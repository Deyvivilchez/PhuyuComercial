<div id="phuyu_buscar">
	<div style="padding:10px 0px; height:53px; border-bottom: 2px solid #f3f3f3;">
		<div class="col-md-12 col-xs-10">
			<input type="text" class="form-control" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="BUSCAR COMPRA . . ." v-bind:autofocus="true">
		</div>
	</div>

	<div class="col-xs-12">
		<div class="phuyu_cargando" v-if="cargando">
			<img src="<?php echo base_url();?>public/img/phuyu_loading.gif"> <h5>CARGANDO DATOS</h5>
		</div>
		<div class="row" v-if="!cargando">
			<table class="table table-striped projects">
				<tbody>
					<tr v-for="dato in ventas">
						<td style="width:60%;cursor:pointer;" v-on:click="phuyu_seleccionado(dato)">
							<a><strong>{{dato.documento}}</strong>  {{dato.cliente}}</a> <br> 
							<b style="color:#d92550">{{dato.tipo}}: {{dato.seriecomprobante}}-{{dato.nrocomprobante}}</b>
							<small>FECHA: {{dato.fechacomprobante}} - {{dato.hora}}</small>
						</td>
						<td style="width:40%;" align="center">
							<b style="font-size:16px;">S/. {{dato.importe}}</b>
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

<script> $(".datepicker").datetimepicker({format: 'YYYY-MM-DD',ignoreReadonly: true}).attr("readonly","true"); </script>