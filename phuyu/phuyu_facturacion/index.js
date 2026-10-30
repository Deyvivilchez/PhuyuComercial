var phuyu_datos = new Vue({
	el: "#phuyu_datos",
	data: {
		sunat: {tipo:"01",serie:"",nrocomprobante:"", fechaemision:"", importe:"", tipo_periodo:"todos", fdesde:"", fhasta:""}, sunatrecepcion:[], sunat_ultima_consulta_auto: "",
		sunatpaginacion: {"total":0, "actual":1, "ultima":1, "limite":10, "desde":0, "hasta":0},
		resumen: {codresumentipo:"", periodo:"", nrocorrelativo:0},
		facturas:[], facturas_anuladas:[], resumenes_boletas:[], resumenes_info:[], facturas_datos: [], boletas_datos: [],
		tipo_reporte: "", comprobantes_lista: [], resumenes_lista: [], guias:[], notas_creditos:[]
	},
	methods: {
		phuyu_pintar_datos_sunat: function(datos){
			if (datos.tipo) {
				this.sunat.tipo = datos.tipo;
				$("#sunat_tipo").val(datos.tipo).trigger("change");
			}
			if (datos.fechaemision) {
				this.sunat.fechaemision = datos.fechaemision;
				$("#sunat_fechaemision").val(datos.fechaemision);
			}
			if (datos.importe) {
				this.sunat.importe = datos.importe;
				$("#sunat_importe").val(datos.importe);
			}
		},
		phuyu_comprobantes: function(){
			phuyu_sistema.phuyu_inicio();
			this.$http.get(url+phuyu_controller+"/comprobantes").then(function(data){
				this.facturas = data.body.facturas; 
				phuyu_sistema.phuyu_fin();
			}, function(){
				phuyu_sistema.phuyu_alerta("NO SE PUEDEN MOSTRAR LOS COMPROBANTES PENDIENTES","","error");
				phuyu_sistema.phuyu_fin();
			});
		},
		phuyu_notas: function(){
			this.$http.get(url+phuyu_controller+"/notas").then(function(data){
				this.notas_creditos = data.body.notas;
			}, function(){
				phuyu_sistema.phuyu_alerta("NO SE PUEDEN MOSTRAR LOS COMPROBANTES PENDIENTES","","error");
				phuyu_sistema.phuyu_fin();
			});
		},
		phuyu_guias: function(){
			this.$http.get(url+phuyu_controller+"/guias").then(function(data){
				this.guias = data.body.guias;
			}, function(){
				phuyu_sistema.phuyu_alerta("NO SE PUEDEN MOSTRAR LOS COMPROBANTES PENDIENTES","","error");
				phuyu_sistema.phuyu_fin();
			});
		},
		comprobantes_enviar: function(codkardex,codoficial){
			phuyu_sistema.phuyu_inicio_guardar("ENVIANDO EL COMPROBANTE A SUNAT . . ."); $("#"+codkardex).attr("disabled","true");

			this.$http.get(url+phuyu_controller+"/comprobantes_enviar/"+codkardex+"/"+codoficial).then(function(data){
				if (data.body=="e") {
					phuyu_sistema.phuyu_alerta("SESION DEL USUARIO TERMINADA","DEBE INICIAR SESION NUEVAMENTE","error");
				}else{
					phuyu_sistema.phuyu_noti("ATENCION USUARIO:",data.body.mensaje,data.body.alerta);
				}
				$("#"+codkardex).removeAttr("disabled");
				if(codoficial == '07'){
                    this.phuyu_notas();
				}else{
				    this.phuyu_comprobantes(); 
                }
				phuyu_sistema.phuyu_fin();
			}, function(){
				phuyu_sistema.phuyu_alerta("NO SE PUEDE ENVIAR EL COMPROBANTE","SIN CONEXION DE INTERNET","error");
				$("#"+codkardex).removeAttr("disabled"); phuyu_sistema.phuyu_fin(); 				 
			});
		},
		guias_enviar: function(codguiar,codoficial){
			phuyu_sistema.phuyu_inicio_guardar("ENVIANDO EL COMPROBANTE A SUNAT . . ."); $("#"+codguiar).attr("disabled","true");

			this.$http.get(url+phuyu_controller+"/guias_enviar/"+codguiar+"/"+codoficial).then(function(data){
				if (data.body=="e") {
					phuyu_sistema.phuyu_alerta("SESION DEL USUARIO TERMINADA","DEBE INICIAR SESION NUEVAMENTE","error");
				}else{
					phuyu_sistema.phuyu_noti("ATENCION USUARIO:",data.body.mensaje,data.body.alerta);
				}
				$("#"+codguiar).removeAttr("disabled"); this.phuyu_guias(); phuyu_sistema.phuyu_fin();
			}, function(){
				phuyu_sistema.phuyu_alerta("NO SE PUEDE ENVIAR EL COMPROBANTE","SIN CONEXION DE INTERNET","error");
				$("#"+codguiar).removeAttr("disabled"); phuyu_sistema.phuyu_fin(); 				 
			});
		},
		comprobantes_xml: function(codkardex,codoficial){
			window.open(url+phuyu_controller+"/comprobantes_xml/"+codkardex+"/"+codoficial,"_blank");
		},
		guias_xml: function(codguiar,codoficial){
			window.open(url+phuyu_controller+"/guias_xml/"+codguiar+"/"+codoficial,"_blank");
		},
		comprobantes_cdr: function(codkardex){
			window.open(url+phuyu_controller+"/comprobantes_cdr/"+codkardex,"_blank");
		},
		comprobantes_correo: function(data){
			this.$http.get(url+"ventas/clientes/correo/"+data.documento).then(function(correo){
				if (correo.body=="") {
					phuyu_sistema.phuyu_alerta("SIN CORREO ELECTRONICO: "+data.razonsocial,"REGISTRAR CORREO ELECTRONICO","error"); 
				}else{
					swal({
						title: "ENVIAR COMPROBANTE ELECTRONICO "+data.seriecomprobante+"-"+data.nrocomprobante+" AL CORREO "+correo.body,   
						text: "", 
						icon: "info",
						dangerMode: true,
						buttons: ["CANCELAR", "SI, ENVIAR CORREO"],
					}).then((willDelete) => {
						if (willDelete) {
							phuyu_sistema.phuyu_inicio_guardar("ENVIANDO EL COMPROBANTE AL CORREO . . .");
							this.$http.post(url+"ventas/clientes/enviar_correo",{"codkardex":data.codkardex,"email":correo.body}).then(function(info){
								if (info.body==1) {
									phuyu_sistema.phuyu_alerta("COMPROBANTE ELECTRONICO "+data.seriecomprobante+"-"+data.nrocomprobante+" ENVIANDO CORRECTAMENTE","","success");
								}else{
									phuyu_sistema.phuyu_alerta("LA CONFIGURACION DEL CORREO ES INCORRECTA","ERROR DE CONFIGURACION","error");
								}
								phuyu_sistema.phuyu_fin();
							}, function(){
								phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); phuyu_sistema.phuyu_fin();
							});
						}
					});
				}
			});
		},

		phuyu_resumenes: function(){
			phuyu_sistema.phuyu_inicio();
			this.$http.get(url+phuyu_controller+"/resumenes").then(function(data){
				this.facturas_anuladas = data.body.facturas_anuladas; 
				this.resumenes_boletas = data.body.resumenes_boletas;
				phuyu_sistema.phuyu_fin();
			}, function(){
				phuyu_sistema.phuyu_alerta("NO SE PUEDE MOSTRAR LOS RESUMENES PENDIENTES","","error");
				phuyu_sistema.phuyu_fin();
			});
		},
		resumenes_generar: function(codresumentipo){
			phuyu_sistema.phuyu_inicio_guardar("GENERANDO EL RESUMEN ELECTRONICO . . .");

			this.$http.get(url+phuyu_controller+"/resumenes_generar/"+codresumentipo+"/"+$("#fecha").val()).then(function(data){
				if (data.body=="e") {
					phuyu_sistema.phuyu_alerta("SESION DEL USUARIO TERMINADA","DEBE INICIAR SESION NUEVAMENTE","error");
				}else{
					var alerta = (data.body.estado==1) ? "success" : "error";
					phuyu_sistema.phuyu_noti("ATENCION USUARIO:",data.body.mensaje,alerta);
					phuyu_sistema.phuyu_fin(); this.phuyu_resumenes();
				}
			}, function(){
				phuyu_sistema.phuyu_alerta("NO SE PUEDE GENERAR EL RESUMEN","SIN CONEXION DE INTERNET","error");
				phuyu_sistema.phuyu_fin();
			});
		},
		resumenes_enviar: function(codresumentipo,periodo,nrocorrelativo){
			$("#"+periodo).attr("disabled","true"); phuyu_sistema.phuyu_inicio_guardar("ENVIANDO EL RESUMEN A SUNAT . . .");

			this.$http.get(url+phuyu_controller+"/resumenes_enviar/"+codresumentipo+"/"+periodo+"/"+nrocorrelativo).then(function(data){
				if (data.body=="e") {
					phuyu_sistema.phuyu_alerta("SESION DEL USUARIO TERMINADA","DEBE INICIAR SESION NUEVAMENTE","error");
				}else{
					var alerta = (data.body.estado==1) ? "success" : "error";
					phuyu_sistema.phuyu_noti("ATENCION USUARIO:",data.body.mensaje,alerta); 
					$("#"+periodo).removeAttr("disabled"); phuyu_sistema.phuyu_fin(); this.phuyu_resumenes();
				}
			}, function(){
				phuyu_sistema.phuyu_alerta("NO SE PUEDE ENVIAR EL RESUMEN","SIN CONEXION DE INTERNET","error");
				phuyu_sistema.phuyu_fin(); $("#"+periodo).removeAttr("disabled");
			});
		},
		resumenes_xml: function(codresumentipo,periodo,nrocorrelativo){
			window.open(url+phuyu_controller+"/resumenes_xml/"+codresumentipo+"/"+periodo+"/"+nrocorrelativo,"_blank");
		},
		resumenes_cdr: function(codresumentipo,periodo,nrocorrelativo){
			window.open(url+phuyu_controller+"/resumenes_cdr/"+codresumentipo+"/"+periodo+"/"+nrocorrelativo,"_blank");
		},
		resumenes_ver: function(codresumentipo,periodo,nrocorrelativo){
			this.resumen.codresumentipo = codresumentipo; 
			this.resumen.periodo = periodo; 
			this.resumen.nrocorrelativo = nrocorrelativo;

			this.$http.get(url+phuyu_controller+"/resumenes_ver/"+codresumentipo+"/"+periodo+"/"+nrocorrelativo).then(function(data){
				this.resumenes_info = data.body; $("#modal_resumenes").modal("show");
			});
		},
		resumenes_eliminar_kardex(data){
			this.$http.get(url+phuyu_controller+"/resumenes_eliminar_kardex/"+data.codkardex+"/"+this.resumen.codresumentipo+"/"+this.resumen.periodo+"/"+this.resumen.nrocorrelativo).then(function(data){
				this.resumenes_ver(this.resumen.codresumentipo,this.resumen.periodo,this.resumen.nrocorrelativo);
			});
		},
		resumenes_siguiente_correlativo: function(){
			swal({
				title: "SEGURO ACTUALIZAR RESUMEN ELECTRONICO ?",   
				text: "SE ACTUALIZARÁ AL SIGUIENTE CORRELATIVO", 
				icon: "warning",
				dangerMode: true,
				buttons: ["CANCELAR", "SI, ACTUALIZAR"],
			}).then((willDelete) => {
				if (willDelete) {
					this.$http.get(url+phuyu_controller+"/resumenes_siguiente_correlativo/"+this.resumen.codresumentipo+"/"+this.resumen.periodo+"/"+this.resumen.nrocorrelativo).then(function(data){
						if (data.body==1) {
							this.phuyu_resumenes();
							phuyu_sistema.phuyu_alerta("RESUMEN ACTUALIZADO CORRECTAMENTE","","success");
						}else{
							phuyu_sistema.phuyu_alerta("NO SE PUEDE ACTUALIZAR EL RESUMEN","","error");
						}
						$("#modal_resumenes").modal("hide");
					});
				}
			});
		},
		resumenes_actualizar: function(){
			swal({
				title: "SEGURO ACTUALIZAR RESUMEN ELECTRONICO ?",   
				text: "SE ACTUALIZARÁ COMO ENVIADO", 
				icon: "warning",
				dangerMode: true,
				buttons: ["CANCELAR", "SI, ACTUALIZAR"],
			}).then((willDelete) => {
				if (willDelete) {
					this.$http.get(url+phuyu_controller+"/resumenes_actualizar/"+this.resumen.codresumentipo+"/"+this.resumen.periodo+"/"+this.resumen.nrocorrelativo).then(function(data){
						if (data.body==1) {
							this.phuyu_resumenes();
							phuyu_sistema.phuyu_alerta("RESUMEN ACTUALIZADO CORRECTAMENTE","","success");
						}else{
							phuyu_sistema.phuyu_alerta("NO SE PUEDE ACTUALIZAR EL RESUMEN","","error");
						}
						$("#modal_resumenes").modal("hide");
					});
				}
			});
		},
		resumenes_quitar_ticket: function(){
			swal({
				title: "SEGURO QUITAR EL TICKET DEL RESUMEN ELECTRONICO ?",   
				text: "", 
				icon: "warning",
				dangerMode: true,
				buttons: ["CANCELAR", "SI, QUITAR"],
			}).then((willDelete) => {
				if (willDelete) {
					this.$http.get(url+phuyu_controller+"/resumenes_quitar_ticket/"+this.resumen.codresumentipo+"/"+this.resumen.periodo+"/"+this.resumen.nrocorrelativo).then(function(data){
						if (data.body==1) {
							this.phuyu_resumenes();
							phuyu_sistema.phuyu_alerta("TICKET ELIMINADO CORRECTAMENTE","","success");
						}else{
							phuyu_sistema.phuyu_alerta("NO SE PUEDE ELIMINAR TICKET","","error");
						}
						$("#modal_resumenes").modal("hide");
					});
				}
			});
		},
		resumenes_anular: function(codresumentipo,periodo,nrocorrelativo){
			swal({
				title: "SEGURO ELIMINAR RESUMEN ELECTRONICO ?",   
				text: "USTED ESTA POR ELIMINAR UN RESUMEN", 
				icon: "warning",
				dangerMode: true,
				buttons: ["CANCELAR", "SI, ELIMINAR"],
			}).then((willDelete) => {
				if (willDelete) {
					this.$http.get(url+phuyu_controller+"/resumenes_anular/"+codresumentipo+"/"+periodo+"/"+nrocorrelativo).then(function(data){
						if (data.body==1) {
							this.phuyu_resumenes();
							phuyu_sistema.phuyu_alerta("RESUMEN ANULADO CORRECTAMENTE","","success");
						}else{
							phuyu_sistema.phuyu_alerta("NO SE PUEDE ANULAR EL RESUMEN","","error");
						}
					});
				}
			});
		},

		phuyu_consultasunat: function(automatico){
			this.sunat.serie = this.sunat.serie.toUpperCase();
			var tipo_anterior = this.sunat.tipo;
			if (this.sunat.serie.charAt(0)=="B" && (this.sunat.tipo=="01" || this.sunat.tipo=="03")) {
				this.sunat.tipo = "03";
			}
			if (this.sunat.serie.charAt(0)=="F" && (this.sunat.tipo=="01" || this.sunat.tipo=="03")) {
				this.sunat.tipo = "01";
			}
			if (tipo_anterior!=this.sunat.tipo) {
				var tipo_texto = this.sunat.tipo=="03" ? "Boleta electronica" : "Factura electronica";
				phuyu_sistema.phuyu_noti("Tipo de comprobante corregido", "La serie "+this.sunat.serie+" corresponde a "+tipo_texto+".", "warning");
			}
			if (automatico===true) {
				var clave_auto = this.sunat.tipo+"-"+this.sunat.serie+"-"+this.sunat.nrocomprobante+"-"+this.sunat.fechaemision+"-"+this.sunat.importe;
				if (this.sunat_ultima_consulta_auto==clave_auto) {
					return;
				}
				this.sunat_ultima_consulta_auto = clave_auto;
			}
			phuyu_sistema.phuyu_inicio_guardar("CONSULTANDO COMPROBANTES EN SUNAT . . .");
			this.$http.post(url+phuyu_controller+"/phuyu_consultasunat",this.sunat).then(function(data){
				this.phuyu_pintar_datos_sunat(data.body);
				var respuesta = data.body.mensaje || "Sin respuesta de SUNAT";
				if (data.body.detalle) {
					respuesta += " | " + data.body.detalle;
				}
				$("#sunat_respuesta").empty().html(respuesta); phuyu_sistema.phuyu_fin();
			}, function(){
				phuyu_sistema.phuyu_fin();
			});
		},
		phuyu_buscar_comprobante_sunat: function(){
			var serie = this.sunat.serie.toUpperCase();
			this.sunat.serie = serie;
			var tipo_anterior = this.sunat.tipo;
			if (serie.charAt(0)=="B" && (this.sunat.tipo=="01" || this.sunat.tipo=="03")) {
				this.sunat.tipo = "03";
			}
			if (serie.charAt(0)=="F" && (this.sunat.tipo=="01" || this.sunat.tipo=="03")) {
				this.sunat.tipo = "01";
			}
			if (tipo_anterior!=this.sunat.tipo) {
				var tipo_texto = this.sunat.tipo=="03" ? "Boleta electronica" : "Factura electronica";
				phuyu_sistema.phuyu_noti("Tipo de comprobante corregido", "La serie "+serie+" corresponde a "+tipo_texto+".", "warning");
			}
			if (this.sunat.serie.length<4 || this.sunat.nrocomprobante=="") {
				return;
			}
			this.$http.post(url+phuyu_controller+"/phuyu_buscar_comprobante_sunat",this.sunat).then(function(data){
				if (data.body.estado==1) {
					var tipo_actual = this.sunat.tipo;
					this.phuyu_pintar_datos_sunat(data.body);
					if (serie.charAt(0)=="B" && (this.sunat.tipo=="01" || this.sunat.tipo=="03")) {
						this.sunat.tipo = "03";
						$("#sunat_tipo").val("03").trigger("change");
					}
					if (serie.charAt(0)=="F" && (this.sunat.tipo=="01" || this.sunat.tipo=="03")) {
						this.sunat.tipo = "01";
						$("#sunat_tipo").val("01").trigger("change");
					}
					if (tipo_actual!=this.sunat.tipo) {
						var tipo_base_texto = this.sunat.tipo=="03" ? "Boleta electronica" : (this.sunat.tipo=="07" ? "Nota de credito electronica" : "Factura electronica");
						phuyu_sistema.phuyu_noti("Tipo de comprobante corregido", "Segun el sistema, este comprobante corresponde a "+tipo_base_texto+".", "warning");
					}
					$("#sunat_respuesta").empty().html("Datos encontrados en el sistema: "+data.body.fechaemision+" | S/ "+data.body.importe+". Presiona Consultar este CPE para validar en SUNAT.");
				}
			});
		},

		phuyu_consultas: function(){
			$("#modal_consultas").modal("show");
			this.$http.post(url+phuyu_controller+"/phuyu_datos_cpe").then(function(data){
				this.facturas_datos = data.body.facturas; this.boletas_datos = data.body.boletas;
			});
		},
		phuyu_reportes_cpe: function(phuyu_url,tipo_reporte){
			phuyu_sistema.phuyu_inicio(); this.tipo_reporte = tipo_reporte;
			this.$http.get(url+phuyu_controller+"/"+phuyu_url+"/"+$("#fdesde").val()+"/"+$("#fhasta").val()).then(function(data){
				if (tipo_reporte=="comprobantes") {
					this.comprobantes_lista = data.body;
				}else{
					this.resumenes_lista = data.body;
				}
				phuyu_sistema.phuyu_fin();
			});
		},
		consulta_cdr: function (ticket) {
			if (ticket=="" || ticket==null) {
				phuyu_sistema.phuyu_noti("EL COMPROBANTE NO TIENE NRO DE TICKET","AUN NO ESTA ENVIADO A LA SUNAT","error");
			}else{
				window.open(url+phuyu_controller+"/consulta_cdr/"+ticket,"_blank");
			}
		},

		sunat_recepcion: function(){
			this.sunat_recepcion_pagina(1);
		},
		sunat_recepcion_pagina: function(pagina){
			pagina = parseInt(pagina);
			if (pagina < 1) {
				pagina = 1;
			}
			if (this.sunatpaginacion.ultima && pagina > this.sunatpaginacion.ultima) {
				pagina = this.sunatpaginacion.ultima;
			}
			phuyu_sistema.phuyu_inicio_guardar("CONSULTANDO COMPROBANTES EN SUNAT . . .");
			this.sunat.fdesde = $("#fecha_desde").val(); this.sunat.fhasta = $("#fecha_hasta").val();
			this.sunat.tipo_periodo = $("#sunat_tipo_periodo").val();
			this.sunat.pagina = pagina;
			this.sunat.limite = this.sunatpaginacion.limite;
			this.$http.post(url+phuyu_controller+"/phuyu_bloquesunat",this.sunat).then(function(data){
				var lista = data.body.lista || [];
				for (var i = 0; i < lista.length; i++) {
					if (!lista[i].descripcion) {
						lista[i].descripcion = "SIN RESPUESTA DE SUNAT";
					}
					if (!lista[i].mensaje_sunat) {
						lista[i].mensaje_sunat = lista[i].descripcion;
					}
					if (!lista[i].nivel_sunat) {
						lista[i].nivel_sunat = "warning";
					}
					if (lista[i].estado_sunat_directo==null) {
						lista[i].estado_sunat_directo = 0;
					}
				}
				this.sunatrecepcion = lista;
				this.sunatpaginacion = data.body.paginacion || this.sunatpaginacion;
				$("#phuyu_infosunat").modal("show"); phuyu_sistema.phuyu_fin();
			}, function(){
				phuyu_sistema.phuyu_fin();
			});
		},
		sunat_automatico: function(){
			phuyu_sistema.phuyu_alerta("ALERTA SUNAT","El sistema no puede responder su solicitud. Intente nuevamente o comuniquese con su Administrador","error");
		},
		sunat_quitar_icbper: function(){
			swal({
                title: "CONFIRMAR CLAVE DE ADMINISTRADOR",   
                text: "", 
                icon: "warning",
                dangerMode: true,
                buttons: ["CANCELAR", "SI, CONFIRMAR"],
                content: {
				    element: "input",
				    attributes: {
				      	placeholder: "INGRESAR CLAVE DE ADMINISTRADOR DEL SISTEMA",
				      	type: "text",
				    },
				},
            }).then((willDelete) => {
                if (willDelete){
                	if ($(".swal-content__input").val()=="phuyuperu") {
                		phuyu_sistema.phuyu_inicio_guardar("REGULARIZANDO . . .");
						this.$http.post(url+phuyu_controller+"/kardex_sinicbper").then(function(data){
							phuyu_sistema.phuyu_alerta("REGULARIZADO CORRECTAMENTE",data.body,"success"); phuyu_sistema.phuyu_fin();
						}, function(){
							phuyu_sistema.phuyu_fin();
						});
                	}else{
                		phuyu_sistema.phuyu_alerta("LO SENTIMOS LA CLAVE INDICADA NO ES LA CORRECTA", "","error");
                	}
                }
            });
		}
	},
	created: function(){
		phuyu_sistema.phuyu_fin(); this.phuyu_comprobantes(); this.phuyu_resumenes();this.phuyu_guias();this.phuyu_notas();
	}
});
