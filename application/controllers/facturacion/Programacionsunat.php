<?php defined('BASEPATH') OR exit('No direct script access allowed');
include("Sunat.php");

class Programacionsunat extends Sunat {

	public function __construct(){
		parent::__construct();
		$this->load->model("Programacion_sunat_model");
		$this->load->model("Facturacion_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			$this->load->view("facturacion/programacion_sunat/index");
			return;
		}
		$this->load->view("phuyu/404");
	}

	public function datos(){
		$this->salida_json([
			"empresas" => $this->Programacion_sunat_model->empresas(),
			"sucursales" => $this->Programacion_sunat_model->sucursales(),
			"configuraciones" => $this->Programacion_sunat_model->configuraciones(),
			"historial" => $this->Programacion_sunat_model->historial(),
			"cola" => $this->Programacion_sunat_model->cola(),
			"cron" => $this->cron_base_estado()
		]);
	}

	public function cron_base(){
		if (!$this->input->is_ajax_request() || !isset($_SESSION["phuyu_codusuario"])) {
			$this->salida_json(["estado" => 0, "mensaje" => "Sesion no valida"]);
			return;
		}

		$this->salida_json($this->cron_base_estado());
	}

	public function crear_cron_base(){
		if (!$this->input->is_ajax_request() || !isset($_SESSION["phuyu_codusuario"])) {
			$this->salida_json(["estado" => 0, "mensaje" => "Sesion no valida"]);
			return;
		}

		$this->salida_json($this->guardar_cron_base());
	}

	public function guardar(){
		if (!$this->input->is_ajax_request() || !isset($_SESSION["phuyu_codusuario"])) {
			$this->salida_json(["estado" => 0, "mensaje" => "Sesion no valida"]);
			return;
		}
		$datos = json_decode(file_get_contents("php://input"));
		$estado = $this->Programacion_sunat_model->guardar($datos);
		$this->salida_json([
			"estado" => $estado > 0 ? 1 : 0,
			"codprogramacion" => $estado,
			"mensaje" => $estado > 0 ? "Programacion guardada correctamente" : "No se pudo guardar la programacion"
		]);
	}

	public function eliminar($codprogramacion){
		if (!$this->input->is_ajax_request() || !isset($_SESSION["phuyu_codusuario"])) {
			$this->salida_json(["estado" => 0, "mensaje" => "Sesion no valida"]);
			return;
		}
		$estado = $this->Programacion_sunat_model->eliminar($codprogramacion);
		$this->salida_json(["estado" => $estado, "mensaje" => $estado ? "Programacion desactivada" : "No se pudo desactivar"]);
	}

	public function ejecutar_manual($codprogramacion){
		if (!$this->input->is_ajax_request() || !isset($_SESSION["phuyu_codusuario"])) {
			$this->salida_json(["estado" => 0, "mensaje" => "Sesion no valida"]);
			return;
		}

		$config = $this->db->query(
			"select *, null::integer as codhorario, 'todo'::varchar as accion
			from sunat.programacion_cpe
			where codprogramacion=? and estado<>0",
			[(int)$codprogramacion]
		)->row_array();

		if (empty($config)) {
			$this->salida_json(["estado" => 0, "mensaje" => "Programacion no encontrada"]);
			return;
		}

		$this->salida_json($this->ejecutar_programacion($config, "manual"));
	}

	public function cron(){
		if (!$this->input->is_cli_request()) {
			$token_config = getenv("PHUYU_SUNAT_CRON_TOKEN");
			if ($token_config && $this->input->get("token") !== $token_config) {
				$this->output->set_status_header(403);
				echo "Token invalido";
				return;
			}
		}

		$resultados = [];
		$programaciones = $this->Programacion_sunat_model->programaciones_vencidas(date("H:i"));
		foreach ($programaciones as $programacion) {
			$resultados[] = $this->ejecutar_programacion($programacion, "auto");
		}

		if ($this->input->is_cli_request()) {
			echo json_encode($resultados, JSON_PRETTY_PRINT).PHP_EOL;
			return;
		}
		$this->salida_json(["estado" => 1, "resultados" => $resultados]);
	}

