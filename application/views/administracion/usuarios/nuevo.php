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
						<div class="input-group">
							<input v-bind:type="mostrar_clave ? 'text' : 'password'" name="clave" v-model="campos.clave" class="form-control" required placeholder="Clave">
							<button type="button" class="btn btn-light border" v-on:click="mostrar_clave = !mostrar_clave" title="Ver u ocultar clave">
								<i v-bind:class="mostrar_clave ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
							</button>
						</div>
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

				<div class="phuyu-section-title">Cajas del usuario</div>
				<div class="phuyu-table-wrap">
					<table class="table table-hover align-middle">
						<thead>
							<tr>
								<th>Sucursal</th>
								<th>Caja</th>
								<th width="110" class="text-center">Permiso</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($cajas as $key => $value) { ?>
								<tr v-if="sucursales.indexOf('<?php echo $value["codsucursal"];?>') !== -1 || sucursales.indexOf(<?php echo (int)$value["codsucursal"];?>) !== -1">
									<td><?php echo $value["sucursal"];?></td>
									<td><?php echo $value["descripcion"];?></td>
									<td class="text-center">
										<input type="checkbox" class="form-check-input" value="<?php echo $value["codcaja"];?>" v-model="cajas">
									</td>
								</tr>
							<?php } ?>
							<tr v-if="sucursales.length==0">
								<td colspan="3" class="text-center text-muted">Seleccione una sucursal para habilitar sus cajas.</td>
							</tr>
						</tbody>
					</table>
				</div>

				<div class="phuyu-section-title">Permisos especiales del usuario</div>
				<div class="alert alert-light border d-flex align-items-center justify-content-between gap-3 mb-3">
					<div>
						<strong>Permisos operativos</strong>
						<div class="text-muted small">Configure excepciones por usuario como modificar precios en ventas.</div>
					</div>
					<button type="button" class="btn btn-info btn-sm text-white" v-on:click="modal_permisos = true">
						<i class="bi bi-sliders me-1"></i> Configurar
					</button>
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

		<div class="modal fade show" tabindex="-1" role="dialog" v-if="modal_permisos" style="display:block; background: rgba(15, 23, 42, .35);">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content border-0 shadow-lg">
					<div class="modal-header">
						<h5 class="modal-title">Permisos especiales</h5>
						<button type="button" class="btn-close" v-on:click="modal_permisos = false"></button>
					</div>
					<div class="modal-body">
						<div class="phuyu-table-wrap">
							<table class="table table-hover align-middle mb-0">
								<thead>
									<tr>
										<th>Permiso</th>
										<th width="110" class="text-center">Activo</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>
											<strong>Modificar precio en ventas</strong>
											<div class="text-muted small">Permite editar precio unitario, descuentos y subtotales al registrar ventas.</div>
										</td>
										<td class="text-center">
											<input type="checkbox" class="form-check-input" true-value="1" false-value="0" v-model="campos.editar_pventa">
										</td>
									</tr>
									<tr>
										<td>
											<strong>Eliminar ventas</strong>
											<div class="text-muted small">Permite anular ventas registradas desde el listado de ventas.</div>
										</td>
										<td class="text-center">
											<input type="checkbox" class="form-check-input" true-value="1" false-value="0" v-model="campos.eliminar_venta">
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-primary" v-on:click="modal_permisos = false">
							<i class="bi bi-check2 me-1"></i> Listo
						</button>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>

<script> var campos = {codregistro:"",codempleado:"",codperfil: "",usuario: "",clave: "",editar_pventa:"0",eliminar_venta:"0"};</script>
<script src="<?php echo base_url();?>phuyu/phuyu_usuarios.js"></script>
