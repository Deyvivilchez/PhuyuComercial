<div id="phuyu_form">
	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
		<br> <input type="hidden" name="codregistro" v-model="campos.codregistro">

		<div class="row form-group">
			<div class="col-xs-12">
				<label>SUCURSAL AMBIENTE</label>
	        	<select class="form-select" name="codsucursal" v-model="campos.codsucursal" required>
	        		<option value="">SELECCIONE</option>
	        		<?php 
	        			foreach ($sucursales as $key => $value) { ?>
	        				<option value="<?php echo $value["codsucursal"];?>"><?php echo $value["descripcion"];?></option>
	        			<?php }
	        		?>
	        	</select>
			</div>
		</div>
		<div class="row form-group">
			<div class="col-xs-12">
				<label>DESCRIPCION AMBIENTE</label>
	        	<input type="text" name="descripcion" v-model.trim="campos.descripcion" class="form-control" required autocomplete="off" placeholder="Descripcion . . ." />
			</div>
		</div>
		<div class="row form-group">
			<div class="col-xs-12">
				<label>AFORO AMBIENTE</label>
	        	<input type="number" name="aforo" v-model.trim="campos.aforo" class="form-control" required autocomplete="off" placeholder="Aforo . . ." />
			</div>
		</div>

		<div class="ln_solid"></div>
		<div class="form-group" align="center">
			<button type="submit" class="btn btn-success" v-bind:disabled="estado==1"> <i class="fa fa-save"></i> GUARDAR </button>
			<button type="button" class="btn btn-danger" v-on:click="phuyu_cerrar()">CERRAR</button>
		</div>
	</form>
</div>

<script> var campos = {codregistro:"",codsucursal:"",descripcion: "",aforo: ""}; </script>
<script src="<?php echo base_url();?>phuyu/phuyu_form.js"></script>