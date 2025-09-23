<style type="text/css">
	.columna{
		background: #ccc;
		font-size: 12px !important; 
	}
</style>
<div id="phuyu_buscar">
	<div class="row form-group" style="padding:10px 0px; height:53px; border-bottom: 2px solid #f3f3f3;">
		<div class="col-md-10">
			<input type="text" class="form-control" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="BUSCAR PRODUCTO . . ." v-bind:autofocus="true">
		</div>
		<div class="col-md-2">
			<button type="button" class="btn btn-icon btn-warning" v-on:click="phuyu_nuevoproducto()">
				<i data-acorn-icon="shipping"></i> <i class="fa fa-plus-circle"></i>
			</button>
		</div>
	</div>
	<div class="row form-group">
		<div class="col-xs-12">
			<div class="row form-group">
				<table class="table table-striped projects" style="font-size: 11px">
					<tbody>
						<tr v-for="(dato,index) in productos">
							<!--<td style="width:20%;cursor:pointer;" v-on:click="phuyu_seleccionado(dato)">
								<ul class="list-inline">
									<li> <img v-bind:src="`<?php echo base_url();?>public/img/productos/${dato.foto}`" style="height:40px;width:100%"> </li>
								</ul>
							</td>-->
							<td style="width:100%;cursor:pointer;padding-left:10px;padding-top: 10px ">
								<div class="row form-group" >
									<div class="col-md-9" v-on:click="phuyu_seleccionado(index,dato)">		
										<b>{{dato.descripcion}}</b> - 
										<b style="font-size:18px;" class="text-success" v-if="rubro==4">S/. {{dato.preciocosto}}</b>
										<b style="font-size:18px;" class="text-success" v-else="rubro!=4">S/. {{dato.precio}}</b> <br> 
										<b style="color:#13a89e" v-if="dato.stock>0">STOCK {{dato.stock}} {{dato.unidad}}</b>
										<b style="color:#d43f3a" v-if="dato.stock<=0">STOCK {{dato.stock}} {{dato.unidad}}</b> 
										<span> STOCK P: {{dato.stockproveedor}}</span> <br> 
										<small>MARCA: {{dato.marca}} CARACT. {{dato.caracteristicas}}</small>
									</div>
									<div class="col-md-3">
										<button type="button" v-if="verprecios==1" v-on:click="phuyu_masprecios(dato,index+1)" class="btn btn-success btn-xs"> <b>MAS PRECIOS</b> </button><br>
										<button type="button" v-on:click="phuyu_masstock(dato)" style="margin:5px" class="btn btn-success btn-xs"> <b>STOCKS</b> </button>
										<div v-if="rubro==2">
											<!--<button type="button" v-on:click="phuyu_salida(dato)" class="btn btn-danger btn-xs"> <b>DAR SALIDA</b> </button>-->
										</div>
									</div>
								</div>
								<template v-if="mostrarprecio==index+1">
									<table class="table table-bordered" style="font-size: 11px;">
										<thead>
											<th>PRECIO PUBLICO</th>
											<th>PRECIO MINIMO</th>
											<th>PRECIO X MAYOR</th>
											<th>PRECIO CREDITO</th>
										</thead>
										<tbody>
											<tr>
												<td>{{dato.precio}}</td>
												<td>{{masprecios.preciomin}}</td>
												<td>{{masprecios.preciomayor}}</td>
												<td>{{masprecios.preciocredito}}</td>
											</tr>	
										</tbody>
									</table>
								</template>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="col-md-12 col-xs-12" align="center">
		<ul class="pagination">
			<li class="page-item disabled" v-if="paginacion.actual <= 1">
		    	<a class="page-link"> <i data-acorn-icon="chevron-left"></i> </a> 
		    </li>
		    <li class="page-item" v-if="paginacion.actual > 1">
		    	<a class="page-link" href="#" v-on:click.prevent="phuyu_paginacion(paginacion.actual - 1)"> 
		    		<i data-acorn-icon="chevron-left"></i>
		    	</a> 
		    </li>

		    <li class="page-item" v-for="pag in phuyu_paginas" v-bind:class="[pag==phuyu_actual ? 'active':'']">
		    	<a class="page-link" href="#" v-on:click.prevent="phuyu_paginacion(pag)">{{pag}}</a> 
		    </li>

		    <li class="page-item" v-if="paginacion.actual < paginacion.ultima">
		    	<a class="page-link" href="#" v-on:click.prevent="phuyu_paginacion(paginacion.actual + 1)"> 
		    		<i data-acorn-icon="chevron-right"></i> 
		    	</a> 
		    </li>
		    <li class="page-item disabled" v-if="paginacion.actual >= paginacion.ultima">
		    	<a class="page-link"> <i data-acorn-icon="chevron-right"></i> </a> 
		    </li>
		</ul>
	</div>

	<div id="modal_precios" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header modal-phuyu-titulo"> 
					<button type="button" class="close" data-dismiss="modal" style="font-size:30px;margin-bottom:0px;">
						<i class="fa fa-times-circle"></i> 
					</button>
					<h4 class="modal-title"> <b style="letter-spacing:1px;">MAS PRECIOS DEL PRODUCTO</b> </h4> 
				</div>
				<div class="modal-body text-center" style="height:350px;">
					<h5>
						<b>PRODUCTO: {{masprecios.producto}} &nbsp; <span class="label label-warning">U.M. {{masprecios.unidad}}</span></b>
					</h5> <hr>
					
					<div class="col-md-4">
						<div class="x_panel">
							<h4> <b>PRECIO VENTA PUBLICO</b> </h4> 
							<button type="button" class="btn btn-success btn-block" v-on:click="phuyu_seleccionado_1(masprecios.precio)"> 
								<b style="font-size:18px;">S/. {{masprecios.precio}}</b> 
							</button>
						</div>
					</div>
					<div class="col-md-4">
						<div class="x_panel">
							<h4> <b>PRECIO VENTA MINIMO</b> </h4> 
							<button type="button" class="btn btn-success btn-block" v-on:click="phuyu_seleccionado_1(masprecios.preciomin)">
								<b style="font-size:18px;">S/. {{masprecios.preciomin}}</b> 
							</button>
						</div>
					</div>
					<div class="col-md-4">
						<div class="x_panel">
							<h4> <b>PRECIO VENTA CREDITO</b> </h4> 
							<button type="button" class="btn btn-success btn-block" v-on:click="phuyu_seleccionado_1(masprecios.preciocredito)">
								<b style="font-size:18px;">S/. {{masprecios.preciocredito}}</b> 
							</button>
						</div>
					</div>
					<div class="col-md-4">
						<div class="x_panel">
							<h4> <b>PRECIO VENTA X MAYOR</b> </h4> 
							<button type="button" class="btn btn-success btn-block" v-on:click="phuyu_seleccionado_1(masprecios.preciomayor)">
								<b style="font-size:18px;">S/. {{masprecios.preciomayor}}</b> 
							</button>
						</div>
					</div>
					<div class="col-md-4">
						<div class="x_panel">
							<h4> <b>PRECIO DE COSTO</b> </h4> 
							<button type="button" class="btn btn-success btn-block" v-on:click="phuyu_seleccionado_1(masprecios.preciocosto)">
								<b style="font-size:18px;">S/. {{masprecios.preciocosto}}</b> 
							</button>
						</div>
					</div>
					<div class="col-md-4">
						<div class="x_panel">
							<h4> <b>PRECIO ADICIONAL</b> </h4> 
							<button type="button" class="btn btn-success btn-block" v-on:click="phuyu_seleccionado_1(masprecios.precioadicional)">
								<b style="font-size:18px;">S/. {{masprecios.precioadicional}}</b> 
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="modal_salidas" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
				<div class="modal-header"> 
					<button type="button" class="close" data-dismiss="modal" style="font-size:30px;margin-bottom:0px;">
						<i class="fa fa-times-circle"></i> 
					</button>
					<h4 class="modal-title"> <b style="letter-spacing:1px;">SALIDA DE STOCK</b> </h4> 
				</div>
				<div class="modal-body" style="height: 410px;">
					<h4 align="center">
						{{salida.producto}} <br> <br> <span class="label label-warning">STOCK: {{salida.stock}} {{salida.unidad}} </span> 
					</h4> <hr>

					<div class="row">
						<div class="col-md-6"> <label align="center">FECHA KARDEX Y COMPROBANTE</label> </div>
						<div class="col-md-6"> <input type="text" class="form-control input-sm datepicker" id="fechakardex_salida" value="<?php echo date('Y-m-d');?>"> </div>
					</div> <br>

					<div class="row">
						<div class="col-md-6"> <label align="center">CANTIDAD SALIDA {{salida.unidad}}</label> </div>
						<div class="col-md-6"> <input type="number" class="form-control number" min="0" step="0.01" v-model="salida.cantidad" v-on:keyup="phuyu_unidadingreso()"> </div>
					</div> <hr>

					<div class="row">
						<div class="col-md-6"> <label align="center">UNIDAD A CONVERTIR</label> </div>
						<div class="col-md-6"> 
							<select class="form-control number" id="codunidad_ingreso" v-model="salida.codunidad_ingreso" v-on:change="phuyu_unidadingreso()">
								<option value="0">SELECCIONE</option>
								<option v-for="dato in unidades" v-bind:value="dato.codunidad"> {{dato.descripcion}} </option>
							</select>
						</div>
					</div>

					<h5 class="text-center"> <b>TOTAL INGRESO: {{salida.cantidadingreso}}</b> </h5>
					<button type="button" class="btn btn-success btn-block btn-salida" v-on:click="phuyu_guardarsalida()">GUARDAR OPERACION DE STOCK</button>
				</div>
			</div>
		</div>
	</div>

	<div id="modal_stock" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header modal-phuyu-titulo">
					<h5 class="modal-title"><b style="font-size:20px;">STOCK EN ALMACENES</b></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body" style="height: 410px;">
					<h4 align="center">
						{{stock.producto}} <br> <br> <span class="label label-warning">STOCK: {{stock.stock}} {{stock.unidad}} </span> 
					</h4> <hr>

					<div class="row">
						<div class="col-md-12">
							<div class="table-responsive">
								<table class="table table-bordered">
									<tbody>
										<tr v-for="dato in almacenes">
											<td>{{dato.almacen}}</td>
											<td>
												<span class="text-danger" v-for="(unidads, und) in dato.unidades"><strong>{{unidads.descripcion}}: {{unidads.stock}}</strong><br></span>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div> <br>

				</div>
			</div>
		</div>
	</div>
