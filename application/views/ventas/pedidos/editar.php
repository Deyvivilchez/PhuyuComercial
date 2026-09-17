<style>
	#phuyu_operacion label,
	#phuyu_operacion .form-label {
		font-size: 11px;
		font-weight: 700;
		color: #343a40;
		margin-bottom: 6px;
		text-transform: uppercase;
	}

	#phuyu_operacion .btn {
		white-space: nowrap;
	}

	.phuyu-edit-card {
		border: 0;
		border-radius: 1rem;
		box-shadow: 0 10px 35px rgba(15, 23, 42, 0.06);
	}

	.phuyu-title {
		display: flex;
		align-items: center;
		gap: .6rem;
		color: #f06548;
		font-weight: 800;
		margin-bottom: 1.25rem;
	}

	.phuyu-title i {
		width: 38px;
		height: 38px;
		border-radius: 12px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: rgba(240, 101, 72, 0.12);
	}

	.phuyu-block {
		border: 1px solid rgba(64, 81, 137, 0.12);
		border-radius: 1rem;
		padding: 1rem;
		background: #fff;
	}

	.phuyu-block-soft {
		background: #f8fafc;
	}

	.phuyu-cliente-row {
		border: 1px solid rgba(64, 81, 137, 0.12);
		border-radius: 1rem;
		padding: 1rem;
		background: #fff;
	}

	#phuyu_operacion #codpersona {
		min-height: 38px;
		border-radius: 8px;
		border: 1px solid #ced4da;
		background-color: #fff;
		font-size: 12px;
		font-weight: 600;
		color: #495057;
		padding: .47rem 2rem .47rem .75rem;
	}

	#phuyu_operacion .select2-container {
		width: 100% !important;
	}

	#phuyu_operacion .select2-container--default .select2-selection--single,
	#phuyu_operacion .select2-container--bootstrap4 .select2-selection--single {
		height: 38px !important;
		border-radius: 8px !important;
		border: 1px solid #ced4da !important;
		display: flex !important;
		align-items: center !important;
		background-color: #fff !important;
	}

	#phuyu_operacion .select2-container--default .select2-selection--single .select2-selection__rendered,
	#phuyu_operacion .select2-container--bootstrap4 .select2-selection--single .select2-selection__rendered {
		line-height: 38px !important;
		padding-left: 12px !important;
		padding-right: 30px !important;
		font-size: 12px !important;
		font-weight: 600 !important;
		color: #495057 !important;
	}

	#phuyu_operacion .select2-container--default .select2-selection--single .select2-selection__arrow,
	#phuyu_operacion .select2-container--bootstrap4 .select2-selection--single .select2-selection__arrow {
		height: 38px !important;
		right: 8px !important;
	}

	.btn-add-cliente {
		height: 38px;
		border-radius: 8px;
		min-width: 58px;
	}

	.phuyu-table-drag {
		overflow-x: auto;
		cursor: grab;
		user-select: none;
		border: 1px solid rgba(64, 81, 137, 0.12);
		border-radius: .9rem;
	}

	.phuyu-table-drag.active {
		cursor: grabbing;
	}

	.phuyu-table-drag table {
		min-width: 1180px;
		margin-bottom: 0;
		font-size: 11px;
	}

	.phuyu-table-drag thead th {
		background: #f3f6f9;
		white-space: nowrap;
		text-transform: uppercase;
		font-weight: 800;
		color: #343a40;
	}

	.phuyu-table-drag tbody td {
		vertical-align: middle;
	}

	.phuyu-table-drag .form-control,
	.phuyu-table-drag .form-select {
		font-size: 12px;
		min-width: 90px;
	}

	.phuyu-product-name {
		min-width: 260px;
		font-weight: 700;
	}

	.phuyu-actions {
		display: flex;
		justify-content: flex-end;
		flex-wrap: wrap;
		gap: .5rem;
	}

	.totales {
		text-align: right;
		font-weight: 700;
		background: #fff !important;
	}

	.totaltotal {
		text-align: right;
		font-weight: 800;
		color: red;
		background: #fff !important;
		font-size: 16px;
	}
</style>

