<div id="phuyu_datos" class="phuyu-hotel-list">
	<div class="page-title-box d-flex align-items-center justify-content-between">
		<div>
			<h4 class="mb-1"><i class="ri-home-8-line me-1"></i> Habitaciones</h4>
			<div class="text-muted small">Gestion de habitaciones - <?php echo $_SESSION["phuyu_sucursal"];?></div>
		</div>
	</div>

	<div class="card">
		<div class="card-body">
			<input type="hidden" id="phuyu_opcion" value="1">
			<div class="d-flex flex-wrap gap-2 justify-content-between mb-3">
				<div style="max-width:360px;width:100%">
					<input class="form-control" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="Buscar habitacion o tipo">
				</div>
				<div class="d-flex gap-2">
					<button type="button" class="btn btn-primary" v-on:click="phuyu_nuevo()"><i class="ri-add-circle-line me-1"></i> Nuevo</button>
					<button type="button" class="btn btn-warning" v-on:click="phuyu_editar()"><i class="ri-pencil-line me-1"></i> Editar</button>
					<button type="button" class="btn btn-danger" v-on:click="phuyu_eliminar()"><i class="ri-delete-bin-line me-1"></i> Eliminar</button>
				</div>
			</div>

			<div class="table-responsive">
				<table class="table table-hover align-middle mb-0">
					<thead>
						<tr>
							<th>ID</th>
							<th>Numero</th>
							<th>Ambiente</th>
							<th>Tipo</th>
							<th>Capacidad</th>
							<th>Precio base</th>
							<th>Caracteristicas</th>
							<th>Estado</th>
							<th class="text-center">Sel.</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="dato in datos">
							<td>{{dato.codhabitacion}}</td>
							<td class="fw-semibold">{{dato.numero}}</td>
							<td>{{dato.ambiente}}</td>
							<td>{{dato.tipo}}</td>
							<td>{{dato.capacidad}}</td>
							<td>S/. {{Number(dato.preciobase).toFixed(2)}}</td>
							<td>
								<span class="me-1" v-for="caracteristica in dato.caracteristicas" v-bind:title="caracteristica.descripcion">
									<i v-bind:class="caracteristica.icono || 'ri-checkbox-circle-line'"></i>
								</span>
							</td>
							<td><span class="badge bg-primary-subtle text-primary">{{dato.situacion_texto}}</span></td>
							<td class="text-center"><input type="radio" class="form-check-input" name="phuyu_seleccionar" v-on:click="phuyu_seleccionar(dato.codhabitacion)"></td>
						</tr>
						<tr v-if="datos.length==0">
							<td colspan="9" class="text-center text-muted py-4">Sin habitaciones registradas</td>
						</tr>
					</tbody>
				</table>
			</div>
			<?php include("application/views/phuyu/phuyu_paginacion.php");?>
		</div>
	</div>
</div>
<script src="<?php echo base_url();?>phuyu/phuyu_datos.js"></script>
