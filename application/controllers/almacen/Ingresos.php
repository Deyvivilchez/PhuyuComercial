<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Ingresos extends CI_Controller {

	public function __construct(){
		parent::__construct(); 
		$this->load->model("phuyu_model"); $this->load->model("Kardex_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_codusuario"])) {
				$formato = $this->db->query("select formato from caja.comprobantes where codcomprobantetipo=3 AND codsucursal = ".$_SESSION["phuyu_codsucursal"])->result_array();
				$movimientos = $this->db->query("select *from almacen.movimientotipos where codmovimientotipo<>2 and codmovimientotipo<>8 and tipo=1 and estado=1")->result_array();
				if (count($formato)==0) {
					$_SESSION["phuyu_formato"] = "a4";
				}else{
					$_SESSION["phuyu_formato"] = $formato[0]["formato"];
				}
				$this->load->view("almacen/ingresos/index",compact("movimientos"));
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
			$limit = 10; $offset = $this->request->pagina * $limit - $limit;$movimiento="";

			if ($this->request->fechas->filtro == 0) {
				$fechas = "";
			}else{
				if(!empty($this->request->fechas->desde)){
					$fechas = "kardex.fechacomprobante>='".$this->request->fechas->desde."' and kardex.fechacomprobante<='".$this->request->fechas->hasta."' and";
				}else{
					$fechas = "kardex.fechacomprobante<='".$this->request->fechas->hasta."' and";
				}
			}

			if($this->request->movimiento>0){
				$movimiento = ' kardex.codmovimientotipo='.$this->request->movimiento.' and';
			}

			$lista = $this->db->query("select kardex.codkardex ,kardex.codalmacen_ref,kardex.codmovimientotipo, kardex.codcomprobantetipo, kardex.seriecomprobante,kardex.nrocomprobante, kardex.fechakardex,round(kardex.importe,2) as importe,kardex.seriecomprobante_ref,kardex.nrocomprobante_ref, personas.razonsocial as cliente,tipos.descripcion as tipomovimiento,comprobantes.descripcion as tipo,kardex.estado from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo_ref=comprobantes.codcomprobantetipo) inner join almacen.movimientotipos as tipos on(kardex.codmovimientotipo=tipos.codmovimientotipo) where ".$fechas." ".$movimiento." codalmacen=".$_SESSION["phuyu_codalmacen"]." and (UPPER(personas.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(tipos.descripcion) like UPPER('%".$this->request->buscar."%') or UPPER(kardex.seriecomprobante) like UPPER('%".$this->request->buscar."%') or UPPER(kardex.nrocomprobante) like UPPER('%".$this->request->buscar."%') ) and tipos.tipo=1 and kardex.codmovimientotipo<>2 and kardex.codmovimientotipo<>8 order by kardex.codkardex desc offset ".$offset." limit ".$limit)->result_array();

			foreach ($lista as $key => $value) {
            	if($value["codalmacen_ref"] > 0){
					$info = $this->db->query("select descripcion from almacen.almacenes where codalmacen=".$value["codalmacen_ref"])->result_array();
					$lista[$key]["destino"] = $info[0]["descripcion"];
				}else{
					$lista[$key]["destino"] = '-';
				}
			}
			
			$total = $this->db->query("select count(*) as total from kardex.kardex as kardex inner join seguridad.usuarios as usuarios on (kardex.codusuario=usuarios.codusuario) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo_ref=comprobantes.codcomprobantetipo) inner join almacen.movimientotipos as tipos on(kardex.codmovimientotipo=tipos.codmovimientotipo) where ".$fechas." ".$movimiento." codalmacen=".$_SESSION["phuyu_codalmacen"]." and (UPPER(usuarios.usuario) like UPPER('%".$this->request->buscar."%') or UPPER(tipos.descripcion) like UPPER('%".$this->request->buscar."%') or UPPER(kardex.seriecomprobante) like UPPER('%".$this->request->buscar."%') or UPPER(kardex.nrocomprobante) like UPPER('%".$this->request->buscar."%') ) and tipos.tipo=1 and kardex.codmovimientotipo<>2 and kardex.codmovimientotipo<>8")->result_array();

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

	public function prestamosotorgados($codpersona){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$this->request = json_decode(file_get_contents('php://input'));
				$prestamo = $this->db->query("SELECT k.codkardex,k.codsucursal,k.codalmacen,sum(kd.cantidad) As cantidad, sum(kd.cantidaddevuelta) As cantidaddevuelta,k.cliente,k.direccion,k.fechakardex,k.hora,k.seriecomprobante,k.nrocomprobante,p.documento,round(k.valorventa,2) as valorventa,round(k.importe,2) as importe,k.codempleado,k.codpersona,k.condicionpago,k.igv,k.codcomprobantetipo FROM kardex.kardex k JOIN kardex.kardexdetalle kd ON k.codkardex = kd.codkardex JOIN public.personas as p on (k.codpersona=p.codpersona) WHERE k.codmovimientotipo = 25 and k.codsucursal=".$_SESSION["phuyu_codsucursal"]." and k.codpersona = ".$codpersona." and k.estado=1 GROUP BY k.codkardex, k.codsucursal,k.codpersona, k.valorventa, k.codalmacen,k.cliente,k.fechakardex,k.hora,k.seriecomprobante,k.nrocomprobante,p.documento HAVING sum(kd.cantidaddevuelta) < sum(kd.cantidad) order by k.fechakardex asc ")->result_array();

				$data = array(); $data["prestamo"] = $prestamo;
				echo json_encode($data);
			}
		}
	}

	public function listarprestamos(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$prestamo = $this->db->query("SELECT k.codkardex,k.codsucursal,k.codalmacen,sum(kd.cantidad) As cantidad, sum(kd.cantidaddevuelta) As cantidaddevuelta,k.cliente,k.direccion,k.fechakardex,k.hora,k.seriecomprobante,k.nrocomprobante,p.documento,round(k.valorventa,2) as valorventa,round(k.importe,2) as importe,k.codempleado,k.codpersona,k.condicionpago,k.igv,k.codcomprobantetipo FROM kardex.kardex k JOIN kardex.kardexdetalle kd ON k.codkardex = kd.codkardex JOIN public.personas as p on (k.codpersona=p.codpersona) WHERE k.codmovimientotipo = 25 and k.codsucursal=".$_SESSION["phuyu_codsucursal"]." and k.estado=1 GROUP BY k.codkardex, k.codsucursal,k.codpersona, k.valorventa, k.codalmacen,k.cliente,k.fechakardex,k.hora,k.seriecomprobante,k.nrocomprobante,p.documento HAVING sum(kd.cantidaddevuelta) < sum(kd.cantidad) order by k.fechakardex asc ")->result_array();

				echo json_encode($prestamo);
			}
		}
	}

	function detalle($codregistro){
		if ($this->input->is_ajax_request()) {
			$detalle = $this->db->query("select kd.codproducto,kd.codunidad,round(kd.cantidad - kd.cantidaddevuelta,2) as cantidad,round(kd.preciounitario,2) as precio,kd.preciosinigv,kd.preciorefunitario,kd.valorventa,round(kd.igv,2) as igv, round(kd.subtotal,2) as subtotal,kd.item, kd.codafectacionigv,p.descripcion as producto,u.descripcion as unidad, kd.recoger,kd.recogido,kd.descripcion,kd.codafectacionigv from kardex.kardexdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codkardex=".$codregistro." and kd.estado=1 and kd.cantidad > kd.cantidaddevuelta order by kd.item")->result_array();

			foreach ($detalle as $key => $value) {
				$unidades = $this->db->query("select *FROM almacen.v_productounidades pun where pun.codproducto=".$value["codproducto"]." AND codalmacen = ".$_SESSION["phuyu_codalmacen"])->result_array();

				$detalle[$key]["unidades"] = $unidades[0]["unidades"];
			}

			$totales = $this->db->query("select codkardex,round(valorventa,2) as valorventa,round(igv,2) as igv,round(importe,2) as importe from kardex.kardex where codkardex=".$codregistro)->result_array();

			$data["detalle"] = $detalle;
			$data["totales"] = $totales;
			echo json_encode($data);
		}
	}

	public function nuevo(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$movimientos = $this->db->query("select *from almacen.movimientotipos where codmovimientotipo<>2 and codmovimientotipo<>11 and tipo=1 and estado=1")->result_array();
				$serie = $this->db->query("select ct.abreviatura as comprobante,c.codcomprobantetipo, c.seriecomprobante from caja.comprobantes as c inner join caja.comprobantetipos as ct on(c.codcomprobantetipo=ct.codcomprobantetipo) where c.codcomprobantetipo=3 and c.codsucursal=".$_SESSION["phuyu_codsucursal"]." and c.codalmacen=".$_SESSION["phuyu_codalmacen"]." and c.estado=1")->result_array();
				$tipocomprobantes = $this->db->query("select *from caja.comprobantetipos where ingresoalmacen=1 and estado=1 order by codcomprobantetipo")->result_array();
				$almacenes = $this->db->query("select almacen.*, sucursal.descripcion as sucursal from almacen.almacenes as almacen inner join public.sucursales as sucursal on(almacen.codsucursal=sucursal.codsucursal) where almacen.codalmacen<>".$_SESSION["phuyu_codalmacen"]." and almacen.estado=1")->result_array();
				$this->load->view("almacen/ingresos/nuevo",compact("movimientos","serie","tipocomprobantes","almacenes"));
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
				$info = $this->db->query("select kardex.*,personas.*,comprobantes.descripcion as tipo from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codkardex=".$codregistro)->result_array();

				$detalle = $this->db->query("select kd.*,p.descripcion as producto,u.descripcion as unidad,p.codigo from kardex.kardexdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codkardex=".$codregistro." and kd.estado=1 order by kd.item")->result_array();

				$this->load->view("almacen/ingresos/ver",compact("info","detalle")); 
			}else{
	            $this->load->view("inicio/505");
	        }
	    }else{
			$this->load->view("inicio/404");
		}
	}

	function verprestamo($codregistro){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_codusuario"])){
				$info = $this->db->query("select kardex.*,personas.*,comprobantes.descripcion as tipo from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codkardex=".$codregistro)->result_array();

				$detalle = $this->db->query("select kd.*,p.descripcion as producto,u.descripcion as unidad,p.codigo,(kd.cantidad - kd.cantidaddevuelta) as cantidadxdevolver from kardex.kardexdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codkardex=".$codregistro." and kd.estado=1 order by kd.item")->result_array();

				$this->load->view("almacen/ingresos/verprestamo",compact("info","detalle")); 
			}else{
	            $this->load->view("inicio/505");
	        }
	    }else{
			$this->load->view("inicio/404");
		}
	}
	
	function guardar(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$this->request = json_decode(file_get_contents('php://input'));
//echo "dd";exit;
				$this->db->trans_begin();

				$proveedor = $this->db->query("select razonsocial,direccion,documento,d.abreviatura as tipo from public.personas p inner join public.documentotipos d on(p.coddocumentotipo=d.coddocumentotipo) where p.codpersona=".$this->request->campos->codpersona)->result_array();

				$this->request->campos->cliente = $proveedor[0]["razonsocial"];
				$this->request->campos->direccion = $proveedor[0]["direccion"];

				// REGISTRO KARDEX //
				$campos = ["codkardex_ref","codsucursal","codalmacen","codalmacen_ref","codpersona","codusuario","codmovimientotipo","fechakardex","fechacomprobante","codcomprobantetipo","seriecomprobante","codcomprobantetipo_ref","seriecomprobante_ref","nrocomprobante_ref","valorventa","porcigv","igv","importe","descripcion","cliente","direccion"];
				$valores = [ (int)$this->request->campos->codkardex_ref,
					(int)$_SESSION["phuyu_codsucursal"],
					(int)$_SESSION["phuyu_codalmacen"],
					(int)$this->request->campos->codalmacen_ref,
					(int)$this->request->campos->codpersona,
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
					$this->request->campos->direccion
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
				foreach ($this->request->detalle as $key => $value) { $item = $item + 1;
					$campos = ["codkardex","codproducto","codunidad","item","cantidad","preciobruto","preciosinigv","preciounitario","preciorefunitario","codafectacionigv","valorventa","subtotal","igv","itemorigen","factor"];
					$valores =[
						(int)$codkardex,
						(int)$this->request->detalle[$key]->codproducto,
						(int)$this->request->detalle[$key]->codunidad, $item,
						(double)$this->request->detalle[$key]->cantidad,
						(double)$this->request->detalle[$key]->precio,
						(double)$this->request->detalle[$key]->preciosinigv,
						(double)$this->request->detalle[$key]->precio,
						(double)$this->request->detalle[$key]->precio,
						$this->request->detalle[$key]->codafectacionigv,
						(double)$this->request->detalle[$key]->valorventa,
						(double)$this->request->detalle[$key]->subtotal,
						(double)$this->request->detalle[$key]->igv,
						(int)$this->request->detalle[$key]->itemorigen,
						(double)$this->request->detalle[$key]->factor
					];
					$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexdetalle", $campos, $valores);

					$campos =["codkardexalmacen","codproducto","codunidad","item","codalmacen","codsucursal","cantidad","factor"];
					$valores =[
						(int)$codkardexalmacen,
						(int)$this->request->detalle[$key]->codproducto,
						(int)$this->request->detalle[$key]->codunidad, $item,
						(int)$_SESSION["phuyu_codalmacen"],
						(int)$_SESSION["phuyu_codsucursal"],
						(double)$this->request->detalle[$key]->cantidad,
						(double)$this->request->detalle[$key]->factor
					];
					$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexalmacendetalle", $campos, $valores);

					$existe_ubi = $this->db->query("select *from almacen.productoubicacion where codalmacen=".$_SESSION["phuyu_codalmacen"]." and codproducto=".$this->request->detalle[$key]->codproducto." and codunidad=".$this->request->detalle[$key]->codunidad)->result_array();
					
					if (count($existe_ubi)>0) {
						$stock = round($existe_ubi[0]["stockactual"] + $this->request->detalle[$key]->cantidad,3);

						$campos = ["stockactual"]; $valores = [(double)$stock];
						$f = ["codalmacen","codproducto","codunidad"]; 
						$v = [(int)$_SESSION["phuyu_codalmacen"],(int)$this->request->detalle[$key]->codproducto,(int)$this->request->detalle[$key]->codunidad];
						$estado = $this->phuyu_model->phuyu_editar_1("almacen.productoubicacion", $campos, $valores, $f, $v);
					}else{
						$campos = ["codalmacen","codproducto","codunidad","codsucursal","stockactual","stockactualreal"];
						$valores =[
							(int)$_SESSION["phuyu_codalmacen"],
							(int)$this->request->detalle[$key]->codproducto,
							(int)$this->request->detalle[$key]->codunidad,
							(int)$_SESSION["phuyu_codsucursal"],
							(double)$this->request->detalle[$key]->cantidad,
							(double)$this->request->detalle[$key]->cantidad
						];
						$estado = $this->phuyu_model->phuyu_guardar("almacen.productoubicacion", $campos, $valores);
					}

					// AUMENTAMOS EL STOCKACTUALCONVERTIDO

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

	function editar(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$this->request = json_decode(file_get_contents('php://input'));
				$info = $this->db->query("select codkardex,fechacomprobante,fechakardex, seriecomprobante, nrocomprobante, descripcion,codmovimientotipo,codpersona,cliente from kardex.kardex where codkardex=".$this->request->codregistro)->result_array();
				$movimientos = $this->db->query("select *from almacen.movimientotipos where codmovimientotipo<>2 and tipo=1 and estado=1")->result_array();
				$this->load->view("almacen/ingresos/editar",compact("info","movimientos"));
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

			$proveedor = $this->db->query("select razonsocial,direccion,documento,d.abreviatura as tipo from public.personas p inner join public.documentotipos d on(p.coddocumentotipo=d.coddocumentotipo) where p.codpersona=".$this->request->codpersona)->result_array();

			$this->request->cliente = $proveedor[0]["razonsocial"];
			$this->request->direccion = $proveedor[0]["direccion"];

			$campos = ["codpersona","codmovimientotipo","fechacomprobante","fechakardex","descripcion","cliente","direccion"];
			$valores = [
				$this->request->codpersona,
				$this->request->codmovimientotipo,
				$this->request->fechacomprobante,
				$this->request->fechakardex,
				$this->request->descripcion,
				$this->request->cliente,
				$this->request->direccion
			];
			$estado = $this->phuyu_model->phuyu_editar("kardex.kardex", $campos, $valores, "codkardex",$this->request->codregistro);

			$campos = ["fechakardex"]; $valores = [$this->request->fechakardex];
			$estado_u = $this->phuyu_model->phuyu_editar("kardex.kardexalmacen", $campos, $valores, "codkardex",$this->request->codregistro);
			echo $estado;
		}
	}

	function eliminar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$this->db->trans_begin();

			// ACTUALIZAMOS PRODUCTOS UBICACION //
			$kardexalmacen = $this->db->query("select codkardexalmacen from kardex.kardexalmacen where codkardex=".$this->request->codregistro)->result_array();

			$kardex = $this->db->query("select *from kardex.kardex where codkardex=".$this->request->codregistro)->result_array();

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

			$estado = $this->phuyu_model->phuyu_eliminar("kardex.kardex", "codkardex", $this->request->codregistro);
			$estado = $this->phuyu_model->phuyu_eliminar("kardex.kardexalmacen", "codkardexalmacen", $kardexalmacen[0]["codkardexalmacen"]);

			// REGISTRO KARDEX ANULADOS //
			$campos = ["codkardex","codsucursal","codusuario","fechaanulacion","observaciones"];
			$valores =[
				(int)$this->request->codregistro, (int)$_SESSION["phuyu_codsucursal"], (int)$_SESSION["phuyu_codusuario"], date("Y-m-d"),
				"SALIDA DE ALMACEN ANULADO"
			];
			$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexanulados", $campos, $valores);

			// REGISTRO KARDEX ALMACEN ANULADOS //
			$campos = ["codkardexalmacen","codsucursal","codusuario","fechaanulacion","observaciones"];
			$valores =[
				(int)$kardexalmacen[0]["codkardexalmacen"], (int)$_SESSION["phuyu_codsucursal"], (int)$_SESSION["phuyu_codusuario"], date("Y-m-d"),
				"SALIDA DE ALMACEN ANULADO"
			];
			$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexalmacenanulado", $campos, $valores);

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

	// Transferecias de almacen //
	function transferencias(){
		if ($this->input->is_ajax_request()) {
			$transferencias = $this->db->query("select k.codkardex,k.fechakardex,k.seriecomprobante,k.nrocomprobante,k.seriecomprobante_ref, k.nrocomprobante_ref,round(k.importe,2) as importe, a.descripcion as almacen from kardex.kardex as k inner join almacen.almacenes as a on(k.codalmacen=a.codalmacen) where k.codalmacen_ref=".$_SESSION["phuyu_codalmacen"]." and k.transferido=0 and k.codmovimientotipo=30 and k.estado=1")->result_array();
			echo json_encode($transferencias);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function transferencia_detalle($codkardex){
		if ($this->input->is_ajax_request()) {
			$detalle = $this->db->query("select kd.*,p.descripcion as producto, u.descripcion as unidad from kardex.kardexdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where codkardex=".$codkardex)->result_array();
			echo json_encode($detalle);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function prestamo_detalle($codkardex){
		if ($this->input->is_ajax_request()) {
			$detalle = $this->db->query("select kd.codproducto,kd.codunidad,round(kd.cantidad - kd.cantidaddevuelta,2) as cantidad,round(kd.preciounitario,2) as precio,kd.preciosinigv,kd.preciorefunitario,kd.valorventa,round(kd.igv,2) as igv, round(kd.subtotal,2) as subtotal,kd.item, kd.codafectacionigv,p.descripcion as producto,u.descripcion as unidad, kd.recoger,kd.recogido,kd.descripcion,kd.codafectacionigv from kardex.kardexdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codkardex=".$codkardex." and kd.estado=1 and kd.cantidad > kd.cantidaddevuelta order by kd.item")->result_array();

			foreach ($detalle as $key => $value) {
				$unidades = $this->db->query("select *FROM almacen.v_productounidades pun where pun.codproducto=".$value["codproducto"]." AND codalmacen = ".$_SESSION["phuyu_codalmacen"])->result_array();

				$detalle[$key]["unidades"] = $unidades[0]["unidades"];
			}
			echo json_encode($detalle);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function formato($formato){
		if ($this->input->is_ajax_request()) {
			$campos = ["formato"]; $valores = [$formato];
			$f = ["codsucursal","codcomprobantetipo"]; $v = [$_SESSION["phuyu_codsucursal"],3];
			$estado = $this->phuyu_model->phuyu_editar_1("caja.comprobantes", $campos, $valores, $f, $v);

			echo $formato;
		}
	}

	function guardar_transferencia(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$this->request = json_decode(file_get_contents('php://input'));

				$this->db->trans_begin();

				// REGISTRO KARDEX //
				$info = $this->db->query("select *from kardex.kardex where codkardex=".$this->request->kardex_ref)->result_array();
				$series = $this->db->query("select seriecomprobante from caja.comprobantes where codcomprobantetipo=3 and codsucursal=".$_SESSION["phuyu_codsucursal"]." and codalmacen=".$_SESSION["phuyu_codalmacen"]." and estado=1")->result_array();

				$campos = ["codkardex_ref","codsucursal","codalmacen","codalmacen_ref","codpersona","codusuario","codmovimientotipo","fechakardex","fechacomprobante","codcomprobantetipo","seriecomprobante","codcomprobantetipo_ref","seriecomprobante_ref","nrocomprobante_ref","descripcion"];
				$valores = [
					(int)$this->request->kardex_ref,
					(int)$_SESSION["phuyu_codsucursal"],
					(int)$_SESSION["phuyu_codalmacen"],
					(int)$info[0]["codalmacen"],1,
					(int)$_SESSION["phuyu_codusuario"],12,date("Y-m-d"),date("Y-m-d"),3,
					$series[0]["seriecomprobante"],
					(int)$info[0]["codcomprobantetipo_ref"],
					$info[0]["seriecomprobante_ref"],
					$info[0]["nrocomprobante_ref"],
					"INGRESO POR TRANSFERENCIA"
				];
				$codkardex = $this->phuyu_model->phuyu_guardar("kardex.kardex", $campos, $valores, "true");

				// REGISTRO KARDEX ALMACEN //
				$campos = ["codsucursal","codalmacen","codalmacen_ref","codkardex","codusuario","codmovimientotipo","fechakardex","codcomprobantetipo","seriecomprobante"];
				$valores = [
					(int)$_SESSION["phuyu_codsucursal"],
					(int)$_SESSION["phuyu_codalmacen"],
					(int)$info[0]["codalmacen"],
					(int)$codkardex,
					(int)$_SESSION["phuyu_codusuario"],12,date("Y-m-d"),3,
					$series[0]["seriecomprobante"]
				];
				$codkardexalmacen = $this->phuyu_model->phuyu_guardar("kardex.kardexalmacen", $campos, $valores, "true");
				$nro_comprobante = $this->Kardex_model->phuyu_kardexcorrelativo($codkardex,$codkardexalmacen,3,$series[0]["seriecomprobante"]);

				// REGISTRO KARDEX DETALLE Y KARDEX ALMACEN DETALLE //
				$item = 0; $subtotal = 0; $igv = 0; $total = 0;
				foreach ($this->request->detalle as $key => $value) { 
					$item = $item + 1;
					$subtotal = $subtotal + (double)$this->request->detalle[$key]->subtotal;
					$igv = $igv + (double)$this->request->detalle[$key]->igv;
					$total = $total + (double)$this->request->detalle[$key]->subtotal;

					$campos = ["codkardex","codproducto","codunidad","item","cantidad","preciobruto","preciosinigv","preciounitario","preciorefunitario","codafectacionigv","valorventa","subtotal"];
					$valores =[
						(int)$codkardex,
						(int)$this->request->detalle[$key]->codproducto,
						(int)$this->request->detalle[$key]->codunidad, $item,
						(double)$this->request->detalle[$key]->cantidad,
						(double)$this->request->detalle[$key]->preciounitario,
						(double)$this->request->detalle[$key]->preciounitario,
						(double)$this->request->detalle[$key]->preciounitario,
						(double)$this->request->detalle[$key]->preciounitario,
						$this->request->detalle[$key]->codafectacionigv,
						(double)$this->request->detalle[$key]->subtotal,
						(double)$this->request->detalle[$key]->subtotal
					];
					$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexdetalle", $campos, $valores);

					$campos = ["codkardexalmacen","codproducto","codunidad","codalmacen","item","codsucursal","cantidad"];
					$valores =[
						(int)$codkardexalmacen,
						(int)$this->request->detalle[$key]->codproducto,
						(int)$this->request->detalle[$key]->codunidad,
						(int)$_SESSION["phuyu_codalmacen"], $item,
						(int)$_SESSION["phuyu_codsucursal"],
						(double)$this->request->detalle[$key]->cantidad
					];
					$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexalmacendetalle", $campos, $valores);

					$existe_ubi = $this->db->query("select *from almacen.productoubicacion where codalmacen=".$_SESSION["phuyu_codalmacen"]." and codproducto=".$this->request->detalle[$key]->codproducto." and codunidad=".$this->request->detalle[$key]->codunidad)->result_array();
					
					if (count($existe_ubi)>0) {
						$stock = round($existe_ubi[0]["stockactual"] + $this->request->detalle[$key]->cantidad,3);

						$campos = ["stockactual"]; $valores = [(double)$stock];
						$f = ["codalmacen","codproducto","codunidad"]; 
						$v = [(int)$_SESSION["phuyu_codalmacen"],(int)$this->request->detalle[$key]->codproducto,(int)$this->request->detalle[$key]->codunidad];
						$estado = $this->phuyu_model->phuyu_editar_1("almacen.productoubicacion", $campos, $valores, $f, $v);
					}else{
						$campos = ["codalmacen","codproducto","codunidad","codsucursal","stockactual","stockactualreal"];
						$valores =[
							(int)$_SESSION["phuyu_codalmacen"],
							(int)$this->request->detalle[$key]->codproducto,
							(int)$this->request->detalle[$key]->codunidad,
							(int)$_SESSION["phuyu_codsucursal"],
							(double)$this->request->detalle[$key]->cantidad,
							(double)$this->request->detalle[$key]->cantidad
						];
						$estado = $this->phuyu_model->phuyu_guardar("almacen.productoubicacion", $campos, $valores);
					}

					// AUMENTAMOS EL STOCKACTUALCONVERTIDO

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

				$campos = ["valorventa","porcigv","igv","importe"]; $valores = [$subtotal,$_SESSION["phuyu_igv"],$igv,$total];
				$estado = $this->phuyu_model->phuyu_editar("kardex.kardex", $campos, $valores, "codkardex", $codkardex);

				$campos = ["transferido"]; $valores = [1];
				$estado = $this->phuyu_model->phuyu_editar("kardex.kardex", $campos, $valores, "codkardex", $this->request->kardex_ref);

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
		}
	}

	function guardar_prestamo(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				//echo "lol";exit;
				$this->request = json_decode(file_get_contents('php://input'));

				$this->db->trans_begin();

				// REGISTRO KARDEX //
				$info = $this->db->query("select *from kardex.kardex where codkardex=".$this->request->kardex_ref)->result_array();
				$series = $this->db->query("select seriecomprobante from caja.comprobantes where codcomprobantetipo=3 and codsucursal=".$_SESSION["phuyu_codsucursal"]." and codalmacen=".$_SESSION["phuyu_codalmacen"]." and estado=1")->result_array();

				$campos = ["codkardex_ref","codsucursal","codalmacen","codalmacen_ref","codpersona","codusuario","codmovimientotipo","fechakardex","fechacomprobante","codcomprobantetipo","seriecomprobante","codcomprobantetipo_ref","seriecomprobante_ref","nrocomprobante_ref","descripcion"];
				$valores = [
					(int)$this->request->kardex_ref,
					(int)$_SESSION["phuyu_codsucursal"],
					(int)$_SESSION["phuyu_codalmacen"],
					(int)$info[0]["codalmacen"],$this->request->codpersona,
					(int)$_SESSION["phuyu_codusuario"],11,date("Y-m-d"),date("Y-m-d"),3,
					$series[0]["seriecomprobante"],
					(int)$info[0]["codcomprobantetipo_ref"],
					$info[0]["seriecomprobante_ref"],
					$info[0]["nrocomprobante_ref"],
					"INGRESO POR DEVOLUCION DE PRESTAMOS OTORGADOS"
				];
				$codkardex = $this->phuyu_model->phuyu_guardar("kardex.kardex", $campos, $valores, "true");

				// REGISTRO KARDEX ALMACEN //
				$campos = ["codsucursal","codalmacen","codalmacen_ref","codkardex","codusuario","codmovimientotipo","fechakardex","codcomprobantetipo","seriecomprobante"];
				$valores = [
					(int)$_SESSION["phuyu_codsucursal"],
					(int)$_SESSION["phuyu_codalmacen"],
					(int)$info[0]["codalmacen"],
					(int)$codkardex,
					(int)$_SESSION["phuyu_codusuario"],11,date("Y-m-d"),3,
					$series[0]["seriecomprobante"]
				];
				$codkardexalmacen = $this->phuyu_model->phuyu_guardar("kardex.kardexalmacen", $campos, $valores, "true");
				$nro_comprobante = $this->Kardex_model->phuyu_kardexcorrelativo($codkardex,$codkardexalmacen,3,$series[0]["seriecomprobante"]);

				// REGISTRO KARDEX DETALLE Y KARDEX ALMACEN DETALLE //
				$item = 0; $subtotal = 0; $igv = 0; $total = 0;
				foreach ($this->request->detalle as $key => $value) { 
					$item = $item + 1;
					$subtotal = $subtotal + (double)$this->request->detalle[$key]->subtotal;
					$igv = $igv + (double)$this->request->detalle[$key]->igv;
					$total = $total + (double)$this->request->detalle[$key]->subtotal;

					$campos = ["codkardex","codproducto","codunidad","item","cantidad","preciobruto","preciosinigv","preciounitario","preciorefunitario","codafectacionigv","valorventa","subtotal","igv","itemorigen"];
					$valores =[
						(int)$codkardex,
						(int)$this->request->detalle[$key]->codproducto,
						(int)$this->request->detalle[$key]->codunidad, $item,
						(double)$this->request->detalle[$key]->cantidad,
						(double)$this->request->detalle[$key]->preciounitario,
						(double)$this->request->detalle[$key]->preciounitario,
						(double)$this->request->detalle[$key]->preciounitario,
						(double)$this->request->detalle[$key]->preciounitario,
						$this->request->detalle[$key]->codafectacionigv,
						(double)$this->request->detalle[$key]->subtotal,
						(double)$this->request->detalle[$key]->subtotal,
						(double)$this->request->detalle[$key]->igv,
						(int)$this->request->detalle[$key]->itemorigen
					];
					$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexdetalle", $campos, $valores);

					$campos = ["codkardexalmacen","codproducto","codunidad","codalmacen","item","codsucursal","cantidad"];
					$valores =[
						(int)$codkardexalmacen,
						(int)$this->request->detalle[$key]->codproducto,
						(int)$this->request->detalle[$key]->codunidad,
						(int)$_SESSION["phuyu_codalmacen"], $item,
						(int)$_SESSION["phuyu_codsucursal"],
						(double)$this->request->detalle[$key]->cantidad
					];
					$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexalmacendetalle", $campos, $valores);

					$existe_ubi = $this->db->query("select *from almacen.productoubicacion where codalmacen=".$_SESSION["phuyu_codalmacen"]." and codproducto=".$this->request->detalle[$key]->codproducto." and codunidad=".$this->request->detalle[$key]->codunidad)->result_array();
					
					if (count($existe_ubi)>0) {
						$stock = round($existe_ubi[0]["stockactual"] + $this->request->detalle[$key]->cantidad,3);

						$campos = ["stockactual"]; $valores = [(double)$stock];
						$f = ["codalmacen","codproducto","codunidad"]; 
						$v = [(int)$_SESSION["phuyu_codalmacen"],(int)$this->request->detalle[$key]->codproducto,(int)$this->request->detalle[$key]->codunidad];
						$estado = $this->phuyu_model->phuyu_editar_1("almacen.productoubicacion", $campos, $valores, $f, $v);
					}else{
						$campos = ["codalmacen","codproducto","codunidad","codsucursal","stockactual","stockactualreal"];
						$valores =[
							(int)$_SESSION["phuyu_codalmacen"],
							(int)$this->request->detalle[$key]->codproducto,
							(int)$this->request->detalle[$key]->codunidad,
							(int)$_SESSION["phuyu_codsucursal"],
							(double)$this->request->detalle[$key]->cantidad,
							(double)$this->request->detalle[$key]->cantidad
						];
						$estado = $this->phuyu_model->phuyu_guardar("almacen.productoubicacion", $campos, $valores);
					}

					// AUMENTAMOS EL STOCKACTUALCONVERTIDO

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

				    $cantidaddevuelta = $this->db->query("select *from kardex.kardexdetalle where codkardex=".$this->request->kardex_ref." AND codproducto=".(int)$this->request->detalle[$key]->codproducto." AND item=".(int)$this->request->detalle[$key]->itemorigen)->result_array();

					$factororigen = $this->db->query("select *from almacen.productounidades where codproducto=".$this->request->detalle[$key]->codproducto." and codunidad=".$cantidaddevuelta[0]["codunidad"])->result_array();

					$cantidaddevueltaconvertida = (float)$this->request->detalle[$key]->cantidad*(float)$factor[0]["factor"]/$factororigen[0]["factor"];

					$campos = ["cantidaddevuelta"]; $valores = [(double)$cantidaddevuelta[0]["cantidaddevuelta"]+(double)$cantidaddevueltaconvertida];

					$f = ["codkardex","codproducto","item"]; 
					$v = [(int)$this->request->kardex_ref,(int)$this->request->detalle[$key]->codproducto,(int)$this->request->detalle[$key]->itemorigen];
					$estado = $this->phuyu_model->phuyu_editar_1("kardex.kardexdetalle", $campos, $valores, $f, $v);
				}

				$campos = ["valorventa","porcigv","igv","importe"]; $valores = [$subtotal,$_SESSION["phuyu_igv"],$igv,$total];
				$estado = $this->phuyu_model->phuyu_editar("kardex.kardex", $campos, $valores, "codkardex", $codkardex);

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
		}
	}

	function clonar(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$this->request = json_decode(file_get_contents('php://input'));
				$info = $this->db->query("select kardex.codkardex,kardex.fechacomprobante,kardex.fechakardex,kardex.codmoneda, kardex.tipocambio,kardex.codcomprobantetipo,kardex.retirar,kardex.afectacaja,kardex.seriecomprobante, kardex.nrocomprobante,kardex.nroplaca,kardex.cliente,kardex.direccion,kardex.descripcion,personas.codpersona, personas.razonsocial,comprobantes.descripcion as tipo,kardex.flete,kardex.gastos,kardex.valorventa,kardex.descglobal,kardex.igv,kardex.importe,kardex.condicionpago,kardex.codmovimientotipo,kardex.codcomprobantetipo_ref,kardex.seriecomprobante_ref,kardex.nrocomprobante_ref,kardex.codalmacen_ref from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codkardex=".$this->request->codregistro)->result_array();

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
					$detalle[$key]["control"] = 1;

				}

				echo json_encode(["campos"=>$info,"socio"=>$socio,"detalle"=>$detalle]);
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}
}