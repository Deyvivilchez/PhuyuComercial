<div id="phuyu_hotel_reservas">
	<style>
		.hotel-availability{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:.65rem}
		.hotel-availability-room{border:1px solid #e9ebec;border-radius:8px;background:#fff;padding:.75rem;cursor:pointer;min-height:104px}
		.hotel-availability-room.active{border-color:#405189;box-shadow:0 6px 16px rgba(64,81,137,.14)}
		.hotel-availability-room.available{border-left:4px solid #0ab39c}
		.hotel-availability-room.busy{border-left:4px solid #f06548}
		.hotel-calendar{display:grid;grid-template-columns:repeat(auto-fill,minmax(108px,1fr));gap:.5rem}
		.hotel-calendar-day{border:1px solid #e9ebec;border-radius:8px;padding:.6rem;background:#fff;min-height:74px}
		.hotel-calendar-day.free{border-left:4px solid #0ab39c}
		.hotel-calendar-day.busy{border-left:4px solid #f06548;background:#fff7f5}
	</style>
	<div class="page-title-box d-flex align-items-center justify-content-between">
		<div>
			<h4 class="mb-1"><i class="ri-calendar-check-line me-1"></i> Reservas hotel</h4>
			<div class="text-muted small">Disponibilidad por habitacion, reservas pendientes/confirmadas y estadias activas</div>
		</div>
	</div>

	<div class="row g-3">
		<div class="col-xl-5">
			<div class="card">
				<div class="card-header"><h5 class="card-title mb-0">Nueva reserva</h5></div>
				<div class="card-body">
					<label class="form-label">Cliente</label>
					<input class="form-control mb-2" v-model="buscarCliente" v-on:keyup="buscar_clientes()" placeholder="Documento o nombre">
					<div class="list-group mb-2" v-if="clientes.length">
						<button type="button" class="list-group-item list-group-item-action" v-for="cliente in clientes" v-on:click="seleccionar_cliente(cliente)">
							<strong>{{cliente.razonsocial}}</strong><br><span class="small text-muted">{{cliente.documento}}</span>
						</button>
					</div>
					<div class="alert alert-info py-2" v-if="form.codpersona">{{form.documento}} - {{form.cliente}}</div>

					<div class="row g-2">
						<div class="col-md-6">
							<label class="form-label">Llegada</label>
							<input type="date" class="form-control" v-model="form.fechallegada">
						</div>
						<div class="col-md-6">
							<label class="form-label">Salida</label>
							<input type="date" class="form-control" v-model="form.fechasalida">
						</div>
						<div class="col-md-12">
							<label class="form-label">Ambiente</label>
							<select class="form-select" v-model="form.codambiente" v-on:change="consultar_disponibilidad()">
								<option value="0">TODOS</option>
								<?php foreach ($ambientes as $ambiente) { ?>
									<option value="<?php echo $ambiente["codambiente"];?>"><?php echo $ambiente["descripcion"];?></option>
								<?php } ?>
							</select>
						</div>
						<div class="col-md-12">
							<label class="form-label">Habitacion</label>
							<select class="form-select" v-model="form.codhabitacion" v-on:change="validar_disponibilidad(false)">
								<option value="0">SELECCIONE</option>
								<option v-for="habitacion in habitaciones" v-bind:value="habitacion.codhabitacion">
									{{habitacion.ambiente}} / {{habitacion.numero}} - {{habitacion.tipo}} - {{habitacion.disponible==1 ? 'LIBRE' : 'OCUPADA'}}
								</option>
							</select>
						</div>
						<div class="col-md-12">
							<label class="form-label">Estado</label>
							<select class="form-select" v-model="form.situacion">
								<option value="1">PENDIENTE</option>
								<option value="2">CONFIRMADA</option>
							</select>
						</div>
						<div class="col-md-12">
							<label class="form-label">Observacion</label>
							<textarea class="form-control" v-model="form.observacion"></textarea>
						</div>
					</div>

					<div class="d-grid gap-2 mt-3">
						<button class="btn btn-light" v-on:click="validar_disponibilidad(true)"><i class="ri-search-eye-line me-1"></i> Validar disponibilidad</button>
						<button class="btn btn-primary" v-on:click="guardar_reserva()"><i class="ri-save-line me-1"></i> Guardar reserva</button>
					</div>
				</div>
			</div>
		</div>

		<div class="col-xl-7">
			<div class="card mb-3">
				<div class="card-header d-flex justify-content-between align-items-center">
					<h5 class="card-title mb-0">Disponibilidad del rango</h5>
					<button class="btn btn-sm btn-light" v-on:click="consultar_disponibilidad()"><i class="ri-refresh-line"></i></button>
				</div>
				<div class="card-body">
					<div class="hotel-availability mb-3">
						<div class="hotel-availability-room" v-for="habitacion in habitaciones"
							v-bind:class="[habitacion.disponible==1 ? 'available' : 'busy', form.codhabitacion==habitacion.codhabitacion ? 'active' : '']"
							v-on:click="seleccionar_habitacion(habitacion)">
							<div class="d-flex justify-content-between align-items-start">
								<div>
									<h5 class="mb-1">Hab. {{habitacion.numero}}</h5>
									<div class="text-muted small">{{habitacion.tipo}}</div>
									<div class="text-muted small">{{habitacion.ambiente}}</div>
								</div>
								<span class="badge" v-bind:class="habitacion.disponible==1 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'">
									{{habitacion.disponible==1 ? 'LIBRE' : 'OCUPADA'}}
								</span>
							</div>
							<div class="small mt-2" v-if="habitacion.eventos && habitacion.eventos.length">
								<div v-for="evento in habitacion.eventos">{{evento.descripcion}}: {{evento.desde}} / {{evento.hasta}}</div>
							</div>
						</div>
					</div>

					<div v-if="form.codhabitacion">
						<h6 class="mb-2">Calendario de habitacion {{habitacion_seleccionada_texto()}}</h6>
						<div class="hotel-calendar">
							<div class="hotel-calendar-day" v-for="dia in calendario" v-bind:class="dia.ocupado ? 'busy' : 'free'">
								<strong>{{dia.fecha}}</strong>
								<div class="small mt-1" v-if="dia.ocupado">{{dia.descripcion}}</div>
								<div class="small text-success mt-1" v-if="!dia.ocupado">Libre</div>
							</div>
						</div>
					</div>
					<div class="text-muted text-center py-3" v-if="!form.codhabitacion">Seleccione una habitacion para ver las fechas libres y ocupadas.</div>
				</div>
			</div>

			<div class="card">
				<div class="card-header d-flex justify-content-between align-items-center">
					<h5 class="card-title mb-0">Listado</h5>
					<button class="btn btn-sm btn-light" v-on:click="cargar_reservas()"><i class="ri-refresh-line"></i></button>
				</div>
				<div class="card-body">
					<input class="form-control mb-3" v-model="buscar" v-on:keyup="cargar_reservas()" placeholder="Buscar reserva">
					<div class="table-responsive">
						<table class="table table-hover align-middle mb-0">
							<thead>
								<tr>
									<th>ID</th>
									<th>Cliente</th>
									<th>Habitacion</th>
									<th>Rango</th>
									<th>Estado</th>
									<th></th>
								</tr>
							</thead>
							<tbody>
								<tr v-for="reserva in reservas">
									<td>{{reserva.codreserva}}</td>
									<td><strong>{{reserva.razonsocial}}</strong><br><span class="small text-muted">{{reserva.documento}}</span></td>
									<td>{{reserva.habitaciones}}</td>
									<td>{{reserva.fechallegada}} / {{reserva.fechasalida}}</td>
									<td><span class="badge bg-primary-subtle text-primary">{{situacion_texto(reserva.situacion)}}</span></td>
									<td><button class="btn btn-sm btn-danger" v-if="reserva.situacion==1 || reserva.situacion==2" v-on:click="cancelar_reserva(reserva)"><i class="ri-close-line"></i></button></td>
								</tr>
								<tr v-if="reservas.length==0">
									<td colspan="6" class="text-center text-muted py-4">Sin reservas registradas</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script src="<?php echo base_url();?>phuyu/phuyu_hotel/reservas.js"></script>
