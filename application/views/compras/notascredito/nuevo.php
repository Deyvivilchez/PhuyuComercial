<div id="phuyu_operacion">
	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
		<div class="phuyu_body">
			<div class="card">
				<div class="card-body">
					<div class="row form-group">
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
				    	<div class="col-md-4">
				    		<div class="w-100">
						    	<label>PROVEEDOR DE LA COMPRA</label>
				    			<select class="form-control" name="codpersona" id="codpersona" required>
				    				<option value="2">PROVEEDORES VARIOS</option>
				    			</select>
				    		</div>
					    </div>
					    <div class="col-md-5">
					    	<label>DESCRIPCION DE LA NOTA DE CREDITO</label>
					    	<input class="form-control" name="descripcion" v-model.trim="campos.descripcion" required autocomplete="off">
					    </div>
				    </div>

				    <div class="row form-group">
					    <div class="col-md-2">
					    	<label class="text-center">FECHA NOTA</label>
					    	<input type="date" class="form-control" id="fechacomprobante" value="<?php echo date('Y-m-d');?>" autocomplete="off">
					    </div>
					    <div class="col-md-2">
					    	<label class="text-center">FECHA COMPROBANTE REF.</label>
					    	<input type="date" class="form-control" id="fechacomprobante_ref" value="<?php echo date('Y-m-d');?>" autocomplete="off">
					    </div>
					    <div class="col-md-1">
					    	<label class="text-center">SERIE NOTA</label>
					    	<input type="text" class="form-control" v-model.trim="campos.seriecomprobante" maxlength="4" autocomplete="off" required>
					    </div>
					    <div class="col-md-2">
					    	<label class="text-center">NRO NOTA</label>
					    	<input type="text" class="form-control" v-model.trim="campos.nrocomprobante" maxlength="8" autocomplete="off" required>
					    </div>
				    	<div class="col-md-2">
				    		<label>&nbsp;</label>
				    		<button type="button" style="margin-top: 1.3rem" class="btn btn-success btn-icon" v-on:click="phuyu_comprobantes()"><i data-acorn-icon="search"></i></button>
				    	</div>
				    </div>
				    <div class="row form-group">
				    	<div class="col-md-6"></div>
				    	<div class="col-md-6" style="height:145px;overflow-y:auto;">
						    <table class="table table-bordered" style="font-size: 11px">
						    	<thead>
						    		<tr>
						    			<th>RAZON SOCIAL</th>
						    			<th width="10px">COMPROBANTE</th>
						    			<th width="80px">FECHA</th>
						    			<th width="10px">IMPORTE</th>
						    			<th width="10px">SELECCIONAR</th>
						    		</tr>
						    	</thead>
						    	<thead>
						    		<tr v-for="dato in comprobantes" style="cursor:pointer;" v-bind:id="dato.codkardex">
						    			<td>{{dato.cliente}}</td>
						    			<td>{{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
						    			<td>{{dato.fechacomprobante}}</td>
						    			<td>
						    				<b v-if="dato.codmoneda==1" style="font-size:17px;">S/.</b> 
											<b v-if="dato.codmoneda!=1" style="font-size:17px;">$</b> 
											{{dato.importe}}
										</td>
						    			<td v-if="dato.codmotivonota!=0">
						    				<button type="button" class="btn btn-danger btn-block btn-xs" style="margin-bottom:-1px;">CON NOTA</button> 
						    			</td>
						    			<td v-if="dato.codmotivonota==0">
						    				<button type="button" class="btn btn-success btn-block btn-xs" style="margin-bottom:-1px;" v-on:click="phuyu_detalle(dato)">
												<i class="fa fa-check"></i> SELECCIONAR
											</button> 
						    			</td>
						    		</tr>
						    	</thead>
						    </table>
						</div>
				    </div>				    
			        <div class="row table-responsive">
						<table class="table table-striped" style="font-size: 11px">
							<thead>
								<tr>
									<th width="40%">PRODUCTO</th>
									<th width="15%">UNIDAD</th>
									<th width="10%">CANTIDAD</th>
									<th width="10%">P.&nbsp;UNITARIO</th>
									<th width="10%">I.G.V.</th>
									<th width="14%">SUBTOTAL</th>
									<th width="1%"><i class="fa fa-trash-o"></i></th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="(dato,index) in detalle">
									<td>{{dato.producto}}</td>
									<td>{{dato.unidad}}</td>
									<td>
										<input type="number" step="0.0001" class="form-control number" v-model.number="dato.cantidad" v-on:keyup="phuyu_calcular(dato)" min="0.0001" required>
									</td>
									<td>
										<input type="number" step="0.0001" class="form-control number" v-model.number="dato.precio" v-on:keyup="phuyu_calcular(dato)" min="0.0001" required>
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
									<td colspan="5" align="right" style="font-size: 14px"><b>Total</b></td>
									<td style="font-size: 14px;color:red"><b>S/. {{totales.importe}}</b></td>
								</tr>
							</tfoot>
						</table>
					</div>
					<div class="row form-group">						
						<div class="col-md-5">
						</div>
						<div class="col-md-7" align="right">
							<button type="submit" class="btn btn-primary btn-icon" v-bind:disabled="estado==1"> <b><i data-acorn-icon="save"></i> GUARDAR NOTA</b> </button>
							<button type="button" class="btn btn-danger btn-icon" v-on:click="phuyu_cerrar()"> <b>ATRAS - CANCELAR</b> </button>
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
<script src="<?php echo base_url();?>phuyu/phuyu_notas/notacompra.js"> </script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas_2.js"> </script>

<script>
	var pantalla = jQuery(document).height(); $("#reportes_modal").css({height: pantalla - 65});
</script>