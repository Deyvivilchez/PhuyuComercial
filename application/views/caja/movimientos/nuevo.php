<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_form" class="phuyu-velzon-form">
	<form id="formulario" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" name="codregistro" v-model="campos.codregistro">

		<div class="card phuyu-card">
			<div class="card-body">
				<div class="phuyu-form-title">
					<div class="phuyu-form-icon"><i class="bi bi-arrow-left-right"></i></div>
					<div>
						<div class="text-muted small text-uppercase fw-semibold">Tesoreria</div>
						<h5 class="mb-0 fw-bold">Movimiento de caja</h5>
					</div>
				</div>

				<div class="row g-3">
					<div class="col-12 col-md-4">
						<label class="form-label">Tipo movimiento</label>
						<select class="form-select" name="tipomovimiento" id="tipomovimiento" v-model="campos.tipomovimiento" required v-on:change="phuyu_tipomovimiento()">
							<option value="1">INGRESO</option>
							<option value="2">EGRESO</option>
						</select>
					</div>

					<div class="col-12 col-md-8">
						<label class="form-label">Comprobante</label>
						<select class="form-select" name="codcomprobantetipo" id="codcomprobantetipo" v-model="campos.codcomprobantetipo" required v-on:change="phuyu_tipopagos()">
							<option value="">SELECCIONE</option>
							<option v-for="dato in comprobantes" v-bind:value="dato.codcomprobantetipo"> {{dato.descripcion}} </option>
						</select>
						<input type="hidden" name="seriecomprobante" v-model="campos.seriecomprobante">
					</div>

					<div class="col-12">
						<label class="form-label">Concepto caja</label>
						<select class="form-select" name="codconcepto" v-model="campos.codconcepto" v-on:change="phuyu_conceptos()" required>
							<option value="">SELECCIONE</option>
							<option v-for="dato in conceptos" v-bind:value="dato.codconcepto"> {{dato.descripcion}} </option>
						</select>
					</div>

					<div class="col-12" v-if="transferencia==1">
						<label class="form-label">Caja destino de la transferencia</label>
						<select class="form-select" name="codcaja_ref" v-model="campos.codcaja_ref">
							<option value="">SELECCIONE CAJA</option>
							<?php foreach ($cajas as $key => $value) { ?>
								<option value="<?php echo $value["codcaja"];?>"> <?php echo "SUCURSAL:".$value["sucursal"]." CAJA:".$value["descripcion"];?> </option>
							<?php } ?>
						</select>
					</div>

					<div class="col-12">
						<label class="form-label">Socio del movimiento</label>
						<select class="form-select" name="codpersona" id="codpersona" required> </select>
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Tipo pago</label>
						<select class="form-select" name="codtipopago" v-model="campos.codtipopago" required v-on:change="phuyu_cajabanco()">
							<option value="">SELECCIONE</option>
							<option v-for="dato in tipopagos" v-bind:value="dato.codtipopago"> {{dato.descripcion}} </option>
						</select>
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Importe</label>
						<input type="number" step="0.01" class="form-control border-danger" name="importe" id="importe" v-model="campos.importe" placeholder="S/. 0.00" required>
					</div>

					<div class="col-12 col-md-6" v-show="movimientobanco==1">
						<label class="form-label">Fecha doc. banco</label>
						<input type="hidden" id="fechadocbanco_ref" value="<?php echo date('Y-m-d');?>">
						<input type="text" class="form-control datepicker" name="fechadocbanco" id="fechadocbanco" v-model="campos.fechadocbanco" autocomplete="off" required v-on:blur="phuyu_fechamovimiento()">
					</div>

					<div class="col-12 col-md-6" v-show="movimientobanco==1">
						<label class="form-label">Nro documento banco</label>
						<input type="text" class="form-control" name="nrodocbanco" id="nrodocbanco" v-model="campos.nrodocbanco" placeholder="Nro documento banco" autocomplete="off">
					</div>

					<div class="col-12">
						<label class="form-label">Comprobante referencia</label>
						<select class="form-select" name="codcomprobantetipo_ref" v-model="campos.codcomprobantetipo_ref">
							<option value="0">SIN COMPROBANTE DE REFERENCIA</option>
							<?php foreach ($tipocomprobantes as $key => $value) { ?>
								<option value="<?php echo $value["codcomprobantetipo"];?>"><?php echo $value["descripcion"];?></option>
							<?php } ?>
						</select>
					</div>

					<div class="col-12 col-md-4">
						<label class="form-label">Serie ref.</label>
						<input type="text" class="form-control text-uppercase" name="seriecomprobante_ref" v-model="campos.seriecomprobante_ref" maxlength="4">
					</div>

					<div class="col-12 col-md-8">
						<label class="form-label">Nro doc. referencia</label>
						<input type="text" class="form-control" name="nrocomprobante_ref" v-model="campos.nrocomprobante_ref" maxlength="10">
					</div>

					<div class="col-12">
						<label class="form-label">Descripcion del movimiento</label>
						<input type="text" class="form-control" name="referencia" v-model="campos.referencia" placeholder="Descripcion del movimiento" maxlength="200">
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

<script>
	var campos = {codregistro:"",tipomovimiento: "1",codcomprobantetipo:"",seriecomprobante:"",codconcepto: "",codpersona: "",codcomprobantetipo_ref:"0",seriecomprobante_ref: "",nrocomprobante_ref: "",codtipopago: "",importe:"",fechadocbanco:"",nrodocbanco:"",referencia:"",codcaja_ref:"",cliente:""};
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_caja/movi_nuevo.js"></script>
<script src="<?php echo base_url();?>phuyu/phuyu_selectsforms.js"> </script>
