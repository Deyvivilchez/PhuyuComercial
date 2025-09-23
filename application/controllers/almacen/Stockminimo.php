<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Stockminimo extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$unidades = $this->db->query("select *from almacen.unidades where estado=1")->result_array();
				$this->load->view("almacen/productos/minimos",compact("unidades"));
				
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function lista(){
		if ($this->input->is_ajax_request()) {
			$lista = $this->db->query("select p.codproducto,p.codigo,p.descripcion,u.codunidad,u.descripcion as unidad,round(pu.stockactualconvertido) as stock,pu.stockminimo,pu.stockmaximo,m.descripcion as marca from almacen.productos as p inner join almacen.productoubicacion as pu on(p.codproducto=pu.codproducto) inner join almacen.unidades as u on(u.codunidad=pu.codunidad) inner join almacen.marcas as m on(p.codmarca=m.codmarca) where p.estado=1 and pu.estado=1 and pu.codalmacen=".$_SESSION["phuyu_codalmacen"]." order by pu.stockactual desc")->result_array();

			$costo = 0; $t_costo = 0; $venta = 0; $t_venta = 0; $minimo = 0; $t_minimo = 0; $item = 0;
			foreach ($lista as $key => $value) { $item = $item + 1;
				$precio = $this->db->query("select factor,pventapublico,pventamin,pventacredito,pventaxmayor,preciocosto from almacen.productounidades where codproducto=".$value["codproducto"]." and codunidad=".$value["codunidad"]." and estado=1")->result_array();
				$lista[$key]["nro"] = $item;
				if (count($precio)==0) {
					$lista[$key]["factor"] = 0;
					$lista[$key]["precioventa"] = 0.00; $lista[$key]["preciomin"] = 0.00; $lista[$key]["preciocosto"] = 0.00;

					$lista[$key]["costo"] = 0; $lista[$key]["venta"] = 0; $lista[$key]["minimo"] = 0;
				}else{
					$lista[$key]["factor"] = round($precio[0]["factor"]);
					$lista[$key]["precioventa"] = number_format($precio[0]["pventapublico"],2);
					$lista[$key]["preciomin"] = number_format($precio[0]["pventamin"],2);
					$lista[$key]["preciocosto"] = number_format($precio[0]["preciocosto"],2);

					$lista[$key]["venta"] = number_format($value["stock"] * $precio[0]["pventapublico"],2); 
					$lista[$key]["minimo"] = number_format($value["stock"] * $precio[0]["pventamin"],2); 
					$lista[$key]["costo"] = number_format($value["stock"] * $precio[0]["preciocosto"],2);

					$t_venta = $t_venta + ($value["stock"] * $precio[0]["pventapublico"]);
					$t_minimo = $t_minimo + ($value["stock"] * $precio[0]["pventamin"]);
					$t_costo = $t_costo + ($value["stock"] * $precio[0]["preciocosto"]);
				}
			}

			$data["lista"] = $lista;
			echo json_encode($data);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function guardar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			$this->db->trans_begin();
			$campos = ["stockminimo"];
			if (isset($this->request->productos)) {
				foreach ($this->request->productos as $key => $value) {
					$valores = [$this->request->productos[$key]->stockminimo];

					$f = ["codproducto","codunidad"]; 
					$v = [$this->request->productos[$key]->codproducto,$this->request->productos[$key]->codunidad];
					$estado = $this->phuyu_model->phuyu_editar_1("almacen.productoubicacion", $campos, $valores, $f, $v);
				}
			}
			if ($this->db->trans_status() === FALSE){
			    $this->db->trans_rollback(); $estado = 0;
			}else{
				$this->db->trans_commit(); $estado = 1;
			}
			echo $estado;
		}
	}

	function productos_almacen(){
		if ($this->input->is_ajax_request()) {
			$productos = $this->db->query("select codproducto,codunidad from almacen.productounidades where estado=1")->result_array();
			foreach ($productos as $key => $value) {
				$almacenes = $this->db->query("select codalmacen,codsucursal from almacen.almacenes where estado=1")->result_array();
				foreach ($almacenes as $val) {
					$existe = $this->db->query("select *from almacen.productoubicacion where codproducto=".$value["codproducto"]." and codunidad=".$value["codunidad"]." and codalmacen=".$val["codalmacen"])->result_array();
					if (count($existe)==0) {
						$campos = ["codalmacen","codproducto","codunidad","codsucursal"];
						$valores =[$val["codalmacen"],$value["codproducto"],$value["codunidad"],$val["codsucursal"]];
						$estado = $this->phuyu_model->phuyu_guardar("almacen.productoubicacion", $campos, $valores);
					}
				}
			}
		}
	}

	function actualizar_stock(){
		if ($this->input->is_ajax_request()) {
			$estado = $this->db->query("UPDATE almacen.productoubicacion
				SET stockactual = coalesce((SELECT sum(( CASE WHEN  codmovimientotipo < 20 THEN 1 ELSE -1 END) * kardex.kardexdetalle.cantidad) FROM kardex.kardexdetalle INNER JOIN kardex.kardex ON (kardex.kardexdetalle.codkardex = kardex.kardex.codkardex) WHERE almacen.productoubicacion.codalmacen = kardex.kardex.codalmacen AND almacen.productoubicacion.codproducto = kardex.kardexdetalle.codproducto AND almacen.productoubicacion.codunidad = kardex.kardexdetalle.codunidad AND kardex.kardex.estado = 1 ),0) WHERE stockactual <>  coalesce((SELECT sum(( CASE WHEN  codmovimientotipo < 20 THEN 1 ELSE -1 END) * kardex.kardexdetalle.cantidad) FROM kardex.kardexdetalle INNER JOIN kardex.kardex ON (kardex.kardexdetalle.codkardex = kardex.kardex.codkardex) WHERE almacen.productoubicacion.codalmacen = kardex.kardex.codalmacen AND almacen.productoubicacion.codproducto = kardex.kardexdetalle.codproducto AND almacen.productoubicacion.codunidad = kardex.kardexdetalle.codunidad AND kardex.kardex.estado = 1 ), 0)");
			echo $estado;
		}
	}


	function migrar_productos(){
		$temporal = $this->db->query("select * from public.productos")->result_array();
		foreach ($temporal as $key => $value) {
			$linea = [];// $this->db->query("select *from almacen.lineas where descripcion='".$value["linea"]."'")->result_array();
			if (count($linea)==0) {
				/* $campos = ["descripcion"]; $valores = [$value["linea"]];
				$estado = $this->phuyu_model->phuyu_guardar("almacen.lineas", $campos, $valores);
				$codlinea = $this->db->insert_id("almacen.lineas_codlinea_seq"); */
			}else{
				$codlinea = $linea[0]["codlinea"];
			}
			$codlinea = 1;

			$campos = ["codfamilia","codlinea","codmarca","codempresa","codigo","descripcion","afectoicbper","codatencion","paraventa","calcular","controlstock","afectoigvcompra","afectoigvventa"];
			$valores = [
				(int)1,(int)$codlinea,(int)1,(int)1, $value["codigo"],
				strtoupper($value["descripcion"]),0,0,1,0,1,0,0
			];
			$estado = $this->phuyu_model->phuyu_guardar("almacen.productos", $campos, $valores, "true");
			$codproducto = $this->db->insert_id("almacen.productos_codproducto_seq");

			$campos_1 = ["codproducto","codunidad","codsucursal","factor","preciocompra","preciocosto","pventapublico","pventamin","pventacredito","pventaxmayor","pventaadicional"];
			$valores_1 = [
				(int)$codproducto,(int)17,1,1,
				(double)$value["precioventa"],
				(double)$value["precioventa"],
				(double)$value["precioventa"],
				(double)$value["precioventa"],
				(double)$value["preciocredito"],
				(double)$value["preciomayor"],
				(double)$value["preciomayor"]
			];
			$estado = $this->phuyu_model->phuyu_guardar("almacen.productounidades", $campos_1, $valores_1);
			
			echo $codproducto."<br>";
		}
	}

	function migrar_clientes(){
		$temporal = $this->db->query("select * from public.temporal_clientes")->result_array();
		$item = 0;
		foreach ($temporal as $key => $value) { $item = $item + 1;

			$ubigeo = $this->db->query("select *from public.ubigeo where distrito='".strtoupper($value["ciudad"])."'")->result_array();
			if (count($ubigeo)>0) {
				$codubigeo = $ubigeo[0]["codubigeo"];
				// echo "SI EXISTE ".$value["ciudad"]." POR ".$ubigeo[0]["distrito"]." <br>";
			}else{
				// echo $value["idtemporal"]." NO EXISTE ".$value["ciudad"]." <br>";
				$codubigeo = 0;
			}
			$documento = "0000000".$item;

			$campos = ["coddocumentotipo","documento","razonsocial","nombrecomercial","direccion","codubigeo","estado"];
			$valores = [1,$documento,strtoupper($value["razonsocial"]),strtoupper($value["razonsocial"]),strtoupper($value["ciudad"]),$codubigeo,1];
			$estado = $this->phuyu_model->phuyu_guardar("public.personas", $campos, $valores);
			$codpersona = $this->db->insert_id("personas_codpersona_seq");

			$campos_1 = ["codpersona","codsociotipo","usuario","clave"];
			$valores_1 = [$codpersona,1,$documento,$documento];
			$estado = $this->phuyu_model->phuyu_guardar("public.socios", $campos_1, $valores_1);
		}
		echo $codpersona."<br>";
	}
}