<style type="text/css">
	.columna{
		background: #ccc;
		font-size: 12px !important; 
	}
</style>
<div id="phuyu_buscar">
	<div style="padding:10px 0px; height:53px; border-bottom: 2px solid #f3f3f3;">
		<div class="col-md-12 col-xs-10">
			<input type="text" class="form-control" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="BUSCAR PEDIDO . . ." v-bind:autofocus="true">
		</div>
	</div>

	<div class="col-xs-12">
		<div class="phuyu_cargando" v-if="cargando">
			<img src="<?php echo base_url();?>public/img/phuyu_loading.gif"> <h5>CARGANDO DATOS</h5>
		</div>
		<div class="row" v-if="!cargando">
			<table class="table table-striped projects">
				<tbody>
					<tr v-for="(dato,index) in pedidos">
						<td style="width:60%;cursor:pointer;" v-on:click="phuyu_seleccionado(index,dato)">
							<a><strong>{{dato.documento}}</strong>  {{dato.cliente}}</a> <br> 
							<b style="color:#13a89e">COMPROBANTE {{dato.seriecomprobante}}-{{dato.nrocomprobante}}</b>
							<span class="label label-warning">PENDIENTE</span> <br> 
							<small>FECHA: {{dato.fechapedido}} - {{dato.hora}}</small>
						</td>
						<td style="width:40%;" align="center">
							<b style="font-size:16px;">S/. {{dato.valorventa}}</b>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>

	<div class="col-md-12 col-xs-12" align="center">
		<ul class="pagination">
			<li class="page-item disabled" v-if="paginacion.actual <= 1">
		    	<a class="page-link"> <i class="fa fa-angle-left"></i> ATRAS </a> 
		    </li>
		    <li class="page-item" v-if="paginacion.actual > 1">
		    	<a class="page-link" href="#" v-on:click.prevent="phuyu_paginacion(paginacion.actual - 1)"> 
		    		<i class="fa fa-angle-left"></i> ATRAS 
		    	</a> 
		    </li>

		    <li class="page-item" v-for="pag in phuyu_paginas" v-bind:class="[pag==phuyu_actual ? 'active':'']">
		    	<a class="page-link" href="#" v-on:click.prevent="phuyu_paginacion(pag)">{{pag}}</a> 
		    </li>

		    <li class="page-item" v-if="paginacion.actual < paginacion.ultima">
		    	<a class="page-link" href="#" v-on:click.prevent="phuyu_paginacion(paginacion.actual + 1)"> 
		    		SIGUE <i class="fa fa-angle-right"></i> 
		    	</a> 
		    </li>
		    <li class="page-item disabled" v-if="paginacion.actual >= paginacion.ultima">
		    	<a class="page-link"> SIGUE <i class="fa fa-angle-right"></i> </a> 
		    </li>
		</ul>
	</div>
</div>

<script>
	var phuyu_buscar = new Vue({
		el: "#phuyu_buscar",
		data: {
			cargando: true, buscar: "", verprecios:1, 
			pedidos:[],
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
			phuyu_pedidos: function(){

				this.cargando = true;
				this.$http.post(url+"ventas/pedidos/buscar_lista",{"buscar":this.buscar,"pagina":this.paginacion.actual}).then(function(data){
					this.pedidos = data.body.lista; this.paginacion = data.body.paginacion; this.cargando = false;
				},function(){
					phuyu_sistema.phuyu_error(); this.cargando = false;
				});
			},
			phuyu_buscar: function(){
				this.paginacion.actual = 1; this.phuyu_pedidos();
			},
			phuyu_paginacion: function(pagina){
				this.paginacion.actual = pagina; this.phuyu_pedidos();
			},
			phuyu_seleccionado: function(index,pedido){
				index = index;
				$('.projects tr:eq('+index+') td').addClass("columna");
				phuyu_operacion.phuyu_addpedido(pedido);
				timeout = setTimeout(removerColumna, 100, index);
			},
			phuyu_cerrar: function(){
				$(".compose").slideToggle();
			}
		},
		created: function(){
			this.phuyu_pedidos();
		}
	});
</script>

<script> $(".datepicker").datetimepicker({format: 'YYYY-MM-DD',ignoreReadonly: true}).attr("readonly","true"); 
function removerColumna(index){
 	$('.projects tr:eq('+index+') td').removeClass("columna");
} 
</script>