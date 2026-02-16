var phuyu_lineas = new Vue({
	el: "#phuyu_ventas",
	data: {
		cargando: true, registro:0, buscar: "", formato_impresion: $("#formato").val(), datos: [], fechas:{"filtro":1,"desde":"","hasta":""},
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
			this.fechas.desde = $("#fecha_desde").val(); this.fechas.hasta = $("#fecha_hasta").val();
			this.cargando = true; this.registro = 0;

			this.$http.post(url+phuyu_controller+"/lista",{"buscar":this.buscar,"fechas":this.fechas,"pagina":this.paginacion.actual}).then(function(data){
				this.datos = data.body.lista; this.paginacion = data.body.paginacion;
				this.cargando = false; phuyu_sistema.phuyu_fin();
			},function(){
				phuyu_sistema.phuyu_error(); this.cargando = false;
			});
		},
		phuyu_buscar: function(){
			this.paginacion.actual = 1; this.phuyu_datos();
		},
		phuyu_paginacion: function(pagina){
			this.paginacion.actual = pagina; this.phuyu_datos();
		},
		
		phuyu_seleccionar: function(registro){
			this.registro = registro;
		},
		phuyu_formato: function(){
			this.$http.get(url+phuyu_controller+"/formato/"+this.formato_impresion).then(function(data){
				phuyu_sistema.phuyu_modulo();
			});
		},
        restaurar_venta: function(registro){
        	swal({
				title: "SEGURO DESEA RESTAURAR LA VENTA?",   
				text: "USTED ESTA POR RESTAURAR UNA VENTA", 
				icon: "warning",
				dangerMode: true,
				buttons: ["CANCELAR", "SI, RESTAURAR"],
			}).then((willDelete) => {
				if (willDelete) {
					this.$http.post(url+phuyu_controller+"/restaurar",{"codregistro":registro}).then(function(data){
						if (data.body==1) {
							phuyu_sistema.phuyu_alerta("RESTAURADO CORRECTAMENTE", "UN REGISTRO RESTAURADO EN EL SISTEMA","success");
						}else{
							phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR !!!", "SE PERDIÓ LA CONEXION !!! LO SENTIMOS","error");				
						}
						this.phuyu_datos();
					}, function(){
						alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
					});
				}
			});
        },
		phuyu_nuevo:function(){
			phuyu_sistema.phuyu_inicio();
			this.$http.post(url+phuyu_controller+"/nuevo").then(function(data){
				$("#phuyu_sistema").empty().html(data.body).show();
			},function(){
				phuyu_sistema.phuyu_error();
			});
		},
		phuyu_ver: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UNA VENTA", "PARA VER EN EL SISTEMA LA VENTA!!!","error");
			}else{
				$(".compose").slideToggle(); $("#phuyu_tituloform").text("INFORMACION DE LA VENTA REGISTRADA"); 
				phuyu_sistema.phuyu_loader("phuyu_formulario",180);

				this.$http.get(url+phuyu_controller+"/ver/"+this.registro).then(function(data){
					$("#phuyu_formulario").empty().html(data.body);
					phuyu_sistema.phuyu_finloader("phuyu_formulario");
				},function(){
					phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); 
					phuyu_sistema.phuyu_finloader("phuyu_formulario");
				});
			}
		},
		phuyu_editar: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UNA VENTA", "PARA EDITAR EN EL SISTEMA LA VENTA !!!","error");
			}else{
				this.$http.post(url+phuyu_controller+"/nuevo").then(function(data){
					$("#phuyu_sistema").empty().html(data.body).show();
					/*this.$http.post(url+phuyu_controller+"/editar",{"codregistro":this.registro}).then(function(info){
						$("#phuyu_sistema").empty().html(data.body); var datos = eval(info.body);
						$.each(campos, function(key, value){
							campos[key] = datos[0][key];
						});
					},function(){
						phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); phuyu_sistema.phuyu_fin();
					});*/
				});
			}
		},
		phuyu_imprimir: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UNA VENTA", "PARA IMPRIMIR EN EL SISTEMA LA VENTA !!!","error");
			}else{
				if ($("#formato").val()=="ticket") {
					window.open(url+"facturacion/formato/ticket/"+this.registro,"_blank");
				}else{
					var phuyu_url = url+"facturacion/formato/"+$("#formato").val()+"/"+this.registro;
					$("#phuyu_pdf").attr("src",phuyu_url); $("#modal_reportes").modal("show");
				}
				
				/* if ($("#phuyu_formato").val()==0) {
					var phuyu_url = url+"facturacion/formato/a4/"+this.registro;
	            	$("#phuyu_pdf").attr("src",phuyu_url); $("#modal_reportes").modal("show");
				}else{
					if ($("#phuyu_formato").val()==1) {
						var phuyu_url = url+"facturacion/formato/a5/"+this.registro;
	            		$("#phuyu_pdf").attr("src",phuyu_url); $("#modal_reportes").modal("show");
					}else{
						window.open(url+"facturacion/formato/ticket/"+this.registro,"_blank");
					}
				} */
			}
        },
		phuyu_eliminar: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UN REGISTRO", "PARA ELIMINAR EN EL SISTEMA UN REGISTRO!!!","error");
			}else{
				swal({
					title: "SEGURO ELIMINAR VENTA ?",   
					text: "USTED ESTA POR ELIMINAR UNA VENTA", 
					icon: "warning",
					dangerMode: true,
					buttons: ["CANCELAR", "SI, ELIMINAR"],
					content: {
					    element: "input",
					    attributes: {
					      	placeholder: "PORQUE DESEAS ELIMINAR LA VENTA",
					      	type: "text",
					    },
					},
				}).then((willDelete) => {
					if (willDelete) {
						this.$http.post(url+phuyu_controller+"/eliminar",{"codregistro":this.registro,"observaciones":$(".swal-content__input").val()}).then(function(data){
							if (data.body==1) {
								phuyu_sistema.phuyu_alerta("ELIMINADO CORRECTAMENTE", "UN REGISTRO ELIMINADO EN EL SISTEMA","success");
							}else{
								if (data.body==2) {
									phuyu_sistema.phuyu_alerta("NO PUEDE ANULAR LA VENTA AL CREDITO", "DEBES ANULAR EL CREDITO","error");
								}else{
									phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR !!!", "SE PERDIÓ LA CONEXION !!! LO SENTIMOS","error");
								}
							}
							this.phuyu_datos();
						}, function(){
							alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
						});
					}
				});
			}
		}
	},
	created: function(){
		this.phuyu_datos(); phuyu_sistema.phuyu_fin();
	}
});