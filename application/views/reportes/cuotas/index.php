<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<style>
	#phuyu_datos .report-shell { border: 1px solid #e9ebec; border-radius: 10px; box-shadow: 0 1px 2px rgba(56,65,74,.06); }
	#phuyu_datos .report-title { font-size: 1.05rem; font-weight: 700; margin: 0; }
	#phuyu_datos .filter-band { background: #f8fafc; border: 1px solid #edf0f2; border-radius: 8px; padding: 14px; }
	#phuyu_datos label { color: #495057; font-size: .72rem; font-weight: 700; margin-bottom: 6px; text-transform: uppercase; }
	#phuyu_datos .form-control, #phuyu_datos .form-select, #phuyu_datos select { border-color: #d9dee3; border-radius: 6px; min-height: 39px; width: 100%; }
	#phuyu_datos .action-row { display: flex; flex-wrap: wrap; gap: 8px; }
	#phuyu_datos .action-row .btn { align-items: center; display: inline-flex; gap: 6px; min-height: 39px; white-space: nowrap; }
	#phuyu_datos .results-box { border: 1px solid #edf0f2; border-radius: 8px; height: calc(100vh - 390px); min-height: 330px; overflow: auto; }
	#phuyu_datos .results-box thead th { background: #f3f6f9; position: sticky; top: 0; z-index: 1; }
	#phuyu_datos .table > tbody>tr>td{ font-size: 11px !important; }
</style>
<div id="phuyu_datos" class="phuyu-reportes-velzon phuyu-velzon-list">
	<div class="phuyu_body">
		<div class="card report-shell">
			<div class="card-body">
				<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
					<h5 class="report-title">REPORTE GENERAL DE CUOTAS</h5>
				</div>
				<input type="hidden" id="sucursal" value="<?php echo $_SESSION["phuyu_codsucursal"];?>" name="">
				<div class="filter-band mb-3">
					<div class="row g-3 align-items-end">
						<div class="col-xl-4 col-lg-6">
							<label>Personas</label>
							<select id="codpersona">
								<option value="0">LISTA GENERAL - TODAS LAS PERSONAS</option>
							</select>
						</div>
						<div class="col-xl-2 col-lg-3 col-md-6">
							<label>Credito</label>
							<select class="form-select" id="tipo" v-model="campos.tipo" v-on:change="phuyu_vacio()">
								<option value="1">POR COBRAR</option>
								<option value="2">POR PAGAR</option>
							</select>
						</div>
						<div class="col-xl-2 col-lg-3 col-md-6">
							<label>Hasta</label>
							<input type="date" class="form-control" id="fecha_hasta" value="<?php echo date('Y-m-d');?>" v-on:blur="phuyu_vacio()">
						</div>
						<div class="col-xl-2 col-lg-3 col-md-6">
							<label>Mostrar</label>
							<select class="form-select" id="mostrar" v-model="campos.mostrar" v-on:change="phuyu_vacio()">
								<option value="1" v-if="campos.tipo==1">POR CLIENTE</option>
								<option value="1" v-if="campos.tipo!=1">POR PROVEEDOR</option>
								<option value="2" v-if="campos.tipo_consulta==1">POR CREDITO</option>
							</select>
						</div>
						<div class="col-xl-2 col-lg-3 col-md-6">
							<label>Moneda</label>
							<select class="form-select" name="codmoneda" v-model="campos.codmoneda" id="codmoneda">
								<option value="0">TODOS</option>
								<?php foreach ($monedas as $key => $value) { ?>
									<option value="<?php echo $value["codmoneda"]?>"><?php echo $value["descripcion"];?></option>
								<?php } ?>
			            	</select>
						</div>
						<div class="col-xl-2 col-lg-3 col-md-6">
							<label>Estado</label>
							<select class="form-select" name="estado" v-model="campos.estado" id="estado">
								<option value="0">TODOS</option>
								<option value="1">PENDIENTES</option>
								<option value="2">COBRADOS</option>
								<option value="3">VENCIDOS</option>
			            	</select>
						</div>
						<div class="col-xl-3 col-lg-4 col-md-6">
							<label>Lineas</label>
							<select class="form-select" name="codlote" v-model="campos.codlote" id="codlote">
								<option value="0">TODAS LAS LINEAS</option>
			            	</select>
						</div>
						<div class="col-xl-7 col-lg-8">
							<label>Acciones</label>
							<div class="action-row">
								<button type="button" class="btn btn-primary" v-on:click="ver_cuotas()"><i class="bi bi-search"></i> Consultar</button>
								<button type="button" class="btn btn-danger" v-on:click="pdf_cuotas"><i class="bi bi-printer"></i> PDF</button>
								<button type="button" class="btn btn-success" v-on:click="excel_cuotas"><i class="bi bi-file-earmark-excel"></i> Excel</button>
							</div>
						</div>
					</div>
				</div>
				<div class="results-box">
					<div class="h-100 d-flex align-items-center justify-content-center text-muted" v-if="datos.length==0">
						Seleccione filtros y consulte cuotas.
					</div>
					<div v-if="datos.length>0">
						<div class="table-responsive">
							<table class="table table-bordered table-hover align-middle" style="font-size: 11px">
								<thead>
									<th style="width: 7%">N° CRED.</th>
									<th>DOCUMENTO</th>
									<th style="width: 29%">RAZON SOCIAL</th>
									<th>COMPROBANTE</th>
									<th style="width: 6%">MONEDA</th>
									<th style="width: 8%">FECHA CRED.</th>
									<th style="width: 8%">FECHA VENC.</th>
									<th>DIAS VENC.</th>
									<th>N° CUOTA</th>
									<th>LETRA</th>
									<th style="width: 9%">N° PAGO UNI.</th>
									<th>IMPORTE</th>
									<th>INTERES</th>
									<th>SALDO</th>
								</thead>
								<tbody>
									<tr v-for="dato in datos">
										<td>{{dato.codcredito}}</td>
										<td>{{dato.tipoynrodocumento}}</td>
										<td>{{dato.razonsocial}}</td>
										<td>{{dato.comprobantereferencia}}</td>	
										<td style="text-align: center">{{dato.monedasimbolo}}</td>
										<td>{{dato.fechainiciocredito}}</td>
										<td>{{dato.fechavencecuota}}</td>
										<td>{{dato.diasvencidos}}</td>
										<td>{{dato.nrocuota}}</td>	
										<td>{{dato.nroletra}}</td>	
										<td>{{dato.nrounicodepago}}</td>	
										<td>{{dato.importecuota}}</td>	
										<td>{{dato.interescuota}}</td>	
										<td>{{dato.saldocuota}}</td>	
									</tr>
									<tr>
										<th colspan="11" style="text-align: right;">TOTAL</th>
										<th>{{total.totalimporte}}</th>
										<th>{{total.totalinteres}}</th>
										<th>{{total.totalsaldo}}</th>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
    			</div>
			</div>
		</div>
	</div>
</div>
<script>
	var campos = {"codpersona":0,"fecha_desde":"","fecha_hasta":"","fecha_saldos":"","tipo_consulta":1,"tipo":1,"mostrar":1,"saldos":0,"codlote":0,"estado":0,"codmoneda":0,"cliente":""};
	var pantalla = jQuery(document).height(); $("#reportes_modal").css({height: pantalla - 65});
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_reportes/cuotas.js"> </script>
<script src="<?php echo base_url();?>phuyu/phuyu_selects.js"> </script>
<script>
	if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }
</script>
