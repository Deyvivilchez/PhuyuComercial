<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_form" class="phuyu-velzon-form">
	<div class="card phuyu-card">
		<div class="card-body">
			<div class="phuyu-form-title">
				<div class="phuyu-form-icon"><i class="bi bi-list-check"></i></div>
				<div>
					<div class="text-muted small text-uppercase fw-semibold">Administracion</div>
					<h5 class="mb-0 fw-bold">Lineas de la sucursal</h5>
				</div>
			</div>

			<div class="alert alert-light border d-flex align-items-center justify-content-between gap-3">
				<strong>Marcar todas las lineas</strong>
				<input type="checkbox" class="form-check-input" id="marcar" v-on:change="phuyu_marcar()">
			</div>

			<form id="formulario" v-on:submit.prevent="phuyu_guardarlineas()">
				<input type="hidden" name="codsucursal" v-model="campos.codsucursal">
				<div class="phuyu-table-wrap">
					<table class="table table-hover align-middle">
						<thead>
							<tr>
								<th>Linea</th>
								<th width="110" class="text-center">Permiso</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($lineas as $key => $value) { ?>
								<tr>
									<td><?php echo $value["descripcion"];?></td>
									<td class="text-center">
										<input type="hidden" name="lineas[]" value="<?php echo $value["codlinea"];?>">
										<input type="checkbox" class="form-check-input" id="linea_<?php echo $value["codlinea"];?>" value="<?php echo $value["codlinea"];?>" v-model="campos.lineas">
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
			</form>
		</div>
	</div>
</div>

<script> var campos = {codsucursal:"",lineas: []}; </script>
<script src="<?php echo base_url();?>phuyu/phuyu_form.js"></script>
