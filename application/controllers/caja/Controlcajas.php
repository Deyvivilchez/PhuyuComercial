<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Controlcajas extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model"); $this->load->model("Caja_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {

				$caja = $this->Caja_model->phuyu_estadocaja();
				if (count($caja) == 0) {
					$saldocaja = $this->Caja_model->phuyu_saldocaja_general($_SESSION["phuyu_codcaja"]); 
					$saldobanco = $this->Caja_model->phuyu_saldobanco_general($_SESSION["phuyu_codcaja"]);
					$automatico = $this->db->query("select saldarautomaticamente as estado from caja.cajas where codcaja=".$_SESSION["phuyu_codcaja"])->result_array(); 
					$this->load->view("caja/controlcajas/aperturar", compact("saldocaja","saldobanco","automatico"));
				}else{
					$tipopagos = $this->db->query("select *from caja.tipopagos where estado=1 order by codtipopago")->result_array();
					foreach ($tipopagos as $key => $value) {
						$total = $this->Caja_model->phuyu_saldotipopago($_SESSION["phuyu_codcontroldiario"],$value["codtipopago"]);

						$tipopagos[$key]["transacciones"] = $total["transacciones"];
						$tipopagos[$key]["ingresosconfirmados"] = $total["ingresosconfirmados"];
						$tipopagos[$key]["ingresospendientes"] = $total["ingresospendientes"];
						$tipopagos[$key]["ingresos"] = $total["ingresos"];
						$tipopagos[$key]["egresosconfirmados"] = $total["egresosconfirmados"];
						$tipopagos[$key]["egresospendientes"] = $total["egresospendientes"];
						$tipopagos[$key]["egresos"] = $total["egresos"];
					}

					$comprobantes = $this->db->query("select *from caja.comprobantetipos where control=1 and estado=1 order by codcomprobantetipo")->result_array();
					foreach ($comprobantes as $key => $value) {
						$total = $this->Caja_model->phuyu_saldocomprobantes($_SESSION["phuyu_codcontroldiario"],$value["codcomprobantetipo"]);

						$comprobantes[$key]["ingresos"] = $total["ingresos"];
						$comprobantes[$key]["egresos"] = $total["egresos"];
					}

					$saldocaja = $this->Caja_model->phuyu_saldocaja_diario($_SESSION["phuyu_codcontroldiario"]); 
					$saldobanco = $this->Caja_model->phuyu_saldobanco_diario($_SESSION["phuyu_codcontroldiario"]); 

					$this->load->view("caja/controlcajas/index", compact("caja","tipopagos","comprobantes","saldocaja","saldobanco"));
				}
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function phuyu_graficocaja(){
		if ($this->input->is_ajax_request()) {
			$saldocaja = $this->Caja_model->phuyu_saldocaja_general($_SESSION["phuyu_codcaja"]); 
			$saldobanco = $this->Caja_model->phuyu_saldobanco_general($_SESSION["phuyu_codcaja"]); 

			$data = array();
			$data["ingresos"] = [(double)$saldocaja["ingresos"],(double)$saldobanco["ingresos"]];
			$data["egresos"] = [(double)$saldocaja["egresos"],(double)$saldobanco["egresos"]];
			echo json_encode($data);
		}
	}

	function phuyu_aperturar(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$this->request = json_decode(file_get_contents('php://input'));
				$this->db->trans_begin();

				$caja = $this->db->query("select max(codcontroldiario) as codcontroldiario from caja.controldiario where codcaja=".$_SESSION["phuyu_codcaja"]." and codsucursal=".$_SESSION["phuyu_codsucursal"]." and cerrado=0 and estado=1")->result_array();
				if ($caja[0]["codcontroldiario"]=="") {
					$saldoinicialcaja = $this->Caja_model->phuyu_saldocaja(0);
					$saldoinicialbanco = $this->Caja_model->phuyu_saldobanco(0);
				}else{
					$saldoinicialcaja = $this->Caja_model->phuyu_saldocaja($caja[0]["codcontroldiario"]);
					$saldoinicialbanco = $this->Caja_model->phuyu_saldobanco($caja[0]["codcontroldiario"]);
				}

				$campos = ["codcaja","codusuario","codsucursal","saldoinicialcaja","saldoinicialbanco","codigodiario","cerrado"];
				$valores = [
					(int)$_SESSION["phuyu_codcaja"],
					(int)$_SESSION["phuyu_codusuario"],
					(int)$_SESSION["phuyu_codsucursal"],
					((double)$saldoinicialcaja["total"] + (double)$saldoinicialcaja["saldoinicial"]),
					((double)$saldoinicialbanco["total"] + (double)$saldoinicialbanco["saldoinicial"]),
					date("dmY"),1
				];
				$estado = $this->phuyu_model->phuyu_guardar("caja.controldiario", $campos, $valores);

				$automatico = $this->db->query("select saldarautomaticamente as estado from caja.cajas where codcaja=".$_SESSION["phuyu_codcaja"])->result_array();

                if($automatico[0]['estado'] == 1){

					$caja = $this->Caja_model->phuyu_estadocaja();

					if(count($caja) > 0){
	                    $series = $this->db->query("select seriecomprobante from caja.comprobantes where codcomprobantetipo=1 and codsucursal=".$_SESSION["phuyu_codsucursal"]." and codcaja=".$_SESSION["phuyu_codcaja"]." and estado=1")->result_array();

						$persona = $this->db->query("select codempleado from seguridad.usuarios where codusuario = ".$_SESSION["phuyu_codusuario"]." and estado=1 order by codusuario")->result_array();

						$fechamovimiento = date('Y-m-d');

						$campos = ["codcontroldiario","codcaja","codconcepto","codpersona","codusuario","codcomprobantetipo","seriecomprobante","tipomovimiento","importe","codcomprobantetipo_ref","seriecomprobante_ref","nrocomprobante_ref","referencia","codempleado","condicionpago","fechamovimiento"];
						$valores = [
							(int)$_SESSION["phuyu_codcontroldiario"],
							(int)$_SESSION["phuyu_codcaja"],
							1,
							1,
							(int)$_SESSION["phuyu_codusuario"],
							1,
							$series[0]["seriecomprobante"],1,
							(double)($this->request->monto_apertura),1,
							"REF","",
							"SALDO INICIAL DE CAPITAL A CAJA CENTRAL",
							1,
							1, $fechamovimiento
						];

						$codmovimiento = $this->phuyu_model->phuyu_guardar("caja.movimientos", $campos, $valores,"true");
						$estado = $this->Caja_model->phuyu_correlativo($codmovimiento,1,$series[0]["seriecomprobante"]);

						$campos = ["codmovimiento","codtipopago","codcontroldiario","codcaja","fechadocbanco","nrodocbanco","importe","importeentregado","vuelto"];
						$valores = [
							(int)$codmovimiento,
							1,
							(int)$_SESSION["phuyu_codcontroldiario"],
							(int)$_SESSION["phuyu_codcaja"],
							$fechamovimiento,
							"",
							(double)($this->request->monto_apertura),
							(double)($this->request->monto_apertura),
							0
						];
						$estado = $this->phuyu_model->phuyu_guardar("caja.movimientosdetalle", $campos, $valores);
					}
				}

				if ($estado == 1 ) {
					$this->db->trans_commit();
				}else{
					$this->db->trans_rollback(); $estado = 0;
				}
				echo $estado;
			}else{
				echo "e";
			}	
	    }else{
			$this->load->view("inicio/404");
		}
	}

	function phuyu_cerrar(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$this->db->trans_begin();

				$saldocaja = $this->Caja_model->phuyu_saldocaja($_SESSION["phuyu_codcontroldiario"]);

                if($saldocaja["total"] > 0){
                    $automatico = $this->db->query("select saldarautomaticamente as estado from caja.cajas where codcaja=".$_SESSION["phuyu_codcaja"])->result_array();
					if($automatico[0]['estado'] == 1){
						$series = $this->db->query("select seriecomprobante from caja.comprobantes where codcomprobantetipo=2 and codsucursal=".$_SESSION["phuyu_codsucursal"]." and codcaja=".$_SESSION["phuyu_codcaja"]." and estado=1")->result_array();

						$persona = $this->db->query("select codempleado from seguridad.usuarios where codusuario = ".$_SESSION["phuyu_codusuario"]." and estado=1 order by codusuario")->result_array();

						$fechamovimiento = date('Y-m-d');

						$campos = ["codcontroldiario","codcaja","codconcepto","codpersona","codusuario","codcomprobantetipo","seriecomprobante","tipomovimiento","importe","codcomprobantetipo_ref","seriecomprobante_ref","nrocomprobante_ref","referencia","codempleado","condicionpago","fechamovimiento"];
						$valores = [
							(int)$_SESSION["phuyu_codcontroldiario"],
							(int)$_SESSION["phuyu_codcaja"],
							9,
							1,
							(int)$_SESSION["phuyu_codusuario"],
							2,
							$series[0]["seriecomprobante"],2,
							(double)($saldocaja["total"]),18,
							"REF","",
							"DEVOLUCION DE CAPITAL A CAJA CENTRAL",
							1,
							1, $fechamovimiento
						];

						$codmovimiento = $this->phuyu_model->phuyu_guardar("caja.movimientos", $campos, $valores,"true");
						$estado = $this->Caja_model->phuyu_correlativo($codmovimiento,2,$series[0]["seriecomprobante"]);

						$campos = ["codmovimiento","codtipopago","codcontroldiario","codcaja","fechadocbanco","nrodocbanco","importe","importeentregado","vuelto"];
						$valores = [
							(int)$codmovimiento,
							1,
							(int)$_SESSION["phuyu_codcontroldiario"],
							(int)$_SESSION["phuyu_codcaja"],
							$fechamovimiento,
							"",
							(double)($saldocaja["total"]),
							(double)($saldocaja["total"]),
							0
						];
						$estado = $this->phuyu_model->phuyu_guardar("caja.movimientosdetalle", $campos, $valores);
					}
                }
                
                $saldocaja = $this->Caja_model->phuyu_saldocaja($_SESSION["phuyu_codcontroldiario"]);
				$saldobanco = $this->Caja_model->phuyu_saldobanco($_SESSION["phuyu_codcontroldiario"]);

				$campos = ["codusuariocierre","fechacierre","saldofinalcaja","totalingresoscaja","totalegresoscaja","saldofinalbanco","totalingresosbanco","totalegresosbanco","cerrado"];
				$valores = [
					(int)$_SESSION["phuyu_codusuario"],date("Y-m-d"),
					(double)($saldocaja["total"]),
					(double)($saldocaja["ingresos"]),
					(double)($saldocaja["egresos"]),
					(double)($saldobanco["total"]),
					(double)($saldobanco["ingresos"]),
					(double)($saldobanco["egresos"]),0
				];
				$estado = $this->phuyu_model->phuyu_editar("caja.controldiario", $campos, $valores, "codcontroldiario", $_SESSION["phuyu_codcontroldiario"]);
				if ($estado == 1 ) {
					$this->db->trans_commit();
				}else{
					$this->db->trans_rollback(); $estado = 0;
				}
				echo $estado;
			}else{
				echo "e";
			}
	    }else{
			$this->load->view("inicio/404");
		}
	}

	function phuyu_almacenes($codsucursal){
		if ($this->input->is_ajax_request()) {
			$almacenes = $this->db->query("select * from almacen.almacenes where codsucursal=".$codsucursal." and estado=1")->result_array();
			echo json_encode($almacenes);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function phuyu_cajas($codsucursal){
		if ($this->input->is_ajax_request()) {
			$cajas = $this->db->query("select * from caja.cajas where codsucursal=".$codsucursal." and estado=1")->result_array();
			echo json_encode($cajas);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function phuyu_seriescaja($codcomprobantetipo){
		if ($this->input->is_ajax_request()) {
			$series = $this->db->query("select seriecomprobante,nombrecomercial from caja.comprobantes where codcomprobantetipo=".$codcomprobantetipo." and codsucursal=".$_SESSION["phuyu_codsucursal"]." and estado=1 ORDER BY seriecomprobante")->result_array();
			$serie = "";
			if (count($series)>0) {
				$serie = $series[0]["seriecomprobante"];
			}
			foreach ($series as $key => $value) {
				$nombrecomercial = '';
				if($value["nombrecomercial"]!=""){
					$nombrecomercial = ' - '.$value["nombrecomercial"];
				}
				$series[$key]["seriedescripcion"] = $value["seriecomprobante"].$nombrecomercial;
			}
			$data = array();
			$data["series"] = $series;
			$data["serie"] = $serie;
			echo json_encode($data);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function phuyu_correlativo($codcomprobantetipo,$seriecomprobante){
		if ($this->input->is_ajax_request()) {
			$comprobante = $this->db->query("select nrocorrelativo from caja.comprobantes where codcomprobantetipo=".$codcomprobantetipo." and seriecomprobante='".$seriecomprobante."' and codsucursal=".$_SESSION["phuyu_codsucursal"]." and estado=1")->result_array();
			if (count($comprobante)==0) {
				$nrocorrelativo = "00000000";
			}else{
				$nrocorrelativo = (int)($comprobante[0]["nrocorrelativo"]) + 1;
				$nrocorrelativo = str_pad($nrocorrelativo, 8, "0", STR_PAD_LEFT);
			}
			echo $nrocorrelativo;
		}
	}

	// FUNCIONES DE REPORTES DE CAJA //

	function pdf_cabecera($titulo, $subtitulo){
		$enlace_logo = base_url().'public/img/'.$_SESSION['phuyu_logo'];
		if(!file_exists($enlace_logo)){
			$enlace_logo = '';
		}
		$html = '<table width="100%" align="center">';
			$html .= '<tr>';
				$html .= '<th style="width:15%">';
					$html .='<img src="'.$enlace_logo.'" height="50">';
				$html .= '</th>';
				$html .= '<th style="width:55%">';
					$html .= '<h3>'.$_SESSION["phuyu_empresa"].'</h3>';
					$html .= '<h4>'.$titulo.'</h4>';
				$html .= '</th>';
				$html .= '<th style="width:30%">';
					$html .= '<h3>'.$_SESSION["phuyu_sucursal"].'</h3>';
					$html .= '<h4>'.$_SESSION["phuyu_caja"].'</h4>';
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

	function pdf_arqueo($fecha){
		$estilo = "border-top:1px solid #D5D8DC; border-left:1px solid #D5D8DC; border-right:1px solid #D5D8DC;";
		$html = $this->pdf_cabecera("ARQUEO DE CAJA","FECHA DEL ARQUEO DE CAJA (".$fecha.")");

		$sesiones = $this->db->query("select *from caja.controldiario where fechaapertura='".$fecha."' and codcaja=".$_SESSION["phuyu_codcaja"]." and estado=1 order by codcontroldiario desc")->result_array();
		$tipopagos = $this->db->query("select *from caja.tipopagos where estado=1 order by codtipopago")->result_array();

		foreach ($sesiones as $key => $value) {
			$html .= '<h3>SESION DE CAJA 0000'.$value["codcontroldiario"].' - USUARIO: '.$_SESSION["phuyu_usuario"].'</h3>';
			$html .= '<table cellpadding="4" width="100%" style="border:1px solid #D5D8DC;font-size:11px;">';
				$html .= '<tr>';
					$html .= '<th style="'.$estilo.' width:30%;"> <b>SALDO INICIAL</b> </th>';
					$html .= '<th style="'.$estilo.' width:20%;"> <b>EN CAJA:</b> </th>';
					$html .= '<th style="'.$estilo.' width:15%;"> <b>S/. '.$value["saldoinicialcaja"].'</b> </th>';
					$html .= '<th style="'.$estilo.' width:15%;"> <b>EN BANCO:</b> </th>';
					$html .= '<th style="'.$estilo.' width:20%;"> <b>S/. '.$value["saldoinicialbanco"].'</b> </th>';
				$html .= '</tr>';

				$html .= '<tr>';
					$html .= '<th style="'.$estilo.' width:30%;"> <b>FORMA DE PAGO</b> </th>';
					$html .= '<th style="'.$estilo.' width:20%;"> <b>TRANSACCIONES</b> </th>';
					$html .= '<th style="'.$estilo.' width:15%;"> <b>INGRESOS</b> </th>';
					$html .= '<th style="'.$estilo.' width:15%;"> <b>EGRESOS</b> </th>';
					$html .= '<th style="'.$estilo.' width:20%;"> <b>S/. TOTAL</b> </th>';
				$html .= '</tr>';

				$saldocaja = 0;
				foreach ($tipopagos as $key => $val) {
					$total = $this->Caja_model->phuyu_saldotipopago($value["codcontroldiario"],$val["codtipopago"]);
					if ($val["codtipopago"]==1) {
						$saldocaja = $total["ingresos"] - $total["egresos"];
					}
					$html .= '<tr>';
						$html .= '<th style="'.$estilo.'"> '.$val["descripcion"].'</th>';
						$html .= '<th style="'.$estilo.'"> '.$total["transacciones"].' </th>';
						$html .= '<th style="'.$estilo.'"> '.$total["ingresos"].' </th>';
						$html .= '<th style="'.$estilo.'"> '.$total["egresos"].' </th>';
						$html .= '<th style="'.$estilo.'"> S/. '.($total["ingresos"] - $total["egresos"]).' </th>';
					$html .= '</tr>';
				}
			$html .= '</table>';
			$html .= '<h3 align="center">MONTO DE CIERRE DE CAJA EFECTIVO: S/. '.number_format(($saldocaja + $value["saldoinicialcaja"]),2).'</h3> <hr>';
		}

		$this->pdf_imprimir($html,"ARQUEO DE CAJA","arqueo.pdf");
	}

	function pdf_movimientos($desde,$hasta){
		$estilo = "border-top:1px solid #D5D8DC; border-left:1px solid #D5D8DC; border-right:1px solid #D5D8DC;";
		$html = $this->pdf_cabecera("REPORTE DE MOVIMIENTOS DE CAJA","REPORTE DE MOVIMIENTO DESDE (".$desde." HASTA ".$hasta.")");

		$lista = $this->db->query("select movimientos.*, round(movimientos.importe,2) as importe_r, personas.razonsocial,conceptos.descripcion as concepto from caja.movimientos as movimientos inner join public.personas as personas on(movimientos.codpersona=personas.codpersona) inner join caja.conceptos as conceptos on(movimientos.codconcepto=conceptos.codconcepto) where movimientos.fechamovimiento>='".$desde."' and movimientos.fechamovimiento<='".$hasta."' and movimientos.estado=1 order by movimientos.codmovimiento desc")->result_array();

		$html .= '<table cellpadding="4" width="100%" style="border:1px solid #D5D8DC;font-size:9px;">';
			$html .= '<tr>';
				$html .= '<th style="'.$estilo.' width:9%;"> <b>FECHA</b> </th>';
				$html .= '<th style="'.$estilo.' width:12%;"> <b>N° RECIBO</b> </th>';
				$html .= '<th style="'.$estilo.' width:22%;"> <b>CONCEPTO CAJA</b> </th>';
				$html .= '<th style="'.$estilo.' width:12%;"> <b>DOC. REF.</b> </th>';
				$html .= '<th style="'.$estilo.' width:25%;"> <b>RAZÓN SOCIAL</b> </th>';
				$html .= '<th style="'.$estilo.' width:8%;"> <b>TIPO</b> </th>';
				$html .= '<th style="'.$estilo.' width:12%;"> <b>S/. IMPORTE</b> </th>';
			$html .= '</tr>';

			foreach ($lista as $value) {
				$html .= '<tr>';
					$html .= '<th style="'.$estilo.'"> '.$value["fechamovimiento"].'</th>';
					$html .= '<th style="'.$estilo.'"> '.$value["seriecomprobante"].'-'.$value["nrocomprobante"].' </th>';
					$html .= '<th style="'.$estilo.'"> '.$value["concepto"].' </th>';
					$html .= '<th style="'.$estilo.'"> '.$value["seriecomprobante_ref"].'-'.$value["nrocomprobante_ref"].' </th>';
					$html .= '<th style="'.$estilo.'"> '.$value["razonsocial"].' </th>';
					if ($value["tipomovimiento"]==1) {
						$html .= '<th style="'.$estilo.'"> INGRESO </th>';
					}else{
						$html .= '<th style="'.$estilo.'"> EGRESO </th>';
					}
					$html .= '<th style="'.$estilo.'"> S/. '.$value["importe_r"].' </th>';
				$html .= '</tr>';
			}
		$html .= '</table>';
		
		$this->pdf_imprimir($html,"MOVIMIENTOS DE CAJA","movimientos.pdf");
	}

	function pdf_arqueo_caja($codcontroldiario){
		$estilo = "border-top:1px solid #D5D8DC; border-left:1px solid #D5D8DC; border-right:1px solid #D5D8DC;";

		$sesion = $this->db->query("select *from caja.controldiario where codcontroldiario=".$codcontroldiario)->result_array();
		$html = $this->pdf_cabecera("ARQUEO DE CAJA","CAJA NUMERO 000".$sesion[0]["codcontroldiario"]." - FECHA: ".$sesion[0]["fechaapertura"]);

		$tipopagos = $this->db->query("select *from caja.tipopagos where estado=1 order by codtipopago")->result_array();
		$caja = $this->db->query("select *from caja.controldiario where codcontroldiario=".$codcontroldiario)->result_array();

		$html .= '<table cellpadding="4" width="100%" style="border:1px solid #D5D8DC;font-size:9px;">';
			$html .= '<tr>';
				$html .= '<th style="'.$estilo.' width:30%;"> <b>SALDO INICIAL</b> </th>';
				$html .= '<th style="'.$estilo.' width:20%;"> <b>EN CAJA:</b> </th>';
				$html .= '<th style="'.$estilo.' width:15%;"> <b>S/. '.round($caja[0]["saldoinicialcaja"],2).'</b> </th>';
				$html .= '<th style="'.$estilo.' width:15%;"> <b>EN BANCO:</b> </th>';
				$html .= '<th style="'.$estilo.' width:20%;"> <b>S/. '.round($caja[0]["saldoinicialbanco"],2).'</b> </th>';
			$html .= '</tr>';

			$html .= '<tr>';
				$html .= '<th style="'.$estilo.' width:16%;" rows="2"> <b>FORMA DE PAGO</b> </th>';
				$html .= '<th style="'.$estilo.' width:15%;" rows="2"> <b>TRANSACCIONES</b> </th>';
				$html .= '<th style="'.$estilo.' width:27%;" colspan="3"> <b>INGRESOS</b> </th>';
				$html .= '<th style="'.$estilo.' width:27%;" colspan="3"> <b>EGRESOS</b> </th>';
				$html .= '<th style="'.$estilo.' width:15%;" rows="2"> <b>S/. TOTAL</b> </th>';
			$html .= '</tr>';
			$html .= '<tr>';
				$html .= '<th style="'.$estilo.'"></th>';
				$html .= '<th style="'.$estilo.'"></th>';
				$html .= '<th style="'.$estilo.' font-size:8px !important;">COBRADOS</th>';
				$html .= '<th style="'.$estilo.' font-size:8px !important;">PENDIENTES</th>';
				$html .= '<th style="'.$estilo.' font-size:8px !important;">TOTAL</th>';
				$html .= '<th style="'.$estilo.' font-size:8px !important;">COBRADOS</th>';
				$html .= '<th style="'.$estilo.' font-size:8px !important;">PENDIENTES</th>';
				$html .= '<th style="'.$estilo.' font-size:8px !important;">TOTAL</th>';
			$html .= '</tr>';

			$transacciones = 0; $ingresos = 0; $egresos = 0; $utilidad = 0; $saldocaja = 0;
			$ingresosconfirmados = 0; $ingresospendientes = 0; $egresosconfirmados = 0; $egresospendientes = 0;
			foreach ($tipopagos as $key => $val) {
				$total = $this->Caja_model->phuyu_saldotipopago($codcontroldiario,$val["codtipopago"]);

				if ($val["codtipopago"]==1) {
					$saldocaja = $total["ingresos"] - $total["egresos"];
				}

				$transacciones = $transacciones + $total["transacciones"];
				$ingresos = $ingresos + $total["ingresos"];
				$ingresosconfirmados = $ingresosconfirmados + $total["ingresosconfirmados"];
				$ingresospendientes = $ingresospendientes + $total["ingresospendientes"];
				$egresos = $egresos + $total["egresos"];
				$egresosconfirmados = $egresosconfirmados + $total["egresosconfirmados"];
				$egresospendientes = $egresospendientes + $total["egresospendientes"];
				$utilidad = $utilidad + ($total["ingresos"] - $total["egresos"]);

				$html .= '<tr>';
					$html .= '<th style="'.$estilo.'"> <b>'.$val["descripcion"].'</b> </th>';
					$html .= '<th style="'.$estilo.'"> '.$total["transacciones"].' </th>';
					$html .= '<th style="'.$estilo.' text-align:right"> '.$total["ingresosconfirmados"].' </th>';
					$html .= '<th style="'.$estilo.' text-align:right"> '.$total["ingresospendientes"].' </th>';
					$html .= '<th style="'.$estilo.' text-align:right"> '.$total["ingresos"].' </th>';
					$html .= '<th style="'.$estilo.' text-align:right"> '.$total["egresosconfirmados"].' </th>';
					$html .= '<th style="'.$estilo.' text-align:right"> '.$total["egresospendientes"].' </th>';
					$html .= '<th style="'.$estilo.' text-align:right"> '.$total["egresos"].' </th>';
					$html .= '<th style="'.$estilo.' text-align:right"> <b>S/. '.($total["ingresos"] - $total["egresos"]).'</b> </th>';
				$html .= '</tr>';
			}
			$html .= '<tr>';
				$html .= '<th style="'.$estilo.'"> <b>TOTALES</b> </th>';
				$html .= '<th style="'.$estilo.'"> '.$transacciones.' </th>';
				$html .= '<th style="'.$estilo.' text-align:right"> '.$ingresosconfirmados.' </th>';
				$html .= '<th style="'.$estilo.' text-align:right"> '.$ingresospendientes.' </th>';
				$html .= '<th style="'.$estilo.' text-align:right"> '.$ingresos.' </th>';
				$html .= '<th style="'.$estilo.' text-align:right"> '.$egresosconfirmados.' </th>';
				$html .= '<th style="'.$estilo.' text-align:right"> '.$egresospendientes.' </th>';
				$html .= '<th style="'.$estilo.' text-align:right"> '.$egresos.' </th>';
				$html .= '<th style="'.$estilo.' text-align:right"> <b>S/. '.$utilidad.'</b> </th>';
			$html .= '</tr>';
		$html .= '</table>';

		$otros = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcontroldiario=".$codcontroldiario." and m.codkardex=0 and m.tipomovimiento=1 and m.estado=1")->result_array();

		$html .= '<h6></h6> <table cellpadding="4" width="100%" style="border:1px solid #D5D8DC;font-size:11px;">';
			$html .= '<tr>';
				$html .= '<th style="'.$estilo.' width:70%;"> <b>OTROS INGRESOS</b> </th>';
				$html .= '<th style="'.$estilo.' width:30%;"> <b>'.number_format($otros[0]["importe"],2).'</b> </th>';
			$html .= '</tr>';

			$venta = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcontroldiario=".$codcontroldiario." and m.codkardex>0 and m.tipomovimiento=1 and m.estado=1")->result_array();
			$html .= '<tr>';
				$html .= '<th style="'.$estilo.' width:70%;"> <b>INGRESOS POR VENTAS</b> </th>';
				$html .= '<th style="'.$estilo.' width:30%;"> <b>'.number_format($venta[0]["importe"],2).'</b> </th>';
			$html .= '</tr>';

			$egresos = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcontroldiario=".$codcontroldiario." and m.tipomovimiento=2 and m.estado=1")->result_array();
			$html .= '<tr>';
				$html .= '<th style="'.$estilo.' width:70%;"> <b>TOTAL EGRESOS</b> </th>';
				$html .= '<th style="'.$estilo.' width:30%;"> <b>'.number_format($egresos[0]["importe"],2).'</b> </th>';
			$html .= '</tr>';
			$html .= '<tr>';
				$html .= '<th style="'.$estilo.' width:70%;"> <b>SALDO TOTAL</b> </th>';
				$html .= '<th style="'.$estilo.' width:30%;"> <b>'.number_format($otros[0]["importe"] + $venta[0]["importe"] - $egresos[0]["importe"],2).'</b> </th>';
			$html .= '</tr>';
		$html .= '</table>';

		$html .= '<br> <h3 align="center" style="color:red;">TOTAL EN CAJA EFECTIVO (CAJA + SALDO INICIAL): S/. '.number_format( ($saldocaja + $caja[0]["saldoinicialcaja"]),2).' </h3>';

		$html .= '<br> <h4 align="center">OPERACIONES REALIZADAS (CAJA APERTURADA N° 000'.$codcontroldiario.') (FECHA APERTURADA: '.$sesion[0]["fechaapertura"].')</h4> <hr> <h6></h6>';

		$lista = $this->db->query("select movimientos.*, round(movimientos.importe,2) as importe_r, personas.razonsocial,conceptos.descripcion as concepto from caja.movimientos as movimientos inner join public.personas as personas on(movimientos.codpersona=personas.codpersona) inner join caja.conceptos as conceptos on(movimientos.codconcepto=conceptos.codconcepto) where movimientos.codcontroldiario=".$codcontroldiario." and movimientos.tipomovimiento=1 and movimientos.condicionpago=1 and movimientos.estado=1 order by movimientos.codmovimiento asc")->result_array();

		$html .= '<h4 align="center">LISTA DE INGRESOS</h4>';
		$html .= '<table cellpadding="4" width="100%" style="border:1px solid #D5D8DC;font-size:8px;">';
			$html .= '<tr>';
				$html .= '<th style="'.$estilo.' width:8%;"> <b>FECHA</b> </th>';
				$html .= '<th style="'.$estilo.' width:11%;"> <b>N° RECIBO</b> </th>';
				$html .= '<th style="'.$estilo.' width:10%;"> <b>CONCEPTO CAJA</b> </th>';
				$html .= '<th style="'.$estilo.' width:10%;"> <b>DOC. REF.</b> </th>';
				$html .= '<th style="'.$estilo.' width:20%;"> <b>RAZÓN SOCIAL</b> </th>';
				$html .= '<th style="'.$estilo.' width:14%;"> <b>REFERENCIA</b> </th>';
				$html .= '<th style="'.$estilo.' width:9%;"> <b>PENDIENTE</b> </th>';
				$html .= '<th style="'.$estilo.' width:8%;"> <b>COBRADO</b> </th>';
				$html .= '<th style="'.$estilo.' width:10%;"> <b>S/. TOTAL</b> </th>';
			$html .= '</tr>';

			$ingresos = 0; $tcobrado = 0; $tpendiente = 0;
			foreach ($lista as $value) { 
				$ingresos = $ingresos + $value["importe_r"];
				$cobrado = 0; $pendiente = 0; $total = 0;
				if($value["cobrado"]==0){
					$pendiente = $value["importe_r"];
				}else{
					$cobrado = $value["importe_r"];
				}
				$total = $pendiente + $cobrado;
				$tpendiente = $tpendiente + $pendiente;
				$tcobrado = $tcobrado + $cobrado;
				$html .= '<tr>';
					$html .= '<th style="'.$estilo.'"> '.$value["fechamovimiento"].' </th>';
					$html .= '<th style="'.$estilo.'"> '.$value["seriecomprobante"].'-'.$value["nrocomprobante"].' </th>';
					$html .= '<th style="'.$estilo.'"> '.$value["concepto"].' </th>';
					$html .= '<th style="'.$estilo.'"> '.$value["seriecomprobante_ref"].'-'.$value["nrocomprobante_ref"].' </th>';
					$html .= '<th style="'.$estilo.'"> '.$value["razonsocial"].' </th>';
					$html .= '<th style="'.$estilo.'"> '.$value["referencia"].' </th>';
					$html .= '<th style="'.$estilo.' text-align:right"> S/. '.$pendiente.' </th>';
					$html .= '<th style="'.$estilo.' text-align:right"> S/. '.$cobrado.' </th>';
					$html .= '<th style="'.$estilo.' text-align:right"> S/. '.$total.' </th>';
				$html .= '</tr>';
			}
			$html .= '<tr>';
				$html .= '<th style="'.$estilo.'" colspan="6">TOTAL INGRESOS</th>';
				$html .= '<th style="'.$estilo.' text-align:right"> S/. '.number_format($tpendiente,2).'</th>';
				$html .= '<th style="'.$estilo.' text-align:right"> S/. '.number_format($tcobrado,2).'</th>';
				$html .= '<th style="'.$estilo.' text-align:right"> S/. '.number_format($ingresos,2).'</th>';
			$html .= '</tr>';
		$html .= '</table>';

		$lista = $this->db->query("select movimientos.*, round(movimientos.importe,2) as importe_r, personas.razonsocial,conceptos.descripcion as concepto from caja.movimientos as movimientos inner join public.personas as personas on(movimientos.codpersona=personas.codpersona) inner join caja.conceptos as conceptos on(movimientos.codconcepto=conceptos.codconcepto) where movimientos.codcontroldiario=".$codcontroldiario." and movimientos.tipomovimiento=2 and movimientos.condicionpago=1 and movimientos.estado=1 order by movimientos.codmovimiento asc")->result_array();

		$html .= '<br> <h4 align="center">LISTA DE EGRESOS</h4>';
		$html .= '<table cellpadding="4" width="100%" style="border:1px solid #D5D8DC;font-size:8px;">';
			$html .= '<tr>';
				$html .= '<th style="'.$estilo.' width:8%;"> <b>FECHA</b> </th>';
				$html .= '<th style="'.$estilo.' width:11%;"> <b>N° RECIBO</b> </th>';
				$html .= '<th style="'.$estilo.' width:10%;"> <b>CONCEPTO CAJA</b> </th>';
				$html .= '<th style="'.$estilo.' width:10%;"> <b>DOC. REF.</b> </th>';
				$html .= '<th style="'.$estilo.' width:20%;"> <b>RAZÓN SOCIAL</b> </th>';
				$html .= '<th style="'.$estilo.' width:14%;"> <b>REFERENCIA</b> </th>';
				$html .= '<th style="'.$estilo.' width:9%;"> <b>PENDIENTE</b> </th>';
				$html .= '<th style="'.$estilo.' width:8%;"> <b>COBRADO</b> </th>';
				$html .= '<th style="'.$estilo.' width:10%;"> <b>S/. TOTAL</b> </th>';
			$html .= '</tr>';

			$egresos = 0; $tcobradoe = 0; $tpendientee = 0;
			foreach ($lista as $value) { 
				$egresos = $egresos + $value["importe_r"];
				$cobrado = 0; $pendiente = 0; $total = 0;
				if($value["cobrado"]==0){
					$pendiente = $value["importe_r"];
				}else{
					$cobrado = $value["importe_r"];
				}
				$total = $pendiente + $cobrado;
				$tpendientee = $tpendientee + $pendiente;
				$tcobradoe = $tcobradoe + $cobrado;

				$html .= '<tr>';
					$html .= '<th style="'.$estilo.'"> '.$value["fechamovimiento"].' </th>';
					$html .= '<th style="'.$estilo.'"> '.$value["seriecomprobante"].'-'.$value["nrocomprobante"].' </th>';
					$html .= '<th style="'.$estilo.'"> '.$value["concepto"].' </th>';
					$html .= '<th style="'.$estilo.'"> '.$value["seriecomprobante_ref"].'-'.$value["nrocomprobante_ref"].' </th>';
					$html .= '<th style="'.$estilo.'"> '.$value["razonsocial"].' </th>';
					$html .= '<th style="'.$estilo.'"> '.$value["referencia"].' </th>';
					$html .= '<th style="'.$estilo.' text-align:right"> S/. '.$pendiente.' </th>';
					$html .= '<th style="'.$estilo.' text-align:right"> S/. '.$cobrado.' </th>';
					$html .= '<th style="'.$estilo.' text-align:right"> S/. '.$total.' </th>';
				$html .= '</tr>';
			}
			$html .= '<tr>';
				$html .= '<th style="'.$estilo.'" colspan="6">TOTAL EGRESOS</th>';
				$html .= '<th style="'.$estilo.' text-align:right"> S/. '.number_format($tpendientee,2).'</th>';
				$html .= '<th style="'.$estilo.' text-align:right"> S/. '.number_format($tcobradoe,2).'</th>';
				$html .= '<th style="'.$estilo.'"> S/. '.number_format($egresos,2).'</th>';
			$html .= '</tr>';
		$html .= '</table>';

		$this->pdf_imprimir($html,"ARQUEO DE CAJA","arqueo.pdf");
	}

	function pdf_arqueo_excel($codcontroldiario){
		if ($codcontroldiario) {
			$ingresos = $this->db->query("select movimientos.*, round(movimientos.importe,2) as importe_r, personas.razonsocial,conceptos.descripcion as concepto from caja.movimientos as movimientos inner join public.personas as personas on(movimientos.codpersona=personas.codpersona) inner join caja.conceptos as conceptos on(movimientos.codconcepto=conceptos.codconcepto) where movimientos.codcontroldiario=".$codcontroldiario." and movimientos.tipomovimiento=1 and movimientos.condicionpago=1 and movimientos.estado=1 order by movimientos.codmovimiento desc")->result_array();
			$egresos = $this->db->query("select movimientos.*, round(movimientos.importe,2) as importe_r, personas.razonsocial,conceptos.descripcion as concepto from caja.movimientos as movimientos inner join public.personas as personas on(movimientos.codpersona=personas.codpersona) inner join caja.conceptos as conceptos on(movimientos.codconcepto=conceptos.codconcepto) where movimientos.codcontroldiario=".$codcontroldiario." and movimientos.tipomovimiento=2 and movimientos.condicionpago=1 and movimientos.estado=1 order by movimientos.codmovimiento desc")->result_array();
			$this->load->view("caja/controlcajas/excel", compact("ingresos", "egresos"));
		}
	}

	// FUNCIONES EXTRAS DE CAJA //

	function actualizar_caja(){
		$movimientos = $this->db->query("select codmovimiento,importe from caja.movimientos order by codmovimiento")->result_array();
		foreach ($movimientos as $key => $value) {
			$pago = $this->db->query("select importeentregado from caja.movimientosdetalle where codmovimiento=".$value["codmovimiento"])->result_array();

			if (count($pago)>0) {
				$campos = ["importe","vuelto"];
				$valores = [
					(double)($value["importe"]),
					(double)($pago[0]["importeentregado"] - $value["importe"]),
				];
				$estado = $this->phuyu_model->phuyu_editar("caja.movimientosdetalle", $campos, $valores, "codmovimiento", $value["codmovimiento"]);
			}else{
				$estado = 1;
			}
		}
		echo $estado;
	}

	function actualizar_controldiario(){
		$control = $this->db->query("select * from caja.controldiario order by codcontroldiario")->result_array();
		foreach ($control as $key => $value) {
			$codcontroldiario = $value["codcontroldiario"];

			$controlanterior = $this->db->query("select COALESCE(max(codcontroldiario),0) as codcontroldiario from caja.controldiario where codcaja=".$value["codcaja"]." and codcontroldiario<".$value["codcontroldiario"])->result_array();

			$inicial = $this->db->query("select * from caja.controldiario where codcontroldiario=".$controlanterior[0]["codcontroldiario"])->result_array();

			if (count($inicial)>0) {
				$inicialcaja = $inicial[0]["saldoinicialcaja"];
				$inicialbanco = $inicial[0]["saldoinicialbanco"];
				$finalcaja = $inicial[0]["saldofinalcaja"];
				$finalbanco = $inicial[0]["saldofinalbanco"];
			}else{
				$inicialcaja = 0;
				$inicialbanco = 0;
				$finalcaja = 0;
				$finalbanco = 0;
			}

			$ingresos_caja = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcontroldiario=".$codcontroldiario." and m.tipomovimiento=1 and (md.codtipopago=1 or md.codtipopago=2) and m.estado=1")->result_array();
			$egresos_caja = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcontroldiario=".$codcontroldiario." and m.tipomovimiento=2 and (md.codtipopago=1 or md.codtipopago=3) and m.estado=1")->result_array();

			$ingresos_banco = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcontroldiario=".$codcontroldiario." and m.tipomovimiento=1 and (md.codtipopago<>1 and md.codtipopago<>2) and m.estado=1")->result_array();
			$egresos_banco = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcontroldiario=".$codcontroldiario." and m.tipomovimiento=2 and (md.codtipopago<>1 and md.codtipopago<>3) and m.estado=1")->result_array();

			echo 
				"CONTROL DE CAJA: ".$value["codcontroldiario"].
				"<br> SALDO INICIAL CAJA: ".($inicialcaja + $finalcaja).
				"<br> SALDO FINAL CAJA: ".($ingresos_caja[0]["importe"] - $egresos_caja[0]["importe"]).
				"<br> TOTAL INGRESOS CAJA: ".($ingresos_caja[0]["importe"]).
				"<br> TOTAL EGRESOS CAJA: ".($egresos_caja[0]["importe"]).

				"<br> SALDO INICIAL BANCO: ".($inicialbanco + $finalbanco).
				"<br> SALDO FINAL BANCO: ".($ingresos_banco[0]["importe"] - $egresos_banco[0]["importe"]).
				"<br> TOTAL INGRESOS BANCO: ".($ingresos_banco[0]["importe"]).
				"<br> TOTAL EGRESOS BANCO: ".($egresos_banco[0]["importe"]).
				"<br> <br>";

			$campos = ["saldoinicialcaja","saldofinalcaja","totalingresoscaja","totalegresoscaja","saldoinicialbanco","saldofinalbanco","totalingresosbanco","totalegresosbanco"];
			$valores = [
				(double)($inicialcaja + $finalcaja),(double)($ingresos_caja[0]["importe"] - $egresos_caja[0]["importe"]),(double)$ingresos_caja[0]["importe"],(double)$egresos_caja[0]["importe"],
				(double)($inicialbanco + $finalbanco),(double)($ingresos_banco[0]["importe"] - $egresos_banco[0]["importe"]),(double)$ingresos_banco[0]["importe"],(double)$egresos_banco[0]["importe"]
			];
			$estado = $this->phuyu_model->phuyu_editar("caja.controldiario", $campos, $valores, "codcontroldiario", $value["codcontroldiario"]);

		}
	}

	function generar_cajadiario(){
		$cajas = $this->db->query("select codcaja from caja.cajas where codcaja=2 or codcaja=7")->result_array();

		$controles = $this->db->query("select codcontroldiario,codusuario,codcaja,fechaapertura from caja.controldiario where codcaja=2 or codcaja=7")->result_array();

		foreach ($controles as $key => $value) {
			$campos = ["codusuariocierre","fechacierre","cerrado"];
			$valores = [
				(int)$value["codusuario"],$value["fechaapertura"],0
			];
			$estado = $this->phuyu_model->phuyu_editar("caja.controldiario", $campos, $valores, "codcontroldiario", $value["codcontroldiario"]);
		}

		foreach ($cajas as $v) {
			echo "caja ".$v["codcaja"]."<br>";
			$movimientos = $this->db->query("select distinct(fechamovimiento) from caja.movimientos where codcaja=".$v["codcaja"]." order by fechamovimiento")->result_array();
			if (count($movimientos)>1) {
				foreach ($movimientos as $key => $value) {
					echo $value["fechamovimiento"]."<br>";
					$controldiario = $this->db->query("select *from caja.controldiario where fechaapertura='".$value["fechamovimiento"]."' and codcaja=".$v["codcaja"])->result_array();

					if (count($controldiario)==0) {
						$codigo = explode("-",$value["fechamovimiento"]);
						$codigodiario = $codigo[2].$codigo[1].$codigo[0];

						$info = $this->db->query("select *from caja.controldiario where codcaja=".$v["codcaja"]." limit 1")->result_array();

						$campos = ["codcaja","codusuario","codusuariocierre","codsucursal","fechaapertura","fechacierre","codigodiario","cerrado"];
						$valores = [
							(int)$info[0]["codcaja"],
							(int)$info[0]["codusuario"],(int)$info[0]["codusuario"],
							(int)$info[0]["codsucursal"],
							$value["fechamovimiento"],$value["fechamovimiento"],$codigodiario,0
						];
						$codcontroldiario = $this->phuyu_model->phuyu_guardar("caja.controldiario", $campos, $valores,"true");
						$codcaja = $info[0]["codcaja"];
					}else{
						$codcontroldiario = $controldiario[0]["codcontroldiario"];
						$codcaja = $controldiario[0]["codcaja"];
					}

					$data = array("codcontroldiario" => $codcontroldiario);
					$this->db->where("codcaja", $codcaja);
					$this->db->where("fechamovimiento", $value["fechamovimiento"]);
					$estado = $this->db->update("caja.movimientos",$data);
				}
			}
		}
	}

	function actualizar_movimientos(){
		$lista = $this->db->query("select *from caja.movimientos order by codmovimiento asc")->result_array();
		foreach ($lista as $key => $value) {

			if ($value["tipomovimiento"]==1) {
				$codcomprobantetipo = 1; $seriecomprobante = "RI01";
			}else{
				$codcomprobantetipo = 2; $seriecomprobante = "RE01";
			}

			$actual = $this->db->query("select nrocorrelativo from caja.comprobantes where codcomprobantetipo=".$codcomprobantetipo." and seriecomprobante='".$seriecomprobante."' and codsucursal=".$_SESSION["phuyu_codsucursal"]." and estado=1")->result_array();

			$nrocorrelativo = (int)($actual[0]["nrocorrelativo"]) + 1;
			$data = array(
				"nrocorrelativo" => $nrocorrelativo
			);
			$this->db->where("codsucursal", $_SESSION["phuyu_codsucursal"]);
			$this->db->where("codcomprobantetipo", $codcomprobantetipo);
			$this->db->where("seriecomprobante", $seriecomprobante);
			$estado = $this->db->update("caja.comprobantes", $data);

			$nrocorrelativo = str_pad($nrocorrelativo, 8, "0", STR_PAD_LEFT);

			$data = array(
				"codcomprobantetipo" => $codcomprobantetipo,
				"seriecomprobante" => $seriecomprobante,
				"nrocomprobante" => $nrocorrelativo
			);
			$this->db->where("codmovimiento", $value["codmovimiento"]);
			$estado = $this->db->update("caja.movimientos",$data);
		}
		echo $estado;
	}

	function actualizar_creditos(){
		$lista = $this->db->query("select *from kardex.creditos")->result_array();
		foreach ($lista as $key => $value) {
			if ($value["tipo"]==1) {
				$tipomovimiento = 2;
			}else{
				$tipomovimiento = 1;
			}

			$data = array(
				"tipomovimiento" => $tipomovimiento
			);
			$this->db->where("codmovimiento", $value["codmovimiento"]);
			$estado = $this->db->update("caja.movimientos",$data);
		}
		echo $estado;
	}

	function actualizar_fechas(){
		$lista = $this->db->query("select kardex.fechakardex,kardex.codkardex from kardex.kardex")->result_array();
		foreach ($lista as $key => $value) {
			$data = array(
				"fechacomprobante" => $value["fechakardex"]
			);
			$this->db->where("codkardex", $value["codkardex"]);
			$estado = $this->db->update("kardex.kardex",$data);
		}
		echo $estado;
	}
}