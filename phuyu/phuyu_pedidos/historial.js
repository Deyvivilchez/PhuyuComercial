var phuyu_historial = new Vue({
	el: "#phuyu_historial",
	data: {
		estado:0, pedidos: [], totales: [],
		campos:{"codpersona":0,"fechadesde":"","fechahasta":"","estado":1,"filtro":1},
	},
	methods: {
		phuyu_fechas: function(){
			this.campos.codpersona = phuyu_pedidos.registro;
			this.campos.fechadesde = $("#fechadesde").val();
			this.campos.fechahasta = $("#fechahasta").val();
		},
		phuyu_pedidos: function(){
			this.phuyu_fechas(); phuyu_sistema.phuyu_inicio();
			this.$http.post(url+phuyu_controller+"/filtro_pedidos",this.campos).then(function(data){
				this.pedidos = data.body.pedidos; this.totales = data.body.totales; phuyu_sistema.phuyu_fin();
			}, function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
				phuyu_sistema.phuyu_fin();
			});
		},
		phuyu_atender: function(codpedido){
			$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario",180);
			this.$http.post(url+phuyu_controller+"/atender/"+codpedido).then(function(data){
				$("#phuyu_formulario").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); 
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			});
		},
		phuyu_ver: function(codpedido){
			$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario",180);
			this.$http.post(url+phuyu_controller+"/ver/"+codpedido).then(function(data){
				$("#phuyu_formulario").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); 
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			});
		},
		phuyu_eliminar: function(codpedido){
			swal({
				title: "SEGURO ELIMINAR PEDIDO ?",   
				text: "USTED ESTA POR ELIMINAR UN PEDIDO DEL SISTEMA", 
				icon: "warning",
				dangerMode: true,
				buttons: ["CANCELAR", "SI, ELIMINAR"],
			}).then((willDelete) => {
				if (willDelete) {
					phuyu_sistema.phuyu_inicio_guardar("ANULANDO PEDIDO . . .");
					this.$http.post(url+phuyu_controller+"/eliminar",{"codregistro":codpedido}).then(function(data){
						if (data.body.estado==1) {
							phuyu_sistema.phuyu_alerta("ELIMINADO CORRECTAMENTE","UN PEDIDO ELIMINADO EN EL SISTEMA","success");
						}else{
							phuyu_sistema.phuyu_alerta("NO PUEDE ELIMINAR EL PEDIDO","TIENE ENTREGAS REALIZADAS","error");
						}
						phuyu_sistema.phuyu_fin(); this.phuyu_pedidos();
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
		phuyu_sistema.phuyu_fin(); this.phuyu_pedidos();
	}
});