<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Ambientes extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model("phuyu_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$this->load->view("hotel/ambientes/index");
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
			$limit = 10;
			$offset = ((int)$this->request->pagina * $limit) - $limit;

			$lista = $this->db->query(
				"select a.*, s.descripcion as sucursal
				from hotel.ambientes a
				inner join public.sucursales s on(s.codsucursal=a.codsucursal)
				where a.estado=1
				  and a.codsucursal=?
				  and upper(a.descripcion) like upper(?)
				order by a.descripcion offset ? limit ?",
				[(int)$_SESSION["phuyu_codsucursal"], "%".$buscar."%", $offset, $limit]
			)->result_array();

			$total = $this->db->query(
				"select count(*) as total
				from hotel.ambientes
				where estado=1 and codsucursal=? and upper(descripcion) like upper(?)",
				[(int)$_SESSION["phuyu_codsucursal"], "%".$buscar."%"]
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
				$this->load->view("hotel/ambientes/nuevo");
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
			$campos = ["codsucursal","descripcion","aforo"];
			$valores = [(int)$_SESSION["phuyu_codsucursal"], $this->request->descripcion, (int)$this->request->aforo];

			if ($this->request->codregistro == "") {
				$estado = $this->phuyu_model->phuyu_guardar("hotel.ambientes", $campos, $valores);
			}else{
				array_shift($campos); array_shift($valores);
				$estado = $this->phuyu_model->phuyu_editar("hotel.ambientes", $campos, $valores, "codambiente", (int)$this->request->codregistro);
			}
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function editar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents("php://input"));
			$info = $this->db->query("select codambiente as codregistro,* from hotel.ambientes where codambiente=?", [(int)$this->request->codregistro])->result_array();
			echo json_encode($info);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function eliminar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents("php://input"));
			$estado = $this->phuyu_model->phuyu_eliminar("hotel.ambientes", "codambiente", (int)$this->request->codregistro);
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}
}
