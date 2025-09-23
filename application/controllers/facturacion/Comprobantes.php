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
			$lista = $this->db->query("select personas.documento,kardex.cliente,kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante,kardex.nrocomprobante, kardex.fechacomprobante,round(kardex.importe,2) as importe,comprobantes.descripcion as tipo, sunat.descripcion_cdr, sunat.estado from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) inner join sunat.kardexsunat as sunat on(kardex.codkardex=sunat.codkardex) where ".$fechas." (UPPER(personas.documento) like UPPER('%".$this->request->buscar."%') or UPPER(personas.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(kardex.cliente) like UPPER('%".$this->request->buscar."%') or UPPER(comprobantes.descripcion) like UPPER('%".$this->request->buscar."%') or UPPER(kardex.seriecomprobante) like UPPER('%".$this->request->buscar."%') or UPPER(kardex.nrocomprobante) like UPPER('%".$this->request->buscar."%') ) and kardex.codsucursal=".$_SESSION["phuyu_codsucursal"]." order by kardex.codkardex desc offset ".$offset." limit ".$limit)->result_array();

			$total = $this->db->query("select count(*) as total from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) inner join sunat.kardexsunat as sunat on(kardex.codkardex=sunat.codkardex) where ".$fechas." (UPPER(personas.documento) like UPPER('%".$this->request->buscar."%') or UPPER(personas.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(kardex.cliente) like UPPER('%".$this->request->buscar."%') or UPPER(comprobantes.descripcion) like UPPER('%".$this->request->buscar."%') or UPPER(kardex.seriecomprobante) like UPPER('%".$this->request->buscar."%') or UPPER(kardex.nrocomprobante) like UPPER('%".$this->request->buscar."%') ) and kardex.codsucursal=".$_SESSION["phuyu_codsucursal"])->result_array();

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
}