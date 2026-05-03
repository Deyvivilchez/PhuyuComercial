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
</style>

<div id="phuyu_form" class="phuyu-producto-operacion">
	<div class="card phuyu-upload-card">
		<div class="phuyu-upload-title">1. Cargar archivo - registro de productos/servicios</div>
		<div class="card-body">
			<form id="formulario_cargarproductos" v-on:submit.prevent="np_guardar_cargarproductos()">
				<div class="row form-group">
					<div class="col-md-12">
						<label>Subir archivo excel <span class="text-danger">*</span></label>
						<input type="file" class="form-control" name="archivo" accept=".xls,.xlsx,.csv" required />
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
	var campos = [];
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_form.js"></script>
