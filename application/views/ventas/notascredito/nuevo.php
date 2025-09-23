<div id="phuyu_operacion">
	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">

		<div class="phuyu_body">
			<div class="card">
				<div class="card-body">
					<div class="row">
						<div class="col-md-12 col-xs-12"> <h5><b>REGISTRO NUEVO NOTA DE CREDITO</b></h5> </div>
					</div>
		        	<div class="row form-group">
				    	<div class="col-md-3">
					    	<label>MOTIVO DE LA NOTA</label>
					    	<select class="form-select" name="codmotivonota" v-model="campos.codmotivonota" v-on:change="phuyu_motivos()" required>
					    		<?php
					    			foreach ($motivos as $key => $value) { ?>
					    				<option value="<?php echo $value["codmotivonota"];?>">
					    					<?php echo $value["descripcion"];?>
					    				</option>
					    			<?php }
					    		?>
					    	</select>
					    </div>
				    	<div class="col-md-4 mb-2">
				    		<div class="w-100">
						    	<label>CLIENTE DE LA VENTA</label>
				    			<select name="codpersona" id="codpersona" required >
				    				<option value="2">CLIENTES VARIOS</option>
				    			</select>
				    		</div>
					    </div>
					    <div class="col-md-5">
					    	<label>DESCRIPCION DE LA NOTA DE CREDITO</label>
					    	<input class="form-control" name="descripcion" v-model.trim="campos.descripcion" required autocomplete="off">
					    </div>
				    </div>
				    <div class="row form-group">
		    			<div class="col-md-3">
					    	<label class="text-center">COMPROBANTE DE REFERENCIA</label>
					    	<select class="form-select" name="codcomprobantetipo_ref" v-model="campos.codcomprobantetipo_ref" v-on:change="phuyu_series()">
					    		<option value="0">SELECCIONE</option>
					    		<?php 
					    			foreach ($tipocomprobantes as $key => $value) { ?>
					    				<option value="<?php echo $value["codcomprobantetipo"];?>"><?php echo $value["descripcion"];?></option>	
					    			<?php }
					    		?>
					    	</select>
					    </div>
					    <div class="col-md-3">
					    	<label class="text-center">FECHA COMPROBANTE REF.</label>
					    	<input type="date" class="form-control" id="fechacomprobante_ref" value="<?php echo date('Y-m-d');?>" autocomplete="off">
					    </div>
				    	<div class="col-md-2">
				    		<label>SERIE COMPROBANTE</label>
					    	<select class="form-select" name="seriecomprobante_ref" v-model="campos.seriecomprobante_ref" v-on:change="phuyu_comprobantes()">
					    		<option value="">SELECCIONE</option>
					    		<option v-for="dato in series_ref" v-bind:value="dato.seriecomprobante">{{dato.seriecomprobante}}</option>
					    	</select>
				    	</div>
				    	<div class="col-md-2">
				    		<label>SERIE NOTA CREDITO</label>
					    	<select class="form-select" name="seriecomprobante" v-model="campos.seriecomprobante" required="true">
					    		<option value="">SELECCIONE</option>
					    		<option v-for="dato in series" v-bind:value="dato.seriecomprobante">{{dato.seriecomprobante}}</option>
					    	</select>
				    	</div>
				    	<div class="col-md-2 mt-3">
				    		<label><br>&nbsp;</label>
				    		<button type="button" class="btn btn-success btn-block" v-on:click="phuyu_comprobantes()"><i data-acorn-icon="search"></i></button>
				    	</div>
				    </div>
				    <div class="row form-group">
				    	<div class="col-md-12" style="height:145px;overflow-y:auto;">
						    <table class="table table-striped" style="font-size: 11px">
						    	<thead>
						    		<tr>
						    			<th width="40%">RAZON SOCIAL</th>
						    			<th width="20%">COMPROBANTE</th>
						    			<th width="10%">FECHA</th>
						    			<th width="10%">IMPORTE</th>
						    			<th width="10%">REFER.</th>
						    			<th width="10%">SELECCIONAR</th>
						    		</tr>
						    	</thead>
						    	<thead>
						    		<tr v-for="dato in comprobantes" style="cursor:pointer;" v-bind:id="dato.codkardex">
						    			<td>{{dato.cliente}}</td>
						    			<td>{{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
						    			<td>{{dato.fechacomprobante}}</td>
						    			<td>{{dato.importe}}</td>
						    			
						    			<td v-if="dato.cantidadnota!=0">
						    				<button type="button" class="btn btn-xs btn-info" style="margin-bottom:-1px;">{{dato.cantidadnota}} NOTAS</button>
						    			</td>
						    			<td v-if="dato.cantidadnota==0">
						    				SIN NOTAS
						    			</td>
						    			<td v-if="dato.procesoestadonota!=0">
						    				<button type="button" class="btn btn-danger btn-block btn-xs" style="margin-bottom:-1px;">TERMINADO</button> 
						    			</td>
						    			<td v-if="dato.procesoestadonota==0">
						    				<button type="button" class="btn btn-success btn-block btn-xs" style="margin-bottom:-1px;" v-on:click="phuyu_detalle(dato)">
												<i class="fa fa-check"></i> SELECCIONAR
											</button> 
						    			</td>
						    		</tr>
						    	</thead>
						    </table>
						</div>
				    </div>
				    <hr>
			        <div class="row form-group table-responsive scroll-phuyu-view" >
						<table class="table table-striped" style="font-size: 11px">
							<thead>
								<tr>
									<th width="40%">PRODUCTO</th>
									<th width="15%">UNIDAD</th>
									<th width="10%">CANTIDAD</th>
									<th width="10%">P.&nbsp;UNITARIO</th>
									<th width="10%">I.G.V.</th>
									<th width="15%">SUBTOTAL</th>
						            <th width="1%"><i class="fa fa-trash-o"></i></th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="(dato,index) in detalle">
									<td>{{dato.producto}}</td>
									<td>{{dato.unidad}}</td>
									<td>
										<input type="number" step="0.0001" class="form-control number" v-model.number="dato.cantidad" v-on:keyup="phuyu_calcular(dato,3)" min="0.0001" required>
									</td>
									<td>
										<input type="number" step="0.0001" class="form-control number" v-model.number="dato.precio" v-on:keyup="phuyu_calcular(dato,3)" min="0.0001" required>
									</td>
									<td>{{dato.igv}}</td>
									<td>{{dato.subtotal}}</td>
									<td>
					    				<button type="button" class="btn btn-danger btn-block btn-xs" style="margin-bottom:-1px;" v-on:click="phuyu_quitardetalle(index,dato)">
											<b>X</b>
										</button>
					    			</td>
								</tr>
							</tbody>
							<tfoot>
								<tr>
									<td colspan="5" align="right"><b>Importe Nota Crédito S/. </b></td>
									<td><b class="text-danger">{{totales.importe}}</b></td>	
								</tr>
							</tfoot>
						</table>
					</div>

					<div class="row form-group">
						<div class="col-md-5"></div>
						<div class="col-md-7" align="right">
							<button type="submit" class="btn btn-success btn-icon" v-bind:disabled="estado==1"> <b><i data-acorn-icon="save"></i> GUARDAR NOTA</b> </button>
							<button type="button" class="btn btn-danger btn-icon" v-on:click="phuyu_cerrar()"> <b>ATRAS - CANCELAR</b> </button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_notas/nuevo.js"> </script>
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