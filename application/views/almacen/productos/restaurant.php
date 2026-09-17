<style>
	#phuyu_buscar.phuyu-restobar-buscar .x_header {
		background: #fff;
		border: 1px solid #e9ebec;
		border-radius: 8px;
		box-shadow: 0 1px 2px rgba(56, 65, 74, 0.08);
		flex: 0 0 auto;
		margin-bottom: 12px;
		padding: 12px;
	}
	#phuyu_buscar.phuyu-restobar-buscar {
		display: flex;
		flex-direction: column;
		height: 100%;
		min-height: 0;
	}
	#phuyu_buscar.phuyu-restobar-buscar .form-control {
		border: 1px solid #d9e2ef;
		border-radius: 6px;
		box-shadow: none;
		min-height: 40px;
	}
	#phuyu_buscar.phuyu-restobar-buscar .phuyu-product-card {
		border: 1px solid #e9ebec;
		border-radius: 8px;
		cursor: pointer;
		margin-top: 8px;
		overflow: hidden;
		padding: 4px;
		transition: box-shadow .2s ease, transform .2s ease;
	}
	#phuyu_buscar.phuyu-restobar-buscar .phuyu-product-card.sin-stock {
		border-color: #f06548;
		box-shadow: inset 0 0 0 1px rgba(240, 101, 72, .28);
		cursor: not-allowed;
		opacity: .78;
	}
	#phuyu_buscar.phuyu-restobar-buscar .phuyu-product-card:hover {
		box-shadow: 0 8px 18px rgba(15, 23, 42, 0.1);
		transform: translateY(-1px);
	}
	#phuyu_buscar.phuyu-restobar-buscar .phuyu-product-card.sin-stock:hover {
		box-shadow: inset 0 0 0 1px rgba(240, 101, 72, .28);
		transform: none;
	}
	#phuyu_buscar.phuyu-restobar-buscar .phuyu-stock-badge {
		border-radius: 999px;
		display: inline-block;
		font-size: 10px;
		font-weight: 700;
		margin-top: 4px;
		padding: 2px 7px;
	}
	#phuyu_buscar.phuyu-restobar-buscar .phuyu-product-title {
		font-size: 10px;
		font-weight: 700;
		height: 30px;
		line-height: 1.2;
		margin: 0 0 3px;
		overflow: hidden;
	}
	#phuyu_buscar.phuyu-restobar-buscar .phuyu-product-unit-mini {
		background: rgba(64, 81, 137, .10);
		border-radius: 999px;
		color: #405189;
		flex: 0 0 auto;
		font-size: 8px;
		font-weight: 800;
		line-height: 1;
		padding: 2px 4px;
	}
	#phuyu_buscar.phuyu-restobar-buscar .phuyu-products-scroll {
		flex: 1 1 auto;
		max-height: 285px;
		min-height: 0;
		overflow-x: hidden;
		overflow-y: auto;
		padding-right: 6px;
		scrollbar-color: #cbd5e1 #f3f6f9;
		scrollbar-width: thin;
	}
	#phuyu_buscar.phuyu-restobar-buscar .phuyu-products-scroll::-webkit-scrollbar {
		width: 6px;
	}
	#phuyu_buscar.phuyu-restobar-buscar .phuyu-products-scroll::-webkit-scrollbar-track {
		background: #f3f6f9;
		border-radius: 999px;
	}
	#phuyu_buscar.phuyu-restobar-buscar .phuyu-products-scroll::-webkit-scrollbar-thumb {
		background: #cbd5e1;
		border-radius: 999px;
	}
	#phuyu_buscar.phuyu-restobar-buscar .phuyu-products-scroll::-webkit-scrollbar-thumb:hover {
		background: #9ca3af;
	}
	#phuyu_buscar.phuyu-restobar-buscar .phuyu-stock-ok {
		background: rgba(10, 179, 156, .14);
		color: #087f6f;
	}
	#phuyu_buscar.phuyu-restobar-buscar .phuyu-stock-error {
		background: rgba(240, 101, 72, .16);
		color: #d84b2a;
	}
	#phuyu_buscar.phuyu-restobar-buscar .modal-content {
		border: 0;
		border-radius: 8px;
		box-shadow: 0 10px 30px rgba(15, 23, 42, 0.14);
	}
	#phuyu_buscar.phuyu-restobar-buscar .modal-header {
		background: #f3f6f9;
		border-bottom: 1px solid #e9ebec;
	}
</style>

