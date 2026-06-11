<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Cuentaspagar extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model"); $this->load->model("Caja_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$this->load->view("creditos/cuentaspagar/index");
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

			$lista = $this->db->query("select personas.*, lotes.descripcion as descripcionlote, lotes.direccion as direccionlote,lotes.codlote 
from public.socios as socios 
inner join public.personas as personas on (socios.codpersona=personas.codpersona) 
inner join public.lotes as lotes on (socios.codpersona=lotes.codsocio) 
where (UPPER(personas.documento) like UPPER('%".$this->request->buscar."%') or UPPER(personas.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(personas.nombrecomercial) like UPPER('%".$this->request->buscar."%') ) and (socios.codsociotipo=2 or socios.codsociotipo=3) and socios.estado=1 order by personas.codpersona desc offset ".$offset." limit ".$limit)->result_array();

			foreach ($lista as $key => $value) {
				$cantidad = $this->db->query("select count(*) as cantidad from kardex.creditos where codpersona=".$value["codpersona"]." and codsucursal=".$_SESSION["phuyu_codsucursal"]." and estado=1 and tipo=2 AND codlote=".(int)$value["codlote"])->result_array();
				$lista[$key]["creditos"] = $cantidad[0]["cantidad"];
			}

			$total = $this->db->query("select count(*) as total from public.socios as socios 
				inner join public.personas as personas on (socios.codpersona=personas.codpersona) 
				inner join public.lotes as lotes on (socios.codpersona=lotes.codsocio)
 				where (UPPER(personas.documento) like UPPER('%".$this->request->buscar."%') or UPPER(personas.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(personas.nombrecomercial) like UPPER('%".$this->request->buscar."%') ) and (socios.codsociotipo=2 or socios.codsociotipo=3) and socios.estado=1")->result_array();

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

	public function nuevo($codpersona,$codlote){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$tipopagos = $this->db->query("select *from caja.tipopagos where (egreso=1) and estado=1 order by codtipopago")->result_array();
				$persona = $this->db->query("select codpersona, razonsocial from public.personas where codpersona=".$codpersona)->result_array();
				$this->load->view("creditos/cuentaspagar/nuevo",compact("tipopagos","persona","codlote"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function guardar(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$this->request = json_decode(file_get_contents('php://input'));

				$this->db->trans_begin();

				// REGISTRO MOVIMIENTO CAJA //
				if ($this->request->campos->afectacaja == true) {
					$condicionpago = 1;
				}else{
					$condicionpago = 2;
				}

				$this->request->campos->codlote = (!isset($this->request->campos->codlote) || empty($this->request->campos->codlote)) ? 0 : $this->request->campos->codlote;

				$proveedor = $this->db->query("select razonsocial,direccion,documento,d.abreviatura as tipo from public.personas p inner join public.documentotipos d on(p.coddocumentotipo=d.coddocumentotipo) where p.codpersona=".$this->request->campos->codpersona)->result_array();

				$this->request->campos->cliente = $proveedor[0]["razonsocial"];
				$this->request->campos->direccion = $proveedor[0]["direccion"];
				$this->request->campos->documento = $proveedor[0]["tipo"].'-'.$proveedor[0]["documento"];
				
				$comprobante_ingresos = 1;
				$series = $this->db->query("select seriecomprobante from caja.comprobantes where codcomprobantetipo=".$comprobante_ingresos." and codsucursal=".$_SESSION["phuyu_codsucursal"]." and codcaja=".$_SESSION["phuyu_codcaja"]." and estado=1")->result_array();

				$campos = ["codcontroldiario","codcaja","codconcepto","codpersona","codusuario","codcomprobantetipo","seriecomprobante","tipomovimiento","codcomprobantetipo_ref","seriecomprobante_ref","nrocomprobante_ref","importe","referencia","condicionpago","cliente","direccion","codlote"];
				$valores = [
					(int)$_SESSION["phuyu_codcontroldiario"],
					(int)$_SESSION["phuyu_codcaja"],
					(int)$this->request->campos->codcajaconcepto,
					(int)$this->request->campos->codpersona,
					(int)$_SESSION["phuyu_codusuario"],
					(int)$comprobante_ingresos,
					$series[0]["seriecomprobante"],1,0,"","",
					(double)$this->request->campos->importe, "INGRESO POR CREDITO",
					(int)$condicionpago,$this->request->campos->cliente,$this->request->campos->direccion,
					(int)$this->request->campos->codlote
				];
				$codmovimiento = $this->phuyu_model->phuyu_guardar("caja.movimientos", $campos, $valores, "true");
				$estado = $this->Caja_model->phuyu_correlativo($codmovimiento,$comprobante_ingresos,$series[0]["seriecomprobante"]);

				if ($this->request->campos->afectacaja == true) {
					$campos = ["codmovimiento","codtipopago","codcontroldiario","codcaja","fechadocbanco","nrodocbanco","importe","importeentregado"];
					$valores = [
						(int)$codmovimiento,
						(int)$this->request->campos->codtipopago,
						(int)$_SESSION["phuyu_codcontroldiario"],
						(int)$_SESSION["phuyu_codcaja"],
						$this->request->campos->fechadocbanco,
						$this->request->campos->nrodocbanco,
						(double)$this->request->campos->importe,
						(double)$this->request->campos->importe
					];
					$estado = $this->phuyu_model->phuyu_guardar("caja.movimientosdetalle", $campos, $valores);
				}

				// REGISTRO DEL CREDITO //

				$campos = ["codsucursal","codcaja","codcreditoconcepto","codpersona","codmovimiento","codusuario","tipo","fechacredito","fechainicio","nrodias","nrocuotas","importe","tasainteres","interes","saldo","total","referencia","cliente","direccion","documento","codlote"];
				$valores = [
					(int)$_SESSION["phuyu_codsucursal"],
					(int)$_SESSION["phuyu_codcaja"],
					(int)$this->request->campos->codcreditoconcepto,
					(int)$this->request->campos->codpersona,
					(int)$codmovimiento,
					(int)$_SESSION["phuyu_codusuario"],2,
					$this->request->campos->fechacredito,
					$this->request->campos->fechainicio,
					(int)$this->request->campos->nrodias,
					(int)$this->request->campos->nrocuotas,
					(double)$this->request->campos->importe,
					(double)$this->request->campos->tasainteres,
					(double)$this->request->campos->interes,
					(double)$this->request->campos->total,
					(double)$this->request->campos->total,
					$this->request->campos->referencia,
					$this->request->campos->cliente,$this->request->campos->direccion,$this->request->campos->documento,(int)$this->request->campos->codlote
				];

				if($this->request->campos->codregistro=="") {
					$codcredito = $this->phuyu_model->phuyu_guardar("kardex.creditos", $campos, $valores, "true");

					foreach ($this->request->cuotas as $key => $value) {
						$campos = ["codcredito","nrocuota","codsucursal","fechavence","nroletra","nrounicodepago","importe","saldo","interes","total"];
						$valores = [
							(int)$codcredito,
							(int)$this->request->cuotas[$key]->nrocuota,
							(int)$_SESSION["phuyu_codsucursal"],
							$this->request->cuotas[$key]->fechavence,
							$this->request->cuotas[$key]->nroletra,
							$this->request->cuotas[$key]->nrounicodepago,
							(double)$this->request->cuotas[$key]->importe,
							(double)$this->request->cuotas[$key]->total,
							(double)$this->request->cuotas[$key]->interes,
							(double)$this->request->cuotas[$key]->total
						];
						$estado = $this->phuyu_model->phuyu_guardar("kardex.cuotas", $campos, $valores);
						$fechavence = $this->request->cuotas[$key]->fechavence;
					}

					$actual = $this->db->query("select seriecomprobante,nrocorrelativo,ct.abreviatura from caja.comprobantes c inner join caja.comprobantetipos ct on(c.codcomprobantetipo=ct.codcomprobantetipo) where c.codcomprobantetipo=26 and codsucursal=".$_SESSION["phuyu_codsucursal"]." and c.estado=1")->result_array();

					$nrocorrelativo = (int)($actual[0]["nrocorrelativo"]) + 1;
					$seriecomprobante = $actual[0]["seriecomprobante"];
					$tipocomprobante = $actual[0]["abreviatura"];
					$data = array(
						"nrocorrelativo" => $nrocorrelativo
					);
					$this->db->where("codsucursal", $_SESSION["phuyu_codsucursal"]);
					$this->db->where("codcomprobantetipo", 26);
					$estado = $this->db->update("caja.comprobantes", $data);

					$nrocorrelativo = str_pad($nrocorrelativo, 8, "0", STR_PAD_LEFT);

					$campos = ["fechavencimiento","codcomprobantetipo","seriecomprobante","nrocomprobante","comprobantereferencia"]; $valores = [$fechavence,26,$seriecomprobante,$nrocorrelativo,$tipocomprobante.'-'.$seriecomprobante.'-'.$nrocorrelativo];
					$estado = $this->phuyu_model->phuyu_editar("kardex.creditos", $campos, $valores, "codcredito", $codcredito);
				}else{
					$estado = $this->phuyu_model->phuyu_editar("kardex.creditos", $campos, $valores, "codcredito", $this->request->campos->codregistro);
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
				echo "e";
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function cobranza($codpersona,$codlote){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$tipopagos = $this->db->query("select *from caja.tipopagos where (egreso=1) and estado=1 order by codtipopago")->result_array();
				$persona = $this->db->query("select codpersona, razonsocial from public.personas where codpersona=".$codpersona)->result_array();
				$monedas = $this->db->query("select *from caja.monedas where estado=1 order by codmoneda asc")->result_array();
				$this->load->view("creditos/cuentaspagar/pagos",compact("tipopagos","persona","codlote","monedas"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function cuotas($codpersona,$codlote){
		if ($this->input->is_ajax_request()) {
			$where = ''; $join = '';
			if($codlote!=0){
				$where.=' AND codlote='.$codlote;
			}
			$totalc = 0;
			$totalcobrado = 0;
			$totalsaldo = 0;
			$cuotas = $this->db->query("select cuo.codcredito,cuo.nrocuota,cuo.fechavence,cuo.fecha,round(cuo.saldo,2) as saldo,round(cuo.total,2) as total,cre.codkardex,cre.codlote from kardex.creditos as cre inner join kardex.cuotas as cuo on(cre.codcredito=cuo.codcredito) where cre.codpersona=".$codpersona." and cre.codsucursal=".$_SESSION["phuyu_codsucursal"]." and cre.estado=1 and cre.tipo=2 and cuo.estado=1 ".$where." order by cre.codcredito,cuo.nrocuota")->result_array();
			foreach ($cuotas as $key => $value) {
				$comprobante = $this->db->query("select k.seriecomprobante as serie,k.nrocomprobante as nro, ct.abreviatura from kardex.kardex as k inner join caja.comprobantetipos as ct on(k.codcomprobantetipo=ct.codcomprobantetipo) where k.codkardex=".(int)$value["codkardex"])->result_array();
				
				$lineascredito = $this->db->query("select *from public.lotes where codlote=".(int)$value["codlote"])->result_array();

				if(count($lineascredito)>0){
					$cuotas[$key]["linea"] = $lineascredito[0]["codlote"];
				}else{
					$cuotas[$key]["linea"] = 'SIN LINEA';
				}

				if (count($comprobante)>0) {
					$cuotas[$key]["comprobante"] = $comprobante[0]["abreviatura"]."-".$comprobante[0]["serie"]."-".(int)$comprobante[0]["nro"];
				}else{
					$cuotas[$key]["comprobante"] = "CRED. DIRECTO";
				}
				$total = $this->db->query("select round(COALESCE(sum(importe),0),2) as cobrado from kardex.cuotaspagos where codcredito=".$value["codcredito"]." and nrocuota=".$value["nrocuota"]." and estado=1")->result_array();
				$cuotas[$key]["cobrado"] = $total[0]["cobrado"];

				$totalc = $totalc + $value["total"];
				$totalcobrado = (double)$totalcobrado + (double)$cuotas[$key]["cobrado"];
				$totalsaldo = (double)$totalsaldo + (double)$value["saldo"];
			}

			$totalcuotas[0]["total"] = number_format($totalc,2,".","");
			$totalcuotas[0]["cobrado"] = number_format($totalcobrado,2,".","");
			$totalcuotas[0]["saldo"] = number_format($totalsaldo,2,".","");

			echo json_encode(["cuotas"=>$cuotas,"totales"=>$totalcuotas]);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function pagar(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$this->request = json_decode(file_get_contents('php://input'));

				$this->db->trans_begin();

				$proveedor = $this->db->query("select *from public.personas where codpersona=".$this->request->campos->codpersona)->result_array();

				$this->request->campos->cliente = $proveedor[0]["razonsocial"];
				$this->request->campos->direccion = $proveedor[0]["direccion"];

				$campos = ["codcontroldiario","codcaja","codconcepto","codpersona","codusuario","codcomprobantetipo","seriecomprobante","tipomovimiento","importe","referencia","codcomprobantetipo_ref","seriecomprobante_ref","nrocomprobante_ref","fechamovimiento","cliente","direccion","codlote"];

				$series = $this->db->query("select seriecomprobante from caja.comprobantes where codcomprobantetipo=".$this->request->campos->codcomprobantetipo." and codsucursal=".$_SESSION["phuyu_codsucursal"]." and codcaja=".$_SESSION["phuyu_codcaja"]." and estado=1")->result_array();

				$this->request->campos->fechamovimiento = date('Y-m-d');

				$valores = [
					(int)$_SESSION["phuyu_codcontroldiario"],
					(int)$_SESSION["phuyu_codcaja"],
					(int)$this->request->campos->codconcepto,
					(int)$this->request->campos->codpersona,
					(int)$_SESSION["phuyu_codusuario"],
					(int)$this->request->campos->codcomprobantetipo,
					$series[0]["seriecomprobante"],2,
					(double)$this->request->campos->total*(double)$this->request->campos->tipocambio,
					$this->request->campos->descripcion,
					18,"REF",$this->request->campos->nrodocbanco,$this->request->campos->fechamovimiento,
					$this->request->campos->cliente,$this->request->campos->direccion,
					$this->request->campos->codlote,
					$this->request->campos->codmoneda,
					$this->request->campos->tipocambio,
					(double)$this->request->campos->total
				];
				
				$codmovimiento = $this->phuyu_model->phuyu_guardar("caja.movimientos", $campos, $valores, "true");
				$estado = $this->Caja_model->phuyu_correlativo($codmovimiento,$this->request->campos->codcomprobantetipo,$series[0]["seriecomprobante"]);

				$campos = ["codmovimiento","codtipopago","codcontroldiario","codcaja","fechadocbanco","nrodocbanco","importe","importeentregado","vuelto","codctacte"];
				$valores = [
					(int)$codmovimiento,
					(int)$this->request->campos->codtipopago,
					(int)$_SESSION["phuyu_codcontroldiario"],
					(int)$_SESSION["phuyu_codcaja"],
					$this->request->campos->fechadocbanco,
					$this->request->campos->nrodocbanco,
					(double)$this->request->campos->total*(double)$this->request->campos->tipocambio,
					(double)$this->request->campos->importe,
					(double)$this->request->campos->vuelto,
					(int)$this->request->campos->codctacte
				];
				$estado = $this->phuyu_model->phuyu_guardar("caja.movimientosdetalle", $campos, $valores);

				$actual = $this->db->query("select seriecomprobante,nrocorrelativo from caja.comprobantes where codcomprobantetipo=28 and codsucursal=".$_SESSION["phuyu_codsucursal"]." and estado=1")->result_array();

				$nrocorrelativo = (int)($actual[0]["nrocorrelativo"]) + 1;
				$seriecomprobante = $actual[0]["seriecomprobante"];
				$data = array(
					"nrocorrelativo" => $nrocorrelativo
				);
				$this->db->where("codsucursal", $_SESSION["phuyu_codsucursal"]);
				$this->db->where("codcomprobantetipo", 28);
				$estado = $this->db->update("caja.comprobantes", $data);

				$nrocorrelativo = str_pad($nrocorrelativo, 8, "0", STR_PAD_LEFT);

				foreach ($this->request->cuotas as $key => $value) {
					$campos = ["codcredito","nrocuota","codsucursal","codmovimiento","codusuario","importe","saldocuota","codcomprobantetipo","seriecomprobante","nrocomprobante"];
					$valores =[
						(int)$this->request->cuotas[$key]->codcredito,
						(int)$this->request->cuotas[$key]->nrocuota,
						(int)$_SESSION["phuyu_codsucursal"],
						(int)$codmovimiento,
						(int)$_SESSION["phuyu_codusuario"],
						(double)$this->request->cuotas[$key]->cobrar,
						(double)$this->request->cuotas[$key]->saldo,28,
						$seriecomprobante,
						$nrocorrelativo
					];
					$estado = $this->phuyu_model->phuyu_guardar("kardex.cuotaspagos", $campos, $valores);

					if ( (double)$this->request->cuotas[$key]->saldo==0 ) {
						$campos = ["saldo","estado"]; $valores = [(double)$this->request->cuotas[$key]->saldo,0];
					}else{
						$campos = ["saldo"]; $valores = [(double)$this->request->cuotas[$key]->saldo];
					}
					$f = ["codcredito","nrocuota"]; 
					$v = [(int)$this->request->cuotas[$key]->codcredito,(int)$this->request->cuotas[$key]->nrocuota];
					$estado = $this->phuyu_model->phuyu_editar_1("kardex.cuotas", $campos, $valores, $f, $v);

					// ACTUALIZAMOS EL CREDITO //

					$cobrado = $this->db->query("select count(*) as cantidad from kardex.cuotas where codcredito=".$this->request->cuotas[$key]->codcredito." and estado=1")->result_array();
					if ($cobrado[0]["cantidad"]==0) {
						$campos = ["saldo","estado"]; $valores = [0,2];
					}else{
						$credito = $this->db->query("select saldo from kardex.creditos where codcredito=".$this->request->cuotas[$key]->codcredito)->result_array();
						$campos = ["saldo"]; $valores = [(double)$credito[0]["saldo"] - (double)$this->request->cuotas[$key]->cobrar];
					}
					$estado = $this->phuyu_model->phuyu_editar("kardex.creditos", $campos, $valores, "codcredito", $this->request->cuotas[$key]->codcredito);
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
				echo "e";
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function historial($codpersona){
		if ($this->input->is_ajax_request()) {
			$persona = $this->db->query("select codpersona, razonsocial from public.personas where codpersona=".$codpersona)->result_array();
			$this->load->view("creditos/cuentaspagar/historial",compact("persona"));
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function verificar_edicion(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$pagos = $this->db->query("select count(*) as cantidad from kardex.cuotaspagos where codcredito=".$this->request->codregistro." and estado=1")->result_array();
			if($pagos[0]["cantidad"]>0){
				echo 0;exit;
			}
			$creditos = $this->db->query("select *from kardex.creditos where codcredito=".$this->request->codregistro." AND estado=1")->result_array();

			if(count($creditos)==0){
				echo 0;exit;
			}
			echo 1;
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function editar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$creditos = $this->db->query("select *from kardex.creditos where codcredito=".$this->request->codregistro." AND estado=1")->result_array();
			foreach ($creditos as $key => $value) {
				if($value["codkardex"]>0){
					$comprobante = $this->db->query("select seriecomprobante,nrocomprobante from kardex.kardex where codkardex=".$value["codkardex"])->result_array();
					$referencia = $comprobante[0]["seriecomprobante"].'-'.$comprobante[0]["nrocomprobante"];
				}else{
					$referencia = 'CREDITO DIRECTO';
				}
				$creditos[$key]["refere"] = $referencia;
			}
			$lotes = $this->db->query("select *from public.lotes where codsocio=".$creditos[0]["codpersona"])->result_array();
			$tipopagos = $this->db->query("select *from caja.tipopagos where (ingreso=1 or abono=1) and estado=1 order by codtipopago")->result_array();
			$empleados = $this->db->query("select empleado.codpersona,persona.documento,persona.razonsocial from public.personas as persona inner join public.empleados as empleado on(persona.codpersona= empleado.codpersona) where empleado.estado=1 and empleado.codpersona>0")->result_array();
			$conceptos = $this->db->query("select *from kardex.creditoconceptos where tipo=2 AND estado = 1")->result_array();
			$monedas = $this->db->query("select *from caja.monedas where estado=1 order by codmoneda asc")->result_array();
			$this->load->view("creditos/cuentaspagar/editar.php",compact("tipopagos","empleados","lotes","creditos","conceptos","monedas"));
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function editarcredito(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$creditos = $this->db->query("select *from kardex.creditos where codcredito=".$this->request->codregistro." AND estado=1")->result_array();
			$movimiento = $this->db->query("select *from caja.movimientosdetalle where codmovimiento=".$creditos[0]["codmovimiento"]." AND estado=1")->result_array();
			$cuotas = $this->db->query("select *from kardex.cuotas where codcredito = ".$this->request->codregistro)->result_array();
			echo json_encode(["creditos"=>$creditos,"movimiento"=>$movimiento,"cuotas"=>$cuotas]);
		}
	}

	function editarcambios(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$this->request = json_decode(file_get_contents('php://input'));

				$this->db->trans_begin();

				// REGISTRO MOVIMIENTO CAJA //

				$this->request->campos->codlote = (!isset($this->request->campos->codlote) || empty($this->request->campos->codlote)) ? 0 : $this->request->campos->codlote;

				$comprobante_egresos = 2;

				$campos = ["importe","codlote"];
				$valores = [
					(double)$this->request->campos->importe, 
					(int)$this->request->campos->codlote
				];
				$estado = $this->phuyu_model->phuyu_editar("caja.movimientos", $campos, $valores, "codmovimiento", $this->request->campos->codmovimiento);

				$detalle = $this->db->query("select *from caja.movimientosdetalle where codmovimiento=".$this->request->campos->codmovimiento)->result_array();

				if (count($detalle)>0) {
					$campos = ["codtipopago","fechadocbanco","nrodocbanco","importe","importeentregado"];
					$valores = [
						(int)$this->request->campos->codtipopago,
						$this->request->campos->fechadocbanco,
						$this->request->campos->nrodocbanco,
						(double)$this->request->campos->importe,
						(double)$this->request->campos->importe
					];
					$estado = $this->phuyu_model->phuyu_editar("caja.movimientosdetalle", $campos, $valores, "codmovimiento", $this->request->campos->codmovimiento);
				}
				
				// REGISTRO DEL CREDITO //

				$this->request->campos->referencia = (isset($this->request->campos->referencia) || !empty($this->request->campos->referencia)) ? $this->request->campos->referencia : "";

				$campos = ["codsucursal","codcaja","codcreditoconcepto","codpersona","codempleado","codmovimiento","codusuario","tipo","fechacredito","fechainicio","nrodias","nrocuotas","importe","tasainteres","interes","saldo","total","tipocuota","referencia","nrotarjeta","codlote","codmoneda","tipocambio","creditoprogramado"];
				$valores = [
					(int)$_SESSION["phuyu_codsucursal"],
					(int)$_SESSION["phuyu_codcaja"],
					(int)$this->request->campos->codcreditoconcepto,
					(int)$this->request->campos->codpersona,
					(int)$this->request->campos->codempleado,
					(int)$this->request->campos->codmovimiento,
					(int)$_SESSION["phuyu_codusuario"],2,
					$this->request->campos->fechacredito,
					$this->request->campos->fechainicio,
					(int)$this->request->campos->nrodias,
					(int)$this->request->campos->nrocuotas,
					(double)$this->request->campos->importe,
					(double)$this->request->campos->tasainteres,
					(double)$this->request->campos->interes,
					(double)$this->request->campos->total,
					(double)$this->request->campos->total,
					(int)$this->request->campos->tipocuota,
					$this->request->campos->referencia,
					$this->request->campos->nrotarjeta,
					$this->request->campos->codlote,$this->request->campos->codmoneda,
					$this->request->campos->tipocambio,
					$this->request->campos->creditoprogramado
				];

				$estado = $this->phuyu_model->phuyu_editar("kardex.creditos", $campos, $valores, "codcredito", $this->request->campos->codregistro);

				$estado = $this->phuyu_model->phuyu_eliminar_total("kardex.cuotas","codcredito",$this->request->campos->codregistro);

				foreach ($this->request->cuotas as $key => $value) {
					$campos = ["codcredito","nrocuota","codsucursal","fechavence","importe","saldo","interes","total","nroletra","nrounicodepago"];
					$valores = [
						(int)$this->request->campos->codregistro,
						(int)$this->request->cuotas[$key]->nrocuota,
						(int)$_SESSION["phuyu_codsucursal"],
						$this->request->cuotas[$key]->fechavence,
						(double)$this->request->cuotas[$key]->importe,
						(double)$this->request->cuotas[$key]->total,
						(double)$this->request->cuotas[$key]->interes,
						(double)$this->request->cuotas[$key]->total,
						$this->request->cuotas[$key]->nroletra,
						$this->request->cuotas[$key]->nrounicodepago
					];
					$estado = $this->phuyu_model->phuyu_guardar("kardex.cuotas", $campos, $valores);
					$fechavence = $this->request->cuotas[$key]->fechavence;
				}

				$campos = ["fechavencimiento"]; $valores = [$fechavence];
				$estado = $this->phuyu_model->phuyu_editar("kardex.creditos", $campos, $valores, "codcredito", $this->request->campos->codregistro);

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
				echo "e";
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function anularpago(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$cuotas = $this->db->query("select c.codcredito,cp.nrocuota, cp.importe from kardex.creditos as c inner join kardex.cuotaspagos as cp on(c.codcredito=cp.codcredito) where cp.codmovimiento=".$this->request->codmovimiento)->result_array();
			foreach ($cuotas as $key => $value) {
				$info = $this->db->query("select *from kardex.cuotas where codcredito=".$value["codcredito"]." and nrocuota=".$value["nrocuota"])->result_array();
				$saldo = round($info[0]["saldo"] + $value["importe"],2);

				$campos = ["saldo","estado"]; $valores = [(double)$saldo,1];
				$f = ["codcredito","nrocuota"]; 
				$v = [(int)$value["codcredito"],(int)$value["nrocuota"]];
				$estado = $this->phuyu_model->phuyu_editar_1("kardex.cuotas", $campos, $valores, $f, $v);

				// ACTUALIZAMOS EL CREDITO //
				$credito = $this->db->query("select saldo from kardex.creditos where codcredito=".$value["codcredito"])->result_array();
				$campos = ["saldo","estado"]; $valores = [(double)$credito[0]["saldo"] + (double)$value["importe"],1];
				
				$estado = $this->phuyu_model->phuyu_editar("kardex.creditos", $campos, $valores, "codcredito", $value["codcredito"]);
			}

			$campos = ["estado"]; $valores = [0];
			$f = ["codmovimiento"]; $v = [(int)$this->request->codmovimiento];
			$estado = $this->phuyu_model->phuyu_editar_1("kardex.cuotaspagos", $campos, $valores, $f, $v);

			$estado = $this->phuyu_model->phuyu_eliminar("caja.movimientos", "codmovimiento", $this->request->codmovimiento);

			echo $estado;
		}
	}

	function eliminar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			$pagos = $this->db->query("select count(*) as cantidad from kardex.cuotaspagos where codcredito=".$this->request->codregistro." and estado=1")->result_array();
			if ($pagos[0]["cantidad"]==0) {
				$estado = $this->phuyu_model->phuyu_eliminar("kardex.creditos", "codcredito", $this->request->codregistro);

				$movimiento = $this->db->query("select codmovimiento from kardex.creditos where codcredito=".$this->request->codregistro)->result_array();
				$estado = $this->phuyu_model->phuyu_eliminar("caja.movimientos", "codmovimiento", $movimiento[0]["codmovimiento"]);

				// REGISTRAMOS EL CREDITO ANULADO EN CREDITOS ANULADOS //
				
				$campos = ["codcredito","codsucursal","fechaanulacion","codusuario"];
				$valores =[
					(int)$this->request->codregistro,
					(int)$_SESSION["phuyu_codsucursal"],date("Y-m-d"),
					(int)$_SESSION["phuyu_codusuario"]
				];
				$estado = $this->phuyu_model->phuyu_guardar("kardex.creditosanulados", $campos, $valores);
			}else{
				$estado = 0;
			}
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}
}