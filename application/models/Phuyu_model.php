<?php


class Phuyu_model extends CI_Model {

	public function __construct(){
		parent::__construct();
	}

	function phuyu_login($usuario,$clave){

		$usuario = stripslashes($usuario);
    	$array = array("'", "=", "/", "\"", "<", ">", "|", "&", "*");
    	$usuario = str_replace($array, "", $usuario );

    	$clave = stripslashes($clave);
    	$array = array("'", "=", "/", "\"", "<", ">", "|", "&", "*");
    	$clave = str_replace($array, "", $clave );

		$existe = $this->db->query("select u.*,p.descripcion as perfil
		from seguridad.usuarios u 
		inner join seguridad.perfiles p ON(u.codperfil=p.codperfil) 
		where u.usuario='".$usuario."' and u.clave='".$clave."' and u.estado=1")->result_array();

		if (count($existe)>0) {
			$empleado = $this->db->query("select *from public.personas where codpersona=".$existe[0]["codempleado"])->result_array();

			$empresa = $this->db->query("select personas.documento, personas.nombrecomercial, personas.foto, empresas.igvsunat,empresas.icbpersunat,empresas.rubro,itemrepetircomprobante from empresas as empresas inner join public.personas as personas on (empresas.codpersona=personas.codpersona) where empresas.codempresa=1")->result_array();

			$_SESSION["phuyu_codusuario"] = $existe[0]["codusuario"];
            $_SESSION["phuyu_usuario"] = $existe[0]["usuario"];
            $_SESSION["phuyu_codperfil"] = $existe[0]["codperfil"];
            $_SESSION["phuyu_perfil"] = $existe[0]["perfil"];
            $_SESSION["phuyu_codempleado"] = $existe[0]["codempleado"];

            $_SESSION["phuyu_foto"] = $empleado[0]["foto"];
            $_SESSION["phuyu_empleado"] = $empleado[0]["razonsocial"];
            $_SESSION["phuyu_codpersona"] = $empleado[0]["codpersona"];
            $_SESSION["phuyu_ruc"] = $empresa[0]["documento"];
            $_SESSION["phuyu_empresa"] = $empresa[0]["nombrecomercial"];
			$_SESSION["phuyu_igv"] = $empresa[0]["igvsunat"];
			$_SESSION["phuyu_icbper"] = $empresa[0]["icbpersunat"];
			$_SESSION["phuyu_itemrepetir"] = $empresa[0]["itemrepetircomprobante"];
			$_SESSION["phuyu_rubro"] = $empresa[0]["rubro"];

			$logo = "default.png";
			if ($empresa[0]["foto"]!="") {
				$logo = $empresa[0]["foto"];
			}
			$_SESSION["phuyu_logo"] = "empresa/".$logo;

			$estado = 1;
		}else{
			$estado = 0;
		}
		return $estado;
	}

	function phuyu_web($sucursal, $almacen, $caja){
		$info = $this->db->query("select *from public.sucursales where codsucursal=".$sucursal)->result_array();
		$_SESSION["phuyu_codempresa"] = $info[0]["codempresa"];
		$_SESSION["phuyu_codsucursal"] = $info[0]["codsucursal"];
		$_SESSION["phuyu_sucursal"] = $info[0]["descripcion"];
		$_SESSION["phuyu_tipodespacho"] = $info[0]["coddespachotipo"];
		$_SESSION["phuyu_ventaconpedido"] = $info[0]["ventaconpedido"];
		$_SESSION["phuyu_ventaconproforma"] = $info[0]["ventaconproforma"];
		$_SESSION["phuyu_creditoprogramado"] = $info[0]["creditoprogramado"];

		$info = $this->db->query("select *from almacen.almacenes where codalmacen=".$almacen)->result_array();
		$_SESSION["phuyu_codalmacen"] = $info[0]["codalmacen"];
		$_SESSION["phuyu_almacen"] = $info[0]["descripcion"];
		$_SESSION["phuyu_stockalmacen"] = $info[0]["controlstock"];
		$_SESSION["phuyu_conpedido"] = $info[0]["conpedido"];
		$_SESSION["phuyu_afectacionigv"] = $info[0]["codafectacionigv"];

        $info = $this->db->query("select *from caja.cajas where codcaja=".$caja)->result_array();
		$_SESSION["phuyu_codcaja"] = $info[0]["codcaja"];
		$_SESSION["phuyu_caja"] = $info[0]["descripcion"];

		// Verficiar el estado de la caja //
		$caja = $this->db->query("select *from caja.controldiario where codcaja=".$_SESSION["phuyu_codcaja"]." and codsucursal=".$_SESSION["phuyu_codsucursal"]." and cerrado=1 and estado=1")->result_array();
		if (count($caja)>0) {
			$_SESSION["phuyu_codcontroldiario"] = $caja[0]["codcontroldiario"];
		}else{
			$_SESSION["phuyu_codcontroldiario"] = 0;
		}

		return 1;
	}

	function phuyu_modulos(){
		$modulos = $this->db->query("select *from seguridad.modulos where codpadre=0 and estado=1 and codsistema=".$_SESSION["phuyu_codsistema"]." order by orden asc")->result_array();
        foreach ($modulos as $key => $value) {
            $modulos[$key]["submodulos"] = $this->db->query("select seguridad.modulos.* from seguridad.modulos inner join seguridad.moduloperfiles on(seguridad.modulos.codmodulo=seguridad.moduloperfiles.codmodulo) where seguridad.moduloperfiles.codperfil=".$_SESSION["phuyu_codperfil"]." and seguridad.modulos.codpadre=".$value["codmodulo"]." and seguridad.modulos.estado=1 order by seguridad.modulos.orden asc")->result_array();
        }
        return $modulos;
	}

	function phuyu_guardar($tabla, $campos, $valores, $return_id="false"){
		for($i = 0 ; $i < count($campos); $i++) {
			$data[$campos[$i]] = $valores[$i];
		}
		$estado = $this->db->insert($tabla, $data);
		
		if ($return_id=="true") {
			$estado = $this->db->insert_id();
		}
		return $estado;
	}

	public function phuyu_editar($tabla, $campos, $valores, $codregistro, $valor){
		for($i = 0 ; $i < count($campos); $i++) {
			$data[$campos[$i]] = $valores[$i];
		}
		$this->db->where($codregistro, $valor);
		$estado = $this->db->update($tabla, $data);
		return $estado;
	}

	public function phuyu_editar_1($tabla, $campos, $valores1, $filtro, $valores2){
		for($i = 0 ; $i < count($campos); $i++) {
			$data[$campos[$i]] = $valores1[$i];
		}
		for($i = 0 ; $i < count($filtro); $i++) {
			$this->db->where($filtro[$i], $valores2[$i]);
		}
		$estado = $this->db->update($tabla, $data);
		return $estado;
	}

	public function phuyu_eliminar($tabla, $codregistro, $valor){
		$data = array( "estado" => 0 );
		$this->db->where($codregistro, $valor);
		$estado = $this->db->update($tabla, $data);
		return $estado;
	}

	public function phuyu_restaurar($tabla, $codregistro, $valor){
		$data = array( "estado" => 1 );
		$this->db->where($codregistro, $valor);
		$estado = $this->db->update($tabla, $data);
		return $estado;
	}

	public function phuyu_pedidodetalle($codpedido,$detalle){
		$item = 0; $estado = 1;
		foreach ($detalle as $key => $value) { 
			$existe = $this->db->query("select *from kardex.pedidosdetalle where codpedido=".$codpedido." and codproducto=".$detalle[$key]->codproducto." and codunidad=".$detalle[$key]->codunidad)->result_array();
            
            if(count($existe) > 0){
	            $cantidad = (double)$existe[0]["cantidadcomprobante"] + (double)$detalle[$key]->cantidad;
				$data = array(
					"cantidadcomprobante" => $cantidad
				);
				$this->db->where("codpedido", $codpedido);
				$this->db->where("codproducto", $detalle[$key]->codproducto);
				$this->db->where("codunidad", $detalle[$key]->codunidad);
				$estado = $this->db->update("kardex.pedidosdetalle", $data);
			}
		}
		$estadoproceso = $this->db->query("select *FROM kardex.pedidosdetalle pd JOIN almacen.productos p ON pd.codproducto = p.codproducto JOIN almacen.unidades u ON pd.codunidad = u.codunidad where codpedido=".$codpedido." and pd.cantidad > pd.cantidadcomprobante")->result_array();
		if(count($estadoproceso) == 0){
			$data = array("estadoproceso" => 1);
			$this->db->where("codpedido", $codpedido);
            $estado = $this->db->update("kardex.pedidos", $data);
		}
		return $estado;
	}

	public function phuyu_proformadetalle($codproforma,$detalle){
		$item = 0; $estado = 1;
		foreach ($detalle as $key => $value) { 
			$existe = $this->db->query("select *from kardex.proformasdetalle where codproforma=".$codproforma." and codproducto=".$detalle[$key]->codproducto." and codunidad=".$detalle[$key]->codunidad)->result_array();
            
            if(count($existe) > 0){
	            $cantidad = (double)$existe[0]["cantidadcomprobante"] + (double)$detalle[$key]->cantidad;
				$data = array(
					"cantidadcomprobante" => $cantidad
				);
				$this->db->where("codproforma", $codproforma);
				$this->db->where("codproducto", $detalle[$key]->codproducto);
				$this->db->where("codunidad", $detalle[$key]->codunidad);
				$estado = $this->db->update("kardex.proformasdetalle", $data);
			}
		}
		$estadoproceso = $this->db->query("select *FROM kardex.proformasdetalle pd JOIN almacen.productos p ON pd.codproducto = p.codproducto JOIN almacen.unidades u ON pd.codunidad = u.codunidad where codproforma=".$codproforma." and pd.cantidad > pd.cantidadcomprobante")->result_array();
		if(count($estadoproceso) == 0){
			$data = array("estadoproceso" => 1);
			$this->db->where("codproforma", $codproforma);
            $estado = $this->db->update("kardex.proformas", $data);
		}
		return $estado;
	}

	public function phuyu_eliminar_total($tabla, $codregistro, $valor){
		$this->db->where($codregistro, $valor);
		$estado = $this->db->delete($tabla);
		return $estado;
	}

	public function actualizar_correlativo($codtabla,$columna,$tabla,$codcomprobantetipo,$seriecomprobante){
		$actual = $this->db->query("select nrocorrelativo from caja.comprobantes where codcomprobantetipo=".$codcomprobantetipo." and seriecomprobante='".$seriecomprobante."' and codsucursal=".$_SESSION["phuyu_codsucursal"]." and estado=1")->result_array();

		$nrocorrelativo = (int)($actual[0]["nrocorrelativo"]) + 1;
		$data = array(
			"nrocorrelativo" => $nrocorrelativo
		);
		$this->db->where("codsucursal", $_SESSION["phuyu_codsucursal"]);
		$this->db->where("codcomprobantetipo", $codcomprobantetipo);
		$this->db->where("seriecomprobante", $seriecomprobante);
		$estado = $this->db->update("caja.comprobantes", $data);

		$nrocorrelativo = str_pad($nrocorrelativo, 8, "0", STR_PAD_LEFT);
		$data = array(
			"nrocomprobante" => $nrocorrelativo
		);
		$this->db->where($columna, $codtabla);
		$estado = $this->db->update($tabla, $data);
		return $estado;
	}

	public function phuyu_guardardetalleguia($codguia,$detalle,$codmotivotraslado,$codkardex){
		$item = 0; $estado = 1;
		foreach ($detalle as $key => $value) { $item = $item + 1;
			$data = array(
				"codguiar" => (int)$codguia, 
				"codproducto" => (int)$detalle[$key]->codproducto,
				"item" => $item,
				"codunidad" => (int)$detalle[$key]->codunidad,
				"detalle" => "",
				"cantidad" => (double)$detalle[$key]->cantidad,
				"estado" => (int)$estado,
				"peso" => (double)$detalle[$key]->pesoitem
			);
			$estado = $this->db->insert("almacen.guiasrdetalle", $data);
            
            if(($codmotivotraslado == 1 || $codmotivotraslado == 2) && count($codkardex) > 0){
				$data = array(
					"codguiar" => (int)$codguia,
					"codkardex" => (int)$detalle[$key]->codkardex,
					"codalmacen" =>(int) $_SESSION["phuyu_codalmacen"],
					"codsucursal" => (int) $_SESSION["phuyu_codsucursal"],
					"codproducto" => (int)$detalle[$key]->codproducto,
					"codunidad" => (int)$detalle[$key]->codunidad,
					"itemgr" =>  $item,
					"itemgk" => (int)$detalle[$key]->itemkardex,
					"cantidad" => (double)$detalle[$key]->cantidad
				);
				$estado = $this->db->insert("almacen.kardexguiasrdetalle", $data);

				$existe = $this->db->query("select *from kardex.kardexdetalle where codkardex=".$detalle[$key]->codkardex." and codproducto=".$detalle[$key]->codproducto." and codunidad=".$detalle[$key]->codunidad)->result_array();
            
	            $cantidad = (double)$existe[0]["cantidadguia"] + (double)$detalle[$key]->cantidad;
				$data = array(
					"cantidadguia" => $cantidad
				);
				$this->db->where("codkardex", $detalle[$key]->codkardex);
				$this->db->where("codproducto", $detalle[$key]->codproducto);
				$this->db->where("codunidad", $detalle[$key]->codunidad);
				$estado = $this->db->update("kardex.kardexdetalle", $data);
			}
		}
		return $estado;
	}
}