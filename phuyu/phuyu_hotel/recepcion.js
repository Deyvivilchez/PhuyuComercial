function phuyu_fecha_local(offsetDias){
	var fecha = new Date();
	if (offsetDias) {
		fecha.setDate(fecha.getDate() + parseInt(offsetDias || 0));
	}
	var mes = String(fecha.getMonth() + 1).padStart(2, "0");
	var dia = String(fecha.getDate()).padStart(2, "0");
	return fecha.getFullYear() + "-" + mes + "-" + dia;
}

var phuyu_datos = new Vue({
	el: "#phuyu_hotel",
	data: {
		codambiente: 0,
		codcaracteristica: 0,
		habitaciones: [],
		habitacionActiva: null,
		modoCambioHabitacion: false,
		estadia: {},
		cargandoEstadia: false,
		controlaStockSistema: 0,
		clientes: [],
		productos: [],
		seriesLista: [],
		buscarCliente: "",
		buscarProducto: "",
		guardandoCliente: false,
		guardandoConsumo: false,
		cobrandoHotel: false,
		clienteDocumentoMax: 8,
		cambioHabitacion: {
			situacion_origen: 4
		},
		campos: {
			codpersona: 0,
			cliente: ""
		},
		clienteNuevo: {
			codsociotipo: 1,
			coddocumentotipo: 2,
			documento: "",
			razonsocial: "",
			nombrecomercial: "",
			direccion: "",
			email: "",
			telefono: "",
			sexo: "M",
			codubigeo: 0,
			codzona: 0
		},
		checkin: {
			codhabitacion: 0,
			codpersona: 0,
			cliente: "",
			documento: "",
			direccion: "",
			fecha_checkin: phuyu_fecha_local(0),
			fecha_checkout: phuyu_fecha_local(1),
			precio_base: 0,
			precio_noche: 0,
			total_estadia: 0,
			noches: 1,
			codempleado: 0,
			observacion: ""
		},
		consumo: {
			codconsumo: 0,
			codestadia: 0,
			codhabitacion: 0,
			codproducto: 0,
			codunidad: 0,
			id_serie: 0,
			series: [],
			controlstock: 0,
			controlarseries: 0,
			stock: 0,
			producto: "",
			unidad: "",
			cantidad: 1,
			preciounitario: 0,
			descripcion: ""
		},
		checkout: {
			tipo: "checkout",
			destino_habitacion: 4,
			campos: {
				codestadia: 0,
				codcomprobantetipo: 0,
				seriecomprobante: "",
				nro: "",
				fechacomprobante: phuyu_fecha_local(0),
				fechakardex: phuyu_fecha_local(0),
				condicionpago: 1,
				codempleado: 0,
				codmoneda: 1,
				tipocambio: 1,
				codlote: 0,
				creditoprogramado: 1,
				nrodias: 30,
				nrocuotas: 1,
				codcreditoconcepto: 3,
				tasainteres: 0,
				totalcredito: 0,
				codpersona_facturacion: 0
			},
			facturacion: {
				codpersona: 0,
				cliente: "",
				documento: "",
				coddocumentotipo: 0,
				direccion: ""
			},
			pagos: {
				codtipopago_efectivo: 1,
				monto_efectivo: 0,
				vuelto_efectivo: 0,
				codtipopago_tarjeta: 0,
				monto_tarjeta: 0,
				nrovoucher: ""
			},
			cuotas: []
		},
		cancelacion: {
			motivo: "",
			procesando: false,
			confirmar_pagos: 0
		}
	},
	methods: {
		modal_bootstrap: function(selector, accion){
			var elemento = document.querySelector(selector);
			if (window.bootstrap && elemento) {
				bootstrap.Modal.getOrCreateInstance(elemento)[accion]();
				return;
			}
			if (window.jQuery && $(selector).modal) {
				$(selector).modal(accion);
			}
		},
		cargar_habitaciones: function(){
			this.$http.post(url+"hotel/recepcion/habitaciones", {codambiente:this.codambiente, codcaracteristica:this.codcaracteristica}).then(function(data){
				this.habitaciones = data.body;
			});
		},
		habitaciones_agrupadas: function(){
			var grupos = [];
			var indices = {};
			(this.habitaciones || []).forEach(function(habitacion){
				var ambiente = habitacion.ambiente || "SIN AMBIENTE";
				if (indices[ambiente] === undefined) {
					indices[ambiente] = grupos.length;
					grupos.push({ambiente: ambiente, habitaciones: []});
				}
				grupos[indices[ambiente]].habitaciones.push(habitacion);
			});
			return grupos;
		},
		seleccionar_habitacion: function(habitacion){
			if (this.modoCambioHabitacion) {
				this.confirmar_cambio_habitacion(habitacion);
				return;
			}
			this.habitacionActiva = habitacion;
			this.checkin.codhabitacion = habitacion.codhabitacion;
			this.checkin.precio_base = Number(habitacion.preciobase || 0);
			this.recalcular_checkin_total();
			this.limpiar_cliente_checkin();
			this.estadia = {};
			this.productos = [];
			this.buscarProducto = "";
			this.limpiar_consumo();
			if (parseInt(habitacion.situacion || 0) == 3) {
				this.cargar_estadia(habitacion.codhabitacion);
			}else{
				this.$nextTick(function(){
					this.iniciar_select_cliente();
				});
			}
		},
		noches_checkin: function(){
			var noches = this.diff_dias(this.checkin.fecha_checkin, this.checkin.fecha_checkout);
			return noches > 0 ? noches : 1;
		},
		recalcular_checkin_total: function(){
			var noches = this.noches_checkin();
			var precioBase = this.toNum(this.checkin.precio_base);
			var total = Number((precioBase * noches).toFixed(2));
			this.checkin.noches = noches;
			this.checkin.total_estadia = total;
			this.checkin.precio_noche = precioBase;
		},
		iniciar_cambio_habitacion: function(){
			if (!this.habitacionActiva || !this.estadia.estadia) {
				phuyu_sistema.phuyu_noti("SELECCIONE UNA ESTADIA ACTIVA", "", "error");
				return;
			}
			this.cambioHabitacion.situacion_origen = 4;
			this.modoCambioHabitacion = true;
			phuyu_sistema.phuyu_noti("SELECCIONE HABITACION DESTINO", "SOLO HABITACIONES DISPONIBLES", "info");
		},
		cancelar_cambio_habitacion: function(){
			this.modoCambioHabitacion = false;
		},
		confirmar_cambio_habitacion: function(habitacion){
			if (!this.habitacionActiva || !this.estadia.estadia) {
				this.modoCambioHabitacion = false;
				return;
			}
			if (parseInt(habitacion.codhabitacion) == parseInt(this.habitacionActiva.codhabitacion)) {
				phuyu_sistema.phuyu_noti("SELECCIONE UNA HABITACION DIFERENTE", "", "warning");
				return;
			}
			if (parseInt(habitacion.situacion) != 1) {
				phuyu_sistema.phuyu_noti("HABITACION NO DISPONIBLE", "ELIJA UNA HABITACION LIBRE", "error");
				return;
			}
			var vm = this;
			swal({
				title: "Cambiar habitacion",
				text: "Mover estadia 000" + this.estadia.estadia.codestadia + " de habitacion " + this.habitacionActiva.numero + " a habitacion " + habitacion.numero,
				icon: "warning",
				buttons: ["Cancelar", "Cambiar"],
				dangerMode: false
			}).then(function(confirmado){
				if (!confirmado) { return; }
				vm.$http.post(url+"hotel/estadias/cambiar_habitacion", {
					codestadia: vm.estadia.estadia.codestadia,
					codhabitacion_origen: vm.habitacionActiva.codhabitacion,
					codhabitacion_destino: habitacion.codhabitacion,
					situacion_origen: vm.cambioHabitacion.situacion_origen,
					observacion: "CAMBIO DESDE RECEPCION"
				}).then(function(data){
					if (data.body.estado == 1) {
						phuyu_sistema.phuyu_noti(data.body.mensaje || "HABITACION CAMBIADA", "HABITACION " + habitacion.numero, "success");
						vm.modoCambioHabitacion = false;
						vm.habitacionActiva = habitacion;
						vm.cargar_habitaciones();
						vm.cargar_estadia(habitacion.codhabitacion);
					}else{
						phuyu_sistema.phuyu_alerta(data.body.mensaje || "NO SE PUDO CAMBIAR HABITACION", "", "error");
					}
				});
			});
		},
		phuyu_infocliente: function(codpersona){
			this.$http.get(url+"ventas/clientes/infocliente/"+codpersona).then(function(data){
				var info = data.body[0] || data.body || {};
				this.campos.codpersona = codpersona;
				this.campos.cliente = info.razonsocial || "";
				this.checkin.codpersona = codpersona;
				this.checkin.cliente = info.razonsocial || "";
				this.checkin.documento = info.documento || "";
				this.checkin.direccion = info.direccion || "";
			});
		},
		limpiar_cliente_checkin: function(){
			this.campos.codpersona = 0;
			this.campos.cliente = "";
			this.checkin.codpersona = 0;
			this.checkin.cliente = "";
			this.checkin.documento = "";
			this.checkin.direccion = "";
		},
		cargar_estadia: function(codhabitacion){
			this.cargandoEstadia = true;
			this.estadia = {};
			this.$http.post(url+"hotel/recepcion/estadia/"+codhabitacion).then(function(data){
				this.cargandoEstadia = false;

				if (data.body.estado == 1) {
					var detalle = data.body.detalle || {};

					if (detalle.estadia && Array.isArray(detalle.estadia)) {
						detalle.estadia = detalle.estadia[0] || null;
					}

					if (!detalle.estadia && detalle.codestadia) {
						detalle = {
							estadia: detalle,
							consumos: data.body.consumos || detalle.consumos || []
						};
					}

					if (!detalle.estadia && data.body.estadia) {
						detalle.estadia = Array.isArray(data.body.estadia) ? (data.body.estadia[0] || null) : data.body.estadia;
						detalle.consumos = data.body.consumos || detalle.consumos || [];
					}

					if (!detalle.consumos) {
						detalle.consumos = [];
					}

					if (!detalle.estadia || !detalle.estadia.codestadia) {
						this.estadia = {};
						phuyu_sistema.phuyu_alerta("ESTADIA NO CARGADA", "EL SERVIDOR NO ENVIO detalle.estadia.codestadia", "error");
						return;
					}

					this.estadia = detalle;
					this.consumo.codestadia = this.estadia.estadia.codestadia;
					this.consumo.codhabitacion = this.habitacionActiva.codhabitacion;
					this.$nextTick(function(){
						this.iniciar_select_producto();
					});
				}else{
					this.estadia = {};
					phuyu_sistema.phuyu_alerta(data.body.mensaje || "ESTADIA NO ACTIVA", "LA HABITACION FIGURA OCUPADA, PERO NO TIENE UNA ESTADIA ACTIVA VIGENTE", "error");
				}
			}, function(){
				this.cargandoEstadia = false;
				this.estadia = {};
				phuyu_sistema.phuyu_alerta("ERROR DE RED", "NO SE PUDO CARGAR LA ESTADIA DE LA HABITACION", "error");
			});
		},
		buscar_clientes: function(){
			if (this.buscarCliente.length < 2) { this.clientes = []; return; }
			this.$http.post(url+"hotel/recepcion/clientes", {buscar:this.buscarCliente}).then(function(data){
				this.clientes = data.body;
			});
		},
		seleccionar_cliente: function(cliente){
			this.checkin.codpersona = cliente.codpersona;
			this.checkin.cliente = cliente.razonsocial;
			this.clientes = [];
			this.buscarCliente = cliente.documento + " - " + cliente.razonsocial;
		},
		limpiar_cliente_nuevo: function(){
			this.clienteNuevo = {
				codsociotipo: 1,
				coddocumentotipo: 2,
				documento: "",
				razonsocial: "",
				nombrecomercial: "",
				direccion: "",
				email: "",
				telefono: "",
				sexo: "M",
				codubigeo: 0,
				codzona: 0
			};
			this.clienteDocumentoMax = 8;
		},
		abrir_cliente_nuevo: function(){
			this.limpiar_cliente_nuevo();
			this.modal_bootstrap("#modal_cliente_hotel", "show");
		},
		cambiar_tipo_documento_cliente: function(){
			var tipo = parseInt(this.clienteNuevo.coddocumentotipo || 0);
			if (tipo == 1) {
				this.clienteDocumentoMax = 8;
				this.clienteNuevo.documento = "";
				return;
			}
			this.clienteDocumentoMax = tipo == 4 ? 11 : 8;
		},
		consultar_cliente_nuevo: function(){
			var tipo = parseInt(this.clienteNuevo.coddocumentotipo || 0);
			var documento = (this.clienteNuevo.documento || "").trim();
			if (!documento) {
				phuyu_sistema.phuyu_noti("INGRESE DOCUMENTO", "", "warning");
				return;
			}
			if (tipo == 2 && documento.length != 8) {
				phuyu_sistema.phuyu_noti("DNI INVALIDO", "DEBE TENER 8 DIGITOS", "warning");
				return;
			}
			if (tipo == 4 && documento.length != 11) {
				phuyu_sistema.phuyu_noti("RUC INVALIDO", "DEBE TENER 11 DIGITOS", "warning");
				return;
			}
			this.$http.get(url+"web/phuyu_buscarsocio/"+documento).then(function(data){
				if (data.body != "") {
					var datos = eval(data.body);
					if (datos && datos[0]) {
						this.clienteNuevo.razonsocial = datos[0].razonsocial || "";
						this.clienteNuevo.nombrecomercial = datos[0].nombrecomercial || "";
						this.clienteNuevo.direccion = datos[0].direccion || "-";
						this.clienteNuevo.email = datos[0].email || "";
						this.clienteNuevo.telefono = datos[0].telefono || "";
						this.clienteNuevo.sexo = datos[0].sexo || this.clienteNuevo.sexo;
						phuyu_sistema.phuyu_noti("DOCUMENTO YA EXISTE", "SE CARGARON SUS DATOS", "warning");
					}
					return;
				}
				if (tipo == 2) {
					this.$http.get(url+"web/phuyu_dni/"+documento).then(function(resp){
						if (resp.body.success == true) {
							this.clienteNuevo.razonsocial = resp.body.result.apellidoPaterno+" "+resp.body.result.apellidoMaterno+" "+resp.body.result.nombres;
							this.clienteNuevo.direccion = "-";
						}else{
							phuyu_sistema.phuyu_noti("NO SE ENCONTRARON DATOS", "", "warning");
						}
					});
				}else if (tipo == 4) {
					this.$http.get(url+"web/phuyu_ruc/"+documento).then(function(resp){
						if (resp.body.persona) {
							this.clienteNuevo.razonsocial = resp.body.persona.razonSocial;
							this.clienteNuevo.nombrecomercial = resp.body.persona.razonSocial;
							this.clienteNuevo.direccion = resp.body.persona.direccion+" "+resp.body.persona.departamento+" - "+resp.body.persona.provincia+" - "+resp.body.persona.distrito;
							this.clienteNuevo.sexo = "E";
						}else{
							phuyu_sistema.phuyu_noti("NO SE ENCONTRARON DATOS", "", "warning");
						}
					});
				}
			});
		},
		guardar_cliente_nuevo: function(){
			if (parseInt(this.clienteNuevo.coddocumentotipo || 0) != 1 && !(this.clienteNuevo.documento || "").trim()) {
				phuyu_sistema.phuyu_noti("INGRESE DOCUMENTO", "", "error");
				return;
			}
			if (!(this.clienteNuevo.razonsocial || "").trim()) {
				phuyu_sistema.phuyu_noti("INGRESE NOMBRE O RAZON SOCIAL", "", "error");
				return;
			}
			if (!(this.clienteNuevo.direccion || "").trim()) {
				this.clienteNuevo.direccion = "-";
			}
			this.guardandoCliente = true;
			this.$http.post(url+"ventas/clientes/guardar_1", this.clienteNuevo).then(function(data){
				this.guardandoCliente = false;
				if (data.body == 0 || data.body == "e") {
					phuyu_sistema.phuyu_alerta("NO SE PUDO REGISTRAR CLIENTE", "REVISE DOCUMENTO O DATOS", "error");
					return;
				}
				var socio = data.body;
				if (typeof socio === "string") {
					socio = eval(socio);
				}
				if (!socio || !socio[0]) {
					phuyu_sistema.phuyu_alerta("NO SE PUDO LEER EL CLIENTE REGISTRADO", "", "error");
					return;
				}
				var cliente = socio[0];
				$("#codpersona_hotel").empty().append(new Option(cliente.razonsocial, cliente.codpersona, true, true)).trigger("change");
				this.phuyu_infocliente(cliente.codpersona);
				this.modal_bootstrap("#modal_cliente_hotel", "hide");
				phuyu_sistema.phuyu_noti("CLIENTE REGISTRADO", cliente.razonsocial, "success");
			}, function(){
				this.guardandoCliente = false;
				phuyu_sistema.phuyu_alerta("ERROR DE RED", "NO SE PUDO REGISTRAR CLIENTE", "error");
			});
		},
		guardar_checkin: function(){
			if (!this.checkin.codpersona) {
				phuyu_sistema.phuyu_noti("SELECCIONE UN CLIENTE", "PARA REGISTRAR CHECK-IN", "error");
				return;
			}
			this.recalcular_checkin_total();
			if (this.toNum(this.checkin.precio_base) <= 0) {
				phuyu_sistema.phuyu_noti("INGRESE PRECIO POR NOCHE", "", "error");
				return;
			}
			this.$http.post(url+"hotel/estadias/checkin", Object.assign({}, this.checkin, {
				precio_noche: this.checkin.precio_base,
				precio_base: this.checkin.precio_base,
				total_estadia: this.checkin.total_estadia,
				noches: this.checkin.noches
			})).then(function(data){
				if (data.body.estado == 1) {
					phuyu_sistema.phuyu_noti("CHECK-IN REGISTRADO", "ESTADIA 000"+data.body.codestadia, "success");
					this.cargar_habitaciones();
					this.habitacionActiva = null;
				}else{
					phuyu_sistema.phuyu_alerta(data.body.mensaje || "NO SE PUDO REGISTRAR CHECK-IN", "", "error");
				}
			});
		},
		buscar_productos: function(){
			if (this.buscarProducto.length < 2) { this.productos = []; return; }
			this.$http.post(url+"hotel/consumos/productos", {buscar:this.buscarProducto}).then(function(data){
				this.productos = data.body;
			});
		},
		es_factura: function(codcomprobantetipo){
			return [10, 25].indexOf(parseInt(codcomprobantetipo || 0)) !== -1;
		},
		es_boleta: function(codcomprobantetipo){
			return [12, 26].indexOf(parseInt(codcomprobantetipo || 0)) !== -1;
		},
		es_cliente_varios: function(codpersona){
			return parseInt(codpersona || 0) == 2;
		},
		toNum: function(valor){
			var numero = parseFloat(valor);
			return isNaN(numero) ? 0 : numero;
		},
		fecha_hoy: function(){
			return phuyu_fecha_local(0);
		},
		parse_fecha: function(valor){
			if (!valor) { return null; }
			var partes = String(valor).split("-");
			if (partes.length !== 3) { return null; }
			return new Date(parseInt(partes[0], 10), parseInt(partes[1], 10) - 1, parseInt(partes[2], 10));
		},
		diff_dias: function(desde, hasta){
			var fechaDesde = this.parse_fecha(desde);
			var fechaHasta = this.parse_fecha(hasta);
			if (!fechaDesde || !fechaHasta) { return 0; }
			fechaDesde.setHours(0, 0, 0, 0);
			fechaHasta.setHours(0, 0, 0, 0);
			return Math.max(0, Math.floor((fechaHasta.getTime() - fechaDesde.getTime()) / 86400000));
		},
		clase_stock: function(stock){
			var cantidad = Number(stock || 0);
			if (cantidad <= 0) { return "stock-empty"; }
			if (cantidad <= 5) { return "stock-low"; }
			return "stock-ok";
		},
		texto_stock: function(stock){
			var cantidad = Number(stock || 0);
			if (cantidad <= 0) { return "Sin stock"; }
			if (cantidad <= 5) { return "Stock bajo"; }
			return "Disponible";
		},
		producto_sin_stock: function(producto){
			if (!producto) { return false; }
			return this.controlaStockSistema == 1 && parseInt(producto.controlstock || 0) == 1 && Number(producto.stock || 0) <= 0;
		},
		clase_alerta_stock: function(producto){
			if (!producto || this.controlaStockSistema != 1 || parseInt(producto.controlstock || 0) != 1) { return ""; }
			var cantidad = Number(producto.stock || 0);
			if (cantidad <= 0) { return "hotel-stock-alert"; }
			if (cantidad <= 5) { return "hotel-stock-warn"; }
			return "";
		},
		seleccionar_producto: function(producto){
			if (!producto.codproducto && producto.id) {
				var partes = String(producto.id).split("|");
				producto.codproducto = partes[0] || 0;
				producto.codunidad = partes[1] || producto.codunidad || 0;
			}

			this.consumo.codproducto = producto.codproducto || 0;
			this.consumo.codunidad = parseInt(producto.codunidad || 0) || 0;
			this.consumo.id_serie = 0;
			this.consumo.series = [];
			this.consumo.controlstock = parseInt(producto.controlstock || 0);
			this.consumo.controlarseries = parseInt(producto.controlarseries || 0);
			this.consumo.stock = Number(producto.stock || 0);
			this.consumo.producto = producto.descripcion || producto.text || "";
			this.consumo.unidad = producto.unidad || (this.consumo.codunidad ? "" : "UNIDAD POR DEFECTO");
			this.consumo.preciounitario = Number(producto.precio || producto.preciounitario || 0);
			this.consumo.descripcion = producto.descripcion || producto.text || "";
			this.buscarProducto = this.consumo.producto;
			this.productos = [];

			if (!this.consumo.codproducto) {
				phuyu_sistema.phuyu_noti("PRODUCTO INVALIDO", "NO SE RECIBIO CODPRODUCTO", "error");
				return;
			}

			if (this.controlaStockSistema == 1 && this.consumo.controlstock == 1 && Number(this.consumo.stock || 0) <= 0) {
				phuyu_sistema.phuyu_noti("SIN STOCK", "NO PUEDE AGREGAR ESTE PRODUCTO", "warning");
			}

			if ($("#codproducto_consumo").length && $("#codproducto_consumo").data("select2")) {
				var option = new Option(this.consumo.producto, this.consumo.codproducto + "|" + (this.consumo.codunidad || 0), true, true);
				$("#codproducto_consumo").empty().append(option).trigger("change");
			}

			if (this.controlaStockSistema == 1 && this.consumo.controlarseries == 1) {
				this.consumo.cantidad = 1;
				this.cargar_series_consumo();
			}
		},
		limpiar_consumo: function(){
			var codestadia = this.consumo.codestadia;
			var codhabitacion = this.consumo.codhabitacion;
			if ($("#codproducto_consumo").length && $("#codproducto_consumo").data("select2")) {
				$("#codproducto_consumo").val(null).trigger("change");
			}
			this.consumo.codconsumo = 0;
			this.consumo.codestadia = codestadia;
			this.consumo.codhabitacion = codhabitacion;
			this.consumo.codproducto = 0;
			this.consumo.codunidad = 0;
			this.consumo.id_serie = 0;
			this.consumo.series = [];
			this.consumo.controlstock = 0;
			this.consumo.controlarseries = 0;
			this.consumo.stock = 0;
			this.consumo.producto = "";
			this.consumo.unidad = "";
			this.consumo.cantidad = 1;
			this.consumo.preciounitario = 0;
			this.consumo.descripcion = "";
		},
		editar_consumo: function(consumo){
			if (parseInt(consumo.situacion) != 1 || parseInt(consumo.codkardex || 0) > 0) {
				phuyu_sistema.phuyu_noti("CONSUMO NO EDITABLE", "YA FUE FACTURADO O ANULADO", "warning");
				return;
			}

			this.consumo.codconsumo = consumo.codconsumo;
			this.consumo.codestadia = consumo.codestadia;
			this.consumo.codhabitacion = consumo.codhabitacion;
			this.consumo.codproducto = consumo.codproducto;
			this.consumo.codunidad = parseInt(consumo.codunidad || 0) || 0;
			this.consumo.id_serie = parseInt(consumo.id_serie || 0);
			this.consumo.controlstock = parseInt(consumo.controlstock || 0);
			this.consumo.controlarseries = parseInt(consumo.controlarseries || 0);
			this.consumo.stock = Number(consumo.stock || 0);
			this.consumo.series = this.consumo.id_serie ? [{id_serie:this.consumo.id_serie, serie_codigo:consumo.serie_codigo}] : [];
			this.consumo.producto = consumo.producto;
			this.consumo.unidad = consumo.unidad || (this.consumo.codunidad ? "" : "UNIDAD POR DEFECTO");
			this.consumo.cantidad = Number(consumo.cantidad);
			this.consumo.preciounitario = Number(consumo.preciounitario);
			this.consumo.descripcion = consumo.descripcion;

			if ($("#codproducto_consumo").length && $("#codproducto_consumo").data("select2")) {
				var option = new Option(consumo.producto, consumo.codproducto + "|" + (this.consumo.codunidad || 0), true, true);
				$("#codproducto_consumo").empty().append(option).trigger("change");
			}

			if (this.controlaStockSistema == 1 && this.consumo.controlarseries == 1) {
				this.cargar_series_consumo();
			}
		},
		cargar_series_consumo: function(){
			if (!this.consumo.codproducto) { return; }
			this.$http.post(url+"hotel/consumos/series", {codproducto:this.consumo.codproducto}).then(function(data){
				var series = data.body || [];
				if (this.consumo.id_serie && !series.find(function(s){ return parseInt(s.id_serie) == parseInt(this.consumo.id_serie); }.bind(this))) {
					series.unshift({id_serie:this.consumo.id_serie, serie_codigo:"SERIE ACTUAL"});
				}
				this.consumo.series = series;
			});
		},
		subtotal_consumo: function(){
			return Number((Number(this.consumo.cantidad || 0) * Number(this.consumo.preciounitario || 0)).toFixed(2));
		},
		iniciar_select_cliente: function(){
			var vm = this;
			var $select = $("#codpersona_hotel");
			if (!$select.length || typeof $.fn.select2 === "undefined") { return; }
			if ($select.data("select2")) { $select.select2("destroy"); }
			$select.empty().select2({
				placeholder: "Buscar cliente",
				minimumInputLength: 1,
				ajax: {
					url: url+"ventas/clientes/buscar",
					dataType: "json",
					delay: 250,
					data: function(params){
						return {search:{value:params.term || "", tipo:1}};
					},
					processResults: function(data){
						return {results:data.data || []};
					},
					cache: true
				},
				escapeMarkup: function(markup){ return markup; },
				templateResult: function(result){
					if (result.loading) { return result.text; }
					return "<div><strong>"+(result.documento || "")+"</strong><div class='text-muted small'>"+(result.razonsocial || result.text || "")+"</div></div>";
				},
				templateSelection: function(result){
					return result.razonsocial || result.text || "";
				}
			}).on("select2:select", function(e){
				var persona = e.params.data || {};
				var codpersona = persona.codpersona || persona.id || 0;
				if (codpersona) {
					vm.phuyu_infocliente(codpersona);
				}
			});
		},
		iniciar_select_producto: function(){
			var vm = this;
			var $select = $("#codproducto_consumo");
			if (!$select.length || typeof $.fn.select2 === "undefined") { return; }
			if ($select.data("select2")) { $select.select2("destroy"); }
			$select.empty().select2({
				placeholder: "Buscar producto",
				minimumInputLength: 1,
				ajax: {
					url: url+"hotel/consumos/productos_select",
					dataType: "json",
					delay: 250,
					data: function(params){
						return {q:params.term || ""};
					},
					processResults: function(data){
						return {results:data.results || []};
					},
					cache: true
				},
				escapeMarkup: function(markup){ return markup; },
				templateResult: function(result){
					if (result.loading) { return result.text; }
					var badges = "";
					if (vm.controlaStockSistema == 1 && parseInt(result.controlstock || 0) == 1) {
						var stockClase = vm.clase_stock(result.stock);
						badges += " <span class='hotel-stock-pill "+stockClase+"'>" + vm.texto_stock(result.stock) + " · Stock " + Number(result.stock || 0).toFixed(2) + "</span>";
					}
					if (parseInt(result.controlarseries || 0) == 1) { badges += " <span class='badge bg-info-subtle text-info'>Series</span>"; }
					return "<div><strong>"+(result.descripcion || result.text || "")+"</strong><div class='text-muted small'>"+(result.unidad || "")+" - S/. "+Number(result.precio || 0).toFixed(2)+" "+badges+"</div></div>";
				},
				templateSelection: function(result){
					return result.descripcion || result.text || "";
				}
			}).on("select2:select", function(e){
				vm.seleccionar_producto(e.params.data || {});
			});
		},
		guardar_consumo: function(){
			if (this.guardandoConsumo) { return; }

			if (!this.estadia.estadia || !this.estadia.estadia.codestadia) {
				phuyu_sistema.phuyu_noti("NO HAY ESTADIA ACTIVA", "VUELVA A SELECCIONAR LA HABITACION", "error");
				return;
			}

			this.consumo.codestadia = this.estadia.estadia.codestadia;
			this.consumo.codhabitacion = this.habitacionActiva ? this.habitacionActiva.codhabitacion : this.consumo.codhabitacion;
			this.consumo.codunidad = parseInt(this.consumo.codunidad || 0) || 0;

			if (!this.consumo.codproducto) {
				phuyu_sistema.phuyu_noti("SELECCIONE PRODUCTO", "", "error");
				return;
			}

			if (this.controlaStockSistema == 1 && this.consumo.controlstock == 1 && Number(this.consumo.cantidad || 0) > Number(this.consumo.stock || 0)) {
				phuyu_sistema.phuyu_noti("STOCK INSUFICIENTE", "DISPONIBLE: " + Number(this.consumo.stock || 0).toFixed(2), "error");
				return;
			}

			if (this.controlaStockSistema == 1 && this.consumo.controlarseries == 1 && !this.consumo.id_serie) {
				phuyu_sistema.phuyu_noti("SELECCIONE SERIE", "", "error");
				return;
			}

			if (this.controlaStockSistema == 1 && this.consumo.controlarseries == 1) {
				this.consumo.cantidad = 1;
			}

			this.consumo.cantidad = Number(this.consumo.cantidad || 0);
			this.consumo.preciounitario = Number(this.consumo.preciounitario || 0);

			if (this.consumo.cantidad <= 0 || this.consumo.preciounitario < 0) {
				phuyu_sistema.phuyu_noti("REVISE CANTIDAD Y PRECIO", "", "error");
				return;
			}

			this.guardandoConsumo = true;
			this.$http.post(url+"hotel/consumos/guardar", this.consumo).then(function(data){
				this.guardandoConsumo = false;

				if (data.body.estado == 1) {
					phuyu_sistema.phuyu_noti(this.consumo.codconsumo ? "CONSUMO ACTUALIZADO" : "CONSUMO REGISTRADO", "", "success");
					this.limpiar_consumo();
					this.buscarProducto = "";
					this.cargar_estadia(this.habitacionActiva.codhabitacion);
				}else{
					phuyu_sistema.phuyu_alerta(data.body.mensaje || "NO SE PUDO REGISTRAR CONSUMO", "", "error");
				}
			}, function(){
				this.guardandoConsumo = false;
				phuyu_sistema.phuyu_alerta("ERROR DE RED", "NO SE PUDO REGISTRAR CONSUMO", "error");
			});
		},
		anular_consumo: function(consumo){
			this.$http.post(url+"hotel/consumos/anular", {codconsumo:consumo.codconsumo}).then(function(data){
				if (data.body.estado == 1) {
					this.cargar_estadia(this.habitacionActiva.codhabitacion);
				}
			});
		},
		total_checkout: function(){
			if (!this.estadia.estadia) { return 0; }
			var total = this.alojamiento_pendiente();
			(this.estadia.consumos || []).forEach(function(c){
				if (parseInt(c.situacion) == 1) { total += Number(c.subtotal || 0); }
			});
			return total;
		},
		alojamiento_pendiente: function(){
			if (!this.estadia.estadia) { return 0; }
			if (this.estadia.estadia.alojamiento_pendiente !== undefined) {
				return Math.max(0, Number(this.estadia.estadia.alojamiento_pendiente || 0));
			}
			return Math.max(0, Number(this.estadia.estadia.alojamiento || 0) - Number(this.estadia.estadia.total_pagado_ocupacion || 0));
		},
		total_consumos_pendientes: function(){
			var total = 0;
			(this.estadia.consumos || []).forEach(function(c){
				if (parseInt(c.situacion) == 1) { total += Number(c.subtotal || 0); }
			});
			return total;
		},
		total_pago_actual: function(){
			if (this.checkout.tipo == "consumos") { return this.total_consumos_pendientes(); }
			if (this.checkout.tipo == "ocupacion") { return this.alojamiento_pendiente(); }
			return this.total_checkout();
		},
		preparar_cobro: function(tipo){
			if (!this.estadia.estadia || !this.estadia.estadia.codestadia) {
				phuyu_sistema.phuyu_noti("NO HAY ESTADIA ACTIVA", "VUELVA A SELECCIONAR LA HABITACION", "error");
				return false;
			}
			if ($("#sessioncaja").val() == 0) {
				phuyu_sistema.phuyu_noti("CAJA NO APERTURADA", "NO PUEDE COBRAR", "error");
				return false;
			}
			this.checkout.tipo = tipo;
			this.checkout.campos.codestadia = this.estadia.estadia.codestadia;
			this.checkout.campos.codempleado = this.estadia.estadia.codempleado;
			this.checkout.campos.codlote = 0;
			this.checkout.campos.totalcredito = this.total_pago_actual();
			this.checkout.campos.condicionpago = 1;
			this.checkout.cuotas = [];
			this.checkout.campos.codpersona_facturacion = 0;
			this.checkout.facturacion = {
				codpersona: 0,
				cliente: "",
				documento: "",
				coddocumentotipo: 0,
				direccion: ""
			};
			this.checkout.pagos.monto_efectivo = this.total_pago_actual();
			this.checkout.pagos.monto_tarjeta = 0;
			this.checkout.pagos.vuelto_efectivo = 0;
			var comprobanteDefault = parseInt($("#hotel_comprobante_default").val() || 0);
			var primerComprobante = $("#modal_checkout select").first().val();
			if (comprobanteDefault > 0) {
				this.checkout.campos.codcomprobantetipo = comprobanteDefault;
			}else if (!this.checkout.campos.codcomprobantetipo && primerComprobante) {
				this.checkout.campos.codcomprobantetipo = primerComprobante;
			}
			this.series();
			this.modal_bootstrap("#modal_checkout", "show");
			this.$nextTick(function(){
				this.iniciar_select_factura();
			});
			return true;
		},
		abrir_cobro_consumos: function(){
			if (this.total_consumos_pendientes() <= 0) {
				phuyu_sistema.phuyu_noti("SIN CONSUMOS PENDIENTES", "", "warning");
				return;
			}
			this.preparar_cobro("consumos");
		},
		abrir_cobro_ocupacion: function(){
			if (this.alojamiento_pendiente() <= 0) {
				phuyu_sistema.phuyu_noti("OCUPACION YA COBRADA", "", "warning");
				return;
			}
			this.preparar_cobro("ocupacion");
		},
		abrir_checkout: function(){
			this.preparar_cobro("checkout");
		},
		abrir_cancelar_ocupacion: function(){
			if (!this.estadia.estadia || !this.estadia.estadia.codestadia) {
				phuyu_sistema.phuyu_noti("NO HAY ESTADIA ACTIVA", "VUELVA A SELECCIONAR LA HABITACION", "error");
				return;
			}
			this.cancelacion.motivo = "";
			this.cancelacion.confirmar_pagos = 0;
			this.modal_bootstrap("#modal_cancelar_ocupacion", "show");
		},
		series: function(){
			if (!this.checkout.campos.codcomprobantetipo) { return; }
			if (!this.es_factura(this.checkout.campos.codcomprobantetipo)) {
				this.checkout.campos.codpersona_facturacion = 0;
				if ($("#codpersona_factura_hotel").length && $("#codpersona_factura_hotel").data("select2")) {
					$("#codpersona_factura_hotel").val(null).trigger("change");
				}
			}
			this.$http.get(url+"caja/controlcajas/phuyu_seriescaja/"+this.checkout.campos.codcomprobantetipo).then(function(data){
				this.seriesLista = data.body.series;
				var serieDefault = $("#hotel_serie_default").val() || "";
				var existeSerieDefault = (this.seriesLista || []).some(function(item){
					return item.seriecomprobante == serieDefault;
				});
				this.checkout.campos.seriecomprobante = existeSerieDefault ? serieDefault : data.body.serie;
				this.correlativo();
			});
			this.$nextTick(function(){
				this.iniciar_select_factura();
			});
		},
		iniciar_select_factura: function(){
			if (!this.es_factura(this.checkout.campos.codcomprobantetipo)) { return; }
			var vm = this;
			var $select = $("#codpersona_factura_hotel");
			if (!$select.length || typeof $.fn.select2 === "undefined") { return; }
			if ($select.data("select2")) { $select.select2("destroy"); }
			$select.empty().select2({
				placeholder: "Buscar cliente con RUC",
				minimumInputLength: 1,
				dropdownParent: $("#modal_checkout"),
				ajax: {
					url: url+"ventas/clientes/buscar",
					dataType: "json",
					delay: 250,
					data: function(params){
						return {search:{value:params.term || "", tipo:1}};
					},
					processResults: function(data){
						return {results:data.data || []};
					},
					cache: true
				},
				escapeMarkup: function(markup){ return markup; },
				templateResult: function(result){
					if (result.loading) { return result.text; }
					return "<div><strong>"+(result.documento || "")+"</strong><div class='text-muted small'>"+(result.razonsocial || result.text || "")+"</div></div>";
				},
				templateSelection: function(result){
					return result.razonsocial || result.text || "";
				}
			}).on("select2:select", function(e){
				var persona = e.params.data || {};
				var codpersona = persona.codpersona || persona.id || 0;
				vm.checkout.campos.codpersona_facturacion = codpersona;
				if (!codpersona) { return; }
				vm.$http.get(url+"ventas/clientes/infocliente/"+codpersona).then(function(data){
					var info = data.body[0] || {};
					vm.checkout.facturacion = {
						codpersona: codpersona,
						cliente: info.razonsocial || persona.razonsocial || persona.text || "",
						documento: info.documento || persona.documento || "",
						coddocumentotipo: parseInt(info.coddocumentotipo || 0),
						direccion: info.direccion || ""
					};
					if (parseInt(vm.checkout.campos.condicionpago || 1) == 2) {
						vm.lineas_credito_checkout();
					}
				});
			});
		},
		usar_hospedado_para_factura: function(){
			if (!this.estadia.estadia) { return false; }
			if (this.es_cliente_varios(this.estadia.estadia.codpersona)) { return false; }
			var documento = String(this.estadia.estadia.documento || "").trim();
			if (parseInt(this.estadia.estadia.coddocumentotipo || 0) != 4 || documento.length != 11) {
				return false;
			}
			this.checkout.campos.codpersona_facturacion = this.estadia.estadia.codpersona;
			this.checkout.facturacion = {
				codpersona: this.estadia.estadia.codpersona,
				cliente: this.estadia.estadia.cliente || "",
				documento: documento,
				coddocumentotipo: 4,
				direccion: this.estadia.estadia.direccion || ""
			};
			return true;
		},
		validar_comprobante_pago: function(){
			var comprobante = parseInt(this.checkout.campos.codcomprobantetipo || 0);
			if (this.es_factura(comprobante)) {
				if (!this.checkout.campos.codpersona_facturacion && !this.usar_hospedado_para_factura()) {
					phuyu_sistema.phuyu_noti("SELECCIONE CLIENTE PARA FACTURA", "Debe elegir a nombre de quien va la factura", "error");
					return false;
				}
				if (this.es_cliente_varios(this.checkout.campos.codpersona_facturacion)) {
					phuyu_sistema.phuyu_noti("CLIENTES VARIOS NO PERMITE FACTURA", "Seleccione un cliente con RUC", "error");
					return false;
				}
				if (parseInt(this.checkout.facturacion.coddocumentotipo || 0) != 4 || String(this.checkout.facturacion.documento || "").length != 11) {
					phuyu_sistema.phuyu_noti("FACTURA REQUIERE RUC", "Seleccione un cliente con RUC de 11 digitos", "error");
					return false;
				}
			}
			if (this.es_boleta(comprobante)) {
				var doc = String((this.estadia.estadia && this.estadia.estadia.documento) || "");
				if (parseInt((this.estadia.estadia && this.estadia.estadia.coddocumentotipo) || 0) == 4) {
					phuyu_sistema.phuyu_noti("BOLETA NO PUEDE EMITIRSE A RUC", "Use factura para clientes con RUC o nota de venta", "error");
					return false;
				}
				if (!doc || doc.length != 8) {
					phuyu_sistema.phuyu_noti("DNI INVALIDO PARA BOLETA", "El documento debe tener 8 digitos", "error");
					return false;
				}
			}
			return true;
		},
		condicion_pago_checkout: function(){
			if (parseInt(this.checkout.campos.condicionpago || 1) == 2) {
				this.checkout.pagos.monto_efectivo = 0;
				this.checkout.pagos.monto_tarjeta = 0;
				this.checkout.pagos.vuelto_efectivo = 0;
				this.lineas_credito_checkout();
				this.cuotas_checkout();
			}else{
				this.checkout.campos.codlote = 0;
				this.checkout.cuotas = [];
				this.checkout.campos.totalcredito = this.total_pago_actual();
				this.checkout.pagos.monto_efectivo = this.total_pago_actual();
				this.vuelto();
			}
		},
		lineas_credito_checkout: function(){
			var codpersona = this.persona_credito_checkout();
			if (!codpersona || this.es_cliente_varios(codpersona)) {
				this.checkout.campos.codlote = 0;
				$("#codlote_hotel").empty();
				return;
			}
			this.$http.get(url+"ventas/lineascredito/phuyu_lineascredito/"+codpersona).then(function(data){
				$("#codlote_hotel").empty().html(data.body);
				this.checkout.campos.codlote = $("#codlote_hotel").val() || 0;
			});
		},
		persona_credito_checkout: function(){
			if (this.es_factura(this.checkout.campos.codcomprobantetipo) && this.checkout.campos.codpersona_facturacion) {
				return this.checkout.campos.codpersona_facturacion;
			}
			return this.estadia.estadia ? this.estadia.estadia.codpersona : 0;
		},
		linea_credito_directa_checkout: function(){
			var codpersona = this.persona_credito_checkout();
			if (!codpersona || this.es_cliente_varios(codpersona)) {
				phuyu_sistema.phuyu_noti("SELECCIONE CLIENTE", "No se puede generar credito a clientes varios", "error");
				return false;
			}
			this.$http.post(url+"ventas/lineascredito/guardarlineascreditodirecto", {"codpersona":codpersona}).then(function(data){
				if (data.body == 1) {
					phuyu_sistema.phuyu_noti("LINEA DE CREDITO AGREGADA", "", "success");
					this.lineas_credito_checkout();
				}else{
					phuyu_sistema.phuyu_noti("NO SE PUDO CREAR LINEA", "", "error");
				}
			});
		},
		cuotas_checkout: function(){
			var nrocuotas = parseInt(this.checkout.campos.nrocuotas || 0);
			var total = this.total_pago_actual();
			this.checkout.cuotas = [];
			if (nrocuotas <= 0) {
				this.checkout.campos.totalcredito = total;
				return;
			}
			var fecha = new Date();
			fecha.setHours(0, 0, 0, 0);
			var base = Number((total / nrocuotas).toFixed(2));
			var acumulado = 0;
			for (var i = 1; i <= nrocuotas; i++) {
				var dias = parseInt(this.checkout.campos.nrodias || 0);
				if (dias > 0) { fecha.setDate(fecha.getDate() + dias); }
				var importe = (i == nrocuotas) ? Number((total - acumulado).toFixed(2)) : base;
				acumulado = Number((acumulado + importe).toFixed(2));
				this.checkout.cuotas.push({
					nrocuota: i,
					fechavence: fecha.getFullYear()+"-"+String(fecha.getMonth()+1).padStart(2, "0")+"-"+String(fecha.getDate()).padStart(2, "0"),
					nroletra: String(i).padStart(2, "0"),
					nrounicodepago: "H"+(this.checkout.campos.codestadia || "")+"-"+String(i).padStart(2, "0"),
					importe: importe < 0 ? 0 : importe,
					interes: 0,
					total: importe < 0 ? 0 : importe
				});
			}
			this.calcular_credito_checkout();
		},
		calcular_credito_checkout: function(){
			var tasa = this.toNum(this.checkout.campos.tasainteres);
			var totalInteres = 0;
			var totalCredito = 0;
			for (var i = 0; i < this.checkout.cuotas.length; i++) {
				var cuota = this.checkout.cuotas[i];
				var dias = this.diff_dias(i === 0 ? this.fecha_hoy() : this.checkout.cuotas[i - 1].fechavence, cuota.fechavence);
				var interes = tasa > 0 ? this.toNum(cuota.importe) * (tasa / 100) * (dias / 30) : 0;
				cuota.interes = Number(interes.toFixed(2));
				cuota.total = Number((this.toNum(cuota.importe) + cuota.interes).toFixed(2));
				totalInteres += cuota.interes;
				totalCredito += cuota.total;
			}
			this.checkout.campos.totalcredito = Number(totalCredito.toFixed(2));
		},
		pago_tarjeta_checkout: function(){
			if (parseInt(this.checkout.pagos.codtipopago_tarjeta || 0) == 0) {
				this.checkout.pagos.monto_tarjeta = 0;
				this.checkout.pagos.nrovoucher = "";
				if (parseInt(this.checkout.campos.condicionpago || 1) == 1) {
					this.checkout.pagos.monto_efectivo = this.total_pago_actual();
				}
			}
			this.vuelto(1);
		},
		correlativo: function(){
			if (!this.checkout.campos.codcomprobantetipo || !this.checkout.campos.seriecomprobante) { return; }
			this.$http.get(url+"caja/controlcajas/phuyu_correlativo/"+this.checkout.campos.codcomprobantetipo+"/"+this.checkout.campos.seriecomprobante).then(function(data){
				this.checkout.campos.nro = data.body;
			});
		},
		vuelto: function(ajustarEfectivo){
			if (ajustarEfectivo == 1 && parseInt(this.checkout.campos.condicionpago || 1) == 1) {
				this.checkout.pagos.monto_efectivo = Math.max(0, this.total_pago_actual() - Number(this.checkout.pagos.monto_tarjeta || 0));
			}
			var vuelto = Number(this.checkout.pagos.monto_efectivo || 0) + Number(this.checkout.pagos.monto_tarjeta || 0) - this.total_pago_actual();
			this.checkout.pagos.vuelto_efectivo = vuelto > 0 ? Number(vuelto.toFixed(2)) : 0;
		},
		checkout_estadia: function(){
			if (this.cobrandoHotel) { return; }
			if (!this.validar_comprobante_pago()) { return; }
			var total = this.total_pago_actual();
			if (parseInt(this.checkout.campos.condicionpago || 1) == 2) {
				var codpersonaCredito = this.persona_credito_checkout();
				this.checkout.campos.codlote = $("#codlote_hotel").val() || this.checkout.campos.codlote || 0;
				if (!codpersonaCredito || this.es_cliente_varios(codpersonaCredito)) {
					phuyu_sistema.phuyu_noti("CREDITO NO PERMITIDO", "Seleccione un cliente valido, no clientes varios", "error");
					return;
				}
				if (!this.checkout.cuotas.length) {
					this.cuotas_checkout();
				}
			}
			if (parseInt(this.checkout.campos.condicionpago) == 1) {
				var pagado = Number(this.checkout.pagos.monto_efectivo || 0) + Number(this.checkout.pagos.monto_tarjeta || 0) - Number(this.checkout.pagos.vuelto_efectivo || 0);
				if (pagado < total) {
					phuyu_sistema.phuyu_noti("PAGO INSUFICIENTE", "FALTA S/. " + Number(total - pagado).toFixed(2), "error");
					return;
				}
			}
			var endpoint = this.checkout.tipo == "consumos" ? "hotel/estadias/cobrar_consumos" : (this.checkout.tipo == "ocupacion" ? "hotel/estadias/cobrar_ocupacion" : "hotel/estadias/checkout");
			this.cobrandoHotel = true;
			this.$http.post(url+endpoint, this.checkout).then(function(data){
				this.cobrandoHotel = false;
				if (data.body.estado == 1) {
					this.modal_bootstrap("#modal_checkout", "hide");
					var mensaje = this.checkout.tipo == "consumos" ? "CONSUMOS COBRADOS" : (this.checkout.tipo == "ocupacion" ? "OCUPACION COBRADA" : "CHECK-OUT REGISTRADO");
					phuyu_sistema.phuyu_noti(mensaje, data.body.codkardex > 0 ? "VENTA 000"+data.body.codkardex : (data.body.mensaje || ""), "success");
					if (data.body.codkardex > 0) {
						window.open(url+"facturacion/formato/ticket/"+data.body.codkardex, "_blank");
					}
					if (this.checkout.tipo == "checkout") {
						this.habitacionActiva = null;
						this.estadia = {};
					}else{
						this.cargar_estadia(this.habitacionActiva.codhabitacion);
					}
					this.cargar_habitaciones();
				}else{
					phuyu_sistema.phuyu_alerta(data.body.mensaje || "NO SE PUDO COBRAR LA ESTADIA", "", "error");
				}
			}, function(){
				this.cobrandoHotel = false;
				phuyu_sistema.phuyu_alerta("ERROR DE RED", "NO SE PUDO COBRAR LA ESTADIA", "error");
			});
		},
		cancelar_ocupacion: function(){
			if (this.cancelacion.procesando) { return; }
			if (!this.estadia.estadia || !this.estadia.estadia.codestadia) {
				phuyu_sistema.phuyu_noti("NO HAY ESTADIA ACTIVA", "", "error");
				return;
			}
			if (!(this.cancelacion.motivo || "").trim()) {
				phuyu_sistema.phuyu_noti("INGRESE MOTIVO", "REQUERIDO PARA AUDITORIA", "error");
				return;
			}
			this.cancelacion.procesando = true;
			this.$http.post(url+"hotel/estadias/cancelar_ocupacion", {
				codestadia: this.estadia.estadia.codestadia,
				motivo: this.cancelacion.motivo,
				confirmar_pagos: this.cancelacion.confirmar_pagos
			}).then(function(data){
				this.cancelacion.procesando = false;
				if (data.body.estado == 1) {
					this.modal_bootstrap("#modal_cancelar_ocupacion", "hide");
					phuyu_sistema.phuyu_noti(data.body.mensaje || "OCUPACION CANCELADA", "", "success");
					this.habitacionActiva = null;
					this.estadia = {};
					this.cargar_habitaciones();
					return;
				}
				if (data.body.requiere_confirmacion == 1) {
					var vm = this;
					swal({
						title: "La estadia tiene pagos",
						text: "Pagos registrados: S/. " + Number(data.body.total_pagado || 0).toFixed(2) + ". La cancelacion no elimina esos comprobantes ni pagos.",
						icon: "warning",
						buttons: ["Volver", "Confirmar cancelacion"],
						dangerMode: true
					}).then(function(confirmado){
						if (!confirmado) { return; }
						vm.cancelacion.confirmar_pagos = 1;
						vm.cancelar_ocupacion();
					});
					return;
				}
				phuyu_sistema.phuyu_alerta(data.body.mensaje || "NO SE PUDO CANCELAR LA OCUPACION", "", "error");
			}, function(){
				this.cancelacion.procesando = false;
				phuyu_sistema.phuyu_alerta("ERROR DE RED", "NO SE PUDO CANCELAR LA OCUPACION", "error");
			});
		}
	},
	created: function(){
		this.controlaStockSistema = parseInt($("#sessionstockalmacen").val() || 0) || 0;
		this.cargar_habitaciones();
	}
});
