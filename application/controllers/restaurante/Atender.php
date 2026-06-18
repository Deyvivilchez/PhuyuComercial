<?php defined('BASEPATH') or exit('No direct script access allowed');
//href="http://localhost/sistemas/phuyu_comercial/phuyu/w/restaurante/atender"
class Atender extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('phuyu_model');
	}

	private function phuyu_datos_atender()
	{
		$ambientes = $this->db
			->select('*')
			->from('restaurante.ambientes')
			->where('codsucursal', $_SESSION['phuyu_codsucursal'])
			->where('estado', 1)
			->order_by('codambiente', 'ASC')
			->get()
			->result_array();

		$lineas = $this->db
			->select('*')
			->from('almacen.lineas')
			->where('estado', 1)
			->order_by('descripcion', 'ASC')
			->get()
			->result_array();

		$comprobantes = $this->db->query(
			'select distinct(ct.codcomprobantetipo) as codigo, ct.* from caja.comprobantetipos as ct
			inner join caja.comprobantes as c on(ct.codcomprobantetipo=c.codcomprobantetipo)
			where c.codsucursal=' . $_SESSION['phuyu_codsucursal'] . ' and ct.venta = 1 and c.estado=1',
		)->result_array();

		$conceptos = $this->db
			->select('*')
			->from('caja.conceptos')
			->where_in('codconcepto', [13, 15])
			->get()
			->result_array();

		$tipopagos = $this->db
			->select('*')
			->from('caja.tipopagos')
			->where('ingreso', 1)
			->where('estado', 1)
			->order_by('codtipopago', 'ASC')
			->get()
			->result_array();

		$perfil = '';
		if ($_SESSION['phuyu_codperfil'] > 3) {
			$perfil .= ' AND empleado.codpersona = ' . $_SESSION['phuyu_codempleado'];
		}

		$vendedores = $this->db->query(
			'select persona.codpersona,persona.razonsocial from public.personas as persona
			inner join public.empleados as empleado on(persona.codpersona=empleado.codpersona)
			where empleado.estado=1 ' . $perfil,
		)->result_array();

		$tipodocumentos = $this->db->query('select *from public.documentotipos where estado=1')->result_array();

		$departamentos = $this->db
			->distinct()
			->select('ubidepartamento, departamento')
			->from('public.ubigeo')
			->order_by('ubidepartamento', 'ASC')
			->get()
			->result_array();

		$sucursal = $this->db
			->select('codcomprobantetipo, seriecomprobante')
			->from('public.sucursales')
			->where('codsucursal', $_SESSION['phuyu_codsucursal'])
			->get()
			->result_array();

		return compact('ambientes', 'lineas', 'comprobantes', 'conceptos', 'tipopagos', 'vendedores', 'sucursal', 'tipodocumentos', 'departamentos');
	}

	public function index_original()
	{
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION['phuyu_usuario'])) {
				$this->load->view('restaurante/atender/index', $this->phuyu_datos_atender());
			} else {
				$this->load->view('phuyu/505');
			}
		} else {
			$this->load->view('phuyu/404');
		}
	}

	public function index()
	{
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION['phuyu_usuario'])) {
				$this->load->view('restaurante/atender/atender', $this->phuyu_datos_atender());
			} else {
				$this->load->view('phuyu/505');
			}
		} else {
			$this->load->view('phuyu/404');
		}
	}
	public function index_tushkuna()
	{
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION['phuyu_usuario'])) {
				$this->load->view('restaurante/atender/atender', $this->phuyu_datos_atender());
			}
			else {
				$this->load->view('phuyu/505');
			}
		} else {
			$this->load->view('phuyu/404');
		}
	}

	function guardar()
	{
		if ($this->input->is_ajax_request()) {
			$campos = ['codambiente', 'descripcion', 'nromesa', 'capacidad'];
			$this->request = json_decode(file_get_contents('php://input'));
			$valores = [$this->request->codambiente, 'MESA NRO ' . $this->request->nromesa, $this->request->nromesa, (int) $this->request->capacidad];

			if ($this->request->codregistro == '') {
				$estado = $this->phuyu_model->phuyu_guardar('restaurante.mesas', $campos, $valores);
			} else {
				$estado = $this->phuyu_model->phuyu_editar('restaurante.mesas', $campos, $valores, 'codmesa', $this->request->codregistro);
			}
			echo $estado;
		} else {
			$this->load->view('phuyu/404');
		}
	}
}
