var phuyu_movimiento = new Vue({
	el: "#phuyu_movimiento",
	data: {estado: 0, campos: campos, movimientobanco:0},
	methods: {
		phuyu_cajabanco: function(){
			if (this.campos.codtipopago==1) {
				this.movimientobanco = 0; $("#nrodocbanco").removeAttr("required");
			}else{
				this.movimientobanco = 1; $("#nrodocbanco").attr("required","true");
			}
		},
		phuyu_guardar: function(){
			if (phuyu_controller=="compras/compras") {
				var url_movimiento = "compras/compras/guardar_gasto";
			}else{
				var url_movimiento = "caja/movimientos/guardar";
			}
			this.campos.fechadocbanco = $("#fechadocbanco").val(); this.estado= 1; 
			this.$http.post(url+url_movimiento, this.campos).then(function(data){
				if (data.body==1) {
					if (this.campos.codregistro=="") {
						phuyu_sistema.phuyu_alerta("GUARDADO CORRECTAMENTE", "UN NUEVO REGISTRO EN EL SISTEMA","success");
					}else{
						phuyu_sistema.phuyu_alerta("EDITADO CORRECTAMENTE", "UN REGISTRO EDITADO EN EL SISTEMA","info");
					}
				}else{
					phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR AL REGISTRAR", "NO SE PUEDE REGISTRAR","error");
				}

				if (phuyu_controller=="compras/compras") {
					phuyu_compras.phuyu_datos();
				}else{
					// phuyu_datos.phuyu_datos();
				}
				this.phuyu_cerrar();
			}, function(){
				phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR AL REGISTRAR", "NO SE PUEDE GUARDAR EL MOVIMIENTO DE CAJA","error");
				this.estado= 0;
			});
		},
		phuyu_cerrar: function(){
			$(".compose").slideToggle();
		}
	}
});