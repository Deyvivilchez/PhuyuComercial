
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
				<?php 
					$disabled = '';
					if($_SESSION["phuyu_codperfil"]>3){
						$disabled = 'disabled';
					}
				?>
				<div class="card-body">
					<div class="row mb-2">
						<h4 class="text-danger"><b>PEDIDO N° {{campos.nro}}</b></h4>
					</div>	
					<div class="row mb-2">
					    <div class="col-md-2 col-xs-12">
					    	<label>SERIE</label>
				        	<select class="form-select" id="seriecomprobante" v-model="campos.seriecomprobante" v-on:change="phuyu_correlativo()" required>
					    		<option value="">SERIE</option>
					    		<option v-for="dato in series" v-bind:value="dato.seriecomprobante"> 
					    			{{dato.seriecomprobante}}
					    		</option>
					    	</select>
					    </div>
					    <div class="col-md-3 col-xs-12">
					    	<label>T.COMPROBANTE REFERENCIA</label>
					    	<select class="form-select" name="codcomprobantetiporeferencia" v-model="campos.codcomprobantetiporeferencia" required >
					    		<option value="">SELECCIONE...</option>
					    		<?php
					    			foreach ($comprobantesreferencia as $key => $value) { ?>
					    				<option value="<?php echo $value["codcomprobantetipo"];?>">
					    					<?php echo $value["descripcion"];?>
					    				</option>
					    			<?php }
					    		?>
					    	</select>
					    </div>
				    	<div class="col-md-2 col-xs-12">
					    	<label>CONDICION PAGO</label>
					    	<select class="form-select" name="condicionpago" v-model="campos.condicionpago" v-on:change="phuyu_condicionpago()">
					    		<option value="1">CONTADO</option>
					    		<option value="2">CREDITO</option>
					    	</select>
					    </div>
					    <div class="col-md-3 col-xs-12">
					    	<label>SELECCIONAR VENDEDOR</label>
					    	<select class="form-select" id="codempleado" name="codempleado" <?php echo $disabled;?> v-model="campos.codempleado" required>
					    		<option value="0">SIN VENDEDOR</option>
					    		<?php
					    			foreach ($vendedores as $key => $value) {
					    			?>
					    				<option value="<?php echo $value["codpersona"];?>" > <?php echo $value["razonsocial"];?> </option>
					    			<?php }
					    		?>
					    	</select>
					    </div>
						<div class="col-md-2 col-xs-12">
							<label>FECHA PEDIDO</label>
			    			<input type="date" class="form-control" name="fechacomprobante" id="fechacomprobante" value="<?php echo date('Y-m-d');?>" autocomplete="off" required>
			    			<input type="hidden" class="form-control" name="fechakardex" id="fechakardex" value="<?php echo date('Y-m-d');?>" autocomplete="off">
						</div>
				    </div>
					<div class="row mb-2">
						<div class="col-md-4 col-xs-12">
							<div class="w-100">
								<label>SELECCIONAR CLIENTE</label>
								<select id="codpersona" name="codpersona">
									<option value="2">CLIENTES VARIOS</option>	
								</select>
							</div>
						</div>
						<div class="col-md-1 mt-4">
							<label>&nbsp;</label>
							<button type="button" class="btn btn-primary btn-icon" v-on:click="phuyu_addcliente()" title="AGREGAR CLIENTE"> 
								<i data-acorn-icon="user"></i>
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
						<div class="col-md-3 col-xs-12">
							<label>CLIENTE DEL PEDIDO</label>
							<input type="text" class="form-control" id="cliente" v-model.trim="campos.cliente" autocomplete="off" maxlength="250" placeholder="Razon social del cliente . . ." required>
						</div>
						<div class="col-md-4 col-xs-12">
							<label>DIRECCION CLIENTE</label>
							<input type="text" class="form-control" id="direccion" v-model.trim="campos.direccion" autocomplete="off" maxlength="250" placeholder="Direccion del cliente . . ." required>
						</div>
					</div>
					<div class="row mb-3">
						<div class="col-md-9">
							<label>GLOSA DEL PEDIDO</label>
							<input type="text" class="form-control" v-model="campos.descripcion">
						</div>
						<div class="col-md-3" align="right">
							<button type="button" class="btn-items-mas btn btn-success btn-icon" style="margin-top: 1.3rem;" v-on:click="phuyu_item()"><i data-acorn-icon="plus"></i> Buscar Productos </button>
						</div>
					</div>
					<div class="row form-group">
						<div class="data-table-responsive-wrapper">	
							<table class="table table-striped" style="font-size: 11px">
								<thead>
									<tr>
										<th width="7%"> </th>
										<th width="30%">PRODUCTO</th>
										<th width="10%">UNIDAD</th>
										<th width="8%">STOCK</th>
										<th width="8%">CANTIDAD</th>
										<th width="10%">PRECIO UNIT.</th>
										<th width="8%">I.G.V.</th>
										<th width="5%">ICBPER</th>
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
											<select class="form-select number" v-model="dato.codunidad" v-on:change="informacion_unidad(index,dato,this.value)" id="codunidad">
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
											<input type="number" step="0.0001" class="form-control number" v-if="dato.control==1" v-model.number="dato.cantidad" v-on:keyup="phuyu_calcular(dato)" min="0.0001" required>
											<input type="number" step="0.0001" class="form-control number" v-if="dato.control==0" v-model.number="dato.cantidad" v-on:keyup="phuyu_calcular(dato)" min="0.0001" required>
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
										<td colspan="8" style="text-align: right;"><b>Subtotal</b></td>
										<td class="text-center">{{totales.valorventa}}</td>
									</tr>
									<tr>
										<td colspan="8" style="text-align: right;"><b>I.G.V</b></td>
										<td class="text-center">{{totales.igv}}</td>
									</tr>
									<tr>
										<td colspan="8" style="text-align: right;"><b>Total</b></td>
										<td class="text-center" style="font-size: 1rem;"><b class="text-danger">{{totales.importe}}</b></td>
									</tr>
								</tfoot>
							</table>
						</div>
					</div>
					<div class="row">
						<div class="col-md-5"></div>
						<div class="col-md-7" align="right">
							<button type="button" class="btn btn-warning btn-icon" v-on:click="phuyu_venta()"> 
								<b> <i data-acorn-icon="plus"></i> NUEVO PEDIDO</b> 
							</button>
							<button type="submit" class="btn btn-info btn-icon" v-bind:disabled="estado==1"> 
								<b><i data-acorn-icon="save"></i> GUARDAR PEDIDO</b> 
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
					<h5 class="modal-title"> <b>GENERAR CUOTAS DE PAGO AL CREDITO</b> </h5>
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
				    		<table class="table table-striped" style="font-size: 11px">
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
						<button type="button" class="btn btn-danger" data-bs-dismiss="modal">
							<i class="fa fa-times-circle"></i> VOLVER AL FORMULARIO
						</button>
						<button type="button" class="btn btn-success" v-on:click="phuyu_pagar()">
							<i class="fa fa-save"></i> GUARDAR PEDIDO
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

	<div id="modal_itemdetalle" data-bs-backdrop="static" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
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
					    		<?php 
									foreach ($afectacionigv as $key => $value) { ?>
										<option value="<?php echo $value["oficial"];?>"><?php echo $value["descripcion"];?></option>
									<?php }
								?>
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

<script src="<?php echo base_url();?>phuyu/phuyu_pedidos/nuevo.js"> </script>
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