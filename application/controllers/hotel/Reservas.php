<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Reservas extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model("phuyu_model");
		$this->load->model("Hotel_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$ambientes = $this->Hotel_model->ambientes();
				$habitaciones = $this->Hotel_model->disponibilidad_habitaciones_rango(date("Y-m-d"), date("Y-m-d", strtotime("+1 day")));
				$this->load->view("hotel/reservas/index", compact("ambientes", "habitaciones"));
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
					string_agg(h.numero, ', ' order by h.numero) as habitaciones
				from hotel.reservas r
				inner join public.personas p on(p.codpersona=r.codpersona)
				left join hotel.reserva_habitaciones rh on(rh.codreserva=r.codreserva and rh.estado=1)
				left join hotel.habitaciones h on(h.codhabitacion=rh.codhabitacion)
				where r.codsucursal=? and r.estado=1
				  and (upper(p.razonsocial) like upper(?) or upper(p.documento) like upper(?) or upper(coalesce(r.cliente,'')) like upper(?))
				group by r.codreserva, p.documento, p.razonsocial
				order by r.fechallegada desc, r.codreserva desc
				limit 80",
				[(int)$_SESSION["phuyu_codsucursal"], "%".$buscar."%", "%".$buscar."%", "%".$buscar."%"]
			)->result_array();
			echo json_encode($lista);
		}else{
			$this->load->view("phuyu/404");
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
			$validacion = $this->Hotel_model->habitacion_disponible_rango(
				(int)$this->request->codhabitacion,
				$this->request->fechallegada,
				$this->request->fechasalida,
				(int)$this->request->codreserva
			);
			if ((int)$validacion["estado"] != 1) {
				echo json_encode($validacion);
				return;
			}

			$persona = $this->db->query("select * from public.personas where codpersona=? and estado=1", [(int)$this->request->codpersona])->row_array();
			if (empty($persona)) {
				echo json_encode(["estado" => 0, "mensaje" => "CLIENTE NO ENCONTRADO"]);
				return;
			}

			$this->db->trans_begin();
			$campos = ["codsucursal","codpersona","codusuario","fechallegada","fechasalida","cliente","direccion","observacion","situacion"];
			$valores = [
				(int)$_SESSION["phuyu_codsucursal"], (int)$persona["codpersona"], (int)$_SESSION["phuyu_codusuario"],
				$this->request->fechallegada, $this->request->fechasalida, $persona["razonsocial"], $persona["direccion"],
				$this->request->observacion, (int)$this->request->situacion
			];

			if ((int)$this->request->codreserva == 0) {
				$codreserva = $this->phuyu_model->phuyu_guardar("hotel.reservas", $campos, $valores, "true");
			}else{
				$codreserva = (int)$this->request->codreserva;
				array_shift($campos); array_shift($valores);
				$this->phuyu_model->phuyu_editar("hotel.reservas", $campos, $valores, "codreserva", $codreserva);
				$this->db->where("codreserva", $codreserva)->update("hotel.reserva_habitaciones", ["estado" => 0]);
			}

			$habitacion = $this->db->query("select preciobase from hotel.habitaciones where codhabitacion=?", [(int)$this->request->codhabitacion])->row_array();
			$estado = $this->db->query(
				"insert into hotel.reserva_habitaciones (codreserva, codhabitacion, precio, estado)
				values (?, ?, ?, 1)
				on conflict (codreserva, codhabitacion)
				do update set precio=excluded.precio, estado=1",
				[(int)$codreserva, (int)$this->request->codhabitacion, (double)$habitacion["preciobase"]]
			);
			if ((int)$this->request->situacion == 2) {
				$this->phuyu_model->phuyu_editar("hotel.habitaciones", ["situacion"], [2], "codhabitacion", (int)$this->request->codhabitacion);
			}

			if ($this->db->trans_status() === FALSE || $estado != 1) {
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

	public function cancelar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents("php://input"));
			$estado = $this->phuyu_model->phuyu_editar("hotel.reservas", ["situacion"], [3], "codreserva", (int)$this->request->codreserva);
			echo json_encode(["estado" => $estado]);
		}else{
			$this->load->view("phuyu/404");
		}
	}
}
