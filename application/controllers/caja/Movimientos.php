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
				$puede_restaurar = $this->puede_restaurar_movimientos();
				$this->load->view("caja/movimientos/index",compact("transferencias","tipopagos","puede_restaurar"));
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
			$where = " WHERE ".$this->filtro_consulta_movimientos($filtros, 'movimientos', 'personas', 'conceptos', true);
			$join_documento = $this->join_documento_movimiento('movimientos');
			$join_pagos = "
				left join (
					select
						md.codmovimiento,
						string_agg(distinct tp.descripcion, ', ' order by tp.descripcion) as tipopago,
						round(COALESCE(sum(md.importe), 0), 2) as importe_pagado,
						round(COALESCE(sum(md.importeentregado), 0), 2) as importe_entregado,
						round(COALESCE(sum(md.vuelto), 0), 2) as vuelto
					from caja.movimientosdetalle md
					left join caja.tipopagos tp on(md.codtipopago=tp.codtipopago)
					group by md.codmovimiento
				) pagos on(pagos.codmovimiento=movimientos.codmovimiento)
			";

			$lista = $this->db->query("select movimientos.*, round(movimientos.importe,2) as importe_r, personas.razonsocial,conceptos.descripcion as concepto, COALESCE(pagos.tipopago, '-') as tipopago, COALESCE(pagos.importe_pagado, 0) AS importe_pagado, COALESCE(pagos.importe_entregado, 0) AS importe_entregado, COALESCE(pagos.vuelto, 0) AS vuelto, COALESCE(documento.comprobante, NULLIF(CONCAT(movimientos.seriecomprobante_ref, '-', movimientos.nrocomprobante_ref), '-')) AS comprobante_referencia, COALESCE(documento.tipo, tipo_ref.descripcion) AS tipo_comprobante_referencia, documento.fecha AS fecha_comprobante_referencia, to_char(movimientos.horamovimiento, 'HH24:MI:SS') AS hora_movimiento from caja.movimientos as movimientos inner join public.personas as personas on(movimientos.codpersona=personas.codpersona) inner join caja.conceptos as conceptos on(movimientos.codconcepto=conceptos.codconcepto) ".$join_pagos.$join_documento." ".$where." order by movimientos.codmovimiento desc offset ".$offset." limit ".$limit)->result_array();
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


    private function puede_restaurar_movimientos()
    {
        if (empty($_SESSION['phuyu_codusuario'])) { return false; }
        $usuario = $this->db->query(
            "SELECT u.codusuario FROM seguridad.usuarios u JOIN seguridad.perfiles p ON p.codperfil=u.codperfil
             WHERE u.codusuario=? AND u.estado=1 AND p.estado=1
             AND UPPER(TRIM(p.descripcion)) IN ('ADMINISTRADOR', 'DESARROLLADOR', 'GERENTE')",
            [(int)$_SESSION['phuyu_codusuario']]
        );
        return $usuario && $usuario->num_rows() === 1;
    }

    private function respuesta_restauracion($exito, $mensaje, $http = 200)
    {
        return $this->output->set_status_header($http)->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode(['exito'=>(bool)$exito, 'mensaje'=>$mensaje]));
    }

    public function restaurar()
    {
        if (!$this->input->is_ajax_request() || $this->input->method(true) !== 'POST') {
            return $this->respuesta_restauracion(false, 'Solicitud no válida.', 400);
        }
        if (!$this->puede_restaurar_movimientos()) {
            return $this->respuesta_restauracion(false, 'Solo Gerente, Administrador o Desarrollador pueden restaurar movimientos anulados.', 403);
        }
        $solicitud = json_decode(file_get_contents('php://input'));
        $id = isset($solicitud->codregistro) ? (int)$solicitud->codregistro : 0;
        if ($id <= 0) { return $this->respuesta_restauracion(false, 'Seleccione un movimiento válido.', 400); }
        if (empty($_SESSION['phuyu_codcaja']) || empty($_SESSION['phuyu_codcontroldiario']) || empty($_SESSION['phuyu_codsucursal'])) {
            return $this->respuesta_restauracion(false, 'Abra la caja para restaurar un movimiento.', 409);
        }
        $this->db->trans_begin();
        $consulta = $this->db->query(
            "SELECT m.*, cd.cerrado AS apertura_vigente, cd.estado AS estado_apertura
             FROM caja.movimientos m JOIN caja.controldiario cd ON cd.codcontroldiario=m.codcontroldiario
             WHERE m.codmovimiento=? AND m.codcaja=? AND cd.codsucursal=? FOR UPDATE OF m, cd",
            [$id, (int)$_SESSION['phuyu_codcaja'], (int)$_SESSION['phuyu_codsucursal']]
        );
        $movimiento = $consulta ? $consulta->row_array() : [];
        $mensaje = '';
        if (!$movimiento) { $mensaje = 'El movimiento no pertenece a la caja seleccionada.'; }
        elseif ((int)$movimiento['estado'] !== 0) { $mensaje = 'El movimiento ya está activo o no está anulado.'; }
        elseif ((int)$movimiento['codcontroldiario'] !== (int)$_SESSION['phuyu_codcontroldiario'] || (int)$movimiento['apertura_vigente'] !== 1 || (int)$movimiento['estado_apertura'] !== 1) {
            $mensaje = 'Solo puede restaurar movimientos de la apertura vigente de esta caja.';
        }
        elseif ((int)$movimiento['codkardex'] > 0) { $mensaje = 'Este movimiento pertenece a una venta o compra. Restaure el documento de origen.'; }
        elseif ((int)$movimiento['transferido'] === 1 || (int)($movimiento['codmovimiento_ref'] ?? 0) > 0) {
            $mensaje = 'La transferencia ya fue recibida. Debe revisarse junto con su movimiento de destino.';
        }
        if ($mensaje === '') {
            $relacion = $this->db->query(
                "SELECT 1 FROM kardex.creditos WHERE codmovimiento=?
                 UNION ALL SELECT 1 FROM kardex.cuotaspagos WHERE codmovimiento=?
                 UNION ALL SELECT 1 FROM caja.movimientos WHERE codmovimiento_ref=? LIMIT 1", [$id, $id, $id]
            );
            if (!$relacion || $relacion->num_rows() > 0) { $mensaje = 'El movimiento está vinculado a un crédito o transferencia. Revise la operación de origen.'; }
            $detalle = $this->db->query('SELECT 1 FROM caja.movimientosdetalle WHERE codmovimiento=? LIMIT 1', [$id]);
            if (!$detalle || $detalle->num_rows() === 0) { $mensaje = 'El movimiento no tiene detalle de pago. Revíselo antes de restaurarlo.'; }
        }
        if ($mensaje !== '') {
            $this->db->trans_rollback();
            return $this->respuesta_restauracion(false, $mensaje, 409);
        }
        $guardado = $this->db->query('UPDATE caja.movimientos SET estado=1 WHERE codmovimiento=? AND estado=0', [$id]);
        if (!$guardado || $this->db->affected_rows() !== 1 || $this->db->trans_status() === false) {
            $this->db->trans_rollback();
            return $this->respuesta_restauracion(false, 'No se pudo restaurar el movimiento.', 500);
        }
        if (!$this->db->trans_commit()) {
            return $this->respuesta_restauracion(false, 'No se pudo confirmar la restauración. Revise el estado del movimiento.', 500);
        }
        return $this->respuesta_restauracion(true, 'Movimiento restaurado. Vuelve a incluirse en los saldos y totales.');
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

    private function join_documento_movimiento($m)
    {
        // Agrupar documentos evita duplicar un movimiento que cancela varias ventas.
        return " LEFT JOIN LATERAL (
            SELECT string_agg(DISTINCT CONCAT(d.seriecomprobante, '-', d.nrocomprobante), ', ') AS comprobante,
                   string_agg(DISTINCT ct.descripcion, ', ') AS tipo,
                   string_agg(DISTINCT d.fechacomprobante::text, ', ') AS fecha
            FROM kardex.kardex d
            LEFT JOIN caja.comprobantetipos ct ON ct.codcomprobantetipo=d.codcomprobantetipo
            WHERE d.codkardex IN (
                SELECT $m.codkardex WHERE $m.codkardex > 0
                UNION SELECT cr.codkardex FROM kardex.creditos cr WHERE cr.codmovimiento=$m.codmovimiento AND cr.estado=1
                UNION SELECT cr.codkardex FROM kardex.cuotaspagos cp
                      JOIN kardex.creditos cr ON cr.codcredito=cp.codcredito
                      WHERE cp.codmovimiento=$m.codmovimiento AND cp.estado=1
            )
        ) documento ON TRUE
        LEFT JOIN caja.comprobantetipos tipo_ref ON tipo_ref.codcomprobantetipo=$m.codcomprobantetipo_ref ";
    }

    private function filtro_consulta_movimientos($filtros, $m, $p, $c, $incluir_anulados = false)
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
        $where = "$m.codcaja=".(int)$_SESSION['phuyu_codcaja']." AND ".($incluir_anulados ? "$m.estado IN (0,1)" : "$m.estado=1")." AND $m.condicionpago=1";
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
                ." OR EXISTS (SELECT 1 FROM caja.movimientos doc_busqueda ".$this->join_documento_movimiento('doc_busqueda')." WHERE doc_busqueda.codmovimiento=$m.codmovimiento AND (documento.comprobante ILIKE $texto ESCAPE '!' OR documento.tipo ILIKE $texto ESCAPE '!' OR CONCAT(doc_busqueda.seriecomprobante_ref, '-', doc_busqueda.nrocomprobante_ref) ILIKE $texto ESCAPE '!'))"
                ." OR EXISTS (SELECT 1 FROM caja.movimientosdetalle md_b JOIN caja.tipopagos tp_b ON tp_b.codtipopago=md_b.codtipopago WHERE md_b.codmovimiento=$m.codmovimiento AND tp_b.descripcion ILIKE $texto ESCAPE '!'))";
        }
        return $where;
    }

    private function identidad_reporte()
    {
        $data = [];
        $logoEmpresa = $_SESSION['phuyu_logo'] ?? '';
    $data['exportado_en'] = date('d/m/Y H:i');
    $data['ruc_empresa'] = $_SESSION['phuyu_ruc'] ?? '';
    $data['usuario_exporta'] = $_SESSION['phuyu_usuario'] ?? '';
    $data['logo_pdf'] = '';
    $directorio_logos = realpath(APPPATH.'../public/img');
    $ruta_logo = $logoEmpresa !== '' ? realpath(APPPATH.'../public/img/'.$logoEmpresa) : false;
    if ($directorio_logos && $ruta_logo && strpos($ruta_logo, $directorio_logos.DIRECTORY_SEPARATOR) === 0 && is_readable($ruta_logo)) {
        $tamano = @getimagesize($ruta_logo);
        if ($tamano && in_array($tamano[2], [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF], true)) {
            $escala = min(155 / $tamano[0], 65 / $tamano[1]);
            $data['logo_pdf'] = $ruta_logo;
            $data['logo_ancho'] = max(1, (int)round($tamano[0] * $escala));
            $data['logo_alto'] = max(1, (int)round($tamano[1] * $escala));
        }
    }

        return $data;
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
    // Los reportes consultan todas las aperturas de la caja dentro del rango.
    $filtros['vista'] = 'historial';
    $filtros['fecha_desde'] = $desde;
    $filtros['fecha_hasta'] = $hasta;
    $where = $this->filtro_consulta_movimientos($filtros, 'm', 'p', 'c');
    $join_documento = $this->join_documento_movimiento('m');
    $sql = "
        SELECT 
            m.codmovimiento,
            m.codcontroldiario,
            m.tipomovimiento,
            m.referencia,
            md.fechadocbanco,
            md.nrodocbanco,
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
            md.codtipopago,
            tp.descripcion        AS tipopago,
            md.importe            AS importe_pago,
            md.importeentregado   AS importe_entregado,
            md.vuelto             AS vuelto,
            ROUND(m.importe, 2)   AS total_movimiento,
			COALESCE(documento.comprobante, NULLIF(CONCAT(m.seriecomprobante_ref, '-', m.nrocomprobante_ref), '-'), '') AS comprobante_referencia,
            COALESCE(documento.tipo, tipo_ref.descripcion, '') AS tipo_comprobante_referencia,
            documento.fecha AS fecha_comprobante_referencia
        FROM caja.movimientosdetalle AS md
        JOIN caja.movimientos AS m       ON m.codmovimiento = md.codmovimiento
        JOIN public.personas AS p        ON p.codpersona = m.codpersona
        JOIN caja.conceptos AS c         ON c.codconcepto = m.codconcepto
        JOIN caja.tipopagos AS tp        ON tp.codtipopago = md.codtipopago
		LEFT JOIN kardex.kardex AS k        ON k.codkardex = m.codkardex
        {$join_documento}
        WHERE {$where}
        ORDER BY m.fechamovimiento, m.codmovimiento
    ";

    $data['movimientos'] = $this->db->query($sql)->result_array();
    $data['desde'] = $this->input->get('desde');
    $data['hasta'] = $this->input->get('hasta');

    $caja = $this->db->query("SELECT descripcion FROM caja.cajas WHERE codcaja=?", [(int)$codcaja])->row_array();
    $data['empresa'] = isset($_SESSION['phuyu_empresa']) ? $_SESSION['phuyu_empresa'] : 'Phuyu System';
    $data['caja_nombre'] = isset($caja['descripcion']) ? $caja['descripcion'] : 'Caja '.$codcaja;
    $data['filtros'] = $filtros;
    $data = array_merge($data, $this->identidad_reporte());
    $nivel_buffer = ob_get_level();
    try {
        $contenido = $this->load->view('reportes/excel_movimientos_detallado', $data, true);
    } catch (Throwable $error) {
        while (ob_get_level() > $nivel_buffer) { ob_end_clean(); }
        log_message('error', 'Exportacion de movimientos Excel: '.$error->getMessage());
        show_error('No se pudo generar el Excel. Intente nuevamente o contacte al administrador.', 500);
        return;
    }
    $this->output
        ->set_content_type('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
        ->set_header('Content-Disposition: attachment; filename="movimientos_caja_'.$desde.'_al_'.$hasta.'.xlsx"')
        ->set_header('Cache-Control: private, no-store, max-age=0')
        ->set_output($contenido);
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
    // Los reportes consultan todas las aperturas de la caja dentro del rango.
    $filtros['vista'] = 'historial';
    $filtros['fecha_desde'] = $desde;
    $filtros['fecha_hasta'] = $hasta;
    $where = $this->filtro_consulta_movimientos($filtros, 'm', 'p', 'c', true);
    $join_documento = $this->join_documento_movimiento('m');
    $sql = "
        SELECT 
            m.codmovimiento,
            m.tipomovimiento,
            m.estado,
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
            COALESCE(tp.descripcion, 'Sin detalle') AS tipopago,
            COALESCE(md.importe, m.importe) AS importe_pago,
            COALESCE(md.importeentregado, 0) AS importe_entregado,
            COALESCE(md.vuelto, 0) AS vuelto,
            ROUND(m.importe, 2)   AS total_movimiento,
            COALESCE(documento.comprobante, NULLIF(CONCAT(m.seriecomprobante_ref, '-', m.nrocomprobante_ref), '-'), '') AS comprobante_referencia,
            COALESCE(documento.tipo, tipo_ref.descripcion, '') AS tipo_comprobante_referencia,
            documento.fecha AS fecha_comprobante_referencia
        FROM caja.movimientos AS m
        LEFT JOIN caja.movimientosdetalle AS md ON md.codmovimiento = m.codmovimiento
        JOIN public.personas AS p        ON p.codpersona = m.codpersona
        JOIN caja.conceptos AS c         ON c.codconcepto = m.codconcepto
        LEFT JOIN caja.tipopagos AS tp        ON tp.codtipopago = md.codtipopago
        LEFT JOIN kardex.kardex AS k          ON k.codkardex = m.codkardex
        {$join_documento}
        WHERE {$where}
        ORDER BY m.fechamovimiento, m.codmovimiento
    ";

    $data['movimientos'] = $this->db->query($sql)->result_array();
    $data['desde'] = $desde;
    $data['hasta'] = $hasta;
	$data['nombreEmpresa'] = $nombreEmpresa;
	$data['logoEmpresa'] = $logoEmpresa;

    $caja = $this->db->query("SELECT descripcion FROM caja.cajas WHERE codcaja=?", [(int)$codcaja])->row_array();
    $data['caja_nombre'] = $caja['descripcion'] ?? 'Caja '.$codcaja;
    $data['buscar'] = $filtros['buscar'] ?? '';
    $data = array_merge($data, $this->identidad_reporte());

    $nivel_buffer = ob_get_level();
    try {
        $html = $this->load->view('reportes/pdf_movimientos_detallado', $data, true);
        require_once APPPATH.'third_party/phuyu_tcpdf/tcpdf.php';
        $pdf = new class('L', 'mm', 'A4', true, 'UTF-8', false) extends TCPDF {
            public $exportadoEn;
            public function Footer() {
                $this->SetDrawColor(227, 218, 237);
                $this->Line(8, $this->getPageHeight()-13, $this->getPageWidth()-8, $this->getPageHeight()-13);
                $this->SetY(-11);
                $this->SetFont('dejavusans', '', 7);
                $this->SetTextColor(88, 28, 135);
                $this->Cell(95, 4, 'Exportado desde Phuyu System', 0, 0, 'L');
                $this->SetTextColor(130, 120, 142);
                $this->Cell(95, 4, $this->exportadoEn.' - Hora de Perú', 0, 0, 'C');
                $this->Cell(0, 4, 'Página '.$this->getAliasNumPage().' de '.$this->getAliasNbPages(), 0, 0, 'R');
            }
        };
        $pdf->exportadoEn = $data['exportado_en'];
        $pdf->SetCreator('Phuyu System');
        $pdf->SetSubject('Reporte de tesorería exportado desde la plataforma Phuyu System');
        $pdf->SetAuthor($nombreEmpresa);
        $pdf->SetTitle('Movimientos de caja - '.$desde.' al '.$hasta);
        $pdf->SetMargins(8, 8, 8);
        $pdf->SetAutoPageBreak(true, 17);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->SetFont('dejavusans', '', 7);
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');
        $contenido = $pdf->Output('', 'S');
    } catch (Throwable $error) {
        while (ob_get_level() > $nivel_buffer) { ob_end_clean(); }
        log_message('error', 'Exportacion de movimientos PDF: '.$error->getMessage());
        show_error('No se pudo generar el PDF. Intente nuevamente o contacte al administrador.', 500);
        return;
    }
    $this->output
        ->set_content_type('application/pdf')
        ->set_header('Content-Disposition: inline; filename="movimientos_caja_'.$desde.'_al_'.$hasta.'.pdf"')
        ->set_header('Cache-Control: private, no-store, max-age=0')
        ->set_output($contenido);
}



public function exportar_pdf_detallado_02()
{
    return $this->exportar_pdf_detallado();
}

}
