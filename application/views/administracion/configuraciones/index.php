<div id="phuyu_datos">

	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" name="codpersona" v-model="campos.codpersona">
		<input type="hidden" name="codempresa" v-model="campos.codempresa">
		<input type="hidden" name="itemrepetircomprobante" v-model="campos.itemrepetircomprobante">
		<div class="card">
			<div class="card-body">
				<div class="row form-group"> 
					<div class="col-md-9 col-xs-12"> <h5>CONFIGURACIONES DE TU EMPRESA</h5> </div>
					<div class="col-md-3">
						<button type="submit" class="btn btn-success btn-block btn-icon"> 
				    		<i data-acorn-icon="save"></i> GUARDAR CONFIGURACION
		                </button>
		            </div>
				</div><hr>
				<div class="row form-group">
					<div class="col-md-6 col-xs-12">
						<div class="phuyu_body_row">
							<h5 class="text-center"><b>DATOS DE LA EMPRESA</b></h5> <hr>

							<div class="row form-group">
							    <div class="col-md-7 col-xs-7">
							    	<label>RUC EMPRESA</label>
						        	<input type="text" class="form-control" name="documento" v-model="campos.documento" id="documento" placeholder="Numero" required autocomplete="off" minlength="11" maxlength="11">
							    </div>
							    <div class="col-md-5 col-xs-5" style="margin-top: 1.2rem">
					                <button type="button" class="btn btn-primary btn-icon btn-consultar" v-on:click="phuyu_consultar()"> 
					                	<i data-acorn-icon="search"></i> CONSULTAR SUNAT
					                </button>
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
						    	<div class="col-md-8 col-xs-12">
							        <label>DIRECCION</label>
							        <input type="text" class="form-control" name="direccion" v-model="campos.direccion" placeholder="Direccion" required autocomplete="off">
							    </div>
							    <div class="col-md-4 col-xs-12">
							    	<label>CLAVE SEGURIDAD</label>
						        	<input type="password" class="form-control" name="claveseguridad" v-model="campos.claveseguridad" placeholder="Clave" autocomplete="off" maxlength="50">
							    </div>
						    </div>
						    <div class="row form-group">
						    	<div class="col-md-4">
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
						        <div class="col-md-4">
						            <label>PROVINCIA</label>
						            <select class="form-select" name="provincia" v-model="campos.provincia" id="provincia" required v-on:change="phuyu_distritos()">
						                <option value="">SELECCIONE</option>
						            </select>
						        </div>
						        <div class="col-md-4">
						            <label>DISTRITO</label>
						            <select class="form-select" name="codubigeo" v-model="campos.codubigeo" id="codubigeo" required >
						                <option value="">SELECCIONE</option>
						            </select>
						        </div>
						    </div>
						    <div class="row form-group">
						    	<div class="col-md-6 col-xs-12">
							        <label>EMAIL EMPRESA</label>
							        <input type="text" class="form-control" name="email" v-model="campos.email" placeholder="Email" autocomplete="off">
							    </div>
						        <div class="col-md-6">
						            <label>TELF./CEL.</label>
						            <input type="text" class="form-control" name="telefono" v-model="campos.telefono" placeholder="Telf./Cel." autocomplete="off" maxlength="100">
						        </div>
						    </div>
						    <div class="row form-group">
						    	<div class="col-xs-12">
							        <label>SLOGAN EMPRESA</label>
							        <textarea class="form-control" name="slogan" v-model="campos.slogan" placeholder="Slogan . . ." autocomplete="off" rows="3"></textarea>
							    </div>
						    </div>
						    <div class="row form-group">
						    	<div class="col-md-4">
						    		<label>CODIGO</label>
						    		<input type="text" class="form-control" v-model="campos.codleyendapamazonia" name="codleyendapamazonia" placeholder="Codigo...">
						    	</div>
						   		<div class="col-md-8">
						   			<label>LEYENDA DE BIENES</label>
						   			<textarea class="form-control" name="leyendapamazonia" v-model="campos.leyendapamazonia" placeholder="Leyenda bienes . . ." autocomplete="off" rows="1"></textarea>
						   		</div>
						   	</div>
					   		<div class="row form-group">
					   			<div class="col-md-4">
						    		<label>CODIGO</label>
						    		<input type="text" class="form-control" v-model="campos.codleyendasamazonia" name="codleyendasamazonia" placeholder="Codigo...">
						    	</div>
						   		<div class="col-md-8">
						   			<label>LEYENDA DE SERVICIOS</label>
						   			<textarea class="form-control" name="leyendasamazonia" v-model="campos.leyendasamazonia" placeholder="Leyenda servicios . . ." autocomplete="off" rows="1"></textarea>
						   		</div>
						   	</div> 
						</div>
					</div>

					<div class="col-md-6 col-xs-12">
						<div class="phuyu_body_row">
							<h5 class="text-center"><b>CONFIGURAR PARAMETROS SUNAT</b></h5> <hr>

							<div class="row form-group">
						    	<div class="col-md-4 col-xs-6">
						    		<label>IGV SUNAT (%)</label>
							        <input type="number" step="0.01" class="form-control" name="igvsunat" v-model.number="campos.igvsunat" autocomplete="off" required>
							    </div>
							    <div class="col-md-4 col-xs-6">
						    		<label>ICBPER SUNAT (%)</label>
							        <input type="number" step="0.01" class="form-control" name="icbpersunat" v-model.number="campos.icbpersunat" autocomplete="off" required>
							    </div>
							    <div class="col-md-4 col-xs-6">
						    		<label>ISC SUNAT (%)</label>
							        <input type="number" step="0.01" class="form-control" name="iscsunat" v-model.number="campos.iscsunat" autocomplete="off" required>
							    </div>
							</div>
							<div class="row form-group">
							    <div class="col-md-12 col-xs-12">
									<div class="">
										<label v-if="campos.itemrepetircomprobante==1" >
										  	REPETIR ITEM (BIEN/SERVICIO) EN EL COMPROBANTE <input type="checkbox" class="js-switch" v-on:click="phuyu_itemrepetir()" checked/>
										</label>
										<label v-else="campos.itemrepetircomprobante!=1" >
										  	REPETIR ITEM (BIEN/SERVICIO) EN EL COMPROBANTE <input type="checkbox" class="js-switch" v-on:click="phuyu_itemrepetir()"/>
										</label>
									</div>
							    </div>
						    </div>
						   	
						</div> <br>

						<div class="phuyu_body_row">
							<h5 class="text-center"><b>LOGOS DE LA EMPRESA</b></h5>
							<div class="row form-group">
						    	<div class="col-md-6">
							        <label>LOGO EMPRESA</label>
							        <input type="file" class="form-control" name="logo" accept="image/*">
							    </div>
							    <div class="col-md-6">
							        <label>LOGO AUSPICIADOR</label>
							        <input type="file" class="form-control" name="auspiciador" accept="image/*">
							    </div>
						    </div>
						    <div class="row form-group">
						    	<div class="col-xs-12">
							        <label>PUBLICIDAD</label>
							        <textarea class="form-control" name="publicidad" v-model="campos.publicidad" placeholder="Publicidad . . ." autocomplete="off" rows="1"></textarea>
							    </div>
						    </div>
						    <div class="row form-group">
						    	<div class="col-xs-12">
							        <label>AGRADECIMIENTO</label>
							        <textarea class="form-control" name="agradecimiento" v-model="campos.agradecimiento" placeholder="Agradecimiento . . ." autocomplete="off" rows="1"></textarea>
							    </div>
						    </div>
						    <div class="row form-group">
						    	<div class="col-xs-12">
							        <label>URL CONSULTA COMPROBANTES</label>
							        <textarea class="form-control" name="urlconsultacomprobantes" v-model="campos.urlconsultacomprobantes" autocomplete="off" rows="1"></textarea>
							    </div>
						    </div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
