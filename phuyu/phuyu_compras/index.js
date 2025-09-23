var phuyu_compras = new Vue({
	el: "#phuyu_compras",
	data: {
		cargando: true, registro:0, estado:0, buscar: "", datos: [], fechas:{"filtro":1,"desde":"","hasta":""},
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
		
		phuyu_seleccionar: function(registro,estado){
			this.registro = registro;
			this.estado = estado;
			if(estado==0){
				$(".eliminar").attr('disabled',true);
				$(".editar").attr('disabled',true);
				$(".gasto").attr('disabled',true);
			}else{
				$(".eliminar").attr('disabled',false);
				$(".editar").attr('disabled',false);
				$(".gasto").attr('disabled',false);
			}
		},
		phuyu_nuevo:function(){
			this.registro = 0;
			if ($("#almacen").val()!=2) {
				phuyu_sistema.phuyu_alerta("ATENCION USUARIO","DEBES CONFIGURAR LOS COMPROBANTES DE ALMACEN","error");
			}else{
				if ($("#caja").val()==0) {
					phuyu_sistema.phuyu_alerta("ATENCION USUARIO","DEBES APERTURAR LA CAJA PARA LAS COMPRAS","error"); 
				}else{
					phuyu_sistema.phuyu_inicio();
					this.$http.post(url+phuyu_controller+"/nuevo").then(function(data){
						$("#phuyu_sistema").empty().html(data.body);
					},function(){
						phuyu_sistema.phuyu_error();
					});
				}
			}
		},
		phuyu_ver: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UNA COMPRA", "PARA VER EN EL SISTEMA LA COMPRA !!!","error");
			}else{
				$(".compose").removeClass("col-md-4").addClass("col-md-7");
				$(".compose").slideToggle(); $("#phuyu_tituloform").text("INFORMACION DE LA COMPRA REGISTRADA"); 
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
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UNA COMPRA", "PARA EDITAR EN EL SISTEMA LA COMPRA !!!","error");
			}else{
				if(this.estado==0){
					return false;
				}
				// EDITAR DE COMPRAS CON TODO DETALLE //
				/* if ($("#sessioncaja").val()==0) {
					phuyu_sistema.phuyu_noti("ESTIMADO USUARIO SU CAJA NO ESTA APERTURADA", "NO PUEDE REALIZAR COMPRAS","error"); 
				}else{
					phuyu_sistema.phuyu_inicio();
					this.$http.post(url+phuyu_controller+"/nuevo").then(function(data){
						$("#phuyu_sistema").empty().html(data.body);
					},function(){
						phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); 
						phuyu_sistema.phuyu_fin();
					});
				} */
				$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario",180);
				this.$http.post(url+phuyu_controller+"/editarcompra",{"codregistro":this.registro}).then(function(data){
					$("#phuyu_formulario").empty().html(data.body);
					phuyu_sistema.phuyu_finloader("phuyu_formulario");
				},function(){
					phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); 
					phuyu_sistema.phuyu_finloader("phuyu_formulario");
				});
			}
		},
		phuyu_egresos: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UNA COMPRA", "PARA CARGAR LOS GASTOS DE TRASPORTE O OTROS A LA COMPRA","error");
			}else{
				if ($("#sessioncaja").val()==0) {
					phuyu_sistema.phuyu_noti("ESTIMADO USUARIO SU CAJA NO ESTA APERTURADA", "NO PUEDE REGISTRAR GASTOS","error"); 
				}else{
					if(this.estado==0){
						return false;
					}
					$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario",180);
					this.$http.get(url+"caja/movimientos/nuevo_1/2/"+this.registro).then(function(data){
						$("#phuyu_formulario").empty().html(data.body);
						phuyu_sistema.phuyu_finloader("phuyu_formulario");
					},function(){
						phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); 
						phuyu_sistema.phuyu_finloader("phuyu_formulario");
					});
				}
			}
		},
		phuyu_eliminar: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UNA COMPRA", "PARA ELIMINAR EN EL SISTEMA UNA COMPRA !!!","error");
			}else{
				if(this.estado==0){
					return false;
				}
				swal({
					title: "SEGURO ELIMINAR COMPRA ?",   
					text: "USTED ESTA POR ELIMINAR UNA COMPRA", 
					icon: "warning",
					dangerMode: true,
					buttons: ["CANCELAR", "SI, ELIMINAR"],
					content: {
					    element: "input",
					    attributes: {
					      	placeholder: "PORQUE DESEAS ELIMINAR LA COMPRA",
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
									phuyu_sistema.phuyu_alerta("NO PUEDE ANULAR LA COMPRA AL CREDITO", "DEBES ANULAR EL CREDITO","error");
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
		},
		phuyu_clonar: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UNA COMPRA", "PARA CLONAR EN EL SISTEMA UNA COMPRA !!!","error");
			}else{
				swal({
					title: "SEGURO DESEA CLONAR ESTA COMPRA ?",   
					text: "USTED ESTA POR CLONAR UNA COMPRA", 
					icon: "warning",
					dangerMode: true,
					buttons: ["CANCELAR", "SI, CLONAR"],
				}).then((willDelete) => {
					if (willDelete) {
						if ($("#almacen").val()!=2) {
							phuyu_sistema.phuyu_alerta("ATENCION USUARIO","DEBES CONFIGURAR LOS COMPROBANTES DE ALMACEN","error");
						}else{
							if ($("#caja").val()==0) {
								phuyu_sistema.phuyu_alerta("ATENCION USUARIO","DEBES APERTURAR LA CAJA PARA LAS COMPRAS","error"); 
							}else{
								phuyu_sistema.phuyu_inicio();
								this.$http.post(url+phuyu_controller+"/nuevo",{"codregistro":this.registro}).then(function(data){
									$("#phuyu_sistema").empty().html(data.body);
								},function(){
									phuyu_sistema.phuyu_error();
								});
							}
						}
					}
				});
			}
		}
	},
	created: function(){
		this.phuyu_datos();
	}
});