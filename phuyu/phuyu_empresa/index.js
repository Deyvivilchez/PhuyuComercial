var phuyu_datos = new Vue({
	el: "#phuyu_datos",
	data: { cargando: true, registro:$("#codempresa").val() },
	methods: {
		phuyu_editar: function(){
			$("#phuyu_tituloform").text("CONFIGURAR FACTURACION - EMPRESA");
			$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario",180);
			this.$http.post(url+phuyu_controller+"/editar",{"codregistro":this.registro}).then(function(data){
				$("#phuyu_formulario").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			},function(){
				phuyu_sistema.phuyu_alerta("ATENCION USUARIO","ERROR DE RED (INTERNET)","error"); 
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			});
		},
		phuyu_copia: function(){
			phuyu_sistema.phuyu_alerta("ATENCION USUARIO","LA GENERACION DE COPIAS NO ESTÁ HABILITADO","error"); 
		}
	},
	created: function(){
		phuyu_sistema.phuyu_fin();
	}
});