<script>
	if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }
</script>
<script>
	var campos = {
		codpersona:"<?php echo $info[0]["codpersona"];?>",
		codempresa:"<?php echo $empresa[0]["codempresa"];?>",
		documento:"<?php echo $info[0]["documento"];?>",
		razonsocial:"<?php echo $info[0]["razonsocial"];?>",
		nombrecomercial:"<?php echo $info[0]["nombrecomercial"];?>",
		direccion:"<?php echo $info[0]["direccion"];?>",
		claveseguridad:"<?php echo $empresa[0]["claveseguridad"];?>",
		email:"<?php echo $info[0]["email"];?>",
		telefono:"<?php echo $info[0]["telefono"];?>",
		slogan:"<?php echo $empresa[0]["slogan"];?>",
		igvsunat:"<?php echo $empresa[0]["igvsunat"];?>",
		icbpersunat:"<?php echo $empresa[0]["icbpersunat"];?>",
		iscsunat:"<?php echo $empresa[0]["iscsunat"];?>",
		itemrepetircomprobante:"<?php echo $empresa[0]["itemrepetircomprobante"];?>",
		publicidad:"<?php echo $empresa[0]["publicidad"];?>",
		agradecimiento:"<?php echo $empresa[0]["agradecimiento"];?>",
		departamento:"<?php echo $info[0]["departamento"];?>",
		provincia:"<?php echo $info[0]["provincia"];?>",
		codubigeo:"<?php echo $info[0]["distrito"];?>",
		provinciacod:"<?php echo $info[0]["provincia"];?>",
		codubigeocod:"<?php echo $info[0]["codubigeo"];?>",
		leyendapamazonia :"<?php echo $empresa[0]["leyendapamazonia"];?>",
		codleyendapamazonia: "<?php echo $empresa[0]["codleyendapamazonia"];?>",
		leyendasamazonia :"<?php echo $empresa[0]["leyendasamazonia"];?>",
		codleyendasamazonia: "<?php echo $empresa[0]["codleyendasamazonia"];?>",
		urlconsultacomprobantes: "<?php echo $empresa[0]["urlconsultacomprobantes"];?>"
		};
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_empresa/configuraciones.js"> </script>