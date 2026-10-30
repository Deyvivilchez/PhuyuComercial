<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<style>
	#phuyu_datos .phuyu-report-header {
		background: linear-gradient(135deg, #f8f9fc 0%, #eef3ff 58%, #f3fbf8 100%);
		border: 1px solid rgba(64, 81, 137, .12);
		border-radius: 14px;
		margin-bottom: 18px;
		overflow: hidden;
		padding: 18px;
		position: relative;
	}

	#phuyu_datos .phuyu-report-header:before {
		background: rgba(64, 81, 137, .06);
		border-radius: 999px;
		content: "";
		height: 150px;
		position: absolute;
		right: -58px;
		top: -78px;
		width: 150px;
	}

	#phuyu_datos .phuyu-report-heading {
		align-items: center;
		display: flex;
		gap: 13px;
		position: relative;
		z-index: 1;
	}

	#phuyu_datos .phuyu-report-icon {
		align-items: center;
		background: #405189;
		border-radius: 13px;
		box-shadow: 0 12px 24px rgba(64, 81, 137, .25);
		color: #fff;
		display: inline-flex;
		flex: 0 0 46px;
		font-size: 23px;
		height: 46px;
		justify-content: center;
		width: 46px;
	}

	#phuyu_datos .phuyu-report-eyebrow {
		color: #64748b;
		font-size: 11px;
		font-weight: 800;
		letter-spacing: .08em;
		line-height: 1;
		margin-bottom: 5px;
		text-transform: uppercase;
	}

	#phuyu_datos .phuyu-report-title {
		color: #111827;
		font-size: 21px;
		font-weight: 900;
		line-height: 1.15;
		margin: 0;
	}

	#phuyu_datos .phuyu-report-subtitle {
		color: #64748b;
		font-size: 12px;
		font-weight: 600;
		margin: 5px 0 0;
	}

	#phuyu_datos .phuyu-report-actions {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
		justify-content: flex-end;
		position: relative;
		z-index: 1;
	}

	#phuyu_datos .phuyu-report-actions .btn {
		align-items: center;
		border-radius: 9px;
		display: inline-flex;
		font-weight: 800;
		gap: 6px;
		min-height: 36px;
		padding-left: 12px;
		padding-right: 12px;
	}

	#phuyu_datos .phuyu-report-actions .btn i {
		font-size: 15px;
	}

	#phuyu_datos .phuyu-report-meta {
		align-items: center;
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
		margin-top: 14px;
		position: relative;
		z-index: 1;
	}

	#phuyu_datos .phuyu-report-pill {
		align-items: center;
		background: rgba(255, 255, 255, .74);
		border: 1px solid rgba(64, 81, 137, .11);
		border-radius: 999px;
		color: #475569;
		display: inline-flex;
		font-size: 12px;
		font-weight: 700;
		gap: 6px;
		padding: 6px 10px;
	}

	#phuyu_datos .phuyu-filter-band {
		background: #ffffff;
		border: 1px solid rgba(64, 81, 137, .12);
		border-radius: 12px;
		box-shadow: 0 10px 28px rgba(15, 23, 42, .05);
		margin-bottom: 16px;
		padding: 14px;
	}

	#phuyu_datos .phuyu-filter-title {
		align-items: center;
		color: #111827;
		display: flex;
		font-size: 13px;
		font-weight: 900;
		gap: 7px;
		margin-bottom: 12px;
		text-transform: uppercase;
	}

	#phuyu_datos .phuyu-filter-title i {
		color: #405189;
		font-size: 16px;
	}

	#phuyu_datos .phuyu-filter-label {
		color: #475569;
		font-size: 11px;
		font-weight: 900;
		letter-spacing: .03em;
		margin-bottom: 6px;
		text-transform: uppercase;
	}

	#phuyu_datos .phuyu-filter-band .form-control,
	#phuyu_datos .phuyu-filter-band .form-select {
		border-color: #dbe3ef;
		border-radius: 8px;
		font-size: 12px;
		min-height: 38px;
	}

	#phuyu_datos .phuyu-filter-actions {
		align-items: end;
		display: grid;
		grid-template-columns: 46px minmax(150px, 1fr);
		gap: 8px;
		height: 100%;
	}

	#phuyu_datos .phuyu-filter-actions .btn {
		align-items: center;
		border-radius: 8px;
		display: inline-flex;
		font-size: 12px;
		font-weight: 800;
		gap: 6px;
		justify-content: center;
		min-height: 38px;
	}

	#phuyu_datos .phuyu-results-title {
		align-items: center;
		border-top: 1px solid #edf2f7;
		color: #111827;
		display: flex;
		font-size: 14px;
		font-weight: 900;
		gap: 8px;
		justify-content: center;
		margin: 14px 0 12px;
		padding-top: 14px;
		text-transform: uppercase;
	}

	#phuyu_datos .phuyu-results-title i {
		color: #405189;
		font-size: 16px;
	}

	@media (max-width: 767.98px) {
		#phuyu_datos .phuyu-report-header {
			padding: 15px;
		}

		#phuyu_datos .phuyu-report-actions {
			justify-content: stretch;
			margin-top: 14px;
		}

		#phuyu_datos .phuyu-report-actions .btn {
			flex: 1 1 145px;
			justify-content: center;
		}

		#phuyu_datos .phuyu-filter-actions {
			grid-template-columns: 1fr;
		}
	}
