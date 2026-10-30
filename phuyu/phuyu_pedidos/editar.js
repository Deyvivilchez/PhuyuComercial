var phuyu_operacion = new Vue({
	el: "#phuyu_operacion",
	data: {
		estado:0, codigobarra: "",codtipodocumento:0,
		stockalmacen: $("#stockalmacen").val(), igvsunat:$("#igvsunat").val(), icbpersunat:$("#icbpersunat").val(), rubro:0, series:[], seriesreferencia:[], detalle:[], cuotas:[], putunidades:[],
		campos:{
			codpedido:$("#codpedido").val(),codcreditopedido:0, codpersona:$("#codpersona").val(), codmovimientotipo:20, codcomprobantetiporeferencia:$("#comprobantereferencia").val(),seriecomprobantereferencia:$("#seriereferencia").val(), 
			nroreferencia:"", codcomprobantetipo:$("#comprobante").val(),seriecomprobante:$("#serie").val(), nro:$("#nrocomprobante").val(),
			fechacomprobante:"", fechakardex:"", codconcepto:13, descripcion:"REGISTRO POR PEDIDO", cliente:$("#cliente").val(), direccion:$("#direccion").val(),
			codempleado:$("#empleado").val(), codmoneda:1, tipocambio:0.00, codcentrocosto:0, nroplaca:"", retirar:true, afectacaja:true,
			condicionpago:$("#condicionpagoref").val(), nrodias:30, nrocuotas:1, codcreditoconcepto:3, tasainteres:0, interes:0, totalcredito:0, porcdescuento:0.00
		},
		item:{
			producto:"", unidad:"", cantidad:0, preciobruto:0, descuento:0, porcdescuento:0, preciosinigv:0, precio:0, 
			codafectacionigv:"", igv:0, valorventa:0, conicbper: 0, icbper:0, subtotal:0, descripcion:""
		},
		pagos:{
			codtipopago_efectivo:1, monto_efectivo:0, vuelto_efectivo:0, codtipopago_tarjeta:0, monto_tarjeta:0, nrovoucher:""
		},
		operaciones:{
			gravadas:0.00, exoneradas:0.00, inafectas:0.00, gratuitas:0.00
		},
		totales:{
			flete:0.00, gastos:0.00, bruto:0.00, descuentos:0.00, descglobal:0.00, valorventa:0.00, igv:0.00, isc:0.00, icbper:0.00, 
			subtotal:0.00, importe:0.00
		}
	},
	methods: {

		/* FUNCIONES GENERALES DE LA VENTA */

		phuyu_venta: function(){
			swal({
				title: "SEGURO REGISTRAR NUEVO PEDIDO?",   
				text: "LOS CAMPOS SE QUEDARAN VACIOS ", 
				icon: "warning",
				dangerMode: true,
				buttons: ["CANCELAR", "SI, NUEVA VENTA"],
			}).then((willDelete) => {
				if (willDelete){
					this.phuyu_nueva_venta();
				}
			});
		},
		phuyu_detalle: function(){
			this.$http.post(url+phuyu_controller+"/buscarproductos/"+this.campos.codpedido).then(function(data){
				if (data.body.length==0) {
					phuyu_sistema.phuyu_alerta("NO EXISTE EL DETALLE DEL PEDIDO", "CORREGIR POR FAVOR", "error");return;
				}else{
					var datos = data.body
					var filas = [];
					$.each( datos, function( k, v ) {

						var unidades = []; var factores = []; var logo = []; arreglo = [];
				    	unidades = (v.unidades).split(";"); var funidades = [];

				    	for (var i = 0; i < unidades.length; i++) {
		                    factores = (unidades[i]).split("|");
				    		logo = {descripcion:factores[1],codunidad:factores[0],factor:factores[8]};
				    		funidades.push(logo)
				    	}

				    	this.putunidades = funidades;

					    var cantidad_faltante =  parseFloat(v.cantidad);
				    	v.valorventa = v.preciosinigv * cantidad_faltante;

                         filas.push({codproducto: v.codproducto, producto: v.producto, codunidad: v.codunidad,unidades: this.putunidades,
				unidad: v.unidad, cantidad: cantidad_faltante, preciobruto: parseFloat(v.preciosinigv).toFixed(2), 
				preciosinigv: parseFloat(v.preciosinigv).toFixed(2), conicbper: v.conicbper, icbper: parseFloat(v.icbper).toFixed(2),
				precio: parseFloat(v.preciounitario).toFixed(2),preciorefunitario: v.preciorefunitario, porcdescuento: v.porcdescuento, 
				descuento: v.descuento,codafectacionigv: v.codafectacionigv, igv: v.igv, 
				valorventa: v.valorventa, subtotal:v.subtotal, subtotal_tem:v.subtotal, 
				descripcion:v.descripcion,itempedido:v.item,stock: v.stock, calcular: v.calcular})

                         this.putunidades = [];
					});

					this.detalle = filas
					this.phuyu_totales();
				}
			});
		},
		informacion_unidad: function(index,producto,val){
			var codunidad = this.detalle[index].codunidad;
			//console.log(codunidad)
            var codproducto = producto.codproducto;
            this.$http.post(url+"almacen/productos/informacion_item",{"codunidad": codunidad, "codproducto": codproducto}).then(function(data){
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
		phuyu_nueva_venta: function(){
			phuyu_sistema.phuyu_inicio(); $(".in").remove();
			this.$http.post(url+phuyu_controller+"/nuevo").then(function(data){
				$("#phuyu_sistema").empty().html(data.body);
			});
		},
		phuyu_atras: function(){
			phuyu_sistema.phuyu_modulo();
		},
		phuyu_addcliente: function(){
			$(".compose").removeClass("col-md-6").addClass("col-md-4");
			$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario",180); 
			this.$http.post(url+"ventas/clientes/nuevo_1").then(function(data){
				$("#phuyu_formulario").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			},function(){ 
				phuyu_sistema.phuyu_error_operacion(); 
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			});
		},
		phuyu_infocliente: function(flag=false){
			this.campos.codpersona = $("#codpersona").val();
			if(flag){
				if (this.campos.codpersona==2) {
					$("#cliente").removeAttr("readonly"); 
					$("#direccion").removeAttr("readonly");
				}else{
					$("#cliente").attr("readonly","true");
				}
			}else{
				this.$http.get(url+"ventas/clientes/infocliente/"+this.campos.codpersona).then(function(data){
					if (this.campos.codpersona==2) {
						$("#cliente").removeAttr("readonly"); $("#direccion").removeAttr("readonly");
					}else{
						$("#cliente").attr("readonly","true"); $("#direccion").removeAttr("readonly");
					}
					this.campos.cliente = data.body[0].razonsocial;
					this.codtipodocumento = data.body[0].coddocumentotipo; 
					this.campos.direccion = data.body[0].direccion;
				});
			}
        },

		/* DETALLE DE LA VENTA Y TOTALES */

		phuyu_codigobarra: function(){
			if (this.codigobarra!="") {
				this.$http.get(url+"almacen/productos/buscar_codigobarra/"+this.codigobarra).then(function(data){
					if (data.body.cantidad==0) {
						phuyu_sistema.phuyu_alerta("NO EXISTE CODIGO DE BARRA", "REGISTRA EL CODIGO DE BARRA", "error");
					}else{
						if (data.body.cantidad==1) {
							this.phuyu_additem(data.body.info[0],data.body.precio); this.codigobarra = "";
						}else{
							phuyu_sistema.phuyu_alerta("EL CODIGO DE BARRA EXISTE EN MÁS DE UN PRODUCTO", "REGISTRADO MAS DE UNA VEZ", "error");
						}
					}
				});
			}
		},
		phuyu_item: function(){
			$(".compose").removeClass("col-md-4").addClass("col-md-6");
			$(".compose").slideToggle(); $("#phuyu_tituloform").text("BUSCAR PRODUCTO"); 
			phuyu_sistema.phuyu_loader("phuyu_formulario",180); 

			this.$http.post(url+"almacen/productos/buscar/ventas").then(function(data){
				$("#phuyu_formulario").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			},function(){
				phuyu_sistema.phuyu_error(); 
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			});
		},
		phuyu_additem: function(producto,precio){
			var existe_item = [];
			if ($("#itemrepetir").val()==0) {
				var existe_item = this.detalle.filter(function(p){
				    if(p.codproducto == producto.codproducto && p.codunidad == producto.codunidad ){
				    	p.cantidad = p.cantidad + 1; return p;
				    };
				});
			}

		    if (existe_item.length==0 || $("#itemrepetir").val()==1) {
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

		    	producto.preciosinigv = producto.precio; producto.precio = precio; 
		    	producto.valorventa = producto.precio; producto.subtotal = producto.precio;
		    	
		    	producto.igv = 0; var porcentaje = 1;
				if (producto.afectacionigv==10) {
					var porcentaje = (1 + this.igvsunat) / 100;
					producto.preciosinigv = Number((producto.precio / porcentaje).toFixed(4));
					producto.valorventa = Number((producto.precio / porcentaje).toFixed(2));
					producto.igv = Number((producto.subtotal - producto.valorventa).toFixed(2));
				}
				
				producto.icbper = 0; producto.isc = 0;
				if (producto.afectoicbper==1) {
					producto.icbper = Number((1 * this.icbpersunat).toFixed(2));;
				}

				producto.control = 0;
				if (this.stockalmacen==1) {
					if (producto.controlstock==1) {
						producto.control = 1;
					}
				}

				this.detalle.push({
					codproducto: producto.codproducto, producto: producto.descripcion, codunidad: producto.codunidad,unidades: this.putunidades,
					unidad: producto.unidad, cantidad: 1, stock:producto.stock, control:producto.control,
					preciobruto: producto.preciosinigv, preciosinigv: producto.preciosinigv, precio: producto.precio,
					preciorefunitario: producto.precio, porcdescuento: 0, descuento: 0,
					codafectacionigv: producto.afectacionigv, igv: producto.igv, conicbper: producto.afectoicbper, icbper: producto.icbper,
					valorventa: producto.valorventa, subtotal:producto.subtotal, subtotal_tem:producto.subtotal, 
					descripcion:"", calcular: producto.calcular
				});
				this.phuyu_totales();
				this.putunidades = [];
		    }else{
		    	this.phuyu_calcular(existe_item[0]);
		    }
		},
		informacion_unidad: function(index,producto,val){
			var codunidad = this.detalle[index].codunidad;
			//console.log(codunidad)
            var codproducto = producto.codproducto;
            this.$http.post(url+"almacen/productos/informacion_item",{"codunidad": codunidad, "codproducto": codproducto}).then(function(data){
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
			this.detalle.splice(index,1); this.phuyu_totales();
			this.putunidades = [];
		},
		phuyu_itemdetalle: function(index,producto){
			this.item = producto; $("#modal_itemdetalle").modal('show');
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
				item.porcdescuento = Number((parseFloat(item.descuento) / parseFloat(item.preciobruto) * 100).toFixed(2));
				item.precio = Number((parseFloat(item.preciobruto) - parseFloat(item.descuento)).toFixed(4)); tipoprecio = 2;
			}
			if (tipoprecio==-2) {
				item.descuento = Number((parseFloat(item.preciobruto) * parseFloat(item.porcdescuento) / 100).toFixed(4));
				item.precio = Number((parseFloat(item.preciobruto) - parseFloat(item.descuento)).toFixed(4)); tipoprecio = 2;
			}
			if(tipoprecio==0){
				item.precio = Number((parseFloat(item.preciobruto) - parseFloat(item.descuento)).toFixed(4));
			}
			
			var descuento = item.descuento;
			if (item.descuento=="") {
				var descuento = 0;
			}
			
			if (tipoprecio==1) {
				item.precio = Number((parseFloat(item.preciosinigv) * parseFloat(porcentaje)).toFixed(4));
				item.preciobruto = Number((parseFloat(item.precio) + parseFloat(descuento)).toFixed(4));
			}
			if (tipoprecio==2) {
				item.preciosinigv = Number((parseFloat(item.precio) / parseFloat(porcentaje)).toFixed(4));
				item.preciobruto = Number((parseFloat(item.precio) + parseFloat(descuento)).toFixed(4));
			}

			item.icbper = 0;
			if (item.conicbper==1) {
				item.icbper = Number((parseFloat(item.cantidad) * this.icbpersunat).toFixed(2));
			}

			item.valorventa = Number((parseFloat(item.cantidad) * parseFloat(item.preciosinigv)).toFixed(2));
			item.subtotal = Number((parseFloat(item.cantidad) * parseFloat(item.precio)).toFixed(2));
			item.igv = Number((parseFloat(item.subtotal) - parseFloat(item.valorventa)).toFixed(2));
			this.phuyu_totales();
		},
		phuyu_itemcalcular_cerrar: function (item) {
			if (parseFloat(item.subtotal) < 0) {
				phuyu_sistema.phuyu_noti("EL SUBTOTAL DEBE SER MAYOR A CERO","REVISAR LOS CAMPOS DEL ITEM","danger"); return false;
			}
			$("#modal_itemdetalle").modal("hide");
		},
		phuyu_calcular: function(producto){
			var porcentaje = 1;
			if (producto.codafectacionigv==10) {
				var porcentaje = (1 + this.igvsunat) / 100;
			}
			producto.preciosinigv = Number((parseFloat(producto.precio) / porcentaje).toFixed(4));
			producto.preciobruto = Number((parseFloat(producto.precio) + parseFloat(producto.descuento)).toFixed(4));

			producto.valorventa = Number((parseFloat(producto.cantidad) * parseFloat(producto.preciosinigv)).toFixed(2));
			producto.subtotal = Number((parseFloat(producto.cantidad) * parseFloat(producto.precio)).toFixed(2));
			producto.igv = Number((parseFloat(producto.subtotal) - parseFloat(producto.valorventa)).toFixed(2));
			producto.icbper = 0;
			if (producto.conicbper==1) {
				producto.icbper = Number((parseFloat(producto.cantidad) * this.icbpersunat).toFixed(2));
			}
			this.phuyu_totales();
		},
		phuyu_subtotal: function(producto){
			// SI producto.calcular = 1 calcula cantidad, producto.calcular = 2 calcula precio //
			if (producto.calcular==1) {
				if (producto.precio!=0) {
					producto.cantidad = Number((producto.subtotal / producto.precio).toFixed(4));
				}
			}else{
				if (producto.cantidad!=0) {
					producto.precio = Number((producto.subtotal / producto.cantidad).toFixed(4));
				}
			}

			var porcentaje = 1;
			if (producto.codafectacionigv==10) {
				var porcentaje = (1 + this.igvsunat) / 100;
			}
			producto.preciosinigv = Number((producto.precio / porcentaje).toFixed(4));
			producto.preciobruto = Number((producto.precio + producto.descuento).toFixed(4));

			producto.valorventa = Number((producto.cantidad * producto.preciosinigv).toFixed(2));
			producto.igv = Number((producto.subtotal - producto.valorventa).toFixed(2));
			producto.icbper = 0;
			if (producto.conicbper==1) {
				producto.icbper = Number((producto.cantidad * this.icbpersunat).toFixed(2));
			}
			this.phuyu_totales();
		},
		phuyu_totales: function () {
			this.totales.bruto = 0.00; this.totales.descuentos = 0.00; this.totales.descglobal = 0.00;
			this.operaciones.gravadas = 0.00; this.operaciones.inafectas = 0.00; 
			this.operaciones.exoneradas = 0.00; this.operaciones.gratuitas = 0.00;
			this.totales.igv = 0.00; this.totales.isc = 0.00; this.totales.icbper = 0.00;
			this.totales.valorventa = 0.00; this.totales.subtotal = 0.00; this.totales.importe = 0.00;
			t = this;
			var detalle = this.detalle.filter(function(p){
				t.totales.bruto = Number((t.totales.bruto + (parseFloat(p.cantidad) * parseFloat(p.preciobruto)) ).toFixed(2));
				t.totales.descuentos = Number((t.totales.descuentos + (parseFloat(p.cantidad) * parseFloat(p.descuento)) ).toFixed(2));

				if (p.codafectacionigv==10) {
					t.operaciones.gravadas = Number((t.operaciones.gravadas + parseFloat(p.subtotal) - parseFloat(p.igv)).toFixed(2));
				}
				if (p.codafectacionigv==20) {
					t.operaciones.exoneradas = Number((t.operaciones.exoneradas + parseFloat(p.subtotal)).toFixed(2));
				}
				if (p.codafectacionigv==30) {
					t.operaciones.inafectas = Number((t.operaciones.inafectas + parseFloat(p.subtotal)).toFixed(2));
				}
				if (p.codafectacionigv==21) {
					t.operaciones.gratuitas = Number((t.operaciones.gratuitas + parseFloat(p.subtotal)).toFixed(2));
				}
                
				t.totales.igv = Number((t.totales.igv + parseFloat(p.igv)).toFixed(2));
				t.totales.icbper = Number((t.totales.icbper + parseFloat(p.icbper)).toFixed(2));

				t.totales.valorventa = Number((t.totales.valorventa + parseFloat(p.valorventa) ).toFixed(2));
				t.totales.subtotal = Number((t.totales.subtotal + parseFloat(p.subtotal) ).toFixed(2));
			});

			var subtotal_tem = this.operaciones.gravadas + this.operaciones.inafectas + this.operaciones.exoneradas + this.operaciones.gratuitas;
			this.totales.importe = Number((subtotal_tem + this.totales.igv + this.totales.icbper).toFixed(2));
		},

		phuyu_totales_total: function () {
			this.totales.bruto = 0.00; this.totales.descuentos = 0.00; this.totales.descglobal = 0.00;
			this.operaciones.gravadas = 0.00; this.operaciones.inafectas = 0.00; 
			this.operaciones.exoneradas = 0.00; this.operaciones.gratuitas = 0.00;
			this.totales.igv = 0.00; this.totales.isc = 0.00; this.totales.icbper = 0.00;
			this.totales.valorventa = 0.00; this.totales.subtotal = 0.00; this.totales.importe = 0.00;
			t = this;
			var detalle = this.detalle.filter(function(p){
				t.totales.bruto = Number((t.totales.bruto + parseFloat(p.subtotal) - parseFloat(p.igv) ).toFixed(2));
				t.totales.descuentos = Number((t.totales.descuentos + (parseFloat(p.cantidad) * parseFloat(p.descuento)) ).toFixed(2));

				if (p.codafectacionigv==10) {
					t.operaciones.gravadas = Number((t.operaciones.gravadas + parseFloat(p.subtotal) - parseFloat(p.igv)).toFixed(2));
				}
				if (p.codafectacionigv==20) {
					t.operaciones.exoneradas = Number((t.operaciones.exoneradas + parseFloat(p.subtotal)).toFixed(2));
				}
				if (p.codafectacionigv==30) {
					t.operaciones.inafectas = Number((t.operaciones.inafectas + parseFloat(p.subtotal)).toFixed(2));
				}
				if (p.codafectacionigv==21) {
					t.operaciones.gratuitas = Number((t.operaciones.gratuitas + parseFloat(p.subtotal)).toFixed(2));
				}
                
				t.totales.igv = Number((t.totales.igv + parseFloat(p.igv)).toFixed(2));
				t.totales.icbper = Number((t.totales.icbper + parseFloat(p.icbper)).toFixed(2));

				t.totales.valorventa = Number((t.totales.valorventa + parseFloat(p.valorventa) ).toFixed(2));
				t.totales.subtotal = Number((t.totales.subtotal + parseFloat(p.subtotal) ).toFixed(2));
			});

			var subtotal_tem = this.operaciones.gravadas + this.operaciones.inafectas + this.operaciones.exoneradas + this.operaciones.gratuitas;
			this.totales.importe = Number((subtotal_tem + this.totales.igv + this.totales.icbper).toFixed(2));
		},

		/* DATOS GENERALES DE LA VENTA */

		phuyu_guardar: function(){
			if (this.detalle.length==0) {
				phuyu_sistema.phuyu_noti("REGISTRAR UN PRODUCTO EN EL DETALLE", "REGISTRAR ITEM PARA EL PEDIDO","danger"); return false;
			}
			this.campos.fechacomprobante = $("#fechacomprobante").val();
			this.campos.fechakardex = $("#fechakardex").val();
			this.pagos.monto_efectivo = this.totales.importe;

			if (this.campos.condicionpago==2) {
				this.phuyu_condicionpago();
				$("#modal_cuotas").modal('show');
			}else{
				this.phuyu_pagar();
			}
		},

		/* PAGO DE LA VENTA */
		phuyu_series_referencia: function(){
			if (this.campos.codcomprobantetiporeferencia!=undefined) {
				this.estado = 1;
				this.$http.get(url+"caja/controlcajas/phuyu_seriescaja/"+this.campos.codcomprobantetiporeferencia).then(function(data){
					this.seriesreferencia = data.body.series; this.estado = 0;
					// this.campos.seriecomprobante = $("#serie").val(); this.phuyu_correlativo();
					this.campos.seriecomprobantereferencia = data.body.serie;
				});

				if (this.campos.codcomprobantetiporeferencia==10) {
					this.$http.get(url+"ventas/clientes/infocliente/"+this.campos.codpersona).then(function(data){
						this.codtipodocumento = data.body[0].coddocumentotipo;
					});
				}
			}
		},
		phuyu_correlativo_referencia: function(){
			if (this.campos.codcomprobantetiporeferencia!=undefined) {
				if (this.campos.seriecomprobantereferencia!="") {
					this.$http.get(url+"caja/controlcajas/phuyu_correlativo/"+this.campos.codcomprobantetiporeferencia+"/"+this.campos.seriecomprobantereferencia).then(function(data){
						this.campos.nroreferencia = data.body;
					});
				}
			}
		},

		phuyu_series: function(){
			if (this.campos.codcomprobantetipo!=undefined) {
				this.estado = 1;
				this.$http.get(url+"caja/controlcajas/phuyu_seriescaja/"+this.campos.codcomprobantetipo).then(function(data){
					this.series = data.body.series; this.estado = 0;
					// this.campos.seriecomprobante = $("#serie").val(); this.phuyu_correlativo();
					this.campos.seriecomprobante = data.body.serie;
				});

				if (this.campos.codcomprobantetipo==10) {
					this.$http.get(url+"ventas/clientes/infocliente/"+this.campos.codpersona).then(function(data){
						this.codtipodocumento = data.body[0].coddocumentotipo;
					});
				}
			}
		},
		phuyu_correlativo: function(){
			if (this.campos.codcomprobantetipo!=undefined) {
				if (this.campos.seriecomprobante!="") {
					this.$http.get(url+"caja/controlcajas/phuyu_correlativo/"+this.campos.codcomprobantetipo+"/"+this.campos.seriecomprobante).then(function(data){
						this.campos.nro = data.body;
					});
				}
			}
		},

		phuyu_pagotarjeta: function(){
			if (this.pagos.codtipopago_tarjeta==0) {
				this.pagos.monto_tarjeta = 0; this.pago.nrovoucher = "";
				$("#monto_tarjeta").attr("readonly","true"); $("#monto_tarjeta").removeAttr("required");
				$("#nrovoucher").attr("readonly","true"); $("#nrovoucher").removeAttr("required");
			}else{
				$("#monto_tarjeta").removeAttr("readonly"); $("#monto_tarjeta").attr("required","true");
				$("#nrovoucher").removeAttr("readonly"); $("#nrovoucher").attr("required","true");
			}
		},
		phuyu_vuelto: function(){
			this.pagos.vuelto_efectivo = Number((this.pagos.monto_efectivo - this.totales.importe).toFixed(2));
			if (this.pagos.vuelto_efectivo<=0) {
				this.pagos.vuelto_efectivo = 0;
			}
		},

		phuyu_condicionpago: function(){
			if (this.campos.condicionpago==2) {
				this.phuyu_cuotas(); this.campos.codconcepto = 15;
			}else{
				this.campos.codconcepto = 13;
			}
		},
		phuyu_cuotas: function(){
			var importe = Number((this.totales.importe/this.campos.nrocuotas).toFixed(1));
			var interes = Number(( (importe*(this.campos.tasainteres/100)*(this.campos.nrodias/30)) ).toFixed(1));
			var total = Number((importe + interes).toFixed(1));

    		var fecha = new Date();
    		this.totales.interes = Number(( (this.totales.importe*(this.campos.tasainteres/100)*(this.campos.nrodias/30)) ).toFixed(1));
			this.campos.totalcredito = Number(( parseFloat(this.totales.importe) + parseFloat(this.totales.interes) ).toFixed(1));
    		
			this.cuotas = []; var suma_importe = 0; var suma_total = 0;
			for (var i = 1; i <= this.campos.nrocuotas; i++) {
				if (this.campos.nrodias=="") {
					fecha.setDate(fecha.getDate() + 0);
				}else{
					fecha.setDate(fecha.getDate() + parseInt(this.campos.nrodias));
				}

				year = fecha.getFullYear(); month = String(fecha.getMonth() + 1); day = String(fecha.getDate());
				if (month.length < 2) month = "0"+month;
				if (day.length < 2) day = "0"+day;

				fechavence = year+"-"+month+"-"+day;

				if (this.campos.nrocuotas==i) {
					importe = Number(( this.totales.importe - parseFloat(suma_importe) ).toFixed(1));
					total = Number(( this.campos.totalcredito - parseFloat(suma_total) ).toFixed(1));
				}else{
					suma_importe = Number(( parseFloat(suma_importe) + parseFloat(importe) ).toFixed(1));
					suma_total = Number(( parseFloat(suma_total) + parseFloat(total) ).toFixed(1));
				}

				this.cuotas.push({
					"nrocuota":i,"fechavence":fechavence,"importe":importe,"interes":interes,"total":total
				});
			}
		},

		phuyu_pagar: function(){
			console.log(this.campos.codcomprobantetiporeferencia+' & '+this.codtipodocumento)
			if ((this.campos.codcomprobantetiporeferencia==10 || this.campos.codcomprobantetiporeferencia==25) && this.codtipodocumento!=4) {
				phuyu_sistema.phuyu_noti("PARA EMITIR UNA FACTURA", "DEBE SELECCIONAR UN CLIENTE CON RUC","danger"); return false;
			}

			if (this.campos.codcomprobantetiporeferencia==12 && this.codtipodocumento==4) {
				phuyu_sistema.phuyu_noti("ALTO! NO PUEDES EMITIR UNA BOLETA", "DEBE CAMBIAR A UN CLIENTE QUE NO TENGA RUC","danger"); return false;
			}

			if (parseFloat(this.totales.importe)>=700) {
				if (this.campos.codcomprobantetiporeferencia==12 && this.codtipodocumento==0) {
					phuyu_sistema.phuyu_noti("PARA EMITIR UNA BOLETA CON MONTO MAYOR A 700.00 SOLES","DEBE SELECCIONAR UN CLIENTE CON DNI o RUC","danger");
					return false;
				}
			}

			if (this.campos.condicionpago==1) {
				if (this.pagos.codtipopago_tarjeta==0) {
					if (parseFloat(this.pagos.monto_efectivo) < parseFloat(this.totales.importe)) {
						phuyu_sistema.phuyu_noti("EL IMPORTE DEBE SER MAYOR O IGUAL AL TOTAL DEL PEDIDO","FALTAN S/. "+
						Number(( parseFloat(this.totales.importe - this.pagos.monto_efectivo) ).toFixed(2)),"danger"); return false;
					}
				}else{
					var suma_importe = parseFloat(this.pagos.monto_efectivo) + parseFloat(this.pagos.monto_tarjeta);
					if (parseFloat(suma_importe)!=parseFloat(this.totales.importe)) {
						phuyu_sistema.phuyu_noti("LA SUMA DE LOS IMPORTES DEBE SER IGUAL AL TOTAL DEL PEDIDO","DIFERENCIA S/. "+
						Number(( parseFloat(this.totales.importe - suma_importe) ).toFixed(2)),"danger"); return false;
					}
				}
			}else{
				if (this.campos.codpersona==2) {
					phuyu_sistema.phuyu_noti("ATENCION USUARIO: EL SISTEMA NO PERMITE REGISTRAR UN CREDITO A CLIENTES VARIOS","","error");
					return false;
				}
			}
			
			this.estado = 1; $("#modal_cuotas").modal("hide"); phuyu_sistema.phuyu_inicio_guardar("GUARDANDO PEDIDO. . .");
			this.$http.post(url+phuyu_controller+"/guardar", {"campos":this.campos,"detalle":this.detalle,"cuotas":this.cuotas,"pagos":this.pagos,"totales":this.totales}).then(function(data){
				if (data.body=="e") {
					phuyu_sistema.phuyu_alerta("SESION DEL USUARIO TERMINADA","DEBE INICIAR SESION NUEVAMENTE","error");
				}else{
					if (data.body.estado==1) {
						phuyu_sistema.phuyu_noti("PEDIDO EDITADO CORRECTAMENTE","PEDIDO EDITADO EN EL SISTEMA","success");
					}else{
						phuyu_sistema.phuyu_alerta("ERROR AL EDITAR PEDIDO","ERROR DE RED","error");
					}
				}
				phuyu_sistema.phuyu_fin(); phuyu_sistema.phuyu_modulo();
			}, function(){
				phuyu_sistema.phuyu_alerta("ERROR AL EDITAR PEDIDO","ERROR DE RED","error");
				phuyu_sistema.phuyu_fin(); phuyu_sistema.phuyu_modulo();
			});
		},
		phuyu_imprimir: function(codkardex){
			if ($("#formato").val()=="ticket") {
				window.open(url+"facturacion/formato/ticket/"+codkardex,"_blank");
			}else{
				var phuyu_url = url+"facturacion/formato/formato_pedido/"+codkardex;
				$("#phuyu_pdf").attr("src",phuyu_url); $("#modal_reportes").modal("show");
			}

			/* if ($("#phuyu_formato").val()==0) {
				var phuyu_url = url+"facturacion/formato/a4/"+codkardex;
            	$("#phuyu_pdf").attr("src",phuyu_url); $("#modal_reportes").modal("show");
			}else{
				if ($("#phuyu_formato").val()==1) {
					var phuyu_url = url+"facturacion/formato/a5/"+codkardex;
            		$("#phuyu_pdf").attr("src",phuyu_url); $("#modal_reportes").modal("show");
				}else{
					window.open(url+"facturacion/formato/ticket/"+codkardex,"_blank");
				}
			} */
        },
        obtener_credito: function(codpedido){
        	this.$http.post(url+phuyu_controller+"/obtenercredito/"+codpedido).then(function(data){
				var datos = data.body;
				this.campos.nrodias = datos[0]["nrodias"];
				this.campos.nrocuotas = datos[0]["nrocuotas"];
				this.campos.tasainteres = datos[0]["tasainteres"];
				this.campos.codcreditopedido = datos[0]["codcreditopedido"];
			}, function(){
				phuyu_sistema.phuyu_alerta("ERROR AL OBTENER CUOTAS","ERROR DE RED","error");
			});
        }
	},
	created: function(){
		phuyu_sistema.phuyu_fin();this.phuyu_infocliente(true);this.phuyu_series_referencia();this.phuyu_detalle();
		if($("#condicionpagoref").val()==2){
			this.obtener_credito(phuyu_pedidos.registro);
		}
	}
});

document.addEventListener("keyup", buscar_f11, false);
function buscar_f11(e){
    var keyCode = e.keyCode;
    if(keyCode==122){
    	phuyu_operacion.phuyu_item();
    }
}