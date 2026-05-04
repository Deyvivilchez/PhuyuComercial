var phuyu_sistema = new Vue({
	el: "#phuyu_sistema",
	methods: {
		phuyu_inicio: function(){
			//let parentCard = event.currentTarget.closest('.card');
	        $('#phuyu_sistema').addClass('overlay-spinner');
		},
		phuyu_inicio_guardar: function(mensaje){
			$("#phuyu_sistema").addClass('overlay-spinner');
		},
		phuyu_fin: function(){
			//let parentCard = event.currentTarget.closest('.card');
			$('#phuyu_sistema').removeClass('overlay-spinner');
		},
		phuyu_fin_flavio: function(){
			$("#phuyu_sistema").removeClass('overlay-spinner');
		},
		phuyu_loader: function(contenido, top){
			$("#"+contenido).addClass('overlay-spinner');
		},
		phuyu_finloader: function(contenido, top){
			$("#"+contenido).removeClass('overlay-spinner');
		},
		phuyu_alerta: function(titulo,mensaje,tipo){
			swal({title: titulo, text: mensaje, icon: tipo, closeOnClickOutside: false });
		},
		phuyu_noti: function(titulo,mensaje,tipo){
			if(tipo=="error"){
				tipo = 'danger'
			}
			jQuery.notify(
		        {title: titulo, message: mensaje},
		        {
		          type: tipo,
		          delay: 5000,
		        },
		    );
		},
		phuyu_modulo_original: function(){
			this.phuyu_inicio();
			this.$http.post(url+phuyu_controller).then(function(data){
				$("#phuyu_sistema").empty().html(data.body).show();
			},function(){
				this.phuyu_alerta("ATENCION USUARIO","ERROR DE RED (INTERNET)","error"); this.phuyu_fin();
			});
		},
		phuyu_modulo: function(){
			this.phuyu_inicio();
			this.$http.post(url+phuyu_controller).then((data) => {
				try {
					$("#phuyu_sistema").empty().html(data.body).show();
				} catch (e) {
					this.phuyu_alerta("ATENCION USUARIO", "No se pudo renderizar el modulo solicitado.", "error");
				} finally {
					this.phuyu_fin();
				}
			}, (error) => {
				var mensaje = error && error.status === 403
					? "No tienes permiso para acceder a este modulo."
					: "ERROR DE RED (INTERNET)";
				this.phuyu_alerta("ATENCION USUARIO", mensaje, "error"); 
				this.phuyu_fin();
			});
		},
		phuyu_error: function(){
			this.phuyu_alerta("ATENCION USUARIO","ERROR DE RED (INTERNET)","error"); this.phuyu_fin();
		},
		phuyu_error_operacion: function(){
			this.phuyu_alerta("ATENCION USUARIO","ERROR DE RED (INTERNET)","error"); this.phuyu_modulo();
		}
	},
	created: function(){
		this.phuyu_modulo();
	}
});
