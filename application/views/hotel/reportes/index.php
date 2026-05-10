<div id="phuyu_hotel_reportes">
	<div class="page-title-box">
		<h4><i class="ri-bar-chart-box-line me-1"></i> Reportes hotel</h4>
	</div>
	<div class="card">
		<div class="card-body">
			<div class="row g-2 align-items-end mb-3">
				<div class="col-md-2"><label class="form-label">Desde</label><input type="date" class="form-control" v-model="filtro.desde"></div>
				<div class="col-md-2"><label class="form-label">Hasta</label><input type="date" class="form-control" v-model="filtro.hasta"></div>
				<div class="col-md-3">
					<label class="form-label">Habitacion</label>
					<select class="form-select" v-model="filtro.codhabitacion">
						<option value="0">TODAS</option>
						<?php foreach ($habitaciones as $habitacion) { ?>
							<option value="<?php echo $habitacion["codhabitacion"];?>"><?php echo $habitacion["ambiente"]." / ".$habitacion["numero"]." - ".$habitacion["tipo"];?></option>
						<?php } ?>
					</select>
				</div>
				<div class="col-md-3"><label class="form-label">Cliente</label><input class="form-control" v-model="filtro.cliente" placeholder="Documento o nombre"></div>
				<div class="col-md-2"><button class="btn btn-primary w-100" v-on:click="cargar()">Consultar</button></div>
			</div>
			<div class="row g-3">
				<div class="col-md-3"><div class="card bg-light mb-0"><div class="card-body"><div class="text-muted">Estadias</div><h3>{{resumen.ocupacion.estadias || 0}}</h3></div></div></div>
				<div class="col-md-3"><div class="card bg-light mb-0"><div class="card-body"><div class="text-muted">Noches</div><h3>{{resumen.ocupacion.noches || 0}}</h3></div></div></div>
				<div class="col-md-3"><div class="card bg-light mb-0"><div class="card-body"><div class="text-muted">Alojamiento + consumos</div><h3>S/. {{Number(resumen.ocupacion.ingresos || 0).toFixed(2)}}</h3></div></div></div>
				<div class="col-md-3"><div class="card bg-light mb-0"><div class="card-body"><div class="text-muted">Consumos</div><h3>S/. {{Number(resumen.consumos.consumos || 0).toFixed(2)}}</h3></div></div></div>
			</div>

			<ul class="nav nav-tabs mt-4">
				<li class="nav-item" v-for="tab in tabs"><button class="nav-link" v-bind:class="{active:activo==tab.id}" v-on:click="activo=tab.id">{{tab.texto}}</button></li>
			</ul>

			<div class="table-responsive mt-3" v-if="activo=='diaria'">
				<table class="table table-sm table-hover"><thead><tr><th>Fecha</th><th>Estadias</th><th>Noches</th><th>Alojamiento</th><th>Consumos</th><th>Total</th></tr></thead>
					<tbody><tr v-for="r in resumen.diaria"><td>{{r.fecha}}</td><td>{{r.estadias}}</td><td>{{r.noches}}</td><td>S/. {{Number(r.alojamiento).toFixed(2)}}</td><td>S/. {{Number(r.consumos).toFixed(2)}}</td><td>S/. {{Number(r.importe).toFixed(2)}}</td></tr></tbody>
				</table>
			</div>
			<div class="table-responsive mt-3" v-if="activo=='mensual'">
				<table class="table table-sm table-hover"><thead><tr><th>Mes</th><th>Estadias</th><th>Noches</th><th>Total</th></tr></thead>
					<tbody><tr v-for="r in resumen.mensual"><td>{{r.periodo}}</td><td>{{r.estadias}}</td><td>{{r.noches}}</td><td>S/. {{Number(r.importe).toFixed(2)}}</td></tr></tbody>
				</table>
			</div>
			<div class="table-responsive mt-3" v-if="activo=='ingresos'">
				<table class="table table-sm table-hover"><thead><tr><th>Concepto</th><th>Importe</th></tr></thead>
					<tbody>
						<tr><td>Alojamiento</td><td>S/. {{Number((resumen.ocupacion.ingresos || 0) - (resumen.consumos.consumos || 0)).toFixed(2)}}</td></tr>
						<tr><td>Consumos</td><td>S/. {{Number(resumen.consumos.consumos || 0).toFixed(2)}}</td></tr>
						<tr><td>Total</td><td>S/. {{Number(resumen.ocupacion.ingresos || 0).toFixed(2)}}</td></tr>
					</tbody>
				</table>
			</div>
			<div class="table-responsive mt-3" v-if="activo=='habitaciones'">
				<table class="table table-sm table-hover"><thead><tr><th>Habitacion</th><th>Tipo</th><th>Usos</th><th>Noches</th><th>Importe</th></tr></thead>
					<tbody><tr v-for="r in resumen.habitaciones"><td>{{r.numero}}</td><td>{{r.tipo}}</td><td>{{r.usos}}</td><td>{{r.noches}}</td><td>S/. {{Number(r.importe).toFixed(2)}}</td></tr></tbody>
				</table>
			</div>
			<div class="table-responsive mt-3" v-if="activo=='libres'">
				<table class="table table-sm table-hover"><thead><tr><th>Habitacion</th><th>Ambiente</th><th>Tipo</th><th>Precio base</th><th>Estado</th></tr></thead>
					<tbody><tr v-for="r in resumen.habitaciones_libres"><td>{{r.numero}}</td><td>{{r.ambiente}}</td><td>{{r.tipo}}</td><td>S/. {{Number(r.preciobase || 0).toFixed(2)}}</td><td><span class="badge bg-success-subtle text-success">LIBRE</span></td></tr></tbody>
				</table>
			</div>
			<div class="table-responsive mt-3" v-if="activo=='reservas_pendientes'">
				<table class="table table-sm table-hover"><thead><tr><th>Reserva</th><th>Cliente</th><th>Documento</th><th>Habitacion</th><th>Llegada</th><th>Salida</th><th>Estado</th></tr></thead>
					<tbody><tr v-for="r in resumen.reservas_pendientes"><td>000{{r.codreserva}}</td><td>{{r.cliente}}</td><td>{{r.documento}}</td><td>{{r.habitaciones}}</td><td>{{r.fechallegada}}</td><td>{{r.fechasalida}}</td><td><span class="badge bg-warning-subtle text-warning">{{r.situacion_texto}}</span></td></tr></tbody>
				</table>
			</div>
			<div class="table-responsive mt-3" v-if="activo=='reservas_confirmadas'">
				<table class="table table-sm table-hover"><thead><tr><th>Reserva</th><th>Cliente</th><th>Documento</th><th>Habitacion</th><th>Llegada</th><th>Salida</th><th>Estado</th></tr></thead>
					<tbody><tr v-for="r in resumen.reservas_confirmadas"><td>000{{r.codreserva}}</td><td>{{r.cliente}}</td><td>{{r.documento}}</td><td>{{r.habitaciones}}</td><td>{{r.fechallegada}}</td><td>{{r.fechasalida}}</td><td><span class="badge bg-info-subtle text-info">{{r.situacion_texto}}</span></td></tr></tbody>
				</table>
			</div>
			<div class="table-responsive mt-3" v-if="activo=='consumos'">
				<table class="table table-sm table-hover"><thead><tr><th>Habitacion</th><th>Producto</th><th>Unidad</th><th>Cantidad</th><th>Total</th></tr></thead>
					<tbody><tr v-for="r in resumen.consumos_habitacion"><td>{{r.numero}}</td><td>{{r.producto}}</td><td>{{r.unidad}}</td><td>{{Number(r.cantidad).toFixed(2)}}</td><td>S/. {{Number(r.subtotal).toFixed(2)}}</td></tr></tbody>
				</table>
			</div>
			<div class="table-responsive mt-3" v-if="activo=='clientes'">
				<table class="table table-sm table-hover"><thead><tr><th>Documento</th><th>Cliente</th><th>Visitas</th><th>Importe</th></tr></thead>
					<tbody><tr v-for="c in resumen.clientes"><td>{{c.documento}}</td><td>{{c.razonsocial}}</td><td>{{c.visitas}}</td><td>S/. {{Number(c.importe).toFixed(2)}}</td></tr></tbody>
				</table>
			</div>
			<div class="table-responsive mt-3" v-if="activo=='historial'">
				<table class="table table-sm table-hover"><thead><tr><th>Estadia</th><th>Cliente</th><th>Habitaciones</th><th>Check-in</th><th>Check-out</th><th>Estado</th><th>Total</th></tr></thead>
					<tbody><tr v-for="r in resumen.historial"><td>000{{r.codestadia}}</td><td>{{r.cliente}}</td><td>{{r.habitaciones}}</td><td>{{r.fecha_checkin}}</td><td>{{r.fecha_checkout}}</td><td>{{r.situacion_texto}}</td><td>S/. {{Number(r.importe).toFixed(2)}}</td></tr></tbody>
				</table>
			</div>
			<div class="table-responsive mt-3" v-if="activo=='mantenimiento'">
				<table class="table table-sm table-hover"><thead><tr><th>Habitacion</th><th>Tipo hab.</th><th>Tipo mant.</th><th>Prioridad</th><th>Inicio</th><th>Fin</th><th>Estado</th><th>Observacion</th></tr></thead>
					<tbody><tr v-for="r in resumen.mantenimiento"><td>{{r.numero}}</td><td>{{r.tipo}}</td><td>{{r.tipo_mantenimiento}}</td><td>{{r.prioridad}}</td><td>{{r.fecha_inicio}}</td><td>{{r.fecha_fin}}</td><td>{{r.situacion_texto}}</td><td>{{r.observacion}}</td></tr></tbody>
				</table>
			</div>
			<div class="table-responsive mt-3" v-if="activo=='limpieza'">
				<table class="table table-sm table-hover"><thead><tr><th>Habitacion</th><th>Tipo hab.</th><th>Tipo limpieza</th><th>Fecha</th><th>Hora</th><th>Estado</th><th>Observacion</th></tr></thead>
					<tbody><tr v-for="r in resumen.limpieza"><td>{{r.numero}}</td><td>{{r.tipo}}</td><td>{{r.tipo_limpieza}}</td><td>{{r.fecha}}</td><td>{{r.hora}}</td><td>{{r.situacion_texto}}</td><td>{{r.observacion}}</td></tr></tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<script>
