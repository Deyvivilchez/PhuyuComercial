var phuyu_administrar = new Vue({
	el: "#phuyu_administrar",
	data: {
		estado: 0,
		campos: { codsucursal: "", codcaja: "", codalmacen: "" },
		cajas: [],
		almacenes: []
	},
	computed: {
		sucursalSeleccionada: function(){
			var codsucursal = this.campos.codsucursal;
			if (!codsucursal) {
				return null;
			}

			var sucursal = null;
			$("#phuyu_administrar .step:first .card-option").each(function(){
				if ($(this).attr("aria-pressed") === "true") {
					sucursal = {
						descripcion: $.trim($(this).find(".card-title").text())
					};
					return false;
				}
			});

			return sucursal;
		},
		almacenSeleccionado: function(){
			var codalmacen = this.campos.codalmacen;
			if (!codalmacen) {
				return null;
			}

			for (var i = 0; i < this.almacenes.length; i++) {
				if (String(this.almacenes[i].codalmacen) === String(codalmacen)) {
					return this.almacenes[i];
				}
			}

			return null;
		},
		cajaSeleccionada: function(){
			var codcaja = this.campos.codcaja;
			if (!codcaja) {
				return null;
			}

			for (var i = 0; i < this.cajas.length; i++) {
				if (String(this.cajas[i].codcaja) === String(codcaja)) {
					return this.cajas[i];
				}
			}

			return null;
		},
		mensajeAyuda: function(){
			if (this.estado === 1) {
				return "Estamos consultando la sucursal seleccionada. En unos segundos verás sus almacenes y cajas disponibles.";
			}

			if (!this.campos.codsucursal) {
				return "Empieza eligiendo una sucursal para cargar automáticamente los almacenes y cajas disponibles.";
			}

			if (!this.almacenes.length) {
				return "La sucursal seleccionada no tiene almacenes activos. Revisa permisos o configuración.";
			}

			if (!this.cajas.length) {
				return "La sucursal seleccionada no tiene cajas activas. Revisa permisos o configuración.";
			}

			if (!this.campos.codcaja) {
				return "Ya casi está listo. Elige una caja para ingresar al sistema.";
			}

			return "Todo listo. Puedes continuar a administrar con la sucursal, almacén y caja seleccionados.";
		}
	},
	methods: {
		phuyu_noti: function(titulo, mensaje, tipo){
			new PNotify({
				title: titulo,
				text: mensaje,
				type: tipo,
				styling: "bootstrap3"
			});
		},
		mostrarErrorRed: function(){
			if (typeof phuyu_sistema !== "undefined" && phuyu_sistema.alerta) {
				phuyu_sistema.alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED", "error");
				return;
			}

			this.phuyu_noti("Error de red", "No pudimos completar la solicitud. Intenta nuevamente.", "error");
		},
		obtenerAlmacenPrincipal: function(almacenes){
			var principal = "";

			$.each(almacenes, function(k, v){
				if (parseInt(v.principal, 10) === 1) {
					principal = v.codalmacen;
					return false;
				}
			});

			if (!principal && almacenes.length > 0) {
				principal = almacenes[0].codalmacen;
			}

			return principal;
		},
		seleccionarAlmacen: function(codalmacen){
			this.campos.codalmacen = codalmacen;

			if (!this.campos.codcaja && this.cajas.length > 0) {
				this.campos.codcaja = this.cajas[0].codcaja;
			}
		},
		administrar: function(){
			if (!this.campos.codcaja || this.estado === 1 || this.estado === 2) {
				return;
			}

			this.estado = 2;
			this.$http.post(url + "phuyu/phuyu_web", this.campos).then(function(){
				window.location.href = url + "phuyu/w";
			}, function(){
				this.estado = 0;
				this.mostrarErrorRed();
			});
		},
		phuyu_resultados: function(){
			if (this.campos.codsucursal === "") {
				this.almacenes = [];
				this.cajas = [];
				this.campos.codalmacen = "";
				this.campos.codcaja = "";
				this.estado = 0;
				return;
			}

			this.estado = 1;
			this.almacenes = [];
			this.cajas = [];
			this.campos.codalmacen = "";
			this.campos.codcaja = "";

			this.$http.post(url + "phuyu/phuyu_sucursal", this.campos).then(function(data){
				var body = data.body || {};
				var almacenes = body.almacenes || [];
				var cajas = body.cajas || [];

				this.almacenes = almacenes;
				this.cajas = cajas;
				this.campos.codalmacen = this.obtenerAlmacenPrincipal(almacenes);
				this.campos.codcaja = cajas.length > 0 ? cajas[0].codcaja : "";
				this.estado = 0;
			}, function(){
				this.estado = 0;
				this.mostrarErrorRed();
			});
		}
	},
	created: function(){
		if ($("#vencimiento").length && Number($("#vencimiento").val()) < 11) {
			$("#modal_vencimiento").modal("show");
		}
	}
});
