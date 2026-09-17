var phuyu_form_1 = new Vue({
	el: "#phuyu_form_1",
	data: { estado_1: 0, agregar: {codigo:$("#codigo_extencion").val(),descripcion: "",sucursales:[]} },
	methods: {
		phuyu_guardar_1: function(tabla){
			if(tabla=="almacen/lineas"){
				var checks = 0;
				$('input[name^="checks"]:checked').each(function() {
		            checks = 1;
		        });
		        if(checks==0){
		        	phuyu_sistema.phuyu_noti("PARA REALIZAR LA OPERACION","DEBES SELECCIONAR AL MENOS UNA SUCURSAL","error");return false;
		        }
			}
			this.estado_1 = 1;
			this.$http.post(url+"almacen/extenciones/guardar/"+tabla, this.agregar).then(function(data){
				if (data.body==0) {
					phuyu_sistema.phuyu_alerta("ATENCION USUARIO","OCURRIO UN ERROR AL REGISTRAR","error");
					$("#modal_extencion").modal("hide");
				}else{
					phuyu_sistema.phuyu_noti("GUARDADO CORRECTAMENTE","UN NUEVO REGISTRO EN EL SISTEMA","success");
					phuyu_form.phuyu_extencion(tabla,data.body); $("#modal_extencion").modal("hide");
				}
			},function(){
				phuyu_sistema.phuyu_alerta("ATENCION USUARIO","ERROR DE RED (INTERNET)","error"); $("#modal_extencion").modal("hide");
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
		}
	}
});