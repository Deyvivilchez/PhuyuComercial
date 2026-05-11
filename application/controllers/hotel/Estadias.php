<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Estadias extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model("phuyu_model");
		$this->load->model("Hotel_model");
		$this->load->model("Kardex_model");
		$this->load->model("Caja_model");
	}

	private function validar_persona_comprobante_hotel($info, $campos){
		$codcomprobante = isset($campos->codcomprobantetipo) ? (int)$campos->codcomprobantetipo : 0;
		$esFactura = in_array($codcomprobante, [10, 25], true);
		$esBoleta = in_array($codcomprobante, [12, 26], true);

		$persona = [
			"codpersona" => (int)$info["estadia"]["codpersona"],
			"razonsocial" => $info["estadia"]["cliente"],
			"direccion" => $info["estadia"]["direccion"],
			"documento" => $info["estadia"]["documento"] ?? "",
			"coddocumentotipo" => (int)($info["estadia"]["coddocumentotipo"] ?? 0)
		];

		if ($esFactura) {
			$codpersona = isset($campos->codpersona_facturacion) ? (int)$campos->codpersona_facturacion : 0;
			if ($codpersona <= 0) {
				if ($persona["codpersona"] != 2 && $persona["coddocumentotipo"] == 4 && strlen(trim($persona["documento"])) == 11) {
					return ["estado" => 1, "persona" => $persona];
				}
				return ["estado" => 0, "mensaje" => "PARA FACTURA SELECCIONE UN CLIENTE CON RUC"];
			}
			if ($codpersona == 2) {
				return ["estado" => 0, "mensaje" => "CLIENTES VARIOS NO PUEDE EMITIR FACTURA"];
			}

			$personaFactura = $this->db->query(
				"select codpersona, razonsocial, direccion, documento, coddocumentotipo
				from public.personas
				where codpersona=? and estado=1
				limit 1",
				[$codpersona]
			)->row_array();

			if (empty($personaFactura) || (int)$personaFactura["coddocumentotipo"] != 4 || strlen(trim($personaFactura["documento"])) != 11) {
				return ["estado" => 0, "mensaje" => "LA FACTURA SOLO SE EMITE A CLIENTES CON RUC DE 11 DIGITOS"];
			}

			return ["estado" => 1, "persona" => $personaFactura];
		}

		if ($esBoleta) {
			$documento = trim($persona["documento"]);
			if ($persona["coddocumentotipo"] == 4) {
				return ["estado" => 0, "mensaje" => "LA BOLETA NO SE PUEDE EMITIR A UN CLIENTE CON RUC"];
			}
			if ($documento === "" || strlen($documento) != 8) {
				return ["estado" => 0, "mensaje" => "LA BOLETA REQUIERE DNI DE 8 DIGITOS"];
			}
		}

		return ["estado" => 1, "persona" => $persona];
	}

	private function descripcion_metodo_pago($pagos, $condicionpago){
		if ((int)$condicionpago == 2) {
			return "CREDITO";
		}
		$metodos = [];
		if ((double)($pagos->monto_efectivo ?? 0) > 0) {
			$metodos[] = "EFECTIVO";
		}
		if ((int)($pagos->codtipopago_tarjeta ?? 0) > 0 && (double)($pagos->monto_tarjeta ?? 0) > 0) {
			$metodos[] = "TARJETA/BANCO";
		}
		return empty($metodos) ? "SIN DETALLE" : implode(" + ", $metodos);
	}

	public function checkin(){
		if ($this->input->is_ajax_request()) {
			if (!isset($_SESSION["phuyu_usuario"])) { echo json_encode("e"); return; }
			$this->request = json_decode(file_get_contents("php://input"));
			$habitacion = $this->db->query("select * from hotel.habitaciones where codhabitacion=? and codsucursal=? and estado=1", [(int)$this->request->codhabitacion, (int)$_SESSION["phuyu_codsucursal"]])->row_array();
			if (empty($habitacion) || !in_array((int)$habitacion["situacion"], [1,2])) {
				echo json_encode(["estado" => 0, "mensaje" => "HABITACION NO DISPONIBLE PARA CHECK-IN"]);
				return;
			}
			if ($this->Hotel_model->habitacion_tiene_estadia_activa((int)$this->request->codhabitacion)) {
				echo json_encode(["estado" => 0, "mensaje" => "LA HABITACION YA TIENE UNA ESTADIA ACTIVA"]);
				return;
			}

			$persona = $this->db->query("select * from public.personas where codpersona=? and estado=1", [(int)$this->request->codpersona])->row_array();
			if (empty($persona)) {
				echo json_encode(["estado" => 0, "mensaje" => "CLIENTE NO ENCONTRADO"]);
				return;
			}

			$this->db->trans_begin();
			$fechaSalida = $this->request->fecha_checkout ?: $this->request->fecha_checkin;
			$noches = max(1, (int)floor((strtotime($fechaSalida) - strtotime($this->request->fecha_checkin)) / 86400));
			$precio = (double)($this->request->precio_noche ?: $habitacion["preciobase"]);
			$alojamiento = round($noches * $precio, 2);

			$campos = ["codsucursal","codalmacen","codpersona","codusuario","codempleado","fecha_checkin","fecha_checkout","noches","alojamiento","importe","cliente","direccion","observacion","situacion"];
			$valores = [
				(int)$_SESSION["phuyu_codsucursal"], (int)$_SESSION["phuyu_codalmacen"], (int)$persona["codpersona"], (int)$_SESSION["phuyu_codusuario"],
				(int)($this->request->codempleado ?: 0), $this->request->fecha_checkin, $fechaSalida, $noches, $alojamiento, $alojamiento,
				$persona["razonsocial"], $persona["direccion"], $this->request->observacion, 1
			];
			$codestadia = $this->phuyu_model->phuyu_guardar("hotel.estadias", $campos, $valores, "true");

			$campos = ["codestadia","codhabitacion","precio_noche","noches","subtotal"];
			$valores = [(int)$codestadia, (int)$habitacion["codhabitacion"], $precio, $noches, $alojamiento];
			$estado = $this->phuyu_model->phuyu_guardar("hotel.estadia_habitaciones", $campos, $valores);

			$estado = $this->phuyu_model->phuyu_guardar("hotel.estadia_huespedes", ["codestadia","codpersona","titular"], [(int)$codestadia, (int)$persona["codpersona"], 1]);
			$estado = $this->phuyu_model->phuyu_editar("hotel.habitaciones", ["situacion"], [3], "codhabitacion", (int)$habitacion["codhabitacion"]);

			if ($this->db->trans_status() === FALSE || $estado != 1) {
				$this->db->trans_rollback();
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUDO REGISTRAR CHECK-IN"]);
				return;
			}
			$this->db->trans_commit();
			echo json_encode(["estado" => 1, "codestadia" => $codestadia]);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function cambiar_habitacion(){
		if ($this->input->is_ajax_request()) {
			if (!isset($_SESSION["phuyu_usuario"])) { echo json_encode("e"); return; }
			$this->request = json_decode(file_get_contents("php://input"));
			$codestadia = (int)$this->request->codestadia;
			$codhabitacionOrigen = (int)$this->request->codhabitacion_origen;
			$codhabitacionDestino = (int)$this->request->codhabitacion_destino;

			if ($codhabitacionOrigen == $codhabitacionDestino) {
				echo json_encode(["estado" => 0, "mensaje" => "SELECCIONE UNA HABITACION DIFERENTE"]);
				return;
			}

			$info = $this->Hotel_model->estadia_detalle($codestadia);
			if (empty($info["estadia"]) || (int)$info["estadia"]["situacion"] != 1) {
				echo json_encode(["estado" => 0, "mensaje" => "ESTADIA NO ACTIVA"]);
				return;
			}

			$origen = $this->db->query(
				"select eh.*, h.numero
				from hotel.estadia_habitaciones eh
				inner join hotel.habitaciones h on(h.codhabitacion=eh.codhabitacion)
				where eh.codestadia=? and eh.codhabitacion=? and eh.estado=1",
				[$codestadia, $codhabitacionOrigen]
			)->row_array();
			if (empty($origen)) {
				echo json_encode(["estado" => 0, "mensaje" => "LA HABITACION ORIGEN NO PERTENECE A LA ESTADIA"]);
				return;
			}

			$destino = $this->db->query(
				"select h.*, ht.descripcion as tipo
				from hotel.habitaciones h
				inner join hotel.habitacion_tipos ht on(ht.codhabitaciontipo=h.codhabitaciontipo)
				where h.codhabitacion=? and h.codsucursal=? and h.estado=1",
				[$codhabitacionDestino, (int)$_SESSION["phuyu_codsucursal"]]
			)->row_array();
			if (empty($destino) || (int)$destino["situacion"] != 1) {
				echo json_encode(["estado" => 0, "mensaje" => "HABITACION DESTINO NO DISPONIBLE"]);
				return;
			}
			$situacionOrigen = isset($this->request->situacion_origen) ? (int)$this->request->situacion_origen : 4;
			if (!in_array($situacionOrigen, [1,4])) {
				$situacionOrigen = 4;
			}

			$desde = $info["estadia"]["fecha_checkin"];
			$hasta = $info["estadia"]["fecha_checkout"] ?: date("Y-m-d", strtotime($desde." +1 day"));
			if (strtotime($hasta) <= strtotime($desde)) {
				$hasta = date("Y-m-d", strtotime($desde." +1 day"));
			}

			$validacion = $this->Hotel_model->habitacion_disponible_rango($codhabitacionDestino, $desde, $hasta);
			if ((int)$validacion["estado"] != 1) {
				echo json_encode($validacion);
				return;
			}

			$this->db->trans_begin();
			$this->db->query(
				"insert into hotel.estadia_cambios_habitacion
					(codestadia, codhabitacion_origen, codhabitacion_destino, codusuario, fecha, hora, observacion, estado)
				values (?, ?, ?, ?, current_date, current_time, ?, 1)",
				[$codestadia, $codhabitacionOrigen, $codhabitacionDestino, (int)$_SESSION["phuyu_codusuario"], isset($this->request->observacion) ? $this->request->observacion : ""]
			);

			$this->db->where("codestadia", $codestadia);
			$this->db->where("codhabitacion", $codhabitacionOrigen);
			$estado = $this->db->update("hotel.estadia_habitaciones", [
				"codhabitacion" => $codhabitacionDestino
			]);
			$this->db->where("codestadia", $codestadia);
			$this->db->where("codhabitacion", $codhabitacionOrigen);
			$this->db->where("situacion", 1);
			$this->db->where("estado", 1);
			$this->db->update("hotel.consumos_habitacion", ["codhabitacion" => $codhabitacionDestino]);

			$nuevoAlojamiento = $this->db->query(
				"select coalesce(sum(subtotal),0) as alojamiento
				from hotel.estadia_habitaciones
				where codestadia=? and estado=1",
				[$codestadia]
			)->row_array();

			$this->phuyu_model->phuyu_editar("hotel.habitaciones", ["situacion"], [$situacionOrigen], "codhabitacion", $codhabitacionOrigen);
			$this->phuyu_model->phuyu_editar("hotel.habitaciones", ["situacion"], [3], "codhabitacion", $codhabitacionDestino);
			$this->phuyu_model->phuyu_editar(
				"hotel.estadias",
				["alojamiento", "importe"],
				[(double)$nuevoAlojamiento["alojamiento"], (double)$nuevoAlojamiento["alojamiento"] + (double)$info["estadia"]["consumos"]],
				"codestadia",
				$codestadia
			);

			if ($this->db->trans_status() === FALSE || !$estado) {
				$this->db->trans_rollback();
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUDO CAMBIAR HABITACION"]);
				return;
			}
			$this->db->trans_commit();
			echo json_encode(["estado" => 1, "mensaje" => "HABITACION CAMBIADA"]);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function checkout(){
		if ($this->input->is_ajax_request()) {
			if (!isset($_SESSION["phuyu_usuario"])) { echo json_encode("e"); return; }
			$this->request = json_decode(file_get_contents("php://input"));
			if ((int)$_SESSION["phuyu_codcontroldiario"] == 0) {
				echo json_encode(["estado" => 0, "mensaje" => "CAJA NO APERTURADA"]);
				return;
			}

			$info = $this->Hotel_model->estadia_detalle((int)$this->request->campos->codestadia);
			if (empty($info["estadia"]) || (int)$info["estadia"]["situacion"] != 1) {
				echo json_encode(["estado" => 0, "mensaje" => "ESTADIA NO ACTIVA"]);
				return;
			}
			if ((int)$info["estadia"]["codsucursal"] != (int)$_SESSION["phuyu_codsucursal"] || (int)$info["estadia"]["codalmacen"] != (int)$_SESSION["phuyu_codalmacen"]) {
				echo json_encode(["estado" => 0, "mensaje" => "LA ESTADIA NO PERTENECE A LA SUCURSAL O ALMACEN ACTUAL"]);
				return;
			}
			$validacionPersona = $this->validar_persona_comprobante_hotel($info, $this->request->campos);
			if ((int)$validacionPersona["estado"] != 1) {
				echo json_encode(["estado" => 0, "mensaje" => $validacionPersona["mensaje"]]);
				return;
			}
			$personaComprobante = $validacionPersona["persona"];
			if ((int)$this->request->campos->condicionpago == 2 && (int)$personaComprobante["codpersona"] == 2) {
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUEDE REGISTRAR CREDITO A CLIENTES VARIOS"]);
				return;
			}

			$alojamiento = $this->Hotel_model->producto_alojamiento();
			if (empty($alojamiento)) {
				echo json_encode(["estado" => 0, "mensaje" => "CONFIGURE UN PRODUCTO DE ALOJAMIENTO EN ALMACEN.PRODUCTOS"]);
				return;
			}

			$this->db->trans_begin();
			$controlaStockSistema = isset($_SESSION["phuyu_stockalmacen"]) ? (int)$_SESSION["phuyu_stockalmacen"] : 0;
			$detalle = [];
			$alojamientoPendiente = (double)($info["estadia"]["alojamiento_pendiente"] ?? $info["estadia"]["alojamiento"]);
			foreach ($info["habitaciones"] as $habitacion) {
				if ($alojamientoPendiente <= 0) { break; }
				$importeHabitacion = min((double)$habitacion["subtotal"], $alojamientoPendiente);
				if ($importeHabitacion <= 0) { continue; }
				$detalle[] = $this->Hotel_model->armar_item_kardex(
					$alojamiento["codproducto"], $alojamiento["codunidad"], $alojamiento["descripcion"], $alojamiento["unidad"],
					1, $importeHabitacion, "ALOJAMIENTO HAB. ".$habitacion["numero"]." - ".$habitacion["noches"]." NOCHE(S)",
					(int)$alojamiento["afectoigvventa"], (int)$alojamiento["afectoicbper"], 0
				);
				$alojamientoPendiente = round($alojamientoPendiente - $importeHabitacion, 2);
			}
			foreach ($info["consumos"] as $consumo) {
				if ((int)$consumo["situacion"] != 1) { continue; }
				$detalle[] = $this->Hotel_model->armar_item_kardex(
					$consumo["codproducto"], $consumo["codunidad"], $consumo["producto"], $consumo["unidad"],
					$consumo["cantidad"], $consumo["preciounitario"], $consumo["descripcion"],
					(int)$consumo["afectoigvventa"], (int)$consumo["afectoicbper"], ($controlaStockSistema == 1 ? (int)$consumo["controlstock"] : 0),
					($controlaStockSistema == 1 ? (int)$consumo["controlarseries"] : 0),
					($controlaStockSistema == 1 && (int)$consumo["controlarseries"] == 1 ? (object)["id_serie" => (int)$consumo["id_serie"], "serie_codigo" => $consumo["serie_codigo"]] : null)
				);
			}
			$erroresDetalle = $this->Hotel_model->validar_detalle_venta($detalle);
			if (!empty($erroresDetalle)) {
				$this->db->trans_rollback();
				echo json_encode(["estado" => 0, "mensaje" => implode("\n", $erroresDetalle)]);
				return;
			}
			if (count($detalle) == 0) {
				$campos = $this->request->campos;
				$campos->fechacomprobante = empty($campos->fechacomprobante) ? date("Y-m-d") : $campos->fechacomprobante;
				$estado = $this->phuyu_model->phuyu_editar("hotel.estadias", ["fecha_checkout","hora_checkout","situacion"], [$campos->fechacomprobante, date("H:i:s"), 2], "codestadia", (int)$info["estadia"]["codestadia"]);
				if ((int)($info["estadia"]["codreserva"] ?? 0) > 0) {
					$this->phuyu_model->phuyu_editar("hotel.reservas", ["situacion"], [4], "codreserva", (int)$info["estadia"]["codreserva"]);
				}
				foreach ($info["habitaciones"] as $habitacion) {
					$this->phuyu_model->phuyu_editar("hotel.habitaciones", ["situacion"], [(int)($this->request->destino_habitacion ?? 4)], "codhabitacion", (int)$habitacion["codhabitacion"]);
				}
				if ($this->db->trans_status() === FALSE || $estado != 1) {
					$this->db->trans_rollback();
					echo json_encode(["estado" => 0, "mensaje" => "NO SE PUDO CERRAR LA ESTADIA"]);
					return;
				}
				$this->db->trans_commit();
				echo json_encode(["estado" => 1, "codkardex" => 0, "mensaje" => "ESTADIA CERRADA SIN COBRO PENDIENTE"]);
				return;
			}

			$totales = (object)["flete"=>0,"gastos"=>0,"descuentos"=>0,"descglobal"=>0,"icbper"=>0,"valorventa"=>0,"igv"=>0,"importe"=>0,"interes"=>0];
			foreach ($detalle as $item) {
				$totales->valorventa += (double)$item->valorventa;
				$totales->igv += (double)$item->igv;
				$totales->icbper += (double)$item->icbper;
				$totales->importe += (double)$item->subtotal;
			}
			$totales->valorventa = round($totales->valorventa, 2);
			$totales->igv = round($totales->igv, 2);
			$totales->importe = round($totales->importe, 2);
			if ((int)$this->request->campos->condicionpago == 1) {
				$pagado = (double)$this->request->pagos->monto_efectivo + (double)$this->request->pagos->monto_tarjeta - (double)$this->request->pagos->vuelto_efectivo;
				if (round($pagado, 2) < round($totales->importe, 2)) {
					$this->db->trans_rollback();
					echo json_encode(["estado" => 0, "mensaje" => "PAGO INSUFICIENTE PARA REGISTRAR CAJA"]);
					return;
				}
			}

			$campos = $this->request->campos;
			$campos->codsucursal = (int)$_SESSION["phuyu_codsucursal"];
			$campos->codalmacen = (int)$_SESSION["phuyu_codalmacen"];
			$campos->codusuario = (int)$_SESSION["phuyu_codusuario"];
			$campos->codcaja = (int)$_SESSION["phuyu_codcaja"];
			$campos->codcontroldiario = (int)$_SESSION["phuyu_codcontroldiario"];
			$campos->codpersona = (int)$personaComprobante["codpersona"];
			$campos->cliente = $personaComprobante["razonsocial"];
			$campos->direccion = $personaComprobante["direccion"];
			$campos->codmovimientotipo = 20;
			$campos->codconcepto = ((int)$campos->condicionpago == 2) ? 15 : 13;
			$campos->descripcion = "CHECK-OUT HOTEL ESTADIA ".$info["estadia"]["codestadia"];
			$campos->observacion = $campos->descripcion;
			$campos->retirar = true;
			$campos->afectacaja = true;
			$campos->porcdescuento = 0;
			$campos->codcentrocosto = 0;
			$campos->nroplaca = "";
			$campos->codlote = isset($campos->codlote) ? (int)$campos->codlote : 0;
			$campos->conleyendaamazonia = 1;
			$campos->codmoneda = isset($campos->codmoneda) ? (int)$campos->codmoneda : 1;
			$campos->tipocambio = isset($campos->tipocambio) ? (double)$campos->tipocambio : 1;
			$campos->fechacomprobante = empty($campos->fechacomprobante) ? date("Y-m-d") : $campos->fechacomprobante;
			$campos->fechakardex = empty($campos->fechakardex) ? $campos->fechacomprobante : $campos->fechakardex;
			$campos->totalcredito = empty($campos->totalcredito) ? $totales->importe : (double)$campos->totalcredito;

			$codkardex = $this->Kardex_model->phuyu_kardex($campos, $totales, 0);
			if ((int)$codkardex <= 0) {
				$this->db->trans_rollback();
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUDO REGISTRAR LA VENTA"]);
				return;
			}
			$codkardexalmacen = $this->Kardex_model->phuyu_kardexalmacen($codkardex, 4, $campos);
			$resultadoDetalle = $this->Kardex_model->phuyu_kardexdetalle($codkardex, $codkardexalmacen, $detalle, true, 0);
			if (isset($resultadoDetalle["success"]) && $resultadoDetalle["success"] === false) {
				$this->db->trans_rollback();
				echo json_encode(["estado" => 0, "mensaje" => "STOCK INSUFICIENTE EN UNO O MAS CONSUMOS"]);
				return;
			}

			$codmovimiento = $this->Caja_model->phuyu_movimientos($codkardex, 1, 1, $totales->importe, $campos, $totales->importe);
			if ((int)$codmovimiento <= 0) {
				$this->db->trans_rollback();
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUDO REGISTRAR MOVIMIENTO DE CAJA"]);
				return;
			}
			$estado = 1;
			if ((int)$campos->condicionpago == 1) {
				$estado = $this->Caja_model->phuyu_movimientosdetalle($codmovimiento, $this->request->pagos);
			}else{
				if (empty($this->request->cuotas)) {
					$this->request->cuotas = [(object)[
						"nrocuota" => 1,
						"fechavence" => date("Y-m-d", strtotime($campos->fechacomprobante." +".(int)$campos->nrodias." days")),
						"importe" => $totales->importe,
						"interes" => 0,
						"total" => $campos->totalcredito
					]];
				}
				$estado = $this->Caja_model->phuyu_credito($codkardex, $codmovimiento, 1, $campos, $totales, $this->request->cuotas, "");
			}
			if ($estado != 1) {
				$this->db->trans_rollback();
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUDO REGISTRAR EL PAGO EN CAJA"]);
				return;
			}

			if ((int)$campos->codcomprobantetipo == 10 || (int)$campos->codcomprobantetipo == 12) {
				$kardex = $this->db->query("select nrocomprobante from kardex.kardex where codkardex=?", [(int)$codkardex])->row_array();
				$tipoSunat = ((int)$campos->codcomprobantetipo == 10) ? "01" : "03";
				$xml = $_SESSION["phuyu_ruc"]."-".$tipoSunat."-".$campos->seriecomprobante."-".$kardex["nrocomprobante"];
				$estado = $this->phuyu_model->phuyu_guardar("sunat.kardexsunat", ["codkardex","codsucursal","codusuario","fechacreado","nombre_xml"], [(int)$codkardex,(int)$_SESSION["phuyu_codsucursal"],(int)$_SESSION["phuyu_codusuario"],$campos->fechacomprobante,$xml]);
			}

			$this->db->where("codestadia", (int)$info["estadia"]["codestadia"]);
			$this->db->where("situacion", 1);
			$this->db->update("hotel.consumos_habitacion", ["situacion" => 2, "codkardex" => (int)$codkardex, "fechafacturado" => $campos->fechacomprobante]);

			$alojamientoFacturado = (double)($info["estadia"]["alojamiento_pendiente"] ?? $info["estadia"]["alojamiento"]);
			$consumosPendientes = (double)$totales->importe - $alojamientoFacturado;
			$consumosTotal = (double)$info["estadia"]["consumos"] + $consumosPendientes;
			$importeTotal = (double)$info["estadia"]["alojamiento"] + $consumosTotal;
			$estado = $this->phuyu_model->phuyu_editar("hotel.estadias", ["codkardex","fecha_checkout","hora_checkout","consumos","importe","situacion"], [(int)$codkardex, $campos->fechacomprobante, date("H:i:s"), $consumosTotal, $importeTotal, 2], "codestadia", (int)$info["estadia"]["codestadia"]);
			if ((int)($info["estadia"]["codreserva"] ?? 0) > 0) {
				$this->phuyu_model->phuyu_editar("hotel.reservas", ["situacion"], [4], "codreserva", (int)$info["estadia"]["codreserva"]);
			}

			$checklistLimpieza = json_encode([
				"Cambio de sabanas",
				"Cambio de toallas",
				"Limpieza de bano",
				"Limpieza de ducha",
				"Barrido/trapeado de piso",
				"Reposicion de papel higienico",
				"Reposicion de jabon/shampoo",
				"Retiro de basura",
				"Desinfeccion general"
			]);
			foreach ($info["habitaciones"] as $habitacion) {
				$codhabitacion = (int)$habitacion["codhabitacion"];
				$this->phuyu_model->phuyu_editar("hotel.habitaciones", ["situacion"], [4], "codhabitacion", $codhabitacion);

				$ordenActiva = $this->db->query(
					"select codlimpieza
					from hotel.limpieza_habitaciones
					where codhabitacion=? and estado=1 and coalesce(estado_orden,1) in (1,2)
					order by codlimpieza desc
					limit 1",
					[$codhabitacion]
				)->row_array();
				if (empty($ordenActiva)) {
					$this->phuyu_model->phuyu_guardar(
						"hotel.limpieza_habitaciones",
						["codhabitacion","codusuario","codresponsable","observacion","tipo_limpieza","checklist","estado_orden"],
						[
							$codhabitacion,
							(int)$_SESSION["phuyu_codusuario"],
							0,
							"Limpieza normal generada por check-out de estadia ".(int)$info["estadia"]["codestadia"],
							"normal",
							$checklistLimpieza,
							1
						]
					);
				}
			}

			if ($this->db->trans_status() === FALSE || $estado != 1) {
				$this->db->trans_rollback();
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUDO CERRAR LA ESTADIA"]);
				return;
			}
			$this->db->trans_commit();
			echo json_encode(["estado" => 1, "codkardex" => $codkardex]);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function cobrar_ocupacion(){
		if ($this->input->is_ajax_request()) {
			if (!isset($_SESSION["phuyu_usuario"])) { echo json_encode("e"); return; }
			$this->request = json_decode(file_get_contents("php://input"));
			if ((int)$_SESSION["phuyu_codcontroldiario"] == 0) {
				echo json_encode(["estado" => 0, "mensaje" => "CAJA NO APERTURADA"]);
				return;
			}

			$info = $this->Hotel_model->estadia_detalle((int)$this->request->campos->codestadia);
			if (empty($info["estadia"]) || (int)$info["estadia"]["situacion"] != 1) {
				echo json_encode(["estado" => 0, "mensaje" => "ESTADIA NO ACTIVA"]);
				return;
			}
			if ((int)$info["estadia"]["codsucursal"] != (int)$_SESSION["phuyu_codsucursal"] || (int)$info["estadia"]["codalmacen"] != (int)$_SESSION["phuyu_codalmacen"]) {
				echo json_encode(["estado" => 0, "mensaje" => "LA ESTADIA NO PERTENECE A LA SUCURSAL O ALMACEN ACTUAL"]);
				return;
			}
			$alojamientoPendiente = round((double)($info["estadia"]["alojamiento_pendiente"] ?? $info["estadia"]["alojamiento"]), 2);
			if ($alojamientoPendiente <= 0) {
				echo json_encode(["estado" => 0, "mensaje" => "LA OCUPACION YA TIENE EL ALOJAMIENTO COBRADO"]);
				return;
			}
			$validacionPersona = $this->validar_persona_comprobante_hotel($info, $this->request->campos);
			if ((int)$validacionPersona["estado"] != 1) {
				echo json_encode(["estado" => 0, "mensaje" => $validacionPersona["mensaje"]]);
				return;
			}
			$personaComprobante = $validacionPersona["persona"];
			if ((int)$this->request->campos->condicionpago == 2 && (int)$personaComprobante["codpersona"] == 2) {
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUEDE REGISTRAR CREDITO A CLIENTES VARIOS"]);
				return;
			}

			$alojamiento = $this->Hotel_model->producto_alojamiento();
			if (empty($alojamiento)) {
				echo json_encode(["estado" => 0, "mensaje" => "CONFIGURE UN PRODUCTO DE ALOJAMIENTO EN ALMACEN.PRODUCTOS"]);
				return;
			}

			$this->db->trans_begin();
			$detalle = [];
			$pendienteDetalle = $alojamientoPendiente;
			foreach ($info["habitaciones"] as $habitacion) {
				if ($pendienteDetalle <= 0) { break; }
				$importeHabitacion = min((double)$habitacion["subtotal"], $pendienteDetalle);
				if ($importeHabitacion <= 0) { continue; }
				$detalle[] = $this->Hotel_model->armar_item_kardex(
					$alojamiento["codproducto"], $alojamiento["codunidad"], $alojamiento["descripcion"], $alojamiento["unidad"],
					1, $importeHabitacion, "COBRO OCUPACION HAB. ".$habitacion["numero"]." - ".$habitacion["noches"]." NOCHE(S)",
					(int)$alojamiento["afectoigvventa"], (int)$alojamiento["afectoicbper"], 0
				);
				$pendienteDetalle = round($pendienteDetalle - $importeHabitacion, 2);
			}
			$erroresDetalle = $this->Hotel_model->validar_detalle_venta($detalle);
			if (!empty($erroresDetalle) || count($detalle) == 0) {
				$this->db->trans_rollback();
				echo json_encode(["estado" => 0, "mensaje" => empty($erroresDetalle) ? "NO HAY ALOJAMIENTO PENDIENTE" : implode("\n", $erroresDetalle)]);
				return;
			}

			$totales = (object)["flete"=>0,"gastos"=>0,"descuentos"=>0,"descglobal"=>0,"icbper"=>0,"valorventa"=>0,"igv"=>0,"importe"=>0,"interes"=>0];
			foreach ($detalle as $item) {
				$totales->valorventa += (double)$item->valorventa;
				$totales->igv += (double)$item->igv;
				$totales->icbper += (double)$item->icbper;
				$totales->importe += (double)$item->subtotal;
			}
			$totales->valorventa = round($totales->valorventa, 2);
			$totales->igv = round($totales->igv, 2);
			$totales->importe = round($totales->importe, 2);
			if ((int)$this->request->campos->condicionpago == 1) {
				$pagado = (double)$this->request->pagos->monto_efectivo + (double)$this->request->pagos->monto_tarjeta - (double)$this->request->pagos->vuelto_efectivo;
				if (round($pagado, 2) < round($totales->importe, 2)) {
					$this->db->trans_rollback();
					echo json_encode(["estado" => 0, "mensaje" => "PAGO INSUFICIENTE PARA REGISTRAR CAJA"]);
					return;
				}
			}

			$campos = $this->request->campos;
			$campos->codsucursal = (int)$_SESSION["phuyu_codsucursal"];
			$campos->codalmacen = (int)$_SESSION["phuyu_codalmacen"];
			$campos->codusuario = (int)$_SESSION["phuyu_codusuario"];
			$campos->codcaja = (int)$_SESSION["phuyu_codcaja"];
			$campos->codcontroldiario = (int)$_SESSION["phuyu_codcontroldiario"];
			$campos->codpersona = (int)$personaComprobante["codpersona"];
			$campos->cliente = $personaComprobante["razonsocial"];
			$campos->direccion = $personaComprobante["direccion"];
			$campos->codmovimientotipo = 20;
			$campos->codconcepto = ((int)$campos->condicionpago == 2) ? 15 : 13;
			$campos->descripcion = "COBRO OCUPACION HOTEL ESTADIA ".$info["estadia"]["codestadia"];
			$campos->observacion = $campos->descripcion;
			$campos->retirar = true;
			$campos->afectacaja = true;
			$campos->porcdescuento = 0;
			$campos->codcentrocosto = 0;
			$campos->nroplaca = "";
			$campos->codlote = isset($campos->codlote) ? (int)$campos->codlote : 0;
			$campos->conleyendaamazonia = 1;
			$campos->codmoneda = isset($campos->codmoneda) ? (int)$campos->codmoneda : 1;
			$campos->tipocambio = isset($campos->tipocambio) ? (double)$campos->tipocambio : 1;
			$campos->fechacomprobante = empty($campos->fechacomprobante) ? date("Y-m-d") : $campos->fechacomprobante;
			$campos->fechakardex = empty($campos->fechakardex) ? $campos->fechacomprobante : $campos->fechakardex;
			$campos->totalcredito = empty($campos->totalcredito) ? $totales->importe : (double)$campos->totalcredito;

			$codkardex = $this->Kardex_model->phuyu_kardex($campos, $totales, 0);
			if ((int)$codkardex <= 0) {
				$this->db->trans_rollback();
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUDO REGISTRAR LA VENTA"]);
				return;
			}
			$codkardexalmacen = $this->Kardex_model->phuyu_kardexalmacen($codkardex, 4, $campos);
			$resultadoDetalle = $this->Kardex_model->phuyu_kardexdetalle($codkardex, $codkardexalmacen, $detalle, true, 0);
			if (isset($resultadoDetalle["success"]) && $resultadoDetalle["success"] === false) {
				$this->db->trans_rollback();
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUDO REGISTRAR DETALLE DE ALOJAMIENTO"]);
				return;
			}

			$codmovimiento = $this->Caja_model->phuyu_movimientos($codkardex, 1, 1, $totales->importe, $campos, $totales->importe);
			if ((int)$codmovimiento <= 0) {
				$this->db->trans_rollback();
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUDO REGISTRAR MOVIMIENTO DE CAJA"]);
				return;
			}
			$estado = 1;
			if ((int)$campos->condicionpago == 1) {
				$estado = $this->Caja_model->phuyu_movimientosdetalle($codmovimiento, $this->request->pagos);
			}else{
				if (empty($this->request->cuotas)) {
					$this->request->cuotas = [(object)[
						"nrocuota" => 1,
						"fechavence" => date("Y-m-d", strtotime($campos->fechacomprobante." +".(int)$campos->nrodias." days")),
						"importe" => $totales->importe,
						"interes" => 0,
						"total" => $campos->totalcredito
					]];
				}
				$estado = $this->Caja_model->phuyu_credito($codkardex, $codmovimiento, 1, $campos, $totales, $this->request->cuotas, "");
			}
			if ($estado != 1) {
				$this->db->trans_rollback();
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUDO REGISTRAR EL PAGO EN CAJA"]);
				return;
			}

			if ((int)$campos->codcomprobantetipo == 10 || (int)$campos->codcomprobantetipo == 12) {
				$kardex = $this->db->query("select nrocomprobante from kardex.kardex where codkardex=?", [(int)$codkardex])->row_array();
				$tipoSunat = ((int)$campos->codcomprobantetipo == 10) ? "01" : "03";
				$xml = $_SESSION["phuyu_ruc"]."-".$tipoSunat."-".$campos->seriecomprobante."-".$kardex["nrocomprobante"];
				$estado = $this->phuyu_model->phuyu_guardar("sunat.kardexsunat", ["codkardex","codsucursal","codusuario","fechacreado","nombre_xml"], [(int)$codkardex,(int)$_SESSION["phuyu_codsucursal"],(int)$_SESSION["phuyu_codusuario"],$campos->fechacomprobante,$xml]);
			}

			$this->Hotel_model->asegurar_auditoria_estadias();
			$estadoPago = $this->db->insert("hotel.estadia_pagos", [
				"codestadia" => (int)$info["estadia"]["codestadia"],
				"codkardex" => (int)$codkardex,
				"codusuario" => (int)$_SESSION["phuyu_codusuario"],
				"codcaja" => (int)$_SESSION["phuyu_codcaja"],
				"codcontroldiario" => (int)$_SESSION["phuyu_codcontroldiario"],
				"fecha" => $campos->fechacomprobante,
				"hora" => date("H:i:s"),
				"tipo" => 1,
				"importe" => (double)$totales->importe,
				"metodo_pago" => $this->descripcion_metodo_pago($this->request->pagos, $campos->condicionpago),
				"observacion" => "Cobro de ocupacion sin liberar habitacion"
			]);

			if ($this->db->trans_status() === FALSE || !$estadoPago) {
				$this->db->trans_rollback();
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUDO REGISTRAR EL COBRO DE OCUPACION"]);
				return;
			}
			$this->db->trans_commit();
			echo json_encode(["estado" => 1, "codkardex" => $codkardex, "mensaje" => "OCUPACION COBRADA. LA HABITACION SIGUE OCUPADA"]);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function cancelar_ocupacion(){
		if ($this->input->is_ajax_request()) {
			if (!isset($_SESSION["phuyu_usuario"])) { echo json_encode("e"); return; }
			$this->request = json_decode(file_get_contents("php://input"));
			$codestadia = isset($this->request->codestadia) ? (int)$this->request->codestadia : 0;
			$motivo = isset($this->request->motivo) ? trim($this->request->motivo) : "";
			$confirmarPagos = isset($this->request->confirmar_pagos) ? (int)$this->request->confirmar_pagos : 0;

			if ($motivo === "") {
				echo json_encode(["estado" => 0, "mensaje" => "INGRESE MOTIVO DE CANCELACION"]);
				return;
			}

			$info = $this->Hotel_model->estadia_detalle($codestadia);
			if (empty($info["estadia"]) || (int)$info["estadia"]["situacion"] != 1) {
				echo json_encode(["estado" => 0, "mensaje" => "SOLO SE PUEDE CANCELAR UNA ESTADIA ACTIVA"]);
				return;
			}
			if ((int)$info["estadia"]["codsucursal"] != (int)$_SESSION["phuyu_codsucursal"]) {
				echo json_encode(["estado" => 0, "mensaje" => "LA ESTADIA NO PERTENECE A LA SUCURSAL ACTUAL"]);
				return;
			}

			$totalPagado = 0;
			foreach (($info["pagos"] ?? []) as $pago) {
				$totalPagado += (double)$pago["importe"];
			}
			if ($totalPagado > 0 && $confirmarPagos != 1) {
				echo json_encode([
					"estado" => 0,
					"requiere_confirmacion" => 1,
					"total_pagado" => round($totalPagado, 2),
					"mensaje" => "LA ESTADIA TIENE PAGOS REGISTRADOS. CONFIRME LA CANCELACION."
				]);
				return;
			}

			$this->db->trans_begin();
			$estado = $this->phuyu_model->phuyu_editar(
				"hotel.estadias",
				["situacion", "fecha_cancelacion", "hora_cancelacion", "codusuario_cancelacion", "motivo_cancelacion"],
				[3, date("Y-m-d"), date("H:i:s"), (int)$_SESSION["phuyu_codusuario"], $motivo],
				"codestadia",
				$codestadia
			);
			foreach ($info["habitaciones"] as $habitacion) {
				$this->phuyu_model->phuyu_editar("hotel.habitaciones", ["situacion"], [1], "codhabitacion", (int)$habitacion["codhabitacion"]);
			}
			$this->db->where("codestadia", $codestadia);
			$this->db->where("situacion", 1);
			$this->db->where("estado", 1);
			$this->db->update("hotel.consumos_habitacion", ["situacion" => 3]);

			if ($this->db->trans_status() === FALSE || $estado != 1) {
				$this->db->trans_rollback();
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUDO CANCELAR LA OCUPACION"]);
				return;
			}
			$this->db->trans_commit();
			echo json_encode(["estado" => 1, "mensaje" => "OCUPACION CANCELADA Y HABITACION LIBERADA"]);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function cobrar_consumos(){
		if ($this->input->is_ajax_request()) {
			if (!isset($_SESSION["phuyu_usuario"])) { echo json_encode("e"); return; }
			$this->request = json_decode(file_get_contents("php://input"));
			if ((int)$_SESSION["phuyu_codcontroldiario"] == 0) {
				echo json_encode(["estado" => 0, "mensaje" => "CAJA NO APERTURADA"]);
				return;
			}

			$info = $this->Hotel_model->estadia_detalle((int)$this->request->campos->codestadia);
			if (empty($info["estadia"]) || (int)$info["estadia"]["situacion"] != 1) {
				echo json_encode(["estado" => 0, "mensaje" => "ESTADIA NO ACTIVA"]);
				return;
			}
			if ((int)$info["estadia"]["codsucursal"] != (int)$_SESSION["phuyu_codsucursal"] || (int)$info["estadia"]["codalmacen"] != (int)$_SESSION["phuyu_codalmacen"]) {
				echo json_encode(["estado" => 0, "mensaje" => "LA ESTADIA NO PERTENECE A LA SUCURSAL O ALMACEN ACTUAL"]);
				return;
			}
			$validacionPersona = $this->validar_persona_comprobante_hotel($info, $this->request->campos);
			if ((int)$validacionPersona["estado"] != 1) {
				echo json_encode(["estado" => 0, "mensaje" => $validacionPersona["mensaje"]]);
				return;
			}
			$personaComprobante = $validacionPersona["persona"];
			if ((int)$this->request->campos->condicionpago == 2 && (int)$personaComprobante["codpersona"] == 2) {
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUEDE REGISTRAR CREDITO A CLIENTES VARIOS"]);
				return;
			}
			$controlaStockSistema = isset($_SESSION["phuyu_stockalmacen"]) ? (int)$_SESSION["phuyu_stockalmacen"] : 0;
			$detalle = [];
			foreach ($info["consumos"] as $consumo) {
				if ((int)$consumo["situacion"] != 1) { continue; }
				$detalle[] = $this->Hotel_model->armar_item_kardex(
					$consumo["codproducto"], $consumo["codunidad"], $consumo["producto"], $consumo["unidad"],
					$consumo["cantidad"], $consumo["preciounitario"], $consumo["descripcion"],
					(int)$consumo["afectoigvventa"], (int)$consumo["afectoicbper"], ($controlaStockSistema == 1 ? (int)$consumo["controlstock"] : 0),
					($controlaStockSistema == 1 ? (int)$consumo["controlarseries"] : 0),
					($controlaStockSistema == 1 && (int)$consumo["controlarseries"] == 1 ? (object)["id_serie" => (int)$consumo["id_serie"], "serie_codigo" => $consumo["serie_codigo"]] : null)
				);
			}
			$erroresDetalle = $this->Hotel_model->validar_detalle_venta($detalle);
			if (!empty($erroresDetalle)) {
				echo json_encode(["estado" => 0, "mensaje" => implode("\n", $erroresDetalle)]);
				return;
			}

			if (count($detalle) == 0) {
				echo json_encode(["estado" => 0, "mensaje" => "NO HAY CONSUMOS PENDIENTES"]);
				return;
			}

			$this->db->trans_begin();
			$totales = (object)["flete"=>0,"gastos"=>0,"descuentos"=>0,"descglobal"=>0,"icbper"=>0,"valorventa"=>0,"igv"=>0,"importe"=>0,"interes"=>0];
			foreach ($detalle as $item) {
				$totales->valorventa += (double)$item->valorventa;
				$totales->igv += (double)$item->igv;
				$totales->icbper += (double)$item->icbper;
				$totales->importe += (double)$item->subtotal;
			}
			$totales->valorventa = round($totales->valorventa, 2);
			$totales->igv = round($totales->igv, 2);
			$totales->importe = round($totales->importe, 2);
			if ((int)$this->request->campos->condicionpago == 1) {
				$pagado = (double)$this->request->pagos->monto_efectivo + (double)$this->request->pagos->monto_tarjeta - (double)$this->request->pagos->vuelto_efectivo;
				if (round($pagado, 2) < round($totales->importe, 2)) {
					$this->db->trans_rollback();
					echo json_encode(["estado" => 0, "mensaje" => "PAGO INSUFICIENTE PARA REGISTRAR CAJA"]);
					return;
				}
			}

			$campos = $this->request->campos;
			$campos->codsucursal = (int)$_SESSION["phuyu_codsucursal"];
			$campos->codalmacen = (int)$_SESSION["phuyu_codalmacen"];
			$campos->codusuario = (int)$_SESSION["phuyu_codusuario"];
			$campos->codcaja = (int)$_SESSION["phuyu_codcaja"];
			$campos->codcontroldiario = (int)$_SESSION["phuyu_codcontroldiario"];
			$campos->codpersona = (int)$personaComprobante["codpersona"];
			$campos->cliente = $personaComprobante["razonsocial"];
			$campos->direccion = $personaComprobante["direccion"];
			$campos->codmovimientotipo = 20;
			$campos->codconcepto = ((int)$campos->condicionpago == 2) ? 15 : 13;
			$campos->descripcion = "COBRO CONSUMOS HOTEL ESTADIA ".$info["estadia"]["codestadia"];
			$campos->observacion = $campos->descripcion;
			$campos->retirar = true;
			$campos->afectacaja = true;
			$campos->porcdescuento = 0;
			$campos->codcentrocosto = 0;
			$campos->nroplaca = "";
			$campos->codlote = isset($campos->codlote) ? (int)$campos->codlote : 0;
			$campos->conleyendaamazonia = 1;
			$campos->codmoneda = isset($campos->codmoneda) ? (int)$campos->codmoneda : 1;
			$campos->tipocambio = isset($campos->tipocambio) ? (double)$campos->tipocambio : 1;
			$campos->fechacomprobante = empty($campos->fechacomprobante) ? date("Y-m-d") : $campos->fechacomprobante;
			$campos->fechakardex = empty($campos->fechakardex) ? $campos->fechacomprobante : $campos->fechakardex;
			$campos->totalcredito = empty($campos->totalcredito) ? $totales->importe : (double)$campos->totalcredito;

			$codkardex = $this->Kardex_model->phuyu_kardex($campos, $totales, 0);
			if ((int)$codkardex <= 0) {
				$this->db->trans_rollback();
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUDO REGISTRAR LA VENTA"]);
				return;
			}
			$codkardexalmacen = $this->Kardex_model->phuyu_kardexalmacen($codkardex, 4, $campos);
			$resultadoDetalle = $this->Kardex_model->phuyu_kardexdetalle($codkardex, $codkardexalmacen, $detalle, true, 0);
			if (isset($resultadoDetalle["success"]) && $resultadoDetalle["success"] === false) {
				$this->db->trans_rollback();
				echo json_encode(["estado" => 0, "mensaje" => "STOCK INSUFICIENTE EN UNO O MAS CONSUMOS"]);
				return;
			}

			$codmovimiento = $this->Caja_model->phuyu_movimientos($codkardex, 1, 1, $totales->importe, $campos, $totales->importe);
			if ((int)$codmovimiento <= 0) {
				$this->db->trans_rollback();
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUDO REGISTRAR MOVIMIENTO DE CAJA"]);
				return;
			}
			$estado = 1;
			if ((int)$campos->condicionpago == 1) {
				$estado = $this->Caja_model->phuyu_movimientosdetalle($codmovimiento, $this->request->pagos);
			}else{
				if (empty($this->request->cuotas)) {
					$this->request->cuotas = [(object)[
						"nrocuota" => 1,
						"fechavence" => date("Y-m-d", strtotime($campos->fechacomprobante." +".(int)$campos->nrodias." days")),
						"importe" => $totales->importe,
						"interes" => 0,
						"total" => $campos->totalcredito
					]];
				}
				$estado = $this->Caja_model->phuyu_credito($codkardex, $codmovimiento, 1, $campos, $totales, $this->request->cuotas, "");
			}
			if ($estado != 1) {
				$this->db->trans_rollback();
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUDO REGISTRAR EL PAGO EN CAJA"]);
				return;
			}

			if ((int)$campos->codcomprobantetipo == 10 || (int)$campos->codcomprobantetipo == 12) {
				$kardex = $this->db->query("select nrocomprobante from kardex.kardex where codkardex=?", [(int)$codkardex])->row_array();
				$tipoSunat = ((int)$campos->codcomprobantetipo == 10) ? "01" : "03";
				$xml = $_SESSION["phuyu_ruc"]."-".$tipoSunat."-".$campos->seriecomprobante."-".$kardex["nrocomprobante"];
				$estado = $this->phuyu_model->phuyu_guardar("sunat.kardexsunat", ["codkardex","codsucursal","codusuario","fechacreado","nombre_xml"], [(int)$codkardex,(int)$_SESSION["phuyu_codsucursal"],(int)$_SESSION["phuyu_codusuario"],$campos->fechacomprobante,$xml]);
			}

			$this->db->where("codestadia", (int)$info["estadia"]["codestadia"]);
			$this->db->where("situacion", 1);
			$this->db->update("hotel.consumos_habitacion", ["situacion" => 2, "codkardex" => (int)$codkardex, "fechafacturado" => $campos->fechacomprobante]);

			$consumosTotal = (double)$info["estadia"]["consumos"] + (double)$totales->importe;
			$importeTotal = (double)$info["estadia"]["alojamiento"] + $consumosTotal;
			$estado = $this->phuyu_model->phuyu_editar("hotel.estadias", ["consumos","importe"], [$consumosTotal, $importeTotal], "codestadia", (int)$info["estadia"]["codestadia"]);

			if ($this->db->trans_status() === FALSE || $estado != 1) {
				$this->db->trans_rollback();
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUDO COBRAR CONSUMOS"]);
				return;
			}
			$this->db->trans_commit();
			echo json_encode(["estado" => 1, "codkardex" => $codkardex]);
		}else{
			$this->load->view("phuyu/404");
		}
	}
}
