<div id="phuyu_operacion">
	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" id="igvsunat" value="<?php echo $_SESSION["phuyu_igv"];?>">

		<div class="phuyu_body">
			<div class="card">
				<div class="card-body">
				    <div class="row form-group">
						<div class="col-md-12 col-xs-12"> <h5><b>REGISTRO NUEVA SALIDA ALMACEN</b></h5> </div>
					</div>
		        	<div class="row mb-2">
		        		<div class="col-md-3">
					    	<label>PERSONA RESPONSABLE</label>
					    	<select class="form-select" name="codpersona" v-model="campos.codpersona" id="codpersona" disabled>
			    				<option value="1"><?php echo $_SESSION["phuyu_empresa"];?></option>
			    			</select>
					    </div>
		        		<div class="col-md-3 col-xs-12">
					    	<label>TIPO MOVIMIENTO</label>
					    	<select class="form-select" name="codmovimientotipo" v-model="campos.codmovimientotipo" required disabled>
					    		<option value="">SELECCIONE  . . .</option>
					    		<?php
					    			foreach ($movimientos as $key => $value) { ?>
					    				<option value="<?php echo $value["codmovimientotipo"];?>">
					    					<?php echo $value["descripcion"];?>
					    				</option>
					    			<?php }
					    		?>
					    	</select>
					    </div>
				    	<div class="col-md-2 col-xs-12">
					    	<label>ALMACEN ORIGEN</label>
					    	<input type="text" class="form-control" value="<?php echo $_SESSION['phuyu_almacen']?>" readonly>
					    </div>
				    	<div class="col-md-1 col-xs-6">
					    	<label>COMPBTE</label>
					    	<?php 
					    		if (count($serie)==0) { ?>
					    			<input type="text" class="form-control" readonly value="NO TIENE" style="border:2px solid #d43f3a"> 
					    			<span style="display:none">{{estado = 1}}</span>
					    		<?php }else{ ?>
					    			<input type="text" class="form-control" readonly value="<?php echo $serie[0]["comprobante"];?>">
					    		<?php }
					    	?>
					    </div>
					    <div class="col-md-1 col-xs-6">
					    	<label>SERIE</label>
					    	<input type="text" class="form-control" name="seriecomprobante" v-model="campos.seriecomprobante" readonly>
					    	
					    	<?php 
					    		if (count($serie)>0) { ?>
					    			<span style="display:none;">
							    		{{campos.codcomprobantetipo = '<?php echo $serie[0]["codcomprobantetipo"];?>'}}
							    		{{campos.seriecomprobante = '<?php echo $serie[0]["seriecomprobante"];?>'}}
							    	</span>
					    		<?php }
					    	?>
					    </div>
					    <div class="col-md-2 col-xs-12">
					    	<label>FECHA SALIDA</label>
					    	<input type="date" class="form-control" name="fechakardex" id="fechakardex" value="<?php echo date('Y-m-d');?>" autocomplete="off" required>
					    </div>
				    </div>
				    <div class="row mb-2" v-if="campos.codmovimientotipo==30">
				    	<div class="col-md-12">
					    	<label>ALMACEN DESTINO</label>
					    	<select class="form-select" name="codalmacen_ref" v-model="campos.codalmacen_ref" required>
					    		<option value="">SELECCIONE  . . .</option>
					    		<?php
					    			foreach ($almacenes as $key => $value) { ?>
					    				<option value="<?php echo $value["codalmacen"];?>">
					    					<?php echo $value["descripcion"]." - ".$value["sucursal"];?>
					    				</option>
					    			<?php }
					    		?>
					    	</select>
					    </div>
				    </div>
				    <div class="row mb-2">
				    	<div class="col-md-3 col-xs-12">
					    	<label>COMPROBANTE REFERENCIA</label>
					    	<select class="form-select" name="codcomprobantetipo_ref" v-model="campos.codcomprobantetipo_ref">
					    		<option value="0">SIN COMPROBANTE DE REFERENCIA</option>
					    		<?php 
					    			foreach ($tipocomprobantes as $key => $value) { ?>
					    				<option value="<?php echo $value["codcomprobantetipo"];?>"><?php echo $value["descripcion"];?></option>	
					    			<?php }
					    		?>
					    	</select>
					    </div>
					    <div class="col-md-2 col-xs-12">
					    	<label>SERIE REF.</label>
				        	<input type="text" class="form-control" name="seriecomprobante_ref" v-model="campos.seriecomprobante_ref" maxlength="4" autocomplete="off">
					    </div>
					    <div class="col-md-2 col-xs-12">
					    	<label>N° DOC. REFERENCIA</label>
				        	<input type="text" class="form-control" name="nrocomprobante_ref" v-model="campos.nrocomprobante_ref" maxlength="10" autocomplete="off">
					    </div>
				    	<div class="col-md-5 col-xs-12">
					    	<label>DESCRIPCION DE LA SALIDA</label>
					    	<input class="form-control" name="descripcion" v-model="campos.descripcion" required autocomplete="off">
					    </div>
				    </div>
				    <div class="row form-group">
				    	<div class="col-md-9"></div>
						<div class="col-md-3" align="right">
							<button type="button" class="btn-items-mas btn btn-success btn-icon" style="margin-top: 1.3rem;" v-on:click="phuyu_item()"><i data-acorn-icon="plus"></i> Buscar Productos </button>
						</div>
				    </div>
				    <?php
                        $data = '';
                        if($_SESSION["phuyu_stockalmacen"] == 1){
                        	$data = 'v-bind:max="dato.stock"';
                        }
				    ?>
				    <div class="row form-group table-responsive">
						<table class="table table-striped" style="font-size: 11px">
							<thead>
								<tr>
									<th width="45%">PRODUCTO</th>
									<th width="10%">UNIDAD</th>
									<th width="10%">STOCK ACTUAL</th>
									<th width="10%">CANTIDAD</th>
									<th width="10%">PRECIO</th>
									<th width="10%">SUBTOTAL</th>
									<th width="5%"> <i class="fa fa-trash-o"></i> </th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="(dato,index) in detalle">
									<td>{{dato.producto}}</td>
									<td>
										<select class="form-select number unidad" v-model="dato.codunidad" v-on:change="informacion_unidad(index,dato,this.value)" id="codunidad">
											<template v-for="(unidad, und) in dato.unidades">
												<option v-bind:value="unidad.codunidad" v-if="unidad.factor==1" selected>
													{{unidad.descripcion}}
												</option>
												<option v-bind:value="unidad.codunidad" v-if="unidad.factor!=1">
													{{unidad.descripcion}}
												</option>
											</template>
										</select>
									</td>
									<td style="color:red;font-weight:bold">{{dato.stock}} </td>
									<td>
										<input type="number" step="0.0001" class="form-control number" v-if="dato.control==1" v-model.number="dato.cantidad" v-on:keyup="phuyu_calcular(dato,3)" <?php echo $data; ?> min="0.0001"  required>
									</td>
									<td> 
										<input type="number" step="0.0001" class="form-control number" v-model.number="dato.precio" v-on:keyup="phuyu_calcular(dato,3)" min="0" required>
									</td>
									<td> 
										<input type="number" step="0.01" class="form-control number" v-model.number="dato.subtotal" readonly> 
									</td>
									<td> 
										<button type="button" class="btn btn-danger btn-xs" style="margin-bottom:-1px;" v-on:click="phuyu_deleteitem(index,dato)">
											<b>X</b> 
										</button> 
									</td>
								</tr>
							</tbody>
							<tfoot>
								<tr>
									<td colspan="5" align="right"><b>Total Salida S/.</b></td>
									<td><b class="text-danger">{{totales.importe}}</b></td>
								</tr>
							</tfoot>
						</table>
					</div>
					<div class="row">
						<div class="col-md-3">
							
						</div>
						<div class="col-md-9" align="right">
							<button type="submit" class="btn btn-primary btn-icon" v-bind:disabled="estado==1"> <b>GUARDAR SALIDA</b> </button>
							<button type="button" class="btn btn-danger icon" v-on:click="phuyu_cerrar()"> <b>CANCELAR</b> </button>
						</div>
					</div>
				</div>
			</div>
        </div>
	</form>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_chacra/nuevasalida.js"> </script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas_2.js"> </script>
<script> 
if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }
</script>