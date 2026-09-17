<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Cuotas extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
		$this->load->model("Creditos_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			$monedas = $this->db->query("select *from caja.monedas where estado=1 order by codmoneda asc")->result_array();
			$this->load->view("reportes/cuotas/index",compact("monedas"));
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function lista(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$this->request->fecha_desde = (isset($this->request->fecha_desde)) ? $this->request->fecha_desde : $this->request->fecha_hasta;
			if($this->request->codpersona == 0){
				$codpersona = '';
			}else{
				$codpersona = "AND codpersona=".$this->request->codpersona;
			}
			if($this->request->codmoneda == 0){
				$codmoneda = '';
			}else{
				$codmoneda = "AND codmoneda=".$this->request->codmoneda;
			}
			$estado = '';
			if($this->request->estado==1){
				$estado = ' AND saldocuota>0';
			}
			if($this->request->estado==2){
				$estado = ' AND saldocuota=0';
			}
			if($this->request->estado==3){
				$estado = " AND fechavencecuota<'".$this->request->fecha_hasta."' AND estadocuota=1";
			}
			$lista = $this->db->query("SELECT codcredito,codsucursalcredito, codcajacredito, estadocuota, codpersona, razonsocial, cliente, direccion, codmoneda,monedasimbolo, tipocambio, codkardex, tipo, fechacredito, fechainiciocredito, fechavencimientocredito, importecredito, tasainterescredito, comprobantereferencia, nrocuota, nroletra, nrounicodepago, fechavencecuota, fechapagocuota, importecuota, saldocuota, interescuota, totalcuota, tipoynrodocumento, ('".$this->request->fecha_hasta."' - fechavencecuota) AS diasvencidos
			  FROM caja.v_cuotascreditos
			  WHERE codsucursalcredito = 1 AND fechavencecuota <= '".$this->request->fecha_hasta."' AND tipo = ".(int)$this->request->tipo." ".$codpersona." ".$codmoneda." ".$estado."
			  ORDER BY fechavencecuota")->result_array();
			$totalimporte=0;$totalinteres=0;$totalsaldo=0;
			foreach ($lista as $key => $value) {
				$totalimporte = $totalimporte + $value["importecuota"];
				$totalinteres = $totalinteres + $value["interescuota"];
				$totalsaldo = $totalsaldo + $value["saldocuota"];
			}

			$total["totalimporte"] = number_format($totalimporte,2,".","");
			$total["totalinteres"] = number_format($totalinteres,2,".","");
			$total["totalsaldo"] = number_format($totalsaldo,2,".","");

			echo json_encode(array("socios" => $lista, "total"=>$total));
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function pdf_cuotas(){
		if ($_GET["datos"]) {
			$this->request = json_decode($_GET["datos"]);


			if ($this->request->tipo==1) {
				$tipo = "CREDITOS POR CUOTAS"; $socio = "CLIENTE"; $tipo_texto = "COBRANZA";
			}else{
				$tipo = "CREDITOS POR CUOTAS"; $socio = "PROVEEDOR"; $tipo_texto = "PAGO";
			}


			$this->load->library('Pdf2'); $pdf = new Pdf2(); $pdf->AddPage('L');

			if($this->request->codpersona == 0){
				$codpersona = '';
			}else{
				$codpersona = "AND codpersona=".$this->request->codpersona;
			}
			if($this->request->codmoneda == 0){
				$codmoneda = '';
			}else{
				$codmoneda = "AND codmoneda=".$this->request->codmoneda;
			}
			$lista = $this->db->query("SELECT codcredito,codsucursalcredito, codcajacredito, estadocuota, codpersona, razonsocial, cliente, direccion, codmoneda,monedasimbolo, tipocambio, codkardex, tipo, fechacredito, fechainiciocredito, fechavencimientocredito, importecredito, tasainterescredito, comprobantereferencia, nrocuota, nroletra, nrounicodepago, fechavencecuota, fechapagocuota, importecuota, saldocuota, interescuota, totalcuota, tipoynrodocumento, ('".$this->request->fecha_hasta."' - fechavencecuota) AS diasvencidos
			  FROM caja.v_cuotascreditos
			  WHERE codsucursalcredito = 1 AND fechavencecuota <= '".$this->request->fecha_hasta."' AND tipo = ".(int)$this->request->tipo." AND  estadocuota = 1 ".$codpersona." ".$codmoneda."
			  ORDER BY fechavencecuota")->result_array();

			$pdf->pdf_header($tipo,"");
			$hasta = explode("-", $this->request->fecha_hasta);
			$pdf->Cell(0,5,"REPORTE DE CUOTAS HASTA ".$hasta[2]."-".$hasta[1]."-".$hasta[0],0,"C"); $pdf->ln(7);

			$columnas = array("N° CRED.","DOCUMENTO","RAZON SOCIAL","COMPROBANTE","MONEDA","FECHA C.","FECHA V.","DIAS V.","N° CUOTA","LETRA","N° PAGO UNI.","IMPORTE","INTERES","SALDO");
			$w = array(13,24,60,27,13,15,15,12,15,15,18,18,15,18); 
			$pdf->pdf_tabla_head($columnas,$w,7);

			$pdf->SetWidths(array(13,24,60,27,13,15,15,12,15,15,18,18,15,18));
            $pdf->SetLineHeight(5); 
			$pdf->SetFont('Arial','',7);
			$totalimporte = 0; $totalinteres = 0; $totalsaldo = 0;
			foreach ($lista as $key => $v) {
				$totalimporte = $totalimporte + $v["importecuota"];
				$totalinteres = $totalinteres + $v["interescuota"];
				$totalsaldo = $totalsaldo + $v["saldocuota"];
				$datos = array($v["codcredito"]);
				array_push($datos,utf8_decode($v["tipoynrodocumento"]));
				array_push($datos,utf8_decode($v["razonsocial"]));
				array_push($datos,utf8_decode($v["comprobantereferencia"]));
				array_push($datos,utf8_decode($v["monedasimbolo"]));
				array_push($datos,utf8_decode($v["fechainiciocredito"]));
				array_push($datos,utf8_decode($v["fechavencecuota"]));
				array_push($datos,utf8_decode($v["diasvencidos"]));
				array_push($datos,utf8_decode($v["nrocuota"]));
				array_push($datos,utf8_decode($v["nroletra"]));
				array_push($datos,utf8_decode($v["nrounicodepago"]));
				array_push($datos,number_format($v["importecuota"],2));
				array_push($datos,number_format($v["interescuota"],2));
				array_push($datos,number_format($v["saldocuota"],2));
                $pdf->Row($datos);
			}
			$pdf->Cell(array_sum($w),0,'','T'); 
			$pdf->Ln();
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(227,5,"TOTALES",1,0,'R');
		    $pdf->Cell(18,5,number_format($totalimporte,2),1,"R");
			$pdf->Cell(15,5,number_format($totalinteres,2),1,"R");
		    $pdf->Cell(18,5,number_format($totalsaldo,2),1,"R");
			$pdf->SetTitle("phuyu Peru - REPORTE ".$tipo); $pdf->Output();
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function excel_cuotas(){
		if ($_GET["datos"]) {
			$this->request = json_decode($_GET["datos"]);

			if ($this->request->tipo==1) {
				$tipo = "CREDITOS POR CUOTAS DE CLIENTES"; 
			}else{
				$tipo = "CREDITOS POR CUOTAS DE PROVEEDORES";
			}

			if($this->request->codpersona == 0){
				$codpersona = '';
			}else{
				$codpersona = "AND codpersona=".$this->request->codpersona;
			}
			if($this->request->codmoneda == 0){
				$codmoneda = '';
			}else{
				$codmoneda = "AND codmoneda=".$this->request->codmoneda;
			}
			$lista = $this->db->query("SELECT codcredito,codsucursalcredito, codcajacredito, estadocuota, codpersona, razonsocial, cliente, direccion, codmoneda,monedasimbolo, tipocambio, codkardex, tipo, fechacredito, fechainiciocredito, fechavencimientocredito, importecredito, tasainterescredito, comprobantereferencia, nrocuota, nroletra, nrounicodepago, fechavencecuota, fechapagocuota, importecuota, saldocuota, interescuota, totalcuota, tipoynrodocumento, ('".$this->request->fecha_hasta."' - fechavencecuota) AS diasvencidos
			  FROM caja.v_cuotascreditos
			  WHERE codsucursalcredito = 1 AND fechavencecuota <= '".$this->request->fecha_hasta."' AND tipo = ".(int)$this->request->tipo." AND  estadocuota = 1 ".$codpersona." ".$codmoneda."
			  ORDER BY fechavencecuota")->result_array();

			$this->load->view("reportes/cuotas/xls_cuotas.php",compact("lista","tipo"));
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
}