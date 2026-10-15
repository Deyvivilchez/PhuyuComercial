<style>
#phuyu_operacion label,
#phuyu_operacion .form-label {
	font-size: 11px;
	font-weight: 800;
	color: #343a40;
	margin-bottom: 6px;
	text-transform: uppercase;
}

#phuyu_operacion .form-control,
#phuyu_operacion .form-select {
	border-radius: 9px;
}

#phuyu_operacion .btn {
	white-space: nowrap;
}

.phuyu-edit-card {
	border: 0;
	border-radius: 16px;
	box-shadow: 0 10px 35px rgba(15, 23, 42, .06);
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
	background: rgba(240, 101, 72, .12);
}

.phuyu-block,
.phuyu-cliente-panel {
	border: 1px solid rgba(64, 81, 137, .12);
	border-radius: 16px;
	padding: 16px;
	background: #fff;
}

.phuyu-block-soft {
	background: #f8fafc;
}

.phuyu-label-cliente {
	color: #405189;
	font-weight: 900;
	letter-spacing: .35px;
}

.phuyu-cliente-select-box {
	padding: 4px;
	border-radius: 13px;
	background: linear-gradient(135deg, rgba(64,81,137,.13), rgba(10,179,156,.12));
	border: 1px solid rgba(64,81,137,.15);
}

.phuyu-cliente-select-box .select2-container {
	width: 100% !important;
}

.phuyu-cliente-select-box .select2-selection--single {
	height: 40px !important;
	border-radius: 9px !important;
	border: 1px solid #d6dce5 !important;
	display: flex !important;
	align-items: center !important;
}

.phuyu-cliente-select-box .select2-selection__rendered {
	font-weight: 700 !important;
	font-size: 12px !important;
	color: #343a40 !important;
	line-height: 40px !important;
}

.phuyu-btn-icon-only {
	width: 40px;
	height: 40px;
	padding: 0;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	border-radius: 10px;
}

.phuyu-btn-text-icon {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	gap: .45rem;
	font-weight: 700;
	border-radius: 10px;
}

.phuyu-table-wrapper {
	border: 1px solid #eef1f4;
	border-radius: 14px;
	overflow: auto;
}

.phuyu-tabla-detalle {
	font-size: 11px;
	min-width: 1080px;
	margin-bottom: 0;
}

.phuyu-tabla-detalle thead th {
	background: #f3f6f9;
	font-weight: 800;
	text-transform: uppercase;
	white-space: nowrap;
	color: #343a40;
}

.phuyu-tabla-detalle td {
	vertical-align: middle;
}

.phuyu-tabla-detalle .form-control,
.phuyu-tabla-detalle .form-select {
	min-height: 34px;
	font-size: 12px;
	min-width: 90px;
}

.phuyu-product-name {
	min-width: 260px;
	font-weight: 700;
}

.phuyu-btn-mas {
	min-width: 74px;
	border-radius: 8px;
}

