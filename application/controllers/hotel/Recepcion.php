<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Recepcion extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model("Hotel_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$comprobantes = $this->db->query(
					"select distinct(ct.codcomprobantetipo) as codigo, ct.*
					from caja.comprobantetipos ct
					inner join caja.comprobantes c on(ct.codcomprobantetipo=c.codcomprobantetipo)
					where c.codsucursal=? and ct.venta=1 and c.estado=1",
					[(int)$_SESSION["phuyu_codsucursal"]]
				)->result_array();
				$tipopagos = $this->db->query("select * from caja.tipopagos where ingreso=1 and estado=1 order by codtipopago")->result_array();
				$vendedores = $this->db->query(
					"select persona.codpersona, persona.razonsocial
					from public.personas persona
					inner join public.empleados empleado on(persona.codpersona=empleado.codpersona)
					where empleado.estado=1 order by persona.razonsocial"
				)->result_array();
				$ambientes = $this->Hotel_model->ambientes();
				$caracteristicas = $this->Hotel_model->caracteristicas();
				$tipodocumentos = $this->db->query("select * from public.documentotipos where estado=1 order by coddocumentotipo")->result_array();
				$sucursal = $this->db->query(
					"select coalesce(codcomprobantetipo,12) as codcomprobantetipo, seriecomprobante
					from public.sucursales
					where codsucursal=?",
					[(int)$_SESSION["phuyu_codsucursal"]]
				)->row_array();
				$this->load->view("hotel/recepcion/index", compact("comprobantes", "tipopagos", "vendedores", "ambientes", "caracteristicas", "tipodocumentos", "sucursal"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function habitaciones(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents("php://input"));
			$codambiente = isset($this->request->codambiente) ? (int)$this->request->codambiente : 0;
			$codcaracteristica = isset($this->request->codcaracteristica) ? (int)$this->request->codcaracteristica : 0;
			echo json_encode($this->Hotel_model->habitaciones_resumen($codambiente, $codcaracteristica));
		}
	}

	public function estadia($codhabitacion){
		if ($this->input->is_ajax_request()) {
			$codhabitacion = (int)$codhabitacion;
			$estadia = $this->Hotel_model->estadia_activa_habitacion($codhabitacion);

			if (empty($estadia) || !isset($estadia["codestadia"])) {
				echo json_encode(["estado" => 0, "mensaje" => "ESTADIA NO ACTIVA"]);
				return;
			}

			$detalle = $this->Hotel_model->estadia_detalle((int)$estadia["codestadia"]);

			if (empty($detalle) || !is_array($detalle)) {
				$detalle = [];
			}

			if (!isset($detalle["estadia"]) || empty($detalle["estadia"])) {
				$detalle["estadia"] = $estadia;
			}

			if (isset($detalle["estadia"][0]) && is_array($detalle["estadia"][0])) {
				$detalle["estadia"] = $detalle["estadia"][0];
			}

			if (!isset($detalle["consumos"]) || !is_array($detalle["consumos"])) {
				$detalle["consumos"] = [];
			}

			if (!isset($detalle["estadia"]["codestadia"])) {
				echo json_encode(["estado" => 0, "mensaje" => "ESTADIA SIN CODIGO"]);
				return;
			}

			echo json_encode(["estado" => 1, "detalle" => $detalle]);
			return;
		}
	}

	public function clientes(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents("php://input"));
			$buscar = isset($this->request->buscar) ? trim($this->request->buscar) : "";
			$clientes = $this->db->query(
				"select codpersona, documento, razonsocial, direccion, coddocumentotipo
				from public.personas
				where estado=1
					and (upper(documento) like upper(?) or upper(razonsocial) like upper(?))
				order by razonsocial limit 20",
				["%".$buscar."%", "%".$buscar."%"]
			)->result_array();
			echo json_encode($clientes);
		}
	}
}
