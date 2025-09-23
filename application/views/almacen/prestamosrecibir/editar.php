<div id="phuyu_editarprestamo">
	<div class="phuyu_body">
		<div class="card">
			<div class="card-header" style="padding: 1rem">
				<div class="row ">
					<div class="col-md-12 col-xs-12">
						<h5 style="letter-spacing:1px;"> <b> *** COMPROBANTE: <?php echo $info[0]["seriecomprobante"]." - ".$info[0]["nrocomprobante"]?></b> </h5>
						<input type="hidden" id="codkardex" value="<?php echo $info[0]["codkardex"]; ?>" name="">
					</div>
				</div>
			</div>

			<div class="card-body ">
				<div class="row form-group">
					<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
						<h5 align="center" style="border-bottom:2px solid #f3f3f3;padding-bottom:10px;">
			        		<b>DETALLE DEL PRESTAMO</b>
			        	</h5>
			        	
						<div class="detalle" style="height:250px;">
							<table class="table table-bordered" style="font-size: 10px">
								<thead>
										<th width="20%">PRODUCTO</th>
										<th width="12%">UNIDAD</th>
										<th width="10%">CANTIDAD PENDIENTE</th>
										<th width="10%">CANTIDAD DEVUELTA</th>
								</thead>
								<tbody>
									<tr v-for="(dato,index) in detalle">
										<td>{{dato.producto}}</td>
										<td>{{dato.unidad}} </td>
										<td>{{dato.cantidadpendiente}} </td>
										<td> 
											<input type="number" step="0.0001" class="form-control number" v-model.number="dato.cantidad" min="0.01" v-bind:max="dato.cantidadpendiente" required style="border:2px solid #13a89e;width:100%;">
											<input type="hidden" v-model="dato.valorventa" name="">
											<input type="hidden" v-model="dato.igv" name="">
											<input type="hidden" v-model="dato.subtotal" name="">
											<input type="hidden" v-model="dato.codproducto" name="">
											<input type="hidden" v-model="dato.codunidad" name="">
											<input type="hidden" v-model="dato.preciobruto" name="">
											<input type="hidden" v-model="dato.cantidadanterior" name="">
											<input type="hidden" v-model="dato.preciosinigv" name="">
											<input type="hidden" v-model="dato.preciounitario" name="">
											<input type="hidden" v-model="dato.preciorefunitario" name="">
											<input type="hidden" v-model="dato.codafectacionigv" name="">
											<input type="hidden" v-model="dato.itemorigen" name="">
											<input type="hidden" v-model="dato.factor" name="">
										</td>											
									</tr>
								</tbody>
							</table>
						</div><br>
						<div class="row form-group">
							<div class="col-md-12">
								<textarea class="form-control" v-model="campos.observacion" placeholder="Escribir una observación(opcional)"></textarea>
							</div>
						</div>
						<div class="col-md-12" align="center"> <br>
							<button type="submit" class="btn btn-success btn-lg" v-bind:disabled="estado==1"> <b>GUARDAR OPERACION</b> </button>
							<button type="button" class="btn btn-danger btn-lg" v-on:click="phuyu_cerrar()"> <b>CANCELAR</b> </button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_prestamos/editar.js"> </script>