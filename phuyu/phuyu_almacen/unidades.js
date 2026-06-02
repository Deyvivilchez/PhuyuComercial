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
				this.productos = data.body.lista; this.totales = data.body.totales; phuyu_sistema.phuyu_fin();
			});
		},
		phuyu_marcar: function(producto_unidad){
			this.campos = producto_unidad;
		},

		cambiar_unidad: function(){
			if(this.campos.length==0){
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UN PRODUCTO", "PARA CAMBIAR LA UNIDAD DE MEDIDA","error");
			}else{
				this.estado = 0; $("#modal_cambiar_unidad").modal("show");
			}
		},
		guardar_cambiar_unidad: function(){
			if ($("#codunidad").val()=="") {
				phuyu_sistema.phuyu_noti("DEBE SELECCIONAR LA NUEVA UNIDAD DE MEDIDA","","error");
			}else{
				swal({
					title: "SEGURO CAMBIAR LA UNIDAD DE MEDIDA",   
					text: "SE CAMBIARÁ EN COMPRAS, VENTAS, INVENTARIOS", 
					icon: "warning",
					dangerMode: true,
					buttons: ["CANCELAR", "SI, CAMBIAR UNIDAD"],
				}).then((willDelete) => {
					if (willDelete){
						this.estado = 1; phuyu_sistema.phuyu_inicio_guardar("GUARDANDO CAMBIO DE UNIDAD . . .");
						this.$http.post(url+phuyu_controller+"/guardar_cambiar_unidad", {"codproducto":this.campos.codproducto,"codunidad":this.campos.codunidad,"codunidad_nueva":$("#codunidad").val()}).then(function(data){
							if (data.body==1) {
								$("#modal_cambiar_unidad").modal("hide"); this.phuyu_productos();
								phuyu_sistema.phuyu_noti("LA UNIDAD DE MEDIDA SE CAMBIO CORRECTAMENTE","","success");
							}else{
								phuyu_sistema.phuyu_alerta("NO SE PUEDE CAMBIAR A ESTA UNIDAD","PUEDE QUE EL PRODUCTO YA TENGA ESTA UNIDAD","error"); 
								phuyu_sistema.phuyu_fin(); this.estado = 0;
							}
						}, function(){
							phuyu_sistema.phuyu_alerta("ERROR AL CAMBIAR DE UNIDAD","SIN CONEXION","error"); phuyu_sistema.phuyu_fin();
						});
					}else{
						$("#modal_cambiar_unidad").modal("hide");
					}
				});
			}
		},
		productos_almacen: function(){
			swal({
				title: "EL SISTEMA REVISARÁ SI ALGUN PRODUCTO FALTA REGISTRAR EN ALGÚN ALMACÉN",   
				text: "", 
				icon: "warning",
				dangerMode: true,
				buttons: ["CANCELAR", "SI, REVISAR Y REGISTRAR"],
			}).then((willDelete) => {
				if (willDelete){
					this.estado = 1; phuyu_sistema.phuyu_inicio_guardar("REVISANDO Y REGISTRANDO . . .");
					this.$http.post(url+phuyu_controller+"/productos_almacen").then(function(data){
						phuyu_sistema.phuyu_noti("REVISADO Y REGISTRADO CORRECTAMENTE","TODOS LOS PRODUCTOS EN LOS ALMACENES","success"); phuyu_sistema.phuyu_fin();
					}, function(){
						phuyu_sistema.phuyu_alerta("ERROR AL REVISAR","SIN CONEXION","error"); phuyu_sistema.phuyu_fin();
					});
				}else{
					$("#modal_cambiar_unidad").modal("hide");
				}
			});
		},
		actualizar_stock: function(){
			swal({
				title: "EL SISTEMA REVISARÁ Y ACTUALIZARÁ EL STOCK DE TODOS LOS PRODUCTOS",   
				text: "OPCION RECOMENDADA POR EL SISTEMA", 
				icon: "warning",
				dangerMode: true,
				buttons: ["CANCELAR", "SI, ACTUALIZAR STOCK"],
			}).then((willDelete) => {
				if (willDelete){
					this.estado = 1; phuyu_sistema.phuyu_inicio_guardar("ACTUALIZANDO STOCK . . .");
					this.$http.post(url+phuyu_controller+"/actualizar_stock").then(function(data){
						phuyu_sistema.phuyu_noti("STOCK ACTUALIZADO CORRECTAMENTE","OPCION RECOMENDADA POR EL SISTEMA","success"); phuyu_sistema.phuyu_fin();
					}, function(){
						phuyu_sistema.phuyu_alerta("ERROR AL ACTUALIZAR STOCK","SIN CONEXION","error"); phuyu_sistema.phuyu_fin();
					});
				}
			});
		},
		guardar_precios: function(){
			swal({
				title: "EL SISTEMA ACTUALIZARÁ LOS DIFERENTES PRECIOS DE CADA PRODUCTO",   
				text: "USTED ESTÁ POR ACTUALIZAR LOS PRECIOS", 
				icon: "warning",
				dangerMode: true,
				buttons: ["CANCELAR", "SI, ACTUALIZAR PRECIOS"],
			}).then((willDelete) => {
				if (willDelete){
					this.estado = 1; phuyu_sistema.phuyu_inicio_guardar("ACTUALIZANDO PRECIOS . . .");
					this.$http.post(url+phuyu_controller+"/actualizar_precios",{"productos":this.productos}).then(function(data){
						phuyu_sistema.phuyu_noti("PRECIOS ACTUALIZADOS CORRECTAMENTE","","success"); 
						this.phuyu_productos(); phuyu_sistema.phuyu_fin();
					}, function(){
						phuyu_sistema.phuyu_alerta("ERROR AL ACTUALIZAR PRECIOS","SIN CONEXION","error"); phuyu_sistema.phuyu_fin();
					});
				}
			});
		},
		phuyu_calcularitem: function(precio){
			
			precio.precioxmayor = Number(parseFloat(precio.precioventa).toFixed(2));
			precio.pventacredito = Number(parseFloat(precio.precioventa).toFixed(2));
		},
	},
	created: function(){
		this.phuyu_productos();
	}
});