var phuyu_sistema = new Vue({
	el: "#phuyu_sistema",
	methods: {
		phuyu_escape_html: function(texto){
			return String(texto || "")
				.replace(/&/g, "&amp;")
				.replace(/</g, "&lt;")
				.replace(/>/g, "&gt;")
				.replace(/"/g, "&quot;")
				.replace(/'/g, "&#039;");
		},
		phuyu_loader_html: function(mensaje){
			var texto = this.phuyu_escape_html(mensaje || "Cargando experiencia...");
			var logo = url + "public/img/logo_completo.png";
			return ''+
				'<div class="phuyu-system-loader">'+
					'<div class="phuyu-system-loader-inner">'+
						'<div class="phuyu-system-spinner" aria-hidden="true">'+
							'<div class="phuyu-system-ring"></div>'+
							'<div class="phuyu-system-square phuyu-system-square-a"></div>'+
							'<div class="phuyu-system-square phuyu-system-square-b"></div>'+
							'<div class="phuyu-system-square phuyu-system-square-c"></div>'+
							'<div class="phuyu-system-square phuyu-system-square-d"></div>'+
						'</div>'+
						'<img src="'+logo+'" alt="Phuyu" class="phuyu-system-logo" />'+
						'<div class="phuyu-system-bar"></div>'+
						'<div class="phuyu-system-text">'+texto+'</div>'+
					'</div>'+
				'</div>';
		},
		phuyu_inicio: function(){
			var contenedor = $('#phuyu_sistema');
			contenedor.find('.phuyu-system-loader').remove();
			contenedor.addClass('phuyu-system-loading').append(this.phuyu_loader_html());
		},
		phuyu_inicio_guardar: function(mensaje){
			var contenedor = $("#phuyu_sistema");
			contenedor.find('.phuyu-system-loader').remove();
			contenedor.addClass('phuyu-system-loading').append(this.phuyu_loader_html(mensaje));
		},
		phuyu_fin: function(){
			$('#phuyu_sistema').removeClass('phuyu-system-loading').find('.phuyu-system-loader').remove();
		},
		phuyu_fin_flavio: function(){
			$("#phuyu_sistema").removeClass('phuyu-system-loading').find('.phuyu-system-loader').remove();
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
