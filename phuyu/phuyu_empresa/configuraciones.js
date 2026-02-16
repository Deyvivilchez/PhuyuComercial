var phuyu_datos = new Vue({
	el: "#phuyu_datos",
	data: {cargando: true, campos: campos},
	methods: {
		phuyu_consultar: function(){
			$(".btn-consultar").attr("disabled","true");
			this.$http.get(url+"web/phuyu_ruc/"+this.campos.documento).then(function(data){
				if(data.body.persona){
					this.campos.razonsocial = data.body.persona.razonSocial;
					this.campos.direccion = data.body.persona.direccion;
					this.campos.nombrecomercial = data.body.persona.razonSocial;
				}else{
					phuyu_sistema.phuyu_noti("NO SE ENCONTRARON DATOS","RUC NO EXISTE","error");
				}
				$(".btn-consultar").empty().html("<i class='fa fa-undo'></i> CONSULTAR SUNAT"); $(".btn-consultar").removeAttr("disabled");
			});
		},
		phuyu_provincias: function(){
			if (this.campos.departamento!=undefined) {
				this.$http.get(url+"ventas/clientes/provincias/"+this.campos.departamento).then(function(data){
					$("#provincia").empty().html(data.body); $("#codubigeo").empty().html('<option value="">SELECCIONE</option>');
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
		phuyu_guardar: function(){
			this.estado = 1; const formulario = new FormData($("#formulario")[0]);
			this.$http.post(url+phuyu_controller+"/guardar", formulario).then(function(data){
				if (data.body==1) {
					phuyu_sistema.phuyu_noti("CONFIGURACION REGISTRADA CORRECTAMENTE","DATOS GUARDADOS EN EL SISTEMA","success");
					setTimeout(function() {
						location.reload();
				    }, 1000);
				}else{
					phuyu_sistema.phuyu_alerta("ATENCION USUARIO","OCURRIO UN ERROR AL GUARDAR LA CONFIGURACION","error");
				}
			}, function(){
				phuyu_sistema.phuyu_alerta("ATENCION USUARIO","ERROR DE RED (INTERNET)","error");
			});
		},
		obtener_ubicacion: function(){
			var prov = this.campos.provinciacod;
			var dis = this.campos.codubigeocod;
			this.$http.get(url+"ventas/clientes/provincias/"+this.campos.departamento).then(function(data){
				$("#provincia").empty().html(data.body); 
				$("#codubigeo").empty().html('<option value="">SELECCIONE</option>');
				this.campos.provincia = '';
				this.campos.provincia = prov

				this.$http.get(url+"ventas/clientes/distritos/"+this.campos.departamento+"/"+this.campos.provincia).then(function(data){
					$("#codubigeo").empty().html(data.body); 
					console.log(dis)
					this.campos.codubigeo = '';
					this.campos.codubigeo = dis;
				});
			});
		}
	},
	created: function(){
		this.obtener_ubicacion()
		phuyu_sistema.phuyu_fin();
	}
});

