<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Vehiculos extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$this->load->view("administracion/vehiculos/index");
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function lista(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$limit = 10; $offset = $this->request->pagina * $limit - $limit;

			$lista = $this->db->query("select * from almacen.vehiculos where UPPER(descripcion) like UPPER('%".$this->request->buscar."%') and UPPER(nroplaca) like UPPER('%".$this->request->buscar."%') AND estado=1 order by codvehiculo desc offset ".$offset." limit ".$limit)->result_array();

			$total = $this->db->query("select count(*) as total from almacen.vehiculos where UPPER(descripcion) like UPPER('%".$this->request->buscar."%') and UPPER(nroplaca) like UPPER('%".$this->request->buscar."%') AND estado=1")->result_array();

			$paginas = floor($total[0]["total"] / $limit);
			if ( ($total[0]["total"] % $limit)!=0 ) {
				$paginas = $paginas + 1;
			}

			$paginacion = array();
			$paginacion["total"] = $total[0]["total"];
			$paginacion["actual"] = $this->request->pagina;
			$paginacion["ultima"] = $paginas;
			$paginacion["desde"] = $offset;
			$paginacion["hasta"] = $offset + $limit;

			echo json_encode(array("lista" => $lista,"paginacion" => $paginacion));
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function buscar(){
		if ($this->input->is_ajax_request()) {
           $vehiculos = $this->db->query("select v.*,v.codvehiculo as id from almacen.vehiculos v where UPPER(nroplaca) like UPPER('%".$_GET["search"]["value"]."%') AND estado=1 order by codvehiculo desc limit 10")->result_array();
           $data["data"] = $vehiculos;
           echo json_encode($data);
		}
	}

	function infovehiculo($codvehiculo){
		if ($this->input->is_ajax_request()) {
			$info = $this->db->query("select constancia from almacen.vehiculos where codvehiculo=".$codvehiculo)->result_array();
			echo json_encode($info);
		}
	}

	public function nuevo(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$this->load->view("administracion/vehiculos/nuevo");
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function guardar(){
		if ($this->input->is_ajax_request()) {
			$campos = ["descripcion","nroplaca","constancia","estado"];
			$this->request = json_decode(file_get_contents('php://input'));
			$valores = [$this->request->descripcion,$this->request->nroplaca, $this->request->constancia,1];

			if($this->request->codregistro=="") {
				$estado = $this->phuyu_model->phuyu_guardar("almacen.vehiculos", $campos, $valores);
			}else{
				$estado = $this->phuyu_model->phuyu_editar("almacen.vehiculos", $campos, $valores, "codvehiculo", $this->request->codregistro);
			}
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function editar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$info = $this->db->query("select codvehiculo as codregistro,* from almacen.vehiculos where codvehiculo=".$this->request->codregistro)->result_array();
			echo json_encode($info);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function eliminar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$estado = $this->phuyu_model->phuyu_eliminar("almacen.vehiculos", "codvehiculo", $this->request->codregistro);
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}
}