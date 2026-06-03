<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Proformas extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			$sucursales = $this->db->query("select *from public.sucursales where estado=1")->result_array();
			$this->load->view("reportes/proformas/index",compact("sucursales"));
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function buscar_producto_pedidos(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$filtro = "(REPLACE(UPPER(pro.descripcion),' ','%') like REPLACE (UPPER('%".$this->request->buscar."%'),' ','%') or UPPER(pro.codigo) like UPPER('%".$this->request->buscar."%') )";
			$lista = $this->db->query("select pro.codigo,pro.descripcion as producto,uni.descripcion as unidad,per.razonsocial as cliente,round(pd.cantidad,2) as cantidad, round(pd.preciounitario,2) as preciounitario,round(pd.subtotal,2) as subtotal, round(pd.preciorefunitario,2) as preciorefunitario, round(pd.preciorefunitario * pd.cantidad, 2) as subtotalref from kardex.proformas as p inner join public.personas as per on(p.codpersona=per.codpersona) inner join kardex.proformasdetalle as pd on (p.codproforma=pd.codproforma) inner join almacen.productos as pro on(pd.codproducto=pro.codproducto) inner join almacen.unidades as uni on(pd.codunidad=uni.codunidad) where ".$filtro)->result_array();
			$totales = $this->db->query("select round(sum(pd.cantidad),2) as cantidad, round(sum(pd.cantidad * pd.preciounitario),2) as total, round(sum(pd.preciorefunitario * pd.cantidad), 2) as totalref from kardex.proformas as p inner join public.personas as per on(p.codpersona=per.codpersona) inner join kardex.proformasdetalle as pd on (p.codproforma=pd.codproforma) inner join almacen.productos as pro on(pd.codproducto=pro.codproducto) inner join almacen.unidades as uni on(pd.codunidad=uni.codunidad) where ".$filtro)->result_array();
			$data["lista"] = $lista; $data["totales"] = $totales;
			echo json_encode($data);
		}
	}

	function pdf_pedidos(){
		if ($_GET["datos"]) {
			$this->request = json_decode($_GET["datos"]);

			$this->load->library('Pdf2'); $pdf = new Pdf2(); $pdf->AddPage();
			$pdf->pdf_header("REPORTE PROFORMAS","");

			if($this->request->codsucursal==0){
				$sucursal = $this->db->query("Select *from public.sucursales where estado = 1")->result_array();
			}else{
				$sucursal = $this->db->query("Select *from public.sucursales where codsucursal=".$this->request->codsucursal." and estado = 1")->result_array();
			}

			if($this->request->estado==0){
				$estado = '';
			}else if($this->request->estado==1){
				$estado = ' AND p.estado=1 AND p.estadoproceso=0';
			}
			else if($this->request->estado==2){
				$estado = ' AND p.estado=1 AND p.estadoproceso=1';
			}else{
				$estado = ' AND p.estado=0';
			}

			foreach ($sucursal as $key => $value) {

				$pdf->pdf_header_titulo("SUCURSAL: ".$value["descripcion"]." DEL ".$this->request->fechadesde." AL ".$this->request->fechahasta);

				$columnas = array("ID","COMPROBANTE","FECHA","CLIENTE","VALOR VENTA","IGV","IMPORTE","ESTADO");
				$w = array(10,25,15,65,20,15,20,20); $pdf->pdf_tabla_head($columnas,$w,7);

				$lista = $this->db->query("select p.*,per.razonsocial as cliente from kardex.proformas as p inner join public.personas as per on(p.codpersona=per.codpersona) where p.codsucursal = ".$value["codsucursal"]." and p.fechaproforma between '".$this->request->fechadesde."' and '".$this->request->fechahasta."' ".$estado." ORDER BY codproforma ASC")->result_array();
				
				$valorventa = 0; $total = 0; $igv = 0;$item= 1;
				$pdf->SetWidths($w); $pdf->SetLineHeight(5); $pdf->SetFont('Arial','',7);
				foreach ($lista as $key => $value) {
					$valorventa = $valorventa + $value["valorventa"];
					$igv = $igv + $value["igv"];
					$total = $total + $value["importe"];

					$estado = ($value["estadoproceso"]==0) ? 'PENDIENTE' : 'CANJEADO';

					$datos = array($value["codproforma"]);
					array_push($datos,utf8_decode($value["seriecomprobante"]."-".$value["nrocomprobante"]));
					array_push($datos,utf8_decode($value["fechaproforma"]));
					array_push($datos,utf8_decode($value["cliente"]));
					array_push($datos,number_format($value["valorventa"],2));
					array_push($datos,number_format($value["igv"],2));
					array_push($datos,number_format($value["importe"],2));
					array_push($datos,utf8_decode($estado));
	                $pdf->Row($datos);
	                $item++;
				}
				$pdf->Cell(array_sum($w),0,'','T'); $pdf->Ln();

				$pdf->SetFont('Arial','B',8);
				$pdf->Cell(115,5,"TOTALES",1,0,'R');
			    $pdf->Cell(20,5,number_format($valorventa,2),1,"R");
			    $pdf->Cell(15,5,number_format($igv,2),1,"R");
			    $pdf->Cell(20,5,number_format($total,2),1,"R");
			}

			$pdf->SetTitle("phuyu Peru - Reporte Proformas"); $pdf->Output();
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function pdf_pedidos_detallado(){
		if ($_GET["datos"]) {
			$this->request = json_decode($_GET["datos"]);

			$this->load->library('Pdf2'); $pdf = new Pdf2(); $pdf->AddPage();
			$pdf->pdf_header("REPORTE PROFORMAS DETALLADOS","");

			if($this->request->codsucursal==0){
				$sucursal = $this->db->query("Select *from public.sucursales where estado = 1")->result_array();
			}else{
				$sucursal = $this->db->query("Select *from public.sucursales where codsucursal=".$this->request->codsucursal." and estado = 1")->result_array();
			}

			if($this->request->estado==0){
				$estado = '';
			}else if($this->request->estado==1){
				$estado = ' AND p.estado=1 AND p.estadoproceso=0';
			}
			else if($this->request->estado==2){
				$estado = ' AND p.estado=1 AND p.estadoproceso=1';
			}else{
				$estado = ' AND p.estado=0';
			}

			foreach ($sucursal as $key => $value) {

				$pdf->pdf_header_titulo("SUCURSAL: ".$value["descripcion"]." DEL ".$this->request->fechadesde." AL ".$this->request->fechahasta);

				$columnas = array("ID","COMPROBANTE","FECHA","CLIENTE","VALOR VENTA","IGV","IMPORTE","ESTADO");
				$w = array(10,25,15,65,20,15,20,20); $pdf->pdf_tabla_head($columnas,$w,7);

				$lista = $this->db->query("select p.*,per.razonsocial as cliente from kardex.proformas as p inner join public.personas as per on(p.codpersona=per.codpersona) where p.codsucursal = ".$value["codsucursal"]." and p.fechaproforma between '".$this->request->fechadesde."' and '".$this->request->fechahasta."' ".$estado." ORDER BY codproforma ASC")->result_array();
				
				$valorventa = 0; $total = 0; $igv = 0;$item= 1;
				foreach ($lista as $key => $value) {
				$pdf->SetWidths($w); $pdf->SetLineHeight(5); $pdf->SetFont('Arial','',7);
					$valorventa = $valorventa + $value["valorventa"];
					$igv = $igv + $value["igv"];
					$total = $total + $value["importe"];

					$estado = ($value["estadoproceso"]==0) ? 'PENDIENTE' : 'CANJEADO';

					$datos = array($value["codproforma"]);
					array_push($datos,utf8_decode($value["seriecomprobante"]."-".$value["nrocomprobante"]));
					array_push($datos,utf8_decode($value["fechaproforma"]));
					array_push($datos,utf8_decode($value["cliente"]));
					array_push($datos,number_format($value["valorventa"],2));
					array_push($datos,number_format($value["igv"],2));
					array_push($datos,number_format($value["importe"],2));
					array_push($datos,utf8_decode($estado));
	                $pdf->Row($datos);
	                $item++;

	                $detalle = $this->db->query("select pd.*,pro.descripcion as producto,uni.descripcion as unidad from kardex.proformasdetalle pd inner join almacen.productos as pro on(pd.codproducto=pro.codproducto) inner join almacen.unidades as uni on(pd.codunidad=uni.codunidad) where pd.codproforma=".$value["codproforma"])->result_array();

	                $columnas = array("CANT","DESCRIPCION DETALLE VENTA","UNI.MED","P.UNITARIO","IGV","IMPORTE",""); $wd = array(10,85,20,20,15,20,20,15); 
	                for($i=0;$i<count($columnas);$i++){
			            $pdf->Cell($wd[$i],5,utf8_decode($columnas[$i]),1,0,'L');
			        } $pdf->Ln();

			        $pdf->SetWidths(array(10,85,20,20,15,20,20,15)); $pdf->SetLineHeight(5); $pdf->SetFont('Arial','',7);
			        foreach ($detalle as $v) {
			        	$datos = array(number_format($v["cantidad"],2));
						array_push($datos,utf8_decode($v["producto"]));
						array_push($datos,utf8_decode($v["unidad"]));

						array_push($datos,number_format($v["preciounitario"],2));
						array_push($datos,number_format($v["igv"],2));
						array_push($datos,number_format($v["subtotal"],2));
						array_push($datos,"");
		                $pdf->Row($datos);
			        }
				}
				$pdf->Cell(array_sum($w),0,'','T'); $pdf->Ln();

				$pdf->SetFont('Arial','B',8);
				$pdf->Cell(115,5,"TOTALES",1,0,'R');
			    $pdf->Cell(20,5,number_format($valorventa,2),1,"R");
			    $pdf->Cell(15,5,number_format($igv,2),1,"R");
			    $pdf->Cell(20,5,number_format($total,2),1,"R");
			}

			$pdf->SetTitle("phuyu Peru - Reporte Proformas detallado"); $pdf->Output();
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function excel_proformas(){
		if ($_GET["datos"]) {
			$this->request = json_decode($_GET["datos"]);

			if($this->request->codsucursal==0){
				$sucursal = $this->db->query("Select *from public.sucursales where estado = 1")->result_array();
			}else{
				$sucursal = $this->db->query("Select *from public.sucursales where codsucursal=".$this->request->codsucursal." and estado = 1")->result_array();
			}

			if($this->request->estado==0){
				$estado = '';
			}else if($this->request->estado==1){
				$estado = ' AND p.estado=1 AND p.estadoproceso=0';
			}
			else if($this->request->estado==2){
				$estado = ' AND p.estado=1 AND p.estadoproceso=1';
			}else{
				$estado = ' AND p.estado=0';
			}

			foreach ($sucursal as $key => $value) {
			
				$lista = $this->db->query("select p.*,per.razonsocial as cliente from kardex.proformas as p inner join public.personas as per on(p.codpersona=per.codpersona) where p.codsucursal = ".$value["codsucursal"]." and p.fechaproforma between '".$this->request->fechadesde."' and '".$this->request->fechahasta."' ".$estado." ORDER BY codproforma ASC")->result_array();

				$sucursal[$key]["lista"] = $lista;
			}

			$fechadesde = $this->request->fechadesde;
			$fechahasta = $this->request->fechahasta;

			$this->load->view("reportes/proformas/xls_proformas.php",compact("sucursal","fechadesde","fechahasta"));
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function excel_proformas_detallado(){
		if ($_GET["datos"]) {
			$this->request = json_decode($_GET["datos"]);

			if($this->request->codsucursal==0){
				$sucursal = $this->db->query("Select *from public.sucursales where estado = 1")->result_array();
			}else{
				$sucursal = $this->db->query("Select *from public.sucursales where codsucursal=".$this->request->codsucursal." and estado = 1")->result_array();
			}

			if($this->request->estado==0){
				$estado = '';
			}else if($this->request->estado==1){
				$estado = ' AND p.estado=1 AND p.estadoproceso=0';
			}
			else if($this->request->estado==2){
				$estado = ' AND p.estado=1 AND p.estadoproceso=1';
			}else{
				$estado = ' AND p.estado=0';
			}

			foreach ($sucursal as $key => $value) {
			
				$lista = $this->db->query("select p.*,per.razonsocial as cliente from kardex.proformas as p inner join public.personas as per on(p.codpersona=per.codpersona) where p.codsucursal = ".$value["codsucursal"]." and p.fechaproforma between '".$this->request->fechadesde."' and '".$this->request->fechahasta."' ".$estado." ORDER BY codproforma ASC")->result_array();

				$sucursal[$key]["lista"] = $lista;
			}

			$fechadesde = $this->request->fechadesde;
			$fechahasta = $this->request->fechahasta;

			$this->load->view("reportes/proformas/xls_proformasdetallado.php",compact("sucursal","fechadesde","fechahasta"));
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function pdf_producto_proformas(){
		if ($_GET["datos"]) {
			$this->request = json_decode($_GET["datos"]);

			$this->load->library('Pdf2'); $pdf = new Pdf2(); $pdf->AddPage();
			$pdf->pdf_header("REPORTE PROFORMAS POR PRODUCTOS","");

			if($this->request->codsucursal==0){
				$sucursal = $this->db->query("Select *from public.sucursales where estado = 1")->result_array();
			}else{
				$sucursal = $this->db->query("Select *from public.sucursales where codsucursal=".$this->request->codsucursal." and estado = 1")->result_array();
			}

			if($this->request->estado==0){
				$estado = '';
			}else if($this->request->estado==1){
				$estado = ' AND p.estado=1 AND p.estadoproceso=0';
			}
			else if($this->request->estado==2){
				$estado = ' AND p.estado=1 AND p.estadoproceso=1';
			}else{
				$estado = ' AND p.estado=0';
			}

			foreach ($sucursal as $key => $value) {

				$pdf->pdf_header_titulo("SUCURSAL: ".$value["descripcion"]." DEL ".$this->request->fechadesde." AL ".$this->request->fechahasta);

				$columnas = array("CODIGO","PRODUCTO","UNIDAD","CANTIDAD","PRECIO","SUBTOTAL","FECHA P.","CLIENTE");
				$w = array(15,42,15,15,15,15,15,60); $pdf->pdf_tabla_head($columnas,$w,7);

				$filtro = "(REPLACE(UPPER(pro.descripcion),' ','%') like REPLACE (UPPER('%".$this->request->buscar."%'),' ','%') or UPPER(pro.codigo) like UPPER('%".$this->request->buscar."%') )";
				$lista = $this->db->query("select pro.codigo,pro.descripcion as producto,uni.descripcion as unidad,per.razonsocial as cliente,round(pd.cantidad,2) as cantidad, round(pd.preciounitario,2) as preciounitario,round(pd.subtotal,2) as subtotal, round(pd.preciorefunitario,2) as preciorefunitario, round(pd.preciorefunitario * pd.cantidad, 2) as subtotalref,p.fechaproforma from kardex.proformas as p inner join public.personas as per on(p.codpersona=per.codpersona) inner join kardex.proformasdetalle as pd on (p.codproforma=pd.codproforma) inner join almacen.productos as pro on(pd.codproducto=pro.codproducto) inner join almacen.unidades as uni on(pd.codunidad=uni.codunidad) where ".$filtro." and p.codsucursal = ".$value["codsucursal"]." and p.fechaproforma between '".$this->request->fechadesde."' and '".$this->request->fechahasta."' ".$estado."")->result_array();
				
				$cantidad = 0; $total = 0; $totalref = 0;
				$pdf->SetWidths($w); $pdf->SetLineHeight(5); $pdf->SetFont('Arial','',7);
				foreach ($lista as $key => $value) {
					$cantidad = $cantidad + $value["cantidad"];
					$total = $total + $value["subtotal"];
					$totalref = $totalref + $value["subtotalref"];

					$datos = array($value["codigo"]);
					array_push($datos,utf8_decode($value["producto"]));
					array_push($datos,$value["unidad"]);
					array_push($datos,number_format($value["cantidad"],2));
					array_push($datos,number_format($value["preciounitario"],2));
					array_push($datos,number_format($value["subtotal"],2));
					array_push($datos,$value["fechaproforma"]);
					array_push($datos,utf8_decode($value["cliente"]));
	                $pdf->Row($datos);
				}
				$pdf->Cell(array_sum($w),0,'','T'); $pdf->Ln();

				$pdf->SetFont('Arial','B',8);
				$pdf->Cell(72,5,"TOTALES",1,0,'R');
			    $pdf->Cell(15,5,number_format($cantidad,2),1,"R");
			    $pdf->Cell(15,5,"",1,"R");
			    $pdf->Cell(30,5,number_format($total,2),1,"R");
			}

			$pdf->SetTitle("phuyu Peru - Reporte Proformas por Producto"); $pdf->Output();
		}else{
			$this->load->view("phuyu/404");
		}
	}
	function excel_producto_proformas(){
		if ($_GET["datos"]) {
			$this->request = json_decode($_GET["datos"]);

			if($this->request->codsucursal==0){
				$sucursal = $this->db->query("Select *from public.sucursales where estado = 1")->result_array();
			}else{
				$sucursal = $this->db->query("Select *from public.sucursales where codsucursal=".$this->request->codsucursal." and estado = 1")->result_array();
			}

			if($this->request->estado==0){
				$estado = '';
			}else if($this->request->estado==1){
				$estado = ' AND p.estado=1 AND p.estadoproceso=0';
			}
			else if($this->request->estado==2){
				$estado = ' AND p.estado=1 AND p.estadoproceso=1';
			}else{
				$estado = ' AND p.estado=0';
			}

			foreach ($sucursal as $key => $value) {
			
				$filtro = "(REPLACE(UPPER(pro.descripcion),' ','%') like REPLACE (UPPER('%".$this->request->buscar."%'),' ','%') or UPPER(pro.codigo) like UPPER('%".$this->request->buscar."%') )";
				$lista = $this->db->query("select pro.codigo,pro.descripcion as producto,uni.descripcion as unidad,per.razonsocial as cliente,round(pd.cantidad,2) as cantidad, round(pd.preciounitario,2) as preciounitario,round(pd.subtotal,2) as subtotal, round(pd.preciorefunitario,2) as preciorefunitario, round(pd.preciorefunitario * pd.cantidad, 2) as subtotalref,p.fechaproforma from kardex.proformas as p inner join public.personas as per on(p.codpersona=per.codpersona) inner join kardex.proformasdetalle as pd on (p.codproforma=pd.codproforma) inner join almacen.productos as pro on(pd.codproducto=pro.codproducto) inner join almacen.unidades as uni on(pd.codunidad=uni.codunidad) where ".$filtro." and p.codsucursal = ".$value["codsucursal"]." and p.fechaproforma between '".$this->request->fechadesde."' and '".$this->request->fechahasta."' ".$estado."")->result_array();

				$sucursal[$key]["lista"] = $lista;
			}

			$fechadesde = $this->request->fechadesde;
			$fechahasta = $this->request->fechahasta;

			$this->load->view("reportes/proformas/xls_productos.php",compact("sucursal","fechadesde","fechahasta"));
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function buscar_cliente_pedidos(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			if ($this->request->codpersona==0) {
				$filtro = "";
			}else{
				$filtro = "where p.codpersona=".$this->request->codpersona;
			}
			$clientes = $this->db->query("select distinct(p.codpersona) as codpersona, per.razonsocial from kardex.proformas as p inner join public.personas as per on(p.codpersona=per.codpersona) ".$filtro)->result_array();
			foreach ($clientes as $key => $value) {
				$clientes[$key]["pedidos"] = $this->db->query("select pro.codigo,pro.descripcion as producto,uni.descripcion as unidad,round(pd.cantidad,2) as cantidad, round(pd.preciounitario,2) as preciounitario,round(pd.subtotal,2) as subtotal, round(pd.preciorefunitario,2) as preciorefunitario, round(pd.preciorefunitario * pd.cantidad, 2) as subtotalref from kardex.proformas as p inner join kardex.proformasdetalle as pd on (p.codproforma=pd.codproforma) inner join almacen.productos as pro on(pd.codproducto=pro.codproducto) inner join almacen.unidades as uni on(pd.codunidad=uni.codunidad) where p.codpersona=".$value["codpersona"])->result_array();
				$clientes[$key]["totales"] = $this->db->query("select round(sum(pd.cantidad),2) as cantidad, round(sum(pd.cantidad * pd.preciounitario),2) as total, round(sum(pd.preciorefunitario * pd.cantidad), 2) as totalref from kardex.proformas as p inner join kardex.proformasdetalle as pd on (p.codproforma=pd.codproforma) inner join almacen.productos as pro on(pd.codproducto=pro.codproducto) inner join almacen.unidades as uni on(pd.codunidad=uni.codunidad) where p.codpersona=".$value["codpersona"])->result_array();
			}
			echo json_encode($clientes);
		}
	}
	function pdf_cliente_proformas(){
		if ($_GET["datos"]) {
			$this->request = json_decode($_GET["datos"]);

			$this->load->library('Pdf2'); $pdf = new Pdf2(); $pdf->AddPage();
			$pdf->pdf_header("REPORTE DE PROFORMAS POR CLIENTE DEL ".$this->request->fechadesde." AL ".$this->request->fechahasta,"");

			if ($this->request->codpersona==0) {
				$filtro = "";
			}else{
				$filtro = "where p.codpersona=".$this->request->codpersona;
			}

			if ($this->request->codsucursal==0) {
				$sucursal = "";
			}else{
				$sucursal = "AND p.codsucursal=".$this->request->codsucursal;
			}

			if($this->request->estado==0){
				$estado = '';
			}else if($this->request->estado==1){
				$estado = ' AND p.estado=1 AND p.estadoproceso=0';
			}
			else if($this->request->estado==2){
				$estado = ' AND p.estado=1 AND p.estadoproceso=1';
			}else{
				$estado = ' AND p.estado=0';
			}

			$clientes = $this->db->query("select distinct(p.codpersona) as codpersona, per.razonsocial from kardex.proformas as p inner join public.personas as per on(p.codpersona=per.codpersona) ".$filtro)->result_array();
			foreach ($clientes as $key => $value) {
				$pedidos = $this->db->query("select pro.codigo,pro.descripcion as producto,uni.descripcion as unidad,round(pd.cantidad,2) as cantidad, round(pd.preciounitario,2) as preciounitario,round(pd.subtotal,2) as subtotal, round(pd.preciorefunitario,2) as preciorefunitario, round(pd.preciorefunitario * pd.cantidad, 2) as subtotalref,p.fechaproforma,s.descripcion as sucursal from kardex.proformas as p inner join kardex.proformasdetalle as pd on (p.codproforma=pd.codproforma) inner join public.sucursales as s on (p.codsucursal=s.codsucursal) inner join almacen.productos as pro on(pd.codproducto=pro.codproducto) inner join almacen.unidades as uni on(pd.codunidad=uni.codunidad) where p.codpersona=".$value["codpersona"]." ".$sucursal." ".$estado." ")->result_array();

				$pdf->SetFont('Arial','B',9);
				$pdf->Cell(0,5,"CLIENTE: ".utf8_decode($value["razonsocial"]),1,0,'L'); $pdf->Ln();
				$columnas = array("CODIGO","PRODUCTO","UNIDAD","CANTIDAD","PRECIO","TOTAL","FECHA P.","SUCURSAL");
				$w = array(15,65,15,15,20,15,15,30); $pdf->pdf_tabla_head($columnas,$w,7);

				$cantidad = 0; $total = 0; $totalref = 0;
				$pdf->SetWidths($w); $pdf->SetLineHeight(5); $pdf->SetFont('Arial','',7);
				foreach ($pedidos as $v) {
					$cantidad = $cantidad + $v["cantidad"];
					$total = $total + $v["subtotal"];
					$totalref = $totalref + $v["subtotalref"];

					$datos = array($v["codigo"]);
					array_push($datos,utf8_decode($v["producto"]));
					array_push($datos,$v["unidad"]);
					array_push($datos,number_format($v["cantidad"],2));

					array_push($datos,number_format($v["preciounitario"],2));
					array_push($datos,number_format($v["subtotal"],2));
					array_push($datos,utf8_decode($v["fechaproforma"]));
					array_push($datos,utf8_decode($v["sucursal"]));
	                $pdf->Row($datos);
				}
				$pdf->Cell(array_sum($w),0,'','T'); $pdf->Ln();

				$pdf->SetFont('Arial','B',8);
				$pdf->Cell(95,5,"TOTALES",1,0,'R');
			    $pdf->Cell(15,5,number_format($cantidad,2),1,"R");
			    $pdf->Cell(20,5,"",1,"R");
			    $pdf->Cell(15,5,number_format($total,2),1,"R");
			    $pdf->Cell(45,5,"",1,"R"); $pdf->Ln();$pdf->Ln();
			}

			$pdf->SetTitle("phuyu Peru - Reporte Proformas por Cliente"); $pdf->Output();
		}else{
			$this->load->view("phuyu/404");
		}
	}
	function excel_cliente_proformas(){
		if ($_GET["datos"]) {
			$this->request = json_decode($_GET["datos"]);
			
			if ($this->request->codpersona==0) {
				$filtro = "";
			}else{
				$filtro = "where p.codpersona=".$this->request->codpersona;
			}
			if ($this->request->codsucursal==0) {
				$sucursal = "";
			}else{
				$sucursal = "AND p.codsucursal=".$this->request->codsucursal;
			}
			if($this->request->estado==0){
				$estado = '';
			}else if($this->request->estado==1){
				$estado = ' AND p.estado=1 AND p.estadoproceso=0';
			}
			else if($this->request->estado==2){
				$estado = ' AND p.estado=1 AND p.estadoproceso=1';
			}else{
				$estado = ' AND p.estado=0';
			}
			$clientes = $this->db->query("select distinct(p.codpersona) as codpersona, per.razonsocial from kardex.proformas as p inner join public.personas as per on(p.codpersona=per.codpersona) ".$filtro)->result_array();
			foreach ($clientes as $key => $value) {
				$clientes[$key]["pedidos"] = $this->db->query("select pro.codigo,pro.descripcion as producto,uni.descripcion as unidad,round(pd.cantidad,2) as cantidad, round(pd.preciounitario,2) as preciounitario,round(pd.subtotal,2) as subtotal, round(pd.preciorefunitario,2) as preciorefunitario, round(pd.preciorefunitario * pd.cantidad, 2) as subtotalref,p.fechaproforma,s.descripcion as sucursal from kardex.proformas as p inner join kardex.proformasdetalle as pd on (p.codproforma=pd.codproforma) inner join public.sucursales as s on (p.codsucursal=s.codsucursal) inner join almacen.productos as pro on(pd.codproducto=pro.codproducto) inner join almacen.unidades as uni on(pd.codunidad=uni.codunidad) where p.codpersona=".$value["codpersona"]." ".$sucursal." ".$estado." ")->result_array();
			}
			$fechadesde = $this->request->fechadesde;
			$fechahasta = $this->request->fechahasta;
			$this->load->view("reportes/proformas/xls_clientes.php",compact("clientes","fechadesde","fechahasta"));
		}else{
			$this->load->view("phuyu/404");
		}
	}
}