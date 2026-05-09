var phuyu_datos = new Vue({
	el: "#phuyu_datos",
	data: {cargando: true, campos: campos},
	methods: {
		phuyu_consultar: function(){
			$(".btn-consultar").attr("disabled", "true");
			this.$http.get(url+"web/phuyu_ruc/"+this.campos.documento).then(function(data){
				if(data.body.persona){
					this.campos.razonsocial = data.body.persona.razonSocial;
					this.campos.direccion = data.body.persona.direccion;
					this.campos.nombrecomercial = data.body.persona.razonSocial;
				}else{
					phuyu_sistema.phuyu_noti("NO SE ENCONTRARON DATOS","RUC NO EXISTE","error");
				}
				$(".btn-consultar").empty().html("<i class='fa fa-undo'></i> CONSULTAR SUNAT").removeAttr("disabled");
			});
		},
		phuyu_provincias: function(){
			if (this.campos.departamento!=undefined) {
				this.$http.get(url+"ventas/clientes/provincias/"+this.campos.departamento).then(function(data){
					$("#provincia").empty().html(data.body);
					$("#codubigeo").empty().html('<option value="">SELECCIONE</option>');
				});
			}
		},
		phuyu_distritos: function(){
			if (this.campos.provincia!=undefined) {
				this.$http.get(url+"ventas/clientes/distritos/"+this.campos.departamento+"/"+this.campos.provincia).then(function(data){
					$("#codubigeo").empty().html(data.body);
				});
			}
		},
		phuyu_itemrepetir: function(){
			if (this.campos.itemrepetircomprobante==1) {
				this.campos.itemrepetircomprobante = 0;
			}else{
				this.campos.itemrepetircomprobante = 1;
			}
		},
		phuyu_cerrar_inactividad: function(){
			this.campos.cerrar_inactividad = this.campos.cerrar_inactividad==1 ? 0 : 1;
		},
		phuyu_mostrar_aviso: function(){
			this.campos.mostrar_aviso = this.campos.mostrar_aviso==1 ? 0 : 1;
		},
		phuyu_guardar: function(){
			this.campos.tiempo_inactividad_minutos = parseInt(this.campos.tiempo_inactividad_minutos || 120, 10);
			this.campos.minutos_aviso = parseInt(this.campos.minutos_aviso || 5, 10);

			if (isNaN(this.campos.tiempo_inactividad_minutos)) {
				this.campos.tiempo_inactividad_minutos = 120;
			}
			if (isNaN(this.campos.minutos_aviso)) {
				this.campos.minutos_aviso = 5;
			}
			if (this.campos.tiempo_inactividad_minutos < 1) {
				this.campos.tiempo_inactividad_minutos = 1;
			}
			if (this.campos.minutos_aviso < 1) {
				this.campos.minutos_aviso = 1;
			}
			if (this.campos.minutos_aviso > this.campos.tiempo_inactividad_minutos) {
				this.campos.minutos_aviso = this.campos.tiempo_inactividad_minutos;
			}

			this.estado = 1;
			var formulario = new FormData(document.getElementById("formulario"));
			formulario.set("sesion_alcance", this.campos.sesion_alcance);
			formulario.set("cerrar_inactividad", this.campos.cerrar_inactividad);
			formulario.set("tiempo_inactividad_minutos", this.campos.tiempo_inactividad_minutos);
			formulario.set("mostrar_aviso", this.campos.mostrar_aviso);
			formulario.set("minutos_aviso", this.campos.minutos_aviso);

			$.ajax({
				url: url+phuyu_controller+"/guardar",
				type: "POST",
				data: formulario,
				processData: false,
				contentType: false,
				cache: false,
				success: function(respuesta){
					var data = respuesta;
					try {
						data = JSON.parse(respuesta);
					} catch(e) {}

					if (respuesta == 1 || data == 1) {
						phuyu_sistema.phuyu_noti("CONFIGURACION REGISTRADA CORRECTAMENTE","DATOS GUARDADOS EN EL SISTEMA","success");
						setTimeout(function() {
							location.reload();
						}, 1000);
					}else{
						phuyu_sistema.phuyu_alerta("ATENCION USUARIO","OCURRIO UN ERROR AL GUARDAR LA CONFIGURACION","error");
					}
				},
				error: function(){
					phuyu_sistema.phuyu_alerta("ATENCION USUARIO","ERROR DE RED O SERVIDOR","error");
				}
			});
		},
		obtener_ubicacion: function(){
			var prov = this.campos.provinciacod;
			var dis = this.campos.codubigeocod;

			if (!this.campos.departamento) {
				return;
			}

			this.$http.get(url+"ventas/clientes/provincias/"+this.campos.departamento).then(function(data){
				$("#provincia").empty().html(data.body);
				$("#codubigeo").empty().html('<option value="">SELECCIONE</option>');
				this.campos.provincia = prov;

				if (!this.campos.provincia) {
					return;
				}

				this.$http.get(url+"ventas/clientes/distritos/"+this.campos.departamento+"/"+this.campos.provincia).then(function(data){
					$("#codubigeo").empty().html(data.body);
					this.campos.codubigeo = dis;
				});
			});
		}
	},
	created: function(){
		this.obtener_ubicacion();
		phuyu_sistema.phuyu_fin();
	}
});
