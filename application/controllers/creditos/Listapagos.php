<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Listapagos extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$this->load->view("creditos/listapagos/index");
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
			if ($this->request->campos->filtro==1) {
				$filtro = " creditos.fechamovimiento>='".$this->request->campos->fechadesde."' and creditos.fechamovimiento<='".$this->request->campos->fechahasta."' and ";
			}else{
				$filtro = "";
			}
			$estado = '';
			if($this->request->estado!=""){
				$estado.=' AND creditos.estadocuotapago='.$this->request->estado;
			}

			$lista = $this->db->query("select *from caja.v_creditospagos as creditos inner join public.personas as personas on (creditos.codpersona=personas.codpersona) where (UPPER(personas.documento) like UPPER('%".$this->request->buscar."%') or UPPER(personas.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(personas.nombrecomercial) like UPPER('%".$this->request->buscar."%') ) and creditos.codsucursalcredito=".$_SESSION["phuyu_codsucursal"]." ".$estado." and ".$filtro." creditos.tipo=2 order by creditos.fechamovimiento desc ")->result_array();

			$totaltotal=0;$totalpago=0;
			foreach ($lista as $key => $value) {

				$totaltotal = $totaltotal + $value["totalcredito"];
				$totalpago = $totalpago + $value["importe"];
			}

			$total["totalpago"] = number_format($totalpago,2,".","");
			$total["totaltotal"] = number_format($totaltotal,2,".","");

			echo json_encode(array("lista" => $lista,"total"=>$total));
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function phuyu_imprimirlistapdf(){
		if (isset($_SESSION["phuyu_usuario"])) {
			$estado = ''; $tipo = "TODOS";
			if($_GET["estado"]!=""){
				$estado.=' AND creditos.estadocuotapago='.$_GET["estado"];
			}
			if($_GET["estado"]===""){
				$tipo = "TODOS";
			}
			else if($_GET["estado"]==0){
				$tipo='ANULADOS';
			}else{
				$tipo="ACTIVOS";
			}

			$this->request = json_decode($_GET["campos"]);

			if ($this->request->filtro==1) {
				$filtro = " creditos.fechamovimiento>='".$this->request->fechadesde."' and creditos.fechamovimiento<='".$this->request->fechahasta."' and ";
			}else{
				$filtro = "";
			}

			$info = $this->db->query("select *from caja.v_creditospagos as creditos where creditos.codsucursalcredito=".$_SESSION["phuyu_codsucursal"]." ".$estado." and ".$filtro." creditos.tipo=2 order by creditos.fechamovimiento desc ")->result_array();

			$this->load->library('Pdf2'); $pdf = new Pdf2(); $pdf->AddPage();
			$pdf->pdf_header("LISTA DE PAGOS GENERAL","");

	        $pdf->SetFont('Arial','B',10); 
	        $pdf->setFillColor(245,245,245); 
	        $pdf->Cell(0,7,"LISTA DE PAGOS - ".$tipo." DEL ".$this->request->fechadesde.' AL '.$this->request->fechahasta,1,1,'L',1);
	        $pdf->SetFont('Arial','B',7);
	        $header = array("N°","RAZON SOCIAL","F. CREDITO","F. VENCE","COMPROBANTE","TOTAL CRED.","F. PAGO","IMPORTE");
			$w = array(10,58,20,20,22,20,20,20);
			for($i=0;$i<count($header);$i++){
			    $pdf->Cell($w[$i],7,utf8_decode($header[$i]),1,0,'L');
			}
			$pdf->Ln();

			$pdf->SetWidths(array(10,58,20,20,22,20,20,20)); $pdf->SetLineHeight(5); $pdf->SetFont('Arial','',7);
			$totalimporte=0;$totalinteres=0;$totaltotal=0;$totalsaldo=0;
			foreach ($info as $key => $value) {
				$datos = array($key+1);
				array_push($datos,utf8_decode($value["cliente"])); 
				array_push($datos,$value["fechacredito"]);
				array_push($datos,$value["fechavencimientocredito"]);
				array_push($datos,utf8_decode($value["seriecomprobante"].'-'.$value["nrocomprobante"]));
				array_push($datos,number_format($value["totalcredito"],2));
				array_push($datos,$value["fechamovimiento"]);
				array_push($datos,number_format($value["importe"],2));
				$pdf->Row($datos);
				$totalimporte = $totalimporte + $value["importe"];
				$totaltotal = $totaltotal + $value["totalcredito"];
			}
			$pdf->SetFont('Arial','B',7);
			$pdf->Cell(130,5,"TOTALES",1,0,'R');
		    $pdf->Cell(20,5,number_format($totaltotal,2,".",""),1,0,"R");
		    $pdf->Cell(20,5,"",1,0,"R");
		    $pdf->Cell(20,5,number_format($totalimporte,2,".",""),1,0,"R");
            
		
			$pdf->SetTitle(utf8_decode("phuyu Perú - Cobranza")); $pdf->Output();
		}
	}

	function phuyu_imprimirlistaexcel(){
		if (isset($_SESSION["phuyu_usuario"])) {
			$estado = ''; $tipo = "TODOS";
			if($_GET["estado"]!=""){
				$estado.=' AND creditos.estadocuotapago='.$_GET["estado"];
			}
			if($_GET["estado"]===""){
				$tipo = "TODOS";
			}
			else if($_GET["estado"]==0){
				$tipo='ANULADOS';
			}else{
				$tipo="ACTIVOS";
			}

			$this->request = json_decode($_GET["campos"]);

			if ($this->request->filtro==1) {
				$filtro = " creditos.fechamovimiento>='".$this->request->fechadesde."' and creditos.fechamovimiento<='".$this->request->fechahasta."' and ";
			}else{
				$filtro = "";
			}

			$info = $this->db->query("select *from caja.v_creditospagos as creditos where creditos.codsucursalcredito=".$_SESSION["phuyu_codsucursal"]." ".$estado." and ".$filtro." creditos.tipo=2 order by creditos.fechamovimiento desc ")->result_array();

			$totalpago=0;$totalinteres=0;$totalsaldo=0;$totaltotal=0;
			foreach ($info as $key => $value) {
				$totaltotal = $totaltotal + $value["totalcredito"];
				$totalpago = $totalpago + $value["importe"];
			}

			$total["totaltotal"] = $totaltotal;
			$total["totalpago"] = $totalpago;
			$fechadesde = $this->request->fechadesde;
			$fechahasta = $this->request->fechahasta;

			$this->load->view("creditos/listacobranza/listageneralxls",compact("info","total","tipo","fechadesde","fechahasta"));
		}
	}

	function pdf_creditos(){
		if (isset($_SESSION["phuyu_usuario"])) {

			$this->load->library('Pdf2'); $pdf = new Pdf2(); $pdf->AddPage();

			$pdf->Image('./public/img/'.$_SESSION['phuyu_logo'], 10, 8, 15);
			$pdf->SetFont('Arial', 'B', 12);
		    $pdf->Cell(145, 5, utf8_decode(substr($_SESSION["phuyu_empresa"],0,30)),0,0,'C');
		    $pdf->Cell(145, 5, utf8_decode($_SESSION["phuyu_sucursal"]));
		    $pdf->Ln(8); $pdf->SetFont('Arial', 'B', 10);
		    $pdf->Cell(145, 5, utf8_decode("REPORTE DE CREDITOS POR COBRAR"),0,0,'C');
		    $pdf->Cell(145, 5, utf8_decode($_SESSION["phuyu_caja"]));
		    $pdf->Ln(5); $pdf->Cell(0,0.05,"",1,1,'L',1); $pdf->Ln(5);

		    $lista = $this->db->query("select personas.razonsocial,personas.documento,creditos.* from kardex.creditos AS creditos inner join public.personas as personas on (creditos.codpersona=personas.codpersona) where creditos.codsucursal=".$_SESSION["phuyu_codsucursal"]." and creditos.estado=1 and creditos.tipo=1 order by creditos.codcredito desc")->result_array();

		    $pdf->SetFont('Arial','',8);
			$pdf->SetFillColor(20,20,0);
			$pdf->SetDrawColor(10,0,0);
			$pdf->SetFont('Arial','B');
			$header = array("N°","COMPROBANTE","CLIENTE","F.CREDITO","F.VENCE","IMPORTE","INTERES","SALDO");
			$w = array(10,24,75,18,17,17,16,16);
			for($i=0;$i<count($header);$i++){
			    $pdf->Cell($w[$i],7,utf8_decode($header[$i]),1,0,'L');
			}
			$pdf->Ln();

			$pdf->SetWidths(array(10,24,75,18,17,17,16,16)); $pdf->SetLineHeight(5); $pdf->SetFont('Arial','',8);
			$item = 0; $importe = 0; $interes = 0; $saldo = 0;
			foreach($lista as $value){ $item = $item + 1;
				$kardex = $this->db->query("select seriecomprobante,nrocomprobante from kardex.kardex where codkardex=".(int)$value["codkardex"])->result_array();
				if (count($kardex)>0) {
					$comprobante = $kardex[0]["seriecomprobante"]."-".$kardex[0]["nrocomprobante"];
				}else{
					$comprobante = "SIN-DOC.";
				}

				$importe = $importe + $value["importe"];
				$interes = $interes + $value["interes"];
				$saldo = $saldo + $value["saldo"];

				$datos = array("0".$item);
				array_push($datos,$comprobante);
				array_push($datos,utf8_decode($value["razonsocial"]));
				array_push($datos,$value["fechacredito"]);
				array_push($datos,$value["fechavencimiento"]);
				array_push($datos,number_format($value["importe"],2));
				array_push($datos,number_format($value["interes"],2));
				array_push($datos,number_format($value["saldo"],2));
                $pdf->Row($datos);
			}
			$pdf->Cell(array_sum($w),0,'','T'); $pdf->Ln();
			$pdf->SetFont('Arial','B',8);
			$pdf->Cell(144,5,"TOTALES",1,0,'R');
		    $pdf->Cell(17,5,number_format($importe,2),1,"R");
		    $pdf->Cell(16,5,number_format($interes,2),1,"R");
		    $pdf->Cell(16,5,number_format($saldo,2),1,"R"); $pdf->Ln(); $pdf->Ln();

			$pdf->SetTitle(utf8_decode("phuyu Perú - Creditos Cobrar")); $pdf->Output();
		}else{
			$this->load->view("phuyu/505");
		}
	}

	/* function actualizar_creditos(){
		$lista = $this->db->query("select *from kardex.creditos where codmovimiento=0")->result_array();
		foreach ($lista as $key => $value) {
			$movimientos = $this->db->query("select *from caja.movimientos where codpersona=".$value["codpersona"]." and fechamovimiento='".$value["fechacredito"]."' and condicionpago=2")->result_array();

			echo $value["codcredito"]." TIPO CREDITO: ".$value["codpersona"]." - TOTAL DEL CREDITO: ".$value["total"]." FECHA CREDIO ".$value["fechacredito"]."<br>";

			foreach ($movimientos as $val) {
				$campos = ["codmovimiento"]; $valores = [$val["codmovimiento"]];
				$estado = $this->phuyu_model->phuyu_editar("kardex.creditos", $campos, $valores, "codcredito", $value["codcredito"]);

				echo "MOVIMIENTO: ".$val["importe"]." FECHAMOV: ".$val["fechamovimiento"]."<br>";
			}
			echo "<br> <br>";
		}
	}

	function actualizar_fechavence(){
		$lista = $this->db->query("select c.codcredito, (select max(fechavence) from kardex.cuotas where c.codcredito=codcredito) as fechavence from kardex.creditos as c where c.fechavencimiento is null")->result_array();
		foreach ($lista as $key => $value) {
			$campos = ["fechavencimiento"]; $valores = [$value["fechavence"]];
			$estado = $this->phuyu_model->phuyu_editar("kardex.creditos", $campos, $valores, "codcredito", $value["codcredito"]);

			echo $value["codcredito"]."<br>";
		}
	} */
}