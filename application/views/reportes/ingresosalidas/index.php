<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<style>
	#phuyu_datos .report-shell { border: 1px solid #e9ebec; border-radius: 10px; box-shadow: 0 1px 2px rgba(56,65,74,.06); }
	#phuyu_datos .report-title { font-size: 1.05rem; font-weight: 700; margin: 0; }
	#phuyu_datos .filter-band { background: #f8fafc; border: 1px solid #edf0f2; border-radius: 8px; padding: 14px; }
	#phuyu_datos label { color: #495057; font-size: .72rem; font-weight: 700; margin-bottom: 6px; text-transform: uppercase; }
	#phuyu_datos .form-control, #phuyu_datos .form-select { border-color: #d9dee3; border-radius: 6px; min-height: 39px; }
	#phuyu_datos .action-row { display: flex; flex-wrap: wrap; gap: 8px; }
	#phuyu_datos .action-row .btn { align-items: center; display: inline-flex; gap: 6px; min-height: 39px; white-space: nowrap; }
	#phuyu_datos .results-box { border: 1px solid #edf0f2; border-radius: 8px; height: calc(100vh - 390px); min-height: 330px; overflow: auto; }
	#phuyu_datos .results-box .table { margin-bottom: 16px; }
	#phuyu_datos .results-box th { background: #f3f6f9; }
</style>

<div id="phuyu_datos" class="phuyu-reportes-velzon phuyu-velzon-list">
	<div class="phuyu_body">
		<div class="card report-shell">
			<div class="card-body">
				<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
					<h5 class="report-title">REPORTE DE INGRESOS Y SALIDAS DE ALMACEN</h5>
					<div class="action-row">
						<button type="button" class="btn btn-warning" v-on:click="phuyu_modalprestamos()"><i class="bi bi-search"></i> Prestamos</button>
						<button type="button" class="btn btn-danger" v-on:click="generar_pdf()"><i class="bi bi-printer"></i> PDF</button>
						<button type="button" class="btn btn-success" v-on:click="generar_excel()"><i class="bi bi-file-earmark-excel"></i> Excel</button>
					</div>
				</div>

				<div class="filter-band mb-3">
					<div class="row g-3 align-items-end">
					<div class="col-xl-2 col-lg-4 col-md-6">
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
					<div class="col-xl-2 col-lg-4 col-md-6">
						<label>TIPO MOVIMIENTO</label>
						<select class="form-select" id="tipo" v-model="campos.tipo" v-on:change="phuyu_movimientos()" required>
							<option value="0">TODOS</option>
							<option value="1">INGRESOS</option>
							<option value="2">SALIDAS</option>
						</select>
					</div>
					<div class="col-xl-2 col-lg-4 col-md-6">
						<label>MOVIMIENTO</label>
						<select class="form-select" id="codmovimientotipo" v-model="campos.codmovimientotipo">
							<option value="0">TODOS</option>
						</select>
					</div>
					<div class="col-xl-2 col-lg-4 col-md-6">
						<label>DESDE</label>
						<input type="date" class="form-control" id="fechadesde" value="<?php echo date('Y-m-01');?>" autocomplete="off">
					</div>
					<div class="col-xl-2 col-lg-4 col-md-6">
						<label>HASTA</label>
						<input type="date" class="form-control" id="fechahasta" value="<?php echo date('Y-m-d');?>" autocomplete="off">
					</div>
					<div class="col-xl-2 col-lg-4 col-md-6">
						<label>Accion</label>
						<button type="button" class="btn btn-primary w-100" v-on:click="generar_reporte()">
							<i class="bi bi-search"></i> Consultar
						</button>
					</div>
					</div>
				</div>
				<div class="results-box detalle">
					<div class="h-100 d-flex align-items-center justify-content-center text-muted" v-if="datos.length==0">
						Seleccione filtros y consulte movimientos.
					</div>
					<div v-for="dato in datos">
						<table class="table table-striped table-hover align-middle" style="font-size: 11px">
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
									<span class="badge bg-primary" v-if="d.tipomov==1">INGRESO</span>
									<span class="badge bg-danger" v-if="d.tipomov==2">SALIDA</span>
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
								<button type="button" class="close" data-bs-dismiss="modal" style="font-size:30px;margin-bottom:0px;">
									<i class="bi bi-x-circle"></i>
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
										<button type="button" style="margin-top: 2rem" class="btn btn-primary" v-on:click="phuyu_listaprestamos()"><i class="bi bi-search"></i> CONSULTAR</button>
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
																<span class="badge bg-success" v-if="dato.procesoprestamo==1">DEVUELTO</span>
																<span class="badge bg-danger" v-if="dato.procesoprestamo!=1">PENDIENTE</span>
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
