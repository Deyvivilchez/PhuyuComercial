<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_sunat" class="phuyu-velzon-list phuyu-cpe-velzon">
	<div class="phuyu-page-title">
		<div class="phuyu-page-icon"><i class="bi bi-receipt"></i></div>
		<div>
			<div class="text-muted small text-uppercase fw-semibold">CPE</div>
			<h4 class="mb-0 fw-bold">Comprobantes electronicos</h4>
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb mb-0 mt-1">
					<li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
					<li class="breadcrumb-item active" aria-current="page">Comprobantes</li>
				</ol>
			</nav>
		</div>
	</div>

	<div class="phuyu_body">
		<div class="card phuyu-card">
			<div class="card-body">
				<input type="hidden" id="sucursal" value="<?php echo $_SESSION['phuyu_codsucursal'];?>" name="">
				<div class="row g-3 align-items-end mb-3">
					<div class="col-12 col-md-6 col-xl-2">
						<label>Sucursales</label>
						<select class="form-select" v-model="sucursal" v-on:change="phuyu_buscar()">
							<?php 
								foreach ($sucursal as $key => $value) { ?>
								<option value="<?php echo $value["codsucursal"];?>"><?php echo $value["descripcion"];?></option>
							<?php	}
							?>
						</select>
					</div>
					<div class="col-12 col-md-3 col-xl-2">
						<label><i class="bi bi-calendar-date me-1"></i> Desde</label>
						<input type="date" class="form-control" id="fecha_desde" value="<?php echo date('Y-m-01');?>" v-on:blur="phuyu_buscar()" autocomplete="off">
					</div>
					<div class="col-12 col-md-3 col-xl-2">
						<label><i class="bi bi-calendar-check me-1"></i> Hasta</label>
						<input type="date" class="form-control" id="fecha_hasta" value="<?php echo date('Y-m-d');?>" v-on:blur="phuyu_buscar()" autocomplete="off">
					</div>
					<div class="col-12 col-md-4 col-xl-2">
						<label><i class="bi bi-file-earmark-text me-1"></i> Comprobantes</label>
						<select class="form-select">
							<option value="0">TODOS</option>	
							<?php 
								foreach ($comprobantes as $key => $value) { ?>
								<option value="<?php echo $value["codcomprobantetipo"];?>"><?php echo $value["descripcion"];?></option>
							<?php	}
							?>
						</select>
					</div>
					<div class="col-12 col-md-3 col-xl-1">
						<label>Estado</label>
						<select class="form-select">
							<option value="">TODOS</option>
							<option value="0">PENDIENTES</option>
							<option value="1">ENVIADOS</option>
						</select>
					</div>
					<div class="col-12 col-md-5 col-xl-3">
						<label>Buscar</label>
						<input type="text" class="form-control" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="Buscar comprobante">
					</div>
				</div>
				<div v-if="!cargando">
					<div class="table-responsive">
						<table class="table table-hover table-striped align-middle" style="font-size: 11px">
							<thead>
								<tr>
									<th width="10px">TIPO</th>
									<th>Razon social cliente</th>
									<th>FECHA</th>
									<th width="10px">COMPROBANTE</th>
									<th width="10px">IMPORTE</th>
									<th>Descripcion</th>
									<th>SUNAT</th>
									<th width="10px">XML</th>
									<th width="10px">CDR</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="dato in datos">
									<td><span class="label label-success">{{dato.tipo}}</span></td>
									<td>{{dato.documento}}-{{dato.cliente}}</td>
									<td>{{dato.fechacomprobante}}</td>
									<td>{{dato.seriecomprobante}}-{{dato.nrocomprobante}}</td>
									<td>{{dato.importe}}</td>
									<td>{{dato.descripcion_cdr}}</td>
									<td>
										<span class="label label-danger" v-if="dato.estado==0">PENDIENTE</span>
										<span class="label label-success" v-else="dato.estado!=0">ENVIADO</span>
									</td>
									<td>
										<button type="button" class="btn btn-info btn-xs btn-table" style="margin:1px;" v-on:click="phuyu_xml(dato.codkardex)"><i class="bi bi-download"></i> XML</button>
									</td>
									<td>
										<button type="button" class="btn btn-warning btn-xs btn-table" style="margin:1px;" v-on:click="phuyu_cdr(dato.codkardex)"><i class="bi bi-download"></i> CDR</button>
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
</div>
<script src="<?php echo base_url();?>phuyu/phuyu_facturacion/comprobantes.js"> </script>
