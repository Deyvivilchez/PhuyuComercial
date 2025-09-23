var phuyu_operacion = new Vue({
	el: "#phuyu_operacion",
	data: {
		series:[],codkardex:$("#codkardex").val(),detalle:[],
		campos:{

            codpersona:$("#codpersona").val(),codcomprobantetipo:16,seriecomprobante:$("#serie").val(), nro:"",codmotivotraslado:4,codmodalidadtraslado:'',
			fechaguia:$("#fechatraslado").val(), fechatraslado:$("#fechatraslado").val(), descripcion:"REGISTRO POR GUIA DE REMISION", destinatario:"", direccionpartida:$("#direccionpartida").val(),descripcionmotivo:"",
			direccionllegada:$("#direccionllegada").val(),codempleado:0, codmoneda:1, tipocambio:1.00, codcentrocosto:0, nroplaca:"", retirar:true, almacenpartida:$("#almacen_principal").val(),
			almacendestino: $("#almacen_llegada").val(),codunidad:'',peso:0,nropaquetes:0,observaciones:"",codubigeopartida:$("#ubigeopartida").val(),codubigeollegada:$("#ubigeollegada").val(),
			coddocumentotipotransportista:0,documentotransportista:'',razonsocialtransportista:'',coddocumentotipoconductor:0,
			documentoconductor:'',razonsocialconductor:'',codmovimientotipo:0,marca:'',licenciaconductor:'',constancia:''
		},
		item:{
			producto:"", unidad:"", cantidad:0, pesoitem:0, descripcion:""
		}
	},
	methods: {
		phuyu_detalle: function(){
            this.$http.get(url+"almacen/salidas/phuyu_detalle/"+this.codkardex).then(function(data){
				if (data.body.length==0) {
					phuyu_sistema.phuyu_alerta("NO EXISTE EL DETALLE DE LA SALIDA", "CORREGIR POR FAVOR", "error");return;
				}else{
					var datos = data.body
					var filas = [];
					$.each( datos, function( k, v ) {
					    var cantidad_faltante =  parseFloat(v.cantidad);

                         filas.push({codkardex:v.codkardex,codproducto: v.codproducto, producto: v.descripcion, codunidad: v.codunidad,
				            unidad: v.unidad, cantidad: cantidad_faltante, descripcion:v.descripcion, itemkardex:v.item,pesoitem:0})
					});

					this.detalle = filas
				}
			});
		},
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
        phuyu_infosocio: function(tabla){
			var codpersona = 0;
			this.campos.razonsocialconductor = $("#codconductor option:selected").text();
			codpersona = this.campos.codconductor

			this.$http.get(url+"ventas/clientes/infosocio/"+codpersona).then(function(data){
				this.campos.coddocumentotipoconductor = data.body[0].coddocumentotipo;
				this.campos.documentoconductor = data.body[0].documento;
				this.campos.licenciaconductor = data.body[0].licenciadeconducir
			});
        },
		phuyu_item: function(){
			$(".compose").slideToggle(); $("#phuyu_tituloform").text("BUSCAR PRODUCTO"); 
			phuyu_sistema.phuyu_loader("phuyu_formulario",180); 

			this.$http.post(url+"almacen/productos/buscar/ventas").then(function(data){
				$("#phuyu_formulario").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); 
				phuyu_sistema.phuyu_modulo();
			});
		},
		phuyu_additem: function(producto){
			var existeproducto = this.detalle.filter(function(p){
			    if(p.codproducto == producto.codproducto && p.codunidad == producto.codunidad ){
			    	p.cantidad = p.cantidad + 1; return p;
			    };
			});

		    if (existeproducto.length==0) {
				this.detalle.push({
					"codproducto":producto.codproducto,"producto":producto.descripcion,"codunidad":producto.codunidad,
					"unidad":producto.unidad,"cantidad":1,"stock":producto.stock,"control":producto.controlstock,"precio":producto.precio,
					"preciorefunitario":producto.precio,"subtotal":producto.precio
				});
				this.phuyu_calcular(producto,1);
		    }else{
		    	this.phuyu_calcular(existeproducto[0],3);
		    }
		},
		phuyu_deleteitem: function(index,producto){
			this.phuyu_calcular(producto,2); this.detalle.splice(index,1);
		},
		phuyu_calcular: function(item){
			var pesob = 0.00;
			t = this;
			var detalle = this.detalle.filter(function(p){
				console.log(pesob)
				pesob = pesob + Number(p.pesoitem);
			});
			var pesototal = Number(pesob)
			this.campos.peso = pesototal
		},
		phuyu_guardar: function(){
			if (this.detalle.length==0) {
				phuyu_sistema.phuyu_noti("REGISTRAR UN PRODUCTO EN EL DETALLE","REGISTRAR ITEM PARA LA SALIDA","error"); 
				return false;
			}

			this.campos.salida = this.codkardex;
			this.estado = 1; phuyu_sistema.phuyu_inicio_guardar("GUARDANDO GUIA DE REMISION . . .");
				$("#modal_guia").modal("hide");
			
			this.$http.post(url+"ventas/guias/guardar", {"codkardex":[],"campos":this.campos,"detalle":this.detalle}).then(function(data){
				
				if (data.body=="e") {
					phuyu_sistema.phuyu_alerta("SU SESION DE USUARIO A TERMINADO","DEBE INICIAR SESION NUEVAMENTE","error");
				}else{
					if (data.body.estado==true) {
						swal({
							title: "DESEA IMPRIMIR LA GUIA DE REMISIÓN ?",   
							text: "DESEA IMPRIMIR EL COMPROBANTE REGISTRADO", 
							icon: "warning",
							dangerMode: true,
							buttons: ["CANCELAR", "SI, IMPRIMIR"],
						}).then((willDelete) => {
							if (willDelete){
								this.phuyu_imprimir(data.body.codguia);
							}
						});
						phuyu_sistema.phuyu_noti("GUIA DE REMISION REGISTRADO","REGISTRADO EN EL SISTEMA","success");
					}else{
						phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR GUIA DE REMISION","ERROR DE RED","error");
					}
				}
				phuyu_sistema.phuyu_fin(); phuyu_sistema.phuyu_modulo();
			}, function(){
				phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR GUIA DE REMISION","ERROR DE RED","error");
			});
		},
		phuyu_imprimir: function(codguia){
			if ($("#formato").val()=="ticket") {
				window.open(url+"facturacion/formato/ticket/"+codguia,"_blank");
			}else{
				var phuyu_url = url+"facturacion/formato/formato_guia/"+codguia;
				$("#phuyu_pdf").attr("src",phuyu_url); $("#modal_reportes").modal("show");
			}
        },
		phuyu_cerrar: function(){
			phuyu_sistema.phuyu_modulo();
		},
		phuyu_addtransportista: function(){
			$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario",180); 
			this.$http.post(url+"compras/proveedores/nuevo_1").then(function(data){
				$("#phuyu_formulario").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			},function(){
				phuyu_sistema.phuyu_error_operacion(); 
			});
		},
		phuyu_addconductor: function(){
			$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario",180); 
			this.$http.post(url+"ventas/clientes/nuevo_conductor").then(function(data){
				$("#phuyu_formulario").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			},function(){
				phuyu_sistema.phuyu_error_operacion(); 
			});
		},
	},
	created: function(){
		this.phuyu_series(); this.phuyu_detalle();phuyu_sistema.phuyu_fin();
	}
});