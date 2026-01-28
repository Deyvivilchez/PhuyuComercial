<style type="text/css">
	.table thead>tr>th, tbody>tr>td{
		padding: 4px;
	}
</style>
<div id="phuyu_operacion">
	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" id="igvsunat" value="<?php echo $_SESSION["phuyu_igv"];?>">
		<input type="hidden" id="empresa" value="<?php echo $_SESSION["phuyu_empresa"];?>" name="">
		<div class="phuyu_body">
            <div class="card">
				<div class="card-body">
					<div class="row form-group">
						<div class="col-md-12 col-xs-12"> <h5><b>REGISTRO NUEVA INGRESO ALMACEN</b></h5> </div>
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
					    	<input type="text" class="form-select" value="<?php echo $_SESSION['phuyu_almacen']?>" readonly>
					    </div>
				    	<div class="col-md-1 col-xs-12">
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
					    <div class="col-md-1 col-xs-12">
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
					    	<label>FECHA INGRESO</label>
					    	<input type="date" class="form-control" name="fechakardex" id="fechakardex" value="<?php echo date('Y-m-d');?>" autocomplete="off" required>
					    </div>
					</div>
					<div class="row mb-2" v-if="campos.codmovimientotipo==11">
						<div class="col-md-4"><h5>LISTA DE PRESTAMOS OTORGADOS DEL CLIENTE <b class="text-danger">{{campos.cliente}}</b></h5></div>
						<div class="col-md-8">	
							<div class="row form-group table-responsive scroll-phuyu-view" style="height:calc(100vh - 550px);padding:0px; overflow:auto;">
								<table class="table table-bordered table-striped" style="font-size: 11px">
									<thead>
										<tr>
											<th>COMPROBANTE</th>
											<th>FECHA PRESTAMO</th>
											<th>SELECCIONAR</th>
											<th>VER</th>
										</tr>
									</thead>
									<tbody>
										<tr v-for="(dato,index) in detalle_prestamo">
											<td>{{dato.producto}}</td>
											<td>
												<select class="phuyu-input unidad" v-model="dato.codunidad" v-on:change="informacion_unidad(index,dato,this.value)" id="codunidad">
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
											<td>
												<input type="number" step="0.0001" class="phuyu-input number" v-model.number="dato.cantidad" v-on:keyup="phuyu_calcular(dato,3)" min="0.0001" required>
											</td>
											<td> 
												<input type="number" step="0.0001" class="phuyu-input number" v-model.number="dato.precio" v-on:keyup="phuyu_calcular(dato,3)" min="0" required>
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
					    	<label>DESCRIPCION DEL INGRESO</label>
					    	<input class="form-control" name="descripcion" v-model="campos.descripcion" required autocomplete="off">
					    </div>
					    <div class="col-md-2" align="right">
					    	<button type="button" style="margin-top: 1.3rem" class="btn btn-success btn-icon btn-block" v-on:click="phuyu_item()"> <b><i data-acorn-icon="search"></i> Productos</b> </button>
					    </div>
				    </div>
				    <div class="row form-group table-responsive">
						<table class="table table-bordered table-striped" style="font-size: 11px">
							<thead>
								<tr><th width="7%">-</th>
									<th width="55%">PRODUCTO</th>
									<th width="10%">UNIDAD</th>
									<th width="10%">CANTIDAD</th>
									<th width="10%">PRECIO</th>
									<th width="10%">SUBTOTAL</th>
									<th width="5%"> <i class="fa fa-trash-o"></i> </th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="(dato,index) in detalle">
									<td class="text-center">
											<div class="d-flex flex-column align-items-center gap-1">
												
												<button v-if="dato.controlarseries == 1" type="button"
													class="btn btn-outline-secondary btn-sm px-2 py-0"
													style="font-size: 11px; line-height: 1;"
													v-on:click="phuyu_ModalSeries(dato,index)">
													+ Series
												</button>
											</div>
										</td>
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
									<td>
										<!-- <input type="number" step="0.0001" class="form-control number" v-model.number="dato.cantidad" v-on:keyup="phuyu_calcular(dato,3)" min="0.0001" required> -->
										<input     :disabled="dato.controlarseries == 1"  type="number" step="0.0001" class="form-control number" v-model.number="dato.cantidad"
											 v-on:keyup="phuyu_calcular(dato,3)" min="0.001" required>
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
									<td colspan="4" style="font-size: 14px;" align="right"><b>Total Ingreso</b></td>
									<td align="right" style="font-size: 14px;color:red"><b>S/. {{totales.importe}}</b></td>
									<td></td>
								</tr>
							</tfoot>
						</table>
					</div><br>
					<div class="row form-group">
						<div class="col-md-5">
						</div>
						<div class="col-md-7" align="right">
							<button type="submit" class="btn btn-primary btn-icon btn-block" v-bind:disabled="estado==1"> <b>GUARDAR INGRESO</b> </button>
							<button type="button" class="btn btn-danger btn-icon btn-block" v-on:click="phuyu_cerrar()"> <b>CANCELAR</b> </button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>