<div id="phuyu_buscar" class="phuyu-restobar-buscar">
	<div class="x_header">
		<div class="input-group">
			<span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
			<input type="text" class="form-control border-start-0" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="BUSCAR PRODUCTO, PLATO O BEBIDA . . ." autocomplete="off">
		</div>
	</div>

	<div class="phuyu_cargando" v-if="cargando">
		<div class="spinner-border text-primary" role="status"></div> <h5>CARGANDO DATOS</h5>
	</div>
	<div class="phuyu-products-scroll" v-if="!cargando">
		<div class="row">
			<div class="col-md-4" v-for="dato in productos" v-on:click="phuyu_seleccionado(dato)">
				<div class="phuyu-product-card" v-bind:class="phuyu_sin_stock(dato) ? 'sin-stock' : ''">
				<div v-bind:style="{background: dato.background}" v-bind:title="dato.mostrarstock">
					<div style="padding:4px;text-align:center;">
						<p class="phuyu-product-title">{{dato.descripcion.substring(0,34)}} - {{dato.marca}}</p>
						<b style="font-size:20px;">S/. {{dato.precio}}</b>
						<div v-if="dato.controlstock == 1">
							<span class="phuyu-stock-badge" v-bind:class="phuyu_sin_stock(dato) ? 'phuyu-stock-error' : 'phuyu-stock-ok'">
								{{ dato.mostrarstock || 'STOCK: 0' }}
							</span>
						</div>
						<div>
							<span class="phuyu-product-unit-mini">{{ dato.unidad && dato.unidad.indexOf('UNIDAD') === 0 ? 'UND' : dato.unidad }}</span>
						</div>
					</div>
				</div>
				</div>
			</div>
		</div>
	</div>

	<div id="modal_precios" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content" align="center">
				<div class="modal-header"> 
					<h5 class="modal-title">Mas precios del producto</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
				</div>
				<div class="modal-body" style="height: 380px;">
					<h4 align="center">
						{{masprecios.producto}} <br> <br> <span class="badge bg-warning text-dark">UNIDAD: {{masprecios.unidad}}</span>
					</h4> <hr>
					
					<div class="col-md-4">
						<div class="x_panel">
							<h4> <b>PRECIO VENTA PUBLICO</b> </h4> 
							<button type="button" class="btn btn-success btn-sm" v-on:click="phuyu_seleccionado_1(masprecios.precio)"> 
								<b style="font-size:18px;">S/. {{masprecios.precio}}</b> 
							</button>
						</div>
					</div>
					<div class="col-md-4">
						<div class="x_panel">
							<h4> <b>PRECIO VENTA MINIMO</b> </h4> 
							<button type="button" class="btn btn-success btn-sm" v-on:click="phuyu_seleccionado_1(masprecios.preciomin)">
								<b style="font-size:18px;">S/. {{masprecios.preciomin}}</b> 
							</button>
						</div>
					</div>
					<div class="col-md-4">
						<div class="x_panel">
							<h4> <b>PRECIO VENTA CREDITO</b> </h4> 
							<button type="button" class="btn btn-success btn-sm" v-on:click="phuyu_seleccionado_1(masprecios.preciocredito)">
								<b style="font-size:18px;">S/. {{masprecios.preciocredito}}</b> 
							</button>
						</div>
					</div>
					<div class="col-md-4">
						<div class="x_panel">
							<h4> <b>PRECIO VENTA X MAYOR</b> </h4> 
							<button type="button" class="btn btn-success btn-sm" v-on:click="phuyu_seleccionado_1(masprecios.preciomayor)">
								<b style="font-size:18px;">S/. {{masprecios.preciomayor}}</b> 
							</button>
						</div>
					</div>
					<div class="col-md-4">
						<div class="x_panel">
							<h4> <b>PRECIO DE COSTO</b> </h4> 
							<button type="button" class="btn btn-success btn-sm" v-on:click="phuyu_seleccionado_1(masprecios.preciocosto)">
								<b style="font-size:18px;">S/. {{masprecios.preciocosto}}</b> 
							</button>
						</div>
					</div>
					<div class="col-md-4">
						<div class="x_panel">
							<h4> <b>PRECIO ADICIONAL</b> </h4> 
							<button type="button" class="btn btn-success btn-sm" v-on:click="phuyu_seleccionado_1(masprecios.precioadicional)">
								<b style="font-size:18px;">S/. {{masprecios.precioadicional}}</b> 
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
	var phuyu_buscar = new Vue({
		el: "#phuyu_buscar",
		data: {
			cargando: true, buscar: "", buscando: "buscando_restobar", codlinea:"<?php echo $codlinea;?>", verprecios:1, productos:[], productoprecio:{},
			masprecios: {"producto":"", "unidad":"", "precio":0, "preciomin":0, "preciocredito":0, "preciomayor":0, "preciocosto":0, "precioadicional":0},
			salida: {"producto":"","unidad":"","codproducto":0,"codunidad":0,"factor":0,"preciocosto":0,"stock":0,"cantidad":1}
		},
		methods: {
			phuyu_productos: function(){
				this.cargando = true;
				this.$http.post(url+"almacen/productos/"+this.buscando,
				{"buscar":this.buscar,"codlinea":this.codlinea})
				.then(function(data){
					this.productos = data.body; this.codlinea = 0; this.cargando = false;
				},function(){
					phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS", "ERROR DE RED","error"); 
					this.cargando = false;
				});
			},
			phuyu_buscar: function(){
				this.phuyu_productos();
			},

			phuyu_sin_stock: function(producto){
				return parseInt(phuyu_operacion.stockalmacen) === 1 && parseInt(producto.controlstock) === 1 && (parseFloat(producto.stockdisponible) || 0) <= 0;
			},
			phuyu_seleccionado: function(producto){
				if (this.phuyu_sin_stock(producto)) {
					phuyu_sistema.phuyu_alerta(
						"No hay stock disponible",
						producto.descripcion + " | " + (producto.mostrarstock || "STOCK: 0"),
						"error"
					);
					return false;
				}
				phuyu_operacion.phuyu_additem(producto, producto.precio);
			},
			phuyu_masprecios:function(producto){
				this.masprecios.producto = producto.descripcion; this.masprecios.unidad = producto.unidad;
				this.masprecios.precio = producto.precio; this.masprecios.preciomin = producto.preciomin; 
				this.masprecios.preciocredito = producto.preciocredito; this.masprecios.preciomayor = producto.preciomayor;
				this.masprecios.preciocosto = producto.preciocosto; this.masprecios.precioadicional = producto.precioadicional;

				this.productoprecio = producto; $("#modal_precios").modal("show");
			},
			phuyu_seleccionado_1: function(precio){
				phuyu_operacion.phuyu_additem(this.productoprecio,precio, precio); $("#modal_precios").modal("hide");
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
