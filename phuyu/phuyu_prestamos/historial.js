var phuyu_historial = new Vue({
	el: "#phuyu_historial",
	data: {
		estado:0, prestamos: [], totales: [], pagos_cobros: [], referencia:"",
		campos:{"codkardex":0,"fechadesde":"","fechahasta":"","estado":1,"filtro":1,"tipo":$("#tipo").val()},
	},
	methods: {
		phuyu_fechas: function(){
			this.campos.codkardex = phuyu_datos.registro;
			this.campos.fechadesde = $("#fechadesde").val();
			this.campos.fechahasta = $("#fechahasta").val();
		},
		phuyu_prestamos: function(){
			this.phuyu_fechas(); phuyu_sistema.phuyu_inicio();
			this.$http.post(url+phuyu_controller+"/filtro_prestamos",this.campos).then(function(data){
				this.prestamos = data.body.prestamos; phuyu_sistema.phuyu_fin();
			}, function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
				phuyu_sistema.phuyu_fin();
			});
		},
		phuyu_ver: function(codkardex){
			$("#modal_ver").modal('show');
			phuyu_sistema.phuyu_loader('modalver');
			this.$http.post(url+phuyu_controller+"/ver_devolucion",{"codregistro":codkardex}).then(function(data){
				$("#modalver").empty().html(data.body);
				phuyu_sistema.phuyu_finloader('modalver');
			}, function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
				phuyu_sistema.phuyu_finloader('modalver');
			});
		},
		phuyu_editar: function(codkardex){
			this.$http.post(url+phuyu_controller+"/editar",{"codregistro":codkardex}).then(function(data){
				$("#cuerpo").empty().html(data.body);
				$("#modal_editar").modal('show')
			}, function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
				phuyu_sistema.phuyu_fin();
			});
		},
		phuyu_eliminar: function(codkardex,codkardex_ref){
			swal({
				title: "SEGURO ELIMINAR DEVOLUCION DE PRESTAMO ?",   
				text: "USTED ESTA POR ELIMINAR UNA DEVOLUCION DE PRESTAMO", 
				icon: "warning",
				dangerMode: true,
				content: {
				    element: "input",
				    attributes: {
				      	placeholder: "PORQUE DESEAS ELIMINAR LA DEVOLUCION",
				      	type: "text",
				    },
				},
				buttons: ["CANCELAR", "SI, ELIMINAR"],
			}).then((willDelete) => {
				if (willDelete) {
					phuyu_sistema.phuyu_inicio_guardar("ANULANDO DEVOLUCION . . .");
					this.$http.post(url+phuyu_controller+"/eliminar",{"codregistro":codkardex,"codkardex_ref":codkardex_ref,"observaciones":$(".swal-content__input").val()}).then(function(data){
						if (data.body==1) {
							phuyu_sistema.phuyu_alerta("ELIMINADO CORRECTAMENTE","UNA DEVOLUCION DE PRESTAMO ELIMINADO EN EL SISTEMA","success");
						}else{
							phuyu_sistema.phuyu_alerta("NO PUEDE ELIMINAR LA DEVOLUCION DE PRESTMO","COMUNIQUESE CON SOPORTE","error");
						}
						phuyu_sistema.phuyu_fin(); this.phuyu_prestamos();
					}, function(){
						phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
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
		phuyu_cerrar: function(){
			phuyu_sistema.phuyu_modulo();
		}
	},
	created: function(){
		phuyu_sistema.phuyu_fin(); this.phuyu_prestamos();
	}
});