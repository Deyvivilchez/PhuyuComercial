var phuyu_form = new Vue({
	el: "#phuyu_form",
	data: {
		estado: 0,
		campos: {
			codperfil: 0, 
			modulos: permisos
		}
	},
	methods: {
		phuyu_marcar: function(){
			if ($("#marcar").is(":checked")) {
				var marcados = [];
				$('input[name^="lista"]').each(function() {
					marcados.push($(this).val());
				});
				this.campos.modulos = marcados;
		    }else{
		    	this.campos.modulos = [];
		    }
		},
		phuyu_guardar: function(){
			this.estado= 1;
			this.$http.post(url+phuyu_controller+"/guardar_permisos", this.campos).then(function(data){
				if (data.body==1) {
					phuyu_sistema.phuyu_alerta("PERMISOS GUARDADOS", "PERMISOS REGISTRADOS EN EL SISTEMA","success");
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
	}
});