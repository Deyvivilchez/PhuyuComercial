<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_ventas" class="phuyu-velzon-list phuyu-agricola-velzon">
	<input type="hidden" id="sessioncaja" value="<?php echo $_SESSION["phuyu_codcontroldiario"];?>">
	<input type="hidden" id="comprobante" value="<?php echo $comprobante;?>">

	<div class="phuyu-page-title">
		<div class="phuyu-page-icon"><i class="bi bi-flower1"></i></div>
		<div>
			<div class="text-muted small text-uppercase fw-semibold">Agricola</div>
			<h4 class="mb-0 fw-bold" id="title">Administracion de chacras</h4>
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb mb-0 mt-1">
					<li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
					<li class="breadcrumb-item active" aria-current="page">Chacras</li>
				</ol>
			</nav>
		</div>
	</div>

	<div class="phuyu_body">
		<div class="card phuyu-card mb-3">
			<div class="card-body">
				<div class="row g-3 align-items-end">
					<div class="col-12 col-md-3 col-xl-2">
						<label><i class="bi bi-calendar-date me-1"></i> Desde</label>
						<input type="date" class="form-control" id="fecha_desde" value="" v-on:change="phuyu_buscar()" autocomplete="off">
					</div>
					<div class="col-12 col-md-3 col-xl-2">
						<label><i class="bi bi-calendar-check me-1"></i> Hasta</label>
						<input type="date" class="form-control" id="fecha_hasta" value="<?php echo date('Y-m-d');?>" v-on:change="phuyu_buscar()" autocomplete="off">
					</div>
				</div>
			</div>
		</div>

		<div class="card phuyu-card">
			<div class="card-body">
				<div class="row">
					<div class="col-sm-12 col-md-3 col-lg-3 col-xxl-2 mb-1">
						<div class="phuyu-search">
							<i class="bi bi-search"></i>
							<input class="form-control datatable-search" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="BUSCAR REGISTRO . . ." />
						</div>
					</div>
					<div class="col-sm-12 col-md-9 col-lg-9 col-xxl-10 text-end mb-1">
						<div class="phuyu-actions">
							<button type="button" class="btn btn-success" v-on:click="phuyu_nuevo()"><i class="bi bi-plus-circle me-1"></i> Nuevo</button>
							<button type="button" class="btn btn-warning" v-on:click="phuyu_editar()"><i class="bi bi-pencil-square me-1"></i> Editar</button>
							<button type="button" class="btn btn-danger" v-on:click="phuyu_eliminar()"><i class="bi bi-trash3 me-1"></i> Eliminar</button>
							<button type="button" class="btn btn-info" v-on:click="phuyu_ver()"><i class="bi bi-eye me-1"></i> Ver</button>
							<button type="button" class="btn btn-info" v-on:click="phuyu_asignargasto()"><i class="bi bi-cash-coin me-1"></i> Gastos</button>
							<button type="button" class="btn btn-info" v-on:click="phuyu_salidaproductos()"><i class="bi bi-box-seam me-1"></i> Insumos</button>
							<button type="button" class="btn btn-info" v-on:click="phuyu_ingresoproductos()"><i class="bi bi-flower2 me-1"></i> Produccion</button>
							<button type="button" class="btn btn-warning" v-on:click="phuyu_historial()"><i class="bi bi-clock-history me-1"></i> Historial</button>
						</div>
					</div>
				</div>
				<div class="phuyu_cargando" v-if="cargando">
					<div class="overlay-spinner"></div>
				</div>
				<div class="phuyu-table-wrap">
					<table class="table table-hover table-striped align-middle" style="font-size: 11px">
						<thead>
							<tr>
								<th>ID</th>
								<th>UBIGEO</th>
								<th>DIRECCION</th>
								<th>AREA(M2)</th>
								<th>FECHA</th>
								<th width="100px">TIPO POSESION</th>
								<th>ESTADO</th>
								<th width="5px;"> <center> <i class="fa fa-circle-o"></i> </center> </th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in datos">
								<td>{{dato.codlote}}</td>
								<td>{{dato.departamento}} / {{dato.provincia}} / {{dato.distrito}} / {{dato.zona}}</td>
								<td>{{dato.direccion}}</td>
								<td>{{dato.area}}</td>
								<td>{{dato.fechainicio}}</td>
								<td>
									<span v-if="dato.tipoposesion==0">PROPIA</span>
									<span v-if="dato.tipoposesion==1">ALQUILADA</span>
									<span v-if="dato.tipoposesion==2">ALQUILER COMPRA</span>
								</td>
								<td>
									<span v-if="dato.estado==0">ANULADO</span>
									<span v-if="dato.estado==1">VÁLIDO</span>
								</td>
								<td v-if="dato.estado!='2'"> 
									<input type="radio" class="form-check-input" name="phuyu_seleccionar" v-on:click="phuyu_seleccionar(dato.codlote)"> 
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

<script src="<?php echo base_url();?>phuyu/phuyu_chacra/index.js"> </script>
