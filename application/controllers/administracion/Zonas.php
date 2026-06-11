<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Zonas extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$this->load->view("administracion/zonas/index");
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
			$limit = 4; $offset = $this->request->pagina * $limit - $limit;

			$lista = $this->db->query("select zona.*, ubigeo.departamento as departamento,ubigeo.provincia as provincia,ubigeo.distrito as distrito from public.zonas as zona inner join public.ubigeo as ubigeo on(zona.codubigeo=ubigeo.codubigeo) where UPPER(zona.descripcion) like UPPER('%".$this->request->buscar."%')  and zona.estado=1 order by zona.codzona desc offset ".$offset." limit ".$limit)->result_array();

			$total = $this->db->query("select count(*) as total from public.zonas as zona inner join public.ubigeo as ubigeo on(zona.codubigeo=ubigeo.codubigeo) where UPPER(zona.descripcion) like UPPER('%".$this->request->buscar."%') and zona.estado=1")->result_array();

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

	public function nuevo(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$departamentos = $this->db->query("select distinct(ubidepartamento), departamento from public.ubigeo order by ubidepartamento")->result_array();
				$this->load->view("administracion/zonas/nuevo",compact("departamentos"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function nuevo_1(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$departamentos = $this->db->query("select distinct(ubidepartamento), departamento from public.ubigeo order by ubidepartamento")->result_array();
				$this->load->view("administracion/zonas/nuevo_1",compact("departamentos"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function guardar(){
		if ($this->input->is_ajax_request()) {
			$campos = ["codubigeo","descripcion"];
			$this->request = json_decode(file_get_contents('php://input'));
			$valores = [$this->request->codubigeo,$this->request->descripcion];

			if($this->request->codregistro=="") {
				$estado = $this->phuyu_model->phuyu_guardar("public.zonas", $campos, $valores);
			}else{
				$estado = $this->phuyu_model->phuyu_editar("public.zonas", $campos, $valores, "codzona", $this->request->codregistro);
			}
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function guardar_1(){
		if ($this->input->is_ajax_request()) {
			$campos = ["codubigeo","descripcion"];
			$this->request = json_decode(file_get_contents('php://input'));
			$valores = [$this->request->codubigeo1,$this->request->descripcion];

			$estado = $this->phuyu_model->phuyu_guardar("public.zonas", $campos, $valores,"true");

			$ubigeo=$this->db->query("select *from public.ubigeo where codubigeo=".$this->request->codubigeo1)->result_array();

			$data["codzona"] = $estado;
			$data["ubigeo"] = $ubigeo;
			$data["estado"] = 1;

			echo json_encode($data);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function ubigeo(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			$info = $this->db->query("select *from zonas where codzona=".$this->request->codregistro)->result_array();
			$ubigeo = $this->db->query("select * from public.ubigeo where codubigeo=".$info[0]["codubigeo"])->result_array();
			
			$data["ubigeo"] = $ubigeo;
			echo json_encode($data);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function editar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$info = $this->db->query("select codzona as codregistro,* from public.zonas where codzona=".$this->request->codregistro)->result_array();
			echo json_encode($info);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function eliminar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$estado = $this->phuyu_model->phuyu_eliminar("public.zonas", "codzona", $this->request->codregistro);
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}
}