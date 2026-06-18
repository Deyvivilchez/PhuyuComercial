<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_sunat" class="phuyu-velzon-list phuyu-cpe-velzon">
	<div class="phuyu-page-title">
		<div class="phuyu-page-icon"><i class="bi bi-card-checklist"></i></div>
		<div>
			<div class="text-muted small text-uppercase fw-semibold">CPE</div>
			<h4 class="mb-0 fw-bold">Resumenes diarios</h4>
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb mb-0 mt-1">
					<li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
					<li class="breadcrumb-item active" aria-current="page">Resumenes</li>
				</ol>
			</nav>
		</div>
	</div>

	<div class="phuyu_body">
		<div class="card phuyu-card">
			<div class="card-body">
				<div class="row g-3 align-items-end mb-3">
					<div class="col-12 col-md-3">
						<label><i class="bi bi-calendar-date me-1"></i> Desde</label>
						<input type="date" class="form-control" id="fecha_desde" value="<?php echo date('Y-m-d');?>" v-on:blur="phuyu_buscar()" autocomplete="off">
					</div>
					<div class="col-12 col-md-3">
						<label><i class="bi bi-calendar-check me-1"></i> Hasta</label>
						<input type="date" class="form-control" id="fecha_hasta" value="<?php echo date('Y-m-d');?>" v-on:blur="phuyu_buscar()" autocomplete="off">
					</div>
					<div class="col-12 col-md-6">
						<label>Buscar</label>
						<input type="text" class="form-control" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="Buscar resumen">
					</div>
				</div>
				<div class="phuyu_cargando" v-if="cargando">
					<img src="<?php echo base_url();?>public/img/phuyu_loading.gif"> <h5>CARGANDO DATOS</h5>
				</div>

				<div v-if="!cargando">
					<div class="table-responsive">
						<table class="table table-hover table-striped align-middle" style="font-size: 11px">
							<thead>
								<tr>
									<th width="10px">TIPO</th>
									<th>RESUMEN DIARIO</th>
									<th>ENVIO</th>
									<th width="10px">PERIODO</th>
									<th width="10px">TICKET</th>
									<th>Descripcion</th>
									<th>SUNAT</th>
									<th width="10px">ACCIONES</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="dato in datos">
									<td> <span class="label label-success">{{dato.tiporesumen}}</span> </td>
									<td>{{dato.nombre_xml}}</td>
									<td>{{dato.fechaenvio}}</td>
									<td>{{dato.periodo}}</td>
									<td>{{dato.ticket}}</td>
									<td>{{dato.descripcion_cdr}}</td>
									<td>
										<span class="label label-danger" v-if="dato.estado==0">PENDIENTE</span>
										<span class="label label-success" v-else="dato.estado!=0">ENVIADO</span>
									</td>
									<td>
										<button type="button" class="btn btn-info btn-xs btn-table" style="margin:1px;" v-on:click="phuyu_verresumen(dato)">VER RESUMEN</button>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<?php include("application/views/phuyu/phuyu_paginacion.php");?>
				</div>
			</div>
		</div>
	</div>

	<div id="modal_resumenes" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal_resumenes_titulo" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header modal-phuyu-titulo align-items-center">
					<div>
						<div class="text-white-50 small text-uppercase fw-semibold">SUNAT</div>
						<h4 class="modal-title mb-0" id="modal_resumenes_titulo">Información del resumen</h4>
					</div>
					<button type="button" class="btn-close btn-close-white" v-on:click="phuyu_cerrarresumen()" aria-label="Cerrar"></button>
				</div>

				<div class="modal-body p-0">
					<div class="table-responsive" style="max-height:420px;overflow-y:auto;">
						<table class="table table-hover table-striped align-middle mb-0" style="font-size: 11px">
							<thead class="table-light">
								<tr>
									<th>RAZÓN SOCIAL</th>
									<th>COMPROBANTE</th>
									<th>F. COMPROBANTE</th>
									<th>F. ANULADO</th>
									<th width="140px">MOTIVO</th>
									<th class="text-end">TOTAL</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="dato in resumenes_info">
									<td>{{dato.cliente}}</td>
									<td><span class="fw-semibold">{{dato.seriecomprobante}} - {{dato.nrocomprobante}}</span></td>
									<td>{{dato.fechacomprobante}}</td>
									<td>{{dato.fechaanulacion}}</td>
									<td>{{dato.motivobaja}}</td>
									<td class="text-end">{{dato.importe}}</td>
								</tr>
								<tr v-if="resumenes_info.length==0">
									<td colspan="6" class="text-center text-muted py-4">Sin comprobantes en este resumen.</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-light" v-on:click="phuyu_cerrarresumen()">
						<i class="bi bi-x-lg me-1"></i> Cerrar
					</button>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_facturacion/resumenes.js"> </script>
