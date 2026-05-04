var phuyu_movimiento = new Vue({
	el: "#phuyu_movimiento",
	data: {
		estado: 0,
		campos: campos,
		movimientobanco: 0,
		mostrarServicio: false,
		registrandoServicio: 0,
		nuevoServicio: {descripcion: ""}
	},
	methods: {
		phuyu_cajabanco: function(){
			if (this.campos.codtipopago==1) {
				this.movimientobanco = 0; $("#nrodocbanco").removeAttr("required");
			}else{
				this.movimientobanco = 1; $("#nrodocbanco").attr("required","true");
			}
		},
		phuyu_guardarservicio: function(){
			var descripcion = $.trim(this.nuevoServicio.descripcion || "");
			if (descripcion=="") {
				phuyu_sistema.phuyu_alerta("INGRESE SERVICIO", "Escriba la descripción del servicio para agregarlo.", "warning");
				return;
			}

			this.registrandoServicio = 1;
			this.$http.post(url+"compras/compras/guardar_servicio_gasto", {descripcion: descripcion}).then(function(data){
				var respuesta = data.body;
				if (typeof respuesta === "string") {
					try {
						respuesta = JSON.parse(respuesta);
					} catch (e) {
						respuesta = {estado: 0, mensaje: "No se pudo registrar el servicio."};
					}
				}

				if (respuesta.estado==1) {
					var codproducto = String(respuesta.codproducto);
					var descripcionServicio = respuesta.descripcion || descripcion;
					var $servicio = $("select[name='codproducto']");

					if ($servicio.find("option[value='" + codproducto + "']").length==0) {
						$servicio.append($("<option>", {value: codproducto, text: descripcionServicio}));
					}

					$servicio.prop("disabled", false);
					$servicio.find("option[value='']").text("SELECCIONE");
					this.campos.codproducto = codproducto;
					$servicio.val(codproducto).trigger("change");
					$("#phuyu-servicios-alerta").slideUp(150);

					this.nuevoServicio.descripcion = "";
					this.mostrarServicio = false;
					phuyu_sistema.phuyu_alerta("SERVICIO AGREGADO", "Ahora puede continuar registrando el egreso.", "success");
				}else{
					phuyu_sistema.phuyu_alerta("NO SE PUDO REGISTRAR", respuesta.mensaje || "Revise la configuración del producto.", "error");
				}

				this.registrandoServicio = 0;
			}, function(){
				phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR", "NO SE PUEDE REGISTRAR EL SERVICIO","error");
				this.registrandoServicio = 0;
			});
		},
		phuyu_guardar: function(){
			if (this.campos.codkardex != 0 && !this.campos.codproducto) {
				phuyu_sistema.phuyu_alerta("SELECCIONE SERVICIO", "Debe seleccionar el servicio para registrar el egreso de compra.", "warning");
				return;
			}

			this.campos.codpersona = $("#codpersona").val() || this.campos.codpersona;
			if (!this.campos.codpersona) {
				phuyu_sistema.phuyu_alerta("SELECCIONE SOCIO", "Debe seleccionar el proveedor o socio del movimiento.", "warning");
				return;
			}

			if (phuyu_controller=="compras/compras") {
				var url_movimiento = "compras/compras/guardar_gasto";
			}else{
				var url_movimiento = "caja/movimientos/guardar";
			}
			this.campos.fechadocbanco = $("#fechadocbanco").val(); this.estado= 1; 
			this.$http.post(url+url_movimiento, this.campos).then(function(data){
				if (data.body==1) {
					if (this.campos.codregistro=="") {
						phuyu_sistema.phuyu_alerta("GUARDADO CORRECTAMENTE", "UN NUEVO REGISTRO EN EL SISTEMA","success");
					}else{
						phuyu_sistema.phuyu_alerta("EDITADO CORRECTAMENTE", "UN REGISTRO EDITADO EN EL SISTEMA","info");
					}

					if (phuyu_controller=="compras/compras") {
						phuyu_compras.phuyu_datos();
					}else{
						// phuyu_datos.phuyu_datos();
					}

					this.phuyu_cerrar();
				}else{
					var mensaje = "NO SE PUEDE REGISTRAR";

					if (data.body==3) {
						mensaje = "Debe seleccionar un servicio para registrar el egreso.";
					}

					if (data.body==4) {
						mensaje = "El servicio seleccionado no tiene unidad de medida activa.";
					}

					if (data.body==5) {
						mensaje = "No hay serie activa para el comprobante de almacén.";
					}

					if (data.body=="e") {
						mensaje = "La sesión expiró. Vuelva a ingresar al sistema.";
					}

					phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR AL REGISTRAR", mensaje,"error");
					this.estado= 0;
				}
			}, function(){
				phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR AL REGISTRAR", "NO SE PUEDE GUARDAR EL MOVIMIENTO DE CAJA","error");
				this.estado= 0;
			});
		},
		phuyu_cerrar: function(){
			$(".compose").slideToggle();
		}
	}
});
