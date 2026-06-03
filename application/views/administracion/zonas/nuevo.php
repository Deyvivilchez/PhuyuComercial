<div id="phuyu_form">
	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
		<br> <input type="hidden" name="codregistro" v-model="campos.codregistro">
		<div class="row form-group">
			<div class="col-md-12">
				<label>DEPARTAMENTO</label>
	            <select class="form-select" name="departamento" v-model="campos.departamento" required v-on:change="phuyu_provincias()">
	                <option value="">SELECCIONE</option>
	                <?php 
	                    foreach ($departamentos as $key => $value) { ?>
	                        <option value="<?php echo $value['ubidepartamento'];?>"><?php echo $value["departamento"];?></option>
	                    <?php }
	                ?>
	            </select>
			</div>
		</div>
		<div class="row form-group">
	        <div class="col-md-6">
	            <label>PROVINCIA</label>
	            <select class="form-select" name="provincia" v-model="campos.provincia" id="provincia" required v-on:change="phuyu_distritos()">
	                <option value="">SELECCIONE</option>
	            </select>
	        </div>
	        <div class="col-md-6">
	            <label>DISTRITO</label>
	            <select class="form-select" name="codubigeo" v-model="campos.codubigeo" id="codubigeo" required>
	                <option value="">SELECCIONE</option>
	            </select>
	        </div>
	    </div>
	    <div class="row form-group">
			<div class="col-md-12">
				<label>DESCRIPCION ZONA</label>
	        	<input type="text" name="descripcion" v-model.trim="campos.descripcion" class="form-control" required autocomplete="off" placeholder="Descripcion . . ." />
			</div>
		</div>

		<div class="ln_solid"></div>
		<div class="form-group" align="center">
			<button type="submit" class="btn btn-success" v-bind:disabled="estado==1"> <i class="fa fa-save"></i> GUARDAR </button>
			<button type="button" class="btn btn-danger" v-on:click="phuyu_cerrar()">CERRAR</button>
		</div>
	</form>
</div>

<script> var campos = {codregistro:"",descripcion: "",departamento: "",provincia: "",codubigeo: ""}; </script>
<script src="<?php echo base_url();?>phuyu/phuyu_form.js"></script>