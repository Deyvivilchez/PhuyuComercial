var phuyu_editarcredito = new Vue({
	el: "#phuyu_editarcredito",
	data: { "rubro":$("#rubro").val(), "importetotalcredito":0,"importecredito":0,"interescredito":0,
		campos:{
			"codregistro":$("#codcredito").val(),"codpersona":phuyu_creditos.registro,"codmovimiento":0,"codempleado":0,"codlote":0,"nrotarjeta":"","fechacredito":$("#fecha").val(),"fechainicio":$("#fecha").val(),
			"nrodias":30, "codmoneda":1, "tipocambio":0.00,"nrocuotas":1,"codcreditoconcepto":1,"codcajaconcepto":7,"codtipopago":1,"fechadocbanco":$("#fechadocbanco_ref").val(),
			"nrodocbanco":"","importe":"","tasainteres":0,"interes":0,"total":0,"totales":0,"tipocuota":0,"afectacaja":true,"referencia":"","creditoprogramado":1
		},
		estado:0, cuotas: []
	},
	methods: {
		phuyu_tipocambio(){
			if (this.campos.codmoneda==1) {
				this.campos.tipocambio = 1;
			}else{
				this.campos.fechacredito = $("#fechacredito").val();
				this.$http.get(url+"caja/tipocambios/consulta/"+this.campos.fechacredito).then(function(data){
					this.campos.tipocambio = data.body;
				});
			}
		},
		phuyu_fecha: function(){
			this.campos.fechainicio = $("#fechainicio").val();
			this.campos.fechacredito = $("#fechacredito").val();
			this.phuyu_calcular();
		},
		phuyu_fechamovimiento: function(){
			this.campos.fechadocbanco = $("#fechadocbanco").val();
		},

		phuyu_calcular: function(){
			var importe = Number((this.campos.importe/this.campos.nrocuotas).toFixed(2));
			var interes = Number(( (importe*(this.campos.tasainteres/100)*(this.campos.nrodias/30)) ).toFixed(2));
			var total = Number((importe + interes).toFixed(2));
			
			var fechainicio = String(this.campos.fechainicio).split("-");
    		var fecha = new Date(fechainicio[0]+"/"+fechainicio[1]+"/"+fechainicio[2]);

    		this.campos.interes = Number(( (this.campos.importe*(this.campos.tasainteres/100)*(this.campos.nrodias/30)) ).toFixed(2));
    		if (this.campos.importe=="") {
    			this.campos.total = 0;
    			this.campos.totales = 0;
    		}else{
    			this.campos.total = Number(( parseFloat(this.campos.importe) + parseFloat(this.campos.interes) ).toFixed(2));
    			this.campos.totales = Number(( parseFloat(this.campos.importe) + parseFloat(this.campos.interes) ).toFixed(2));
    		}

			this.cuotas = []; var suma_importe = 0; var suma_total = 0;this.importetotalcredito = 0;this.importecredito=0;this.interescredito=0;
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
					importe = Number(( this.campos.importe - parseFloat(suma_importe) ).toFixed(2));
					total = Number(( this.campos.total - parseFloat(suma_total) ).toFixed(2));
				}else{
					suma_importe = Number(( parseFloat(suma_importe) + parseFloat(importe) ).toFixed(2));
					suma_total = Number(( parseFloat(suma_total) + parseFloat(total) ).toFixed(2));
				}

				if (this.campos.tipocuota==1 && i!=1){
					importe = Number((total/this.campos.nrocuotas).toFixed(2));
					interes = Number(( (importe*(this.campos.tasainteres/100)*(this.campos.nrodias/30)) ).toFixed(2));
					total = Number((importe + interes).toFixed(1));
				}
				this.importecredito = this.importecredito + importe;
				this.interescredito = this.interescredito + interes;
				this.importetotalcredito = (this.importetotalcredito + total);

				this.cuotas.push({
					"nrocuota":i,"fechavence":fechavence,"importe":importe,"nroletra":"","nrounicodepago":"","tasa":this.campos.tasainteres,
					"interes":interes,"total":total
				});
			}

			this.importecredito = Number((this.importecredito).toFixed(2));
			this.interescredito = Number((this.interescredito).toFixed(2));
			this.importetotalcredito = Number((this.importetotalcredito).toFixed(2));
		},

		calcular_credito: function(){
			var importe = Number((this.campos.importe/this.campos.nrocuotas).toFixed(2));
			var interes = Number(( (importe*(this.campos.tasainteres/100)*(this.campos.nrodias/30)) ).toFixed(2));
			var total = Number((importe + interes).toFixed(2));
			
			var fechainicio = String(this.campos.fechainicio).split("-");
    		var fecha = new Date(fechainicio[0]+"/"+fechainicio[1]+"/"+fechainicio[2]);

    		this.campos.interes = Number(( (this.campos.importe*(this.campos.tasainteres/100)*(this.campos.nrodias/30)) ).toFixed(2));
    		if (this.campos.importe=="") {
    			this.campos.total = 0;
    			this.campos.totales = 0;
    		}else{
    			this.campos.total = Number(( parseFloat(this.campos.importe) + parseFloat(this.campos.interes) ).toFixed(2));
    			this.campos.totales = Number(( parseFloat(this.campos.importe) + parseFloat(this.campos.interes) ).toFixed(2));
    		}
    		this.importetotalcredito = 0;this.importecredito=0;this.interescredito=0;
			var t = this;
			var l = this.cuotas.length; i = 1;
			var suma_importe = 0; var suma_total = 0;
			var cuotas = this.cuotas.filter(function(p){
				p.interes = interes;
				p.total = Number((parseFloat(p.importe) + parseFloat(p.interes)).toFixed(2));
				if(l==i){
					p.importe = Number(( t.campos.importe - parseFloat(suma_importe) ).toFixed(2));
					p.total = Number(( t.campos.total - parseFloat(suma_total) ).toFixed(2));
				}else{
					suma_importe = Number(( parseFloat(suma_importe) + parseFloat(p.importe) ).toFixed(2));
					suma_total = Number(( parseFloat(suma_total) + parseFloat(p.total) ).toFixed(2));
				}
				t.importecredito = t.importecredito + p.importe;
				t.interescredito = t.interescredito + p.interes;
				t.importetotalcredito = t.importetotalcredito + p.total;
				i++
			});

			this.importetotalcredito = parseFloat(this.importetotalcredito).toFixed(2);
			this.importecredito = parseFloat(this.importecredito).toFixed(2);
			this.interescredito = parseFloat(this.interescredito).toFixed(2);
		},

		phuyu_cal: function(){
			this.importetotalcredito = 0;this.importecredito=0;this.interescredito=0;
			var t = this;
			var cuotas = this.cuotas.filter(function(p){
				//console.log(p.total)
				t.importecredito = t.importecredito + parseFloat(p.importe);
				t.interescredito = t.interescredito + parseFloat(p.interes);
				t.importetotalcredito = t.importetotalcredito + parseFloat(p.total);
				//console.log(t.importetotalcredito)
			});

			t.importetotalcredito = parseFloat(t.importetotalcredito).toFixed(2);
			t.importecredito = parseFloat(t.importecredito).toFixed(2);
			t.interescredito = parseFloat(t.interescredito).toFixed(2);
		},

		phuyu_guardar: function(){
			if (this.cuotas.length==0) {
				phuyu_sistema.phuyu_noti("DEBE INGRESAR UN MONTO","NO SE ENCONTRARON CUOTAS","error"); 
				return false;
			}
			if(parseFloat(this.campos.total)!=parseFloat(this.importetotalcredito)){
				$("#totalimportecredito").addClass("rojo");
				phuyu_sistema.phuyu_noti("ATENCION USUARIO: LA EDICION NO SE PUEDE REALIZAR YA QUE LOS TOTALES DE LOS CREDITOS NO COINCIDEN","","error");
				return false;
			}
			if($("#creditoprogramado").is(':checked')){
				this.campos.creditoprogramado = 1;
			}else{
				this.campos.creditoprogramado = 0;
			}
			this.campos.codlote = $("#codlote").val();
			this.estado = 1; phuyu_sistema.phuyu_inicio_guardar("GUARDANDO CREDITO . . .");
			this.$http.post(url+phuyu_controller+"/editarcambios", {"campos":this.campos,"cuotas":this.cuotas}).then(function(data){
				if (data.body=="e") {
					phuyu_sistema.phuyu_alerta("SU SESION DE USUARIO A TERMINADO","DEBE INICIAR SESION NUEVAMENTE","error");
				}else{
					if (data.body==1) {
						phuyu_sistema.phuyu_alerta("CREDITO EDITADO CORRECTAMENTE","CREDITO EDITADO EN EL SISTEMA","success");
						$("#modal_editar").modal('hide')
					}else{
						phuyu_sistema.phuyu_alerta("ERROR AL EDITAR CREDITO","ERROR DE RED","error");
					}
				}
				phuyu_sistema.phuyu_fin(); phuyu_historial.phuyu_creditos();
			}, function(){
				phuyu_sistema.phuyu_alerta("ERROR AL CUENTA POR COBRAR","ERROR DE RED","error");
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
		},
		phuyu_editar: function(){
			this.$http.post(url+phuyu_controller+"/editarcredito",{"codregistro":this.campos.codregistro}).then(function(data){
				var credito = eval(data.body.creditos[0]);
				this.campos.codpersona=credito.codpersona;
				this.campos.codempleado=credito.codempleado;
				this.campos.codlote=parseInt(credito.codlote);
				this.campos.nrotarjeta=credito.nrotarjeta;
				this.campos.fechacredito=credito.fechacredito;
				this.campos.fechainicio=credito.fechainicio;
				this.campos.nrodias=credito.nrodias;
				this.campos.nrocuotas=credito.nrocuotas;
				this.campos.codcreditoconcepto=credito.codcreditoconcepto;
				this.campos.importe=parseFloat(credito.importe).toFixed(2);
				this.campos.tasainteres=parseFloat(credito.tasainteres).toFixed(2);
				this.campos.interes=credito.interes;
				this.campos.total=credito.total;
				this.campos.totales = credito.total;
				this.campos.referencia = credito.observaciones;
				this.campos.codmovimiento = credito.codmovimiento;
				this.campos.codmoneda = credito.codmoneda;
				this.campos.tipocambio = credito.tipocambio;
				this.campos.creditoprogramado = credito.creditoprogramado;
				if(data.body.movimiento!=""){
					var movimiento = eval(data.body.movimiento[0]);
					this.campos.codtipopago = movimiento.codtipopago;
				}
				this.cuotas = data.body.cuotas;
				this.phuyu_cal();
				//this.phuyu_calcular()
			});
			
		}
	},
	created: function(){
		this.phuyu_editar();phuyu_sistema.phuyu_fin();
		phuyu_historial.referencia = $("#referencia").val()
	}
});