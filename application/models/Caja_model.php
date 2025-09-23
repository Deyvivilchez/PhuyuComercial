<?php

class Caja_model extends CI_Model {

	public function __construct(){
		parent::__construct();
	}

	function phuyu_movimientos($codkardex, $comprobantecaja, $tipomovimiento, $importe, $campos, $importemoneda){
		$kardex = $this->db->query("select nrocomprobante from kardex.kardex where codkardex=".$codkardex)->result_array(); $nrocomprobante = "";
		if (count($kardex) > 0) {
			$nrocomprobante = $kardex[0]["nrocomprobante"];
		}
		$descripcion = "INGRESO POR VENTA";
		if ($tipomovimiento == 2) {
			$descripcion = "EGRESO POR COMPRA";
		}
		$serie = $this->db->query("select seriecomprobante from caja.comprobantes where codcomprobantetipo=".$comprobantecaja." and codsucursal=".$_SESSION["phuyu_codsucursal"]." and codcaja=".$_SESSION["phuyu_codcaja"]." and estado=1")->result_array();

		$campos->codlote = (isset($campos->codlote) || !empty($campos->codlote)) ? $campos->codlote : 0;

		$data = array(
			"codcontroldiario" => (int)$_SESSION["phuyu_codcontroldiario"],
			"codcaja" => (int)$_SESSION["phuyu_codcaja"],
			"codusuario" => (int)$_SESSION["phuyu_codusuario"],
			"codconcepto" => $campos->codconcepto,
			"codpersona" => $campos->codpersona,
			"codcomprobantetipo" => $comprobantecaja,
			"seriecomprobante" => $serie[0]["seriecomprobante"],
			"tipomovimiento" => $tipomovimiento,
			"codkardex" => $codkardex,
			"codcomprobantetipo_ref" => $campos->codcomprobantetipo,
			"seriecomprobante_ref" => $campos->seriecomprobante,
			"nrocomprobante_ref" => $nrocomprobante,
			"importe" => (double)$importe,
			"referencia" => $descripcion,
			"condicionpago" => $campos->condicionpago,
			"codlote" => (int)$campos->codlote,
			"cliente" => $campos->cliente, "direccion" => $campos->direccion,
			"tipocambio" => $campos->tipocambio, "importemoneda" => $importemoneda
		);
		$estado = $this->db->insert("caja.movimientos", $data);
		$codmovimiento = $this->db->insert_id("caja.movimientos_codmovimiento_seq");

		/* GENERAR CORRELATIVO DEL MOVIMIENTO DE CAJA */

		$actual = $this->db->query("select nrocorrelativo from caja.comprobantes where codcomprobantetipo=".$comprobantecaja." and seriecomprobante='".$serie[0]["seriecomprobante"]."' and codsucursal=".$_SESSION["phuyu_codsucursal"]." and estado=1")->result_array();

		$nrocorrelativo = (int)($actual[0]["nrocorrelativo"]) + 1;
		$data = array(
			"nrocorrelativo" => $nrocorrelativo
		);
		$this->db->where("codsucursal", $_SESSION["phuyu_codsucursal"]);
		$this->db->where("codcomprobantetipo", $comprobantecaja);
		$this->db->where("seriecomprobante", $serie[0]["seriecomprobante"]);
		$estado = $this->db->update("caja.comprobantes", $data);

		$nrocorrelativo = str_pad($nrocorrelativo, 8, "0", STR_PAD_LEFT);
		$data = array(
			"nrocomprobante" => $nrocorrelativo
		);
		$this->db->where("codmovimiento", $codmovimiento);
		$estado = $this->db->update("caja.movimientos", $data);

		return $codmovimiento;
	}

