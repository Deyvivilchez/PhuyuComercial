<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Listacobrar extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$this->load->view("creditos/reportes/listacobrar");
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
				$filtro = " fechacredito>='".$this->request->campos->fechadesde."' and fechacredito<='".$this->request->campos->fechahasta."' and ";
			}else{
				$filtro = "";
			}
			$estado = '';
			if($this->request->estado!=""){
				if($this->request->estado==3){
					$estado.=' AND creditos.estado<>0';
				}else{
					$estado.=' AND creditos.estado='.$this->request->estado;
				}
			}

			$lista = $this->db->query("select personas.razonsocial,personas.documento,creditos.codcredito,creditos.codkardex, creditos.fechacredito, creditos.fechavencimiento, round(creditos.importe,2) as importe, round(creditos.interes,2) as interes, round(creditos.saldo,2) as saldo, creditos.estado,round(creditos.total,2) as total from kardex.creditos as creditos inner join public.personas as personas on (creditos.codpersona=personas.codpersona) where (UPPER(personas.documento) like UPPER('%".$this->request->buscar."%') or UPPER(personas.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(personas.nombrecomercial) like UPPER('%".$this->request->buscar."%') ) and creditos.codsucursal=".$_SESSION["phuyu_codsucursal"]." ".$estado." and ".$filtro." creditos.tipo=1 order by creditos.fechacredito desc ")->result_array();

			$totalimporte=0;$totalinteres=0;$totalsaldo=0;$totaltotal=0;
			foreach ($lista as $key => $value) {
				$kardex = $this->db->query("select seriecomprobante,nrocomprobante from kardex.kardex where codkardex=".(int)$value["codkardex"])->result_array();
				if (count($kardex)>0) {
					$lista[$key]["comprobante"] = $kardex[0]["seriecomprobante"]."-".$kardex[0]["nrocomprobante"];
				}else{
					$lista[$key]["comprobante"] = "SIN - DOCUMENTO";
				}

				$totalimporte = $totalimporte + $value["importe"];
				$totalinteres = $totalinteres + $value["interes"];
				$totaltotal = $totaltotal + $value["total"];
				$totalsaldo = $totalsaldo + $value["saldo"];
			}

			$total["totalimporte"] = number_format($totalimporte,2,".","");
			$total["totalinteres"] = number_format($totalinteres,2,".","");
			$total["totaltotal"] = number_format($totaltotal,2,".","");
			$total["totalsaldo"] = number_format($totalsaldo,2,".","");

			echo json_encode(array("lista" => $lista,"total"=>$total));
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function editar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			$tipo = "CLIENTE";
			$info = $this->db->query("select c.*, p.documento,p.razonsocial,p.direccion from kardex.creditos as c inner join public.personas as p on(c.codpersona=p.codpersona) where c.codcredito=".$this->request->codregistro)->result_array();
			
			$sunat_existe = $this->db->query("select estado from sunat.kardexsunat where codkardex=".(int)$info[0]["codkardex"])->result_array();
			if (count($sunat_existe)==0) {
				$sunat = 0;
			}else{
				if ($sunat_existe[0]["estado"]==0) {
					$sunat = 0;
				}else{
					$sunat = 1;
				}
			}
			$this->load->view("creditos/reportes/editar",compact("tipo","info","sunat"));
		}
	}

	function editar_guardar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			$clave = $this->db->query("select *from public.parametros where clavecambiarsociocredito='".$this->request->clave."'")->result_array();
			if (count($clave)>0) {
				$persona = $this->db->query("select razonsocial,direccion from public.personas where codpersona=".(int)$this->request->campos->codpersona)->result_array();

				$campos = ["codpersona","cliente","direccion"];
				$valores = [$this->request->campos->codpersona,$persona[0]["razonsocial"],$persona[0]["direccion"]];
				if ((int)$this->request->campos->codkardex>0) {
					$estado = $this->phuyu_model->phuyu_editar("kardex.kardex", $campos, $valores, "codkardex",$this->request->campos->codkardex);
				}

				$campos = ["codpersona"]; $valores = [$this->request->campos->codpersona];
				$estado = $this->phuyu_model->phuyu_editar("kardex.creditos", $campos, $valores, "codcredito",$this->request->campos->codcredito);

				$pagos = $this->db->query("select distinct(codmovimiento) as codmovimiento from kardex.cuotaspagos where codcredito=".$this->request->campos->codcredito)->result_array();
				foreach ($pagos as $key => $value) {
					$campos = ["codpersona"]; $valores = [$this->request->campos->codpersona];
					$estado = $this->phuyu_model->phuyu_editar("caja.movimientos", $campos, $valores, "codmovimiento",$value["codmovimiento"]);
				}
			}else{
				$estado = 2;
			}
			echo $estado;
		}
	}

	function phuyu_imprimir($codcredito){
		if (isset($_SESSION["phuyu_usuario"])) {
			$info = $this->db->query("select c.*,p.documento,p.razonsocial,p.direccion,p.telefono from kardex.creditos as c inner join public.personas as p on(c.codpersona=p.codpersona) where c.codcredito=".$codcredito)->result_array();
			$cuotas = $this->db->query("select sum(cp.importe) importe,m.fechamovimiento,m.referencia from kardex.cuotaspagos as cp inner join caja.movimientos as m on(cp.codmovimiento=m.codmovimiento) where cp.codcredito=".$codcredito." and m.estado=1 group by m.fechamovimiento,m.referencia order by m.fechamovimiento")->result_array();

			if ($info[0]["codkardex"]!="") {
				$kardex = $this->db->query("select kardex.seriecomprobante,kardex.nrocomprobante, (select COALESCE(string_agg(p.descripcion::text || ' || CANT: ' || round(kd.cantidad,2)::text || ' || P.U: ' || round(kd.preciounitario,2)::text,','), '') from kardex.kardex as k inner join kardex.kardexdetalle as kd on(k.codkardex=kd.codkardex) inner join almacen.productos as p on(kd.codproducto=p.codproducto) where kardex.codkardex=k.codkardex) as referencia from kardex.kardex as kardex where kardex.codkardex=".(int)$info[0]["codkardex"])->result_array();
			}else{
				$kardex = [];
			}

			$this->load->library('Pdf2'); $pdf = new Pdf2(); $pdf->AddPage();
			$pdf->pdf_header("CREDITO POR COBRAR","");

			$pdf->SetFont('Arial','B',10); $pdf->setFillColor(245,245,245);
	        $pdf->Cell(0,7,"CLIENTE: ".utf8_decode($info[0]["razonsocial"]),1,1,'L',1);
	        $pdf->Cell(0,7,"DIRECCION: ".utf8_decode($info[0]["direccion"]),1,1,'L',1);

	        $pdf->SetFont('Arial','',8); $pdf->setFillColor(260,260,260);
	        $pdf->Cell(50,6,"DNI/RUC: ".$info[0]["documento"],1,0,'L',1);
	        $pdf->Cell(50,6,"TELF/CEL: ".$info[0]["telefono"],1,0,'L',1);
	        $pdf->Cell(45,6,"FECHA CREDITO: ".$info[0]["fechacredito"],1,0,'L',1);
	        $pdf->Cell(45,6,"FECHA VENCE: ".$info[0]["fechavencimiento"],1,1,'L',1);

	        $pdf->Cell(50,6,"NRO CUOTAS: 0".$info[0]["nrocuotas"],1,0,'L',1);
	        $pdf->Cell(50,6,"IMPORTE: ".number_format($info[0]["importe"],2),1,0,'L',1);
	        $pdf->Cell(45,6,"INTERES: ".number_format($info[0]["interes"],2),1,0,'L',1);
	        $pdf->Cell(45,6,"TOTAL: ".number_format($info[0]["total"],2),1,1,'L',1);

	        $hora_i_2 = new DateTime("now"); $hora_s_2 = new DateTime($info[0]["fechavencimiento"]);	
			$intervalo_2 = $hora_i_2->diff($hora_s_2);
			if(date("Y-m-d") < $info[0]["fechavencimiento"]){
				$sum_dias = (int)$intervalo_2->days + 1;
				$nota = "EL CREDITO ESTÁ POR VENCER EN ".$sum_dias." DIA(S)";
			}else{
				$nota = "EL CREDITO ESTÁ VENCIDO HACE ".$intervalo_2->days." DIA(S)";
			}

	        $pdf->SetTextColor(250,10,0);
	        $pdf->Cell(50,6,"SALDO: ".number_format($info[0]["saldo"],2),1,0,'L',1);
	        $pdf->Cell(0,6,"NOTA: ".utf8_decode($nota),1,1,'L',1);
	        $pdf->Ln(); $pdf->SetTextColor(0,0,0);

	        if (count($kardex)>0) {
		        $pdf->Cell(50,6,"COMPROBANTE: ".$kardex[0]["seriecomprobante"]."-".$kardex[0]["nrocomprobante"],1,0,'L',1);
		        $pdf->MultiCell(0,6,"DETALLE: ".utf8_decode($kardex[0]["referencia"]),1,1,'L',1);
		        $pdf->Ln();
	        }

	        $pdf->SetFont('Arial','B',10); 
	        $pdf->setFillColor(245,245,245); 
	        $pdf->Cell(0,7,"ESTADO DE CUENTA DEL CREDITO",1,1,'L',1);

	        $header = array("N°","FECHA","CARGO","ABONO","SALDO","DESCRIPCION");
			$w = array(10,20,20,20,20,100);
			for($i=0;$i<count($header);$i++){
			    $pdf->Cell($w[$i],7,utf8_decode($header[$i]),1,0,'L');
			}
			$pdf->Ln();

			$pdf->SetWidths(array(10,20,20,20,20,100)); $pdf->SetLineHeight(5); $pdf->SetFont('Arial','B',8);
			
			$datos = array("01");
			array_push($datos,utf8_decode($info[0]["fechacredito"]));
			array_push($datos,number_format($info[0]["total"],2)); array_push($datos,"");
			array_push($datos,number_format($info[0]["total"],2));
			array_push($datos,utf8_decode($info[0]["referencia"]));
            $pdf->Row($datos);

			$item = 1; $saldo = $info[0]["total"];
			foreach ($cuotas as $key => $value) { $item = $item + 1;
				$datos = array("0".$item); $saldo = $saldo - $value["importe"];
				array_push($datos,utf8_decode($value["fechamovimiento"])); array_push($datos,"");
				array_push($datos,number_format($value["importe"],2));
				array_push($datos,number_format($saldo,2));
				array_push($datos,utf8_decode($value["referencia"]));
                $pdf->Row($datos);
			}			
			$pdf->SetTitle(utf8_decode("phuyu Perú - Credito")); $pdf->Output();
		}
	}

	function phuyu_imprimirlistapdf(){
		if (isset($_SESSION["phuyu_usuario"])) {
			$estado = ''; $tipo = "TODOS";
			if($_GET["estado"]!=""){
				if($_GET["estado"]==3){
					$estado.=' AND creditos.estado<>0';
				}else{
					$estado.=' AND creditos.estado='.$_GET["estado"];
				}
			}
			if($_GET["estado"]===""){
				$tipo = "TODOS";
			}
			else if($_GET["estado"]==0){
				$tipo='ANULADOS';
			}else if($_GET["estado"]==1){
				$tipo="PENDIENTES";
			}else if($_GET["estado"]==2){
				$tipo="CANCELADOS";
			}

			$this->request = json_decode($_GET["campos"]);

			if ($this->request->filtro==1) {
				$filtro = " fechacredito>='".$this->request->fechadesde."' and fechacredito<='".$this->request->fechahasta."' and ";
			}else{
				$filtro = "";
			}

			$info = $this->db->query("select personas.razonsocial, personas.documento, creditos.codcredito,creditos.codkardex, creditos.fechacredito, creditos.fechavencimiento, round(creditos.importe,2) as importe, round(creditos.interes,2) as interes, round(creditos.saldo,2) as saldo,round(creditos.total,2) as total, creditos.estado from kardex.creditos as creditos inner join public.personas as personas on (creditos.codpersona=personas.codpersona) where creditos.codsucursal=".$_SESSION["phuyu_codsucursal"]." ".$estado." and ".$filtro." creditos.tipo=1 order by creditos.codcredito desc ")->result_array();

			foreach ($info as $key => $value) {
				$kardex = $this->db->query("select seriecomprobante,nrocomprobante from kardex.kardex where codkardex=".(int)$value["codkardex"])->result_array();
				if (count($kardex)>0) {
					$info[$key]["comprobante"] = $kardex[0]["seriecomprobante"]."-".$kardex[0]["nrocomprobante"];
				}else{
					$info[$key]["comprobante"] = "SIN - DOCUMENTO";
				}
			}

			$this->load->library('Pdf2'); $pdf = new Pdf2(); $pdf->AddPage();
			$pdf->pdf_header("LISTA DE CREDITOS GENERAL","");

	        $pdf->SetFont('Arial','B',10); 
	        $pdf->setFillColor(245,245,245); 
	        $pdf->Cell(0,7,"CREDITOS POR COBRAR - ".$tipo,1,1,'L',1);
	        $pdf->SetFont('Arial','B',7);
	        $header = array("N°","DOCUMENTO","RAZON SOCIAL","F. CREDITO","F. VENCE","COMPROBANTE","IMPORTE","INTERES","TOTAL","SALDO");
			$w = array(10,18,45,16,15,21,17,15,17,16);
			for($i=0;$i<count($header);$i++){
			    $pdf->Cell($w[$i],7,utf8_decode($header[$i]),1,0,'L');
			}
			$pdf->Ln();

			$pdf->SetWidths(array(10,18,45,16,15,21,17,15,17,16)); $pdf->SetLineHeight(5); $pdf->SetFont('Arial','',7);
			$totalimporte=0;$totalinteres=0;$totaltotal=0;$totalsaldo=0;
			foreach ($info as $key => $value) {
				$datos = array($key+1);
				array_push($datos,utf8_decode($value["documento"]));
				array_push($datos,utf8_decode($value["razonsocial"])); 
				array_push($datos,$value["fechacredito"]);
				array_push($datos,$value["fechavencimiento"]);
				array_push($datos,utf8_decode($value["comprobante"]));
				array_push($datos,number_format($value["importe"],2));
				array_push($datos,number_format($value["interes"],2));
				array_push($datos,number_format($value["total"],2));
				array_push($datos,number_format($value["saldo"],2));
				$pdf->Row($datos);
				$totalimporte = $totalimporte + $value["importe"];
				$totalinteres = $totalinteres + $value["interes"];
				$totaltotal = $totaltotal + $value["total"];
				$totalsaldo = $totalsaldo + $value["saldo"];
			}
			$pdf->SetFont('Arial','B',7);
			$pdf->Cell(125,5,"TOTALES",1,0,'R');
		    $pdf->Cell(17,5,number_format($totalimporte,2,".",""),1,0,"R");
		    $pdf->Cell(15,5,number_format($totalinteres,2,".",""),1,0,"R");
		    $pdf->Cell(17,5,number_format($totaltotal,2,".",""),1,0,"R");
		    $pdf->Cell(16,5,number_format($totalsaldo,2,".",""),1,0,"R");
            
		
			$pdf->SetTitle(utf8_decode("phuyu Perú - Credito")); $pdf->Output();
		}
	}

	function phuyu_imprimirlistaexcel(){
		if (isset($_SESSION["phuyu_usuario"])) {
			$estado = ''; $tipo = "TODOS";
			if($_GET["estado"]!=""){
				if($_GET["estado"]==3){
					$estado.=' AND creditos.estado<>0';
				}else{
					$estado.=' AND creditos.estado='.$_GET["estado"];
				}
			}
			if($_GET["estado"]===""){
				$tipo = "TODOS";
			}
			else if($_GET["estado"]==0){
				$tipo='ANULADOS';
			}else if($_GET["estado"]==1){
				$tipo="PENDIENTES";
			}else if($_GET["estado"]==2){
				$tipo="CANCELADOS";
			}

			$this->request = json_decode($_GET["campos"]);

			if ($this->request->filtro==1) {
				$filtro = " fechacredito>='".$this->request->fechadesde."' and fechacredito<='".$this->request->fechahasta."' and ";
			}else{
				$filtro = "";
			}

			$info = $this->db->query("select personas.razonsocial, personas.documento, creditos.codcredito,creditos.codkardex, creditos.fechacredito, creditos.fechavencimiento, round(creditos.importe,2) as importe, round(creditos.interes,2) as interes, round(creditos.saldo,2) as saldo,round(creditos.total,2) as total, creditos.estado from kardex.creditos as creditos inner join public.personas as personas on (creditos.codpersona=personas.codpersona) where creditos.codsucursal=".$_SESSION["phuyu_codsucursal"]." ".$estado." and ".$filtro." creditos.tipo=1 order by creditos.codcredito desc ")->result_array();

			$totalimporte=0;$totalinteres=0;$totalsaldo=0;$totaltotal=0;
			foreach ($info as $key => $value) {
				$kardex = $this->db->query("select seriecomprobante,nrocomprobante from kardex.kardex where codkardex=".(int)$value["codkardex"])->result_array();
				if (count($kardex)>0) {
					$info[$key]["comprobante"] = $kardex[0]["seriecomprobante"]."-".$kardex[0]["nrocomprobante"];
				}else{
					$info[$key]["comprobante"] = "SIN - DOCUMENTO";
				}
				$totalimporte = $totalimporte + $value["importe"];
				$totalinteres = $totalinteres + $value["interes"];
				$totaltotal = $totaltotal + $value["total"];
				$totalsaldo = $totalsaldo + $value["saldo"];
			}

			$total["totalimporte"] = $totalimporte;
			$total["totalinteres"] = $totalinteres;
			$total["totaltotal"] = $totaltotal;
			$total["totalsaldo"] = $totalsaldo;

			$this->load->view("creditos/reportes/listageneralxls",compact("info","total","tipo"));
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