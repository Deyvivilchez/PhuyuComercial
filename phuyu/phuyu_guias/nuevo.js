var phuyu_operacion = new Vue({
	el: "#phuyu_operacion",
	data: {
		estado:0, codigobarra: "",rubro:0, series:[], detalle:[],codkardex:[],detallecomprobante:[], putunidades:[],
		campos:{
			codguiar:0, codpersona:0, codcomprobantetipo:16,seriecomprobante:$("#serie").val(), nro:"",codmotivotraslado:'',codmodalidadtraslado:'',
			fechaguia:$("#fechaguia").val(), fechatraslado:$("#fechatraslado").val(), descripcion:"REGISTRO POR GUIA DE REMISION", destinatario:"", direccion:"-",descripcionmotivo:"",
			codempleado:0, codmoneda:1, tipocambio:0.00, codcentrocosto:0, nroplaca:"", retirar:true, almacenpartida:$("#almacen_principal").val(),
			almacendestino: $("#almacen_llegada").val(),codunidad:'',peso:0,nropaquetes:0,observaciones:"",codubigeopartida:0,codubigeollegada:0,
			coddocumentotipotransportista:0,documentotransportista:'',razonsocialtransportista:'',coddocumentotipoconductor:0,nrocontenedor:1,constancia:'',
			documentoconductor:'',razonsocialconductor:'',codmovimientotipo:0,marca:'',licenciaconductor:'',codremitente,coddocumentotiporemitente:0, documentoremitente:'',remitente:''
		},
		item:{
			producto:"", unidad:"", cantidad:0, pesoitem:0, descripcion:""
		}
	},
	methods: {

		/* FUNCIONES GENERALES DE LA VENTA */

		phuyu_venta: function(){
			swal({
				title: "SEGURO REGISTRAR NUEVA GUIA DE REMISION?",   
				text: "LOS CAMPOS SE QUEDARAN VACIOS ", 
				icon: "warning",
				dangerMode: true,
				buttons: ["CANCELAR", "SI, NUEVA GUIA"],
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
		phuyu_atras: function(){
			phuyu_sistema.phuyu_modulo();
		},
		phuyu_addcliente: function(){
			$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario",180); 
			this.$http.post(url+"ventas/clientes/nuevo_1").then(function(data){
				$("#phuyu_formulario").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			},function(){ 
				phuyu_sistema.phuyu_error_operacion(); 
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			});
		},
		phuyu_infodestinatario: function(codpersona,destinatario,tipo){
			this.campos.codpersona = codpersona;
			this.campos.destinatario = destinatario;
			this.codtipodocumento = tipo;
        },
        phuyu_inforemitente: function(codpersona,remitente,tipo,documento){
			this.campos.codremitente = codpersona;
			this.campos.remitente = remitente;
			this.campos.coddocumentotiporemitente = tipo;
            this.campos.documentoremitente = documento;
        },
        phuyu_infotransportista: function(codpersona,transportista,tipo,documento){
			this.campos.codtransportista = codpersona;
			this.campos.razonsocialtransportista = transportista;
			this.campos.coddocumentotipotransportista = tipo;
            this.campos.documentotransportista = documento;
        },
        phuyu_infoconductor: function(codpersona,conductor,licenciadeconducir,documento,coddocumentotipo){
			this.campos.codconductor = codpersona;
			this.campos.razonsocialconductor = conductor;
			this.campos.coddocumentotipoconductor = coddocumentotipo;
			this.campos.documentoconductor = documento;
			this.campos.licenciaconductor = licenciadeconducir
        },
        phuyu_infovehiculo: function(codvehiculo,placa){
			this.campos.codvehiculo = codvehiculo;
			this.campos.nroplaca = placa;
        },

		/* DETALLE DE LA VENTA Y TOTALES */

		phuyu_codigobarra: function(){
			if (this.codigobarra!="") {
				this.$http.get(url+"almacen/productos/buscar_codigobarra/"+this.codigobarra).then(function(data){
					if (data.body.cantidad==0) {
						phuyu_sistema.phuyu_alerta("NO EXISTE CODIGO DE BARRA", "REGISTRA EL CODIGO DE BARRA", "error");
					}else{
						if (data.body.cantidad==1) {
							this.phuyu_additem(data.body.info[0],data.body.precio); this.codigobarra = "";
						}else{
							phuyu_sistema.phuyu_alerta("EL CODIGO DE BARRA EXISTE EN MÁS DE UN PRODUCTO", "REGISTRADO MAS DE UNA VEZ", "error");
						}
					}
				});
			}
		},
		phuyu_item: function(){
			$(".compose").slideToggle(); $("#phuyu_tituloform").text("BUSCAR PRODUCTO"); 
			phuyu_sistema.phuyu_loader("phuyu_formulario",180); 

			this.$http.post(url+"almacen/productos/buscar/ventas").then(function(data){
				$("#phuyu_formulario").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			},function(){
				phuyu_sistema.phuyu_error(); 
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			});
		},
		phuyu_additem: function(producto,precio){
			var existe_item = [];
			if ($("#itemrepetir").val()==0) {
				var existe_item = this.detalle.filter(function(p){
				    if(p.codproducto == producto.codproducto && p.codunidad == producto.codunidad ){
				    	p.cantidad = p.cantidad + 1; return p;
				    };
				});
			}

		    if (existe_item.length==0 || $("#itemrepetir").val()==1) {
                var unidades = []; var factores = []; var logo = []; arreglo = [];
		    	unidades = (producto.unidades).split(";");

		    	for (var i = 0; i < unidades.length; i++) {
                    factores = (unidades[i]).split("|");
		    		logo = {descripcion:factores[1],codunidad:factores[0],factor:factores[8]};
		    		this.putunidades.push(logo)
		    		if(factores[8]==1){
		    			producto.codunidad = factores[0];
		    			producto.unidad = factores[1];
		    		}
		    	}
				this.detalle.push({
					codproducto: producto.codproducto, producto: producto.descripcion, codunidad: producto.codunidad,unidades: this.putunidades,
					unidad: producto.unidad, cantidad: 1, pesoitem: 0, descripcion:""
				});

				this.putunidades = [];
		    }
		},
		phuyu_deleteitem: function(index,item){
			var anterior = this.detalle
            var itemanterior = item.codkardex
            this.detalle.splice(index,1);
            if(this.detalle.findIndex( x => x.codkardex == itemanterior) == -1){
            	var i = this.codkardex.indexOf(itemanterior);
 
			    if ( i !== -1 ) {
			        this.codkardex.splice( i, 1 );
			    }
            	this.detallecomprobante = [];
            	for (var i = 0; i < this.codkardex.length; i++) {
            		this.$http.get(url+"ventas/ventas/buscarventa/"+this.codkardex[i]).then(function(data){
            			var venta = data.body[0]
                        this.detallecomprobante.push({codcomprobantetipo:venta.codcomprobantetipo,tipo:venta.tipo,seriecomprobante:venta.seriecomprobante,nrocomprobante:venta.nrocomprobante,codkardex:venta.codkardex})
            		})
            	}
            }
		},
		phuyu_itemdetalle: function(index,producto){
			this.item = producto; $("#modal_itemdetalle").modal({backdrop: 'static', keyboard: false});
		},
        phuyu_deleteitemcomprobante: function(index,comprobante){
			this.detallecomprobante.splice(index,1);
            var i = this.codkardex.indexOf(comprobante.codkardex);
 
		    if ( i !== -1 ) {
		        this.codkardex.splice( i, 1 );
		    }
            if(this.codkardex.length > 0){
            	this.detalle = [];
			    for (var cont = 0; cont < this.codkardex.length; cont++) {
			    	this.$http.get(url+"ventas/ventas/buscarproductos/"+this.codkardex[cont]).then(function(data){
						if (data.body.length==0) {
							phuyu_sistema.phuyu_alerta("NO EXISTE EL DETALLE DE LA VENTA", "CORREGIR POR FAVOR", "error");return;
						}else{
							var datos = data.body
							var filas = this.detalle;
							$.each( datos, function( k, v ) {
							    var cantidad_faltante =  parseFloat(v.cantidad) - parseFloat(v.cantidadguia);

	                             filas.push({codcomprobantetipo:comprobante.codcomprobantetipo,seriecomprobante:comprobante.seriecomprobante,nrocomprobante:comprobante.nrocomprobante,
	                             	codkardex:v.codkardex,codproducto: v.codproducto, producto: v.descripcion, codunidad: v.codunidad,
						unidad: v.unidad, cantidad: cantidad_faltante,pesoitem:0, descripcion:v.descripcion, itemkardex:v.item})
							});

							this.detalle = filas
						}
					});
			    }
			}else{
				this.detalle = [];
			}
		},
		/* DATOS GENERALES DE LA VENTA */

		phuyu_guardar: function(){
			if(this.campos.codubigeopartida == 0){
                phuyu_sistema.phuyu_noti("DEBE ELEGIR EL UBIGEO DE PARTIDA", "PARA GUARDAR LA GUIA DE REMISION","error"); return false;
			}
			if(this.campos.codubigeollegada == 0){
                phuyu_sistema.phuyu_noti("DEBE ELEGIR EL UBIGEO DE LLEGADA", "PARA GUARDAR LA GUIA DE REMISION","error"); return false;
			}
			if (this.detalle.length==0) {
				phuyu_sistema.phuyu_noti("REGISTRAR UN PRODUCTO EN EL DETALLE", "REGISTRAR ITEM PARA LA GUIA DE REMISION","error"); return false;
			}
			if(this.campos.codpersona==''){
				phuyu_sistema.phuyu_noti("SELECCIONAR DESTINATARIO", "PARA GUARDAR LA GUIA DE REMISION","error"); return false;
			}
			if(this.campos.codmotivotraslado==2){
				if(this.campos.codremitente=='' || this.campos.coddocumentotiporemitente==0){
					phuyu_sistema.phuyu_noti("SELECCIONAR REMITENTE", "PARA GUARDAR LA GUIA DE REMISION","error"); return false;
				}
			}
			if(this.campos.codtransportista=='' || this.campos.coddocumentotipotransportista==0){
				phuyu_sistema.phuyu_noti("SELECCIONAR TRANSPORTISTA", "PARA GUARDAR LA GUIA DE REMISION","error"); return false;
			}
			if(this.campos.codconductor=='' || this.campos.coddocumentotipoconductor==0){
				phuyu_sistema.phuyu_noti("SELECCIONAR CONDUCTOR", "PARA GUARDAR LA GUIA DE REMISION","error"); return false;
			}
			if(this.campos.codvehiculo=='' || this.campos.nroplaca==''){
				phuyu_sistema.phuyu_noti("SELECCIONAR VEHICULO", "PARA GUARDAR LA GUIA DE REMISION","error"); return false;
			}
			
			this.campos.fechacomprobante = $("#fechacomprobante").val();
			this.campos.fechakardex = $("#fechakardex").val();
			
			this.phuyu_pagar()
		},

		/* PAGO DE LA VENTA */

		phuyu_series: function(){
			if (this.campos.codcomprobantetipo!=undefined) {
				this.estado = 1;
				this.$http.get(url+"caja/controlcajas/phuyu_seriescaja/"+this.campos.codcomprobantetipo).then(function(data){
					this.series = data.body.series; this.estado = 0;
					// this.campos.seriecomprobante = $("#serie").val(); this.phuyu_correlativo();
					this.campos.seriecomprobante = data.body.serie; this.phuyu_correlativo();
				});

				if (this.campos.codcomprobantetipo==10) {
					this.$http.get(url+"ventas/clientes/infocliente/"+this.campos.codpersona).then(function(data){
						this.codtipodocumento = data.body[0].coddocumentotipo;
					});
				}
			}
		},
		phuyu_correlativo: function(){
			if (this.campos.codcomprobantetipo!=undefined) {
				if (this.campos.seriecomprobante!="") {
					this.$http.get(url+"caja/controlcajas/phuyu_correlativo/"+this.campos.codcomprobantetipo+"/"+this.campos.seriecomprobante).then(function(data){
						this.campos.nro = data.body;
					});
				}
			}
			this.validar_general()
		},
		phuyu_pagar: function(){
			
			this.estado = 1; $("#modal_pago").modal("hide"); phuyu_sistema.phuyu_inicio_guardar("GUARDANDO GUIA . . .");
			this.$http.post(url+phuyu_controller+"/guardar", {"codkardex":this.codkardex,"detallecomprobante":this.detallecomprobante,"campos":this.campos,"detalle":this.detalle}).then(function(data){
				if (data.body=="e") {
					phuyu_sistema.phuyu_alerta("SESION DEL USUARIO TERMINADA","DEBE INICIAR SESION NUEVAMENTE","error");
				}else{
					if (data.body.estado==1) {
						swal({
							title: "DESEA IMPRIMIR LA GUIA ?",   
							text: "DESEA IMPRIMIR EL COMPROBANTE REGISTRADO", 
							icon: "warning",
							dangerMode: true,
							buttons: ["CANCELAR", "SI, IMPRIMIR"],
						}).then((willDelete) => {
							if (willDelete){
								this.phuyu_imprimir(data.body.codguia);
							}
						});
						phuyu_sistema.phuyu_noti("GUIA DE REMISION REGISTRADA CORRECTAMENTE","GUIA REGISTRADA EN EL SISTEMA","success");
					}else{
						phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR GUIA DE REMISION","ERROR DE RED","error");
					}
				}
				phuyu_sistema.phuyu_fin(); this.phuyu_nueva_venta();
			}, function(){
				phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR GUIA DE REMISION","ERROR DE RED","error");
				phuyu_sistema.phuyu_fin(); this.phuyu_nueva_venta();
			});
		},
		phuyu_imprimir: function(codkardex){
			if ($("#formato").val()=="ticket") {
				window.open(url+"facturacion/formato/ticket/"+codkardex,"_blank");
			}else{
				var phuyu_url = url+"facturacion/formato/formato_guia/"+codkardex;
				//$("#phuyu_pdf").attr("src",phuyu_url); $("#modal_reportes").modal("show");
				window.open(phuyu_url,"_blank");
			}

			/* if ($("#phuyu_formato").val()==0) {
				var phuyu_url = url+"facturacion/formato/a4/"+codkardex;
            	$("#phuyu_pdf").attr("src",phuyu_url); $("#modal_reportes").modal("show");
			}else{
				if ($("#phuyu_formato").val()==1) {
					var phuyu_url = url+"facturacion/formato/a5/"+codkardex;
            		$("#phuyu_pdf").attr("src",phuyu_url); $("#modal_reportes").modal("show");
				}else{
					window.open(url+"facturacion/formato/ticket/"+codkardex,"_blank");
				}
			} */
        },
        phuyu_prov_part: function(pro){
			if ($("#dep_par").val()!=undefined) {
				this.$http.get(url+"ventas/clientes/provincias/"+$("#dep_par").val()).then(function(data){
					$("#"+pro).empty().html(data.body); $("#codubigeo").empty().html('<option value="">SELECCIONE</option>');
				});
			}
		},
		phuyu_dist_part: function(dis){
			if ($("#pro_par").val()!=undefined) {
				this.$http.get(url+"ventas/clientes/distritos/"+$("#dep_par").val()+"/"+$("#pro_par").val()).then(function(data){
					$("#"+dis).empty().html(data.body);
				});
			}
		},
		aceptar_ubigeo_partida: function(){
            if ($("#dep_par").val()=='') {$("#dep_par").focus();return;}
            if ($("#pro_par").val()=='') {$("#pro_par").focus();return;}
            if ($("#dis_par").val()=='') {$("#dis_par").focus();return;}

            var deparpar = $("#dep_par option:selected").text()
            var propar = $("#pro_par option:selected").text()
            var dispar = $("#dis_par option:selected").text()

            this.campos.codubigeopartida = $("#dis_par").val()
            $("#ubigeopartida").val(deparpar+', '+propar+', '+dispar)
            $("#modal-ubigeo-partida").modal('hide')
            this.validar_envio()
		},
        phuyu_bsubigeo: function (){
            $("#modal-ubigeo-partida").modal('show')
        },
		phuyu_prov_lleg: function(pro){
			if ($("#dep_lle").val()!=undefined) {
				this.$http.get(url+"ventas/clientes/provincias/"+$("#dep_lle").val()).then(function(data){
					$("#"+pro).empty().html(data.body); $("#codubigeo").empty().html('<option value="">SELECCIONE</option>');
				});
			}
		},
		phuyu_dist_lleg: function(dis){
			if ($("#pro_lle").val()!=undefined) {
				this.$http.get(url+"ventas/clientes/distritos/"+$("#dep_lle").val()+"/"+$("#pro_lle").val()).then(function(data){
					$("#"+dis).empty().html(data.body);
				});
			}
		},
		aceptar_ubigeo_llegada: function(){
            if ($("#dep_lle").val()=='') {$("#dep_lle").focus();return;}
            if ($("#pro_lle").val()=='') {$("#pro_lle").focus();return;}
            if ($("#dis_lle").val()=='') {$("#dis_lle").focus();return;}

            var deparpar = $("#dep_lle option:selected").text()
            var propar = $("#pro_lle option:selected").text()
            var dispar = $("#dis_lle option:selected").text()
            this.campos.codubigeollegada = $("#dis_lle").val()
            $("#ubigeollegada").val(deparpar+', '+propar+', '+dispar)
            $("#modal-ubigeo-llegada").modal('hide')
            this.validar_envio()
		},
        phuyu_bsubigeollegada: function (){
            $("#modal-ubigeo-llegada").modal('show')
        },
        motivotraslado: function(){
        	if($("#motivotraslado").val() == 4){
        		$(".almacenes").show()
        		$("#almacen_principal").addClass('requeridogeneral')
        		$("#almacen_llegada").addClass('requeridogeneral')
        		$(".row_remitente").hide()
        		$("#codremitente").removeAttr('required')
        	}else{
        		if($("#motivotraslado").val() == 2){
        			$(".row_remitente").show()
        		    $("#codremitente").attr('required','required')
        		}else{
        			$(".row_remitente").hide()
        			$("#codremitente").removeAttr('required')
        		}
        		$(".almacenes").hide()
        		$("#almacen_principal").removeClass('requeridogeneral')
        		$("#almacen_llegada").removeClass('requeridogeneral')
        	}
        	this.otras_opciones()
        },
        otras_opciones: function(){
        	if($("#motivotraslado").val() != ''){
	            if($("#motivotraslado").val() == 1){
	               $(".btnventa").show()
	               $(".btncompra,.btnproducto").hide()
	            }else{
	            	$(".btnproducto").show()
	               $(".btnventa,.btncompra").hide()
	            }
	        }else{
	        	$(".btncompra,.btnproducto,.btnventa").hide()
	        }

            this.detalle = [];
            this.detallecomprobante = [];
            this.codkardex = [];
        },
		phuyu_itemventa: function(){
			$(".compose").slideToggle();$("#phuyu_tituloform").text("BUSCAR VENTA");
			phuyu_sistema.phuyu_loader("phuyu_formulario",180); 
			this.$http.post(url+"ventas/ventas/buscar").then(function(data){
				$("#phuyu_formulario").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			},function(){ 
				phuyu_sistema.phuyu_error_operacion(); 
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			});
		},
		phuyu_addventa: function(venta){
            var existe_venta = [];
            if($.inArray(venta.codkardex, this.codkardex) != -1){
            	phuyu_sistema.phuyu_noti("CUIDADO","LA VENTA YA FUE SELECCIONADO","error"); return false;
            }
            this.codkardex.push(venta.codkardex);
            this.campos.destinatario = venta.cliente
            $("#codpersona").empty().html("<option value='"+venta.codpersona+"'>"+venta.cliente+"</option>");            
			$("#codpersona").val(venta.codpersona);
            this.campos.codpersona = venta.codpersona;
            $("#select2-codpersona-container").empty().text(venta.cliente);
			this.campos.direccionpartida = venta.direccionpartida;
			$("#ubigeopartida").val(venta.ubigeodescripcion);
			this.campos.codubigeopartida = venta.ubigeopartida;
			var tmno = (venta.direcciondestino).length
			console.log(tmno)
			if(tmno>100){
				llegadadir = (venta.direcciondestino).substring(0,100);
			}else{
				llegadadir = venta.direcciondestino
			}

			this.campos.direccionllegada = llegadadir;
			$("#ubigeollegada").val(venta.ubigeodescripciondestino);
			this.campos.codubigeollegada = venta.ubigeodestino;
			
            this.detallecomprobante.push({codcomprobantetipo:venta.codcomprobantetipo,tipo:venta.tipo,seriecomprobante:venta.seriecomprobante,nrocomprobante:venta.nrocomprobante,codkardex:venta.codkardex})
            if(!$.isEmptyObject(this.codkardex)){
            	this.$http.get(url+"ventas/ventas/buscarproductos/"+venta.codkardex).then(function(data){
            		//console.log(data.body)
					if (data.body.length==0) {
						phuyu_sistema.phuyu_alerta("NO EXISTE EL DETALLE DE LA VENTA", "CORREGIR POR FAVOR", "error");return;
					}else{
						var datos = data.body
						var filas = this.detalle;
						$.each( datos, function( k, v ) {
							var unidades = []; var factores = []; var logo = []; arreglo = [];
					    	unidades = (v.unidades).split(";"); var funidades = [];

					    	for (var i = 0; i < unidades.length; i++) {
			                    factores = (unidades[i]).split("|");
					    		logo = {descripcion:factores[1],codunidad:factores[0],factor:factores[8]};
					    		funidades.push(logo)
					    	}

					    	this.putunidades = funidades;
						    var cantidad_faltante =  parseFloat(v.cantidad) - parseFloat(v.cantidadguia);

                             filas.push({codcomprobantetipo:venta.codcomprobantetipo,seriecomprobante:venta.seriecomprobante,nrocomprobante:venta.nrocomprobante,
                             	codkardex:venta.codkardex,codproducto: v.codproducto, producto: v.descripcion, codunidad: v.codunidad,unidades: this.putunidades,
					            unidad: v.unidad, cantidad: cantidad_faltante, pesoitem: 0, descripcion:v.descripcion, itemkardex:v.item})

                             this.putunidades = [];
						});

						this.detalle = filas
					}
				});
            }

		},
		phuyu_itemcompra: function(){
			$(".compose").slideToggle();$("#phuyu_tituloform").text("BUSCAR COMPRA");
			phuyu_sistema.phuyu_loader("phuyu_formulario",180); 
			this.$http.post(url+"compras/compras/buscar").then(function(data){
				$("#phuyu_formulario").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			},function(){ 
				phuyu_sistema.phuyu_error_operacion(); 
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			});
		},
		phuyu_addtransportista: function(){
			$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario",180); 
			this.$http.post(url+"compras/proveedores/nuevo_1").then(function(data){
				$("#phuyu_formulario").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			},function(){
				phuyu_sistema.phuyu_error_operacion(); 
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			});
		},
		phuyu_addconductor: function(){
			$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario",180); 
			this.$http.post(url+"ventas/clientes/nuevo_conductor").then(function(data){
				$("#phuyu_formulario").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			},function(){
				phuyu_sistema.phuyu_error_operacion(); 
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			});
		},
		validar_general: function(){
           $("#btngeneral").attr('disabled',true)
           if(validar("requeridogeneral") != true) {return;}
           $("#btngeneral").attr('disabled', false)
		},
		enviar_general: function(envio){
			$(".general").removeClass('active')
			$("#general").removeClass('active in')
			$(".envio").addClass('active')
			$("#envio").addClass('active in')
			this.validar_envio()
		},
		validar_envio: function(){
           $("#btnenvio").attr('disabled',true)
           if(validar("requeridoenvio") != true) {return;}
           if(this.campos.codubigeopartida == ''){return;}
           if(this.campos.codubigeollegada == ''){return;}
           $("#btnenvio").attr('disabled', false)
		},
		enviar_envio: function(envio){
			$(".envio").removeClass('active')
			$("#envio").removeClass('active in')
			$(".detalle").addClass('active')
			$("#detalle").addClass('active in')
		},
		atras: function(donde,desde){
			$("."+desde).removeClass('active')
			$("#"+desde).removeClass('active in')
			$("."+donde).addClass('active')
			$("#"+donde).addClass('active in')
		}
	},
	created: function(){
		this.phuyu_series(); phuyu_sistema.phuyu_fin(); this.validar_general(); this.otras_opciones();
	}
});

document.addEventListener("keyup", buscar_f11, false);
function buscar_f11(e){
    var keyCode = e.keyCode;
    if(keyCode==122){
    	phuyu_operacion.phuyu_item();
    }
}