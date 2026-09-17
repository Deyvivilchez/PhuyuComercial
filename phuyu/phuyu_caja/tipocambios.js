var phuyu_form = new Vue({
	el: "#phuyu_form",
	data: {estado: 0, campos: campos},
	methods: {
		phuyu_fecha: function(){
			this.campos.fecha = $("#fecha").val();
		},
		phuyu_guardar: function(){
			this.estado= 1; 
			this.$http.post(url+phuyu_controller+"/guardar", this.campos).then(function(data){
				if (data.body==1) {
					if (this.campos.codregistro=="") {
						phuyu_sistema.phuyu_alerta("GUARDADO CORRECTAMENTE", "UN NUEVO REGISTRO EN EL SISTEMA","success");
					}else{
						phuyu_sistema.phuyu_alerta("EDITADO CORRECTAMENTE", "UN REGISTRO EDITADO EN EL SISTEMA","info");
					}
				}else{
					phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR AL REGISTRAR", "NO SE PUEDE REGISTRAR","error");
				}
				phuyu_datos.phuyu_opcion(); this.phuyu_cerrar();
			}, function(){
				phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR AL REGISTRAR", "NO SE PUEDE GUARDAR TIPO CAMBIO","error");
				this.estado= 0;
			});
		},
		phuyu_cerrar: function(){
			$(".compose").slideToggle();
		}
	}
});