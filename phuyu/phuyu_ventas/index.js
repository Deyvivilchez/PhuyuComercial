var phuyu_ventas = new Vue({
	el: "#phuyu_ventas",
	data: {
		cargando: true, registro:0, estado:0, buscar: "", formato_impresion: $("#formato").val(), datos: [], fechas:{"filtro":1,"desde":"","hasta":""},
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
				$(".restaurar").attr('disabled',false);
			}else{
				$(".eliminar").attr('disabled',false);
				$(".restaurar").attr('disabled',true);
			}
		},
		phuyu_formato: function(){
			this.$http.get(url+phuyu_controller+"/formato/"+this.formato_impresion).then(function(data){
				phuyu_sistema.phuyu_modulo();
			});
		},
        restaurar_venta: function(){
        	if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UN REGISTRO", "PARA RESTAURAR UNA VENTA EN EL SISTEMA!!!","error");
			}else{
				if(this.estado!=0){
					return false;
				}
	        	swal({
					title: "SEGURO DESEA RESTAURAR LA VENTA?",   
					text: "USTED ESTA POR RESTAURAR UNA VENTA", 
					icon: "warning",
					dangerMode: true,
					buttons: ["CANCELAR", "SI, RESTAURAR"],
				}).then((willDelete) => {
					if (willDelete) {
						this.$http.post(url+phuyu_controller+"/restaurar",{"codregistro":this.registro}).then(function(data){
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
			}
        },
		phuyu_nuevo:function(){
			if ($("#almacen").val()!=2) {
				phuyu_sistema.phuyu_alerta("ATENCION USUARIO","DEBES CONFIGURAR LOS COMPROBANTES DE ALMACEN","error");
			}else{
				if ($("#caja").val()==0) {
					phuyu_sistema.phuyu_alerta("ATENCION USUARIO","DEBES APERTURAR LA CAJA PARA LAS VENTAS","error"); 
				}else{
					$(".compose").removeClass("col-md-7").addClass("col-md-4");
					this.registro = 0;
					phuyu_sistema.phuyu_inicio();
					this.$http.post(url+phuyu_controller+"/nuevo").then(function(data){
						$("#phuyu_sistema").empty().html(data.body).show();
					},function(){
						phuyu_sistema.phuyu_error();
					});
				}
			}
		},
		phuyu_ver: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UNA VENTA", "PARA VER EN EL SISTEMA LA VENTA!!!","error");
			}else{
				$(".compose").removeClass("col-md-4").addClass("col-md-7");
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
		phuyu_imprimir: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UNA VENTA", "PARA IMPRIMIR EN EL SISTEMA LA VENTA !!!","error");
			}else{
				if ($("#formato").val()=="ticket") {
					window.open(url+"facturacion/formato/ticket/"+this.registro,"_blank");
				}else{
					var phuyu_url = url+"facturacion/formato/"+$("#formato").val()+"/"+this.registro;
					window.open(phuyu_url,"_blank");
					//$("#phuyu_pdf").attr("src",phuyu_url); $("#modal_reportes").modal("show");
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
				if(this.estado==0){
					return false;
				}
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
					      	value: ""
					    },
					},
				}).then((willDelete) => {
					if (willDelete) {
						this.$http.post(url+phuyu_controller+"/eliminar",{"codregistro":this.registro,"observaciones":$(".swal-content__input").val()}).then(function(data){
							if (data.body.estado==1) {
								phuyu_sistema.phuyu_alerta("ELIMINADO CORRECTAMENTE", "UN REGISTRO ELIMINADO EN EL SISTEMA","success");
								this.phuyu_datos();
							}
							else if(data.body.estado==2){
								swal({
									title: data.body.mensaje,   
									text: "", 
									icon: "warning",
									dangerMode: true,
									buttons: ["CANCELAR", "SI, ELIMINAR"],
								}).then((willDelete) => {
									if (willDelete) {
										this.$http.post(url+phuyu_controller+"/eliminarinterno",{"codregistro":this.registro}).then(function(data){
											if (data.body==1) {
												phuyu_sistema.phuyu_alerta("ELIMINADO CORRECTAMENTE", "UN REGISTRO ELIMINADO EN EL SISTEMA","success");
												this.phuyu_datos();
											}else{
												phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR !!!", "SE PERDIÓ LA CONEXION !!! LO SENTIMOS","error");				
											}
											this.phuyu_datos();
										}, function(){
											phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
										});
									}
								});
							}
							else{
								phuyu_sistema.phuyu_alerta(data.body.mensaje, "","error");
							}
						}, function(){
							phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
						});
					}
				});
			}
		},
		phuyu_docu: function(tipo,codkardex){
			if(tipo=="pdf"){
				if ($("#formato").val()=="ticket") {
					window.open(url+"facturacion/formato/ticket/"+codkardex,"_blank");
				}else{
					var phuyu_url = url+"facturacion/formato/"+$("#formato").val()+"/"+codkardex;
					$("#phuyu_pdf").attr("src",phuyu_url); $("#modal_reportes").modal("show");
				}
			}else{
				window.open(url+"facturacion/comprobantes/phuyu_"+tipo+"/"+codkardex,"_blank");
			}
		},
		phuyu_clonar: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UNA VENTA REGISTRADA", "PARA CLONAR EN EL SISTEMA !!!","error");
			}else{
				swal({
					title: "SEGURO DESEA CLONAR ESTA VENTA ?",   
					text: "USTED ESTA POR CLONAR UNA VENTA", 
					icon: "warning",
					dangerMode: true,
					buttons: ["CANCELAR", "SI, CLONAR"],
				}).then((willDelete) => {
					if (willDelete) {
						if ($("#almacen").val()!=2) {
							phuyu_sistema.phuyu_alerta("ATENCION USUARIO","DEBES CONFIGURAR LOS COMPROBANTES DE ALMACEN","error");
						}else{
							if ($("#caja").val()==0) {
								phuyu_sistema.phuyu_alerta("ATENCION USUARIO","DEBES APERTURAR LA CAJA PARA LAS VENTAS","error"); 
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
		this.phuyu_datos(); phuyu_sistema.phuyu_fin();
	}
});