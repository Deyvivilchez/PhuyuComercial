var phuyu_operacion = new Vue({
	el: "#phuyu_operacion",
	data: {
		estado: 0,
		buscar:"",
		filtro: {"fechadesde":"","fechahasta":""},
		filtro_producto: "preparados",
		filtro_ingrediente: "insumos",
		buscar_ingrediente: "",
		buscando_productos: null,
		buscando_ingredientes: null,
		productos:[],
		ingredientes: [],
		paginacion: {actual: 1, limit: 25, total: 0, ultima: 1},
		campos:[],
		detalle: []
	},
	computed: {
        buscar_productos: function () {
            return this.productos;
        },
		phuyu_paginas: function(){
			var paginas = [];
			var actual = parseInt(this.paginacion.actual) || 1;
			var ultima = parseInt(this.paginacion.ultima) || 1;
			var inicio = Math.max(1, actual - 2);
			var fin = Math.min(ultima, actual + 2);
			for (var i = inicio; i <= fin; i++) {
				paginas.push(i);
			}
			return paginas;
		},
		costo_receta: function(){
			var total = 0;
			this.detalle.forEach(function(dato){
				total += (parseFloat(dato.cantidad) || 0) * (parseFloat(dato.preciocosto) || 0);
			});
			return total.toFixed(2);
		},
		utilidad_receta: function(){
			var venta = parseFloat(this.campos.precioventa) || 0;
			return (venta - (parseFloat(this.costo_receta) || 0)).toFixed(2);
		}
    },
	methods: {
		phuyu_moneda: function(valor){
			return (parseFloat(valor) || 0).toFixed(2);
		},
		phuyu_unidades_producto: function(producto){
			if (Array.isArray(producto.unidades_lista)) {
				return producto.unidades_lista.map(function(item){
					return {
						codunidad: parseInt(item.codunidad) || 0,
						unidad: item.unidad,
						preciocosto: parseFloat(item.preciocosto) || 0
					};
				});
			}
			var unidades = [];
			if (producto.unidades) {
				producto.unidades.split(";").forEach(function(item){
					var datos = item.split("|");
					if (datos.length > 8) {
						var preciocosto = parseFloat(datos[9]) || parseFloat(datos[5]) || 0;
						unidades.push({
							codunidad: parseInt(datos[0]) || 0,
							unidad: datos[1],
							preciocosto: preciocosto
						});
					}
				});
			}
			if (unidades.length == 0) {
				unidades.push({
					codunidad: parseInt(producto.codunidad) || 0,
					unidad: producto.unidad,
					preciocosto: parseFloat(producto.preciocosto) || parseFloat(producto.precio) || 0
				});
			}
			return unidades;
		},
		phuyu_cambiarunidad: function(dato){
			var unidad = (dato.unidades || []).filter(function(item){
				return parseInt(item.codunidad) == parseInt(dato.codunidad);
			})[0];
			if (unidad) {
				dato.unidad = unidad.unidad;
				dato.preciocosto = parseFloat(unidad.preciocosto) || 0;
			}
		},
		phuyu_productos : function(){
			this.$http.post(url+phuyu_controller+"/lista", {
				buscar: this.buscar,
				tipo: this.filtro_producto,
				pagina: this.paginacion.actual,
				limit: this.paginacion.limit
			}).then(function(data){
				if (data.body && data.body.lista) {
					this.productos = data.body.lista;
					this.paginacion = data.body.paginacion || this.paginacion;
				}else{
					this.productos = [];
				}
				phuyu_sistema.phuyu_fin();
			}, function(){
				phuyu_sistema.phuyu_alerta("ERROR AL CONSULTAR PRODUCTOS", "NO SE PUDO CARGAR RECETAS", "error");
				phuyu_sistema.phuyu_fin();
			});
		},
		phuyu_filtrar_productos: function(inmediato){
			if (this.buscando_productos) {
				clearTimeout(this.buscando_productos);
			}
			var cargar = () => {
				this.paginacion.actual = 1;
				this.phuyu_productos();
			};
			if (inmediato === true) {
				cargar();
				return true;
			}
			this.buscando_productos = setTimeout(cargar, 300);
		},
		phuyu_cambiar_pagina: function(pagina, forzar){
			pagina = parseInt(pagina) || 1;
			if (pagina < 1 || pagina > this.paginacion.ultima || (pagina == this.paginacion.actual && forzar !== true)) {
				return false;
			}
			this.paginacion.actual = pagina;
			this.phuyu_productos();
		},
		phuyu_item: function(){
			this.phuyu_buscar_ingredientes(true);
		},
		phuyu_buscar_ingredientes: function(inmediato){
			var cargar = () => {
				this.$http.post(url+phuyu_controller+"/ingredientes", {
					buscar: this.buscar_ingrediente,
					tipo: this.filtro_ingrediente
				}).then(function(data){
					this.ingredientes = data.body || [];
				},function(){
					phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
				});
			};
			if (this.buscando_ingredientes) {
				clearTimeout(this.buscando_ingredientes);
			}
			if (inmediato === true) {
				cargar();
				return true;
			}
			this.buscando_ingredientes = setTimeout(cargar, 250);
		},
		phuyu_additem: function(producto){
			var unidades = this.phuyu_unidades_producto(producto);
			var unidadBase = unidades.filter(function(item){
				return parseInt(item.codunidad) == parseInt(producto.codunidad);
			})[0] || unidades[0];
			producto.codunidad = parseInt(unidadBase.codunidad) || 0;
			var existeproducto = this.detalle.filter(function(p){
			    if(p.codproducto == producto.codproducto && p.codunidad == producto.codunidad ){
			    	p.cantidad = parseFloat(p.cantidad) + 1; return p;
			    };
			});

		    if (existeproducto.length==0) {
				this.detalle.push({
					"codproducto":producto.codproducto,"producto":producto.descripcion,"codunidad":producto.codunidad,
					"unidad":unidadBase.unidad,"cantidad":1,"preciocosto":parseFloat(unidadBase.preciocosto) || 0,
					"unidades":unidades
				});
		    }
		},
		phuyu_deleteitem: function(index,producto){
			this.detalle.splice(index,1);
		},
		phuyu_actualizar_costo_plato: function(){
			if (this.detalle.length == 0) {
				phuyu_sistema.phuyu_alerta("AGREGA INGREDIENTES A LA RECETA", "NO HAY COSTO PARA ACTUALIZAR", "warning");
				return false;
			}
			phuyu_sistema.phuyu_inicio_guardar("ACTUALIZANDO COSTO DEL PLATO . . .");
			this.$http.post(url+phuyu_controller+"/actualizar_costo_plato", {
				"campos": this.campos,
				"preciocosto": this.costo_receta
			}).then(function(data){
				if (data.body==1) {
					this.campos.preciocosto = this.costo_receta;
					phuyu_sistema.phuyu_noti("COSTO ACTUALIZADO", "EL COSTO DEL PLATO SE ACTUALIZO CORRECTAMENTE", "success");
					this.phuyu_productos();
				}else{
					phuyu_sistema.phuyu_alerta("NO SE PUDO ACTUALIZAR EL COSTO", "VERIFICA LA UNIDAD DEL PRODUCTO", "error");
					phuyu_sistema.phuyu_fin();
				}
			}, function(){
				phuyu_sistema.phuyu_alerta("ERROR AL ACTUALIZAR COSTO", "ERROR DE RED", "error"); phuyu_sistema.phuyu_fin();
			});
		},
		phuyu_receta: function(producto){
			this.campos = producto; this.estado = 0;
			this.buscar_ingrediente = "";
			this.filtro_ingrediente = "insumos";
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
					this.campos.receta = this.detalle.map(function(dato){
						var item = JSON.parse(JSON.stringify(dato));
						item.costototal = (parseFloat(item.cantidad) || 0) * (parseFloat(item.preciocosto) || 0);
						return item;
					});
					this.campos.costo_receta = this.costo_receta;
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
