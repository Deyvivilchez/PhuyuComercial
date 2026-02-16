<div id="phuyu_datos">
	<div class="phuyu_header">
		<div class="row phuyu_header_title">
			<div class="col-md-5">
				<h5> <b>REPORTE DE UTILIDADES DE PRODUCTOS</b> </h5> 
			</div>
		</div>

		<div class="row phuyu_header" style="padding:5px 0px;">
			<div class="col-md-2">
				<label>ALMACEN</label>
				<select class="form-control" id="codalmacen" v-model="campos.codalmacen">
					<option value="">TODOS</option>
					<?php 
						foreach ($almacenes as $key => $value) { ?>
							<option value="<?php echo $value['codalmacen'];?>"><?php echo $value["descripcion"];?></option>
						<?php } ?>
					?>
				</select>
			</div>
			<div class="col-md-4">
				<label>PRODUCTO</label>
				<select class="form-control selectpicker productos" name="codproducto" id="codproducto" required data-live-search="true">
    				<option value="">TODOS LOS PRODUCTOS</option>
    			</select>
			</div>
			<div class="col-md-2">
				<label>DESDE</label>
				<input type="text" class="form-control input-sm datepicker" id="fechadesde" value="<?php echo date('Y-m-d');?>" autocomplete="off">
			</div>
			<div class="col-md-2">
				<label>HASTA</label>
				<input type="text" class="form-control input-sm datepicker" id="fechahasta" value="<?php echo date('Y-m-d');?>" autocomplete="off">
			</div>
			<div class="col-md-1" style="margin-top: 2.5rem">
				<button type="button" class="btn btn-success btn-block btn-sm" v-on:click="generar_utilidades()">
					<i class="fa fa-search"></i>
				</button>
			</div>
		</div>
		<div class="detalle" style="height:320px;overflow-y:auto;">
			<table class="table table-bordered">
				<thead>
					<tr>
						<th style="width:5px;"> <center> <i class="fa fa-circle-o"></i> </center> </th>
						<th style="width:5px;">ID</th>
						<th style="width:15px;">CODIGO</th>
						<th style="width:40%;">DESCRIPCION</th>
						<th style="width:15%;">UNIDAD</th>
						<th style="width:10%;">CANTIDAD V.</th>
						<th style="width:15%;">UTILIDAD</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="d in datos">
						<td> <input type="radio" class="phuyu_radio" name="phuyu_seleccionar" v-on:click="phuyu_seleccionar(d)"> </td>
						<td>{{d.codproducto}}</td>
						<td>{{d.codigo}}</td>
						<td>{{d.producto}}</td>
						<td>{{d.unidad}}</td>
						<td>{{d.cantidad_v}}</td>
						<td>{{d.ganancia}}</td>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="6" align="right"><strong>TOTAL</strong></td>
						<td>{{total}}</td>
					</tr>
				</tfoot>
			</table>
		</div>
		<div align="center">
			<h3>TOTAL DE UTILIDAD S/. {{total}}</h3>
		</div>
	</div> <br>

	<div class="phuyu_body">
		
	</div>
</div>

<script> 
	var campos = {"codalmacen":<?php echo $_SESSION["phuyu_codalmacen"];?>,"codlinea":0,"stock":0,"fecha":"<?php echo date("Y-m-d");?>","controlstock":1,"estado":1,"buscar":""};

	
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_reportes/productos.js"> </script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas_2.js"> </script>
<script>
	$(".datepicker").datetimepicker({format: 'YYYY-MM-DD'});
</script>