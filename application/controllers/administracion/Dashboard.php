<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model"); $this->load->model("Caja_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			$stockminimos = $this->db->query("select p.descripcion,u.descripcion as unidad,round(pu.stockactualconvertido) as stock,m.descripcion as marca from almacen.productos as p inner join almacen.productoubicacion as pu on(p.codproducto=pu.codproducto) JOIN almacen.lineasxsucursales ls ON (pu.codsucursal = ls.codsucursal AND p.codlinea = ls.codlinea AND ls.codsucursal = ".$_SESSION["phuyu_codsucursal"]." ) inner join almacen.unidades as u on(u.codunidad=pu.codunidad) inner join almacen.marcas as m on(p.codmarca=m.codmarca) where p.estado=1 and pu.estado=1 and pu.codalmacen=".$_SESSION["phuyu_codalmacen"]." and pu.stockactualconvertido<=10 and pu.factor = 1 order by pu.stockactualconvertido asc limit 3")->result_array();

			$stockmaximos = $this->db->query("select p.descripcion,u.descripcion as unidad,round(pu.stockactualconvertido) as stock,m.descripcion as marca from almacen.productos as p inner join almacen.productoubicacion as pu on(p.codproducto=pu.codproducto) JOIN almacen.lineasxsucursales ls ON (pu.codsucursal = ls.codsucursal AND p.codlinea = ls.codlinea AND ls.codsucursal = ".$_SESSION["phuyu_codsucursal"]." ) inner join almacen.unidades as u on(u.codunidad=pu.codunidad) inner join almacen.marcas as m on(p.codmarca=m.codmarca) where p.estado=1 and pu.estado=1 and pu.codalmacen=".$_SESSION["phuyu_codalmacen"]." and pu.factor = 1 order by pu.stockactualconvertido desc limit 3")->result_array();

			$clientes = $this->db->query("select personas.codpersona,personas.razonsocial,personas.documento, count(kardex.codpersona) as cantidad,sum(kardex.importe) as importe from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) where kardex.codmovimientotipo=20 and kardex.codsucursal=".$_SESSION["phuyu_codsucursal"]." and kardex.estado=1 group by personas.codpersona,personas.razonsocial order by cantidad desc limit 3")->result_array();

			$proveedores = $this->db->query("select personas.codpersona,personas.razonsocial,personas.documento, count(kardex.codpersona) as cantidad,sum(kardex.importe) as importe from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) where kardex.codmovimientotipo=2 and kardex.codsucursal=".$_SESSION["phuyu_codsucursal"]." and kardex.estado=1 group by personas.codpersona,personas.razonsocial order by cantidad desc limit 3")->result_array();

			$info = $this->db->query("select *from sunat.kardexsunat where estado<>1")->result_array();

			$informacion = count($info);

			$this->load->view("administracion/dashboard/index",compact("stockminimos","stockmaximos","clientes","proveedores","informacion"));
		}else{
			$this->load->view("phuyu/404");
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

	function vaciabd(){
		$vaciar = $this->db->query("SELECT f_datosiniciales(".$_SESSION["phuyu_codsucursal"].")")->result_array();

		echo 1;
	}

	function ver_pedidos(){
			$this->request = json_decode($_GET["datos"]); $categorias = array(); $totalesc = array(); $totalesp = array();
			$año = date('Y'); $max = 0; $step = 0;
			for ($i=0; $i < 12 ; $i++) {
				$m = $i + 1;
				if($m<10){
					$m = '0'.$m;
				}
				$mes = $año.'-'.$m;
				$totalc = $this->db->query("select round(COALESCE(sum(importe),0),2) as importe from kardex.pedidos where TO_CHAR(fechapedido,'YYYY-MM')='".$mes."' and codempleado=".$_SESSION["phuyu_codempleado"]." AND estadoproceso=1 AND estado = 1")->result_array();

				$totalp = $this->db->query("select round(COALESCE(sum(importe),0),2) as importe from kardex.pedidos where TO_CHAR(fechapedido,'YYYY-MM')='".$mes."' and codempleado=".$_SESSION["phuyu_codempleado"]." AND estadoproceso=0 AND estado = 1")->result_array();

				if((double)$totalc[0]["importe"]>(double)$totalp[0]["importe"]){
					$lebel = (double)$totalc[0]["importe"];
				}else{
					$lebel = (double)$totalp[0]["importe"];
				}

				if($lebel>$max){
					$max = $lebel;
				}

				$categorias[$i] = ""; $totalesc[$i] = (double)$totalc[0]["importe"]; $totalesp[$i] = (double)$totalp[0]["importe"];
			}

			if($max<=1000){
				$step = 100;
			}else if($max>1000 && $max <= 10000){
				$step = 500;
			}
			else if($max>10000 && $max <= 50000){
				$step = 5000;
			}else{
				$step = 10000;
			}

			$data["categorias"] = $categorias; 
			$data["totalesc"] = $totalesc;
			$data["totalesp"] = $totalesp;
			$data["maximo"] = $max;
			$data["rango"] = $step;
			echo json_encode($data);
		}

	function ver_pedidosventas(){
			$categorias = array(); $totalesc = array();
			$año = date('Y'); $max = 0; $step = 0;
			for ($i=0; $i < 12 ; $i++) {
				$m = $i + 1;
				if($m<10){
					$m = '0'.$m;
				}
				$mes = $año.'-'.$m;
				$totalc = $this->db->query("select round(COALESCE(sum(importe),0),2) as importe from kardex.kardex where TO_CHAR(fechacomprobante,'YYYY-MM')='".$mes."' and codempleado=".$_SESSION["phuyu_codempleado"]." AND estado = 1")->result_array();

				$lebel = (double)$totalc[0]["importe"];

				if($lebel>$max){
					$max = $lebel;
				}

				$categorias[$i] = ""; $totalesc[$i] = (double)$totalc[0]["importe"];
			}

			if($max<=1000){
				$step = 100;
			}else if($max>1000 && $max <= 10000){
				$step = 500;
			}
			else if($max>10000 && $max <= 50000){
				$step = 5000;
			}else{
				$step = 10000;
			}

			$data["categorias"] = $categorias; 
			$data["totalesc"] = $totalesc;
			$data["maximo"] = $max;
			$data["rango"] = $step;
			echo json_encode($data);
		}
}