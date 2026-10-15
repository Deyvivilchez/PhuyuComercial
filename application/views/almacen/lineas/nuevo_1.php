<style>
	#phuyu_form_1.phuyu-almacen-form .form-label {
		font-size: .74rem;
		font-weight: 800;
		text-transform: uppercase;
		letter-spacing: .03em;
		color: #495057;
		margin-bottom: .4rem;
	}

	#phuyu_form_1.phuyu-almacen-form .form-control {
		min-height: 40px;
		border-color: rgba(64, 81, 137, .16);
	}

	#phuyu_form_1.phuyu-almacen-form .phuyu-form-header {
		display: flex;
		align-items: center;
		gap: .65rem;
		margin-bottom: 1rem;
	}

	#phuyu_form_1.phuyu-almacen-form .phuyu-form-icon {
		width: 38px;
		height: 38px;
		border-radius: 12px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: rgba(64, 81, 137, .10);
		color: #405189;
	}

	#phuyu_form_1.phuyu-almacen-form .phuyu-table-wrap {
		border: 1px solid rgba(64, 81, 137, .10);
		border-radius: .75rem;
		overflow: hidden;
	}

	#phuyu_form_1.phuyu-almacen-form table thead th {
		background: #f8fafc;
		color: #495057;
		font-size: .74rem;
		font-weight: 800;
		text-transform: uppercase;
	}

	#phuyu_form_1.phuyu-almacen-form .phuyu-form-actions {
		display: flex;
		justify-content: flex-end;
		gap: .65rem;
		padding-top: 1rem;
		margin-top: 1rem;
		border-top: 1px solid rgba(64, 81, 137, .10);
	}
</style>

<div id="phuyu_form_1" class="phuyu-almacen-form">
	<div class="phuyu-form-header">
		<div class="phuyu-form-icon"><i class="bi bi-diagram-3"></i></div>
		<div>
			<div class="text-muted small text-uppercase fw-semibold">Almacen</div>
			<h5 class="mb-0 fw-bold">Registrar nueva linea</h5>
		</div>
	</div>

	<form id="formulario" v-on:submit.prevent="phuyu_guardar_1('almacen/lineas')">
		<input type="hidden" id="codigo_extencion" value="codlinea">

		<div class="row g-3">
			<div class="col-12">
				<label class="form-label">Descripcion linea</label>
				<input type="text" name="descripcion_extencion" v-model.trim="agregar.descripcion" class="form-control" required autocomplete="off" placeholder="Descripcion" maxlength="100">
			</div>

			<div class="col-12">
				<div class="d-flex align-items-center gap-2 mb-2 text-primary fw-bold">
					<i class="bi bi-shop"></i>
					<span>Sucursales donde se manejara la linea</span>
				</div>
				<div class="table-responsive phuyu-table-wrap">
					<table class="table table-hover align-middle mb-0">
						<thead>
							<tr>
								<th>Sucursal</th>
								<th class="text-center" width="100">Permiso</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($sucursales as $value) { ?>
								<tr>
									<td class="fw-semibold"><?php echo $value["descripcion"];?></td>
									<td class="text-center">
										<input type="hidden" name="sucursales[]" value="<?php echo $value["codsucursal"];?>">
										<input type="checkbox" class="form-check-input" id="sucursal_<?php echo $value["codsucursal"];?>" value="<?php echo $value["codsucursal"];?>" v-model="agregar.sucursales" name="checks[]">
									</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>

		<div class="phuyu-form-actions">
			<button type="submit" class="btn btn-primary" v-bind:disabled="estado_1==1">
				<i class="bi bi-save me-1"></i> Guardar
			</button>
			<button type="button" class="btn btn-light" data-bs-dismiss="modal">
				<i class="bi bi-x-circle me-1"></i> Cerrar
			</button>
		</div>
	</form>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_form_1.js"></script>
