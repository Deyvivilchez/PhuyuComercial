<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Extenciones extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
	}

	public function lista($carpeta,$tabla){
		if ($this->input->is_ajax_request()) {
			$lista = $this->db->query("select *from ".$carpeta.".".$tabla." where estado=1 order by descripcion asc")->result_array();
			echo json_encode($lista);
		}
	}

	public function nuevo($carpeta,$tabla){
		if ($this->input->is_ajax_request()) {
			$sucursales = $this->db->query("select * from public.sucursales where estado=1")->result_array();
			$this->load->view($carpeta."/".$tabla."/nuevo_1",compact("sucursales"));
		}
	}

	function guardar($carpeta,$tabla){
		if ($this->input->is_ajax_request()) {
			$campos = ["descripcion","estado"];
			$this->request = json_decode(file_get_contents('php://input'));
			$valores = [$this->request->descripcion,1];

			$existe = $this->db->query("select *from ".$carpeta.".".$tabla." where descripcion='".$this->request->descripcion."'")->result_array();
			if(count($existe)==0) {
				$estado = $this->phuyu_model->phuyu_guardar($carpeta.".".$tabla, $campos, $valores, "true");
				$codlinea = $estado;
				if($tabla=="lineas"){
					$this->db->where("codlinea", $codlinea);
					$estado = $this->db->delete("almacen.lineasxsucursales");
					if (isset($this->request->sucursales)) {
						foreach ($this->request->sucursales as $key => $value) {
							$data = array(
								"codlinea" => $codlinea, 
								"codsucursal" => $value
							);
							$estado = $this->db->insert("almacen.lineasxsucursales", $data);

							$almacenes = $this->db->query("select codalmacen,codsucursal from almacen.almacenes where codsucursal=".$value." AND estado=1")->result_array();
							foreach ($almacenes as $key => $valu) {
								$productos = $this->db->query("select *from almacen.productos where codlinea=".$codlinea." AND estado=1")->result_array();

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
					$estado = $codlinea;
				}
			}else{
				$estado = $this->phuyu_model->phuyu_editar($carpeta.".".$tabla, $campos, $valores, $this->request->codigo, $existe[0][$this->request->codigo]);
				$estado = $existe[0][$this->request->codigo];
			}
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}
}