	function phuyu_movimientosdetalle($codmovimiento, $pagos){
		$estado = 1;
		if ((double)$pagos->monto_efectivo > 0) {
			$data = array(
				"codmovimiento" => (int)$codmovimiento,
				"codtipopago" => (int)$pagos->codtipopago_efectivo,
				"codcontroldiario" => (int)$_SESSION["phuyu_codcontroldiario"],
				"codcaja" => (int)$_SESSION["phuyu_codcaja"],
				"fechadocbanco" => date("Y-m-d"),
				"importe" => round( ((double)$pagos->monto_efectivo - (double)$pagos->vuelto_efectivo),2),
				"importeentregado" => (double)$pagos->monto_efectivo,
				"vuelto" => (double)$pagos->vuelto_efectivo
			);
			$estado = $this->db->insert("caja.movimientosdetalle", $data);
		}
		if ((int)$pagos->codtipopago_tarjeta > 0) {
			$data = array(
				"codmovimiento" => (int)$codmovimiento,
				"codtipopago" => (int)$pagos->codtipopago_tarjeta,
				"codcontroldiario" => (int)$_SESSION["phuyu_codcontroldiario"],
				"codcaja" => (int)$_SESSION["phuyu_codcaja"],
				"fechadocbanco" => date("Y-m-d"),
				"nrodocbanco" => $pagos->nrovoucher,
				"importe" => (double)$pagos->monto_tarjeta,
				"importeentregado" => (double)$pagos->monto_tarjeta
			);
			$estado = $this->db->insert("caja.movimientosdetalle", $data);
		}
		return $estado;
	}

	function phuyu_credito($codkardex, $codmovimiento, $tipocredito, $campos, $totales, $cuotas,$documento){

		$campos->codlote = (isset($campos->codlote) || !empty($campos->codlote)) ? $campos->codlote : 0;

		$data = array(
			"codsucursal" => (int)$_SESSION["phuyu_codsucursal"],
			"codcaja" => (int)$_SESSION["phuyu_codcaja"],
			"codusuario" => (int)$_SESSION["phuyu_codusuario"],
			"codcreditoconcepto" => (int)$campos->codcreditoconcepto,
			"codpersona" => (int)$campos->codpersona,
			"codmoneda" => (int)$campos->codmoneda, "tipocambio" => (double)$campos->tipocambio,
			"codmovimiento" => (int)$codmovimiento,
			"codkardex" => (int)$codkardex,
			"tipo" => (int)$tipocredito,
			"fechacredito" => $campos->fechacomprobante,
			"fechainicio" => $campos->fechacomprobante,
			"nrodias" => (int)$campos->nrodias,
			"nrocuotas" => (int)$campos->nrocuotas,
			"importe" => (double)$totales->importe,
			"tasainteres" => (double)$campos->tasainteres,
			"interes" => (double)$totales->interes,
			"saldo" => (double)$campos->totalcredito,
			"total" => (double)$campos->totalcredito,
			"codlote" => (int)$campos->codlote,
			"cliente" => $campos->cliente,
			"direccion" => $campos->direccion,
			"documento" => $documento,
			"creditoprogramado" => $campos->creditoprogramado
		);
		$estado = $this->db->insert("kardex.creditos", $data);
		$codcredito = $this->db->insert_id("kardex.creditos_codcredito_seq");

		foreach ($cuotas as $key => $value) {
			$importe = (double)$cuotas[$key]->importe;
			$interes = (double)$cuotas[$key]->interes;
			$total = (double)$cuotas[$key]->total;
			if ($campos->codmoneda!=1) {
				$importe = round((double)$cuotas[$key]->importe,1);
				$interes = round($cuotas[$key]->interes,1);
				$total = round($cuotas[$key]->total,1);
			}
			$cuotas[$key]->nroletra = (isset($cuotas[$key]->nroletra)) ? $cuotas[$key]->nroletra : "";
			$cuotas[$key]->nrounicodepago = (isset($cuotas[$key]->nrounicodepago)) ? $cuotas[$key]->nrounicodepago : "";
			$data = array(
				"codcredito" => (int)$codcredito,
				"nrocuota" => (int)$cuotas[$key]->nrocuota,
				"codsucursal" => (int)$_SESSION["phuyu_codsucursal"],
				"fechavence" => $cuotas[$key]->fechavence,
				"nroletra" => $cuotas[$key]->nroletra,
				"nrounicodepago" => $cuotas[$key]->nrounicodepago ,
				"importe" => (double)$importe,
				"saldo" => (double)$total,
				"interes" => (double)$interes,
				"total" => (double)$total
			);
			$estado = $this->db->insert("kardex.cuotas", $data);
			$fechavence = $cuotas[$key]->fechavence;
		}
		$actual = $this->db->query("select seriecomprobante,nrocorrelativo from caja.comprobantes where codcomprobantetipo=26 and codsucursal=".$_SESSION["phuyu_codsucursal"]." and estado=1")->result_array();

		$nrocorrelativo = (int)($actual[0]["nrocorrelativo"]) + 1;
		$seriecomprobante = $actual[0]["seriecomprobante"];
		$data = array(
			"nrocorrelativo" => $nrocorrelativo
		);
		$this->db->where("codsucursal", $_SESSION["phuyu_codsucursal"]);
		$this->db->where("codcomprobantetipo", 26);
		$estado = $this->db->update("caja.comprobantes", $data);

		$nrocorrelativo = str_pad($nrocorrelativo, 8, "0", STR_PAD_LEFT);

		$comprobante = $this->db->query("select seriecomprobante,nrocomprobante,ct.abreviatura as tipo from kardex.kardex k inner join caja.comprobantetipos ct on(k.codcomprobantetipo=ct.codcomprobantetipo) where codkardex=".$codkardex)->result_array();

		$data = array(
			"fechavencimiento" => $fechavence,
			"codcomprobantetipo" => 26,
			"seriecomprobante" => $seriecomprobante,
			"nrocomprobante" => $nrocorrelativo,
			"comprobantereferencia" => $comprobante[0]["tipo"].'-'.$comprobante[0]["seriecomprobante"].'-'.$comprobante[0]["nrocomprobante"]
		);
		$this->db->where("codcredito", $codcredito);
		$estado = $this->db->update("kardex.creditos", $data);

		return $estado;
	}

