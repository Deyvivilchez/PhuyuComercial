<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Vendedores extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			$sucursales = $this->db->query("select *from public.sucursales where estado=1")->result_array();
			$where = '';
			if($_SESSION["phuyu_codperfil"]==5){
				$where = ' AND persona.codpersona='.$_SESSION["phuyu_codempleado"];
			}
			$vendedores = $this->db->query("select persona.codpersona,persona.razonsocial from public.personas as persona inner join public.empleados as empleado on(persona.codpersona=empleado.codpersona) where empleado.estado=1 ".$where." and empleado.codcargo=4")->result_array();
			$comprobantes = $this->db->query("select distinct(ct.codcomprobantetipo) as codigo, ct.* from caja.comprobantetipos as ct inner join caja.comprobantes as c on(ct.codcomprobantetipo=c.codcomprobantetipo) where c.codsucursal=".$_SESSION["phuyu_codsucursal"]." and c.codcomprobantetipo>=5 and c.estado=1")->result_array();
			$this->load->view("reportes/vendedores/index",compact("sucursales","vendedores","comprobantes"));
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

				$lista = $this->db->query("select distinct(kd.codproducto) as codproducto,p.descripcion,p.codigo from kardex.kardex as k inner join kardex.kardexdetalle as kd on(k.codkardex=kd.codkardex) inner join almacen.productos as p on(kd.codproducto=p.codproducto) where k.codmovimientotipo=20 ".$filtro." and k.fechacomprobante>='".$this->request->fechadesde."' and k.fechacomprobante<='".$this->request->fechahasta."' and k.estado=1 ".$filtro." order by p.descripcion")->result_array();

				$this->load->view("reportes/ventas/masvendidosxls",compact("vendedor_texto","lista"));
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

		        if ($this->request->codvendedor == "") {
					$vendedor_texto = 'VENTAS DESDE '.$this->request->fechadesde.' HASTA '.$this->request->fechahasta.' - TODOS LOS VENDEDORES';
					$filtro = "";
				}else{
					$vendedor = $this->db->query("select documento, razonsocial from public.personas where codpersona=".$this->request->codvendedor)->result_array();
					$vendedor_texto = 'VENTAS DESDE '.$this->request->fechadesde.' HASTA '.$this->request->fechahasta.' - VENDEDOR: '.$vendedor[0]["razonsocial"];
					$filtro = "and kardex.codempleado=".$this->request->codvendedor;
				}

				$lista = $this->db->query("select personas.documento,kardex.cliente,personas.coddocumentotipo, kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.estado, kardex.condicionpago,kardex.nrocomprobante, kardex.fechacomprobante,kardex.valorventa,kardex.igv, kardex.importe,kardex.estado,comprobantes.abreviatura as tipo from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join public.empleados as empleados on (kardex.codempleado=empleados.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codmovimientotipo=20 and kardex.fechacomprobante>='".$this->request->fechadesde."' and kardex.fechacomprobante<='".$this->request->fechahasta."' and kardex.estado=".$this->request->estado." ".$filtro." AND empleados.codcargo = 4 order by kardex.fechacomprobante, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.nrocomprobante")->result_array();

				$fechadesde= $this->request->fechadesde; $fechahasta = $this->request->fechahasta;
				
				$this->load->view("reportes/ventas/ventasvendedorxls",compact("vendedor_texto","lista","tipos"));
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

				$lista = $this->db->query("select personas.documento,kardex.cliente,personas.coddocumentotipo, kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.estado, kardex.condicionpago,kardex.nrocomprobante, kardex.fechacomprobante,kardex.valorventa,kardex.igv, kardex.importe,kardex.estado,comprobantes.abreviatura as tipo from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join public.empleados as empleados on (kardex.codempleado=empleados.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codmovimientotipo=20 and kardex.fechacomprobante>='".$this->request->fechadesde."' and kardex.fechacomprobante<='".$this->request->fechahasta."' and kardex.estado=".$this->request->estado." ".$filtro." AND empleados.codcargo=4 order by kardex.fechacomprobante, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.nrocomprobante")->result_array();

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
				
				$this->load->view("reportes/ventas/ventasclientesxls",compact("fecha","socios","tipos","almacen"));
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

	function consulta_reporte_vendedores(){
		if (isset($_SESSION["phuyu_codusuario"])) {
			$this->request = json_decode(file_get_contents('php://input'));

			$valorventatotal = 0; $igvtotal=0; $icbpertotal=0;$totalgeneral=0;
			$codvendedor = '';
			if($this->request->codvendedor!=""){
				$codvendedor = ' AND codempleado='.$this->request->codvendedor;
			}

			$lista = $this->db->query("select personas.documento,kardex.cliente,kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante,kardex.condicionpago, kardex.nrocomprobante, kardex.fechacomprobante,round(kardex.valorventa,2) AS valorventa,round(kardex.igv,2) AS IGV, kardex.descglobal, round(kardex.icbper,2) AS icbper,round(kardex.importe,2) AS importe,kardex.condicionpago, comprobantes.descripcion as tipo from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join public.empleados as empleados on (kardex.codempleado=empleados.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.fechacomprobante>='".$this->request->fechadesde."' and kardex.fechacomprobante<='".$this->request->fechahasta."' and kardex.codmovimientotipo=20 ".$codvendedor." and kardex.estado=".$this->request->estado." and empleados.codcargo = 4 order by kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.nrocomprobante")->result_array();

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
	
	function pdf_reporte_ventas(){
		if (isset($_SESSION["phuyu_codusuario"])) {
			if ($_GET["datos"]) {
				$this->request = json_decode($_GET["datos"]); 

				if ($this->request->estado == 0) {
					$titulo = "ANULADAS";
				}
				$this->load->library('Pdf2'); $pdf = new Pdf2(); $pdf->AddPage();
				$pdf->pdf_header("REPORTE DE VENTAS ".$titulo. "(DE ".$this->request->fechadesde." A ".$this->request->fechahasta.")","");
                
                foreach ($sucursales as $key => $value) {
					$lista = $this->db->query("select personas.documento,kardex.cliente,kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante,kardex.condicionpago, kardex.nrocomprobante, kardex.fechacomprobante,kardex.valorventa,kardex.igv, kardex.descglobal, kardex.icbper, kardex.importe,kardex.condicionpago, comprobantes.descripcion as tipo from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join public.empleados as empleados on (kardex.codempleado=empleados.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.fechacomprobante>='".$this->request->fechadesde."' and kardex.fechacomprobante<='".$this->request->fechahasta."' and kardex.codmovimientotipo=20 and kardex.estado=".$this->request->estado." AND empleados.codcargo = 4 order by kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.nrocomprobante")->result_array();

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

				$lista = $this->db->query("select personas.documento,personas.razonsocial,personas.coddocumentotipo, kardex.codkardex,kardex.codkardex_ref, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.condicionpago, kardex.nrocomprobante, kardex.fechacomprobante,kardex.valorventa,kardex.igv, kardex.icbper,kardex.importe,kardex.estado,comprobantes.abreviatura as tipo,comprobantes.oficial,tipodocumento.oficial as tipodocumento,kardex.codmovimientotipo from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join public.documentotipos as tipodocumento on (personas.coddocumentotipo=tipodocumento.coddocumentotipo) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.fechacomprobante>='".$this->request->fechadesde."' and kardex.fechacomprobante<='".$this->request->fechahasta."' and ".$comprobantes." and (kardex.codmovimientotipo=20 or kardex.codmovimientotipo=8) ".$almacen." ".$sucursal." order by kardex.fechacomprobante, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.nrocomprobante")->result_array();
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