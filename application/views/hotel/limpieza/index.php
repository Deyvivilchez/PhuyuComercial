<div id="phuyu_hotel_limpieza" class="hotel-cleaning-page">
	<style>
		/* ========== BACKDROP DEL MODAL ========== */
		.modal-backdrop {
			position: fixed !important;
			top: 0 !important;
			left: 0 !important;
			width: 100vw !important;
			height: 100vh !important;
			background-color: rgba(0, 0, 0, 0.5) !important;
			z-index: 1040 !important;
		}

		/* ========== MODAL ========== */
		.hotel-modal {
			z-index: 1050 !important;
		}

		.hotel-modal .modal-dialog {
			max-width: 700px !important;
		}

		.hotel-modal .modal-content {
			border: none !important;
			border-radius: 16px !important;
			box-shadow: 0 20px 60px rgba(0,0,0,0.3) !important;
			overflow: hidden !important;
		}

		.hotel-modal .modal-header {
			background: linear-gradient(135deg, #4f46e5, #7c3aed) !important;
			color: #fff !important;
			padding: 16px 20px !important;
			border-bottom: none !important;
			display: flex !important;
			align-items: center !important;
			justify-content: space-between !important;
		}

		.hotel-modal .modal-title {
			font-size: 18px !important;
			font-weight: 700 !important;
		}

		.hotel-modal-subtitle {
			font-size: 12px !important;
			opacity: 0.8 !important;
			margin-top: 2px !important;
		}

		.hotel-modal-close {
			background: rgba(255,255,255,0.2) !important;
			border: none !important;
			color: #fff !important;
			width: 32px !important;
			height: 32px !important;
			border-radius: 8px !important;
			font-size: 18px !important;
			cursor: pointer !important;
			display: flex !important;
			align-items: center !important;
			justify-content: center !important;
		}

		.hotel-modal .modal-body {
			padding: 20px !important;
		}

		.hotel-modal .modal-footer {
			border-top: 1px solid #e5e7eb !important;
			padding: 14px 20px !important;
			background: #f9fafb !important;
		}

		/* ========== SECCIONES DEL FORMULARIO ========== */
		.hotel-form-section {
			margin-bottom: 20px !important;
		}

		.hotel-section-title {
			font-size: 14px !important;
			font-weight: 700 !important;
			color: #374151 !important;
			margin-bottom: 10px !important;
			display: flex !important;
			align-items: center !important;
			gap: 6px !important;
			padding-bottom: 8px !important;
			border-bottom: 2px solid #e5e7eb !important;
		}

		/* ========== CHECKLIST ========== */
		.hotel-checklist-grid {
			display: grid !important;
			grid-template-columns: repeat(2, 1fr) !important;
			gap: 8px !important;
			padding: 12px !important;
			background: #f9fafb !important;
			border-radius: 10px !important;
			border: 1px solid #e5e7eb !important;
		}

		.hotel-check-item {
			display: flex !important;
			align-items: center !important;
			gap: 8px !important;
			padding: 8px 10px !important;
			border-radius: 6px !important;
			cursor: pointer !important;
			margin: 0 !important;
			transition: background 0.15s !important;
		}

		.hotel-check-item:hover {
			background: #e0e7ff !important;
		}

		.hotel-check-item.is-disabled {
			opacity: 0.7 !important;
			cursor: not-allowed !important;
		}

		.hotel-check-item input[type="checkbox"] {
			width: 18px !important;
			height: 18px !important;
			accent-color: #4f46e5 !important;
			cursor: pointer !important;
		}

		.hotel-check-item span {
			font-size: 14px !important;
			color: #374151 !important;
		}

		/* ========== TABLA ========== */
		.hotel-table-card {
			box-shadow: 0 2px 8px rgba(0,0,0,0.08) !important;
			border-radius: 12px !important;
		}

		.hotel-order-code {
			font-weight: 700 !important;
			color: #4f46e5 !important;
		}

		.hotel-room-title {
			font-weight: 600 !important;
		}

		.hotel-room-subtitle {
			font-size: 12px !important;
			color: #6b7280 !important;
		}

		.hotel-badge {
			font-size: 11px !important;
			padding: 5px 10px !important;
			border-radius: 6px !important;
			font-weight: 600 !important;
			letter-spacing: 0.3px !important;
		}

		.hotel-actions {
			display: flex !important;
			gap: 4px !important;
			flex-wrap: wrap !important;
		}

		.hotel-empty {
			padding: 40px !important;
			color: #9ca3af !important;
		}

		/* ========== RESPONSIVE ========== */
		@media (max-width: 768px) {
			.hotel-checklist-grid {
				grid-template-columns: 1fr !important;
			}
			
			.hotel-modal .modal-dialog {
				margin: 10px !important;
			}
		}
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
					<button class="btn btn-sm btn-secondary" v-on:click="cargarOrdenes()">
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
							<th width="330">Acciones</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="o in ordenes">
							<td><span class="hotel-order-code">OL-{{o.numero_orden}}</span></td>
							<td>{{o.fecha}} {{o.hora}}</td>
							<td><div class="hotel-room-title">{{o.numero}}</div><div class="hotel-room-subtitle">{{o.ambiente}}</div></td>
							<td>{{o.responsable || 'SIN RESPONSABLE'}}</td>
							<td>{{o.tipo_limpieza_texto}}</td>
							<td><span class="badge hotel-badge" v-bind:class="claseEstado(o.estado_orden)">{{o.estado_orden_texto}}</span></td>
							<td>
								<div class="hotel-actions">
									<button class="btn btn-outline-primary btn-sm" v-on:click="ver(o)"><i class="ri-eye-line"></i></button>
									<button class="btn btn-outline-secondary btn-sm" v-if="o.estado_orden==1 || o.estado_orden==2" v-on:click="editar(o)"><i class="ri-edit-line"></i></button>
									<button class="btn btn-outline-dark btn-sm" v-on:click="imprimir(o.codlimpieza)"><i class="ri-printer-line"></i></button>
									<button class="btn btn-outline-info btn-sm" v-if="o.estado_orden==1" v-on:click="cambiarEstado(o,2)">En proceso</button>
									<button class="btn btn-outline-success btn-sm" v-if="o.estado_orden==1 || o.estado_orden==2" v-on:click="finalizar(o)">Finalizar</button>
									<button class="btn btn-outline-danger btn-sm" v-if="o.estado_orden==1 || o.estado_orden==2" v-on:click="cambiarEstado(o,4)">Anular</button>
								</div>
							</td>
						</tr>
						<tr v-if="ordenes.length==0"><td colspan="7" class="text-center hotel-empty"><i class="ri-inbox-line d-block fs-3 mb-1"></i>No hay órdenes registradas</td></tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<!-- ========== BACKDROP OSCURO ========== -->
	<div class="modal-backdrop fade show" v-if="modal" @click="cerrar()"></div>

	<!-- ========== MODAL ========== -->
	<div class="modal fade show d-block hotel-modal" tabindex="-1" role="dialog" v-if="modal" aria-modal="true">
		<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<div>
						<h5 class="modal-title">{{tituloModal}}</h5>
						<div class="hotel-modal-subtitle">Registro y control de limpieza</div>
					</div>
					<button type="button" class="hotel-modal-close" @click="cerrar()" aria-label="Cerrar">
						<i class="ri-close-line"></i>
					</button>
				</div>

				<div class="modal-body">
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
						<div class="hotel-section-title"><i class="ri-checkbox-circle-line"></i> Checklist de limpieza</div>
						<div class="hotel-checklist-grid">
							<label class="hotel-check-item" v-for="t in tareas" v-bind:class="{'is-disabled': soloVer}">
								<input type="checkbox" :value="t" v-model="form.checklist" :disabled="soloVer">
								<span>{{t}}</span>
							</label>
						</div>
					</div>

					<div class="hotel-form-section">
						<div class="hotel-section-title"><i class="ri-file-text-line"></i> Observación</div>
						<textarea class="form-control" rows="3" v-model="form.observacion" :disabled="soloVer" placeholder="Escriba observaciones..."></textarea>
					</div>
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-light" @click="cerrar()">Cerrar</button>
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
			estado_orden: 0
		},
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
				this.ordenes = data.body;
			});
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
			document.body.classList.add("modal-open");
			document.body.style.overflow = "hidden";
			this.modal = true;
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
			document.body.classList.add("modal-open");
			document.body.style.overflow = "hidden";
			this.modal = true;
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
			document.body.classList.add("modal-open");
			document.body.style.overflow = "hidden";
			this.modal = true;
		},
		cerrar: function() {
			this.modal = false;
			document.body.classList.remove("modal-open");
			document.body.style.overflow = "";
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