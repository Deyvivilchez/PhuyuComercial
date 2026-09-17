<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<style>
#phuyu_notas .phuyu-card-notas {
	border-radius: 16px;
}

#phuyu_notas .phuyu-search-box .form-control {
	border-radius: 12px;
	min-height: 40px;
}

#phuyu_notas .phuyu-btn-group .phuyu-btn {
	display: inline-flex;
	align-items: center;
	justify-content: center;
	gap: 6px;
	border-radius: 10px;
	font-weight: 700;
	padding: 7px 12px;
	transition: all .2s ease;
	box-shadow: 0 2px 6px rgba(15, 23, 42, .06);
	white-space: nowrap;
}

#phuyu_notas .phuyu-btn-group .phuyu-btn:hover {
	transform: translateY(-2px);
	box-shadow: 0 8px 18px rgba(15, 23, 42, .14);
}

#phuyu_notas .phuyu-btn-group .phuyu-btn:hover i {
	transform: scale(1.15);
}

#phuyu_notas .phuyu-btn-group .phuyu-btn i {
	transition: transform .2s ease;
}

#phuyu_notas .phuyu-table-wrapper {
	border-radius: 14px;
	overflow: auto;
	border: 1px solid #eef1f4;
}

#phuyu_notas .phuyu-table-notas {
	font-size: 13px;
	min-width: 1120px;
}

#phuyu_notas .phuyu-table-notas thead th {
	background: #f8f9fb;
	color: #495057;
	font-size: 12px;
	font-weight: 800;
	text-transform: uppercase;
	border-bottom: 1px solid #e9ecef;
	white-space: nowrap;
	vertical-align: middle;
}

#phuyu_notas .phuyu-table-notas tbody td {
	font-size: 13px;
	vertical-align: middle;
	border-color: #f1f3f5;
}

#phuyu_notas .phuyu-table-notas tbody tr:hover {
	background: #fafcff;
}

#phuyu_notas .phuyu_anulado {
	opacity: .65;
	background: #fff5f5 !important;
}

#phuyu_notas .phuyu_anulado td {
	color: #8b8b8b !important;
}

#phuyu_notas .phuyu-importe {
	font-weight: 800;
	color: #0ab39c;
	white-space: nowrap;
}

#phuyu_notas .form-check-input {
	cursor: pointer;
}

@media (max-width: 768px) {
	#phuyu_notas #title {
		font-size: 1.6rem !important;
	}

	#phuyu_notas .phuyu-btn-group {
		justify-content: flex-start !important;
	}

	#phuyu_notas .phuyu-btn-group .phuyu-btn {
		flex: 1 1 auto;
	}
}
</style>

