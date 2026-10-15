<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_datos" class="phuyu-velzon-list">
	<div class="phuyu-page-title">
		<div class="phuyu-page-icon"><i class="bi bi-clipboard-data"></i></div>
		<div>
			<div class="text-muted small text-uppercase fw-semibold">Tesoreria</div>
			<h4 class="mb-0 fw-bold">Arqueos de caja</h4>
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb mb-0 mt-1">
					<li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
					<li class="breadcrumb-item active" aria-current="page">Arqueos de caja</li>
				</ol>
			</nav>
		</div>
	</div>

	<div class="card phuyu-card mb-3">
		<div class="card-body">
			<div class="row g-3 align-items-end justify-content-end">
				<div class="col-12 col-md-3">
					<label class="form-label">Desde</label>
					<input type="date" class="form-control input-sm" id="desde" value="<?php echo date('Y-m-01');?>" v-on:change="phuyu_buscar()" autocomplete="off">
				</div>
				<div class="col-12 col-md-3">
					<label class="form-label">Hasta</label>
					<input type="date" class="form-control input-sm" id="hasta" value="<?php echo date('Y-m-d');?>" v-on:change="phuyu_buscar()" autocomplete="off">
				</div>
			</div>
		</div>
	</div>

	<div class="phuyu_body">
		<input type="hidden" id="phuyu_opcion" value="1">
		<div class="card phuyu-card">
			<div class="card-body">
				<div class="phuyu_cargando" v-if="cargando">
					<div class="overlay-spinner"></div>
				</div>

				<div class="phuyu-table-wrap" v-if="!cargando">
					<table class="table table-hover table-striped align-middle">
						<thead>
							<tr>
								<th>Codigo</th>
								<th>F. apertura</th>
								<th>F. cierre</th>
								<th>Codigo diario</th>
								<th>S/. total apertura</th>
								<th>S/. total cierre</th>
								<th colspan="2" class="text-center">Anfitrionas</th>
								<th>V. diaria</th>
								<th>Balance</th>
								<th>Arqueo</th>
								<th>Excel</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in datos">
								<td>{{dato.codcontroldiario}}</td>
								<td>
									{{dato.fechaapertura}}
									<span class="text-muted small ms-1">{{dato.horaapertura_texto || 'S/H'}}</span>
								</td>
								<td>
									<span v-if="dato.fechacierre!=null">
										{{dato.fechacierre}}
										<span class="text-muted small ms-1">{{dato.horacierre_texto || 'S/H'}}</span>
									</span>
									<span class="badge bg-danger" v-else>SIN CERRAR</span>
								</td>
								<td>{{dato.codigodiario}}</td>
								<td>S/. {{dato.saldoinicialcaja}}</td>
								<td>
									<b v-if="dato.cerrado==0">S/. {{dato.cierre}}</b>
									<span class="badge bg-danger" v-else="dato.cerrado!=0">CAJA APERTURADA</span>
								</td>
								<td><button type="button" class="btn btn-success btn-sm" v-on:click="pdf_anfitrionas(dato)">Gral</button></td>
								<td><button type="button" class="btn btn-success btn-sm" v-on:click="pdf_anfitrionas_general(dato)">Res</button></td>
								<td><button type="button" class="btn btn-primary btn-sm" v-on:click="pdf_venta(dato)">V. diaria</button></td>
								<td><button type="button" class="btn btn-info btn-sm" v-on:click="pdf_balance(dato)">B. caja</button></td>
								<td><button type="button" class="btn btn-success btn-sm" v-on:click="pdf_arqueo_caja(dato)"><i class="bi bi-printer me-1"></i> PDF</button></td>
								<td><button type="button" class="btn btn-warning btn-sm" v-on:click="pdf_arqueo_excel(dato)"><i class="bi bi-download me-1"></i> Excel</button></td>
							</tr>
							<tr v-if="datos.length==0">
								<td colspan="12" class="text-center text-muted py-4">
									<i class="bi bi-inbox me-1"></i> Sin arqueos registrados
								</td>
							</tr>
						</tbody>
					</table>
				</div>

				<?php include("application/views/phuyu/phuyu_paginacion.php");?>
			</div>
		</div>
	</div>

	<div id="modal_empleados" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Reporte de anfitrionas</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>
				<div class="modal-body" id="modal_empleados_contenido"></div>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_caja/arqueos.js"> </script>
