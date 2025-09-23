var phuyu_datos = new Vue({
	el: "#phuyu_datos",
	data: {
		rubro: $("#rubro").val(),
		cargando: true,
		campos: campos,
		datos: [], total:[]
	},
	methods: {
		phuyu_infocliente: function(codpersona){
			this.campos.codpersona = codpersona;
		},
		phuyu_fecha: function () {
			this.campos.codpersona = $("#codpersona").val();
			this.campos.fecha_desde = $("#fecha_desde").val();
			this.campos.fecha_hasta = $("#fecha_hasta").val();
			this.campos.fecha_saldos = $("#fecha_saldos").val();

		},
		phuyu_vacio: function () {
			this.datos = [];
			this.total = [];
		},
		phuyu_lineascredito: function(){
			this.phuyu_fecha();
			if (this.campos.codpersona!="" || this.campos.codpersona!=2 || this.campos.codpersona!=0) {
				this.$http.post(url+"ventas/lineascredito/phuyu_lineascredito/"+this.campos.codpersona,{"flag":1}).then(function(data){
					$("#codlote").empty().html(data.body);
					this.campos.codlote = $("#codlote").val()
				});
			}else{
				$("#codlote").empty().html("<option value='0'>TODAS LAS LINEAS</option>");
			}
		},
		ver_cuotas: function () {
			phuyu_sistema.phuyu_inicio();
			this.phuyu_fecha();
			this.campos.saldos = 0;
			this.$http.post(url + phuyu_controller + "/lista", this.campos).then(function (data) {
				this.total = data.body.total;
				this.datos = data.body.socios;
				phuyu_sistema.phuyu_fin();
			});
		},
		actualizar_interes: function () {
			this.phuyu_fecha();
			if(this.campos.codpersona==0){
				phuyu_sistema.phuyu_alerta("SELECCIONE EL CLIENTE POR FAVOR", "PARA REALIZAR LA ACTUALIZACION","error");return;
			}
			this.campos.saldos = 0;
			swal({
				title: "SEGURO DESEA ACTUALIZAR EL INTERES ?",   
				text: "ESTA POR MODIFICAR EL INTERES DEL CREDITO", 
				icon: "warning",
				dangerMode: true,
				buttons: ["CANCELAR", "SI, ACTUALIZAR INTERES"],
			}).then((willDelete) => {
				if (willDelete){
					this.$http.post(url + phuyu_controller + "/actualizar_interes", this.campos).then(function (data) {
						if(data.body==1){
							phuyu_sistema.phuyu_noti("INTERES ACTUALIZADO CORRECTAMENTE", "UN ACTUALIZACION REALIZADA CORRECTAMENTE EN EL SISTEMA","success");
							this.ver_creditos()
						}
					});
				}
			});
		},
		saldo_creditos: function () {
			phuyu_sistema.phuyu_inicio();
			this.phuyu_fecha();
			this.phuyu_vacio();
			this.campos.saldos = 1;
			this.$http.post(url + phuyu_controller + "/ver_creditos", this.campos).then(function (data) {
				this.saldos = data.body;
				phuyu_sistema.phuyu_fin();
			});
		},
		saldo_creditos_actual: function () {
			phuyu_sistema.phuyu_inicio();
			this.phuyu_fecha();
			this.phuyu_vacio();
			this.campos.saldos = 2;
			this.$http.post(url + phuyu_controller + "/ver_creditos", this.campos).then(function (data) {
				this.saldos_actual = data.body;
				phuyu_sistema.phuyu_fin();
			});
		},

		pdf_cuotas: function () {
			this.phuyu_fecha();
			window.open(url + phuyu_controller + "/pdf_cuotas?datos=" + JSON.stringify(this.campos), "_blank");
		},
		excel_cuotas: function () {
			this.phuyu_fecha();
			window.open(url + phuyu_controller + "/excel_cuotas?datos=" + JSON.stringify(this.campos), "_blank");
		}
	},
	created: function () {
		phuyu_sistema.phuyu_fin();
	}
});