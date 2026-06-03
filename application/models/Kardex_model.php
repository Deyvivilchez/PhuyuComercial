<?php

class Kardex_model extends CI_Model {

	public function __construct(){
		parent::__construct();
	}

	function phuyu_kardex($campos, $totales, $operacion = 0){
		$data = array(
			"codsucursal" => (int)$_SESSION["phuyu_codsucursal"], "codalmacen" => (int)$_SESSION["phuyu_codalmacen"],
			"codusuario" => (int)$_SESSION["phuyu_codusuario"],
			"codpersona" => (int)$campos->codpersona,
			"codmovimientotipo" => (int)$campos->codmovimientotipo,
			"condicionpago" => (int)$campos->condicionpago,
			"codmoneda" => (int)$campos->codmoneda, "tipocambio" => (double)$campos->tipocambio,
			"fechacomprobante" => $campos->fechacomprobante, "fechakardex" => $campos->fechakardex,
			"hora" => date('H:i:s'),
			"codcomprobantetipo" => (int)$campos->codcomprobantetipo,
			"seriecomprobante" => $campos->seriecomprobante,
			"nrocomprobante" => $campos->nro,
			"valorventa" => (double)$totales->valorventa,
			"porcdescuento" => (double)$campos->porcdescuento,
			"descglobal" => (double)$totales->descglobal,
			"descuentos" => (double)$totales->descuentos,
			"porcigv" => (double)$_SESSION["phuyu_igv"], "igv" => (double)$totales->igv,
			"porcicbper" => (double)$_SESSION["phuyu_icbper"], "icbper" => (double)$totales->icbper,
			"importe" => (double)$totales->importe,
			"flete" => (double)$totales->flete, "gastos" => (double)$totales->gastos,
			"retirar" => (int)$campos->retirar,
			"descripcion" => $campos->descripcion,
			"nroplaca" => $campos->nroplaca,
			"cliente" => $campos->cliente,
			"direccion" => $campos->direccion,
			"codempleado" => (int)$campos->codempleado,
			"codcentrocosto" => (int)$campos->codcentrocosto,
			"afectacaja" => (int)$campos->afectacaja,
			"conleyendaamazonia" => (int)$campos->conleyendaamazonia,
			"codlote" => (int)$campos->codlote
		);
		$estado = $this->db->insert("kardex.kardex", $data);
		$codkardex = $this->db->insert_id("kardex.kardex_codkardex_seq");

		/* GENERAR CORRELATIVO DEL KARDEX */

		if ($operacion == 0 || $campos->codcomprobantetipo == 13) {
			$actual = $this->db->query("select nrocorrelativo from caja.comprobantes where codcomprobantetipo=".$campos->codcomprobantetipo." and seriecomprobante='".$campos->seriecomprobante."' and codsucursal=".$_SESSION["phuyu_codsucursal"]." and estado=1")->result_array();

			$nrocorrelativo = (int)($actual[0]["nrocorrelativo"]) + 1;
			$data = array(
				"nrocorrelativo" => $nrocorrelativo
			);
			$this->db->where("codsucursal", $_SESSION["phuyu_codsucursal"]);
			$this->db->where("codcomprobantetipo", $campos->codcomprobantetipo);
			$this->db->where("seriecomprobante", $campos->seriecomprobante);
			$estado = $this->db->update("caja.comprobantes", $data);

			$nrocorrelativo = str_pad($nrocorrelativo, 8, "0", STR_PAD_LEFT);
			$data = array(
				"nrocomprobante" => $nrocorrelativo
			);
			$this->db->where("codkardex", $codkardex);
			$estado = $this->db->update("kardex.kardex", $data);
		}

		return $codkardex;
	}

