<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Proformas extends CI_Controller {

	public function __construct(){
		parent::__construct(); 
		$this->load->model("phuyu_model"); $this->load->model("Caja_model"); $this->load->model("Kardex_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_codusuario"])) {
				/* CODIGO TEMPORAL DE LA IMPRESION */

				$formato = $this->db->query("select formato from caja.comprobantes where codcomprobantetipo=9 AND codsucursal = ".$_SESSION["phuyu_codsucursal"])->result_array();
				if (count($formato)==0) {
					$_SESSION["phuyu_formatoproforma"] = "a4";
				}else{
					$_SESSION["phuyu_formatoproforma"] = $formato[0]["formato"];
				}

				/* FIN CODIGO TEMPORAL DE LA IMPRESION */

				$comprobante_almacen = $this->db->query("select count(*) as cantidad from caja.comprobantes where codcomprobantetipo=9 and codsucursal=".$_SESSION["phuyu_codsucursal"]." and estado=1")->result_array();
				$almacen = $comprobante_almacen[0]["cantidad"];
				$this->load->view("compras/proformas/index",compact("almacen"));
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
				if (!empty($this->request->fechas->desde)) {
					$fechas = "fechaproforma>='".$this->request->fechas->desde."' and fechaproforma<='".$this->request->fechas->hasta."' and";
				}else{
					$fechas = "fechaproforma<='".$this->request->fechas->hasta."' and";
				}
			}

			$lista = $this->db->query("select proformas.hora,personas.documento,proformas.razonsocial,proformas.codproforma, proformas.codcomprobantetipo, proformas.seriecomprobante,proformas.condicionpago, proformas.nrocomprobante, proformas.fechaproforma,round(proformas.importe,2) as importe,proformas.estado, comprobantes.descripcion as tipo,proformas.codkardex,proformas.estadoproceso from kardex.proformas inner join public.personas as personas on (proformas.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(proformas.codcomprobantetipo=comprobantes.codcomprobantetipo) where ".$fechas." (UPPER(personas.documento) like UPPER('%".$this->request->buscar."%') or UPPER(personas.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(proformas.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(comprobantes.descripcion) like UPPER('%".$this->request->buscar."%') or UPPER(proformas.seriecomprobante) like UPPER('%".$this->request->buscar."%') or UPPER(proformas.nrocomprobante) like UPPER('%".$this->request->buscar."%') ) and proformas.codsucursal=".$_SESSION["phuyu_codsucursal"]." AND tipoproforma = 2 order by proformas.fechaproforma desc,proformas.hora desc offset ".$offset." limit ".$limit)->result_array();

            foreach ($lista as $key => $value) {

				$hora = explode(".", $lista[$key]["hora"]);
				$lista[$key]["hora"] = $hora[0];
			}

			$total = $this->db->query("select count(*) as total from kardex.proformas inner join public.personas as personas on (proformas.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(proformas.codcomprobantetipo=comprobantes.codcomprobantetipo) where ".$fechas." (UPPER(personas.documento) like UPPER('%".$this->request->buscar."%') or UPPER(personas.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(proformas.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(comprobantes.descripcion) like UPPER('%".$this->request->buscar."%') or UPPER(proformas.seriecomprobante) like UPPER('%".$this->request->buscar."%') or UPPER(proformas.nrocomprobante) like UPPER('%".$this->request->buscar."%') ) and proformas.codsucursal=".$_SESSION["phuyu_codsucursal"]." AND tipoproforma = 2")->result_array();

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

	public function buscar_lista(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$limit = 10; $offset = $this->request->pagina * $limit - $limit;

			$lista = $this->db->query("SELECT k.codproforma,k.codsucursal,k.codalmacen,sum(kd.cantidad) As cantidad, sum(kd.cantidadcomprobante) As cantidadcomprobante,k.razonsocial,k.direccion,k.fechaproforma,k.hora,k.seriecomprobante,k.nrocomprobante,p.documento,round(k.valorventa,2) as valorventa,k.codpersona,k.codempleado,k.condicionpago FROM kardex.proformas k JOIN kardex.proformasdetalle kd ON k.codproforma = kd.codproforma JOIN public.personas as p on (k.codpersona=p.codpersona) WHERE (UPPER(p.documento) ilike UPPER('%".$this->request->buscar."%') or UPPER(p.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(k.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(k.seriecomprobante) like UPPER('%".$this->request->buscar."%') or UPPER(k.nrocomprobante) like UPPER('%".$this->request->buscar."%') ) and k.codsucursal=".$_SESSION["phuyu_codsucursal"]." and k.estado=1 and tipoproforma = 2 AND estadoproceso = 0 GROUP BY k.codproforma, k.codsucursal,k.codpersona, k.valorventa, k.codalmacen,k.razonsocial,k.fechaproforma,k.hora,k.seriecomprobante,k.nrocomprobante,p.documento HAVING sum(kd.cantidadcomprobante) < sum(kd.cantidad) order by k.codproforma desc offset ".$offset." limit ".$limit)->result_array();

			foreach ($lista as $key => $value) {

				$lista[$key]["nrodias"] = 30;
				$lista[$key]["nrocuotas"] = 1;
				$lista[$key]["tasainteres"] = 0;
				
				if($value["condicionpago"]==2){
					$credito = $this->db->query("select *from kardex.creditosproformas where codproforma=".$value["codproforma"]." AND estado = 1")->result_array();

					if(count($credito)>0){
						$lista[$key]["nrodias"] = $credito[0]["nrodias"];
						$lista[$key]["nrocuotas"] = $credito[0]["nrocuotas"];
						$lista[$key]["tasainteres"] = $credito[0]["tasainteres"];
					}
				}
			}

			$total = $this->db->query("select count(*) as total FROM kardex.proformas k JOIN kardex.proformasdetalle kd ON k.codproforma = kd.codproforma JOIN public.personas as p on (k.codpersona=p.codpersona) WHERE (UPPER(p.documento) ilike UPPER('%".$this->request->buscar."%') or UPPER(p.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(k.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(k.seriecomprobante) like UPPER('%".$this->request->buscar."%') or UPPER(k.nrocomprobante) like UPPER('%".$this->request->buscar."%') ) and k.codsucursal=".$_SESSION["phuyu_codsucursal"]." and k.estado=1 and tipoproforma = 2 AND estadoproceso = 0 HAVING sum(kd.cantidadcomprobante) < sum(kd.cantidad)")->result_array();

			//print_r($total[0]["total"]);exit;
            $total = (count($total) > 0) ? $total[0]["total"] : 0;
            $paginas = floor($total / $limit);
			if ( ($total % $limit)!=0 ) {
				$paginas = $paginas + 1;
			}

			$paginacion = array();
			$paginacion["total"] = $total;
			$paginacion["actual"] = $this->request->pagina;
			$paginacion["ultima"] = $paginas;
			$paginacion["desde"] = $offset;
			$paginacion["hasta"] = $offset + $limit;

			echo json_encode(array("lista" => $lista,"paginacion" => $paginacion));
		}
	}

	public function buscar(){
        if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_codusuario"])) {
				$this->load->view("compras/proformas/buscar");
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function buscarproductos($codproforma){
		if ($this->input->is_ajax_request()) {
			$productos = $this->db->query("select pd.*,p.descripcion as producto,p.calcular,u.descripcion as unidad,p.controlstock as control FROM kardex.proformasdetalle pd JOIN almacen.productos p ON pd.codproducto = p.codproducto JOIN almacen.unidades u ON pd.codunidad = u.codunidad where codproforma=".$codproforma." and pd.cantidad > pd.cantidadcomprobante")->result_array();

			foreach ($productos as $key => $value) {

				$unidades = $this->db->query("select *FROM almacen.v_productounidades pun where pun.codproducto=".$value["codproducto"]." AND codalmacen= ".$_SESSION["phuyu_codalmacen"])->result_array();

				$stock = $this->db->query("select pu.stockactualconvertido from almacen.productoubicacion as pu where pu.codproducto=".$value["codproducto"]." and pu.codunidad=".$value["codunidad"]." and pu.codalmacen=".$_SESSION["phuyu_codalmacen"]." and pu.estado=1")->result_array();
				if (count($stock)==0) {
					$productos[$key]["stock"] = 0; 
				}else{
					$productos[$key]["stock"] = round($stock[0]["stockactualconvertido"],2);
				}

				$productos[$key]["unidades"] = $unidades[0]["unidades"];
			}

			echo json_encode($productos);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function nuevo(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$perfil = '';
				if($_SESSION["phuyu_codperfil"] > 3){
					$perfil .= ' AND empleado.codpersona = '.$_SESSION["phuyu_codempleado"];
				}
				$comprobantes = $this->db->query("select distinct(ct.codcomprobantetipo) as codigo, ct.*, c.seriecomprobante from caja.comprobantetipos as ct inner join caja.comprobantes as c on(ct.codcomprobantetipo=c.codcomprobantetipo) where c.codsucursal=".$_SESSION["phuyu_codsucursal"]." and c.codcomprobantetipo=9 and c.estado=1")->result_array();
				$comprobantesreferencia = $this->db->query("select distinct(ct.codcomprobantetipo) as codigo, ct.* from caja.comprobantetipos as ct inner join caja.comprobantes as c on(ct.codcomprobantetipo=c.codcomprobantetipo) where c.codsucursal=".$_SESSION["phuyu_codsucursal"]." and c.codcomprobantetipo>=5 and c.estado=1")->result_array();
				$tipopagos = $this->db->query("select *from caja.tipopagos where ingreso=1 and estado=1 order by codtipopago")->result_array();
				$vendedores = $this->db->query("select persona.codpersona,persona.razonsocial from public.personas as persona inner join public.empleados as empleado on(persona.codpersona=empleado.codpersona) where empleado.estado=1 ".$perfil."")->result_array();
				$sucursal = $this->db->query("select coalesce(codcomprobantetipo,8) as codcomprobantetipo, seriecomprobante from public.sucursales where codsucursal=".$_SESSION["phuyu_codsucursal"])->result_array();
				$sucursalreferencia = $this->db->query("select coalesce(codcomprobantetipo,12) as codcomprobantetipo, seriecomprobante from public.sucursales where codsucursal=".$_SESSION["phuyu_codsucursal"])->result_array();
				$centrocostos = $this->db->query("select *from caja.centrocostos where estado=1")->result_array();
				$this->load->view("compras/proformas/nuevo",compact("comprobantes","comprobantesreferencia","tipopagos","vendedores","sucursal","sucursalreferencia","centrocostos"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}
	public function historial($codpersona){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$persona = $this->db->query("select codpersona, razonsocial from public.personas where codpersona=".$codpersona)->result_array();
				$this->load->view("ventas/proformas/historial",compact("persona"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function filtro_proformas(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			if ($this->request->filtro==1) {
				$filtro = " and fechaproforma>='".$this->request->fechadesde."' and fechaproforma<='".$this->request->fechahasta."' ";
			}else{
				$filtro = "";
			}

			if ($this->request->estado!="") {
				$filtro = $filtro." and estado=".$this->request->estado;
			}

			$proformas = $this->db->query("select codproforma from kardex.proformas where codpersona=".$this->request->codpersona." and codsucursal=".$_SESSION["phuyu_codsucursal"]." and estado=1")->result_array();
			foreach ($proformas as $key => $value) {
				$detalle = $this->db->query("select coalesce(sum(cantidad),0) as cantidad from kardex.proformasdetalle where codproforma=".$value["codproforma"]." and estado=1")->result_array();
				$atendido = $this->db->query("select coalesce(sum(cantidad),0) as cantidad from restaurante.atendidos where codproforma=".$value["codproforma"])->result_array();
				if ($detalle[0]["cantidad"] == $atendido[0]["cantidad"]) {
					$data = array('estado' => 2);
					$this->db->where("codproforma", $value["codproforma"]);
					$estado = $this->db->update("kardex.proformas", $data);
				}
			}

			$proformas = $this->db->query("select codproforma,fechaproforma,cliente,direccion, importe, estado from kardex.proformas where codpersona=".$this->request->codpersona." and codsucursal=".$_SESSION["phuyu_codsucursal"]." ".$filtro." order by codproforma")->result_array();
			$total = 0;
			foreach ($proformas as $key => $value) {
				$total = $total + (double)$value["importe"];
			}
			$totales = $this->db->query("select ".number_format($total,2,".","")." as total")->result_array();

			$data["proformas"] = $proformas;
			$data["totales"] = $totales;
			echo json_encode($data);
		}else{
			$this->load->view("phuyu/404");
		}
	}
	
	function atender($codproforma){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$info = $this->db->query("select pedido.* from kardex.proformas as pedido where pedido.codproforma=".$codproforma)->result_array();
				$this->load->view("ventas/proformas/atender",compact("info"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function ver($codproforma){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_codusuario"])){
				$info = $this->db->query("select proforma.*,p.*,comprobantes.descripcion as tipo,(CASE WHEN condicionpago = 1 THEN 'CONTADO' ELSE 'CREDITO' END) AS pago,(CASE WHEN estadoproceso = 0 THEN 'PENDIENTE' ELSE 'ATENDIDO' END) AS proceso from kardex.proformas as proforma inner join caja.comprobantetipos as comprobantes on(proforma.codcomprobantetipo=comprobantes.codcomprobantetipo) INNER JOIN public.personas as p on (proforma.codpersona=p.codpersona) where proforma.codproforma=".$codproforma)->result_array();
				$detalle = $this->db->query("select pd.*,p.descripcion AS producto,p.codigo,u.descripcion as unidad FROM kardex.proformasdetalle pd JOIN almacen.productos p ON pd.codproducto = p.codproducto JOIN almacen.unidades u ON pd.codunidad = u.codunidad where codproforma=".$codproforma)->result_array();
				$cantidad = 0; $atendido = 0;
				foreach ($detalle as $key => $value) {
					$detalle[$key]["atendido"] = round($value["cantidadcomprobante"],2);
					$detalle[$key]["falta"] = round($value["cantidad"] - $value["cantidadcomprobante"]);
					$cantidad = $cantidad + $value["cantidad"]; $atendido = $atendido + $value["cantidadcomprobante"];
				}
				$totales = $this->db->query("select ".round($cantidad,2)." as cantidad, ".round($atendido,2)." as atendido")->result_array();
				
				$this->load->view("ventas/proformas/ver",compact("info","detalle","totales")); 
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

				$campos = ["codsucursal","codalmacen","codusuario","codpersona","fechaproforma","hora","valorventa","porcigv","igv","importe","razonsocial","direccion","codempleado", "afectastock","afectacaja","descripcion","codcomprobantetipo","seriecomprobante","condicionpago","nrocomprobante","tipoproforma"];
				$valores = [
					(int)$_SESSION["phuyu_codsucursal"],
					(int)$_SESSION["phuyu_codalmacen"],
					(int)$_SESSION["phuyu_codusuario"],
					(int)$this->request->campos->codpersona,
					$this->request->campos->fechacomprobante,
					date('H:i:s'),
					(double)$this->request->totales->subtotal,
					(double)$_SESSION["phuyu_igv"],
					(double)$this->request->totales->igv,
					(double)$this->request->totales->importe,
					$this->request->campos->cliente,
					$this->request->campos->direccion,
					$this->request->campos->codempleado,
					1,
					(int)$this->request->campos->afectacaja,
					$this->request->campos->descripcion,
					$this->request->campos->codcomprobantetipo,
					$this->request->campos->seriecomprobante,
					$this->request->campos->condicionpago,
					$this->request->campos->nro,
					2
				];

				if($this->request->campos->codproforma == 0){
					$codproforma = $this->phuyu_model->phuyu_guardar("kardex.proformas", $campos, $valores, "true");

					$actual = $this->db->query("select nrocorrelativo from caja.comprobantes where codcomprobantetipo=".$this->request->campos->codcomprobantetipo." and seriecomprobante='".$this->request->campos->seriecomprobante."' and codsucursal=".$_SESSION["phuyu_codsucursal"]." and estado=1")->result_array();

					$nrocorrelativo = (int)($actual[0]["nrocorrelativo"]) + 1;
					$data = ["nrocorrelativo"];

					$valores = [$nrocorrelativo];

					$f = ["codsucursal","codcomprobantetipo","seriecomprobante"];
					$v = [$_SESSION["phuyu_codsucursal"],$this->request->campos->codcomprobantetipo,$this->request->campos->seriecomprobante];
					$estado = $this->phuyu_model->phuyu_editar_1("caja.comprobantes", $data, $valores, $f, $v);

					$nrocorrelativo = str_pad($nrocorrelativo, 8, "0", STR_PAD_LEFT);
					$data = ["nrocomprobante"];
					$valores = [$nrocorrelativo];

					$estado = $this->phuyu_model->phuyu_editar("kardex.proformas", $data, $valores, "codproforma",$codproforma);

					$item = 0;
					foreach ($this->request->detalle as $key => $value) { 
						$item = $item + 1;

						$campos = ["codproforma","codproducto","codunidad","item","cantidad","preciounitario","preciosinigv","preciobruto","valorventa","preciorefunitario","codafectacionigv","subtotal","descripcion"];
						$valores =[
							(int)$codproforma,
							(int)$this->request->detalle[$key]->codproducto,
							(int)$this->request->detalle[$key]->codunidad, $item,
							(double)$this->request->detalle[$key]->cantidad,
							(double)$this->request->detalle[$key]->precio,
							(double)$this->request->detalle[$key]->preciosinigv,
							(double)$this->request->detalle[$key]->preciobruto,
							(double)$this->request->detalle[$key]->subtotal,
							(double)$this->request->detalle[$key]->preciorefunitario,
							$this->request->detalle[$key]->codafectacionigv,
							(double)$this->request->detalle[$key]->subtotal,
							$this->request->detalle[$key]->descripcion
						];
						$estado = $this->phuyu_model->phuyu_guardar("kardex.proformasdetalle", $campos, $valores);
					}

					/* REGISTRO CREDITO POR COBRAR */

					if ($this->request->campos->condicionpago==2) {
						$estado = $this->Caja_model->phuyu_creditoproforma($codproforma, 0, 1, $this->request->campos, $this->request->totales, $this->request->cuotas);
					}

					if ($this->db->trans_status() === FALSE){
					    $this->db->trans_rollback(); $estado = 0;
					}else{
						if ($estado!=1) {
							$this->db->trans_rollback(); $estado = 0;
						}
						$this->db->trans_commit();
					}
				}else{
					$codproforma = $this->request->campos->codproforma;
				    $estado = $this->phuyu_model->phuyu_editar("kardex.proformas", $campos, $valores, "codproforma", $codproforma);
				    $estado = $this->phuyu_model->phuyu_eliminar_total("kardex.proformasdetalle","codproforma",$codproforma);
				    if (count($this->request->detalle) > 0) {
				    	$item = 0;
                        foreach ($this->request->detalle as $key => $value) { 
							$item = $item + 1;

							$campos = ["codproforma","codproducto","codunidad","item","cantidad","preciounitario","preciobruto","preciosinigv","valorventa","preciorefunitario","codafectacionigv","subtotal","descripcion"];
							$valores =[
								(int)$codproforma,
								(int)$this->request->detalle[$key]->codproducto,
								(int)$this->request->detalle[$key]->codunidad, $item,
								(double)$this->request->detalle[$key]->cantidad,
								(double)$this->request->detalle[$key]->precio,
								(double)$this->request->detalle[$key]->preciobruto,
								(double)$this->request->detalle[$key]->preciosinigv,
								(double)$this->request->detalle[$key]->subtotal,
								(double)$this->request->detalle[$key]->preciorefunitario,
								$this->request->detalle[$key]->codafectacionigv,
								(double)$this->request->detalle[$key]->subtotal,
								$this->request->detalle[$key]->descripcion
							];
							$estado = $this->phuyu_model->phuyu_guardar("kardex.proformasdetalle", $campos, $valores);
						}
				    }

				    if((int)$this->request->campos->condicionpago==2 && (int)$this->request->campos->codcreditoproforma > 0){

				    	$this->request->campos->codlote = (isset($this->request->campos->codlote)) ? $this->request->campos->codlote : 0;

				    	$campos = ["codsucursal","codcaja","codcreditoconcepto","codpersona","codempleado","codmovimiento","codusuario","tipo","fechacredito","fechainicio","nrodias","nrocuotas","importe","tasainteres","interes","saldo","total","codlote","cliente","direccion"];

				    	$valores = [
							(int)$_SESSION["phuyu_codsucursal"],
							(int)$_SESSION["phuyu_codcaja"],
							(int)$this->request->campos->codcreditoconcepto,
							(int)$this->request->campos->codpersona,
							(int)$this->request->campos->codempleado,
							0,
							(int)$_SESSION["phuyu_codusuario"],1,
							$this->request->campos->fechacomprobante,
							$this->request->campos->fechacomprobante,
							(int)$this->request->campos->nrodias,
							(int)$this->request->campos->nrocuotas,
							(double)$this->request->totales->importe,
							(double)$this->request->campos->tasainteres,
							(double)$this->request->totales->interes,
							(double)$this->request->campos->totalcredito,
							(double)$this->request->campos->totalcredito,
							$this->request->campos->codlote,
							$this->request->campos->cliente,
							$this->request->campos->direccion
						];

				    	$estado = $this->phuyu_model->phuyu_editar("kardex.creditosproformas", $campos, $valores, "codcreditoproforma", (int)$this->request->campos->codcreditoproforma);

				    	$estado = $this->phuyu_model->phuyu_eliminar_total("kardex.cuotasproformas","codcreditoproforma",(int)$this->request->campos->codcreditoproforma);

				    	foreach ($this->request->cuotas as $key => $value) {
							$importe = (double)$this->request->cuotas[$key]->importe;
							$interes = (double)$this->request->cuotas[$key]->interes;
							$total = (double)$this->request->cuotas[$key]->total;
							if ($this->request->campos->codmoneda!=1) {
								$importe = round((double)$this->request->cuotas[$key]->importe * $this->request->campos->tipocambio,1);
								$interes = round($this->request->cuotas[$key]->interes * $this->request->campos->tipocambio,1);
								$total = round($this->request->cuotas[$key]->total * $this->request->campos->tipocambio,1);
							}
							$data = array(
								"codcreditoproforma" => (int)$this->request->campos->codcreditoproforma,
								"nrocuotaproforma" => (int)$this->request->cuotas[$key]->nrocuota,
								"codsucursal" => (int)$_SESSION["phuyu_codsucursal"],
								"fechavence" => $this->request->cuotas[$key]->fechavence,
								"importe" => (double)$importe,
								"saldo" => (double)$total,
								"interes" => (double)$interes,
								"total" => (double)$total
							);
							$estado = $this->db->insert("kardex.cuotasproformas", $data);
							$fechavence = $this->request->cuotas[$key]->fechavence;
						}
						$data = array(
							"fechavencimiento" => $fechavence
						);
						$this->db->where("codcreditoproforma", (int)$this->request->campos->codcreditoproforma);
						$estado = $this->db->update("kardex.creditosproformas", $data);
				    }

				    if((int)$this->request->campos->condicionpago==2 && (int)$this->request->campos->codcreditoproforma == 0){
				    	$estado = $this->Caja_model->phuyu_creditoproforma($codproforma, 0, 1, $this->request->campos, $this->request->totales, $this->request->cuotas);
				    }

				    if((int)$this->request->campos->condicionpago==1 && (int)$this->request->campos->codcreditoproforma > 0){
				    	$estado = $this->phuyu_model->phuyu_eliminar("kardex.creditosproformas", "codcreditoproforma", (int)$this->request->campos->codcreditoproforma);
				    }

					if ($this->db->trans_status() === FALSE){
					    $this->db->trans_rollback(); $estado = 0;
					}else{
						if ($estado!=1) {
							$this->db->trans_rollback(); $estado = 0;
						}
						$this->db->trans_commit();
					}
				}

				$data["estado"] = $estado; $data["codproforma"] = $codproforma;
				echo json_encode($data);
			}else{
				echo json_encode("e");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function editar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			$total = $this->db->query("select count(*) as total FROM kardex.proformas k JOIN kardex.proformasdetalle kd ON k.codproforma = kd.codproforma JOIN public.personas as p on (k.codpersona=p.codpersona) WHERE k.codsucursal=".$_SESSION["phuyu_codsucursal"]." and k.estado=1 and tipoproforma = 2 AND estadoproceso = 0 AND k.codproforma =".$this->request->codregistro." HAVING sum(kd.cantidadcomprobante)=0")->result_array();

			if(count($total) > 0){
				$perfil = '';
				if($_SESSION["phuyu_codperfil"] > 3){
					$perfil .= ' AND empleado.codpersona = '.$_SESSION["phuyu_codempleado"];
				}
				$info=$this->db->query("select p.*,pr.razonsocial from kardex.proformas p inner join public.personas as pr ON p.codpersona=pr.codpersona where codproforma=".$this->request->codregistro)->result_array();
				$detalle = $this->db->query("select pd.*,p.descripcion AS producto,u.descripcion as unidad FROM kardex.proformasdetalle pd JOIN almacen.productos p ON pd.codproducto = p.codproducto JOIN almacen.unidades u ON pd.codunidad = u.codunidad where codproforma=".$this->request->codregistro)->result_array();

				$comprobantes = $this->db->query("select distinct(ct.codcomprobantetipo) as codigo, ct.*, c.seriecomprobante from caja.comprobantetipos as ct inner join caja.comprobantes as c on(ct.codcomprobantetipo=c.codcomprobantetipo) where c.codsucursal=".$_SESSION["phuyu_codsucursal"]." and c.codcomprobantetipo=9 and c.estado=1")->result_array();
				
				$vendedores = $this->db->query("select persona.codpersona,persona.razonsocial from public.personas as persona inner join public.empleados as empleado on(persona.codpersona=empleado.codpersona) where empleado.estado=1 ".$perfil."")->result_array();
				$tipopagos = $this->db->query("select *from caja.tipopagos where ingreso=1 and estado=1 order by codtipopago")->result_array();

	            $this->load->view("ventas/proformas/editar",compact("info","detalle","vendedores","tipopagos","comprobantes"));
			}else{
			    echo 1;
			}

		}else{
			$this->load->view("phuyu/404");
		}
	}

	function eliminar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			$pedido=$this->db->query("select *from kardex.kardexproforma where codproforma=".$this->request->codregistro)->result_array();
			if (count($pedido)==0) {
				$estado = $this->phuyu_model->phuyu_eliminar("kardex.proformas", "codproforma", $this->request->codregistro);
				if ($estado == 1) {
					$mensaje = "PROFORMA ANULADO CORRECTAMENTE";
				}else{
					$mensaje = "OCURRIO UN ERROR AL ANULAR LA PROFORMA";
				}
			}else{
				$estado = 2;
				$mensaje = "LA PROFORMA FUE REGISTRADO EN UNA VENTA, SI DESEA ANULARLO, PRIMERO DEBE ANULAR LA VENTA";
			}
			
			$data["estado"] = $estado; $data["mensaje"] = $mensaje;
			echo json_encode($data);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function clonar(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$this->request = json_decode(file_get_contents('php://input'));
				$info = $this->db->query("select kardex.codproforma,kardex.fechaproforma, kardex.codcomprobantetipo,kardex.seriecomprobante, kardex.nrocomprobante,kardex.razonsocial as cliente,kardex.direccion,kardex.descripcion,personas.codpersona, personas.razonsocial,comprobantes.descripcion as tipo,kardex.valorventa,kardex.descglobal,kardex.igv,kardex.importe,kardex.condicionpago from kardex.proformas as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codproforma=".$this->request->codregistro)->result_array();

				$info[0]["nrodias"] = 30;
				$info[0]["nrocuotas"] = 1;
				$info[0]["tasainteres"] = 0;

				if($info[0]["condicionpago"]==2){
					$credito = $this->db->query("select *from kardex.creditosproformas where codproforma=".$info[0]["codproforma"]." AND estado = 1")->result_array();

					if(count($credito)>0){
						$info[0]["nrodias"] = $credito[0]["nrodias"];
						$info[0]["nrocuotas"] = $credito[0]["nrocuotas"];
						$info[0]["tasainteres"] = $credito[0]["tasainteres"];
					}
				}

				$socio = $this->db->query("Select *from personas WHERE codpersona=".$info[0]["codpersona"])->result_array();
				$detalle = $this->db->query("select kd.*,p.descripcion as producto,u.descripcion as unidad from kardex.proformasdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codproforma=".$this->request->codregistro." and kd.estado=1 order by kd.item")->result_array();

				foreach ($detalle as $key => $value) {
					$unn = $this->db->query("SELECT pun.unidades
											  FROM almacen.v_productounidades pun
											  WHERE pun.codproducto = ".$value["codproducto"]." and pun.codalmacen=".$_SESSION["phuyu_codalmacen"]."  ")->result_array();

					$unidades = []; $factores = []; $logo = []; $arreglo = []; $putunidades = [];
					$unidades = explode(";", $unn[0]["unidades"]);
					foreach ($unidades as $k => $v) {
						$factores = explode("|", $unidades[$k]);
			    		$logo = array("descripcion"=>$factores[1],"codunidad"=>$factores[0],"factor"=>$factores[8]);
			    		if($factores[0]==$value["codunidad"]){
			    			$detalle[$key]["stock"] = $factores[3];
			    		}
			    		array_push($putunidades, $logo);
					}

					$detalle[$key]["unidades"] = $putunidades;
					$detalle[$key]["precio"] = round($detalle[$key]["preciounitario"],2);
					$detalle[$key]["cantidad"] = round($detalle[$key]["cantidad"],2);
					$detalle[$key]["control"] = 0;

				}

				echo json_encode(["campos"=>$info,"socio"=>$socio,"detalle"=>$detalle]);
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function formato($formato){
		if ($this->input->is_ajax_request()) {
			$campos = ["formato"]; $valores = [$formato];
			$f = ["codsucursal","codcomprobantetipo"]; $v = [$_SESSION["phuyu_codsucursal"],9];
			$estado = $this->phuyu_model->phuyu_editar_1("caja.comprobantes", $campos, $valores, $f, $v);

			echo $formato;
		}
	}

	function obtenercredito($codproforma){
		if ($this->input->is_ajax_request()) {
			$cuotas = $this->db->query("select *from kardex.creditosproformas where codproforma=".$codproforma." AND estado = 1")->result_array();

			echo json_encode($cuotas);
		}
	}
	
}