	private function cron_base_estado(){
		try {
			$info = $this->cron_base_info();
			$archivo_detectado = $this->cron_base_buscar_archivo($info);
			$existe = $archivo_detectado !== "";
			if ($existe) {
				$info["archivo"] = $archivo_detectado;
			}

			return [
				"estado" => 1,
				"existe" => $existe ? 1 : 0,
				"mensaje" => $existe ? "Cron base encontrado" : "Cron base no encontrado",
				"proyecto" => $info["proyecto"],
				"ruta" => $info["ruta"],
				"archivo" => $info["archivo"],
				"comando" => $info["comando"]
			];
		} catch (Throwable $e) {
			return ["estado" => 0, "existe" => 0, "mensaje" => $e->getMessage()];
		}
	}

	private function guardar_cron_base(){
		try {
			$info = $this->cron_base_info();
			$archivo_detectado = $this->cron_base_buscar_archivo($info);
			if ($archivo_detectado !== "") {
				$info["archivo"] = $archivo_detectado;
			}
			$contenido = "# Cron base para Programacion de envios CPE / SUNAT\n";
			$contenido .= "# Proyecto: ".$info["proyecto"]."\n";
			$contenido .= "# Ejecuta cada minuto el despachador interno de tareas programadas.\n";
			$contenido .= $info["comando"]."\n";

			if (file_put_contents($info["archivo"], $contenido, LOCK_EX) === false || !chmod($info["archivo"], 0644)) {
				$this->cron_base_instalar_con_sudo($info["proyecto"]);
			}

			$respuesta = $this->cron_base_estado();
			$respuesta["mensaje"] = "Cron base creado o actualizado correctamente";
			return $respuesta;
		} catch (Throwable $e) {
			return ["estado" => 0, "existe" => 0, "mensaje" => $e->getMessage()];
		}
	}

	private function cron_base_info(){
		$ruta = realpath(FCPATH);
		if ($ruta === false || !is_file($ruta.DIRECTORY_SEPARATOR."index.php")) {
			throw new Exception("No se pudo detectar la ruta real del index.php del proyecto.");
		}

		$ruta = rtrim($ruta, DIRECTORY_SEPARATOR);
		$proyecto = strtolower(basename($ruta));
		if ($proyecto === "" || !preg_match('/^[a-z0-9.-]+$/', $proyecto)) {
			throw new Exception("No se pudo detectar un nombre de proyecto valido.");
		}

		$nombre_archivo = preg_replace('/[^a-z0-9_-]+/', '-', $proyecto);
		$archivo = "/etc/cron.d/phuyu-".$nombre_archivo."-programacion-sunat";
		$comando = "* * * * * www-data cd ".$ruta." && /usr/bin/php7.4 index.php facturacion/programacionsunat/cron >/dev/null 2>&1";

		return [
			"proyecto" => $proyecto,
			"ruta" => $ruta,
			"archivo" => $archivo,
			"comando" => $comando
		];
	}

	private function cron_base_buscar_archivo($info){
		if (file_exists($info["archivo"])) {
			return $info["archivo"];
		}

		$archivos = glob("/etc/cron.d/*");
		if (!is_array($archivos)) {
			return "";
		}

		foreach ($archivos as $archivo) {
			if (!is_readable($archivo) || is_dir($archivo)) {
				continue;
			}

			$contenido = file_get_contents($archivo);
			if ($contenido !== false && strpos($contenido, $info["ruta"]) !== false && strpos($contenido, "facturacion/programacionsunat/cron") !== false) {
				return $archivo;
			}
		}

		return "";
	}

	private function cron_base_instalar_con_sudo($proyecto){
		if (!function_exists("exec")) {
			throw new Exception("No se pudo escribir el cron base y exec() no esta disponible para usar el instalador seguro.");
		}

		if (!preg_match('/^[a-z0-9.-]+$/', $proyecto)) {
			throw new Exception("Nombre de proyecto no permitido para instalar cron.");
		}

		$instalador = "/usr/local/sbin/phuyu-cron-sunat-installer";
		if (!is_executable($instalador)) {
			throw new Exception("No se pudo escribir el cron base. Falta instalar el helper seguro: ".$instalador);
		}

		$salida = [];
		$codigo = 1;
		exec("/usr/bin/sudo -n ".escapeshellarg($instalador)." ".escapeshellarg($proyecto)." 2>&1", $salida, $codigo);

		if ($codigo !== 0) {
			throw new Exception("No se pudo instalar el cron base: ".implode(" ", $salida));
		}
	}

