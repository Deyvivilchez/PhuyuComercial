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

	#phuyu_operacion.phuyu-recetas .card-body {
		padding: 1rem;
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

	#phuyu_operacion.phuyu-recetas .phuyu-main-table tbody tr {
		border-bottom: 1px solid #edf2f7;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-main-table tbody tr:hover {
		background: #fbfefd;
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

	#phuyu_operacion.phuyu-recetas .phuyu-actions {
		display: inline-flex;
		align-items: center;
		justify-content: flex-end;
		gap: .25rem;
		white-space: nowrap;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-actions .btn {
		width: 34px;
		height: 34px;
		min-width: 34px;
		padding: 0;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border-radius: 8px;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-actions .phuyu-create-btn {
		width: auto;
		min-width: 82px;
		padding: 0 .65rem;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-row-number {
		width: 28px;
		height: 28px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border-radius: 50%;
		background: #f1f5f9;
		color: #64748b;
		font-weight: 800;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-money {
		font-weight: 800;
		white-space: nowrap;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-money-cost {
		color: #475569;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-money-sale {
		color: #047857;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-money-recipe {
		color: #1d4ed8;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-modal-scroll {
		max-height: 340px;
		min-height: 0;
		overflow-y: auto;
		overflow-x: hidden;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-modal-scroll table {
		table-layout: fixed;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-col-producto {
		width: 36%;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-col-unidad {
		width: 17%;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-col-cantidad {
		width: 17%;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-col-costo {
		width: 13%;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-col-subtotal {
		width: 13%;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-col-eliminar {
		width: 4%;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-modal-scroll .form-select,
	#phuyu_operacion.phuyu-recetas .phuyu-modal-scroll .form-control {
		width: 100%;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-modal-scroll thead th {
		position: sticky;
		top: 0;
		z-index: 2;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-modal-scroll tbody td {
		height: 58px;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-recipe-modal .modal-content {
		border: 0;
		border-radius: 14px;
		overflow: hidden;
		box-shadow: 0 24px 70px rgba(15, 23, 42, .22);
	}

	#phuyu_operacion.phuyu-recetas .phuyu-recipe-modal .modal-body {
		overflow: hidden;
		max-height: calc(100vh - 190px);
	}

	#phuyu_operacion.phuyu-recetas .phuyu-recipe-modal .modal-header {
		background: linear-gradient(90deg, #40528b 0%, #5877db 100%);
		color: #fff;
		border-bottom: 0;
		padding: 1rem 1.15rem;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-recipe-modal .btn-close {
		filter: invert(1) grayscale(100%) brightness(200%);
		opacity: .9;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-modal-title {
		display: flex;
		align-items: center;
		gap: .7rem;
		min-width: 0;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-modal-title-icon {
		width: 38px;
		height: 38px;
		border-radius: 10px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: rgba(255, 255, 255, .16);
		font-size: 1.15rem;
		flex: 0 0 auto;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-modal-title-text {
		font-size: .95rem;
		line-height: 1.25;
		white-space: normal;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-mode-badge {
		border: 1px solid rgba(255, 255, 255, .35);
		border-radius: 999px;
		color: #fff;
		background: rgba(255, 255, 255, .14);
		padding: .25rem .65rem;
		font-size: .68rem;
		font-weight: 800;
		text-transform: uppercase;
		white-space: nowrap;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-recipe-panel {
		border: 1px solid rgba(64, 82, 139, .14);
		border-radius: 12px;
		background: #fff;
		overflow: hidden;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-panel-head {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: .75rem;
		padding: .55rem .85rem;
		background: #f8fafc;
		border-bottom: 1px solid rgba(64, 82, 139, .10);
	}

	#phuyu_operacion.phuyu-recetas .phuyu-panel-head h6 {
		color: #1f2b52;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-summary-strip {
		display: grid;
		grid-template-columns: repeat(3, minmax(0, 1fr));
		border-bottom: 1px solid rgba(64, 82, 139, .10);
		background: #fff;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-summary-item {
		padding: .75rem .9rem;
		border-right: 1px solid rgba(64, 82, 139, .08);
	}

	#phuyu_operacion.phuyu-recetas .phuyu-summary-item:last-child {
		border-right: 0;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-summary-label {
		font-size: .68rem;
		font-weight: 800;
		text-transform: uppercase;
		color: #64748b;
		margin-bottom: .18rem;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-summary-value {
		font-weight: 900;
		color: #1f2b52;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-calculator-box {
		padding: .9rem;
		background: #fbfdff;
		border-top: 1px solid rgba(64, 82, 139, .10);
	}

	#phuyu_operacion.phuyu-recetas .phuyu-calculator-note {
		border-left: 3px solid #00ab91;
		padding-left: .65rem;
		color: #64748b;
		font-size: .73rem;
		margin-bottom: .7rem;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-ingredient-panel-title {
		display: flex;
		align-items: center;
		gap: .5rem;
		margin-bottom: .7rem;
		color: #1f2b52;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-ingredient-panel-title i {
		width: 30px;
		height: 30px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border-radius: 8px;
		background: rgba(0, 171, 145, .10);
		color: #00ab91;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-recipe-actions {
		border: 1px solid rgba(10, 132, 117, .12);
		border-top: 0;
		border-radius: 0 0 .75rem .75rem;
		background: #fbfdff;
		padding: .65rem .75rem;
		justify-content: flex-end;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-products-panel {
		height: calc(100vh - 220px);
		min-height: 320px;
		overflow: auto;
		border: 1px solid rgba(10, 132, 117, .12);
		border-radius: .75rem;
		padding: .75rem;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-add-ingredient-box {
		border: 0;
		background: transparent;
		padding: 0;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-selected-help {
		padding-top: .35rem;
		color: #64748b;
		font-size: .75rem;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-ingredient-picker {
		display: grid;
		grid-template-columns: 170px minmax(260px, 1fr);
		gap: .6rem;
		margin-bottom: .6rem;
		align-items: end;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-ingredient-add {
		display: grid;
		grid-template-columns: minmax(260px, 1fr) 130px;
		gap: .6rem;
		align-items: end;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-inline-ingredient-search {
		padding: .65rem .85rem .8rem;
		border-bottom: 1px solid rgba(64, 82, 139, .10);
		background: #fff;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-add-header {
		display: none;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-add-title {
		display: flex;
		align-items: center;
		gap: .55rem;
		color: #1f2b52;
		font-size: .78rem;
		font-weight: 800;
		text-transform: uppercase;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-add-title i {
		width: 24px;
		height: 24px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		border-radius: 8px;
		background: rgba(0, 171, 145, .10);
		color: #00ab91;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-add-count {
		border-radius: 999px;
		background: #eef2ff;
		color: #40528b;
		font-size: .68rem;
		font-weight: 800;
		padding: .2rem .55rem;
		white-space: nowrap;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-title-line {
		display: flex;
		align-items: center;
		gap: .55rem;
		flex-wrap: wrap;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-list-title-row {
		display: flex;
		align-items: center;
		gap: .55rem;
		padding: .65rem .75rem;
		background: #f8fafc;
		border-top: 1px solid rgba(64, 82, 139, .10);
		border-bottom: 1px solid rgba(64, 82, 139, .10);
		font-size: .78rem;
		font-weight: 800;
		text-transform: uppercase;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-head-actions .btn {
		min-height: 34px;
		padding: .35rem .65rem;
		font-size: .76rem;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-inline-ingredient-search .form-control,
	#phuyu_operacion.phuyu-recetas .phuyu-inline-ingredient-search .form-select {
		background-color: #fff;
		border-color: rgba(64, 82, 139, .16);
	}

	#phuyu_operacion.phuyu-recetas .phuyu-inline-ingredient-search .select2-container {
		width: 100% !important;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-inline-ingredient-search .select2-selection--single {
		height: 40px !important;
		border: 1px solid rgba(64, 82, 139, .16) !important;
		border-radius: .375rem !important;
		display: flex !important;
		align-items: center !important;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-inline-ingredient-search .select2-selection__rendered {
		line-height: 40px !important;
		color: #343a40 !important;
		font-size: .84rem !important;
		padding-left: .75rem !important;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-inline-ingredient-search .select2-selection__arrow {
		height: 40px !important;
	}

	#modal_receta .select2-dropdown {
		border-color: rgba(64, 82, 139, .20);
		box-shadow: 0 18px 36px rgba(15, 23, 42, .14);
	}

	#modal_receta .select2-results__option {
		color: #1f2b52;
		font-size: .84rem;
		padding: .55rem .75rem;
	}

	#modal_receta .select2-results__option--highlighted[aria-selected],
	#modal_receta .select2-results__option--highlighted[data-selected] {
		background: #40528b !important;
		color: #fff !important;
	}

	#modal_receta .select2-results__option[aria-selected="true"],
	#modal_receta .select2-results__option[data-selected="true"] {
		background: rgba(0, 171, 145, .12);
		color: #1f2b52;
	}

	#modal_receta .select2-search__field {
		border-color: rgba(64, 82, 139, .20) !important;
		outline: none;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-inline-ingredient-search .btn-success {
		background: #00ab91;
		border-color: #00ab91;
		font-weight: 800;
		min-height: 40px;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-inline-ingredient-search .btn-success:hover {
		background: #009b84;
		border-color: #009b84;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-recipe-panel .table tbody td {
		padding-top: .55rem;
		padding-bottom: .55rem;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-cost-summary {
		background: #f8fafc;
		border-top: 1px solid rgba(10, 132, 117, .12);
		font-weight: 800;
	}

	#phuyu_operacion.phuyu-recetas .phuyu-ingredient-item {
		border-bottom: 1px solid rgba(10, 132, 117, .10);
		padding: .8rem .65rem;
		cursor: pointer;
		border-radius: 10px;
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

		#phuyu_operacion.phuyu-recetas .phuyu-summary-strip {
			grid-template-columns: 1fr;
		}

		#phuyu_operacion.phuyu-recetas .phuyu-summary-item {
			border-right: 0;
			border-bottom: 1px solid rgba(64, 82, 139, .08);
		}

		#phuyu_operacion.phuyu-recetas .phuyu-ingredient-picker,
		#phuyu_operacion.phuyu-recetas .phuyu-ingredient-add {
			grid-template-columns: 1fr;
		}

		#phuyu_operacion.phuyu-recetas .phuyu-add-header {
			align-items: flex-start;
			flex-direction: column;
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
					<table class="table table-hover align-middle mb-0 phuyu-main-table">
						<thead>
							<tr>
								<th width="70">#</th>
								<th>Producto</th>
								<th width="120">Costo guardado</th>
								<th width="120">Costo receta</th>
								<th width="110">Venta</th>
								<th width="120">% utilidad</th>
								<th width="160">Estado receta</th>
								<th class="text-center" width="270">Acciones</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in buscar_productos">
								<td><span class="phuyu-row-number">{{dato.nro}}</span></td>
								<td>
									<div class="fw-semibold">{{dato.descripcion}}</div>
									<div class="text-muted small">{{dato.unidad}}</div>
								</td>
								<td><span class="phuyu-money phuyu-money-cost">S/. {{dato.preciocosto}}</span></td>
								<td><span class="phuyu-money phuyu-money-recipe">S/. {{dato.costo_receta}}</span></td>
								<td><span class="phuyu-money phuyu-money-sale">S/. {{dato.precioventa}}</span></td>
								<td>
									<span class="phuyu-money" v-bind:class="parseFloat(phuyu_porcentaje_utilidad_producto(dato)) >= 0 ? 'text-success' : 'text-danger'">
										{{phuyu_porcentaje_utilidad_producto(dato)}}%
									</span>
								</td>
								<td>
									<span class="badge bg-success-subtle text-success" v-if="dato.receta.length>0">
										<i class="bi bi-check2-circle me-1"></i>{{dato.receta.length}} ingrediente(s)
									</span>
									<span class="badge bg-warning-subtle text-warning" v-if="dato.receta.length==0">
										<i class="bi bi-exclamation-triangle me-1"></i>Sin receta
									</span>
								</td>
								<td class="text-end">
									<div class="phuyu-actions">
										<button type="button" class="btn btn-outline-primary btn-sm" v-if="dato.receta.length>0" v-on:click="phuyu_ver_receta(dato)" title="Ver receta">
											<i class="bi bi-eye"></i>
										</button>
										<button type="button" class="btn btn-outline-success btn-sm" v-bind:class="dato.receta.length==0 ? 'phuyu-create-btn' : ''" v-on:click="phuyu_editar_receta(dato)" v-bind:title="dato.receta.length>0 ? 'Editar receta' : 'Crear receta'">
											<i class="bi bi-pencil-square" v-bind:class="dato.receta.length==0 ? 'me-1' : ''"></i>
											<span v-if="dato.receta.length==0">Crear</span>
										</button>
										<button type="button" class="btn btn-outline-danger btn-sm" v-if="dato.receta.length>0" v-on:click="phuyu_imprimir_receta(dato)" title="Imprimir PDF">
											<i class="bi bi-file-earmark-pdf"></i>
										</button>
									</div>
								</td>
							</tr>
							<tr v-if="buscar_productos.length==0">
								<td colspan="8" class="text-center text-muted py-3">
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

	<div id="modal_receta" class="modal fade phuyu-recipe-modal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" data-backdrop="static" data-keyboard="false">
		<div class="modal-dialog modal-xl">
			<div class="modal-content">
				<div class="modal-header">
					<div class="phuyu-modal-title">
						<div class="phuyu-modal-title-icon"><i class="bi bi-journal-check"></i></div>
						<div>
							<div class="small opacity-75 fw-semibold" v-if="modo_receta!='ver'">Registro y edición de receta</div>
							<div class="small opacity-75 fw-semibold" v-if="modo_receta=='ver'">Vista de receta</div>
							<h5 class="modal-title fw-bold phuyu-modal-title-text mb-0" id="titulo_receta"></h5>
						</div>
					</div>
					<span class="phuyu-mode-badge me-auto ms-3" v-if="modo_receta=='ver'">Solo lectura</span>
					<span class="phuyu-mode-badge me-auto ms-3" v-if="modo_receta!='ver'">Editable</span>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
				</div>
				<div class="modal-body">
					<div class="row g-3">
						<div class="col-12">
							<div class="phuyu-recipe-panel">
								<div class="phuyu-inline-ingredient-search" v-if="modo_receta!='ver'">
									<div class="phuyu-ingredient-picker">
										<div>
											<label class="form-label">Tipo</label>
											<select class="form-select" v-model="filtro_ingrediente" v-on:change="phuyu_buscar_ingredientes(true)">
												<option value="insumos">Insumos</option>
												<option value="venta">Para venta</option>
												<option value="preparados">Preparados</option>
												<option value="todos">Todos</option>
											</select>
										</div>
										<div>
											<label class="form-label">Buscar y seleccionar ingrediente</label>
											<select class="form-select" id="ingrediente_seleccionado_receta" v-model="ingrediente_seleccionado">
												<option value="">Seleccionar ingrediente</option>
												<option v-for="(producto, index) in ingredientes" v-bind:value="index">
													{{producto.descripcion}} - S/. {{phuyu_moneda(producto.preciocosto)}} - Stock {{producto.stock}} {{producto.unidad}}
												</option>
											</select>
										</div>
									</div>
									<div class="phuyu-selected-help" v-if="ingredientes.length==0">
										Sin resultados para la búsqueda actual.
									</div>
								</div>
								<div class="phuyu-list-title-row">
									<span v-if="modo_receta!='ver'">Lista de ingredientes</span>
									<span v-if="modo_receta=='ver'">Detalle de ingredientes</span>
									<span class="phuyu-add-count">{{detalle.length}} item(s)</span>
								</div>
								<div class="table-responsive phuyu-modal-scroll">
									<table class="table table-hover align-middle mb-0">
										<colgroup>
											<col class="phuyu-col-producto">
											<col class="phuyu-col-unidad">
											<col class="phuyu-col-cantidad">
											<col class="phuyu-col-costo">
											<col class="phuyu-col-subtotal">
											<col class="phuyu-col-eliminar" v-if="modo_receta!='ver'">
										</colgroup>
										<thead>
											<tr>
												<th>Ingrediente / producto</th>
												<th width="120">Unidad</th>
												<th width="120">Cantidad</th>
												<th width="100">P. costo</th>
												<th width="110">Subtotal</th>
												<th class="text-center" width="70" v-if="modo_receta!='ver'"><i class="bi bi-trash3"></i></th>
											</tr>
										</thead>
										<tbody>
											<tr v-for="(dato,index) in detalle">
												<td class="fw-semibold">{{dato.producto}}</td>
												<td>
													<select class="form-select form-select-sm" v-model.number="dato.codunidad" v-on:change="phuyu_cambiarunidad(dato)" v-bind:disabled="modo_receta=='ver'">
														<option v-for="unidad in dato.unidades" v-bind:value="unidad.codunidad">{{unidad.unidad}}</option>
													</select>
												</td>
												<td>
													<input type="number" step="0.001" class="form-control number" v-model.number="dato.cantidad" min="0.001" required v-bind:disabled="modo_receta=='ver'">
												</td>
												<td>S/. {{phuyu_moneda(dato.preciocosto)}}</td>
												<td>S/. {{phuyu_moneda(dato.cantidad * dato.preciocosto)}}</td>
												<td class="text-center" v-if="modo_receta!='ver'">
													<button type="button" class="btn btn-danger btn-sm" v-on:click="phuyu_deleteitem(index,dato)">
														<i class="bi bi-trash3"></i>
													</button>
												</td>
											</tr>
											<tr v-if="detalle.length==0">
												<td v-bind:colspan="modo_receta=='ver' ? 5 : 6" class="text-center text-muted py-3">Sin ingredientes</td>
											</tr>
										</tbody>
									</table>
								</div>
								<div v-if="detalle.length>0">
									<div class="phuyu-summary-strip">
										<div class="phuyu-summary-item">
											<div class="phuyu-summary-label">Costo receta</div>
											<div class="phuyu-summary-value">S/. {{phuyu_decimal4(costo_receta)}}</div>
										</div>
										<div class="phuyu-summary-item">
											<div class="phuyu-summary-label">Precio venta plato</div>
											<div class="phuyu-summary-value text-success">S/. {{phuyu_decimal4(campos.precioventa)}}</div>
										</div>
										<div class="phuyu-summary-item">
											<div class="phuyu-summary-label">Utilidad actual</div>
											<div class="phuyu-summary-value" v-bind:class="parseFloat(utilidad_receta) >= 0 ? 'text-success' : 'text-danger'">S/. {{phuyu_decimal4(utilidad_receta)}}</div>
										</div>
									</div>
									<div class="phuyu-calculator-box">
										<div class="phuyu-calculator-note">
											Calcula el precio final de dos formas: escribe el % sobre costo y calcula el precio, o escribe el precio final y calcula el % sobre costo.
										</div>
										<div class="row g-2 align-items-end">
											<div class="col-6 col-md-3">
												<label class="form-label">% sobre costo</label>
												<input type="number" step="0.0001" min="0" class="form-control form-control-sm" v-model.number="margen_utilidad" v-bind:disabled="modo_receta=='ver'">
											</div>
											<div class="col-6 col-md-3">
												<label class="form-label">Precio final</label>
												<input type="number" step="0.0001" min="0" class="form-control form-control-sm" v-model.number="precio_sugerido_manual" v-bind:disabled="modo_receta=='ver'">
											</div>
											<div class="col-6 col-md-3">
												<div class="small text-muted">Utilidad</div>
												<div class="fw-bold">S/. {{phuyu_decimal4(utilidad_sugerida)}}</div>
											</div>
											<div class="col-6 col-md-3">
												<div class="small text-muted">% sobre venta</div>
												<div class="fw-bold">{{phuyu_decimal4(margen_real_sugerido)}}%</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="phuyu-recipe-actions d-flex flex-wrap gap-2" v-if="detalle.length>0 && modo_receta!='ver'">
								<button type="button" class="btn btn-outline-secondary btn-sm" v-on:click="phuyu_calcular_precio_sugerido()">
									<i class="bi bi-calculator me-1"></i> % costo -> precio
								</button>
								<button type="button" class="btn btn-outline-secondary btn-sm" v-on:click="phuyu_calcular_margen_desde_precio()">
									<i class="bi bi-percent me-1"></i> Precio -> % costo
								</button>
								<button type="button" class="btn btn-outline-primary btn-sm" v-on:click="phuyu_actualizar_costo_plato()" v-bind:disabled="detalle.length==0">
									<i class="bi bi-arrow-repeat me-1"></i> Actualizar costo
								</button>
								<button type="button" class="btn btn-success btn-sm" v-on:click="phuyu_actualizar_costo_precio_plato()" v-bind:disabled="detalle.length==0">
									<i class="bi bi-check2-circle me-1"></i> Actualizar costo y venta
								</button>
								<button type="button" class="btn btn-primary btn-sm" v-on:click="phuyu_guardar()" v-bind:disabled="estado==1">
									<i class="bi bi-save me-1"></i> Guardar receta
								</button>
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
