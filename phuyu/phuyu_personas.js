var phuyu_form = new Vue({
	el: "#phuyu_form",
	data: {
		estado: 0, campos: campos
	},
	methods: {
		phuyu_guardar: function(){
			this.estado= 1;
			if (phuyu_controller=="creditos/cuentaspagar" || phuyu_controller=="creditos/cuentascobrar") {
				var ruta = "caja/ctasctes";
			}else{
				var ruta = phuyu_controller;
			}
			this.$http.post(url+ruta+"/guardar", this.campos).then(function(data){
				if (data.body=="e") {
					phuyu_sistema.phuyu_noti("ESTE NRO DE DOCUMENTO YA EXISTE", "CAMBIAR DE NRO DOCUMENTO","danger"); this.estado= 0;
				}else{
					if (data.body==1) {
						if (this.campos.codregistro=="") {
							phuyu_sistema.phuyu_alerta("GUARDADO CORRECTAMENTE", "UN NUEVO REGISTRO EN EL SISTEMA","success");
							if (phuyu_controller=="creditos/cuentaspagar" || phuyu_controller=="creditos/cuentascobrar") {
								$("#phuyu_tituloform").text("BUSCAR CUENTAS CORRIENTES DEL SOCIO"); phuyu_sistema.phuyu_loader("cuerpo",180); 
								this.$http.post(url+"caja/ctasctes/buscar",{"codregistro":phuyu_creditos.registro}).then(function(data){
									$("#cuerpo").empty().html(data.body);
									phuyu_sistema.phuyu_finloader("cuerpo");
								});
							}
						}else{
							phuyu_sistema.phuyu_alerta("EDITADO CORRECTAMENTE", "UN REGISTRO EDITADO EN EL SISTEMA","info");
						}
					}else{
						phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR AL REGISTRAR", "NO SE PUEDE REGISTRAR","error");
					}
					if (phuyu_controller!="creditos/cuentaspagar" && phuyu_controller!="creditos/cuentascobrar") {
						phuyu_datos.phuyu_opcion(); 
						this.phuyu_cerrar();
					}
				}
			}, function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS", "ERROR DE RED","error");
			});
		},
		phuyu_cerrar: function(){
			if (phuyu_controller=="creditos/cuentaspagar" || phuyu_controller=="creditos/cuentascobrar") {
				$("#phuyu_tituloform").text("BUSCAR CUENTAS CORRIENTES DEL SOCIO"); phuyu_sistema.phuyu_loader("cuerpo",180);
				this.$http.post(url+"caja/ctasctes/buscar",{"codregistro":phuyu_creditos.registro}).then(function(data){
					$("#cuerpo").empty().html(data.body);
					phuyu_sistema.phuyu_finloader("cuerpo");
				},function(){
					phuyu_sistema.phuyu_error(); 
				});
			}else{
				$(".compose").slideToggle();
			}
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
								this.$http.post(url+phuyu_controller+"/activar/"+datos[0]["codpersona"]).then(function(data){
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
							this.$http.post(url+"web/phuyu_ruc/"+this.campos.documento).then(function(data){
								if(data.body.persona){
									this.campos.razonsocial = data.body.persona.razonSocial;
									this.campos.direccion = data.body.persona.direccion;
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
		},
		phuyu_editarpersona: function(){
			this.$http.post(url+phuyu_controller+"/ubigeo", {"codregistro":phuyu_datos.registro}).then(function(data){
				if (this.campos.codpatrocinador==undefined) {
					var patrocinador = eval(data.body.patrocinador);
					$(".select2-selection__rendered").empty().append(patrocinador[0]["razonsocial"]); 
					$("#codpatrocinador").val(patrocinador[0]["codpersona"]); this.campos.codpatrocinador = patrocinador[0]["codpersona"];
				}
				var zona = eval(data.body.codzona);
				var datos = eval(data.body.ubigeo); this.campos.departamento = datos[0]["ubidepartamento"];
				this.campos.provincia = datos[0]["ubiprovincia"]; this.campos.codubigeo = datos[0]["codubigeo"];

				this.$http.get(url+"ventas/clientes/provincias/"+this.campos.departamento).then(function(data){
					$("#provincia").empty().html(data.body); $("#codubigeo").empty().html('<option value="">SELECCIONE</option>');
					this.campos.provincia = datos[0]["ubiprovincia"];

					this.$http.get(url+"ventas/clientes/distritos/"+this.campos.departamento+"/"+this.campos.provincia).then(function(data){
						$("#codubigeo").empty().html(data.body); this.campos.codubigeo = datos[0]["codubigeo"];

						this.$http.get(url+"ventas/clientes/zonas/"+this.campos.codubigeo).then(function(data){
							$("#codzona").empty().html(data.body); this.campos.codzona = zona;
						});

					});
				});
			});
		},
		phuyu_editarctacte:function(){
			this.$http.post(url+phuyu_controller+"/socio", {"codregistro":phuyu_datos.registro}).then(function(data){
				var socio = eval(data.body);
				$(".select2-selection__rendered").text(socio[0]["razonsocial"]); 
				this.campos.codpersona = socio[0]["codpersona"];
			});
		},
		phuyu_obtenersocio:function(){
			if(phuyu_controller=="creditos/cuentascobrar"){
				var id = 1
			}else{
				var id = phuyu_creditos.registro
			}
			this.$http.post(url+"caja/ctasctes/obtenersocio", {"codregistro":id}).then(function(data){
				var socio = eval(data.body);
				$("..select2-selection__rendered").text(socio[0]["razonsocial"]); 
				this.campos.codpersona = socio[0]["codpersona"];
			});
		}
	},
	mounted: function(){
		if (phuyu_controller!="creditos/cuentaspagar" && phuyu_controller!="creditos/cuentascobrar") {
			if (phuyu_datos.registro>0) {
				if (phuyu_controller=="ventas/clientes" || phuyu_controller=="compras/proveedores") {
					this.phuyu_editarpersona();
				}
				if (phuyu_controller=="caja/ctasctes") {
					this.phuyu_editarctacte();
				}
			}
		}else{
			if(phuyu_creditos.registro>0){
				this.phuyu_obtenersocio();
			}
		}
	}
});