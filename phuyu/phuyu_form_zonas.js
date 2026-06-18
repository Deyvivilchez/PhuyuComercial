var phuyu_form_1 = new Vue({
	el: "#phuyu_form_1",
	data: { estado_1: 0, campos: campos },
	methods: {
		phuyu_guardar_1: function(tabla){
			this.estado_1 = 1;
			this.$http.post(url+"administracion/zonas/guardar_1", this.campos).then(function(data){
				if (data.body.estado==0) {
					phuyu_sistema.phuyu_alerta("ATENCION USUARIO","OCURRIO UN ERROR AL REGISTRAR","error");
				}else{
					phuyu_sistema.phuyu_noti("GUARDADO CORRECTAMENTE","UN NUEVO REGISTRO EN EL SISTEMA","success");
					var datos = eval(data.body.ubigeo);
					phuyu_operacion.obtener_zona(data.body.codzona,datos);
				}
			},function(){
				phuyu_sistema.phuyu_alerta("ATENCION USUARIO","ERROR DE RED (INTERNET)","error"); $("#modal_extencion").modal("hide");
			});
		},
		phuyu_provincias1: function(){
			if (this.campos.departamento1!=undefined) {
				this.$http.get(url+"ventas/clientes/provincias/"+this.campos.departamento1).then(function(data){
					$("#provincia1").empty().html(data.body); $("#codubigeo1").empty().html('<option value="">SELECCIONE</option>');
				});
			}
		},
		phuyu_distritos1: function(){
			if (this.campos.provincia1!=undefined) {
				this.$http.get(url+"ventas/clientes/distritos/"+this.campos.departamento1+"/"+this.campos.provincia1).then(function(data){
					$("#codubigeo1").empty().html(data.body);
				});
			}
		}
	}
});