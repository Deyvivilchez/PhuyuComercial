function phuyu_login(){
	var $boton = $("#iniciar_sesion");
	var $mensaje = $("#mensaje");
	var $spinner = $("#spinner-login");
	var $label = $("#btn-label");

	$boton.attr("disabled", true);
	if ($spinner.length) {
		$spinner.show();
	}
	if ($label.length) {
		$label.text("Autenticando...");
	}else{
		$boton.html("<span>Verificando...</span>");
	}
	$mensaje.removeClass("alert-danger alert-warning alert-success").addClass("d-none").hide();

	$.ajax({
		url: url+"phuyu/phuyu_login/",
		type: "POST",
		dataType: "text",
		data: {
			usuario: $("#phuyu_usuario").val(),
			clave: $("#phuyu_clave").val()
		},
		success: function(data){
			data = $.trim(data);
			if (data == "1") {
				window.location.href = url;
				return;
			}
			mostrar_mensaje_login("Usuario o clave incorrectos.");
		},
		error: function(xhr){
			var detalle = xhr && xhr.status ? " Codigo: "+xhr.status : "";
			mostrar_mensaje_login("No se pudo conectar con el servidor."+detalle);
		},
		complete: function(){
			if ($spinner.length) {
				$spinner.hide();
			}
			if ($label.length) {
				$label.text("Iniciar sesion");
			}else{
				$boton.html("<span>Ingresar</span>");
			}
			$boton.attr("disabled", false);
		}
	});

	function mostrar_mensaje_login(texto){
		$mensaje
			.text(texto)
			.removeClass("d-none alert-warning alert-success")
			.addClass("alert-danger")
			.show();
	}

	return false;
}
