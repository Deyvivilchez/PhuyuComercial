<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_datos" class="phuyu-velzon-list phuyu-creditos-velzon">
	<input type="hidden" id="sessioncaja" value="<?php echo $_SESSION["phuyu_codcontroldiario"];?>">

	<div class="phuyu-page-title">
		<div class="phuyu-page-icon"><i class="bi bi-cash-coin"></i></div>
		<div>
			<div class="text-muted small text-uppercase fw-semibold">Tesoreria</div>
			<h4 class="mb-0 fw-bold">Lista de pagos</h4>
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb mb-0 mt-1">
					<li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
					<li class="breadcrumb-item active" aria-current="page">Pagos</li>
				</ol>
			</nav>
		</div>
	</div>

	<div class="card phuyu-card mb-3">
		<div class="card-body">
			<div class="row g-3 align-items-end">
				<div class="col-12 col-md-3">
					<label class="form-label">Desde</label>
					<input type="date" class="form-control input-sm" id="fechadesde" value="<?php echo date('Y-m-01');?>" v-on:change="phuyu_buscar()" autocomplete="off">
				</div>
				<div class="col-12 col-md-3">
					<label class="form-label">Hasta</label>
					<input type="date" class="form-control input-sm" id="fechahasta" value="<?php echo date('Y-m-d');?>" v-on:change="phuyu_buscar()" autocomplete="off">
				</div>
				<div class="col-12 col-md-3">
					<label class="form-label">Por fechas?</label>
					<select class="form-select input-sm" v-model="campos.filtro" v-on:change="phuyu_buscar()">
						<option value="1">FECHAS FILTRO (SI)</option>
						<option value="0">FECHAS FILTRO (NO)</option>
					</select>
				</div>
			</div>
		</div>
	</div>

	<div class="phuyu_body">
		<div class="card phuyu-card">
			<div class="card-body">
				<div class="phuyu-toolbar">
					<div class="dropdown">
						<button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							Lista general
						</button>
						<div class="dropdown-menu">
							<a class="dropdown-item" href="javascript:;" v-on:click="phuyu_imprimirlista(0)">Archivo PDF</a>
							<a class="dropdown-item" href="javascript:;" v-on:click="phuyu_imprimirlista(1)">Archivo Excel</a>
						</div>
					</div>

					<div class="flex-fill" style="max-width:260px;">
						<select class="form-select" v-model="estado" v-on:change="phuyu_buscar()">
							<option value="">TODOS LOS PAGOS</option>
							<option value="0">ANULADOS</option>
							<option value="1">ACTIVOS</option>
						</select>
					</div>

					<div class="phuyu-search">
						<i class="bi bi-search"></i>
						<input class="form-control datatable-search" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="Buscar registro">
					</div>
				</div>

				<div class="phuyu_cargando" v-if="cargando">
					<div class="overlay-spinner"></div>
				</div>

				<div class="phuyu-table-wrap" v-if="!cargando">
					<table class="table table-hover table-striped align-middle">
						<thead>
							<tr>
								<th>ID</th>
								<th>Razon social</th>
								<th>F. credito</th>
								<th>F. vence</th>
								<th>Comprobante</th>
								<th>Total cred.</th>
								<th>F. pago</th>
								<th>Importe</th>
								<th>Estado</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in datos">
								<td class="fw-semibold">{{dato.codcredito}}</td>
								<td>{{dato.razonsocial}}</td>
								<td>{{dato.fechacredito}}</td>
								<td>{{dato.fechavencimientocredito}}</td>
								<td>{{dato.seriecomprobante}} - {{dato.nrocomprobante}}</td>
								<td>{{dato.totalcredito}}</td>
								<td>{{dato.fechamovimiento}}</td>
								<td>{{dato.importe}}</td>
								<td>
									<span class="badge bg-danger" v-if="dato.estado==0">ANULADO</span>
									<span class="badge bg-success" v-if="dato.estado==1">ACTIVO</span>
								</td>
							</tr>
							<tr v-if="datos.length==0">
								<td colspan="9" class="text-center text-muted py-4">
									<i class="bi bi-inbox me-1"></i> Sin pagos
								</td>
							</tr>
							<tr>
								<th colspan="5" class="text-end">Totales</th>
								<th>{{total.totaltotal}}</th>
								<th></th>
								<th>{{total.totalpago}}</th>
								<th></th>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_creditos/listacobranza.js"> </script>
