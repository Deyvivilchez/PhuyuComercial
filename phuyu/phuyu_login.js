
function phuyu_login(){
	$("#iniciar_sesion").attr('disabled',true);$("#mensaje").css('display','none');
	
	$.post(url+"phuyu/phuyu_login/",{"usuario":$("#phuyu_usuario").val(),"clave":$("#phuyu_clave").val()},function(data){
		if (data==1) {
			window.location.href = url;
		}else{
			$("#iniciar_sesion").attr("disabled",false);
			$("#mensaje").css('display','block');
		}
	},"json");
	return false;
}