	function phuyu_kardexalmacen($codkardex, $comprobantealmacen, $campos){
		$serie = $this->db->query("select seriecomprobante from caja.comprobantes where codcomprobantetipo=".$comprobantealmacen." and codsucursal=".$_SESSION["phuyu_codsucursal"]." and codalmacen=".$_SESSION["phuyu_codalmacen"]." and estado=1")->result_array();

		$data = array(
			"codsucursal" => (int)$_SESSION["phuyu_codsucursal"], "codalmacen" => (int)$_SESSION["phuyu_codalmacen"],
			"codusuario" => (int)$_SESSION["phuyu_codusuario"],
			"codkardex" => (int)$codkardex,
			"codmovimientotipo" => (int)$campos->codmovimientotipo,
			"fechakardex" => $campos->fechakardex,
			"codcomprobantetipo" => $comprobantealmacen, "seriecomprobante" => $serie[0]["seriecomprobante"]
		);
		$estado = $this->db->insert("kardex.kardexalmacen", $data);
		$codkardexalmacen = $this->db->insert_id("kardex.kardexalmacen_codkardexalmacen_seq");

		/* GENERAR CORRELATIVO DEL KARDEX ALMACEN */

		$actual = $this->db->query("select nrocorrelativo from caja.comprobantes where codcomprobantetipo=".$comprobantealmacen." and seriecomprobante='".$serie[0]["seriecomprobante"]."' and codsucursal=".$_SESSION["phuyu_codsucursal"]." and estado=1")->result_array();

		$nrocorrelativo = (int)($actual[0]["nrocorrelativo"]) + 1;
		$data = array(
			"nrocorrelativo" => $nrocorrelativo
		);
		$this->db->where("codsucursal", $_SESSION["phuyu_codsucursal"]);
		$this->db->where("codcomprobantetipo", $comprobantealmacen);
		$this->db->where("seriecomprobante", $serie[0]["seriecomprobante"]);
		$estado = $this->db->update("caja.comprobantes", $data);

		$nrocorrelativo = str_pad($nrocorrelativo, 8, "0", STR_PAD_LEFT);
		$data = array(
			"nrocomprobante" => $nrocorrelativo
		);
		$this->db->where("codkardexalmacen", $codkardexalmacen);
		$estado = $this->db->update("kardex.kardexalmacen", $data);

		return $codkardexalmacen;
	}

