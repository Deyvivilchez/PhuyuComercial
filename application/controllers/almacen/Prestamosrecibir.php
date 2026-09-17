<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Prestamosrecibir extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model"); $this->load->model("Kardex_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$this->load->view("almacen/prestamosrecibir/index");
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

			$where = '';

			if($this->request->estadodespacho != ""){
				if($this->request->estadodespacho==0){
					$where = ' HAVING sum(kd.cantidaddevuelta) < sum(kd.cantidad) ';
				}else{
					$where = ' HAVING sum(kd.cantidaddevuelta) = sum(kd.cantidad) ';
				}
			}

			$lista = $this->db->query("SELECT k.codkardex,k.codsucursal,k.codalmacen,(sum(kd.cantidad) - sum(kd.cantidaddevuelta)) As cantidaddevuelta,p.razonsocial as cliente,k.direccion,k.fechakardex,k.hora,k.seriecomprobante,k.nrocomprobante,p.documento,round(k.valorventa,2) as valorventa,round(k.importe,2) as importe,k.codempleado,k.codpersona,k.condicionpago,k.igv,k.codcomprobantetipo,k.codmovimientotipo FROM kardex.kardex k JOIN kardex.kardexdetalle kd ON k.codkardex = kd.codkardex JOIN public.personas as p on (k.codpersona=p.codpersona) WHERE k.codmovimientotipo = 25 and k.codsucursal=".$_SESSION["phuyu_codsucursal"]." and k.estado=1 GROUP BY k.codkardex, k.codsucursal,k.codpersona, k.valorventa, k.codalmacen,p.razonsocial,k.fechakardex,k.hora,k.seriecomprobante,k.nrocomprobante,p.documento ".$where." order by k.fechakardex asc ")->result_array();

			$total = $this->db->query("select count(*) as total FROM kardex.kardex k JOIN kardex.kardexdetalle kd ON k.codkardex = kd.codkardex WHERE k.codmovimientotipo = 25 and k.codsucursal=".$_SESSION["phuyu_codsucursal"]." and k.estado=1 GROUP BY k.codkardex, k.codsucursal,k.codpersona, k.valorventa, k.codalmacen,k.cliente,k.fechakardex,k.hora,k.seriecomprobante,k.nrocomprobante ".$where." ")->result_array();

			if(count($total)==0){
				$total[0]["total"] = 0;
			}

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

	public function nuevo($codkardex){
		if ($this->input->is_ajax_request()) {
			$info = $this->db->query("select * from kardex.kardex where codkardex=".$codkardex)->result_array();
			$this->load->view("almacen/prestamosrecibir/nuevo",compact("info"));
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function filtro_prestamos(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			if ($this->request->filtro==1) {
				$filtro = " fechakardex>='".$this->request->fechadesde."' and fechakardex<='".$this->request->fechahasta."' and ";
			}else{
				$filtro = "";
			}

			if ($this->request->estado!="") {
				$filtro = $filtro." estado=".$this->request->estado." and ";
			}

			$prestamos = $this->db->query("select *from kardex.kardex where codkardex_ref=".$this->request->codkardex." and codsucursal=".$_SESSION["phuyu_codsucursal"]." and ".$filtro." codmovimientotipo=".$this->request->tipo." order by codkardex")->result_array();

			$data["prestamos"] = $prestamos;
			echo json_encode($data);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function filtrar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$lista = $this->db->query("select kardex.codkardex, kardex.codmovimientotipo, kardex.codcomprobantetipo, kardex.seriecomprobante,kardex.condicionpago,kardex.nrocomprobante, kardex.fechakardex,round(kardex.importe,2) as importe,kardex.estado,comprobantes.descripcion as tipo from kardex.kardex as kardex inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where codpersona=".$this->request->codpersona." and kardex.seriecomprobante='".$this->request->seriecomprobante."' and kardex.nrocomprobante='".$this->request->nrocomprobante."' and kardex.codmovimientotipo=20")->result_array();
			echo json_encode($lista);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function detalle($codkardex){
		if ($this->input->is_ajax_request()) {
			$detalle = $this->db->query("select kd.codproducto,kd.codunidad,kd.cantidad as cantidadprestada,round(kd.cantidad - kd.cantidaddevuelta,2) as cantidad,kd.cantidaddevuelta,round(kd.preciounitario,2) as preciounitario,kd.preciobruto,kd.preciosinigv,kd.preciorefunitario,kd.valorventa,round(kd.igv,2) as igv, round(kd.subtotal,2) as subtotal,kd.item, kd.codafectacionigv,p.descripcion as producto,u.descripcion as unidad, kd.recoger,kd.recogido,kd.descripcion,kd.codafectacionigv,kd.factor from kardex.kardexdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codkardex=".$codkardex." and kd.estado=1 and kd.cantidad > kd.cantidaddevuelta order by kd.item")->result_array();
			$data["detalle"] = $detalle;
			echo json_encode($data);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function detalle_edicion($codkardex,$codkardexpadre){
		if ($this->input->is_ajax_request()) {
			$detalle = $this->db->query("select kd.codproducto,kd.codunidad,kd.cantidad,kd.cantidad as cantidadanterior,round(kd.preciounitario,2) as preciounitario,kd.preciobruto,kd.preciosinigv,kd.preciorefunitario,kd.valorventa,round(kd.igv,2) as igv, round(kd.subtotal,2) as subtotal,kd.item, kd.codafectacionigv,p.descripcion as producto,u.descripcion as unidad, kd.itemorigen,kd.recoger,kd.recogido,kd.descripcion,kd.codafectacionigv,kd.factor from kardex.kardexdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codkardex=".$codkardex." and kd.estado=1 and kd.cantidad > kd.cantidaddevuelta order by kd.item")->result_array();

			foreach ($detalle as $key => $value) {
				$detalle_ori = $this->db->query("select round(kd.cantidad - kd.cantidaddevuelta,2) as cantidadpendiente from kardex.kardexdetalle as kd where kd.codkardex=".$codkardexpadre." and kd.codproducto = ".$value["codproducto"]." and kd.item=".$value["itemorigen"]." and kd.estado=1")->result_array();

				$detalle[$key]["cantidadpendiente"] = (double)$detalle_ori[0]["cantidadpendiente"] + $value["cantidad"];
			}

			$data["detalle"] = $detalle;
			echo json_encode($data);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function ver_devolucion(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			$info = $this->db->query("select k.*,p.razonsocial as cliente, p.documento from kardex.kardex k JOIN public.personas p ON k.codpersona = p.codpersona where codkardex=".$this->request->codregistro)->result_array();

			$detalle = $this->db->query("select kd.*,p.descripcion as producto,u.descripcion as unidad,p.codigo from kardex.kardexdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codkardex=".$this->request->codregistro." and kd.estado=1 order by kd.item")->result_array();

			$this->load->view("almacen/prestamosdevolver/ver",compact("info","detalle"));
		}
	}

	function guardar(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$this->request = json_decode(file_get_contents('php://input'));

				$this->db->trans_begin();
				$series = $this->db->query("select seriecomprobante from caja.comprobantes where codcomprobantetipo=3 and codsucursal=".$_SESSION["phuyu_codsucursal"]." and codalmacen=".$_SESSION["phuyu_codalmacen"]." and estado=1")->result_array();

				$campos = ["codkardex_ref","codsucursal","codalmacen","codalmacen_ref","codpersona","codusuario","codmovimientotipo","fechakardex","fechacomprobante","codcomprobantetipo","seriecomprobante","codcomprobantetipo_ref","seriecomprobante_ref","nrocomprobante_ref","descripcion"];
				$valores = [
					(int)$this->request->campos->codkardex_ref,
					(int)$_SESSION["phuyu_codsucursal"],
					(int)$_SESSION["phuyu_codalmacen"],
					(int)$info[0]["codalmacen"],$this->request->campos->codpersona,
					(int)$_SESSION["phuyu_codusuario"],11,date("Y-m-d"),date("Y-m-d"),3,
					$series[0]["seriecomprobante"],
					(int)$this->request->campos->codcomprobantetipo,
					$this->request->campos->seriecomprobante_ref,
					$this->request->campos->nrocomprobante_ref,
					"INGRESO POR DEVOLUCION DE PRESTAMOS OTORGADOS"
				];
				$codkardex = $this->phuyu_model->phuyu_guardar("kardex.kardex", $campos, $valores, "true");
				
				// REGISTRO KARDEX ALMACEN //
				$series = $this->db->query("select seriecomprobante from caja.comprobantes where codcomprobantetipo=".$this->request->campos->codcomprobantetipo." and codsucursal=".$_SESSION["phuyu_codsucursal"]." and codalmacen=".$_SESSION["phuyu_codalmacen"]." and estado=1")->result_array();

				$campos = ["codsucursal","codalmacen","codkardex","codusuario","codmovimientotipo","fechakardex","codcomprobantetipo","seriecomprobante","observacion"];
				$valores = [
					(int)$_SESSION["phuyu_codsucursal"],
					(int)$_SESSION["phuyu_codalmacen"],
					(int)$codkardex,
					(int)$_SESSION["phuyu_codusuario"],
					11,date("Y-m-d"),
					3,
					$series[0]["seriecomprobante"],
					$this->request->campos->observacion
				];
				$codkardexalmacen = $this->phuyu_model->phuyu_guardar("kardex.kardexalmacen", $campos, $valores, "true");
				$nro_comprobante = $this->Kardex_model->phuyu_kardexcorrelativo($codkardex,$codkardexalmacen,4,$series[0]["seriecomprobante"]);

				// REGISTRO KARDEX DETALLE Y KARDEX ALMACEN DETALLE //
				$item = 0; $subtotal = 0; $igv = 0; $total = 0; $valorventa = 0;
				foreach ($this->request->detalle as $key => $value) {
					if ((double)($this->request->detalle[$key]->recoger)!=0) {
						$item = $item + 1;
						$valorventaitem = (double)$this->request->detalle[$key]->recoger*(double)$this->request->detalle[$key]->preciosinigv;
						$subtotalitem = (double)$this->request->detalle[$key]->recoger*(double)$this->request->detalle[$key]->preciounitario;
						$igvitem = $subtotalitem - $valorventaitem;
						$valorventa = $valorventa + $valorventaitem;
						$total = $total + $subtotalitem;
						$igv = $igv + $igvitem;

						$campos = ["codkardex","codproducto","codunidad","item","cantidad","preciobruto","preciosinigv","preciounitario","preciorefunitario","codafectacionigv","valorventa","subtotal","igv","itemorigen","factor"];
						$valores =[
							(int)$codkardex,
							(int)$this->request->detalle[$key]->codproducto,
							(int)$this->request->detalle[$key]->codunidad, $item,
							(double)$this->request->detalle[$key]->recoger,
							(double)$this->request->detalle[$key]->preciobruto,
							(double)$this->request->detalle[$key]->preciosinigv,
							(double)$this->request->detalle[$key]->preciounitario,
							(double)$this->request->detalle[$key]->preciorefunitario,
							$this->request->detalle[$key]->codafectacionigv,
							(double)$valorventaitem,
							(double)$subtotalitem,
							(double)$igvitem,
							(int)$this->request->detalle[$key]->item,
							(double)$this->request->detalle[$key]->factor
						];
						$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexdetalle", $campos, $valores);

						$campos = ["codkardexalmacen","codproducto","codunidad","codalmacen","item","codsucursal","cantidad","factor"];
						$valores =[
							(int)$codkardexalmacen,
							(int)$this->request->detalle[$key]->codproducto,
							(int)$this->request->detalle[$key]->codunidad,
							(int)$_SESSION["phuyu_codalmacen"], $item,
							(int)$_SESSION["phuyu_codsucursal"],
							(double)$this->request->detalle[$key]->recoger,
							(double)$this->request->detalle[$key]->factor
						];
						$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexalmacendetalle", $campos, $valores);

					    $cantidaddevuelta = $this->db->query("select cantidaddevuelta from kardex.kardexdetalle where codkardex=".$this->request->campos->codkardex_ref." AND codproducto=".(int)$this->request->detalle[$key]->codproducto." AND item=".(int)$this->request->detalle[$key]->item)->result_array();

					    if((double)$this->request->detalle[$key]->cantidadprestada < ((double)$cantidaddevuelta[0]["cantidaddevuelta"]+(double)$this->request->detalle[$key]->recoger)){
					    	echo 0; exit; 
					    }

						$campos = ["cantidaddevuelta"]; $valores = [(double)$cantidaddevuelta[0]["cantidaddevuelta"]+(double)$this->request->detalle[$key]->recoger];

						$f = ["codkardex","codproducto","item"]; 
						$v = [(int)$this->request->campos->codkardex_ref,(int)$this->request->detalle[$key]->codproducto,(int)$this->request->detalle[$key]->item];
						$estado = $this->phuyu_model->phuyu_editar_1("kardex.kardexdetalle", $campos, $valores, $f, $v);
					}
				}

				$campos = ["valorventa","porcigv","igv","importe"]; $valores = [$valorventa,$_SESSION["phuyu_igv"],$igv,$total];
				$estado = $this->phuyu_model->phuyu_editar("kardex.kardex", $campos, $valores, "codkardex", $codkardex);

				$this->db->query("SELECT f_actualizarstockkardex(".$_SESSION["phuyu_codalmacen"].",".$codkardex.",1)")->result_array();

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

	function guardarcambios(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$this->request = json_decode(file_get_contents('php://input'));

				$this->db->trans_begin();

				$estado = $this->db->query("SELECT f_restaurarstockkardex(".$_SESSION["phuyu_codalmacen"].",".$this->request->campos->codkardex.",-1)")->result_array();

				$kardexalmacen = $this->db->query("select codkardexalmacen from kardex.kardexalmacen where codkardex=".$this->request->campos->codkardex)->result_array();

				$estado = $this->phuyu_model->phuyu_eliminar_total('kardex.kardexalmacendetalle','codkardexalmacen',$kardexalmacen[0]["codkardexalmacen"]);

				// REGISTRO KARDEX DETALLE Y KARDEX ALMACEN DETALLE //
				$item = 0; $subtotal = 0; $igv = 0; $total = 0; $valorventa = 0;
				foreach ($this->request->detalle as $key => $value) {
					if((double)$this->request->detalle[$key]->cantidad!=0){
						$item = $item + 1;
						$valorventaitem = (double)$this->request->detalle[$key]->cantidad*(double)$this->request->detalle[$key]->preciosinigv;
						$subtotalitem = (double)$this->request->detalle[$key]->cantidad*(double)$this->request->detalle[$key]->preciounitario;
						$igvitem = $subtotalitem - $valorventaitem;
						$valorventa = $valorventa + $valorventaitem;
						$total = $total + $subtotalitem;
						$igv = $igv + $igvitem;

						$campos = ["codkardex","codproducto","codunidad","item","cantidad","preciobruto","preciosinigv","preciounitario","preciorefunitario","codafectacionigv","valorventa","subtotal","igv","itemorigen","factor"];
						$valores =[
							(int)$this->request->campos->codkardex,
							(int)$this->request->detalle[$key]->codproducto,
							(int)$this->request->detalle[$key]->codunidad, $item,
							(double)$this->request->detalle[$key]->cantidad,
							(double)$this->request->detalle[$key]->preciobruto,
							(double)$this->request->detalle[$key]->preciosinigv,
							(double)$this->request->detalle[$key]->preciounitario,
							(double)$this->request->detalle[$key]->preciorefunitario,
							$this->request->detalle[$key]->codafectacionigv,
							(double)$valorventaitem,
							(double)$subtotalitem,
							(double)$igvitem,
							(int)$this->request->detalle[$key]->itemorigen,
							(double)$this->request->detalle[$key]->factor
						];
						$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexdetalle", $campos, $valores);

						$campos = ["codkardexalmacen","codproducto","codunidad","codalmacen","item","codsucursal","cantidad","factor"];
						$valores =[
							(int)$kardexalmacen[0]["codkardexalmacen"],
							(int)$this->request->detalle[$key]->codproducto,
							(int)$this->request->detalle[$key]->codunidad,
							(int)$_SESSION["phuyu_codalmacen"], $item,
							(int)$_SESSION["phuyu_codsucursal"],
							(double)$this->request->detalle[$key]->cantidad,
							(double)$this->request->detalle[$key]->factor
						];
						$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexalmacendetalle", $campos, $valores);

					    $cantidaddevuelta = $this->db->query("select cantidaddevuelta,cantidad from kardex.kardexdetalle where codkardex=".$this->request->campos->codkardex_ref." AND codproducto=".(int)$this->request->detalle[$key]->codproducto." AND item=".(int)$this->request->detalle[$key]->itemorigen)->result_array();

					    $cantidadactualdevuelta = (double)$cantidaddevuelta[0]["cantidaddevuelta"] - (double)$this->request->detalle[$key]->cantidadanterior;

					    if((double)$cantidaddevuelta[0]["cantidad"] < ((double)$cantidadactualdevuelta+(double)$this->request->detalle[$key]->cantidad))
					    {
					    	echo 0; exit; 
					    }

						$campos = ["cantidaddevuelta"]; $valores = [(double)$cantidadactualdevuelta+(double)$this->request->detalle[$key]->cantidad];

						$f = ["codkardex","codproducto","item"]; 
						$v = [(int)$this->request->campos->codkardex_ref,(int)$this->request->detalle[$key]->codproducto,(int)$this->request->detalle[$key]->itemorigen];
						$estado = $this->phuyu_model->phuyu_editar_1("kardex.kardexdetalle", $campos, $valores, $f, $v);
					}
				}

				$campos = ["valorventa","porcigv","igv","importe"]; $valores = [$valorventa,$_SESSION["phuyu_igv"],$igv,$total];
				$estado = $this->phuyu_model->phuyu_editar("kardex.kardex", $campos, $valores, "codkardex", $this->request->campos->codkardex);

				$this->db->query("SELECT f_actualizarstockkardex(".$_SESSION["phuyu_codalmacen"].",".$this->request->campos->codkardex.",1)")->result_array();

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

	function historial($codkardex){
		if ($this->input->is_ajax_request()) {
			$info = $this->db->query("select k.cliente from kardex.kardex k where k.codkardex=".$codkardex)->result_array();
			$this->load->view("almacen/prestamosrecibir/historial",compact("info"));
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function editar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			$info = $this->db->query("select * from kardex.kardex where codkardex=".$this->request->codregistro)->result_array();
			$this->load->view("almacen/prestamosrecibir/editar",compact("info"));
		}
	}

	function eliminar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$this->db->trans_begin();
//echo "lol";exit;
			// ACTUALIZAMOS PRODUCTOS UBICACION //
			$kardexalmacen = $this->db->query("select codkardexalmacen from kardex.kardexalmacen where codkardex=".$this->request->codregistro)->result_array();

			$info = $this->db->query("select *from kardex.kardexdetalle where codkardex=".$this->request->codregistro)->result_array();
			foreach ($info as $key => $value) {

				$cantidaddevuelta = $this->db->query("select cantidaddevuelta from kardex.kardexdetalle where codkardex=".$this->request->codkardex_ref." AND codproducto=".$value["codproducto"]." AND item=".$value["itemorigen"])->result_array();

				$campos = ["cantidaddevuelta"]; $valores = [(double)$cantidaddevuelta[0]["cantidaddevuelta"]-(double)$value["cantidad"]];
				$f = ["codkardex","codproducto","item"]; 
				$v = [(int)$this->request->codkardex_ref,$value["codproducto"],$value["itemorigen"]];
				$estado = $this->phuyu_model->phuyu_editar_1("kardex.kardexdetalle", $campos, $valores, $f, $v);
			}

			$this->db->query("SELECT f_actualizarstockkardex(".$_SESSION["phuyu_codalmacen"].",".$this->request->codregistro.",-1)")->result_array();

			$estado = $this->phuyu_model->phuyu_eliminar("kardex.kardex", "codkardex", $this->request->codregistro);
			if(count($kardexalmacen)>0){
			    $estado = $this->phuyu_model->phuyu_eliminar("kardex.kardexalmacen", "codkardexalmacen", $kardexalmacen[0]["codkardexalmacen"]);
			}

			// REGISTRO KARDEX ANULADOS //
			$campos = ["codkardex","codsucursal","codusuario","fechaanulacion","observaciones"];
			$valores =[
				(int)$this->request->codregistro, (int)$_SESSION["phuyu_codsucursal"], (int)$_SESSION["phuyu_codusuario"], date("Y-m-d"),"DEVOLUCION DE PRESTAMOS ANULADO"
			];
			$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexanulados", $campos, $valores);

			// REGISTRO KARDEX ALMACEN ANULADOS //
			if(count($kardexalmacen)>0){
				$campos = ["codkardexalmacen","codsucursal","codusuario","fechaanulacion","observaciones"];
				$valores =[
					(int)$kardexalmacen[0]["codkardexalmacen"], (int)$_SESSION["phuyu_codsucursal"], (int)$_SESSION["phuyu_codusuario"], date("Y-m-d"),
					"DEVOLUCION DE PRESTAMOS ANULADO"
				];
				$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexalmacenanulado", $campos, $valores);
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
}