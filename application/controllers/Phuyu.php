<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Phuyu extends CI_Controller
{

	function __construct(){
		parent::__construct();
		$this->load->model("phuyu_model");
	}

	public function index(){
		if (isset($_SESSION["phuyu_usuario"])) {
			$info = $this->db->query("select sucursal.* from public.sucursales as sucursal inner join seguridad.sucursalusuarios as sucursalusuario on(sucursal.codsucursal=sucursalusuario.codsucursal) where sucursalusuario.codusuario=".$_SESSION["phuyu_codusuario"]." and sucursal.estado=1 order by sucursal.codsucursal")->result_array();
			$this->load->view("phuyu/administrar",compact("info"));
		}else{
			$this->load->view("phuyu/login");
		}
	}

	public function phuyu_sucursal(){
		if($this->input->is_ajax_request()){
			$this->request = json_decode(file_get_contents('php://input'));
			$info["almacenes"] = $this->db->query("select *from almacen.almacenes where estado=1 and codsucursal=".$this->request->codsucursal)->result_array();
			$info["cajas"] = $this->db->query("select *from caja.cajas where estado=1 and codsucursal=".$this->request->codsucursal)->result_array();

			echo json_encode($info);
		}
	}

	public function phuyu_login(){
		if ($this->input->is_ajax_request()) {
			// $this->request = json_decode(file_get_contents('php://input'));
			$estado = $this->phuyu_model->phuyu_login($this->input->post("usuario"),$this->input->post("clave"));
	        echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function phuyu_web(){
		if (isset($_SESSION["phuyu_codusuario"])){
			$this->request = json_decode(file_get_contents('php://input'));
			//echo $this->request->sistema;exit;
			$estado = $this->phuyu_model->phuyu_web($this->request->codsucursal,$this->request->codalmacen,$this->request->codcaja);
	        echo $estado;
        }else{
            $this->load->view("phuyu/404");
        }
	}

	public function phuyu_perfil(){
		if (isset($_SESSION["phuyu_codusuario"])){
			$usuario = $this->db->query("select *from seguridad.usuarios where codusuario=".$_SESSION["phuyu_codusuario"])->result_array();
			$persona = $this->db->query("select *from public.personas where codpersona=".$usuario[0]["codempleado"])->result_array();
			$this->load->view("phuyu/perfil",compact("usuario","persona"));
        }else{
            $this->load->view("phuyu/404");
        }
	}

	public function w($phuyu_modulo = "", $phuyu_submodulo = ""){
		if (isset($_SESSION["phuyu_codsucursal"])){
			if(!isset($_SESSION["phuyu_codsistema"])){
				$_SESSION["phuyu_codsistema"] = 1;
			}
			$sistemas = $this->db->query("select *from sistemas where estado = 1")->result_array();
			$phuyu_modulos = $this->phuyu_model->phuyu_modulos();
			// FALTA CONSULTAR SI TIENE PERMISO A ESTE MODULO //
            $this->load->view("phuyu/index",compact("phuyu_modulos","sistemas"));
        }else{
            $this->load->view("phuyu/404");
        }
	}

	public function cambiarsistema($codsistema){
		if (isset($_SESSION["phuyu_codsucursal"])){
			$_SESSION["phuyu_codsistema"] = $codsistema;
			echo 1;
        }else{
            $this->load->view("phuyu/404");
        }
	}

	public function generarelectronicos(){
		$estado = 1;
		$fecha = date('Y-m-d');
		$this->db->trans_begin();
		//FACTURAS ANULADAS
		$codresumentipo = 1;
		$fechas_resumen = $this->db->query("select distinct(k.fechacomprobante) as fechacomprobante from kardex.kardexanulados as ka inner join kardex.kardex as k on(ka.codkardex=k.codkardex) inner join sunat.kardexsunat as ks on(k.codkardex=ks.codkardex) where ka.fechaanulacion<='".$fecha."' and k.codmovimientotipo=20 and k.codcomprobantetipo=10 and (ks.estado=1 or ks.estado=2) and k.codkardex not in (select codkardex from sunat.kardexsunatanulados where fechaanulacion<='".$fecha."') ")->result_array();
		
		if (count($fechas_resumen)>0) {
			foreach ($fechas_resumen as $key => $value) {
				$lista = $this->db->query("select ka.codkardex,ka.observaciones from kardex.kardexanulados as ka inner join kardex.kardex as k on(ka.codkardex=k.codkardex) inner join sunat.kardexsunat as ks on(k.codkardex=ks.codkardex) where k.fechacomprobante='".$value["fechacomprobante"]."' and ka.fechaanulacion<='".$fecha."' and k.codmovimientotipo=20 and k.codcomprobantetipo=10 and (ks.estado=1 or ks.estado=2) and k.codkardex not in (select codkardex from sunat.kardexsunatanulados where fechaanulacion<='".$fecha."') ")->result_array();

				$fecharesumen = $value["fechacomprobante"];

				$f = explode("-",$fecharesumen); $periodo = $f[0].$f[1].$f[2];

				$resumenes = $this->db->query("select count(*) as cantidad from sunat.resumenes where periodo='".$periodo."' and codresumentipo=".$codresumentipo." and codempresa=".$_SESSION["phuyu_codempresa"])->result_array();

				$nrocorrelativo = $resumenes[0]["cantidad"] + 1;
				$oficial = $this->db->query("select oficial from sunat.resumentipos where codresumentipo=".$codresumentipo)->result_array();
				$xml = $_SESSION["phuyu_ruc"]."-".$oficial[0]["oficial"]."-".$periodo."-".$nrocorrelativo;

				$campos = ["codresumentipo","periodo","nrocorrelativo","codempresa","codsucursal","codusuario","nombre_xml","fecharesumen"];
				$valores = [
					(int)$codresumentipo,$periodo,
					(int)$nrocorrelativo,
					(int)$_SESSION["phuyu_codempresa"],
					(int)$_SESSION["phuyu_codsucursal"],
					(int)$_SESSION["phuyu_codusuario"],
					$xml,$fecharesumen
				];
				$estado = $this->phuyu_model->phuyu_guardar("sunat.resumenes", $campos, $valores);

				foreach ($lista as $val) {
					$campos =["codkardex","codresumentipo","nrocorrelativo","periodo","codempresa","codsucursal","fechaanulacion","fechareferencia","motivobaja"];
					$valores = [
						(int)$val["codkardex"],
						(int)$codresumentipo,
						(int)$nrocorrelativo,
						$periodo,
						(int)$_SESSION["phuyu_codempresa"],
						(int)$_SESSION["phuyu_codsucursal"],
						$fecha,$fecharesumen,
						$val["observaciones"]
					];
					$estado = $this->phuyu_model->phuyu_guardar("sunat.kardexsunatanulados", $campos, $valores);
				}
			}
		}

		//BOLETAS GENERADAS
		$codresumentipo = 3;
		$fechas_resumen = $this->db->query("select distinct(kardex.fechacomprobante) as fechacomprobante from kardex.kardex as kardex inner join sunat.kardexsunat as kardexs on(kardex.codkardex=kardexs.codkardex) where kardexs.fechacreado<='".$fecha."' and kardex.codmovimientotipo=20 and kardex.codcomprobantetipo=12 and kardex.codkardex not in (select codkardex from sunat.kardexsunatdetalle where fecharesumen<='".$fecha."')")->result_array();

		if (count($fechas_resumen)>0) {
			foreach ($fechas_resumen as $key => $value) {
				$lista = $this->db->query("select kardex.codkardex from kardex.kardex as kardex inner join sunat.kardexsunat as kardexs on(kardex.codkardex=kardexs.codkardex) where kardex.fechacomprobante='".$value["fechacomprobante"]."' and kardex.codmovimientotipo=20 and kardex.codcomprobantetipo=12 and kardex.codkardex not in (select codkardex from sunat.kardexsunatdetalle where fecharesumen<='".$fecha."')")->result_array();

				$fecharesumen = $value["fechacomprobante"];
				$f = explode("-",$fecharesumen); $periodo = $f[0].$f[1].$f[2];

				$resumenes = $this->db->query("select coalesce(max(nrocorrelativo),0) as cantidad from sunat.resumenes where periodo='".$periodo."' and (codresumentipo=3 or codresumentipo=4) and codempresa=".$_SESSION["phuyu_codempresa"])->result_array();

				$nrocorrelativo = $resumenes[0]["cantidad"] + 1;
				$oficial = $this->db->query("select oficial from sunat.resumentipos where codresumentipo=".$codresumentipo)->result_array();
				$xml = $_SESSION["phuyu_ruc"]."-".$oficial[0]["oficial"]."-".$periodo."-".$nrocorrelativo;

				$campos = ["codresumentipo","periodo","nrocorrelativo","codempresa","codsucursal","codusuario","nombre_xml","fecharesumen"];
				$valores = [
					(int)$codresumentipo,$periodo,
					(int)$nrocorrelativo,
					(int)$_SESSION["phuyu_codempresa"],
					(int)$_SESSION["phuyu_codsucursal"],
					(int)$_SESSION["phuyu_codusuario"],
					$xml,$fecharesumen
				];
				$estado = $this->phuyu_model->phuyu_guardar("sunat.resumenes", $campos, $valores);

				foreach ($lista as $val) {
					$campos=["codkardex","codresumentipo","nrocorrelativo","periodo","codempresa","fecharesumen"];
					$valores = [
						(int)$val["codkardex"],
						(int)$codresumentipo,
						(int)$nrocorrelativo,
						$periodo,
						(int)$_SESSION["phuyu_codempresa"],
						$fecharesumen
					];
					$estado = $this->phuyu_model->phuyu_guardar("sunat.kardexsunatdetalle", $campos, $valores);
				}
			}
		}

		//BOLETAS ANULADAS GENERADAS
		$codresumentipo = 4;
		$fechas_resumen = $this->db->query("select distinct(k.fechacomprobante) as fechacomprobante from kardex.kardexanulados as ka inner join kardex.kardex as k on(ka.codkardex=k.codkardex) inner join sunat.kardexsunat as ks on(k.codkardex=ks.codkardex) where ka.fechaanulacion<='".$fecha."' and k.codmovimientotipo=20 and k.codcomprobantetipo=12 and (ks.estado=1 or ks.estado=2) and k.codkardex not in (select codkardex from sunat.kardexsunatanulados where fechaanulacion<='".$fecha."')")->result_array();

		if (count($fechas_resumen)>0) {
			foreach ($fechas_resumen as $key => $value) {
				$lista = $this->db->query("select ka.codkardex,ka.observaciones from kardex.kardexanulados as ka inner join kardex.kardex as k on(ka.codkardex=k.codkardex) inner join sunat.kardexsunat as ks on(k.codkardex=ks.codkardex) where k.fechacomprobante='".$value["fechacomprobante"]."' and ka.fechaanulacion<='".$fecha."' and k.codmovimientotipo=20 and k.codcomprobantetipo=12 and (ks.estado=1 or ks.estado=2) and k.codkardex not in (select codkardex from sunat.kardexsunatanulados where fechaanulacion<='".$fecha."') ")->result_array();

				$fecharesumen = $value["fechacomprobante"];
				$f = explode("-",$fecharesumen); $periodo = $f[0].$f[1].$f[2];

				$resumenes = $this->db->query("select coalesce(max(nrocorrelativo),0) as cantidad from sunat.resumenes where periodo='".$periodo."' and (codresumentipo=3 or codresumentipo=4) and codempresa=".$_SESSION["phuyu_codempresa"])->result_array();

				$nrocorrelativo = $resumenes[0]["cantidad"] + 1;
				$oficial = $this->db->query("select oficial from sunat.resumentipos where codresumentipo=".$codresumentipo)->result_array();
				$xml = $_SESSION["phuyu_ruc"]."-".$oficial[0]["oficial"]."-".$periodo."-".$nrocorrelativo;

				$campos = ["codresumentipo","periodo","nrocorrelativo","codempresa","codsucursal","codusuario","nombre_xml","fecharesumen"];
				$valores = [
					(int)$codresumentipo,$periodo,
					(int)$nrocorrelativo,
					(int)$_SESSION["phuyu_codempresa"],
					(int)$_SESSION["phuyu_codsucursal"],
					(int)$_SESSION["phuyu_codusuario"],
					$xml,$fecharesumen
				];
				$estado = $this->phuyu_model->phuyu_guardar("sunat.resumenes", $campos, $valores);

				foreach ($lista as $val) {
					$campos =["codkardex","codresumentipo","nrocorrelativo","periodo","codempresa","codsucursal","fechaanulacion","fechareferencia","motivobaja"];
					$valores = [
						(int)$val["codkardex"],
						(int)$codresumentipo,
						(int)$nrocorrelativo,
						$periodo,
						(int)$_SESSION["phuyu_codempresa"],
						(int)$_SESSION["phuyu_codsucursal"],
						$fecha,$fecharesumen,
						$val["observaciones"]
					];
					$estado = $this->phuyu_model->phuyu_guardar("sunat.kardexsunatanulados", $campos, $valores);
				}
			}
		}

		if ($this->db->trans_status() === FALSE){
		    $this->db->trans_rollback(); $estado = 0;
		}else{
			if ($estado!=1) {
				$this->db->trans_rollback(); $estado = 0;
			}else{
				$this->db->trans_commit(); $estado = 1;
			}
		}
	}

	public function verificarcomprobantes(){
		$generarcomprobantes = $this->generarelectronicos();

		$info = $this->db->query("select count(*) as cantidad from sunat.kardexsunat where estado<>1")->result_array();

		$cantidad = (int)$info[0]["cantidad"];

		echo $cantidad;
	}

	public function obtenercomprobanteselectronicos(){
		if ($this->input->is_ajax_request()) {
			$this->load->view("facturacion/facturacion/electronicos");
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function phuyu_logout(){
		session_destroy(); header("Location: ".base_url());
	}

	public function phuyu_logout2(){
		session_destroy();
	}
}