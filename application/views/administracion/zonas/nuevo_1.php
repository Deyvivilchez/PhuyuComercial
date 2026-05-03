<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_form_1" class="phuyu-velzon-form">
	<div class="phuyu-form-title">
		<div class="phuyu-form-icon"><i class="bi bi-geo-alt"></i></div>
		<div>
			<h5 class="mb-1 fw-bold">Registrar zona</h5>
			<p class="text-muted mb-0">Ubicacion comercial para agrupar clientes y rutas.</p>
		</div>
	</div>

	<form id="formulario" v-on:submit.prevent="phuyu_guardar_1()">
		<div class="row g-3">
			<div class="col-12">
				<label class="form-label">Departamento</label>
				<select class="form-select" name="departamento" v-model="campos.departamento1" required v-on:change="phuyu_provincias1()">
					<option value="">Seleccione</option>
					<?php foreach ($departamentos as $key => $value) { ?>
						<option value="<?php echo $value['ubidepartamento'];?>"><?php echo $value["departamento"];?></option>
					<?php } ?>
				</select>
			</div>

			<div class="col-md-6">
				<label class="form-label">Provincia</label>
				<select class="form-select" name="provincia1" v-model="campos.provincia1" id="provincia1" required v-on:change="phuyu_distritos1()">
					<option value="">Seleccione</option>
				</select>
			</div>

			<div class="col-md-6">
				<label class="form-label">Distrito</label>
				<select class="form-select" name="codubigeo1" v-model="campos.codubigeo1" id="codubigeo1" required>
					<option value="">Seleccione</option>
				</select>
			</div>

			<div class="col-12">
				<label class="form-label">Descripcion zona</label>
				<input type="text" name="descripcion" v-model.trim="campos.descripcion" class="form-control" required autocomplete="off" placeholder="Descripcion">
			</div>
		</div>

		<div class="phuyu-form-actions">
			<button type="submit" class="btn btn-primary" v-bind:disabled="estado_1==1">
				<i class="bi bi-save me-1"></i>
				Guardar
			</button>
			<button type="button" class="btn btn-light" data-bs-dismiss="modal">
				<i class="bi bi-x-circle me-1"></i>
				Cerrar
			</button>
		</div>
	</form>
</div>

<script> var campos = {codregistro:"",descripcion: "",departamento1: "",provincia1: "",codubigeo1: ""}; </script>
<script src="<?php echo base_url();?>phuyu/phuyu_form_zonas.js"></script>
