<div id="phuyu_hotel_mantenimiento">
	<div class="page-title-box">
		<h4><i class="ri-tools-line me-1"></i> Mantenimiento de habitaciones</h4>
	</div>
	<div class="card">
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-hover align-middle">
					<thead><tr><th>Habitacion</th><th>Tipo</th><th>Estado</th><th>Observacion</th><th>Finalizacion</th><th width="260">Accion</th></tr></thead>
					<tbody>
						<tr v-for="h in habitaciones">
							<td class="fw-semibold">{{h.numero}}</td>
							<td>{{h.tipo}}</td>
							<td><span class="badge bg-warning-subtle text-warning">{{h.situacion_texto}}</span></td>
							<td><input class="form-control form-control-sm" v-model="h.observacion" placeholder="Motivo"></td>
							<td><input class="form-control form-control-sm" v-model="h.observacion_final" placeholder="Observacion final"></td>
							<td>
								<button class="btn btn-sm btn-warning" v-if="h.situacion!=5 && h.situacion!=3" v-on:click="guardar(h)">Enviar a mantenimiento</button>
								<div class="btn-group btn-group-sm" v-if="h.situacion==5">
									<button class="btn btn-success" v-on:click="finalizar(h,1)">Disponible</button>
									<button class="btn btn-secondary" v-on:click="finalizar(h,4)">Limpieza</button>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<script>
new Vue({
	el:"#phuyu_hotel_mantenimiento",
	data:{habitaciones:[]},
	methods:{
		cargar:function(){this.$http.post(url+"hotel/mantenimiento/habitaciones").then(function(data){this.habitaciones=data.body;});},
		guardar:function(h){
			this.$http.post(url+"hotel/mantenimiento/guardar",{codhabitacion:h.codhabitacion,codresponsable:0,observacion:h.observacion || ""}).then(function(data){
				if(data.body.estado==1){phuyu_sistema.phuyu_noti("MANTENIMIENTO REGISTRADO","","success");this.cargar();}
				else{phuyu_sistema.phuyu_alerta(data.body.mensaje || "NO SE PUDO REGISTRAR","","error");}
			});
		},
		finalizar:function(h,situacion){
			this.$http.post(url+"hotel/mantenimiento/finalizar",{codhabitacion:h.codhabitacion,observacion_final:h.observacion_final || "",situacion:situacion}).then(function(data){
				if(data.body.estado==1){phuyu_sistema.phuyu_noti("MANTENIMIENTO FINALIZADO","","success");this.cargar();}
				else{phuyu_sistema.phuyu_alerta(data.body.mensaje || "NO SE PUDO FINALIZAR","","error");}
			});
		}
	},
	created:function(){this.cargar();}
});
</script>
