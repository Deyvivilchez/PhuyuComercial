var phuyu_operacion = new Vue({
	el: "#phuyu_operacion",
	data: {
		campos:{
			"codlote":phuyu_lineas.registro,"codmovimientotipo":33,"codpersona":1,"codcomprobantetipo":"","seriecomprobante":"","codalmacen_ref":"",
			"codcomprobantetipo_ref":0,"seriecomprobante_ref":"","nrocomprobante_ref":"","descripcion":"","fechakardex":""
		},
		pagos:{
			codtipopago_efectivo:1, monto_efectivo:0, vuelto_efectivo:0, codtipopago_tarjeta:0, monto_tarjeta:0, nrovoucher:""
		},
		cuotas:[{
			nrocuota:1,nroletra:1,nrounicodepago:1
		}],
		estado:0, porcigv:$("#porcigv").val(), detalle: [],putunidades : [], totales: {"valorventa":0.00,"igv":0.00,"importe":0.00},
	},
	methods: {
		phuyu_item: function(){
			$(".compose").slideToggle(); $("#phuyu_tituloform").text("BUSCAR PRODUCTO"); 
			phuyu_sistema.phuyu_loader("phuyu_formulario",180); 

			this.$http.post(url+"almacen/productos/buscar/ventas").then(function(data){
				$("#phuyu_formulario").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); 
				phuyu_sistema.phuyu_modulo();
			});
		},
		phuyu_additem: function(producto){
			var existeproducto = this.detalle.filter(function(p){
			    if(p.codproducto == producto.codproducto && p.codunidad == producto.codunidad ){
			    	p.cantidad = p.cantidad + 1; return p;
			    };
			});

		    if (existeproducto.length==0) {
		    	var unidades = []; var factores = []; var logo = []; arreglo = [];
		    	unidades = (producto.unidades).split(";");

		    	for (var i = 0; i < unidades.length; i++) {
                    factores = (unidades[i]).split("|");
		    		logo = {descripcion:factores[1],codunidad:factores[0],factor:factores[8]};
		    		this.putunidades.push(logo)
		    		if(factores[8]==1){
		    			producto.codunidad = factores[0];
		    			producto.unidad = factores[1];
		    		}
		    	}

				this.detalle.push({
					"codproducto":producto.codproducto,"producto":producto.descripcion,"codunidad":producto.codunidad,"unidades": this.putunidades,
					"cantidad":1,"stock":producto.stock,"control":producto.controlstock,"precio":parseFloat(producto.precio).toFixed(2),
					"preciorefunitario":producto.precio,"subtotal":parseFloat(producto.precio).toFixed(2),"unidad": producto.unidad
				});
				this.phuyu_calcular(producto,1);
				this.putunidades = [];
		    }else{
		    	this.phuyu_calcular(existeproducto[0],3);
		    }
		},
		informacion_unidad: function(index,producto,val){
			var codunidad = this.detalle[index].codunidad;
			//console.log(codunidad)
            var codproducto = producto.codproducto;
            this.$http.post(url+"almacen/productos/informacion_item",{"codunidad": codunidad, "codproducto": codproducto, "salida": 1}).then(function(data){
				//console.log(data)
				producto.preciosinigv = data.body[0].precio; producto.precio = data.body[0].precio; 
		    	producto.valorventa = data.body[0].precio; producto.subtotal = data.body[0].precio;
		    	producto.subtotal_tem = data.body[0].precio;
		    	
		    	this.detalle[index].afectacionigv = 20; this.detalle[index].igv = 0; var porcentaje = 1;
				if (this.detalle[index].afectoigvventa==1) {
					var porcentaje = (1 + this.igvsunat) / 100;

					this.detalle[index].afectacionigv = 10;
					producto.preciosinigv = Number((producto.precio / porcentaje).toFixed(4));
					producto.valorventa = Number((producto.precio / porcentaje).toFixed(2));
					producto.igv = Number((producto.subtotal - producto.valorventa).toFixed(2));
				}
				this.detalle[index].codunidad = codunidad;
				this.detalle[index].stock = data.body[0].stock;
				this.phuyu_itemcalcular(producto,1)
			});
		},
		phuyu_deleteitem: function(index,producto){
			this.phuyu_calcular(producto,2); this.detalle.splice(index,1);
			this.putunidades = [];
		},
		phuyu_calcular: function(producto,tipo){
			if (tipo==1) {
				this.totales.valorventa = Number((this.totales.valorventa + parseFloat(producto.precio) ).toFixed(2));
			}else{
				if (tipo==2) {
					this.totales.valorventa = Number((this.totales.valorventa - producto.subtotal).toFixed(2));
				}else{
					this.totales.valorventa = Number((this.totales.valorventa - producto.subtotal).toFixed(2));
					producto.subtotal = Number((producto.cantidad * producto.precio).toFixed(2));
					this.totales.valorventa = Number((this.totales.valorventa + producto.subtotal).toFixed(2));
				}
			}
			this.totales.importe = Number((this.totales.valorventa + this.totales.igv).toFixed(2));
		},
		phuyu_itemcalcular: function (item,tipoprecio) {
			var porcentaje = 1;
			if (item.codafectacionigv==21) {
				item.preciobruto = 0; item.porcdescuento = 0; item.descuento = 0; item.preciosinigv = 0; item.precio = 0; 
				item.igv = 0; item.valorventa = 0; item.subtotal = 0; 
			}
			if (item.codafectacionigv==10) {
				var porcentaje = (1 + this.igvsunat) / 100;
			}

			if (tipoprecio==-1) {
				item.porcdescuento = Number((item.descuento / item.preciobruto * 100).toFixed(2));
				item.precio = Number((item.preciobruto - item.descuento).toFixed(4)); tipoprecio = 2;
			}
			if (tipoprecio==-2) {
				item.descuento = Number((item.preciobruto * item.porcdescuento / 100).toFixed(4));
				item.precio = Number((item.preciobruto - item.descuento).toFixed(4)); tipoprecio = 2;
			}
			if(tipoprecio==0){
				item.precio = Number((item.preciobruto - item.descuento).toFixed(4));
			}
			
			var descuento = item.descuento;
			if (item.descuento=="") {
				var descuento = 0;
			}
			
			if (tipoprecio==1) {
				item.precio = Number((item.preciosinigv * porcentaje).toFixed(4));
				item.preciobruto = Number((item.precio + descuento).toFixed(4));
			}
			if (tipoprecio==2) {
				item.preciosinigv = Number((item.precio / porcentaje).toFixed(4));
				item.preciobruto = Number((item.precio + descuento).toFixed(4));
			}

			item.icbper = 0;
			if (item.conicbper==1) {
				item.icbper = Number((item.cantidad * this.icbpersunat).toFixed(2));
			}

			item.valorventa = Number((item.cantidad * item.preciosinigv).toFixed(2));
			item.subtotal = Number((item.cantidad * item.precio).toFixed(2));
			item.igv = Number((item.subtotal - item.valorventa).toFixed(2));
			this.phuyu_totales();
		},
		phuyu_totales: function () {
			this.totales.valorventa = 0.00; this.totales.subtotal = 0.00; this.totales.importe = 0.00;
			t = this;
			var detalle = this.detalle.filter(function(p){
				console.log(p.valorventa)
				t.totales.valorventa = Number((t.totales.valorventa + parseFloat(p.subtotal) ).toFixed(2));
			});

			this.totales.importe = Number((this.totales.valorventa).toFixed(2));
		},
		phuyu_guardar: function(){
			if (this.detalle.length==0) {
				phuyu_sistema.phuyu_noti("REGISTRAR UN PRODUCTO EN EL DETALLE","REGISTRAR ITEM PARA LA SALIDA","error"); 
				return false;
			}

			this.campos.fechakardex = $("#fechakardex").val();
			this.estado = 1; phuyu_sistema.phuyu_inicio_guardar("GUARDANDO SALIDA DE ALMACEN . . .");
			
			this.$http.post(url+phuyu_controller+"/guardarsalida", {"campos":this.campos,"detalle":this.detalle,"totales":this.totales,"pagos":this.pagos,"cuotas":this.cuotas}).then(function(data){
				if (data.body=="e") {
					phuyu_sistema.phuyu_alerta("SU SESION DE USUARIO A TERMINADO","DEBE INICIAR SESION NUEVAMENTE","error");
				}else{
					if (data.body.estado==1) {
						phuyu_sistema.phuyu_alerta("SALIDA DE ALMACEN REGISTRADO","SALIDA DE ALMACEN EN EL SISTEMA","success");
						phuyu_sistema.phuyu_modulo();
					}else if(data.body.estado==2){
						var informacion = data.body.informacion;
						var mensaje = '';
						$.each(informacion.producto,function(indice, elemento) {
						  mensaje += '* '+elemento+': '+informacion.stock[indice]+' '+informacion.unidad[indice]+'\n';
						});
                       phuyu_sistema.phuyu_alerta("PRODUCTOS SIN STOCK, STOCK ACTUAL DE LOS PRODUCTOS:",mensaje,"error");
                       this.estado = 0;
					}else{
						phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR SALIDA DE ALMACEN","ERROR DE RED","error");
						this.estado = 0;
					}
				}
				phuyu_sistema.phuyu_fin();
			}, function(){
				phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR SALIDA DE ALMACEN","ERROR DE RED","error");
			});
		},
		phuyu_cerrar: function(){
			phuyu_sistema.phuyu_modulo();
		}
	},
	created: function(){
		phuyu_sistema.phuyu_fin();
	}
});