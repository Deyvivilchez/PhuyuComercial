<div id="phuyu_form" class="phuyu-proveedor-form">
	<form id="formulario" class="phuyu-form-card" v-on:submit.prevent="phuyu_guardar_1()">
		<input type="hidden" name="codsociotipo" v-model="campos.codsociotipo">

		<div class="phuyu-form-header">
			<div class="phuyu-form-icon"><i class="bi bi-person-plus"></i></div>
			<div>
				<div class="text-muted small text-uppercase fw-semibold">Registro rapido</div>
				<h5 class="mb-0 fw-bold">Proveedor</h5>
			</div>
		</div>

		<div class="row g-3">
			<div class="col-md-4 col-12">
				<label class="form-label">Tipo documento</label>
				<select class="form-select" name="coddocumentotipo" v-model="campos.coddocumentotipo" required v-on:change="phuyu_tipodocumento()" ref="coddocumentotipo">
					<option value="">SELECCIONE</option>
					<?php foreach ($tipodocumentos as $value) { ?>
						<option value="<?php echo $value['coddocumentotipo']; ?>"><?php echo $value["descripcion"]; ?></option>
					<?php } ?>
				</select>
			</div>

			<div class="col-md-4 col-12">
				<label class="form-label">Documento</label>
				<input type="text" class="form-control line-danger" name="documento" v-model="campos.documento" id="documento" placeholder="Numero" required autocomplete="off" minlength="8" maxlength="8" ref="documento">
			</div>

			<div class="col-md-4 col-12 d-flex align-items-end">
				<button type="button" class="btn btn-primary w-100 btn-consultar" v-on:click="phuyu_consultar();">
					<i class="bi bi-search me-1"></i> Consultar
				</button>
			</div>

			<div class="col-12">
				<label class="form-label">Razon social</label>
				<input type="text" class="form-control" name="razonsocial" v-model="campos.razonsocial" placeholder="Razon social" required autocomplete="off">
			</div>

			<div class="col-12">
				<label class="form-label">Nombre comercial</label>
				<input type="text" class="form-control" name="nombrecomercial" v-model="campos.nombrecomercial" placeholder="Nombre comercial" autocomplete="off">
			</div>

			<div class="col-12">
				<label class="form-label">Direccion</label>
				<input type="text" class="form-control" name="direccion" v-model="campos.direccion" placeholder="Direccion" required autocomplete="off">
			</div>

			<div class="col-md-6 col-12">
				<label class="form-label">Email</label>
				<input type="text" class="form-control" name="email" v-model="campos.email" placeholder="Email" autocomplete="off">
			</div>

			<div class="col-md-6 col-12">
				<label class="form-label">Telefono / Celular</label>
				<input type="number" class="form-control" name="telefono" v-model="campos.telefono" placeholder="Telf./Cel." autocomplete="off">
			</div>
		</div>

		<div class="phuyu-form-actions">
			<button type="submit" class="btn btn-success" v-bind:disabled="estado==1">
				<i class="bi bi-save me-1"></i> Guardar
			</button>
			<button type="button" class="btn btn-light" v-on:click="phuyu_cerrar()">
				<i class="bi bi-x-lg me-1"></i> Cerrar
			</button>
		</div>
	</form>

	<div id="modal_consultar" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-sm">
			<div class="modal-content phuyu-modal-card">
				<div class="modal-header">
					<h5 class="modal-title fw-bold">Consultar datos</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>
				<div class="modal-body text-center">
					<p class="text-muted mb-3">Seleccione la fuente de consulta.</p>
					<button type="button" class="btn btn-warning w-100 mb-2 btn-consultar" v-show="campos.coddocumentotipo==2" v-on:click="phuyu_consultando()">
						<i class="bi bi-person-badge me-1"></i> RENIEC o ESSALUD
					</button>
					<button type="button" class="btn btn-warning w-100 mb-2 btn-consultar" v-show="campos.coddocumentotipo==4" v-on:click="phuyu_consultando()">
						<i class="bi bi-building-check me-1"></i> SUNAT
					</button>
					<button type="button" class="btn btn-light w-100" data-bs-dismiss="modal">
						<i class="bi bi-x-lg me-1"></i> Cerrar
					</button>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
	#phuyu_form.phuyu-proveedor-form {
		padding: .25rem;
	}

	#phuyu_form .phuyu-form-card {
		background: #fff;
		border: 1px solid rgba(64, 81, 137, .12);
		border-radius: .9rem;
		padding: 1.25rem;
		box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
	}

	#phuyu_form .phuyu-form-header {
		display: flex;
		align-items: center;
		gap: .75rem;
		padding-bottom: 1rem;
		margin-bottom: 1rem;
		border-bottom: 1px solid rgba(64, 81, 137, .10);
	}

	#phuyu_form .phuyu-form-icon {
		width: 44px;
		height: 44px;
		border-radius: 12px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: rgba(64, 81, 137, .10);
		color: #405189;
		font-size: 1.25rem;
	}

	#phuyu_form .form-label {
		font-size: .76rem;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: .03em;
		color: #495057;
		margin-bottom: .4rem;
	}

	#phuyu_form .form-control,
	#phuyu_form .form-select {
		border-color: rgba(64, 81, 137, .16);
		min-height: 40px;
	}

	#phuyu_form .phuyu-form-actions {
		display: flex;
		justify-content: flex-end;
		gap: .65rem;
		padding-top: 1rem;
		margin-top: 1rem;
		border-top: 1px solid rgba(64, 81, 137, .10);
	}

	#phuyu_form .phuyu-modal-card {
		border: 0;
		border-radius: .9rem;
		box-shadow: 0 24px 70px rgba(15, 23, 42, .22);
	}

	@media (max-width: 575.98px) {
		#phuyu_form .phuyu-form-card {
			padding: 1rem;
		}

		#phuyu_form .phuyu-form-actions {
			flex-direction: column;
		}

		#phuyu_form .phuyu-form-actions .btn {
			width: 100%;
		}
	}
</style>

<script>
	var campos = {codsociotipo: "2",coddocumentotipo: "2",documento: "",razonsocial: "",nombrecomercial:"",direccion: "",email: "",telefono: ""};
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas_1.js"></script>
