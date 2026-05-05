<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_form" class="phuyu-velzon-form">
	<form id="formulario" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" name="codregistro" v-model="campos.codregistro">
		<input type="hidden" v-model="campos.seriecomprobante_editar" name="seriecomprobante_editar">
		<input type="hidden" v-model="campos.codsucursal_editar" name="codsucursal_editar">
		<input type="hidden" v-model="campos.codcomprobantetipo_editar" name="codcomprobantetipo_editar">
		<input type="hidden" v-model="campos.logo" name="logo">
		<input type="hidden" v-model="campos.logoauspiciador" name="logoauspiciador">
		<input type="hidden" v-model="campos.impresion" name="impresion">

		<div class="card phuyu-card">
			<div class="card-body">
				<div class="phuyu-form-title">
					<div class="phuyu-form-icon"><i class="bi bi-file-earmark-text"></i></div>
					<div>
						<div class="text-muted small text-uppercase fw-semibold">Administracion</div>
						<h5 class="mb-0 fw-bold">Serie de comprobante</h5>
					</div>
				</div>

				<div class="row g-3">
					<div class="col-12">
						<label class="form-label">Seleccionar sucursal</label>
						<select class="form-select" id="codsucursal" v-model="campos.codsucursal" name="codsucursal" required v-on:change="phuyu_tipocomprobante()">
							<option value="">SELECCIONE</option>
							<?php foreach ($sucursales as $key => $value) { ?>
								<option value="<?php echo $value["codsucursal"];?>"><?php echo $value["descripcion"];?></option>
							<?php } ?>
						</select>
					</div>

					<div class="col-12">
						<label class="form-label">Tipo comprobante</label>
						<select class="form-select" id="codcomprobantetipo" v-model="campos.codcomprobantetipo" required v-on:change="phuyu_tipocomprobante()" name="codcomprobantetipo">
							<option value="">SELECCIONE</option>
							<?php foreach ($tipos as $key => $value) { ?>
								<option value="<?php echo $value["codcomprobantetipo"];?>"><?php echo $value["descripcion"];?></option>
							<?php } ?>
						</select>
					</div>

					<div class="col-12" v-if="caja">
						<label class="form-label">Seleccione caja</label>
						<select class="form-select" name="codcaja" v-model="campos.codcaja" id="codcaja" required v-on:change="phuyu_caja()">
							<option value="">SELECCIONE</option>
						</select>
						<div class="alert alert-danger mt-2 mb-0" v-if="caja_alerta">
							<strong>Ya tiene registrado una serie para esta caja. Cambiar de caja o tipo comprobante.</strong>
						</div>
					</div>

					<div class="col-12" v-if="almacen">
						<label class="form-label">Seleccione almacen</label>
						<select class="form-select" name="codalmacen" v-model="campos.codalmacen" id="codalmacen" required v-on:change="phuyu_almacen()">
							<option value="">SELECCIONE</option>
						</select>
						<div class="alert alert-danger mt-2 mb-0" v-if="almacen_alerta">
							<strong>Ya tiene registrado una serie para este almacen. Cambiar de almacen o tipo de comprobante.</strong>
						</div>
					</div>

					<div class="col-12" v-if="nota">
						<label class="form-label">Comprobante y serie referencia</label>
						<select class="form-select" name="codcomprobantetipo_ref" v-model="campos.codcomprobantetipo_ref" id="codcomprobantetipo_ref" required v-on:change="phuyu_notas()">
							<option value="">SELECCIONE</option>
						</select>
						<div class="alert alert-danger mt-2 mb-0" v-if="nota_alerta">
							<strong>Ya tiene registrado una serie para esta nota electronica. Cambiar de tipo de comprobante o serie.</strong>
						</div>
					</div>

					<div class="col-12 col-md-4">
						<label class="form-label">Serie</label>
						<input type="text" id="seriecomprobante" v-model.trim="campos.seriecomprobante" class="form-control text-uppercase" required autocomplete="off" minlength="4" maxlength="4" name="seriecomprobante">
					</div>

					<div class="col-12 col-md-4">
						<label class="form-label">Nro inicial</label>
						<input type="number" name="nroinicial" v-model.number="campos.nroinicial" class="form-control" required autocomplete="off" placeholder="Nro inicial">
					</div>

					<div class="col-12 col-md-4">
						<label class="form-label">Correlativo</label>
						<input type="number" name="nrocorrelativo" v-model.number="campos.nrocorrelativo" class="form-control" required autocomplete="off" placeholder="Nro correlativo">
					</div>

					<div class="col-12">
						<div class="form-check form-switch">
							<input class="form-check-input" v-bind:checked="campos.impresion==1" type="checkbox" id="flexSwitchCheckChecked" v-on:click="phuyu_impresion()">
							<label class="form-check-label" for="flexSwitchCheckChecked">Configurar impresion del comprobante</label>
						</div>
					</div>
				</div>

				<div v-show="campos.impresion==1">
					<div class="phuyu-section-title">Impresion</div>
					<div class="row g-3">
						<div class="col-12 col-md-6">
							<label class="form-label">Formato</label>
							<select v-model="campos.formato" class="form-select" name="formato" required>
								<option value="a4">A4</option>
								<option value="a5">A5</option>
								<option value="ticket">TICKET</option>
							</select>
						</div>

						<div class="col-12 col-md-6">
							<label class="form-label">Orientacion</label>
							<select v-model="campos.orientacion" class="form-select" name="orientacion" required>
								<option value="h">HORIZONTAL</option>
								<option value="p">VERTICAL</option>
							</select>
						</div>

						<div class="col-12 col-md-6">
							<label class="form-label">Tipo leyenda</label>
							<select v-model="campos.tipoconleyendaamazonia" class="form-select" name="tipoconleyendaamazonia" required>
								<option value="1">LEYENDA BIENES</option>
								<option value="2">LEYENDA SERVICIOS</option>
								<option value="3">LEYENDA BIENES Y SERVICIOS</option>
							</select>
						</div>

						<div class="col-12 col-md-6">
							<label class="form-label">Impresion logo</label>
							<select class="form-select" v-model="campos.impresionlogo" name="impresionlogo" required>
								<option value="1">PARCIAL</option>
								<option value="2">TOTAL</option>
							</select>
						</div>

						<div class="col-12 col-md-6">
							<label class="form-label">Logo serie</label>
							<input type="file" class="form-control" name="logoa" accept="image/*">
						</div>

						<div class="col-12 col-md-6">
							<label class="form-label">Logo auspiciador</label>
							<input type="file" class="form-control" name="auspiciadora" accept="image/*">
						</div>

						<div class="col-12">
							<label class="form-label">Nombre comercial</label>
							<input type="text" class="form-control" maxlength="100" v-model="campos.nombrecomercial" name="nombrecomercial">
						</div>

						<div class="col-12">
							<label class="form-label">Slogan empresa</label>
							<textarea class="form-control" name="slogan" v-model="campos.slogan" placeholder="Slogan" autocomplete="off" rows="3"></textarea>
						</div>

						<div class="col-12">
							<label class="form-label">Publicidad</label>
							<textarea class="form-control" name="publicidad" v-model="campos.publicidad" placeholder="Publicidad" autocomplete="off" rows="1"></textarea>
						</div>

						<div class="col-12">
							<label class="form-label">Agradecimiento</label>
							<textarea class="form-control" name="agradecimiento" v-model="campos.agradecimiento" placeholder="Agradecimiento" autocomplete="off" rows="1"></textarea>
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

<script>
	var campos = {codregistro:"",codsucursal:"",codcomprobantetipo:"",codcaja:"",codalmacen:"",codcomprobantetipo_ref:"",seriecomprobante:"",seriecomprobante_editar:"",nroinicial:"",nrocorrelativo:"",impresion:0,formato:"a4",orientacion:"p",impresora:"",slogan:"",publicidad:"",agradecimiento:"",logo:"",logoauspiciador:"",tipoconleyendaamazonia:1,nombrecomercial:"",impresionlogo:1};
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_caja/comprobantes.js?v=<?php echo uniqid(); ?>"></script>
