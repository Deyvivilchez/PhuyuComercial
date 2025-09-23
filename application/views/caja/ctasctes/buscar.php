<div id="phuyu_buscar">
	<div class="row">
		<div class="col-md-4">
			<select class="form-control" v-model="bancos" v-on:change="phuyu_buscar()">
				<option value="0">TODOS LOS BANCOS</option>
	    		<?php 
	    			foreach ($bancos as $key => $value) { ?>
						<option value="<?php echo $value["codbanco"];?>">
	    					<?php echo $value["banco"];?>
	    				</option>
	    		<?php
	    			}
	    		?>
    		</select>
		</div>	
		<div class="col-md-6 col-xs-10">
			<input type="text" class="form-control" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="BUSCAR CUENTA CORRIENTE . . ." v-bind:autofocus="true">
		</div>
		<div class="col-md-2 col-xs-2">
			<button type="button" class="btn btn-block btn-success" v-on:click="phuyu_nuevoccte()">
				<i class="fa fa-shopping-cart"></i> <i class="fa fa-plus-circle"></i>
			</button>
		</div>
	</div>

	<div class="col-xs-12">
		<div class="phuyu_cargando" v-if="cargando">
			<img src="<?php echo base_url();?>public/img/phuyu_loading.gif"> <h5>CARGANDO DATOS</h5>
		</div>
		<div class="row" v-if="!cargando">
			<div class="table-responsive">
				<table class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th>BANCO / CAJA</th>
							<th>MONEDA</th>
							<th>NRO CUENTA</th>
							<th>CODIGO INTERB. (CCI)</th>
							<th></th>
						</tr>
					</thead>
					<tbody style="font-size: 11px">
						<tr v-for="dato in datos">
							<td>{{dato.banco}}</td>
							<td>
								<span v-if="dato.codmoneda==1">SOLES</span>
								<span v-else="dato.codmoneda==2">DOLARES</span>
							</td>
							<td>{{dato.nroctacte}}</td>
							<td>{{dato.descripcion}}</td>
							<td><button type="button" class="btn btn-xs btn-success" v-on:click="phuyu_seleccionado(dato)"><i class="fa fa-check"></i></button></td>
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