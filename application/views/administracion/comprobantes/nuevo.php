<div id="phuyu_form">
	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
		<br> <input type="hidden" name="codregistro" v-model="campos.codregistro">
		<input type="hidden" v-model="campos.seriecomprobante_editar" name="seriecomprobante_editar">
		<input type="hidden" v-model="campos.codsucursal_editar" name="codsucursal_editar">
		<input type="hidden" v-model="campos.codcomprobantetipo_editar" name="codcomprobantetipo_editar">
		<input type="hidden" v-model="campos.logo" name="logo">
		<input type="hidden" v-model="campos.logoauspiciador" name="logoauspiciador">
		<div class="row form-group">
			<div class="col-md-12">
				<label>SELECCIONAR SUCURSAL</label>
	        	<select class="form-select" id="codsucursal" v-model="campos.codsucursal" name="codsucursal" required v-on:change="phuyu_tipocomprobante()">
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
				<label>TIPO COMPROBANTE</label>
	        	<select class="form-select" id="codcomprobantetipo" v-model="campos.codcomprobantetipo" required v-on:change="phuyu_tipocomprobante()" name="codcomprobantetipo">
	        		<option value="">SELECCIONE</option>
	        		<?php 
	        			foreach ($tipos as $key => $value) { ?>
	        				<option value="<?php echo $value["codcomprobantetipo"];?>"><?php echo $value["descripcion"];?></option>
	        			<?php }
	        		?>
	        	</select>
			</div>
		</div>
		<div class="row form-group" v-if="caja">
			<div class="col-md-12">
				<label>SELECCIONE CAJA</label>
	        	<select class="form-select" name="codcaja" v-model="campos.codcaja" id="codcaja" required v-on:change="phuyu_caja()">
	        		<option value="">SELECCIONE</option>
	        	</select> <br>
	        	<div class="alert alert-danger" v-if="caja_alerta" align="center"> 
	        		<strong>YA TIENE REGISTRADO UNA SERIE PARA ESTA CAJA <br> <b style="font-size:16px;">CAMBIAR DE CAJA O TIPO COMPROBANTE</b></strong> 
	        	</div>
			</div>
		</div>
		<div class="row form-group" v-if="almacen">
			<div class="col-md-12">
				<label>SELECCIONE ALMACEN</label>
	        	<select class="form-select" name="codalmacen" v-model="campos.codalmacen" id="codalmacen" required v-on:change="phuyu_almacen()">
	        		<option value="">SELECCIONE</option>
	        	</select> <br>
	        	<div class="alert alert-danger" v-if="almacen_alerta" align="center"> 
	        		<strong>YA TIENE REGISTRADO UNA SERIE PARA ESTE ALMACEN <br> <b style="font-size:16px;">CAMBIAR DE ALMACEN O TIPO DE COMPROBANTE</b></strong> 
	        	</div>
			</div>
		</div>
		<div class="row form-group" v-if="nota">
			<div class="col-md-12">
				<label>SELECCIONE COMPROBANTE Y SERIE REFERENCIA</label>
	        	<select class="form-select" name="codcomprobantetipo_ref" v-model="campos.codcomprobantetipo_ref" id="codcomprobantetipo_ref" required v-on:change="phuyu_notas()">
	        		<option value="">SELECCIONE</option>
	        	</select> <br>
	        	<div class="alert alert-danger" v-if="nota_alerta" align="center"> 
	        		<strong>YA TIENE REGISTRADO UNA SERIE PARA ESTA NOTA ELECTRONICA <br> <b style="font-size:16px;">CAMBIAR DE TIPO DE COMPROBANTE O SERIE</b></strong> 
	        	</div>
			</div>
		</div>
		<div class="row form-group">
			<div class="col-md-4 col-xs-12">
				<label>SERIE</label>
	        	<input type="text" id="seriecomprobante" v-model.trim="campos.seriecomprobante" class="form-control" required autocomplete="off" minlength="4" maxlength="4" style="text-transform:uppercase;" name="seriecomprobante" />
			</div>
			<div class="col-md-4 col-xs-12">
				<label>NRO INICIAL</label>
	        	<input type="number" name="nroinicial" v-model.number="campos.nroinicial" class="form-control" required autocomplete="off" placeholder="Nro inicial . . ." />
			</div>
			<div class="col-md-4 col-xs-12">
				<label>CORRELATIVO</label>
	        	<input type="number" name="nrocorrelativo" v-model.number="campos.nrocorrelativo" class="form-control" required autocomplete="off" placeholder="Nro Correlativo . . ." />
			</div>
		</div>
 <br>
		<div class="row form-group">
			<div class="col-md-2"></div>
			<div class="col-md-10 col-xs-12">
				<div class="form-check form-switch">
					<input class="form-check-input" style="width: 3em" v-if="campos.impresion==1" type="checkbox" id="flexSwitchCheckChecked" checked v-on:click="phuyu_impresion()" />
					<input class="form-check-input" style="width: 3em" v-else="campos.impresion!=1" type="checkbox" id="flexSwitchCheckChecked" v-on:click="phuyu_impresion()" />
					<label class="form-check-label" style="margin-top: .2em" for="flexSwitchCheckChecked">&nbsp;CONFIGURAR IMPRESION DEL COMPROBANTE</label>
				</div>
			</div>
		</div>
		<div class="row form-group" v-show="campos.impresion==1">
			<div class="col-md-6 col-xs-12">
				<label>FORMATO</label>
	        	<select v-model="campos.formato" class="form-select" required>
	        		<option value="a4">A4</option>
	        		<option value="a5">A5</option>
	        		<option value="ticket">TICKET</option>
	        	</select>
			</div>
			<div class="col-md-6 col-xs-12">
				<label>ORIENTACIÓN</label>
				<select v-model="campos.orientacion" class="form-select" required>
	        		<option value="h">HORIZONTAL</option>
	        		<option value="p">VERTICAL</option>
	        	</select>
			</div>
		</div>
		<div class="row form-group" v-show="campos.impresion==1">
			<div class="col-md-6 col-xs-12">
				<label>TIPO LEYENDA</label>
				<select v-model="campos.tipoconleyendaamazonia" class="form-select" name="tipoconleyendaamazonia" required>
	        		<option value="1">LEYENDA BIENES</option>
	        		<option value="2">LEYENDA SERVICIOS</option>
	        		<option value="3">LEYENDA BIENES Y SERVICIOS</option>
	        	</select>
			</div>
			<div class="col-md-6 col-xs-12">
				<label>IMPRESION LOGO</label>
	        	<select class="form-select" v-model="campos.impresionlogo" name="impresionlogo" required>
	        		<option value="1">PARCIAL</option>
	        		<option value="2">TOTAL</option>
	        	</select>
			</div>
		</div>
		<div class="row form-group" v-show="campos.impresion==1">
			<div class="col-md-6">
		        <label>LOGO SERIE</label>
		        <input type="file" class="form-control" name="logoa" accept="image/*">
		    </div>
		    <div class="col-md-6">
		        <label>LOGO AUSPICIADOR</label>
		        <input type="file" class="form-control" name="auspiciadora" accept="image/*">
		    </div>
		</div>
		<div class="row form-group" v-show="campos.impresion==1">
			<div class="col-md-12">
				<label>NOMBRE COMERCIAL</label>
				<input type="text" class="form-control" maxlength="100" v-model="campos.nombrecomercial" name="nombrecomercial">
			</div>
		</div>
		<div class="row form-group" v-show="campos.impresion==1">
			<div class="col-xs-12">
		        <label>SLOGAN EMPRESA</label>
		        <textarea class="form-control" name="slogan" v-model="campos.slogan" placeholder="Slogan . . ." autocomplete="off" rows="3"></textarea>
		    </div>
		</div>
		<div class="row form-group" v-show="campos.impresion==1">
			<div class="col-xs-12">
		        <label>PUBLICIDAD</label>
		        <textarea class="form-control" name="publicidad" v-model="campos.publicidad" placeholder="Publicidad . . ." autocomplete="off" rows="1"></textarea>
		    </div>
		</div>
		<div class="row form-group" v-show="campos.impresion==1">
			<div class="col-xs-12">
		        <label>AGRADECIMIENTO</label>
		        <textarea class="form-control" name="agradecimiento" v-model="campos.agradecimiento" placeholder="Agradecimiento . . ." autocomplete="off" rows="1"></textarea>
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
	var campos = {codregistro:"",codsucursal:"",codcomprobantetipo:"",codcaja:"",codalmacen:"",codcomprobantetipo_ref:"",seriecomprobante:"",seriecomprobante_editar:"",nroinicial:"",nrocorrelativo:"",impresion:0,formato:"a4",orientacion:"p",impresora:"",slogan:"",publicidad:"",agradecimiento:"",logo:"",logoauspiciador:"",tipoconleyendaamazonia:1,nombrecomercial:"",impresionlogo:1}; 
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_caja/comprobantes.js?v=<?php echo uniqid(); ?>"></script>