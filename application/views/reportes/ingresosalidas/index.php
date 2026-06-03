<div id="phuyu_datos">
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<div class="row form-group">
					<div class="col-md-5">
						<h5> <b>REPORTE DE INGRESO Y SALIDAS DE ALMACEN</b> </h5> 
					</div>
					<div class="col-md-7" align="right">
						<button type="button" class="btn btn-danger btn-icon" v-on:click="generar_pdf()">
							<i data-acorn-icon="print"></i> Generar PDF
						</button>
						<button type="button" class="btn btn-success btn-icon" v-on:click="generar_excel()">
							<i data-acorn-icon="file-text"></i> Generar EXCEL
						</button>
						<button type="button" class="btn btn-warning btn-icon" v-on:click="phuyu_modalprestamos()"><i data-acorn-icon="search"></i> CONSULTAR PRESTAMOS</button>
					</div>
				</div>

				<div class="row form-group">
					<div class="col-md-2">
						<label>ALMACENES</label>
						<select class="form-select input-sm" v-model="campos.codalmacen">
							<option value="0">TODOS ALMACENES</option>
							<?php 
								foreach ($almacenes as $key => $value) { ?>
									<option value="<?php echo $value["codalmacen"];?>"><?php echo $value["descripcion"];?></option>	
								<?php }
							?>
						</select>
					</div>
					<div class="col-md-2">
						<label>TIPO MOVIMIENTO</label>
						<select class="form-select" id="tipo" v-model="campos.tipo" v-on:change="phuyu_movimientos()" required>
							<option value="0">TODOS</option>
							<option value="1">INGRESOS</option>
							<option value="2">SALIDAS</option>
						</select>
					</div>
					<div class="col-md-2">
						<label>MOVIMIENTO</label>
						<select class="form-select" id="codmovimientotipo" v-model="campos.codmovimientotipo">
							<option value="0">TODOS</option>
						</select>
					</div>
					<div class="col-md-2">
						<label>DESDE</label>
						<input type="date" class="form-control" id="fechadesde" value="<?php echo date('Y-m-01');?>" autocomplete="off">
					</div>
					<div class="col-md-2">
						<label>HASTA</label>
						<input type="date" class="form-control" id="fechahasta" value="<?php echo date('Y-m-d');?>" autocomplete="off">
					</div>
					<div class="col-md-2" style="margin-top: 1.3rem">
						<button type="button" class="btn btn-white btn-icon" v-on:click="generar_reporte()">
							<i data-acorn-icon="search"></i> Consultar
						</button>
					</div>	
				</div>
				<hr>
				<div class="row form-group detalle mt-3">
					<div v-for="dato in datos">
						<table class="table table-striped" style="font-size: 11px">
							<tr>
								<th colspan="9">{{dato.descripcion}} | DIRECCION: {{dato.direccion}}</th>
							</tr>
							<tr>
								<th> <center> ID </center> </th>
								<th>FECHA</th>
								<th>TIPO</th>
								<th>MOVIMIENTO</th>
								<th>DOCUMENTO</th>
								<th>CLIENTE</th>
								<th>SUBTOTAL</th>
								<th>IGV</th>
								<th>TOTAL</th>
							</tr>
							<tr v-for="d in dato.lista">
								<td>{{d.codkardex}}</td>
								<td>{{d.fechacomprobante}}</td>
								<td>
									<span class="label label-primary" v-if="d.tipomov==1">INGRESO</span>
									<span class="label label-danger" v-if="d.tipomov==2">SALIDA</span>
								</td>
								<td>{{d.motivo}}</td>
								<td>{{d.seriecomprobante}}-{{d.nrocomprobante}}</td>
								<td>{{d.razonsocial}}</td>
								<td>{{d.valorventa}}</td>
								<td>{{d.igv}}</td>
								<td>{{d.importe}}</td>
							</tr>
							<tr>
								<th colspan="6" style="text-align: right !important;">TOTAL</th>
								<th>{{dato.valortotal}}</th>
								<th>{{dato.igv}}</th>
								<th>{{dato.importe}}</th>
							</tr>
						</table>
					</div>
				</div>
				<div id="modal_prestamos" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
					<div class="modal-dialog modal-lg">
						<div class="modal-content">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" style="font-size:30px;margin-bottom:0px;">
									<i class="fa fa-times-circle"></i> 
								</button>
								<h4 class="modal-title">REPORTE DE PRESTAMOS RECIBIDOS Y OTORGADOS</h4>
							</div>
							<div class="modal-body" id="modal_prestamos_contenido">
								<div class="row">
									<div class="col-md-2">
										<label>FECHA DESDE</label>
										<input type="date" class="form-control" id="fechai" value="<?php echo date('Y-m-01'); ?>" name="">
									</div>
									<div class="col-md-2">
										<label>FECHA HASTA</label>
										<input type="date" class="form-control" id="fechaf" value="<?php echo date('Y-m-d'); ?>" name="">
									</div>
									<div class="col-md-3">
										<label>TIPO PRESTAMO</label>
										<select class="form-control" v-model="filtro.tipo">
											<option value="1">PRESTAMOS OTORGADOS</option>
											<option value="2">PRESTAMOS RECIBIDOS</option>
										</select>
									</div>
									<div class="col-md-3">
										<label>ESTADO</label>
										<select class="form-control" v-model="filtro.estado">
											<option value="1">PENDIENTES</option>
											<option value="2">DEVUELTOS</option>
										</select>
									</div>
									<div class="col-md-2">
										<label>FORMATO</label>
										<select class="form-control" v-model="filtro.formato">
											<option value="1">GENERAL</option>
											<option value="2">DETALLADO</option>
										</select>
									</div>
								</div>
								<div class="row">
									<div class="col-md-4"></div>
									<div class="col-md-8">
										<button type="button" style="margin-top: 2rem" class="btn btn-primary" v-on:click="phuyu_listaprestamos()"><i class="fa fa-search"></i> CONSULTAR</button>
										<button type="button" style="margin-top: 2rem" class="btn btn-danger" v-on:click="pdf_reporte_prestamo()">PDF</button>
										<button type="button" style="margin-top: 2rem" class="btn btn-success" v-on:click="excel_reporte_prestamo()">EXCEL</button>
									</div>
								</div><br>
								<div class="row">
									<div class="col-md-12">
										<div class="table-responsive">
											<table class="table table-bordered">
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
															<td colspan="2">{{dato.persona}}</td>
															<td>{{dato.fechakardex}}</td>
															<td>{{dato.seriecomprobante_ref}}-{{dato.nrocomprobante_ref}}</td>
															<td>
																{{dato.importe}}
															</td>
															<td colspan="2">{{dato.descripcion}}</td>
															<td>
																<span class="label label-success" v-if="dato.procesoprestamo==1">DEVUELTO</span>	
																<span class="label label-danger" v-if="dato.procesoprestamo!=1">PENDIENTE</span>	
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
<script src="<?php echo base_url();?>phuyu/phuyu_reportes/ingresosalidas.js"> </script>