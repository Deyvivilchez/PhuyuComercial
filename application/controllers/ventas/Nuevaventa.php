<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Nuevaventa extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model"); $this->load->model("Caja_model"); $this->load->model("Kardex_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_codusuario"])) {

				/* CODIGO TEMPORAL DE LA IMPRESION */

				$formato = $this->db->query("select formato from caja.comprobantes where codcomprobantetipo=10 AND codsucursal = ".$_SESSION["phuyu_codsucursal"])->result_array();
				if (count($formato)==0) {
					$_SESSION["phuyu_formato"] = "a4";
				}else{
					$_SESSION["phuyu_formato"] = $formato[0]["formato"];
				}
				$perfil = '';
				if($_SESSION["phuyu_codperfil"] > 3){
					$perfil .= ' AND empleado.codpersona = '.$_SESSION["phuyu_codempleado"];
				}
				$monedas = $this->db->query("select *from caja.monedas where estado=1 order by codmoneda asc")->result_array();
				$comprobantes = $this->db->query("select distinct(ct.codcomprobantetipo) as codigo, ct.* from caja.comprobantetipos as ct inner join caja.comprobantes as c on(ct.codcomprobantetipo=c.codcomprobantetipo) where c.codsucursal=".$_SESSION["phuyu_codsucursal"]." and ct.venta = 1 and c.estado=1")->result_array();
				$conceptos = $this->db->query("select *from caja.conceptos where codconcepto=13 or codconcepto=15")->result_array();
				$tipopagos = $this->db->query("select *from caja.tipopagos where ingreso=1 and estado=1 order by codtipopago")->result_array();
				$vendedores = $this->db->query("select persona.codpersona,persona.razonsocial from public.personas as persona inner join public.empleados as empleado on(persona.codpersona=empleado.codpersona) where empleado.estado=1 ".$perfil."")->result_array();
				$sucursal = $this->db->query("select coalesce(codcomprobantetipo,12) as codcomprobantetipo, seriecomprobante from public.sucursales where codsucursal=".$_SESSION["phuyu_codsucursal"])->result_array();
				$centrocostos = $this->db->query("select *from caja.centrocostos where estado=1")->result_array();
				$afectacionigv = $this->db->query("select *from afectacionigv where estado = 1")->result_array();
				$this->load->view("ventas/ventas/nuevaventa",compact("comprobantes","conceptos","tipopagos","vendedores","sucursal","centrocostos","monedas","afectacionigv"));
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
					$fechas = "kardex.fechacomprobante>='".$this->request->fechas->desde."' and kardex.fechacomprobante<='".$this->request->fechas->hasta."' and";
				}else{
					$fechas = "kardex.fechacomprobante<='".$this->request->fechas->hasta."' and";
				}
			}
			$lista = $this->db->query("select kardex.hora,personas.documento,kardex.cliente,kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante,kardex.condicionpago, kardex.nrocomprobante, kardex.fechacomprobante,round(kardex.importe,2) as importe,kardex.estado, comprobantes.descripcion as tipo,comprobantes.abreviatura from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where ".$fechas." (UPPER(personas.documento) like UPPER('%".$this->request->buscar."%') or UPPER(personas.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(kardex.cliente) like UPPER('%".$this->request->buscar."%') or UPPER(comprobantes.descripcion) like UPPER('%".$this->request->buscar."%') or UPPER(kardex.seriecomprobante) like UPPER('%".$this->request->buscar."%') or UPPER(kardex.nrocomprobante) like UPPER('%".$this->request->buscar."%') ) and kardex.codmovimientotipo=20 and kardex.codsucursal=".$_SESSION["phuyu_codsucursal"]." order by kardex.fechacomprobante desc,kardex.hora desc offset ".$offset." limit ".$limit)->result_array();

			foreach ($lista as $key => $value) {
				$info = $this->db->query("select codpedido from kardex.kardexpedido where codkardex=".$value["codkardex"])->result_array();

				if(count($info) > 0){
					$pedido = $this->db->query("select seriecomprobante,nrocomprobante from kardex.pedidos where codpedido=".$info[0]["codpedido"])->result_array();

					$lista[$key]["referencia"] = $pedido[0]['seriecomprobante'].'-'.$pedido[0]['nrocomprobante'];
				}else{
					$lista[$key]["referencia"] = '';
				}

				$hora = explode(".", $lista[$key]["hora"]);
				$lista[$key]["hora"] = $hora[0];

				$kardexsunat = $this->db->query("select estado from sunat.kardexsunat where codkardex = ".$value["codkardex"])->result_array();

				if(count($kardexsunat)){
					$lista[$key]["estadosunat"] = $kardexsunat[0]["estado"];
				}else{
					$lista[$key]["estadosunat"] = 2;
				}

			}

			$total = $this->db->query("select count(*) as total from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where ".$fechas." (UPPER(personas.documento) like UPPER('%".$this->request->buscar."%') or UPPER(personas.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(personas.nombrecomercial) like UPPER('%".$this->request->buscar."%') or UPPER(comprobantes.descripcion) like UPPER('%".$this->request->buscar."%') or UPPER(kardex.seriecomprobante) like UPPER('%".$this->request->buscar."%') or UPPER(kardex.nrocomprobante) like UPPER('%".$this->request->buscar."%') ) and kardex.codmovimientotipo=20 and kardex.codsucursal=".$_SESSION["phuyu_codsucursal"])->result_array();

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

			if(isset($this->request->tabla) &&  $this->request->tabla == "compra"){
				$movimiento = 2;
			}else{
				$movimiento = 20;
			}

			$lista = $this->db->query("SELECT p.documento,kardex.cliente,kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante,kardex.condicionpago, kardex.nrocomprobante, kardex.fechacomprobante,round(kardex.importe,2) as importe,kardex.estado, comprobantes.descripcion as tipo,kardex.hora from kardex.kardex as kardex JOIN kardex.kardexdetalle kd ON kardex.codkardex = kd.codkardex JOIN public.personas as p on (kardex.codpersona=p.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) WHERE (UPPER(p.documento) ilike UPPER('%".$this->request->buscar."%') or UPPER(p.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(kardex.cliente) like UPPER('%".$this->request->buscar."%') or UPPER(kardex.seriecomprobante) like UPPER('%".$this->request->buscar."%') or UPPER(kardex.nrocomprobante) like UPPER('%".$this->request->buscar."%') ) and kardex.codsucursal=".$_SESSION["phuyu_codsucursal"]." and kardex.estado=1 and kardex.codmovimientotipo=".$movimiento." GROUP BY kardex.codkardex, kardex.codsucursal,kardex.codpersona, kardex.importe, comprobantes.descripcion, kardex.cliente,kardex.fechacomprobante,kardex.hora,kardex.seriecomprobante,kardex.nrocomprobante,p.documento HAVING sum(kd.cantidadguia) < sum(kd.cantidad) order by kardex.nrocomprobante desc offset ".$offset." limit ".$limit)->result_array();

			$total = $this->db->query("select count(*) as total FROM kardex.kardex k JOIN kardex.kardexdetalle kd ON k.codkardex = kd.codkardex JOIN public.personas as p on (k.codpersona=p.codpersona) WHERE (UPPER(p.documento) ilike UPPER('%".$this->request->buscar."%') or UPPER(p.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(k.cliente) like UPPER('%".$this->request->buscar."%') or UPPER(k.seriecomprobante) like UPPER('%".$this->request->buscar."%') or UPPER(k.nrocomprobante) like UPPER('%".$this->request->buscar."%') ) and k.codsucursal=".$_SESSION["phuyu_codsucursal"]." and k.estado=1 and k.codmovimientotipo=".$movimiento." HAVING sum(kd.cantidadguia) < sum(kd.cantidad)")->result_array();

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
				$this->load->view("ventas/ventas/buscar");
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function buscarproductos($codkardex){
		if ($this->input->is_ajax_request()) {
			$productos = $this->db->query("select pd.*,p.descripcion,u.descripcion as unidad FROM kardex.kardexdetalle pd JOIN almacen.productos p ON pd.codproducto = p.codproducto JOIN almacen.unidades u ON pd.codunidad = u.codunidad where codkardex=".$codkardex." and pd.cantidad > pd.cantidadguia")->result_array();

			foreach ($productos as $key => $value) {

				$unidades = $this->db->query("select *FROM almacen.v_productounidades pun where pun.codproducto=".$value["codproducto"]." ")->result_array();

				$productos[$key]["unidades"] = $unidades[0]["unidades"];
			}

			echo json_encode($productos);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function buscarventa($codkardex){
		if ($this->input->is_ajax_request()) {
			$venta = $this->db->query("select k.*, comprobantes.descripcion as tipo FROM kardex.kardex k inner join caja.comprobantetipos as comprobantes on(k.codcomprobantetipo=comprobantes.codcomprobantetipo) where k.codkardex=".$codkardex." and k.estado = 1")->result_array();
			echo json_encode($venta);
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
				$monedas = $this->db->query("select *from caja.monedas where estado=1 order by codmoneda asc")->result_array();
				$comprobantes = $this->db->query("select distinct(ct.codcomprobantetipo) as codigo, ct.* from caja.comprobantetipos as ct inner join caja.comprobantes as c on(ct.codcomprobantetipo=c.codcomprobantetipo) where c.codsucursal=".$_SESSION["phuyu_codsucursal"]." and ct.venta = 1 and c.estado=1")->result_array();
				$conceptos = $this->db->query("select *from caja.conceptos where codconcepto=13 or codconcepto=15")->result_array();
				$tipopagos = $this->db->query("select *from caja.tipopagos where ingreso=1 and estado=1 order by codtipopago")->result_array();
				$vendedores = $this->db->query("select persona.codpersona,persona.razonsocial from public.personas as persona inner join public.empleados as empleado on(persona.codpersona=empleado.codpersona) where empleado.estado=1 ".$perfil."")->result_array();
				$sucursal = $this->db->query("select coalesce(codcomprobantetipo,12) as codcomprobantetipo, seriecomprobante from public.sucursales where codsucursal=".$_SESSION["phuyu_codsucursal"])->result_array();
				$centrocostos = $this->db->query("select *from caja.centrocostos where estado=1")->result_array();
				$afectacionigv = $this->db->query("select *from afectacionigv where estado = 1")->result_array();
				$this->load->view("ventas/ventas/nuevaventa",compact("comprobantes","conceptos","tipopagos","vendedores","sucursal","centrocostos","monedas","afectacionigv"));
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
				$info = $this->db->query("select kardex.*,p.documento,(CASE WHEN condicionpago = 1 THEN 'CONTADO' ELSE 'CREDITO' END) AS pago,mt.descripcion AS movimiento,comprobantes.descripcion as tipo from kardex.kardex as kardex inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) INNER JOIN public.personas as p on (kardex.codpersona=p.codpersona) INNER JOIN almacen.movimientotipos as mt on (kardex.codmovimientotipo=mt.codmovimientotipo) where kardex.codkardex=".$codregistro)->result_array();

				$detalle = $this->db->query("select kd.*,p.descripcion as producto,u.descripcion as unidad,p.codigo from kardex.kardexdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codkardex=".$codregistro." and kd.estado=1 order by kd.item")->result_array();

				$pagos = $this->db->query("select p.descripcion as tipopago, md.importe,md.importeentregado,md.vuelto,md.nrodocbanco from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) inner join caja.tipopagos as p on(md.codtipopago=p.codtipopago) where m.codkardex=".$codregistro." and m.estado=1 order by p.codtipopago")->result_array();
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

				//echo 1;exit;
				//REVISAMOS SI EL PEDIDO SIGUE ACTIVO
				if($this->request->codpedido != 0){
					$info = $this->db->query("select *from kardex.pedidos where codpedido=".$this->request->codpedido)->result_array();
					if($info[0]["estado"] == 0){
						echo json_encode("e");exit;
					}
				}

				if($this->request->codproforma != 0){
					$info = $this->db->query("select *from kardex.proformas where codproforma=".$this->request->codproforma)->result_array();
					if($info[0]["estado"] == 0){
						echo json_encode("e");exit;
					}
				}

				$this->request->campos->codpersona = ($this->request->codpersonapedido == 0) ? $this->request->campos->codpersona : $this->request->codpersonapedido;
                
                //VERIFICAMOS SI ES BOLETA Y EL IMPORTE SEA MENOR A 700
				if($this->request->campos->codpersona == 2 && $this->request->campos->codcomprobantetipo == 12){
					if($this->request->totales->importe >= 700){
						echo json_encode("e");exit;
					}
				}

				$this->request->campos->codlote = (!isset($this->request->campos->codlote) || empty($this->request->campos->codlote)) ? 0 : $this->request->campos->codlote;

				$this->db->trans_begin();

				/* REGISTRO KARDEX Y KARDEXDETALLE */

				$codkardex = $this->Kardex_model->phuyu_kardex($this->request->campos, $this->request->totales, 0); 
				$codkardexalmacen = 0; $retirar = $this->request->campos->retirar; $estado = 1;
				if ($retirar == 1) {
					$codkardexalmacen = $this->Kardex_model->phuyu_kardexalmacen($codkardex, 4, $this->request->campos);
				}

				$detalle = $this->Kardex_model->phuyu_kardexdetalle($codkardex, $codkardexalmacen, $this->request->detalle, $retirar, 0,$this->request->codpedido,$this->request->codproforma);

				//echo json_encode($detalle['success']);exit;
				if(!$detalle['success']){
					$data["estado"] = 0; $data["informacion"] = $detalle;
				    echo json_encode($data);exit;
				}


				if($this->request->codpedido != 0){
                    $detallepedido = $this->phuyu_model->phuyu_pedidodetalle($this->request->codpedido, $this->request->detalle);
                    if($this->request->campos->terminarpedido == true){
						$campos = ["estadoproceso"];
						$valores = [1];

						$estado_u = $this->phuyu_model->phuyu_editar("kardex.pedidos", $campos, $valores, "codpedido",$this->request->codpedido);
					}
				}

				if($this->request->codproforma != 0){
                    $detalleproforma = $this->phuyu_model->phuyu_proformadetalle($this->request->codproforma, $this->request->detalle);
                    if($this->request->campos->terminarpedido == true){
						$campos = ["estadoproceso"];
						$valores = [1];

						$estado_u = $this->phuyu_model->phuyu_editar("kardex.proformas", $campos, $valores, "codproforma",$this->request->codproforma);
					}
				}

				/* REGISTRO MOVIMIENTO DE CAJA */

				if ($this->request->campos->codmoneda!=1) {
					$importe = round($this->request->totales->importe * $this->request->campos->tipocambio,2);
					$importemoneda = $this->request->totales->importe;
				}else{
					$importe = $this->request->totales->importe;
					$importemoneda = $this->request->totales->importe;
				}

				$codmovimiento = $this->Caja_model->phuyu_movimientos($codkardex, 1, 1, $importe, $this->request->campos,$importemoneda);
				if ($this->request->campos->condicionpago==1) {
					$estado = $this->Caja_model->phuyu_movimientosdetalle($codmovimiento, $this->request->pagos);
				}

				/* REGISTRO CREDITO POR COBRAR */

				if ($this->request->campos->condicionpago==2) {
					$persona = $this->db->query("select documento,d.abreviatura as tipo from public.personas p inner join public.documentotipos d on(p.coddocumentotipo=d.coddocumentotipo) where p.codpersona=".$this->request->campos->codpersona)->result_array();

					$estado = $this->Caja_model->phuyu_credito($codkardex, $codmovimiento, 1, $this->request->campos, $this->request->totales, $this->request->cuotas,$persona[0]["tipo"].'-'.$persona[0]["documento"]);
				}

				/* COMPROBANTE ELECTRONICO PARA SUNAT: REGISTRO EN KARDEX SUNAT */

				if ($this->request->campos->codcomprobantetipo==10 || $this->request->campos->codcomprobantetipo==12) {
					$kardex = $this->db->query("select nrocomprobante from kardex.kardex where codkardex=".$codkardex)->result_array();
					if ($this->request->campos->codcomprobantetipo==10) {
						$xml = $_SESSION["phuyu_ruc"]."-01-".$this->request->campos->seriecomprobante."-".$kardex[0]["nrocomprobante"];
					}else{
						$xml = $_SESSION["phuyu_ruc"]."-03-".$this->request->campos->seriecomprobante."-".$kardex[0]["nrocomprobante"];
					}
					$campos = ["codkardex","codsucursal","codusuario","fechacreado","nombre_xml"];
					$valores = [
						(int)$codkardex,(int)$_SESSION["phuyu_codsucursal"],(int)$_SESSION["phuyu_codusuario"],
						$this->request->campos->fechacomprobante, $xml
					];
					$estado = $this->phuyu_model->phuyu_guardar("sunat.kardexsunat", $campos, $valores);
				}

				if ($this->db->trans_status() === FALSE){
				    $this->db->trans_rollback(); $estado = 0;
				}else{
					if ($estado!=1) { 
						$this->db->trans_rollback(); $estado = 0; 
					}
					$this->db->trans_commit();
				}
				$data["estado"] = $estado; $data["codkardex"] = $codkardex;
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
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$this->request = json_decode(file_get_contents('php://input'));
				$info = $this->db->query("select kardex.codkardex,kardex.fechacomprobante,kardex.fechakardex, kardex.seriecomprobante, kardex.nrocomprobante,kardex.nroplaca,kardex.cliente,kardex.direccion,kardex.descripcion,personas.codpersona, personas.razonsocial,comprobantes.descripcion as tipo from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codkardex=".$this->request->codregistro)->result_array();
				$sunat_existe = $this->db->query("select estado from sunat.kardexsunat where codkardex=".$this->request->codregistro)->result_array();
				if (count($sunat_existe)==0) {
					$sunat = 0;
				}else{
					if ($sunat_existe[0]["estado"]==0) {
						$sunat = 0;
					}else{
						$sunat = 1;
					}
				}
				$this->load->view("ventas/ventas/editar",compact("info","sunat"));
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

			$campos = ["codpersona","fechacomprobante","fechakardex","cliente","direccion","descripcion","nroplaca"];
			$valores = [
				$this->request->codpersona,
				$this->request->fechacomprobante,
				$this->request->fechakardex,
				$this->request->cliente,
				$this->request->direccion,
				$this->request->descripcion,
				$this->request->nroplaca
			];
			$estado = $this->phuyu_model->phuyu_editar("kardex.kardex", $campos, $valores, "codkardex",$this->request->codregistro);

			$campos = ["fechakardex"]; $valores = [$this->request->fechakardex];
			$estado_u = $this->phuyu_model->phuyu_editar("kardex.kardexalmacen", $campos, $valores, "codkardex",$this->request->codregistro);

			$campos = ["codpersona","fechacredito"]; $valores = [$this->request->codpersona,$this->request->fechacomprobante];
			$estado_u = $this->phuyu_model->phuyu_editar("kardex.creditos", $campos, $valores, "codkardex",$this->request->codregistro);
			$campos = ["codpersona","fechamovimiento"]; $valores = [$this->request->codpersona,$this->request->fechacomprobante];
			$estado_u = $this->phuyu_model->phuyu_editar("caja.movimientos", $campos, $valores, "codkardex",$this->request->codregistro);

			echo $estado;
		}
	}

	function eliminar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$this->db->trans_begin();

			//REVISAMOS SI LA VENTA YA ESTA ELIMINADO	

			// REVISAMOS SI ESTA SUJETO A UNA NOTA DE CREDITO

			$comprobante = $this->db->query("select *from kardex.kardex where codkardex_ref=".$this->request->codregistro." and estado<>0 and codmovimientotipo=8")->result_array();

			if(count($comprobante) > 0){
				$this->db->trans_rollback(); 
				$data["estado"] = 5;
				$data["mensaje"] = "La venta no puede ser anulada porque está sujeta a la nota de credito ".$comprobante[0]["seriecomprobante"]."-".$comprobante[0]["nrocomprobante"]; 
				echo json_encode($data); exit();
			}		

			$comprobante = $this->db->query("select *from kardex.kardex where codkardex=".$this->request->codregistro." and estado<>0")->result_array();

			// REVISAMOS LA FECHA DE EMISION SI ES FACTURA O BOLETA

			$dteStart = new DateTime($comprobante[0]["fechacomprobante"]); 
            $dteEnd   = new DateTime(date('Y-m-d'));
            $dteDiff  = $dteStart->diff($dteEnd);
            $diferencia = $dteDiff->days;

            if($comprobante[0]["codcomprobantetipo"] == 10 || $comprobante[0]["codcomprobantetipo"] == 12){
            	if((int)$diferencia > 3){
                   $this->db->trans_rollback(); 
                    $data["estado"] = 2;
					$data["mensaje"] = "Los dias máximos de anulación de ventas sobrepasó el límite, desea de todas maneras eliminar este comprobante para control interno?";
                    echo json_encode($data); exit();
                }
            }

			// SI EXISTE EN CREDITOS //
			$credito = $this->db->query("select *from kardex.creditos where codkardex=".$this->request->codregistro." and estado<>0")->result_array();
			if (count($credito)>0) {
				$this->db->trans_rollback();
				$data["estado"] = 3;
				$data["mensaje"] = "La venta no puede ser anulado porque está sujeto a un crédito, debe anular el crédito en cuentas por cobrar en el modulo AREA CREDITOS y volver a intentar"; 
				echo json_encode($data); exit();
			}

			// ACTUALIZAMOS PRODUCTOS UBICACION //
			$kardexalmacen = $this->db->query("select codkardexalmacen from kardex.kardexalmacen where codkardex=".$this->request->codregistro)->result_array();

			$info = $this->db->query("select *from kardex.kardexdetalle where codkardex=".$this->request->codregistro)->result_array();
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
			$estado = $this->phuyu_model->phuyu_eliminar("kardex.kardex", "codkardex", $this->request->codregistro);
            if(count($kardexalmacen) > 0){
				$estado = $this->phuyu_model->phuyu_eliminar("kardex.kardexalmacen", "codkardexalmacen", $kardexalmacen[0]["codkardexalmacen"]);
            }

			// REGISTRO KARDEX ANULADOS //
			$campos = ["codkardex","codsucursal","codusuario","fechaanulacion","observaciones"];
			$valores =[
				(int)$this->request->codregistro, (int)$_SESSION["phuyu_codsucursal"], (int)$_SESSION["phuyu_codusuario"], date("Y-m-d"),
				$this->request->observaciones
			];
			$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexanulados", $campos, $valores);
            
            if(count($kardexalmacen) > 0){
				// REGISTRO KARDEX ALMACEN ANULADOS //
				$campos = ["codkardexalmacen","codsucursal","codusuario","fechaanulacion","observaciones"];
				$valores =[
					(int)$kardexalmacen[0]["codkardexalmacen"], (int)$_SESSION["phuyu_codsucursal"], (int)$_SESSION["phuyu_codusuario"], date("Y-m-d"),
					$this->request->observaciones
				];
				$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexalmacenanulado", $campos, $valores);
            }
			//VERIFICAMOS Y ELIMINAMOS EN LA TABLA KARDEXPEDIDO

			$info = $this->db->query("select *from kardex.kardexpedido where codkardex=".$this->request->codregistro)->result_array();

			if(count($info) > 0){
				$codpedido = 0;
				foreach ($info as $key => $value) {

					$existepedido = $this->db->query("select *from kardex.pedidosdetalle where codpedido=".$value["codpedido"]." and codproducto=".$value["codproducto"]." and item=".$value["itempedido"])->result_array();

					$existeventa = $this->db->query("select *from kardex.kardexdetalle where codkardex=".$value["codkardex"]." and codproducto=".$value["codproducto"]." and item=".$value["itemkardex"])->result_array();

					$stock = $existepedido[0]["cantidadcomprobante"] - $existeventa[0]["cantidad"];

					$campos = ["cantidadcomprobante"]; $valores = [(double)$stock];
					$f = ["codpedido","codproducto","item"];
					$v = [(int)$value["codpedido"],(int)$value["codproducto"],(int)$value["itempedido"]];
					$estado = $this->phuyu_model->phuyu_editar_1("kardex.pedidosdetalle", $campos, $valores, $f, $v);
					$codpedido = $value["codpedido"];
				}
				$estado = $this->phuyu_model->phuyu_eliminar_total("kardex.kardexpedido", "codkardex",$this->request->codregistro);

				$estadoproceso = $this->db->query("select *FROM kardex.pedidosdetalle pd where pd.codpedido=".$codpedido." and pd.cantidad > pd.cantidadcomprobante")->result_array();

				if(count($estadoproceso) > 0){
					$data = array("estadoproceso" => 0);
					$this->db->where("codpedido", $codpedido);
		            $estado = $this->db->update("kardex.pedidos", $data);
				}
			}

			// ANULAMOS EL MOVIMIENTO DE CAJA //
			$movi = $this->db->query("select codmovimiento from caja.movimientos where codkardex=".$this->request->codregistro)->result_array();
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

	function eliminarinterno(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$this->db->trans_begin();

			// ACTUALIZAMOS PRODUCTOS UBICACION //
			$kardexalmacen = $this->db->query("select codkardexalmacen from kardex.kardexalmacen where codkardex=".$this->request->codregistro)->result_array();

			$info = $this->db->query("select *from kardex.kardexdetalle where codkardex=".$this->request->codregistro)->result_array();
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
			$estado = $this->phuyu_model->phuyu_eliminar("kardex.kardex", "codkardex", $this->request->codregistro);
            if(count($kardexalmacen) > 0){
				$estado = $this->phuyu_model->phuyu_eliminar("kardex.kardexalmacen", "codkardexalmacen", $kardexalmacen[0]["codkardexalmacen"]);
            }

            //VERIFICAMOS Y ELIMINAMOS EN LA TABLA KARDEXPEDIDO

			$info = $this->db->query("select *from kardex.kardexpedido where codkardex=".$this->request->codregistro)->result_array();

			if(count($info) > 0){
				$codpedido = 0;
				foreach ($info as $key => $value) {

					$existepedido = $this->db->query("select *from kardex.pedidosdetalle where codpedido=".$value["codpedido"]." and codproducto=".$value["codproducto"]." and item=".$value["itempedido"])->result_array();

					$existeventa = $this->db->query("select *from kardex.kardexdetalle where codkardex=".$value["codkardex"]." and codproducto=".$value["codproducto"]." and item=".$value["itemkardex"])->result_array();

					$stock = $existepedido[0]["cantidadcomprobante"] - $existeventa[0]["cantidad"];

					$campos = ["cantidadcomprobante"]; $valores = [(double)$stock];
					$f = ["codpedido","codproducto","item"];
					$v = [(int)$value["codpedido"],(int)$value["codproducto"],(int)$value["itempedido"]];
					$estado = $this->phuyu_model->phuyu_editar_1("kardex.pedidosdetalle", $campos, $valores, $f, $v);
					$codpedido = $value["codpedido"];
				}
				$estado = $this->phuyu_model->phuyu_eliminar_total("kardex.kardexpedido", "codkardex",$this->request->codregistro);

				$estadoproceso = $this->db->query("select *FROM kardex.pedidosdetalle pd where pd.codpedido=".$codpedido." and pd.cantidad > pd.cantidadcomprobante")->result_array();

				if(count($estadoproceso) > 0){
					$data = array("estadoproceso" => 0);
					$this->db->where("codpedido", $codpedido);
		            $estado = $this->db->update("kardex.pedidos", $data);
				}
			}

			// ANULAMOS EL MOVIMIENTO DE CAJA //
			$movi = $this->db->query("select codmovimiento from caja.movimientos where codkardex=".$this->request->codregistro)->result_array();
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
		}
	}

	function restaurar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$this->db->trans_begin();

			// ACTUALIZAMOS PRODUCTOS UBICACION //
			$kardexalmacen = $this->db->query("select codkardexalmacen from kardex.kardexalmacen where codkardex=".$this->request->codregistro)->result_array();

			$info = $this->db->query("select *from kardex.kardexdetalle where codkardex=".$this->request->codregistro)->result_array();
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
			$estado = $this->phuyu_model->phuyu_restaurar("kardex.kardex", "codkardex", $this->request->codregistro);
			$estado = $this->phuyu_model->phuyu_restaurar("kardex.kardexalmacen", "codkardexalmacen", $kardexalmacen[0]["codkardexalmacen"]);

			$estado = $this->phuyu_model->phuyu_eliminar_total("kardex.kardexanulados","codkardex",$this->request->codregistro);
			$estado = $this->phuyu_model->phuyu_eliminar_total("kardex.kardexalmacenanulado","codkardexalmacen",$kardexalmacen[0]["codkardexalmacen"]);

			//VERIFICAMOS Y ELIMINAMOS EN LA TABLA KARDEXPEDIDO

			/*$info = $this->db->query("select *from kardex.kardexpedido where codkardex=".$this->request->codregistro)->result_array();

			if(count($info) > 0){
				$codpedido = 0;
				foreach ($info as $key => $value) {

					$existepedido = $this->db->query("select *from kardex.pedidosdetalle where codpedido=".$value["codpedido"]." and codproducto=".$value["codproducto"]." and item=".$value["itempedido"])->result_array();

					$existeventa = $this->db->query("select *from kardex.kardexdetalle where codkardex=".$value["codkardex"]." and codproducto=".$value["codproducto"]." and item=".$value["itemkardex"])->result_array();

					$stock = $existepedido[0]["cantidadcomprobante"] + $existeventa[0]["cantidad"];

					$campos = ["cantidadcomprobante"]; $valores = [(double)$stock];
					$f = ["codpedido","codproducto","item"];
					$v = [(int)$value["codpedido"],(int)$value["codproducto"],(int)$value["itempedido"]];
					$estado = $this->phuyu_model->phuyu_editar_1("kardex.pedidosdetalle", $campos, $valores, $f, $v);
					$codpedido = $value["codpedido"];
				}
				$estado = $this->phuyu_model->phuyu_eliminar_total("kardex.kardexpedido", "codkardex",$this->request->codregistro);

				$estadoproceso = $this->db->query("select *FROM kardex.pedidosdetalle pd where pd.codpedido=".$codpedido." and pd.cantidad > pd.cantidadcomprobante")->result_array();

				if(count($estadoproceso) > 0){
					$data = array("estadoproceso" => 0);
					$this->db->where("codpedido", $codpedido);
		            $estado = $this->db->update("kardex.pedidos", $data);
				}
			}*/

			// ANULAMOS EL MOVIMIENTO DE CAJA //
			$movi = $this->db->query("select codmovimiento from caja.movimientos where codkardex=".$this->request->codregistro)->result_array();
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

	function clonar(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$this->request = json_decode(file_get_contents('php://input'));
				$info = $this->db->query("select kardex.codkardex,kardex.fechacomprobante,kardex.fechakardex,kardex.codmoneda, kardex.tipocambio,kardex.codcomprobantetipo,kardex.retirar,kardex.afectacaja,kardex.seriecomprobante, kardex.nrocomprobante,kardex.nroplaca,kardex.cliente,kardex.direccion,kardex.descripcion,personas.codpersona, personas.razonsocial,comprobantes.descripcion as tipo,kardex.flete,kardex.gastos,kardex.valorventa,kardex.descglobal,kardex.igv,kardex.importe,kardex.condicionpago from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codkardex=".$this->request->codregistro)->result_array();

				$info[0]["nrodias"] = 30;
				$info[0]["nrocuotas"] = 1;
				$info[0]["tasainteres"] = 0;

				if($info[0]["condicionpago"]==2){
					$credito = $this->db->query("select *from kardex.creditos where codkardex=".$info[0]["codkardex"]." AND estado = 1")->result_array();

					if(count($credito)>0){
						$info[0]["nrodias"] = $credito[0]["nrodias"];
						$info[0]["nrocuotas"] = $credito[0]["nrocuotas"];
						$info[0]["tasainteres"] = $credito[0]["tasainteres"];
					}
				}

				$socio = $this->db->query("Select *from personas WHERE codpersona=".$info[0]["codpersona"])->result_array();
				$detalle = $this->db->query("select kd.*,p.descripcion as producto,u.descripcion as unidad from kardex.kardexdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codkardex=".$this->request->codregistro." and kd.estado=1 order by kd.item")->result_array();

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
			$f = ["codsucursal","codcomprobantetipo"]; $v = [$_SESSION["phuyu_codsucursal"],10];
			$estado = $this->phuyu_model->phuyu_editar_1("caja.comprobantes", $campos, $valores, $f, $v);

			echo $formato;
		}
	}
}