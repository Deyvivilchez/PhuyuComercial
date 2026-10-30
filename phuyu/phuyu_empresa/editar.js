var phuyu_form = new Vue({
	el: "#phuyu_form",
	data: {estado: 0, campos: campos, mostrar_secret_sunat: false},
	methods: {
		phuyu_guardar: function(){
			this.estado= 1; const formulario = new FormData($("#formulario")[0]);
			this.$http.post(url+phuyu_controller+"/guardar", formulario).then(function(data){
				if (data.body==1) {
					phuyu_sistema.phuyu_noti("EMPRESA CONFIGURADA CORRECTAMENTE","DATOS GUARDADOS EN EL SISTEMA","success");
				}else{
					var mensaje = (typeof data.body === "string" && data.body.indexOf("ERROR:") === 0) ? data.body : "NO SE PUEDE GENERAR LOS ARCHIVOS PARA LA FACTURACION ELECTRONICA";
					phuyu_sistema.phuyu_alerta("ERROR AL PROCESAR CERTIFICADO",mensaje,"error");
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
