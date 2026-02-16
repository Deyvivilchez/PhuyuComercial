<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Notascredito extends CI_Controller {

	public function __construct(){
		parent::__construct(); 
		$this->load->model("phuyu_model"); $this->load->model("Caja_model"); $this->load->model("Kardex_model");
	}

	public function index(){
		if ($this->input->is_ajax_request()) {
			if (isset($_SESSION["phuyu_codusuario"])) {
				$formato = $this->db->query("select formato from caja.comprobantes where codcomprobantetipo=14 AND codsucursal = ".$_SESSION["phuyu_codsucursal"])->result_array();
				if (count($formato)==0) {
					$_SESSION["phuyu_formatonotacredito"] = "a4";
				}else{
					$_SESSION["phuyu_formatonotacredito"] = $formato[0]["formato"];
				}
				$this->load->view("ventas/notascredito/index");
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
					$fechas = "kardex.fechacomprobante>='".$this->request->fechas->desde."' and kardex.fechacomprobante<='".$this->request->fechas->hasta."' and";
				}else{
					$fechas = "kardex.fechacomprobante<='".$this->request->fechas->hasta."' and";
				}
			}

			$lista = $this->db->query("select personas.documento,kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.nrocomprobante, kardex.fechacomprobante,kardex.seriecomprobante_ref,kardex.nrocomprobante_ref,round(kardex.importe,2) as importe, kardex.estado, comprobantes.descripcion as tipo, kardex.descripcion, kardex.cliente from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where ".$fechas." (UPPER(personas.documento) like UPPER('%".$this->request->buscar."%') or UPPER(personas.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(personas.nombrecomercial) like UPPER('%".$this->request->buscar."%') or UPPER(comprobantes.descripcion) like UPPER('%".$this->request->buscar."%') or UPPER(kardex.seriecomprobante) like UPPER('%".$this->request->buscar."%') or UPPER(kardex.nrocomprobante) like UPPER('%".$this->request->buscar."%') ) and kardex.codmovimientotipo=8 and kardex.codcomprobantetipo=14 and  kardex.codsucursal=".$_SESSION["phuyu_codsucursal"]." order by kardex.codkardex desc offset ".$offset." limit ".$limit)->result_array();

			foreach ($lista as $key => $value) {
				$kardexsunat = $this->db->query("select estado from sunat.kardexsunat where codkardex = ".$value["codkardex"])->result_array();

				if(count($kardexsunat)){
					$lista[$key]["estadosunat"] = $kardexsunat[0]["estado"];
				}else{
					$lista[$key]["estadosunat"] = 2;
				}
			}

			$total = $this->db->query("select count(*) as total from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) where (UPPER(personas.documento) like UPPER('%".$this->request->buscar."%') or UPPER(personas.razonsocial) like UPPER('%".$this->request->buscar."%') or UPPER(personas.nombrecomercial) like UPPER('%".$this->request->buscar."%') or UPPER(kardex.seriecomprobante) like UPPER('%".$this->request->buscar."%') or UPPER(kardex.nrocomprobante) like UPPER('%".$this->request->buscar."%') ) and kardex.codmovimientotipo=8 and kardex.codcomprobantetipo=14 and kardex.codsucursal=".$_SESSION["phuyu_codsucursal"]."")->result_array();

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
			if (isset($_SESSION["phuyu_usuario"])) {
				$motivos = $this->db->query("select *from kardex.motivonotas where tipo=7 and estado=1 order by codmotivonota")->result_array();
				$tipocomprobantes = $this->db->query("select distinct(ct.codcomprobantetipo) as codigo, ct.* from caja.comprobantetipos as ct inner join caja.comprobantes as c on(ct.codcomprobantetipo=c.codcomprobantetipo) where (c.codcomprobantetipo=10 or c.codcomprobantetipo=12) and c.estado=1")->result_array();
				$this->load->view("ventas/notascredito/nuevo",compact("motivos","tipocomprobantes"));
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
				$info = $this->db->query("select kardex.*,personas.*,comprobantes.descripcion as tipo from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codkardex=".$codregistro)->result_array();

				$detalle = $this->db->query("select kd.*,p.descripcion as producto,u.descripcion as unidad from kardex.kardexdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codkardex=".$codregistro." and kd.estado=1 order by kd.item")->result_array();

				$this->load->view("ventas/notascredito/ver",compact("info","detalle")); 
			}else{
	            $this->load->view("inicio/505");
	        }
	    }else{
			$this->load->view("inicio/404");
		}
	}

	function comprobantes($codpersona,$codcomprobantetipo,$seriecomprobante,$fechacomprobante){
		if ($this->input->is_ajax_request()) {
			$lista = $this->db->query("select codkardex,codcomprobantetipo,seriecomprobante,nrocomprobante,fechacomprobante,round(kardex.importe,2) as importe,kardex.estado,kardex.cliente,kardex.direccion,kardex.procesoestadonota from kardex.kardex where fechacomprobante='".$fechacomprobante."' and codpersona=".$codpersona." and codmovimientotipo=20 and codsucursal=".$_SESSION["phuyu_codsucursal"]." and codcomprobantetipo=".$codcomprobantetipo." and seriecomprobante='".$seriecomprobante."' and estado=1 order by codkardex")->result_array();
			foreach ($lista as $key => $value) {
				$motivo = $this->db->query("select count(*) as cantidad from kardex.kardex as k inner join kardex.motivonotas as mn on(k.codmotivonota=mn.codmotivonota) where k.codkardex_ref=".$value["codkardex"]." AND k.estado=1")->result_array();

				if (count($motivo)==0) {
					$lista[$key]["cantidadnota"] = 0;
				}else{
					$lista[$key]["cantidadnota"] = $motivo[0]["cantidad"];
				}
			}

			$series = $this->db->query("select seriecomprobante from caja.comprobantes where codcomprobantetipo=14 and codcomprobantetipo_ref=".$codcomprobantetipo." and seriecomprobante_ref='".$seriecomprobante."' and codsucursal=".$_SESSION["phuyu_codsucursal"]." and estado=1")->result_array();

			$data = array(); $data["comprobantes"] = $lista; $data["series"] = $series;
			echo json_encode($data);
		}
	}

	function detalle($codregistro){
		if ($this->input->is_ajax_request()) {
			$detalle = $this->db->query("select kd.codproducto,kd.codunidad,round((kd.cantidad-kd.cantidadnota),2) as cantidad,round((kd.cantidad-kd.cantidadnota),2) as stock,round(kd.preciounitario,2) as precio,kd.cantidad as cantidadoriginal,kd.item ,kd.preciosinigv,kd.preciorefunitario,kd.valorventa,round(kd.igv,2) as igv, round(kd.subtotal,2) as subtotal,kd.codafectacionigv,p.descripcion as producto,u.descripcion as unidad, kd.recoger,kd.recogido,kd.descripcion from kardex.kardexdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codkardex=".$codregistro." and kd.estado=1 and kd.cantidad > kd.cantidadnota order by kd.item")->result_array();

			$totales = $this->db->query("select codkardex,round(valorventa,2) as valorventa,round(igv,2) as igv,round(importe,2) as importe from kardex.kardex where codkardex=".$codregistro)->result_array();

			$data["detalle"] = $detalle;
			$data["totales"] = $totales;
			echo json_encode($data);
		}
	}

	function guardar(){
		if ($this->input->is_ajax_request()) {
			if (isset( $_SESSION["phuyu_codusuario"]) ) {
				$this->request = json_decode(file_get_contents('php://input'));

				$this->db->trans_begin();
//echo "1";exit;


				// REGISTRO KARDEX //
				$comprobante_nota = 14;
				$campos = ["codsucursal","codalmacen","codkardex_ref","codpersona","codusuario","codmotivonota","codmovimientotipo","fechakardex","fechacomprobante","codcomprobantetipo","seriecomprobante","codcomprobantetipo_ref","seriecomprobante_ref","nrocomprobante_ref","valorventa","porcigv","igv","importe","descripcion","cliente","direccion"];
				$valores = [
					(int)$_SESSION["phuyu_codsucursal"],
					(int)$_SESSION["phuyu_codalmacen"],
					(int)$this->request->campos->codkardex_ref,
					(int)$this->request->campos->codpersona,
					(int)$_SESSION["phuyu_codusuario"],
					(int)$this->request->campos->codmotivonota,
					(int)$this->request->campos->codmovimientotipo,date("Y-m-d"),date("Y-m-d"),
					(int)$comprobante_nota,$this->request->campos->seriecomprobante,
					(int)$this->request->campos->codcomprobantetipo_ref,
					$this->request->campos->seriecomprobante_ref,
					$this->request->campos->nrocomprobante_ref,
					(double)$this->request->totales->valorventa,
					(double)$_SESSION["phuyu_igv"],
					(double)$this->request->totales->igv,
					(double)$this->request->totales->importe,
					$this->request->campos->descripcion,
					$this->request->campos->cliente,
					$this->request->campos->direccion
				];
				$codkardex = $this->phuyu_model->phuyu_guardar("kardex.kardex", $campos, $valores, "true");

				$estado = $this->phuyu_model->actualizar_correlativo($codkardex,'codkardex','kardex.kardex',$comprobante_nota,$this->request->campos->seriecomprobante);

				// REGISTRO KARDEX ALMACEN //
				$comprobante_almacen = 3;
				$series = $this->db->query("select seriecomprobante from caja.comprobantes where codcomprobantetipo=".$comprobante_almacen." and codsucursal=".$_SESSION["phuyu_codsucursal"]." and codalmacen=".$_SESSION["phuyu_codalmacen"]." and estado=1")->result_array();

				$campos = ["codsucursal","codalmacen","codkardex","codusuario","codmovimientotipo","fechakardex","codcomprobantetipo","seriecomprobante"];
				$valores = [
					(int)$_SESSION["phuyu_codsucursal"],
					(int)$_SESSION["phuyu_codalmacen"],
					(int)$codkardex,
					(int)$_SESSION["phuyu_codusuario"],
					(int)$this->request->campos->codmovimientotipo,date("Y-m-d"),
					(int)$comprobante_almacen, $series[0]["seriecomprobante"]
				];
				$codkardexalmacen = $this->phuyu_model->phuyu_guardar("kardex.kardexalmacen", $campos, $valores, "true");
				$nro_kardexalmacen = $this->Kardex_model->phuyu_kardexalmacencorrelativo($codkardexalmacen,$comprobante_almacen,$series[0]["seriecomprobante"]);

				// REGISTRO KARDEX DETALLE Y KARDEX ALMACEN DETALLE //
				$item = 0;
				foreach ($this->request->detalle as $key => $value) { $item = $item + 1;
					$campos = ["codkardex","codproducto","codunidad","item","cantidad","preciobruto","preciosinigv","preciounitario","preciorefunitario","codafectacionigv","igv","valorventa","subtotal","itemorigennota"];
					$valores =[
						(int)$codkardex,
						(int)$this->request->detalle[$key]->codproducto,
						(int)$this->request->detalle[$key]->codunidad, $item,
						(double)$this->request->detalle[$key]->cantidad,
						(double)$this->request->detalle[$key]->precio,
						(double)$this->request->detalle[$key]->preciosinigv,
						(double)$this->request->detalle[$key]->precio,
						(double)$this->request->detalle[$key]->preciorefunitario,
						$this->request->detalle[$key]->codafectacionigv,
						(double)$this->request->detalle[$key]->igv,
						(double)$this->request->detalle[$key]->valorventa,
						(double)$this->request->detalle[$key]->subtotal,
						(int)$this->request->detalle[$key]->itemorigen
					];
					$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexdetalle", $campos, $valores);

					$campos =["codkardexalmacen","codproducto","codunidad","item","codalmacen","codsucursal","cantidad"];
					$valores =[
						(int)$codkardexalmacen,
						(int)$this->request->detalle[$key]->codproducto,
						(int)$this->request->detalle[$key]->codunidad, $item,
						(int)$_SESSION["phuyu_codalmacen"],
						(int)$_SESSION["phuyu_codsucursal"],
						(double)$this->request->detalle[$key]->cantidad
					];
					$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexalmacendetalle", $campos, $valores);

					$existe_ubi = $this->db->query("select *from almacen.productoubicacion where codalmacen=".$_SESSION["phuyu_codalmacen"]." and codproducto=".$this->request->detalle[$key]->codproducto." and codunidad=".$this->request->detalle[$key]->codunidad)->result_array();
					
					if (count($existe_ubi)>0) {
						$stock = $existe_ubi[0]["stockactual"] + (double)$this->request->detalle[$key]->cantidad;

						$campos = ["stockactual"]; $valores = [(double)$stock];
						$f = ["codalmacen","codproducto","codunidad"]; 
						$v = [(int)$_SESSION["phuyu_codalmacen"],(int)$this->request->detalle[$key]->codproducto,(int)$this->request->detalle[$key]->codunidad];
						$estado = $this->phuyu_model->phuyu_editar_1("almacen.productoubicacion", $campos, $valores, $f, $v);
					}

					$stockconvertido = $this->db->query("select *from almacen.productoubicacion where codalmacen=".$_SESSION["phuyu_codalmacen"]." and codproducto=".$this->request->detalle[$key]->codproducto)->result_array();

					$factor = $this->db->query("select *from almacen.productounidades where codproducto=".$this->request->detalle[$key]->codproducto." and codunidad=".$this->request->detalle[$key]->codunidad)->result_array();

					foreach ($stockconvertido as $k => $value) {
						$productounidad = $this->db->query("select *from almacen.productounidades where codproducto=".$this->request->detalle[$key]->codproducto." and codunidad=".$value["codunidad"])->result_array();

						$stockc = ((float)$this->request->detalle[$key]->cantidad*(float)$factor[0]["factor"])/(float)$productounidad[0]["factor"];

						$data = array(
							"stockactualconvertido" => (double)round(($value["stockactualconvertido"] + $stockc),3)
						);

						$this->db->where("codalmacen", $_SESSION["phuyu_codalmacen"]);
						$this->db->where("codproducto", $this->request->detalle[$key]->codproducto);
						$this->db->where("codunidad", $value["codunidad"]);
						$estado = $this->db->update("almacen.productoubicacion", $data);
					}

					//ACTUALIZAMOS LA CANTIDAD NOTA EN EL KARDEXDETALLE ORIGEN

					$cantidadnota = $this->db->query("select *from kardex.kardexdetalle where codkardex=".$this->request->campos->codkardex_ref." AND codproducto=".(int)$this->request->detalle[$key]->codproducto." AND item=".(int)$this->request->detalle[$key]->itemorigen)->result_array();

					$factororigen = $this->db->query("select *from almacen.productounidades where codproducto=".$this->request->detalle[$key]->codproducto." and codunidad=".$cantidadnota[0]["codunidad"])->result_array();

					$cantidadnotaconvertida = (float)$this->request->detalle[$key]->cantidad*(float)$factor[0]["factor"]/$factororigen[0]["factor"];

					$campos = ["cantidadnota"]; $valores = [(double)$cantidadnota[0]["cantidadnota"]+(double)$cantidadnotaconvertida];
					$f = ["codkardex","codproducto","item"]; 
					$v = [(int)$this->request->campos->codkardex_ref,(int)$this->request->detalle[$key]->codproducto,(int)$this->request->detalle[$key]->itemorigen];
					$estado = $this->phuyu_model->phuyu_editar_1("kardex.kardexdetalle", $campos, $valores, $f, $v);
				}

				$detalle = $this->db->query("select (sum(cantidad) - sum(cantidadnota)) as cantidad  from kardex.kardexdetalle where codkardex=".$this->request->campos->codkardex_ref." and estado=1")->result_array();
				if ($detalle[0]["cantidad"]==0) {
					$data = array("procesoestadonota" => 1);
					$this->db->where("codkardex", $this->request->campos->codkardex_ref);
					$estado = $this->db->update("kardex.kardex",$data);
				}

				$kardex = $this->db->query("select nrocomprobante from kardex.kardex where codkardex=".$codkardex)->result_array();
				
				$xml = $_SESSION["phuyu_ruc"]."-07-".$this->request->campos->seriecomprobante."-".$kardex[0]["nrocomprobante"];
				
				$campos = ["codkardex","codsucursal","codusuario","fechacreado","nombre_xml"];
				$valores = [
					(int)$codkardex,(int)$_SESSION["phuyu_codsucursal"],(int)$_SESSION["phuyu_codusuario"],
					date("Y-m-d"), $xml
				];
				$estado = $this->phuyu_model->phuyu_guardar("sunat.kardexsunat", $campos, $valores);

				if ($this->db->trans_status() === FALSE){
				    $this->db->trans_rollback(); $estado = 0;
				}else{
					if ($estado!=1) {
						$this->db->trans_rollback(); $estado = 0;
					}
					$this->db->trans_commit();
				}
				$data["estado"] = $estado; $data["codkardex"] = $codkardex;
				echo json_encode($data);
			}else{
				echo "e";
			}
		}else{
			$this->load->view("phuyu/404");
		}
	}

	function formato($formato){
		if ($this->input->is_ajax_request()) {
			$campos = ["formato"]; $valores = [$formato];
			$f = ["codsucursal","codcomprobantetipo"]; $v = [$_SESSION["phuyu_codsucursal"],14];
			$estado = $this->phuyu_model->phuyu_editar_1("caja.comprobantes", $campos, $valores, $f, $v);

			echo $formato;
		}
	}

	function eliminar(){
		if ($this->input->is_ajax_request()) {
			$this->request = json_decode(file_get_contents('php://input'));
			$this->db->trans_begin();

			//VERIFICAMOS SI LA NOTA DE CREDITO FUE ENVIADA A SUNAT

			$enviado = $this->db->query("Select *from sunat.kardexsunat where codkardex=".$this->request->codregistro." AND estado = 1")->result_array();

			// ACTUALIZAMOS PRODUCTOS UBICACION //
			$kardexalmacen = $this->db->query("select codkardexalmacen from kardex.kardexalmacen where codkardex=".$this->request->codregistro." AND estado = 1")->result_array();

			$kardex = $this->db->query("select *from kardex.kardex where codkardex=".$this->request->codregistro)->result_array();

			$info = $this->db->query("select *from kardex.kardexdetalle where codkardex=".$this->request->codregistro)->result_array();
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

				$cantidadnota = $this->db->query("select *from kardex.kardexdetalle where codkardex=".$kardex[0]["codkardex_ref"]." AND codproducto=".$value["codproducto"]." AND item=".$value["itemorigennota"])->result_array();

				$factororigen = $this->db->query("select *from almacen.productounidades where codproducto=".$value["codproducto"]." and codunidad=".$cantidadnota[0]["codunidad"])->result_array();

				$cantidadnotaconvertida = (float)$value["cantidad"]*(float)$factor[0]["factor"]/$factororigen[0]["factor"];

				$campos = ["cantidadnota"]; $valores = [(double)$cantidadnota[0]["cantidadnota"]-(double)$cantidadnotaconvertida];
				$f = ["codkardex","codproducto","item"]; 
				$v = [(int)$kardex[0]["codkardex_ref"],$value["codproducto"],$value["itemorigennota"]];
				$estado = $this->phuyu_model->phuyu_editar_1("kardex.kardexdetalle", $campos, $valores, $f, $v);
			}
			$estado = $this->phuyu_model->phuyu_eliminar("kardex.kardex", "codkardex", $this->request->codregistro);

			$detalle = $this->db->query("select (sum(cantidad) - sum(cantidadnota)) as cantidad  from kardex.kardexdetalle where codkardex=".$kardex[0]["codkardex_ref"]." and estado=1")->result_array();
			if ($detalle[0]["cantidad"]>0) {
				$data = array("procesoestadonota" => 0);
				$this->db->where("codkardex", $kardex[0]["codkardex_ref"]);
				$estado = $this->db->update("kardex.kardex",$data);
			}

			if(count($enviado)==0){
				// REGISTRO KARDEX ANULADOS //
				$campos = ["codkardex","codsucursal","codusuario","fechaanulacion","observaciones"];
				$valores =[
					(int)$this->request->codregistro, (int)$_SESSION["phuyu_codsucursal"], (int)$_SESSION["phuyu_codusuario"], date("Y-m-d"),$this->request->observaciones
				];
				$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexanulados", $campos, $valores);
			}

			if(count($kardexalmacen)){
				$estado = $this->phuyu_model->phuyu_eliminar("kardex.kardexalmacen", "codkardexalmacen", $kardexalmacen[0]["codkardexalmacen"]);
				// REGISTRO KARDEX ALMACEN ANULADOS //
				$campos = ["codkardexalmacen","codsucursal","codusuario","fechaanulacion","observaciones"];
				$valores =[
					(int)$kardexalmacen[0]["codkardexalmacen"], (int)$_SESSION["phuyu_codsucursal"], (int)$_SESSION["phuyu_codusuario"], date("Y-m-d"), $this->request->observaciones
				];
				$estado = $this->phuyu_model->phuyu_guardar("kardex.kardexalmacenanulado", $campos, $valores);
			}

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
			echo json_encode(["estado"=>$estado]);
		}else{
			$this->load->view("phuyu/404");
		}
	}
}