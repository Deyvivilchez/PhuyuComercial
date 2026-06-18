<?php

class Programacion_sunat_model extends CI_Model {

	public function __construct(){
		parent::__construct();
	}

	public function empresas(){
		return $this->db->query(
			"select e.codempresa, p.documento, p.nombrecomercial, p.razonsocial
			from public.empresas e
			inner join public.personas p on p.codpersona=e.codpersona
			order by e.codempresa"
		)->result_array();
	}

	public function sucursales($codempresa = 0){
		$params = [];
		$where = "where estado=1";
		if ((int)$codempresa > 0) {
			$where .= " and codempresa=?";
			$params[] = (int)$codempresa;
		}
		return $this->db->query(
			"select codsucursal, codempresa, descripcion
			from public.sucursales
			".$where."
			order by codsucursal",
			$params
		)->result_array();
	}

	public function configuraciones(){
		$configuraciones = $this->db->query(
			"select p.*, coalesce(s.descripcion, 'Todas') as sucursal
			from sunat.programacion_cpe p
			left join public.sucursales s on s.codsucursal=p.codsucursal
			where p.estado<>0
			order by p.codprogramacion desc"
		)->result_array();

		foreach ($configuraciones as $key => $value) {
			$configuraciones[$key]["horarios"] = $this->horarios((int)$value["codprogramacion"]);
		}
		return $configuraciones;
	}

	public function horarios($codprogramacion){
		return $this->db->query(
			"select *
			from sunat.programacion_cpe_horarios
			where codprogramacion=? and estado=1
			order by hora, codhorario",
			[(int)$codprogramacion]
		)->result_array();
	}

	public function guardar($datos){
		$codprogramacion = isset($datos->codprogramacion) ? (int)$datos->codprogramacion : 0;
		$data = [
			"codempresa" => isset($datos->codempresa) ? (int)$datos->codempresa : 1,
			"codsucursal" => isset($datos->codsucursal) && (int)$datos->codsucursal > 0 ? (int)$datos->codsucursal : null,
			"descripcion" => trim(isset($datos->descripcion) ? $datos->descripcion : "Programacion SUNAT"),
			"modo_envio" => isset($datos->modo_envio) ? $datos->modo_envio : "programado",
			"procesar_facturas" => !empty($datos->procesar_facturas) ? 1 : 0,
			"procesar_boletas" => !empty($datos->procesar_boletas) ? 1 : 0,
			"procesar_notas_credito" => !empty($datos->procesar_notas_credito) ? 1 : 0,
			"procesar_notas_debito" => !empty($datos->procesar_notas_debito) ? 1 : 0,
			"procesar_resumenes" => !empty($datos->procesar_resumenes) ? 1 : 0,
			"procesar_bajas" => !empty($datos->procesar_bajas) ? 1 : 0,
			"max_intentos" => isset($datos->max_intentos) ? max(1, (int)$datos->max_intentos) : 3,
			"limite_por_ejecucion" => isset($datos->limite_por_ejecucion) ? max(1, (int)$datos->limite_por_ejecucion) : 20,
			"activo" => !empty($datos->activo) ? 1 : 0,
			"actualizado_en" => date("Y-m-d H:i:s")
		];

		$this->db->trans_begin();

		if ($codprogramacion > 0) {
			$this->db->where("codprogramacion", $codprogramacion);
			$this->db->update("sunat.programacion_cpe", $data);
		} else {
			$data["creado_en"] = date("Y-m-d H:i:s");
			$this->db->insert("sunat.programacion_cpe", $data);
			$codprogramacion = (int)$this->db->insert_id("sunat.programacion_cpe_codprogramacion_seq");
		}

		$this->db->where("codprogramacion", $codprogramacion);
		$this->db->update("sunat.programacion_cpe_horarios", ["estado" => 0]);

		if (!empty($datos->horarios) && is_array($datos->horarios)) {
			foreach ($datos->horarios as $horario) {
				$hora = isset($horario->hora) ? trim($horario->hora) : "";
				if (!preg_match('/^[0-2][0-9]:[0-5][0-9]$/', $hora)) {
					continue;
				}
				$accion = isset($horario->accion) ? $horario->accion : "todo";
				$this->db->insert("sunat.programacion_cpe_horarios", [
					"codprogramacion" => $codprogramacion,
					"hora" => $hora,
					"accion" => $accion,
					"estado" => 1
				]);
			}
		}

		if ($this->db->trans_status() === FALSE) {
			$this->db->trans_rollback();
			return 0;
		}

		$this->db->trans_commit();
		return $codprogramacion;
	}

