<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Movimientos extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model"); $this->load->model("Caja_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$caja = $this->Caja_model->phuyu_estadocaja();
				$transferencias = $this->db->query("select count(*) as cantidad from caja.movimientos where codcaja_ref=".$_SESSION["phuyu_codcaja"]." and transferido=0 and estado=1")->result_array();
				$tipopagos = $this->db->query("select codtipopago, descripcion from caja.tipopagos where estado=1 order by descripcion")->result_array();
				$this->load->view("caja/movimientos/index",compact("transferencias","tipopagos"));
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
			$limit = 10;
			$pagina = isset($this->request->pagina) ? max(1, (int)$this->request->pagina) : 1;
			$offset = $pagina * $limit - $limit;
			$filtros = (array)$this->request;
			$where = " WHERE ".$this->filtro_consulta_movimientos($filtros, 'movimientos', 'personas', 'conceptos');
			$join_pagos = "
				left join (
					select
						md.codmovimiento,
						string_agg(distinct tp.descripcion, ', ' order by tp.descripcion) as tipopago
					from caja.movimientosdetalle md
					inner join caja.tipopagos tp on(md.codtipopago=tp.codtipopago)
					group by md.codmovimiento
				) pagos on(pagos.codmovimiento=movimientos.codmovimiento)
			";

			$lista = $this->db->query("select movimientos.*, round(movimientos.importe,2) as importe_r, personas.razonsocial,conceptos.descripcion as concepto, COALESCE(pagos.tipopago, '-') as tipopago from caja.movimientos as movimientos inner join public.personas as personas on(movimientos.codpersona=personas.codpersona) inner join caja.conceptos as conceptos on(movimientos.codconcepto=conceptos.codconcepto) ".$join_pagos." ".$where." order by movimientos.codmovimiento desc offset ".$offset." limit ".$limit)->result_array();
			foreach ($lista as $key => $value) {
				if ($value["codkardex"]==0) {
					$creditos = $this->db->query("select codmovimiento from kardex.creditos where codmovimiento=".$value["codmovimiento"])->result_array();
					$pagos = $this->db->query("select codmovimiento from kardex.cuotaspagos where codmovimiento=".$value["codmovimiento"])->result_array();

					if (count($creditos)>0 || count($pagos)>0) {
						$lista[$key]["ver"] = 1;
					}else{
						$lista[$key]["ver"] = 0;
					}
				}else{
					$lista[$key]["ver"] = 1;
				}
			}

			$total = $this->db->query("select count(*) as total from caja.movimientos as movimientos inner join public.personas as personas on(movimientos.codpersona=personas.codpersona) inner join caja.conceptos as conceptos on(movimientos.codconcepto=conceptos.codconcepto) ".$join_pagos." ".$where)->result_array();

			$paginas = floor($total[0]["total"] / $limit);
			if ( ($total[0]["total"] % $limit)!=0 ) {
				$paginas = $paginas + 1;
			}

			$paginacion = array();
			$paginacion["total"] = $total[0]["total"];
			$paginacion["actual"] = $pagina;
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
				$tipocomprobantes = $this->db->query("select *from caja.comprobantetipos where codcomprobantetipo>=10 and estado=1 order by codcomprobantetipo")->result_array();
				$cajas = $this->db->query("select caja.codcaja,caja.descripcion,sucursal.descripcion as sucursal from caja.cajas as caja inner join public.sucursales as sucursal on(caja.codsucursal=sucursal.codsucursal) where caja.estado=1 and caja.codcaja<>".$_SESSION["phuyu_codcaja"])->result_array();
				$this->load->view("caja/movimientos/nuevo",compact("tipocomprobantes","cajas"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function nuevo_1($tipomovimiento,$codkardex){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_usuario"])) {
				$tipomovimiento = (int)$tipomovimiento;
				$codkardex = (int)$codkardex;
				$configuracion_error = "";
				$tipocomprobantes = $this->db->query("select *from caja.comprobantetipos where codcomprobantetipo>=10 and estado=1 order by codcomprobantetipo")->result_array();
				if ($tipomovimiento==1) {
					$comprobante_caja = $this->db->query("select caja.comprobantetipos.* from caja.comprobantetipos inner join caja.comprobantes on(caja.comprobantetipos.codcomprobantetipo=caja.comprobantes.codcomprobantetipo) where caja.comprobantetipos.codcomprobantetipo=1 and caja.comprobantes.codsucursal=".$_SESSION["phuyu_codsucursal"]." and caja.comprobantes.codcaja=".$_SESSION["phuyu_codcaja"]." and caja.comprobantes.estado=1 order by caja.comprobantetipos.codcomprobantetipo")->result_array();
				}else{
					$comprobante_caja = $this->db->query("select caja.comprobantetipos.* from caja.comprobantetipos inner join caja.comprobantes on(caja.comprobantetipos.codcomprobantetipo=caja.comprobantes.codcomprobantetipo) where caja.comprobantetipos.codcomprobantetipo=2 and caja.comprobantes.codsucursal=".$_SESSION["phuyu_codsucursal"]." and caja.comprobantes.codcaja=".$_SESSION["phuyu_codcaja"]." and caja.comprobantes.estado=1 order by caja.comprobantetipos.codcomprobantetipo")->result_array();
				}
				$conceptos = $this->db->query("select *from caja.conceptos where tipo=".$tipomovimiento." and estado=1 order by codconcepto")->result_array();

				if (count($comprobante_caja)==0) {
					$configuracion_error = "No hay comprobante de caja configurado para este movimiento en la caja actual.";
					$comprobante_caja = [["codcomprobantetipo" => ($tipomovimiento==1 ? 1 : 2)]];
				}

				$series = $this->db->query("select seriecomprobante from caja.comprobantes where codcomprobantetipo=".$comprobante_caja[0]["codcomprobantetipo"]." and codsucursal=".$_SESSION["phuyu_codsucursal"]." and codcaja=".$_SESSION["phuyu_codcaja"]." and estado=1")->result_array();
				if (count($series)==0) {
					$configuracion_error = "No hay serie activa para el comprobante de caja en la caja actual.";
					$series = [["seriecomprobante" => ""]];
				}

				if ($comprobante_caja[0]["codcomprobantetipo"]==1) {
					$tipopagos = $this->db->query("select *from caja.tipopagos where (ingreso=1 or abono=1) and estado=1 order by codtipopago")->result_array();
				}else{
					$tipopagos = $this->db->query("select *from caja.tipopagos where (egreso=1 or cargo=1) and estado=1 order by codtipopago")->result_array();
				}

				if ($codkardex==0) {
					$productos = [];
				}else{
					$productos = $this->db->query("
						select distinct p.codproducto, p.descripcion
						from almacen.productos as p
						left join almacen.productounidades as pu on(p.codproducto=pu.codproducto and pu.estado=1)
						where p.estado=1
						and (
							coalesce(p.tipo, 0)=2
							or coalesce(p.controlstock, 0)=0
						)
						order by p.descripcion
					")->result_array();
				}

				$this->load->view("caja/movimientos/nuevo_1",compact("tipocomprobantes","tipomovimiento","codkardex","comprobante_caja","conceptos","series","tipopagos","productos","configuracion_error"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function tipomovimiento($tipomovimiento){
		if ($this->input->is_ajax_request()) {
			if ($tipomovimiento==1) {
				$tipocomprobantes = $this->db->query("select caja.comprobantetipos.* from caja.comprobantetipos inner join caja.comprobantes on(caja.comprobantetipos.codcomprobantetipo=caja.comprobantes.codcomprobantetipo) where caja.comprobantetipos.codcomprobantetipo=1 and caja.comprobantes.codsucursal=".$_SESSION["phuyu_codsucursal"]." and caja.comprobantes.codcaja=".$_SESSION["phuyu_codcaja"]." and caja.comprobantes.estado=1 order by caja.comprobantetipos.codcomprobantetipo")->result_array();
			}else{
				$tipocomprobantes = $this->db->query("select caja.comprobantetipos.* from caja.comprobantetipos inner join caja.comprobantes on(caja.comprobantetipos.codcomprobantetipo=caja.comprobantes.codcomprobantetipo) where caja.comprobantetipos.codcomprobantetipo=2 and caja.comprobantes.codsucursal=".$_SESSION["phuyu_codsucursal"]." and caja.comprobantes.codcaja=".$_SESSION["phuyu_codcaja"]." and caja.comprobantes.estado=1 order by caja.comprobantetipos.codcomprobantetipo")->result_array();
			}
			$conceptos = $this->db->query("select *from caja.conceptos where tipo=".$tipomovimiento." and estado=1 order by codconcepto")->result_array();

			$data = array();
			$data["comprobantes"] = $tipocomprobantes;
			$data["conceptos"] = $conceptos;
			echo json_encode($data);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function tipopagos($comprobantetipo){
		if ($this->input->is_ajax_request()) {
			if ($comprobantetipo==1) {
				$tipopagos = $this->db->query("select *from caja.tipopagos where (ingreso=1 or abono=1) and estado=1 order by codtipopago")->result_array();
			}else{
				$tipopagos = $this->db->query("select *from caja.tipopagos where (egreso=1 or cargo=1) and estado=1 order by codtipopago")->result_array();
			}

			$series = $this->db->query("select seriecomprobante from caja.comprobantes where codcomprobantetipo=".$comprobantetipo." and codsucursal=".$_SESSION["phuyu_codsucursal"]." and codcaja=".$_SESSION["phuyu_codcaja"]." and estado=1")->result_array();

			$data = array();
			$data["serie"] = $series[0]["seriecomprobante"];
			$data["tipopagos"] = $tipopagos;
			echo json_encode($data);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function guardar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));

			$campos = ["codcontroldiario","codcaja","codconcepto","codpersona","codusuario","codcomprobantetipo","seriecomprobante","tipomovimiento","codcomprobantetipo_ref","seriecomprobante_ref","nrocomprobante_ref","importe","referencia","codcaja_ref"];
			$campos_1 = ["codmovimiento","codtipopago","codcontroldiario","codcaja","fechadocbanco","nrodocbanco","importe","importeentregado"];

			$valores = [
				(int)$_SESSION["phuyu_codcontroldiario"],
				(int)$_SESSION["phuyu_codcaja"],
				(int)$this->request->codconcepto,
				(int)$this->request->codpersona,
				(int)$_SESSION["phuyu_codusuario"],
				(int)$this->request->codcomprobantetipo,
				$this->request->seriecomprobante,
				(int)$this->request->tipomovimiento,
				(int)$this->request->codcomprobantetipo_ref,
				$this->request->seriecomprobante_ref,
				$this->request->nrocomprobante_ref,
				(double)$this->request->importe,
				$this->request->referencia,
				(int)$this->request->codcaja_ref
			];

			if($this->request->codregistro=="") {
				$campos_insert = array_merge($campos, ["horamovimiento"]);
				$valores_insert = array_merge($valores, [date("H:i:s")]);
				$codmovimiento = $this->phuyu_model->phuyu_guardar("caja.movimientos", $campos_insert, $valores_insert, "true");
				$estado = $this->Caja_model->phuyu_correlativo($codmovimiento,$this->request->codcomprobantetipo,$this->request->seriecomprobante);

				$valores_1 = [(int)$codmovimiento,(int)$this->request->codtipopago,(int)$_SESSION["phuyu_codcontroldiario"],(int)$_SESSION["phuyu_codcaja"],$this->request->fechadocbanco,$this->request->nrodocbanco,(double)$this->request->importe,(double)$this->request->importe];
				$estado = $this->phuyu_model->phuyu_guardar("caja.movimientosdetalle", $campos_1, $valores_1);
			}else{
				$estado = $this->phuyu_model->phuyu_editar("caja.movimientos", $campos, $valores, "codmovimiento", $this->request->codregistro);

				$valores_1 = [(int)$this->request->codregistro,(int)$this->request->codtipopago,(int)$_SESSION["phuyu_codcontroldiario"],(int)$_SESSION["phuyu_codcaja"],$this->request->fechadocbanco,$this->request->nrodocbanco,(double)$this->request->importe,(double)$this->request->importe];
				$estado = $this->phuyu_model->phuyu_editar("caja.movimientosdetalle", $campos_1, $valores_1,"codmovimiento", $this->request->codregistro);
			}
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function editar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$info = $this->db->query("select codmovimiento as codregistro,* from caja.movimientos where codmovimiento=".$this->request->codregistro)->result_array();
			echo json_encode($info);
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function editarmovimiento(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$info = $this->db->query("select mov.codpersona, mov.codcontroldiario, mov.tipomovimiento,mov.codcomprobantetipo,mov.codconcepto, movdetalle.codtipopago, movdetalle.fechadocbanco, movdetalle.nrodocbanco from caja.movimientos as mov inner join caja.movimientosdetalle as movdetalle on(mov.codmovimiento=movdetalle.codmovimiento) where mov.codmovimiento=".$this->request->codregistro)->result_array();
			if ($_SESSION["phuyu_codcontroldiario"]==$info[0]["codcontroldiario"]) {
				$editar = 1;
			}else{
				$editar = 0;
			}
			$socio =$this->db->query("select codpersona,razonsocial from public.personas where codpersona=".$info[0]["codpersona"])->result_array();

			$data = array();
			$data["info"] = $info;
			$data["socio"] = $socio;
			$data["editar"] = $editar;
			echo json_encode($data);
		}
	}

	function eliminar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$estado = $this->phuyu_model->phuyu_eliminar("caja.movimientos", "codmovimiento", $this->request->codregistro);
			echo $estado;
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function transferencias(){
		if ($this->input->is_ajax_request()) {
			$lista = $this->db->query("select movimientos.*, round(movimientos.importe,2) as importe_r, personas.razonsocial,conceptos.descripcion as concepto,cajas.descripcion as caja from caja.movimientos as movimientos inner join public.personas as personas on(movimientos.codpersona=personas.codpersona) inner join caja.conceptos as conceptos on(movimientos.codconcepto=conceptos.codconcepto) inner join caja.cajas as cajas on(movimientos.codcaja=cajas.codcaja) where movimientos.codcaja_ref=".$_SESSION["phuyu_codcaja"]." and movimientos.transferido=0 and movimientos.estado=1")->result_array();
			foreach ($lista as $key => $value) {
				$tipopago = $this->db->query("select codtipopago,fechadocbanco,nrodocbanco from caja.movimientosdetalle where codmovimiento=".$value["codmovimiento"])->result_array();
				$lista[$key]["codtipopago"] = $tipopago[0]["codtipopago"];
				$lista[$key]["fechadocbanco"] = $tipopago[0]["fechadocbanco"];
				$lista[$key]["nrodocbanco"] = $tipopago[0]["nrodocbanco"];
			}
			echo json_encode($lista);
		}
	}

	function aceptar_transferencia(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			if (empty($_SESSION["phuyu_codusuario"]) || empty($_SESSION["phuyu_codcaja"]) || empty($_SESSION["phuyu_codcontroldiario"])) {
				return $this->respuesta_transferencia(false, "La sesion no tiene una caja abierta. Inicie sesion y abra la caja.", 401);
			}
			if (empty($this->request->campos->codmovimiento)) {
				return $this->respuesta_transferencia(false, "Seleccione una transferencia valida.", 400);
			}
			$columna = $this->db->query("SELECT 1 FROM information_schema.columns WHERE table_schema = 'caja' AND table_name = 'movimientos' AND column_name = 'codmovimiento_ref'");
			if (!$columna || $columna->num_rows() === 0) {
				return $this->respuesta_transferencia(false, "Falta actualizar la base de datos. Ejecute la migracion 007 de phuyu_migraciones_ordenado2.sql.", 409);
			}

			$comprobante_ingresos = 1;
			$series = $this->db->query("select seriecomprobante from caja.comprobantes where codcomprobantetipo=".$comprobante_ingresos." and codsucursal=".$_SESSION["phuyu_codsucursal"]." and codcaja=".$_SESSION["phuyu_codcaja"]." and estado=1")->result_array();

			if (empty($series)) {
				return $this->respuesta_transferencia(false, "La caja no tiene una serie activa para comprobantes de ingreso.", 422);
			}

			$campos = ["codmovimiento_ref","codcontroldiario","codcaja","codconcepto","codpersona","codusuario","codcomprobantetipo","seriecomprobante","tipomovimiento","codcomprobantetipo_ref","seriecomprobante_ref","nrocomprobante_ref","importe","referencia","codcaja_ref","transferido","horamovimiento"];

			$valores = [
				(int)$this->request->campos->codmovimiento,
				(int)$_SESSION["phuyu_codcontroldiario"],
				(int)$_SESSION["phuyu_codcaja"],26,
				(int)$this->request->campos->codpersona,
				(int)$_SESSION["phuyu_codusuario"],
				(int)$comprobante_ingresos,
				$series[0]["seriecomprobante"],1,
				(int)$this->request->campos->codcomprobantetipo,
				$this->request->campos->seriecomprobante,
				$this->request->campos->nrocomprobante,
				(double)$this->request->campos->importe,
				$this->request->referencia,
				(int)$this->request->campos->codcaja,1,date("H:i:s")
			];
			$this->db->trans_begin();
			if (!$this->db->insert("caja.movimientos", array_combine($campos, $valores))) {
				$this->db->trans_rollback();
				return $this->respuesta_transferencia(false, "No se pudo registrar el ingreso de la transferencia. No se guardaron cambios.", 500);
			}
			$codmovimiento = $this->db->insert_id();
			$estado = $this->Caja_model->phuyu_correlativo($codmovimiento,$comprobante_ingresos,$series[0]["seriecomprobante"]);

			$campos_1 = ["codmovimiento","codtipopago","codcontroldiario","codcaja","fechadocbanco","nrodocbanco","importe","importeentregado"];
			$valores_1 = [
				(int)$codmovimiento,
				(int)$this->request->campos->codtipopago,
				(int)$_SESSION["phuyu_codcontroldiario"],
				(int)$_SESSION["phuyu_codcaja"],
				$this->request->campos->fechadocbanco,
				$this->request->campos->nrodocbanco,
				(double)$this->request->campos->importe,
				(double)$this->request->campos->importe
			];
			$estado = $this->phuyu_model->phuyu_guardar("caja.movimientosdetalle", $campos_1, $valores_1);

			$campos = ["transferido"]; $valores = [1];
			$estado = $this->phuyu_model->phuyu_editar("caja.movimientos", $campos, $valores, "codmovimiento", $this->request->campos->codmovimiento);

			if ($this->db->trans_status() === FALSE) {
				$this->db->trans_rollback();
				return $this->respuesta_transferencia(false, "No se pudo completar la transferencia. No se guardaron cambios.", 500);
			} else {
				if (!$this->db->trans_commit()) {
					return $this->respuesta_transferencia(false, "No se pudo confirmar la transferencia. Revise el estado antes de reintentar.", 500);
				}
				return $this->respuesta_transferencia(true, "Transferencia aceptada correctamente.", 200, $codmovimiento);
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}


	private function respuesta_transferencia($exito, $mensaje, $http, $codmovimiento = null){
		return $this->output
			->set_status_header($http)
			->set_content_type('application/json', 'utf-8')
			->set_output(json_encode([
				"exito" => (bool)$exito,
				"mensaje" => $mensaje,
				"codmovimiento" => $codmovimiento
			]));
	}






    // Un mismo filtro para la lista, Excel y PDF. La caja siempre sale de la sesion.
    private function filtro_consulta_movimientos($filtros, $m, $p, $c)
    {
        $historial = isset($filtros['vista']) && $filtros['vista'] === 'historial';
        $desde = isset($filtros['fecha_desde']) ? trim($filtros['fecha_desde']) : '';
        $hasta = isset($filtros['fecha_hasta']) ? trim($filtros['fecha_hasta']) : '';
        foreach ([$desde, $hasta] as $fecha) {
            if ($fecha !== '') {
                $valor = DateTime::createFromFormat('!Y-m-d', $fecha);
                if (!$valor || $valor->format('Y-m-d') !== $fecha) {
                    show_error('Seleccione fechas validas.', 422);
                    exit;
                }
            }
        }
        if (($historial && ($desde === '' || $hasta === '')) || ($desde !== '' && $hasta !== '' && $desde > $hasta)) {
            show_error('Seleccione un rango de fechas valido para consultar el historial.', 422);
            exit;
        }
        $where = "$m.codcaja=".(int)$_SESSION['phuyu_codcaja']." AND $m.estado=1 AND $m.condicionpago=1";
        if (!$historial) {
            $where .= " AND $m.codcontroldiario=".(int)$_SESSION['phuyu_codcontroldiario'];
        }
        if ($desde !== '') { $where .= " AND $m.fechamovimiento >= ".$this->db->escape($desde); }
        if ($hasta !== '') { $where .= " AND $m.fechamovimiento <= ".$this->db->escape($hasta); }
        $tipo = isset($filtros['tipomovimiento']) ? (int)$filtros['tipomovimiento'] : 0;
        if ($tipo === 1 || $tipo === 2) { $where .= " AND $m.tipomovimiento=".$tipo; }
        $pago = isset($filtros['codtipopago']) ? (int)$filtros['codtipopago'] : 0;
        if ($pago > 0) {
            $where .= " AND EXISTS (SELECT 1 FROM caja.movimientosdetalle md_f WHERE md_f.codmovimiento=$m.codmovimiento AND md_f.codtipopago=".$pago.")";
        }
        $buscar = isset($filtros['buscar']) ? trim($filtros['buscar']) : '';
        if ($buscar !== '') {
            $texto = $this->db->escape('%'.$this->db->escape_like_str($buscar).'%');
            $where .= " AND (CONCAT($m.seriecomprobante, '-', $m.nrocomprobante) ILIKE $texto ESCAPE '!'"
                ." OR $p.razonsocial ILIKE $texto ESCAPE '!' OR $c.descripcion ILIKE $texto ESCAPE '!'"
                ." OR $m.referencia ILIKE $texto ESCAPE '!'"
                ." OR EXISTS (SELECT 1 FROM caja.movimientosdetalle md_b JOIN caja.tipopagos tp_b ON tp_b.codtipopago=md_b.codtipopago WHERE md_b.codmovimiento=$m.codmovimiento AND tp_b.descripcion ILIKE $texto ESCAPE '!'))";
        }
        return $where;
    }

public function exportar_excel_detallado()
{
    $desde           = $this->input->get('desde');
    $hasta           = $this->input->get('hasta');
    $codcaja          = $_SESSION['phuyu_codcaja'];

    if (empty($desde) || empty($hasta)) {
        show_error('Debe seleccionar un rango de fechas válido.');
        return;
    }

    $filtros = (array)$this->input->get();
    $filtros['vista'] = isset($filtros['vista']) ? $filtros['vista'] : 'historial';
    $filtros['fecha_desde'] = $desde;
    $filtros['fecha_hasta'] = $hasta;
    $where = $this->filtro_consulta_movimientos($filtros, 'm', 'p', 'c');
    $sql = "
        SELECT 
            m.codmovimiento,
            m.fechamovimiento,
            COALESCE(
                TO_CHAR(m.horamovimiento, 'HH24:MI:SS'),
                SPLIT_PART(k.hora::text, '.', 1)
            ) AS horamovimiento,
            m.seriecomprobante,
            m.nrocomprobante,
            m.codkardex,
            p.razonsocial,
            c.descripcion         AS concepto_caja,
            tp.descripcion        AS tipopago,
            md.importe            AS importe_pago,
            md.importeentregado   AS importe_entregado,
            md.vuelto             AS vuelto,
            ROUND(m.importe, 2)   AS total_movimiento,
			CASE
				WHEN k.codkardex IS NULL THEN COALESCE(NULLIF(CONCAT(m.seriecomprobante_ref,'-',m.nrocomprobante_ref), '-'), '')
				ELSE CONCAT(k.seriecomprobante,'-',k.nrocomprobante)
			END AS comprobante_referencia
        FROM caja.movimientosdetalle AS md
        JOIN caja.movimientos AS m       ON m.codmovimiento = md.codmovimiento
        JOIN public.personas AS p        ON p.codpersona = m.codpersona
        JOIN caja.conceptos AS c         ON c.codconcepto = m.codconcepto
        JOIN caja.tipopagos AS tp        ON tp.codtipopago = md.codtipopago
		LEFT JOIN kardex.kardex AS k        ON k.codkardex = m.codkardex
        WHERE {$where}
        ORDER BY m.fechamovimiento, m.codmovimiento
    ";

    $data['movimientos'] = $this->db->query($sql)->result_array();
    $data['desde'] = $this->input->get('desde');
    $data['hasta'] = $this->input->get('hasta');

    $this->load->view('reportes/excel_movimientos_detallado', $data);
}


public function exportar_pdf_detallado()
{
    $desde = $this->input->get('desde');
    $hasta = $this->input->get('hasta');
    $codcaja = $_SESSION['phuyu_codcaja'];
	$nombreEmpresa =  $_SESSION["phuyu_empresa"] ;
	$logoEmpresa = $_SESSION["phuyu_logo"] ;

    if (empty($desde) || empty($hasta)) {
        show_error('Debe seleccionar un rango de fechas válido.');
        return;
    }

    $filtros = (array)$this->input->get();
    $filtros['vista'] = isset($filtros['vista']) ? $filtros['vista'] : 'historial';
    $filtros['fecha_desde'] = $desde;
    $filtros['fecha_hasta'] = $hasta;
    $where = $this->filtro_consulta_movimientos($filtros, 'm', 'p', 'c');
    $sql = "
        SELECT 
            m.codmovimiento,
            m.fechamovimiento,
            COALESCE(
                TO_CHAR(m.horamovimiento, 'HH24:MI:SS'),
                SPLIT_PART(k.hora::text, '.', 1)
            ) AS horamovimiento,
            m.seriecomprobante,
            m.nrocomprobante,
            m.codkardex,
            p.razonsocial,
            c.descripcion         AS concepto_caja,
            tp.descripcion        AS tipopago,
            md.importe            AS importe_pago,
            md.importeentregado   AS importe_entregado,
            md.vuelto             AS vuelto,
            ROUND(m.importe, 2)   AS total_movimiento,
            CASE
                WHEN k.codkardex IS NULL THEN COALESCE(NULLIF(CONCAT(m.seriecomprobante_ref,'-',m.nrocomprobante_ref), '-'), '')
                ELSE CONCAT(k.seriecomprobante,'-',k.nrocomprobante)
            END AS comprobante_referencia
        FROM caja.movimientosdetalle AS md
        JOIN caja.movimientos AS m       ON m.codmovimiento = md.codmovimiento
        JOIN public.personas AS p        ON p.codpersona = m.codpersona
        JOIN caja.conceptos AS c         ON c.codconcepto = m.codconcepto
        JOIN caja.tipopagos AS tp        ON tp.codtipopago = md.codtipopago
        LEFT JOIN kardex.kardex AS k          ON k.codkardex = m.codkardex
        WHERE {$where}
        ORDER BY m.fechamovimiento, m.codmovimiento
    ";

    $data['movimientos'] = $this->db->query($sql)->result_array();
    $data['desde'] = $desde;
    $data['hasta'] = $hasta;
	$data['nombreEmpresa'] = $nombreEmpresa;
	$data['logoEmpresa'] = $logoEmpresa;

    // Simplemente devuelve la vista renderizada en navegador
    $this->load->view('reportes/pdf_movimientos_detallado', $data);
}



public function exportar_pdf_detallado_02()
{
   $desde = $this->input->get('desde');
    $hasta = $this->input->get('hasta');
    $codcaja = $_SESSION['phuyu_codcaja'];
	$nombreEmpresa =  $_SESSION["phuyu_empresa"] ;
	$logoEmpresa = $_SESSION["phuyu_logo"] ;

    if (empty($desde) || empty($hasta)) {
        show_error('Debe seleccionar un rango de fechas válido.');
        return;
    }

    $sql = "
        SELECT 
            m.codmovimiento,
            m.fechamovimiento,
            COALESCE(
                TO_CHAR(m.horamovimiento, 'HH24:MI:SS'),
                SPLIT_PART(k.hora::text, '.', 1)
            ) AS horamovimiento,
            m.seriecomprobante,
            m.nrocomprobante,
            m.codkardex,
            p.razonsocial,
            c.descripcion         AS concepto_caja,
            tp.descripcion        AS tipopago,
            md.importe            AS importe_pago,
            md.importeentregado   AS importe_entregado,
            md.vuelto             AS vuelto,
            ROUND(m.importe, 2)   AS total_movimiento,
            CASE
                WHEN k.codkardex IS NULL THEN COALESCE(NULLIF(CONCAT(m.seriecomprobante_ref,'-',m.nrocomprobante_ref), '-'), '')
                ELSE CONCAT(k.seriecomprobante,'-',k.nrocomprobante)
            END AS comprobante_referencia
        FROM caja.movimientosdetalle AS md
        JOIN caja.movimientos AS m       ON m.codmovimiento = md.codmovimiento
        JOIN public.personas AS p        ON p.codpersona = m.codpersona
        JOIN caja.conceptos AS c         ON c.codconcepto = m.codconcepto
        JOIN caja.tipopagos AS tp        ON tp.codtipopago = md.codtipopago
        LEFT JOIN kardex.kardex AS k          ON k.codkardex = m.codkardex
        WHERE m.fechamovimiento BETWEEN {$this->db->escape($desde)} AND {$this->db->escape($hasta)}
        AND m.codcaja = {$this->db->escape($codcaja)}
        AND m.estado = 1
        AND m.condicionpago = 1
        AND tp.estado = 1
        ORDER BY m.fechamovimiento, m.codmovimiento
    ";

    $data['movimientos'] = $this->db->query($sql)->result_array();
    $data['desde'] = $desde;
    $data['hasta'] = $hasta;
	$data['nombreEmpresa'] = $nombreEmpresa;
	$data['logoEmpresa'] = $logoEmpresa;

    // Simplemente devuelve la vista renderizada en navegador
     $html = $this->load->view('reportes/pdf_movimientos_detallado', $data,true);
    //$html = $this->load->view('reportes/pdf_movimientos_detallado', $data, true);

    // 2) Cargar TCPDF
    require_once(APPPATH . 'third_party/phuyu_tcpdf/tcpdf.php'); // ajusta la ruta si la tienes en otro lado

    // 3) Configurar TCPDF (horizontal, A4)
    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Phuyu System');
    $pdf->SetAuthor('Phuyu System');
    $pdf->SetTitle('Reporte de Movimientos Detallado');

    // Márgenes pequeños para aprovechar el ancho
    $pdf->SetMargins(5, 5, 5);
    $pdf->SetAutoPageBreak(TRUE, 5);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // 4) Nueva página
    $pdf->AddPage();

    // 5) Escribir el HTML (TU DISEÑO TAL CUAL)
    $pdf->writeHTML($html, true, false, true, false, '');

    // 6) Salida del PDF al navegador
    $nombreArchivo = 'reporte_movimientos_detallado_' . date('Ymd_His') . '.pdf';
    $pdf->Output($nombreArchivo, 'I'); // 'I' = inline, 'D' = descarga directa
}



	
}
