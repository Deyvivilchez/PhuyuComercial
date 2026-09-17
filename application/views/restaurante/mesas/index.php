<style>
	#phuyu_datos.phuyu-restobar-list .phuyu-page-title {
		display: flex;
		align-items: center;
		gap: .75rem;
		margin-bottom: 1rem;
	}

	#phuyu_datos.phuyu-restobar-list .phuyu-page-icon {
		width: 44px;
		height: 44px;
		border-radius: 12px;
		display: inline-flex;
		align-items: center;
		justify-content: center;
		background: rgba(10, 132, 117, .10);
		color: #0a8475;
		font-size: 1.25rem;
	}

	#phuyu_datos.phuyu-restobar-list .phuyu-card {
		border: 1px solid rgba(10, 132, 117, .12);
		border-radius: .9rem;
		box-shadow: 0 10px 28px rgba(15, 23, 42, .06);
	}

	#phuyu_datos.phuyu-restobar-list .phuyu-toolbar {
		display: flex;
		flex-wrap: wrap;
		gap: .6rem;
		align-items: center;
		justify-content: space-between;
		margin-bottom: 1rem;
	}

	#phuyu_datos.phuyu-restobar-list .phuyu-search {
		max-width: 360px;
		position: relative;
		flex: 1 1 260px;
	}

	#phuyu_datos.phuyu-restobar-list .phuyu-search .form-control {
		padding-left: 2.35rem;
		min-height: 40px;
		border-color: rgba(10, 132, 117, .18);
	}

	#phuyu_datos.phuyu-restobar-list .phuyu-search i {
		position: absolute;
		left: .85rem;
		top: 50%;
		transform: translateY(-50%);
		color: #878a99;
	}

	#phuyu_datos.phuyu-restobar-list .phuyu-actions {
		display: flex;
		flex-wrap: wrap;
		gap: .45rem;
	}

	#phuyu_datos.phuyu-restobar-list .phuyu-table-wrap {
		border: 1px solid rgba(10, 132, 117, .12);
		border-radius: .75rem;
		overflow: hidden;
	}

	#phuyu_datos.phuyu-restobar-list table thead th {
		background: #f8fafc;
		color: #495057;
		font-size: .74rem;
		font-weight: 800;
		text-transform: uppercase;
		white-space: nowrap;
		border-bottom: 1px solid rgba(10, 132, 117, .14);
	}

	#phuyu_datos.phuyu-restobar-list table tbody td {
		font-size: .86rem;
		vertical-align: middle;
	}

	.phuyu-status-pill {
		display: inline-flex;
		align-items: center;
		gap: .35rem;
		border-radius: 999px;
		padding: .22rem .6rem;
		font-size: .72rem;
		font-weight: 800;
	}

	.phuyu-status-pill.disponible {
		background: rgba(10, 132, 117, .10);
		color: #0a8475;
	}

	.phuyu-status-pill.ocupada {
		background: rgba(239, 83, 80, .12);
		color: #c62828;
	}

	@media (max-width: 575.98px) {
		#phuyu_datos.phuyu-restobar-list .phuyu-actions .btn,
		#phuyu_datos.phuyu-restobar-list .phuyu-search {
			width: 100%;
			max-width: 100%;
		}
	}
</style>

<div id="phuyu_datos" class="phuyu-restobar-list">
	<div class="phuyu-page-title">
		<div class="phuyu-page-icon"><i class="bi bi-grid-3x3-gap"></i></div>
		<div>
			<div class="text-muted small text-uppercase fw-semibold">Restobar</div>
			<h4 class="mb-0 fw-bold">Administracion de mesas - <?php echo $_SESSION["phuyu_sucursal"];?></h4>
		</div>
	</div>

	<div class="phuyu_body">
		<input type="hidden" id="phuyu_opcion" value="1">
		<div class="card phuyu-card">
			<div class="card-body">
				<div class="phuyu-toolbar">
					<div class="phuyu-search">
						<i class="bi bi-search"></i>
						<input class="form-control" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="Buscar mesa">
					</div>

					<div class="phuyu-actions">
						<button type="button" class="btn btn-primary" v-on:click="phuyu_nuevo()">
							<i class="bi bi-plus-circle me-1"></i> Nuevo
						</button>
						<button type="button" class="btn btn-warning" v-on:click="phuyu_editar()">
							<i class="bi bi-pencil-square me-1"></i> Editar
						</button>
						<button type="button" class="btn btn-danger" v-on:click="phuyu_eliminar()">
							<i class="bi bi-trash3 me-1"></i> Eliminar
						</button>
					</div>
				</div>

				<div class="phuyu_cargando" v-if="cargando">
					<div class="overlay-spinner"></div>
				</div>

				<div class="table-responsive phuyu-table-wrap">
					<table class="table table-hover align-middle mb-0">
						<thead>
							<tr>
								<th width="90">ID</th>
								<th>Mesa</th>
								<th>Ambiente</th>
								<th width="120">Capacidad</th>
								<th width="130">Estado</th>
								<th class="text-center" width="70">Sel.</th>
							</tr>
						</thead>
						<tbody>
							<tr v-for="dato in datos">
								<td class="text-muted fw-semibold">{{dato.codmesa}}</td>
								<td class="fw-semibold">Nro {{dato.nromesa}}</td>
								<td>{{dato.ambiente}}</td>
								<td>{{dato.capacidad}}</td>
								<td>
									<span class="phuyu-status-pill" v-bind:class="dato.situacion==2 ? 'ocupada' : 'disponible'">
										<i class="bi" v-bind:class="dato.situacion==2 ? 'bi-circle-fill' : 'bi-check-circle'"></i>
										{{dato.situacion==2 ? 'Ocupada' : 'Disponible'}}
									</span>
								</td>
								<td class="text-center">
									<input type="radio" class="form-check-input" name="phuyu_seleccionar" v-on:click="phuyu_seleccionar(dato.codmesa)">
								</td>
							</tr>
							<tr v-if="datos.length==0">
								<td colspan="6" class="text-center text-muted py-3">
									<i class="bi bi-inbox me-1"></i> Sin mesas registradas
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

<script src="<?php echo base_url();?>phuyu/phuyu_datos.js"> </script>
