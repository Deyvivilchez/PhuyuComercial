var phuyu_form = new Vue({
	el: "#phuyu_form",
	data: {estado: 0, campos: campos},
	methods: {
		phuyu_guardar: function(){
			this.estado= 1; const formulario = new FormData($("#formulario")[0]);
			this.$http.post(url+phuyu_controller+"/guardar", formulario).then(function(data){
				if (data.body==1) {
					phuyu_sistema.phuyu_noti("EMPRESA CONFIGURADA CORRECTAMENTE","DATOS GUARDADOS EN EL SISTEMA","success");
				}else{
					phuyu_sistema.phuyu_alerta("LA CLAVE DEL CERTIFICADO ES INCORRECTA","NO SE PUEDE GENERAR LOS ARCHIVOS PARA LA FACTURACION ELECTRONICA","error");
				}
				this.phuyu_cerrar(); phuyu_sistema.phuyu_modulo();
			}, function(){
				phuyu_sistema.phuyu_alerta("ATENCION USUARIO","ERROR DE RED (INTERNET)","error");
			});
		},
		phuyu_cerrar: function(){
			$(".compose").slideToggle();
		}
	}
});