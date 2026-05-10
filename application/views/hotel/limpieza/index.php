<div id="phuyu_hotel_limpieza">
	<style>
		.hotel-order-modal{position:fixed!important;top:0!important;right:0!important;bottom:0!important;left:0!important;width:100vw!important;height:100vh!important;background:rgba(15,23,42,.52)!important;z-index:2147483000!important;display:flex!important;align-items:flex-start!important;justify-content:center!important;padding:32px 12px!important;overflow:auto!important}
		.hotel-order-dialog{position:relative;z-index:2147483001;width:min(860px,100%);max-height:calc(100vh - 64px);margin:0 auto 32px;background:#fff;border-radius:8px;box-shadow:0 18px 60px rgba(15,23,42,.22);display:flex;flex-direction:column}
		.hotel-order-head{display:flex;align-items:center;justify-content:space-between;padding:16px 18px;border-bottom:1px solid #e9ebf0}
		.hotel-order-body{padding:18px;overflow:auto}
		.hotel-order-actions{display:flex;gap:8px;justify-content:flex-end;padding:14px 18px;border-top:1px solid #e9ebf0}
		.hotel-checklist{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:8px 14px}
		.hotel-badge{font-size:11px;letter-spacing:.02em}
		@media(max-width:576px){.hotel-order-actions{flex-wrap:wrap}.hotel-order-actions .btn{flex:1 1 auto}}
	</style>
	<div class="page-title-box d-flex align-items-center justify-content-between">
		<h4><i class="ri-brush-3-line me-1"></i> Ordenes de limpieza</h4>
		<button class="btn btn-sm btn-primary" v-on:click="abrirNueva()"><i class="ri-add-line me-1"></i>Nueva orden</button>
	</div>

	<div class="card mb-3">
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
					<button class="btn btn-sm btn-secondary" v-on:click="cargarOrdenes()"><i class="ri-search-line me-1"></i>Filtrar</button>
				</div>
			</div>
		</div>
	</div>

	<div class="card">
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-hover align-middle">
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
							<td class="fw-semibold">OL-{{o.numero_orden}}</td>
							<td>{{o.fecha}} {{o.hora}}</td>
							<td><span class="fw-semibold">{{o.numero}}</span><br><span class="small text-muted">{{o.ambiente}}</span></td>
							<td>{{o.responsable || 'SIN RESPONSABLE'}}</td>
							<td>{{o.tipo_limpieza_texto}}</td>
							<td><span class="badge hotel-badge" v-bind:class="claseEstado(o.estado_orden)">{{o.estado_orden_texto}}</span></td>
							<td>
								<div class="btn-group btn-group-sm flex-wrap">
									<button class="btn btn-outline-primary" v-on:click="ver(o)"><i class="ri-eye-line"></i></button>
									<button class="btn btn-outline-secondary" v-if="o.estado_orden==1 || o.estado_orden==2" v-on:click="editar(o)"><i class="ri-edit-line"></i></button>
									<button class="btn btn-outline-dark" v-on:click="imprimir(o.codlimpieza)"><i class="ri-printer-line"></i></button>
									<button class="btn btn-outline-info" v-if="o.estado_orden==1" v-on:click="cambiarEstado(o,2)">En proceso</button>
									<button class="btn btn-outline-success" v-if="o.estado_orden==1 || o.estado_orden==2" v-on:click="finalizar(o)">Finalizar</button>
									<button class="btn btn-outline-danger" v-if="o.estado_orden==1 || o.estado_orden==2" v-on:click="cambiarEstado(o,4)">Anular</button>
								</div>
							</td>
						</tr>
						<tr v-if="ordenes.length==0"><td colspan="7" class="text-center text-muted py-4">No hay ordenes registradas</td></tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="hotel-order-modal" v-if="modal" style="position:fixed!important;top:0!important;right:0!important;bottom:0!important;left:0!important;width:100vw!important;height:100vh!important;z-index:2147483000!important;display:flex!important;align-items:flex-start!important;justify-content:center!important;padding:32px 12px!important;overflow:auto!important;background:rgba(15,23,42,.52)!important;">
		<div class="hotel-order-dialog" style="position:relative;z-index:2147483001;width:min(860px,100%);max-height:calc(100vh - 64px);margin:0 auto 32px;display:flex;flex-direction:column;">
			<div class="hotel-order-head">
				<h5 class="mb-0">{{tituloModal}}</h5>
				<button class="btn btn-sm btn-light" v-on:click="cerrar()"><i class="ri-close-line"></i></button>
			</div>
			<div class="hotel-order-body">
				<div class="row g-3">
					<div class="col-md-4">
						<label class="form-label">Habitacion</label>
						<select class="form-select" v-model="form.codhabitacion" v-bind:disabled="soloVer">
							<option value="0">Seleccione</option>
							<option v-for="h in habitacionesDisponibles" v-bind:value="h.codhabitacion">{{h.numero}} - {{h.ambiente}}</option>
						</select>
					</div>
					<div class="col-md-4">
						<label class="form-label">Responsable</label>
						<select class="form-select" v-model="form.codresponsable" v-bind:disabled="soloVer">
							<option value="0">Sin responsable</option>
							<option v-for="r in responsables" v-bind:value="r.codpersona">{{r.razonsocial}}</option>
						</select>
					</div>
					<div class="col-md-4">
						<label class="form-label">Tipo de limpieza</label>
						<select class="form-select" v-model="form.tipo_limpieza" v-bind:disabled="soloVer">
							<option value="normal">Limpieza normal</option>
							<option value="profunda">Limpieza profunda</option>
							<option value="post_checkout">Limpieza post check-out</option>
							<option value="incidencia">Limpieza por incidencia</option>
						</select>
					</div>
					<div class="col-12">
						<label class="form-label">Checklist</label>
						<div class="hotel-checklist">
							<label class="form-check" v-for="t in tareas">
								<input class="form-check-input" type="checkbox" v-bind:value="t" v-model="form.checklist" v-bind:disabled="soloVer">
								<span class="form-check-label">{{t}}</span>
							</label>
						</div>
					</div>
					<div class="col-12">
						<label class="form-label">Observacion</label>
						<textarea class="form-control" rows="3" v-model="form.observacion" v-bind:disabled="soloVer"></textarea>
					</div>
				</div>
			</div>
			<div class="hotel-order-actions">
				<button class="btn btn-light" v-on:click="cerrar()">Cerrar</button>
				<button class="btn btn-primary" v-if="!soloVer" v-on:click="guardar()">{{form.codlimpieza ? 'Actualizar orden' : 'Guardar orden'}}</button>
				<button class="btn btn-dark" v-if="soloVer" v-on:click="imprimir(form.codlimpieza)"><i class="ri-printer-line me-1"></i>Imprimir ticket</button>
			</div>
		</div>
	</div>
</div>
<script>
new Vue({
	el:"#phuyu_hotel_limpieza",
	data:{
		habitaciones:[],
		responsables:[],
		ordenes:[],
		modal:false,
		soloVer:false,
		filtros:{desde:"",hasta:"",codhabitacion:0,codresponsable:0,estado_orden:0},
		form:{codlimpieza:0,codhabitacion:0,codresponsable:0,tipo_limpieza:"normal",checklist:[],observacion:""},
		tareas:["Cambio de sabanas","Cambio de toallas","Limpieza de bano","Limpieza de ducha","Limpieza de ventanas","Barrido/trapeado de piso","Reposicion de papel higienico","Reposicion de jabon/shampoo","Revision de frigobar","Retiro de basura","Desinfeccion general"]
	},
	computed:{
		habitacionesDisponibles:function(){
			return this.habitaciones.filter(function(h){return h.situacion!=3 && h.situacion!=5 && h.situacion!=6;});
		},
		tituloModal:function(){
			if(this.soloVer){return "Detalle de orden";}
			return this.form.codlimpieza ? "Editar orden de limpieza" : "Nueva orden de limpieza";
		}
	},
	methods:{
		hoy:function(){var d=new Date();return d.getFullYear()+"-"+String(d.getMonth()+1).padStart(2,"0")+"-"+String(d.getDate()).padStart(2,"0");},
		cargarHabitaciones:function(){this.$http.post(url+"hotel/limpieza/habitaciones").then(function(data){this.habitaciones=data.body;});},
		cargarResponsables:function(){this.$http.post(url+"hotel/limpieza/responsables").then(function(data){this.responsables=data.body;});},
		cargarOrdenes:function(){this.$http.post(url+"hotel/limpieza/listar",this.filtros).then(function(data){this.ordenes=data.body;});},
		claseEstado:function(estado){
			estado=parseInt(estado);
			if(estado==1){return "bg-warning-subtle text-warning";}
			if(estado==2){return "bg-info-subtle text-info";}
			if(estado==3){return "bg-success-subtle text-success";}
			return "bg-danger-subtle text-danger";
		},
		abrirNueva:function(){
			this.soloVer=false;
			this.form={codlimpieza:0,codhabitacion:0,codresponsable:0,tipo_limpieza:"normal",checklist:this.tareas.slice(),observacion:""};
			this.modal=true;
		},
		ver:function(o){
			this.soloVer=true;
			this.form={codlimpieza:o.codlimpieza,codhabitacion:o.codhabitacion,codresponsable:o.codresponsable,tipo_limpieza:o.tipo_limpieza || "normal",checklist:o.checklist_items || [],observacion:o.observacion || ""};
			this.modal=true;
		},
		editar:function(o){
			this.soloVer=false;
			this.form={codlimpieza:o.codlimpieza,codhabitacion:o.codhabitacion,codresponsable:o.codresponsable,tipo_limpieza:o.tipo_limpieza || "normal",checklist:o.checklist_items || [],observacion:o.observacion || ""};
			this.modal=true;
		},
		cerrar:function(){this.modal=false;},
		guardar:function(){
			if(parseInt(this.form.codhabitacion)==0){phuyu_sistema.phuyu_alerta("SELECCIONE UNA HABITACION","","warning");return;}
			var esNuevo=parseInt(this.form.codlimpieza || 0)==0;
			this.$http.post(url+"hotel/limpieza/guardar",this.form).then(function(data){
				if(data.body.estado==1){
					phuyu_sistema.phuyu_noti(esNuevo ? "ORDEN DE LIMPIEZA REGISTRADA" : "ORDEN DE LIMPIEZA ACTUALIZADA","","success");
					this.cerrar();this.cargarHabitaciones();this.cargarOrdenes();
					if(esNuevo){this.imprimir(data.body.codlimpieza);}
				}else{phuyu_sistema.phuyu_alerta(data.body.mensaje || "NO SE PUDO REGISTRAR","","error");}
			});
		},
		cambiarEstado:function(o,estado){
			this.$http.post(url+"hotel/limpieza/cambiar_estado",{codlimpieza:o.codlimpieza,estado_orden:estado}).then(function(data){
				if(data.body.estado==1){phuyu_sistema.phuyu_noti("ORDEN ACTUALIZADA","","success");this.cargarOrdenes();}
				else{phuyu_sistema.phuyu_alerta(data.body.mensaje || "NO SE PUDO ACTUALIZAR","","error");}
			});
		},
		finalizar:function(o){
			this.$http.post(url+"hotel/limpieza/finalizar",{codlimpieza:o.codlimpieza}).then(function(data){
				if(data.body.estado==1){phuyu_sistema.phuyu_noti("LIMPIEZA FINALIZADA","","success");this.cargarHabitaciones();this.cargarOrdenes();}
				else{phuyu_sistema.phuyu_alerta(data.body.mensaje || "NO SE PUDO FINALIZAR","","error");}
			});
		},
		imprimir:function(codlimpieza){window.open(url+"hotel/limpieza/ticket/"+codlimpieza,"_blank");}
	},
	created:function(){
		this.filtros.desde=this.hoy();this.filtros.hasta=this.hoy();
		this.cargarHabitaciones();this.cargarResponsables();this.cargarOrdenes();
	}
});
</script>
