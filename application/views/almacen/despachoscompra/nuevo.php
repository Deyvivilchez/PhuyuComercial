<div id="phuyu_despacho">
	<div class="phuyu_body">
			<div class="card">
				<div class="card-header" style="padding: 1rem">
					<div class="row ">
						<div class="col-md-12 col-xs-12">
							<?php 
								if ($info[0]["codmovimientotipo"]==2) {$tipo = 3; $operacion = "COMPRA"; $ope1 = "RECIBIDO"; $ope2 = "RECIBIR";  ?>
									<h5 style="letter-spacing:1px;"> <b>RECIBIR COMPRA *** COMPROBANTE: <?php echo $info[0]["seriecomprobante"]." - ".$info[0]["nrocomprobante"]?></b> </h5>
								<?php }else{ $tipo = 4; $operacion = "VENTA"; $ope1 = "DESPACHADO"; $ope2 = "DESPACHAR"; ?>
									<h5 style="letter-spacing:1px;"> <b>DESPACHAR VENTA *** COMPROBANTE: <?php echo $info[0]["seriecomprobante"]." - ".$info[0]["nrocomprobante"]?></b> </h5>
								<?php }
							?>
						</div>
					</div>
				</div>
	
				<div class="card-body ">
					<div class="row form-group">
						<div class="col-md-7 col-xs-12">
							<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
								<h5 align="center" style="border-bottom:2px solid #f3f3f3;padding-bottom:10px;">
					        		<b>DETALLE DE LA <?php echo $operacion;?></b>
					        	</h5>
					        	
								<div class="detalle" style="height:250px;">
									<table class="table table-bordered" style="font-size: 10px">
										<thead>
											<tr align="center" >
												<th width="20%">PRODUCTO</th>
												<th width="12%">UNIDAD</th>
												<th width="10%">CANTIDAD</th>
												<th width="10%"><?php echo $ope1;?></th>
												<th width="10%">PENDIENTE</th>
												<th width="10%"><?php echo $ope2;?></th>
											</tr>
										</thead>
										<tbody>
											<tr v-for="(dato,index) in detalle">
												<td>{{dato.producto}}</td>
												<td>{{dato.unidad}} </td>
												<td>{{dato.cantidad}} </td>
												<td>{{dato.recogido}} </td>
												<td style="color:#d43f3a"> <b>{{dato.pendiente}}</b> </td>
												<td> 
													<input type="number" step="0.0001" class="phuyu-input number" v-model.number="dato.recoger" min="0" v-bind:max="dato.pendiente" required style="border:2px solid #13a89e;width:100%;">
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
									<button type="submit" class="btn btn-success btn-lg" v-bind:disabled="estado==1"> <b>GUARDAR <?php echo $ope1; ?></b> </button>
									<button type="button" class="btn btn-danger btn-lg" v-on:click="phuyu_cerrar()"> <b>CANCELAR</b> </button>
								</div>
							</form>
						</div>

						<div class="col-md-5 col-xs-12">
				        	<h5 align="center" style="border-bottom:2px solid #f3f3f3;padding-bottom:10px;">
				        		<b>ENTREGAS O DESPACHOS REALIZADOS</b>
				        	</h5>

				        	<div class="entregas" style="height:200px;">
								<table class="table table-bordered" style="font-size: 9px">
									<thead>
										<tr align="center" >
											<th width="8%">ID</th>
											<th width="15%">FECHA</th>
											<th width="20%">PRODUCTO</th>
											<th width="15%">UNIDAD</th>
											<th width="10%">CANTIDAD</th>
											<th width="5%"> </th>
										</tr>
									</thead>
									<tbody>
										<tr v-for="dato in entregados">
											<td>{{dato.codkardexalmacen}}</td>
											<td>{{dato.fechakardex}}</td>
											<td>{{dato.producto}}</td>
											<td>{{dato.unidad}} </td>
											<td>{{dato.cantidad}} </td>
											<td>
												<button type="button" class="btn btn-warning btn-xs" v-on:click="phuyu_imprimir(dato.codkardexalmacen)" title="IMPRIMIR"><i data-acorn-icon="print"></i></button> 
												<button type="button" class="btn btn-danger btn-xs" v-on:click="phuyu_eliminar(dato)"><i data-acorn-icon="bin"></i></button>
											</td>
										</tr>
									</tbody>
								</table>
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
<script> var campos = {"codkardex":"<?php echo $info[0]['codkardex'];?>","codmovimientotipo":"<?php echo $info[0]['codmovimientotipo'];?>","codcomprobantetipo":"<?php echo $tipo;?>","observacion":""};</script>
<script src="<?php echo base_url();?>phuyu/phuyu_despachos/nuevo.js"> </script>
<script>
	var div_altura = jQuery(document).height(); var detalle = div_altura - 320; var entregas = div_altura - 250;
</script>