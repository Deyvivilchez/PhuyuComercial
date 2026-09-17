var phuyu_despacho = new Vue({
	el: "#phuyu_despacho",
	data: {
		estado:0, campos:campos, detalle: [], entregados: []
	},
	methods: {
		phuyu_detalle: function(){
			this.$http.get(url+phuyu_controller+"/detalle/"+this.campos.codkardex_ref).then(function(data){
				this.detalle = data.body.detalle; this.entregados = data.body.entregados; phuyu_sistema.phuyu_fin();
			}, function(){
				phuyu_sistema.phuyu_alerta("ERROR AL CARGAR DETALLE DE LA OPERACION","ERROR DE RED","error");
			});
		},

		phuyu_guardar: function(){
			var total = 0;
			for (var i = 0; i < this.detalle.length; i++) {
				total = total + parseFloat(this.detalle[i]["recoger"]);
			}
			if (total==0) {
				phuyu_sistema.phuyu_noti("LA CANTIDAD DE LOS ITEM DEBE SER MAYOR A CERO (MINIMO DE UN ITEM)","","error"); return false;
			}

			this.estado = 1; phuyu_sistema.phuyu_inicio_guardar("GUARDANDO OPERACION . . .");
			this.$http.post(url+phuyu_controller+"/guardar", {"campos":this.campos,"detalle":this.detalle}).then(function(data){
				if (data.body=="e") {
					phuyu_sistema.phuyu_alerta("SU SESION DE USUARIO A TERMINADO","DEBE INICIAR SESION NUEVAMENTE","error");
				}else{
					if (data.body!=0) {
						phuyu_sistema.phuyu_alerta("OPERACION REGISTRADA","OPERACION REGISTRADA CORRECTAMENTE","success");
					}else{
						phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR LA OPERACION","ERROR DE RED","error");
					}
				}
				phuyu_sistema.phuyu_fin(); phuyu_sistema.phuyu_modulo();
			}, function(){
				phuyu_sistema.phuyu_alerta("ERROR AL REALIZAR LA OPERACION","ERROR DE RED","error"); phuyu_sistema.phuyu_modulo();
			});
		},
		phuyu_eliminar: function(datos){
			swal({
				title: "SEGURO ELIMINAR ENTREGA ?",   
				text: "USTED ESTA POR ELIMINAR UNA ENTREGA", 
				icon: "warning",
				dangerMode: true,
				buttons: ["CANCELAR", "SI, ELIMINAR"],
			}).then((willDelete) => {
				if (willDelete) {
					phuyu_sistema.phuyu_inicio_guardar("ELIMINANDO OPERACION ENTREGA . . .");
					this.$http.post(url+phuyu_controller+"/eliminar",datos).then(function(data){
						if (data.body==1) {
							phuyu_sistema.phuyu_alerta("ELIMINADO CORRECTAMENTE", "UN REGISTRO ELIMINADO EN EL SISTEMA","success");
						}else{
							phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR !!!", "SE PERDIÓ LA CONEXION !!! LO SENTIMOS","error");
						}
						this.phuyu_detalle();
					}, function(){
						alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
					});
				}
			});
		},

		phuyu_cerrar: function(){
			phuyu_sistema.phuyu_modulo();
		}
	},
	created: function(){
		this.phuyu_detalle();
	}
});