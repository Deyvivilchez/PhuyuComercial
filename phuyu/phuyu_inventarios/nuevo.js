var phuyu_form = new Vue({
	el: "#phuyu_form",
	data: {estado: 0, campos: campos, almacenes:[]},
	methods: {
		phuyu_almacenes : function(){
			if (this.campos.codsucursal!=undefined) {
				this.estado = 1;
				this.$http.get(url+phuyu_controller+"/almacenes/"+this.campos.codsucursal).then(function(data){
					this.almacenes = data.body; this.estado = 0;
				});
			}
		},
		phuyu_guardar: function(){
			this.estado= 1;
			this.$http.post(url+phuyu_controller+"/guardar", this.campos).then(function(data){
				if(data.body=="e"){
					phuyu_sistema.phuyu_alerta("YA EXISTE UN INVENTARIO", "EN ESTA SUCURSAL Y EN ESTE ALMACEN","error"); this.estado= 0;
				}else{
					if (data.body==1) {
						phuyu_sistema.phuyu_alerta("INVENTARIO INICIADO CORRECTAMENTE", "PROCESO DE INVENTARIO INICIARIO EN EL SISTEMA","success");
					}else{
						phuyu_sistema.phuyu_alerta("ERROR AL CREAR INVENTARIO", "NO SE PUEDE INICIAR UN INVENTARIO AHORA","error");
					}
					phuyu_datos.phuyu_datos(); this.phuyu_cerrar();
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
		this.phuyu_almacenes();
	}
});