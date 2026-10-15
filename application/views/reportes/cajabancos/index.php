<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<style>
	#phuyu_datos.phuyu-reportes-velzon .report-shell {
		border: 1px solid #e9ebec;
		border-radius: 10px;
		box-shadow: 0 1px 2px rgba(56, 65, 74, .06);
	}

	#phuyu_datos.phuyu-reportes-velzon .report-title {
		color: #212529;
		font-size: 1.05rem;
		font-weight: 700;
		margin: 0;
	}

	#phuyu_datos.phuyu-reportes-velzon .filter-band {
		background: #f8fafc;
		border: 1px solid #edf0f2;
		border-radius: 8px;
		padding: 14px;
	}

	#phuyu_datos.phuyu-reportes-velzon label {
		color: #495057;
		font-size: .72rem;
		font-weight: 700;
		margin-bottom: 6px;
		text-transform: uppercase;
	}

	#phuyu_datos.phuyu-reportes-velzon .form-control,
	#phuyu_datos.phuyu-reportes-velzon .form-select {
		border-color: #d9dee3;
		border-radius: 6px;
		min-height: 39px;
	}

	#phuyu_datos.phuyu-reportes-velzon .action-row {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
	}

	#phuyu_datos.phuyu-reportes-velzon .action-row .btn {
		align-items: center;
		display: inline-flex;
		gap: 6px;
		justify-content: center;
		min-height: 39px;
		white-space: nowrap;
	}

	#phuyu_datos.phuyu-reportes-velzon .scope-box {
		align-items: center;
		background: #fff;
		border: 1px solid #d9dee3;
		border-radius: 6px;
		display: flex;
		gap: 10px;
		min-height: 39px;
		padding: 7px 10px;
	}

	#phuyu_datos.phuyu-reportes-velzon .scope-box input {
		height: 18px;
		margin: 0;
		width: 18px;
	}

	#phuyu_datos.phuyu-reportes-velzon .results-box {
		border: 1px solid #edf0f2;
		border-radius: 8px;
		height: calc(100vh - 430px);
		min-height: 320px;
		overflow: auto;
	}

	#phuyu_datos.phuyu-reportes-velzon .results-box .table {
		margin-bottom: 0;
	}

	#phuyu_datos.phuyu-reportes-velzon .results-box thead th {
		background: #f3f6f9;
		position: sticky;
		top: 0;
		z-index: 1;
	}
</style>

