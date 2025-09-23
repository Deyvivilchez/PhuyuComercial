var phuyu_cobranza = new Vue({
	el: "#phuyu_cobranza",
	data: {
		campos:{
			"codpersona":phuyu_creditos.registro,"codlote":phuyu_creditos.codlote,"codmoneda":1,"tipocambio":0.00,"codconcepto":19,"rubro":$("#rubro").val(),"codcomprobantetipo":1,"codtipopago":1,"importe":0,"vuelto":0,
			"fechadocbanco":$("#fechadocbanco").val(),"nrodocbanco":"","total":0,"descripcion":"COBRO DE CUOTAS","fechamovimiento":$("#fechamovimiento").val(),
			"codctacte":0,"cobrado":phuyu_creditos.cobrado
		},
		estado:0, cuotas: [],totalcuotas: [], cuotascobrar: [],rubro : $("#rubro").val()
	},
	methods: {
		phuyu_tipocambio(){
			if (this.campos.codmoneda==1) {
				this.campos.tipocambio = 1;
			}else{
				this.campos.fechamovimiento = $("#fechamovimiento").val();
				this.$http.get(url+"caja/tipocambios/consulta/"+this.campos.fechamovimiento).then(function(data){
					this.campos.tipocambio = data.body;
				});
			}
		},
		buscar_ccte: function(){
			$("#modal_ccte").modal('show')
			$("#phuyu_tituloform").text("SELECCIONAR CUENTAS CORRIENTES DE LA EMPRESA"); phuyu_sistema.phuyu_loader("cuerpo",180);
			this.$http.post(url+"caja/ctasctes/buscar",{"codregistro":1}).then(function(data){
				$("#cuerpo").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("cuerpo");
			},function(){
				phuyu_sistema.phuyu_error(); 
			});
		},
		phuyu_addccte: function(ccte){
			var moneda = '';
			if(ccte.codmoneda==1){
				moneda = 'SOLES';
			}
			else if(ccte.codmoneda==2){
				moneda = 'DOLARES';
			}else{
				moneda = 'EUROS';
			}
			this.campos.codctacte = ccte.codctacte;
			this.campos.banco = ccte.abreviatura;
			this.campos.nroctacte = ccte.nroctacte;
			this.campos.moneda = moneda;
			this.campos.descripcioncci = ccte.descripcion;
			$("#modal_ccte").modal('hide');
		},
		phuyu_cuotas: function(){

			this.$http.get(url+phuyu_controller+"/cuotas/"+phuyu_creditos.registro+'/'+phuyu_creditos.codlote).then(function(data){
				if (data.body.cuotas=="") {
					this.estado = 1;
				}
				this.cuotas = data.body.cuotas;this.totalcuotas = data.body.totales; phuyu_sistema.phuyu_fin();
			}, function(){
				phuyu_sistema.phuyu_alerta("ERROR AL CARGAR CUOTAS","ERROR DE RED","error");
			});
		},
		phuyu_cobrar: function(index,cuota){
			if ($("#"+index).is(":checked")){
				$("#"+index).attr("disabled","true");
				this.cuotascobrar.push({
					"item":index,"codcredito":cuota.codcredito,"nrocuota":cuota.nrocuota,"total":parseFloat(cuota.total),
					"importe":parseFloat(cuota.saldo),"saldo":0.00,"cobrarconvertido":parseFloat(cuota.saldo)	,"cobrar":parseFloat(cuota.saldo),"cobrartem":parseFloat(cuota.saldo)
				});
				this.campos.total = Number(( (this.campos.total + parseFloat(cuota.saldo)) ).toFixed(1)); this.phuyu_vuelto();
			}
		},
		phuyu_anularcuota:function(index,cuota){
			$("#"+cuota.item).removeAttr("disabled"); $("#"+cuota.item).removeAttr("checked");

			this.campos.total = this.campos.total - cuota.cobrar;
			this.cuotascobrar.splice(index,1); this.phuyu_vuelto();
		},
		phuyu_calcular: function(cuota){
			this.campos.total = this.campos.total - cuota.cobrartem;
			cuota.saldo = parseFloat(cuota.importe - cuota.cobrar).toFixed(2); 
			cuota.cobrartem = cuota.cobrar;
			this.campos.total = this.campos.total + cuota.cobrartem; this.phuyu_vuelto();
		},
		phuyu_vuelto: function(){
			if (this.campos.codtipopago==1) {
				this.campos.vuelto = Number((this.campos.importe - this.campos.total).toFixed(2));
				if (this.campos.vuelto < 0) {
					this.campos.vuelto = 0; this.estado = 1;
				}else{
					this.estado = 0;
				}
			}else{
				this.estado = 0;
			}
		},

		phuyu_guardar: function(){
			if (this.cuotascobrar.length==0) {
				phuyu_sistema.phuyu_noti("DEBE SELECCIONAR MINIMO UNA CUOTA PARA GUARDAR EL PAGO","","error"); 
				return false;
			}

			if (this.campos.codtipopago==1) {
				if (this.campos.importe<this.campos.total) {
					phuyu_sistema.phuyu_noti("EL IMPORTE ENTREGADO","DEBE SER MAYOR O IGUAL AL TOTAL","error"); 
					return false;
				}
			}else{
				if(this.campos.codctacte==0){
					phuyu_sistema.phuyu_noti("ANTES DE GUARDAR EL PAGO","DEBE SELECCIONAR UNA CUENTA CORRIENTE DEL SOCIO","error"); 
					return false;
				}
				if(this.campos.importe!=this.campos.total){
					phuyu_sistema.phuyu_noti("EL IMPORTE DEBE SER S/. "+this.campos.total,"LOS IMPORTE NO COINCIDEN","error"); 
					return false;
				}
			}
			if(this.campos.tipocambio==""){
				phuyu_sistema.phuyu_noti("EL TIPO DE CAMBIO NO DEBE ESTAR VACÍO","CORRIGE POR FAVOR","error"); 
					return false;
			}
			this.campos.fechadocbanco = $("#fechadocbanco").val();
			this.campos.fechamovimiento = $("#fechamovimiento").val();
			
			this.estado = 1; phuyu_sistema.phuyu_inicio_guardar("GUARDANDO COBRO DEL CREDITO . . .");
			this.$http.post(url+phuyu_controller+"/pagar", {"campos":this.campos,"cuotas":this.cuotascobrar}).then(function(data){
				if (data.body=="e") {
					phuyu_sistema.phuyu_alerta("SU SESION DE USUARIO A TERMINADO","DEBE INICIAR SESION NUEVAMENTE","error");
				}else{
					if (data.body==1) {
						phuyu_sistema.phuyu_alerta("COBRANZA REGISTRADA","CUOTA DE CREDITO COBRADO EN EL SISTEMA","success");
					}else{
						phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR COBRANZA","ERROR DE RED","error");
					}
				}
				phuyu_sistema.phuyu_fin(); phuyu_sistema.phuyu_modulo();
			}, function(){
				phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR COBRANZA","ERROR DE RED","error"); phuyu_sistema.phuyu_fin();
			});
		},
		phuyu_cerrar: function(){
			phuyu_sistema.phuyu_modulo();
		}
	},
	created: function(){
		this.phuyu_cuotas();this.phuyu_tipocambio();
	}
});