new Vue({
	el:"#phuyu_hotel_reportes",
	data:{
		filtro:{desde:"",hasta:"",codhabitacion:0,cliente:""},
		activo:"diaria",
		tabs:[
			{id:"diaria",texto:"Ocupacion diaria"},
			{id:"mensual",texto:"Ocupacion mensual"},
			{id:"ingresos",texto:"Ingresos"},
			{id:"habitaciones",texto:"Habitaciones"},
			{id:"libres",texto:"Libres"},
			{id:"reservas_pendientes",texto:"Reservas pendientes"},
			{id:"reservas_confirmadas",texto:"Reservas confirmadas"},
			{id:"consumos",texto:"Consumos"},
			{id:"clientes",texto:"Clientes"},
			{id:"historial",texto:"Historial"},
			{id:"mantenimiento",texto:"Mantenimiento"},
			{id:"limpieza",texto:"Limpieza"}
		],
		resumen:{ocupacion:{},consumos:{},clientes:[],diaria:[],mensual:[],habitaciones:[],habitaciones_libres:[],reservas_pendientes:[],reservas_confirmadas:[],consumos_habitacion:[],historial:[],mantenimiento:[],limpieza:[]}
	},
	methods:{
		hoy:function(){var d=new Date();return d.getFullYear()+"-"+String(d.getMonth()+1).padStart(2,"0")+"-"+String(d.getDate()).padStart(2,"0");},
		cargar:function(){
			this.$http.post(url+"hotel/reportes/resumen",this.filtro).then(function(data){this.resumen=data.body;});
		}
	},
	created:function(){this.filtro.desde=this.hoy();this.filtro.hasta=this.hoy();this.cargar();}
});
</script>
