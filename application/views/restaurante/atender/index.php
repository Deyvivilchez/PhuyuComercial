<div id="phuyu_operacion"> <br>
	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar_pedido()">
		<input type="hidden" id="comprobante" value="<?php echo $sucursal[0]['codcomprobantetipo'];?>">
		<input type="hidden" id="serie" value="<?php echo $sucursal[0]['seriecomprobante'];?>">
		<input type="hidden" id="stockalmacen" value="<?php echo $_SESSION["phuyu_stockalmacen"];?>">
		<input type="hidden" id="itemrepetir" value="<?php echo $_SESSION["phuyu_itemrepetir"];?>">
		<input type="hidden" id="igvsunat" value="<?php echo $_SESSION["phuyu_igv"];?>">
		<input type="hidden" id="icbpersunat" value="<?php echo $_SESSION["phuyu_icbper"];?>">
		<input type="hidden" id="fechapedido" value="<?php echo date('Y-m-d');?>">
		<div class="card" style="margin-top: -2rem">
			<div class="card-body" style="padding: 3px;">
				<div class="row" style="padding:0px;margin:0px"> <br> <br>
					<div class="col-md-5 col-xs-12" style="border:1px solid #bbb;border-radius: .5rem">
						<h6 style="padding: .5rem;background-color: red;text-align: center;color:white;font-weight: 700">MESAS REGISTRADAS</h6>
						<div class="scroll-phuyu-view" style="height:300px;overflow:auto;overflow-x:hidden;">
							<div class="row" style="padding:5px;">
								<div class="col-md-7">
									<select class="form-select input-sm" v-model="campos.codambiente" v-on:change="phuyu_mesas()" id="codambiente" style="border:2px solid #d43f3a;">
										<?php 
											foreach ($ambientes as $key => $value) { ?>
												<option value="<?php echo $value['codambiente'];?>"><?php echo $value["descripcion"];?></option>
											<?php }
										?>
									</select>
								</div>
								<div class="col-md-5">
									<button type="button" class="btn btn-primary btn-block btn-icon" v-on:click="cambiar_mesa()">CAMBIAR MESA</button>
								</div>
							</div>

							<div class="row">
								<div class="col-md-3 col-xs-6 phuyu-mesas" v-for="dato in mesas" v-on:click="phuyu_pedido(dato)">
									<div v-bind:class="dato.color" v-bind:id="dato.codmesa"> 
										<h6>MESA</h6> <h3>{{dato.nromesa}}</h3> <h6>{{dato.texto}}</h6>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="col-md-5 col-xs-12">
						<div class="phuyu_card scroll-phuyu-view" style="height:300px;overflow:auto;overflow-x:hidden;" id="phuyu_restaurante">
						</div>
					</div>

					<div class="col-md-2 col-xs-12">
						<div class="scroll-phuyu-view" style="height:300px;overflow:auto;overflow-x:hidden;">
							<table class="table projects">
								<thead> <tr> <th> <i class="fa fa-ioxhost"></i> LINEAS</th> </tr> </thead>
								<tbody>
									<?php 
										foreach ($lineas as $key => $value) { $estilo = "background:".$value["background"].";color:".$value["color"]; ?>
											<tr>
												<td class="phuyu-restaurante-table" v-on:click="phuyu_producto(<?php echo $value['codlinea']?>)" style="">
													<?php echo $value["descripcion"];?>
												</td> 
											</tr>
										<?php }
									?>
								</tbody>
							</table>
						</div>
					</div>
				</div> <br>

				<div class="row" style="padding:0px; margin:0px;">
					<div class="col-md-5"> 
						<div class="row form-group">
							<div class="col-md-12"> 
								<div class="d-grid">
									<a class="btn btn-primary btn-sm btn-block">MESA {{campos.mesa}} | PEDIDO 00000{{campos.codpedido}}</a>
								</div>
							</div>
						</div>
						<?php
							if ($_SESSION["phuyu_codcontroldiario"]>0) { ?>
							<div class="row form-group">
								<div class="col-md-4">
									<!--<button type="button" class="btn btn-success btn-block btn-sm" v-on:click="phuyu_cocina()">
										<i class="fa fa-print"></i> IMPRIMIR COCINA
									</button>-->
									<div class="d-grid">
										<button type="button" class="btn btn-success mb-2 btn-sm" v-on:click="phuyu_vendedores_caja()">
											<i class="fa fa-dollar"></i> ANFITRION
										</button>
										<button type="button" class="btn btn-warning mb-2 btn-sm" v-on:click="phuyu_movimientos(1)">
											<i class="fa fa-arrow-right"></i> INGRE. CAJA
										</button>	
										<button type="button" class="btn btn-danger mb-2 btn-sm" v-on:click="phuyu_movimientos(2)">
											<i class="fa fa-arrow-left"></i> EGRE. CAJA
										</button>
										<button type="button" class="btn btn-info btn-block btn-sm" v-on:click="phuyu_ventadiaria()">
											<i class="fa fa-money"></i> VENTA DIARIA
										</button>
									</div>
								</div>
								<div class="col-md-4">
									<div class="d-grid">	
										<button type="button" class="btn btn-primary mb-2 btn-sm" v-on:click="phuyu_balancecaja()">
											<i class="fa fa-dollar"></i> BALANCE CAJA
										</button>
										<button type="button" class="btn btn-info btn-block btn-sm" v-on:click="phuyu_avance_pedido()">
											<i class="fa fa-money"></i> AVANCE CUENTA
										</button>
									</div>
								</div>

								<div class="col-md-4">
									<div class="d-grid">
										<button type="submit" class="btn btn-success mb-2 btn-sm" v-bind:disabled="estado==1">
											<i class="fa fa-send"></i> GUARDAR PEDIDO
										</button>
										<button type="button" class="btn btn-warning mb-2 btn-sm" v-on:click="phuyu_atender_pedido()">
											<i class="fa fa-ioxhost"></i> ATENDER PEDIDO
										</button>
										<button type="button" class="btn btn-danger mb-2 btn-sm" v-on:click="phuyu_anular_pedido()">
											<i class="fa fa-trash-o"></i> ANULAR PEDIDO
										</button>
										<button type="button" class="btn btn-primary btn-block btn-sm" v-on:click="phuyu_cobrar_pedido()">
											<i class="fa fa-dollar"></i> COBRAR PEDIDO
										</button>
									</div>
								</div>
							</div>

								<div class="ticket" style="display:none">
									<div id="imprimir_pedido"> </div>
								</div>
							<?php }else{ ?>
								<div class="col-md-12">
									<div class="alert alert-danger text-center">
										<h1><i class="fa fa-money"></i></h1>
										<h4>DEBE APERTURAR CAJA</h4> <br>
										<a href="<?php echo base_url();?>phuyu/w/caja/controlcajas" class="btn btn-success">IR A CAJA</a>
									</div>
								</div>
							<?php }
						?>
					</div>

					<div class="col-md-7 phuyu_card">
						<div class="row" style="padding:0px;margin:0px;">
							<div class="col-md-4 col-xs-12">
								<select class="form-select input-sm" v-model="campos.codcomprobante">
									<option value="0">SIN COMPROBANTE</option>
									<?php 
										foreach ($comprobantes as $key => $value) { ?>
											<option value="<?php echo $value['codcomprobantetipo'];?>"><?php echo $value["descripcion"];?></option>
										<?php }
									?>
								</select>
							</div>
							<div class="col-md-1 col-xs-12">
								<button type="button" class="btn btn-success btn-sm btn-block" v-on:click="phuyu_addcliente()"> 
									<i data-acorn-icon="user"></i>
								</button>
							</div>
							<div class="col-md-4 col-xs-12">
								<select class="form-select input-sm" id="codempleado" v-model="campos.codempleado">
									<option value="0">SELECCIONE MOZO</option>
									<?php 
										foreach ($vendedores as $key => $value) { ?>
											<option value="<?php echo $value['codpersona'];?>"><?php echo $value["razonsocial"];?></option>
										<?php }
									?>
								</select>
							</div>
							<div class="col-md-3 col-xs-12">
								<select class="form-select input-sm" id="tipopedido" v-model="campos.tipopedido" v-on:change="phuyu_tipopedido()">
									<option value="0">PARA SALON</option>
									<option value="1">PARA LLEVAR</option>
									<option value="2">PARA DELIVERY</option>
								</select>
							</div>
						</div>

						<div class="table-responsive detalle" style="height:100px;">
							<table class="table table-striped" style="font-size: 11px;width: 130%">
								<thead>
									<th width="3%"><i class="fa fa-file-o"></i> </th>
									<th width="15%"><i class="fa fa-flag-o"></i>&nbsp;ESTADO</th>
									<th width="25%">PRODUCTO</th>
									<th width="10%">UNIDAD</th>
									<th width="10%">CANTIDAD</th>
									<th width="15%">PRECIO</th>
									<th width="20%">SUBTOTAL</th>
									<th width="5%"> <i class="fa fa-trash-o"></i> </th>
								</thead>
								<tbody>
									<tr v-for="(dato,index) in detalle">
										<td> 
											<button type="button" class="btn btn-warning btn-block btn-xs" style="margin-bottom:-1px;" v-on:click="phuyu_itemdetalle(index,dato)">
												Ver
											</button> 
										</td>
										<td>
											<button type="button" class="btn btn-danger btn-block btn-xs" style="margin-bottom:-1px;" v-if="dato.cantidad!=dato.atendido">
												<b><i class="fa fa-flag-o"></i> PENDIENTE {{dato.cantidad - dato.atendido}}</b>
											</button>
											<button type="button" class="btn btn-success btn-block btn-xs" style="margin-bottom:-1px;" v-if="dato.cantidad==dato.atendido">
												<b><i class="fa fa-flag-o"></i> ATENDIDO {{dato.atendido}}</b>
											</button>
										</td>
										<td>{{dato.producto}}</td>
										<td> <input type="hidden" v-model="dato.codunidad">{{dato.unidad}} </td>
										<td>
											<input type="number" step="0.0001" class="form-control number" v-if="dato.control==1" v-model.number="dato.cantidad" v-on:keyup="phuyu_calcular(dato)" min="0.0001" required>
											<input type="number" step="0.0001" class="form-control number" v-if="dato.control==0" v-model.number="dato.cantidad" v-on:keyup="phuyu_calcular(dato)" min="0.0001" required>
										</td>
										<td>
											<input type="number" step="0.01" class="form-control number" v-model.number="dato.precio" v-on:keyup="phuyu_calcular(dato,3)" min="0" required>
										</td>
										<td>
											<input type="number" step="0.01" class="form-control number" v-model.number="dato.subtotal" readonly>
										</td>
										<td> 
											<button type="button" class="btn btn-danger btn-xs" style="margin-bottom:-1px;" v-on:click="phuyu_deleteitem(index,dato)">
												X 
											</button> 
										</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="text-center">
							<a class="btn btn-success"><b>S/. TOTAL PEDIDO: {{totales.importe}}</b></a> 
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>

	<div id="modal_itemdetalle" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content" align="center">
				<div class="modal-header modal-phuyu-titulo"> 
					<h4 class="modal-title"> <b style="letter-spacing:1px;">DETALLE DEL ITEM DEL PEDIDO</b> </h4> 
					<button type="button" class="btn-close" data-bs-dismiss="modal" style="font-size:30px;margin-bottom:0px;">
					</button>
				</div>
				<div class="modal-body" style="height:380px;">
					<h4 align="center">
						{{item.producto}} <br> <br> <span class="label label-warning">UNIDAD: {{item.unidad}}</span> 
					</h4> <hr>

					<h6>DESCRIPCION DEL ITEM DEL PEDIDO</h6>
					<textarea class="form-control" v-model="item.descripcion" rows="3" maxlength="250"></textarea>
					<div align="center"> <br>
						<button type="button" class="btn btn-success" v-on:click="phuyu_cerrar_itemdetalle()">
							GUARDAR Y CERRAR
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="modal_pago" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-body">
					<form class="x_panel" v-on:submit.prevent="phuyu_pagar()">
			        	<div class="d-grid"><a class="btn btn-success btn-block"> <b style="font-size:25px;">
				    		TOTAL VENTA S/. {{totales.importe}}</b> 
				    	</a></div> <br>

				    	<div class="row form-group">
					    	<div class="col-md-12 col-xs-12">
				    			<label>CLIENTE DE LA VENTA</label>
				    			<select class="form-control" name="codpersona" v-model="campos.codpersona" id="codpersona" required>
				    				<option value="2">CLIENTES VARIOS</option>
				    			</select>
				    		</div>
				    	</div>
			        	<div class="row form-group">
					    	<div class="col-md-5 col-xs-12">
						    	<label>TIPO COMPROBANTE</label>
						    	<select class="form-select" name="codcomprobantetipo" v-model="campos.codcomprobantetipo" required v-on:change="phuyu_series()">
						    		<?php
						    			foreach ($comprobantes as $key => $value) { ?>
						    				<option value="<?php echo $value["codcomprobantetipo"];?>">
						    					<?php echo $value["descripcion"];?>
						    				</option>
						    			<?php }
						    		?>
						    	</select>
						    </div>
						    <div class="col-md-3 col-xs-12">
						    	<label>SERIE</label>
					        	<select class="form-control" id="seriecomprobante" v-model="campos.seriecomprobante" v-on:change="phuyu_correlativo()" required>
						    		<option value="">SERIE</option>
						    		<option v-for="dato in series" v-bind:value="dato.seriecomprobante"> 
						    			{{dato.seriecomprobante}}
						    		</option>
						    	</select>
						    </div>
					    	<div class="col-md-4 col-xs-12">
						    	<label>CONDICION PAGO</label>
						    	<select class="form-select" name="condicionpago" v-model="campos.condicionpago" v-on:change="phuyu_condicionpago()">
						    		<option value="1">CONTADO</option>
						    		<option value="2">CREDITO</option>
						    	</select>
						    </div>
					    </div>
					    <div class="row form-group">
					    	<div class="col-md-12 col-xs-12">
						    	<label>
						    		SELECCIONAR VENDEDOR
						    		<b style="color:#d9534f;padding-left:100px">(COMPROBANTE: {{campos.seriecomprobante}} - {{campos.nro}})</b> 
						    	</label>
						    	<select class="form-select" name="codempleado" v-model="campos.codempleado" required>
						    		<option value="0">SIN VENDEDOR</option>
						    		<?php
						    			foreach ($vendedores as $key => $value) { ?>
						    				<option value="<?php echo $value["codpersona"];?>"> <?php echo $value["razonsocial"];?> </option>
						    			<?php }
						    		?>
						    	</select>
						    </div>
						</div>

					    <div class="row form-group" v-if="campos.condicionpago==2">
					    	<div class="col-md-5 col-xs-12">
						    	<label>NRO DIAS</label>
					        	<input class="form-control" name="nrodias" v-model="campos.nrodias" v-on:keyup="phuyu_cuotas()" required>
						    </div>
						    <div class="col-md-3 col-xs-12">
						    	<label>CUOTAS</label>
						    	<input class="form-control" name="nrocuotas" v-model="campos.nrocuotas" v-on:keyup="phuyu_cuotas()" required>
						    </div>
						    <div class="col-md-4 col-xs-12">
						    	<label>INTERES (%)</label>
						    	<input class="form-control" name="tasainteres" v-model="campos.tasainteres" v-on:keyup="phuyu_cuotas()" required>
						    </div>
					    </div>

					    <div v-if="campos.condicionpago==1">
					    	<h5 align="center"> <b> <i class="fa fa-money"></i> REGISTRAR PAGO DE LA VENTA</b> </h5> 
							<div class="phuyu-linea"></div>
					    	<div class="row form-group">
					    		<div class="col-md-4 col-xs-12" align="center">
				    				<label><i class="fa fa-money" style="font-size:35px;"></i> <br>PAGO CON EFECTIVO</label>
				    			</div>
							    <div class="col-md-4 col-xs-12">
				    				<label>S/. MONTO RECIBIDO</label>
				    				<input type="number" step="0.01" class="form-control number phuyu-money-success" min="0" required v-model="pagos.monto_efectivo" placeholder="S/. 0.00" v-on:keyup="phuyu_vuelto()">
				    			</div>
					    		<div class="col-md-4 col-xs-12">
				    				<label>VUELTO</label>
				    				<input type="number" step="0.01" class="form-control phuyu-money-error" readonly v-model="pagos.vuelto_efectivo">
				    			</div>
				    		</div>
				    		
							<div class="phuyu-linea"></div>
				    		<div class="row form-group">
				    			<div class="col-md-4 col-xs-12">
				    				<label> <i class="fa fa-money"></i> TARJETA O CHEQUE</label>
						        	<select class="form-select" v-model="pagos.codtipopago_tarjeta" v-on:change="phuyu_pagotarjeta()" required>
						        		<option value="0">SIN TARJETA</option>
							    		<?php 
							    			foreach ($tipopagos as $key => $value) { 
							    				if ($value["codtipopago"]!=1) { ?>
							    					<option value="<?php echo $value["codtipopago"];?>">
								    					<?php echo $value["descripcion"];?>
								    				</option>
							    				<?php } 
							    			}
							    		?>
							    	</select>
				    			</div>
				    			<div class="col-md-4 col-xs-12">
				    				<label>S/. MONTO</label>
				    				<input type="number" step="0.01" class="form-control number phuyu-money-success" min="0.01" id="monto_tarjeta" v-model="pagos.monto_tarjeta" placeholder="S/. 0.00" readonly>
				    			</div>
				    			<div class="col-md-4 col-xs-12">
							    	<label>NRO VOUCHER</label>
						        	<input type="text" class="form-control phuyu-money-default" id="nrovoucher" v-model.trim="pagos.nrovoucher" autocomplete="off" readonly>
							    </div>
				    		</div>
			    		</div>

					    <div v-if="campos.condicionpago==2">
					    	<div class="table-responsive" style="height:90px;">
					    		<table class="table table-bordered">
					    			<thead>
					    				<tr>
					    					<th>FECHA VENCE</th>
					    					<th>IMPORTE</th>
					    					<th>INTERES</th>
					    					<th>TOTAL</th>
					    				</tr>
					    			</thead>
					    			<tbody>
					    				<tr v-for="dato in cuotas">
					    					<td>{{dato.fechavence}}</td>
					    					<td>{{dato.importe}}</td>
					    					<td>{{dato.interes}}</td>
					    					<td>{{dato.total}}</td>
					    				</tr>
					    			</tbody>
					    		</table>
					    	</div>

					    	<div style="border-bottom:2px solid #13a89e;padding-bottom:10px;" align="center">
								<button type="button" class="btn btn-warning btn-sm"> <b>INTERES: S/. {{totales.interes}}</b></button>
								<button type="button" class="btn btn-danger btn-sm"> <b>TOTAL CREDITO: S/. {{campos.totalcredito}}</b> </button>
							</div>
				    	</div>
			            
					    <div class="row form-group" align="center"> <br>
							<div class="col-md-12">
								<button type="submit" class="btn btn-success btn-lg" v-bind:disabled="estado==1"> 
									<b>GUARDAR VENTA</b>
								</button>
								<button type="button" class="btn btn-danger btn-lg" data-dismiss="modal"> <b>CANCELAR</b> </button>
							</div>
						</div>
			        </form>
				</div>
			</div>
		</div>
	</div>

	<div id="modal_atender" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header" style="padding:10px 15px 5px">
					<h4 class="modal-title">
						<b style="letter-spacing:3px;">PEDIDO N°: 0000{{campos.codpedido}} | MESA {{campos.mesa}}</b>
					</h4>
					<button type="button" class="btn-close" data-bs-dismiss="modal" style="font-size:30px;margin-bottom:0px;">
					</button>
				</div>
				<div class="modal-body">
					<div class="x_panel scroll-phuyu" style="height:220px;overflow:auto;overflow-x:hidden;padding:0px;"> 
						<table class="table table-bordered" style="font-size: 11px">
							<thead>
								<tr>
									<th>DESCRIPCION</th>
									<th width="10px">UNIDAD</th>
									<th width="10px">CANTIDAD</th>
									<th width="10px">ATENDIDO</th>
									<th width="10px">ATENDER</th>
									<th width="10px" colspan="2">AGREGAR</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="(dato,index) in atender">
									<td>{{dato.producto}} - {{dato.descripcion}}</td>
									<td>{{dato.unidad}}</td>
									<td>{{dato.cantidad}}</td>
									<td>{{dato.atendido}}</td>
									<td>
										<input type="number" step="0.1" class="form-control number line-success" v-model.number="dato.atender" min="0" max="dato.cantidad" readonly>
									</td>
									<td v-if="dato.cantidad!=dato.atendido">
										<button class="btn btn-info btn-xs btn-block" style="margin-bottom:-1px;" v-on:click="phuyu_mas_menos(dato,1)">
											+
										</button>
									</td>
									<td v-if="dato.cantidad!=dato.atendido">
										<button class="btn btn-warning btn-xs btn-block" style="margin-bottom:-1px;" v-on:click="phuyu_mas_menos(dato,2)">
											-
										</button>
									</td>
									<td v-if="dato.cantidad==dato.atendido" colspan="2">
										<button type="button" class="btn btn-danger btn-xs btn-block" style="margin-bottom:-1px;">ATENDIDO</button>
									</td>
								</tr>
							</tbody>
							<tfoot>
								<tr v-for="dato in totales">
									<td colspan="2" align="right"><b>TOTALES</b></td>
									<td><b>{{dato.cantidad}}</b></td>
									<td><b>{{dato.atendido}}</b></td>
									<td colspan="3">
										<button type="button" class="btn btn-success btn-block btn-sm" style="margin-bottom:-1px;" v-on:click="phuyu_atender()" v-bind:disabled="estado==1">GUARDAR ATENCION</button>
									</td>
								</tr>
							</tfoot>
						</table>
					</div>
					<h5 class="text-center"> <b>DETALLE DE LAS ATENCIONES DEL PEDIDO</b> </h5>

					<div class="x_panel scroll-phuyu" style="height:200px;overflow:auto;overflow-x:hidden;padding:0px;"> 
						<table class="table table-bordered">
							<thead>
								<tr>
									<th>DESCRIPCION</th>
									<th width="10px">UNIDAD</th>
									<th width="10px">CANTIDAD</th>
									<th width="140px">FECHA Y HORA</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="dato in atendidos">
									<td><b>{{dato.producto}} - {{dato.descripcion}}</b></td>
									<td>{{dato.unidad}}</td>
									<td>{{dato.cantidad}}</td>
									<td><b style="color:#d43f3a">{{dato.fecha}} {{dato.hora}}</b></td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>

		</div>
	</div>

	<div id="modal_reportes" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog" style="width:100%;margin:0px;">
			<div class="modal-content" align="center" style="border-radius:0px">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" style="font-size:30px;margin-bottom:0px;">
						<i class="fa fa-times-circle"></i> 
					</button>
					<h4 class="modal-title">
						<b style="letter-spacing:4px;"><?php echo $_SESSION["phuyu_empresa"]." - ".$_SESSION["phuyu_sucursal"];?> </b>
					</h4>
				</div>
				<div class="modal-body" id="reportes_modal" style="height:450px;padding:0px;">
					<iframe id="phuyu_pdf" src="" style="width:100%; height:100%; border:none;"> </iframe>
				</div>
			</div>
		</div>
	</div>

	<div id="modal_empleados" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" style="font-size:30px;margin-bottom:0px;">
						<i class="fa fa-times-circle"></i> 
					</button>
					<h4 class="modal-title">REPORTE DE ANFITRIONAS</h4>
				</div>
				<div class="modal-body" id="modal_empleados_contenido">

				</div>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_restaurante/atender.js"> </script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas_2.js"> </script>
<script>
	var pantalla = jQuery(document).height(); $("#reportes_modal").css({height: pantalla - 65});
	$(".detalle").css("height",pantalla - 470);
</script>