	public function eliminar($codprogramacion){
		$this->db->where("codprogramacion", (int)$codprogramacion);
		return $this->db->update("sunat.programacion_cpe", [
			"estado" => 0,
			"activo" => 0,
			"actualizado_en" => date("Y-m-d H:i:s")
		]) ? 1 : 0;
	}

	public function historial($limite = 10, $offset = 0){
		return $this->db->query(
			"select h.*, p.descripcion as programacion
			from sunat.programacion_cpe_historial h
			left join sunat.programacion_cpe p on p.codprogramacion=h.codprogramacion
			order by h.codejecucion desc
			offset ? limit ?",
			[(int)$offset, (int)$limite]
		)->result_array();
	}

	public function historial_total(){
		$total = $this->db->query(
			"select count(*) as total
			from sunat.programacion_cpe_historial"
		)->row_array();
		return (int)$total["total"];
	}

	public function cola($limite = 10, $offset = 0){
		return $this->db->query(
			"select *
			from sunat.programacion_cpe_cola
			where estado in ('pendiente','procesando','error')
			order by prioridad desc, siguiente_intento asc nulls first, codcola desc
			offset ? limit ?",
			[(int)$offset, (int)$limite]
		)->result_array();
	}

	public function cola_total(){
		$total = $this->db->query(
			"select count(*) as total
			from sunat.programacion_cpe_cola
			where estado in ('pendiente','procesando','error')"
		)->row_array();
		return (int)$total["total"];
	}

	public function limpiar_historial($dias = 30, $todo = false){
		$dias = max(1, (int)$dias);
		$this->db->trans_begin();

		if ($todo) {
			$historial = $this->db->query("delete from sunat.programacion_cpe_historial");
		} else {
			$historial = $this->db->query(
				"delete from sunat.programacion_cpe_historial
				where inicio < now() - (?::text || ' days')::interval",
				[$dias]
			);
		}
		$historial_eliminado = $this->db->affected_rows();

		if ($todo) {
			$cola = $this->db->query(
				"delete from sunat.programacion_cpe_cola
				where estado in ('enviado','descartado')"
			);
		} else {
			$cola = $this->db->query(
				"delete from sunat.programacion_cpe_cola
				where estado in ('enviado','descartado')
					and actualizado_en < now() - (?::text || ' days')::interval",
				[$dias]
			);
		}
		$cola_eliminada = $this->db->affected_rows();

		if ($this->db->trans_status() === FALSE || !$historial || !$cola) {
			$this->db->trans_rollback();
			return [
				"estado" => 0,
				"historial_eliminado" => 0,
				"cola_eliminada" => 0
			];
		}

		$this->db->trans_commit();
		return [
			"estado" => 1,
			"historial_eliminado" => (int)$historial_eliminado,
			"cola_eliminada" => (int)$cola_eliminada
		];
	}

	public function programaciones_vencidas($hora = null){
		$hora = $hora ?: date("H:i");
		return $this->db->query(
			"select p.*, h.codhorario, h.hora, h.accion
			from sunat.programacion_cpe p
			inner join sunat.programacion_cpe_horarios h on h.codprogramacion=p.codprogramacion
			where p.estado=1 and p.activo=1 and h.estado=1
				and p.modo_envio='programado'
				and to_char(h.hora, 'HH24:MI')=?
				and not exists (
					select 1
					from sunat.programacion_cpe_historial x
					where x.codprogramacion=p.codprogramacion
						and x.codhorario=h.codhorario
						and x.origen='auto'
						and x.inicio >= date_trunc('minute', now())
				)
			union all
			select p.*, null::integer as codhorario, null::time as hora, 'todo'::varchar as accion
			from sunat.programacion_cpe p
			where p.estado=1 and p.activo=1
				and p.modo_envio='inmediato'
				and not exists (
					select 1
					from sunat.programacion_cpe_historial x
					where x.codprogramacion=p.codprogramacion
						and x.origen='auto'
						and x.inicio >= date_trunc('minute', now())
				)
			order by hora nulls first, codprogramacion",
			[$hora]
		)->result_array();
	}

