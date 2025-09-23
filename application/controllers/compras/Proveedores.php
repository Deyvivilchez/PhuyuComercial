<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Proveedores extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$this->load->view("compras/proveedores/index");
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
			$limit = 8; $offset = $this->request->pagina * $limit - $limit;

			$lista = $this->db->query("select personas.* from public.socios as socios inner join public.personas as personas on (socios.codpersona=personas.codpersona) where (UPPER(personas.documento) like UPPER('%".$this->request->buscar."%') or UPPER(personas.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(personas.nombrecomercial) like UPPER('%".$this->request->buscar."%') ) and (socios.codsociotipo=2 or socios.codsociotipo=3) and socios.estado=1 order by personas.codpersona desc offset ".$offset." limit ".$limit)->result_array();

			$total = $this->db->query("select count(*) as total from public.socios as socios inner join public.personas as personas on (socios.codpersona=personas.codpersona) where (UPPER(personas.documento) like UPPER('%".$this->request->buscar."%') or UPPER(personas.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(personas.nombrecomercial) like UPPER('%".$this->request->buscar."%') ) and (socios.codsociotipo=2 or socios.codsociotipo=3) and socios.estado=1")->result_array();

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

	public function nuevo(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$tipodocumentos = $this->db->query("select *from public.documentotipos where estado=1")->result_array();
				$departamentos = $this->db->query("select distinct(ubidepartamento), departamento from public.ubigeo order by ubidepartamento")->result_array();
				$this->load->view("compras/proveedores/nuevo",compact("tipodocumentos","departamentos"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function nuevo_1(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$tipodocumentos = $this->db->query("select *from public.documentotipos where estado=1")->result_array();
				$this->load->view("compras/proveedores/nuevo_1",compact("tipodocumentos"));
			}else{
				$this->load->view("phuyu/505");
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

	function guardar(){
		if ($this->input->is_ajax_request()) {
			$campos = ["coddocumentotipo","documento","razonsocial","nombrecomercial","direccion","email","telefono","codubigeo","estado"];
			$campos_1 = ["codpersona","codsociotipo","usuario","clave"];
			$this->request = json_decode(file_get_contents('php://input'));
			$valores = [$this->request->coddocumentotipo,$this->request->documento,$this->request->razonsocial,$this->request->nombrecomercial,$this->request->direccion,$this->request->email,$this->request->telefono,$this->request->codubigeo,1];

			if($this->request->codregistro=="") {
				$existe =$this->db->query("select codpersona from public.personas where documento='".$this->request->documento."'")->result_array();
				if (count($existe)>0) {
					$socio =$this->db->query("select codpersona,codsociotipo from public.socios where codpersona=".$existe[0]["codpersona"])->result_array();
					if (count($socio)>0) {
						if ($socio[0]["codsociotipo"]==1) {
							$valores_1 = [$existe[0]["codpersona"],3,$this->request->documento,$this->request->documento];
							$estado = $this->phuyu_model->phuyu_editar("public.socios", $campos_1, $valores_1, "codpersona", $existe[0]["codpersona"]);
							echo $estado;
						}else{
							echo "e"; 
						}
						exit();
					}else{
						$estado = $existe[0]["codpersona"];
					}
				}else{
					$codpersona = $this->phuyu_model->phuyu_guardar("public.personas", $campos, $valores,"true");
				}

				$valores_1 = [$codpersona,$this->request->codsociotipo,$this->request->documento,$this->request->documento];
				$estado = $this->phuyu_model->phuyu_guardar("public.socios", $campos_1, $valores_1);
			}else{
				$actual = $this->db->query("select documento from public.personas where codpersona=".$this->request->codregistro)->result_array();
				$existe = $this->db->query("select documento from public.personas where documento='".$this->request->documento."'")->result_array();
				if (count($existe)>0) {
					if ( $actual[0]["documento"]!=$existe[0]["documento"] ) {
						echo "e"; exit();
					}
				}
				
				$estado = $this->phuyu_model->phuyu_editar("public.personas", $campos, $valores, "codpersona", $this->request->codregistro);

				$valores_1 = [$this->request->codregistro,$this->request->codsociotipo,$this->request->documento,$this->request->documento];
				$existe = $this->db->query("select codpersona from public.socios where codpersona=".$this->request->codregistro)->result_array();
				if (count($existe)==0) {
					$estado = $this->phuyu_model->phuyu_guardar("public.socios", $campos_1, $valores_1);
				}else{
					$estado = $this->phuyu_model->phuyu_editar("public.socios", $campos_1, $valores_1, "codpersona", $this->request->codregistro);
				}
			}
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function guardar_1(){
		if ($this->input->is_ajax_request()) {
			$campos = ["coddocumentotipo","documento","razonsocial","nombrecomercial","direccion","email","telefono","estado"];
			$campos_1 = ["codpersona","codsociotipo","usuario","clave"];
			$this->request = json_decode(file_get_contents('php://input'));
			$valores = [$this->request->coddocumentotipo,$this->request->documento,$this->request->razonsocial,$this->request->nombrecomercial,$this->request->direccion,$this->request->email,$this->request->telefono,1];

			$this->db->trans_begin();

			$existe =$this->db->query("select codpersona from public.personas where documento='".$this->request->documento."'")->result_array();
			if (count($existe)>0) {
				$socio =$this->db->query("select codpersona,codsociotipo from public.socios where codpersona=".$existe[0]["codpersona"])->result_array();
				if (count($socio)>0) {
					if ($socio[0]["codsociotipo"]==1) {
						$this->request->codsociotipo = 3;
					}
				}

				$codpersona = $existe[0]["codpersona"];
				$estado = $this->phuyu_model->phuyu_editar("public.personas", $campos, $valores, "codpersona", $codpersona);
			}else{
				$socio = array();
				$codpersona = $this->phuyu_model->phuyu_guardar("public.personas", $campos, $valores, "true");

				$campos = ["codsocio","codubigeo","codzona","codalmacen","descripcion","direccion","fechainicio","fechafin","tasainteres","creditomaximo","codsocioreferencia","codempleado","area","codsocioconvenio","comprado","tipoposesion","codusuario","observaciones","estado"];

				$fechainicio = date('Y-m-d');
				$fechafin = $this->sumarDiasNaturales($fechainicio,150);

				$valores = [$codpersona,0,0,$_SESSION["phuyu_codalmacen"],$this->request->razonsocial,'',$fechainicio,$fechafin,2,10000,$codpersona,$_SESSION["phuyu_codusuario"],0,$codpersona,0,0,$_SESSION["phuyu_codusuario"],'',1];

				$estado = $this->phuyu_model->phuyu_guardar("public.lotes", $campos, $valores);
			}

			$valores_1 = [$codpersona,$this->request->codsociotipo,$this->request->documento,$this->request->documento];
			if (count($socio)>0) {
				$estado = $this->phuyu_model->phuyu_editar("public.socios", $campos_1, $valores_1,"codpersona",$codpersona);
			}else{
				$estado = $this->phuyu_model->phuyu_guardar("public.socios", $campos_1, $valores_1);
			}

			if ($this->db->trans_status() === FALSE){
			    $this->db->trans_rollback(); $estado = 0;
			}else{
				$this->db->trans_commit();
				$estado = $this->db->query("select codpersona,razonsocial from public.personas where codpersona=".$codpersona)->result_array();
			}

			echo json_encode($estado);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function ubigeo(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			$info = $this->db->query("select personas.codubigeo,personas.codzona, socios.codpatrocinador from public.personas as personas inner join public.socios as socios on(personas.codpersona=socios.codpersona) where personas.codpersona=".$this->request->codregistro)->result_array();
			$ubigeo = $this->db->query("select * from public.ubigeo where codubigeo=".$info[0]["codubigeo"])->result_array();
			$patrocinador = $this->db->query("select codpersona,razonsocial from public.personas where codpersona=".$info[0]["codpatrocinador"])->result_array();
			$data["ubigeo"] = $ubigeo; $data["patrocinador"] = $patrocinador;
			$data["codzona"] = $info[0]["codzona"];
			echo json_encode($data);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function editar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$info = $this->db->query("select personas.codpersona as codregistro,* from public.personas as personas inner join public.socios as socios on(personas.codpersona=socios.codpersona) where personas.codpersona=".$this->request->codregistro)->result_array();
			echo json_encode($info);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function eliminar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$estado = $this->phuyu_model->phuyu_eliminar("public.socios", "codpersona", $this->request->codregistro);
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}
}