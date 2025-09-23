<div id="phuyu_form">
	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar_1()">
		<br> <input type="hidden" name="codsociotipo" v-model="campos.codsociotipo">

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
		    <div class="col-md-2 col-xs-12" style="padding-top:25px;">
		    	<button type="button" class="btn btn-success btn-block btn-consultar" v-on:click="phuyu_consultar();"> <i class="fa fa-search"></i> </button>
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
		        <label>DIRECCION</label>
		        <input type="text" class="form-control" name="direccion" v-model="campos.direccion" placeholder="Direccion" required autocomplete="off">
		    </div>
	    </div>
	    <div class="row form-group">
	    	<div class="col-xs-12">
		        <label>LICENCIA DE CONDUCIR</label>
		        <input type="text" class="form-control" name="licencia" v-model="campos.licencia" placeholder="Licencia de conducir" required autocomplete="off">
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

		<div class="ln_solid"></div>
		<div class="form-group" align="center">
			<button type="submit" class="btn btn-success" v-bind:disabled="estado==1"> <i class="fa fa-save"></i> GUARDAR </button>
			<button type="button" class="btn btn-danger" v-on:click="phuyu_cerrar()">CERRAR</button>
		</div>
	</form>
</div>

<script> var campos = {codsociotipo: "1",coddocumentotipo: "2",documento: "",razonsocial: "",nombrecomercial:"",direccion: "",email: "",telefono: "",sexo: "M"}; </script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas_conductor.js"></script>