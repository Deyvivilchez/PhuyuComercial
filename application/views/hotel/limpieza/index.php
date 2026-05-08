<div id="phuyu_hotel_limpieza">
	<div class="page-title-box">
		<h4><i class="ri-brush-3-line me-1"></i> Limpieza de habitaciones</h4>
	</div>
	<div class="card">
		<div class="card-body">
			<div class="table-responsive">
				<table class="table table-hover align-middle">
					<thead><tr><th>Habitacion</th><th>Tipo</th><th>Estado</th><th>Observacion</th><th width="220">Acciones</th></tr></thead>
					<tbody>
						<tr v-for="h in habitaciones">
							<td class="fw-semibold">{{h.numero}}</td>
							<td>{{h.tipo}}</td>
							<td><span class="badge bg-primary-subtle text-primary">{{h.situacion_texto}}</span></td>
							<td><input class="form-control form-control-sm" v-model="h.observacion" placeholder="Observacion"></td>
							<td>
								<button class="btn btn-sm btn-success" v-if="h.situacion==4" v-on:click="guardar(h,1)">Disponible</button>
								<button class="btn btn-sm btn-secondary" v-if="h.situacion!=3 && h.situacion!=5 && h.situacion!=6" v-on:click="guardar(h,4)">Limpieza</button>
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
	el:"#phuyu_hotel_limpieza",
	data:{habitaciones:[]},
	methods:{
		cargar:function(){this.$http.post(url+"hotel/limpieza/habitaciones").then(function(data){this.habitaciones=data.body;});},
		guardar:function(h,situacion){
			this.$http.post(url+"hotel/limpieza/guardar",{codhabitacion:h.codhabitacion,codresponsable:0,observacion:h.observacion || "",situacion:situacion}).then(function(data){
				if(data.body.estado==1){phuyu_sistema.phuyu_noti("REGISTRADO CORRECTAMENTE","","success");this.cargar();}
				else{phuyu_sistema.phuyu_alerta(data.body.mensaje || "NO SE PUDO REGISTRAR","","error");}
			});
		}
	},
	created:function(){this.cargar();}
});
</script>
