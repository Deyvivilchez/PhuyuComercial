<div id="phuyu_operacion" class="phuyu-nota-form">
	<form id="formulario" v-on:submit.prevent="phuyu_guardar()">
		<div class="phuyu_body">
			<div class="card phuyu-nota-card">
				<div class="card-body">
					<div class="phuyu-form-header">
						<div class="phuyu-form-icon"><i class="bi bi-receipt-cutoff"></i></div>
						<div>
							<div class="text-muted small text-uppercase fw-semibold">Compras</div>
							<h5 class="mb-0 fw-bold">Nueva nota de credito</h5>
						</div>
					</div>

					<div class="phuyu-section mb-3">
						<div class="row g-3 align-items-end">
							<div class="col-md-3 col-12">
								<label class="form-label">Motivo de la nota</label>
								<select class="form-select" name="codmotivonota" v-model="campos.codmotivonota" v-on:change="phuyu_motivos()" required>
									<?php foreach ($motivos as $value) { ?>
										<option value="<?php echo $value["codmotivonota"]; ?>"><?php echo $value["descripcion"]; ?></option>
									<?php } ?>
								</select>
							</div>

							<div class="col-md-4 col-12">
								<label class="form-label">Seleccionar proveedor</label>
								<div class="phuyu-select-wrap">
									<select class="form-select ajax" name="codpersona" id="codpersona" required data-live-search="true" v-on:change="phuyu_infocliente()">
										<option value="2">PROVEEDORES VARIOS</option>
									</select>
								</div>
							</div>

							<div class="col-md-5 col-12">
								<label class="form-label">Descripcion de la nota de credito</label>
								<input class="form-control" name="descripcion" v-model.trim="campos.descripcion" required autocomplete="off">
							</div>

							<div class="col-md-2 col-6">
								<label class="form-label">Fecha nota</label>
								<input type="date" class="form-control" id="fechacomprobante" value="<?php echo date('Y-m-d');?>" autocomplete="off">
							</div>

							<div class="col-md-2 col-6">
								<label class="form-label">Fecha ref.</label>
								<input type="date" class="form-control" id="fechacomprobante_ref" value="<?php echo date('Y-m-d');?>" autocomplete="off">
							</div>

							<div class="col-md-2 col-6">
								<label class="form-label">Serie nota</label>
								<input type="text" class="form-control text-uppercase" v-model.trim="campos.seriecomprobante" maxlength="4" autocomplete="off" required>
							</div>

							<div class="col-md-2 col-6">
								<label class="form-label">Nro nota</label>
								<input type="text" class="form-control" v-model.trim="campos.nrocomprobante" maxlength="8" autocomplete="off" required>
							</div>

							<div class="col-md-2 col-12 d-flex align-items-end">
								<button type="button" class="btn btn-primary w-100" v-on:click="phuyu_comprobantes()">
									<i class="bi bi-search me-1"></i> Buscar
								</button>
							</div>
						</div>
					</div>

					<div class="row g-3 mb-3">
						<div class="col-12">
							<div class="phuyu-section-title">
								<i class="bi bi-file-earmark-text"></i>
								<span>Comprobantes de referencia</span>
							</div>
							<div class="table-responsive phuyu-table-wrap phuyu-comprobantes-wrap">
								<table class="table table-hover align-middle mb-0">
									<thead>
										<tr>
											<th>Razon social</th>
											<th>Comprobante</th>
											<th>Fecha</th>
											<th class="text-end">Importe</th>
											<th class="text-center">Seleccionar</th>
										</tr>
									</thead>
									<tbody>
										<tr v-for="dato in comprobantes" style="cursor:pointer;" v-bind:id="dato.codkardex">
											<td class="fw-semibold">{{dato.cliente}}</td>
											<td>{{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
											<td>{{dato.fechacomprobante}}</td>
											<td class="text-end fw-semibold">
												<span v-if="dato.codmoneda==1">S/</span>
												<span v-if="dato.codmoneda!=1">$</span>
												{{dato.importe}}
											</td>
											<td class="text-center" v-if="dato.codmotivonota!=0">
												<span class="badge bg-danger-subtle text-danger">Con nota</span>
											</td>
											<td class="text-center" v-if="dato.codmotivonota==0">
												<button type="button" class="btn btn-success btn-sm" v-on:click="phuyu_detalle(dato)">
													<i class="bi bi-check2 me-1"></i> Seleccionar
												</button>
											</td>
										</tr>
										<tr v-if="comprobantes.length==0">
											<td colspan="5" class="text-center text-muted py-3">
												<i class="bi bi-inbox me-1"></i> Sin comprobantes cargados
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>

					<div class="phuyu-section-title">
						<i class="bi bi-list-check"></i>
						<span>Detalle de la nota</span>
					</div>
					<div class="table-responsive phuyu-table-wrap">
						<table class="table table-striped align-middle mb-0">
							<thead>
								<tr>
									<th style="min-width:240px;">Producto</th>
									<th>Unidad</th>
									<th style="min-width:120px;">Cantidad</th>
									<th style="min-width:120px;">P. Unitario</th>
									<th class="text-end">I.G.V.</th>
									<th class="text-end">Subtotal</th>
									<th class="text-center" style="width:56px;"><i class="bi bi-trash3"></i></th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="(dato,index) in detalle">
									<td>{{dato.producto}}</td>
									<td>{{dato.unidad}}</td>
									<td>
										<input type="number" step="0.0001" class="form-control number" v-model.number="dato.cantidad" v-on:input="phuyu_calcular(dato)" min="0.0001" required>
									</td>
									<td>
										<input type="number" step="0.0001" class="form-control number" v-model.number="dato.precio" v-on:input="phuyu_calcular(dato)" min="0.0001" required>
									</td>
									<td class="text-end">{{dato.igv}}</td>
									<td class="text-end fw-semibold">{{dato.subtotal}}</td>
									<td class="text-center">
										<button type="button" class="btn btn-danger btn-sm" v-on:click="phuyu_quitardetalle(index,dato)">
											<i class="bi bi-trash3"></i>
										</button>
									</td>
								</tr>
								<tr v-if="detalle.length==0">
									<td colspan="7" class="text-center text-muted py-3">
										<i class="bi bi-inbox me-1"></i> Seleccione un comprobante para cargar el detalle
									</td>
								</tr>
							</tbody>
							<tfoot>
								<tr>
									<td colspan="5" class="text-end fw-bold">Total</td>
									<td class="text-end text-danger fw-bold">S/ {{totales.importe}}</td>
									<td></td>
								</tr>
							</tfoot>
						</table>
					</div>

					<div class="phuyu-form-actions">
						<button type="submit" class="btn btn-primary" v-bind:disabled="estado==1">
							<i class="bi bi-save me-1"></i> Guardar nota
						</button>
						<button type="button" class="btn btn-light" v-on:click="phuyu_cerrar()">
							<i class="bi bi-arrow-left me-1"></i> Atras
						</button>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>

<style>
	#phuyu_operacion.phuyu-nota-form .phuyu-nota-card {
		border: 1px solid rgba(64, 81, 137, .10);
		border-radius: .9rem;
		box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
	}

	#phuyu_operacion.phuyu-nota-form .phuyu-form-header {
		display: flex;
		align-items: center;
		gap: .75rem;
		padding-bottom: 1rem;
		margin-bottom: 1rem;
		border-bottom: 1px solid rgba(64, 81, 137, .10);
	}

	#phuyu_operacion.phuyu-nota-form .phuyu-form-icon {
		width: 44px;
		height: 44px;
		border-radius: 12px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: rgba(64, 81, 137, .10);
		color: #405189;
		font-size: 1.25rem;
	}

	#phuyu_operacion.phuyu-nota-form .form-label {
		font-size: .74rem;
		font-weight: 800;
		text-transform: uppercase;
		letter-spacing: .03em;
		color: #495057;
		margin-bottom: .4rem;
	}

	#phuyu_operacion.phuyu-nota-form .form-control,
	#phuyu_operacion.phuyu-nota-form .form-select,
	#phuyu_operacion.phuyu-nota-form .select2-container .select2-selection {
		border-color: rgba(64, 81, 137, .16);
		min-height: 40px;
	}

	#phuyu_operacion.phuyu-nota-form .select2-container {
		width: 100% !important;
	}

	#phuyu_operacion.phuyu-nota-form .phuyu-select-wrap .select2-selection--single {
		display: flex !important;
		align-items: center !important;
		border-radius: .375rem !important;
		height: 40px !important;
	}

	#phuyu_operacion.phuyu-nota-form .phuyu-select-wrap .select2-selection__rendered {
		line-height: 40px !important;
		padding-left: .75rem !important;
	}

	#phuyu_operacion.phuyu-nota-form .phuyu-section {
		padding: 1rem;
		border: 1px solid rgba(64, 81, 137, .10);
		border-radius: .75rem;
		background: #f8fafc;
	}

	#phuyu_operacion.phuyu-nota-form .phuyu-section-title {
		display: flex;
		align-items: center;
		gap: .55rem;
		margin-bottom: .75rem;
		font-weight: 700;
		color: #405189;
	}

	#phuyu_operacion.phuyu-nota-form .phuyu-section-title i {
		width: 32px;
		height: 32px;
		border-radius: 10px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: rgba(64, 81, 137, .10);
	}

	#phuyu_operacion.phuyu-nota-form .phuyu-table-wrap {
		border: 1px solid rgba(64, 81, 137, .10);
		border-radius: .75rem;
		overflow: hidden;
	}

	#phuyu_operacion.phuyu-nota-form .phuyu-comprobantes-wrap {
		max-height: 220px;
		overflow-y: auto;
	}

	#phuyu_operacion.phuyu-nota-form table thead th {
		background: #f8fafc;
		color: #495057;
		font-size: .74rem;
		font-weight: 800;
		text-transform: uppercase;
		white-space: nowrap;
		border-bottom: 1px solid rgba(64, 81, 137, .12);
	}

	#phuyu_operacion.phuyu-nota-form table tbody td {
		font-size: .86rem;
		vertical-align: middle;
	}

	#phuyu_operacion.phuyu-nota-form .phuyu-form-actions {
		display: flex;
		justify-content: flex-end;
		gap: .65rem;
		padding-top: 1rem;
		margin-top: 1rem;
		border-top: 1px solid rgba(64, 81, 137, .10);
	}

	@media (max-width: 575.98px) {
		#phuyu_operacion.phuyu-nota-form .phuyu-form-actions {
			flex-direction: column;
		}

		#phuyu_operacion.phuyu-nota-form .phuyu-form-actions .btn {
			width: 100%;
		}
	}
</style>

<script src="<?php echo base_url();?>phuyu/phuyu_notas/notacompra.js"></script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas_2.js"></script>

<script>
	var pantalla = jQuery(document).height(); $("#reportes_modal").css({height: pantalla - 65});
</script>