</style>

<div id="phuyu_datos" class="phuyu-reportes-velzon phuyu-velzon-list">
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<div class="phuyu-report-header">
					<div class="row g-3 align-items-center">
						<div class="col-lg-5">
							<div class="phuyu-report-heading">
								<span class="phuyu-report-icon"><i class="ri-line-chart-line"></i></span>
								<div>
									<div class="phuyu-report-eyebrow">Modulo de reportes</div>
									<h4 class="phuyu-report-title">Reporte de ventas</h4>
									<p class="phuyu-report-subtitle">Consulta ventas, comprobantes y estados SUNAT por periodo.</p>
								</div>
							</div>
						</div>
						<div class="col-lg-7">
							<div class="phuyu-report-actions">
								<button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modal_vendedor"><i class="bi bi-person-lines-fill"></i> Por vendedor</button>
								<button type="button" class="btn btn-info btn-sm" v-on:click="modal_clientes()"><i class="bi bi-people"></i> Por clientes</button>
								<button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modal_productos"><i class="bi bi-box-seam"></i> Por productos</button>
								<button type="button" class="btn btn-danger btn-sm" v-on:click="mas_reportes()"><i class="bi bi-printer"></i> General contable</button>
							</div>
						</div>
					</div>
					<div class="phuyu-report-meta">
						<span class="phuyu-report-pill"><i class="bi bi-building"></i> <?php echo $_SESSION["phuyu_sucursal"]; ?></span>
						<span class="phuyu-report-pill"><i class="bi bi-calendar3"></i> <?php echo date('d/m/Y'); ?></span>
						<span class="phuyu-report-pill"><i class="bi bi-shield-check"></i> Estados SUNAT incluidos</span>
					</div>
				</div>
				<div class="phuyu-filter-band">
					<div class="phuyu-filter-title">
						<i class="ri-filter-3-line"></i>
						Filtros de consulta
					</div>
					<div class="row g-3 align-items-end">
						<div class="col-xl-3 col-lg-4 col-md-6">
							<label class="phuyu-filter-label">Sucursal</label>
							<select class="form-select" v-model="campos.codsucursal" v-on:change="phuyu_cajas()">
								<option value="0">TODAS SUCURSALES</option>
								<?php
									foreach ($sucursales as $key => $value) { ?>
										<option value="<?php echo $value["codsucursal"];?>"><?php echo $value["descripcion"];?></option>
									<?php }
								?>
							</select>
						</div>
						<div class="col-xl-2 col-lg-4 col-md-6">
							<label class="phuyu-filter-label">Caja</label>
							<select class="form-select" v-model="campos.codcaja" disabled>
								<option value="0">TODAS CAJAS</option>
								<option v-for="dato in cajas" v-bind:value="dato.codcaja"> {{dato.descripcion}} </option>
							</select>
						</div>
						<div class="col-xl-2 col-lg-4 col-md-6">
							<label class="phuyu-filter-label">Almacen</label>
							<select class="form-select input-sm" v-model="campos.codalmacen">
								<option value="0">TODOS ALMACENES</option>
								<option v-for="dato in almacenes" v-bind:value="dato.codalmacen"> {{dato.descripcion}} </option>
							</select>
						</div>
						<div class="col-xl-2 col-lg-4 col-md-6">
							<label class="phuyu-filter-label">Comprobante</label>
							<select class="form-select input-sm" v-model="campos.codcomprobantetipo">
								<option value="0">TODOS</option>
								<?php foreach ($comprobantes as $value) { ?>
									<option value="<?php echo $value["codcomprobantetipo"];?>"><?php echo $value["descripcion"];?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-xl-3 col-lg-4 col-md-6">
							<label class="phuyu-filter-label">Formato PDF para ZIP</label>
							<select class="form-select input-sm" v-model="campos.formato_pdf">
								<option value="ticket">TICKET</option>
								<option value="a5">A5</option>
								<option value="a4">A4</option>
							</select>
						</div>
						<div class="col-xl-2 col-lg-4 col-md-6">
							<label class="phuyu-filter-label"><i class="bi bi-calendar3"></i> Desde</label>
							<input type="date" class="form-control" id="fechadesde" value="<?php echo date('Y-m-d');?>" autocomplete="off">
						</div>
						<div class="col-xl-2 col-lg-4 col-md-6">
							<label class="phuyu-filter-label"><i class="bi bi-calendar3"></i> Hasta</label>
							<input type="date" class="form-control" id="fechahasta" value="<?php echo date('Y-m-d');?>" autocomplete="off">
						</div>
						<div class="col-xl-4 col-lg-5 col-md-6">
							<div class="phuyu-filter-actions">
								<button type="button" class="btn btn-warning" title="Consultar ventas" v-on:click="ver_consulta()">
									<i class="bi bi-search"></i>
								</button>
								<button type="button" class="btn btn-primary" v-on:click="zip_tickets_ventas()">
									<i class="ri-file-zip-line"></i> Tickets ZIP
								</button>
							</div>
						</div>
					</div>
					<div class="phuyu-results-title" id="consulta">
						<i class="ri-table-line"></i>
						Informacion generada
					</div>
				</div>
				<div class="row form-group">
					<div class="table-responsive">
						<table class="table table-striped" style="font-size: 11px">
							<thead>
								<th>#</th>
								<th>COMPROBANTE</th>
								<th>FECHA</th>
								<th>DOCUMENTO</th>
								<th>CLIENTE</th>
								<th>SUNAT</th>
								<th>SUBTOTAL</th>
								<th>IGV</th>
								<th>ICBPER</th>
								<th>TOTAL</th>
								<th>CONDICION</th>
							</thead>
							<tbody>
								<tr v-for="(dato,index) in detalle">
									<td>{{index+1}}</td>
									<td>{{dato.seriecomprobante}}-{{dato.nrocomprobante}}</td>
									<td>{{dato.fechacomprobante}}</td>
									<td>{{dato.documento}}</td>
									<td>{{dato.cliente}}</td>
									<td>
										<span class="badge bg-light text-muted border" v-if="parseInt(dato.codcomprobantetipo)==5" title="Comprobante interno, no se envia a SUNAT">NO APLICA</span>
										<span class="badge bg-success" v-else-if="parseInt(dato.estadosunat)==1" v-bind:title="dato.respuestasunat">ACEPTADO</span>
										<span class="badge bg-info" v-else-if="parseInt(dato.estadosunat)==2" v-bind:title="dato.respuestasunat">ACEPTADO OBS.</span>
										<span class="badge bg-danger" v-else-if="parseInt(dato.estadosunat)==3" v-bind:title="dato.respuestasunat">RECHAZADO</span>
										<span class="badge bg-warning text-dark" v-else-if="parseInt(dato.estadosunat)==4" v-bind:title="dato.respuestasunat">OBSERVADO</span>
										<span class="badge bg-secondary" v-else v-bind:title="dato.respuestasunat">PENDIENTE</span>
									</td>
									<td>{{dato.valorventa}}</td>
									<td>{{dato.igv}}</td>
									<td>{{dato.icbper}}</td>
									<td>{{dato.importe}}</td>
									<td>
										<span v-if="dato.condicionpago==1">CONTADO</span>
										<span v-else>CREDITO</span>
									</td>
								</tr>
								<tr v-for="(dato1,index1) in totales">
									<td colspan="6" align="right" style="font-weight: 700;font-size: 12px">TOTALES</td>
									<td style="font-weight: 700;font-size: 12px">{{dato1.valorventatotal}}</td>
									<td style="font-weight: 700;font-size: 12px">{{dato1.igvtotal}}</td>
									<td style="font-weight: 700;font-size: 12px">{{dato1.icbpertotal}}</td>
									<td style="font-weight: 700;font-size: 12px">{{dato1.totalgeneral}}</td>
									<td></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="modal_vendedor" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	  <div class="modal-dialog">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h4 class="modal-title">Reporte de ventas por vendedores</h4>
	      </div>
	      <div class="modal-body">
	        <div class="row form-group">
	        	<div class="col-md-12">
	        		<label>VENDEDORES</label>
	        		<select class="form-select" v-model="campos.codvendedor">
						<option value="">TODOS LOS VENDEDORES</option>
						<?php 
							foreach ($vendedores as $key => $value) { ?>
								<option value="<?php echo $value["codpersona"];?>"><?php echo $value["razonsocial"];?></option>
							<?php }
						?>
					</select>
	        	</div>
	        </div><br>
	        <div class="row form-group">
	        	<div class="col-md-12">
	        		<div align="center">
						<button type="button" class="btn btn-danger btn-sm" v-on:click="pdf_ventas_vendedor_resumen()" style="margin-bottom: 1rem"><i class="bi bi-printer"></i> Reporte general PDF</button>
						<button type="button" class="btn btn-danger btn-sm" v-on:click="pdf_ventas_vendedor()" style="margin-bottom: 1rem">
							<i class="bi bi-printer"></i> Reporte detallado PDF
						</button>
						<button type="button" class="btn btn-danger btn-sm" v-on:click="pdf_ventas_vendedor()" style="margin-bottom: 1rem">
							<i class="bi bi-printer"></i> Reporte segundo formato
						</button>
					</div>
				</div>
			</div>
			<div class="row form-group">
				<div class="col-md-12">
					<div align="center">
						<button type="button" class="btn btn-success btn-sm" v-on:click="excel_ventas_vendedor_resumen()"><i class="bi bi-file-earmark-excel"></i> Reporte general EXCEL</button>
						<button type="button" class="btn btn-success btn-sm" v-on:click="excel_ventas_vendedor()"><i class="bi bi-file-earmark-excel"></i> Reporte detallado EXCEL</button>
					</div>
	        	</div>
	        </div>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-default" style="border:1px solid #ddd;color:#000 !important" data-bs-dismiss="modal">Cerrar</button>
	      </div>
	    </div>
	  </div>
	</div>

	<div class="modal" id="modal_clientes">
	  <div class="modal-dialog">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h4 class="modal-title">Reporte de ventas por clientes</h4>
	      </div>
	      <div class="modal-body">
	        <div class="row form-group">
	        	<div class="col-md-12">
	        		<label>CLIENTE</label>	        		
					<select name="codpersona" id="codpersona" required>
						<option value="0">SELECCIONAR CLIENTE</option>
					</select>
	        	</div>
	        </div><br>
	        <div class="row form-group">
	        	<div class="col-md-12">
	        		<div align="center">
						<button type="button" class="btn btn-danger btn-sm" v-on:click="pdf_ventas_cliente()"><i class="bi bi-printer"></i> Resumen PDF</button>
						<button type="button" class="btn btn-danger btn-sm" v-on:click="pdf_ventas_cliente_detallado()">
							<i class="bi bi-printer"></i> Detallado PDF
						</button>
						<button type="button" class="btn btn-success btn-sm" v-on:click="excel_ventas_cliente()"><i class="bi bi-file-earmark-excel"></i> Resumen EXCEL</button>
						<button type="button" class="btn btn-success btn-sm" v-on:click="excel_ventas_cliente_detallado()"><i class="bi bi-file-earmark-excel"></i> Detallado EXCEL</button>
					</div>
				</div>
			</div>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-default" style="border:1px solid #ddd;color:#000 !important" data-bs-dismiss="modal">Cerrar</button>
	      </div>
	    </div>
	  </div>
	</div>

	<div class="modal fade" id="modal_productos" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	  <div class="modal-dialog">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h4 class="modal-title">Reporte de ventas por productos</h4>
	      </div>
	      <div class="modal-body">
	        <div class="row form-group">
	        	<div class="col-md-12">
					<div align="center">
						<button type="button" class="btn btn-danger btn-sm w-100" v-on:click="pdf_productos_vendidos()"><i class="bi bi-printer"></i> Resumen formato PDF</button>
						<button type="button" class="btn btn-success btn-sm w-100" v-on:click="excel_productos_vendidos()"><i class="bi bi-file-earmark-excel"></i> Resumen formato EXCEL</button>
					</div>
				</div>
			</div>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-default" style="border:1px solid #ddd;color:#000 !important" data-bs-dismiss="modal">Cerrar</button>
	      </div>
	    </div>
	  </div>
	</div>

<div id="modal_reportes" class="modal fade" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <!-- HEADER -->
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold">
                    GENERAR REPORTES DE VENTAS
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- BODY -->
            <div class="modal-body">

                <!-- FECHAS -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="ri-calendar-line"></i> Desde
                        </label>
                        <input type="date" class="form-control"
                               id="fechadesde_mas"
                               value="<?php echo date('Y-m-d');?>">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="ri-calendar-line"></i> Hasta
                        </label>
                        <input type="date" class="form-control"
                               id="fechahasta_mas"
                               value="<?php echo date('Y-m-d');?>">
                    </div>
                </div>

                <div class="row g-3">

                    <!-- TABLA -->
                    <div class="col-lg-7">
                        <div class="table-responsive border rounded" style="max-height:300px;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th class="text-center">✔</th>
                                        <th>Tipo Comprobante</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($comprobantes as $value) { ?>
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox"
                                                       class="form-check-input"
                                                       name="comprobantes"
                                                       value="<?php echo $value['codcomprobantetipo'];?>"
                                                       checked>
                                            </td>
                                            <td><?php echo $value["descripcion"];?></td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- BOTONES -->
                    <div class="col-lg-5">

                        <div class="d-grid gap-2 mb-3">
                            <button class="btn btn-success"
                                    v-on:click="pdf_reporte_ventas(1)">
                                <i class="ri-file-pdf-line"></i> Ventas
                            </button>

                            <button class="btn btn-success"
                                    v-on:click="pdf_reporte_ventas_det(1)">
                                <i class="ri-file-list-line"></i> Ventas Detallado
                            </button>

                            <button class="btn btn-danger"
                                    v-on:click="pdf_reporte_ventas(0)">
                                <i class="ri-close-circle-line"></i> Ventas Anuladas
                            </button>

                            <button class="btn btn-danger"
                                    v-on:click="pdf_reporte_ventas_det(0)">
                                <i class="ri-file-warning-line"></i> Anuladas Detallado
                            </button>
                        </div>

                        <hr>

                        <h6 class="text-center fw-bold mb-3">
                            FORMATOS CONTABLES
                        </h6>

                        <div class="d-grid gap-2">
                            <button class="btn btn-warning"
                                    v-on:click="pdf_contable_ventas()">
                                <i class="ri-file-pdf-line"></i> Ventas PDF
                            </button>

                            <button class="btn btn-warning"
                                    v-on:click="excel_contable_ventas()">
                                <i class="ri-file-excel-line"></i> Ventas Excel
                            </button>
                        </div>

                    </div>

                </div>
            </div>

        </div>
    </div>
</div>


</div>

<div class="modal fade" id="modal_zip_tickets" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content border-0 shadow">
			<div class="modal-body text-center p-4">
				<div class="spinner-border text-primary mb-3" role="status" aria-hidden="true"></div>
				<h5 class="fw-bold mb-2">Generando ZIP de comprobantes</h5>
				<p class="text-muted mb-2">Estamos preparando los PDFs filtrados. Esto puede tomar unos segundos.</p>
				<div class="fw-bold text-primary" id="zip_tickets_progreso">Iniciando proceso...</div>
				<div class="progress mt-3" style="height: 8px;">
					<div class="progress-bar progress-bar-striped progress-bar-animated" id="zip_tickets_barra" style="width: 0%"></div>
				</div>
			</div>
		</div>
	</div>
</div>

<script> 
	var campos = {"codsucursal":'<?php echo $_SESSION['phuyu_codsucursal'];?>',"codcaja":0,"codalmacen":0,"codcomprobantetipo":0,"formato_pdf":"ticket","codpersona":0,"codvendedor":"","fechadesde":"","fechahasta":"","estado":1};

	var pantalla = jQuery(document).height(); $("#reporte_ventas").css({height: pantalla - 250});
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_reportes/ventas.js?v=<?php echo filemtime(FCPATH.'phuyu/phuyu_reportes/ventas.js');?>"> </script>
<script src="<?php echo base_url();?>phuyu/phuyu_reportes/selects.js?v=<?php echo filemtime(FCPATH.'phuyu/phuyu_reportes/selects.js');?>"> </script>
