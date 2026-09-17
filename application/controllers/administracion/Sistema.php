<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Sistema extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$this->load->view("administracion/sistema/index");
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

			$lista = $this->db->query("select * from public.sucursales where UPPER(descripcion) like UPPER('%".$this->request->buscar."%') and estado=1 order by codsucursal desc offset ".$offset." limit ".$limit)->result_array();

			$total = $this->db->query("select count(*) as total from public.sucursales where UPPER(descripcion) like UPPER('%".$this->request->buscar."%') and estado=1")->result_array();

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
				$comprobantes = $this->db->query("select distinct(ct.codcomprobantetipo) as codigo, ct.* from caja.comprobantetipos as ct inner join caja.comprobantes as c on(ct.codcomprobantetipo=c.codcomprobantetipo) where c.codcomprobantetipo>=5 and c.estado=1")->result_array();
				$departamentos = $this->db->query("select distinct(ubidepartamento), departamento from public.ubigeo order by ubidepartamento")->result_array();
				$lineas = $this->db->query("select * from almacen.lineas where estado=1")->result_array();
				$rubros = $this->db->query("select *from public.rubros where estado = 1 ORDER BY codrubro")->result_array();
				$this->load->view("administracion/sucursales/nuevo",compact("comprobantes","departamentos","lineas","rubros"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function guardar(){
		if ($this->input->is_ajax_request()) {
			//echo 1;exit;
			$campos = ["codempresa","descripcion","direccion","telefonos","principal","codcomprobantetipo","seriecomprobante","codubigeo","coddespachotipo","codrubro","ventaconpedido","ventaconproforma"];
			$this->request = json_decode(file_get_contents('php://input'));
			$valores = [$_SESSION["phuyu_codempresa"],$this->request->descripcion,$this->request->direccion,$this->request->telefonos,$this->request->principal,$this->request->codcomprobantetipo,strtoupper($this->request->seriecomprobante),$this->request->codubigeo, $this->request->coddespachotipo,$this->request->codrubro,$this->request->ventaconpedido,$this->request->ventaconproforma];

			if($this->request->codregistro=="") {
				$estado = $this->phuyu_model->phuyu_guardar("public.sucursales", $campos, $valores, "true");
				$this->request->codregistro = $estado;
			}else{
				$estado = $this->phuyu_model->phuyu_editar("public.sucursales", $campos, $valores, "codsucursal", $this->request->codregistro);
			}

			if($this->request->codregistro==$_SESSION["phuyu_codsucursal"]){
				$_SESSION["phuyu_rubro"] = $this->request->codrubro;
				$_SESSION["phuyu_ventaconpedido"] = $this->request->ventaconpedido;
				$_SESSION["phuyu_ventaconproforma"] = $this->request->ventaconproforma;
			}

			$this->db->where("codsucursal", $this->request->codregistro);
			$estado = $this->db->delete("almacen.lineasxsucursales");
			if (isset($this->request->lineas)) {
				foreach ($this->request->lineas as $key => $value) {
					$data = array(
						"codlinea" => $value, 
						"codsucursal" => $this->request->codregistro
					);
					$estado = $this->db->insert("almacen.lineasxsucursales", $data);
				}
			}
			$_SESSION["phuyu_tipodespacho"] = $this->request->coddespachotipo;
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function ubigeo(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			$info = $this->db->query("select *from sucursales where codsucursal=".$this->request->codregistro)->result_array();
			$ubigeo = $this->db->query("select * from public.ubigeo where codubigeo=".$info[0]["codubigeo"])->result_array();
			
			$data["ubigeo"] = $ubigeo;
			echo json_encode($data);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function lineasxsucursales($codsucursal){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$lineas = $this->db->query("select * from almacen.lineasxsucursales where codsucursal=".$codsucursal)->result_array(); $data = array();
			foreach ($lineas as $key => $value) {
				$data[] = $value["codlinea"];
			}
			echo json_encode($data);
		}
	}

	function sucursalesxlineas($codlinea){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$sucursales = $this->db->query("select * from almacen.lineasxsucursales where codlinea=".$codlinea)->result_array(); 
			$data = array();
			foreach ($sucursales as $key => $value) {
				$data[] = $value["codsucursal"];
			}
			echo json_encode($data);
		}
	}

	function editar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$info = $this->db->query("select codsucursal as codregistro,* from public.sucursales where codsucursal=".$this->request->codregistro)->result_array();
			echo json_encode($info);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function eliminar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$estado = $this->phuyu_model->phuyu_eliminar("public.sucursales", "codsucursal", $this->request->codregistro);
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}
}