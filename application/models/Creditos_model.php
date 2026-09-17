<?php

class Creditos_model extends CI_Model {

	public function __construct(){
		parent::__construct();
	}

	function socios_creditos_defectuoso($fecha_desde,$fecha_hasta,$tipo){
		$lista = $this->db->query("select  p.codpersona,p.razonsocial,p.documento,p.direccion,p.telefono from kardex.creditos c inner join personas p on (c.codpersona=p.codpersona) where c.fechacredito between '".$fecha_desde."' and '".$fecha_hasta."' and c.codsucursal=".$_SESSION["phuyu_codsucursal"]." and c.estado>=1 and c.tipo=".(int)$tipo." group by p.codpersona order by p.razonsocial asc")->result_array();
		return $lista;
		// c.estado>=1
	}

	function socios_creditos($fecha_desde,$fecha_hasta,$tipo){
		$lista = $this->db->query("select  p.codpersona,p.razonsocial,p.documento,p.direccion,p.telefono from kardex.creditos c inner join personas p on (c.codpersona=p.codpersona) where c.fechacredito <= '".$fecha_hasta."' and c.codsucursal=".$_SESSION["phuyu_codsucursal"]." and c.estado>=1 and c.tipo=".(int)$tipo." group by p.codpersona order by p.razonsocial asc")->result_array();
		return $lista;
		// c.estado>=1
	}

	function estado_cuenta_anterior_original($fecha_desde,$tipo,$codpersona){
		$lista = $this->db->query("select * from (select c.codcredito as movimiento, 0 as orden, c.fechacredito as fecha, 0.00 as abono, round(c.total,2) as cargo,(select COALESCE(k.seriecomprobante || '-' || k.nrocomprobante,'') from kardex.kardex as k where c.codkardex=k.codkardex) as comprobante, (select COALESCE(string_agg(p.descripcion::text || ' || CANT: ' || round(kd.cantidad,2)::text || ' || P.U: ' || round(kd.preciounitario,2)::text,',') ,c.referencia) from kardex.kardex as k inner join kardex.kardexdetalle as kd on(k.codkardex=kd.codkardex) inner join almacen.productos as p on(kd.codproducto=p.codproducto) where c.codkardex=k.codkardex) as referencia  from kardex.creditos as c where c.fechacredito < '".$fecha_desde."' and c.codsucursal=".$_SESSION["phuyu_codsucursal"]." and c.tipo=".(int)$tipo." and c.codpersona=".$codpersona." and c.estado>=1
			UNION 
			select distinct(m.codmovimiento) as movimiento,1 as orden, m.fechamovimiento as fecha, round(m.importe,2) as abono, 0.00 as cargo, m.seriecomprobante || '-' || m.nrocomprobante as comprobante,m.referencia from caja.movimientos as m inner join kardex.cuotaspagos as cp on(m.codmovimiento=cp.codmovimiento) inner join kardex.creditos as c on(c.codcredito=cp.codcredito) where m.fechamovimiento < '".$fecha_desde."' and c.tipo=".(int)$tipo." and m.codpersona=".$codpersona." and m.estado=1) as operaciones order by fecha,orden")->result_array();
		//c.estado>=1
		$saldo = 0;
		foreach ($lista as $k => $v) {
			$saldo = $saldo + $v["cargo"] - $v["abono"];
		}
		return $saldo;
	}

