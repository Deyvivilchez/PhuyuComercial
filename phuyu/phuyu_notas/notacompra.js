var phuyu_operacion = new Vue({
	el: "#phuyu_operacion",
	data: {
		campos:{
			"codmotivonota":1,"codpersona":2,"cliente":"PROVEEDORES VARIOS","codmovimientotipo":26,"codkardex_ref":0,"seriecomprobante":"","nrocomprobante":"","codcomprobantetipo_ref":0,"seriecomprobante_ref":"",
			"nrocomprobante_ref":"","descripcion":"","cliente":"","direccion":"","codmoneda":1, "tipocambio":0
		},
		estado:0, kardex_id:0, comprobantes:[], detalle: [], totales: {"valorventa":0.00,"igv":0.00,"importe":0.00},
	},
	methods: {
		phuyu_infocliente: function(){
			this.campos.codpersona = $("#codpersona").val();
			this.campos.cliente = $(".select2-selection__rendered").text();
        },
		phuyu_motivos: function(){
			// Motivos de las Notas de Credito //
		},
		phuyu_series: function(){
			if (this.campos.codcomprobantetipo_ref!=undefined) {
				this.estado = 1;
				this.$http.get(url+"caja/controlcajas/phuyu_seriescaja/"+this.campos.codcomprobantetipo_ref).then(function(data){
					this.series_ref = data.body.series; this.estado = 0;
				});
			}
		},
		phuyu_comprobantes: function(){
			if (this.campos.codpersona!="") {
				this.estado = 1;
				this.$http.get(url+phuyu_controller+"/comprobantes/"+this.campos.codpersona+"/"+$("#fechacomprobante_ref").val()).then(function(data){
					this.comprobantes = data.body.comprobantes; this.estado = 0;
				},function(){
					phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
				});
			}else{
				this.comprobantes = []; phuyu_sistema.phuyu_noti("SELECCIONAR PROVEEDOR DE LA COMPRA", "PARA FILTRAR LOS COMPROBANTES","error");
			}
		},
		phuyu_detalle: function(datos){
			if (datos.codmotivonota==0) {
				$("#"+this.kardex_id).css({"background-color":"#fff","color":"#000"}); this.kardex_id = datos.codkardex;
				$("#"+datos.codkardex).css({"background-color":"#13a89e","color":"#fff"});

				this.campos.codkardex_ref = datos.codkardex; this.campos.codcomprobantetipo_ref = datos.codcomprobantetipo;
				this.campos.seriecomprobante_ref = datos.seriecomprobante; this.campos.nrocomprobante_ref = datos.nrocomprobante;
				this.campos.cliente = datos.cliente; this.campos.direccion = datos.direccion; 
				this.campos.codmoneda = datos.codmoneda; this.campos.tipocambio = datos.tipocambio;

				this.$http.get(url+phuyu_controller+"/detalle/"+datos.codkardex).then(function(data){
					this.detalle = data.body.detalle; var datos = eval(data.body.totales);
					this.totales.valorventa = datos[0]["valorventa"]; this.totales.igv = datos[0]["igv"]; this.totales.importe = datos[0]["importe"];
				},function(){
					phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
				});
			}else{
				phuyu_sistema.phuyu_noti("NOTA DE CREDITO GENERADA CON EL MOTIVO: "+datos.motivo,"","error");
			}
		},
		
		phuyu_calcular: function(dato){
			this.totales.valorventa = Number((this.totales.valorventa - dato.valorventa ).toFixed(2));
			this.totales.igv = Number((this.totales.igv - dato.igv ).toFixed(2));
			this.totales.importe = Number((this.totales.importe - dato.subtotal ).toFixed(2));

			dato.subtotal = Number((dato.cantidad * dato.precio ).toFixed(2));
			if (dato.codafectacionigv=="10") {
				dato.igv = Number(( dato.subtotal - (dato.subtotal / 1.18) ).toFixed(2));
			}
			dato.valorventa = Number((dato.subtotal - dato.igv ).toFixed(2));
			
			this.totales.valorventa = Number((this.totales.valorventa + dato.valorventa ).toFixed(2));
			this.totales.igv = Number((this.totales.igv + dato.igv ).toFixed(2));
			this.totales.importe = Number((this.totales.importe + dato.subtotal ).toFixed(2));
		},
		phuyu_quitardetalle: function(index, dato){
			this.detalle.splice(index,1); this.phuyu_calcular(dato);
		},

		phuyu_guardar: function(){
			if (this.detalle.length==0) {
				phuyu_sistema.phuyu_noti("DEBE TENER MINIMO UN ITEM LA NOTA","PARA REGISTRAR LA NOTA ELECTRONICA","error"); 
				return false;
			}

			this.estado = 1; phuyu_sistema.phuyu_inicio_guardar("GUARDANDO NOTA ELECTRONICA . . .");
			this.$http.post(url+phuyu_controller+"/guardar", {"campos":this.campos,"detalle":this.detalle,"totales":this.totales}).then(function(data){
				if (data.body=="e") {
					phuyu_sistema.phuyu_alerta("SU SESION DE USUARIO A TERMINADO","DEBE INICIAR SESION NUEVAMENTE","error");
				}else{
					if (data.body.estado==1) {
						phuyu_sistema.phuyu_noti("NOTA REGISTRADA CORRECTAMENTE","NOTA REGISTRADA EN EL SISTEMA","success");
					}else{
						phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR NOTA ELECTRONICA","ERROR DE RED","error");
					}
				}
				phuyu_sistema.phuyu_fin(); phuyu_sistema.phuyu_modulo();
			}, function(){
				phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR NOTA ELECTRONICA","ERROR DE RED","error"); phuyu_sistema.phuyu_fin();
			});
		},
		phuyu_cerrar: function(){
			phuyu_sistema.phuyu_modulo();
		}
	},
	created: function(){
		phuyu_sistema.phuyu_fin();
	}
});