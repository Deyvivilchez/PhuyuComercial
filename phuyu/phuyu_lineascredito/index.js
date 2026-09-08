var phuyu_lineas = new Vue({
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
			this.cargando = true; this.registro = 0; this.estado = 0;
			$(".eliminar,.editar").attr('disabled',false);

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
			}else{
				$(".eliminar").attr('disabled',false);
				$(".editar").attr('disabled',false);
			}
		},
		phuyu_formato: function(){
			this.$http.get(url+phuyu_controller+"/formato/"+this.formato_impresion).then(function(data){
				phuyu_sistema.phuyu_modulo();
			});
		},
        restaurar_venta: function(registro){
        	swal({
				title: "SEGURO DESEA RESTAURAR LA LINEA?",   
				text: "USTED ESTA POR RESTAURAR UNA LINEA DE CREDITO", 
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
			this.registro = 0;
			phuyu_sistema.phuyu_inicio();
			this.$http.post(url+phuyu_controller+"/nuevo").then(function(data){
				$("#phuyu_sistema").empty().html(data.body).show();
			},function(){
				phuyu_sistema.phuyu_error();
			});
		},
		phuyu_ver: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UNA LINEA", "PARA VER EN EL SISTEMA LA LINEA DE CREDITO!!!","error");
			}else{
				$(".compose").removeClass("col-md-4").addClass("col-md-7");
				$(".compose").slideToggle(); $("#phuyu_tituloform").text("INFORMACION DE LA LINEA DE CREDITO"); 
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
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UNA LINEA", "PARA EDITAR EN EL SISTEMA LA LINEA DE CREDITO !!!","error");
			}else{
				if(this.estado==0){
					return false;
				}
				phuyu_sistema.phuyu_inicio();
				this.$http.post(url+phuyu_controller+"/nuevo").then(function(data){
					$("#phuyu_sistema").empty().html(data.body).show();
				},function(){
					phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); phuyu_sistema.phuyu_fin();
				});
			}
		},
		phuyu_imprimir: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UNA LINEA", "PARA IMPRIMIR EN EL SISTEMA LA LINEA DE CREDITO !!!","error");
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
				if(this.estado==0){
					return false;
				}
				swal({
					title: "SEGURO ANULAR LINEA DE CREDITO ?",   
					text: "USTED ESTA POR ANULAR UNA LINEA DE CREDITO", 
					icon: "warning",
					dangerMode: true,
					buttons: ["CANCELAR", "SI, ELIMINAR"],
					content: {
					    element: "input",
					    attributes: {
					      	placeholder: "PORQUE DESEAS ANULAR LA LINEA",
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
									phuyu_sistema.phuyu_alerta("NO PUEDE ANULAR LA LINEA DE CREDITO", "TIENE CREDITOS ACTIVOS ASOCIADOS","error");
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
