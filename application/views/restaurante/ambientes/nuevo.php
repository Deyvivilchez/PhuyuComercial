<style>
	#phuyu_form.phuyu-restobar-form .phuyu-card {
		border: 1px solid rgba(10, 132, 117, .12);
		border-radius: .9rem;
		box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
	}

	#phuyu_form.phuyu-restobar-form .phuyu-form-header {
		display: flex;
		align-items: center;
		gap: .75rem;
		padding-bottom: 1rem;
		margin-bottom: 1rem;
		border-bottom: 1px solid rgba(10, 132, 117, .12);
	}

	#phuyu_form.phuyu-restobar-form .phuyu-form-icon {
		width: 40px;
		height: 40px;
		border-radius: 12px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: rgba(10, 132, 117, .10);
		color: #0a8475;
		font-size: 1.15rem;
	}

	#phuyu_form.phuyu-restobar-form .form-label {
		font-size: .74rem;
		font-weight: 800;
		text-transform: uppercase;
		letter-spacing: .03em;
		color: #495057;
		margin-bottom: .4rem;
	}

	#phuyu_form.phuyu-restobar-form .form-control,
	#phuyu_form.phuyu-restobar-form .form-select {
		min-height: 40px;
		border-color: rgba(10, 132, 117, .18);
	}

	#phuyu_form.phuyu-restobar-form .phuyu-form-actions {
		display: flex;
		justify-content: flex-end;
		gap: .65rem;
		padding-top: 1rem;
		margin-top: 1rem;
		border-top: 1px solid rgba(10, 132, 117, .12);
	}
</style>

<div id="phuyu_form" class="phuyu-restobar-form">
	<form id="formulario" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" name="codregistro" v-model="campos.codregistro">

		<div class="card phuyu-card">
			<div class="card-body">
				<div class="phuyu-form-header">
					<div class="phuyu-form-icon"><i class="bi bi-shop"></i></div>
					<div>
						<div class="text-muted small text-uppercase fw-semibold">Restobar</div>
						<h5 class="mb-0 fw-bold">Ambiente</h5>
					</div>
				</div>

				<div class="row g-3">
					<div class="col-12">
						<label class="form-label">Sucursal</label>
						<select class="form-select" name="codsucursal" v-model="campos.codsucursal" required>
							<option value="">SELECCIONE</option>
							<?php foreach ($sucursales as $value) { ?>
								<option value="<?php echo $value["codsucursal"];?>"><?php echo $value["descripcion"];?></option>
							<?php } ?>
						</select>
					</div>

					<div class="col-12">
						<label class="form-label">Descripcion ambiente</label>
						<input type="text" name="descripcion" v-model.trim="campos.descripcion" class="form-control" required autocomplete="off" placeholder="Descripcion">
					</div>

					<div class="col-12">
						<label class="form-label">Aforo</label>
						<input type="number" name="aforo" v-model.trim="campos.aforo" class="form-control" required autocomplete="off" min="1" placeholder="Aforo">
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

<script> var campos = {codregistro:"",codsucursal:"",descripcion: "",aforo: ""}; </script>
<script src="<?php echo base_url();?>phuyu/phuyu_form.js"></script>
