<div id="phuyu_operacion">
	
	<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
		<input type="hidden" id="comprobante" value="<?php echo $info[0]['codcomprobantetipo'];?>">
		<input type="hidden" id="serie" value="<?php echo $info[0]['seriecomprobante'];?>">
		<input type="hidden" id="nrocomprobante" value="<?php echo $info[0]['nrocomprobante'];?>">
		<input type="hidden" id="itemrepetir" value="<?php echo $_SESSION["phuyu_itemrepetir"];?>">
		<input type="hidden" id="igvsunat" value="<?php echo $_SESSION["phuyu_igv"];?>">
		<input type="hidden" id="icbpersunat" value="<?php echo $_SESSION["phuyu_icbper"];?>">
		<input type="hidden" id="comprobantetipo" value="<?php echo $comprobantes[0]["codcomprobantetipo"]?>" name="">
		<input type="hidden" id="codproforma" value="<?php echo $info[0]['codproforma'];?>">
		<input type="hidden" id="empleado" value="<?php echo $info[0]['codempleado'];?>">
		<input type="hidden" id="codmoneda" value="<?php echo $info[0]['codmoneda'];?>">
		<input type="hidden" id="codcondicionpago" value="<?php echo $info[0]['condicionpago'];?>">

		<div class="phuyu_body">
			<div class="card">
				<div class="card-header" style="padding:1rem 2rem 1rem !important">
					<div class="row">
						<div class="col-md-4 col-xs-12"> <h5>EDITAR PROFORMA</h5> </div>
					</div>
				</div>
				<div class="card-body">
					<div class="row mb-2">
						<div class="col-md-4 col-xs-12">
							<label>PROVEEDOR DE LA PROFORMA</label>
			    			<select class="form-control selectpicker ajax" name="codpersona" id="codpersona" required data-live-search="true" v-on:change="phuyu_infocliente()">
			    				<option value="<?php echo $info[0]["codpersona"];?>"><?php echo $info[0]["razonsocial"];?></option>
			    			</select>
						</div>
						<div class="col-md-1 col-xs-2">
							<label>&nbsp;</label>
							<button type="button" class="btn btn-success btn-block" v-on:click="phuyu_addproveedor()" title="AGREGAR PROVEEDOR"> 
								<i class="fa fa-user-plus"></i>
							</button>
						</div>
						<div class="col-md-3 col-xs-12">
							<label>RAZON SOCIAL</label>
							<input type="text" class="form-control" id="cliente" v-model.trim="campos.cliente" autocomplete="off" maxlength="250" placeholder="Razon social del proveedor . . ." required value="<?php echo $info[0]["razonsocial"];?>">
							<input type="hidden" id="direccion" v-model.trim="campos.direccion" maxlength="250">
						</div>
						<div class="col-md-2 col-xs-6">
							<label>FECHA PROFORMA</label>
			    			<input type="date" class="form-control" name="fechacomprobante" id="fechacomprobante" autocomplete="off" v-on:blur="phuyu_tipocambio()" required value="<?php echo $info[0]["fechaproforma"];?>">
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
					</div>

					<div class="row mb-2">
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
					    <div class="col-md-1 col-xs-12 serie_ot">
					    	<label>SERIE</label>
				        	<input class="form-control" id="seriecomprobante" name="seriecomprobante" v-model.trim="campos.seriecomprobante" maxlength="4" required autocomplete="off">
					    </div>
					    <div class="col-md-2 col-xs-12">
					    	<label>NRO COMPROBANTE</label>
				        	<input class="form-control" name="nro" id="nro" v-model.trim="campos.nro" maxlength="8" required autocomplete="off">
				    	</div>
				    	<div class="col-md-4">
							<label>GLOSA DE LA PROFORMA</label>
							<input type="text" class="form-control" v-model="campos.descripcion" autocomplete="off" maxlength="250" placeholder="Referencia de la compra . . .">
						</div>

					    <div class="col-md-2 col-xs-12">
					    	<label>CONDICION PAGO</label>
					    	<select class="form-control" name="condicionpago" v-model="campos.condicionpago" v-on:change="phuyu_condicionpago()">
					    		<option value="1">CONTADO</option>
					    		<option value="2">CREDITO</option>
					    	</select>
					    </div>
					</div>
					<div class="row">

					</div><br>
					<div class="row form-group table-responsive scroll-phuyu-view">
						<table class="table table-bordered table-striped">
							<thead>
								<tr>
									<th width="7%" class="phuyu-item-mas" v-on:click="phuyu_item()"> <i class="fa fa-plus-square"></i> ITEM </th>
									<th width="25%">PRODUCTO</th>
									<th width="7%">UNIDAD</th>
									<th width="9%">CANTIDAD</th>
									<th width="10%">PRECIO</th>
									<th width="9%">SUBTOTAL</th>
									<th width="9%">I.G.V.</th>
									<th width="10%">TOTAL</th>
									<th width="1%"> <i class="fa fa-trash-o"></i> </th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="(dato,index) in detalle">
									<td class="phuyu-item-mas" v-on:click="phuyu_itemdetalle(index,dato)"> <i class="fa fa-plus-circle"></i> MAS </td>
									<td style="font-size:10px;">{{dato.producto}}</td>
									<td> <input type="hidden" v-model="dato.codunidad">{{dato.unidad}} </td>
									<td>
										<input type="number" step="0.0001" class="phuyu-input number" v-model.number="dato.cantidad" v-on:keyup="phuyu_calcular(dato)" min="0.001" required>
									</td>
									<td>
										<input type="number" step="0.000001" class="phuyu-input number" v-if="dato.codafectacionigv==21" v-model.number="dato.precio" min="0" readonly>
										<input type="number" step="0.000001" class="phuyu-input number" v-if="dato.codafectacionigv!=21" v-model.number="dato.precio" v-on:keyup="phuyu_calcular(dato)" min="0.0001" required  v-bind:disabled="dato.porcdescuento==100">
									</td>
									<td> <input type="number" class="phuyu-input number" v-model.number="dato.valorventa" readonly> </td>
									<td> <input type="number" class="phuyu-input number" v-model.number="dato.igv" min="0" readonly> </td>
									<td v-if="dato.codafectacionigv==21">
										<input type="number" step="0.0001" class="phuyu-input number" v-model.number="dato.subtotal">
									</td>
									<td v-if="dato.codafectacionigv!=21">
										<input type="number" step="0.0001" class="phuyu-input number" v-if="dato.calcular==0" v-model.number="dato.subtotal" readonly>
										<input type="number" step="0.0001" class="phuyu-input number" v-if="dato.calcular!=0" v-model.number="dato.subtotal" v-on:keyup="phuyu_subtotal(dato)" required v-bind:disabled="dato.porcdescuento==100">
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
					<div class="row" style="padding:2px;">
						<div class="text-center">
							<button type="button" class="btn btn-info btn-sm">IMP.BRUTO: S/. {{totales.bruto}}</button>
							<button type="button" class="btn btn-warning btn-sm">DESC: S/. {{totales.descuentos}}</button>

							<button type="button" class="btn btn-primary btn-sm">GRAV.: S/. {{operaciones.gravadas}}</button>
							<button type="button" class="btn btn-primary btn-sm">EXON.: S/. {{operaciones.exoneradas}}</button>
							<button type="button" class="btn btn-primary btn-sm">INAF.: S/. {{operaciones.inafectas}}</button>
							<button type="button" class="btn btn-primary btn-sm">GRAT.: S/. {{operaciones.gratuitas}}</button>

							<button type="button" class="btn btn-danger btn-sm"><b>IGV: S/. {{totales.igv}}</b></button>
							<button type="button" class="btn btn-danger btn-sm"><b>ISC: S/. {{totales.isc}}</b></button>
							<button type="button" class="btn btn-danger btn-sm"><b>ICBPER: S/. {{totales.icbper}}</b> </button>
							
							<button type="button" class="btn btn-success btn-sm"><b>TOTAL PROFORMA S/. {{totales.importe}}</b></button>
						</div>
						<div class="row">
							<div class="col-md-4 col-xs-6">
								<button type="button" class="btn btn-warning btn-block" disabled v-on:click="phuyu_compra()"> 
									<b> <i class="fa fa-plus-square"></i> NUEVA PROFORMA</b> 
								</button>
							</div>
							<div class="col-md-4 col-xs-6">
								<button type="submit" class="btn btn-success btn-block" v-bind:disabled="estado==1"> 
									<b><i class="fa fa-save"></i> GUARDAR CAMBIOS</b> 
								</button>
							</div>
							<div class="col-md-4 col-xs-12">
								<button type="button" class="btn btn-danger btn-block" v-on:click="phuyu_atras()"> 
									<b> <i class="fa fa-arrow-left"></i> CANCELAR</b> 
								</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>

	<div id="modal_itemdetalle" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header modal-phuyu-titulo">
					<h4 class="modal-title"> <b style="letter-spacing:0.5px;">DETALLE DEL ITEM DE LA PROFORMA</b> </h4> 
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
				    	<div class="col-md-3 col-xs-12">
					    	<label>PRECIO SIN IGV</label>
					    	<input type="number" class="phuyu-input number" v-model.number="item.preciosinigv" v-on:keyup="phuyu_itemcalcular(item,2)" v-bind:disabled="item.codafectacionigv==21">
					    </div>
					    <div class="col-md-3 col-xs-12">
					    	<label>PRECIO CON IGV</label>
					    	<input type="number" class="phuyu-input number" v-model.number="item.precio" v-on:keyup="phuyu_itemcalcular(item,3)" v-bind:disabled="item.codafectacionigv==21">
					    </div>
					    <div class="col-md-3 col-xs-12">
					    	<label>AFECTACION</label>
					    	<select class="phuyu-input" v-model="item.codafectacionigv" v-on:change="phuyu_itemcalcular(item,2)">
					    		<option value="10">GRAVADO</option> 
					    		<option value="20">EXONERADO</option> 
					    		<option value="21">GRATUITO</option> 
					    		<option value="30">INAFECTO</option>
					    	</select>
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
						<label>DESCRIPCION DEL ITEM DE PROFORMA</label>
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

<script src="<?php echo base_url();?>phuyu/phuyu_compras/editarproforma.js"> </script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas_2.js"> </script>

<script> $(".datepicker").datetimepicker({format: 'YYYY-MM-DD',ignoreReadonly: true}).attr("readonly","true"); </script>