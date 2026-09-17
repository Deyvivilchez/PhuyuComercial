<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_creditos" class="phuyu-velzon-list phuyu-creditos-velzon">
	<input type="hidden" id="sessioncaja" value="<?php echo $_SESSION["phuyu_codcontroldiario"];?>">
	<input type="hidden" id="comprobante" value="<?php echo $comprobante;?>">
	<input type="hidden" id="rubro" value="<?php echo $_SESSION["phuyu_rubro"];?>">

	<div class="phuyu-page-title">
		<div class="phuyu-page-icon"><i class="bi bi-cash-stack"></i></div>
		<div>
			<div class="text-muted small text-uppercase fw-semibold">Tesoreria</div>
			<h4 class="mb-0 fw-bold">
				<span class="badge bg-danger me-2" v-if="sessioncaja==0">La caja no esta aperturada</span>
				Cuentas por cobrar
			</h4>
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb mb-0 mt-1">
					<li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
					<li class="breadcrumb-item active" aria-current="page">Cuentas por cobrar</li>
				</ol>
			</nav>
		</div>
	</div>

	<div class="phuyu_body">
		<input type="hidden" id="phuyu_opcion" value="1">
		<div class="card phuyu-card">
			<div class="card-body">
				<div class="phuyu-toolbar">
					<div class="phuyu-search">
						<i class="bi bi-search"></i>
						<input class="form-control datatable-search" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="Buscar cuenta por cobrar">
					</div>

					<div class="phuyu-actions">
						<button type="button" class="btn btn-primary" v-on:click="phuyu_nuevo()">
							<i class="bi bi-plus-circle me-1"></i> Nuevo
						</button>
						<button type="button" class="btn btn-info" v-on:click="phuyu_cobranza()">
							<i class="bi bi-cash-coin me-1"></i> Cobranza
						</button>
						<button type="button" class="btn btn-warning" v-on:click="phuyu_historial()">
							<i class="bi bi-file-earmark-text me-1"></i> Historial
						</button>
						<button type="button" class="btn btn-success" v-on:click="phuyu_persona()">
							<i class="bi bi-person-plus me-1"></i> Nuevo cliente
						</button>
					</div>
				</div>

				<div class="phuyu_cargando" v-if="cargando">
					<div class="overlay-spinner"></div>
				</div>

				<div class="phuyu-table-wrap" v-if="!cargando">
					<table class="table table-hover table-striped align-middle">
						<thead>
							<tr>
								<th>Lote</th>
								<th>Documento</th>
								<th>Cliente</th>
								<th>Direccion</th>
								<th>Telefono</th>
								<th class="text-center">Pendientes</th>
								<th width="70" class="text-center">Sel.</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="(dato,index) in datos">
								<td class="fw-semibold">{{dato.codlote}}</td>
								<td>{{dato.documento}}</td>
								<td>{{dato.razonsocial}}</td>
								<td>{{dato.direccion}}</td>
								<td>{{dato.telefonos}}</td>
								<td class="text-center">{{dato.creditos}}</td>
								<td class="text-center">
									<input type="radio" class="form-check-input" name="phuyu_seleccionar" v-on:click="phuyu_seleccionar(dato.codpersona,dato.codlote)" v-if="sessioncaja==1">
								</td>
							</tr>
							<tr v-if="datos.length==0">
								<td colspan="7" class="text-center text-muted py-4">
									<i class="bi bi-inbox me-1"></i> Sin cuentas por cobrar
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

<script src="<?php echo base_url();?>phuyu/phuyu_creditos/cuentascobrar.js"> </script>
