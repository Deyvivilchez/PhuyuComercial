<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_form" class="phuyu-velzon-form">
	<form id="formulario" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" name="codregistro" v-model="campos.codregistro">

		<div class="card phuyu-card">
			<div class="card-body">
				<div class="phuyu-form-title">
					<div class="phuyu-form-icon"><i class="bi bi-person-plus"></i></div>
					<div>
						<div class="text-muted small text-uppercase fw-semibold">Administracion</div>
						<h5 class="mb-0 fw-bold">Registro de usuario</h5>
					</div>
				</div>

				<div class="row g-3">
					<div class="col-12">
						<label class="form-label">Seleccionar empleado</label>
						<select class="form-select" name="codempleado" v-model="campos.codempleado" required>
							<option value="">SELECCIONE</option>
							<?php foreach ($empleados as $key => $value) { ?>
								<option value="<?php echo $value['codpersona'];?>"><?php echo $value["razonsocial"];?></option>
							<?php } ?>
						</select>
					</div>

					<div class="col-12">
						<label class="form-label">Seleccionar perfil</label>
						<select class="form-select" name="codperfil" v-model="campos.codperfil" required>
							<option value="">SELECCIONE</option>
							<?php foreach ($perfiles as $key => $value) { ?>
								<option value="<?php echo $value['codperfil'];?>"><?php echo $value["descripcion"];?></option>
							<?php } ?>
						</select>
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Nombre de usuario</label>
						<input type="text" name="usuario" v-model.trim="campos.usuario" class="form-control" required autocomplete="off" placeholder="Usuario">
					</div>

					<div class="col-12 col-md-6">
						<label class="form-label">Clave de usuario</label>
						<input type="password" name="clave" v-model="campos.clave" class="form-control" required placeholder="Clave">
					</div>
				</div>

				<div class="phuyu-section-title">Sucursales del usuario</div>
				<div class="phuyu-table-wrap">
					<table class="table table-hover align-middle">
						<thead>
							<tr>
								<th>Sucursal</th>
								<th width="110" class="text-center">Permiso</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($sucursales as $key => $value) { ?>
								<tr>
									<td><?php echo $value["descripcion"];?></td>
									<td class="text-center">
										<input type="hidden" name="sucursales[]" value="<?php echo $value["codsucursal"];?>">
										<input type="checkbox" class="form-check-input" id="sucursal_<?php echo $value["codsucursal"];?>" value="<?php echo $value["codsucursal"];?>" v-model="sucursales">
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
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

<script> var campos = {codregistro:"",codempleado:"",codperfil: "",usuario: "",clave: "",editar_pventa:"1"};</script>
<script src="<?php echo base_url();?>phuyu/phuyu_usuarios.js"></script>
