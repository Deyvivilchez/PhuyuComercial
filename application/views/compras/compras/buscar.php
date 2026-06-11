<style>
	#phuyu_buscar .phuyu-search-box {
		position: relative;
	}

	#phuyu_buscar .phuyu-search-box .form-control {
		min-height: 40px;
		border-radius: 10px;
		padding-left: 40px;
		font-weight: 600;
	}

	#phuyu_buscar .phuyu-search-box i {
		position: absolute;
		left: 14px;
		top: 50%;
		transform: translateY(-50%);
		color: #878a99;
	}

	#phuyu_buscar .phuyu-buy-row {
		cursor: pointer;
		transition: background-color .15s ease;
	}

	#phuyu_buscar .phuyu-buy-row:hover {
		background: #f8fafc;
	}

	#phuyu_buscar .phuyu-amount {
		font-size: 16px;
		font-weight: 800;
		color: #0ab39c;
	}
</style>

<div id="phuyu_buscar">
	<div class="border-bottom pb-3 mb-3">
		<div class="phuyu-search-box">
			<i class="bi bi-search"></i>
			<input type="text" class="form-control" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="Buscar compra..." v-bind:autofocus="true">
		</div>
	</div>

	<div class="phuyu_cargando text-center py-4" v-if="cargando">
		<div class="overlay-spinner"></div>
	</div>

	<div v-if="!cargando">
		<div class="table-responsive">
			<table class="table table-hover table-striped align-middle">
				<tbody>
					<tr class="phuyu-buy-row" v-for="dato in ventas" v-on:click="phuyu_seleccionado(dato)">
						<td style="width:65%;">
							<div class="fw-bold">{{dato.documento}} · {{dato.cliente}}</div>
							<div class="text-danger fw-semibold">{{dato.tipo}}: {{dato.seriecomprobante}}-{{dato.nrocomprobante}}</div>
							<small class="text-muted">
								<i class="bi bi-calendar3 me-1"></i>
								{{dato.fechacomprobante}} - {{dato.hora}}
							</small>
						</td>
						<td class="text-end" style="width:35%;">
							<span class="phuyu-amount">S/. {{dato.importe}}</span>
						</td>
					</tr>

					<tr v-if="ventas.length === 0">
						<td colspan="2" class="text-center text-muted py-4">
							<i class="bi bi-inbox d-block mb-1" style="font-size:28px;"></i>
							Sin compras encontradas
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	<div class="text-center">
		<ul class="pagination justify-content-center flex-wrap">
			<li class="page-item disabled" v-if="paginacion.actual <= 1">
				<a class="page-link">
					<i class="bi bi-chevron-left"></i>
					Atrás
				</a>
			</li>
			<li class="page-item" v-if="paginacion.actual > 1">
				<a class="page-link" href="#" v-on:click.prevent="phuyu_paginacion(paginacion.actual - 1)">
					<i class="bi bi-chevron-left"></i>
					Atrás
				</a>
			</li>

			<li class="page-item" v-for="pag in phuyu_paginas" v-bind:class="[pag==phuyu_actual ? 'active':'']">
				<a class="page-link" href="#" v-on:click.prevent="phuyu_paginacion(pag)">{{pag}}</a>
			</li>

			<li class="page-item" v-if="paginacion.actual < paginacion.ultima">
				<a class="page-link" href="#" v-on:click.prevent="phuyu_paginacion(paginacion.actual + 1)">
					Sigue
					<i class="bi bi-chevron-right"></i>
				</a>
			</li>
			<li class="page-item disabled" v-if="paginacion.actual >= paginacion.ultima">
				<a class="page-link">
					Sigue
					<i class="bi bi-chevron-right"></i>
				</a>
			</li>
		</ul>
	</div>
</div>

<script>
	var phuyu_buscar = new Vue({
		el: "#phuyu_buscar",
		data: {
			cargando: true, buscar: "", verprecios:1,
			ventas:[],
			paginacion: {"total":0, "actual":1, "ultima":0, "desde":0, "hasta":0}, offset: 3
		},
		computed: {
			phuyu_actual: function(){
				return this.paginacion.actual;
			},
			phuyu_paginas: function(){
				if (!this.paginacion.hasta) {
					return [];
				}
				var desde = this.paginacion.actual - this.offset;
				if (desde < 1) {
					desde = 1;
				}
				var hasta = desde + (this.offset * 2);
				if (hasta >= this.paginacion.ultima) {
					hasta = this.paginacion.ultima;
				}

				var paginas = [];
				while(desde <= hasta){
					paginas.push(desde); desde++;
				}
				return paginas;
			}
		},
		methods: {
			phuyu_ventas: function(){

				this.cargando = true;
				this.$http.post(url+"ventas/ventas/buscar_lista",{"tabla":"compra","buscar":this.buscar,"pagina":this.paginacion.actual}).then(function(data){
					this.ventas = data.body.lista; this.paginacion = data.body.paginacion; this.cargando = false;
				},function(){
					phuyu_sistema.phuyu_error(); this.cargando = false;
				});
			},
			phuyu_buscar: function(){
				this.paginacion.actual = 1; this.phuyu_ventas();
			},
			phuyu_paginacion: function(pagina){
				this.paginacion.actual = pagina; this.phuyu_ventas();
			},
			phuyu_seleccionado: function(venta){
				phuyu_operacion.phuyu_addventa(venta);
			},
			phuyu_cerrar: function(){
				$(".compose").slideToggle();
			}
		},
		created: function(){
			this.phuyu_ventas();
		}
	});
</script>
