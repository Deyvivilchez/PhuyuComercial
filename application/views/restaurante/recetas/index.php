<style>
	#phuyu_operacion.phuyu-recetas .phuyu-page-title {
		display: flex;
		align-items: center;
		gap: .75rem;
		margin-bottom: 1rem;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-page-icon {
		width: 44px;
		height: 44px;
		border-radius: 12px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: rgba(10, 132, 117, .10);
		color: #0a8475;
		font-size: 1.25rem;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-card {
		border: 1px solid rgba(10, 132, 117, .12);
		border-radius: .9rem;
		box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
	}

	#phuyu_operacion.phuyu-recetas .phuyu-toolbar {
		display: grid;
		grid-template-columns: minmax(220px, 1fr) 180px repeat(2, 160px) auto auto;
		gap: .65rem;
		align-items: end;
		margin-bottom: 1rem;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-search {
		position: relative;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-search .form-control {
		padding-left: 2.35rem;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-search i {
		position: absolute;
		left: .85rem;
		top: 50%;
		transform: translateY(-50%);
		color: #878a99;
	}

	#phuyu_operacion.phuyu-recetas .form-label {
		font-size: .72rem;
		font-weight: 800;
		text-transform: uppercase;
		color: #495057;
		margin-bottom: .35rem;
	}

	#phuyu_operacion.phuyu-recetas .form-control,
	#phuyu_operacion.phuyu-recetas .form-select {
		min-height: 40px;
		border-color: rgba(10, 132, 117, .18);
	}

	#phuyu_operacion.phuyu-recetas .phuyu-table-wrap {
		border: 1px solid rgba(10, 132, 117, .12);
		border-radius: .75rem;
		overflow: hidden;
	}

	#phuyu_operacion.phuyu-recetas table thead th {
		background: #f8fafc;
		color: #495057;
		font-size: .72rem;
		font-weight: 800;
		text-transform: uppercase;
		white-space: nowrap;
	}

	#phuyu_operacion.phuyu-recetas table tbody td {
		font-size: .84rem;
		vertical-align: middle;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-recipe-list {
		max-height: 58px;
		overflow: auto;
		background: #f8fafc;
		border-radius: .55rem;
		padding: .45rem .65rem;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-modal-scroll {
		height: calc(100vh - 210px);
		min-height: 320px;
		overflow: auto;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-products-panel {
		height: calc(100vh - 220px);
		min-height: 320px;
		overflow: auto;
		border: 1px solid rgba(10, 132, 117, .12);
		border-radius: .75rem;
		padding: .75rem;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-cost-summary {
		background: #f8fafc;
		border-top: 1px solid rgba(10, 132, 117, .12);
		font-weight: 800;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-ingredient-item {
		border-bottom: 1px solid rgba(10, 132, 117, .10);
		padding: .8rem .25rem;
		cursor: pointer;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-ingredient-item:hover {
		background: #f8fafc;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-badge {
		display: inline-flex;
		align-items: center;
		gap: .25rem;
		border-radius: 999px;
		padding: .15rem .45rem;
		font-size: .66rem;
		font-weight: 800;
		background: #eef2ff;
		color: #364fc7;
	}

	@media (max-width: 991.98px) {
		#phuyu_operacion.phuyu-recetas .phuyu-toolbar {
			grid-template-columns: 1fr 1fr;
		}

		#phuyu_operacion.phuyu-recetas .phuyu-search {
			grid-column: 1 / -1;
		}
	}

	@media (max-width: 575.98px) {
		#phuyu_operacion.phuyu-recetas .phuyu-toolbar {
			grid-template-columns: 1fr;
		}
	}
</style>

<div id="phuyu_operacion" class="phuyu-recetas">
	<div class="phuyu-page-title">
		<div class="phuyu-page-icon"><i class="bi bi-journal-check"></i></div>
		<div>
			<div class="text-muted small text-uppercase fw-semibold">Restobar</div>
			<h4 class="mb-0 fw-bold">Productos para la venta - recetas</h4>
		</div>
	</div>

	<div class="phuyu_body">
		<div class="card phuyu-card">
			<div class="card-body">
				<div class="phuyu-toolbar">
					<div class="phuyu-search">
						<i class="bi bi-search"></i>
						<input type="text" class="form-control" v-model="buscar" v-on:keyup="phuyu_filtrar_productos()" placeholder="Buscar producto">
					</div>

					<div>
						<label class="form-label">Mostrar</label>
						<select class="form-select" v-model="filtro_producto" v-on:change="phuyu_filtrar_productos(true)">
							<option value="preparados">Preparados / platos</option>
							<option value="venta">Para venta</option>
							<option value="con_receta">Con receta</option>
							<option value="sin_receta">Sin receta</option>
							<option value="todos">Todos</option>
						</select>
					</div>

					<div>
						<label class="form-label">Desde</label>
						<input type="text" class="form-control datepicker" id="fechadesde" value="<?php echo date('Y-m-d');?>" autocomplete="off">
					</div>

					<div>
						<label class="form-label">Hasta</label>
						<input type="text" class="form-control datepicker" id="fechahasta" value="<?php echo date('Y-m-d');?>" autocomplete="off">
					</div>

					<button type="button" class="btn btn-primary" v-on:click="consumo_total()">
						<i class="bi bi-printer me-1"></i> Totalizado
					</button>

					<button type="button" class="btn btn-warning" v-on:click="consumo_fechas()">
						<i class="bi bi-calendar-range me-1"></i> Por fechas
					</button>
				</div>

				<div class="table-responsive phuyu-table-wrap lista" style="overflow-y:auto;height:420px;">
					<table class="table table-hover align-middle mb-0">
						<thead>
							<tr>
								<th width="70">#</th>
								<th>Producto</th>
								<th width="110">Costo</th>
								<th width="110">Venta</th>
								<th width="120">Costo receta</th>
								<th>Receta actual</th>
								<th class="text-center" width="110">Editar</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in buscar_productos">
								<td class="text-muted fw-semibold">{{dato.nro}}</td>
								<td>
									<div class="fw-semibold">{{dato.descripcion}}</div>
									<div class="text-muted small">{{dato.unidad}}</div>
								</td>
								<td>S/. <b>{{dato.preciocosto}}</b></td>
								<td>S/. <b>{{dato.precioventa}}</b></td>
								<td>S/. <b>{{dato.costo_receta}}</b></td>
								<td>
									<div class="phuyu-recipe-list">
										<div v-for="d in dato.receta" class="small">
											<i class="bi bi-check2 text-success me-1"></i>
											Cant. {{d.cantidad}} <strong>{{d.producto}} - {{d.unidad}}</strong>
											<span class="text-muted">S/. {{phuyu_moneda(d.costototal)}}</span>
										</div>
										<div v-if="dato.receta.length==0" class="text-muted small">Sin receta registrada</div>
									</div>
								</td>
								<td class="text-center">
									<button type="button" class="btn btn-success btn-sm" v-on:click="phuyu_receta(dato)">
										<i class="bi bi-pencil-square me-1"></i> Receta
									</button>
								</td>
							</tr>
							<tr v-if="buscar_productos.length==0">
								<td colspan="7" class="text-center text-muted py-3">
									<i class="bi bi-inbox me-1"></i> Sin productos
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3">
					<div class="small text-muted">
						Mostrando página {{paginacion.actual}} de {{paginacion.ultima}} · {{paginacion.total}} producto(s)
					</div>
					<div class="d-flex align-items-center gap-2">
						<select class="form-select form-select-sm" style="width: 86px;" v-model.number="paginacion.limit" v-on:change="phuyu_cambiar_pagina(1, true)">
							<option value="10">10</option>
							<option value="25">25</option>
							<option value="50">50</option>
							<option value="100">100</option>
						</select>
						<ul class="pagination pagination-sm mb-0">
							<li class="page-item" v-bind:class="{disabled: paginacion.actual <= 1}">
								<a class="page-link" href="#" v-on:click.prevent="phuyu_cambiar_pagina(paginacion.actual - 1)">
									<i class="bi bi-chevron-left"></i>
								</a>
							</li>
							<li class="page-item" v-for="pag in phuyu_paginas" v-bind:class="{active: pag == paginacion.actual}">
								<a class="page-link" href="#" v-on:click.prevent="phuyu_cambiar_pagina(pag)">{{pag}}</a>
							</li>
							<li class="page-item" v-bind:class="{disabled: paginacion.actual >= paginacion.ultima}">
								<a class="page-link" href="#" v-on:click.prevent="phuyu_cambiar_pagina(paginacion.actual + 1)">
									<i class="bi bi-chevron-right"></i>
								</a>
							</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="modal_receta" class="modal fade" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-xl modal-dialog-scrollable">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title fw-bold" id="titulo_receta"></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>
				<div class="modal-body">
					<div class="row g-3">
						<div class="col-12 col-lg-6">
							<div class="d-flex align-items-center justify-content-between mb-2">
								<div>
									<h6 class="mb-0 fw-bold">Productos en la receta</h6>
									<div class="small text-muted">Costo total: <strong>S/. {{costo_receta}}</strong></div>
								</div>
								<div class="d-flex gap-2">
									<button type="button" class="btn btn-outline-primary btn-sm" v-on:click="phuyu_actualizar_costo_plato()" v-bind:disabled="detalle.length==0">
										<i class="bi bi-arrow-repeat me-1"></i> Actualizar costo plato
									</button>
									<button type="button" class="btn btn-primary btn-sm" v-on:click="phuyu_guardar()" v-bind:disabled="estado==1">
										<i class="bi bi-save me-1"></i> Guardar
									</button>
								</div>
							</div>
							<div class="table-responsive phuyu-table-wrap phuyu-modal-scroll">
								<table class="table table-hover align-middle mb-0">
									<thead>
										<tr>
											<th>Producto</th>
											<th width="120">Unidad</th>
											<th width="120">Cantidad</th>
											<th width="100">P. costo</th>
											<th width="110">Subtotal</th>
											<th class="text-center" width="70"><i class="bi bi-trash3"></i></th>
										</tr>
									</thead>
									<tbody>
										<tr v-for="(dato,index) in detalle">
											<td class="fw-semibold">{{dato.producto}}</td>
											<td>
												<select class="form-select form-select-sm" v-model.number="dato.codunidad" v-on:change="phuyu_cambiarunidad(dato)">
													<option v-for="unidad in dato.unidades" v-bind:value="unidad.codunidad">{{unidad.unidad}}</option>
												</select>
											</td>
											<td>
												<input type="number" step="0.001" class="form-control number" v-model.number="dato.cantidad" min="0.001" required>
											</td>
											<td>S/. {{phuyu_moneda(dato.preciocosto)}}</td>
											<td>S/. {{phuyu_moneda(dato.cantidad * dato.preciocosto)}}</td>
											<td class="text-center">
												<button type="button" class="btn btn-danger btn-sm" v-on:click="phuyu_deleteitem(index,dato)">
													<i class="bi bi-trash3"></i>
												</button>
											</td>
										</tr>
										<tr v-if="detalle.length==0">
											<td colspan="6" class="text-center text-muted py-3">Sin ingredientes</td>
										</tr>
									</tbody>
									<tfoot v-if="detalle.length>0">
										<tr class="phuyu-cost-summary">
											<td colspan="4" class="text-end">Costo total receta</td>
											<td>S/. {{costo_receta}}</td>
											<td></td>
										</tr>
										<tr>
											<td colspan="4" class="text-end text-muted">Precio venta plato</td>
											<td>S/. {{phuyu_moneda(campos.precioventa)}}</td>
											<td></td>
										</tr>
										<tr>
											<td colspan="4" class="text-end text-muted">Diferencia</td>
											<td>S/. {{utilidad_receta}}</td>
											<td></td>
										</tr>
									</tfoot>
								</table>
							</div>
						</div>
						<div class="col-12 col-lg-6">
							<h6 class="mb-2 fw-bold">Buscar ingredientes</h6>
							<div class="row g-2 mb-2">
								<div class="col-md-8">
									<div class="phuyu-search">
										<i class="bi bi-search"></i>
										<input type="text" class="form-control" v-model="buscar_ingrediente" v-on:keyup="phuyu_buscar_ingredientes()" placeholder="Buscar insumo">
									</div>
								</div>
								<div class="col-md-4">
									<select class="form-select" v-model="filtro_ingrediente" v-on:change="phuyu_buscar_ingredientes()">
										<option value="insumos">Insumos</option>
										<option value="venta">Para venta</option>
										<option value="preparados">Preparados</option>
										<option value="todos">Todos</option>
									</select>
								</div>
							</div>
							<div class="phuyu-products-panel" id="lista_productos">
								<div class="phuyu-ingredient-item" v-for="producto in ingredientes" v-on:click="phuyu_additem(producto)">
									<div class="d-flex justify-content-between gap-2">
										<div>
											<div class="fw-bold">{{producto.descripcion}}</div>
											<div class="small text-muted">{{producto.codigo}} · {{producto.marca}}</div>
										</div>
										<div class="text-end">
											<div class="fw-bold text-success">S/. {{phuyu_moneda(producto.preciocosto)}}</div>
											<div class="small" v-bind:class="producto.stock > 0 ? 'text-success' : 'text-danger'">
												Stock {{producto.stock}} {{producto.unidad}}
											</div>
										</div>
									</div>
									<div class="mt-1 d-flex flex-wrap gap-1">
										<span class="phuyu-badge" v-if="producto.es_insumo==1">Insumo</span>
										<span class="phuyu-badge" v-if="producto.es_preparado==1">Preparado</span>
										<span class="phuyu-badge" v-if="producto.es_venta==1">Venta</span>
									</div>
								</div>
								<div class="text-center text-muted py-4" v-if="ingredientes.length==0">
									<i class="bi bi-inbox me-1"></i> Sin ingredientes
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-light" data-bs-dismiss="modal">
						<i class="bi bi-x-circle me-1"></i> Cerrar
					</button>
				</div>
			</div>
		</div>
	</div>

	<div id="modal_reportes" class="modal fade" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-fullscreen">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title fw-bold"><?php echo $_SESSION["phuyu_empresa"];?></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>
				<div class="modal-body p-0" id="reportes_modal">
					<iframe id="phuyu_pdf" src="" style="width:100%; height:100%; border:none;"> </iframe>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	var pantalla = jQuery(document).height();
	$("#reportes_modal").css({height: pantalla - 65});
	var productos = pantalla - 250;
	$(".lista").css("height", productos + "px");
</script>

<script src="<?php echo base_url();?>phuyu/phuyu_restaurante/recetas.js"></script>
<script>
	$(".datepicker").datetimepicker({format: 'YYYY-MM-DD',ignoreReadonly: true}).attr("readonly","true");
</script>
