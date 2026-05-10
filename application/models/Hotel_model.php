<?php

class Hotel_model extends CI_Model {

	public function __construct(){
		parent::__construct();
	}

	private function tabla_hotel_existe($tabla){
		$info = $this->db->query(
			"select 1
			from information_schema.tables
			where table_schema='hotel' and table_name=?
			limit 1",
			[$tabla]
		)->row_array();
		return !empty($info);
	}

	public function ambientes(){
		return $this->db->query(
			"select *
			from hotel.ambientes
			where codsucursal=? and estado=1
			order by descripcion",
			[(int)$_SESSION["phuyu_codsucursal"]]
		)->result_array();
	}

	public function caracteristicas(){
		if (!$this->tabla_hotel_existe("caracteristicas")) {
			return [];
		}
		return $this->db->query(
			"select *
			from hotel.caracteristicas
			where estado=1
			order by orden, descripcion"
		)->result_array();
	}

	public function caracteristicas_habitacion($codhabitacion){
		if (!$this->tabla_hotel_existe("caracteristicas") || !$this->tabla_hotel_existe("habitacion_caracteristicas")) {
			return [];
		}
		return $this->db->query(
			"select c.*
			from hotel.habitacion_caracteristicas hc
			inner join hotel.caracteristicas c on(c.codcaracteristica=hc.codcaracteristica)
			where hc.codhabitacion=? and hc.estado=1 and c.estado=1
			order by c.orden, c.descripcion",
			[(int)$codhabitacion]
		)->result_array();
	}

	public function caracteristicas_habitaciones($codhabitaciones){
		if (empty($codhabitaciones) || !$this->tabla_hotel_existe("caracteristicas") || !$this->tabla_hotel_existe("habitacion_caracteristicas")) {
			return [];
		}

		$codhabitaciones = array_values(array_unique(array_map("intval", $codhabitaciones)));
		$placeholders = implode(",", array_fill(0, count($codhabitaciones), "?"));
		$lista = $this->db->query(
			"select hc.codhabitacion, c.codcaracteristica, c.descripcion, c.icono
			from hotel.habitacion_caracteristicas hc
			inner join hotel.caracteristicas c on(c.codcaracteristica=hc.codcaracteristica)
			where hc.estado=1 and c.estado=1 and hc.codhabitacion in (".$placeholders.")
			order by c.orden, c.descripcion",
			$codhabitaciones
		)->result_array();

		$caracteristicas = [];
		foreach ($lista as $item) {
			$codhabitacion = (int)$item["codhabitacion"];
			if (!isset($caracteristicas[$codhabitacion])) {
				$caracteristicas[$codhabitacion] = [];
			}
			$caracteristicas[$codhabitacion][] = $item;
		}

		return $caracteristicas;
	}

	public function guardar_habitacion_caracteristicas($codhabitacion, $caracteristicas){
		if (!$this->tabla_hotel_existe("caracteristicas") || !$this->tabla_hotel_existe("habitacion_caracteristicas")) {
			return true;
		}

		$codhabitacion = (int)$codhabitacion;
		$caracteristicas = is_array($caracteristicas) ? $caracteristicas : [];
		$caracteristicas = array_values(array_unique(array_filter(array_map("intval", $caracteristicas))));

		$this->db->where("codhabitacion", $codhabitacion)->update("hotel.habitacion_caracteristicas", ["estado" => 0]);

		foreach ($caracteristicas as $codcaracteristica) {
			$this->db->query(
				"insert into hotel.habitacion_caracteristicas (codhabitacion, codcaracteristica, estado)
				values (?, ?, 1)
				on conflict (codhabitacion, codcaracteristica)
				do update set estado=1",
				[$codhabitacion, $codcaracteristica]
			);
		}

		return true;
	}

