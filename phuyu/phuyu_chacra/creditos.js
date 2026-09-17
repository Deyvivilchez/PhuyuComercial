var phuyu_datos = new Vue({
	el: "#phuyu_datos",
	data: {
		rubro: $("#rubro").val(),
		cargando: true,
		campos: campos,
		estado_cuenta_socios: [],
		estado_cuenta_creditos: [],
		estado_cuenta_detallado: [],
		estado_cuenta_creditos_interes_actualizado: [],
		estado_cuenta_detallado_interes_actualizado: [],
		saldos: [],
		saldos_actual: []
	},
	methods: {
		phuyu_atras: function(){
			phuyu_sistema.phuyu_modulo();
		},
		phuyu_fecha: function () {
			this.campos.codpersona = $("#codpersona").val();
			this.campos.fecha_desde = $("#fecha_desde").val();
			this.campos.fecha_hasta = $("#fecha_hasta").val();
			this.campos.fecha_saldos = $("#fecha_saldos").val();

		},
		phuyu_vacio: function () {
			this.estado_cuenta_socios = [];
			this.estado_cuenta_creditos = [];
			this.estado_cuenta_detallado = [];
			

			this.estado_cuenta_creditos_interes_actualizado = [];
			this.estado_cuenta_detallado_interes_actualizado = [];
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
		ver_creditos: function () {
			phuyu_sistema.phuyu_inicio();
			this.phuyu_fecha();
			this.campos.saldos = 0;
			this.campos.codlote = phuyu_lineas.registro;
			this.$http.post(url + phuyu_controller + "/ver_creditos", this.campos).then(function (data) {

				if (this.campos.tipo_consulta == 1) {
					if (this.campos.mostrar == 1) {

						this.estado_cuenta_socios = data.body;
					} else {
						this.estado_cuenta_creditos = data.body;
					}
				} else if(this.campos.tipo_consulta == 2){

					this.estado_cuenta_detallado = data.body;

				} else if(this.campos.tipo_consulta == 3){
					
					if (this.campos.mostrar == 1) {

						this.estado_cuenta_socios = data.body;
					} else {
						this.estado_cuenta_creditos_interes_actualizado = data.body;
					}

				}else {
					this.estado_cuenta_detallado_interes_actualizado = data.body;
				}
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

		pdf_creditos: function () {
			this.phuyu_fecha();
			var phuyu_url = url + phuyu_controller + "/pdf_creditos?datos=" + JSON.stringify(this.campos);
			window.open(phuyu_url,"_blank");
		},
		excel_creditos: function () {
			this.phuyu_fecha();
			window.open(url + phuyu_controller + "/excel_creditos?datos=" + JSON.stringify(this.campos), "_blank");
		}
	},
	created: function () {
		phuyu_sistema.phuyu_fin();
	}
});