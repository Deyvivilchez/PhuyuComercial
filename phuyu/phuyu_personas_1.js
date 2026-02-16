var phuyu_form = new Vue({
	el: "#phuyu_form",
	data: {
		estado: 0, campos: campos, tipo: "CLIENTE", urltipo: "ventas/clientes"
	},
	methods: {
		phuyu_guardar_1: function(){
			if (phuyu_controller=="compras/compras") {
				this.tipo = "PROVEEDOR"; this.urltipo = "compras/proveedores";
			}
			if (phuyu_controller=="creditos/cuentascobrar") {
				this.tipo = "CLIENTE"; this.urltipo = "ventas/clientes";
			}
			if (phuyu_controller=="creditos/cuentaspagar") {
				this.tipo = "PROVEEDOR"; this.urltipo = "compras/proveedores";
			}

			this.estado= 1;
			this.$http.post(url+this.urltipo+"/guardar_1", this.campos).then(function(data){
				if (data.body==0) {
					phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR AL REGISTRAR", "NO SE PUEDE REGISTRAR","error");
				}else{
					phuyu_sistema.phuyu_noti(this.tipo+" REGISTRADO CORRECTAMENTE", "UN NUEVO "+this.tipo+" EN EL SISTEMA","success");
					if (phuyu_controller=="creditos/cuentascobrar") {
						phuyu_creditos.phuyu_datos();
					}
					var socio = eval(data.body);
					if(phuyu_controller=="ventas/ventas" || phuyu_controller=="ventas/pedidos" || phuyu_controller=="ventas/proformas" || phuyu_controller=="compras/compras"){ 
						if($("#acv").is(':checked')){
							$("#acv").click();
						}else{
							phuyu_operacion.activar_cvarios();
						}
						phuyu_operacion.campos.codpersona = socio[0]["codpersona"];
						phuyu_operacion.campos.cliente = socio[0]["razonsocial"];
						$("#codpersona").empty().html("<option value='"+socio[0]["codpersona"]+"'>"+socio[0]["razonsocial"]+"</option>");
						phuyu_operacion.phuyu_infocliente();
					}
					$(".select2-selection__rendered").empty().append(socio[0]["razonsocial"]); 					
					
				}
				this.phuyu_cerrar();
			}, function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS", "ERROR DE RED","error"); this.phuyu_cerrar();
			});
		},
		phuyu_cerrar: function(){
			$(".compose").slideToggle();
		},

		phuyu_tipodocumento: function(){
			if (this.campos.coddocumentotipo==2) {
				$("#documento").attr("minlength","8"); $("#documento").attr("maxlength","8");
			}else{
				if (this.campos.coddocumentotipo==4) {
					$("#documento").attr("minlength","11"); $("#documento").attr("maxlength","11");
				}else{
					$("#documento").attr("minlength","8"); $("#documento").attr("maxlength","15");
				}
			}

			if(this.campos.coddocumentotipo==1){
				console.log(this.campos.coddocumentotipo)
				$("#documento").removeAttr('required');
				$("#documento").attr('readonly',true);
				$(".btn-consultar").attr('disabled',true);
			}else{
				$("#documento").attr('required',true);
				$("#documento").removeAttr('readonly');
				$(".btn-consultar").removeAttr('disabled');
			}
		},
		phuyu_consultar: function(){
			if (this.campos.coddocumentotipo=="") {
				phuyu_sistema.phuyu_noti("SELECCIONE TIPO DE DOCUMENTO","DEBE SELECCIONAR . . .","danger"); 
				this.$refs.coddocumentotipo.focus(); return false;
			}

			if (this.campos.coddocumentotipo==2) {
				if (this.campos.documento.length!=8) {
					this.$refs.documento.focus(); return false;
				}
			}
			if (this.campos.coddocumentotipo==4) {
				if (this.campos.documento.length!=11) {
					this.$refs.documento.focus(); return false;
				}
			}
			
			$(".btn-consultar").attr("disabled","true");
			this.$http.get(url+"web/phuyu_buscarsocio/"+this.campos.documento).then(function(data){
				if (data.body!="") {
					var datos = eval(data.body);
					if(datos[0]["estado"] !== "0"){
						this.campos.razonsocial = datos[0]["razonsocial"];
						this.campos.nombrecomercial = datos[0]["nombrecomercial"];
						this.campos.direccion = datos[0]["direccion"];
						this.campos.email = datos[0]["email"];
						this.campos.telefono = datos[0]["telefono"];
						this.campos.sexo = datos[0]["sexo"];
						phuyu_sistema.phuyu_noti("DOCUMENTO EXISTE EN EL SISTEMA","DOCUMENTO YA REGISTRADO","warning");
						$(".btn-consultar").removeAttr("disabled");
					}else{
						var numero = this.campos.documento;
						swal({
							title: "EL REGISTRO ESTÁ ELIMINADO O ANULADO",   
							text: "USTED DESEA ACTIVARLO DE NUEVO AL SISTEMA?", 
							icon: "warning",
							dangerMode: true,
							buttons: ["CANCELAR", "SI, ACTIVAR"],
						}).then((willDelete) => {
							if (willDelete) {
								this.$http.post(url+"ventas/clientes/activar/"+datos[0]["codpersona"]).then(function(data){
									if (data.body==1) {
										phuyu_sistema.phuyu_alerta("ACTIVADO CORRECTAMENTE", "UN REGISTRO ACTIVADO EN EL SISTEMA","success");
										phuyu_datos.phuyu_opcion();
									}else{
										phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR !!!", "SE PERDIÓ LA CONEXION !!! LO SENTIMOS","error");
									}
									this.phuyu_opcion();
								}, function(){
									phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
								});
							}
						});

						this.phuyu_cerrar();
					}
				}else{
					if (this.campos.coddocumentotipo==2) {
						this.$http.get(url+"web/phuyu_dni/"+this.campos.documento).then(function(data){
							if(data.body.success==true){
								this.campos.razonsocial = data.body.result.apellidoPaterno+" "+data.body.result.apellidoMaterno+" "+data.body.result.nombres;
								
								this.campos.direccion = "-";
							}else{
								phuyu_sistema.phuyu_noti("NO SE ENCONTRARON DATOS","DOCUMENTO NO EXISTE","danger");
							}
							$(".btn-consultar").removeAttr("disabled");
						});
					}else{
						if (this.campos.coddocumentotipo==4) {
							this.$http.get(url+"web/phuyu_ruc/"+this.campos.documento).then(function(data){
								if(data.body.persona){
									this.campos.razonsocial = data.body.persona.razonSocial;
									this.campos.direccion = data.body.persona.direccion+' '+data.body.persona.departamento+' - '+data.body.persona.provincia+' - '+data.body.persona.distrito;
									this.campos.nombrecomercial = data.body.persona.razonSocial;
								}else{
									phuyu_sistema.phuyu_noti("NO SE ENCONTRARON DATOS","DOCUMENTO NO EXISTE","danger");
								}
								$(".btn-consultar").removeAttr("disabled");
							});
						}else{
							phuyu_sistema.phuyu_noti("NO SE ENCONTRARON DATOS","DOCUMENTO NO EXISTE","danger");
							$(".btn-consultar").removeAttr("disabled");
						}
					}
				}
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
		phuyu_zonas: function(){
			if (this.campos.codubigeo!=undefined) {
				this.$http.get(url+"ventas/clientes/zonas/"+this.campos.codubigeo).then(function(data){
					$("#codzona").empty().html(data.body);
				});
			}
		}

	}
});