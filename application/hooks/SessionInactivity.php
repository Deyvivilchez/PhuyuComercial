<?php defined('BASEPATH') OR exit('No direct script access allowed');

class SessionInactivity {

	public function handle(){
		if (php_sapi_name() === 'cli' || empty($_SESSION["phuyu_usuario"])) {
			return;
		}

		$CI =& get_instance();
		$clase = strtolower($CI->router->fetch_class());
		$metodo = strtolower($CI->router->fetch_method());

		if ($clase === 'phuyu' && in_array($metodo, ['phuyu_login', 'phuyu_logout', 'phuyu_logout2'], true)) {
			return;
		}

		$configuracion = $this->obtener_configuracion($CI);
		$ahora = time();

		$_SESSION["phuyu_sesion_cerrar_inactividad"] = (int)$configuracion["cerrar_inactividad"];
		$_SESSION["phuyu_sesion_tiempo_minutos"] = (int)$configuracion["tiempo_inactividad_minutos"];
		$_SESSION["phuyu_sesion_mostrar_aviso"] = (int)$configuracion["mostrar_aviso"];
		$_SESSION["phuyu_sesion_minutos_aviso"] = (int)$configuracion["minutos_aviso"];

		if ((int)$configuracion["cerrar_inactividad"] !== 1) {
			$_SESSION["phuyu_ultima_actividad"] = $ahora;
			return;
		}

		$ultimaActividad = isset($_SESSION["phuyu_ultima_actividad"]) ? (int)$_SESSION["phuyu_ultima_actividad"] : $ahora;
		$limiteSegundos = max(1, (int)$configuracion["tiempo_inactividad_minutos"]) * 60;

		if (($ahora - $ultimaActividad) > $limiteSegundos) {
			$this->cerrar_sesion($CI);
			return;
		}

		$_SESSION["phuyu_ultima_actividad"] = $ahora;
	}

	private function obtener_configuracion($CI){
		$default = [
			"cerrar_inactividad" => 0,
			"tiempo_inactividad_minutos" => 120,
			"mostrar_aviso" => 1,
			"minutos_aviso" => 5
		];

		try {
			$tabla = $CI->db->query("select to_regclass('public.configuracion_sesion') as tabla")->row_array();
			if (empty($tabla) || empty($tabla["tabla"])) {
				return $default;
			}

			$codempresa = isset($_SESSION["phuyu_codempresa"]) ? (int)$_SESSION["phuyu_codempresa"] : 0;
			$configEmpresa = [];
			if ($codempresa > 0) {
				$configEmpresa = $CI->db->query(
					"select * from public.configuracion_sesion
					where alcance='empresa' and codempresa=? and estado=1
					order by codconfiguracion desc limit 1",
					[$codempresa]
				)->row_array();
			}

			if (!empty($configEmpresa)) {
				return $this->normalizar($configEmpresa, $default);
			}

			$configGlobal = $CI->db->query(
				"select * from public.configuracion_sesion
				where alcance='global' and estado=1
				order by codconfiguracion desc limit 1"
			)->row_array();

			if (!empty($configGlobal)) {
				return $this->normalizar($configGlobal, $default);
			}
		} catch (Throwable $e) {
			log_message("error", "No se pudo validar configuracion de sesion: " . $e->getMessage());
		} catch (Exception $e) {
			log_message("error", "No se pudo validar configuracion de sesion: " . $e->getMessage());
		}

		return $default;
	}

	private function normalizar($configuracion, $default){
		$configuracion = array_merge($default, $configuracion);
		$configuracion["cerrar_inactividad"] = (int)$configuracion["cerrar_inactividad"] === 1 ? 1 : 0;
		$configuracion["tiempo_inactividad_minutos"] = max(1, min((int)$configuracion["tiempo_inactividad_minutos"], 1440));
		$configuracion["mostrar_aviso"] = (int)$configuracion["mostrar_aviso"] === 1 ? 1 : 0;
		$configuracion["minutos_aviso"] = max(1, min((int)$configuracion["minutos_aviso"], $configuracion["tiempo_inactividad_minutos"]));
		return $configuracion;
	}

	private function cerrar_sesion($CI){
		session_unset();
		session_destroy();

		if ($CI->input->is_ajax_request()) {
			$CI->output
				->set_status_header(401)
				->set_content_type("application/json", "utf-8")
				->set_output(json_encode([
					"estado" => 0,
					"sesion_expirada" => 1,
					"mensaje" => "Sesion expirada por inactividad"
				]))
				->_display();
			exit;
		}

		redirect(base_url());
		exit;
	}
}
