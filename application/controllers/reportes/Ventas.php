<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Ventas extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
	}

	private function filtro_comprobante_ventas($alias, $codcomprobantetipo)
	{
		$codcomprobantetipo = (int)$codcomprobantetipo;
		if ($codcomprobantetipo <= 0) {
			return "";
		}

		return " and ".$alias.".codcomprobantetipo=".$codcomprobantetipo." ";
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			$sucursales = $this->db->query("select *from public.sucursales where estado=1")->result_array();
			$vendedores = $this->db->query("select persona.codpersona,persona.razonsocial from public.personas as persona inner join public.empleados as empleado on(persona.codpersona=empleado.codpersona) where empleado.estado=1 and empleado.codcargo=4")->result_array();
			$comprobantes = $this->db->query("select distinct(ct.codcomprobantetipo) as codigo, ct.* from caja.comprobantetipos as ct inner join caja.comprobantes as c on(ct.codcomprobantetipo=c.codcomprobantetipo) where c.codsucursal=".(int)$_SESSION["phuyu_codsucursal"]." and ct.venta=1 and c.estado=1 order by ct.codcomprobantetipo")->result_array();
			$this->load->view("reportes/ventas/index",compact("sucursales","vendedores","comprobantes"));
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function ver_grafico(){
		if ($_GET["datos"]) {
			$this->request = json_decode($_GET["datos"]); $categorias = array(); $totales = array();

			if ($this->request->codsucursal==0) {
				$sucursales = $this->db->query("select *from public.sucursales where estado=1")->result_array();
				foreach ($sucursales as $key => $value) {
					$total = $this->db->query("select round(COALESCE(sum(importe),0),2) as importe from caja.movimientos where codkardex<>0 and fechamovimiento>='".$this->request->fechadesde."' and fechamovimiento<='".$this->request->fechahasta."' and tipomovimiento=1 and estado=".(int)$this->request->estado)->result_array();
					$categorias[] = $value["descripcion"]; $totales[] = (double)$total[0]["importe"];
				}
			}else{
				if ($this->request->codcaja==0) {
					$cajas = $this->db->query("select *from caja.cajas where codsucursal=".$this->request->codsucursal." and estado=1")->result_array();
					foreach ($cajas as $key => $value) {
						$total = $this->db->query("select round(COALESCE(sum(importe),0),2) as importe from caja.movimientos where codkardex<>0 and fechamovimiento>='".$this->request->fechadesde."' and fechamovimiento<='".$this->request->fechahasta."' and tipomovimiento=1 and codcaja=".$value["codcaja"]." and estado=".(int)$this->request->estado)->result_array();
						$categorias[] = $value["descripcion"]; $totales[] = (double)$total[0]["importe"];
					}
				}else{
					$desde = explode("-", $this->request->fechadesde); $hasta = explode("-", $this->request->fechahasta);

					if ( ($hasta[0] - $desde[0])!=0 ) {
						$year = $hasta[0] - $desde[0] + 1; $y_inicio = $desde[0]; $f_inicio = $this->request->fechadesde;
						for ($i=0; $i < $year ; $i++) { 
							$total = $this->db->query("select round(COALESCE(sum(importe),0),2) as importe from caja.movimientos where codkardex<>0 and TO_CHAR(fechamovimiento,'YYYY')='".$y_inicio."' and tipomovimiento=1 and codcaja=".$this->request->codcaja." and estado=".(int)$this->request->estado)->result_array();

							$categorias[$i] = "Año-".$y_inicio; $totales[$i] = (double)$total[0]["importe"];
							$y_inicio = $y_inicio + 1; $f_inicio = date("Y-m-d",strtotime($f_inicio."+ 1 year")); 
						}
					}else{
						if ( ($hasta[1] - $desde[1]!=0 ) ) {
							$meses = $hasta[1] - $desde[1] + 1; $m_inicio = $desde[1]; $f_inicio = $this->request->fechadesde;
							for ($i=0; $i < $meses ; $i++) { 
								$total = $this->db->query("select round(COALESCE(sum(importe),0),2) as importe from caja.movimientos where codkardex<>0 and TO_CHAR(fechamovimiento,'YYYY-MM')='".$desde[0]."-".$m_inicio."' and tipomovimiento=1 and codcaja=".$this->request->codcaja." and estado=".(int)$this->request->estado)->result_array();

								$categorias[$i] = "Mes-".$m_inicio; $totales[$i] = (double)$total[0]["importe"];
								$m_inicio = $m_inicio + 1; $f_inicio = date("Y-m-d",strtotime($f_inicio."+ 1 month")); 
							}
						}else{
							$dias = $hasta[2] - $desde[2] + 1; $d_inicio = $desde[2]; $f_inicio = $this->request->fechadesde;
							for ($i=0; $i < $dias ; $i++) { 
								$total = $this->db->query("select round(COALESCE(sum(importe),0),2) as importe from caja.movimientos where codkardex<>0 and fechamovimiento='".$f_inicio."' and tipomovimiento=1 and codcaja=".$this->request->codcaja." and estado=".(int)$this->request->estado)->result_array();

								$categorias[$i] = "Dia-".$d_inicio; $totales[$i] = (double)$total[0]["importe"];
								$d_inicio = $d_inicio + 1; $f_inicio = date("Y-m-d",strtotime($f_inicio."+ 1 days")); 
							}
						}
					}
				}
			}

			$data["categorias"] = $categorias; $data["totales"] = $totales;
			echo json_encode($data);
		}
	}

	function excel_productos_vendidos(){
		if (isset($_SESSION["phuyu_codusuario"])) {
			if ($_GET["datos"]) {
				$this->request = json_decode($_GET["datos"]);

				if ($this->request->codvendedor==0) {
					$vendedor_texto = 'PRODUCTOS VENDIDOS DESDE '.$this->request->fechadesde.' HASTA '.$this->request->fechahasta.' - TODOS LOS VENDEDORES';
					$filtro = "";
				}else{
					$vendedor = $this->db->query("select documento, razonsocial from public.personas where codpersona=".$this->request->codvendedor)->result_array();
					$vendedor_texto = 'PRODUCTOS VENDIDOS DESDE '.$this->request->fechadesde.' HASTA '.$this->request->fechahasta.' - VENDEDOR: '.$vendedor[0]["razonsocial"];
					$filtro = "and k.codempleado=".$this->request->codvendedor;
				}

				$sucursal_texto = 'TODAS LAS SUCURSALES ACTIVAS';

				if ($this->request->codsucursal==0) {
					$filtro .= "";
				}else{
					$sucursal = $this->db->query("select descripcion from public.sucursales where codsucursal=".$this->request->codsucursal)->result_array();
					$sucursal_texto = isset($sucursal[0]["descripcion"]) ? $sucursal[0]["descripcion"] : 'SUCURSAL '.$this->request->codsucursal;
					$filtro .= "and k.codsucursal=".$this->request->codsucursal." ";
				}

				if ($this->request->codalmacen==0) {
					$filtro .= "";
				}else{
					$filtro .= "and k.codalmacen=".$this->request->codalmacen;
				}

				$lista = $this->db->query("select distinct(kd.codproducto) as codproducto,p.descripcion,p.codigo from kardex.kardex as k inner join kardex.kardexdetalle as kd on(k.codkardex=kd.codkardex) inner join almacen.productos as p on(kd.codproducto=p.codproducto) where k.codmovimientotipo=20 ".$filtro." and k.fechacomprobante>='".$this->request->fechadesde."' and k.fechacomprobante<='".$this->request->fechahasta."' and k.estado=1 ".$filtro." order by p.descripcion")->result_array();

				$empresa = $this->db->query("select *from public.personas where codpersona=1")->result_array();
				$fechadesde = $this->request->fechadesde;
				$fechahasta = $this->request->fechahasta;
				$this->load->view("reportes/ventas/masvendidosxls",compact("empresa","vendedor_texto","sucursal_texto","fechadesde","fechahasta","lista","filtro"));
			}
		}
	}

	function pdf_productos_vendidos(){
		if (isset($_SESSION["phuyu_codusuario"])) {
			if ($_GET["datos"]) {
				$this->request = json_decode($_GET["datos"]);

				$this->load->library('Pdf2'); $pdf = new Pdf2(); $pdf->AddPage();
				$pdf->pdf_header("REPORTE DE PRODUCTOS VENDIDOS","");

		        $pdf->SetFont('Arial','B',10); $pdf->setFillColor(245,245,245);
		        if ($this->request->codvendedor==0) {
					$vendedor_texto = 'PRODUCTOS VENDIDOS DESDE '.$this->request->fechadesde.' HASTA '.$this->request->fechahasta.' - TODOS LOS VENDEDORES';
					$filtro = "";
				}else{
					$vendedor = $this->db->query("select documento, razonsocial from public.personas where codpersona=".$this->request->codvendedor)->result_array();
					$vendedor_texto = 'PRODUCTOS VENDIDOS DESDE '.$this->request->fechadesde.' HASTA '.$this->request->fechahasta.' - VENDEDOR: '.$vendedor[0]["razonsocial"];
					$filtro = "and k.codempleado=".$this->request->codvendedor;
				}

				if ($this->request->codsucursal==0) {
					$filtro .= "";
				}else{
					$filtro .= "and k.codsucursal=".$this->request->codsucursal." ";
				}

				if ($this->request->codalmacen==0) {
					$filtro .= "";
				}else{
					$filtro .= "and k.codalmacen=".$this->request->codalmacen;
				}
		        $pdf->Cell(0,7,$vendedor_texto,0,1,'L',1); $pdf->Ln(2);

				$lista = $this->db->query("select distinct(kd.codproducto) as codproducto,p.descripcion,p.codigo from kardex.kardex as k inner join kardex.kardexdetalle as kd on(k.codkardex=kd.codkardex) inner join almacen.productos as p on(kd.codproducto=p.codproducto) where k.codmovimientotipo=20 ".$filtro." and k.fechacomprobante>='".$this->request->fechadesde."' and k.fechacomprobante<='".$this->request->fechahasta."' and k.estado=1 ".$filtro." order by p.descripcion")->result_array();

				$columnas = array("N°","CODIGO","DESCRIPCION PRODUCTO","U.MEDIDA","CANTIDAD","U.MEDIDAD MIN","CANTIDAD");
				$w = array(10,20,73,20,20,27,20); $pdf->pdf_tabla_head($columnas,$w,9);

				$pdf->SetWidths(array(10,20,73,20,20,27,20));
	            $pdf->SetLineHeight(5); $pdf->SetFont('Arial','',8);

				$item = 0; $total = 0; $totalmin = 0;
				foreach($lista as $value){ $item = $item + 1;
					$unidades = $this->db->query("select u.descripcion as unidad,pu.codunidad, pu.factor from almacen.productounidades as pu inner join almacen.unidades as u on(pu.codunidad=u.codunidad) where pu.codproducto=".$value["codproducto"]." and pu.estado=1 order by factor asc")->result_array();
					if (count($unidades)==1) {
						$codunidadmin = $unidades[0]["codunidad"]; $unidadmin = $unidades[0]["unidad"]; $factormin = $unidades[0]["factor"];
						$codunidad= 0; $unidad = "-"; $factor = 1;
					}else{
						$codunidadmin = $unidades[0]["codunidad"]; $unidadmin = $unidades[0]["unidad"]; $factormin = $unidades[0]["factor"];
						$codunidad = $unidades[1]["codunidad"]; $unidad = $unidades[1]["unidad"]; $factor = $unidades[1]["factor"];
					}

					$ventas = $this->db->query("select kd.codproducto,kd.codunidad,kd.cantidad from kardex.kardex as k inner join kardex.kardexdetalle as kd on(k.codkardex=kd.codkardex) where k.codmovimientotipo=20 and kd.codproducto=".$value["codproducto"]." and k.fechacomprobante>='".$this->request->fechadesde."' and k.fechacomprobante<='".$this->request->fechahasta."' and k.estado=".$this->request->estado." ".$filtro)->result_array();

					$cantidad = 0;
					foreach ($ventas as $v) {
						if ($v["codunidad"]==$codunidadmin) {
							$cantidad = $cantidad + ($factormin * $v["cantidad"]);
						}else{
							$cantidad = $cantidad + ($factor * $v["cantidad"]);
						}
					}

					if ($codunidad==0) {
						$cantidad_unidad = $cantidad; $cantidad_unidad_min = 0; $unidad = $unidadmin; $unidadmin = "-";
					}else{
						$cantidad_unidad = floor($cantidad / $factor);
						$cantidad_unidad_min = $cantidad - ($cantidad_unidad * $factor);
					}
					
					$total = $total + $cantidad_unidad; $totalmin = $totalmin + $cantidad_unidad_min;
					$datos = array("0".$item);
					array_push($datos,$value["codigo"]);
					array_push($datos,utf8_decode($value["descripcion"]));
					array_push($datos,$unidad);
					array_push($datos,number_format($cantidad_unidad,2));
					array_push($datos,$unidadmin);
					array_push($datos,number_format($cantidad_unidad_min,2));
	                $pdf->Row($datos);
				}
				$pdf->Cell(array_sum($w),0,'','T'); $pdf->Ln();

				$pdf->SetFont('Arial','B',8);
				$pdf->Cell(103,5,"TOTALES VENDIDOS",1,0,'R');
				$pdf->Cell(20,5,"",1,"R");
			    $pdf->Cell(20,5,number_format($total,2),1,"R");
			    $pdf->Cell(27,5,"",1,"R");
			    $pdf->Cell(20,5,number_format($totalmin,2),1,"R");

				$pdf->SetTitle("phuyu Peru - Productos Vendidos"); $pdf->Output();
			}
		}
	}

	function excel_ventas_vendedor(){
		if (isset($_SESSION["phuyu_codusuario"])) {
			if ($_GET["datos"]) { 
				$this->request = json_decode($_GET["datos"]);
				$this->tipos = (isset($_GET["tipo"]) ? "resumen" : null);
				$tipos = $this->tipos;
				if ($this->request->codalmacen==0) {
					$almacen = "";
				}else{
					$almacen = "and kardex.codalmacen=".$this->request->codalmacen;
				}

		        if ($this->request->codvendedor == "") {
					$vendedor_texto = 'VENTAS DESDE '.$this->request->fechadesde.' HASTA '.$this->request->fechahasta.' - TODOS LOS VENDEDORES';
					$filtro = "";
				}else{
					$vendedor = $this->db->query("select documento, razonsocial from public.personas where codpersona=".$this->request->codvendedor)->result_array();
					$vendedor_texto = 'VENTAS DESDE '.$this->request->fechadesde.' HASTA '.$this->request->fechahasta.' - VENDEDOR: '.$vendedor[0]["razonsocial"];
					$filtro = "and kardex.codempleado=".$this->request->codvendedor;
				}

				$lista = $this->db->query("select personas.documento,kardex.cliente,personas.coddocumentotipo, kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.estado, kardex.condicionpago,kardex.nrocomprobante, kardex.fechacomprobante,kardex.valorventa,kardex.igv, kardex.importe,kardex.estado,comprobantes.abreviatura as tipo from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join public.empleados as empleados on (kardex.codempleado = empleados.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codmovimientotipo=20 ".$almacen." and kardex.codsucursal=".$this->request->codsucursal." and kardex.fechacomprobante>='".$this->request->fechadesde."' and kardex.fechacomprobante<='".$this->request->fechahasta."' and kardex.estado=".$this->request->estado." ".$filtro." and empleados.codcargo=4 order by kardex.fechacomprobante, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.nrocomprobante")->result_array();
				$fechadesde= $this->request->fechadesde; $fechahasta = $this->request->fechahasta;
				
				$empresa = $this->db->query("select *from public.personas where codpersona=1")->result_array();
				$this->load->view("reportes/ventas/ventasvendedorxls",compact("empresa","vendedor_texto","lista","tipos"));
			}
		}
	}

	function pdf_ventas_vendedor(){
		if (isset($_SESSION["phuyu_codusuario"])) {
			if ($_GET["datos"]) {
				$this->request = json_decode($_GET["datos"]);

				$color_letra = "B";
				if (isset($_GET["tipo"])) {
					$color_letra = "";
				}

				$this->load->library('Pdf2'); $pdf = new Pdf2(); $pdf->AddPage();
				$pdf->pdf_header("REPORTE DE VENTAS VENDEDOR","");

				if ($this->request->codalmacen==0) {
					$almacen = "";
				}else{
					$almacen = "and kardex.codalmacen=".$this->request->codalmacen;
				}

				$pdf->SetFont('Arial','B',10); $pdf->setFillColor(245,245,245);
		        if ($this->request->codvendedor == "") {
					$vendedor_texto = 'VENTAS DESDE '.$this->request->fechadesde.' HASTA '.$this->request->fechahasta.' - TODOS LOS VENDEDORES';
					$filtro = "";
				}else{
					$vendedor = $this->db->query("select documento, razonsocial from public.personas where codpersona=".$this->request->codvendedor)->result_array();
					$vendedor_texto = 'VENTAS DESDE '.$this->request->fechadesde.' HASTA '.$this->request->fechahasta.' - VENDEDOR: '.$vendedor[0]["razonsocial"];
					$filtro = "and kardex.codempleado=".$this->request->codvendedor;
				}
		        $pdf->Cell(0,7,$vendedor_texto,0,1,'L',1); $pdf->Ln(2);

				$columnas = array("N°","FECHA","DOCUMENTO","DNI/RUC","RAZON SOCIAL","SUBTOTAL","IGV","TOTAL");
				$w = array(10,15,22,20,73,20,10,20); $pdf->pdf_tabla_head($columnas,$w,8);

				$lista = $this->db->query("select personas.documento,kardex.cliente,personas.coddocumentotipo, kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.estado, kardex.condicionpago,kardex.nrocomprobante, kardex.fechacomprobante,kardex.valorventa,kardex.igv, kardex.importe,kardex.estado,comprobantes.abreviatura as tipo from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join public.empleados as empleados on (kardex.codempleado = empleados.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codmovimientotipo=20 ".$almacen." and kardex.codsucursal=".$this->request->codsucursal." and kardex.fechacomprobante>='".$this->request->fechadesde."' and kardex.fechacomprobante<='".$this->request->fechahasta."' and kardex.estado=".$this->request->estado." ".$filtro." and empleados.codcargo=4 order by kardex.fechacomprobante, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.nrocomprobante")->result_array();

				$item = 0; $importe = 0;
				foreach ($lista as $key => $value) { 
					$item = $item + 1; $importe = $importe + $value["importe"];
					$pdf->SetWidths(array(10,15,22,20,73,20,10,20));
	            	$pdf->SetLineHeight(5); $pdf->SetFont('Arial',$color_letra,7);

					$datos = array("0".$item);
					array_push($datos,$value["fechacomprobante"]);
					array_push($datos,$value["seriecomprobante"]."-".$value["nrocomprobante"]);
					array_push($datos,utf8_decode($value["documento"]));
					array_push($datos,utf8_decode($value["cliente"]));

					array_push($datos,number_format($value["valorventa"],2));
					array_push($datos,number_format($value["igv"],2));
					array_push($datos,number_format($value["importe"],2));
	                $pdf->Row($datos);

	                if ($color_letra=="B") {
	                	$detalle = $this->db->query("select kd.*,p.descripcion as producto,u.descripcion as unidad from kardex.kardexdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codkardex=".$value["codkardex"]." and kd.estado=1 order by kd.item")->result_array();

		                $columnas = array("CANT","DESCRIPCION DETALLE VENTA","UNI.MED","P.UNITARIO","IGV","IMPORTE"); $wd = array(10,110,20,20,10,20); 
		                for($i=0;$i<count($columnas);$i++){
				            $pdf->Cell($wd[$i],5,utf8_decode($columnas[$i]),1,0,'L');
				        } $pdf->Ln();

				        $pdf->SetWidths(array(10,110,20,20,10,20)); $pdf->SetLineHeight(5); $pdf->SetFont('Arial','',7);
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
				$pdf->Cell(170,5,"TOTALES",1,0,'R');
			    $pdf->Cell(20,5,number_format($importe,2),1,"R");
			
				$pdf->SetTitle("phuyu Peru - Reporte Ventas - Vendedor"); $pdf->Output();
			}
		}
	}

	function excel_ventas_cliente(){
		if (isset($_SESSION["phuyu_codusuario"])) {
			if ($_GET["datos"]) { 
				$this->request = json_decode($_GET["datos"]);
				$this->tipos = (isset($_GET["tipo"]) ? "resumen" : null);
				$tipos = $this->tipos;
				if ($this->request->codalmacen==0) {
					$almacen = "";
				}else{
					$almacen = "and kardex.codalmacen=".$this->request->codalmacen;
				}

		        if ($this->request->codpersona==0) {
					$socios = $this->db->query("select distinct(personas.codpersona),personas.documento,personas.razonsocial,personas.direccion from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codmovimientotipo=20 ".$almacen." and kardex.codsucursal=".$this->request->codsucursal." and kardex.fechacomprobante>='".$this->request->fechadesde."' and kardex.fechacomprobante<='".$this->request->fechahasta."' and kardex.estado=1")->result_array();
				}else{
					$socios = $this->db->query("select distinct(personas.codpersona),personas.documento,personas.razonsocial,personas.direccion from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codpersona=".$this->request->codpersona." and kardex.codmovimientotipo=20 ".$almacen." and kardex.codsucursal=".$this->request->codsucursal." and kardex.fechacomprobante>='".$this->request->fechadesde."' and kardex.fechacomprobante<='".$this->request->fechahasta."' and kardex.estado=1")->result_array();
				}
				$fecha = 'LISTA DE VENTA DESDE '.$this->request->fechadesde.' HASTA '.$this->request->fechahasta;
				
				$empresa = $this->db->query("select *from public.personas where codpersona=1")->result_array();
				$this->load->view("reportes/ventas/ventasclientesxls",compact("empresa","fecha","socios","tipos","almacen"));
			}
		}
	}

	function pdf_ventas_cliente(){
		if (isset($_SESSION["phuyu_codusuario"])) {
			if ($_GET["datos"]) {
				$this->request = json_decode($_GET["datos"]);

				$this->load->library('Pdf2'); $pdf = new Pdf2(); $pdf->AddPage();
				$pdf->pdf_header("REPORTE DE VENTAS CLIENTE","");

				if ($this->request->codalmacen==0) {
					$almacen = "";
				}else{
					$almacen = "and kardex.codalmacen=".$this->request->codalmacen;
				}

				if ($this->request->codpersona==0) {
					$socios = $this->db->query("select distinct(personas.codpersona),personas.documento,personas.razonsocial,personas.direccion from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codmovimientotipo=20 ".$almacen." and kardex.codsucursal=".$this->request->codsucursal." and kardex.fechacomprobante>='".$this->request->fechadesde."' and kardex.fechacomprobante<='".$this->request->fechahasta."' and kardex.estado=1")->result_array();
				}else{
					$socios = $this->db->query("select distinct(personas.codpersona),personas.documento,personas.razonsocial,personas.direccion from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codpersona=".$this->request->codpersona." and kardex.codmovimientotipo=20 ".$almacen." and kardex.codsucursal=".$this->request->codsucursal." and kardex.fechacomprobante>='".$this->request->fechadesde."' and kardex.fechacomprobante<='".$this->request->fechahasta."' and kardex.estado=1")->result_array();
				}
		        $pdf->Cell(0,7,'LISTA DE VENTAS DESDE '.$this->request->fechadesde.' HASTA '.$this->request->fechahasta,0,1,'C',0); $pdf->Ln(2);

		        foreach ($socios as $key => $value) {
		        	$texto = "CLIENTE: ".utf8_decode($value["razonsocial"])." | DIRECCION: ".utf8_decode($value["direccion"]);
					$pdf->SetFont('Arial','B',9);
					$pdf->Cell(190,6,substr($texto,0,95),1); $pdf->Ln();

					$lista = $this->db->query("select personas.documento,kardex.cliente,kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante,kardex.condicionpago, kardex.nrocomprobante, kardex.fechacomprobante,kardex.valorventa,kardex.igv, kardex.descglobal, kardex.importe,kardex.condicionpago, comprobantes.descripcion as tipo from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codpersona=".$value["codpersona"]." and kardex.fechacomprobante>='".$this->request->fechadesde."' and kardex.fechacomprobante<='".$this->request->fechahasta."' and kardex.codmovimientotipo=20 ".$almacen." and kardex.codsucursal=".$this->request->codsucursal." and kardex.estado=1 order by kardex.fechacomprobante, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.nrocomprobante")->result_array();

					$columnas = array("N°","FECHA","DOCUMENTO","SUBTOTAL","DESCUENTO","IGV","TOTAL","CONDICION");
					$w = array(10,25,30,25,25,25,25,25); $pdf->pdf_tabla_head($columnas,$w,8);

					$pdf->SetWidths(array(10,25,30,25,25,25,25,25));
		            $pdf->SetLineHeight(5); $pdf->SetFont('Arial','',7);

		            $item = 0; $valorventa = 0; $descglobal = 0; $igv = 0; $importe = 0;
					foreach($lista as $value){ 
						$item = $item + 1; $valorventa = $valorventa + $value["valorventa"]; $descglobal = $descglobal + $value["descglobal"];
						$igv = $igv + $value["igv"]; $importe = $importe + $value["importe"];

						$datos = array("0".$item);
						array_push($datos,$value["fechacomprobante"]);
						array_push($datos,$value["seriecomprobante"]."-".$value["nrocomprobante"]);

						array_push($datos,number_format($value["valorventa"],2));
						array_push($datos,number_format($value["descglobal"],2));
						array_push($datos,number_format($value["igv"],2));
						array_push($datos,number_format($value["importe"],2));
						if ($value["condicionpago"]==1) {
							array_push($datos,"CONTADO");
					    }else{
					    	array_push($datos,"CREDITO");
					    }
		                $pdf->Row($datos);
					}
					$pdf->Cell(array_sum($w),0,'','T'); $pdf->Ln();

					$pdf->SetFont('Arial','B',8);
					$pdf->Cell(65,5,"TOTALES",1,0,'R');
				    $pdf->Cell(25,5,number_format($valorventa,2),1,"R");
				    $pdf->Cell(25,5,number_format($descglobal,2),1,"R");
				    $pdf->Cell(25,5,number_format($igv,2),1,"R");
				    $pdf->Cell(50,5,number_format($importe,2),1,"R"); $pdf->Ln(); $pdf->Ln();
		        }
				$pdf->SetTitle("phuyu Peru - Reporte Ventas - Cliente"); $pdf->Output();
			}
		}
	}

	function pdf_ventas_cliente_detallado(){
		if (isset($_SESSION["phuyu_codusuario"])) {
			if ($_GET["datos"]) {
				$this->request = json_decode($_GET["datos"]);

				$this->load->library('Pdf2'); $pdf = new Pdf2(); $pdf->AddPage();
				$pdf->pdf_header("REPORTE DE VENTAS CLIENTE","");

				if ($this->request->codalmacen==0) {
					$almacen = "";
				}else{
					$almacen = "and kardex.codalmacen=".$this->request->codalmacen;
				}

				if ($this->request->codpersona==0) {
					$socios = $this->db->query("select distinct(personas.codpersona),personas.documento,personas.razonsocial,personas.direccion from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codmovimientotipo=20 ".$almacen." and kardex.codsucursal=".$this->request->codsucursal." and kardex.fechacomprobante>='".$this->request->fechadesde."' and kardex.fechacomprobante<='".$this->request->fechahasta."' and kardex.estado=1")->result_array();
				}else{
					$socios = $this->db->query("select distinct(personas.codpersona),personas.documento,personas.razonsocial,personas.direccion from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codpersona=".$this->request->codpersona." and kardex.codmovimientotipo=20 ".$almacen." and kardex.codsucursal=".$this->request->codsucursal." and kardex.fechacomprobante>='".$this->request->fechadesde."' and kardex.fechacomprobante<='".$this->request->fechahasta."' and kardex.estado=1")->result_array();
				}
		        $pdf->Cell(0,7,'LISTA DE VENTAS DESDE '.$this->request->fechadesde.' HASTA '.$this->request->fechahasta,0,1,'C',0); $pdf->Ln(2);

		        foreach ($socios as $key => $value) {
		        	$texto = "CLIENTE: ".utf8_decode($value["razonsocial"])." | DIRECCION: ".utf8_decode($value["direccion"]);
					$pdf->SetFont('Arial','B',9);
					$pdf->Cell(190,6,substr($texto,0,95),1); $pdf->Ln();

					$lista = $this->db->query("select personas.documento,kardex.cliente,kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante,kardex.condicionpago, kardex.nrocomprobante, kardex.fechacomprobante,kardex.valorventa,kardex.igv, kardex.descglobal, kardex.importe,kardex.condicionpago, comprobantes.descripcion as tipo from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codpersona=".$value["codpersona"]." and kardex.fechacomprobante>='".$this->request->fechadesde."' and kardex.fechacomprobante<='".$this->request->fechahasta."' and kardex.codmovimientotipo=20 ".$almacen." and kardex.codsucursal=".$this->request->codsucursal." and kardex.estado=1 order by kardex.fechacomprobante, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.nrocomprobante")->result_array();

					$columnas = array("N°","FECHA","DOCUMENTO","SUBTOTAL","DESCUENTO","IGV","TOTAL","CONDICION");
					$w = array(10,25,30,25,25,25,25,25); $pdf->pdf_tabla_head($columnas,$w,8);

		            $item = 0; $valorventa = 0; $descglobal = 0; $igv = 0; $importe = 0;
					foreach($lista as $value){
						$pdf->SetWidths(array(10,25,30,25,25,25,25,25)); $pdf->SetLineHeight(5); $pdf->SetFont('Arial','B',7);
						
						$item = $item + 1; $valorventa = $valorventa + $value["valorventa"]; $descglobal = $descglobal + $value["descglobal"];
						$igv = $igv + $value["igv"]; $importe = $importe + $value["importe"];

						$datos = array("0".$item);
						array_push($datos,$value["fechacomprobante"]);
						array_push($datos,$value["seriecomprobante"]."-".$value["nrocomprobante"]);

						array_push($datos,number_format($value["valorventa"],2));
						array_push($datos,number_format($value["descglobal"],2));
						array_push($datos,number_format($value["igv"],2));
						array_push($datos,number_format($value["importe"],2));
						if ($value["condicionpago"]==1) {
							array_push($datos,"CONTADO");
					    }else{
					    	array_push($datos,"CREDITO");
					    }
		                $pdf->Row($datos);

		                $detalle = $this->db->query("select kd.*,p.descripcion as producto,u.descripcion as unidad from kardex.kardexdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codkardex=".$value["codkardex"]." and kd.estado=1 order by kd.item")->result_array();

		                $columnas = array("CANT","DESCRIPCION DETALLE VENTA","UNI.MED","P.UNITARIO","IGV","IMPORTE"); $wd = array(10,110,20,20,10,20); 
		                for($i=0;$i<count($columnas);$i++){
				            $pdf->Cell($wd[$i],5,utf8_decode($columnas[$i]),1,0,'L');
				        } $pdf->Ln();

				        $pdf->SetWidths(array(10,110,20,20,10,20)); $pdf->SetLineHeight(5); $pdf->SetFont('Arial','',7);
				        foreach ($detalle as $v) {
				        	$datos = array(number_format($v["cantidad"],2));
							array_push($datos,utf8_decode($v["producto"].' '.$v["descripcion"]));
							array_push($datos,utf8_decode($v["unidad"]));

							array_push($datos,number_format($v["preciounitario"],2));
							array_push($datos,number_format($v["igv"],2));
							array_push($datos,number_format($v["subtotal"],2));
			                $pdf->Row($datos);
				        }
					}
					$pdf->Cell(array_sum($w),0,'','T'); $pdf->Ln();

					$pdf->SetFont('Arial','B',8);
					$pdf->Cell(65,5,"TOTALES",1,0,'R');
				    $pdf->Cell(25,5,number_format($valorventa,2),1,"R");
				    $pdf->Cell(25,5,number_format($descglobal,2),1,"R");
				    $pdf->Cell(25,5,number_format($igv,2),1,"R");
				    $pdf->Cell(50,5,number_format($importe,2),1,"R"); $pdf->Ln(); $pdf->Ln();
		        }
				$pdf->SetTitle("phuyu Peru - Reporte Ventas - Cliente"); $pdf->Output();
			}
		}
	}

	function consulta_reporte_ventas(){
		if (isset($_SESSION["phuyu_codusuario"])) {
			$this->request = json_decode(file_get_contents('php://input'));
			$comprobante = $this->filtro_comprobante_ventas("kardex", isset($this->request->datos->codcomprobantetipo) ? $this->request->datos->codcomprobantetipo : 0);

			$sucursales = '';
			$almacen = '';
			if ($this->request->datos->codsucursal!=0) {
				$sucursales = ' and kardex.codsucursal='.$this->request->datos->codsucursal;
			}

			if ($this->request->datos->codalmacen!=0) {
				$almacen = "and kardex.codalmacen=".$this->request->datos->codalmacen;
			}

			$valorventatotal = 0; $igvtotal=0; $icbpertotal=0;$totalgeneral=0;

			$lista = $this->db->query("select personas.documento,kardex.cliente,kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante,kardex.condicionpago, kardex.nrocomprobante, kardex.fechacomprobante,round(kardex.valorventa,2) AS valorventa,round(kardex.igv,2) AS IGV, kardex.descglobal, round(kardex.icbper,2) AS icbper,round(kardex.importe,2) AS importe,kardex.condicionpago, comprobantes.descripcion as tipo,sunat.estado as estadosunat,sunat.codigorespuesta as codigosunat,sunat.descripcion_cdr as respuestasunat from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) left join sunat.kardexsunat as sunat on(kardex.codkardex=sunat.codkardex) where kardex.fechacomprobante>=".$this->db->escape($this->request->datos->fechadesde)." and kardex.fechacomprobante<=".$this->db->escape($this->request->datos->fechahasta)." ".$comprobante." and kardex.codmovimientotipo=20 ".$almacen." ".$sucursales." and kardex.estado=1 order by kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.nrocomprobante")->result_array();

			foreach ($lista as $key => $value) {
				$valorventatotal = $valorventatotal + (double)$value["valorventa"];
				$igvtotal = $igvtotal + (double)$value['igv'];
				$icbpertotal = $icbpertotal + (double)$value['icbper'];
				$totalgeneral = $totalgeneral + (double)$value['importe'];
			}

			$totalreporte[0]["valorventatotal"] = number_format($valorventatotal,2,".","");
			$totalreporte[0]["igvtotal"] = number_format($igvtotal,2,".","");
			$totalreporte[0]["icbpertotal"] = number_format($icbpertotal,2,".","");
			$totalreporte[0]["totalgeneral"] = number_format($totalgeneral,2,".","");

			echo json_encode(['lista'=>$lista,'totalreporte'=>$totalreporte]);
		}
	}

	private function lista_tickets_ventas($request, $limite = 200)
	{
		$sucursales = "";
		$almacen = "";
		$comprobante = $this->filtro_comprobante_ventas("k", isset($request->codcomprobantetipo) ? $request->codcomprobantetipo : 0);

		if ((int)$request->codsucursal != 0) {
			$sucursales = " and k.codsucursal=".(int)$request->codsucursal;
		}

		if ((int)$request->codalmacen != 0) {
			$almacen = " and k.codalmacen=".(int)$request->codalmacen;
		}

		return $this->db->query("
			select k.codkardex, k.fechacomprobante, k.seriecomprobante, k.nrocomprobante, ct.abreviatura as tipo
			from kardex.kardex as k
			inner join caja.comprobantetipos as ct on(k.codcomprobantetipo=ct.codcomprobantetipo)
			where k.fechacomprobante >= ".$this->db->escape($request->fechadesde)."
			  and k.fechacomprobante <= ".$this->db->escape($request->fechahasta)."
			  and k.codmovimientotipo = 20
			  and k.estado = 1
			  ".$sucursales."
			  ".$almacen."
			  ".$comprobante."
			order by k.fechacomprobante, k.codcomprobantetipo, k.seriecomprobante, k.nrocomprobante
			limit ".(int)$limite."
		")->result_array();
	}

	private function imagen_base64($path)
	{
		if (empty($path) || !file_exists($path)) {
			return "";
		}

		$extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
		$mime = ($extension === "jpg" || $extension === "jpeg") ? "jpeg" : "png";
		return "data:image/".$mime.";base64,".base64_encode(file_get_contents($path));
	}

	private function qr_data_uri($texto, $size = 5)
	{
		if ((string)$texto === "") {
			return "";
		}

		$this->load->library("ciqrcode");
		ob_start();
		$this->ciqrcode->generate(array(
			"data" => (string)$texto,
			"level" => "H",
			"size" => $size
		));
		$png = ob_get_clean();

		return $png !== "" ? "data:image/png;base64,".base64_encode($png) : "";
	}

	private function mesa_restaurante($codkardex)
	{
		$tablas = $this->db->query("select to_regclass('kardex.kardexpedido') as kardexpedido, to_regclass('restaurante.mesaspedido') as mesaspedido")->row_array();
		if (empty($tablas["kardexpedido"]) || empty($tablas["mesaspedido"])) {
			return "";
		}

		$mesa = $this->db->query("
			select string_agg(distinct mp.nromesa::text, ' - ') as numero
			from restaurante.mesaspedido mp
			where mp.codpedido in (
				select p.codpedido from kardex.pedidos p where p.codkardex=".(int)$codkardex."
				union
				select kp.codpedido from kardex.kardexpedido kp where kp.codkardex=".(int)$codkardex."
			)
		")->row_array();

		return trim((string)($mesa["numero"] ?? ""));
	}

	private function ticket_pdf_data($codkardex)
	{
		$codkardex = (int)$codkardex;
		$venta = $this->db->query("
			select k.codkardex, k.fechacomprobante, k.conleyendaamazonia, ct.descripcion as comprobante,
				   ct.oficial, k.codcomprobantetipo, k.seriecomprobante, k.nrocomprobante, p.documento,
				   k.cliente, k.direccion, k.valorventa, k.descglobal, k.igv, k.icbper, k.importe,
				   k.codempleado, k.condicionpago, k.nroplaca, k.codsucursal
			from kardex.kardex as k
			inner join public.personas as p on(k.codpersona=p.codpersona)
			inner join caja.comprobantetipos as ct on(k.codcomprobantetipo=ct.codcomprobantetipo)
			where k.codkardex=".$codkardex."
			limit 1
		")->row_array();

		if (empty($venta)) {
			return array("estado" => 0, "mensaje" => "No se encontro la venta ".$codkardex);
		}

		$empresa = $this->db->query("select documento, razonsocial, nombrecomercial from public.personas where codpersona=1 limit 1")->row_array();
		$sucursal = $this->db->query("select sucursal.*, empresa.* from public.sucursales as sucursal inner join public.empresas as empresa on(sucursal.codempresa=empresa.codempresa) where sucursal.codsucursal=".(int)$venta["codsucursal"]." limit 1")->row_array();
		$parametros = $this->db->query("select *from public.empresas limit 1")->row_array();
		$principal = $this->db->query("select *from public.sucursales where principal=1 and estado=1 limit 1")->row_array();

		$totales = $this->db->query("
			select
				(select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=".$codkardex." and codafectacionigv='10') as gravado,
				(select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=".$codkardex." and codafectacionigv='20') as exonerado,
				(select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=".$codkardex." and codafectacionigv='30') as inafecto,
				(select coalesce(sum(subtotal),0) from kardex.kardexdetalle where codkardex=".$codkardex." and codafectacionigv='21') as gratuito
		")->row_array();

		$detalle = $this->db->query("
			select kd.item, kd.cantidad, p.descripcion as producto, u.descripcion as unidad,
				   kd.preciounitario, kd.subtotal, kd.descripcion
			from kardex.kardexdetalle as kd
			inner join almacen.productos as p on(p.codproducto=kd.codproducto)
			inner join almacen.unidades as u on(u.codunidad=kd.codunidad)
			where kd.codkardex=".$codkardex."
			order by kd.item
		")->result_array();

		$formato = $this->db->query("
			select *
			from caja.comprobantes
			where codcomprobantetipo=".(int)$venta["codcomprobantetipo"]."
			  and seriecomprobante=".$this->db->escape($venta["seriecomprobante"])."
			  and codsucursal=".(int)$venta["codsucursal"]."
			limit 1
		")->row_array();

		if (empty($formato)) {
			$formato = array("nombrecomercial" => "", "logo" => "", "slogan" => "", "publicidad" => "", "agradecimiento" => "", "impresionlogo" => 1);
		}

		$nombre = !empty($formato["nombrecomercial"]) ? $formato["nombrecomercial"] : (!empty($empresa["nombrecomercial"]) ? $empresa["nombrecomercial"] : $empresa["razonsocial"]);
		$logoArchivo = !empty($formato["logo"]) ? FCPATH."public/img/empresa/".$formato["logo"] : FCPATH."public/img/".($_SESSION["phuyu_logo"] ?? "");
		$fechavencimiento = $venta["fechacomprobante"];
		$credito = array();
		if ((int)$venta["condicionpago"] === 2) {
			$credito = $this->db->query("select *from kardex.creditos where codkardex=".$codkardex." limit 1")->row_array();
			if (!empty($credito["fechavencimiento"])) {
				$fechavencimiento = $credito["fechavencimiento"];
			}
		}

		$vendedor = $this->db->query("select razonsocial, telefono from public.personas where codpersona=".(int)$venta["codempleado"]." limit 1")->row_array();
		$empleado = $this->db->query("select p.razonsocial from public.empleados e inner join public.personas p on(p.codpersona=e.codpersona) where e.codpersona=".(int)$venta["codempleado"]." limit 1")->row_array();
		if (empty($empleado) && !empty($vendedor)) {
			$empleado = array("razonsocial" => $vendedor["razonsocial"]);
		}
		$cuentascorrientes = $this->db->query("select ct.*, b.descripcion as banco from caja.ctasctes ct inner join caja.bancos b on(ct.codbanco=b.codbanco) where ct.codpersona=1")->result_array();
		$movimiento = $this->db->query("select codmovimiento from caja.movimientos where codkardex=".$codkardex." limit 1")->row_array();
		$detallemovimiento = array();
		if (!empty($movimiento["codmovimiento"])) {
			$detallemovimiento = $this->db->query("select importeentregado, vuelto from caja.movimientosdetalle where codtipopago=1 and codmovimiento=".(int)$movimiento["codmovimiento"])->result_array();
		}

		$this->load->library("Number");
		$number = new Number();
		$total = number_format((float)$venta["importe"], 2, ".", "");
		$partes = explode(".", $total);
		$total_texto = $number->convertirNumeroEnLetras(round((float)$venta["importe"], 2));
		$texto_importe = "SON ".strtoupper($number->convertirNumeroEnLetras($partes[0]))." Y ".$partes[1]."/100 SOLES";
		$texto_qr = ($empresa["razonsocial"] ?? "")."|".$venta["seriecomprobante"]."|".$venta["nrocomprobante"]."|".number_format((float)$venta["igv"], 2, ".", "")."|".number_format((float)$venta["importe"], 2, ".", "")."|".$venta["fechacomprobante"]."|".$venta["documento"];

		return array(
			"estado" => 1,
			"data" => array(
				"modo_pdf" => true,
				"empresa" => $empresa,
				"sucursal" => $sucursal,
				"principal" => $principal,
				"parametros" => $parametros,
				"venta" => $venta,
				"credito" => $credito,
				"empleado" => $empleado,
				"vendedor" => $vendedor,
				"totales" => $totales,
				"detalle" => $detalle,
				"cuentascorrientes" => $cuentascorrientes,
				"formato" => $formato,
				"nombre_empresa" => $nombre,
				"nombre" => $nombre,
				"slogan" => !empty($formato["slogan"]) ? $formato["slogan"] : ($parametros["slogan"] ?? ""),
				"publicidad" => !empty($formato["publicidad"]) ? $formato["publicidad"] : ($parametros["publicidad"] ?? ""),
				"logo_src" => $this->imagen_base64($logoArchivo),
				"qr_src" => $this->qr_data_uri($texto_qr),
				"fechavencimiento" => $fechavencimiento,
				"total_texto" => $total_texto,
				"texto_importe" => $texto_importe,
				"detallemovimiento" => $detallemovimiento,
				"efectivo" => !empty($detallemovimiento) ? 1 : 0,
				"logoEmpresa" => $_SESSION["phuyu_logo"] ?? "",
				"mesa_restaurante" => $this->mesa_restaurante($codkardex)
			)
		);
	}

	private function generar_comprobante_pdf_binario($codkardex, $formato_pdf = "ticket")
	{
		$datos = $this->ticket_pdf_data($codkardex);
		if ($datos["estado"] != 1) {
			return $datos;
		}

		$formato_pdf = strtolower((string)$formato_pdf);
		if (!in_array($formato_pdf, array("ticket", "a5", "a4"))) {
			$formato_pdf = "ticket";
		}

		require_once FCPATH."vendor/autoload.php";
		if ($formato_pdf === "a4") {
			$view = "reportes/ventas/a4comprobante";
			$paper = "A4";
		} elseif ($formato_pdf === "a5") {
			$view = "reportes/ventas/a5venta";
			$paper = "A5";
		} else {
			$view = "facturacion/formato/ticket_Phuyu";
			$items = count($datos["data"]["detalle"]);
			$height = max(650, min(1800, 560 + ($items * 34)));
			$paper = array(0, 0, 226.77, $height);
		}

		$html = $this->load->view($view, $datos["data"], true);
		$tempDir = APPPATH."cache/dompdf";
		if (!is_dir($tempDir)) {
			@mkdir($tempDir, 0777, true);
		}

		$options = new \Dompdf\Options();
		$options->set("isHtml5ParserEnabled", true);
		$options->set("isRemoteEnabled", true);
		$options->set("defaultFont", "DejaVu Sans");
		$options->set("chroot", FCPATH);
		$options->set("tempDir", $tempDir);
		$options->set("fontDir", $tempDir);
		$options->set("fontCache", $tempDir);
		$options->set("dpi", 96);

		$dompdf = new \Dompdf\Dompdf($options);
		$dompdf->loadHtml($html, "UTF-8");
		$dompdf->setPaper($paper, "portrait");
		$dompdf->render();

		return array("estado" => 1, "pdf" => $dompdf->output());
	}

	private function zip_tickets_progress_path($progress_id)
	{
		$progress_id = preg_replace('/[^A-Za-z0-9_-]/', '', (string)$progress_id);
		if ($progress_id === "") {
			return "";
		}

		$dir = APPPATH."cache/tickets_zip_progress/";
		if (!is_dir($dir)) {
			@mkdir($dir, 0777, true);
		}

		return $dir.$progress_id.".json";
	}

	private function zip_tickets_set_progress($progress_id, $actual, $total, $estado = "procesando", $mensaje = "")
	{
		$path = $this->zip_tickets_progress_path($progress_id);
		if ($path === "") {
			return;
		}

		$porcentaje = $total > 0 ? round(($actual / $total) * 100, 2) : 0;
		file_put_contents($path, json_encode(array(
			"actual" => (int)$actual,
			"total" => (int)$total,
			"porcentaje" => $porcentaje,
			"estado" => $estado,
			"mensaje" => $mensaje
		)));
	}

	function zip_tickets_ventas_progreso()
	{
		if (!isset($_SESSION["phuyu_codusuario"])) {
			$this->output
				->set_content_type("application/json", "utf-8")
				->set_status_header(401)
				->set_output(json_encode(array("estado" => "error", "mensaje" => "Sesion expirada.")));
			return;
		}

		$progress_id = $this->input->get("progress_id", true);
		$path = $this->zip_tickets_progress_path($progress_id);
		if ($path === "" || !file_exists($path)) {
			$this->output
				->set_content_type("application/json", "utf-8")
				->set_output(json_encode(array("actual" => 0, "total" => 0, "porcentaje" => 0, "estado" => "iniciando", "mensaje" => "Iniciando proceso...")));
			return;
		}

		$this->output
			->set_content_type("application/json", "utf-8")
			->set_output(file_get_contents($path));
	}

	function zip_tickets_ventas()
	{
		if (!isset($_SESSION["phuyu_codusuario"])) {
			$this->output
				->set_content_type("application/json", "utf-8")
				->set_status_header(401)
				->set_output(json_encode(array("estado" => 0, "mensaje" => "Sesion expirada. Vuelva a iniciar sesion.")));
			return;
		}

		if (empty($_GET["datos"])) {
			$this->output
				->set_content_type("application/json", "utf-8")
				->set_status_header(400)
				->set_output(json_encode(array("estado" => 0, "mensaje" => "No se recibieron filtros para generar el ZIP.")));
			return;
		}

		if (!class_exists("ZipArchive")) {
			$this->output
				->set_content_type("application/json", "utf-8")
				->set_status_header(500)
				->set_output(json_encode(array("estado" => 0, "mensaje" => "La extension ZipArchive no esta disponible en PHP.")));
			return;
		}

		$request = json_decode($_GET["datos"]);
		$progress_id = $this->input->get("progress_id", true);
		$formato_pdf = isset($request->formato_pdf) ? strtolower((string)$request->formato_pdf) : "ticket";
		if (!in_array($formato_pdf, array("ticket", "a5", "a4"))) {
			$formato_pdf = "ticket";
		}
		$ventas = $this->lista_tickets_ventas($request, 200);
		$this->zip_tickets_set_progress($progress_id, 0, count($ventas), "procesando", "Preparando comprobantes...");
		if (empty($ventas)) {
			$comprobante = "todos los comprobantes";
			if ((int)($request->codcomprobantetipo ?? 0) > 0) {
				$tipo = $this->db->query(
					"select descripcion from caja.comprobantetipos where codcomprobantetipo=? limit 1",
					array((int)$request->codcomprobantetipo)
				)->row_array();
				if (!empty($tipo["descripcion"])) {
					$comprobante = $tipo["descripcion"];
				}
			}
			$this->output
				->set_content_type("application/json", "utf-8")
				->set_status_header(422)
				->set_output(json_encode(array(
					"estado" => 0,
					"mensaje" => "No hay ventas activas para descargar con ".$comprobante." entre ".$request->fechadesde." y ".$request->fechahasta."."
				)));
			return;
		}

		$dir = APPPATH."cache/tickets_zip/";
		if (!is_dir($dir)) {
			@mkdir($dir, 0777, true);
		}

		$nombreZip = "tickets_ventas_".$request->fechadesde."_".$request->fechahasta."_".date("His").".zip";
		$rutaZip = $dir.$nombreZip;
		$zip = new ZipArchive();
		if ($zip->open($rutaZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
			$this->output
				->set_content_type("application/json", "utf-8")
				->set_status_header(500)
				->set_output(json_encode(array("estado" => 0, "mensaje" => "No se pudo crear el archivo ZIP temporal.")));
			return;
		}

		$errores = array();
		$totalVentas = count($ventas);
		$procesados = 0;
		foreach ($ventas as $venta) {
			$procesados++;
			$pdf = $this->generar_comprobante_pdf_binario($venta["codkardex"], $formato_pdf);
			if ($pdf["estado"] != 1) {
				$errores[] = $venta["codkardex"].": ".$pdf["mensaje"];
				$this->zip_tickets_set_progress($progress_id, $procesados, $totalVentas, "procesando", "Procesando comprobantes...");
				continue;
			}

			$archivo = preg_replace('/[^A-Za-z0-9._-]/', '_', strtoupper($formato_pdf)."-".$venta["tipo"]."-".$venta["seriecomprobante"]."-".$venta["nrocomprobante"]).".pdf";
			$zip->addFromString($archivo, $pdf["pdf"]);
			$this->zip_tickets_set_progress($progress_id, $procesados, $totalVentas, "procesando", "Procesando comprobantes...");
		}

		if (!empty($errores)) {
			$zip->addFromString("errores.txt", implode(PHP_EOL, $errores));
		}

		$zip->close();
		$this->zip_tickets_set_progress($progress_id, $totalVentas, $totalVentas, "completado", "ZIP generado correctamente.");

		header("Content-Type: application/zip");
		header("Content-Disposition: attachment; filename=".$nombreZip);
		header("Content-Length: ".filesize($rutaZip));
		readfile($rutaZip);
		@unlink($rutaZip);
	}
	
	function pdf_reporte_ventas(){
		if (isset($_SESSION["phuyu_codusuario"])) {
			if ($_GET["datos"]) {
				$this->request = json_decode($_GET["datos"]); $this->tipos = json_decode($_GET["tipos"]); $titulo = "";

				$item = 0; $comprobantes = "(";
				foreach ($this->tipos as $key => $value) { $item = $item + 1;
					if ($item==count($this->tipos)) {
						$comprobantes .= "kardex.codcomprobantetipo=".$value->codcomprobantetipo." )";
					}else{
						$comprobantes .= "kardex.codcomprobantetipo=".$value->codcomprobantetipo." or ";
					}
				}

				if ($this->request->codsucursal==0) {
					$sucursales = $this->db->query("select *from public.sucursales where estado=1")->result_array();
				}else{
					$sucursales = $this->db->query("select *from public.sucursales where codsucursal=".$this->request->codsucursal)->result_array();
				}

				if ($this->request->codalmacen==0) {
					$almacen = "";
				}else{
					$almacen = "and kardex.codalmacen=".$this->request->codalmacen;
				}

				if ($this->request->estado == 0) {
					$titulo = "ANULADAS";
				}
				$this->load->library('Pdf2'); $pdf = new Pdf2(); $pdf->AddPage();
				$pdf->pdf_header("REPORTE DE VENTAS ".$titulo. "(DE ".$this->request->fechadesde." A ".$this->request->fechahasta.")","");
                
                foreach ($sucursales as $key => $value) {
					$lista = $this->db->query("select personas.documento,kardex.cliente,kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante,kardex.condicionpago, kardex.nrocomprobante, kardex.fechacomprobante,kardex.valorventa,kardex.igv, kardex.descglobal, kardex.icbper, kardex.importe,kardex.condicionpago, comprobantes.descripcion as tipo from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.fechacomprobante>='".$this->request->fechadesde."' and kardex.fechacomprobante<='".$this->request->fechahasta."' and ".$comprobantes." and kardex.codmovimientotipo=20 ".$almacen." and kardex.codsucursal=".$value["codsucursal"]." and kardex.estado=".$this->request->estado." order by kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.nrocomprobante")->result_array();

					$pdf->Ln(8); $pdf->SetFont('Arial', 'B', 10);
			        $pdf->Cell(200, 5, utf8_decode("SUCURSAL: ".$value["descripcion"]),0,0,'C');
                    $pdf->Ln(8);
					$columnas = array("N°","FECHA","DOCUMENTO","CLIENTE","SUBTOTAL","IGV","ICBPER","TOTAL","CONDICION");
					$w = array(10,15,20,73,18,10,12,15,18); $pdf->pdf_tabla_head($columnas,$w,8);

					$pdf->SetWidths($w); $pdf->SetLineHeight(5); $pdf->SetFont('Arial','',7);
		            $item = 0; $valorventa = 0; $descglobal = 0; $igv = 0; $icbper = 0; $importe = 0;
					foreach($lista as $value){ 
						$item = $item + 1; $valorventa = $valorventa + $value["valorventa"]; 
						$descglobal = $descglobal + $value["descglobal"];
						$igv = $igv + $value["igv"]; $icbper = $icbper + $value["icbper"]; 
						$importe = $importe + $value["importe"];

						$datos = array("0".$item);
						array_push($datos,$value["fechacomprobante"]);
						array_push($datos,$value["seriecomprobante"]."-".$value["nrocomprobante"]);
						array_push($datos,utf8_decode($value["cliente"]));

						array_push($datos,number_format($value["valorventa"],2));
						array_push($datos,number_format($value["igv"],2));
						array_push($datos,number_format($value["icbper"],2));
						array_push($datos,number_format($value["importe"],2));
						if ($value["condicionpago"]==1) {
							array_push($datos,"CONTADO");
					    }else{
					    	array_push($datos,"CREDITO");
					    }
		                $pdf->Row($datos);
					}
					$pdf->Cell(array_sum($w),0,'','T'); $pdf->Ln();

					$pdf->SetFont('Arial','B',8);
					$pdf->Cell(118,5,"TOTALES",1,0,'R');
				    $pdf->Cell($w[4],5,number_format($valorventa,2),1,"R");
				    $pdf->Cell($w[5],5,number_format($igv,2),1,"R");
				    $pdf->Cell($w[6],5,number_format($icbper,2),1,"R");
				    $pdf->Cell(35,5,number_format($importe,2),1,"R");
                }
				$pdf->SetTitle("phuyu Peru - Reporte de Ventas"); $pdf->Output();
			}
		}
	}

	function pdf_reporte_ventas_det(){
		if (isset($_SESSION["phuyu_codusuario"])) {
			if ($_GET["datos"]) {
				$this->request = json_decode($_GET["datos"]); $this->tipos = json_decode($_GET["tipos"]); $titulo = "";

				$item = 0; $comprobantes = "(";
				foreach ($this->tipos as $key => $value) { $item = $item + 1;
					if ($item==count($this->tipos)) {
						$comprobantes .= "kardex.codcomprobantetipo=".$value->codcomprobantetipo." )";
					}else{
						$comprobantes .= "kardex.codcomprobantetipo=".$value->codcomprobantetipo." or ";
					}
				}

				if ($this->request->codsucursal==0) {
					$sucursales = $this->db->query("select *from public.sucursales where estado=1")->result_array();
				}else{
					$sucursales = $this->db->query("select *from public.sucursales where codsucursal=".$this->request->codsucursal)->result_array();
				}

				if ($this->request->codalmacen==0) {
					$almacen = "";
				}else{
					$almacen = "and kardex.codalmacen=".$this->request->codalmacen;
				}

				if ($this->request->estado == 0) {
					$titulo = "ANULADAS";
				}
				$this->load->library('Pdf2'); $pdf = new Pdf2(); $pdf->AddPage();

				$pdf->pdf_header("REPORTE DE VENTAS ".$titulo. "(DE ".$this->request->fechadesde." A ".$this->request->fechahasta.")","");


                foreach ($sucursales as $key => $value) {
					$lista = $this->db->query("select personas.documento,kardex.cliente,kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante,kardex.condicionpago, kardex.nrocomprobante, kardex.fechacomprobante,kardex.valorventa,kardex.igv, kardex.descglobal, kardex.icbper, kardex.importe,kardex.condicionpago, comprobantes.descripcion as tipo from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.fechacomprobante>='".$this->request->fechadesde."' and kardex.fechacomprobante<='".$this->request->fechahasta."' and ".$comprobantes." and kardex.codmovimientotipo=20 ".$almacen." AND kardex.codsucursal=".$value["codsucursal"]." and kardex.estado=".$this->request->estado." order by kardex.fechacomprobante, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.nrocomprobante")->result_array();

					$pdf->Ln(8); $pdf->SetFont('Arial', 'B', 10);
			        $pdf->Cell(200, 5, utf8_decode("SUCURSAL: ".$value["descripcion"]),0,0,'C');
                    $pdf->Ln(8);

					$columnas = array("N°","FECHA","DOCUMENTO","CLIENTE","SUBTOTAL","ICB","IGV","TOTAL","CONDICION");
					$w = array(10,15,20,73,18,10,10,15,20); $pdf->pdf_tabla_head($columnas,$w,8);

		            $item = 0; $valorventa = 0; $descglobal = 0; $igv = 0; $importe = 0; $icbper = 0;
					foreach($lista as $value){
						$pdf->SetWidths(array(10,15,20,73,18,10,10,15,20));
		            	$pdf->SetLineHeight(5); $pdf->SetFont('Arial','B',7);

						$item = $item + 1; $valorventa = $valorventa + $value["valorventa"]; 
						$descglobal = $descglobal + $value["descglobal"];
						$igv = $igv + $value["igv"]; $icbper = $icbper + $value["igv"];
						$importe = $importe + $value["importe"];

						$datos = array("0".$item);
						array_push($datos,$value["fechacomprobante"]);
						array_push($datos,$value["seriecomprobante"]."-".$value["nrocomprobante"]);
						array_push($datos,utf8_decode($value["cliente"]));

						array_push($datos,number_format($value["valorventa"],2));
						array_push($datos,number_format($value["icbper"],2));
						array_push($datos,number_format($value["igv"],2));
						array_push($datos,number_format($value["importe"],2));
						if ($value["condicionpago"]==1) {
							array_push($datos,"CONTADO");
					    }else{
					    	array_push($datos,"CREDITO");
					    }
		                $pdf->Row($datos);

		                $detalle = $this->db->query("select kd.*,p.descripcion as producto,u.descripcion as unidad,p.codigo from kardex.kardexdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codkardex=".$value["codkardex"]." and kd.estado=1 order by kd.item")->result_array();

		             	$pdf->SetLineHeight(5); $pdf->SetFont('Arial','B',7);
		                $columnas = array("CANT","DESCRIPCION DETALLE VENTA","UNI.MED","P.UNITARIO","IGV","SUBTOTAL"); $wd = array(10,108,18,20,15,20);
		                for($i=0;$i<count($columnas);$i++){
							$pdf->SetFillColor(230,230,230);
				            $pdf->Cell($wd[$i],5,utf8_decode($columnas[$i]),1,0,'L',True);
				        } $pdf->Ln();

				        $pdf->SetWidths(array(10,108,18,20,15,20)); $pdf->SetLineHeight(5); $pdf->SetFont('Arial','',7);
				        foreach ($detalle as $v) {
				        	$datos = array(number_format($v["cantidad"],0));
							array_push($datos,utf8_decode($v["codigo"].' - '.$v["producto"].' '.$v["descripcion"]));
							array_push($datos,utf8_decode($v["unidad"]));

							array_push($datos,number_format($v["preciounitario"],2));
							array_push($datos,number_format($v["igv"],2));
							array_push($datos,number_format($v["subtotal"],2));
			                $pdf->Row($datos);
				        }
					}
					$pdf->Cell(array_sum($w),0,'','T'); $pdf->Ln();

					$pdf->SetFont('Arial','B',8);
					$pdf->Cell(118,5,"TOTALES",1,0,'R');
				    $pdf->Cell($w[4],5,number_format($valorventa,2),1,"R");
				    $pdf->Cell($w[5],5,number_format($icbper,2),1,"R");
				    $pdf->Cell($w[6],5,number_format($igv,2),1,"R");
				    $pdf->Cell(35,5,number_format($importe,2),1,"R");
                }
				$pdf->SetTitle("phuyu Peru - Reporte de Ventas Detallado"); $pdf->Output();
			}
		}
	}

	function pdf_contable_ventas(){
		if (isset($_SESSION["phuyu_codusuario"])) {
			if ($_GET["datos"]) {
				$this->request = json_decode($_GET["datos"]); $this->tipos = json_decode($_GET["tipos"]);

				$item = 0; $comprobantes = "(";
				foreach ($this->tipos as $key => $value) { $item = $item + 1;
					if ($item==count($this->tipos)) {
						$comprobantes .= "kardex.codcomprobantetipo=".$value->codcomprobantetipo." )";
					}else{
						$comprobantes .= "kardex.codcomprobantetipo=".$value->codcomprobantetipo." or ";
					}
				}

				if ($this->request->codalmacen==0) {
					$almacen = "";
				}else{
					$almacen = "and kardex.codalmacen=".$this->request->codalmacen;
				}

				$this->load->library('Pdf2'); $pdf = new Pdf2(); $pdf->AddPage('L','A4',0);

				$pdf->SetFont('Arial', 'B', 12);
				$pdf->Cell(0,7,utf8_decode("REPORTE DE VENTAS ".$_SESSION["phuyu_empresa"]),0,1,'C',0); $pdf->Ln(1);
				$pdf->SetFont('Arial', 'B', 8);
				$pdf->Cell(0,7,"DEL ".$this->request->fechadesde." AL ".$this->request->fechahasta,0,1,'C',0); $pdf->Ln(2);

				$empresa = $this->db->query("select *from public.personas where codpersona=1")->result_array();
				$pdf->SetFont('Arial', '', 10);
				$pdf->Cell(150,5,"RUC: ".$empresa[0]["documento"],0,0,'L'); $pdf->Cell(100,5,"MONEDA: SOLES",0,1,"R");

				if ($this->request->codsucursal==0) {
					$sucursales = $this->db->query("select *from public.sucursales where estado=1")->result_array();
				}else{
					$sucursales = $this->db->query("select *from public.sucursales where codsucursal=".$this->request->codsucursal)->result_array();
				}

				$valorventa_general = 0; $igv_general = 0; $icbper_general = 0; $total_general = 0;
				foreach ($sucursales as $key => $value) {
					$pdf->SetFont('Arial', 'B', 10); $pdf->Ln(5); $pdf->SetTextColor(0,0,0);
					$pdf->Cell(0,7,utf8_decode("SUCURSAL: ".$value["descripcion"]),0,1,'C',0); $pdf->Ln(1);

					$lista = $this->db->query("select kardex.codmovimientotipo,  kardex.codkardex_ref, personas.documento,personas.razonsocial,personas.coddocumentotipo, kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.condicionpago, kardex.nrocomprobante, kardex.fechacomprobante,kardex.valorventa,kardex.igv, kardex.icbper, kardex.importe,kardex.estado,comprobantes.abreviatura as tipo from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.fechacomprobante>='".$this->request->fechadesde."' and kardex.fechacomprobante<='".$this->request->fechahasta."' and ".$comprobantes." and (kardex.codmovimientotipo=20 OR kardex.codmovimientotipo=8) ".$almacen." and kardex.codsucursal=".$value["codsucursal"]." order by kardex.fechacomprobante, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.nrocomprobante")->result_array();

					foreach ($lista as $k => $v) {
						if($v["codmovimientotipo"]==8){
							$comprobante_ref = $this->db->query("select k.*, comprobantes.oficial as tipo from kardex.kardex as k inner join caja.comprobantetipos as comprobantes on(k.codcomprobantetipo=comprobantes.codcomprobantetipo) where k.codkardex=".$v["codkardex_ref"])->result_array();
							$lista[$k]["fecharef"] = $comprobante_ref[0]["fechacomprobante"];
							$lista[$k]["tiporef"] = $comprobante_ref[0]["tipo"];
							$lista[$k]["serie_ref"] = $comprobante_ref[0]["seriecomprobante"];
							$lista[$k]["numero_ref"] = $comprobante_ref[0]["nrocomprobante"];
						}else{
						    $lista[$k]["fecharef"] = "";
							$lista[$k]["tiporef"] = "";
							$lista[$k]["serie_ref"] = "";
							$lista[$k]["numero_ref"] = "";
						}
					}

					$pdf->SetFont('Arial', 'B', 8);
					$pdf->Cell(225,5,' ','LTR',0,'L',0);
					$pdf->Cell(50,5,"DOCUMENTO QUE SE MODIFICA",1,0,'L',0);$pdf->Ln();

					$columnas = array("FECHA","T.DOC","N°DOC","DOC.IDEN","RAZON SOCIAL","VALOR","IGV","ICBPER","TOTAL","IMP. N.C","FECHA","T.DOC","N°DOC");
					$w = array(15,15,20,20,87,15,10,13,15,15,15,15,20); $pdf->pdf_tabla_head($columnas,$w,8);

					$pdf->SetWidths($w); $pdf->SetLineHeight(5); $pdf->SetFont('Arial','',7);
					
					$valorventa = 0; $igv = 0; $icbper = 0; $total = 0;
	            	foreach ($lista as $key => $value) {
	            		if ($value["estado"]==0) {
	            			$pdf->SetTextColor(250,10,0); 
	            		}else{
	            			$pdf->SetTextColor(0,0,0);
	            		}
	            		
	            		$datos = array($value["fechacomprobante"]);
						array_push($datos,$value["tipo"]);
						array_push($datos,$value["seriecomprobante"]."-".$value["nrocomprobante"]);
						if ($value["coddocumentotipo"]==1) {
							array_push($datos,"");
						}else{
							array_push($datos,$value["documento"]);
						}

						if ($value["estado"]==0) {
							array_push($datos,"ANULADO");
							array_push($datos,number_format(0,2)); array_push($datos,number_format(0,2));
							array_push($datos,number_format(0,2));
							array_push($datos,number_format(0,2)); array_push($datos,number_format(0,2));
						}else{
							$valorventa = $valorventa + $value["valorventa"]; 
							$igv = $igv + $value["igv"]; 
							$icbper = $icbper + $value["icbper"]; 
							$total = $total + $value["importe"];

							array_push($datos,utf8_decode($value["razonsocial"]));
							array_push($datos,number_format($value["valorventa"],2));
							array_push($datos,number_format($value["igv"],2));
							array_push($datos,number_format($value["icbper"],2));
							array_push($datos,number_format($value["importe"],2));
							array_push($datos,number_format(0,2));
						}
						array_push($datos,$value["fecharef"]); array_push($datos,$value["tiporef"]); array_push($datos,$value["serie_ref"].'-'.$value["numero_ref"]);
		                $pdf->Row($datos);
	            	}
	            	$pdf->Cell(array_sum($w),0,'','T'); $pdf->Ln();

					$pdf->SetFont('Arial','B',8); $pdf->SetTextColor(250,10,0);
					$pdf->Cell(157,5,utf8_decode($empresa[0]["direccion"]),1,0,'R');
				    $pdf->Cell(15,5,number_format($valorventa,2),1,"R");
				    $pdf->Cell(10,5,number_format($igv,2),1,"R");
				    $pdf->Cell(13,5,number_format($icbper,2),1,"R");
				    $pdf->Cell(15,5,number_format($total,2),1,"R");
				    $pdf->Cell(15,5,number_format(0,2),1,"R");
				    $pdf->Cell(50,5,"",1,"R"); $pdf->Ln();

				    $pdf->Cell(157,5,utf8_decode("TOTAL NETO SUCURSAL S/:"),1,0,'R');
				    $pdf->Cell(25,5,number_format($total,2),1,"R");
				    $pdf->Cell(93,5,"",1,"R"); $pdf->Ln();

				    $valorventa_general = $valorventa_general + $valorventa; 
				    $igv_general = $igv_general + $igv; 
				    $icbper_general = $icbper_general + $icbper; 
				    $total_general = $total_general + $total;
				}

				$pdf->SetTextColor(0,0,0);

				$pdf->Cell(157,5,utf8_decode("TOTAL GENERAL S/:"),1,0,'R');
			    $pdf->Cell(15,5,number_format($valorventa_general,2),1,"R");
			    $pdf->Cell(10,5,number_format($igv_general,2),1,"R");
			    $pdf->Cell(13,5,number_format($icbper_general,2),1,"R");
			    $pdf->Cell(15,5,number_format($total_general,2),1,"R");
			    $pdf->Cell(15,5,number_format(0,2),1,"R");
			    $pdf->Cell(50,5,"",1,"R"); $pdf->Ln();

			    $pdf->Cell(157,5,utf8_decode("TOTAL NETO GENERAL S/:"),1,0,'R');
			    $pdf->Cell(25,5,number_format($total_general,2),1,"R");
			    $pdf->Cell(93,5,"",1,"R"); $pdf->Ln();

				$pdf->SetTitle("phuyu Peru - Reporte de Ventas"); $pdf->Output();
			}
		}
	}

	function excel_contable_ventas(){
		if (isset($_SESSION["phuyu_codusuario"])) {
			if ($_GET["datos"]) {
				$this->request = json_decode($_GET["datos"]); $this->tipos = json_decode($_GET["tipos"]);

				$item = 0; $comprobantes = "(";
				foreach ($this->tipos as $key => $value) { $item = $item + 1;
					if ($item==count($this->tipos)) {
						$comprobantes .= "kardex.codcomprobantetipo=".$value->codcomprobantetipo." )";
					}else{
						$comprobantes .= "kardex.codcomprobantetipo=".$value->codcomprobantetipo." or ";
					}
				}

				if ($this->request->codalmacen==0) {
					$almacen = "";
				}else{
					$almacen = "and kardex.codalmacen=".$this->request->codalmacen;
				}

				$empresa = $this->db->query("select *from public.personas where codpersona=1")->result_array();

				$sucursal = '';

				if ($this->request->codsucursal==0) {
					$sucursales = $this->db->query("select *from public.sucursales where estado=1")->result_array();
				}else{
					$sucursales = $this->db->query("select *from public.sucursales where codsucursal=".$this->request->codsucursal)->result_array();
					$sucursal = ' AND kardex.codsucursal='.$this->request->codsucursal;
				}

				$lista = $this->db->query("select personas.documento,personas.razonsocial,personas.coddocumentotipo, kardex.codkardex,kardex.codkardex_ref, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.condicionpago, kardex.nrocomprobante, kardex.fechacomprobante,kardex.valorventa,kardex.igv, kardex.icbper,kardex.importe,kardex.estado,comprobantes.abreviatura as tipo,comprobantes.oficial,tipodocumento.oficial as tipodocumento,kardex.codmovimientotipo,sunat.estado as estadosunat,sunat.codigorespuesta as codigosunat,sunat.descripcion_cdr as respuestasunat from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join public.documentotipos as tipodocumento on (personas.coddocumentotipo=tipodocumento.coddocumentotipo) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) left join sunat.kardexsunat as sunat on(kardex.codkardex=sunat.codkardex) where kardex.fechacomprobante>='".$this->request->fechadesde."' and kardex.fechacomprobante<='".$this->request->fechahasta."' and ".$comprobantes." and (kardex.codmovimientotipo=20 or kardex.codmovimientotipo=8) ".$almacen." ".$sucursal." order by kardex.fechacomprobante, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.nrocomprobante")->result_array();
				foreach ($lista as $k => $v) {
					if($v["codmovimientotipo"]==8){
						$comprobante_ref = $this->db->query("select k.*, comprobantes.oficial as tipo from kardex.kardex as k inner join caja.comprobantetipos as comprobantes on(k.codcomprobantetipo=comprobantes.codcomprobantetipo) where k.codkardex=".$v["codkardex_ref"])->result_array();
						$lista[$k]["fecharef"] = $comprobante_ref[0]["fechacomprobante"];
						$lista[$k]["tiporef"] = $comprobante_ref[0]["tipo"];
						$lista[$k]["serie_ref"] = $comprobante_ref[0]["seriecomprobante"];
						$lista[$k]["numero_ref"] = $comprobante_ref[0]["nrocomprobante"];
					}else{
					    $lista[$k]["fecharef"] = "";
						$lista[$k]["tiporef"] = "";
						$lista[$k]["serie_ref"] = "";
						$lista[$k]["numero_ref"] = "";
					}
				}

				$fechadesde= $this->request->fechadesde; $fechahasta = $this->request->fechahasta;

				$periodo = explode("-", $fechadesde);
				
				$this->load->view("reportes/ventas/ventasxls",compact("empresa","sucursales","fechadesde","fechahasta","periodo","lista"));
			}
		}
	}

	function pdf_productospedidos(){
		if (isset($_SESSION["phuyu_codusuario"])) {
			if ($_GET["datos"]) {
				$this->request = json_decode($_GET["datos"]); $titulo = "";

				if ($this->request->codsucursal==0) {
					$sucursales = $this->db->query("select *from public.sucursales where estado=1")->result_array();
				}else{
					$sucursales = $this->db->query("select *from public.sucursales where codsucursal=".$this->request->codsucursal)->result_array();
				}

				if ($this->request->codalmacen==0) {
					$almacen = "";
				}else{
					$almacen = "and kardex.codalmacen=".$this->request->codalmacen;
				}

				if ($this->request->estado == 0) {
					$titulo = "ANULADAS";
				}
				$this->load->library('Pdf2'); $pdf = new Pdf2(); $pdf->AddPage();
				$pdf->pdf_header("REPORTE GENERAL ".$titulo. "(DE ".$this->request->fechadesde." A ".$this->request->fechahasta.")","");
                
                foreach ($sucursales as $key => $value) {
					$lista = $this->db->query("select personas.documento,kardex.cliente,kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante,kardex.condicionpago, kardex.nrocomprobante, kardex.fechacomprobante,kardex.valorventa,kardex.igv, kardex.descglobal, kardex.icbper, kardex.importe,kardex.condicionpago, comprobantes.descripcion as tipo from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.fechacomprobante>='".$this->request->fechadesde."' and kardex.fechacomprobante<='".$this->request->fechahasta."' and kardex.codmovimientotipo=20 ".$almacen." and kardex.codsucursal=".$value["codsucursal"]." and kardex.estado=".$this->request->estado." order by kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.nrocomprobante")->result_array();

					$pdf->Ln(8); $pdf->SetFont('Arial', 'B', 10);
			        $pdf->Cell(200, 5, utf8_decode("PRODUCTOS POR CONFIRMAR EN PEDIDOS"),0,0,'C');
                    $pdf->Ln(8);
					$columnas = array("ID","DESCRIPCION DEL PRODUCTO","UNIDAD","STOCK ACTUAL","CANT. X CONFIRMAR","DIFERENCIA");
					$w = array(10,70,25,25,30,30,12,15,18); $pdf->pdf_tabla_head($columnas,$w,7);

					$pdf->SetWidths($w); $pdf->SetLineHeight(5); $pdf->SetFont('Arial','',7);
		            $item = 0; $valorventa = 0; $descglobal = 0; $igv = 0; $icbper = 0; $importe = 0;
					foreach($lista as $value){ 
						$item = $item + 1; $valorventa = $valorventa + $value["valorventa"]; 
						$descglobal = $descglobal + $value["descglobal"];
						$igv = $igv + $value["igv"]; $icbper = $icbper + $value["icbper"]; 
						$importe = $importe + $value["importe"];

						$datos = array("0".$item);
						array_push($datos,$value["fechacomprobante"]);
						array_push($datos,$value["seriecomprobante"]."-".$value["nrocomprobante"]);
						array_push($datos,utf8_decode($value["cliente"]));

						array_push($datos,number_format($value["valorventa"],2));
						array_push($datos,number_format($value["igv"],2));
						array_push($datos,number_format($value["icbper"],2));
						array_push($datos,number_format($value["importe"],2));
						if ($value["condicionpago"]==1) {
							array_push($datos,"CONTADO");
					    }else{
					    	array_push($datos,"CREDITO");
					    }
		                $pdf->Row($datos);
					}
					$pdf->Cell(array_sum($w),0,'','T'); $pdf->Ln();

					$pdf->SetFont('Arial','B',8);
					$pdf->Cell(118,5,"TOTALES",1,0,'R');
				    $pdf->Cell($w[4],5,number_format($valorventa,2),1,"R");
				    $pdf->Cell($w[5],5,number_format($igv,2),1,"R");
				    $pdf->Cell($w[6],5,number_format($icbper,2),1,"R");
				    $pdf->Cell(35,5,number_format($importe,2),1,"R");
                }
				$pdf->SetTitle("phuyu Peru - Reporte de Ventas"); $pdf->Output();
			}
		}
	}
}
