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
				$info = $this->db->query("select lotes.*,comprobantes.descripcion as tipo from lotes.lotes as lotes inner join caja.comprobantetipos as comprobantes on(lotes.codcomprobantetipo=comprobantes.codcomprobantetipo) where lotes.codlotes=".$codregistro)->result_array();

				$detalle = $this->db->query("select kd.*,p.descripcion as producto,u.descripcion as unidad,p.codigo from lotes.lotesdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codlotes=".$codregistro." and kd.estado=1 order by kd.item")->result_array();

				$pagos = $this->db->query("select p.descripcion as tipopago, md.importe,md.importeentregado,md.vuelto,md.nrodocbanco from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) inner join caja.tipopagos as p on(md.codtipopago=p.codtipopago) where m.codlotes=".$codregistro." and m.estado=1 order by p.codtipopago")->result_array();
				$this->load->view("ventas/ventas/ver",compact("info","detalle","pagos")); 
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

				$campos = ["codsocio","codubigeo","codzona","codalmacen","descripcion","direccion","fechainicio","fechafin","tasainteres","creditomaximo","codsocioreferencia","codempleado","area","codsocioconvenio","comprado","tipoposesion","codusuario","observaciones","estado"];

				$valores = [$this->request->codsocio,$this->request->codubigeo,$this->request->codzona,$_SESSION["phuyu_codalmacen"],$this->request->cliente,$this->request->direccion,$this->request->fechainicio,$this->request->fechafin,$this->request->tasainteres,$this->request->creditomaximo,$this->request->codsocio,$this->request->codempleado,$this->request->area,$this->request->codsocio,$comprado,$this->request->tipoposesion,$_SESSION["phuyu_codusuario"],$this->request->observaciones,1];

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

				$info = $this->db->query("select lotes.fechainicio,lotes.fechafin, lotes.area, lotes.codsocioreferencia, lotes.tipoposesion,lotes.tasainteres, lotes.creditomaximo, lotes.comprado, lotes.codempleado, lotes.codzona, lotes.codubigeo, lotes.descripcion,lotes.direccion,lotes.codsocio,personas.razonsocial AS cliente from public.lotes as lotes inner join public.personas as personas on (lotes.codsocio=personas.codpersona) where lotes.codlote=".$this->request->codregistro)->result_array();

				if($info[0]["codsocio"]!=$info[0]["codsocioreferencia"]){
					$codsocioreferencia = $this->db->query("select * from public.personas where codpersona=".$info[0]["codsocioreferencia"])->result_array();

					$info[0]["garante"] = $codsocioreferencia[0]["razonsocial"];
				}else{
					$info[0]["garante"] = $info[0]["cliente"];
				}

				$ubigeo = $this->db->query("select * from public.ubigeo where codubigeo=".$info[0]["codubigeo"])->result_array();

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
			$this->request = json_decode(file_get_contents('php://input'));

			$campos = ["codpersona","fechacomprobante","fechalotes","cliente","direccion","descripcion","nroplaca"];
			$valores = [
				$this->request->codpersona,
				$this->request->fechacomprobante,
				$this->request->fechalotes,
				$this->request->cliente,
				$this->request->direccion,
				$this->request->descripcion,
				$this->request->nroplaca
			];
			$estado = $this->phuyu_model->phuyu_editar("lotes.lotes", $campos, $valores, "codlotes",$this->request->codregistro);

			$campos = ["fechalotes"]; $valores = [$this->request->fechalotes];
			$estado_u = $this->phuyu_model->phuyu_editar("lotes.lotesalmacen", $campos, $valores, "codlotes",$this->request->codregistro);

			$campos = ["codpersona","fechacredito"]; $valores = [$this->request->codpersona,$this->request->fechacomprobante];
			$estado_u = $this->phuyu_model->phuyu_editar("lotes.creditos", $campos, $valores, "codlotes",$this->request->codregistro);
			$campos = ["codpersona","fechamovimiento"]; $valores = [$this->request->codpersona,$this->request->fechacomprobante];
			$estado_u = $this->phuyu_model->phuyu_editar("caja.movimientos", $campos, $valores, "codlotes",$this->request->codregistro);

			echo $estado;
		}
	}

	function eliminar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$this->db->trans_begin();	

			// REVISAMOS SI ESTA SUJETO A UNA NOTA DE CREDITO

			$comprobante = $this->db->query("select *from lotes.lotes where codlotes_ref=".$this->request->codregistro." and estado<>0 and codmovimientotipo=8")->result_array();

			if(count($comprobante) > 0){
				$this->db->trans_rollback(); $estado = 5; echo $estado; exit();
			}		

			$comprobante = $this->db->query("select *from lotes.lotes where codlotes=".$this->request->codregistro." and estado<>0")->result_array();

			// REVISAMOS LA FECHA DE EMISION SI ES FACTURA O BOLETA

			$dteStart = new DateTime($comprobante[0]["fechacomprobante"]); 
            $dteEnd   = new DateTime(date('Y-m-d'));
            $dteDiff  = $dteStart->diff($dteEnd);
            $diferencia = $dteDiff->days;

            if($comprobante[0]["codcomprobantetipo"] == 10 || $comprobante[0]["codcomprobantetipo"] == 12){
            	if((int)$diferencia > 7){
                   $this->db->trans_rollback(); $estado = 3; echo $estado; exit();
                }
            }

			// SI EXISTE EN CREDITOS //
			$credito = $this->db->query("select *from lotes.creditos where codlotes=".$this->request->codregistro." and estado<>0")->result_array();
			if (count($credito)>0) {
				$this->db->trans_rollback(); $estado = 2; echo $estado; exit();
			}

			// ACTUALIZAMOS PRODUCTOS UBICACION //
			$lotesalmacen = $this->db->query("select codlotesalmacen from lotes.lotesalmacen where codlotes=".$this->request->codregistro)->result_array();

			$info = $this->db->query("select *from lotes.lotesdetalle where codlotes=".$this->request->codregistro)->result_array();
			foreach ($info as $key => $value) {
				$existe = $this->db->query("select *from almacen.productoubicacion where codalmacen=".$_SESSION["phuyu_codalmacen"]." and codproducto=".$value["codproducto"]." and codunidad=".$value["codunidad"])->result_array();
				$stock = $existe[0]["stockactual"] + $value["cantidad"];

				$campos = ["stockactual"]; $valores = [(double)$stock];
				$f = ["codalmacen","codproducto","codunidad"];
				$v = [(int)$_SESSION["phuyu_codalmacen"],(int)$value["codproducto"],(int)$value["codunidad"]];
				$estado = $this->phuyu_model->phuyu_editar_1("almacen.productoubicacion", $campos, $valores, $f, $v);

				// AUMENTAMOS EL STOCKACTUALCONVERTIDO

				$stockconvertido = $this->db->query("select *from almacen.productoubicacion where codalmacen=".$_SESSION["phuyu_codalmacen"]." and codproducto=".$value["codproducto"])->result_array();

				$factor = $this->db->query("select *from almacen.productounidades where codproducto=".$value["codproducto"]." and codunidad=".$value["codunidad"])->result_array();

				foreach ($stockconvertido as $k => $val) {
					$stockc = 0;
					$productounidad = $this->db->query("select *from almacen.productounidades where codproducto=".$value["codproducto"]." and codunidad=".$val["codunidad"])->result_array();

					$stockc = ((float)$value["cantidad"]*(float)$factor[0]["factor"])/(float)$productounidad[0]["factor"];
					$stockc = $stockc + $val["stockactualconvertido"];
                    $campos = ["stockactualconvertido"]; $valores = [(double)$stockc];
					$f = ["codalmacen","codproducto","codunidad"];
					$v = [(int)$_SESSION["phuyu_codalmacen"],(int)$value["codproducto"],(int)$val["codunidad"]];
					$estado = $this->phuyu_model->phuyu_editar_1("almacen.productoubicacion", $campos, $valores, $f, $v);
				}
			}
			$estado = $this->phuyu_model->phuyu_eliminar("lotes.lotes", "codlotes", $this->request->codregistro);
            if(count($lotesalmacen) > 0){
				$estado = $this->phuyu_model->phuyu_eliminar("lotes.lotesalmacen", "codlotesalmacen", $lotesalmacen[0]["codlotesalmacen"]);
            }

			// REGISTRO lotes ANULADOS //
			$campos = ["codlotes","codsucursal","codusuario","fechaanulacion","observaciones"];
			$valores =[
				(int)$this->request->codregistro, (int)$_SESSION["phuyu_codsucursal"], (int)$_SESSION["phuyu_codusuario"], date("Y-m-d"),
				$this->request->observaciones
			];
			$estado = $this->phuyu_model->phuyu_guardar("lotes.lotesanulados", $campos, $valores);
            
            if(count($lotesalmacen) > 0){
				// REGISTRO lotes ALMACEN ANULADOS //
				$campos = ["codlotesalmacen","codsucursal","codusuario","fechaanulacion","observaciones"];
				$valores =[
					(int)$lotesalmacen[0]["codlotesalmacen"], (int)$_SESSION["phuyu_codsucursal"], (int)$_SESSION["phuyu_codusuario"], date("Y-m-d"),
					$this->request->observaciones
				];
				$estado = $this->phuyu_model->phuyu_guardar("lotes.lotesalmacenanulado", $campos, $valores);
            }
			//VERIFICAMOS Y ELIMINAMOS EN LA TABLA lotesPEDIDO

			$info = $this->db->query("select *from lotes.lotespedido where codlotes=".$this->request->codregistro)->result_array();

			if(count($info) > 0){
				$codpedido = 0;
				foreach ($info as $key => $value) {

					$existepedido = $this->db->query("select *from lotes.pedidosdetalle where codpedido=".$value["codpedido"]." and codproducto=".$value["codproducto"]." and item=".$value["itempedido"])->result_array();

					$existeventa = $this->db->query("select *from lotes.lotesdetalle where codlotes=".$value["codlotes"]." and codproducto=".$value["codproducto"]." and item=".$value["itemlotes"])->result_array();

					$stock = $existepedido[0]["cantidadcomprobante"] - $existeventa[0]["cantidad"];

					$campos = ["cantidadcomprobante"]; $valores = [(double)$stock];
					$f = ["codpedido","codproducto","item"];
					$v = [(int)$value["codpedido"],(int)$value["codproducto"],(int)$value["itempedido"]];
					$estado = $this->phuyu_model->phuyu_editar_1("lotes.pedidosdetalle", $campos, $valores, $f, $v);
					$codpedido = $value["codpedido"];
				}
				$estado = $this->phuyu_model->phuyu_eliminar_total("lotes.lotespedido", "codlotes",$this->request->codregistro);

				$estadoproceso = $this->db->query("select *FROM lotes.pedidosdetalle pd where pd.codpedido=".$codpedido." and pd.cantidad > pd.cantidadcomprobante")->result_array();

				if(count($estadoproceso) > 0){
					$data = array("estadoproceso" => 0);
					$this->db->where("codpedido", $codpedido);
		            $estado = $this->db->update("lotes.pedidos", $data);
				}
			}

			// ANULAMOS EL MOVIMIENTO DE CAJA //
			$movi = $this->db->query("select codmovimiento from caja.movimientos where codlotes=".$this->request->codregistro)->result_array();
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

	function restaurar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$this->db->trans_begin();

			// ACTUALIZAMOS PRODUCTOS UBICACION //
			$lotesalmacen = $this->db->query("select codlotesalmacen from lotes.lotesalmacen where codlotes=".$this->request->codregistro)->result_array();

			$info = $this->db->query("select *from lotes.lotesdetalle where codlotes=".$this->request->codregistro)->result_array();
			foreach ($info as $key => $value) {
				$existe = $this->db->query("select *from almacen.productoubicacion where codalmacen=".$_SESSION["phuyu_codalmacen"]." and codproducto=".$value["codproducto"]." and codunidad=".$value["codunidad"])->result_array();
				$stock = $existe[0]["stockactual"] - $value["cantidad"];

				$campos = ["stockactual"]; $valores = [(double)$stock];
				$f = ["codalmacen","codproducto","codunidad"];
				$v = [(int)$_SESSION["phuyu_codalmacen"],(int)$value["codproducto"],(int)$value["codunidad"]];
				$estado = $this->phuyu_model->phuyu_editar_1("almacen.productoubicacion", $campos, $valores, $f, $v);

				// DISMINUIMOS EL STOCKACTUALCONVERTIDO

				$stockconvertido = $this->db->query("select *from almacen.productoubicacion where codalmacen=".$_SESSION["phuyu_codalmacen"]." and codproducto=".$value["codproducto"])->result_array();

				$factor = $this->db->query("select *from almacen.productounidades where codproducto=".$value["codproducto"]." and codunidad=".$value["codunidad"])->result_array();

				foreach ($stockconvertido as $k => $val) {
					$stockc = 0;
					$productounidad = $this->db->query("select *from almacen.productounidades where codproducto=".$value["codproducto"]." and codunidad=".$val["codunidad"])->result_array();

					$stockc = ((float)$value["cantidad"]*(float)$factor[0]["factor"])/(float)$productounidad[0]["factor"];
					$stockc = $val["stockactualconvertido"] - $stockc;
                    $campos = ["stockactualconvertido"]; $valores = [(double)$stockc];
					$f = ["codalmacen","codproducto","codunidad"];
					$v = [(int)$_SESSION["phuyu_codalmacen"],(int)$value["codproducto"],(int)$val["codunidad"]];
					$estado = $this->phuyu_model->phuyu_editar_1("almacen.productoubicacion", $campos, $valores, $f, $v);
				}
			}
			$estado = $this->phuyu_model->phuyu_restaurar("lotes.lotes", "codlotes", $this->request->codregistro);
			$estado = $this->phuyu_model->phuyu_restaurar("lotes.lotesalmacen", "codlotesalmacen", $lotesalmacen[0]["codlotesalmacen"]);

			$estado = $this->phuyu_model->phuyu_eliminar_total("lotes.lotesanulados","codlotes",$this->request->codregistro);
			$estado = $this->phuyu_model->phuyu_eliminar_total("lotes.lotesalmacenanulado","codlotesalmacen",$lotesalmacen[0]["codlotesalmacen"]);

			//VERIFICAMOS Y ELIMINAMOS EN LA TABLA lotesPEDIDO

			/*$info = $this->db->query("select *from lotes.lotespedido where codlotes=".$this->request->codregistro)->result_array();

			if(count($info) > 0){
				$codpedido = 0;
				foreach ($info as $key => $value) {

					$existepedido = $this->db->query("select *from lotes.pedidosdetalle where codpedido=".$value["codpedido"]." and codproducto=".$value["codproducto"]." and item=".$value["itempedido"])->result_array();

					$existeventa = $this->db->query("select *from lotes.lotesdetalle where codlotes=".$value["codlotes"]." and codproducto=".$value["codproducto"]." and item=".$value["itemlotes"])->result_array();

					$stock = $existepedido[0]["cantidadcomprobante"] + $existeventa[0]["cantidad"];

					$campos = ["cantidadcomprobante"]; $valores = [(double)$stock];
					$f = ["codpedido","codproducto","item"];
					$v = [(int)$value["codpedido"],(int)$value["codproducto"],(int)$value["itempedido"]];
					$estado = $this->phuyu_model->phuyu_editar_1("lotes.pedidosdetalle", $campos, $valores, $f, $v);
					$codpedido = $value["codpedido"];
				}
				$estado = $this->phuyu_model->phuyu_eliminar_total("lotes.lotespedido", "codlotes",$this->request->codregistro);

				$estadoproceso = $this->db->query("select *FROM lotes.pedidosdetalle pd where pd.codpedido=".$codpedido." and pd.cantidad > pd.cantidadcomprobante")->result_array();

				if(count($estadoproceso) > 0){
					$data = array("estadoproceso" => 0);
					$this->db->where("codpedido", $codpedido);
		            $estado = $this->db->update("lotes.pedidos", $data);
				}
			}*/

			// ANULAMOS EL MOVIMIENTO DE CAJA //
			$movi = $this->db->query("select codmovimiento from caja.movimientos where codlotes=".$this->request->codregistro)->result_array();
			if(count($movi) > 0){
				$estado = $this->phuyu_model->phuyu_restaurar("caja.movimientos", "codmovimiento", $movi[0]["codmovimiento"]);

				$campos = ["estado"]; $valores = [1];
				$f = ["codmovimiento"]; $v = [(int)$movi[0]["codmovimiento"]];
				$estado = $this->phuyu_model->phuyu_editar_1("caja.movimientosdetalle", $campos, $valores, $f, $v);
            }
            
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