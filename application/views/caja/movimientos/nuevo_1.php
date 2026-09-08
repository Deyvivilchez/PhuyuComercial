<?php
	$configuracion_error = isset($configuracion_error) ? $configuracion_error : "";
	$requiere_servicio = ((int)$codkardex !== 0);
	$servicios_disponibles = count($productos) > 0;
	$form_bloqueado = ($configuracion_error !== "");
	$campos_movimiento = [
		"codkardex" => (string)$codkardex,
		"codproducto" => "",
		"codregistro" => "",
		"tipomovimiento" => (string)$tipomovimiento,
		"codcomprobantetipo" => (string)$comprobante_caja[0]["codcomprobantetipo"],
		"seriecomprobante" => (string)$series[0]["seriecomprobante"],
		"codconcepto" => "",
		"codpersona" => "",
		"codcomprobantetipo_ref" => "0",
		"seriecomprobante_ref" => "",
		"nrocomprobante_ref" => "",
		"codtipopago" => "",
		"importe" => "",
		"fechadocbanco" => "",
		"nrodocbanco" => "",
		"referencia" => "",
		"codcaja_ref" => "",
		"cliente" => ""
	];
?>

<div id="phuyu_movimiento" class="phuyu-caja-egreso">
	<form id="formulario" class="phuyu-caja-card" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" name="codregistro" v-model="campos.codregistro">
		<input type="hidden" name="codkardex" v-model="campos.codkardex">
		<input type="hidden" name="tipomovimiento" v-model="campos.tipomovimiento">
		<input type="hidden" name="codcomprobantetipo" v-model="campos.codcomprobantetipo">
		<input type="hidden" name="seriecomprobante" v-model="campos.seriecomprobante">

		<div class="phuyu-caja-header" v-bind:class="campos.tipomovimiento==2 ? 'is-egreso' : 'is-ingreso'">
			<div class="phuyu-caja-icon">
				<i v-if="campos.tipomovimiento==1" class="bi bi-cash-stack"></i>
				<i v-if="campos.tipomovimiento==2" class="bi bi-wallet2"></i>
			</div>
			<div>
				<div class="text-uppercase text-muted small fw-semibold">Movimiento de caja</div>
				<h5 class="mb-0 fw-bold" v-if="campos.tipomovimiento==1">Registrar ingreso a caja</h5>
				<h5 class="mb-0 fw-bold text-danger" v-if="campos.tipomovimiento==2">Registrar egreso a caja</h5>
			</div>
		</div>

		<?php if ($configuracion_error !== "") { ?>
			<div class="alert alert-danger d-flex align-items-start gap-2 mb-3" role="alert">
				<i class="bi bi-exclamation-triangle-fill fs-5"></i>
				<div><?php echo $configuracion_error; ?></div>
			</div>
		<?php } ?>

		<?php if ($requiere_servicio && !$servicios_disponibles) { ?>
			<div id="phuyu-servicios-alerta" class="alert alert-warning d-flex align-items-start gap-2 mb-3" role="alert">
				<i class="bi bi-info-circle-fill fs-5"></i>
				<div>No hay servicios activos para registrar el egreso. Puede crear uno aquí mismo y continuar con el registro.</div>
			</div>
		<?php } ?>

		<div class="row g-3">
			<?php if ($requiere_servicio) { ?>
				<div class="col-12">
					<label class="form-label">Seleccionar servicio <span class="text-danger">*</span></label>
					<div class="input-group">
						<span class="input-group-text"><i class="bi bi-briefcase"></i></span>
						<select class="form-select" name="codproducto" v-model="campos.codproducto" required <?php echo !$servicios_disponibles ? "disabled" : ""; ?>>
							<option value=""><?php echo $servicios_disponibles ? "SELECCIONE" : "SIN SERVICIOS CONFIGURADOS"; ?></option>
							<?php foreach ($productos as $value) { ?>
								<option value="<?php echo $value["codproducto"]; ?>"><?php echo $value["descripcion"]; ?></option>
							<?php } ?>
						</select>
						<button type="button" class="btn btn-primary" v-on:click="mostrarServicio = !mostrarServicio">
							<i class="bi bi-plus-lg"></i>
						</button>
					</div>
					<div class="phuyu-servicio-rapido mt-2" v-show="mostrarServicio">
						<div class="input-group">
							<span class="input-group-text"><i class="bi bi-tools"></i></span>
							<input type="text" class="form-control" v-model.trim="nuevoServicio.descripcion" maxlength="100" placeholder="Nuevo servicio">
							<button type="button" class="btn btn-success" v-on:click="phuyu_guardarservicio()" v-bind:disabled="registrandoServicio==1">
								<span v-if="registrandoServicio==0"><i class="bi bi-check2 me-1"></i> Agregar</span>
								<span v-if="registrandoServicio==1"><span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span></span>
							</button>
						</div>
					</div>
				</div>
			<?php } ?>

			<div class="col-12">
				<label class="form-label">Concepto caja <span class="text-danger">*</span></label>
				<div class="input-group">
					<span class="input-group-text"><i class="bi bi-tags"></i></span>
					<select class="form-select" name="codconcepto" v-model="campos.codconcepto" required>
						<option value="">SELECCIONE</option>
						<?php foreach ($conceptos as $value) { ?>
							<option value="<?php echo $value["codconcepto"]; ?>"><?php echo $value["descripcion"]; ?></option>
						<?php } ?>
					</select>
				</div>
			</div>

			<div class="col-12">
				<label class="form-label">Socio del movimiento <span class="text-danger">*</span></label>
				<div class="input-group phuyu-socio-group">
					<span class="input-group-text"><i class="bi bi-person-vcard"></i></span>
					<select class="form-select" name="codpersona" id="codpersona" required>
						<option value=""></option>
					</select>
				</div>
			</div>

			<div class="col-md-6 col-12">
				<label class="form-label">Tipo pago <span class="text-danger">*</span></label>
				<div class="input-group">
					<span class="input-group-text"><i class="bi bi-credit-card-2-front"></i></span>
					<select class="form-select" name="codtipopago" v-model="campos.codtipopago" required v-on:change="phuyu_cajabanco()">
						<option value="">SELECCIONE</option>
						<?php foreach ($tipopagos as $value) { ?>
							<option value="<?php echo $value["codtipopago"]; ?>"><?php echo $value["descripcion"]; ?></option>
						<?php } ?>
					</select>
				</div>
			</div>

			<div class="col-md-6 col-12">
				<label class="form-label">Importe <span class="text-danger">*</span></label>
				<div class="input-group">
					<span class="input-group-text">S/</span>
					<input type="number" step="0.01" class="form-control phuyu-importe" name="importe" id="importe" v-model="campos.importe" placeholder="0.00" required>
				</div>
			</div>

			<div class="col-md-6 col-12" v-show="movimientobanco==1">
				<label class="form-label">Fecha doc. banco <span class="text-danger">*</span></label>
				<div class="input-group">
					<span class="input-group-text"><i class="bi bi-calendar-event"></i></span>
					<input type="text" class="form-control datepicker" name="fechadocbanco" id="fechadocbanco" autocomplete="off" required value="<?php echo date('Y-m-d'); ?>">
				</div>
			</div>

			<div class="col-md-6 col-12" v-show="movimientobanco==1">
				<label class="form-label">Nro documento banco</label>
				<div class="input-group">
					<span class="input-group-text"><i class="bi bi-receipt"></i></span>
					<input type="text" class="form-control" name="nrodocbanco" id="nrodocbanco" v-model="campos.nrodocbanco" placeholder="Documento banco" autocomplete="off">
				</div>
			</div>

			<div class="col-12">
				<label class="form-label">Comprobante referencia</label>
				<div class="input-group">
					<span class="input-group-text"><i class="bi bi-file-earmark-text"></i></span>
					<select class="form-select" name="codcomprobantetipo_ref" v-model="campos.codcomprobantetipo_ref">
						<option value="0">SIN COMPROBANTE DE REFERENCIA</option>
						<?php foreach ($tipocomprobantes as $value) { ?>
							<option value="<?php echo $value["codcomprobantetipo"]; ?>"><?php echo $value["descripcion"]; ?></option>
						<?php } ?>
					</select>
				</div>
			</div>

			<div class="col-md-4 col-12">
				<label class="form-label">Serie ref.</label>
				<input type="text" class="form-control text-uppercase" name="seriecomprobante_ref" v-model="campos.seriecomprobante_ref" maxlength="4" autocomplete="off">
			</div>

			<div class="col-md-8 col-12">
				<label class="form-label">Nro doc. referencia</label>
				<input type="text" class="form-control" name="nrocomprobante_ref" v-model="campos.nrocomprobante_ref" maxlength="10" autocomplete="off">
			</div>

			<div class="col-12">
				<label class="form-label">Descripción del movimiento <span class="text-danger">*</span></label>
				<textarea class="form-control" name="referencia" v-model="campos.referencia" placeholder="Descripción del movimiento ..." maxlength="200" rows="3" required></textarea>
			</div>
		</div>

		<div class="phuyu-caja-actions">
			<button type="submit" class="btn btn-success" v-bind:disabled="estado==1" <?php echo $form_bloqueado ? "disabled" : ""; ?>>
				<span v-if="estado==0"><i class="bi bi-save me-1"></i> Guardar</span>
				<span v-if="estado==1"><span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Guardando</span>
			</button>
			<button type="button" class="btn btn-light" v-on:click="phuyu_cerrar()">
				<i class="bi bi-x-lg me-1"></i> Cerrar
			</button>
		</div>
	</form>
