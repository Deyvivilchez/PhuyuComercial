var phuyu_form = new Vue({
	el: "#phuyu_form",
	data: {
		estado: 0, campos: campos, tipo: "CONDUCTOR", urltipo: "ventas/clientes"
	},
	methods: {
		phuyu_guardar_1: function(){

			this.estado= 1;
			this.$http.post(url+this.urltipo+"/guardar_conductor", this.campos).then(function(data){
				if (data.body==0) {
					phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR AL REGISTRAR", "NO SE PUEDE REGISTRAR","error");
				}else{
					phuyu_sistema.phuyu_noti("CONDUCTOR REGISTRADO CORRECTAMENTE", "UN NUEVO CONDUCTOR EN EL SISTEMA","success");

					/* var socio = eval(data.body);
					$("#codpersona").empty().html("<option value='"+socio[0]["codpersona"]+"'>"+socio[0]["razonsocial"]+"</option>");

					$(".selectpicker").selectpicker("refresh"); $(".filter-option").text(socio[0]["razonsocial"]); 
					$("#codpersona").val(socio[0]["codpersona"]); this.campos.codpersona = socio[0]["codpersona"]; */
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
		},
		phuyu_consultar: function(){
			if (this.campos.coddocumentotipo=="") {
				phuyu_sistema.phuyu_noti("SELECCIONE TIPO DE DOCUMENTO","DEBE SELECCIONAR . . .","error"); 
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
			
			$(".btn-consultar").empty().html("<i class='fa fa-spinner fa-spin'></i>"); $(".btn-consultar").attr("disabled","true");
			$(".btn-consultar").empty().html("<i class='fa fa-spinner fa-spin'></i>"); $(".btn-consultar").attr("disabled","true");
			this.$http.get(url+"web/phuyu_buscarsocio/"+this.campos.documento).then(function(data){
				if (data.body!="") {
					var datos = eval(data.body);
					this.campos.razonsocial = datos[0]["razonsocial"];
					this.campos.nombrecomercial = datos[0]["nombrecomercial"];
					this.campos.direccion = datos[0]["direccion"];
					this.campos.email = datos[0]["email"];
					this.campos.telefono = datos[0]["telefono"];
					this.campos.sexo = datos[0]["sexo"];
					phuyu_sistema.phuyu_noti("DOCUMENTO EXISTE EN EL SISTEMA","DOCUMENTO YA REGISTRADO","warning");
					$(".btn-consultar").empty().html("<i class='fa fa-search'></i>"); $(".btn-consultar").removeAttr("disabled");
				}else{
					if (this.campos.coddocumentotipo==2) {
						this.$http.get(url+"web/phuyu_dni/"+this.campos.documento).then(function(data){
							if(data.body.success==true){
								this.campos.razonsocial = data.body.result.apellidoPaterno+" "+data.body.result.apellidoMaterno+" "+data.body.result.nombres;
								
								this.campos.direccion = "-";
							}else{
								phuyu_sistema.phuyu_noti("NO SE ENCONTRARON DATOS","DOCUMENTO NO EXISTE","error");
							}
							$(".btn-consultar").empty().html("<i class='fa fa-search'></i>"); $(".btn-consultar").removeAttr("disabled");
						});
					}else{
						if (this.campos.coddocumentotipo==4) {
							this.$http.get(url+"web/phuyu_ruc/"+this.campos.documento).then(function(data){
								if(data.body.success==true){
									this.campos.razonsocial = data.body.result.RazonSocial;
									this.campos.direccion = data.body.result.Direccion;
								}else{
									phuyu_sistema.phuyu_noti("NO SE ENCONTRARON DATOS","DOCUMENTO NO EXISTE","error");
								}
								$(".btn-consultar").empty().html("<i class='fa fa-search'></i>"); $(".btn-consultar").removeAttr("disabled");
							});
						}else{
							phuyu_sistema.phuyu_noti("NO SE ENCONTRARON DATOS","DOCUMENTO NO EXISTE","error");
							$(".btn-consultar").empty().html("<i class='fa fa-search'></i>"); $(".btn-consultar").removeAttr("disabled");
						}
					}
				}
			});
		}
	}
});