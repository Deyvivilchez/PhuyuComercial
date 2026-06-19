<?php defined('BASEPATH') OR exit('No direct script access allowed');
include("Sunat.php");

class Facturacion extends Sunat {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model"); $this->load->model("Facturacion_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			$this->load->view("facturacion/facturacion/index");
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function comprobantes(){
		if ($this->input->is_ajax_request()) {
			$facturas = $this->db->query("select personas.documento, kardex.cliente, kardex.codkardex, kardex.seriecomprobante, kardex.nrocomprobante,kardex.fechacomprobante,round(kardex.importe,2) as importe,kardexs.estado from kardex.kardex as kardex inner join sunat.kardexsunat as kardexs on(kardex.codkardex=kardexs.codkardex) inner join public.personas as personas on (kardex.codpersona=personas.codpersona) where kardexs.estado<>1 and kardex.codmovimientotipo=20 and kardex.codcomprobantetipo=10 order by kardex.codkardex asc")->result_array();

			$data["facturas"] = $facturas;
			echo json_encode($data);
		}
	}

	function notas(){
		if ($this->input->is_ajax_request()) {
			$facturas = $this->db->query("select personas.documento, kardex.cliente, kardex.codkardex, kardex.seriecomprobante, kardex.nrocomprobante,kardex.fechacomprobante,round(kardex.importe,2) as importe,kardexs.estado from kardex.kardex as kardex inner join sunat.kardexsunat as kardexs on(kardex.codkardex=kardexs.codkardex) inner join public.personas as personas on (kardex.codpersona=personas.codpersona) where kardexs.estado<>1 and kardex.codmovimientotipo=8 and kardex.codcomprobantetipo=14 order by kardex.codkardex asc")->result_array();

			$data["notas"] = $facturas;
			echo json_encode($data);
		}
	}

	function guias(){
		if ($this->input->is_ajax_request()) {
			$guias = $this->db->query("select motivos.descripcion AS motivo,personas.documento, guiasr.destinatario, guiasr.codguiar, guiasr.seriecomprobante, guiasr.nrocomprobante,guiasr.fechaguia,guiasrs.estado from almacen.guiasr as guiasr inner join sunat.guiasunat as guiasrs on(guiasr.codguiar=guiasrs.codguiar) inner join public.personas as personas on (guiasr.codpersona=personas.codpersona) inner join almacen.motivotraslado as motivos on(guiasr.codmotivotraslado=motivos.codmotivotraslado) where guiasrs.estado<>1 order by guiasr.codguiar asc")->result_array();

			$data["guias"] = $guias;
			echo json_encode($data);
		}
	}

	function validar_nota_credito($codkardex){
		$nota = $this->db->query("select codkardex, codkardex_ref, seriecomprobante, nrocomprobante, seriecomprobante_ref, nrocomprobante_ref, round(importe,2) as importe from kardex.kardex where codkardex=".(int)$codkardex." and codcomprobantetipo=14")->result_array();
		if (count($nota)==0) {
			return ["estado" => 1];
		}

		$referencia = $this->db->query("select k.codkardex, k.seriecomprobante, k.nrocomprobante, round(k.importe,2) as importe, ks.estado as estadosunat from kardex.kardex as k left join sunat.kardexsunat as ks on(k.codkardex=ks.codkardex and ks.estado=1) where k.codkardex=".(int)$nota[0]["codkardex_ref"]." and k.estado=1")->result_array();
		if (count($referencia)==0) {
			return ["estado" => 0, "mensaje" => "La nota ".$nota[0]["seriecomprobante"]."-".$nota[0]["nrocomprobante"]." no tiene comprobante afectado valido."];
		}
		if ((int)$referencia[0]["estadosunat"]!=1) {
			return ["estado" => 0, "mensaje" => "La nota ".$nota[0]["seriecomprobante"]."-".$nota[0]["nrocomprobante"]." afecta a ".$nota[0]["seriecomprobante_ref"]."-".$nota[0]["nrocomprobante_ref"].", pero ese comprobante no esta aceptado por SUNAT. Elimine esa nota y genere una nueva contra el comprobante aceptado."];
		}

		$notas = $this->db->query("select coalesce(sum(importe),0) as total from kardex.kardex where codkardex_ref=".(int)$nota[0]["codkardex_ref"]." and codcomprobantetipo=14 and estado=1 and codkardex<>".(int)$codkardex)->result_array();
		$importeDisponible = round((double)$referencia[0]["importe"] - (double)$notas[0]["total"], 2);
		if ((double)$nota[0]["importe"] > $importeDisponible) {
			return ["estado" => 0, "mensaje" => "La nota ".$nota[0]["seriecomprobante"]."-".$nota[0]["nrocomprobante"]." es por S/ ".number_format((double)$nota[0]["importe"],2).", pero el disponible del comprobante afectado ".$referencia[0]["seriecomprobante"]."-".$referencia[0]["nrocomprobante"]." es S/ ".number_format($importeDisponible,2)."."];
		}

		return ["estado" => 1];
	}