</div>

<script>
	var phuyu_buscar = new Vue({
		el: "#phuyu_buscar",
		data: {
			cargando: true, buscar: "", rubro:"<?php echo $_SESSION['phuyu_rubro'];?>", verprecios:1, putunidades:[],mostrarprecio:0,
			productos:[], unidades:[], productoprecio:{},almacenes:[],
			masprecios: {
				producto:"", unidad:"", precio:0, preciomin:0, preciocredito:0, preciomayor:0, preciocosto:0, precioadicional:0
			},
			stock:{
                producto:"", unidad:"", stock:0
			},
			salida: {
				producto:"", unidad:"", codproducto:0, codunidad:0, factor:0, preciocosto:0, stock:0, cantidad:1, fechakardex:"", codunidad_ingreso:0, factor_ingreso:0, cantidadingreso:0
			},
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
			phuyu_nuevoproducto : function(){
				$(".compose").removeClass("col-md-4").addClass("col-md-9");
				phuyu_sistema.phuyu_loader("phuyu_formulario",180);
				this.$http.post(url+"almacen/productos/nuevo").then(function(data){
					$("#phuyu_formulario").empty().html(data.body);phuyu_sistema.phuyu_finloader("phuyu_formulario");
				},function(){
					phuyu_sistema.phuyu_error();phuyu_sistema.phuyu_finloader("phuyu_formulario");
				});
			},
			phuyu_productos: function(){
				var buscar = "buscar_salidas";
				if (phuyu_controller=="almacen/ingresos" || phuyu_controller=="almacen/salidas" || phuyu_controller=="compras/compras") {
					var buscar = "buscar_ingresos"; this.verprecios = 0;
				}

				this.cargando = true;
				this.$http.post(url+"almacen/productos/"+buscar,{"buscar":this.buscar,"pagina":this.paginacion.actual}).then(function(data){
					this.productos = data.body.lista; this.paginacion = data.body.paginacion; this.cargando = false;
				},function(){
					phuyu_sistema.phuyu_error(); this.cargando = false;
				});
			},
			phuyu_buscar: function(){
				this.paginacion.actual = 1; this.phuyu_productos();
			},
			phuyu_paginacion: function(pagina){
				this.paginacion.actual = pagina; this.phuyu_productos();
			},
			phuyu_seleccionado: function(index,producto){
				index = index;
				$('.projects tr:eq('+index+') td').addClass("columna");
				phuyu_operacion.phuyu_additem(producto, producto.precio);
				timeout = setTimeout(removerColumna, 100, index);
			},
			phuyu_masprecios:function(producto,index){
				this.masprecios.producto = producto.descripcion; this.masprecios.unidad = producto.unidad;
				this.masprecios.precio = producto.precio; this.masprecios.preciomin = producto.preciomin; 
				this.masprecios.preciocredito = producto.preciocredito; this.masprecios.preciomayor = producto.preciomayor;
				this.masprecios.preciocosto = producto.precio; this.masprecios.preciomayorcre = producto.preciomayorcre;

				this.productoprecio = producto; 
				if(this.mostrarprecio==index){
					this.mostrarprecio = 0;
				}else{
					this.mostrarprecio = index;
				}
			},
			phuyu_masstock:function(producto){
				this.stock.producto = producto.descripcion;
				this.stock.stock = producto.stock;
				this.stock.unidad = producto.unidad;
				$("#modal_stock").modal("show");
				this.$http.get(url+"almacen/productos/stock_almacenes/"+producto.codproducto).then(function(data){
					var datos = data.body
                    var filas = [];
					$.each( datos.almacenes, function( k, v ) {
                        var unidades = []; var factores = []; var logo = []; arreglo = [];
				    	unidades = (v.unidades).split(";"); var funidades = [];

				    	for (var i = 0; i < unidades.length; i++) {
		                    factores = (unidades[i]).split("|");
				    		logo = {descripcion:factores[1],codunidad:factores[0],factor:factores[8],stock:factores[3]};
				    		funidades.push(logo)
				    	}
				    	this.putunidades = funidades;
				    	filas.push({
                           almacen:v.almacen, unidades : this.putunidades
				    	});
				    	this.putunidades = [];
					});
					

			    	
					this.almacenes = filas; 
					$("#modal_stock").modal({backdrop: 'static', keyboard: false});
				});
			},
			phuyu_seleccionado_1: function(precio){
				phuyu_operacion.phuyu_additem(this.productoprecio,precio); $("#modal_precios").modal("hide");
			},
			phuyu_salida:function(producto){
				this.salida.producto = producto.descripcion; this.salida.unidad = producto.unidad;
				this.salida.codproducto = producto.codproducto;
				this.salida.codunidad = producto.codunidad;
				this.salida.factor = producto.factor;
				this.salida.preciocosto = producto.precio;
				this.salida.stock = producto.stock;
				this.salida.cantidad = 1;
				this.salida.codunidad_ingreso = 0;
				this.salida.factor_ingreso = 0;
				this.salida.cantidadingreso = 0;

				this.$http.get(url+"almacen/productos/unidades_venta/"+producto.codproducto+"/"+producto.factor).then(function(data){
					this.unidades = data.body;
					$(".btn-salida").html("GUARDAR OPERACION DE STOCK").removeAttr("disabled"); 
					$("#modal_salidas").modal({backdrop: 'static', keyboard: false});
				});
			},
			phuyu_unidadingreso: function(){
				that = this;
				var existe_factor = this.unidades.filter(function(u){
				    if(u.codunidad == that.salida.codunidad_ingreso){
				    	that.salida.factor_ingreso = u.factor; return u;
				    };
				});
				this.salida.cantidadingreso = 0;
				if (this.salida.factor_ingreso>0) {
					this.salida.cantidadingreso = this.salida.cantidad * this.salida.factor / this.salida.factor_ingreso;
				}
			},
			phuyu_guardarsalida: function(){
				if ($("#codunidad_ingreso").val()==0 || $("#codunidad_ingreso").val()=="") {
					phuyu_sistema.phuyu_alerta("SELECCIONE UNIDAD MEDIDA A CONVERTIR","","error"); return false;
				}
				if (this.salida.cantidad=="") {
					phuyu_sistema.phuyu_alerta("INGRESAR LA CANTIDAD A DAR SALIDA","","error"); return false;
				}
				if (parseFloat(this.salida.stock)<parseFloat(this.salida.cantidad)) {
					phuyu_sistema.phuyu_alerta("LA CANTIDAD EN STOCK SOLO ES "+this.salida.stock+" "+this.salida.unidad,"","error");
				}else{
					this.salida.fechakardex = $("#fechakardex_salida").val();
					$(".btn-salida").html("<i class='fa fa-spinner fa-spin'></i> GUARDANDO OPERACION").attr("disabled","true");
					this.$http.post(url+"almacen/salidas/guardar_operacionstock",this.salida).then(function(data){
						if (data.body==1) {
							phuyu_sistema.phuyu_alerta("OPERACION GUARDADA CORRECTAMENTE","","success");
							this.phuyu_productos();
						}else{
							phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS", "ERROR DE RED","error");
						}
						$("#modal_salidas").modal("hide");
					},function(){
						phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS", "ERROR DE RED","error");
						$("#modal_salidas").modal("hide");
					});
				}
			},
			phuyu_cerrar: function(){
				$(".compose").slideToggle();
			}
		},
		created: function(){
			this.phuyu_productos();
		}
	});
</script>

<script> 
    if (typeof AcornIcons !== 'undefined') {
      new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
      const icons = new Icons();
    }
 function removerColumna(index){
 	$('.projects tr:eq('+index+') td').removeClass("columna");
} 
</script>