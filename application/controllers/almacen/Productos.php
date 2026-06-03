<?php defined('BASEPATH') or exit('No direct script access allowed');

require_once(APPPATH . "third_party/phuyu_excel/PHPExcel.php");

class Productos extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model("phuyu_model");
	}

	public function index()
	{
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				if ($_SESSION["phuyu_ruc"] == "20603454112") {
					$this->load->view("almacen/productos/index_lista");
				} else {
					$this->load->view("almacen/productos/index");
				}
				// $this->load->view("almacen/productos/index_lista");
			} else {
				$this->load->view("phuyu/505");
			}
		} else {
			$this->load->view("phuyu/404");
		}
	}

	public function lista()
	{
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$limit = 12;
			$offset = $this->request->pagina * $limit - $limit;

			$lista = $this->db->query("select productos.*, marcas.descripcion as marca from almacen.productos as productos inner join almacen.marcas as marcas on(productos.codmarca=marcas.codmarca) where (UPPER(productos.descripcion) like UPPER('%" . $this->request->buscar . "%') or UPPER(productos.codigo) like UPPER('%" . $this->request->buscar . "%') or UPPER(marcas.descripcion) like UPPER('%" . $this->request->buscar . "%') ) and productos.estado=1 order by productos.descripcion, productos.codproducto asc offset " . $offset . " limit " . $limit)->result_array();

			foreach ($lista as $key => $value) {
				$precio = $this->db->query("select pventapublico,codunidad,preciocosto from almacen.productounidades where codproducto=" . $value["codproducto"] . " order by factor")->result_array();

				if (count($precio) == 0) {
					$lista[$key]["precio"] = 0.00;
					$codunidad = 0;
					$lista[$key]["costo"] = 0.00;
				} else {
					$lista[$key]["precio"] = number_format(round($precio[0]["pventapublico"], 2), 2);
					$codunidad = $precio[0]["codunidad"];
					$lista[$key]["costo"] = number_format(round($precio[0]["preciocosto"], 2), 2);
				}

				$stock = $this->db->query("select pu.stockactualconvertido,u.descripcion as unidad from almacen.productoubicacion as pu inner join almacen.unidades as u on(pu.codunidad=u.codunidad) where pu.codproducto=" . $value["codproducto"] . " and pu.codunidad=" . $codunidad . " and pu.codalmacen=" . $_SESSION["phuyu_codalmacen"] . " and pu.estado=1")->result_array();
				if (count($stock) == 0) {
					$lista[$key]["stock"] = 0;
					$lista[$key]["unidad"] = "SIN UNIDAD";
				} else {
					$lista[$key]["stock"] = round($stock[0]["stockactualconvertido"], 2);
					$lista[$key]["unidad"] = $stock[0]["unidad"];
				}
			}

			$total = $this->db->query("select count(*) as total from almacen.productos as productos inner join almacen.marcas as marcas on(productos.codmarca=marcas.codmarca) where (UPPER(productos.descripcion) like UPPER('%" . $this->request->buscar . "%') or UPPER(productos.codigo) like UPPER('%" . $this->request->buscar . "%') or UPPER(marcas.descripcion) like UPPER('%" . $this->request->buscar . "%') ) and productos.estado=1")->result_array();

			$paginas = floor($total[0]["total"] / $limit);
			if (($total[0]["total"] % $limit) != 0) {
				$paginas = $paginas + 1;
			}

			$paginacion = array();
			$paginacion["total"] = $total[0]["total"];
			$paginacion["actual"] = $this->request->pagina;
			$paginacion["ultima"] = $paginas;
			$paginacion["desde"] = $offset;
			$paginacion["hasta"] = $offset + $limit;

			echo json_encode(array("lista" => $lista, "paginacion" => $paginacion));
		} else {
			$this->load->view("phuyu/404");
		}
	}

	function buscar_productos()
	{
		if ($this->input->is_ajax_request()) {
			if (isset($_POST["q"])) {
				$productos = $this->db->query("select producto.codproducto, producto.codigo, 
				producto.descripcion, 
				marca.descripcion as marca from almacen.productos as producto 
				inner join almacen.marcas as marca on (producto.codmarca=marca.codmarca) 
				where (REPLACE(UPPER(producto.descripcion),' ','%') like REPLACE (UPPER('%" . $_POST["q"] . "%'),' ','%') or UPPER(producto.codigo) like UPPER('%" . $_POST["q"] . "%') or UPPER(marca.descripcion) like UPPER('%" . $_POST["q"] . "%') ) and producto.estado=1 limit 10")->result_array();
			} else {
				$productos = $this->db->query("select producto.codproducto, producto.codigo, producto.descripcion, marca.descripcion as marca from almacen.productos as producto inner join almacen.marcas as marca on (producto.codmarca=marca.codmarca) where producto.estado=1 limit 10")->result_array();
			}
			echo json_encode($productos);
		}
	}

	public function nuevo()
	{
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$unidades = $this->db->query("select *from almacen.unidades where estado=1 order by descripcion")->result_array();
				$atenciones = $this->db->query("select *from almacen.atenciones where estado=1 order by descripcion")->result_array();
				$afectacionigv = $this->db->query("select *from afectacionigv where estado = 1")->result_array();
				if ($_SESSION["phuyu_rubro"] == 4) {
					$this->load->view("almacen/productos/nuevo_perfumeria", compact("unidades", "atenciones"));
				} else {
					$this->load->view("almacen/productos/nuevo", compact("unidades", "atenciones", "afectacionigv"));
				}
			} else {
				$this->load->view("phuyu/505");
			}
		} else {
			$this->load->view("phuyu/404");
		}
	}

	public function operacion()
	{
		if ($this->input->is_ajax_request() and isset($_SESSION["phuyu_codusuario"])) {
			$this->load->view("almacen/productos/operacion");
		} else {
			$this->load->view("phuyu/404");
		}
	}

	function ver($codregistro)
	{
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_codusuario"])) {
				$info = $this->db->query("select almacen.productos.*, almacen.marcas.descripcion as marca, almacen.familias.descripcion as familia, almacen.lineas.descripcion as linea,(CASE WHEN tipo = 1 THEN 'BIEN' ELSE 'SERVICIO' END) AS tipo from almacen.productos inner join almacen.marcas on(almacen.productos.codmarca=almacen.marcas.codmarca) inner join almacen.familias on(almacen.productos.codfamilia=almacen.familias.codfamilia) inner join almacen.lineas on(almacen.productos.codlinea=almacen.lineas.codlinea) where codproducto=" . $codregistro)->result_array();

				foreach ($info as $key => $value) {
					$afectacion = $this->db->query("select *from almacen.productoubicacion where codproducto=" . $value["codproducto"] . " AND codalmacen=" . $_SESSION["phuyu_codalmacen"] . " AND factor=1 AND estado = 1")->result_array();

					$info[$key]["comisionvendedor"] = $afectacion[0]["comisionvendedor"];
				}

				$unidades = $this->db->query("select almacen.productounidades.*, almacen.unidades.descripcion as unidad from almacen.productounidades inner join almacen.unidades on(almacen.productounidades.codunidad=almacen.unidades.codunidad) where almacen.productounidades.codproducto=" . $codregistro . " and almacen.productounidades.estado=1 order by almacen.productounidades.factor")->result_array();
				foreach ($unidades as $key => $value) {
					$stock = $this->db->query("select stockactual from almacen.productoubicacion where codproducto=" . $value["codproducto"] . " and codunidad=" . $value["codunidad"] . " and codalmacen=" . $_SESSION["phuyu_codalmacen"] . " and estado=1")->result_array();
					$unidades[$key]["stock"] = 0;
					if (count($stock) > 0) {
						$unidades[$key]["stock"] = round($stock[0]["stockactual"], 2);
					}
				}
				$this->load->view("almacen/productos/ver", compact("info", "unidades"));
			} else {
				$this->load->view("inicio/505");
			}
		} else {
			$this->load->view("inicio/404");
		}
	}

	function stock_almacenes($codproducto)
	{
		if ($this->input->is_ajax_request()) {

			$info = $this->db->query("SELECT pun.codalmacen, p.codproducto, p.codigo,p.codfamilia, p.codlinea, p.codmarca, ma.descripcion AS marca, p.descripcion, pun.unidades, p.afectoicbper, p.controlstock, p.afectoigvcompra, pun.almacen, p.foto, p.calcular, p.paraventa, p.codmodelo, p.codcolor, p.codtalla
				FROM almacen.productos p
  				JOIN almacen.v_productounidades pun ON (p.codproducto = pun.codproducto AND p.estado = 1 )
  				JOIN almacen.marcas ma ON (p.codmarca = ma.codmarca) WHERE pun.codalmacen <> " . $_SESSION["phuyu_codalmacen"] . " AND p.codproducto=" . $codproducto)->result_array();

			echo json_encode(["almacenes" => $info]);
		}
	}

	function guardar()
	{
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			$campos = ["codfamilia", "codlinea", "codmarca", "codempresa", "codigo", "descripcion", "afectoicbper", "codatencion", "paraventa", "calcular", "controlstock", "caracteristicas", "tipo"];
			$valores = [
				(int)$this->request->campos->codfamilia,
				(int)$this->request->campos->codlinea,
				(int)$this->request->campos->codmarca,
				(int)$_SESSION["phuyu_codempresa"],
				$this->request->campos->codigo,
				$this->request->campos->descripcion,
				(int)$this->request->campos->afectoicbper,
				(int)$this->request->campos->codatencion,
				(int)1,
				(int)$this->request->campos->calcular,
				(int)$this->request->campos->controlstock,
				$this->request->campos->caracteristicas,
				(int)$this->request->campos->tipo
			];

			$campos_1 = ["codproducto", "codunidad", "codsucursal", "factor", "preciocompra", "preciocosto", "pventapublico", "pventamin", "pventacredito", "pventaxmayor", "pventaadicional", "codigobarra", "estado"];

			$this->db->trans_begin();

			if ($this->request->campos->codregistro == "") {
				$codproducto = $this->phuyu_model->phuyu_guardar("almacen.productos", $campos, $valores, "true");

				if ($this->request->campos->codigo == "") {
					$data = array("codigo" => "000" . $codproducto);
					$this->db->where("codproducto", $codproducto);
					$estado = $this->db->update("almacen.productos", $data);
				}

				if (isset($this->request->unidades)) {
					foreach ($this->request->unidades as $key => $value) {
						$valores_1 = [
							(int)$codproducto,
							(int)$this->request->unidades[$key]->codunidad,
							(int)$_SESSION["phuyu_codsucursal"],
							$this->request->unidades[$key]->factor,
							(float)$this->request->unidades[$key]->preciocompra,
							(float)$this->request->unidades[$key]->preciocompra,
							(float)$this->request->unidades[$key]->pventapublico,
							(float)$this->request->unidades[$key]->pventamin,
							(float)$this->request->unidades[$key]->pventacredito,
							(float)$this->request->unidades[$key]->pventaxmayor,
							(float)$this->request->unidades[$key]->pventaadicional,
							$this->request->unidades[$key]->codigobarra,
							1
						];
						$estado = $this->phuyu_model->phuyu_guardar("almacen.productounidades", $campos_1, $valores_1);

						$almacenes = $this->db->query("select *from almacen.almacenes where estado = 1")->result_array();

						foreach ($almacenes as $k => $val) {
							if ($_SESSION["phuyu_codalmacen"] != (int)$val["codalmacen"]) {
								$this->request->campos->codafectacionigvcompra = $val["codafectacionigv"];
								$this->request->campos->codafectacionigvventa = $val["codafectacionigv"];
							}
							$campos = ["codalmacen", "codproducto", "codunidad", "codsucursal", "factor", "preciocompra", "preciocosto", "pventapublico", "pventamin", "pventacredito", "pventaxmayor", "pventaadicional", "codigobarra", "estado", "codafectacionigvcompra", "codafectacionigvventa", "comisionvendedor"];
							$valores = [
								(int)$val["codalmacen"],
								(int)$codproducto,
								(int)$this->request->unidades[$key]->codunidad,
								(int)$val["codsucursal"],
								$this->request->unidades[$key]->factor,
								(float)$this->request->unidades[$key]->preciocompra,
								(float)$this->request->unidades[$key]->preciocompra,
								(float)$this->request->unidades[$key]->pventapublico,
								(float)$this->request->unidades[$key]->pventamin,
								(float)$this->request->unidades[$key]->pventacredito,
								(float)$this->request->unidades[$key]->pventaxmayor,
								(float)$this->request->unidades[$key]->pventaadicional,
								$this->request->unidades[$key]->codigobarra,
								1,
								(int)$this->request->campos->codafectacionigvcompra,
								(int)$this->request->campos->codafectacionigvventa,
								(int)$this->request->campos->comisionvendedor
							];
							$estado = $this->phuyu_model->phuyu_guardar("almacen.productoubicacion", $campos, $valores);
						}
					}
				}
			} else {
				$codproducto = $this->request->campos->codregistro;
				$estado = $this->phuyu_model->phuyu_editar("almacen.productos", $campos, $valores, "codproducto", $codproducto);

				$campos_2 = ["estado"];
				$valores_2 = [0];
				$f = ["codproducto"];
				$v = [$codproducto];
				$f2 = ["codproducto", "codalmacen"];
				$v2 = [$codproducto, (int)$_SESSION["phuyu_codalmacen"]];
				$estado = $this->phuyu_model->phuyu_editar_1("almacen.productounidades", $campos_2, $valores_2, $f, $v);
				$estado = $this->phuyu_model->phuyu_editar_1("almacen.productoubicacion", $campos_2, $valores_2, $f2, $v2);

				if (isset($this->request->unidades)) {
					foreach ($this->request->unidades as $key => $value) {
						$valores_1 = [
							(int)$codproducto,
							(int)$this->request->unidades[$key]->codunidad,
							(int)$_SESSION["phuyu_codsucursal"],
							$this->request->unidades[$key]->factor,
							(float)$this->request->unidades[$key]->preciocompra,
							(float)$this->request->unidades[$key]->preciocompra,
							(float)$this->request->unidades[$key]->pventapublico,
							(float)$this->request->unidades[$key]->pventamin,
							(float)$this->request->unidades[$key]->pventacredito,
							(float)$this->request->unidades[$key]->pventaxmayor,
							(float)$this->request->unidades[$key]->pventaadicional,
							$this->request->unidades[$key]->codigobarra,
							1
						];

						$existe = $this->db->query("select *from almacen.productounidades where codproducto=" . $codproducto . " and codunidad=" . $this->request->unidades[$key]->codunidad)->result_array();

						if (count($existe) == 0) {
							$estado = $this->phuyu_model->phuyu_guardar("almacen.productounidades", $campos_1, $valores_1);
						} else {
							$f = ["codproducto", "codunidad"];
							$v = [(int)$codproducto, $this->request->unidades[$key]->codunidad];
							$estado = $this->phuyu_model->phuyu_editar_1("almacen.productounidades", $campos_1, $valores_1, $f, $v);
						}

						$existe_ubi = $this->db->query("select *from almacen.productoubicacion where codproducto=" . $codproducto . " and codunidad=" . $this->request->unidades[$key]->codunidad)->result_array();

						if (count($existe_ubi) == 0) {
							$almacenes = $this->db->query("select *from almacen.almacenes where estado = 1")->result_array();

							foreach ($almacenes as $k => $val) {
								if ($_SESSION["phuyu_codalmacen"] != (int)$val["codalmacen"]) {
									$this->request->campos->codafectacionigvcompra = $val["codafectacionigv"];
									$this->request->campos->codafectacionigvventa = $val["codafectacionigv"];
								}
								$campos = ["codalmacen", "codproducto", "codunidad", "codsucursal", "factor", "preciocompra", "preciocosto", "pventapublico", "pventamin", "pventacredito", "pventaxmayor", "pventaadicional", "codigobarra", "estado", "codafectacionigvcompra", "codafectacionigvventa", "comisionvendedor"];
								$valores = [
									(int)$val["codalmacen"],
									(int)$codproducto,
									(int)$this->request->unidades[$key]->codunidad,
									(int)$val["codsucursal"],
									$this->request->unidades[$key]->factor,
									(float)$this->request->unidades[$key]->preciocompra,
									(float)$this->request->unidades[$key]->preciocompra,
									(float)$this->request->unidades[$key]->pventapublico,
									(float)$this->request->unidades[$key]->pventamin,
									(float)$this->request->unidades[$key]->pventacredito,
									(float)$this->request->unidades[$key]->pventaxmayor,
									(float)$this->request->unidades[$key]->pventaadicional,
									$this->request->unidades[$key]->codigobarra,
									1,
									(int)$this->request->campos->codafectacionigvcompra,
									(int)$this->request->campos->codafectacionigvventa,
									(int)$this->request->campos->comisionvendedor
								];
								$estado = $this->phuyu_model->phuyu_guardar("almacen.productoubicacion", $campos, $valores);
							}
						} else {
							$campos = ["estado", "factor", "preciocompra", "preciocosto", "pventapublico", "pventamin", "pventacredito", "pventaxmayor", "pventaadicional", "codigobarra", "codafectacionigvcompra", "codafectacionigvventa", "comisionvendedor"];
							$valores = [
								1,
								$this->request->unidades[$key]->factor,
								(float)$this->request->unidades[$key]->preciocompra,
								(float)$this->request->unidades[$key]->preciocompra,
								(float)$this->request->unidades[$key]->pventapublico,
								(float)$this->request->unidades[$key]->pventamin,
								(float)$this->request->unidades[$key]->pventacredito,
								(float)$this->request->unidades[$key]->pventaxmayor,
								(float)$this->request->unidades[$key]->pventaadicional,
								$this->request->unidades[$key]->codigobarra,
								(int)$this->request->campos->codafectacionigvcompra,
								(int)$this->request->campos->codafectacionigvventa,
								(int)$this->request->campos->comisionvendedor
							];
							$f = ["codproducto", "codunidad", "codalmacen"];
							$v = [(int)$codproducto, $this->request->unidades[$key]->codunidad, (int)$_SESSION["phuyu_codalmacen"]];
							$estado = $this->phuyu_model->phuyu_editar_1("almacen.productoubicacion", $campos, $valores, $f, $v);
						}

						if ($this->request->unidades[$key]->factor > 1) {
							$almacenes = $this->db->query("select *from almacen.almacenes where estado = 1")->result_array();

							foreach ($almacenes as $k => $val) {
								$stockconvertidobase = $this->db->query("select *from almacen.productoubicacion where codproducto=" . $codproducto . " AND factor=1 AND codalmacen=" . $val["codalmacen"])->result_array();

								$stockc = ((float)$stockconvertidobase[0]["stockactualconvertido"] / (float)$this->request->unidades[$key]->factor);

								$data = array(
									"stockactualconvertido" => (float)round($stockc, 3)
								);

								$this->db->where("codalmacen", $val["codalmacen"]);
								$this->db->where("codproducto", $codproducto);
								$this->db->where("codunidad", (int)$this->request->unidades[$key]->codunidad);
								$estado = $this->db->update("almacen.productoubicacion", $data);
							}
						}
					}
				}
			}

			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$estado = 0;
			} else {
				$this->db->trans_commit();
				$estado = $codproducto;
			}

			echo $estado;
		} else {
			$this->load->view("phuyu/404");
		}
	}

	function guardar_foto()
	{
		if ($this->input->is_ajax_request()) {
			$estado = 1;
			echo $this->input->post('foto');
			exit;
			if ($_FILES["foto"]["name"] != "") {
				$file = $this->input->post("codproducto") . "_" . substr($_FILES["foto"]["name"], -5);
				move_uploaded_file($_FILES["foto"]["tmp_name"], "./public/img/productos/" . $file);
				chmod("./public/img/productos/" . $file, 0777);

				$data = array("foto" => $file);
				$this->db->where("codproducto", $this->input->post("codproducto"));
				$estado = $this->db->update("almacen.productos", $data);
			}
			echo $estado;
		} else {
			$this->load->view("phuyu/404");
		}
	}

	function editar()
	{
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$info = $this->db->query("select codproducto as codregistro,* from almacen.productos where codproducto=" . $this->request->codregistro)->result_array();
			foreach ($info as $key => $value) {
				$afectacion = $this->db->query("select *from almacen.productoubicacion where codproducto=" . $value["codproducto"] . " AND codalmacen=" . $_SESSION["phuyu_codalmacen"] . " AND factor=1 AND estado = 1")->result_array();

				if ($afectacion[0]["codafectacionigvcompra"] == "" || $afectacion[0]["codafectacionigvcompra"] == null || $afectacion[0]["codafectacionigvcompra"] == 0) {
					$afectacion[0]["codafectacionigvcompra"] = $_SESSION["phuyu_afectacionigv"];
				}
				if ($afectacion[0]["codafectacionigvventa"] == "" || $afectacion[0]["codafectacionigvventa"] == null || $afectacion[0]["codafectacionigvventa"] == 0) {
					$afectacion[0]["codafectacionigvventa"] = $_SESSION["phuyu_afectacionigv"];
				}
				$info[$key]["codafectacionigvcompra"] = $afectacion[0]["codafectacionigvcompra"];
				$info[$key]["codafectacionigvventa"] = $afectacion[0]["codafectacionigvventa"];
				$info[$key]["comisionvendedor"] = $afectacion[0]["comisionvendedor"];
			}
			echo json_encode($info);
		} else {
			$this->load->view("phuyu/404");
		}
	}

	function unidades()
	{
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$unidades = $this->db->query("select pu.*,u.descripcion as unidad from almacen.productoubicacion as pu inner join almacen.unidades as u on(pu.codunidad=u.codunidad) where pu.codproducto=" . $this->request->codregistro . " AND pu.codalmacen = " . $_SESSION["phuyu_codalmacen"] . " and pu.estado=1 order by pu.factor asc")->result_array();
			$campos = $this->db->query("select codfamilia,codlinea,codmarca from almacen.productos where codproducto=" . $this->request->codregistro)->result_array();

			$data["unidades"] = $unidades;
			$data["campos"] = $campos;
			echo json_encode($data);
		} else {
			$this->load->view("phuyu/404");
		}
	}

	function unidades_venta($codproducto, $factor)
	{
		if ($this->input->is_ajax_request()) {
			$unidades = $this->db->query("select u.codunidad,u.descripcion,pu.factor from almacen.productounidades as pu inner join almacen.unidades as u on(pu.codunidad=u.codunidad) where pu.codproducto=" . $codproducto . " and pu.factor<>" . $factor . " order by pu.factor asc")->result_array();
			echo json_encode($unidades);
		}
	}

	function eliminar()
	{
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$estado = $this->phuyu_model->phuyu_eliminar("almacen.productos", "codproducto", $this->request->codregistro);
			echo $estado;
		} else {
			$this->load->view("phuyu/404");
		}
	}


	// BUSCAR PRODUCTOS EN COMPRAS, EN VENTAS, EN INGRESOS Y EGRESOS ALMACEN //

	function buscar($operacion)
	{
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_codusuario"])) {
				$this->load->view("almacen/productos/buscar", compact("operacion"));
			} else {
				$this->load->view("phuyu/505");
			}
		} else {
			$this->load->view("phuyu/404");
		}
	}

	function buscar_codigobarra($codigobarra)
	{
		if ($this->input->is_ajax_request()) {
			$info = $this->db->query("select p.codproducto,p.descripcion,p.caracteristicas, p.afectoicbper,p.controlstock, p.afectoigvcompra, p.afectoigvventa, p.codigo,p.calcular,p.foto,u.codunidad,u.descripcion as unidad,round(pu.stockactual,3) as stock, m.descripcion as marca, puv.factor, puv.factor as factormaximo, round(puv.pventapublico,2) as precio, round(puv.pventamin,2) as preciomin, round(puv.pventacredito,2) as preciocredito, round(puv.pventaxmayor,2) as preciomayor, round(puv.preciocosto,2) as preciocosto, round(puv.pventaadicional,2) as precioadicional from almacen.productos as p inner join almacen.productoubicacion as pu on(p.codproducto=pu.codproducto) inner join almacen.unidades as u on(u.codunidad=pu.codunidad) inner join almacen.marcas as m on(p.codmarca=m.codmarca) inner join almacen.productounidades as puv on(pu.codproducto=puv.codproducto and pu.codunidad=puv.codunidad) where puv.codigobarra='" . $codigobarra . "' and p.estado=1 and pu.estado=1 and pu.codalmacen=" . $_SESSION["phuyu_codalmacen"])->result_array();
			$data = array();
			$precio = 0;
			if (count($info) > 0) {
				$precio = $info[0]["precio"];
			}
			$data["cantidad"] = count($info);
			$data["info"] = $info;
			$data["precio"] = (float)$precio;

			echo json_encode($data);
		}
	}

	function buscar_salidas()
	{
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$limit = 10;
			$offset = $this->request->pagina * $limit - $limit;

			$lista = $this->db->query("SELECT pun.codalmacen, p.codproducto, p.codigo,p.codfamilia, p.codlinea, p.codmarca, ma.descripcion AS marca, p.descripcion, pun.unidades, p.afectoicbper, p.controlstock, p.afectoigvcompra, p.afectoigvventa, p.foto, p.calcular, p.paraventa, p.codmodelo, p.codcolor, p.codtalla, p.tipo 
  FROM almacen.productos p
  JOIN almacen.v_productounidades pun ON (p.codproducto = pun.codproducto AND p.estado = 1 )
  JOIN almacen.lineasxsucursales ls ON (pun.codsucursal = ls.codsucursal AND p.codlinea = ls.codlinea AND ls.codsucursal = " . $_SESSION["phuyu_codsucursal"] . " )
  JOIN almacen.marcas ma ON (p.codmarca = ma.codmarca)
			  where (REPLACE(UPPER(p.descripcion),' ','%') like REPLACE (UPPER('%" . $this->request->buscar . "%'),' ','%') or UPPER(p.codigo) like UPPER('%" . $this->request->buscar . "%') or UPPER(ma.descripcion) like UPPER('%" . $this->request->buscar . "%') ) and p.estado=1 and pun.codalmacen=" . $_SESSION["phuyu_codalmacen"] . " order by p.codproducto desc offset " . $offset . " limit " . $limit)->result_array();

			foreach ($lista as $key => $value) {
				$factormaximo = $this->db->query("select max(factor) as factor from almacen.productounidades where codproducto=" . $value["codproducto"] . " and estado=1")->result_array();

				if ($value["unidades"] != "" || $value["unidades"] != null) {
					$unidades = explode(';', $value["unidades"]);
					foreach ($unidades as $k => $v) {
						$factores = explode('|', $v);
						if ($factores[8] == 1) {
							$lista[$key]["factormaximo"] = $factormaximo[0]["factor"];
							$lista[$key]["factor"] = $factores[8];
							$lista[$key]["precio"] = $factores[5];
							$lista[$key]["precioventa"] = $factores[5];
							$lista[$key]["preciomin"] = round((float)$factores[6], 2);
							$lista[$key]["preciocredito"] = round((float)$factores[7], 2);
							$lista[$key]["preciomayor"] = round((float)$factores[10], 2);
							$lista[$key]["stock"] = $factores[3];
							$lista[$key]["stockproveedor"] = $factores[4];
							$lista[$key]["unidad"] = $factores[1];
						}
					}
				}
			}

			$total = $this->db->query("select count(*) as total FROM almacen.productos p
  JOIN almacen.v_productounidades pun ON (p.codproducto = pun.codproducto AND p.estado = 1 )
  JOIN almacen.lineasxsucursales ls ON (pun.codsucursal = ls.codsucursal AND p.codlinea = ls.codlinea AND ls.codsucursal = " . $_SESSION["phuyu_codsucursal"] . " )
  JOIN almacen.marcas ma ON (p.codmarca = ma.codmarca)
			  where (REPLACE(UPPER(p.descripcion),' ','%') like REPLACE (UPPER('%" . $this->request->buscar . "%'),' ','%') or UPPER(p.codigo) like UPPER('%" . $this->request->buscar . "%') or UPPER(ma.descripcion) like UPPER('%" . $this->request->buscar . "%') ) and p.estado=1 and pun.codalmacen=" . $_SESSION["phuyu_codalmacen"])->result_array();

			$paginas = floor($total[0]["total"] / $limit);
			if (($total[0]["total"] % $limit) != 0) {
				$paginas = $paginas + 1;
			}

			$paginacion = array();
			$paginacion["total"] = $total[0]["total"];
			$paginacion["actual"] = $this->request->pagina;
			$paginacion["ultima"] = $paginas;
			$paginacion["desde"] = $offset;
			$paginacion["hasta"] = $offset + $limit;

			echo json_encode(array("lista" => $lista, "paginacion" => $paginacion));
		} else {
			$this->load->view("phuyu/404");
		}
	}

	function informacion_item()
	{
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			$lista = $this->db->query("select round(pu.stockactualconvertido,2) as stock,pu.codunidad 
				from almacen.productoubicacion as pu WHERE codproducto=" . $this->request->codproducto . " and codunidad=" . $this->request->codunidad . " AND codalmacen=" . $_SESSION["phuyu_codalmacen"])->result_array();

			foreach ($lista as $key => $value) {
				$factormaximo = $this->db->query("select max(factor) as factor from almacen.productounidades where codproducto=" . $this->request->codproducto . " and estado=1")->result_array();

				$precio = $this->db->query("select factor,pventapublico,pventamin,pventacredito,pventaxmayor, preciocosto,pventaadicional from almacen.productounidades where codproducto=" . $this->request->codproducto . " and codunidad=" . $this->request->codunidad . " and estado=1")->result_array();
				if (count($precio) == 0) {
					$lista[$key]["factor"] = 0;
					$lista[$key]["factormaximo"] = 0;
					$lista[$key]["precio"] = 0.00;
					$lista[$key]["preciomin"] = 0.00;
					$lista[$key]["preciocredito"] = 0.00;
					$lista[$key]["preciomayor"] = 0.00;
					$lista[$key]["preciocosto"] = 0.00;
					$lista[$key]["precioadicional"] = 0.00;
				} else {
					$lista[$key]["factor"] = $precio[0]["factor"];
					$lista[$key]["factormaximo"] = $factormaximo[0]["factor"];
					if (isset($this->request->salida)) {
						$preciod = $precio[0]["preciocosto"];
					} else {
						$preciod = $precio[0]["pventapublico"];
					}
					$lista[$key]["precio"] = round($preciod, 2);
					$lista[$key]["precioventa"] = round($preciod, 2);
					$lista[$key]["preciomin"] = round($precio[0]["pventamin"], 2);
					$lista[$key]["preciocredito"] = round($precio[0]["pventacredito"], 2);
					$lista[$key]["preciomayor"] = round($precio[0]["pventaxmayor"], 2);
					$lista[$key]["preciocosto"] = round($precio[0]["preciocosto"], 2);
					$lista[$key]["precioadicional"] = round($precio[0]["pventaadicional"], 2);
				}
			}

			echo json_encode($lista);
		}
	}

	function buscar_ingresos()
	{
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$limit = 10;
			$offset = $this->request->pagina * $limit - $limit;

			$lista = $this->db->query("SELECT pun.codalmacen, p.codproducto, p.codigo,p.codfamilia, p.codlinea, p.codmarca, ma.descripcion AS marca, p.descripcion, pun.unidades, p.afectoicbper, p.controlstock, p.afectoigvcompra, p.afectoigvventa, p.foto, p.calcular, p.paraventa, p.codmodelo, p.codcolor, p.codtalla
			  FROM almacen.productos p
			  JOIN almacen.v_productounidades pun ON (p.codproducto = pun.codproducto AND p.estado = 1 )
			  JOIN almacen.lineasxsucursales ls ON (pun.codsucursal = ls.codsucursal AND p.codlinea = ls.codlinea AND ls.codsucursal = " . $_SESSION["phuyu_codsucursal"] . " )
			  JOIN almacen.marcas ma ON (p.codmarca = ma.codmarca)
			  where (REPLACE(UPPER(p.descripcion),' ','%') like REPLACE (UPPER('%" . $this->request->buscar . "%'),' ','%') or UPPER(p.codigo) like UPPER('%" . $this->request->buscar . "%') or UPPER(ma.descripcion) like UPPER('%" . $this->request->buscar . "%') ) and p.estado=1 and pun.codalmacen=" . $_SESSION["phuyu_codalmacen"] . " order by p.codproducto desc offset " . $offset . " limit " . $limit)->result_array();

			foreach ($lista as $key => $value) {
				$factormaximo = $this->db->query("select max(factor) as factor from almacen.productounidades where codproducto=" . $value["codproducto"] . " and estado=1")->result_array();

				if ($value["unidades"] != "") {
					$unidades = explode(';', $value["unidades"]);
					foreach ($unidades as $k => $v) {
						$factores = explode('|', $v);
						if ($factores[8] == 1) {
							$lista[$key]["factormaximo"] = $factormaximo[0]["factor"];
							$lista[$key]["factor"] = $factores[8];
							$lista[$key]["precio"] = $factores[9];
							$lista[$key]["stock"] = $factores[3];
							$lista[$key]["preciomayor"] = round((float)$factores[10], 2);
						}
					}
				}
			}

			$total = $this->db->query("select count(*) as total FROM almacen.productos p
  JOIN almacen.v_productounidades pun ON (p.codproducto = pun.codproducto AND p.estado = 1 )
  JOIN almacen.lineasxsucursales ls ON (pun.codsucursal = ls.codsucursal AND p.codlinea = ls.codlinea AND ls.codsucursal = " . $_SESSION["phuyu_codsucursal"] . " )
  JOIN almacen.marcas ma ON (p.codmarca = ma.codmarca)
			  where (REPLACE(UPPER(p.descripcion),' ','%') like REPLACE (UPPER('%" . $this->request->buscar . "%'),' ','%') or UPPER(p.codigo) like UPPER('%" . $this->request->buscar . "%') or UPPER(ma.descripcion) like UPPER('%" . $this->request->buscar . "%') ) and p.estado=1 and pun.codalmacen=" . $_SESSION["phuyu_codalmacen"])->result_array();

			$paginas = floor($total[0]["total"] / $limit);
			if (($total[0]["total"] % $limit) != 0) {
				$paginas = $paginas + 1;
			}

			$paginacion = array();
			$paginacion["total"] = $total[0]["total"];
			$paginacion["actual"] = $this->request->pagina;
			$paginacion["ultima"] = $paginas;
			$paginacion["desde"] = $offset;
			$paginacion["hasta"] = $offset + $limit;

			echo json_encode(array("lista" => $lista, "paginacion" => $paginacion));
		} else {
			$this->load->view("phuyu/404");
		}
	}


	function restobar($codlinea)
	{
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_codusuario"])) {
				$this->load->view("almacen/productos/restaurant", compact("codlinea"));
			}
		}
	}

	function buscando_restobar_original()
	{
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			if ($this->request->codlinea == 0) {
				$linea = "";
			} else {
				$linea = "p.codlinea=" . $this->request->codlinea . " and ";
			}

			$lista = $this->db->query("select p.codproducto,p.codigo,p.descripcion,p.controlstock,p.afectoigvcompra,p.afectoigvventa, 
			p.codigo, p.calcular, p.foto, 
			u.codunidad,u.descripcion as unidad,round(pu.stockactualconvertido,3) as stock,
				(select coalesce(sum(pd.cantidad),0) 
				from kardex.pedidos as pedi inner join kardex.pedidosdetalle as pd on(pedi.codpedido=pd.codpedido) 
				where pedi.estado=1 and pu.codproducto=pd.codproducto and pu.codunidad=pd.codunidad) as comprometido, m.descripcion as marca,l.background,l.color 
				from almacen.productos as p inner join almacen.productoubicacion as pu on(p.codproducto=pu.codproducto) 
				inner join almacen.unidades as u on(u.codunidad=pu.codunidad) inner join almacen.marcas as m on(p.codmarca=m.codmarca) 
				inner join almacen.lineas as l on(p.codlinea=l.codlinea) 
				where " . $linea . " (REPLACE(UPPER(p.descripcion),' ','%') like REPLACE (UPPER('%" . $this->request->buscar . "%'),' ','%') or UPPER(p.codigo) like UPPER('%" . $this->request->buscar . "%') or UPPER(m.descripcion) like UPPER('%" . $this->request->buscar . "%') ) and p.paraventa=1 and p.estado=1 and pu.estado=1 and pu.codalmacen=" . $_SESSION["phuyu_codalmacen"] . " order by p.codproducto desc")->result_array();

			foreach ($lista as $key => $value) {
				$factormaximo = $this->db->query("select max(factor) as factor from almacen.productounidades where codproducto=" . $value["codproducto"] . " and estado=1")->result_array();

				$precio = $this->db->query("select factor,pventapublico,pventamin,pventacredito,pventaxmayor, preciocosto,pventaadicional from almacen.productounidades where codproducto=" . $value["codproducto"] . " and codunidad=" . $value["codunidad"] . " and estado=1")->result_array();
				$lista[$key]["mostrarstock"] = "STOCK: " . round($value["stock"] - $value["comprometido"], 3);
				$lista[$key]["stockdisponible"] = round($value["stock"] - $value["comprometido"], 3);
				if (count($precio) == 0) {
					$lista[$key]["factor"] = 0;
					$lista[$key]["factormaximo"] = 0;
					$lista[$key]["precio"] = 0.00;
					$lista[$key]["preciomin"] = 0.00;
					$lista[$key]["preciocredito"] = 0.00;
					$lista[$key]["preciomayor"] = 0.00;
					$lista[$key]["preciocosto"] = 0.00;
					$lista[$key]["precioadicional"] = 0.00;
				} else {
					$lista[$key]["factor"] = $precio[0]["factor"];
					$lista[$key]["factormaximo"] = $factormaximo[0]["factor"];
					$lista[$key]["precio"] = round($precio[0]["pventapublico"], 2);
					$lista[$key]["preciomin"] = round($precio[0]["pventamin"], 2);
					$lista[$key]["preciocredito"] = round($precio[0]["pventacredito"], 2);
					$lista[$key]["preciomayor"] = round($precio[0]["pventaxmayor"], 2);
					$lista[$key]["preciocosto"] = round($precio[0]["preciocosto"], 2);
					$lista[$key]["precioadicional"] = round($precio[0]["pventaadicional"], 2);
				}
			}

			echo json_encode($lista);
		}
	}
	public function buscando_restobar()
	{
		if (!$this->input->is_ajax_request()) {
			show_error('Acceso no autorizado', 403);
			return;
		}

		$this->request = json_decode(file_get_contents('php://input'), true);

		// Parámetros del cliente
		$buscar     = isset($this->request['buscar']) ? trim($this->request['buscar']) : '';
		$codlinea   = isset($this->request['codlinea']) ? intval($this->request['codlinea']) : 0;
		$codalmacen = isset($_SESSION["phuyu_codalmacen"]) ? intval($_SESSION["phuyu_codalmacen"]) : 0;
		$limit      = isset($this->request['limit']) ? intval($this->request['limit']) : 50;
		$offset     = isset($this->request['offset']) ? intval($this->request['offset']) : 0;

		$like = $this->db->escape_like_str($buscar);

		$this->db->select("
        p.codproducto, p.codigo, p.descripcion,
        p.controlstock, p.afectoigvcompra, p.afectoigvventa,
        p.calcular, p.foto,
        u.codunidad, u.descripcion AS unidad,
        ROUND(pu.stockactualconvertido,3) AS stock,
        m.descripcion AS marca, l.background, l.color,
        (
            SELECT COALESCE(SUM(pd.cantidad),0)
            FROM kardex.pedidos AS pedi
            INNER JOIN kardex.pedidosdetalle AS pd ON (pedi.codpedido = pd.codpedido)
            WHERE pedi.estado = 1
              AND pu.codproducto = pd.codproducto
              AND pu.codunidad = pd.codunidad
        ) AS comprometido,
        (
            SELECT COALESCE(MAX(factor),0)
            FROM almacen.productounidades AS pun2
            WHERE pun2.codproducto = p.codproducto AND pun2.estado = 1
        ) AS factormaximo,
        pun.factor, pun.pventapublico, pun.pventamin, pun.pventacredito, pun.pventaxmayor, 
        pun.preciocosto, pun.pventaadicional
   		 ", false);

		$this->db->from('almacen.productos AS p');
		$this->db->join('almacen.productoubicacion AS pu', 'p.codproducto = pu.codproducto', 'inner');
		$this->db->join('almacen.unidades AS u', 'u.codunidad = pu.codunidad', 'inner');
		$this->db->join('almacen.marcas AS m', 'p.codmarca = m.codmarca', 'inner');
		$this->db->join('almacen.lineas AS l', 'p.codlinea = l.codlinea', 'inner');
		$this->db->join('almacen.productounidades AS pun', 'p.codproducto = pun.codproducto AND pu.codunidad = pun.codunidad AND pun.estado = 1', 'left');

		if ($codlinea > 0) {
			$this->db->where('p.codlinea', $codlinea);
		}

		$this->db->group_start();
		$this->db->like("REPLACE(UPPER(p.descripcion),' ','')", str_replace(' ', '', strtoupper($like)));
		$this->db->or_like('UPPER(p.codigo)', strtoupper($like));
		$this->db->or_like('UPPER(m.descripcion)', strtoupper($like));
		$this->db->group_end();

		$this->db->where('p.paraventa', 1);
		$this->db->where('p.estado', 1);
		$this->db->where('pu.estado', 1);
		$this->db->where('pu.codalmacen', $codalmacen);

		$this->db->order_by('p.codproducto', 'desc');

		// 👉 Aquí se aplica el límite
		$limit = 80; // cantidad de registros por página
		$page  = 1;
		$this->db->limit($limit, $offset);

		$query = $this->db->get();
		$lista = $query->result_array();

		foreach ($lista as $k => $row) {
			$stock = (float)$row['stock'];
			$comprometido = (float)$row['comprometido'];
			$disponible = round($stock - $comprometido, 3);

			$lista[$k]['mostrarstock'] = "STOCK: " . $disponible;
			$lista[$k]['stockdisponible'] = $disponible;

			$lista[$k]['precio'] = round((float)($row['pventapublico'] ?? 0), 2);
			$lista[$k]['preciomin'] = round((float)($row['pventamin'] ?? 0), 2);
			$lista[$k]['preciocredito'] = round((float)($row['pventacredito'] ?? 0), 2);
			$lista[$k]['preciomayor'] = round((float)($row['pventaxmayor'] ?? 0), 2);
			$lista[$k]['preciocosto'] = round((float)($row['preciocosto'] ?? 0), 2);
			$lista[$k]['precioadicional'] = round((float)($row['pventaadicional'] ?? 0), 2);
		}

		$this->output->set_content_type('application/json')
			->set_output(json_encode($lista));
	}

	function producto_tipopedido($codproducto)
	{
		if ($this->input->is_ajax_request()) {
			$lista = $this->db->query("select p.codproducto,p.descripcion,u.codunidad,u.descripcion as unidad from almacen.productos as p inner join almacen.productoubicacion as pu on(p.codproducto=pu.codproducto) inner join almacen.unidades as u on(u.codunidad=pu.codunidad) where p.codproducto=" . $codproducto . " and pu.estado=1")->result_array();
			foreach ($lista as $key => $value) {
				$lista[$key]["stock"] = 0;
				$lista[$key]["control"] = 0;
				$lista[$key]["calcular"] = 0;

				$precio = $this->db->query("select pventapublico from almacen.productounidades where codproducto=" . $value["codproducto"] . " and codunidad=" . $value["codunidad"] . " and estado=1")->result_array();
				if (count($precio) == 0) {
					$lista[$key]["precio"] = 0.00;
				} else {
					$lista[$key]["precio"] = round($precio[0]["pventapublico"], 2);
				}
			}
			echo json_encode($lista);
		}
	}

	public function stockextra()
	{
		if ($this->input->is_ajax_request()) {
			if ($_FILES["archivo"]["name"] != "") {
				$file = str_replace(" ", "_", $_FILES["archivo"]["name"]);
				move_uploaded_file($_FILES["archivo"]["tmp_name"], "./public/arch_stock/" . $file);

				$data = array("stockproveedor" => 0);
				$estado = $this->db->update("almacen.productoubicacion", $data);
			} else {
				$estado = 0;
			}

			if ($estado == 1) {
				$archivo = "./public/arch_stock/" . $file;
				$inputFileType = PHPExcel_IOFactory::identify($archivo);
				$objReader = PHPExcel_IOFactory::createReader($inputFileType);
				$objPHPExcel = $objReader->load($archivo);
				$sheet = $objPHPExcel->getSheet(0);
				$highestRow = $sheet->getHighestRow();
				$highestColumn = $sheet->getHighestColumn();

				for ($row = 2; $row <= $highestRow; $row++) {
					$producto = $this->db->get_where("almacen.productos", array("codigo" => trim($sheet->getCell("A" . $row)->getValue())))->result_array();
					if (count($producto) > 0) {
						$data = array("stockproveedor" => (float)$sheet->getCell("E" . $row)->getValue());
						$this->db->where("codproducto", $producto[0]["codproducto"]);
						$this->db->where("factor", 1);
						$estado = $this->db->update("almacen.productoubicacion", $data);
					}
				}

				unlink($archivo);
			}

			echo json_encode(
				array("estado" => (int)$estado, "mensaje" => "Operación registrada")
			);
		}
	}

	function phuyu_masprecios()
	{
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$almacenes = $this->db->query("select *from almacen.almacenes where estado = 1 AND codsucursal=" . $_SESSION["phuyu_codsucursal"])->result_array();
			$precio = (float)$this->request->producto->preciosinigv * (float)$this->request->tipocambio;
			$igv = (float)$this->request->producto->igv * (float)$this->request->tipocambio;
			$moneda = $this->request->moneda;
			$tipocambio = $this->request->tipocambio;
			$productosunidades = $this->db->query("select *from almacen.productoubicacion where codproducto=" . $this->request->producto->codproducto . " AND codalmacen=" . $_SESSION["phuyu_codalmacen"] . " AND codunidad=" . $this->request->producto->codunidad)->result_array();
			$this->load->view("almacen/productos/masprecios", compact("almacenes", "productosunidades", "precio", "igv", "moneda", "tipocambio"));
		}
	}

	function modificarprecios()
	{
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			$campos = ["preciocompra", "pventapublico", "pventamin", "pventacredito", "pventaxmayor", "preciocosto", "igvcompra", "fletecompra", "utilidad", "utilidadporc", "igvventa", "pigvventapublico", "pventaminutilidad", "pventaminutilidadporc", "igvminimo", "pventaminigv", "pventaxmayorutilidad", "pventaxmayorutilidadporc", "igvxmayor", "pventaxmayorigv", "pventacreditoutilidad", "pventacreditoutilidadporc", "igvcredito", "pventacreditoigv"];

			$valores = [$this->request->precios->preciocompra, $this->request->precios->pventapublico, $this->request->precios->pventamin, $this->request->precios->pventacredito, $this->request->precios->pventaxmayor, $this->request->precios->preciocosto, $this->request->precios->igvcompra, $this->request->precios->fletecompra, $this->request->precios->utilidad, $this->request->precios->utilidadporc, $this->request->precios->igvventa, $this->request->precios->pigvventapublico, $this->request->precios->pventaminutilidad, $this->request->precios->pventaminutilidadporc, $this->request->precios->igvminimo, $this->request->precios->pventaminigv, $this->request->precios->pventaxmayorutilidad, $this->request->precios->pventaxmayorutilidadporc, $this->request->precios->igvxmayor, $this->request->precios->pventaxmayorigv, $this->request->precios->pventacreditoutilidad, $this->request->precios->pventacreditoutilidadporc, $this->request->precios->igvcredito, $this->request->precios->pventacreditoigv];

			$f = ["codproducto", "codunidad", "codalmacen"];
			$v = [(int)$this->request->campos->codproducto, (int)$this->request->campos->codunidad, (int)$this->request->campos->codalmacen];
			$estado = $this->phuyu_model->phuyu_editar_1("almacen.productoubicacion", $campos, $valores, $f, $v);

			echo $estado;
		}
	}
}
