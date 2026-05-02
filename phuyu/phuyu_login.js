function phuyu_login(){
	$("#iniciar_sesion")
		.attr('disabled', true)
		.html('<span>Verificando...</span>');
	$("#mensaje").hide();

	$.post(url+"phuyu/phuyu_login/",{"usuario":$("#phuyu_usuario").val(),"clave":$("#phuyu_clave").val()},function(data){
		if (data==1) {
			window.location.href = url;
		}else{
			$("#iniciar_sesion")
				.attr("disabled",false)
				.html('<span>Ingresar</span>');
			$("#mensaje").show();
		}
	},"json").fail(function(){
		$("#iniciar_sesion")
			.attr("disabled",false)
			.html('<span>Ingresar</span>');
		$("#mensaje")
			.text("No se pudo conectar con el servidor.")
			.show();
	});
	return false;
}
