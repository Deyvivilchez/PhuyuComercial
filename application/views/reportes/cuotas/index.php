<style type="text/css">
	.table > tbody>tr>td{
		font-size: 9px !important;
	}
</style>
<div id="phuyu_datos">
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<div class="row form-group">
					<div class="col-md-8"> <h5 style="letter-spacing:1px;"> <b>REPORTE GENERAL DE CUOTAS</b> </h5> </div>
				</div>
				<input type="hidden" id="sucursal" value="<?php echo $_SESSION["phuyu_codsucursal"];?>" name="">
				<div class="row form-group">
					<div class="col-md-4">
						<div class="w-100">
							<label>PERSONAS</label>
							<select id="codpersona">
								<option value="0">LISTA GENERAL - TODAS LAS PERSONAS</option>
							</select>
						</div>
					</div>
					<div class="col-md-2">
						<label>CREDITO</label>
						<select class="form-select" id="tipo" v-model="campos.tipo" v-on:change="phuyu_vacio()">
							<option value="1">POR COBRAR</option>
							<option value="2">POR PAGAR</option>
						</select>
					</div>
					<div class="col-md-2">
						<label>HASTA</label>
						<input type="date" class="form-control" id="fecha_hasta" value="<?php echo date('Y-m-d');?>" v-on:blur="phuyu_vacio()">
					</div>
					<div class="col-md-2">
						<label>MOSTRAR</label>
						<select class="form-select" id="mostrar" v-model="campos.mostrar" v-on:change="phuyu_vacio()">
							<option value="1" v-if="campos.tipo==1">POR CLIENTE</option>
							<option value="1" v-if="campos.tipo!=1">POR PROVEEDOR</option>
							<option value="2" v-if="campos.tipo_consulta==1">POR CREDITO</option>
						</select>
					</div>

					<div class="col-md-2">
						<label>MONEDA</label>
						<select class="form-select" name="codmoneda" v-model="campos.codmoneda" id="codmoneda">
							<option value="0">TODOS</option>
							<?php
								foreach ($monedas as $key => $value) { ?>
								<option value="<?php echo $value["codmoneda"]?>"><?php echo $value["descripcion"];?></option>
							<?php	}
							?>
		            	</select>
					</div>
				</div>
				<div class="row form-group">
					<div class="col-md-2">
						<label>ESTADO</label>
						<select class="form-select" name="estado" v-model="campos.estado" id="estado">
							<option value="0">TODOS</option>
							<option value="1">PENDIENTES</option>
							<option value="2">COBRADOS</option>	
							<option value="3">VENCIDOS</option>	
		            	</select>
					</div>		
					<div class="col-md-3">
						<label>LINEAS</label>
						<select class="form-select" name="codlote" v-model="campos.codlote" id="codlote">
							<option value="0">TODAS LAS LINEAS</option>
		            	</select>
					</div>
					<div class="col-md-4" style="margin-top: 1.2rem">
						<button type="button" class="btn btn-primary btn-icon" v-on:click="ver_cuotas()"><i data-acorn-icon="search"></i> Consultar</button>
						<button type="button" class="btn btn-danger btn-icon" v-on:click="pdf_cuotas"><i data-acorn-icon="print"></i> Pdf</button>
						<button type="button" class="btn btn-success btn-icon" v-on:click="excel_cuotas"><i data-acorn-icon="file-text"></i> Excel</button>
					</div>	
				</div>
				<div class="row" >
    				<div class="col-md-12">
						<div class="table-responsive">
							<table class="table table-bordered" style="font-size: 9px">
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