var phuyu_operacion = new Vue({
	el: "#phuyu_operacion",
	data: {
		estado:0, 
    stockalmacen: $("#stockalmacen").val(), 
    igvsunat:$("#igvsunat").val(), 
    icbpersunat:$("#icbpersunat").val(), 
		rubro:0, series:[], cuotas: [], mesas:[], detalle: [], atender: [], atendidos: [], modoCambioMesa:false,
		campos:{
			codambiente: $("#codambiente").val(), 
      conleyendaamazonia:1,creditoprogramado:1, 
      codlote:2, 
      codmesa: "0", 
      mesa:"...", 
      codpedido:0, 
      pedidonuevo:1, 
      tipopedido:0, 
			codcomprobante:$("#comprobante").val(), 
      fechapedido: $("#fechapedido").val(),
			codkardex:0, 
      codpersona:2, 
      codmovimientotipo:20, 
      codcomprobantetipo:$("#comprobante").val(),
      seriecomprobante:$("#serie").val(), nro:"",
			fechacomprobante:$("#fechapedido").val(), 
      fechakardex:$("#fechapedido").val(), 
      codconcepto:13, 
      descripcion:"REGISTRO POR VENTA", 
      cliente:"CLIENTES VARIOS",
      direccion:"-",
			codempleado:0, 
      codmoneda:1, 
      tipocambio:0.00,
      codcentrocosto:0, 
      nroplaca:"", 
      retirar:true, afectacaja:true,
			condicionpago:1, 
      nrodias:30, nrocuotas:1, codcreditoconcepto:3, tasainteres:0, interes:0, totalcredito:0, porcdescuento:0.00
		},
		pagos:{
			codtipopago_efectivo:1, monto_efectivo:0, vuelto_efectivo:0, codtipopago_tarjeta:0, monto_tarjeta:0, nrovoucher:""
		},
		operaciones:{
			gravadas:0.00, exoneradas:0.00, inafectas:0.00, gratuitas:0.00
		},
		item:{
			producto:"", unidad:"", cantidad:0, preciobruto:0, descuento:0, porcdescuento:0, preciosinigv:0, precio:0, 
			codafectacionigv:"", igv:0, valorventa:0, conicbper: 0, icbper:0, subtotal:0, descripcion:""
		},
		totales:{
			flete:0.00, gastos:0.00, bruto:0.00, descuentos:0.00, descglobal:0.00, valorventa:0.00, igv:0.00, isc:0.00, icbper:0.00, 
			subtotal:0.00, importe:0.00
		}
	},
	methods: {
		phuyu_mesas: function(){
			this.$http.post(url+"restaurante/mesas/mesas_ambiente/"+this.campos.codambiente).then(function(data){
				this.mesas = data.body;
			});
		},
		phuyu_pedido: function(mesa){
			if (this.modoCambioMesa) {
				this.confirmar_cambio_mesa(mesa);
				return false;
			}

			this.campos.codmesa = mesa.codmesa; 
			this.campos.mesa = mesa.nromesa;

			this.$http.post(url+"ventas/pedidos/phuyu_pedido",{"codmesa":this.campos.codmesa}).then(function(data){

				this.campos.codpedido = data.body.codpedido; 
				this.campos.pedidonuevo = data.body.pedidonuevo;
				// data.body.pedidonuevo=1 		SIN DETALLE - PEDIDO NUEVO //
				if (data.body.pedidonuevo==0) {
					this.totales.valorventa = Number((parseFloat(data.body.pedido[0].valorventa) ).toFixed(2));
					this.totales.descglobal = Number((parseFloat(data.body.pedido[0].descglobal) ).toFixed(2));
					this.totales.igv = Number((parseFloat(data.body.pedido[0].igv) ).toFixed(2));
					this.totales.importe = Number((parseFloat(data.body.pedido[0].importe) ).toFixed(2));

					this.campos.codcomprobante = parseInt(data.body.pedido[0].codcomprobantetipo);
					this.campos.codempleado = parseInt(data.body.pedido[0].codempleado);
				}
				this.detalle = data.body.detalle;
			},function(){
				phuyu_sistema.phuyu_alerta("SIN CONEXION DE INTERNET", "ERROR DE RED","error");
			});
		},
		cambiar_mesa: function(){
			if (this.campos.pedidonuevo==1) {
				phuyu_sistema.phuyu_noti("DEBE SELECCIONAR UNA MESA CON PEDIDO","PARA CAMBIAR DE MESA","error");
			}else{
				this.modoCambioMesa = true;
				phuyu_sistema.phuyu_noti("SELECCIONE UNA MESA LIBRE", "PARA MOVER EL PEDIDO 000" + this.campos.codpedido, "info");
			}
		},
		confirmar_cambio_mesa: function(mesa){
			if (mesa.codmesa == this.campos.codmesa) {
				this.modoCambioMesa = false;
				phuyu_sistema.phuyu_noti("CAMBIO DE MESA CANCELADO", "", "info");
				return false;
			}

			swal({
				title: "CAMBIAR MESA ?",
				text: "Mover pedido 000" + this.campos.codpedido + " a la mesa " + mesa.nromesa,
				icon: "warning",
				dangerMode: true,
				buttons: ["CANCELAR", "SI, CAMBIAR"],
			}).then((confirmado) => {
				if (!confirmado) {
					this.modoCambioMesa = false;
					return false;
				}

				this.estado = 1;
				this.$http.post(url+"ventas/pedidos/cambiar_mesa", {
					codpedido: this.campos.codpedido,
					codmesa_origen: this.campos.codmesa,
					codmesa_destino: mesa.codmesa
				}).then(function(data){
					this.estado = 0;
					this.modoCambioMesa = false;

					if (data.body.estado == 1) {
						phuyu_sistema.phuyu_noti(data.body.mensaje, "MESA " + mesa.nromesa, "success");
						this.phuyu_mesas();
						this.phuyu_pedido(mesa);
					}else{
						phuyu_sistema.phuyu_noti(data.body.mensaje || "NO SE PUDO CAMBIAR LA MESA", "", "error");
					}
				}, function(){
					this.estado = 0;
					this.modoCambioMesa = false;
					phuyu_sistema.phuyu_alerta("ERROR AL CAMBIAR DE MESA", "ERROR DE RED", "error");
				});
			});
		},

		phuyu_producto: function(codlinea){
			// phuyu_sistema.phuyu_loader("phuyu_restaurante",180);

			this.$http.post(url+"almacen/productos/restobar/"+codlinea).then(function(data){
				$("#phuyu_restaurante").empty().html(data.body);
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
			});
		},
		phuyu_controla_stock: function(producto){
			return parseInt(this.stockalmacen) === 1 && parseInt(producto.controlstock) === 1;
		},
		phuyu_stock_item: function(producto){
			if (producto.stockdisponible!=undefined) {
				return parseFloat(producto.stockdisponible) || 0;
			}

			return parseFloat(producto.stock) || 0;
		},
		phuyu_alerta_stock: function(producto, stock){
			var nombre = producto.descripcion || producto.producto || "PRODUCTO";
			var stockTexto = Number(stock || 0).toFixed(0);
			phuyu_sistema.phuyu_alerta(
				"STOCK INSUFICIENTE",
				nombre + "\nDisponible: " + stockTexto + " UND",
				"error"
			);
		},
		phuyu_alerta_stock_excedido: function(producto, stock, solicitado){
			var nombre = producto.descripcion || producto.producto || "PRODUCTO";
			phuyu_sistema.phuyu_alerta(
				"STOCK INSUFICIENTE",
				nombre + "\nDisponible: " + Number(stock || 0).toFixed(0) + " UND\nSolicitado: " + Number(solicitado || 0).toFixed(0) + " UND",
				"error"
			);
		},
		phuyu_cantidad_producto_detalle: function(producto, omitir){
			var cantidad = 0;
			for (var i = 0; i < this.detalle.length; i++) {
				var item = this.detalle[i];
				if (item === omitir) {
					continue;
				}
				if (item.codproducto == producto.codproducto && item.codunidad == producto.codunidad) {
					cantidad = cantidad + (parseFloat(item.cantidad) || 0);
				}
			}

			return cantidad;
		},
		phuyu_additem: function(producto, precio){
			if (parseInt(this.campos.codmesa || 0) <= 0) {
				phuyu_sistema.phuyu_noti("SELECCIONE UNA MESA", "ANTES DE AGREGAR PRODUCTOS AL PEDIDO","error");
				return false;
			}

			producto.control = this.phuyu_controla_stock(producto) ? 1 : 0;

			if (producto.control==1 && this.phuyu_stock_item(producto) <= 0) {
				this.phuyu_alerta_stock(producto, 0);
				return false;
			}

			var existe_item = [];
			if ($("#itemrepetir").val()==0) {
				existe_item = this.detalle.filter(function(p){
				    return p.codproducto == producto.codproducto && p.codunidad == producto.codunidad;
				});

				if (existe_item.length>0) {
					var item = existe_item[0];
					var nueva_cantidad = parseFloat(item.cantidad) + 1;
					var stock_item = parseFloat(item.stock) || this.phuyu_stock_item(producto);

					var cantidad_total = this.phuyu_cantidad_producto_detalle(item, item) + nueva_cantidad;
					if (parseInt(this.stockalmacen) === 1 && parseInt(item.control) === 1 && cantidad_total > stock_item) {
						this.phuyu_alerta_stock_excedido(item, stock_item, cantidad_total);
						return false;
					}

					item.cantidad = nueva_cantidad;
				}
			}

		    if (existe_item.length==0 || $("#itemrepetir").val()==1) {
		    	producto.preciosinigv = producto.precio; producto.precio = precio; 
		    	producto.valorventa = producto.precio; producto.subtotal = producto.precio;
		    	
		    	producto.afectacionigv = 20; producto.igv = 0; var porcentaje = 1;
				if (producto.afectoigvventa==1) {
					var porcentaje = (1 + this.igvsunat) / 100;

					producto.afectacionigv = 10;
					producto.preciosinigv = Number((producto.precio / porcentaje).toFixed(4));
					producto.valorventa = Number((producto.precio / porcentaje).toFixed(2));
					producto.igv = Number((producto.subtotal - producto.valorventa).toFixed(2));
				}
				
				producto.icbper = 0; producto.isc = 0;
				if (producto.afectoicbper==1) {
					producto.icbper = Number((1 * this.icbpersunat).toFixed(2));;
				}

				producto.control = this.phuyu_controla_stock(producto) ? 1 : 0;
				var stock_disponible = this.phuyu_stock_item(producto);

				var solicitado = this.phuyu_cantidad_producto_detalle(producto) + 1;
				if (producto.control==1 && solicitado > stock_disponible) {
					this.phuyu_alerta_stock_excedido(producto, stock_disponible, solicitado);
					return false;
				}

		    	this.detalle.push({
					codproducto: producto.codproducto, producto: producto.descripcion, codunidad: producto.codunidad,
					unidad: producto.unidad, cantidad: 1, stock:stock_disponible, control:producto.control,
					preciobruto: producto.preciosinigv, preciosinigv: producto.preciosinigv, precio: producto.precio,
					preciorefunitario: producto.precio, porcdescuento: 0, descuento: 0,
					codafectacionigv: producto.afectacionigv, igv: producto.igv, conicbper: producto.afectoicbper, icbper: producto.icbper,
					valorventa: producto.valorventa, subtotal:producto.subtotal, subtotal_tem:producto.subtotal, 
					descripcion:"", calcular: producto.calcular, atendido:0, item:0,
					afectoigvventa: producto.afectoigvventa, unidades: producto.unidades || [],
				});
				this.phuyu_calcular(producto,1);
		    }else{
		    	this.phuyu_calcular(existe_item[0],3);
		    }
		},
		phuyu_itemdetalle: function(index,producto){
			this.item = producto; $("#modal_itemdetalle").modal("show");
		},
		phuyu_cerrar_itemdetalle: function(){
			$("#modal_itemdetalle").modal("hide");
		},
		phuyu_deleteitem: function(index,producto){
			this.phuyu_calcular(producto,2); this.detalle.splice(index,1);
		},
		phuyu_recalcular_item: function(producto){
			var cantidad = parseFloat(producto.cantidad) || 0;
			var precio = parseFloat(producto.precio) || 0;
			var porcentaje = 1;

			producto.subtotal = Number((cantidad * precio).toFixed(2));
			producto.preciobruto = precio;
			producto.preciorefunitario = precio;
			producto.preciosinigv = precio;
			producto.valorventa = producto.subtotal;
			producto.igv = 0;

			if (parseInt(producto.afectoigvventa || 0) === 1 || parseInt(producto.codafectacionigv || 0) === 10) {
				porcentaje = (1 + parseFloat(this.igvsunat || 0)) / 100;
				producto.codafectacionigv = 10;
				producto.preciosinigv = Number((precio / porcentaje).toFixed(4));
				producto.valorventa = Number((cantidad * producto.preciosinigv).toFixed(2));
				producto.igv = Number((producto.subtotal - producto.valorventa).toFixed(2));
			}

			producto.icbper = 0;
			if (parseInt(producto.conicbper || 0) === 1) {
				producto.icbper = Number((cantidad * parseFloat(this.icbpersunat || 0)).toFixed(2));
			}
		},
		phuyu_cambiar_unidad: function(index, producto){
			if (parseFloat(producto.atendido || 0) > 0) {
				phuyu_sistema.phuyu_noti("ITEM YA ATENDIDO", "No se puede cambiar la unidad de un producto atendido", "warning");
				return false;
			}

			var unidad = null;
			var unidades = producto.unidades || [];
			for (var i = 0; i < unidades.length; i++) {
				if (parseInt(unidades[i].codunidad) === parseInt(producto.codunidad)) {
					unidad = unidades[i];
					break;
				}
			}

			if (!unidad) {
				this.$http.post(url + "almacen/productos/informacion_item", {
					codproducto: producto.codproducto,
					codunidad: producto.codunidad
				}).then(function(data){
					if (data.body && data.body.length > 0) {
						producto.stock = parseFloat(data.body[0].stock) || 0;
						producto.precio = parseFloat(data.body[0].precio) || 0;
						this.phuyu_recalcular_item(producto);
						this.phuyu_recalcular_totales_detalle();
					}
				});
				return false;
			}

			producto.unidad = unidad.unidad;
			producto.stock = parseFloat(unidad.stock) || 0;
			producto.precio = parseFloat(unidad.precio) || 0;
			this.phuyu_recalcular_item(producto);
			this.phuyu_recalcular_totales_detalle();
		},
		phuyu_calcular: function(producto,tipo){
			if (parseInt(this.stockalmacen) === 1 && parseInt(producto.control) === 1) {
				var stock_item = parseFloat(producto.stock) || 0;
				var cantidad_item = parseFloat(producto.cantidad) || 0;
				var cantidad_total = this.phuyu_cantidad_producto_detalle(producto, producto) + cantidad_item;

				if (cantidad_total > stock_item) {
					producto.cantidad = Math.max(0, stock_item - this.phuyu_cantidad_producto_detalle(producto, producto));
					this.phuyu_alerta_stock(producto, stock_item);
				}
			}

			if (tipo==1) {
				this.totales.valorventa = Number((this.totales.valorventa + parseFloat(producto.precio)).toFixed(2));
			}else{
				if (tipo==2) {
					this.totales.valorventa = Number((this.totales.valorventa - producto.subtotal).toFixed(2));
				}else{
					this.totales.valorventa = Number((this.totales.valorventa - producto.subtotal).toFixed(2));

					this.phuyu_recalcular_item(producto);
					this.totales.valorventa = Number((this.totales.valorventa + producto.subtotal).toFixed(2));
				}
			}
			this.totales.importe = Number((this.totales.valorventa + this.totales.igv).toFixed(2));
		},
		phuyu_recalcular_totales_detalle: function(){
			var valorventa = 0;
			var igv = 0;
			var importe = 0;

			for (var i = 0; i < this.detalle.length; i++) {
				var item = this.detalle[i];
				this.phuyu_recalcular_item(item);

				valorventa = Number((valorventa + item.valorventa).toFixed(2));
				igv = Number((igv + item.igv).toFixed(2));
				importe = Number((importe + item.subtotal).toFixed(2));
			}

			this.totales.valorventa = valorventa;
			this.totales.igv = igv;
			this.totales.importe = importe;
		},

		phuyu_guardar_pedido: function(){
			if ($("#sessioncaja").val()==0) {
				phuyu_sistema.phuyu_noti("ESTIMADO USUARIO SU CAJA NO ESTA APERTURADA", "NO PUEDE REALIZAR VENTAS","error"); return false;
			}

			if(parseInt(this.campos.codmesa || 0) <= 0){
				phuyu_sistema.phuyu_noti("DEBE SELECCIONAR LA MESA DEL PEDIDO PARA PODER REGISTRAR","","error"); return false;
			}

			if (this.detalle.length==0) {
				phuyu_sistema.phuyu_noti("REGISTRAR UN PRODUCTO EN EL DETALLE", "REGISTRAR ITEM PARA EL PEDIDO","error"); return false;
			}

			this.phuyu_recalcular_totales_detalle();
			this.estado = 1; 
			phuyu_sistema.phuyu_inicio_guardar("GUARDANDO PEDIDO . . .");

			this.$http.post(url+"ventas/pedidos/guardar_pedido", {"campos":this.campos,"detalle":this.detalle,"totales":this.totales}).then(function(data){
				if (data.body=="e") {	phuyu_sistema.phuyu_alerta("SESION DEL USUARIO TERMINADA","DEBE INICIAR SESION NUEVAMENTE","error");}else{
					
					if (data.body.estado==1) {
						phuyu_sistema.phuyu_noti("PEDIDO REGISTRADO CORRECTAMENTE","PEDIDO REGISTRADO EN EL SISTEMA","success");
						// this.$http.get(url+"restaurante/caja/comanda/"+data.body.codpedido).then(function(data){
						// 	$("#imprimir_pedido").empty().html(data.body); 
						// 	var id = "imprimir_pedido";
						// 	var data = document.getElementById(id).innerHTML;
					    //     var myWindow = window.open('', 'IMPRIMIENDO', 'height=500,width=1000');
					    //     myWindow.document.write('<html><head><title>TICKET</title>');
					    //     // myWindow.document.write('<link rel="stylesheet" href="main.css" type="text/css" />');
					    //     myWindow.document.write('</head><body >');
					    //     myWindow.document.write(data);
					    //     myWindow.document.write('</body></html>');
					    //     myWindow.document.close();

					    //     myWindow.onload=function(){
					    //     myWindow.focus(); 
						// 	myWindow.print(); 
						// 	myWindow.close();
					    //     };

						// });
					}else{
						phuyu_sistema.phuyu_alerta(data.body.mensaje || "ERROR AL REGISTRAR PEDIDO","ERROR DE RED","error");
					}
				}
				phuyu_sistema.phuyu_fin(); 
				phuyu_sistema.phuyu_modulo();
			}, function(){
				phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR PEDIDO","ERROR DE RED","error");
				phuyu_sistema.phuyu_fin(); 
				phuyu_sistema.phuyu_modulo();
			});
		},

		phuyu_atender_pedido: function(){
			if (this.campos.pedidonuevo==1) {
				phuyu_sistema.phuyu_noti("DEBE SELECCIONAR UNA MESA CON PEDIDO","PARA ATENDER UN PEDIDO","error");
				return false;
			}else{
				$("#modal_atender").modal("show");
				this.$http.post(url+"ventas/pedidos/phuyu_atenciones",{"codpedido":this.campos.codpedido}).then(function(data){
					this.atender = data.body.detalle; this.atendidos = data.body.atendidos; this.totales = data.body.totales;
				},function(){
					phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
				});
			}
		},
		phuyu_mas_menos: function(pedido,tipo){
			if (tipo==1) {
				if (pedido.falta!=pedido.atender) {
					pedido.atender = pedido.atender + 1;
				}
			}else{
				if (pedido.atender>0) {
					pedido.atender = pedido.atender - 1;
				}
			}
		},
		phuyu_atender: function(){
			var atender = 0;
			for (var i = 0; i < this.atender.length; i++) {
				if (this.atender[i]["atender"]!="") {
					atender = atender + parseFloat(this.atender[i]["atender"]);
				}
			}
			if (atender==0) {
				phuyu_sistema.phuyu_noti("NO HAY PEDIDOS PARA ATENDER","MINIMO DEBE HABER UNA CANTIDAD ATENDIDA","error");
			}else{
				this.estado = 1; $("#modal_atender").modal("hide"); phuyu_sistema.phuyu_inicio_guardar("GUARDANDO ATENCION . . .");
				this.$http.post(url+"ventas/pedidos/guardar_atencion",{"atender":this.atender}).then(function(data){
					if (data.body==1) {
						phuyu_sistema.phuyu_noti("ATENCION REGISTRADA CORRECTAMENTE","PEDIDO 000"+this.campos.codpedido+" ATENDIDO","success");
					}else{
						phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR ATENCION","ERROR DE RED","error");
					}
					phuyu_sistema.phuyu_fin(); phuyu_sistema.phuyu_modulo();
				}, function(){
					phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR ATENCION","ERROR DE RED","error");
					phuyu_sistema.phuyu_fin(); phuyu_sistema.phuyu_modulo();
				});
			}
		},

		phuyu_anular_pedido: function(){
			if (this.campos.pedidonuevo==1) {
				phuyu_sistema.phuyu_noti("DEBE SELECCIONAR UNA MESA CON PEDIDO","PARA ANULAR EL PEDIDO","error");
				return false;
			}

			swal({
				title: "SEGURO ANULAR PEDIDO ?",   
				text: "USTED ESTA POR ANULAR EL PEDIDO 00"+this.campos.codpedido, 
				icon: "warning",
				dangerMode: true,
				buttons: ["CANCELAR", "SI, ANULAR"],
			}).then((willDelete) => {
				if (willDelete) {
					this.$http.post(url+"ventas/pedidos/anular_pedido",{"codregistro":this.campos.codpedido,"codmesa":this.campos.codmesa}).then(function(data){
						if (data.body==1) {
							phuyu_sistema.phuyu_alerta("ANULADO CORRECTAMENTE", "UN PEDIDO ANULADO EN EL SISTEMA","success");
						}else{
							phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR !!!", "SE PERDIÓ LA CONEXION !!! LO SENTIMOS","error");
						}
						phuyu_sistema.phuyu_modulo();
					}, function(){
						phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
					});
				}
			});
		},

		phuyu_avance_pedido: function(){
			if (this.campos.pedidonuevo==1) {
				phuyu_sistema.phuyu_noti("DEBE SELECCIONAR UNA MESA CON PEDIDO","PARA IMPRIMIR EL AVANCE DE CUENTE","error");
				return false;
			}else{
				this.$http.get(url+"restaurante/caja/avance_pedido/"+this.campos.codpedido).then(function(data){
					$("#imprimir_pedido").empty().html(data.body); var id = "imprimir_pedido";
					var data = document.getElementById(id).innerHTML;
			        var myWindow = window.open('', 'IMPRIMIENDO', 'height=400,width=800');
			        myWindow.document.write('<html><head><title>TICKET</title>');
			        // myWindow.document.write('<link rel="stylesheet" href="main.css" type="text/css" />');
			        myWindow.document.write('</head><body >');
			        myWindow.document.write(data);
			        myWindow.document.write('</body></html>');
			        myWindow.document.close();

			        myWindow.onload=function(){
			            myWindow.focus(); myWindow.print(); myWindow.close();
			        };
				});
				// $("#phuyu_pdf").attr("src",url+"restaurante/caja/avance_pedido/"+this.campos.codpedido);
			}
		},
		phuyu_comanda: function(){
			if (this.campos.pedidonuevo==1) {
				phuyu_sistema.phuyu_noti("DEBE SELECCIONAR UNA MESA CON PEDIDO","PARA IMPRIMIR LA COMANDA","error");
				return false;
			}else{
				this.$http.get(url+"restaurante/caja/comanda/"+this.campos.codpedido).then(function(data){
					$("#imprimir_pedido").empty().html(data.body); var id = "imprimir_pedido";
					var data = document.getElementById(id).innerHTML;
			        var myWindow = window.open('', 'IMPRIMIENDO', 'height=500,width=1000');
			        myWindow.document.write('<html><head><title>TICKET</title>');
			        // myWindow.document.write('<link rel="stylesheet" href="main.css" type="text/css" />');
			        myWindow.document.write('</head><body >');
			        myWindow.document.write(data);
			        myWindow.document.write('</body></html>');
			        myWindow.document.close();

			        myWindow.onload=function(){
			            myWindow.focus(); myWindow.print(); myWindow.close();
			        };
				});
				// $("#phuyu_pdf").attr("src",url+"restaurante/caja/avance_pedido/"+this.campos.codpedido);
			}
		},

		phuyu_cobrar_pedido: function(){
			if (this.campos.pedidonuevo==1) {
				phuyu_sistema.phuyu_noti("DEBE SELECCIONAR UNA MESA CON PEDIDO","PARA COBRAR EL PEDIDO","error");
				return false;
			}

			if ($("#sessioncaja").val()==0) {
				phuyu_sistema.phuyu_noti("ESTIMADO USUARIO SU CAJA NO ESTA APERTURADA", "NO PUEDE COBRAR PEDIDO","error"); 
			}else{
				this.phuyu_recalcular_totales_detalle();
				this.campos.codcomprobantetipo = this.campos.codcomprobante; this.phuyu_series();
				this.pagos.monto_efectivo = this.totales.importe;
				this.pagos.vuelto_efectivo = 0;
				this.pagos.codtipopago_tarjeta = 0;
				this.pagos.monto_tarjeta = 0;
				this.pagos.nrovoucher = "";
				$("#modal_pago").modal('show');
			}
		},

		// GUARDAR EL PEDIDO COMO VENTA //

		phuyu_addcliente: function(){
			$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario",180); 
			this.$http.post(url+"ventas/clientes/nuevo_1").then(function(data){
				$("#phuyu_formulario").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); 
				phuyu_sistema.phuyu_modulo();
			});
		},

		phuyu_infocliente: function(codpersona,cliente){
			this.campos.codpersona = codpersona;
			this.campos.cliente = cliente;
			this.$http.get(url+"ventas/clientes/infocliente/"+this.campos.codpersona).then(function(data){
				this.codtipodocumento = data.body[0].coddocumentotipo; this.campos.direccion = data.body[0].direccion;
			});
        },

		phuyu_series: function(){
			if (this.campos.codcomprobantetipo!=undefined) {
				this.estado = 1;
				this.$http.get(url+"caja/controlcajas/phuyu_seriescaja/"+this.campos.codcomprobantetipo).then(function(data){
					this.series = data.body.series; this.estado = 0;
					// this.campos.seriecomprobante = $("#serie").val(); this.phuyu_correlativo();
					this.campos.seriecomprobante = data.body.serie; this.phuyu_correlativo();
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
				this.pagos.monto_tarjeta = 0; this.pagos.nrovoucher = "";
				$("#monto_tarjeta").attr("readonly","true"); $("#monto_tarjeta").removeAttr("required");
				$("#nrovoucher").attr("readonly","true"); $("#nrovoucher").removeAttr("required");
			}else{
				$("#monto_tarjeta").removeAttr("readonly"); $("#monto_tarjeta").attr("required","true");
				$("#nrovoucher").removeAttr("readonly"); $("#nrovoucher").attr("required","true");
			}
			this.phuyu_recalcular_pago();
		},
		phuyu_numero_pago: function(valor){
			var numero = parseFloat(valor);
			if (isNaN(numero) || numero < 0) {
				return 0;
			}
			return numero;
		},
		phuyu_redondear_pago: function(valor){
			return Number((this.phuyu_numero_pago(valor)).toFixed(2));
		},
		phuyu_total_venta: function(){
			return this.phuyu_redondear_pago(this.totales.importe);
		},
		phuyu_resumen_pago: function(){
			var total = this.phuyu_total_venta();
			var tarjeta = this.phuyu_numero_pago(this.pagos.codtipopago_tarjeta)==0 ? 0 : this.phuyu_redondear_pago(this.pagos.monto_tarjeta);
			var efectivo = this.phuyu_redondear_pago(this.pagos.monto_efectivo);
			var restante = this.phuyu_redondear_pago(total - tarjeta);
			var vuelto = efectivo > restante ? this.phuyu_redondear_pago(efectivo - restante) : 0;
			var aplicado = this.phuyu_redondear_pago(tarjeta + efectivo - vuelto);
			var falta = aplicado < total ? this.phuyu_redondear_pago(total - aplicado) : 0;

			return {
				total: total,
				tarjeta: tarjeta,
				efectivo: efectivo,
				vuelto: vuelto,
				aplicado: aplicado,
				falta: falta,
				correcto: falta == 0 && aplicado == total
			};
		},
		phuyu_recalcular_pago: function(){
			var total = this.phuyu_total_venta();
			var tarjeta = this.phuyu_numero_pago(this.pagos.monto_tarjeta);

			if (this.phuyu_numero_pago(this.pagos.codtipopago_tarjeta)==0) {
				tarjeta = 0;
				this.pagos.monto_tarjeta = 0;
				this.pagos.nrovoucher = "";
			}

			if (tarjeta > total) {
				tarjeta = total;
				this.pagos.monto_tarjeta = total;
				phuyu_sistema.phuyu_noti("EL PAGO NO PUEDE SER MAYOR AL TOTAL", "SE AJUSTO AL TOTAL DE LA VENTA","warning");
			}

			var restante = this.phuyu_redondear_pago(total - tarjeta);
			this.pagos.monto_efectivo = restante > 0 ? restante : 0;
			this.pagos.vuelto_efectivo = 0;
		},
		phuyu_vuelto: function(){
			var total = this.phuyu_total_venta();
			var tarjeta = this.phuyu_numero_pago(this.pagos.monto_tarjeta);
			var efectivo = this.phuyu_numero_pago(this.pagos.monto_efectivo);

			if (this.phuyu_numero_pago(this.pagos.codtipopago_tarjeta)==0) {
				tarjeta = 0;
			}

			if (tarjeta > total) {
				tarjeta = total;
				this.pagos.monto_tarjeta = total;
			}

			var restante = this.phuyu_redondear_pago(total - tarjeta);
			var vuelto = this.phuyu_redondear_pago(efectivo - restante);
			this.pagos.vuelto_efectivo = vuelto > 0 ? vuelto : 0;
			if (efectivo < restante) {
				this.pagos.vuelto_efectivo = 0;
			}
		},
		phuyu_validar_pago_contado: function(){
			this.phuyu_vuelto();

			var resumen = this.phuyu_resumen_pago();
			var usaTarjeta = this.phuyu_numero_pago(this.pagos.codtipopago_tarjeta) > 0;

			if (resumen.total <= 0) {
				phuyu_sistema.phuyu_noti("TOTAL DE VENTA INVALIDO", "REVISE EL PEDIDO ANTES DE COBRAR", "error");
				return false;
			}

			if (usaTarjeta && resumen.tarjeta <= 0) {
				phuyu_sistema.phuyu_noti("MONTO DE TARJETA INVALIDO", "INGRESE UN MONTO MAYOR A CERO", "error");
				return false;
			}

			if (usaTarjeta && String(this.pagos.nrovoucher || "").trim() == "") {
				phuyu_sistema.phuyu_noti("FALTA NRO VOUCHER", "REGISTRE EL NUMERO DE OPERACION", "error");
				return false;
			}

			if (resumen.tarjeta > resumen.total) {
				phuyu_sistema.phuyu_noti("EL PAGO NO PUEDE SER MAYOR AL TOTAL", "REVISE EL MONTO DE TARJETA", "error");
				return false;
			}

			if (resumen.falta > 0) {
				phuyu_sistema.phuyu_noti("EL IMPORTE DEBE CUBRIR EL TOTAL DE LA VENTA", "FALTAN S/. " + resumen.falta, "error");
				return false;
			}

			if (Math.abs(resumen.aplicado - resumen.total) > 0.01) {
				phuyu_sistema.phuyu_noti("PAGO DESCUADRADO", "EL PAGO APLICADO NO COINCIDE CON EL TOTAL", "error");
				return false;
			}

			this.pagos.monto_efectivo = resumen.efectivo;
			this.pagos.monto_tarjeta = resumen.tarjeta;
			this.pagos.vuelto_efectivo = resumen.vuelto;
			return true;
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
			var interes = Number(( (this.campos.tasainteres*importe/100) ).toFixed(1));
			var total = Number((importe + interes).toFixed(1));

    		var fecha = new Date();
    		this.totales.interes = Number(( (this.campos.tasainteres * this.totales.importe/100) ).toFixed(1));
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
			this.phuyu_recalcular_totales_detalle();
			if ((this.campos.codcomprobantetipo==10 || this.campos.codcomprobantetipo==25) && this.codtipodocumento!=4) {
				phuyu_sistema.phuyu_noti("PARA EMITIR UNA FACTURA", "DEBE SELECCIONAR UN CLIENTE CON RUC","error"); return false;
			}

			if (parseFloat(this.totales.importe)>=700) {
				if ((this.campos.codcomprobantetipo==12 || this.campos.codcomprobantetipo==26) && this.codtipodocumento==0) {
					phuyu_sistema.phuyu_noti("PARA EMITIR UNA BOLETA CON MONTO MAYOR A 700.00 SOLES","DEBE SELECCIONAR UN CLIENTE CON DNI o RUC","error");
					return false;
				}
			}

			if (this.campos.condicionpago==1) {
				if (!this.phuyu_validar_pago_contado()) {
					return false;
				}
			}else{
				if (this.campos.codpersona==2) {
					phuyu_sistema.phuyu_noti("ATENCION USUARIO: EL SISTEMA NO PERMITE REGISTRAR UN CREDITO A CLIENTES VARIOS","","error");
					return false;
				}
			}
			
			this.estado = 1; $("#modal_pago").modal("hide"); phuyu_sistema.phuyu_inicio_guardar("GUARDANDO VENTA . . .");

			this.$http.post(url+"ventas/pedidos/cobrar_pedido", {"campos":this.campos,"detalle":this.detalle,"cuotas":this.cuotas,"pagos":this.pagos,"totales":this.totales}).then(function(data){
				if (data.body=="e") {
					phuyu_sistema.phuyu_alerta("SESION DEL USUARIO TERMINADA","DEBE INICIAR SESION NUEVAMENTE","error");
				}else{
					if (data.body.estado==1) {
						swal({
							title: "DESEA IMPRIMIR LA VENTA ?",   
							text: "DESEA IMPRIMIR EL COMPROBANTE REGISTRADO", 
							icon: "warning",
							dangerMode: true,
							buttons: ["CANCELAR", "SI, IMPRIMIR"],
						}).then((willDelete) => {
							if (willDelete){
								window.open(url+"facturacion/formato/ticket/"+data.body.codkardex,"_blank");

								// $("#phuyu_pdf").attr("src",url+"restaurante/caja/cobrar_pedido/"+data.body.codkardex);
							}
						});
						phuyu_sistema.phuyu_noti("VENTA REGISTRADA CORRECTAMENTE","VENTA REGISTRADA EN EL SISTEMA","success");
					}else{
						phuyu_sistema.phuyu_alerta(data.body.mensaje || "ERROR AL REGISTRAR VENTA","REVISE EL PEDIDO","error");
					}
				}
				phuyu_sistema.phuyu_fin(); phuyu_sistema.phuyu_modulo();
			}, function(){
				phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR VENTA","ERROR DE RED","error");
				phuyu_sistema.phuyu_fin(); phuyu_sistema.phuyu_modulo();
			});
		},
		
		phuyu_cocina: function(){
			/* this.$http.get("http://localhost/phuyuperu_tickets/phuyu_cocina.php").then(function(data){
				if (data.body!="") {
					phuyu_sistema.phuyu_alerta(data.body,"","error");
				}
			},function(){
				phuyu_sistema.phuyu_alerta("LA IMPRESORA DE COCINA NO ESTA CONFIGURADA","ERROR DE IMPRESION","error");
			}); */
			phuyu_sistema.phuyu_alerta("LA IMPRESORA DE COCINA NO ESTA CONFIGURADA","ERROR DE IMPRESION","error");
		},

		phuyu_movimientos: function(tipomovimiento){
			if ($("#sessioncaja").val()==0) {
				phuyu_sistema.phuyu_noti("ESTIMADO USUARIO SU CAJA NO ESTA APERTURADA", "NO PUEDE REGISTRAR GASTOS","error"); 
			}else{
				$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario",180);

				this.$http.get(url+"caja/movimientos/nuevo_1/"+tipomovimiento+"/0").then(function(data){
					$("#phuyu_formulario").empty().html(data.body);
					phuyu_sistema.phuyu_finloader("phuyu_formulario");
				},function(){
					phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); 
				});
			}
		},
		phuyu_ventadiaria: function(){
			$("#modal_reportes").modal("show");
	        $("#phuyu_pdf").attr("src",url+"restaurante/caja/venta_diaria");
		},
		phuyu_balancecaja: function(){
			if ($("#sessioncaja").val()==0) {
				phuyu_sistema.phuyu_noti("ESTIMADO USUARIO SU CAJA NO ESTA APERTURADA", "NO PUEDE VER EL BALANCE DE CAJA","error"); 
			}else{
				$("#modal_reportes").modal("show");
				$("#phuyu_pdf").attr("src",url+"restaurante/caja/balance_caja");
			}
		},
		phuyu_vendedores_caja: function(){
			$("#modal_empleados").modal("show");
			this.$http.get(url+"restaurante/caja/pdf_vendedores_caja_directo").then(function(data){
				$("#modal_empleados_contenido").empty().html(data.body);
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); 
			});
		},
		phuyu_tipopedido: function(){
			if (this.campos.tipopedido > 0) {
				this.$http.post(url+"almacen/productos/producto_tipopedido/"+this.campos.tipopedido).then(function(data){
					var producto = {"codproducto":data.body[0].codproducto,"descripcion":data.body[0].descripcion,"codunidad":data.body[0].codunidad,
					"unidad":data.body[0].unidad,"stockdisponible":0,"control":0,"precio":data.body[0].precio,"preciorefunitario":data.body[0].precio,
					"calcular":0,"subtotal":data.body[0].precio};
					this.phuyu_additem(producto);
				});
			}
		}
	},
	created: function(){
		this.phuyu_mesas(); this.phuyu_producto(0); phuyu_sistema.phuyu_fin(); $(".in").remove();
	}
});

document.addEventListener("keyup", buscar_tecla, false);
function buscar_tecla(e){
    var keyCode = e.keyCode;

    // TECHA CTROL + F11 //
    if(keyCode==122){ 
    	phuyu_operacion.phuyu_producto();
    }
}
