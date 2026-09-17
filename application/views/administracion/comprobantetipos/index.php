<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_datos" class="phuyu-velzon-list">
	<div class="phuyu-page-title">
		<div class="phuyu-page-icon"><i class="bi bi-receipt"></i></div>
		<div>
			<div class="text-muted small text-uppercase fw-semibold">Administracion</div>
			<h4 class="mb-0 fw-bold">Tipos de comprobante</h4>
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb mb-0 mt-1">
					<li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
					<li class="breadcrumb-item active" aria-current="page">Tipo comprobantes</li>
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
						<input class="form-control datatable-search" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="Buscar tipo de comprobante">
					</div>

					<div class="phuyu-actions">
						<button type="button" class="btn btn-primary" v-on:click="phuyu_nuevo()">
							<i class="bi bi-plus-circle me-1"></i> Nuevo
						</button>
						<button type="button" class="btn btn-warning" v-on:click="phuyu_editar()">
							<i class="bi bi-pencil-square me-1"></i> Editar
						</button>
						<button type="button" class="btn btn-danger eliminar" v-on:click="phuyu_eliminar()">
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
								<th width="90">ID</th>
								<th>Descripcion</th>
								<th>Abreviatura</th>
								<th>Codigo oficial</th>
								<th width="70" class="text-center">Sel.</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="(dato,index) in datos">
								<td class="fw-semibold">{{dato.codcomprobantetipo}}</td>
								<td>{{dato.descripcion}}</td>
								<td>{{dato.abreviatura}}</td>
								<td>{{dato.oficial}}</td>
								<td class="text-center">
									<input type="radio" class="form-check-input" name="phuyu_seleccionar" v-on:click="phuyu_seleccionar(dato.codcomprobantetipo)">
								</td>
							</tr>
							<tr v-if="datos.length==0 && !cargando">
								<td colspan="5" class="text-center text-muted py-4">
									<i class="bi bi-inbox me-1"></i> Sin tipos de comprobante registrados
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
