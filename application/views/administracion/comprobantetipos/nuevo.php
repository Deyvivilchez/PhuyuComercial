<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_form" class="phuyu-velzon-form">
	<form id="formulario" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" name="codregistro" v-model="campos.codregistro">

		<div class="card phuyu-card">
			<div class="card-body">
				<div class="phuyu-form-title">
					<div class="phuyu-form-icon"><i class="bi bi-receipt"></i></div>
					<div>
						<div class="text-muted small text-uppercase fw-semibold">Administracion</div>
						<h5 class="mb-0 fw-bold">Tipo de comprobante</h5>
					</div>
				</div>

				<div class="row g-3">
					<div class="col-12">
						<label class="form-label">Descripcion</label>
						<input type="text" name="descripcion" v-model.trim="campos.descripcion" class="form-control" required autocomplete="off" placeholder="Descripcion">
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Abreviatura</label>
						<input type="text" name="abreviatura" v-model.trim="campos.abreviatura" class="form-control" autocomplete="off" placeholder="Abreviatura">
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Codigo oficial</label>
						<input type="text" name="oficial" v-model.trim="campos.oficial" class="form-control" autocomplete="off" placeholder="Codigo oficial">
					</div>
				</div>

				<div class="phuyu-section-title">Procesos donde estara el comprobante</div>
				<div class="row g-3">
					<div class="col-12 col-md-6">
						<div class="form-check form-switch">
							<input class="form-check-input" v-bind:checked="campos.venta==1" type="checkbox" id="venta" v-on:click="phuyu_proceso(1)">
							<label class="form-check-label" for="venta">Venta</label>
						</div>
					</div>
					<div class="col-12 col-md-6">
						<div class="form-check form-switch">
							<input class="form-check-input" v-bind:checked="campos.compra==1" type="checkbox" id="compra" v-on:click="phuyu_proceso(2)">
							<label class="form-check-label" for="compra">Compra</label>
						</div>
					</div>
					<div class="col-12 col-md-6">
						<div class="form-check form-switch">
							<input class="form-check-input" v-bind:checked="campos.ingreso==1" type="checkbox" id="ingreso" v-on:click="phuyu_proceso(3)">
							<label class="form-check-label" for="ingreso">Ingreso almacen</label>
						</div>
					</div>
					<div class="col-12 col-md-6">
						<div class="form-check form-switch">
							<input class="form-check-input" v-bind:checked="campos.egreso==1" type="checkbox" id="egreso" v-on:click="phuyu_proceso(4)">
							<label class="form-check-label" for="egreso">Salida almacen</label>
						</div>
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

<script> var campos = {codregistro:"",descripcion: "",abreviatura:"",oficial:"",venta:0,compra:0,ingreso:0,egreso:0}; </script>
<script src="<?php echo base_url();?>phuyu/phuyu_form.js"></script>