	public function habitaciones_resumen($codambiente = 0, $codcaracteristica = 0){
		$whereAmbiente = "";
		$params = [(int)$_SESSION["phuyu_codsucursal"]];
		if ((int)$codambiente > 0) {
			$whereAmbiente = " and h.codambiente=?";
			$params[] = (int)$codambiente;
		}
		$whereCaracteristica = "";
		$tieneCaracteristicas = $this->tabla_hotel_existe("caracteristicas") && $this->tabla_hotel_existe("habitacion_caracteristicas");
		if ((int)$codcaracteristica > 0 && $tieneCaracteristicas) {
			$whereCaracteristica = " and exists (
				select 1
				from hotel.habitacion_caracteristicas hcf
				where hcf.codhabitacion=h.codhabitacion
					and hcf.codcaracteristica=?
					and hcf.estado=1
			)";
			$params[] = (int)$codcaracteristica;
		}

		$habitaciones = $this->db->query(
			"select h.*, ht.descripcion as tipo, coalesce(a.descripcion, h.piso) as ambiente,
				(select e.codestadia
				 from hotel.estadia_habitaciones eh
				 inner join hotel.estadias e on(e.codestadia=eh.codestadia)
				 where eh.codhabitacion=h.codhabitacion and e.codsucursal=h.codsucursal and e.situacion=1 and e.estado=1 and eh.estado=1
				 order by (
				 	select count(*)
				 	from hotel.consumos_habitacion ch
				 	where ch.codestadia=e.codestadia and ch.situacion=1 and ch.estado=1
				 ) desc, e.codestadia desc limit 1) as codestadia
			from hotel.habitaciones h
			inner join hotel.habitacion_tipos ht on(ht.codhabitaciontipo=h.codhabitaciontipo)
			left join hotel.ambientes a on(a.codambiente=h.codambiente)
			where h.codsucursal=? and h.estado=1".$whereAmbiente.$whereCaracteristica."
			order by coalesce(a.descripcion, h.piso), h.numero",
			$params
		)->result_array();

		$caracteristicas = $this->caracteristicas_habitaciones(array_column($habitaciones, "codhabitacion"));
		foreach ($habitaciones as $key => $value) {
			$habitaciones[$key]["situacion_texto"] = $this->habitacion_situacion_texto((int)$value["situacion"]);
			$habitaciones[$key]["situacion_clase"] = $this->habitacion_situacion_clase((int)$value["situacion"]);
			$habitaciones[$key]["caracteristicas"] = $caracteristicas[(int)$value["codhabitacion"]] ?? [];
		}

