<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Comprobantes extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$sucursales = $this->db->query("select codsucursal,descripcion from sucursales where estado=1 order by codsucursal")->result_array();
				$this->load->view("administracion/comprobantes/index",compact("sucursales"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function lista(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$limit = 10; $offset = $this->request->pagina * $limit - $limit;
            $acceso = '';
			if($this->request->sucursal !== ''){
               $acceso = ' AND comprobantes.codsucursal = '.$this->request->sucursal;
			}

			$lista = $this->db->query("select comprobantes.*, tipos.descripcion as tipo, sucursales.descripcion as sucursal from caja.comprobantes as comprobantes inner join caja.comprobantetipos as tipos on(comprobantes.codcomprobantetipo=tipos.codcomprobantetipo) inner join public.sucursales as sucursales on(comprobantes.codsucursal=sucursales.codsucursal) where (UPPER(tipos.descripcion) like UPPER('%".$this->request->buscar."%') or UPPER(comprobantes.seriecomprobante) like UPPER('%".$this->request->buscar."%') or UPPER(sucursales.descripcion) like UPPER('%".$this->request->buscar."%')) and comprobantes.estado=1 ".$acceso." order by sucursales.codsucursal desc offset ".$offset." limit ".$limit)->result_array();
			foreach ($lista as $key => $value) {
				$caja = $this->db->query("select *from caja.cajas where codcaja=".$value["codcaja"])->result_array();
				if (count($caja)!=0) {
					$lista[$key]["referencia"] = $caja[0]["descripcion"];
				}else{
					$almacen = $this->db->query("select *from almacen.almacenes where codalmacen=".$value["codalmacen"])->result_array();
					if (count($almacen)!=0) {
						$lista[$key]["referencia"] = $almacen[0]["descripcion"];
					}else{
						$lista[$key]["referencia"] = "";
					}
				}
			}
			
			$total = $this->db->query("select count(*) as total from caja.comprobantes as comprobantes inner join caja.comprobantetipos as tipos on(comprobantes.codcomprobantetipo=tipos.codcomprobantetipo) inner join public.sucursales as sucursales on(comprobantes.codsucursal=sucursales.codsucursal) where (UPPER(tipos.descripcion) like UPPER('%".$this->request->buscar."%') or UPPER(comprobantes.seriecomprobante) like UPPER('%".$this->request->buscar."%') or UPPER(sucursales.descripcion) like UPPER('%".$this->request->buscar."%')) and comprobantes.estado=1 ".$acceso."")->result_array();

			$paginas = floor($total[0]["total"] / $limit);
			if ( ($total[0]["total"] % $limit)!=0 ) {
				$paginas = $paginas + 1;
			}

			$paginacion = array();
			$paginacion["total"] = $total[0]["total"];
			$paginacion["actual"] = $this->request->pagina;
			$paginacion["ultima"] = $paginas;
			$paginacion["desde"] = $offset;
			$paginacion["hasta"] = $offset + $limit;

			echo json_encode(array("lista" => $lista,"paginacion" => $paginacion));
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function nuevo(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$sucursales = $this->db->query("select * from public.sucursales where estado=1")->result_array();
				$tipos = $this->db->query("select * from caja.comprobantetipos where estado=1 order by codcomprobantetipo")->result_array();
				$this->load->view("administracion/comprobantes/nuevo",compact("sucursales","tipos"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function cajas($codsucursal){
		if ($this->input->is_ajax_request()) {
			$cajas = $this->db->query("select *from caja.cajas where codsucursal=".$codsucursal." and estado=1")->result_array();
			$html = '<option value="">SELECCIONE</option>';
			foreach ($cajas as $key => $value) {
				$html .= '<option value="'.$value["codcaja"].'">'.$value["descripcion"].'</option>';
			}
			echo $html;
		}
	}

	function cajas_existe($codcaja,$codcomprobantetipo){
		if ($this->input->is_ajax_request()) {
			$existe = $this->db->query("select *from caja.comprobantes where codcaja=".$codcaja." and codcomprobantetipo=".$codcomprobantetipo." and estado=1")->result_array();
			if (count($existe)==0) {
				echo "0";
			}else{
				echo "1";
			}
		}
	}

	function almacenes($codsucursal){
		if ($this->input->is_ajax_request()) {
			$almacenes = $this->db->query("select *from almacen.almacenes where codsucursal=".$codsucursal." and estado=1")->result_array();
			$html = '<option value="">SELECCIONE</option>';
			foreach ($almacenes as $key => $value) {
				$html .= '<option value="'.$value["codalmacen"].'">'.$value["descripcion"].'</option>';
			}
			echo $html;
		}
	}

	function almacen_existe($codalmacen,$codcomprobantetipo){
		if ($this->input->is_ajax_request()) {
			$existe = $this->db->query("select *from caja.comprobantes where codalmacen=".$codalmacen." and codcomprobantetipo=".$codcomprobantetipo." and estado=1")->result_array();
			if (count($existe)==0) {
				echo "0";
			}else{
				echo "1";
			}
		}
	}

	function notas($codsucursal){
		if ($this->input->is_ajax_request()) {
			$notas = $this->db->query("select c.*,ct.descripcion as tipo from caja.comprobantes as c inner join caja.comprobantetipos as ct on(c.codcomprobantetipo=ct.codcomprobantetipo) where (c.codcomprobantetipo=10 or c.codcomprobantetipo=12) and c.codsucursal=".$codsucursal." and c.estado=1")->result_array();
			$html = '<option value="">SELECCIONE</option>';
			foreach ($notas as $key => $value) {
				$html .= '<option value="'.$value["codcomprobantetipo"].'-'.$value["seriecomprobante"].'">'.$value["tipo"].' (SERIE: '.$value["seriecomprobante"].')</option>';
			}
			echo $html;
		}
	}

	function notas_existe($codcomprobante,$codcomprobantetipo){
		if ($this->input->is_ajax_request()) {
			$datos = explode("-", $codcomprobante);

			$existe = $this->db->query("select *from caja.comprobantes where codcomprobantetipo_ref=".$datos[0]." and seriecomprobante_ref='".$datos[1]."' and codcomprobantetipo=".$codcomprobantetipo." and estado=1")->result_array();
			if (count($existe)==0) {
				echo "0";
			}else{
				echo "1";
			}
		}
	}

	function guardar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			//echo 1;exit;
			$this->db->trans_begin();

			$_POST["publicidad"] = (isset($_POST["publicidad"])) ? $_POST["publicidad"] : "";
			$_POST["agradecimiento"] = (isset($_POST["agradecimiento"])) ? $_POST["agradecimiento"] : "";
			if($_POST["codregistro"]=="") {
				$campos = ["codsucursal","codcomprobantetipo","codcaja","codalmacen","seriecomprobante","nroinicial","nrocorrelativo","codcomprobantetipo_ref","seriecomprobante_ref","impresion","formato","orientacion","impresora","logo","logoauspiciador","tipoconleyendaamazonia","nombrecomercial","publicidad","agradecimiento","impresionlogo"];

				$_POST["codcaja"] = (isset($_POST["codcaja"])) ? $_POST["codcaja"] : "";
				$_POST["codalmacen"] = (isset($_POST["codalmacen"])) ? $_POST["codalmacen"] : "";
				$_POST["codcomprobantetipo_ref"] = (isset($_POST["codcomprobantetipo_ref"])) ? $_POST["codcomprobantetipo_ref"] : "";
				$_POST["impresion"] = (isset($_POST["impresion"])) ? $_POST["impresion"] : 1;
				$_POST["formato"] = (isset($_POST["formato"])) ? $_POST["formato"] : "a4"; 
				$_POST["orientacion"] = (isset($_POST["orientacion"])) ? $_POST["orientacion"] : "p";
				$_POST["impresora"] = (isset($_POST["impresora"])) ? $_POST["impresora"] : "";
				$_POST["impresionlogo"] = (isset($_POST["impresionlogo"])) ? $_POST["impresionlogo"] : 1;

				if ($_POST["codcaja"]=="") {
					$_POST["codcaja"] = 0;
				}
				if ($_POST["codalmacen"]=="") {
					$_POST["codalmacen"] = 0;
				}

				if ($_FILES["logoa"]["name"]!="") {
					$logo = "logo_".substr($_FILES["logoa"]["name"],-5);
					move_uploaded_file($_FILES["logoa"]["tmp_name"],"./public/img/empresa/".$file);
					
				}else{
					$persona = $this->db->query("select *from public.personas where codpersona=1")->result_array();
					$logo = $persona[0]["foto"];
				}
				if ($_FILES["auspiciadora"]["name"]!="") {
					$auspiciador = "auspiciador_".substr($_FILES["auspiciadora"]["name"],-5);
					move_uploaded_file($_FILES["auspiciadora"]["tmp_name"],"./public/img/empresa/".$file);
					
				}else{
					$persona = $this->db->query("select *from public.empresas where codempresa=1")->result_array();
					$auspiciador = $persona[0]["logoauspiciador"];
				}

				$comprobantetipo_ref = ""; $seriecomprobante_ref = "";
				if ($_POST["codcomprobantetipo_ref"]!="" && $_POST["codcomprobantetipo_ref"]!=0) {
					$datos = explode("-",$_POST["codcomprobantetipo_ref"]);
					$comprobantetipo_ref = $datos[0]; $seriecomprobante_ref = $datos[1];
				}

				$valores = [
					(int)$_POST["codsucursal"],
					(int)$_POST["codcomprobantetipo"],
					(int)$_POST["codcaja"],
					(int)$_POST["codalmacen"],
					strtoupper($_POST["seriecomprobante"]),
					(int)$_POST["nroinicial"],
					(int)$_POST["nrocorrelativo"], 
					(int)$comprobantetipo_ref,$seriecomprobante_ref,
					(int)$_POST["impresion"],
					$_POST["formato"],$_POST["orientacion"],$_POST["impresora"],
					$logo,$auspiciador,(int)$_POST["tipoconleyendaamazonia"],
					$_POST["nombrecomercial"],
					$_POST["publicidad"],
					$_POST["agradecimiento"], $_POST["impresionlogo"]
				];
				$estado = $this->phuyu_model->phuyu_guardar("caja.comprobantes", $campos, $valores);
			}else{
				$_POST["slogan"] = (isset($_POST["slogan"])) ? $_POST["slogan"] : "";
				$_POST["logo"] = (isset($_POST["logo"])) ? $_POST["logo"] : "";
				$_POST["auspiciador"] = (isset($_POST["auspiciador"])) ? $_POST["auspiciador"] : "";
				if(isset($_FILES["logoa"]["name"])){
					if ($_FILES["logoa"]["name"]!=$_POST["logo"] && $_FILES["logoa"]["name"] !="") {
						$logo = "logo_".substr($_FILES["logoa"]["name"],-5);
						move_uploaded_file($_FILES["logoa"]["tmp_name"],"./public/img/empresa/".$logo);
						
					}else{
						$logo = $_POST["logo"];
					}
				}else{
					$logo = $_POST["logo"];
				}
				if(isset($_FILES["auspiciadora"]["name"])){
					if ($_FILES["auspiciadora"]["name"]!=$_POST["auspiciador"]) {
						$auspiciador = "auspiciador_".substr($_FILES["auspiciadora"]["name"],-5);
						move_uploaded_file($_FILES["auspiciadora"]["tmp_name"],"./public/img/empresa/".$auspiciador);
						
					}else{
						$auspiciador = $_POST["logoauspiciador"];
					}
				}else{
					$auspiciador = $_POST["logoauspiciador"];
				}

				$campos = ["nroinicial","nrocorrelativo","logo","logoauspiciador","slogan","tipoconleyendaamazonia","nombrecomercial","publicidad","agradecimiento","impresionlogo"];
				$valores = [(int)$_POST["nroinicial"],(int)$_POST["nrocorrelativo"],$logo,$auspiciador,$_POST["slogan"],(int)$_POST["tipoconleyendaamazonia"],$_POST["nombrecomercial"],$_POST["publicidad"],$_POST["agradecimiento"],$_POST["impresionlogo"]];
				$f = ["codsucursal","codcomprobantetipo","seriecomprobante"];
				$v = [$_POST["codsucursal_editar"],$_POST["codcomprobantetipo_editar"],$_POST["seriecomprobante_editar"]];
				$estado = $this->phuyu_model->phuyu_editar_1("caja.comprobantes", $campos, $valores, $f, $v);
			}
			if ($this->db->trans_status() === FALSE){
			    $this->db->trans_rollback(); $estado = 0;
			}else{
				if ($estado!=1) { 
					$this->db->trans_rollback(); $estado = 0; 
				}
				$this->db->trans_commit();
			}
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function editar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$codigo = explode("-", $this->request->codregistro);
			$info = $this->db->query("select codcomprobantetipo as codregistro,* from caja.comprobantes where codcomprobantetipo=".$codigo[0]." and seriecomprobante='".$codigo[1]."' ")->result_array();
			echo json_encode($info);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function validar_serie($serie){
		if ($this->input->is_ajax_request()) {
			$codigo = explode("-", $serie);
			$estado = $this->db->query("select count(*) as cantidad from kardex.kardexalmacen where seriecomprobante='".$codigo[1]."'")->result_array();
			$data["serie"] = $codigo[1];
			$data["estado"] = $estado[0]["cantidad"];
			echo json_encode($data);
		}
	}

	function eliminar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$codigo = explode("-", $this->request->codregistro);

			$campos = ["estado"]; $valores = [0];
			$f = ["codcomprobantetipo","seriecomprobante"]; $v = [$codigo[0],$codigo[1]];
			$estado = $this->phuyu_model->phuyu_editar_1("caja.comprobantes", $campos, $valores, $f, $v);
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}
}