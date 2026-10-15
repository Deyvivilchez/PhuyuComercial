<style>
	#phuyu_unidades.phuyu-productos-grid .phuyu-header-card {
		background: #fff;
		border: 1px solid #e9ebec;
		border-radius: 8px;
		box-shadow: 0 1px 2px rgba(56, 65, 74, 0.08);
		margin-bottom: 14px;
		padding: 12px;
	}
	#phuyu_unidades.phuyu-productos-grid .phuyu-toolbar {
		align-items: center;
		gap: 8px;
	}
	#phuyu_unidades.phuyu-productos-grid .form-control,
	#phuyu_unidades.phuyu-productos-grid .form-select {
		border: 1px solid #d9e2ef;
		border-radius: 6px;
		box-shadow: none;
		min-height: 36px;
	}
	#phuyu_unidades.phuyu-productos-grid .table {
		font-size: 12px;
	}
	#phuyu_unidades.phuyu-productos-grid .table thead th {
		background: #f3f6f9;
		color: #495057;
		font-size: 11px;
		text-transform: uppercase;
		white-space: nowrap;
	}
	#phuyu_unidades.phuyu-productos-grid .modal-content {
		border: 0;
		border-radius: 8px;
		box-shadow: 0 10px 30px rgba(15, 23, 42, 0.14);
	}
	#phuyu_unidades.phuyu-productos-grid .modal-header {
		background: #f3f6f9;
		border-bottom: 1px solid #e9ebec;
	}
</style>

<div id="phuyu_unidades" class="phuyu-productos-grid">
	<div class="phuyu_header phuyu-header-card">
		<div class="row g-2 align-items-center phuyu_header_title">
			<div class="col-md-3 col-xs-12"> <h5 class="mb-0">PRODUCTOS X UNIDADES</h5> </div>
			<div class="col-md-2"> 
				<button type="button" class="btn btn-warning w-100" v-on:click="productos_almacen()"><i class="bi bi-building me-1"></i> Asignar almacenes</button>
			</div>
			<div class="col-md-2"> 
				<button type="button" class="btn btn-success w-100" v-on:click="actualizar_stock()"><i class="bi bi-arrow-repeat me-1"></i> Actualizar stock</button>
			</div>
		    <div class="col-md-5 col-xs-12">
				<div class="input-group">
					<span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
					<input type="text" class="form-control border-start-0" v-model="buscar" placeholder="BUSCAR PRODUCTO . . .">
				</div>
		    </div>
	    </div>
	</div>
	
	<div class="phuyu_body lista scroll-phuyu-view" style="height:300px;overflow:auto;overflow-x:hidden;">
		<div class="table-responsive">
			<table class="table table-hover align-middle mb-0">
				<thead>
					<tr>
						<th width="3%"> # </th>
						<th width="3%"> <i class="bi bi-record-circle"></i> </th>
						<th width="33%">PRODUCTO</th>
						<th width="7%">UNIDAD</th>
						<th width="3%">F.</th>
						<th width="7%">STOCK</th>
						<th width="15%">P. CON DESCUENTO</th>
						<th width="8%" style="background:#23c6c8;color:#fff;">S/.&nbsp;TOTAL</th>
						<th width="15%">P. CATALOGO</th>
						<th width="8%" style="background:#1ab394;color:#fff;">S/.&nbsp;TOTAL</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(dato, index) in buscar_productos">
						<td>{{dato.nro}}</td>
						<td> <input type="radio" name="phuyu_seleccionar" v-on:click="phuyu_marcar(dato)" style="height:15px;width:15px;"> </td>
						<td style="font-size:10px;">{{dato.descripcion}}</td>
						<td>{{dato.unidad}}</td>
						<td>{{dato.factor}}</td>
						<td>{{dato.stock}}</td>
						<td>{{dato.preciocosto}}</td>
						<td style="background:#23c6c8;color:#fff;">{{dato.costo}}</td>
						<td>{{dato.precioventa}}</td>
						<td style="background:#1ab394;color:#fff;">{{dato.venta}}</td>
					</tr>
				</tbody>
				<tfoot>
					<tr v-for="dato in totales">
						<td colspan="7" style="text-align:right;"> <b>TOTALES GENERALES</b> </td>
						<td><b>{{dato.costo}}</b></td> <td></td>
						<td><b>{{dato.venta}}</b></td>
					</tr>
					<tr v-for="dato in totales">
						<td colspan="7" style="text-align:right;"> <b>RESUMEN TOTAL (S/. CATALAGO - S/. DESCUENTO)</b> </td>
						<td colspan="3"><b>{{dato.venta - dato.costo}}</b></td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>

	<div id="modal_cambiar_unidad" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">{{campos.descripcion}} | UNIDAD MEDIDA: {{campos.unidad}}</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body" style="height:330px">
					<div class="row">
						<div class="col-md-6 col-xs-12 text-center">
							<h5><b>UNIDAD ACTUAL</b></h5> <hr>
							<h4><span class="badge bg-danger">UNIDAD MEDIDA: {{campos.unidad}}</span></h4> <br>

							<ul class="list-inline widget_tally">
								<li>
									<p> <span class="month"><b>FACTOR MEDIDA</b></span> <span class="count">{{campos.factor}}</span> </p>
								</li>
                                <li>
                                  	<p> <span class="month"><b>STOCK ACTUAL</b></span> <span class="count">{{campos.stock}}</span> </p>
                                </li>
                                <li>
                                  	<p> <span class="month"><b>PRECIO COSTO</b></span> <span class="count">{{campos.preciocosto}}</span> </p>
                                </li>
                                <li>
                                  	<p> <span class="month"><b>PRECIO VENTA</b></span> <span class="count">{{campos.precioventa}}</span> </p>
                                </li>
                                <li>
                                  	<p> <span class="month"><b>PRECIO MINIMO</b></span> <span class="count">{{campos.preciomin}}</span> </p>
                                </li>
                            </ul>
						</div>
						<div class="col-md-6 col-xs-12">
							<h5 class="text-center"><b>UNIDAD NUEVA</b></h5> <hr>
							<h5>SELECCIONE NUEVA UNIDAD</h5>
							<select class="form-control" id="codunidad">
								<option value="">SELECCIONE UNIDAD</option>
								<?php 
									foreach ($unidades as $key => $value) { ?>
										<option value="<?php echo $value['codunidad'];?>"><?php echo $value["descripcion"]."(OFICIAL: ".$value["oficial"].")";?></option>
									<?php }
								?>
							</select> <br>
							<div class="alert alert-danger text-center">
								ATENCION USUARIO: AL CAMBIAR LA UNIDAD DE MEDIDA, SE REEMPLAZARA EN LAS VENTAS, COMPRAS, KARDEX E INVENTARIO
							</div>
							<button type="button" class="btn btn-success w-100" v-on:click="guardar_cambiar_unidad" v-bind:disabled="estado==1">CAMBIAR UNIDAD DE MEDIDA</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_almacen/unidades.js"></script>

<script> 
	var div_altura = jQuery(document).height(); var productos = div_altura - 200; $(".lista").css("height",productos+"px");
</script>
