var phuyu_inventario = new Vue({
	el: "#phuyu_inventario",
	data: {estado: 0,buscar:"",tiporeporte:0,campos:{"codregistro":0,"importe":0},productos:[]},
	computed: {
        buscar_productos: function () {
            return this.productos.filter((dato) => dato.descripcion.includes(this.buscar.toUpperCase()) || dato.codigo.includes(this.buscar.toUpperCase()));
        }
    },
	methods: {
		phuyu_importarinventario: function(){
			$("#modal_subir").modal('show');
		},
		phuyu_guardar_archivo: function(){
            const formulario = new FormData($("#formulario")[0]);
            this.$http.post(url+phuyu_controller+"/guardar_archivo/"+phuyu_datos.registro, formulario).then(function(info){
				if (info.body==0) {
					phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR AL SUBIR EL ARCHIVO CSV", "ERROR REGISTRADO","error");
				}else{
	                phuyu_sistema.phuyu_alerta("INVENTARIO IMPORTADO CORRECTAMENTE", "UN NUEVO REGISTRO EN EL SISTEMA","success");
				}
				this.phuyu_productos(); $("#modal_subir").modal("hide");
            }, function(){
				phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR AL SUBIR EL ARCHIVO CSV", "ERROR DE RED","error");
			});
		},
		phuyu_productos : function(){
			phuyu_sistema.phuyu_inicio();
			this.campos.codregistro = phuyu_datos.registro;
			this.$http.get(url+phuyu_controller+"/productos_inventario/"+phuyu_datos.registro+'?codlinea='+$("#codlinea").val()).then(function(data){
				this.productos = data.body.productos; this.campos.importe = data.body.importe; phuyu_sistema.phuyu_fin();
			});
		},
		phuyu_masproductos : function(){
			phuyu_sistema.phuyu_inicio();
			this.$http.get(url+phuyu_controller+"/mas_productos_inventario/"+phuyu_datos.registro).then(function(data){
				if (data.body=="") {
					phuyu_sistema.phuyu_noti("NO HAY PRODUCTO PARA AGREGAR","PRODUCTOS ACTUALIZADOS","error");
				}else{
					for(i in data.body){
						this.productos.push({
							"codproducto":data.body[i]["codproducto"],"codunidad":data.body[i]["codunidad"],
							"unidad":data.body[i]["unidad"],"codigo":data.body[i]["codigo"],"descripcion":data.body[i]["descripcion"],
							"cantidad":data.body[i]["cantidad"],"preciocosto":data.body[i]["preciocosto"],
							"precioventa":data.body[i]["precioventa"],"importe":data.body[i]["importe"]
						}); 
					}
					phuyu_sistema.phuyu_noti("PRODUCTOS CARGADOS CORRECTAMENTE","PRODUCTOS EN EL INVENTARIO","success");
				}
				phuyu_sistema.phuyu_fin();
			});
		},
		phuyu_nuevoproducto : function(){
			$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario",180);
			this.$http.post(url+"almacen/productos/nuevo").then(function(data){
				$("#phuyu_formulario").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); 
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			});
		},
		phuyu_itemquitar: function(index, campo){
			swal({
				title: "SEGURO QUITAR DE INVENTARIO?",   
				text: "QUITAR EL PRODUCTO DEL INVENTARIO", 
				icon: "warning",
				dangerMode: true,
				buttons: ["CANCELAR", "SI, ELIMINAR"],
			}).then((willDelete) => {
				if (willDelete){
					this.$http.get(url+phuyu_controller+"/productos_quitaritem/"+this.campos.codregistro+"/"+campo.codproducto+"/"+campo.codunidad).then(function(data){
						this.campos.importe = this.campos.importe - campo.importe;
						this.productos.splice(index,1);
					});
				}
			});
		},
		phuyu_calcular: function(campo){
			this.campos.importe = this.campos.importe - campo.importe;
			campo.importe = campo.cantidad * campo.preciocosto;
			this.campos.importe = this.campos.importe + campo.importe;
		},

		phuyu_guardar: function(){
			this.estado= 1; phuyu_sistema.phuyu_inicio_guardar("GUARDANDO CAMBIOS INVENTARIO");
			this.$http.post(url+phuyu_controller+"/guardar_inventario", {"campos":this.campos,"productos":this.productos}).then(function(data){
				if (data.body==1) {
					phuyu_sistema.phuyu_alerta("GUARDADO CORRECTAMENTE", "CAMBIOS DEL INVENTARIO REGISTRADO","success");
				}else{
					phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR AL GUARDAR CAMBIOS", "ERROR DE RED","error");
				}
				phuyu_sistema.phuyu_fin(); phuyu_sistema.phuyu_modulo();
			}, function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS", "ERROR DE RED","error");
			});
		},

		phuyu_actualizarprecios: function(){
			swal({
				title: "SEGURO DESEA ACTUALIZAR LOS PRECIOS EN PRODUCTOS?",   
				text: "SE ACTUALIZARÁ LOS PRECIOS DE LOS PRODUCTOS GENERADOS", 
				icon: "warning",
				dangerMode: true,
				buttons: ["CANCELAR", "SI, ACTUALIZAR"],
			}).then((willDelete) => {
				if (willDelete){
					this.estado= 1; phuyu_sistema.phuyu_inicio_guardar("ACTUALIZANDO PRECIOS EN PRODUCTOS");
					this.$http.post(url+phuyu_controller+"/actualizarpreciosproductos", {"productos":this.productos}).then(function(data){
						if (data.body==1) {
							phuyu_sistema.phuyu_alerta("ACTUALIZADO CORRECTAMENTE", "CAMBIOS REGISTRADOS EN EL SISTEMA","success");
						}else{
							phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR AL GUARDAR CAMBIOS", "ERROR DE RED","error");
						}
						this.estado = 0;
						phuyu_sistema.phuyu_fin();
					}, function(){
						phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS", "ERROR DE RED","error");
					});
				}
			});
		},

		phuyu_pdf: function(){
			var phuyu_url = url+phuyu_controller+"/phuyu_pdf/"+this.campos.codregistro+"/"+this.tiporeporte;
            $("#phuyu_pdf").attr("src",phuyu_url); $("#modal_reportes").modal("show");
		},
		phuyu_excel: function(){
			window.open(url+phuyu_controller+"/phuyu_excel/"+this.campos.codregistro+"/"+this.tiporeporte,"_blank");
		},
		phuyu_cerrar: function(){
			phuyu_sistema.phuyu_modulo();
		}
	},
	created: function(){
		this.phuyu_productos();
	}
});