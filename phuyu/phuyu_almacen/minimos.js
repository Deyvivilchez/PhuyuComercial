var phuyu_unidades = new Vue({
	el: "#phuyu_unidades",
	data: {estado: 0,buscar:"",totales:[], productos:[], campos:[]},
	computed: {
        buscar_productos: function () {
            return this.productos.filter((dato) => dato.descripcion.includes(this.buscar.toUpperCase()));
        }
    },
	methods: {
		phuyu_productos : function(){
			this.$http.get(url+phuyu_controller+"/lista").then(function(data){
				this.productos = data.body.lista; phuyu_sistema.phuyu_fin();
			});
		},
		phuyu_marcar: function(producto_unidad){
			this.campos = producto_unidad;
		},
		phuyu_guardar: function(){
			this.estado= 1; phuyu_sistema.phuyu_inicio_guardar("GUARDANDO CAMBIOS DE STOCK MINIMOS");
			this.$http.post(url+phuyu_controller+"/guardar", {"productos":this.productos}).then(function(data){
				if (data.body==1) {
					phuyu_sistema.phuyu_alerta("GUARDADO CORRECTAMENTE", "CAMBIOS DEL STOCK MINIMO","success");
				}else{
					phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR AL GUARDAR CAMBIOS", "ERROR DE RED","error");
				}
				phuyu_sistema.phuyu_fin(); phuyu_sistema.phuyu_modulo();
			}, function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS", "ERROR DE RED","error");
			});
		},
		cambiar_unidad: function(){
			if(this.campos.length==0){
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UN PRODUCTO", "PARA CAMBIAR LA UNIDAD DE MEDIDA","error");
			}else{
				this.estado = 0; $("#modal_cambiar_unidad").modal("show");
			}
		}
	},
	created: function(){
		this.phuyu_productos();
	}
});