<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_datos" class="phuyu-reportes-velzon phuyu-velzon-list">
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<div class="row form-group">
					<div class="col-md-3">
						<p style="font-size: 17px;font-weight: bold;">REPORTE DE VENTAS</p>
					</div>
					<div class="col-md-6" align="right">
						<div style="padding-bottom: 1.5rem">
							<button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modal_vendedor"><i class="bi bi-layout-text-window"></i> X VENDEDOR</button>
							<button type="button" class="btn btn-info btn-sm" v-on:click="modal_clientes()"><i class="bi bi-layout-text-window"></i> X CLIENTES</button>
							<button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modal_productos"><i class="bi bi-layout-text-window"></i> X PRODUCTOS</button>
						</div>
					</div>	
					<div class="col-md-3">
						<div align="right" style="padding-bottom: 1.5rem">
							<button type="button" class="btn btn-danger btn-sm" v-on:click="mas_reportes()"><i class="bi bi-printer"></i> REPORTE GENERAL CONTABLE</button>
						</div>
					</div>
				</div>
				<div class="row form-group">
					<div class="col-md-3">
						<label>SUCURSAL</label>
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
						<label>CAJA</label>	
						<select class="form-select" v-model="campos.codcaja" disabled>
							<option value="0">TODAS CAJAS</option>
							<option v-for="dato in cajas" v-bind:value="dato.codcaja"> {{dato.descripcion}} </option>
						</select>
					</div>
					<div class="col-md-2">
						<label>ALMACEN</label>
						<select class="form-select input-sm" v-model="campos.codalmacen">
							<option value="0">TODOS ALMACENES</option>
							<option v-for="dato in almacenes" v-bind:value="dato.codalmacen"> {{dato.descripcion}} </option>
						</select>
					</div>
					<div class="col-md-2">
						<label><i class="bi bi-calendar3"></i> DESDE</label>
						<input type="date" class="form-control" id="fechadesde" value="<?php echo date('Y-m-d');?>" autocomplete="off">
					</div>
					<div class="col-md-2">
						<label><i class="bi bi-calendar3"></i> HASTA</label>
						<input type="date" class="form-control" id="fechahasta" value="<?php echo date('Y-m-d');?>" autocomplete="off">
					</div>
					<div class="col-md-1" style="margin-top: 1.2rem">
						<button type="button" class="btn btn-warning btn-icon" v-on:click="ver_consulta()"><i class="bi bi-search"></i></button>
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
								<th>COMPROBANTE</th>
								<th>FECHA</th>
								<th>DOCUMENTO</th>
								<th>CLIENTE</th>
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
									<td>{{dato.valorventa}}</td>
									<td>{{dato.igv}}</td>
									<td>{{dato.icbper}}</td>
									<td>{{dato.importe}}</td>
									<td>
										<span v-if="dato.condicionpago==1">CONTADO</span>
										<span v-else="dato.condicionpago==1">CREDITO</span>
									</td>
								</tr>
								<tr v-for="(dato1,index1) in totales">
									<td colspan="5" align="right" style="font-weight: 700;font-size: 12px">TOTALES</td>
									<td style="font-weight: 700;font-size: 12px">{{dato1.valorventatotal}}</td>
									<td style="font-weight: 700;font-size: 12px">{{dato1.igvtotal}}</td>
									<td style="font-weight: 700;font-size: 12px">{{dato1.icbpertotal}}</td>
									<td style="font-weight: 700;font-size: 12px">{{dato1.totalgeneral}}</td>
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

<script> 
	var campos = {"codsucursal":'<?php echo $_SESSION['phuyu_codsucursal'];?>',"codcaja":0,"codalmacen":0,"codpersona":0,"codvendedor":"","fechadesde":"","fechahasta":"","estado":1};

	var pantalla = jQuery(document).height(); $("#reporte_ventas").css({height: pantalla - 250});
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_reportes/ventas.js"> </script>
<script src="<?php echo base_url();?>phuyu/phuyu_reportes/selects.js"> </script>
