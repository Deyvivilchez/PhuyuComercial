<div id="phuyu_datos">
	<div class="phuyu_header">
		<div class="row phuyu_header_title">
			<div class="col-md-8 col-xs-12"> <h5>LISTA DE CENTROS DE COSTOS</h5> </div>
		</div>
	    <div class="row">
	    	<div class="col-md-8 phuyu_header_button">
		    	<button type="button" class="btn btn-success" v-on:click="phuyu_nuevo()"> <i class="fa fa-plus-square"></i> NUEVO </button>
			    <button type="button" class="btn btn-info" v-on:click="phuyu_editar()"> <i class="fa fa-edit"></i> EDITAR </button>
			    <button type="button" class="btn btn-danger" v-on:click="phuyu_eliminar()"> <i class="fa fa-trash-o"></i> ELIMINAR </button>
		    </div>
		    <div class="col-md-4 col-xs-12">
		    	<input type="text" class="form-control" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="BUSCAR REGISTRO . . .">
		    </div>
	    </div>
	</div> <br>

	<div class="phuyu_body">
		<input type="hidden" id="phuyu_opcion" value="1">

		<div class="phuyu_cargando" v-if="cargando">
			<i class="fa fa-spinner fa-spin"></i> <h5>CARGANDO DATOS</h5>
		</div>

		<div v-if="!cargando">
			<div class="table-responsive">
				<table class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th width="5px;"> <center> <i class="fa fa-circle-o"></i> </center> </th>
							<th>DESCRIPCION</th>
							<th>CENTRO COSTO</th>
							<th>CTA ABONO</th>
							<th>CTA CARGO</th>
							<th>CTA DEBE</th>
							<th>CTA HABER</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="dato in datos">
							<td> <input type="radio" class="phuyu_radio" name="phuyu_seleccionar" v-on:click="phuyu_seleccionar(dato.codcentrocosto)"> </td>
							<td>{{dato.descripcion}}</td>
							<td>{{dato.centrocosto}}</td>
							<td>{{dato.ctacontableabono}}</td>
							<td>{{dato.ctacontablecargo}}</td>
							<td>{{dato.ctacontabledebe}}</td>
							<td>{{dato.ctacontablehaber}}</td>
						</tr>
					</tbody>
				</table>
			</div> <hr>

			<?php include("application/views/phuyu/phuyu_paginacion.php");?>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_datos.js"> </script>