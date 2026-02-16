<div id="phuyu_datos">
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<div class="row form-group">
					<div class="col-md-5">
						<h5> <b>REPORTE DE PRESTAMOS OTORGADOS Y RECIBIDOS</b> </h5> 
					</div>
					<div class="col-md-7" align="right">
						<button type="button" class="btn btn-danger btn-icon" v-on:click="pdf_reporte_prestamo()">
							<i data-acorn-icon="print"></i> Generar PDF
						</button>
						<button type="button" class="btn btn-success btn-icon" v-on:click="excel_reporte_prestamo()">
							<i data-acorn-icon="file-text"></i> Generar EXCEL
						</button>
					</div>
				</div>

				<div class="row form-group">
					<div class="col-md-2">
						<label>FECHA DESDE</label>
						<input type="date" class="form-control" id="fechai" value="<?php echo date('Y-m-01'); ?>" name="">
					</div>
					<div class="col-md-2">
						<label>FECHA HASTA</label>
						<input type="date" class="form-control" id="fechaf" value="<?php echo date('Y-m-d'); ?>" name="">
					</div>
					<div class="col-md-2">
						<label>TIPO PRESTAMO</label>
						<select class="form-select" v-model="filtro.tipo">
							<option value="1">PRESTAMOS OTORGADOS</option>
							<option value="2">PRESTAMOS RECIBIDOS</option>
						</select>
					</div>
					<div class="col-md-2">
						<label>ESTADO</label>
						<select class="form-select" v-model="filtro.estado">
							<option value="0">TODOS</option>
							<option value="1">PENDIENTES</option>
							<option value="2">DEVUELTOS</option>
						</select>
					</div>
					<div class="col-md-2">
						<label>FORMATO</label>
						<select class="form-select" v-model="filtro.formato">
							<option value="1">GENERAL</option>
							<option value="2">DETALLADO</option>
						</select>
					</div>
					<div class="col-md-2" style="margin-top: 1.3rem">
						<button type="button" class="btn btn-white btn-icon" v-on:click="phuyu_listaprestamos()"><i data-acorn-icon="search"></i> Consultar</button>
					</div>	
				</div>
				<hr>
				<div class="row form-group detalle mt-3">
					<div class="table-responsive">
						<table class="table table-bordered" style="font-size: 11px">
							<thead>
								<th colspan="2">PERSONA</th>
								<th>FECHA PRESTAMO</th>
								<th>COMPROB. REF.</th>
								<th>IMPORTE</th>
								<th colspan="2">OBSERVACION</th>
								<th>ESTADO</th>	
							</thead>
							<tbody>
								<template v-for="dato in detalleprestamo">	
									<tr>
										<td colspan="2">{{dato.cliente}}</td>
										<td>{{dato.fechakardex}}</td>
										<td>{{dato.seriecomprobante}}-{{dato.nrocomprobante}}</td>
										<td>
											{{dato.importe}}
										</td>
										<td colspan="2">{{dato.descripcion}}</td>
										<td>
											<span class="alert alert-warning" v-if="dato.cantidaddevuelta!=0">PENDIENTE</span>
											<span class="alert alert-success" v-else="dato.cantidaddevuelta==dato.cantidad">DEVUELTO</span>	
										</td>	
									</tr>
									<template v-if="filtro.formato==2">	
										<tr>
											<th>#</th>
											<th class="detalle">PRODUCTO</th>
											<th class="detalle">ID</th>
											<th class="detalle">CODIGO</th>
											<th class="detalle">UNIDAD</th>
											<th class="detalle">CANT. PRESTADA</th>
											<th class="detalle">CANT. DEVUELTA</th>		
										</tr>
										<tr v-for="(item,i) in dato.detalle"  v-bind:class="[item.cantidadxdevolver>0 ? 'faltante':'devuelto']">
											<td>{{i+1}}</td>		
											<td class="detalle">{{item.producto}}</td>
											<td class="detalle">{{item.codproducto}}</td>
											<td class="detalle">{{item.codigo}}</td>
											<td class="detalle">{{item.unidad}}</td>
											<td class="detalle">{{item.cantidad}}</td>
											<td class="detalle">{{item.cantidaddevuelta}}</td>		
										</tr>
									</template>
								</template>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script> 
	var campos = {"codalmacen":'<?php echo $_SESSION['phuyu_codalmacen'];?>',"tipo":0,"codmovimientotipo":0,"stock":0,"fecha":"<?php echo date("Y-m-d");?>","controlstock":1,"estado":1,"buscar":""};

	if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_reportes/prestamos.js"> </script>