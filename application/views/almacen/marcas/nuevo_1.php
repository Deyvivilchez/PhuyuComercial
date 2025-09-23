<div class="row" id="phuyu_form_1">
	<div class="col-md-12 col-xs-12">
		<h4><b>REGISTRAR NUEVA MARCA</b></h4> <hr>
		<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar_1('almacen/marcas')">
			<input type="hidden" id="codigo_extencion" value="codmarca">
			<div class="form-group">
				<label>DESCRIPCION MARCA</label>
	        	<input type="text" name="descripcion_extencion" v-model.trim="agregar.descripcion" class="form-control" required autocomplete="off" placeholder="Descripcion . . ." maxlength="100" />
			</div>

			<div class="form-group text-center"> <br>
				<button type="submit" class="btn btn-primary" v-bind:disabled="estado_1==1"> <i data-acorn-icon="save"></i> GUARDAR </button>
				<button type="button" class="btn btn-danger" data-bs-dismiss="modal">CERRAR</button>
			</div>
		</form>
	</div>
</div>
<script>
	if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_form_1.js"></script>