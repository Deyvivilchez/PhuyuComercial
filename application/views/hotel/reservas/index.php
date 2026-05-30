
<div id="phuyu_hotel_reservas" class="hotel-reservas-app">
	<style>
		.hotel-reservas-app{--hz-border:var(--vz-border-color,#e9ebec);--hz-muted:var(--vz-secondary-color,#878a99);--hz-text:var(--vz-body-color,#212529)}
		.hotel-page{display:flex;flex-direction:column;gap:1rem;padding-bottom:1rem}
		.hotel-topbar{display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:.25rem}
		.hotel-topbar h4{margin:0;font-weight:700;color:var(--hz-text);display:flex;align-items:center;gap:.45rem;font-size:1.1rem}
		.hotel-topbar .text-muted{font-size:.78rem}
		.hotel-topbar .btn{height:37px;display:inline-flex;align-items:center}
		.hotel-filters{border:1px solid var(--hz-border);background:var(--vz-card-bg,#fff);border-radius:.25rem;padding:1rem;box-shadow:0 1px 2px rgba(56,65,74,.06)}
		.hotel-filter-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.75rem}
		.hotel-filter-field label{display:block;margin:0 0 .35rem;font-size:.7rem;font-weight:700;text-transform:uppercase;color:var(--hz-muted)}
		.hotel-filter-field .form-select,.hotel-filter-field .form-control{height:36px;border-radius:.25rem;border-color:var(--hz-border);font-size:.78rem;box-shadow:none}
		.hotel-status-strip{display:flex;flex-wrap:wrap;gap:.5rem;margin-top:.85rem}
		.hotel-status-pill{display:inline-flex;align-items:center;gap:.45rem;border:1px solid var(--hz-border);border-radius:999px;background:#fff;padding:.35rem .65rem;font-size:.73rem;font-weight:700;color:#495057}
		.hotel-status-pill b{font-size:.85rem;color:var(--hz-text)}
		.hotel-status-dot{width:.5rem;height:.5rem;border-radius:999px;display:inline-block}
		.hotel-heatmap-card{border:1px solid var(--hz-border);background:var(--vz-card-bg,#fff);border-radius:.25rem;box-shadow:0 1px 2px rgba(56,65,74,.06);overflow:hidden}
		.hotel-heatmap-head{display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;padding:.9rem 1rem;border-bottom:1px solid var(--hz-border)}
		.hotel-heatmap-head h5{margin:0;font-weight:700;color:var(--hz-text);font-size:.98rem}
			.hotel-heatmap-head .small{color:var(--hz-muted);font-size:.76rem}
			.hotel-heatmap-body{padding:.75rem 1rem .5rem;background:#fff}
			#reservas_ocupacion_heatmap{min-height:260px}
			.hotel-heatmap-body .apexcharts-heatmap-rect{cursor:pointer}
			.hotel-heatmap-empty{display:flex;align-items:center;justify-content:center;min-height:220px;color:var(--hz-muted);font-size:.85rem;border:1px dashed var(--hz-border);border-radius:.25rem;background:#f8f9fa}
		.hotel-planner{border:1px solid var(--hz-border);background:var(--vz-card-bg,#fff);border-radius:.25rem;box-shadow:0 1px 2px rgba(56,65,74,.06);overflow:hidden}
		.hotel-planner-head{display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;padding:.9rem 1rem;border-bottom:1px solid var(--hz-border);background:var(--vz-card-cap-bg,#fff)}
		.hotel-planner-head h5{margin:0;font-weight:700;color:var(--hz-text);font-size:.98rem}
		.hotel-planner-head .small{color:var(--hz-muted);font-size:.76rem}
		.hotel-legend{display:flex;flex-wrap:wrap;gap:.4rem;align-items:center}
		.hotel-legend .badge{border-radius:999px;padding:.38rem .58rem;font-weight:700}
		.hotel-planner-body{padding:.8rem;background:#f6f8fb}
		.hotel-calendar-wrap{width:100%;max-width:100%;border:1px solid var(--hz-border);border-radius:.25rem;overflow:hidden;background:#fff}
		.hotel-calendar-board{overflow:auto;width:100%;height:calc(100vh - 340px);min-height:430px;max-height:620px;background:#fff}
		.hotel-calendar-board::-webkit-scrollbar{height:10px;width:10px}
		.hotel-calendar-board::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:999px;border:2px solid #f8fafc}
		.hotel-calendar-board::-webkit-scrollbar-track{background:#f8fafc}
		.hotel-calendar-table{width:max-content;min-width:100%;margin:0;border-collapse:separate;border-spacing:0;table-layout:fixed;background:#fff}
		.hotel-calendar-table th{position:sticky;top:0;z-index:4;background:#f8f9fa;border-bottom:1px solid var(--hz-border);border-left:1px solid #edf1f7;width:58px;min-width:58px;max-width:58px;height:48px;padding:.35rem .2rem;text-align:center;font-size:.68rem;font-weight:700;color:#495057;vertical-align:middle}
		.hotel-calendar-table td{width:58px;min-width:58px;max-width:58px;height:52px;padding:3px;border-bottom:1px solid #edf1f7;border-left:1px solid #edf1f7;background:#fff;vertical-align:middle;text-align:center}
		.hotel-calendar-table tbody tr:hover td:not(.hotel-calendar-room){background:#fbfcff}
		.hotel-calendar-room{position:sticky;left:0;z-index:7;width:165px!important;min-width:165px!important;max-width:165px!important;background:#fff!important;border-left:0!important;border-right:1px solid var(--hz-border)!important;box-shadow:4px 0 10px rgba(56,65,74,.04);padding:.55rem .75rem!important;color:var(--hz-text);vertical-align:middle!important;text-align:left!important;font-weight:700}
		.hotel-calendar-table thead .hotel-calendar-room{top:0;z-index:10;background:#f8f9fa!important;color:var(--hz-muted);text-transform:uppercase;font-size:.68rem}
		.hotel-room-title{font-size:.86rem;font-weight:700;color:var(--hz-text);text-transform:none}
		.hotel-calendar-room small{display:block;margin-top:.14rem;color:var(--hz-muted);font-size:.68rem;font-weight:600;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
		.hotel-day{height:100%;min-height:46px;position:relative;cursor:pointer;overflow:hidden;display:flex;align-items:center;justify-content:center;transition:.12s ease;border-radius:.2rem;background:#f8f9fa}
		.hotel-day:hover{box-shadow:inset 0 0 0 2px rgba(var(--vz-primary-rgb,64,81,137),.18);background:#fff}
		.hotel-day.libre{background:#e8f7f3}
		.hotel-day.libre:before{content:'';width:7px;height:7px;border-radius:50%;background:var(--vz-success,#0ab39c);box-shadow:0 0 0 4px rgba(10,179,156,.12)}
		.hotel-day.selected{outline:2px solid var(--vz-primary,#405189);outline-offset:-2px}
		.hotel-day-event{width:100%;height:100%;border-radius:.2rem;padding:.28rem .25rem;font-size:.62rem;line-height:1.05;font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;display:flex;align-items:center;justify-content:center;box-shadow:none}
		.hotel-day-event span{display:none}
		.hotel-day-free-label{display:none}
		.hotel-day.reserva .hotel-day-event{background:#e8f3ff;color:var(--vz-info,#299cdb)}
		.hotel-day.pendiente .hotel-day-event{background:#fff7e6;color:var(--vz-warning,#f7b84b)}
		.hotel-day.estadia .hotel-day-event{background:#fdeeea;color:var(--vz-danger,#f06548)}
		.hotel-day.mantenimiento .hotel-day-event{background:#f3f6f9;color:var(--vz-secondary,#878a99)}
		.hotel-day.limpieza .hotel-day-event{background:#f1edff;color:var(--vz-primary,#405189)}
		.hotel-reserva-modal .modal-dialog{max-width:820px}
		.hotel-dialog-body{padding:16px}
		.hotel-dialog-actions{gap:8px;flex-wrap:wrap}
		.hotel-reserva-form .form-label{font-size:12px;font-weight:700;color:#495057;margin-bottom:.25rem}
		.hotel-reserva-form textarea{min-height:82px}
		.hotel-form-box{border:1px solid #eef1f5;border-radius:12px;padding:.9rem;background:#fbfcfe;margin-bottom:.9rem}
		@media(max-width:991px){.hotel-filter-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.hotel-calendar-wrap{width:100%}}
		@media(max-width:575px){.hotel-filter-grid{grid-template-columns:1fr}.hotel-calendar-board{height:430px;min-height:430px;max-height:430px}.hotel-calendar-table th,.hotel-calendar-table td{width:52px;min-width:52px;max-width:52px}.hotel-calendar-room{width:140px!important;min-width:140px!important;max-width:140px!important}}
	</style>

	<div class="hotel-page">
		<div class="hotel-topbar">
			<div>
				<h4><i class="ri-calendar-check-line"></i> Reservas hotel</h4>
				<div class="text-muted">Planning mensual de habitaciones</div>
			</div>
			<button class="btn btn-primary" v-on:click="abrir_nueva()"><i class="ri-add-line me-1"></i>Nueva reserva</button>
		</div>

		<div class="hotel-filters">
			<div class="hotel-filter-grid">
				<div class="hotel-filter-field">
					<label>Sucursal</label>
					<select class="form-select form-select-sm" v-model="cal.filtro.codsucursal" v-on:change="cargar_calendario()">
						<?php foreach ($sucursales as $sucursal) { ?>
							<option value="<?php echo $sucursal["codsucursal"];?>"><?php echo $sucursal["descripcion"];?></option>
						<?php } ?>
					</select>
				</div>
				<div class="hotel-filter-field">
					<label>Ambiente / piso</label>
					<select class="form-select form-select-sm" v-model="cal.filtro.codambiente" v-on:change="cargar_calendario()">
						<option value="0">Todos los ambientes</option>
						<?php foreach ($ambientes as $ambiente) { ?>
							<option value="<?php echo $ambiente["codambiente"];?>"><?php echo $ambiente["descripcion"];?></option>
						<?php } ?>
					</select>
				</div>
				<div class="hotel-filter-field">
					<label>Habitacion</label>
					<select class="form-select form-select-sm" v-model="cal.filtro.codhabitacion" v-on:change="cargar_calendario()">
						<option value="0">Todas las habitaciones</option>
						<?php foreach ($habitaciones as $habitacion) { ?>
							<option value="<?php echo $habitacion["codhabitacion"];?>"><?php echo $habitacion["ambiente"]." / ".$habitacion["numero"];?></option>
						<?php } ?>
					</select>
				</div>
				<div class="hotel-filter-field">
					<label>Mes</label>
					<input type="month" class="form-control form-control-sm" v-model="cal.mesvalor" v-on:change="cambiar_mes()">
				</div>
			</div>
				<div class="hotel-status-strip">
					<span class="hotel-status-pill"><i class="hotel-status-dot bg-success"></i><b>{{resumen_estado.libre}}</b> Libres</span>
					<span class="hotel-status-pill"><i class="hotel-status-dot bg-warning"></i><b>{{resumen_estado.pendiente}}</b> Pendientes</span>
					<span class="hotel-status-pill"><i class="hotel-status-dot bg-info"></i><b>{{resumen_estado.confirmada}}</b> Confirmadas</span>
					<span class="hotel-status-pill"><i class="hotel-status-dot bg-danger"></i><b>{{resumen_estado.ocupada}}</b> Ocupadas</span>
					<span class="hotel-status-pill"><i class="hotel-status-dot bg-secondary"></i><b>{{resumen_estado.mantenimiento}}</b> Mantenimiento</span>
					<span class="hotel-status-pill"><i class="hotel-status-dot bg-primary"></i><b>{{resumen_estado.limpieza}}</b> Limpieza</span>
				</div>
			</div>

			<div class="hotel-heatmap-card">
				<div class="hotel-heatmap-head">
					<div>
						<h5>Mapa de ocupacion</h5>
						<div class="small">Vista rapida por habitacion y dia, estilo Heatmap - Range Without Shades.</div>
					</div>
					<div class="hotel-legend">
						<span class="badge bg-success-subtle text-success">Libre</span>
						<span class="badge bg-warning-subtle text-warning">Pendiente</span>
						<span class="badge bg-info-subtle text-info">Confirmada</span>
						<span class="badge bg-danger-subtle text-danger">Ocupada</span>
						<span class="badge bg-secondary-subtle text-secondary">Bloqueo</span>
					</div>
				</div>
				<div class="hotel-heatmap-body">
					<div id="reservas_ocupacion_heatmap" v-show="cal.filas.length"></div>
					<div class="hotel-heatmap-empty" v-show="!cal.filas.length">Sin habitaciones para graficar</div>
				</div>
			</div>

			<div class="hotel-planner">
				<div class="hotel-planner-head">
					<div>
						<h5>Grilla detallada</h5>
						<div class="small">Vista operativa completa por habitacion y dia.</div>
					</div>
					<button type="button" class="btn btn-sm btn-soft-primary" v-on:click="mostrarGrilla = !mostrarGrilla">
						<i class="ri-layout-grid-line me-1"></i>{{mostrarGrilla ? 'Ocultar grilla' : 'Ver grilla'}}
					</button>
				</div>
				<div class="hotel-planner-body" v-show="mostrarGrilla">
					<div class="hotel-calendar-wrap">
						<div class="hotel-calendar-board">
						<table class="table hotel-calendar-table">
							<thead>
								<tr>
									<th class="hotel-calendar-room text-start">Habitacion</th>
									<th v-for="d in cal.dias" v-bind:class="clase_cabecera_dia(d)">
										<small class="text-muted d-block">{{d.nombre || ''}}</small>
										<div class="fs-6">{{d.dia}}</div>
									</th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="fila in cal.filas">
									<td class="hotel-calendar-room">
										<div class="hotel-room-title">Hab. {{fila.habitacion.numero}}</div>
										<small>{{fila.habitacion.tipo || 'Habitacion'}}</small>
										<small>{{fila.habitacion.ambiente}}</small>
									</td>
									<td v-for="dia in fila.dias" v-bind:class="clase_cabecera_dia(dia)">
										<div class="hotel-day" v-bind:class="[clase_dia(dia), es_seleccionado(fila.habitacion.codhabitacion,dia.fecha) ? 'selected' : '']"
											v-on:click="click_dia(fila.habitacion,dia)"
											v-bind:title="titulo_dia(dia)">
											<div v-if="dia.evento" class="hotel-day-event">
												<strong>{{dia.evento.estado || dia.evento.descripcion || 'Reserva'}}</strong>
												<span v-if="dia.evento.tipo=='reserva' || dia.evento.tipo=='estadia'">{{dia.evento.cliente || 'Sin cliente'}}</span>
												<span v-if="dia.evento.tipo!='reserva' && dia.evento.tipo!='estadia'">{{dia.evento.descripcion || dia.evento.estado}}</span>
											</div>
											<div v-if="!dia.evento" class="hotel-day-free-label"><span class="hotel-day-free-dot"></span></div>
										</div>
									</td>
								</tr>
								<tr v-if="cal.filas.length==0">
									<td v-bind:colspan="cal.dias.length + 1" class="text-center text-muted py-4">No hay habitaciones para el filtro seleccionado</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="modal fade hotel-reserva-modal" tabindex="-1" ref="modalReserva" v-if="modalReserva">
		<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
			<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">{{form.codreserva ? 'Editar reserva' : 'Nueva reserva'}}</h5>
				<button type="button" class="btn-close" v-on:click="cerrar_modal('modalReserva')" aria-label="Cerrar"></button>
			</div>
			<div class="modal-body hotel-dialog-body hotel-reserva-form">
				<div class="hotel-form-box">
					<div class="row g-2 align-items-end">
						<div class="col-md-9">
							<label class="form-label">Cliente</label>
							<select id="hotel_reserva_codpersona" class="form-select">
								<option v-if="form.codpersona" v-bind:value="form.codpersona">{{form.cliente}}</option>
							</select>
						</div>
						<div class="col-md-3">
							<label class="form-label">&nbsp;</label>
							<button type="button" class="btn btn-primary w-100 hotel-client-add-btn" v-on:click="phuyu_addcliente()" title="Registrar nuevo cliente">
								<i class="ri-user-add-line me-1"></i> Nuevo
							</button>
						</div>
						<div class="col-12" v-if="form.codpersona">
							<div class="alert alert-info py-2 mb-0">{{form.documento}} - {{form.cliente}}</div>
						</div>
					</div>
				</div>
				<div class="hotel-form-box">
					<div class="row g-2">
						<div class="col-md-6"><label class="form-label">Llegada</label><input type="date" class="form-control" v-model="form.fechallegada"></div>
						<div class="col-md-6"><label class="form-label">Salida</label><input type="date" class="form-control" v-model="form.fechasalida"></div>
						<div class="col-md-6">
							<label class="form-label">Ambiente</label>
							<select class="form-select" v-model="form.codambiente" v-on:change="consultar_disponibilidad()">
								<option value="0">TODOS</option>
								<?php foreach ($ambientes as $ambiente) { ?>
									<option value="<?php echo $ambiente["codambiente"];?>"><?php echo $ambiente["descripcion"];?> </option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-6">
							<label class="form-label">Habitacion</label>
							<select class="form-select" v-model="form.codhabitacion">
								<option value="0">SELECCIONE</option>
								<option v-for="habitacion in habitaciones" v-bind:value="habitacion.codhabitacion">{{habitacion.ambiente}} / {{habitacion.numero}}</option>
							</select>
						</div>
						<div class="col-md-6">
							<label class="form-label">Estado</label>
							<select class="form-select" v-model="form.situacion"><option value="1">Pendiente</option><option value="2">Confirmada</option></select>
						</div>
						<div class="col-md-12"><label class="form-label">Observacion</label><textarea class="form-control" v-model="form.observacion"></textarea></div>
					</div>
				</div>
			</div>
			<div class="modal-footer hotel-dialog-actions">
				<button class="btn btn-light" v-on:click="cerrar_modal('modalReserva')">Cancelar</button>
				<button class="btn btn-primary" v-on:click="guardar_reserva()"><i class="ri-save-line me-1"></i>Guardar reserva</button>
			</div>
			</div>
		</div>
	</div>

	<div class="modal fade hotel-reserva-modal" tabindex="-1" ref="modalDetalle" v-if="modalDetalle">
		<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
			<div class="modal-content">
			<div class="modal-header"><h5 class="modal-title">Detalle de reserva</h5><button type="button" class="btn-close" v-on:click="cerrar_modal('modalDetalle')" aria-label="Cerrar"></button></div>
			<div class="modal-body hotel-dialog-body" v-if="detalle">
				<div class="row g-3">
					<div class="col-md-6"><div class="text-muted">Cliente / responsable</div><strong>{{detalle.cliente || detalle.descripcion || 'Sin cliente registrado'}}</strong></div>
					<div class="col-md-3"><div class="text-muted">Estado</div><span class="badge" v-bind:class="clase_estado(detalle.situacion)">{{situacion_texto(detalle.situacion)}}</span></div>
					<div class="col-md-3"><div class="text-muted">Habitacion</div><strong>{{detalle.numero}}</strong></div>
					<div class="col-md-6"><div class="text-muted">Rango</div>{{detalle.fechallegada}} / {{detalle.fechasalida}}</div>
					<div class="col-md-6"><div class="text-muted">Ambiente</div>{{detalle.ambiente}}</div>
					<div class="col-12"><div class="text-muted">Observacion</div>{{detalle.observacion}}</div>
				</div>
			</div>
			<div class="modal-footer hotel-dialog-actions">
				<button class="btn btn-light" v-on:click="cerrar_modal('modalDetalle')">Cerrar</button>
				<button class="btn btn-primary" v-if="detalle && (detalle.situacion==1 || detalle.situacion==2)" v-on:click="editar_reserva(detalle)"><i class="ri-edit-line me-1"></i>Editar reserva</button>
				<button class="btn btn-info" v-if="detalle && detalle.situacion==1" v-on:click="confirmar_reserva(detalle)"><i class="ri-check-line me-1"></i>Confirmar</button>
				<button class="btn btn-success" v-if="detalle && detalle.situacion==2" v-on:click="abrir_checkin(detalle)"><i class="ri-login-circle-line me-1"></i>Check-in</button>
				<button class="btn btn-danger" v-if="detalle && (detalle.situacion==1 || detalle.situacion==2)" v-on:click="anular_reserva(detalle)">Anular</button>
			</div>
			</div>
		</div>
	</div>

	<div class="modal fade hotel-reserva-modal" tabindex="-1" ref="modalCheckin" v-if="modalCheckin">
		<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
			<div class="modal-content">
			<div class="modal-header"><h5 class="modal-title">Realizar check-in</h5><button type="button" class="btn-close" v-on:click="cerrar_modal('modalCheckin')" aria-label="Cerrar"></button></div>
			<div class="modal-body hotel-dialog-body hotel-reserva-form"><div class="row g-3"><div class="col-md-4"><label class="form-label">Fecha check-in</label><input type="date" class="form-control" v-model="checkin.fecha_checkin"></div><div class="col-md-8"><label class="form-label">Observacion</label><input class="form-control" v-model="checkin.observacion"></div></div></div>
			<div class="modal-footer hotel-dialog-actions"><button class="btn btn-light" v-on:click="cerrar_modal('modalCheckin')">Cancelar</button><button class="btn btn-success" v-on:click="realizar_checkin()">Iniciar hospedaje</button></div>
			</div>
		</div>
	</div>
</div>
<script>window.phuyu_hotel_codsucursal = <?php echo (int)$_SESSION["phuyu_codsucursal"]; ?>;</script>
<script src="<?php echo base_url();?>public/plantilla_phuyu/libs/apexcharts/apexcharts.min.js"></script>
<script src="<?php echo base_url();?>phuyu/phuyu_hotel/reservas.js?v=<?php echo filemtime(FCPATH."phuyu/phuyu_hotel/reservas.js"); ?>"></script>
