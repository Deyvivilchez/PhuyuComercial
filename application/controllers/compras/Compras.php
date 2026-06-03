<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Compras extends CI_Controller {

	public function __construct(){
		parent::__construct(); $this->load->model("phuyu_model"); $this->load->model("Caja_model"); $this->load->model("Kardex_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_codusuario"])) {
				$comprobante_almacen = $this->db->query("select count(*) as cantidad from caja.comprobantes where (codcomprobantetipo=3 or codcomprobantetipo=4) and codalmacen=".$_SESSION["phuyu_codalmacen"]." and estado=1")->result_array();
				$almacen = $comprobante_almacen[0]["cantidad"]; $caja = $_SESSION["phuyu_codcontroldiario"];
				$this->load->view("compras/compras/index",compact("almacen","caja"));
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
				if (!empty($this->request->fechas->desde)) {
					$fechas = "kardex.fechacomprobante>='".$this->request->fechas->desde."' and kardex.fechacomprobante<='".$this->request->fechas->hasta."' and";
				}else{
					$fechas = "kardex.fechacomprobante<='".$this->request->fechas->hasta."' and";
				}
			}
			$lista = $this->db->query("select personas.documento,personas.razonsocial,personas.nombrecomercial, kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante,kardex.condicionpago, kardex.nrocomprobante,kardex.codmoneda,kardex.fechacomprobante,round(kardex.importe,2) as importe,kardex.estado,comprobantes.descripcion as tipo from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where ".$fechas." (UPPER(personas.documento) like UPPER('%".$this->request->buscar."%') or UPPER(personas.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(personas.nombrecomercial) like UPPER('%".$this->request->buscar."%') or UPPER(comprobantes.descripcion) like UPPER('%".$this->request->buscar."%') or UPPER(kardex.seriecomprobante) like UPPER('%".$this->request->buscar."%') or UPPER(kardex.nrocomprobante) like UPPER('%".$this->request->buscar."%') ) and kardex.codmovimientotipo=2 and kardex.codsucursal=".$_SESSION["phuyu_codsucursal"]." order by kardex.codkardex desc offset ".$offset." limit ".$limit)->result_array();

			$total = $this->db->query("select count(*) as total from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where ".$fechas." (UPPER(personas.documento) like UPPER('%".$this->request->buscar."%') or UPPER(personas.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(personas.nombrecomercial) like UPPER('%".$this->request->buscar."%') or UPPER(comprobantes.descripcion) like UPPER('%".$this->request->buscar."%') or UPPER(kardex.seriecomprobante) like UPPER('%".$this->request->buscar."%') or UPPER(kardex.nrocomprobante) like UPPER('%".$this->request->buscar."%') ) and kardex.codmovimientotipo=2 and kardex.codsucursal=".$_SESSION["phuyu_codsucursal"])->result_array();

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

	public function buscar(){
        if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_codusuario"])) {
				$this->load->view("compras/compras/buscar");
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	public function nuevo(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$comprobantes = $this->db->query("select * from caja.comprobantetipos where compra=1 and estado=1")->result_array();
				$conceptos = $this->db->query("select *from caja.conceptos where codconcepto=13 or codconcepto=15")->result_array();
				$tipopagos = $this->db->query("select *from caja.tipopagos where egreso=1 and estado=1")->result_array();
				$monedas = $this->db->query("select *from caja.monedas where estado=1 order by codmoneda asc")->result_array();
				$centrocostos = $this->db->query("select *from caja.centrocostos where estado=1")->result_array();
				$this->load->view("compras/compras/nuevo",compact("comprobantes","conceptos","tipopagos","monedas","centrocostos"));
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

				$info = $this->db->query("select kardex.*,p.*,(CASE WHEN condicionpago = 1 THEN 'CONTADO' ELSE 'CREDITO' END) AS pago,mt.descripcion AS movimiento,comprobantes.descripcion as tipo from kardex.kardex as kardex inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) INNER JOIN public.personas as p on (kardex.codpersona=p.codpersona) INNER JOIN almacen.movimientotipos as mt on (kardex.codmovimientotipo=mt.codmovimientotipo) where kardex.codkardex=".$codregistro)->result_array();

				$detalle = $this->db->query("select kd.*,p.descripcion as producto,u.descripcion as unidad,p.codigo from kardex.kardexdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codkardex=".$codregistro." and kd.estado=1 order by kd.item")->result_array();

				foreach ($detalle as $key => $value) {
					$producto = stripslashes($value["producto"]);
			    	$array = array("'", "=", "/", "\"", "<", ">", "|", "&", "*");
			    	$producto = str_replace($array, "", $producto);

			    	$detalle[$key]["producto"] = $producto;
				}

				$pagos = $this->db->query("select p.descripcion as tipopago, md.importe,md.importeentregado, md.vuelto,md.nrodocbanco from caja.movimientos as m inner join caja.movimientosdetalle as md on(m.codmovimiento=md.codmovimiento) inner join caja.tipopagos as p on(md.codtipopago=p.codtipopago) where m.codkardex=".$codregistro." and m.estado=1 order by p.codtipopago")->result_array();

				$otros = $this->db->query("select kardex.importe,personas.razonsocial from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) where kardex.codkardex_ref=".$codregistro." and kardex.estado=1")->result_array();

				$this->load->view("compras/compras/ver",compact("info","detalle","pagos","otros")); 
			}else{
	            $this->load->view("phuyu/505");
	        }
	    }else{
			$this->load->view("phuyu/404");
		}
	}

	function phuyu_correlativo($codcomprobantetipo,$seriecomprobante){
		if ($this->input->is_ajax_request()) {
			$comprobante = $this->db->query("select nrocorrelativo from caja.comprobantes where codcomprobantetipo=".$codcomprobantetipo." and seriecomprobante='".$seriecomprobante."' and codsucursal=".$_SESSION["phuyu_codsucursal"]." and estado=1")->result_array();
			if (count($comprobante)==0) {
				$nrocorrelativo = "00000000";
			}else{
				$nrocorrelativo = (int)($comprobante[0]["nrocorrelativo"]) + 1;
				$nrocorrelativo = str_pad($nrocorrelativo, 8, "0", STR_PAD_LEFT);
			}
			return $nrocorrelativo;
		}
	}
	
	function guardar(){
		if ($this->input->is_ajax_request()) {
			//echo "1";exit;
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$this->request = json_decode(file_get_contents('php://input'));

				if($this->request->campos->codcomprobantetipo == 13){
                     $this->request->campos->nro = $this->phuyu_correlativo($this->request->campos->codcomprobantetipo,$this->request->campos->seriecomprobanteliq);

                     $this->request->campos->seriecomprobante = $this->request->campos->seriecomprobanteliq;
				}

				$this->db->trans_begin();

				/* REGISTRO KARDEX Y KARDEXDETALLE */

				$proveedor = $this->db->query("select razonsocial,direccion,documento,d.abreviatura as tipo from public.personas p inner join public.documentotipos d on(p.coddocumentotipo=d.coddocumentotipo) where p.codpersona=".$this->request->campos->codpersona)->result_array();

				$this->request->campos->cliente = $proveedor[0]["razonsocial"];
				$this->request->campos->direccion = $proveedor[0]["direccion"];
				$this->request->campos->documento = $proveedor[0]["tipo"].'-'.$proveedor[0]["documento"];
				$this->request->campos->conleyendaamazonia = 1;

				$codkardex = $this->Kardex_model->phuyu_kardex($this->request->campos, $this->request->totales, 1); 
				$codkardexalmacen = 0; $retirar = $this->request->campos->retirar; $estado = 1;
				if ($retirar == true) {
					$codkardexalmacen = $this->Kardex_model->phuyu_kardexalmacen($codkardex, 3, $this->request->campos);
				}
				$detalle = $this->Kardex_model->phuyu_kardexdetalle($codkardex, $codkardexalmacen, $this->request->detalle, $retirar, 1, 0, 0, $this->request->campos->tipocambio);
                
                /* COMPROBANTE ELECTRONICO PARA SUNAT: REGISTRO EN KARDEX SUNAT */

				if ($this->request->campos->codcomprobantetipo==13) {
					$kardex = $this->db->query("select nrocomprobante from kardex.kardex where codkardex=".$codkardex)->result_array();
					$xml = $_SESSION["phuyu_ruc"]."-04-".$this->request->campos->seriecomprobante."-".$kardex[0]["nrocomprobante"];
					$campos = ["codkardex","codsucursal","codusuario","fechacreado","nombre_xml"];
					$valores = [
						(int)$codkardex,(int)$_SESSION["phuyu_codsucursal"],(int)$_SESSION["phuyu_codusuario"],
						$this->request->campos->fechacomprobante, $xml
					];
					$estado = $this->phuyu_model->phuyu_guardar("sunat.kardexsunat", $campos, $valores);
				}

				/* REGISTRO MOVIMIENTO DE CAJA */

				if ($this->request->campos->afectacaja==true) {
					if ($this->request->campos->codmoneda!=1) {
						$importe = round($this->request->totales->importe * $this->request->campos->tipocambio,2);
						$importemoneda = $this->request->totales->importe;
					}else{
						$importe = $this->request->totales->importe;
						$importemoneda = $this->request->totales->importe;
					}

					$codmovimiento = $this->Caja_model->phuyu_movimientos($codkardex, 2, 2, $importe, $this->request->campos,$importemoneda);

					if($codmovimiento==0){
						$data["estado"] = 0; $data["informacion"] = 'La compra se interrumpió porque la caja que usted está utilizando está cerrada, vuelve a iniciar sesión';
					    echo json_encode($data);exit;
					}

					if ($this->request->campos->condicionpago==1) {
						$campos = ["codmovimiento","codtipopago","codcontroldiario","codcaja","codmoneda","tipocambio","fechadocbanco","nrodocbanco","importe","importeentregado"];
						$valores = [
							(int)$codmovimiento,
							(int)$this->request->pagos->codtipopago,
							(int)$_SESSION["phuyu_codcontroldiario"],
							(int)$_SESSION["phuyu_codcaja"],
							(int)$this->request->campos->codmoneda,
							(double)$this->request->campos->tipocambio,
							$this->request->pagos->fechadocbanco,
							$this->request->pagos->nrodocbanco,
							(double)$importe,
							(double)$importe
						];
						$estado = $this->phuyu_model->phuyu_guardar("caja.movimientosdetalle", $campos, $valores);
					}
				}else{
					$codmovimiento = 0;
				}

				/* REGISTRO CREDITO POR COMPRA */

				if ($this->request->campos->condicionpago==2) {
					$estado = $this->Caja_model->phuyu_credito($codkardex, $codmovimiento, 2, $this->request->campos, $this->request->totales, $this->request->cuotas,$proveedor[0]["tipo"].'-'.$proveedor[0]["documento"]);
				}

				if ($this->db->trans_status() === FALSE){
				    $this->db->trans_rollback(); $estado = 0;
				}else{
					if ($estado!=1) {
						$this->db->trans_rollback(); $estado = 0;
					}
					$this->db->trans_commit();
				}
				$data["estado"] = $estado; $data["informacion"] = 'GUARDADO CORRECTAMENTE';
				echo json_encode($data);
			}else{
				echo "e";
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function guardar_gasto(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$this->request = json_decode(file_get_contents('php://input'));

				$this->db->trans_begin();

				// REGISTRO KARDEX //
				$campos = ["codkardex_ref","codsucursal","codalmacen","codusuario","codpersona","codmovimientotipo","condicionpago","fechacomprobante","fechakardex","codcomprobantetipo","seriecomprobante","nrocomprobante","valorventa","porcigv","igv","importe","descripcion"];
				$valores = [
					(int)$this->request->codkardex,
					(int)$_SESSION["phuyu_codsucursal"],
					(int)$_SESSION["phuyu_codalmacen"],
					(int)$_SESSION["phuyu_codusuario"],
					(int)$this->request->codpersona,2,1,
					$this->request->fechadocbanco,$this->request->fechadocbanco,
					(int)$this->request->codcomprobantetipo_ref,
					$this->request->seriecomprobante_ref,
					$this->request->nrocomprobante_ref,
					(double)$this->request->importe,
					(double)$_SESSION["phuyu_igv"],(double)0,
					(double)$this->request->importe,
					"COMPRA DE UN SERVICIO"
				];
				$codkardex = $this->phuyu_model->phuyu_guardar("kardex.kardex", $campos, $valores, "true");

				// REGISTRO KARDEX ALMACEN //
				$comprobante_almacen = 3;
				$series = $this->db->query("select seriecomprobante from caja.comprobantes where codcomprobantetipo=".$comprobante_almacen." and codsucursal=".$_SESSION["phuyu_codsucursal"]." and codalmacen=".$_SESSION["phuyu_codalmacen"]." and estado=1")->result_array();

				$campos = ["codsucursal","codalmacen","codusuario","codkardex","codmovimientotipo","fechakardex","codcomprobantetipo","seriecomprobante"];
				$valores = [
					(int)$_SESSION["phuyu_codsucursal"],
					(int)$_SESSION["phuyu_codalmacen"],
					(int)$_SESSION["phuyu_codusuario"],
					(int)$codkardex,2,
					$this->request->fechadocbanco,
					(int)$comprobante_almacen,
					$series[0]["seriecomprobante"]
				];
				$codkardexalmacen = $this->phuyu_model->phuyu_guardar("kardex.kardexalmacen", $campos, $valores,"true");

				$nro_kardexalmacen = $this->Kardex_model->phuyu_kardexcorrelativo($codkardex,$codkardexalmacen,$comprobante_almacen,$series[0]["seriecomprobante"]);
				
				// REGISTRO KARDEX DETALLE Y KARDEX ALMACEN DETALLE //
				$campos = ["codkardex","codproducto","codunidad","item","cantidad","preciobruto","preciosinigv","preciounitario","preciorefunitario","codafectacionigv","valorventa","subtotal"];
				$valores =[
					(int)$codkardex,(int)$this->request->codproducto,18,1,1,
					(double)$this->request->importe,
					(double)$this->request->importe,
					(double)$this->request->importe,
					(double)$this->request->importe,'20',
					(double)$this->request->importe,
					(double)$this->request->importe
				];
				$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexdetalle", $campos, $valores);

				$campos = ["codkardexalmacen","codproducto","codunidad","item","codalmacen","codsucursal","cantidad"];
				$valores =[
					(int)$codkardexalmacen,
					(int)$this->request->codproducto,
					(int)18, 1,
					(int)$_SESSION["phuyu_codalmacen"],
					(int)$_SESSION["phuyu_codsucursal"],1
				];
				$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexalmacendetalle", $campos, $valores);

				// REGISTRAMOS EL MOVIMIENTO DE CAJA //
				$campos = ["codcontroldiario","codcaja","codconcepto","codpersona","codusuario","codkardex","codcomprobantetipo","seriecomprobante","tipomovimiento","codcomprobantetipo_ref","seriecomprobante_ref","nrocomprobante_ref","importe","referencia","codcaja_ref"];
				$campos_1 = ["codmovimiento","codtipopago","codcontroldiario","codcaja","fechadocbanco","nrodocbanco","importe","importeentregado"];

				$valores = [
					(int)$_SESSION["phuyu_codcontroldiario"],
					(int)$_SESSION["phuyu_codcaja"],
					(int)$this->request->codconcepto,
					(int)$this->request->codpersona,
					(int)$_SESSION["phuyu_codusuario"],
					(int)$codkardex,
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
				$codmovimiento = $this->phuyu_model->phuyu_guardar("caja.movimientos", $campos, $valores, "true");
				$estado = $this->Caja_model->phuyu_correlativo($codmovimiento,$this->request->codcomprobantetipo,$this->request->seriecomprobante);

				$valores_1 = [(int)$codmovimiento,(int)$this->request->codtipopago,(int)$_SESSION["phuyu_codcontroldiario"],(int)$_SESSION["phuyu_codcaja"],$this->request->fechadocbanco,$this->request->nrodocbanco,(double)$this->request->importe,(double)$this->request->importe];
				$estado = $this->phuyu_model->phuyu_guardar("caja.movimientosdetalle", $campos_1, $valores_1);

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
				echo "e";
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	// EDITAR DE COMPRAS CON TODO DETALLE //
	/* function editar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$kardex = $this->db->query("select *from kardex.kardex where codkardex=".$this->request->codregistro)->result_array();
			$data["socio"] =$this->db->query("select codpersona,razonsocial from public.personas where codpersona=".$kardex[0]["codpersona"])->result_array();
			$data["campos"] = $kardex;
			$data["detalle"] = $this->db->query("select kd.codproducto,kd.codunidad,p.descripcion as producto,u.descripcion as unidad, round(kd.cantidad,3) as cantidad,round(kd.precio,3) as precio,kd.preciorefunitario,kd.codafectacionigv,round(kd.igv) as igv,round(kd.subtotal,3) as subtotal, round(kd.subtotal,3) as subtotal_tem from kardex.kardexdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codkardex=".$this->request->codregistro." and kd.estado=1 order by kd.item")->result_array();
			echo json_encode($data);
		}
	} */

	function editar(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$this->request = json_decode(file_get_contents('php://input'));
				$info = $this->db->query("select kardex.codkardex,kardex.fechacomprobante,kardex.fechakardex,kardex.codmoneda, kardex.tipocambio,kardex.codcomprobantetipo,kardex.retirar,kardex.afectacaja,kardex.seriecomprobante, kardex.nrocomprobante,kardex.nroplaca,kardex.cliente,kardex.direccion,kardex.descripcion,personas.codpersona, personas.razonsocial,comprobantes.descripcion as tipo,kardex.flete,kardex.gastos,kardex.valorventa,kardex.descglobal,kardex.igv,kardex.importe from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codkardex=".$this->request->codregistro)->result_array();

				$socio = $this->db->query("Select *from personas WHERE codpersona=".$info[0]["codpersona"])->result_array();
				$detalle = $this->db->query("select kd.*,p.descripcion as producto,u.descripcion as unidad from kardex.kardexdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codkardex=".$this->request->codregistro." and kd.estado=1 order by kd.item")->result_array();

				foreach ($detalle as $key => $value) {
					$unn = $this->db->query("SELECT pun.unidades
											  FROM almacen.v_productounidades pun
											  WHERE pun.codproducto = ".$value["codproducto"]." and pun.codalmacen=".$_SESSION["phuyu_codalmacen"]."  ")->result_array();
					
					$unidades = []; $factores = []; $logo = []; $arreglo = []; $putunidades = [];
					$unidades = explode(";", $unn[0]["unidades"]);
					foreach ($unidades as $k => $v) {
						$factores = explode("|", $unidades[$k]);
			    		$logo = array("descripcion"=>$factores[1],"codunidad"=>$factores[0],"factor"=>$factores[8]);
			    		array_push($putunidades, $logo);
			    		if($factores[8]==1){
			    			$detalle[$key]["codunidad"] = $factores[0];
			    		}
					}

					$detalle[$key]["unidades"] = $putunidades;
					$detalle[$key]["precio"] = round($detalle[$key]["preciounitario"],2);
					$detalle[$key]["cantidad"] = round($detalle[$key]["cantidad"],2);
					$detalle[$key]["control"] = 0;
				}

				echo json_encode(["campos"=>$info,"socio"=>$socio,"detalle"=>$detalle]);exit;
				$this->load->view("compras/compras/nuevo",compact("info","socio"));
			}else{
				$this->load->view("phuyu/505");
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function editarcompra(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$this->request = json_decode(file_get_contents('php://input'));
				$info = $this->db->query("select kardex.codkardex,kardex.codcomprobantetipo,kardex.fechacomprobante,kardex.fechakardex, kardex.seriecomprobante, kardex.nrocomprobante,kardex.nroplaca,kardex.cliente,kardex.direccion,kardex.descripcion,personas.codpersona, personas.razonsocial,comprobantes.descripcion as tipo from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codkardex=".$this->request->codregistro)->result_array();
				$comprobantes = $this->db->query("select * from caja.comprobantetipos where codcomprobantetipo>6 and estado=1")->result_array();
				$this->load->view("compras/compras/editar",compact("info","comprobantes"));
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

			$campos = ["codpersona","fechacomprobante","fechakardex","descripcion","codcomprobantetipo","seriecomprobante","nrocomprobante"];
			$valores = [
				$this->request->codpersona,
				$this->request->fechacomprobante,
				$this->request->fechakardex,
				$this->request->descripcion,
				$this->request->codcomprobantetipo,
				$this->request->seriecomprobante,
				$this->request->nrocomprobante
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
				$stock = $existe[0]["stockactual"] - $value["cantidad"];

				$cantidad_recogo = $value["cantidad"] - $value["recogido"];

				$campos = ["stockactual","comprarecogo"]; $valores = [(double)$stock, (double)$existe[0]["comprarecogo"] - $cantidad_recogo];
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

					$stockrecoger = ((double)$cantidad_recogo*(float)$factor[0]["factor"])/(float)$productounidad[0]["factor"];

                    $campos = ["stockactualconvertido","comprarecogoconvertido"]; $valores = [(double)$stockc,(double)$val["comprarecogoconvertido"] - (double)$stockrecoger];
					$f = ["codalmacen","codproducto","codunidad"];
					$v = [(int)$_SESSION["phuyu_codalmacen"],(int)$value["codproducto"],(int)$val["codunidad"]];
					$estado = $this->phuyu_model->phuyu_editar_1("almacen.productoubicacion", $campos, $valores, $f, $v);
				}
			}
			$estado = $this->phuyu_model->phuyu_eliminar("kardex.kardex", "codkardex", $this->request->codregistro);
			$estado = $this->phuyu_model->phuyu_eliminar("kardex.kardexalmacen", "codkardexalmacen", $kardexalmacen[0]["codkardexalmacen"]);

			// REGISTRO KARDEX ANULADOS //
			$campos = ["codkardex","codsucursal","codusuario","fechaanulacion","observaciones"];
			$valores =[
				(int)$this->request->codregistro, (int)$_SESSION["phuyu_codsucursal"], (int)$_SESSION["phuyu_codusuario"], date("Y-m-d"),$this->request->observaciones
			];
			$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexanulados", $campos, $valores);

			// REGISTRO KARDEX ALMACEN ANULADOS //
			$campos = ["codkardexalmacen","codsucursal","codusuario","fechaanulacion","observaciones"];
			$valores =[
				(int)$kardexalmacen[0]["codkardexalmacen"], (int)$_SESSION["phuyu_codsucursal"], (int)$_SESSION["phuyu_codusuario"], date("Y-m-d"), $this->request->observaciones
			];
			$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexalmacenanulado", $campos, $valores);

			// ANULAMOS EL MOVIMIENTO DE CAJA //
			$movi = $this->db->query("select codmovimiento from caja.movimientos where codkardex=".$this->request->codregistro)->result_array();
			if(count($movi)>0){
				$estado = $this->phuyu_model->phuyu_eliminar("caja.movimientos", "codmovimiento", $movi[0]["codmovimiento"]);
				$campos = ["estado"]; $valores = [0];
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

	function valorizar_precios($codkardex, $fechakardex){
		if ($this->input->is_ajax_request()) {
			$detalle = $this->db->query("select *from kardex.kardexdetalle where codkardex=".$codkardex)->result_array();
			$estado = 1;
			
			foreach ($detalle as $key => $value) {
				$stock = $this->db->query("select stockactual from almacen.productoubicacion where codproducto=".$value["codproducto"]." and codunidad=".$value["codunidad"]." and codalmacen=".$_SESSION["phuyu_codalmacen"])->result_array();
				$stockactual = $stock[0]["stockactual"] - $value["cantidad"];

				$detalle_anterior = $this->db->query("select detalle.codkardex, detalle.cantidad, tipo.tipo as tipomovimiento from kardex.kardex as kardex inner join kardex.kardexdetalle as detalle on(kardex.codkardex=detalle.codkardex) inner join almacen.movimientotipos as tipo on(kardex.codmovimientotipo=tipo.codmovimientotipo) where detalle.codproducto=".$value["codproducto"]." and detalle.codunidad=".$value["codunidad"]." and kardex.fechakardex<='".$fechakardex."' and kardex.codalmacen=".$_SESSION["phuyu_codalmacen"]." and kardex.codkardex<>".$codkardex." and kardex.estado=1 order by kardex.fechakardex desc, kardex.codkardex desc")->result_array();
				
				// TIPO MOVIMIENTO 1: INGRESO STOCK, 2: SALIDA STOCK //

				$codkardex_inicio = 0; $fechakardex_inicio = date("Y-m-d"); 
				// echo $stockactual."<br>";
				foreach ($detalle_anterior as $v) {
					if ($v["tipomovimiento"]==1) {
						$stockactual = round(($stockactual - $value["cantidad"]),3);
						// echo "resta ".$value["cantidad"]." = ".$stockactual."<br>";
					}else{
						$stockactual = round(($stockactual + $value["cantidad"]),3);
						// echo "aumenta ".$value["cantidad"]." = ".$stockactual."<br>";
					}
					if ($stockactual==0) {
						$codkardex_inicio = $v["codkardex"]; $fechakardex_inicio = $v["fechakardex"];  break;
					}
				}
				 
				$compras_anterior = $this->db->query("select coalesce(sum(detalle.cantidad),0) as cantidad, coalesce(sum((detalle.cantidad * detalle.preciounitario) + detalle.icbper),0) as total from kardex.kardex as kardex inner join kardex.kardexdetalle as detalle on(kardex.codkardex=detalle.codkardex) where detalle.codproducto=".$value["codproducto"]." and detalle.codunidad=".$value["codunidad"]." and kardex.codkardex>".$codkardex_inicio." and kardex.codmovimientotipo=2 and kardex.codalmacen=".$_SESSION["phuyu_codalmacen"]." and kardex.estado=1")->result_array();
				$suma_anterior = $compras_anterior[0]["total"];
				$suma_actual = ($value["cantidad"] * $value["preciounitario"]) + $value["icbper"];
				$cantidad_anterior = $compras_anterior[0]["cantidad"];
				$cantidad_actual = $value["cantidad"];
				$preciocosto = round( ($suma_anterior + $suma_actual)/($cantidad_anterior + $cantidad_actual) ,3);

				$campos = ["preciocompra","preciocosto"]; $valores = [$preciocosto,$preciocosto];
				$f = ["codproducto","codunidad"]; $v = [(int)$value["codproducto"],(int)$value["codunidad"]];
				$estado = $this->phuyu_model->phuyu_editar_1("almacen.productounidades", $campos, $valores, $f, $v);
			}
			echo $estado;
		}
	}
}