<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Prestamos extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			$almacenes = $this->db->query("select *from almacen.almacenes where estado=1")->result_array();
			$lineas = $this->db->query("select *from almacen.lineas where estado=1 order by descripcion")->result_array();
			$this->load->view("reportes/prestamos/index",compact("almacenes","lineas"));
		}else{
			$this->load->view("phuyu/404");
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
			$estado = '';
			if($this->request->estado!=0){
				if($this->request->estado==1){
					$estado = ' HAVING sum(kd.cantidaddevuelta) < sum(kd.cantidad) ';
				}else{
					$estado = ' HAVING sum(kd.cantidaddevuelta) = sum(kd.cantidad) ';
				}
			}

			$lista = $this->db->query("SELECT k.codkardex,k.codsucursal,k.codalmacen,(sum(kd.cantidad) - sum(kd.cantidaddevuelta)) As cantidaddevuelta,p.razonsocial as cliente,k.direccion,k.fechakardex,k.hora,k.seriecomprobante,k.nrocomprobante,p.documento,round(k.valorventa,2) as valorventa,round(k.importe,2) as importe,k.codempleado,k.codpersona,k.condicionpago,k.igv,k.codcomprobantetipo,k.codmovimientotipo FROM kardex.kardex k JOIN kardex.kardexdetalle kd ON k.codkardex = kd.codkardex JOIN public.personas as p on (k.codpersona=p.codpersona) WHERE ".$tipo." and k.codsucursal=".$_SESSION["phuyu_codsucursal"]." and k.estado=1 AND fechakardex>='".$this->request->desde."' AND fechakardex<='".$this->request->hasta."' GROUP BY k.codkardex, k.codsucursal,k.codpersona, k.valorventa, k.codalmacen,p.razonsocial,k.fechakardex,k.hora,k.seriecomprobante,k.nrocomprobante,p.documento ".$estado." order by k.fechakardex asc ")->result_array();

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

				$estado = '';
				if($this->request->estado!=0){
					if($this->request->estado==1){
						$estado = ' HAVING sum(kd.cantidaddevuelta) < sum(kd.cantidad) ';
					}else{
						$estado = ' HAVING sum(kd.cantidaddevuelta) = sum(kd.cantidad) ';
					}
				}

				$this->load->library('Pdf2'); $pdf = new Pdf2(); $pdf->AddPage();

				$pdf->pdf_header("REPORTE DE PRESTAMOS (DE ".$this->request->desde." A ".$this->request->hasta.")","");

				$lista = $this->db->query("SELECT k.codkardex,k.codsucursal,k.codalmacen,(sum(kd.cantidad) - sum(kd.cantidaddevuelta)) As cantidaddevuelta,p.razonsocial as cliente,k.direccion,k.fechakardex,k.hora,k.seriecomprobante,k.nrocomprobante,p.documento,round(k.valorventa,2) as valorventa,round(k.importe,2) as importe,k.codempleado,k.codpersona,k.condicionpago,k.igv,k.codcomprobantetipo,k.codmovimientotipo,k.descripcion FROM kardex.kardex k JOIN kardex.kardexdetalle kd ON k.codkardex = kd.codkardex JOIN public.personas as p on (k.codpersona=p.codpersona) WHERE ".$tipo." and k.codsucursal=".$_SESSION["phuyu_codsucursal"]." and k.estado=1 AND fechakardex>='".$this->request->desde."' AND fechakardex<='".$this->request->hasta."' GROUP BY k.codkardex, k.codsucursal,k.codpersona, k.valorventa, k.codalmacen,p.razonsocial,k.fechakardex,k.hora,k.seriecomprobante,k.nrocomprobante,p.documento ".$estado." order by k.fechakardex asc ")->result_array();

				$pdf->Ln(8);

				$columnas = array("PERSONA","FECHA P.","COMPROB. REF","IMPORTE","OBSERVACION","ESTADO");
				$w = array(60,20,25,15,50,20); $pdf->pdf_tabla_head($columnas,$w,8);

	            $item = 0; $valorventa = 0; $descglobal = 0; $igv = 0; $importe = 0; $icbper = 0;
				foreach($lista as $value){
					$pdf->SetWidths(array(60,20,25,15,50,20));
	            	$pdf->SetLineHeight(5); $pdf->SetFont('Arial','',7);

					$item = $item + 1;

					$datos = array();
					array_push($datos,$value["cliente"]);
					array_push($datos,$value["fechakardex"]);
					array_push($datos,$value["seriecomprobante"]."-".$value["nrocomprobante"]);
					

					array_push($datos,number_format($value["importe"],2));
					array_push($datos,utf8_decode($value["descripcion"]));
					if ($value["cantidaddevuelta"]!=0) {
						array_push($datos,"PENDIENTE");
				    }else{
				    	array_push($datos,"DEVUELTO");
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

				$estado = '';
				if($this->request->estado!=0){
					if($this->request->estado==1){
						$estado = ' HAVING sum(kd.cantidaddevuelta) < sum(kd.cantidad) ';
					}else{
						$estado = ' HAVING sum(kd.cantidaddevuelta) = sum(kd.cantidad) ';
					}
				}

				$titulo = "REPORTE DE PRESTAMOS (DE ".$this->request->desde." A ".$this->request->hasta.")";

				$lista = $this->db->query("SELECT k.codkardex,k.codsucursal,k.codalmacen,(sum(kd.cantidad) - sum(kd.cantidaddevuelta)) As cantidaddevuelta,p.razonsocial as cliente,k.direccion,k.fechakardex,k.hora,k.seriecomprobante,k.nrocomprobante,p.documento,round(k.valorventa,2) as valorventa,round(k.importe,2) as importe,k.codempleado,k.codpersona,k.condicionpago,k.igv,k.codcomprobantetipo,k.codmovimientotipo,k.descripcion FROM kardex.kardex k JOIN kardex.kardexdetalle kd ON k.codkardex = kd.codkardex JOIN public.personas as p on (k.codpersona=p.codpersona) WHERE ".$tipo." and k.codsucursal=".$_SESSION["phuyu_codsucursal"]." and k.estado=1 AND fechakardex>='".$this->request->desde."' AND fechakardex<='".$this->request->hasta."' GROUP BY k.codkardex, k.codsucursal,k.codpersona, k.valorventa, k.codalmacen,p.razonsocial,k.fechakardex,k.hora,k.seriecomprobante,k.nrocomprobante,p.documento ".$estado." order by k.fechakardex asc ")->result_array();

				$formato = $this->request->formato;

				$this->load->view("reportes/ingresosalidas/prestamosxls",compact("titulo","lista","formato"));
			}
		}
	}
}