<div id="phuyu_datos" class="phuyu-reportes-velzon phuyu-velzon-list">
	<div class="phuyu_body">
		<div class="card report-shell">
			<div class="card-body">
				<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
					<h4 class="report-title">REPORTE DE CAJA</h4>
					<button type="button" class="btn btn-outline-primary btn-sm" v-on:click="modal_conceptos()">
						<i class="bi bi-filter-square"></i> Conceptos
					</button>
				</div>

				<div class="filter-band mb-3">
					<div class="row g-3 align-items-end">
						<div class="col-xl-5 col-lg-6">
							<label>Seleccionar persona</label>
							<select class="form-select" id="codpersona" required>
								<option value="0">LISTA GENERAL - TODAS LAS PERSONAS</option>
							</select>
						</div>
						<div class="col-xl-3 col-lg-3 col-md-6">
							<label><i class="bi bi-calendar3"></i> Caja detallado al</label>
							<input type="date" class="form-control" id="fecha_detallado" value="<?php echo date('Y-m-d');?>">
						</div>
						<div class="col-xl-4 col-lg-3">
							<div class="action-row">
								<button type="button" class="btn btn-warning" v-on:click="caja_detallado()">
									<i class="bi bi-journal-text"></i> Caja detallado
								</button>
							</div>
						</div>
					</div>

					<hr class="my-3">

					<div class="row g-3 align-items-end">
						<div class="col-xl-2 col-lg-3 col-md-6">
							<label><i class="bi bi-calendar3"></i> Desde</label>
							<input type="date" class="form-control" id="fecha_desde" value="<?php echo date('Y-m-d');?>">
						</div>
						<div class="col-xl-2 col-lg-3 col-md-6">
							<label><i class="bi bi-calendar3"></i> Hasta</label>
							<input type="date" class="form-control" id="fecha_hasta" value="<?php echo date('Y-m-d');?>">
						</div>
						<div class="col-xl-2 col-lg-3 col-md-6">
							<label>Origen</label>
							<div class="scope-box">
								<input type="checkbox" v-model="campos.caja">
								<span>Caja</span>
							</div>
						</div>
						<div class="col-xl-2 col-lg-3 col-md-6">
							<label>&nbsp;</label>
							<div class="scope-box">
								<input type="checkbox" v-model="campos.banco">
								<span>Banco</span>
							</div>
						</div>
						<div class="col-xl-4">
							<label>Acciones</label>
							<div class="action-row">
								<button type="button" class="btn btn-success" v-on:click="reporte_movimientos()">
									<i class="bi bi-search"></i> Movimientos
								</button>
								<button type="button" class="btn btn-danger" v-on:click="reporte_movimientos_anulados()">
									<i class="bi bi-x-octagon"></i> Mov. anulados
								</button>
								<div class="dropdown">
									<button class="btn btn-warning dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										<i class="bi bi-printer"></i> Formatos
									</button>
									<div class="dropdown-menu">
										<a class="dropdown-item" href="javascript:;" v-on:click="pdf_caja()">Formato PDF</a>
										<a class="dropdown-item" href="javascript:;" v-on:click="excel_caja()">Formato Excel</a>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="results-box" id="phuyu_cajabancos">
					<div class="h-100 d-flex align-items-center justify-content-center text-muted" v-if="estado_detallado==0 && estado_movimientos==0">
						<div class="text-center">
							<i class="bi bi-bar-chart fs-3 d-block mb-2"></i>
							Seleccione un reporte para visualizar resultados.
						</div>
					</div>
					<table class="table table-striped table-hover align-middle" v-if="estado_detallado==1" style="font-size: 11px">
						<thead>
							<tr>
								<th width="120px">N° RECIBO</th>
								<th>CONCEPTO</th>
								<th>DOC.&nbsp;REFERENCIA</th>
								<th>RAZON SOCIAL</th>
								<th>REFERENCIA</th>
								<th>INGRESOS&nbsp;S/.</th>
								<th>EGRESOS&nbsp;S/.</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td colspan="5" align="right"> <b>SALDO ANTERIOR</b> </td>
								<td>{{saldocaja.ingresos}}</td>
								<td>{{saldocaja.egresos}}</td>
							</tr>
							<tr v-for="dato in detallado">
								<td>{{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
								<td>{{dato.concepto}}</td>
								<td>{{dato.seriecomprobante_ref}} - {{dato.nrocomprobante_ref}}</td>
								<td>{{dato.razonsocial}}</td>
								<td>{{dato.referencia}}</td>
								<td> <b v-if="dato.tipomovimiento==1">{{dato.importe_r}}</b> </td>
								<td> <b v-if="dato.tipomovimiento==2">{{dato.importe_r}}</b> </td>
							</tr>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="5" align="right"> <b>TOTALES</b> </td>
								<td>{{saldocaja.totalingresos}}</td>
								<td>{{saldocaja.totalegresos}}</td>
							</tr>
							<tr>
								<td colspan="7" align="right"> <b>SALDO (INGRESOS - EGRESOS): {{saldocaja.total}}</b> </td>
							</tr>
						</tfoot>
					</table>

					<table class="table table-striped table-hover align-middle" v-if="estado_movimientos==1" style="font-size: 11px">
						<thead>
							<tr>
								<th width="100px">FECHA</th>
								<th width="120px">N° RECIBO</th>
								<th>CONCEPTO</th>
								<th>DOC.&nbsp;REFERENCIA</th>
								<th>RAZON SOCIAL</th>
								<th>REFERENCIA</th>
								<th>INGRESOS&nbsp;S/.</th>
								<th>EGRESOS&nbsp;S/.</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in movimientos">
								<td>{{dato.fechamovimiento}}</td>
								<td>{{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
								<td>{{dato.concepto}}</td>
								<td>{{dato.seriecomprobante_ref}} - {{dato.nrocomprobante_ref}}</td>
								<td>{{dato.razonsocial}}</td>
								<td>{{dato.referencia}}</td>
								<td> <b v-if="dato.tipomovimiento==1">{{dato.importe_r}}</b> </td>
								<td> <b v-if="dato.tipomovimiento==2">{{dato.importe_r}}</b> </td>
							</tr>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="6" align="right"> <b>TOTALES</b> </td>
								<td>{{saldocaja.totalingresos}}</td>
								<td>{{saldocaja.totalegresos}}</td>
							</tr>
							<tr>
								<td colspan="8" align="right"> <b>SALDO (INGRESOS - EGRESOS): {{saldocaja.total}}</b> </td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>

			<div id="modal_reportes" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="modal-dialog" style="width:100%;margin:0px;">
					<div class="modal-content" align="center" style="border-radius:0px">
						<div class="modal-header">
							<button type="button" class="close" data-bs-dismiss="modal" style="font-size:30px;margin-bottom:0px;">
								<i class="bi bi-x-circle"></i>
							</button>
							<h4 class="modal-title">
								<b style="letter-spacing:4px;"><?php echo $_SESSION["phuyu_empresa"];?> </b>
							</h4>
						</div>
						<div class="modal-body" id="reportes_modal" style="height:450px;padding:0px;">
							<iframe id="phuyu_pdf" src="" style="width:100%; height:100%; border:none;"> </iframe>
						</div>
					</div>
				</div>
			</div>

			<div id="modal_conceptos" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-bs-dismiss="modal" style="font-size:30px;margin-bottom:0px;">
								<i class="bi bi-x-circle"></i>
							</button>
							<h4 class="modal-title"> <b>CONCEPTOS DE CAJA</b> </h4>
						</div>
						<div class="modal-body">
							<div class="row">
								<div class="col-md-12">
									<table class="table table-bordered">
										<tr>
											<td style="width: 350px;"><b>MARCAR TODOS LOS CONCEPTOS</b></td>
											<td class="text-center"> <input type="checkbox" class="check-phuyu" id="marcar" v-on:change="phuyu_marcar()" style="height:20px;width:20px;" checked> </td>
										</tr>
									</table>
								</div>
							</div>
							<div class="row">
								<div class="col-md-6" style="height:400px;overflow-y:scroll;">
									<table class="table table-bordered">
										<thead>
											<tr>
												<th>CONCEPTO INGRESO</th>
												<th>MARCAR</th>
											</tr>
										</thead>
										<tbody>

											<?php
												foreach ($conceptosingresos as $key => $value) { ?>
													<tr>
														<td><?php echo $value["descripcion"];?></td>
														<td align="center">
															<input type="checkbox" name="conceptos" value="<?php echo $value['codconcepto'];?>" style="height:20px;width:20px;" checked>
														</td>
													</tr>
												<?php }
											?>
										</tbody>
									</table>
								</div>
								<div class="col-md-6" style="height:400px;overflow-y:scroll;">
									<table class="table table-bordered">
										<thead>
											<tr>
												<th>CONCEPTO EGRESO</th>
												<th>MARCAR</th>
											</tr>
										</thead>
										<tbody>
											<?php
												foreach ($conceptosegresos as $key => $value) { ?>
													<tr>
														<td><?php echo $value["descripcion"];?></td>
														<td align="center">
															<input type="checkbox" name="conceptos" value="<?php echo $value['codconcepto'];?>" style="height:20px;width:20px;" checked>
														</td>
													</tr>
												<?php }
											?>
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
<script>
	var campos = {"codpersona":0,"fecha_detallado":$("#fecharef").val(),"fecha_desde":$("#fecharef").val(),"fecha_hasta":$("#fecharef").val(),"caja":1,"banco":0,"reporte":0,"cliente":"TODAS LAS PERSONAS"};

	var pantalla = jQuery(document).height(); $("#reportes_modal").css({height: pantalla - 65});
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_reportes/cajabancos.js"> </script>
<script src="<?php echo base_url();?>phuyu/phuyu_selects.js"> </script>
