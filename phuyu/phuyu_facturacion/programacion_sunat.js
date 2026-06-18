var phuyu_datos = new Vue({
	el: "#phuyu_datos",
	data: {
		empresas: [],
		sucursales: [],
		configuraciones: [],
		historial: [],
		cola: [],
		historial_paginacion: {total: 0, limite: 10, offset: 0},
		cola_paginacion: {total: 0, limite: 10, offset: 0},
		cron: {},
		form: {}
	},
	computed: {
		sucursalesFiltradas: function(){
			var empresa = parseInt(this.form.codempresa || 0);
			return this.sucursales.filter(function(item){
				return parseInt(item.codempresa) === empresa;
			});
		}
	},
	methods: {
		nuevo: function(){
			this.form = {
				codprogramacion: 0,
				codempresa: this.empresas.length ? parseInt(this.empresas[0].codempresa) : 1,
				codsucursal: 0,
				descripcion: "Envio automatico SUNAT",
				modo_envio: "programado",
				procesar_facturas: true,
				procesar_boletas: true,
				procesar_notas_credito: true,
				procesar_notas_debito: true,
				procesar_resumenes: true,
				procesar_bajas: true,
				max_intentos: 3,
				limite_por_ejecucion: 20,
				activo: true,
				horarios: [
					{hora: "08:00", accion: "todo"},
					{hora: "12:00", accion: "generar_resumen"},
					{hora: "18:00", accion: "reenviar"}
				]
			};
		},
		cargar: function(){
			phuyu_sistema.phuyu_inicio();
			var params = [
				"historial_limite=" + encodeURIComponent(this.historial_paginacion.limite || 10),
				"historial_offset=" + encodeURIComponent(this.historial_paginacion.offset || 0),
				"cola_limite=" + encodeURIComponent(this.cola_paginacion.limite || 10),
				"cola_offset=" + encodeURIComponent(this.cola_paginacion.offset || 0)
			].join("&");
			this.$http.get(url + phuyu_controller + "/datos?" + params).then(function(res){
				this.empresas = res.body.empresas || [];
				this.sucursales = res.body.sucursales || [];
				this.configuraciones = res.body.configuraciones || [];
				this.historial = res.body.historial || [];
				this.cola = res.body.cola || [];
				this.historial_paginacion = res.body.historial_paginacion || this.historial_paginacion;
				this.cola_paginacion = res.body.cola_paginacion || this.cola_paginacion;
				this.cron = res.body.cron || {};
				if (!this.form.descripcion) {
					this.nuevo();
				}
				phuyu_sistema.phuyu_fin();
			}, function(){
				phuyu_sistema.phuyu_alerta("No se puede cargar la programacion SUNAT", "Error de red", "error");
				phuyu_sistema.phuyu_fin();
			});
		},
		paginacionTexto: function(paginacion){
			var total = parseInt(paginacion.total || 0);
			if (total === 0) {
				return "Sin registros";
			}
			var offset = parseInt(paginacion.offset || 0);
			var limite = parseInt(paginacion.limite || 10);
			var desde = offset + 1;
			var hasta = Math.min(offset + limite, total);
			return "Mostrando " + desde + " - " + hasta + " de " + total;
		},
		cambiarPagina: function(tipo, direccion){
			var paginacion = tipo === "historial" ? this.historial_paginacion : this.cola_paginacion;
			var total = parseInt(paginacion.total || 0);
			var limite = parseInt(paginacion.limite || 10);
			var offset = parseInt(paginacion.offset || 0) + (direccion * limite);
			offset = Math.max(0, Math.min(offset, Math.max(0, total - 1)));
			offset = Math.floor(offset / limite) * limite;
			if (tipo === "historial") {
				this.historial_paginacion.offset = offset;
			} else {
				this.cola_paginacion.offset = offset;
			}
			this.cargar();
		},
		agregarHorario: function(){
			this.form.horarios.push({hora: "08:00", accion: "todo"});
		},
		quitarHorario: function(index){
			this.form.horarios.splice(index, 1);
		},
		editar: function(item){
			this.form = JSON.parse(JSON.stringify(item));
			this.form.codempresa = parseInt(this.form.codempresa);
			this.form.codsucursal = this.form.codsucursal ? parseInt(this.form.codsucursal) : 0;
			this.form.procesar_facturas = parseInt(this.form.procesar_facturas) === 1;
			this.form.procesar_boletas = parseInt(this.form.procesar_boletas) === 1;
			this.form.procesar_notas_credito = parseInt(this.form.procesar_notas_credito) === 1;
			this.form.procesar_notas_debito = parseInt(this.form.procesar_notas_debito) === 1;
			this.form.procesar_resumenes = parseInt(this.form.procesar_resumenes) === 1;
			this.form.procesar_bajas = parseInt(this.form.procesar_bajas) === 1;
			this.form.activo = parseInt(this.form.activo) === 1;
			this.form.horarios = this.form.horarios || [];
			if (this.form.horarios.length === 0) {
				this.agregarHorario();
			}
			window.scrollTo({top: 0, behavior: "smooth"});
		},
		guardar: function(){
			if (!this.form.horarios.length) {
				phuyu_sistema.phuyu_alerta("Agrega al menos un horario", "", "error");
				return;
			}
			phuyu_sistema.phuyu_inicio_guardar("Guardando programacion SUNAT...");
			this.$http.post(url + phuyu_controller + "/guardar", this.form).then(function(res){
				phuyu_sistema.phuyu_noti("SUNAT", res.body.mensaje, res.body.estado == 1 ? "success" : "error");
				this.cargar();
			}, function(){
				phuyu_sistema.phuyu_alerta("No se puede guardar", "Error de red", "error");
				phuyu_sistema.phuyu_fin();
			});
		},
		eliminar: function(item){
			swal({
				title: "Desactivar programacion",
				text: item.descripcion,
				icon: "warning",
				buttons: ["Cancelar", "Desactivar"],
				dangerMode: true
			}).then((ok) => {
				if (!ok) return;
				this.$http.get(url + phuyu_controller + "/eliminar/" + item.codprogramacion).then(function(res){
					phuyu_sistema.phuyu_noti("SUNAT", res.body.mensaje, res.body.estado == 1 ? "success" : "error");
					this.cargar();
				});
			});
		},
		ejecutar: function(item){
			phuyu_sistema.phuyu_inicio_guardar("Ejecutando envio manual...");
			this.$http.get(url + phuyu_controller + "/ejecutar_manual/" + item.codprogramacion).then(function(res){
				var respuesta = res.body || {};
				var detalle = respuesta.detalle || "";
				var accion = respuesta.accion_recomendada || "";
				var texto = detalle;
				if (accion) {
					texto += (texto ? "\n\n" : "") + "Accion recomendada: " + accion;
				}
				if (respuesta.codigo) {
					texto += (texto ? "\n\n" : "") + "Codigo interno: " + respuesta.codigo;
				}
				swal({
					title: respuesta.mensaje || "Resultado SUNAT",
					text: texto || "La ejecucion finalizo sin detalle adicional.",
					icon: respuesta.estado == 1 ? "success" : "warning"
				});
				this.cargar();
			}, function(){
				phuyu_sistema.phuyu_alerta("No se puede ejecutar", "Error de red", "error");
				phuyu_sistema.phuyu_fin();
			});
		},
		limpiarHistorial: function(){
			swal({
				title: "Limpiar historial SUNAT",
				text: "Se eliminara todo el historial de ejecuciones y las colas cerradas. No se tocaran pendientes, procesando ni errores activos.",
				icon: "warning",
				buttons: ["Cancelar", "Limpiar"],
				dangerMode: true
			}).then((ok) => {
				if (!ok) return;
				phuyu_sistema.phuyu_inicio_guardar("Limpiando historial SUNAT...");
				this.$http.post(url + phuyu_controller + "/limpiar_historial", {modo: "todo"}).then(function(res){
					var respuesta = res.body || {};
					swal({
						title: respuesta.mensaje || "Limpieza SUNAT",
						text: (respuesta.detalle || "") + (respuesta.accion_recomendada ? "\n\nAccion recomendada: " + respuesta.accion_recomendada : ""),
						icon: respuesta.estado == 1 ? "success" : "error"
					});
					this.historial_paginacion.offset = 0;
					this.cola_paginacion.offset = 0;
					this.cargar();
				}, function(){
					phuyu_sistema.phuyu_alerta("No se puede limpiar el historial", "Error de red", "error");
					phuyu_sistema.phuyu_fin();
				});
			});
		},
		verificarCron: function(){
			this.$http.get(url + phuyu_controller + "/cron_base").then(function(res){
				this.cron = res.body || {};
			}, function(){
				phuyu_sistema.phuyu_alerta("No se puede verificar el cron base", "Error de red", "error");
			});
		},
		crearCron: function(){
			var accion = parseInt(this.cron.existe || 0) === 1 ? "recrear" : "crear";
			swal({
				title: accion === "crear" ? "Crear cron base" : "Recrear cron base",
				text: "Se intentara escribir el archivo cron en /etc/cron.d/ para este proyecto.",
				icon: "warning",
				buttons: ["Cancelar", accion === "crear" ? "Crear" : "Recrear"],
				dangerMode: false
			}).then((ok) => {
				if (!ok) return;
				phuyu_sistema.phuyu_inicio_guardar("Actualizando cron base...");
				this.$http.post(url + phuyu_controller + "/crear_cron_base", {}).then(function(res){
					this.cron = res.body || {};
					phuyu_sistema.phuyu_noti("Cron SUNAT", res.body.mensaje, res.body.estado == 1 ? "success" : "error");
					phuyu_sistema.phuyu_fin();
				}, function(){
					phuyu_sistema.phuyu_alerta("No se puede crear el cron base", "Error de red", "error");
					phuyu_sistema.phuyu_fin();
				});
			});
		}
	},
	created: function(){
		this.nuevo();
		this.cargar();
	}
});
