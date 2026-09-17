<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Comprobantetipos extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$this->load->view("administracion/comprobantetipos/index");
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

			$lista = $this->db->query("select * from caja.comprobantetipos where UPPER(descripcion) like UPPER('%".$this->request->buscar."%') and estado=1 order by codcomprobantetipo desc offset ".$offset." limit ".$limit)->result_array();

			$total = $this->db->query("select count(*) as total from caja.comprobantetipos where UPPER(descripcion) like UPPER('%".$this->request->buscar."%') and estado=1")->result_array();

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
				$this->load->view("administracion/comprobantetipos/nuevo");
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function guardar(){
		if ($this->input->is_ajax_request()) {

			$this->request = json_decode(file_get_contents('php://input'));
			
			$campos = ["descripcion","abreviatura","oficial","ingresoalmacen","egresoalmacen","compra","venta"];

			$this->request->ingreso = (isset($this->request->ingreso)) ? $this->request->ingreso : 0;
			$this->request->egreso = (isset($this->request->egreso)) ? $this->request->egreso : 0;
			$this->request->compra = (isset($this->request->compra)) ? $this->request->compra : 0;
			$this->request->venta = (isset($this->request->venta)) ? $this->request->venta : 0;

			$valores = [$this->request->descripcion,$this->request->abreviatura,$this->request->oficial,$this->request->ingreso,$this->request->egreso,$this->request->compra,$this->request->venta];

			if($this->request->codregistro=="") {
				$estado = $this->phuyu_model->phuyu_guardar("caja.comprobantetipos", $campos, $valores);
			}else{
				$estado = $this->phuyu_model->phuyu_editar("caja.comprobantetipos", $campos, $valores, "codcomprobantetipo", $this->request->codregistro);
			}
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function editar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$info = $this->db->query("select codcomprobantetipo as codregistro,* from caja.comprobantetipos where codcomprobantetipo=".$this->request->codregistro)->result_array();
			echo json_encode($info);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function eliminar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$estado = $this->phuyu_model->phuyu_eliminar("caja.comprobantetipos", "codcomprobantetipo", $this->request->codregistro);
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}
}