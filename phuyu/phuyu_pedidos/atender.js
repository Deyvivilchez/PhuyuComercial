var phuyu_atender = new Vue({
	el: "#phuyu_atender",
	data: {estado:0, atender:[], totales:[]},
	methods: {
		phuyu_atender_pedido: function(){
			this.$http.post(url+phuyu_controller+"/phuyu_atenciones",{"codpedido":$("#codpedido").val()}).then(function(data){
				this.atender = data.body.detalle; this.totales = data.body.totales;
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
			});
		},
		phuyu_mas_menos: function(pedido,tipo){
			if (tipo==1) {
				if (pedido.falta!=pedido.atender) {
					pedido.atender = pedido.atender + 1;
				}
			}else{
				if (pedido.atender>0) {
					pedido.atender = pedido.atender - 1;
				}
			}
		},
		phuyu_atender: function(){
			var atender = 0;
			for (var i = 0; i < this.atender.length; i++) {
				if (this.atender[i]["atender"]!="") {
					atender = atender + parseFloat(this.atender[i]["atender"]);
				}
			}
			if (atender==0) {
				phuyu_sistema.phuyu_noti("NO HAY PEDIDOS PARA ATENDER","MINIMO DEBE HABER UNA CANTIDAD ATENDIDA","error");
			}else{
				this.estado = 1; phuyu_sistema.phuyu_inicio_guardar("GUARDANDO ATENCION . . .");
				this.$http.post(url+phuyu_controller+"/guardar_atencion",{"atender":this.atender}).then(function(data){
					if (data.body==1) {
						phuyu_sistema.phuyu_noti("ATENCION REGISTRADA CORRECTAMENTE","PEDIDO ATENDIDO","success");
					}else{
						phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR ATENCION","ERROR DE RED","error");
					}
					phuyu_sistema.phuyu_fin(); phuyu_historial.phuyu_pedidos(); this.phuyu_cerrar();
				}, function(){
					phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR ATENCION","ERROR DE RED","error");
					phuyu_sistema.phuyu_fin(); this.phuyu_cerrar();
				});
			}
		},
		phuyu_cerrar: function(){
			$(".compose").slideToggle();
		}
	},
	created: function(){
		this.phuyu_atender_pedido();
	}
});