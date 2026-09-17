<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Lineas extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$this->load->view("almacen/lineas/index");
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

			$lista = $this->db->query("select * from almacen.lineas where UPPER(descripcion) like UPPER('%".$this->request->buscar."%') and estado=1 order by codlinea desc offset ".$offset." limit ".$limit)->result_array();

			$total = $this->db->query("select count(*) as total from almacen.lineas where UPPER(descripcion) like UPPER('%".$this->request->buscar."%') and estado=1")->result_array();

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
				$this->load->view("almacen/lineas/nuevo",compact("sucursales"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function guardar(){
		if ($this->input->is_ajax_request()) {
			$campos = ["descripcion","color","background"];
			$this->request = json_decode(file_get_contents('php://input'));
			$valores = [$this->request->descripcion,$this->request->color,$this->request->background];

			if($this->request->codregistro=="") {
				$estado = $this->phuyu_model->phuyu_guardar("almacen.lineas", $campos, $valores, "true");
				$this->request->codregistro = $estado;
			}else{
				$estado = $this->phuyu_model->phuyu_editar("almacen.lineas", $campos, $valores, "codlinea", $this->request->codregistro);
			}

			$this->db->where("codlinea", $this->request->codregistro);
			$estado = $this->db->delete("almacen.lineasxsucursales");
			if (isset($this->request->sucursales)) {
				foreach ($this->request->sucursales as $key => $value) {
					$data = array(
						"codlinea" => $this->request->codregistro, 
						"codsucursal" => $value
					);
					$estado = $this->db->insert("almacen.lineasxsucursales", $data);

					$almacenes = $this->db->query("select codalmacen,codsucursal from almacen.almacenes where codsucursal=".$value." AND estado=1")->result_array();
					foreach ($almacenes as $key => $valu) {
						$productos = $this->db->query("select *from almacen.productos where codlinea=".$this->request->codregistro." AND estado=1")->result_array();

						foreach ($productos as $key => $val) {
							$data = array(
								"estado" => 0
							);

							$this->db->where("codalmacen", $valu["codalmacen"]);
							$this->db->where("codproducto", $val["codproducto"]);
							$estado = $this->db->update("almacen.productoubicacion", $data);

							$productosuni = $this->db->query("select codproducto,codunidad from almacen.productounidades where codproducto = ".$val["codproducto"]." AND estado=1")->result_array();
							foreach ($productosuni as $key => $va) {
								$productosubi = $this->db->query("select *from almacen.productoubicacion where codproducto = ".$val["codproducto"]." AND codunidad=".$va["codunidad"]." AND codalmacen=".$valu["codalmacen"])->result_array();

								if (count($productosubi)==0) {
									$campos = ["codalmacen","codproducto","codunidad","codsucursal"];
									$valores =[$valu["codalmacen"],$val["codproducto"],$va["codunidad"],$value];
									$estado = $this->phuyu_model->phuyu_guardar("almacen.productoubicacion", $campos, $valores);
								}else{
									$data = array(
										"estado" => 1
									);

									$this->db->where("codalmacen", $valu["codalmacen"]);
									$this->db->where("codproducto", $val["codproducto"]);
									$this->db->where("codunidad", $va["codunidad"]);
									$estado = $this->db->update("almacen.productoubicacion", $data);
								}
								
							}
						}
					}
				}
			}

			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function editar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$info = $this->db->query("select codlinea as codregistro,* from almacen.lineas where codlinea=".$this->request->codregistro)->result_array();
			echo json_encode($info);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function eliminar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$estado = $this->phuyu_model->phuyu_eliminar("almacen.lineas", "codlinea", $this->request->codregistro);
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}
}