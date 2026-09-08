var phuyu_form = new Vue({
	el: "#phuyu_form",
	data: {
		estado: 0, 
		campos: campos,
		sucursales: []
	},
	methods: {
		phuyu_sucursales: function(){
			this.$http.post(url+phuyu_controller+"/sucursales", {"codregistro":phuyu_datos.registro}).then(function(data){
				this.sucursales = data.body;
			});
		},
		phuyu_guardar: function(){
			this.estado= 1;
			this.$http.post(url+phuyu_controller+"/guardar", {"campos":this.campos,"sucursales":this.sucursales}).then(function(data){
				if (data.body=="e") {
					phuyu_sistema.phuyu_noti("NOMBRE DE USUARIO YA EXISTE", "CAMBIAR DE USUARIO","error"); this.estado= 0;
				}else{
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
				}
			}, function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS", "ERROR DE RED","error");
			});
		},
		phuyu_cerrar: function(){
			$(".compose").slideToggle();
		}
	},
	created: function(){
		this.phuyu_sucursales();
	}
});