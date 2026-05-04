<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_datos" class="phuyu-velzon-list phuyu-creditos-velzon">
	<input type="hidden" id="sessioncaja" value="<?php echo $_SESSION["phuyu_codcontroldiario"];?>">

	<div class="phuyu-page-title">
		<div class="phuyu-page-icon"><i class="bi bi-receipt"></i></div>
		<div>
			<div class="text-muted small text-uppercase fw-semibold">Tesoreria</div>
			<h4 class="mb-0 fw-bold">Creditos por pagar</h4>
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb mb-0 mt-1">
					<li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
					<li class="breadcrumb-item active" aria-current="page">Credito por pagar</li>
				</ol>
			</nav>
		</div>
	</div>

	<div class="card phuyu-card mb-3">
		<div class="card-body">
			<div class="row g-3 align-items-end">
				<div class="col-12 col-md-2">
					<label class="form-label">Desde</label>
					<input type="date" class="form-control input-sm" id="fechadesde" value="<?php echo date('Y-m-01');?>" v-on:change="phuyu_buscar()" autocomplete="off">
				</div>
				<div class="col-12 col-md-2">
					<label class="form-label">Hasta</label>
					<input type="date" class="form-control input-sm" id="fechahasta" value="<?php echo date('Y-m-d');?>" v-on:change="phuyu_buscar()" autocomplete="off">
				</div>
				<div class="col-12 col-md-2">
					<label class="form-label">Por fechas?</label>
					<select class="form-select input-sm" v-model="campos.filtro" v-on:change="phuyu_buscar()">
						<option value="1">FECHAS FILTRO (SI)</option>
						<option value="0">FECHAS FILTRO (NO)</option>
					</select>
				</div>
				<div class="col-12 col-md-2">
					<label class="form-label">Programados</label>
					<select class="form-select" v-model="campos.creditoprogramado" v-on:change="phuyu_buscar()">
						<option value="">TODOS</option>
						<option value="0">SIN PROGRAMAR</option>
						<option value="1">PROGRAMADOS</option>
					</select>
				</div>
			</div>
		</div>
	</div>

	<div class="phuyu_body">
		<div class="card phuyu-card">
			<div class="card-body">
				<div class="phuyu-toolbar">
					<button type="button" class="btn btn-warning" v-on:click="phuyu_editar('CLIENTE')">
						<i class="bi bi-pencil-square me-1"></i> Cambiar proveedor
					</button>

					<div class="dropdown">
						<button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							Credito detallado
						</button>
						<div class="dropdown-menu">
							<a class="dropdown-item" href="javascript:;" v-on:click="phuyu_imprimir(0)">Archivo PDF</a>
							<a class="dropdown-item" href="javascript:;" v-on:click="phuyu_imprimir(1)">Archivo Excel</a>
						</div>
					</div>

					<div class="dropdown">
						<button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							Lista general
						</button>
						<div class="dropdown-menu">
							<a class="dropdown-item" href="javascript:;" v-on:click="phuyu_imprimirlista(0)">Archivo PDF</a>
							<a class="dropdown-item" href="javascript:;" v-on:click="phuyu_imprimirlista(1)">Archivo Excel</a>
						</div>
					</div>

					<div class="flex-fill" style="max-width:230px;">
						<select class="form-select" v-model="estado" v-on:change="phuyu_buscar()">
							<option value="">TODOS</option>
							<option value="0">ANULADOS</option>
							<option value="1">PENDIENTES</option>
							<option value="2">COBRADOS</option>
							<option value="3">VALIDOS</option>
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
								<th>Documento</th>
								<th>Razon social</th>
								<th>F. credito</th>
								<th>F. vence</th>
								<th>Comprobante</th>
								<th>Importe</th>
								<th>Interes</th>
								<th>Total</th>
								<th>Saldo</th>
								<th>Estado</th>
								<th width="70" class="text-center">Sel.</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in datos">
								<td class="fw-semibold">{{dato.codcredito}}</td>
								<td>{{dato.documento}}</td>
								<td>{{dato.razonsocial}}</td>
								<td>{{dato.fechacredito}}</td>
								<td>{{dato.fechavencimiento}}</td>
								<td>{{dato.comprobante}}</td>
								<td>{{dato.importe}}</td>
								<td>{{dato.interes}}</td>
								<td>{{dato.total}}</td>
								<td>{{dato.saldo}}</td>
								<td>
									<span class="badge bg-danger" v-if="dato.estado==0">ANULADO</span>
									<span class="badge bg-warning text-dark" v-if="dato.estado==1">PENDIENTE</span>
									<span class="badge bg-success" v-if="dato.estado==2">CANCELADO</span>
								</td>
								<td class="text-center">
									<input type="radio" class="form-check-input" name="phuyu_seleccionar" v-on:click="phuyu_seleccionar(dato.codcredito)">
								</td>
							</tr>
							<tr v-if="datos.length==0">
								<td colspan="12" class="text-center text-muted py-4">
									<i class="bi bi-inbox me-1"></i> Sin creditos por pagar
								</td>
							</tr>
							<tr>
								<th colspan="7" class="text-end">Totales</th>
								<th>{{total.totalimporte}}</th>
								<th>{{total.totalinteres}}</th>
								<th>{{total.totaltotal}}</th>
								<th>{{total.totalsaldo}}</th>
								<th></th>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_creditos/reportes.js"> </script>