	private function ejecutar_programacion($programacion, $origen){
		$this->preparar_contexto_empresa($programacion);
		$codejecucion = $this->Programacion_sunat_model->iniciar_historial($programacion, $origen);
		$procesados = 0;
		$errores = 0;

		try {
			$this->encolar_pendientes($programacion, $codejecucion);
			$forzar_reintento = $origen === "manual";
			$pendientes = $this->Programacion_sunat_model->pendientes_cola($programacion, (int)$programacion["limite_por_ejecucion"], $forzar_reintento);

			foreach ($pendientes as $cola) {
				if (!$this->Programacion_sunat_model->marcar_procesando($cola["codcola"])) {
					continue;
				}

				$respuesta = $this->procesar_cola($cola);
				$this->Programacion_sunat_model->marcar_resultado_cola($cola, $respuesta, (int)$programacion["max_intentos"]);
				$procesados++;

				$ok = in_array((int)$respuesta["estado"], [1, 2], true);
				if (!$ok) {
					$errores++;
				}

				$this->Programacion_sunat_model->log(
					$codejecucion,
					$ok ? "info" : "error",
					isset($respuesta["mensaje"]) ? $respuesta["mensaje"] : "Sin respuesta",
					$cola["tipo"].":".$cola["referencia"]
				);
			}

			$mensaje = $procesados > 0
				? "Procesados: ".$procesados.". Errores: ".$errores
				: "No hay pendientes disponibles para procesar";
			$this->Programacion_sunat_model->finalizar_historial($codejecucion, [
				"estado" => $errores > 0 ? "parcial" : "ok",
				"cantidad_procesada" => $procesados,
				"cantidad_error" => $errores,
				"respuesta_sunat" => $mensaje
			]);
			return ["estado" => $errores > 0 ? 0 : 1, "mensaje" => $mensaje, "codejecucion" => $codejecucion];
		} catch (Throwable $e) {
			$this->Programacion_sunat_model->log($codejecucion, "error", $e->getMessage());
			$this->Programacion_sunat_model->finalizar_historial($codejecucion, [
				"estado" => "error",
				"cantidad_procesada" => $procesados,
				"cantidad_error" => $errores + 1,
				"errores" => $e->getMessage()
			]);
			return ["estado" => 0, "mensaje" => $e->getMessage(), "codejecucion" => $codejecucion];
		}
	}

	private function preparar_contexto_empresa($programacion){
		$empresa = $this->db->query(
			"select e.codempresa, p.documento, p.nombrecomercial, e.igvsunat, e.icbpersunat
			from public.empresas e
			inner join public.personas p on p.codpersona=e.codpersona
			where e.codempresa=?",
			[(int)$programacion["codempresa"]]
		)->row_array();

		if (empty($empresa)) {
			throw new Exception("Empresa no encontrada para programacion SUNAT");
		}

		$_SESSION["phuyu_codempresa"] = (int)$empresa["codempresa"];
		$_SESSION["phuyu_ruc"] = $empresa["documento"];
		$_SESSION["phuyu_empresa"] = $empresa["nombrecomercial"];
		$_SESSION["phuyu_igv"] = $empresa["igvsunat"];
		$_SESSION["phuyu_icbper"] = $empresa["icbpersunat"];
		$_SESSION["phuyu_codsucursal"] = !empty($programacion["codsucursal"]) ? (int)$programacion["codsucursal"] : 1;
		$_SESSION["phuyu_codusuario"] = isset($_SESSION["phuyu_codusuario"]) ? (int)$_SESSION["phuyu_codusuario"] : 0;
	}

