<style>
	#phuyu_compras .phuyu-title {
		display: flex;
		align-items: center;
		gap: .65rem;
		color: #405189;
		font-weight: 800;
	}

	#phuyu_compras .phuyu-title i {
		width: 42px;
		height: 42px;
		border-radius: 12px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: rgba(64, 81, 137, .1);
	}

	#phuyu_compras label,
	#phuyu_compras .form-label {
		font-size: 11px;
		font-weight: 800;
		color: #343a40;
		margin-bottom: 6px;
		text-transform: uppercase;
	}

	#phuyu_compras .form-control {
		border-radius: 9px;
		min-height: 40px;
	}

	#phuyu_compras .phuyu-card {
		border: 0;
		border-radius: 1rem;
		box-shadow: 0 10px 35px rgba(15, 23, 42, .06);
	}

	#phuyu_compras .phuyu-search-box {
		position: relative;
	}

	#phuyu_compras .phuyu-search-box .form-control {
		padding-left: 40px;
		font-weight: 600;
	}

	#phuyu_compras .phuyu-search-box i {
		position: absolute;
		left: 14px;
		top: 50%;
		transform: translateY(-50%);
		color: #878a99;
	}

	#phuyu_compras .phuyu-btn-text-icon {
		display: inline-flex;
		align-items: center;
		justify-content: center;
		gap: .45rem;
		border-radius: 10px;
		font-weight: 700;
	}

	#phuyu_compras .phuyu-table-wrapper {
		border: 1px solid #eef1f4;
		border-radius: 14px;
		overflow: auto;
	}

	#phuyu_compras .phuyu-table {
		margin-bottom: 0;
		font-size: 12px;
		min-width: 980px;
	}

	#phuyu_compras .phuyu-table thead th {
		background: #f3f6f9;
		color: #343a40;
		font-weight: 800;
		text-transform: uppercase;
		white-space: nowrap;
	}

	#phuyu_compras .phuyu-table td {
		vertical-align: middle;
	}

	#phuyu_compras .phuyu-amount {
		font-size: 14px;
		font-weight: 800;
		color: #0ab39c;
	}

	@media (max-width: 767.98px) {
		#phuyu_compras .phuyu-actions .btn {
			width: 100%;
		}
	}
</style>

