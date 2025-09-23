<div id="phuyu_operacion">
	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" id="comprobante" value="<?php echo $comprobantes[0]['codcomprobantetipo'];?>">
		<input type="hidden" id="codkardex" value="<?php echo $salida[0]['codkardex'];?>" name="">
		<input type="hidden" id="codpersonas" value="<?php echo $salida[0]['codpersona']?>" name="">
		<input type="hidden" id="destinatarios" value="<?php echo $salida[0]['cliente'];?>" name="">
		<input type="hidden" id="almacen_principal" value="<?php echo $salida[0]['codalmacen'];?>" name="">
		<div class="row form-group">
			<div class="col-md-3 col-xs-12">
		    	<label>SERIE <b>(NRO: {{campos.nro}})</b></label>
	        	<select class="form-select requeridogeneral" id="seriecomprobante" v-model="campos.seriecomprobante" v-on:change="phuyu_correlativo()">
		    		<option v-for="dato in series" v-bind:value="dato.seriecomprobante"> 
		    			{{dato.seriecomprobante}}
		    		</option>
		    	</select>
		    </div>
		    <div class="col-md-3 col-xs-12">
		    	<label>FECHA TRASLADO<span class="text-danger">*</span></label>
		    	<input type="date" class="form-control requeridogeneral" id="fechatraslado" name="fechatraslado" value="<?php echo date('Y-m-d');?>" required>
		    </div>
			<div class="col-md-6">
	    		<label>MODALIDAD DE TRASLADO<span class="text-danger">*</span></label>
	    		<select class="form-select requeridogeneral" name="modotraslado" v-model="campos.codmodalidadtraslado" id="modotraslado" required>
	    			<option value="">SELECCIONE</option>
	    			<?php
		    			foreach ($modalidades as $key => $value) { ?>
		    				<option value="<?php echo $value["codmodalidadtraslado"];?>" codigo="<?php echo $value["oficial"]?>">
		    					<?php echo $value["modalidadtraslado"];?>
		    				</option>
		    			<?php }
		    		?>
	    		</select>
	    	</div>
        </div>
        <div class="row form-group">
	    	<div class="col-md-2">
	    		<label>PESO TOTAL<span class="text-danger">*</span></label>
	    		<input type="text" readonly class="form-control requeridogeneral" v-model="campos.peso" required name="pesobultos" id="pesobultos" value="0">
	    	</div>
	    	<div class="col-md-2">
	    		<label>TOTAL DE BULTOS</label>
	    		<input type="number" class="form-control" name="totalbultos" v-model="campos.nropaquetes" id="totalbultos" value="0">
	    	</div>
	    	<div class="col-md-8">
	    		<label>OBSERVACIONES</label>
	    		<textarea class="form-control" name="observacion" v-model="campos.observaciones" id="observacion" rows="1"></textarea>
	    	</div>
		</div><hr>
		<div class="row form-group">
			<div class="col-md-6">
				<label>DIRECCION DE PARTIDA<span class="text-danger">*</span></label>
				<input type="text" class="form-control" id="direccionpartida" required value="<?php echo $almacen_partida[0]['direccion'];?>" name="">
				<input type="hidden" id="ubigeopartida" value="<?php echo $almacen_partida[0]['codubigeo'];?>" name="">
			</div>
			<div class="col-md-6">
				<label>DIRECCION DE DESTINO<span class="text-danger">*</span></label>
				<input type="text" class="form-control" id="direccionllegada" required value="<?php echo $almacen_destino[0]['direccion'];?>" name="">
				<input type="hidden" id="ubigeollegada" value="<?php echo $almacen_destino[0]['codubigeo'];?>" name="">
			</div>
		</div>
		<h4>DATOS DEL TRANSPORTISTA</h4>
		<div class="row form-group">
			<div class="col-md-9">
				<label>TRANSPORTISTA<span class="text-danger">*</span></label>
    			<select class="form-control" name="codtransportista" id="codtransportista" required>
    				<option value="">SELECCIONE TRANSPORTISTA</option>
    			</select>
			</div>
			<div class="col-md-3">
				<label>DOCUMENTO</label>
				<input type="text" class="form-control" v-model="campos.documentotransportista" name="documentotransportista" readonly>
			</div>
		</div>
		<h4>DATOS DEL CONDUCTOR</h4>
		<div class="row form-group">
			<div class="col-md-6 col-xs-10">
				<label>CONDUCTOR<span class="text-danger">*</span></label>
    			<select class="form-control" name="codconductor" id="codconductor" required>
    				<option value="">SELECCIONE CONDUCTOR</option>
    			</select>
			</div>
			<div class="col-md-3">
				<label>DOCUMENTO</label>
				<input type="text" class="form-control" name="documentoconductor" v-model="campos.documentoconductor" readonly>
			</div>
			<div class="col-md-3">
				<label>LICENCIA</label>
				<input type="text" class="form-control" name="licenciaconductor" v-model="campos.licenciaconductor" >
			</div>
		</div>
		<div class="row form-group">
			<div class="col-md-6 col-xs-12">
				<label>NRO PLACA VEHICULO<span class="text-danger">*</span></label>
				<select class="form-control" name="codvehiculo" id="codvehiculo" required>
    				<option value="">SELECCIONE VEHICULO</option>
    			</select>
			</div>
			<div class="col-md-6 col-xs-12">
				<label>CONSTANCIA DE INSCRIPCION</label>
				<input type="text" class="form-control" id="constancia" v-model.trim="campos.constancia" autocomplete="off" maxlength="100" autocomplete="off" placeholder="Constancia" >
			</div>
		</div><br>
		<div class="row form-group"><div class="col-md-12 text-center"><h4><label>DETALLE DE LA GUIA</label></h4></div></div>
		<div class="phuyu_body_row table-responsive scroll-phuyu-view" style="height:calc(100vh - 414px);padding:0px; overflow:auto;">
			<div class="col-md-12">
				<table class="table table-bordered table-striped" style="font-size: 11px">
					<thead>
						<tr>
							<th width="40%">PRODUCTO</th>
							<th width="19%">UNIDAD</th>
							<th width="20%">CANTIDAD</th>
							<th width="20%">PESO UNIT.</th>
							<th width="1%"> <i class="fa fa-trash-o"></i> </th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="(dato,index) in detalle">
							<td style="font-size:10px;">{{dato.producto}}</td>
							<td> <input type="hidden" v-model="dato.codunidad">{{dato.unidad}} </td>
							<td>
								<input type="number" step="0.0001" class="form-control number" v-model.number="dato.cantidad" min="0.0001" required>
							</td>
							<td>
								<input type="number" step="0.0001" class="form-control number" v-on:keyup="phuyu_calcular(dato)" v-model.number="dato.pesoitem" min="0" required>
							</td>
							<td> 
								<button type="button" class="btn btn-danger btn-block btn-xs" style="margin-bottom:-1px;" v-on:click="phuyu_deleteitem(index,dato)">
									<b>X</b>
								</button>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div class="row form-group">
			<div class="col-md-12">
				<center>
					<button type="submit" class="btn btn-primary">GUARDAR GUIA</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">CANCELAR</button>
				</center>
			</div>
		</div>
	</form>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_almacen/nuevaguiasalida.js"> </script>
<script src="<?php echo base_url();?>phuyu/phuyu_guias/selects.js"> </script>