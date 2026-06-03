<div id="phuyu_form">
	<form id="formulario" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" v-model="campos.codregistro">
		<div class="card">
			<div class="card-body">
				<h5 class="mb-3"><i class="ri-building-2-line me-1"></i> Ambiente hotel</h5>
				<div class="row g-3">
					<div class="col-12">
						<label class="form-label">Descripcion</label>
						<input class="form-control" v-model.trim="campos.descripcion" required placeholder="1ER PISO / TORRE A / BLOQUE B">
					</div>
					<div class="col-12">
						<label class="form-label">Aforo</label>
						<input type="number" class="form-control" v-model.number="campos.aforo" min="0">
					</div>
				</div>
				<div class="text-end mt-3">
					<button type="submit" class="btn btn-primary" v-bind:disabled="estado==1"><i class="ri-save-line me-1"></i> Guardar</button>
					<button type="button" class="btn btn-light" v-on:click="phuyu_cerrar()">Cerrar</button>
				</div>
			</div>
		</div>
	</form>
</div>
<script>
var campos = {codregistro:"", descripcion:"", aforo:0};
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_form.js"></script>
