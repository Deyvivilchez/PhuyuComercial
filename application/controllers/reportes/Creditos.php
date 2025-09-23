<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Creditos extends CI_Controller {

	public function __construct(){
		parent::__construct(); 
		$this->load->model("phuyu_model"); 
		$this->load->model("Creditos_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			$this->load->view("reportes/creditos/index");
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function ver_creditos(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$this->request->codlote = (!isset($this->request->codlote) || empty($this->request->codlote)) ? 0 : $this->request->codlote;
			if ($this->request->saldos == 0) {
				if($this->request->codpersona == 0){
					$socios = $this->Creditos_model->socios_creditos($this->request->fecha_desde,$this->request->fecha_hasta,$this->request->tipo);
				}else{
					$socios = $this->db->query("select codpersona,razonsocial,documento,direccion,telefono from personas where codpersona=".$this->request->codpersona)->result_array();
				}

				if($this->request->tipo_consulta == 1){
					if ($this->request->mostrar==1) {
						foreach ($socios as $key => $value) {
							$anterior = $this->Creditos_model->estado_cuenta_anterior($this->request->fecha_desde,$this->request->tipo,$value["codpersona"],$this->request->estado);
							$movimientos = $this->Creditos_model->estado_cuenta_cliente($this->request->fecha_desde,$this->request->fecha_hasta,$this->request->tipo,$value["codpersona"],$this->request->codlote,$this->request->estado);
							$importeanterior = $anterior["totalimporte"];
							$interesanterior = $anterior["totalinteres"];
							$totalanterior = $anterior["totaltotal"];
							$pagadoanterior = $anterior["totalpagado"];
							$saldo = $anterior["saldo"]; 
							$abono = 0; 
							$cargo = 0;
							$total_interes=0;
							$cargototal = 0;
							foreach ($movimientos as $k => $v) {
								//$saldo = $saldo + $v["cargo"] - $v["abono"]+;
								$saldo = $saldo + $v["cargo"] - $v["abono"]+$v["interes"];
								$movimientos[$k]["saldo"] = number_format($saldo,2);
								$cargo = $cargo + $v["cargo"];
								$abono = $abono + $v["abono"];
								$cargototal = $cargototal + ((double)$v["cargo"]+(double)$v["interes"]);
								$movimientos[$k]["cargototal"] = (double)$v["cargo"]+(double)$v["interes"];
								$total_interes = $total_interes + $v["interes"]; 
							}
							$socios[$key]["importeanterior"] = number_format($importeanterior,2,".","");
							$socios[$key]["interesanterior"] = number_format($interesanterior,2,".","");
							$socios[$key]["totalanterior"] = number_format($totalanterior,2,".","");
							$socios[$key]["pagadoanterior"] = number_format($pagadoanterior,2,".","");
							$socios[$key]["anterior"] = number_format($anterior["saldo"],2);
							$socios[$key]["movimientos"] = $movimientos;
							$socios[$key]["cargo"] = number_format(($cargo+$socios[$key]["importeanterior"]),2);
							$socios[$key]["abono"] = number_format(($abono+$socios[$key]["pagadoanterior"]),2);
							$socios[$key]["cargototal"] = number_format(($cargototal+$socios[$key]["totalanterior"]),2);
							$socios[$key]["total_interes"] = number_format(($total_interes+$socios[$key]["interesanterior"]),2);
							//$socios[$key]["saldo"] = number_format($anterior + $cargo - $abono,2);
							$socios[$key]["saldo"]= number_format($saldo,2);
						}
					}else{
						foreach ($socios as $key => $value) {
							$creditos = $this->Creditos_model->estado_cuenta_creditos($this->request->fecha_desde,$this->request->fecha_hasta,$this->request->tipo,$value["codpersona"],$this->request->estado);
							$socios[$key]["creditos"] = $creditos;
						}
					}
				}
				elseif($this->request->tipo_consulta == 2) {

					foreach ($socios as $key => $value) {
						$anterior = $this->Creditos_model->estado_cuenta_anterior($this->request->fecha_desde,$this->request->tipo,$value["codpersona"],$this->request->estado);
						$movimientos = $this->Creditos_model->estado_cuenta_detallado($this->request->fecha_desde,$this->request->fecha_hasta,
						$this->request->tipo,$value["codpersona"],$this->request->codlote,$this->request->estado);

						$importeanterior = $anterior["totalimporte"];
						$interesanterior = $anterior["totalinteres"];
						$totalanterior = $anterior["totaltotal"];
						$pagadoanterior = $anterior["totalpagado"];
						$saldo = $anterior["saldo"]; 
						$abono = 0; 
						$cargo = 0;
						$total_interes=0;
						$cargototal = 0;
						foreach ($movimientos as $k => $v) {
							//$saldo = $saldo + $v["cargo"] - $v["abono"]+;
							$saldo = $saldo + $v["cargo"] - $v["abono"]+$v["interes"];
							$movimientos[$k]["saldo"] = number_format($saldo,2);
							$cargo = $cargo + $v["cargo"];
							$abono = $abono + $v["abono"];
							$cargototal = $cargototal + ((double)$v["cargo"]+(double)$v["interes"]);
							$movimientos[$k]["cargototal"] = (double)$v["cargo"]+(double)$v["interes"];
							$total_interes = $total_interes + $v["interes"]; 
						}
						$socios[$key]["importeanterior"] = number_format($importeanterior,2,".","");
						$socios[$key]["interesanterior"] = number_format($interesanterior,2,".","");
						$socios[$key]["totalanterior"] = number_format($totalanterior,2,".","");
						$socios[$key]["pagadoanterior"] = number_format($pagadoanterior,2,".","");
						$socios[$key]["anterior"] = number_format($anterior["saldo"],2);
						$socios[$key]["movimientos"] = $movimientos;
						$socios[$key]["cargo"] = number_format(($cargo+$socios[$key]["importeanterior"]),2);
						$socios[$key]["abono"] = number_format(($abono+$socios[$key]["pagadoanterior"]),2);
						$socios[$key]["cargototal"] = number_format(($cargototal+$socios[$key]["totalanterior"]),2);
						$socios[$key]["totalinteres"] = number_format(($total_interes+$socios[$key]["interesanterior"]),2);
						//$socios[$key]["saldo"] = number_format($anterior + $cargo - $abono,2);
						$socios[$key]["saldo"]= number_format($saldo,2);
					}
				}elseif($this->request->tipo_consulta == 3){
					if ($this->request->mostrar==1) {
						foreach ($socios as $key => $value) {
							$anterior = $this->Creditos_model->estado_cuenta_anterior($this->request->fecha_desde,$this->request->tipo,$value["codpersona"],$this->request->estado);

							$movimientos = $this->Creditos_model->estado_cuenta_cliente(
							$this->request->fecha_desde,
							$this->request->fecha_hasta,
							$this->request->tipo,
							$value["codpersona"],$this->request->codlote,$this->request->estado);
							$importeanterior = $anterior["totalimporte"];
							$interesanterior = $anterior["totalinteresactual"];
							$totalanterior = $anterior["totaltotalactual"];
							$pagadoanterior = $anterior["totalpagado"];
							$saldo = $anterior["saldoactual"]; 
							$abono = 0; 
							$cargo = 0;
							$totalinteresactual=0;
							$cargototal = 0;
							foreach ($movimientos as $k => $v) {
								$saldo = $saldo + $v["cargo"] +$v["interesactual"] - $v["abono"];
								$movimientos[$k]["saldo"] = number_format($saldo,2);
								$cargo = $cargo + $v["cargo"]; 
								$abono = $abono + $v["abono"];
								$movimientos[$k]["cargototal"] = number_format((double)$v["cargo"] + (double)$v["interesactual"],2,".","");
								$totalinteresactual=$totalinteresactual + $v["interesactual"];
								$cargototal = $cargototal + ((double)$v["cargo"] + (double)$v["interesactual"]);
							}
							$socios[$key]["importeanterior"] = number_format($importeanterior,2,".","");
							$socios[$key]["interesanterior"] = number_format($interesanterior,2,".","");
							$socios[$key]["totalanterior"] = number_format($totalanterior,2,".","");
							$socios[$key]["pagadoanterior"] = number_format($pagadoanterior,2,".","");
							$socios[$key]["anterior"] = number_format($anterior["saldoactual"],2);
							$socios[$key]["movimientos"] = $movimientos;
							$socios[$key]["cargo"] = number_format($cargo+$socios[$key]["importeanterior"],2);
							$socios[$key]["totalinteresactual"] = number_format($totalinteresactual+$socios[$key]["interesanterior"],2);
							$socios[$key]["abono"] = number_format($abono+$socios[$key]["pagadoanterior"],2);
							$socios[$key]["cargototal"] = number_format($cargototal+$socios[$key]["totalanterior"],2);
							//$socios[$key]["saldo"] = number_format($anterior + $cargo - $abono,2);
							$socios[$key]["saldoit"] = number_format($saldo,2);
						}
					}else{
						foreach ($socios as $key => $value) {
							$creditos = $this->Creditos_model->estado_cuenta_creditos($this->request->fecha_desde,$this->request->fecha_hasta,$this->request->tipo,$value["codpersona"],$this->request->estado);
							$socios[$key]["creditos"] = $creditos;
						}
					}
				}else{
					foreach ($socios as $key => $value) {
						$anterior = $this->Creditos_model->estado_cuenta_anterior($this->request->fecha_desde,$this->request->tipo,$value["codpersona"],$this->request->estado);
						$movimientos = $this->Creditos_model->estado_cuenta_detallado($this->request->fecha_desde,$this->request->fecha_hasta,
						$this->request->tipo,
						$value["codpersona"],$this->request->codlote,$this->request->estado);
						$importeanterior = $anterior["totalimporte"];
						$interesanterior = $anterior["totalinteresactual"];
						$totalanterior = $anterior["totaltotalactual"];
						$pagadoanterior = $anterior["totalpagado"];
						$saldo = $anterior["saldoactual"];  
						$abono = 0; 
						$cargo = 0;
						$totalinteresactual=0;
						$cargototal = 0;
						foreach ($movimientos as $k => $v) {
							$saldo = $saldo + $v["cargo"]+ $v["interesactual"] - $v["abono"];
							$movimientos[$k]["saldo"] = number_format($saldo,2);
							$cargo = $cargo + $v["cargo"]; 
							$abono = $abono + $v["abono"];
							$movimientos[$k]["cargototaldet"] = (double)$v["cargo"] + (double)$v["interesactual"];
							$totalinteresactual=$totalinteresactual + $v["interesactual"];
							$cargototal = $cargototal + ((double)$v["cargo"] + (double)$v["interesactual"]);
						}

						$socios[$key]["importeanterior"] = number_format($importeanterior,2,".","");
						$socios[$key]["interesanterior"] = number_format($interesanterior,2,".","");
						$socios[$key]["totalanterior"] = number_format($totalanterior,2,".","");
						$socios[$key]["pagadoanterior"] = number_format($pagadoanterior,2,".","");
						$socios[$key]["anterior"] = number_format($anterior["saldoactual"],2);
						$socios[$key]["movimientos"] = $movimientos;
						$socios[$key]["cargo"] = number_format($cargo+$socios[$key]["importeanterior"],2);
						$socios[$key]["totalinteresactual"] = number_format($totalinteresactual+$socios[$key]["interesanterior"],2);
						$socios[$key]["cargototal"] = number_format($cargototal+$socios[$key]["totalanterior"],2);
						$socios[$key]["abono"] = number_format($abono+$socios[$key]["pagadoanterior"],2);
						//$socios[$key]["saldo"] = number_format($anterior + $cargo - $abono,2);
						$socios[$key]["saldo"] = number_format($saldo,2);
					}
				}
			}else{
				if($this->request->codpersona == 0){
					$socios = $this->Creditos_model->socios_saldos($this->request->fecha_saldos,$this->request->tipo);
				}else{
					$socios = $this->db->query("select codpersona,razonsocial,documento,direccion,telefono from personas where codpersona=".$this->request->codpersona)->result_array();
				}

				foreach ($socios as $key => $value) {
					$creditos = $this->Creditos_model->phuyu_saldos($this->request->fecha_saldos,$this->request->tipo,$value["codpersona"]);

					$importe = 0; 
					$interes = 0;
					$total = 0;
					$saldo = 0;
					$totalinteresactual=0;
					$totalsaldoactual=0;
					$totalimportepagado=0;
					foreach ($creditos as $k => $v) {
						$hora_i_2 = new DateTime("now"); 
						$hora_s_2 = new DateTime($v["fechavencimiento"]);	
						$intervalo_2 = $hora_i_2->diff($hora_s_2);
						if(date("Y-m-d") < $v["fechavencimiento"]){
							$sum_dias = (int)$intervalo_2->days + 1;
							$color = "green"; $estado = "POR VENCER EN ".$sum_dias." DIA(S)";
						}else{
							$color = "red"; $estado = "VENCIDO HACE ".$intervalo_2->days." DIA(S)";
						}
						$creditos[$k]["color"] = $color;
						$creditos[$k]["estado"] = $estado;

						$totalinteresactual = $totalinteresactual + $v["interesactual"];
						
						$importe = $importe + $v["importe"]; 
						$interes = $interes + $v["interes"]; 
						$totalsaldoactual=$totalsaldoactual+$v["saldoactual"];
						$totalimportepagado=$totalimportepagado+$v["importepagado"];
						$total = $total + $v["total"]; 
						$saldo = $saldo + $v["saldo"];
						
						$importemastotalinteres=$importe+$totalinteresactual;	

						$socios[$key]["importe"] = number_format($importe,2);
						$socios[$key]["interes"] = number_format($interes,2);
						$socios[$key]["totalinteresactual"] = number_format($totalinteresactual,2);
						$socios[$key]["importemastotalinteres"]=number_format($importemastotalinteres,2);
						$socios[$key]["total"] = number_format($total,2);
						$socios[$key]["saldo"] = number_format($saldo,2);
						$socios[$key]["totalimportepagado"] = number_format($totalimportepagado,2);
						$socios[$key]["totalsaldoactual"] = number_format($totalsaldoactual,2);
					}
					$socios[$key]["creditos"] = $creditos;
				}
			}
			echo json_encode($socios);
		}
	}

	public function actualizar_interes(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			$socios = $this->db->query("select codpersona,razonsocial,documento,direccion,telefono from personas where codpersona=".$this->request->codpersona)->result_array();

			$this->db->trans_begin();

			$estado = 1;

			foreach ($socios as $key => $value) {
				$info = $this->db->query("select *FROM caja.v_creditos pun where pun.codpersona=".$value["codpersona"]." ")->result_array();

				foreach ($info as $key => $val) {

					$nrodiasactual = $this->getDifDays($this->request->fecha_hasta,$val["fechainicio"]);

					$interestotal = ($val["importecredito"]*($val["tasainteres"]/100)*($nrodiasactual/30));
					$totaltotalanterior = $val['total'];
					$saldototalanterior = $val['saldocredito'];
					$montototalpagado = (double)$totaltotalanterior - (double)$saldototalanterior;

					$totaltotalactual = $val["importecredito"] + $interestotal;
					$saldototalactual = (double)$totaltotalactual - (double)$montototalpagado;

					$campos = ["interes","saldo","total","nrodias"];
					$valores = [$interestotal,$saldototalactual,$totaltotalactual,$nrodiasactual];
					$f = ["codcredito"];
					$g = [$val["codcredito"]];
					$estado = $this->phuyu_model->phuyu_editar_1("kardex.creditos", $campos, $valores, $f, $g);

					$cuotas = $this->db->query("select *FROM kardex.cuotas pun where pun.codcredito=".$val["codcredito"]." ")->result_array();

					$fecha = $val["fechainicio"];

					foreach ($cuotas as $key => $v) {
						$fechavence = $this->sumarDiasNaturales($fecha,$nrodiasactual);
						$interes = ($v["importe"]*($val["tasainteres"]/100)*($nrodiasactual/30));
						$totalanterior = $v['total'];
						$saldoanterior = $v['saldo'];
						$montopagado = (double)$totalanterior - (double)$saldoanterior;

						$totalactual = $v["importe"] + $interes;
						$saldoactual = (double)$totalactual - (double)$montopagado;
						$campos = ["fechavence","interes","saldo","total"];
						$valores = [$fechavence,$interes,$saldoactual,$totalactual];
						$f = ["codcredito","nrocuota"];
						$g = [$val["codcredito"],$v["nrocuota"]];
						$estado = $this->phuyu_model->phuyu_editar_1("kardex.cuotas", $campos, $valores, $f, $g);
						$fecha = $fechavence;
					}
				}
			}

			if ($this->db->trans_status() === FALSE){
			    $this->db->trans_rollback(); $estado = 0;
			}else{
				if ($estado!=1) { 
					$this->db->trans_rollback(); $estado = 0; 
				}
				$this->db->trans_commit();
			}

			echo $estado;

		}
	}

	public static function getDifDays($fecha1, $fecha2)
    {
        list($anio1, $mes1, $dia1) = explode('-', $fecha1);
        list($anio2, $mes2, $dia2) = explode('-', $fecha2);
        /* calculo timestam de las dos fechas */
        $timestamp1 = mktime(0, 0, 0, $mes1, $dia1, $anio1);
        $timestamp2 = mktime(0, 0, 0, $mes2, $dia2, $anio2);
        /* resto a una fecha la otra */
        $segundos_diferencia = $timestamp1 - $timestamp2;
        /* convierto segundos en días */
        $dias_diferencia = $segundos_diferencia / (60 * 60 * 24);
        /* Obtenemos el valor absoluto de los días 
           (quito el posible signo negativo) */
        $dias_diferencia = abs($dias_diferencia);
        /* quito los decimales a los dí­as de diferencia  */
        $dias_diferencia = floor($dias_diferencia);
        return intval($dias_diferencia);
    }

    public static function sumarDiasNaturales($fecha, $dias)
    {
        if ($dias < 0) {
            return '';
        }
        $dateArray = explode("-", $fecha);
        $sd = $dias;
        while ($sd > 0) {
            if ($sd <= date("t", mktime(0, 0, 0,
                                        $dateArray[ 1 ],
                                        1,
                                        $dateArray[ 0 ])
                            ) - $dateArray[ 2 ]) {
                $dateArray[ 2 ] = $dateArray[ 2 ] + $sd;
                $sd = 0;
            } else {
                $sd  = $sd - ( date( "t", mktime(0, 0, 0,
                                                $dateArray[ 1 ],
                                                1,
                                                $dateArray[ 0 ])
                                   ) - $dateArray[ 2 ]);
                $dateArray[ 2 ] = 0;
                if ($dateArray[ 1 ] < 12) {
                    $dateArray[ 1 ]++;
                } else {
                    $dateArray[ 1 ] = 1;
                    $dateArray[ 0 ]++;
                }
            }
        }
        $sDia = '00'.$dateArray[ 2 ];
        $sDia = substr($sDia, -2);
        $sMes = '00'.$dateArray[ 1 ];
        $sMes = substr($sMes, -2);
        return $dateArray[ 0 ].'-'.$sMes.'-'.$sDia;
    }

	function pdf_creditos(){
		if ($_GET["datos"]) {
			$this->request = json_decode($_GET["datos"]);
			if ($this->request->tipo==1) {
				$tipo = "CREDITOS POR COBRAR"; $socio = "CLIENTE"; $tipo_texto = "COBRANZA";
			}else{
				$tipo = "CREDITOS POR PAGAR"; $socio = "PROVEEDOR"; $tipo_texto = "PAGO";
			}

			$this->request->codlote = (isset($this->request->codlote) || !empty($this->request->codlote)) ? $this->request->codlote : 0;

			$this->load->library('Pdf2'); 
			$pdf = new Pdf2(); 
			$pdf->AddPage();

			if ($this->request->saldos == 0) {
				if($this->request->codpersona == 0){
					$socios = $this->Creditos_model->socios_creditos($this->request->fecha_desde,$this->request->fecha_hasta,$this->request->tipo);
				}else{
					$socios = $this->db->query("select codpersona,razonsocial,documento,direccion,telefono from personas where codpersona=".$this->request->codpersona)->result_array();
				}

				if($this->request->tipo_consulta == 1){
					if ($this->request->mostrar==1) {
						$pdf->pdf_header("ESTADO DE CUENTA - ".$tipo,"ESTADO DE CUENTA POR CLIENTE DE ".$tipo." (DE:".$this->request->fecha_desde." A:".$this->request->fecha_hasta.")");
						$desde = explode("-", $this->request->fecha_desde); $hasta = explode("-", $this->request->fecha_hasta);
						$pdf->Cell(0,5,"REPORTE DESDE ".$desde[2]."-".$desde[1]."-".$desde[0]." HASTA ".$hasta[2]."-".$hasta[1]."-".$hasta[0],0,"C"); $pdf->ln(7);

						foreach ($socios as $key => $value) {
							$texto = $socio.": ".utf8_decode($value["razonsocial"])." | DIRECCION: ".utf8_decode($value["direccion"]);
							$pdf->SetFont('Arial','B',9);
							$pdf->Cell(190,6,substr($texto,0,95),1); $pdf->Ln();

							$columnas = array("FECHA","LINEA","COMPROBANTE","DESCRIPCION","CARGO","INTERES","T CARGO","ABONO","SALDO");
							$w = array(15,15,25,60,15,15,15,15,15); 
							$pdf->pdf_tabla_head($columnas,$w,8);

							$pdf->SetWidths(array(15,15,25,60,15,15,15,15,15));
				            $pdf->SetLineHeight(5); 
							$pdf->SetFont('Arial','',7);

				            $anterior = $this->Creditos_model->estado_cuenta_anterior($this->request->fecha_desde,$this->request->tipo,$value["codpersona"],$this->request->estado);
							$movimientos = $this->Creditos_model->estado_cuenta_cliente($this->request->fecha_desde,$this->request->fecha_hasta,$this->request->tipo,$value["codpersona"],$this->request->codlote,$this->request->estado);

							$pdf->Cell(115,5,"SALDO ANTERIOR HASTA ".$desde[2]."-".$desde[1]."-".$desde[0],1,0,'R');
						    $pdf->Cell(15,5,number_format($anterior["totalimporte"],2),1,"R");
							$pdf->Cell(15,5,number_format($anterior["totalinteres"],2),1,"R");
							$pdf->Cell(15,5,number_format($anterior["totaltotal"],2),1,"R");
							$pdf->Cell(15,5,number_format($anterior["totalpagado"],2),1,"R");
							$pdf->Cell(15,5,number_format($anterior["saldo"],2),1,"R"); $pdf->ln();

							$saldo = $anterior["saldo"]; $abono = 0; 
							$cargo = 0; $totalinterespdf=0;
							foreach ($movimientos as $k => $v) {
								$saldo = $saldo + $v["cargo"]+$v["interes"] - $v["abono"];
								$cargo = $cargo + $v["cargo"]; 
								$abono = $abono + $v["abono"];
								$totalinterespdf=$totalinterespdf+$v["interes"];

								$datos = array($v["fecha"]);
								array_push($datos,utf8_decode($v["linea"]));
								array_push($datos,utf8_decode($v["comprobante"]));
								array_push($datos,utf8_decode($v["referencia"]));
								array_push($datos,number_format($v["cargo"],2));
								array_push($datos,number_format($v["interes"],2));
								array_push($datos,number_format($v["cargo"]+$v["interes"],2));
								array_push($datos,number_format($v["abono"],2));
								array_push($datos,number_format($saldo,2));
				                $pdf->Row($datos);
							}
							$pdf->Cell(array_sum($w),0,'','T'); 
							$pdf->Ln();

							$pdf->SetFont('Arial','B',8);
							$pdf->Cell(115,5,"TOTALES",1,0,'R');
						    $pdf->Cell(15,5,number_format($cargo+$anterior["totalimporte"],2),1,"R");
							$pdf->Cell(15,5,number_format($totalinterespdf+$anterior["totalinteres"],2),1,"R");
						    $pdf->Cell(15,5,number_format($cargo+$totalinterespdf+$anterior["totaltotal"],2),1,"R");
						    $pdf->Cell(15,5,number_format($abono+$anterior["totalpagado"],2),1,"R");
						    $pdf->Cell(15,5,number_format($anterior["saldo"] + $cargo - $abono + $totalinterespdf,2),1,"R"); $pdf->Ln(); $pdf->Ln();
						}
					}else{
						$pdf->pdf_header("ESTADO DE CUENTA - ".$tipo,"ESTADO DE CUENTA POR CREDITO DE ".$tipo." (DE:".$this->request->fecha_desde." A:".$this->request->fecha_hasta.")");

						foreach ($socios as $key => $value) {
							$texto = $socio.": ".utf8_decode($value["razonsocial"])." | DIRECCION: ".utf8_decode($value["direccion"]);
							$pdf->SetFont('Arial','B',9);
							$pdf->Cell(190,6,substr($texto,0,95),1); $pdf->Ln();

							$columnas = array("FECHA","COMPROBANTE","DESCRIPCION","IMPORTE","INTERES","TOTAL",$tipo_texto,"SALDO");
							$w = array(20,25,65,15,15,15,20,15); $pdf->pdf_tabla_head($columnas,$w,8);

							$pdf->SetWidths(array(20,25,65,15,15,15,20,15));
				            $pdf->SetLineHeight(5); 
							$pdf->SetFont('Arial','',7);

							$creditos = $this->Creditos_model->estado_cuenta_creditos($this->request->fecha_desde,$this->request->fecha_hasta,$this->request->tipo,$value["codpersona"],$this->request->codlote,$this->request->estado);

							$importe = 0; $interes = 0; $total = 0; $cobranza = 0; $saldo = 0;
							foreach($creditos as $v){
								$datos = array($v["fecha"]);
								array_push($datos,utf8_decode($v["comprobante"]));
								array_push($datos,utf8_decode($v["referencia"]));

								array_push($datos,number_format($v["importe"],2));
								array_push($datos,number_format($v["interes"],2));
								array_push($datos,number_format($v["total"],2));
								array_push($datos,number_format($v["cobranza"],2));
								array_push($datos,number_format($v["saldo"],2));
				                $pdf->Row($datos);

				                $importe = $importe + $v["importe"]; 
				                $interes = $interes + $v["interes"]; 
				                $total = $total + $v["total"]; 
				                $cobranza = $cobranza + $v["cobranza"]; 
				                $saldo = $saldo + $v["saldo"];
							}
							$pdf->Cell(array_sum($w),0,'','T'); $pdf->Ln();

							$pdf->SetFont('Arial','B',8);
							$pdf->Cell(110,5,"TOTALES",1,0,'R');
						    $pdf->Cell(15,5,number_format($importe,2),1,"R");
						    $pdf->Cell(15,5,number_format($interes,2),1,"R");
						    $pdf->Cell(15,5,number_format($total,2),1,"R");
						    $pdf->Cell(20,5,number_format($cobranza,2),1,"R");
						    $pdf->Cell(15,5,number_format($saldo,2),1,"R"); $pdf->Ln(); $pdf->Ln();
						}
					}
				}else{
					$pdf->pdf_header("ESTADO DE CUENTA DETALLADO ".$tipo,"ESTADO DE CUENTA POR CREDITO DE ".$tipo." (DE:".$this->request->fecha_desde." A:".$this->request->fecha_hasta.")");
					$desde = explode("-", $this->request->fecha_desde); $hasta = explode("-", $this->request->fecha_hasta);
					$pdf->Cell(0,5,"REPORTE DESDE ".$desde[2]."-".$desde[1]."-".$desde[0]." HASTA ".$hasta[2]."-".$hasta[1]."-".$hasta[0],0,"C"); $pdf->ln(7);

					foreach ($socios as $key => $value) {
						$texto = $socio.": ".utf8_decode($value["razonsocial"])." | DIRECCION: ".utf8_decode($value["direccion"]);
						$pdf->SetFont('Arial','B',9);
						$pdf->Cell(192,6,substr($texto,0,95),1); $pdf->Ln();

						$columnas = array("FECHA","LINEA","COMPROB","DESCRIPCION","UND","CANT","P. UNIT","CARGO","INT","T CARGO","ABONO","SALDO");
						$w = array(15,10,21,40,13,13,13,13,13,15,13,13); $pdf->pdf_tabla_head($columnas,$w,8);

						$pdf->SetWidths(array(15,10,21,40,13,13,13,13,13,15,13,13));
			            $pdf->SetLineHeight(5); 
						$pdf->SetFont('Arial','',7);

			            $anterior = $this->Creditos_model->estado_cuenta_anterior($this->request->fecha_desde,$this->request->tipo,$value["codpersona"],$this->request->estado);
						$movimientos = $this->Creditos_model->estado_cuenta_detallado($this->request->fecha_desde,$this->request->fecha_hasta,$this->request->tipo,$value["codpersona"],$this->request->codlote,$this->request->estado);

						$pdf->Cell(125,5,"SALDO ANTERIOR HASTA ".$desde[2]."-".$desde[1]."-".$desde[0],1,0,'R');
						$pdf->Cell(13,5,number_format($anterior["totalimporte"],2),1,"R");
						$pdf->Cell(13,5,number_format($anterior["totalinteres"],2),1,"R");
						$pdf->Cell(15,5,number_format($anterior["totaltotal"],2),1,"R");
						$pdf->Cell(13,5,number_format($anterior["totalpagado"],2),1,"R");
						$pdf->Cell(13,5,number_format($anterior["saldo"],2),1,"R"); $pdf->ln();
						
						$saldo = $anterior["saldo"]; $abono = 0; $cargo = 0;
						$totalinterespdf=0;
						foreach ($movimientos as $k => $v) {
							$saldo = $saldo + $v["cargo"] - $v["abono"];
							$cargo = $cargo + $v["cargo"]; $abono = $abono + $v["abono"];
							$totalinterespdf=$totalinterespdf+$v["interes"];
							$datos = array($v["fechacomprobante"]);
							array_push($datos,utf8_decode($v["linea"])); 
							array_push($datos,utf8_decode($v["comprobante"])); 
							array_push($datos,utf8_decode($v["descripcion"]));
							array_push($datos,utf8_decode($v["unidad"])); 
							array_push($datos,number_format($v["cantidad"],2));
							array_push($datos,number_format($v["preciounitario"],2));
							array_push($datos,number_format($v["cargo"],2));
							array_push($datos,number_format($v["interes"],2));
							array_push($datos,number_format($v["cargo"] + $v["interes"],2));
							array_push($datos,number_format($v["abono"],2));
							
							array_push($datos,number_format($saldo + $totalinterespdf,2));
			                $pdf->Row($datos);
						}
						$pdf->Cell(array_sum($w),0,'','T'); $pdf->Ln();

						$pdf->SetFont('Arial','B',8);
						$pdf->Cell(125,5,"TOTALES",1,0,'R');
					    $pdf->Cell(13,5,number_format($cargo+$anterior["totalimporte"],2),1,"R");
						$pdf->Cell(13,5,number_format($totalinterespdf+$anterior["totalinteres"],2),1,"R");
						$pdf->Cell(15,5,number_format($cargo+$totalinterespdf+$anterior["totaltotal"],2),1,"R");
					    $pdf->Cell(13,5,number_format($abono+$anterior["totalpagado"],2),1,"R");
					    $pdf->Cell(13,5,number_format($anterior["saldo"] + $cargo - $abono + $totalinterespdf,2),1,"R"); $pdf->Ln(); $pdf->Ln();
					}
				}
			}else{
				if($this->request->codpersona == 0){
					$socios = $this->Creditos_model->socios_saldos($this->request->fecha_saldos,$this->request->tipo);
				}else{
					$socios = $this->db->query("select codpersona,razonsocial,documento,direccion,telefono from personas where codpersona=".$this->request->codpersona)->result_array();
				}

				$pdf->pdf_header("SALDOS DE ".$tipo." ".$this->request->fecha_saldos,"SALDOS");

				foreach ($socios as $key => $value) {
					$texto = $socio.": ".utf8_decode($value["razonsocial"])." | DIRECCION: ".utf8_decode($value["direccion"]);
					$pdf->SetFont('Arial','B',9);
					$pdf->Cell(190,6,substr($texto,0,95),1); $pdf->Ln();

					$columnas = array("COMPROBANTE","FECHA CREDITO","FECHA VENCE","ESTADO","IMPORTE","INTERES","TOTAL","SALDO");
					$w = array(25,25,25,55,15,15,15,15); $pdf->pdf_tabla_head($columnas,$w,8);

					$pdf->SetWidths(array(25,25,25,55,15,15,15,15));
		            $pdf->SetLineHeight(5); $pdf->SetFont('Arial','',7);

					$creditos = $this->Creditos_model->phuyu_saldos($this->request->fecha_saldos,$this->request->tipo,$value["codpersona"]);

					$importe = 0; 
					$interes = 0; 
					$total = 0; 
					$saldo = 0;
					$totalinteresactual=0;
					foreach ($creditos as $k => $v) {
						$hora_i_2 = new DateTime("now"); $hora_s_2 = new DateTime($v["fechavencimiento"]);	
						$intervalo_2 = $hora_i_2->diff($hora_s_2);
						if(date("Y-m-d") < $v["fechavencimiento"]){
							$sum_dias = (int)$intervalo_2->days + 1;
							$color = "green"; $estado = "POR VENCER EN ".$sum_dias." DIA(S)";
						}else{
							$color = "red"; $estado = "VENCIDO HACE ".$intervalo_2->days." DIA(S)";
						}

						$datos = array($v["seriecomprobante_ref"]."-".$v["nrocomprobante_ref"]);
						array_push($datos,utf8_decode($v["fechacredito"]));
						array_push($datos,utf8_decode($v["fechavencimiento"]));
						array_push($datos,utf8_decode($estado));

						array_push($datos,number_format($v["importe"],2));
						array_push($datos,number_format($v["interes"],2));
						array_push($datos,number_format($v["total"],2));
						array_push($datos,number_format($v["saldo"],2));
		                $pdf->Row($datos);

						$importe = $importe + $v["importe"]; 
						$interes = $interes + $v["interes"]; 
						$totalinteresactual = $totalinteresactual + $v["interesactual"];
						$total = $total + $v["total"]; 
						$saldo = $saldo + $v["saldo"];
					}
					$pdf->Cell(array_sum($w),0,'','T'); $pdf->Ln();

					$pdf->SetFont('Arial','B',8);
					$pdf->Cell(130,5,"TOTALES",1,0,'R');
				    $pdf->Cell(15,5,number_format($importe,2),1,"R");
				    $pdf->Cell(15,5,number_format($interes,2),1,"R");
				    $pdf->Cell(15,5,number_format($total,2),1,"R");
				    $pdf->Cell(15,5,number_format($saldo,2),1,"R"); $pdf->Ln(); $pdf->Ln();
				}
			}

			$pdf->SetTitle("phuyu Peru - Reporte Creditos"); $pdf->Output();
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function excel_creditos(){
		if ($_GET["datos"]) {
			$this->request = json_decode($_GET["datos"]);
			if ($this->request->tipo==1) {
				$tipo = "CREDITOS POR COBRAR"; $socio = "CLIENTE"; $tipo_texto = "COBRANZA";
			}else{
				$tipo = "CREDITOS POR PAGAR"; $socio = "PROVEEDOR"; $tipo_texto = "PAGO";
			}

			$desde = explode("-", $this->request->fecha_desde); $hasta = explode("-", $this->request->fecha_hasta);

			$this->request->codlote = (isset($this->request->codlote) || !empty($this->request->codlote)) ? $this->request->codlote : 0;

			if($this->request->codpersona == 0){
				$socios = $this->Creditos_model->socios_creditos($this->request->fecha_desde,$this->request->fecha_hasta,$this->request->tipo);
			}else{
				$socios = $this->db->query("select codpersona,razonsocial,documento,direccion,telefono from personas where codpersona=".$this->request->codpersona)->result_array();
			}

			if($this->request->tipo_consulta == 1){
				if ($this->request->mostrar==1) {
					foreach ($socios as $key => $value) {
						$anterior = $this->Creditos_model->estado_cuenta_anterior($this->request->fecha_desde,$this->request->tipo,$value["codpersona"],$this->request->estado);
						$movimientos = $this->Creditos_model->estado_cuenta_cliente($this->request->fecha_desde,$this->request->fecha_hasta,$this->request->tipo,$value["codpersona"],$this->request->codlote,$this->request->estado);

						$socios[$key]["anterior"] = $anterior;
						$socios[$key]["movimientos"] = $movimientos;
					}

					$this->load->view("reportes/creditos/estadocuentaxls.php",compact("socios","socio","tipo","desde","hasta"));
				}else{
					foreach ($socios as $key => $value) {
						$creditos = $this->Creditos_model->estado_cuenta_creditos($this->request->fecha_desde,$this->request->fecha_hasta,$this->request->tipo,$value["codpersona"],$this->request->codlote);

						$socios[$key]["creditos"] = $creditos;
					}
					$this->load->view("reportes/creditos/creditosxls.php",compact("socios","socio","tipo","desde","hasta"));
				}
			}else{
				foreach ($socios as $key => $value) {
					$anterior = $this->Creditos_model->estado_cuenta_anterior($this->request->fecha_desde,$this->request->tipo,$value["codpersona"],$this->request->estado);
					$movimientos = $this->Creditos_model->estado_cuenta_detallado($this->request->fecha_desde,$this->request->fecha_hasta,$this->request->tipo,$value["codpersona"],$this->request->codlote,$this->request->estado);

					$socios[$key]["anterior"] = $anterior;
					$socios[$key]["movimientos"] = $movimientos;
				}
				$this->load->view("reportes/creditos/estadocuentadetalladoxls.php",compact("socios","socio","tipo","desde","hasta"));
			}
			
		}else{
			$this->load->view("phuyu/404");
		}
	}
}