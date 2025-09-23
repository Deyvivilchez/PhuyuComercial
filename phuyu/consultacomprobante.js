var phuyu_operacion = new Vue({
	el: "#phuyu_operacion",
	data: {
		detalle:[],coddocumentotipo:"",documento:"", fechai:"",fechaf:"", estado : 0,codcomprobantetipo:"",
		item:{
			seriecomprobante:"",nrocomprobante:""
		}
	},
	methods: {
		phuyu_tipodocumento: function(){
			if (this.coddocumentotipo==2) {
				$("#documento").attr("minlength","8"); $("#documento").attr("maxlength","8");
			}else{
				if (this.coddocumentotipo==4) {
					$("#documento").attr("minlength","11"); $("#documento").attr("maxlength","11");
				}else{
					$("#documento").attr("minlength","8"); $("#documento").attr("maxlength","15");
				}
			}
		},
		enviar_consulta: function(){
			if(this.codcomprobantetipo == ""){
            	new PNotify({title: "SELECCIONE EL TIPO DE COMPROBANTE",text: "",type: "error",delay: 2500,styling: 'bootstrap3'});
            	return false;
            }
            if(this.coddocumentotipo == ""){
            	new PNotify({title: "SELECCIONE EL TIPO DE DOCUMENTO",text: "",type: "error",delay: 2500,styling: 'bootstrap3'});
            	return false;
            }
            if(this.documento == ""){
            	new PNotify({title: "INGRESE EL NUMERO DE DOCUMENTO",text: "",type: "error",delay: 2500,styling: 'bootstrap3'});
            	return false;
            }

            if(this.coddocumentotipo == 4){
            	if(this.documento.length != 11){
            		new PNotify({title: "EL RUC DEBE TENER 11 CARACTERES",text: "",type: "error",delay: 3500,
						styling: 'bootstrap3'
					});return false;
            	}
            }else if(this.coddocumentotipo == 2){
            	if(this.documento.length != 8){
            		new PNotify({title: "EL DNI DEBE TENER 8 CARACTERES",text: "",type: "error",delay: 3500,
						styling: 'bootstrap3'
					});return false;
            	}
            }

            this.fechai = $("#fechai").val();
			this.fechaf = $("#fechaf").val();

            this.phuyu_consulta();
		},
		phuyu_consulta: function(){
            this.estado = 1;
			this.$http.post(url+"consultacomprobantes/consultar", {"codcomprobantetipo": this.codcomprobantetipo,"documento":this.documento,"fechai":this.fechai,"fechaf":this.fechaf}).then(function(data){
				this.detalle = data.body
				this.estado = 0;
			}, function(){
				new PNotify({title: "ERROR AL REALIZAR LA CONSULTA",text: "Consulte con su Proveedor",type: "error",delay: 3500,styling: 'bootstrap3'});
				this.estado = 0;
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
		    	producto.preciosinigv = producto.precio; producto.precio = precio; 
		    	producto.valorventa = producto.precio; producto.subtotal = producto.precio;
		    	
		    	producto.afectacionigv = 20; producto.igv = 0; var porcentaje = 1;
				if (producto.afectoigvventa==1) {
					var porcentaje = (1 + this.igvsunat) / 100;

					producto.afectacionigv = 10;
					producto.preciosinigv = Number((producto.precio / porcentaje).toFixed(4));
					producto.valorventa = Number((producto.precio / porcentaje).toFixed(2));
					producto.igv = Number((producto.subtotal - producto.valorventa).toFixed(2));
				}
				
				producto.icbper = 0; producto.isc = 0;
				if (producto.afectoicbper==1) {
					producto.icbper = Number((1 * this.icbpersunat).toFixed(2));;
				}

				producto.control = 0;
				if (this.stockalmacen==1) {
					if (producto.controlstock==1) {
						producto.control = 1;
					}
				}

				this.detalle.push({
					codproducto: producto.codproducto, producto: producto.descripcion, codunidad: producto.codunidad,
					unidad: producto.unidad, cantidad: 1, stock:producto.stock, control:producto.control,
					preciobruto: producto.preciosinigv, preciosinigv: producto.preciosinigv, precio: producto.precio,
					preciorefunitario: producto.precio, porcdescuento: 0, descuento: 0,
					codafectacionigv: producto.afectacionigv, igv: producto.igv, conicbper: producto.afectoicbper, icbper: producto.icbper,
					valorventa: producto.valorventa, subtotal:producto.subtotal, subtotal_tem:producto.subtotal, 
					descripcion:"", calcular: producto.calcular
				});
				this.phuyu_totales();
		    }else{
		    	this.phuyu_calcular(existe_item[0]);
		    }
		},
	},
	created: function(){
		
	}
});

document.addEventListener("keyup", buscar_f11, false);
function buscar_f11(e){
    var keyCode = e.keyCode;
    if(keyCode==122){
    	phuyu_operacion.phuyu_item();
    }
}