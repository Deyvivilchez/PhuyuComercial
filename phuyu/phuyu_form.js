var phuyu_form = new Vue({
	el: "#phuyu_form",
	data: {
		estado: 0, campos: campos
	},
	methods: {
		phuyu_guardar: function(){
			if(phuyu_controller=="almacen/lineas"){
				var checks = 0;
				$('input[name^="checks"]:checked').each(function() {
		            checks = 1;
		        });
		        if(checks==0){
		        	phuyu_sistema.phuyu_noti("PARA REALIZAR LA OPERACION","DEBES SELECCIONAR AL MENOS UNA SUCURSAL","error");return false;
		        }
			}
			this.estado= 1;
			this.$http.post(url+phuyu_controller+"/guardar", this.campos).then(function(data){
				if (data.body==1) {
					if (this.campos.codregistro=="") {
						phuyu_sistema.phuyu_alerta("GUARDADO CORRECTAMENTE", "UN NUEVO REGISTRO EN EL SISTEMA","success");
					}else{
						phuyu_sistema.phuyu_alerta("EDITADO CORRECTAMENTE", "UN REGISTRO EDITADO EN EL SISTEMA","info");
					}
				}else{
					phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR AL REGISTRAR", "NO SE PUEDE REGISTRAR","error");
				}
				phuyu_datos.phuyu_opcion(); this.phuyu_cerrar();
			}, function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS", "ERROR DE RED","error");
			});
		},
		phuyu_activarrubro: function(){
			if (this.campos.activo==1) {
				this.campos.activo = 0;
			}else{
				this.campos.activo = 1;
			}
		},
		phuyu_ventapedido: function(){
			if (this.campos.ventaconpedido==1) {
				this.campos.ventaconpedido = 0;
			}else{
				this.campos.ventaconpedido = 1;
			}
		},
		phuyu_ventaproforma: function(){
			if (this.campos.ventaconproforma==1) {
				this.campos.ventaconproforma = 0;
			}else{
				this.campos.ventaconproforma = 1;
			}
		},
		phuyu_proceso: function(codigo){
			if(codigo==1){
				if(this.campos.venta==1){
					this.campos.venta = 0;
				}else{
					this.campos.venta = 1;
				}
			}
			else if(codigo==2){
				if(this.campos.compra==1){
					this.campos.compra = 0;
				}else{
					this.campos.compra = 1;
				}
			}else if(codigo==3){
				if(this.campos.ingreso==1){
					this.campos.ingreso = 0;
				}else{
					this.campos.ingreso = 1;
				}
			}else{
				if(this.campos.egreso==1){
					this.campos.egreso = 0;
				}else{
					this.campos.egreso = 1;
				}
			}
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
		phuyu_editaralmacen: function(){
			this.$http.post(url+"administracion/almacenes/ubigeo", {"codregistro":phuyu_datos.registro}).then(function(data){

				var datos = eval(data.body.ubigeo); this.campos.departamento = datos[0]["ubidepartamento"];
				this.campos.provincia = datos[0]["ubiprovincia"]; this.campos.codubigeo = datos[0]["codubigeo"];

				this.$http.get(url+"ventas/clientes/provincias/"+this.campos.departamento).then(function(data){
					$("#provincia").empty().html(data.body); $("#codubigeo").empty().html('<option value="">SELECCIONE</option>');
					this.campos.provincia = datos[0]["ubiprovincia"];

					this.$http.get(url+"ventas/clientes/distritos/"+this.campos.departamento+"/"+this.campos.provincia).then(function(data){
						$("#codubigeo").empty().html(data.body); this.campos.codubigeo = datos[0]["codubigeo"];
					});
				});
			});
		},
		phuyu_editarsucursal: function(){
			this.$http.post(url+phuyu_controller+"/ubigeo", {"codregistro":phuyu_datos.registro}).then(function(data){

				var datos = eval(data.body.ubigeo); this.campos.departamento = datos[0]["ubidepartamento"];
				this.campos.provincia = datos[0]["ubiprovincia"]; this.campos.codubigeo = datos[0]["codubigeo"];
				
				this.$http.get(url+"ventas/clientes/provincias/"+this.campos.departamento).then(function(data){
					$("#provincia").empty().html(data.body); $("#codubigeo").empty().html('<option value="">SELECCIONE</option>');
					this.campos.provincia = datos[0]["ubiprovincia"];

					this.$http.get(url+"ventas/clientes/distritos/"+this.campos.departamento+"/"+this.campos.provincia).then(function(data){
						$("#codubigeo").empty().html(data.body); this.campos.codubigeo = datos[0]["codubigeo"];

						this.$http.get(url+"administracion/sucursales/lineasxsucursales/"+phuyu_datos.registro).then(function(data){
							this.campos.lineas = data.body;
						});
					});
				});
			});
		},
		phuyu_editarlinea: function(){
			this.$http.get(url+"administracion/sucursales/sucursalesxlineas/"+phuyu_datos.registro).then(function(data){
				this.campos.sucursales = data.body;
			});
		},
		phuyu_editarsucursalrubro: function(){
			this.$http.get(url+"administracion/rubros/sucursalrubro/"+phuyu_datos.registro).then(function(data){
				this.campos.sucursales = data.body;
			});
		},
		phuyu_cerrar: function(){
			$(".compose").slideToggle();
		},
		np_guardar_cargarproductos: function(){
            this.estado = 1; const formulario = new FormData($("#formulario_cargarproductos")[0]);
            this.$http.post(url+"almacen/kardex/cargarproductos", formulario).then(function (response) {
                if (response.body.estado == 1) {
                    phuyu_sistema.phuyu_alerta("Guardado correctamente !!!", "Carga del archivo realizada correctamente", "success");
                }else{
                    phuyu_sistema.phuyu_alerta("Operación no registrada !!!", "Formato del archivo incorrecto", "error");
                }
                this.phuyu_cerrar(); this.estado = 0;
            }, function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS", "ERROR DE RED","error");
			});
        },
        np_guardar_stockextra: function(){
            this.estado = 1; const formulario = new FormData($("#formulario_stockextra")[0]);
            this.$http.post(url+"almacen/productos/stockextra", formulario).then(function (response) {
                if (response.body.estado == 1) {
                    phuyu_sistema.phuyu_alerta("Guardado correctamente !!!", "Carga del archivo realizada correctamente", "success");
                }else{
                	phuyu_sistema.phuyu_alerta("Operación no registrada !!!", "Formato del archivo incorrecto", "error");
                }
                this.phuyu_cerrar(); this.estado = 0;
            }, function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS", "ERROR DE RED","error");
			});
        },
		phuyu_marcar: function(){
			if ($("#marcar").is(":checked")) {
				var marcados = [];
				$('input[name^="lineas"]').each(function() {
					marcados.push($(this).val());
				});
				this.campos.lineas = marcados;
		    }else{
		    	this.campos.lineas = [];
		    }
		},
		phuyu_guardarlineas: function(){
			this.estado= 1;
			this.$http.post(url+phuyu_controller+"/guardarlineas", this.campos).then(function(data){
				if (data.body==1) {
					phuyu_sistema.phuyu_alerta("LINEAS ASIGNADAS CORRECTAMENTE", "UN NUEVO REGISTRO EN EL SISTEMA","success");
				}else{
					phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR AL REGISTRAR", "NO SE PUEDE REGISTRAR","error");
				}
				phuyu_datos.phuyu_opcion(); this.phuyu_cerrar();
			}, function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS", "ERROR DE RED","error");
			});
		}
	},
	mounted: function(){
		if (phuyu_datos.registro>0) {
			if (phuyu_controller=="administracion/almacenes") {
				this.phuyu_editaralmacen();
			}
			if (phuyu_controller=="administracion/sucursales" || phuyu_controller=="administracion/zonas") {
				this.phuyu_editarsucursal();
			}
			if(phuyu_controller=="almacen/lineas"){
				this.phuyu_editarlinea();
			}
			if(phuyu_controller=="administracion/rubros"){
				this.phuyu_editarsucursalrubro();
			}
		}
	}
});