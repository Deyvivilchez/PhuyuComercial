<style>
	#phuyu_operacion.phuyu-almacen-movimiento {
		color: #444;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .phuyu_body {
		padding: 32px 20px;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-mov-card {
		background: #fff;
		border: 1px solid #dedede;
		border-radius: 14px;
		box-shadow: 0 8px 22px rgba(33, 37, 41, .05);
	}

	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-form-header {
		margin-bottom: 18px;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-form-header h5 {
		color: #4a4a4a;
		font-size: 15px;
		font-weight: 800;
		letter-spacing: .01em;
		text-transform: uppercase;
	}

	#phuyu_operacion.phuyu-almacen-movimiento label,
	#phuyu_operacion.phuyu-almacen-movimiento .form-label {
		font-size: .74rem;
		font-weight: 800;
		text-transform: uppercase;
		letter-spacing: .01em;
		color: #4f4f4f;
		margin-bottom: 5px;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-main-grid,
	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-ref-grid {
		display: grid;
		gap: 10px 22px;
		margin-left: 0;
		margin-right: 0;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-main-grid {
		grid-template-columns: minmax(0, 2.4fr) minmax(0, 2.4fr) minmax(0, 1.55fr) minmax(0, .75fr) minmax(0, .75fr) minmax(0, 1.55fr);
	}

	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-ref-grid {
		grid-template-columns: minmax(0, 2.4fr) minmax(0, .75fr) minmax(0, 1.55fr) minmax(0, 3.25fr) minmax(0, 1.35fr);
	}

	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-main-grid > [class*="col-"],
	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-ref-grid > [class*="col-"] {
		max-width: none;
		min-width: 0;
		padding-left: 0;
		padding-right: 0;
		width: auto;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .form-control,
	#phuyu_operacion.phuyu-almacen-movimiento .form-select,
	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-input,
	#phuyu_operacion.phuyu-almacen-movimiento .select2-container .select2-selection {
		min-height: 36px;
		border-color: #cfcfcf;
		border-radius: 9px;
		box-shadow: none;
		color: #4a4a4a;
		font-size: 12px;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .form-control:focus,
	#phuyu_operacion.phuyu-almacen-movimiento .form-select:focus,
	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-input:focus {
		border-color: #2fa84f;
		box-shadow: 0 0 0 .14rem rgba(47, 168, 79, .12);
	}

	#phuyu_operacion.phuyu-almacen-movimiento .form-control[readonly],
	#phuyu_operacion.phuyu-almacen-movimiento .form-select[readonly] {
		background: #eeeeee;
		color: #444;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-quick-product {
		background: #f7f9fc;
		border: 1px solid #dde5ef;
		border-radius: 10px;
		margin: 10px 0 8px;
		padding: 10px 12px 14px;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-product-select2.select2-container {
		width: 100% !important;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-product-select2 .select2-selection--single {
		align-items: center !important;
		background: #fbfcfe !important;
		border: 1px solid #cfd8e3 !important;
		border-radius: 9px !important;
		display: flex !important;
		min-height: 36px !important;
		transition: border-color .15s ease, box-shadow .15s ease, background .15s ease;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-product-select2.select2-container--open .select2-selection--single,
	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-product-select2.select2-container--focus .select2-selection--single {
		background: #fff !important;
		border-color: #2fa84f !important;
		box-shadow: 0 0 0 .14rem rgba(47, 168, 79, .12) !important;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-product-select2 .select2-selection__rendered {
		color: #4a4a4a !important;
		font-size: 12px !important;
		line-height: 36px !important;
		padding-left: .75rem !important;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-product-select2 .select2-selection__arrow {
		height: 36px !important;
	}

	#phuyu_operacion .select2-dropdown {
		border-color: #cfd8e3;
		border-radius: 10px;
		box-shadow: 0 14px 32px rgba(33, 37, 41, .12);
		overflow: hidden;
	}

	#phuyu_operacion .select2-results__option {
		padding: 7px 9px;
	}

	#phuyu_operacion .select2-results__option--highlighted[aria-selected] {
		background: #eefaf1;
		color: #245b2d;
	}

	.phuyu-product-result {
		align-items: center;
		display: flex;
		justify-content: space-between;
		gap: 10px;
	}

	.phuyu-product-result-name {
		color: #333;
		font-size: 12.5px;
		font-weight: 800;
	}

	.phuyu-product-result-meta {
		color: #777;
		font-size: 11px;
	}

	.phuyu-product-result-price {
		color: #2f8f3b;
		font-size: 12px;
		font-weight: 900;
		white-space: nowrap;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .table-responsive {
		background: #fff;
		border: 0;
		border-radius: 0;
		overflow: auto;
	}

	#phuyu_operacion.phuyu-almacen-movimiento table {
		margin-bottom: 0;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .table thead th {
		background: #f8fafc;
		color: #444;
		font-size: 11px;
		font-weight: 800;
		text-transform: uppercase;
		white-space: nowrap;
		border: 1px solid #cfd6dd;
		padding: 6px;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .table tbody td,
	#phuyu_operacion.phuyu-almacen-movimiento .table tfoot td {
		border: 1px solid #cfd6dd;
		font-size: 12px;
		vertical-align: middle;
		padding: 6px;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .table tbody tr:hover {
		background: #f8fbff;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-products-table {
		margin-top: 8px;
		min-height: 66px;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-products-table .form-control,
	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-products-table .form-select {
		min-height: 32px;
		font-size: 12px;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-total-label {
		background: #fff;
		color: #4f4f4f;
		font-weight: 800;
		text-align: right;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-total-amount {
		background: #fff;
		color: #f00;
		font-size: 14px !important;
		font-weight: 900;
		text-align: right;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .btn-block {
		width: 100%;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .btn-xs {
		--vz-btn-padding-y: .2rem;
		--vz-btn-padding-x: .45rem;
		--vz-btn-font-size: .75rem;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .btn {
		border-radius: 9px;
		font-weight: 700;
		min-height: 36px;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-product-button {
		background: #3c9c35;
		border-color: #3c9c35;
		min-height: 36px;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-form-actions {
		display: flex;
		justify-content: flex-end;
		gap: 4px;
		margin-top: 46px;
		padding-top: 0;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-form-actions .btn-primary {
		background: #23a8df;
		border-color: #23a8df;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .phuyu-form-actions .btn-light {
		background: #d32133;
		border-color: #d32133;
		color: #fff;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .modal-content {
		border: 0;
		border-radius: 10px;
		box-shadow: 0 18px 45px rgba(15, 23, 42, .16);
		overflow: hidden;
	}

	#phuyu_operacion.phuyu-almacen-movimiento .modal-header {
		background: linear-gradient(135deg, #0ab39c, #299cdb) !important;
		border-bottom: 0 !important;
	}

	@media (max-width: 767.98px) {
		#phuyu_operacion.phuyu-almacen-movimiento .phuyu-main-grid,
		#phuyu_operacion.phuyu-almacen-movimiento .phuyu-ref-grid {
			grid-template-columns: 1fr;
		}

		#phuyu_operacion.phuyu-almacen-movimiento .phuyu-form-actions {
			flex-direction: column-reverse;
			margin-top: 18px;
		}

		#phuyu_operacion.phuyu-almacen-movimiento .phuyu-form-actions .btn {
			width: 100%;
		}
	}
</style>
<div id="phuyu_operacion" class="phuyu-almacen-movimiento">
	<form id="formulario" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" id="igvsunat" value="<?php echo $_SESSION["phuyu_igv"];?>">
		<input type="hidden" id="empresa" value="<?php echo $_SESSION["phuyu_empresa"];?>" name="">
		<div class="phuyu_body">
            <div class="card phuyu-mov-card">
				<div class="card-body">
					<div class="phuyu-form-header">
						<h5 class="mb-0">Registro nuevo ingreso almacen</h5>
					</div>
		        	<div class="phuyu-main-grid mb-2">
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
					<div v-if="campos.codmovimientotipo==11">
					<div class="row g-3">
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
					</div>
				    <div class="phuyu-ref-grid mb-2">
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
						    <div class="col-md-2 d-flex align-items-end">
								<button type="button" class="btn btn-success w-100 phuyu-product-button" v-on:click="phuyu_item()">
								<i class="bi bi-search me-1"></i> Productos
							</button>
					    </div>
				    </div>
					<div class="phuyu-quick-product">
						<label>BUSCAR PRODUCTO DIRECTO</label>
						<select id="producto_rapido_select" class="form-select" style="width: 100%;"></select>
					</div>
				    <div class="table-responsive phuyu-products-table">
						<table class="table table-hover align-middle" style="font-size: 11px">
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
									<td colspan="5" class="phuyu-total-label">Total Ingreso</td>
									<td class="phuyu-total-amount">S/. {{totales.importe}}</td>
									<td></td>
								</tr>
							</tfoot>
						</table>
					</div>
					<div class="phuyu-form-actions">
						<button type="submit" class="btn btn-primary" v-bind:disabled="estado==1">
							<i class="bi bi-save me-1"></i> Guardar ingreso
						</button>
						<button type="button" class="btn btn-light" v-on:click="phuyu_cerrar()">
							<i class="bi bi-x-circle me-1"></i> Cancelar
						</button>
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
									<tr v-for="(serie, indexSerie) in productoSeleccionado.series"
										:key="indexSerie">
										<td>{{ indexSerie + 1 }}</td>
										<td>
										<span class="font-monospace">{{ serie.serie_codigo }}</span>
										</td>
										<td>
										<button type="button"
												class="btn btn-sm btn-outline-danger btnEliminarSerie"
												@click.prevent="eliminarSerie(indexSerie)">
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

<script src="<?php echo base_url();?>phuyu/phuyu_almacen/nuevoingreso.js?v=<?php echo filemtime(FCPATH . 'phuyu/phuyu_almacen/nuevoingreso.js'); ?>"> </script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas_2.js"> </script>
<script>
	if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }
</script>
