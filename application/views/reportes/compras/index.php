<?php include("application/views/phuyu/phuyu_velzon_module.php");?>
<style>
	#phuyu_datos .phuyu-report-search {
		min-height: 38px;
		font-weight: 800;
		letter-spacing: .02em;
		box-shadow: 0 8px 18px rgba(64, 81, 137, .18);
	}
	#phuyu_datos .phuyu-report-search i,
	#phuyu_datos .phuyu-row-pdf i {
		line-height: 1;
	}
	#phuyu_datos .phuyu-row-pdf {
		width: 34px;
		height: 30px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
	}
	#modal_clientes .select2-container {
		width: 100% !important;
	}
	#modal_clientes .select2-dropdown {
		z-index: 1065;
	}
	#modal_clientes .phuyu-provider-actions {
		display: grid;
		grid-template-columns: repeat(3, minmax(0, 1fr));
		gap: .5rem;
	}
	@media (max-width: 575.98px) {
		#modal_clientes .phuyu-provider-actions {
			grid-template-columns: 1fr;
		}
	}
</style>

<div id="phuyu_datos" class="phuyu-reportes-velzon phuyu-velzon-list">
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<div class="row form-group mb-3">
					<div class="col-md-3"> <h5 style="letter-spacing:1px;"> <b>REPORTE DE COMPRAS</b> </h5> </div>
					<div class="col-md-9">
						<button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modal_clientes"><i class="bi bi-layout-text-window"></i> X PROVEEDORES</button>
						<button type="button" class="btn btn-danger btn-sm" v-on:click="pdf_compras()"><i class="bi bi-printer"></i> Resumen PDF</button>
						<button type="button" class="btn btn-danger btn-sm" v-on:click="pdf_compras_detallado()"><i class="bi bi-printer"></i> Detallado PDF</button>
						<button type="button" class="btn btn-success btn-sm" v-on:click="excel_compras()"><i class="bi bi-file-earmark-excel"></i> Resumen EXCEL</button>
						<button type="button" class="btn btn-success btn-sm" v-on:click="excel_compras_detallado()"><i class="bi bi-file-earmark-excel"></i> Detallado EXCEL</button>
					</div>
				</div>
				<input type="hidden" id="fecharef" value="<?php echo date("Y-m-d");?>">
				<div class="row form-group">
					<div class="col-md-3">
						<label>SUCURSALES</label>
						<select class="form-select" v-model="campos.codsucursal" v-on:change="phuyu_cajas()">
							<option value="0">TODAS SUCURSALES</option>
							<?php 
								foreach ($sucursales as $key => $value) { ?>
									<option value="<?php echo $value["codsucursal"];?>"><?php echo $value["descripcion"];?></option>	
								<?php }
							?>
						</select>
					</div>
					<div class="col-md-2">
						<label>CAJAS</label>
						<select class="form-select" v-model="campos.codcaja">
							<option value="0">TODAS CAJAS</option>
							<option v-for="dato in cajas" v-bind:value="dato.codcaja"> {{dato.descripcion}} </option>
						</select>
					</div>
					<div class="col-md-2">
						<label>DESDE</label>
						<input type="hidden" id="fechad" value="<?php echo date("Y-m-01");?>">
						<input type="date" class="form-control" id="fechadesde" v-model="campos.fechadesde" v-on:blur="phuyu_fecha()">
					</div>
					<div class="col-md-2">
						<label>HASTA</label>
						<input type="hidden" id="fechah" value="<?php echo date("Y-m-d");?>">
						<input type="date" class="form-control" id="fechahasta" v-model="campos.fechahasta" v-on:blur="phuyu_fecha()">
					</div>
					<div class="col-md-1">
						<label>ACTIVOS</label>
						<input type="checkbox" class="form-check-input" style="height:20px;width:20px;" title="ACTIVAR" v-model="campos.estado">
					</div>

					<div class="col-md-2" style="margin-top: 1.2rem">
						<button type="button" class="btn btn-primary w-100 phuyu-report-search" v-on:click="ver_consulta()">
							<i class="bi bi-search me-1"></i> Consultar
						</button>
					</div>
				</div>
				<div class="row form-group mt-4" id="consulta">
					<div class="col-md-12 text-center"><h5><b>INFORMACION GENERADA</b></h5></div>
				</div>
				<div class="row form-group">
					<div class="table-responsive">
						<table class="table table-striped" style="font-size: 11px">
							<thead>
								<th>#</th>
								<th>DOCUMENTO</th>
								<th>PROVEEDOR</th>
								<th>FECHA</th>
								<th>TIPO</th>
								<th>COMPROBANTE</th>
								<th>SUBTOTAL</th>
								<th>IGV</th>
								<th>TOTAL</th>
								<th>CONDICION</th>
								<th class="text-center">PDF</th>
							</thead>
							<tbody>
								<tr v-for="(dato,index) in detalle">
									<td>{{index+1}}</td>
									<td>{{dato.documento}}</td>
									<td>{{dato.cliente}}</td>
									<td>{{dato.fechacomprobante}}</td>
									<td>{{dato.tipo}}</td>
									<td>{{dato.seriecomprobante}}-{{dato.nrocomprobante}}</td>
									<td>{{dato.valorventa}}</td>
									<td>{{dato.igv}}</td>
									<td>{{dato.importe}}</td>
									<td>
										<span v-if="dato.condicionpago==1">CONTADO</span>
										<span v-else="dato.condicionpago==1">CREDITO</span>
									</td>
									<td class="text-center">
										<button type="button" class="btn btn-outline-danger btn-sm phuyu-row-pdf" v-on:click="pdf_compra_individual(dato.codkardex)" title="Ver PDF de esta compra">
											<i class="bi bi-file-earmark-pdf"></i>
										</button>
									</td>
								</tr>
								<tr v-for="(dato1,index1) in totales">
									<td colspan="6" align="right" style="font-weight: 700;font-size: 12px">TOTALES</td>
									<td style="font-weight: 700;font-size: 12px">{{dato1.valorventatotal}}</td>
									<td style="font-weight: 700;font-size: 12px">{{dato1.igvtotal}}</td>
									<td style="font-weight: 700;font-size: 12px">{{dato1.totalgeneral}}</td>
									<td></td>
									<td></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="modal_reportes" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-fullscreen-xxl-down">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">
						<b style="letter-spacing:4px;"><?php echo $_SESSION["phuyu_empresa"];?> </b>
					</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> 
				</div>
				<div class="modal-body" id="reportes_modal" style="height:450px;padding:0px;">
					<iframe id="phuyu_pdf" src="" style="width:100%; height:100%; border:none;"> </iframe>
				</div>
			</div>
		</div>
	</div>

	<div class="modal" id="modal_clientes">
	  <div class="modal-dialog">
	    <div class="modal-content">
	      <div class="modal-header">
	        <h4 class="modal-title">Reporte de compras por proveedores</h4>
	      </div>
	      <div class="modal-body">
	        <div class="row form-group">
	        	<div class="col-md-12">
	        		<label>PROVEEDORES</label>	        		
					<select name="codpersona" id="codpersona" class="form-select" required>
						<option value="0">TODOS</option>
					</select>
	        	</div>
	        </div><br>
	        <div class="row form-group">
	        	<div class="col-md-12">
					<div class="phuyu-provider-actions">
						<button type="button" class="btn btn-danger btn-sm" v-on:click="phuyu_comprasproveedorpdf()"><i class="bi bi-printer"></i> Resumen PDF</button>
						<button type="button" class="btn btn-danger btn-sm" v-on:click="phuyu_comprasproveedorpdfdet()">
							<i class="bi bi-printer"></i> Detallado PDF
						</button>
						<button type="button" class="btn btn-success btn-sm" v-on:click="phuyu_comprasproveedorexcel()"><i class="bi bi-file-earmark-excel"></i> Resumen EXCEL</button>
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
</div>
<script> 
	var campos = {"codsucursal":<?php echo $_SESSION["phuyu_codsucursal"];?>,"codcaja":<?php echo $_SESSION["phuyu_codcaja"];?>,"fechadesde":$("#fechad").val(),"fechahasta":$("#fechah").val(),"estado":1,"codpersona":0};
	var pantalla = jQuery(document).height(); $("#reportes_modal").css({height: pantalla - 65});
	if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_reportes/compras.js?v=<?php echo filemtime(FCPATH . 'phuyu/phuyu_reportes/compras.js'); ?>"> </script>
<script src="<?php echo base_url();?>phuyu/phuyu_reportes/selectscompra.js?v=<?php echo filemtime(FCPATH . 'phuyu/phuyu_reportes/selectscompra.js'); ?>"> </script>
