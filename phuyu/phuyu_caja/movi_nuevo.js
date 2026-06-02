var phuyu_form = new Vue({
	el: "#phuyu_form",
	data: {estado: 0, transferencia: 0, campos: campos, movimientobanco:0, comprobantes:[], conceptos:[], tipopagos:[]},
	methods: {
		phuyu_infocliente: function(codpersona){
			this.campos.codpersona = codpersona;
		},
		phuyu_tipomovimiento : function(){
			if (this.campos.tipomovimiento!=undefined) {
				$("#fechadocbanco").val($("#fechadocbanco_ref").val()); this.campos.fechadocbanco = $("#fechadocbanco").val();
				this.estado = 1; this.campos.codcomprobantetipo = "";
				this.$http.get(url+phuyu_controller+"/tipomovimiento/"+this.campos.tipomovimiento).then(function(data){
					this.comprobantes = data.body.comprobantes; this.conceptos = data.body.conceptos; this.estado = 0;
				});
			}
		},
		phuyu_conceptos: function(){
			if (this.campos.codconcepto==25) {
				this.transferencia = 1;
			}else{
				this.transferencia = 0; this.campos.codcaja_ref = "";
			}
		},
		phuyu_tipopagos : function(){
			if (this.campos.codcomprobantetipo!=undefined) {
				this.estado = 1; this.campos.codtipopago = "";
				this.$http.get(url+phuyu_controller+"/tipopagos/"+this.campos.codcomprobantetipo).then(function(data){
					this.tipopagos = data.body.tipopagos; this.campos.seriecomprobante = data.body.serie; this.estado = 0;
				});
			}
		},
		phuyu_cajabanco: function(){
			if (this.campos.codtipopago==1) {
				this.movimientobanco = 0; $("#nrodocbanco").removeAttr("required");
			}else{
				this.movimientobanco = 1; $("#nrodocbanco").attr("required","true");
			}
		},
		phuyu_fechamovimiento: function(){
			this.campos.fechadocbanco = $("#fechadocbanco").val();
		},
		phuyu_guardar: function(){
			if(this.campos.codpersona==""){
				phuyu_sistema.phuyu_noti("SELECCIONE EL SOCIO DEL MOVIMIENTO","Antes de guardar el movimiento debe seleccionar al socio que está realizando dicho movimiento","warning");return;
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
				phuyu_datos.phuyu_datos(); this.phuyu_cerrar();
			}, function(){
				phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR AL REGISTRAR", "NO SE PUEDE GUARDAR EL MOVIMIENTO DE CAJA","error");
				this.estado= 0;
			});
		},
		phuyu_editarmovimiento: function(){
			this.estado = 1;
			this.$http.post(url+phuyu_controller+"/editarmovimiento",{"codregistro":phuyu_datos.registro}).then(function(data){
				if (data.body.editar!=1) { 
					$("#importe").attr("disabled","true");
				}

				var info = eval(data.body.info);
				this.$http.get(url+phuyu_controller+"/tipomovimiento/"+info[0]["tipomovimiento"]).then(function(data){
					this.comprobantes = data.body.comprobantes; this.conceptos = data.body.conceptos;
					this.campos.tipomovimiento = info[0]["tipomovimiento"]; this.campos.codcomprobantetipo = info[0]["codcomprobantetipo"];
					this.campos.codconcepto = info[0]["codconcepto"];

					this.campos.fechadocbanco = info[0]["fechadocbanco"]; this.campos.nrodocbanco = info[0]["nrodocbanco"];

					this.$http.get(url+phuyu_controller+"/tipopagos/"+info[0]["codcomprobantetipo"]).then(function(data){
						this.tipopagos = data.body.tipopagos; this.campos.seriecomprobante = data.body.serie; 
						this.campos.codtipopago = info[0]["codtipopago"]; 
						this.phuyu_cajabanco(); this.phuyu_conceptos(); this.estado = 0;

						$("#tipomovimiento").attr("disabled","true"); $("#codcomprobantetipo").attr("disabled","true"); 
					});
				});

				var socio = eval(data.body.socio);
				$("#codpersona").empty().html("<option value='"+socio[0]["codpersona"]+"'>"+socio[0]["razonsocial"]+"</option>");

				$(".selectpicker").selectpicker("refresh"); $(".filter-option").text(socio[0]["razonsocial"]); 
				$("#codpersona").val(socio[0]["codpersona"]); this.campos.codpersona = socio[0]["codpersona"];
			});
		},
		phuyu_cerrar: function(){
			$(".compose").slideToggle();
		}
	},
	created: function(){
		if (phuyu_datos.registro=="0") {
			this.phuyu_tipomovimiento();
		}else{
			this.phuyu_editarmovimiento();
		}
	}
});