.phuyu-btn-delete {
	width: 32px;
	height: 32px;
	padding: 0;
	display: inline-flex;
	align-items: center;
	justify-content: center;
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
		<input type="hidden" id="stockalmacen" value="<?php echo $_SESSION["phuyu_stockalmacen"];?>">
		<input type="hidden" id="itemrepetir" value="<?php echo $_SESSION["phuyu_itemrepetir"];?>">
		<input type="hidden" id="igvsunat" value="<?php echo $_SESSION["phuyu_igv"];?>">
		<input type="hidden" id="icbpersunat" value="<?php echo $_SESSION["phuyu_icbper"];?>">
		<input type="hidden" id="formato" value="<?php echo $_SESSION['phuyu_formatoproforma'];?>">
		<input type="hidden" id="codproforma" value="<?php echo $info[0]['codproforma'];?>">
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
						<h4 class="mb-0">EDITAR PROFORMA</h4>
					</div>

					<div class="phuyu-block phuyu-block-soft mb-3">
						<div class="row g-3">
							<div class="col-lg-2 col-md-6">
								<label class="form-label">Tipo comprobante</label>
								<select class="form-select" name="codcomprobantetipo" v-model="campos.codcomprobantetipo" required v-on:change="phuyu_series()">
									<?php foreach ($comprobantes as $key => $value) { ?>
										<option value="<?php echo $value["codcomprobantetipo"];?>">
											<?php echo $value["descripcion"];?>
										</option>
									<?php } ?>
								</select>
							</div>

							<div class="col-lg-2 col-md-6">
								<label class="form-label">Serie <b>(Nro: <?php echo $info[0]["nrocomprobante"];?>)</b></label>
								<select class="form-select" id="seriecomprobante" v-model="campos.seriecomprobante" v-on:change="phuyu_correlativo()" required>
									<option value="">SERIE</option>
									<option v-for="dato in series" v-bind:value="dato.seriecomprobante">
										{{dato.seriecomprobante}}
									</option>
								</select>
							</div>

							<div class="col-lg-2 col-md-6">
								<label class="form-label">Condición pago</label>
								<select class="form-select" name="condicionpago" v-model="campos.condicionpago">
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
								<label class="form-label">Fecha proforma</label>
								<input type="date" class="form-control" name="fechacomprobante" id="fechacomprobante" value="<?php echo $info[0]["fechaproforma"];?>" autocomplete="off" required>
							</div>
						</div>
					</div>

					<div class="phuyu-cliente-panel mb-3">
						<div class="row g-3 align-items-end">
							<div class="col-lg-3 col-md-6">
								<label class="phuyu-label-cliente">
									<i class="bi bi-person-check me-1"></i>
									Seleccionar cliente
								</label>

								<div class="phuyu-cliente-select-box">
									<select class="form-select" name="codpersona" id="codpersona" required>
										<option value="<?php echo $info[0]["codpersona"];?>">
											<?php echo $info[0]["razonsocial"];?>
										</option>
									</select>
								</div>
							</div>

							<div class="col-lg-1 col-md-2">
								<label class="d-block">&nbsp;</label>
								<button type="button" class="btn btn-primary phuyu-btn-icon-only w-100" v-on:click="phuyu_addcliente()" title="Agregar cliente">
									<i class="bi bi-person-plus"></i>
								</button>
							</div>

							<div class="col-lg-4 col-md-6">
								<label class="form-label">Cliente de la proforma</label>
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
							<label class="form-label">Glosa de la proforma</label>
							<input type="text" class="form-control" maxlength="150" v-model="campos.descripcion">
						</div>

						<div class="col-lg-3 col-md-4">
							<button type="button" class="btn btn-success phuyu-btn-text-icon w-100" v-on:click="phuyu_item()">
								<i class="bi bi-search"></i>
								<span>Buscar productos</span>
							</button>
						</div>
					</div>

					<div class="table-responsive phuyu-table-wrapper">
						<table class="table table-sm table-striped table-hover align-middle phuyu-tabla-detalle">
							<thead>
								<tr>
									<th>Acción</th>
									<th width="30%">Producto</th>
									<th width="13%">Unidad</th>
									<th width="10%">Cantidad</th>
									<th width="10%">Precio unit.</th>
									<th width="7%">I.G.V.</th>
									<th width="7%">ICBPER</th>
									<th width="10%">Subtotal</th>
									<th class="text-center"><i class="bi bi-trash"></i></th>
								</tr>
							</thead>

							<tbody>
								<tr v-for="(dato,index) in detalle">
									<td>
										<button type="button" data-bs-target="#modal_itemdetalle" class="btn btn-primary btn-sm phuyu-btn-mas" v-on:click="phuyu_itemdetalle(index,dato)">
											<i class="bi bi-plus-circle me-1"></i> Más
										</button>
									</td>

									<td class="phuyu-product-name">{{dato.producto}}</td>

									<td>
										<select class="form-select number unidad" v-model="dato.codunidad" v-on:change="informacion_unidad(index,dato,this.value)" id="codunidad">
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

									<td>
										<input type="number" step="0.0001" class="form-control number" v-model.number="dato.cantidad" v-on:keyup="phuyu_calcular(dato)" min="0.0001" required>
									</td>

									<td>
										<input type="number" step="0.0001" class="form-control number" v-if="dato.codafectacionigv==21" v-model.number="dato.precio" min="0" readonly>
										<input type="number" step="0.0001" class="form-control number" v-if="dato.codafectacionigv!=21" v-model.number="dato.precio" v-on:keyup="phuyu_calcular(dato)" min="0.001" required v-bind:disabled="dato.porcdescuento==100">
									</td>

									<td><input type="number" class="form-control number" v-model.number="dato.igv" min="0" readonly></td>
									<td><input type="number" class="form-control number" v-model.number="dato.icbper" min="0" readonly></td>

									<td v-if="dato.codafectacionigv==21">
										<input type="number" step="0.01" class="form-control number" v-model.number="dato.subtotal">
									</td>

									<td v-if="dato.codafectacionigv!=21">
										<input type="number" step="0.01" class="form-control number" v-if="dato.calcular==0" v-model.number="dato.subtotal" readonly>
										<input type="number" step="0.01" class="form-control number" v-if="dato.calcular!=0" v-model.number="dato.subtotal" v-on:keyup="phuyu_subtotal(dato)" required v-bind:disabled="dato.porcdescuento==100">
									</td>

									<td>
										<button type="button" class="btn btn-danger btn-sm phuyu-btn-delete" v-on:click="phuyu_deleteitem(index,dato)">
											<i class="bi bi-x-lg"></i>
										</button>
									</td>
								</tr>
							</tbody>

							<tfoot>
								<tr>
									<td colspan="7" class="totales">Subtotal</td>
									<td class="text-center">{{totales.valorventa}}</td>
									<td></td>
								</tr>
								<tr>
									<td colspan="7" class="totales">I.G.V</td>
									<td class="text-center">{{totales.igv}}</td>
									<td></td>
								</tr>
								<tr>
									<td colspan="7" class="totaltotal">Total</td>
									<td class="text-center fs-6">
										<b class="text-danger">{{totales.importe}}</b>
									</td>
									<td></td>
								</tr>
							</tfoot>
						</table>
					</div>

					<div class="border-top pt-3 mt-3">
						<div class="d-flex justify-content-end flex-wrap gap-2">
							<button type="button" class="btn btn-warning phuyu-btn-text-icon" v-on:click="phuyu_venta()" disabled>
								<i class="bi bi-plus-circle"></i>
								<span>Nuevo pedido</span>
							</button>

							<button type="submit" class="btn btn-info phuyu-btn-text-icon text-white" v-bind:disabled="estado==1">
								<i class="bi bi-save"></i>
								<span>Guardar cambios</span>
							</button>

							<button type="button" class="btn btn-danger phuyu-btn-text-icon" v-on:click="phuyu_atras()">
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
							<table class="table table-sm table-striped mb-0">
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
						<button type="button" class="btn btn-light" data-bs-dismiss="modal">
							<i class="bi bi-x-circle me-1"></i>
							VOLVER AL FORMULARIO
						</button>

						<button type="button" class="btn btn-success" v-on:click="phuyu_itemcalcular_cerrar(item)">
							<i class="bi bi-save me-1"></i>
							GUARDAR CAMBIOS
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="modal_masconfiguraciones" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">
						<i class="bi bi-sliders me-1"></i>
						<b>DETALLE DEL ITEM</b>
					</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>

				<div class="modal-body">
					<div class="row g-3">
						<div class="col-md-4 text-center">
							<label class="form-label d-block">Recogido</label>
							<input type="checkbox" style="height:20px;width:20px;" v-model="campos.retirar" disabled="true">
						</div>

						<div class="col-md-4">
							<label class="form-label">Centro costo</label>
							<select class="form-select" v-model="campos.codcentrocosto">
								<option value="0">SIN CENTRO COSTO</option>
								<?php foreach ($centrocostos as $key => $value) { ?>
									<option value="<?php echo $value["codcentrocosto"];?>">
										<?php echo $value["descripcion"];?>
									</option>
								<?php } ?>
							</select>
						</div>

						<div class="col-md-4" v-if="rubro==1">
							<label class="form-label">Nro placa</label>
							<input type="text" class="form-control" v-model="campos.nroplaca" autocomplete="off" maxlength="50" placeholder="Nro placa...">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="modal_itemdetalle" class="modal fade" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header modal-phuyu-titulo">
					<h4 class="modal-title">
						<i class="bi bi-box-seam me-1"></i>
						<b>DETALLE DEL ITEM</b>
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
							<label class="form-label">Descuento precio (S/.)</label>
							<input type="number" class="form-control number" v-model.number="item.descuento" v-on:keyup="phuyu_itemcalcular(item,-1)" v-bind:disabled="item.codafectacionigv==21">
						</div>

						<div class="col-md-4">
							<label class="form-label">Descuento precio (%)</label>
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
								<option value="10">GRAVADO</option> 
								<option value="20">EXONERADO</option> 
								<option value="21">GRATUITO</option> 
								<option value="30">INAFECTO</option>
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
							<b>VALOR VENTA: S/. {{item.valorventa}}</b>
						</button>
						<button type="button" class="btn btn-danger btn-sm">
							<b>IGV: S/. {{item.igv}}</b>
						</button>
						<button type="button" class="btn btn-warning btn-sm">
							<b>ICBPER: S/. {{item.icbper}}</b>
						</button>
						<button type="button" class="btn btn-success btn-sm">
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
			<div class="modal-content border-0 rounded-0">
				<div class="modal-header">
					<h4 class="modal-title mb-0 w-100 text-center">
						<i class="bi bi-file-earmark-pdf me-2"></i>
						<b style="letter-spacing:4px;">
							<?php echo $_SESSION["phuyu_empresa"]." - ".$_SESSION["phuyu_sucursal"];?>
						</b>
					</h4>

					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>

				<div class="modal-body p-0" id="reportes_modal" style="height:450px;">
					<iframe id="phuyu_pdf" src="" style="width:100%; height:100%; border:none;"></iframe>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_proformas/editar.js"></script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas_2.js"></script>

<script>
	var pantalla = jQuery(document).height();
	$("#reportes_modal").css({height: pantalla - 65});

	if (typeof Select2Controls !== 'undefined') {
		let select2Controls = new Select2Controls();
	}
</script>