<div id="phuyu_notas" class="phuyu-velzon-list phuyu-ventas-velzon">
	<div class="row g-3 align-items-start mb-3">
		<div class="col-12 col-md-6">
			<input type="hidden" id="formato" value="<?php echo $_SESSION['phuyu_formatonotacredito'];?>">
			<input type="hidden" id="perfil" value="<?php echo $_SESSION["phuyu_codperfil"];?>">

			<div class="phuyu-page-title mb-0">
				<span class="phuyu-page-icon"><i class="bi bi-arrow-counterclockwise"></i></span>
				<div>
					<div class="text-muted small text-uppercase fw-semibold">Ventas</div>
					<h4 class="mb-1" id="title">Notas de crédito</h4>
					<p class="text-muted mb-0">Consulta, emisión e impresión de notas de crédito.</p>
				</div>
			</div>
		</div>

		<div class="col-12 col-md-6">
			<div class="row g-2 justify-content-md-end">
				<div class="col-12 col-sm-4">
					<label class="form-label small fw-semibold mb-1">
						<i class="bi bi-calendar-date me-1"></i> Desde
					</label>
					<input type="date" class="form-control form-control-sm" id="fecha_desde" value="" v-on:change="phuyu_buscar()" autocomplete="off">
				</div>

				<div class="col-12 col-sm-4">
					<label class="form-label small fw-semibold mb-1">
						<i class="bi bi-calendar-check me-1"></i> Hasta
					</label>
					<input type="date" class="form-control form-control-sm" id="fecha_hasta" value="<?php echo date('Y-m-d');?>" v-on:change="phuyu_buscar()" autocomplete="off">
				</div>

				<div class="col-12 col-sm-4">
					<label class="form-label small fw-semibold mb-1">
						<i class="bi bi-printer me-1"></i> Formato impresión
					</label>
					<select class="form-select form-select-sm" v-model="formato_impresion" v-on:change="phuyu_formato()">
						<option value="a4">A4 Impresión</option>
						<option value="a5">A5 Impresión</option>
						<option value="ticket">Ticket Impresión</option>
					</select>
				</div>
			</div>
		</div>
	</div>

	<div class="phuyu_body">
		<div class="card border-0 shadow-sm phuyu-card-notas">
			<div class="card-body">

				<div class="row g-3 align-items-center mb-3">
					<div class="col-12 col-md-5 col-lg-4 col-xl-3">
						<div class="position-relative phuyu-search-box">
							<input
								class="form-control ps-5"
								v-model="buscar"
								v-on:keyup="phuyu_buscar()"
								placeholder="Buscar registro..."
								autocomplete="off"
							>
							<i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
						</div>
					</div>

					<div class="col-12 col-md-7 col-lg-8 col-xl-9">
						<div class="d-flex flex-wrap gap-2 justify-content-md-end phuyu-btn-group">

							<button type="button" class="btn btn-success btn-sm phuyu-btn" title="Nueva nota" data-bs-toggle="tooltip" v-on:click="phuyu_nuevo()">
								<i class="bi bi-plus-circle"></i>
								<span>Nuevo</span>
							</button>

							<button type="button" class="btn btn-danger btn-sm phuyu-btn eliminar" title="Anular nota crédito" data-bs-toggle="tooltip" v-on:click="phuyu_eliminar()">
								<i class="bi bi-trash"></i>
								<span>Eliminar</span>
							</button>

							<button type="button" class="btn btn-info btn-sm text-white phuyu-btn" title="Ver registro" data-bs-toggle="tooltip" v-on:click="phuyu_ver()">
								<i class="bi bi-eye"></i>
								<span>Ver</span>
							</button>

							<button type="button" class="btn btn-primary btn-sm phuyu-btn" title="Imprimir registro" data-bs-toggle="tooltip" v-on:click="phuyu_imprimir()">
								<i class="bi bi-printer"></i>
								<span>Imprimir</span>
							</button>

						</div>
					</div>
				</div>

				<div class="phuyu_cargando" v-if="cargando">
					<div class="overlay-spinner"></div>
				</div>

				<div class="table-responsive phuyu-table-wrapper">
					<table class="table table-hover align-middle mb-0 phuyu-table-notas">
						<thead>
							<tr>
								<th style="width:70px;">ID</th>
								<th>Documento</th>
								<th>Razón social</th>
								<th style="width:110px;">Fecha</th>
								<th style="width:100px;">Tipo</th>
								<th>Comprobante</th>
								<th>C. Referencia</th>
								<th style="width:120px;" class="text-end">Importe</th>
								<th>Descripción</th>
								<th style="width:60px;" class="text-center">
									<i class="bi bi-check2-circle"></i>
								</th>
							</tr>
						</thead>

						<tbody>
							<tr v-for="dato in datos" v-bind:class="[dato.estado==0 ? 'phuyu_anulado':'']">
								<td class="text-muted fw-semibold">{{dato.codkardex}}</td>
								<td>{{dato.documento}}</td>
								<td class="fw-semibold text-dark">{{dato.cliente}}</td>
								<td>{{dato.fechacomprobante}}</td>
								<td>{{dato.tipo}}</td>
								<td>{{dato.seriecomprobante}}-{{dato.nrocomprobante}}</td>
								<td>{{dato.seriecomprobante_ref}}-{{dato.nrocomprobante_ref}}</td>
								<td class="text-end">
									<span class="phuyu-importe">S/. {{dato.importe}}</span>
								</td>
								<td>{{dato.descripcion}}</td>
								<td class="text-center">
									<input type="radio" class="form-check-input" name="phuyu_seleccionar" v-on:click="phuyu_seleccionar(dato.codkardex,dato.estado)">
								</td>
							</tr>

							<tr v-if="!cargando && datos.length === 0">
								<td colspan="10" class="text-center text-muted py-5">
									<i class="bi bi-inbox d-block mb-2" style="font-size:32px;"></i>
									No se encontraron notas de crédito
								</td>
							</tr>
						</tbody>
					</table>
				</div>

				<div class="mt-3">
					<?php include("application/views/phuyu/phuyu_paginacion.php");?>
				</div>

			</div>

			<div id="modal_reportes" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
				<div class="modal-dialog modal-fullscreen">
					<div class="modal-content border-0 rounded-0">
						<div class="modal-header">
							<h4 class="modal-title mb-0 w-100 text-center">
								<i class="bi bi-file-earmark-pdf me-2"></i>
								<b style="letter-spacing:4px;"><?php echo $_SESSION["phuyu_empresa"];?> </b>
							</h4>

							<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
						</div>

						<div class="modal-body p-0" id="reportes_modal" style="height:450px;">
							<iframe id="phuyu_pdf" src="" style="width:100%; height:100%; border:none;"></iframe>
						</div>
					</div>
				</div>
			</div>

		</div>
	</div>
</div>

<script>
	var pantalla = jQuery(document).height();
	$("#reportes_modal").css({height: pantalla - 65});

	if (typeof bootstrap !== 'undefined') {
		document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
			new bootstrap.Tooltip(el);
		});
	}
</script>

<script src="<?php echo base_url();?>phuyu/phuyu_notas/index.js"></script>