	function phuyu_creditopedido($codpedido, $codmovimiento, $tipocredito, $campos, $totales, $cuotas){

		$campos->codlote = (isset($campos->codlote) || !empty($campos->codlote)) ? $campos->codlote : 0;

		$data = array(
			"codsucursal" => (int)$_SESSION["phuyu_codsucursal"],
			"codcaja" => (int)$_SESSION["phuyu_codcaja"],
			"codusuario" => (int)$_SESSION["phuyu_codusuario"],
			"codcreditoconcepto" => (int)$campos->codcreditoconcepto,
			"codpersona" => (int)$campos->codpersona,
			"codmoneda" => (int)$campos->codmoneda, "tipocambio" => (double)$campos->tipocambio,
			"codmovimiento" => (int)$codmovimiento,
			"codpedido" => (int)$codpedido,
			"tipo" => (int)$tipocredito,
			"fechacredito" => $campos->fechacomprobante,
			"fechainicio" => $campos->fechacomprobante,
			"nrodias" => (int)$campos->nrodias,
			"nrocuotas" => (int)$campos->nrocuotas,
			"importe" => (double)$totales->importe,
			"tasainteres" => (double)$campos->tasainteres,
			"interes" => (double)$totales->interes,
			"saldo" => (double)$campos->totalcredito,
			"total" => (double)$campos->totalcredito,
			"codlote" => (int)$campos->codlote,
			"cliente" => $campos->cliente,
			"direccion" => $campos->direccion
		);
		$estado = $this->db->insert("kardex.creditospedidos", $data);
		$codcredito = $this->db->insert_id("kardex.creditospedidos_codcreditopedido_seq");

		foreach ($cuotas as $key => $value) {
			$importe = (double)$cuotas[$key]->importe;
			$interes = (double)$cuotas[$key]->interes;
			$total = (double)$cuotas[$key]->total;
			if ($campos->codmoneda!=1) {
				$importe = round((double)$cuotas[$key]->importe * $campos->tipocambio,1);
				$interes = round($cuotas[$key]->interes * $campos->tipocambio,1);
				$total = round($cuotas[$key]->total * $campos->tipocambio,1);
			}
			$data = array(
				"codcreditopedido" => (int)$codcredito,
				"nrocuotapedido" => (int)$cuotas[$key]->nrocuota,
				"codsucursal" => (int)$_SESSION["phuyu_codsucursal"],
				"fechavence" => $cuotas[$key]->fechavence,
				"importe" => (double)$importe,
				"saldo" => (double)$total,
				"interes" => (double)$interes,
				"total" => (double)$total
			);
			$estado = $this->db->insert("kardex.cuotaspedidos", $data);
			$fechavence = $cuotas[$key]->fechavence;
		}

		$data = array(
			"fechavencimiento" => $fechavence
		);
		$this->db->where("codcreditopedido", $codcredito);
		$estado = $this->db->update("kardex.creditospedidos", $data);

		return $estado;
	}

