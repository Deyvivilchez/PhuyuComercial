<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Limpieza extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model("phuyu_model");
		$this->load->model("Hotel_model");
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
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUEDE CAMBIAR LIMPIEZA/DISPONIBLE: LA HABITACION TIENE ESTADIA ACTIVA"]);
				return;
			}
			if ((int)$this->request->situacion == 1 && (int)$habitacion["situacion"] != 4) {
				echo json_encode(["estado" => 0, "mensaje" => "SOLO HABITACIONES EN LIMPIEZA PUEDEN MARCARSE DISPONIBLES"]);
				return;
			}
			$this->db->trans_begin();
			$estado = $this->phuyu_model->phuyu_guardar("hotel.limpieza_habitaciones", ["codhabitacion","codusuario","codresponsable","observacion"], [(int)$this->request->codhabitacion,(int)$_SESSION["phuyu_codusuario"],(int)$this->request->codresponsable,$this->request->observacion]);
			$estado = $this->phuyu_model->phuyu_editar("hotel.habitaciones", ["situacion"], [(int)$this->request->situacion], "codhabitacion", (int)$this->request->codhabitacion);
			if ($this->db->trans_status() === FALSE || $estado != 1) { $this->db->trans_rollback(); $estado = 0; } else { $this->db->trans_commit(); }
			echo json_encode(["estado" => $estado]);
		}
	}
}
