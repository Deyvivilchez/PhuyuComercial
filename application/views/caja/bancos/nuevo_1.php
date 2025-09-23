<div class="row" id="phuyu_form_1">
	<div class="col-md-7 col-xs-12" style="border-right: 1px solid #e5e6e7">
		<h4><b>REGISTRAR NUEVA MARCA</b></h4> 
		<span>SI YA ESTA REGISTRADA NO ES NECESARIO QUE INGRESES DE NUEVO SOLO TIENES QUE BUSCAR</span> <hr>
		<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar_1('almacen/marcas')">
			<input type="hidden" id="codigo_extencion" value="codmarca">
			<div class="form-group">
				<label>DESCRIPCION MARCA</label>
	        	<input type="text" name="descripcion_extencion" v-model.trim="agregar.descripcion" class="form-control" required autocomplete="off" placeholder="Descripcion . . ." maxlength="100" />
			</div>

			<div class="form-group text-center"> <br>
				<button type="submit" class="btn btn-success" v-bind:disabled="estado_1==1"> <i class="fa fa-save"></i> GUARDAR </button>
				<button type="button" class="btn btn-danger" data-dismiss="modal">CERRAR</button>
			</div>
		</form>
	</div>
	<div class="col-md-5 col-xs-12">
		<h5><b>ESTAS REGISTRANDO</b> <br> UNA NUEVA MARCA PARA AGRUPAR LOS PRODUCTOS</h5>
		<div class="text-center"> <i class="phuyu_big_icon fa fa-bookmark-o"></i> </div>
	</div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_form_1.js"></script>