<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<style>
	#phuyu_datos .phuyu-sunat-panel {
		background: #fff;
		border: 1px solid rgba(64, 81, 137, .12);
		border-radius: 14px;
		box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
		margin-bottom: 16px;
		overflow: hidden;
	}

	#phuyu_datos .phuyu-sunat-panel-head {
		align-items: center;
		background: linear-gradient(135deg, #f8f9fc 0%, #eef3ff 100%);
		border-bottom: 1px solid rgba(64, 81, 137, .1);
		display: flex;
		gap: 12px;
		padding: 16px 18px;
	}

	#phuyu_datos .phuyu-sunat-panel-icon {
		align-items: center;
		background: #405189;
		border-radius: 12px;
		color: #fff;
		display: inline-flex;
		flex: 0 0 42px;
		font-size: 20px;
		height: 42px;
		justify-content: center;
		width: 42px;
	}

	#phuyu_datos .phuyu-sunat-panel-title {
		color: #111827;
		font-size: 16px;
		font-weight: 900;
		line-height: 1.15;
		margin: 0;
	}

	#phuyu_datos .phuyu-sunat-panel-subtitle {
		color: #64748b;
		font-size: 12px;
		font-weight: 600;
		margin: 4px 0 0;
	}

	#phuyu_datos .phuyu-sunat-panel-body {
		padding: 18px;
	}

	#phuyu_datos .phuyu-sunat-result {
		background: #f8fafc;
		border: 1px solid rgba(64, 81, 137, .1);
		border-radius: 10px;
		color: #475569;
		font-size: 12px;
		font-weight: 700;
		line-height: 1.5;
		margin-top: 14px;
		padding: 10px 12px;
	}

	#phuyu_datos .phuyu-sunat-result strong {
		color: #405189;
	}

	#phuyu_datos .phuyu-sunat-note {
		background: #fff8e6;
		border: 1px solid #ffe7a3;
		border-radius: 10px;
		color: #8a5a00;
		font-size: 12px;
		font-weight: 700;
		line-height: 1.45;
		margin-top: 12px;
		padding: 10px 12px;
	}

	#phuyu_datos .phuyu-sunat-actions .btn {
		align-items: center;
		border-radius: 9px;
		display: inline-flex;
		font-weight: 800;
		gap: 6px;
		justify-content: center;
		min-height: 39px;
		width: 100%;
	}

	#phuyu_datos .phuyu-sunat-table {
		border: 1px solid rgba(64, 81, 137, .12);
		border-radius: 12px;
		margin-top: 16px;
		overflow: auto;
	}

	#phuyu_datos .phuyu-sunat-table table {
		margin-bottom: 0;
	}

	#phuyu_datos .phuyu-sunat-table thead th {
		background: #f3f6f9;
		font-size: 11px;
		font-weight: 900;
		text-transform: uppercase;
		white-space: nowrap;
	}

	#phuyu_infosunat .modal-dialog {
		max-width: 1120px;
	}

	#phuyu_infosunat .modal-content {
		border: 0;
		border-radius: 14px;
		overflow: hidden;
	}

	#phuyu_infosunat .modal-header {
		background: #f8fafc;
		border-bottom: 1px solid #e5e7eb;
		padding: 14px 18px;
	}

	#phuyu_infosunat .modal-body {
		max-height: 68vh;
		overflow: auto;
		padding: 18px;
	}

	#phuyu_infosunat table {
		table-layout: fixed;
	}

	#phuyu_infosunat thead th {
		background: #f3f6f9;
		color: #1f2937;
		position: sticky;
		top: 0;
		z-index: 2;
	}

	#phuyu_infosunat td,
	#phuyu_infosunat th {
		vertical-align: middle;
		white-space: normal;
		word-break: break-word;
	}

	#phuyu_infosunat .phuyu-sunat-status {
		border-radius: 999px;
		display: inline-block;
		font-size: 11px;
		font-weight: 800;
		line-height: 1.35;
		padding: 6px 9px;
		white-space: normal;
	}

	#phuyu_infosunat .phuyu-sunat-detail {
		color: #64748b;
		display: block;
		font-size: 11px;
		font-weight: 700;
		line-height: 1.35;
		margin-top: 5px;
	}
</style>

