<div id="phuyu_pedidos">

	<div class="phuyu_body">
		<div class="card">
			<div class="card-header">
		        <div class="row">
					<div class="col-md-2 col-xs-12">
						<input type="hidden" id="almacen" value="<?php echo $almacen;?>">
						<input type="hidden" id="formato" value="<?php echo $_SESSION['phuyu_formatopedido'];?>">
						<h5>LISTA DE PEDIDOS</h5> 
					</div>
					<div class="col-md-1 text-right"> <label class="p-5"><i class="fa fa-calendar"></i> DESDE:</label> </div>
					<div class="col-md-2 col-xs-12">
						<input type="date" class="form-control input-sm" id="fecha_desde" value="" v-on:change="phuyu_buscar()" autocomplete="off">
					</div>
					<div class="col-md-1 text-right"> <label class="p-5"><i class="fa fa-calendar"></i> HASTA:</label> </div>
					<div class="col-md-2 col-xs-12">
						<input type="date" class="form-control input-sm" id="fecha_hasta" value="<?php echo date('Y-m-d');?>" v-on:change="phuyu_buscar()" autocomplete="off">
					</div>
					<div class="col-md-2 text-right"> <label class="p-5">FORMATO IMPRESION:</label> </div>
					<div class="col-md-2 col-xs-12 hidden-xs">
						<select class="form-control input-sm" v-model="formato_impresion" v-on:change="phuyu_formato()">
							<option value="a4">A4 IMPRESION</option>
			        		<option value="a5">A5 IMPRESION</option>
			        		<option value="ticket">TICKET IMPRESION</option>
						</select>
					</div>
				</div><br>
			    <div class="row">
			    	<div class="col-md-8 phuyu_header_button">
				    	<button type="button" class="btn btn-success" v-on:click="phuyu_nuevo()"> <i class="fa fa-plus"></i> Nuevo Pedido</button>
				    	<button type="button" class="btn btn-warning editar" v-on:click="phuyu_editar()"> <i class="fa fa-edit"></i> Editar</button>
					    <button type="button" class="btn btn-danger eliminar" v-on:click="phuyu_eliminar()"> <i class="fa fa-trash-o"></i> Eliminar</button>
				    	<button type="button" class="btn btn-info" v-on:click="phuyu_ver()"> <i class="fa fa-eye"></i> Ver</button>
				    	
					    <button type="button" class="btn btn-primary" v-on:click="phuyu_imprimir()"> <i class="fa fa-print"></i> Imprimir</button> 
					    <button type="button" class="btn btn-info" v-on:click="phuyu_clonar()"> <i class="fa fa-files-o"></i> Clonar </button>
				    </div>
				    <div class="col-md-4 col-xs-12">
				    	<input type="text" class="form-control" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="BUSCAR REGISTRO . . .">
				    </div>
			    </div>
			</div>
			<div class="card-body" v-if="!cargando">

				<div class="phuyu_cargando" v-if="cargando">
					<img src="<?php echo base_url();?>public/img/phuyu_loading.gif"> <h5>CARGANDO DATOS</h5>
				</div>
				<div class="table-responsive">
					<table class="table table-bordered table-condensed">
						<thead>
							<tr>
								<th width="5px;"> <center> <i class="fa fa-circle-o"></i> </center> </th>
								<th width="5px"><i class="fa fa-file-o"></i></th>
								<th>DOCUMENTO</th>
								<th>RAZON SOCIAL</th>
								<th width="80px">FECHA</th>
								<th>TIPO</th>
								<th>COMPROBANTE</th>
								<th width="120px">IMPORTE</th>
								<th>PAGO</th>
								<th>ESTADO</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in datos" v-bind:class="[dato.estado==0 ? 'phuyu_anulado':'']">
								<td> 
									<input type="radio" class="phuyu_radio" name="phuyu_seleccionar" v-on:click="phuyu_seleccionar(dato.codpedido,dato.estado)"> 
								</td>
								<td>{{dato.codpedido}}</td>
								<td>{{dato.documento}}</td>
								<td>{{dato.cliente}}</td>
								<td>{{dato.fechapedido}}</td>
								<td>{{dato.tipo}}</td>
								<td>{{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
								<td> <b style="font-size:15px;" v-if="dato.estado!=0" class="text-success">S/. {{dato.importe}}</b> <b style="font-size:12px;" v-if="dato.estado==0">S/. {{dato.importe}}</b> </td>
								<td>
									<span class="label label-success" v-if="dato.condicionpago==1">AL CONTADO</span>
									<span class="label label-warning" v-else="dato.condicionpago==2">AL CREDITO</span>
								</td>
								<td>
									<span class="label label-warning" v-if="dato.estadoproceso==0">PENDIENTE</span>
									<span class="label label-success" v-else="dato.estadoproceso==1">ATENDIDO</span>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<?php include("application/views/phuyu/phuyu_paginacion.php");?>

			    <div id="modal_reportes" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
					<div class="modal-dialog" style="width:100%;margin:0px;">
						<div class="modal-content" align="center" style="border-radius:0px">
							<div class="modal-header">
								<button type="button" class="close" data-dismiss="modal" style="font-size:30px;margin-bottom:0px;">
									<i class="fa fa-times-circle"></i> 
								</button>
								<h4 class="modal-title">
									<b style="letter-spacing:4px;"><?php echo $_SESSION["phuyu_empresa"];?> </b>
								</h4>
							</div>
							<div class="modal-body" id="reportes_modal" style="height:450px;padding:0px;">
								<iframe id="phuyu_pdf" src="" style="width:100%; height:100%; border:none;"> </iframe>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	var pantalla = jQuery(document).height(); $("#reportes_modal").css({height: pantalla - 65});
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_pedidos/index.js"> </script>