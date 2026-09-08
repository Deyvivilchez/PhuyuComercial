<div id="phuyu_datos" class="phuyu-proveedores">
	<input type="hidden" id="phuyu_opcion" value="1">

	<div class="page-title-box d-sm-flex align-items-center justify-content-between mb-3">
		<div>
			<h4 class="mb-1 fw-bold">Proveedores</h4>
			<div class="text-muted">Administracion de socios proveedores</div>
		</div>
		<div class="page-title-right">
			<ol class="breadcrumb m-0">
				<li class="breadcrumb-item"><a href="javascript:;">Compras</a></li>
				<li class="breadcrumb-item active">Proveedores</li>
			</ol>
		</div>
	</div>

	<div class="phuyu_body">
		<div class="card phuyu-provider-card">
			<div class="card-body">
				<div class="row g-2 align-items-center mb-3">
					<div class="col-12 col-lg-5 col-xl-4">
						<div class="input-group phuyu-search">
							<span class="input-group-text"><i class="bi bi-search"></i></span>
							<input class="form-control" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="Buscar proveedor..." autocomplete="off">
							<button type="button" class="btn btn-light" v-if="buscar!=''" v-on:click="buscar=''; phuyu_buscar();">
								<i class="bi bi-x-lg"></i>
							</button>
						</div>
					</div>
					<div class="col-12 col-lg-7 col-xl-8">
						<div class="d-flex flex-wrap gap-2 justify-content-lg-end">
							<button type="button" class="btn btn-success" data-bs-toggle="tooltip" data-bs-placement="top" title="Nuevo registro" v-on:click="phuyu_nuevo()">
								<i class="bi bi-plus-lg me-1"></i> Nuevo
							</button>
							<button type="button" class="btn btn-warning" data-bs-toggle="tooltip" data-bs-placement="top" title="Editar registro" v-on:click="phuyu_editar()">
								<i class="bi bi-pencil-square me-1"></i> Editar
							</button>
							<button type="button" class="btn btn-danger" data-bs-toggle="tooltip" data-bs-placement="top" title="Eliminar registro" v-on:click="phuyu_eliminar()">
								<i class="bi bi-trash3 me-1"></i> Eliminar
							</button>
						</div>
					</div>
				</div>

				<div class="phuyu_cargando" v-if="cargando">
					<div class="overlay-spinner"></div>
				</div>

				<div class="table-responsive phuyu-table-wrap">
					<table class="table table-hover align-middle mb-0">
						<thead>
							<tr>
								<th>ID</th>
								<th>Documento</th>
								<th>Razon social</th>
								<th>Direccion</th>
								<th>Telefono</th>
								<th>Email</th>
								<th class="text-center" style="width:52px;"><i class="bi bi-check2-circle"></i></th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="(dato,index) in datos" :key="dato.codpersona">
								<td class="text-muted fw-semibold">{{dato.codpersona}}</td>
								<td>{{dato.documento}}</td>
								<td class="fw-semibold">{{dato.razonsocial}}</td>
								<td>{{dato.direccion}}</td>
								<td>{{dato.telefono}}</td>
								<td>{{dato.email}}</td>
								<td class="text-center">
									<input type="radio" class="form-check-input" name="phuyu_seleccionar" v-on:click="phuyu_seleccionar(dato.codpersona,dato.estado)">
								</td>
							</tr>
							<tr v-if="!cargando && datos.length==0">
								<td colspan="7" class="text-center text-muted py-4">
									<i class="bi bi-inbox me-1"></i> Sin proveedores para mostrar
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

<style>
	#phuyu_datos.phuyu-proveedores .phuyu-provider-card {
		border: 1px solid rgba(64, 81, 137, .10);
		border-radius: .9rem;
		box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
	}

	#phuyu_datos.phuyu-proveedores .phuyu-search .input-group-text {
		background: #f8fafc;
		border-color: rgba(64, 81, 137, .16);
		color: #405189;
	}

	#phuyu_datos.phuyu-proveedores .phuyu-search .form-control,
	#phuyu_datos.phuyu-proveedores .phuyu-search .btn {
		border-color: rgba(64, 81, 137, .16);
		min-height: 40px;
	}

	#phuyu_datos.phuyu-proveedores .phuyu-table-wrap {
		border: 1px solid rgba(64, 81, 137, .10);
		border-radius: .75rem;
		overflow: hidden;
	}

	#phuyu_datos.phuyu-proveedores table thead th {
		background: #f8fafc;
		color: #495057;
		font-size: .74rem;
		font-weight: 800;
		text-transform: uppercase;
		white-space: nowrap;
		border-bottom: 1px solid rgba(64, 81, 137, .12);
	}

	#phuyu_datos.phuyu-proveedores table tbody td {
		font-size: .86rem;
		vertical-align: middle;
	}

	@media (max-width: 767.98px) {
		#phuyu_datos.phuyu-proveedores .page-title-box {
			display: block !important;
		}

		#phuyu_datos.phuyu-proveedores .page-title-right {
			margin-top: .5rem;
		}
	}
</style>

<script src="<?php echo base_url();?>phuyu/phuyu_datos.js"></script>
