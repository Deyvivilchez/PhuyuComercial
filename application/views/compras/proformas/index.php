<div id="phuyu_pedidos">

	<div class="phuyu_body">
		<div class="card">
			<div class="card-header">
				<div class="row">
					<div class="col-md-6 col-xs-12">
						<input type="hidden" id="almacen" value="<?php echo $almacen;?>">
						<h5>LISTA DE PROFORMAS REGISTRADAS</h5> 
					</div>
					<div class="col-md-1"> <label class="p-5"><i class="fa fa-calendar"></i> DESDE</label> </div>
					<div class="col-md-2 col-xs-12">
						<input type="date" class="form-control input-sm" id="fecha_desde" value="" v-on:blur="phuyu_buscar()" autocomplete="off">
					</div>
					<div class="col-md-1"> <label class="p-5"><i class="fa fa-calendar"></i> HASTA</label> </div>
					<div class="col-md-2 col-xs-12">
						<input type="date" class="form-control input-sm" id="fecha_hasta" value="<?php echo date('Y-m-d');?>" v-on:blur="phuyu_buscar()" autocomplete="off">
					</div>
				</div><br>
			    <div class="row">
			    	<div class="col-md-8 phuyu_header_button">
				    	<button type="button" class="btn btn-success" v-on:click="phuyu_nuevo()"> <i class="fa fa-plus-square"></i> Nueva Proforma </button>
					    <button type="button" class="btn btn-info" v-on:click="phuyu_ver()"> <i class="fa fa-file"></i> Ver </button>
					    <button type="button" class="btn btn-warning" v-on:click="phuyu_editar()"> <i class="fa fa-edit"></i> Editar </button>
					    <button type="button" class="btn btn-danger" v-on:click="phuyu_eliminar()"> <i class="fa fa-trash-o"></i> Eliminar </button>
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
								<th>DOCUMENTO</th>
								<th>RAZON SOCIAL</th>
								<th>FECHA</th>
								<th>TIPO</th>
								<th>COMPROBANTE</th>
								<th width="130px">IMPORTE</th>
								<th>PAGO</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in datos" v-bind:class="[dato.estado==0 ? 'phuyu_anulado':'']">
								<td> <input type="radio" v-if="dato.estado!=0" name="phuyu_seleccionar" v-on:click="phuyu_seleccionar(dato.codproforma)"> </td>
								<td>{{dato.documento}}</td>
								<td>{{dato.razonsocial}}</td>
								<td>{{dato.fechaproforma}}</td>
								<td>{{dato.tipo}}</td>
								<td>{{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
								<td>
									<b v-if="dato.codmoneda==1" style="font-size:17px;">S/.</b> 
									<b v-if="dato.codmoneda!=1" style="font-size:17px;">$</b> 
									<b style="font-size:17px;">{{dato.importe}}</b>
								</td>
								<td>
									<span class="label label-danger" v-if="dato.condicionpago==1">AL CONTADO</span>
									<span class="label label-warning" v-else="dato.condicionpago==2">AL CREDITO</span>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<?php include("application/views/phuyu/phuyu_paginacion.php");?>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_proformas/comprasindex.js"> </script>
<script>
	$(".datepicker").datetimepicker({format: 'YYYY-MM-DD'});
</script>