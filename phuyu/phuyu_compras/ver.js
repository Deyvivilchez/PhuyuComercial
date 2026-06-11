var phuyu_operacion = new Vue({
	el: "#phuyu_operacion",
	data: {estado:0,cargando: true,productos:{codproducto:0,codunidad:0,
		preciosinigv:0,igv:0}},
	methods: {
		phuyu_masprecios: function(codproducto,producto,preciosinigv,codunidad,igv,tipocambio,codmoneda){
			this.cargando = true;
			this.productos.codproducto = codproducto;
			this.productos.codunidad = codunidad;
			this.productos.preciosinigv = preciosinigv;
			this.productos.igv = igv;
			var moneda = (codmoneda==1) ? 'SOLES' : 'DOLARES';
			$("#descripcionproducto").text(producto)
			$("#modal_masprecios").modal('show');
			this.$http.post(url+"almacen/productos/phuyu_masprecios",{'producto':this.productos,'moneda':moneda,'tipocambio':tipocambio}).then(function(data){
				this.cargando = false;
				$("#cuerpomasprecios").empty().html(data.body);
			});
		},
		phuyu_valorizar_precios: function(codkardex, fechakardex){
			swal({
				title: "SEGURO VALORIZAR PRECIOS ?",   
				text: "EL PRECIO DE COSTO SE ACTUALIZARÁ HASTA ESTA COMPRA", 
				icon: "warning",
				dangerMode: true,
				buttons: ["CANCELAR", "SI, VALORIZAR"],
			}).then((willDelete) => {
				if (willDelete){
					this.estado = 1; phuyu_sistema.phuyu_inicio_guardar("RECALCULANDO PRECIOS . . .");
					this.$http.post(url+phuyu_controller+"/valorizar_precios/"+codkardex+"/"+fechakardex).then(function(data){
						if (data.body==1) {
							phuyu_sistema.phuyu_alerta("RECALCULANDO CORRECTAMENTE","-","success");
						}else{
							phuyu_sistema.phuyu_alerta("ERROR AL RECALCULAR","ERROR DE RED","error");
						}
						phuyu_sistema.phuyu_fin();
					}, function(){
						phuyu_sistema.phuyu_alerta("ERROR AL RECALCULAR","ERROR DE RED","error"); phuyu_sistema.phuyu_fin();
					});
				}
			});
		}
	}
});