	function estado_cuenta_anterior($fecha_desde,$tipo,$codpersona,$estado){
		if($estado==0){
			$estadocredito = '>=1';
		}else{
			$estadocredito = '='.$estado;
		}
		$lista = $this->db->query("select sum(c.importe) as importe, 
		sum(c.interes) as interes, 
		sum(round((c.importe*c.tasainteres/100)*('".$fecha_desde."' - c.fechainicio)/30, 2)) as interesactual, 
		sum(c.saldo) as saldo, sum(c.saldomora) as saldomora, 
		sum(c.total) as total, sum(COALESCE(public.f_cobranzaxcreditohastafechaa( c.codcredito, '".$fecha_desde."'), 0.0000)) AS importepagado 
		from kardex.creditos c    
		where c.fechacredito < '".$fecha_desde."' and c.codsucursal=".$_SESSION["phuyu_codsucursal"]." and c.tipo=".(int)$tipo." and c.codpersona=".$codpersona." and c.estado".$estadocredito)->result_array();
		//c.estado>=1
		$totalsaldo = [];
		$totalimporte = 0;
		$totalinteres = 0;
		$totaltotal = 0;
		$totaltotalactual = 0;
		$totalpagado = 0;
		$totalinteresactual=0;
		$saldo = 0;
		$saldoactual = 0;
		foreach ($lista as $k => $v) {
			$totalimporte = $totalimporte + $v["importe"];
			$totalinteres = $totalinteres + $v["interes"];
			$totalinteresactual = $totalinteresactual + $v["interesactual"];
			$totaltotalactual = $totaltotalactual + ($v["importe"] + $v["interesactual"]);
			$totaltotal = $totaltotal + ($v["importe"] + $v["interes"]);
			$totalpagado = $totalpagado + $v["importepagado"];
			$saldo = $saldo + $v["importe"] + $v["interes"] - $v["importepagado"];
			$saldoactual = $saldoactual + $v["importe"] + $v["interesactual"] - $v["importepagado"];
		}
		$totalsaldo["totalimporte"] = $totalimporte;
		$totalsaldo["totalinteres"] = $totalinteres;
		$totalsaldo["totalinteresactual"] = $totalinteresactual;
		$totalsaldo["totaltotal"] = $totaltotal;
		$totalsaldo["totaltotalactual"] = $totaltotalactual;
		$totalsaldo["totalpagado"] = $totalpagado;
		$totalsaldo["saldo"] = $saldo;
		$totalsaldo["saldoactual"] = $saldoactual;
		return $totalsaldo;
	}
	
	function estado_cuenta_cliente_original($fecha_desde,$fecha_hasta,$tipo,$codpersona){
		$lista = $this->db->query("select * from (select c.codcredito as movimiento, 0 as orden, c.fechacredito as fecha, 0.00 as abono, round(c.total,2) as cargo,(select COALESCE(k.seriecomprobante || '-' || k.nrocomprobante,'') from kardex.kardex as k where c.codkardex=k.codkardex) as comprobante, (select COALESCE(string_agg(p.descripcion::text || ' || CANT: ' || round(kd.cantidad,2)::text || ' || P.U: ' || round(kd.preciounitario,2)::text,',') ,c.referencia) from kardex.kardex as k inner join kardex.kardexdetalle as kd on(k.codkardex=kd.codkardex) inner join almacen.productos as p on(kd.codproducto=p.codproducto) where c.codkardex=k.codkardex) as referencia  from kardex.creditos as c where c.fechacredito between '".$fecha_desde."' and '".$fecha_hasta."' and c.codsucursal=".$_SESSION["phuyu_codsucursal"]." and c.tipo=".(int)$tipo." and c.codpersona=".(int)$codpersona." and c.estado>=1
			UNION 
			select distinct(m.codmovimiento) as movimiento,1 as orden, m.fechamovimiento as fecha, round(m.importe,2) as abono, 0.00 as cargo, m.seriecomprobante || '-' || m.nrocomprobante as comprobante,m.referencia from caja.movimientos as m inner join kardex.cuotaspagos as cp on(m.codmovimiento=cp.codmovimiento) inner join kardex.creditos as c on(c.codcredito=cp.codcredito) where m.fechamovimiento between '".$fecha_desde."' and '".$fecha_hasta."' and c.tipo=".(int)$tipo." and m.codpersona=".$codpersona." and m.estado=1) as operaciones order by fecha,orden")->result_array();
		return $lista;
		//c.estado>=1
	}
	

	function estado_cuenta_cliente($fecha_desde,$fecha_hasta,$tipo,$codpersona,$codlote,$estado){

		$where = ''; $where2 = '';
		if($codlote!=0){
			$where.=' AND c.codlote='.$codlote;
			$where2.=' AND m.codlote='.$codlote;
		}

		if($estado==0){
			$estadocredito = 'AND c.estado>=1 ';
		}else if($estado==3){
			$estadocredito = '';
		}
		else{
			$estadocredito = 'AND c.estado='.$estado." ";
		}

		$lista = $this->db->query("select *from 
(select c.codcredito as movimiento, 
       c.codcredito, 
	   c.codlote, 
	   0 as orden, 
	   c.fechacredito as fecha, 
	   c.tasainteres AS tasainteres, 
       0.00 as abono, 
       round(c.importe,2) as cargo,  
       round(c.interes,2) as interes, 
       round((c.importe*c.tasainteres/100)*('".$fecha_hasta."' - c.fechainicio)/30, 2) AS interesactual, 
       round(c.total,2) as total,(CASE WHEN c.codkardex > 0 THEN (select COALESCE(k.seriecomprobante || '-' || k.nrocomprobante,'') from 
       kardex.kardex as k where c.codkardex=k.codkardex
       ) ELSE COALESCE(c.seriecomprobante || '-' || c.nrocomprobante,'') END )  as comprobante, 
       CASE WHEN c.codkardex > 0 THEN (select COALESCE(string_agg(p.descripcion::text || '  CANT: ' || round(kd.cantidad,2)::text || ' P.U: ' || round(kd.preciounitario,2)::text,'/') ,c.referencia) 
	   from kardex.kardex as k 
	   inner join kardex.kardexdetalle as kd on(k.codkardex=kd.codkardex) 
	   inner join almacen.productos as p on(kd.codproducto=p.codproducto) 
	   where c.codkardex=k.codkardex) ELSE TRIM(COALESCE(c.referencia::text || ' '::text, ''::text)) || 'CREDITO DIRECTO'::text END as referencia  from kardex.creditos as c where c.fechacredito between '".$fecha_desde."' and '".$fecha_hasta."' and c.codsucursal=".$_SESSION["phuyu_codsucursal"]." and c.tipo=".(int)$tipo." and c.codpersona=".(int)$codpersona." ".$estadocredito." ".$where."
					UNION 
select distinct on (m.codmovimiento, fecha, orden) m.codmovimiento as movimiento, 
       c.codcredito, 
	   m.codlote, 
	   1 as orden, 
	   m.fechamovimiento as fecha, 
	   c.tasainteres AS tasainteres, 
	   round(m.importe,2) as abono, 0.00 as cargo, 0 as interes,0 AS interesactual, round(m.importe,2) as cargototal, m.seriecomprobante || '-' || m.nrocomprobante as comprobante,m.referencia 
	   from caja.movimientos as m inner 
	   join kardex.cuotaspagos as cp on(m.codmovimiento=cp.codmovimiento) 
	   inner join kardex.creditos as c on(c.codcredito=cp.codcredito) 
	   where m.fechamovimiento between '".$fecha_desde."' and '".$fecha_hasta."' and c.tipo=".(int)$tipo." and m.codpersona=".(int)$codpersona." and m.estado=1 ".$estadocredito." ".$where2.") as operaciones order by movimiento,fecha,orden")->result_array();

		foreach ($lista as $key => $value) {
			$lineascredito = $this->db->query("select *from public.lotes where codlote=".(int)$value["codlote"])->result_array();

			if(count($lineascredito)>0){
				$lista[$key]["linea"] = $lineascredito[0]["codlote"];
			}else{
				$lista[$key]["linea"] = 'S/L';
			}
		}
		return $lista;
		//c.estado>=1
	}

	

	function estado_cuenta_creditos_original($fecha_desde,$fecha_hasta,$tipo,$codpersona){
		$lista = $this->db->query("select c.fechacredito as fecha, round(c.importe,2) as importe,round(c.interes) as interes, round(c.total,2) as total, round(c.total - c.saldo,2) as cobranza, round(c.saldo) as saldo, (select COALESCE(k.seriecomprobante || '-' || k.nrocomprobante,'') from kardex.kardex as k where c.codkardex=k.codkardex) as comprobante, (select COALESCE(string_agg(p.descripcion::text || ' || CANT: ' || round(kd.cantidad,2)::text || ' || P.U: ' || round(kd.preciounitario,2)::text,',') ,c.referencia) from kardex.kardex as k inner join kardex.kardexdetalle as kd on(k.codkardex=kd.codkardex) inner join almacen.productos as p on(kd.codproducto=p.codproducto) where c.codkardex=k.codkardex) as referencia  from kardex.creditos as c where c.fechacredito between '".$fecha_desde."' and '".$fecha_hasta."' and c.codsucursal=".$_SESSION["phuyu_codsucursal"]." and c.tipo=".(int)$tipo." and c.codpersona=".$codpersona." and c.estado>=1")->result_array();
		return $lista;
		//c.estado>=1
	}

	function estado_cuenta_creditos($fecha_desde,$fecha_hasta,$tipo,$codpersona,$codlote,$estado){
		$where = ''; $where2 = '';
		if($codlote!=0){
			$where.=' AND c.codlote='.$codlote;
			$where2.=' AND m.codlote='.$codlote;
		}

		if($estado==0){
			$estadocredito = '>=1';
		}else{
			$estadocredito = '='.$estado;
		}

		$lista = $this->db->query("select c.codcredito, c.codpersona, c.codlote,c.codmoneda, c.tipocambio, c.estado, k.codkardex, c.tipo, c.codcomprobantetipo, case when k.codkardex is null then c.seriecomprobante || '-' || c.nrocomprobante else k.seriecomprobante || '-' || k.nrocomprobante end as comprobante, c.seriecomprobante, c.nrocomprobante, c.fechacredito as fecha, c.fechainicio, c.fechavencimiento, case when k.codkardex is null then c.referencia else k.referencia end as referencia, c.importecredito as importe, c.tasainteres, c.tasainteresmora, c.interes,  (case when c.estado > 1 then round((c.importecredito*c.tasainteres/100)*('".$fecha_hasta."' - c.fechainicio)/30, 2) else c.interes end) as interesactual, c.saldocredito as saldo, round(c.total - c.saldocredito,2) as cobranza, ((case when c.estado > 1 then round((c.importecredito*c.tasainteres/100)*('".$fecha_hasta."' - c.fechainicio)/30, 2) else c.interes end) + c.importecredito - coalesce(cp.importepagado, 0.0000)) as saldocreditoactual, c.saldomora, c.total, ((case when c.estado > 1 then round((c.importecredito*c.tasainteres/100)*('".$fecha_hasta."' - c.fechainicio)/30, 2) else c.interes end) + c.importecredito) as totalactual, coalesce(cp.importepagado, 0.0000) as importepagado  from caja.v_creditos c  left join caja.v_cuotaspagosxcredito cp ON (c.codcredito=cp.codcredito)  left join kardex.v_kardexdetalle_pr k ON (c.codkardex=k.codkardex)  where c.fechacredito between ".$where." '".$fecha_desde."' and '".$fecha_hasta."' and c.codsucursal=".$_SESSION["phuyu_codsucursal"]." and c.tipo=".(int)$tipo." and c.codpersona=".$codpersona." and c.estado".$estadocredito)->result_array();

		foreach ($lista as $key => $value) {
			$lineascredito = $this->db->query("select *from public.lotes where codlote=".(int)$value["codlote"])->result_array();

			if(count($lineascredito)>0){
				$lista[$key]["linea"] = $lineascredito[0]["codlote"];
			}else{
				$lista[$key]["linea"] = 'S/L';
			}
		}

		return $lista;
		//c.estado>=1
	}

	function estado_cuenta_detallado_original($fecha_desde,$fecha_hasta,$tipo,$codpersona){
		$lista = $this->db->query("select * from (select 0 as movimiento, c.fechacredito as fecha, c.referencia, 1 as cantidad, round(c.total,2) as preciounitario, 0.00 as abono, round(c.total,2) as cargo from kardex.creditos as c where c.fechacredito between '".$fecha_desde."' and '".$fecha_hasta."' and c.codsucursal=".$_SESSION["phuyu_codsucursal"]." and c.tipo=".(int)$tipo." and c.codpersona=".$codpersona." and c.estado>=1 and (c.codkardex=0 or c.codkardex is null) 
			UNION 
			select 0 as movimiento, c.fechacredito as fecha, p.descripcion as referencia, round(kd.cantidad,2) as cantidad,round(kd.preciounitario,2) as preciounitario, 0.00 as abono,round(kd.subtotal,2) as cargo from kardex.creditos as c inner join kardex.kardex as k on(c.codkardex=k.codkardex) inner join kardex.kardexdetalle as kd on (k.codkardex=kd.codkardex) inner join almacen.productos as p on(kd.codproducto=p.codproducto) where c.fechacredito between '".$fecha_desde."' and '".$fecha_hasta."' and c.codsucursal=".$_SESSION["phuyu_codsucursal"]." and c.tipo=".(int)$tipo." and c.codpersona=".$codpersona." and c.estado>=1
			UNION 
			select distinct(m.codmovimiento) as movimiento, m.fechamovimiento as fecha, m.referencia, 1 as cantidad, round(m.importe,2) as preciounitario, round(m.importe,2) as abono, 0.00 as cargo from caja.movimientos as m inner join kardex.cuotaspagos as cp on(m.codmovimiento=cp.codmovimiento) inner join kardex.creditos as c on(c.codcredito=cp.codcredito) where m.fechamovimiento between '".$fecha_desde."' and '".$fecha_hasta."' and c.tipo=".(int)$tipo." and m.codpersona=".$codpersona." and m.estado=1) as operaciones order by fecha,movimiento")->result_array();
		return $lista;
		//c.estado>=1
	}

	function estado_cuenta_detallado($fecha_desde,$fecha_hasta,$tipo,$codpersona,$codlote,$estado){
		$where = ''; $where2 = '';
		if($codlote!=0){
			$where.=' AND codlote='.$codlote;
			$where2.=' AND codlote='.$codlote;
		}
		if($estado==0){
			$estadocredito = 'estado>=1 AND';
		}else if($estado==3){
			$estadocredito = '';
		}
		else{
			$estadocredito = 'estado='.$estado." AND ";
		}
		$lista = $this->db->query("select codkardex, codsucursal, codlote,codpersona, codusuario, estado, codcredito, codmoneda, tipocambio, codempleado, fechainicio, fechacomprobante, codcomprobantetipo, seriecomprobante, nrocomprobante, (seriecomprobante || '-' ||nrocomprobante) as comprobante, cliente, direccion, estadok, item, cantidad, preciounitario, descripcion, unidad, unidadoficial, round(cargo, 2) as cargo, round(interes, 2) as interes, round((cargo*tasainteres/100)*('".$fecha_hasta."' - fechainicio)/30, 2) AS interesactual, round(cargo + interes,2) as cargototal, round(abono, 2) as abono, tipo, tasainteres, tasainteresmora from caja.v_estadocuenta where  fechacomprobante between '". $fecha_desde."' and '".$fecha_hasta."'  and tipo=".(int)$tipo." and ".$estadocredito." codpersona = ".$codpersona." ".$where." order by fechacomprobante,orden,codcredito,codmovimiento")->result_array();

		foreach ($lista as $key => $value) {
			$lineascredito = $this->db->query("select *from public.lotes where codlote=".(int)$value["codlote"])->result_array();

			if(count($lineascredito)>0){
				$lista[$key]["linea"] = $lineascredito[0]["codlote"];
			}else{
				$lista[$key]["linea"] = 'S/L';
			}
		}
		
		return $lista;
		//estadocredito = 1
	}

	function socios_saldos($fecha_saldos,$tipo){
		$lista = $this->db->query("select p.codpersona,p.razonsocial,p.documento,p.direccion,p.telefono from kardex.creditos c inner join personas p on (c.codpersona=p.codpersona) where c.fechacredito<='".$fecha_saldos."' and c.codsucursal=".$_SESSION["phuyu_codsucursal"]." and c.estado > 0 and c.saldo > 0 and c.tipo=".(int)$tipo." group by p.codpersona order by p.razonsocial asc")->result_array();
		return $lista;
	}

	function phuyu_saldos_anterior($fecha_saldos,$tipo,$codpersona){
		$lista = $this->db->query("select c.codcredito, m.codmovimiento, c.fechacredito,c.fechavencimiento, m.seriecomprobante_ref, m.nrocomprobante_ref,c.referencia as referencia,round(c.importe,2) as importe, round(c.interes,2) as interes, round(c.total,2) as total, round(c.saldo,2) as saldo from kardex.creditos c inner join caja.movimientos m on(c.codmovimiento=m.codmovimiento) where c.fechacredito<='".$fecha_saldos."' and c.codsucursal=".$_SESSION["phuyu_codsucursal"]." and c.estado > 0 and c.saldo > 0 and c.tipo=".(int)$tipo." and c.codpersona=".$codpersona)->result_array();
		return $lista;
	}

	function phuyu_saldos($fecha_saldos,$tipo,$codpersona){
		$lista = $this->db->query("select c.codcredito, c.codmovimiento, c.fechacredito,c.fechavencimiento, c.seriecomprobante as seriecomprobante_ref, c.nrocomprobante as nrocomprobante_ref, coalesce(c.referencia, 'PRODUCTOS VARIOS') as referencia,round(c.importe,2) as importe, round(c.interes,2) as interes, round((c.importe*c.tasainteres/100)*('".$fecha_saldos."' - c.fechainicio)/30, 2) AS interesactual, round(c.total,2) as total, round(c.importe + ((c.importe*c.tasainteres/100)*('".$fecha_saldos."' - c.fechainicio)/30),2) as totalactual, coalesce(round(p.importepagado,2), 0.00) as importepagado, round(c.saldo,2) as saldo, (round(c.importe + ((c.importe*c.tasainteres/100)*('".$fecha_saldos."' - c.fechainicio)/30),2) - coalesce(round(p.importepagado,2), 0.00)) as saldoactual from kardex.creditos c left join caja.v_cuotaspagosxcredito p on(c.codcredito=p.codcredito) where c.fechacredito<='".$fecha_saldos."' and c.codsucursal=".$_SESSION["phuyu_codsucursal"]." and c.estado > 0 and c.saldo > 0 and c.tipo=".(int)$tipo." and c.codpersona=".$codpersona)->result_array();
		return $lista;
	}
}