	function phuyu_creditoproforma($codproforma, $codmovimiento, $tipocredito, $campos, $totales, $cuotas){

		$campos->codlote = (isset($campos->codlote) || !empty($campos->codlote)) ? $campos->codlote : 0;

		$data = array(
			"codsucursal" => (int)$_SESSION["phuyu_codsucursal"],
			"codcaja" => (int)$_SESSION["phuyu_codcaja"],
			"codusuario" => (int)$_SESSION["phuyu_codusuario"],
			"codcreditoconcepto" => (int)$campos->codcreditoconcepto,
			"codpersona" => (int)$campos->codpersona,
			"codmoneda" => (int)$campos->codmoneda, "tipocambio" => (double)$campos->tipocambio,
			"codmovimiento" => (int)$codmovimiento,
			"codproforma" => (int)$codproforma,
			"tipo" => (int)$tipocredito,
			"fechacredito" => $campos->fechacomprobante,
			"fechainicio" => $campos->fechacomprobante,
			"nrodias" => (int)$campos->nrodias,
			"nrocuotas" => (int)$campos->nrocuotas,
			"importe" => (double)$totales->importe,
			"tasainteres" => (double)$campos->tasainteres,
			"interes" => (double)$totales->interes,
			"saldo" => (double)$campos->totalcredito,
			"total" => (double)$campos->totalcredito,
			"codlote" => (int)$campos->codlote,
			"cliente" => $campos->cliente,
			"direccion" => $campos->direccion
		);
		$estado = $this->db->insert("kardex.creditosproformas", $data);
		$codcredito = $this->db->insert_id("kardex.creditosproformas_codcreditoproforma_seq");

		foreach ($cuotas as $key => $value) {
			$importe = (double)$cuotas[$key]->importe;
			$interes = (double)$cuotas[$key]->interes;
			$total = (double)$cuotas[$key]->total;
			if ($campos->codmoneda!=1) {
				$importe = round((double)$cuotas[$key]->importe * $campos->tipocambio,1);
				$interes = round($cuotas[$key]->interes * $campos->tipocambio,1);
				$total = round($cuotas[$key]->total * $campos->tipocambio,1);
			}
			$data = array(
				"codcreditoproforma" => (int)$codcredito,
				"nrocuotaproforma" => (int)$cuotas[$key]->nrocuota,
				"codsucursal" => (int)$_SESSION["phuyu_codsucursal"],
				"fechavence" => $cuotas[$key]->fechavence,
				"importe" => (double)$importe,
				"saldo" => (double)$total,
				"interes" => (double)$interes,
				"total" => (double)$total
			);
			$estado = $this->db->insert("kardex.cuotasproformas", $data);
			$fechavence = $cuotas[$key]->fechavence;
		}

		$data = array(
			"fechavencimiento" => $fechavence
		);
		$this->db->where("codcreditoproforma", $codcredito);
		$estado = $this->db->update("kardex.creditosproformas", $data);

		return $estado;
	}

	function phuyu_estadocaja(){
		$caja = $this->db->query("select *from caja.controldiario where codcaja=".$_SESSION["phuyu_codcaja"]." and codsucursal=".$_SESSION["phuyu_codsucursal"]." and cerrado=1 and estado=1")->result_array();
		if (count($caja)>0) {
			$_SESSION["phuyu_codcontroldiario"] = $caja[0]["codcontroldiario"];
		}else{
			$_SESSION["phuyu_codcontroldiario"] = 0;
		}
		return $caja;
	}

	function phuyu_correlativo($codmovimiento, $codcomprobantetipo, $seriecomprobante){
		$actual = $this->db->query("select nrocorrelativo from caja.comprobantes where codcomprobantetipo=".$codcomprobantetipo." and seriecomprobante='".$seriecomprobante."' and codsucursal=".$_SESSION["phuyu_codsucursal"]." and estado=1")->result_array();

		$nrocorrelativo = (int)($actual[0]["nrocorrelativo"]) + 1;
		$data = array(
			"nrocorrelativo" => $nrocorrelativo
		);
		$this->db->where("codsucursal", $_SESSION["phuyu_codsucursal"]);
		$this->db->where("codcomprobantetipo", $codcomprobantetipo);
		$this->db->where("seriecomprobante", $seriecomprobante);
		$estado = $this->db->update("caja.comprobantes", $data);

		// ACTUALIZAMOS EL NRO COMPROBANTE DE MOVIMIENTOS //
		$nrocorrelativo = str_pad($nrocorrelativo, 8, "0", STR_PAD_LEFT);
		$data = array(
			"nrocomprobante" => $nrocorrelativo
		);
		$this->db->where("codmovimiento", $codmovimiento);
		$estado = $this->db->update("caja.movimientos", $data);
		return $estado;
	}

