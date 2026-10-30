var phuyu_datos = new Vue({
	el: "#phuyu_datos",
	data: {
		cargando: true, registro:0, operacion:0, buscar: "", datos: [], estadodespacho:"0",
		filtro:{"codpersona":0,"seriecomprobante":"","nrocomprobante":""}, filtros: [],
		paginacion: {"total":0, "actual":1, "ultima":0, "desde":0, "hasta":0}, offset: 3
	},
	computed: {
		phuyu_actual: function(){
			return this.paginacion.actual;
		},
		phuyu_paginas: function(){
			if (!this.paginacion.hasta) {
				return [];
			}
			var desde = this.paginacion.actual - this.offset;
			if (desde < 1) {
				desde = 1;
			}
			var hasta = desde + (this.offset * 2);
			if (hasta >= this.paginacion.ultima) {
				hasta = this.paginacion.ultima;
			}

			var paginas = [];
			while(desde <= hasta){
				paginas.push(desde); desde++;
			}
			return paginas;
		}
	},
	methods: {
		phuyu_datos: function(){
			this.cargando = true; this.registro = 0;
			this.$http.post(url+phuyu_controller+"/lista",{"buscar":this.buscar, "pagina":this.paginacion.actual,"estadodespacho":this.estadodespacho}).then(function(data){
				this.datos = data.body.lista; this.paginacion = data.body.paginacion;
				this.cargando = false; phuyu_sistema.phuyu_fin();
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS", "ERROR DE RED","error"); this.cargando = false;
			});
		},
		phuyu_buscar: function(){
			this.paginacion.actual = 1; this.phuyu_datos();
		},
		phuyu_paginacion: function(pagina){
			this.paginacion.actual = pagina; this.phuyu_datos();
		},
		phuyu_seleccionar: function(registro,operacion){
			this.registro = registro; this.operacion = operacion;
		},
		
		phuyu_operacion: function(tipo){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UNA OPERACION","SELECCIONAR CON UN CHECK","error");
			}else{
				if(tipo==this.operacion){
					phuyu_sistema.phuyu_inicio();
					this.$http.get(url+phuyu_controller+"/nuevo/"+this.registro).then(function(data){
						$("#phuyu_sistema").empty().html(data.body);
					},function(){
						phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); phuyu_sistema.phuyu_fin();
					});
				}else{
					if (tipo==20) {
						phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UNA VENTA","PARA REGISTRAR UN DESPACHO","error");
					}else{
						phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UNA COMPRA","PARA REGISTRAR UNA ENTREGA","error");
					}
				}
			}
		},
		phuyu_operacion_1: function(){
			this.$http.get(url+phuyu_controller+"/nuevo/"+this.registro).then(function(data){
				$("#phuyu_sistema").empty().html(data.body);
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); 
				phuyu_sistema.phuyu_fin();
			});
		},
		phuyu_imprimir: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UN DESPACHO", "PARA IMPRIMIR EN EL SISTEMA !!!","error");
			}else{
				window.open(url+"facturacion/formato/a4despacho/"+this.registro,"_blank");
			}
        },
		phuyu_buscarkardex: function(){
			$("#modal_buscarkardex").modal("show");
		},
		phuyu_filtrar: function(){
			$filtros = []; phuyu_sistema.phuyu_inicio_guardar("CONSULTANDO Y BUSCANDO . . .");
			this.$http.post(url+phuyu_controller+"/filtrar", this.filtro).then(function(data){
				this.filtros = data.body; phuyu_sistema.phuyu_fin();
			});
		},
		phuyu_seleccionar_1: function(registro){
			this.registro = registro; $("#modal_buscarkardex").modal("hide"); phuyu_sistema.phuyu_inicio(); 
			var self = this;
			setTimeout(function(){
  				self.phuyu_operacion_1();
			},300);
		}
	},
	created: function(){
		this.phuyu_datos();
	}
});