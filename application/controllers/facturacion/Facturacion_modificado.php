<?php defined('BASEPATH') or exit('No direct script access allowed');
include 'Sunat.php';

class Facturacion extends Sunat
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('phuyu_model');
		$this->load->model('Facturacion_model');
	}

	public function index()
	{
		if ($this->input->is_ajax_request()) {
			$this->load->view('facturacion/facturacion/index');
		} else {
			$this->load->view('phuyu/404');
		}
	}

	function comprobantes()
	{
		if ($this->input->is_ajax_request()) {
			$facturas = $this->db->query('select personas.documento, kardex.cliente, kardex.codkardex, kardex.seriecomprobante, kardex.nrocomprobante,kardex.fechacomprobante,round(kardex.importe,2) as importe,kardexs.estado from kardex.kardex as kardex inner join sunat.kardexsunat as kardexs on(kardex.codkardex=kardexs.codkardex) inner join public.personas as personas on (kardex.codpersona=personas.codpersona) where kardexs.estado<>1 and kardex.codmovimientotipo=20 and kardex.codcomprobantetipo=10 order by kardex.codkardex asc')->result_array();

			$data['facturas'] = $facturas;
			echo json_encode($data);
		}
	}

	function notas()
	{
		if ($this->input->is_ajax_request()) {
			$facturas = $this->db->query('select personas.documento, kardex.cliente, kardex.codkardex, kardex.seriecomprobante, kardex.nrocomprobante,kardex.fechacomprobante,round(kardex.importe,2) as importe,kardexs.estado from kardex.kardex as kardex inner join sunat.kardexsunat as kardexs on(kardex.codkardex=kardexs.codkardex) inner join public.personas as personas on (kardex.codpersona=personas.codpersona) where kardexs.estado<>1 and kardex.codmovimientotipo=8 and kardex.codcomprobantetipo=14 order by kardex.codkardex asc')->result_array();

			$data['notas'] = $facturas;
			echo json_encode($data);
		}
	}

	function guias()
	{
		if ($this->input->is_ajax_request()) {
			$guias = $this->db->query('select motivos.descripcion AS motivo,personas.documento, guiasr.destinatario, guiasr.codguiar, guiasr.seriecomprobante, guiasr.nrocomprobante,guiasr.fechaguia,guiasrs.estado from almacen.guiasr as guiasr inner join sunat.guiasunat as guiasrs on(guiasr.codguiar=guiasrs.codguiar) inner join public.personas as personas on (guiasr.codpersona=personas.codpersona) inner join almacen.motivotraslado as motivos on(guiasr.codmotivotraslado=motivos.codmotivotraslado) where guiasrs.estado<>1 order by guiasr.codguiar asc')->result_array();

			$data['guias'] = $guias;
			echo json_encode($data);
		}
	}

	function comprobantes_enviar($codkardex, $codoficial)
	{
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION['phuyu_codusuario'])) {
				$empresa = $this->db->query('select *from public.webservice where codempresa=1')->result_array();

				$estado = $this->Facturacion_model->phuyu_crearXML($codoficial, $codkardex);
				if ($estado['estado'] != 0) {
					$firma = Sunat::phuyu_firmarXML($estado['carpeta_phuyu'] . '/' . $estado['archivo_phuyu'], 0);
					if ($firma == 1) {
						$credenciales = [$_SESSION['phuyu_ruc'], $empresa[0]['usuariosol'], $empresa[0]['clavesol'], $codkardex];
						$estado = Sunat::phuyu_enviarSUNAT('sendBill', $estado['carpeta_phuyu'], $estado['archivo_phuyu'], $credenciales);
						$mensaje = $estado['mensaje'];
						$estado = $estado['estado'];
						$alerta = 'success';
					} else {
						$estado = 0;
						$mensaje = 'NO SE PUEDE FIRMAR EL DOCUMENTO XML';
						$alerta = 'error';
					}
				} else {
					$estado = 0;
					$mensaje = 'NO SE PUEDE GENERAR EL DOCUMENTO XML';
					$alerta = 'error';
				}

				$data['estado'] = $estado;
				$data['mensaje'] = $mensaje;
				$data['alerta'] = $alerta;
				echo json_encode($data);
			} else {
				echo 'e';
			}
		}
	}

	function guias_enviar($codguiar, $codoficial)
	{
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION['phuyu_codusuario'])) {
				$empresa = $this->db->query('select *from public.webservice where codempresa=1')->result_array();

				$estado = $this->Facturacion_model->phuyu_crearXMLGUIAS($codoficial, $codguiar);
				if ($estado['estado'] != 0) {
					$firma = Sunat::phuyu_firmarXML($estado['carpeta_phuyu'] . '/' . $estado['archivo_phuyu'], 0);
					if ($firma == 1) {
						$credenciales = [$_SESSION['phuyu_ruc'], $empresa[0]['usuariosol'], $empresa[0]['clavesol'], $codguiar];
						$estado = Sunat::phuyu_enviarSUNATGUIA('sendBill', $estado['carpeta_phuyu'], $estado['archivo_phuyu'], $credenciales);
						$mensaje = $estado['mensaje'];
						$estado = $estado['estado'];
						$alerta = 'success';
					} else {
						$estado = 0;
						$mensaje = 'NO SE PUEDE FIRMAR EL DOCUMENTO XML';
						$alerta = 'error';
					}
				} else {
					$estado = 0;
					$mensaje = 'NO SE PUEDE GENERAR EL DOCUMENTO XML';
					$alerta = 'error';
				}

				$data['estado'] = $estado;
				$data['mensaje'] = $mensaje;
				$data['alerta'] = $alerta;
				echo json_encode($data);
			} else {
				echo 'e';
			}
		}
	}

	function comprobantes_xml($codkardex, $codoficial)
	{
		if (isset($_SESSION['phuyu_codusuario'])) {
			$estado = $this->Facturacion_model->phuyu_crearXML($codoficial, $codkardex);
			if ($estado['estado'] != 0) {
				$firma = Sunat::phuyu_firmarXML($estado['carpeta_phuyu'] . '/' . $estado['archivo_phuyu'], 0);

				$this->load->helper('download');
				$descargar_ruta = file_get_contents($estado['carpeta_phuyu'] . '/' . $estado['archivo_phuyu'] . '.xml');
				force_download($estado['archivo_phuyu'] . '.xml', $descargar_ruta);
			}
		}
	}

	function guias_xml($codguiar, $codoficial)
	{
		if (isset($_SESSION['phuyu_codusuario'])) {
			$estado = $this->Facturacion_model->phuyu_crearXMLGUIAS($codoficial, $codguiar);
			if ($estado['estado'] != 0) {
				$firma = Sunat::phuyu_firmarXML($estado['carpeta_phuyu'] . '/' . $estado['archivo_phuyu'], 0);

				$this->load->helper('download');
				$descargar_ruta = file_get_contents($estado['carpeta_phuyu'] . '/' . $estado['archivo_phuyu'] . '.xml');
				force_download($estado['archivo_phuyu'] . '.xml', $descargar_ruta);
			}
		}
	}

	function comprobantes_cdr($codkardex)
	{
		if (isset($_SESSION['phuyu_codusuario'])) {
			$ruta = $this->db->query('select ruta_cdr from sunat.kardexsunat where codkardex=' . $codkardex)->result_array();
			if ($ruta[0]['ruta_cdr'] != '') {
				$archivo = explode('R-', $ruta[0]['ruta_cdr']);
				$this->load->helper('download');
				$descargar_ruta = file_get_contents($ruta[0]['ruta_cdr'] . '.zip');
				force_download('R-' . $archivo[1] . '.zip', $descargar_ruta);
			}
		}
	}

	function resumenes()
	{
		if ($this->input->is_ajax_request()) {
			$facturas_anuladas = $this->db->query('select *from sunat.resumenes where codresumentipo=1 and estado<>1 order by fecharesumen')->result_array();
			$resumen_boletas = $this->db->query('select *from sunat.resumenes where (codresumentipo=3 or codresumentipo=4) and estado<>1 order by fecharesumen')->result_array();

			$data['facturas_anuladas'] = $facturas_anuladas;
			$data['resumenes_boletas'] = $resumen_boletas;
			echo json_encode($data);
		}
	}

	function resumenes_generar($codresumentipo, $fecha)
	{
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION['phuyu_codusuario'])) {
				if ($codresumentipo == 1) {
					$fechas_resumen = $this->db->query("select distinct(k.fechacomprobante) as fechacomprobante from kardex.kardexanulados as ka inner join kardex.kardex as k on(ka.codkardex=k.codkardex) inner join sunat.kardexsunat as ks on(k.codkardex=ks.codkardex) where ka.fechaanulacion<='" . $fecha . "' and k.codmovimientotipo=20 and k.codcomprobantetipo=10 and (ks.estado=1 or ks.estado=2) and k.codkardex not in (select codkardex from sunat.kardexsunatanulados where fechaanulacion<='" . $fecha . "') ")->result_array();
					$tipo = 'FACTURAS ANULADAS';
				} elseif ($codresumentipo == 3) {
					$fechas_resumen = $this->db->query("select distinct(kardex.fechacomprobante) as fechacomprobante from kardex.kardex as kardex inner join sunat.kardexsunat as kardexs on(kardex.codkardex=kardexs.codkardex) where kardexs.fechacreado<='" . $fecha . "' and kardex.codmovimientotipo=20 and kardex.codcomprobantetipo=12 and kardex.codkardex not in (select codkardex from sunat.kardexsunatdetalle where fecharesumen<='" . $fecha . "')")->result_array();
					$tipo = 'BOLETAS';
				} else {
					$fechas_resumen = $this->db->query("select distinct(k.fechacomprobante) as fechacomprobante from kardex.kardexanulados as ka inner join kardex.kardex as k on(ka.codkardex=k.codkardex) inner join sunat.kardexsunat as ks on(k.codkardex=ks.codkardex) where ka.fechaanulacion<='" . $fecha . "' and k.codmovimientotipo=20 and k.codcomprobantetipo=12 and (ks.estado=1 or ks.estado=2) and k.codkardex not in (select codkardex from sunat.kardexsunatanulados where fechaanulacion<='" . $fecha . "')")->result_array();
					$tipo = 'BOLETAS ANULADAS';
				}

				if (count($fechas_resumen) > 0) {
					$this->db->trans_begin();

					foreach ($fechas_resumen as $key => $value) {
						if ($codresumentipo == 1) {
							$lista = $this->db->query("select ka.codkardex,ka.observaciones from kardex.kardexanulados as ka inner join kardex.kardex as k on(ka.codkardex=k.codkardex) inner join sunat.kardexsunat as ks on(k.codkardex=ks.codkardex) where k.fechacomprobante='" . $value['fechacomprobante'] . "' and ka.fechaanulacion<='" . $fecha . "' and k.codmovimientotipo=20 and k.codcomprobantetipo=10 and (ks.estado=1 or ks.estado=2) and k.codkardex not in (select codkardex from sunat.kardexsunatanulados where fechaanulacion<='" . $fecha . "') ")->result_array();
						} elseif ($codresumentipo == 3) {
							$lista = $this->db->query("select kardex.codkardex from kardex.kardex as kardex inner join sunat.kardexsunat as kardexs on(kardex.codkardex=kardexs.codkardex) where kardex.fechacomprobante='" . $value['fechacomprobante'] . "' and kardex.codmovimientotipo=20 and kardex.codcomprobantetipo=12 and kardex.codkardex not in (select codkardex from sunat.kardexsunatdetalle where fecharesumen<='" . $fecha . "')")->result_array();
						} else {
							$lista = $this->db->query("select ka.codkardex,ka.observaciones from kardex.kardexanulados as ka inner join kardex.kardex as k on(ka.codkardex=k.codkardex) inner join sunat.kardexsunat as ks on(k.codkardex=ks.codkardex) where k.fechacomprobante='" . $value['fechacomprobante'] . "' and ka.fechaanulacion<='" . $fecha . "' and k.codmovimientotipo=20 and k.codcomprobantetipo=12 and (ks.estado=1 or ks.estado=2) and k.codkardex not in (select codkardex from sunat.kardexsunatanulados where fechaanulacion<='" . $fecha . "') ")->result_array();
						}

						$fecharesumen = $value['fechacomprobante'];

						$f = explode('-', $fecharesumen);
						$periodo = $f[0] . $f[1] . $f[2];
						if ($codresumentipo == 1) {
							$resumenes = $this->db->query("select count(*) as cantidad from sunat.resumenes where periodo='" . $periodo . "' and codresumentipo=" . $codresumentipo . ' and codempresa=' . $_SESSION['phuyu_codempresa'])->result_array();
						} else {
							$resumenes = $this->db->query("select coalesce(max(nrocorrelativo),0) as cantidad from sunat.resumenes where periodo='" . $periodo . "' and (codresumentipo=3 or codresumentipo=4) and codempresa=" . $_SESSION['phuyu_codempresa'])->result_array();
						}

						$nrocorrelativo = $resumenes[0]['cantidad'] + 1;
						$oficial = $this->db->query('select oficial from sunat.resumentipos where codresumentipo=' . $codresumentipo)->result_array();
						$xml = $_SESSION['phuyu_ruc'] . '-' . $oficial[0]['oficial'] . '-' . $periodo . '-' . $nrocorrelativo;

						$campos = ['codresumentipo', 'periodo', 'nrocorrelativo', 'codempresa', 'codsucursal', 'codusuario', 'nombre_xml', 'fecharesumen'];
						$valores = [(int) $codresumentipo, $periodo, (int) $nrocorrelativo, (int) $_SESSION['phuyu_codempresa'], (int) $_SESSION['phuyu_codsucursal'], (int) $_SESSION['phuyu_codusuario'], $xml, $fecharesumen];
						$estado = $this->phuyu_model->phuyu_guardar('sunat.resumenes', $campos, $valores);

						foreach ($lista as $val) {
							if ($codresumentipo == 1 || $codresumentipo == 4) {
								$campos = ['codkardex', 'codresumentipo', 'nrocorrelativo', 'periodo', 'codempresa', 'codsucursal', 'fechaanulacion', 'fechareferencia', 'motivobaja'];
								$valores = [(int) $val['codkardex'], (int) $codresumentipo, (int) $nrocorrelativo, $periodo, (int) $_SESSION['phuyu_codempresa'], (int) $_SESSION['phuyu_codsucursal'], $fecha, $fecharesumen, $val['observaciones']];
								$estado = $this->phuyu_model->phuyu_guardar('sunat.kardexsunatanulados', $campos, $valores);
							} else {
								$campos = ['codkardex', 'codresumentipo', 'nrocorrelativo', 'periodo', 'codempresa', 'fecharesumen'];
								$valores = [(int) $val['codkardex'], (int) $codresumentipo, (int) $nrocorrelativo, $periodo, (int) $_SESSION['phuyu_codempresa'], $fecharesumen];
								$estado = $this->phuyu_model->phuyu_guardar('sunat.kardexsunatdetalle', $campos, $valores);
							}
						}
					}

					if ($this->db->trans_status() === false) {
						$this->db->trans_rollback();
						$estado = 0;
						$mensaje = 'NO SE PUEDE GEENERAR LOS RESUMENES DE ' . $tipo;
					} else {
						if ($estado != 1) {
							$this->db->trans_rollback();
							$estado = 0;
							$mensaje = 'RESUMENES DE ' . $tipo . 'GENERADOS CORRECTAMENTE';
						} else {
							$this->db->trans_commit();
							$estado = 1;
							$mensaje = 'RESUMENES DE ' . $tipo . 'GENERADOS CORRECTAMENTE';
						}
					}
					$data['estado'] = $estado;
					$data['mensaje'] = $mensaje;
				} else {
					$data['estado'] = 0;
					$data['mensaje'] = 'NO EXISTEN ' . $tipo . ' DE HABERLO YA FUERON GENERADOS EN UN RESUMEN';
				}
				echo json_encode($data);
			} else {
				echo 'e';
			}
		} else {
			$this->load->view('phuyu/404');
		}
	}

	function resumenes_enviar_original($codresumentipo, $periodo, $nrocorrelativo)
	{
		//print_r( $_SESSION);
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION['phuyu_codusuario'])) {
				$resumen = $this->db->query('select *from sunat.resumenes where codresumentipo=' . $codresumentipo . " and periodo='" . $periodo . "' and nrocorrelativo=" . $nrocorrelativo)->result_array();
				$empresa = $this->db->query('select *from public.webservice where codempresa=1')->result_array();

				if ($resumen[0]['ticket'] != '') {
					$credenciales = [$_SESSION['phuyu_ruc'], $empresa[0]['usuariosol'], $empresa[0]['clavesol'], $codresumentipo, $periodo, $nrocorrelativo, $_SESSION['phuyu_codempresa']];
					$estado = Sunat::phuyu_consultarTICKET($resumen[0]['nombre_xml'], $resumen[0]['ticket'], $credenciales);
					$mensaje = $estado['mensaje'];
					$estado = $estado['estado'];
				} else {
					if ($codresumentipo == 1) {
						$estado = $this->Facturacion_model->phuyu_rf_crearXML($periodo, $nrocorrelativo);
					} else {
						$estado = $this->Facturacion_model->phuyu_rb_crearXML($periodo, $nrocorrelativo, $codresumentipo);
					}

					if ($estado['estado'] != 0) {
						$firma = Sunat::phuyu_firmarXML($estado['carpeta_phuyu'] . '/' . $estado['archivo_phuyu'], 0);
						if ($firma == 1) {
							$credenciales = [$_SESSION['phuyu_ruc'], $empresa[0]['usuariosol'], $empresa[0]['clavesol'], $codresumentipo, $periodo, $nrocorrelativo, $_SESSION['phuyu_codempresa']];
							$estado = Sunat::phuyu_enviarSUNAT('sendSummary', $estado['carpeta_phuyu'], $estado['archivo_phuyu'], $credenciales);
							$mensaje = $estado['mensaje'];
							$estado = $estado['estado'];
						} else {
							$estado = 0;
							$mensaje = 'NO SE PUEDE FIRMAR EL DOCUMENTO XML';
						}
					} else {
						$estado = 0;
						$mensaje = 'NO SE PUEDE GENERAR EL DOCUMENTO XML';
					}
				}

				$data['estado'] = $estado;
				$data['mensaje'] = $mensaje;
				echo json_encode($data);
			} else {
				echo 'e';
			} 
		}
	}
	function resumenes_enviar($codresumentipo, $periodo, $nrocorrelativo)
	{
		if (!$this->input->is_ajax_request()) {$this->load->view('phuyu/404');	return;}
		if (!isset($_SESSION['phuyu_codusuario'])) {	echo json_encode(['estado' => 0, 'mensaje' => 'sesion_expirada']);	return;}

		// ========== CONFIGURACIÓN DE LOGS ==========
		// Base path seguro (CodeIgniter FCPATH) o intento de resolución
		if (defined('FCPATH')) {	$basePath = rtrim(FCPATH, '/') . '/';} 
		else {	$basePath = realpath(__DIR__ . '/../../../') . '/';}
		// Asegurarse que carpeta logs existe
		$logDir = $basePath . 'sunat/logs';
		if (!is_dir($logDir)) {	@mkdir($logDir, 0777, true);}
		// Helpers de log
			$log = function ($msg) use ($logDir) {
				$file = $logDir . '/resumenes_enviar_' . date('Ymd') . '.log';
				@file_put_contents($file, date('[Y-m-d H:i:s] ') . $msg . "\n", FILE_APPEND);
			};
		// ========== FIN CONFIGURACIÓN LOGS ==========

		// Obtener resumen y empresa
		$resumen = $this->db->where('codresumentipo', $codresumentipo)->where('periodo', $periodo)->where('nrocorrelativo', $nrocorrelativo)->get('sunat.resumenes')->result_array();
		$empresa = $this->db->where('codempresa', $_SESSION['phuyu_codempresa'])->get('public.webservice')->result_array();

		//valifacion de empresa y de resumen
		if (empty($resumen)) {	echo json_encode(['estado' => 0, 'mensaje' => 'resumen_no_encontrado']);return;	}
		if (empty($empresa)) {	echo json_encode(['estado' => 0, 'mensaje' => 'config_webservice_no_encontrada']);return;}

		//print_r($empresa)	;	//se usa para verificar lo datos de la empres y tipos de envio
		$empresa = $empresa[0];
		$mensaje = '';
		$estado_final = 0;

		//print_r($resumen); return exit;
		try {
			// Si ya hay ticket, consultamos estado

			//echo $resumen[0]['ticket']; return exit;
					
			if (!empty($resumen[0]['ticket'])) {

				//echo $resumen[0]['ticket']; return exit;
					// ⬇️ ESTE BLOQUE SE EJECUTA SI:
					// - Ya existe un ticket en la BD  
					// - El resumen ya fue enviado anteriormente
					// - Solo necesitas consultar el estado
				  
				$log('Ticket existente: ' . $resumen[0]['ticket'] . ' nombre_xml=' . $resumen[0]['nombre_xml']);
				//echo 'Ticket existente: ' . $resumen[0]['ticket'] . ' nombre_xml=' . $resumen[0]['nombre_xml'];
				$credenciales = [
					'ruc' => $_SESSION['phuyu_ruc'],
					'usuario' => $empresa['usuariosol'],
					'clave' => $empresa['clavesol'],
					'tipo' => $codresumentipo,
					'periodo' => $periodo,
					'nro' => $nrocorrelativo,
					'codempresa' => $_SESSION['phuyu_codempresa'],
				];

				// Forzar que la función Sunat::phuyu_consultarTICKET reciba datos y loguee (implementación en Sunat)
				$estado = Sunat::phuyu_consultarTICKET($resumen[0]['nombre_xml'], $resumen[0]['ticket'], $credenciales);
				print_r($estado); return exit;
				$mensaje = isset($resp['mensaje']) ? $resp['mensaje'] : '';
				$estado = isset($resp['estado']) ? $resp['estado'] : 0;
				// $mensaje = $estado['mensaje'];
				// $estado = $estado['estado'];

				$log("Consulta TICKET resultado estado={$estado} mensaje=" . $mensaje);

			} 
			else {
			//	echo "entro donde No hay ticket:  generar XML: tipo" .$codresumentipo ;
				// No hay ticket: generar XML
				if ($codresumentipo == 1) {	$generar = $this->Facturacion_model->phuyu_rf_crearXML($periodo, $nrocorrelativo);} 
				else {	$generar = $this->Facturacion_model->phuyu_rb_crearXML($periodo, $nrocorrelativo, $codresumentipo);}

				if (!is_array($generar) || !isset($generar['estado']) || $generar['estado'] == 0) {
					$log('Fallo generar XML: ' . json_encode($generar)); echo json_encode(['estado' => 0, 'mensaje' => 'NO SE PUEDE GENERAR EL DOCUMENTO XML']); return;}

				// Firmar XML
				$archivoCompleto = rtrim($generar['carpeta_phuyu'], '/') . '/' . $generar['archivo_phuyu'];
				$log('XML generado: ' . $archivoCompleto);

				$firma = Sunat::phuyu_firmarXML($archivoCompleto, 0);
				if ($firma !== 1) {
					$log('Fallo firma XML. phuyu_firmarXML devolvio: ' . var_export($firma, true));
					echo json_encode(['estado' => 0, 'mensaje' => 'NO SE PUEDE FIRMAR EL DOCUMENTO XML']);	return;	}

				// Preparar credenciales y forzar WSSE username = RUC + USUARIOSOL
				$ruc = $_SESSION['phuyu_ruc'];
				$usuarioSol = $empresa['usuariosol'];
				$claveSol = $empresa['clavesol'];

				// Credential array para enviar: incluye las partes por claridad
				$credenciales = [
					'ruc' => $ruc,
					'usuario' => $usuarioSol,
					'clave' => $claveSol,
					'tipo' => $codresumentipo,
					'periodo' => $periodo,
					'nro' => $nrocorrelativo,
					'codempresa' => $_SESSION['phuyu_codempresa'],
				];

				// Loguear el usuario WSSE que se debería usar
				$log('Preparando envio: wsse_username=' . $ruc . $usuarioSol . ' archivo=' . $generar['archivo_phuyu']);

				// Volcar SOAP/Request dentro de la función Sunat::phuyu_enviarSUNAT; aquí pedimos que la función guarde
				// el soap enviado y la respuesta en sunat/logs para depuración.
				$envio = Sunat::phuyu_enviarSUNAT('sendSummary', $generar['carpeta_phuyu'], $generar['archivo_phuyu'], $credenciales);

				echo $envio;
				$mensaje = isset($envio['mensaje']) ? $envio['mensaje'] : '';
				$estado_final = isset($envio['estado']) ? $envio['estado'] : 0;

				$log("Resultado envio: estado={$estado_final} mensaje=" . $mensaje);
			}
		} catch (Exception $e) {
			$log('EXCEPTION: ' . $e->getMessage());
			echo json_encode(['estado' => 0, 'mensaje' => 'ERROR INTERNO: ' . $e->getMessage()]);
			return;
		}

		// Respuesta final
		echo json_encode(['estado' => (int) $estado_final, 'mensaje' => $mensaje]);
	}

	function resumenes_xml($codresumentipo, $periodo, $nrocorrelativo)
	{
		$resumen = $this->db->query('select *from sunat.resumenes where codresumentipo=' . $codresumentipo . " and periodo='" . $periodo . "' and nrocorrelativo=" . $nrocorrelativo . ' and codsucursal=' . $_SESSION['phuyu_codsucursal'])->result_array();
		$empresa = $this->db->query('select *from public.empresas where codempresa=' . $_SESSION['phuyu_codempresa'])->result_array();

		if ($codresumentipo == 1) {
			$estado = $this->Facturacion_model->phuyu_rf_crearXML($periodo, $nrocorrelativo);
		} else {
			$estado = $this->Facturacion_model->phuyu_rb_crearXML($periodo, $nrocorrelativo, $codresumentipo);
		}

		if ($estado['estado'] != 0) {
			$firma = Sunat::phuyu_firmarXML($estado['carpeta_phuyu'] . '/' . $estado['archivo_phuyu'], 0);

			$this->load->helper('download');
			$cpe_ruta = file_get_contents($estado['carpeta_phuyu'] . '/' . $estado['archivo_phuyu'] . '.xml');
			force_download($estado['archivo_phuyu'] . '.xml', $cpe_ruta);
		} else {
			echo 'NO SE PUEDE GENERAR EL DOCUMENTO XML';
		}
	}

	function resumenes_cdr($codresumentipo, $periodo, $nrocorrelativo)
	{
		if (isset($_SESSION['phuyu_codusuario'])) {
			$ruta = $this->db->query('select ruta_cdr from sunat.resumenes where codresumentipo=' . $codresumentipo . " and periodo='" . $periodo . "' and nrocorrelativo=" . $nrocorrelativo . ' and codsucursal=' . $_SESSION['phuyu_codsucursal'])->result_array();
			if ($ruta[0]['ruta_cdr'] != '') {
				$archivo = explode('R-', $ruta[0]['ruta_cdr']);
				$this->load->helper('download');
				$descargar_ruta = file_get_contents($ruta[0]['ruta_cdr'] . '.zip');
				force_download('R-' . $archivo[1] . '.zip', $descargar_ruta);
			}
		}
	}

	function resumenes_ver($codresumentipo, $periodo, $nrocorrelativo)
	{
		if ($this->input->is_ajax_request()) {
			if ($codresumentipo == 1 || $codresumentipo == 4) {
				$lista = $this->db->query('select ka.*,k.cliente,k.seriecomprobante,k.nrocomprobante,k.fechacomprobante, k.importe from sunat.kardexsunatanulados as ka inner join kardex.kardex as k on(ka.codkardex=k.codkardex) where ka.codresumentipo=' . $codresumentipo . ' and ka.nrocorrelativo=' . $nrocorrelativo . " and ka.periodo='" . $periodo . "' and ka.codsucursal=" . $_SESSION['phuyu_codsucursal'])->result_array();
			} else {
				$lista = $this->db->query('select k.codkardex,k.cliente,k.seriecomprobante,k.nrocomprobante,k.fechacomprobante, k.importe from kardex.kardex as k inner join sunat.kardexsunatdetalle as ksd on(k.codkardex=ksd.codkardex) where ksd.codresumentipo=' . $codresumentipo . ' and ksd.nrocorrelativo=' . $nrocorrelativo . " and ksd.periodo='" . $periodo . "' and ksd.codempresa=" . $_SESSION['phuyu_codempresa'] . ' order by k.seriecomprobante,k.nrocomprobante')->result_array();
			}

			echo json_encode($lista);
		}
	}

	function resumenes_eliminar_kardex($codkardex, $codresumentipo, $periodo, $nrocorrelativo)
	{
		if ($this->input->is_ajax_request()) {
			$this->db->where('codkardex', $codkardex);
			$this->db->where('codresumentipo', $codresumentipo);
			$this->db->where('nrocorrelativo', $nrocorrelativo);
			$this->db->where('periodo', $periodo);
			$estado = $this->db->delete('sunat.kardexsunatdetalle');

			echo $estado;
		}
	}

	function resumenes_actualizar($codresumentipo, $periodo, $nrocorrelativo)
	{
		if ($this->input->is_ajax_request()) {
			$resumen = $this->db->query('select nombre_xml from sunat.resumenes where codresumentipo=' . $codresumentipo . ' and nrocorrelativo=' . $nrocorrelativo . " and periodo='" . $periodo . "'")->result_array();

			$update = [
				// "fechaenvio" => date("Y-m-d"),
				'ruta_cdr' => 'SIN CDR',
				'descripcion_cdr' => 'El Resumen numero ' . $resumen[0]['nombre_xml'] . ', ha sido aceptada',
				'codigorespuesta' => '0',
				'estado' => 1,
			];
			$this->db->where('codresumentipo', $codresumentipo);
			$this->db->where('nrocorrelativo', $nrocorrelativo);
			$this->db->where('periodo', $periodo);
			$estado = $this->db->update('sunat.resumenes', $update);

			if ($codresumentipo == 1 || $codresumentipo == 4) {
				$detalle = $this->db->query('select codkardex from sunat.kardexsunatanulados where codresumentipo=' . $codresumentipo . " and periodo='" . $periodo . "' and nrocorrelativo=" . $nrocorrelativo)->result_array();
				foreach ($detalle as $value) {
					$update = ['estado' => 1];
					$this->db->where('codkardex', $value['codkardex']);
					$actualizaranulados = $this->db->update('sunat.kardexsunatanulados', $update);
				}
			}

			if ($codresumentipo == 3 || $codresumentipo == 5) {
				$detalle = $this->db->query('select codkardex from sunat.kardexsunatdetalle where codresumentipo=' . $codresumentipo . " and periodo='" . $periodo . "' and nrocorrelativo=" . $nrocorrelativo)->result_array();
				foreach ($detalle as $value) {
					$update = [
						// "fechaenvio" => date("Y-m-d"),
						'ruta_cdr' => 'SIN CDR',
						'descripcion_cdr' => 'El Resumen numero ' . $resumen[0]['nombre_xml'] . ', ha sido aceptada',
						'codigorespuesta' => '0',
						'estado' => 1,
					];
					$this->db->where('codkardex', $value['codkardex']);
					$actualizarkardex = $this->db->update('sunat.kardexsunat', $update);
				}

				$update = [
					'descripcion_cdr' => 'El Resumen numero ' . $resumen[0]['nombre_xml'] . ', ha sido aceptada',
					// "fechaenvio" => date("Y-m-d"),
					'estado' => 1,
				];
				$this->db->where('codresumentipo', $codresumentipo);
				$this->db->where('nrocorrelativo', $nrocorrelativo);
				$this->db->where('periodo', $periodo);
				$actualizarkardex = $this->db->update('sunat.kardexsunatdetalle', $update);
			}
			echo $estado;
		}
	}

	function resumenes_siguiente_correlativo($codresumentipo, $periodo, $nrocorrelativo)
	{
		if ($this->input->is_ajax_request()) {
			$resumen = $this->db->query('select *from sunat.resumenes where codresumentipo=' . $codresumentipo . ' and nrocorrelativo=' . $nrocorrelativo . " and periodo='" . $periodo . "'")->result_array();

			$nrocorrelativo_nuevo = (int) $nrocorrelativo + 1;
			$oficial = $this->db->query('select oficial from sunat.resumentipos where codresumentipo=' . $codresumentipo)->result_array();
			$xml = $_SESSION['phuyu_ruc'] . '-' . $oficial[0]['oficial'] . '-' . $periodo . '-' . $nrocorrelativo_nuevo;

			$campos = ['codresumentipo', 'periodo', 'nrocorrelativo', 'codempresa', 'codsucursal', 'codusuario', 'nombre_xml', 'fecharesumen'];
			$valores = [(int) $codresumentipo, $periodo, (int) $nrocorrelativo_nuevo, (int) $_SESSION['phuyu_codempresa'], (int) $_SESSION['phuyu_codsucursal'], (int) $_SESSION['phuyu_codusuario'], $xml, $resumen[0]['fecharesumen']];
			$estado = $this->phuyu_model->phuyu_guardar('sunat.resumenes', $campos, $valores);

			$data = ['nrocorrelativo' => $nrocorrelativo_nuevo];
			$this->db->where('codresumentipo', $codresumentipo);
			$this->db->where('nrocorrelativo', $nrocorrelativo);
			$this->db->where('periodo', $periodo);
			if ($codresumentipo == 1 || $codresumentipo == 4) {
				$estado = $this->db->update('sunat.kardexsunatanulados', $data);
			} else {
				$estado = $this->db->update('sunat.kardexsunatdetalle', $data);
			}

			$this->db->where('codresumentipo', $codresumentipo);
			$this->db->where('nrocorrelativo', $nrocorrelativo);
			$this->db->where('periodo', $periodo);
			$this->db->where('codsucursal', $_SESSION['phuyu_codsucursal']);
			$estado = $this->db->delete('sunat.resumenes');

			echo $estado;
		}
	}

	function resumenes_quitar_ticket($codresumentipo, $periodo, $nrocorrelativo)
	{
		if ($this->input->is_ajax_request()) {
			$datos = ['ticket' => ''];
			$this->db->where('codresumentipo', $codresumentipo);
			$this->db->where('nrocorrelativo', $nrocorrelativo);
			$this->db->where('periodo', $periodo);
			$estado = $this->db->update('sunat.resumenes', $datos);

			echo $estado;
		}
	}

	function resumenes_anular($codresumentipo, $periodo, $nrocorrelativo)
	{
		if ($this->input->is_ajax_request()) {
			if ($codresumentipo == 1 || $codresumentipo == 4) {
				$this->db->where('codresumentipo', $codresumentipo);
				$this->db->where('nrocorrelativo', $nrocorrelativo);
				$this->db->where('periodo', $periodo);
				$this->db->where('codsucursal', $_SESSION['phuyu_codsucursal']);
				$estado = $this->db->delete('sunat.kardexsunatanulados');
			} else {
				$this->db->where('codresumentipo', $codresumentipo);
				$this->db->where('nrocorrelativo', $nrocorrelativo);
				$this->db->where('periodo', $periodo);
				$this->db->where('codempresa', $_SESSION['phuyu_codempresa']);
				$estado = $this->db->delete('sunat.kardexsunatdetalle');
			}

			$this->db->where('codresumentipo', $codresumentipo);
			$this->db->where('nrocorrelativo', $nrocorrelativo);
			$this->db->where('periodo', $periodo);
			$this->db->where('codsucursal', $_SESSION['phuyu_codsucursal']);
			$estado = $this->db->delete('sunat.resumenes');

			echo $estado;
		}
	}

	function phuyu_consultasunat()
	{
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$empresa = $this->db->query('select *from public.webservice')->result_array();
			if ($this->request->tipo == '09') {
				$envio = 'guia';
			} elseif ($this->request->tipo == '20') {
				$envio = 'retencion';
			} else {
				$envio = 'electronico';
			}

			$informacion = [$this->request->tipo, strtoupper($this->request->serie), $this->request->nrocomprobante];
			$credenciales = [$_SESSION['phuyu_ruc'], $empresa[0]['usuariosol'], $empresa[0]['clavesol']];
			$xml = $_SESSION['phuyu_ruc'] . '-' . $this->request->tipo . '-' . strtoupper($this->request->serie) . '-' . $this->request->nrocomprobante;
			$estado = Sunat::phuyu_consultarSUNAT($informacion, $credenciales, $envio, $xml);

			$mensaje = $estado['mensaje'];
			$estado = $estado['estado'];

			$data['estado'] = $estado;
			$data['mensaje'] = $mensaje;
			echo json_encode($data);
		}
	}

	function phuyu_bloquesunat()
	{
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			$empresa = $this->db->query('select *from public.webservice')->result_array();
			$lista = $this->db->query("select personas.documento, kardex.cliente, kardex.codkardex, kardex.seriecomprobante, kardex.nrocomprobante,kardex.fechacomprobante,round(kardex.importe,2) as importe,kardexs.estado from kardex.kardex as kardex inner join sunat.kardexsunat as kardexs on(kardex.codkardex=kardexs.codkardex) inner join public.personas as personas on (kardex.codpersona=personas.codpersona) where kardex.fechacomprobante>='" . $this->request->fdesde . "' and kardex.fechacomprobante<='" . $this->request->fhasta . "' and kardex.codmovimientotipo=20 and kardex.codcomprobantetipo=10 order by kardex.codkardex asc")->result_array();
			foreach ($lista as $key => $value) {
				$informacion = ['01', $value['seriecomprobante'], $value['nrocomprobante']];
				$credenciales = [$_SESSION['phuyu_ruc'], $empresa[0]['usuariosol'], $empresa[0]['clavesol']];
				$estado = Sunat::phuyu_consultarSUNAT($informacion, $credenciales, 'electronico');

				if ((int) $estado['estado'] == 0) {
					$sunat = $estado['mensaje'];
				} else {
					$sunat = explode('statusMessage', $estado['mensaje']);
					$sunat = $sunat[1];
				}
				$lista[$key]['descripcion'] = $sunat;
			}
			echo json_encode($lista);
		}
	}

	// FACTURACION ELECTRONICA: REPORTES GENERADOS //

	function phuyu_datos_cpe()
	{
		if ($this->input->is_ajax_request()) {
			$facturas = [];
			$boletas = [];

			// DATOS FACTURAS //
			$enviados = $this->db->query('select count(*) as cantidad from kardex.kardex as k inner join sunat.kardexsunat as ks on(k.codkardex=ks.codkardex) where k.codmovimientotipo=20 and k.codcomprobantetipo=10 and (ks.estado=1 or ks.estado=2)')->result_array();
			$anulados = $this->db->query('select count(*) as cantidad from kardex.kardex as k inner join sunat.kardexsunatanulados as ksa on(k.codkardex=ksa.codkardex) where k.codmovimientotipo=20 and k.codcomprobantetipo=10 and (ksa.estado=1 or ksa.estado=2)')->result_array();
			$pendientes = $this->db->query('select count(*) as cantidad from kardex.kardex as kardex inner join sunat.kardexsunat as kardexs on(kardex.codkardex=kardexs.codkardex) where kardexs.estado<>1 and kardexs.estado<>2 and kardex.codmovimientotipo=20 and kardex.codcomprobantetipo=10')->result_array();

			$facturas['enviados'] = (float) $enviados[0]['cantidad'] - $anulados[0]['cantidad'];
			$facturas['anulados'] = (float) $anulados[0]['cantidad'];
			$facturas['pendientes'] = (float) $pendientes[0]['cantidad'];

			// DATOS BOLETAS //
			$enviados = $this->db->query('select count(*) as cantidad from kardex.kardex as k inner join sunat.kardexsunat as ks on(k.codkardex=ks.codkardex) where k.codmovimientotipo=20 and k.codcomprobantetipo=12 and (ks.estado=1 or ks.estado=2)')->result_array();
			$anulados = $this->db->query('select count(*) as cantidad from kardex.kardex as k inner join sunat.kardexsunatanulados as ksa on(k.codkardex=ksa.codkardex) where k.codmovimientotipo=20 and k.codcomprobantetipo=12 and (ksa.estado=1 or ksa.estado=2)')->result_array();
			$pendientes = $this->db->query('select count(*) as cantidad from sunat.resumenes where codresumentipo=3 and estado<>1 and estado<>2')->result_array();

			$boletas['enviados'] = (float) $enviados[0]['cantidad'] - $anulados[0]['cantidad'];
			$boletas['anulados'] = (float) $anulados[0]['cantidad'];
			$boletas['pendientes'] = (float) $pendientes[0]['cantidad'];

			$data['facturas'] = $facturas;
			$data['boletas'] = $boletas;
			echo json_encode($data);
		}
	}

	function reporte_facturas_enviados($fdesde, $fhasta)
	{
		if ($this->input->is_ajax_request()) {
			$lista = $this->db->query("select personas.documento, personas.razonsocial, kardex.codkardex, kardex.seriecomprobante, kardex.nrocomprobante,kardex.fechacomprobante,round(kardex.importe,2) as importe,kardexs.descripcion_cdr as sunat from kardex.kardex as kardex inner join sunat.kardexsunat as kardexs on(kardex.codkardex=kardexs.codkardex) inner join public.personas as personas on (kardex.codpersona=personas.codpersona) where kardex.fechacomprobante>='" . $fdesde . "' and kardex.fechacomprobante<='" . $fhasta . "' and kardex.codmovimientotipo=20 and kardex.codcomprobantetipo=10 order by kardex.seriecomprobante,kardex.nrocomprobante")->result_array();
			echo json_encode($lista);
		}
	}
	function reporte_facturas_anulados($fdesde, $fhasta)
	{
		if ($this->input->is_ajax_request()) {
			$resumenes = $this->db->query("select *from sunat.resumenes where fecharesumen>='" . $fdesde . "' and fecharesumen<='" . $fhasta . "' and codresumentipo=1 order by fecharesumen")->result_array();
			foreach ($resumenes as $key => $value) {
				$lista = $this->db->query('select personas.documento, personas.razonsocial, kardex.seriecomprobante, kardex.nrocomprobante, round(kardex.importe,2) as importe,kardexa.motivobaja as motivo from kardex.kardex as kardex inner join sunat.kardexsunatanulados as kardexa on(kardex.codkardex=kardexa.codkardex) inner join public.personas as personas on (kardex.codpersona=personas.codpersona) where kardexa.codresumentipo=' . $value['codresumentipo'] . " and kardexa.periodo='" . $value['periodo'] . "' and kardexa.nrocorrelativo=" . $value['nrocorrelativo'] . ' and kardexa.codempresa=' . $value['codempresa'] . ' order by kardex.seriecomprobante,kardex.nrocomprobante')->result_array();
				$resumenes[$key]['lista'] = $lista;
			}
			echo json_encode($resumenes);
		}
	}
	function reporte_boletas_enviados($fdesde, $fhasta)
	{
		if ($this->input->is_ajax_request()) {
			$resumenes = $this->db->query("select *from sunat.resumenes where fecharesumen>='" . $fdesde . "' and fecharesumen<='" . $fhasta . "' and codresumentipo=3 order by fecharesumen")->result_array();
			foreach ($resumenes as $key => $value) {
				$lista = $this->db->query("select personas.documento, personas.razonsocial, kardex.seriecomprobante, kardex.nrocomprobante, round(kardex.importe,2) as importe,'-' as motivo from kardex.kardex as kardex inner join sunat.kardexsunatdetalle as kardexsd on(kardex.codkardex=kardexsd.codkardex) inner join public.personas as personas on (kardex.codpersona=personas.codpersona) where kardexsd.codresumentipo=" . $value['codresumentipo'] . " and kardexsd.periodo='" . $value['periodo'] . "' and kardexsd.nrocorrelativo=" . $value['nrocorrelativo'] . ' and kardexsd.codempresa=' . $value['codempresa'] . ' order by kardex.seriecomprobante,kardex.nrocomprobante')->result_array();
				$resumenes[$key]['lista'] = $lista;
			}
			echo json_encode($resumenes);
		}
	}
	function reporte_boletas_anulados($fdesde, $fhasta)
	{
		if ($this->input->is_ajax_request()) {
			$resumenes = $this->db->query("select *from sunat.resumenes where fecharesumen>='" . $fdesde . "' and fecharesumen<='" . $fhasta . "' and codresumentipo=4 order by fecharesumen")->result_array();
			foreach ($resumenes as $key => $value) {
				$lista = $this->db->query('select personas.documento, personas.razonsocial, kardex.seriecomprobante, kardex.nrocomprobante, round(kardex.importe,2) as importe,kardexa.motivobaja as motivo from kardex.kardex as kardex inner join sunat.kardexsunatanulados as kardexa on(kardex.codkardex=kardexa.codkardex) inner join public.personas as personas on (kardex.codpersona=personas.codpersona) where kardexa.codresumentipo=' . $value['codresumentipo'] . " and kardexa.periodo='" . $value['periodo'] . "' and kardexa.nrocorrelativo=" . $value['nrocorrelativo'] . ' and kardexa.codempresa=' . $value['codempresa'] . ' order by kardex.seriecomprobante,kardex.nrocomprobante')->result_array();
				$resumenes[$key]['lista'] = $lista;
			}
			echo json_encode($resumenes);
		}
	}

	function consulta_cdr($ticket) {}

	function kardex_faltantes()
	{
		// $lista = $this->db->query("select *from kardex.kardex where (codcomprobantetipo=12 or codcomprobantetipo=10) and codkardex not in (select codkardex from sunat.kardexsunat) limit 10000")->result_array();
		$lista = $this->db->query("select *from kardex.kardex where fechacomprobante>='2019-10-01' and (codcomprobantetipo=12 or codcomprobantetipo=10) and codkardex not in (select codkardex from sunat.kardexsunat)")->result_array();
		foreach ($lista as $key => $value) {
			if ($value['codcomprobantetipo'] == 10) {
				$xml = $_SESSION['phuyu_ruc'] . '-01-' . $value['seriecomprobante'] . '-' . $value['nrocomprobante'];
			} else {
				$xml = $_SESSION['phuyu_ruc'] . '-03-' . $value['seriecomprobante'] . '-' . $value['nrocomprobante'];
			}
			$campos = ['codkardex', 'codsucursal', 'codusuario', 'fechacreado', 'nombre_xml'];
			$valores = [(int) $value['codkardex'], (int) $_SESSION['phuyu_codsucursal'], (int) $_SESSION['phuyu_codusuario'], $value['fechacomprobante'], $xml];
			$estado = $this->phuyu_model->phuyu_guardar('sunat.kardexsunat', $campos, $valores);
		}
		echo count($lista);
	}

	function kardex_sinicbper()
	{
		$lista = $this->db->query('select *from sunat.kardexsunat where estado=0')->result_array();
		foreach ($lista as $key => $value) {
			$detalle = $this->db->query('select *from kardex.kardexdetalle where codkardex=' . $value['codkardex'])->result_array();
			$importe = 0;
			foreach ($detalle as $v) {
				$importe = $importe + $v['valorventa'];

				$data = [
					'conicbper' => 0,
					'icbper' => 0,
					'subtotal' => $v['valorventa'],
				];

				$this->db->where('codkardex', $v['codkardex']);
				$this->db->where('codproducto', $v['codproducto']);
				$this->db->where('codunidad', $v['codunidad']);
				$this->db->where('item', $v['item']);
				$estado = $this->db->update('kardex.kardexdetalle', $data);
			}

			$data = [
				'icbper' => 0,
				'importe' => round($importe, 2),
			];
			$this->db->where('codkardex', $value['codkardex']);
			$estado = $this->db->update('kardex.kardex', $data);

			$movimiento = $this->db->query('select codmovimiento from caja.movimientos where codkardex=' . $value['codkardex'])->result_array();
			if (count($movimiento) > 0) {
				$data = ['importe' => round($importe, 2)];
				$this->db->where('codmovimiento', $movimiento[0]['codmovimiento']);
				$estado = $this->db->update('caja.movimientos', $data);

				$data = [
					'importe' => round($importe, 2),
					'importeentregado' => round($importe, 2),
					'vuelto' => 0,
				];
				$this->db->where('codmovimiento', $movimiento[0]['codmovimiento']);
				$estado = $this->db->update('caja.movimientosdetalle', $data);
			}

			echo 'COD KARDEX = ' . $value['codkardex'] . '<br>';
		}
	}
}
