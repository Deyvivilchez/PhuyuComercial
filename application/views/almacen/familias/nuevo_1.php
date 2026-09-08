<style>
	#phuyu_form_1.phuyu-almacen-form .form-label {
		font-size: .74rem;
		font-weight: 800;
		text-transform: uppercase;
		letter-spacing: .03em;
		color: #495057;
		margin-bottom: .4rem;
	}

	#phuyu_form_1.phuyu-almacen-form .form-control {
		min-height: 40px;
		border-color: rgba(64, 81, 137, .16);
	}

	#phuyu_form_1.phuyu-almacen-form .phuyu-form-header {
		display: flex;
		align-items: center;
		gap: .65rem;
		margin-bottom: 1rem;
	}

	#phuyu_form_1.phuyu-almacen-form .phuyu-form-icon {
		width: 38px;
		height: 38px;
		border-radius: 12px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: rgba(64, 81, 137, .10);
		color: #405189;
	}

	#phuyu_form_1.phuyu-almacen-form .phuyu-form-actions {
		display: flex;
		justify-content: flex-end;
		gap: .65rem;
		padding-top: 1rem;
		margin-top: 1rem;
		border-top: 1px solid rgba(64, 81, 137, .10);
	}
</style>

<div id="phuyu_form_1" class="phuyu-almacen-form">
	<div class="phuyu-form-header">
		<div class="phuyu-form-icon"><i class="bi bi-collection"></i></div>
		<div>
			<div class="text-muted small text-uppercase fw-semibold">Almacen</div>
			<h5 class="mb-0 fw-bold">Registrar nueva familia</h5>
		</div>
	</div>

	<form id="formulario" v-on:submit.prevent="phuyu_guardar_1('almacen/familias')">
		<input type="hidden" id="codigo_extencion" value="codfamilia">

		<div class="row g-3">
			<div class="col-12">
				<label class="form-label">Descripcion familia</label>
				<input type="text" name="descripcion_extencion" v-model.trim="agregar.descripcion" class="form-control" required autocomplete="off" placeholder="Descripcion" maxlength="100">
			</div>
		</div>

		<div class="phuyu-form-actions">
			<button type="submit" class="btn btn-primary" v-bind:disabled="estado_1==1">
				<i class="bi bi-save me-1"></i> Guardar
			</button>
			<button type="button" class="btn btn-light" data-bs-dismiss="modal">
				<i class="bi bi-x-circle me-1"></i> Cerrar
			</button>
		</div>
	</form>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_form_1.js"></script>
