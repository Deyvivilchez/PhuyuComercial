<?php
	$comprobante_default = count($comprobantes) > 0 ? $comprobantes[0] : ["codcomprobantetipo" => 30, "seriecomprobante" => ""];
	$referencia_default = count($sucursalreferencia) > 0 ? $sucursalreferencia[0] : ["codcomprobantetipo" => 12, "seriecomprobante" => ""];
	$disabled = $_SESSION["phuyu_codperfil"] > 3 ? "disabled" : "";
?>

<div id="phuyu_operacion" class="phuyu-pedido-compra-form">
	<form id="formulario" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" id="comprobante" value="<?php echo $comprobante_default["codcomprobantetipo"];?>">
		<input type="hidden" id="serie" value="<?php echo $comprobante_default["seriecomprobante"];?>">
		<input type="hidden" id="comprobantereferencia" value="<?php echo $referencia_default["codcomprobantetipo"];?>">
		<input type="hidden" id="seriereferencia" value="<?php echo $referencia_default["seriecomprobante"];?>">
		<input type="hidden" id="stockalmacen" value="<?php echo $_SESSION["phuyu_stockalmacen"];?>">
		<input type="hidden" id="itemrepetir" value="<?php echo $_SESSION["phuyu_itemrepetir"];?>">
		<input type="hidden" id="igvsunat" value="<?php echo $_SESSION["phuyu_igv"];?>">
		<input type="hidden" id="icbpersunat" value="<?php echo $_SESSION["phuyu_icbper"];?>">
		<input type="hidden" id="formato" value="<?php echo $_SESSION["phuyu_formatopedido"];?>">
		<input type="hidden" id="seriecomprobantereferencia" v-model="campos.seriecomprobantereferencia">
		<input type="hidden" id="empleado" value="<?php echo $_SESSION["phuyu_codpersona"];?>">
		<input type="hidden" name="fechakardex" id="fechakardex" value="<?php echo date("Y-m-d");?>">

		<div class="phuyu_body">
			<div class="card phuyu-card-form">
				<div class="card-body">
					<div class="phuyu-form-header">
						<div class="phuyu-form-icon">
							<i class="bi bi-cart-plus"></i>
						</div>
						<div class="flex-grow-1">
							<div class="text-muted small text-uppercase fw-semibold">Compras</div>
							<h5 class="mb-0 fw-bold">Nuevo pedido de compra</h5>
						</div>
						<div class="badge bg-primary-subtle text-primary fs-12">
							Nro {{campos.nro || '...'}}
						</div>
					</div>

					<div class="row g-3 mb-3">
						<div class="col-12 col-md-6 col-xl-3">
							<label class="form-label">Tipo comprobante</label>
							<select class="form-select" name="codcomprobantetipo" v-model="campos.codcomprobantetipo" required v-on:change="phuyu_series()">
								<?php foreach ($comprobantes as $value) { ?>
									<option value="<?php echo $value["codcomprobantetipo"];?>"><?php echo $value["descripcion"];?></option>
								<?php } ?>
							</select>
						</div>

						<div class="col-12 col-md-6 col-xl-2">
							<label class="form-label">Serie</label>
							<select class="form-select" id="seriecomprobante" v-model="campos.seriecomprobante" v-on:change="phuyu_correlativo()" required>
								<option value="">Serie</option>
								<option v-for="dato in series" v-bind:value="dato.seriecomprobante">{{dato.seriecomprobante}}</option>
							</select>
						</div>

						<div class="col-12 col-md-6 col-xl-2">
							<label class="form-label">Fecha pedido</label>
							<input type="date" class="form-control" name="fechacomprobante" id="fechacomprobante" value="<?php echo date("Y-m-d");?>" autocomplete="off" required>
						</div>

						<div class="col-12 col-md-6 col-xl-2">
							<label class="form-label">Fecha entrega</label>
							<input type="date" class="form-control" name="fechaentrega" id="fechaentrega" value="<?php echo date("Y-m-d");?>" autocomplete="off">
						</div>

						<div class="col-12 col-md-6 col-xl-3">
							<label class="form-label">Responsable</label>
							<select class="form-select" id="codempleado" name="codempleado" <?php echo $disabled;?> v-model="campos.codempleado" required>
								<option value="0">Sin responsable</option>
								<?php foreach ($vendedores as $value) { ?>
									<option value="<?php echo $value["codpersona"];?>"><?php echo $value["razonsocial"];?></option>
								<?php } ?>
							</select>
						</div>
					</div>

					<div class="phuyu-section mb-3">
						<div class="row g-3 align-items-end">
							<div class="col-12 col-lg-4">
								<label class="form-label">Seleccionar proveedor</label>
								<div class="phuyu-select-wrap">
									<select class="form-select ajax" name="codpersona" v-model="campos.codpersona" id="codpersona" required data-live-search="true" v-on:change="phuyu_infocliente()">
										<option value="2">PROVEEDORES VARIOS</option>
									</select>
								</div>
							</div>

							<div class="col-12 col-sm-2 col-lg-1">
								<label class="form-label d-none d-lg-block">&nbsp;</label>
								<button type="button" class="btn btn-primary phuyu-btn-icon-only w-100" v-on:click="phuyu_addcliente()" title="Agregar proveedor">
									<i class="bi bi-person-plus"></i>
								</button>
							</div>

							<div class="col-12 col-lg-3">
								<label class="form-label">Proveedor del pedido</label>
								<input type="text" class="form-control" id="cliente" v-model.trim="campos.cliente" autocomplete="off" maxlength="250" placeholder="Razon social del proveedor" required>
							</div>

							<div class="col-12 col-lg-4">
								<label class="form-label">Direccion proveedor</label>
								<input type="text" class="form-control" id="direccion" v-model.trim="campos.direccion" autocomplete="off" maxlength="250" placeholder="Direccion del proveedor" required>
							</div>
						</div>
					</div>

					<div class="row g-3 align-items-end mb-3">
						<div class="col-12 col-md-4 col-xl-2">
							<label class="form-label">Condicion pago</label>
							<select class="form-select" name="condicionpago" v-model="campos.condicionpago" v-on:change="phuyu_condicionpago()">
								<option value="1">Contado</option>
								<option value="2">Credito</option>
							</select>
						</div>

						<?php if ($_SESSION["phuyu_rubro"] == 1) { ?>
							<div class="col-12 col-md-4 col-xl-2">
								<label class="form-label">Nro placa</label>
								<input type="text" class="form-control" v-model="campos.nroplaca" autocomplete="off" maxlength="100" placeholder="Nro placa">
							</div>
							<div class="col-12 col-md-8 col-xl-5">
								<label class="form-label">Glosa del pedido</label>
								<input type="text" class="form-control" v-model="campos.descripcion" autocomplete="off" maxlength="250" placeholder="Referencia del pedido">
							</div>
						<?php } else { ?>
							<div class="col-12 col-md-8 col-xl-7">
								<label class="form-label">Glosa del pedido</label>
								<input type="text" class="form-control" v-model="campos.descripcion" autocomplete="off" maxlength="250" placeholder="Referencia del pedido">
							</div>
						<?php } ?>

						<div class="col-12 col-md-4 col-xl-3">
							<button type="button" class="btn btn-success w-100 phuyu-btn-text-icon" v-on:click="phuyu_item()">
								<i class="bi bi-search"></i>
								<span>Buscar productos</span>
							</button>
						</div>
					</div>

					<div class="phuyu-table-wrap mb-3">
						<table class="table table-hover align-middle mb-0 phuyu-detalle-table">
							<thead>
								<tr>
									<th style="width:90px;">Item</th>
									<th style="min-width:260px;">Producto</th>
									<th style="min-width:120px;">Unidad</th>
									<th class="text-end">Stock</th>
									<th style="min-width:120px;">Cantidad</th>
									<th style="min-width:130px;">Precio unit.</th>
									<th style="min-width:105px;">I.G.V.</th>
									<th style="min-width:105px;">ICBPER</th>
									<th style="min-width:125px;">Subtotal</th>
									<th class="text-center" style="width:56px;"><i class="bi bi-trash3"></i></th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="(dato,index) in detalle">
									<td>
										<button type="button" class="btn btn-outline-primary btn-sm phuyu-btn-text-icon" v-on:click="phuyu_itemdetalle(index,dato)">
											<i class="bi bi-sliders"></i>
											<span>Mas</span>
										</button>
									</td>
									<td class="fw-semibold">{{dato.producto}}</td>
									<td>
										<select class="form-select form-select-sm unidad" v-model="dato.codunidad" v-on:change="informacion_unidad(index,dato)" id="codunidad">
											<template v-for="(unidads, und) in dato.unidades">
												<option v-bind:value="unidads.codunidad" v-if="unidads.factor==1" selected>{{unidads.descripcion}}</option>
												<option v-bind:value="unidads.codunidad" v-if="unidads.factor!=1">{{unidads.descripcion}}</option>
											</template>
										</select>
									</td>
									<td class="text-end fw-bold text-danger">{{dato.stock}}</td>
									<td>
										<input type="number" step="0.0001" class="form-control form-control-sm number" v-if="dato.control==1" v-model.number="dato.cantidad" v-on:keyup="phuyu_calcular(dato)" min="0.0001" v-bind:max="dato.stock" required>
										<input type="number" step="0.0001" class="form-control form-control-sm number" v-if="dato.control==0" v-model.number="dato.cantidad" v-on:keyup="phuyu_calcular(dato)" min="0.0001" required>
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
										<input type="number" step="0.01" class="form-control form-control-sm number" v-if="dato.calcular==0" v-model.number="dato.subtotal" readonly>
										<input type="number" step="0.01" class="form-control form-control-sm number" v-if="dato.calcular!=0" v-model.number="dato.subtotal" v-on:keyup="phuyu_subtotal(dato)" required v-bind:disabled="dato.porcdescuento==100">
									</td>
									<td class="text-center">
										<button type="button" class="btn btn-danger btn-sm phuyu-btn-icon-only" v-on:click="phuyu_deleteitem(index,dato)">
											<i class="bi bi-trash3"></i>
										</button>
									</td>
								</tr>
								<tr v-if="detalle.length==0">
									<td colspan="10" class="text-center text-muted py-4">
										<i class="bi bi-inbox me-1"></i> Agregue productos al pedido
									</td>
								</tr>
							</tbody>
						</table>
					</div>

					<div class="phuyu-totals-grid mb-3">
						<div class="phuyu-total-box">
							<span>Importe</span>
							<strong>S/ {{totales.bruto}}</strong>
						</div>
						<div class="phuyu-total-box">
							<span>Descuento</span>
							<strong>S/ {{totales.descuentos}}</strong>
						</div>
						<div class="phuyu-total-box">
							<span>Gravada</span>
							<strong>S/ {{operaciones.gravadas}}</strong>
						</div>
						<div class="phuyu-total-box">
							<span>Exonerada</span>
							<strong>S/ {{operaciones.exoneradas}}</strong>
						</div>
						<div class="phuyu-total-box">
							<span>Inafecta</span>
							<strong>S/ {{operaciones.inafectas}}</strong>
						</div>
						<div class="phuyu-total-box">
							<span>Gratuita</span>
							<strong>S/ {{operaciones.gratuitas}}</strong>
						</div>
						<div class="phuyu-total-box">
							<span>IGV</span>
							<strong>S/ {{totales.igv}}</strong>
						</div>
						<div class="phuyu-total-box">
							<span>ISC</span>
							<strong>S/ {{totales.isc}}</strong>
						</div>
						<div class="phuyu-total-box">
							<span>ICBPER</span>
							<strong>S/ {{totales.icbper}}</strong>
						</div>
						<div class="phuyu-total-box phuyu-total-final">
							<span>Total</span>
							<strong>S/ {{totales.importe}}</strong>
						</div>
					</div>

					<div class="phuyu-actions">
						<button type="button" class="btn btn-warning phuyu-btn-text-icon" v-on:click="phuyu_venta()">
							<i class="bi bi-plus-circle"></i>
							<span>Nuevo pedido</span>
						</button>
						<button type="submit" class="btn btn-primary phuyu-btn-text-icon" v-bind:disabled="estado==1">
							<i class="bi bi-save"></i>
							<span>Guardar pedido</span>
						</button>
						<button type="button" class="btn btn-danger phuyu-btn-text-icon" v-on:click="phuyu_atras()">
							<i class="bi bi-arrow-left-circle"></i>
							<span>Atras</span>
						</button>
					</div>
				</div>
			</div>
		</div>
	</form>

	<div id="modal_cuotas" class="modal fade" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						<i class="bi bi-calendar2-check me-1"></i> Cuotas de pago al credito
					</h5>
					<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>
				<div class="modal-body">
					<div class="row g-3 mb-3" v-if="campos.condicionpago==2">
						<div class="col-12 col-md-4">
							<label class="form-label">Nro dias</label>
							<input class="form-control" name="nrodias" v-model="campos.nrodias" v-on:keyup="phuyu_cuotas()" required>
						</div>
						<div class="col-12 col-md-4">
							<label class="form-label">Cuotas</label>
							<input class="form-control" name="nrocuotas" v-model="campos.nrocuotas" v-on:keyup="phuyu_cuotas()" required>
						</div>
						<div class="col-12 col-md-4">
							<label class="form-label">Tasa interes (%)</label>
							<input class="form-control" name="tasainteres" v-model="campos.tasainteres" v-on:keyup="phuyu_cuotas()" required>
						</div>
					</div>

					<div v-if="campos.condicionpago==2">
						<div class="table-responsive phuyu-table-wrap mb-3">
							<table class="table table-hover align-middle mb-0">
								<thead>
									<tr>
										<th>Fecha vence</th>
										<th class="text-end">Importe</th>
										<th class="text-end">Interes</th>
										<th class="text-end">Total</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="dato in cuotas">
										<td>{{dato.fechavence}}</td>
										<td class="text-end">{{dato.importe}}</td>
										<td class="text-end">{{dato.interes}}</td>
										<td class="text-end fw-bold">{{dato.total}}</td>
									</tr>
								</tbody>
							</table>
						</div>

						<div class="d-flex flex-wrap gap-2 justify-content-end">
							<span class="badge bg-warning-subtle text-warning fs-12">Interes: S/ {{totales.interes}}</span>
							<span class="badge bg-danger-subtle text-danger fs-12">Total credito: S/ {{campos.totalcredito}}</span>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-light" data-bs-dismiss="modal">
						<i class="bi bi-arrow-left me-1"></i> Volver
					</button>
					<button type="button" class="btn btn-primary" v-on:click="phuyu_pagar()">
						<i class="bi bi-save me-1"></i> Guardar pedido
					</button>
				</div>
			</div>
		</div>
	</div>

	<div id="modal_masconfiguraciones" class="modal fade" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						<i class="bi bi-sliders me-1"></i> Configuracion del pedido
					</h5>
					<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>
				<div class="modal-body">
					<div class="row g-3">
						<div class="col-12 col-md-4">
							<label class="form-label">Recogido</label>
							<div class="form-check form-switch">
								<input type="checkbox" class="form-check-input" v-model="campos.retirar" disabled>
							</div>
						</div>
						<div class="col-12 col-md-5">
							<label class="form-label">Centro costo</label>
							<select class="form-select" v-model="campos.codcentrocosto">
								<option value="0">Sin centro costo</option>
								<?php foreach ($centrocostos as $value) { ?>
									<option value="<?php echo $value["codcentrocosto"];?>"><?php echo $value["descripcion"];?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-12 col-md-3" v-if="rubro==1">
							<label class="form-label">Nro placa</label>
							<input type="text" class="form-control" v-model="campos.nroplaca" autocomplete="off" maxlength="50">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="modal_itemdetalle" class="modal fade" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-lg modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">
						<i class="bi bi-box-seam me-1"></i> Detalle del item
					</h5>
					<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>
				<div class="modal-body">
					<div class="alert alert-light border mb-3">
						<div class="fw-bold">{{item.producto}}</div>
						<div class="text-muted small">Cantidad: {{item.cantidad}} {{item.unidad}}</div>
					</div>

					<div class="row g-3 mb-3">
						<div class="col-12 col-md-4">
							<label class="form-label">Precio bruto</label>
							<input type="number" class="form-control number" v-model.number="item.preciobruto" v-on:keyup="phuyu_itemcalcular(item,0)" v-bind:disabled="item.codafectacionigv==21">
						</div>
						<div class="col-12 col-md-4">
							<label class="form-label">Descuento precio (S/)</label>
							<input type="number" class="form-control number" v-model.number="item.descuento" v-on:keyup="phuyu_itemcalcular(item,-1)" v-bind:disabled="item.codafectacionigv==21">
						</div>
						<div class="col-12 col-md-4">
							<label class="form-label">Descuento precio (%)</label>
							<input type="number" class="form-control number" v-model.number="item.porcdescuento" v-on:keyup="phuyu_itemcalcular(item,-2)" v-bind:disabled="item.codafectacionigv==21">
						</div>
					</div>

					<div class="row g-3 mb-3">
						<div class="col-12 col-md-3">
							<label class="form-label">Precio sin IGV</label>
							<input type="number" class="form-control number" v-model.number="item.preciosinigv" v-on:keyup="phuyu_itemcalcular(item,1)" v-bind:disabled="item.codafectacionigv==21">
						</div>
						<div class="col-12 col-md-3">
							<label class="form-label">Precio unitario</label>
							<input type="number" class="form-control number" v-model.number="item.precio" v-on:keyup="phuyu_itemcalcular(item,2)" v-bind:disabled="item.codafectacionigv==21">
						</div>
						<div class="col-12 col-md-3">
							<label class="form-label">Tipo afectacion</label>
							<select class="form-select" v-model="item.codafectacionigv" v-on:change="phuyu_itemcalcular(item,2)">
								<option value="10">Gravado</option>
								<option value="20">Exonerado</option>
								<option value="21">Gratuito</option>
								<option value="30">Inafecto</option>
							</select>
						</div>
						<div class="col-12 col-md-3">
							<label class="form-label">ICBPER</label>
							<select class="form-select" v-model="item.conicbper" v-on:change="phuyu_itemcalcular(item,2)">
								<option value="1">Si</option>
								<option value="0">No</option>
							</select>
						</div>
					</div>

					<div class="phuyu-item-summary mb-3">
						<span>Valor venta: S/ {{item.valorventa}}</span>
						<span>IGV: S/ {{item.igv}}</span>
						<span>ICBPER: S/ {{item.icbper}}</span>
						<strong>Subtotal: S/ {{item.subtotal}}</strong>
					</div>

					<div class="mb-3">
						<label class="form-label">Descripcion del item</label>
						<textarea class="form-control" v-model="item.descripcion" rows="3" maxlength="250"></textarea>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-light" data-bs-dismiss="modal">
						<i class="bi bi-x-circle me-1"></i> Cancelar
					</button>
					<button type="button" class="btn btn-primary" v-on:click="phuyu_itemcalcular_cerrar(item)">
						<i class="bi bi-check2-circle me-1"></i> Guardar cambios
					</button>
				</div>
			</div>
		</div>
	</div>

	<div id="modal_reportes" class="modal fade" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-fullscreen">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title fw-bold"><?php echo $_SESSION["phuyu_empresa"]." - ".$_SESSION["phuyu_sucursal"];?></h5>
					<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>
				<div class="modal-body p-0" id="reportes_modal">
					<iframe id="phuyu_pdf" src="" class="w-100 h-100 border-0"></iframe>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
	#phuyu_operacion.phuyu-pedido-compra-form .phuyu-card-form {
		border: 1px solid rgba(64, 81, 137, .10);
		border-radius: .9rem;
		box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
	}

	#phuyu_operacion.phuyu-pedido-compra-form .phuyu-form-header {
		display: flex;
		align-items: center;
		gap: .75rem;
		padding-bottom: 1rem;
		margin-bottom: 1rem;
		border-bottom: 1px solid rgba(64, 81, 137, .10);
	}

	#phuyu_operacion.phuyu-pedido-compra-form .phuyu-form-icon {
		width: 44px;
		height: 44px;
		border-radius: 12px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: rgba(64, 81, 137, .10);
		color: #405189;
		font-size: 1.35rem;
	}

	#phuyu_operacion.phuyu-pedido-compra-form .form-label {
		font-size: .74rem;
		font-weight: 800;
		text-transform: uppercase;
		letter-spacing: .03em;
		color: #495057;
		margin-bottom: .4rem;
	}

	#phuyu_operacion.phuyu-pedido-compra-form .form-control,
	#phuyu_operacion.phuyu-pedido-compra-form .form-select,
	#phuyu_operacion.phuyu-pedido-compra-form .select2-container .select2-selection {
		border-color: rgba(64, 81, 137, .16);
		min-height: 40px;
	}

	#phuyu_operacion.phuyu-pedido-compra-form .select2-container {
		width: 100% !important;
	}

	#phuyu_operacion.phuyu-pedido-compra-form .select2-selection--single {
		display: flex !important;
		align-items: center !important;
		border-radius: .375rem !important;
		height: 40px !important;
	}

	#phuyu_operacion.phuyu-pedido-compra-form .select2-selection__rendered {
		line-height: 40px !important;
	}

	#phuyu_operacion.phuyu-pedido-compra-form .phuyu-section {
		padding: 1rem;
		border: 1px solid rgba(64, 81, 137, .10);
		border-radius: .75rem;
		background: #f8fafc;
	}

	#phuyu_operacion.phuyu-pedido-compra-form .phuyu-table-wrap {
		border: 1px solid rgba(64, 81, 137, .10);
		border-radius: .75rem;
		overflow: auto;
	}

	#phuyu_operacion.phuyu-pedido-compra-form table thead th {
		background: #f8fafc;
		color: #495057;
		font-size: .74rem;
		font-weight: 800;
		text-transform: uppercase;
		white-space: nowrap;
		border-bottom: 1px solid rgba(64, 81, 137, .12);
	}

	#phuyu_operacion.phuyu-pedido-compra-form table tbody td {
		font-size: .86rem;
		vertical-align: middle;
	}

	#phuyu_operacion.phuyu-pedido-compra-form .phuyu-detalle-table {
		min-width: 1180px;
	}

	#phuyu_operacion.phuyu-pedido-compra-form .phuyu-btn-text-icon {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		gap: .45rem;
		font-weight: 700;
	}

	#phuyu_operacion.phuyu-pedido-compra-form .phuyu-btn-icon-only {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		width: 38px;
		height: 38px;
		padding: 0;
	}

	#phuyu_operacion.phuyu-pedido-compra-form .phuyu-totals-grid {
		display: grid;
		grid-template-columns: repeat(5, minmax(130px, 1fr));
		gap: .75rem;
	}

	#phuyu_operacion.phuyu-pedido-compra-form .phuyu-total-box {
		border: 1px solid rgba(64, 81, 137, .10);
		border-radius: .75rem;
		padding: .75rem;
		background: #fff;
	}

	#phuyu_operacion.phuyu-pedido-compra-form .phuyu-total-box span {
		display: block;
		color: #6c757d;
		font-size: .72rem;
		font-weight: 800;
		text-transform: uppercase;
		margin-bottom: .25rem;
	}

	#phuyu_operacion.phuyu-pedido-compra-form .phuyu-total-box strong {
		font-size: .98rem;
		color: #212529;
	}

	#phuyu_operacion.phuyu-pedido-compra-form .phuyu-total-final {
		background: #fff5f5;
		border-color: rgba(240, 101, 72, .18);
	}

	#phuyu_operacion.phuyu-pedido-compra-form .phuyu-total-final strong {
		color: #f06548;
		font-size: 1.12rem;
	}

	#phuyu_operacion.phuyu-pedido-compra-form .phuyu-actions {
		display: flex;
		justify-content: flex-end;
		flex-wrap: wrap;
		gap: .65rem;
		padding-top: 1rem;
		border-top: 1px solid rgba(64, 81, 137, .10);
	}

	#phuyu_operacion.phuyu-pedido-compra-form .modal-header {
		background: #405189;
		color: #fff;
		border-bottom: 0;
	}

	#phuyu_operacion.phuyu-pedido-compra-form .modal-content {
		border: 0;
		border-radius: .9rem;
		overflow: hidden;
	}

	#phuyu_operacion.phuyu-pedido-compra-form .phuyu-item-summary {
		display: flex;
		flex-wrap: wrap;
		gap: .5rem;
	}

	#phuyu_operacion.phuyu-pedido-compra-form .phuyu-item-summary span,
	#phuyu_operacion.phuyu-pedido-compra-form .phuyu-item-summary strong {
		display: inline-flex;
		align-items: center;
		border-radius: 999px;
		padding: .45rem .7rem;
		background: #f8fafc;
		font-size: .82rem;
	}

	#phuyu_operacion.phuyu-pedido-compra-form #reportes_modal {
		height: calc(100vh - 64px);
	}

	@media (max-width: 1199.98px) {
		#phuyu_operacion.phuyu-pedido-compra-form .phuyu-totals-grid {
			grid-template-columns: repeat(2, minmax(130px, 1fr));
		}
	}

	@media (max-width: 575.98px) {
		#phuyu_operacion.phuyu-pedido-compra-form .phuyu-form-header {
			align-items: flex-start;
		}

		#phuyu_operacion.phuyu-pedido-compra-form .phuyu-totals-grid {
			grid-template-columns: 1fr;
		}

		#phuyu_operacion.phuyu-pedido-compra-form .phuyu-actions .btn {
			width: 100%;
		}
	}
</style>

<script src="<?php echo base_url();?>phuyu/phuyu_pedidos/nuevo.js"></script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas_2.js"></script>

<script>
	var pantalla = jQuery(document).height();
	$("#reportes_modal").css({height: pantalla - 65});

	if ($.fn.datetimepicker) {
		$(".datepicker").datetimepicker({format: "YYYY-MM-DD", ignoreReadonly: true}).attr("readonly", "true");
	}
</script>
