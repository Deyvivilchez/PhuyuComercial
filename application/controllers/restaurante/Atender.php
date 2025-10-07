<?php defined('BASEPATH') or exit('No direct script access allowed');
//href="http://localhost/sistemas/phuyu_comercial/phuyu/w/restaurante/atender"
class Atender extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('phuyu_model');
	}

	public function index_original()
	{
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION['phuyu_usuario'])) {
				$ambientes = $this->db->query('select *from restaurante.ambientes where codsucursal=' . $_SESSION['phuyu_codsucursal'] . ' and estado=1 order by codambiente asc')->result_array();
				$lineas = $this->db->query('select *from almacen.lineas where estado=1 order by descripcion asc')->result_array();

				$comprobantes = $this->db->query('select distinct(ct.codcomprobantetipo) as codigo, ct.* from caja.comprobantetipos as ct inner join caja.comprobantes as c on(ct.codcomprobantetipo=c.codcomprobantetipo) where c.codsucursal=' . $_SESSION['phuyu_codsucursal'] . ' and c.codcomprobantetipo>=5 and c.estado=1')->result_array();
				$conceptos = $this->db->query('select *from caja.conceptos where codconcepto=13 or codconcepto=15')->result_array();
				$tipopagos = $this->db->query('select *from caja.tipopagos where ingreso=1 and estado=1 order by codtipopago')->result_array();
				$vendedores = $this->db->query('select persona.codpersona,persona.razonsocial 
					from public.personas as persona inner 
					join public.empleados as empleado on(persona.codpersona=empleado.codpersona) where empleado.estado=1 and empleado.codcargo=4')->result_array();
				$sucursal = $this->db->query('select codcomprobantetipo, seriecomprobante from public.sucursales where codsucursal=' . $_SESSION['phuyu_codsucursal'])->result_array();

				$this->load->view('restaurante/atender/index', compact('ambientes', 'lineas', 'comprobantes', 'conceptos', 'tipopagos', 'vendedores', 'sucursal'));
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
				// Ambientes
				$ambientes = $this->db->select('*')
				->from('restaurante.ambientes')->where('codsucursal', $_SESSION['phuyu_codsucursal'])
				->where('estado', 1)->order_by('codambiente', 'ASC')->get()->result_array();

				// Lineas
				$lineas = $this->db->select('*')->from('almacen.lineas')->where('estado', 1)
				->order_by('descripcion', 'ASC')->get()->result_array();

				// Comprobantes
				$comprobantes = $this->db->select('DISTINCT(ct.codcomprobantetipo) as codigo, ct.*')
				->from('caja.comprobantetipos ct')
				->join('caja.comprobantes c', 'ct.codcomprobantetipo = c.codcomprobantetipo')
				->where('c.codsucursal', $_SESSION['phuyu_codsucursal'])->where('c.codcomprobantetipo >=', 5)
				->where('c.estado', 1)->get()->result_array();
				// Conceptos
				$conceptos = $this->db
					->select('*')
					->from('caja.conceptos')
					->where_in('codconcepto', [13, 15])
					->get()
					->result_array();

				// Tipo Pagos
				$tipopagos = $this->db->select('*')->from('caja.tipopagos')->where('ingreso', 1)->where('estado', 1)->order_by('codtipopago', 'ASC')->get()->result_array();

				// Vendedores
				$perfil = '';
				if($_SESSION["phuyu_codperfil"] > 3){
					$perfil .= ' AND empleado.codpersona = '.$_SESSION["phuyu_codempleado"];
				}
				$vendedores = $this->db->query("select persona.codpersona,persona.razonsocial from public.personas as persona inner join public.empleados as 
				empleado on(persona.codpersona=empleado.codpersona) where empleado.estado=1 ".$perfil."")->result_array();
				// $vendedores = $this->db->select('persona.codpersona, persona.razonsocial')
				// ->from('public.personas persona')->join('public.empleados empleado', 'persona.codpersona = empleado.codpersona')
				// ->where('empleado.estado', 1)
				// ->where('empleado.codcargo', 4)->get()->result_array();

				// Sucursal
				$sucursal = $this->db->select('codcomprobantetipo, seriecomprobante')->from('public.sucursales')->where('codsucursal', $_SESSION['phuyu_codsucursal'])->get()->result_array();
				 $data = [
					'ambientes' => $ambientes,
					'lineas' => $lineas,
					'comprobantes' => $comprobantes,
					'conceptos' => $conceptos,
					'tipopagos' => $tipopagos,
					'vendedores' => $vendedores,
					'sucursal' => $sucursal
				];
    
				$this->load->view('restaurante/atender/atender', $data );
			}
			else {
				$this->load->view('phuyu/505');
			}
		} else {
			$this->load->view('phuyu/404', compact('ambientes', 'lineas', 'comprobantes', 'conceptos', 'tipopagos', 'vendedores', 'sucursal'));
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
