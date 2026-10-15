var phuyu_nuevocredito = new Vue({
	el: "#phuyu_nuevocredito",
	data: { "rubro":$("#rubro").val(),"importetotalcredito":0,
		campos:{
			"codregistro":"","codpersona":phuyu_creditos.registro,"codlote":phuyu_creditos.codlote,"fechacredito":$("#fecha").val(),"fechainicio":$("#fecha").val(),
			"nrodias":30,"nrocuotas":1,"codcreditoconcepto":2,"codcajaconcepto":8,"codtipopago":1,"fechadocbanco":$("#fechadocbanco_ref").val(),
			"nrodocbanco":"","importe":"","tasainteres":0,"interes":0,"total":0,"afectacaja":true,"referencia":""
		},
		estado:0, cuotas: []
	},
	methods: {
		phuyu_fecha: function(){
			this.campos.fechainicio = $("#fechainicio").val();
			this.campos.fechacredito = $("#fechacredito").val();
		},
		phuyu_fechamovimiento: function(){
			this.campos.fechadocbanco = $("#fechadocbanco").val();
		},

		phuyu_calcular: function(){
			var importe = Number((this.campos.importe/this.campos.nrocuotas).toFixed(1));
			var interes = Number(( (importe*(this.campos.tasainteres/100)*(this.campos.nrodias/30)) ).toFixed(1));
			var total = Number((importe + interes).toFixed(1));
			
			var fechainicio = String(this.campos.fechainicio).split("-");
    		var fecha = new Date(fechainicio[0]+"/"+fechainicio[1]+"/"+fechainicio[2]);

    		this.campos.interes = Number(( (this.campos.importe*(this.campos.tasainteres/100)*(this.campos.nrodias/30)) ).toFixed(1));
    		if (this.campos.importe=="") {
    			this.campos.total = 0;
    			this.campos.totales = 0;
    		}else{
    			this.campos.total = Number(( parseFloat(this.campos.importe) + parseFloat(this.campos.interes) ).toFixed(1));
    			this.campos.totales = Number(( parseFloat(this.campos.importe) + parseFloat(this.campos.interes) ).toFixed(1));
    		}

			this.cuotas = []; var suma_importe = 0; var suma_total = 0;this.importetotalcredito = 0;
			for (var i = 1; i <= this.campos.nrocuotas; i++) {
				if (this.campos.nrodias=="") {
					fecha.setDate(fecha.getDate() + 0);
				}else{
					fecha.setDate(fecha.getDate() + parseInt(this.campos.nrodias));
				}

				year = fecha.getFullYear(); month = String(fecha.getMonth() + 1); day = String(fecha.getDate());
				if (month.length < 2) month = "0"+month;
				if (day.length < 2) day = "0"+day;

				fechavence = year+"-"+month+"-"+day; 

				if (this.campos.nrocuotas==i) {
					importe = Number(( this.campos.importe - parseFloat(suma_importe) ).toFixed(1));
					total = Number(( this.campos.total - parseFloat(suma_total) ).toFixed(1));
				}else{
					suma_importe = Number(( parseFloat(suma_importe) + parseFloat(importe) ).toFixed(1));
					suma_total = Number(( parseFloat(suma_total) + parseFloat(total) ).toFixed(1));
				}

				if (this.campos.tipocuota==1 && i!=1){
					importe = Number((total/this.campos.nrocuotas).toFixed(1));
					interes = Number(( (importe*(this.campos.tasainteres/100)*(this.campos.nrodias/30)) ).toFixed(1));
					total = Number((importe + interes).toFixed(1));
				}

				this.importetotalcredito = (this.importetotalcredito + total);

				this.cuotas.push({
					"nrocuota":i,"fechavence":fechavence,"nroletra":"","nrounicodepago":"","importe":importe,"tasa":this.campos.tasainteres,
					"interes":interes,"total":total
				});
			}
		},

		calcular_credito: function(){
			var importe = Number((this.campos.importe/this.campos.nrocuotas).toFixed(1));
			var interes = Number(( (importe*(this.campos.tasainteres/100)*(this.campos.nrodias/30)) ).toFixed(1));
			var total = Number((importe + interes).toFixed(1));
			
			var fechainicio = String(this.campos.fechainicio).split("-");
    		var fecha = new Date(fechainicio[0]+"/"+fechainicio[1]+"/"+fechainicio[2]);

    		this.campos.interes = Number(( (this.campos.importe*(this.campos.tasainteres/100)*(this.campos.nrodias/30)) ).toFixed(1));
    		if (this.campos.importe=="") {
    			this.campos.total = 0;
    			this.campos.totales = 0;
    		}else{
    			this.campos.total = Number(( parseFloat(this.campos.importe) + parseFloat(this.campos.interes) ).toFixed(1));
    			this.campos.totales = Number(( parseFloat(this.campos.importe) + parseFloat(this.campos.interes) ).toFixed(1));
    		}
    		this.importetotalcredito = 0;
			var t = this;
			var l = this.cuotas.length; i = 1;
			var suma_importe = 0; var suma_total = 0;
			var cuotas = this.cuotas.filter(function(p){
				p.interes = interes;
				p.total = Number((parseFloat(p.importe) + parseFloat(p.interes)).toFixed(2));
				if(l==i){
					p.importe = Number(( t.campos.importe - parseFloat(suma_importe) ).toFixed(1));
					p.total = Number(( t.campos.total - parseFloat(suma_total) ).toFixed(1));
				}else{
					suma_importe = Number(( parseFloat(suma_importe) + parseFloat(p.importe) ).toFixed(1));
					suma_total = Number(( parseFloat(suma_total) + parseFloat(p.total) ).toFixed(1));
				}
				t.importetotalcredito = t.importetotalcredito + p.total;
				i++
			});

			this.importetotalcredito = parseFloat(this.importetotalcredito).toFixed(2);
		},

		phuyu_cal: function(){
			this.importetotalcredito = 0;
			var t = this;
			var cuotas = this.cuotas.filter(function(p){
				console.log(p.total)
				t.importetotalcredito = t.importetotalcredito + parseFloat(p.total);
				console.log(t.importetotalcredito)
			});

			t.importetotalcredito = parseFloat(t.importetotalcredito).toFixed(2);
		},
		phuyu_guardar: function(){
			if (this.cuotas.length==0) {
				phuyu_sistema.phuyu_noti("DEBE INGRESAR UN MONTO","NO SE ENCONTRARON CUOTAS","error"); 
				return false;
			}
			if(this.campos.codlote==0 && this.rubro==6){
				phuyu_sistema.phuyu_noti("ATENCION USUARIO: EL CREDITO NO SE PUEDE REALIZAR PORQUE EL PROVEEDOR SELECCIONADO NO CUENTA CON UNA LINEA DE CREDITO VÁLIDA","","error");
				return false;
			}
			this.estado = 1; phuyu_sistema.phuyu_inicio_guardar("GUARDANDO CREDITO POR PAGAR . . .");
			this.$http.post(url+phuyu_controller+"/guardar", {"campos":this.campos,"cuotas":this.cuotas}).then(function(data){
				if (data.body=="e") {
					phuyu_sistema.phuyu_alerta("SU SESION DE USUARIO A TERMINADO","DEBE INICIAR SESION NUEVAMENTE","error");
				}else{
					if (data.body==1) {
						phuyu_sistema.phuyu_alerta("CREDITO POR PAGAR REGISTRADO","CREDITO REGISTRADO EN EL SISTEMA","success");
					}else{
						phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR CREDITO","ERROR DE RED","error");
					}
				}
				phuyu_sistema.phuyu_fin(); phuyu_sistema.phuyu_modulo();
			}, function(){
				phuyu_sistema.phuyu_alerta("ERROR AL CUENTA POR PAGAR","ERROR DE RED","error");
			});
		},
		phuyu_cerrar: function(){
			phuyu_sistema.phuyu_modulo();
		},
		phuyu_lineascredito: function(){
			if (this.campos.codpersona!="" || this.campos.codpersona!=2) {
				this.$http.get(url+"ventas/lineascredito/phuyu_lineascredito/"+this.campos.codpersona).then(function(data){
					$("#codlote").empty().html(data.body);
					this.campos.codlote = $("#codlote").val()
				});
			}
		},
		phuyu_lineascreditodirecto: function(){
			if (this.campos.codpersona=="" || this.campos.codpersona==2) {
				phuyu_sistema.phuyu_noti("ATENCION USUARIO: PARA REALIZAR UNA NUEVA LINEA DE CREDITO DEBE SELECCIONAR UN CLIENTE","","error");
					return false;
			}
			swal({
				title: "DESEA AGREGAR UNA LINEA DE CREDITO DIRECTO?",   
				text: "USTED ESTA POR REALIZAR EL PROCESO DE LINEA DE CREDITO DIRECTO", 
				icon: "warning",
				dangerMode: true,
				buttons: ["CANCELAR", "SI, AGREGAR"],
			}).then((willDelete) => {
				if (willDelete) {
					this.$http.post(url+"ventas/lineascredito/guardarlineascreditodirecto",{"codpersona":this.campos.codpersona}).then(function(data){
						if (data.body==1) {
							phuyu_sistema.phuyu_noti("LINEA DE CREDITO AGREGADO CORRECTAMENTE", "UN REGISTRO AGREGADO EN EL SISTEMA","success");
						}else{
							phuyu_sistema.phuyu_noti("OCURRIO UN ERROR !!!", "SE PERDIÓ LA CONEXION !!! LO SENTIMOS","error");
						}
						this.phuyu_lineascredito();
					}, function(){
						alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
					});
				}
			});
		}
	},
	created: function(){
		phuyu_sistema.phuyu_fin();
	}
});