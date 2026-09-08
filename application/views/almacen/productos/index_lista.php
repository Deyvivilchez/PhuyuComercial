<style>
	#phuyu_datos.phuyu-productos-list .phuyu-header-card {
		background: #fff;
		border: 1px solid #e9ebec;
		border-radius: 8px;
		box-shadow: 0 1px 2px rgba(56, 65, 74, 0.08);
		margin-bottom: 14px;
		padding: 12px;
	}
	#phuyu_datos.phuyu-productos-list .phuyu-toolbar {
		align-items: center;
		gap: 8px;
	}
	#phuyu_datos.phuyu-productos-list .form-control {
		border: 1px solid #d9e2ef;
		border-radius: 6px;
		box-shadow: none;
		min-height: 38px;
	}
	#phuyu_datos.phuyu-productos-list .phuyu-table-card {
		border: 1px solid #e9ebec;
		border-radius: 8px;
		box-shadow: 0 1px 2px rgba(56, 65, 74, 0.08);
		padding: 12px;
	}
	#phuyu_datos.phuyu-productos-list .table {
		font-size: 12px;
	}
	#phuyu_datos.phuyu-productos-list .table thead th {
		background: #f3f6f9;
		color: #495057;
		font-size: 11px;
		text-transform: uppercase;
		white-space: nowrap;
	}
</style>

<div id="phuyu_datos" class="phuyu-productos-list">
	<div class="phuyu_header phuyu-header-card">
		<div class="row g-2 align-items-center">
			<div class="col-md-4 col-xs-12"> <h5 class="mb-0">LISTA DE PRODUCTOS</h5> </div>
			<div class="col-md-4">
				<div class="d-flex flex-wrap phuyu-toolbar">
					<button type="button" class="btn btn-success" v-on:click="phuyu_nuevo()"> <i class="bi bi-plus-lg me-1"></i> Nuevo </button>
					<button type="button" class="btn btn-info" v-on:click="phuyu_ver()"> <i class="bi bi-eye me-1"></i> Ver </button>
					<button type="button" class="btn btn-warning" v-on:click="phuyu_editar()"> <i class="bi bi-pencil-square me-1"></i> Editar </button>
					<button type="button" class="btn btn-danger" v-on:click="phuyu_eliminar()"> <i class="bi bi-trash me-1"></i> Eliminar </button>
				</div>
			</div>
			<div class="col-md-4 col-xs-12">
				<div class="input-group">
					<span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
					<input type="text" class="form-control border-start-0" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="BUSCAR REGISTRO . . .">
				</div>
			</div>
		</div>
	</div>
	
	<div class="phuyu_body phuyu-table-card">
		<input type="hidden" id="phuyu_opcion" value="1">

		<div class="phuyu_cargando" v-if="cargando">
			<img src="<?php echo base_url();?>public/img/phuyu_loading.gif"> <h5>CARGANDO DATOS</h5>
		</div>

		<div v-if="!cargando">
			<div class="table-responsive">
				<table class="table table-hover align-middle mb-0">
					<thead>
						<tr>
							<th width="5px;"> <center> <i class="bi bi-record-circle"></i> </center> </th>
							<th width="10px">CODIGO</th>
							<th>DESCRIPCION PRODUCTO</th>
							<th>MARCA</th>
							<th>CARACTERISTICAS</th>
							<th>STOCK</th>
							<th>PRECIO</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="dato in datos">
							<td> <input type="radio" class="phuyu_radio" name="phuyu_seleccionar" v-on:click="phuyu_seleccionar(dato.codproducto)"> </td>
							<td>{{dato.codigo}}</td>
							<td>{{dato.descripcion}}</td>
							<td>{{dato.marca}}</td>
							<td>{{dato.caracteristicas}}</td>
							<td>
								<span class="badge bg-danger" v-if="dato.stock<=0">STOCK: {{dato.stock}} {{dato.unidad}} </span>
								<span class="badge bg-info" v-if="dato.stock>0">STOCK: {{dato.stock}} {{dato.unidad}} </span>
							</td>
							<td>S/. <b>{{dato.precio}}</b></td>
						</tr>
					</tbody>
				</table>
			</div>
			<?php include("application/views/phuyu/phuyu_paginacion.php");?>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_datos.js"> </script>