	private function encolar_pendientes($programacion, $codejecucion){
		$accion = isset($programacion["accion"]) ? $programacion["accion"] : "todo";

		if ($accion === "generar_resumen" || $accion === "todo") {
			if ((int)$programacion["procesar_resumenes"] === 1 || (int)$programacion["procesar_boletas"] === 1) {
				$this->generar_resumenes(3, date("Y-m-d"), $programacion, $codejecucion);
			}
			if ((int)$programacion["procesar_bajas"] === 1) {
				$this->generar_resumenes(1, date("Y-m-d"), $programacion, $codejecucion);
				$this->generar_resumenes(4, date("Y-m-d"), $programacion, $codejecucion);
			}
		}

		if ($accion === "generar_resumen") {
			$this->encolar_resumenes($programacion);
			return;
		}

		if ((int)$programacion["procesar_facturas"] === 1) {
			$this->encolar_comprobantes($programacion, "factura", [10]);
		}
		if ((int)$programacion["procesar_notas_credito"] === 1) {
			$this->encolar_comprobantes($programacion, "nota_credito", [14]);
		}
		if ((int)$programacion["procesar_notas_debito"] === 1) {
			$this->encolar_comprobantes($programacion, "nota_debito", [15]);
		}
		if ((int)$programacion["procesar_resumenes"] === 1 || (int)$programacion["procesar_boletas"] === 1 || (int)$programacion["procesar_bajas"] === 1) {
			$this->encolar_resumenes($programacion);
		}
	}

	private function encolar_comprobantes($programacion, $tipo, $tipos){
		$tipos = array_map("intval", $tipos);
		$params = [];
		$where_sucursal = "";
		if (!empty($programacion["codsucursal"])) {
			$where_sucursal = " and k.codsucursal=?";
			$params[] = (int)$programacion["codsucursal"];
		}

		$lista = $this->db->query(
			"select k.codkardex
			from kardex.kardex k
			inner join sunat.kardexsunat ks on ks.codkardex=k.codkardex
			where k.codcomprobantetipo in (".implode(",", $tipos).")
				and ks.estado not in (1,2)
				and k.estado=1
				".$where_sucursal."
			order by k.codkardex asc",
			$params
		)->result_array();

		foreach ($lista as $value) {
			$this->Programacion_sunat_model->encolar($tipo, $value["codkardex"], $programacion, 10);
		}
	}

	private function encolar_resumenes($programacion){
		$params = [(int)$programacion["codempresa"]];
		$where_sucursal = "";
		if (!empty($programacion["codsucursal"])) {
			$where_sucursal = " and codsucursal=?";
			$params[] = (int)$programacion["codsucursal"];
		}

		$lista = $this->db->query(
			"select codresumentipo, periodo, nrocorrelativo
			from sunat.resumenes
			where codempresa=?
				and estado not in (1,2)
				".$where_sucursal."
			order by fecharesumen asc, codresumentipo, nrocorrelativo",
			$params
		)->result_array();

		foreach ($lista as $value) {
			$ref = $value["codresumentipo"]."|".$value["periodo"]."|".$value["nrocorrelativo"];
			$this->Programacion_sunat_model->encolar("resumen", $ref, $programacion, 5);
		}
	}

