<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<style>
#phuyu_operacion .phuyu-card-linea {
	border-radius: 16px;
}

#phuyu_operacion .form-label {
	font-size: 11px;
	font-weight: 700;
	text-transform: uppercase;
	color: #6c757d;
	margin-bottom: 4px;
}

#phuyu_operacion .form-control,
#phuyu_operacion .form-select,
#phuyu_operacion .select2-container--bootstrap4 .select2-selection {
	border-radius: 10px;
	min-height: 38px;
}

#phuyu_operacion .phuyu-action-btn {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	gap: 6px;
	border-radius: 10px;
	font-weight: 700;
	white-space: nowrap;
}

#phuyu_operacion .phuyu-section {
	padding: 1rem;
	border: 1px solid rgba(64, 81, 137, .10);
	border-radius: 12px;
	background: #f8fafc;
}

#phuyu_operacion .phuyu-select-wrap .select2-container {
	width: 100% !important;
}

#phuyu_operacion .phuyu-select-wrap .select2-selection--single {
	display: flex !important;
	align-items: center !important;
	border-color: rgba(64, 81, 137, .16) !important;
	border-radius: 10px !important;
	height: 40px !important;
}

#phuyu_operacion .phuyu-select-wrap .select2-selection__rendered {
	line-height: 40px !important;
	padding-left: 12px !important;
}

#phuyu_operacion .phuyu-btn-icon-only {
	width: 40px;
	height: 40px;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	padding: 0;
}

#phuyu_operacion .phuyu-check-card {
	min-height: 70px;
	border: 1px solid #e9ecef;
	border-radius: 12px;
	display: flex;
	align-items: center;
	justify-content: center;
	flex-direction: column;
	gap: 6px;
}

@media (max-width: 768px) {
	#phuyu_operacion .phuyu-action-btn {
		width: 100%;
	}
}
</style>

