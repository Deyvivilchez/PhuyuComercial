<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_form" class="phuyu-velzon-form">
	<form id="formulario" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" name="codregistro" v-model="campos.codregistro">

		<div class="card phuyu-card">
			<div class="card-body">
				<div class="phuyu-form-title">
					<span class="phuyu-form-icon"><i class="bi bi-truck"></i></span>
					<div>
						<h5 class="mb-1">Registro de vehículo</h5>
						<p class="text-muted mb-0">Datos usados para guías y despachos.</p>
					</div>
				</div>

				<div class="row g-3">
					<div class="col-12">
						<label class="form-label">Descripción del vehículo <span class="text-danger">*</span></label>
						<input type="text" name="descripcion" v-model.trim="campos.descripcion" class="form-control" required autocomplete="off" placeholder="Ej. Camión principal" maxlength="100">
					</div>
					<div class="col-12">
						<label class="form-label">Nro placa <span class="text-danger">*</span></label>
						<input type="text" name="nroplaca" v-model.trim="campos.nroplaca" class="form-control text-uppercase" required autocomplete="off" placeholder="Ej. ABC-123" maxlength="20">
					</div>
					<div class="col-12">
						<label class="form-label">Constancia de inscripción <span class="text-danger">*</span></label>
						<input type="text" name="constancia" v-model.trim="campos.constancia" class="form-control" required autocomplete="off" placeholder="Constancia" maxlength="100">
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

<script> var campos = {codregistro:"",descripcion:"",nroplaca:"",constancia:""}; </script>
<script src="<?php echo base_url();?>phuyu/phuyu_form.js"></script>