</div>

<style>
	#phuyu_movimiento.phuyu-caja-egreso {
		padding: .25rem;
	}

	#phuyu_movimiento .phuyu-caja-card {
		background: #fff;
		border: 1px solid rgba(64, 81, 137, .12);
		border-radius: 1rem;
		padding: 1.25rem;
		box-shadow: 0 12px 34px rgba(15, 23, 42, .08);
	}

	#phuyu_movimiento .phuyu-caja-header {
		display: flex;
		align-items: center;
		gap: .85rem;
		padding-bottom: 1rem;
		margin-bottom: 1rem;
		border-bottom: 1px solid rgba(64, 81, 137, .12);
	}

	#phuyu_movimiento .phuyu-caja-icon {
		width: 46px;
		height: 46px;
		border-radius: 14px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: rgba(64, 81, 137, .10);
		color: #405189;
		font-size: 1.35rem;
		flex: 0 0 auto;
	}

	#phuyu_movimiento .phuyu-caja-header.is-egreso .phuyu-caja-icon {
		background: rgba(239, 68, 68, .10);
		color: #dc3545;
	}

	#phuyu_movimiento .form-label {
		font-size: .76rem;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: .03em;
		color: #495057;
		margin-bottom: .4rem;
	}

	#phuyu_movimiento .input-group-text {
		background: #f8fafc;
		border-color: rgba(64, 81, 137, .16);
		color: #405189;
	}

	#phuyu_movimiento .form-control,
	#phuyu_movimiento .form-select,
	#phuyu_movimiento .select2-container--bootstrap4 .select2-selection {
		border-color: rgba(64, 81, 137, .16);
		min-height: 40px;
	}

	#phuyu_movimiento .phuyu-socio-group {
		flex-wrap: nowrap;
	}

	#phuyu_movimiento .phuyu-socio-group .select2-container {
		display: block;
		flex: 1 1 auto;
		width: 1% !important;
		min-width: 0;
	}

	#phuyu_movimiento .phuyu-socio-group .select2-container .select2-selection--single {
		min-height: 40px;
		border-color: rgba(64, 81, 137, .16);
		border-top-left-radius: 0;
		border-bottom-left-radius: 0;
		display: flex;
		align-items: center;
	}

	#phuyu_movimiento .phuyu-socio-group .select2-selection__rendered {
		width: 100%;
		line-height: 38px;
	}

	#phuyu_movimiento .phuyu-socio-group .select2-selection__arrow {
		height: 38px;
	}

	#phuyu_movimiento .phuyu-importe {
		border-color: rgba(220, 53, 69, .4);
		font-weight: 700;
	}

	#phuyu_movimiento .phuyu-servicio-rapido {
		padding: .75rem;
		border: 1px dashed rgba(64, 81, 137, .22);
		border-radius: .75rem;
		background: #f8fafc;
	}

	#phuyu_movimiento .phuyu-caja-actions {
		display: flex;
		justify-content: flex-end;
		gap: .65rem;
		padding-top: 1rem;
		margin-top: 1rem;
		border-top: 1px solid rgba(64, 81, 137, .10);
	}

	@media (max-width: 575.98px) {
		#phuyu_movimiento .phuyu-caja-card {
			padding: 1rem;
		}

		#phuyu_movimiento .phuyu-caja-actions {
			flex-direction: column;
		}

		#phuyu_movimiento .phuyu-caja-actions .btn {
			width: 100%;
		}
	}
</style>

<script>
	var campos = <?php echo json_encode($campos_movimiento); ?>;
</script>
<script src="<?php echo base_url(); ?>phuyu/phuyu_caja/movi_nuevo_1.js"></script>
<script src="<?php echo base_url(); ?>phuyu/phuyu_caja/selects.js"></script>
