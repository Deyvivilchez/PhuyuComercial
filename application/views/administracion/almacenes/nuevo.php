<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_form" class="phuyu-velzon-form">
	<form id="formulario" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" name="codregistro" v-model="campos.codregistro">

		<div class="card phuyu-card">
			<div class="card-body">
				<div class="phuyu-form-title">
					<div class="phuyu-form-icon"><i class="bi bi-box-seam"></i></div>
					<div>
						<div class="text-muted small text-uppercase fw-semibold">Administracion</div>
						<h5 class="mb-0 fw-bold">Registro de almacen</h5>
					</div>
				</div>

				<div class="row g-3">
					<div class="col-12">
						<label class="form-label">Sucursal almacen</label>
						<select class="form-select" name="codsucursal" v-model="campos.codsucursal" required>
							<option value="">SELECCIONE</option>
							<?php foreach ($sucursales as $key => $value) { ?>
								<option value="<?php echo $value["codsucursal"];?>"><?php echo $value["descripcion"];?></option>
							<?php } ?>
						</select>
					</div>

					<div class="col-12">
						<label class="form-label">Descripcion almacen</label>
						<input type="text" name="descripcion" v-model.trim="campos.descripcion" class="form-control" required autocomplete="off" placeholder="Descripcion">
					</div>

					<div class="col-12">
						<label class="form-label">Direccion almacen</label>
						<input type="text" name="direccion" v-model="campos.direccion" class="form-control" required autocomplete="off" placeholder="Direccion">
					</div>

					<div class="col-12">
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

					<div class="col-12">
						<label class="form-label">Telefonos almacen</label>
						<input type="number" name="telefonos" v-model.number="campos.telefonos" class="form-control" autocomplete="off" placeholder="Telefonos">
					</div>

					<div class="col-12">
						<label class="form-label">Almacen controla stock</label>
						<select name="controlstock" v-model.number="campos.controlstock" class="form-select">
							<option value="1">SI CONTROLA STOCK</option>
							<option value="0">NO CONTROLA STOCK</option>
						</select>
					</div>

					<div class="col-12">
						<label class="form-label">Afectacion por defecto</label>
						<select name="codafectacionigv" v-model="campos.codafectacionigv" class="form-select" required>
							<option value="">SELECCIONE</option>
							<?php foreach ($afectacionigv as $key => $value) { ?>
								<option value="<?php echo $value["codafectacionigv"]?>"><?php echo $value["descripcion"]?></option>
							<?php } ?>
						</select>
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

<script> var campos = {codregistro:"",codsucursal:"",descripcion: "",direccion: "",telefonos: "",controlstock: 1,departamento: "",provincia: "",codubigeo: "",codafectacionigv:9}; </script>
<script src="<?php echo base_url();?>phuyu/phuyu_form.js"></script>
