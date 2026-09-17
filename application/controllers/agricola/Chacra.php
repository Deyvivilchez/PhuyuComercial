<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Chacra extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model"); $this->load->model("Caja_model"); $this->load->model("Kardex_model");
		$this->load->model("Creditos_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_codusuario"])) {
				$comprobante_almacen = $this->db->query("select count(*) as cantidad from caja.comprobantes where codcomprobantetipo=26 and codsucursal=".$_SESSION["phuyu_codsucursal"]." and estado=1")->result_array();
				$comprobante = $comprobante_almacen[0]["cantidad"];
				$this->load->view("agricola/chacra/index",compact("comprobante"));
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

			if ($this->request->fechas->filtro == 0) {
				$fechas = "";
			}else{
				if(!empty($this->request->fechas->desde)){
					$fechas = "lotes.fechainicio>='".$this->request->fechas->desde."' and lotes.fechainicio<='".$this->request->fechas->hasta."' and";
				}else{
					$fechas = "lotes.fechainicio<='".$this->request->fechas->hasta."' and";
				}
			}
			$lista = $this->db->query("select lotes.*, round(lotes.area,2) as area,ubigeo.departamento,ubigeo.provincia,ubigeo.distrito, zona.descripcion as zona, personas.razonsocial as socio from public.lotes as lotes inner join public.personas as personas on (lotes.codsocio=personas.codpersona) inner join public.ubigeo as ubigeo on (lotes.codubigeo=ubigeo.codubigeo) inner join public.zonas as zona on (ubigeo.codubigeo=zona.codubigeo) where ".$fechas." (UPPER(personas.documento) like UPPER('%".$this->request->buscar."%') or UPPER(personas.razonsocial) like UPPER('%".$this->request->buscar."%') ) AND lotes.codsocio = 1 order by lotes.codlote desc offset ".$offset." limit ".$limit)->result_array();

			$total = $this->db->query("select count(*) as total from public.lotes as lotes inner join public.personas as personas on (lotes.codsocio=personas.codpersona) 
				inner join public.ubigeo as ubigeo on (lotes.codubigeo=ubigeo.codubigeo) 
				inner join public.zonas as zona on (ubigeo.codubigeo=zona.codubigeo)
				where ".$fechas." (UPPER(personas.documento) like UPPER('%".$this->request->buscar."%') or UPPER(personas.razonsocial) like UPPER('%".$this->request->buscar."%') ) AND lotes.codsocio = 1")->result_array();

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

	public function asignargasto($codpersona,$codlote){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$tipopagos = $this->db->query("select *from caja.tipopagos where (egreso=1) and estado=1 order by codtipopago")->result_array();
				$persona = $this->db->query("select codpersona, razonsocial from public.personas where codpersona=".$codpersona)->result_array();
				$empleados = $this->db->query("select empleado.codpersona,persona.documento,persona.razonsocial from public.personas as persona inner join public.empleados as empleado on(persona.codpersona= empleado.codpersona) where empleado.estado=1 and empleado.codpersona>0")->result_array();
				$monedas = $this->db->query("select *from caja.monedas where estado=1 order by codmoneda asc")->result_array();
				$this->load->view("agricola/chacra/gasto",compact("tipopagos","persona","empleados","codlote","monedas"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function guardargasto(){
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

	public function nuevasalida(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$movimientos = $this->db->query("select *from almacen.movimientotipos where codmovimientotipo<>20 and tipo=2 and estado=1")->result_array();
				$serie = $this->db->query("select ct.abreviatura as comprobante,c.codcomprobantetipo, c.seriecomprobante from caja.comprobantes as c inner join caja.comprobantetipos as ct on(c.codcomprobantetipo=ct.codcomprobantetipo) where c.codcomprobantetipo=4 and c.codsucursal=".$_SESSION["phuyu_codsucursal"]." and c.codalmacen=".$_SESSION["phuyu_codalmacen"]." and c.estado=1")->result_array();
				$tipocomprobantes = $this->db->query("select *from caja.comprobantetipos where egresoalmacen=1 and estado=1 order by codcomprobantetipo")->result_array();
				$almacenes = $this->db->query("select almacen.*, sucursal.descripcion as sucursal from almacen.almacenes as almacen inner join public.sucursales as sucursal on(almacen.codsucursal=sucursal.codsucursal) where almacen.codalmacen<>".$_SESSION["phuyu_codalmacen"]." and almacen.estado=1")->result_array();
				$this->load->view("agricola/chacra/salidaproductos",compact("movimientos","serie","tipocomprobantes","almacenes"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function guardarsalida(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$this->request = json_decode(file_get_contents('php://input'));

				$this->db->trans_begin();

				$proveedor = $this->db->query("select razonsocial,direccion,documento,d.abreviatura as tipo from public.personas p inner join public.documentotipos d on(p.coddocumentotipo=d.coddocumentotipo) where p.codpersona=".$this->request->campos->codpersona)->result_array();

				$this->request->campos->cliente = $proveedor[0]["razonsocial"];
				$this->request->campos->direccion = $proveedor[0]["direccion"];

				// REGISTRO KARDEX //
				$campos = ["codsucursal","codalmacen","codalmacen_ref","codpersona","condicionpago","codusuario","codmovimientotipo","fechakardex","fechacomprobante","codcomprobantetipo","seriecomprobante","codcomprobantetipo_ref","seriecomprobante_ref","nrocomprobante_ref","valorventa","porcigv","igv","importe","descripcion","cliente","direccion","codlote"];
				$valores = [
					(int)$_SESSION["phuyu_codsucursal"],
					(int)$_SESSION["phuyu_codalmacen"],
					(int)$this->request->campos->codalmacen_ref,
					(int)$this->request->campos->codpersona,2,
					(int)$_SESSION["phuyu_codusuario"],
					(int)$this->request->campos->codmovimientotipo,
					$this->request->campos->fechakardex,$this->request->campos->fechakardex,
					(int)$this->request->campos->codcomprobantetipo,
					$this->request->campos->seriecomprobante,
					(int)$this->request->campos->codcomprobantetipo_ref,
					$this->request->campos->seriecomprobante_ref,
					$this->request->campos->nrocomprobante_ref,
					(double)$this->request->totales->valorventa,
					(double)$_SESSION["phuyu_igv"],
					(double)$this->request->totales->igv,
					(double)$this->request->totales->importe,
					$this->request->campos->descripcion,$this->request->campos->cliente,
					$this->request->campos->direccion,
					(int)$this->request->campos->codlote
				];
				$codkardex = $this->phuyu_model->phuyu_guardar("kardex.kardex", $campos, $valores, "true");

				// REGISTRO KARDEX ALMACEN //
				$campos = ["codsucursal","codalmacen","codalmacen_ref","codkardex","codusuario","codmovimientotipo","fechakardex","codcomprobantetipo","seriecomprobante"];
				$valores = [
					(int)$_SESSION["phuyu_codsucursal"],
					(int)$_SESSION["phuyu_codalmacen"],
					(int)$this->request->campos->codalmacen_ref,
					(int)$codkardex,
					(int)$_SESSION["phuyu_codusuario"],
					(int)$this->request->campos->codmovimientotipo,
					$this->request->campos->fechakardex,
					(int)$this->request->campos->codcomprobantetipo,
					$this->request->campos->seriecomprobante
				];
				$codkardexalmacen = $this->phuyu_model->phuyu_guardar("kardex.kardexalmacen", $campos, $valores, "true");
				
				$nro_comprobante = $this->Kardex_model->phuyu_kardexcorrelativo($codkardex,$codkardexalmacen,$this->request->campos->codcomprobantetipo,$this->request->campos->seriecomprobante);

				// REGISTRO KARDEX DETALLE Y KARDEX ALMACEN DETALLE //
				$item = 0;
				$informacion['success'] = true;
				foreach ($this->request->detalle as $key => $value) { 
					$item = $item + 1;
					$campos = ["codkardex","codproducto","codunidad","item","cantidad","preciobruto","preciosinigv","preciounitario","preciorefunitario","codafectacionigv","valorventa","subtotal"];
					$valores =[
						(int)$codkardex,
						(int)$this->request->detalle[$key]->codproducto,
						(int)$this->request->detalle[$key]->codunidad, $item,
						(double)$this->request->detalle[$key]->cantidad,
						(double)$this->request->detalle[$key]->precio,
						(double)$this->request->detalle[$key]->precio,
						(double)$this->request->detalle[$key]->precio,
						(double)$this->request->detalle[$key]->precio,'10',
						(double)$this->request->detalle[$key]->subtotal,
						(double)$this->request->detalle[$key]->subtotal
					];
					$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexdetalle", $campos, $valores);

					$campos =["codkardexalmacen","codproducto","codunidad","item","codalmacen","codsucursal","cantidad"];
					$valores =[
						(int)$codkardexalmacen,
						(int)$this->request->detalle[$key]->codproducto,
						(int)$this->request->detalle[$key]->codunidad, $item,
						(int)$_SESSION["phuyu_codalmacen"],
						(int)$_SESSION["phuyu_codsucursal"],
						(double)$this->request->detalle[$key]->cantidad
					];
					$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexalmacendetalle", $campos, $valores);

					$existe = $this->db->query("select *from almacen.productoubicacion where codalmacen=".$_SESSION["phuyu_codalmacen"]." and codproducto=".$this->request->detalle[$key]->codproducto." and codunidad=".$this->request->detalle[$key]->codunidad)->result_array();

                    if( $_SESSION["phuyu_stockalmacen"] == 1 && ($existe[0]["stockactualconvertido"] < $this->request->detalle[$key]->cantidad)){
						$informacion['success'] = false;
						$informacion['stock'][$key] = $existe[0]["stockactualconvertido"];
						$informacion['producto'][$key] = $this->request->detalle[$key]->producto;
						$informacion['unidad'][$key] = $this->request->detalle[$key]->unidad;
					}

					$stock = round($existe[0]["stockactual"] - $this->request->detalle[$key]->cantidad,3);

					$campos = ["stockactual"]; $valores = [(double)$stock];
					$f = ["codalmacen","codproducto","codunidad"]; 
					$v = [(int)$_SESSION["phuyu_codalmacen"],(int)$this->request->detalle[$key]->codproducto,(int)$this->request->detalle[$key]->codunidad];
					$estado = $this->phuyu_model->phuyu_editar_1("almacen.productoubicacion", $campos, $valores, $f, $v);

					// DISMINUIMOS EL STOCKACTUALCONVERTIDO

					$stockconvertido = $this->db->query("select *from almacen.productoubicacion where codalmacen=".$_SESSION["phuyu_codalmacen"]." and codproducto=".$this->request->detalle[$key]->codproducto)->result_array();

					$factor = $this->db->query("select *from almacen.productounidades where codproducto=".$this->request->detalle[$key]->codproducto." and codunidad=".$this->request->detalle[$key]->codunidad)->result_array();

					foreach ($stockconvertido as $k => $val) {
						$stockc = 0;
						$productounidad = $this->db->query("select *from almacen.productounidades where codproducto=".$this->request->detalle[$key]->codproducto." and codunidad=".$val["codunidad"])->result_array();
						$stockc = ((float)$this->request->detalle[$key]->cantidad*(float)$factor[0]["factor"])/(float)$productounidad[0]["factor"];
						
                        $stockc = (double)$val["stockactualconvertido"] - $stockc;
						$campos = ["stockactualconvertido"]; $valores = [(double)$stockc];
						$f = ["codalmacen","codproducto","codunidad"]; 
						$v = [(int)$_SESSION["phuyu_codalmacen"],(int)$this->request->detalle[$key]->codproducto,(int)$val["codunidad"]];
						$estado = $this->phuyu_model->phuyu_editar_1("almacen.productoubicacion", $campos, $valores, $f, $v);
					}
				}

				$this->request->campos->codconcepto = 6;
				$this->request->campos->condicionpago = 2;
				$this->request->campos->tipocambio = 1;
				$this->request->campos->codcreditoconcepto = 2;
				$this->request->campos->codmoneda = 1;
				$this->request->campos->fechacomprobante = date('Y-m-d');
				$this->request->campos->nrodias = 30;
				$this->request->campos->nrocuotas = 1;
				$this->request->campos->tasainteres = 0;
				$this->request->totales->interes = 0;
				$this->request->campos->totalcredito = $this->request->totales->importe;

				$codmovimiento = $this->Caja_model->phuyu_movimientos($codkardex, 1, 1, $this->request->totales->importe, $this->request->campos,$this->request->totales->importe);

				$this->request->pagos->monto_efectivo = $this->request->totales->importe;
				
				$estado = $this->Caja_model->phuyu_movimientosdetalle($codmovimiento, $this->request->pagos);

				$persona = $this->db->query("select documento,d.abreviatura as tipo from public.personas p inner join public.documentotipos d on(p.coddocumentotipo=d.coddocumentotipo) where p.codpersona=".$this->request->campos->codpersona)->result_array();

				$this->request->cuotas[0]->fechavence=date('Y-m-d');
				$this->request->cuotas[0]->importe=$this->request->totales->importe;
				$this->request->cuotas[0]->interes = 0;
				$this->request->cuotas[0]->total = $this->request->totales->importe;

				$estado = $this->Caja_model->phuyu_credito($codkardex, $codmovimiento, 2, $this->request->campos, $this->request->totales, $this->request->cuotas,$persona[0]["tipo"].'-'.$persona[0]["documento"]);

				//print_r($informacion);exit;

				if(!$informacion['success']){
					$data["estado"] = 2; $data["informacion"] = $informacion;
				    echo json_encode($data);exit;
				}

				if ($this->db->trans_status() === FALSE){
				    $this->db->trans_rollback(); $estado = 0;
				}else{
					if ($estado!=1) {
						$this->db->trans_rollback(); $estado = 0;
					}
					$this->db->trans_commit();
				}
				$data["estado"] = $estado;
				echo json_encode($data);
			}else{
				echo "e";
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function nuevoingreso(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$movimientos = $this->db->query("select *from almacen.movimientotipos where codmovimientotipo<>2 and tipo=1 and estado=1")->result_array();
				$serie = $this->db->query("select ct.abreviatura as comprobante,c.codcomprobantetipo, c.seriecomprobante from caja.comprobantes as c inner join caja.comprobantetipos as ct on(c.codcomprobantetipo=ct.codcomprobantetipo) where c.codcomprobantetipo=3 and c.codsucursal=".$_SESSION["phuyu_codsucursal"]." and c.codalmacen=".$_SESSION["phuyu_codalmacen"]." and c.estado=1")->result_array();
				$tipocomprobantes = $this->db->query("select *from caja.comprobantetipos where ingresoalmacen=1 and estado=1 order by codcomprobantetipo")->result_array();
				$almacenes = $this->db->query("select almacen.*, sucursal.descripcion as sucursal from almacen.almacenes as almacen inner join public.sucursales as sucursal on(almacen.codsucursal=sucursal.codsucursal) where almacen.codalmacen<>".$_SESSION["phuyu_codalmacen"]." and almacen.estado=1")->result_array();
				$this->load->view("agricola/chacra/produccion",compact("movimientos","serie","tipocomprobantes","almacenes"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function guardaringreso(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$this->request = json_decode(file_get_contents('php://input'));

				$this->db->trans_begin();

				$proveedor = $this->db->query("select razonsocial,direccion,documento,d.abreviatura as tipo from public.personas p inner join public.documentotipos d on(p.coddocumentotipo=d.coddocumentotipo) where p.codpersona=".$this->request->campos->codpersona)->result_array();

				$this->request->campos->cliente = $proveedor[0]["razonsocial"];
				$this->request->campos->direccion = $proveedor[0]["direccion"];

				// REGISTRO KARDEX //
				$campos = ["codsucursal","codalmacen","codalmacen_ref","codpersona","condicionpago","codusuario","codmovimientotipo","fechakardex","fechacomprobante","codcomprobantetipo","seriecomprobante","codcomprobantetipo_ref","seriecomprobante_ref","nrocomprobante_ref","valorventa","porcigv","igv","importe","descripcion","cliente","direccion","codlote"];
				$valores = [
					(int)$_SESSION["phuyu_codsucursal"],
					(int)$_SESSION["phuyu_codalmacen"],
					(int)$this->request->campos->codalmacen_ref,
					(int)$this->request->campos->codpersona,2,
					(int)$_SESSION["phuyu_codusuario"],
					(int)$this->request->campos->codmovimientotipo,
					$this->request->campos->fechakardex,$this->request->campos->fechakardex,
					(int)$this->request->campos->codcomprobantetipo,
					$this->request->campos->seriecomprobante,
					(int)$this->request->campos->codcomprobantetipo_ref,
					$this->request->campos->seriecomprobante_ref,
					$this->request->campos->nrocomprobante_ref,
					(double)$this->request->totales->valorventa,
					(double)$_SESSION["phuyu_igv"],
					(double)$this->request->totales->igv,
					(double)$this->request->totales->importe,
					$this->request->campos->descripcion,$this->request->campos->cliente,
					$this->request->campos->direccion,
					(int)$this->request->campos->codlote
				];
				$codkardex = $this->phuyu_model->phuyu_guardar("kardex.kardex", $campos, $valores, "true");

				// REGISTRO KARDEX ALMACEN //
				$campos = ["codsucursal","codalmacen","codalmacen_ref","codkardex","codusuario","codmovimientotipo","fechakardex","codcomprobantetipo","seriecomprobante"];
				$valores = [
					(int)$_SESSION["phuyu_codsucursal"],
					(int)$_SESSION["phuyu_codalmacen"],
					(int)$this->request->campos->codalmacen_ref,
					(int)$codkardex,
					(int)$_SESSION["phuyu_codusuario"],
					(int)$this->request->campos->codmovimientotipo,
					$this->request->campos->fechakardex,
					(int)$this->request->campos->codcomprobantetipo,
					$this->request->campos->seriecomprobante
				];
				$codkardexalmacen = $this->phuyu_model->phuyu_guardar("kardex.kardexalmacen", $campos, $valores, "true");
				
				$nro_comprobante = $this->Kardex_model->phuyu_kardexcorrelativo($codkardex,$codkardexalmacen,$this->request->campos->codcomprobantetipo,$this->request->campos->seriecomprobante);

				// REGISTRO KARDEX DETALLE Y KARDEX ALMACEN DETALLE //
				$item = 0;
				$informacion['success'] = true;
				foreach ($this->request->detalle as $key => $value) { 
					$item = $item + 1;
					$campos = ["codkardex","codproducto","codunidad","item","cantidad","preciobruto","preciosinigv","preciounitario","preciorefunitario","codafectacionigv","valorventa","subtotal","cantidadporcentaje","cantidaddescuento","cantidadbruta"];
					$valores =[
						(int)$codkardex,
						(int)$this->request->detalle[$key]->codproducto,
						(int)$this->request->detalle[$key]->codunidad, $item,
						(double)$this->request->detalle[$key]->cantidad,
						(double)$this->request->detalle[$key]->precio,
						(double)$this->request->detalle[$key]->precio,
						(double)$this->request->detalle[$key]->precio,
						(double)$this->request->detalle[$key]->precio,'10',
						(double)$this->request->detalle[$key]->subtotal,
						(double)$this->request->detalle[$key]->subtotal
					];
					$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexdetalle", $campos, $valores);

					$campos =["codkardexalmacen","codproducto","codunidad","item","codalmacen","codsucursal","cantidad"];
					$valores =[
						(int)$codkardexalmacen,
						(int)$this->request->detalle[$key]->codproducto,
						(int)$this->request->detalle[$key]->codunidad, $item,
						(int)$_SESSION["phuyu_codalmacen"],
						(int)$_SESSION["phuyu_codsucursal"],
						(double)$this->request->detalle[$key]->cantidad
					];
					$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexalmacendetalle", $campos, $valores);

					$existe = $this->db->query("select *from almacen.productoubicacion where codalmacen=".$_SESSION["phuyu_codalmacen"]." and codproducto=".$this->request->detalle[$key]->codproducto." and codunidad=".$this->request->detalle[$key]->codunidad)->result_array();

					$stock = round($existe[0]["stockactual"] + $this->request->detalle[$key]->cantidad,3);

					$campos = ["stockactual"]; $valores = [(double)$stock];
					$f = ["codalmacen","codproducto","codunidad"]; 
					$v = [(int)$_SESSION["phuyu_codalmacen"],(int)$this->request->detalle[$key]->codproducto,(int)$this->request->detalle[$key]->codunidad];
					$estado = $this->phuyu_model->phuyu_editar_1("almacen.productoubicacion", $campos, $valores, $f, $v);

					// DISMINUIMOS EL STOCKACTUALCONVERTIDO

					$stockconvertido = $this->db->query("select *from almacen.productoubicacion where codalmacen=".$_SESSION["phuyu_codalmacen"]." and codproducto=".$this->request->detalle[$key]->codproducto)->result_array();

					$factor = $this->db->query("select *from almacen.productounidades where codproducto=".$this->request->detalle[$key]->codproducto." and codunidad=".$this->request->detalle[$key]->codunidad)->result_array();

					foreach ($stockconvertido as $k => $val) {
						$stockc = 0;
						$productounidad = $this->db->query("select *from almacen.productounidades where codproducto=".$this->request->detalle[$key]->codproducto." and codunidad=".$val["codunidad"])->result_array();
						$stockc = ((float)$this->request->detalle[$key]->cantidad*(float)$factor[0]["factor"])/(float)$productounidad[0]["factor"];
						
                        $stockc = (double)$val["stockactualconvertido"] + $stockc;
						$campos = ["stockactualconvertido"]; $valores = [(double)$stockc];
						$f = ["codalmacen","codproducto","codunidad"]; 
						$v = [(int)$_SESSION["phuyu_codalmacen"],(int)$this->request->detalle[$key]->codproducto,(int)$val["codunidad"]];
						$estado = $this->phuyu_model->phuyu_editar_1("almacen.productoubicacion", $campos, $valores, $f, $v);
					}
				}

				$this->request->campos->codconcepto = 5;
				$this->request->campos->condicionpago = 2;
				$this->request->campos->tipocambio = 1;
				$this->request->campos->codcreditoconcepto = 1;
				$this->request->campos->codmoneda = 1;
				$this->request->campos->fechacomprobante = date('Y-m-d');
				$this->request->campos->nrodias = 30;
				$this->request->campos->nrocuotas = 1;
				$this->request->campos->tasainteres = 0;
				$this->request->totales->interes = 0;
				$this->request->campos->totalcredito = $this->request->totales->importe;

				$codmovimiento = $this->Caja_model->phuyu_movimientos($codkardex, 1, 1, $this->request->totales->importe, $this->request->campos,$this->request->totales->importe);

				$this->request->pagos->monto_efectivo = $this->request->totales->importe;
				
				$estado = $this->Caja_model->phuyu_movimientosdetalle($codmovimiento, $this->request->pagos);

				$persona = $this->db->query("select documento,d.abreviatura as tipo from public.personas p inner join public.documentotipos d on(p.coddocumentotipo=d.coddocumentotipo) where p.codpersona=".$this->request->campos->codpersona)->result_array();

				$this->request->cuotas[0]->fechavence=date('Y-m-d');
				$this->request->cuotas[0]->importe=$this->request->totales->importe;
				$this->request->cuotas[0]->interes = 0;
				$this->request->cuotas[0]->total = $this->request->totales->importe;

				$estado = $this->Caja_model->phuyu_credito($codkardex, $codmovimiento, 1, $this->request->campos, $this->request->totales, $this->request->cuotas,$persona[0]["tipo"].'-'.$persona[0]["documento"]);

				//print_r($informacion);exit;

				if(!$informacion['success']){
					$data["estado"] = 2; $data["informacion"] = $informacion;
				    echo json_encode($data);exit;
				}

				if ($this->db->trans_status() === FALSE){
				    $this->db->trans_rollback(); $estado = 0;
				}else{
					if ($estado!=1) {
						$this->db->trans_rollback(); $estado = 0;
					}
					$this->db->trans_commit();
				}
				$data["estado"] = $estado;
				echo json_encode($data);
			}else{
				echo "e";
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function historial($codlote){
		if ($this->input->is_ajax_request()) {
			$lote = $this->db->query("select *from public.lotes where codlote=".$codlote)->result_array();
			$this->load->view("agricola/chacra/historial",compact("lote"));
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function ver_creditos(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$this->request->codlote = (!isset($this->request->codlote) || empty($this->request->codlote)) ? 0 : $this->request->codlote;
			if ($this->request->saldos == 0) {
				$this->request->codpersona = 1;
				if($this->request->codpersona == 0){
					$socios = $this->Creditos_model->socios_creditos($this->request->fecha_desde,$this->request->fecha_hasta,$this->request->tipo);
				}else{
					$socios = $this->db->query("select codpersona,razonsocial,documento,direccion,telefono from personas where codpersona=".$this->request->codpersona)->result_array();
				}

				if($this->request->tipo_consulta == 1){
					if ($this->request->mostrar==1) {
						foreach ($socios as $key => $value) {
							$movimientos = $this->Creditos_model->estado_cuenta_cliente($this->request->fecha_desde,$this->request->fecha_hasta,$this->request->tipo,$value["codpersona"],$this->request->codlote,$this->request->estado);
							$importeanterior = 0;
							$interesanterior = 0;
							$totalanterior = 0;
							$pagadoanterior = 0;
							$saldo = 0; 
							$abono = 0; 
							$cargo = 0;
							$total_interes=0;
							$cargototal = 0;
							foreach ($movimientos as $k => $v) {
								//$saldo = $saldo + $v["cargo"] - $v["abono"]+;
								$saldo = $saldo + $v["cargo"] - $v["abono"]+$v["interes"];
								$movimientos[$k]["saldo"] = number_format($saldo,2);
								$cargo = $cargo + $v["cargo"];
								$abono = $abono + $v["abono"];
								$cargototal = $cargototal + ((double)$v["cargo"]+(double)$v["interes"]);
								$movimientos[$k]["cargototal"] = (double)$v["cargo"]+(double)$v["interes"];
								$total_interes = $total_interes + $v["interes"]; 
							}
							$socios[$key]["importeanterior"] = number_format($importeanterior,2,".","");
							$socios[$key]["interesanterior"] = number_format($interesanterior,2,".","");
							$socios[$key]["totalanterior"] = number_format($totalanterior,2,".","");
							$socios[$key]["pagadoanterior"] = number_format($pagadoanterior,2,".","");
							$socios[$key]["anterior"] = number_format(0,2);
							$socios[$key]["movimientos"] = $movimientos;
							$socios[$key]["cargo"] = number_format(($cargo+$socios[$key]["importeanterior"]),2);
							$socios[$key]["abono"] = number_format(($abono+$socios[$key]["pagadoanterior"]),2);
							$socios[$key]["cargototal"] = number_format(($cargototal+$socios[$key]["totalanterior"]),2);
							$socios[$key]["total_interes"] = number_format(($total_interes+$socios[$key]["interesanterior"]),2);
							//$socios[$key]["saldo"] = number_format($anterior + $cargo - $abono,2);
							$socios[$key]["saldo"]= number_format($saldo,2);
						}
					}else{
						foreach ($socios as $key => $value) {
							$creditos = $this->Creditos_model->estado_cuenta_creditos($this->request->fecha_desde,$this->request->fecha_hasta,$this->request->tipo,$value["codpersona"],$this->request->estado);
							$socios[$key]["creditos"] = $creditos;
						}
					}
				}
				elseif($this->request->tipo_consulta == 2) {

					foreach ($socios as $key => $value) {
						$anterior = $this->Creditos_model->estado_cuenta_anterior($this->request->fecha_desde,$this->request->tipo,$value["codpersona"],$this->request->estado);
						$movimientos = $this->Creditos_model->estado_cuenta_detallado($this->request->fecha_desde,$this->request->fecha_hasta,
						$this->request->tipo,$value["codpersona"],$this->request->codlote,$this->request->estado);

						$importeanterior = 0;
						$interesanterior = 0;
						$totalanterior = 0;
						$pagadoanterior = 0;
						$saldo = 0; 
						$abono = 0; 
						$cargo = 0;
						$total_interes=0;
						$cargototal = 0;
						foreach ($movimientos as $k => $v) {
							//$saldo = $saldo + $v["cargo"] - $v["abono"]+;
							$saldo = $saldo + $v["cargo"] - $v["abono"]+$v["interes"];
							$movimientos[$k]["saldo"] = number_format($saldo,2);
							$cargo = $cargo + $v["cargo"];
							$abono = $abono + $v["abono"];
							$cargototal = $cargototal + ((double)$v["cargo"]+(double)$v["interes"]);
							$movimientos[$k]["cargototal"] = (double)$v["cargo"]+(double)$v["interes"];
							$total_interes = $total_interes + $v["interes"]; 
						}
						$socios[$key]["importeanterior"] = number_format($importeanterior,2,".","");
						$socios[$key]["interesanterior"] = number_format($interesanterior,2,".","");
						$socios[$key]["totalanterior"] = number_format($totalanterior,2,".","");
						$socios[$key]["pagadoanterior"] = number_format($pagadoanterior,2,".","");
						$socios[$key]["anterior"] = number_format(0,2);
						$socios[$key]["movimientos"] = $movimientos;
						$socios[$key]["cargo"] = number_format(($cargo+$socios[$key]["importeanterior"]),2);
						$socios[$key]["abono"] = number_format(($abono+$socios[$key]["pagadoanterior"]),2);
						$socios[$key]["cargototal"] = number_format(($cargototal+$socios[$key]["totalanterior"]),2);
						$socios[$key]["totalinteres"] = number_format(($total_interes+$socios[$key]["interesanterior"]),2);
						//$socios[$key]["saldo"] = number_format($anterior + $cargo - $abono,2);
						$socios[$key]["saldo"]= number_format($saldo,2);
					}
				}elseif($this->request->tipo_consulta == 3){
					if ($this->request->mostrar==1) {
						foreach ($socios as $key => $value) {
							$anterior = $this->Creditos_model->estado_cuenta_anterior($this->request->fecha_desde,$this->request->tipo,$value["codpersona"],$this->request->estado);

							$movimientos = $this->Creditos_model->estado_cuenta_cliente(
							$this->request->fecha_desde,
							$this->request->fecha_hasta,
							$this->request->tipo,
							$value["codpersona"],$this->request->codlote,$this->request->estado);
							$importeanterior = $anterior["totalimporte"];
							$interesanterior = $anterior["totalinteresactual"];
							$totalanterior = $anterior["totaltotalactual"];
							$pagadoanterior = $anterior["totalpagado"];
							$saldo = $anterior["saldoactual"]; 
							$abono = 0; 
							$cargo = 0;
							$totalinteresactual=0;
							$cargototal = 0;
							foreach ($movimientos as $k => $v) {
								$saldo = $saldo + $v["cargo"] +$v["interesactual"] - $v["abono"];
								$movimientos[$k]["saldo"] = number_format($saldo,2);
								$cargo = $cargo + $v["cargo"]; 
								$abono = $abono + $v["abono"];
								$movimientos[$k]["cargototal"] = number_format((double)$v["cargo"] + (double)$v["interesactual"],2,".","");
								$totalinteresactual=$totalinteresactual + $v["interesactual"];
								$cargototal = $cargototal + ((double)$v["cargo"] + (double)$v["interesactual"]);
							}
							$socios[$key]["importeanterior"] = number_format($importeanterior,2,".","");
							$socios[$key]["interesanterior"] = number_format($interesanterior,2,".","");
							$socios[$key]["totalanterior"] = number_format($totalanterior,2,".","");
							$socios[$key]["pagadoanterior"] = number_format($pagadoanterior,2,".","");
							$socios[$key]["anterior"] = number_format($anterior["saldoactual"],2);
							$socios[$key]["movimientos"] = $movimientos;
							$socios[$key]["cargo"] = number_format($cargo+$socios[$key]["importeanterior"],2);
							$socios[$key]["totalinteresactual"] = number_format($totalinteresactual+$socios[$key]["interesanterior"],2);
							$socios[$key]["abono"] = number_format($abono+$socios[$key]["pagadoanterior"],2);
							$socios[$key]["cargototal"] = number_format($cargototal+$socios[$key]["totalanterior"],2);
							//$socios[$key]["saldo"] = number_format($anterior + $cargo - $abono,2);
							$socios[$key]["saldoit"] = number_format($saldo,2);
						}
					}else{
						foreach ($socios as $key => $value) {
							$creditos = $this->Creditos_model->estado_cuenta_creditos($this->request->fecha_desde,$this->request->fecha_hasta,$this->request->tipo,$value["codpersona"],$this->request->estado);
							$socios[$key]["creditos"] = $creditos;
						}
					}
				}else{
					foreach ($socios as $key => $value) {
						$anterior = $this->Creditos_model->estado_cuenta_anterior($this->request->fecha_desde,$this->request->tipo,$value["codpersona"],$this->request->estado);
						$movimientos = $this->Creditos_model->estado_cuenta_detallado($this->request->fecha_desde,$this->request->fecha_hasta,
						$this->request->tipo,
						$value["codpersona"],$this->request->codlote,$this->request->estado);
						$importeanterior = $anterior["totalimporte"];
						$interesanterior = $anterior["totalinteresactual"];
						$totalanterior = $anterior["totaltotalactual"];
						$pagadoanterior = $anterior["totalpagado"];
						$saldo = $anterior["saldoactual"];  
						$abono = 0; 
						$cargo = 0;
						$totalinteresactual=0;
						$cargototal = 0;
						foreach ($movimientos as $k => $v) {
							$saldo = $saldo + $v["cargo"]+ $v["interesactual"] - $v["abono"];
							$movimientos[$k]["saldo"] = number_format($saldo,2);
							$cargo = $cargo + $v["cargo"]; 
							$abono = $abono + $v["abono"];
							$movimientos[$k]["cargototaldet"] = (double)$v["cargo"] + (double)$v["interesactual"];
							$totalinteresactual=$totalinteresactual + $v["interesactual"];
							$cargototal = $cargototal + ((double)$v["cargo"] + (double)$v["interesactual"]);
						}

						$socios[$key]["importeanterior"] = number_format($importeanterior,2,".","");
						$socios[$key]["interesanterior"] = number_format($interesanterior,2,".","");
						$socios[$key]["totalanterior"] = number_format($totalanterior,2,".","");
						$socios[$key]["pagadoanterior"] = number_format($pagadoanterior,2,".","");
						$socios[$key]["anterior"] = number_format($anterior["saldoactual"],2);
						$socios[$key]["movimientos"] = $movimientos;
						$socios[$key]["cargo"] = number_format($cargo+$socios[$key]["importeanterior"],2);
						$socios[$key]["totalinteresactual"] = number_format($totalinteresactual+$socios[$key]["interesanterior"],2);
						$socios[$key]["cargototal"] = number_format($cargototal+$socios[$key]["totalanterior"],2);
						$socios[$key]["abono"] = number_format($abono+$socios[$key]["pagadoanterior"],2);
						//$socios[$key]["saldo"] = number_format($anterior + $cargo - $abono,2);
						$socios[$key]["saldo"] = number_format($saldo,2);
					}
				}
			}else{
				if($this->request->codpersona == 0){
					$socios = $this->Creditos_model->socios_saldos($this->request->fecha_saldos,$this->request->tipo);
				}else{
					$socios = $this->db->query("select codpersona,razonsocial,documento,direccion,telefono from personas where codpersona=".$this->request->codpersona)->result_array();
				}

				foreach ($socios as $key => $value) {
					$creditos = $this->Creditos_model->phuyu_saldos($this->request->fecha_saldos,$this->request->tipo,$value["codpersona"]);

					$importe = 0; 
					$interes = 0;
					$total = 0;
					$saldo = 0;
					$totalinteresactual=0;
					$totalsaldoactual=0;
					$totalimportepagado=0;
					foreach ($creditos as $k => $v) {
						$hora_i_2 = new DateTime("now"); 
						$hora_s_2 = new DateTime($v["fechavencimiento"]);	
						$intervalo_2 = $hora_i_2->diff($hora_s_2);
						if(date("Y-m-d") < $v["fechavencimiento"]){
							$sum_dias = (int)$intervalo_2->days + 1;
							$color = "green"; $estado = "POR VENCER EN ".$sum_dias." DIA(S)";
						}else{
							$color = "red"; $estado = "VENCIDO HACE ".$intervalo_2->days." DIA(S)";
						}
						$creditos[$k]["color"] = $color;
						$creditos[$k]["estado"] = $estado;

						$totalinteresactual = $totalinteresactual + $v["interesactual"];
						
						$importe = $importe + $v["importe"]; 
						$interes = $interes + $v["interes"]; 
						$totalsaldoactual=$totalsaldoactual+$v["saldoactual"];
						$totalimportepagado=$totalimportepagado+$v["importepagado"];
						$total = $total + $v["total"]; 
						$saldo = $saldo + $v["saldo"];
						
						$importemastotalinteres=$importe+$totalinteresactual;	

						$socios[$key]["importe"] = number_format($importe,2);
						$socios[$key]["interes"] = number_format($interes,2);
						$socios[$key]["totalinteresactual"] = number_format($totalinteresactual,2);
						$socios[$key]["importemastotalinteres"]=number_format($importemastotalinteres,2);
						$socios[$key]["total"] = number_format($total,2);
						$socios[$key]["saldo"] = number_format($saldo,2);
						$socios[$key]["totalimportepagado"] = number_format($totalimportepagado,2);
						$socios[$key]["totalsaldoactual"] = number_format($totalsaldoactual,2);
					}
					$socios[$key]["creditos"] = $creditos;
				}
			}
			echo json_encode($socios);
		}
	}

	function pdf_creditos(){
		if ($_GET["datos"]) {
			$this->request = json_decode($_GET["datos"]);
			if ($this->request->tipo==1) {
				$tipo = "PRODUCCION"; $socio = "EMPRESA"; $tipo_texto = "COBRANZA";
			}else{
				$tipo = "GASTOS"; $socio = "EMPRESA"; $tipo_texto = "PAGO";
			}

			$this->request->codlote = (isset($this->request->codlote) || !empty($this->request->codlote)) ? $this->request->codlote : 0;

			$this->load->library('Pdf2'); 
			$pdf = new Pdf2(); 
			$pdf->AddPage();

			if ($this->request->saldos == 0) {
				$this->request->codpersona = 1;
				if($this->request->codpersona == 0){
					$socios = $this->Creditos_model->socios_creditos($this->request->fecha_desde,$this->request->fecha_hasta,$this->request->tipo);
				}else{
					$socios = $this->db->query("select codpersona,razonsocial,documento,direccion,telefono from personas where codpersona=".$this->request->codpersona)->result_array();
				}

				if($this->request->tipo_consulta == 1){
					if ($this->request->mostrar==1) {
						$pdf->pdf_header("HISTORIAL - ".$tipo,"GAST ".$tipo." (DE:".$this->request->fecha_desde." A:".$this->request->fecha_hasta.")");
						$desde = explode("-", $this->request->fecha_desde); $hasta = explode("-", $this->request->fecha_hasta);
						$pdf->Cell(0,5,"REPORTE DESDE ".$desde[2]."-".$desde[1]."-".$desde[0]." HASTA ".$hasta[2]."-".$hasta[1]."-".$hasta[0],0,"C"); $pdf->ln(7);

						foreach ($socios as $key => $value) {
							$texto = $socio.": ".utf8_decode($value["razonsocial"])." | DIRECCION: ".utf8_decode($value["direccion"]);
							$pdf->SetFont('Arial','B',9);
							$pdf->Cell(190,6,substr($texto,0,95),1); $pdf->Ln();

							$columnas = array("FECHA","LINEA","COMPROBANTE","DESCRIPCION","IMPORTE");
							$w = array(20,20,25,100,25); 
							$pdf->pdf_tabla_head($columnas,$w,8);

							$pdf->SetWidths(array(20,20,25,100,25));
				            $pdf->SetLineHeight(5); 
							$pdf->SetFont('Arial','',7);

				            $anterior = $this->Creditos_model->estado_cuenta_anterior($this->request->fecha_desde,$this->request->tipo,$value["codpersona"],$this->request->estado);
							$movimientos = $this->Creditos_model->estado_cuenta_cliente($this->request->fecha_desde,$this->request->fecha_hasta,$this->request->tipo,$value["codpersona"],$this->request->codlote,$this->request->estado);

							$saldo = 0	; $abono = 0; 
							$cargo = 0; $totalinterespdf=0;
							foreach ($movimientos as $k => $v) {
								$saldo = $saldo + $v["cargo"]+$v["interes"] - $v["abono"];
								$cargo = $cargo + $v["cargo"]; 
								$abono = $abono + $v["abono"];
								$totalinterespdf=$totalinterespdf+$v["interes"];

								$datos = array($v["fecha"]);
								array_push($datos,utf8_decode($v["linea"]));
								array_push($datos,utf8_decode($v["comprobante"]));
								array_push($datos,utf8_decode($v["referencia"]));
								array_push($datos,number_format($v["cargo"]+$v["interes"],2));
				                $pdf->Row($datos);
							}
							$pdf->Cell(array_sum($w),0,'','T'); 
							$pdf->Ln();

							$pdf->SetFont('Arial','B',8);
							$pdf->Cell(165,5,"TOTALES",1,0,'R');
						    $pdf->Cell(25,5,number_format($cargo+$totalinterespdf,2),1,"R");
						    
						    $pdf->Ln(); $pdf->Ln();
						}
					}else{
						$pdf->pdf_header("ESTADO DE CUENTA - ".$tipo,"ESTADO DE CUENTA POR CREDITO DE ".$tipo." (DE:".$this->request->fecha_desde." A:".$this->request->fecha_hasta.")");

						foreach ($socios as $key => $value) {
							$texto = $socio.": ".utf8_decode($value["razonsocial"])." | DIRECCION: ".utf8_decode($value["direccion"]);
							$pdf->SetFont('Arial','B',9);
							$pdf->Cell(190,6,substr($texto,0,95),1); $pdf->Ln();

							$columnas = array("FECHA","COMPROBANTE","DESCRIPCION","IMPORTE","INTERES","TOTAL",$tipo_texto,"SALDO");
							$w = array(20,25,65,15,15,15,20,15); $pdf->pdf_tabla_head($columnas,$w,8);

							$pdf->SetWidths(array(20,25,65,15,15,15,20,15));
				            $pdf->SetLineHeight(5); 
							$pdf->SetFont('Arial','',7);

							$creditos = $this->Creditos_model->estado_cuenta_creditos($this->request->fecha_desde,$this->request->fecha_hasta,$this->request->tipo,$value["codpersona"],$this->request->codlote,$this->request->estado);

							$importe = 0; $interes = 0; $total = 0; $cobranza = 0; $saldo = 0;
							foreach($creditos as $v){
								$datos = array($v["fecha"]);
								array_push($datos,utf8_decode($v["comprobante"]));
								array_push($datos,utf8_decode($v["referencia"]));

								array_push($datos,number_format($v["importe"],2));
								array_push($datos,number_format($v["interes"],2));
								array_push($datos,number_format($v["total"],2));
								array_push($datos,number_format($v["cobranza"],2));
								array_push($datos,number_format($v["saldo"],2));
				                $pdf->Row($datos);

				                $importe = $importe + $v["importe"]; 
				                $interes = $interes + $v["interes"]; 
				                $total = $total + $v["total"]; 
				                $cobranza = $cobranza + $v["cobranza"]; 
				                $saldo = $saldo + $v["saldo"];
							}
							$pdf->Cell(array_sum($w),0,'','T'); $pdf->Ln();

							$pdf->SetFont('Arial','B',8);
							$pdf->Cell(110,5,"TOTALES",1,0,'R');
						    $pdf->Cell(15,5,number_format($importe,2),1,"R");
						    $pdf->Cell(15,5,number_format($interes,2),1,"R");
						    $pdf->Cell(15,5,number_format($total,2),1,"R");
						    $pdf->Cell(20,5,number_format($cobranza,2),1,"R");
						    $pdf->Cell(15,5,number_format($saldo,2),1,"R"); $pdf->Ln(); $pdf->Ln();
						}
					}
				}else{
					$pdf->pdf_header("HISTORIAL DETALLADO - ".$tipo,"ESTADO DE CUENTA POR CREDITO DE ".$tipo." (DE:".$this->request->fecha_desde." A:".$this->request->fecha_hasta.")");
					$desde = explode("-", $this->request->fecha_desde); $hasta = explode("-", $this->request->fecha_hasta);
					$pdf->Cell(0,5,"REPORTE DESDE ".$desde[2]."-".$desde[1]."-".$desde[0]." HASTA ".$hasta[2]."-".$hasta[1]."-".$hasta[0],0,"C"); $pdf->ln(7);

					foreach ($socios as $key => $value) {
						$texto = $socio.": ".utf8_decode($value["razonsocial"])." | DIRECCION: ".utf8_decode($value["direccion"]);
						$pdf->SetFont('Arial','B',9);
						$pdf->Cell(192,6,substr($texto,0,95),1); $pdf->Ln();

						$columnas = array("FECHA","LINEA","COMPROB","DESCRIPCION","UND","CANT","P. UNIT","IMPORTE");
						$w = array(15,10,21,80,13,13,20,20); $pdf->pdf_tabla_head($columnas,$w,8);

						$pdf->SetWidths(array(15,10,21,80,13,13,20,20));
			            $pdf->SetLineHeight(5); 
						$pdf->SetFont('Arial','',7);

			            $anterior = $this->Creditos_model->estado_cuenta_anterior($this->request->fecha_desde,$this->request->tipo,$value["codpersona"],$this->request->estado);
						$movimientos = $this->Creditos_model->estado_cuenta_detallado($this->request->fecha_desde,$this->request->fecha_hasta,$this->request->tipo,$value["codpersona"],$this->request->codlote,$this->request->estado);
						
						$saldo = 0; $abono = 0; $cargo = 0;
						$totalinterespdf=0;
						foreach ($movimientos as $k => $v) {
							$saldo = $saldo + $v["cargo"] - $v["abono"];
							$cargo = $cargo + $v["cargo"]; $abono = $abono + $v["abono"];
							$totalinterespdf=$totalinterespdf+$v["interes"];
							$datos = array($v["fechacomprobante"]);
							array_push($datos,utf8_decode($v["linea"])); 
							array_push($datos,utf8_decode($v["comprobante"])); 
							array_push($datos,utf8_decode($v["descripcion"]));
							array_push($datos,utf8_decode($v["unidad"])); 
							array_push($datos,number_format($v["cantidad"],2));
							array_push($datos,number_format($v["preciounitario"],2));
							array_push($datos,number_format($v["cargo"] + $v["interes"],2));
			                $pdf->Row($datos);
						}
						$pdf->Cell(array_sum($w),0,'','T'); $pdf->Ln();

						$pdf->SetFont('Arial','B',8);
						$pdf->Cell(172,5,"TOTALES",1,0,'R');
					    $pdf->Cell(20,5,number_format($cargo+$totalinterespdf,2),1,"R");
						$pdf->Ln(); $pdf->Ln();
					}
				}
			}else{
				if($this->request->codpersona == 0){
					$socios = $this->Creditos_model->socios_saldos($this->request->fecha_saldos,$this->request->tipo);
				}else{
					$socios = $this->db->query("select codpersona,razonsocial,documento,direccion,telefono from personas where codpersona=".$this->request->codpersona)->result_array();
				}

				$pdf->pdf_header("SALDOS DE ".$tipo." ".$this->request->fecha_saldos,"SALDOS");

				foreach ($socios as $key => $value) {
					$texto = $socio.": ".utf8_decode($value["razonsocial"])." | DIRECCION: ".utf8_decode($value["direccion"]);
					$pdf->SetFont('Arial','B',9);
					$pdf->Cell(190,6,substr($texto,0,95),1); $pdf->Ln();

					$columnas = array("COMPROBANTE","FECHA CREDITO","FECHA VENCE","ESTADO","IMPORTE","INTERES","TOTAL","SALDO");
					$w = array(25,25,25,55,15,15,15,15); $pdf->pdf_tabla_head($columnas,$w,8);

					$pdf->SetWidths(array(25,25,25,55,15,15,15,15));
		            $pdf->SetLineHeight(5); $pdf->SetFont('Arial','',7);

					$creditos = $this->Creditos_model->phuyu_saldos($this->request->fecha_saldos,$this->request->tipo,$value["codpersona"]);

					$importe = 0; 
					$interes = 0; 
					$total = 0; 
					$saldo = 0;
					$totalinteresactual=0;
					foreach ($creditos as $k => $v) {
						$hora_i_2 = new DateTime("now"); $hora_s_2 = new DateTime($v["fechavencimiento"]);	
						$intervalo_2 = $hora_i_2->diff($hora_s_2);
						if(date("Y-m-d") < $v["fechavencimiento"]){
							$sum_dias = (int)$intervalo_2->days + 1;
							$color = "green"; $estado = "POR VENCER EN ".$sum_dias." DIA(S)";
						}else{
							$color = "red"; $estado = "VENCIDO HACE ".$intervalo_2->days." DIA(S)";
						}

						$datos = array($v["seriecomprobante_ref"]."-".$v["nrocomprobante_ref"]);
						array_push($datos,utf8_decode($v["fechacredito"]));
						array_push($datos,utf8_decode($v["fechavencimiento"]));
						array_push($datos,utf8_decode($estado));

						array_push($datos,number_format($v["importe"],2));
						array_push($datos,number_format($v["interes"],2));
						array_push($datos,number_format($v["total"],2));
						array_push($datos,number_format($v["saldo"],2));
		                $pdf->Row($datos);

						$importe = $importe + $v["importe"]; 
						$interes = $interes + $v["interes"]; 
						$totalinteresactual = $totalinteresactual + $v["interesactual"];
						$total = $total + $v["total"]; 
						$saldo = $saldo + $v["saldo"];
					}
					$pdf->Cell(array_sum($w),0,'','T'); $pdf->Ln();

					$pdf->SetFont('Arial','B',8);
					$pdf->Cell(130,5,"TOTALES",1,0,'R');
				    $pdf->Cell(15,5,number_format($importe,2),1,"R");
				    $pdf->Cell(15,5,number_format($interes,2),1,"R");
				    $pdf->Cell(15,5,number_format($total,2),1,"R");
				    $pdf->Cell(15,5,number_format($saldo,2),1,"R"); $pdf->Ln(); $pdf->Ln();
				}
			}

			$pdf->SetTitle("phuyu Peru - Reporte Creditos"); $pdf->Output();
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function excel_creditos(){
		if ($_GET["datos"]) {
			$this->request = json_decode($_GET["datos"]);
			if ($this->request->tipo==1) {
				$tipo = "PRODUCCION"; $socio = "EMPRESA"; $tipo_texto = "COBRANZA";
			}else{
				$tipo = "GASTOS"; $socio = "EMPRESA"; $tipo_texto = "PAGO";
			}

			$desde = explode("-", $this->request->fecha_desde); $hasta = explode("-", $this->request->fecha_hasta);

			$this->request->codlote = (isset($this->request->codlote) || !empty($this->request->codlote)) ? $this->request->codlote : 0;

			$this->request->codpersona = 1;

			if($this->request->codpersona == 0){
				$socios = $this->Creditos_model->socios_creditos($this->request->fecha_desde,$this->request->fecha_hasta,$this->request->tipo);
			}else{
				$socios = $this->db->query("select codpersona,razonsocial,documento,direccion,telefono from personas where codpersona=".$this->request->codpersona)->result_array();
			}

			if($this->request->tipo_consulta == 1){
				if ($this->request->mostrar==1) {
					foreach ($socios as $key => $value) {
						$anterior = $this->Creditos_model->estado_cuenta_anterior($this->request->fecha_desde,$this->request->tipo,$value["codpersona"],$this->request->estado);
						$movimientos = $this->Creditos_model->estado_cuenta_cliente($this->request->fecha_desde,$this->request->fecha_hasta,$this->request->tipo,$value["codpersona"],$this->request->codlote,$this->request->estado);

						$socios[$key]["anterior"] = $anterior;
						$socios[$key]["movimientos"] = $movimientos;
					}

					$this->load->view("agricola/chacra/estadocuentaxls.php",compact("socios","socio","tipo","desde","hasta"));
				}else{
					foreach ($socios as $key => $value) {
						$creditos = $this->Creditos_model->estado_cuenta_creditos($this->request->fecha_desde,$this->request->fecha_hasta,$this->request->tipo,$value["codpersona"],$this->request->codlote);

						$socios[$key]["creditos"] = $creditos;
					}
					$this->load->view("reportes/creditos/creditosxls.php",compact("socios","socio","tipo","desde","hasta"));
				}
			}else{
				foreach ($socios as $key => $value) {
					$anterior = $this->Creditos_model->estado_cuenta_anterior($this->request->fecha_desde,$this->request->tipo,$value["codpersona"],$this->request->estado);
					$movimientos = $this->Creditos_model->estado_cuenta_detallado($this->request->fecha_desde,$this->request->fecha_hasta,$this->request->tipo,$value["codpersona"],$this->request->codlote,$this->request->estado);

					$socios[$key]["anterior"] = $anterior;
					$socios[$key]["movimientos"] = $movimientos;
				}
				$this->load->view("agricola/chacra/estadocuentadetalladoxls.php",compact("socios","socio","tipo","desde","hasta"));
			}
			
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function nuevo(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$departamentos = $this->db->query("select distinct(ubidepartamento), departamento from public.ubigeo order by ubidepartamento")->result_array();
				$empleados = $this->db->query("select persona.codpersona,persona.razonsocial from public.personas as persona inner join public.empleados as empleado on(persona.codpersona=empleado.codpersona) where empleado.estado=1 and empleado.codcargo=4")->result_array();
				$this->load->view("agricola/chacra/nuevo",compact("departamentos","empleados"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}
	
	function ver($codregistro){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_codusuario"])){
				$info = $this->db->query("select lotes.*,comprobantes.descripcion as tipo from lotes.lotes as lotes inner join caja.comprobantetipos as comprobantes on(lotes.codcomprobantetipo=comprobantes.codcomprobantetipo) where lotes.codlotes=".$codregistro)->result_array();

				$detalle = $this->db->query("select kd.*,p.descripcion as producto,u.descripcion as unidad,p.codigo from lotes.lotesdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codlotes=".$codregistro." and kd.estado=1 order by kd.item")->result_array();

				$pagos = $this->db->query("select p.descripcion as tipopago, md.importe,md.importeentregado,md.vuelto,md.nrodocbanco from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) inner join caja.tipopagos as p on(md.codtipopago=p.codtipopago) where m.codlotes=".$codregistro." and m.estado=1 order by p.codtipopago")->result_array();
				$this->load->view("ventas/ventas/ver",compact("info","detalle","pagos")); 
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

				$comprado = ($this->request->comprado) ? 1 : 0;

				$this->request->observaciones = (isset($this->request->observaciones)) ? $this->request->observaciones : '';

				$campos = ["codsocio","codubigeo","codzona","codalmacen","descripcion","direccion","fechainicio","fechafin","tasainteres","creditomaximo","codsocioreferencia","codempleado","area","codsocioconvenio","comprado","tipoposesion","codusuario","observaciones","estado"];

				$valores = [$this->request->codsocio,$this->request->codubigeo,$this->request->codzona,$_SESSION["phuyu_codalmacen"],$this->request->cliente,$this->request->direccion,$this->request->fechainicio,$this->request->fechafin,$this->request->tasainteres,$this->request->creditomaximo,$this->request->codsocio,$this->request->codempleado,$this->request->area,$this->request->codsocio,$comprado,(int)$this->request->tipoposesion,(int)$_SESSION["phuyu_codusuario"],$this->request->observaciones,1];

				if($this->request->codlote==0){
					$estado = $this->phuyu_model->phuyu_guardar("public.lotes", $campos, $valores);	
				}else{
					$estado = $this->phuyu_model->phuyu_editar("public.lotes", $campos, $valores, "codlote", $this->request->codlote);
				}

				$data['codlote'] = $this->request->codlote;
				$data['estado'] = $estado;
				echo json_encode($data);
			}else{
				echo "e";
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public static function sumarDiasNaturales($fecha, $dias)
    {
        if ($dias < 0) {
            return '';
        }
        $dateArray = explode("-", $fecha);
        $sd = $dias;
        while ($sd > 0) {
            if ($sd <= date("t", mktime(0, 0, 0,
                                        $dateArray[ 1 ],
                                        1,
                                        $dateArray[ 0 ])
                            ) - $dateArray[ 2 ]) {
                $dateArray[ 2 ] = $dateArray[ 2 ] + $sd;
                $sd = 0;
            } else {
                $sd  = $sd - ( date( "t", mktime(0, 0, 0,
                                                $dateArray[ 1 ],
                                                1,
                                                $dateArray[ 0 ])
                                   ) - $dateArray[ 2 ]);
                $dateArray[ 2 ] = 0;
                if ($dateArray[ 1 ] < 12) {
                    $dateArray[ 1 ]++;
                } else {
                    $dateArray[ 1 ] = 1;
                    $dateArray[ 0 ]++;
                }
            }
        }
        $sDia = '00'.$dateArray[ 2 ];
        $sDia = substr($sDia, -2);
        $sMes = '00'.$dateArray[ 1 ];
        $sMes = substr($sMes, -2);
        return $dateArray[ 0 ].'-'.$sMes.'-'.$sDia;
    }

	function editar(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$this->request = json_decode(file_get_contents('php://input'));

				$info = $this->db->query("select lotes.fechainicio,lotes.fechafin, lotes.area, lotes.codsocioreferencia, lotes.observaciones,lotes.tipoposesion,lotes.tasainteres, lotes.creditomaximo, lotes.comprado, lotes.codempleado, lotes.codzona, lotes.codubigeo, lotes.descripcion,lotes.direccion,lotes.codsocio,personas.razonsocial AS cliente from public.lotes as lotes inner join public.personas as personas on (lotes.codsocio=personas.codpersona) where lotes.codlote=".$this->request->codregistro)->result_array();

				if($info[0]["codsocio"]!=$info[0]["codsocioreferencia"]){
					$codsocioreferencia = $this->db->query("select * from public.personas where codpersona=".$info[0]["codsocioreferencia"])->result_array();

					$info[0]["garante"] = $codsocioreferencia[0]["razonsocial"];
				}else{
					$info[0]["garante"] = $info[0]["cliente"];
				}

				$ubigeo = $this->db->query("select * from public.ubigeo where codubigeo=".$info[0]["codubigeo"])->result_array();

				$data['info'] = $info;
				$data['ubigeo'] = $ubigeo;
				echo json_encode($data);
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function editar_guardar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			$campos = ["codpersona","fechacomprobante","fechalotes","cliente","direccion","descripcion","nroplaca"];
			$valores = [
				$this->request->codpersona,
				$this->request->fechacomprobante,
				$this->request->fechalotes,
				$this->request->cliente,
				$this->request->direccion,
				$this->request->descripcion,
				$this->request->nroplaca
			];
			$estado = $this->phuyu_model->phuyu_editar("lotes.lotes", $campos, $valores, "codlotes",$this->request->codregistro);

			$campos = ["fechalotes"]; $valores = [$this->request->fechalotes];
			$estado_u = $this->phuyu_model->phuyu_editar("lotes.lotesalmacen", $campos, $valores, "codlotes",$this->request->codregistro);

			$campos = ["codpersona","fechacredito"]; $valores = [$this->request->codpersona,$this->request->fechacomprobante];
			$estado_u = $this->phuyu_model->phuyu_editar("lotes.creditos", $campos, $valores, "codlotes",$this->request->codregistro);
			$campos = ["codpersona","fechamovimiento"]; $valores = [$this->request->codpersona,$this->request->fechacomprobante];
			$estado_u = $this->phuyu_model->phuyu_editar("caja.movimientos", $campos, $valores, "codlotes",$this->request->codregistro);

			echo $estado;
		}
	}

	function eliminar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$this->db->trans_begin();	

			// REVISAMOS SI ESTA SUJETO A UNA NOTA DE CREDITO

			$comprobante = $this->db->query("select *from lotes.lotes where codlotes_ref=".$this->request->codregistro." and estado<>0 and codmovimientotipo=8")->result_array();

			if(count($comprobante) > 0){
				$this->db->trans_rollback(); $estado = 5; echo $estado; exit();
			}		

			$comprobante = $this->db->query("select *from lotes.lotes where codlotes=".$this->request->codregistro." and estado<>0")->result_array();

			// REVISAMOS LA FECHA DE EMISION SI ES FACTURA O BOLETA

			$dteStart = new DateTime($comprobante[0]["fechacomprobante"]); 
            $dteEnd   = new DateTime(date('Y-m-d'));
            $dteDiff  = $dteStart->diff($dteEnd);
            $diferencia = $dteDiff->days;

            if($comprobante[0]["codcomprobantetipo"] == 10 || $comprobante[0]["codcomprobantetipo"] == 12){
            	if((int)$diferencia > 7){
                   $this->db->trans_rollback(); $estado = 3; echo $estado; exit();
                }
            }

			// SI EXISTE EN CREDITOS //
			$credito = $this->db->query("select *from lotes.creditos where codlotes=".$this->request->codregistro." and estado<>0")->result_array();
			if (count($credito)>0) {
				$this->db->trans_rollback(); $estado = 2; echo $estado; exit();
			}

			// ACTUALIZAMOS PRODUCTOS UBICACION //
			$lotesalmacen = $this->db->query("select codlotesalmacen from lotes.lotesalmacen where codlotes=".$this->request->codregistro)->result_array();

			$info = $this->db->query("select *from lotes.lotesdetalle where codlotes=".$this->request->codregistro)->result_array();
			foreach ($info as $key => $value) {
				$existe = $this->db->query("select *from almacen.productoubicacion where codalmacen=".$_SESSION["phuyu_codalmacen"]." and codproducto=".$value["codproducto"]." and codunidad=".$value["codunidad"])->result_array();
				$stock = $existe[0]["stockactual"] + $value["cantidad"];

				$campos = ["stockactual"]; $valores = [(double)$stock];
				$f = ["codalmacen","codproducto","codunidad"];
				$v = [(int)$_SESSION["phuyu_codalmacen"],(int)$value["codproducto"],(int)$value["codunidad"]];
				$estado = $this->phuyu_model->phuyu_editar_1("almacen.productoubicacion", $campos, $valores, $f, $v);

				// AUMENTAMOS EL STOCKACTUALCONVERTIDO

				$stockconvertido = $this->db->query("select *from almacen.productoubicacion where codalmacen=".$_SESSION["phuyu_codalmacen"]." and codproducto=".$value["codproducto"])->result_array();

				$factor = $this->db->query("select *from almacen.productounidades where codproducto=".$value["codproducto"]." and codunidad=".$value["codunidad"])->result_array();

				foreach ($stockconvertido as $k => $val) {
					$stockc = 0;
					$productounidad = $this->db->query("select *from almacen.productounidades where codproducto=".$value["codproducto"]." and codunidad=".$val["codunidad"])->result_array();

					$stockc = ((float)$value["cantidad"]*(float)$factor[0]["factor"])/(float)$productounidad[0]["factor"];
					$stockc = $stockc + $val["stockactualconvertido"];
                    $campos = ["stockactualconvertido"]; $valores = [(double)$stockc];
					$f = ["codalmacen","codproducto","codunidad"];
					$v = [(int)$_SESSION["phuyu_codalmacen"],(int)$value["codproducto"],(int)$val["codunidad"]];
					$estado = $this->phuyu_model->phuyu_editar_1("almacen.productoubicacion", $campos, $valores, $f, $v);
				}
			}
			$estado = $this->phuyu_model->phuyu_eliminar("lotes.lotes", "codlotes", $this->request->codregistro);
            if(count($lotesalmacen) > 0){
				$estado = $this->phuyu_model->phuyu_eliminar("lotes.lotesalmacen", "codlotesalmacen", $lotesalmacen[0]["codlotesalmacen"]);
            }

			// REGISTRO lotes ANULADOS //
			$campos = ["codlotes","codsucursal","codusuario","fechaanulacion","observaciones"];
			$valores =[
				(int)$this->request->codregistro, (int)$_SESSION["phuyu_codsucursal"], (int)$_SESSION["phuyu_codusuario"], date("Y-m-d"),
				$this->request->observaciones
			];
			$estado = $this->phuyu_model->phuyu_guardar("lotes.lotesanulados", $campos, $valores);
            
            if(count($lotesalmacen) > 0){
				// REGISTRO lotes ALMACEN ANULADOS //
				$campos = ["codlotesalmacen","codsucursal","codusuario","fechaanulacion","observaciones"];
				$valores =[
					(int)$lotesalmacen[0]["codlotesalmacen"], (int)$_SESSION["phuyu_codsucursal"], (int)$_SESSION["phuyu_codusuario"], date("Y-m-d"),
					$this->request->observaciones
				];
				$estado = $this->phuyu_model->phuyu_guardar("lotes.lotesalmacenanulado", $campos, $valores);
            }
			//VERIFICAMOS Y ELIMINAMOS EN LA TABLA lotesPEDIDO

			$info = $this->db->query("select *from lotes.lotespedido where codlotes=".$this->request->codregistro)->result_array();

			if(count($info) > 0){
				$codpedido = 0;
				foreach ($info as $key => $value) {

					$existepedido = $this->db->query("select *from lotes.pedidosdetalle where codpedido=".$value["codpedido"]." and codproducto=".$value["codproducto"]." and item=".$value["itempedido"])->result_array();

					$existeventa = $this->db->query("select *from lotes.lotesdetalle where codlotes=".$value["codlotes"]." and codproducto=".$value["codproducto"]." and item=".$value["itemlotes"])->result_array();

					$stock = $existepedido[0]["cantidadcomprobante"] - $existeventa[0]["cantidad"];

					$campos = ["cantidadcomprobante"]; $valores = [(double)$stock];
					$f = ["codpedido","codproducto","item"];
					$v = [(int)$value["codpedido"],(int)$value["codproducto"],(int)$value["itempedido"]];
					$estado = $this->phuyu_model->phuyu_editar_1("lotes.pedidosdetalle", $campos, $valores, $f, $v);
					$codpedido = $value["codpedido"];
				}
				$estado = $this->phuyu_model->phuyu_eliminar_total("lotes.lotespedido", "codlotes",$this->request->codregistro);

				$estadoproceso = $this->db->query("select *FROM lotes.pedidosdetalle pd where pd.codpedido=".$codpedido." and pd.cantidad > pd.cantidadcomprobante")->result_array();

				if(count($estadoproceso) > 0){
					$data = array("estadoproceso" => 0);
					$this->db->where("codpedido", $codpedido);
		            $estado = $this->db->update("lotes.pedidos", $data);
				}
			}

			// ANULAMOS EL MOVIMIENTO DE CAJA //
			$movi = $this->db->query("select codmovimiento from caja.movimientos where codlotes=".$this->request->codregistro)->result_array();
			$estado = $this->phuyu_model->phuyu_eliminar("caja.movimientos", "codmovimiento", $movi[0]["codmovimiento"]);

			$campos = ["estado"]; $valores = [0];
			$f = ["codmovimiento"]; $v = [(int)$movi[0]["codmovimiento"]];
			$estado = $this->phuyu_model->phuyu_editar_1("caja.movimientosdetalle", $campos, $valores, $f, $v);

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

	function restaurar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$this->db->trans_begin();

			// ACTUALIZAMOS PRODUCTOS UBICACION //
			$lotesalmacen = $this->db->query("select codlotesalmacen from lotes.lotesalmacen where codlotes=".$this->request->codregistro)->result_array();

			$info = $this->db->query("select *from lotes.lotesdetalle where codlotes=".$this->request->codregistro)->result_array();
			foreach ($info as $key => $value) {
				$existe = $this->db->query("select *from almacen.productoubicacion where codalmacen=".$_SESSION["phuyu_codalmacen"]." and codproducto=".$value["codproducto"]." and codunidad=".$value["codunidad"])->result_array();
				$stock = $existe[0]["stockactual"] - $value["cantidad"];

				$campos = ["stockactual"]; $valores = [(double)$stock];
				$f = ["codalmacen","codproducto","codunidad"];
				$v = [(int)$_SESSION["phuyu_codalmacen"],(int)$value["codproducto"],(int)$value["codunidad"]];
				$estado = $this->phuyu_model->phuyu_editar_1("almacen.productoubicacion", $campos, $valores, $f, $v);

				// DISMINUIMOS EL STOCKACTUALCONVERTIDO

				$stockconvertido = $this->db->query("select *from almacen.productoubicacion where codalmacen=".$_SESSION["phuyu_codalmacen"]." and codproducto=".$value["codproducto"])->result_array();

				$factor = $this->db->query("select *from almacen.productounidades where codproducto=".$value["codproducto"]." and codunidad=".$value["codunidad"])->result_array();

				foreach ($stockconvertido as $k => $val) {
					$stockc = 0;
					$productounidad = $this->db->query("select *from almacen.productounidades where codproducto=".$value["codproducto"]." and codunidad=".$val["codunidad"])->result_array();

					$stockc = ((float)$value["cantidad"]*(float)$factor[0]["factor"])/(float)$productounidad[0]["factor"];
					$stockc = $val["stockactualconvertido"] - $stockc;
                    $campos = ["stockactualconvertido"]; $valores = [(double)$stockc];
					$f = ["codalmacen","codproducto","codunidad"];
					$v = [(int)$_SESSION["phuyu_codalmacen"],(int)$value["codproducto"],(int)$val["codunidad"]];
					$estado = $this->phuyu_model->phuyu_editar_1("almacen.productoubicacion", $campos, $valores, $f, $v);
				}
			}
			$estado = $this->phuyu_model->phuyu_restaurar("lotes.lotes", "codlotes", $this->request->codregistro);
			$estado = $this->phuyu_model->phuyu_restaurar("lotes.lotesalmacen", "codlotesalmacen", $lotesalmacen[0]["codlotesalmacen"]);

			$estado = $this->phuyu_model->phuyu_eliminar_total("lotes.lotesanulados","codlotes",$this->request->codregistro);
			$estado = $this->phuyu_model->phuyu_eliminar_total("lotes.lotesalmacenanulado","codlotesalmacen",$lotesalmacen[0]["codlotesalmacen"]);

			//VERIFICAMOS Y ELIMINAMOS EN LA TABLA lotesPEDIDO

			/*$info = $this->db->query("select *from lotes.lotespedido where codlotes=".$this->request->codregistro)->result_array();

			if(count($info) > 0){
				$codpedido = 0;
				foreach ($info as $key => $value) {

					$existepedido = $this->db->query("select *from lotes.pedidosdetalle where codpedido=".$value["codpedido"]." and codproducto=".$value["codproducto"]." and item=".$value["itempedido"])->result_array();

					$existeventa = $this->db->query("select *from lotes.lotesdetalle where codlotes=".$value["codlotes"]." and codproducto=".$value["codproducto"]." and item=".$value["itemlotes"])->result_array();

					$stock = $existepedido[0]["cantidadcomprobante"] + $existeventa[0]["cantidad"];

					$campos = ["cantidadcomprobante"]; $valores = [(double)$stock];
					$f = ["codpedido","codproducto","item"];
					$v = [(int)$value["codpedido"],(int)$value["codproducto"],(int)$value["itempedido"]];
					$estado = $this->phuyu_model->phuyu_editar_1("lotes.pedidosdetalle", $campos, $valores, $f, $v);
					$codpedido = $value["codpedido"];
				}
				$estado = $this->phuyu_model->phuyu_eliminar_total("lotes.lotespedido", "codlotes",$this->request->codregistro);

				$estadoproceso = $this->db->query("select *FROM lotes.pedidosdetalle pd where pd.codpedido=".$codpedido." and pd.cantidad > pd.cantidadcomprobante")->result_array();

				if(count($estadoproceso) > 0){
					$data = array("estadoproceso" => 0);
					$this->db->where("codpedido", $codpedido);
		            $estado = $this->db->update("lotes.pedidos", $data);
				}
			}*/

			// ANULAMOS EL MOVIMIENTO DE CAJA //
			$movi = $this->db->query("select codmovimiento from caja.movimientos where codlotes=".$this->request->codregistro)->result_array();
			if(count($movi) > 0){
				$estado = $this->phuyu_model->phuyu_restaurar("caja.movimientos", "codmovimiento", $movi[0]["codmovimiento"]);

				$campos = ["estado"]; $valores = [1];
				$f = ["codmovimiento"]; $v = [(int)$movi[0]["codmovimiento"]];
				$estado = $this->phuyu_model->phuyu_editar_1("caja.movimientosdetalle", $campos, $valores, $f, $v);
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

	function formato($formato){
		if ($this->input->is_ajax_request()) {
			$campos = ["formato"]; $valores = [$formato];
			$f = ["codsucursal","codcomprobantetipo"]; $v = [$_SESSION["phuyu_codsucursal"],10];
			$estado = $this->phuyu_model->phuyu_editar_1("caja.comprobantes", $campos, $valores, $f, $v);

			echo $formato;
		}
	}
}