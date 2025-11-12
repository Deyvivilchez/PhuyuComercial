<div id="phuyu_form">
	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()"> <br>
		<input type="hidden" name="codregistro" v-model="campos.codregistro">
        <input type="hidden"  name="" id="paraventa" v-model="campos.paraventa">
        <input type="hidden"  name="" v-model.trim="campos.codatencion">
        <input type="hidden"  name="" id="calcular" v-model="campos.calcular">
		<input type="hidden" id="afectoicbper" v-model="campos.afectoicbper">
		<input type="hidden" id="codafectoigv" value="<?php echo $_SESSION["phuyu_afectacionigv"]; ?>">
		<div class="row form-group">
			<div class="col-md-2 col-xs-6">
				<label>CODIGO</label>
	        	<input type="text" id="codigo" v-model.trim="campos.codigo" class="form-control" autocomplete="off" placeholder="Codigo . . ." />
			</div>
			<div class="col-md-2 col-xs-6"> 
	    		<label>TIPO PRODUCTO</label>
	    		<select id="tipo" v-model="campos.tipo" class="form-select">
	        		<option value="1">BIEN</option>
	        		<option value="2">SERVICIO</option>
	        	</select>
	    	</div>
			<div class="col-md-8 col-xs-12">
				<label>DESCRIPCION PRODUCTO <span class="text-danger">(*)</span></label>
	        	<input type="text" id="descripcion" v-model="campos.descripcion" class="form-control" autocomplete="off" placeholder="Descripcion . . ." maxlength="100" required />
			</div>
		</div>
		<div class="row form-group">
			<div class="col-md-4 col-xs-12">
				<label>FAMILIA PRODUCTO <span class="text-danger">(*)</span></label>
				<div class="input-group">
					<select class="form-select" id="codfamilia" v-model="campos.codfamilia" required>
			        	<option value="">SELECCIONE ...</option>
			        	<option v-for="dato in familias" v-bind:value="dato.codfamilia"> {{dato.descripcion}} </option>
			        </select>
					<span class="input-group-btn">
						<button type="button" class="btn btn-icon btn-icon-end btn-primary" v-on:click="phuyu_nuevo_extencion('almacen/familias')"><i data-acorn-icon="plus"></i></button>
					</span>
				</div>
			</div>
			<div class="col-md-4 col-xs-12">
				<label>LINEA PRODUCTO <span class="text-danger">(*)</span></label>
				<div class="input-group">
					<select class="form-select" id="codlinea" v-model="campos.codlinea" required>
			        	<option value="">SELECCIONE ...</option>
			        	<option v-for="dato in lineas" v-bind:value="dato.codlinea"> {{dato.descripcion}} </option>
			        </select>
					<span class="input-group-btn">
						<button type="button" class="btn btn-icon btn-icon-end btn-primary" v-on:click="phuyu_nuevo_extencion('almacen/lineas')"><i data-acorn-icon="plus"></i></button>
					</span>
				</div>
			</div>
			<div class="col-md-4 col-xs-12">
				<label>MARCA PRODUCTO <span class="text-danger">(*)</span></label>
				<div class="input-group">
					<select class="form-select" id="codmarca" v-model="campos.codmarca" required>
			        	<option value="">SELECCIONE ...</option>
			        	<option v-for="dato in marcas" v-bind:value="dato.codmarca"> {{dato.descripcion}} </option>
			        </select>
					<span class="input-group-btn">
						<button type="button" class="btn btn-icon btn-icon-end btn-primary" v-on:click="phuyu_nuevo_extencion('almacen/marcas')"><i data-acorn-icon="plus"></i></button>
					</span>
				</div>
			</div>
        </div>
	    <div class="row form-group">

			<div class="col-md-12 col-xs-12">
				<label>CARACTERISTICAS PRODUCTO</label>
	        	<input type="text" v-model="campos.caracteristicas" class="form-control" autocomplete="off" placeholder="Caracteristicas . . ." maxlength="255" />
			</div>
		</div> 
		<div class="row form-group">
	    	<div class="col-md-3">
	    		<label>AFECTACION IGV COMPRA</label>
				<select name="codafectacionigvcompra" v-model="campos.codafectacionigvcompra" class="form-select" required="">
	        		<option value="">SELECCIONE</option>
	        		<?php
		        	foreach ($afectacionigv as $key => $value) { ?>
		        		<option value="<?php echo $value["codafectacionigv"]?>"><?php echo $value["descripcion"]?></option>
		        	<?php }
		        	?>
	        	</select>
	    	</div>
	    	<div class="col-md-3">
	    		<label>AFECTACION IGV VENTA</label>
				<select name="codafectacionigvventa" v-model="campos.codafectacionigvventa" class="form-select" required="">
	        		<option value="">SELECCIONE</option>
	        		<?php
		        	foreach ($afectacionigv as $key => $value) { ?>
		        		<option value="<?php echo $value["codafectacionigv"]?>"><?php echo $value["descripcion"]?></option>
		        	<?php }
		        	?>
	        	</select>
	    	</div>
	    	<div class="col-md-3">	
			<div class="form-check form-switch">
				<input class="form-check-input" v-if="campos.afectoicbper==0" type="checkbox" id="afectoicbper_check" v-on:click="phuyu_activaricbper()" />
				<input class="form-check-input" v-if="campos.afectoicbper!=0" type="checkbox" id="afectoicbper_check" checked v-on:click="phuyu_activaricbper()" />
				<label class="form-check-label" for="afectoicbper_check">ICBPER</label>
			</div>    		
			<div class="form-check form-switch">
				<input class="form-check-input" v-if="campos.controlstock==1" type="checkbox" id="stock" checked v-on:click="phuyu_activarstock()" />
				<input class="form-check-input" v-if="campos.controlstock!=1" type="checkbox" id="stock" v-on:click="phuyu_activarstock()" />
				<label class="form-check-label" for="stock">CONTROLA STOCK</label>
			</div>
			<!-- En lugar de dos inputs condicionales, usa uno solo: -->
			<div class="form-check form-switch">
			<!-- 	<input class="form-check-input" type="checkbox" id="controlarseries" v-model="campos.controlarseries" true-value="1" false-value="0"> -->
				<input class="form-check-input" type="checkbox" id="controlarseries" v-model="campos.controlarseries" true-value="1" false-value="0">
				<label class="form-check-label" for="controlarseries">CONTROLAR SERIES</label>
			</div>
		</div>

	    	<div class="col-md-3 col-xs-6">
				<label>COMISION(%)</label>
				<input type="hidden" name="codproducto" id="codproducto">
				<input type="number" class="form-control" v-model="campos.comisionvendedor" name="">
			</div>
	    </div>

	    <hr>
		<div class="row form-group">
		    <div class="col-md-5 col-xs-12">
                <button type="button" class="btn btn-success btn-icon btn-icon-end btn-block" style="margin-top: 8px" v-on:click="phuyu_addunidad()"><i data-acorn-icon="plus"></i> Agregar Unidad de Medida</button>
	    	</div>
		</div>
		<div class="row form-group">	
			<div class="table-responsive">
				<table class="table table-bordered table-condensed" style="width: 130%">
					<thead>
						<th>UNIDAD</th>
						<th>FACTOR</th>
						<th>P.COMPRA</th>
						<th>P.VENTA</th>
						<th>P.MINIMO</th>
						<th>P.CREDITO</th>
						<th>P.MAYOR</th>
						<th>P.OTROS</th>
						<th>C.BARRA</th>
						<th>ELIMINAR</th>
					</thead>
					<tbody style="font-size:13px;">
						<tr v-for="(uni,index) in unidades" :key="uni.factor">
							<td>
								<select class="form-select number" v-bind:disabled="editar==1 && uni.factor==1" v-model="uni.codunidad" required>
	                                <?php 
	                                    foreach ($unidades as $key => $value) { ?>
	                                        <option value="<?php echo $value["codunidad"];?>"><?php echo $value["descripcion"];?></option>
	                                    <?php }
	                                ?>
	                            </select>
							</td>
							<td>
								<input type="number" step="0.1" class="form-control number" v-model.number="uni.factor" min="1" required>
							</td>
							<td>
								<input type="number" step="0.0001" class="form-control number" v-model.number="uni.preciocompra" min="0" required>
							</td>
							<td>
								<input type="number" step="0.0001" class="form-control number" v-model.number="uni.pventapublico" min="0.1" required>
							</td>
							<td>
								<input type="number" step="0.0001" class="form-control number" v-model.number="uni.pventamin" min="0" required>
							</td>
							<td>
								<input type="number" step="0.0001" class="form-control number" v-model.number="uni.pventacredito" min="0" required>
							</td>
							<td>
								<input type="number" step="0.0001" class="form-control number" v-model.number="uni.pventaxmayor" min="0" required>
							</td>
							<td>
								<input type="number" step="0.0001" class="form-control number" v-model.number="uni.pventaadicional" min="0" required>
							</td>
							<td>
								<input type="text" class="form-control number" v-model.number="uni.codigobarra">
							</td>
							<td>
								<button type="button" class="btn btn-danger btn-xs btn-table" v-on:click="phuyu_deleteunidad(index,uni)" v-bind:disabled="editar==1 && uni.factor==1">ELIMINAR</button>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div class="form-group text-center"> <br>
			<button type="submit" class="btn btn-success" v-bind:disabled="estado==1"> <i class="fa fa-save"></i> GUARDAR </button>
			<button type="button" class="btn btn-danger" v-on:click="phuyu_cerrar()">CERRAR</button>
		</div>
	</form>

	<div id="modal_extencion" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-sm">
			<div class="modal-content" style="border: 2px solid #747474;">
				<div class="modal-body" id="extencion_modal"> </div>
			</div>
		</div>
	</div>
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
		codregistro:"",
		descripcion:"",
		codfamilia:0,
		codlinea:0,
		codmarca:0,
		codigo:"",
		codigobarra:"",
		calcular:"0",
		controlstock:1,
		tipo:"1",
		codafectacionigvcompra:"1",codafectacionigvventa:"9",
		afectoicbper:"0",codatencion:"0",paraventa:"0", caracteristicas:"",
		comisionvendedor:0,
		controlarseries:0,
	};
	var campos_1 = {codunidad:"",
	unidad:"",factor:"1",preciocompra:"0.00",
	pventapublico:"0.00",pventamin:"0.00",pventacredito:"0.00",pventaxmayor:"0.00",pventaadicional:"0.00",codigobarra:""};
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_almacen/productos.js"></script>