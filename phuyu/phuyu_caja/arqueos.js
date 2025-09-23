var phuyu_datos = new Vue({
	el: "#phuyu_datos",
	data: {
		cargando: true, registro:0, filtro:{buscar:"", desde:"", hasta:""}, datos: [],
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
			this.cargando = true; this.filtro.desde = $("#desde").val(); this.filtro.hasta = $("#hasta").val();
			this.$http.post(url+phuyu_controller+"/lista",{"filtro":this.filtro, "pagina":this.paginacion.actual}).then(function(data){
				this.datos = data.body.lista; this.paginacion = data.body.paginacion;
				this.cargando = false; phuyu_sistema.phuyu_fin();
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS", "ERROR DE RED","error"); 
				this.cargando = false; phuyu_sistema.phuyu_fin();
			});
		},
		phuyu_buscar: function(){
			this.paginacion.actual = 1; this.phuyu_datos();
		},
		phuyu_paginacion: function(pagina){
			this.paginacion.actual = pagina; this.phuyu_datos();
		},
		
		pdf_anfitrionas: function(dato){
            var phuyu_url = url+"restaurante/caja/pdf_vendedores_caja/"+dato.codcontroldiario;
            window.open(phuyu_url,"_blank");
        },
        pdf_anfitrionas_general: function(dato){
        	$("#modal_empleados").modal("show");
			this.$http.get(url+"restaurante/caja/pdf_vendedores_caja_directo/"+dato.codcontroldiario).then(function(data){
				$("#modal_empleados_contenido").empty().html(data.body);
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); 
			});
        },
        pdf_venta: function(dato){
            var phuyu_url = url+"restaurante/caja/venta_diaria/"+dato.codcontroldiario;
            window.open(phuyu_url,"_blank");
        },
        pdf_balance: function(dato){
            var phuyu_url = url+"restaurante/caja/balance_caja/"+dato.codcontroldiario;
            window.open(phuyu_url,"_blank");
        },

		pdf_arqueo_caja: function(dato){
            var phuyu_url = url+"caja/controlcajas/pdf_arqueo_caja/"+dato.codcontroldiario;
            window.open(phuyu_url,"_blank");
        },
        pdf_arqueo_excel: function(dato){
            var phuyu_url = url+"caja/controlcajas/pdf_arqueo_excel/"+dato.codcontroldiario;
            window.open(phuyu_url,"_blank");
        }
	},
	created: function(){
		this.phuyu_datos();
	}
});