<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Arqueos extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$this->load->view("caja/arqueos/index");
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
			$desde = $this->request->filtro->desde;
			$hasta = $this->request->filtro->hasta;
			$codcaja = (int) $_SESSION["phuyu_codcaja"];

			$lista = $this->db->query(
				"select cd.*, round(cd.saldoinicialcaja + cd.saldofinalcaja,2) as cierre,
				to_char(cd.horaapertura, 'HH24:MI') as horaapertura_texto,
				to_char(cd.horacierre, 'HH24:MI') as horacierre_texto
				from caja.controldiario as cd
				where cd.fechaapertura>=? and cd.fechaapertura<=? and cd.codcaja=?
				order by cd.fechaapertura desc, cd.horaapertura desc, cd.codcontroldiario desc
				offset ? limit ?",
				[$desde, $hasta, $codcaja, $offset, $limit]
			)->result_array();
			$total = $this->db->query(
				"select count(*) as total from caja.controldiario where fechaapertura>=? and fechaapertura<=? and codcaja=?",
				[$desde, $hasta, $codcaja]
			)->result_array();

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
}
