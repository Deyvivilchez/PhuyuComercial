var phuyu_datos = new Vue({
	el: "#phuyu_datos",
	data: {
		campos: campos,
		kpi: {},
		resumen: [],
		historial: [],
		producto_texto: ""
	},
	methods: {
		phuyu_fecha: function(){
			this.campos.fechadesde = $("#fechadesde").val();
			this.campos.fechahasta = $("#fechahasta").val();
		},
		moneda: function(valor){
			if (valor === null || valor === undefined || valor === "") return "0.00";
			return (parseFloat(valor) || 0).toFixed(2);
		},
		porcentaje: function(valor){
			if (valor === null || valor === undefined || valor === "") return "-";
			return (parseFloat(valor) || 0).toFixed(2) + "%";
		},
		clase_variacion: function(valor){
			var numero = parseFloat(valor) || 0;
			if (numero > 0) return "text-danger fw-bold";
			if (numero < 0) return "text-success fw-bold";
			return "";
		},
		filtrar_texto: function(lista){
			var buscar = (this.producto_texto || "").trim().toUpperCase();
			if (buscar == "") return lista;
			return lista.filter(function(item){
				return ((item.producto || "") + " " + (item.codigo || "")).toUpperCase().indexOf(buscar) >= 0;
			});
		},
		respuesta_json: function(data){
			return data && data.body && typeof data.body == "object";
		},
		generar_resumen: function(){
			this.phuyu_fecha();
			if (this.campos.fechadesde > this.campos.fechahasta) {
				phuyu_sistema.phuyu_noti("LA FECHA DESDE DEBE SER MENOR", "QUE LA FECHA HASTA", "error");
				return false;
			}
			phuyu_sistema.phuyu_inicio();
			this.$http.post(url + phuyu_controller + "/resumen", this.campos).then(function(data){
				if (!this.respuesta_json(data)) {
					phuyu_sistema.phuyu_alerta("ERROR AL CONSULTAR", "EL SERVIDOR NO DEVOLVIO JSON VALIDO", "error");
					phuyu_sistema.phuyu_fin();
					return false;
				}
				this.kpi = data.body.kpi || {};
				this.resumen = this.filtrar_texto(data.body.lista || []);
				phuyu_sistema.phuyu_fin();
			}, function(){
				phuyu_sistema.phuyu_alerta("ERROR AL CONSULTAR", "NO SE PUDO GENERAR EL RESUMEN", "error");
				phuyu_sistema.phuyu_fin();
			});
		},
		generar_historial: function(){
			this.phuyu_fecha();
			phuyu_sistema.phuyu_inicio();
			this.$http.post(url + phuyu_controller + "/historial", this.campos).then(function(data){
				if (!Array.isArray(data.body)) {
					phuyu_sistema.phuyu_alerta("ERROR AL CONSULTAR", "EL SERVIDOR NO DEVOLVIO HISTORIAL VALIDO", "error");
					phuyu_sistema.phuyu_fin();
					return false;
				}
				this.historial = this.filtrar_texto(data.body || []);
				phuyu_sistema.phuyu_fin();
			}, function(){
				phuyu_sistema.phuyu_alerta("ERROR AL CONSULTAR", "NO SE PUDO GENERAR EL HISTORIAL", "error");
				phuyu_sistema.phuyu_fin();
			});
		},
		limpiar_producto: function(){
			this.producto_texto = "";
			this.generar_resumen();
			this.generar_historial();
		}
	},
	created: function(){
		phuyu_sistema.phuyu_fin();
		this.$nextTick(function(){
			this.generar_resumen();
		});
	}
});
