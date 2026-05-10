<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Limpieza extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model("phuyu_model");
		$this->load->model("Hotel_model");
		$this->asegurar_columnas();
	}

	private function asegurar_columnas(){
		$this->db->query("ALTER TABLE hotel.limpieza_habitaciones ADD COLUMN IF NOT EXISTS tipo_limpieza varchar(40) DEFAULT 'normal'");
		$this->db->query("ALTER TABLE hotel.limpieza_habitaciones ADD COLUMN IF NOT EXISTS checklist text");
		$this->db->query("ALTER TABLE hotel.limpieza_habitaciones ADD COLUMN IF NOT EXISTS estado_orden integer DEFAULT 1");
		$this->db->query("ALTER TABLE hotel.limpieza_habitaciones ADD COLUMN IF NOT EXISTS fecha_fin date");
		$this->db->query("ALTER TABLE hotel.limpieza_habitaciones ADD COLUMN IF NOT EXISTS hora_fin time");
	}

	private function texto_estado($estado){
		$estados = [1 => "PENDIENTE", 2 => "EN PROCESO", 3 => "FINALIZADO", 4 => "ANULADO"];
		return $estados[(int)$estado] ?? "PENDIENTE";
	}

	private function texto_tipo($tipo){
		$tipos = [
			"normal" => "Limpieza normal",
			"profunda" => "Limpieza profunda",
			"post_checkout" => "Limpieza post check-out",
			"incidencia" => "Limpieza por incidencia"
		];
		return $tipos[$tipo] ?? $tipos["normal"];
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$this->load->view("hotel/limpieza/index");
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
			if ($codresponsable > 0) { $where .= " and lh.codresponsable=?"; $params[] = $codresponsable; }
			if ($estadoOrden > 0) { $where .= " and coalesce(lh.estado_orden,1)=?"; $params[] = $estadoOrden; }

			$lista = $this->db->query(
				"select lh.*, h.numero, coalesce(a.descripcion, h.piso) as ambiente, s.descripcion as sucursal,
					coalesce(pr.razonsocial, '') as responsable,
					coalesce(pu.razonsocial, u.usuario) as usuario
				from hotel.limpieza_habitaciones lh
				inner join hotel.habitaciones h on(h.codhabitacion=lh.codhabitacion)
				left join hotel.ambientes a on(a.codambiente=h.codambiente)
				left join public.sucursales s on(s.codsucursal=h.codsucursal)
				left join public.personas pr on(pr.codpersona=lh.codresponsable)
				left join seguridad.usuarios u on(u.codusuario=lh.codusuario)
				left join public.personas pu on(pu.codpersona=u.codempleado)
				where h.codsucursal=? and lh.estado=1 and lh.fecha>=? and lh.fecha<=?".$where."
				order by lh.codlimpieza desc",
				$params
			)->result_array();

			foreach ($lista as $key => $item) {
				$lista[$key]["numero_orden"] = str_pad((string)$item["codlimpieza"], 6, "0", STR_PAD_LEFT);
				$lista[$key]["tipo_limpieza_texto"] = $this->texto_tipo($item["tipo_limpieza"] ?? "normal");
				$lista[$key]["estado_orden_texto"] = $this->texto_estado($item["estado_orden"] ?? 1);
				$lista[$key]["checklist_items"] = json_decode($item["checklist"] ?? "[]", true) ?: [];
			}

			echo json_encode($lista);
		}
	}

	public function guardar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents("php://input"));
			$codhabitacion = (int)$this->request->codhabitacion;
			$codlimpieza = (int)($this->request->codlimpieza ?? 0);
			$habitacion = $this->db->query(
				"select * from hotel.habitaciones where codhabitacion=? and codsucursal=? and estado=1 limit 1",
				[$codhabitacion, (int)$_SESSION["phuyu_codsucursal"]]
			)->row_array();
			if (empty($habitacion)) {
				echo json_encode(["estado" => 0, "mensaje" => "HABITACION NO ENCONTRADA"]);
				return;
			}
			if ($this->Hotel_model->habitacion_tiene_estadia_activa($codhabitacion)) {
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUEDE CAMBIAR LIMPIEZA/DISPONIBLE: LA HABITACION TIENE ESTADIA ACTIVA"]);
				return;
			}
			if ($codlimpieza == 0 && in_array((int)$habitacion["situacion"], [3,5,6])) {
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUEDE ENVIAR A LIMPIEZA EN EL ESTADO ACTUAL"]);
				return;
			}
			if ($codlimpieza > 0) {
				$orden = $this->db->query(
					"select lh.*, h.codsucursal
					from hotel.limpieza_habitaciones lh
					inner join hotel.habitaciones h on(h.codhabitacion=lh.codhabitacion)
					where lh.codlimpieza=? and h.codsucursal=? and lh.estado=1 limit 1",
					[$codlimpieza, (int)$_SESSION["phuyu_codsucursal"]]
				)->row_array();
				if (empty($orden)) {
					echo json_encode(["estado" => 0, "mensaje" => "ORDEN NO ENCONTRADA"]);
					return;
				}
				if (!in_array((int)$orden["estado_orden"], [1,2], true)) {
					echo json_encode(["estado" => 0, "mensaje" => "SOLO SE PUEDEN EDITAR ORDENES PENDIENTES O EN PROCESO"]);
					return;
				}
			}
			$activa = $this->db->query(
				"select codlimpieza from hotel.limpieza_habitaciones where codhabitacion=? and estado=1 and coalesce(estado_orden,1) in (1,2) and codlimpieza<>? order by codlimpieza desc limit 1",
				[$codhabitacion, $codlimpieza]
			)->row_array();
			if (!empty($activa)) {
				echo json_encode(["estado" => 0, "mensaje" => "LA HABITACION YA TIENE UNA ORDEN DE LIMPIEZA ACTIVA"]);
				return;
			}

			$tipo = trim((string)($this->request->tipo_limpieza ?? "normal"));
			if (!in_array($tipo, ["normal","profunda","post_checkout","incidencia"])) { $tipo = "normal"; }
			$checklist = $this->request->checklist ?? [];
			if (!is_array($checklist)) { $checklist = []; }

			$this->db->trans_begin();
			if ($codlimpieza > 0) {
				$this->db->where("codlimpieza", $codlimpieza);
				$estado = $this->db->update("hotel.limpieza_habitaciones", [
					"codhabitacion" => $codhabitacion,
					"codresponsable" => (int)($this->request->codresponsable ?? 0),
					"observacion" => $this->request->observacion ?? "",
					"tipo_limpieza" => $tipo,
					"checklist" => json_encode($checklist)
				]);
			}else{
				$estado = $this->phuyu_model->phuyu_guardar(
					"hotel.limpieza_habitaciones",
					["codhabitacion","codusuario","codresponsable","observacion","tipo_limpieza","checklist","estado_orden"],
					[$codhabitacion,(int)$_SESSION["phuyu_codusuario"],(int)($this->request->codresponsable ?? 0),$this->request->observacion ?? "",$tipo,json_encode($checklist),1],
					"true"
				);
				$codlimpieza = (int)$estado;
			}
			$estado = $this->phuyu_model->phuyu_editar("hotel.habitaciones", ["situacion"], [4], "codhabitacion", $codhabitacion);
			if ($this->db->trans_status() === FALSE || $estado != 1) { $this->db->trans_rollback(); $estado = 0; } else { $this->db->trans_commit(); }
			echo json_encode(["estado" => $estado, "codlimpieza" => $codlimpieza]);
		}
	}

	public function cambiar_estado(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents("php://input"));
			$codlimpieza = (int)($this->request->codlimpieza ?? 0);
			$estadoOrden = (int)($this->request->estado_orden ?? 0);
			if (!in_array($estadoOrden, [2,4])) {
				echo json_encode(["estado" => 0, "mensaje" => "ESTADO INVALIDO"]);
				return;
			}
			$orden = $this->db->query(
				"select lh.*, h.codsucursal from hotel.limpieza_habitaciones lh inner join hotel.habitaciones h on(h.codhabitacion=lh.codhabitacion) where lh.codlimpieza=? and h.codsucursal=? and lh.estado=1 limit 1",
				[$codlimpieza, (int)$_SESSION["phuyu_codsucursal"]]
			)->row_array();
			if (empty($orden)) {
				echo json_encode(["estado" => 0, "mensaje" => "ORDEN NO ENCONTRADA"]);
				return;
			}
			$this->db->trans_begin();
			$this->db->where("codlimpieza", $codlimpieza);
			$estado = $this->db->update("hotel.limpieza_habitaciones", ["estado_orden" => $estadoOrden]);
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
			$codlimpieza = (int)($this->request->codlimpieza ?? 0);
			$orden = $this->db->query(
				"select lh.*, h.codsucursal from hotel.limpieza_habitaciones lh inner join hotel.habitaciones h on(h.codhabitacion=lh.codhabitacion) where lh.codlimpieza=? and h.codsucursal=? and lh.estado=1 limit 1",
				[$codlimpieza, (int)$_SESSION["phuyu_codsucursal"]]
			)->row_array();
			if (empty($orden)) {
				echo json_encode(["estado" => 0, "mensaje" => "ORDEN NO ENCONTRADA"]);
				return;
			}
			if ($this->Hotel_model->habitacion_tiene_estadia_activa((int)$orden["codhabitacion"])) {
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUEDE FINALIZAR: LA HABITACION TIENE ESTADIA ACTIVA"]);
				return;
			}
			$this->db->trans_begin();
			$this->db->where("codlimpieza", $codlimpieza);
			$estado = $this->db->update("hotel.limpieza_habitaciones", ["estado_orden" => 3, "fecha_fin" => date("Y-m-d"), "hora_fin" => date("H:i:s")]);
			$estado = $this->phuyu_model->phuyu_editar("hotel.habitaciones", ["situacion"], [1], "codhabitacion", (int)$orden["codhabitacion"]);
			if ($this->db->trans_status() === FALSE || $estado != 1) { $this->db->trans_rollback(); $estado = 0; } else { $this->db->trans_commit(); }
			echo json_encode(["estado" => $estado]);
		}
	}

	public function ticket($codlimpieza = 0){
		if (!isset($_SESSION["phuyu_usuario"])) { $this->load->view("phuyu/505"); return; }
		$orden = $this->db->query(
			"select lh.*, h.numero, coalesce(a.descripcion, h.piso) as ambiente, s.descripcion as sucursal,
				coalesce(pr.razonsocial, 'SIN RESPONSABLE') as responsable,
				coalesce(pu.razonsocial, u.usuario) as usuario
			from hotel.limpieza_habitaciones lh
			inner join hotel.habitaciones h on(h.codhabitacion=lh.codhabitacion)
			left join hotel.ambientes a on(a.codambiente=h.codambiente)
			left join public.sucursales s on(s.codsucursal=h.codsucursal)
			left join public.personas pr on(pr.codpersona=lh.codresponsable)
			left join seguridad.usuarios u on(u.codusuario=lh.codusuario)
			left join public.personas pu on(pu.codpersona=u.codempleado)
			where lh.codlimpieza=? and h.codsucursal=? and lh.estado=1 limit 1",
			[(int)$codlimpieza, (int)$_SESSION["phuyu_codsucursal"]]
		)->row_array();
		if (empty($orden)) { show_404(); return; }
		$orden["numero_orden"] = str_pad((string)$orden["codlimpieza"], 6, "0", STR_PAD_LEFT);
		$orden["tipo_limpieza_texto"] = $this->texto_tipo($orden["tipo_limpieza"] ?? "normal");
		$orden["estado_orden_texto"] = $this->texto_estado($orden["estado_orden"] ?? 1);
		$orden["checklist_items"] = json_decode($orden["checklist"] ?? "[]", true) ?: [];
		$this->load->view("hotel/limpieza/ticket", ["orden" => $orden]);
	}
}
