var phuyu_form = new Vue({
	el: "#phuyu_form",
	data: {estado: 0, campos: campos, caja:false, caja_alerta:false, almacen:false,almacen_alerta:false, nota:false,nota_alerta:false, editable_identidad:1},
	methods: {
		phuyu_tipocomprobante : function(){
			if(this.campos.codcomprobantetipo>=1 && this.campos.codcomprobantetipo<=2){
				if (this.campos.codsucursal!=undefined) {
					this.caja = true; this.caja_alerta = false;
					this.$http.get(url+"administracion/comprobantes/cajas/"+this.campos.codsucursal).then(function(data){
						$("#codcaja").empty().html(data.body); 
					});
				}
			}else{
				this.caja = false; this.caja_alerta = false; this.estado= 0;
			}

			if(this.campos.codcomprobantetipo>=3 && this.campos.codcomprobantetipo<=4){
				if (this.campos.codsucursal!=undefined) {
					this.almacen = true; this.almacen_alerta = false;
					this.$http.get(url+"administracion/comprobantes/almacenes/"+this.campos.codsucursal).then(function(data){
						$("#codalmacen").empty().html(data.body); 
					});
				}
			}else{
				this.almacen = false; this.almacen_alerta = false; this.estado= 0;
			}

			if(this.campos.codcomprobantetipo>=14 && this.campos.codcomprobantetipo<=15){
				if (this.campos.codsucursal!=undefined) {
					this.nota = true; this.nota_alerta = false;
					this.$http.get(url+"administracion/comprobantes/notas/"+this.campos.codsucursal).then(function(data){
						$("#codcomprobantetipo_ref").empty().html(data.body); 
					});
				}
			}else{
				this.nota = false; this.nota_alerta = false; this.estado= 0;
			}
		},
		phuyu_caja: function(){
			if (this.campos.codcomprobantetipo!=undefined && this.campos.codcaja!=undefined) {
				this.$http.get(url+"administracion/comprobantes/cajas_existe/"+this.campos.codcaja+"/"+this.campos.codcomprobantetipo).then(function(data){
					if(data.body==1){
						this.caja_alerta = true; this.estado= 1;
					}else{
						this.caja_alerta = false; this.estado= 0;
					}
				});
			}
		},
		phuyu_almacen: function(){
			if (this.campos.codcomprobantetipo!=undefined && this.campos.codalmacen!=undefined) {
				this.$http.get(url+"administracion/comprobantes/almacen_existe/"+this.campos.codalmacen+"/"+this.campos.codcomprobantetipo).then(function(data){
					if(data.body==1){
						this.almacen_alerta = true; this.estado= 1;
					}else{
						this.almacen_alerta = false; this.estado= 0;
					}
				});
			}
		},
		phuyu_notas: function(){
			if (this.campos.codcomprobantetipo!=undefined && this.campos.codcomprobantetipo_ref!=undefined) {
				this.$http.get(url+"administracion/comprobantes/notas_existe/"+this.campos.codcomprobantetipo_ref+"/"+this.campos.codcomprobantetipo).then(function(data){
					if(data.body==1){
						this.nota_alerta = true; this.estado= 1;
					}else{
						this.nota_alerta = false; this.estado= 0;
					}
				});
			}
		},

		phuyu_impresion: function(){
			if (this.campos.impresion==1) {
				this.campos.impresion = 0;
			}else{
				this.campos.impresion = 1;
			}
		},

		phuyu_editarcomprobante: function(){
			this.campos.codsucursal_editar = this.campos.codsucursal;
			this.campos.codcomprobantetipo_editar = this.campos.codcomprobantetipo;
			this.campos.seriecomprobante_editar = this.campos.seriecomprobante;
			$("#codsucursal").attr("disabled","true"); $("#codcomprobantetipo").attr("disabled","true");
			$("#codcaja").attr("disabled","true"); $("#codalmacen").attr("disabled","true");
			$("#codcomprobantetipo_ref").attr("disabled","true"); $("#seriecomprobante").attr("disabled","true");

			this.$http.get(url+phuyu_controller+"/validar_serie/"+phuyu_datos.registro).then(function(data){
				this.campos.seriecomprobante_editar = data.body.serie;
				this.editable_identidad = parseInt(data.body.editable_identidad);
				this.phuyu_tipocomprobante();
				if (data.body.editable_identidad==1) {
					$("#codsucursal").removeAttr("disabled"); $("#codcomprobantetipo").removeAttr("disabled");
					$("#codcaja").removeAttr("disabled"); $("#codalmacen").removeAttr("disabled");
					$("#codcomprobantetipo_ref").removeAttr("disabled"); $("#seriecomprobante").removeAttr("disabled");
				}
			});
		},
		phuyu_guardar: function(){
			if (this.campos.codsucursal=="" || this.campos.codcomprobantetipo=="" || this.campos.seriecomprobante=="" || this.campos.nroinicial==="" || this.campos.nrocorrelativo==="") {
				phuyu_sistema.phuyu_alerta("DATOS INCOMPLETOS", "VERIFIQUE SUCURSAL, TIPO, SERIE Y CORRELATIVOS","error");
				return;
			}

			this.estado= 1;const formulario = new FormData($("#formulario")[0]);
			formulario.set("codsucursal", this.campos.codsucursal);
			formulario.set("codcomprobantetipo", this.campos.codcomprobantetipo);
			formulario.set("seriecomprobante", this.campos.seriecomprobante);
			formulario.set("codsucursal_editar", this.campos.codsucursal_editar);
			formulario.set("codcomprobantetipo_editar", this.campos.codcomprobantetipo_editar);
			formulario.set("seriecomprobante_editar", this.campos.seriecomprobante_editar);
			formulario.set("codcaja", this.campos.codcaja || 0);
			formulario.set("codalmacen", this.campos.codalmacen || 0);
			formulario.set("codcomprobantetipo_ref", this.campos.codcomprobantetipo_ref || 0);
			this.$http.post(url+phuyu_controller+"/guardar", formulario).then(function(data){
				if (data.body==1) {
					if (this.campos.codregistro=="") {
						phuyu_sistema.phuyu_alerta("GUARDADO CORRECTAMENTE", "UN NUEVO REGISTRO EN EL SISTEMA","success");
					}else{
						phuyu_sistema.phuyu_alerta("EDITADO CORRECTAMENTE", "UN REGISTRO EDITADO EN EL SISTEMA","info");
					}
				}else if(data.body==2){
					phuyu_sistema.phuyu_alerta("SERIE YA REGISTRADA", "EL SISTEMA YA TIENE REGISTRADO ESTE TIPO DOCUMENTO CON ESTA SERIE","error");
					this.estado= 0;
				}else{
					phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR AL REGISTRAR", "NO SE PUEDE REGISTRAR","error");
					this.estado= 0;
				}
				if (data.body==1) {
					phuyu_datos.phuyu_opcion(); this.phuyu_cerrar();
				}
			}, function(){
				phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR AL REGISTRAR", "EL SISTEMA YA TIENE REGISTRADO ESTE TIPO DOCUMENTO CON ESTA SERIE","error");
				this.estado= 0;
			});
		},
		phuyu_cerrar: function(){
			$(".compose").slideToggle();
		}
	},
	created: function(){
		if (phuyu_datos.registro!="0") {
			//this.phuyu_editarcomprobante();
		}
	}
});
