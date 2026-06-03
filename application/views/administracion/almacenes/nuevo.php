<div id="phuyu_form">
	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
		<br> <input type="hidden" name="codregistro" v-model="campos.codregistro">

		<div class="row form-group">
			<div class="col-md-12">
				<label>SUCURSAL ALMACEN</label>
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
			<div class="col-md-12">
				<label>DESCRIPCION ALMACEN</label>
	        	<input type="text" name="descripcion" v-model.trim="campos.descripcion" class="form-control" required autocomplete="off" placeholder="Descripcion . . ." />
			</div>
		</div>
		<div class="row form-group">
			<div class="col-md-12">
				<label>DIRECCION ALMACEN</label>
	        	<input type="text" name="direccion" v-model="campos.direccion" class="form-control" required autocomplete="off" placeholder="Direccion . . ." />
			</div>
		</div>
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
				<label>TELEFONOS ALMACEN</label>
	        	<input type="number" name="telefonos" v-model.number="campos.telefonos" class="form-control" autocomplete="off" placeholder="Telefonos . . ." />
			</div>
		</div>

		<div class="row form-group">
			<div class="col-md-12">
				<label>ALMACEN CONTROLA STOCK</label>
	        	<select name="controlstock" v-model.number="campos.controlstock" class="form-select">
	        		<option value="1">SI CONTROLA STOCK</option>
	        		<option value="0">NO CONTROLA STOCK</option>
	        	</select>
			</div>
		</div>
		<div class="row form-group">
			<div class="col-md-12">
				<label>AFECTACION POR DEFECTO</label>
				<select name="codafectacionigv" v-model="campos.codafectacionigv" class="form-select" required="">
	        		<option value="">SELECCIONE</option>
	        		<?php
		        	foreach ($afectacionigv as $key => $value) { ?>
		        		<option value="<?php echo $value["codafectacionigv"]?>"><?php echo $value["descripcion"]?></option>
		        	<?php }
		        	?>
	        	</select>
			</div>
		</div>
		<div class="ln_solid"></div>
		<div class="form-group" align="center">
			<button type="submit" class="btn btn-success" v-bind:disabled="estado==1"> <i class="fa fa-save"></i> GUARDAR </button>
			<button type="button" class="btn btn-danger" v-on:click="phuyu_cerrar()">CERRAR</button>
		</div>
	</form>
</div>

<script> var campos = {codregistro:"",codsucursal:"",descripcion: "",direccion: "",telefonos: "",controlstock: 1,departamento: "",provincia: "",codubigeo: "",codafectacionigv:9}; </script>
<script src="<?php echo base_url();?>phuyu/phuyu_form.js"></script>