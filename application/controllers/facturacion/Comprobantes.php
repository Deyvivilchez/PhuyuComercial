<?php defined('BASEPATH') OR exit('No direct script access allowed');
include("Sunat.php");

class Comprobantes extends Sunat {

	public function __construct(){
		parent::__construct(); $this->load->model("Facturacion_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_codusuario"])) {
				$sucursal = $this->db->query("select *from public.sucursales where estado = 1")->result_array();
				$comprobantes = $this->db->query("select *from caja.comprobantetipos where codcomprobantetipo IN(10,12,14) and estado=1 ORDER BY codcomprobantetipo")->result_array();
				$this->load->view("facturacion/comprobantes/index",compact("sucursal","comprobantes"));
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

			if ($this->request->fechas->desde==$this->request->fechas->hasta) {
				$fechas = "kardex.fechacomprobante='".$this->request->fechas->desde."' and";
			}else{
				$fechas = "kardex.fechacomprobante>='".$this->request->fechas->desde."' and kardex.fechacomprobante<='".$this->request->fechas->hasta."' and";
			}
			$codsucursal = isset($this->request->sucursal) ? (int)$this->request->sucursal : (int)$_SESSION["phuyu_codsucursal"];
			$tipo = isset($this->request->comprobantetipo) ? (int)$this->request->comprobantetipo : 0;
			$estado = isset($this->request->estado_sunat) ? (string)$this->request->estado_sunat : "";
			$filtro_tipo = $tipo>0 ? " and kardex.codcomprobantetipo=".$tipo : "";
			$filtro_estado = ($estado==="0" || $estado==="1") ? " and sunat.estado=".(int)$estado : "";
			$filtro_habilitados = !empty($this->request->solo_habilitados) ? " and sunat.consulta_api_estado='0'" : "";
			$buscar = str_replace("'", "''", $this->db->escape_like_str(isset($this->request->buscar) ? $this->request->buscar : ""));
			$where = $fechas." (UPPER(personas.documento) like UPPER('%".$buscar."%') or UPPER(personas.razonsocial) like UPPER('%".$buscar."%') or UPPER(kardex.cliente) like UPPER('%".$buscar."%') or UPPER(comprobantes.descripcion) like UPPER('%".$buscar."%') or UPPER(kardex.seriecomprobante) like UPPER('%".$buscar."%') or UPPER(kardex.nrocomprobante) like UPPER('%".$buscar."%') or UPPER(coalesce(sunat.descripcion_cdr,'')) like UPPER('%".$buscar."%')) and kardex.codsucursal=".$codsucursal.$filtro_tipo.$filtro_estado.$filtro_habilitados;
			$lista = $this->db->query("select personas.documento,kardex.cliente,kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante,kardex.nrocomprobante, kardex.fechacomprobante,round(kardex.importe,2) as importe,comprobantes.descripcion as tipo, sunat.descripcion_cdr, sunat.estado from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) inner join sunat.kardexsunat as sunat on(kardex.codkardex=sunat.codkardex) where ".$where." order by kardex.codkardex desc offset ".$offset." limit ".$limit)->result_array();

			$total = $this->db->query("select count(*) as total from kardex.kardex as kardex inner join public.personas as personas on(kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) inner join sunat.kardexsunat as sunat on(kardex.codkardex=sunat.codkardex) where ".$where)->result_array();

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

	function phuyu_xml($codkardex){
		if (isset($_SESSION["phuyu_codusuario"])) {
			$info = $this->db->query("select ct.oficial from kardex.kardex as k inner join caja.comprobantetipos as ct on(k.codcomprobantetipo=ct.codcomprobantetipo) where k.codkardex=".$codkardex)->result_array();
			
			$estado = $this->Facturacion_model->phuyu_crearXML($info[0]["oficial"],$codkardex);
			if ($estado["estado"]!=0) {
				$firma = Sunat::phuyu_firmarXML($estado["carpeta_phuyu"]."/".$estado["archivo_phuyu"], 0);

				$this->load->helper("download"); 
				$descargar_ruta = file_get_contents($estado["carpeta_phuyu"]."/".$estado["archivo_phuyu"].".xml");
				force_download($estado["archivo_phuyu"].".xml", $descargar_ruta);
			}
		}
	}

	function phuyu_cdr($codkardex){
		if (isset($_SESSION["phuyu_codusuario"])) {
			$ruta = $this->db->query("select ruta_cdr from sunat.kardexsunat where codkardex=".$codkardex)->result_array();
			if ($ruta[0]["ruta_cdr"]!="") {
				$archivo = explode("R-",$ruta[0]["ruta_cdr"]);
				$this->load->helper("download"); 
				$descargar_ruta = file_get_contents($ruta[0]["ruta_cdr"].".zip");
				force_download("R-".$archivo[1].".zip", $descargar_ruta);
			}
		}
	}

	function phuyu_validar_sunat($codkardex){
		if (!$this->input->is_ajax_request() || !isset($_SESSION["phuyu_codusuario"])) {
			$this->output->set_status_header(403);
			echo json_encode(["estado" => 0, "nivel" => "danger", "mensaje" => "Sesion no valida"]);
			return;
		}

		$comprobante = $this->db->query(
			"select ct.oficial as tipo, k.seriecomprobante as serie, k.nrocomprobante as numero, " .
			"k.fechacomprobante as fecha, round(k.importe, 2) as importe " .
			"from kardex.kardex k inner join caja.comprobantetipos ct " .
			"on ct.codcomprobantetipo=k.codcomprobantetipo " .
			"where k.codkardex=".(int)$codkardex." and k.codsucursal=".(int)$_SESSION["phuyu_codsucursal"]." limit 1"
		)->result_array();

		if (count($comprobante)==0) {
			echo json_encode(["estado" => 0, "nivel" => "danger", "mensaje" => "Comprobante no encontrado"]);
			return;
		}

		$cpe = $comprobante[0];
		$empresa = $this->db->query("select sunat_api_client_id, sunat_api_client_secret from public.webservice where codempresa=".(int)$_SESSION["phuyu_codempresa"]." limit 1")->result_array();
		$api = count($empresa)>0 ? $empresa[0] : [];
		$datos = ["numRuc"=>$_SESSION["phuyu_ruc"], "codComp"=>$cpe["tipo"], "numeroSerie"=>strtoupper($cpe["serie"]), "numero"=>(int)$cpe["numero"], "fechaEmision"=>date("d/m/Y",strtotime($cpe["fecha"])), "monto"=>(double)$cpe["importe"]];
		$respuesta=Sunat::phuyu_consultaIntegradaSUNAT($datos, isset($api["sunat_api_client_id"])?$api["sunat_api_client_id"]:"", isset($api["sunat_api_client_secret"])?$api["sunat_api_client_secret"]:"", $_SESSION["phuyu_ruc"]);
		if (isset($respuesta["estado_cp"])) {
			$codigos=["NO EXISTE"=>"0","ACEPTADO"=>"1","ANULADO"=>"2","AUTORIZADO"=>"3","NO AUTORIZADO"=>"4"];
			$this->db->where("codkardex",(int)$codkardex)->update("sunat.kardexsunat",["consulta_api_estado"=>isset($codigos[$respuesta["estado_cp"]])?$codigos[$respuesta["estado_cp"]]:null,"consulta_api_fecha"=>date("Y-m-d H:i:s")]);
		}
		echo json_encode($respuesta);
	}

	function phuyu_reprogramar_resumen($codkardex){
		if (!$this->input->is_ajax_request() || !isset($_SESSION["phuyu_codusuario"])) {
			$this->output->set_status_header(403); echo json_encode(["estado"=>0,"mensaje"=>"Sesion no valida"]); return;
		}
		$codkardex=(int)$codkardex;
		$cpe=$this->db->query("select k.codkardex,k.seriecomprobante,k.nrocomprobante,k.fechacomprobante,round(k.importe,2) importe,ct.oficial tipo from kardex.kardex k inner join caja.comprobantetipos ct on ct.codcomprobantetipo=k.codcomprobantetipo where k.codkardex=".$codkardex." and k.codcomprobantetipo=12 and k.codsucursal=".(int)$_SESSION["phuyu_codsucursal"]." limit 1")->row_array();
		if (!$cpe) { echo json_encode(["estado"=>0,"mensaje"=>"La boleta no fue encontrada en esta sucursal."]); return; }
		$empresa=$this->db->query("select sunat_api_client_id,sunat_api_client_secret from public.webservice where codempresa=".(int)$_SESSION["phuyu_codempresa"]." limit 1")->row_array();
		$datos=["numRuc"=>$_SESSION["phuyu_ruc"],"codComp"=>$cpe["tipo"],"numeroSerie"=>strtoupper($cpe["seriecomprobante"]),"numero"=>(int)$cpe["nrocomprobante"],"fechaEmision"=>date("d/m/Y",strtotime($cpe["fechacomprobante"])),"monto"=>(double)$cpe["importe"]];
		$verificacion=Sunat::phuyu_consultaIntegradaSUNAT($datos,$empresa?$empresa["sunat_api_client_id"]:"",$empresa?$empresa["sunat_api_client_secret"]:"",$_SESSION["phuyu_ruc"]);
		if (!isset($verificacion["estado_cp"]) || $verificacion["estado_cp"]!=="NO EXISTE") {
			echo json_encode(["estado"=>0,"mensaje"=>"SUNAT ya no responde NO EXISTE. Estado actual: ".(isset($verificacion["mensaje"])?$verificacion["mensaje"]:"sin respuesta")]); return;
		}
		$referencias=$this->db->query("select distinct codresumentipo,periodo,nrocorrelativo,codempresa from sunat.kardexsunatdetalle where codkardex=".$codkardex)->result_array();
		$this->db->trans_begin();
		$this->db->where("codkardex",$codkardex)->delete("sunat.kardexsunatdetalle");
		$this->db->where("codkardex",$codkardex)->update("sunat.kardexsunat",["estado"=>0,"descripcion_cdr"=>"Pendiente de incluir en nuevo resumen; validado NO EXISTE mediante API SUNAT"]);
		foreach ($referencias as $ref) {
			$restantes=$this->db->where(["codresumentipo"=>$ref["codresumentipo"],"periodo"=>$ref["periodo"],"nrocorrelativo"=>$ref["nrocorrelativo"],"codempresa"=>$ref["codempresa"]])->count_all_results("sunat.kardexsunatdetalle");
			if ($restantes===0) {
				$this->db->where(["codresumentipo"=>$ref["codresumentipo"],"periodo"=>$ref["periodo"],"nrocorrelativo"=>$ref["nrocorrelativo"],"codempresa"=>$ref["codempresa"]])->where_not_in("estado",[1,2])->delete("sunat.resumenes");
			}
		}
		if ($this->db->trans_status()===false) { $this->db->trans_rollback(); echo json_encode(["estado"=>0,"mensaje"=>"No se pudo actualizar el resumen anterior."]); return; }
		$this->db->trans_commit();
		log_message("info","Boleta ".$codkardex." habilitada para nuevo resumen por usuario ".(int)$_SESSION["phuyu_codusuario"]." tras confirmar NO EXISTE en API SUNAT");
		echo json_encode(["estado"=>1,"mensaje"=>"La boleta quedó pendiente y podrá incluirse al generar nuevamente el resumen."]);
	}

	function phuyu_sincronizar_aceptado($codkardex){
		if (!$this->input->is_ajax_request() || !isset($_SESSION["phuyu_codusuario"])) {
			$this->output->set_status_header(403); echo json_encode(["estado"=>0,"mensaje"=>"Sesion no valida"]); return;
		}
		$codkardex=(int)$codkardex;
		$cpe=$this->db->query("select k.codkardex,k.seriecomprobante,k.nrocomprobante,k.fechacomprobante,round(k.importe,2) importe,ct.oficial tipo from kardex.kardex k inner join caja.comprobantetipos ct on ct.codcomprobantetipo=k.codcomprobantetipo where k.codkardex=".$codkardex." and k.codsucursal=".(int)$_SESSION["phuyu_codsucursal"]." limit 1")->row_array();
		if (!$cpe) { echo json_encode(["estado"=>0,"mensaje"=>"Comprobante no encontrado en esta sucursal."]); return; }
		$empresa=$this->db->query("select sunat_api_client_id,sunat_api_client_secret from public.webservice where codempresa=".(int)$_SESSION["phuyu_codempresa"]." limit 1")->row_array();
		$datos=["numRuc"=>$_SESSION["phuyu_ruc"],"codComp"=>$cpe["tipo"],"numeroSerie"=>strtoupper($cpe["seriecomprobante"]),"numero"=>(int)$cpe["nrocomprobante"],"fechaEmision"=>date("d/m/Y",strtotime($cpe["fechacomprobante"])),"monto"=>(double)$cpe["importe"]];
		$verificacion=Sunat::phuyu_consultaIntegradaSUNAT($datos,$empresa?$empresa["sunat_api_client_id"]:"",$empresa?$empresa["sunat_api_client_secret"]:"",$_SESSION["phuyu_ruc"]);
		if (!isset($verificacion["estado_cp"]) || $verificacion["estado_cp"]!=="ACEPTADO") {
			echo json_encode(["estado"=>0,"mensaje"=>"SUNAT no confirma ACEPTADO. Estado actual: ".(isset($verificacion["mensaje"])?$verificacion["mensaje"]:"sin respuesta")]); return;
		}
		$mensaje="Aceptado según Consulta Integrada API SUNAT el ".date("d/m/Y H:i:s");
		$this->db->where("codkardex",$codkardex)->update("sunat.kardexsunat",["estado"=>1,"descripcion_cdr"=>$mensaje]);
		if (!$this->db->affected_rows()) { echo json_encode(["estado"=>0,"mensaje"=>"No se pudo actualizar el estado local."]); return; }
		log_message("info","Comprobante ".$codkardex." sincronizado ACEPTADO por usuario ".(int)$_SESSION["phuyu_codusuario"]." tras validar API SUNAT");
		echo json_encode(["estado"=>1,"mensaje"=>$mensaje]);
	}

	private function phuyu_interpretar_respuesta_sunat($consulta){
		if (!isset($consulta["estado"]) || (int)$consulta["estado"]!==1) {
			$mensaje = isset($consulta["mensaje"]) ? trim(strip_tags((string)$consulta["mensaje"])) : "SUNAT no respondio";
			return ["estado" => 0, "nivel" => "warning", "mensaje" => "NO SE PUDO CONSULTAR", "detalle" => $mensaje];
		}

		$xml = isset($consulta["mensaje"]) ? (string)$consulta["mensaje"] : "";
		$codigo = "";
		$detalle = "";
		if ($xml!=="") {
			$dom = new DOMDocument();
			if (@$dom->loadXML($xml)) {
				$xpath = new DOMXPath($dom);
				$codigos = $xpath->query("//*[local-name()='statusCode']");
				$mensajes = $xpath->query("//*[local-name()='statusMessage']");
				if ($codigos->length>0) $codigo = trim($codigos->item(0)->textContent);
				if ($mensajes->length>0) $detalle = trim($mensajes->item(0)->textContent);
			}
		}

		$estados = [
			"0001" => [1, "success", "ACEPTADO"],
			"0002" => [0, "danger", "RECHAZADO"],
			"0003" => [0, "warning", "DE BAJA"],
			"0011" => [0, "danger", "NO EXISTE EN SUNAT"]
		];
		if (isset($estados[$codigo])) {
			return ["estado" => $estados[$codigo][0], "nivel" => $estados[$codigo][1], "mensaje" => $estados[$codigo][2], "detalle" => $detalle!=="" ? $detalle : "Codigo SUNAT: ".$codigo];
		}
		return ["estado" => 0, "nivel" => "warning", "mensaje" => "RESPUESTA SUNAT: ".($codigo!=="" ? $codigo : "DESCONOCIDA"), "detalle" => $detalle!=="" ? $detalle : "SUNAT devolvio una respuesta sin estado reconocible."];
	}

	private function phuyu_consulta_publica_sunat($cpe){
		if (!function_exists("curl_init")) {
			return ["estado" => 0, "nivel" => "danger", "mensaje" => "No se pudo conectar con SUNAT", "detalle" => "PHP no tiene cURL activo."];
		}

		$estados = ["0" => "NO EXISTE", "1" => "ACEPTADO", "2" => "ANULADO", "3" => "AUTORIZADO", "4" => "NO AUTORIZADO"];
		$post = [
			"numRuc" => $_SESSION["phuyu_ruc"],
			"codComp" => $cpe["tipo"],
			"numeroSerie" => strtoupper($cpe["serie"]),
			"numero" => str_pad((string)$cpe["numero"], 8, "0", STR_PAD_LEFT),
			"codDocRecep" => "",
			"numDocRecep" => "",
			"fechaEmision" => date("d/m/Y", strtotime($cpe["fecha"])),
			"monto" => number_format((double)$cpe["importe"], 2, ".", ""),
			"token" => substr(md5(uniqid(mt_rand(), true)).md5(uniqid(mt_rand(), true)), 0, 52)
		];

		$curl = curl_init("https://ww1.sunat.gob.pe/ol-ti-itconsultaunificadalibre/consultaUnificadaLibre/consultaIndividual");
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0");
		$respuesta = curl_exec($curl);
		$error = curl_error($curl);
		curl_close($curl);

		if ($respuesta===false || trim($respuesta)==="") {
			return ["estado" => 0, "nivel" => "warning", "mensaje" => "SUNAT sin respuesta", "detalle" => $error ?: "El portal publico no devolvio informacion."];
		}

		$data = json_decode(trim($respuesta), true);
		if (is_string($data)) $data = json_decode($data, true);
		if (!is_array($data)) {
			return ["estado" => 0, "nivel" => "warning", "mensaje" => "Respuesta temporal no valida", "detalle" => "SUNAT no devolvio el formato esperado. Vuelva a intentar."];
		}
		if (isset($data["rpta"]) && $data["rpta"]==="1" && isset($data["data"])) {
			$codigo = (string)$data["data"]["estadoCp"];
			$mensaje = isset($estados[$codigo]) ? $estados[$codigo] : $codigo;
			return ["estado" => $codigo==="1" ? 1 : 0, "nivel" => $codigo==="1" ? "success" : ($codigo==="0" ? "danger" : "warning"), "mensaje" => $mensaje, "detalle" => "Consulta directa realizada en SUNAT."];
		}
		if (isset($data["rpta"]) && $data["rpta"]==="3") {
			return ["estado" => 0, "nivel" => "danger", "mensaje" => "NO EXISTE EN SUNAT", "detalle" => "SUNAT no encontro el comprobante con esos datos."];
		}
		return ["estado" => 0, "nivel" => "warning", "mensaje" => "SUNAT no pudo procesar la consulta", "detalle" => isset($data["data"]) && is_string($data["data"]) ? $data["data"] : "Intente nuevamente en unos minutos."];
	}
}
