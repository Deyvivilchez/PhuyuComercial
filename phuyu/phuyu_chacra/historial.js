var phuyu_historial = new Vue({
	el: "#phuyu_historial",
	data: {
		estado:0, creditos: [], totales: [], pagos_cobros: [], referencia:"",
		campos:{"codpersona":0,"codlote":0,"fechadesde":"","fechahasta":"","tipo":1,"filtro":1},
	},
	methods: {
		phuyu_fechas: function(){
			this.campos.codlote = phuyu_lineas.registro;
			this.campos.fechadesde = $("#fechadesde").val();
			this.campos.fechahasta = $("#fechahasta").val();
		},
		phuyu_creditos: function(){
			this.phuyu_fechas(); phuyu_sistema.phuyu_inicio();
			this.$http.post(url+"agricola/chacra/filtro_creditos",this.campos).then(function(data){
				this.creditos = data.body.creditos; this.totales = data.body.totales; phuyu_sistema.phuyu_fin();
			}, function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
				phuyu_sistema.phuyu_fin();
			});
		},
		phuyu_pagos_cobros: function(){
			this.campos.fechadesde = $("#fechadesde_c").val(); this.campos.fechahasta = $("#fechahasta_c").val();
			phuyu_sistema.phuyu_inicio();
			this.$http.post(url+"creditos/cuentascobrar/filtro_pagos_cobros",this.campos).then(function(data){
				this.pagos_cobros = data.body; phuyu_sistema.phuyu_fin();
			}, function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
				phuyu_sistema.phuyu_fin();
			});
		},
		phuyu_editar: function(codcredito){
			this.$http.post(url+phuyu_controller+"/verificar_edicion",{"codregistro":codcredito}).then(function(data){
				if (data.body!=0) {
					this.$http.post(url+phuyu_controller+"/editar",{"codregistro":codcredito}).then(function(data){
						$("#cuerpo").empty().html(data.body);
						$("#modal_editar").modal('show')
					}, function(){
						phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
						phuyu_sistema.phuyu_fin();
					});
				}else{
					phuyu_sistema.phuyu_alerta("NO PUEDE EDITAR EL CREDITO","TIENE PAGOS REGISTRADOS LO SENTIMOS","error");
				}
			}, function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
				phuyu_sistema.phuyu_fin();
			});
		},
		phuyu_eliminar: function(codcredito){
			swal({
				title: "SEGURO ELIMINAR CREDITO ?",   
				text: "USTED ESTA POR ELIMINAR UN CREDITO DEL SISTEMA", 
				icon: "warning",
				dangerMode: true,
				content: {
				    element: "input",
				    attributes: {
				      	placeholder: "PORQUE DESEAS ELIMINAR EL CREDITO",
				      	type: "text",
				    },
				},
				buttons: ["CANCELAR", "SI, ELIMINAR"],
			}).then((willDelete) => {
				if (willDelete) {
					phuyu_sistema.phuyu_inicio_guardar("ANULANDO CREDITO . . .");
					this.$http.post(url+phuyu_controller+"/eliminar",{"codregistro":codcredito,"observaciones":$(".swal-content__input").val()}).then(function(data){
						if (data.body==1) {
							phuyu_sistema.phuyu_alerta("ELIMINADO CORRECTAMENTE","UN CREDITO ELIMINADO EN EL SISTEMA","success");
						}else{
							phuyu_sistema.phuyu_alerta("NO PUEDE ELIMINAR EL CREDITO","TIENE PAGOS REGISTRADOS LO SENTIMOS","error");
						}
						phuyu_sistema.phuyu_fin(); this.phuyu_creditos();
					}, function(){
						phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
						phuyu_sistema.phuyu_fin();
					});
				}
			});
		},
		phuyu_anular_pagocobro: function(codmovimiento,tipo){
			if (tipo=="COBRO") {
				urlanular = "anularcobro";
			}else{
				urlanular = "anularpago";
			}
			swal({
				title: "SEGURO ANULAR "+tipo+" ?",   
				text: "USTED ESTA POR ANULAR UN "+tipo+" DEL SISTEMA", 
				icon: "warning",
				dangerMode: true,
				buttons: ["CANCELAR", "SI, ANULAR"],
			}).then((willDelete) => {
				if (willDelete) {
					phuyu_sistema.phuyu_inicio_guardar("ANULANDO "+tipo+" DEL CREDITO . . .");
					this.$http.post(url+phuyu_controller+"/"+urlanular,{"codmovimiento":codmovimiento}).then(function(data){
						if (data.body==1) {
							phuyu_sistema.phuyu_alerta(tipo+" ELIMINADO CORRECTAMENTE","UN "+tipo+" ELIMINADO EN EL SISTEMA","success");
						}else{
							phuyu_sistema.phuyu_alerta("NO PUEDE ANULAR EL "+tipo,"ERROR DE CONEXION INTERNET","error");
						}
						phuyu_sistema.phuyu_fin(); this.phuyu_pagos_cobros();
					}, function(){
						phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE CONEXION INTERNET","error");
						phuyu_sistema.phuyu_fin();
					});
				}
			});
		},
		phuyu_imprimir_recibo: function(codmovimiento,tipo){
			swal("IMPRIMIR RECIBO DE PAGO ?", {
				buttons: {
					cancel: "CANCELAR",
					catch: {
						text: "IMPRIMIR A5",
						value: "a5",
					},
					defeat: {
						text: "TICKET",
						value: "ticket",
					},
				},
			}).then((value) => {
				switch (value) {
					case "ticket":
						this.$http.get(url+"creditos/historial/imprimir_recibo/ticket/"+codmovimiento+"/"+tipo).then(function(data){
							$("#imprimir_recibo").empty().html(data.body);
							var id = "imprimir_recibo";
							var data = document.getElementById(id).innerHTML;
					        var modal = window.open('', 'IMPRIMIENDO', 'height=400,width=800');
					        modal.document.write('<html><head> <meta charset="utf-8"><title>RECIBO CREDITO</title>');
					        modal.document.write('</head><body >'+data+'</body></html>');
					        modal.document.close();

					        modal.focus(); modal.print(); modal.close();
						}); break;
					case "a5":
						window.open(url+"creditos/historial/imprimir_recibo/a5/"+codmovimiento+"/"+tipo,"_target"); break;
					default:
						console.log("CANCELAR - IMPRESION");
				}
			});
		},
		phuyu_editarfecha_pagocobro: function(codmovimiento,tipo,fecha){
			var urledit = '';
			if(tipo=='COBRO'){
				urledit = 'editarfechacobro';
			}else{
				urledit = 'editarfechapago';
			}
			swal("EDITAR FECHA DE MOVIMIENTO", {
				buttons: {
					cancel: "CANCELAR",
					defeat: {
						text: "GUARDAR CAMBIO",
						value: "guardar",
					},
				},
				content: {
				    element: "input",
				    attributes: {
				      	type: "date",
				      	value: fecha,
				    },
				},
			}).then((value) => {
				switch (value) {
					case "guardar":
						if($(".swal-content__input").val()!=""){
							this.$http.post(url+"creditos/historial/"+urledit,{"codmovimiento":codmovimiento,"tipo":tipo,"fechanueva":$(".swal-content__input").val()}).then(function(data){
								if (data.body==1) {
									phuyu_sistema.phuyu_alerta("FECHA MODIFICADA CORRECTAMENTE", "UN REGISTRO EDITADO EN EL SISTEMA","success");
								}else{
									phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR !!!", "SE PERDIÓ LA CONEXION !!! LO SENTIMOS","error");
								}
								this.phuyu_pagos_cobros();
							}, function(){
								phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
							});
						}else{
							phuyu_sistema.phuyu_alerta("INGRESE UNA FECHA VÁLIDA", "ERROR DE RED","error");
						}
					console.log($(".swal-content__input").val())
						break;
					default:
						console.log("CANCELAR - IMPRESION");
				}
			});
		},

		phuyu_cerrar: function(){
			phuyu_sistema.phuyu_modulo();
		}
	},
	created: function(){
		phuyu_sistema.phuyu_fin(); this.phuyu_creditos();
	}
});