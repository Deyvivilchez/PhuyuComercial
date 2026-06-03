var phuyu_form = new Vue({
	el: "#phuyu_form",
	data: {
		estado: 0,
		campos: {
			codusuario: 0,
		},
		zonas: []
	},
	methods: {
		phuyu_zonas: function(){
			this.$http.post(url+phuyu_controller+"/zonas", {"codregistro":phuyu_datos.registro}).then(function(data){
				this.zonas = data.body;
			});
		},
		phuyu_guardar: function(){
			this.estado= 1;
			this.$http.post(url+phuyu_controller+"/guardar_asignacion", {"campos":this.campos, "zonas": this.zonas}).then(function(data){
				if (data.body==1) {
					phuyu_sistema.phuyu_alerta("ZONAS ASIGNADAS", "ZONAS ASIGNADAS AL USUARIO","success");
				}else{
					phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR AL REGISTRAR", "NO SE PUEDE REGISTRAR","error");
				}
				this.phuyu_cerrar();
			}, function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS", "ERROR DE RED","error");
			});
		},
		phuyu_cerrar: function(){
			$(".compose").slideToggle();
		}
	},
	created: function(){
		this.phuyu_zonas();
	}
});