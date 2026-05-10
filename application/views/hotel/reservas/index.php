
<div id="phuyu_hotel_reservas" class="hotel-reservas-app">
	<style>
		.hotel-reservas-app{--p:#405189;--border:#e5eaf0;--muted:#64748b;--text:#1f2937;--blue:#dbeafe;--yellow:#fef3c7;--green:#ccfbf1;--red:#fee2e2;--orange:#ffedd5}
		.hotel-page{display:flex;flex-direction:column;gap:14px;padding-bottom:18px}
		.hotel-topbar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;border:1px solid var(--border);background:linear-gradient(135deg,#fff,#f7f9ff);border-radius:16px;padding:14px 16px;box-shadow:0 8px 24px rgba(15,23,42,.05)}
		.hotel-topbar h4{margin:0;font-weight:800;color:#24324a;display:flex;align-items:center;gap:8px}
		.hotel-topbar .text-muted{font-size:12px}
		.hotel-filters{border:1px solid var(--border);background:#fff;border-radius:16px;padding:12px;box-shadow:0 8px 22px rgba(15,23,42,.035)}
		.hotel-filter-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}
		.hotel-filter-field label{display:block;margin:0 0 4px 2px;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:var(--muted)}
		.hotel-filter-field .form-select,.hotel-filter-field .form-control{height:36px;border-radius:10px;border-color:#dbe3ee;font-size:12px;box-shadow:none}
		.hotel-planner{border:1px solid var(--border);background:#fff;border-radius:18px;box-shadow:0 12px 30px rgba(15,23,42,.045);overflow:hidden}
		.hotel-planner-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;padding:12px 14px;border-bottom:1px solid var(--border);background:#fbfcff}
		.hotel-planner-head h5{margin:0;font-weight:800;color:#24324a}
		.hotel-planner-head .small{color:var(--muted)}
		.hotel-legend{display:flex;flex-wrap:wrap;gap:6px;align-items:center}
		.hotel-legend .badge{border-radius:999px;padding:6px 9px;font-weight:700}
		.hotel-planner-body{padding:12px;background:#fff}
		.hotel-calendar-wrap{width:1045px;max-width:100%;margin:0 auto;border:1px solid #dfe5ec;border-radius:14px;overflow:hidden;background:#fff;box-shadow:0 10px 24px rgba(15,23,42,.04)}
		.hotel-calendar-board{overflow:auto;width:100%;height:430px;min-height:430px;max-height:430px;background:#fff}
		.hotel-calendar-board::-webkit-scrollbar{height:10px;width:10px}
		.hotel-calendar-board::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:999px;border:2px solid #f8fafc}
		.hotel-calendar-board::-webkit-scrollbar-track{background:#f8fafc}
		.hotel-calendar-table{width:max-content;min-width:max-content;margin:0;border-collapse:separate;border-spacing:0;table-layout:fixed;background:#fff}
		.hotel-calendar-table th{position:sticky;top:0;z-index:4;background:#f8fafc;border-bottom:1px solid #dfe5ec;border-left:1px solid #e4e9ef;width:86px;min-width:86px;max-width:86px;height:50px;padding:6px 4px;text-align:center;font-size:11px;font-weight:800;color:#111827;vertical-align:middle}
		.hotel-calendar-table td{width:86px;min-width:86px;max-width:86px;height:88px;padding:0;border-bottom:1px solid #e4e9ef;border-left:1px solid #e4e9ef;background:#fff;vertical-align:top;text-align:left}
		.hotel-calendar-table tr:nth-child(even) td:not(.hotel-calendar-room){background:#fcfdff}
		.hotel-calendar-room{position:sticky;left:0;z-index:7;width:185px!important;min-width:185px!important;max-width:185px!important;background:#fff!important;border-left:0!important;border-right:1px solid #dfe5ec!important;box-shadow:5px 0 12px rgba(15,23,42,.055);padding:12px!important;color:var(--text);vertical-align:middle!important;text-align:left!important;font-weight:800}
		.hotel-calendar-table thead .hotel-calendar-room{top:0;z-index:10;background:#f8fafc!important}
		.hotel-room-title{font-size:13px;font-weight:900;color:#182235;text-transform:uppercase}
		.hotel-calendar-room small{display:block;margin-top:3px;color:#64748b;font-size:11px;font-weight:600;line-height:1.25}
		.hotel-day{height:100%;min-height:88px;position:relative;cursor:pointer;overflow:hidden;display:flex;align-items:flex-start;justify-content:flex-start;transition:.12s ease;background:transparent}
		.hotel-day:hover{background:#f1f5ff;box-shadow:inset 0 0 0 1px rgba(64,81,137,.18)}
		.hotel-day.libre:before{content:'';position:absolute;left:50%;top:50%;transform:translate(-50%,-50%);width:7px;height:7px;border-radius:50%;background:#22c55e;box-shadow:0 0 0 4px rgba(34,197,94,.11)}
		.hotel-day.selected{outline:2px solid var(--p);outline-offset:-2px}
		.hotel-day-event{width:calc(100% - 10px);min-height:44px;margin:7px 5px 0;border-radius:8px;padding:7px 8px;font-size:10px;line-height:1.2;font-weight:900;white-space:normal;overflow:hidden;box-shadow:0 5px 12px rgba(15,23,42,.06)}
		.hotel-day-event span{display:block;margin-top:4px;font-size:10px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;opacity:.95}
		.hotel-day-free-label{display:none}
		.hotel-day.reserva .hotel-day-event{background:var(--blue);color:#1d4ed8;border-top:6px solid #93c5fd}
		.hotel-day.pendiente .hotel-day-event{background:var(--yellow);color:#92400e;border-top:6px solid #fde68a}
		.hotel-day.estadia .hotel-day-event{background:var(--green);color:#047857;border-top:6px solid #99f6e4}
		.hotel-day.mantenimiento .hotel-day-event{background:var(--red);color:#991b1b;border-top:6px solid #fecaca}
		.hotel-day.limpieza .hotel-day-event{background:var(--orange);color:#9a3412;border-top:6px solid #fed7aa}
		.hotel-modal{position:fixed;inset:0;background:rgba(15,23,42,.45);z-index:1050;display:flex;align-items:flex-start;justify-content:center;padding:32px 12px;overflow:auto;backdrop-filter:blur(2px)}
		.hotel-dialog{width:min(820px,100%);background:#fff;border-radius:16px;box-shadow:0 24px 75px rgba(15,23,42,.28);overflow:hidden}
		.hotel-dialog-head{display:flex;align-items:center;justify-content:space-between;padding:16px 18px;border-bottom:1px solid #e9ebf0;background:#fbfcfe}
		.hotel-dialog-body{padding:16px;max-height:calc(100vh - 190px);overflow:auto}
		.hotel-dialog-actions{display:flex;gap:8px;justify-content:flex-end;padding:14px 18px;border-top:1px solid #e9ebf0;background:#fbfcfe;flex-wrap:wrap}
		.hotel-reserva-form .form-label{font-size:12px;font-weight:700;color:#495057;margin-bottom:.25rem}
		.hotel-reserva-form textarea{min-height:82px}
		.hotel-form-box{border:1px solid #eef1f5;border-radius:12px;padding:.9rem;background:#fbfcfe;margin-bottom:.9rem}
		@media(max-width:991px){.hotel-filter-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.hotel-calendar-wrap{width:100%}}
		@media(max-width:575px){.hotel-filter-grid{grid-template-columns:1fr}.hotel-calendar-board{height:430px;min-height:430px;max-height:430px}}
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
		</div>

		<div class="hotel-planner">
			<div class="hotel-planner-head">
				<div>
					<h5>Calendario mensual por habitacion</h5>
					<div class="small">Scroll horizontal para mas dias y vertical para mas habitaciones. Clic en libre para reservar.</div>
				</div>
				<div class="hotel-legend">
					<span class="badge bg-light text-dark"><i class="ri-checkbox-blank-circle-fill text-success me-1"></i>Libre {{resumen_estado.libre}}</span>
					<span class="badge bg-warning-subtle text-warning">Pendiente {{resumen_estado.pendiente}}</span>
					<span class="badge bg-info-subtle text-info">Confirmada {{resumen_estado.confirmada}}</span>
					<span class="badge bg-success-subtle text-success">Ocupada {{resumen_estado.ocupada}}</span>
					<span class="badge bg-danger-subtle text-danger">Mantenimiento {{resumen_estado.mantenimiento}}</span>
					<span class="badge bg-warning-subtle text-warning">Limpieza {{resumen_estado.limpieza}}</span>
				</div>
			</div>
			<div class="hotel-planner-body">
				<div class="hotel-calendar-wrap">
					<div class="hotel-calendar-board">
						<table class="table hotel-calendar-table">
							<thead>
								<tr>
									<th class="hotel-calendar-room text-start">Habitacion</th>
									<th v-for="d in cal.dias">
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
									<td v-for="dia in fila.dias">
										<div class="hotel-day" v-bind:class="[clase_dia(dia), es_seleccionado(fila.habitacion.codhabitacion,dia.fecha) ? 'selected' : '']"
											v-on:click="click_dia(fila.habitacion,dia)"
											v-bind:title="titulo_dia(dia)">
											<div v-if="dia.evento" class="hotel-day-event">
												{{dia.evento.estado || dia.evento.descripcion || 'Reserva'}}
												<span v-if="dia.evento.tipo=='reserva' || dia.evento.tipo=='estadia'">{{dia.evento.cliente || 'Sin cliente'}}</span>
												<span v-if="dia.evento.tipo!='reserva' && dia.evento.tipo!='estadia'">{{dia.evento.descripcion || dia.evento.estado}}</span>
											</div>
											<div v-if="!dia.evento" class="hotel-day-free-label">Libre</div>
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

	<div class="hotel-modal" v-if="modalReserva">
		<div class="hotel-dialog">
			<div class="hotel-dialog-head">
				<h5 class="mb-0">{{form.codreserva ? 'Editar reserva' : 'Nueva reserva'}}</h5>
				<button class="btn btn-sm btn-light" v-on:click="modalReserva=false"><i class="ri-close-line"></i></button>
			</div>
			<div class="hotel-dialog-body hotel-reserva-form">
				<div class="hotel-form-box">
					<label class="form-label">Cliente</label>
					<input class="form-control mb-2" v-model="buscarCliente" v-on:keyup="buscar_clientes()" placeholder="Documento o nombre">
					<div class="list-group mb-2" v-if="clientes.length">
						<button type="button" class="list-group-item list-group-item-action" v-for="cliente in clientes" v-on:click="seleccionar_cliente(cliente)">
							<strong>{{cliente.razonsocial}}</strong><br><span class="small text-muted">{{cliente.documento}}</span>
						</button>
					</div>
					<div class="alert alert-info py-2 mb-0" v-if="form.codpersona">{{form.documento}} - {{form.cliente}}</div>
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
			<div class="hotel-dialog-actions">
				<button class="btn btn-light" v-on:click="modalReserva=false">Cancelar</button>
				<button class="btn btn-primary" v-on:click="guardar_reserva()"><i class="ri-save-line me-1"></i>Guardar reserva</button>
			</div>
		</div>
	</div>

	<div class="hotel-modal" v-if="modalDetalle">
		<div class="hotel-dialog">
			<div class="hotel-dialog-head"><h5 class="mb-0">Detalle de reserva</h5><button class="btn btn-sm btn-light" v-on:click="modalDetalle=false"><i class="ri-close-line"></i></button></div>
			<div class="hotel-dialog-body" v-if="detalle">
				<div class="row g-3">
					<div class="col-md-6"><div class="text-muted">Cliente / responsable</div><strong>{{detalle.cliente || detalle.descripcion || 'Sin cliente registrado'}}</strong></div>
					<div class="col-md-3"><div class="text-muted">Estado</div><span class="badge" v-bind:class="clase_estado(detalle.situacion)">{{situacion_texto(detalle.situacion)}}</span></div>
					<div class="col-md-3"><div class="text-muted">Habitacion</div><strong>{{detalle.numero}}</strong></div>
					<div class="col-md-6"><div class="text-muted">Rango</div>{{detalle.fechallegada}} / {{detalle.fechasalida}}</div>
					<div class="col-md-6"><div class="text-muted">Ambiente</div>{{detalle.ambiente}}</div>
					<div class="col-12"><div class="text-muted">Observacion</div>{{detalle.observacion}}</div>
				</div>
			</div>
			<div class="hotel-dialog-actions">
				<button class="btn btn-light" v-on:click="modalDetalle=false">Cerrar</button>
				<button class="btn btn-primary" v-if="detalle && (detalle.situacion==1 || detalle.situacion==2)" v-on:click="editar_reserva(detalle)"><i class="ri-edit-line me-1"></i>Editar reserva</button>
				<button class="btn btn-info" v-if="detalle && detalle.situacion==1" v-on:click="confirmar_reserva(detalle)"><i class="ri-check-line me-1"></i>Confirmar</button>
				<button class="btn btn-success" v-if="detalle && detalle.situacion==2" v-on:click="abrir_checkin(detalle)"><i class="ri-login-circle-line me-1"></i>Check-in</button>
				<button class="btn btn-danger" v-if="detalle && (detalle.situacion==1 || detalle.situacion==2)" v-on:click="anular_reserva(detalle)">Anular</button>
			</div>
		</div>
	</div>

	<div class="hotel-modal" v-if="modalCheckin">
		<div class="hotel-dialog">
			<div class="hotel-dialog-head"><h5 class="mb-0">Realizar check-in</h5><button class="btn btn-sm btn-light" v-on:click="modalCheckin=false"><i class="ri-close-line"></i></button></div>
			<div class="hotel-dialog-body hotel-reserva-form"><div class="row g-3"><div class="col-md-4"><label class="form-label">Fecha check-in</label><input type="date" class="form-control" v-model="checkin.fecha_checkin"></div><div class="col-md-8"><label class="form-label">Observacion</label><input class="form-control" v-model="checkin.observacion"></div></div></div>
			<div class="hotel-dialog-actions"><button class="btn btn-light" v-on:click="modalCheckin=false">Cancelar</button><button class="btn btn-success" v-on:click="realizar_checkin()">Iniciar hospedaje</button></div>
		</div>
	</div>
</div>
<script>window.phuyu_hotel_codsucursal = <?php echo (int)$_SESSION["phuyu_codsucursal"]; ?>;</script>
<script src="<?php echo base_url();?>phuyu/phuyu_hotel/reservas.js"></script>
