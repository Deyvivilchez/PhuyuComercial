<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Mantenimiento extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model("phuyu_model");
		$this->load->model("Hotel_model");
		$this->asegurar_columnas();
	}

	private function asegurar_columnas(){
		$this->db->query("ALTER TABLE hotel.mantenimiento_habitaciones ADD COLUMN IF NOT EXISTS tipo_mantenimiento varchar(40) DEFAULT 'correctivo'");
		$this->db->query("ALTER TABLE hotel.mantenimiento_habitaciones ADD COLUMN IF NOT EXISTS prioridad varchar(20) DEFAULT 'media'");
		$this->db->query("ALTER TABLE hotel.mantenimiento_habitaciones ADD COLUMN IF NOT EXISTS descripcion_problema text");
		$this->db->query("ALTER TABLE hotel.mantenimiento_habitaciones ADD COLUMN IF NOT EXISTS trabajos_realizados text");
		$this->db->query("ALTER TABLE hotel.mantenimiento_habitaciones ADD COLUMN IF NOT EXISTS materiales text");
		$this->db->query("ALTER TABLE hotel.mantenimiento_habitaciones ADD COLUMN IF NOT EXISTS estado_orden integer DEFAULT 1");
		$this->db->query("ALTER TABLE hotel.mantenimiento_habitaciones ADD COLUMN IF NOT EXISTS hora_inicio time DEFAULT now()");
		$this->db->query("ALTER TABLE hotel.mantenimiento_habitaciones ADD COLUMN IF NOT EXISTS hora_fin time");
		$this->db->query("UPDATE hotel.mantenimiento_habitaciones SET estado_orden=3 WHERE situacion=2 AND coalesce(estado_orden,1)=1");
	}

	private function texto_estado($estado){
		$estados = [1 => "PENDIENTE", 2 => "EN PROCESO", 3 => "FINALIZADO", 4 => "ANULADO"];
		return $estados[(int)$estado] ?? "PENDIENTE";
	}

	private function texto_tipo($tipo){
		$tipos = [
			"correctivo" => "Correctivo",
			"preventivo" => "Preventivo",
			"electrico" => "Electrico",
			"gasfiteria" => "Gasfiteria",
			"carpinteria" => "Carpinteria",
			"pintura" => "Pintura",
			"aire_acondicionado" => "Aire acondicionado",
			"tv_internet" => "TV/Internet",
			"cerradura_puerta" => "Cerradura/puerta",
			"mobiliario" => "Mobiliario",
			"otro" => "Otro"
		];
		return $tipos[$tipo] ?? $tipos["correctivo"];
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$this->load->view("hotel/mantenimiento/index");
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function habitaciones(){
		if ($this->input->is_ajax_request()) {
			echo json_encode($this->Hotel_model->habitaciones_resumen());
		}
	}

	public function responsables(){
		if ($this->input->is_ajax_request()) {
			$lista = $this->db->query(
				"select p.codpersona, p.razonsocial
				from public.personas p
				inner join public.empleados e on(e.codpersona=p.codpersona)
				where p.estado=1 and e.estado=1
				order by p.razonsocial"
			)->result_array();
			echo json_encode($lista);
		}
	}

	public function listar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents("php://input"));
			$desde = trim((string)($this->request->desde ?? date("Y-m-d")));
			$hasta = trim((string)($this->request->hasta ?? date("Y-m-d")));
			$codhabitacion = (int)($this->request->codhabitacion ?? 0);
			$codresponsable = (int)($this->request->codresponsable ?? 0);
			$estadoOrden = (int)($this->request->estado_orden ?? 0);

			$where = "";
			$params = [(int)$_SESSION["phuyu_codsucursal"], $desde, $hasta];
			if ($codhabitacion > 0) { $where .= " and h.codhabitacion=?"; $params[] = $codhabitacion; }
			if ($codresponsable > 0) { $where .= " and mh.codresponsable=?"; $params[] = $codresponsable; }
			if ($estadoOrden > 0) { $where .= " and coalesce(mh.estado_orden, mh.situacion, 1)=?"; $params[] = $estadoOrden; }

			$lista = $this->db->query(
				"select mh.*, h.numero, coalesce(a.descripcion, h.piso) as ambiente, s.descripcion as sucursal,
					coalesce(pr.razonsocial, '') as responsable,
					coalesce(pu.razonsocial, u.usuario) as usuario
				from hotel.mantenimiento_habitaciones mh
				inner join hotel.habitaciones h on(h.codhabitacion=mh.codhabitacion)
				left join hotel.ambientes a on(a.codambiente=h.codambiente)
				left join public.sucursales s on(s.codsucursal=h.codsucursal)
				left join public.personas pr on(pr.codpersona=mh.codresponsable)
				left join seguridad.usuarios u on(u.codusuario=mh.codusuario)
				left join public.personas pu on(pu.codpersona=u.codempleado)
				where h.codsucursal=? and mh.estado=1 and mh.fecha_inicio>=? and mh.fecha_inicio<=?".$where."
				order by mh.codmantenimiento desc",
				$params
			)->result_array();

			foreach ($lista as $key => $item) {
				$lista[$key]["numero_orden"] = str_pad((string)$item["codmantenimiento"], 6, "0", STR_PAD_LEFT);
				$lista[$key]["tipo_mantenimiento_texto"] = $this->texto_tipo($item["tipo_mantenimiento"] ?? "correctivo");
				$lista[$key]["estado_orden_texto"] = $this->texto_estado($item["estado_orden"] ?? $item["situacion"] ?? 1);
			}

			echo json_encode($lista);
		}
	}

	public function guardar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents("php://input"));
			$codhabitacion = (int)$this->request->codhabitacion;
			$habitacion = $this->db->query(
				"select * from hotel.habitaciones where codhabitacion=? and codsucursal=? and estado=1 limit 1",
				[$codhabitacion, (int)$_SESSION["phuyu_codsucursal"]]
			)->row_array();
			if (empty($habitacion)) {
				echo json_encode(["estado" => 0, "mensaje" => "HABITACION NO ENCONTRADA"]);
				return;
			}
			if ($this->Hotel_model->habitacion_tiene_estadia_activa($codhabitacion)) {
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUEDE ENVIAR A MANTENIMIENTO: LA HABITACION TIENE ESTADIA ACTIVA"]);
				return;
			}
			if (in_array((int)$habitacion["situacion"], [3,5])) {
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUEDE ENVIAR A MANTENIMIENTO EN EL ESTADO ACTUAL"]);
				return;
			}
			$activa = $this->db->query(
				"select codmantenimiento from hotel.mantenimiento_habitaciones where codhabitacion=? and estado=1 and coalesce(estado_orden, situacion, 1) in (1,2) order by codmantenimiento desc limit 1",
				[$codhabitacion]
			)->row_array();
			if (!empty($activa)) {
				echo json_encode(["estado" => 0, "mensaje" => "LA HABITACION YA TIENE UNA ORDEN DE MANTENIMIENTO ACTIVA"]);
				return;
			}
			$tipo = trim((string)($this->request->tipo_mantenimiento ?? "correctivo"));
			$tipos = ["correctivo","preventivo","electrico","gasfiteria","carpinteria","pintura","aire_acondicionado","tv_internet","cerradura_puerta","mobiliario","otro"];
			if (!in_array($tipo, $tipos)) { $tipo = "correctivo"; }
			$prioridad = trim((string)($this->request->prioridad ?? "media"));
			if (!in_array($prioridad, ["baja","media","alta","urgente"])) { $prioridad = "media"; }
			$fechaFin = trim((string)($this->request->fecha_fin ?? ""));
			$fechaFin = $fechaFin != "" ? $fechaFin : null;

			$this->db->trans_begin();
			$estado = $this->phuyu_model->phuyu_guardar(
				"hotel.mantenimiento_habitaciones",
				["codhabitacion","codusuario","codresponsable","observacion","situacion","tipo_mantenimiento","prioridad","descripcion_problema","trabajos_realizados","materiales","estado_orden","fecha_fin","hora_inicio"],
				[$codhabitacion,(int)$_SESSION["phuyu_codusuario"],(int)($this->request->codresponsable ?? 0),$this->request->observacion ?? "",1,$tipo,$prioridad,$this->request->descripcion_problema ?? "",$this->request->trabajos_realizados ?? "",$this->request->materiales ?? "",1,$fechaFin,date("H:i:s")],
				"true"
			);
			$codmantenimiento = (int)$estado;
			$estado = $this->phuyu_model->phuyu_editar("hotel.habitaciones", ["situacion"], [5], "codhabitacion", $codhabitacion);
			if ($this->db->trans_status() === FALSE || $estado != 1) { $this->db->trans_rollback(); $estado = 0; } else { $this->db->trans_commit(); }
			echo json_encode(["estado" => $estado, "codmantenimiento" => $codmantenimiento]);
		}
	}

	public function cambiar_estado(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents("php://input"));
			$codmantenimiento = (int)($this->request->codmantenimiento ?? 0);
			$estadoOrden = (int)($this->request->estado_orden ?? 0);
			if (!in_array($estadoOrden, [2,4])) {
				echo json_encode(["estado" => 0, "mensaje" => "ESTADO INVALIDO"]);
				return;
			}
			$orden = $this->db->query(
				"select mh.*, h.codsucursal from hotel.mantenimiento_habitaciones mh inner join hotel.habitaciones h on(h.codhabitacion=mh.codhabitacion) where mh.codmantenimiento=? and h.codsucursal=? and mh.estado=1 limit 1",
				[$codmantenimiento, (int)$_SESSION["phuyu_codsucursal"]]
			)->row_array();
			if (empty($orden)) {
				echo json_encode(["estado" => 0, "mensaje" => "ORDEN NO ENCONTRADA"]);
				return;
			}
			$this->db->trans_begin();
			$this->db->where("codmantenimiento", $codmantenimiento);
			$estado = $this->db->update("hotel.mantenimiento_habitaciones", ["estado_orden" => $estadoOrden, "situacion" => $estadoOrden == 4 ? 2 : 1]);
			if ($estadoOrden == 4) {
				$estado = $this->phuyu_model->phuyu_editar("hotel.habitaciones", ["situacion"], [1], "codhabitacion", (int)$orden["codhabitacion"]);
			}
			if ($this->db->trans_status() === FALSE || $estado != 1) { $this->db->trans_rollback(); $estado = 0; } else { $this->db->trans_commit(); }
			echo json_encode(["estado" => $estado ? 1 : 0]);
		}
	}

	public function finalizar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents("php://input"));
			$codhabitacion = (int)($this->request->codhabitacion ?? 0);
			$codmantenimiento = (int)($this->request->codmantenimiento ?? 0);
			$destino = (int)$this->request->situacion;
			if (!in_array($destino, [1,4])) {
				echo json_encode(["estado" => 0, "mensaje" => "ESTADO DESTINO INVALIDO"]);
				return;
			}
			$mantenimiento = $this->db->query(
				"select mh.*, h.situacion as situacion_habitacion
				from hotel.mantenimiento_habitaciones mh
				inner join hotel.habitaciones h on(h.codhabitacion=mh.codhabitacion)
				where ".($codmantenimiento > 0 ? "mh.codmantenimiento=?" : "mh.codhabitacion=?")." and h.codsucursal=? and coalesce(mh.estado_orden, mh.situacion, 1) in (1,2) and mh.estado=1
				order by codmantenimiento desc limit 1",
				[$codmantenimiento > 0 ? $codmantenimiento : $codhabitacion, (int)$_SESSION["phuyu_codsucursal"]]
			)->row_array();
			if (empty($mantenimiento)) {
				echo json_encode(["estado" => 0, "mensaje" => "NO HAY MANTENIMIENTO ACTIVO"]);
				return;
			}
			$codhabitacion = (int)$mantenimiento["codhabitacion"];
			if ($this->Hotel_model->habitacion_tiene_estadia_activa($codhabitacion)) {
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUEDE FINALIZAR: LA HABITACION TIENE ESTADIA ACTIVA"]);
				return;
			}

			$observacion = trim((string)($this->request->observacion_final ?? ""));
			$observacionFinal = trim((string)$mantenimiento["observacion"]);
			if ($observacion != "") {
				$observacionFinal .= ($observacionFinal != "" ? "\n" : "")."FIN: ".$observacion;
			}

			$this->db->trans_begin();
			$this->db->where("codmantenimiento", (int)$mantenimiento["codmantenimiento"]);
			$estado = $this->db->update("hotel.mantenimiento_habitaciones", [
				"situacion" => 2,
				"estado_orden" => 3,
				"fecha_fin" => date("Y-m-d"),
				"hora_fin" => date("H:i:s"),
				"trabajos_realizados" => trim((string)($this->request->trabajos_realizados ?? $mantenimiento["trabajos_realizados"] ?? "")),
				"materiales" => trim((string)($this->request->materiales ?? $mantenimiento["materiales"] ?? "")),
				"observacion" => $observacionFinal
			]);
			$estado = $this->phuyu_model->phuyu_editar("hotel.habitaciones", ["situacion"], [$destino], "codhabitacion", $codhabitacion);
			if ($this->db->trans_status() === FALSE || $estado != 1) { $this->db->trans_rollback(); $estado = 0; } else { $this->db->trans_commit(); }
			echo json_encode(["estado" => $estado]);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function ticket($codmantenimiento = 0){
		if (!isset($_SESSION["phuyu_usuario"])) { $this->load->view("phuyu/505"); return; }
		$orden = $this->db->query(
			"select mh.*, h.numero, coalesce(a.descripcion, h.piso) as ambiente, s.descripcion as sucursal,
				coalesce(pr.razonsocial, 'SIN RESPONSABLE') as responsable,
				coalesce(pu.razonsocial, u.usuario) as usuario
			from hotel.mantenimiento_habitaciones mh
			inner join hotel.habitaciones h on(h.codhabitacion=mh.codhabitacion)
			left join hotel.ambientes a on(a.codambiente=h.codambiente)
			left join public.sucursales s on(s.codsucursal=h.codsucursal)
			left join public.personas pr on(pr.codpersona=mh.codresponsable)
			left join seguridad.usuarios u on(u.codusuario=mh.codusuario)
			left join public.personas pu on(pu.codpersona=u.codempleado)
			where mh.codmantenimiento=? and h.codsucursal=? and mh.estado=1 limit 1",
			[(int)$codmantenimiento, (int)$_SESSION["phuyu_codsucursal"]]
		)->row_array();
		if (empty($orden)) { show_404(); return; }
		$orden["numero_orden"] = str_pad((string)$orden["codmantenimiento"], 6, "0", STR_PAD_LEFT);
		$orden["tipo_mantenimiento_texto"] = $this->texto_tipo($orden["tipo_mantenimiento"] ?? "correctivo");
		$orden["estado_orden_texto"] = $this->texto_estado($orden["estado_orden"] ?? $orden["situacion"] ?? 1);
		$this->load->view("hotel/mantenimiento/ticket", ["orden" => $orden]);
	}
}