	function phuyu_saldotipopago($codcontroldiario,$codtipopago){
		$ingresos = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcontroldiario=".$codcontroldiario." and m.tipomovimiento=1 and md.codtipopago=".$codtipopago." and m.estado=1")->result_array();
		$ingresosconfirmados = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcontroldiario=".$codcontroldiario." and m.tipomovimiento=1 and md.codtipopago=".$codtipopago." and m.estado=1 and m.cobrado=1")->result_array();
		$ingresospendientes = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcontroldiario=".$codcontroldiario." and m.tipomovimiento=1 and md.codtipopago=".$codtipopago." and m.estado=1 and m.cobrado=0")->result_array();

		$egresos = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcontroldiario=".$codcontroldiario." and m.tipomovimiento=2 and md.codtipopago=".$codtipopago." and m.estado=1")->result_array();

		$egresosconfirmados = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcontroldiario=".$codcontroldiario." and m.tipomovimiento=2 and md.codtipopago=".$codtipopago." and m.estado=1 and m.cobrado = 1")->result_array();

		$egresospendientes = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcontroldiario=".$codcontroldiario." and m.tipomovimiento=2 and md.codtipopago=".$codtipopago." and m.estado=1 and m.cobrado = 0")->result_array();

		$transacciones = $this->db->query("select count(md.*) as transacciones from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where md.codcontroldiario=".$codcontroldiario." and md.codtipopago=".$codtipopago." and m.estado=1")->result_array();

		$total = array();
		$total["ingresosconfirmados"] = (double)($ingresosconfirmados[0]["importe"]);
		$total["ingresospendientes"] = (double)($ingresospendientes[0]["importe"]);
		$total["ingresos"] = (double)($ingresos[0]["importe"]);
		$total["egresosconfirmados"] = (double)($egresosconfirmados[0]["importe"]);
		$total["egresospendientes"] = (double)($egresospendientes[0]["importe"]);
		$total["egresos"] = (double)($egresos[0]["importe"]);
		$total["transacciones"] = (int)($transacciones[0]["transacciones"]);
		
		return $total;
	}

	function phuyu_saldotipopago_general($codcaja,$codtipopago){
		$ingresos = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcaja=".$codcaja." and m.tipomovimiento=1 and md.codtipopago=".$codtipopago." and m.estado=1")->result_array();

		$egresos = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcaja=".$codcaja." and m.tipomovimiento=2 and md.codtipopago=".$codtipopago." and m.estado=1")->result_array();

		$total = array();
		$total["ingresos"] = (double)($ingresos[0]["importe"]);
		$total["egresos"] = (double)($egresos[0]["importe"]);
		
		return $total;
	}

	function phuyu_saldocomprobantes($codcontroldiario,$codcomprobantetipo){
		$ingresos = $this->db->query("select round(COALESCE(sum(importe),0),2) as importe from caja.movimientos where codcontroldiario=".$codcontroldiario." and codcomprobantetipo_ref=".$codcomprobantetipo." and tipomovimiento=1 and estado=1")->result_array();
		$egresos = $this->db->query("select round(COALESCE(sum(importe),0),2) as importe from caja.movimientos where codcontroldiario=".$codcontroldiario." and codcomprobantetipo_ref=".$codcomprobantetipo." and tipomovimiento=2 and estado=1")->result_array();

		$total = array();
		$total["ingresos"] = (double)($ingresos[0]["importe"]);
		$total["egresos"] = (double)($egresos[0]["importe"]);
		
		return $total;
	}

	function phuyu_saldocaja($codcontroldiario){
		$saldoinicial = $this->db->query("select saldoinicialcaja from caja.controldiario where codcontroldiario=".$codcontroldiario)->result_array();
		$ingresos = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcontroldiario=".$codcontroldiario." and m.tipomovimiento=1 and (md.codtipopago=1 or md.codtipopago=2) and m.estado=1")->result_array();
		$egresos = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcontroldiario=".$codcontroldiario." and m.tipomovimiento=2 and (md.codtipopago=1 or md.codtipopago=3) and m.estado=1")->result_array();

		$saldo = array();
		if (count($saldoinicial)==0) {
			$saldo["saldoinicial"] = 0.00;
		}else{
			$saldo["saldoinicial"] = (double)($saldoinicial[0]["saldoinicialcaja"]);
		}
		$saldo["ingresos"] = (double)($ingresos[0]["importe"]);
		$saldo["egresos"] = (double)($egresos[0]["importe"]);
		$saldo["total"] = (double)($ingresos[0]["importe"] - $egresos[0]["importe"]);

		return $saldo;
	}

