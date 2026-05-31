<style>
	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-mov-card {
		border: 1px solid rgba(64, 81, 137, .10);
		border-radius: .9rem;
		box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
	}

	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-form-header {
		display: flex;
		align-items: center;
		gap: .75rem;
		padding-bottom: 1rem;
		margin-bottom: 1rem;
		border-bottom: 1px solid rgba(64, 81, 137, .10);
	}

	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-form-icon {
		width: 44px;
		height: 44px;
		border-radius: 12px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: rgba(240, 101, 72, .12);
		color: #f06548;
		font-size: 1.35rem;
	}

	#phuyu_operacion.phuyu-almacen-movimiento label,
	#phuyu_operacion.phuyu-almacen-movimiento .form-label {
		font-size: .74rem;
		font-weight: 800;
		text-transform: uppercase;
		letter-spacing: .03em;
		color: #495057;
		margin-bottom: .4rem;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .form-control,
	#phuyu_operacion.phuyu-almacen-movimiento .form-select,
	#phuyu_operacion.phuyu-almacen-movimiento .select2-container .select2-selection {
		min-height: 40px;
		border-color: rgba(64, 81, 137, .16);
		border-radius: .375rem;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .table-responsive {
		border: 1px solid rgba(64, 81, 137, .10);
		border-radius: .75rem;
		overflow: auto;
	}

	#phuyu_operacion.phuyu-almacen-movimiento table {
		margin-bottom: 0;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .table thead th {
		background: #f8fafc;
		color: #495057;
		font-size: .74rem;
		font-weight: 800;
		text-transform: uppercase;
		white-space: nowrap;
		border-bottom: 1px solid rgba(64, 81, 137, .12);
		padding: .65rem;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .table tbody td,
	#phuyu_operacion.phuyu-almacen-movimiento .table tfoot td {
		font-size: .86rem;
		vertical-align: middle;
		padding: .55rem;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .btn-block {
		width: 100%;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .btn-xs {
		--vz-btn-padding-y: .2rem;
		--vz-btn-padding-x: .45rem;
		--vz-btn-font-size: .75rem;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-form-actions {
		display: flex;
		justify-content: flex-end;
		gap: .65rem;
		padding-top: 1rem;
		margin-top: 1rem;
		border-top: 1px solid rgba(64, 81, 137, .10);
	}
</style>

<div id="phuyu_operacion" class="phuyu-almacen-movimiento">
	<form id="formulario" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" id="igvsunat" value="<?php echo $_SESSION["phuyu_igv"];?>">

		<div class="phuyu_body">
			<div class="card phuyu-mov-card">
				<div class="card-body">
				    <div class="phuyu-form-header">
						<div class="phuyu-form-icon"><i class="bi bi-box-arrow-up"></i></div>
						<div>
							<div class="text-muted small text-uppercase fw-semibold">Almacen</div>
							<h5 class="mb-0 fw-bold">Registro nueva salida almacen</h5>
						</div>
					</div>
		        	<div class="row mb-2">
		        		<div class="col-md-3">
		        			<div class="w-100">
						    	<label>PERSONA RESPONSABLE</label>
						    	<select class="form-control" name="codpersona" id="codpersona" required>
				    				<option value="1"><?php echo $_SESSION["phuyu_empresa"];?></option>
				    			</select>
				    		</div>
					    </div>
		        		<div class="col-md-3 col-xs-12">
					    	<label>TIPO MOVIMIENTO</label>
					    	<select class="form-select" name="codmovimientotipo" v-model="campos.codmovimientotipo" required v-on:change="phuyu_prestamos()">
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
				    <div class="row mb-2" v-if="campos.codmovimientotipo==29">
						<div class="col-md-4"></div>
						<div class="col-md-8">	
							<div class="row form-group table-responsive">
								<table class="table table-bordered table-striped" style="font-size: 11px">
									<thead>
										<tr align="center" >
											<th>COMPROBANTE</th>
											<th>FECHA PRESTAMO</th>
											<th>SELECCIONAR</th>
											<th>VER</th>
										</tr>
									</thead>
									<tbody>
										<tr v-for="(dato,index) in detalle_prestamo" style="cursor:pointer;" v-bind:id="dato.codkardex">
											<td>{{dato.seriecomprobante}}-{{dato.nrocomprobante}}</td>
											<td>
												{{dato.fechakardex}}
											</td>
											<td>
												<button type="button" v-on:click="phuyu_seleccionar(dato)" class="btn btn-xs btn-block btn-danger">Seleccionar</button>
											</td>
											<td> 
												<button type="button" class="btn btn-xs btn-info btn-block" v-on:click="phuyu_verprestamo(dato.codkardex)"><i class="fa fa-eye"></i> VER</button>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
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
					    <div class="col-md-1 col-xs-12">
					    	<label>SERIE REF.</label>
				        	<input type="text" class="form-control" name="seriecomprobante_ref" v-model="campos.seriecomprobante_ref" maxlength="4" autocomplete="off">
					    </div>
					    <div class="col-md-2 col-xs-12">
					    	<label>N° DOC. REFERENCIA</label>
				        	<input type="text" class="form-control" name="nrocomprobante_ref" v-model="campos.nrocomprobante_ref" maxlength="10" autocomplete="off">
					    </div>
				    	<div class="col-md-4 col-xs-12">
					    	<label>DESCRIPCION DE LA SALIDA</label>
					    	<input class="form-control" name="descripcion" v-model="campos.descripcion" required autocomplete="off">
					    </div>
						    <div class="col-md-2 d-flex align-items-end">
								<button type="button" class="btn btn-success w-100" v-on:click="phuyu_item()">
								<i class="bi bi-search me-1"></i> Productos
							</button>
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

									<td v-if="dato.controlarseries == 1">
									{{ dato.producto }} - SERIE:
									<strong style="color:rgb(118, 29, 165);">
										{{ dato.serie_seleccionada?.serie_codigo }}
									</strong>
									</td>

									<td v-else>
									{{ dato.producto }}
									</td>
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

									<td v-if="dato.controlarseries != 1">
										<input
											type="number"
											class="form-control number"
											v-model.number="dato.cantidad"
											@keyup="phuyu_calcular(dato, 3)"
											<?php echo $data; ?>
											min="1"
											step="1"
											required
										>
										</td>

									<td v-else>
										{{dato.cantidad}}
									</td>
									<!-- <td>
										<input type="number" step="0.0001" class="form-control number" 
										v-if="dato.control==1" 
										v-model.number="dato.cantidad" 
										v-on:keyup="phuyu_calcular(dato,3)" <?php echo $data; ?> min="0.0001"  required>
									</td> -->
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
									<td colspan="5" style="font-size: 14px;" align="right"><b>Total Salida</b></td>
									<td align="right" style="font-size: 14px;color:red"><b>S/. {{totales.importe}}</b></td>
									<td></td>
								</tr>
							</tfoot>
						</table>
					</div>
					<div class="phuyu-form-actions">
						<button type="submit" class="btn btn-primary" v-bind:disabled="estado==1">
							<i class="bi bi-save me-1"></i> Guardar salida
						</button>
						<button type="button" class="btn btn-light" v-on:click="phuyu_cerrar()">
							<i class="bi bi-x-circle me-1"></i> Cancelar
						</button>
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
<script src="<?php echo base_url();?>phuyu/phuyu_almacen/nuevasalida.js"> </script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas_2.js"> </script>
