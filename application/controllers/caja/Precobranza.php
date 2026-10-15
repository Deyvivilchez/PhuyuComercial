<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Precobranza extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$vendedores = $this->db->query("select persona.codpersona,persona.razonsocial from public.personas as persona inner join public.empleados as empleado on(persona.codpersona=empleado.codpersona) where empleado.estado=1 ")->result_array();
				$this->load->view("caja/precobranza/index",compact("vendedores"));
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
			$filtro = "";
			if (!empty($this->request->filtro->codempleado)) {
				$filtro .= " AND pr.codempleado = ".(int)$this->request->filtro->codempleado;
			}
			$desde = $this->db->escape($this->request->filtro->desde);
			$hasta = $this->db->escape($this->request->filtro->hasta);

			$lista = $this->db->query("SELECT pr.codmovimiento, codcontroldiario, codcaja, codconcepto, codpersona, 
				   codusuario, usuario, tipomovimiento, codcomprobantetipo, seriecomprobante, 
				   nrocomprobante, codkardex, fechamovimiento, round(importe,2) as importe, referencia, 
				   condicionpago, estado, transferido, codempleado, vendedor, coddocumentotipo, 
				   descripciondt, documento, razonsocial, direccion, codlote, codmoneda, 
				   tipocambio, importemoneda, cobrado, cc.comprobantereferencia
				FROM caja.v_precobranzas pr
				JOIN caja.v_comprobantexcredito cc ON pr.codmovimiento = cc.codmovimiento 
				WHERE pr.codcaja = ".$_SESSION["phuyu_codcaja"]." AND pr.fechamovimiento >= ".$desde." and pr.fechamovimiento <= ".$hasta.$filtro."
				order by pr.codempleado, pr.fechamovimiento ASC offset ".$offset." limit ".$limit)->result_array();
			$cobranza = $this->db->query("select round(COALESCE(sum(pr.importe),0),2) as total FROM caja.v_precobranzas pr
				JOIN caja.v_comprobantexcredito cc ON pr.codmovimiento = cc.codmovimiento 
				WHERE pr.codcaja = ".$_SESSION["phuyu_codcaja"]." AND pr.fechamovimiento >= ".$desde." and pr.fechamovimiento <= ".$hasta.$filtro)->result_array();
			$cobranzat = number_format($cobranza[0]["total"],2,".","");
			$total = $this->db->query("select count(*) as total FROM caja.v_precobranzas pr
				JOIN caja.v_comprobantexcredito cc ON pr.codmovimiento = cc.codmovimiento 
				WHERE pr.codcaja = ".$_SESSION["phuyu_codcaja"]." AND pr.fechamovimiento >= ".$desde." and pr.fechamovimiento <= ".$hasta.$filtro)->result_array();

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

			echo json_encode(array("lista" => $lista,"paginacion" => $paginacion,"total"=>$cobranzat));
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function guardarcobranza(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$this->db->trans_begin();
			$estado = 1;

			if (isset($this->request->cobrado)) {
				foreach ($this->request->cobrado as $key => $value) {
					$data = array(
						"cobrado" => 1
					);

					$this->db->where("codmovimiento", (int)$value);
					$estado = $this->db->update("caja.movimientos", $data);
					
				}
			}

			if ($this->db->trans_status() === FALSE){
			    $this->db->trans_rollback(); $estado = 0;
			}else{
				if ($estado!=1) {
					$this->db->trans_rollback(); $estado = 0;
				}
				$this->db->trans_commit();
			}
			echo $estado;
		}
	}
}
