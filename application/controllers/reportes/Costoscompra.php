<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Costoscompra extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model("phuyu_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_codusuario"])) {
				$almacenes = $this->db->query("select *from almacen.almacenes where estado=1 order by descripcion")->result_array();
				$lineas = $this->db->query("select *from almacen.lineas where estado=1 order by descripcion")->result_array();
				$this->load->view("reportes/costoscompra/index", compact("almacenes", "lineas"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	private function filtros($request, &$params){
		$where = " where k.codmovimientotipo=2 and k.estado=1 and kd.estado=1 ";

		if (!empty($request->fechadesde)) {
			$where .= " and k.fechacomprobante >= ? ";
			$params[] = $request->fechadesde;
		}
		if (!empty($request->fechahasta)) {
			$where .= " and k.fechacomprobante <= ? ";
			$params[] = $request->fechahasta;
		}
		if (isset($request->codalmacen) && (int)$request->codalmacen > 0) {
			$where .= " and k.codalmacen = ? ";
			$params[] = (int)$request->codalmacen;
		}
		if (isset($request->codlinea) && (int)$request->codlinea > 0) {
			$where .= " and p.codlinea = ? ";
			$params[] = (int)$request->codlinea;
		}
		if (isset($request->codproducto) && (int)$request->codproducto > 0) {
			$where .= " and kd.codproducto = ? ";
			$params[] = (int)$request->codproducto;
		}
		if (isset($request->codpersona) && (int)$request->codpersona > 0) {
			$where .= " and k.codpersona = ? ";
			$params[] = (int)$request->codpersona;
		}

		return $where;
	}

	private function base_sql($where){
		return "
			with compras as (
				select
					k.codkardex,
					k.fechacomprobante,
					k.fechakardex,
					k.codalmacen,
					k.codsucursal,
					k.codpersona,
					per.razonsocial as proveedor,
					k.codmoneda,
					k.tipocambio,
					k.codcomprobantetipo,
					k.seriecomprobante,
					k.nrocomprobante,
					k.estado,
					kd.item,
					kd.codproducto,
					p.codigo,
					p.descripcion as producto,
					p.codlinea,
					li.descripcion as linea,
					kd.codunidad,
					u.descripcion as unidad,
					kd.cantidad,
					kd.preciounitario,
					kd.preciocompra,
					kd.preciocosto,
					kd.subtotal,
					kd.igv,
					kd.icbper
				from kardex.kardex k
				inner join kardex.kardexdetalle kd on(kd.codkardex=k.codkardex)
				inner join almacen.productos p on(p.codproducto=kd.codproducto)
				inner join almacen.unidades u on(u.codunidad=kd.codunidad)
				left join almacen.lineas li on(li.codlinea=p.codlinea)
				left join public.personas per on(per.codpersona=k.codpersona)
				" . $where . "
			)
		";
	}

	public function resumen(){
		if (!$this->input->is_ajax_request()) {
			$this->load->view("phuyu/404"); return;
		}

		$request = json_decode(file_get_contents('php://input'));
		$params = array();
		$where = $this->filtros($request, $params);
		$limit = isset($request->limit) ? (int)$request->limit : 50;
		if ($limit <= 0 || $limit > 500) { $limit = 50; }

		$sql = $this->base_sql($where) . "
			, ordenado as (
				select c.*,
				lag(c.preciounitario) over(partition by c.codproducto, c.codunidad order by c.fechacomprobante, c.codkardex, c.item) as precio_anterior,
				row_number() over(partition by c.codproducto, c.codunidad order by c.fechacomprobante desc, c.codkardex desc, c.item desc) as rn_desc,
				count(*) over(partition by c.codproducto, c.codunidad) as compras_producto
				from compras c
			)
			select
				codproducto, codigo, producto, codunidad, unidad, linea,
				fechacomprobante, proveedor, cantidad,
				round(preciounitario,4) as ultimo_precio,
				round(precio_anterior,4) as precio_anterior,
				round((preciounitario - precio_anterior),4) as variacion,
				case when precio_anterior > 0 then round(((preciounitario - precio_anterior) / precio_anterior) * 100,2) else null end as variacion_porc,
				compras_producto,
				seriecomprobante, nrocomprobante, codkardex
			from ordenado
			where rn_desc=1
			order by abs(coalesce((preciounitario - precio_anterior),0)) desc, producto
			limit " . $limit;

		$lista = $this->db->query($sql, $params)->result_array();

		$kpi_sql = $this->base_sql($where) . "
			select
				count(distinct codproducto) as productos,
				count(*) as compras,
				round(avg(preciounitario),4) as precio_promedio,
				round(min(preciounitario),4) as precio_minimo,
				round(max(preciounitario),4) as precio_maximo
			from compras
		";
		$kpi = $this->db->query($kpi_sql, $params)->row_array();

		echo json_encode(array("lista" => $lista, "kpi" => $kpi));
	}

	public function historial(){
		if (!$this->input->is_ajax_request()) {
			$this->load->view("phuyu/404"); return;
		}

		$request = json_decode(file_get_contents('php://input'));
		$params = array();
		$where = $this->filtros($request, $params);

		$sql = $this->base_sql($where) . "
			, ordenado as (
				select c.*,
				lag(c.preciounitario) over(partition by c.codproducto, c.codunidad order by c.fechacomprobante, c.codkardex, c.item) as precio_anterior
				from compras c
			)
			select
				codkardex, item, fechacomprobante, proveedor, codproducto, codigo, producto,
				codunidad, unidad, cantidad,
				round(preciounitario,4) as precio,
				round(preciocompra,4) as preciocompra,
				round(preciocosto,4) as preciocosto,
				round(subtotal,2) as subtotal,
				round(precio_anterior,4) as precio_anterior,
				round((preciounitario - precio_anterior),4) as variacion,
				case when precio_anterior > 0 then round(((preciounitario - precio_anterior) / precio_anterior) * 100,2) else null end as variacion_porc,
				seriecomprobante, nrocomprobante
			from ordenado
			order by fechacomprobante desc, codkardex desc, item desc
			limit 500
		";

		echo json_encode($this->db->query($sql, $params)->result_array());
	}

	public function ultimo_precio(){
		if (!$this->input->is_ajax_request()) {
			$this->load->view("phuyu/404"); return;
		}

		$request = json_decode(file_get_contents('php://input'));
		$codproducto = isset($request->codproducto) ? (int)$request->codproducto : 0;
		$codunidad = isset($request->codunidad) ? (int)$request->codunidad : 0;
		if ($codproducto <= 0 || $codunidad <= 0) {
			echo json_encode(null); return;
		}

		$sql = "
			select k.fechacomprobante, k.codkardex, k.codpersona, per.razonsocial as proveedor,
			k.seriecomprobante, k.nrocomprobante, kd.codproducto, kd.codunidad,
			round(kd.preciounitario,4) as precio, round(kd.preciocompra,4) as preciocompra, round(kd.preciocosto,4) as preciocosto
			from kardex.kardex k
			inner join kardex.kardexdetalle kd on(kd.codkardex=k.codkardex)
			left join public.personas per on(per.codpersona=k.codpersona)
			where k.codmovimientotipo=2 and k.estado=1 and kd.estado=1
			and kd.codproducto=? and kd.codunidad=?
			order by k.fechacomprobante desc, k.codkardex desc, kd.item desc
			limit 1
		";
		echo json_encode($this->db->query($sql, array($codproducto, $codunidad))->row_array());
	}
}
