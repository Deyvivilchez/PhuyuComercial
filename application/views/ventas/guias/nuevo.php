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
	}

	#phuyu_operacion .phuyu-guia-card {
		border: 0;
		border-radius: 1rem;
		box-shadow: 0 10px 35px rgba(15, 23, 42, .06);
	}

	#phuyu_operacion .phuyu-title {
		display: flex;
		align-items: center;
		gap: .65rem;
		color: #f06548;
		font-weight: 800;
		margin-bottom: 1rem;
	}

	#phuyu_operacion .phuyu-title i {
		width: 40px;
		height: 40px;
		border-radius: 12px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: rgba(240, 101, 72, .12);
	}

	#phuyu_operacion .phuyu-section {
		border: 1px solid rgba(64, 81, 137, .12);
		border-radius: 1rem;
		padding: 1rem;
		background: #fff;
		margin-bottom: 1rem;
	}

	#phuyu_operacion .phuyu-section-soft {
		background: #f8fafc;
	}

	#phuyu_operacion .phuyu-section-title {
		display: flex;
		align-items: center;
		gap: .5rem;
		color: #405189;
		font-weight: 800;
		margin-bottom: .85rem;
	}

	#phuyu_operacion .phuyu-section-title i {
		width: 30px;
		height: 30px;
		border-radius: 10px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: rgba(64, 81, 137, .1);
	}

	#phuyu_operacion .phuyu-btn-icon-only {
		width: 40px;
		height: 38px;
		padding: 0;
		border-radius: 10px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
	}

	#phuyu_operacion .phuyu-btn-text-icon {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		gap: .45rem;
		font-weight: 700;
		border-radius: 10px;
	}

	#phuyu_operacion .phuyu-actions {
		display: flex;
		justify-content: flex-end;
		flex-wrap: wrap;
		gap: .5rem;
	}

	#phuyu_operacion .phuyu-table-wrapper {
		border: 1px solid #eef1f4;
		border-radius: 14px;
		overflow: auto;
	}

	#phuyu_operacion .phuyu-table-guia {
		font-size: 11px;
		min-width: 760px;
		margin-bottom: 0;
	}

	#phuyu_operacion .phuyu-table-comprobantes {
		min-width: 420px;
	}

	#phuyu_operacion .phuyu-table-guia thead th {
		background: #f3f6f9;
		font-weight: 800;
		text-transform: uppercase;
		white-space: nowrap;
		color: #343a40;
	}

	#phuyu_operacion .phuyu-table-guia td {
		vertical-align: middle;
	}

	#phuyu_operacion .phuyu-table-guia .form-control,
	#phuyu_operacion .phuyu-table-guia .form-select {
		min-height: 34px;
		font-size: 12px;
	}

	#phuyu_operacion .phuyu-product-name {
		min-width: 240px;
		font-weight: 700;
	}

	#phuyu_operacion .select2-container {
		width: 100% !important;
	}

	#phuyu_operacion .select2-container--default .select2-selection--single,
	#phuyu_operacion .select2-container--bootstrap4 .select2-selection--single {
		height: 38px !important;
		border-radius: 9px !important;
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

	@media (max-width: 768px) {
		#phuyu_operacion .phuyu-actions .btn {
			width: 100%;
		}
	}
</style>

