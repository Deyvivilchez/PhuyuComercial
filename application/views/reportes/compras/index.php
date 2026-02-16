<div id="phuyu_datos">
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<div class="row form-group mb-3">
					<div class="col-md-3"> <h5 style="letter-spacing:1px;"> <b>REPORTE DE COMPRAS</b> </h5> </div>
					<div class="col-md-9">
						<button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modal_clientes"><i data-acorn-icon="content"></i> X PROVEEDORES</button>
						<button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modal_productos"><i data-acorn-icon="content"></i> X PRODUCTOS</button>
						<button type="button" class="btn btn-danger btn-sm" v-on:click="pdf_compras()"><i data-acorn-icon="print"></i> Resumen PDF</button>
						<button type="button" class="btn btn-danger btn-sm" v-on:click="pdf_compras_detallado()"><i data-acorn-icon="print"></i> Detallado PDF</button>
						<button type="button" class="btn btn-success btn-sm" v-on:click="excel_compras()"><i data-acorn-icon="file-text"></i> Resumen EXCEL</button>
						<button type="button" class="btn btn-success btn-sm" v-on:click="excel_compras_detallado()"><i data-acorn-icon="file-text"></i> Detallado EXCEL</button>
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
						<button type="button" class="btn btn-white btn-icon" v-on:click="ver_consulta()"><i data-acorn-icon="search"></i> Consultar</button>
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
								</tr>
								<tr v-for="(dato1,index1) in totales">
									<td colspan="6" align="right" style="font-weight: 700;font-size: 12px">TOTALES</td>
									<td style="font-weight: 700;font-size: 12px">{{dato1.valorventatotal}}</td>
									<td style="font-weight: 700;font-size: 12px">{{dato1.igvtotal}}</td>
									<td style="font-weight: 700;font-size: 12px">{{dato1.totalgeneral}}</td>
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
				<div class="modal-header modal-phuyu-titulo">
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
	      <div class="modal-header modal-phuyu-titulo">
	        <h4 class="modal-title">Reporte de compras por proveedores</h4>
	      </div>
	      <div class="modal-body">
	        <div class="row form-group">
	        	<div class="col-md-12">
	        		<label>PROVEEDORES</label>	        		
					<select name="codpersona" id="codpersona" required>
						<option value="0">TODOS</option>
					</select>
	        	</div>
	        </div><br>
	        <div class="row form-group">
	        	<div class="col-md-12">
	        		<div align="center">
						<button type="button" class="btn btn-danger btn-sm" v-on:click="phuyu_comprasproveedorpdf()"><i class="fa fa-print"></i> Resumen PDF</button>
						<button type="button" class="btn btn-danger btn-sm" v-on:click="phuyu_comprasproveedorpdfdet()">
							<i class="fa fa-print"></i> Detallado PDF
						</button>
						<button type="button" class="btn btn-success btn-sm" v-on:click="phuyu_comprasproveedorexcel()"><i class="fa fa-file-excel-o"></i> Resumen EXCEL</button>
						<button type="button" class="btn btn-success btn-sm" v-on:click="excel_ventas_cliente_detallado()"><i class="fa fa-file-excel-o"></i> Detallado EXCEL</button>
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
	      <div class="modal-header modal-phuyu-titulo">
	        <h4 class="modal-title">Reporte de compras por productos</h4>
	      </div>
	      <div class="modal-body">
	        <div class="row form-group">
	        	<div class="col-md-12">
					<div align="center">
						<button type="button" class="btn btn-danger btn-sm btn-block" v-on:click="pdf_productos_vendidos()"><i class="fa fa-print"></i> Resumen formato PDF</button>
						<button type="button" class="btn btn-success btn-sm btn-block" v-on:click="excel_productos_vendidos()"><i class="fa fa-file-excel-o"></i> Resumen formato EXCEL</button>
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
<script src="<?php echo base_url();?>phuyu/phuyu_reportes/compras.js"> </script>
<script src="<?php echo base_url();?>phuyu/phuyu_reportes/selectscompra.js"> </script>