	public function iniciar_historial($programacion, $origen = "auto"){
		$this->db->insert("sunat.programacion_cpe_historial", [
			"codprogramacion" => (int)$programacion["codprogramacion"],
			"codhorario" => isset($programacion["codhorario"]) ? (int)$programacion["codhorario"] : null,
			"codempresa" => (int)$programacion["codempresa"],
			"codsucursal" => !empty($programacion["codsucursal"]) ? (int)$programacion["codsucursal"] : null,
			"origen" => $origen,
			"accion" => isset($programacion["accion"]) ? $programacion["accion"] : "manual",
			"estado" => "procesando",
			"inicio" => date("Y-m-d H:i:s")
		]);
		return (int)$this->db->insert_id("sunat.programacion_cpe_historial_codejecucion_seq");
	}

	public function finalizar_historial($codejecucion, $data){
		$data["fin"] = date("Y-m-d H:i:s");
		$this->db->where("codejecucion", (int)$codejecucion);
		return $this->db->update("sunat.programacion_cpe_historial", $data);
	}

	public function log($codejecucion, $nivel, $mensaje, $referencia = null){
		$this->db->insert("sunat.programacion_cpe_logs", [
			"codejecucion" => (int)$codejecucion,
			"nivel" => $nivel,
			"mensaje" => (string)$mensaje,
			"referencia" => $referencia,
			"creado_en" => date("Y-m-d H:i:s")
		]);
	}

	public function encolar($tipo, $referencia, $programacion, $prioridad = 0){
		$existe = $this->db->query(
			"select codcola
			from sunat.programacion_cpe_cola
			where tipo=?
				and referencia=?
				and estado in ('pendiente','procesando','error')
			limit 1",
			[$tipo, (string)$referencia]
		)->row_array();

		if (!empty($existe)) {
			return (int)$existe["codcola"];
		}

		$this->db->insert("sunat.programacion_cpe_cola", [
			"codprogramacion" => (int)$programacion["codprogramacion"],
			"codempresa" => (int)$programacion["codempresa"],
			"codsucursal" => !empty($programacion["codsucursal"]) ? (int)$programacion["codsucursal"] : null,
			"tipo" => $tipo,
			"referencia" => (string)$referencia,
			"estado" => "pendiente",
			"prioridad" => (int)$prioridad,
			"siguiente_intento" => date("Y-m-d H:i:s"),
			"creado_en" => date("Y-m-d H:i:s")
		]);
		return (int)$this->db->insert_id("sunat.programacion_cpe_cola_codcola_seq");
	}

	public function pendientes_cola($programacion, $limite, $forzar_reintento = false){
		$params = [(int)$programacion["codempresa"], (int)$limite];
		$where_sucursal = "";
		if (!empty($programacion["codsucursal"])) {
			$where_sucursal = " and (codsucursal is null or codsucursal=?)";
			array_splice($params, 1, 0, [(int)$programacion["codsucursal"]]);
		}
		$where_intento = $forzar_reintento ? "" : "and (siguiente_intento is null or siguiente_intento<=now())";

		return $this->db->query(
			"select *
			from sunat.programacion_cpe_cola
			where codempresa=?
				".$where_sucursal."
				and estado in ('pendiente','error')
				".$where_intento."
			order by prioridad desc, siguiente_intento asc nulls first, codcola asc
			limit ?",
			$params
		)->result_array();
	}

	public function marcar_procesando($codcola){
		$this->db->where("codcola", (int)$codcola);
		$this->db->where_in("estado", ["pendiente", "error"]);
		return $this->db->update("sunat.programacion_cpe_cola", [
			"estado" => "procesando",
			"actualizado_en" => date("Y-m-d H:i:s")
		]);
	}

	public function marcar_resultado_cola($cola, $respuesta, $max_intentos){
		$estado_respuesta = isset($respuesta["estado"]) ? (int)$respuesta["estado"] : 0;
		$intentos = (int)$cola["intentos"] + 1;
		$aceptado = in_array($estado_respuesta, [1, 2], true);
		$estado = $aceptado ? "enviado" : ($intentos >= (int)$max_intentos ? "error" : "pendiente");
		$siguiente = $aceptado ? null : date("Y-m-d H:i:s", strtotime("+10 minutes"));

		$this->db->where("codcola", (int)$cola["codcola"]);
		return $this->db->update("sunat.programacion_cpe_cola", [
			"estado" => $estado,
			"intentos" => $intentos,
			"ultimo_estado_sunat" => $estado_respuesta,
			"ultimo_mensaje" => isset($respuesta["mensaje"]) ? substr((string)$respuesta["mensaje"], 0, 1000) : "",
			"siguiente_intento" => $siguiente,
			"actualizado_en" => date("Y-m-d H:i:s")
		]);
	}
}
