<?php

class Kardex_model extends CI_Model
{

	public function __construct()
	{
		parent::__construct();
	}

	function phuyu_kardex($campos, $totales, $operacion = 0)
	{
		$data = array(
			"codsucursal" => (int)$_SESSION["phuyu_codsucursal"],
			"codalmacen" => (int)$_SESSION["phuyu_codalmacen"],
			"codusuario" => (int)$_SESSION["phuyu_codusuario"],
			"codpersona" => (int)$campos->codpersona,
			"codmovimientotipo" => (int)$campos->codmovimientotipo,
			"condicionpago" => (int)$campos->condicionpago,
			"codmoneda" => (int)$campos->codmoneda,
			"tipocambio" => (float)$campos->tipocambio,
			"fechacomprobante" => $campos->fechacomprobante,
			"fechakardex" => $campos->fechakardex,
			"hora" => date('H:i:s'),
			"codcomprobantetipo" => (int)$campos->codcomprobantetipo,
			"seriecomprobante" => $campos->seriecomprobante,
			"nrocomprobante" => $campos->nro,
			"valorventa" => (float)$totales->valorventa,
			"porcdescuento" => (float)$campos->porcdescuento,
			"descglobal" => (float)$totales->descglobal,
			"descuentos" => (float)$totales->descuentos,
			"porcigv" => (float)$_SESSION["phuyu_igv"],
			"igv" => (float)$totales->igv,
			"porcicbper" => (float)$_SESSION["phuyu_icbper"],
			"icbper" => (float)$totales->icbper,
			"importe" => (float)$totales->importe,
			"flete" => (float)$totales->flete,
			"gastos" => (float)$totales->gastos,
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
			$actual = $this->db->query("select nrocorrelativo from caja.comprobantes where codcomprobantetipo=" . $campos->codcomprobantetipo . " and seriecomprobante='" . $campos->seriecomprobante . "' and codsucursal=" . $_SESSION["phuyu_codsucursal"] . " and estado=1")->result_array();

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

	function phuyu_kardexalmacen($codkardex, $comprobantealmacen, $campos)
	{
		$serie = $this->db->query("select seriecomprobante from caja.comprobantes where codcomprobantetipo=" . $comprobantealmacen . " and codsucursal=" . $_SESSION["phuyu_codsucursal"] . " and codalmacen=" . $_SESSION["phuyu_codalmacen"] . " and estado=1")->result_array();

		$data = array(
			"codsucursal" => (int)$_SESSION["phuyu_codsucursal"],
			"codalmacen" => (int)$_SESSION["phuyu_codalmacen"],
			"codusuario" => (int)$_SESSION["phuyu_codusuario"],
			"codkardex" => (int)$codkardex,
			"codmovimientotipo" => (int)$campos->codmovimientotipo,
			"fechakardex" => $campos->fechakardex,
			"codcomprobantetipo" => $comprobantealmacen,
			"seriecomprobante" => $serie[0]["seriecomprobante"]
		);
		$estado = $this->db->insert("kardex.kardexalmacen", $data);
		$codkardexalmacen = $this->db->insert_id("kardex.kardexalmacen_codkardexalmacen_seq");

		/* GENERAR CORRELATIVO DEL KARDEX ALMACEN */

		$actual = $this->db->query("select nrocorrelativo from caja.comprobantes where codcomprobantetipo=" . $comprobantealmacen . " and seriecomprobante='" . $serie[0]["seriecomprobante"] . "' and codsucursal=" . $_SESSION["phuyu_codsucursal"] . " and estado=1")->result_array();

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

	function phuyu_kardexdetalle_original($codkardex, $codkardexalmacen, $detalle, $retirar, $operacion = 0, $codpedido = 0, $codproforma = 0, $tipocambio = 1)
	{
		$item = 0;
		$estado = 1;
		$informacion['success'] = true;
		foreach ($detalle as $key => $value) {
			if (!isset($detalle[$key]->flete)) {
				$detalle[$key]->flete = 0;
			}

			// registro de series por compras
			if (count($detalle[$key]->series) > 0) {
				foreach ($detalle[$key]->series as $serie) {
					$dataSeries = array(
						"codproducto" => (int)$detalle[$key]->codproducto,
						"serie_codigo" => $serie->serie_codigo,
						"estado" => 'EN_ALMACEN',
						'fecha_ingreso' => date('Y-m-d H:i:s'), // ✅ FECHA ACTUAL
						'codalmacen' => $_SESSION["phuyu_codalmacen"], // ✅ Agregar almacén
						'codkardex' => $codkardex // ✅ Relacionar con el kardex
					);
					// ✅ Guardar en la base de datos
					$this->db->insert('almacen.series', $dataSeries);
				}
			}
			// fin de series

			$producto = $this->db->query("select pr.*,u.descripcion AS unidad 
			from almacen.productounidades pr 
			inner join almacen.unidades u ON pr.codunidad = u.codunidad 
			where pr.codproducto=" . $detalle[$key]->codproducto . " and pr.codunidad=" . $detalle[$key]->codunidad)->result_array();

			if ($operacion == 0) {
				$preciocompra = (float)$producto[0]['preciocompra'];
				$preciocosto = (float)$producto[0]['preciocosto'];
			} else {
				$preciocompra = ((float)$detalle[$key]->precio * (float)$tipocambio);
				$preciocosto = (((float)$detalle[$key]->precio + (float)$detalle[$key]->flete) * (float)$tipocambio);

				$data = array(
					"preciocompra" => (float)$preciocompra,
					"preciocosto" => (float)$preciocosto
				);

				$this->db->where("codproducto", $detalle[$key]->codproducto);
				$this->db->where("codunidad", $detalle[$key]->codunidad);
				$estado = $this->db->update("almacen.productounidades", $data);

				$this->db->where("codproducto", $detalle[$key]->codproducto);
				$this->db->where("codalmacen", $_SESSION["phuyu_codalmacen"]);
				$this->db->where("codunidad", $detalle[$key]->codunidad);
				$estado = $this->db->update("almacen.productoubicacion", $data);
			}

			if ($retirar == 1) {
				$recogido = (float)$detalle[$key]->cantidad;
			} else {
				$recogido = 0;
			}

			$item = $item + 1;
			$data = array(
				"codkardex" => (int)$codkardex,
				"codproducto" => (int)$detalle[$key]->codproducto,
				"codunidad" => (int)$detalle[$key]->codunidad,
				"item" => $item,
				"cantidad" => (float)$detalle[$key]->cantidad,
				"preciobruto" => (float)$detalle[$key]->preciobruto,
				"porcdescuento" => (float)$detalle[$key]->porcdescuento,
				"descuento" => (float)$detalle[$key]->descuento,
				"preciosinigv" => (float)$detalle[$key]->preciosinigv,
				"preciounitario" => (float)$detalle[$key]->precio,
				"preciorefunitario" => (float)$detalle[$key]->preciorefunitario,
				"codafectacionigv" => $detalle[$key]->codafectacionigv,
				"igv" => (float)$detalle[$key]->igv,
				"conicbper" => (float)$detalle[$key]->conicbper,
				"icbper" => (float)$detalle[$key]->icbper,
				"valorventa" => (float)$detalle[$key]->valorventa,
				"subtotal" => (float)$detalle[$key]->subtotal,
				"descripcion" => $detalle[$key]->descripcion,
				"recoger" => (int)$retirar,
				"recogido" => (float)$recogido,
				"flete" => $detalle[$key]->flete,
				"preciocompra" => $preciocompra,
				"preciocosto" => $preciocosto
			);
			$estado = $this->db->insert("kardex.kardexdetalle", $data);

			$cantidad_recoger = 0;
			if ($retirar == 1) {
				$data = array(
					"codkardexalmacen" => (int)$codkardexalmacen,
					"codproducto" => (int)$detalle[$key]->codproducto,
					"codunidad" => (int)$detalle[$key]->codunidad,
					"item" => $item,
					"codalmacen" => (int)$_SESSION["phuyu_codalmacen"],
					"codsucursal" => (int)$_SESSION["phuyu_codsucursal"],
					"cantidad" => (float)$detalle[$key]->cantidad
				);
				$estado = $this->db->insert("kardex.kardexalmacendetalle", $data);
			} else {
				$cantidad_recoger = (float)$detalle[$key]->cantidad;
			}

			$existe = $this->db->query("select *from almacen.productoubicacion where codalmacen=" . $_SESSION["phuyu_codalmacen"] . " and codproducto=" . $detalle[$key]->codproducto . " and codunidad=" . $detalle[$key]->codunidad)->result_array();
			if (count($existe) == 0) {
				$data = array(
					"codalmacen" => (int)$_SESSION["phuyu_codalmacen"],
					"codproducto" => (int)$detalle[$key]->codproducto,
					"codunidad" => (int)$detalle[$key]->codunidad,
					"codsucursal" => (int)$_SESSION["phuyu_codsucursal"],
					"stockactual" => 0,
					"stockactualreal" => 0
				);
				$estado = $this->db->insert("almacen.productoubicacion", $data);

				$existe = $this->db->query("select *from almacen.productoubicacion where codalmacen=" . $_SESSION["phuyu_codalmacen"] . " and codproducto=" . $detalle[$key]->codproducto . " and codunidad=" . $detalle[$key]->codunidad)->result_array();
			}

			if ($_SESSION["phuyu_stockalmacen"] == 1 && $detalle[$key]->control == 1 && ($existe[0]["stockactualconvertido"] < $detalle[$key]->cantidad)) {

				$informacion['success'] = false;
				$informacion['stock'][$key] = $existe[0]["stockactualconvertido"];
				$informacion['producto'][$key] = $detalle[$key]->producto;
				$informacion['unidad'][$key] = $producto[0]["unidad"];
			}

			if ($operacion == 0) {
				$data = array(
					"stockactual" => (float)round(($existe[0]["stockactual"] - $detalle[$key]->cantidad), 3),
					"ventarecogo" => (float)$existe[0]["ventarecogo"] + (float)$cantidad_recoger
				);
			} else {
				$data = array(
					"stockactual" => (float)round(($existe[0]["stockactual"] + $detalle[$key]->cantidad), 3),
					"comprarecogo" => (float)$existe[0]["comprarecogo"] + (float)$cantidad_recoger
				);
			}
			$this->db->where("codalmacen", $_SESSION["phuyu_codalmacen"]);
			$this->db->where("codproducto", $detalle[$key]->codproducto);
			$this->db->where("codunidad", $detalle[$key]->codunidad);
			$estado = $this->db->update("almacen.productoubicacion", $data);

			$stockconvertido = $this->db->query("select *from almacen.productoubicacion where codalmacen=" . $_SESSION["phuyu_codalmacen"] . " and codproducto=" . $detalle[$key]->codproducto)->result_array();

			$factor = $this->db->query("select *from almacen.productounidades where codproducto=" . $detalle[$key]->codproducto . " and codunidad=" . $detalle[$key]->codunidad)->result_array();

			foreach ($stockconvertido as $k => $value) {
				$productounidad = $this->db->query("select *from almacen.productounidades where codproducto=" . $detalle[$key]->codproducto . " and codunidad=" . $value["codunidad"])->result_array();

				$stockc = ((float)$detalle[$key]->cantidad * (float)$factor[0]["factor"]) / (float)$productounidad[0]["factor"];

				$stockrecoger = ((float)$cantidad_recoger * (float)$factor[0]["factor"]) / (float)$productounidad[0]["factor"];

				if ($operacion == 0) {
					$data = array(
						"stockactualconvertido" => (float)round(($value["stockactualconvertido"] - $stockc), 3),
						"ventarecogoconvertido" => (float)$value["ventarecogoconvertido"] + $stockrecoger
					);
				} else {
					$data = array(
						"stockactualconvertido" => (float)round(($value["stockactualconvertido"] + $stockc), 3),
						"comprarecogoconvertido" => (float)$existe[0]["comprarecogoconvertido"] + (float)$stockrecoger
					);
				}

				$this->db->where("codalmacen", $_SESSION["phuyu_codalmacen"]);
				$this->db->where("codproducto", $detalle[$key]->codproducto);
				$this->db->where("codunidad", $value["codunidad"]);
				$estado = $this->db->update("almacen.productoubicacion", $data);
			}

			if ($codpedido != 0) {
				if (isset($detalle[$key]->itempedido)) {
					$data = array(
						"codpedido" => (int)$codpedido,
						"codproducto" => (int)$detalle[$key]->codproducto,
						"codunidad" => (int)$detalle[$key]->codunidad,
						"itempedido" => (int)$detalle[$key]->itempedido,
						"codkardex" => (int)$codkardex,
						"itemkardex" => $item
					);

					$kardexpedido = $this->db->insert("kardex.kardexpedido", $data);
				}
			}

			if ($codproforma != 0) {
				if (isset($detalle[$key]->itemproforma)) {
					$data = array(
						"codproforma" => (int)$codproforma,
						"codproducto" => (int)$detalle[$key]->codproducto,
						"codunidad" => (int)$detalle[$key]->codunidad,
						"itemproforma" => (int)$detalle[$key]->itemproforma,
						"codkardex" => (int)$codkardex,
						"itemkardex" => $item
					);

					$kardexproforma = $this->db->insert("kardex.kardexproforma", $data);
				}
			}
		}
		return $informacion;
	}

	/**
	 * Registra el detalle de un movimiento de kardex (compra/venta) y actualiza stocks/series.
	 *
	 * @param int   $codkardex          Código del kardex (cabecera del movimiento).
	 * @param int   $codkardexalmacen   Código del kardex por almacén (si aplica retirar).
	 * @param array $detalle            Lista de ítems (productos) con cantidades, precios, etc.
	 * @param int   $retirar            1 = generar registro de retiro/recogo, 0 = no.
	 * @param int   $operacion          0 = SALIDA/VENTA, !=0 = ENTRADA/COMPRA.
	 * @param int   $codpedido          Código de pedido para trazabilidad (opcional).
	 * @param int   $codproforma        Código de proforma para trazabilidad (opcional).
	 * @param float $tipocambio         Tipo de cambio aplicado a los precios (en compras).
	 *
	 * @return array $informacion       success=true/false y otros datos de validación (stock/series).
	 */
	function phuyu_kardexdetalle($codkardex, $codkardexalmacen, $detalle, $retirar, $operacion = 0, $codpedido = 0, $codproforma = 0, $tipocambio = 1)
	{
		$item = 0;                    // Contador correlativo de ítems del kardex
		$estado = 1;                  // Bandera de estado para operaciones de BD
		$informacion['success'] = true; // Resultado general del proceso
		$estado_serie = 'EN_PROVEEDOR'; //SOLO SE REGITRO LA COMPRA SI INGRESO DE STOCK
		$esCompra = ($operacion != 0);                 // true si es ENTRADA/COMPRA
		$esRecepcion = ($esCompra && $retirar == 1);

		foreach ($detalle as $key => $value) {

			// Si no viene flete definido en el ítem, inicialízalo a 0
			if (!isset($detalle[$key]->flete)) {
				$detalle[$key]->flete = 0;
			}
			// ============================================================
			// Obtiene datos del producto-unidad (incluye unidad de medida)
			// ============================================================
			$producto = $this->db->query("
            select pr.*, u.descripcion AS unidad
            from almacen.productounidades pr
            inner join almacen.unidades u ON pr.codunidad = u.codunidad
            where pr.codproducto = " . $detalle[$key]->codproducto . "
            and pr.codunidad   = " . $detalle[$key]->codunidad . "")->result_array();

			// ============================================================
			// Precios de compra/costo según tipo de operación
			// - VENTA (operacion == 0): usa precios ya guardados en productounidades
			// - COMPRA (operacion != 0): recalcula con tipocambio y actualiza costos
			// ============================================================
			if ($operacion == 0) {
				// SALIDA/VENTA: toma precios actuales configurados
				$preciocompra = (float)$producto[0]['preciocompra'];
				$preciocosto  = (float)$producto[0]['preciocosto'];
			} else {
				// ENTRADA/COMPRA: calcula precio de compra y costo
				$preciocompra = ((float)$detalle[$key]->precio * (float)$tipocambio);
				$preciocosto  = (((float)$detalle[$key]->precio + (float)$detalle[$key]->flete) * (float)$tipocambio);

				// Actualiza precio de compra y costo en productounidades
				$data = array(
					"preciocompra" => (float)$preciocompra,
					"preciocosto"  => (float)$preciocosto
				);

				$this->db->where("codproducto", $detalle[$key]->codproducto);
				$this->db->where("codunidad",   $detalle[$key]->codunidad);
				$estado = $this->db->update("almacen.productounidades", $data);

				// También actualiza esos precios en productoubicacion del almacén actual
				$this->db->where("codproducto", $detalle[$key]->codproducto);
				$this->db->where("codalmacen",  $_SESSION["phuyu_codalmacen"]);
				$this->db->where("codunidad",   $detalle[$key]->codunidad);
				$estado = $this->db->update("almacen.productoubicacion", $data);
			}

			// ============================================================
			// Marcado de “recoger/retirar”: cantidad a retirar si corresponde
			// ============================================================
			if ($retirar == 1) {
				$recogido = (float)$detalle[$key]->cantidad;
			} else {
				$recogido = 0;
			}

			// ============================================================
			// Insert del DETALLE DEL KARDEX (traza contable del movimiento)
			// ============================================================
			$item = $item + 1; // correlativo ítem
			$data = array(
				"codkardex"         => (int)$codkardex,
				"codproducto"       => (int)$detalle[$key]->codproducto,
				"codunidad"         => (int)$detalle[$key]->codunidad,
				"item"              => $item,
				"cantidad"          => (float)$detalle[$key]->cantidad,
				"preciobruto"       => (float)$detalle[$key]->preciobruto,
				"porcdescuento"     => (float)$detalle[$key]->porcdescuento,
				"descuento"         => (float)$detalle[$key]->descuento,
				"preciosinigv"      => (float)$detalle[$key]->preciosinigv,
				"preciounitario"    => (float)$detalle[$key]->precio,
				"preciorefunitario" => (float)$detalle[$key]->preciorefunitario,
				"codafectacionigv"  => $detalle[$key]->codafectacionigv,
				"igv"               => (float)$detalle[$key]->igv,
				"conicbper"         => (float)$detalle[$key]->conicbper,
				"icbper"            => (float)$detalle[$key]->icbper,
				"valorventa"        => (float)$detalle[$key]->valorventa,
				"subtotal"          => (float)$detalle[$key]->subtotal,
				"descripcion"       => $detalle[$key]->descripcion,
				"recoger"           => (int)$retirar,
				"recogido"          => (float)$recogido,
				"flete"             => $detalle[$key]->flete,
				"preciocompra"      => $preciocompra, // según bloque previo
				"preciocosto"       => $preciocosto   // según bloque previo
			);
			// Inserta una fila en kardex.kardexdetalle (aplica para compra/venta)
			$estado = $this->db->insert("kardex.kardexdetalle", $data);
			// ============================================================
			// REGISTRO DE SERIES 
			//  $operacion != 0 referencia a ENTRADA/COMPRA
			// ============================================================

			//OPERACION PARA COMPRAS SERIES
			$lineSeries = isset($detalle[$key]->series) ? $detalle[$key]->series : [];
			if ($operacion != 0 && !empty($lineSeries) && is_array($lineSeries)) {
				foreach ($lineSeries as $serie) {

					$estado_serie = $retirar ? 'EN_ALMACEN' : 'EN_PROVEEDOR';
					$dataSeries = [
						"codproducto"   => (int)$detalle[$key]->codproducto,
						"serie_codigo"  => $serie->serie_codigo,
						"estado"        => $estado_serie,
						"fecha_ingreso" => date('Y-m-d H:i:s'),
						"codsucursal"      => (int)$_SESSION["phuyu_codsucursal"],
						"codalmacen"    => (int)$_SESSION["phuyu_codalmacen"],
						"codkardex"     => (int)$codkardex
					];
					$this->db->insert('almacen.series', $dataSeries);
				}
			}
			//FIN OPERACION PARA COMPRAS EN SERIES
			// INICIO DE ACTUALIZACION PARA SERIES EN VENTAS
			if ($operacion == 0 && !empty($detalle[$key]->controlarseries)) { // VENTA CON CONTROL DE SERIES
				// Actualiza el estado de la serie a 'VENDIDO' o 'RESERVADO'
				$serieId     = isset($detalle[$key]->id_serie) ? (int)$detalle[$key]->id_serie : null;
				$serieCodigo = $detalle[$key]->serie_codigo;
				$serieRow = $this->db->get_where('almacen.series', ['id_serie' => $serieId])->row_array(); // CONSULTO O BUSCO LA SERIE 
				$estado_serie = ($retirar == 1) ? 'VENDIDO' : 'RESERVADO';

				if (!empty($serieRow)) {
					$this->db->where('id_serie', $serieId)
							->update('almacen.series', [
								'estado'          => $estado_serie, // ver nota sobre estados abajo	
								'codkardex_egreso' => $retirar ? (int)$codkardex : null,
								'fecha_egreso'     => $retirar ? date('Y-m-d H:i:s') : null,
							]);
				}
			}
				
			//FIN ACTULIZACION SERIE EN COMPRAS
			// === FIN REGISTRO DE SERIES ===

			// ============================================================
			// Si $retirar == 1, inserta detalle del kardex por almacén
			// Si no, usa la cantidad para el acumulado “recogo”
			// ============================================================
			$cantidad_recoger = 0;
			if ($retirar == 1) {
				$data = array(
					"codkardexalmacen" => (int)$codkardexalmacen,
					"codproducto"      => (int)$detalle[$key]->codproducto,
					"codunidad"        => (int)$detalle[$key]->codunidad,
					"item"             => $item,
					"codalmacen"       => (int)$_SESSION["phuyu_codalmacen"],
					"codsucursal"      => (int)$_SESSION["phuyu_codsucursal"],
					"cantidad"         => (float)$detalle[$key]->cantidad
				);
				$estado = $this->db->insert("kardex.kardexalmacendetalle", $data);
			} else {
				$cantidad_recoger = (float)$detalle[$key]->cantidad;
			}

			// ============================================================
			// Asegura existencia de fila en productoubicacion (por almacén)
			// Si no existe, la crea en 0
			// ============================================================
			$existe = $this->db->query("
            select * from almacen.productoubicacion
            where codalmacen = " . $_SESSION["phuyu_codalmacen"] . "
              and codproducto = " . $detalle[$key]->codproducto . "
              and codunidad   = " . $detalle[$key]->codunidad . "
        ")->result_array();

			if (count($existe) == 0) {
				$data = array(
					"codalmacen"       => (int)$_SESSION["phuyu_codalmacen"],
					"codproducto"      => (int)$detalle[$key]->codproducto,
					"codunidad"        => (int)$detalle[$key]->codunidad,
					"codsucursal"      => (int)$_SESSION["phuyu_codsucursal"],
					"stockactual"      => 0,
					"stockactualreal"  => 0
				);
				$estado = $this->db->insert("almacen.productoubicacion", $data);

				// Vuelve a leer para tener la fila recién creada
				$existe = $this->db->query("
                select * from almacen.productoubicacion
                where codalmacen = " . $_SESSION["phuyu_codalmacen"] . "
                  and codproducto = " . $detalle[$key]->codproducto . "
                  and codunidad   = " . $detalle[$key]->codunidad . "
            ")->result_array();
			}

			// ============================================================
			// Validación de stock convertido para productos controlados
			// (si stock convertido es menor a la cantidad pedida, marca error)
			// ============================================================
			if (
				$_SESSION["phuyu_stockalmacen"] == 1 &&
				$detalle[$key]->control == 1 &&
				($existe[0]["stockactualconvertido"] < $detalle[$key]->cantidad)
			) {
				$informacion['success']      = false;
				$informacion['stock'][$key]  = $existe[0]["stockactualconvertido"];
				$informacion['producto'][$key] = $detalle[$key]->producto;
				$informacion['unidad'][$key] = $producto[0]["unidad"];
			}

			// ============================================================
			// Actualización de STOCK BASE (en productoubicacion - unidad actual)
			// - VENTA (operacion == 0): Resta stock y acumula ventarecogo
			// - COMPRA (operacion != 0): Suma stock y acumula comprarecogo
			// ============================================================
			if ($operacion == 0) {
				$data = array(
					"stockactual" => (float)round(($existe[0]["stockactual"] - $detalle[$key]->cantidad), 3),
					"ventarecogo" => (float)$existe[0]["ventarecogo"] + (float)$cantidad_recoger
				);
			} else {
				$data = array(
					"stockactual"  => (float)round(($existe[0]["stockactual"] + $detalle[$key]->cantidad), 3),
					"comprarecogo" => (float)$existe[0]["comprarecogo"] + (float)$cantidad_recoger
				);
			}
			$this->db->where("codalmacen", $_SESSION["phuyu_codalmacen"]);
			$this->db->where("codproducto", $detalle[$key]->codproducto);
			$this->db->where("codunidad",   $detalle[$key]->codunidad);
			$estado = $this->db->update("almacen.productoubicacion", $data);

			// ============================================================
			// Actualización de STOCK CONVERTIDO (todas las presentaciones)
			// Convierte la cantidad en función de factores y suma/resta
			// ============================================================
			$stockconvertido = $this->db->query("
            select * from almacen.productoubicacion
            where codalmacen = " . $_SESSION["phuyu_codalmacen"] . "
              and codproducto = " . $detalle[$key]->codproducto . "
        ")->result_array();

			// Factor de la unidad base del ítem actual
			$factor = $this->db->query("
            select * from almacen.productounidades
            where codproducto = " . $detalle[$key]->codproducto . "
              and codunidad   = " . $detalle[$key]->codunidad . "
        ")->result_array();

			foreach ($stockconvertido as $k => $value) {
				// Factor de la unidad de cada presentación
				$productounidad = $this->db->query("
                select * from almacen.productounidades
                where codproducto = " . $detalle[$key]->codproducto . "
                  and codunidad   = " . $value["codunidad"] . "
            ")->result_array();

				// Cantidad convertida a la unidad $value["codunidad"]
				$stockc = (
					(float)$detalle[$key]->cantidad * (float)$factor[0]["factor"]
				) / (float)$productounidad[0]["factor"];

				// Cantidad a recoger convertida
				$stockrecoger = (
					(float)$cantidad_recoger * (float)$factor[0]["factor"]
				) / (float)$productounidad[0]["factor"];

				if ($operacion == 0) {
					// SALIDA/VENTA: resta convertido y acumula ventas recogidas convertidas
					$data = array(
						"stockactualconvertido" => (float)round(($value["stockactualconvertido"] - $stockc), 3),
						"ventarecogoconvertido" => (float)$value["ventarecogoconvertido"] + $stockrecoger
					);
				} else {
					// ENTRADA/COMPRA: suma convertido y acumula compras recogidas convertidas
					// (nota: aquí usas $existe[0] para comprarecogoconvertido; dejamos igual)
					$data = array(
						"stockactualconvertido"  => (float)round(($value["stockactualconvertido"] + $stockc), 3),
						"comprarecogoconvertido" => (float)$existe[0]["comprarecogoconvertido"] + (float)$stockrecoger
					);
				}

				$this->db->where("codalmacen", $_SESSION["phuyu_codalmacen"]);
				$this->db->where("codproducto", $detalle[$key]->codproducto);
				$this->db->where("codunidad",   $value["codunidad"]);
				$estado = $this->db->update("almacen.productoubicacion", $data);
			}

			// ============================================================
			// Trazabilidad con PEDIDO (si se proporcionó $codpedido)
			// ============================================================
			if ($codpedido != 0) {
				if (isset($detalle[$key]->itempedido)) {
					$data = array(
						"codpedido"   => (int)$codpedido,
						"codproducto" => (int)$detalle[$key]->codproducto,
						"codunidad"   => (int)$detalle[$key]->codunidad,
						"itempedido"  => (int)$detalle[$key]->itempedido,
						"codkardex"   => (int)$codkardex,
						"itemkardex"  => $item
					);
					$kardexpedido = $this->db->insert("kardex.kardexpedido", $data);
				}
			}

			// ============================================================
			// Trazabilidad con PROFORMA (si se proporcionó $codproforma)
			// ============================================================
			if ($codproforma != 0) {
				if (isset($detalle[$key]->itemproforma)) {
					$data = array(
						"codproforma" => (int)$codproforma,
						"codproducto" => (int)$detalle[$key]->codproducto,
						"codunidad"   => (int)$detalle[$key]->codunidad,
						"itemproforma" => (int)$detalle[$key]->itemproforma,
						"codkardex"   => (int)$codkardex,
						"itemkardex"  => $item
					);
					$kardexproforma = $this->db->insert("kardex.kardexproforma", $data);
				}
			}
		}

		// Devuelve estado general y, si hubo problemas, info de stock/series
		$this->db->trans_complete();
		if ($this->db->trans_status() === FALSE) {
			return ['success' => false, 'error' => 'Transacción fallida'];
		}
		return $informacion;
		
	}


	function phuyu_correlativo($codkardex, $codcomprobantetipo, $seriecomprobante)
	{
		$actual = $this->db->query("select nrocorrelativo from caja.comprobantes where codcomprobantetipo=" . $codcomprobantetipo . " and seriecomprobante='" . $seriecomprobante . "' and codsucursal=" . $_SESSION["phuyu_codsucursal"] . " and estado=1")->result_array();

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

	function phuyu_corre_kardexalmacen($codkardexalmacen, $codcomprobantetipo, $seriecomprobante)
	{
		$actual = $this->db->query("select nrocorrelativo from caja.comprobantes where codcomprobantetipo=" . $codcomprobantetipo . " and seriecomprobante='" . $seriecomprobante . "' and codsucursal=" . $_SESSION["phuyu_codsucursal"] . " and estado=1")->result_array();

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

	function phuyu_kardexcorrelativo($codkardex, $codkardexalmacen, $codcomprobantetipo, $seriecomprobante)
	{
		$actual = $this->db->query("select nrocorrelativo from caja.comprobantes where codcomprobantetipo=" . $codcomprobantetipo . " and seriecomprobante='" . $seriecomprobante . "' and codsucursal=" . $_SESSION["phuyu_codsucursal"] . " and estado=1")->result_array();

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

	function phuyu_kardexalmacencorrelativo($codkardexalmacen, $codcomprobantetipo, $seriecomprobante)
	{
		$actual = $this->db->query("select nrocorrelativo from caja.comprobantes where codcomprobantetipo=" . $codcomprobantetipo . " and seriecomprobante='" . $seriecomprobante . "' and codsucursal=" . $_SESSION["phuyu_codsucursal"] . " and estado=1")->result_array();

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
