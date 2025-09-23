var phuyu_ingresos = new Vue({
	el: "#phuyu_ingresos",
	data: {
		cargando: true, registro:0, estado:0, buscar: "",movimiento:0, datos: [], fechas:{"filtro":1,"desde":"","hasta":""}, formato_impresion: $("#formato").val(),
		paginacion: {"total":0, "actual":1, "ultima":0, "desde":0, "hasta":0}, offset: 3,
		estado_envio:0,	kardex_ref:0, codpersona:0, putunidades:[], detalle_prestamo:[],prestamos:0, numprestamo:"",detallecobroprestamo:[], transferencias:1, texto_transferencia:"", listatransferencias:[], detalletransferencia:[]
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
			this.fechas.desde = $("#fecha_desde").val(); this.fechas.hasta = $("#fecha_hasta").val();
			this.$http.post(url+phuyu_controller+"/lista",{"movimiento":this.movimiento,"buscar":this.buscar,"fechas":this.fechas, "pagina":this.paginacion.actual}).then(function(data){
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
		
		phuyu_nuevo:function(){
			this.registro = 0;
			phuyu_sistema.phuyu_inicio();
			this.$http.post(url+phuyu_controller+"/nuevo").then(function(data){
				$("#phuyu_sistema").empty().html(data.body);
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); 
				phuyu_sistema.phuyu_fin();
			});
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
		phuyu_ver: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UN INGRESO", "PARA VER EN EL SISTEMA EL INGRESO!!!","error");
			}else{
				$(".compose").removeClass("col-md-4").addClass("col-md-7");
				$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario",180);
				this.$http.get(url+phuyu_controller+"/ver/"+this.registro).then(function(data){
					$("#phuyu_formulario").empty().html(data.body);
					phuyu_sistema.phuyu_finloader("phuyu_formulario");
				},function(){
					phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); phuyu_sistema.phuyu_finloader("phuyu_formulario");
				});
			}
		},
		phuyu_editar: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UN INGRESO", "PARA EDITAR EN EL SISTEMA EL INGRESO ALMACEN !!!","error");
			}else{
				if(this.estado==0){
					return false;
				}
				$(".compose").removeClass("col-md-7").addClass("col-md-4");
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
		phuyu_eliminar: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UN REGISTRO", "PARA ELIMINAR EN EL SISTEMA UN REGISTRO!!!","error");
			}else{
				if(this.estado==0){
					return false;
				}
				swal({
					title: "SEGURO ELIMINAR INGRESO DE ALMACEN ?",   
					text: "USTED ESTA POR ELIMINAR UNA INGRESO DE ALMACEN", 
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
							this.phuyu_datos();
						}, function(){
							alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
						});
					}
				});
			}
		},

		phuyu_trasferencias: function(){
			this.transferencias = 1; $("#modal_transferencias").modal("show");
			this.$http.get(url+phuyu_controller+"/transferencias").then(function(data){
				this.listatransferencias = data.body;
			},function(){
				phuyu_sistema.phuyu_alerta("NO SE PUEDE ABRIR LA VENTANA DE TRANSAFERENCIAS DE ALMACEN", "ERROR DE RED","error"); 
				$("#modal_transferencias").modal("hide");
			});
		},
		phuyu_detalle: function(campo){
			this.texto_transferencia = "ALM: "+campo.almacen + " *** REF: "+campo.seriecomprobante+" - "+campo.nrocomprobante;
			this.$http.get(url+phuyu_controller+"/transferencia_detalle/"+campo.codkardex).then(function(data){
				this.transferencias = 0; this.detalletransferencia = data.body; this.kardex_ref = campo.codkardex; this.estado_envio = 0;
			},function(){
				phuyu_sistema.phuyu_alerta("NO SE PUEDE ABRIR LA VENTANA DEL DETALLE DE LA TRANSAFERENCIA", "ERROR DE RED","error"); 
				this.transferencias = 1;
			});
		},
		phuyu_detalleprestamo: function(campo){
			this.texto_transferencia = "RAZON SOCIAL: "+campo.cliente + " *** REF: "+campo.seriecomprobante+" - "+campo.nrocomprobante;
			this.codpersona = campo.codpersona
			this.$http.get(url+phuyu_controller+"/prestamo_detalle/"+campo.codkardex).then(function(data){
				this.prestamos = 1;this.kardex_ref = campo.codkardex; 
				this.estado_envio = 0;
				var productos = eval(data.body);
				var filas = [];
				$.each( productos, function( k, v ) {
				    var unidades = []; var factores = []; var logo = []; arreglo = [];
		    		unidades = (v.unidades).split(";"); var funidades = [];

			    	for (var i = 0; i < unidades.length; i++) {
	                    factores = (unidades[i]).split("|");
			    		logo = {descripcion:factores[1],codunidad:factores[0],factor:factores[8]};
			    		funidades.push(logo)
			    		if(factores[8]==1){
			    			v.codunidad = factores[0];
			    		}
			    	}
			    	this.putunidades = funidades;
			    	v.subtotal = parseFloat(v.cantidad)*parseFloat(v.precio);
			    	v.valorventa = parseFloat(v.cantidad)*parseFloat(v.preciosinigv);
					filas.push({
						"itemorigen":v.item,"codproducto":v.codproducto,"producto":v.producto,"codunidad":v.codunidad,"unidades": this.putunidades,
						"unidad":v.unidad,"cantidad":v.cantidad,"stock":v.stock,"control":v.controlstock,"preciounitario":parseFloat(v.precio).toFixed(2),
						"preciorefunitario":v.precio,"subtotal":v.subtotal,"valorventa":v.valorventa,"codafectacionigv":v.codafectacionigv,"igv":v.igv,
						"preciosinigv" : v.preciosinigv
					});
					this.putunidades = [];
				});

				this.detallecobroprestamo = filas
			},function(){
				phuyu_sistema.phuyu_alerta("NO SE PUEDE ABRIR LA VENTANA DEL DETALLE DE LA TRANSAFERENCIA", "ERROR DE RED","error"); 
				this.prestamos = 0;
			});
		},
		phuyu_calcular: function(campo){
			this.detalletransferencia.subtotal = this.detalletransferencia.subtotal - campo.subtotal;
			campo.subtotal = campo.cantidad * campo.precio;
			this.detalletransferencia.subtotal = this.detalletransferencia.subtotal + campo.subtotal;
		},
		phuyu_calcularprestamo: function(campo){
			this.detallecobroprestamo.subtotal = this.detallecobroprestamo.subtotal - campo.subtotal;
			campo.subtotal = campo.cantidad * campo.preciounitario;
			this.detallecobroprestamo.subtotal = this.detallecobroprestamo.subtotal + campo.subtotal;
		},
		phuyu_guardartransferencia: function(){
			this.estado_envio = 1; phuyu_sistema.phuyu_inicio_guardar("GUARDANDO Y ACEPTANDO LA TRANSFERENCIA DE ALMACEN . . .");
			this.$http.post(url+phuyu_controller+"/guardar_transferencia", {"kardex_ref":this.kardex_ref,"detalle":this.detalletransferencia}).then(function(data){
				if (data.body=="e") {
					phuyu_sistema.phuyu_alerta("SU SESION DE USUARIO A TERMINADO","DEBE INICIAR SESION NUEVAMENTE","error");
				}else{
					if (data.body==1) {
						phuyu_sistema.phuyu_alerta("TRANSFERENCIA DE ALMACEN REGISTRADO","INGRESO DE ALMACEN EN EL SISTEMA","success");
					}else{
						phuyu_sistema.phuyu_alerta("ERROR AL ACEPTAR LA TRANSFERENCIA DE ALMACEN","ERROR DE RED","error");
					}
				}
				phuyu_sistema.phuyu_fin(); this.phuyu_datos(); $("#modal_transferencias").modal("hide");
			}, function(){
				phuyu_sistema.phuyu_alerta("ERROR AL ACEPTAR LA TRANSFERENCIA DE ALMACEN","ERROR DE RED","error"); phuyu_sistema.phuyu_fin();
			});
		},
		phuyu_guardarcobroprestamo: function(){
			this.estado_envio = 1; phuyu_sistema.phuyu_inicio_guardar("GUARDANDO Y ACEPTANDO EL COBRO DE PRESTAMO . . .");
			this.$http.post(url+phuyu_controller+"/guardar_prestamo", {"codpersona": this.codpersona,"kardex_ref":this.kardex_ref,"detalle":this.detallecobroprestamo}).then(function(data){
				if (data.body=="e") {
					phuyu_sistema.phuyu_alerta("SU SESION DE USUARIO A TERMINADO","DEBE INICIAR SESION NUEVAMENTE","error");
				}else{
					if (data.body==1) {
						phuyu_sistema.phuyu_alerta("COBRO DE PRESTAMO REGISTRADO","INGRESO DE ALMACEN EN EL SISTEMA","success");
					}else{
						phuyu_sistema.phuyu_alerta("ERROR AL ACEPTAR EL COBRO DE PRESTAMO","ERROR DE RED","error");
					}
				}
				this.prestamos = 0;
				phuyu_sistema.phuyu_fin(); this.phuyu_datos(); $("#modal_prestamos").modal("hide");
			}, function(){
				phuyu_sistema.phuyu_alerta("ERROR AL ACEPTAR EL COBRO DE PRESTAMO","ERROR DE RED","error"); phuyu_sistema.phuyu_fin();
			});
		},
		phuyu_deleteitem: function(index,producto){
			this.detallecobroprestamo.splice(index,1);
			this.putunidades = [];
		},
		cerrar_modalprestamo:function(){
			this.prestamos = 0;
		},
		phuyu_imprimir: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UN INGRESO", "PARA IMPRIMIR EN EL SISTEMA EL INGRESO !!!","error");
			}else{
				if ($("#formato").val()=="ticket") {
					window.open(url+"facturacion/formato/ticketmovimiento/"+this.registro,"_blank");
				}else{
					window.open(url+"facturacion/formato/"+$("#formato").val()+"movimiento/"+this.registro,"_blank");
				}
			}
        },
		phuyu_clonar: function(){
			if (this.registro==0) {
				phuyu_sistema.phuyu_alerta("DEBE SELECCIONAR UN INGRESO REGISTRADA", "PARA CLONAR EN EL SISTEMA !!!","error");
			}else{
				swal({
					title: "SEGURO DESEA CLONAR ESTE INGRESO ?",   
					text: "USTED ESTA POR CLONAR UN INGRESO", 
					icon: "warning",
					dangerMode: true,
					buttons: ["CANCELAR", "SI, CLONAR"],
				}).then((willDelete) => {
					if (willDelete) {
						phuyu_sistema.phuyu_inicio();
						this.$http.post(url+phuyu_controller+"/nuevo",{"codregistro":this.registro}).then(function(data){
							$("#phuyu_sistema").empty().html(data.body);
						},function(){
							phuyu_sistema.phuyu_error();
						});
					}
				});
			}
		},
		phuyu_modalprestamos: function(){
			this.$http.post(url+phuyu_controller+"/listarprestamos").then(function(data){
				this.detalle_prestamo = data.body;
			},function(){
				phuyu_sistema.phuyu_error();
			});
		}
	},
	created: function(){
		this.phuyu_datos();
	}
});