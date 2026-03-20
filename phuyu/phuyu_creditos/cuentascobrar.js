var phuyu_creditos = new Vue({
	el: "#phuyu_creditos",
	data: {
		cargando: true,
		registro: 0,
		codlote: 0,
		cobrado: parseInt(1),
		buscar: "",
		datos: [],
		sessioncaja: 0,
		rubro: $("#rubro").val(),
		paginacion: { "total": 0, "actual": 1, "ultima": 0, "desde": 0, "hasta": 0 }, 
		offset: 3
	},
	computed: {
		phuyu_actual: function () {
			return this.paginacion.actual;
		},
		phuyu_paginas: function () {
			if (!this.paginacion.hasta) {
				return [];
			}
			var desde = this.paginacion.actual - this.offset;
			if (desde < 1) {
				desde = 1;
			}
			var hasta = desde + (this.offset * 2);
			if (hasta >= this.paginacion.ultima) {
				hasta = this.paginacion.ultima;
			}

			var paginas = [];
			while (desde <= hasta) {
				paginas.push(desde); desde++;
			}
			return paginas;
		}
	},
	methods: {
		phuyu_datos: function () {
			this.cargando = true; this.registro = 0;
			this.$http.post(url + phuyu_controller + "/lista", { "buscar": this.buscar, "pagina": this.paginacion.actual, "zonas": $("#zonas").val() }).then(function (data) {
				this.datos = data.body.lista; this.paginacion = data.body.paginacion;
				this.cargando = false; phuyu_sistema.phuyu_fin();
			}, function () {
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS", "ERROR DE RED", "error"); this.cargando = false;
			});

			// VERIFICAMOS EL ESTADO DE LA CAJA //
			if ($("#sessioncaja").val() == 0) {
				this.sessioncaja = 0;
			} else {
				this.sessioncaja = 1;
			}
		},
		phuyu_buscar: function () {
			this.paginacion.actual = 1; this.phuyu_datos();
		},
		phuyu_paginacion: function (pagina) {
			this.paginacion.actual = pagina; this.phuyu_datos();
		},

		phuyu_seleccionar: function (registro, codlote) {
			this.registro = registro;
			this.codlote = codlote;
		},
		phuyu_nuevo: function () {
			if ($("#comprobante").val() == 0) {
				phuyu_sistema.phuyu_alerta("ATENCION USUARIO", "DEBES CONFIGURAR LOS COMPROBANTES DE CREDITO", "error"); return;
			}
			if (this.registro == 0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UN REGISTRO", "PARA REGISTRAR UN NUEVO CREDITO !!!", "error");
			} else {
				phuyu_sistema.phuyu_inicio();
				this.$http.get(url + phuyu_controller + "/nuevo/" + this.registro + '/' + this.codlote).then(function (data) {
					$("#phuyu_sistema").empty().html(data.body);
				}, function () {
					phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED", "error");
					phuyu_sistema.phuyu_fin();
				});
			}
		},

		phuyu_cobranza_original: function (cobrado=0) {
			if (this.registro == 0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UN REGISTRO", "PARA REALIZAR LA COBRANZA DE UN CREDITO !!!", "error");
			} else {
				this.cobrado = cobrado;
				phuyu_sistema.phuyu_inicio();
				this.$http.get(url + phuyu_controller + "/cobranza/" + this.registro + '/' + this.codlote).then(function (data) {
					$("#phuyu_sistema").empty().html(data.body);
				}, function () {
					phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED", "error");
					phuyu_sistema.phuyu_fin();
				});
			}
		},
		phuyu_cobranza: function (cobrado) {
			if (this.registro == 0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UN REGISTRO", "PARA REALIZAR LA COBRANZA DE UN CREDITO !!!", "error");
			} else {
				// ⚡ Guarda el valor cobrado correctamente (entero)
				this.cobrado = parseInt(cobrado) || 0;
				phuyu_sistema.phuyu_inicio();
				this.$http.get(url + phuyu_controller + "/cobranza/" + this.registro + '/' + this.codlote).then(function (data) {
					$("#phuyu_sistema").empty().html(data.body);
				}, function () {
					phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED", "error");
					phuyu_sistema.phuyu_fin();
				});
			}
		},
		phuyu_historial: function () {
			if (this.registro == 0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UN REGISTRO", "PARA VER LOS CREDITOS DEL SOCIO EN EL SISTEMA !!!", "error");
			} else {
				phuyu_sistema.phuyu_inicio();
				this.$http.get(url + phuyu_controller + "/historial/" + this.registro + '/' + this.codlote).then(function (data) {
					$("#phuyu_sistema").empty().html(data.body);
				}, function () {
					phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED", "error");
					phuyu_sistema.phuyu_fin();
				});
			}
		},
		phuyu_persona: function () {
			$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario", 180);
			$(".phuyu_radio").removeAttr('checked'); this.registro = 0;
			this.$http.post(url + "ventas/clientes/nuevo_1").then(function (data) {
				$("#phuyu_formulario").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			}, function () {
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED", "error");
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			});
		},
		phuyu_conciliacion: function () {
			if (this.registro == 0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UN REGISTRO", "PARA CONCILIAR LOS CREDITOS EN EL SISTEMA !!!", "error");
			} else {
				$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario", 180);
				$(".phuyu_radio").removeAttr('checked'); this.registro = 0;
				this.$http.post(url + phuyu_controller + "/conciliar/" + this.registro).then(function (data) {
					$("#phuyu_formulario").empty().html(data.body);
					phuyu_sistema.phuyu_finloader("phuyu_formulario");
				}, function () {
					phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED", "error");
					phuyu_sistema.phuyu_finloader("phuyu_formulario");
				});
			}
		}
	},
	created: function () {
		this.phuyu_datos();
	}
});