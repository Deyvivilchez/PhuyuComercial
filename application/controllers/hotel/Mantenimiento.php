<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Mantenimiento extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model("phuyu_model");
		$this->load->model("Hotel_model");
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

	public function guardar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents("php://input"));
			$habitacion = $this->db->query(
				"select * from hotel.habitaciones where codhabitacion=? and codsucursal=? and estado=1 limit 1",
				[(int)$this->request->codhabitacion, (int)$_SESSION["phuyu_codsucursal"]]
			)->row_array();
			if (empty($habitacion)) {
				echo json_encode(["estado" => 0, "mensaje" => "HABITACION NO ENCONTRADA"]);
				return;
			}
			if ($this->Hotel_model->habitacion_tiene_estadia_activa((int)$this->request->codhabitacion)) {
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUEDE ENVIAR A MANTENIMIENTO: LA HABITACION TIENE ESTADIA ACTIVA"]);
				return;
			}
			$this->db->trans_begin();
			$estado = $this->phuyu_model->phuyu_guardar("hotel.mantenimiento_habitaciones", ["codhabitacion","codusuario","codresponsable","observacion","situacion"], [(int)$this->request->codhabitacion,(int)$_SESSION["phuyu_codusuario"],(int)$this->request->codresponsable,$this->request->observacion,1]);
			$estado = $this->phuyu_model->phuyu_editar("hotel.habitaciones", ["situacion"], [5], "codhabitacion", (int)$this->request->codhabitacion);
			if ($this->db->trans_status() === FALSE || $estado != 1) { $this->db->trans_rollback(); $estado = 0; } else { $this->db->trans_commit(); }
			echo json_encode(["estado" => $estado]);
		}
	}

	public function finalizar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents("php://input"));
			$codhabitacion = (int)$this->request->codhabitacion;
			$destino = (int)$this->request->situacion;
			if (!in_array($destino, [1,4])) {
				echo json_encode(["estado" => 0, "mensaje" => "ESTADO DESTINO INVALIDO"]);
				return;
			}
			$habitacion = $this->db->query(
				"select * from hotel.habitaciones where codhabitacion=? and codsucursal=? and estado=1 and situacion=5 limit 1",
				[$codhabitacion, (int)$_SESSION["phuyu_codsucursal"]]
			)->row_array();
			if (empty($habitacion)) {
				echo json_encode(["estado" => 0, "mensaje" => "LA HABITACION NO ESTA EN MANTENIMIENTO"]);
				return;
			}
			if ($this->Hotel_model->habitacion_tiene_estadia_activa($codhabitacion)) {
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUEDE FINALIZAR: LA HABITACION TIENE ESTADIA ACTIVA"]);
				return;
			}
			$mantenimiento = $this->db->query(
				"select *
				from hotel.mantenimiento_habitaciones
				where codhabitacion=? and situacion=1 and estado=1
				order by codmantenimiento desc limit 1",
				[$codhabitacion]
			)->row_array();
			if (empty($mantenimiento)) {
				echo json_encode(["estado" => 0, "mensaje" => "NO HAY MANTENIMIENTO ACTIVO"]);
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
				"fecha_fin" => date("Y-m-d"),
				"observacion" => $observacionFinal
			]);
			$estado = $this->phuyu_model->phuyu_editar("hotel.habitaciones", ["situacion"], [$destino], "codhabitacion", $codhabitacion);
			if ($this->db->trans_status() === FALSE || $estado != 1) { $this->db->trans_rollback(); $estado = 0; } else { $this->db->trans_commit(); }
			echo json_encode(["estado" => $estado]);
		}else{
			$this->load->view("phuyu/404");
		}
	}
}
