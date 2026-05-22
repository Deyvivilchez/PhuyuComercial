var phuyu_hotel_reservas = new Vue({
	el: "#phuyu_hotel_reservas",
	data: {
		buscar: "",
		buscarCliente: "",
		clientes: [],
		reservas: [],
		habitaciones: [],
		eventos: [],
		calendario: [],
		form: {
			codreserva: 0,
			codpersona: 0,
			cliente: "",
			documento: "",
			codambiente: 0,
			codhabitacion: 0,
			fechallegada: new Date().toISOString().slice(0,10),
			fechasalida: new Date(Date.now() + 86400000).toISOString().slice(0,10),
			situacion: 1,
			observacion: ""
		}
	},
	methods: {
		cargar_reservas: function(){
			this.$http.post(url+"hotel/reservas/lista", {buscar:this.buscar}).then(function(data){
				this.reservas = data.body;
			});
		},
		buscar_clientes: function(){
			if (this.buscarCliente.length < 2) { this.clientes = []; return; }
			this.$http.post(url+"hotel/reservas/clientes", {buscar:this.buscarCliente}).then(function(data){
				this.clientes = data.body;
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
				if (this.form.codhabitacion) {
					this.validar_disponibilidad(false);
				}else{
					this.armar_calendario();
				}
			});
		},
		seleccionar_habitacion: function(habitacion){
			this.form.codhabitacion = habitacion.codhabitacion;
			this.validar_disponibilidad(false);
		},
		validar_disponibilidad: function(mostrarMensaje){
			if (!this.form.codhabitacion) {
				if (mostrarMensaje) {
					phuyu_sistema.phuyu_noti("SELECCIONE HABITACION", "", "error");
				}
				return;
			}
			this.$http.post(url+"hotel/reservas/disponibilidad", this.form).then(function(data){
				this.eventos = data.body.eventos || [];
				this.habitaciones = data.body.habitaciones || this.habitaciones;
				this.armar_calendario();
				if (mostrarMensaje) {
					phuyu_sistema.phuyu_noti(data.body.mensaje, "", data.body.estado == 1 ? "success" : "error");
				}
			});
		},
		armar_calendario: function(){
			this.calendario = [];
			if (!this.form.fechallegada || !this.form.fechasalida || !this.form.codhabitacion) { return; }
			var desde = new Date(this.form.fechallegada + "T00:00:00");
			var hasta = new Date(this.form.fechasalida + "T00:00:00");
			if (hasta <= desde) { return; }
			var eventos = this.eventos;
			for (var fecha = new Date(desde); fecha < hasta; fecha.setDate(fecha.getDate() + 1)) {
				var iso = fecha.toISOString().slice(0,10);
				var evento = eventos.find(function(e){
					return e.desde <= iso && e.hasta > iso;
				});
				this.calendario.push({
					fecha: iso,
					ocupado: !!evento,
					descripcion: evento ? (evento.descripcion + " " + (evento.cliente || "")) : ""
				});
			}
		},
		habitacion_seleccionada_texto: function(){
			var codhabitacion = parseInt(this.form.codhabitacion);
			var habitacion = this.habitaciones.find(function(h){ return parseInt(h.codhabitacion) == codhabitacion; });
			return habitacion ? habitacion.numero : "";
		},
		guardar_reserva: function(){
			if (!this.form.codpersona || !this.form.codhabitacion) {
				phuyu_sistema.phuyu_noti("COMPLETE CLIENTE Y HABITACION", "", "error");
				return;
			}
			this.$http.post(url+"hotel/reservas/guardar", this.form).then(function(data){
				if (data.body.estado == 1) {
					phuyu_sistema.phuyu_noti("RESERVA REGISTRADA", "000"+data.body.codreserva, "success");
					this.limpiar();
					this.cargar_reservas();
				}else{
					phuyu_sistema.phuyu_alerta(data.body.mensaje || "NO SE PUDO GUARDAR RESERVA", "", "error");
				}
			});
		},
		cancelar_reserva: function(reserva){
			this.$http.post(url+"hotel/reservas/cancelar", {codreserva:reserva.codreserva}).then(function(data){
				if (data.body.estado == 1) {
					phuyu_sistema.phuyu_noti("RESERVA CANCELADA", "", "success");
					this.cargar_reservas();
				}
			});
		},
		situacion_texto: function(situacion){
			var estados = {1:"PENDIENTE",2:"CONFIRMADA",3:"CANCELADA",4:"NO SHOW",5:"FINALIZADA"};
			return estados[parseInt(situacion)] || "SIN ESTADO";
		},
		limpiar: function(){
			this.buscarCliente = "";
			this.clientes = [];
			this.form = {
				codreserva: 0,
				codpersona: 0,
				cliente: "",
				documento: "",
				codambiente: 0,
				codhabitacion: 0,
				fechallegada: new Date().toISOString().slice(0,10),
				fechasalida: new Date(Date.now() + 86400000).toISOString().slice(0,10),
				situacion: 1,
				observacion: ""
			};
			this.eventos = [];
			this.calendario = [];
			this.consultar_disponibilidad();
		}
	},
	watch: {
		"form.fechallegada": function(){ this.consultar_disponibilidad(); },
		"form.fechasalida": function(){ this.consultar_disponibilidad(); }
	},
	created: function(){
		this.cargar_reservas();
		this.consultar_disponibilidad();
	}
});
