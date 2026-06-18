var phuyu_operacion = new Vue({
	el: "#phuyu_operacion",
	data: {estado: 0,buscar:"", filtro: {"fechadesde":"","fechahasta":""}, productos:[], campos:[], detalle: []},
	computed: {
        buscar_productos: function () {
            return this.productos.filter((dato) => dato.descripcion.includes(this.buscar.toUpperCase()));
        }
    },
	methods: {
		phuyu_productos : function(){
			this.$http.get(url+phuyu_controller+"/lista").then(function(data){
				this.productos = data.body; phuyu_sistema.phuyu_fin();
			});
		},
		phuyu_item: function(){
			phuyu_sistema.phuyu_loader("lista_productos",180);
			this.$http.post(url+"almacen/productos/buscar/ventas").then(function(data){
				$("#lista_productos").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("lista_productos");
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); 
				phuyu_sistema.phuyu_modulo();
			});
		},
		phuyu_additem: function(producto){
			var existeproducto = this.detalle.filter(function(p){
			    if(p.codproducto == producto.codproducto && p.codunidad == producto.codunidad ){
			    	p.cantidad = parseFloat(p.cantidad) + 1; return p;
			    };
			});

		    if (existeproducto.length==0) {
				this.detalle.push({
					"codproducto":producto.codproducto,"producto":producto.descripcion,"codunidad":producto.codunidad,
					"unidad":producto.unidad,"cantidad":1
				});
		    }
		},
		phuyu_deleteitem: function(index,producto){
			this.detalle.splice(index,1);
		},
		phuyu_receta: function(producto){
			this.campos = producto; this.estado = 0;
			$("#titulo_receta").text("RECETA DE: "+producto.descripcion+" - UNIDAD: "+producto.unidad); this.phuyu_item();
			this.$http.get(url+phuyu_controller+"/detalle_receta/"+producto.codproducto+"/"+producto.codunidad).then(function(data){
				this.detalle = data.body; $("#modal_receta").modal("show");
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
			});
		},
		phuyu_guardar: function(){
			this.estado = 1; $("#modal_receta").modal("hide"); phuyu_sistema.phuyu_inicio_guardar("GUARDANDO RECETA . . .");
			this.$http.post(url+phuyu_controller+"/guardar", {"campos":this.campos,"detalle":this.detalle}).then(function(data){
				if (data.body==1) {
					phuyu_sistema.phuyu_noti("RECETA REGISTRADA CORRECTAMENTE","RECETA REGISTRADA EN EL SISTEMA","success");
				}else{
					phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR RECETA","ERROR DE RED","error");
				}
				this.phuyu_productos();
			}, function(){
				phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR RECETA","ERROR DE RED","error"); phuyu_sistema.phuyu_fin();
			});
		},

		consumo_total: function(){
			this.filtro.fechadesde = $("#fechadesde").val(); this.filtro.fechahasta = $("#fechahasta").val();

			var phuyu_url = url+phuyu_controller+"/consumo_total_pdf?datos="+JSON.stringify(this.filtro); 
			$("#phuyu_pdf").attr("src",phuyu_url); $("#modal_reportes").modal("show");
		},
		consumo_fechas: function(){
			this.filtro.fechadesde = $("#fechadesde").val(); this.filtro.fechahasta = $("#fechahasta").val();

			var phuyu_url = url+phuyu_controller+"/consumo_fechas_pdf?datos="+JSON.stringify(this.filtro); 
			$("#phuyu_pdf").attr("src",phuyu_url); $("#modal_reportes").modal("show");
		}
	},
	created: function(){
		this.phuyu_productos();
	}
});