<div id="phuyu_compras">
	<input type="hidden" id="caja" value="<?php echo $caja;?>">
	<input type="hidden" id="almacen" value="<?php echo $almacen;?>">

	<div class="row g-3 align-items-end mb-3">
		<div class="col-12 col-lg-5">
			<div class="phuyu-title">
				<i class="bi bi-bag-check fs-5"></i>
				<div>
					<h4 class="mb-0">Administración de compras</h4>
					<nav aria-label="breadcrumb">
						<ol class="breadcrumb mb-0 mt-1">
							<li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
							<li class="breadcrumb-item active" aria-current="page">Compras</li>
						</ol>
					</nav>
				</div>
			</div>
		</div>

		<div class="col-12 col-lg-7">
			<div class="row g-2 justify-content-lg-end">
				<div class="col-12 col-md-4">
					<label class="form-label">
						<i class="bi bi-calendar3 me-1"></i>
						Desde
					</label>
					<input type="date" class="form-control" id="fecha_desde" value="" v-on:blur="phuyu_buscar()" autocomplete="off">
				</div>
				<div class="col-12 col-md-4">
					<label class="form-label">
						<i class="bi bi-calendar-check me-1"></i>
						Hasta
					</label>
					<input type="date" class="form-control" id="fecha_hasta" value="<?php echo date('Y-m-d');?>" v-on:blur="phuyu_buscar()" autocomplete="off">
				</div>
			</div>
		</div>
	</div>

	<div class="phuyu_body">
		<div class="card phuyu-card">
			<div class="card-body p-3 p-lg-4">
				<div class="row g-2 align-items-center mb-3">
					<div class="col-12 col-lg-4 col-xxl-3">
						<div class="phuyu-search-box">
							<i class="bi bi-search"></i>
							<input class="form-control datatable-search" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="Buscar compra..." />
						</div>
					</div>

					<div class="col-12 col-lg-8 col-xxl-9">
						<div class="phuyu-actions d-flex flex-wrap justify-content-lg-end gap-2">
							<button type="button" class="btn btn-success phuyu-btn-text-icon" v-on:click="phuyu_nuevo()">
								<i class="bi bi-plus-circle"></i>
								<span>Nuevo</span>
							</button>
							<button type="button" class="btn btn-info phuyu-btn-text-icon" v-on:click="phuyu_ver()">
								<i class="bi bi-eye"></i>
								<span>Ver</span>
							</button>
							<button type="button" class="btn btn-warning phuyu-btn-text-icon editar" v-on:click="phuyu_editar()">
								<i class="bi bi-pencil-square"></i>
								<span>Editar</span>
							</button>
							<button type="button" class="btn btn-primary phuyu-btn-text-icon gasto" v-on:click="phuyu_egresos()">
								<i class="bi bi-cash-coin"></i>
								<span>Gasto</span>
							</button>
							<button type="button" class="btn btn-danger phuyu-btn-text-icon eliminar" v-on:click="phuyu_eliminar()">
								<i class="bi bi-trash"></i>
								<span>Eliminar</span>
							</button>
							<button type="button" class="btn btn-warning phuyu-btn-text-icon restaurar" v-on:click="phuyu_restaurar()" disabled>
								<i class="bi bi-arrow-counterclockwise"></i>
								<span>Restaurar</span>
							</button>
							<button type="button" class="btn btn-secondary phuyu-btn-text-icon" v-on:click="phuyu_clonar()">
								<i class="bi bi-copy"></i>
								<span>Clonar</span>
							</button>
						</div>
					</div>
				</div>

				<div class="phuyu_cargando" v-if="cargando">
					<div class="overlay-spinner"></div>
				</div>

				<div class="phuyu-table-wrapper">
					<table class="table table-hover table-striped align-middle phuyu-table">
						<thead>
							<tr>
								<th>ID</th>
								<th>Documento</th>
								<th>Razón social</th>
								<th>Fecha</th>
								<th>Tipo</th>
								<th>Comprobante</th>
								<th style="width:130px;">Importe</th>
								<th>Pago</th>
								<th style="width:70px;" class="text-center">
									<i class="bi bi-check2-circle"></i>
								</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in datos" v-bind:class="[dato.estado==0 ? 'phuyu_anulado':'']">
								<td class="fw-semibold">{{dato.codkardex}}</td>
								<td>{{dato.documento}}</td>
								<td>{{dato.razonsocial}}</td>
								<td>{{dato.fechacomprobante}}</td>
								<td>{{dato.tipo}}</td>
								<td>{{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
								<td>
									<span class="phuyu-amount" v-if="dato.codmoneda==1">S/. {{dato.importe}}</span>
									<span class="phuyu-amount" v-if="dato.codmoneda!=1">$ {{dato.importe}}</span>
								</td>
								<td>
									<span class="badge bg-primary" v-if="dato.condicionpago==1">Al contado</span>
									<span class="badge bg-warning text-dark" v-else="dato.condicionpago==2">Al crédito</span>
								</td>
								<td class="text-center">
									<input type="radio" class="form-check-input" name="phuyu_seleccionar" v-on:click="phuyu_seleccionar(dato.codkardex,dato.estado)">
								</td>
							</tr>

							<tr v-if="datos.length === 0 && !cargando">
								<td colspan="9" class="text-center text-muted py-4">
									<i class="bi bi-inbox d-block mb-1" style="font-size:28px;"></i>
									Sin compras registradas
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

<script src="<?php echo base_url();?>phuyu/phuyu_compras/index.js"></script>
