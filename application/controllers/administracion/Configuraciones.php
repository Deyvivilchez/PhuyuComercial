<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Configuraciones extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
	}

	private function tabla_configuracion_sesion_existe(){
		$tabla = $this->db->query("select to_regclass('public.configuracion_sesion') as tabla")->row_array();
		return !empty($tabla) && !empty($tabla["tabla"]);
	}

	private function configuracion_sesion($codempresa){
		$default = [
			"alcance" => "global",
			"codempresa" => $codempresa,
			"cerrar_inactividad" => 0,
			"tiempo_inactividad_minutos" => 120,
			"mostrar_aviso" => 1,
			"minutos_aviso" => 5
		];

		if (!$this->tabla_configuracion_sesion_existe()) {
			return $default;
		}

		$global = $this->db->query(
			"select * from public.configuracion_sesion where alcance='global' and estado=1 order by codconfiguracion desc limit 1"
		)->row_array();
		$empresa = $this->db->query(
			"select * from public.configuracion_sesion where alcance='empresa' and codempresa=? and estado=1 order by codconfiguracion desc limit 1",
			[(int)$codempresa]
		)->row_array();

		if (!empty($empresa)) {
			return $empresa;
		}
		if (!empty($global)) {
			return $global;
		}
		return $default;
	}

	private function guardar_configuracion_sesion($codempresa){
		if (!$this->tabla_configuracion_sesion_existe()) {
			return true;
		}

		$alcance = isset($_POST["sesion_alcance"]) && $_POST["sesion_alcance"] === "empresa" ? "empresa" : "global";
		$codempresaConfig = $alcance === "empresa" ? (int)$codempresa : null;
		$cerrar = isset($_POST["cerrar_inactividad"]) ? (int)$_POST["cerrar_inactividad"] : 0;
		$tiempo = isset($_POST["tiempo_inactividad_minutos"]) ? (int)$_POST["tiempo_inactividad_minutos"] : 120;
		$aviso = isset($_POST["mostrar_aviso"]) ? (int)$_POST["mostrar_aviso"] : 0;
		$minutosAviso = isset($_POST["minutos_aviso"]) ? (int)$_POST["minutos_aviso"] : 5;

		$cerrar = $cerrar === 1 ? 1 : 0;
		$aviso = $aviso === 1 ? 1 : 0;
		$tiempo = max(1, min($tiempo, 1440));
		$minutosAviso = max(1, min($minutosAviso, $tiempo));

		if ($alcance === "global") {
			$actual = $this->db->query(
				"select codconfiguracion from public.configuracion_sesion where alcance='global' and estado=1 order by codconfiguracion desc limit 1"
			)->row_array();
		}else{
			$actual = $this->db->query(
				"select codconfiguracion from public.configuracion_sesion where alcance='empresa' and codempresa=? and estado=1 order by codconfiguracion desc limit 1",
				[$codempresaConfig]
			)->row_array();
		}

		$data = [
			"alcance" => $alcance,
			"codempresa" => $codempresaConfig,
			"cerrar_inactividad" => $cerrar,
			"tiempo_inactividad_minutos" => $tiempo,
			"mostrar_aviso" => $aviso,
			"minutos_aviso" => $minutosAviso,
			"estado" => 1,
			"actualizado_en" => date("Y-m-d H:i:s")
		];

		if (!empty($actual)) {
			$this->db->where("codconfiguracion", (int)$actual["codconfiguracion"]);
			return $this->db->update("public.configuracion_sesion", $data);
		}

		$data["creado_en"] = date("Y-m-d H:i:s");
		return $this->db->insert("public.configuracion_sesion", $data);
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			$info = $this->db->query("select *from public.personas where codpersona=".$_SESSION["phuyu_codempresa"])->result_array();
			$empresa = $this->db->query("select *from public.empresas where codempresa=".$_SESSION["phuyu_codempresa"])->result_array();
			$dep = substr($empresa[0]["ubigeo"],0,2);
			$pro = substr($empresa[0]["ubigeo"],2,2); 
			$dis = substr($empresa[0]["ubigeo"],4,2);
			$info[0]["departamento"] = $dep;
			$info[0]["provincia"] = $pro;
			$info[0]["distrito"] = $dis;
			$departamentos = $this->db->query("select distinct(ubidepartamento), departamento from public.ubigeo order by ubidepartamento")->result_array();
			$sesion_config = $this->configuracion_sesion($_SESSION["phuyu_codempresa"]);
			$this->load->view("administracion/configuraciones/index",compact("info","empresa","departamentos","sesion_config"));
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function guardar(){
		if ($this->input->is_ajax_request()) {
			/* $dep = substr($_POST["ubigeo"],0,2); $pro = substr($_POST["ubigeo"],2,2); $dis = substr($_POST["ubigeo"],4,2); $codubigeo = 0;
			$ubigeo = $this->db->query("select codubigeo from public.ubigeo where ubidepartamento='".$dep."' and ubiprovincia='".$pro."' and ubidistrito='".$dis."'")->result_array();
			if(count($ubigeo)>0){
				$codubigeo = $ubigeo[0]["codubigeo"];
			} */
			$codubigeo = $_POST["codubigeo"];

			$ubigeo = $this->db->query("select *from public.ubigeo where codubigeo=".$codubigeo)->result_array();

			$campos = ["coddocumentotipo","documento","razonsocial","nombrecomercial","direccion","email","telefono","codubigeo"];
			$valores = [4,$_POST["documento"],
			$_POST["razonsocial"],
			$_POST["nombrecomercial"],
			$_POST["direccion"],
			$_POST["email"],
			$_POST["telefono"],
			$codubigeo];

			$estado = $this->phuyu_model->phuyu_editar("public.personas", $campos, $valores,"codpersona",$_POST["codpersona"]);

			$campos = ["igvsunat","icbpersunat","iscsunat","slogan","itemrepetircomprobante","claveseguridad","publicidad","agradecimiento","ubigeo","departamento","provincia","distrito","leyendapamazonia","codleyendapamazonia","leyendasamazonia","codleyendasamazonia","urlconsultacomprobantes"];
			$valores = [(double)$_POST["igvsunat"],
			(double)$_POST["icbpersunat"],
			(double)$_POST["iscsunat"],
			$_POST["slogan"],
			$_POST["itemrepetircomprobante"],
			$_POST["claveseguridad"],
			$_POST["publicidad"],
			$_POST["agradecimiento"],
			$ubigeo[0]["ubidepartamento"].''.$ubigeo[0]["ubiprovincia"].''.$ubigeo[0]["ubidistrito"],
			$ubigeo[0]["departamento"],
			$ubigeo[0]["provincia"],
			$ubigeo[0]["distrito"],
			$_POST["leyendapamazonia"],
			$_POST["codleyendapamazonia"],
			$_POST["leyendasamazonia"],
			$_POST["codleyendasamazonia"],
			$_POST["urlconsultacomprobantes"]];
			$estado = $this->phuyu_model->phuyu_editar("public.empresas", $campos, $valores,"codempresa",$_POST["codempresa"]);

			$file = $this->guardar_imagen_configuracion("logo", "logo");
			if ($file === false) {
				echo 0;
				return;
			}
			if ($file !== null) {
				$data = array("foto" => $file);
				$this->db->where("codpersona",$_POST["codpersona"]);
				$estado = $this->db->update("public.personas",$data);
				$_SESSION["phuyu_logo"] = "empresa/".$file;
			}
			$file = $this->guardar_imagen_configuracion("auspiciador", "auspiciador");
			if ($file === false) {
				echo 0;
				return;
			}
			if ($file !== null) {
				$data = array("logoauspiciador" => $file);
				$this->db->where("codempresa",$_POST["codempresa"]);
				$estado = $this->db->update("public.empresas",$data);
			}

			if (!$this->guardar_configuracion_sesion($_POST["codempresa"])) {
				echo 0;
				return;
			}

			$_SESSION["phuyu_ruc"] = $_POST["documento"];
            $_SESSION["phuyu_empresa"] = ($_POST["nombrecomercial"]=='')?$_POST["razonsocial"]:$_POST["nombrecomercial"];
			$_SESSION["phuyu_igv"] = (double)$_POST["igvsunat"];
			$_SESSION["phuyu_icbper"] = (double)$_POST["icbpersunat"];
			$_SESSION["phuyu_itemrepetir"] = $_POST["itemrepetircomprobante"];

			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function guardar1(){
		return $this->guardar();
	}

	private function guardar_imagen_configuracion($campo, $prefijo){
		if (!isset($_FILES[$campo]) || $_FILES[$campo]["name"] == "") {
			return null;
		}

		if ($_FILES[$campo]["error"] !== UPLOAD_ERR_OK || !is_uploaded_file($_FILES[$campo]["tmp_name"])) {
			log_message("error", "Upload invalido en configuracion. Campo=" . $campo . " error=" . $_FILES[$campo]["error"]);
			return false;
		}

		$destino = FCPATH . "public/img/empresa/";
		if (!is_dir($destino)) {
			@mkdir($destino, 0775, true);
		}
		if (!is_dir($destino) || !is_writable($destino)) {
			log_message("error", "Directorio de logos no escribible: " . $destino);
			return false;
		}

		$extension = strtolower(pathinfo($_FILES[$campo]["name"], PATHINFO_EXTENSION));
		$extensionesPermitidas = ["jpg", "jpeg", "png", "gif", "webp"];
		if (!in_array($extension, $extensionesPermitidas, true)) {
			log_message("error", "Extension de imagen no permitida en configuracion. Campo=" . $campo . " extension=" . $extension);
			return false;
		}

		$nombre = $prefijo . "_" . date("YmdHis") . "_" . mt_rand(1000, 9999) . "." . $extension;
		if (!@move_uploaded_file($_FILES[$campo]["tmp_name"], $destino . $nombre)) {
			log_message("error", "No se pudo guardar imagen de configuracion. Campo=" . $campo . " destino=" . $destino . $nombre);
			return false;
		}

		return $nombre;
	}

	

	// application/controllers/administracion/Configuraciones.php ///

public function guardar22()
{
    $campo = 'logo';                 // <input type="file" name="logo">
    $nombreArchivo = 'logo_4.png';   // o genera uno dinámico

    // 1) Ruta ABSOLUTA dentro del proyecto (NADA de "./")
    $destDir  = FCPATH . 'public/img/empresa/';     // p.ej. /Applications/XAMPP/.../motorepuestosmirsan/public/img/empresa/
    $destPath = $destDir . $nombreArchivo;

    // 2) Asegura carpeta
    if (!is_dir($destDir)) {
        if (!@mkdir($destDir, 0775, true)) {
            show_error('No se pudo crear el directorio: ' . $destDir, 500);
            return;
        }
    }

    // 3) Valida el upload
    if (
        !isset($_FILES[$campo]) ||
        $_FILES[$campo]['error'] !== UPLOAD_ERR_OK ||
        !is_uploaded_file($_FILES[$campo]['tmp_name'])
    ) {
        show_error('Archivo inválido o no enviado (campo "logo").', 400);
        return;
    }

    // (Opcional) Limpia un archivo previo con permisos raros
    if (file_exists($destPath)) { @unlink($destPath); }

    // 4) Mueve el archivo
    if (!@move_uploaded_file($_FILES[$campo]['tmp_name'], $destPath)) {
        // Log de diagnóstico útil
        log_message('error', 'move_uploaded_file falló. TMP=' . ($_FILES[$campo]['tmp_name'] ?? 'null') . ' DEST=' . $destPath);
        log_message('error', 'cwd=' . getcwd() . ' FCPATH=' . FCPATH);

        show_error('No se pudo guardar el archivo. Revisa permisos y ruta: ' . $destDir, 500);
        return;
    }

    // 5) Éxito
    // ... guarda en BD la ruta si hace falta ...
    $this->output
        ->set_status_header(200)
        ->set_output(json_encode(['ok' => true, 'path' => 'public/img/empresa/'.$nombreArchivo]));
}

}
