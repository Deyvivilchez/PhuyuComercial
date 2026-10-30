<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_datos" class="phuyu-velzon-list">
	<div class="phuyu-page-title">
		<div class="phuyu-page-icon"><i class="ri-timer-flash-line"></i></div>
		<div>
			<div class="text-muted small text-uppercase fw-semibold">CPE</div>
			<h4 class="mb-0 fw-bold">Programacion de envios CPE</h4>
			<nav aria-label="breadcrumb">
				<ol class="breadcrumb mb-0 mt-1">
					<li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
					<li class="breadcrumb-item active" aria-current="page">Configuracion automatica SUNAT</li>
				</ol>
			</nav>
		</div>
	</div>

	<div class="phuyu_body">
		<div class="row g-3">
			<div class="col-12 col-xl-5">
				<div class="card phuyu-card">
					<div class="card-header">
						<h5 class="card-title mb-0">Configuracion</h5>
					</div>
					<div class="card-body">
						<form v-on:submit.prevent="guardar">
							<div class="row g-3">
								<div class="col-12">
									<label class="form-label">Descripcion</label>
									<input type="text" class="form-control" v-model.trim="form.descripcion" required>
								</div>
								<div class="col-md-6">
									<label class="form-label">Empresa</label>
									<select class="form-select" v-model.number="form.codempresa" required>
										<option v-for="empresa in empresas" v-bind:value="empresa.codempresa">
											{{empresa.documento}} - {{empresa.nombrecomercial || empresa.razonsocial}}
										</option>
									</select>
								</div>
								<div class="col-md-6">
									<label class="form-label">Sucursal</label>
									<select class="form-select" v-model.number="form.codsucursal">
										<option v-bind:value="0">Todas</option>
										<option v-for="sucursal in sucursalesFiltradas" v-bind:value="sucursal.codsucursal">
											{{sucursal.descripcion}}
										</option>
									</select>
								</div>
								<div class="col-md-6">
									<label class="form-label">Modo</label>
									<select class="form-select" v-model="form.modo_envio">
										<option value="programado">Programado</option>
										<option value="inmediato">Inmediato</option>
										<option value="manual">Manual</option>
									</select>
								</div>
								<div class="col-md-3">
									<label class="form-label">Intentos</label>
									<input type="number" min="1" class="form-control" v-model.number="form.max_intentos">
								</div>
								<div class="col-md-3">
									<label class="form-label">Limite</label>
									<input type="number" min="1" class="form-control" v-model.number="form.limite_por_ejecucion">
								</div>
							</div>

							<div class="mt-3">
								<label class="form-label">Comprobantes</label>
								<div class="row g-2">
									<label class="col-6 form-check">
										<input class="form-check-input" type="checkbox" v-model="form.procesar_facturas"> Facturas
									</label>
									<label class="col-6 form-check">
										<input class="form-check-input" type="checkbox" v-model="form.procesar_boletas"> Boletas
									</label>
									<label class="col-6 form-check">
										<input class="form-check-input" type="checkbox" v-model="form.procesar_notas_credito"> Notas de credito
									</label>
									<label class="col-6 form-check">
										<input class="form-check-input" type="checkbox" v-model="form.procesar_notas_debito"> Notas de debito
									</label>
									<label class="col-6 form-check">
										<input class="form-check-input" type="checkbox" v-model="form.procesar_resumenes"> Resumenes
									</label>
									<label class="col-6 form-check">
										<input class="form-check-input" type="checkbox" v-model="form.procesar_bajas"> Bajas
									</label>
								</div>
							</div>

							<div class="mt-3">
								<div class="d-flex justify-content-between align-items-center mb-2">
									<label class="form-label mb-0">Horarios</label>
									<button type="button" class="btn btn-sm btn-primary" v-on:click="agregarHorario">
										<i class="ri-add-line"></i>
									</button>
								</div>
								<div class="row g-2 mb-2" v-for="(horario, index) in form.horarios">
									<div class="col-4">
										<input type="time" class="form-control" v-model="horario.hora">
									</div>
									<div class="col-6">
										<select class="form-select" v-model="horario.accion">
											<option value="todo">Procesar todo</option>
											<option value="generar_resumen">Generar resumen</option>
											<option value="reenviar">Reenviar pendientes</option>
										</select>
									</div>
									<div class="col-2">
										<button type="button" class="btn btn-light w-100" v-on:click="quitarHorario(index)">
											<i class="ri-delete-bin-line"></i>
										</button>
									</div>
								</div>
							</div>

							<div class="d-flex justify-content-between align-items-center mt-4">
								<label class="form-check mb-0">
									<input class="form-check-input" type="checkbox" v-model="form.activo"> Activo
								</label>
								<div class="d-flex gap-2">
									<button type="button" class="btn btn-light" v-on:click="nuevo">Nuevo</button>
									<button type="submit" class="btn btn-success">
										<i class="ri-save-3-line"></i> Guardar
									</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>

			<div class="col-12 col-xl-7">
				<div class="card phuyu-card mb-3">
					<div class="card-header d-flex justify-content-between align-items-center">
						<h5 class="card-title mb-0">Cron base SUNAT</h5>
						<button type="button" class="btn btn-sm btn-light" v-on:click="verificarCron">
							<i class="ri-refresh-line"></i>
						</button>
					</div>
					<div class="card-body">
						<div class="d-flex flex-column flex-md-row justify-content-between gap-3">
							<div>
								<span class="badge" v-bind:class="cron.existe == 1 ? 'bg-success' : 'bg-warning text-dark'">
									{{cron.mensaje || 'Verificando cron base...'}}
								</span>
								<div class="mt-2 small text-muted" v-if="cron.proyecto">
									<div><b>Proyecto:</b> {{cron.proyecto}}</div>
									<div><b>Ruta:</b> {{cron.ruta}}</div>
									<div><b>Archivo:</b> {{cron.archivo}}</div>
								</div>
								<div class="mt-2 small text-muted" v-if="cron.comando">
									<b>Comando:</b> {{cron.comando}}
								</div>
							</div>
							<div class="d-flex align-items-start gap-2">
								<button type="button" class="btn btn-primary" v-if="cron.existe != 1" v-on:click="crearCron">
									<i class="ri-add-line"></i> Crear cron base
								</button>
								<button type="button" class="btn btn-outline-primary" v-if="cron.existe == 1" v-on:click="crearCron">
									<i class="ri-loop-right-line"></i> Recrear cron base
								</button>
							</div>
						</div>
					</div>
				</div>

				<div class="card phuyu-card mb-3">
					<div class="card-header d-flex justify-content-between align-items-center">
						<h5 class="card-title mb-0">Programaciones</h5>
						<button type="button" class="btn btn-sm btn-light" v-on:click="cargar">
							<i class="ri-refresh-line"></i>
						</button>
					</div>
					<div class="card-body table-responsive">
						<table class="table table-bordered align-middle">
							<thead>
								<tr>
									<th>Descripcion</th>
									<th>Modo</th>
									<th>Horario</th>
									<th>Estado</th>
									<th width="150">Acciones</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="item in configuraciones">
									<td>
										<b>{{item.descripcion}}</b><br>
										<span class="text-muted">{{item.sucursal}}</span>
									</td>
									<td>{{item.modo_envio}}</td>
									<td>
										<span class="badge bg-primary-subtle text-primary me-1" v-for="h in item.horarios">
											{{h.hora.substring(0,5)}} {{h.accion}}
										</span>
									</td>
									<td>
										<span class="badge bg-success" v-if="item.activo==1">Activo</span>
										<span class="badge bg-secondary" v-else>Inactivo</span>
									</td>
									<td>
										<div class="btn-group btn-group-sm">
											<button type="button" class="btn btn-light" v-on:click="editar(item)">
												<i class="ri-pencil-line"></i>
											</button>
											<button type="button" class="btn btn-success" v-on:click="ejecutar(item)">
												<i class="ri-send-plane-line"></i>
											</button>
											<button type="button" class="btn btn-danger" v-on:click="eliminar(item)">
												<i class="ri-close-line"></i>
											</button>
										</div>
									</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>

				<div class="card phuyu-card">
					<div class="card-header">
						<div class="d-flex flex-column flex-md-row justify-content-between gap-2">
							<ul class="nav nav-tabs card-header-tabs">
								<li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#historial" type="button">Historial</button></li>
								<li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#cola" type="button">Cola</button></li>
							</ul>
							<button type="button" class="btn btn-sm btn-outline-danger" v-on:click="limpiarHistorial">
								<i class="ri-delete-bin-6-line"></i> Limpiar historial y cola
							</button>
						</div>
					</div>
					<div class="card-body tab-content table-responsive">
						<div class="tab-pane fade show active" id="historial">
							<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-2">
								<span class="text-muted small">{{paginacionTexto(historial_paginacion)}}</span>
								<div class="btn-group btn-group-sm">
									<button type="button" class="btn btn-light" v-on:click="cambiarPagina('historial', -1)" v-bind:disabled="historial_paginacion.offset <= 0">
										<i class="ri-arrow-left-s-line"></i> Anterior
									</button>
									<button type="button" class="btn btn-light" v-on:click="cambiarPagina('historial', 1)" v-bind:disabled="historial_paginacion.offset + historial_paginacion.limite >= historial_paginacion.total">
										Siguiente <i class="ri-arrow-right-s-line"></i>
									</button>
								</div>
							</div>
							<table class="table table-sm table-bordered">
								<thead><tr><th>Inicio</th><th>Programacion</th><th>Estado</th><th>Procesados</th><th>Respuesta</th></tr></thead>
								<tbody>
									<tr v-for="item in historial">
										<td>{{item.inicio}}</td>
										<td>{{item.programacion}}</td>
										<td>{{item.estado}}</td>
										<td>{{item.cantidad_procesada}} / {{item.cantidad_error}}</td>
										<td>{{item.respuesta_sunat || item.errores}}</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="tab-pane fade" id="cola">
							<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-2">
								<span class="text-muted small">{{paginacionTexto(cola_paginacion)}}</span>
								<div class="btn-group btn-group-sm">
									<button type="button" class="btn btn-light" v-on:click="cambiarPagina('cola', -1)" v-bind:disabled="cola_paginacion.offset <= 0">
										<i class="ri-arrow-left-s-line"></i> Anterior
									</button>
									<button type="button" class="btn btn-light" v-on:click="cambiarPagina('cola', 1)" v-bind:disabled="cola_paginacion.offset + cola_paginacion.limite >= cola_paginacion.total">
										Siguiente <i class="ri-arrow-right-s-line"></i>
									</button>
								</div>
							</div>
							<table class="table table-sm table-bordered">
								<thead><tr><th>Tipo</th><th>Referencia</th><th>Estado</th><th>Intentos</th><th>Mensaje</th></tr></thead>
								<tbody>
									<tr v-for="item in cola">
										<td>{{item.tipo}}</td>
										<td>{{item.referencia}}</td>
										<td>{{item.estado}}</td>
										<td>{{item.intentos}}</td>
										<td>{{item.ultimo_mensaje}}</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_facturacion/programacion_sunat.js"></script>
