<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Inventarios extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model"); $this->load->model("Kardex_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$this->load->view("almacen/inventarios/index");
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
			$limit = 4; $offset = $this->request->pagina * $limit - $limit;

			$lista = $this->db->query("select inventarios.*,round(inventarios.importe,2) as importe_r,almacenes.descripcion as almacen,sucursales.descripcion as sucursal from almacen.inventarios as inventarios inner join almacen.almacenes as almacenes on(inventarios.codalmacen=almacenes.codalmacen) inner join public.sucursales as sucursales on(sucursales.codsucursal=almacenes.codsucursal) where (UPPER(inventarios.descripcion) like UPPER('%".$this->request->buscar."%') or UPPER(sucursales.descripcion) like UPPER('%".$this->request->buscar."%') ) and inventarios.codsucursal=".$_SESSION["phuyu_codsucursal"]." and inventarios.estado<2 order by inventarios.codinventario desc offset ".$offset." limit ".$limit)->result_array();

			$total = $this->db->query("select count(*) as total from almacen.inventarios as inventarios inner join almacen.almacenes as almacenes on(inventarios.codalmacen=almacenes.codalmacen) inner join public.sucursales as sucursales on(sucursales.codsucursal=almacenes.codsucursal) where (UPPER(inventarios.descripcion) like UPPER('%".$this->request->buscar."%') or UPPER(sucursales.descripcion) like UPPER('%".$this->request->buscar."%') ) and inventarios.codsucursal=".$_SESSION["phuyu_codsucursal"]." and inventarios.estado<2")->result_array();

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

	public function nuevo(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$sucursales = $this->db->query("select *from public.sucursales where estado=1")->result_array();
				$this->load->view("almacen/inventarios/nuevo",compact("sucursales"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function reabrir_inventario($codinventario){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$info = $this->db->query("select inventarios.codinventario,almacenes.descripcion as almacen,sucursales.descripcion as sucursal from almacen.inventarios as inventarios inner join almacen.almacenes as almacenes on(inventarios.codalmacen=almacenes.codalmacen) inner join public.sucursales as sucursales on(sucursales.codsucursal=almacenes.codsucursal) where inventarios.codinventario=".$codinventario)->result_array();
				$lineas = $this->db->query("select *from almacen.lineas WHERE estado = 1")->result_array();
				$this->load->view("almacen/inventarios/reabrir",compact("info","lineas"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function almacenes($codsucursal){
		if ($this->input->is_ajax_request()) {
			$almacenes = $this->db->query("select *from almacen.almacenes where codsucursal=".$codsucursal." and estado=1")->result_array();
			echo json_encode($almacenes);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function guardar(){
		if ($this->input->is_ajax_request()) {
			$campos = ["codsucursal","codalmacen","tipoinventario","codmovimientotipo","codusuario","descripcion"];
			$this->request = json_decode(file_get_contents('php://input'));
			
			if ($this->request->tipoinventario==0) {
				$descripcion = "INVENTARIO INICIAL";
			}else{
				if ($this->request->tipoinventario==1) {
					$descripcion = "INVENTARIO SEMANAL";
				}else{
					if ($this->request->tipoinventario==2) {
						$descripcion = "INVENTARIO MENSUAL";
					}else{
						if ($this->request->tipoinventario==3) {
							$descripcion = "INVENTARIO TRIMESTRAL";
						}else{
							$descripcion = "INVENTARIO ANUAL";
						}
					}
				}
			}
			$valores = [$this->request->codsucursal,$this->request->codalmacen,$this->request->tipoinventario,$this->request->codmovimientotipo,$_SESSION["phuyu_codusuario"],$descripcion];

			if($this->request->codregistro=="") {
				$estado = $this->phuyu_model->phuyu_guardar("almacen.inventarios", $campos, $valores);
			}else{
				$estado = $this->phuyu_model->phuyu_editar("almacen.inventarios", $campos, $valores, "codinventario", $this->request->codregistro);
			}
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function verinventario($codinventario){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$info = $this->db->query("select inventarios.codinventario,almacenes.descripcion as almacen,sucursales.descripcion as sucursal from almacen.inventarios as inventarios inner join almacen.almacenes as almacenes on(inventarios.codalmacen=almacenes.codalmacen) inner join public.sucursales as sucursales on(sucursales.codsucursal=almacenes.codsucursal) where inventarios.codinventario=".$codinventario)->result_array();
				$lineas = $this->db->query("Select *from almacen.lineas where estado = 1")->result_array();
				$this->load->view("almacen/inventarios/ver",compact("info","lineas"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function inventario($codinventario){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$info = $this->db->query("select inventarios.codinventario,almacenes.descripcion as almacen,sucursales.descripcion as sucursal from almacen.inventarios as inventarios inner join almacen.almacenes as almacenes on(inventarios.codalmacen=almacenes.codalmacen) inner join public.sucursales as sucursales on(sucursales.codsucursal=almacenes.codsucursal) where inventarios.codinventario=".$codinventario)->result_array();
				$lineas = $this->db->query("select *from almacen.lineas WHERE estado = 1")->result_array();
				$this->load->view("almacen/inventarios/inventario",compact("info","lineas"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function productos_unidades($codinventario, $codproducto){
		if ($this->input->is_ajax_request()) {
			$data = $this->db->query("select p.codproducto,p.codigo,(p.descripcion || ' - M:' || m.descripcion) as descripcion,u.codunidad,u.descripcion as unidad,m.descripcion as marca,round(id.cantidad,3) as cantidad, round(id.preciocosto,2) as preciocosto,id.precioventa as precioventa, (id.cantidad * id.preciocosto) as importe from almacen.productos as p inner join almacen.inventariodetalle as id on(p.codproducto=id.codproducto) inner join almacen.unidades as u on(u.codunidad=id.codunidad) inner join almacen.marcas as m on(p.codmarca=m.codmarca) where id.codinventario=".$codinventario." and id.codproducto=".$codproducto)->result_array();
			echo json_encode($data);
		}
	}

	function productos_inventario($codinventario){
		if ($this->input->is_ajax_request()) {
			$lineas = '';
			if(!empty($_GET["codlinea"])){
               $lineas = ' AND p.codlinea = '.$_GET["codlinea"];
			}
			$productos = $this->db->query("select p.codproducto,p.codigo,(p.descripcion || ' - M:' || m.descripcion) as descripcion,u.codunidad,u.descripcion as unidad,m.descripcion as marca,round(id.cantidad,3) as cantidad, round(id.preciocosto,2) as preciocosto,id.precioventa as precioventa, (id.cantidad * id.preciocosto) as importe from almacen.productos as p inner join almacen.inventariodetalle as id on(p.codproducto=id.codproducto) inner join almacen.unidades as u on(u.codunidad=id.codunidad) inner join almacen.marcas as m on(p.codmarca=m.codmarca) where id.codinventario=".$codinventario.$lineas." order by p.descripcion asc")->result_array();
			$importe = $this->db->query("select sum(id.cantidad * id.preciocosto) as importe from almacen.inventariodetalle id JOIN almacen.productos p ON id.codproducto = p.codproducto where codinventario=".$codinventario.$lineas)->result_array();

			$data = array();
			$data["productos"] = $productos;
			$data["importe"] = $importe[0]["importe"];
			echo json_encode($data);
		}
	}

	function mas_productos_inventario($codinventario){
		if ($this->input->is_ajax_request()) {
			$productos = $this->db->query("select p.codproducto,p.codigo,p.descripcion,u.codunidad,u.descripcion as unidad,m.descripcion as marca,0 as cantidad, pu.preciocompra as preciocosto,pu.pventapublico as precioventa, 0 as importe from almacen.productos as p inner join almacen.productounidades as pu on(p.codproducto=pu.codproducto) inner join almacen.unidades as u on(u.codunidad=pu.codunidad) inner join almacen.marcas as m on(p.codmarca=m.codmarca) where p.estado=1 and p.controlstock=1 and pu.estado=1 and (pu.codproducto || cast(pu.codunidad AS VARCHAR) ) not in (select (id.codproducto || cast(id.codunidad AS VARCHAR) ) as pro_uni from almacen.inventariodetalle as id where id.codinventario=".$codinventario.") order by p.descripcion asc")->result_array();

			foreach ($productos as $key => $value) {
				$campos = ["codinventario","codproducto","codunidad","cantidad","preciocosto","precioventa","importecosto","importe"];
				$valores = [$codinventario,$value["codproducto"],$value["codunidad"],$value["cantidad"],$value["preciocosto"],$value["precioventa"],$value["importe"],$value["importe"]];

				$estado = $this->phuyu_model->phuyu_guardar("almacen.inventariodetalle", $campos, $valores);
			}
			echo json_encode($productos);
		}
	}

	function productos_quitaritem($codinventario, $codproducto, $codunidad){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			$existe = $this->db->query("select *from almacen.inventariodetalle where codinventario=".$codinventario." and codproducto=".$codproducto." and codunidad=".$codunidad)->result_array(); $estado = 1;
			if (count($existe) > 0) {
				$this->db->where("codinventario", $codinventario);
				$this->db->where("codproducto", $codproducto);
				$this->db->where("codunidad", $codunidad);
				$estado = $this->db->delete("almacen.inventariodetalle");
			}
			echo $estado;
		}
	}

	function guardar_inventario(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$this->db->trans_begin();

			$campos = ["codinventario","codproducto","codunidad","cantidad","preciocosto","precioventa","importecosto","importe"];
			if (isset($this->request->productos)) {
				foreach ($this->request->productos as $key => $value) {
					$valores = [$this->request->campos->codregistro,$this->request->productos[$key]->codproducto,$this->request->productos[$key]->codunidad,$this->request->productos[$key]->cantidad,$this->request->productos[$key]->preciocosto,$this->request->productos[$key]->precioventa,$this->request->productos[$key]->importe,$this->request->productos[$key]->importe];

					$existe = $this->db->query("select *from almacen.inventariodetalle where codinventario=".$this->request->campos->codregistro." and codproducto=".$this->request->productos[$key]->codproducto." and codunidad=".$this->request->productos[$key]->codunidad)->result_array();
					if (count($existe)==0) {
						$estado = $this->phuyu_model->phuyu_guardar("almacen.inventariodetalle", $campos, $valores);
					}else{
						$f = ["codinventario","codproducto","codunidad"]; 
						$v = [$this->request->campos->codregistro,$this->request->productos[$key]->codproducto,$this->request->productos[$key]->codunidad];
						$estado = $this->phuyu_model->phuyu_editar_1("almacen.inventariodetalle", $campos, $valores, $f, $v);
					}
				}
				
				$campos = ["importecosto","importe"]; $valores = [$this->request->campos->importe,$this->request->campos->importe];
				$estado = $this->phuyu_model->phuyu_editar("almacen.inventarios", $campos, $valores, "codinventario", $this->request->campos->codregistro);
			}

			if ($this->db->trans_status() === FALSE){
			    $this->db->trans_rollback(); $estado = 0;
			}else{
				$this->db->trans_commit(); $estado = 1;
			}

			echo $estado;
		}
	}

	function actualizarpreciosproductos(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			if (isset($this->request->productos)) {
				$campos = ["preciocompra","preciocosto","pventapublico"];
				foreach ($this->request->productos as $key => $value) {
					$valores = [
						(double)$this->request->productos[$key]->preciocosto,
						(double)$this->request->productos[$key]->preciocosto,
						(double)$this->request->productos[$key]->precioventa
					];

					$f = ["codproducto","codunidad"]; 
					$v = [(int)$this->request->productos[$key]->codproducto,(int)$this->request->productos[$key]->codunidad];
					$estado = $this->phuyu_model->phuyu_editar_1("almacen.productounidades", $campos, $valores, $f, $v);
				}
			}
			echo 1;
		}
	}

	function guardar_editar_inventario(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			$inventario = $this->db->query("select *from almacen.inventarios where codinventario=".$this->request->codregistro)->result_array(); $estado = 1;
			foreach ($this->request->detalle as $key => $value) {
				$kardex = $this->db->query("select COALESCE(sum(( CASE WHEN  codmovimientotipo < 20 THEN 1 ELSE -1 END) * kardex.kardexdetalle.cantidad) ,0) as cantidad FROM kardex.kardexdetalle INNER JOIN kardex.kardex ON (kardex.kardexdetalle.codkardex = kardex.kardex.codkardex) WHERE (kardex.kardex.codkardex<>".$inventario[0]["codkardex_ingreso"]." and kardex.kardex.codkardex<>".$inventario[0]["codkardex_salida"].") and kardex.kardex.codalmacen = ".$inventario[0]["codalmacen"]." AND kardex.kardexdetalle.codproducto = ".$this->request->detalle[$key]->codproducto." and kardex.kardexdetalle.codunidad = ".$this->request->detalle[$key]->codunidad." AND kardex.kardex.estado = 1")->result_array();

				// EDITAR KARDEX DE INGRESO //

				$verificar = round((double)$this->request->detalle[$key]->cantidad + ((double)$kardex[0]["cantidad"] * -1),2);
				if ((double)$verificar < 0) {
					$estado = "NO SE PUEDE ACTUALIZAR EL INVENTARIO FINAL PORQUE EL INVENTARIO INICIAL NO PUEDE SER NEGATIVO ".round($verificar,3); break;
				}

				$cantidad_ingreso = (double)$verificar; $cantidad_salida = 0;

				/* $direrencia = round((double)$kardex[0]["cantidad"] - (double)$this->request->detalle[$key]->cantidad,2);
				
				if ($direrencia==0) {
					$cantidad_ingreso = 0; $cantidad_salida = 0;
				}else{
					if ($direrencia>0) {
						$cantidad_ingreso = $kardex[0]["cantidad"]; $cantidad_salida = $direrencia;
					}else{
						$cantidad_ingreso = ($direrencia * (-1));
						if ($kardex[0]["cantidad"]<0) {
							$cantidad_ingreso = ($direrencia * (-1)) + $this->request->detalle[$key]->cantidad;
						}
						$cantidad_salida = 0;
					}
				} */

				$existe_ingreso = $this->db->query("select item from kardex.kardexdetalle where codkardex=".$inventario[0]["codkardex_ingreso"]." and codproducto=".$this->request->detalle[$key]->codproducto." and codunidad=".$this->request->detalle[$key]->codunidad)->result_array();
				$kardex_almacen_ingreso = $this->db->query("select codkardexalmacen from kardex.kardexalmacen where codkardex=".$inventario[0]["codkardex_ingreso"])->result_array();

				if (count($existe_ingreso)==0) {
					$item_ingreso = $this->db->query("select coalesce(max(item),0) as item from kardex.kardexdetalle where codkardex=".$inventario[0]["codkardex_ingreso"])->result_array();

					$subtotal = round(($cantidad_ingreso * (double)$this->request->detalle[$key]->preciocosto),2);
					$precio = $this->request->detalle[$key]->preciocosto;

					$campos = ["codkardex","codproducto","codunidad","item","cantidad","preciobruto","preciosinigv","preciounitario","preciorefunitario","valorventa","subtotal","recoger"];					
					$valores =[
						(int)$inventario[0]["codkardex_ingreso"],(int)$this->request->detalle[$key]->codproducto,(int)$this->request->detalle[$key]->codunidad,$item_ingreso[0]["item"],(double)$cantidad_ingreso,
						(double)$precio,(double)$precio,(double)$precio,(double)$precio,
						(double)$subtotal,(double)$subtotal,1
					];
					$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexdetalle", $campos, $valores);

					$campos = ["codkardexalmacen","codproducto","codunidad","item","codalmacen","codsucursal","cantidad"];
					$valores =[
						(int)$kardex_almacen_ingreso[0]["codkardexalmacen"],(int)$this->request->detalle[$key]->codproducto,(int)$this->request->detalle[$key]->codunidad,$item_ingreso[0]["item"],(int)$inventario[0]["codalmacen"],
						(int)$inventario[0]["codsucursal"],(double)$cantidad_ingreso
					];
					$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexalmacendetalle", $campos, $valores);
				}else{
					$data = array("cantidad" => $cantidad_ingreso);
					$this->db->where("codkardex",$inventario[0]["codkardex_ingreso"]);
					$this->db->where("codproducto",$this->request->detalle[$key]->codproducto);
					$this->db->where("codunidad",$this->request->detalle[$key]->codunidad);
					$estado = $this->db->update("kardex.kardexdetalle",$data);

					$data = array("cantidad" => $cantidad_ingreso);
					$this->db->where("codkardexalmacen",$kardex_almacen_ingreso[0]["codkardexalmacen"]);
					$this->db->where("codproducto",$this->request->detalle[$key]->codproducto);
					$this->db->where("codunidad",$this->request->detalle[$key]->codunidad);
					$estado = $this->db->update("kardex.kardexalmacendetalle",$data);
				}

				// EDITAR KARDEX DE SALIDA // 

				$existe_salida = $this->db->query("select item from kardex.kardexdetalle where codkardex=".$inventario[0]["codkardex_salida"]." and codproducto=".$this->request->detalle[$key]->codproducto." and codunidad=".$this->request->detalle[$key]->codunidad)->result_array();
				$kardex_almacen_salida = $this->db->query("select codkardexalmacen from kardex.kardexalmacen where codkardex=".$inventario[0]["codkardex_salida"])->result_array();

				if (count($existe_salida)==0) {
					$item_salida = $this->db->query("select coalesce(max(item),0) as item from kardex.kardexdetalle where codkardex=".$inventario[0]["codkardex_salida"])->result_array();

					$subtotal = round(($cantidad_salida * $this->request->detalle[$key]->preciocosto),2);
					$precio = $this->request->detalle[$key]->preciocosto;

					$campos = ["codkardex","codproducto","codunidad","item","cantidad","preciobruto","preciosinigv","preciounitario","preciorefunitario","valorventa","subtotal","recoger"];					
					$valores =[
						(int)$inventario[0]["codkardex_salida"],(int)$this->request->detalle[$key]->codproducto,(int)$this->request->detalle[$key]->codunidad,$item_salida[0]["item"],(double)$cantidad_salida,
						(double)$precio,(double)$precio,(double)$precio,(double)$precio,
						(double)$subtotal,(double)$subtotal,1
					];
					$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexdetalle", $campos, $valores);

					$campos = ["codkardexalmacen","codproducto","codunidad","item","codalmacen","codsucursal","cantidad"];
					$valores =[
						(int)$kardex_almacen_salida[0]["codkardexalmacen"],(int)$this->request->detalle[$key]->codproducto,(int)$this->request->detalle[$key]->codunidad,$item_salida[0]["item"],(int)$inventario[0]["codalmacen"],
						(int)$inventario[0]["codsucursal"],(double)$cantidad_salida
					];
					$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexalmacendetalle", $campos, $valores);
				}else{
					$data = array("cantidad" => $cantidad_salida);
					$this->db->where("codkardex",$inventario[0]["codkardex_salida"]);
					$this->db->where("codproducto",$this->request->detalle[$key]->codproducto);
					$this->db->where("codunidad",$this->request->detalle[$key]->codunidad);
					$estado = $this->db->update("kardex.kardexdetalle",$data);

					$data = array("cantidad" => $cantidad_salida);
					$this->db->where("codkardexalmacen",$kardex_almacen_salida[0]["codkardexalmacen"]);
					$this->db->where("codproducto",$this->request->detalle[$key]->codproducto);
					$this->db->where("codunidad",$this->request->detalle[$key]->codunidad);
					$estado = $this->db->update("kardex.kardexalmacendetalle",$data);
				}

				$data = array(
					"cantidad" => $cantidad_ingreso,
					"importecosto" => round(($cantidad_ingreso * $this->request->detalle[$key]->preciocosto),2),
					"importe" => round(($cantidad_ingreso * $this->request->detalle[$key]->preciocosto),2)
				);
				$this->db->where("codinventario",$inventario[0]["codinventario"]);
				$this->db->where("codproducto",$this->request->detalle[$key]->codproducto);
				$this->db->where("codunidad",$this->request->detalle[$key]->codunidad);
				$estado = $this->db->update("almacen.inventariodetalle",$data);

				$campos = ["stockactual","stockactualreal"];
				$valores =[(double)$this->request->detalle[$key]->cantidad,$this->request->detalle[$key]->cantidad];
				$f = ["codalmacen","codproducto","codunidad"];
				$v = [(int)$inventario[0]["codalmacen"],$this->request->detalle[$key]->codproducto,$this->request->detalle[$key]->codunidad];
				$estado = $this->phuyu_model->phuyu_editar_1("almacen.productoubicacion", $campos, $valores, $f, $v);

				$stockconvertido = $this->db->query("select *from almacen.productoubicacion where codalmacen=".$inventario[0]["codalmacen"]." and codproducto=".$this->request->detalle[$key]->codproducto)->result_array();

				$factor = $this->db->query("select *from almacen.productounidades where codproducto=".$this->request->detalle[$key]->codproducto." and codunidad=".$this->request->detalle[$key]->codunidad)->result_array();

				foreach ($stockconvertido as $k => $val) {
					$stockc = 0;
					$productounidad = $this->db->query("select *from almacen.productounidades where codproducto=".$this->request->detalle[$key]->codproducto." and codunidad=".$val["codunidad"])->result_array();
					$stockc = ((double)$this->request->detalle[$key]->cantidad*(double)$factor[0]["factor"])/(double)$productounidad[0]["factor"];

					$stockcc = (double)$val["stockactualconvertido"] + $stockc;
					
					$campos = ["stockactualconvertido"]; $valores = [(double)$stockcc];
					$f = ["codalmacen","codproducto","codunidad"]; 
					$v = [(int)$inventario[0]["codalmacen"],(int)$this->request->detalle[$key]->codproducto,(int)$val["codunidad"]];
					$estado = $this->phuyu_model->phuyu_editar_1("almacen.productoubicacion", $campos, $valores, $f, $v);
				}

				$importe = $this->db->query("select sum(cantidad * preciocosto) as importe from almacen.inventariodetalle where codinventario=".$inventario[0]["codinventario"])->result_array();
				$data = array(
					"importecosto" => round($importe[0]["importe"],2),
					"importe" => round($importe[0]["importe"],2)
				);
				$this->db->where("codinventario",$inventario[0]["codinventario"]);
				$estado = $this->db->update("almacen.inventarios",$data);
			}
			echo $estado;
		}
	}

	function cerrar_inventario(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			
			// VALIDAMOS SERIES DE LOS COMPROBANTES DE ALMACEN //
			$inventario = $this->db->query("select *from almacen.inventarios where codinventario=".$this->request->codregistro)->result_array();

			$ingreso_almacen_serie = $this->db->query("select seriecomprobante from caja.comprobantes where codcomprobantetipo=3 and codsucursal=".$inventario[0]["codsucursal"]." and codalmacen=".$inventario[0]["codalmacen"]." and estado=1")->result_array();
			$salida_almacen_serie = $this->db->query("select seriecomprobante from caja.comprobantes where codcomprobantetipo=4 and codsucursal=".$inventario[0]["codsucursal"]." and codalmacen=".$inventario[0]["codalmacen"]." and estado=1")->result_array();

			if(count($ingreso_almacen_serie)==0 or count($salida_almacen_serie)==0){
				$estado = 0; exit();
			}

			$this->db->trans_begin();

			// DATOS DEL INVENTARIO //
			$productos = $this->db->query("select *from almacen.inventariodetalle where codinventario=".$this->request->codregistro." order by codproducto desc")->result_array();
			$importe = $this->db->query("select sum(importe) as importe from almacen.inventariodetalle where codinventario=".$this->request->codregistro)->result_array();

			// REGITRAMOS EL REGISTRO KARDEX DE INGRESO DEL INVENTARIO //
			$campos = ["codsucursal","codalmacen","codpersona","codusuario","codmovimientotipo","fechakardex","fechacomprobante","codcomprobantetipo","seriecomprobante"];
			$valores = [(int)$inventario[0]["codsucursal"],(int)$inventario[0]["codalmacen"],1,(int)$_SESSION["phuyu_codusuario"],17,date("Y-m-d"),date("Y-m-d"),3,$ingreso_almacen_serie[0]["seriecomprobante"]];
			$codkardex_ingreso = $this->phuyu_model->phuyu_guardar("kardex.kardex", $campos, $valores, "true");

			$campos = ["codsucursal","codalmacen","codkardex","codusuario","codmovimientotipo","fechakardex","codcomprobantetipo","seriecomprobante"];
			$valores = [(int)$inventario[0]["codsucursal"],(int)$inventario[0]["codalmacen"],$codkardex_ingreso,(int)$_SESSION["phuyu_codusuario"],17,date("Y-m-d"),3,$ingreso_almacen_serie[0]["seriecomprobante"]];
			$codkardexalmacen_ingreso = $this->phuyu_model->phuyu_guardar("kardex.kardexalmacen", $campos, $valores, "true");

			$estado = $this->Kardex_model->phuyu_kardexcorrelativo($codkardex_ingreso,$codkardexalmacen_ingreso,3,$ingreso_almacen_serie[0]["seriecomprobante"]);

			// REGITRAMOS EL REGISTRO KARDEX DE SALIDA DEL INVENTARIO //
			$campos = ["codsucursal","codalmacen","codpersona","codusuario","codmovimientotipo","fechakardex","fechacomprobante","codcomprobantetipo","seriecomprobante"];
			$valores = [(int)$inventario[0]["codsucursal"],(int)$inventario[0]["codalmacen"],1,(int)$_SESSION["phuyu_codusuario"],36,date("Y-m-d"),date("Y-m-d"),4,$salida_almacen_serie[0]["seriecomprobante"],(double)$inventario[0]["importe"],(double)$inventario[0]["importe"]];
			$codkardex_salida = $this->phuyu_model->phuyu_guardar("kardex.kardex", $campos, $valores, "true");

			$campos = ["codsucursal","codalmacen","codkardex","codusuario","codmovimientotipo","fechakardex","codcomprobantetipo","seriecomprobante"];
			$valores = [(int)$inventario[0]["codsucursal"],(int)$inventario[0]["codalmacen"],$codkardex_salida,(int)$_SESSION["phuyu_codusuario"],36,date("Y-m-d"),4,$salida_almacen_serie[0]["seriecomprobante"]];
			$codkardexalmacen_salida = $this->phuyu_model->phuyu_guardar("kardex.kardexalmacen", $campos, $valores, "true");

			$estado = $this->Kardex_model->phuyu_kardexcorrelativo($codkardex_salida,$codkardexalmacen_salida,4,$salida_almacen_serie[0]["seriecomprobante"]);

			// KARDEX DETALLE //
			$importe_ingreso = 0; $importe_salida = 0; $item_ingreso = 0; $item_salida = 0; $idproducto = 0;
			foreach ($productos as $key => $value) {
				$subtotal = round(($value["cantidad"] * $value["precioventa"]),4); $codkardex = 0; $codkardexalmacen = 0; $item = 0;
				$stock = $this->db->query("select stockactual from almacen.productoubicacion where codalmacen=".$inventario[0]["codalmacen"]." and codproducto=".$value["codproducto"]." and codunidad=".$value["codunidad"])->result_array();

				if (count($stock)>0) {
					$validar = round((double)$value["cantidad"] - (double)$stock[0]["stockactual"],2);
					if ($validar!=0) {
						if ($validar>0) {
							$item_ingreso = $item_ingreso + 1; $importe_ingreso = $importe_ingreso + $subtotal;
							$codkardex = $codkardex_ingreso; $codkardexalmacen = $codkardexalmacen_ingreso; $item = $item_ingreso;
						}else{
							$item_salida = $item_salida + 1; $importe_salida = $importe_salida + $subtotal;
							$codkardex = $codkardex_salida; $codkardexalmacen = $codkardexalmacen_salida; $item = $item_salida;
						}
					}

					if ($validar>=0) {
						$cantidad_mover = $validar;
					}else{
						$cantidad_mover = $validar * (-1);
					}
				}else{
					if ($value["cantidad"]>0) {
						$item_ingreso = $item_ingreso + 1; $importe_ingreso = $importe_ingreso + $subtotal;
						$codkardex = $codkardex_ingreso; $codkardexalmacen = $codkardexalmacen_ingreso; $item = $item_ingreso;
					}
					$cantidad_mover = $value["cantidad"];
				}

				if ($codkardex>0) {
					$campos = ["codkardex","codproducto","codunidad","item","cantidad","preciobruto","preciosinigv","preciounitario","preciorefunitario","valorventa","subtotal","recoger"];					
					$valores =[
						(int)$codkardex,(int)$value["codproducto"],(int)$value["codunidad"],$item,(double)$cantidad_mover,
						(double)$value["precioventa"],(double)$value["precioventa"],(double)$value["precioventa"],(double)$value["precioventa"],
						(double)$subtotal,(double)$subtotal,1
					];
					$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexdetalle", $campos, $valores);

					// PRODUCTOS UNIDADES //
					$campos = ["preciocompra","pventapublico"]; $valores = [$value["preciocosto"],$value["precioventa"]];
					$f = ["codproducto","codunidad"]; $v = [(int)$value["codproducto"],(int)$value["codunidad"]];
					$estado = $this->phuyu_model->phuyu_editar_1("almacen.productounidades", $campos, $valores, $f, $v);

					// PRODUCTOS UBICACION //
					$ubicacion = $this->db->query("select *from almacen.productoubicacion where codalmacen=".$inventario[0]["codalmacen"]." and codproducto=".$value["codproducto"]." and codunidad=".$value["codunidad"])->result_array();

					$campos = ["codalmacen","codproducto","codunidad","codsucursal","stockactual","stockactualreal"];
					$valores =[(int)$inventario[0]["codalmacen"],(int)$value["codproducto"],(int)$value["codunidad"],(int)$inventario[0]["codsucursal"],(double)$value["cantidad"],(double)$value["cantidad"]];
					if (count($ubicacion)>0) {
						$f = ["codalmacen","codproducto","codunidad"]; 
						$v = [(int)$inventario[0]["codalmacen"],(int)$value["codproducto"],(int)$value["codunidad"]];
						$estado = $this->phuyu_model->phuyu_editar_1("almacen.productoubicacion", $campos, $valores, $f, $v);
					}else{
						$estado = $this->phuyu_model->phuyu_guardar("almacen.productoubicacion", $campos, $valores);
					}

					$stockconvertido = $this->db->query("select *from almacen.productoubicacion where codalmacen=".$inventario[0]["codalmacen"]." and codproducto=".$value["codproducto"])->result_array();

					$factor = $this->db->query("select *from almacen.productounidades where codproducto=".$value["codproducto"]." and codunidad=".$value["codunidad"])->result_array();

					foreach ($stockconvertido as $k => $val) {
						$stockc = 0;
						$productounidad = $this->db->query("select *from almacen.productounidades where codproducto=".$value["codproducto"]." and codunidad=".$val["codunidad"])->result_array();
						$stockc = ((double)$value["cantidad"]*(double)$factor[0]["factor"])/(double)$productounidad[0]["factor"];

						if($idproducto==$value["codproducto"]){
							$stockc = (double)$val["stockactualconvertido"] + $stockc;
						}else{
							$stockc = $stockc;
						}
						
						$campos = ["stockactualconvertido"]; $valores = [(double)$stockc];
						$f = ["codalmacen","codproducto","codunidad"]; 
						$v = [(int)$inventario[0]["codalmacen"],(int)$value["codproducto"],(int)$val["codunidad"]];
						$estado = $this->phuyu_model->phuyu_editar_1("almacen.productoubicacion", $campos, $valores, $f, $v);
					}

					// KARDEX DETALLE ALMACEN //
					$campos = ["codkardexalmacen","codproducto","codunidad","item","codalmacen","codsucursal","cantidad"];
					$valores =[
						(int)$codkardexalmacen,(int)$value["codproducto"],(int)$value["codunidad"],$item,(int)$inventario[0]["codalmacen"],
						(int)$inventario[0]["codsucursal"],(double)$cantidad_mover
					];
					$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexalmacendetalle", $campos, $valores);
				}
				$idproducto = $value["codproducto"];
			}

			$campos = ["valorventa","importe"]; $valores = [(double)round($importe_ingreso,2),(double)round($importe_ingreso,2)];
			$estado = $this->phuyu_model->phuyu_editar("kardex.kardex", $campos, $valores, "codkardex", $codkardex_ingreso);

			$campos = ["valorventa","importe"]; $valores = [(double)round($importe_salida,2),(double)round($importe_salida,2)];
			$estado = $this->phuyu_model->phuyu_editar("kardex.kardex", $campos, $valores, "codkardex", $codkardex_salida);

			// CERRAMOS EL INVENTARIO //
			$campos = ["fechacierre","importe","codkardex_ingreso","codkardex_salida","estado"]; 
			$valores = [date("Y-m-d"),$importe[0]["importe"],$codkardex_ingreso,$codkardex_salida,0];
			$estado = $this->phuyu_model->phuyu_editar("almacen.inventarios", $campos, $valores, "codinventario", $this->request->codregistro);

			if ($this->db->trans_status() === FALSE){
			    $this->db->trans_rollback(); $estado = 0;
			}else{
				$this->db->trans_commit(); $estado = 1;
			}

			echo $estado;
		}
	}

	function guardar_archivo($codinventario){
		if ($this->input->is_ajax_request()) {
			$archivo = $this->input->post('archivo');
            $this->db->trans_begin();
			//SUBIMOS EL ARCHIVO CSV
			if ($_FILES["archivo"]["name"]!="") {
				$file = substr($_FILES["archivo"]["name"],-5);
				move_uploaded_file($_FILES["archivo"]["tmp_name"],"./public/archivos/inventario/".$file);
				chmod("./public/archivos/inventario/".$file, 0777);
			}else{
				echo 10;exit;
			}
            $path = "./public/archivos/inventario/".$file;
            $lineas = file($path);
            $arreglo = array_map('utf8_decode', $lineas);

            $modificado = array_map(function($v){return str_getcsv($v, ";");}, $arreglo); $item = 1;
            $total_importecosto = 0; $total_importe = 0; $numpro = 1;
            for($i = 1; $i < sizeof($modificado); $i++){

            	$codproducto = ($modificado[$i][0]=="") ? 0 : $modificado[$i][0];
				$familia = trim($modificado[$i][1]);
				$linea = trim($modificado[$i][2]);
                $marca = trim($modificado[$i][3]);
				$descripcion_producto = utf8_encode($modificado[$i][4]);
				$unidad = trim($modificado[$i][5]);
				$stock = ($modificado[$i][6]=="" || empty($modificado[$i][6])) ? 0 : $modificado[$i][6];
				$preciocompra = ($modificado[$i][7]=="" || empty($modificado[$i][7])) ? 0 : $modificado[$i][7];
				$preciocosto = ($modificado[$i][7]=="" || empty($modificado[$i][7])) ? 0 : $modificado[$i][7];
				$precioventa = ($modificado[$i][8]=="" || empty($modificado[$i][8])) ? 0 : $modificado[$i][8];

				$existeunidad = $this->db->query("select *from almacen.unidades where descripcion='".$unidad."' and estado=1")->result_array();

				$codunidad = $existeunidad[0]["codunidad"];

				if($codproducto==0){
					//GUARDAMOS LA FAMILIA

					$existe = $this->db->query("select *from almacen.familias where descripcion='".$familia."' and estado=1")->result_array();

					if(count($existe)==0){
	                    $campos = ["descripcion","estado"];
						$valores = [$familia,1];

						$codfamilia = $this->phuyu_model->phuyu_guardar("almacen.familias", $campos, $valores, "true");
					}else{
						$codfamilia = $existe[0]["codfamilia"];
					}

					//GUARDAMOS LA LINEA

					$existe = $this->db->query("select *from almacen.lineas where descripcion='".$linea."' and estado=1")->result_array();

					if(count($existe)==0){
	                    $campos = ["descripcion","estado"];
						$valores = [$linea,1];

						$codlinea = $this->phuyu_model->phuyu_guardar("almacen.lineas", $campos, $valores, "true");
					}else{
						$codlinea = $existe[0]["codlinea"];
					}

					//GUARDAMOS LA MARCA

					$existe = $this->db->query("select *from almacen.marcas where descripcion='".$marca."' and estado=1")->result_array();

					if(count($existe)==0){
	                    $campos = ["descripcion","estado"];
						$valores = [$marca,1];

						$codmarca = $this->phuyu_model->phuyu_guardar("almacen.marcas", $campos, $valores, "true");
					}else{
						$codmarca = $existe[0]["codmarca"];
					}

					// GUARDAMOS LOS PRODUCTOS

					$existe = $this->db->query("select *from almacen.productos where descripcion='".$descripcion_producto."' and estado=1")->result_array();

					if(count($existe)>0){
						$numpro++;
	                    $descripcion_producto = $descripcion_producto.' '.$numpro;
					}

					$campos = ["codfamilia","codlinea","codmarca","codempresa","codigo","descripcion","paraventa","controlstock","afectoigvcompra","afectoigvventa","caracteristicas"];
					$valores = [
						$codfamilia,$codlinea,$codmarca,(int)$_SESSION["phuyu_codempresa"],
						"",$descripcion_producto,1,1,1,0,""
					];

					$campos_1 = ["codproducto","codunidad","codsucursal","factor","preciocompra","preciocosto","pventapublico","estado"];


					$codproducto = $this->phuyu_model->phuyu_guardar("almacen.productos", $campos, $valores, "true");
					
					$data = array( "codigo" => "000".$codproducto);
					$this->db->where("codproducto", $codproducto);
					$estado = $this->db->update("almacen.productos", $data);

					$valores_1 = [
						(int)$codproducto,$codunidad,(int)$_SESSION["phuyu_codsucursal"],
						1,(double)$preciocompra,
						(double)$preciocompra,
						(double)$precioventa,1
					];
					$estado = $this->phuyu_model->phuyu_guardar("almacen.productounidades", $campos_1, $valores_1);

					// REGISTRO EN LA TABLA PRODUCTOS UBICACION //

					$campos = ["codalmacen","codproducto","codunidad","codsucursal"];
					$valores =[
						(int)$_SESSION["phuyu_codalmacen"],
						(int)$codproducto,$codunidad,
						(int)$_SESSION["phuyu_codsucursal"]
					];
					$estado = $this->phuyu_model->phuyu_guardar("almacen.productoubicacion", $campos, $valores);
				}

                // REGISTRO DEL DETALLE DEL INVENTARIO

				$campos = ["codinventario","codproducto","codunidad","cantidad","preciocosto","precioventa","importecosto","importe"];
				$importecosto = (int)$stock * (double)$preciocosto;
				$importe = (int)$stock * (double)$precioventa;

				$total_importecosto = $total_importecosto + $importecosto;
				$total_importe = $total_importe + $importe;

				$valores = [$codinventario,$codproducto,$codunidad,(int)$stock,(double)$preciocosto,(double)$precioventa,(double)$importecosto,(double)$importe];

				$estado = $this->phuyu_model->phuyu_guardar("almacen.inventariodetalle", $campos, $valores);
				
				if(sizeof($modificado) == $item){
					$campos = ["importecosto","importe"]; $valores = [$total_importecosto,$total_importe];
					$estado = $this->phuyu_model->phuyu_editar("almacen.inventarios", $campos, $valores, "codinventario", $codinventario);
				}
            	$item++;
            }

                $this->db->trans_commit();
                echo 1;
		}
	}

	function phuyu_pdf($codinventario,$tiporeporte){
		if (isset($codinventario)) {

			$this->load->library('Pdf2'); $pdf = new Pdf2(); $pdf->AddPage();
			$pdf->pdf_header("INVENTARIO REALIZADO","");

			$info = $this->db->query("select *from almacen.inventarios where codinventario=".$codinventario)->result_array();

			$pdf->SetFont('Arial','B',10);
		    $pdf->setFillColor(245,245,245);
			$pdf->Cell(0,7,$info[0]["descripcion"]." (FECHA APERTURA:".$info[0]["fechaapertura"]." FECHA CIERRE:".$info[0]["fechacierre"].")",0,1,'L',1); $pdf->Ln(2);

			if ($tiporeporte==0) {
				$filtro = "";
			}elseif ($tiporeporte==1) {
				$filtro = "and id.cantidad>0";
			}else{
				$filtro = "and id.cantidad=0";
			}

			$lista = $this->db->query("select p.codproducto,p.codigo,p.descripcion,u.codunidad,u.descripcion as unidad, m.descripcion as marca, round(id.cantidad,3) as cantidad, round(id.preciocosto,2) as preciocosto,id.precioventa as precioventa, (id.cantidad * id.preciocosto) as importe from almacen.productos as p inner join almacen.inventariodetalle as id on(p.codproducto=id.codproducto) inner join almacen.unidades as u on(u.codunidad=id.codunidad) inner join almacen.marcas as m on(p.codmarca=m.codmarca) where id.codinventario=".$codinventario." ".$filtro." order by p.descripcion asc")->result_array();

			$columnas = array("N°","MARCA","PRODUCTO","UNIDAD","CANT.","P.COSTO","P.VENTA","IMPORTE");
			$w = array(10,20,70,20,15,18,18,18); $pdf->pdf_tabla_head($columnas,$w,9);

			$pdf->SetWidths(array(10,20,70,20,15,18,18,18));
            $pdf->SetLineHeight(5);
			$pdf->SetFont('Arial','',7);

			$item = 0; $total = 0;$cantidad = 0;
			foreach ($lista as $key => $value) { 
				$item = $item + 1; $cantidad = $cantidad + $value["cantidad"]; $total = $total + $value["importe"];

				$datos = array("0".$item);
				array_push($datos,$value["marca"]);
				array_push($datos,utf8_decode($value["descripcion"]));
				array_push($datos,utf8_decode($value["unidad"]));

				array_push($datos,number_format($value["cantidad"],2));
				array_push($datos,number_format($value["preciocosto"],2));
				array_push($datos,number_format($value["precioventa"],2));
				array_push($datos,number_format($value["importe"],2));
                $pdf->Row($datos);
			}
			$pdf->Cell(array_sum($w),0,'','T'); $pdf->Ln();

			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(120,5,"TOTALES",1,0,'R');
		    $pdf->Cell(51,5,number_format($cantidad,2),1,"R");
		    $pdf->Cell(18,5,number_format($total,2),1,"R");

			$pdf->SetTitle("phuyu Peru - Inventario"); $pdf->Output();
		}
	}

	function phuyu_excel($codinventario,$tiporeporte){
		if (isset($codinventario)) {
			if ($tiporeporte==0) {
				$filtro = "";
			}elseif ($tiporeporte==1) {
				$filtro = "and id.cantidad>0";
			}else{
				$filtro = "and id.cantidad=0";
			}

			$lista = $this->db->query("select p.codproducto,p.codigo,p.descripcion,u.codunidad,u.descripcion as unidad,m.descripcion as marca,round(id.cantidad,3) as cantidad, round(id.preciocosto,2) as preciocosto,id.precioventa as precioventa, (id.cantidad * id.preciocosto) as importe from almacen.productos as p inner join almacen.inventariodetalle as id on(p.codproducto=id.codproducto) inner join almacen.unidades as u on(u.codunidad=id.codunidad) inner join almacen.marcas as m on(p.codmarca=m.codmarca) where id.codinventario=".$codinventario." ".$filtro." order by p.descripcion asc")->result_array();
			
			$this->load->view("almacen/inventarios/excel",compact("lista"));
		}
	}
}