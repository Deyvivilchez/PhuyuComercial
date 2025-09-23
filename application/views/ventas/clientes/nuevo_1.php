<div id="phuyu_form">
	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar_1()">
		<br> <input type="hidden" name="codsociotipo" v-model="campos.codsociotipo">
		<input type="hidden" name="">
	    <div class="row form-group">
	    	<div class="col-md-6 col-xs-12">
		    	<label>TIPO DOCUMENTO</label>
		    	<select class="form-select" name="coddocumentotipo" v-model="campos.coddocumentotipo" required v-on:change="phuyu_tipodocumento()" ref="coddocumentotipo">
		    		<option value="">SELECCIONE</option>
		            <?php 
		                foreach ($tipodocumentos as $key => $value) { ?>
		                    <option value="<?php echo $value['coddocumentotipo'];?>"><?php echo $value["descripcion"];?></option>
		                <?php }
		            ?>
		    	</select>
		    </div>
		    <div class="col-md-4 col-xs-12">
		    	<label>DOCUMENTO</label>
	        	<input type="text" class="form-control line-danger" name="documento" v-model="campos.documento" id="documento" placeholder="Numero" required autocomplete="off" minlength="8" maxlength="8" ref="documento">
		    </div>
		    <div class="col-md-2 col-xs-12" style="padding-top:15px;">
		    	<button type="button" class="btn btn-primary btn-icon btn-consultar" v-on:click="phuyu_consultar();"> <i data-acorn-icon="search"></i> </button>
		    </div>
	    </div>
	    <div class="row form-group">
	    	<div class="col-xs-12">
		        <label>RAZON SOCIAL</label>
		        <input type="text" class="form-control" name="razonsocial" v-model="campos.razonsocial" placeholder="Razon social" required autocomplete="off">
		    </div>
	    </div>
	    <div class="row form-group">
	    	<div class="col-xs-12">
		        <label>NOMBRE COMERCIAL</label>
		        <input type="text" class="form-control" name="nombrecomercial" v-model="campos.nombrecomercial" placeholder="Nombre comercial" autocomplete="off">
		    </div>
	    </div>
	    <div class="row form-group">
	    	<div class="col-xs-12">
		        <label>DIRECCION</label>
		        <input type="text" class="form-control" name="direccion" v-model="campos.direccion" placeholder="Direccion" required autocomplete="off">
		    </div>
	    </div>
	    <div class="row form-group">
	    	<div class="col-xs-12">
		        <label>EMAIL</label>
		        <input type="text" class="form-control" name="email" v-model="campos.email" placeholder="Email" autocomplete="off">
		    </div>
	    </div>
	    <div class="row form-group">
	        <div class="col-md-6 col-xs-12">
	            <label>TELF./CEL.</label>
	            <input type="number" class="form-control" name="telefono" v-model="campos.telefono" placeholder="Telf./Cel." autocomplete="off">
	        </div>
	        <div class="col-md-6 col-xs-12">
                <label>SEXO / EMPRESA</label>
                <select class="form-select" name="sexo" v-model="campos.sexo" required >
                    <option value="">SELECCIONE</option>
                    <option value="M">MASCULINO</option>
                    <option value="F">FEMENINO</option>
                    <option value="E">EMPRESA</option>
                </select>
            </div>
	    </div>
	    <div class="row form-group" v-if="campos.rubro==5">
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
	    <div class="row form-group" v-if="campos.rubro==5">
	        <div class="col-md-6">
	            <label>PROVINCIA</label>
	            <select class="form-select" name="provincia" v-model="campos.provincia" id="provincia" required v-on:change="phuyu_distritos()">
	                <option value="">SELECCIONE</option>
	            </select>
	        </div>
	        <div class="col-md-6" v-if="campos.rubro==5">
	            <label>DISTRITO</label>
	            <select class="form-select" name="codubigeo" v-model="campos.codubigeo" id="codubigeo" required v-on:change="phuyu_zonas()">
	                <option value="">SELECCIONE</option>
	            </select>
	        </div>
	    </div>

	    <div class="row form-group" v-if="campos.rubro==5">
	        <div class="col-md-12">
	            <label>ZONA</label>
	            <select class="form-select" name="codzona" v-model="campos.codzona" id="codzona" required>
	                <option value="">SELECCIONE</option>
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

<script> var campos = {codsociotipo: "1",coddocumentotipo: "2",documento: "",razonsocial: "",nombrecomercial:"",direccion: "",email: "",telefono: "",sexo: "M",departamento: 0,provincia: 0,codubigeo: 0,codpatrocinador:0,codzona:0,rubro:<?php echo $_SESSION["phuyu_rubro"];?>}; 

	if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }

</script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas_1.js"></script>