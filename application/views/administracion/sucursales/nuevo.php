<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_form" class="phuyu-velzon-form">
	<form id="formulario" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" name="codregistro" v-model="campos.codregistro">

		<div class="card phuyu-card">
			<div class="card-body">
				<div class="phuyu-form-title">
					<div class="phuyu-form-icon"><i class="bi bi-building"></i></div>
					<div>
						<div class="text-muted small text-uppercase fw-semibold">Administracion</div>
						<h5 class="mb-0 fw-bold">Registro de sucursal</h5>
					</div>
				</div>

				<div class="row g-3">
					<div class="col-12">
						<label class="form-label">Descripcion sucursal</label>
						<input type="text" name="descripcion" v-model.trim="campos.descripcion" class="form-control" required autocomplete="off" placeholder="Descripcion">
					</div>

					<div class="col-12">
						<label class="form-label">Direccion sucursal</label>
						<input type="text" name="direccion" v-model="campos.direccion" class="form-control" required autocomplete="off" placeholder="Direccion">
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Departamento</label>
						<select class="form-select" name="departamento" v-model="campos.departamento" required v-on:change="phuyu_provincias()">
							<option value="">SELECCIONE</option>
							<?php foreach ($departamentos as $key => $value) { ?>
								<option value="<?php echo $value['ubidepartamento'];?>"><?php echo $value["departamento"];?></option>
							<?php } ?>
						</select>
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Provincia</label>
						<select class="form-select" name="provincia" v-model="campos.provincia" id="provincia" required v-on:change="phuyu_distritos()">
							<option value="">SELECCIONE</option>
						</select>
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Distrito</label>
						<select class="form-select" name="codubigeo" v-model="campos.codubigeo" id="codubigeo" required>
							<option value="">SELECCIONE</option>
						</select>
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Estado credito</label>
						<select class="form-select" v-model="campos.creditoprogramado" required>
							<option value="">Seleccione</option>
							<option value="0">NO PROGRAMADO</option>
							<option value="1">SI PROGRAMADO</option>
						</select>
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Telefono sucursal</label>
						<input type="text" name="telefonos" v-model="campos.telefonos" class="form-control" autocomplete="off" placeholder="Telefonos" maxlength="50">
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Es sucursal principal</label>
						<select name="principal" v-model="campos.principal" class="form-select" required>
							<option value="0">NO ES SUCURSAL PRINCIPAL</option>
							<option value="1">SI ES SUCURSAL PRINCIPAL</option>
						</select>
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Tipo de despacho</label>
						<select name="coddespachotipo" v-model="campos.coddespachotipo" class="form-select" required>
							<option value="1">DESPACHO DIRECTO</option>
							<option value="0">DESPACHO POSTERIOR</option>
						</select>
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Rubro orientado</label>
						<select name="codrubro" v-model="campos.codrubro" class="form-select" required>
							<option value="">SELECCIONE</option>
							<?php foreach ($rubros as $key => $value) { ?>
								<option value="<?php echo $value["codrubro"]?>"><?php echo $value["descripcion"]?></option>
							<?php } ?>
						</select>
					</div>
				</div>

				<div class="phuyu-section-title">Comprobante de ventas por defecto</div>
				<div class="row g-3">
					<div class="col-12 col-md-7">
						<label class="form-label">Tipo comprobante</label>
						<select name="codcomprobantetipo" v-model="campos.codcomprobantetipo" class="form-select">
							<option value="0">SIN COMPROBANTE POR DEFECTO</option>
							<?php foreach ($comprobantes as $key => $value) { ?>
								<option value="<?php echo $value['codcomprobantetipo'];?>"><?php echo $value["descripcion"];?></option>
							<?php } ?>
						</select>
					</div>

					<div class="col-12 col-md-5">
						<label class="form-label">Serie comprobante</label>
						<input type="text" name="seriecomprobante" v-model="campos.seriecomprobante" class="form-control text-uppercase" autocomplete="off" minlength="4" maxlength="4">
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
var campos = {codregistro:"",descripcion: "",direccion: "",telefonos: "",codrubro:"",ventaconproforma:0,ventaconpedido:0,principal: 0,codcomprobantetipo:"0",seriecomprobante:"",departamento: "",provincia: "",codubigeo: "", coddespachotipo:1, lineas: [],creditoprogramado:""}; </script>
<script src="<?php echo base_url();?>phuyu/phuyu_form.js"></script>
