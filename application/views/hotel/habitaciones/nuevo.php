<div id="phuyu_form">
	<form id="formulario" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" v-model="campos.codregistro">
		<div class="card">
			<div class="card-body">
				<h5 class="mb-3"><i class="ri-home-8-line me-1"></i> Habitacion</h5>
				<div class="row g-3">
					<div class="col-md-6">
						<label class="form-label">Ambiente / piso</label>
						<select class="form-select" v-model="campos.codambiente" required>
							<option value="">SELECCIONE</option>
							<?php foreach ($ambientes as $ambiente) { ?>
								<option value="<?php echo $ambiente["codambiente"];?>"><?php echo $ambiente["descripcion"];?></option>
							<?php } ?>
						</select>
					</div>
					<div class="col-md-6">
						<label class="form-label">Tipo</label>
						<select class="form-select" v-model="campos.codhabitaciontipo" required>
							<option value="">SELECCIONE</option>
							<?php foreach ($tipos as $tipo) { ?>
								<option value="<?php echo $tipo["codhabitaciontipo"];?>"><?php echo $tipo["descripcion"];?></option>
							<?php } ?>
						</select>
					</div>
					<div class="col-md-6">
						<label class="form-label">Numero</label>
						<input class="form-control" v-model.trim="campos.numero" required maxlength="20">
					</div>
					<div class="col-md-3">
						<label class="form-label">Capacidad</label>
						<input type="number" class="form-control" v-model.number="campos.capacidad" min="1" required>
					</div>
					<div class="col-md-3">
						<label class="form-label">Precio base</label>
						<input type="number" step="0.01" class="form-control" v-model.number="campos.preciobase" min="0" required>
					</div>
					<div class="col-md-6">
						<label class="form-label">Estado</label>
						<select class="form-select" v-model="campos.situacion" required>
							<option value="1">DISPONIBLE</option>
							<option value="2">RESERVADA</option>
							<option value="3">OCUPADA</option>
							<option value="4">LIMPIEZA</option>
							<option value="5">MANTENIMIENTO</option>
							<option value="6">BLOQUEADA</option>
						</select>
					</div>
					<div class="col-12">
						<label class="form-label">Caracteristicas</label>
						<div class="row g-2">
							<?php foreach ($caracteristicas as $caracteristica) { ?>
								<div class="col-md-4 col-sm-6">
									<div class="form-check">
										<input class="form-check-input" type="checkbox" v-model="campos.caracteristicas" v-bind:value="<?php echo (int)$caracteristica["codcaracteristica"];?>" id="caracteristica_<?php echo (int)$caracteristica["codcaracteristica"];?>">
										<label class="form-check-label" for="caracteristica_<?php echo (int)$caracteristica["codcaracteristica"];?>">
											<i class="<?php echo htmlspecialchars($caracteristica["icono"] ?: 'ri-checkbox-circle-line');?> me-1"></i><?php echo htmlspecialchars($caracteristica["descripcion"]);?>
										</label>
									</div>
								</div>
							<?php } ?>
							<?php if (empty($caracteristicas)) { ?>
								<div class="col-12 text-muted small">No hay caracteristicas registradas.</div>
							<?php } ?>
						</div>
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
var campos = {codregistro:"", codambiente:"", codhabitaciontipo:"", numero:"", piso:"", capacidad:1, preciobase:0, situacion:1, caracteristicas:[]};
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_form.js"></script>