	function phuyu_kardexdetalle($codkardex, $codkardexalmacen, $detalle, $retirar, $operacion = 0, $codpedido = 0, $codproforma = 0,$tipocambio = 1){
		$item = 0; $estado = 1;
		$informacion['success'] = true;
		foreach ($detalle as $key => $value) { 
			if(!isset($detalle[$key]->flete)){
                $detalle[$key]->flete = 0;
			}

			$producto = $this->db->query("select pr.*,u.descripcion AS unidad from almacen.productounidades pr inner join almacen.unidades u ON pr.codunidad = u.codunidad where pr.codproducto=".$detalle[$key]->codproducto." and pr.codunidad=".$detalle[$key]->codunidad)->result_array();

			if($operacion==0){               
				$preciocompra = (double)$producto[0]['preciocompra'];
				$preciocosto = (double)$producto[0]['preciocosto'];
			}else{
				$preciocompra = ((double)$detalle[$key]->precio*(double)$tipocambio);
				$preciocosto = (((double)$detalle[$key]->precio + (double)$detalle[$key]->flete)*(double)$tipocambio);

				$data = array(
					"preciocompra" => (double)$preciocompra,
					"preciocosto" => (double)$preciocosto
				);

				$this->db->where("codproducto", $detalle[$key]->codproducto);
				$this->db->where("codunidad", $detalle[$key]->codunidad);
				$estado = $this->db->update("almacen.productounidades", $data);

				$this->db->where("codproducto", $detalle[$key]->codproducto);
				$this->db->where("codalmacen", $_SESSION["phuyu_codalmacen"]);
				$this->db->where("codunidad", $detalle[$key]->codunidad);
				$estado = $this->db->update("almacen.productoubicacion", $data);
			}

			if ($retirar==1) {
				$recogido = (double)$detalle[$key]->cantidad;
			}else{
				$recogido = 0;
			}

			$item = $item + 1;
			$data = array(
				"codkardex" => (int)$codkardex, 
				"codproducto" => (int)$detalle[$key]->codproducto, "codunidad" => (int)$detalle[$key]->codunidad, "item" => $item,
				"cantidad" => (double)$detalle[$key]->cantidad,
				"preciobruto" => (double)$detalle[$key]->preciobruto,
				"porcdescuento" => (double)$detalle[$key]->porcdescuento,
				"descuento" => (double)$detalle[$key]->descuento,
				"preciosinigv" => (double)$detalle[$key]->preciosinigv,
				"preciounitario" => (double)$detalle[$key]->precio,
				"preciorefunitario" => (double)$detalle[$key]->preciorefunitario,
				"codafectacionigv" => $detalle[$key]->codafectacionigv,
				"igv" => (double)$detalle[$key]->igv,
				"conicbper" => (double)$detalle[$key]->conicbper,
				"icbper" => (double)$detalle[$key]->icbper,
				"valorventa" => (double)$detalle[$key]->valorventa,
				"subtotal" => (double)$detalle[$key]->subtotal,
				"descripcion" => $detalle[$key]->descripcion,
				"recoger" => (int)$retirar,
				"recogido" => (double)$recogido,
				"flete" => $detalle[$key]->flete,
				"preciocompra" => $preciocompra,
				"preciocosto" => $preciocosto
			);
			$estado = $this->db->insert("kardex.kardexdetalle", $data);

			$cantidad_recoger = 0;
			if ($retirar==1) {
				$data = array(
					"codkardexalmacen" => (int)$codkardexalmacen, 
					"codproducto" => (int)$detalle[$key]->codproducto, "codunidad" => (int)$detalle[$key]->codunidad, "item" => $item,
					"codalmacen" => (int)$_SESSION["phuyu_codalmacen"], "codsucursal" => (int)$_SESSION["phuyu_codsucursal"],
					"cantidad" => (double)$detalle[$key]->cantidad
				);
				$estado = $this->db->insert("kardex.kardexalmacendetalle", $data);
			}else{
				$cantidad_recoger = (double)$detalle[$key]->cantidad;
			}

			$existe = $this->db->query("select *from almacen.productoubicacion where codalmacen=".$_SESSION["phuyu_codalmacen"]." and codproducto=".$detalle[$key]->codproducto." and codunidad=".$detalle[$key]->codunidad)->result_array();
			if (count($existe) == 0) {
				$data = array(
					"codalmacen" => (int)$_SESSION["phuyu_codalmacen"], 
					"codproducto" => (int)$detalle[$key]->codproducto,
					"codunidad" => (int)$detalle[$key]->codunidad, 
					"codsucursal" => (int)$_SESSION["phuyu_codsucursal"],
					"stockactual" => 0, "stockactualreal" => 0
				);
				$estado = $this->db->insert("almacen.productoubicacion", $data);
				
				$existe = $this->db->query("select *from almacen.productoubicacion where codalmacen=".$_SESSION["phuyu_codalmacen"]." and codproducto=".$detalle[$key]->codproducto." and codunidad=".$detalle[$key]->codunidad)->result_array();
			}

			if( $_SESSION["phuyu_stockalmacen"] == 1 && $detalle[$key]->control == 1 && ($existe[0]["stockactualconvertido"] < $detalle[$key]->cantidad)){

				$informacion['success'] = false;
				$informacion['stock'][$key] = $existe[0]["stockactualconvertido"];
				$informacion['producto'][$key] = $detalle[$key]->producto;
				$informacion['unidad'][$key] = $producto[0]["unidad"];
			}

			if ($operacion == 0) {
				$data = array(
					"stockactual" => (double)round(($existe[0]["stockactual"] - $detalle[$key]->cantidad),3), 
					"ventarecogo" => (double)$existe[0]["ventarecogo"] + (double)$cantidad_recoger
				);
			}else{
				$data = array(
					"stockactual" => (double)round(($existe[0]["stockactual"] + $detalle[$key]->cantidad),3), 
					"comprarecogo" => (double)$existe[0]["comprarecogo"] + (double)$cantidad_recoger
				);
			}
			$this->db->where("codalmacen", $_SESSION["phuyu_codalmacen"]);
			$this->db->where("codproducto", $detalle[$key]->codproducto);
			$this->db->where("codunidad", $detalle[$key]->codunidad);
			$estado = $this->db->update("almacen.productoubicacion", $data);

			$stockconvertido = $this->db->query("select *from almacen.productoubicacion where codalmacen=".$_SESSION["phuyu_codalmacen"]." and codproducto=".$detalle[$key]->codproducto)->result_array();

			$factor = $this->db->query("select *from almacen.productounidades where codproducto=".$detalle[$key]->codproducto." and codunidad=".$detalle[$key]->codunidad)->result_array();

			foreach ($stockconvertido as $k => $value) {
				$productounidad = $this->db->query("select *from almacen.productounidades where codproducto=".$detalle[$key]->codproducto." and codunidad=".$value["codunidad"])->result_array();

				$stockc = ((float)$detalle[$key]->cantidad*(float)$factor[0]["factor"])/(float)$productounidad[0]["factor"];

				$stockrecoger = ((double)$cantidad_recoger*(float)$factor[0]["factor"])/(float)$productounidad[0]["factor"];

				if ($operacion == 0) {	
					$data = array(
						"stockactualconvertido" => (double)round(($value["stockactualconvertido"] - $stockc),3), 
						"ventarecogoconvertido" => (double)$value["ventarecogoconvertido"] + $stockrecoger
					);
				}else{
					$data = array(
						"stockactualconvertido" => (double)round(($value["stockactualconvertido"] + $stockc),3), 
						"comprarecogoconvertido" => (double)$existe[0]["comprarecogoconvertido"] + (double)$stockrecoger
					);
				}

				$this->db->where("codalmacen", $_SESSION["phuyu_codalmacen"]);
				$this->db->where("codproducto", $detalle[$key]->codproducto);
				$this->db->where("codunidad", $value["codunidad"]);
				$estado = $this->db->update("almacen.productoubicacion", $data);
			}

			if($codpedido != 0){
				if(isset($detalle[$key]->itempedido)){
	                $data = array(
					  "codpedido" => (int)$codpedido,
					  "codproducto" => (int)$detalle[$key]->codproducto,
					  "codunidad" => (int)$detalle[$key]->codunidad,
					  "itempedido" => (int)$detalle[$key]->itempedido,
					  "codkardex" => (int)$codkardex,
					  "itemkardex" => $item
					);

					$kardexpedido = $this->db->insert("kardex.kardexpedido",$data);
				}
			}

			if($codproforma != 0){
				if(isset($detalle[$key]->itemproforma)){
	                $data = array(
					  "codproforma" => (int)$codproforma,
					  "codproducto" => (int)$detalle[$key]->codproducto,
					  "codunidad" => (int)$detalle[$key]->codunidad,
					  "itemproforma" => (int)$detalle[$key]->itemproforma,
					  "codkardex" => (int)$codkardex,
					  "itemkardex" => $item
					);

					$kardexproforma = $this->db->insert("kardex.kardexproforma",$data);
				}
			}
		}
		return $informacion;
	}

