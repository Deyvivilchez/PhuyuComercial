<div id="phuyu_datos">
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<div class="row form-group">
					<div class="col-md-3"> <h5 style="letter-spacing:1px;"> <b>REPORTE DE PEDIDOS</b> </h5> </div>
					<div class="col-md-6" align="right">
						<button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modal_vendedor"><i data-acorn-icon="content"></i> X VENDEDORES</button>
						<button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modal_clientes"><i data-acorn-icon="content"></i> X CLIENTES</button>
						<button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modal_productos"><i data-acorn-icon="content"></i> X PRODUCTOS</button>
					</div>
					<div class="col-md-3" align="right">
						<div class="dropdown">
                          <button class="btn btn-danger btn-sm dropdown-toggle mb-1"
                            type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Reporte de pedidos en Formatos
                          </button>
                          <div class="dropdown-menu">
                            <a class="dropdown-item" href="javascript:;" v-on:click="pdf_pedidos()">Resumen PDF</a>
                            <a class="dropdown-item" href="javascript:;" v-on:click="pdf_pedidos_detallado()">Detallado PDF</a>
                            <a class="dropdown-item" href="javascript:;" v-on:click="excel_pedidos()">Resumen Excel</a>
                            <a class="dropdown-item" href="javascript:;" v-on:click="excel_pedidos_detallado()">Detallado Excel</a>
                          </div>
                        </div>
					</div>
				</div>
				<input type="hidden" id="sucursal" value="<?php echo $_SESSION["phuyu_codsucursal"];?>" name="">
				<div class="row form-group">
					<div class="col-md-4">
						<label>SUCURSALES</label>
						<select class="form-select" v-model="campos.codsucursal">
							<option value="0">TODAS SUCURSALES</option>
							<?php 
								foreach ($sucursales as $key => $value) { ?>
									<option value="<?php echo $value["codsucursal"];?>"><?php echo $value["descripcion"];?></option>	
								<?php }
							?>
						</select>
						<input type="hidden" id="fecharef" value="<?php echo date("Y-m-d");?>">
					</div>
					<div class="col-md-2">
						<input type="hidden" id="fechad" value="<?php echo date("Y-m-01");?>">
						<label>DESDE</label>
						<input type="date" class="form-control" id="fechadesde" v-model="campos.fechadesde" v-on:blur="phuyu_fecha()">
					</div>
					<div class="col-md-2">
						<label>HASTA</label>
						<input type="hidden" id="fechah" value="<?php echo date("Y-m-d");?>">
						<input type="date" class="form-control" id="fechahasta" v-model="campos.fechahasta" v-on:blur="phuyu_fecha()">
					</div>
					<div class="col-md-2">
						<label>ESTADOS</label>
						<select class="form-select" v-model="campos.estado">
							<option value="0">TODOS</option>
							<option value="1">PENDIENTES</option>
							<option value="2">CANJEADOS</option>
							<option value="3">ANULADOS</option>
						</select>
					</div>
					<div class="col-md-2" style="margin-top: 1.2rem">
						<button type="button" class="btn btn-warning btn-icon" v-on:click="ver_consulta()"><i data-acorn-icon="search"></i> Consultar</button>
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
								<th>TOTAL</th>
								<th>CONDICION</th>
							</thead>
							<tbody>
								<tr v-for="(dato,index) in detalle">
									<td>{{index+1}}</td>
									<td>{{dato.seriecomprobante}}-{{dato.nrocomprobante}}</td>
									<td>{{dato.fechapedido}}</td>
									<td>{{dato.documento}}</td>
									<td>{{dato.cliente}}</td>
									<td>{{dato.valorventa}}</td>
									<td>{{dato.igv}}</td>
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
									<td style="font-weight: 700;font-size: 12px">{{dato1.totalgeneral}}</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>	
			</div>
		</div>
		<div class="modal fade" id="modal_vendedor" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			<div class="modal-dialog">
			<div class="modal-content">
			  <div class="modal-header modal-phuyu-titulo">
			    <h4 class="modal-title">Reporte de pedidos por vendedores</h4>
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
							<button type="button" class="btn btn-danger btn-sm" v-on:click="pdf_pedidos_vendedor_resumen()" style="margin-bottom: 1rem"><i class="fa fa-print"></i> Reporte general PDF</button>
							<button type="button" class="btn btn-danger btn-sm" v-on:click="pdf_pedidos_vendedor()" style="margin-bottom: 1rem">
								<i class="fa fa-print"></i> Reporte detallado PDF
							</button>
						</div>
					</div>
				</div>
				<div class="row form-group">
					<div class="col-md-12">
						<div align="center">
							<button type="button" class="btn btn-success btn-sm" v-on:click="excel_pedidos_vendedor_resumen()"><i class="fa fa-file-excel-o"></i> Reporte general EXCEL</button>
							<button type="button" class="btn btn-success btn-sm" v-on:click="excel_pedidos_vendedor()"><i class="fa fa-file-excel-o"></i> Reporte detallado EXCEL</button>
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

		<div class="modal fade" id="modal_clientes" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
			<div class="modal-dialog">
			<div class="modal-content">
			  <div class="modal-header modal-phuyu-titulo">
			    <h4 class="modal-title">Reporte de pedidos por clientes</h4>
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
							<button type="button" class="btn btn-danger btn-sm" v-on:click="pdf_ventas_cliente()"><i class="fa fa-print"></i> Resumen PDF</button>
							<button type="button" class="btn btn-danger btn-sm" v-on:click="pdf_ventas_cliente_detallado()">
								<i class="fa fa-print"></i> Detallado PDF
							</button>
							<button type="button" class="btn btn-success btn-sm" v-on:click="excel_ventas_cliente()"><i class="fa fa-file-excel-o"></i> Resumen EXCEL</button>
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
			    <h4 class="modal-title">Reporte de pedidos por productos</h4>
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
</div>
<script src="<?php echo base_url();?>phuyu/phuyu_reportes/pedidos.js"> </script>
<script src="<?php echo base_url();?>phuyu/phuyu_reportes/selects.js"> </script>