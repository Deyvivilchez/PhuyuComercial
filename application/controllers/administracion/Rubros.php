<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Rubros extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$this->load->view("administracion/rubros/index");
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

			$lista = $this->db->query("select * from public.rubros where UPPER(descripcion) like UPPER('%".$this->request->buscar."%') and estado=1 order by codrubro desc offset ".$offset." limit ".$limit)->result_array();

			$total = $this->db->query("select count(*) as total from public.rubros where UPPER(descripcion) like UPPER('%".$this->request->buscar."%') and estado=1")->result_array();

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
				$sucursales = $this->db->query("select * from public.sucursales where estado=1")->result_array();
				$this->load->view("administracion/rubros/nuevo",compact("sucursales"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function guardar(){
		if ($this->input->is_ajax_request()) {
			$campos = ["descripcion","activo"];
			$this->request = json_decode(file_get_contents('php://input'));
			$valores = [$this->request->descripcion,$this->request->activo];

			if($this->request->codregistro=="") {
				$estado = $this->phuyu_model->phuyu_guardar("public.rubros", $campos, $valores,"true");
				$this->request->codregistro = $estado;
			}else{
				$estado = $this->phuyu_model->phuyu_editar("public.rubros", $campos, $valores, "codrubro", $this->request->codregistro);
			}

			if($this->request->activo==1){

				$data = array("activo" => 0);
				$estado = $this->db->update("public.rubros",$data);

				$data = array("activo" => 1);
				$this->db->where("codrubro",(int)$this->request->codregistro);
				$estado = $this->db->update("public.rubros",$data);

				$campos = ["rubro"];
				$valores = [(int)$this->request->codregistro];
				$estado = $this->phuyu_model->phuyu_editar("public.empresas", $campos, $valores,"codempresa",$_SESSION["phuyu_codempresa"]);
			}

			$campos = ["codrubro"];
			$valores = [0];
			$estado = $this->phuyu_model->phuyu_editar("public.sucursales", $campos, $valores,"codrubro",(int)$this->request->codregistro);

			if (isset($this->request->sucursales)) {
				foreach ($this->request->sucursales as $key => $value) {
					$campos = ["codrubro"];
					$valores = [(int)$this->request->codregistro];
					$estado = $this->phuyu_model->phuyu_editar("public.sucursales", $campos, $valores,"codsucursal",$value["codsucursal"]);
				}
			}

			echo 1;
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function editar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$info = $this->db->query("select codrubro as codregistro,* from public.rubros where codrubro=".$this->request->codregistro)->result_array();
			echo json_encode($info);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function sucursalrubro($codrubro){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$info = $this->db->query("select * from public.sucursales where codrubro=".$codrubro)->result_array();
			$data = array();
			foreach ($info as $key => $value) {
				$data[] = $value["codsucursal"];
			}
			echo json_encode($data);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function eliminar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$estado = $this->phuyu_model->phuyu_eliminar("public.rubros", "codrubro", $this->request->codregistro);
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}
}