<div id="phuyu_form">
	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar_1()">
		<br> <input type="hidden" name="codsociotipo" v-model="campos.codsociotipo">

	    <div class="row form-group">
	    	<div class="col-md-4 col-xs-12">
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
		    <div class="col-md-4 col-xs-12" align="right" style="padding-top:16px;">
		    	<button type="button" class="btn btn-white btn-icon btn-consultar" v-on:click="phuyu_consultar();"> <i data-acorn-icon="search"></i> Consultar </button>
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
	        <div class="col-md-12">
	            <label>TELFONO / CELULAR</label>
	            <input type="number" class="form-control" name="telefono" v-model="campos.telefono" placeholder="Telf./Cel." autocomplete="off">
	        </div>
	    </div>

		<div class="ln_solid"></div>
		<div class="form-group" align="center">
			<button type="submit" class="btn btn-success" v-bind:disabled="estado==1"> <i class="fa fa-save"></i> GUARDAR </button>
			<button type="button" class="btn btn-danger" v-on:click="phuyu_cerrar()">CERRAR</button>
		</div>
	</form>

	<div id="modal_consultar" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title"> <b style="letter-spacing:2px;">CONSULTAR DATOS </b> </h4>
				</div>
				<div class="modal-body" align="center">
					<p>CONSULTAR LOS DATOS DEL CLIENTES O PROVEEDOR DESDE</p>
					<button type="button" class="btn btn-warning btn-block btn-consultar" v-show="campos.coddocumentotipo==2" v-on:click="phuyu_consultando()"><b>DESDE RENIEC O ESSALUD</b></button>
					<button type="button" class="btn btn-warning btn-block btn-consultar" v-show="campos.coddocumentotipo==4" v-on:click="phuyu_consultando()"><b>DESDE SUNAT</b></button>
					<button type="button" class="btn btn-danger" data-dismiss="modal">NO, CERRAR</button>
				</div>
			</div>
		</div>
	</div>
</div>

<script> var campos = {codsociotipo: "2",coddocumentotipo: "2",documento: "",razonsocial: "",nombrecomercial:"",direccion: "",email: "",telefono: ""}; 
	if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas_1.js"></script>