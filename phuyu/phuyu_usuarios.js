var phuyu_form = new Vue({
	el: "#phuyu_form",
	data: {
		estado: 0, 
		campos: campos,
		sucursales: [],
		cajas: [],
		modal_permisos: false,
		mostrar_clave: false
	},
	methods: {
		phuyu_sucursales: function(){
			this.$http.post(url+phuyu_controller+"/sucursales", {"codregistro":phuyu_datos.registro}).then(function(data){
				this.sucursales = data.body;
			});
		},
		phuyu_cajas: function(){
			this.$http.post(url+phuyu_controller+"/cajas", {"codregistro":phuyu_datos.registro}).then(function(data){
				this.cajas = data.body;
			});
		},
		phuyu_guardar: function(){
			this.estado= 1;
			this.$http.post(url+phuyu_controller+"/guardar", {"campos":this.campos,"sucursales":this.sucursales,"cajas":this.cajas}).then(function(data){
				if (data.body=="e") {
					phuyu_sistema.phuyu_noti("NOMBRE DE USUARIO YA EXISTE", "CAMBIAR DE USUARIO","error"); this.estado= 0;
				}else{
					var respuesta = data.body;
					var guardado = respuesta==1 || (respuesta && parseInt(respuesta.estado || 0) == 1);
					if (guardado) {
						if (this.campos.codregistro=="") {
							phuyu_sistema.phuyu_alerta("GUARDADO CORRECTAMENTE", "UN NUEVO REGISTRO EN EL SISTEMA","success");
						}else{
							phuyu_sistema.phuyu_alerta("EDITADO CORRECTAMENTE", "UN REGISTRO EDITADO EN EL SISTEMA","info");
						}
						phuyu_datos.phuyu_opcion(); this.phuyu_cerrar();
					}else{
						var mensaje = respuesta && respuesta.mensaje ? respuesta.mensaje : "NO SE PUEDE REGISTRAR";
						phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR AL REGISTRAR", mensaje, "error");
						this.estado = 0;
					}
				}
			}, function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS", "ERROR DE RED","error");
				this.estado = 0;
			});
		},
		phuyu_cerrar: function(){
			$(".compose").slideToggle();
		}
	},
	created: function(){
		this.phuyu_sucursales();
		this.phuyu_cajas();
	}
});
