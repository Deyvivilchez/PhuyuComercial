<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Configuraciones extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
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
			$this->load->view("administracion/configuraciones/index",compact("info","empresa","departamentos"));
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

			if ($_FILES["logo"]["name"]!="") {
				$file = "logo_".substr($_FILES["logo"]["name"],-5);
				move_uploaded_file($_FILES["logo"]["tmp_name"],"./public/img/empresa/".$file);
				
				$data = array("foto" => $file);
				$this->db->where("codpersona",$_POST["codpersona"]);
				$estado = $this->db->update("public.personas",$data);
			}
			if ($_FILES["auspiciador"]["name"]!="") {
				$file = "auspiciador_".substr($_FILES["auspiciador"]["name"],-5);
				move_uploaded_file($_FILES["auspiciador"]["tmp_name"],"./public/img/empresa/".$file);
				
				$data = array("logoauspiciador" => $file);
				$this->db->where("codempresa",$_POST["codempresa"]);
				$estado = $this->db->update("public.empresas",$data);
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