	function phuyu_saldobanco($codcontroldiario){
		$saldoinicial = $this->db->query("select saldoinicialbanco from caja.controldiario where codcontroldiario=".$codcontroldiario)->result_array();
		$ingresos = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcontroldiario=".$codcontroldiario." and m.tipomovimiento=1 and (md.codtipopago<>1 and md.codtipopago<>2) and m.estado=1")->result_array();
		$egresos = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcontroldiario=".$codcontroldiario." and m.tipomovimiento=2 and (md.codtipopago<>1 and md.codtipopago<>3) and m.estado=1")->result_array();

		$saldo = array();
		if (count($saldoinicial)==0) {
			$saldo["saldoinicial"] = 0.00;
		}else{
			$saldo["saldoinicial"] = (double)($saldoinicial[0]["saldoinicialbanco"]);
		}
		$saldo["ingresos"] = (double)($ingresos[0]["importe"]);
		$saldo["egresos"] = (double)($egresos[0]["importe"]);
		$saldo["total"] = (double)($ingresos[0]["importe"] - $egresos[0]["importe"]);
		
		return $saldo;
	}

	function phuyu_saldocaja_general($codcaja){
		$ingresos = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcaja=".$codcaja." and m.tipomovimiento=1 and (md.codtipopago=1 or md.codtipopago=2) and m.estado=1")->result_array();
		$egresos = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcaja=".$codcaja." and m.tipomovimiento=2 and (md.codtipopago=1 or md.codtipopago=3) and m.estado=1")->result_array();

		$saldo = array();
		$saldo["ingresos"] = (double)($ingresos[0]["importe"]);
		$saldo["egresos"] = (double)($egresos[0]["importe"]);
		$saldo["total"] = (double)($ingresos[0]["importe"] - $egresos[0]["importe"]);
		
		return $saldo;
	}

	function phuyu_saldobanco_general($codcaja){
		$ingresos = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcaja=".$codcaja." and m.tipomovimiento=1 and (md.codtipopago<>1 and md.codtipopago<>2) and m.estado=1")->result_array();
		$egresos = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcaja=".$codcaja." and m.tipomovimiento=2 and (md.codtipopago<>1 and md.codtipopago<>3) and m.estado=1")->result_array();

		$saldo = array();
		$saldo["ingresos"] = (double)($ingresos[0]["importe"]);
		$saldo["egresos"] = (double)($egresos[0]["importe"]);
		$saldo["total"] = (double)($ingresos[0]["importe"] - $egresos[0]["importe"]);
		
		return $saldo;
	}

	function phuyu_saldocaja_diario($codcontroldiario){
		$ingresos = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcontroldiario=".$codcontroldiario." and m.tipomovimiento=1 and (md.codtipopago=1 or md.codtipopago=2) and m.estado=1")->result_array();
		$egresos = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcontroldiario=".$codcontroldiario." and m.tipomovimiento=2 and (md.codtipopago=1 or md.codtipopago=3) and m.estado=1")->result_array();

		$saldo = array();
		$saldo["ingresos"] = (double)($ingresos[0]["importe"]);
		$saldo["egresos"] = (double)($egresos[0]["importe"]);
		$saldo["total"] = (double)($ingresos[0]["importe"] - $egresos[0]["importe"]);
		
		return $saldo;
	}

	function phuyu_saldobanco_diario($codcontroldiario){
		$ingresos = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcontroldiario=".$codcontroldiario." and m.tipomovimiento=1 and (md.codtipopago<>1 and md.codtipopago<>2) and m.estado=1")->result_array();
		$egresos = $this->db->query("select round(COALESCE(sum(md.importe),0),2) as importe from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) where m.codcontroldiario=".$codcontroldiario." and m.tipomovimiento=2 and (md.codtipopago<>1 and md.codtipopago<>3) and m.estado=1")->result_array();

		$saldo = array();
		$saldo["ingresos"] = (double)($ingresos[0]["importe"]);
		$saldo["egresos"] = (double)($egresos[0]["importe"]);
		$saldo["total"] = (double)($ingresos[0]["importe"] - $egresos[0]["importe"]);
		
		return $saldo;
	}
}