<div id="phuyu_operacion">

	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">

		<input type="hidden" id="comprobante" value="<?php echo $info[0]['codcomprobantetipo'];?>">
		<input type="hidden" id="serie" value="<?php echo $info[0]['seriecomprobante'];?>">
		<input type="hidden" id="nrocomprobante" value="<?php echo $info[0]['nrocomprobante'];?>">
		<input type="hidden" id="comprobantereferencia" value="<?php echo $info[0]['codcomprobantetiporeferencia'];?>">
		<input type="hidden" id="seriereferencia" value="<?php echo $info[0]['seriecomprobantereferencia'];?>">
		<input type="hidden" id="stockalmacen" value="<?php echo $_SESSION["phuyu_stockalmacen"];?>">
		<input type="hidden" id="itemrepetir" value="<?php echo $_SESSION["phuyu_itemrepetir"];?>">
		<input type="hidden" id="igvsunat" value="<?php echo $_SESSION["phuyu_igv"];?>">
		<input type="hidden" id="icbpersunat" value="<?php echo $_SESSION["phuyu_icbper"];?>">
		<input type="hidden" id="formato" value="<?php echo $_SESSION['phuyu_formatopedido'];?>">
		<input type="hidden" id="codpedido" value="<?php echo $info[0]['codpedido'];?>">
		<input type="hidden" id="empleado" value="<?php echo $info[0]['codempleado'];?>">
		<input type="hidden" id="condicionpagoref" value="<?php echo $info[0]["condicionpago"];?>">

		<?php 
			$disabled = '';
			if($_SESSION["phuyu_codperfil"] > 3){
				$disabled = 'disabled';
			}
		?>

		<div class="phuyu_body">
			<div class="card phuyu-edit-card">
				<div class="card-body p-4">

					<div class="phuyu-title">
						<i class="bi bi-pencil-square fs-5"></i>
						<h4 class="mb-0">EDITAR PEDIDO N° {{campos.nro}}</h4>
					</div>

					<div class="phuyu-block phuyu-block-soft mb-3">
						<div class="row g-3">

							<div class="col-lg-3 col-md-6">
								<label class="form-label">Tipo comprobante referencia</label>
								<select class="form-select" name="codcomprobantetiporeferencia" v-model="campos.codcomprobantetiporeferencia" required v-on:change="phuyu_series_referencia()">
									<?php foreach ($comprobantesreferencia as $key => $value) { ?>
										<option value="<?php echo $value["codcomprobantetipo"];?>">
											<?php echo $value["descripcion"];?>
										</option>
									<?php } ?>
								</select>
							</div>

							<div class="col-lg-3 col-md-6">
								<label class="form-label">Condición pago</label>
								<select class="form-select" name="condicionpago" v-model="campos.condicionpago" v-on:change="phuyu_condicionpago()">
									<option value="1">CONTADO</option>
									<option value="2">CREDITO</option>
								</select>
							</div>

							<div class="col-lg-4 col-md-6">
								<label class="form-label">Seleccionar vendedor</label>
								<select class="form-select" name="codempleado" <?php echo $disabled;?> v-model="campos.codempleado" required>
									<option value="0">SIN VENDEDOR</option>
									<?php foreach ($vendedores as $key => $value) { ?>
										<option value="<?php echo $value["codpersona"];?>">
											<?php echo $value["razonsocial"];?>
										</option>
									<?php } ?>
								</select>
							</div>

							<div class="col-lg-2 col-md-6">
								<label class="form-label">Fecha pedido</label>
								<input type="date" class="form-control" name="fechacomprobante" id="fechacomprobante" value="<?php echo $info[0]["fechapedido"];?>" autocomplete="off" required>
							</div>

						</div>
					</div>

					<div class="phuyu-cliente-row mb-3">
						<div class="row g-3 align-items-end">

							<div class="col-lg-3 col-md-6">
								<label class="form-label">Seleccionar cliente</label>
								<select class="form-select" name="codpersona" id="codpersona">
									<option value="<?php echo $info[0]["codpersona"];?>">
										<?php echo $info[0]["razonsocial"];?>
									</option>
								</select>
							</div>

							<div class="col-lg-1 col-md-2">
								<button type="button"
									class="btn btn-primary btn-add-cliente w-100 d-flex align-items-center justify-content-center"
									v-on:click="phuyu_addcliente()"
									title="Agregar cliente">
									<i class="bi bi-person-plus fs-5"></i>
								</button>
							</div>

							<div class="col-lg-4 col-md-10">
								<label class="form-label">Cliente del pedido</label>
								<input type="text" class="form-control" id="cliente" v-model.trim="campos.cliente" autocomplete="off" maxlength="250" placeholder="Razón social del cliente..." required value="<?php echo $info[0]["razonsocial"];?>">
							</div>

							<div class="col-lg-4 col-md-12">
								<label class="form-label">Dirección cliente</label>
								<input type="text" class="form-control" id="direccion" v-model.trim="campos.direccion" autocomplete="off" maxlength="250" placeholder="Dirección del cliente..." required value="<?php echo $info[0]["direccion"]?>">
							</div>

						</div>
					</div>

					<div class="row g-3 align-items-end mb-3">
						<div class="col-lg-9 col-md-8">
							<label class="form-label">Glosa del pedido</label>
							<input type="text" class="form-control" v-model="campos.descripcion" placeholder="Referencia del pedido...">
						</div>

						<div class="col-lg-3 col-md-4">
							<button type="button" class="btn btn-success w-100 d-flex align-items-center justify-content-center gap-2" v-on:click="phuyu_item()">
								<i class="bi bi-search"></i>
								<span>Buscar productos</span>
							</button>
						</div>
					</div>

					<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
						<h6 class="mb-0 text-primary fw-bold">
							<i class="bi bi-cart-check me-1"></i>
							Detalle de productos
						</h6>

						<small class="text-muted">
							<i class="bi bi-arrows-move me-1"></i>
							Arrastra la tabla con el mouse
						</small>
					</div>

					<div class="phuyu-table-drag">
						<table class="table table-sm table-hover table-striped align-middle">
							<thead>
								<tr>
									<th>Acción</th>
									<th>Producto</th>
									<th>Unidad</th>
									<th>Stock</th>
									<th>Cantidad</th>
									<th>Precio unit.</th>
									<th>I.G.V.</th>
									<th>ICBPER</th>
									<th>Subtotal</th>
									<th></th>
								</tr>
							</thead>

							<tbody>
								<tr v-for="(dato,index) in detalle">
									<td>
										<button type="button" data-bs-target="#modal_itemdetalle" class="btn btn-primary btn-sm d-flex align-items-center gap-1" v-on:click="phuyu_itemdetalle(index,dato)">
											<i class="bi bi-plus-circle"></i>
											<span>Más</span>
										</button>
									</td>

									<td class="phuyu-product-name">{{dato.producto}}</td>

									<td>
										<select class="form-select form-select-sm number unidad" v-model="dato.codunidad" v-on:change="informacion_unidad(index,dato,this.value)" id="codunidad">
											<template v-for="(unidads, und) in dato.unidades">
												<option v-bind:value="unidads.codunidad" v-if="unidads.factor==1" selected>
													{{unidads.descripcion}}
												</option>
												<option v-bind:value="unidads.codunidad" v-if="unidads.factor!=1">
													{{unidads.descripcion}}
												</option>
											</template>
										</select>
									</td>

									<td class="fw-bold text-danger">{{dato.stock}}</td>

									<td>
										<input type="number" step="0.0001" class="form-control form-control-sm number" v-model.number="dato.cantidad" v-on:keyup="phuyu_calcular(dato)" min="0.0001" required>
									</td>

									<td>
										<input type="number" step="0.0001" class="form-control form-control-sm number" v-if="dato.codafectacionigv==21" v-model.number="dato.precio" min="0" readonly>
										<input type="number" step="0.0001" class="form-control form-control-sm number" v-if="dato.codafectacionigv!=21" v-model.number="dato.precio" v-on:keyup="phuyu_calcular(dato)" min="0.001" required v-bind:disabled="dato.porcdescuento==100">
									</td>

									<td>
										<input type="number" class="form-control form-control-sm number" v-model.number="dato.igv" min="0" readonly>
									</td>

									<td>
										<input type="number" class="form-control form-control-sm number" v-model.number="dato.icbper" min="0" readonly>
									</td>

									<td v-if="dato.codafectacionigv==21">
										<input type="number" step="0.01" class="form-control form-control-sm number" v-model.number="dato.subtotal">
									</td>

									<td v-if="dato.codafectacionigv!=21">
										<input type="number" step="0.01" class="form-control form-control-sm number" v-model.number="dato.subtotal" v-if="dato.calcular==0" readonly>
										<input type="number" step="0.01" class="form-control form-control-sm number" v-if="dato.calcular!=0" v-model.number="dato.subtotal" v-on:keyup="phuyu_subtotal(dato)" required v-bind:disabled="dato.porcdescuento==100">
									</td>

									<td>
										<button type="button" class="btn btn-danger btn-sm" v-on:click="phuyu_deleteitem(index,dato)">
											<i class="bi bi-trash"></i>
										</button>
									</td>
								</tr>
							</tbody>

							<tfoot>
								<tr>
									<td colspan="8" class="totales">Subtotal</td>
									<td class="text-center fw-bold">{{totales.valorventa}}</td>
									<td></td>
								</tr>
								<tr>
									<td colspan="8" class="totales">I.G.V</td>
									<td class="text-center fw-bold">{{totales.igv}}</td>
									<td></td>
								</tr>
								<tr>
									<td colspan="8" class="totaltotal">Total</td>
									<td class="text-center fs-6">
										<b class="text-danger">{{totales.importe}}</b>
									</td>
									<td></td>
								</tr>
							</tfoot>
						</table>
					</div>

					<div class="border-top pt-3 mt-3">
						<div class="phuyu-actions">

							<button type="button" class="btn btn-warning d-flex align-items-center gap-2" v-on:click="phuyu_venta()">
								<i class="bi bi-plus-circle"></i>
								<span>Nuevo pedido</span>
							</button>

							<button type="submit" class="btn btn-info d-flex align-items-center gap-2" v-bind:disabled="estado==1">
								<i class="bi bi-save"></i>
								<span>Guardar pedido</span>
							</button>

							<button type="button" class="btn btn-danger d-flex align-items-center gap-2" v-on:click="phuyu_atras()">
								<i class="bi bi-arrow-left-circle"></i>
								<span>Atrás</span>
							</button>

						</div>
					</div>

				</div>
			</div>
		</div>

	</form>

	<div id="modal_cuotas" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header modal-phuyu-titulo">
					<h5 class="modal-title">
						<i class="bi bi-calendar2-check me-1"></i>
						<b>GENERAR CUOTAS DE PAGO AL CRÉDITO</b>
					</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>

				<div class="modal-body">
					<div class="row g-3 mb-3" v-if="campos.condicionpago==2">
						<div class="col-md-5">
							<label class="form-label">Nro días</label>
							<input class="form-control" name="nrodias" v-model="campos.nrodias" v-on:keyup="phuyu_cuotas()" required>
						</div>

						<div class="col-md-3">
							<label class="form-label">Cuotas</label>
							<input class="form-control" name="nrocuotas" v-model="campos.nrocuotas" v-on:keyup="phuyu_cuotas()" required>
						</div>

						<div class="col-md-4">
							<label class="form-label">Tasa interés (%)</label>
							<input class="form-control" name="tasainteres" v-model="campos.tasainteres" v-on:keyup="phuyu_cuotas()" required>
						</div>
					</div>

					<div v-if="campos.condicionpago==2">
						<div class="table-responsive border rounded-3" style="max-height:120px;">
							<table class="table table-sm table-striped mb-0" style="font-size: 11px">
								<thead>
									<tr>
										<th>Fecha vence</th>
										<th>Importe</th>
										<th>Interés</th>
										<th>Total</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="dato in cuotas">
										<td>{{dato.fechavence}}</td>
										<td>{{dato.importe}}</td>
										<td>{{dato.interes}}</td>
										<td>{{dato.total}}</td>
									</tr>
								</tbody>
							</table>
						</div>

						<div class="text-center border-bottom pb-3 mt-3">
							<button type="button" class="btn btn-warning btn-sm">
								<i class="bi bi-percent me-1"></i>
								<b>INTERÉS: S/. {{totales.interes}}</b>
							</button>

							<button type="button" class="btn btn-danger btn-sm">
								<i class="bi bi-cash-coin me-1"></i>
								<b>TOTAL CRÉDITO: S/. {{campos.totalcredito}}</b>
							</button>
						</div>
					</div>

					<div class="text-center mt-3">
						<button type="button" class="btn btn-danger" data-bs-dismiss="modal">
							<i class="bi bi-x-circle me-1"></i>
							VOLVER AL FORMULARIO
						</button>

						<button type="button" class="btn btn-success" v-on:click="phuyu_pagar()">
							<i class="bi bi-save me-1"></i>
							GUARDAR EDICIÓN
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="modal_itemdetalle" data-bs-backdrop="static" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header modal-phuyu-titulo">
					<h4 class="modal-title">
						<i class="bi bi-box-seam me-1"></i>
						<b>DETALLE DEL ITEM DE LA VENTA</b>
					</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>

				<div class="modal-body">
					<h5>
						<b>
							PRODUCTO: {{item.producto}}
							<span class="badge bg-warning text-dark ms-2">
								CANTIDAD: {{item.cantidad}} {{item.unidad}}
							</span>
						</b>
					</h5>

					<hr>

					<div class="row g-3 mb-3">
						<div class="col-md-4">
							<label class="form-label">Precio bruto</label>
							<input type="number" class="form-control number" v-model.number="item.preciobruto" v-on:keyup="phuyu_itemcalcular(item,0)" v-bind:disabled="item.codafectacionigv==21">
						</div>

						<div class="col-md-4">
							<label class="form-label">Descuento (S/.)</label>
							<input type="number" class="form-control number" v-model.number="item.descuento" v-on:keyup="phuyu_itemcalcular(item,-1)" v-bind:disabled="item.codafectacionigv==21">
						</div>

						<div class="col-md-4">
							<label class="form-label">Descuento (%)</label>
							<input type="number" class="form-control number" v-model.number="item.porcdescuento" v-on:keyup="phuyu_itemcalcular(item,-2)" v-bind:disabled="item.codafectacionigv==21">
						</div>
					</div>

					<div class="row g-3 mb-3">
						<div class="col-md-3">
							<label class="form-label">Precio sin I.G.V.</label>
							<input type="number" class="form-control number" v-model.number="item.preciosinigv" v-on:keyup="phuyu_itemcalcular(item,1)" v-bind:disabled="item.codafectacionigv==21">
						</div>

						<div class="col-md-3">
							<label class="form-label">Precio unitario</label>
							<input type="number" class="form-control number" v-model.number="item.precio" v-on:keyup="phuyu_itemcalcular(item,2)" v-bind:disabled="item.codafectacionigv==21">
						</div>

						<div class="col-md-3">
							<label class="form-label">Tipo afectación</label>
							<select class="form-select" v-model="item.codafectacionigv" v-on:change="phuyu_itemcalcular(item,2)">
								<?php foreach ($afectacionigv as $key => $value) { ?>
									<option value="<?php echo $value["oficial"];?>">
										<?php echo $value["descripcion"];?>
									</option>
								<?php } ?>
							</select>
						</div>

						<div class="col-md-3">
							<label class="form-label">ICBPER</label>
							<select class="form-select" v-model="item.conicbper" v-on:change="phuyu_itemcalcular(item,2)">
								<option value="1">SI</option>
								<option value="0">NO</option>
							</select>
						</div>
					</div>

					<div class="text-center mb-3">
						<button type="button" class="btn btn-info btn-sm">
							<i class="bi bi-receipt me-1"></i>
							<b>VALOR VENTA: S/. {{item.valorventa}}</b>
						</button>

						<button type="button" class="btn btn-danger btn-sm">
							<i class="bi bi-percent me-1"></i>
							<b>IGV: S/. {{item.igv}}</b>
						</button>

						<button type="button" class="btn btn-warning btn-sm">
							<i class="bi bi-bag me-1"></i>
							<b>ICBPER: S/. {{item.icbper}}</b>
						</button>

						<button type="button" class="btn btn-success btn-sm">
							<i class="bi bi-cash-coin me-1"></i>
							<b>SUBTOTAL: S/. {{item.subtotal}}</b>
						</button>
					</div>

					<div class="mb-3">
						<label class="form-label">Descripción del item de venta</label>
						<textarea class="form-control" v-model="item.descripcion" rows="3" maxlength="250"></textarea>
					</div>

					<div class="text-center">
						<button type="button" class="btn btn-success" v-on:click="phuyu_itemcalcular_cerrar(item)">
							<i class="bi bi-save me-1"></i>
							GUARDAR CAMBIOS Y CERRAR
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="modal_reportes" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-fullscreen">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">
						<i class="bi bi-file-earmark-pdf me-1"></i>
						<b><?php echo $_SESSION["phuyu_empresa"]." - ".$_SESSION["phuyu_sucursal"];?></b>
					</h4>

					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>

				<div class="modal-body p-0" id="reportes_modal" style="height:450px;">
					<iframe id="phuyu_pdf" src="" style="width:100%; height:100%; border:none;"></iframe>
				</div>
			</div>
		</div>
	</div>

</div>

<script src="<?php echo base_url();?>phuyu/phuyu_pedidos/editar.js"></script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas_2.js"></script>

<script>
	var pantalla = jQuery(document).height();
	$("#reportes_modal").css({height: pantalla - 65});

	document.querySelectorAll('.phuyu-table-drag').forEach(function(el) {
		let isDown = false;
		let startX;
		let scrollLeft;

		el.addEventListener('mousedown', function(e) {
			if (e.target.closest('input, select, button, textarea, a')) return;

			isDown = true;
			el.classList.add('active');
			startX = e.pageX - el.offsetLeft;
			scrollLeft = el.scrollLeft;
		});

		el.addEventListener('mouseleave', function() {
			isDown = false;
			el.classList.remove('active');
		});

		el.addEventListener('mouseup', function() {
			isDown = false;
			el.classList.remove('active');
		});

		el.addEventListener('mousemove', function(e) {
			if (!isDown) return;

			e.preventDefault();
			const x = e.pageX - el.offsetLeft;
			const walk = (x - startX) * 1.5;
			el.scrollLeft = scrollLeft - walk;
		});
	});
</script>