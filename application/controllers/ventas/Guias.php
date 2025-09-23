<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Guias extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model"); $this->load->model("Kardex_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_codusuario"])) {

				/* CODIGO TEMPORAL DE LA IMPRESION */
                $_SESSION["phuyu_formato"] = "a4";
				/*$formato = $this->db->query("select formato from caja.comprobantes where codcomprobantetipo=10 AND codsucursal = ".$_SESSION["phuyu_codsucursal"])->result_array();
				if (count($formato)==0) {
					$_SESSION["phuyu_formato"] = "a4";
				}else{
					$_SESSION["phuyu_formato"] = $formato[0]["formato"];
				}*/

				/* FIN CODIGO TEMPORAL DE LA IMPRESION */

				$comprobante_almacen = $this->db->query("select count(*) as cantidad from caja.comprobantes where codcomprobantetipo=16 and codsucursal=".$_SESSION["phuyu_codsucursal"]." and estado=1")->result_array();
				$almacen = $comprobante_almacen[0]["cantidad"];
				$this->load->view("ventas/guias/index",compact("almacen"));
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
					$fechas = "guiasr.fechaguia>='".$this->request->fechas->desde."' and guiasr.fechaguia<='".$this->request->fechas->hasta."' and";
				}else{
					$fechas = "guiasr.fechaguia<='".$this->request->fechas->hasta."' and";
				}
			}
			$lista = $this->db->query("select motivos.descripcion AS motivo,personas.documento,personas.razonsocial,guiasr.codguiar, guiasr.codcomprobantetipo, guiasr.seriecomprobante,guiasr.nrocomprobante, guiasr.fechaguia,guiasr.estado, comprobantes.descripcion as tipo from almacen.guiasr as guiasr inner join public.personas as personas on (guiasr.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(guiasr.codcomprobantetipo=comprobantes.codcomprobantetipo) inner join almacen.motivotraslado as motivos on(guiasr.codmotivotraslado=motivos.codmotivotraslado) where ".$fechas." (UPPER(personas.documento) like UPPER('%".$this->request->buscar."%') or UPPER(personas.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(comprobantes.descripcion) like UPPER('%".$this->request->buscar."%') or UPPER(guiasr.seriecomprobante) like UPPER('%".$this->request->buscar."%') or UPPER(guiasr.nrocomprobante) like UPPER('%".$this->request->buscar."%') ) and guiasr.codsucursal=".$_SESSION["phuyu_codsucursal"]." order by guiasr.codguiar desc offset ".$offset." limit ".$limit)->result_array();

			foreach ($lista as $key => $value) {
				$guiasunat = $this->db->query("select estado from sunat.guiasunat where codguiar = ".$value["codguiar"])->result_array();

				if(count($guiasunat)){
					$lista[$key]["estadosunat"] = $guiasunat[0]["estado"];
				}else{
					$lista[$key]["estadosunat"] = 2;
				}
			}

			$total = $this->db->query("select count(*) as total from almacen.guiasr as guiasr inner join public.personas as personas on (guiasr.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(guiasr.codcomprobantetipo=comprobantes.codcomprobantetipo) where ".$fechas." (UPPER(personas.documento) like UPPER('%".$this->request->buscar."%') or UPPER(personas.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(personas.nombrecomercial) like UPPER('%".$this->request->buscar."%') or UPPER(comprobantes.descripcion) like UPPER('%".$this->request->buscar."%') or UPPER(guiasr.seriecomprobante) like UPPER('%".$this->request->buscar."%') or UPPER(guiasr.nrocomprobante) like UPPER('%".$this->request->buscar."%') ) and guiasr.codsucursal=".$_SESSION["phuyu_codsucursal"])->result_array();

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
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$comprobantes = $this->db->query("select distinct(ct.codcomprobantetipo) as codigo, ct.* from caja.comprobantetipos as ct inner join caja.comprobantes as c on(ct.codcomprobantetipo=c.codcomprobantetipo) where c.codsucursal=".$_SESSION["phuyu_codsucursal"]." and c.codcomprobantetipo=16 and c.estado=1")->result_array();
				$modalidades = $this->db->query("select *from almacen.modalidadtraslado where estado = 1")->result_array();
				$motivos = $this->db->query("select *from almacen.motivotraslado where estado=1 order by codmotivotraslado")->result_array();
				$unidades = $this->db->query("select *from almacen.unidades where estado=1 order by codunidad")->result_array();
				$sucursal = $this->db->query("select coalesce(codcomprobantetipo,16) as codcomprobantetipo, seriecomprobante from public.sucursales where codsucursal=".$_SESSION["phuyu_codsucursal"])->result_array();
				$tipodoc = $this->db->query("select *from documentotipos where estado=1")->result_array();
				$departamentopartida = $this->db->query("select distinct(ubidepartamento), departamento from public.ubigeo order by ubidepartamento")->result_array();
				$departamentollegada = $this->db->query("select distinct(ubidepartamento), departamento from public.ubigeo order by ubidepartamento")->result_array();
				$almacenes_1 = $this->db->query("select *from almacen.almacenes where codalmacen=".$_SESSION["phuyu_codalmacen"]." and estado=1")->result_array();
				$almacenes_2 = $this->db->query("select *from almacen.almacenes where codalmacen<>".$_SESSION["phuyu_codalmacen"]." and estado=1")->result_array();

				$this->load->view("ventas/guias/nuevo",compact("comprobantes","modalidades","motivos","unidades","sucursal","tipodoc","departamentopartida","departamentollegada","almacenes_2","almacenes_1"));
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
				$info = $this->db->query("select kardex.*,comprobantes.descripcion as tipo from almacen.guiasr as kardex inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codguiar=".$codregistro)->result_array();

				$detalle = $this->db->query("select kd.*,p.descripcion as producto,u.descripcion as unidad from almacen.guiasrdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codguiar=".$codregistro." and kd.estado=1 order by kd.item")->result_array();
				$this->load->view("ventas/guias/ver",compact("info","detalle")); 
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

				//$this->db->trans_begin();

                //GUARDAR LA CABECERA DE LA GUIA

                $destinatario = $this->db->query("select codempresa,p.razonsocial as empresa from empresas e inner join personas p ON(e.codpersona = p.codpersona) where e.codempresa=1")->result_array();

                if($this->request->campos->codmotivotraslado == 4){
                	$this->request->campos->destinatario = $destinatario[0]["empresa"];
                	$coddestinatario = $destinatario[0]["codempresa"];
                	$codremitente = $destinatario[0]["codempresa"];
                	$razonsocialremitente = $destinatario[0]["empresa"];
                	$codproveedor = $destinatario[0]["codempresa"];
                	$razonsocialproveedor = $destinatario[0]["empresa"];
                }else if($this->request->campos->codmotivotraslado == 2){
                	$coddestinatario = $this->request->campos->codpersona;
                	$codremitente = $destinatario[0]["codempresa"];
                	$razonsocialremitente = $destinatario[0]["empresa"];
                	$codproveedor = $this->request->campos->codremitente;
                	$razonsocialproveedor = $this->request->campos->remitente;
                }else if($this->request->campos->codmotivotraslado == 1){
                	$coddestinatario = $this->request->campos->codpersona;
                	$codremitente = $destinatario[0]["codempresa"];
                	$razonsocialremitente = $destinatario[0]["empresa"];
                	$codproveedor = $destinatario[0]["codempresa"];
                	$razonsocialproveedor = $destinatario[0]["empresa"];
                }else{
                	$coddestinatario = $destinatario[0]["codempresa"];
                	$codremitente = $destinatario[0]["codempresa"];
                	$razonsocialremitente = $destinatario[0]["empresa"];
                	$codproveedor = $destinatario[0]["codempresa"];
                	$razonsocialproveedor = $destinatario[0]["empresa"];
                }

                if(!isset($this->request->campos->nrocontenedor)){
                    $this->request->campos->nrocontenedor = 1;
                }

                if(!isset($this->request->campos->licenciaconductor)){
                	$this->request->campos->licenciaconductor = '';
                }

                if(!isset($this->request->campos->constancia)){
                	$this->request->campos->constancia = '';
                }
                if(!isset($this->request->campos->codvehiculo)){
                	$this->request->campos->codvehiculo = 0;
                }

                $campos = ["codpersona","codusuario","codcomprobantetipo","seriecomprobante","nrocomprobante","codmodalidadtraslado",
                           "codmotivotraslado","fechatraslado","codigopuerto","transbordo","peso","nropaquetes","nrocontenedor",
                           "descripcionmotivo","observaciones","codempleado","codubigeopartida","direccionpartida","codubigeollegada","direccionllegada","coddocumentotipotransportista","documentotransportista","razonsocialtransportista","nroplaca","coddocumentotipoconductor","documentoconductor","razonsocialconductor","codalmacen",
                           "codsucursal","estado","tipomovimiento","fechaguia","destinatario","coddestinatario","codremitente","razonsocialremitente","codproveedor","razonsocialproveedor","licenciaconductor","constancia"];

				$valores = [$this->request->campos->codpersona,(int)$_SESSION["phuyu_codusuario"],$this->request->campos->codcomprobantetipo,$this->request->campos->seriecomprobante,$this->request->campos->nro,$this->request->campos->codmodalidadtraslado,$this->request->campos->codmotivotraslado,$this->request->campos->fechatraslado,"000",0,$this->request->campos->peso,$this->request->campos->nropaquetes,$this->request->campos->nrocontenedor,$this->request->campos->descripcionmotivo,$this->request->campos->observaciones,(int)$_SESSION["phuyu_codusuario"],(int)$this->request->campos->codubigeopartida,$this->request->campos->direccionpartida,(int)$this->request->campos->codubigeollegada,$this->request->campos->direccionllegada,$this->request->campos->coddocumentotipotransportista,$this->request->campos->documentotransportista,$this->request->campos->razonsocialtransportista,$this->request->campos->nroplaca,$this->request->campos->coddocumentotipoconductor,$this->request->campos->documentoconductor,$this->request->campos->razonsocialconductor,$this->request->campos->almacenpartida,(int)$_SESSION["phuyu_codsucursal"],1,0,$this->request->campos->fechaguia,$this->request->campos->destinatario,$coddestinatario,$codremitente,$razonsocialremitente,$codproveedor,$razonsocialproveedor,$this->request->campos->licenciaconductor,$this->request->campos->constancia];

				$codguia = $this->phuyu_model->phuyu_guardar("almacen.guiasr", $campos, $valores, "true");

				if(($this->request->campos->codmotivotraslado == 1 || $this->request->campos->codmotivotraslado == 2) && count($this->request->codkardex) > 0){
                    $detallecomprobante = $this->request->detallecomprobante;
					foreach ($detallecomprobante as $key => $value) {
                        
                        $campos = ["codguiar","codkardex","codalmacen","codsucursal","codcomprobantetipok","seriecomprobantek","nrocomprobantek","codcomprobantetipogr","seriecomprobantegr","nrocomprobantegr","estado"];
                    
                        $valores = [$codguia,$detallecomprobante[$key]->codkardex,$this->request->campos->almacenpartida,(int)$_SESSION["phuyu_codsucursal"],$detallecomprobante[$key]->codcomprobantetipo,$detallecomprobante[$key]->seriecomprobante,$detallecomprobante[$key]->nrocomprobante,$this->request->campos->codcomprobantetipo,
                            $this->request->campos->seriecomprobante,$this->request->campos->nro,1];

                        $codkardexguiasr = $this->phuyu_model->phuyu_guardar("almacen.kardexguiasr", $campos, $valores, "true");
					}
				}

				//GUARDAR DETALLE DE LA GUIA

				$detalle = $this->phuyu_model->phuyu_guardardetalleguia($codguia,$this->request->detalle,$this->request->campos->codmotivotraslado,$this->request->codkardex);

				//ACTUALIZAR CORRELATIVO DEL COMPROBANTE

				$estado = $this->phuyu_model->actualizar_correlativo($codguia,'codguiar','almacen.guiasr',$this->request->campos->codcomprobantetipo,$this->request->campos->seriecomprobante);

				if(isset($this->request->campos->salida)){
					$guia = $this->db->query("select *from almacen.guiasr where codguiar=".$codguia)->result_array();
                    $campos = ["seriecomprobante_ref","nrocomprobante_ref","codcomprobantetipo_ref"]; 
                    $valores = [$guia[0]["seriecomprobante"],$guia[0]["nrocomprobante"],$guia[0]["codcomprobantetipo"]];
					$f = ["codkardex"]; $v = [(int)$this->request->campos->salida];
					$estado = $this->phuyu_model->phuyu_editar_1("kardex.kardex", $campos, $valores, $f, $v);
				}

				//REGISTRO EN KARDEX SUNAT

				$guia = $this->db->query("select nrocomprobante from almacen.guiasr where codguiar=".$codguia)->result_array();
				$xml = $_SESSION["phuyu_ruc"]."-09-".$this->request->campos->seriecomprobante."-".$guia[0]["nrocomprobante"];
				
				$campos = ["codguiar","codsucursal","codusuario","fechacreado","nombre_xml"];
				$valores = [
					(int)$codguia,(int)$_SESSION["phuyu_codsucursal"],(int)$_SESSION["phuyu_codusuario"],
					$this->request->campos->fechaguia, $xml
				];
				$estado = $this->phuyu_model->phuyu_guardar("sunat.guiasunat", $campos, $valores);


				$data["estado"] = $estado; $data["codguia"] = $codguia;
				echo json_encode($data);
			}else{
				echo json_encode("e");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function editar(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$this->request = json_decode(file_get_contents('php://input'));
				$info = $this->db->query("select kardex.codkardex,kardex.fechacomprobante,kardex.fechakardex, kardex.seriecomprobante, kardex.nrocomprobante,kardex.nroplaca,kardex.cliente,kardex.direccion,kardex.descripcion,personas.codpersona, personas.razonsocial,comprobantes.descripcion as tipo from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codkardex=".$this->request->codregistro)->result_array();
				$sunat_existe = $this->db->query("select estado from sunat.kardexsunat where codkardex=".$this->request->codregistro)->result_array();
				if (count($sunat_existe)==0) {
					$sunat = 0;
				}else{
					if ($sunat_existe[0]["estado"]==0) {
						$sunat = 0;
					}else{
						$sunat = 1;
					}
				}
				$this->load->view("ventas/ventas/editar",compact("info","sunat"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function editar_guardar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			$campos = ["codpersona","fechacomprobante","fechakardex","cliente","direccion","descripcion","nroplaca"];
			$valores = [
				$this->request->codpersona,
				$this->request->fechacomprobante,
				$this->request->fechakardex,
				$this->request->cliente,
				$this->request->direccion,
				$this->request->descripcion,
				$this->request->nroplaca
			];
			$estado = $this->phuyu_model->phuyu_editar("kardex.kardex", $campos, $valores, "codkardex",$this->request->codregistro);

			$campos = ["fechakardex"]; $valores = [$this->request->fechakardex];
			$estado_u = $this->phuyu_model->phuyu_editar("kardex.kardexalmacen", $campos, $valores, "codkardex",$this->request->codregistro);

			$campos = ["codpersona","fechacredito"]; $valores = [$this->request->codpersona,$this->request->fechacomprobante];
			$estado_u = $this->phuyu_model->phuyu_editar("kardex.creditos", $campos, $valores, "codkardex",$this->request->codregistro);
			$campos = ["codpersona","fechamovimiento"]; $valores = [$this->request->codpersona,$this->request->fechacomprobante];
			$estado_u = $this->phuyu_model->phuyu_editar("caja.movimientos", $campos, $valores, "codkardex",$this->request->codregistro);

			echo $estado;
		}
	}

	function eliminar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$this->db->trans_begin();

			// SI EXISTE EN CREDITOS //
			$credito = $this->db->query("select *from kardex.creditos where codkardex=".$this->request->codregistro." and estado<>0")->result_array();
			if (count($credito)>0) {
				$this->db->trans_rollback(); $estado = 2; echo $estado; exit();
			}

			// ACTUALIZAMOS PRODUCTOS UBICACION //
			$kardexalmacen = $this->db->query("select codkardexalmacen from kardex.kardexalmacen where codkardex=".$this->request->codregistro)->result_array();

			$info = $this->db->query("select *from kardex.kardexdetalle where codkardex=".$this->request->codregistro)->result_array();
			foreach ($info as $key => $value) {
				$existe = $this->db->query("select *from almacen.productoubicacion where codalmacen=".$_SESSION["phuyu_codalmacen"]." and codproducto=".$value["codproducto"]." and codunidad=".$value["codunidad"])->result_array();
				$stock = $existe[0]["stockactual"] + $value["cantidad"];

				$campos = ["stockactual"]; $valores = [(double)$stock];
				$f = ["codalmacen","codproducto","codunidad"];
				$v = [(int)$_SESSION["phuyu_codalmacen"],(int)$value["codproducto"],(int)$value["codunidad"]];
				$estado = $this->phuyu_model->phuyu_editar_1("almacen.productoubicacion", $campos, $valores, $f, $v);
			}
			$estado = $this->phuyu_model->phuyu_eliminar("kardex.kardex", "codkardex", $this->request->codregistro);
			$estado = $this->phuyu_model->phuyu_eliminar("kardex.kardexalmacen", "codkardexalmacen", $kardexalmacen[0]["codkardexalmacen"]);

			// REGISTRO KARDEX ANULADOS //
			$campos = ["codkardex","codsucursal","codusuario","fechaanulacion","observaciones"];
			$valores =[
				(int)$this->request->codregistro, (int)$_SESSION["phuyu_codsucursal"], (int)$_SESSION["phuyu_codusuario"], date("Y-m-d"),
				$this->request->observaciones
			];
			$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexanulados", $campos, $valores);

			// REGISTRO KARDEX ALMACEN ANULADOS //
			$campos = ["codkardexalmacen","codsucursal","codusuario","fechaanulacion","observaciones"];
			$valores =[
				(int)$kardexalmacen[0]["codkardexalmacen"], (int)$_SESSION["phuyu_codsucursal"], (int)$_SESSION["phuyu_codusuario"], date("Y-m-d"),
				$this->request->observaciones
			];
			$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexalmacenanulado", $campos, $valores);

			// ANULAMOS EL MOVIMIENTO DE CAJA //
			$movi = $this->db->query("select codmovimiento from caja.movimientos where codkardex=".$this->request->codregistro)->result_array();
			$estado = $this->phuyu_model->phuyu_eliminar("caja.movimientos", "codmovimiento", $movi[0]["codmovimiento"]);

			$campos = ["estado"]; $valores = [0];
			$f = ["codmovimiento"]; $v = [(int)$movi[0]["codmovimiento"]];
			$estado = $this->phuyu_model->phuyu_editar_1("caja.movimientosdetalle", $campos, $valores, $f, $v);

			if ($this->db->trans_status() === FALSE){
			    $this->db->trans_rollback(); $estado = 0;
			}else{
				if ($estado!=1) {
					$this->db->trans_rollback(); $estado = 0;
				}
				$this->db->trans_commit();
			}
			echo $estado;
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