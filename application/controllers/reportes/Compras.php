<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Compras extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			$sucursales = $this->db->query("select *from public.sucursales where estado=1")->result_array();
			$this->load->view("reportes/compras/index",compact("sucursales"));
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
					$total = $this->db->query("select round(COALESCE(sum(importe),0),2) as importe from caja.movimientos where codkardex<>0 and fechamovimiento>='".$this->request->fechadesde."' and fechamovimiento<='".$this->request->fechahasta."' and tipomovimiento=2 and estado=".(int)$this->request->estado)->result_array();
					$categorias[] = $value["descripcion"]; $totales[] = (double)$total[0]["importe"];
				}
			}else{
				if ($this->request->codcaja==0) {
					$cajas = $this->db->query("select *from caja.cajas where codsucursal=".$this->request->codsucursal." and estado=1")->result_array();
					foreach ($cajas as $key => $value) {
						$total = $this->db->query("select round(COALESCE(sum(importe),0),2) as importe from caja.movimientos where codkardex<>0 and fechamovimiento>='".$this->request->fechadesde."' and fechamovimiento<='".$this->request->fechahasta."' and tipomovimiento=2 and codcaja=".$value["codcaja"]." and estado=".(int)$this->request->estado)->result_array();
						$categorias[] = $value["descripcion"]; $totales[] = (double)$total[0]["importe"];
					}
				}else{
					$desde = explode("-", $this->request->fechadesde); $hasta = explode("-", $this->request->fechahasta);

					if ( ($hasta[0] - $desde[0])!=0 ) {
						$year = $hasta[0] - $desde[0] + 1; $y_inicio = $desde[0]; $f_inicio = $this->request->fechadesde;
						for ($i=0; $i < $year ; $i++) { 
							$total = $this->db->query("select round(COALESCE(sum(importe),0),2) as importe from caja.movimientos where codkardex<>0 and TO_CHAR(fechamovimiento,'YYYY')='".$y_inicio."' and tipomovimiento=2 and codcaja=".$this->request->codcaja." and estado=".(int)$this->request->estado)->result_array();

							$categorias[$i] = "Año-".$y_inicio; $totales[$i] = (double)$total[0]["importe"];
							$y_inicio = $y_inicio + 1; $f_inicio = date("Y-m-d",strtotime($f_inicio."+ 1 year")); 
						}
					}else{
						if ( ($hasta[1] - $desde[1]!=0 ) ) {
							$meses = $hasta[1] - $desde[1] + 1; $m_inicio = $desde[1]; $f_inicio = $this->request->fechadesde;
							for ($i=0; $i < $meses ; $i++) { 
								$total = $this->db->query("select round(COALESCE(sum(importe),0),2) as importe from caja.movimientos where codkardex<>0 and TO_CHAR(fechamovimiento,'YYYY-MM')='".$desde[0]."-".$m_inicio."' and tipomovimiento=2 and codcaja=".$this->request->codcaja." and estado=".(int)$this->request->estado)->result_array();

								$categorias[$i] = "Mes-".$m_inicio; $totales[$i] = (double)$total[0]["importe"];
								$m_inicio = $m_inicio + 1; $f_inicio = date("Y-m-d",strtotime($f_inicio."+ 1 month")); 
							}
						}else{
							$dias = $hasta[2] - $desde[2] + 1; $d_inicio = $desde[2]; $f_inicio = $this->request->fechadesde;
							for ($i=0; $i < $dias ; $i++) { 
								$total = $this->db->query("select round(COALESCE(sum(importe),0),2) as importe from caja.movimientos where codkardex<>0 and fechamovimiento='".$f_inicio."' and tipomovimiento=2 and codcaja=".$this->request->codcaja." and estado=".(int)$this->request->estado)->result_array();

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

	// REPORTES PDF DE COMPRAS //

	function pdf_cabecera($titulo, $subtitulo){
		$logo = base_url().'public/img/'.$_SESSION['phuyu_logo'];
		if(!file_exists($logo)){
			$logo = '';
		}
		$html = '<table width="100%" align="center">';
			$html .= '<tr>';
				$html .= '<th style="width:15%">';
					$html .='<img src="'.$logo.'" height="50">';
				$html .= '</th>';
				$html .= '<th style="width:55%">';
					$html .= '<h3>'.$_SESSION["phuyu_empresa"].'</h3>';
					$html .= '<h4>'.$titulo.'</h4>';
				$html .= '</th>';
				$html .= '<th style="width:30%">';
					$html .= '<h3>'.$_SESSION["phuyu_sucursal"].'</h3>';
					$html .= '<h4>COMPRAS REALIZADAS</h4>';
				$html .= '</th>';
			$html .= '</tr>';
		$html .= '</table> <hr>';

		$html .= '<h4 align="center">'.$subtitulo.'</h4> <hr> <h6></h6>';
		return $html;
	}

	function pdf_imprimir($html,$titulo,$descarga){
		$this->load->library('Pdf');
        $pdf = new Pdf('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('WEB phuyu');
        $pdf->SetTitle($titulo);
        $pdf->SetSubject('WEB phuyu');

        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->setPrintHeader(false);

        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        $pdf->setFontSubsetting(true);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->AddPage("A");
        $pdf->writeHTML($html, true, 0, true, 0);

        $nombre_archivo = utf8_decode($descarga);
        $pdf->Output($nombre_archivo, 'I');
	}


	function consulta_reporte_compras(){
		if (isset($_SESSION["phuyu_codusuario"])) {
			$this->request = json_decode(file_get_contents('php://input'));
			$titulo = "";

			$sucursales = '';
			$almacen = '';
			if ($this->request->codsucursal!=0) {
				$sucursales = ' and kardex.codsucursal='.$this->request->codsucursal;
			}

			$valorventatotal = 0; $igvtotal=0; $icbpertotal=0;$totalgeneral=0;

			$lista = $this->db->query("select personas.documento,kardex.cliente,kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante,kardex.condicionpago, kardex.nrocomprobante, kardex.fechacomprobante,round(kardex.valorventa,2) AS valorventa,round(kardex.igv,2) AS IGV, kardex.descglobal, round(kardex.icbper,2) AS icbper,round(kardex.importe,2) AS importe,kardex.condicionpago, comprobantes.descripcion as tipo from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.fechacomprobante>='".$this->request->fechadesde."' and kardex.fechacomprobante<='".$this->request->fechahasta."' and kardex.codmovimientotipo=2 ".$sucursales." and kardex.estado=".(int)$this->request->estado." order by kardex.codkardex")->result_array();

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

	function pdf_compras(){
		if (isset($_SESSION["phuyu_codusuario"])) {
			if ($_GET["datos"]) {
				$this->request = json_decode($_GET["datos"]);

				$estilo = "border-top:1px solid #D5D8DC; border-left:1px solid #D5D8DC; border-right:1px solid #D5D8DC;";
				$html = $this->pdf_cabecera("REPORTE DE COMPRAS","REPORTE GENERAL DE COMPRAS (".$this->request->fechadesde." HASTA ".$this->request->fechahasta.")");

				if ($this->request->codsucursal==0) {
					$sucursales = $this->db->query("select *from public.sucursales where estado=1")->result_array();
				}else{
					$sucursales = $this->db->query("select *from public.sucursales where codsucursal=".$this->request->codsucursal)->result_array();
				}

				foreach ($sucursales as $key => $value) {
					$html .= '<h4 align="center">SUCURSAL: '.$value["descripcion"].'</h4>';

					$lista = $this->db->query("select personas.documento,personas.razonsocial,personas.nombrecomercial,kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante,kardex.condicionpago,kardex.nrocomprobante,kardex.fechakardex,round(kardex.importe,2) as importe,round(kardex.valorventa,2) as valorventa ,round(kardex.igv,2) as igv ,kardex.estado,comprobantes.descripcion as tipo from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codmovimientotipo=2 and kardex.codsucursal=".$value["codsucursal"]." and kardex.fechacomprobante>='".$this->request->fechadesde."' and kardex.fechacomprobante<='".$this->request->fechahasta."' and kardex.estado=".(int)$this->request->estado)->result_array();

					$html .= '<table cellpadding="4" width="100%" style="border:1px solid #D5D8DC;font-size:8px;">';
						$html .= '<tr>';
							$html .= '<th style="'.$estilo.' width:3%;"> <b>#</b> </th>';
							$html .= '<th style="'.$estilo.' width:10%;"> <b>DOCUMENTO</b> </th>';
							$html .= '<th style="'.$estilo.' width:25%;"> <b>RAZON SOCIAL</b> </th>';
							$html .= '<th style="'.$estilo.' width:10%;"> <b>FECHA</b> </th>';
							$html .= '<th style="'.$estilo.' width:18%;"> <b>TIPO</b> </th>';
							$html .= '<th style="'.$estilo.' width:12%;"> <b>COMPROBANTE</b> </th>';
							$html .= '<th style="'.$estilo.' width:8%;"> <b>V. VENTA</b> </th>';
							$html .= '<th style="'.$estilo.' width:6%;"> <b>IGV</b> </th>';
							$html .= '<th style="'.$estilo.' width:8%;"> <b>IMPORTE</b> </th>';
						$html .= '</tr>';
						$importe = 0; $item = 1;
						foreach ($lista as $key => $value) {
							$html .= '<tr>';
								$html .= '<th style="'.$estilo.'"> '.$item.'</th>';
								$html .= '<th style="'.$estilo.'"> '.$value["documento"].'</th>';
								$html .= '<th style="'.$estilo.'"> '.$value["razonsocial"].' </th>';
								$html .= '<th style="'.$estilo.'"> '.$value["fechakardex"].' </th>';
								$html .= '<th style="'.$estilo.'"> '.$value["tipo"].' </th>';
								$html .= '<th style="'.$estilo.'"> '.$value["seriecomprobante"].'-'.$value["nrocomprobante"].' </th>';
								$html .= '<th style="'.$estilo.'"> '.$value["valorventa"].' </th>';
								$html .= '<th style="'.$estilo.'"> '.$value["igv"].' </th>';
								$html .= '<th style="'.$estilo.'"> '.$value["importe"].' </th>';
							$html .= '</tr>';
							$importe = $importe + $value["importe"];
							$item++;
						}
						$html .= '<tr><th colspan="8" style="'.$estilo.';text-align:right;font-weight:700">TOTAL</th><th style="'.$estilo.';text-align:center">'.$importe.'</th></tr>';
					$html .= '</table>';
				}

				$this->pdf_imprimir($html,"REPORTE DE COMPRAS","compras.pdf");
			}
		}
	}

	function comprasproveedorpdf(){
		if (isset($_SESSION["phuyu_codusuario"])) {
			if ($_GET["datos"]) {
				$this->request = json_decode($_GET["datos"]);

				$estilo = "border-top:1px solid #D5D8DC; border-left:1px solid #D5D8DC; border-right:1px solid #D5D8DC;";
				$html = $this->pdf_cabecera("REPORTE DE COMPRAS POR PROVEEDOR","REPORTE GENERAL DE COMPRAS (".$this->request->fechadesde." HASTA ".$this->request->fechahasta.")");

				if ($this->request->codsucursal==0) {
					$sucursales = '';
				}else{
					$sucursales = ' AND kardex.codsucursal='.$this->request->codsucursal;
				}
				if ($this->request->codpersona==0) {
					$personas = $this->db->query("select personas.* from public.socios as socios inner join public.personas as personas on (socios.codpersona=personas.codpersona) where (socios.codsociotipo=2 or socios.codsociotipo=3) and socios.estado=1")->result_array();
				}else{
					$personas = $this->db->query("select *from public.personas where codpersona=".$this->request->codpersona)->result_array();
				}

				foreach ($personas as $key => $value) {
					$html .= '<h4 align="center">PROVEEDOR: '.$value["razonsocial"].'</h4>';

					$lista = $this->db->query("select personas.documento,personas.razonsocial,personas.nombrecomercial,kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante,kardex.condicionpago,kardex.nrocomprobante,kardex.fechakardex,round(kardex.importe,2) as importe,kardex.estado,comprobantes.descripcion as tipo from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codmovimientotipo=2 and kardex.codpersona=".$value["codpersona"]." ".$sucursales." and kardex.fechakardex>='".$this->request->fechadesde."' and kardex.fechakardex<='".$this->request->fechahasta."' and kardex.estado=".(int)$this->request->estado)->result_array();

					$html .= '<table cellpadding="4" width="100%" style="border:1px solid #D5D8DC;font-size:9px;">';
						$html .= '<tr>';
							$html .= '<th style="'.$estilo.' width:12%;"> <b>DOCUMENTO</b> </th>';
							$html .= '<th style="'.$estilo.' width:30%;"> <b>RAZON SOCIAL</b> </th>';
							$html .= '<th style="'.$estilo.' width:15%;"> <b>FECHA</b> </th>';
							$html .= '<th style="'.$estilo.' width:18%;"> <b>TIPO</b> </th>';
							$html .= '<th style="'.$estilo.' width:15%;"> <b>COMPROBANTE</b> </th>';
							$html .= '<th style="'.$estilo.' width:10%;"> <b>IMPORTE</b> </th>';
						$html .= '</tr>';
						$importe = 0;
						foreach ($lista as $key => $value) {
							$html .= '<tr>';
								$html .= '<th style="'.$estilo.'"> '.$value["documento"].'</th>';
								$html .= '<th style="'.$estilo.'"> '.$value["razonsocial"].' </th>';
								$html .= '<th style="'.$estilo.'"> '.$value["fechakardex"].' </th>';
								$html .= '<th style="'.$estilo.'"> '.$value["tipo"].' </th>';
								$html .= '<th style="'.$estilo.'"> '.$value["seriecomprobante"].'-'.$value["nrocomprobante"].' </th>';
								$html .= '<th style="'.$estilo.'"> '.$value["importe"].' </th>';
							$html .= '</tr>';
							$importe = $importe + $value["importe"];
						}
						$html .= '<tr><th colspan="5" style="'.$estilo.';text-align:right;font-weight:700">TOTAL</th><th style="'.$estilo.';text-align:center">'.$importe.'</th></tr>';
					$html .= '</table>';
				}

				$this->pdf_imprimir($html,"REPORTE DE COMPRAS POR PROVEEDOR","compras.pdf");
			}
		}
	}

	function comprasproveedorpdfdet(){
		if (isset($_SESSION["phuyu_codusuario"])) {
			if ($_GET["datos"]) {
				$this->request = json_decode($_GET["datos"]); $titulo = "";

				$item = 0; 

				if ($this->request->codsucursal==0) {
					$sucursales = '';
				}else{
					$sucursales = ' AND kardex.codsucursal='.$this->request->codsucursal;
				}
				if ($this->request->codpersona==0) {
					$personas = $this->db->query("select personas.* from public.socios as socios inner join public.personas as personas on (socios.codpersona=personas.codpersona) where (socios.codsociotipo=2 or socios.codsociotipo=3) and socios.estado=1")->result_array();
				}else{
					$personas = $this->db->query("select *from public.personas where codpersona=".$this->request->codpersona)->result_array();
				}

				$this->load->library('Pdf2'); $pdf = new Pdf2(); $pdf->AddPage();

				$pdf->pdf_header("REPORTE DE COMPRAS POR PROVEEDOR ".$titulo. "(DE ".$this->request->fechadesde." A ".$this->request->fechahasta.")","");


                foreach ($personas as $key => $value) {
					$lista = $this->db->query("select personas.documento,personas.razonsocial,kardex.cliente,kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante,kardex.condicionpago, kardex.nrocomprobante, kardex.fechacomprobante,kardex.valorventa,kardex.igv, kardex.descglobal, kardex.icbper, kardex.importe,kardex.condicionpago, comprobantes.descripcion as tipo from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.fechacomprobante>='".$this->request->fechadesde."' and kardex.fechacomprobante<='".$this->request->fechahasta."' and kardex.codmovimientotipo=2 AND kardex.codpersona=".$value["codpersona"]." ".$sucursales." and kardex.estado=".$this->request->estado." order by kardex.fechacomprobante, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.nrocomprobante")->result_array();

					$pdf->Ln(8); $pdf->SetFont('Arial', 'B', 10);
			        $pdf->Cell(200, 5, utf8_decode("PROVEEDOR: ".$value["razonsocial"]),0,0,'C');
                    $pdf->Ln(8);

					$columnas = array("DOCUMENTO","RAZON SOCIAL","TIPO","COMPROB.","FECHA","IMPORTE");
					$w = array(20,88,25,20,18,20); $pdf->pdf_tabla_head($columnas,$w,8);

		            $item = 0; $valorventa = 0; $descglobal = 0; $igv = 0; $importe = 0; $icbper = 0;
					foreach($lista as $value){
						$pdf->SetWidths(array(20,88,25,20,18,20));
		            	$pdf->SetLineHeight(5); $pdf->SetFont('Arial','B',7);

						$item = $item + 1; $valorventa = $valorventa + $value["valorventa"]; 
						$descglobal = $descglobal + $value["descglobal"];
						$igv = $igv + $value["igv"]; $icbper = $icbper + $value["igv"];
						$importe = $importe + $value["importe"];

						$datos = array($value["documento"]);
						array_push($datos,$value["razonsocial"]);
						array_push($datos,$value["tipo"]);
						array_push($datos,$value["seriecomprobante"]."-".$value["nrocomprobante"]);
						array_push($datos,utf8_decode($value["fechacomprobante"]));

						array_push($datos,number_format($value["importe"],2));
		                $pdf->Row($datos);

		                $detalle = $this->db->query("select kd.*,p.descripcion as producto,u.descripcion as unidad,p.codigo from kardex.kardexdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codkardex=".$value["codkardex"]." and kd.estado=1 order by kd.item")->result_array();

		             	$pdf->SetLineHeight(5); $pdf->SetFont('Arial','B',7);
		                $columnas = array("CANT","DESCRIPCION DETALLE VENTA","UNI.MED","P.UNITARIO","IGV","SUBTOTAL"); $wd = array(10,98,25,20,18,20);
		                for($i=0;$i<count($columnas);$i++){
							$pdf->SetFillColor(230,230,230);
				            $pdf->Cell($wd[$i],5,utf8_decode($columnas[$i]),1,0,'L',True);
				        } $pdf->Ln();

				        $pdf->SetWidths(array(10,98,25,20,18,20)); $pdf->SetLineHeight(5); $pdf->SetFont('Arial','',7);
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
					$pdf->Cell(171,5,"TOTALES",1,0,'R');
				    $pdf->Cell(20,5,number_format($importe,2),1,"R");
                }
				$pdf->SetTitle("REPORTE COMPRAS DETALLADO X PROVEEDOR"); $pdf->Output();
			}
		}
	}

	function pdf_reporte_compras_det(){
		if (isset($_SESSION["phuyu_codusuario"])) {
			if ($_GET["datos"]) {
				$this->request = json_decode($_GET["datos"]); $titulo = "";

				$item = 0; 

				if ($this->request->codsucursal==0) {
					$sucursales = $this->db->query("select *from public.sucursales where estado=1")->result_array();
				}else{
					$sucursales = $this->db->query("select *from public.sucursales where codsucursal=".$this->request->codsucursal)->result_array();
				}

				$this->load->library('Pdf2'); $pdf = new Pdf2(); $pdf->AddPage();

				$pdf->pdf_header("REPORTE DE COMPRAS ".$titulo. "(DE ".$this->request->fechadesde." A ".$this->request->fechahasta.")","");


                foreach ($sucursales as $key => $value) {
					$lista = $this->db->query("select personas.documento,personas.razonsocial,kardex.cliente,kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante,kardex.condicionpago, kardex.nrocomprobante, kardex.fechacomprobante,kardex.valorventa,kardex.igv, kardex.descglobal, kardex.icbper, kardex.importe,kardex.condicionpago, comprobantes.descripcion as tipo from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.fechacomprobante>='".$this->request->fechadesde."' and kardex.fechacomprobante<='".$this->request->fechahasta."' and kardex.codmovimientotipo=2 AND kardex.codsucursal=".$value["codsucursal"]." and kardex.estado=".$this->request->estado." order by kardex.fechacomprobante, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.nrocomprobante")->result_array();

					$pdf->Ln(8); $pdf->SetFont('Arial', 'B', 10);
			        $pdf->Cell(200, 5, utf8_decode("SUCURSAL: ".$value["descripcion"]),0,0,'C');
                    $pdf->Ln(8);

					$columnas = array("DOCUMENTO","RAZON SOCIAL","TIPO","COMPROB.","FECHA","IMPORTE");
					$w = array(20,88,25,20,18,20); $pdf->pdf_tabla_head($columnas,$w,8);

		            $item = 0; $valorventa = 0; $descglobal = 0; $igv = 0; $importe = 0; $icbper = 0;
					foreach($lista as $value){
						$pdf->SetWidths(array(20,88,25,20,18,20));
		            	$pdf->SetLineHeight(5); $pdf->SetFont('Arial','B',7);

						$item = $item + 1; $valorventa = $valorventa + $value["valorventa"]; 
						$descglobal = $descglobal + $value["descglobal"];
						$igv = $igv + $value["igv"]; $icbper = $icbper + $value["igv"];
						$importe = $importe + $value["importe"];

						$datos = array($value["documento"]);
						array_push($datos,$value["razonsocial"]);
						array_push($datos,$value["tipo"]);
						array_push($datos,$value["seriecomprobante"]."-".$value["nrocomprobante"]);
						array_push($datos,utf8_decode($value["fechacomprobante"]));

						array_push($datos,number_format($value["importe"],2));
		                $pdf->Row($datos);

		                $detalle = $this->db->query("select kd.*,p.descripcion as producto,u.descripcion as unidad,p.codigo from kardex.kardexdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codkardex=".$value["codkardex"]." and kd.estado=1 order by kd.item")->result_array();

		             	$pdf->SetLineHeight(5); $pdf->SetFont('Arial','B',7);
		                $columnas = array("CANT","DESCRIPCION DETALLE VENTA","UNI.MED","P.UNITARIO","IGV","SUBTOTAL"); $wd = array(10,98,25,20,18,20);
		                for($i=0;$i<count($columnas);$i++){
							$pdf->SetFillColor(230,230,230);
				            $pdf->Cell($wd[$i],5,utf8_decode($columnas[$i]),1,0,'L',True);
				        } $pdf->Ln();

				        $pdf->SetWidths(array(10,98,25,20,18,20)); $pdf->SetLineHeight(5); $pdf->SetFont('Arial','',7);
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
					$pdf->Cell(171,5,"TOTALES",1,0,'R');
				    $pdf->Cell(20,5,number_format($importe,2),1,"R");
                }
				$pdf->SetTitle("phuyu Peru - Reporte de Compras Detallado"); $pdf->Output();
			}
		}
	}

	function excel_compras(){
		if (isset($_SESSION["phuyu_codusuario"])) {
			if ($_GET["datos"]) { 
				$this->request = json_decode($_GET["datos"]);
				$this->tipos = (isset($_GET["tipo"]) ? "resumen" : null);
				$tipos = $this->tipos;
				
				if ($this->request->codsucursal==0) {
					$sucursales = $this->db->query("select *from public.sucursales where estado=1")->result_array();
				}else{
					$sucursales = $this->db->query("select *from public.sucursales where codsucursal=".$this->request->codsucursal)->result_array();
				}

				foreach ($sucursales as $key => $value) {
					$lista = $this->db->query("select personas.documento,personas.razonsocial,kardex.cliente,kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante,kardex.condicionpago, kardex.nrocomprobante, kardex.fechacomprobante,kardex.valorventa,kardex.igv, kardex.descglobal, kardex.icbper, kardex.importe,kardex.condicionpago, comprobantes.descripcion as tipo,kardex.estado from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.fechacomprobante>='".$this->request->fechadesde."' and kardex.fechacomprobante<='".$this->request->fechahasta."' and kardex.codmovimientotipo=2 AND kardex.codsucursal=".$value["codsucursal"]." and kardex.estado=".$this->request->estado." order by kardex.fechacomprobante, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.nrocomprobante")->result_array();

					$sucursales[$key]["lista"] = $lista;
				}
				$fechadesde= $this->request->fechadesde; $fechahasta = $this->request->fechahasta;
				
				$this->load->view("reportes/compras/comprasxls",compact("sucursales","fechadesde","fechahasta","tipos"));
			}
		}
	}

	function comprasproveedorexcel(){
		if (isset($_SESSION["phuyu_codusuario"])) {
			if ($_GET["datos"]) { 
				$this->request = json_decode($_GET["datos"]);
				$this->tipos = (isset($_GET["tipo"]) ? "resumen" : null);
				$tipos = $this->tipos;
				
				if ($this->request->codsucursal==0) {
					$sucursales = '';
				}else{
					$sucursales = " AND kardex.codsucursal=".$this->request->codsucursal;
				}

				if ($this->request->codpersona==0) {
					$personas = $this->db->query("select personas.* from public.socios as socios inner join public.personas as personas on (socios.codpersona=personas.codpersona) where (socios.codsociotipo=2 or socios.codsociotipo=3) and socios.estado=1")->result_array();
				}else{
					$personas = $this->db->query("select *from public.personas where codpersona=".$this->request->codpersona)->result_array();
				}

				foreach ($personas as $key => $value) {
					$lista = $this->db->query("select personas.documento,personas.razonsocial,kardex.cliente,kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante,kardex.condicionpago, kardex.nrocomprobante, kardex.fechacomprobante,kardex.valorventa,kardex.igv, kardex.descglobal, kardex.icbper, kardex.importe,kardex.condicionpago, comprobantes.descripcion as tipo,kardex.estado from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.fechacomprobante>='".$this->request->fechadesde."' and kardex.fechacomprobante<='".$this->request->fechahasta."' and kardex.codmovimientotipo=2 AND kardex.codpersona=".$value["codpersona"]." ".$sucursales." and kardex.estado=".$this->request->estado." order by kardex.fechacomprobante, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.nrocomprobante")->result_array();

					$personas[$key]["lista"] = $lista;
				}
				$fechadesde= $this->request->fechadesde; $fechahasta = $this->request->fechahasta;
				
				$this->load->view("reportes/compras/comprasproveedorxls",compact("personas","fechadesde","fechahasta","tipos"));
			}
		}
	}
}