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
	#phuyu_inventario.phuyu-inventario-operacion .form-select,
	#phuyu_inventario.phuyu-inventario-operacion .phuyu-input-inv {
		border: 1px solid #d9e2ef;
		border-radius: 6px;
		box-shadow: none;
		min-height: 34px;
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
			<div class="col-md-3 col-xs-12"> <h5 class="mb-0">PRODUCTOS DEL INVENTARIO</h5> </div>

			<div class="col-md-3 col-xs-12">
				<input type="text" class="form-control" v-model="buscar" placeholder="BUSCAR PRODUCTO . . .">
			</div>
			<div class="col-md-2">
				<select class="form-select" id="codlinea">
					<option value="0">TODAS LAS LINEAS</option>
					<?php
		    			foreach ($lineas as $key => $value) { ?>
		    				<option value="<?php echo $value["codlinea"];?>">
		    					<?php echo $value["descripcion"];?>
		    				</option>
		    			<?php }
		    		?>
				</select>
			</div>
			<div class="col-md-2">
				<select class="form-select" v-model="tiporeporte">
					<option value="0">LISTA GENERAL</option>
					<option value="1">PRODUCTOS CON STOCK</option>
					<option value="2">PRODUCTOS SIN STOCK</option>
				</select>
			</div>
			<div class="col-md-2 col-xs-12 text-end">
				<button type="button" class="btn btn-success" v-on:click="phuyu_pdf()"><i class="bi bi-printer me-1"></i> PDF</button>
				<button type="button" class="btn btn-warning" v-on:click="phuyu_excel()"><i class="bi bi-file-earmark-excel me-1"></i> Excel</button>
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
						<th width="10px">CODIGO</th>
						<th>PRODUCTO</th>
						<th width="10px">UNIDAD</th>
						<th>MARCA</th>
						<th width="10%">CANTIDAD</th>
						<th width="10%">P. COSTO</th>
						<th width="10%">P. VENTA</th>
						<th width="10%">IMPORTE</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(dato, index) in buscar_productos">
						<td>{{index + 1}}</td>
						<td> <input type="text" class="phuyu-input-inv" v-model="dato.codigo" readonly> </td>
						<td> <input type="text" class="phuyu-input-inv" v-model="dato.descripcion" readonly> </td>
						<td>
							<input type="hidden" class="phuyu-input-inv" v-model="dato.codunidad" readonly>
							<input type="text" class="phuyu-input-inv" v-model="dato.unidad" readonly>
						</td>
						<td width="10px">{{dato.marca}}</td>
						<td> 
							<input type="number" class="phuyu-input-inv" v-model="dato.cantidad" readonly> 
						</td>
						<td> 
							<input type="number" class="phuyu-input-inv" v-model="dato.preciocosto" readonly>
						</td>
						<td> <input type="number" class="phuyu-input-inv" v-model="dato.precioventa" readonly> </td>
						<td> <input type="number" class="phuyu-input-inv" v-model="dato.importe" readonly> </td>
					</tr>
				</tbody>
				<tfoot>
					<tr>
						<td colspan="8"> <center> <b>TOTAL COSTO (S/. IMPORTE VALORIZADO)</b> </center> </td>
						<td> <input type="number" class="phuyu-input-inv" v-model="campos.importe" readonly> </td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
	
	<div class="phuyu-actions">
		<button type="button" class="btn btn-light" v-on:click="phuyu_cerrar()"><i class="bi bi-x-lg me-1"></i> Cerrar vista</button>
	</div>

	<div id="modal_reportes" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog" style="width:100%;margin:0px;">
			<div class="modal-content" align="center">
				<div class="modal-header">
					<h5 class="modal-title"><?php echo $_SESSION["phuyu_empresa"]." - ".$_SESSION["phuyu_sucursal"];?></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body" id="reportes_modal" style="height:450px;padding:0px;">
					<iframe id="phuyu_pdf" src="" style="width:100%; height:100%; border:none;"> </iframe>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_inventarios/inventario.js"></script>
<script>
	var pantalla = jQuery(document).height(); $("#reportes_modal").css({height: pantalla - 65});
</script>
