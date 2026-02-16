<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Almacenes extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$this->load->view("administracion/almacenes/index");
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
			$limit = 8; $offset = $this->request->pagina * $limit - $limit;

			$lista = $this->db->query("select almacen.*, sucursal.descripcion as sucursal from almacen.almacenes as almacen inner join public.sucursales as sucursal on(almacen.codsucursal=sucursal.codsucursal) where (UPPER(almacen.descripcion) like UPPER('%".$this->request->buscar."%') or UPPER(sucursal.descripcion) like UPPER('%".$this->request->buscar."%')) and almacen.estado=1 order by almacen.codalmacen ASC offset ".$offset." limit ".$limit)->result_array();

			$total = $this->db->query("select count(*) as total from almacen.almacenes as almacen inner join public.sucursales as sucursal on(almacen.codsucursal=sucursal.codsucursal) where (UPPER(almacen.descripcion) like UPPER('%".$this->request->buscar."%') or UPPER(sucursal.descripcion) like UPPER('%".$this->request->buscar."%')) and almacen.estado=1")->result_array();

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
				$departamentos = $this->db->query("select distinct(ubidepartamento), departamento from public.ubigeo order by ubidepartamento")->result_array();
				$afectacionigv = $this->db->query("select *from afectacionigv where estado = 1")->result_array();
				$this->load->view("administracion/almacenes/nuevo",compact("sucursales","departamentos","afectacionigv"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function guardar(){
		if ($this->input->is_ajax_request()) {
			$campos = ["codsucursal","descripcion","direccion","telefonos","controlstock","codubigeo","codafectacionigv"];
			$this->request = json_decode(file_get_contents('php://input'));
			$valores = [$this->request->codsucursal,$this->request->descripcion,$this->request->direccion,$this->request->telefonos,$this->request->controlstock,$this->request->codubigeo,$this->request->codafectacionigv];

			$this->db->trans_begin();

			if($this->request->codregistro=="") {
				$estado = $this->phuyu_model->phuyu_guardar("almacen.almacenes", $campos, $valores,"true");
				$codalmacen = $estado;
				$lineas = $this->db->query("select *from almacen.lineasxsucursales where codsucursal = ".$this->request->codsucursal)->result_array();

				$item = 0; $lineasxsucursales = "(";
				foreach ($lineas as $key => $value) { 
					$item = $item + 1;
					if ($item==count($lineas)) {
						$lineasxsucursales .= " codlinea=".$value["codlinea"]." ) AND";
					}else{
						$lineasxsucursales .= " codlinea=".$value["codlinea"]." or ";
					}
				}

				$productos = $this->db->query("select *from almacen.productos where ".$lineasxsucursales." estado=1")->result_array();

				foreach ($productos as $key => $value) {
					$productosuni = $this->db->query("select codproducto,codunidad from almacen.productounidades where codproducto = ".$value["codproducto"]." AND estado=1")->result_array();
					foreach ($productosuni as $key => $val) {
						$campos = ["codalmacen","codproducto","codunidad","codsucursal","codafectacionigvcompra","codafectacionigvventa"];
						$valores =[$codalmacen,$value["codproducto"],$val["codunidad"],$this->request->codsucursal,$this->request->codafectacionigv,$this->request->codafectacionigv];
						$estado = $this->phuyu_model->phuyu_guardar("almacen.productoubicacion", $campos, $valores);
					}
				}

			}else{
				$estado = $this->phuyu_model->phuyu_editar("almacen.almacenes", $campos, $valores, "codalmacen", $this->request->codregistro);

				$estado = $this->phuyu_model->phuyu_editar("almacen.productoubicacion",["codafectacionigvcompra","codafectacionigvventa"],[$this->request->codafectacionigv,$this->request->codafectacionigv],"codalmacen",$this->request->codregistro);

				if($_SESSION["phuyu_codalmacen"] == $this->request->codregistro){
					$_SESSION["phuyu_stockalmacen"] = $this->request->controlstock; 
					$_SESSION["phuyu_afectacionigv"] = (int)$this->request->codafectacionigv;
				}
			}

			if ($this->db->trans_status() === FALSE){
			    $this->db->trans_rollback(); $estado = 0;
			}else{
				$this->db->trans_commit();
			}
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function editar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$info = $this->db->query("select codalmacen as codregistro,* from almacen.almacenes where codalmacen=".$this->request->codregistro)->result_array();
			echo json_encode($info);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function ubigeo(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			$info = $this->db->query("select *from almacen.almacenes where codalmacen=".$this->request->codregistro)->result_array();
			$ubigeo = $this->db->query("select * from public.ubigeo where codubigeo=".$info[0]["codubigeo"])->result_array();
			$data["ubigeo"] = $ubigeo;
			echo json_encode($data);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function eliminar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$estado = $this->phuyu_model->phuyu_eliminar("almacen.almacenes", "codalmacen", $this->request->codregistro);
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function phuyu_tipomovimiento($tipo){
		if ($this->input->is_ajax_request()) {
			$where = '';
			if($tipo==1){
				$where .= ' AND codmovimientotipo<>2';
			}else{
				$where .= ' AND codmovimientotipo<>20';
			}
			$movimientotipo = $this->db->query("select *from almacen.movimientotipos where tipo='".$tipo."' AND estado = 1 ".$where." order by codmovimientotipo")->result_array();
			$html = '<option value="0">TODOS</option>';
			foreach ($movimientotipo as $key => $value) {
				$html .= '<option value="'.$value["codmovimientotipo"].'">'.$value["descripcion"].'</option>';
			}
			echo $html;
		}
	}
}