<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Backup extends CI_Controller {
    public function __construct() {
        parent::__construct();
    }

    public function index() {
        $this->load->view('backup');
    }

    function database_backup() {
        if (!isset($_SESSION["phuyu_usuario"])) {
            $this->load->view("phuyu/404");
            return;
        }

        if (!function_exists('proc_open')) {
            $this->responder_error_backup("El servidor no permite ejecutar pg_dump.");
            return;
        }

        @set_time_limit(0);

        $pg_dump = $this->buscar_pg_dump();
        if ($pg_dump === "") {
            $this->responder_error_backup("No se encontro pg_dump en el servidor.");
            return;
        }

        $database = $this->db->database;
        $tipo_backup = $this->tipo_backup_solicitado();
        $extension = $tipo_backup === "sql" ? "sql" : "backup";
        $archivo = "phuyu-backup-" . $this->nombre_archivo_seguro($database) . "-" . date("Ymd-His") . "." . $extension;
        $temporal = $this->crear_archivo_temporal_backup();

        if ($temporal === false) {
            $this->responder_error_backup("No se pudo preparar el archivo temporal del backup.");
            return;
        }

        $comando = $this->comando_pg_dump($pg_dump, $temporal, $database, $tipo_backup);

        $salida_temporal = $this->crear_archivo_temporal_backup();
        $error_temporal = $this->crear_archivo_temporal_backup();

        if ($salida_temporal === false || $error_temporal === false) {
            @unlink($temporal);
            @unlink($salida_temporal);
            @unlink($error_temporal);
            $this->responder_error_backup("No se pudo preparar el registro temporal del backup.");
            return;
        }

        $descriptor = array(
            0 => array("pipe", "r"),
            1 => array("file", $salida_temporal, "w"),
            2 => array("file", $error_temporal, "w")
        );

        $entorno = $_ENV;
        $entorno["PATH"] = getenv("PATH") ?: "/opt/homebrew/bin:/usr/local/bin:/usr/bin:/bin:/Applications/XAMPP/xamppfiles/bin";
        $entorno["PGPASSWORD"] = $this->db->password;

        $proceso = proc_open($comando, $descriptor, $pipes, null, $entorno);
        if (!is_resource($proceso)) {
            @unlink($temporal);
            @unlink($salida_temporal);
            @unlink($error_temporal);
            $this->responder_error_backup("No se pudo iniciar el proceso de backup.");
            return;
        }

        fclose($pipes[0]);
        $codigo = proc_close($proceso);
        $salida = file_exists($salida_temporal) ? (string) file_get_contents($salida_temporal) : "";
        $error = file_exists($error_temporal) ? (string) file_get_contents($error_temporal) : "";
        @unlink($salida_temporal);
        @unlink($error_temporal);

        if ($codigo !== 0 || !file_exists($temporal) || filesize($temporal) === 0) {
            @unlink($temporal);
            $mensaje = trim($error) !== "" ? trim($error) : trim($salida);
            $this->responder_error_backup("pg_dump no pudo generar el backup. " . $mensaje);
            return;
        }

        $this->limpiar_buffers_salida();

        header("Content-Description: File Transfer");
        header("Content-Type: " . ($tipo_backup === "sql" ? "application/sql; charset=utf-8" : "application/octet-stream"));
        header("Content-Disposition: attachment; filename=\"" . $archivo . "\"");
        header("Content-Transfer-Encoding: binary");
        header("Content-Length: " . filesize($temporal));
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Pragma: no-cache");

        readfile($temporal);
        @unlink($temporal);
        exit;
    }

    private function buscar_pg_dump() {
        $rutas = array(
            "/opt/homebrew/bin/pg_dump",
            "/usr/local/bin/pg_dump",
            "/usr/bin/pg_dump",
            "/Applications/XAMPP/xamppfiles/bin/pg_dump",
            "/Applications/Postgres.app/Contents/Versions/latest/bin/pg_dump"
        );

        foreach ($rutas as $ruta) {
            if (is_executable($ruta)) {
                return $ruta;
            }
        }

        if (function_exists('shell_exec')) {
            $ruta = trim((string) shell_exec("command -v pg_dump 2>/dev/null"));
            if ($ruta !== "" && is_executable($ruta)) {
                return $ruta;
            }
        }

        return "";
    }

    private function crear_archivo_temporal_backup() {
        $directorio = $this->directorio_temporal_backup();

        if ($directorio === "") {
            return false;
        }

        return @tempnam($directorio, "phuyu_backup_");
    }

    private function directorio_temporal_backup() {
        $rutas = array(
            APPPATH . "cache/phuyu_backups",
            APPPATH . "cache",
            sys_get_temp_dir()
        );

        foreach ($rutas as $ruta) {
            $ruta = rtrim((string) $ruta, DIRECTORY_SEPARATOR);

            if ($ruta === "") {
                continue;
            }

            if (!is_dir($ruta)) {
                @mkdir($ruta, 0775, true);
            }

            if (is_dir($ruta) && is_writable($ruta)) {
                return $ruta;
            }
        }

        return "";
    }

    private function tipo_backup_solicitado() {
        $tipo = $this->input->post("tipo");
        if ($tipo === null || $tipo === "") {
            $request = json_decode(file_get_contents("php://input"));
            if (isset($request->tipo)) {
                $tipo = $request->tipo;
            }
        }

        $tipo = strtolower(trim((string) $tipo));
        return $tipo === "sql" ? "sql" : "backup";
    }

    private function comando_pg_dump($pg_dump, $temporal, $database, $tipo_backup = "backup") {
        $partes = array(escapeshellarg($pg_dump));

        if (!empty($this->db->hostname)) {
            $partes[] = "--host " . escapeshellarg($this->db->hostname);
        }

        if (!empty($this->db->port)) {
            $partes[] = "--port " . escapeshellarg($this->db->port);
        }

        if (!empty($this->db->username)) {
            $partes[] = "--username " . escapeshellarg($this->db->username);
        }

        if ($tipo_backup === "sql") {
            $partes[] = "--format plain";
            $partes[] = "--encoding UTF8";
        } else {
            $partes[] = "--format custom";
        }

        $partes[] = "--blobs";
        $partes[] = "--no-owner";
        $partes[] = "--no-acl";
        $partes[] = "--no-password";
        $partes[] = "--file " . escapeshellarg($temporal);
        $partes[] = escapeshellarg($database);

        return implode(" ", $partes);
    }

    private function nombre_archivo_seguro($nombre) {
        $nombre = preg_replace("/[^A-Za-z0-9_-]/", "_", $nombre);
        return trim($nombre, "_") ?: "database";
    }

    private function responder_error_backup($mensaje) {
        $this->limpiar_buffers_salida();
        $this->output->set_status_header(500);
        $this->output->set_content_type("text/plain", "utf-8");
        $this->output->set_output($mensaje);
    }

    private function limpiar_buffers_salida() {
        while (ob_get_level() > 0) {
            if (!@ob_end_clean()) {
                break;
            }
        }
    }
}
