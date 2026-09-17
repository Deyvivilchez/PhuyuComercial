var phuyu_datos = new Vue({
	el: "#phuyu_datos",
	data: {
		cargando: true, registro:0, buscar: "", datos: [], 
		editar:{"codinventario":0}, editardetalle:[],
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
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS", "ERROR DE RED","error"); phuyu_sistema.phuyu_fin();
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
			this.$http.post(url+phuyu_controller+"/nuevo").then(function(data){
				$("#phuyu_formulario").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); 
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			});
		},

		phuyu_inventario: function(codinventario){
			this.registro = codinventario; phuyu_sistema.phuyu_inicio();
			this.$http.post(url+phuyu_controller+"/inventario/"+this.registro).then(function(data){
				$("#phuyu_sistema").empty().html(data.body);
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); phuyu_sistema.phuyu_fin();
			});
		},
		phuyu_verinventario: function(codinventario){
			this.registro = codinventario; phuyu_sistema.phuyu_inicio();
			this.$http.post(url+phuyu_controller+"/verinventario/"+this.registro).then(function(data){
				$("#phuyu_sistema").empty().html(data.body);
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); phuyu_sistema.phuyu_fin();
			});
		},

		phuyu_masproductos : function(){
			phuyu_sistema.phuyu_inicio();
			this.$http.get(url+phuyu_controller+"/mas_productos_inventario/"+this.editar.codinventario).then(function(data){
				if (data.body=="") {
					phuyu_sistema.phuyu_noti("NO HAY PRODUCTO PARA AGREGAR","PRODUCTOS ACTUALIZADOS","error");
				}else{
					phuyu_sistema.phuyu_noti("PRODUCTOS CARGADOS CORRECTAMENTE","PRODUCTOS EN EL INVENTARIO","success");
				}
				phuyu_sistema.phuyu_fin();
			});
		},
		phuyu_editarinventario: function(codinventario){
			this.editar.codinventario = codinventario; this.editardetalle = [];
			$("#codproducto").empty().html("<option value=''>SELECCIONE PRODUCTO</option>");
			$(".selectpicker").selectpicker("refresh"); $(".filter-option").text("SELECCIONE PRODUCTO"); 
			$("#codproducto").val(""); $("#editar_inventario").modal("show");
		},
		phuyu_unidades: function(){
			this.$http.get(url+phuyu_controller+"/productos_unidades/"+this.editar.codinventario+"/"+$("#codproducto").val()).then(function(data){
				this.editardetalle = data.body;
			});
		},
		phuyu_abrirmodal: function(){
            $("#modal_subir").modal({backdrop: 'static', keyboard: false});
		},
		phuyu_guardar_archivo: function(){
            const formulario = new FormData($("#formulario")[0]);
            this.$http.post(url+phuyu_controller+"/guardar_archivo", formulario).then(function(info){
				if (info.body==0) {
					phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR AL SUBIR EL ARCHIVO CSV", "PRODUCTO REGISTRADO","error");
				}else{
	                phuyu_sistema.phuyu_alerta("EDITADO CORRECTAMENTE", "UN REGISTRO PRODUCTO EN EL SISTEMA","success");
				}
				this.phuyu_datos(); $("#modal_subir").modal("hide");
            }, function(){
				phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR AL SUBIR EL ARCHIVO CSV", "ERROR DE RED","error");
			});
		},
		phuyu_guardar_editar: function(){
			if ($("#codproducto").val()=="") {
				$("#codproducto").focus(); return false;
			}
			phuyu_sistema.phuyu_inicio_guardar("EDITANDO INVENTARIO . . .");
			this.$http.post(url+phuyu_controller+"/guardar_editar_inventario",{"codregistro":this.editar.codinventario,detalle:this.editardetalle}).then(function(data){
				if (data.body==1) {
					phuyu_sistema.phuyu_alerta("INVENTARIO EDITADO CORRECTAMENTE", "EL INVENTARIO EDITADO EN EL SISTEMA","success");
				}else{
					if (data.body==0) {
						phuyu_sistema.phuyu_alerta("ERROR AL EDITAR INVENTARIO", "NO SE PUEDE EDITAR","error");
					}else{
						phuyu_sistema.phuyu_alerta("NO PUEDE ACTUALIZAR EL INVENTARIO INICIAL", data.body,"error");
					}
				}
				this.phuyu_datos(); $("#editar_inventario").modal("hide");
			}, function(){
				phuyu_sistema.phuyu_alerta("ERROR AL EDITAR INVENTARIO", "NO SE PUEDE EDITAR","error");
				this.phuyu_datos();
			});
		},

		phuyu_cerrarinventario: function(codinventario){
			swal({
				title: "SEGURO DESEA CERRAR INVENTARIO ?",   
				text: "NRO DE INVENTARIO A CERRAR ES EL 000"+codinventario, 
				icon: "warning",
				dangerMode: true,
				buttons: ["CANCELAR", "SI, CERRAR INVENTARIO 000"+codinventario],
			}).then((willDelete) => {
				if (willDelete) {
					phuyu_sistema.phuyu_inicio_guardar("GUARDANDO CIERRE DE INVENTARIO");
					this.$http.post(url+phuyu_controller+"/cerrar_inventario",{"codregistro":codinventario}).then(function(data){
						if (data.body==1) {
							phuyu_sistema.phuyu_alerta("INVENTARIO CERRADO CORRECTAMENTE", "EL INVENTARIO DE CERRÓ EN EL SISTEMA","success");
						}else{
							phuyu_sistema.phuyu_alerta("ERROR AL CERRAR INVENTARIO", "REVISAR CONFIGURACIONES DE LOS CMPROBANTES DE ALMACEN","error");
						}
						this.phuyu_datos();
					}, function(){
						phuyu_sistema.phuyu_alerta("ERROR AL CERRAR INVENTARIO", "REVISAR CONFIGURACIONES DE LOS CMPROBANTES DE ALMACEN","error");
						this.phuyu_datos();
					});
				}else {
					phuyu_sistema.phuyu_alerta("CIERRE DE INVENTARIO CANCELADO", "PROCESO DE INVENTARIO NO TERMINADO","error");
				}
			});
		},
		phuyu_reabririnventario: function(codinventario){
			swal({
				title: "SEGURO DESEA REABRIR EL INVENTARIO ?",   
				text: "NRO DE INVENTARIO A REABRIR ES EL 000"+codinventario, 
				icon: "warning",
				dangerMode: true,
				buttons: ["CANCELAR", "SI, REABRIR INVENTARIO 000"+codinventario],
			}).then((willDelete) => {
				if (willDelete) {
					this.registro = codinventario;
					phuyu_sistema.phuyu_inicio_guardar("REABRIENDO INVENTARIO");
					this.$http.post(url+phuyu_controller+"/reabrir_inventario/"+codinventario).then(function(data){
						$("#phuyu_sistema").empty().html(data.body);
					}, function(){
						phuyu_sistema.phuyu_alerta("ERROR AL REABRIR INVENTARIO", "CONTACTESE CON SOPORTE","error");
						this.phuyu_datos();
					});
				}
			});
		}
	},
	created: function(){
		this.phuyu_datos();
	}
});