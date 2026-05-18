<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Reservas extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model("phuyu_model");
		$this->load->model("Hotel_model");
	}

	private function estado_texto($situacion){
		$estados = [
			0 => "ANULADA",
			1 => "PENDIENTE",
			2 => "CONFIRMADA",
			3 => "EN HOSPEDAJE",
			4 => "FINALIZADA"
		];
		return $estados[(int)$situacion] ?? "SIN ESTADO";
	}

	private function reserva_detalle($codreserva){
		return $this->db->query(
			"select r.*, p.documento, p.razonsocial, p.direccion as direccion_persona,
				rh.codhabitacion, rh.precio, h.numero, h.situacion as situacion_habitacion,
				coalesce(a.descripcion, h.piso) as ambiente, ht.descripcion as tipo,
				(select e.codestadia from hotel.estadias e where e.codreserva=r.codreserva and e.estado=1 order by e.codestadia desc limit 1) as codestadia
			from hotel.reservas r
			inner join public.personas p on(p.codpersona=r.codpersona)
			left join hotel.reserva_habitaciones rh on(rh.codreserva=r.codreserva and rh.estado=1)
			left join hotel.habitaciones h on(h.codhabitacion=rh.codhabitacion)
			left join hotel.ambientes a on(a.codambiente=h.codambiente)
			left join hotel.habitacion_tipos ht on(ht.codhabitaciontipo=h.codhabitaciontipo)
			where r.codreserva=? and r.codsucursal=? and r.estado=1
			limit 1",
			[(int)$codreserva, (int)$_SESSION["phuyu_codsucursal"]]
		)->row_array();
	}

	private function validar_reserva_conflicto($reserva){
		if (empty($reserva) || (int)$reserva["codhabitacion"] <= 0) {
			return ["estado" => 0, "mensaje" => "RESERVA SIN HABITACION"];
		}
		return $this->Hotel_model->habitacion_disponible_rango(
			(int)$reserva["codhabitacion"],
			$reserva["fechallegada"],
			$reserva["fechasalida"],
			(int)$reserva["codreserva"]
		);
	}

	private function liberar_habitacion_reserva($codhabitacion){
		$activa = $this->db->query(
			"select r.codreserva
			from hotel.reserva_habitaciones rh
			inner join hotel.reservas r on(r.codreserva=rh.codreserva)
			where rh.codhabitacion=? and rh.estado=1 and r.estado=1 and r.situacion in (2,3)
			limit 1",
			[(int)$codhabitacion]
		)->row_array();
		if (empty($activa) && !$this->Hotel_model->habitacion_tiene_estadia_activa((int)$codhabitacion)) {
			$this->phuyu_model->phuyu_editar("hotel.habitaciones", ["situacion"], [1], "codhabitacion", (int)$codhabitacion);
		}
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$ambientes = $this->Hotel_model->ambientes();
				$habitaciones = $this->Hotel_model->disponibilidad_habitaciones_rango(date("Y-m-d"), date("Y-m-d", strtotime("+1 day")));
				$sucursales = $this->db->query(
					"select s.codsucursal, s.descripcion
					from public.sucursales s
					inner join seguridad.sucursalusuarios su on(su.codsucursal=s.codsucursal)
					where su.codusuario=? and s.estado=1
					order by s.descripcion",
					[(int)$_SESSION["phuyu_codusuario"]]
				)->result_array();
				$this->load->view("hotel/reservas/index", compact("ambientes", "habitaciones", "sucursales"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function lista(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents("php://input"));
			$buscar = isset($this->request->buscar) ? trim($this->request->buscar) : "";
			$lista = $this->db->query(
				"select r.*, p.documento, p.razonsocial,
					string_agg(h.numero, ', ' order by h.numero) as habitaciones,
					min(rh.codhabitacion) as codhabitacion,
					(select e.codestadia from hotel.estadias e where e.codreserva=r.codreserva and e.estado=1 order by e.codestadia desc limit 1) as codestadia
				from hotel.reservas r
				inner join public.personas p on(p.codpersona=r.codpersona)
				left join hotel.reserva_habitaciones rh on(rh.codreserva=r.codreserva and rh.estado=1)
				left join hotel.habitaciones h on(h.codhabitacion=rh.codhabitacion)
				where r.codsucursal=? and r.estado=1
				  and (upper(p.razonsocial) like upper(?) or upper(p.documento) like upper(?) or upper(coalesce(r.cliente,'')) like upper(?))
				group by r.codreserva, p.documento, p.razonsocial
				order by r.fechallegada desc, r.codreserva desc
				limit 120",
				[(int)$_SESSION["phuyu_codsucursal"], "%".$buscar."%", "%".$buscar."%", "%".$buscar."%"]
			)->result_array();
			foreach ($lista as $key => $item) {
				$lista[$key]["situacion_texto"] = $this->estado_texto($item["situacion"]);
			}
			echo json_encode($lista);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function detalle(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents("php://input"));
			$reserva = $this->reserva_detalle((int)$this->request->codreserva);
			if (empty($reserva)) {
				echo json_encode(["estado" => 0, "mensaje" => "RESERVA NO ENCONTRADA"]);
				return;
			}
			$reserva["situacion_texto"] = $this->estado_texto($reserva["situacion"]);
			echo json_encode(["estado" => 1, "reserva" => $reserva]);
		}
	}

	public function clientes(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents("php://input"));
			$buscar = isset($this->request->buscar) ? trim($this->request->buscar) : "";
			$clientes = $this->db->query(
				"select codpersona, documento, razonsocial, direccion
				from public.personas
				where estado=1
				  and (upper(documento) like upper(?) or upper(razonsocial) like upper(?))
				order by razonsocial limit 20",
				["%".$buscar."%", "%".$buscar."%"]
			)->result_array();
			echo json_encode($clientes);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function disponibilidad(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents("php://input"));
			$codhabitacion = isset($this->request->codhabitacion) ? (int)$this->request->codhabitacion : 0;
			if ($codhabitacion == 0) {
				echo json_encode([
					"estado" => 1,
					"mensaje" => "DISPONIBILIDAD GENERAL",
					"habitaciones" => $this->Hotel_model->disponibilidad_habitaciones_rango(
						$this->request->fechallegada,
						$this->request->fechasalida,
						isset($this->request->codambiente) ? (int)$this->request->codambiente : 0,
						isset($this->request->codreserva) ? (int)$this->request->codreserva : 0
					)
				]);
				return;
			}
			$resultado = $this->Hotel_model->habitacion_disponible_rango(
				$codhabitacion,
				$this->request->fechallegada,
				$this->request->fechasalida,
				isset($this->request->codreserva) ? (int)$this->request->codreserva : 0
			);
			$resultado["eventos"] = $this->Hotel_model->ocupacion_habitacion_rango(
				$codhabitacion,
				$this->request->fechallegada,
				$this->request->fechasalida,
				isset($this->request->codreserva) ? (int)$this->request->codreserva : 0
			);
			$resultado["habitaciones"] = $this->Hotel_model->disponibilidad_habitaciones_rango(
				$this->request->fechallegada,
				$this->request->fechasalida,
				isset($this->request->codambiente) ? (int)$this->request->codambiente : 0,
				isset($this->request->codreserva) ? (int)$this->request->codreserva : 0
			);
			echo json_encode($resultado);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function guardar(){
		if ($this->input->is_ajax_request()) {
			if (!isset($_SESSION["phuyu_usuario"])) { echo json_encode("e"); return; }
			$this->request = json_decode(file_get_contents("php://input"));
			$codreserva = isset($this->request->codreserva) ? (int)$this->request->codreserva : 0;
			$codhabitacion = (int)$this->request->codhabitacion;
			$situacion = (int)$this->request->situacion;
			if (!in_array($situacion, [1,2], true)) { $situacion = 1; }
			if (strtotime($this->request->fechasalida) <= strtotime($this->request->fechallegada)) {
				echo json_encode(["estado" => 0, "mensaje" => "LA FECHA DE SALIDA DEBE SER MAYOR A LA FECHA DE LLEGADA"]);
				return;
			}
			$validacion = $this->Hotel_model->habitacion_disponible_rango($codhabitacion, $this->request->fechallegada, $this->request->fechasalida, $codreserva);
			if ((int)$validacion["estado"] != 1) {
				echo json_encode($validacion);
				return;
			}

			$persona = $this->db->query("select * from public.personas where codpersona=? and estado=1", [(int)$this->request->codpersona])->row_array();
			if (empty($persona)) {
				echo json_encode(["estado" => 0, "mensaje" => "CLIENTE NO ENCONTRADO"]);
				return;
			}
			$habitacion = $this->db->query("select * from hotel.habitaciones where codhabitacion=? and codsucursal=? and estado=1", [$codhabitacion, (int)$_SESSION["phuyu_codsucursal"]])->row_array();
			if (empty($habitacion)) {
				echo json_encode(["estado" => 0, "mensaje" => "HABITACION NO ENCONTRADA"]);
				return;
			}

			$habitacionAnterior = 0;
			if ($codreserva > 0) {
				$actual = $this->reserva_detalle($codreserva);
				if (empty($actual) || !in_array((int)$actual["situacion"], [1,2], true)) {
					echo json_encode(["estado" => 0, "mensaje" => "SOLO SE PUEDEN EDITAR RESERVAS PENDIENTES O CONFIRMADAS"]);
					return;
				}
				$habitacionAnterior = (int)$actual["codhabitacion"];
			}

			$this->db->trans_begin();
			$campos = ["codsucursal","codpersona","codusuario","fechallegada","fechasalida","cliente","direccion","observacion","situacion"];
			$valores = [
				(int)$_SESSION["phuyu_codsucursal"], (int)$persona["codpersona"], (int)$_SESSION["phuyu_codusuario"],
				$this->request->fechallegada, $this->request->fechasalida, $persona["razonsocial"], $persona["direccion"],
				$this->request->observacion, $situacion
			];

			if ($codreserva == 0) {
				$codreserva = $this->phuyu_model->phuyu_guardar("hotel.reservas", $campos, $valores, "true");
			}else{
				array_shift($campos); array_shift($valores);
				$this->phuyu_model->phuyu_editar("hotel.reservas", $campos, $valores, "codreserva", $codreserva);
				$this->db->where("codreserva", $codreserva)->update("hotel.reserva_habitaciones", ["estado" => 0]);
			}

			$estado = $this->db->query(
				"insert into hotel.reserva_habitaciones (codreserva, codhabitacion, precio, estado)
				values (?, ?, ?, 1)
				on conflict (codreserva, codhabitacion)
				do update set precio=excluded.precio, estado=1",
				[$codreserva, $codhabitacion, (double)$habitacion["preciobase"]]
			);
			if ($situacion == 2) {
				$this->phuyu_model->phuyu_editar("hotel.habitaciones", ["situacion"], [2], "codhabitacion", $codhabitacion);
			}
			if ($habitacionAnterior > 0 && $habitacionAnterior != $codhabitacion) {
				$this->liberar_habitacion_reserva($habitacionAnterior);
			}

			if ($this->db->trans_status() === FALSE || !$estado) {
				$this->db->trans_rollback();
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUDO GUARDAR RESERVA"]);
				return;
			}
			$this->db->trans_commit();
			echo json_encode(["estado" => 1, "codreserva" => $codreserva]);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function confirmar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents("php://input"));
			$reserva = $this->reserva_detalle((int)$this->request->codreserva);
			if (empty($reserva)) {
				echo json_encode(["estado" => 0, "mensaje" => "RESERVA NO ENCONTRADA"]);
				return;
			}
			if ((int)$reserva["situacion"] != 1) {
				echo json_encode(["estado" => 0, "mensaje" => "SOLO SE PUEDEN CONFIRMAR RESERVAS PENDIENTES"]);
				return;
			}
			$validacion = $this->validar_reserva_conflicto($reserva);
			if ((int)$validacion["estado"] != 1) {
				echo json_encode($validacion);
				return;
			}
			$this->db->trans_begin();
			$estado = $this->phuyu_model->phuyu_editar("hotel.reservas", ["situacion"], [2], "codreserva", (int)$reserva["codreserva"]);
			$this->phuyu_model->phuyu_editar("hotel.habitaciones", ["situacion"], [2], "codhabitacion", (int)$reserva["codhabitacion"]);
			if ($this->db->trans_status() === FALSE || $estado != 1) { $this->db->trans_rollback(); $estado = 0; } else { $this->db->trans_commit(); }
			echo json_encode(["estado" => $estado]);
		}
	}

	public function checkin(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents("php://input"));
			$reserva = $this->reserva_detalle((int)$this->request->codreserva);
			if (empty($reserva)) {
				echo json_encode(["estado" => 0, "mensaje" => "RESERVA NO ENCONTRADA"]);
				return;
			}
			if ((int)$reserva["situacion"] == 0) {
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUEDE HACER CHECK-IN DE UNA RESERVA ANULADA"]);
				return;
			}
			if ((int)$reserva["situacion"] != 2) {
				echo json_encode(["estado" => 0, "mensaje" => "LA RESERVA DEBE ESTAR CONFIRMADA"]);
				return;
			}
			if ((int)$reserva["codestadia"] > 0) {
				echo json_encode(["estado" => 0, "mensaje" => "LA RESERVA YA TIENE CHECK-IN"]);
				return;
			}
			$fechaCheckin = isset($this->request->fecha_checkin) && $this->request->fecha_checkin != "" ? $this->request->fecha_checkin : date("Y-m-d");
			if (strtotime($fechaCheckin) < strtotime($reserva["fechallegada"]) || strtotime($fechaCheckin) >= strtotime($reserva["fechasalida"])) {
				echo json_encode(["estado" => 0, "mensaje" => "LA FECHA DE CHECK-IN DEBE ESTAR DENTRO DEL RANGO DE LA RESERVA"]);
				return;
			}
			$validacion = $this->validar_reserva_conflicto($reserva);
			if ((int)$validacion["estado"] != 1) {
				echo json_encode($validacion);
				return;
			}
			if (!in_array((int)$reserva["situacion_habitacion"], [1,2], true)) {
				echo json_encode(["estado" => 0, "mensaje" => "LA HABITACION NO ESTA DISPONIBLE PARA CHECK-IN"]);
				return;
			}
			if ($this->Hotel_model->habitacion_tiene_estadia_activa((int)$reserva["codhabitacion"])) {
				echo json_encode(["estado" => 0, "mensaje" => "LA HABITACION YA TIENE UNA ESTADIA ACTIVA"]);
				return;
			}

			$this->db->trans_begin();
			$noches = max(1, (int)floor((strtotime($reserva["fechasalida"]) - strtotime($fechaCheckin)) / 86400));
			$precio = (double)($reserva["precio"] > 0 ? $reserva["precio"] : 0);
			$alojamiento = round($noches * $precio, 2);
			$campos = ["codreserva","codsucursal","codalmacen","codpersona","codusuario","codempleado","fecha_checkin","fecha_checkout","noches","alojamiento","importe","cliente","direccion","observacion","situacion"];
			$valores = [
				(int)$reserva["codreserva"], (int)$_SESSION["phuyu_codsucursal"], (int)$_SESSION["phuyu_codalmacen"], (int)$reserva["codpersona"], (int)$_SESSION["phuyu_codusuario"],
				(int)($_SESSION["phuyu_codempleado"] ?? 0), $fechaCheckin, $reserva["fechasalida"], $noches, $alojamiento, $alojamiento,
				$reserva["cliente"], $reserva["direccion"], $this->request->observacion ?? "", 1
			];
			$codestadia = $this->phuyu_model->phuyu_guardar("hotel.estadias", $campos, $valores, "true");
			$estado = $this->phuyu_model->phuyu_guardar("hotel.estadia_habitaciones", ["codestadia","codhabitacion","precio_noche","noches","subtotal"], [(int)$codestadia, (int)$reserva["codhabitacion"], $precio, $noches, $alojamiento]);
			$estado = $this->phuyu_model->phuyu_guardar("hotel.estadia_huespedes", ["codestadia","codpersona","titular"], [(int)$codestadia, (int)$reserva["codpersona"], 1]);
			$this->phuyu_model->phuyu_editar("hotel.habitaciones", ["situacion"], [3], "codhabitacion", (int)$reserva["codhabitacion"]);
			$this->phuyu_model->phuyu_editar("hotel.reservas", ["situacion"], [3], "codreserva", (int)$reserva["codreserva"]);
			if ($this->db->trans_status() === FALSE || $estado != 1) { $this->db->trans_rollback(); $estado = 0; } else { $this->db->trans_commit(); }
			echo json_encode(["estado" => $estado, "codestadia" => $codestadia]);
		}
	}

	public function anular(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents("php://input"));
			$reserva = $this->reserva_detalle((int)$this->request->codreserva);
			if (empty($reserva)) {
				echo json_encode(["estado" => 0, "mensaje" => "RESERVA NO ENCONTRADA"]);
				return;
			}
			if (in_array((int)$reserva["situacion"], [3,4], true)) {
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUEDE ANULAR UNA RESERVA EN HOSPEDAJE O FINALIZADA"]);
				return;
			}
			$this->db->trans_begin();
			$estado = $this->phuyu_model->phuyu_editar("hotel.reservas", ["situacion"], [0], "codreserva", (int)$reserva["codreserva"]);
			$this->liberar_habitacion_reserva((int)$reserva["codhabitacion"]);
			if ($this->db->trans_status() === FALSE || $estado != 1) { $this->db->trans_rollback(); $estado = 0; } else { $this->db->trans_commit(); }
			echo json_encode(["estado" => $estado]);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function cancelar(){
		$this->anular();
	}

	public function calendario(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents("php://input"));
			$anio = (int)($this->request->anio ?? date("Y"));
			$mes = (int)($this->request->mes ?? date("n"));
			$codsucursal = (int)($this->request->codsucursal ?? $_SESSION["phuyu_codsucursal"]);
			$permitida = $this->db->query(
				"select 1 from seguridad.sucursalusuarios where codusuario=? and codsucursal=? limit 1",
				[(int)$_SESSION["phuyu_codusuario"], $codsucursal]
			)->row_array();
			if (empty($permitida)) { $codsucursal = (int)$_SESSION["phuyu_codsucursal"]; }
			$codambiente = (int)($this->request->codambiente ?? 0);
			$codhabitacion = (int)($this->request->codhabitacion ?? 0);
			$desde = date("Y-m-01", strtotime($anio."-".str_pad((string)$mes, 2, "0", STR_PAD_LEFT)."-01"));
			$hasta = date("Y-m-d", strtotime($desde." +1 month"));

			$params = [$codsucursal];
			$where = "";
			if ($codambiente > 0) { $where .= " and h.codambiente=?"; $params[] = $codambiente; }
			if ($codhabitacion > 0) { $where .= " and h.codhabitacion=?"; $params[] = $codhabitacion; }
			$habitaciones = $this->db->query(
				"select h.codhabitacion, h.numero, h.situacion, ht.descripcion as tipo, coalesce(a.descripcion, h.piso) as ambiente
				from hotel.habitaciones h
				inner join hotel.habitacion_tipos ht on(ht.codhabitaciontipo=h.codhabitaciontipo)
				left join hotel.ambientes a on(a.codambiente=h.codambiente)
				where h.codsucursal=? and h.estado=1".$where."
				order by coalesce(a.descripcion, h.piso),
					nullif(regexp_replace(h.numero::text, '\\D', '', 'g'), '')::int nulls last,
					h.numero",
				$params
			)->result_array();

			$calendario = [];
			$diasSemana = ["Dom", "Lun", "Mar", "Mie", "Jue", "Vie", "Sab"];
			foreach ($habitaciones as $habitacion) {
				$eventos = $this->Hotel_model->ocupacion_habitacion_rango((int)$habitacion["codhabitacion"], $desde, $hasta);
				$dias = [];
				for ($fecha = strtotime($desde); $fecha < strtotime($hasta); $fecha = strtotime("+1 day", $fecha)) {
					$iso = date("Y-m-d", $fecha);
					$eventoDia = null;
					foreach ($eventos as $evento) {
						if ($evento["desde"] <= $iso && $evento["hasta"] > $iso) {
							$eventoDia = $evento;
							if ($eventoDia["tipo"] == "reserva") {
								$eventoDia["estado"] = $this->estado_texto((int)$eventoDia["situacion"]);
							}else if ($eventoDia["tipo"] == "estadia") {
								$eventoDia["estado"] = "OCUPADA";
							}else if ($eventoDia["tipo"] == "mantenimiento") {
								$eventoDia["estado"] = "MANTENIMIENTO";
							}else if ($eventoDia["tipo"] == "limpieza") {
								$eventoDia["estado"] = "LIMPIEZA";
							}
							break;
						}
					}
					$dias[] = [
						"fecha" => $iso,
						"dia" => date("j", $fecha),
						"nombre" => $diasSemana[(int)date("w", $fecha)],
						"estado" => empty($eventoDia) ? "libre" : $eventoDia["tipo"],
						"evento" => $eventoDia
					];
				}
				$calendario[] = ["habitacion" => $habitacion, "dias" => $dias];
			}
			echo json_encode(["estado" => 1, "desde" => $desde, "hasta" => $hasta, "calendario" => $calendario]);
		}
	}
}
