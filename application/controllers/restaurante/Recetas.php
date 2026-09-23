<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Recetas extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$this->load->view("restaurante/recetas/index");
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
			$pagina = isset($this->request->pagina) ? (int)$this->request->pagina : 1;
			$limit = isset($this->request->limit) ? (int)$this->request->limit : 25;
			$buscar = isset($this->request->buscar) ? trim($this->request->buscar) : "";
			$tipo = isset($this->request->tipo) ? trim($this->request->tipo) : "preparados";
			if ($pagina <= 0) { $pagina = 1; }
			if ($limit <= 0 || $limit > 100) { $limit = 25; }
			$offset = ($pagina - 1) * $limit;

			$params = [(int)$_SESSION["phuyu_codalmacen"]];
			$where = "
				where p.estado=1
				and pu.estado=1
				and pu.codalmacen=?
			";

			if ($tipo === "preparados") {
				$where .= " and coalesce(p.es_preparado,0)=1 ";
			}elseif ($tipo === "venta") {
				$where .= " and coalesce(p.es_venta,1)=1 ";
			}elseif ($tipo === "con_receta") {
				$where .= " and exists (
					select 1 from restaurante.recetas r
					where r.codproducto=p.codproducto
					and r.codunidad=pu.codunidad
					and r.estado=1
				) ";
			}elseif ($tipo === "sin_receta") {
				$where .= " and not exists (
					select 1 from restaurante.recetas r
					where r.codproducto=p.codproducto
					and r.codunidad=pu.codunidad
					and r.estado=1
				) ";
			}

			if ($buscar !== "") {
				$where .= " and (upper(p.descripcion) like upper(?) or upper(coalesce(p.codigo,'')) like upper(?)) ";
				$params[] = "%".$buscar."%";
				$params[] = "%".$buscar."%";
			}

			$total = $this->db->query("
				select count(*) as total
				from almacen.productos as p
				inner join almacen.productoubicacion as pu on(p.codproducto=pu.codproducto)
				".$where."
			", $params)->row_array();
			$total_registros = isset($total["total"]) ? (int)$total["total"] : 0;

			$lista = $this->db->query("
				select p.codproducto,p.codigo,p.descripcion,u.codunidad,u.descripcion as unidad,
				round(pu.stockactual) as stock,m.descripcion as marca,
				coalesce(p.es_venta,1) as es_venta,
				coalesce(p.es_insumo,0) as es_insumo,
				coalesce(p.es_preparado,0) as es_preparado
				from almacen.productos as p
				inner join almacen.productoubicacion as pu on(p.codproducto=pu.codproducto)
				inner join almacen.unidades as u on(u.codunidad=pu.codunidad)
				inner join almacen.marcas as m on(p.codmarca=m.codmarca)
				".$where."
				order by coalesce(p.es_preparado,0) desc, p.descripcion
				limit ".$limit." offset ".$offset."
			", $params)->result_array(); $item = $offset;
			foreach ($lista as $key => $value) { $item = $item + 1;
				$precio = $this->db->query("select factor,pventapublico,pventamin,pventacredito,pventaxmayor,preciocosto from almacen.productounidades where codproducto=".$value["codproducto"]." and codunidad=".$value["codunidad"]." and estado=1")->result_array();
				$lista[$key]["nro"] = $item;
				if (count($precio)==0) {
					$lista[$key]["precioventa"] = 0.00; $lista[$key]["preciocosto"] = 0.00;
				}else{
					$lista[$key]["precioventa"] = number_format($precio[0]["pventapublico"],2);
					$lista[$key]["preciocosto"] = number_format($precio[0]["preciocosto"],2);
				}

				$lista[$key]["receta"] = $this->detalle_receta_producto($value["codproducto"], $value["codunidad"]);
				$lista[$key]["costo_receta"] = number_format($this->costo_receta($lista[$key]["receta"]), 2);
			}
			echo json_encode([
				"lista" => $lista,
				"paginacion" => [
					"actual" => $pagina,
					"limit" => $limit,
					"total" => $total_registros,
					"ultima" => max(1, (int)ceil($total_registros / $limit))
				]
			]);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function ingredientes(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$buscar = isset($this->request->buscar) ? trim($this->request->buscar) : "";
			$tipo = isset($this->request->tipo) ? trim($this->request->tipo) : "insumos";
			$params = [(int)$_SESSION["phuyu_codalmacen"]];
			$where = "
				where p.estado=1
				and pu.estado=1
				and pu.codalmacen=?
			";

			if ($tipo === "insumos") {
				$where .= " and coalesce(p.es_insumo,0)=1 ";
			}elseif ($tipo === "venta") {
				$where .= " and coalesce(p.es_venta,1)=1 ";
			}elseif ($tipo === "preparados") {
				$where .= " and coalesce(p.es_preparado,0)=1 ";
			}

			if ($buscar !== "") {
				$where .= " and (upper(p.descripcion) like upper(?) or upper(coalesce(p.codigo,'')) like upper(?)) ";
				$params[] = "%".$buscar."%";
				$params[] = "%".$buscar."%";
			}

			$lista = $this->db->query("
				select p.codproducto,p.codigo,p.descripcion,p.caracteristicas,
				u.codunidad,u.descripcion as unidad,
				round(pu.stockactual,3) as stock,
				m.descripcion as marca,
				round(coalesce(nullif(puv.preciocosto,0),puv.pventapublico,0),2) as preciocosto,
				coalesce(p.es_venta,1) as es_venta,
				coalesce(p.es_insumo,0) as es_insumo,
				coalesce(p.es_preparado,0) as es_preparado
				from almacen.productos as p
				inner join almacen.productoubicacion as pu on(p.codproducto=pu.codproducto)
				inner join almacen.unidades as u on(u.codunidad=pu.codunidad)
				inner join almacen.marcas as m on(p.codmarca=m.codmarca)
				inner join almacen.productounidades as puv on(pu.codproducto=puv.codproducto and pu.codunidad=puv.codunidad and puv.estado=1)
				".$where."
				order by p.descripcion
				limit 80
			", $params)->result_array();

			foreach ($lista as $key => $value) {
				$lista[$key]["unidades_lista"] = $this->unidades_producto($value["codproducto"]);
			}
			echo json_encode($lista);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function detalle_receta($codproducto,$codunidad){
		if ($this->input->is_ajax_request()) {
			$detalle = $this->detalle_receta_producto($codproducto, $codunidad);
			echo json_encode($detalle);
		}
	}

	private function detalle_receta_producto($codproducto, $codunidad){
		$detalle = $this->db->query("
			select r.codproducto_receta as codproducto,p.descripcion as producto,r.codunidad_receta as codunidad, u.descripcion as unidad,
			round(r.cantidad,3) as cantidad,
			round(coalesce(p.merma_porcentaje,0),4) as merma_porcentaje,
			round(coalesce(nullif(pu.preciocosto,0),pu.pventapublico,0),2) as preciocosto,
			round((coalesce(nullif(pu.preciocosto,0),pu.pventapublico,0) / greatest((1 + (coalesce(p.merma_porcentaje,0) / 100)), 0.0001)),2) as preciocosto_merma,
			round((r.cantidad * (coalesce(nullif(pu.preciocosto,0),pu.pventapublico,0) / greatest((1 + (coalesce(p.merma_porcentaje,0) / 100)), 0.0001))),2) as costototal
			from restaurante.recetas as r
			inner join almacen.productos as p on(r.codproducto_receta=p.codproducto)
			inner join almacen.unidades as u on(r.codunidad_receta=u.codunidad)
			left join almacen.productounidades as pu on(pu.codproducto=r.codproducto_receta and pu.codunidad=r.codunidad_receta and pu.estado=1)
			where r.codproducto=".(int)$codproducto."
			and r.codunidad=".(int)$codunidad."
			and r.estado=1
			order by r.item
		")->result_array();
		foreach ($detalle as $key => $value) {
			$detalle[$key]["unidades"] = $this->unidades_producto($value["codproducto"]);
		}
		return $detalle;
	}

	private function unidades_producto($codproducto){
		return $this->db->query("
			select pu.codunidad,u.descripcion as unidad,
			round((coalesce(nullif(pu.preciocosto,0),pu.pventapublico,0) / greatest((1 + (coalesce(p.merma_porcentaje,0) / 100)), 0.0001)),2) as preciocosto
			from almacen.productounidades pu
			inner join almacen.productos p on(p.codproducto=pu.codproducto)
			inner join almacen.unidades u on(u.codunidad=pu.codunidad)
			where pu.codproducto=".(int)$codproducto."
			and pu.estado=1
			order by pu.factor
		")->result_array();
	}

	private function costo_receta($detalle){
		$total = 0;
		foreach ($detalle as $value) {
			$total = $total + (float)$value["costototal"];
		}
		return $total;
	}

	function guardar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input')); $item = 0; $estado = 1;

			$this->db->where("codproducto",$this->request->campos->codproducto); 
			$this->db->where("codunidad",$this->request->campos->codunidad);
			$this->db->delete("restaurante.recetas");

			foreach ($this->request->detalle as $key => $value) { $item = $item + 1;
				$codproducto_receta = (int)$this->request->detalle[$key]->codproducto;
				$codunidad_receta = (int)$this->request->detalle[$key]->codunidad;
				if ($codunidad_receta <= 0) {
					$unidad = $this->db->query("select codunidad from almacen.productounidades where codproducto=".$codproducto_receta." and estado=1 order by factor limit 1")->row_array();
					$codunidad_receta = isset($unidad["codunidad"]) ? (int)$unidad["codunidad"] : 0;
				}
				$campos = ["codproducto","codunidad","item","codproducto_receta","codunidad_receta","cantidad","estado"];
				$valores = [
					(int)$this->request->campos->codproducto,(int)$this->request->campos->codunidad,$item,
					$codproducto_receta,
					$codunidad_receta,
					(double)$this->request->detalle[$key]->cantidad,
					1
				];
				$estado = $this->phuyu_model->phuyu_guardar("restaurante.recetas", $campos, $valores);
				$this->db->where("codproducto", $codproducto_receta);
				$this->db->update("almacen.productos", ["es_insumo" => 1]);
			}
			$this->db->where("codproducto", (int)$this->request->campos->codproducto);
			$this->db->update("almacen.productos", ["es_preparado" => 1, "es_venta" => 1]);
			echo $estado;
		}
	}

	function actualizar_costo_plato(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$codproducto = (int)$this->request->campos->codproducto;
			$codunidad = (int)$this->request->campos->codunidad;
			$preciocosto = (double)$this->request->preciocosto;

			$this->db->where("codproducto", $codproducto);
			$this->db->where("codunidad", $codunidad);
			$this->db->where("estado", 1);
			$estado = $this->db->update("almacen.productounidades", ["preciocosto" => $preciocosto]);

			$this->db->where("codproducto", $codproducto);
			$this->db->where("codunidad", $codunidad);
			$this->db->where("codalmacen", $_SESSION["phuyu_codalmacen"]);
			$this->db->where("estado", 1);
			$this->db->update("almacen.productoubicacion", ["preciocosto" => $preciocosto]);

			echo $estado ? 1 : 0;
		}
	}

	function consumo_total_pdf(){
		if ($_GET["datos"]) {
			$this->request = json_decode($_GET["datos"]);

			$this->load->library("Pdf2"); $pdf = new Pdf2(); $pdf->AddPage();
			$pdf->pdf_header_titulo("CONSUMO INGREDIENTES TOTALIZADO: DESDE ".$this->request->fechadesde." A ".$this->request->fechahasta);

			$columnas = array("N°","DESCRIPCION PRODUCTO","UNIDAD","CANTIDAD","S/ P.COSTO","S/ VALORIZADO");
			$w = array(10,85,30,20,20,25); $pdf->pdf_tabla_head($columnas,$w,8);

			$recetas = $this->db->query("select r.codproducto_receta,p.descripcion as producto,r.codunidad_receta,u.descripcion as unidad,pu.preciocosto from restaurante.recetas as r inner join almacen.productos as p on(r.codproducto_receta=p.codproducto) inner join almacen.unidades as u on(r.codunidad_receta=u.codunidad) inner join almacen.productounidades as pu on(pu.codproducto=r.codproducto_receta and pu.codunidad=r.codunidad_receta) where r.estado=1 group by r.codproducto_receta,r.codunidad_receta,p.descripcion,u.descripcion,pu.preciocosto")->result_array();

			$pdf->SetWidths(array(10,85,30,20,20,25)); $pdf->SetLineHeight(5); $pdf->SetFont('Arial','',8); 
			$item = 0; $cantidad = 0; $valorizado = 0;
			foreach ($recetas as $key => $value) { $item = $item + 1;
				$salidas = $this->db->query("select coalesce(sum(kd.cantidad),0) as cantidad from kardex.kardex as k inner join kardex.kardexdetalle as kd on(k.codkardex=kd.codkardex) where kd.codproducto=".$value["codproducto_receta"]." and kd.codunidad=".$value["codunidad_receta"]." and k.fechakardex>='".$this->request->fechadesde."' and k.fechakardex<='".$this->request->fechahasta."' and k.codmovimientotipo>=20 and k.estado=1")->result_array();

				$datos = array($item);
				array_push($datos,utf8_decode($value["producto"]));
				array_push($datos,utf8_decode($value["unidad"]));
				array_push($datos,number_format($salidas[0]["cantidad"],2));
				array_push($datos,number_format($value["preciocosto"],2));
				array_push($datos,number_format($value["preciocosto"] * $salidas[0]["cantidad"],2));
	            $pdf->Row($datos);

	            $cantidad = $cantidad + $salidas[0]["cantidad"];
	            $valorizado = $valorizado + ($value["preciocosto"] * $salidas[0]["cantidad"]);
			}
			$pdf->Cell(array_sum($w),0,'','T'); $pdf->Ln();

			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(125,5,"TOTALES",1,0,'R');
		    $pdf->Cell(40,5,number_format($cantidad,2),1,"R");
		    $pdf->Cell(25,5,number_format($valorizado,2),1,"R");

			$pdf->SetTitle("Consumo Ingredientes"); $pdf->Output();
		}
	}

	function consumo_fechas_pdf(){
		if ($_GET["datos"]) {
			$this->request = json_decode($_GET["datos"]);

			$this->load->library("Pdf2"); $pdf = new Pdf2(); $pdf->AddPage();
			$pdf->pdf_header_titulo("CONSUMO INGREDIENTES POR FECHAS: DESDE ".$this->request->fechadesde." A ".$this->request->fechahasta);

			$columnas = array("N°","DESCRIPCION PRODUCTO","UNIDAD","CANTIDAD","S/ P.COSTO","S/ VALORIZADO");
			$w = array(10,85,30,20,20,25); $pdf->pdf_tabla_head($columnas,$w,8);

			$recetas = $this->db->query("select r.codproducto_receta,p.descripcion as producto,r.codunidad_receta,u.descripcion as unidad,pu.preciocosto from restaurante.recetas as r inner join almacen.productos as p on(r.codproducto_receta=p.codproducto) inner join almacen.unidades as u on(r.codunidad_receta=u.codunidad) inner join almacen.productounidades as pu on(pu.codproducto=r.codproducto_receta and pu.codunidad=r.codunidad_receta) where r.estado=1 group by r.codproducto_receta,r.codunidad_receta,p.descripcion,u.descripcion,pu.preciocosto")->result_array();

			$fecha = $this->request->fechadesde;
			while ($fecha <= $this->request->fechahasta) {
				$pdf->SetFont('Arial','B',10); $pdf->setFillColor(245,245,245);
				$pdf->Cell(0,7,"SALIDAS DE INGREDIENTES EN LA FECHA: ".$fecha,0,1,'L',1); $pdf->Ln(2);

				$pdf->SetWidths(array(10,85,30,20,20,25)); $pdf->SetLineHeight(5); $pdf->SetFont('Arial','',8); 
				$item = 0; $cantidad = 0; $valorizado = 0;
				foreach ($recetas as $key => $value) { $item = $item + 1;
					$salidas = $this->db->query("select coalesce(sum(kd.cantidad),0) as cantidad from kardex.kardex as k inner join kardex.kardexdetalle as kd on(k.codkardex=kd.codkardex) where kd.codproducto=".$value["codproducto_receta"]." and kd.codunidad=".$value["codunidad_receta"]." and k.fechakardex='".$fecha."' and k.codmovimientotipo>=20 and k.estado=1")->result_array();

					$datos = array($item);
					array_push($datos,utf8_decode($value["producto"]));
					array_push($datos,utf8_decode($value["unidad"]));
					array_push($datos,number_format($salidas[0]["cantidad"],2));
					array_push($datos,number_format($value["preciocosto"],2));
					array_push($datos,number_format($value["preciocosto"] * $salidas[0]["cantidad"],2));
		            $pdf->Row($datos);

		            $cantidad = $cantidad + $salidas[0]["cantidad"];
		            $valorizado = $valorizado + ($value["preciocosto"] * $salidas[0]["cantidad"]);
				}
				$pdf->Cell(array_sum($w),0,'','T'); $pdf->Ln();

				$pdf->SetFont('Arial','B',8);
				$pdf->Cell(125,5,"TOTALES",1,0,'R');
			    $pdf->Cell(40,5,number_format($cantidad,2),1,"R");
			    $pdf->Cell(25,5,number_format($valorizado,2),1,"R"); $pdf->Ln();

				$fecha = date("Y-m-d",strtotime($fecha."+ 1 days"));
			}
			$pdf->SetTitle("Consumo Ingredientes"); $pdf->Output();
		}
	}
}
