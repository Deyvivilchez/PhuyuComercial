<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Reportes extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model("Hotel_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$habitaciones = $this->Hotel_model->habitaciones_resumen();
				$this->load->view("hotel/reportes/index", compact("habitaciones"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function resumen(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents("php://input"));
			$desde = $this->request->desde;
			$hasta = $this->request->hasta;
			$codhabitacion = isset($this->request->codhabitacion) ? (int)$this->request->codhabitacion : 0;
			$cliente = isset($this->request->cliente) ? trim($this->request->cliente) : "";
			$whereHabitacion = "";
			$whereCliente = "";
			$whereClienteConsumos = "";
			$paramsBase = [(int)$_SESSION["phuyu_codsucursal"], $desde, $hasta];
			if ($codhabitacion > 0) {
				$whereHabitacion = " and exists (
					select 1 from hotel.estadia_habitaciones ehf
					where ehf.codestadia=e.codestadia and ehf.codhabitacion=? and ehf.estado=1
				)";
				$paramsBase[] = $codhabitacion;
			}
			if ($cliente != "") {
				$whereCliente = " and (upper(e.cliente) like upper(?) or upper(p.documento) like upper(?))";
				$whereClienteConsumos = " and (upper(e.cliente) like upper(?) or upper(pe.documento) like upper(?))";
				$paramsBase[] = "%".$cliente."%";
				$paramsBase[] = "%".$cliente."%";
			}

			$ocupacion = $this->db->query(
				"select count(*) as estadias, coalesce(sum(noches),0) as noches, coalesce(sum(importe),0) as ingresos
				from hotel.estadias e
				inner join public.personas p on(p.codpersona=e.codpersona)
				where e.codsucursal=? and e.fecha_checkin>=? and e.fecha_checkin<=? and e.estado=1".$whereHabitacion.$whereCliente,
				$paramsBase
			)->row_array();
			$consumos = $this->db->query(
				"select coalesce(sum(e.consumos),0) as consumos
				from hotel.estadias e
				inner join public.personas p on(p.codpersona=e.codpersona)
				where e.codsucursal=? and e.estado=1 and e.fecha_checkin>=? and e.fecha_checkin<=?".$whereHabitacion.$whereCliente,
				$paramsBase
			)->row_array();
			$clientes = $this->db->query(
				"select p.codpersona, p.documento, p.razonsocial, count(*) as visitas, coalesce(sum(e.importe),0) as importe
				from hotel.estadias e
				inner join public.personas p on(p.codpersona=e.codpersona)
				where e.codsucursal=? and e.estado=1 and e.fecha_checkin>=? and e.fecha_checkin<=?".$whereHabitacion.$whereCliente."
				group by p.codpersona, p.documento, p.razonsocial
				order by visitas desc, importe desc limit 10",
				$paramsBase
			)->result_array();

			$diaria = $this->db->query(
				"select e.fecha_checkin as fecha, count(*) as estadias, coalesce(sum(e.noches),0) as noches,
					coalesce(sum(e.alojamiento),0) as alojamiento, coalesce(sum(e.consumos),0) as consumos, coalesce(sum(e.importe),0) as importe
				from hotel.estadias e
				inner join public.personas p on(p.codpersona=e.codpersona)
				where e.codsucursal=? and e.fecha_checkin>=? and e.fecha_checkin<=? and e.estado=1".$whereHabitacion.$whereCliente."
				group by e.fecha_checkin
				order by e.fecha_checkin",
				$paramsBase
			)->result_array();
			$mensual = $this->db->query(
				"select to_char(e.fecha_checkin,'YYYY-MM') as periodo, count(*) as estadias, coalesce(sum(e.noches),0) as noches,
					coalesce(sum(e.importe),0) as importe
				from hotel.estadias e
				inner join public.personas p on(p.codpersona=e.codpersona)
				where e.codsucursal=? and e.fecha_checkin>=? and e.fecha_checkin<=? and e.estado=1".$whereHabitacion.$whereCliente."
				group by to_char(e.fecha_checkin,'YYYY-MM')
				order by periodo",
				$paramsBase
			)->result_array();
			$habitacionesUsadas = $this->db->query(
				"select h.codhabitacion, h.numero, ht.descripcion as tipo, count(*) as usos, coalesce(sum(eh.noches),0) as noches,
					coalesce(sum(eh.subtotal),0) as importe
				from hotel.estadia_habitaciones eh
				inner join hotel.estadias e on(e.codestadia=eh.codestadia)
				inner join public.personas p on(p.codpersona=e.codpersona)
				inner join hotel.habitaciones h on(h.codhabitacion=eh.codhabitacion)
				inner join hotel.habitacion_tipos ht on(ht.codhabitaciontipo=h.codhabitaciontipo)
				where e.codsucursal=? and h.codsucursal=? and e.fecha_checkin>=? and e.fecha_checkin<=? and e.estado=1".$whereCliente."
					".($codhabitacion > 0 ? " and h.codhabitacion=".$codhabitacion : "")."
				group by h.codhabitacion, h.numero, ht.descripcion
				order by usos desc, noches desc limit 20",
				array_merge([(int)$_SESSION["phuyu_codsucursal"], (int)$_SESSION["phuyu_codsucursal"], $desde, $hasta], $cliente != "" ? ["%".$cliente."%", "%".$cliente."%"] : [])
			)->result_array();
			$consumosHabitacion = $this->db->query(
				"select h.numero, pr.descripcion as producto, u.descripcion as unidad, coalesce(sum(ch.cantidad),0) as cantidad,
					coalesce(sum(ch.subtotal),0) as subtotal
				from hotel.consumos_habitacion ch
				inner join hotel.estadias e on(e.codestadia=ch.codestadia)
				inner join public.personas pe on(pe.codpersona=e.codpersona)
				inner join hotel.habitaciones h on(h.codhabitacion=ch.codhabitacion)
				inner join almacen.productos pr on(pr.codproducto=ch.codproducto)
				inner join almacen.unidades u on(u.codunidad=ch.codunidad)
				where e.codsucursal=? and h.codsucursal=? and e.fecha_checkin>=? and e.fecha_checkin<=? and ch.estado=1 and ch.situacion<>3".$whereClienteConsumos."
					".($codhabitacion > 0 ? " and h.codhabitacion=".$codhabitacion : "")."
				group by h.numero, pr.descripcion, u.descripcion
				order by subtotal desc limit 30",
				array_merge([(int)$_SESSION["phuyu_codsucursal"], (int)$_SESSION["phuyu_codsucursal"], $desde, $hasta], $cliente != "" ? ["%".$cliente."%", "%".$cliente."%"] : [])
			)->result_array();
			$historial = $this->db->query(
				"select e.codestadia, e.fecha_checkin, e.fecha_checkout, e.cliente, e.noches, e.alojamiento, e.consumos, e.importe,
					string_agg(h.numero, ', ' order by h.numero) as habitaciones,
					case when e.situacion=1 then 'ACTIVA' when e.situacion=2 then 'CERRADA' else 'OTRA' end as situacion_texto
				from hotel.estadias e
				inner join public.personas p on(p.codpersona=e.codpersona)
				left join hotel.estadia_habitaciones eh on(eh.codestadia=e.codestadia and eh.estado=1)
				left join hotel.habitaciones h on(h.codhabitacion=eh.codhabitacion)
				where e.codsucursal=? and e.fecha_checkin>=? and e.fecha_checkin<=? and e.estado=1".$whereHabitacion.$whereCliente."
				group by e.codestadia
				order by e.fecha_checkin desc, e.codestadia desc limit 100",
				$paramsBase
			)->result_array();
			$reservasPendientes = $this->db->query(
				"select r.codreserva, r.fechallegada, r.fechasalida, r.cliente, p.documento,
					string_agg(h.numero, ', ' order by h.numero) as habitaciones,
					case when r.situacion=1 then 'PENDIENTE' when r.situacion=2 then 'CONFIRMADA' when r.situacion=3 then 'EN HOSPEDAJE' when r.situacion=4 then 'FINALIZADA' else 'ANULADA' end as situacion_texto
				from hotel.reservas r
				inner join public.personas p on(p.codpersona=r.codpersona)
				left join hotel.reserva_habitaciones rh on(rh.codreserva=r.codreserva and rh.estado=1)
				left join hotel.habitaciones h on(h.codhabitacion=rh.codhabitacion)
				where r.codsucursal=? and r.estado=1 and r.situacion=1 and r.fechallegada>=? and r.fechallegada<=?
					".($codhabitacion > 0 ? " and h.codhabitacion=".$codhabitacion : "")."
					".($cliente != "" ? " and (upper(r.cliente) like upper(?) or upper(p.documento) like upper(?))" : "")."
				group by r.codreserva, p.documento
				order by r.fechallegada, r.codreserva",
				array_merge([(int)$_SESSION["phuyu_codsucursal"], $desde, $hasta], $cliente != "" ? ["%".$cliente."%", "%".$cliente."%"] : [])
			)->result_array();
			$reservasConfirmadas = $this->db->query(
				"select r.codreserva, r.fechallegada, r.fechasalida, r.cliente, p.documento,
					string_agg(h.numero, ', ' order by h.numero) as habitaciones,
					case when r.situacion=1 then 'PENDIENTE' when r.situacion=2 then 'CONFIRMADA' when r.situacion=3 then 'EN HOSPEDAJE' when r.situacion=4 then 'FINALIZADA' else 'ANULADA' end as situacion_texto
				from hotel.reservas r
				inner join public.personas p on(p.codpersona=r.codpersona)
				left join hotel.reserva_habitaciones rh on(rh.codreserva=r.codreserva and rh.estado=1)
				left join hotel.habitaciones h on(h.codhabitacion=rh.codhabitacion)
				where r.codsucursal=? and r.estado=1 and r.situacion=2 and r.fechallegada>=? and r.fechallegada<=?
					".($codhabitacion > 0 ? " and h.codhabitacion=".$codhabitacion : "")."
					".($cliente != "" ? " and (upper(r.cliente) like upper(?) or upper(p.documento) like upper(?))" : "")."
				group by r.codreserva, p.documento
				order by r.fechallegada, r.codreserva",
				array_merge([(int)$_SESSION["phuyu_codsucursal"], $desde, $hasta], $cliente != "" ? ["%".$cliente."%", "%".$cliente."%"] : [])
			)->result_array();
			$habitacionesLibres = [];
			foreach ($this->Hotel_model->disponibilidad_habitaciones_rango($desde, date("Y-m-d", strtotime($hasta." +1 day")), 0, 0) as $habLibre) {
				if ((int)$habLibre["disponible"] == 1 && ($codhabitacion == 0 || (int)$habLibre["codhabitacion"] == $codhabitacion)) {
					$habitacionesLibres[] = $habLibre;
				}
			}
			$mantenimiento = $this->db->query(
				"select h.numero, ht.descripcion as tipo, mh.fecha_inicio, mh.fecha_fin, mh.observacion, mh.tipo_mantenimiento, mh.prioridad,
					case when coalesce(mh.estado_orden,mh.situacion,1)=1 then 'PENDIENTE'
						when coalesce(mh.estado_orden,mh.situacion,1)=2 then 'EN PROCESO'
						when coalesce(mh.estado_orden,mh.situacion,1)=3 then 'FINALIZADO'
						else 'ANULADO' end as situacion_texto
				from hotel.mantenimiento_habitaciones mh
				inner join hotel.habitaciones h on(h.codhabitacion=mh.codhabitacion)
				inner join hotel.habitacion_tipos ht on(ht.codhabitaciontipo=h.codhabitaciontipo)
				where h.codsucursal=? and mh.estado=1 and mh.fecha_inicio>=? and mh.fecha_inicio<=?
					".($codhabitacion > 0 ? " and h.codhabitacion=".$codhabitacion : "")."
				order by mh.situacion, mh.fecha_inicio desc",
				[(int)$_SESSION["phuyu_codsucursal"], $desde, $hasta]
			)->result_array();
			$limpieza = $this->db->query(
				"select h.numero, ht.descripcion as tipo, lh.fecha, lh.hora, lh.observacion, lh.tipo_limpieza,
					case when coalesce(lh.estado_orden,1)=1 then 'PENDIENTE'
						when coalesce(lh.estado_orden,1)=2 then 'EN PROCESO'
						when coalesce(lh.estado_orden,1)=3 then 'FINALIZADO'
						else 'ANULADO' end as situacion_texto
				from hotel.limpieza_habitaciones lh
				inner join hotel.habitaciones h on(h.codhabitacion=lh.codhabitacion)
				inner join hotel.habitacion_tipos ht on(ht.codhabitaciontipo=h.codhabitaciontipo)
				where h.codsucursal=? and lh.estado=1 and lh.fecha>=? and lh.fecha<=?
					".($codhabitacion > 0 ? " and h.codhabitacion=".$codhabitacion : "")."
				order by lh.fecha desc, lh.hora desc",
				[(int)$_SESSION["phuyu_codsucursal"], $desde, $hasta]
			)->result_array();

			echo json_encode([
				"ocupacion" => $ocupacion,
				"consumos" => $consumos,
				"clientes" => $clientes,
				"diaria" => $diaria,
				"mensual" => $mensual,
				"habitaciones" => $habitacionesUsadas,
				"consumos_habitacion" => $consumosHabitacion,
				"historial" => $historial,
				"reservas_pendientes" => $reservasPendientes,
				"reservas_confirmadas" => $reservasConfirmadas,
				"habitaciones_libres" => $habitacionesLibres,
				"mantenimiento" => $mantenimiento,
				"limpieza" => $limpieza
			]);
		}
	}
}
