
<style type="text/css">
	label{
		font-size: 11px !important;
	}
</style>
<div id="phuyu_operacion">
	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
		<div class="phuyu_body">
			<div class="card">
				<div class="card-body">
					<div class="row">
						<div class="col-md-6 col-xs-12"> <h5><b>NUEVO LINEA DE CREDITO</b></h5> </div>
					</div>
					<div class="row form-group">
						<div class="col-md-3">
							<label>SOCIO DE LA LINEA DE CREDITO</label>
			    			<select class="form-select" name="codsocio" id="codsocio" required>
			    			</select>
						</div>
						<div class="col-md-1 mt-4">
							<label>&nbsp;</label>
							<button type="button" class="btn btn-primary btn-icon" v-on:click="phuyu_addcliente()" title="AGREGAR CLIENTE"> 
								<i data-acorn-icon="user"></i>
							</button>
						</div>
						<div class="col-md-4">
							<label>DESCRIPCION</label>
							<input type="text" class="form-control" v-model="campos.cliente" name="">
						</div>
						<div class="col-md-2">
							<label>TIPO POSESION</label>
			    			<select class="form-select" name="tipoposesion" v-model="campos.tipoposesion" required>
					    		<option value="">SELECCIONE</option>
					    		<option value="0">PROPIA</option>
					    		<option value="1">ALQUILADA</option>
					    		<option value="2">ALQUILER COMPRA</option>	
					    	</select>
						</div>
						<div class="col-md-2">
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
						<div class="col-md-2">
				            <label>PROVINCIA</label>
				            <select class="form-select" name="provincia" v-model="campos.provincia" id="provincia" required v-on:change="phuyu_distritos()">
				                <option value="">SELECCIONE</option>
				            </select>
				        </div>
				        <div class="col-md-2">
				            <label>DISTRITO</label>
				            <select class="form-select" name="codubigeo" v-model="campos.codubigeo" id="codubigeo" required v-on:change="phuyu_zonas()">
				                <option value="">SELECCIONE</option>
				            </select>
				        </div>
						<div class="col-md-2">
							<label>ZONA</label>
							<div class="input-group">
					            <select class="form-select" name="codzona" v-model="campos.codzona" id="codzona" required>
					                <option value="">SELECCIONE</option>
					            </select>
					            <span class="input-group-btn">
									<button type="button" class="btn btn-success btn-icon" title="AGREGAR NUEVA ZONA" v-on:click="phuyu_nuevo_zona()"><i data-acorn-icon="plus"></i></button>
								</span>
							</div>
						</div>
						<div class="col-md-4 col-xs-12">
							<label>DIRECCION</label>
							<input type="text" class="form-control" id="direccion" v-model.trim="campos.direccion" autocomplete="off" maxlength="250" placeholder="Direccion del cliente . . ." required>
						</div>
						<div class="col-md-1 col-xs-12">
							<label>T. INTERES</label>
							<input type="number" class="form-control" v-model.trim="campos.tasainteres" required>
						</div>
						<div class="col-md-1 col-xs-12">
							<label>CRED. MAX</label>
							<input type="number" class="form-control" v-model.trim="campos.creditomaximo" required>
						</div>
					</div>
					<div class="row form-group">
						
						<div class="col-md-2 col-xs-12">
							<label>FECHA INICIO</label>
							<input type="date" class="form-control" id="fechainicio" value="<?php echo date("Y-m-d");?>">
						</div>
						<div class="col-md-2 col-xs-12">
							<label>FECHA FIN</label>
							<input type="date" class="form-control" id="fechafin" value="<?php echo date("Y-m-d");?>">
						</div>
						<div class="col-md-3 col-xs-10">
							<label>GARANTE DE LA LINEA DE CREDITO</label>
			    			<select class="form-select" name="codsocioreferencia" id="codsocioreferencia">
			    			</select>
						</div>

						<div class="col-md-2 col-xs-10">
							<label>SECTORISTA</label>
			    			<select class="form-select" name="codempleado" v-model="campos.codempleado" required>
					    		<option value="">SELECCIONE</option>
					    		<?php
					    			foreach ($empleados as $key => $value) { ?>
					    				<option value="<?php echo $value["codpersona"];?>"> <?php echo $value["razonsocial"];?> </option>
					    			<?php }
					    		?>
					    	</select>
						</div>
						<div class="col-md-1 col-xs-12">
							<label>AREA</label>
							<input type="number" class="form-control" v-model.trim="campos.area" required>
						</div>
						<div class="col-md-1" align="center">
							<label style="font-size:10px;">COMPRADO</label> <br>
							<input type="checkbox" style="height:20px;width:20px;" v-model="campos.comprado" name="comprado" id="comprado"> 
						</div>
						<div class="col-md-1">
							<label>ESTADO</label>
			    			<select class="form-select" name="estado" v-model="campos.estado" disabled>
					    		<option value="0">NORMAL</option>
					    		<option value="1">LIQUIDADO</option>
					    		<option value="2">ANULADO</option>	
					    	</select>
						</div>
					</div>
					<div class="row form-group">
						<div class="col-xs-12">
							<label>OBSERVACIONES</label>
							<textarea class="form-control" v-model="campos.observaciones"></textarea>
						</div>	
					</div>	
					<div class="ln_solid"></div>
					<div class="row form-group">
						<div class="col-md-4 col-xs-6">
						</div>
						<div class="col-md-8 col-xs-12" align="right">
							<button type="button" class="btn btn-warning btn-icon" v-on:click="phuyu_venta()"> 
								<b> <i data-acorn-icon="plus"></i> NUEVA LINEA CREDITO</b> 
							</button>
							<button type="submit" class="btn btn-info btn-icon" v-bind:disabled="estado==1"> 
								<b><i data-acorn-icon="save"></i> GUARDAR LINEA CREDITO</b> 
							</button>
							<button type="button" class="btn btn-danger btn-icon" v-on:click="phuyu_atras()"> 
								<b> <i data-acorn-icon="arrow-left"></i> ATRAS</b> 
							</button>
						</div>
					</div>
					<br>
				</div>
			</div>
		</div>
	</form>

	<div id="modal_reportes" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog" style="width:100%;margin:0px;">
			<div class="modal-content" align="center" style="border-radius:0px">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" style="font-size:30px;margin-bottom:0px;">
						<i class="fa fa-times-circle"></i> 
					</button>
					<h4 class="modal-title">
						<b style="letter-spacing:4px;"><?php echo $_SESSION["phuyu_empresa"]." - ".$_SESSION["phuyu_sucursal"];?> </b>
					</h4>
				</div>
				<div class="modal-body"  style="height:450px;padding:0px;">
					<iframe src="" style="width:100%; height:100%; border:none;"> </iframe>
				</div>
			</div>
		</div>
	</div>

	<div id="modal_zonas" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header modal-phuyu-titulo">
					<h5 class="modal-title"> <b style="letter-spacing:1px;">REGISTRAR NUEVA ZONA</b> </h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body" id="zonas_modal"> </div>
			</div>
		</div>
	</div>

</div>

<script src="<?php echo base_url();?>phuyu/phuyu_lineascredito/nuevo.js"> </script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas_2.js"> </script>

<script>
	var pantalla = jQuery(document).height(); $("#reportes_modal").css({height: pantalla - 65});
	if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }
    if (typeof Select2Controls !== 'undefined') {
      let select2Controls = new Select2Controls();
    }
</script>