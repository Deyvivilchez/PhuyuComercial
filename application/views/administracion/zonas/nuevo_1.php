<div id="phuyu_form_1">
	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar_1()">
		<div class="row form-group">
			<div class="col-md-12">
				<label>DEPARTAMENTO</label>
	            <select class="form-select" name="departamento" v-model="campos.departamento1" required v-on:change="phuyu_provincias1()">
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
	            <select class="form-select" name="provincia1" v-model="campos.provincia1" id="provincia1" required v-on:change="phuyu_distritos1()">
	                <option value="">SELECCIONE</option>
	            </select>
	        </div>
	        <div class="col-md-6">
	            <label>DISTRITO</label>
	            <select class="form-select" name="codubigeo1" v-model="campos.codubigeo1" id="codubigeo1" required>
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
			<button type="submit" class="btn btn-success" v-bind:disabled="estado_1==1"> <i class="fa fa-save"></i> GUARDAR </button>
			<button type="button" class="btn btn-danger" data-bs-dismiss="modal">CERRAR</button>
		</div>
	</form>
</div>

<script> var campos = {codregistro:"",descripcion: "",departamento1: "",provincia1: "",codubigeo1: ""}; </script>
<script src="<?php echo base_url();?>phuyu/phuyu_form_zonas.js"></script>