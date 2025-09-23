<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Consultacomprobantes extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model"); $this->load->model("Caja_model");
	}

	public function index(){
		$comprobantes = $this->db->query("select *from caja.comprobantetipos where codcomprobantetipo IN (10,12,14) and estado=1 ORDER BY codcomprobantetipo")->result_array();
		$this->load->view("consultas/index",compact("comprobantes"));
	}

	function consultar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$where = '';
			if(!empty($this->request->fechai)){
				$where .= " AND k.fechacomprobante>='".$this->request->fechai."'";
			}
			if(!empty($this->request->fechaf)){
				$where .= " AND k.fechacomprobante<='".$this->request->fechaf."'";
			}

			$data = $this->db->query("SELECT k.*,ks.*,p.*,round(k.importe,2) AS importe FROM kardex.kardex k JOIN sunat.kardexsunat ks on (k.codkardex = ks.codkardex ) JOIN public.personas p on (k.codpersona = p.codpersona ) WHERE p.documento='".$this->request->documento."' ".$where." AND k.codcomprobantetipo=".$this->request->codcomprobantetipo." AND k.estado = 1 ORDER BY k.fechacomprobante DESC")->result_array();
			echo json_encode($data);
		}	
	}

	function phuyu_totales(){
		if ($this->input->is_ajax_request()) {
			$caja = $this->Caja_model->phuyu_estadocaja();
			if (count($caja) == 0) {
				$estado = "CERRADA";
			}else{
				$estado = "APERTURADA";
			}

			$saldocaja = $this->Caja_model->phuyu_saldocaja_general($_SESSION["phuyu_codcaja"]); 
			$saldobanco = $this->Caja_model->phuyu_saldobanco_general($_SESSION["phuyu_codcaja"]); 

			$data = array();
			$data["estado"] = $estado;
			$data["caja"] = (double)round($saldocaja["total"],2);
			$data["banco"] = (double)round($saldobanco["total"],2);
			$data["general"] = (double)round( ($saldocaja["total"] + (double)$saldobanco["total"]),2);

			echo json_encode($data);
		}	
	}

	function phuyu_pagos(){
		if ($this->input->is_ajax_request()) {
			$tipopagos = $this->db->query("select *from caja.tipopagos where estado=1 order by codtipopago")->result_array();
			$ingresos = array(); $egresos = array(); $data = array();

			foreach ($tipopagos as $key => $value) {
				$total = $this->Caja_model->phuyu_saldotipopago_general($_SESSION["phuyu_codcaja"],$value["codtipopago"]);

				$ingresos[$key]["name"] = $value["descripcion"];
				$ingresos[$key]["y"] = (double)$total["ingresos"];

				$egresos[$key]["name"] = $value["descripcion"];
				$egresos[$key]["y"] = (double)$total["egresos"];
			}

			$data["ingresos"] = $ingresos; $data["egresos"] = $egresos;
			echo json_encode($data);
		}
	}
}