<div id="phuyu_notas" class="phuyu-notas-credito">
	<div class="page-title-box d-sm-flex align-items-center justify-content-between mb-3">
		<div>
			<h4 class="mb-1 fw-bold">Notas de credito</h4>
			<div class="text-muted">Gestion de notas de credito de compras</div>
		</div>
		<div class="page-title-right">
			<ol class="breadcrumb m-0">
				<li class="breadcrumb-item"><a href="javascript:;">Compras</a></li>
				<li class="breadcrumb-item active">Notas de credito</li>
			</ol>
		</div>
	</div>

	<div class="phuyu_body">
		<div class="card phuyu-notas-card">
			<div class="card-body">
				<div class="row g-2 align-items-center mb-3">
					<div class="col-12 col-lg-5 col-xl-4">
						<div class="input-group phuyu-search">
							<span class="input-group-text"><i class="bi bi-search"></i></span>
							<input class="form-control" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="Buscar nota..." autocomplete="off">
							<button type="button" class="btn btn-light" v-if="buscar!=''" v-on:click="buscar=''; phuyu_buscar();">
								<i class="bi bi-x-lg"></i>
							</button>
						</div>
					</div>
					<div class="col-12 col-lg-7 col-xl-8">
						<div class="d-flex flex-wrap gap-2 justify-content-lg-end">
							<button type="button" class="btn btn-success" v-on:click="phuyu_nuevo()">
								<i class="bi bi-plus-lg me-1"></i> Nuevo
							</button>
							<button type="button" class="btn btn-info" v-on:click="phuyu_ver()">
								<i class="bi bi-eye me-1"></i> Ver
							</button>
						</div>
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
									<th>ID</th>
									<th>Documento</th>
									<th>Proveedor</th>
									<th>Fecha</th>
									<th>Tipo</th>
									<th>Comprobante</th>
									<th>Referencia</th>
									<th class="text-end">Importe</th>
									<th>Descripcion</th>
									<th class="text-center" style="width:52px;"><i class="bi bi-check2-circle"></i></th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="dato in datos" :key="dato.codkardex">
									<td class="text-muted fw-semibold">{{dato.codkardex}}</td>
									<td>{{dato.documento}}</td>
									<td class="fw-semibold">{{dato.cliente}}</td>
									<td>{{dato.fechacomprobante}}</td>
									<td>{{dato.tipo}}</td>
									<td>{{dato.seriecomprobante}}-{{dato.nrocomprobante}}</td>
									<td>{{dato.seriecomprobante_ref}}-{{dato.nrocomprobante_ref}}</td>
									<td class="text-end fw-semibold">S/ {{dato.importe}}</td>
									<td>{{dato.descripcion}}</td>
									<td class="text-center">
										<input v-if="dato.estado!=0" type="radio" class="form-check-input phuyu_radio" name="phuyu_seleccionar" v-on:click="phuyu_seleccionar(dato.codkardex)">
										<span v-if="dato.estado==0" class="badge bg-danger-subtle text-danger">Anulado</span>
									</td>
								</tr>
								<tr v-if="datos.length==0">
									<td colspan="10" class="text-center text-muted py-4">
										<i class="bi bi-inbox me-1"></i> Sin notas de credito para mostrar
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
								<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
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
	#phuyu_notas.phuyu-notas-credito .phuyu-notas-card {
		border: 1px solid rgba(64, 81, 137, .10);
		border-radius: .9rem;
		box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
	}

	#phuyu_notas.phuyu-notas-credito .phuyu-search .input-group-text {
		background: #f8fafc;
		border-color: rgba(64, 81, 137, .16);
		color: #405189;
	}

	#phuyu_notas.phuyu-notas-credito .phuyu-search .form-control,
	#phuyu_notas.phuyu-notas-credito .phuyu-search .btn {
		border-color: rgba(64, 81, 137, .16);
		min-height: 40px;
	}

	#phuyu_notas.phuyu-notas-credito .phuyu-table-wrap {
		border: 1px solid rgba(64, 81, 137, .10);
		border-radius: .75rem;
		overflow: hidden;
	}

	#phuyu_notas.phuyu-notas-credito table thead th {
		background: #f8fafc;
		color: #495057;
		font-size: .74rem;
		font-weight: 800;
		text-transform: uppercase;
		white-space: nowrap;
		border-bottom: 1px solid rgba(64, 81, 137, .12);
	}

	#phuyu_notas.phuyu-notas-credito table tbody td {
		font-size: .86rem;
		vertical-align: middle;
	}

	#phuyu_notas.phuyu-notas-credito #reportes_modal {
		height: calc(100vh - 64px);
	}

	@media (max-width: 767.98px) {
		#phuyu_notas.phuyu-notas-credito .page-title-box {
			display: block !important;
		}

		#phuyu_notas.phuyu-notas-credito .page-title-right {
			margin-top: .5rem;
		}
	}
</style>

<script>
	var pantalla = jQuery(document).height(); $("#reportes_modal").css({height: pantalla - 65});
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_notas/index.js"></script>