	function comprobantes_enviar($codkardex,$codoficial = ""){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_codusuario"])) {
				$empresa = $this->db->query("select *from public.webservice where codempresa=1")->result_array();
				if ($codoficial=="") {
					$comprobante = $this->db->query("select ct.oficial from kardex.kardex as k inner join caja.comprobantetipos as ct on(k.codcomprobantetipo=ct.codcomprobantetipo) where k.codkardex=".(int)$codkardex)->result_array();
					if (count($comprobante)==0) {
						echo json_encode(["estado" => 0, "mensaje" => "Comprobante no encontrado", "alerta" => "error"]);
						return;
					}
					$codoficial = $comprobante[0]["oficial"];
				}
				if ($codoficial=="07") {
					$validacionNota = $this->validar_nota_credito($codkardex);
					if ($validacionNota["estado"]==0) {
						echo json_encode(["estado" => 0, "mensaje" => $validacionNota["mensaje"], "alerta" => "error"]);
						return;
					}
				}

				$estado = $this->Facturacion_model->phuyu_crearXML($codoficial,$codkardex);
				if ($estado["estado"]!=0) {
					$firma = $this->phuyu_firmarXML($estado["carpeta_phuyu"]."/".$estado["archivo_phuyu"], 0, true);
					if ($firma["estado"]==1) {
						$credenciales = [$_SESSION["phuyu_ruc"],$empresa[0]["usuariosol"],$empresa[0]["clavesol"],$codkardex];
                        $estado = Sunat::phuyu_enviarSUNAT("sendBill",$estado["carpeta_phuyu"],$estado["archivo_phuyu"],$credenciales);
						$mensaje = $estado["mensaje"]; $estado = $estado["estado"]; $alerta = ($estado==1 || $estado==2) ? "success" : "error";
					}else{
						$estado = 0; $mensaje = "NO SE PUEDE FIRMAR EL DOCUMENTO XML: ".$firma["mensaje"]; $alerta = "error";
					}
				}else{
					$estado = 0; $mensaje = "NO SE PUEDE GENERAR EL DOCUMENTO XML"; $alerta = "error";
				}

				$data["estado"] = $estado; $data["mensaje"] = $mensaje; $data["alerta"] = $alerta;
				echo json_encode($data);
			}else{
				echo "e";
			}
		}
	}

	function guias_enviar($codguiar,$codoficial){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_codusuario"])) {
				$empresa = $this->db->query("select *from public.webservice where codempresa=1")->result_array();

				$estado = $this->Facturacion_model->phuyu_crearXMLGUIAS($codoficial,$codguiar);
				if ($estado["estado"]!=0) {
					$firma = $this->phuyu_firmarXML($estado["carpeta_phuyu"]."/".$estado["archivo_phuyu"], 0, true);
					if ($firma["estado"]==1) {
						$credenciales = [$_SESSION["phuyu_ruc"],$empresa[0]["usuariosol"],$empresa[0]["clavesol"],$codguiar];
                        $estado = Sunat::phuyu_enviarSUNATGUIA("sendBill",$estado["carpeta_phuyu"],$estado["archivo_phuyu"],$credenciales);
						$mensaje = $estado["mensaje"]; $estado = $estado["estado"]; $alerta = ($estado==1 || $estado==2) ? "success" : "error";
					}else{
						$estado = 0; $mensaje = "NO SE PUEDE FIRMAR EL DOCUMENTO XML: ".$firma["mensaje"]; $alerta = "error";
					}
				}else{
					$estado = 0; $mensaje = "NO SE PUEDE GENERAR EL DOCUMENTO XML"; $alerta = "error";
				}

				$data["estado"] = $estado; $data["mensaje"] = $mensaje; $data["alerta"] = $alerta;
				echo json_encode($data);
			}else{
				echo "e";
			}
		}
	}

	function comprobantes_xml($codkardex,$codoficial){
		if (isset($_SESSION["phuyu_codusuario"])) {
			$estado = $this->Facturacion_model->phuyu_crearXML($codoficial,$codkardex);
			if ($estado["estado"]!=0) {
				$firma = $this->phuyu_firmarXML($estado["carpeta_phuyu"]."/".$estado["archivo_phuyu"], 0, true);
				if ($firma["estado"]!=1) {
					echo "NO SE PUEDE FIRMAR EL DOCUMENTO XML: ".$firma["mensaje"];
					return;
				}

				$this->load->helper("download"); 
				$descargar_ruta = file_get_contents($estado["carpeta_phuyu"]."/".$estado["archivo_phuyu"].".xml");
				force_download($estado["archivo_phuyu"].".xml", $descargar_ruta);
			}
		}
	}

	function guias_xml($codguiar,$codoficial){
		if (isset($_SESSION["phuyu_codusuario"])) {
			$estado = $this->Facturacion_model->phuyu_crearXMLGUIAS($codoficial,$codguiar);
			if ($estado["estado"]!=0) {
				$firma = $this->phuyu_firmarXML($estado["carpeta_phuyu"]."/".$estado["archivo_phuyu"], 0, true);
				if ($firma["estado"]!=1) {
					echo "NO SE PUEDE FIRMAR EL DOCUMENTO XML: ".$firma["mensaje"];
					return;
				}

				$this->load->helper("download"); 
				$descargar_ruta = file_get_contents($estado["carpeta_phuyu"]."/".$estado["archivo_phuyu"].".xml");
				force_download($estado["archivo_phuyu"].".xml", $descargar_ruta);
			}
		}
	}

	function comprobantes_cdr($codkardex){
		if (isset($_SESSION["phuyu_codusuario"])) {
			$ruta = $this->db->query("select ruta_cdr from sunat.kardexsunat where codkardex=".$codkardex)->result_array();
			if ($ruta[0]["ruta_cdr"]!="") {
				$archivo = explode("R-",$ruta[0]["ruta_cdr"]);
				$this->load->helper("download"); 
				$descargar_ruta = file_get_contents($ruta[0]["ruta_cdr"].".zip");
				force_download("R-".$archivo[1].".zip", $descargar_ruta);
			}
		}
	}

	function guias_cdr($codguiar){
		if (isset($_SESSION["phuyu_codusuario"])) {
			$codguiar = (int) $codguiar;
			$ruta = $this->db->query("select ruta_cdr from sunat.guiasunat where codguiar=".$codguiar)->result_array();

			if (count($ruta) && $ruta[0]["ruta_cdr"]!="") {
				$archivo_cdr = $ruta[0]["ruta_cdr"].".zip";
				$archivo = explode("R-",$ruta[0]["ruta_cdr"]);
				$this->load->helper("download");

				if (file_exists($archivo_cdr)) {
					$descargar_ruta = file_get_contents($archivo_cdr);
					force_download("R-".$archivo[1].".zip", $descargar_ruta);
				}
			}
		}
	}

	function resumenes(){
		if ($this->input->is_ajax_request()) {
			$facturas_anuladas = $this->db->query("select *from sunat.resumenes where codresumentipo=1 and estado<>1 order by fecharesumen")->result_array();
			$resumen_boletas = $this->db->query("select *from sunat.resumenes where (codresumentipo=3 or codresumentipo=4) and estado<>1 order by fecharesumen")->result_array();

			$data["facturas_anuladas"] = $facturas_anuladas;
			$data["resumenes_boletas"] = $resumen_boletas;
			echo json_encode($data);
		}
	}
	
	function resumenes_generar($codresumentipo,$fecha){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_codusuario"])) {
				if ($codresumentipo==1) {
					$fechas_resumen = $this->db->query("select distinct(k.fechacomprobante) as fechacomprobante from kardex.kardexanulados as ka inner join kardex.kardex as k on(ka.codkardex=k.codkardex) inner join sunat.kardexsunat as ks on(k.codkardex=ks.codkardex) where ka.fechaanulacion<='".$fecha."' and k.codmovimientotipo=20 and k.codcomprobantetipo=10 and (ks.estado=1 or ks.estado=2) and k.codkardex not in (select codkardex from sunat.kardexsunatanulados where fechaanulacion<='".$fecha."') ")->result_array();
					$tipo = "FACTURAS ANULADAS";
				}elseif($codresumentipo==3){
					$fechas_resumen = $this->db->query("select distinct(kardex.fechacomprobante) as fechacomprobante from kardex.kardex as kardex inner join sunat.kardexsunat as kardexs on(kardex.codkardex=kardexs.codkardex) where kardexs.fechacreado<='".$fecha."' and kardex.codmovimientotipo=20 and kardex.codcomprobantetipo=12 and upper(kardex.seriecomprobante) like 'B%' and kardex.codkardex not in (select codkardex from sunat.kardexsunatdetalle where fecharesumen<='".$fecha."')")->result_array();
					$tipo = "BOLETAS";
				}else{
					$fechas_resumen = $this->db->query("select distinct(k.fechacomprobante) as fechacomprobante from kardex.kardexanulados as ka inner join kardex.kardex as k on(ka.codkardex=k.codkardex) inner join sunat.kardexsunat as ks on(k.codkardex=ks.codkardex) where ka.fechaanulacion<='".$fecha."' and k.codmovimientotipo=20 and k.codcomprobantetipo=12 and upper(k.seriecomprobante) like 'B%' and (ks.estado=1 or ks.estado=2) and k.codkardex not in (select codkardex from sunat.kardexsunatanulados where fechaanulacion<='".$fecha."')")->result_array();
					$tipo = "BOLETAS ANULADAS";
				}

				if (count($fechas_resumen)>0) {
					$this->db->trans_begin();

					foreach ($fechas_resumen as $key => $value) {
						if ($codresumentipo==1) {
				            $lista = $this->db->query("select ka.codkardex,ka.observaciones from kardex.kardexanulados as ka inner join kardex.kardex as k on(ka.codkardex=k.codkardex) inner join sunat.kardexsunat as ks on(k.codkardex=ks.codkardex) where k.fechacomprobante='".$value["fechacomprobante"]."' and ka.fechaanulacion<='".$fecha."' and k.codmovimientotipo=20 and k.codcomprobantetipo=10 and (ks.estado=1 or ks.estado=2) and k.codkardex not in (select codkardex from sunat.kardexsunatanulados where fechaanulacion<='".$fecha."') ")->result_array();
				        }elseif($codresumentipo==3){
				            $lista = $this->db->query("select kardex.codkardex from kardex.kardex as kardex inner join sunat.kardexsunat as kardexs on(kardex.codkardex=kardexs.codkardex) where kardex.fechacomprobante='".$value["fechacomprobante"]."' and kardex.codmovimientotipo=20 and kardex.codcomprobantetipo=12 and upper(kardex.seriecomprobante) like 'B%' and kardex.codkardex not in (select codkardex from sunat.kardexsunatdetalle where fecharesumen<='".$fecha."')")->result_array();
				        }else{
				            $lista = $this->db->query("select ka.codkardex,ka.observaciones from kardex.kardexanulados as ka inner join kardex.kardex as k on(ka.codkardex=k.codkardex) inner join sunat.kardexsunat as ks on(k.codkardex=ks.codkardex) where k.fechacomprobante='".$value["fechacomprobante"]."' and ka.fechaanulacion<='".$fecha."' and k.codmovimientotipo=20 and k.codcomprobantetipo=12 and upper(k.seriecomprobante) like 'B%' and (ks.estado=1 or ks.estado=2) and k.codkardex not in (select codkardex from sunat.kardexsunatanulados where fechaanulacion<='".$fecha."') ")->result_array();
				        }

				        $fecharesumen = $value["fechacomprobante"];

						$f = explode("-",$fecharesumen); $periodo = $f[0].$f[1].$f[2];
						if($codresumentipo==1){
							$resumenes = $this->db->query("select count(*) as cantidad from sunat.resumenes where periodo='".$periodo."' and codresumentipo=".$codresumentipo." and codempresa=".$_SESSION["phuyu_codempresa"])->result_array();
						}else{
							$resumenes = $this->db->query("select coalesce(max(nrocorrelativo),0) as cantidad from sunat.resumenes where periodo='".$periodo."' and (codresumentipo=3 or codresumentipo=4) and codempresa=".$_SESSION["phuyu_codempresa"])->result_array();
						}

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
							if($codresumentipo==1 || $codresumentipo==4){
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
							}else{
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

					if ($this->db->trans_status() === FALSE){
					    $this->db->trans_rollback(); $estado = 0; $mensaje = "NO SE PUEDE GEENERAR LOS RESUMENES DE ".$tipo;
					}else{
						if ($estado!=1) {
							$this->db->trans_rollback(); $estado = 0; $mensaje = "RESUMENES DE ".$tipo."GENERADOS CORRECTAMENTE";
						}else{
							$this->db->trans_commit(); $estado = 1; $mensaje = "RESUMENES DE ".$tipo."GENERADOS CORRECTAMENTE";
						}
					}
					$data["estado"] = $estado; $data["mensaje"] = $mensaje;
				}else{
					$data["estado"] = 0; $data["mensaje"]="NO EXISTEN ".$tipo." DE HABERLO YA FUERON GENERADOS EN UN RESUMEN";
				}
				echo json_encode($data);
			}else{
				echo "e";
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function resumenes_enviar($codresumentipo,$periodo,$nrocorrelativo){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_codusuario"])) {
				$resumen = $this->db->query("select *from sunat.resumenes where codresumentipo=".$codresumentipo." and periodo='".$periodo."' and nrocorrelativo=".$nrocorrelativo)->result_array();
				$empresa = $this->db->query("select *from public.webservice where codempresa=1")->result_array();

				if ($resumen[0]["ticket"]!="") {
					$credenciales = [$_SESSION["phuyu_ruc"],$empresa[0]["usuariosol"],$empresa[0]["clavesol"],$codresumentipo,$periodo,$nrocorrelativo,$_SESSION["phuyu_codempresa"]];
                    $estado = Sunat::phuyu_consultarTICKET($resumen[0]["nombre_xml"],$resumen[0]["ticket"],$credenciales);
					$mensaje = $estado["mensaje"]; $estado = $estado["estado"];
				}else{
					if ($codresumentipo==1) {
						$estado = $this->Facturacion_model->phuyu_rf_crearXML($periodo,$nrocorrelativo);
					}else{
						$estado = $this->Facturacion_model->phuyu_rb_crearXML($periodo,$nrocorrelativo,$codresumentipo);
					}
					
					if ($estado["estado"]!=0) {
						$firma = $this->phuyu_firmarXML($estado["carpeta_phuyu"]."/".$estado["archivo_phuyu"], 0, true);
						if ($firma["estado"]==1) {
							$credenciales = [$_SESSION["phuyu_ruc"],$empresa[0]["usuariosol"],$empresa[0]["clavesol"],$codresumentipo,$periodo,$nrocorrelativo,$_SESSION["phuyu_codempresa"]];
	                        $estado = Sunat::phuyu_enviarSUNAT("sendSummary",$estado["carpeta_phuyu"],$estado["archivo_phuyu"],$credenciales);
							$mensaje = $estado["mensaje"]; $estado = $estado["estado"];
						}else{
							$estado = 0; $mensaje = "NO SE PUEDE FIRMAR EL DOCUMENTO XML: ".$firma["mensaje"];
						}
					}else{
						$estado = 0; $mensaje = "NO SE PUEDE GENERAR EL DOCUMENTO XML";
					}
				}

				$data["estado"] = $estado; $data["mensaje"] = $mensaje;
				echo json_encode($data);
			}else{
				echo "e";
			}
		}
	}

	function resumenes_xml($codresumentipo,$periodo,$nrocorrelativo){
		$resumen = $this->db->query("select *from sunat.resumenes where codresumentipo=".$codresumentipo." and periodo='".$periodo."' and nrocorrelativo=".$nrocorrelativo." and codsucursal=".$_SESSION["phuyu_codsucursal"])->result_array();
		$empresa = $this->db->query("select *from public.empresas where codempresa=".$_SESSION["phuyu_codempresa"])->result_array();

		if ($codresumentipo==1) {
			$estado = $this->Facturacion_model->phuyu_rf_crearXML($periodo,$nrocorrelativo);
		}else{
			$estado = $this->Facturacion_model->phuyu_rb_crearXML($periodo,$nrocorrelativo,$codresumentipo);
		}
		
		if ($estado["estado"]!=0) {
			$firma = $this->phuyu_firmarXML($estado["carpeta_phuyu"]."/".$estado["archivo_phuyu"], 0, true);
			if ($firma["estado"]!=1) {
				echo "NO SE PUEDE FIRMAR EL DOCUMENTO XML: ".$firma["mensaje"];
				return;
			}
			
			$this->load->helper("download"); 
			$cpe_ruta = file_get_contents($estado["carpeta_phuyu"]."/".$estado["archivo_phuyu"].".xml");
			force_download($estado["archivo_phuyu"].".xml", $cpe_ruta);
		}else{
			echo "NO SE PUEDE GENERAR EL DOCUMENTO XML";
		}
	}

	function resumenes_cdr($codresumentipo,$periodo,$nrocorrelativo){
		if (isset($_SESSION["phuyu_codusuario"])) {
			$ruta = $this->db->query("select ruta_cdr from sunat.resumenes where codresumentipo=".$codresumentipo." and periodo='".$periodo."' and nrocorrelativo=".$nrocorrelativo." and codsucursal=".$_SESSION["phuyu_codsucursal"])->result_array();
			if ($ruta[0]["ruta_cdr"]!="") {
				$archivo = explode("R-",$ruta[0]["ruta_cdr"]);
				$this->load->helper("download"); 
				$descargar_ruta = file_get_contents($ruta[0]["ruta_cdr"].".zip");
				force_download("R-".$archivo[1].".zip", $descargar_ruta);
			}
		}
	}

	function resumenes_ver($codresumentipo,$periodo,$nrocorrelativo){
		if ($this->input->is_ajax_request()) {
			if ($codresumentipo==1 || $codresumentipo==4) {
				$lista = $this->db->query("select ka.*,k.cliente,k.seriecomprobante,k.nrocomprobante,k.fechacomprobante, k.importe from sunat.kardexsunatanulados as ka inner join kardex.kardex as k on(ka.codkardex=k.codkardex) where ka.codresumentipo=".$codresumentipo." and ka.nrocorrelativo=".$nrocorrelativo." and ka.periodo='".$periodo."' and ka.codsucursal=".$_SESSION["phuyu_codsucursal"])->result_array();
			}else{
				$lista = $this->db->query("select k.codkardex,k.cliente,k.seriecomprobante,k.nrocomprobante,k.fechacomprobante, k.importe from kardex.kardex as k inner join sunat.kardexsunatdetalle as ksd on(k.codkardex=ksd.codkardex) where ksd.codresumentipo=".$codresumentipo." and ksd.nrocorrelativo=".$nrocorrelativo." and ksd.periodo='".$periodo."' and ksd.codempresa=".$_SESSION["phuyu_codempresa"]." order by k.seriecomprobante,k.nrocomprobante")->result_array();
			}
			
			echo json_encode($lista);
		}
	}

	function resumenes_eliminar_kardex($codkardex,$codresumentipo,$periodo,$nrocorrelativo){
		if ($this->input->is_ajax_request()) {
			$this->db->where("codkardex", $codkardex);
			$this->db->where("codresumentipo", $codresumentipo);
			$this->db->where("nrocorrelativo", $nrocorrelativo);
			$this->db->where("periodo", $periodo);
			$estado = $this->db->delete("sunat.kardexsunatdetalle");

			echo $estado;
		}
	}

	function resumenes_actualizar($codresumentipo,$periodo,$nrocorrelativo){
		if ($this->input->is_ajax_request()) {
			$resumen = $this->db->query("select nombre_xml from sunat.resumenes where codresumentipo=".$codresumentipo." and nrocorrelativo=".$nrocorrelativo." and periodo='".$periodo."'")->result_array();

			$update = array(
				// "fechaenvio" => date("Y-m-d"), 
				"ruta_cdr" => "SIN CDR", 
                "descripcion_cdr" => "El Resumen numero ".$resumen[0]["nombre_xml"].", ha sido aceptada",
                "codigorespuesta" => "0", "estado" => 1
            );
            $this->db->where("codresumentipo",$codresumentipo);
            $this->db->where("nrocorrelativo",$nrocorrelativo);
            $this->db->where("periodo",$periodo);
            $estado = $this->db->update("sunat.resumenes", $update);

            if ($codresumentipo==1 || $codresumentipo==4) {
                $detalle = $this->db->query("select codkardex from sunat.kardexsunatanulados where codresumentipo=".$codresumentipo." and periodo='".$periodo."' and nrocorrelativo=".$nrocorrelativo)->result_array();
                foreach ($detalle as $value) {
                    $update = array("estado" => 1);
                    $this->db->where("codkardex",$value["codkardex"]);
                    $actualizaranulados = $this->db->update("sunat.kardexsunatanulados", $update);
                }
            }

            if ($codresumentipo==3 || $codresumentipo==5) {
                $detalle = $this->db->query("select codkardex from sunat.kardexsunatdetalle where codresumentipo=".$codresumentipo." and periodo='".$periodo."' and nrocorrelativo=".$nrocorrelativo)->result_array();
                foreach ($detalle as $value) {
                    $update = array(
                        // "fechaenvio" => date("Y-m-d"), 
                        "ruta_cdr" => "SIN CDR",
                        "descripcion_cdr" => "El Resumen numero ".$resumen[0]["nombre_xml"].", ha sido aceptada",
                        "codigorespuesta" => "0", "estado" => 1
                    );
                    $this->db->where("codkardex",$value["codkardex"]);
                    $actualizarkardex = $this->db->update("sunat.kardexsunat", $update);
                }

                $update = array(
                    "descripcion_cdr" => "El Resumen numero ".$resumen[0]["nombre_xml"].", ha sido aceptada",
                    // "fechaenvio" => date("Y-m-d"), 
                    "estado" => 1
                );
                $this->db->where("codresumentipo",$codresumentipo);
                $this->db->where("nrocorrelativo",$nrocorrelativo);
                $this->db->where("periodo",$periodo);
                $actualizarkardex = $this->db->update("sunat.kardexsunatdetalle", $update);
            }
            echo $estado;
		}
	}

	function resumenes_siguiente_correlativo($codresumentipo,$periodo,$nrocorrelativo){
		if ($this->input->is_ajax_request()) {
			$resumen = $this->db->query("select *from sunat.resumenes where codresumentipo=".$codresumentipo." and nrocorrelativo=".$nrocorrelativo." and periodo='".$periodo."'")->result_array();

			$nrocorrelativo_nuevo = (int)$nrocorrelativo + 1;
			$oficial = $this->db->query("select oficial from sunat.resumentipos where codresumentipo=".$codresumentipo)->result_array();
			$xml = $_SESSION["phuyu_ruc"]."-".$oficial[0]["oficial"]."-".$periodo."-".$nrocorrelativo_nuevo;
			
			$campos = ["codresumentipo","periodo","nrocorrelativo","codempresa","codsucursal","codusuario","nombre_xml","fecharesumen"];
			$valores = [
				(int)$codresumentipo,$periodo,(int)$nrocorrelativo_nuevo,
				(int)$_SESSION["phuyu_codempresa"],
				(int)$_SESSION["phuyu_codsucursal"],
				(int)$_SESSION["phuyu_codusuario"],
				$xml,$resumen[0]["fecharesumen"]
			];
			$estado = $this->phuyu_model->phuyu_guardar("sunat.resumenes", $campos, $valores);

			$data = array('nrocorrelativo' => $nrocorrelativo_nuevo);
			$this->db->where("codresumentipo", $codresumentipo);
			$this->db->where("nrocorrelativo", $nrocorrelativo);
			$this->db->where("periodo", $periodo);
			if ($codresumentipo==1 || $codresumentipo==4) {
				$estado = $this->db->update("sunat.kardexsunatanulados",$data);
			}else{
				$estado = $this->db->update("sunat.kardexsunatdetalle",$data);
			}
			
			$this->db->where("codresumentipo", $codresumentipo);
			$this->db->where("nrocorrelativo", $nrocorrelativo);
			$this->db->where("periodo", $periodo);
			$this->db->where("codsucursal", $_SESSION["phuyu_codsucursal"]);
			$estado = $this->db->delete("sunat.resumenes");

			echo $estado;
		}
	}

	function resumenes_quitar_ticket($codresumentipo,$periodo,$nrocorrelativo){
		if ($this->input->is_ajax_request()) {
			$datos = array("ticket" => "");
			$this->db->where("codresumentipo", $codresumentipo);
			$this->db->where("nrocorrelativo", $nrocorrelativo);
			$this->db->where("periodo", $periodo);
			$estado = $this->db->update("sunat.resumenes",$datos);

			echo $estado;
		}
	}

	function resumenes_anular($codresumentipo,$periodo,$nrocorrelativo){
		if ($this->input->is_ajax_request()) {
			if ($codresumentipo==1 || $codresumentipo==4) {
				$this->db->where("codresumentipo", $codresumentipo);
				$this->db->where("nrocorrelativo", $nrocorrelativo);
				$this->db->where("periodo", $periodo);
				$this->db->where("codsucursal", $_SESSION["phuyu_codsucursal"]);
				$estado = $this->db->delete("sunat.kardexsunatanulados");
			}else{
				$this->db->where("codresumentipo", $codresumentipo);
				$this->db->where("nrocorrelativo", $nrocorrelativo);
				$this->db->where("periodo", $periodo);
				$this->db->where("codempresa", $_SESSION["phuyu_codempresa"]);
				$estado = $this->db->delete("sunat.kardexsunatdetalle");
			}

			$this->db->where("codresumentipo", $codresumentipo);
			$this->db->where("nrocorrelativo", $nrocorrelativo);
			$this->db->where("periodo", $periodo);
			$this->db->where("codsucursal", $_SESSION["phuyu_codsucursal"]);
			$estado = $this->db->delete("sunat.resumenes");

			echo $estado;
		}
	}

	function phuyu_cookie_consulta_libre_sunat(){
		if (!function_exists("curl_init")) {
			return "";
		}
		$cookie = tempnam(sys_get_temp_dir(), "sunat_cpe_");
		if ($cookie===false || $cookie=="") {
			return "";
		}
		$inicio = curl_init("https://ww1.sunat.gob.pe/ol-ti-itconsultaunificadalibre/consultaUnificadaLibre/consulta");
		curl_setopt($inicio, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($inicio, CURLOPT_TIMEOUT, 20);
		curl_setopt($inicio, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($inicio, CURLOPT_COOKIEJAR, $cookie);
		curl_setopt($inicio, CURLOPT_COOKIEFILE, $cookie);
		curl_setopt($inicio, CURLOPT_USERAGENT, "Mozilla/5.0");
		curl_exec($inicio);
		curl_close($inicio);
		return $cookie;
	}

	function phuyu_consulta_libre_sunat($tipo, $serie, $numero, $fecha, $importe, $codDocRecep = "", $numDocRecep = "", $cookie_sunat = ""){
		$estados_cp = ["-" => "-", "0" => "NO EXISTE", "1" => "ACEPTADO", "2" => "ANULADO", "3" => "AUTORIZADO", "4" => "NO AUTORIZADO"];
		$estados_ruc = ["-" => "-", "00" => "ACTIVO", "01" => "BAJA PROVISIONAL", "02" => "BAJA PROV. POR OFICIO", "03" => "SUSPENSION TEMPORAL", "10" => "BAJA DEFINITIVA", "11" => "BAJA DE OFICIO", "12" => "BAJA MULT.INSCR. Y OTROS", "20" => "NUM. INTERNO IDENTIF.", "21" => "OTROS OBLIGADOS", "22" => "INHABILITADO-VENT.UNICA", "30" => "ANULACION - ERROR SUNAT"];
		$condiciones = ["-" => "-", "00" => "HABIDO", "01" => "NO HALLADO SE MUDO DE DOMICILIO", "02" => "NO HALLADO FALLECIO", "03" => "NO HALLADO NO EXISTE DOMICILIO", "04" => "NO HALLADO CERRADO", "05" => "NO HALLADO NRO.PUERTA NO EXISTE", "06" => "NO HALLADO DESTINATARIO DESCONOCIDO", "07" => "NO HALLADO RECHAZADO", "08" => "NO HALLADO OTROS MOTIVOS", "09" => "PENDIENTE"];
		$fecha_sunat = date("d/m/Y", strtotime($fecha));
		$numero_sunat = str_pad((string)$numero, 8, "0", STR_PAD_LEFT);
		$token = substr(md5(uniqid(mt_rand(), true)).md5(uniqid(mt_rand(), true)), 0, 52);
		$post = [
			"numRuc" => $_SESSION["phuyu_ruc"],
			"codComp" => $tipo,
			"numeroSerie" => strtoupper($serie),
			"numero" => $numero_sunat,
			"codDocRecep" => $codDocRecep,
			"numDocRecep" => $numDocRecep,
			"fechaEmision" => $fecha_sunat,
			"monto" => number_format((double)$importe, 2, ".", ""),
			"token" => $token
		];

		if (!function_exists("curl_init")) {
			return ["estado" => 0, "nivel" => "danger", "mensaje" => "No se pudo consultar SUNAT", "detalle" => "PHP no tiene cURL activo para conectar con la consulta publica."];
		}

		$curl = curl_init("https://ww1.sunat.gob.pe/ol-ti-itconsultaunificadalibre/consultaUnificadaLibre/consultaIndividual");
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_TIMEOUT, 30);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0");
		$respuesta = curl_exec($curl);
		$error = curl_error($curl);
		curl_close($curl);

		if ($respuesta === false || $respuesta === "") {
			return ["estado" => 0, "nivel" => "warning", "mensaje" => "SUNAT sin respuesta", "detalle" => $error !== "" ? $error : "El portal publico no devolvio informacion."];
		}

		$respuesta = trim($respuesta);
		$data = json_decode($respuesta, true);
		if (is_string($data)) {
			$data = json_decode($data, true);
		}
		if (!is_array($data)) {
			return ["estado" => 0, "nivel" => "warning", "mensaje" => "Respuesta temporal no valida", "detalle" => "SUNAT no devolvio el formato esperado. No significa que el comprobante este rechazado; vuelva a intentar en unos minutos."];
		}
		if (isset($data["rpta"]) && $data["rpta"]=="1" && isset($data["data"])) {
			$info = $data["data"];
			$estado_cp = isset($estados_cp[$info["estadoCp"]]) ? $estados_cp[$info["estadoCp"]] : $info["estadoCp"];
			$estado_ruc = isset($estados_ruc[$info["estadoRuc"]]) ? $estados_ruc[$info["estadoRuc"]] : $info["estadoRuc"];
			$condicion = isset($condiciones[$info["condDomiRuc"]]) ? $condiciones[$info["condDomiRuc"]] : $info["condDomiRuc"];
			$nivel = $info["estadoCp"]=="1" ? "success" : ($info["estadoCp"]=="0" ? "danger" : "warning");
			return ["estado" => $info["estadoCp"]=="1" ? 1 : 0, "nivel" => $nivel, "mensaje" => $estado_cp, "detalle" => "RUC: ".$estado_ruc." | DOMICILIO: ".$condicion, "estado_cp" => $estado_cp, "estado_ruc" => $estado_ruc, "condicion" => $condicion];
		}
		if (isset($data["rpta"]) && $data["rpta"]=="3") {
			return ["estado" => 0, "nivel" => "danger", "mensaje" => "No existe en SUNAT", "detalle" => "SUNAT no encontro el comprobante con los datos consultados."];
		}
		return ["estado" => 0, "nivel" => "warning", "mensaje" => "SUNAT no pudo procesar", "detalle" => isset($data["data"]) ? $data["data"] : "Intente nuevamente en unos minutos."];
	}

	function phuyu_datos_comprobante_sunat($tipo, $serie, $numero){
		$codtipo = $tipo=="03" ? 12 : ($tipo=="07" ? 14 : ($tipo=="08" ? 15 : 10));
		$numero = (string)$numero;
		$numero_sin_ceros = ltrim($numero, "0");
		if ($numero_sin_ceros=="") {
			$numero_sin_ceros = "0";
		}
		$numero_padded = str_pad($numero_sin_ceros, 8, "0", STR_PAD_LEFT);
		$where_numero = "(nrocomprobante=".$this->db->escape($numero)." or nrocomprobante=".$this->db->escape($numero_sin_ceros)." or nrocomprobante=".$this->db->escape($numero_padded)." or ltrim(nrocomprobante::text, '0')=".$this->db->escape($numero_sin_ceros).")";
		$exacto = $this->db->query("select codcomprobantetipo, case when codcomprobantetipo=14 then '07' when codcomprobantetipo=15 then '08' when codcomprobantetipo=12 then '03' when upper(seriecomprobante) like 'B%' then '03' else '01' end as tiposunat, fechacomprobante, round(importe,2) as importe from kardex.kardex where upper(seriecomprobante)=".$this->db->escape(strtoupper($serie))." and ".$where_numero." and codcomprobantetipo=".$codtipo." order by codkardex desc limit 1")->result_array();
		if (count($exacto)>0) {
			return $exacto;
		}
		return $this->db->query("select codcomprobantetipo, case when codcomprobantetipo=14 then '07' when codcomprobantetipo=15 then '08' when codcomprobantetipo=12 then '03' when upper(seriecomprobante) like 'B%' then '03' else '01' end as tiposunat, fechacomprobante, round(importe,2) as importe from kardex.kardex where upper(seriecomprobante)=".$this->db->escape(strtoupper($serie))." and ".$where_numero." and codcomprobantetipo in (10,12,14,15) order by codkardex desc limit 1")->result_array();
	}

	function phuyu_buscar_comprobante_sunat(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$local = $this->phuyu_datos_comprobante_sunat($this->request->tipo, $this->request->serie, $this->request->nrocomprobante);
			if (count($local)==0) {
				echo json_encode(["estado" => 0, "mensaje" => "Comprobante no encontrado en el sistema"]);
				return;
			}
			echo json_encode([
				"estado" => 1,
				"tipo" => $local[0]["tiposunat"],
				"fechaemision" => date("Y-m-d", strtotime($local[0]["fechacomprobante"])),
				"importe" => number_format((double)$local[0]["importe"], 2, ".", "")
			]);
		}
	}

	function phuyu_consultasunat(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$fecha = isset($this->request->fechaemision) ? $this->request->fechaemision : "";
			$importe = isset($this->request->importe) ? $this->request->importe : "";
			if ($fecha=="" || $importe=="") {
				$local = $this->phuyu_datos_comprobante_sunat($this->request->tipo, $this->request->serie, $this->request->nrocomprobante);
				if (count($local)>0) {
					$this->request->tipo = $local[0]["tiposunat"];
					$fecha = $local[0]["fechacomprobante"];
					$importe = $local[0]["importe"];
				}
			}
			if ($fecha=="" || $importe=="") {
				echo json_encode(["estado" => 0, "mensaje" => "Para consultar directo en SUNAT falta fecha de emision e importe total. Si el comprobante esta registrado localmente, revise serie y numero."]);
				return;
			}
			$data = $this->phuyu_consulta_libre_sunat($this->request->tipo, $this->request->serie, $this->request->nrocomprobante, $fecha, $importe);
			$data["tipo"] = $this->request->tipo;
			$data["fechaemision"] = date("Y-m-d", strtotime($fecha));
			$data["importe"] = number_format((double)$importe, 2, ".", "");
			echo json_encode($data);
		}
	}

	function phuyu_bloquesunat(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			$pagina = isset($this->request->pagina) ? (int)$this->request->pagina : 1;
			$limite = isset($this->request->limite) ? (int)$this->request->limite : 10;
			if ($pagina < 1) {
				$pagina = 1;
			}
			if ($limite < 1 || $limite > 50) {
				$limite = 10;
			}
			$offset = ($pagina * $limite) - $limite;
			$tipo_periodo = isset($this->request->tipo_periodo) ? $this->request->tipo_periodo : "todos";
			$where_tipo_periodo = "ct.oficial in ('01','03','07','08')";
			if ($tipo_periodo=="facturas") {
				$where_tipo_periodo = "ct.oficial='01'";
			}
			if ($tipo_periodo=="boletas") {
				$where_tipo_periodo = "ct.oficial='03'";
			}
			if ($tipo_periodo=="notas_credito") {
				$where_tipo_periodo = "ct.oficial='07'";
			}
			if ($tipo_periodo=="notas_debito") {
				$where_tipo_periodo = "ct.oficial='08'";
			}

			$total = $this->db->query("select count(*) as total from kardex.kardex as kardex inner join caja.comprobantetipos as ct on(kardex.codcomprobantetipo=ct.codcomprobantetipo) where kardex.fechacomprobante>='".$this->request->fdesde."' and kardex.fechacomprobante<='".$this->request->fhasta."' and kardex.codmovimientotipo in (8,20) and ".$where_tipo_periodo)->result_array();
			$lista = $this->db->query("select personas.documento, kardex.cliente, kardex.codkardex, kardex.codcomprobantetipo, ct.oficial as tipo_sunat, ct.descripcion as tipocomprobante, kardex.seriecomprobante, kardex.nrocomprobante,kardex.fechacomprobante,round(kardex.importe,2) as importe,coalesce(kardexs.estado,0) as estado from kardex.kardex as kardex inner join caja.comprobantetipos as ct on(kardex.codcomprobantetipo=ct.codcomprobantetipo) left join sunat.kardexsunat as kardexs on(kardex.codkardex=kardexs.codkardex) inner join public.personas as personas on (kardex.codpersona=personas.codpersona) where kardex.fechacomprobante>='".$this->request->fdesde."' and kardex.fechacomprobante<='".$this->request->fhasta."' and kardex.codmovimientotipo in (8,20) and ".$where_tipo_periodo." order by kardex.codkardex asc offset ".$offset." limit ".$limite)->result_array();
			foreach ($lista as $key => $value) {
				$tipo_sunat = $value["tipo_sunat"];
				$estado = $this->phuyu_consulta_libre_sunat($tipo_sunat, $value["seriecomprobante"], $value["nrocomprobante"], $value["fechacomprobante"], $value["importe"]);
				$mensaje_sunat = (isset($estado["mensaje"]) && trim($estado["mensaje"])!="") ? $estado["mensaje"] : "Sin respuesta de SUNAT";
				$detalle_sunat = isset($estado["detalle"]) ? $estado["detalle"] : "";
				$lista[$key]["descripcion"] = $detalle_sunat!="" ? $mensaje_sunat." | ".$detalle_sunat : $mensaje_sunat;
				$lista[$key]["mensaje_sunat"] = $mensaje_sunat;
				$lista[$key]["detalle_sunat"] = $detalle_sunat;
				$lista[$key]["nivel_sunat"] = isset($estado["nivel"]) ? $estado["nivel"] : "warning";
				$lista[$key]["estado_sunat_directo"] = isset($estado["estado"]) ? $estado["estado"] : 0;
				usleep(250000);
			}
			$total_registros = (int)$total[0]["total"];
			$ultima = $total_registros > 0 ? (int)ceil($total_registros / $limite) : 1;
			echo json_encode([
				"lista" => $lista,
				"paginacion" => [
					"total" => $total_registros,
					"actual" => $pagina,
					"ultima" => $ultima,
					"limite" => $limite,
					"desde" => $offset + 1,
					"hasta" => min($offset + $limite, $total_registros)
				]
			]);
		}
	}

	// FACTURACION ELECTRONICA: REPORTES GENERADOS //

	function phuyu_datos_cpe(){
		if ($this->input->is_ajax_request()) {
			$facturas = array(); $boletas = array();

			// DATOS FACTURAS //
			$enviados = $this->db->query("select count(*) as cantidad from kardex.kardex as k inner join sunat.kardexsunat as ks on(k.codkardex=ks.codkardex) where k.codmovimientotipo=20 and k.codcomprobantetipo=10 and (ks.estado=1 or ks.estado=2)")->result_array();
			$anulados = $this->db->query("select count(*) as cantidad from kardex.kardex as k inner join sunat.kardexsunatanulados as ksa on(k.codkardex=ksa.codkardex) where k.codmovimientotipo=20 and k.codcomprobantetipo=10 and (ksa.estado=1 or ksa.estado=2)")->result_array();
			$pendientes = $this->db->query("select count(*) as cantidad from kardex.kardex as kardex inner join sunat.kardexsunat as kardexs on(kardex.codkardex=kardexs.codkardex) where kardexs.estado<>1 and kardexs.estado<>2 and kardex.codmovimientotipo=20 and kardex.codcomprobantetipo=10")->result_array();

			$facturas["enviados"] = (double)$enviados[0]["cantidad"] - $anulados[0]["cantidad"];
			$facturas["anulados"] = (double)$anulados[0]["cantidad"];
			$facturas["pendientes"] = (double)$pendientes[0]["cantidad"];

			// DATOS BOLETAS //
			$enviados = $this->db->query("select count(*) as cantidad from kardex.kardex as k inner join sunat.kardexsunat as ks on(k.codkardex=ks.codkardex) where k.codmovimientotipo=20 and k.codcomprobantetipo=12 and (ks.estado=1 or ks.estado=2)")->result_array();
			$anulados = $this->db->query("select count(*) as cantidad from kardex.kardex as k inner join sunat.kardexsunatanulados as ksa on(k.codkardex=ksa.codkardex) where k.codmovimientotipo=20 and k.codcomprobantetipo=12 and (ksa.estado=1 or ksa.estado=2)")->result_array();
			$pendientes = $this->db->query("select count(*) as cantidad from sunat.resumenes where codresumentipo=3 and estado<>1 and estado<>2")->result_array();

			$boletas["enviados"] = (double)$enviados[0]["cantidad"] - $anulados[0]["cantidad"];
			$boletas["anulados"] = (double)$anulados[0]["cantidad"];
			$boletas["pendientes"] = (double)$pendientes[0]["cantidad"];

			$data["facturas"] = $facturas; $data["boletas"] = $boletas;
			echo json_encode($data);
		}
	}

	function reporte_facturas_enviados($fdesde,$fhasta){
		if ($this->input->is_ajax_request()) {
			$lista = $this->db->query("select personas.documento, personas.razonsocial, kardex.codkardex, kardex.seriecomprobante, kardex.nrocomprobante,kardex.fechacomprobante,round(kardex.importe,2) as importe,kardexs.descripcion_cdr as sunat from kardex.kardex as kardex inner join sunat.kardexsunat as kardexs on(kardex.codkardex=kardexs.codkardex) inner join public.personas as personas on (kardex.codpersona=personas.codpersona) where kardex.fechacomprobante>='".$fdesde."' and kardex.fechacomprobante<='".$fhasta."' and kardex.codmovimientotipo=20 and kardex.codcomprobantetipo=10 order by kardex.seriecomprobante,kardex.nrocomprobante")->result_array();
			echo json_encode($lista);
		}
	}
	function reporte_facturas_anulados($fdesde,$fhasta){
		if ($this->input->is_ajax_request()) {
			$resumenes = $this->db->query("select *from sunat.resumenes where fecharesumen>='".$fdesde."' and fecharesumen<='".$fhasta."' and codresumentipo=1 order by fecharesumen")->result_array();
			foreach ($resumenes as $key => $value) {
				$lista = $this->db->query("select personas.documento, personas.razonsocial, kardex.seriecomprobante, kardex.nrocomprobante, round(kardex.importe,2) as importe,kardexa.motivobaja as motivo from kardex.kardex as kardex inner join sunat.kardexsunatanulados as kardexa on(kardex.codkardex=kardexa.codkardex) inner join public.personas as personas on (kardex.codpersona=personas.codpersona) where kardexa.codresumentipo=".$value["codresumentipo"]." and kardexa.periodo='".$value["periodo"]."' and kardexa.nrocorrelativo=".$value["nrocorrelativo"]." and kardexa.codempresa=".$value["codempresa"]." order by kardex.seriecomprobante,kardex.nrocomprobante")->result_array();
				$resumenes[$key]["lista"] = $lista;
			}
			echo json_encode($resumenes);
		}
	}
	function reporte_boletas_enviados($fdesde,$fhasta){
		if ($this->input->is_ajax_request()) {
			$resumenes = $this->db->query("select *from sunat.resumenes where fecharesumen>='".$fdesde."' and fecharesumen<='".$fhasta."' and codresumentipo=3 order by fecharesumen")->result_array();
			foreach ($resumenes as $key => $value) {
				$lista = $this->db->query("select personas.documento, personas.razonsocial, kardex.seriecomprobante, kardex.nrocomprobante, round(kardex.importe,2) as importe,'-' as motivo from kardex.kardex as kardex inner join sunat.kardexsunatdetalle as kardexsd on(kardex.codkardex=kardexsd.codkardex) inner join public.personas as personas on (kardex.codpersona=personas.codpersona) where kardexsd.codresumentipo=".$value["codresumentipo"]." and kardexsd.periodo='".$value["periodo"]."' and kardexsd.nrocorrelativo=".$value["nrocorrelativo"]." and kardexsd.codempresa=".$value["codempresa"]." order by kardex.seriecomprobante,kardex.nrocomprobante")->result_array();
				$resumenes[$key]["lista"] = $lista;
			}
			echo json_encode($resumenes);
		}
	}
	function reporte_boletas_anulados($fdesde,$fhasta){
		if ($this->input->is_ajax_request()) {
			$resumenes = $this->db->query("select *from sunat.resumenes where fecharesumen>='".$fdesde."' and fecharesumen<='".$fhasta."' and codresumentipo=4 order by fecharesumen")->result_array();
			foreach ($resumenes as $key => $value) {
				$lista = $this->db->query("select personas.documento, personas.razonsocial, kardex.seriecomprobante, kardex.nrocomprobante, round(kardex.importe,2) as importe,kardexa.motivobaja as motivo from kardex.kardex as kardex inner join sunat.kardexsunatanulados as kardexa on(kardex.codkardex=kardexa.codkardex) inner join public.personas as personas on (kardex.codpersona=personas.codpersona) where kardexa.codresumentipo=".$value["codresumentipo"]." and kardexa.periodo='".$value["periodo"]."' and kardexa.nrocorrelativo=".$value["nrocorrelativo"]." and kardexa.codempresa=".$value["codempresa"]." order by kardex.seriecomprobante,kardex.nrocomprobante")->result_array();
				$resumenes[$key]["lista"] = $lista;
			}
			echo json_encode($resumenes);
		}
	}

	function consulta_cdr($ticket){
		
	}

	function kardex_faltantes(){
		// $lista = $this->db->query("select *from kardex.kardex where (codcomprobantetipo=12 or codcomprobantetipo=10) and codkardex not in (select codkardex from sunat.kardexsunat) limit 10000")->result_array();
		$lista = $this->db->query("select *from kardex.kardex where fechacomprobante>='2019-10-01' and (codcomprobantetipo=12 or codcomprobantetipo=10) and codkardex not in (select codkardex from sunat.kardexsunat)")->result_array();
		foreach ($lista as $key => $value) {
			if ($value["codcomprobantetipo"]==10) {
				$xml = $_SESSION["phuyu_ruc"]."-01-".$value["seriecomprobante"]."-".$value["nrocomprobante"];
			}else{
				$xml = $_SESSION["phuyu_ruc"]."-03-".$value["seriecomprobante"]."-".$value["nrocomprobante"];
			}
			$campos = ["codkardex","codsucursal","codusuario","fechacreado","nombre_xml"];
			$valores = [
				(int)$value["codkardex"],(int)$_SESSION["phuyu_codsucursal"],(int)$_SESSION["phuyu_codusuario"],
				$value["fechacomprobante"], $xml
			];
			$estado = $this->phuyu_model->phuyu_guardar("sunat.kardexsunat", $campos, $valores);
		}
		echo count($lista);
	}

	function kardex_sinicbper(){
		$lista = $this->db->query("select *from sunat.kardexsunat where estado=0")->result_array();
		foreach ($lista as $key => $value) {
			$detalle = $this->db->query("select *from kardex.kardexdetalle where codkardex=".$value["codkardex"])->result_array();
			$importe = 0;
			foreach ($detalle as $v) {
				$importe = $importe + $v["valorventa"];

				$data = array(
					'conicbper' => 0,
					'icbper' => 0,
					'subtotal' => $v["valorventa"]
				);

				$this->db->where("codkardex",$v["codkardex"]);
				$this->db->where("codproducto",$v["codproducto"]);
				$this->db->where("codunidad",$v["codunidad"]);
				$this->db->where("item",$v["item"]);
				$estado = $this->db->update("kardex.kardexdetalle",$data);
			}

			$data = array(
				'icbper' => 0,
				'importe' => round($importe,2)
			);
			$this->db->where("codkardex",$value["codkardex"]);
			$estado = $this->db->update("kardex.kardex",$data);

			$movimiento = $this->db->query("select codmovimiento from caja.movimientos where codkardex=".$value["codkardex"])->result_array();
			if (count($movimiento)>0) {
				$data = array('importe' => round($importe,2) );
				$this->db->where("codmovimiento",$movimiento[0]["codmovimiento"]);
				$estado = $this->db->update("caja.movimientos",$data);

				$data = array(
					'importe' => round($importe,2),
					'importeentregado' => round($importe,2),
					'vuelto' => 0
				);
				$this->db->where("codmovimiento",$movimiento[0]["codmovimiento"]);
				$estado = $this->db->update("caja.movimientosdetalle",$data);
			}

			echo "COD KARDEX = ".$value["codkardex"]."<br>";
		}
	}
}
