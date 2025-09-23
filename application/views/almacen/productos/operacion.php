<div id="phuyu_form">
	<div class="panel bg-success text-center">
		<div class="panel-content">
			<h4><b>1. Cargar archivo - registro de productos/servicios</b></h4>
		</div>
	</div>
	<form id="formulario_cargarproductos" class="form-horizontal" v-on:submit.prevent="np_guardar_cargarproductos()">
		<div class="row form-group">	
			<div class="col-md-12">
				<label>Subir archivo excel <span class="text-danger">*</span></label>
	        	<input type="file" class="form-control" name="archivo" accept=".xlsx, .xls" required />
			</div>
		</div>
		<div class="row form-group">
			<div class="col-md-12 text-center">
				<button type="button" class="btn btn-success" v-on:click="np_formato_cargarproductos()"><i class="fa fa-file"></i> Descargar formato</button>
				<button type="submit" class="btn btn-primary" v-bind:disabled="estado==1"><i class="fa fa-save"></i> Guardar</button>
			</div>
		</div>
	</form> <hr>

	<div class="panel bg-success text-center">
		<div class="panel-content">
			<h4><b>2. Adicionar stock extra - registro de pedidos</b></h4>
		</div>
	</div>
	<form id="formulario_stockextra" class="form-horizontal" v-on:submit.prevent="np_guardar_stockextra()">
		<div class="row form-group">
			<div class="col-md-12">
				<label>Subir archivo excel <span class="text-danger">*</span></label>
	        	<input type="file" class="form-control" name="archivo" accept=".xlsx, .xls" required />
			</div>
		</div>
		<div class="form-group">
			<div class="col-md-12 text-center">
				<button type="submit" class="btn btn-primary" v-bind:disabled="estado==1"><i class="fa fa-save"></i> Guardar</button>
			</div>
	</form> <hr>
</div>
<script>
	var campos = [];
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_form.js"></script>