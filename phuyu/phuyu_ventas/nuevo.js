var phuyu_operacion = new Vue({
	el: "#phuyu_operacion",
	data: {
		afectacionigv : $("#afectacionigv").val(),
		estado:0, importetotalcredito:0,importecredito:0,interescredito:0, codigobarra: "",rubro:$("#rubro").val(),simbolo:"S/",codtipodocumento:0,codpedido: $("#codpedido").val(),codpersonaref:0,codproforma:$("#codproforma").val(),codimprimir:0,coddespachotipo:$("#coddespachotipo").val(),
		stockalmacen: $("#stockalmacen").val(), igvsunat:$("#igvsunat").val(), icbpersunat:$("#icbpersunat").val(), series:[], detalle:[], cuotas:[], putunidades:[],ventaconpedido:$("#ventaconpedido").val(),ventaconproforma:$("#ventaconproforma").val(),
		campos:{
			codkardex:0, codpersona:2, conleyendaamazonia:1,codmovimientotipo:20, codlote:0,codcomprobantetipo:$("#comprobante").val(),seriecomprobante:$("#serie").val(), nro:"",
			fechacomprobante:"", fechakardex:"", codconcepto:13, descripcion:"REGISTRO POR VENTA", cliente:"CLIENTES VARIOS", direccion:"-",
			codempleado:$("#codempleado").val(), codmoneda:1, tipocambio:0.00, codcentrocosto:0, nroplaca:"", retirar:true, afectacaja:true,nrodocumento:"",
			condicionpago:1, nrodias:30, nrocuotas:1, codcreditoconcepto:3, tasainteres:0, interes:0, totalcredito:0, porcdescuento:0.00, terminarpedido:true,
			creditoprogramado:1
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
		phuyu_tipocambio(){
			if (this.campos.codmoneda==1) {
				this.campos.tipocambio = 1;
				this.simbolo = 'S/'
			}else{
				this.campos.fechacomprobante = $("#fechacomprobante").val();
				this.$http.get(url+"caja/tipocambios/consulta/"+this.campos.fechacomprobante).then(function(data){
					this.campos.tipocambio = data.body;
				});
				this.$http.get(url+"caja/tipocambios/consultamoneda/"+this.campos.codmoneda).then(function(data){
					this.simbolo = data.body;
				});
			}
		},
		phuyu_venta: function(){
			swal({
				title: "SEGURO REGISTRAR NUEVA VENTA?",   
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
		phuyu_nueva_venta: function(){
			phuyu_sistema.phuyu_inicio(); $(".in").remove();
			phuyu_ventas.registro = 0;
			this.$http.post(url+phuyu_controller+"/nuevo").then(function(data){
				$("#phuyu_sistema").empty().html(data.body);
			});
		},
		phuyu_lista: function(){
			$("#modal_finventa").modal('hide');
			if ($('.modal-backdrop').is(':visible')) {
			  $('body').removeClass('modal-open'); 
			  $('.modal-backdrop').remove(); 
			};
			phuyu_sistema.phuyu_modulo();
		},
		phuyu_atras: function(){
			phuyu_sistema.phuyu_modulo();
		},
		phuyu_addcliente: function(){
			$(".compose").removeClass("col-md-6").addClass("col-md-4");
			$(".compose").slideToggle(); $("#phuyu_tituloform").text("CREAR CLIENTE");  phuyu_sistema.phuyu_loader("phuyu_formulario",180); 
			this.$http.post(url+"ventas/clientes/nuevo_1").then(function(data){
				$("#phuyu_formulario").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			},function(){ 
				phuyu_sistema.phuyu_error_operacion(); 
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			});
		},
		phuyu_searchpedido: function(){

			$(".compose").slideToggle();$("#phuyu_tituloform").text("BUSCAR PEDIDO");
			phuyu_sistema.phuyu_loader("phuyu_formulario",180); 
			this.$http.post(url+"ventas/pedidos/buscar").then(function(data){
				$("#phuyu_formulario").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			},function(){ 
				phuyu_sistema.phuyu_error_operacion(); 
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			});
		},
		phuyu_searchproforma: function(){
			$(".compose").slideToggle();$("#phuyu_tituloform").text("BUSCAR PROFORMA");
			phuyu_sistema.phuyu_loader("phuyu_formulario",180); 
			this.$http.post(url+"ventas/proformas/buscar").then(function(data){
				$("#phuyu_formulario").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			},function(){ 
				phuyu_sistema.phuyu_error_operacion(); 
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			});
		},
		phuyu_infocliente: function(){
			if(this.codpersonaref == 0){
				var cliente = $("#codpersona option:selected").text();
				//console.log($("#codpersona").val())
				this.campos.codpersona = $("#codpersona").val();
				this.$http.get(url+"ventas/clientes/infocliente/"+this.campos.codpersona).then(function(data){
					//console.log(this.campos.codpersona)
					if (this.campos.codpersona==2) {
						$("#cliente").removeAttr("readonly"); $("#direccion").removeAttr("readonly");
					}else{
						$("#cliente").attr("readonly","true"); $("#direccion").removeAttr("readonly");
						this.campos.direccion = data.body[0].direccion
						this.campos.cliente = data.body[0].razonsocial
					}
					this.codtipodocumento = data.body[0].coddocumentotipo; 
					this.campos.nrodocumento = data.body[0].documento;
				});
			}else{
				this.codpersonaref = this.campos.codpersona
				var cliente = $("#codpersona option:selected").text();
				this.$http.get(url+"ventas/clientes/infocliente/"+this.codpersonaref).then(function(data){
					if (this.codpersonaref==2) {
						$("#cliente").removeAttr("readonly"); $("#direccion").removeAttr("readonly");
					}else{
						$("#cliente").attr("readonly","true");
					}
					this.codtipodocumento = data.body[0].coddocumentotipo; 
					this.campos.nrodocumento = data.body[0].documento;
					this.campos.direccion = data.body[0].direccion;
					this.campos.cliente = data.body[0].razonsocial
				});
			}
        },

		/* DETALLE DE LA VENTA Y TOTALES */

		phuyu_codigobarra: function(){
			if (this.codigobarra!="") {
				this.$http.get(url+"almacen/productos/buscar_codigobarra/"+this.codigobarra).then(function(data){
					if (data.body.cantidad==0) {
						phuyu_sistema.phuyu_alerta("NO EXISTE CODIGO DE BARRA", "REGISTRA EL CODIGO DE BARRA", "danger");
					}else{
						if (data.body.cantidad==1) {
							this.phuyu_additem(data.body.info[0],data.body.precio); this.codigobarra = "";
						}else{
							phuyu_sistema.phuyu_alerta("EL CODIGO DE BARRA EXISTE EN MÁS DE UN PRODUCTO", "REGISTRADO MAS DE UNA VEZ", "danger");
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
		phuyu_addpedido: function(pedido){
            
            this.codpedido = pedido.codpedido
            this.codproforma = 0;
			$(".select2-selection__rendered").empty().removeAttr('title');
            $(".select2-selection__rendered").empty().append(pedido.cliente);
			this.campos.nrodocumento = pedido.documento
            this.campos.cliente = pedido.cliente
            this.campos.direccion = pedido.direccion
            this.campos.codpersona = parseInt(pedido.codpersona)
            this.campos.codcomprobantetipo = pedido.codcomprobantetiporeferencia
            this.campos.codempleado = pedido.codempleado;
            this.campos.condicionpago = pedido.condicionpago;
            this.totales.igv = pedido.igv;
            this.totales.bruto = pedido.importe;
            this.totales.descuentos = pedido.descuentos;
            if(pedido.condicionpago==2){
	            this.campos.nrodias = pedido.nrodias;
	            this.campos.nrocuotas = pedido.nrocuotas;
	            this.campos.tasainteres = parseFloat(pedido.tasainteres).toFixed(2);

	            this.cuotas = pedido.cuotas;
	            this.phuyu_cal();
	        }
            if(this.codpedido != ""){
            	this.$http.get(url+"ventas/pedidos/buscarproductos/"+this.codpedido).then(function(data){
            		//console.log(data.body)
					if (data.body.length==0) {
						phuyu_sistema.phuyu_alerta("NO EXISTE EL DETALLE DEL PEDIDO", "CORREGIR POR FAVOR", "danger");return;
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

						    var cantidad_faltante =  parseFloat(v.cantidad) - parseFloat(v.cantidadcomprobante);
					    	v.valorventa = parseFloat(v.preciosinigv * cantidad_faltante).toFixed(4); 
					    	v.subtotal = v.preciounitario * cantidad_faltante;

                             filas.push({codproducto: v.codproducto, producto: v.producto, codunidad: v.codunidad,unidades: this.putunidades,
					unidad: v.unidad, cantidad: cantidad_faltante, preciobruto: parseFloat(v.preciosinigv).toFixed(4), 
					preciosinigv: parseFloat(v.preciosinigv).toFixed(4), conicbper: v.conicbper, icbper: v.icbper,
					precio: parseFloat(v.preciounitario).toFixed(4),preciorefunitario: v.preciorefunitario, porcdescuento: v.porcdescuento, 
					descuento: v.descuento,codafectacionigv: v.codafectacionigv, igv: v.igv, 
					valorventa: v.valorventa, subtotal:v.subtotal, subtotal_tem:v.subtotal, 
					descripcion:v.descripcion,itempedido:v.item,stock: v.stock,control: v.control, calcular: v.calcular})
                             this.putunidades = [];
						});

						this.detalle = filas
						this.phuyu_totales();
					}
					this.phuyu_series(true,pedido.codpersona)
				});
            }

		},
		phuyu_addproforma: function(proforma){
            var existe_proforma = [];
            if(this.codproforma == proforma.codproforma){
            	phuyu_sistema.phuyu_noti("CUIDADO","LA PROFORMA YA FUE SELECCIONADO","danger"); return false;
            }
            $(".select2-selection__rendered").text(proforma.razonsocial); 
			this.codproforma = proforma.codproforma
            this.codpedido = 0;
            this.campos.cliente = proforma.razonsocial
            this.campos.direccion = proforma.direccion
            this.campos.codpersona = parseInt(proforma.codpersona)
            this.campos.codempleado = proforma.codempleado;
            this.campos.condicionpago = proforma.condicionpago;
            if(proforma.condicionpago==2){
	            this.campos.nrodias = proforma.nrodias;
	            this.campos.nrocuotas = proforma.nrocuotas;
	            this.campos.tasainteres = parseFloat(proforma.tasainteres).toFixed(2);
	        }
            if(this.codproforma != ""){
            	this.$http.get(url+"ventas/proformas/buscarproductos/"+this.codproforma).then(function(data){
            		//console.log(data.body)
					if (data.body.length==0) {
						phuyu_sistema.phuyu_alerta("NO EXISTE EL DETALLE DE LA PROFORMA", "CORREGIR POR FAVOR", "danger");return;
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

						    var cantidad_faltante =  parseFloat(v.cantidad) - parseFloat(v.cantidadcomprobante);
					    	v.valorventa = parseFloat(v.preciosinigv * cantidad_faltante).toFixed(4); 
					    	v.subtotal = v.preciounitario * cantidad_faltante;

                             filas.push({codproducto: v.codproducto, producto: v.producto, codunidad: v.codunidad,unidades: this.putunidades,
					unidad: v.unidad, cantidad: cantidad_faltante, preciobruto: parseFloat(v.preciosinigv).toFixed(2), 
					preciosinigv: parseFloat(v.preciosinigv).toFixed(2), conicbper: v.conicbper, icbper: parseFloat(v.icbper).toFixed(2),
					precio: parseFloat(v.preciounitario).toFixed(2),preciorefunitario: v.preciorefunitario, porcdescuento: v.porcdescuento, 
					descuento: v.descuento,codafectacionigv: v.codafectacionigv, igv: parseFloat(v.igv).toFixed(2), 
					valorventa: v.valorventa, subtotal:(v.subtotal).toFixed(2), subtotal_tem:v.subtotal, 
					descripcion:v.descripcion,itemproforma:v.item,stock: v.stock,control: v.control, calcular: v.calcular})

                             this.putunidades = [];
						});

						this.detalle = filas
						this.phuyu_totales();
					}
					this.phuyu_series(true,proforma.codpersona)
				});
            }

		},
		phuyu_additem: function(producto,precio){
			// console.log("controladadore ",phuyu_controller)
			 console.log("Productodesde nuevo de ventassss:", producto);
			// console.log("controlseries:", producto.controlarseries);
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
		    			producto.factor = factores[8];
		    		}
		    	}

		    	producto.preciooriginal = precio;

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

				if (producto.controlarseries == 1) {
				let serieYaExiste = this.detalle.some(detalle => 
					detalle.serie_seleccionada && 
					detalle.serie_seleccionada.id_serie == producto.serie_seleccionada.id_serie
				);
				
				if (serieYaExiste) {
					swal({
						title: "❌ ERROR", 
						text: "La serie " + producto.serie_seleccionada.serie_codigo + " ya está en la lista", 
						icon: "error",
						button: false,
						timer: 1500
					});
					return false;
				}
			}

				

				this.detalle.push({
					codproducto: producto.codproducto, 
					producto: producto.descripcion, 
					codunidad: producto.codunidad,
					unidades: this.putunidades,
					unidad: producto.unidad, 
					cantidad: 1, 
					controlarseries: producto.controlarseries,
					stock:producto.stock, 
					control:producto.control,
					factor: producto.factor,
					preciobruto: producto.preciosinigv,
					preciosinigv: producto.preciosinigv, 
					precio: producto.precio,
					preciorefunitario: producto.precio,
					preciocredito:producto.preciocredito,
					porcdescuento: 0, 
					descuento: 0,
					codafectacionigv: producto.afectacionigv,
					igv: producto.igv, 
					conicbper: producto.afectoicbper,
					icbper: producto.icbper,
					valorventa: producto.valorventa, 
					subtotal:producto.subtotal, subtotal_tem:producto.subtotal, 
				//	descripcion:"", 
					calcular: producto.calcular,
					preciooriginal:producto.preciooriginal,
					precioventa:producto.precioventa,
					descripcion: producto.controlarseries == 1 ? 'SERIE/CODIGO : ' + producto.serie_seleccionada.serie_codigo : producto.descripcion,
    				serie_seleccionada: producto.controlarseries == 1 ? producto.serie_seleccionada : null,
				});
				swal({
					title: "✓ PRODUCTO AGREGADO", 
					text: producto.descripcion + " se agregó correctamente a la lista", 
					icon: "success",
					button: false,
					timer: 1000
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
		    	producto.factor = data.body[0].factor;
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
			this.item = producto; 
			$("#modal_itemdetalle").modal('show');
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
				item.porcdescuento = Number((item.descuento / item.preciobruto * 100).toFixed(4));
				item.precio = Number((item.preciobruto - item.descuento).toFixed(4)); tipoprecio = 2;
			}
			if (tipoprecio==-2) {
				item.descuento = Number((item.preciobruto * item.porcdescuento / 100).toFixed(4));
				item.precio = Number((item.preciobruto - item.descuento).toFixed(4)); tipoprecio = 2;
			}
			if(tipoprecio==0){
				item.precio = Number((item.preciobruto - item.descuento).toFixed(4));
				item.preciosinigv = Number((item.precio / porcentaje).toFixed(4));
			}
			
			var descuento = item.descuento;
			if (item.descuento=="") {
				var descuento = 0;
			}
			
			if (tipoprecio==1) {
				item.precio = Number((parseFloat(item.preciosinigv) * porcentaje).toFixed(4));
				item.preciobruto = Number((parseFloat(item.precio) + parseFloat(descuento)).toFixed(4));
			}
			if (tipoprecio==2) {
				item.preciosinigv = Number((parseFloat(item.precio) / porcentaje).toFixed(4));
				item.preciobruto = Number((parseFloat(item.precio) + parseFloat(descuento)).toFixed(4));
			}

			item.icbper = 0;
			if (item.conicbper==1) {
				item.icbper = Number((parseFloat(item.cantidad) * this.icbpersunat).toFixed(4));
			}

			item.valorventa = Number((parseFloat(item.cantidad) * parseFloat(item.preciosinigv)).toFixed(4));
			item.subtotal = Number((parseFloat(item.cantidad) * parseFloat(item.precio)).toFixed(4));
			item.igv = Number((parseFloat(item.subtotal) - parseFloat(item.valorventa)).toFixed(4));
			this.phuyu_totales();
		},
		phuyu_itemcalcular_cerrar: function (item) {
			if (parseFloat(item.subtotal) < 0) {
				phuyu_sistema.phuyu_noti("EL SUBTOTAL DEBE SER MAYOR A CERO","REVISAR LOS CAMPOS DEL ITEM","danger"); return false;
			}
			$("#modal_itemdetalle").modal("hide");
		},
		phuyu_calcular: function(producto){
			producto.preciooriginal = producto.precio;
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
		phuyu_cal: function(){
			this.importetotalcredito = 0;this.importecredito=0;this.interescredito=0;
			var t = this;
			var cuotas = this.cuotas.filter(function(p){
				//console.log(p.total)
				t.importecredito = t.importecredito + parseFloat(p.importe);
				t.interescredito = t.interescredito + parseFloat(p.interes);
				t.importetotalcredito = t.importetotalcredito + parseFloat(p.total);
				//console.log(t.importetotalcredito)
			});

			t.importetotalcredito = parseFloat(t.importetotalcredito).toFixed(2);
			t.importecredito = parseFloat(t.importecredito).toFixed(2);
			t.interescredito = parseFloat(t.interescredito).toFixed(2);
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
				//console.log('ñpk')
				//console.log(p.preciobruto)
				t.totales.bruto = Number((t.totales.bruto + (parseFloat(p.cantidad) * parseFloat(p.preciobruto)) ).toFixed(2));
				//console.log('bruto: '+p.preciobruto)
				t.totales.descuentos = Number((t.totales.descuentos + (parseFloat(p.cantidad) * parseFloat(p.descuento)) ).toFixed(2));

				if (p.codafectacionigv==10) {
					t.operaciones.gravadas = Number((t.operaciones.gravadas + parseFloat(p.subtotal) - parseFloat(p.igv)).toFixed(2));
				}
				if (p.codafectacionigv==20) {
					//console.log(p.subtotal)
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
			//console.log(subtotal_tem)
			this.totales.importe = Number((subtotal_tem + this.totales.igv + this.totales.icbper).toFixed(2));
		},

		/* DATOS GENERALES DE LA VENTA */

		phuyu_guardar: function(){

			console.log(this.campos.codcomprobantetipo);
			console.log(this.codtipodocumento);

			if (this.detalle.length==0) {
				phuyu_sistema.phuyu_noti("REGISTRAR UN PRODUCTO EN EL DETALLE", "REGISTRAR ITEM PARA LA VENTA","danger"); return false;
			}

			if ((this.campos.codcomprobantetipo==10 || this.campos.codcomprobantetipo==25) && this.codtipodocumento!=4) {
				phuyu_sistema.phuyu_noti("PARA EMITIR UNA FACTURA", "DEBE SELECCIONAR UN CLIENTE CON RUC","danger"); return false;
			}

			if (this.campos.codcomprobantetipo==12 && this.codtipodocumento==4) {
				phuyu_sistema.phuyu_noti("ALTO! NO PUEDE EMITIR LA BOLETA", "DEBE CAMBIAR DE CLIENTE QUE NO TENGA RUC","danger"); return false;
			}

			if (parseFloat(this.totales.importe)>=700) {
				if ((this.campos.codcomprobantetipo==12 || this.campos.codcomprobantetipo==26) && this.codtipodocumento==0) {
					phuyu_sistema.phuyu_noti("PARA EMITIR UNA BOLETA CON MONTO MAYOR A 700.00 SOLES","DEBE SELECCIONAR UN CLIENTE CON DNI","danger");
					return false;
				}
			}
			this.campos.fechacomprobante = $("#fechacomprobante").val();
			this.campos.fechakardex = $("#fechakardex").val();
			this.pagos.monto_efectivo = this.totales.importe;

			this.phuyu_vuelto(); this.phuyu_lineascredito(); this.calcular_credito();
			$("#modal_finventa").modal('show');
		},

		/* PAGO DE LA VENTA */

		phuyu_series: function($pedido = false, $codpersona = ""){
			if (this.campos.codcomprobantetipo!=undefined) {
				this.estado = 1;
				this.$http.get(url+"caja/controlcajas/phuyu_seriescaja/"+this.campos.codcomprobantetipo).then(function(data){
					this.series = data.body.series; this.estado = 0;
					this.campos.seriecomprobante = data.body.serie; this.phuyu_correlativo();
					//this.campos.seriecomprobante = $("#serie").val(); this.phuyu_correlativo();
				});

                if($pedido){
                	//console.log('lkl')
                	this.codpersonaref = $codpersona
                	this.phuyu_infocliente()
                }else{
					if (this.campos.codcomprobantetipo==10) {
						this.$http.get(url+"ventas/clientes/infocliente/"+this.campos.codpersona).then(function(data){
							this.codtipodocumento = data.body[0].coddocumentotipo;
						});
					}
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
				this.pagos.monto_efectivo = this.totales.importe;
				this.pagos.monto_tarjeta = 0; this.pagos.nrovoucher = "";
				$("#monto_tarjeta").attr("readonly","true"); $("#monto_tarjeta").removeAttr("required");
				$("#nrovoucher").attr("readonly","true"); $("#nrovoucher").removeAttr("required");
			}else{
				$("#monto_tarjeta").removeAttr("readonly"); $("#monto_tarjeta").attr("required","true");
				$("#nrovoucher").removeAttr("readonly"); $("#nrovoucher").attr("required","true");
			}
		},
		phuyu_vuelto: function(flag){
			if(flag==1){
				this.pagos.monto_efectivo = parseFloat(this.totales.importe) - parseFloat(this.pagos.monto_tarjeta);
				if(this.pagos.monto_efectivo <= 0){
					this.pagos.monto_efectivo = 0;
				}
			}
			if(this.pagos.monto_efectivo > 0){
				this.pagos.vuelto_efectivo = Number(((parseFloat(this.pagos.monto_efectivo) + parseFloat(this.pagos.monto_tarjeta)) - this.totales.importe).toFixed(2));
				if (this.pagos.vuelto_efectivo<=0) {
					this.pagos.vuelto_efectivo = 0;
				}
			}else{
				this.pagos.vuelto_efectivo = 0;
			}
		},

		phuyu_condicionpago: function(){
			if (this.campos.condicionpago==2) {
				this.phuyu_lineascredito(); this.phuyu_cuotas(); this.campos.codconcepto = 15;
			}else{
				this.campos.codconcepto = 13;
			}

			//this.phuyu_cambiarprecio(this.campos.condicionpago)
		},
		phuyu_cambiarprecio: function(condicionpago){
			var t = this;
			var detalle = this.detalle.filter(function(p){
				if(condicionpago==2){
					p.precio = p.preciocredito;
				}else{
					p.precio = p.precioventa;
				}
				t.phuyu_calcular(p)
			})
		},
		phuyu_lineascredito: function(){
			if (this.campos.codpersona!="" || this.campos.codpersona!=2) {
				this.$http.get(url+"ventas/lineascredito/phuyu_lineascredito/"+this.campos.codpersona).then(function(data){
					$("#codlote").empty().html(data.body);
					this.campos.codlote = $("#codlote").val()
				});
			}
		},
		phuyu_lineascreditodirecto: function(){
			if (this.campos.codpersona=="" || this.campos.codpersona==2) {
				phuyu_sistema.phuyu_noti("ATENCION USUARIO: PARA REALIZAR UNA NUEVA LINEA DE CREDITO DEBE SELECCIONAR UN CLIENTE","","danger");
					return false;
			}
			swal({
				title: "DESEA AGREGAR UNA LINEA DE CREDITO DIRECTO?",   
				text: "USTED ESTA POR REALIZAR EL PROCESO DE LINEA DE CREDITO DIRECTO", 
				icon: "warning",
				dangerMode: true,
				buttons: ["CANCELAR", "SI, AGREGAR"],
			}).then((willDelete) => {
				if (willDelete) {
					this.$http.post(url+"ventas/lineascredito/guardarlineascreditodirecto",{"codpersona":this.campos.codpersona}).then(function(data){
						if (data.body==1) {
							phuyu_sistema.phuyu_noti("LINEA DE CREDITO AGREGADO CORRECTAMENTE", "UN REGISTRO AGREGADO EN EL SISTEMA","success");
						}else{
							phuyu_sistema.phuyu_noti("OCURRIO UN ERROR !!!", "SE PERDIÓ LA CONEXION !!! LO SENTIMOS","danger");
						}
						this.phuyu_lineascredito();
					}, function(){
						alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
					});
				}
			});
		},
		phuyu_cuotas: function(){
			var importe = Number((this.totales.importe/this.campos.nrocuotas).toFixed(2));
			var interes = Number(( (importe*(this.campos.tasainteres/100)*(this.campos.nrodias/30)) ).toFixed(2));
			var total = Number((importe + interes).toFixed(1));

    		var fecha = new Date();
    		this.totales.interes = Number(( (this.totales.importe*(this.campos.tasainteres/100)*(this.campos.nrodias/30)) ).toFixed(2));
			this.campos.totalcredito = Number(( parseFloat(this.totales.importe) + parseFloat(this.totales.interes) ).toFixed(2));
    		
			this.cuotas = []; var suma_importe = 0; var suma_total = 0;this.importetotalcredito = 0;
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
					importe = Number(( this.totales.importe - parseFloat(suma_importe) ).toFixed(2));
					total = Number(( this.campos.totalcredito - parseFloat(suma_total) ).toFixed(2));
				}else{
					suma_importe = Number(( parseFloat(suma_importe) + parseFloat(importe) ).toFixed(2));
					suma_total = Number(( parseFloat(suma_total) + parseFloat(total) ).toFixed(2));
				}
				this.importetotalcredito = (this.importetotalcredito + total);
				this.cuotas.push({
					"nrocuota":i,"fechavence":fechavence,"nroletra":"","nrounicodepago":"","importe":importe,"interes":interes,"total":total
				});
			}

			this.importetotalcredito = parseFloat(this.importetotalcredito).toFixed(2);
		},
		calcular_credito: function(){
			var importe = Number((this.totales.importe/this.campos.nrocuotas).toFixed(2));

			var interes = Number(( (importe*(this.campos.tasainteres/100)*(this.campos.nrodias/30)) ).toFixed(2));
    		this.totales.interes = Number(( (this.totales.importe*(this.campos.tasainteres/100)*(this.campos.nrodias/30)) ).toFixed(2));
			this.campos.totalcredito = Number(( parseFloat(this.totales.importe) + parseFloat(this.totales.interes) ).toFixed(2));
			this.importetotalcredito = 0;
			var t = this;
			var l = this.cuotas.length; i = 1;
			var suma_importe = 0; var suma_total = 0;
			var cuotas = this.cuotas.filter(function(p){
				p.interes = interes;
				p.total = Number((parseFloat(p.importe) + parseFloat(p.interes)).toFixed(2));
				if(l==i){
					p.importe = Number(( t.totales.importe - parseFloat(suma_importe) ).toFixed(2));
					p.total = Number(( t.campos.totalcredito - parseFloat(suma_total) ).toFixed(2));
				}else{
					suma_importe = Number(( parseFloat(suma_importe) + parseFloat(p.importe) ).toFixed(2));
					suma_total = Number(( parseFloat(suma_total) + parseFloat(p.total) ).toFixed(2));
				}
				t.importetotalcredito = t.importetotalcredito + p.total;
				i++
			});

			this.importetotalcredito = parseFloat(this.importetotalcredito).toFixed(2);
		},
		phuyu_pagar: function(){

			if (this.campos.condicionpago==1) {
				if (this.pagos.codtipopago_tarjeta==0) {
					if (parseFloat(this.pagos.monto_efectivo) < parseFloat(this.totales.importe)) {
						phuyu_sistema.phuyu_noti("EL IMPORTE DEBE SER MAYOR O IGUAL AL TOTAL DE LA VENTA","FALTAN S/. "+
						Number(( parseFloat(this.totales.importe - this.pagos.monto_efectivo) ).toFixed(2)),"danger"); return false;
					}
				}else{
					var suma_importe = parseFloat(this.pagos.monto_efectivo) + parseFloat(this.pagos.monto_tarjeta);
					if(this.pagos.monto_efectivo==0){
						if (parseFloat(suma_importe)!=parseFloat(this.totales.importe)) {
							phuyu_sistema.phuyu_noti("LA SUMA DE LOS IMPORTES DEBE SER IGUAL AL TOTAL DE LA VENTA","DIFERENCIA S/. "+
							Number(( parseFloat(this.totales.importe - suma_importe) ).toFixed(2)),"danger"); return false;
						}
					}else{
						if (parseFloat(suma_importe)<parseFloat(this.totales.importe)) {
							phuyu_sistema.phuyu_noti("LA SUMA DE LOS IMPORTES DEBE SER IGUAL AL TOTAL DE LA VENTA","DIFERENCIA S/. "+
							Number(( parseFloat(this.totales.importe - suma_importe) ).toFixed(2)),"danger"); return false;
						}
					}
					
				}
			}else{
				this.campos.codlote = $("#codlote").val()
				if (this.campos.codpersona==2) {
					phuyu_sistema.phuyu_noti("ATENCION USUARIO: EL SISTEMA NO PERMITE REGISTRAR UN CREDITO A CLIENTES VARIOS","","danger");
					return false;
				}
				if(this.campos.codlote==0 && this.rubro==6){
					phuyu_sistema.phuyu_noti("ATENCION USUARIO: LA VENTA NO SE PUEDE REALIZAR PORQUE EL CLIENTE SELECCIONADO NO CUENTA CON UNA LINEA DE CREDITO VÁLIDA","","danger");
					return false;
				}
			}

			if($("#conleyendaamazonia").is(":checked")){
				this.campos.conleyendaamazonia = 1;
			}else{
				this.campos.conleyendaamazonia = 0;
			}

			if($("#retirar").is(':checked')){
				this.campos.retirar = 1;
			}else{
				this.campos.retirar = 0;
			}
			
			this.estado = 1;
			this.$http.post(url+phuyu_controller+"/guardar", {"codpedido":this.codpedido,"codproforma":this.codproforma,"codpersonapedido":this.codpersonaref,"campos":this.campos,"detalle":this.detalle,"cuotas":this.cuotas,"pagos":this.pagos,"totales":this.totales}).then(function(data){
				if (data.body=="e") {
					phuyu_sistema.phuyu_alerta("SESION DEL USUARIO TERMINADA","DEBE INICIAR SESION NUEVAMENTE","error");
				}else{
					if (data.body.estado==1) {
						this.codimprimir = data.body.codkardex
						$("#modal_finventa").modal('hide');
						this.phuyu_imprimir(data.body.codkardex)
						if (this.campos.codcomprobantetipo == 10) {
							var codoficial = '01';
							this.phuyu_enviar_comprobante(data.body.codkardex,codoficial);
						}
						this.phuyu_nueva_venta();
					}else{
						if(data.body.estado==0){
							$("#modal_finventa").modal('hide');
							var informacion = data.body.informacion;
							var mensaje = '';
							$.each(informacion.producto,function(indice, elemento) {
							  mensaje += '* '+elemento+': '+informacion.stock[indice]+' '+informacion.unidad[indice]+'\n';
							});
                           phuyu_sistema.phuyu_alerta("PRODUCTOS SIN STOCK, STOCK ACTUAL DE LOS PRODUCTOS:",mensaje,"error");
                           this.estado = 0;
						}else{
						   phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR VENTA","ERROR DE RED","error");
						}
					}
				}
				phuyu_sistema.phuyu_fin();
			}, function(){
				phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR VENTA","ERROR DE RED","error");
				phuyu_sistema.phuyu_fin();
			});
		},
		phuyu_imprimir: function(codkardex){
			/*$(".botones").removeClass('btn-danger');
			if ($("#formato").val()=="ticket") {
				$(".ticket").removeClass('btn-default');
				$(".ticket").addClass('btn-danger');
				window.open(url+"facturacion/formato/ticket/"+codkardex,"_blank");
			}else{
				if($("#formato").val()=="a5"){
					$(".a5").removeClass('btn-default');
				    $(".a5").addClass('btn-danger');
				}else{
					$(".a4").removeClass('btn-default');
				    $(".a4").addClass('btn-danger');
				}
				var phuyu_url = url+"facturacion/formato/"+$("#formato").val()+"/"+codkardex;
				$("#phuyu_pdf").attr("src",phuyu_url);
			}*/

			if ($("#formato").val()=='a4') {
				window.open(url+"facturacion/formato/a4/"+codkardex,"_blank");
			}else{
				if ($("#formato").val()=='a5') {
					window.open(url+"facturacion/formato/a5/"+codkardex,"_blank");
				}else{
					window.open(url+"facturacion/formato/ticket/"+codkardex,"_blank");
				}
			}
        },
        phuyu_imprimir2: function(formato){
            $(".botones").removeClass('btn-danger');
            $(".botones").addClass('btn-default');
            $("."+formato).addClass('btn-danger');
            if (formato=="ticket") {
                window.open(url+"facturacion/formato/ticket/"+this.codimprimir,"_blank");
                $("#phuyu_pdf").attr("src","");
            }else{
	            var phuyu_url = url+"facturacion/formato/"+formato+"/"+this.codimprimir;
				$("#phuyu_pdf").attr("src",phuyu_url);
            }
        },
        phuyu_enviar_comprobante: function(codkardex,codoficial){
            this.$http.get(url+"facturacion/facturacion/comprobantes_enviar/"+codkardex+"/"+codoficial).then(function(data){
				if (data.body=="e") {
					phuyu_sistema.phuyu_alerta("SESION DEL USUARIO TERMINADA","DEBE INICIAR SESION NUEVAMENTE","error");
				}else{
					phuyu_sistema.phuyu_noti("ATENCION USUARIO:",data.body.mensaje,data.body.alerta);
				}
			}, function(){
				phuyu_sistema.phuyu_alerta("NO SE PUEDE ENVIAR EL COMPROBANTE","SIN CONEXION DE INTERNET","error");
				$("#"+codkardex).removeAttr("disabled"); phuyu_sistema.phuyu_fin(); 				 
			});
        },
        resetear_formulario: function(pedido = false){
        	if(pedido){
        		$(".terminarpedido").show()
        	}else{
        		$(".terminarpedido").hide()
        	}
        	/*if(pedido){
               $(".selectpicker").attr('disabled',pedido)
        	}else{
        	   $(".selectpicker").removeAttr('disabled')
        	   $(".selectpicker").removeClass('disabled')
        	   var len = $(".selectpicker").siblings('button').removeClass('disabled')

        	}*/
            this.detalle = []
            this.phuyu_totales();
            this.codpersonaref = 0;
            this.codpedido = 0;
            this.codproforma = 0;
            phuyu_sistema.phuyu_fin_flavio();
        },
        phuyu_pedido: function(){
			phuyu_sistema.phuyu_inicio()
        	var pedido = false
			if ($("#ventac").is(':checked')) {
				$(".btnpedido").show()
				$(".btnproforma").hide()
				pedido = true
			}
			else if ($("#ventap").is(':checked')) {
				$(".btnproforma").show()
				$(".btnpedido").hide()
				pedido = true
			}else{
				$(".btnpedido").hide()
				$(".btnproforma").hide()
			}
			this.resetear_formulario(pedido)
		},
		phuyu_clonar: function(){
			this.titulo = "NUEVA COMPRA";
			this.$http.post(url+phuyu_controller+"/clonar",{"codregistro":phuyu_ventas.registro}).then(function(data){
				var socio = eval(data.body.socio);
				
				$(".select2-selection__rendered").empty().removeAttr('title');
				this.campos.codpersona = socio[0]["codpersona"];
				if(socio[0]["codpersona"]==2){
					$("#codpersona").attr('disabled',true);
					$("#acv").attr('checked',true);
				}else{
					$("#codpersona").removeAttr('disabled');
					$("#acv").removeAttr('checked');
					$(".select2-selection__rendered").empty().append(socio[0]["razonsocial"]); 
				}
				this.campos.nrodocumento = socio[0]["documento"];
				this.codtipodocumento = socio[0]["coddocumentotipo"];
				this.campos.cliente = socio[0]["razonsocial"];
				this.campos.direccion = data.body.campos[0].direccion;
				this.campos.condicionpago = data.body.campos[0].condicionpago;

				if(data.body.campos[0].condicionpago==2){
					this.campos.tasainteres = data.body.campos[0].tasainteres;
					this.campos.nrodias = data.body.campos[0].nrodias;
					this.campos.nrocuotas = data.body.campos[0].nrocuotas;

					this.cuotas = data.body.cuotas;
					this.phuyu_cal();
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
				this.phuyu_series();
				this.phuyu_totales();
				phuyu_sistema.phuyu_fin();
			});
		},
		activar_cvarios: function(){
			if($("#acv").is(':checked')){
				$("#codpersona").attr('disabled',true);
				$("#codpersona").removeAttr('required');
				this.campos.codpersona = 2;
				this.campos.cliente = 'CLIENTES VARIOS';
				this.campos.direccion = '-';
				this.codtipodocumento = 0;
				this.campos.nrodocumento = "";
				$("#cliente").removeAttr("readonly");
				$(".select2-selection__rendered").empty();
			}else{
				$("#codpersona").removeAttr('disabled');
				$("#codpersona").attr('required',true);
			}
		}
	},
	created: function(){
		if (parseInt(phuyu_ventas.registro)!=0) {
			this.phuyu_clonar();
		}else{
			this.phuyu_series();this.phuyu_tipocambio(); 
			this.activar_cvarios();phuyu_sistema.phuyu_fin(); 
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