	function phuyu_correlativo($codkardex,$codcomprobantetipo, $seriecomprobante){
		$actual = $this->db->query("select nrocorrelativo from caja.comprobantes where codcomprobantetipo=".$codcomprobantetipo." and seriecomprobante='".$seriecomprobante."' and codsucursal=".$_SESSION["phuyu_codsucursal"]." and estado=1")->result_array();

		$nrocorrelativo = (int)($actual[0]["nrocorrelativo"]) + 1;
		$data = array(
			"nrocorrelativo" => $nrocorrelativo
		);
		$this->db->where("codsucursal", $_SESSION["phuyu_codsucursal"]);
		$this->db->where("codcomprobantetipo", $codcomprobantetipo);
		$this->db->where("seriecomprobante", $seriecomprobante);
		$estado = $this->db->update("caja.comprobantes", $data);

		// ACTUALIZAMOS EL NRO COMPROBANTE DE KARDEX //
		
		$nrocorrelativo = str_pad($nrocorrelativo, 8, "0", STR_PAD_LEFT);
		$data = array(
			"nrocomprobante" => $nrocorrelativo
		);
		$this->db->where("codkardex", $codkardex);
		$estado = $this->db->update("kardex.kardex", $data);

		return $estado;
	}

	function phuyu_corre_kardexalmacen($codkardexalmacen,$codcomprobantetipo, $seriecomprobante){
		$actual = $this->db->query("select nrocorrelativo from caja.comprobantes where codcomprobantetipo=".$codcomprobantetipo." and seriecomprobante='".$seriecomprobante."' and codsucursal=".$_SESSION["phuyu_codsucursal"]." and estado=1")->result_array();

		$nrocorrelativo = (int)($actual[0]["nrocorrelativo"]) + 1;
		$data = array(
			"nrocorrelativo" => $nrocorrelativo
		);
		$this->db->where("codsucursal", $_SESSION["phuyu_codsucursal"]);
		$this->db->where("codcomprobantetipo", $codcomprobantetipo);
		$this->db->where("seriecomprobante", $seriecomprobante);
		$estado = $this->db->update("caja.comprobantes", $data);

		// ACTUALIZAMOS EL NRO COMPROBANTE DE KARDEX //
		
		$nrocorrelativo = str_pad($nrocorrelativo, 8, "0", STR_PAD_LEFT);
		$data = array(
			"nrocomprobante" => $nrocorrelativo
		);
		$this->db->where("codkardexalmacen", $codkardexalmacen);
		$estado = $this->db->update("kardex.kardexalmacen", $data);

		return $estado;
	}

