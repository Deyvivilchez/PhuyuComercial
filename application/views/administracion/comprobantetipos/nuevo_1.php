<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div class="row g-3 phuyu-velzon-form" id="phuyu_form_1">
	<div class="col-md-7 col-12 border-end">
		<h5 class="fw-bold mb-1">
			<i class="bi bi-bookmark-plus me-1"></i>
			Registrar nueva marca
		</h5>
		<p class="text-muted mb-3">Si ya está registrada, solo debes buscarla y seleccionarla.</p>

		<form id="formulario" v-on:submit.prevent="phuyu_guardar_1('almacen/marcas')">
			<input type="hidden" id="codigo_extencion" value="codmarca">

			<div class="mb-3">
				<label class="form-label">Descripcion marca</label>
				<input type="text" name="descripcion_extencion" v-model.trim="agregar.descripcion" class="form-control" required autocomplete="off" placeholder="Descripcion" maxlength="100">
			</div>

			<div class="text-center pt-2">
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

	<div class="col-md-5 col-12 d-flex flex-column justify-content-center text-center">
		<i class="bi bi-bookmark-check text-primary" style="font-size:56px;"></i>
		<h6 class="fw-bold mt-3 mb-1">Nueva marca</h6>
		<p class="text-muted mb-0">Registro auxiliar para agrupar productos.</p>
	</div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_form_1.js"></script>
