<style type="text/css">
	.totales{
		text-align: right;
		font-weight: 700;
		background: #fff !important;
	}
	.totaltotal{
		text-align: right;
		font-weight: 700;
		color: red;
		background: #fff !important;
		font-size: 16px
	}
</style>
<div id="phuyu_operacion">

	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" id="comprobante" value="<?php echo $comprobantes[0]['codcomprobantetipo'];?>">
		<input type="hidden" id="serie" value="<?php echo $comprobantes[0]['seriecomprobante'];?>">
		<input type="hidden" id="comprobantereferencia" value="<?php echo $sucursalreferencia[0]['codcomprobantetipo'];?>">
		<input type="hidden" id="seriereferencia" value="<?php echo $sucursalreferencia[0]['seriecomprobante'];?>">
		<input type="hidden" id="stockalmacen" value="<?php echo $_SESSION["phuyu_stockalmacen"];?>">
		<input type="hidden" id="itemrepetir" value="<?php echo $_SESSION["phuyu_itemrepetir"];?>">
		<input type="hidden" id="igvsunat" value="<?php echo $_SESSION["phuyu_igv"];?>">
		<input type="hidden" id="icbpersunat" value="<?php echo $_SESSION["phuyu_icbper"];?>">
		<input type="hidden" id="formato" value="<?php echo $_SESSION['phuyu_formatopedido'];?>">
		<input type="hidden" id="seriecomprobantereferencia" v-model="campos.seriecomprobantereferencia">
		<input type="hidden" id="empleado" value="<?php echo $_SESSION["phuyu_codpersona"]; ?>" name="">

		<div class="phuyu_body">
			<div class="card">
				<div class="card-header" style="padding:1rem 2rem 1rem !important">
					<div class="row">
						<div class="col-md-4 col-xs-12"> <h5>REGISTRO NUEVO PEDIDO</h5> </div>
					</div>
				</div>
				<?php 
					$disabled = '';
					if($_SESSION["phuyu_codperfil"]>3){
						$disabled = 'disabled';
					}
				?>
				<div class="card-body">
					<div class="row mb-2">
				    	<div class="col-md-2 col-xs-12">
					    	<label>TIPO COMPROBANTE</label>
					    	<select class="form-control" name="codcomprobantetipo" v-model="campos.codcomprobantetipo" required v-on:change="phuyu_series()">
					    		<?php
					    			foreach ($comprobantes as $key => $value) { ?>
					    				<option value="<?php echo $value["codcomprobantetipo"];?>">
					    					<?php echo $value["descripcion"];?>
					    				</option>
					    			<?php }
					    		?>
					    	</select>
					    </div>
					    <div class="col-md-1 col-xs-12">
					    	<label>SERIE </label>
				        	<select class="form-control" id="seriecomprobante" v-model="campos.seriecomprobante" v-on:change="phuyu_correlativo()" required>
					    		<option value="">SERIE</option>
					    		<option v-for="dato in series" v-bind:value="dato.seriecomprobante"> 
					    			{{dato.seriecomprobante}}
					    		</option>
					    	</select>
					    </div>
						<div class="col-md-2 col-xs-12">
							<label>FECHA PEDIDO</label>
			    			<input type="date" class="form-control" name="fechacomprobante" id="fechacomprobante" value="<?php echo date('Y-m-d');?>" autocomplete="off" required>
						</div>
						<div class="col-md-2 col-xs-12">
							<label>FECHA ENTREGA</label>
			    			<input type="date" class="form-control" name="fechaentrega" id="fechaentrega" value="<?php echo date('Y-m-d');?>" autocomplete="off" required>
			    			<input type="hidden" class="form-control" name="fechakardex" id="fechakardex" value="<?php echo date('Y-m-d');?>" autocomplete="off">
						</div>
				    	<div class="col-md-2 col-xs-12">
					    	<label>CONDICION PAGO</label>
					    	<select class="form-control" name="condicionpago" v-model="campos.condicionpago" v-on:change="phuyu_condicionpago()">
					    		<option value="1">CONTADO</option>
					    		<option value="2">CREDITO</option>
					    	</select>
					    </div>
					    <div class="col-md-3 col-xs-12">
					    	<label>SELECCIONAR VENDEDOR</label>
					    	<select class="form-control" id="codempleado" name="codempleado" <?php echo $disabled;?> v-model="campos.codempleado" required>
					    		<option value="0">SIN VENDEDOR</option>
					    		<?php
					    			foreach ($vendedores as $key => $value) {
					    			?>
					    				<option value="<?php echo $value["codpersona"];?>" > <?php echo $value["razonsocial"];?> </option>
					    			<?php }
					    		?>
					    	</select>
					    </div>
				    </div>
					<div class="row mb-2">
						<div class="col-md-3 col-xs-10">
							<label>SELECCIONAR CLIENTE</label>
			    			<select class="form-control selectpicker ajax" name="codpersona" v-model="campos.codpersona" id="codpersona" required data-live-search="true" v-on:change="phuyu_infocliente()">
			    				<option value="2">CLIENTES VARIOS</option>
			    			</select>
						</div>
						<div class="col-md-1 col-xs-2">
							<label>&nbsp;</label>
							<button type="button" class="btn btn-success btn-block" v-on:click="phuyu_addcliente()" title="AGREGAR CLIENTE"> 
								<i class="fa fa-user-plus"></i>
							</button>
						</div>
						<?php 
							if ($_SESSION["phuyu_rubro"]==1) { ?>
								<div class="col-md-4 col-xs-10">
									<label>NRO DE PLACA</label>
									<input type="text" class="form-control" v-model="campos.nroplaca" autocomplete="off" maxlength="100" placeholder="Nro placa . . .">
								</div>
							<?php }else{ ?>
								<div class="col-md-5 col-xs-10" style="display: none">
									<label>GLOSA DEL PEDIDO</label>
									<input type="text" class="form-control" v-model="campos.descripcion" autocomplete="off" maxlength="250" placeholder="Referencia del pedido . . .">
								</div>
							<?php }
						?>
						<div class="col-md-2 col-xs-12">
							<label>CLIENTE DEL PEDIDO</label>
							<input type="text" class="form-control" id="cliente" v-model.trim="campos.cliente" autocomplete="off" maxlength="250" placeholder="Razon social del cliente . . ." required>
						</div>
						<div class="col-md-3 col-xs-12">
							<label>DIRECCION CLIENTE</label>
							<input type="text" class="form-control" id="direccion" v-model.trim="campos.direccion" autocomplete="off" maxlength="250" placeholder="Direccion del cliente . . ." required>
						</div>
						<div class="col-md-3 col-xs-12">
							<label>DIRECCION ENTREGA</label>
							<input type="text" class="form-control" id="direccionentrega" v-model.trim="campos.direccionentrega" autocomplete="off" maxlength="250" placeholder="Direccion de entrega . . ." required>
						</div>
					</div><br>
					<div class="row form-group table-responsive scroll-phuyu-view">
						<table class="table table-bordered table-striped">
							<thead>
								<tr>
									<th width="7%" class="phuyu-item-mas" v-on:click="phuyu_item()"> <i class="fa fa-plus-square"></i> ITEM </th>
									<th width="30%">PRODUCTO</th>
									<th width="10%">UNIDAD</th>
									<th width="8%">STOCK</th>
									<th width="8%">CANTIDAD</th>
									<th width="10%">PRECIO UNIT.</th>
									<th width="8%">I.G.V.</th>
									<th width="10%">ICBPER</th>
									<th width="10%">SUBTOTAL</th>
									<th width="1%"> <i class="fa fa-trash-o"></i> </th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="(dato,index) in detalle">
									<td class="phuyu-item-mas" v-on:click="phuyu_itemdetalle(index,dato)"> <i class="fa fa-plus-circle"></i> MAS </td>
									<td style="font-size:10px;">{{dato.producto}}</td>
									<td>
										<select class="phuyu-input unidad" v-model="dato.codunidad" v-on:change="informacion_unidad(index,dato,this.value)" id="codunidad">
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
									<td style="color:red;font-weight:bold">{{dato.stock}} </td>
									<td>
										<input type="number" step="0.0001" class="phuyu-input number" v-if="dato.control==1" v-model.number="dato.cantidad" v-on:keyup="phuyu_calcular(dato)" min="0.0001" v-bind:max="dato.stock" required>
										<input type="number" step="0.0001" class="phuyu-input number" v-if="dato.control==0" v-model.number="dato.cantidad" v-on:keyup="phuyu_calcular(dato)" min="0.0001" required>
									</td>
									<td>
										<input type="number" step="0.0001" class="phuyu-input number" v-if="dato.codafectacionigv==21" v-model.number="dato.precio" min="0" readonly>
										<input type="number" step="0.0001" class="phuyu-input number" v-if="dato.codafectacionigv!=21" v-model.number="dato.precio" v-on:keyup="phuyu_calcular(dato)" min="0.001" required  v-bind:disabled="dato.porcdescuento==100">
									</td>
									<td> <input type="number" class="phuyu-input number" v-model.number="dato.igv" min="0" readonly> </td>
									<td> <input type="number" class="phuyu-input number" v-model.number="dato.icbper" min="0" readonly> </td>
									<td v-if="dato.codafectacionigv==21">
										<input type="number" step="0.01" class="phuyu-input number" v-model.number="dato.subtotal">
									</td>
									<td v-if="dato.codafectacionigv!=21">
										<input type="number" step="0.01" class="phuyu-input number" v-if="dato.calcular==0" v-model.number="dato.subtotal" readonly>
										<input type="number" step="0.01" class="phuyu-input number" v-if="dato.calcular!=0" v-model.number="dato.subtotal" v-on:keyup="phuyu_subtotal(dato)" required v-bind:disabled="dato.porcdescuento==100">
									</td>
									<td> 
										<button type="button" class="btn btn-danger btn-block btn-xs" style="margin-bottom:-1px;" v-on:click="phuyu_deleteitem(index,dato)">
											<i class="fa fa-trash-o"></i> 
										</button>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="row form-group">
						<div class="text-center">
							<div class="col-md-2">
								<div class="input-group">
								    <div class="input-group-prepend">
								      <div class="input-group-text" id="btnGroupAddon"><b>IMPORTE S/.</b></div>
								    </div>
								    <input type="text" class="form-control totales" v-bind:value="totales.bruto" readonly>
								</div>
							</div>
							<div class="col-md-2">
								<div class="input-group">
								    <div class="input-group-prepend">
								      <div class="input-group-text" id="btnGroupAddon"><b>DESC: S/.</b></div>
								    </div>
								    <input type="text" class="form-control totales" v-bind:value="totales.descuentos" readonly>
								</div>
							</div>
							<div class="col-md-2">
								<div class="input-group">
								    <div class="input-group-prepend">
								      <div class="input-group-text" id="btnGroupAddon"><b>GRAV.: S/.</b></div>
								    </div>
								    <input type="text" class="form-control totales" v-bind:value="operaciones.gravadas" readonly>
								</div>
							</div>
							<div class="col-md-2">
								<div class="input-group">
								    <div class="input-group-prepend">
								      <div class="input-group-text" id="btnGroupAddon"><b>EXON.: S/.</b></div>
								    </div>
								    <input type="text" class="form-control totales" v-bind:value="operaciones.exoneradas" readonly>
								</div>
							</div>
							<div class="col-md-2">
								<div class="input-group">
								    <div class="input-group-prepend">
								      <div class="input-group-text" id="btnGroupAddon"><b>INAF.: S/.</b></div>
								    </div>
								    <input type="text" class="form-control totales" v-bind:value="operaciones.inafectas" readonly>
								</div>
							</div>
							<div class="col-md-2">
								<div class="input-group">
								    <div class="input-group-prepend">
								      <div class="input-group-text" id="btnGroupAddon"><b>GRAT.: S/.</b></div>
								    </div>
								    <input type="text" class="form-control totales" v-bind:value="operaciones.gratuitas" readonly>
								</div>
							</div>
						</div>
					</div>
					<div class="row form-group">
						<div class="text-center">
							<div class="col-md-2"></div>
							<div class="col-md-2">
								<div class="input-group">
								    <div class="input-group-prepend">
								      <div class="input-group-text" id="btnGroupAddon"><b>IGV: S/.</b></div>
								    </div>
								    <input type="text" class="form-control totales" v-bind:value="totales.igv" readonly>
								</div>
							</div>
							<div class="col-md-2">
								<div class="input-group">
								    <div class="input-group-prepend">
								      <div class="input-group-text" id="btnGroupAddon"><b>ISC: S/.</b></div>
								    </div>
								    <input type="text" class="form-control totales" v-bind:value="totales.isc" readonly>
								</div>
							</div>
							<div class="col-md-2">
								<div class="input-group">
								    <div class="input-group-prepend">
								      <div class="input-group-text" id="btnGroupAddon"><b>ICBPER: S/.</b></div>
								    </div>
								    <input type="text" class="form-control totales" v-bind:value="totales.icbper" readonly>
								</div>
							</div>
							<div class="col-md-2">
								<div class="input-group">
								    <div class="input-group-prepend">
								      <div class="input-group-text" id="btnGroupAddon"><b style="font-size: 14px">TOTAL S/.</b></div>
								    </div>
								    <input type="text" class="form-control totaltotal" v-bind:value="totales.importe" readonly>
								</div>
							</div>
							
						</div>
					</div>
					<div class="row">
						<div class="col-md-4 col-xs-6">
							<button type="button" class="btn btn-warning btn-block" v-on:click="phuyu_venta()"> 
								<b> <i class="fa fa-plus-square"></i> NUEVO PEDIDO</b> 
							</button>
						</div>
						<div class="col-md-4 col-xs-6">
							<button type="submit" class="btn btn-info btn-block" v-bind:disabled="estado==1"> 
								<b><i class="fa fa-save"></i> GUARDAR PEDIDO</b> 
							</button>
						</div>
						<div class="col-md-4 col-xs-12">
							<button type="button" class="btn btn-danger btn-block" v-on:click="phuyu_atras()"> 
								<b> <i class="fa fa-arrow-left"></i> ATRAS</b> 
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
	<div id="modal_cuotas" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" style="font-size:30px;margin-bottom:0px;">
						<i class="fa fa-times-circle"></i> 
					</button>
					<h4 class="modal-title"> <b style="letter-spacing:1px;">GENERAR CUOTAS DE PAGO AL CREDITO</b> </h4> 
				</div>
				<div class="modal-body">
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
					    	<label>TASA INTERES (%)</label>
					    	<input class="form-control" name="tasainteres" v-model="campos.tasainteres" v-on:keyup="phuyu_cuotas()" required>
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
			    	</div><br>
			    	<div class="text-center">
						<button type="button" class="btn btn-success" v-on:click="phuyu_itemcalcular_cerrar(item)">
							<i class="fa fa-save"></i> GUARDAR PEDIDO
						</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">
							<i class="fa fa-times-circle"></i> VOLVER AL FORMULARIO
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="modal_masconfiguraciones" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content" align="center">
				<div class="modal-header"> 
					<button type="button" class="close" data-dismiss="modal" style="font-size:30px;margin-bottom:0px;">
						<i class="fa fa-times-circle"></i> 
					</button>
					<h4 class="modal-title"> <b style="letter-spacing:1px;">DETALLE DEL ITEM DE LA VENTA</b> </h4> 
				</div>
				<div class="modal-body" style="height: 380px;">
					<div class="row form-group">
						<div class="col-md-4 col-xs-12" align="center">
							<label>RECOGIDO</label>
							<input type="checkbox" style="height:20px;width:20px;" v-model="campos.retirar" disabled="true"> 
						</div>
					</div>
					<div class="row form-group">
						<div class="col-md-4 col-xs-12">
							<label>CENTRO COSTO</label>
							<select class="form-control" v-model="campos.codcentrocosto">
								<option value="0">SIN CENTRO COSTO</option>
								<?php 
									foreach ($centrocostos as $key => $value) { ?>
										<option value="<?php echo $value["codcentrocosto"];?>"><?php echo $value["descripcion"];?></option>
									<?php }
								?>
							</select>
						</div>
						<div class="col-md-2 col-xs-12" v-if="rubro==1">
							<label>NRO PLACA</label>
							<input type="text" class="form-control" v-model="campos.nroplaca" autocomplete="off" maxlength="50" placeholder="Nro placa . . .">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="modal_itemdetalle" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header modal-phuyu-titulo">
					<h4 class="modal-title"> <b style="letter-spacing:0.5px;">DETALLE DEL ITEM DE LA VENTA</b> </h4> 
				</div>
				<div class="modal-body">
					<h5> <b>
						PRODUCTO: {{item.producto}} &nbsp; <span class="label label-warning">CANTIDAD: {{item.cantidad}} {{item.unidad}}</span>
					</b> </h5> <hr>

					<div class="row form-group">
				    	<div class="col-md-4 col-xs-12">
					    	<label>PRECIO BRUTO</label>
					    	<input type="number" class="phuyu-input number" v-model.number="item.preciobruto" v-on:keyup="phuyu_itemcalcular(item,0)" v-bind:disabled="item.codafectacionigv==21">
					    </div>
					    <div class="col-md-4 col-xs-12">
					    	<label>DESCUENTO PRECIO (S/.)</label>
					    	<input type="number" class="phuyu-input number" v-model.number="item.descuento" v-on:keyup="phuyu_itemcalcular(item,-1)" v-bind:disabled="item.codafectacionigv==21">
					    </div>
					    <div class="col-md-4 col-xs-12">
					    	<label>DESCUENTO PRECIO (%)</label>
					    	<input type="number" class="phuyu-input number" v-model.number="item.porcdescuento" v-on:keyup="phuyu_itemcalcular(item,-2)" v-bind:disabled="item.codafectacionigv==21">
					    </div>
					</div>
					<div class="row form-group">
				    	<div class="col-md-3 col-xs-12">
					    	<label>PRECIO SIN I.G.V.</label>
					    	<input type="number" class="phuyu-input number" v-model.number="item.preciosinigv" v-on:keyup="phuyu_itemcalcular(item,1)" v-bind:disabled="item.codafectacionigv==21">
					    </div>
					    <div class="col-md-3 col-xs-12">
					    	<label>PRECIO UNITARIO</label>
					    	<input type="number" class="phuyu-input number" v-model.number="item.precio" v-on:keyup="phuyu_itemcalcular(item,2)" v-bind:disabled="item.codafectacionigv==21">
					    </div>
					    <div class="col-md-3 col-xs-12">
					    	<label>TIPO AFECTACION</label>
					    	<select class="phuyu-input" v-model="item.codafectacionigv" v-on:change="phuyu_itemcalcular(item,2)">
					    		<option value="10">GRAVADO</option> 
					    		<option value="20">EXONERADO</option> 
					    		<option value="21">GRATUITO</option> 
					    		<option value="30">INAFECTO</option>
					    	</select>
					    </div>
					    <div class="col-md-3 col-xs-12">
					    	<label>ICBPER</label>
					    	<select class="phuyu-input" v-model="item.conicbper" v-on:change="phuyu_itemcalcular(item,2)">
					    		<option value="1">SI</option>
					    		<option value="0">NO</option>
					    	</select>
					    </div>
					</div>
					<div class="form-group text-center">
						<button type="button" class="btn btn-info btn-sm"> <b>VALOR VENTA: S/. {{item.valorventa}}</b> </button>
						<button type="button" class="btn btn-danger btn-sm"> <b>IGV: S/. {{item.igv}}</b></button>
						<button type="button" class="btn btn-danger btn-sm"> <b>ICBPER: S/. {{item.icbper}}</b> </button>
						<button type="button" class="btn btn-success btn-sm"> <b>SUBTOTAL: S/. {{item.subtotal}}</b> </button>
					</div>
					<div class="form-group">
						<label>DESCRIPCION DEL ITEM DE VENTA</label>
						<textarea class="form-control" v-model="item.descripcion" rows="3" maxlength="250"></textarea>
					</div>

					<div class="text-center">
						<button type="button" class="btn btn-success" v-on:click="phuyu_itemcalcular_cerrar(item)">
							<i class="fa fa-save"></i> GUARDAR CAMBIOS DEL ITEM Y <i class="fa fa-times-circle"></i> CERRAR
						</button>
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
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_pedidos/nuevo.js"> </script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas_2.js"> </script>

<script>
	var pantalla = jQuery(document).height(); $("#reportes_modal").css({height: pantalla - 65});
	$(".datepicker").datetimepicker({format: 'YYYY-MM-DD',ignoreReadonly: true}).attr("readonly","true");
</script>