	function phuyu_kardexcorrelativo($codkardex,$codkardexalmacen, $codcomprobantetipo, $seriecomprobante){
		$actual = $this->db->query("select nrocorrelativo from caja.comprobantes where codcomprobantetipo=".$codcomprobantetipo." and seriecomprobante='".$seriecomprobante."' and codsucursal=".$_SESSION["phuyu_codsucursal"]." and estado=1")->result_array();

		$nrocorrelativo = (int)($actual[0]["nrocorrelativo"]) + 1;
		$data = array(
			"nrocorrelativo" => $nrocorrelativo
		);
		$this->db->where("codsucursal", $_SESSION["phuyu_codsucursal"]);
		$this->db->where("codcomprobantetipo", $codcomprobantetipo);
		$this->db->where("seriecomprobante", $seriecomprobante);
		$estado = $this->db->update("caja.comprobantes", $data);

		// ACTUALIZAMOS EL NRO COMPROBANTE DE KARDEX //
		
		$nrocorrelativo = str_pad($nrocorrelativo, 8, "0", STR_PAD_LEFT);
		$data = array(
			"nrocomprobante" => $nrocorrelativo
		);
		$this->db->where("codkardex", $codkardex);
		$estado = $this->db->update("kardex.kardex", $data);

		$this->db->where("codkardexalmacen", $codkardexalmacen);
		$estado = $this->db->update("kardex.kardexalmacen", $data);

		return $estado;
	}

	function phuyu_kardexalmacencorrelativo($codkardexalmacen, $codcomprobantetipo, $seriecomprobante){
		$actual = $this->db->query("select nrocorrelativo from caja.comprobantes where codcomprobantetipo=".$codcomprobantetipo." and seriecomprobante='".$seriecomprobante."' and codsucursal=".$_SESSION["phuyu_codsucursal"]." and estado=1")->result_array();

		$nrocorrelativo = (int)($actual[0]["nrocorrelativo"]) + 1;
		$data = array(
			"nrocorrelativo" => $nrocorrelativo
		);
		$this->db->where("codsucursal", $_SESSION["phuyu_codsucursal"]);
		$this->db->where("codcomprobantetipo", $codcomprobantetipo);
		$this->db->where("seriecomprobante", $seriecomprobante);
		$estado = $this->db->update("caja.comprobantes", $data);

		// ACTUALIZAMOS EL NRO COMPROBANTE DE KARDEX //
		
		$nrocorrelativo = str_pad($nrocorrelativo, 8, "0", STR_PAD_LEFT);
		$data = array(
			"nrocomprobante" => $nrocorrelativo
		);
		$this->db->where("codkardexalmacen", $codkardexalmacen);
		$estado = $this->db->update("kardex.kardexalmacen", $data);

		return $estado;
	}
}