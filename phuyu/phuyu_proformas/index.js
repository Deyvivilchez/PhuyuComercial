var phuyu_proformas = new Vue({
	el: "#phuyu_pedidos",
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

		phuyu_nuevo:function(){
			if ($("#almacen").val()!=1) {
				phuyu_sistema.phuyu_alerta("ATENCION USUARIO","DEBES CONFIGURAR LOS COMPROBANTES DE PROFORMA","error");
			}else{
				this.registro = 0;
				phuyu_sistema.phuyu_inicio();
				this.$http.post(url+phuyu_controller+"/nuevo").then(function(data){
					$("#phuyu_sistema").empty().html(data.body);
				},function(){
					phuyu_sistema.phuyu_error();
				});
			}
		},
		phuyu_ver: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UNA PROFORMA", "PARA VER EN EL SISTEMA EL COMPROBANTE!!!","error");
			}else{
				$(".compose").removeClass("col-md-4").addClass("col-md-7");
				$(".compose").slideToggle(); $("#phuyu_tituloform").text("INFORMACION DE LA PROFORMA REGISTRADA"); 
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
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UNA PROFORMA", "PARA EDITAR EN EL SISTEMA LA PROFORMA !!!","error");
			}else{
				if(this.estado==0){
					return false;
				}
				phuyu_sistema.phuyu_inicio();
				this.$http.post(url+phuyu_controller+"/editar",{"codregistro":this.registro}).then(function(data){
					if(data.body == 1){
                       phuyu_sistema.phuyu_alerta("LA PROFORMA NO PUEDE SER EDITADO", "LA PROFORMA YA SE ENCUANTRA ATENDIDO !!!","error"); phuyu_sistema.phuyu_fin();
					}else{
					   $("#phuyu_sistema").empty().html(data.body);
					}
				},function(){
					phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); phuyu_sistema.phuyu_fin();
				});
			}
		},
		phuyu_imprimir: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UNA PROFORMA", "PARA IMPRIMIR EN EL SISTEMA LA PROFORMA !!!","error");
			}else{
				window.open(url+"facturacion/formato/"+$("#formato").val()+"proforma/"+this.registro,"_blank");
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
					title: "SEGURO ELIMINAR LA PROFORMA?",   
					text: "USTED ESTA POR ELIMINAR EL DOCUMENTO", 
					icon: "warning",
					dangerMode: true,
					buttons: ["CANCELAR", "SI, ELIMINAR"],
					content: {
					    element: "input",
					    attributes: {
					      	placeholder: "PORQUE DESEAS ELIMINAR LA PROFORMA?",
					      	type: "text",
					    },
					},
				}).then((willDelete) => {
					if (willDelete) {
						this.$http.post(url+phuyu_controller+"/eliminar",{"codregistro":this.registro,"observaciones":$(".swal-content__input").val()}).then(function(data){
							if (data.body.estado==1) {
								phuyu_sistema.phuyu_alerta("ELIMINADO CORRECTAMENTE", "UN REGISTRO ELIMINADO EN EL SISTEMA","success");
							}else{
								if (data.body.estado==2) {
									phuyu_sistema.phuyu_alerta("NO PUEDE ANULAR LA PROFORMA", data.body.mensaje,"error");
								}else{
									phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR !!!", data.body.mensaje,"error");
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
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UNA PROFORMA REGISTRADO", "PARA CLONAR EN EL SISTEMA !!!","error");
			}else{
				swal({
					title: "SEGURO DESEA CLONAR LA PROFORMA ?",   
					text: "USTED ESTA POR CLONAR UNA PROFORMA", 
					icon: "warning",
					dangerMode: true,
					buttons: ["CANCELAR", "SI, CLONAR"],
				}).then((willDelete) => {
					if (willDelete) {
						if ($("#almacen").val()!=1) {
							phuyu_sistema.phuyu_alerta("ATENCION USUARIO","DEBES CONFIGURAR LOS COMPROBANTES DE PROFORMAS","error");
						}else{
							phuyu_sistema.phuyu_inicio();
							this.$http.post(url+phuyu_controller+"/nuevo",{"codregistro":this.registro}).then(function(data){
								$("#phuyu_sistema").empty().html(data.body);
							},function(){
								phuyu_sistema.phuyu_error();
							});
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