<!-- Modal para Series -->
		<div class="modal fade" id="modalSeries" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header bg-gradient text-white py-2 px-3" style="background: linear-gradient(90deg, #007bff, #00bcd4); border-bottom: 3px solid #00acc1;">
						<h5 class="modal-title d-flex align-items-center mb-0">
							<i class="mdi mdi-barcode-scan me-2 fs-4 text-white"></i>
							<span class="fw-bold text-uppercase">Gestión de Series</span>
						</h5>
						<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>

					<div class="modal-body">
						<!-- Información del producto -->
						<div class="row mb-3">
							<div class="col-md-6">
								<strong>Código:</strong> {{this.productoSeleccionado.codproducto }}
							</div>
							<div class="col-md-6">
								<strong>Producto:</strong> {{this.productoSeleccionado.producto }}
							</div>
						</div>

						<hr>

						<!-- Formulario para agregar series -->
						<div class="row mb-3">
							<div class="col-md-8">
								<label class="form-label">Número de Serie</label>
								<input type="text" class="form-control" id="nuevaSerieInput" v-model="nuevaSerie"
									placeholder="Ingrese el número de serie..." maxlength="50">
							</div>
							<div class="col-md-4">
								<label class="form-label">&nbsp;</label>
								<button type="button" class="btn btn-primary w-100" @click="agregarSerie">
									<i class="mdi mdi-plus"></i> Agregar Serie
								</button>
							</div>
						</div>

						<!-- Lista de series -->
						<div class="table-responsive">
							<table class="table table-sm table-hover">
								<thead class="table-light">
									<tr>
										<th width="5%">#</th>
										<th width="70%">NÚMERO DE SERIE</th>
										<th width="25%">ACCIONES</th>
									</tr>
								</thead>
								<tbody id="tablaSeries">
									<tr v-for="(serie, index) in productoSeleccionado.series" :key="index">
										<td>{{index++}}</td>
										<td>
											<span class="font-monospace">{{serie.serie_codigo}}</span>
										</td>
										<td>
											<button type="button" class="btn btn-sm btn-outline-danger btnEliminarSerie" 
												@click="eliminarSerie(index)">
												<i class="mdi mdi-trash-can-outline"></i> Eliminar
											</button>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>

					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
							<i class="mdi mdi-close"></i> Cerrar
						</button>
						<!-- <button type="button" class="btn btn-success" id="btnGuardarSeries">
                    <i class="mdi mdi-content-save"></i> Guardar Series
                </button> -->
					</div>
				</div>
			</div>
		</div>











</div>

<script src="<?php echo base_url();?>phuyu/phuyu_almacen/nuevoingreso.js"> </script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas_2.js"> </script>
<script>
	if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }
</script>