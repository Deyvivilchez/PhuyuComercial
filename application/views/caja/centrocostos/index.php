<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_datos" class="phuyu-velzon-list">
	<div class="phuyu-page-title">
		<div class="phuyu-page-icon"><i class="bi bi-diagram-3"></i></div>
		<div>
			<div class="text-muted small text-uppercase fw-semibold">Tesoreria</div>
			<h4 class="mb-0 fw-bold">Centros de costos</h4>
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb mb-0 mt-1">
					<li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
					<li class="breadcrumb-item active" aria-current="page">Centros de costos</li>
				</ol>
			</nav>
		</div>
	</div>

	<div class="phuyu_body">
		<input type="hidden" id="phuyu_opcion" value="1">
		<div class="card phuyu-card">
			<div class="card-body">
				<div class="phuyu-toolbar">
					<div class="phuyu-search">
						<i class="bi bi-search"></i>
						<input type="text" class="form-control datatable-search" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="Buscar centro de costo">
					</div>

					<div class="phuyu-actions">
						<button type="button" class="btn btn-primary" v-on:click="phuyu_nuevo()">
							<i class="bi bi-plus-circle me-1"></i> Nuevo
						</button>
						<button type="button" class="btn btn-warning" v-on:click="phuyu_editar()">
							<i class="bi bi-pencil-square me-1"></i> Editar
						</button>
						<button type="button" class="btn btn-danger" v-on:click="phuyu_eliminar()">
							<i class="bi bi-trash3 me-1"></i> Eliminar
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
								<th width="70" class="text-center">Sel.</th>
								<th>Descripcion</th>
								<th>Centro costo</th>
								<th>Cta abono</th>
								<th>Cta cargo</th>
								<th>Cta debe</th>
								<th>Cta haber</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in datos">
								<td class="text-center">
									<input type="radio" class="form-check-input phuyu_radio" name="phuyu_seleccionar" v-on:click="phuyu_seleccionar(dato.codcentrocosto)">
								</td>
								<td>{{dato.descripcion}}</td>
								<td>{{dato.centrocosto}}</td>
								<td>{{dato.ctacontableabono}}</td>
								<td>{{dato.ctacontablecargo}}</td>
								<td>{{dato.ctacontabledebe}}</td>
								<td>{{dato.ctacontablehaber}}</td>
							</tr>
							<tr v-if="datos.length==0 && !cargando">
								<td colspan="7" class="text-center text-muted py-4">
									<i class="bi bi-inbox me-1"></i> Sin centros de costos registrados
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

<script src="<?php echo base_url();?>phuyu/phuyu_datos.js"> </script>
