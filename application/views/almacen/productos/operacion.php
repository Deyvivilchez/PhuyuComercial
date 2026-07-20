<style>
	#phuyu_form.phuyu-producto-operacion .phuyu-upload-card {
		border: 1px solid #e9ebec;
		border-radius: 8px;
		box-shadow: 0 1px 2px rgba(56, 65, 74, 0.08);
		margin-bottom: 14px;
	}
	#phuyu_form.phuyu-producto-operacion .phuyu-upload-title {
		background: #f3f6f9;
		border-bottom: 1px solid #e9ebec;
		color: #343a40;
		font-weight: 700;
		padding: 12px 14px;
	}
	#phuyu_form.phuyu-producto-operacion label {
		color: #495057;
		font-size: 12px;
		font-weight: 700;
		margin-bottom: 7px;
		text-transform: uppercase;
	}
	#phuyu_form.phuyu-producto-operacion .form-control {
		border: 1px solid #d9e2ef;
		border-radius: 6px;
		box-shadow: none;
		min-height: 38px;
	}
	#phuyu_form.phuyu-producto-operacion .phuyu-actions {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
		justify-content: center;
	}
	#phuyu_form.phuyu-producto-operacion .form-check-label {
		font-size: 12px;
		font-weight: 600;
		text-transform: none;
	}
</style>

<div id="phuyu_form" class="phuyu-producto-operacion">
	<?php $sucursalesMigracion = []; ?>
	<div class="card phuyu-upload-card">
		<div class="phuyu-upload-title">1. Migrar productos/servicios por sucursal</div>
		<div class="card-body">
			<form id="formulario_cargarproductos" v-on:submit.prevent="np_guardar_cargarproductos()">
				<div class="row form-group g-2">
					<div class="col-md-6">
						<label>Sucursal destino <span class="text-danger">*</span></label>
						<select class="form-control" name="codsucursal" v-model="campos.codsucursal" v-on:change="np_sucursal_productos()" required>
							<option value="">SELECCIONE</option>
							<?php foreach ($almacenes as $value): ?>
								<?php if (!isset($sucursalesMigracion[$value['codsucursal']])): ?>
									<?php $sucursalesMigracion[$value['codsucursal']] = true; ?>
									<option value="<?php echo $value['codsucursal']; ?>"><?php echo $value['sucursal']; ?></option>
								<?php endif; ?>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-6">
						<label>Almacen destino <span class="text-danger">*</span></label>
						<select class="form-control" name="codalmacen" v-model="campos.codalmacen" required>
							<option value="">SELECCIONE</option>
							<?php foreach ($almacenes as $value): ?>
								<option value="<?php echo $value['codalmacen']; ?>" data-codsucursal="<?php echo $value['codsucursal']; ?>"><?php echo $value['almacen']; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="col-md-12">
						<label>Subir archivo excel <span class="text-danger">*</span></label>
						<input type="file" class="form-control" name="archivo" accept=".xls,.xlsx,.csv" required />
					</div>
					<div class="col-md-12">
						<div class="form-check mt-2">
							<input class="form-check-input" type="checkbox" name="limpiar_almacen" value="1" id="limpiar_almacen_productos">
							<label class="form-check-label" for="limpiar_almacen_productos">Limpiar productos del almacen destino antes de importar</label>
						</div>
					</div>
				</div>
				<div class="phuyu-actions mt-3">
					<button type="button" class="btn btn-success" v-on:click="np_formato_cargarproductos()"><i class="bi bi-file-earmark-spreadsheet me-1"></i> Descargar formato</button>
					<button type="submit" class="btn btn-primary" v-bind:disabled="estado==1"><i class="bi bi-save me-1"></i> Guardar</button>
				</div>
			</form>
		</div>
	</div>

	<div class="card phuyu-upload-card">
		<div class="phuyu-upload-title">2. Adicionar stock extra - registro de pedidos</div>
		<div class="card-body">
			<form id="formulario_stockextra" v-on:submit.prevent="np_guardar_stockextra()">
				<div class="row form-group">
					<div class="col-md-12">
						<label>Subir archivo excel <span class="text-danger">*</span></label>
						<input type="file" class="form-control" name="archivo" accept=".xls,.xlsx,.csv" required />
					</div>
				</div>
				<div class="phuyu-actions mt-3">
					<button type="button" class="btn btn-success" v-on:click="np_formato_stockextra()"><i class="bi bi-file-earmark-spreadsheet me-1"></i> Descargar formato</button>
					<button type="submit" class="btn btn-primary" v-bind:disabled="estado==1"><i class="bi bi-save me-1"></i> Guardar</button>
				</div>
			</form>
		</div>
	</div>
</div>
<script>
	var campos = {
		codsucursal: "<?php echo isset($_SESSION['phuyu_codsucursal']) ? (int) $_SESSION['phuyu_codsucursal'] : ''; ?>",
		codalmacen: "<?php echo isset($_SESSION['phuyu_codalmacen']) ? (int) $_SESSION['phuyu_codalmacen'] : ''; ?>"
	};
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_form.js"></script>
