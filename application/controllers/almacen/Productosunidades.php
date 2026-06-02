<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Productosunidades extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$unidades = $this->db->query("select *from almacen.unidades where estado=1")->result_array();
				if($_SESSION["phuyu_rubro"]==4){
					$this->load->view("almacen/productos/unidades_perfumeria",compact("unidades"));
				}else{
					$this->load->view("almacen/productos/unidades",compact("unidades"));
				}
				
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function lista(){
		if ($this->input->is_ajax_request()) {
			$lista = $this->db->query("select p.codproducto,p.codigo,p.descripcion,u.codunidad,u.descripcion as unidad,round(pu.stockactualconvertido,2) as stock,m.descripcion as marca from almacen.productos as p inner join almacen.productoubicacion as pu on(p.codproducto=pu.codproducto) inner join almacen.unidades as u on(u.codunidad=pu.codunidad) inner join almacen.marcas as m on(p.codmarca=m.codmarca) where p.estado=1 and pu.estado=1 and pu.codalmacen=".$_SESSION["phuyu_codalmacen"]." order by pu.stockactual desc")->result_array();

			$costo = 0; $t_costo = 0; $venta = 0; $t_venta = 0; $mayor = 0; $t_mayor = 0; $t_credito=0; $item = 0; $minimox = 0;
			foreach ($lista as $key => $value) { $item = $item + 1;
				$precio = $this->db->query("select factor,pventapublico,pventamin,pventacredito,pventaxmayor,preciocosto,pventaadicional from almacen.productoubicacion where codproducto=".$value["codproducto"]." and codunidad=".$value["codunidad"]." and codalmacen = ".$_SESSION["phuyu_codalmacen"]." and estado=1")->result_array();
				$lista[$key]["stock"] = ($lista[$key]["stock"]=="" || $lista[$key]["stock"]==null) ? 0 : $lista[$key]["stock"];
				$lista[$key]["nro"] = $item;
				if (count($precio)==0) {
					$lista[$key]["factor"] = 0;
					$lista[$key]["precioventa"] = 0.00; $lista[$key]["precioxmayor"] = 0.00; $lista[$key]["preciocosto"] = 0.00; $lista[$key]["pventacredito"] = 0.00; $lista[$key]["preciomin"] = 0.00;

					$lista[$key]["costo"] = 0; $lista[$key]["venta"] = 0; $lista[$key]["mayor"] = 0; $lista[$key]["credito"] = 0; $lista[$key]["minimo"] = 0;
				}else{
					$lista[$key]["factor"] = round($precio[0]["factor"],2);
					$lista[$key]["precioventa"] = number_format($precio[0]["pventapublico"],2);
					$lista[$key]["precioxmayor"] = number_format($precio[0]["pventaxmayor"],2);
					$lista[$key]["preciocosto"] = number_format($precio[0]["preciocosto"],2);
					$lista[$key]["pventacredito"] = number_format($precio[0]["pventacredito"],2);
					$lista[$key]["preciomin"] = number_format($precio[0]["pventamin"],2);
					$lista[$key]["venta"] = number_format($value["stock"] * $precio[0]["pventapublico"],2); 
					$lista[$key]["mayor"] = number_format($value["stock"] * $precio[0]["pventaxmayor"],2); 
					$lista[$key]["costo"] = number_format($value["stock"] * $precio[0]["preciocosto"],2);
					$lista[$key]["credito"] = number_format($value["stock"] * $precio[0]["pventacredito"],2);
					$lista[$key]["minimo"] = number_format($value["stock"] * $precio[0]["pventamin"],2);
					$t_venta = $t_venta + ($value["stock"] * $precio[0]["pventapublico"]);
					$t_mayor = $t_mayor + ($value["stock"] * $precio[0]["pventaxmayor"]);
					$t_costo = $t_costo + ($value["stock"] * $precio[0]["preciocosto"]);
					$t_credito = $t_credito + ($value["stock"] * $precio[0]["pventacredito"]);
					$minimox = $minimox + ($value["stock"] * $precio[0]["pventamin"]);
				}
			}
			$total = $t_venta - $t_costo;
			$totales = $this->db->query("select ".number_format($t_venta,2,".","")." as venta, ".number_format($t_mayor,2,".","")." as mayor, ".number_format($t_costo,2,".","")." as costo, ".number_format($t_credito,2,".","")." as credito,".number_format($minimox,2,".","")." as minimox, ".number_format($total,2,".","")." as total")->result_array();

			$data["lista"] = $lista;
			$data["totales"] = $totales;
			echo json_encode($data);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function guardar_cambiar_unidad(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			$this->db->trans_begin();

			// REGISTRO EN PRODUCTOS UNIDADES Y PRODUCTOS UBICACION //

			$unidades = $this->db->query("select *from almacen.productounidades where codproducto=".$this->request->codproducto." and codunidad=".$this->request->codunidad)->result_array();
			$campos = ["codproducto","codunidad","codsucursal","factor","preciocompra","pventapublico","pventamin","pventacredito","pventaxmayor","pventaadicional","preciocosto","gastos","estado"];
			$valores =[(int)$this->request->codproducto,$this->request->codunidad_nueva,$_SESSION["phuyu_codsucursal"],$unidades[0]["factor"],$unidades[0]["preciocompra"],$unidades[0]["pventapublico"],$unidades[0]["pventamin"],$unidades[0]["pventacredito"],$unidades[0]["pventaxmayor"],$unidades[0]["pventaadicional"],$unidades[0]["preciocosto"],$unidades[0]["gastos"],1];

			$exiteunidades = $this->db->query("select *from almacen.productounidades where codproducto=".$this->request->codproducto." AND codunidad=".$this->request->codunidad_nueva)->result_array();

			if(count($exiteunidades)>0){
				$campos = ["preciocompra","pventapublico","pventamin","pventacredito","pventaxmayor","pventaadicional","preciocosto","gastos","estado"];
				$valores =[$unidades[0]["preciocompra"],$unidades[0]["pventapublico"],$unidades[0]["pventamin"],$unidades[0]["pventacredito"],$unidades[0]["pventaxmayor"],$unidades[0]["pventaadicional"],$unidades[0]["preciocosto"],$unidades[0]["gastos"],1];

				$f = ["codproducto","codunidad"]; $v = [$this->request->codproducto,$this->request->codunidad_nueva];
				$estado = $this->phuyu_model->phuyu_editar_1("almacen.productounidades",$campos,$valores,$f,$v);
			}else{
				$estado = $this->phuyu_model->phuyu_guardar("almacen.productounidades", $campos, $valores);
			}

			$almacenes = $this->db->query("select codalmacen,codsucursal from almacen.productoubicacion where codproducto=".$this->request->codproducto." and codunidad=".$this->request->codunidad)->result_array();
			foreach ($almacenes as $key => $value) {
				$ubicacion = $this->db->query("select *from almacen.productoubicacion where codalmacen=".$value["codalmacen"]." and codproducto=".$this->request->codproducto." and codunidad=".$this->request->codunidad)->result_array();

				$existeubicacion = $this->db->query("select *from almacen.productoubicacion where codalmacen=".$value["codalmacen"]." and codproducto=".$this->request->codproducto." and codunidad=".$this->request->codunidad_nueva)->result_array();

				$campos = ["codalmacen","codproducto","codunidad","codsucursal","stockactual","stockactualreal","preciostockvalorizado","ventarecogo","comprarecogo","ventarecogoconvertido","comprarecogoconvertido","stockminimo","stockmaximo","stockactualconvertido","factor","preciocompra","pventapublico","pventamin","pventacredito","pventaxmayor","pventaadicional","preciocosto","gastos","codigobarra","estado"];
				$valores =[$value["codalmacen"],$ubicacion[0]["codproducto"],$this->request->codunidad_nueva,$value["codsucursal"],$ubicacion[0]["stockactual"],$ubicacion[0]["stockactualreal"],$ubicacion[0]["preciostockvalorizado"],$ubicacion[0]["ventarecogo"],$ubicacion[0]["comprarecogo"],$ubicacion[0]["ventarecogoconvertido"],$ubicacion[0]["comprarecogoconvertido"],$ubicacion[0]["stockminimo"],$ubicacion[0]["stockmaximo"],$ubicacion[0]["stockactualconvertido"],$ubicacion[0]["factor"],$ubicacion[0]["preciocompra"],$ubicacion[0]["pventapublico"],$ubicacion[0]["pventamin"],$ubicacion[0]["pventacredito"],$ubicacion[0]["pventaxmayor"],$ubicacion[0]["pventaadicional"],$ubicacion[0]["preciocosto"],$ubicacion[0]["gastos"],$ubicacion[0]["codigobarra"],1];

				if(count($existeubicacion)>0){
					$campos = ["stockactual","stockactualreal","preciostockvalorizado","ventarecogo","comprarecogo","ventarecogoconvertido","comprarecogoconvertido","stockminimo","stockmaximo","stockactualconvertido","factor","preciocompra","pventapublico","pventamin","pventacredito","pventaxmayor","pventaadicional","preciocosto","gastos","codigobarra","estado"];

					$valores =[$ubicacion[0]["stockactual"],$ubicacion[0]["stockactualreal"],$ubicacion[0]["preciostockvalorizado"],$ubicacion[0]["ventarecogo"],$ubicacion[0]["comprarecogo"],$ubicacion[0]["ventarecogoconvertido"],$ubicacion[0]["comprarecogoconvertido"],$ubicacion[0]["stockminimo"],$ubicacion[0]["stockmaximo"],$ubicacion[0]["stockactualconvertido"],$ubicacion[0]["factor"],$ubicacion[0]["preciocompra"],$ubicacion[0]["pventapublico"],$ubicacion[0]["pventamin"],$ubicacion[0]["pventacredito"],$ubicacion[0]["pventaxmayor"],$ubicacion[0]["pventaadicional"],$ubicacion[0]["preciocosto"],$ubicacion[0]["gastos"],$ubicacion[0]["codigobarra"],1];

					$f = ["codproducto","codunidad","codalmacen"]; $v = [$this->request->codproducto,$this->request->codunidad_nueva,$value["codalmacen"]];
					$estado = $this->phuyu_model->phuyu_editar_1("almacen.productoubicacion",$campos,$valores,$f,$v);
				}else{
					$estado = $this->phuyu_model->phuyu_guardar("almacen.productoubicacion", $campos, $valores);
				}
				
			}

			// CAMBIAMOS EN KARDEX DETALLE, KARDEX ALMACEN DETALLE Y EN INVENTARIO DETALLE //
			$campos = ["codunidad"]; $valores = [$this->request->codunidad_nueva]; 
			$f = ["codproducto","codunidad"]; $v = [$this->request->codproducto,$this->request->codunidad];
			$estado = $this->phuyu_model->phuyu_editar_1("kardex.kardexdetalle",$campos,$valores,$f,$v);
			$estado = $this->phuyu_model->phuyu_editar_1("kardex.kardexalmacendetalle",$campos,$valores,$f,$v);
			$estado = $this->phuyu_model->phuyu_editar_1("almacen.inventariodetalle",$campos,$valores,$f,$v);
			$estado = $this->phuyu_model->phuyu_editar_1("almacen.guiasrdetalle",$campos,$valores,$f,$v);

			// ELIMINAMOS LAS UNIDADES ANTERIORES //
			foreach ($almacenes as $key => $value) {
				$this->db->where("codalmacen",$value["codalmacen"]); $this->db->where("codproducto",$this->request->codproducto); 
				$this->db->where("codunidad",$this->request->codunidad); $estado = $this->db->delete("almacen.productoubicacion");
			}
			$this->db->where("codproducto",$this->request->codproducto); $this->db->where("codunidad",$this->request->codunidad); 
			$estado = $this->db->delete("almacen.productounidades");

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

	function actualizar_stock_original(){
		if ($this->input->is_ajax_request()) {
			$estado = $this->db->query("UPDATE almacen.productoubicacion
				SET stockactual = coalesce((SELECT sum(( CASE WHEN  codmovimientotipo < 20 THEN 1 ELSE -1 END) * kardex.kardexdetalle.cantidad) FROM kardex.kardexdetalle INNER JOIN kardex.kardex ON (kardex.kardexdetalle.codkardex = kardex.kardex.codkardex) WHERE almacen.productoubicacion.codalmacen = kardex.kardex.codalmacen AND almacen.productoubicacion.codproducto = kardex.kardexdetalle.codproducto AND almacen.productoubicacion.codunidad = kardex.kardexdetalle.codunidad AND kardex.kardex.estado = 1 ),0) WHERE stockactual <>  coalesce((SELECT sum(( CASE WHEN  codmovimientotipo < 20 THEN 1 ELSE -1 END) * kardex.kardexdetalle.cantidad) FROM kardex.kardexdetalle INNER JOIN kardex.kardex ON (kardex.kardexdetalle.codkardex = kardex.kardex.codkardex) WHERE almacen.productoubicacion.codalmacen = kardex.kardex.codalmacen AND almacen.productoubicacion.codproducto = kardex.kardexdetalle.codproducto AND almacen.productoubicacion.codunidad = kardex.kardexdetalle.codunidad AND kardex.kardex.estado = 1 ), 0)");
			echo $estado;
		}
	}

	function actualizar_stock(){
		if ($this->input->is_ajax_request()) {
			$estado = $this->db->query("DO $$
DECLARE ln_stockactualconvertido numeric(18,4);
r_pub RECORD;
BEGIN

for r_pub in SELECT codalmacen, codproducto, codunidad, stockactual, stockactualreal, stockactualconvertido FROM almacen.productoubicacion loop
	ln_stockactualconvertido:=(SELECT sum(kpper.cantidad*pu.factor*kpper.signo)  over (order by kpper.codsucursal, kpper.codalmacen, kpper.fechacomprobante, kpper.tipo asc rows between unbounded preceding and current row) AS cantidad_sa
from kardex.v_kardexdetalle_mt kpper
INNER JOIN almacen.productounidades pu ON (kpper.codproducto = pu.codproducto AND kpper.codunidad = pu.codunidad)
WHERE kpper.estadok = 1 AND kpper.codalmacen = r_pub.codalmacen AND kpper.codproducto = r_pub.codproducto
LIMIT 1 OFFSET GREATEST((SELECT COUNT(*)  FROM kardex.v_kardexdetalle_mt k   INNER JOIN almacen.productounidades pu ON (k.codproducto = pu.codproducto AND k.codunidad = pu.codunidad)  
WHERE k.estadok = 1 AND k.codalmacen = r_pub.codalmacen AND k.codproducto = r_pub.codproducto) - 1, 0));
  
  UPDATE almacen.productoubicacion
   SET stockactualconvertido= COALESCE(ln_stockactualconvertido,0)
   WHERE codalmacen = r_pub.codalmacen AND codproducto = r_pub.codproducto AND codunidad = r_pub.codunidad;
   
END LOOP;
END $$;


UPDATE almacen.productoubicacion
   SET stockactualconvertido=stockactualconvertido/pun.factor
   FROM almacen.productounidades pun
   WHERE almacen.productoubicacion.codproducto = pun.codproducto AND almacen.productoubicacion.codunidad = pun.codunidad;
");
			echo 1;
		}
	}

	function actualizar_precios(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			$campos = ["preciocompra","preciocosto","pventapublico","pventamin","pventacredito","pventaxmayor","pventaadicional"];
			$this->db->trans_begin();

			foreach ($this->request->productos as $key => $value) {
				$valores = [
					(double)$this->request->productos[$key]->preciocosto,
					(double)$this->request->productos[$key]->preciocosto,
					(double)$this->request->productos[$key]->precioventa,
					(double)$this->request->productos[$key]->precioxmayor,
					(double)$this->request->productos[$key]->pventacredito,
					(double)$this->request->productos[$key]->precioxmayor,
					(double)$this->request->productos[$key]->precioxmayorcre
				];

				$f = ["codproducto","codunidad"]; 
				$v = [(int)$this->request->productos[$key]->codproducto,$this->request->productos[$key]->codunidad];
				$estado = $this->phuyu_model->phuyu_editar_1("almacen.productounidades", $campos, $valores, $f, $v);

				$f1 = ["codproducto","codunidad"]; 
				$v1 = [(int)$this->request->productos[$key]->codproducto,$this->request->productos[$key]->codunidad];
				$estado = $this->phuyu_model->phuyu_editar_1("almacen.productoubicacion", $campos, $valores, $f1, $v1);
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