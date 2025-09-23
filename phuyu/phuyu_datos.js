var phuyu_datos = new Vue({
	el: "#phuyu_datos",
	data: {
		cargando: true, registro:0, buscar: "", datos: [],
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
			if ($("#phuyu_opcion").val()==1) {
				this.phuyu_datos_1();
			}else{
				this.phuyu_datos_2();
			}
		},
		phuyu_datos_1: function(){
			this.cargando = true; this.registro = 0;
			this.$http.post(url+phuyu_controller+"/lista",{"buscar":this.buscar, "pagina":this.paginacion.actual,"sucursal": $("#sucursal_search").val()}).then(function(data){
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
		
		phuyu_nuevo: function(){
			$('td').removeClass("highlightRow")
			this.registro = 0;
			$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario",180);
			$(".phuyu_radio").removeAttr('checked');
			this.$http.post(url+phuyu_controller+"/nuevo").then(function(data){
				$("#phuyu_formulario").empty().html(data.body);phuyu_sistema.phuyu_finloader("phuyu_formulario");
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); phuyu_sistema.phuyu_finloader("phuyu_formulario");
			});
		},
		phuyu_seleccionar: function(registro){
			this.registro = registro;
		},
		phuyu_ver: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UN REGISTRO", "PARA VER EN EL SISTEMA EL REGISTRO!!!","error");
			}else{
				$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario",180);
				this.$http.get(url+phuyu_controller+"/ver/"+this.registro).then(function(data){
					$("#phuyu_formulario").empty().html(data.body);phuyu_sistema.phuyu_finloader("phuyu_formulario");
				},function(){
					phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); phuyu_sistema.phuyu_finloader("phuyu_formulario");
				});
			}
		},
		phuyu_editar: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UN REGISTRO", "PARA EDITAR EN EL SISTEMA UN REGISTRO!!!","error");
			}else{
				$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario",180);
				this.$http.post(url+phuyu_controller+"/nuevo").then(function(data){
					this.$http.post(url+phuyu_controller+"/editar",{"codregistro":this.registro}).then(function(info){
						$("#phuyu_formulario").empty().html(data.body); var datos = eval(info.body);
						
						$.each(campos, function(key, value){
							campos[key] = datos[0][key];
						});
						phuyu_form.campos = campos;
						if(phuyu_controller=="administracion/comprobantes"){
							phuyu_form.phuyu_editarcomprobante();
						}
						phuyu_sistema.phuyu_finloader("phuyu_formulario");
					});
				},function(){
					phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); phuyu_sistema.phuyu_finloader("phuyu_formulario");
				});
			}
		},
		phuyu_eliminar: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UN REGISTRO", "PARA ELIMINAR EN EL SISTEMA UN REGISTRO!!!","error");
			}else{
				swal({
					title: "SEGURO ELIMINAR REGISTRO ?",   
					text: "USTED ESTA POR ELIMINAR UN REGISTRO", 
					icon: "warning",
					dangerMode: true,
					buttons: ["CANCELAR", "SI, ELIMINAR"],
				}).then((willDelete) => {
					if (willDelete) {
						this.$http.post(url+phuyu_controller+"/eliminar",{"codregistro":this.registro}).then(function(data){
							if (data.body==1) {
								phuyu_sistema.phuyu_alerta("ELIMINADO CORRECTAMENTE", "UN REGISTRO ELIMINADO EN EL SISTEMA","success");
							}else{
								phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR !!!", "SE PERDIÓ LA CONEXION !!! LO SENTIMOS","error");
							}
							this.phuyu_opcion();
						}, function(){
							phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
						});
					}
				});
			}
		},
		phuyu_permisos: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UN REGISTRO", "PARA EDITAR EN EL SISTEMA UN REGISTRO!!!","error");
			}else{
				$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario",180);
				this.$http.post(url+phuyu_controller+"/permisos",{"codregistro":this.registro}).then(function(data){
					$("#phuyu_formulario").empty().html(data.body); phuyu_form.campos.codperfil = this.registro;
					phuyu_sistema.phuyu_finloader("phuyu_formulario");
				},function(){
					phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); phuyu_sistema.phuyu_finloader("phuyu_formulario");
				});
			}
		},
		phuyu_asignarzona: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UN REGISTRO", "PARA ASIGNAR ZONAS AL REGISTRO!!!","error");
			}else{
				$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario",180);
				this.$http.post(url+phuyu_controller+"/asignarzona",{"codregistro":this.registro}).then(function(data){
					$("#phuyu_formulario").empty().html(data.body); phuyu_form.campos.codusuario = this.registro;
					phuyu_sistema.phuyu_finloader("phuyu_formulario");
				},function(){
					phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); phuyu_sistema.phuyu_finloader("phuyu_formulario");
				});
			}
		},
		marcar: function(obj,dato) {
			obj = obj + 1
			if($('tr:eq('+obj+') td').hasClass("highlightRow")){
				$('tr:eq('+obj+') td').removeClass("highlightRow")
				this.registro = 0;
			}else{
				$('tr:eq('+obj+') td').addClass('highlightRow').parents('tr').siblings().find('td').removeClass('highlightRow')
				this.registro = dato
			}
            		
		},
		phuyu_asignarlineas: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UN REGISTRO", "PARA ASIGNAR LINEAS AL REGISTRO!!!","error");
			}else{
				$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario",180);
				this.$http.post(url+phuyu_controller+"/asignarlineas",{"codregistro":this.registro}).then(function(data){
					$("#phuyu_formulario").empty().html(data.body); phuyu_form.campos.codsucursal = this.registro;
				},function(){
					phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); phuyu_sistema.phuyu_fin();
				});
			}
		}
	},
	created: function(){
		this.phuyu_opcion(); phuyu_sistema.phuyu_fin();
	}
});