<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_form" class="phuyu-velzon-form">
	<form id="formulario" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" name="codregistro" v-model="campos.codregistro">

		<div class="card phuyu-card">
			<div class="card-body">
				<div class="phuyu-form-title">
					<div class="phuyu-form-icon"><i class="bi bi-tags"></i></div>
					<div>
						<div class="text-muted small text-uppercase fw-semibold">Administracion</div>
						<h5 class="mb-0 fw-bold">Registro de rubro</h5>
					</div>
				</div>

				<div class="row g-3">
					<div class="col-12">
						<label class="form-label">Descripcion rubro</label>
						<input type="text" name="descripcion" v-model.trim="campos.descripcion" class="form-control" required autocomplete="off" placeholder="Descripcion">
					</div>

					<div class="col-12">
						<div class="form-check form-switch">
							<input type="checkbox" class="form-check-input" id="activo_rubro" v-on:click="phuyu_activarrubro()" v-bind:checked="campos.activo==1">
							<label class="form-check-label" for="activo_rubro">Utilizar rubro en el sistema</label>
						</div>
					</div>
				</div>

				<div class="phuyu-section-title">Sucursales donde se manejara el rubro</div>
				<div class="phuyu-table-wrap">
					<table class="table table-hover align-middle">
						<thead>
							<tr>
								<th>Rubro</th>
								<th width="110" class="text-center">Permiso</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($sucursales as $key => $value) { ?>
								<tr>
									<td><?php echo $value["descripcion"];?></td>
									<td class="text-center">
										<input type="hidden" name="sucursales[]" value="<?php echo $value["codsucursal"];?>">
										<input type="checkbox" class="form-check-input" id="sucursal_<?php echo $value["codsucursal"];?>" value="<?php echo $value["codsucursal"];?>" v-model="campos.sucursales">
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>

				<div class="phuyu-form-actions">
					<button type="submit" class="btn btn-primary" v-bind:disabled="estado==1">
						<i class="bi bi-save me-1"></i> Guardar
					</button>
					<button type="button" class="btn btn-light" v-on:click="phuyu_cerrar()">
						<i class="bi bi-x-circle me-1"></i> Cerrar
					</button>
				</div>
			</div>
		</div>
	</form>
</div>

<script> var campos = {codregistro:"",descripcion: "",activo:0,sucursales: []}; </script>
<script src="<?php echo base_url();?>phuyu/phuyu_form.js"></script>
