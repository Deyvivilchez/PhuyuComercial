<div id="phuyu_hotel_limpieza" class="hotel-cleaning-page">

	<style>
		.hotel-cleaning-page{--hc-border:var(--vz-border-color,#e9ebec);--hc-muted:var(--vz-secondary-color,#878a99);--hc-text:var(--vz-body-color,#212529)}
		.hotel-cleaning-page .hotel-page-title{margin:0;font-size:1.1rem;font-weight:800;color:#343a40;display:flex;align-items:center;gap:.45rem}
		.hotel-cleaning-page .page-title-box{padding:1.05rem 1.2rem;border:1px solid var(--hc-border);border-radius:12px;background:linear-gradient(135deg,#fff 0%,#f7f9ff 100%);box-shadow:0 8px 24px rgba(56,65,74,.07);margin-bottom:1rem}
		.hotel-cleaning-page .hotel-filter-card,.hotel-cleaning-page .hotel-table-card{border:1px solid var(--hc-border);border-radius:12px;box-shadow:0 8px 24px rgba(56,65,74,.055);overflow:hidden}
		.hotel-cleaning-page .hotel-filter-card .card-body{padding:1rem}
		.hotel-cleaning-page .form-label{font-size:.7rem;font-weight:800;text-transform:uppercase;letter-spacing:.03em;color:#687385}
		.hotel-cleaning-page .form-control-sm,.hotel-cleaning-page .form-select-sm{height:38px;border-radius:7px;border-color:#d8dee9;box-shadow:none;font-size:.78rem}
		.hotel-cleaning-page .hotel-table-card .card-body{padding:0}
		.hotel-cleaning-page .hotel-table thead th{background:#f8f9fa;color:#687385;font-size:.7rem;text-transform:uppercase;letter-spacing:.03em;border-bottom:1px solid var(--hc-border);padding:.85rem .9rem}
		.hotel-cleaning-page .hotel-table tbody td{padding:.85rem .9rem;border-color:#eef1f5;vertical-align:middle}
		.hotel-cleaning-page .hotel-table tbody tr:hover{background:#fbfcff}
		.hotel-cleaning-page .hotel-order-code{display:inline-flex;align-items:center;border-radius:999px;background:#eef4ff;color:#405189;font-weight:800;padding:.35rem .55rem;font-size:.76rem}
		.hotel-cleaning-page .hotel-room-title{font-weight:800;color:#343a40;line-height:1.1}
		.hotel-cleaning-page .hotel-room-subtitle{font-size:.72rem;color:var(--hc-muted);margin-top:.15rem}
		.hotel-cleaning-page .hotel-actions{display:flex;align-items:center;gap:.35rem;flex-wrap:wrap;justify-content:flex-end}
		.hotel-cleaning-page .hotel-actions .btn{min-height:31px;border-radius:7px;font-weight:700}
		.hotel-cleaning-page .hotel-actions .btn-icon-clean{width:31px;padding:0;display:inline-flex;align-items:center;justify-content:center}
		.hotel-cleaning-page .hotel-empty{padding:2.8rem 1rem!important;color:var(--hc-muted)}
		.hotel-cleaning-page .hotel-pagination-wrap{display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;padding:1rem;border-top:1px solid var(--hc-border);background:#fff}
		.hotel-cleaning-page .hotel-pagination-info{margin:0;font-size:.82rem;color:var(--hc-muted);font-weight:600}
		.hotel-cleaning-page .hotel-pagination-info b{color:#343a40;font-weight:800}
		.hotel-cleaning-page .hotel-pagination{display:flex;align-items:center;gap:.35rem;margin:0;flex-wrap:wrap}
		.hotel-cleaning-page .hotel-pagination .page-link{min-width:34px;height:34px;padding:0 .65rem;border-radius:8px;border:1px solid #e2e8f0;color:#495057;font-weight:800;display:inline-flex;align-items:center;justify-content:center}
		.hotel-cleaning-page .hotel-pagination .page-item.active .page-link{background:#405189;border-color:#405189;color:#fff}
		.hotel-cleaning-page .hotel-pagination .page-item.disabled .page-link{background:#f8fafc;color:#adb5bd;cursor:not-allowed}
		.hotel-checklist-grid {
			display: flex;
			flex-direction: column;
			gap: 5px;
			padding: 10px;
			background: #f9fafb;
			border-radius: 10px;
			border: 1px solid #e5e7eb;
		}
		@media(max-width:767px){.hotel-cleaning-page .page-title-box{align-items:flex-start!important;flex-direction:column}.hotel-cleaning-page .hotel-pagination-wrap{align-items:stretch;flex-direction:column}.hotel-cleaning-page .hotel-pagination{justify-content:center}.hotel-cleaning-page .hotel-actions{justify-content:flex-start}}
	</style>
	<!-- ========== CONTENIDO PRINCIPAL ========== -->
	<div class="page-title-box d-flex align-items-center justify-content-between">
		<h4 class="hotel-page-title"><i class="ri-brush-3-line"></i> Órdenes de limpieza</h4>
		<button type="button" class="btn btn-primary btn-sm" v-on:click="abrirNueva()">
			<i class="ri-add-line me-1"></i>Nueva orden
		</button>
	</div>

	<div class="card mb-3 hotel-filter-card">
		<div class="card-body">
			<div class="row g-2 align-items-end">
				<div class="col-md-2">
					<label class="form-label mb-1">Desde</label>
					<input type="date" class="form-control form-control-sm" v-model="filtros.desde">
				</div>
				<div class="col-md-2">
					<label class="form-label mb-1">Hasta</label>
					<input type="date" class="form-control form-control-sm" v-model="filtros.hasta">
				</div>
				<div class="col-md-3">
					<label class="form-label mb-1">Habitacion</label>
					<select class="form-select form-select-sm" v-model="filtros.codhabitacion">
						<option value="0">Todas</option>
						<option v-for="h in habitaciones" v-bind:value="h.codhabitacion">{{h.numero}} - {{h.ambiente}}</option>
					</select>
				</div>
				<div class="col-md-3">
					<label class="form-label mb-1">Responsable</label>
					<select class="form-select form-select-sm" v-model="filtros.codresponsable">
						<option value="0">Todos</option>
						<option v-for="r in responsables" v-bind:value="r.codpersona">{{r.razonsocial}}</option>
					</select>
				</div>
				<div class="col-md-2">
					<label class="form-label mb-1">Estado</label>
					<select class="form-select form-select-sm" v-model="filtros.estado_orden">
						<option value="0">Todos</option>
						<option value="1">Pendiente</option>
						<option value="2">En proceso</option>
						<option value="3">Finalizado</option>
						<option value="4">Anulado</option>
					</select>
				</div>
				<div class="col-12 text-end">
					<button class="btn btn-sm btn-secondary" v-on:click="aplicarFiltros()">
						<i class="ri-search-line me-1"></i>Filtrar
					</button>
				</div>
			</div>
		</div>
	</div>

	<div class="card hotel-table-card">
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-hover align-middle hotel-table mb-0">
					<thead>
						<tr>
							<th>Orden</th>
							<th>Fecha</th>
							<th>Habitacion</th>
							<th>Responsable</th>
							<th>Tipo</th>
							<th>Estado</th>
							<th class="text-end" width="280">Acciones</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="o in ordenes">
							<td><span class="hotel-order-code">OL-{{o.numero_orden}}</span></td>
							<td>{{o.fecha}} {{o.hora}}</td>
							<td>
								<div class="hotel-room-title">{{o.numero}}</div>
								<div class="hotel-room-subtitle">{{o.ambiente}}</div>
							</td>
							<td>{{o.responsable || 'SIN RESPONSABLE'}}</td>
							<td>{{o.tipo_limpieza_texto}}</td>
							<td><span class="badge hotel-badge" v-bind:class="claseEstado(o.estado_orden)">{{o.estado_orden_texto}}</span></td>
							<td>
								<div class="hotel-actions">
									<button class="btn btn-outline-primary btn-sm btn-icon-clean" title="Ver" v-on:click="ver(o)"><i class="ri-eye-line"></i></button>
									<button class="btn btn-outline-secondary btn-sm btn-icon-clean" title="Editar" v-if="o.estado_orden==1 || o.estado_orden==2" v-on:click="editar(o)"><i class="ri-edit-line"></i></button>
									<button class="btn btn-outline-dark btn-sm btn-icon-clean" title="Imprimir" v-on:click="imprimir(o.codlimpieza)"><i class="ri-printer-line"></i></button>
									<button class="btn btn-outline-info btn-sm" v-if="o.estado_orden==1" v-on:click="cambiarEstado(o,2)">En proceso</button>
									<button class="btn btn-outline-success btn-sm" v-if="o.estado_orden==1 || o.estado_orden==2" v-on:click="finalizar(o)">Finalizar</button>
									<button class="btn btn-outline-danger btn-sm" v-if="o.estado_orden==1 || o.estado_orden==2" v-on:click="cambiarEstado(o,4)">Anular</button>
								</div>
							</td>
						</tr>
						<tr v-if="ordenes.length==0">
							<td colspan="7" class="text-center hotel-empty"><i class="ri-inbox-line d-block fs-3 mb-1"></i>No hay órdenes registradas</td>
						</tr>
					</tbody>
				</table>
			</div>
			<div class="hotel-pagination-wrap">
				<p class="hotel-pagination-info">
					Mostrando <b>{{paginacion.total ? paginacion.desde + 1 : 0}}</b> - <b>{{paginacion.hasta}}</b> de <b>{{paginacion.total}}</b> ordenes
				</p>
				<ul class="pagination hotel-pagination">
					<li class="page-item" v-bind:class="{disabled:paginacion.actual<=1}">
						<a class="page-link" href="#" v-on:click.prevent="cambiarPagina(paginacion.actual-1)"><i class="ri-arrow-left-s-line"></i></a>
					</li>
					<li class="page-item" v-for="pag in paginasVisibles" v-bind:class="{active:pag==paginacion.actual}">
						<a class="page-link" href="#" v-on:click.prevent="cambiarPagina(pag)">{{pag}}</a>
					</li>
					<li class="page-item" v-bind:class="{disabled:paginacion.actual>=paginacion.ultima}">
						<a class="page-link" href="#" v-on:click.prevent="cambiarPagina(paginacion.actual+1)"><i class="ri-arrow-right-s-line"></i></a>
					</li>
				</ul>
			</div>
		</div>
	</div>

	<!-- ========== MODAL ========== -->
	<!-- Modal Limpieza Ordenado -->
	<div class="modal fade show d-block hotel-modal" tabindex="-1" role="dialog" ref="modalLimpieza" v-if="modal" aria-modal="true">
		<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
			<div class="modal-content hotel-modal-content">
				<div class="modal-header hotel-modal-header">
					<div class="d-flex align-items-center gap-2">
						<div class="hotel-modal-icon"><i class="ri-brush-3-line"></i></div>
						<div>
							<h5 class="modal-title mb-0">{{tituloModal}}</h5>
							<div class="hotel-modal-subtitle">Registro y control de limpieza</div>
						</div>
					</div>
					<button type="button" class="btn-close btn-close-white" @click="cerrar()" aria-label="Cerrar"></button>
				</div>

				<div class="modal-body hotel-modal-body">
					<div class="hotel-form-section">
						<div class="hotel-section-title"><i class="ri-hotel-bed-line"></i> Información principal</div>
						<div class="row g-3">
							<div class="col-md-4">
								<label class="form-label">Habitación</label>
								<select class="form-select" v-model="form.codhabitacion" :disabled="soloVer">
									<option value="0">Seleccione</option>
									<option v-for="h in habitacionesDisponibles" :value="h.codhabitacion">{{h.numero}} - {{h.ambiente}}</option>
								</select>
							</div>
							<div class="col-md-4">
								<label class="form-label">Responsable</label>
								<select class="form-select" v-model="form.codresponsable" :disabled="soloVer">
									<option value="0">Sin responsable</option>
									<option v-for="r in responsables" :value="r.codpersona">{{r.razonsocial}}</option>
								</select>
							</div>
							<div class="col-md-4">
								<label class="form-label">Tipo de limpieza</label>
								<select class="form-select" v-model="form.tipo_limpieza" :disabled="soloVer">
									<option value="normal">Limpieza normal</option>
									<option value="profunda">Limpieza profunda</option>
									<option value="post_checkout">Post check-out</option>
									<option value="incidencia">Por incidencia</option>
								</select>
							</div>
						</div>
					</div>

					<div class="hotel-form-section">
						<div class="hotel-section-title">
							<i class="ri-checkbox-circle-line"></i> Checklist de limpieza
						</div>

						<div class="row g-2 mt-2">
							<div class="col-md-6" v-for="t in tareas">
								<label class="hotel-check-item" v-bind:class="{'is-disabled': soloVer}">
									<input type="checkbox" :value="t" v-model="form.checklist" :disabled="soloVer">
									<span>{{t}}</span>
								</label>
							</div>
						</div>
					</div>

					<div class="hotel-form-section mb-0">
						<div class="hotel-section-title"><i class="ri-file-text-line"></i> Observación</div>
						<textarea class="form-control" rows="3" v-model="form.observacion" :disabled="soloVer" placeholder="Escriba observaciones..."></textarea>
					</div>
				</div>

				<div class="modal-footer hotel-modal-footer">
					<button type="button" class="btn btn-light border" @click="cerrar()">Cerrar</button>
					<button type="button" class="btn btn-primary" v-if="!soloVer" @click="guardar()">
						<i class="ri-save-line me-1"></i>{{form.codlimpieza ? 'Actualizar orden' : 'Guardar orden'}}
					</button>
					<button type="button" class="btn btn-dark" v-if="soloVer" @click="imprimir(form.codlimpieza)">
						<i class="ri-printer-line me-1"></i>Imprimir ticket
					</button>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	new Vue({
		el: "#phuyu_hotel_limpieza",
		data: {
			habitaciones: [],
			responsables: [],
			ordenes: [],
			modal: false,
			soloVer: false,
			filtros: {
				desde: "",
				hasta: "",
				codhabitacion: 0,
				codresponsable: 0,
				estado_orden: 0,
				pagina: 1
			},
			paginacion: {total: 0, actual: 1, ultima: 1, desde: 0, hasta: 0},
			form: {
				codlimpieza: 0,
				codhabitacion: 0,
				codresponsable: 0,
				tipo_limpieza: "normal",
				checklist: [],
				observacion: ""
			},
			tareas: [
				"Cambio de sabanas",
				"Cambio de toallas",
				"Limpieza de bano",
				"Limpieza de ducha",
				"Limpieza de ventanas",
				"Barrido/trapeado de piso",
				"Reposicion de papel higienico",
				"Reposicion de jabon/shampoo",
				"Revision de frigobar",
				"Retiro de basura",
				"Desinfeccion general"
			]
		},
		computed: {
			habitacionesDisponibles: function() {
				return this.habitaciones.filter(function(h) {
					return h.situacion != 3 && h.situacion != 5 && h.situacion != 6;
				});
			},
			tituloModal: function() {
				if (this.soloVer) return "Detalle de orden";
				return this.form.codlimpieza ? "Editar orden de limpieza" : "Nueva orden de limpieza";
			},
			paginasVisibles: function() {
				var paginas = [];
				var actual = parseInt(this.paginacion.actual || 1);
				var ultima = parseInt(this.paginacion.ultima || 1);
				var inicio = Math.max(1, actual - 2);
				var fin = Math.min(ultima, inicio + 4);
				inicio = Math.max(1, fin - 4);
				for (var i = inicio; i <= fin; i++) { paginas.push(i); }
				return paginas;
			}
		},
		methods: {
			hoy: function() {
				var d = new Date();
				return d.getFullYear() + "-" + String(d.getMonth() + 1).padStart(2, "0") + "-" + String(d.getDate()).padStart(2, "0");
			},
			cargarHabitaciones: function() {
				this.$http.post(url + "hotel/limpieza/habitaciones").then(function(data) {
					this.habitaciones = data.body;
				});
			},
			cargarResponsables: function() {
				this.$http.post(url + "hotel/limpieza/responsables").then(function(data) {
					this.responsables = data.body;
				});
			},
			cargarOrdenes: function() {
				this.$http.post(url + "hotel/limpieza/listar", this.filtros).then(function(data) {
					this.ordenes = data.body.lista || [];
					this.paginacion = data.body.paginacion || {total: 0, actual: 1, ultima: 1, desde: 0, hasta: 0};
				});
			},
			aplicarFiltros: function() {
				this.filtros.pagina = 1;
				this.cargarOrdenes();
			},
			cambiarPagina: function(pagina) {
				pagina = parseInt(pagina || 1);
				if (pagina < 1 || pagina > parseInt(this.paginacion.ultima || 1) || pagina == parseInt(this.paginacion.actual || 1)) { return; }
				this.filtros.pagina = pagina;
				this.cargarOrdenes();
			},
			claseEstado: function(estado) {
				estado = parseInt(estado);
				if (estado == 1) return "bg-warning-subtle text-warning";
				if (estado == 2) return "bg-info-subtle text-info";
				if (estado == 3) return "bg-success-subtle text-success";
				return "bg-danger-subtle text-danger";
			},
			abrirNueva: function() {
				this.soloVer = false;
				this.form = {
					codlimpieza: 0,
					codhabitacion: 0,
					codresponsable: 0,
					tipo_limpieza: "normal",
					checklist: this.tareas.slice(),
					observacion: ""
				};
				this.abrirModal();
			},
			ver: function(o) {
				this.soloVer = true;
				this.form = {
					codlimpieza: o.codlimpieza,
					codhabitacion: o.codhabitacion,
					codresponsable: o.codresponsable,
					tipo_limpieza: o.tipo_limpieza || "normal",
					checklist: o.checklist_items || [],
					observacion: o.observacion || ""
				};
				this.abrirModal();
			},
			editar: function(o) {
				this.soloVer = false;
				this.form = {
					codlimpieza: o.codlimpieza,
					codhabitacion: o.codhabitacion,
					codresponsable: o.codresponsable,
					tipo_limpieza: o.tipo_limpieza || "normal",
					checklist: o.checklist_items || [],
					observacion: o.observacion || ""
				};
				this.abrirModal();
			},
			abrirModal: function() {
				this.modal = true;
				this.$nextTick(function() {
					if (window.bootstrap && this.$refs.modalLimpieza) {
						var self = this;
						this.$refs.modalLimpieza.addEventListener("hidden.bs.modal", function() {
							self.modal = false;
						}, {
							once: true
						});
						bootstrap.Modal.getOrCreateInstance(this.$refs.modalLimpieza).show();
					}
				});
			},
			cerrar: function() {
				if (window.bootstrap && this.$refs.modalLimpieza) {
					bootstrap.Modal.getOrCreateInstance(this.$refs.modalLimpieza).hide();
				} else {
					this.modal = false;
				}
			},
			guardar: function() {
				if (parseInt(this.form.codhabitacion) == 0) {
					phuyu_sistema.phuyu_alerta("SELECCIONE UNA HABITACION", "", "warning");
					return;
				}
				var esNuevo = parseInt(this.form.codlimpieza || 0) == 0;
				this.$http.post(url + "hotel/limpieza/guardar", this.form).then(function(data) {
					if (data.body.estado == 1) {
						phuyu_sistema.phuyu_noti(esNuevo ? "ORDEN DE LIMPIEZA REGISTRADA" : "ORDEN DE LIMPIEZA ACTUALIZADA", "", "success");
						this.cerrar();
						this.cargarHabitaciones();
						this.cargarOrdenes();
						if (esNuevo) {
							this.imprimir(data.body.codlimpieza);
						}
					} else {
						phuyu_sistema.phuyu_alerta(data.body.mensaje || "NO SE PUDO REGISTRAR", "", "error");
					}
				});
			},
			cambiarEstado: function(o, estado) {
				this.$http.post(url + "hotel/limpieza/cambiar_estado", {
					codlimpieza: o.codlimpieza,
					estado_orden: estado
				}).then(function(data) {
					if (data.body.estado == 1) {
						phuyu_sistema.phuyu_noti("ORDEN ACTUALIZADA", "", "success");
						this.cargarOrdenes();
					} else {
						phuyu_sistema.phuyu_alerta(data.body.mensaje || "NO SE PUDO ACTUALIZAR", "", "error");
					}
				});
			},
			finalizar: function(o) {
				this.$http.post(url + "hotel/limpieza/finalizar", {
					codlimpieza: o.codlimpieza
				}).then(function(data) {
					if (data.body.estado == 1) {
						phuyu_sistema.phuyu_noti("LIMPIEZA FINALIZADA", "", "success");
						this.cargarHabitaciones();
						this.cargarOrdenes();
					} else {
						phuyu_sistema.phuyu_alerta(data.body.mensaje || "NO SE PUDO FINALIZAR", "", "error");
					}
				});
			},
			imprimir: function(codlimpieza) {
				window.open(url + "hotel/limpieza/ticket/" + codlimpieza, "_blank");
			}
		},
		created: function() {
			this.filtros.desde = this.hoy();
			this.filtros.hasta = this.hoy();
			this.cargarHabitaciones();
			this.cargarResponsables();
			this.cargarOrdenes();
		}
	});
</script>
