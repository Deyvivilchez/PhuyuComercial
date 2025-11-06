<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Pedidos extends CI_Controller {

	public function __construct(){
		parent::__construct(); 
		$this->load->model("phuyu_model"); $this->load->model("Caja_model"); $this->load->model("Kardex_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_codusuario"])) {
				/* CODIGO TEMPORAL DE LA IMPRESION */

				$formato = $this->db->query("select formato from caja.comprobantes where codcomprobantetipo=8 AND codsucursal = ".$_SESSION["phuyu_codsucursal"]." AND estado=1")->result_array();
				if (count($formato)==0) {
					$_SESSION["phuyu_formatopedido"] = "a4";
					$almacen = 0;
				}else{
					$_SESSION["phuyu_formatopedido"] = $formato[0]["formato"];
					$almacen = 1;
				}
				$this->load->view("ventas/pedidos/index",compact("almacen"));
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
			$limit = 10; $offset = $this->request->pagina * $limit - $limit; $vendedor="";

			if ($this->request->fechas->filtro == 0) {
				$fechas = "";
			}else{
				if (!empty($this->request->fechas->desde)) {
					$fechas = "fechapedido>='".$this->request->fechas->desde."' and fechapedido<='".$this->request->fechas->hasta."' and";
				}else{
					$fechas = "fechapedido<='".$this->request->fechas->hasta."' and";
				}
			}

			if($_SESSION["phuyu_perfil"]>3){
				$vendedor = ' AND pedidos.codempleado='.$_SESSION["phuyu_codempleado"];
			}

			$lista = $this->db->query("select pedidos.hora,personas.documento,pedidos.cliente,pedidos.codpedido, pedidos.codcomprobantetipo, pedidos.seriecomprobante,pedidos.condicionpago, pedidos.nrocomprobante, pedidos.fechapedido,round(pedidos.importe,2) as importe,pedidos.estado, comprobantes.descripcion as tipo,pedidos.codkardex,pedidos.estadoproceso,pedidos.igv from kardex.pedidos inner join public.personas as personas on (pedidos.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(pedidos.codcomprobantetipo=comprobantes.codcomprobantetipo) where ".$fechas." (UPPER(personas.documento) like UPPER('%".$this->request->buscar."%') or UPPER(personas.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(pedidos.cliente) like UPPER('%".$this->request->buscar."%') or UPPER(comprobantes.descripcion) like UPPER('%".$this->request->buscar."%') or UPPER(pedidos.seriecomprobante) like UPPER('%".$this->request->buscar."%') or UPPER(pedidos.nrocomprobante) like UPPER('%".$this->request->buscar."%') ) and pedidos.codsucursal=".$_SESSION["phuyu_codsucursal"]." AND tipopedido = 2 ".$vendedor." order by pedidos.fechapedido desc,pedidos.hora desc offset ".$offset." limit ".$limit)->result_array();

			foreach ($lista as $key => $value) {

				$hora = explode(".", $lista[$key]["hora"]);
				$lista[$key]["hora"] = $hora[0];
			}



			$total = $this->db->query("select count(*) as total from kardex.pedidos inner join public.personas as personas on (pedidos.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(pedidos.codcomprobantetipo=comprobantes.codcomprobantetipo) where ".$fechas." (UPPER(personas.documento) like UPPER('%".$this->request->buscar."%') or UPPER(personas.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(pedidos.cliente) like UPPER('%".$this->request->buscar."%') or UPPER(comprobantes.descripcion) like UPPER('%".$this->request->buscar."%') or UPPER(pedidos.seriecomprobante) like UPPER('%".$this->request->buscar."%') or UPPER(pedidos.nrocomprobante) like UPPER('%".$this->request->buscar."%') ) and pedidos.codsucursal=".$_SESSION["phuyu_codsucursal"]." AND tipopedido = 2")->result_array();

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

			$lista = $this->db->query("SELECT k.codpedido,k.codsucursal,k.codalmacen,sum(kd.cantidad) As cantidad, sum(kd.cantidadcomprobante) As cantidadcomprobante,k.cliente,k.direccion,k.fechapedido,k.hora,k.seriecomprobante,k.nrocomprobante,p.documento,round(k.valorventa,2) as valorventa,round(k.importe,2) as importe,k.codempleado,k.codcomprobantetiporeferencia,k.codpersona,k.condicionpago,k.igv FROM kardex.pedidos k JOIN kardex.pedidosdetalle kd ON k.codpedido = kd.codpedido JOIN public.personas as p on (k.codpersona=p.codpersona) WHERE (UPPER(p.documento) ilike UPPER('%".$this->request->buscar."%') or UPPER(p.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(k.cliente) like UPPER('%".$this->request->buscar."%') or UPPER(k.seriecomprobante) like UPPER('%".$this->request->buscar."%') or UPPER(k.nrocomprobante) like UPPER('%".$this->request->buscar."%') ) and k.codsucursal=".$_SESSION["phuyu_codsucursal"]." and k.estado=1 and tipopedido = 2 AND estadoproceso = 0 GROUP BY k.codpedido, k.codsucursal,k.codpersona, k.valorventa, k.codalmacen,k.cliente,k.fechapedido,k.hora,k.seriecomprobante,k.nrocomprobante,p.documento,k.codcomprobantetiporeferencia HAVING sum(kd.cantidadcomprobante) < sum(kd.cantidad) order by k.codpedido desc offset ".$offset." limit ".$limit)->result_array();

			foreach ($lista as $key => $value) {

				$lista[$key]["nrodias"] = 30;
				$lista[$key]["nrocuotas"] = 1;
				$lista[$key]["tasainteres"] = 0;
				$cuotas = [];
				if($value["condicionpago"]==2){
					$credito = $this->db->query("select *from kardex.creditospedidos where codpedido=".$value["codpedido"]." AND estado = 1")->result_array();

					if(count($credito)>0){
						$lista[$key]["nrodias"] = $credito[0]["nrodias"];
						$lista[$key]["nrocuotas"] = $credito[0]["nrocuotas"];
						$lista[$key]["tasainteres"] = $credito[0]["tasainteres"];
						$cuotas = $this->db->query("select *from kardex.cuotaspedidos where codcreditopedido = ".$credito[0]["codcreditopedido"])->result_array();
						$lista[$key]["cuotas"] = $cuotas;
						foreach ($cuotas as $k => $val) {
							$lista[$key]["cuotas"][$k]["nrocuota"] = $val["nrocuotapedido"];
						}
						 
					}
				}
			}

			$total = $this->db->query("select count(*) as total FROM kardex.pedidos k JOIN kardex.pedidosdetalle kd ON k.codpedido = kd.codpedido JOIN public.personas as p on (k.codpersona=p.codpersona) WHERE (UPPER(p.documento) ilike UPPER('%".$this->request->buscar."%') or UPPER(p.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(k.cliente) like UPPER('%".$this->request->buscar."%') or UPPER(k.seriecomprobante) like UPPER('%".$this->request->buscar."%') or UPPER(k.nrocomprobante) like UPPER('%".$this->request->buscar."%') ) and k.codsucursal=".$_SESSION["phuyu_codsucursal"]." AND estadoproceso=0 and k.estado=1 and tipopedido = 2 HAVING sum(kd.cantidadcomprobante) < sum(kd.cantidad)")->result_array();

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
				$this->load->view("ventas/pedidos/buscar");
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function buscarproductos($codpedido){
		if ($this->input->is_ajax_request()) {
			$productos = $this->db->query("select pd.*,p.descripcion as producto,p.calcular,u.descripcion as unidad,p.controlstock as control FROM kardex.pedidosdetalle pd JOIN almacen.productos p ON pd.codproducto = p.codproducto JOIN almacen.unidades u ON pd.codunidad = u.codunidad where codpedido=".$codpedido." and pd.cantidad > pd.cantidadcomprobante ORDER BY pd.item")->result_array();

			foreach ($productos as $key => $value) {
				$unidades = $this->db->query("select *FROM almacen.v_productounidades pun where pun.codproducto=".$value["codproducto"]." AND codalmacen = ".$_SESSION["phuyu_codalmacen"])->result_array();

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
				$comprobantes = $this->db->query("select distinct(ct.codcomprobantetipo) as codigo, ct.*, c.seriecomprobante from caja.comprobantetipos as ct inner join caja.comprobantes as c on(ct.codcomprobantetipo=c.codcomprobantetipo) where c.codsucursal=".$_SESSION["phuyu_codsucursal"]." and c.codcomprobantetipo=8 and c.estado=1")->result_array();
				$comprobantesreferencia = $this->db->query("select distinct(ct.codcomprobantetipo) as codigo, ct.* from caja.comprobantetipos as ct inner join caja.comprobantes as c on(ct.codcomprobantetipo=c.codcomprobantetipo) where c.codsucursal=".$_SESSION["phuyu_codsucursal"]." and c.codcomprobantetipo IN (5,10,12) and c.estado=1")->result_array();
				$tipopagos = $this->db->query("select *from caja.tipopagos where ingreso=1 and estado=1 order by codtipopago")->result_array();
				$vendedores = $this->db->query("select persona.codpersona,persona.razonsocial from public.personas as persona inner join public.empleados as empleado on(persona.codpersona=empleado.codpersona) where empleado.estado=1 ".$perfil."")->result_array();
				$sucursal = $this->db->query("select coalesce(codcomprobantetipo,8) as codcomprobantetipo, seriecomprobante from public.sucursales where codsucursal=".$_SESSION["phuyu_codsucursal"])->result_array();
				$sucursalreferencia = $this->db->query("select coalesce(codcomprobantetipo,12) as codcomprobantetipo, seriecomprobante from public.sucursales where codsucursal=".$_SESSION["phuyu_codsucursal"])->result_array();
				$centrocostos = $this->db->query("select *from caja.centrocostos where estado=1")->result_array();
				$afectacionigv = $this->db->query("select *from afectacionigv where estado = 1")->result_array();
				$this->load->view("ventas/pedidos/nuevo",compact("comprobantes","comprobantesreferencia","tipopagos","vendedores","sucursal","sucursalreferencia","centrocostos","afectacionigv"));
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
				$this->load->view("ventas/pedidos/historial",compact("persona"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function filtro_pedidos(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			if ($this->request->filtro==1) {
				$filtro = " and fechapedido>='".$this->request->fechadesde."' and fechapedido<='".$this->request->fechahasta."' ";
			}else{
				$filtro = "";
			}

			if ($this->request->estado!="") {
				$filtro = $filtro." and estado=".$this->request->estado;
			}

			$pedidos = $this->db->query("select codpedido from kardex.pedidos where codpersona=".$this->request->codpersona." and codsucursal=".$_SESSION["phuyu_codsucursal"]." and estado=1")->result_array();
			foreach ($pedidos as $key => $value) {
				$detalle = $this->db->query("select coalesce(sum(cantidad),0) as cantidad from kardex.pedidosdetalle where codpedido=".$value["codpedido"]." and estado=1")->result_array();
				$atendido = $this->db->query("select coalesce(sum(cantidad),0) as cantidad from restaurante.atendidos where codpedido=".$value["codpedido"])->result_array();
				if ($detalle[0]["cantidad"] == $atendido[0]["cantidad"]) {
					$data = array('estado' => 2);
					$this->db->where("codpedido", $value["codpedido"]);
					$estado = $this->db->update("kardex.pedidos", $data);
				}
			}

			$pedidos = $this->db->query("select codpedido,fechapedido,cliente,direccion, importe, estado from kardex.pedidos where codpersona=".$this->request->codpersona." and codsucursal=".$_SESSION["phuyu_codsucursal"]." ".$filtro." order by codpedido")->result_array();
			$total = 0;
			foreach ($pedidos as $key => $value) {
				$total = $total + (double)$value["importe"];
			}
			$totales = $this->db->query("select ".number_format($total,2,".","")." as total")->result_array();

			$data["pedidos"] = $pedidos;
			$data["totales"] = $totales;
			echo json_encode($data);
		}else{
			$this->load->view("phuyu/404");
		}
	}
	
	function atender($codpedido){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$info = $this->db->query("select pedido.* from kardex.pedidos as pedido where pedido.codpedido=".$codpedido)->result_array();
				$this->load->view("ventas/pedidos/atender",compact("info"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function ver($codpedido){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_codusuario"])){
				$info = $this->db->query("select pedido.*,p.*,comprobantes.descripcion as tipo,(CASE WHEN condicionpago = 1 THEN 'CONTADO' ELSE 'CREDITO' END) AS pago,(CASE WHEN estadoproceso = 0 THEN 'PENDIENTE' ELSE 'ATENDIDO' END) AS proceso,empleado.razonsocial as vendedor from kardex.pedidos as pedido inner join caja.comprobantetipos as comprobantes on(pedido.codcomprobantetipo=comprobantes.codcomprobantetipo) INNER JOIN public.personas as p on (pedido.codpersona=p.codpersona) inner join public.personas as empleado on(pedido.codempleado=empleado.codpersona) where pedido.codpedido=".$codpedido)->result_array();
				$detalle = $this->db->query("select pd.*,p.descripcion AS producto,p.codigo,u.descripcion as unidad FROM kardex.pedidosdetalle pd JOIN almacen.productos p ON pd.codproducto = p.codproducto JOIN almacen.unidades u ON pd.codunidad = u.codunidad where codpedido=".$codpedido)->result_array();
				$cantidad = 0; $atendido = 0;
				foreach ($detalle as $key => $value) {
					$detalle[$key]["atendido"] = round($value["cantidadcomprobante"],2);
					$detalle[$key]["falta"] = round($value["cantidad"] - $value["cantidadcomprobante"]);
					$cantidad = $cantidad + $value["cantidad"]; $atendido = $atendido + $value["cantidadcomprobante"];
				}
				$totales = $this->db->query("select ".round($cantidad,2)." as cantidad, ".round($atendido,2)." as atendido")->result_array();
				
				$this->load->view("ventas/pedidos/ver",compact("info","detalle","totales")); 
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
//echo 1;exit;
				$this->request = json_decode(file_get_contents('php://input'));
                $campos = ["codsucursal","codalmacen","codusuario","codpersona","fechapedido","hora","valorventa","porcigv","igv","importe","cliente","direccion","codempleado", "afectastock","afectacaja","descripcion","codcomprobantetipo","seriecomprobante","condicionpago","nrocomprobante","codcomprobantetiporeferencia","tipopedido"];
				$valores = [
					(int)$_SESSION["phuyu_codsucursal"],
					(int)$_SESSION["phuyu_codalmacen"],
					(int)$_SESSION["phuyu_codusuario"],
					(int)$this->request->campos->codpersona,
					$this->request->campos->fechacomprobante,
					date('H:i:s'),
					(double)$this->request->totales->valorventa,
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
					$this->request->campos->codcomprobantetiporeferencia,
					2
				];
				$this->db->trans_begin();
                if($this->request->campos->codpedido == 0){
					
					$codpedido = $this->phuyu_model->phuyu_guardar("kardex.pedidos", $campos, $valores, "true");

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

					$estado = $this->phuyu_model->phuyu_editar("kardex.pedidos", $data, $valores, "codpedido",$codpedido);

					$item = 0;
					foreach ($this->request->detalle as $key => $value) { 
						$item = $item + 1;

						$campos = ["codpedido","codproducto","codunidad","item","cantidad","preciounitario","valorventa","preciorefunitario","codafectacionigv","subtotal","descripcion","igv","preciosinigv","preciobruto"];
						$valores =[
							(int)$codpedido,
							(int)$this->request->detalle[$key]->codproducto,
							(int)$this->request->detalle[$key]->codunidad, $item,
							(double)$this->request->detalle[$key]->cantidad,
							(double)$this->request->detalle[$key]->precio,
							(double)$this->request->detalle[$key]->valorventa,
							(double)$this->request->detalle[$key]->preciorefunitario,
							$this->request->detalle[$key]->codafectacionigv,
							(double)$this->request->detalle[$key]->subtotal,
							$this->request->detalle[$key]->descripcion,
							(double)$this->request->detalle[$key]->igv,
							(double)$this->request->detalle[$key]->preciosinigv,
							(double)$this->request->detalle[$key]->preciobruto
						];
						$estado = $this->phuyu_model->phuyu_guardar("kardex.pedidosdetalle", $campos, $valores);
					}

					/* REGISTRO CREDITO POR COBRAR */

					if ($this->request->campos->condicionpago==2) {
						$estado = $this->Caja_model->phuyu_creditopedido($codpedido, 0, 1, $this->request->campos, $this->request->totales, $this->request->cuotas);
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
                    $codpedido = $this->request->campos->codpedido;
				    $estado = $this->phuyu_model->phuyu_editar("kardex.pedidos", $campos, $valores, "codpedido", $codpedido);
				    $estado = $this->phuyu_model->phuyu_eliminar_total("kardex.pedidosdetalle","codpedido",$codpedido);
				    if (count($this->request->detalle) > 0) {
				    	$item = 0;
                        foreach ($this->request->detalle as $key => $value) { 
							$item = $item + 1;

							$campos = ["codpedido","codproducto","codunidad","item","cantidad","preciounitario","valorventa","preciorefunitario","codafectacionigv","subtotal","descripcion","igv","preciosinigv","preciobruto"];
							$valores =[
								(int)$codpedido,
								(int)$this->request->detalle[$key]->codproducto,
								(int)$this->request->detalle[$key]->codunidad, $item,
								(double)$this->request->detalle[$key]->cantidad,
								(double)$this->request->detalle[$key]->precio,
								(double)$this->request->detalle[$key]->valorventa,
								(double)$this->request->detalle[$key]->preciorefunitario,
								$this->request->detalle[$key]->codafectacionigv,
								(double)$this->request->detalle[$key]->subtotal,
								$this->request->detalle[$key]->descripcion,
								(double)$this->request->detalle[$key]->igv,
								(double)$this->request->detalle[$key]->preciosinigv,
								(double)$this->request->detalle[$key]->preciobruto
							];
							$estado = $this->phuyu_model->phuyu_guardar("kardex.pedidosdetalle", $campos, $valores);
						}
				    }

				    if((int)$this->request->campos->condicionpago==2 && (int)$this->request->campos->codcreditopedido > 0){

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

				    	$estado = $this->phuyu_model->phuyu_editar("kardex.creditospedidos", $campos, $valores, "codcreditopedido", (int)$this->request->campos->codcreditopedido);

				    	$estado = $this->phuyu_model->phuyu_eliminar_total("kardex.cuotaspedidos","codcreditopedido",(int)$this->request->campos->codcreditopedido);

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
								"codcreditopedido" => (int)$this->request->campos->codcreditopedido,
								"nrocuotapedido" => (int)$this->request->cuotas[$key]->nrocuota,
								"codsucursal" => (int)$_SESSION["phuyu_codsucursal"],
								"fechavence" => $this->request->cuotas[$key]->fechavence,
								"importe" => (double)$importe,
								"saldo" => (double)$total,
								"interes" => (double)$interes,
								"total" => (double)$total
							);
							$estado = $this->db->insert("kardex.cuotaspedidos", $data);
							$fechavence = $this->request->cuotas[$key]->fechavence;
						}
						$data = array(
							"fechavencimiento" => $fechavence
						);
						$this->db->where("codcreditopedido", (int)$this->request->campos->codcreditopedido);
						$estado = $this->db->update("kardex.creditospedidos", $data);
				    }

				    if((int)$this->request->campos->condicionpago==2 && (int)$this->request->campos->codcreditopedido == 0){
				    	$estado = $this->Caja_model->phuyu_creditopedido($codpedido, 0, 1, $this->request->campos, $this->request->totales, $this->request->cuotas);
				    }

				    if((int)$this->request->campos->condicionpago==1 && (int)$this->request->campos->codcreditopedido > 0){
				    	$estado = $this->phuyu_model->phuyu_eliminar("kardex.creditospedidos", "codcreditopedido", (int)$this->request->campos->codcreditopedido);
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
				$data["estado"] = $estado; $data["codpedido"] = $codpedido;
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

			$total = $this->db->query("select count(*) as total FROM kardex.pedidos k JOIN kardex.pedidosdetalle kd ON k.codpedido = kd.codpedido JOIN public.personas as p on (k.codpersona=p.codpersona) WHERE k.codsucursal=".$_SESSION["phuyu_codsucursal"]." and k.estado=1 and tipopedido = 2 AND estadoproceso = 0 AND k.codpedido =".$this->request->codregistro." HAVING sum(kd.cantidadcomprobante)=0")->result_array();

			if(count($total) > 0){
				$perfil = '';
				if($_SESSION["phuyu_codperfil"] > 3){
					$perfil .= ' AND empleado.codpersona = '.$_SESSION["phuyu_codempleado"];
				}
				$info=$this->db->query("select p.*,pr.razonsocial from kardex.pedidos p inner join public.personas as pr ON p.codpersona=pr.codpersona where codpedido=".$this->request->codregistro)->result_array();
				$detalle = $this->db->query("select pd.*,p.descripcion AS producto,u.descripcion as unidad FROM kardex.pedidosdetalle pd JOIN almacen.productos p ON pd.codproducto = p.codproducto JOIN almacen.unidades u ON pd.codunidad = u.codunidad where codpedido=".$this->request->codregistro)->result_array();
				$comprobantesreferencia = $this->db->query("select distinct(ct.codcomprobantetipo) as codigo, ct.* from caja.comprobantetipos as ct inner join caja.comprobantes as c on(ct.codcomprobantetipo=c.codcomprobantetipo) where c.codsucursal=".$_SESSION["phuyu_codsucursal"]." and c.codcomprobantetipo>=5 and c.estado=1")->result_array();
				$vendedores = $this->db->query("select persona.codpersona,persona.razonsocial from public.personas as persona inner join public.empleados as empleado on(persona.codpersona=empleado.codpersona) where empleado.estado=1 ".$perfil."")->result_array();
				$tipopagos = $this->db->query("select *from caja.tipopagos where ingreso=1 and estado=1 order by codtipopago")->result_array();
				$afectacionigv = $this->db->query("select *from public.afectacionigv where estado = 1")->result_array();
	            $this->load->view("ventas/pedidos/editar",compact("info","detalle","comprobantesreferencia","vendedores","tipopagos","afectacionigv"));
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

			$pedido=$this->db->query("select *from kardex.kardexpedido where codpedido=".$this->request->codregistro)->result_array();
			if (count($pedido)==0) {
				$estado = $this->phuyu_model->phuyu_eliminar("kardex.pedidos", "codpedido", $this->request->codregistro);
				if ($estado == 1) {
					$mensaje = "PEDIDO ANULADO CORRECTAMENTE";
				}else{
					$mensaje = "OCURRIO UN ERROR AL ANULAR EL PEDIDO";
				}
			}else{
				$estado = 2;
				$mensaje = "EL PEDIDO FUE REGISTRADO EN UNA VENTA, SI DESEA ANULARLO, PRIMERO DEBE ANULAR LA VENTA";
			}
			
			$data["estado"] = $estado; $data["mensaje"] = $mensaje;
			echo json_encode($data);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function obtenercredito($codpedido){
		if ($this->input->is_ajax_request()) {
			$cuotas = $this->db->query("select *from kardex.creditospedidos where codpedido=".$codpedido." AND estado = 1")->result_array();

			echo json_encode($cuotas);
		}
	}

	// phuyu PERU - RESTOBAR //

	function phuyu_pedido(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			$pedido = $this->db->query("select max(codpedido) as codpedido from restaurante.mesaspedido where codmesa=".$this->request->codmesa." and estado=1")->result_array();
			if ($pedido[0]["codpedido"]!="") {
				$estado = 0;
				$info = $this->db->query("select valorventa,descglobal,igv,importe, codempleado, codcomprobantetipo from kardex.pedidos where codpedido=".$pedido[0]["codpedido"])->result_array();

				$detalle = $this->db->query("select kd.codproducto,p.descripcion as producto,kd.codunidad,u.descripcion as unidad,kd.item,round(kd.cantidad) as cantidad, (select stockactual from almacen.productoubicacion where kd.codproducto=codproducto and kd.codunidad=codunidad and codalmacen=".$_SESSION["phuyu_codalmacen"].") as stock,p.controlstock as control,
					kd.preciounitario as preciobruto, 0 as descuento, 0 as porcdescuento, kd.preciounitario as preciosinigv, 20 as codafectacionigv, 0 as conicbper, 0 as icbper, 0 as igv, kd.valorventa,
					round(kd.preciounitario,3) as precio,kd.preciorefunitario, p.calcular, round(kd.subtotal,3) as subtotal, kd.descripcion, 
					(select round(coalesce(sum(cantidad),0)) from restaurante.atendidos where codpedido=".$pedido[0]["codpedido"]." and kd.codproducto=codproducto and kd.codunidad=codunidad and kd.item=item) as atendido 
					from kardex.pedidosdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codpedido=".$pedido[0]["codpedido"]." and kd.estado=1 order by kd.item")->result_array();
			}else{
				$pedido = $this->db->query("select (coalesce(max(codpedido),0) + 1) as codpedido from kardex.pedidos")->result_array();
				$estado = 1; $info = []; $detalle = [];
			}
			$data["pedidonuevo"] = $estado;
			$data["codpedido"] = $pedido[0]["codpedido"];
			$data["pedido"] = $info;
			$data["detalle"] = $detalle;
			echo json_encode($data);
		}
	}

	function phuyu_atenciones(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			$detalle = $this->db->query("select kd.codpedido, kd.codproducto,p.descripcion as producto,kd.codunidad,u.descripcion as unidad, kd.item, round(kd.cantidad) as cantidad, (select round(coalesce(sum(cantidad),0)) from restaurante.atendidos where codpedido=".$this->request->codpedido." and kd.codproducto=codproducto and kd.codunidad=codunidad and kd.item=item) as atendido, kd.descripcion from kardex.pedidosdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codpedido=".$this->request->codpedido." and kd.estado=1 order by kd.item")->result_array();
			$cantidad = 0; $atendido = 0;
			foreach ($detalle as $key => $value) {
				$detalle[$key]["atender"] = round($value["cantidad"] - $value["atendido"]);
				$detalle[$key]["falta"] = round($value["cantidad"] - $value["atendido"]);
				$cantidad = $cantidad + $value["cantidad"]; $atendido = $atendido + $value["atendido"];
			}
			$totales = $this->db->query("select ".round($cantidad,2)." as cantidad, ".round($atendido,2)." as atendido")->result_array();
			$atendidos = $this->db->query("select kd.codproducto,p.descripcion as producto,kd.codunidad,u.descripcion as unidad, round(kd.cantidad) as cantidad,kd.fecha,to_char(kd.hora,'HH12:MI:SS') as hora from restaurante.atendidos as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codpedido=".$this->request->codpedido." and kd.estado=1 order by kd.item")->result_array();
			
			$data["detalle"] = $detalle;
			$data["totales"] = $totales;
			$data["atendidos"] = $atendidos;
			echo json_encode($data);
		}
	}

	function guardar_pedido_original(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$this->request = json_decode(file_get_contents('php://input'));

				$this->db->trans_begin();

				$campos = ["codsucursal","codalmacen","codusuario","codpersona","fechapedido","valorventa","porcdescuento","descglobal","descuentos","porcigv",
				"igv","importe","cliente","direccion","codcomprobantetipo","codempleado","tipopedido","codcontroldiario"];
				$valores = [
					(int)$_SESSION["phuyu_codsucursal"],
					(int)$_SESSION["phuyu_codalmacen"],
					(int)$_SESSION["phuyu_codusuario"],
					(int)$this->request->campos->codpersona,
					$this->request->campos->fechapedido,
					(double)$this->request->totales->valorventa,
					(double)$this->request->campos->porcdescuento,
					(double)$this->request->totales->descglobal,
					(double)$this->request->totales->descuentos,
					(double)$_SESSION["phuyu_igv"],
					(double)$this->request->totales->igv,
					(double)$this->request->totales->importe,
					$this->request->campos->cliente,
					$this->request->campos->direccion,
					(int)$this->request->campos->codcomprobante,
					(int)$this->request->campos->codempleado,
					(int)$this->request->campos->tipopedido,
					(int)$_SESSION["phuyu_codcontroldiario"]
				];

				if((int)$this->request->campos->pedidonuevo==1){
					$codpedido = $this->phuyu_model->phuyu_guardar("kardex.pedidos", $campos, $valores, "true");
				}else{
					$codpedido = $this->request->campos->codpedido;
					$estado = $this->phuyu_model->phuyu_editar("kardex.pedidos", $campos, $valores, "codpedido", $codpedido);

					$campos = ["estado"];
					$valores = [0];
					$estado = $this->phuyu_model->phuyu_editar("restaurante.atendidos", $campos, $valores, "codpedido", $codpedido);
					$estado = $this->phuyu_model->phuyu_editar("kardex.pedidosdetalle", $campos, $valores, "codpedido", $codpedido);
				}

				$items = $this->db->query("select coalesce(max(item),0) as item from kardex.pedidosdetalle where codpedido=".$codpedido)->result_array();
				$item = $items[0]["item"]; $suma_total = 0;
				foreach ($this->request->detalle as $key => $value) {
					if ($this->request->detalle[$key]->item==0) {
						$item = $item + 1; $this->request->detalle[$key]->item = $item;
					}
					$codafectacionigv = "20";
					if ((double)$this->request->detalle[$key]->precio==0) {
						$codafectacionigv = "21";
					}

					$suma_total = $suma_total + $this->request->detalle[$key]->subtotal;
					$campos = ["codpedido","codproducto","codunidad","item","cantidad","preciounitario","valorventa","preciorefunitario","codafectacionigv","subtotal","descripcion","estado"];
					// $valores =[
					// 	(int)$codpedido,
					// 	(int)$this->request->detalle[$key]->codproducto,
					// 	(int)$this->request->detalle[$key]->codunidad,
					// 	(int)$this->request->detalle[$key]->item,
					// 	(double)$this->request->detalle[$key]->cantidad,
					// 	(double)$this->request->detalle[$key]->precio,
					// 	(double)$this->request->detalle[$key]->subtotal,
					// 	(double)$this->request->detalle[$key]->preciorefunitario,
					// 	$codafectacionigv,
					// 	(double)$this->request->detalle[$key]->subtotal,
					// 	$this->request->detalle[$key]->descripcion,1
					// ];


						$valores =[
						(int)$codpedido,
						(int)$this->request->detalle[$key]->codproducto,
						(int)$this->request->detalle[$key]->codunidad,
						(int)$this->request->detalle[$key]->item,
						(double)$this->request->detalle[$key]->cantidad,
						(double)$this->request->detalle[$key]->precio,
						(double)($this->request->detalle[$key]->precio * $this->request->detalle[$key]->cantidad),
						(double)$this->request->detalle[$key]->preciorefunitario,
						$codafectacionigv,
						(double)($this->request->detalle[$key]->precio * $this->request->detalle[$key]->cantidad),
						$this->request->detalle[$key]->descripcion,1
					];

					
					$existe = $this->db->query("select *from kardex.pedidosdetalle where codpedido=".$codpedido." and codproducto=".$this->request->detalle[$key]->codproducto." and codunidad=".$this->request->detalle[$key]->codunidad." and item=".$this->request->detalle[$key]->item)->result_array();
					if (count($existe)==0) {
						$estado = $this->phuyu_model->phuyu_guardar("kardex.pedidosdetalle", $campos, $valores);
					}else{
						$f = ["codpedido","codproducto","codunidad","item"]; 
						$v = [(int)$codpedido,(int)$this->request->detalle[$key]->codproducto,(int)$this->request->detalle[$key]->codunidad,(int)$this->request->detalle[$key]->item];
						$estado = $this->phuyu_model->phuyu_editar_1("kardex.pedidosdetalle", $campos, $valores, $f, $v);

						$campos = ["estado"]; $valores = [1];
						$f = ["codpedido","codproducto","codunidad","item"]; 
						$v = [(int)$codpedido,(int)$this->request->detalle[$key]->codproducto,(int)$this->request->detalle[$key]->codunidad,(int)$this->request->detalle[$key]->item];
						$estado = $this->phuyu_model->phuyu_editar_1("restaurante.atendidos", $campos, $valores, $f, $v);
					}
				}
				$this->db->where("codpedido", $codpedido); $this->db->where("estado",0); 
				$estado = $this->db->delete("restaurante.atendidos");
				$this->db->where("codpedido", $codpedido); $this->db->where("estado",0); 
				$estado = $this->db->delete("kardex.pedidosdetalle");

				$campos = ["valorventa","importe"]; $valores = [(double)round($suma_total,2),(double)round($suma_total,2)];
				$estado = $this->phuyu_model->phuyu_editar("kardex.pedidos", $campos, $valores, "codpedido", $codpedido);

				if((int)$this->request->campos->pedidonuevo==1){
					$campos = ["codpedido","codmesa","nromesa"];
					$valores = [(int)$codpedido,(int)$this->request->campos->codmesa,$this->request->campos->mesa];
					$estado = $this->phuyu_model->phuyu_guardar("restaurante.mesaspedido", $campos, $valores);
				}
				$campos = ["situacion"]; $valores = [2];
				$estado = $this->phuyu_model->phuyu_editar("restaurante.mesas", $campos, $valores, "codmesa", $this->request->campos->codmesa);

				if ($this->db->trans_status() === FALSE){
				    $this->db->trans_rollback(); $estado = 0;
				}else{
					if ($estado!=1) {
						$this->db->trans_rollback(); $estado = 0;
					}
					$this->db->trans_commit();
				}
				$data["estado"] = $estado; $data["codpedido"] = $codpedido;
				echo json_encode($data);
			}else{
				echo json_encode("e");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function guardar_pedido(){
		if ($this->input->is_ajax_request()) {
			if (!isset($_SESSION["phuyu_codusuario"])) {echo json_encode("e");return;}

			$this->request = json_decode(file_get_contents('php://input'));
			$this->db->trans_begin();

			// --- suma total de detalle (seguro para objetos o arrays) ---
			$suma_total = array_sum(array_map(function($item) {
				$subtotal = is_object($item) ? ($item->subtotal ?? 0) : ($item['subtotal'] ?? 0);
				return (double)$subtotal;
			}, $this->request->detalle ?? []));
			$suma_total = round((double)$suma_total, 2);

			// --- valores previos ---
			$campos = [
				"codsucursal","codalmacen","codusuario","codpersona","fechapedido",
				"valorventa","porcdescuento","descglobal","descuentos","porcigv",
				"igv","importe","cliente","direccion","codcomprobantetipo",
				"codempleado","tipopedido","codcontroldiario"
			];

			$valores = [
				(int)($_SESSION["phuyu_codsucursal"] ?? 0),
				(int)($_SESSION["phuyu_codalmacen"] ?? 0),
				(int)($_SESSION["phuyu_codusuario"] ?? 0),
				(int)($this->request->campos->codpersona ?? 0),	
				$this->request->campos->fechapedido ?? null,
				(double)$suma_total,                                      // <- usamos la suma calculada
				(double)($this->request->campos->porcdescuento ?? 0.0),
				(double)($this->request->totales->descglobal ?? 0.0),
				(double)($this->request->totales->descuentos ?? 0.0),
				(double)($_SESSION["phuyu_igv"] ?? 0.0),
				(double)($this->request->totales->igv ?? 0.0),
				(double)($this->request->totales->importe ?? $suma_total),
				$this->request->campos->cliente ?? '',
				$this->request->campos->direccion ?? '',
				(int)($this->request->campos->codcomprobante ?? 0),
				(int)($this->request->campos->codempleado ?? 0),
				(int)($this->request->campos->tipopedido ?? 0),
				(int)($_SESSION["phuyu_codcontroldiario"] ?? 0)
			];

			// --- insertar o actualizar pedido principal ---
			if ((int)$this->request->campos->pedidonuevo == 1) {
				$codpedido = $this->phuyu_model->phuyu_guardar("kardex.pedidos", $campos, $valores, "true");
			} else {
				$codpedido = $this->request->campos->codpedido;
				$estado = $this->phuyu_model->phuyu_editar("kardex.pedidos", $campos, $valores, "codpedido", $codpedido);

				// marcar atendidos y detalle en 0 para reescribir después
				$campos_upd = ["estado"]; $valores_upd = [0];
				$this->phuyu_model->phuyu_editar("restaurante.atendidos", $campos_upd, $valores_upd, "codpedido", $codpedido);
				$this->phuyu_model->phuyu_editar("kardex.pedidosdetalle", $campos_upd, $valores_upd, "codpedido", $codpedido);
			}

			// --- manejar items detalle ---
			$items = $this->db->query("select coalesce(max(item),0) as item 
			from kardex.pedidosdetalle 
			where codpedido = ?", [$codpedido])->result_array();
			$item = $items[0]["item"];
			$suma_total_loop = 0;

			foreach ($this->request->detalle ?? [] as $key => $value) {
				$detalleItem = is_object($value) ? $value : (object)$value;

				if ((int)$detalleItem->item === 0) {
					$item++;
					$detalleItem->item = $item;
				}

				$codafectacionigv = ((double)$detalleItem->precio == 0.0) ? "21" : "20";

				$cantidad = (double)($detalleItem->cantidad ?? 0);
				$precio = (double)($detalleItem->precio ?? 0);
				$subtotal_calc = round($precio * $cantidad, 2);
				$suma_total_loop += $subtotal_calc;

				$campos_det = [
					"codpedido","codproducto","codunidad","item","cantidad","preciounitario",
					"valorventa","preciorefunitario","codafectacionigv","subtotal","descripcion","estado"
				];

				$valores_det = [
					(int)$codpedido,
					(int)($detalleItem->codproducto ?? 0),
					(int)($detalleItem->codunidad ?? 0),
					(int)($detalleItem->item ?? 0),
					(double)$cantidad,
					(double)$precio,
					(double)$subtotal_calc,
					(double)($detalleItem->preciorefunitario ?? 0),
					$codafectacionigv,
					(double)$subtotal_calc,
					$detalleItem->descripcion ?? '',
					1
				];

				// existe?
				$existe = $this->db->query(
					"select * from kardex.pedidosdetalle where codpedido = ? and codproducto = ? and codunidad = ? and item = ?",
					[$codpedido, $valores_det[1], $valores_det[2], $valores_det[3]]
				)->result_array();

				if (count($existe) == 0) {
					$this->phuyu_model->phuyu_guardar("kardex.pedidosdetalle", $campos_det, $valores_det);
				} else {
					$f = ["codpedido","codproducto","codunidad","item"];
					$v = [(int)$codpedido, (int)$detalleItem->codproducto, (int)$detalleItem->codunidad, (int)$detalleItem->item];
					$this->phuyu_model->phuyu_editar_1("kardex.pedidosdetalle", $campos_det, $valores_det, $f, $v);

					// asegurar atendidos en estado 1
					$campos_upd = ["estado"]; $valores_upd = [1];
					$this->phuyu_model->phuyu_editar_1("restaurante.atendidos", $campos_upd, $valores_upd, $f, $v);
				}
			}

			// eliminar registros con estado = 0 (limpieza)
			$this->db->where("codpedido", $codpedido);
			$this->db->where("estado", 0);
			$this->db->delete("restaurante.atendidos");

			$this->db->where("codpedido", $codpedido);
			$this->db->where("estado", 0);
			$this->db->delete("kardex.pedidosdetalle");

			// actualizar totales en tabla pedidos usando la suma real del loop
			$campos_tot = ["valorventa","importe"];
			$valores_tot = [(double)round($suma_total_loop, 2), (double)round($suma_total_loop, 2)];
			$this->phuyu_model->phuyu_editar("kardex.pedidos", $campos_tot, $valores_tot, "codpedido", $codpedido);

			// si era pedidonuevo, insertar en mesaspedido
			if ((int)$this->request->campos->pedidonuevo == 1) {
				$campos_m = ["codpedido","codmesa","nromesa"];
				$valores_m = [(int)$codpedido, (int)($this->request->campos->codmesa ?? 0), $this->request->campos->mesa ?? ''];
				$this->phuyu_model->phuyu_guardar("restaurante.mesaspedido", $campos_m, $valores_m);
			}

			// actualizar situacion de mesa
			$campos_s = ["situacion"]; $valores_s = [2];
			$this->phuyu_model->phuyu_editar("restaurante.mesas", $campos_s, $valores_s, "codmesa", $this->request->campos->codmesa);

			// transacción
			if ($this->db->trans_status() === FALSE){
				$this->db->trans_rollback();
				$estado = 0;
			} else {
				// si alguna operación devolvió diferente a 1, considerarlo error (esto depende de tus modelos)
				$this->db->trans_commit();
				$estado = 1;
			}

			$data["estado"] = $estado;
			$data["codpedido"] = $codpedido;
			echo json_encode($data);
		} else {
			$this->load->view("phuyu/404");
		}
	}


	function guardar_atencion(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			$campos = ["codpedido","codproducto","codunidad","item","nro","cantidad"];
			foreach ($this->request->atender as $key => $value) {
				$nroatencion = $this->db->query("select coalesce(max(nro),0) as nro from restaurante.atendidos where codpedido=".$this->request->atender[$key]->codpedido." and codproducto=".$this->request->atender[$key]->codproducto." and codunidad=".$this->request->atender[$key]->codunidad." and item=".$this->request->atender[$key]->item)->result_array();
				
				$nro = $nroatencion[0]["nro"] + 1;
				if ((int)$this->request->atender[$key]->atender > 0) {
					$valores =[
						(int)$this->request->atender[$key]->codpedido,
						(int)$this->request->atender[$key]->codproducto,
						(int)$this->request->atender[$key]->codunidad, 
						(int)$this->request->atender[$key]->item, $nro,
						(double)$this->request->atender[$key]->atender
					];
					$estado = $this->phuyu_model->phuyu_guardar("restaurante.atendidos", $campos, $valores);
				}
			}
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function cobrar_pedido(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$this->request = json_decode(file_get_contents('php://input'));

				$this->db->trans_begin();

				// REGISTRO KARDEX //

				$codkardex = $this->Kardex_model->phuyu_kardex($this->request->campos, $this->request->totales, 0); 
				$codkardexalmacen = 0; $retirar = $this->request->campos->retirar; $estado = 1;
				if ($retirar == true) {
					$codkardexalmacen = $this->Kardex_model->phuyu_kardexalmacen($codkardex, 4, $this->request->campos);
				}
				$detalle = $this->Kardex_model->phuyu_kardexdetalle($codkardex, $codkardexalmacen, $this->request->detalle, $retirar, 0);
				
				// REGISTRO MOVIMIENTO DE CAJA //

				if ($this->request->campos->codmoneda!=1) {
					$importe = round($this->request->totales->importe * $this->request->campos->tipocambio,2);
					$importemoneda = $this->request->totales->importe;
				}else{
					$importe = $this->request->totales->importe;
					$importemoneda = $this->request->totales->importe;
				}

				$codmovimiento = $this->Caja_model->phuyu_movimientos($codkardex, 1, 1, $this->request->totales->importe, $this->request->campos,$importemoneda);
				if ($this->request->campos->condicionpago==1) {
					$estado = $this->Caja_model->phuyu_movimientosdetalle($codmovimiento, $this->request->pagos);
				}

				// REGISTRO CREDITO DE LA VENTA //

				if ($this->request->campos->condicionpago==2) {
					$estado = $this->Caja_model->phuyu_credito($codkardex, $codmovimiento, 1, $this->request->campos, $this->request->totales, $this->request->cuotas);
				}
				
				// COMPROBANTE ELECTRONICO PARA SUNAT: REGISTRO EN KARDEX SUNAT //

				$codkardex_return = $codkardex;
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
						$this->request->campos->fechacomprobante,$xml
					];
					$estado = $this->phuyu_model->phuyu_guardar("sunat.kardexsunat", $campos, $valores);
				}

				// ACTUALIZAMOS EL PEDIDO Y LAS MESAS //

				$campos = ["codkardex","estado"]; $valores = [$codkardex,0];
				$estado = $this->phuyu_model->phuyu_editar("kardex.pedidos", $campos, $valores, "codpedido",$this->request->campos->codpedido);
				
				$campos = ["estado"]; $valores = [0];
				$estado = $this->phuyu_model->phuyu_editar("restaurante.mesaspedido", $campos, $valores, "codpedido",$this->request->campos->codpedido);

				$campos = ["situacion"]; $valores = [1];
				$estado = $this->phuyu_model->phuyu_editar("restaurante.mesas", $campos, $valores, "codmesa", $this->request->campos->codmesa);


				// KARDEX PEDIDO SALIDAS DE RECETAS //

				$serie = $this->db->query("select ct.abreviatura as comprobante,c.codcomprobantetipo, c.seriecomprobante from caja.comprobantes as c inner join caja.comprobantetipos as ct on(c.codcomprobantetipo=ct.codcomprobantetipo) where c.codcomprobantetipo=4 and c.codsucursal=".$_SESSION["phuyu_codsucursal"]." and c.codalmacen=".$_SESSION["phuyu_codalmacen"]." and c.estado=1")->result_array();

				$campos = ["codkardex_ref","codsucursal","codalmacen","codpersona","codusuario","codmovimientotipo","fechacomprobante","fechakardex","codcomprobantetipo","seriecomprobante","codcomprobantetipo_ref"];
				$valores = [
					(int)$codkardex,(int)$_SESSION["phuyu_codsucursal"],(int)$_SESSION["phuyu_codalmacen"],1,(int)$_SESSION["phuyu_codusuario"],
					(int)28,$this->request->campos->fechacomprobante, $this->request->campos->fechakardex,
					(int)$serie[0]["codcomprobantetipo"],$serie[0]["seriecomprobante"],0
				];
				$codkardex = $this->phuyu_model->phuyu_guardar("kardex.kardex", $campos, $valores, "true");

				$campos = ["codsucursal","codalmacen","codkardex","codusuario","codmovimientotipo","fechakardex","codcomprobantetipo","seriecomprobante"];
				$valores = [
					(int)$_SESSION["phuyu_codsucursal"], (int)$_SESSION["phuyu_codalmacen"], (int)$codkardex, (int)$_SESSION["phuyu_codusuario"],
					(int)$this->request->campos->codmovimientotipo, $this->request->campos->fechakardex,(int)$serie[0]["codcomprobantetipo"],
					$serie[0]["seriecomprobante"]
				];
				$codkardexalmacen = $this->phuyu_model->phuyu_guardar("kardex.kardexalmacen", $campos, $valores, "true");
				$nro_comprobante = $this->Kardex_model->phuyu_kardexcorrelativo($codkardex,$codkardexalmacen,(int)$serie[0]["codcomprobantetipo"],$serie[0]["seriecomprobante"]);

				$totalsalida = 0;
				foreach ($this->request->detalle as $key => $value) { 
					$recetas = $this->db->query("select *from restaurante.recetas where codproducto=".$this->request->detalle[$key]->codproducto." and codunidad=".$this->request->detalle[$key]->codunidad." and estado=1")->result_array();
					$item = 0;
					foreach ($recetas as $v) { $item = $item + 1;
						$costo = $this->db->query("select preciocosto from almacen.productounidades where codproducto=".$v["codproducto_receta"]." and codunidad=".$v["codunidad_receta"]." and estado=1")->result_array();

						$campos = ["codkardex","codproducto","codunidad","item","cantidad","preciobruto","preciosinigv","preciounitario","preciorefunitario","codafectacionigv","valorventa","subtotal"];
						$cantidad = round(($this->request->detalle[$key]->cantidad * $v["cantidad"]),3);
						$subtotal = round(($cantidad * $costo[0]["preciocosto"]),2); $totalsalida = $totalsalida + $subtotal;
						$valores = [
							(int)$codkardex, (int)$v["codproducto_receta"], (int)$v["codunidad_receta"], $item, (double)$cantidad,
							(double)$costo[0]["preciocosto"],(double)$costo[0]["preciocosto"],(double)$costo[0]["preciocosto"],
							(double)$costo[0]["preciocosto"],'10',(double)$subtotal,(double)$subtotal
						];
						$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexdetalle", $campos, $valores);

						$campos =["codkardexalmacen","codproducto","codunidad","item","codalmacen","codsucursal","cantidad"];
						$valores =[
							(int)$codkardexalmacen,(int)$v["codproducto_receta"], (int)$v["codunidad_receta"], $item,
							(int)$_SESSION["phuyu_codalmacen"], (int)$_SESSION["phuyu_codsucursal"], (double)$cantidad
						];
						$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexalmacendetalle", $campos, $valores);

						$existe = $this->db->query("select *from almacen.productoubicacion where codalmacen=".$_SESSION["phuyu_codalmacen"]." and codproducto=".$v["codproducto_receta"]." and codunidad=".$v["codunidad_receta"])->result_array();
						$stock = round($existe[0]["stockactual"] - $cantidad,3);
						$stockconvertido = round($existe[0]["stockactualconvertido"] - $cantidad,3);

						$campos = ["stockactual","stockactualconvertido"]; $valores = [(double)$stock,(double)$stockconvertido];
						$f = ["codalmacen","codproducto","codunidad"]; 
						$v = [(int)$_SESSION["phuyu_codalmacen"],(int)$v["codproducto_receta"], (int)$v["codunidad_receta"]];
						$estado = $this->phuyu_model->phuyu_editar_1("almacen.productoubicacion", $campos, $valores, $f, $v);
					}
				}
				$campos = ["valorventa","igv","importe"]; $valores = [round($totalsalida,2),18,round($totalsalida,2)];
				$estado = $this->phuyu_model->phuyu_editar("kardex.kardex", $campos, $valores, "codkardex", $codkardex);

				if ($this->db->trans_status() === FALSE){
				    $this->db->trans_rollback(); $estado = 0;
				}else{
					if ($estado!=1) {
						$this->db->trans_rollback(); $estado = 0;
					}
					$this->db->trans_commit();
				}
				$data["estado"] = $estado; $data["codkardex"] = $codkardex_return;
				echo json_encode($data);
			}else{
				echo json_encode("e");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function anular_pedido(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			$estado = $this->phuyu_model->phuyu_eliminar("kardex.pedidos", "codpedido", $this->request->codregistro);
			
			$campos = ["estado"]; $valores = [0];
			$estado = $this->phuyu_model->phuyu_editar("restaurante.mesaspedido", $campos, $valores, "codpedido", $this->request->codregistro);

			$campos = ["situacion"]; $valores = [1];
			$estado = $this->phuyu_model->phuyu_editar("restaurante.mesas", $campos, $valores, "codmesa", $this->request->codmesa);

			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function clonar(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$this->request = json_decode(file_get_contents('php://input'));
				$info = $this->db->query("select kardex.codpedido,kardex.fechapedido, kardex.codcomprobantetipo,kardex.seriecomprobante, kardex.nrocomprobante,kardex.cliente,kardex.direccion,kardex.descripcion,personas.codpersona, personas.razonsocial,comprobantes.descripcion as tipo,kardex.valorventa,kardex.descglobal,kardex.igv,kardex.importe,kardex.condicionpago,kardex.codcomprobantetiporeferencia from kardex.pedidos as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codpedido=".$this->request->codregistro)->result_array();

				$info[0]["nrodias"] = 30;
				$info[0]["nrocuotas"] = 1;
				$info[0]["tasainteres"] = 0;
				$cuotas = [];
				if($info[0]["condicionpago"]==2){
					$credito = $this->db->query("select *from kardex.creditospedidos where codpedido=".$info[0]["codpedido"])->result_array();

					if(count($credito)>0){
						$info[0]["nrodias"] = $credito[0]["nrodias"];
						$info[0]["nrocuotas"] = $credito[0]["nrocuotas"];
						$info[0]["tasainteres"] = $credito[0]["tasainteres"];
						$cuotas = $this->db->query("select *from kardex.cuotaspedidos where codcreditopedido = ".$credito[0]["codcreditopedido"])->result_array();
					}
				}

				$socio = $this->db->query("Select *from personas WHERE codpersona=".$info[0]["codpersona"])->result_array();
				$detalle = $this->db->query("select kd.*,p.descripcion as producto,u.descripcion as unidad from kardex.pedidosdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codpedido=".$this->request->codregistro." and kd.estado=1 order by kd.item")->result_array();

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

				echo json_encode(["campos"=>$info,"socio"=>$socio,"detalle"=>$detalle,"cuotas"=>$cuotas]);
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
			$f = ["codsucursal","codcomprobantetipo"]; $v = [$_SESSION["phuyu_codsucursal"],8];
			$estado = $this->phuyu_model->phuyu_editar_1("caja.comprobantes", $campos, $valores, $f, $v);

			echo $formato;
		}
	}
	
}