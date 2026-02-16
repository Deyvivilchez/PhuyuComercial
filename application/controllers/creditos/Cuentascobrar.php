<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Cuentascobrar extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model"); $this->load->model("Caja_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$zonas = $this->db->query("select zonas.descripcion,uz.codzona from public.usuariozonas as uz inner join public.zonas as zonas ON (uz.codzona=zonas.codzona) WHERE codpersona =".$_SESSION["phuyu_codusuario"])->result_array();

				$comprobante_almacen = $this->db->query("select count(*) as cantidad from caja.comprobantes where codcomprobantetipo=26 and codsucursal=".$_SESSION["phuyu_codsucursal"]." and estado=1")->result_array();

				$comprobante = $comprobante_almacen[0]["cantidad"];

				$this->load->view("creditos/cuentascobrar/index",compact("zonas","comprobante"));
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
			$limit = 10; $offset = $this->request->pagina * $limit - $limit;$where=" AND personas.codzona IN(";

			if($_SESSION["phuyu_rubro"]==5){
				if($this->request->zonas == 0 || $this->request->zonas==""){
					$sql = $this->db->query("select *from public.usuariozonas WHERE codpersona=".$_SESSION["phuyu_codusuario"])->result_array();

					if(count($sql)>0){
						$i = 0;
						foreach ($sql as $key => $value) {
							if($i==0){
								$where.= $value["codzona"];
							} else{
								$where.= ','.$value["codzona"];
							}
							$i++;
						}
						$where.=')';
					}else{
						$where.='0)';
					}
				}else{
					$where.=$this->request->zonas.')';
				}
			}else{
				$where = '';
			}

			$lista = $this->db->query("select personas.*, lotes.descripcion as descripcionlote, lotes.direccion as direccionlote,lotes.codlote 
from public.socios as socios 
inner join public.personas as personas on (socios.codpersona=personas.codpersona) 
inner join public.lotes as lotes on (socios.codpersona=lotes.codsocio) 
where (UPPER(personas.documento) like UPPER('%".$this->request->buscar."%') or UPPER(personas.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(personas.nombrecomercial) like UPPER('%".$this->request->buscar."%') ) and (socios.codsociotipo=1 or socios.codsociotipo=3) and socios.codpersona not in (SELECT codpersona  FROM empresas) and socios.estado=1 order by personas.codpersona desc offset ".$offset." limit ".$limit)->result_array();

			foreach ($lista as $key => $value) {
				$cantidad = $this->db->query("select count(*) as cantidad from kardex.creditos where codpersona=".$value["codpersona"]." and codsucursal=".$_SESSION["phuyu_codsucursal"]." and estado=1 and tipo=1 AND codlote=".(int)$value["codlote"])->result_array();
				$lista[$key]["creditos"] = $cantidad[0]["cantidad"];
			}

			$total = $this->db->query("select count(*) as total from public.socios as socios 
				inner join public.personas as personas on (socios.codpersona=personas.codpersona) 
				inner join public.lotes as lotes on (socios.codpersona=lotes.codsocio)
			 where (socios.codsociotipo=1 or socios.codsociotipo=3) and socios.codpersona not in (SELECT codpersona  FROM empresas) and socios.estado=1 ".$where." ")->result_array();

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

	function conciliar($codregistro){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_codusuario"])){
				$fecha = date('Y-m-d');
				$detalle = $this->db->query("select c.codcredito, sum(c.importe) as importe, sum(c.interes) as interes, sum(round((c.importe*c.tasainteres/100)*('".$fecha."' - c.fechainicio)/30, 2)) as interesactual, 
					sum(c.saldo) as saldo, sum(c.total - coalesce(ccp.importepagado, 0.0000)) as saldo_tot_cob, sum(c.importe + c.interes - coalesce(ccp.importepagado, 0.0000)) as saldo_imp_int_cob, 
					sum(c.saldomora) as saldomora, sum(c.total) as total, sum(c.importe + c.interes) as total_imp_int, sum(coalesce(ccp.importepagado, 0.0000)) AS importepagado from kardex.creditos c   
					left join caja.v_cuotaspagosxcredito ccp ON c.codcredito = ccp.codcredito  where c.codsucursal=1 and c.tipo=2 and c.codpersona=".$codregistro." and c.estado>=1
					GROUP BY c.codcredito")->result_array();
				$this->load->view("creditos/cuentascobrar/conciliar",compact("detalle")); 
			}else{
	            $this->load->view("phuyu/505");
	        }
	    }else{
			$this->load->view("phuyu/404");
		}
	}

	public function nuevo($codpersona,$codlote){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$tipopagos = $this->db->query("select *from caja.tipopagos where (ingreso=1 or abono=1) and estado=1 order by codtipopago")->result_array();
				$persona = $this->db->query("select codpersona, razonsocial from public.personas where codpersona=".$codpersona)->result_array();
				$empleados = $this->db->query("select empleado.codpersona,persona.documento,persona.razonsocial from public.personas as persona inner join public.empleados as empleado on(persona.codpersona= empleado.codpersona) where empleado.estado=1 and empleado.codpersona>0")->result_array();
				$monedas = $this->db->query("select *from caja.monedas where estado=1 order by codmoneda asc")->result_array();
				$this->load->view("creditos/cuentascobrar/nuevo",compact("tipopagos","persona","empleados","codlote","monedas"));
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

				$comprobante_egresos = 2;
				$series = $this->db->query("select seriecomprobante from caja.comprobantes where codcomprobantetipo=".$comprobante_egresos." and codsucursal=".$_SESSION["phuyu_codsucursal"]." and codcaja=".$_SESSION["phuyu_codcaja"]." and estado=1")->result_array();

				$campos = ["codcontroldiario","codcaja","codconcepto","codpersona","codusuario","codcomprobantetipo","seriecomprobante","tipomovimiento","codcomprobantetipo_ref","seriecomprobante_ref","nrocomprobante_ref","importe","referencia","condicionpago","codlote","cliente","direccion"];
				$valores = [
					(int)$_SESSION["phuyu_codcontroldiario"],
					(int)$_SESSION["phuyu_codcaja"],
					(int)$this->request->campos->codcajaconcepto,
					(int)$this->request->campos->codpersona,
					(int)$_SESSION["phuyu_codusuario"],
					(int)$comprobante_egresos,
					$series[0]["seriecomprobante"],2,0,"","",
					(double)$this->request->campos->importe, "EGRESO POR CREDITO",
					(int)$condicionpago,
					(int)$this->request->campos->codlote,$this->request->campos->cliente,$this->request->campos->direccion
				];
				$codmovimiento = $this->phuyu_model->phuyu_guardar("caja.movimientos", $campos, $valores, "true");
				$estado = $this->Caja_model->phuyu_correlativo($codmovimiento,$comprobante_egresos,$series[0]["seriecomprobante"]);

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

				$campos = ["codsucursal","codcaja","codcreditoconcepto","codpersona","codempleado","codmovimiento","codusuario","tipo","fechacredito","fechainicio","nrodias","nrocuotas","importe","tasainteres","interes","saldo","total","tipocuota","referencia","nrotarjeta","codlote","cliente","direccion","documento"];
				$valores = [
					(int)$_SESSION["phuyu_codsucursal"],
					(int)$_SESSION["phuyu_codcaja"],
					(int)$this->request->campos->codcreditoconcepto,
					(int)$this->request->campos->codpersona,
					(int)$this->request->campos->codempleado,
					(int)$codmovimiento,
					(int)$_SESSION["phuyu_codusuario"],1,
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
					$this->request->campos->codlote,
					$this->request->campos->cliente,
					$this->request->campos->direccion,
					$this->request->campos->documento
				];

				if($this->request->campos->codregistro=="") {
					$codcredito = $this->phuyu_model->phuyu_guardar("kardex.creditos", $campos, $valores, "true");

					foreach ($this->request->cuotas as $key => $value) {
						$campos = ["codcredito","nrocuota","codsucursal","fechavence","importe","saldo","interes","total"];
						$valores = [
							(int)$codcredito,
							(int)$this->request->cuotas[$key]->nrocuota,
							(int)$_SESSION["phuyu_codsucursal"],
							$this->request->cuotas[$key]->fechavence,
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

				$campos = ["codsucursal","codcaja","codcreditoconcepto","codpersona","codempleado","codmovimiento","codusuario","tipo","fechacredito","fechainicio","nrodias","nrocuotas","importe","tasainteres","interes","saldo","total","tipocuota","referencia","nrotarjeta","codlote"];
				$valores = [
					(int)$_SESSION["phuyu_codsucursal"],
					(int)$_SESSION["phuyu_codcaja"],
					(int)$this->request->campos->codcreditoconcepto,
					(int)$this->request->campos->codpersona,
					(int)$this->request->campos->codempleado,
					(int)$this->request->campos->codmovimiento,
					(int)$_SESSION["phuyu_codusuario"],1,
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
					$this->request->campos->codlote
				];

				$estado = $this->phuyu_model->phuyu_editar("kardex.creditos", $campos, $valores, "codcredito", $this->request->campos->codregistro);

				$estado = $this->phuyu_model->phuyu_eliminar_total("kardex.cuotas","codcredito",$this->request->campos->codregistro);

				foreach ($this->request->cuotas as $key => $value) {
					$campos = ["codcredito","nrocuota","codsucursal","fechavence","importe","saldo","interes","total"];
					$valores = [
						(int)$this->request->campos->codregistro,
						(int)$this->request->cuotas[$key]->nrocuota,
						(int)$_SESSION["phuyu_codsucursal"],
						$this->request->cuotas[$key]->fechavence,
						(double)$this->request->cuotas[$key]->importe,
						(double)$this->request->cuotas[$key]->total,
						(double)$this->request->cuotas[$key]->interes,
						(double)$this->request->cuotas[$key]->total
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

	public function cobranza($codpersona,$codlote){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$tipopagos = $this->db->query("select *from caja.tipopagos where (ingreso=1 or abono=1) and estado=1 order by codtipopago")->result_array();
				$persona = $this->db->query("select codpersona, razonsocial from public.personas where codpersona=".$codpersona)->result_array();
				$lineascredito = $this->db->query("select *from public.lotes where codsocio=".$codpersona)->result_array();
				$monedas = $this->db->query("select *from caja.monedas where estado=1 order by codmoneda asc")->result_array();
				$this->load->view("creditos/cuentascobrar/cobranza",compact("tipopagos","persona","codlote","monedas"));
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
			$cuotas = $this->db->query("select cuo.codcredito,cuo.nrocuota,cuo.fechavence,cuo.fecha,round(cuo.saldo,2) as saldo,round(cuo.total,2) as total,cre.codkardex,cre.codlote from kardex.creditos as cre inner join kardex.cuotas as cuo on(cre.codcredito=cuo.codcredito) where cre.codpersona=".$codpersona." and cre.codsucursal=".$_SESSION["phuyu_codsucursal"]." and cre.estado=1 and cre.tipo=1 and cuo.estado=1 ".$where." order by cre.codcredito, cuo.nrocuota")->result_array();
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

				$campos = ["codcontroldiario","codcaja","codconcepto","codpersona","codusuario","codcomprobantetipo","seriecomprobante","tipomovimiento","importe","referencia","codcomprobantetipo_ref","seriecomprobante_ref","nrocomprobante_ref","fechamovimiento","cliente","direccion","codlote","codmoneda","tipocambio","importemoneda","cobrado","codempleado"];
				$series = $this->db->query("select seriecomprobante from caja.comprobantes where codcomprobantetipo=".$this->request->campos->codcomprobantetipo." and codsucursal=".$_SESSION["phuyu_codsucursal"]." and codcaja=".$_SESSION["phuyu_codcaja"]." and estado=1")->result_array();

				$valores = [
					(int)$_SESSION["phuyu_codcontroldiario"],
					(int)$_SESSION["phuyu_codcaja"],
					(int)$this->request->campos->codconcepto,
					(int)$this->request->campos->codpersona,
					(int)$_SESSION["phuyu_codusuario"],
					(int)$this->request->campos->codcomprobantetipo,
					$series[0]["seriecomprobante"],1,
					(double)$this->request->campos->total*(double)$this->request->campos->tipocambio,
					$this->request->campos->descripcion,
					18,"REF",$this->request->campos->nrodocbanco,$this->request->campos->fechamovimiento,
					$this->request->campos->cliente,$this->request->campos->direccion,
					$this->request->campos->codlote,
					$this->request->campos->codmoneda,
					$this->request->campos->tipocambio,
					(double)$this->request->campos->total,
					(int)$this->request->campos->cobrado,$_SESSION["phuyu_codempleado"]
				];

				$codmovimiento = $this->phuyu_model->phuyu_guardar("caja.movimientos", $campos, $valores, "true");
				$estado = $this->Caja_model->phuyu_correlativo($codmovimiento,$this->request->campos->codcomprobantetipo,$series[0]["seriecomprobante"]);

				$campos = ["codmovimiento","codtipopago","codcontroldiario","codcaja","fechadocbanco","nrodocbanco","importe","importeentregado","vuelto"];
				$valores = [
					(int)$codmovimiento,
					(int)$this->request->campos->codtipopago,
					(int)$_SESSION["phuyu_codcontroldiario"],
					(int)$_SESSION["phuyu_codcaja"],
					$this->request->campos->fechadocbanco,
					$this->request->campos->nrodocbanco,
					(double)$this->request->campos->total*(double)$this->request->campos->tipocambio,
					(double)$this->request->campos->importe,
					(double)$this->request->campos->vuelto
				];
				$estado = $this->phuyu_model->phuyu_guardar("caja.movimientosdetalle", $campos, $valores);

				$actual = $this->db->query("select seriecomprobante,nrocorrelativo from caja.comprobantes where codcomprobantetipo=27 and codsucursal=".$_SESSION["phuyu_codsucursal"]." and estado=1")->result_array();

				$nrocorrelativo = (int)($actual[0]["nrocorrelativo"]) + 1;
				$seriecomprobante = $actual[0]["seriecomprobante"];
				$data = array(
					"nrocorrelativo" => $nrocorrelativo
				);
				$this->db->where("codsucursal", $_SESSION["phuyu_codsucursal"]);
				$this->db->where("codcomprobantetipo", 27);
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
						(double)$this->request->cuotas[$key]->saldo,27,
						$actual[0]["seriecomprobante"],
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
			$this->load->view("creditos/cuentascobrar/historial",compact("persona"));
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function filtro_creditos(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			if ($this->request->filtro==1) {
				$filtro = " fechacredito>='".$this->request->fechadesde."' and fechacredito<='".$this->request->fechahasta."' and ";
			}else{
				$filtro = "";
			}

			if ($this->request->estado!="") {
				$filtro = $filtro." estado=".$this->request->estado." and ";
			}

			$creditos = $this->db->query("select codcredito,fechacredito,nrocuotas,round(importe,2) as importe,round(tasainteres,2) as tasainteres,round(interes,2) as interes,round(total,2) as total, round(saldo,2) as saldo, referencia, estado,nrotarjeta,comprobantereferencia from kardex.creditos where codpersona=".$this->request->codpersona." AND codlote=".(int)$this->request->codlote." and codsucursal=".$_SESSION["phuyu_codsucursal"]." and ".$filtro." tipo=".$this->request->tipo." order by codcredito")->result_array();

			$importe = 0; $interes = 0; $total = 0; $cobrado = 0; $saldo = 0;
			foreach ($creditos as $key => $value) {
				$cobranza = $this->db->query("select round(COALESCE(sum(importe),0),2) as total from kardex.cuotaspagos where codcredito=".$value["codcredito"]." and estado=1")->result_array();
				$creditos[$key]["cobrado"] = $cobranza[0]["total"];

				if ((int)$value["estado"]!=0) {
					$importe = $importe + (double)$value["importe"];
					$interes = $interes + (double)$value["interes"];
					$total = $total + (double)$value["total"];
					$cobrado = $cobrado + (double)$cobranza[0]["total"];
					$saldo = $saldo + (double)$value["saldo"];
				}
			}
			$totales = $this->db->query("select ".number_format($importe,2,".","")." as importe, ".number_format($interes,2,".","")." as interes, ".number_format($total,2,".","")." as total, ".number_format($cobrado,2,".","")." as cobrado, ".number_format($saldo,2,".","")." as saldo")->result_array();

			$data["creditos"] = $creditos;
			$data["totales"] = $totales;
			echo json_encode($data);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function filtro_pagos_cobros(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			$pagos_cobros = $this->db->query("select distinct(m.codmovimiento),m.fechamovimiento,round(m.importe,2) as importe from caja.movimientos as m inner join kardex.cuotaspagos as cp on(m.codmovimiento=cp.codmovimiento) inner join kardex.creditos as c on(c.codcredito=cp.codcredito) where  m.fechamovimiento>='".$this->request->fechadesde."' and m.fechamovimiento<='".$this->request->fechahasta."' and c.estado<>0 and c.tipo=".$this->request->tipo." and c.codpersona=".$this->request->codpersona." and m.estado=1 order by m.fechamovimiento desc")->result_array();
			foreach ($pagos_cobros as $key => $value) {
				$cuotas = $this->db->query("select c.fechacredito,cp.codcredito, cp.nrocuota, round(cp.importe,2) as importe from kardex.creditos as c inner join kardex.cuotaspagos as cp on(c.codcredito=cp.codcredito) where cp.codmovimiento=".$value["codmovimiento"])->result_array();
				$pagos_cobros[$key]["cuotas"] = $cuotas;
			}
			echo json_encode($pagos_cobros);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function anularcobro(){
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
			$lotes = $this->db->query("select *from public.lotes where codsocio=".$creditos[0]["codpersona"])->result_array();
			$tipopagos = $this->db->query("select *from caja.tipopagos where (ingreso=1 or abono=1) and estado=1 order by codtipopago")->result_array();
			$empleados = $this->db->query("select empleado.codpersona,persona.documento,persona.razonsocial from public.personas as persona inner join public.empleados as empleado on(persona.codpersona= empleado.codpersona) where empleado.estado=1 and empleado.codpersona>0")->result_array();
			$conceptos = $this->db->query("select *from kardex.creditoconceptos where tipo=1 AND estado = 1")->result_array();
			$monedas = $this->db->query("select *from caja.monedas where estado=1 order by codmoneda asc")->result_array();
			$this->load->view("creditos/cuentascobrar/editar.php",compact("tipopagos","empleados","lotes","creditos","conceptos","monedas"));
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function editarcredito(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$creditos = $this->db->query("select *from kardex.creditos where codcredito=".$this->request->codregistro." AND estado=1")->result_array();
			$cuotas = $this->db->query("select *from kardex.cuotas where codcredito = ".$this->request->codregistro)->result_array();
			$movimiento = $this->db->query("select *from caja.movimientosdetalle where codmovimiento=".$creditos[0]["codmovimiento"]." AND estado=1")->result_array();
			
			echo json_encode(["creditos"=>$creditos,"movimiento"=>$movimiento,"cuotas"=>$cuotas]);
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
				
				$campos = ["codcredito","codsucursal","fechaanulacion","codusuario","observaciones"];
				$valores =[
					(int)$this->request->codregistro,
					(int)$_SESSION["phuyu_codsucursal"],date("Y-m-d"),
					(int)$_SESSION["phuyu_codusuario"],
					$this->request->observaciones
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