<div id="phuyu_datos" class="phuyu-velzon-list phuyu-cpe-velzon">
	<div class="phuyu-page-title">
		<div class="phuyu-page-icon"><i class="bi bi-file-earmark-arrow-up"></i></div>
		<div>
			<div class="text-muted small text-uppercase fw-semibold">CPE</div>
			<h4 class="mb-0 fw-bold">Envios SUNAT</h4>
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb mb-0 mt-1">
					<li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
					<li class="breadcrumb-item active" aria-current="page">Facturacion electronica</li>
				</ol>
			</nav>
		</div>
	</div>

	<div class="phuyu_body">
		<div class="card phuyu-card mb-3">
			<div class="card-body">
				<div class="row g-3 align-items-end">
					<div class="col-12 col-md-3 col-xl-2">
						<label class="form-label"><i class="bi bi-calendar-date me-1"></i> Fecha resumen</label>
						<input type="date" class="form-control" id="fecha" value="<?php echo date('Y-m-d');?>">
					</div>
					<div class="col-12 col-md-4 col-xl-3">
						<a href="https://e-menu.sunat.gob.pe/cl-ti-itmenu/MenuInternet.htm" class="btn btn-warning btn-block" target="_blank">
							<i class="bi bi-box-arrow-up-right"></i> Portal de SUNAT
						</a>
					</div>
					<div class="col-12 col-md-5 col-xl-3">
						<button type="button" class="btn btn-success btn-block" v-on:click="phuyu_consultas()">
							<i class="bi bi-search"></i> Ver resumen CPE
						</button>
					</div>
				</div>
			</div>
		</div>
		<section class="scroll-section" id="responsiveTabs">
            <div class="card mb-3">
                <div class="card-header border-0 pb-0">
                    <ul class="nav nav-tabs nav-tabs-line card-header-tabs responsive-tabs" role="tablist">
                    	<li class="nav-item" role="presentation">
                          <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#facturas" role="tab" type="button" aria-selected="true">
                            Facturas Electrónicas
                          </button>
                        </li>
                        <li class="nav-item" role="presentation">
                          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#boletas" role="tab" type="button" aria-selected="false">Boletas Electrónicas</button>
                        </li>
                        <li class="nav-item" role="presentation">
                          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#guias" role="tab" type="button" aria-selected="false">Guías Electrónicas</button>
                        </li>
                        <li class="nav-item" role="presentation">
                          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#notas" role="tab" type="button" aria-selected="false">Notas de Créditos</button>
                        </li>
                        <li class="nav-item" role="presentation">
                          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#sunat" role="tab" type="button" aria-selected="false">Consultas Sunat</button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                    	<div class="tab-pane fade active show" id="facturas" role="tabpanel">
                    		<h5 class="text-success"><b>FACTURAS ACTIVAS</b></h5>
                    		<div class="table-responsive mb-4">
								<table class="table table-bordered" style="font-size: 11px">
									<thead>
										<tr>
											<th width="100px"><i class="fa fa-send"></i> ENVIAR</th>
											<th>DOCUMENTO</th>
											<th>RAZON SOCIAL</th>
											<th>COMPROBANTE</th>
											<th>FECHA</th>
											<th>IMPORTE</th>
											<th width="10px">ESTADO</th>
											<th width="50px">XML</th>
										</tr>
									</thead>
									<tbody>
										<tr v-for="dato in facturas">
											<td style="padding-top:5px;"> 
												<button type="button" class="btn btn-success btn-sm" v-bind:id="dato.codkardex" v-on:click="comprobantes_enviar(dato.codkardex,'01')">
													<i class="fa fa-send"></i> ENVIAR
												</button> 
											</td>
											<td> {{dato.documento}} </td>
											<td> {{dato.cliente}} </td>
											<td> {{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
											<td> {{dato.fechacomprobante}} </td>
											<td> S/. {{dato.importe}} </td>
											<td>
												<b v-if="dato.estado==0" style="color:#d43f3a">PENDIENTE</b>
												<b v-if="dato.estado==2" style="color:#eea236">CON EXCEPCIONES</b> 
												<b v-if="dato.estado==3" style="color:#d43f3a">RECHAZADO</b> 
												<b v-if="dato.estado==4" style="color:#eea236">OBSERVADO</b> 
											</td>
											<td style="padding-top:5px;"> 
												<button type="button" class="btn btn-success btn-sm" v-on:click="comprobantes_xml(dato.codkardex,'01')">
													<i class="fa fa-cloud-download"></i> XML
												</button>
											</td>
										</tr>
									</tbody>
								</table>
							</div>

							<div class="row form-group">
								<div class="col-md-8 col-xs-12">
									<h5 class="text-danger"><b>RESUMEN DE FACTURAS ANULADAS</b></h5>
								</div>
								<div class="col-md-4 col-xs-12" align="right">
									<button type="button" class="btn btn-danger btn-sm btn-block" v-on:click="resumenes_generar(1)">
										<i class="fa fa-file-o"></i> GENERAR RESUMEN FACTURAS ANULADAS
									</button>
								</div>
							</div>
							
							<div class="table-responsive">
								<table class="table table-bordered" style="font-size: 11px">
									<thead>
										<tr>
											<th width="100px"><i class="fa fa-send"></i> ENVIAR</th>
											<th>FECHA RESUMEN</th>
											<th>PERIODO</th>
											<th>NOMBRE XML</th>
											<th>ESTADO</th>
											<th width="50px">VER</th>
											<th width="10px"><i class="fa fa-trash-o"></i></th>
										</tr>
									</thead>
									<tbody>
										<tr v-for="dato in facturas_anuladas">
											<td style="padding-top:5px;"> 
												<button type="button" class="btn btn-success btn-sm" v-bind:id="dato.periodo" v-on:click="resumenes_enviar(dato.codresumentipo,dato.periodo,dato.nrocorrelativo)">
													<i class="fa fa-send"></i> ENVIAR
												</button>
											</td>
											<td> {{dato.fecharesumen}} </td>
											<td> {{dato.periodo}} </td>
											<td> {{dato.nombre_xml}} </td>
											<td>
												<b v-if="dato.estado==0" style="color:#d43f3a">PENDIENTE</b>
												<b v-if="dato.estado==2" style="color:#eea236">CON EXCEPCIONES</b> 
												<b v-if="dato.estado==3" style="color:#d43f3a">RECHAZADO</b> 
												<b v-if="dato.estado==4" style="color:#eea236">OBSERVADO</b> 
											</td>
											<td> 
												<button type="button" class="btn btn-primary btn-sm" v-on:click="resumenes_ver(dato.codresumentipo,dato.periodo,dato.nrocorrelativo)"><i class="fa fa-file"></i> VER</button>
											</td>
											<td>
												<button type="button" class="btn btn-danger btn-sm" v-on:click="resumenes_anular(dato.codresumentipo,dato.periodo,dato.nrocorrelativo)">
													<i class="fa fa-trash-o"></i>
												</button>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
                    	</div>
                    	<div class="tab-pane fade" id="boletas" role="tabpanel">
                    		<div class="row form-group">
								<div class="col-md-4 col-xs-12">
									<h5 class="text-success"><b>RESUMEN DE BOLETAS</b></h5>
								</div>
								<div class="col-md-4 col-xs-12">
									<button type="button" class="btn btn-info btn-sm btn-block" v-on:click="resumenes_generar(3)">
										<i class="fa fa-file-o"></i> GENERAR RESUMEN DE BOLETAS
									</button>
								</div>
								<div class="col-md-4 col-xs-12">
								    <button type="button" class="btn btn-danger btn-sm btn-block" v-on:click="resumenes_generar(4)">
								       	<i class="fa fa-file-o"></i> GENERAR RESUMEN BOLETAS ANULADAS
								    </button>
								</div>
							</div>

							<div class="table-responsive">
								<table class="table table-bordered" style="font-size: 11px">
									<thead>
										<tr>
											<th width="100px"><i class="fa fa-send"></i> ENVIAR</th>
											<th width="120px">TIPO</th>
											<th>FECHA RESUMEN</th>
											<th>PERIODO</th>
											<th>NOMBRE XML</th>
											<th>ESTADO</th>
											<th width="50px">XML</th>
											<th width="50px">VER</th>
											<th width="10px"><i class="fa fa-trash-o"></i></th>
										</tr>
									</thead>
									<tbody>
										<tr v-for="dato in resumenes_boletas">
											<td style="padding-top:5px;"> 
												<button type="button" class="btn btn-success btn-sm" v-bind:id="dato.periodo" v-on:click="resumenes_enviar(dato.codresumentipo,dato.periodo,dato.nrocorrelativo)">
													<i class="fa fa-send"></i> ENVIAR
												</button>
											</td>
											<td>
												<span class="label label-danger" v-if="dato.codresumentipo==3">RES. BOLETAS</span>
												<span class="label label-danger" v-if="dato.codresumentipo==4">RES. BOLETAS ANULADAS</span>
											</td>
											<td> {{dato.fecharesumen}} </td>
											<td> {{dato.periodo}} </td>
											<td> {{dato.nombre_xml}} </td>
											<td>
												<b v-if="dato.estado==0" style="color:#d43f3a">PENDIENTE</b>
												<b v-if="dato.estado==2" style="color:#eea236">CON EXCEPCIONES</b> 
												<b v-if="dato.estado==3" style="color:#d43f3a">RECHAZADO</b> 
												<b v-if="dato.estado==4" style="color:#eea236">OBSERVADO</b> 
											</td>
											<td style="padding-top:5px;"> 
												<button type="button" class="btn btn-success btn-sm" v-on:click="resumenes_xml(dato.codresumentipo,dato.periodo,dato.nrocorrelativo)"> <i class="fa fa-cloud-download"></i> XML</button>
											</td>
											<td style="padding-top:5px;"> 
												<button type="button" class="btn btn-primary btn-sm" v-on:click="resumenes_ver(dato.codresumentipo,dato.periodo,dato.nrocorrelativo)"><i class="fa fa-file"></i> VER</button>
											</td>
											<td style="padding-top:5px;">
												<button type="button" class="btn btn-danger btn-sm" v-on:click="resumenes_anular(dato.codresumentipo,dato.periodo,dato.nrocorrelativo)">
													<b>X</b>
												</button>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
                    	</div>
                    	<div class="tab-pane fade" id="guias" role="tabpanel">
                    		<div class="table-responsive">
								<table class="table table-bordered" style="font-size: 11px">
									<thead>
										<tr>
											<th width="100px"><i class="fa fa-send"></i> ENVIAR</th>
											<th>DOCUMENTO</th>
											<th>DESTINATARIO</th>
											<th>COMPROBANTE</th>
											<th>FECHA</th>
											<th>MOTIVO</th>
											<th width="10px">ESTADO</th>
											<th width="50px">XML</th>
										</tr>
									</thead>
									<tbody>
										<tr v-for="dato in guias">
											<td style="padding-top:5px;"> 
												<button type="button" class="btn btn-success btn-sm" v-bind:id="dato.codguiar" v-on:click="guias_enviar(dato.codguiar,'09')">
													<i class="fa fa-send"></i> ENVIAR
												</button> 
											</td>
											<td> {{dato.documento}} </td>
											<td> {{dato.destinatario}} </td>
											<td> {{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
											<td> {{dato.fechaguia}} </td>
											<td> {{dato.motivo}} </td>
											<td>
												<b v-if="dato.estado==0" style="color:#d43f3a">PENDIENTE</b>
												<b v-if="dato.estado==2" style="color:#eea236">CON EXCEPCIONES</b> 
												<b v-if="dato.estado==3" style="color:#d43f3a">RECHAZADO</b> 
												<b v-if="dato.estado==4" style="color:#eea236">OBSERVADO</b> 
											</td>
											<td style="padding-top:5px;"> 
												<button type="button" class="btn btn-success btn-sm" v-on:click="guias_xml(dato.codguiar,'09')">
													<i class="fa fa-cloud-download"></i> XML
												</button>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
                    	</div>
                    	<div class="tab-pane fade" id="notas" role="tabpanel">
                    		<div class="table-responsive">
								<table class="table table-bordered" style="font-size: 11px">
									<thead>
										<tr>
											<th width="100px"><i class="fa fa-send"></i> ENVIAR</th>
											<th>DOCUMENTO</th>
											<th>RAZON SOCIAL</th>
											<th>COMPROBANTE</th>
											<th>FECHA</th>
											<th>IMPORTE</th>
											<th width="10px">ESTADO</th>
											<th width="50px">XML</th>
										</tr>
									</thead>
									<tbody>
										<tr v-for="dato in notas_creditos">
											<td style="padding-top:5px;"> 
												<button type="button" class="btn btn-success btn-sm" v-bind:id="dato.codkardex" v-on:click="comprobantes_enviar(dato.codkardex,'07')">
													<i class="fa fa-send"></i> ENVIAR
												</button> 
											</td>
											<td> {{dato.documento}} </td>
											<td> {{dato.cliente}} </td>
											<td> {{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
											<td> {{dato.fechacomprobante}} </td>
											<td> S/. {{dato.importe}} </td>
											<td>
												<b v-if="dato.estado==0" style="color:#d43f3a">PENDIENTE</b>
												<b v-if="dato.estado==2" style="color:#eea236">CON EXCEPCIONES</b> 
												<b v-if="dato.estado==3" style="color:#d43f3a">RECHAZADO</b> 
												<b v-if="dato.estado==4" style="color:#eea236">OBSERVADO</b> 
											</td>
											<td style="padding-top:5px;"> 
												<button type="button" class="btn btn-success btn-sm" v-on:click="comprobantes_xml(dato.codkardex,'01')">
													<i class="fa fa-cloud-download"></i> XML
												</button>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
                    	</div>
                    	<div class="tab-pane fade" id="sunat" role="tabpanel">
                    		<form class="phuyu-sunat-panel" v-on:submit.prevent="phuyu_consultasunat()">
								<div class="phuyu-sunat-panel-head">
									<span class="phuyu-sunat-panel-icon"><i class="bi bi-search"></i></span>
									<div>
										<h5 class="phuyu-sunat-panel-title">Consulta individual de CPE</h5>
										<p class="phuyu-sunat-panel-subtitle">Consulta directo en SUNAT con la validacion publica por tipo, serie, numero, fecha e importe.</p>
									</div>
								</div>
								<div class="phuyu-sunat-panel-body">
									<div class="row g-3 align-items-end">
										<div class="col-12 col-md-3">
											<label class="form-label">Tipo comprobante</label>
											<select id="sunat_tipo" v-model="sunat.tipo" class="form-select" required>
												<option value="01">Factura electronica</option>
												<option value="03">Boleta electronica</option>
												<option value="07">Nota de credito electronica</option>
												<option value="08">Nota de debito electronica</option>
											</select>
										</div>
										<div class="col-12 col-md-2">
											<label class="form-label">Serie</label>
											<input type="text" v-model.trim="sunat.serie" v-on:blur="phuyu_buscar_comprobante_sunat()" class="form-control" required autocomplete="off" minlength="4" maxlength="4" style="text-transform: uppercase;" />
										</div>
										<div class="col-12 col-md-2">
											<label class="form-label">Numero</label>
											<input type="text" v-model.trim="sunat.nrocomprobante" v-on:blur="phuyu_buscar_comprobante_sunat()" v-on:keyup.enter="phuyu_buscar_comprobante_sunat()" class="form-control" required autocomplete="off" maxlength="8" />
										</div>
										<div class="col-12 col-md-2">
											<label class="form-label">Fecha emision</label>
											<input type="date" id="sunat_fechaemision" v-model="sunat.fechaemision" class="form-control" />
										</div>
										<div class="col-12 col-md-1">
											<label class="form-label">Importe</label>
											<input type="number" id="sunat_importe" v-model="sunat.importe" class="form-control" step="0.01" min="0" />
										</div>
										<div class="col-12 col-md-2 phuyu-sunat-actions">
											<button type="submit" class="btn btn-success"><i class="bi bi-cloud-check"></i> Consultar este CPE</button>
										</div>
									</div>
									<div class="phuyu-sunat-result">
										<strong>Respuesta SUNAT:</strong> <span id="sunat_respuesta">SIN RESPUESTA</span>
									</div>
								</div>
							</form>

							<div class="phuyu-sunat-panel">
								<div class="phuyu-sunat-panel-head">
									<span class="phuyu-sunat-panel-icon"><i class="bi bi-calendar-range"></i></span>
									<div>
										<h5 class="phuyu-sunat-panel-title">Verificar comprobantes del periodo en SUNAT</h5>
										<p class="phuyu-sunat-panel-subtitle">Elige un rango, el sistema toma los comprobantes electronicos de la base de datos y los valida directamente en SUNAT al presionar consultar.</p>
									</div>
								</div>
								<div class="phuyu-sunat-panel-body">
									<div class="row g-3 align-items-end">
										<div class="col-12 col-md-3">
											<label class="form-label"><i class="bi bi-receipt me-1"></i> Tipo comprobante</label>
											<select id="sunat_tipo_periodo" class="form-select">
												<option value="todos">Todos</option>
												<option value="facturas">Facturas</option>
												<option value="boletas">Boletas</option>
												<option value="notas_credito">Notas de credito</option>
												<option value="notas_debito">Notas de debito</option>
											</select>
										</div>
										<div class="col-12 col-md-2">
											<label class="form-label"><i class="bi bi-calendar3 me-1"></i> Desde</label>
											<input type="date" class="form-control" id="fecha_desde" value="<?php echo date('Y-m-d');?>">
										</div>
										<div class="col-12 col-md-2">
											<label class="form-label"><i class="bi bi-calendar3 me-1"></i> Hasta</label>
											<input type="date" class="form-control" id="fecha_hasta" value="<?php echo date('Y-m-d');?>">
										</div>
										<div class="col-12 col-md-3 phuyu-sunat-actions">
											<button type="button" class="btn btn-warning" v-on:click="sunat_recepcion()">
												<i class="bi bi-arrow-repeat"></i> Consultar periodo en SUNAT
											</button>
										</div>
										<div class="col-12 col-md-2 phuyu-sunat-actions">
											<button type="button" class="btn btn-primary" v-on:click="sunat_quitar_icbper()">
												<i class="bi bi-tools"></i> Regularizar ICBPER
											</button>
										</div>
									</div>
									<div class="phuyu-sunat-note">
										No se consulta SUNAT al cambiar las fechas. Al presionar el boton, primero se buscan los comprobantes locales del rango y luego se valida cada uno en SUNAT usando tipo, serie, numero, fecha e importe. Se muestran 10 comprobantes por bloque.
									</div>
								</div>
							</div>

							<div class="table-responsive phuyu-sunat-table">
								<table class="table table-bordered" style="font-size: 11px">
									<thead >
					                    <tr>
					                        <th width="10px"> <i class="fa fa-align-center"></i> </th>
					                        <th width="230px"> <i class="fa fa-code"></i> TIPO COMPROBANTE</th>
					                        <th width="100px"> <i class="fa fa-calendar-o"></i> FECHA</th>
					                        <th> <i class="fa fa-file-o"></i> ARCHIVO COMPROBANTE XML</th>
					                        <th width="10px"> <i class="fa fa-undo"></i></th>
					                    </tr>
					                </thead>
					                <tbody>
					                	<!-- <tr v-for="dato in datos">
					                		<td>{{dato.codigo}}</td>
					                		<td v-if="dato.tipo==0">
					                			<label class="badge badge-teal" v-if="dato.tipocom=='01'">FACTURA ELECTRONICA</label>
					                            <label class="badge badge-warning" v-if="dato.tipocom=='09'">GUIA DE REMISION</label>
					                            <label class="badge badge-info" v-if="dato.tipocom=='20'">RETENCION ELECTRONICA</label>
					                		</td>
					                		<td v-else="dato.tipo!=0">
					                			<label class="badge badge-teal">{{dato.tipocom}}</label>
					                		</td>
					                		<td>{{dato.fecha}}</td>
					                		<td>ARCHIVO {{dato.archivo}}.xml</td>
					                        <td>
					                        	<button type="button" class="btn btn-warning btn-sm btn-table" v-on:click="cpe_actualizar(dato)">
					                                <i class="fa fa-undo"></i>
					                            </button>
					                        </td>
					                	</tr> -->
					                </tbody>
								</table>
							</div>
                    	</div>
                    </div>
                </div>
            </div>
        </section>
	</div>

	<div id="modal_resumenes" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header modal-phuyu-titulo">
					<h4 class="modal-title">Informacion del resumen</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>

				<div class="modal-body" style="height:350px;overflow-y:auto;">
					<table class="table table-bordered" style="font-size: 11px">
						<thead>
							<tr>
								<th>RAZON SOCIAL</th>
								<th>COMPROBANTE</th>
								<th>F.COMPROBANTE</th>
								<th>F.ANULADO</th>
								<th width="100px">MOTIVO</th>
								<th>TOTAL</th>
								<th width="5px"><i class="fa fa-trash-o"></i></th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in resumenes_info">
								<td>{{dato.cliente}}</td>
								<td>{{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
								<td>{{dato.fechacomprobante}}</td>
								<td>{{dato.fechaanulacion}}</td>
								<td>{{dato.motivobaja}}</td>
								<td>{{dato.importe}}</td>
								<td>
									<button type="button" class="btn btn-danger btn-xs" v-on:click="resumenes_eliminar_kardex(dato)">
										<b>X</b>
									</button>
								</td>
							</tr>
						</tbody>
					</table>
					<div class="text-center">
						<button type="button" class="btn btn-info" v-on:click="resumenes_siguiente_correlativo()">SIGUIENTE CORRELATIVO</button>
						<button type="button" class="btn btn-danger" v-on:click="resumenes_actualizar()">ACTUALIZAR RESUMEN</button>
						<button type="button" class="btn btn-warning" v-on:click="resumenes_quitar_ticket()">QUITAR TICKET</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="modal_consultas" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-xl modal-fullscreen-lg-down">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">
						<?php echo $_SESSION["phuyu_empresa"];?> - Consulta comprobantes
					</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>

				<div class="modal-body" id="consultas_modal">
					<div class="row">
						<div class="col-md-3">
							<div class="x_panel">
								<h5 class="text-center"> <b>CONSULTA COMPROBANTES ELECTRONICOS</b> </h5> <hr>
								<div class="row">
									<div class="col-md-6 col-xs-12">
										<label>FECHA DESDE</label>
										<input type="text" class="form-control datepicker" id="fdesde" value="<?php echo date('Y-m-d');?>">
									</div>
									<div class="col-md-6 col-xs-12">
										<label>FECHA HASTA</label>
										<input type="text" class="form-control datepicker" id="fhasta" value="<?php echo date('Y-m-d');?>">
									</div>
								</div> <br>
								<div class="row">
									<div class="col-md-6 col-xs-12">
										<button type="button" class="btn btn-success btn-sm btn-block" v-on:click="phuyu_reportes_cpe('reporte_facturas_enviados','comprobantes')"><i class="fa fa-send"></i> F.E ENVIADAS</button>
									</div>
									<div class="col-md-6 col-xs-12">
										<button type="button" class="btn btn-danger btn-sm btn-block" v-on:click="phuyu_reportes_cpe('reporte_facturas_anulados','resumenes')"><i class="fa fa-trash-o"></i> F.E ANULADAS</button>
									</div>
								</div>
								<div class="row">
									<div class="col-md-6 col-xs-12">
										<button type="button" class="btn btn-success btn-sm btn-block" v-on:click="phuyu_reportes_cpe('reporte_boletas_enviados','resumenes')"><i class="fa fa-send"></i> B.E ENVIADAS</button> 
									</div>
									<div class="col-md-6 col-xs-12">
										<button type="button" class="btn btn-danger btn-sm btn-block" v-on:click="phuyu_reportes_cpe('reporte_boletas_anulados','resumenes')"><i class="fa fa-trash-o"></i> B.E ANULADAS</button>
									</div>
								</div>
							</div>

							<div class="x_panel">
								<h5 class="text-center"> <b>FACTURAS ELECTRONICAS</b> </h5> <hr>

								<ul class="list-inline">
									<li>
										<p>
											<span class="icon"><i class="fa fa-square green"></i></span> 
											<b class="name">ACTIVAS: {{facturas_datos.enviados}}</b> 
										</p>
									</li>
									<li>
										<p>
											<span class="icon"><i class="fa fa-square red"></i></span> 
											<b class="name">ANULADAS: {{facturas_datos.anulados}}</b>
										</p>
									</li>
									<li>
										<p>
											<span class="icon"><i class="fa fa-square blue"></i></span> 
											<b class="name">TOTAL FACTURAS PENDIENTES: {{facturas_datos.pendientes}}</b>
										</p>
									</li>
								</ul>
							</div>
							<div class="x_panel">
								<h5 class="text-center"> <b>BOLETAS ELECTRONICAS</b> </h5> <hr>

								<ul class="list-inline">
									<li>
										<p>
											<span class="icon"><i class="fa fa-square green"></i></span> 
											<b class="name">ACTIVAS: {{boletas_datos.enviados}}</b>
										</p>
									</li>
									<li>
										<p>
											<span class="icon"><i class="fa fa-square red"></i></span>
											<b class="name">ANULADAS: {{boletas_datos.anulados}}</b>
										</p>
									</li>
									<li>
										<p>
											<span class="icon"><i class="fa fa-square blue"></i></span> 
											<b class="name">TOTAL BOLETAS PENDIENTES: {{boletas_datos.pendientes}}</b>
										</p>
									</li>
								</ul>
							</div>
						</div>

						<div class="col-md-9 table-responsive panel_comprobantes" style="height:350px; overflow-y: auto;">
							<table class="table table-bordered table-condensed" v-if="tipo_reporte=='comprobantes'">
								<thead>
									<tr>
										<th colspan="9" class="text-center">LISTA DE FACTURAS ELECTRONICAS</th>
									</tr>
									<tr>
										<th width="5px;">XML</b></th>
										<th width="5px;">CDR</b></th>
										<th width="5px;">CORREO</b></th>
										<th width="10px;">DOCUMENTO</th>
										<th>RAZON SOCIAL</th>
										<th>COMPROBANTE</th>
										<th width="80px;">FECHA</th>
										<th width="10px;">IMPORTE</th>
										<th>SUNAT CDR</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="dato in comprobantes_lista">
										<td> 
											<button type="button" class="btn btn-success btn-xs" v-on:click="comprobantes_xml(dato.codkardex,'01')" style="margin:0px;"><i class="fa fa-cloud-download"></i> XML</button>
										</td>
										<td>
											<button type="button" class="btn btn-danger btn-xs" v-on:click="comprobantes_cdr(dato.codkardex)" style="margin:0px;"><i class="fa fa-cloud"></i> CDR</button>
										</td>
										<td>
											<button type="button" class="btn btn-warning btn-xs" v-on:click="comprobantes_correo(dato)" style="margin:0px;"><i class="fa fa-send"></i> CORREO</button>
										</td>
										<td>{{dato.documento}}</td>
										<td>{{dato.razonsocial}}</td>
										<td>{{dato.seriecomprobante}}-{{dato.nrocomprobante}}</td>
										<td>{{dato.fechacomprobante}}</td>
										<td>{{dato.importe}}</td>
										<td style="font-size:9px;">{{dato.sunat}}</td>
									</tr>
								</tbody>
							</table>

							<div v-if="tipo_reporte=='resumenes'">
								<table class="table table-bordered table-condensed">
									<tr>
										<th colspan="9" class="text-center">LISTA DE RESUMENES ELECTRONICOS</th>
									</tr>
								</table>
								<div v-for="dato in resumenes_lista">
									<table class="table table-bordered table-condensed">
										<thead>
											<tr>
												<th width="5px;">XML</b></th>
												<th width="5px;">CDR</b></th>
												<th>NOMBRE XML</th>
												<th width="10px;">PERIODO</th>
												<th width="10px;">F.&nbsp;RESUMEN</th>
												<th colspan="2">SUNAT CDR</th>
											</tr>
										</thead>
										<tbody>
											<tr>
												<td> 
													<button type="button" class="btn btn-success btn-xs" v-on:click="resumenes_xml(dato.codresumentipo,dato.periodo,dato.nrocorrelativo)" style="margin:0px;"><i class="fa fa-cloud-download"></i> XML</button>
												</td>
												<td>
													<button type="button" class="btn btn-danger btn-xs" v-on:click="resumenes_cdr(dato.codresumentipo,dato.periodo,dato.nrocorrelativo)" style="margin:0px;"><i class="fa fa-cloud"></i> CDR</button>
												</td>
												<td><b>{{dato.nombre_xml}}</b></td>
												<td><b>{{dato.periodo}}</b></td>
												<td><b>{{dato.fecharesumen}}</b></td>
												<td colspan="2"><b>{{dato.descripcion_cdr}}</b></td>
											</tr>

											<tr>
												<td colspan="2"><b>COMPROBANTE</b></td>
												<td><b>MOTIVO BAJA</b></td>
												<td><b>IMPORTE</b></td>
												<td><b>DOCUMENTO</b></td>
												<td><b>RAZON SOCIAL</b></td>
												<th width="5px;">CDR</b></th>
											</tr>
											<tr v-for="d in dato.lista">
												<td colspan="2">{{d.seriecomprobante}}-{{d.nrocomprobante}}</td>
												<td>{{d.motivo}}</td>
												<td>{{d.importe}}</td>
												<td>{{d.documento}}</td>
												<td>{{d.razonsocial}}</td>
												<td>
													<button type="button" class="btn btn-warning btn-xs" v-on:click="consulta_cdr(dato.ticket)" style="margin:0px;"><i class="fa fa-cloud"></i> CDR</button>
												</td>
											</tr>
										</tbody>
									</table>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>


	<div id="phuyu_infosunat" class="modal fade">
        <div class="modal-dialog modal-xl">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Informacion comprobantes de SUNAT</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>

				<div class="modal-body">
					<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
						<div class="text-muted fw-semibold">
							Consultando bloque {{sunatpaginacion.actual}} de {{sunatpaginacion.ultima}} 
							<span v-if="sunatpaginacion.total > 0">({{sunatpaginacion.desde}}-{{sunatpaginacion.hasta}} de {{sunatpaginacion.total}})</span>
						</div>
						<div class="btn-group">
							<button type="button" class="btn btn-light btn-sm" v-on:click="sunat_recepcion_pagina(sunatpaginacion.actual - 1)" v-bind:disabled="sunatpaginacion.actual <= 1">
								<i class="bi bi-chevron-left"></i> Consultar bloque anterior
							</button>
							<button type="button" class="btn btn-light btn-sm" v-on:click="sunat_recepcion_pagina(sunatpaginacion.actual + 1)" v-bind:disabled="sunatpaginacion.actual >= sunatpaginacion.ultima">
								Consultar siguiente bloque <i class="bi bi-chevron-right"></i>
							</button>
						</div>
					</div>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" style="font-size:12px;">
                            <thead>
                                <tr>
                                    <th class="font-11" style="width:68px;"> <i class="fa fa-align-center"></i></th>
                                    <th class="font-11" style="width:90px;"> <i class="fa fa-code"></i> DNI/RUC</th>
                                    <th class="font-11" style="width:160px;"> <i class="fa fa-user"></i> RAZON SOCIAL</th>
                                    <th class="font-11" style="width:90px;"> <i class="fa fa-calendar-o"></i> FECHA</th>
                                    <th class="font-11" style="width:150px;"> <i class="fa fa-dropbox"></i> TIPO</th>
                                    <th class="font-11" style="width:130px;">COMPROBANTE</th>
                                    <th class="font-11" style="width:80px;"> <i class="fa fa-dollar"></i> TOTAL</th>
                                    <th class="font-11" style="width:260px;"> <i class="fa fa-flag"></i> RESPUESTA SUNAT</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="dato in sunatrecepcion">
                                    <td>{{dato.codkardex}}</td>
                                    <td>{{dato.documento}}</td>
                                    <td>{{dato.cliente}}</td>
                                    <td>{{dato.fechacomprobante}}</td>
                                    <td>{{dato.tipocomprobante}}</td>
                                    <td>{{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
                                    <td>{{dato.importe}}</td>
                                    <td>
                                        <span class="badge phuyu-sunat-status"
											v-bind:class="{
												'bg-success': dato.nivel_sunat=='success',
												'bg-warning text-dark': dato.nivel_sunat=='warning',
												'bg-danger': dato.nivel_sunat=='danger',
												'bg-secondary': dato.nivel_sunat=='secondary'
											}">
											{{dato.mensaje_sunat || dato.descripcion || 'Sin respuesta'}}
										</span>
										<span class="phuyu-sunat-detail" v-if="dato.detalle_sunat">{{dato.detalle_sunat}}</span>
                                    </td>
                                </tr>
                                <tr v-if="sunatrecepcion.length==0">
                                    <td colspan="8" class="text-center text-muted py-4">No hay comprobantes para consultar en este bloque.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_facturacion/index.js"> </script>
<script>
	var pantalla = jQuery(document).height(); $("#consultas_modal").css({height: pantalla - 65}); 
	$(".panel_boletas").css({height: pantalla - 505}); $(".panel_comprobantes").css({height: pantalla - 75});
</script>
