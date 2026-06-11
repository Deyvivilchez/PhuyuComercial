function phuyu_login(){
    console.log("Función phuyu_login ejecutándose...");
    
    // Deshabilitar botón
    $("#iniciar_sesion").prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> <span>VERIFICANDO...</span>');
    
    // Ocultar y resetear mensaje
    $("#mensaje").hide().removeClass('alert-success alert-danger').html('');
    
    // Obtener valores
    var usuario = $("#phuyu_usuario").val();
    var clave = $("#phuyu_clave").val();
    
    console.log("Enviando datos:", {usuario: usuario, clave: clave});
    
    $.ajax({
        url: url + "phuyu/phuyu_login/",
        type: "POST",
        data: {
            "usuario": usuario,
            "clave": clave
        },
        dataType: "json",
        success: function(data) {
            console.log("Respuesta recibida:", data);
            console.log("Tipo de dato:", typeof data);
            
            // Convertir a número si es string
            var respuesta = typeof data === 'string' ? parseInt(data) : data;
            console.log("Respuesta convertida:", respuesta);
            
            if (respuesta == 1 || respuesta === "1") {
                // ÉXITO
                console.log("Login exitoso, redirigiendo...");
                $("#mensaje").addClass('alert-success').html('<i class="fas fa-check-circle"></i> ¡Login exitoso! Redirigiendo...').show();
                
                setTimeout(function(){
                    window.location.href = url;
                }, 1000);
                
            } else {
                // ERROR - Reactivar botón
                console.log("Login fallido");
                $("#iniciar_sesion").prop('disabled', false).html('<i class="fas fa-arrow-right"></i> <span>INGRESAR</span>');
                
                // Mostrar mensaje de error
                $("#mensaje").addClass('alert-danger').html('<i class="fas fa-exclamation-triangle"></i> Usuario o contraseña incorrectos').show();
                
                // Efecto de error
                $(".login-card").css('animation', 'shake 0.5s ease-in-out');
                setTimeout(function(){
                    $(".login-card").css('animation', '');
                }, 500);
                
                // Enfocar campo de usuario
                $("#phuyu_usuario").focus().select();
            }
        },
        error: function(xhr, status, error) {
            console.error("Error en la petición:", error);
            console.log("Status:", status);
            console.log("Respuesta completa:", xhr.responseText);
            
            $("#iniciar_sesion").prop('disabled', false).html('<i class="fas fa-arrow-right"></i> <span>INGRESAR</span>');
            
            $("#mensaje").addClass('alert-danger').html('<i class="fas fa-exclamation-circle"></i> Error de conexión: ' + error).show();
        }
    });
    
    return false;
}