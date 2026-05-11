var phuyu_hotel_reservas = new Vue({
	el: "#phuyu_hotel_reservas",
	data: {
		buscar: "",
		buscarCliente: "",
		clientes: [],
		reservas: [],
		habitaciones: [],
		eventos: [],
		modalReserva: false,
		modalDetalle: false,
		modalCheckin: false,
		detalle: null,
		checkin: {codreserva:0, fecha_checkin:"", observacion:""},
		seleccionCalendario: {codhabitacion:0, desde:"", hasta:""},
		cal: {
			mesvalor: "",
			desde: "",
			hasta: "",
			dias: [],
			filas: [],
			filtro: {codsucursal:0, codambiente:0, codhabitacion:0, anio:0, mes:0}
		},
		form: {
			codreserva: 0,
			codpersona: 0,
			cliente: "",
			documento: "",
			codambiente: 0,
			codhabitacion: 0,
			fechallegada: "",
			fechasalida: "",
			situacion: 1,
			observacion: ""
		}
	},
	computed: {
		resumen_estado: function(){
			var resumen = {libre:0, pendiente:0, confirmada:0, ocupada:0, mantenimiento:0, limpieza:0};
			this.cal.filas.forEach(function(fila){
				fila.dias.forEach(function(dia){
					if (!dia.evento) { resumen.libre++; return; }
					if (dia.evento.tipo == "reserva" && parseInt(dia.evento.situacion) == 1) { resumen.pendiente++; return; }
					if (dia.evento.tipo == "reserva" && parseInt(dia.evento.situacion) == 2) { resumen.confirmada++; return; }
					if (dia.evento.tipo == "reserva" && parseInt(dia.evento.situacion) == 3) { resumen.ocupada++; return; }
					if (dia.evento.tipo == "estadia") { resumen.ocupada++; return; }
					if (dia.evento.tipo == "mantenimiento") { resumen.mantenimiento++; return; }
					if (dia.evento.tipo == "limpieza") { resumen.limpieza++; return; }
				});
			});
			return resumen;
		}
	},
	methods: {
		hoy: function(){
			var d = new Date();
			return d.getFullYear()+"-"+String(d.getMonth()+1).padStart(2,"0")+"-"+String(d.getDate()).padStart(2,"0");
		},
		manana: function(){
			var d = new Date();
			d.setDate(d.getDate()+1);
			return d.getFullYear()+"-"+String(d.getMonth()+1).padStart(2,"0")+"-"+String(d.getDate()).padStart(2,"0");
		},
		cargar_reservas: function(){
			this.$http.post(url+"hotel/reservas/lista", {buscar:this.buscar}).then(function(data){
				this.reservas = data.body || [];
			});
		},
		buscar_clientes: function(){
			if (this.buscarCliente.length < 2) { this.clientes = []; return; }
			this.$http.post(url+"hotel/reservas/clientes", {buscar:this.buscarCliente}).then(function(data){
				this.clientes = data.body || [];
			});
		},
		seleccionar_cliente: function(cliente){
			this.form.codpersona = cliente.codpersona;
			this.form.cliente = cliente.razonsocial;
			this.form.documento = cliente.documento;
			this.buscarCliente = cliente.documento + " - " + cliente.razonsocial;
			this.clientes = [];
		},
		consultar_disponibilidad: function(){
			if (!this.form.fechallegada || !this.form.fechasalida) { return; }
			this.$http.post(url+"hotel/reservas/disponibilidad", {
				codreserva: this.form.codreserva,
				codambiente: this.form.codambiente,
				codhabitacion: 0,
				fechallegada: this.form.fechallegada,
				fechasalida: this.form.fechasalida
			}).then(function(data){
				this.habitaciones = data.body.habitaciones || [];
				if (this.form.codhabitacion) { this.validar_disponibilidad(false); }
			});
		},
		seleccionar_habitacion: function(habitacion){
			this.form.codhabitacion = habitacion.codhabitacion;
			this.validar_disponibilidad(false);
		},
		validar_disponibilidad: function(mostrarMensaje){
			if (!this.form.codhabitacion) {
				if (mostrarMensaje) { phuyu_sistema.phuyu_noti("SELECCIONE HABITACION", "", "error"); }
				return;
			}
			this.$http.post(url+"hotel/reservas/disponibilidad", this.form).then(function(data){
				this.eventos = data.body.eventos || [];
				this.habitaciones = data.body.habitaciones || this.habitaciones;
				if (mostrarMensaje) {
					phuyu_sistema.phuyu_noti(data.body.mensaje, "", data.body.estado == 1 ? "success" : "error");
				}
			});
		},
		guardar_reserva: function(){
			if (!this.form.codpersona || !this.form.codhabitacion) {
				phuyu_sistema.phuyu_noti("COMPLETE CLIENTE Y HABITACION", "", "error");
				return;
			}
			this.$http.post(url+"hotel/reservas/guardar", this.form).then(function(data){
				if (data.body.estado == 1) {
					phuyu_sistema.phuyu_noti("RESERVA REGISTRADA", "000"+data.body.codreserva, "success");
					this.cerrar_modal("modalReserva");
					this.limpiar();
					this.cargar_reservas();
					this.cargar_calendario();
				}else{
					phuyu_sistema.phuyu_alerta(data.body.mensaje || "NO SE PUDO GUARDAR RESERVA", "", "error");
				}
			});
		},
		confirmar_reserva: function(reserva){
			this.$http.post(url+"hotel/reservas/confirmar", {codreserva:reserva.codreserva}).then(function(data){
				if (data.body.estado == 1) {
					phuyu_sistema.phuyu_noti("RESERVA CONFIRMADA", "", "success");
					this.refrescar();
				}else{
					phuyu_sistema.phuyu_alerta(data.body.mensaje || "NO SE PUDO CONFIRMAR", "", "error");
				}
			});
		},
		anular_reserva: function(reserva){
			this.$http.post(url+"hotel/reservas/anular", {codreserva:reserva.codreserva}).then(function(data){
				if (data.body.estado == 1) {
					phuyu_sistema.phuyu_noti("RESERVA ANULADA", "", "success");
					this.cerrar_modal("modalDetalle");
					this.refrescar();
				}else{
					phuyu_sistema.phuyu_alerta(data.body.mensaje || "NO SE PUDO ANULAR", "", "error");
				}
			});
		},
		abrir_checkin: function(reserva){
			this.checkin = {codreserva:reserva.codreserva, fecha_checkin:this.hoy(), observacion:""};
			this.cerrar_y_abrir("modalDetalle", "modalCheckin");
		},
		realizar_checkin: function(){
			this.$http.post(url+"hotel/reservas/checkin", this.checkin).then(function(data){
				if (data.body.estado == 1) {
					phuyu_sistema.phuyu_noti("CHECK-IN REGISTRADO", "Estadia 000"+data.body.codestadia, "success");
					this.cerrar_modal("modalCheckin");
					this.cerrar_modal("modalDetalle");
					this.refrescar();
				}else{
					phuyu_sistema.phuyu_alerta(data.body.mensaje || "NO SE PUDO REGISTRAR CHECK-IN", "", "error");
				}
			});
		},
		editar_reserva: function(reserva){
			this.$http.post(url+"hotel/reservas/detalle", {codreserva:reserva.codreserva}).then(function(data){
				if (data.body.estado != 1) { return; }
				var r = data.body.reserva;
				this.form = {
					codreserva: r.codreserva,
					codpersona: r.codpersona,
					cliente: r.cliente,
					documento: r.documento,
					codambiente: 0,
					codhabitacion: r.codhabitacion,
					fechallegada: r.fechallegada,
					fechasalida: r.fechasalida,
					situacion: parseInt(r.situacion) == 2 ? 2 : 1,
					observacion: r.observacion || ""
				};
				this.buscarCliente = r.documento + " - " + r.cliente;
				this.cerrar_y_abrir("modalDetalle", "modalReserva");
				this.consultar_disponibilidad();
			});
		},
		ver_detalle: function(codreserva){
			this.$http.post(url+"hotel/reservas/detalle", {codreserva:codreserva}).then(function(data){
				if (data.body.estado == 1) {
					this.detalle = data.body.reserva;
					this.abrir_modal("modalDetalle");
				}
			});
		},
		cambiar_mes: function(){
			var partes = this.cal.mesvalor.split("-");
			this.cal.filtro.anio = parseInt(partes[0]);
			this.cal.filtro.mes = parseInt(partes[1]);
			this.cargar_calendario();
		},
		cargar_calendario: function(){
			this.$http.post(url+"hotel/reservas/calendario", this.cal.filtro).then(function(data){
				var body = data.body || {};
				this.cal.desde = body.desde || "";
				this.cal.hasta = body.hasta || "";
				this.cal.filas = body.calendario || [];
				this.cal.dias = this.cal.filas.length ? this.cal.filas[0].dias : [];
			});
		},
		clase_dia: function(dia){
			if (!dia.evento) { return "libre"; }
			if (dia.evento.tipo == "reserva" && parseInt(dia.evento.situacion) == 1) { return "pendiente"; }
			if (dia.evento.tipo == "reserva" && parseInt(dia.evento.situacion) == 3) { return "estadia"; }
			if (dia.evento.tipo == "reserva") { return "reserva"; }
			if (dia.evento.tipo == "estadia") { return "estadia"; }
			if (dia.evento.tipo == "mantenimiento") { return "mantenimiento"; }
			if (dia.evento.tipo == "limpieza") { return "limpieza"; }
			return "libre";
		},
		titulo_dia: function(dia){
			if (!dia.evento) { return dia.fecha + " libre"; }
			return dia.evento.descripcion + " " + (dia.evento.cliente || "") + " " + dia.evento.desde + " / " + dia.evento.hasta;
		},
		click_dia: function(habitacion, dia){
			if (dia.evento) {
				if (dia.evento.tipo == "reserva") {
					if (parseInt(dia.evento.situacion) == 1) {
						this.editar_reserva({codreserva:dia.evento.codigo});
					}else{
						this.ver_detalle(dia.evento.codigo);
					}
				}else{
					this.detalle = {
						codreserva: 0,
						cliente: dia.evento.cliente || dia.evento.descripcion,
						descripcion: dia.evento.descripcion,
						situacion: dia.evento.tipo == "estadia" ? 3 : 9,
						numero: habitacion.numero,
						ambiente: habitacion.ambiente,
						fechallegada: dia.evento.desde,
						fechasalida: dia.evento.hasta,
						observacion: dia.evento.estado || dia.evento.descripcion,
						tipo_evento: dia.evento.tipo
					};
					this.abrir_modal("modalDetalle");
				}
				return;
			}
			if (parseInt(this.cal.filtro.codsucursal) != parseInt(window.phuyu_hotel_codsucursal || 0)) {
				phuyu_sistema.phuyu_alerta("PARA REGISTRAR EN ESA SUCURSAL, INGRESE PRIMERO A ESA SUCURSAL", "", "warning");
				return;
			}
			this.limpiar(false);
			this.seleccionCalendario = {codhabitacion:habitacion.codhabitacion, desde:dia.fecha, hasta:this.sumar_dia(dia.fecha)};
			this.form.codhabitacion = habitacion.codhabitacion;
			this.form.fechallegada = dia.fecha;
			this.form.fechasalida = this.sumar_dia(dia.fecha);
			this.abrir_modal("modalReserva");
			this.consultar_disponibilidad();
		},
		rango_libre: function(codhabitacion, desde, hastaDia){
			var fila = this.cal.filas.find(function(f){ return parseInt(f.habitacion.codhabitacion) == parseInt(codhabitacion); });
			if (!fila) { return false; }
			return !fila.dias.some(function(d){ return d.fecha >= desde && d.fecha <= hastaDia && d.evento; });
		},
		sumar_dia: function(fecha){
			var d = new Date(fecha + "T00:00:00");
			d.setDate(d.getDate()+1);
			return d.getFullYear()+"-"+String(d.getMonth()+1).padStart(2,"0")+"-"+String(d.getDate()).padStart(2,"0");
		},
		es_seleccionado: function(codhabitacion, fecha){
			return this.seleccionCalendario.codhabitacion == codhabitacion && (this.seleccionCalendario.desde == fecha || this.seleccionCalendario.hasta == this.sumar_dia(fecha));
		},
		abrir_nueva: function(){
			this.limpiar(false);
			this.abrir_modal("modalReserva");
		},
		abrir_modal: function(nombre){
			this[nombre] = true;
			this.$nextTick(function(){
				var modal = this.$refs[nombre];
				if (window.bootstrap && modal) {
					var self = this;
					modal.addEventListener("hidden.bs.modal", function(){
						self[nombre] = false;
					}, {once:true});
					bootstrap.Modal.getOrCreateInstance(modal).show();
				}
			});
		},
		cerrar_modal: function(nombre){
			var modal = this.$refs[nombre];
			if (window.bootstrap && modal) {
				bootstrap.Modal.getOrCreateInstance(modal).hide();
			}else{
				this[nombre] = false;
			}
		},
		cerrar_y_abrir: function(origen, destino){
			var modal = this.$refs[origen];
			if (window.bootstrap && modal) {
				var self = this;
				modal.addEventListener("hidden.bs.modal", function(){
					self[origen] = false;
					self.abrir_modal(destino);
				}, {once:true});
				bootstrap.Modal.getOrCreateInstance(modal).hide();
			}else{
				this[origen] = false;
				this.abrir_modal(destino);
			}
		},
		situacion_texto: function(situacion){
			var estados = {0:"ANULADA",1:"PENDIENTE",2:"CONFIRMADA",3:"EN HOSPEDAJE",4:"FINALIZADA",9:"BLOQUEO"};
			return estados[parseInt(situacion)] || "SIN ESTADO";
		},
		clase_estado: function(situacion){
			situacion = parseInt(situacion);
			if (situacion == 0) { return "bg-danger-subtle text-danger"; }
			if (situacion == 1) { return "bg-warning-subtle text-warning"; }
			if (situacion == 2) { return "bg-info-subtle text-info"; }
			if (situacion == 3) { return "bg-success-subtle text-success"; }
			if (situacion == 4) { return "bg-secondary-subtle text-secondary"; }
			if (situacion == 9) { return "bg-danger-subtle text-danger"; }
			return "bg-light text-dark";
		},
		refrescar: function(){
			this.cargar_reservas();
			this.consultar_disponibilidad();
			this.cargar_calendario();
		},
		limpiar: function(refrescar){
			this.buscarCliente = "";
			this.clientes = [];
			this.form = {
				codreserva: 0,
				codpersona: 0,
				cliente: "",
				documento: "",
				codambiente: 0,
				codhabitacion: 0,
				fechallegada: this.hoy(),
				fechasalida: this.manana(),
				situacion: 1,
				observacion: ""
			};
			this.eventos = [];
			if (refrescar !== false) { this.consultar_disponibilidad(); }
		}
	},
	watch: {
		"form.fechallegada": function(){ this.consultar_disponibilidad(); },
		"form.fechasalida": function(){ this.consultar_disponibilidad(); }
	},
	created: function(){
		var d = new Date();
		this.cal.filtro.anio = d.getFullYear();
		this.cal.filtro.mes = d.getMonth()+1;
		this.cal.filtro.codsucursal = window.phuyu_hotel_codsucursal || 0;
		this.cal.mesvalor = d.getFullYear()+"-"+String(d.getMonth()+1).padStart(2,"0");
		this.limpiar(false);
		this.cargar_reservas();
		this.consultar_disponibilidad();
		this.cargar_calendario();
	}
});
