<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Comprobantes extends CI_Controller {

	public function __construct(){
		parent::__construct(); 
		$this->load->model("phuyu_model");
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
			$this->db->trans_begin();

			$_POST["publicidad"] = (isset($_POST["publicidad"])) ? $_POST["publicidad"] : "";
			$_POST["agradecimiento"] = (isset($_POST["agradecimiento"])) ? $_POST["agradecimiento"] : "";
			$_POST["slogan"] = (isset($_POST["slogan"])) ? $_POST["slogan"] : "";
			$_POST["impresion"] = (isset($_POST["impresion"])) ? $_POST["impresion"] : 0;
			$_POST["formato"] = (isset($_POST["formato"])) ? $_POST["formato"] : "a4";
			$_POST["orientacion"] = (isset($_POST["orientacion"])) ? $_POST["orientacion"] : "p";
			$_POST["impresora"] = (isset($_POST["impresora"])) ? $_POST["impresora"] : "";
			$_POST["impresionlogo"] = (isset($_POST["impresionlogo"])) ? $_POST["impresionlogo"] : 1;

			if($_POST["codregistro"]=="") {
				$campos = ["codsucursal","codcomprobantetipo","codcaja","codalmacen","seriecomprobante","nroinicial","nrocorrelativo","codcomprobantetipo_ref","seriecomprobante_ref","impresion","formato","orientacion","impresora","logo","logoauspiciador","slogan","tipoconleyendaamazonia","nombrecomercial","publicidad","agradecimiento","impresionlogo"];

				$_POST["codcaja"] = (isset($_POST["codcaja"])) ? $_POST["codcaja"] : "";
				$_POST["codalmacen"] = (isset($_POST["codalmacen"])) ? $_POST["codalmacen"] : "";
				$_POST["codcomprobantetipo_ref"] = (isset($_POST["codcomprobantetipo_ref"])) ? $_POST["codcomprobantetipo_ref"] : "";

				if ($_POST["codcaja"]=="") {
					$_POST["codcaja"] = 0;
				}
				if ($_POST["codalmacen"]=="") {
					$_POST["codalmacen"] = 0;
				}

				$logoSubido = $this->guardar_imagen_comprobante("logoa", "logo");
				if ($logoSubido === false) {
					$this->db->trans_rollback();
					echo 0;
					return;
				}
				if ($logoSubido !== null) {
					$logo = $logoSubido;
				}else{
					$persona = $this->db->query("select *from public.personas where codpersona=1")->result_array();
					$logo = $persona[0]["foto"];
				}
				$auspiciadorSubido = $this->guardar_imagen_comprobante("auspiciadora", "auspiciador");
				if ($auspiciadorSubido === false) {
					$this->db->trans_rollback();
					echo 0;
					return;
				}
				if ($auspiciadorSubido !== null) {
					$auspiciador = $auspiciadorSubido;
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
					$logo,$auspiciador,$_POST["slogan"],(int)$_POST["tipoconleyendaamazonia"],
					$_POST["nombrecomercial"],
					$_POST["publicidad"],
					$_POST["agradecimiento"], $_POST["impresionlogo"]
				];
				$estado = $this->phuyu_model->phuyu_guardar("caja.comprobantes", $campos, $valores);
			}else{
				$codsucursalEditar = isset($_POST["codsucursal_editar"]) ? (int)$_POST["codsucursal_editar"] : 0;
				$codtipoEditar = isset($_POST["codcomprobantetipo_editar"]) ? (int)$_POST["codcomprobantetipo_editar"] : 0;
				$serieEditar = isset($_POST["seriecomprobante_editar"]) ? strtoupper(trim($_POST["seriecomprobante_editar"])) : "";

				$actual = $this->obtener_comprobante($codsucursalEditar, $codtipoEditar, $serieEditar);
				if (empty($actual)) {
					$this->db->trans_rollback();
					echo 0;
					return;
				}

				$_POST["logo"] = (isset($_POST["logo"])) ? $_POST["logo"] : "";
				$_POST["auspiciador"] = (isset($_POST["auspiciador"])) ? $_POST["auspiciador"] : "";
				$logoSubido = $this->guardar_imagen_comprobante("logoa", "logo");
				if ($logoSubido === false) {
					$this->db->trans_rollback();
					echo 0;
					return;
				}
				if ($logoSubido !== null) {
					$logo = $logoSubido;
				}else{
					$logo = $_POST["logo"];
				}
				$auspiciadorSubido = $this->guardar_imagen_comprobante("auspiciadora", "auspiciador");
				if ($auspiciadorSubido === false) {
					$this->db->trans_rollback();
					echo 0;
					return;
				}
				if ($auspiciadorSubido !== null) {
					$auspiciador = $auspiciadorSubido;
				}else{
					$auspiciador = $_POST["logoauspiciador"];
				}

				$puedeEditarIdentidad = $this->comprobante_identidad_editable($actual);
				if ($puedeEditarIdentidad) {
					$_POST["codcaja"] = (isset($_POST["codcaja"]) && $_POST["codcaja"]!="") ? $_POST["codcaja"] : 0;
					$_POST["codalmacen"] = (isset($_POST["codalmacen"]) && $_POST["codalmacen"]!="") ? $_POST["codalmacen"] : 0;
					$_POST["codcomprobantetipo_ref"] = (isset($_POST["codcomprobantetipo_ref"])) ? $_POST["codcomprobantetipo_ref"] : "";

					$comprobantetipo_ref = ""; $seriecomprobante_ref = "";
					if ($_POST["codcomprobantetipo_ref"]!="" && $_POST["codcomprobantetipo_ref"]!=0) {
						$datos = explode("-",$_POST["codcomprobantetipo_ref"]);
						$comprobantetipo_ref = isset($datos[0]) ? $datos[0] : "";
						$seriecomprobante_ref = isset($datos[1]) ? $datos[1] : "";
					}

					$nuevoTipo = (int)$_POST["codcomprobantetipo"];
					$nuevaSerie = strtoupper(trim($_POST["seriecomprobante"]));
					$duplicado = $this->db->query(
						"select count(*) as cantidad from caja.comprobantes
						where codcomprobantetipo=? and seriecomprobante=? and not (codcomprobantetipo=? and seriecomprobante=?) and estado=1",
						[$nuevoTipo, $nuevaSerie, $codtipoEditar, $serieEditar]
					)->row_array();

					if (!empty($duplicado) && (int)$duplicado["cantidad"] > 0) {
						$this->db->trans_rollback();
						echo 2;
						return;
					}

					$campos = ["codsucursal","codcomprobantetipo","codcaja","codalmacen","seriecomprobante","nroinicial","nrocorrelativo","codcomprobantetipo_ref","seriecomprobante_ref","impresion","formato","orientacion","impresora","logo","logoauspiciador","slogan","tipoconleyendaamazonia","nombrecomercial","publicidad","agradecimiento","impresionlogo"];
					$valores = [(int)$_POST["codsucursal"],$nuevoTipo,(int)$_POST["codcaja"],(int)$_POST["codalmacen"],$nuevaSerie,(int)$_POST["nroinicial"],(int)$_POST["nrocorrelativo"],(int)$comprobantetipo_ref,$seriecomprobante_ref,(int)$_POST["impresion"],$_POST["formato"],$_POST["orientacion"],$_POST["impresora"],$logo,$auspiciador,$_POST["slogan"],(int)$_POST["tipoconleyendaamazonia"],$_POST["nombrecomercial"],$_POST["publicidad"],$_POST["agradecimiento"],$_POST["impresionlogo"]];
				}else{
					$nrocorrelativo = max((int)$_POST["nrocorrelativo"], (int)$actual["nrocorrelativo"]);
					$campos = ["nroinicial","nrocorrelativo","impresion","formato","orientacion","impresora","logo","logoauspiciador","slogan","tipoconleyendaamazonia","nombrecomercial","publicidad","agradecimiento","impresionlogo"];
					$valores = [(int)$_POST["nroinicial"],$nrocorrelativo,(int)$_POST["impresion"],$_POST["formato"],$_POST["orientacion"],$_POST["impresora"],$logo,$auspiciador,$_POST["slogan"],(int)$_POST["tipoconleyendaamazonia"],$_POST["nombrecomercial"],$_POST["publicidad"],$_POST["agradecimiento"],$_POST["impresionlogo"]];
				}
				$f = ["codsucursal","codcomprobantetipo","seriecomprobante"];
				$v = [$codsucursalEditar,$codtipoEditar,$serieEditar];
				$estado = $this->phuyu_model->phuyu_editar_1("caja.comprobantes", $campos, $valores, $f, $v);
			}
			if ($this->db->trans_status() === FALSE){
			    $this->db->trans_rollback(); $estado = 0;
			}else{
				if ($estado!=1) { 
					$this->db->trans_rollback(); $estado = 0; 
				}else{
					$this->db->trans_commit();
				}
			}
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}

	private function guardar_imagen_comprobante($campo, $prefijo){
		if (!isset($_FILES[$campo]) || $_FILES[$campo]["name"] == "") {
			return null;
		}

		if ($_FILES[$campo]["error"] !== UPLOAD_ERR_OK || !is_uploaded_file($_FILES[$campo]["tmp_name"])) {
			log_message("error", "Upload invalido en comprobantes. Campo=" . $campo . " error=" . $_FILES[$campo]["error"]);
			return false;
		}

		$destino = FCPATH . "public/img/empresa/";
		if (!is_dir($destino)) {
			@mkdir($destino, 0775, true);
		}
		if (!is_dir($destino) || !is_writable($destino)) {
			log_message("error", "Directorio de logos de comprobantes no escribible: " . $destino);
			return false;
		}

		$extension = strtolower(pathinfo($_FILES[$campo]["name"], PATHINFO_EXTENSION));
		$extensionesPermitidas = ["jpg", "jpeg", "png", "gif", "webp"];
		if (!in_array($extension, $extensionesPermitidas, true)) {
			log_message("error", "Extension de imagen no permitida en comprobantes. Campo=" . $campo . " extension=" . $extension);
			return false;
		}

		$nombre = $prefijo . "_comprobante_" . date("YmdHis") . "_" . mt_rand(1000, 9999) . "." . $extension;
		if (!@move_uploaded_file($_FILES[$campo]["tmp_name"], $destino . $nombre)) {
			log_message("error", "No se pudo guardar imagen de comprobante. Campo=" . $campo . " destino=" . $destino . $nombre);
			return false;
		}

		return $nombre;
	}

	private function clave_comprobante($codregistro){
		$partes = explode("-", (string)$codregistro);
		if (count($partes) >= 3) {
			return [
				"codsucursal" => (int)$partes[0],
				"codcomprobantetipo" => (int)$partes[1],
				"seriecomprobante" => strtoupper(trim($partes[2]))
			];
		}

		return [
			"codsucursal" => 0,
			"codcomprobantetipo" => isset($partes[0]) ? (int)$partes[0] : 0,
			"seriecomprobante" => isset($partes[1]) ? strtoupper(trim($partes[1])) : ""
		];
	}

	private function obtener_comprobante($codsucursal, $codcomprobantetipo, $seriecomprobante){
		if ((int)$codsucursal > 0) {
			$info = $this->db->query(
				"select codcomprobantetipo as codregistro,* from caja.comprobantes
				where codsucursal=? and codcomprobantetipo=? and seriecomprobante=?",
				[(int)$codsucursal, (int)$codcomprobantetipo, strtoupper(trim($seriecomprobante))]
			)->row_array();
		}else{
			$info = $this->db->query(
				"select codcomprobantetipo as codregistro,* from caja.comprobantes
				where codcomprobantetipo=? and seriecomprobante=?",
				[(int)$codcomprobantetipo, strtoupper(trim($seriecomprobante))]
			)->row_array();
		}
		return $info;
	}

	private function uso_comprobante($codsucursal, $codcomprobantetipo, $seriecomprobante){
		$params = [];
		$consultas = [
			["kardex.kardex", "codcomprobantetipo", "seriecomprobante", true],
			["kardex.kardex", "codcomprobantetipo_ref", "seriecomprobante_ref", true],
			["kardex.kardexalmacen", "codcomprobantetipo", "seriecomprobante", true],
			["kardex.pedidos", "codcomprobantetipo", "seriecomprobante", true],
			["kardex.pedidos", "codcomprobantetiporeferencia", "seriecomprobantereferencia", true],
			["kardex.proformas", "codcomprobantetipo", "seriecomprobante", true],
			["kardex.creditos", "codcomprobantetipo", "seriecomprobante", true],
			["kardex.creditospedidos", "codcomprobantetipo", "seriecomprobante", true],
			["kardex.creditosproformas", "codcomprobantetipo", "seriecomprobante", true],
			["kardex.cuotaspagos", "codcomprobantetipo", "seriecomprobante", true],
			["caja.movimientos", "codcomprobantetipo", "seriecomprobante", false],
			["caja.movimientos", "codcomprobantetipo_ref", "seriecomprobante_ref", false],
			["almacen.guiasr", "codcomprobantetipo", "seriecomprobante", true],
			["almacen.guiast", "codcomprobantetipo", "seriecomprobante", true]
		];

		$sql = [];
		foreach ($consultas as $consulta) {
			$filtroSucursal = $consulta[3] ? "codsucursal=? and " : "";
			$sql[] = "select count(*) as cantidad from ".$consulta[0]." where ".$filtroSucursal.$consulta[1]."=? and ".$consulta[2]."=?";
			if ($consulta[3]) {
				$params[] = (int)$codsucursal;
			}
			$params[] = (int)$codcomprobantetipo;
			$params[] = strtoupper(trim($seriecomprobante));
		}

		$uso = $this->db->query("select coalesce(sum(cantidad),0) as cantidad from (".implode(" union all ", $sql).") usos", $params)->row_array();
		return !empty($uso) ? (int)$uso["cantidad"] : 0;
	}

	private function comprobante_identidad_editable($comprobante){
		if (empty($comprobante)) {
			return false;
		}
		if ((int)$comprobante["nrocorrelativo"] !== 0) {
			return false;
		}
		return $this->uso_comprobante($comprobante["codsucursal"], $comprobante["codcomprobantetipo"], $comprobante["seriecomprobante"]) === 0;
	}

	function editar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$codigo = $this->clave_comprobante($this->request->codregistro);
			$info = $this->obtener_comprobante($codigo["codsucursal"], $codigo["codcomprobantetipo"], $codigo["seriecomprobante"]);
			echo json_encode(empty($info) ? [] : [$info]);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function validar_serie($serie){
		if ($this->input->is_ajax_request()) {
			$codigo = $this->clave_comprobante($serie);
			$comprobante = $this->obtener_comprobante($codigo["codsucursal"], $codigo["codcomprobantetipo"], $codigo["seriecomprobante"]);
			$uso = empty($comprobante) ? 0 : $this->uso_comprobante($comprobante["codsucursal"], $comprobante["codcomprobantetipo"], $comprobante["seriecomprobante"]);
			$editable = empty($comprobante) ? false : $this->comprobante_identidad_editable($comprobante);
			$data["serie"] = $codigo["seriecomprobante"];
			$data["estado"] = $uso;
			$data["editable_identidad"] = $editable ? 1 : 0;
			echo json_encode($data);
		}
	}

	function eliminar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$codigo = $this->clave_comprobante($this->request->codregistro);
			$comprobante = $this->obtener_comprobante($codigo["codsucursal"], $codigo["codcomprobantetipo"], $codigo["seriecomprobante"]);
			if (!empty($comprobante) && $this->uso_comprobante($comprobante["codsucursal"], $comprobante["codcomprobantetipo"], $comprobante["seriecomprobante"]) > 0) {
				echo 0;
				return;
			}

			$campos = ["estado"]; $valores = [0];
			$f = ["codsucursal","codcomprobantetipo","seriecomprobante"]; $v = [$codigo["codsucursal"],$codigo["codcomprobantetipo"],$codigo["seriecomprobante"]];
			$estado = $this->phuyu_model->phuyu_editar_1("caja.comprobantes", $campos, $valores, $f, $v);
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}
}
