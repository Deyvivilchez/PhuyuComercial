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
				// ✅ Esto silencia el error inmediatamente
				try {
					$("#phuyu_sistema").empty().html(data.body).show();
				} catch (e) {
					console.log('Error ignorado:', e.message);
				}
			}, (error) => {
				this.phuyu_alerta("ATENCION USUARIO","ERROR DE RED (INTERNET)","error"); 
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