<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Consumos extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model("phuyu_model");
		$this->load->model("Hotel_model");
	}

	public function productos(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents("php://input"));
			$buscar = isset($this->request->buscar) ? $this->request->buscar : "";
			$productos = $this->db->query(
				"select p.codproducto, p.descripcion, p.controlstock, p.controlarseries, p.afectoigvventa, p.afectoicbper, p.calcular,
					pu.codunidad, u.descripcion as unidad, pu.pventapublico as precio,
					coalesce(pub.stockactualconvertido,0) as stock
				from almacen.productos p
				inner join almacen.productounidades pu on(pu.codproducto=p.codproducto and pu.estado=1)
				inner join almacen.unidades u on(u.codunidad=pu.codunidad)
				left join almacen.productoubicacion pub on(pub.codproducto=p.codproducto and pub.codunidad=pu.codunidad and pub.codalmacen=? and pub.estado=1)
				where p.estado=1 and p.paraventa=1 and upper(p.descripcion) like upper(?)
				order by p.descripcion limit 30",
				[(int)$_SESSION["phuyu_codalmacen"], "%".$buscar."%"]
			)->result_array();
			echo json_encode($productos);
		}
	}

	public function productos_select(){
		if ($this->input->is_ajax_request()) {
			$buscar = $this->input->get("q") ?: "";
			$productos = $this->db->query(
				"select p.codproducto, p.descripcion, p.controlstock, p.controlarseries, p.afectoigvventa, p.afectoicbper, p.calcular,
					pu.codunidad, u.descripcion as unidad, pu.pventapublico as precio,
					coalesce(pub.stockactualconvertido,0) as stock
				from almacen.productos p
				inner join almacen.productounidades pu on(pu.codproducto=p.codproducto and pu.estado=1)
				inner join almacen.unidades u on(u.codunidad=pu.codunidad)
				left join almacen.productoubicacion pub on(pub.codproducto=p.codproducto and pub.codunidad=pu.codunidad and pub.codalmacen=? and pub.estado=1)
				where p.estado=1 and p.paraventa=1
					and (upper(p.descripcion) like upper(?) or upper(coalesce(p.codigo,'')) like upper(?))
				order by p.descripcion limit 30",
				[(int)$_SESSION["phuyu_codalmacen"], "%".$buscar."%", "%".$buscar."%"]
			)->result_array();

			$resultados = [];
			foreach ($productos as $producto) {
				$producto["id"] = $producto["codproducto"]."|".$producto["codunidad"];
				$controlaStockSistema = isset($_SESSION["phuyu_stockalmacen"]) ? (int)$_SESSION["phuyu_stockalmacen"] : 0;
				$textoStock = ($controlaStockSistema == 1 && (int)$producto["controlstock"] == 1) ? " - Stock ".number_format((double)$producto["stock"], 2) : "";
				$producto["text"] = $producto["descripcion"]." - S/. ".number_format((double)$producto["precio"], 2).$textoStock;
				$resultados[] = $producto;
			}

			echo json_encode(["results" => $resultados]);
		}
	}

	public function series(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents("php://input"));
			$codproducto = (int)$this->request->codproducto;
			$series = $this->db->query(
				"select id_serie, serie_codigo
				from almacen.series
				where codproducto=?
				  and codalmacen=?
				  and codsucursal=?
				  and estado='EN_ALMACEN'
				order by serie_codigo",
				[$codproducto, (int)$_SESSION["phuyu_codalmacen"], (int)$_SESSION["phuyu_codsucursal"]]
			)->result_array();
			echo json_encode($series);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function guardar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents("php://input"));
			$codconsumo = isset($this->request->codconsumo) ? (int)$this->request->codconsumo : 0;
			$codestadia = isset($this->request->codestadia) ? (int)$this->request->codestadia : 0;
			$codhabitacion = isset($this->request->codhabitacion) ? (int)$this->request->codhabitacion : 0;
			$codproducto = isset($this->request->codproducto) ? (int)$this->request->codproducto : 0;
			$codunidad = isset($this->request->codunidad) ? (int)$this->request->codunidad : 0;

			if ($codestadia == 0 && $codhabitacion > 0) {
				$activa = $this->Hotel_model->estadia_activa_habitacion($codhabitacion);
				$codestadia = empty($activa) ? 0 : (int)$activa["codestadia"];
				$this->request->codestadia = $codestadia;
			}
			$estadia = $this->db->query(
				"select * from hotel.estadias
				where codestadia=? and codsucursal=? and situacion=1 and estado=1",
				[$codestadia, (int)$_SESSION["phuyu_codsucursal"]]
			)->row_array();
			if (empty($estadia)) {
				echo json_encode(["estado" => 0, "mensaje" => "ESTADIA NO ACTIVA"]);
				return;
			}

			if ($codhabitacion == 0) {
				$habitacionActiva = $this->db->query(
					"select eh.codhabitacion
					from hotel.estadia_habitaciones eh
					inner join hotel.habitaciones h on(h.codhabitacion=eh.codhabitacion)
					where eh.codestadia=? and eh.estado=1 and h.codsucursal=? and h.estado=1
					order by eh.codhabitacion desc
					limit 1",
					[$codestadia, (int)$_SESSION["phuyu_codsucursal"]]
				)->row_array();
				$codhabitacion = empty($habitacionActiva) ? 0 : (int)$habitacionActiva["codhabitacion"];
				$this->request->codhabitacion = $codhabitacion;
			}

			if ($codproducto <= 0) {
				echo json_encode(["estado" => 0, "mensaje" => "SELECCIONE PRODUCTO"]);
				return;
			}

			if ($codunidad <= 0) {
				$unidadProducto = $this->db->query(
					"select codunidad
					from almacen.productounidades
					where codproducto=? and estado=1
					order by case when coalesce(pventapublico,0) > 0 then 0 else 1 end, codunidad
					limit 1",
					[$codproducto]
				)->row_array();
				$codunidad = empty($unidadProducto) ? 0 : (int)$unidadProducto["codunidad"];
				$this->request->codunidad = $codunidad;
			}

			if ($codunidad <= 0) {
				echo json_encode(["estado" => 0, "mensaje" => "PRODUCTO SIN UNIDAD DE VENTA"]);
				return;
			}

			$habitacion = $this->db->query(
				"select eh.codhabitacion, h.numero
				from hotel.estadia_habitaciones eh
				inner join hotel.habitaciones h on(h.codhabitacion=eh.codhabitacion)
				where eh.codestadia=? and eh.codhabitacion=? and eh.estado=1 and h.codsucursal=? and h.estado=1",
				[(int)$this->request->codestadia, (int)$this->request->codhabitacion, (int)$_SESSION["phuyu_codsucursal"]]
			)->row_array();
			if (empty($habitacion)) {
				echo json_encode(["estado" => 0, "mensaje" => "LA HABITACION NO PERTENECE A LA ESTADIA"]);
				return;
			}
			$producto = $this->db->query(
				"select p.codproducto, p.descripcion, p.controlstock, p.controlarseries, p.afectoigvventa, p.afectoicbper,
					pu.codunidad, u.descripcion as unidad, coalesce(pub.stockactualconvertido,0) as stock
				from almacen.productos p
				inner join almacen.productounidades pu on(pu.codproducto=p.codproducto and pu.codunidad=? and pu.estado=1)
				inner join almacen.unidades u on(u.codunidad=pu.codunidad)
				left join almacen.productoubicacion pub on(pub.codproducto=p.codproducto and pub.codunidad=pu.codunidad and pub.codalmacen=? and pub.estado=1)
				where p.codproducto=? and p.estado=1 and p.paraventa=1
				limit 1",
				[$codunidad, (int)$_SESSION["phuyu_codalmacen"], $codproducto]
			)->row_array();
			if (empty($producto)) {
				echo json_encode(["estado" => 0, "mensaje" => "PRODUCTO NO DISPONIBLE PARA VENTA"]);
				return;
			}
			$item = $this->db->query("select coalesce(max(item),0)+1 as item from hotel.consumos_habitacion where codestadia=?", [(int)$this->request->codestadia])->row_array();
			$cantidad = (double)$this->request->cantidad;
			$precio = (double)$this->request->preciounitario;
			if ($cantidad <= 0 || $precio < 0) {
				echo json_encode(["estado" => 0, "mensaje" => "CANTIDAD O PRECIO INVALIDO"]);
				return;
			}
			$controlaStockSistema = isset($_SESSION["phuyu_stockalmacen"]) ? (int)$_SESSION["phuyu_stockalmacen"] : 0;
			if ($controlaStockSistema == 1 && (int)$producto["controlstock"] == 1) {
				$stockDisponible = (double)$producto["stock"];
				if ($stockDisponible < $cantidad) {
					echo json_encode(["estado" => 0, "mensaje" => "STOCK INSUFICIENTE. DISPONIBLE: ".number_format($stockDisponible, 2)]);
					return;
				}
			}
			$controlaStockSistema = isset($_SESSION["phuyu_stockalmacen"]) ? (int)$_SESSION["phuyu_stockalmacen"] : 0;
			$idSerie = isset($this->request->id_serie) ? (int)$this->request->id_serie : 0;
			if ($controlaStockSistema == 1 && (int)$producto["controlarseries"] == 1) {
				if ($idSerie == 0) {
					echo json_encode(["estado" => 0, "mensaje" => "SELECCIONE SERIE DEL PRODUCTO"]);
					return;
				}
				if ($cantidad != 1) {
					echo json_encode(["estado" => 0, "mensaje" => "LOS PRODUCTOS CON SERIE SE REGISTRAN CON CANTIDAD 1"]);
					return;
				}
				$serie = $this->db->query(
					"select id_serie, serie_codigo
					from almacen.series
					where id_serie=? and codproducto=? and codalmacen=? and codsucursal=? and estado='EN_ALMACEN'
					limit 1",
					[$idSerie, (int)$producto["codproducto"], (int)$_SESSION["phuyu_codalmacen"], (int)$_SESSION["phuyu_codsucursal"]]
				)->row_array();
				if (empty($serie)) {
					echo json_encode(["estado" => 0, "mensaje" => "SERIE NO DISPONIBLE EN ALMACEN"]);
					return;
				}
				$serieUsada = $this->db->query(
					"select codconsumo
					from hotel.consumos_habitacion
					where id_serie=? and situacion=1 and estado=1 and codconsumo<>?
					limit 1",
					[$idSerie, $codconsumo]
				)->row_array();
				if (!empty($serieUsada)) {
					echo json_encode(["estado" => 0, "mensaje" => "LA SERIE YA ESTA CARGADA EN OTRO CONSUMO PENDIENTE"]);
					return;
				}
			}else{
				$idSerie = 0;
			}
			$itemKardex = $this->Hotel_model->armar_item_kardex(
				(int)$producto["codproducto"], (int)$producto["codunidad"], $producto["descripcion"], $producto["unidad"],
				$cantidad, $precio, $this->request->descripcion,
				(int)$producto["afectoigvventa"], (int)$producto["afectoicbper"], ($controlaStockSistema == 1 ? (int)$producto["controlstock"] : 0)
			);
			$campos = ["codhabitacion","codproducto","codunidad","id_serie","cantidad","preciounitario","preciosinigv","preciobruto","preciorefunitario","codafectacionigv","igv","valorventa","subtotal","descripcion","situacion"];
			$valores = [
				(int)$this->request->codhabitacion, (int)$producto["codproducto"], (int)$producto["codunidad"], $idSerie,
				$cantidad, $precio, (double)$itemKardex->preciosinigv, (double)$itemKardex->preciobruto,
				(double)$itemKardex->preciorefunitario, $itemKardex->codafectacionigv, (double)$itemKardex->igv,
				(double)$itemKardex->valorventa, (double)$itemKardex->subtotal, $this->request->descripcion, 1
			];
			if ($codconsumo > 0) {
				$estado = $this->phuyu_model->phuyu_editar("hotel.consumos_habitacion", $campos, $valores, "codconsumo", $codconsumo);
			}else{
				array_unshift($campos, "codestadia", "item");
				array_unshift($valores, (int)$this->request->codestadia, (int)$item["item"]);
				$estado = $this->phuyu_model->phuyu_guardar("hotel.consumos_habitacion", $campos, $valores);
			}
			echo json_encode(["estado" => $estado]);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function anular(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents("php://input"));
			$consumo = $this->db->query(
				"select * from hotel.consumos_habitacion where codconsumo=? and estado=1 limit 1",
				[(int)$this->request->codconsumo]
			)->row_array();
			if (empty($consumo) || (int)$consumo["situacion"] != 1 || (int)$consumo["codkardex"] > 0) {
				echo json_encode(["estado" => 0, "mensaje" => "NO SE PUEDE ANULAR UN CONSUMO FACTURADO O YA ANULADO"]);
				return;
			}
			$estado = $this->phuyu_model->phuyu_editar("hotel.consumos_habitacion", ["situacion"], [3], "codconsumo", (int)$this->request->codconsumo);
			echo json_encode(["estado" => $estado]);
		}
	}
}
