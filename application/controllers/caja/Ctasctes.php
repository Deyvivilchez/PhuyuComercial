<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Ctasctes extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$this->load->view("caja/ctasctes/index");
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

			$lista = $this->db->query("select cta.*,bancos.descripcion as banco,personas.razonsocial as socio from caja.ctasctes as cta inner join caja.bancos as bancos on(cta.codbanco=bancos.codbanco) inner join public.personas as personas on(cta.codpersona=personas.codpersona) where (UPPER(cta.nroctacte) like UPPER('%".$this->request->buscar."%') or UPPER(personas.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(bancos.descripcion) like UPPER('%".$this->request->buscar."%') ) and cta.estado=1 order by cta.codctacte desc offset ".$offset." limit ".$limit)->result_array();

			$total = $this->db->query("select count(*) as total from caja.ctasctes as cta inner join caja.bancos as bancos on(cta.codbanco=bancos.codbanco) inner join public.personas as personas on(cta.codpersona=personas.codpersona) where (UPPER(cta.nroctacte) like UPPER('%".$this->request->buscar."%') or UPPER(personas.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(bancos.descripcion) like UPPER('%".$this->request->buscar."%') ) and cta.estado=1")->result_array();

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

	public function buscar(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$this->request = json_decode(file_get_contents('php://input'));
				$bancos = $this->db->query("select distinct(cta.codbanco),bancos.descripcion as banco from caja.ctasctes as cta inner join caja.bancos as bancos on(cta.codbanco=bancos.codbanco) where cta.codpersona=".$this->request->codregistro." AND cta.estado=1")->result_array();
				$this->load->view("caja/ctasctes/buscar",compact("bancos"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function buscarccte(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$limit = 5; $offset = $this->request->pagina * $limit - $limit;
			$bancos = '';
			if($this->request->bancos!=0){
				$bancos.=' AND cta.codbanco='.$this->request->bancos;
			}

			$lista = $this->db->query("select cta.*,bancos.descripcion as banco,bancos.abreviatura,personas.razonsocial as socio from caja.ctasctes as cta inner join caja.bancos as bancos on(cta.codbanco=bancos.codbanco) inner join public.personas as personas on(cta.codpersona=personas.codpersona) where (UPPER(cta.nroctacte) like UPPER('%".$this->request->buscar."%') or UPPER(bancos.descripcion) like UPPER('%".$this->request->buscar."%') ) ".$bancos." and cta.codpersona=".$this->request->codregistro." and cta.estado=1 order by cta.codctacte desc offset ".$offset." limit ".$limit)->result_array();

			$total = $this->db->query("select count(*) as total from caja.ctasctes as cta inner join caja.bancos as bancos on(cta.codbanco=bancos.codbanco) inner join public.personas as personas on(cta.codpersona=personas.codpersona) where (UPPER(cta.nroctacte) like UPPER('%".$this->request->buscar."%') or UPPER(bancos.descripcion) like UPPER('%".$this->request->buscar."%') ) ".$bancos." and cta.codpersona=".$this->request->codregistro." and cta.estado=1")->result_array();

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
				$bancos = $this->db->query("select *from caja.bancos where estado=1")->result_array();
				$monedas = $this->db->query("select *from caja.monedas where estado=1 order by codmoneda")->result_array();
				$this->load->view("caja/ctasctes/nuevo",compact("bancos","monedas"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function guardar(){
		if ($this->input->is_ajax_request()) {
			$campos = ["codpersona","codbanco","codmoneda","nroctacte","descripcion"];
			$this->request = json_decode(file_get_contents('php://input'));
			$valores = [$this->request->codpersona,$this->request->codbanco,$this->request->codmoneda,$this->request->nroctacte,$this->request->descripcion];

			if($this->request->codregistro=="") {
				$estado = $this->phuyu_model->phuyu_guardar("caja.ctasctes", $campos, $valores);
			}else{
				$estado = $this->phuyu_model->phuyu_editar("caja.ctasctes", $campos, $valores, "codctacte", $this->request->codregistro);
			}
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function editar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$info = $this->db->query("select codctacte as codregistro,* from caja.ctasctes where codctacte=".$this->request->codregistro)->result_array();
			echo json_encode($info);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function obtenersocio(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$socio =$this->db->query("select codpersona,razonsocial from public.personas where codpersona=".$this->request->codregistro)->result_array();
			echo json_encode($socio);
		}else{
			$this->load->view("inicio/404");
		}
	}

	function socio(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$info = $this->db->query("select *from caja.ctasctes where codctacte=".$this->request->codregistro)->result_array();
			$socio =$this->db->query("select codpersona,razonsocial from public.personas where codpersona=".$info[0]["codpersona"])->result_array();
			echo json_encode($socio);
		}else{
			$this->load->view("inicio/404");
		}
	}

	function eliminar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$estado = $this->phuyu_model->phuyu_eliminar("caja.ctasctes", "codctacte", $this->request->codregistro);
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}
}