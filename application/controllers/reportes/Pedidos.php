<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Pedidos extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			$sucursales = $this->db->query("select *from public.sucursales where estado=1")->result_array();
			$vendedores = $this->db->query("select persona.codpersona,persona.razonsocial from public.personas as persona inner join public.empleados as empleado on(persona.codpersona=empleado.codpersona) where empleado.estado=1 and empleado.codcargo=4")->result_array();
			$this->load->view("reportes/pedidos/index",compact("sucursales","vendedores"));
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function buscar_producto_pedidos(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$filtro = "(REPLACE(UPPER(pro.descripcion),' ','%') like REPLACE (UPPER('%".$this->request->buscar."%'),' ','%') or UPPER(pro.codigo) like UPPER('%".$this->request->buscar."%') )";
			$lista = $this->db->query("select pro.codigo,pro.descripcion as producto,uni.descripcion as unidad,per.razonsocial as cliente,round(pd.cantidad,2) as cantidad, round(pd.preciounitario,2) as preciounitario,round(pd.subtotal,2) as subtotal, round(pd.preciorefunitario,2) as preciorefunitario, round(pd.preciorefunitario * pd.cantidad, 2) as subtotalref from kardex.pedidos as p inner join public.personas as per on(p.codpersona=per.codpersona) inner join kardex.pedidosdetalle as pd on (p.codpedido=pd.codpedido) inner join almacen.productos as pro on(pd.codproducto=pro.codproducto) inner join almacen.unidades as uni on(pd.codunidad=uni.codunidad) where ".$filtro)->result_array();
			$totales = $this->db->query("select round(sum(pd.cantidad),2) as cantidad, round(sum(pd.cantidad * pd.preciounitario),2) as total, round(sum(pd.preciorefunitario * pd.cantidad), 2) as totalref from kardex.pedidos as p inner join public.personas as per on(p.codpersona=per.codpersona) inner join kardex.pedidosdetalle as pd on (p.codpedido=pd.codpedido) inner join almacen.productos as pro on(pd.codproducto=pro.codproducto) inner join almacen.unidades as uni on(pd.codunidad=uni.codunidad) where ".$filtro)->result_array();
			$data["lista"] = $lista; $data["totales"] = $totales;
			echo json_encode($data);
		}
	}

	function consulta_reporte_pedidos(){
		if (isset($_SESSION["phuyu_codusuario"])) {
			$this->request = json_decode(file_get_contents('php://input'));

			$sucursales = '';
			$almacen = '';
			if ($this->request->codsucursal!=0) {
				$sucursales = ' and kardex.codsucursal='.$this->request->codsucursal;
			}

			if($this->request->estado==0){
				$estado = '';
			}else if($this->request->estado==1){
				$estado = ' AND kardex.estado=1 AND kardex.estadoproceso=0';
			}
			else if($this->request->estado==2){
				$estado = ' AND kardex.estado=1 AND kardex.estadoproceso=1';
			}else{
				$estado = ' AND kardex.estado=0';
			}

			$valorventatotal = 0; $igvtotal=0; $icbpertotal=0;$totalgeneral=0;

			$lista = $this->db->query("select personas.documento,kardex.cliente,kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante,kardex.condicionpago, kardex.nrocomprobante, kardex.fechapedido,round(kardex.valorventa,2) AS valorventa,round(kardex.igv,2) AS IGV, kardex.descglobal, round(kardex.importe,2) AS importe,kardex.condicionpago, comprobantes.descripcion as tipo from kardex.pedidos as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.fechapedido>='".$this->request->fechadesde."' and kardex.fechapedido<='".$this->request->fechahasta."' ".$sucursales." ".$estado." order by kardex.seriecomprobante, kardex.nrocomprobante")->result_array();

			foreach ($lista as $key => $value) {
				$valorventatotal = $valorventatotal + (double)$value["valorventa"];
				$igvtotal = $igvtotal + (double)$value['igv'];
				$totalgeneral = $totalgeneral + (double)$value['importe'];
			}

			$totalreporte[0]["valorventatotal"] = number_format($valorventatotal,2,".","");
			$totalreporte[0]["igvtotal"] = number_format($igvtotal,2,".","");
			$totalreporte[0]["totalgeneral"] = number_format($totalgeneral,2,".","");

			echo json_encode(['lista'=>$lista,'totalreporte'=>$totalreporte]);
		}
	}

	function pdf_pedidos(){
		if ($_GET["datos"]) {
			$this->request = json_decode($_GET["datos"]);

			$this->load->library('Pdf2'); $pdf = new Pdf2(); $pdf->AddPage();
			$pdf->pdf_header("REPORTE PEDIDOS","");

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
				$pdf->SetFont('Arial','',7);
				$pdf->pdf_header_titulo("SUCURSAL: ".$value["descripcion"]." DEL ".$this->request->fechadesde." AL ".$this->request->fechahasta);

				$columnas = array("ID","COMPROBANTE","FECHA","CLIENTE","V. VENTA","IGV","IMPORTE","ESTADO","CANJEADO");
				$w = array(10,25,15,55,15,15,15,20,20); $pdf->pdf_tabla_head($columnas,$w,7);

				$lista = $this->db->query("select p.*,per.razonsocial as cliente from kardex.pedidos as p inner join public.personas as per on(p.codpersona=per.codpersona) where p.codsucursal = ".$value["codsucursal"]." and p.fechapedido between '".$this->request->fechadesde."' and '".$this->request->fechahasta."' ".$estado." ORDER BY codpedido ASC")->result_array();
				
				$valorventa = 0; $total = 0; $igv = 0;$item= 1;
				$pdf->SetWidths($w); $pdf->SetLineHeight(5); $pdf->SetFont('Arial','',7);
				foreach ($lista as $key => $value) {
					$valorventa = $valorventa + $value["valorventa"];
					$igv = $igv + $value["igv"];
					$total = $total + $value["importe"];

					$estado = ($value["estadoproceso"]==0) ? 'PENDIENTE' : 'CANJEADO';

					$estadopedido = ($value["estado"]==1) ? 'CONCRETADO' : 'ANULADO';

					$datos = array($value["codpedido"]);
					array_push($datos,utf8_decode($value["seriecomprobante"]."-".$value["nrocomprobante"]));
					array_push($datos,utf8_decode($value["fechapedido"]));
					array_push($datos,utf8_decode($value["cliente"]));
					array_push($datos,number_format($value["valorventa"],2));
					array_push($datos,number_format($value["igv"],2));
					array_push($datos,number_format($value["importe"],2));
					array_push($datos,utf8_decode($estadopedido));
					array_push($datos,utf8_decode($estado));
	                $pdf->Row($datos);
	                $item++;
				}
				$pdf->Cell(array_sum($w),0,'','T'); $pdf->Ln();

				$pdf->SetFont('Arial','B',7);
				$pdf->Cell(105,5,"TOTALES",1,0,'R');
			    $pdf->Cell(15,5,number_format($valorventa,2),1,"R");
			    $pdf->Cell(15,5,number_format($igv,2),1,"R");
			    $pdf->Cell(15,5,number_format($total,2),1,"R");
			}

			$pdf->SetTitle("phuyu Peru - Reporte Pedidos"); $pdf->Output();
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function pdf_pedidos_detallado(){
		if ($_GET["datos"]) {
			$this->request = json_decode($_GET["datos"]);

			$this->load->library('Pdf2'); $pdf = new Pdf2(); $pdf->AddPage();
			$pdf->pdf_header("REPORTE PEDIDOS DETALLADOS","");

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

				$lista = $this->db->query("select p.*,per.razonsocial as cliente from kardex.pedidos as p inner join public.personas as per on(p.codpersona=per.codpersona) where p.codsucursal = ".$value["codsucursal"]." and p.fechapedido between '".$this->request->fechadesde."' and '".$this->request->fechahasta."' ".$estado." ORDER BY codpedido ASC")->result_array();
				
				$valorventa = 0; $total = 0; $igv = 0;$item= 1;
				foreach ($lista as $key => $value) {
				$pdf->SetWidths($w); $pdf->SetLineHeight(5); $pdf->SetFont('Arial','',7);
					$valorventa = $valorventa + $value["valorventa"];
					$igv = $igv + $value["igv"];
					$total = $total + $value["importe"];

					$estado = ($value["estadoproceso"]==0) ? 'PENDIENTE' : 'CANJEADO';

					$datos = array($value["codpedido"]);
					array_push($datos,utf8_decode($value["seriecomprobante"]."-".$value["nrocomprobante"]));
					array_push($datos,utf8_decode($value["fechapedido"]));
					array_push($datos,utf8_decode($value["cliente"]));
					array_push($datos,number_format($value["valorventa"],2));
					array_push($datos,number_format($value["igv"],2));
					array_push($datos,number_format($value["importe"],2));
					array_push($datos,utf8_decode($estado));
	                $pdf->Row($datos);
	                $item++;

	                $detalle = $this->db->query("select pd.*,pro.descripcion as producto,uni.descripcion as unidad from kardex.pedidosdetalle pd inner join almacen.productos as pro on(pd.codproducto=pro.codproducto) inner join almacen.unidades as uni on(pd.codunidad=uni.codunidad) where pd.codpedido=".$value["codpedido"])->result_array();

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

			$pdf->SetTitle("phuyu Peru - Reporte Pedidos detallado"); $pdf->Output();
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function excel_pedidos(){
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
			
				$lista = $this->db->query("select p.*,per.razonsocial as cliente from kardex.pedidos as p inner join public.personas as per on(p.codpersona=per.codpersona) where p.codsucursal = ".$value["codsucursal"]." and p.fechapedido between '".$this->request->fechadesde."' and '".$this->request->fechahasta."' ".$estado." ORDER BY codpedido ASC")->result_array();

				$sucursal[$key]["lista"] = $lista;
			}

			$fechadesde = $this->request->fechadesde;
			$fechahasta = $this->request->fechahasta;

			$this->load->view("reportes/pedidos/xls_pedidos.php",compact("sucursal","fechadesde","fechahasta"));
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function excel_pedidos_detallado(){
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
			
				$lista = $this->db->query("select p.*,per.razonsocial as cliente from kardex.pedidos as p inner join public.personas as per on(p.codpersona=per.codpersona) where p.codsucursal = ".$value["codsucursal"]." and p.fechapedido between '".$this->request->fechadesde."' and '".$this->request->fechahasta."' ".$estado." ORDER BY codpedido ASC")->result_array();

				$sucursal[$key]["lista"] = $lista;
			}

			$fechadesde = $this->request->fechadesde;
			$fechahasta = $this->request->fechahasta;

			$this->load->view("reportes/pedidos/xls_pedidosdetallado.php",compact("sucursal","fechadesde","fechahasta"));
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function pdf_producto_pedidos(){
		if ($_GET["datos"]) {
			$this->request = json_decode($_GET["datos"]);

			$this->load->library('Pdf2'); $pdf = new Pdf2(); $pdf->AddPage();
			$pdf->pdf_header("REPORTE PEDIDOS POR PRODUCTOS","");

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
				$lista = $this->db->query("select pro.codigo,pro.descripcion as producto,uni.descripcion as unidad,per.razonsocial as cliente,round(pd.cantidad,2) as cantidad, round(pd.preciounitario,2) as preciounitario,round(pd.subtotal,2) as subtotal, round(pd.preciorefunitario,2) as preciorefunitario, round(pd.preciorefunitario * pd.cantidad, 2) as subtotalref,p.fechapedido from kardex.pedidos as p inner join public.personas as per on(p.codpersona=per.codpersona) inner join kardex.pedidosdetalle as pd on (p.codpedido=pd.codpedido) inner join almacen.productos as pro on(pd.codproducto=pro.codproducto) inner join almacen.unidades as uni on(pd.codunidad=uni.codunidad) where ".$filtro." and p.codsucursal = ".$value["codsucursal"]." and p.fechapedido between '".$this->request->fechadesde."' and '".$this->request->fechahasta."' ".$estado."")->result_array();
				
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
					array_push($datos,$value["fechapedido"]);
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

			$pdf->SetTitle("phuyu Peru - Reporte Pedidos por Producto"); $pdf->Output();
		}else{
			$this->load->view("phuyu/404");
		}
	}
	function excel_producto_pedidos(){
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
				$lista = $this->db->query("select pro.codigo,pro.descripcion as producto,uni.descripcion as unidad,per.razonsocial as cliente,round(pd.cantidad,2) as cantidad, round(pd.preciounitario,2) as preciounitario,round(pd.subtotal,2) as subtotal, round(pd.preciorefunitario,2) as preciorefunitario, round(pd.preciorefunitario * pd.cantidad, 2) as subtotalref,p.fechapedido from kardex.pedidos as p inner join public.personas as per on(p.codpersona=per.codpersona) inner join kardex.pedidosdetalle as pd on (p.codpedido=pd.codpedido) inner join almacen.productos as pro on(pd.codproducto=pro.codproducto) inner join almacen.unidades as uni on(pd.codunidad=uni.codunidad) where ".$filtro." and p.codsucursal = ".$value["codsucursal"]." and p.fechapedido between '".$this->request->fechadesde."' and '".$this->request->fechahasta."' ".$estado."")->result_array();

				$sucursal[$key]["lista"] = $lista;
			}

			$fechadesde = $this->request->fechadesde;
			$fechahasta = $this->request->fechahasta;

			$this->load->view("reportes/pedidos/xls_productos.php",compact("sucursal","fechadesde","fechahasta"));
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
			$clientes = $this->db->query("select distinct(p.codpersona) as codpersona, per.razonsocial from kardex.pedidos as p inner join public.personas as per on(p.codpersona=per.codpersona) ".$filtro)->result_array();
			foreach ($clientes as $key => $value) {
				$clientes[$key]["pedidos"] = $this->db->query("select pro.codigo,pro.descripcion as producto,uni.descripcion as unidad,round(pd.cantidad,2) as cantidad, round(pd.preciounitario,2) as preciounitario,round(pd.subtotal,2) as subtotal, round(pd.preciorefunitario,2) as preciorefunitario, round(pd.preciorefunitario * pd.cantidad, 2) as subtotalref from kardex.pedidos as p inner join kardex.pedidosdetalle as pd on (p.codpedido=pd.codpedido) inner join almacen.productos as pro on(pd.codproducto=pro.codproducto) inner join almacen.unidades as uni on(pd.codunidad=uni.codunidad) where p.codpersona=".$value["codpersona"])->result_array();
				$clientes[$key]["totales"] = $this->db->query("select round(sum(pd.cantidad),2) as cantidad, round(sum(pd.cantidad * pd.preciounitario),2) as total, round(sum(pd.preciorefunitario * pd.cantidad), 2) as totalref from kardex.pedidos as p inner join kardex.pedidosdetalle as pd on (p.codpedido=pd.codpedido) inner join almacen.productos as pro on(pd.codproducto=pro.codproducto) inner join almacen.unidades as uni on(pd.codunidad=uni.codunidad) where p.codpersona=".$value["codpersona"])->result_array();
			}
			echo json_encode($clientes);
		}
	}
	function pdf_cliente_pedidos(){
		if ($_GET["datos"]) {
			$this->request = json_decode($_GET["datos"]);

			$this->load->library('Pdf2'); $pdf = new Pdf2(); $pdf->AddPage();
			$pdf->pdf_header("REPORTE DE PEDIDOS POR CLIENTE DEL ".$this->request->fechadesde." AL ".$this->request->fechahasta,"");

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

			$clientes = $this->db->query("select distinct(p.codpersona) as codpersona, per.razonsocial from kardex.pedidos as p inner join public.personas as per on(p.codpersona=per.codpersona) ".$filtro)->result_array();
			foreach ($clientes as $key => $value) {
				$pedidos = $this->db->query("select pro.codigo,pro.descripcion as producto,uni.descripcion as unidad,round(pd.cantidad,2) as cantidad, round(pd.preciounitario,2) as preciounitario,round(pd.subtotal,2) as subtotal, round(pd.preciorefunitario,2) as preciorefunitario, round(pd.preciorefunitario * pd.cantidad, 2) as subtotalref,p.fechapedido,s.descripcion as sucursal from kardex.pedidos as p inner join kardex.pedidosdetalle as pd on (p.codpedido=pd.codpedido) inner join public.sucursales as s on (p.codsucursal=s.codsucursal) inner join almacen.productos as pro on(pd.codproducto=pro.codproducto) inner join almacen.unidades as uni on(pd.codunidad=uni.codunidad) where p.codpersona=".$value["codpersona"]." ".$sucursal." ".$estado." ")->result_array();

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
					array_push($datos,utf8_decode($v["fechapedido"]));
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

			$pdf->SetTitle("phuyu Peru - Reporte Pedidos por Cliente"); $pdf->Output();
		}else{
			$this->load->view("phuyu/404");
		}
	}
	function excel_cliente_pedidos(){
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
			$clientes = $this->db->query("select distinct(p.codpersona) as codpersona, per.razonsocial from kardex.pedidos as p inner join public.personas as per on(p.codpersona=per.codpersona) ".$filtro)->result_array();
			foreach ($clientes as $key => $value) {
				$clientes[$key]["pedidos"] = $this->db->query("select pro.codigo,pro.descripcion as producto,uni.descripcion as unidad,round(pd.cantidad,2) as cantidad, round(pd.preciounitario,2) as preciounitario,round(pd.subtotal,2) as subtotal, round(pd.preciorefunitario,2) as preciorefunitario, round(pd.preciorefunitario * pd.cantidad, 2) as subtotalref,p.fechapedido,s.descripcion as sucursal from kardex.pedidos as p inner join kardex.pedidosdetalle as pd on (p.codpedido=pd.codpedido) inner join public.sucursales as s on (p.codsucursal=s.codsucursal) inner join almacen.productos as pro on(pd.codproducto=pro.codproducto) inner join almacen.unidades as uni on(pd.codunidad=uni.codunidad) where p.codpersona=".$value["codpersona"]." ".$sucursal." ".$estado." ")->result_array();
			}
			$fechadesde = $this->request->fechadesde;
			$fechahasta = $this->request->fechahasta;
			$this->load->view("reportes/pedidos/xls_clientes.php",compact("clientes","fechadesde","fechahasta"));
		}else{
			$this->load->view("phuyu/404");
		}
	}

	// REPORTES DE VENDEDORES EN PEDIDOS

	function pdf_pedidos_vendedor(){
		if (isset($_SESSION["phuyu_codusuario"])) {
			if ($_GET["datos"]) {
				$this->request = json_decode($_GET["datos"]);

				$color_letra = "B";
				if (isset($_GET["tipo"])) {
					$color_letra = "";
				}

				$this->load->library('Pdf2'); $pdf = new Pdf2(); $pdf->AddPage();
				$pdf->pdf_header("REPORTE DE PEDIDOS VENDEDOR","");

				$pdf->SetFont('Arial','B',10); $pdf->setFillColor(245,245,245);
		        if ($this->request->codvendedor == "") {
					$vendedor_texto = 'PEDIDOS DESDE '.$this->request->fechadesde.' HASTA '.$this->request->fechahasta.' - TODOS LOS VENDEDORES';
					$filtro = "";
				}else{
					$vendedor = $this->db->query("select documento, razonsocial from public.personas where codpersona=".$this->request->codvendedor)->result_array();
					$vendedor_texto = 'PEDIDOS DESDE '.$this->request->fechadesde.' HASTA '.$this->request->fechahasta.' - VENDEDOR: '.$vendedor[0]["razonsocial"];
					$filtro = "and kardex.codempleado=".$this->request->codvendedor;
				}

				if($this->request->estado==0){
					$estado = '';
				}else if($this->request->estado==1){
					$estado = ' AND kardex.estado=1 AND kardex.estadoproceso=0';
				}
				else if($this->request->estado==2){
					$estado = ' AND kardex.estado=1 AND kardex.estadoproceso=1';
				}else{
					$estado = ' AND kardex.estado=0';
				}

		        $pdf->Cell(0,7,$vendedor_texto,0,1,'L',1); $pdf->Ln(2);

				$columnas = array("N°","FECHA","DOCUMENTO","DNI/RUC","RAZON SOCIAL","SUBTOTAL","IGV","TOTAL");
				$w = array(10,15,22,20,68,20,15,20); $pdf->pdf_tabla_head($columnas,$w,8);

				$lista = $this->db->query("select personas.documento,kardex.cliente,personas.coddocumentotipo, kardex.codpedido, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.estado, kardex.condicionpago,kardex.nrocomprobante, kardex.fechapedido,kardex.valorventa,kardex.igv, kardex.importe,kardex.estado,comprobantes.abreviatura as tipo from kardex.pedidos as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join public.empleados as empleados on (kardex.codempleado = empleados.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codsucursal=".$this->request->codsucursal." and kardex.fechapedido>='".$this->request->fechadesde."' and kardex.fechapedido<='".$this->request->fechahasta."' ".$estado." ".$filtro." and empleados.codcargo=4 order by kardex.fechapedido, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.nrocomprobante")->result_array();

				$item = 0; $importe = 0; $valorventa = 0; $igv = 0;
				foreach ($lista as $key => $value) { 
					$item = $item + 1; 
					$valorventa = $valorventa + $value["valorventa"];
					$igv = $igv + $value["igv"];
					$importe = $importe + $value["importe"];
					$pdf->SetWidths(array(10,15,22,20,68,20,15,20));
	            	$pdf->SetLineHeight(5); $pdf->SetFont('Arial',$color_letra,7);

					$datos = array("0".$item);
					array_push($datos,$value["fechapedido"]);
					array_push($datos,$value["seriecomprobante"]."-".$value["nrocomprobante"]);
					array_push($datos,utf8_decode($value["documento"]));
					array_push($datos,utf8_decode($value["cliente"]));

					array_push($datos,number_format($value["valorventa"],2));
					array_push($datos,number_format($value["igv"],2));
					array_push($datos,number_format($value["importe"],2));
	                $pdf->Row($datos);

	                if ($color_letra=="B") {
	                	$detalle = $this->db->query("select kd.*,p.descripcion as producto,u.descripcion as unidad from kardex.pedidosdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codpedido=".$value["codpedido"]." and kd.estado=1 order by kd.item")->result_array();

		                $columnas = array("CANT","DESCRIPCION DETALLE VENTA","UNI.MED","P.UNITARIO","IGV","IMPORTE"); $wd = array(10,105,20,20,15,20); 
		                for($i=0;$i<count($columnas);$i++){
				            $pdf->Cell($wd[$i],5,utf8_decode($columnas[$i]),1,0,'L');
				        } $pdf->Ln();

				        $pdf->SetWidths(array(10,105,20,20,15,20)); $pdf->SetLineHeight(5); $pdf->SetFont('Arial','',7);
				        foreach ($detalle as $v) {
				        	$datos = array(number_format($v["cantidad"],0));
							array_push($datos,utf8_decode($v["producto"].' '.$v["descripcion"]));
							array_push($datos,utf8_decode($v["unidad"]));

							array_push($datos,number_format($v["preciounitario"],2));
							array_push($datos,number_format($v["igv"],2));
							array_push($datos,number_format($v["subtotal"],2));
			                $pdf->Row($datos);
				        }
	                }
				}
				$pdf->Cell(array_sum($w),0,'','T'); $pdf->Ln();

				$pdf->SetFont('Arial','B',8);
				$pdf->Cell(135,5,"TOTALES",1,0,'R');
			    $pdf->Cell(20,5,number_format($valorventa,2),1,"R");
			    $pdf->Cell(15,5,number_format($igv,2),1,"R");
			    $pdf->Cell(20,5,number_format($importe,2),1,"R");
			
				$pdf->SetTitle("phuyu Peru - Reporte Ventas - Vendedor"); $pdf->Output();
			}
		}
	}

	function excel_pedidos_vendedor(){
		if (isset($_SESSION["phuyu_codusuario"])) {
			if ($_GET["datos"]) { 
				//echo "hola";exit;
				$this->request = json_decode($_GET["datos"]);
				$this->tipos = (isset($_GET["tipo"]) ? "resumen" : null);
				$tipos = $this->tipos;

		        if ($this->request->codvendedor == "") {
					$vendedor_texto = 'PEDIDOS DESDE '.$this->request->fechadesde.' HASTA '.$this->request->fechahasta.' - TODOS LOS VENDEDORES';
					$filtro = "";
				}else{
					$vendedor = $this->db->query("select documento, razonsocial from public.personas where codpersona=".$this->request->codvendedor)->result_array();
					$vendedor_texto = 'PEDIDOS DESDE '.$this->request->fechadesde.' HASTA '.$this->request->fechahasta.' - VENDEDOR: '.$vendedor[0]["razonsocial"];
					$filtro = "and kardex.codempleado=".$this->request->codvendedor;
				}

				if($this->request->estado==0){
					$estado = '';
				}else if($this->request->estado==1){
					$estado = ' AND kardex.estado=1 AND kardex.estadoproceso=0';
				}
				else if($this->request->estado==2){
					$estado = ' AND kardex.estado=1 AND kardex.estadoproceso=1';
				}else{
					$estado = ' AND kardex.estado=0';
				}

				$lista = $this->db->query("select personas.documento,kardex.cliente,personas.coddocumentotipo, kardex.codpedido, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.estado, kardex.condicionpago,kardex.nrocomprobante, kardex.fechapedido,kardex.valorventa,kardex.igv, kardex.importe,kardex.estado,comprobantes.abreviatura as tipo from kardex.pedidos as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join public.empleados as empleados on (kardex.codempleado = empleados.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codsucursal=".$this->request->codsucursal." and kardex.fechapedido>='".$this->request->fechadesde."' and kardex.fechapedido<='".$this->request->fechahasta."' ".$estado." ".$filtro." and empleados.codcargo=4 order by kardex.fechapedido, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.nrocomprobante")->result_array();

				$fechadesde= $this->request->fechadesde; $fechahasta = $this->request->fechahasta;
				
				$this->load->view("reportes/pedidos/pedidosvendedorxls",compact("vendedor_texto","lista","tipos"));
			}
		}
	}
}