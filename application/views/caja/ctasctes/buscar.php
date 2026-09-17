<div id="phuyu_buscar" class="phuyu-velzon-list">
	<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

	<div class="card phuyu-card">
		<div class="card-body">
			<div class="row g-2 align-items-end mb-3">
				<div class="col-12 col-md-4">
					<label class="form-label">Banco</label>
					<select class="form-select" v-model="bancos" v-on:change="phuyu_buscar()">
						<option value="0">TODOS LOS BANCOS</option>
						<?php foreach ($bancos as $key => $value) { ?>
							<option value="<?php echo $value["codbanco"];?>"><?php echo $value["banco"];?></option>
						<?php } ?>
					</select>
				</div>

				<div class="col-12 col-md-6">
					<label class="form-label">Buscar cuenta corriente</label>
					<input type="text" class="form-control" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="Buscar cuenta corriente" v-bind:autofocus="true">
				</div>

				<div class="col-12 col-md-2">
					<button type="button" class="btn btn-primary w-100" v-on:click="phuyu_nuevoccte()">
						<i class="bi bi-plus-circle"></i>
					</button>
				</div>
			</div>

			<div class="phuyu_cargando" v-if="cargando">
				<div class="overlay-spinner"></div>
			</div>

			<div class="phuyu-table-wrap" v-if="!cargando">
				<table class="table table-hover align-middle">
					<thead>
						<tr>
							<th>Banco / caja</th>
							<th>Moneda</th>
							<th>Nro cuenta</th>
							<th>Codigo interb. (CCI)</th>
							<th width="70" class="text-center">Sel.</th>
						</tr>
					</thead>
					<tbody>
						<tr v-for="dato in datos">
							<td>{{dato.banco}}</td>
							<td>
								<span v-if="dato.codmoneda==1">SOLES</span>
								<span v-else="dato.codmoneda==2">DOLARES</span>
							</td>
							<td>{{dato.nroctacte}}</td>
							<td>{{dato.descripcion}}</td>
							<td class="text-center">
								<button type="button" class="btn btn-sm btn-success" v-on:click="phuyu_seleccionado(dato)">
									<i class="bi bi-check-lg"></i>
								</button>
							</td>
						</tr>
						<tr v-if="datos.length==0">
							<td colspan="5" class="text-center text-muted py-4">
								<i class="bi bi-inbox me-1"></i> Sin cuentas corrientes
							</td>
						</tr>
					</tbody>
				</table>
			</div>

			<?php include("application/views/phuyu/phuyu_paginacion.php");?>
		</div>
	</div>
</div>

<script>
	var phuyu_buscar = new Vue({
		el: "#phuyu_buscar",
		data: {
			cargando: true, buscar: "",bancos:0, rubro:"<?php echo $_SESSION['phuyu_rubro'];?>", verprecios:1, putunidades:[],
			productos:[], unidades:[], productoprecio:{},almacenes:[],
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
			phuyu_nuevoccte : function(){
				$("#phuyu_tituloform").text("AGREGAR NUEVA CUENTA CORRIENTE");phuyu_sistema.phuyu_loader("cuerpo",180);
				this.$http.post(url+"caja/ctasctes/nuevo").then(function(data){
					$("#cuerpo").empty().html(data.body);
				},function(){
					phuyu_sistema.phuyu_error();
				});
			},
			phuyu_ccte: function(){
				this.cargando = true;
				if(phuyu_controller=="creditos/cuentascobrar"){
					var id = 1
				}else{
					var id = phuyu_creditos.registro
				}
				this.$http.post(url+"caja/ctasctes/buscarccte",{"buscar":this.buscar,"bancos":this.bancos,"codregistro":id,"pagina":this.paginacion.actual}).then(function(data){
					this.datos = data.body.lista; this.paginacion = data.body.paginacion; this.cargando = false;
				},function(){
					phuyu_sistema.phuyu_error(); this.cargando = false;
				});
			},
			phuyu_buscar: function(){
				this.paginacion.actual = 1; this.phuyu_ccte();
			},
			phuyu_paginacion: function(pagina){
				this.paginacion.actual = pagina; this.phuyu_ccte();
			},
			phuyu_seleccionado: function(ccte){
				phuyu_cobranza.phuyu_addccte(ccte);
			},
			phuyu_seleccionado_1: function(precio){
				phuyu_operacion.phuyu_additem(this.productoprecio,precio); $("#modal_precios").modal("hide");
			},
			phuyu_cerrar: function(){
				$(".compose").slideToggle();
			}
		},
		created: function(){
			this.phuyu_ccte();
		}
	});
</script>

<script> $(".datepicker").datetimepicker({format: 'YYYY-MM-DD',ignoreReadonly: true}).attr("readonly","true"); </script>
