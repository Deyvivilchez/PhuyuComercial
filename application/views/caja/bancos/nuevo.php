<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_form" class="phuyu-velzon-form">
	<form id="formulario" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" name="codregistro" v-model="campos.codregistro">

		<div class="card phuyu-card">
			<div class="card-body">
				<div class="phuyu-form-title">
					<div class="phuyu-form-icon"><i class="bi bi-bank"></i></div>
					<div>
						<div class="text-muted small text-uppercase fw-semibold">Tesoreria</div>
						<h5 class="mb-0 fw-bold">Registro de banco</h5>
					</div>
				</div>

				<div class="row g-3">
					<div class="col-12">
						<label class="form-label">Nombre del banco</label>
						<input type="text" name="descripcion" v-model.trim="campos.descripcion" class="form-control" required autocomplete="off" placeholder="Descripcion">
					</div>

					<div class="col-12">
						<label class="form-label">Abreviatura</label>
						<input type="text" name="abreviatura" v-model.trim="campos.abreviatura" class="form-control" required autocomplete="off" placeholder="Abreviatura">
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

<script> var campos = {codregistro:"",descripcion: "",abreviatura:""}; </script>
<script src="<?php echo base_url();?>phuyu/phuyu_form.js"></script>