<div id="phuyu_operacion" class="phuyu-velzon-form phuyu-ventas-velzon">
	<div class="phuyu-page-title">
		<span class="phuyu-page-icon"><i class="bi bi-truck"></i></span>
		<div>
			<div class="text-muted small text-uppercase fw-semibold">Ventas</div>
			<h4 class="mb-1">Nueva guía de remisión</h4>
			<p class="text-muted mb-0">Datos de traslado, destinatario, transportista y detalle de productos.</p>
		</div>
	</div>

	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" id="comprobante" value="<?php echo $comprobantes[0]['codcomprobantetipo'];?>">
		<input type="hidden" id="serie" value="<?php echo $sucursal[0]['seriecomprobante'];?>">
		<input type="hidden" id="formato" value="<?php echo $_SESSION['phuyu_formato'];?>">

		<div class="phuyu_body">
			<div class="card phuyu-guia-card">
				<div class="card-body p-3 p-lg-4">
					<div class="phuyu-title">
						<i class="bi bi-truck fs-5"></i>
						<h4 class="mb-0">Guía de remisión N° {{campos.nro}}</h4>
					</div>

					<div class="phuyu-section phuyu-section-soft">
						<div class="phuyu-section-title">
							<i class="bi bi-file-earmark-text"></i>
							Datos de emisión
						</div>

						<div class="row g-3">
							<div class="col-lg-2 col-md-4">
								<label class="form-label">Serie</label>
								<select class="form-select requeridogeneral" id="seriecomprobante" v-model="campos.seriecomprobante" v-on:change="phuyu_correlativo()">
									<option v-for="dato in series" v-bind:value="dato.seriecomprobante">
										{{dato.seriecomprobante}}
									</option>
								</select>
							</div>

							<div class="col-lg-2 col-md-4">
								<label class="form-label">Fecha emisión <span class="text-danger">*</span></label>
								<input type="date" class="form-control requeridogeneral" id="fechaguia" name="fechaguia" value="<?php echo date('Y-m-d');?>" v-on:change="validar_general()">
							</div>

							<div class="col-lg-2 col-md-4">
								<label class="form-label">Fecha traslado <span class="text-danger">*</span></label>
								<input type="date" class="form-control" id="fechatraslado" name="fechatraslado" value="<?php echo date('Y-m-d');?>" v-on:change="validar_general()">
							</div>

							<div class="col-lg-6 col-md-12">
								<label class="form-label">Destinatario <span class="text-danger">*</span></label>
								<select class="form-select" name="codpersona" id="codpersona" required>
									<option value="">Seleccione destinatario</option>
								</select>
							</div>
						</div>
					</div>

					<div class="phuyu-section">
						<div class="phuyu-section-title">
							<i class="bi bi-signpost-2"></i>
							Motivo y carga
						</div>

						<div class="row g-3">
							<div class="col-lg-3 col-md-6">
								<label class="form-label">Modalidad de traslado <span class="text-danger">*</span></label>
								<select class="form-select" name="modotraslado" v-model="campos.codmodalidadtraslado" id="modotraslado" required>
									<option value="">Seleccione</option>
									<?php foreach ($modalidades as $key => $value) { ?>
										<option value="<?php echo $value["codmodalidadtraslado"];?>" codigo="<?php echo $value["oficial"]?>">
											<?php echo $value["modalidadtraslado"];?>
										</option>
									<?php } ?>
								</select>
							</div>

							<div class="col-lg-3 col-md-6">
								<label class="form-label">Motivo de traslado <span class="text-danger">*</span></label>
								<select class="form-select" v-model="campos.codmotivotraslado" name="motivotraslado" id="motivotraslado" v-on:change="motivotraslado()" required>
									<option value="">Seleccione</option>
									<?php foreach ($motivos as $key => $value) { ?>
										<option value="<?php echo $value["codmotivotraslado"];?>" codigo="<?php echo $value["oficial"]?>">
											<?php echo $value["descripcion"];?>
										</option>
									<?php } ?>
								</select>
							</div>

							<div class="col-lg-6 col-md-12">
								<label class="form-label">Descripción del motivo de traslado</label>
								<input type="text" maxlength="120" class="form-control" v-model="campos.descripcionmotivo" name="descripcionmotivo" id="descripcionmotivo">
							</div>

							<div class="col-md-6 almacenes" style="display:none;">
								<label class="form-label">Almacén de partida <span class="text-danger">*</span></label>
								<select class="form-select" v-model="campos.almacenpartida" id="almacen_principal" name="almacen_principal">
									<?php foreach ($almacenes_1 as $key => $value) { ?>
										<option value="<?php echo $value["codalmacen"];?>">
											<?php echo $value["descripcion"];?>
										</option>
									<?php } ?>
								</select>
							</div>

							<div class="col-md-6 almacenes" style="display:none;">
								<label class="form-label">Almacén de llegada <span class="text-danger">*</span></label>
								<select class="form-select" v-model="campos.almacendestino" id="almacen_llegada" name="almacen_llegada">
									<?php foreach ($almacenes_2 as $key => $value) { ?>
										<option value="<?php echo $value["codalmacen"];?>">
											<?php echo $value["descripcion"];?>
										</option>
									<?php } ?>
								</select>
							</div>

							<div class="col-lg-2 col-md-4">
								<label class="form-label">Peso total <span class="text-danger">*</span></label>
								<input type="number" class="form-control" v-model="campos.peso" name="pesobultos" id="pesobultos" v-on:keyup="validar_general()" value="0" required>
							</div>

							<div class="col-lg-2 col-md-4">
								<label class="form-label">Total de bultos</label>
								<input type="number" class="form-control" name="totalbultos" v-model="campos.nropaquetes" id="totalbultos" required value="0">
							</div>

							<div class="col-lg-2 col-md-4">
								<label class="form-label">Nro contenedor <span class="text-danger">*</span></label>
								<input type="number" class="form-control requeridogeneral" v-model="campos.nrocontenedor" name="nrocontenedor" id="nrocontenedor" v-on:keyup="validar_general()" value="1">
							</div>

							<div class="col-lg-6 col-md-12">
								<label class="form-label">Observaciones</label>
								<input type="text" class="form-control" name="observacion" v-model="campos.observaciones" id="observacion" maxlength="150">
							</div>
						</div>
					</div>

					<div class="phuyu-section row_remitente" style="display:none;">
						<div class="phuyu-section-title">
							<i class="bi bi-person-lines-fill"></i>
							Remitente
						</div>

						<select class="form-select" name="codremitente" id="codremitente">
							<option value="">Seleccione remitente</option>
						</select>
					</div>

					<div class="phuyu-section">
						<div class="phuyu-section-title">
							<i class="bi bi-geo-alt"></i>
							Puntos de traslado
						</div>

						<div class="row g-3 align-items-end">
							<div class="col-lg-5 col-md-6">
								<label class="form-label">Ubigeo de partida <span class="text-danger">*</span></label>
								<input type="text" class="form-control" id="ubigeopartida" name="ubigeopartida" disabled>
								<input type="hidden" id="idubigeopartida" v-model="campos.codubigeopartida" name="idubigeopartida">
							</div>

							<div class="col-lg-1 col-md-2">
								<button type="button" class="btn btn-info text-white phuyu-btn-icon-only w-100" v-on:click="phuyu_bsubigeo()" title="Buscar ubigeo de partida" data-bs-toggle="tooltip">
									<i class="bi bi-search"></i>
								</button>
							</div>

							<div class="col-lg-6 col-md-12">
								<label class="form-label">Dirección de partida <span class="text-danger">*</span></label>
								<input type="text" class="form-control" v-model="campos.direccionpartida" id="puntopartida" name="puntopartida" maxlength="100" required>
							</div>

							<div class="col-lg-5 col-md-6">
								<label class="form-label">Ubigeo de llegada <span class="text-danger">*</span></label>
								<input type="text" class="form-control" id="ubigeollegada" name="ubigeollegada" disabled>
								<input type="hidden" id="idubigeollegada" v-model="campos.codubigeollegada" name="idubigeollegada">
							</div>

							<div class="col-lg-1 col-md-2">
								<button type="button" class="btn btn-info text-white phuyu-btn-icon-only w-100" v-on:click="phuyu_bsubigeollegada()" title="Buscar ubigeo de llegada" data-bs-toggle="tooltip">
									<i class="bi bi-search"></i>
								</button>
							</div>

							<div class="col-lg-6 col-md-12">
								<label class="form-label">Dirección de llegada <span class="text-danger">*</span></label>
								<input type="text" class="form-control" id="puntollegada" name="puntollegada" v-model="campos.direccionllegada" maxlength="100" required>
							</div>
						</div>
					</div>

					<div class="phuyu-section">
						<div class="phuyu-section-title">
							<i class="bi bi-truck-front"></i>
							Transporte
						</div>

						<div class="row g-3 align-items-end">
							<div class="col-lg-5 col-md-6">
								<label class="form-label">Transportista <span class="text-danger">*</span></label>
								<select class="form-select" name="codtransportista" id="codtransportista" required>
									<option value="">Seleccione transportista</option>
								</select>
							</div>

							<div class="col-lg-1 col-md-2">
								<button type="button" class="btn btn-success phuyu-btn-icon-only w-100" v-on:click="phuyu_addtransportista()" title="Agregar transportista" data-bs-toggle="tooltip">
									<i class="bi bi-person-plus"></i>
								</button>
							</div>

							<div class="col-lg-5 col-md-6">
								<label class="form-label">Conductor <span class="text-danger">*</span></label>
								<select class="form-select" name="codconductor" id="codconductor" required>
									<option value="">Seleccione conductor</option>
								</select>
							</div>

							<div class="col-lg-1 col-md-2">
								<button type="button" class="btn btn-success phuyu-btn-icon-only w-100" v-on:click="phuyu_addconductor()" title="Agregar conductor" data-bs-toggle="tooltip">
									<i class="bi bi-person-plus"></i>
								</button>
							</div>

							<div class="col-lg-5 col-md-6">
								<label class="form-label">Nro placa vehículo <span class="text-danger">*</span></label>
								<select class="form-select" name="codvehiculo" id="codvehiculo" required>
									<option value="">Seleccione vehículo</option>
								</select>
							</div>

							<div class="col-lg-1 col-md-2">
								<button type="button" class="btn btn-success phuyu-btn-icon-only w-100" v-on:click="phuyu_addvehiculo()" title="Agregar vehículo" data-bs-toggle="tooltip">
									<i class="bi bi-plus-circle"></i>
								</button>
							</div>

							<div class="col-lg-6 col-md-12">
								<label class="form-label">Constancia de inscripción</label>
								<input type="text" class="form-control" id="constancia" v-model.trim="campos.constancia" autocomplete="off" maxlength="100" placeholder="Constancia">
							</div>
						</div>
					</div>

					<div class="phuyu-section">
						<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
							<div class="phuyu-section-title mb-0">
								<i class="bi bi-box-seam"></i>
								Detalle de productos y comprobantes
							</div>

							<div class="d-flex flex-wrap gap-2">
								<button type="button" class="btn btn-success btn-sm phuyu-btn-text-icon btnproducto" v-on:click="phuyu_item()">
									<i class="bi bi-search"></i>
									<span>Buscar productos</span>
								</button>
								<button type="button" class="btn btn-primary btn-sm phuyu-btn-text-icon btnventa" v-on:click="phuyu_itemventa()">
									<i class="bi bi-receipt"></i>
									<span>Buscar ventas</span>
								</button>
								<button type="button" class="btn btn-secondary btn-sm phuyu-btn-text-icon btncompra" v-on:click="phuyu_itemcompra()">
									<i class="bi bi-bag-check"></i>
									<span>Buscar compras</span>
								</button>
							</div>
						</div>

						<div class="row g-3">
							<div class="col-lg-7">
								<div class="phuyu-table-wrapper">
									<table class="table table-sm table-hover table-striped align-middle phuyu-table-guia">
										<thead>
											<tr>
												<th>Producto</th>
												<th style="width:150px;">Unidad</th>
												<th style="width:120px;">Cantidad</th>
												<th style="width:120px;">Peso unit.</th>
												<th style="width:46px;" class="text-center">
													<i class="bi bi-trash"></i>
												</th>
											</tr>
										</thead>
										<tbody>
											<tr v-for="(dato,index) in detalle">
												<td class="phuyu-product-name">{{dato.producto}}</td>
												<td>
													<select class="form-select form-select-sm number unidad" v-model="dato.codunidad" id="codunidad">
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
													<input type="number" step="0.0001" class="form-control form-control-sm number" v-model.number="dato.cantidad" min="0.0001" required>
												</td>
												<td>
													<input type="number" step="0.0001" class="form-control form-control-sm number" v-model.number="dato.pesoitem" min="0" required>
												</td>
												<td class="text-center">
													<button type="button" class="btn btn-danger btn-sm phuyu-btn-icon-only" v-on:click="phuyu_deleteitem(index,dato)" title="Quitar producto">
														<i class="bi bi-x-lg"></i>
													</button>
												</td>
											</tr>

											<tr v-if="detalle.length === 0">
												<td colspan="5" class="text-center text-muted py-4">
													<i class="bi bi-inbox d-block mb-1" style="font-size:28px;"></i>
													Sin productos agregados
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>

							<div class="col-lg-5">
								<div class="phuyu-table-wrapper">
									<table class="table table-sm table-hover table-striped align-middle phuyu-table-guia phuyu-table-comprobantes">
										<thead>
											<tr>
												<th>Comprobante</th>
												<th style="width:46px;" class="text-center">
													<i class="bi bi-trash"></i>
												</th>
											</tr>
										</thead>
										<tbody>
											<tr v-for="(dato,index) in detallecomprobante">
												<td class="fw-semibold">{{dato.tipo}}: {{dato.seriecomprobante}}-{{dato.nrocomprobante}}</td>
												<td class="text-center">
													<button type="button" class="btn btn-danger btn-sm phuyu-btn-icon-only" v-on:click="phuyu_deleteitemcomprobante(index,dato)" title="Quitar comprobante">
														<i class="bi bi-x-lg"></i>
													</button>
												</td>
											</tr>

											<tr v-if="detallecomprobante.length === 0">
												<td colspan="2" class="text-center text-muted py-4">
													<i class="bi bi-file-earmark d-block mb-1" style="font-size:28px;"></i>
													Sin comprobantes vinculados
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>

					<div class="border-top pt-3 mt-3">
						<div class="phuyu-actions">
							<button type="button" class="btn btn-warning phuyu-btn-text-icon" v-on:click="phuyu_venta()">
								<i class="bi bi-plus-circle"></i>
								<span>Nueva guía</span>
							</button>

							<button type="button" class="btn btn-danger phuyu-btn-text-icon" v-on:click="phuyu_atras()">
								<i class="bi bi-arrow-left-circle"></i>
								<span>Cancelar guía</span>
							</button>

							<button type="submit" class="btn btn-primary phuyu-btn-text-icon" v-bind:disabled="estado==1">
								<i class="bi bi-save"></i>
								<span>Guardar guía</span>
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>

	<div class="modal fade" id="modal-vehiculo-guia" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<form class="modal-content border-0" v-on:submit.prevent="phuyu_guardarvehiculo()">
				<div class="modal-header">
					<h5 class="modal-title">
						<i class="bi bi-truck-front me-1"></i>
						Registrar vehículo
					</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>

				<div class="modal-body">
					<div class="row g-3">
						<div class="col-12">
							<label class="form-label">Descripción <span class="text-danger">*</span></label>
							<input type="text" class="form-control" v-model.trim="vehiculo.descripcion" autocomplete="off" maxlength="100" placeholder="Ej. Camión principal" required>
						</div>

						<div class="col-md-6">
							<label class="form-label">Nro placa <span class="text-danger">*</span></label>
							<input type="text" class="form-control text-uppercase" v-model.trim="vehiculo.nroplaca" autocomplete="off" maxlength="20" placeholder="Ej. ABC-123" required>
						</div>

						<div class="col-md-6">
							<label class="form-label">Constancia</label>
							<input type="text" class="form-control" v-model.trim="vehiculo.constancia" autocomplete="off" maxlength="100" placeholder="Constancia de inscripción">
						</div>
					</div>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-light" data-bs-dismiss="modal">
						<i class="bi bi-x-circle me-1"></i>
						Cerrar
					</button>
					<button type="submit" class="btn btn-primary" v-bind:disabled="estadoVehiculo==1">
						<i class="bi bi-save me-1"></i>
						Guardar y usar
					</button>
				</div>
			</form>
		</div>
	</div>

	<div class="modal fade" id="modal-ubigeo-partida" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content border-0">
				<div class="modal-header">
					<h5 class="modal-title">
						<i class="bi bi-geo-alt me-1"></i>
						Seleccionar ubigeo de partida
					</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>

				<div class="modal-body">
					<div class="row g-3">
						<div class="col-12">
							<label class="form-label">Departamento</label>
							<select class="form-select" id="dep_par" v-on:change="phuyu_prov_part('pro_par')">
								<?php foreach ($departamentopartida as $key => $value) { ?>
									<option value="<?php echo $value['ubidepartamento'];?>"><?php echo $value["departamento"];?></option>
								<?php } ?>
							</select>
						</div>

						<div class="col-12">
							<label class="form-label">Provincia</label>
							<select class="form-select" id="pro_par" v-on:change="phuyu_dist_part('dis_par')"></select>
						</div>

						<div class="col-12">
							<label class="form-label">Distrito</label>
							<select class="form-select" id="dis_par"></select>
						</div>
					</div>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-light" data-bs-dismiss="modal">
						<i class="bi bi-x-circle me-1"></i>
						Cerrar
					</button>
					<button type="button" class="btn btn-primary" id="aceptar_ubigeo_partida" v-on:click="aceptar_ubigeo_partida()">
						<i class="bi bi-check-circle me-1"></i>
						Aceptar
					</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade" id="modal-ubigeo-llegada" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content border-0">
				<div class="modal-header">
					<h5 class="modal-title">
						<i class="bi bi-pin-map me-1"></i>
						Seleccionar ubigeo de llegada
					</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>

				<div class="modal-body">
					<div class="row g-3">
						<div class="col-12">
							<label class="form-label">Departamento</label>
							<select class="form-select" id="dep_lle" v-on:change="phuyu_prov_lleg('pro_lle')">
								<?php foreach ($departamentollegada as $key => $value) { ?>
									<option value="<?php echo $value['ubidepartamento'];?>"><?php echo $value["departamento"];?></option>
								<?php } ?>
							</select>
						</div>

						<div class="col-12">
							<label class="form-label">Provincia</label>
							<select class="form-select" id="pro_lle" v-on:change="phuyu_dist_lleg('dis_lle')"></select>
						</div>

						<div class="col-12">
							<label class="form-label">Distrito</label>
							<select class="form-select" id="dis_lle"></select>
						</div>
					</div>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-light" data-bs-dismiss="modal">
						<i class="bi bi-x-circle me-1"></i>
						Cerrar
					</button>
					<button type="button" class="btn btn-primary" id="aceptar_ubigeo_llegada" v-on:click="aceptar_ubigeo_llegada()">
						<i class="bi bi-check-circle me-1"></i>
						Aceptar
					</button>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_guias/nuevo.js"></script>
<script src="<?php echo base_url();?>phuyu/phuyu_guias/selects.js"></script>

<script>
	var pantalla = jQuery(document).height();
	$("#reportes_modal").css({height: pantalla - 65});

	if (typeof bootstrap !== 'undefined') {
		document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
			bootstrap.Tooltip.getOrCreateInstance(el);
		});
	}
</script>