		return $habitaciones;
	}

	public function habitacion_situacion_texto($situacion){
		$estados = [
			1 => "DISPONIBLE",
			2 => "RESERVADA",
			3 => "OCUPADA",
			4 => "LIMPIEZA",
			5 => "MANTENIMIENTO",
			6 => "BLOQUEADA"
		];
		return isset($estados[$situacion]) ? $estados[$situacion] : "SIN ESTADO";
	}

	public function habitacion_situacion_clase($situacion){
		$clases = [
			1 => "disponible",
			2 => "reservada",
			3 => "ocupada",
			4 => "limpieza",
			5 => "mantenimiento",
			6 => "bloqueada"
		];
		return isset($clases[$situacion]) ? $clases[$situacion] : "bloqueada";
	}

	public function estadia_activa_habitacion($codhabitacion){
		return $this->db->query(
			"select e.*, eh.codhabitacion, h.numero, ht.descripcion as tipo
			from hotel.estadias e
			inner join hotel.estadia_habitaciones eh on(e.codestadia=eh.codestadia)
			inner join hotel.habitaciones h on(h.codhabitacion=eh.codhabitacion)
			inner join hotel.habitacion_tipos ht on(ht.codhabitaciontipo=h.codhabitaciontipo)
			where eh.codhabitacion=? and h.codsucursal=? and e.codsucursal=? and e.situacion=1 and e.estado=1 and eh.estado=1
			order by (
				select count(*)
				from hotel.consumos_habitacion ch
				where ch.codestadia=e.codestadia and ch.situacion=1 and ch.estado=1
			) desc, e.codestadia desc limit 1",
			[(int)$codhabitacion, (int)$_SESSION["phuyu_codsucursal"], (int)$_SESSION["phuyu_codsucursal"]]
		)->row_array();
	}

	public function habitacion_tiene_estadia_activa($codhabitacion){
		$info = $this->db->query(
			"select e.codestadia
			from hotel.estadias e
			inner join hotel.estadia_habitaciones eh on(e.codestadia=eh.codestadia and eh.estado=1)
			inner join hotel.habitaciones h on(h.codhabitacion=eh.codhabitacion)
			where eh.codhabitacion=?
			  and h.codsucursal=?
			  and h.estado=1
			  and e.codsucursal=?
			  and e.situacion=1
			  and e.estado=1
			limit 1",
			[(int)$codhabitacion, (int)$_SESSION["phuyu_codsucursal"], (int)$_SESSION["phuyu_codsucursal"]]
		)->row_array();

		return !empty($info);
	}

	public function habitacion_disponible_rango($codhabitacion, $desde, $hasta, $codreserva = 0){
		$codhabitacion = (int)$codhabitacion;
		$codreserva = (int)$codreserva;
		$desde = date("Y-m-d", strtotime($desde));
		$hasta = date("Y-m-d", strtotime($hasta));

		if (strtotime($hasta) <= strtotime($desde)) {
			return ["estado" => 0, "mensaje" => "LA FECHA DE SALIDA DEBE SER MAYOR A LA FECHA DE LLEGADA"];
		}

		$reserva = $this->db->query(
			"select r.codreserva
			from hotel.reserva_habitaciones rh
			inner join hotel.reservas r on(r.codreserva=rh.codreserva)
			where rh.codhabitacion=?
			  and rh.estado=1
			  and r.estado=1
			  and r.situacion in (1,2,3)
			  and r.codreserva<>?
			  and r.fechallegada < ?
			  and r.fechasalida > ?
			limit 1",
			[$codhabitacion, $codreserva, $hasta, $desde]
		)->row_array();

		if (!empty($reserva)) {
			return ["estado" => 0, "mensaje" => "LA HABITACION YA TIENE UNA RESERVA EN ESE RANGO"];
		}

		$estadia = $this->db->query(
			"select e.codestadia
			from hotel.estadia_habitaciones eh
			inner join hotel.estadias e on(e.codestadia=eh.codestadia)
			where eh.codhabitacion=?
			  and eh.estado=1
			  and e.estado=1
			  and e.situacion=1
			  and e.fecha_checkin < ?
			  and coalesce(e.fecha_checkout, ?) > ?
			limit 1",
			[$codhabitacion, $hasta, $hasta, $desde]
		)->row_array();

		if (!empty($estadia)) {
			return ["estado" => 0, "mensaje" => "LA HABITACION TIENE UNA ESTADIA ACTIVA EN ESE RANGO"];
		}

		$mantenimiento = $this->db->query(
			"select codmantenimiento
			from hotel.mantenimiento_habitaciones
			where codhabitacion=?
			  and estado=1
			  and coalesce(estado_orden, situacion, 1) in (1,2)
			  and fecha_inicio < ?
			  and coalesce(fecha_fin, ?) > ?
			limit 1",
			[$codhabitacion, $hasta, $hasta, $desde]
		)->row_array();

		if (!empty($mantenimiento)) {
			return ["estado" => 0, "mensaje" => "LA HABITACION TIENE MANTENIMIENTO ACTIVO EN ESE RANGO"];
		}

		$limpieza = $this->db->query(
			"select codlimpieza
			from hotel.limpieza_habitaciones
			where codhabitacion=?
			  and estado=1
			  and coalesce(estado_orden, 1) in (1,2)
			  and fecha < ?
			  and coalesce(fecha_fin, fecha + interval '1 day')::date > ?
			limit 1",
			[$codhabitacion, $hasta, $desde]
		)->row_array();

		if (!empty($limpieza)) {
			return ["estado" => 0, "mensaje" => "LA HABITACION TIENE LIMPIEZA ACTIVA EN ESE RANGO"];
		}

		return ["estado" => 1, "mensaje" => "DISPONIBLE"];
	}

	public function ocupacion_habitacion_rango($codhabitacion, $desde, $hasta, $codreserva = 0){
		$codhabitacion = (int)$codhabitacion;
		$codreserva = (int)$codreserva;
		$desde = date("Y-m-d", strtotime($desde));
		$hasta = date("Y-m-d", strtotime($hasta));

		$reservas = $this->db->query(
			"select 'reserva' as tipo, r.codreserva as codigo, r.fechallegada as desde, r.fechasalida as hasta,
				r.cliente, r.situacion,
				case
					when r.situacion=1 then 'RESERVA PENDIENTE'
					when r.situacion=2 then 'RESERVA CONFIRMADA'
					when r.situacion=3 then 'EN HOSPEDAJE'
					else 'RESERVA'
				end as descripcion
			from hotel.reserva_habitaciones rh
			inner join hotel.reservas r on(r.codreserva=rh.codreserva)
			where rh.codhabitacion=?
			  and rh.estado=1
			  and r.estado=1
			  and r.situacion in (1,2,3)
			  and r.codreserva<>?
			  and r.fechallegada < ?
			  and r.fechasalida > ?",
			[$codhabitacion, $codreserva, $hasta, $desde]
		)->result_array();

		$estadias = $this->db->query(
			"select 'estadia' as tipo, e.codestadia as codigo, e.fecha_checkin as desde,
				coalesce(e.fecha_checkout, ?) as hasta, e.cliente, e.situacion,
				'ESTADIA ACTIVA' as descripcion
			from hotel.estadia_habitaciones eh
			inner join hotel.estadias e on(e.codestadia=eh.codestadia)
			where eh.codhabitacion=?
			  and eh.estado=1
			  and e.estado=1
			  and e.situacion=1
			  and e.fecha_checkin < ?
			  and coalesce(e.fecha_checkout, ?) > ?",
			[$hasta, $codhabitacion, $hasta, $hasta, $desde]
		)->result_array();

		$mantenimiento = $this->db->query(
			"select 'mantenimiento' as tipo, mh.codmantenimiento as codigo, mh.fecha_inicio as desde,
				coalesce(mh.fecha_fin, ?) as hasta, '' as cliente, coalesce(mh.estado_orden, mh.situacion, 1) as situacion,
				'MANTENIMIENTO' as descripcion
			from hotel.mantenimiento_habitaciones mh
			where mh.codhabitacion=?
			  and mh.estado=1
			  and coalesce(mh.estado_orden, mh.situacion, 1) in (1,2)
			  and mh.fecha_inicio < ?
			  and coalesce(mh.fecha_fin, ?) > ?",
			[$hasta, $codhabitacion, $hasta, $hasta, $desde]
		)->result_array();

		$limpieza = $this->db->query(
			"select 'limpieza' as tipo, lh.codlimpieza as codigo, lh.fecha as desde,
				coalesce(lh.fecha_fin, lh.fecha + interval '1 day')::date as hasta, '' as cliente,
				coalesce(lh.estado_orden, 1) as situacion, 'LIMPIEZA' as descripcion
			from hotel.limpieza_habitaciones lh
			where lh.codhabitacion=?
			  and lh.estado=1
			  and coalesce(lh.estado_orden, 1) in (1,2)
			  and lh.fecha < ?
			  and coalesce(lh.fecha_fin, lh.fecha + interval '1 day')::date > ?",
			[$codhabitacion, $hasta, $desde]
		)->result_array();

		return array_merge($reservas, $estadias, $mantenimiento, $limpieza);
	}

	public function disponibilidad_habitaciones_rango($desde, $hasta, $codambiente = 0, $codreserva = 0){
		$desde = date("Y-m-d", strtotime($desde));
		$hasta = date("Y-m-d", strtotime($hasta));
		$params = [(int)$_SESSION["phuyu_codsucursal"]];
		$whereAmbiente = "";
		if ((int)$codambiente > 0) {
			$whereAmbiente = " and h.codambiente=?";
			$params[] = (int)$codambiente;
		}

		$habitaciones = $this->db->query(
			"select h.codhabitacion, h.numero, h.preciobase, h.codambiente, h.situacion,
				ht.descripcion as tipo, coalesce(a.descripcion, h.piso) as ambiente
			from hotel.habitaciones h
			inner join hotel.habitacion_tipos ht on(ht.codhabitaciontipo=h.codhabitaciontipo)
			left join hotel.ambientes a on(a.codambiente=h.codambiente)
			where h.codsucursal=? and h.estado=1 and h.situacion not in (5,6)".$whereAmbiente."
			order by coalesce(a.descripcion, h.piso), h.numero",
			$params
		)->result_array();

		foreach ($habitaciones as $key => $habitacion) {
			$validacion = $this->habitacion_disponible_rango((int)$habitacion["codhabitacion"], $desde, $hasta, $codreserva);
			$eventos = $this->ocupacion_habitacion_rango((int)$habitacion["codhabitacion"], $desde, $hasta, $codreserva);
			$habitaciones[$key]["disponible"] = (int)$validacion["estado"];
			$habitaciones[$key]["mensaje"] = $validacion["mensaje"];
			$habitaciones[$key]["eventos"] = $eventos;
		}

		return $habitaciones;
	}

	public function estadia_detalle($codestadia){
		$estadia = $this->db->query(
			"select e.*, p.documento, p.coddocumentotipo
			from hotel.estadias e
			inner join public.personas p on(p.codpersona=e.codpersona)
			where e.codestadia=? and e.estado=1",
			[(int)$codestadia]
		)->row_array();

		$habitaciones = $this->db->query(
			"select eh.*, h.numero, ht.descripcion as tipo
			from hotel.estadia_habitaciones eh
			inner join hotel.habitaciones h on(h.codhabitacion=eh.codhabitacion)
			inner join hotel.habitacion_tipos ht on(ht.codhabitaciontipo=h.codhabitaciontipo)
			where eh.codestadia=? and eh.estado=1",
			[(int)$codestadia]
		)->result_array();

		$huespedes = $this->db->query(
			"select eh.*, p.documento, p.razonsocial
			from hotel.estadia_huespedes eh
			inner join public.personas p on(p.codpersona=eh.codpersona)
			where eh.codestadia=? and eh.estado=1
			order by eh.titular desc, p.razonsocial",
			[(int)$codestadia]
		)->result_array();

		$consumos = $this->db->query(
			"select ch.*, p.descripcion as producto, p.controlstock, p.controlarseries, p.afectoigvventa, p.afectoicbper, p.calcular,
				u.descripcion as unidad, h.numero as habitacion, s.serie_codigo, coalesce(pub.stockactualconvertido,0) as stock
			from hotel.consumos_habitacion ch
			inner join almacen.productos p on(p.codproducto=ch.codproducto)
			inner join almacen.unidades u on(u.codunidad=ch.codunidad)
			inner join hotel.habitaciones h on(h.codhabitacion=ch.codhabitacion)
			left join almacen.series s on(s.id_serie=ch.id_serie)
			left join almacen.productoubicacion pub on(pub.codproducto=ch.codproducto and pub.codunidad=ch.codunidad and pub.codalmacen=? and pub.estado=1)
			where ch.codestadia=? and ch.estado=1
			order by ch.codconsumo",
			[(int)$_SESSION["phuyu_codalmacen"], (int)$codestadia]
		)->result_array();

		return compact("estadia", "habitaciones", "huespedes", "consumos");
	}

	public function producto_alojamiento(){
		$config = $this->db->query("select * from hotel.configuraciones where codsucursal=? and estado=1 limit 1", [(int)$_SESSION["phuyu_codsucursal"]])->row_array();
		if (!empty($config) && (int)$config["codproducto_alojamiento"] > 0 && (int)$config["codunidad_alojamiento"] > 0) {
			return $this->db->query(
				"select p.codproducto, p.descripcion, p.controlstock, p.controlarseries, p.afectoigvventa, p.afectoicbper, p.calcular,
					pu.codunidad, u.descripcion as unidad, pu.pventapublico as precio
				from almacen.productos p
				inner join almacen.productounidades pu on(pu.codproducto=p.codproducto and pu.codunidad=? and pu.estado=1)
				inner join almacen.unidades u on(u.codunidad=pu.codunidad)
				where p.codproducto=? and p.estado=1 limit 1",
				[(int)$config["codunidad_alojamiento"], (int)$config["codproducto_alojamiento"]]
			)->row_array();
		}

		return [];
	}

	public function armar_item_kardex($codproducto, $codunidad, $producto, $unidad, $cantidad, $precio, $descripcion, $afectoigvventa = 0, $afectoicbper = 0, $control = 0, $controlarseries = 0, $serie = null){
		$precio = (double)$precio;
		$cantidad = (double)$cantidad;
		$subtotal = round($cantidad * $precio, 2);
		$codafectacionigv = "20";
		$preciosinigv = $precio;
		$valorventa = $subtotal;
		$igv = 0;

		if ((int)$afectoigvventa == 1) {
			$factor = 1 + ((double)$_SESSION["phuyu_igv"] / 100);
			$codafectacionigv = "10";
			$preciosinigv = round($precio / $factor, 4);
			$valorventa = round($subtotal / $factor, 2);
			$igv = round($subtotal - $valorventa, 2);
		}

		return (object)[
			"codproducto" => (int)$codproducto,
			"producto" => $producto,
			"codunidad" => (int)$codunidad,
			"unidad" => $unidad,
			"cantidad" => $cantidad,
			"stock" => 0,
			"control" => (int)$control,
			"controlarseries" => (int)$controlarseries,
			"serie_seleccionada" => $serie,
			"preciobruto" => $preciosinigv,
			"porcdescuento" => 0,
			"descuento" => 0,
			"preciosinigv" => $preciosinigv,
			"precio" => $precio,
			"preciorefunitario" => $precio,
			"codafectacionigv" => $codafectacionigv,
			"igv" => $igv,
			"conicbper" => (int)$afectoicbper,
			"icbper" => 0,
			"valorventa" => $valorventa,
			"subtotal" => $subtotal,
			"descripcion" => $descripcion,
			"flete" => 0,
			"series" => []
		];
	}

	public function validar_detalle_venta($detalle){
		$errores = [];
		if (!is_array($detalle)) {
			return $errores;
		}

		$controlaStockSistema = isset($_SESSION["phuyu_stockalmacen"]) ? (int)$_SESSION["phuyu_stockalmacen"] : 0;

		foreach ($detalle as $item) {
			$codproducto = (int)$item->codproducto;
			$codunidad = (int)$item->codunidad;
			$cantidad = (double)$item->cantidad;

			if ($controlaStockSistema == 1 && (int)$item->controlarseries == 1) {
				if (empty($item->serie_seleccionada) || empty($item->serie_seleccionada->id_serie)) {
					$errores[] = $item->producto.": seleccione serie";
					continue;
				}
				$serie = $this->db->query(
					"select id_serie
					from almacen.series
					where id_serie=? and codproducto=? and codalmacen=? and codsucursal=? and estado='EN_ALMACEN'
					limit 1",
					[(int)$item->serie_seleccionada->id_serie, $codproducto, (int)$_SESSION["phuyu_codalmacen"], (int)$_SESSION["phuyu_codsucursal"]]
				)->row_array();
				if (empty($serie)) {
					$errores[] = $item->producto.": serie no disponible";
				}
			}

			if ($controlaStockSistema != 1 || (int)$item->control != 1) {
				continue;
			}

			$stock = $this->db->query(
				"select coalesce(pub.stockactualconvertido,0) as stock, p.descripcion, u.descripcion as unidad
				from almacen.productos p
				inner join almacen.unidades u on(u.codunidad=?)
				left join almacen.productoubicacion pub on(pub.codproducto=p.codproducto and pub.codunidad=? and pub.codalmacen=? and pub.estado=1)
				where p.codproducto=? and p.estado=1
				limit 1",
				[$codunidad, $codunidad, (int)$_SESSION["phuyu_codalmacen"], $codproducto]
			)->row_array();

			$stockActual = empty($stock) ? 0 : (double)$stock["stock"];
			if ($stockActual < $cantidad) {
				$producto = empty($stock) ? $item->producto : $stock["descripcion"];
				$unidad = empty($stock) ? $item->unidad : $stock["unidad"];
				$errores[] = $producto.": stock insuficiente (".number_format($stockActual, 2)." ".$unidad.")";
			}
		}

		return $errores;
	}
}
