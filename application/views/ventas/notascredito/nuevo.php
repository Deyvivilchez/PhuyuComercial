<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

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
	min-height: 38px;
	font-size: 12px;
}

#phuyu_operacion .btn {
	white-space: nowrap;
}

.phuyu-nota-card {
	border: 0;
	border-radius: 1rem;
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

.phuyu-title h4 {
	font-size: 1.25rem;
	font-weight: 800;
	letter-spacing: .2px;
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

.phuyu-block {
	border: 1px solid rgba(64, 81, 137, .12);
	border-radius: 1rem;
	padding: 1rem;
	background: #fff;
}

.phuyu-block-soft {
	background: #f8fafc;
}

.phuyu-label-cliente {
	display: inline-flex;
	align-items: center;
	gap: 5px;
	color: #343a40;
	font-weight: 800;
	letter-spacing: 0;
	font-size: 11px;
	text-transform: uppercase;
	margin-bottom: 6px;
}

.phuyu-label-cliente i {
	color: #405189;
	font-size: 13px;
}

.phuyu-cliente-select-box {
	position: relative;
	padding: .25rem;
	border-radius: .75rem;
	background: #f8fafc;
	border: 1px solid rgba(64, 81, 137, .10);
}

.phuyu-cliente-select-box select {
	width: 100%;
	min-height: 40px;
	border-radius: .375rem;
	border: 1px solid rgba(64, 81, 137, .16);
	background-color: #fff;
	font-weight: 600;
	font-size: 12px;
	color: #343a40;
}

.phuyu-cliente-select-box .select2-container {
	width: 100% !important;
}

.phuyu-cliente-select-box .select2-selection--single {
	height: 40px !important;
	border-radius: .375rem !important;
	border: 1px solid rgba(64, 81, 137, .16) !important;
	display: flex !important;
	align-items: center !important;
	background: #fff !important;
	box-shadow: none !important;
}

.phuyu-cliente-select-box .select2-selection__rendered {
	font-weight: 600 !important;
	font-size: 12px !important;
	color: #343a40 !important;
	line-height: 40px !important;
	padding-left: 12px !important;
}

.phuyu-cliente-select-box .select2-selection__arrow {
	height: 40px !important;
}

.phuyu-cliente-select-box .select2-container--default.select2-container--focus .select2-selection--single,
.phuyu-cliente-select-box .select2-container--default.select2-container--open .select2-selection--single {
	border-color: #405189 !important;
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

.phuyu-table-wrapper table {
	margin-bottom: 0;
	font-size: 11px;
}

.phuyu-table-wrapper thead th {
	background: #f3f6f9;
	font-weight: 800;
	text-transform: uppercase;
	white-space: nowrap;
	color: #343a40;
}

.phuyu-table-wrapper td {
	vertical-align: middle;
}

.phuyu-table-wrapper .form-control,
.phuyu-table-wrapper .form-select {
	min-height: 34px;
	font-size: 12px;
}

.phuyu-comprobantes-table {
	min-width: 980px;
}

.phuyu-detalle-table {
	min-width: 980px;
}

.phuyu-scroll-small {
	max-height: 220px;
	overflow-y: auto;
}

.phuyu-product-name {
	min-width: 260px;
	font-weight: 700;
}

.phuyu-btn-delete {
	width: 32px;
	height: 32px;
	padding: 0;
	display: inline-flex;
	align-items: center;
	justify-content: center;
}

.phuyu-total-row td {
	background: #fff !important;
	font-weight: 800;
}
</style>

<div id="phuyu_operacion" class="phuyu-velzon-form phuyu-ventas-velzon">
	<div class="phuyu-page-title">
		<span class="phuyu-page-icon"><i class="bi bi-arrow-counterclockwise"></i></span>
		<div>
			<div class="text-muted small text-uppercase fw-semibold">Ventas</div>
			<h4 class="mb-1">Nueva nota de crédito</h4>
			<p class="text-muted mb-0">Emisión de nota asociada a comprobantes y productos facturados.</p>
		</div>
	</div>

	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">

		<div class="phuyu_body">
			<div class="card phuyu-nota-card">
				<div class="card-body p-4">

					<div class="phuyu-title">
						<i class="bi bi-file-earmark-minus fs-5"></i>
						<h4 class="mb-0">REGISTRO NUEVO NOTA DE CRÉDITO</h4>
					</div>

					<div class="phuyu-block phuyu-block-soft mb-3">
						<div class="row g-3 align-items-end">

							<div class="col-lg-3 col-md-6">
								<label class="form-label">Motivo de la nota</label>
								<select class="form-select" name="codmotivonota" v-model="campos.codmotivonota" v-on:change="phuyu_motivos()" required>
									<?php foreach ($motivos as $key => $value) { ?>
										<option value="<?php echo $value["codmotivonota"];?>">
											<?php echo $value["descripcion"];?>
										</option>
									<?php } ?>
								</select>
							</div>

							<div class="col-lg-4 col-md-6">
								<label class="phuyu-label-cliente">
									<i class="bi bi-person-check"></i>
									Seleccionar cliente
								</label>

								<div class="phuyu-cliente-select-box">
									<select class="form-select ajax" name="codpersona" id="codpersona" required data-live-search="true" v-on:change="phuyu_infocliente()">
										<option value="2">CLIENTES VARIOS</option>
									</select>
								</div>
							</div>

							<div class="col-lg-5 col-md-12">
								<label class="form-label">Descripción de la nota de crédito</label>
								<input class="form-control" name="descripcion" v-model.trim="campos.descripcion" required autocomplete="off" placeholder="Ingrese descripción o sustento de la nota...">
							</div>

						</div>
					</div>

					<div class="phuyu-block mb-3">
						<div class="row g-3 align-items-end">

							<div class="col-lg-3 col-md-6">
								<label class="form-label">Tipo comprobante referencia</label>
								<select class="form-select" name="codcomprobantetipo_ref" v-model="campos.codcomprobantetipo_ref" v-on:change="phuyu_series()">
									<option value="0">SELECCIONE</option>
									<?php foreach ($tipocomprobantes as $key => $value) { ?>
										<option value="<?php echo $value["codcomprobantetipo"];?>">
											<?php echo $value["descripcion"];?>
										</option>
									<?php } ?>
								</select>
							</div>

							<div class="col-lg-3 col-md-6">
								<label class="form-label">Fecha comprobante ref.</label>
								<input type="date" class="form-control" id="fechacomprobante_ref" value="<?php echo date('Y-m-d');?>" autocomplete="off">
							</div>

							<div class="col-lg-2 col-md-6">
								<label class="form-label">Serie comprobante</label>
								<select class="form-select" name="seriecomprobante_ref" v-model="campos.seriecomprobante_ref" v-on:change="phuyu_comprobantes()">
									<option value="">SELECCIONE</option>
									<option v-for="dato in series_ref" v-bind:value="dato.seriecomprobante">{{dato.seriecomprobante}}</option>
								</select>
							</div>

							<div class="col-lg-2 col-md-6">
								<label class="form-label">Serie nota crédito</label>
								<select class="form-select" name="seriecomprobante" v-model="campos.seriecomprobante" required>
									<option value="">SELECCIONE</option>
									<option v-for="dato in series" v-bind:value="dato.seriecomprobante">{{dato.seriecomprobante}}</option>
								</select>
							</div>

							<div class="col-lg-2 col-md-12">
								<button type="button" class="btn btn-success phuyu-btn-text-icon w-100" v-on:click="phuyu_comprobantes()">
									<i class="bi bi-search"></i>
									<span>Buscar</span>
								</button>
							</div>

						</div>
					</div>

					<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
						<h6 class="mb-0 text-primary fw-bold">
							<i class="bi bi-receipt me-1"></i>
							Comprobantes encontrados
						</h6>
						<small class="text-muted">Selecciona un comprobante para cargar su detalle</small>
					</div>

					<div class="phuyu-table-wrapper phuyu-scroll-small mb-3">
						<table class="table table-sm table-striped table-hover align-middle phuyu-comprobantes-table">
							<thead>
								<tr>
									<th width="40%">Razón social</th>
									<th width="20%">Comprobante</th>
									<th width="10%">Fecha</th>
									<th width="10%">Importe</th>
									<th width="10%">Refer.</th>
									<th width="10%">Seleccionar</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="dato in comprobantes" style="cursor:pointer;" v-bind:id="dato.codkardex">
									<td class="fw-semibold text-dark">{{dato.cliente}}</td>
									<td>{{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
									<td>{{dato.fechacomprobante}}</td>
									<td><b class="text-success">S/. {{dato.importe}}</b></td>

									<td v-if="dato.cantidadnota!=0">
										<span class="badge bg-info-subtle text-info border border-info-subtle">{{dato.cantidadnota}} NOTAS</span>
									</td>
									<td v-if="dato.cantidadnota==0">
										<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">SIN NOTAS</span>
									</td>

									<td v-if="dato.procesoestadonota!=0">
										<button type="button" class="btn btn-danger btn-sm phuyu-btn-text-icon">
											<i class="bi bi-lock"></i>
											<span>Terminado</span>
										</button>
									</td>
									<td v-if="dato.procesoestadonota==0">
										<button type="button" class="btn btn-success btn-sm phuyu-btn-text-icon" v-on:click="phuyu_detalle(dato)">
											<i class="bi bi-check-circle"></i>
											<span>Seleccionar</span>
										</button>
									</td>
								</tr>
							</tbody>
						</table>
					</div>

					<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
						<h6 class="mb-0 text-primary fw-bold">
							<i class="bi bi-cart-check me-1"></i>
							Detalle de productos
						</h6>
						<small class="text-muted">Productos que serán considerados en la nota</small>
					</div>

					<div class="phuyu-table-wrapper mb-3">
						<table class="table table-sm table-striped table-hover align-middle phuyu-detalle-table">
							<thead>
								<tr>
									<th width="40%">Producto</th>
									<th width="15%">Unidad</th>
									<th width="10%">Cantidad</th>
									<th width="10%">P. Unitario</th>
									<th width="10%">I.G.V.</th>
									<th width="15%">Subtotal</th>
									<th width="1%" class="text-center"><i class="bi bi-trash"></i></th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="(dato,index) in detalle">
									<td class="phuyu-product-name">{{dato.producto}}</td>
									<td>{{dato.unidad}}</td>
									<td>
										<input type="number" step="0.0001" class="form-control number" v-model.number="dato.cantidad" v-on:keyup="phuyu_calcular(dato,3)" min="0.0001" required>
									</td>
									<td>
										<input type="number" step="0.0001" class="form-control number" v-model.number="dato.precio" v-on:keyup="phuyu_calcular(dato,3)" min="0.0001" required>
									</td>
									<td>{{dato.igv}}</td>
									<td><b>S/. {{dato.subtotal}}</b></td>
									<td>
										<button type="button" class="btn btn-danger btn-sm phuyu-btn-delete" v-on:click="phuyu_quitardetalle(index,dato)">
											<i class="bi bi-x-lg"></i>
										</button>
									</td>
								</tr>
							</tbody>
							<tfoot>
								<tr class="phuyu-total-row">
									<td colspan="5" class="text-end">Importe Nota Crédito S/.</td>
									<td><b class="text-danger fs-6">{{totales.importe}}</b></td>
									<td></td>
								</tr>
							</tfoot>
						</table>
					</div>

					<div class="border-top pt-3 mt-3">
						<div class="d-flex justify-content-end flex-wrap gap-2">
							<button type="submit" class="btn btn-success phuyu-btn-text-icon" v-bind:disabled="estado==1">
								<i class="bi bi-save"></i>
								<span>Guardar nota</span>
							</button>

							<button type="button" class="btn btn-danger phuyu-btn-text-icon" v-on:click="phuyu_cerrar()">
								<i class="bi bi-arrow-left-circle"></i>
								<span>Atrás - Cancelar</span>
							</button>
						</div>
					</div>

				</div>
			</div>
		</div>
	</form>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_notas/nuevo.js"></script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas_2.js"></script>

<script>
	if (typeof Select2Controls !== 'undefined') {
		let select2Controls = new Select2Controls();
	}
</script>
