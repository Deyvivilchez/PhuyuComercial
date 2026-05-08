<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Habitaciones extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model("phuyu_model");
		$this->load->model("Hotel_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$this->load->view("hotel/habitaciones/index");
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
			$limit = 20; $offset = ((int)$this->request->pagina * $limit) - $limit;

			$lista = $this->db->query(
				"select h.*, ht.descripcion as tipo, coalesce(a.descripcion, h.piso) as ambiente
				from hotel.habitaciones h
				inner join hotel.habitacion_tipos ht on(ht.codhabitaciontipo=h.codhabitaciontipo)
				left join hotel.ambientes a on(a.codambiente=h.codambiente)
				where h.codsucursal=? and h.estado=1
					and (upper(h.numero) like upper(?) or upper(ht.descripcion) like upper(?) or upper(coalesce(a.descripcion,'')) like upper(?))
				order by coalesce(a.descripcion, h.piso), h.numero offset ? limit ?",
				[(int)$_SESSION["phuyu_codsucursal"], "%".$buscar."%", "%".$buscar."%", "%".$buscar."%", $offset, $limit]
			)->result_array();

			foreach ($lista as $key => $value) {
				$lista[$key]["situacion_texto"] = $this->Hotel_model->habitacion_situacion_texto((int)$value["situacion"]);
				$lista[$key]["caracteristicas"] = $this->Hotel_model->caracteristicas_habitacion((int)$value["codhabitacion"]);
			}

			$total = $this->db->query(
				"select count(*) as total
				from hotel.habitaciones h
				inner join hotel.habitacion_tipos ht on(ht.codhabitaciontipo=h.codhabitaciontipo)
				left join hotel.ambientes a on(a.codambiente=h.codambiente)
				where h.codsucursal=? and h.estado=1
					and (upper(h.numero) like upper(?) or upper(ht.descripcion) like upper(?) or upper(coalesce(a.descripcion,'')) like upper(?))",
				[(int)$_SESSION["phuyu_codsucursal"], "%".$buscar."%", "%".$buscar."%", "%".$buscar."%"]
			)->row_array();

			$paginas = floor($total["total"] / $limit);
			if (($total["total"] % $limit) != 0) { $paginas++; }
			echo json_encode([
				"lista" => $lista,
				"paginacion" => [
					"total" => (int)$total["total"],
					"actual" => (int)$this->request->pagina,
					"ultima" => $paginas,
					"desde" => $offset,
					"hasta" => $offset + $limit
				]
			]);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function nuevo(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$tipos = $this->db->query("select * from hotel.habitacion_tipos where estado=1 order by descripcion")->result_array();
				$ambientes = $this->Hotel_model->ambientes();
				$caracteristicas = $this->Hotel_model->caracteristicas();
				$this->load->view("hotel/habitaciones/nuevo", compact("tipos", "ambientes", "caracteristicas"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function guardar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents("php://input"));
			$caracteristicas = isset($this->request->caracteristicas) && is_array($this->request->caracteristicas) ? $this->request->caracteristicas : [];
			$campos = ["codsucursal","codambiente","codhabitaciontipo","numero","piso","capacidad","preciobase","situacion"];
			$valores = [
				(int)$_SESSION["phuyu_codsucursal"],
				(int)$this->request->codambiente,
				(int)$this->request->codhabitaciontipo,
				$this->request->numero,
				isset($this->request->piso) ? $this->request->piso : "",
				(int)$this->request->capacidad,
				(double)$this->request->preciobase,
				(int)$this->request->situacion
			];

			$this->db->trans_begin();
			if ($this->request->codregistro == "") {
				$codhabitacion = $this->phuyu_model->phuyu_guardar("hotel.habitaciones", $campos, $valores, "true");
				$estado = (int)$codhabitacion > 0;
			}else{
				array_shift($campos); array_shift($valores);
				$codhabitacion = (int)$this->request->codregistro;
				$estado = $this->phuyu_model->phuyu_editar("hotel.habitaciones", $campos, $valores, "codhabitacion", (int)$this->request->codregistro);
			}
			if ($estado) {
				$this->Hotel_model->guardar_habitacion_caracteristicas((int)$codhabitacion, $caracteristicas);
			}

			if ($this->db->trans_status() === FALSE || !$estado) {
				$this->db->trans_rollback();
				echo 0;
			}else{
				$this->db->trans_commit();
				echo 1;
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function editar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents("php://input"));
			$info = $this->db->query("select codhabitacion as codregistro,* from hotel.habitaciones where codhabitacion=?", [(int)$this->request->codregistro])->result_array();
			if (!empty($info)) {
				$seleccionadas = $this->Hotel_model->caracteristicas_habitacion((int)$this->request->codregistro);
				$info[0]["caracteristicas"] = array_map("intval", array_column($seleccionadas, "codcaracteristica"));
			}
			echo json_encode($info);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function eliminar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents("php://input"));
			$estado = $this->phuyu_model->phuyu_eliminar("hotel.habitaciones", "codhabitacion", (int)$this->request->codregistro);
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}
}
