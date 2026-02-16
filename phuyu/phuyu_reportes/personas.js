var phuyu_datos = new Vue({
	el: "#phuyu_datos",
	data: {
		cargando: true, registro:0, buscar: "", datos: [],
		campos:{
			departamento:"",provincia:"",distrito:"",codsociotipo:1
		},
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
		phuyu_opcion: function(){
			this.phuyu_datos_1();
		},
		phuyu_datos_1: function(){
			phuyu_sistema.phuyu_inicio();
			this.cargando = true; this.registro = 0;
			this.$http.post(url+phuyu_controller+"/lista",{"buscar":this.buscar, "pagina":this.paginacion.actual,"campos": this.campos}).then(function(data){
				this.datos = data.body.lista; this.paginacion = data.body.paginacion;
				this.cargando = false; phuyu_sistema.phuyu_fin();
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS", "ERROR DE RED","error"); this.cargando = false;
			});
		},
		phuyu_datos_2: function(){
			phuyu_sistema.phuyu_fin();
		},
		phuyu_buscar: function(){
			this.paginacion.actual = 1; this.phuyu_opcion();
		},
		phuyu_paginacion: function(pagina){
			this.paginacion.actual = pagina; this.phuyu_opcion();
		},
		pdf_personas: function(){
			window.open(url+phuyu_controller+"/pdf_personas?datos=" + JSON.stringify(this.campos), "_blank");
		},
		excel_personas: function(){
			window.open(url+phuyu_controller+"/excel_personas?datos=" + JSON.stringify(this.campos), "_blank");
		},
		phuyu_provincias: function(){
			if (this.campos.departamento!=undefined) {
				this.$http.get(url+"ventas/clientes/provinciasreporte/"+this.campos.departamento).then(function(data){
					$("#provincia").empty().html(data.body); 
					this.campos.provincia = "";
					$("#codubigeo").empty().html('<option value="">TODOS</option>');
					this.campos.distrito = "";
					this.phuyu_opcion();
				});
			}
		},
		phuyu_distritos: function(){
			if (this.campos.provincia!=undefined) {
				this.$http.get(url+"ventas/clientes/distritosreporte/"+this.campos.departamento+"/"+this.campos.provincia).then(function(data){
					$("#codubigeo").empty().html(data.body);
					this.campos.distrito = "";
					this.phuyu_opcion();
				});
			}
		}
	},
	created: function(){
		this.phuyu_opcion(); phuyu_sistema.phuyu_fin();
	}
});