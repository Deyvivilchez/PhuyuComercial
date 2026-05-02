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

	<div id="modal_resumenes" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header modal-phuyu-titulo">
					<h4 class="modal-title">Informacion del resumen</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>

				<div class="modal-body" style="height:350px;overflow-y:auto;">
					<table class="table table-bordered" style="font-size: 11px">
						<thead>
							<tr>
								<th>RAZON SOCIAL</th>
								<th>COMPROBANTE</th>
								<th>F.COMPROBANTE</th>
								<th>F.ANULADO</th>
								<th width="100px">MOTIVO</th>
								<th>TOTAL</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in resumenes_info">
								<td>{{dato.cliente}}</td>
								<td>{{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
								<td>{{dato.fechacomprobante}}</td>
								<td>{{dato.fechaanulacion}}</td>
								<td>{{dato.motivobaja}}</td>
								<td>{{dato.importe}}</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_facturacion/resumenes.js"> </script>
