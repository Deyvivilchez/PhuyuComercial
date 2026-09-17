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
	.rojo{
		border: 3px solid red !important;
	}
</style>
<div id="phuyu_operacion">
	
	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" id="itemrepetir" value="<?php echo $_SESSION["phuyu_itemrepetir"];?>">
		<input type="hidden" id="igvsunat" value="<?php echo $_SESSION["phuyu_igv"];?>">
		<input type="hidden" id="icbpersunat" value="<?php echo $_SESSION["phuyu_icbper"];?>">
        <input type="hidden" id="rubro" value="<?php echo $_SESSION["phuyu_rubro"];?>" name="">
        <input type="hidden" id="afectacionigv" value="<?php echo $_SESSION["phuyu_afectacionigv"];?>" name="">

		<div class="phuyu_body">
			<div class="card">
				<div class="card-body">
					<div class="row">
						<div class="col-md-4 col-xs-12 text-danger"> <h5><strong>{{titulo}}</strong></h5> </div>
					</div>
					<div class="row">
						<div class="col-md-4 col-xs-12">
							<label>PROVEEDOR DE LA COMPRA</label>
			    			<select class="form-control selectpicker ajax" name="codpersona" v-model="campos.codpersona" id="codpersona" required data-live-search="true">
			    				<option value="2">PROVEEDORES VARIOS</option>
			    			</select>
						</div>
						<div class="col-md-1 col-xs-2">
							<label>&nbsp;</label>
							<button type="button" class="btn btn-success btn-block" v-on:click="phuyu_addproveedor()" title="AGREGAR PROVEEDOR"> 
								<i class="fa fa-user-plus"></i>
							</button>
						</div>
						<div class="col-md-2 col-xs-6">
							<label>FECHA COMPRA</label>
			    			<input type="text" class="form-control datepicker" name="fechacomprobante" id="fechacomprobante" autocomplete="off" v-on:blur="phuyu_tipocambio()" required value="<?php echo date('Y-m-d');?>">
						</div>
						<div class="col-md-2 col-xs-6">
							<label>FECHA KARDEX</label>
							<input type="text" class="form-control datepicker" name="fechakardex" id="fechakardex" autocomplete="off" required value="<?php echo date('Y-m-d');?>">
						</div>
						<div class="col-md-2 col-xs-6">
							<label>MONEDA</label>
			    			<select class="form-control" name="codmoneda" v-model="campos.codmoneda" v-on:change="phuyu_tipocambio()" required>
			    				<?php 
			    					foreach ($monedas as $key => $value) {?>
			    						<option value="<?php echo $value["codmoneda"];?>"><?php echo $value["simbolo"]." ".$value["descripcion"];?></option>
			    					<?php }
			    				?>
			    			</select>
						</div>
						<div class="col-md-1 col-xs-6">
							<label>CAMBIO</label>
			    			<input type="number" step="0.001" class="form-control number" name="tipocambio" v-model.number="campos.tipocambio" autocomplete="off" min="1" v-bind:disabled="campos.codmoneda==1" required>
						</div>
					</div>

					<div class="row">
						<div class="col-md-3 col-xs-12">
					    	<label>TIPO COMPROBANTE</label>
					    	<select class="form-control" name="codcomprobantetipo" v-model="campos.codcomprobantetipo" v-on:change="phuyu_series()" required>
					    		<option value="">SELECCIONE</option>
					    		<?php
					    			foreach ($comprobantes as $key => $value) { ?>
					    				<option value="<?php echo $value["codcomprobantetipo"];?>">
					    					<?php echo $value["descripcion"];?>
					    				</option>
					    			<?php }
					    		?>
					    	</select>
					    </div>
					    <div class="col-md-1 col-xs-12 serie_liq" style="display: none">
					    	<label>SERIE</label>
				        	<select class="form-control" id="seriecomprobanteliq" v-model="campos.seriecomprobanteliq" v-on:change="phuyu_correlativo()">
					    		<option v-for="dato in series" v-bind:value="dato.seriecomprobante"> 
					    			{{dato.seriecomprobante}}
					    		</option>
					    	</select>
					    </div>
					    <div class="col-md-1 col-xs-12 serie_ot">
					    	<label>SERIE</label>
				        	<input class="form-control" id="seriecomprobante" name="seriecomprobante" v-model.trim="campos.seriecomprobante" maxlength="4" required autocomplete="off">
					    </div>
					    <div class="col-md-2 col-xs-12">
					    	<label>NRO COMPROBANTE</label>
				        	<input class="form-control" name="nro" id="nro" v-model.trim="campos.nro" maxlength="8" required autocomplete="off">
				    	</div>
				    	<div class="col-md-1" align="center">
							<label style="font-size:10px;">AFECTACAJA</label> <br>
							<input type="checkbox" style="height:20px;width:20px;" v-model="campos.afectacaja"> 
						</div>
						<div class="col-md-1" align="center">
							<label style="font-size:10px;">RECEPCION</label> <br>
							<input type="checkbox" style="height:20px;width:20px;" v-model="campos.retirar"> 
						</div>
						<div class="col-md-1" align="center">
							<label style="font-size:10px;">CON IGV</label> <br>
							<input type="checkbox" style="height:20px;width:20px;" v-model="igv" v-on:change="phuyu_igv()"> 
						</div>
				    	<div class="col-md-3">
							<label>GLOSA DE LA COMPRA</label>
							<input type="text" class="form-control" v-model="campos.descripcion" autocomplete="off" maxlength="250" placeholder="Referencia de la compra . . .">
						</div>
					</div><br>
					<div class="row form-group table-responsive scroll-phuyu-view" style="height:calc(100vh - 465px);padding:0px; overflow:auto;">
						<table class="table table-bordered table-striped">
							<thead>
								<tr>
									<th width="7%" class="phuyu-item-mas" v-on:click="phuyu_item()"> <i class="fa fa-plus-square"></i> ITEM </th>
									<th width="25%">PRODUCTO</th>
									<th width="9%">UNIDAD</th>
									<th width="8%">CANTIDAD</th>
									<th width="9%">PRECIO</th>
									<th width="9%">SUBTOTAL</th>
									<th width="9%">I.G.V.</th>
									<th width="9%">FLETE</th>
									<th width="9%">ICBPER</th>
									<th width="10%">TOTAL</th>
									<th width="1%"> <i class="fa fa-trash-o"></i> </th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="(dato,index) in detalle">
									<td class="phuyu-item-mas" v-on:click="phuyu_itemdetalle(index,dato)"> <i class="fa fa-plus-circle"></i> MAS </td>
									<td style="font-size:10px;">{{dato.producto}}</td>
									<td>
										<select class="phuyu-input unidad" v-model="dato.codunidad" id="codunidad">
											<template v-for="(unidad, und) in dato.unidades">
												<option v-bind:value="unidad.codunidad" v-if="unidad.factor==1" selected>
													{{unidad.descripcion}}
												</option>
												<option v-bind:value="unidad.codunidad" v-if="unidad.factor!=1">
													{{unidad.descripcion}}
												</option>
											</template>
										</select>
									</td>
									<td>
										<input type="number" step="0.0001" class="phuyu-input number" v-model.number="dato.cantidad" v-on:keyup="phuyu_calcular(dato)" min="0.001" required>
									</td>
									<td>
										<input type="number" step="0.0001" class="phuyu-input number" v-if="dato.codafectacionigv==21" v-model.number="dato.preciosinigv" min="0" readonly>
										<input type="number" step="0.0001" class="phuyu-input number" v-if="dato.codafectacionigv!=21" v-model.number="dato.preciosinigv" v-on:keyup="phuyu_calcular(dato)" min="0.001" required  v-bind:disabled="dato.porcdescuento==100">
									</td>
									<td> <input type="number" class="phuyu-input number" v-model.number="dato.valorventa" readonly> </td>
									<td> <input type="number" class="phuyu-input number" v-model.number="dato.igv" min="0" readonly> </td>
									<td> <input type="number" class="phuyu-input number" v-model.number="dato.flete" min="0" readonly> </td>
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
							<div class="col-md-3">
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
							<button type="button" class="btn btn-warning btn-block" v-on:click="phuyu_compra()"> 
								<b> <i class="fa fa-plus-square"></i> NUEVA COMPRA</b> 
							</button>
						</div>
						<div class="col-md-4 col-xs-6">
							<button type="submit" class="btn btn-success btn-block" v-bind:disabled="estado==1"> 
								<b><i class="fa fa-save"></i> GUARDAR COMPRA</b> 
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

	<div id="modal_finventa" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content" style="margin-top:-3rem">
				<div class="modal-header">
					<h5 class="modal-title">
						<b style="font-size:25px;">TOTAL COMPRA S/. {{totales.importe}}</b>
					</h5>
				</div>
				<div class="modal-body" id="cuerpotermino">
					<form v-on:submit.prevent="phuyu_pagar()" class="formulario">
					    <div class="row form-group">

						    <div class="col-md-3 col-xs-12">
						    	<label>FLETE</label>
						    	<input type="number" step="0.01" class="form-control number" name="flete" v-model.number="totales.flete" autocomplete="off" min="0" required v-on:keyup="phuyu_totales()" readonly>
						    </div>
						    <div class="col-md-5 col-xs-12">
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
					    	<div class="col-md-4 col-xs-12">
						    	<label>CONDICION PAGO</label>
						    	<select class="form-control" name="condicionpago" v-model="campos.condicionpago" v-on:change="phuyu_condicionpago()">
						    		<option value="1">CONTADO</option>
						    		<option value="2">CREDITO</option>
						    	</select>
						    </div>
					    </div>
					    <div class="row form-group" v-show="campos.condicionpago==2 && rubro==6">
                        	<div class="col-xs-7">
	                        	<label>LINEA DE CREDITO DEL CLIENTE</label>
	                        	<select class="form-control" name="codlote" v-model="campos.codlote" id="codlote">
	                        		
					            </select>
					        </div>
					        <div class="col-xs-5">
					        	<label></label>
					        	<button type="button" class="btn btn-success btn-block" v-on:click="phuyu_lineascreditodirecto()"><i class="fa fa-plus"></i> NUEVA LINEA DE CREDITO</button>
					        </div>
                        </div>
					    <div class="row">
						    <div v-if="campos.condicionpago==1">
								<div class="col-md-4 col-xs-12">
							    	<label>TIPO DE PAGO</label>
						        	<select class="form-control" id="codtipopago" v-model="pagos.codtipopago" required>
							    		<?php 
							    			foreach ($tipopagos as $key => $value) { ?>
							    				<option value="<?php echo $value["codtipopago"];?>">
							    					<?php echo $value["descripcion"];?>
							    				</option>
							    			<?php }
							    		?>
							    	</select>
							    </div>
					    	</div>
				    		<div v-show="pagos.codtipopago!=1 && campos.condicionpago==1">
						    	<div class="col-md-3 col-xs-12">
							    	<label>FECHA BANCO</label>
							    	<input type="text" class="form-control datepicker" id="fechadocbanco" autocomplete="off" required value="<?php echo date('Y-m-d');?>">
							    </div>
							    <div class="col-md-5 col-xs-12">
							    	<label>NRO VOUCHER</label>
						        	<input type="text" class="form-control" name="nrodocbanco" id="nrodocbanco" v-model="pagos.nrodocbanco" placeholder="Nro voucher" autocomplete="off">
							    </div>
							</div>
						    <div v-show="campos.condicionpago==2">
						    	<div class="col-md-2 col-xs-12">
							    	<label>DIAS</label>
						        	<input class="form-control" name="nrodias" v-model="campos.nrodias" v-on:keyup="phuyu_cuotas()" required autocomplete="off">
							    </div>
							    <div class="col-md-2 col-xs-12">
							    	<label>CUOTAS</label>
							    	<input class="form-control" name="nrocuotas" v-model="campos.nrocuotas" v-on:keyup="phuyu_cuotas()" required autocomplete="off">
							    </div>
							    <div class="col-md-3 col-xs-12">
							    	<label>TASA INTERES (%)</label>
							    	<input class="form-control" name="tasainteres" v-model="campos.tasainteres" v-on:keyup="calcular_credito()" required autocomplete="off">
							    </div>
							    <div class="col-md-4">
							    	<label>FECHA INICIO</label>
							    	<input type="date" class="form-control" id="fechainicio" v-on:change="phuyu_cuotas()" value="<?php echo date('Y-m-d');?>">
							    </div>
							</div>
						</div>
						<br>
					    <div v-if="campos.condicionpago==2">
					    	<div class="table-responsive">
					    		<table class="table table-bordered">
					    			<thead>
					    				<tr>
					    					<th>FECHA VENCE</th>
					    					<th>N° LETRA</th>
					    					<th>COD. UNICO</th>
					    					<th>IMPORTE</th>
					    					<th>INTERES</th>
					    					<th>TOTAL</th>
					    				</tr>
					    			</thead>
					    			<tbody>
					    				<tr v-for="dato in cuotas">
					    					<td><input type="date" class="form-control" v-model="dato.fechavence" name=""></td>
					    					<td><input type="text" class="form-control" v-model="dato.nroletra" name="" maxlength="10"></td>
					    					<td><input type="text" class="form-control" v-model="dato.nrounicodepago" name=""></td>
					    					<td><input type="number" class="form-control" v-model="dato.importe" step="0.01" v-on:keyup="calcular_credito()"></td>
					    					<td><input type="number" disabled="disabled" class="form-control" v-model="dato.interes"></td>
					    					<td>{{dato.total}}</td>
					    				</tr>
					    				<tr>
					    					<td colspan="5" align="right">TOTAL</td>
					    					<td id="totalimportecredito">{{importetotalcredito}}</td>
					    				</tr>
					    			</tbody>
					    		</table>
					    	</div>

					    	<div align="center">
								<button type="button" class="btn btn-warning btn-sm"> <b>INTERES: S/. {{totales.interes}}</b></button>
								<button type="button" class="btn btn-danger btn-sm"> <b>TOTAL CREDITO: S/. {{campos.totalcredito}}</b> </button>
							</div>
				    	</div>
				    	<div class="phuyu-linea"></div>
					    <div class="row form-group" align="center">
							<div class="col-md-12">
								<button type="submit" class="btn btn-success btn-lg" v-bind:disabled="estado==1"> 
									<b>GUARDAR COMPRA</b>
								</button>
								<button type="button" class="btn btn-danger btn-lg" data-dismiss="modal"> <b>CANCELAR</b> </button>
							</div>
						</div>
			        </form>
				</div>
			</div>
		</div>
	</div>

	<div id="modal_itemdetalle" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header modal-phuyu-titulo">
					<h4 class="modal-title"> <b style="letter-spacing:0.5px;">DETALLE DEL ITEM DE LA COMPRA</b> </h4> 
				</div>
				<div class="modal-body">
					<h5> <b>
						PRODUCTO: {{item.producto}} &nbsp; <span class="label label-warning">CANTIDAD: {{item.cantidad}} {{item.unidad}}</span>
					</b> </h5> <hr>

					<div class="row form-group">
				    	<div class="col-md-3 col-xs-12">
					    	<label>S/ BRUTO SIN IGV</label>
					    	<input type="number" class="phuyu-input number" v-model.number="item.preciobrutosinigv" v-on:keyup="phuyu_itemcalcular(item,0)" v-bind:disabled="item.codafectacionigv==21">
					    </div>
					    <div class="col-md-3 col-xs-12">
					    	<label>S/ BRUTO CON IGV</label>
					    	<input type="number" class="phuyu-input number" v-model.number="item.preciobruto" v-on:keyup="phuyu_itemcalcular(item,1)" v-bind:disabled="item.codafectacionigv==21">
					    </div>
					    <div class="col-md-3 col-xs-12">
					    	<label>DESCUENTO PRECIO (S/.)</label>
					    	<input type="number" class="phuyu-input number" v-model.number="item.descuento" v-on:keyup="phuyu_itemcalcular(item,-1)" v-bind:disabled="item.codafectacionigv==21">
					    </div>
					    <div class="col-md-3 col-xs-12">
					    	<label>DESCUENTO PRECIO (%)</label>
					    	<input type="number" class="phuyu-input number" v-model.number="item.porcdescuento" v-on:keyup="phuyu_itemcalcular(item,-2)" v-bind:disabled="item.codafectacionigv==21">
					    </div>
					</div>
					<div class="row form-group">
				    	<div class="col-md-2 col-xs-12">
					    	<label>PRECIO SIN IGV</label>
					    	<input type="number" class="phuyu-input number" v-model.number="item.preciosinigv" v-on:keyup="phuyu_itemcalcular(item,2)" v-bind:disabled="item.codafectacionigv==21">
					    </div>
					    <div class="col-md-2 col-xs-12">
					    	<label>PRECIO CON IGV</label>
					    	<input type="number" class="phuyu-input number" v-model.number="item.precio" v-on:keyup="phuyu_itemcalcular(item,3)" v-bind:disabled="item.codafectacionigv==21">
					    </div>
					    <div class="col-md-2 col-xs-12">
					    	<label>AFECTACION</label>
					    	<select class="phuyu-input" v-model="item.codafectacionigv" v-on:change="phuyu_itemcalcular(item,2)">
					    		<option value="10">GRAVADO</option> 
					    		<option value="20">EXONERADO</option> 
					    		<option value="21">GRATUITO</option> 
					    		<option value="30">INAFECTO</option>
					    	</select>
					    </div>
					    <div class="col-md-1 col-xs-12">
					    	<label>ICBPER</label>
					    	<select class="phuyu-input" v-model="item.conicbper" v-on:change="phuyu_itemcalcular(item,3)">
					    		<option value="1">SI</option>
					    		<option value="0">NO</option>
					    	</select>
					    </div>
					    <div class="col-md-2 col-xs-12">
					    	<label>FLETE</label>
					    	<input type="number" class="phuyu-input number" v-model.number="item.flete" v-on:keyup="phuyu_itemcalcular(item,0)">
					    </div>
					    <div class="col-md-3 col-xs-12">
					    	<label>SUBTOTAL</label>
					    	<input type="number" class="phuyu-input number" v-model.number="item.valorventa" v-on:keyup="phuyu_itemcalcular(item,4)" v-bind:disabled="item.codafectacionigv==21">
					    </div>
					</div>
					<div class="form-group text-center">
						<button type="button" class="btn btn-info btn-sm"> <b>SUBTOTAL: S/. {{item.valorventa}}</b> </button>
						<button type="button" class="btn btn-danger btn-sm"> <b>IGV: S/. {{item.igv}}</b></button>
						<button type="button" class="btn btn-danger btn-sm"> <b>ICBPER: S/. {{item.icbper}}</b> </button>
						<button type="button" class="btn btn-success btn-sm"> <b>TOTAL: S/. {{item.subtotal}}</b> </button>
					</div>
					<div class="form-group">
						<label>DESCRIPCION DEL ITEM DE COMPRA</label>
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
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_compras/nuevacompra.js"> </script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas_2.js"> </script>

<script> $(".datepicker").datetimepicker({format: 'YYYY-MM-DD',ignoreReadonly: true}).attr("readonly","true"); </script>