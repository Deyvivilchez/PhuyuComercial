var phuyu_operacion = new Vue({
	el: "#phuyu_operacion",
	data: {
		estado:0,
		campos:{
			codlote:0, codsocio:"",codsocioreferencia:"", fechainicio:"", fechafin:"", departamento:"", observaciones:"", cliente:"", direccion:"",
			provincia:"", codubigeo:"", codzona:"", codempleado:"", area:0, tipoposesion:"", estado:"0",comprado:0,tasainteres:0, creditomaximo:0
		}
	},
	methods: {

		/* FUNCIONES GENERALES DE LA VENTA */

		phuyu_venta: function(){
			swal({
				title: "SEGURO REGISTRAR NUEVA VENTA?",   
				text: "LOS CAMPOS SE QUEDARAN VACIOS ", 
				icon: "warning",
				dangerMode: true,
				buttons: ["CANCELAR", "SI, NUEVA VENTA"],
			}).then((willDelete) => {
				if (willDelete){
					this.phuyu_nueva_venta();
				}
			});
		},
		phuyu_nueva_venta: function(){
			phuyu_sistema.phuyu_inicio(); $(".in").remove();
			this.$http.post(url+phuyu_controller+"/nuevo").then(function(data){
				$("#phuyu_sistema").empty().html(data.body);
			});
		},
		phuyu_nuevo_zona: function(){
			this.$http.get(url+"administracion/zonas/nuevo_1").then(function(data){
				$("#zonas_modal").empty().html(data.body); $("#modal_zonas").modal("show");
			});
		},
		obtener_zona: function(codzona,datos){
			var zona = eval(codzona);
			this.campos.departamento = datos[0]["ubidepartamento"];

			this.$http.get(url+"ventas/clientes/provincias/"+this.campos.departamento).then(function(data){
				$("#provincia").empty().html(data.body); 
				$("#codubigeo").empty().html('<option value="">SELECCIONE</option>');
				this.campos.provincia = datos[0]["ubiprovincia"];

				this.$http.get(url+"ventas/clientes/distritos/"+this.campos.departamento+"/"+this.campos.provincia).then(function(data){
					$("#codubigeo").empty().html(data.body); 
					this.campos.codubigeo = datos[0]["codubigeo"];

					this.$http.get(url+"ventas/clientes/zonas/"+this.campos.codubigeo).then(function(data){
						$("#codzona").empty().html(data.body); 
						this.campos.codzona = zona;

						$("#modal_zonas").modal("hide");
					});

				});
			});
		},
		phuyu_atras: function(){
			phuyu_sistema.phuyu_modulo();
		},
		phuyu_addcliente: function(){
			$(".compose").slideToggle(); $("#phuyu_tituloform").text("CREAR CLIENTE");  phuyu_sistema.phuyu_loader("phuyu_formulario",180); 
			this.$http.post(url+"ventas/clientes/nuevo_1").then(function(data){
				$("#phuyu_formulario").empty().html(data.body);
			},function(){ 
				phuyu_sistema.phuyu_error_operacion(); 
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
		phuyu_infosocio: function(){
			this.campos.codsocio = $("#codsocio").val();
        },
        phuyu_infosocioref: function(){
			this.campos.codsocioreferencia = $("#codsocioreferencia").val();
        },
		/* DATOS GENERALES DE LA VENTA */

		phuyu_guardar: function(){
			this.campos.fechainicio = $("#fechainicio").val();
			this.campos.fechafin = $("#fechafin").val();

			this.estado= 1;
			this.$http.post(url+phuyu_controller+"/guardar", this.campos).then(function(data){
				if (data.body.estado==1) {
					phuyu_sistema.phuyu_alerta("GUARDADO CORRECTAMENTE", "UN NUEVO REGISTRO EN EL SISTEMA","success");
					if(data.body.codlote==0){
						this.phuyu_nueva_venta();
					}else{
						phuyu_sistema.phuyu_modulo();
					}
				}else{
					phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR AL REGISTRAR", "NO SE PUEDE REGISTRAR","error");
				}
			}, function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS", "ERROR DE RED","error");
			});
		},
		phuyu_editar: function(){
			this.$http.post(url+phuyu_controller+"/editar",{"codregistro":phuyu_lineas.registro}).then(function(info){
				this.campos.codlote = phuyu_lineas.registro;
				var socio = eval(info.body.info);
				var datos = eval(info.body.ubigeo);
				$("#select2-codsocio-container").empty().append(socio[0]["cliente"]);
				this.campos.codsocio = socio[0]["codsocio"];
				$("#select2-codsocioreferencia-container").empty().append(socio[0]["garante"]);
				this.campos.codsocioreferencia = socio[0]["codsocioreferencia"];

				$("#codsocio").removeAttr('required');
				$("#fechainicio").val(socio[0]["fechainicio"]); 
				$("#fechafin").val(socio[0]["fechafin"]);
				this.campos.direccion = socio[0]["direccion"];
				this.campos.codempleado = socio[0]["codempleado"];
				this.campos.area = socio[0]["area"];
				this.campos.tipoposesion = socio[0]["tipoposesion"];
				this.campos.observaciones = socio[0]["observaciones"];
				this.campos.tasainteres = socio[0]["tasainteres"];
				this.campos.creditomaximo = socio[0]["creditomaximo"];
				this.campos.comprado = parseInt(socio[0]["comprado"]);
				this.obtener_zona(socio[0]["codzona"],datos);
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); phuyu_sistema.phuyu_fin();
			});
		}
	},
	created: function(){
		phuyu_sistema.phuyu_fin();
		if (parseInt(phuyu_lineas.registro)!=0) {
			this.phuyu_editar();
		}
	}
});

document.addEventListener("keyup", buscar_f11, false);
function buscar_f11(e){
    var keyCode = e.keyCode;
    if(keyCode==122){
    	phuyu_operacion.phuyu_item();
    }
}