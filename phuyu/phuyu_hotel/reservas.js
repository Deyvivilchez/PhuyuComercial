(function(){
	if (document.getElementById("phuyu-hotel-reservas-style")) { return; }
	var css = ""+
		"#phuyu_hotel_reservas.hotel-reservas-app{--hz-border:var(--vz-border-color,#e9ebec);--hz-muted:var(--vz-secondary-color,#878a99);--hz-text:var(--vz-body-color,#212529);display:block}"+
		"#phuyu_hotel_reservas .hotel-page{display:flex!important;flex-direction:column!important;gap:1rem!important;padding-bottom:1rem!important}"+
		"#phuyu_hotel_reservas .hotel-topbar{display:flex!important;align-items:center!important;justify-content:space-between!important;gap:1rem!important;flex-wrap:wrap!important;margin:0!important;padding:1.1rem 1.25rem!important;border:1px solid var(--hz-border)!important;border-radius:12px!important;background:linear-gradient(135deg,#fff 0%,#f7f9ff 100%)!important;box-shadow:0 8px 26px rgba(56,65,74,.08)!important}"+
		"#phuyu_hotel_reservas .hotel-topbar h4{margin:0!important;font-size:1.15rem!important;font-weight:800!important;color:#343a40!important;display:flex!important;align-items:center!important;gap:.5rem!important}"+
		"#phuyu_hotel_reservas .hotel-topbar .text-muted{margin-top:.4rem!important;font-size:.82rem!important;color:#878a99!important}"+
		"#phuyu_hotel_reservas .hotel-topbar .btn{height:40px!important;display:inline-flex!important;align-items:center!important;border-radius:7px!important;padding:0 1rem!important;box-shadow:0 6px 16px rgba(64,81,137,.18)!important}"+
		"#phuyu_hotel_reservas .hotel-filters{display:block!important;border:1px solid var(--hz-border)!important;background:#fff!important;border-radius:12px!important;padding:1rem!important;box-shadow:0 8px 26px rgba(56,65,74,.06)!important}"+
		"#phuyu_hotel_reservas .hotel-filter-grid{display:grid!important;grid-template-columns:1.15fr 1fr 1fr .72fr!important;gap:.85rem!important;align-items:end!important}"+
		"#phuyu_hotel_reservas .hotel-filter-field{min-width:0!important}"+
		"#phuyu_hotel_reservas .hotel-filter-field label{display:block!important;margin:0 0 .38rem!important;font-size:.68rem!important;font-weight:800!important;letter-spacing:.04em!important;text-transform:uppercase!important;color:#687385!important}"+
		"#phuyu_hotel_reservas .hotel-filter-field .form-select,#phuyu_hotel_reservas .hotel-filter-field .form-control{height:40px!important;min-height:40px!important;border-radius:7px!important;border:1px solid #d8dee9!important;background-color:#fff!important;font-size:.78rem!important;box-shadow:none!important}"+
		"#phuyu_hotel_reservas .hotel-status-strip{display:grid!important;grid-template-columns:repeat(6,minmax(0,1fr))!important;gap:.65rem!important;margin-top:1rem!important}"+
		"#phuyu_hotel_reservas .hotel-status-pill{display:flex!important;align-items:center!important;gap:.55rem!important;min-height:46px!important;border:1px solid #e9ebec!important;border-radius:10px!important;background:#fbfcff!important;padding:.55rem .7rem!important;font-size:.72rem!important;font-weight:800!important;color:#687385!important;box-shadow:none!important;white-space:nowrap!important}"+
		"#phuyu_hotel_reservas .hotel-status-pill b{font-size:1.05rem!important;line-height:1!important;color:#212529!important}"+
		"#phuyu_hotel_reservas .hotel-status-dot{width:.55rem!important;height:.55rem!important;border-radius:50%!important;display:inline-block!important;flex:0 0 auto!important}"+
		"#phuyu_hotel_reservas .hotel-heatmap-card,#phuyu_hotel_reservas .hotel-planner{border:1px solid var(--hz-border)!important;background:#fff!important;border-radius:12px!important;box-shadow:0 8px 26px rgba(56,65,74,.06)!important;overflow:hidden!important}"+
		"#phuyu_hotel_reservas .hotel-heatmap-head,#phuyu_hotel_reservas .hotel-planner-head{display:flex!important;align-items:center!important;justify-content:space-between!important;gap:1rem!important;flex-wrap:wrap!important;padding:1rem 1.15rem!important;border-bottom:1px solid var(--hz-border)!important;background:#fff!important}"+
		"#phuyu_hotel_reservas .hotel-heatmap-head h5,#phuyu_hotel_reservas .hotel-planner-head h5{margin:0!important;font-size:.98rem!important;font-weight:800!important;color:#343a40!important}"+
		"#phuyu_hotel_reservas .hotel-heatmap-head .small,#phuyu_hotel_reservas .hotel-planner-head .small{margin-top:.25rem!important;color:#878a99!important;font-size:.76rem!important}"+
		"#phuyu_hotel_reservas .hotel-legend{display:flex!important;flex-wrap:wrap!important;gap:.45rem!important;align-items:center!important}"+
		"#phuyu_hotel_reservas .hotel-legend .badge{border-radius:999px!important;padding:.4rem .62rem!important;font-weight:800!important}"+
		"#phuyu_hotel_reservas .hotel-heatmap-body{padding:.85rem 1rem .6rem!important;background:#fff!important}"+
		"#phuyu_hotel_reservas #reservas_ocupacion_heatmap{min-height:260px!important}"+
		"#phuyu_hotel_reservas .hotel-heatmap-body .apexcharts-heatmap-rect{cursor:pointer!important}"+
		"#phuyu_hotel_reservas .hotel-planner-body{padding:.85rem!important;background:#f6f8fb!important}"+
		"#phuyu_hotel_reservas .hotel-calendar-wrap{width:100%!important;max-width:100%!important;border:1px solid var(--hz-border)!important;border-radius:12px!important;overflow:hidden!important;background:#fff!important;box-shadow:inset 0 1px 0 rgba(255,255,255,.75)!important}"+
		"#phuyu_hotel_reservas .hotel-calendar-board{overflow:auto!important;width:100%!important;height:calc(100vh - 315px)!important;min-height:480px!important;max-height:720px!important;background:#fff!important}"+
		"#phuyu_hotel_reservas .hotel-calendar-board::-webkit-scrollbar{height:10px!important;width:10px!important}"+
		"#phuyu_hotel_reservas .hotel-calendar-board::-webkit-scrollbar-thumb{background:#cbd5e1!important;border-radius:999px!important;border:2px solid #f8fafc!important}"+
		"#phuyu_hotel_reservas .hotel-calendar-board::-webkit-scrollbar-track{background:#f8fafc!important}"+
		"#phuyu_hotel_reservas .hotel-calendar-table{width:max-content!important;min-width:100%!important;margin:0!important;border-collapse:separate!important;border-spacing:0!important;table-layout:fixed!important;background:#fff!important}"+
		"#phuyu_hotel_reservas .hotel-calendar-table th{position:sticky!important;top:0!important;z-index:4!important;width:62px!important;min-width:62px!important;max-width:62px!important;height:54px!important;padding:.42rem .25rem!important;text-align:center!important;font-size:.68rem!important;font-weight:800!important;color:#495057!important;vertical-align:middle!important;background:#f8f9fa!important;border-bottom:1px solid var(--hz-border)!important;border-left:1px solid #edf1f7!important}"+
		"#phuyu_hotel_reservas .hotel-calendar-table th.weekend,#phuyu_hotel_reservas .hotel-calendar-table td.weekend{background:#fbfcff!important}"+
		"#phuyu_hotel_reservas .hotel-calendar-table th.today{background:#eef4ff!important;color:#405189!important;box-shadow:inset 0 -2px 0 #405189!important}"+
		"#phuyu_hotel_reservas .hotel-calendar-table td{width:62px!important;min-width:62px!important;max-width:62px!important;height:58px!important;padding:4px!important;border-bottom:1px solid #edf1f7!important;border-left:1px solid #edf1f7!important;background:#fff!important;vertical-align:middle!important;text-align:center!important}"+
		"#phuyu_hotel_reservas .hotel-calendar-table tbody tr:hover td:not(.hotel-calendar-room){background:#f9fbff!important}"+
		"#phuyu_hotel_reservas .hotel-calendar-room{position:sticky!important;left:0!important;z-index:7!important;width:190px!important;min-width:190px!important;max-width:190px!important;background:#fff!important;border-left:0!important;border-right:1px solid var(--hz-border)!important;box-shadow:5px 0 12px rgba(56,65,74,.05)!important;padding:.62rem .78rem!important;color:#343a40!important;vertical-align:middle!important;text-align:left!important;font-weight:800!important}"+
		"#phuyu_hotel_reservas .hotel-calendar-table thead .hotel-calendar-room{top:0!important;z-index:10!important;background:#f8f9fa!important;color:#687385!important;text-transform:uppercase!important;font-size:.68rem!important;letter-spacing:.04em!important}"+
		"#phuyu_hotel_reservas .hotel-room-title{font-size:.88rem!important;font-weight:800!important;color:#343a40!important;text-transform:none!important;line-height:1.1!important}"+
		"#phuyu_hotel_reservas .hotel-calendar-room small{display:block!important;margin-top:.16rem!important;color:#878a99!important;font-size:.68rem!important;font-weight:700!important;line-height:1.2!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important}"+
		"#phuyu_hotel_reservas .hotel-day{height:100%!important;min-height:50px!important;position:relative!important;cursor:pointer!important;overflow:hidden!important;display:flex!important;align-items:center!important;justify-content:center!important;transition:.12s ease!important;border-radius:7px!important;background:#f8f9fa!important;border:1px solid transparent!important}"+
		"#phuyu_hotel_reservas .hotel-day:hover{box-shadow:inset 0 0 0 2px rgba(64,81,137,.22)!important;background:#fff!important;transform:translateY(-1px)!important}"+
		"#phuyu_hotel_reservas .hotel-day.libre{background:#e8f7f3!important;border-color:#c6efe6!important}"+
		"#phuyu_hotel_reservas .hotel-day.libre:before{content:none!important}"+
		"#phuyu_hotel_reservas .hotel-day.selected{outline:2px solid #405189!important;outline-offset:-2px!important}"+
		"#phuyu_hotel_reservas .hotel-day-free-dot{width:8px!important;height:8px!important;border-radius:50%!important;background:#0ab39c!important;box-shadow:0 0 0 5px rgba(10,179,156,.13)!important;display:block!important}"+
		"#phuyu_hotel_reservas .hotel-day-free-label{display:flex!important;align-items:center!important;justify-content:center!important;width:100%!important;height:100%!important}"+
		"#phuyu_hotel_reservas .hotel-day-event{width:100%!important;height:100%!important;border-radius:7px!important;padding:.36rem .28rem!important;font-size:.62rem!important;line-height:1.08!important;font-weight:800!important;white-space:normal!important;overflow:hidden!important;display:flex!important;flex-direction:column!important;align-items:center!important;justify-content:center!important;gap:.1rem!important;text-align:center!important;box-shadow:none!important}"+
		"#phuyu_hotel_reservas .hotel-day-event strong{display:block!important;max-width:100%!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;font-size:.62rem!important;line-height:1.05!important}"+
		"#phuyu_hotel_reservas .hotel-day-event span{display:block!important;max-width:100%!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;font-size:.56rem!important;line-height:1!important;opacity:.82!important}"+
		"#phuyu_hotel_reservas .hotel-day.reserva .hotel-day-event{background:#e8f3ff!important;color:#299cdb!important;border:1px solid #cde8fb!important}"+
		"#phuyu_hotel_reservas .hotel-day.pendiente .hotel-day-event{background:#fff7e6!important;color:#c88405!important;border:1px solid #ffe7b5!important}"+
		"#phuyu_hotel_reservas .hotel-day.estadia .hotel-day-event{background:#fdeeea!important;color:#f06548!important;border:1px solid #fad4ca!important}"+
		"#phuyu_hotel_reservas .hotel-day.mantenimiento .hotel-day-event{background:#f3f6f9!important;color:#687385!important;border:1px solid #e4e8ee!important}"+
		"#phuyu_hotel_reservas .hotel-day.limpieza .hotel-day-event{background:#f1edff!important;color:#405189!important;border:1px solid #ddd5ff!important}"+
		"#phuyu_hotel_reservas .hotel-reserva-form .select2-container{width:100%!important}"+
		"#phuyu_hotel_reservas .hotel-reserva-form .select2-selection--single{display:flex!important;align-items:center!important;height:40px!important;min-height:40px!important;border:1px solid #d8dee9!important;border-radius:7px!important;background:#fff!important}"+
		"#phuyu_hotel_reservas .hotel-reserva-form .select2-selection__rendered{line-height:40px!important;padding-left:.75rem!important;padding-right:2rem!important;font-size:.86rem!important;font-weight:600!important;color:#343a40!important}"+
		"#phuyu_hotel_reservas .hotel-reserva-form .select2-selection__arrow{height:40px!important;right:.35rem!important}"+
		"#phuyu_hotel_reservas .hotel-client-add-btn{height:40px!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;border-radius:7px!important;font-weight:700!important}"+
		"@media(max-width:1199px){#phuyu_hotel_reservas .hotel-filter-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important}#phuyu_hotel_reservas .hotel-status-strip{grid-template-columns:repeat(3,minmax(0,1fr))!important}}"+
		"@media(max-width:575px){#phuyu_hotel_reservas .hotel-topbar{padding:1rem!important}#phuyu_hotel_reservas .hotel-filter-grid{grid-template-columns:1fr!important}#phuyu_hotel_reservas .hotel-status-strip{grid-template-columns:1fr 1fr!important}#phuyu_hotel_reservas .hotel-status-pill{white-space:normal!important}#phuyu_hotel_reservas .hotel-calendar-board{height:480px!important;min-height:480px!important;max-height:480px!important}#phuyu_hotel_reservas .hotel-calendar-table th,#phuyu_hotel_reservas .hotel-calendar-table td{width:56px!important;min-width:56px!important;max-width:56px!important}#phuyu_hotel_reservas .hotel-calendar-room{width:145px!important;min-width:145px!important;max-width:145px!important}}";
	var style = document.createElement("style");
	style.id = "phuyu-hotel-reservas-style";
	style.appendChild(document.createTextNode(css));
	document.head.appendChild(style);
})();

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
		mostrarGrilla: true,
		detalle: null,
		checkin: {codreserva:0, fecha_checkin:"", observacion:""},
		seleccionCalendario: {codhabitacion:0, desde:"", hasta:""},
		ocupacionHeatmap: null,
		ocupacionHeatmapIntentos: 0,
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
			this.refrescar_cliente_select(cliente);
		},
		refrescar_cliente_select: function(cliente){
			if (!window.jQuery) { return; }
			var $select = jQuery("#hotel_reserva_codpersona");
			if (!$select.length || !cliente || !cliente.codpersona) { return; }
			var texto = cliente.razonsocial || cliente.cliente || "Cliente";
			var option = new Option(texto, cliente.codpersona, true, true);
			$select.find("option").remove();
			$select.append(option).trigger("change.select2");
		},
		init_cliente_select: function(){
			if (!window.jQuery || !jQuery.fn.select2) { return; }
			var self = this;
			var $select = jQuery("#hotel_reserva_codpersona");
			if (!$select.length) { return; }
			if ($select.hasClass("select2-hidden-accessible")) {
				$select.off("select2:select.hotelReservas");
				$select.select2("destroy");
			}
			$select.select2({
				dropdownParent: jQuery(this.$refs.modalReserva || document.body),
				width: "100%",
				placeholder: "Buscar por documento o nombre",
				allowClear: true,
				minimumInputLength: 1,
				ajax: {
					url: url + "ventas/clientes/buscar",
					dataType: "json",
					delay: 250,
					data: function(params){
						return {search: {value: params.term || "", tipo: 0}, page: params.page};
					},
					processResults: function(data){
						return {results: data.data || []};
					},
					cache: true
				},
				escapeMarkup: function(markup){ return markup; },
				templateResult: function(result){
					if (result.loading) { return result.text; }
					var documento = result.documento || "";
					var nombre = result.razonsocial || result.text || "";
					return "<div><strong>" + documento + "</strong><div class='text-muted'>" + nombre + "</div></div>";
				},
				templateSelection: function(result){
					return result.razonsocial || result.text || self.form.cliente || "Seleccione cliente";
				}
			});
			$select.on("select2:select.hotelReservas", function(e){
				var cliente = e.params.data || {};
				self.form.codpersona = parseInt(cliente.codpersona || cliente.id || 0);
				self.form.cliente = cliente.razonsocial || cliente.text || "";
				self.form.documento = cliente.documento || "";
				self.buscarCliente = self.form.documento + " - " + self.form.cliente;
			});
			$select.on("select2:clear.hotelReservas", function(){
				self.form.codpersona = 0;
				self.form.cliente = "";
				self.form.documento = "";
				self.buscarCliente = "";
			});
			if (this.form.codpersona) {
				this.refrescar_cliente_select({
					codpersona: this.form.codpersona,
					razonsocial: this.form.cliente,
					documento: this.form.documento
				});
			}
		},
		phuyu_addcliente: function(){
			if (!window.jQuery) { return; }
			jQuery(".compose").removeClass("col-md-6").addClass("col-md-4");
			jQuery(".compose").slideDown();
			jQuery("#phuyu_tituloform").text("CREAR CLIENTE");
			phuyu_sistema.phuyu_loader("phuyu_formulario", 180);
			this.$http.post(url + "ventas/clientes/nuevo_1").then(function(data){
				jQuery("#phuyu_formulario").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			}, function(){
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
				phuyu_sistema.phuyu_alerta("NO SE PUDO CARGAR CLIENTE", "Intente nuevamente", "error");
			});
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
				this.$nextTick(this.render_ocupacion_heatmap);
			});
		},
		valor_heatmap_dia: function(dia){
			if (!dia.evento) { return 0; }
			if (dia.evento.tipo == "reserva" && parseInt(dia.evento.situacion) == 1) { return 25; }
			if (dia.evento.tipo == "reserva" && parseInt(dia.evento.situacion) == 2) { return 50; }
			if (dia.evento.tipo == "reserva" && parseInt(dia.evento.situacion) == 3) { return 80; }
			if (dia.evento.tipo == "estadia") { return 80; }
			if (dia.evento.tipo == "mantenimiento" || dia.evento.tipo == "limpieza") { return 100; }
			return 0;
		},
		render_ocupacion_heatmap: function(){
			if (!window.ApexCharts) {
				if (this.ocupacionHeatmapIntentos < 12) {
					var self = this;
					this.ocupacionHeatmapIntentos++;
					window.setTimeout(function(){ self.render_ocupacion_heatmap(); }, 250);
				}
				return;
			}
			this.ocupacionHeatmapIntentos = 0;
			var el = document.querySelector("#reservas_ocupacion_heatmap");
			if (!el) { return; }
			if (!this.cal.filas.length) {
				if (this.ocupacionHeatmap) { this.ocupacionHeatmap.destroy(); this.ocupacionHeatmap = null; }
				return;
			}
			var self = this;
			var series = this.cal.filas.map(function(fila){
				return {
					name: "Hab. " + fila.habitacion.numero,
					data: fila.dias.map(function(dia){
						return {
							x: String(parseInt((dia.fecha || "").slice(-2), 10) || dia.fecha),
							y: self.valor_heatmap_dia(dia),
							evento: dia.evento,
							fecha: dia.fecha
						};
					})
				};
			});
			var height = Math.min(Math.max(260, (series.length * 24) + 95), 520);
			var options = {
				series: series,
				chart: {
					height: height,
					type: "heatmap",
					toolbar: {show: false},
					animations: {enabled: false},
					events: {
						dataPointSelection: function(event, chartContext, config){
							self.click_heatmap_dia(config.seriesIndex, config.dataPointIndex);
						}
					}
				},
				dataLabels: {enabled: false},
				stroke: {width: 2, colors: ["#fff"]},
				plotOptions: {
					heatmap: {
						radius: 8,
						enableShades: false,
						colorScale: {
							ranges: [
								{from: 0, to: 0, name: "Libre", color: "#0ab39c"},
								{from: 1, to: 30, name: "Pendiente", color: "#f7b84b"},
								{from: 31, to: 60, name: "Confirmada", color: "#299cdb"},
								{from: 61, to: 90, name: "Ocupada", color: "#f06548"},
								{from: 91, to: 100, name: "Bloqueo", color: "#878a99"}
							]
						}
					}
				},
				xaxis: {type: "category", labels: {style: {fontSize: "11px"}}},
				yaxis: {labels: {style: {fontSize: "11px"}}},
				legend: {show: false},
				grid: {padding: {left: 8, right: 8, top: 0, bottom: 0}},
				tooltip: {
					custom: function(opts){
						var punto = opts.w.config.series[opts.seriesIndex].data[opts.dataPointIndex];
						var evento = punto.evento;
						var estado = "Libre";
						if (evento) { estado = evento.estado || evento.descripcion || "Ocupado"; }
						return "<div class='px-2 py-1'><strong>" + opts.w.config.series[opts.seriesIndex].name + "</strong><br>" + punto.fecha + "<br>" + estado + "</div>";
					}
				}
			};
			if (this.ocupacionHeatmap) {
				this.ocupacionHeatmap.updateOptions(options, true, true);
			}else{
				this.ocupacionHeatmap = new ApexCharts(el, options);
				this.ocupacionHeatmap.render();
			}
		},
		click_heatmap_dia: function(seriesIndex, dataPointIndex){
			var fila = this.cal.filas[seriesIndex];
			if (!fila || !fila.dias || !fila.dias[dataPointIndex]) { return; }
			this.click_dia(fila.habitacion, fila.dias[dataPointIndex]);
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
		clase_cabecera_dia: function(dia){
			if (!dia || !dia.fecha) { return ""; }
			var clases = [];
			var fecha = new Date(dia.fecha + "T00:00:00");
			var numeroDia = fecha.getDay();
			if (numeroDia === 0 || numeroDia === 6) { clases.push("weekend"); }
			if (dia.fecha === this.hoy()) { clases.push("today"); }
			return clases.join(" ");
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
				if (nombre == "modalReserva") { this.init_cliente_select(); }
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
