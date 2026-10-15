<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_form" class="phuyu-velzon-form">
	<form id="formulario" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" name="codregistro" v-model="campos.codregistro">

		<div class="card phuyu-card">
			<div class="card-body">
				<div class="phuyu-form-title">
					<div class="phuyu-form-icon"><i class="bi bi-diagram-3"></i></div>
					<div>
						<div class="text-muted small text-uppercase fw-semibold">Tesoreria</div>
						<h5 class="mb-0 fw-bold">Centro de costo</h5>
					</div>
				</div>

				<div class="row g-3">
					<div class="col-12">
						<label class="form-label">Descripcion</label>
						<input type="text" name="descripcion" v-model.trim="campos.descripcion" class="form-control" required autocomplete="off" placeholder="Descripcion" maxlength="100">
					</div>

					<div class="col-12">
						<label class="form-label">Centro costo</label>
						<input type="text" name="centrocosto" v-model.trim="campos.centrocosto" class="form-control" required autocomplete="off" placeholder="Centro costo" maxlength="10">
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Cta abono</label>
						<input type="text" name="ctacontableabono" v-model.trim="campos.ctacontableabono" class="form-control" required autocomplete="off" placeholder="Cuenta abono" maxlength="20">
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Cta cargo</label>
						<input type="text" name="ctacontablecargo" v-model.trim="campos.ctacontablecargo" class="form-control" required autocomplete="off" placeholder="Cuenta cargo" maxlength="20">
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Cta debe</label>
						<input type="text" name="ctacontabledebe" v-model.trim="campos.ctacontabledebe" class="form-control" required autocomplete="off" placeholder="Cuenta debe" maxlength="20">
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Cta haber</label>
						<input type="text" name="ctacontablehaber" v-model.trim="campos.ctacontablehaber" class="form-control" required autocomplete="off" placeholder="Cuenta haber" maxlength="20">
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

<script> var campos = {codregistro:"",descripcion: "",centrocosto: "",ctacontableabono: "",ctacontablecargo: "",ctacontabledebe: "",ctacontablehaber: ""}; </script>
<script src="<?php echo base_url();?>phuyu/phuyu_form.js"></script>
