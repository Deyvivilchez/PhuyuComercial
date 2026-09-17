<style>
	#phuyu_inventario.phuyu-inventario-operacion .phuyu-header-card {
		background: #fff;
		border: 1px solid #e9ebec;
		border-radius: 8px;
		box-shadow: 0 1px 2px rgba(56, 65, 74, 0.08);
		margin-bottom: 14px;
		padding: 12px;
	}
	#phuyu_inventario.phuyu-inventario-operacion .form-control,
	#phuyu_inventario.phuyu-inventario-operacion .form-select {
		border: 1px solid #d9e2ef;
		border-radius: 6px;
		box-shadow: none;
		min-height: 36px;
	}
	#phuyu_inventario.phuyu-inventario-operacion .phuyu-table-wrap {
		border: 1px solid #e9ebec;
		border-radius: 8px;
		height: calc(100vh - 235px);
		overflow: auto;
		padding: 0;
	}
	#phuyu_inventario.phuyu-inventario-operacion .table {
		font-size: 12px;
	}
	#phuyu_inventario.phuyu-inventario-operacion .table thead th {
		background: #f3f6f9;
		color: #495057;
		font-size: 11px;
		text-transform: uppercase;
		white-space: nowrap;
	}
	#phuyu_inventario.phuyu-inventario-operacion .phuyu-actions {
		display: flex;
		flex-wrap: wrap;
		gap: 8px;
		justify-content: center;
		padding-top: 14px;
	}
	#phuyu_inventario.phuyu-inventario-operacion .modal-content {
		border: 0;
		border-radius: 8px;
		box-shadow: 0 10px 30px rgba(15, 23, 42, 0.14);
	}
	#phuyu_inventario.phuyu-inventario-operacion .modal-header {
		background: #f3f6f9;
		border-bottom: 1px solid #e9ebec;
	}
</style>

<div id="phuyu_inventario" class="phuyu-inventario-operacion">
	<div class="phuyu_header phuyu-header-card">
		<div class="row g-2 align-items-center phuyu_header_title">
			<div class="col-md-2 col-xs-12"> <h5 class="mb-0">INVENTARIO</h5> </div>

			<div class="col-md-3 col-xs-12">
				<input type="text" class="form-control" v-model="buscar" placeholder="BUSCAR PRODUCTO . . .">
			</div>
			<div class="col-md-2 col-xs-12">
				<select class="form-select" id="codlinea" v-on:change="phuyu_productos()">
					<option value="">TODAS LINEAS</option>
					<?php
		    			foreach ($lineas as $key => $value) { ?>
		    				<option value="<?php echo $value["codlinea"];?>">
		    					<?php echo $value["descripcion"];?>
		    				</option>
		    			<?php }
		    		?>
				</select>
			</div>
			<div class="col-md-5 col-xs-12 text-end">
				<button type="button" class="btn btn-success" v-on:click="phuyu_masproductos()"><i class="bi bi-arrow-repeat me-1"></i> Cargar productos</button>
				<button type="button" class="btn btn-warning" v-on:click="phuyu_nuevoproducto()"><i class="bi bi-plus-lg me-1"></i> Nuevo producto</button>
			</div>
		</div>
	</div>

	<div class="phuyu_body_row">
		<input type="hidden" name="codregistro" v-model="campos.codregistro">
		<div class="table-responsive scroll-phuyu-view phuyu-table-wrap">
			<table class="table table-hover align-middle mb-0">
				<thead>
					<tr>
						<th width="5px">#</th>
						<th width="5px">ID</th>
						<th width="10px">CODIGO</th>
						<th>PRODUCTO</th>
						<th width="10px">UNIDAD</th>
						<th>MARCA</th>
						<th width="10%">CANTIDAD</th>
						<th width="10%">P.&nbsp;COSTO</th>
						<th width="10%">P.&nbsp;VENTA</th>
						<th width="10%">IMPORTE</th>
						<th width="5px"><i class="bi bi-trash"></i></th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(dato, index) in buscar_productos">
						<td>{{index + 1}}</td>
						<td>{{dato.codproducto}}</td>
						<td>{{dato.codigo}}</td>
						<td>{{dato.descripcion}}</td>
						<td>
							<input type="hidden" class="phuyu-input-inv" v-model="dato.codunidad" readonly>
							<input type="text" class="form-control number" v-model="dato.unidad" readonly>
						</td>
						<td width="10px">{{dato.marca}}</td>
						<td> 
							<input type="number" step="0.001" class="form-control number" v-model="dato.cantidad" v-on:keyup="phuyu_calcular(dato)"> 
						</td>
						<td> 
							<input type="number" step="0.01" class="form-control number" v-model="dato.preciocosto" v-on:keyup="phuyu_calcular(dato)">
						</td>
						<td> <input type="number" step="0.01" class="form-control number" v-model="dato.precioventa"> </td>
						<td> <input type="number" class="form-control number" v-model="dato.importe" readonly> </td>
						<td>
							<button type="button" class="btn btn-danger btn-sm" v-on:click="phuyu_itemquitar(index, dato)"><i class="bi bi-trash"></i></button>
						</td>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="8"> <center> <b>TOTAL COSTO (S/. IMPORTE VALORIZADO)</b> </center> </td>
						<td> <input type="number" class="form-control number" v-model="campos.importe" readonly> </td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
	<div class="phuyu-actions">
		<button type="button" class="btn btn-primary" v-on:click="phuyu_importarinventario()" v-bind:disabled="estado==1"><i class="bi bi-upload me-1"></i> Importar inventario</button>
		<button type="button" class="btn btn-success" v-on:click="phuyu_guardar()" v-bind:disabled="estado==1"><i class="bi bi-save me-1"></i> Guardar cambios</button>
		<button type="button" class="btn btn-light" v-on:click="phuyu_cerrar()"><i class="bi bi-x-lg me-1"></i> Cerrar</button>
		<button type="button" class="btn btn-warning" v-on:click="phuyu_actualizarprecios()" v-bind:disabled="estado==1"><i class="bi bi-currency-exchange me-1"></i> Actualizar precios en productos</button>
	</div>
	<div id="modal_subir" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content" align="center">
				<div class="modal-header">
					<h5 class="modal-title">Subir archivo CSV</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body">
					<form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar_archivo()">
						<div class="form-group">
							<span class="foto">
								<input type="file" name="archivo" id="foto" class="upload" />
							</span>
							<label for="foto"> <span><i class="bi bi-upload me-1"></i> Cargar archivo CSV</span> </label>
						</div>
						<div class="form-group text-center">
							<button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i> Guardar inventario</button>
							<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
						</div>
					</form>
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
<script src="<?php echo base_url();?>phuyu/phuyu_inventarios/inventario.js"></script>