	private function generar_resumenes($codresumentipo, $fecha, $programacion, $codejecucion){
		$fechas_resumen = $this->fechas_resumen($codresumentipo, $fecha, $programacion);
		foreach ($fechas_resumen as $value) {
			$fecharesumen = $value["fechacomprobante"];
			$lista = $this->detalle_resumen($codresumentipo, $fecharesumen, $fecha, $programacion);
			if (count($lista) === 0) {
				continue;
			}

			$f = explode("-", $fecharesumen);
			$periodo = $f[0].$f[1].$f[2];
			$resumenes = $codresumentipo == 1
				? $this->db->query("select count(*) as cantidad from sunat.resumenes where periodo=? and codresumentipo=? and codempresa=?", [$periodo, (int)$codresumentipo, (int)$programacion["codempresa"]])->row_array()
				: $this->db->query("select coalesce(max(nrocorrelativo),0) as cantidad from sunat.resumenes where periodo=? and (codresumentipo=3 or codresumentipo=4) and codempresa=?", [$periodo, (int)$programacion["codempresa"]])->row_array();

			$nrocorrelativo = (int)$resumenes["cantidad"] + 1;
			$oficial = $this->db->query("select oficial from sunat.resumentipos where codresumentipo=?", [(int)$codresumentipo])->row_array();
			$xml = $_SESSION["phuyu_ruc"]."-".$oficial["oficial"]."-".$periodo."-".$nrocorrelativo;
			$codsucursal = !empty($programacion["codsucursal"]) ? (int)$programacion["codsucursal"] : (int)$_SESSION["phuyu_codsucursal"];

			$this->db->trans_begin();
			$this->db->insert("sunat.resumenes", [
				"codresumentipo" => (int)$codresumentipo,
				"periodo" => $periodo,
				"nrocorrelativo" => (int)$nrocorrelativo,
				"codempresa" => (int)$programacion["codempresa"],
				"codsucursal" => $codsucursal,
				"codusuario" => (int)$_SESSION["phuyu_codusuario"],
				"nombre_xml" => $xml,
				"fecharesumen" => $fecharesumen
			]);

			foreach ($lista as $val) {
				if ($codresumentipo == 1 || $codresumentipo == 4) {
					$this->db->insert("sunat.kardexsunatanulados", [
						"codkardex" => (int)$val["codkardex"],
						"codresumentipo" => (int)$codresumentipo,
						"nrocorrelativo" => (int)$nrocorrelativo,
						"periodo" => $periodo,
						"codempresa" => (int)$programacion["codempresa"],
						"codsucursal" => $codsucursal,
						"fechaanulacion" => $fecha,
						"fechareferencia" => $fecharesumen,
						"motivobaja" => isset($val["observaciones"]) ? $val["observaciones"] : "ANULACION"
					]);
				} else {
					$this->db->insert("sunat.kardexsunatdetalle", [
						"codkardex" => (int)$val["codkardex"],
						"codresumentipo" => (int)$codresumentipo,
						"nrocorrelativo" => (int)$nrocorrelativo,
						"periodo" => $periodo,
						"codempresa" => (int)$programacion["codempresa"],
						"fecharesumen" => $fecharesumen
					]);
				}
			}

			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				$this->Programacion_sunat_model->log($codejecucion, "error", "No se pudo generar resumen ".$xml);
			} else {
				$this->db->trans_commit();
				$this->Programacion_sunat_model->log($codejecucion, "info", "Resumen generado ".$xml);
			}
		}
	}

	private function fechas_resumen($codresumentipo, $fecha, $programacion){
		$sucursal_k = !empty($programacion["codsucursal"]) ? " and k.codsucursal=".(int)$programacion["codsucursal"] : "";
		if ($codresumentipo == 1) {
			return $this->db->query(
				"select distinct(k.fechacomprobante) as fechacomprobante
				from kardex.kardexanulados ka
				inner join kardex.kardex k on ka.codkardex=k.codkardex
				inner join sunat.kardexsunat ks on k.codkardex=ks.codkardex
				where ka.fechaanulacion<=? and k.codmovimientotipo=20 and k.codcomprobantetipo=10
					and (ks.estado=1 or ks.estado=2)
					and k.codkardex not in (select codkardex from sunat.kardexsunatanulados where fechaanulacion<=?)
					".$sucursal_k,
				[$fecha, $fecha]
			)->result_array();
		}
		if ($codresumentipo == 3) {
			$sucursal = !empty($programacion["codsucursal"]) ? " and kardex.codsucursal=".(int)$programacion["codsucursal"] : "";
			return $this->db->query(
				"select distinct(kardex.fechacomprobante) as fechacomprobante
				from kardex.kardex kardex
				inner join sunat.kardexsunat kardexs on kardex.codkardex=kardexs.codkardex
				where kardexs.fechacreado<=? and kardex.codmovimientotipo=20 and kardex.codcomprobantetipo=12
					and upper(kardex.seriecomprobante) like 'B%'
					and kardex.codkardex not in (select codkardex from sunat.kardexsunatdetalle where fecharesumen<=?)
					".$sucursal,
				[$fecha, $fecha]
			)->result_array();
		}
		return $this->db->query(
			"select distinct(k.fechacomprobante) as fechacomprobante
			from kardex.kardexanulados ka
			inner join kardex.kardex k on ka.codkardex=k.codkardex
			inner join sunat.kardexsunat ks on k.codkardex=ks.codkardex
			where ka.fechaanulacion<=? and k.codmovimientotipo=20 and k.codcomprobantetipo=12
				and (ks.estado=1 or ks.estado=2)
				and k.codkardex not in (select codkardex from sunat.kardexsunatanulados where fechaanulacion<=?)
				".$sucursal_k,
			[$fecha, $fecha]
		)->result_array();
	}

	private function detalle_resumen($codresumentipo, $fecharesumen, $fecha, $programacion){
		$sucursal_k = !empty($programacion["codsucursal"]) ? " and k.codsucursal=".(int)$programacion["codsucursal"] : "";
		if ($codresumentipo == 1) {
			return $this->db->query(
				"select ka.codkardex, ka.observaciones
				from kardex.kardexanulados ka
				inner join kardex.kardex k on ka.codkardex=k.codkardex
				inner join sunat.kardexsunat ks on k.codkardex=ks.codkardex
				where k.fechacomprobante=? and ka.fechaanulacion<=? and k.codmovimientotipo=20 and k.codcomprobantetipo=10
					and (ks.estado=1 or ks.estado=2)
					and k.codkardex not in (select codkardex from sunat.kardexsunatanulados where fechaanulacion<=?)
					".$sucursal_k,
				[$fecharesumen, $fecha, $fecha]
			)->result_array();
		}
		if ($codresumentipo == 3) {
			$sucursal = !empty($programacion["codsucursal"]) ? " and kardex.codsucursal=".(int)$programacion["codsucursal"] : "";
			return $this->db->query(
				"select kardex.codkardex
				from kardex.kardex kardex
				inner join sunat.kardexsunat kardexs on kardex.codkardex=kardexs.codkardex
				where kardex.fechacomprobante=? and kardex.codmovimientotipo=20 and kardex.codcomprobantetipo=12
					and upper(kardex.seriecomprobante) like 'B%'
					and kardex.codkardex not in (select codkardex from sunat.kardexsunatdetalle where fecharesumen<=?)
					".$sucursal,
				[$fecharesumen, $fecha]
			)->result_array();
		}
		return $this->db->query(
			"select ka.codkardex, ka.observaciones
			from kardex.kardexanulados ka
			inner join kardex.kardex k on ka.codkardex=k.codkardex
			inner join sunat.kardexsunat ks on k.codkardex=ks.codkardex
			where k.fechacomprobante=? and ka.fechaanulacion<=? and k.codmovimientotipo=20 and k.codcomprobantetipo=12
				and (ks.estado=1 or ks.estado=2)
				and k.codkardex not in (select codkardex from sunat.kardexsunatanulados where fechaanulacion<=?)
				".$sucursal_k,
			[$fecharesumen, $fecha, $fecha]
		)->result_array();
	}

	private function procesar_cola($cola){
		if ($cola["tipo"] === "resumen") {
			return $this->enviar_resumen($cola["referencia"], (int)$cola["codempresa"]);
		}
		$oficial = ["factura" => "01", "nota_credito" => "07", "nota_debito" => "08"];
		return $this->enviar_comprobante((int)$cola["referencia"], $oficial[$cola["tipo"]]);
	}

	private function enviar_comprobante($codkardex, $codoficial){
		$empresa = $this->db->query("select *from public.webservice where codempresa=?", [(int)$_SESSION["phuyu_codempresa"]])->result_array();
		if (count($empresa) === 0) {
			$empresa = $this->db->query("select *from public.webservice limit 1")->result_array();
		}
		if (count($empresa) === 0) {
			return ["estado" => 0, "mensaje" => "No existen credenciales SUNAT"];
		}

		$estado = $this->Facturacion_model->phuyu_crearXML($codoficial, $codkardex);
		if ($estado["estado"] == 0) {
			return ["estado" => 0, "mensaje" => "No se puede generar XML"];
		}
		$firma = $this->phuyu_firmarXML($estado["carpeta_phuyu"]."/".$estado["archivo_phuyu"], 0, true);
		if ($firma["estado"] != 1) {
			return ["estado" => 0, "mensaje" => "No se puede firmar XML: ".$firma["mensaje"]];
		}
		$credenciales = [$_SESSION["phuyu_ruc"], $empresa[0]["usuariosol"], $empresa[0]["clavesol"], $codkardex];
		return Sunat::phuyu_enviarSUNAT("sendBill", $estado["carpeta_phuyu"], $estado["archivo_phuyu"], $credenciales);
	}

	private function enviar_resumen($referencia, $codempresa){
		$partes = explode("|", $referencia);
		if (count($partes) !== 3) {
			return ["estado" => 0, "mensaje" => "Referencia de resumen invalida"];
		}
		$codresumentipo = (int)$partes[0];
		$periodo = $partes[1];
		$nrocorrelativo = (int)$partes[2];
		$resumen = $this->db->query(
			"select *from sunat.resumenes where codresumentipo=? and periodo=? and nrocorrelativo=? and codempresa=?",
			[$codresumentipo, $periodo, $nrocorrelativo, (int)$codempresa]
		)->result_array();
		if (count($resumen) === 0) {
			return ["estado" => 0, "mensaje" => "Resumen no encontrado"];
		}

		$empresa = $this->db->query("select *from public.webservice where codempresa=?", [(int)$codempresa])->result_array();
		if (count($empresa) === 0) {
			$empresa = $this->db->query("select *from public.webservice limit 1")->result_array();
		}

		$credenciales = [$_SESSION["phuyu_ruc"], $empresa[0]["usuariosol"], $empresa[0]["clavesol"], $codresumentipo, $periodo, $nrocorrelativo, (int)$codempresa];
		if ($resumen[0]["ticket"] != "") {
			return Sunat::phuyu_consultarTICKET($resumen[0]["nombre_xml"], $resumen[0]["ticket"], $credenciales);
		}

		$estado = $codresumentipo == 1
			? $this->Facturacion_model->phuyu_rf_crearXML($periodo, $nrocorrelativo)
			: $this->Facturacion_model->phuyu_rb_crearXML($periodo, $nrocorrelativo, $codresumentipo);

		if ($estado["estado"] == 0) {
			return ["estado" => 0, "mensaje" => "No se puede generar XML de resumen"];
		}
		$firma = $this->phuyu_firmarXML($estado["carpeta_phuyu"]."/".$estado["archivo_phuyu"], 0, true);
		if ($firma["estado"] != 1) {
			return ["estado" => 0, "mensaje" => "No se puede firmar XML de resumen: ".$firma["mensaje"]];
		}
		$respuesta = Sunat::phuyu_enviarSUNAT("sendSummary", $estado["carpeta_phuyu"], $estado["archivo_phuyu"], $credenciales);
		if ((int)$respuesta["estado"] === 0) {
			$resumen_actualizado = $this->db->query(
				"select nombre_xml, ticket, codigorespuesta, descripcion_cdr
				from sunat.resumenes
				where codresumentipo=? and periodo=? and nrocorrelativo=? and codempresa=?",
				[$codresumentipo, $periodo, $nrocorrelativo, (int)$codempresa]
			)->row_array();

			$descripcion = isset($resumen_actualizado["descripcion_cdr"]) ? (string)$resumen_actualizado["descripcion_cdr"] : "";
			$ya_enviado = isset($resumen_actualizado["codigorespuesta"]) && (string)$resumen_actualizado["codigorespuesta"] === "2223";
			$ya_enviado = $ya_enviado || stripos($descripcion, "ya fue enviado") !== false || stripos($descripcion, "presentado anteriormente") !== false;
			if (!empty($resumen_actualizado["ticket"]) && $ya_enviado) {
				return Sunat::phuyu_consultarTICKET($resumen_actualizado["nombre_xml"], $resumen_actualizado["ticket"], $credenciales);
			}
		}
		return $respuesta;
	}

	private function salida_json($data){
		$this->output->set_content_type("application/json", "utf-8");
		echo json_encode($data);
	}
}
