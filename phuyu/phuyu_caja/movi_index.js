var phuyu_datos = new Vue({
	el: "#phuyu_datos",
	data: {
		cargando: true, registro:0, buscar: "", datos: [], transferencias:[], 
		campos_t:{"codmovimiento":"","codpersona":"","codcaja":0,"codtipopago":0,"importe":0,"codcomprobantetipo":0,
		"seriecomprobante":"","nrocomprobante":"","fechadocbanco":"","nrodocbanco":""},
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
			this.cargando = true; this.registro = 0;
			this.$http.post(url+phuyu_controller+"/lista",{"buscar":this.buscar, "pagina":this.paginacion.actual}).then(function(data){
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
		
		phuyu_nuevo: function(){
			$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario",180);
			$(".phuyu_radio").removeAttr('checked'); this.registro = 0;
			this.$http.post(url+phuyu_controller+"/nuevo").then(function(data){
				$("#phuyu_formulario").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); 
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
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
						phuyu_sistema.phuyu_finloader("phuyu_formulario");
					});
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
							this.phuyu_datos();
						}, function(){
							alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
						});
					}
				});
			}
		},
		phuyu_transferencias: function(){
			this.$http.post(url+phuyu_controller+"/transferencias").then(function(data){
				this.transferencias = data.body; $("#modal_transferencias").modal("show");
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS", "ERROR DE RED","error");
			});
		},
		phuyu_aceptar_transferencia(datos){
			this.campos_t.codmovimiento = datos.codmovimiento;
			this.campos_t.codpersona = datos.codpersona;
			this.campos_t.codcaja = datos.codcaja;
			this.campos_t.codtipopago = datos.codtipopago;
			this.campos_t.importe = datos.importe;
			this.campos_t.codcomprobantetipo = datos.codcomprobantetipo;
			this.campos_t.seriecomprobante = datos.seriecomprobante;
			this.campos_t.nrocomprobante = datos.nrocomprobante;
			this.campos_t.fechadocbanco = datos.fechadocbanco;
			this.campos_t.nrodocbanco = datos.nrodocbanco;

			$("#modal_transferencias").modal("hide");
			swal({
				title: "SEGURO ACEPTAR ESTA TRANSFERENCIA DE CAJA ?",   
				text: "", 
				icon: "warning",
				dangerMode: true,
				buttons: ["CANCELAR", "SI, ACEPTAR"],
				content: {
				    element: "input",
				    attributes: {
				      	placeholder: "ESCRIBIR UNA REFERENCIA",
				      	type: "text",
				    },
				},
			}).then((willDelete) => {
				if (willDelete) {
					this.$http.post(url+phuyu_controller+"/aceptar_transferencia",{"campos":this.campos_t,"referencia":$(".swal-content__input").val()}).then(function(data){
						if (data.body==1) {
							phuyu_sistema.phuyu_alerta("TRANSFERENCIA ACEPTADA CORRECTAMENTE", "","success");
						}else{
							phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR !!!", "SE PERDIÓ LA CONEXION !!! LO SENTIMOS","error");
						}
						$("#modal_transferencias").modal("hide"); this.phuyu_datos();
					}, function(){
						phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
					});
				}else{
					$("#modal_transferencias").modal("show");
				}
			});
		},
		marcar: function(obj,dato,ver) {
			if(ver==0){
				obj = obj + 1
	            $('tr:eq('+obj+') td').addClass('highlightRow').parents('tr').siblings().find('td').removeClass('highlightRow')
				this.registro = dato
			}		
		}
	},
	created: function(){
		this.phuyu_datos();
	}
});