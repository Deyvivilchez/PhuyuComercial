<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_datos" class="phuyu-velzon-list">
	<div class="phuyu-page-title">
		<span class="phuyu-page-icon"><i class="bi bi-truck"></i></span>
		<div>
			<h4 class="mb-1">Administración de vehículos</h4>
			<p class="text-muted mb-0">Registro y mantenimiento de vehículos para despacho.</p>
		</div>
	</div>

	<div class="phuyu_body">
		<input type="hidden" id="phuyu_opcion" value="1">

		<div class="card phuyu-card">
			<div class="card-body">
				<div class="phuyu-toolbar">
					<div class="phuyu-search">
						<i class="bi bi-search"></i>
						<input class="form-control datatable-search" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="Buscar vehículo..." />
					</div>

					<div class="phuyu-actions">
						<button type="button" class="btn btn-primary" v-on:click="phuyu_nuevo()">
							<i class="bi bi-plus-circle me-1"></i> Nuevo
						</button>
						<button type="button" class="btn btn-warning" v-on:click="phuyu_editar()">
							<i class="bi bi-pencil-square me-1"></i> Editar
						</button>
						<button type="button" class="btn btn-danger eliminar" v-on:click="phuyu_eliminar()">
							<i class="bi bi-trash me-1"></i> Eliminar
						</button>
					</div>
				</div>

				<div class="phuyu_cargando" v-if="cargando">
					<div class="overlay-spinner"></div>
				</div>

				<div class="phuyu-table-wrap">
					<table class="table table-hover table-striped align-middle">
						<thead>
							<tr>
								<th>Descripción del vehículo</th>
								<th>Nro placa</th>
								<th>Constancia de inscripción</th>
								<th style="width:70px;" class="text-center">
									<i class="bi bi-check2-circle"></i>
								</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in datos">
								<td class="fw-semibold">{{dato.descripcion}}</td>
								<td class="fw-bold text-success">{{dato.nroplaca}}</td>
								<td>{{dato.constancia}}</td>
								<td class="text-center">
									<input type="radio" class="form-check-input phuyu_radio" name="phuyu_seleccionar" v-on:click="phuyu_seleccionar(dato.codvehiculo)">
								</td>
							</tr>
							<tr v-if="datos.length === 0 && !cargando">
								<td colspan="4" class="text-center text-muted py-4">
									<i class="bi bi-inbox d-block mb-1" style="font-size:28px;"></i>
									Sin vehículos registrados
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

<script src="<?php echo base_url();?>phuyu/phuyu_datos.js"></script>
