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

	private function phuyu_producto_tiene_receta($codproducto, $codunidad)
	{
		$receta = $this->db->query("
			select 1
			from restaurante.recetas
			where codproducto = " . (int)$codproducto . "
			and codunidad = " . (int)$codunidad . "
			and estado = 1
			limit 1
		")->row_array();

		return !empty($receta);
	}

	private function phuyu_factor_producto($codproducto, $codunidad)
	{
		$factor = $this->db->query("
			select coalesce(nullif(factor,0),1) as factor
			from almacen.productounidades
			where codproducto = " . (int)$codproducto . "
			and codunidad = " . (int)$codunidad . "
			and estado = 1
			limit 1
		")->row_array();

		return empty($factor) ? 1 : (float)$factor["factor"];
	}

	private function phuyu_explotar_receta($codproducto, $codunidad, $cantidad, $origen, $visitados = array())
	{
		$clave = (int)$codproducto . "-" . (int)$codunidad;
		if (isset($visitados[$clave])) {
			return array();
		}
		$visitados[$clave] = true;

		$receta = $this->db->query("
			select r.codproducto_receta as codproducto, r.codunidad_receta as codunidad, r.cantidad,
			coalesce(rc.rendimiento,1) as rendimiento
			from restaurante.recetas r
			left join restaurante.recetas_config rc on(rc.codproducto=r.codproducto and rc.codunidad=r.codunidad and rc.estado=1)
			where r.codproducto = " . (int)$codproducto . "
			and r.codunidad = " . (int)$codunidad . "
			and r.estado = 1
			order by r.item
		")->result_array();

		if (count($receta) == 0) {
			return array(array(
				"codproducto" => (int)$codproducto,
				"codunidad" => (int)$codunidad,
				"cantidad" => (float)$cantidad,
				"origen" => $origen,
				"es_receta" => 0
			));
		}

		$lineas = array();
		foreach ($receta as $value) {
			$rendimiento = (float)$value["rendimiento"] <= 0 ? 1 : (float)$value["rendimiento"];
			$cantidad_receta = ((float)$value["cantidad"] * (float)$cantidad) / $rendimiento;

			if ($this->phuyu_producto_tiene_receta($value["codproducto"], $value["codunidad"])) {
				$lineas = array_merge($lineas, $this->phuyu_explotar_receta($value["codproducto"], $value["codunidad"], $cantidad_receta, $origen, $visitados));
			} else {
				$lineas[] = array(
					"codproducto" => (int)$value["codproducto"],
					"codunidad" => (int)$value["codunidad"],
					"cantidad" => (float)$cantidad_receta,
					"origen" => $origen,
					"es_receta" => 1
				);
			}
		}

		return $lineas;
	}

	private function phuyu_movimientos_stock_producto($detalle, $item, $operacion)
	{
		$origen = array(
			"codproducto" => (int)$detalle->codproducto,
			"codunidad" => (int)$detalle->codunidad,
			"item" => (int)$item,
			"cantidad" => (float)$detalle->cantidad
		);

		if ($operacion == 0 && isset($_SESSION["phuyu_rubro"]) && (int)$_SESSION["phuyu_rubro"] == 3 && $this->phuyu_producto_tiene_receta($detalle->codproducto, $detalle->codunidad)) {
			return $this->phuyu_explotar_receta($detalle->codproducto, $detalle->codunidad, $detalle->cantidad, $origen);
		}

		return array(array(
			"codproducto" => (int)$detalle->codproducto,
			"codunidad" => (int)$detalle->codunidad,
			"cantidad" => (float)$detalle->cantidad,
			"origen" => $origen,
			"es_receta" => 0
		));
	}

	private function phuyu_consolidar_movimientos_stock($movimientos)
	{
		$consolidados = array();

		foreach ($movimientos as $movimiento) {
			$clave = (int)$movimiento["codproducto"] . "-" . (int)$movimiento["codunidad"];

			if (!isset($consolidados[$clave])) {
				$consolidados[$clave] = $movimiento;
				$consolidados[$clave]["cantidad"] = 0;
			}

			$consolidados[$clave]["cantidad"] += (float)$movimiento["cantidad"];
			$consolidados[$clave]["es_receta"] = ((int)$consolidados[$clave]["es_receta"] == 1 || (int)$movimiento["es_receta"] == 1) ? 1 : 0;
		}

		return array_values($consolidados);
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

							// DEBUG ESPECÍFICO
				$serieSeleccionada = $detalle[$key]->serie_seleccionada;

				$serieId = $serieSeleccionada->id_serie;
				$serieCodigo = $serieSeleccionada->serie_codigo;
				
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
			$movimientos_stock = $this->phuyu_consolidar_movimientos_stock(
				$this->phuyu_movimientos_stock_producto($detalle[$key], $item, $operacion)
			);
			foreach ($movimientos_stock as $movimiento_stock) {
				$cantidad_recoger = ($retirar == 1) ? 0 : (float)$movimiento_stock["cantidad"];

				if ($retirar == 1) {
					$data = array(
						"codkardexalmacen" => (int)$codkardexalmacen,
						"codproducto"      => (int)$movimiento_stock["codproducto"],
						"codunidad"        => (int)$movimiento_stock["codunidad"],
						"item"             => $item,
						"codalmacen"       => (int)$_SESSION["phuyu_codalmacen"],
						"codsucursal"      => (int)$_SESSION["phuyu_codsucursal"],
						"cantidad"         => (float)$movimiento_stock["cantidad"],
						"es_receta"        => (int)$movimiento_stock["es_receta"],
						"codproducto_origen" => (int)$movimiento_stock["origen"]["codproducto"],
						"codunidad_origen" => (int)$movimiento_stock["origen"]["codunidad"],
						"item_origen"      => (int)$movimiento_stock["origen"]["item"],
						"cantidad_origen"  => (float)$movimiento_stock["origen"]["cantidad"]
					);
					$estado = $this->db->insert("kardex.kardexalmacendetalle", $data);
				}

				$codproducto_stock = (int)$movimiento_stock["codproducto"];
				$codunidad_stock = (int)$movimiento_stock["codunidad"];
				$cantidad_stock = (float)$movimiento_stock["cantidad"];

				$existe = $this->db->query("
					select * from almacen.productoubicacion
					where codalmacen = " . $_SESSION["phuyu_codalmacen"] . "
					and codproducto = " . $codproducto_stock . "
					and codunidad = " . $codunidad_stock . "
				")->result_array();

				if (count($existe) == 0) {
					$data = array(
						"codalmacen" => (int)$_SESSION["phuyu_codalmacen"],
						"codproducto" => $codproducto_stock,
						"codunidad" => $codunidad_stock,
						"codsucursal" => (int)$_SESSION["phuyu_codsucursal"],
						"stockactual" => 0,
						"stockactualreal" => 0
					);
					$estado = $this->db->insert("almacen.productoubicacion", $data);
					$existe = $this->db->query("
						select * from almacen.productoubicacion
						where codalmacen = " . $_SESSION["phuyu_codalmacen"] . "
						and codproducto = " . $codproducto_stock . "
						and codunidad = " . $codunidad_stock . "
					")->result_array();
				}

				if (
					$_SESSION["phuyu_stockalmacen"] == 1 &&
					$detalle[$key]->control == 1 &&
					($existe[0]["stockactualconvertido"] < $cantidad_stock)
				) {
					$informacion['success'] = false;
					$informacion['stock'][$key] = $existe[0]["stockactualconvertido"];
					$informacion['producto'][$key] = $detalle[$key]->producto;
					$informacion['unidad'][$key] = $producto[0]["unidad"];
				}

				if ($operacion == 0) {
					$data = array(
						"stockactual" => (float)round(((float)$existe[0]["stockactual"] - $cantidad_stock), 3),
						"ventarecogo" => (float)$existe[0]["ventarecogo"] + (float)$cantidad_recoger
					);
				} else {
					$data = array(
						"stockactual" => (float)round(((float)$existe[0]["stockactual"] + $cantidad_stock), 3),
						"comprarecogo" => (float)$existe[0]["comprarecogo"] + (float)$cantidad_recoger
					);
				}
				$this->db->where("codalmacen", $_SESSION["phuyu_codalmacen"]);
				$this->db->where("codproducto", $codproducto_stock);
				$this->db->where("codunidad", $codunidad_stock);
				$estado = $this->db->update("almacen.productoubicacion", $data);

				$stockconvertido = $this->db->query("
					select * from almacen.productoubicacion
					where codalmacen = " . $_SESSION["phuyu_codalmacen"] . "
					and codproducto = " . $codproducto_stock . "
				")->result_array();

				$factor = $this->phuyu_factor_producto($codproducto_stock, $codunidad_stock);
				foreach ($stockconvertido as $k => $value) {
					$productounidad_factor = $this->phuyu_factor_producto($codproducto_stock, $value["codunidad"]);
					$stockc = ($cantidad_stock * $factor) / $productounidad_factor;
					$stockrecoger = ((float)$cantidad_recoger * $factor) / $productounidad_factor;

					if ($operacion == 0) {
						$data = array(
							"stockactualconvertido" => (float)round(((float)$value["stockactualconvertido"] - $stockc), 3),
							"ventarecogoconvertido" => (float)$value["ventarecogoconvertido"] + $stockrecoger
						);
					} else {
						$data = array(
							"stockactualconvertido" => (float)round(((float)$value["stockactualconvertido"] + $stockc), 3),
							"comprarecogoconvertido" => (float)$value["comprarecogoconvertido"] + (float)$stockrecoger
						);
					}

					$this->db->where("codalmacen", $_SESSION["phuyu_codalmacen"]);
					$this->db->where("codproducto", $codproducto_stock);
					$this->db->where("codunidad", $value["codunidad"]);
					$estado = $this->db->update("almacen.productoubicacion", $data);
				}
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
