<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Configuraciones extends CI_Controller {
	private $ultimo_error_configuracion = "";

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
	}

	private function responder_guardar($estado, $mensaje, $extra = []){
		$respuesta = array_merge([
			"estado" => (int)$estado,
			"mensaje" => $mensaje
		], $extra);

		$this->output
			->set_content_type("application/json")
			->set_output(json_encode($respuesta));
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
			$codempresa = isset($_SESSION["phuyu_codempresa"]) ? (int)$_SESSION["phuyu_codempresa"] : 0;
			$empresa = $this->db->query("select * from public.empresas where codempresa=?", [$codempresa])->result_array();
			if (count($empresa) == 0) {
				$this->load->view("phuyu/404");
				return;
			}

			$codpersona = isset($empresa[0]["codpersona"]) ? (int)$empresa[0]["codpersona"] : $codempresa;
			$info = $this->db->query("select * from public.personas where codpersona=?", [$codpersona])->result_array();
			if (count($info) == 0) {
				$this->load->view("phuyu/404");
				return;
			}

			$ubigeoEmpresa = isset($empresa[0]["ubigeo"]) ? (string)$empresa[0]["ubigeo"] : "";
			$dep = substr($ubigeoEmpresa,0,2);
			$pro = substr($ubigeoEmpresa,2,2); 
			$dis = substr($ubigeoEmpresa,4,2);
			$info[0]["departamento"] = $dep;
			$info[0]["provincia"] = $pro;
			$info[0]["distrito"] = $dis;
			$departamentos = $this->db->query("select distinct(ubidepartamento), departamento from public.ubigeo order by ubidepartamento")->result_array();
			$sesion_config = $this->configuracion_sesion($codempresa);
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
			$codubigeo = isset($_POST["codubigeo"]) ? (int)$_POST["codubigeo"] : 0;

			$ubigeo = $this->db->query("select * from public.ubigeo where codubigeo=?", [$codubigeo])->result_array();
			if (count($ubigeo) == 0) {
				$this->responder_guardar(0, "Seleccione un departamento, provincia y distrito validos.");
				return;
			}

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
				$this->responder_guardar(0, $this->ultimo_error_configuracion ?: "No se pudo guardar el logo de la empresa.");
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
				$this->responder_guardar(0, $this->ultimo_error_configuracion ?: "No se pudo guardar el logo auspiciador.");
				return;
			}
			if ($file !== null) {
				$data = array("logoauspiciador" => $file);
				$this->db->where("codempresa",$_POST["codempresa"]);
				$estado = $this->db->update("public.empresas",$data);
			}

			if (!$this->guardar_configuracion_sesion($_POST["codempresa"])) {
				$this->responder_guardar(0, "No se pudo guardar la configuracion de sesion.");
				return;
			}

			$_SESSION["phuyu_ruc"] = $_POST["documento"];
            $_SESSION["phuyu_empresa"] = ($_POST["nombrecomercial"]=='')?$_POST["razonsocial"]:$_POST["nombrecomercial"];
			$_SESSION["phuyu_igv"] = (double)$_POST["igvsunat"];
			$_SESSION["phuyu_icbper"] = (double)$_POST["icbpersunat"];
			$_SESSION["phuyu_itemrepetir"] = $_POST["itemrepetircomprobante"];

			if ($estado) {
				$this->responder_guardar(1, "Configuracion registrada correctamente.");
				return;
			}

			$errorDb = $this->db->error();
			$mensaje = !empty($errorDb["message"]) ? $errorDb["message"] : "No se pudo guardar la configuracion.";
			$this->responder_guardar(0, $mensaje);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function guardar1(){
		return $this->guardar();
	}

	private function guardar_imagen_configuracion($campo, $prefijo){
		$this->ultimo_error_configuracion = "";

		if (!isset($_FILES[$campo]) || $_FILES[$campo]["name"] == "") {
			return null;
		}

		if ($_FILES[$campo]["error"] !== UPLOAD_ERR_OK || !is_uploaded_file($_FILES[$campo]["tmp_name"])) {
			log_message("error", "Upload invalido en configuracion. Campo=" . $campo . " error=" . $_FILES[$campo]["error"]);
			$errores = [
				UPLOAD_ERR_INI_SIZE => "La imagen supera el tamano maximo permitido por el servidor.",
				UPLOAD_ERR_FORM_SIZE => "La imagen supera el tamano maximo permitido por el formulario.",
				UPLOAD_ERR_PARTIAL => "La imagen se subio incompleta. Intente nuevamente.",
				UPLOAD_ERR_NO_FILE => "No se recibio ningun archivo.",
				UPLOAD_ERR_NO_TMP_DIR => "El servidor no tiene directorio temporal para uploads.",
				UPLOAD_ERR_CANT_WRITE => "El servidor no pudo escribir el archivo temporal.",
				UPLOAD_ERR_EXTENSION => "Una extension de PHP bloqueo la subida del archivo."
			];
			$this->ultimo_error_configuracion = isset($errores[$_FILES[$campo]["error"]])
				? $errores[$_FILES[$campo]["error"]]
				: "El archivo recibido no es valido.";
			return false;
		}

		$destino = FCPATH . "public/img/empresa/";
		if (!is_dir($destino)) {
			@mkdir($destino, 0775, true);
		}
		if (!is_dir($destino) || !is_writable($destino)) {
			log_message("error", "Directorio de logos no escribible: " . $destino);
			$this->ultimo_error_configuracion = "No hay permisos de escritura en public/img/empresa. Revise propietario/permisos del directorio.";
			return false;
		}

		$extension = strtolower(pathinfo($_FILES[$campo]["name"], PATHINFO_EXTENSION));
		$extensionesPermitidas = ["jpg", "jpeg", "png", "gif", "webp"];
		if (!in_array($extension, $extensionesPermitidas, true)) {
			log_message("error", "Extension de imagen no permitida en configuracion. Campo=" . $campo . " extension=" . $extension);
			$this->ultimo_error_configuracion = "Formato de imagen no permitido. Use JPG, JPEG, PNG, GIF o WEBP.";
			return false;
		}

		$nombre = $prefijo . "_" . date("YmdHis") . "_" . mt_rand(1000, 9999) . "." . $extension;
		if (!@move_uploaded_file($_FILES[$campo]["tmp_name"], $destino . $nombre)) {
			log_message("error", "No se pudo guardar imagen de configuracion. Campo=" . $campo . " destino=" . $destino . $nombre);
			$this->ultimo_error_configuracion = "No se pudo mover la imagen al directorio public/img/empresa. Revise permisos y espacio en disco.";
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
