<?php defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH."third_party/phuyu_sunat/curl.php");
require_once(APPPATH."third_party/phuyu_sunat/sunat.php");
require_once(APPPATH."third_party/phuyu_sunat/htmlparser.php");
require_once(APPPATH."third_party/phuyu_sunat/Company.php");

require_once(APPPATH."third_party/phuyu_reniec/curl.php");
require_once(APPPATH."third_party/phuyu_reniec/essalud.php");
require_once(APPPATH."third_party/phuyu_reniec/mintra.php");
require_once(APPPATH."third_party/phuyu_reniec/reniec.php");

class Web extends CI_Controller {

	public function index(){
		// session_destroy(); 
		$this->load->view("phuyu/404"); // $this->load->view("phuyu/404");
	}

	public function phuyu_ruc($ruc){
		$ch = curl_init("https://e-factura.tuscomprobantes.pe/wsconsulta/ruc/".$ruc);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        $response = curl_exec($ch);
        curl_close($ch);

        echo $response;
	}

	public function phuyu_dni($dni){
		$this->reniec = new \Reniec\Reniec(); 
		$this->essalud = new \EsSalud\EsSalud();
		$this->mintra = new \MinTra\mintra();

		$arrContextOptions=array(
            "ssl"=>array(
                "verify_peer"=>false,
                "verify_peer_name"=>false,
            ),
        );
        //$data = file_get_contents("https://dniruc.apisperu.com/api/v1/dni/".$dni."?token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6InJlZ3Vsb21lcmlub0BnbWFpbC5jb20ifQ.ljGDDFI4nRzxEEa8_JBRpSZEHCkxasQcGTMPcstNdOM", false, stream_context_create($arrContextOptions));
        $data = file_get_contents("https://cuenta.contasiscorp.com/api/reniec-ruc/".$dni,false, stream_context_create($arrContextOptions));
        $data = json_decode($data,true);
        //print_r($data['data']);exit;

        if($data['success']){
          echo json_encode(['success' => true, 'result' => $data['data']]);exit;
        }
	}

	public function phuyu_buscarsocio($documento){
		if (isset($documento)) {
			$existe = $this->db->query("select personas.*,socios.estado as estado from public.socios as socios inner join public.personas as personas on (socios.codpersona=personas.codpersona) where personas.documento='".$documento."'")->result_array();
			echo json_encode($existe);
		}
	}

	public function phuyu($desarrollador){
		if (isset($desarrollador)) {
			$existe_empresa = $this->db->query("select *from public.empresas")->result_array();
			if ($desarrollador=="carlosyrigoin" && count($existe_empresa)==1) {
				$this->db->trans_begin();
				// CREAMOS EL USUARIO DE SOPORTE WEB phuyu //
				$data = array(
					"codpersona" => 0, "codubigeo" => 0, "coddocumentotipo" => 1, "documento" => "-", "razonsocial" => "USUARIO SOPORTE", "nombrecomercial" => "USUARIO SOPORTE",
					"direccion" => "TARAPOTO", "email" => "soporte@gmail.com", "telefono" => "964777055", "sexo" => "M"
				);
				$estado = $this->db->insert("public.personas", $data);

				$data = array("codpersona" => 0, "codarea" => 1, "codcargo" => 1, "tipoempleado" => 2);
				$estado = $this->db->insert("public.empleados", $data);

				$data = array("codempleado" => 0, "codperfil" => 1, "usuario" => "soporte", "clave" => "123");
				$estado = $this->db->insert("seguridad.usuarios", $data);

				// CREAMOS LOS PERMISOS INICIALES A WEB phuyu AL USUARIO SOPORTE //
				$data = array("codmodulo" => 88, "codperfil" => 1);
				$estado = $this->db->insert("seguridad.moduloperfiles", $data);

				// CREAMOS LA EMPRESA EN WEB phuyu //
				$data = array(
					"codubigeo" => 0, "tipopersona" => 2, "coddocumentotipo" => 4, "documento" => "10334343445", "razonsocial" => "NOMBRE DE LA EMPRESA", "nombrecomercial" => "NUEVA EMPRESA", "direccion" => "-", "email" => "empresa@gmail.com"
				);
				$estado = $this->db->insert("public.personas", $data);

				$data = array("codpersona" => 1);
				$estado = $this->db->insert("public.empresas", $data);

				$data = array("codempresa" => 1, "usuariosol" => "USUARIO1", "clavesol" => "CLAVE1");
				$estado = $this->db->insert("public.webservice", $data);

				$data = array( "codpersona" => 1, "codsociotipo" => 3);
				$estado = $this->db->insert("public.socios", $data);

				// CREAMOS LA SUCURSAL, CAJA Y ALMACEN POR DEFECTO EN WEB phuyu //
				$data = array("codempresa" => 1, "descripcion" => "SUCURSAL PRINCIPAL", "direccion" => "-");
				$estado = $this->db->insert("public.sucursales", $data);

				$data = array("codsucursal" => 1, "descripcion" => "CAJA PRINCIPAL", "direccion" => "-");
				$estado = $this->db->insert("caja.cajas", $data);

				$data = array("codsucursal" => 1, "descripcion" => "ALMACEN PRINCIPAL", "direccion" => "-");
				$estado = $this->db->insert("almacen.almacenes", $data);

				// CREAMOS EL SOCIO CLIENTES Y PROVEEDORES VARIOS //
				$data = array(
					"coddocumentotipo" => 1, "documento" => "00000000", "razonsocial" => "VARIOS", "nombrecomercial" => "VARIOS", "direccion" => "-"
				);
				$estado = $this->db->insert("public.personas", $data);

				$data = array( "codpersona" => 2, "codsociotipo" => 1);
				$estado = $this->db->insert("public.socios", $data);

				// CREAMOS EL EMPLEADO ADMINISTRADOR WEB phuyu //
				$data = array(
					"codubigeo" => 0, "coddocumentotipo" => 2, "documento" => "94949494", "razonsocial" => "ADMINISTRADOR", "nombrecomercial" => "ADMINISTRADOR DE LA EMPRESA", "direccion" => "-", "email" => "administrador@gmail.com", "telefono" => "964777055", "sexo" => "M"
				);
				$estado = $this->db->insert("public.personas", $data);

				$data = array("codpersona" => 3, "codarea" => 1, "codcargo" => 1);
				$estado = $this->db->insert("public.empleados", $data);

				$data = array("codempleado" => 3, "codperfil" => 2, "usuario" => "administrador", "clave" => "123");
				$estado = $this->db->insert("seguridad.usuarios", $data);

				// ASIGNAMOS LAS SUCURSALES A LOS USUARIOS WEB phuyu //
				$data = array("codsucursal" => 1, "codusuario" => 1);
				$estado = $this->db->insert("seguridad.sucursalusuarios", $data);

				$data = array("codsucursal" => 1, "codusuario" => 2);
				$estado = $this->db->insert("seguridad.sucursalusuarios", $data);

				$data = array("codsucursal" => 1);
				$estado = $this->db->update("public.empleados", $data);

				if ($this->db->trans_status() === FALSE){
					$this->db->trans_rollback();
					echo "OCURRIO UN ERROR AL INICIAR phuyu PERÚ";
				}else{
					$this->db->trans_commit();
					echo "<br> <br> <h1 align='center'>WEB phuyu BIENVENIDO - TODO SALIO BIEN</h1>";
				}
			}
		}
	}
}