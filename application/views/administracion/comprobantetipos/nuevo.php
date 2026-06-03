<div id="phuyu_form">
	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
		<br> <input type="hidden" name="codregistro" v-model="campos.codregistro">

		<div class="row form-group">
			<div class="col-md-12">
				<label>DESCRIPCION</label>
	        	<input type="text" name="descripcion" v-model.trim="campos.descripcion" class="form-control" required autocomplete="off" placeholder="Descripcion . . ." />
			</div>
		</div>
		<div class="row form-group">
			<div class="col-md-12">
				<label>ABREVIATURA</label>
	        	<input type="text" name="abreviatura" v-model.trim="campos.abreviatura" class="form-control" autocomplete="off" placeholder="Abreviatura . . ." />
			</div>
		</div>
		<div class="row form-group">
			<div class="col-md-12">
				<label>CODIGO OFICIAL</label>
	        	<input type="text" name="oficial" v-model.trim="campos.oficial" class="form-control" autocomplete="off" placeholder="Codigo Oficial . . ." />
			</div>
		</div><br>
		<div class="row form-group"><div class="col-xs-12" align="center"><b>PROCESOS DONDE ESTARÁ EL COMPROBANTE</b></div></div>
		<div class="row form-group">
			<div class="col-md-1"></div>
			<div class="col-md-3">
				<div class="form-check form-switch">
					<input class="form-check-input" style="width: 3em" v-if="campos.venta==1" type="checkbox" id="venta" checked v-on:click="phuyu_proceso(1)" />
					<input class="form-check-input" style="width: 3em" v-else="campos.venta!=1" type="checkbox" id="venta" v-on:click="phuyu_proceso(1)" />
					<label class="form-check-label" style="margin-top: .2em" for="venta">&nbsp;VENTA</label>
				</div>
			</div>
			<div class="col-md-3">
				<div class="form-check form-switch">
					<input class="form-check-input" style="width: 3em" v-if="campos.compra==1" type="checkbox" id="compra" checked v-on:click="phuyu_proceso(2)" />
					<input class="form-check-input" style="width: 3em" v-else="campos.compra!=1" type="checkbox" id="compra" v-on:click="phuyu_proceso(2)" />
					<label class="form-check-label" style="margin-top: .2em" for="compra">&nbsp;COMPRA</label>
				</div>
			</div>
			<div class="col-md-5">
				<div class="form-check form-switch">
					<input class="form-check-input" style="width: 3em" v-if="campos.ingreso==1" type="checkbox" id="ingreso" checked v-on:click="phuyu_proceso(3)" />
					<input class="form-check-input" style="width: 3em" v-else="campos.ingreso!=1" type="checkbox" id="ingreso" v-on:click="phuyu_proceso(3)" />
					<label class="form-check-label" style="margin-top: .2em" for="ingreso">&nbsp;INGRESO ALMACEN</label>
				</div>
			</div>
		</div>
		<div class="row form-group">
			<div class="col-md-1"></div>
			<div class="col-md-5">
				<div class="form-check form-switch">
					<input class="form-check-input" style="width: 3em" v-if="campos.egreso==1" type="checkbox" id="egreso" checked v-on:click="phuyu_proceso(4)" />
					<input class="form-check-input" style="width: 3em" v-else="campos.egreso!=1" type="checkbox" id="egreso" v-on:click="phuyu_proceso(4)" />
					<label class="form-check-label" style="margin-top: .2em" for="egreso">&nbsp;SALIDA ALMACEN</label>
				</div>
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
var campos = {codregistro:"",descripcion: "",abreviatura:"",oficial:"",venta:0,compra:0,ingreso:0,egreso:0}; </script>
<script src="<?php echo base_url();?>phuyu/phuyu_form.js"></script>