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
		<input type="hidden" id="comprobante" value="<?php echo $info[0]['codcomprobantetipo'];?>">
		<input type="hidden" id="serie" value="<?php echo $info[0]['seriecomprobante'];?>">
		<input type="hidden" id="nrocomprobante" value="<?php echo $info[0]['nrocomprobante'];?>">
		<input type="hidden" id="stockalmacen" value="<?php echo $_SESSION["phuyu_stockalmacen"];?>">
		<input type="hidden" id="itemrepetir" value="<?php echo $_SESSION["phuyu_itemrepetir"];?>">
		<input type="hidden" id="igvsunat" value="<?php echo $_SESSION["phuyu_igv"];?>">
		<input type="hidden" id="icbpersunat" value="<?php echo $_SESSION["phuyu_icbper"];?>">
		<input type="hidden" id="formato" value="<?php echo $_SESSION['phuyu_formatoproforma'];?>">
		<input type="hidden" id="codproforma" value="<?php echo $info[0]['codproforma'];?>">
		<input type="hidden" id="empleado" value="<?php echo $info[0]['codempleado'];?>">
		<input type="hidden" id="condicionpagoref" value="<?php echo $info[0]["condicionpago"];?>" name="">

		<div class="phuyu_body">
			<div class="card">
				<?php 
					$disabled = '';
					if($_SESSION["phuyu_codperfil"]>3){
						$disabled = 'disabled';
					}
				?>
				<div class="card-body">
					<div class="row form-group">
						<div class="col-md-4 col-xs-12"> <h5><b>EDITAR PROFORMA</b></h5> </div>
					</div>
					<div class="row form-group">
						<div class="col-md-2 col-xs-12">
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
					    <div class="col-md-2 col-xs-12">
					    	<label>SERIE <b>(NRO: <?php echo $info[0]["nrocomprobante"];?>)</b></label>
				        	<select class="form-select" id="seriecomprobante" v-model="campos.seriecomprobante" v-on:change="phuyu_correlativo()" required>
					    		<option value="">SERIE</option>
					    		<option v-for="dato in series" v-bind:value="dato.seriecomprobante"> 
					    			{{dato.seriecomprobante}}
					    		</option>
					    	</select>
					    </div>
				    	<div class="col-md-2 col-xs-12">
					    	<label>CONDICION PAGO</label>
					    	<select class="form-select" name="condicionpago" v-model="campos.condicionpago">
					    		<option value="1">CONTADO</option>
					    		<option value="2">CREDITO</option>
					    	</select>
					    </div>
					    <div class="col-md-4 col-xs-12">
					    	<label>SELECCIONAR VENDEDOR</label>
					    	<select class="form-select" name="codempleado" <?php echo $disabled;?> v-model="campos.codempleado" required>
					    		<option value="0">SIN VENDEDOR</option>
					    		<?php
					    			foreach ($vendedores as $key => $value) { ?>
					    				<option value="<?php echo $value["codpersona"];?>"> <?php echo $value["razonsocial"];?> </option>
					    			<?php }
					    		?>
					    	</select>
					    </div>
						<div class="col-md-2 col-xs-12">
							<label>FECHA PROFORMA</label>
			    			<input type="date" class="form-control" name="fechacomprobante" id="fechacomprobante" value="<?php echo $info[0]["fechaproforma"];?>" autocomplete="off" required>
						</div>
					</div>
					<div class="row form-group">
						<div class="col-md-3 col-xs-10">
							<div class="w-100">
								<label>SELECCIONAR CLIENTE</label>
				    			<select class="form-select" name="codpersona" id="codpersona" required>
				    				<option value="<?php echo $info[0]["codpersona"];?>"><?php echo $info[0]["razonsocial"];?></option>
				    			</select>
				    		</div>
						</div>
						<div class="col-md-1 mt-4">
							<label>&nbsp;</label>
							<button type="button" class="btn btn-primary btn-icon" v-on:click="phuyu_addcliente()" title="AGREGAR CLIENTE"> 
								<i data-acorn-icon="user"></i>
							</button>
						</div>
						<div class="col-md-5 col-xs-10" style="display: none">
							<label>GLOSA DE LA PROFORMA</label>
							<input type="text" class="form-control" v-model="campos.descripcion" autocomplete="off" maxlength="250" placeholder="Referencia del pedido . . .">
						</div>

						<div class="col-md-4 col-xs-12">
							<label>CLIENTE DE LA PROFORMA</label>
							<input type="text" class="form-control" id="cliente" v-model.trim="campos.cliente" autocomplete="off" maxlength="250" placeholder="Razon social del cliente . . ." required value="<?php echo $info[0]["razonsocial"];?>">
						</div>
						<div class="col-md-4 col-xs-12">
							<label>DIRECCION CLIENTE</label>
							<input type="text" class="form-control" id="direccion" v-model.trim="campos.direccion" autocomplete="off" maxlength="250" placeholder="Direccion del cliente . . ." required value="<?php echo $info[0]["direccion"]?>">
						</div>
					</div>
					<div class="row form-group">
						<div class="col-md-9">
							<label>GLOSA DE LA PROFORMA</label>
							<input type="text" class="form-control" maxlength="150" v-model="campos.descripcion" name="">
						</div>
						<div class="col-md-3" align="right">
							<button type="button" class="btn-items-mas btn btn-success btn-icon" style="margin-top: 1.3rem;" v-on:click="phuyu_item()"><i data-acorn-icon="plus"></i> Buscar Productos </button>
						</div>
					</div>
					<div class="row form-group table-responsive scroll-phuyu-view">
						<table class="table table-striped" style="font-size: 11px">
							<thead>
								<tr>
									<th></th>
									<th width="30%">PRODUCTO</th>
									<th width="13%">UNIDAD</th>
									<th width="10%">CANTIDAD</th>
									<th width="10%">PRECIO UNIT.</th>
									<th width="7%">I.G.V.</th>
									<th width="7%">ICBPER</th>
									<th width="10%">SUBTOTAL</th>
									<th width="1%"> <i class="fa fa-trash-o"></i> </th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="(dato,index) in detalle">
									<td>
										<button type="button" data-bs-target="#modal_itemdetalle" class="btn btn-primary btn-block btn-xs" style="margin-bottom:-1px;" v-on:click="phuyu_itemdetalle(index,dato)">
												<b>+ MAS</b> 
											</button>
									</td>
									<td style="font-size:10px;">{{dato.producto}}</td>
									<td>
										<select class="form-select number unidad" v-model="dato.codunidad" v-on:change="informacion_unidad(index,dato,this.value)" id="codunidad">
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
									<td>
										<input type="number" step="0.0001" class="form-control number" v-model.number="dato.cantidad" v-on:keyup="phuyu_calcular(dato)" min="0.0001" required>
									</td>
									<td>
										<input type="number" step="0.0001" class="form-control number" v-if="dato.codafectacionigv==21" v-model.number="dato.precio" min="0" readonly>
										<input type="number" step="0.0001" class="form-control number" v-if="dato.codafectacionigv!=21" v-model.number="dato.precio" v-on:keyup="phuyu_calcular(dato)" min="0.001" required  v-bind:disabled="dato.porcdescuento==100">
									</td>
									<td> <input type="number" class="form-control number" v-model.number="dato.igv" min="0" readonly> </td>
									<td> <input type="number" class="form-control number" v-model.number="dato.icbper" min="0" readonly> </td>
									<td v-if="dato.codafectacionigv==21">
										<input type="number" step="0.01" class="form-control number" v-model.number="dato.subtotal">
									</td>
									<td v-if="dato.codafectacionigv!=21">
										<input type="number" step="0.01" class="form-control number" v-if="dato.calcular==0" v-model.number="dato.subtotal" readonly>
										<input type="number" step="0.01" class="form-control number" v-if="dato.calcular!=0" v-model.number="dato.subtotal" v-on:keyup="phuyu_subtotal(dato)" required v-bind:disabled="dato.porcdescuento==100">
									</td>
									<td> 
										<button type="button" class="btn btn-danger btn-block btn-xs" style="margin-bottom:-1px;" v-on:click="phuyu_deleteitem(index,dato)">
											<b>X</b> 
										</button>
									</td>
								</tr>
							</tbody>
							<tfoot>
								<tr>
									<td colspan="7" style="text-align: right;"><b>Subtotal</b></td>
									<td class="text-center">{{totales.valorventa}}</td>
								</tr>
								<tr>
									<td colspan="7" style="text-align: right;"><b>I.G.V</b></td>
									<td class="text-center">{{totales.igv}}</td>
								</tr>
								<tr>
									<td colspan="7" style="text-align: right;"><b>Total</b></td>
									<td class="text-center" style="font-size: 1rem;"><b class="text-danger">{{totales.importe}}</b></td>
								</tr>
							</tfoot>
						</table>
					</div>
					<div class="row form-group">
						<div class="col-md-5"></div>
						<div class="col-md-7 col-xs-12" align="right">
							<button type="button" class="btn btn-warning btn-icon" v-on:click="phuyu_venta()" disabled> 
								<b> <i data-acorn-icon="plus"></i> NUEVO PEDIDO</b> 
							</button>
							<button type="submit" class="btn btn-info btn-icon" v-bind:disabled="estado==1"> 
								<b><i data-acorn-icon="save"></i> GUARDAR CAMBIOS</b> 
							</button>
							<button type="button" class="btn btn-danger btn-icon" v-on:click="phuyu_atras()"> 
								<b> <i data-acorn-icon="arrow-left"></i> ATRAS</b> 
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
				<div class="modal-header modal-phuyu-titulo">
					<h5 class="modal-title"> <b style="letter-spacing:1px;">GENERAR CUOTAS DE PAGO AL CREDITO</b> </h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
						<button type="button" class="btn btn-default" data-bs-dismiss="modal">
							<i class="fa fa-times-circle"></i> VOLVER AL FORMULARIO
						</button>
						<button type="button" class="btn btn-success" v-on:click="phuyu_itemcalcular_cerrar(item)">
							<i class="fa fa-save"></i> GUARDAR CAMBIOS
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

	<div id="modal_itemdetalle" class="modal fade" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-hidden="true">
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
					    	<input type="number" class="form-control number" v-model.number="item.preciobruto" v-on:keyup="phuyu_itemcalcular(item,0)" v-bind:disabled="item.codafectacionigv==21">
					    </div>
					    <div class="col-md-4 col-xs-12">
					    	<label>DESCUENTO PRECIO (S/.)</label>
					    	<input type="number" class="form-control number" v-model.number="item.descuento" v-on:keyup="phuyu_itemcalcular(item,-1)" v-bind:disabled="item.codafectacionigv==21">
					    </div>
					    <div class="col-md-4 col-xs-12">
					    	<label>DESCUENTO PRECIO (%)</label>
					    	<input type="number" class="form-control number" v-model.number="item.porcdescuento" v-on:keyup="phuyu_itemcalcular(item,-2)" v-bind:disabled="item.codafectacionigv==21">
					    </div>
					</div>
					<div class="row form-group">
				    	<div class="col-md-3 col-xs-12">
					    	<label>PRECIO SIN I.G.V.</label>
					    	<input type="number" class="form-control number" v-model.number="item.preciosinigv" v-on:keyup="phuyu_itemcalcular(item,1)" v-bind:disabled="item.codafectacionigv==21">
					    </div>
					    <div class="col-md-3 col-xs-12">
					    	<label>PRECIO UNITARIO</label>
					    	<input type="number" class="form-control number" v-model.number="item.precio" v-on:keyup="phuyu_itemcalcular(item,2)" v-bind:disabled="item.codafectacionigv==21">
					    </div>
					    <div class="col-md-3 col-xs-12">
					    	<label>TIPO AFECTACION</label>
					    	<select class="form-select" v-model="item.codafectacionigv" v-on:change="phuyu_itemcalcular(item,2)">
					    		<option value="10">GRAVADO</option> 
					    		<option value="20">EXONERADO</option> 
					    		<option value="21">GRATUITO</option> 
					    		<option value="30">INAFECTO</option>
					    	</select>
					    </div>
					    <div class="col-md-3 col-xs-12">
					    	<label>ICBPER</label>
					    	<select class="form-select" v-model="item.conicbper" v-on:change="phuyu_itemcalcular(item,2)">
					    		<option value="1">SI</option>
					    		<option value="0">NO</option>
					    	</select>
					    </div>
					</div>
					<div class="form-group text-center">
						<button type="button" class="btn btn-info btn-sm" style="font-size: 11px"> <b>VALOR VENTA: S/. {{item.valorventa}}</b> </button>
						<button type="button" class="btn btn-danger btn-sm" style="font-size: 11px"> <b>IGV: S/. {{item.igv}}</b></button>
						<button type="button" class="btn btn-danger btn-sm" style="font-size: 11px"> <b>ICBPER: S/. {{item.icbper}}</b> </button>
						<button type="button" class="btn btn-success btn-sm" style="font-size: 11px"> <b>SUBTOTAL: S/. {{item.subtotal}}</b> </button>
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

<script src="<?php echo base_url();?>phuyu/phuyu_proformas/editar.js"> </script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas_2.js"> </script>

<script>
	var pantalla = jQuery(document).height(); $("#reportes_modal").css({height: pantalla - 65});
	
	if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }
    if (typeof Select2Controls !== 'undefined') {
      let select2Controls = new Select2Controls();
    }
</script>