<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Personas extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			$departamentos = $this->db->query("select distinct(ubidepartamento), departamento from public.ubigeo order by ubidepartamento")->result_array();
			$this->load->view("reportes/personas/index",compact("departamentos"));
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function lista(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$limit = 10; $offset = $this->request->pagina * $limit - $limit;
			$tiposocio = ' AND socios.codsociotipo='.$this->request->campos->codsociotipo;
			$departamento = '';$provincia = '';$distrito="";
			if($this->request->campos->departamento!=""){
				$departamento .= " AND ubigeo.ubidepartamento='".$this->request->campos->departamento."'";
			}
			if($this->request->campos->provincia!=""){
				$departamento .= " AND ubigeo.ubiprovincia='".$this->request->campos->provincia."'";
			}
			if($this->request->campos->distrito!=""){
				$departamento .= " AND ubigeo.ubidistrito='".$this->request->campos->distrito."'";
			}

			$lista = $this->db->query("select personas.*,ubigeo.* from public.socios as socios inner join public.personas as personas on (socios.codpersona=personas.codpersona) inner join public.ubigeo ubigeo on (personas.codubigeo=ubigeo.codubigeo) where socios.estado=1 ".$tiposocio." AND personas.codpersona>2 ".$departamento." ".$provincia." ".$distrito." order by personas.codpersona desc offset ".$offset." limit ".$limit)->result_array();

			$total = $this->db->query("select count(*) as total from public.socios as socios inner join public.personas as personas on (socios.codpersona=personas.codpersona) inner join public.ubigeo ubigeo on (personas.codubigeo=ubigeo.codubigeo) where socios.estado=1 ".$tiposocio." AND personas.codpersona>2 ".$departamento." ".$provincia." ".$distrito." ")->result_array();

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

	function pdf_personas(){
		if ($_GET["datos"]) {
			$this->request = json_decode($_GET["datos"]);

			$this->load->library('Pdf2'); $pdf = new Pdf2(); $pdf->AddPage();
			$pdf->pdf_header("REPORTE DE PERSONAS","");

			$tiposocio = ' AND socios.codsociotipo='.$this->request->codsociotipo;
			$departamento = '';$provincia = '';$distrito="";$tipo='';
			if($this->request->departamento!=""){
				$departamento = " AND ubigeo.ubidepartamento='".$this->request->departamento."'";
			}
			if($this->request->provincia!=""){
				$departamento = " AND ubigeo.ubiprovincia='".$this->request->provincia."'";
			}
			if($this->request->distrito!=""){
				$departamento = " AND ubigeo.ubidistrito='".$this->request->distrito."'";
			}
			if($this->request->codsociotipo==1){
				$tipo = 'CLIENTES';
			}
			else if($this->request->codsociotipo==2){
				$tipo = 'PROVEEDORES';
			}else{
				$tipo = 'CLIENTES Y PROVEEDORES';
			}

			$pdf->pdf_header_titulo("LISTA DE : ".$tipo);

			$columnas = array("ID","DOCUMENTO","RAZON SOCIAL","DIRECCION","CONTACTO","EMAIL","UBIGEO");
			$w = array(10,20,40,50,20,25,25); $pdf->pdf_tabla_head($columnas,$w,7);

			$lista = $this->db->query("select personas.*,ubigeo.* from public.socios as socios inner join public.personas as personas on (socios.codpersona=personas.codpersona) inner join public.ubigeo ubigeo on (personas.codubigeo=ubigeo.codubigeo) where socios.estado=1 ".$tiposocio." AND personas.codpersona>2 ".$departamento." ".$provincia." ".$distrito." order by personas.codpersona desc ")->result_array();
			
			$pdf->SetWidths($w); $pdf->SetLineHeight(5); $pdf->SetFont('Arial','',6);
			foreach ($lista as $key => $value) {

				$datos = array($value["codpersona"]);
				array_push($datos,utf8_decode($value["documento"]));
				array_push($datos,utf8_decode($value["razonsocial"]));
				array_push($datos,utf8_decode($value["direccion"]));
				array_push($datos,utf8_decode($value["telefono"]));
				array_push($datos,utf8_decode($value["email"]));
				array_push($datos,utf8_decode($value["provincia"].' - '.$value["distrito"]));
                $pdf->Row($datos);
			}
			$pdf->Cell(array_sum($w),0,'','T'); $pdf->Ln();

			$pdf->SetTitle("phuyu Peru - REPORTE ".$tipo); $pdf->Output();
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function excel_personas(){
		if ($_GET["datos"]) {
			$this->request = json_decode($_GET["datos"]);

			$tiposocio = ' AND socios.codsociotipo='.$this->request->codsociotipo;
			$departamento = '';$provincia = '';$distrito="";$tipo='';
			if($this->request->departamento!=""){
				$departamento = " AND ubigeo.ubidepartamento='".$this->request->departamento."'";
			}
			if($this->request->provincia!=""){
				$departamento = " AND ubigeo.ubiprovincia='".$this->request->provincia."'";
			}
			if($this->request->distrito!=""){
				$departamento = " AND ubigeo.ubidistrito='".$this->request->distrito."'";
			}
			if($this->request->codsociotipo==1){
				$tipo = 'CLIENTES';
			}
			else if($this->request->codsociotipo==2){
				$tipo = 'PROVEEDORES';
			}else{
				$tipo = 'CLIENTES Y PROVEEDORES';
			}

			$lista = $this->db->query("select personas.*,ubigeo.* from public.socios as socios inner join public.personas as personas on (socios.codpersona=personas.codpersona) inner join public.ubigeo ubigeo on (personas.codubigeo=ubigeo.codubigeo) where socios.estado=1 ".$tiposocio." AND personas.codpersona>2 ".$departamento." ".$provincia." ".$distrito." order by personas.codpersona desc ")->result_array();

			$this->load->view("reportes/personas/xls_personas.php",compact("lista","tipo"));
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