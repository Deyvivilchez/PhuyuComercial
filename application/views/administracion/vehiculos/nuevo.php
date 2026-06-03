<div id="phuyu_form">
	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
		<br> <input type="hidden" name="codregistro" v-model="campos.codregistro">

		<div class="row form-group">
			<div class="col-md-12">
				<label>DESCRIPCION MARCA</label>
	        	<input type="text" name="descripcion" v-model.trim="campos.descripcion" class="form-control" required autocomplete="off" placeholder="Descripcion . . ." />
	        </div>
	    </div>
	    <div class="row form-group">
	        <div class="col-md-12">
	        	<label>NRO PLACA</label>
	        	<input type="text" name="nroplaca" v-model.trim="campos.nroplaca" class="form-control" required autocomplete="off" placeholder="Placa . . ." />
	        </div>
	    </div>
	    <div class="row form-group">
	        <div class="col-md-12">
	        	<label>CONSTANCIA DE INSCRIPCION</label>
	        	<input type="text" name="constancia" v-model.trim="campos.constancia" class="form-control" required autocomplete="off" placeholder="Constancia . . ." />
			</div>
		</div>

		<div class="ln_solid"></div>
		<div class="form-group" align="center">
			<button type="submit" class="btn btn-success" v-bind:disabled="estado==1"> <i class="fa fa-save"></i> GUARDAR </button>
			<button type="button" class="btn btn-danger" v-on:click="phuyu_cerrar()">CERRAR</button>
		</div>
	</form>
</div>

<script> var campos = {codregistro:"",descripcion: "",nroplaca:"",constancia:""}; </script>
<script src="<?php echo base_url();?>phuyu/phuyu_form.js"></script>