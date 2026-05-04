<style>
	#phuyu_unidades.phuyu-productos-grid .page-title-box {
		margin-bottom: 18px;
	}
	#phuyu_unidades.phuyu-productos-grid .page-title-box h4 {
		color: #212529;
		font-weight: 700;
		margin-bottom: 4px;
	}
	#phuyu_unidades.phuyu-productos-grid .phuyu-list-card {
		border: 1px solid #e9ebec;
		border-radius: 8px;
		box-shadow: 0 1px 2px rgba(56, 65, 74, 0.08);
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
	<div class="row page-title-box">
		<div class="col-12">
            <h4 id="title">Productos x Unidades</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
                    <li class="breadcrumb-item active">PXU</li>
                </ol>
            </nav>
        </div>
	</div>
	<div class="phuyu_body">
		<div class="card phuyu-list-card">
	
			<div class="card-body">
				<div class="row g-2 phuyu-toolbar mb-3">
					<div class="col-sm-12 col-md-5 col-lg-4 col-xxl-2 mb-1">
	                    <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                            <input class="form-control datatable-search border-start-0" v-model="buscar" placeholder="BUSCAR REGISTRO . . ." />
                        </div>
	                </div>
	                <div class="col-sm-12 col-md-7 col-lg-8 col-xxl-10 text-end mb-1">
	                    <div class="d-flex flex-wrap justify-content-end phuyu-toolbar">
	                        <button type="button" class="btn btn-info" v-on:click="cambiar_unidad()"><i class="bi bi-arrow-left-right me-1"></i> Cambiar unidad</button>
	                      
							<button type="button" class="btn btn-warning" v-on:click="productos_almacen()"><i class="bi bi-building me-1"></i> Asignar almacenes</button>

							<button type="button" class="btn btn-success" v-on:click="actualizar_stock()"><i class="bi bi-arrow-repeat me-1"></i> Actualizar stock</button>
	                    </div>
	                </div>
			    </div>
				<div class="table-responsive lista scroll-phuyu-view" style="height:300px;overflow:auto;overflow-x:hidden;">
					<table class="table table-hover align-middle mb-0">
						<thead>
							<tr>
								<th width="3%"> # </th>
								<th width="3%"> <i class="bi bi-record-circle"></i> </th>
								<th width="6%">CODIGO</th>
								<th width="25%">PRODUCTO</th>
								<th width="7%">UNIDAD</th>
								<th width="6%">STOCK</th>
								<th width="7%">P.COSTO</th>
								<th width="7%" style="background:#23c6c8;color:#fff;">S/.&nbsp;COSTO</th>
								<th width="6%">P.VENTA</th>
								<th width="6%" style="background:#1ab394;color:#fff;">S/.&nbsp;VENTA</th>
								<th width="7%">P.MIN.</th>
								<th width="8%" style="background:#f8ac59;color:#fff;">S/.&nbsp;P. MIN.</th>
								<th width="7%">P.XMAYOR</th>
								<th width="8%" style="background:#f8ac59;color:#fff;">S/.&nbsp;XMAYOR</th>
								<th width="7%">P.CREDI</th>
								<th width="7%" style="background:#8b8aeb;color:#fff;">S/.&nbsp;CREDITO</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="(dato, index) in buscar_productos">
								<td><input type="hidden" class="phuyu-input-inv" v-model="dato.codproducto" readonly>{{dato.nro}}</td>
								<td> <input type="radio" name="phuyu_seleccionar" v-on:click="phuyu_marcar(dato)" style="height:15px;width:15px;"> </td>
								<td style="font-size:10px;">{{dato.codigo}}</td>
								<td style="font-size:10px;">{{dato.descripcion}}</td>
								<td>
									<input type="hidden" v-model="dato.codunidad" readonly>
									{{dato.unidad}}
								</td>
								<td>{{dato.stock}}</td>
								<td><input type="text" class="form-control _number" v-model="dato.preciocosto"></td>
								<td><input type="text" class="form-control _number" v-model="dato.costo" readonly></td>
								<td><input type="text" class="form-control _number" v-model="dato.precioventa" v-on:keyup="phuyu_calcularitem(dato)"></td>
								<td><input type="text" class="form-control _number" v-model="dato.venta" readonly></td>

								<td><input type="text" class="form-control _number" v-model="dato.preciomin"></td>
								<td><input type="text" class="form-control _number" v-model="dato.minimo" readonly></td>
								<td><input type="text" class="form-control _number" v-model="dato.precioxmayor" readonly></td>
								<td><input type="text" class="form-control _number" v-model="dato.mayor"></td>
								<td><input type="text" class="form-control _number" v-model="dato.pventacredito"></td>
								<td><input type="text" class="form-control _number" v-model="dato.credito" readonly></td>
							</tr>
						</tbody>
						<tfoot>
							<tr v-for="dato in totales">
								<td colspan="7" style="text-align:right;"> <b>TOTALES GENERALES</b> </td>
								<td><b>{{dato.costo}}</b></td> <td></td>
								<td><b>{{dato.venta}}</b></td> <td></td>
								<td><b>{{dato.minimox}}</b></td> <td></td>
								<td><b>{{dato.mayor}}</b></td> <td></td>
								<td><b>{{dato.credito}}</b></td>
							</tr>
							<tr v-for="dato in totales">
								<td colspan="7" style="text-align:right;"> <b>RESUMEN TOTAL (S/. VENTA - S/. COSTO)</b> </td>
								<td colspan="5"><b>{{dato.total}}</b></td>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>
	</div>

	<div id="modal_cambiar_unidad" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header modal-phuyu-titulo">
					<h5 class="modal-title"> <b>{{campos.codigo}} - {{campos.descripcion}} | UNIDAD MEDIDA: {{campos.unidad}}</b> </h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
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
							<select class="form-select" id="codunidad">
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
							<button type="button" class="btn btn-primary w-100" v-on:click="guardar_cambiar_unidad" v-bind:disabled="estado==1">CAMBIAR UNIDAD DE MEDIDA</button>
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
