<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Lineascredito extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model"); $this->load->model("Caja_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_codusuario"])) {
				$this->load->view("ventas/lineascredito/index");
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

			if ($this->request->fechas->filtro == 0) {
				$fechas = "";
			}else{
				if(!empty($this->request->fechas->desde)){
					$fechas = "lotes.fechainicio>='".$this->request->fechas->desde."' and lotes.fechainicio<='".$this->request->fechas->hasta."' and";
				}else{
					$fechas = "lotes.fechainicio<='".$this->request->fechas->hasta."' and";
				}
			}
			$lista = $this->db->query("select lotes.*, personas.razonsocial as socio from public.lotes as lotes inner join public.personas as personas on (lotes.codsocio=personas.codpersona) where ".$fechas." (UPPER(personas.documento) like UPPER('%".$this->request->buscar."%') or UPPER(personas.razonsocial) like UPPER('%".$this->request->buscar."%') ) order by lotes.codlote desc offset ".$offset." limit ".$limit)->result_array();

			$total = $this->db->query("select count(*) as total from public.lotes as lotes inner join public.personas as personas on (lotes.codsocio=personas.codpersona) where ".$fechas." (UPPER(personas.documento) like UPPER('%".$this->request->buscar."%') or UPPER(personas.razonsocial) like UPPER('%".$this->request->buscar."%') )")->result_array();

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

	public function phuyu_lineascredito($codpersona){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$lotes = $this->db->query("select *from public.lotes where codsocio=".$codpersona." AND liquidado<>1 AND estado=1 order by descripcion")->result_array();
			$html = '';
			if(count($lotes)==0){
				$html.='<option value="0">NO TIENE LINEAS DE CREDITO VALIDOS</option>';
			}
			else if(count($lotes)==1){
				$html.='<option value="'.$lotes[0]["codlote"].'">'.'COD. LOTE: '.$lotes[0]["codlote"].' | '.$lotes[0]["descripcion"].'</option>';
			}else{
				if(isset($this->request->flag)){
					$html = '<option value="0">TODAS LAS LINEAS</option>';
				}else{
					$html = '<option value="0">SELECCIONE</option>';
				}
				foreach ($lotes as $key => $value) {
					$html .= '<option value="'.$value["codlote"].'">'.'COD. LOTE: '.$value["codlote"].' | '.$value["descripcion"].'</option>';
				}
			}
			echo $html;
		}
	}

	public function nuevo(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$departamentos = $this->db->query("select distinct(ubidepartamento), departamento from public.ubigeo order by ubidepartamento")->result_array();
				$empleados = $this->db->query("select persona.codpersona,persona.razonsocial from public.personas as persona inner join public.empleados as empleado on(persona.codpersona=empleado.codpersona) where empleado.estado=1 and empleado.codcargo=4")->result_array();
				$this->load->view("ventas/lineascredito/nuevo",compact("departamentos","empleados"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}
	
	function ver($codregistro){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_codusuario"])){
				$codregistro = (int)$codregistro;
				$info = $this->db->query("
					select
						lotes.*,
						socio.documento,
						socio.razonsocial as socio,
						socio.razonsocial as cliente,
						coalesce(garante.razonsocial, socio.razonsocial) as garante,
						sectorista.razonsocial as sectorista,
						ubigeo.departamento,
						ubigeo.provincia,
						ubigeo.distrito,
						zona.descripcion as zona
					from public.lotes as lotes
					inner join public.personas as socio on (lotes.codsocio=socio.codpersona)
					left join public.personas as garante on (lotes.codsocioreferencia=garante.codpersona)
					left join public.personas as sectorista on (lotes.codempleado=sectorista.codpersona)
					left join public.ubigeo as ubigeo on (lotes.codubigeo=ubigeo.codubigeo)
					left join public.zonas as zona on (lotes.codzona=zona.codzona)
					where lotes.codlote=".$codregistro
				)->result_array();

				$this->load->view("ventas/lineascredito/ver",compact("info")); 
			}else{
	            $this->load->view("phuyu/505");
	        }
	    }else{
			$this->load->view("phuyu/404");
		}
	}
	
	function guardar(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$this->request = json_decode(file_get_contents('php://input'));

				$comprado = ($this->request->comprado) ? 1 : 0;

				$this->request->observaciones = (isset($this->request->observaciones)) ? $this->request->observaciones : '';

				$codsocioreferencia = (!empty($this->request->codsocioreferencia)) ? $this->request->codsocioreferencia : $this->request->codsocio;

				$campos = ["codsocio","codubigeo","codzona","codalmacen","descripcion","direccion","fechainicio","fechafin","tasainteres","creditomaximo","codsocioreferencia","codempleado","area","codsocioconvenio","comprado","tipoposesion","codusuario","observaciones","estado"];

				$valores = [$this->request->codsocio,$this->request->codubigeo,$this->request->codzona,$_SESSION["phuyu_codalmacen"],$this->request->cliente,$this->request->direccion,$this->request->fechainicio,$this->request->fechafin,$this->request->tasainteres,$this->request->creditomaximo,$codsocioreferencia,$this->request->codempleado,$this->request->area,$this->request->codsocio,$comprado,$this->request->tipoposesion,$_SESSION["phuyu_codusuario"],$this->request->observaciones,1];

				if($this->request->codlote==0){
					$estado = $this->phuyu_model->phuyu_guardar("public.lotes", $campos, $valores);	
				}else{
					$estado = $this->phuyu_model->phuyu_editar("public.lotes", $campos, $valores, "codlote", $this->request->codlote);
				}

				$data['codlote'] = $this->request->codlote;
				$data['estado'] = $estado;
				echo json_encode($data);
			}else{
				echo "e";
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function guardarlineascreditodirecto(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$this->request = json_decode(file_get_contents('php://input'));

				$campos = ["codsocio","codubigeo","codzona","codalmacen","descripcion","direccion","fechainicio","fechafin","tasainteres","creditomaximo","codsocioreferencia","codempleado","area","codsocioconvenio","comprado","tipoposesion","codusuario","observaciones","estado"];

				$cliente = $this->db->query("select *from public.personas where codpersona=".$this->request->codpersona)->result_array();

				$fechainicio = date('Y-m-d');
				$fechafin = $this->sumarDiasNaturales($fechainicio,150);

				$valores = [$this->request->codpersona,0,0,$_SESSION["phuyu_codalmacen"],$cliente[0]["razonsocial"],'',$fechainicio,$fechafin,2,10000,$this->request->codpersona,$_SESSION["phuyu_codusuario"],0,$this->request->codpersona,0,0,$_SESSION["phuyu_codusuario"],'',1];

				$estado = $this->phuyu_model->phuyu_guardar("public.lotes", $campos, $valores);
				echo $estado;
			}else{
				echo "e";
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public static function sumarDiasNaturales($fecha, $dias)
    {
        if ($dias < 0) {
            return '';
        }
        $dateArray = explode("-", $fecha);
        $sd = $dias;
        while ($sd > 0) {
            if ($sd <= date("t", mktime(0, 0, 0,
                                        $dateArray[ 1 ],
                                        1,
                                        $dateArray[ 0 ])
                            ) - $dateArray[ 2 ]) {
                $dateArray[ 2 ] = $dateArray[ 2 ] + $sd;
                $sd = 0;
            } else {
                $sd  = $sd - ( date( "t", mktime(0, 0, 0,
                                                $dateArray[ 1 ],
                                                1,
                                                $dateArray[ 0 ])
                                   ) - $dateArray[ 2 ]);
                $dateArray[ 2 ] = 0;
                if ($dateArray[ 1 ] < 12) {
                    $dateArray[ 1 ]++;
                } else {
                    $dateArray[ 1 ] = 1;
                    $dateArray[ 0 ]++;
                }
            }
        }
        $sDia = '00'.$dateArray[ 2 ];
        $sDia = substr($sDia, -2);
        $sMes = '00'.$dateArray[ 1 ];
        $sMes = substr($sMes, -2);
        return $dateArray[ 0 ].'-'.$sMes.'-'.$sDia;
    }

	function editar(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$this->request = json_decode(file_get_contents('php://input'));

				$codregistro = (int)$this->request->codregistro;
				$info = $this->db->query("select lotes.fechainicio,lotes.fechafin, lotes.area, lotes.codsocioreferencia, lotes.observaciones, lotes.tipoposesion,lotes.tasainteres, lotes.creditomaximo, lotes.comprado, lotes.codempleado, lotes.codzona, lotes.codubigeo, lotes.descripcion,lotes.direccion,lotes.codsocio,personas.razonsocial AS cliente from public.lotes as lotes inner join public.personas as personas on (lotes.codsocio=personas.codpersona) where lotes.codlote=".$codregistro)->result_array();

				if(count($info)==0){
					echo json_encode(array("info" => array(), "ubigeo" => array()));
					return;
				}

				if((int)$info[0]["codsocioreferencia"]>0 && $info[0]["codsocio"]!=$info[0]["codsocioreferencia"]){
					$codsocioreferencia = $this->db->query("select * from public.personas where codpersona=".$info[0]["codsocioreferencia"])->result_array();

					$info[0]["garante"] = (count($codsocioreferencia)>0) ? $codsocioreferencia[0]["razonsocial"] : $info[0]["cliente"];
				}else{
					$info[0]["garante"] = $info[0]["cliente"];
				}

				$ubigeo = array();
				if((int)$info[0]["codubigeo"]>0){
					$ubigeo = $this->db->query("select * from public.ubigeo where codubigeo=".$info[0]["codubigeo"])->result_array();
				}

				$data['info'] = $info;
				$data['ubigeo'] = $ubigeo;
				echo json_encode($data);
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function editar_guardar(){
		if ($this->input->is_ajax_request()) {
			echo 0;
		}
	}

	function eliminar(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$this->request = json_decode(file_get_contents('php://input'));
				$codregistro = (int)$this->request->codregistro;

				if($codregistro==0){
					echo 0;
					return;
				}

				$credito = $this->db->query("select codcredito from kardex.creditos where codlote=".$codregistro." and estado<>0 limit 1")->result_array();
				if(count($credito)>0){
					echo 2;
					return;
				}

				$estado = $this->phuyu_model->phuyu_eliminar("public.lotes", "codlote", $codregistro);
				echo $estado;
			}else{
				echo "e";
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function restaurar(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$this->request = json_decode(file_get_contents('php://input'));
				$codregistro = (int)$this->request->codregistro;
				$estado = $this->phuyu_model->phuyu_restaurar("public.lotes", "codlote", $codregistro);
				echo $estado;
			}else{
				echo "e";
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function formato($formato){
		if ($this->input->is_ajax_request()) {
			$campos = ["formato"]; $valores = [$formato];
			$f = ["codsucursal","codcomprobantetipo"]; $v = [$_SESSION["phuyu_codsucursal"],10];
			$estado = $this->phuyu_model->phuyu_editar_1("caja.comprobantes", $campos, $valores, $f, $v);

			echo $formato;
		}
	}
}
