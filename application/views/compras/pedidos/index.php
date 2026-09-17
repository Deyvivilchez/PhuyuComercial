<div id="phuyu_pedidos" class="phuyu-pedidos">
	<input type="hidden" id="almacen" value="<?php echo $almacen;?>">
	<input type="hidden" id="formato" value="<?php echo $_SESSION['phuyu_formatopedido'];?>">

	<div class="page-title-box d-sm-flex align-items-center justify-content-between mb-3">
		<div>
			<h4 class="mb-1 fw-bold">Pedidos de compra</h4>
			<div class="text-muted">Seguimiento y atencion de pedidos registrados</div>
		</div>
		<div class="page-title-right">
			<ol class="breadcrumb m-0">
				<li class="breadcrumb-item"><a href="javascript:;">Compras</a></li>
				<li class="breadcrumb-item active">Pedidos</li>
			</ol>
		</div>
	</div>

	<div class="phuyu_body">
		<div class="card phuyu-pedidos-card">
			<div class="card-body">
				<div class="row g-2 align-items-end mb-3">
					<div class="col-12 col-md-6 col-xl-2">
						<label class="form-label text-muted mb-1">Desde</label>
						<div class="input-group phuyu-filter">
							<span class="input-group-text"><i class="bi bi-calendar3"></i></span>
							<input type="date" class="form-control" id="fecha_desde" value="" v-on:change="phuyu_buscar()" autocomplete="off">
						</div>
					</div>
					<div class="col-12 col-md-6 col-xl-2">
						<label class="form-label text-muted mb-1">Hasta</label>
						<div class="input-group phuyu-filter">
							<span class="input-group-text"><i class="bi bi-calendar-check"></i></span>
							<input type="date" class="form-control" id="fecha_hasta" value="<?php echo date('Y-m-d');?>" v-on:change="phuyu_buscar()" autocomplete="off">
						</div>
					</div>
					<div class="col-12 col-md-6 col-xl-3">
						<label class="form-label text-muted mb-1">Formato impresion</label>
						<select class="form-select" v-model="formato_impresion" v-on:change="phuyu_formato()">
							<option value="a4">A4 impresion</option>
			        		<option value="a5">A5 impresion</option>
			        		<option value="ticket">Ticket impresion</option>
						</select>
					</div>
					<div class="col-12 col-md-6 col-xl-5">
						<label class="form-label text-muted mb-1">Buscar</label>
						<div class="input-group phuyu-search">
							<span class="input-group-text"><i class="bi bi-search"></i></span>
							<input type="text" class="form-control" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="Buscar pedido..." autocomplete="off">
							<button type="button" class="btn btn-light" v-if="buscar!=''" v-on:click="buscar=''; phuyu_buscar();">
								<i class="bi bi-x-lg"></i>
							</button>
						</div>
					</div>
				</div>

				<div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
					<div class="d-flex flex-wrap gap-2">
						<button type="button" class="btn btn-success" v-on:click="phuyu_nuevo()">
							<i class="bi bi-plus-lg me-1"></i> Nuevo pedido
						</button>
						<button type="button" class="btn btn-info" v-on:click="phuyu_ver()">
							<i class="bi bi-eye me-1"></i> Ver
						</button>
						<button type="button" class="btn btn-warning editar" v-on:click="phuyu_editar()">
							<i class="bi bi-pencil-square me-1"></i> Editar
						</button>
						<button type="button" class="btn btn-danger eliminar" v-on:click="phuyu_eliminar()">
							<i class="bi bi-trash3 me-1"></i> Eliminar
						</button>
					</div>
					<div class="d-flex flex-wrap gap-2">
						<button type="button" class="btn btn-primary" v-on:click="phuyu_imprimir()">
							<i class="bi bi-printer me-1"></i> Imprimir
						</button>
						<button type="button" class="btn btn-secondary" v-on:click="phuyu_clonar()">
							<i class="bi bi-files me-1"></i> Clonar
						</button>
					</div>
				</div>

				<div class="phuyu_cargando" v-if="cargando">
					<div class="overlay-spinner"></div>
				</div>

				<div v-if="!cargando">
					<div class="table-responsive phuyu-table-wrap">
						<table class="table table-hover align-middle mb-0">
							<thead>
								<tr>
									<th class="text-center" style="width:52px;"><i class="bi bi-check2-circle"></i></th>
									<th>ID</th>
									<th>Documento</th>
									<th>Razon social</th>
									<th>Fecha</th>
									<th>Tipo</th>
									<th>Comprobante</th>
									<th class="text-end">Importe</th>
									<th>Pago</th>
									<th>Estado</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="dato in datos" v-bind:class="[dato.estado==0 ? 'phuyu_anulado':'']">
									<td class="text-center">
										<input type="radio" class="form-check-input phuyu_radio" name="phuyu_seleccionar" v-on:click="phuyu_seleccionar(dato.codpedido,dato.estado)">
									</td>
									<td class="text-muted fw-semibold">{{dato.codpedido}}</td>
									<td>{{dato.documento}}</td>
									<td class="fw-semibold">{{dato.cliente}}</td>
									<td>{{dato.fechapedido}}</td>
									<td>{{dato.tipo}}</td>
									<td>{{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
									<td class="text-end">
										<span class="fw-bold text-success" v-if="dato.estado!=0">S/ {{dato.importe}}</span>
										<span class="fw-semibold text-muted" v-if="dato.estado==0">S/ {{dato.importe}}</span>
									</td>
									<td>
										<span class="badge bg-success-subtle text-success" v-if="dato.condicionpago==1">Al contado</span>
										<span class="badge bg-warning-subtle text-warning" v-else="dato.condicionpago==2">Al credito</span>
									</td>
									<td>
										<span class="badge bg-warning-subtle text-warning" v-if="dato.estadoproceso==0">Pendiente</span>
										<span class="badge bg-success-subtle text-success" v-else="dato.estadoproceso==1">Atendido</span>
									</td>
								</tr>
								<tr v-if="datos.length==0">
									<td colspan="10" class="text-center text-muted py-4">
										<i class="bi bi-inbox me-1"></i> Sin pedidos para mostrar
									</td>
								</tr>
							</tbody>
						</table>
					</div>
					<?php include("application/views/phuyu/phuyu_paginacion.php");?>
				</div>

			    <div id="modal_reportes" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
					<div class="modal-dialog modal-fullscreen">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title fw-bold"><?php echo $_SESSION["phuyu_empresa"];?></h5>
								<button type="button" class="btn-close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Cerrar"></button>
							</div>
							<div class="modal-body p-0" id="reportes_modal">
								<iframe id="phuyu_pdf" src="" class="w-100 h-100 border-0"></iframe>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<style>
	#phuyu_pedidos.phuyu-pedidos .phuyu-pedidos-card {
		border: 1px solid rgba(64, 81, 137, .10);
		border-radius: .9rem;
		box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
	}

	#phuyu_pedidos.phuyu-pedidos .phuyu-filter .input-group-text,
	#phuyu_pedidos.phuyu-pedidos .phuyu-search .input-group-text {
		background: #f8fafc;
		border-color: rgba(64, 81, 137, .16);
		color: #405189;
	}

	#phuyu_pedidos.phuyu-pedidos .phuyu-filter .form-control,
	#phuyu_pedidos.phuyu-pedidos .phuyu-search .form-control,
	#phuyu_pedidos.phuyu-pedidos .phuyu-search .btn,
	#phuyu_pedidos.phuyu-pedidos .form-select {
		border-color: rgba(64, 81, 137, .16);
		min-height: 40px;
	}

	#phuyu_pedidos.phuyu-pedidos .phuyu-table-wrap {
		border: 1px solid rgba(64, 81, 137, .10);
		border-radius: .75rem;
		overflow: hidden;
	}

	#phuyu_pedidos.phuyu-pedidos table thead th {
		background: #f8fafc;
		color: #495057;
		font-size: .74rem;
		font-weight: 800;
		text-transform: uppercase;
		white-space: nowrap;
		border-bottom: 1px solid rgba(64, 81, 137, .12);
	}

	#phuyu_pedidos.phuyu-pedidos table tbody td {
		font-size: .86rem;
		vertical-align: middle;
	}

	#phuyu_pedidos.phuyu-pedidos .phuyu_anulado td {
		background: #f8fafc;
		color: #94a3b8;
		text-decoration: line-through;
	}

	#phuyu_pedidos.phuyu-pedidos #reportes_modal {
		height: calc(100vh - 64px);
	}

	@media (max-width: 767.98px) {
		#phuyu_pedidos.phuyu-pedidos .page-title-box {
			display: block !important;
		}

		#phuyu_pedidos.phuyu-pedidos .page-title-right {
			margin-top: .5rem;
		}
	}
</style>

<script>
	var pantalla = jQuery(document).height(); $("#reportes_modal").css({height: pantalla - 65});
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_pedidos/index.js"> </script>
