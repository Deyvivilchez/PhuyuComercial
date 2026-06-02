<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Mesas extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$this->load->view("restaurante/mesas/index");
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
			$limit = 20; $offset = $this->request->pagina * $limit - $limit;

			$lista = $this->db->query("select m.*, a.descripcion as ambiente 
			from restaurante.mesas as m 
			inner join restaurante.ambientes as a on(m.codambiente=a.codambiente) 
			where UPPER(m.descripcion) like UPPER('%".$this->request->buscar."%') and m.estado=1 order by m.codmesa asc offset ".$offset." limit ".$limit)->result_array();

			$total = $this->db->query("select count(*) as total from restaurante.mesas where UPPER(descripcion) like UPPER('%".$this->request->buscar."%') and estado=1")->result_array();

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

	public function mesas_ambiente($codambiente){
		if ($this->input->is_ajax_request()) {
			$mesas = $this->db->query("select m.*,
				(select count(*) from restaurante.mesaspedido mp where mp.codmesa=m.codmesa and mp.estado=1) as pedidos_activos
				from restaurante.mesas as m
				where m.codambiente=".$codambiente." and m.estado=1
				order by m.codmesa")->result_array();
			foreach ($mesas as $key => $value) {
				if((int)$value["pedidos_activos"] > 0){
					$color = "phuyu-ocupada"; $texto = "OCUPADA";
				}elseif ($value["situacion"]==3) {
					$color = "phuyu-reservada"; $texto = "RESERVADA";
				}elseif ($value["situacion"]==4) {
					$color = "phuyu-avancecta"; $texto = "AVANCE CTA";
				}else{
					$color = "phuyu-libre"; $texto = "LIBRE";
				}
				$mesas[$key]["color"] = $color;
				$mesas[$key]["texto"] = $texto;
			}
			echo json_encode($mesas);
		}
	}

	public function nuevo(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$ambientes = $this->db->query("select * from restaurante.ambientes where codsucursal=".$_SESSION["phuyu_codsucursal"]." and estado=1")->result_array();
				$this->load->view("restaurante/mesas/nuevo",compact("ambientes"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function guardar(){
		if ($this->input->is_ajax_request()) {
			$campos = ["codambiente","descripcion","nromesa","capacidad"];
			$this->request = json_decode(file_get_contents('php://input'));
			$valores = [$this->request->codambiente,"MESA NRO ".$this->request->nromesa,$this->request->nromesa,(int)$this->request->capacidad];

			if($this->request->codregistro=="") {
				$estado = $this->phuyu_model->phuyu_guardar("restaurante.mesas", $campos, $valores);
			}else{
				$estado = $this->phuyu_model->phuyu_editar("restaurante.mesas", $campos, $valores, "codmesa", $this->request->codregistro);
			}
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function editar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$info = $this->db->query("select codmesa as codregistro,* from restaurante.mesas where codmesa=".$this->request->codregistro)->result_array();
			echo json_encode($info);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function eliminar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$estado = $this->phuyu_model->phuyu_eliminar("restaurante.mesas", "codmesa", $this->request->codregistro);
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}
}
