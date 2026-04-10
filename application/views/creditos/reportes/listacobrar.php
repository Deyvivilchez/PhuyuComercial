<div id="phuyu_datos" class="container-fluid">
	<input type="hidden" id="sessioncaja" value="<?php echo $_SESSION["phuyu_codcontroldiario"]; ?>">

	<div class="row align-items-center mb-4">
		<div class="col-12 col-lg-6 mb-3 mb-lg-0">
			<h1 class="display-5 fw-bold mb-1" id="title">Créditos por Cobrar</h1>
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb mb-0">
					<li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
					<li class="breadcrumb-item active" aria-current="page">Créditos por Cobrar</li>
				</ol>
			</nav>
		</div>

		<div class="col-12 col-lg-6">
			<div class="row g-2 justify-content-lg-end">
				<div class="col-12 col-sm-4">
					<label for="fechadesde" class="form-label small fw-semibold">
						<i class="fa fa-calendar me-1"></i> DESDE
					</label>
					<input
						type="date"
						class="form-control"
						id="fechadesde"
						value="<?php echo date('Y-m-01'); ?>"
						v-on:change="phuyu_buscar()"
						autocomplete="off">
				</div>

				<div class="col-12 col-sm-4">
					<label for="fechahasta" class="form-label small fw-semibold">
						<i class="fa fa-calendar me-1"></i> HASTA
					</label>
					<input
						type="date"
						class="form-control"
						id="fechahasta"
						value="<?php echo date('Y-m-d'); ?>"
						v-on:change="phuyu_buscar()"
						autocomplete="off">
				</div>

				<div class="col-12 col-sm-4">
					<label for="filtrofechas" class="form-label small fw-semibold">FILTRAR POR FECHAS</label>
					<select
						id="filtrofechas"
						class="form-select"
						v-model="campos.filtro"
						v-on:change="phuyu_buscar()">
						<option value="1">Sí</option>
						<option value="0">No</option>
					</select>
				</div>
			</div>
		</div>
	</div>

	<div class="card shadow-sm border-0">
		<div class="card-body">
			<div class="row g-2 align-items-end mb-3">
				<div class="col-12 col-md-6 col-lg-2">
					<button
						type="button"
						class="btn btn-warning w-100"
						v-on:click="phuyu_editar('CLIENTE')">
						<i class="fa fa-edit me-1"></i> Cambiar Cliente
					</button>
				</div>

				<div class="col-12 col-md-6 col-lg-2">
					<div class="dropdown w-100">
						<button
							class="btn btn-primary dropdown-toggle w-100"
							type="button"
							data-bs-toggle="dropdown"
							aria-expanded="false">
							Crédito Detallado
						</button>
						<ul class="dropdown-menu w-100">
							<li>
								<a class="dropdown-item" href="javascript:;" v-on:click="phuyu_imprimir(0)">
									<i class="fa fa-file-pdf-o me-2"></i> Archivo PDF
								</a>
							</li>
							<li>
								<a class="dropdown-item" href="javascript:;" v-on:click="phuyu_imprimir(1)">
									<i class="fa fa-file-excel-o me-2"></i> Archivo Excel
								</a>
							</li>
						</ul>
					</div>
				</div>

				<div class="col-12 col-md-6 col-lg-2">
					<div class="dropdown w-100">
						<button
							class="btn btn-primary dropdown-toggle w-100"
							type="button"
							data-bs-toggle="dropdown"
							aria-expanded="false">
							Lista General
						</button>
						<ul class="dropdown-menu w-100">
							<li>
								<a class="dropdown-item" href="javascript:;" v-on:click="phuyu_imprimirlista(0)">
									<i class="fa fa-file-pdf-o me-2"></i> Archivo PDF
								</a>
							</li>
							<li>
								<a class="dropdown-item" href="javascript:;" v-on:click="phuyu_imprimirlista(1)">
									<i class="fa fa-file-excel-o me-2"></i> Archivo Excel
								</a>
							</li>
						</ul>
					</div>
				</div>

				<div class="col-12 col-md-6 col-lg-2">
					<label for="estado" class="form-label small fw-semibold">ESTADO</label>
					<select id="estado" class="form-select" v-model="estado" v-on:change="phuyu_buscar()">
						<option value="">TODOS</option>
						<option value="0">ANULADOS</option>
						<option value="1">PENDIENTES</option>
						<option value="2">COBRADOS</option>
						<option value="3">VÁLIDOS</option>
					</select>
				</div>

				<div class="col-12 col-lg-4">
					<label for="buscar" class="form-label small fw-semibold">BUSCAR</label>
					<div class="position-relative">
						<input
							id="buscar"
							class="form-control pe-5"
							v-model="buscar"
							v-on:keyup="phuyu_buscar()"
							placeholder="Buscar registro...">
						<span class="position-absolute top-50 end-0 translate-middle-y me-3 text-muted">
							<i class="fa fa-search"></i>
						</span>
					</div>
				</div>
			</div>

			<div class="text-center py-5" v-if="cargando">
				<div class="spinner-border text-primary" role="status"></div>
				<div class="mt-2 text-muted">Cargando créditos...</div>
			</div>

			<div v-if="!cargando">
				<div class="table-responsive">
					<table class="table table-striped table-hover table-bordered align-middle small">
						<thead class="table-dark text-center">
							<tr>
								<th>ID</th>
								<th>DOCUMENTO</th>
								<th>RAZÓN SOCIAL</th>
								<th>F. CRÉDITO</th>
								<th>F. VENCE</th>
								<th>COMPROBANTE</th>
								<th>IMPORTE</th>
								<th>INTERÉS</th>
								<th>TOTAL</th>
								<th>SALDO</th>
								<th>ESTADO</th>
								<th>CRONOGRAMA</th>
								<th>SEL.</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in datos" :key="dato.codcredito">
								<td class="text-center">{{ dato.codcredito }}</td>
								<td>{{ dato.documento }}</td>
								<td>{{ dato.razonsocial }}</td>
								<td>{{ dato.fechacredito }}</td>
								<td>{{ dato.fechavencimiento }}</td>
								<td>{{ dato.comprobante }}</td>
								<td class="text-end">{{ dato.importe }}</td>
								<td class="text-end">{{ dato.interes }}</td>
								<td class="text-end fw-semibold">{{ dato.total }}</td>
								<td class="text-end text-danger fw-semibold">{{ dato.saldo }}</td>
								<td class="text-center">
									<span v-if="dato.estado == 0" class="badge bg-danger">ANULADO</span>
									<span v-else-if="dato.estado == 1" class="badge bg-warning text-dark">PENDIENTE</span>
									<span v-else-if="dato.estado == 2" class="badge bg-success">CANCELADO</span>
									<span v-else class="badge bg-secondary">SIN ESTADO</span>
								</td>
								<td class="text-center">
									<button
										type="button"
										class="btn btn-info btn-sm text-white"
										v-on:click="phuyu_imprimircronograma(dato.codcredito)"
										title="Imprimir cronograma">
										<i class="fa fa-print me-1"></i> Cronograma
									</button>
								</td>
								<td class="text-center">
									<input
										type="radio"
										class="form-check-input"
										name="phuyu_seleccionar"
										v-on:click="phuyu_seleccionar(dato.codcredito)">
								</td>
							</tr>

							<tr class="table-light fw-bold">
								<td colspan="7" class="text-end">TOTALES</td>
								<td class="text-end">{{ total.totalimporte }}</td>
								<td class="text-end">{{ total.totalinteres }}</td>
								<td class="text-end">{{ total.totaltotal }}</td>
								<td class="text-end">{{ total.totalsaldo }}</td>
								<td colspan="2"></td>
							</tr>

							<tr v-if="datos.length === 0">
								<td colspan="13" class="text-center text-muted py-4">
									No se encontraron registros.
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url(); ?>phuyu/phuyu_creditos/reportes.js"></script>
