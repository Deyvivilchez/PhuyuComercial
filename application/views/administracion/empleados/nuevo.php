<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_form" class="phuyu-velzon-form">
	<form id="formulario" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" name="codregistro" v-model="campos.codregistro">
		<input type="hidden" name="coddocumentotipo" v-model="campos.coddocumentotipo">

		<div class="card phuyu-card">
			<div class="card-body">
				<div class="phuyu-form-title">
					<div class="phuyu-form-icon"><i class="bi bi-person-lines-fill"></i></div>
					<div>
						<div class="text-muted small text-uppercase fw-semibold">Administracion</div>
						<h5 class="mb-0 fw-bold">Registro de empleado</h5>
					</div>
				</div>

				<div class="row g-3">
					<div class="col-12 col-md-6">
						<label class="form-label">Seleccionar sucursal</label>
						<select class="form-select" name="codsucursal" v-model="campos.codsucursal" required>
							<option value="">SELECCIONE</option>
							<?php foreach ($sucursales as $key => $value) { ?>
								<option value="<?php echo $value['codsucursal'];?>"><?php echo $value["descripcion"];?></option>
							<?php } ?>
						</select>
					</div>

					<div class="col-12 col-md-4">
						<label class="form-label">Nro de DNI</label>
						<input type="number" name="documento" v-model.trim="campos.documento" class="form-control" required autocomplete="off" placeholder="Nro DNI">
					</div>

					<div class="col-12 col-md-2 d-flex align-items-end">
						<button type="button" class="btn btn-primary w-100" v-on:click="phuyu_consultar()">
							<i class="bi bi-search"></i>
						</button>
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Seleccionar area</label>
						<select class="form-select" name="codarea" v-model="campos.codarea" required>
							<option value="">SELECCIONE</option>
							<?php foreach ($areas as $key => $value) { ?>
								<option value="<?php echo $value['codarea'];?>"><?php echo $value["descripcion"];?></option>
							<?php } ?>
						</select>
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Seleccionar cargo</label>
						<select class="form-select" name="codcargo" v-model="campos.codcargo" required>
							<option value="">SELECCIONE</option>
							<?php foreach ($cargos as $key => $value) { ?>
								<option value="<?php echo $value['codcargo'];?>"><?php echo $value["descripcion"];?></option>
							<?php } ?>
						</select>
					</div>

					<div class="col-12">
						<label class="form-label">Nombres completos</label>
						<input type="text" name="razonsocial" v-model.trim="campos.razonsocial" class="form-control" required autocomplete="off" placeholder="Nombres completos">
					</div>

					<div class="col-12">
						<label class="form-label">Direccion</label>
						<input type="text" name="direccion" v-model.trim="campos.direccion" class="form-control" required autocomplete="off" placeholder="Direccion">
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Email</label>
						<input type="email" name="email" v-model.trim="campos.email" class="form-control" autocomplete="off" placeholder="Email">
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Sueldo</label>
						<input type="number" name="sueldo" v-model="campos.sueldo" class="form-control" required autocomplete="off" placeholder="Sueldo">
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Telf./Cel.</label>
						<input type="number" name="telefono" v-model="campos.telefono" class="form-control" placeholder="Telf./Cel." autocomplete="off" onkeypress="return store_numeros(event)">
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Sexo</label>
						<select class="form-select" name="sexo" v-model="campos.sexo" required>
							<option value="">SELECCIONE</option>
							<option value="M">MASCULINO</option>
							<option value="F">FEMENINO</option>
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

<script> var campos = {codregistro:"",coddocumentotipo:"2",codsucursal: "",documento: "",codarea: "",codcargo:"",razonsocial:"",direccion:"",email:"",sueldo:"0.00",telefono:"",sexo:""};</script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas.js"></script>
