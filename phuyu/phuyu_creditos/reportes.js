var phuyu_datos = new Vue({
	el: "#phuyu_datos",
	data: {
		cargando: true, registro:0, buscar: "", datos: [], estado:"",total:[],
		campos:{"filtro":1,"fechadesde":"","fechahasta":"","creditoprogramado":""},
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
		phuyu_fechas: function(){
			this.campos.fechadesde = $("#fechadesde").val();
			this.campos.fechahasta = $("#fechahasta").val();
		},
		phuyu_datos: function(){
			this.phuyu_fechas();this.cargando = true; this.registro = 0;
			this.$http.post(url+phuyu_controller+"/lista",{"buscar":this.buscar,"estado":this.estado,"campos":this.campos}).then(function(data){
				this.datos = data.body.lista; 
				this.total = data.body.total;
				this.cargando = false; phuyu_sistema.phuyu_fin();
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS", "ERROR DE RED","error"); this.cargando = false;
			});
		},
		phuyu_buscar: function(){
			this.phuyu_datos();
		},
		phuyu_paginacion: function(pagina){
			this.paginacion.actual = pagina; this.phuyu_datos();
		},
		phuyu_seleccionar: function(registro){
			this.registro = registro;
		},
		
		phuyu_editar: function(tipo){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UN CREDITO", "PARA CAMBIAR EL "+tipo+" !!!","error");
			}else{
				$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario",180);
				this.$http.post(url+phuyu_controller+"/editar",{"codregistro":this.registro}).then(function(data){
					$("#phuyu_formulario").empty().html(data.body);
					phuyu_sistema.phuyu_finloader("phuyu_formulario");
				},function(){
					phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); 
					phuyu_sistema.phuyu_finloader("phuyu_formulario");
				});
			}
		},
		phuyu_imprimir: function(tipo){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UN CREDITO", "PARA IMPRIMIR EL CREDITO !!!","error");
			}else{
				if(tipo==0){
					window.open(url+phuyu_controller+"/phuyu_imprimir/"+this.registro,"_blank");
				}else{
					window.open(url+phuyu_controller+"/phuyu_imprimir/"+this.registro,"_blank");
				}
			}
		},

		phuyu_imprimirlista: function(tipo){
			if(tipo==0){
				window.open(url+phuyu_controller+"/phuyu_imprimirlistapdf?estado="+this.estado+'&campos='+encodeURIComponent(JSON.stringify(this.campos)),"_blank");
			}else{
				window.open(url+phuyu_controller+"/phuyu_imprimirlistaexcel?estado="+this.estado+'&campos='+encodeURIComponent(JSON.stringify(this.campos)),"_blank");
			}
		},
		pdf_creditos: function(){
			window.open(url+phuyu_controller+"/pdf_creditos","_blank");
		}
	},
	created: function(){
		this.phuyu_datos();
	}
});