<div id="phuyu_operacion" class="phuyu-velzon-form phuyu-ventas-velzon">
	<div class="phuyu-page-title">
		<span class="phuyu-page-icon"><i class="bi bi-credit-card-2-front"></i></span>
		<div>
			<div class="text-muted small text-uppercase fw-semibold">Ventas</div>
			<h4 class="mb-1">Nueva línea de crédito</h4>
			<p class="text-muted mb-0">Condiciones de crédito, cuotas e información del socio.</p>
		</div>
	</div>

	<form id="formulario" v-on:submit.prevent="phuyu_guardar()">
		<div class="phuyu_body">
			<div class="card border-0 shadow-sm phuyu-card-linea">
				<div class="card-header bg-transparent border-bottom">
					<div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
						<div>
							<h4 class="mb-1 fw-semibold">
								<i class="bi bi-credit-card-2-front me-1 text-primary"></i>
								Línea de Crédito
							</h4>
							<div class="text-muted small">Registro y edición de condiciones de crédito por socio</div>
						</div>

						<span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle">
							<i class="bi bi-pencil-square me-1"></i>
							Formulario
						</span>
					</div>
				</div>

				<div class="card-body">
					<div class="row g-3">
						<div class="col-12 col-lg-4">
							<div class="phuyu-section">
								<label class="form-label">
									<i class="bi bi-person me-1"></i> Socio de la línea
								</label>
								<div class="d-flex gap-2 align-items-start">
									<div class="phuyu-select-wrap flex-grow-1">
										<select class="form-select" name="codsocio" id="codsocio" required v-on:change="phuyu_infosocio()"></select>
									</div>
									<button type="button" class="btn btn-primary phuyu-btn-icon-only flex-shrink-0" v-on:click="phuyu_addcliente()" title="Agregar cliente" data-bs-toggle="tooltip">
										<i class="bi bi-person-plus"></i>
									</button>
								</div>
							</div>
						</div>

						<div class="col-12 col-lg-4">
							<label class="form-label">
								<i class="bi bi-card-text me-1"></i> Descripción
							</label>
							<input type="text" class="form-control" v-model.trim="campos.cliente" autocomplete="off" required>
						</div>

						<div class="col-12 col-md-6 col-lg-2">
							<label class="form-label">
								<i class="bi bi-house-check me-1"></i> Tipo posesión
							</label>
							<select class="form-select" name="tipoposesion" v-model="campos.tipoposesion" required>
								<option value="">SELECCIONE</option>
								<option value="0">PROPIA</option>
								<option value="1">ALQUILADA</option>
								<option value="2">ALQUILER COMPRA</option>
							</select>
						</div>

						<div class="col-12 col-md-6 col-lg-2">
							<label class="form-label">
								<i class="bi bi-map me-1"></i> Departamento
							</label>
							<select class="form-select" name="departamento" v-model="campos.departamento" required v-on:change="phuyu_provincias()">
								<option value="">SELECCIONE</option>
								<?php foreach ($departamentos as $key => $value) { ?>
									<option value="<?php echo $value['ubidepartamento'];?>"><?php echo $value["departamento"];?></option>
								<?php } ?>
							</select>
						</div>

						<div class="col-12 col-md-4">
							<label class="form-label">
								<i class="bi bi-geo-alt me-1"></i> Provincia
							</label>
							<select class="form-select" name="provincia" v-model="campos.provincia" id="provincia" required v-on:change="phuyu_distritos()">
								<option value="">SELECCIONE</option>
							</select>
						</div>

						<div class="col-12 col-md-4">
							<label class="form-label">
								<i class="bi bi-pin-map me-1"></i> Distrito
							</label>
							<select class="form-select" name="codubigeo" v-model="campos.codubigeo" id="codubigeo" required v-on:change="phuyu_zonas()">
								<option value="">SELECCIONE</option>
							</select>
						</div>

						<div class="col-12 col-md-4">
							<label class="form-label">
								<i class="bi bi-signpost-split me-1"></i> Zona
							</label>
							<div class="input-group">
								<select class="form-select" name="codzona" v-model="campos.codzona" id="codzona" required>
									<option value="">SELECCIONE</option>
								</select>
								<button type="button" class="btn btn-success" title="Agregar nueva zona" data-bs-toggle="tooltip" v-on:click="phuyu_nuevo_zona()">
									<i class="bi bi-plus-circle"></i>
								</button>
							</div>
						</div>

						<div class="col-12 col-lg-5">
							<label class="form-label">
								<i class="bi bi-geo me-1"></i> Dirección
							</label>
							<input type="text" class="form-control" id="direccion" v-model.trim="campos.direccion" autocomplete="off" maxlength="250" placeholder="Dirección del cliente" required>
						</div>

						<div class="col-6 col-lg-2">
							<label class="form-label">
								<i class="bi bi-percent me-1"></i> T. interés
							</label>
							<input type="number" step="0.01" class="form-control" v-model.trim="campos.tasainteres" required>
						</div>

						<div class="col-6 col-lg-2">
							<label class="form-label">
								<i class="bi bi-cash-stack me-1"></i> Crédito máx.
							</label>
							<input type="number" step="0.01" class="form-control" v-model.trim="campos.creditomaximo" required>
						</div>

						<div class="col-12 col-lg-3">
							<label class="form-label">
								<i class="bi bi-person-check me-1"></i> Garante
							</label>
							<div class="phuyu-select-wrap">
								<select class="form-select" name="codsocioreferencia" id="codsocioreferencia" v-on:change="phuyu_infosocioref()"></select>
							</div>
						</div>

						<div class="col-6 col-lg-2">
							<label class="form-label">
								<i class="bi bi-calendar-event me-1"></i> Fecha inicio
							</label>
							<input type="date" class="form-control" id="fechainicio" value="<?php echo date("Y-m-d");?>">
						</div>

						<div class="col-6 col-lg-2">
							<label class="form-label">
								<i class="bi bi-calendar-check me-1"></i> Fecha fin
							</label>
							<input type="date" class="form-control" id="fechafin" value="<?php echo date("Y-m-d");?>">
						</div>

						<div class="col-12 col-lg-3">
							<label class="form-label">
								<i class="bi bi-person-badge me-1"></i> Sectorista
							</label>
							<select class="form-select" name="codempleado" v-model="campos.codempleado" required>
								<option value="">SELECCIONE</option>
								<?php foreach ($empleados as $key => $value) { ?>
									<option value="<?php echo $value["codpersona"];?>"><?php echo $value["razonsocial"];?></option>
								<?php } ?>
							</select>
						</div>

						<div class="col-6 col-lg-2">
							<label class="form-label">
								<i class="bi bi-aspect-ratio me-1"></i> Área
							</label>
							<input type="number" step="0.01" class="form-control" v-model.trim="campos.area" required>
						</div>

						<div class="col-6 col-lg-1">
							<div class="phuyu-check-card">
								<label class="form-label mb-0">Comprado</label>
								<input type="checkbox" class="form-check-input" style="height:20px;width:20px;" v-model="campos.comprado" name="comprado" id="comprado">
							</div>
						</div>

						<div class="col-12 col-lg-2">
							<label class="form-label">
								<i class="bi bi-shield-check me-1"></i> Estado
							</label>
							<select class="form-select" name="estado" v-model="campos.estado" disabled>
								<option value="0">NORMAL</option>
								<option value="1">LIQUIDADO</option>
								<option value="2">ANULADO</option>
							</select>
						</div>

						<div class="col-12">
							<label class="form-label">
								<i class="bi bi-chat-left-text me-1"></i> Observaciones
							</label>
							<textarea class="form-control" rows="3" v-model.trim="campos.observaciones"></textarea>
						</div>
					</div>
				</div>

				<div class="card-footer bg-transparent border-top">
					<div class="d-flex flex-wrap gap-2 justify-content-end">
						<button type="button" class="btn btn-warning phuyu-action-btn" v-on:click="phuyu_venta()">
							<i class="bi bi-plus-circle"></i>
							Nueva línea
						</button>
						<button type="submit" class="btn btn-info text-white phuyu-action-btn" v-bind:disabled="estado==1">
							<i class="bi bi-save"></i>
							Guardar
						</button>
						<button type="button" class="btn btn-danger phuyu-action-btn" v-on:click="phuyu_atras()">
							<i class="bi bi-arrow-left-circle"></i>
							Atrás
						</button>
					</div>
				</div>
			</div>
		</div>
	</form>

	<div id="modal_zonas" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content border-0 shadow">
				<div class="modal-header">
					<h5 class="modal-title mb-0">
						<i class="bi bi-signpost-split me-1 text-success"></i>
						Registrar Nueva Zona
					</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body" id="zonas_modal"></div>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_lineascredito/nuevo.js"></script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas_2.js"></script>

<script>
	if (typeof bootstrap !== 'undefined') {
		document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
			bootstrap.Tooltip.getOrCreateInstance(el);
		});
	}
</script>
