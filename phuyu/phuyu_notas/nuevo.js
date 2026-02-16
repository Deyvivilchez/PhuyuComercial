var phuyu_operacion = new Vue({
	el: "#phuyu_operacion",
	data: {
		campos:{
			"codmotivonota":1,"codpersona":2,"codmovimientotipo":8,"codkardex_ref":0,"seriecomprobante":"","codcomprobantetipo_ref":0,"seriecomprobante_ref":"",
			"nrocomprobante_ref":"","descripcion":"","cliente":"","direccion":""
		},
		estado:0, cambio:0, kardex_id:0, series_ref:[], series:[], comprobantes:[], detalle: [], totales: {"valorventa":0.00,"igv":0.00,"importe":0.00},
	},
	methods: {
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
		phuyu_infocliente: function(){
			this.campos.codpersona = $("#codpersona").val();
			this.$http.get(url+"ventas/clientes/infocliente/"+this.campos.codpersona).then(function(data){
				if (this.campos.codpersona==2) {
					$("#cliente").removeAttr("readonly"); $("#direccion").removeAttr("readonly");
				}else{
					$("#cliente").attr("readonly","true"); $("#direccion").removeAttr("readonly");
				}
				this.campos.cliente = data.body[0].razonsocial;
				this.codtipodocumento = data.body[0].coddocumentotipo; 
				this.campos.direccion = data.body[0].direccion;

				this.phuyu_comprobantes();
			});
        },
		phuyu_comprobantes: function(){
			if (this.campos.codpersona!="" && this.campos.codcomprobantetipo_ref!="0" && this.campos.seriecomprobante_ref!="") {
				this.estado = 1;
				this.$http.get(url+phuyu_controller+"/comprobantes/"+this.campos.codpersona+"/"+this.campos.codcomprobantetipo_ref+"/"+
					this.campos.seriecomprobante_ref+"/"+$("#fechacomprobante_ref").val()).then(function(data){
					this.comprobantes = data.body.comprobantes; this.series = data.body.series; this.estado = 0;
				},function(){
					phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","danger");
				});
			}else{
				this.comprobantes = []; phuyu_sistema.phuyu_noti("LLENAR EL COMPROBANTE DE REFERENCIA Y LA SERIE", "PARA FILTRAR LOS COMPROBANTES","danger");
			}
		},
		phuyu_detalle: function(datos){
			$("#"+this.kardex_id).removeAttr('style');
			$("#"+datos.codkardex).removeAttr('style');
			$("#"+this.kardex_id).attr('style', 'background-color: #fff;color:#000 !important');
			this.kardex_id = datos.codkardex;
			$("#"+datos.codkardex).attr('style',"background-color: #13a89e;color:#fff !important");

			this.campos.codkardex_ref = datos.codkardex; this.campos.codcomprobantetipo_ref = datos.codcomprobantetipo;
			this.campos.seriecomprobante_ref = datos.seriecomprobante; this.campos.nrocomprobante_ref = datos.nrocomprobante;
			this.campos.cliente = datos.cliente; this.campos.direccion = datos.direccion;

			this.$http.get(url+phuyu_controller+"/detalle/"+datos.codkardex).then(function(data){
				var productos = eval(data.body.detalle);
				var filas = [];
				this.cambio = 0;
				$.each( productos, function( k, v ) {
			    	v.subtotal = (parseFloat(v.cantidad)*parseFloat(v.precio)).toFixed(4);
			    	v.valorventa = parseFloat(v.cantidad)*parseFloat(v.preciosinigv);
			    	if(v.cantidad!=v.cantidadoriginal){
			    		this.cambio = 1;
			    	}
					filas.push({
						"itemorigen":v.item,"codproducto":v.codproducto,"producto":v.producto,"codunidad":v.codunidad,"unidades": this.putunidades,
						"unidad":v.unidad,"cantidad":v.cantidad,"stock":v.stock,"control":v.controlstock,"precio":parseFloat(v.precio).toFixed(2),
						"preciorefunitario":v.precio,"subtotal":v.subtotal,"valorventa":v.valorventa,"codafectacionigv":v.codafectacionigv,"igv":v.igv,
						"preciosinigv" : v.preciosinigv,"stock":v.stock,"cantidadoriginal":v.cantidadoriginal
					});
					this.putunidades = [];
				});

				this.detalle = filas
				var datos = eval(data.body.totales);
				//this.totales.valorventa = datos[0]["valorventa"]; this.totales.igv = datos[0]["igv"]; this.totales.importe = datos[0]["importe"];
				this.phuyu_totales();
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","danger");
			});
		},
		phuyu_calcular: function(producto,tipo){
			producto.preciooriginal = producto.precio;
			producto.valorventa = Number((parseFloat(producto.cantidad) * parseFloat(producto.preciosinigv)).toFixed(2));
			producto.subtotal = Number((parseFloat(producto.cantidad) * parseFloat(producto.precio)).toFixed(2));
			
			producto.igv = Number((parseFloat(producto.subtotal) - parseFloat(producto.valorventa)).toFixed(2));
			
			this.phuyu_totales();
		},
		phuyu_quitardetalle: function(index, dato){
			this.detalle.splice(index,1); this.phuyu_calcular(dato,2);
		},
		phuyu_totales: function () {
			this.totales.valorventa = 0.00; this.totales.igv = 0.00; this.totales.subtotal = 0.00; this.totales.importe = 0.00;
			t = this;
			var detalle = this.detalle.filter(function(p){
				t.totales.igv = Number((t.totales.igv + parseFloat(p.igv)).toFixed(2));
				t.totales.valorventa = Number((t.totales.valorventa + parseFloat(p.valorventa) ).toFixed(2));
			});

			this.totales.importe = Number((this.totales.valorventa + this.totales.igv).toFixed(2));
			this.cambio = 1;
		},
		phuyu_guardar: function(){
			if (this.detalle.length==0) {
				phuyu_sistema.phuyu_noti("DEBE SELECCIONAR UNA VENTA","PARA REGISTRAR LA NOTA ELECTRONICA","danger"); 
				return false;
			}
			if((this.campos.codmotivonota==1 || this.campos.codmotivonota==2 || this.campos.codmotivonota==6) && this.cambio == 1){
				phuyu_sistema.phuyu_noti("USTED HIZO CAMBIOS EN EL DETALLE DE LA NOTA, ASI QUE NO PUEDE HACER LA NOTA DE CREDITO CON ESE MOTIVO","SELECCIONE OTRO MOTIVO POR FAVOR","danger"); 
				return false;
			}
			this.estado = 1; phuyu_sistema.phuyu_inicio_guardar("GUARDANDO NOTA ELECTRONICA . . .");
			this.$http.post(url+phuyu_controller+"/guardar", {"campos":this.campos,"detalle":this.detalle,"totales":this.totales}).then(function(data){
				if (data.body=="e") {
					phuyu_sistema.phuyu_alerta("SU SESION DE USUARIO A TERMINADO","DEBE INICIAR SESION NUEVAMENTE","danger");
				}else{
					if (data.body.estado==1) {
						swal({
							title: "DESEA IMPRIMIR LA NOTA ?",   
							text: "DESEA IMPRIMIR NOTA ELECTRONICA", 
							icon: "warning",
							dangerMode: true,
							buttons: ["CANCELAR", "SI, IMPRIMIR"],
						}).then((willDelete) => {
							if (willDelete){
								this.phuyu_imprimir(data.body.codkardex);
							}
						});
						phuyu_sistema.phuyu_noti("NOTA REGISTRADA CORRECTAMENTE","NOTA REGISTRADA EN EL SISTEMA","success");
					}else{
						phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR NOTA ELECTRONICA","ERROR DE RED","danger");
					}
				}
				phuyu_sistema.phuyu_fin(); phuyu_sistema.phuyu_modulo();
			}, function(){
				phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR NOTA ELECTRONICA","ERROR DE RED","danger"); phuyu_sistema.phuyu_fin();
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