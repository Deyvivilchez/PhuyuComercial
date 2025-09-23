<div id="phuyu_form">
	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
		<br> <input type="hidden" name="codregistro" v-model="campos.codregistro">

		<div class="row form-group">
			<div class="col-md-12">
				<label>DESCRIPCION SUCURSAL</label>
	        	<input type="text" name="descripcion" v-model.trim="campos.descripcion" class="form-control" required autocomplete="off" placeholder="Descripcion . . ." />
			</div>
		</div>
		<div class="row form-group">
			<div class="col-md-12">
				<label>DIRECCION SUCURSAL</label>
	        	<input type="text" name="direccion" v-model="campos.direccion" class="form-control" required autocomplete="off" placeholder="Direccion . . ." />
			</div>
		</div>
		<div class="row form-group">
			<div class="col-md-6">
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
	        <div class="col-md-6">
	            <label>PROVINCIA</label>
	            <select class="form-select" name="provincia" v-model="campos.provincia" id="provincia" required v-on:change="phuyu_distritos()">
	                <option value="">SELECCIONE</option>
	            </select>
	        </div>
		</div>
		<div class="row form-group">
	        <div class="col-md-6">
	            <label>DISTRITO</label>
	            <select class="form-select" name="codubigeo" v-model="campos.codubigeo" id="codubigeo" required>
	                <option value="">SELECCIONE</option>
	            </select>
	        </div>
	        <div class="col-md-6">
	        	<label>ESTADO CREDITO</label>
	        	<select class="form-select" v-model="campos.creditoprogramado" required>
	        		<option value="">Seleccione</option>
	        		<option value="0">NO PROGRAMADO</option>
	        		<option value="1">SI PROGRAMADO</option>	
	        	</select>
	        </div>
	    </div>
		<div class="row form-group">
			<div class="col-md-6">
				<label>TELEFONO SUCURSAL</label>
	        	<input type="text" name="telefonos" v-model="campos.telefonos" class="form-control" autocomplete="off" placeholder="Telefonos . . ." maxlength="50" />
			</div>
			<div class="col-md-6">
				<label>ES SUCURSAL PRINCIPAL</label>
	        	<select name="principal" v-model="campos.principal" class="form-select" required="">
	        		<option value="0">NO ES SUCURSAL PRINCIPAL</option>
	        		<option value="1">SI ES SUCURSAL PRINCIPAL</option>
	        	</select>
			</div>
		</div>
		<div class="row form-group">
			<div class="col-md-6">
				<label>TIPO DE DESPACHO</label>
	        	<select name="coddespachotipo" v-model="campos.coddespachotipo" class="form-control" required="">
	        		<option value="1">DESPACHO DIRECTO</option>
	        		<option value="0">DESPACHO POSTERIOR</option>
	        	</select>
			</div>
			<div class="col-md-6">
				<label>RUBRO ORIENTADO</label>
	        	<select name="codrubro" v-model="campos.codrubro" class="form-select" required="">
	        		<option value="">SELECCIONE</option>
	        		<?php
		        	foreach ($rubros as $key => $value) { ?>
		        		<option value="<?php echo $value["codrubro"]?>"><?php echo $value["descripcion"]?></option>
		        	<?php }
		        	?>
	        	</select>
			</div>
		</div>
		<hr> <h5 class="text-center"><b>CONFIGURAR COMPROBANTE DE VENTAS POR DEFECTO</b></h5> <hr>
		<div class="row form-group">
			<div class="col-md-7 col-xs-12">
				<label>TIPO COMPROBANTE</label>
	        	<select name="codcomprobantetipo" v-model="campos.codcomprobantetipo" class="form-select">
	        		<option value="0">SIN COMPROBANTE POR DEFECTO</option>
	        		<?php 
	        			foreach ($comprobantes as $key => $value) { ?>
	        				<option value="<?php echo $value['codcomprobantetipo'];?>"><?php echo $value["descripcion"];?></option>
	        			<?php }
	        		?>
	        	</select>
			</div>
			<div class="col-md-5 col-xs-12">
				<label>SERIE COMPROBANTE</label>
	        	<input type="text" name="seriecomprobante" v-model="campos.seriecomprobante" class="form-control" autocomplete="off" minlength="4" maxlength="4" style="text-transform:uppercase;" />
			</div>
		</div>

		<div class="ln_solid"></div>
		<div class="form-group" align="center">
			<button type="submit" class="btn btn-success" v-bind:disabled="estado==1"> <i class="fa fa-save"></i> GUARDAR </button>
			<button type="button" class="btn btn-danger" v-on:click="phuyu_cerrar()">CERRAR</button>
		</div>
	</form>
</div>

<script> 
var campos = {codregistro:"",descripcion: "",direccion: "",telefonos: "",codrubro:"",ventaconproforma:0,ventaconpedido:0,principal: 0,codcomprobantetipo:"0",seriecomprobante:"",departamento: "",provincia: "",codubigeo: "", coddespachotipo:1, lineas: [],creditoprogramado:""}; </script>
<script src="<?php echo base_url();?>phuyu/phuyu_form.js"></script>