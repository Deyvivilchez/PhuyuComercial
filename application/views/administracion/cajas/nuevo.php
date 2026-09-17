<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_form" class="phuyu-velzon-form">
	<form id="formulario" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" name="codregistro" v-model="campos.codregistro">

		<div class="card phuyu-card">
			<div class="card-body">
				<div class="phuyu-form-title">
					<div class="phuyu-form-icon"><i class="bi bi-cash-stack"></i></div>
					<div>
						<div class="text-muted small text-uppercase fw-semibold">Administracion</div>
						<h5 class="mb-0 fw-bold">Registro de caja</h5>
					</div>
				</div>

				<div class="row g-3">
					<div class="col-12">
						<label class="form-label">Sucursal caja</label>
						<select class="form-select" name="codsucursal" v-model="campos.codsucursal" required>
							<option value="">SELECCIONE</option>
							<?php foreach ($sucursales as $key => $value) { ?>
								<option value="<?php echo $value["codsucursal"];?>"><?php echo $value["descripcion"];?></option>
							<?php } ?>
						</select>
					</div>

					<div class="col-12">
						<label class="form-label">Descripcion caja</label>
						<input type="text" name="descripcion" v-model.trim="campos.descripcion" class="form-control" required autocomplete="off" placeholder="Descripcion">
					</div>

					<div class="col-12">
						<label class="form-label">Direccion caja</label>
						<input type="text" name="direccion" v-model="campos.direccion" class="form-control" required autocomplete="off" placeholder="Direccion">
					</div>

					<div class="col-12">
						<label class="form-label">Telefonos caja</label>
						<input type="number" name="telefonos" v-model.number="campos.telefonos" class="form-control" autocomplete="off" placeholder="Telefonos">
					</div>
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

<script> var campos = {codregistro:"",codsucursal:"",descripcion: "",direccion: "",telefonos: ""}; </script>
<script src="<?php echo base_url();?>phuyu/phuyu_form.js"></script>
