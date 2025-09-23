<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Ingresosalidas extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			$almacenes = $this->db->query("select *from almacen.almacenes where estado=1")->result_array();
			$lineas = $this->db->query("select *from almacen.lineas where estado=1 order by descripcion")->result_array();
			$this->load->view("reportes/ingresosalidas/index",compact("almacenes","lineas"));
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function generar_reporte(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$where = '';
			if ($this->request->codalmacen==0) {
				$almacen = $this->db->query("select *from almacen.almacenes where estado = 1")->result_array();
			}else{
				$almacen = $this->db->query("select *from almacen.almacenes where codalmacen=".$this->request->codalmacen." AND estado = 1")->result_array();
			}

			if($this->request->codmovimientotipo==0){
				if($this->request->tipo==0){
					$codmovimientotipo = ' AND kardex.codmovimientotipo<>2 AND kardex.codmovimientotipo<>20';
				}
				else if($this->request->tipo==1){
					$codmovimientotipo = " AND kardex.codmovimientotipo<>2 AND movimientotipos.tipo=1";
				}else{
					$codmovimientotipo = " AND kardex.codmovimientotipo<>20 AND movimientotipos.tipo=2";
				}
			}else{
				$codmovimientotipo = " AND kardex.codmovimientotipo=".$this->request->codmovimientotipo;
			}

			foreach ($almacen as $key => $value) {
				$lista = $this->db->query("select personas.documento,personas.razonsocial ,kardex.cliente,personas.coddocumentotipo, kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.estado, kardex.condicionpago,kardex.nrocomprobante,movimientotipos.descripcion as motivo,movimientotipos.tipo as tipomov, kardex.fechacomprobante,round(kardex.valorventa,2) as valorventa,round(kardex.igv,2) as igv, round(kardex.importe,2) as importe,kardex.estado,comprobantes.abreviatura as tipo from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) inner join almacen.movimientotipos as movimientotipos on(kardex.codmovimientotipo=movimientotipos.codmovimientotipo) where kardex.estado=1 ".$codmovimientotipo." AND kardex.codalmacen = ".$value["codalmacen"]." and kardex.fechacomprobante>='".$this->request->fechadesde."' and kardex.fechacomprobante<='".$this->request->fechahasta."' order by kardex.fechacomprobante, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.nrocomprobante")->result_array();

				$almacen[$key]["lista"] = $lista;

				$valortotal = 0; $igv = 0; $importe = 0;

				foreach ($almacen[$key]["lista"] as $k => $v) {
					$valortotal = $valortotal + (float)$v['valorventa'];
					$igv = $igv + (float)$v['igv'];
					$importe = $importe + (float)$v['importe'];
				}

				$almacen[$key]["valortotal"] = number_format($valortotal,2,".","");
				$almacen[$key]["igv"] = number_format($igv,2,".","");
				$almacen[$key]["importe"] = number_format($importe,2,".","");
			}

			

			//echo json_encode($data);exit;

			

			echo json_encode(["almacen"=>$almacen]);
		}
	}

	function pdf_reporte(){
		if (isset($_SESSION["phuyu_codusuario"])) {
			if ($_GET["datos"]) {
				$this->request = json_decode($_GET["datos"]);
				if($this->request->tipo==0){
					$cabecera = '(INGRESO Y SALIDA)';
				}
				else if($this->request->tipo==1){
					$cabecera = 'INGRESOS';
				}else{
					$cabecera = 'EGRESOS';
				}

				if ($this->request->codalmacen==0) {
					$almacen = $this->db->query("select *from almacen.almacenes where estado = 1")->result_array();
				}else{
					$almacen = $this->db->query("select *from almacen.almacenes where codalmacen=".$this->request->codalmacen." AND estado = 1")->result_array();
				}

				if($this->request->codmovimientotipo==0){
					if($this->request->tipo==0){
						$codmovimientotipo = ' AND kardex.codmovimientotipo<>2 AND kardex.codmovimientotipo<>20';
					}
					else if($this->request->tipo==1){
						$codmovimientotipo = " AND kardex.codmovimientotipo<>2 AND movimientotipos.tipo=1";
					}else{
						$codmovimientotipo = " AND kardex.codmovimientotipo<>20 AND movimientotipos.tipo=2";
					}
				}else{
					$codmovimientotipo = " AND kardex.codmovimientotipo=".$this->request->codmovimientotipo;
				}
				$color_letra = "";
				$this->load->library('Pdf2'); $pdf = new Pdf2(); $pdf->AddPage();
				$pdf->pdf_header("REPORTE DE ".$cabecera." DE ".$this->request->fechadesde.' AL '.$this->request->fechahasta,"");

				foreach ($almacen as $key => $value) {
					$lista = $this->db->query("select personas.documento,personas.razonsocial ,kardex.cliente,personas.coddocumentotipo, kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.estado, kardex.condicionpago,kardex.nrocomprobante,movimientotipos.descripcion as motivo,movimientotipos.tipo as tipomov, kardex.fechacomprobante,round(kardex.valorventa,2) as valorventa,round(kardex.igv,2) as igv, round(kardex.importe,2) as importe,kardex.estado,comprobantes.abreviatura as tipo from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) inner join almacen.movimientotipos as movimientotipos on(kardex.codmovimientotipo=movimientotipos.codmovimientotipo) where kardex.estado=1 ".$codmovimientotipo." AND kardex.codalmacen = ".$value["codalmacen"]." and kardex.fechacomprobante>='".$this->request->fechadesde."' and kardex.fechacomprobante<='".$this->request->fechahasta."' order by kardex.fechacomprobante, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.nrocomprobante")->result_array();

					$almacen[$key]["lista"] = $lista;

					$valortotal = 0; $igv = 0; $importe = 0;

					foreach ($almacen[$key]["lista"] as $k => $v) {
						$valortotal = $valortotal + (float)$v['valorventa'];
						$igv = $igv + (float)$v['igv'];
						$importe = $importe + (float)$v['importe'];
					}

					$almacen[$key]["valortotal"] = number_format($valortotal,2,".","");
					$almacen[$key]["igv"] = number_format($igv,2,".","");
					$almacen[$key]["importe"] = number_format($importe,2,".","");
				}

				$item = 0; $importe = 0; $igv = 0; $valorventa = 0;
				foreach ($almacen as $key => $value) { 

					$columnas = array($value["descripcion"]." | DIRECCION: ".$value["direccion"]);
					$w = array(190); $pdf->pdf_tabla_head($columnas,$w,7);

					$columnas = array("N°","FECHA","TIPO","MOVIMIENTO","DOCUMENTO","RAZON SOCIAL","SUBTOTAL","IGV","TOTAL");
					$w = array(10,15,15,29,20,58,15,10,18); $pdf->pdf_tabla_head($columnas,$w,7);
					$item = $item + 1; 
					$pdf->SetWidths(array(10,15,15,29,20,58,15,10,18));
	            	$pdf->SetLineHeight(5); $pdf->SetFont('Arial',$color_letra,6);

	            	foreach ($value["lista"] as $ke => $val) {
	            		$importe = $importe + $val["importe"];
						$igv = $igv + $val["igv"]; $valorventa = $valorventa + $val["valorventa"];
	            		$tipomov = ($val["tipomov"]==1) ? 'INGRESO' : 'SALIDA';
						$datos = array($val["codkardex"]);
						array_push($datos,$val["fechacomprobante"]);
						array_push($datos,$tipomov);
						array_push($datos,utf8_decode($val["motivo"]));
						array_push($datos,$val["seriecomprobante"]."-".$val["nrocomprobante"]);
						array_push($datos,utf8_decode($val["documento"].' - '.$val["razonsocial"]));

						array_push($datos,number_format($val["valorventa"],2));
						array_push($datos,number_format($val["igv"],2));
						array_push($datos,number_format($val["importe"],2));
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
					$pdf->Cell(array_sum($w),0,'','T'); $pdf->Ln();
						}

					$pdf->SetFont('Arial','B',7);
					$pdf->Cell(147,5,"TOTALES",1,0,'R');
				    $pdf->Cell(15,5,number_format($valorventa,2),1,"R");
				    $pdf->Cell(10,5,number_format($igv,2),1,"R");
				    $pdf->Cell(18,5,number_format($importe,2),1,"R");
				
					$pdf->SetTitle("phuyu Peru - Reporte Ingresos y Salidas"); $pdf->Output();
				}
			}
		}
	}

	function excel_reporte(){
		if (isset($_SESSION["phuyu_codusuario"])) {
			if ($_GET["datos"]) {
				$this->request = json_decode($_GET["datos"]);
				$tipos = $this->request->tipo;
				if($this->request->tipo==1){
					$cabecera = 'INGRESOS';
				}else{
					$cabecera = 'EGRESOS';
				}
				$cabecera_texto =  "REPORTE DE ".$cabecera." DEL ".$this->request->fechadesde.' AL '.$this->request->fechahasta;

				if ($this->request->codalmacen==0) {
					$almacen = $this->db->query("select *from almacen.almacenes where estado = 1")->result_array();
				}else{
					$almacen = $this->db->query("select *from almacen.almacenes where codalmacen=".$this->request->codalmacen." AND estado = 1")->result_array();
				}

				if($this->request->codmovimientotipo==0){
					if($this->request->tipo==0){
						$codmovimientotipo = ' AND kardex.codmovimientotipo<>2 AND kardex.codmovimientotipo<>20';
					}
					else if($this->request->tipo==1){
						$codmovimientotipo = " AND kardex.codmovimientotipo<>2 AND movimientotipos.tipo=1";
					}else{
						$codmovimientotipo = " AND kardex.codmovimientotipo<>20 AND movimientotipos.tipo=2";
					}
				}else{
					$codmovimientotipo = " AND kardex.codmovimientotipo=".$this->request->codmovimientotipo;
				}

				$lista = $this->db->query("select personas.documento,kardex.cliente,personas.coddocumentotipo, kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.estado, kardex.condicionpago,kardex.nrocomprobante, movimientotipos.descripcion AS movimiento, kardex.fechacomprobante,kardex.valorventa,kardex.igv, kardex.importe,kardex.estado,comprobantes.abreviatura as tipo from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) inner join almacen.movimientotipos as movimientotipos on(kardex.codmovimientotipo=movimientotipos.codmovimientotipo) where kardex.estado=1 ".$codmovimientotipo." ".$almacen." and kardex.codsucursal=".$this->request->codsucursal." and kardex.fechacomprobante>='".$this->request->fechadesde."' and kardex.fechacomprobante<='".$this->request->fechahasta."' order by kardex.fechacomprobante, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.nrocomprobante")->result_array();

				$fechadesde= $this->request->fechadesde; $fechahasta = $this->request->fechahasta;

				$empresa = $this->db->query("select *from public.personas where codpersona=1")->result_array();
				
				$this->load->view("reportes/ingresosalidas/ingresosalidasxls",compact("cabecera_texto","lista","tipos","empresa"));
			}
		}
	}

	public function phuyu_listaprestamos(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			if($this->request->tipo==1){
				$tipo = ' k.codmovimientotipo=25 ';
			}else{
				$tipo = ' k.codmovimientotipo=7 ';
			}

			if($this->request->estado==1){
				$estado = ' k.procesoprestamo=0 ';
			}else{
				$estado = ' k.procesoprestamo=1 ';
			}

			$lista = $this->db->query("select k.*,p.razonsocial as persona from kardex.kardex k JOIN public.personas as p on (k.codpersona=p.codpersona) where ".$tipo." AND ".$estado." AND fechakardex>='".$this->request->desde."' AND fechakardex<='".$this->request->hasta."' ORDER BY fechakardex ASC ")->result_array();

			foreach ($lista as $key => $value) {
				$detalle = $this->db->query("select kd.codproducto,kd.codunidad,round(kd.cantidad,2) as cantidad,round(kd.cantidaddevuelta,2) as cantidaddevuelta,p.codigo,COALESCE(round(kd.cantidad - kd.cantidaddevuelta,2),0) as cantidadxdevolver,round(kd.preciounitario,2) as precio,kd.preciosinigv,kd.preciorefunitario,kd.valorventa,round(kd.igv,2) as igv, round(kd.subtotal,2) as subtotal,kd.item, kd.codafectacionigv,p.descripcion as producto,u.descripcion as unidad, kd.recoger,kd.recogido,kd.descripcion,kd.codafectacionigv from kardex.kardexdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codkardex=".$value["codkardex"]." and kd.estado=1 order by kd.item")->result_array();

				$lista[$key]["detalle"] = $detalle;
			}

			echo json_encode($lista);
		}
	}

	public function pdf_reporte_prestamo(){
		if (isset($_SESSION["phuyu_codusuario"])) {
			if ($_GET["datos"]) {
				$this->request = json_decode($_GET["datos"]);

				if($this->request->tipo==1){
					$tipo = ' k.codmovimientotipo=25 ';
				}else{
					$tipo = ' k.codmovimientotipo=7 ';
				}

				if($this->request->estado==1){
					$estado = ' k.procesoprestamo=0 ';
				}else{
					$estado = ' k.procesoprestamo=1 ';
				}

				$this->load->library('Pdf2'); $pdf = new Pdf2(); $pdf->AddPage();

				$pdf->pdf_header("REPORTE DE PRESTAMOS (DE ".$this->request->desde." A ".$this->request->hasta.")","");

				$lista = $this->db->query("select k.*,p.razonsocial as persona from kardex.kardex k JOIN public.personas as p on (k.codpersona=p.codpersona) where ".$tipo." AND ".$estado." AND fechakardex>='".$this->request->desde."' AND fechakardex<='".$this->request->hasta."' ORDER BY fechakardex ASC ")->result_array();

				$pdf->Ln(8);

				$columnas = array("PERSONA","FECHA P.","COMPROB. REF","IMPORTE","OBSERVACION","ESTADO");
				$w = array(60,20,25,15,50,20); $pdf->pdf_tabla_head($columnas,$w,8);

	            $item = 0; $valorventa = 0; $descglobal = 0; $igv = 0; $importe = 0; $icbper = 0;
				foreach($lista as $value){
					$pdf->SetWidths(array(60,20,25,15,50,20));
	            	$pdf->SetLineHeight(5); $pdf->SetFont('Arial','',7);

					$item = $item + 1;

					$datos = array();
					array_push($datos,$value["persona"]);
					array_push($datos,$value["fechakardex"]);
					array_push($datos,$value["seriecomprobante_ref"]."-".$value["nrocomprobante_ref"]);
					

					array_push($datos,number_format($value["importe"],2));
					array_push($datos,utf8_decode($value["descripcion"]));
					if ($value["procesoprestamo"]==1) {
						array_push($datos,"DEVUELTO");
				    }else{
				    	array_push($datos,"PENDIENTE");
				    }
	                $pdf->Row($datos);

	                if($this->request->formato==2){
		                $detalle = $this->db->query("select kd.codproducto,kd.codunidad,round(kd.cantidad,2) as cantidad,round(kd.cantidaddevuelta,2) as cantidaddevuelta,p.codigo,COALESCE(round(kd.cantidad - kd.cantidaddevuelta,2),0) as cantidadxdevolver,round(kd.preciounitario,2) as precio,kd.preciosinigv,kd.preciorefunitario,kd.valorventa,round(kd.igv,2) as igv, round(kd.subtotal,2) as subtotal,kd.item, kd.codafectacionigv,p.descripcion as producto,u.descripcion as unidad, kd.recoger,kd.recogido,kd.descripcion,kd.codafectacionigv from kardex.kardexdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codkardex=".$value["codkardex"]." and kd.estado=1 order by kd.item")->result_array();

		             	$pdf->SetLineHeight(5); $pdf->SetFont('Arial','B',7);
		                $columnas = array("#","PRODUCTO","ID","CODIGO","UNIDAD MED.","CANTIDAD PRESTADA","CANTIDAD DEVUELTA"); $wd = array(5,75,8,17,25,30,30);
		                for($i=0;$i<count($columnas);$i++){
							$pdf->SetFillColor(230,230,230);
				            $pdf->Cell($wd[$i],5,utf8_decode($columnas[$i]),1,0,'L',True);
				        } $pdf->Ln();

				        $pdf->SetWidths(array(5,75,8,17,25,30,30)); $pdf->SetLineHeight(5); $pdf->SetFont('Arial','',7);
				        $i = 0;
				        foreach ($detalle as $v) {
				        	$i++;
				        	$datos = array($i);
							array_push($datos,utf8_decode($v["producto"]));
							array_push($datos,utf8_decode($v["codproducto"]));
							array_push($datos,utf8_decode($v["codigo"]));

							array_push($datos,utf8_decode($v["unidad"]));
							array_push($datos,number_format($v["cantidad"],2));
							array_push($datos,number_format($v["cantidaddevuelta"],2));
			                $pdf->Row($datos);
				        }
				    }
				}
				$pdf->Cell(array_sum($w),0,'','T'); $pdf->Ln();

				$pdf->SetTitle("phuyu Peru - Reporte de Prestamos"); $pdf->Output();
			}
		}
	}

	public function excel_reporte_prestamo(){
		if (isset($_SESSION["phuyu_codusuario"])) {
			if ($_GET["datos"]) {
				$this->request = json_decode($_GET["datos"]);

				if($this->request->tipo==1){
					$tipo = ' k.codmovimientotipo=25 ';
				}else{
					$tipo = ' k.codmovimientotipo=7 ';
				}

				if($this->request->estado==1){
					$estado = ' k.procesoprestamo=0 ';
				}else{
					$estado = ' k.procesoprestamo=1 ';
				}

				$titulo = "REPORTE DE PRESTAMOS (DE ".$this->request->desde." A ".$this->request->hasta.")";

				$lista = $this->db->query("select k.*,p.razonsocial as persona from kardex.kardex k JOIN public.personas as p on (k.codpersona=p.codpersona) where ".$tipo." AND ".$estado." AND fechakardex>='".$this->request->desde."' AND fechakardex<='".$this->request->hasta."' ORDER BY fechakardex ASC ")->result_array();

				$formato = $this->request->formato;

				$this->load->view("reportes/ingresosalidas/prestamosxls",compact("titulo","lista","formato"));
			}
		}
	}
}