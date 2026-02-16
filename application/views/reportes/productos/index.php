<div id="phuyu_datos">
	<div class="phuyu_body">
		<div class="card">
			<div class="card-body">
				<div class="row form-group">
					<div class="col-md-8">
						<h5> <b>REPORTE GENERAL DE PRODUCTOS</b> </h5> 
					</div>
					<div class="col-md-2">
                    	<div class="dropdown">
                          <button class="btn btn-warning dropdown-toggle mb-1"
                            type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Stock Productos
                          </button>
                          <div class="dropdown-menu">
                            <a class="dropdown-item" href="javascript:;" v-on:click="stock_general()">Formato PDF</a>
                            <a class="dropdown-item" href="javascript:;" v-on:click="stock_general_excel()">Formato Excel</a>
                          </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                    	<div class="dropdown">
                          <button class="btn btn-danger dropdown-toggle mb-1"
                            type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Stock Valorizado
                          </button>
                          <div class="dropdown-menu">
                            <a class="dropdown-item" href="javascript:;" v-on:click="stock_valorizado()">Formato PDF</a>
                            <a class="dropdown-item" href="javascript:;" v-on:click="stock_valorizado_excel()">Formato Excel</a>
                          </div>
                        </div>
					</div>
				</div>
				<div class="row form-group" >
					<div class="col-md-2">
						<label>ALMACEN</label>
						<select class="form-select" v-model="campos.codalmacen">
							<?php 
								foreach ($almacenes as $key => $value) { ?>
									<option value="<?php echo $value['codalmacen'];?>"><?php echo $value["descripcion"];?></option>
								<?php } ?>
							?>
						</select>
					</div>
					<div class="col-md-2">
						<label>LINEA PRODUCTO</label>
						<select class="form-select" v-model="campos.codlinea">
							<option value="0">TODAS LAS LINEAS DE PRODUCTOS</option>
							<?php 
								foreach ($lineas as $key => $value) { ?>
									<option value="<?php echo $value['codlinea'];?>"><?php echo $value["descripcion"];?></option>
								<?php } ?>
							?>
						</select>
					</div>
					<div class="col-md-2">
						<label>STOCK PRODUCTO</label>
						<select class="form-select" v-model="campos.stock">
							<option value="0">TODOS</option>
							<option value="1">CON STOCK</option>
							<option value="2">SIN STOCK</option>
						</select>
					</div>
					<div class="col-md-2">
						<label>DESDE FECHA (KARDEX)</label>
						<input type="date" class="form-control" id="fechad" v-model="campos.fechad" v-on:blur="phuyu_fecha()">
					</div>
					<div class="col-md-2">
						<label>A LA FECHA (KARDEX)</label>
						<input type="date" class="form-control" id="fecha" v-model="campos.fecha" v-on:blur="phuyu_fecha()">
					</div>
					<div class="col-md-1">
						<label style="margin-top:5px;">CTRL&nbsp;STOCK</label> <br>
						<label style="margin-top:5px;">ACTIVOS</label>
					</div>

					<div class="col-md-1">
						<input type="checkbox" class="form-check-input" style="height:20px;width:20px;" v-model="campos.controlstock"> <br>
						<input type="checkbox" class="form-check-input" style="height:20px;width:20px;" v-model="campos.estado">
					</div>
				</div>

				<div class="row form-group" >
					<div class="col-md-4">
						<input type="text" class="form-control" v-model="campos.buscar" placeholder="BUSCAR PRODUCTO . . ." v-on:keyup.13="buscar_productos()">
					</div>
					<div class="col-md-2">
						<button type="button" class="btn btn-white btn-icon" v-on:click="buscar_productos()">
							<i data-acorn-icon="search"></i>
							Consultar
						</button>
					</div>
					<div class="col-md-2">
                    	<div class="dropdown">
                          <button class="btn btn-info dropdown-toggle mb-1"
                            type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Compras y Ventas
                          </button>
                          <div class="dropdown-menu">
                            <a class="dropdown-item" href="javascript:;" v-on:click="compras_producto()">Lista de Compras</a>
                            <a class="dropdown-item" href="javascript:;" v-on:click="ventas_producto()">Lista de Ventas</a>
                          </div>
                        </div>
					</div>
					<div class="col-md-2">
                    	<div class="dropdown">
                          <button class="btn btn-info dropdown-toggle mb-1"
                            type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Precios Productos
                          </button>
                          <div class="dropdown-menu">
                            <a class="dropdown-item" href="javascript:;" v-on:click="pdf_precios()">Precios PDF</a>
                            <a class="dropdown-item" href="javascript:;" v-on:click="pdf_precios_stock()">Precios + Stock PDF</a>
                            <a class="dropdown-item" href="javascript:;" v-on:click="excel_precios()">Precios Excel</a>
                          </div>
                        </div>
					</div>
					<div class="col-md-2">
                    	<div class="dropdown">
                          <button class="btn btn-info dropdown-toggle mb-1"
                            type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Kardex Valorizado
                          </button>
                          <div class="dropdown-menu">
                            <a class="dropdown-item" href="javascript:;" v-on:click="pdf_kardexproductos()">Formato PDF</a>
                            <a class="dropdown-item" href="javascript:;" v-on:click="excel_kardexproductos()">Formato Excel</a>
                          </div>
                        </div>
					</div>
				</div>
				<div class="detalle" v-if="consultar.precios==1" style="height:150px;overflow-y:auto;">
					<div v-for="dato in datos" v-if="dato.tiene!=0">
						<h6 align="center"> <b>LINEA: {{dato.descripcion}}</b> </h6>
						<table class="table table-striped" style="font-size: 11px;">
							<thead>
								<tr>
									<th style="width:5px;"> <center> <i class="fa fa-circle-o"></i> </center> </th>
									<th style="width:5px;">ID</th>
									<th style="width:10px;">CODIGO</th>
									<th style="width:35%;">DESCRIPCION</th>
									<th style="width:15%;">UNIDAD</th>
									<th style="width:10%;">STOCK</th>
									<th style="width:10%;">V.X.RECOGER</th>
									<th style="width:10%;">C.X.RECOGER</th>
									<th style="width:10%;">FISICO</th>
									<th style="width:10%;">P.COSTO</th>
									<th style="width:10%;">P.MINIMO</th>
									<th style="width:10%;">P.VENTA</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="d in dato.productos">
									<td> <input type="radio" class="form-check-input" name="phuyu_seleccionar" v-on:click="phuyu_seleccionar(d)"> </td>
									<td>{{d.codproducto}}</td>
									<td>{{d.codigo}}</td>
									<td>{{d.descripcion}}</td>
									<td>{{d.unidad}}</td>
									<td> 
										<button type="button" class="btn btn-success btn-sm btn-block" v-on:click="phuyu_kardex(d)" style="font-size: 1rem">
											<i data-acorn-icon="search"></i> {{d.stock}}
										</button>
									</td>
									<td> 
										<button type="button" class="btn btn-primary btn-sm btn-block" v-on:click="phuyu_recoger(d,20)" style="font-size: 1rem">
											<i class="fa fa-arrow-right"></i> {{d.ventarecogo}}
										</button>
									</td>
									<td> 
										<button type="button" class="btn btn-danger btn-sm btn-block" v-on:click="phuyu_recoger(d,2)" style="font-size: 1rem">
											<i class="fa fa-arrow-right"></i> {{d.comprarecogo}}
										</button>
									</td>
									<td>{{d.fisico}}</td>
									<td>{{d.preciocosto}}</td>
									<td>{{d.preciominimo}}</td>
									<td>{{d.precioventa}}</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>

			<div id="modal_kardex" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="modal-dialog modal-xl">
					<div class="modal-content">
						<div class="modal-header modal-phuyu-titulo">
							<h5 class="modal-title"> <b style="letter-spacing:1px;" id="producto_kardex"></b> </h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> 
						</div>
						<div class="modal-body" style="height:450px;overflow-y:auto;padding:5px 15px;">
							<div class="row form-group mb-4">
								<div class="col-md-1" style="padding-top:7px;"> <label>F.&nbsp;DESDE</label> </div>
								<div class="col-md-2">
									<input type="date" class="form-control " id="fechadesde_k" value="<?php echo date('Y-m-01');?>">
								</div>
								<div class="col-md-1" style="padding-top:7px;"> <label>F.&nbsp;HASTA</label> </div>
								<div class="col-md-2">
									<input type="date" class="form-control" id="fechahasta_k" value="<?php echo date('Y-m-d');?>">
								</div>
								<div class="col-md-2">
									<button type="button" class="btn btn-success btn-block btn-sm" v-on:click="phuyu_kardex_1()">Ver Kardex</button>
								</div>
								<div class="col-md-2">
									<button type="button" class="btn btn-info btn-block btn-sm" v-on:click="phuyu_kardex_pdf()"><i class="fa fa-print"></i>Formato PDF</button>
								</div>
								<div class="col-md-2">
									<button type="button" class="btn btn-warning btn-block btn-sm" v-on:click="phuyu_kardex_excel()"><i class="fa fa-file"></i>Formato EXCEL</button>
								</div>
							</div>

							<table class="table table-bordered table-condensed" style="font-size:10px;color:#000 !important">
								<thead>
									<tr>
										<th rowspan="2" width="3px"><i class="fa fa-calendar"></i></th>
										<th rowspan="2" width="70px">FECHA</th>
										<th rowspan="2">MOTIVO</th>
										<th rowspan="2">COMPROBANTE</th>
										<th rowspan="2">RAZON SOCIAL</th>
										<th colspan="3" style="text-align: center;background-color: #aed4f5">ENTRADAS</th>
										<th colspan="3" style="text-align: center;background-color: #f58585">SALIDAS</th>
										<th colspan="3" style="text-align: center;background-color: #6ecf6e">EXISTENCIAS</th>
									</tr>
									<tr>
										<th>CANTIDAD</th>
										<th>P.U</th>
										<th>TOTAL</th>
										<th>CANTIDAD</th>
										<th>P.U</th>
										<th>TOTAL</th>
										<th>CANTIDAD</th>
										<th>P.U</th>
										<th>TOTAL</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="dato in existencias_a">
										<td colspan="11" align="center"><b>SALDO ANTERIOR</b></td>
										<td><b>{{dato.cantidad_sa}}</b></td>
										<td><b>{{dato.precio_sa}}</b></td>
										<td><b>{{dato.total_sa}}</b></td>
									</tr>
									<tr v-for="dato in existencias">
										<td>
											<button type="button" class="btn btn-success btn-sm" style="margin:0px !important" v-on:click="phuyu_cambiar_fecha(dato)"><i data-acorn-icon="calendar"></i></button>
										</td>
										<td>{{dato.fechakardex}}</td>
										<td>{{dato.motivo}}</td>
										<td>{{dato.seriecomprobante}}-{{dato.nrocomprobante}}</td>
										<td style="font-size:8px;">{{dato.razonsocial}}</td>

										<td style="background-color: #aed4f5"> <b v-if="dato.signo==1">{{dato.convertido}}</b> </td>
										<td style="background-color: #aed4f5"><b v-if="dato.signo==1">{{dato.preciounitario}}</b></td>
										<td style="background-color: #aed4f5"><b v-if="dato.signo==1">{{dato.total}}</b></td>
										<td style="background-color: #f58585"> <b v-if="dato.signo!=1">{{dato.convertido}}</b> </td>
										<td style="background-color: #f58585"><b v-if="dato.signo!=1">{{dato.preciounitario}}</b></td>
										<td style="background-color: #f58585"><b v-if="dato.signo!=1">{{dato.total}}</b></td>
										<td style="background-color: #6ecf6e;"> <b>{{dato.cantidad_sa}}</b> </td>
										<td style="background-color: #6ecf6e;"><b>{{dato.preciounitario_sa}}</b></td>
										<td style="background-color: #6ecf6e;"><b>{{dato.total_sa}}</b></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>

			<div id="modal_comprasventas" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header modal-phuyu-titulo">
							<h5 class="modal-title"> <b style="letter-spacing:1px;" id="producto_compraventa"></b> </h5>

							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body" style="height:450px;overflow-y:auto;padding:5px 15px;">
							<div class="row form-group">
								<div class="col-md-2" style="padding-top:7px;"> <label>FECHA DESDE</label> </div>
								<div class="col-md-2">
									<input type="date" class="form-control" id="fechadesde_cv" value="<?php echo date('Y-m-01');?>">
								</div>
								<div class="col-md-2" style="padding-top:7px;"> <label>FECHA HASTA</label> </div>
								<div class="col-md-2">
									<input type="date" class="form-control" id="fechahasta_cv" value="<?php echo date('Y-m-d');?>">
								</div>
								<div class="col-md-2">
									<select class="form-select" id="codmoneda">
										<option value="0">TODOS</option>
										<?php
											foreach ($monedas as $key => $value) { ?>
												<option value="<?php echo $value["codmoneda"];?>"><?php echo $value["descripcion"];?></option>
											<?php }
										?>
									</select>
								</div>
								<div class="col-md-2">
									<button type="button" class="btn btn-success btn-block btn-sm" v-on:click="phuyu_compraventas()"><i data-acorn-icon="search"></i> BUSCAR</button>
								</div>
							</div>

							<table class="table table-bordered table-condensed" style="font-size:11px;">
								<thead>
									<tr>
										<th>FECHA</th>
										<th>RAZON SOCIAL</th>
										<th>COMPROBANTE</th>
										<th>CANTIDAD</th>
										<th>P.U.</th>
										<th>TOTAL</th>
										<th>MONEDA</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="dato in compraventas">
										<td>{{dato.fechacomprobante}}</td>
										<td>{{dato.razonsocial}}</td>
										<td>{{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
										<td>{{dato.cantidad}}</td>
										<td>{{dato.preciounitario}}</td>
										<td>{{dato.subtotal}}</td>
										<td>{{dato.moneda}}</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>

			<div id="modal_recoger" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="modal-dialog modal-lg">
					<div class="modal-content">
						<div class="modal-header modal-phuyu-titulo">
							<h5 class="modal-title"><b id="producto_recoger"></b></h5>
							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body">
							<div class="table-responsive" style="height:450px;overflow-y:auto;">
								<table class="table table-bordered table-condensed" style="font-size:11px;">
									<thead>
										<tr>
											<th>OPERACION</th>
											<th>DOCUMENTO</th>
											<th>RAZON SOCIAL</th>
											<th>FECHA</th>
											<th>TIPO</th>
											<th>COMPROBANTE</th>
											<th>IMPORTE</th>
											<th>PENDIENTE</th>
										</tr>
									</thead>
									<tbody>
										<tr v-for="dato in recoger">
											<td>
												<span class="label label-danger" v-if="dato.codmovimientotipo==2">COMPRA</span>
												<span class="label label-warning" v-else="dato.codmovimientotipo==20">VENTA</span>
											</td>
											<td>{{dato.documento}}</td>
											<td>{{dato.razonsocial}}</td>
											<td>{{dato.fechakardex}}</td>
											<td>{{dato.tipo}}</td>
											<td>{{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
											<td>S/. {{dato.importe}}</td>
											<td>{{dato.pendiente}}</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div id="modal_kardex_fecha" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="modal-dialog modal-sm">
					<div class="modal-content">
						<div class="modal-header modal-phuyu-titulo">
							<h5 class="modal-title"> <b style="letter-spacing:1px;" id="producto_kardex_fecha"></b> </h5>

							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						</div>
						<div class="modal-body" style="height:300px;overflow-y:auto;padding:5px 15px;">
							<input type="hidden" id="c_codkardex">
							<div class="form-group">
								<label>FECHA KARDEX</label>
								<input type="date" class="form-control" id="c_fechakardex" value="<?php echo date('Y-m-01');?>">
							</div>
							<div class="form-group">
								<label>FECHA COMPROBANTE</label>
								<input type="date" class="form-control" id="c_fechacomprobante" value="<?php echo date('Y-m-01');?>">
							</div> <br>
							<div class="text-center">
								<button type="button" class="btn btn-success" v-on:click="phuyu_cambiar_fecha_1()">GUARDAR CAMBIO DE FECHAS</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }
</script>
<script> 
	var campos = {"codalmacen":<?php echo $_SESSION["phuyu_codalmacen"];?>,"codlinea":0,"stock":0,"fechad":"<?php echo date('Y-m-01');?>","fecha":"<?php echo date("Y-m-d");?>","controlstock":1,"estado":1,"buscar":""};

	var pantalla = jQuery(document).height(); $("#reportes_modal").css({height: pantalla - 65});
	$(".detalle").css({height: pantalla - 280});
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_reportes/productos.js"> </script>