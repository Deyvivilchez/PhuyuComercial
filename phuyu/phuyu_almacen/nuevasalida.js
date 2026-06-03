var phuyu_operacion = new Vue({
	el: "#phuyu_operacion",
	data: {
		campos:{
			"codkardex_ref":0,"codmovimientotipo":"","codpersona":1,"cliente":$("#codpersona option:selected").text(),"codcomprobantetipo":"","seriecomprobante":"","codalmacen_ref":"",
			"codcomprobantetipo_ref":0,"seriecomprobante_ref":"","nrocomprobante_ref":"","descripcion":"","fechakardex":""
		},
		estado:0, igvsunat:$("#igvsunat").val(), detalle: [],detalle_prestamo: [],putunidades : [], totales: {"valorventa":0.00,"igv":0.00,"importe":0.00},
	},
	methods: {
		phuyu_infocliente: function(){
			this.campos.codpersona = $("#codpersona").val()
			this.campos.cliente = $(".select2-selection__rendered").text();
			this.phuyu_prestamos();
        },
        phuyu_verprestamo: function(codkardex){
        	$(".compose").removeClass("col-md-4").addClass("col-md-7");
			$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario",180);
			this.$http.get(url+phuyu_controller+"/verprestamo/"+codkardex).then(function(data){
				$("#phuyu_formulario").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); 
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			});
        },
        phuyu_prestamos: function(){
        	if(this.campos.codmovimientotipo==29){
				if (this.campos.codpersona!="") {
					this.estado = 1;
					this.$http.get(url+phuyu_controller+"/prestamosotorgados/"+this.campos.codpersona).then(function(data){
						this.detalle_prestamo = data.body.prestamo; this.estado = 0;
					},function(){
						phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
					});
				}else{
					this.comprobantes = []; phuyu_sistema.phuyu_noti("SELECCIONAR A LA PERSONA Y EL TIPO MOVIMIENTO", "PARA FILTRAR LOS PRESTAMOS","error");
				}
			}else{

			}
		},
		phuyu_seleccionar: function(datos){
			$("#"+this.kardex_id).css({"background-color":"#fff","color":"#000"}); 
			this.kardex_id = datos.codkardex;
			$("#"+datos.codkardex).css({"background-color":"#13a89e","color":"#fff"});

			this.campos.codkardex_ref = datos.codkardex; 
			this.campos.codcomprobantetipo_ref = datos.codcomprobantetipo;
			this.campos.seriecomprobante_ref = datos.seriecomprobante; this.campos.nrocomprobante_ref = datos.nrocomprobante;
			this.campos.cliente = datos.cliente; this.campos.direccion = datos.direccion;

			this.$http.get(url+phuyu_controller+"/detalle/"+datos.codkardex).then(function(data){
				var productos = eval(data.body.detalle);
				var filas = [];
				$.each( productos, function( k, v ) {
				    var unidades = []; var factores = []; var logo = []; arreglo = [];
		    		unidades = (v.unidades).split(";"); var funidades = [];

			    	for (var i = 0; i < unidades.length; i++) {
	                    factores = (unidades[i]).split("|");
			    		logo = {descripcion:factores[1],codunidad:factores[0],factor:factores[8]};
			    		funidades.push(logo)
			    		if(factores[8]==1){
			    			v.codunidad = factores[0];
			    		}
			    	}
			    	this.putunidades = funidades;
			    	v.subtotal = parseFloat(v.cantidad)*parseFloat(v.precio);
			    	v.valorventa = parseFloat(v.cantidad)*parseFloat(v.preciosinigv);
					filas.push({
						"itemorigen":v.item,"codproducto":v.codproducto,"producto":v.producto,"codunidad":v.codunidad,"unidades": this.putunidades,
						"unidad":v.unidad,"cantidad":v.cantidad,"stock":v.stock,"control":v.controlstock,"precio":parseFloat(v.precio).toFixed(2),
						"preciorefunitario":v.precio,"subtotal":v.subtotal,"valorventa":v.valorventa,"codafectacionigv":v.codafectacionigv,"igv":v.igv,
						"preciosinigv" : v.preciosinigv
					});
					this.putunidades = [];
				});

				this.detalle = filas
				var datos = eval(data.body.totales);
				//this.totales.valorventa = datos[0]["valorventa"]; this.totales.igv = datos[0]["igv"]; this.totales.importe = datos[0]["importe"];
				this.phuyu_totales();
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
			});
		},
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
		    			producto.afectacionigv = factores[14];
		    		}
		    	}

		    	producto.preciosinigv = producto.precio;
				producto.valorventa =producto.precio;

		    	producto.igv = 0; var porcentaje = 1;
				if (producto.afectacionigv==10) {
					var porcentaje = (1 + this.igvsunat) / 100;
					producto.preciosinigv = Number((producto.precio / porcentaje).toFixed(4));
					producto.valorventa = Number((producto.precio / porcentaje).toFixed(2));
					producto.igv = Number((producto.precio - producto.valorventa).toFixed(2));
				}

				this.detalle.push({
					"itemorigen":0,"codproducto":producto.codproducto,"producto":producto.descripcion,"codunidad":producto.codunidad,"unidades": this.putunidades,
					"cantidad":1,"stock":producto.stock,"control":producto.controlstock,"precio":parseFloat(producto.precio).toFixed(2),
					"preciorefunitario":producto.precio,"subtotal":parseFloat(producto.precio).toFixed(2),"unidad": producto.unidad,"igv":producto.igv
					,"valorventa":producto.valorventa,"codafectacionigv": producto.afectacionigv
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
			
			this.$http.post(url+phuyu_controller+"/guardar", {"campos":this.campos,"detalle":this.detalle,"totales":this.totales}).then(function(data){
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
		},
		phuyu_clonar: function(){
			this.titulo = "NUEVA COMPRA";
			this.$http.post(url+phuyu_controller+"/clonar",{"codregistro":phuyu_salidas.registro}).then(function(data){
				var socio = eval(data.body.socio);
				$("#select2-codpersona-container").empty().append(socio[0]["razonsocial"]);
				this.campos.codpersona = socio[0]["codpersona"];

				$("#codpersona").val(socio[0]["codpersona"]);
				this.campos.nrodocumento = socio[0]["documento"];
				this.codtipodocumento = socio[0]["coddocumentotipo"];
				this.campos.cliente = socio[0]["razonsocial"];
				this.campos.direccion = data.body.campos[0].direccion;
				this.campos.condicionpago = data.body.campos[0].condicionpago;
				this.campos.codmovimientotipo = data.body.campos[0].codmovimientotipo;
				this.campos.codcomprobantetipo_ref = data.body.campos[0].codcomprobantetipo_ref;
				this.campos.seriecomprobante_ref = data.body.campos[0].seriecomprobante_ref;
				this.campos.nrocomprobante_ref = data.body.campos[0].nrocomprobante_ref;
				this.campos.codalmacen_ref = data.body.campos[0].codalmacen_ref;
				if(data.body.campos[0].condicionpago==2){
					this.campos.tasainteres = data.body.campos[0].tasainteres;
					this.campos.nrodias = data.body.campos[0].nrodias;
					this.campos.nrocuotas = data.body.campos[0].nrocuotas;
				}
				/* campos:{
					"codkardex":0,"codpersona":2,"retirar":true,"afectacaja":true,"codmovimientotipo":2,"fechacomprobante":"","fechakardex":"",
					"codmoneda":1,"tipocambio":0.00,"codcomprobantetipo":"","seriecomprobante":"","nrocomprobante":"","codconcepto":12,
					"condicionpago":1,"nrodias":30,"nrocuotas":1,"codcreditoconcepto":4,"tasainteres":0,"totalcredito":0,"descripcion":"REGISTRO POR COMPRA"
				}, */
				this.campos.retirar = data.body.campos[0].retirar;
				this.campos.afectacaja = data.body.campos[0].afectacaja;
				$("#fechakardex").val(data.body.campos[0].fechakardex);
				this.campos.codmoneda = data.body.campos[0].codmoneda;
				this.campos.tipocambio = data.body.campos[0].tipocambio;
				this.campos.codcomprobantetipo = data.body.campos[0].codcomprobantetipo;
				this.campos.descripcion = data.body.campos[0].descripcion;

				this.totales.flete = data.body.campos[0].flete;
				this.totales.gastos = data.body.campos[0].gastos;
				this.totales.subtotal = data.body.campos[0].importe;
				this.totales.valorventa = data.body.campos[0].valorventa;
				this.totales.bruto = data.body.campos[0].valorventa;
				this.totales.descuentoglobal = data.body.campos[0].descglobal;
				this.totales.igv = data.body.campos[0].igv;
				this.totales.importe = data.body.campos[0].importe;
				
				/* this.detalle.push({
					"stock":producto.stock,"control":producto.control, "descuentototal":0,"descuento":0,
					"calcular":producto.calcular
				}); */
				
				this.detalle = data.body.detalle;
				this.phuyu_totales()
				phuyu_sistema.phuyu_fin();
			});
		}
	},
	created: function(){
		if (parseInt(phuyu_salidas.registro)!=0) {
			this.phuyu_clonar();
		}else{
			phuyu_sistema.phuyu_fin(); 
		}
	}
});