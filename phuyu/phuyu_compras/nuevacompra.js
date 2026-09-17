var phuyu_operacion = new Vue({
	el: "#phuyu_operacion",
	data: {
		estado:0, importetotalcredito:0,titulo: "REGISTRO NUEVA COMPRA", igvsunat:$("#igvsunat").val(),rubro:$("#rubro").val(), icbpersunat:$("#icbpersunat").val(), igv:false,
		series:[], detalle: [], cuotas: [], putunidades:[],
		campos:{
			codkardex:0, codpersona:2, codmovimientotipo:2, codlote:0, codcomprobantetipo:"", seriecomprobante:"", nro:"", 
			fechacomprobante:"", fechakardex:"", codconcepto:12, descripcion:"REGISTRO POR COMPRA", cliente:"", direccion:"",
			codempleado:0, codmoneda:1, tipocambio:0.00, codcentrocosto:0, nroplaca:"", retirar:true, afectacaja:true,
			condicionpago:1, nrodias:30, nrocuotas:1, codcreditoconcepto:4, tasainteres:0, interes:0, totalcredito:0, porcdescuento:0.00
		},
		item:{
			producto:"", unidad:"", cantidad:0, preciobruto:0, descuento:0, porcdescuento:0, preciosinigv:0, precio:0, 
			codafectacionigv:"", igv:0, flete:0, valorventa:0, conicbper:0, icbper:0, subtotal:0, descripcion:""
		},
		pagos:{
			codtipopago:1, importe:0, fechadocbanco:"", nrodocbanco:""
		},
		operaciones:{
			gravadas:0.00, exoneradas:0.00, inafectas:0.00, gratuitas:0.00
		},
		totales:{
			flete:0.00, gastos:0.00, bruto:0.00, descuentos:0.00, descglobal:0.00, valorventa:0.00, igv:0.00, isc:0.00, icbper:0.00, 
			subtotal:0.00, importe:0.00
		}
	},
	methods: {

		/* FUNCIONES GENERALES DE LA COMPRA */

		phuyu_compra: function(){
			swal({
				title: "SEGURO REGISTRAR NUEVA COMPRA ?",   
				text: "LOS CAMPOS SE QUEDARAN VACIOS ", 
				icon: "warning",
				dangerMode: true,
				buttons: ["CANCELAR", "SI, NUEVA COMPRA"],
			}).then((willDelete) => {
				if (willDelete){
					this.phuyu_nueva_compra();
				}
			});
		},
		phuyu_series: function(){
			if (this.campos.codcomprobantetipo!=undefined && this.campos.codcomprobantetipo==13) {
				$(".serie_liq").show()
				$(".serie_ot").hide()
				$("#nro").attr('disabled',true)
				$("#seriecomprobante").removeAttr('required')
				this.estado = 1;
				this.$http.get(url+"caja/controlcajas/phuyu_seriescaja/"+this.campos.codcomprobantetipo).then(function(data){
					this.series = data.body.series; this.estado = 0;
					// this.campos.seriecomprobante = $("#serie").val(); this.phuyu_correlativo();
					this.campos.seriecomprobanteliq = data.body.serie; this.phuyu_correlativo();
				});
			}else{
				$(".serie_liq").hide()
				$(".serie_ot").show()
				$("#nro").attr('disabled',false)
				$("#seriecomprobante").attr('required')
				$("#seriecomprobanteliq").removeAttr('required')
				this.campos.nro = ''
			}
		},
		phuyu_correlativo: function(){
			if (this.campos.codcomprobantetipo!=undefined) {
				if (this.campos.seriecomprobanteliq!="") {
					this.$http.get(url+"caja/controlcajas/phuyu_correlativo/"+this.campos.codcomprobantetipo+"/"+this.campos.seriecomprobanteliq).then(function(data){
						this.campos.nro = data.body;
					});
				}
			}
		},
		phuyu_nueva_compra: function(){
			phuyu_sistema.phuyu_inicio();
			this.titulo = "REGISTRO NUEVA COMPRA"; this.campos.codkardex = 0;

			this.$http.post(url+phuyu_controller+"/nuevo").then(function(data){
				$("#phuyu_sistema").empty().html(data.body);
			});
		},
		phuyu_atras: function(){
			phuyu_sistema.phuyu_modulo();
		},
		phuyu_addproveedor: function(){
			$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario",180); 
			this.$http.post(url+"compras/proveedores/nuevo_1").then(function(data){
				$("#phuyu_formulario").empty().html(data.body);
			},function(){
				phuyu_sistema.phuyu_error_operacion(); 
			});
		},

		/* DETALLE DE LA COMPRA Y TOTALES */

		phuyu_item: function(){
			$(".compose").slideToggle(); $("#phuyu_tituloform").text("BUSCAR PRODUCTO"); 
			phuyu_sistema.phuyu_loader("phuyu_formulario",180); 

			this.$http.post(url+"almacen/productos/buscar/compras").then(function(data){
				$("#phuyu_formulario").empty().html(data.body);
			},function(){
				phuyu_sistema.phuyu_error(); 
			});
		},
		phuyu_additem: function(producto, precio){

			//console.log("HolaaaaaaaDeyvii", producto);
			
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
		    			producto.afectacionigv = factores[14];
		    		}
		    	}
		    	
		    	producto.preciosinigv = producto.precio; producto.precio = precio; 
		    	producto.valorventa = producto.precio; producto.subtotal = producto.precio;
		    	
		    	producto.igv = 0; var porcentaje = 1;
				if (producto.afectacionigv==10) {
					var porcentaje = (1 + this.igvsunat) / 100;
					producto.preciosinigv = Number((producto.precio / porcentaje).toFixed(4));
					producto.valorventa = Number((producto.precio / porcentaje).toFixed(2));
					producto.igv = Number((producto.subtotal - producto.valorventa).toFixed(2));
				}
				
				producto.icbper = 0; producto.isc = 0;
				if (producto.afectoicbper==1) {
					producto.icbper = Number((1 * this.icbpersunat).toFixed(2));;
				}

				this.detalle.push({
					codproducto: producto.codproducto, producto: producto.descripcion, codunidad: producto.codunidad,unidades: this.putunidades,
					unidad: producto.unidad, cantidad: 1, stock:producto.stock, control:0,
					preciobrutosinigv:0, preciobruto: 0, preciosinigv: null, precio: 0,
					preciorefunitario: producto.precio, porcdescuento: 0, descuento: 0,
					codafectacionigv: producto.afectacionigv, igv: producto.igv,flete: 0, conicbper: producto.afectoicbper, icbper: producto.icbper,
					valorventa: 0, subtotal:0, subtotal_tem:0, descripcion:"", calcular: producto.calcular
				});
				this.phuyu_igv();
				this.putunidades = [];
		    }else{
		    	this.phuyu_calcular(existe_item[0]);
		    }
		},
		phuyu_deleteitem: function(index,producto){
			this.detalle.splice(index,1); this.phuyu_totales();
		},
		phuyu_itemdetalle: function(index,producto){
			this.item = producto; $("#modal_itemdetalle").modal({backdrop: 'static', keyboard: false});
		},
		phuyu_itemcalcular: function (item,tipoprecio) {
			/* 
				tipoprecio: -1: DESCUENTO PRECIO, -2: DESCUENTO PORCENTAJE, 0: BRUTO SIN IGV, 1: BRUTO CON IGV, 
				2: PRECIO SIN IGV, 3: PRECIO CON IGV, 4: CALCULAR DEL SUBTOTAL
			*/
			var porcentaje = 1;
			if (item.codafectacionigv==21) {
				item.preciobrutosinigv = 0; item.preciobruto = 0; item.porcdescuento = 0; item.descuento = 0; 
				item.preciosinigv = 0; item.precio = 0; item.igv = 0; item.valorventa = 0; item.subtotal = 0; 
			}
			if (item.codafectacionigv==10) {
				var porcentaje = (1 + this.igvsunat) / 100;
			}

			if (tipoprecio==-1) {
				item.porcdescuento = Number((item.descuento / item.preciobruto * 100).toFixed(2));
				item.preciosinigv = Number((item.preciobrutosinigv - item.descuento).toFixed(4)); 
				tipoprecio = 2;
			}
			if (tipoprecio==-2) {
				item.descuento = Number((item.preciobruto * item.porcdescuento / 100).toFixed(4));
				item.preciosinigv = Number((item.preciobrutosinigv - item.descuento).toFixed(4)); tipoprecio = 2;
			}
			if(tipoprecio==0){
				item.preciobruto = Number((item.preciobrutosinigv * porcentaje).toFixed(4));
				item.descuento = Number((item.preciobrutosinigv * item.porcdescuento / 100).toFixed(4));

				item.preciosinigv = Number((item.preciobrutosinigv - item.descuento).toFixed(4));
				item.precio = Number((item.preciosinigv * porcentaje).toFixed(4));
			}
			if(tipoprecio==1){
				item.preciobrutosinigv = Number((item.preciobruto / porcentaje).toFixed(4));
				item.descuento = Number((item.preciobrutosinigv * item.porcdescuento / 100).toFixed(4));

				item.precio = Number((item.preciobruto - item.descuento).toFixed(4));
				item.preciosinigv = Number((item.precio / porcentaje).toFixed(4));
			}

			var descuento = item.descuento;
			if (item.descuento=="") {
				var descuento = 0;
			}
			
			if (tipoprecio==2) {
				item.precio = Number((item.preciosinigv * porcentaje).toFixed(4));
				item.preciobrutosinigv = Number((parseFloat(item.preciosinigv) + parseFloat(descuento) ).toFixed(4));
				item.preciobruto = Number((item.preciobrutosinigv * porcentaje).toFixed(4));
			}
			if (tipoprecio==3) {
				item.preciosinigv = Number((item.precio / porcentaje).toFixed(4));
				item.preciobruto = Number((parseFloat(item.precio) + parseFloat(descuento) ).toFixed(4));
				item.preciobrutosinigv = Number((item.preciobruto / porcentaje).toFixed(4));
			}

			if(tipoprecio==4){
				item.preciosinigv = Number((item.valorventa / item.cantidad).toFixed(4));
				item.preciobrutosinigv = Number((parseFloat(item.preciosinigv) + parseFloat(descuento) ).toFixed(4));

				item.precio = Number((item.preciosinigv * porcentaje).toFixed(4));
				item.preciobruto = Number((parseFloat(item.precio) + parseFloat(descuento) ).toFixed(4));
			}else{
				item.valorventa = Number((item.cantidad * item.preciosinigv).toFixed(2));
			}

			item.icbper = 0;
			if (item.conicbper==1) {
				item.icbper = Number((item.cantidad * this.icbpersunat).toFixed(2));
			}

			item.subtotal = Number((item.cantidad * item.precio).toFixed(2));
			item.igv = Number((item.subtotal - item.valorventa).toFixed(2));
			this.phuyu_totales();
		},
		phuyu_itemcalcular_cerrar: function (item) {
			if (parseFloat(item.subtotal) < 0) {
				phuyu_sistema.phuyu_noti("EL SUBTOTAL DEBE SER MAYOR A CERO","REVISAR LOS CAMPOS DEL ITEM","error"); return false;
			}
			$("#modal_itemdetalle").modal("hide");
		},
		phuyu_calcular: function(producto){
			var porcentaje = 0;
			if (producto.codafectacionigv==10) {
				var porcentaje = this.igvsunat / 100;
			}

			producto.precio = Number((parseFloat(producto.preciosinigv) + (parseFloat(producto.preciosinigv) * porcentaje)).toFixed(4));
			if (producto.preciosinigv=="") {
				producto.preciobruto = Number((producto.descuento).toFixed(4));
			}else{
				producto.preciobruto = Number((producto.precio + producto.descuento).toFixed(4));
				producto.preciobrutosinigv = Number((producto.preciosinigv + producto.descuento).toFixed(4));
			}

			producto.valorventa = Number((producto.cantidad * producto.preciosinigv).toFixed(2));
			producto.subtotal = Number((producto.cantidad * producto.precio).toFixed(2));
			producto.igv = Number((producto.subtotal - producto.valorventa).toFixed(2));

			producto.icbper = 0;
			if (producto.conicbper==1) {
				producto.icbper = Number((producto.cantidad * this.icbpersunat).toFixed(2));
			}
			this.phuyu_totales();
		},
		phuyu_subtotal: function(producto){
			// SI producto.calcular = 1 calcula cantidad, producto.calcular = 2 calcula precio //
			if (producto.calcular==1) {
				if (producto.precio!=0) {
					producto.cantidad = Number((producto.subtotal / producto.precio).toFixed(4));
				}
			}else{
				if (producto.cantidad!=0) {
					producto.precio = Number((producto.subtotal / producto.cantidad).toFixed(4));
				}
			}

			var porcentaje = 1;
			if (producto.codafectacionigv==10) {
				var porcentaje = (1 + this.igvsunat) / 100;
			}
			producto.preciosinigv = Number((producto.precio / porcentaje).toFixed(4));
			if (producto.precio=="") {
				producto.preciobruto = Number((producto.descuento).toFixed(4));
			}else{
				producto.preciobruto = Number((producto.precio + producto.descuento).toFixed(4));
			}

			producto.valorventa = Number((producto.cantidad * producto.preciosinigv).toFixed(2));
			producto.igv = Number((producto.subtotal - producto.valorventa).toFixed(2));
			producto.icbper = 0;
			if (producto.conicbper==1) {
				producto.icbper = Number((producto.cantidad * this.icbpersunat).toFixed(2));
			}
			this.phuyu_totales();
		},
		phuyu_totales: function () {
			this.totales.bruto = 0.00; this.totales.descuentos = 0.00; this.totales.descglobal = 0.00;
			this.operaciones.gravadas = 0.00; this.operaciones.inafectas = 0.00; 
			this.operaciones.exoneradas = 0.00; this.operaciones.gratuitas = 0.00;
			this.totales.igv = 0.00; this.totales.isc = 0.00; this.totales.icbper = 0.00;this.totales.flete = 0.00,
			this.totales.valorventa = 0.00; this.totales.subtotal = 0.00; this.totales.importe = 0.00;
			t = this;
			var detalle = this.detalle.filter(function(p){
				t.totales.bruto = Number((t.totales.bruto + (parseFloat(p.cantidad) * parseFloat(p.preciobruto)) ).toFixed(2));
				t.totales.descuentos = Number((t.totales.descuentos + (parseFloat(p.cantidad) * parseFloat(p.descuento)) ).toFixed(2));

				if (p.codafectacionigv==10) {
					t.operaciones.gravadas = Number((t.operaciones.gravadas + parseFloat(p.subtotal) - parseFloat(p.igv) ).toFixed(2));
				}
				if (p.codafectacionigv==20) {
					t.operaciones.exoneradas = Number((t.operaciones.exoneradas + parseFloat(p.subtotal) ).toFixed(2));
				}
				if (p.codafectacionigv==30) {
					t.operaciones.inafectas = Number((t.operaciones.inafectas + parseFloat(p.subtotal) ).toFixed(2));
				}
				if (p.codafectacionigv==21) {
					t.operaciones.gratuitas = Number((t.operaciones.gratuitas + parseFloat(p.subtotal) ).toFixed(2));
				}

				t.totales.igv = Number((t.totales.igv + parseFloat(p.igv)).toFixed(2));
				t.totales.icbper = Number((t.totales.icbper + parseFloat(p.icbper)).toFixed(2));
                t.totales.flete = Number((t.totales.flete + parseFloat(p.flete)).toFixed(2));
				t.totales.valorventa = Number((t.totales.valorventa + parseFloat(p.valorventa) ).toFixed(2));
				t.totales.subtotal = Number((t.totales.subtotal + parseFloat(p.subtotal)).toFixed(2));
			});

			if (this.totales.flete=="" && this.totales.gastos=="") {
				var gastos = 0;
			}else{
				if (this.totales.flete=="") {
					var gastos =  this.totales.gastos;
				}else{
					if (this.totales.gastos=="") {
						var gastos = this.totales.flete;
					}else{
						var gastos = this.totales.flete + this.totales.gastos;
					}
				}
			}

			var subtotal_tem = this.operaciones.gravadas + this.operaciones.inafectas + this.operaciones.exoneradas + this.operaciones.gratuitas;
			this.totales.importe = Number((parseFloat(subtotal_tem) + parseFloat(gastos) + this.totales.igv + this.totales.icbper).toFixed(2));
			this.phuyu_condicionpago();
		},
		phuyu_igv: function () {
			if (this.igv == true) {
				var valorigv = (parseFloat(this.igvsunat) + 100) / 100;
				this.totales.valorventa = Number((this.totales.subtotal / valorigv).toFixed(2));
				this.totales.igv = Number((this.totales.importe - this.totales.valorventa).toFixed(2));
				//alert(this.totales.valorventa);
				this.totales.subtotal = Number((this.totales.valorventa).toFixed(2));
				this.totales.importe = Number((this.totales.valorventa + this.totales.igv + this.totales.icbper).toFixed(2));
			}
			var igvsunat = this.igvsunat; var icbpersunat = this.icbpersunat; var itemigv = this.igv;
			var detalle = this.detalle.filter(function(producto){
				producto.codafectacionigv = 20;
				if (itemigv == true) {
					producto.codafectacionigv = 10;
				}
				var porcentaje = 1;
				if (producto.codafectacionigv==10) {
					var porcentaje = (1 + igvsunat) / 100;
				}
				producto.preciobrutosinigv = Number((producto.precio / porcentaje).toFixed(4));
				producto.preciobruto = Number((parseFloat(producto.precio) + parseFloat(producto.descuento)).toFixed(4));
				producto.preciosinigv = Number((producto.preciobrutosinigv - producto.descuento).toFixed(4));
				producto.precio = Number((producto.preciobruto - producto.descuento).toFixed(4));

				producto.valorventa = Number((producto.cantidad * producto.preciosinigv).toFixed(2));
				producto.subtotal = Number((producto.cantidad * producto.precio).toFixed(2));
				producto.igv = Number((producto.subtotal - producto.valorventa).toFixed(2));
				producto.icbper = 0;
				if (producto.conicbper==1) {
					producto.icbper = Number((producto.cantidad * icbpersunat).toFixed(2));
				}
			});
			this.phuyu_totales(); 
		},
		/* DATOS GENERALES DE LA COMPRA */

		phuyu_tipocambio(){
			if (this.campos.codmoneda==1) {
				this.campos.tipocambio = 1;
			}else{
				this.campos.fechacomprobante = $("#fechacomprobante").val();
				this.$http.get(url+"caja/tipocambios/consulta/"+this.campos.fechacomprobante).then(function(data){
					this.campos.tipocambio = data.body;
				});
			}
		},

		phuyu_condicionpago: function(){
			if (this.campos.condicionpago==2) {
				this.phuyu_lineascredito(); this.phuyu_cuotas(); this.campos.codconcepto = 14;
			}else{
				this.campos.codconcepto = 12;
			}
		},
		phuyu_lineascredito: function(){
			if (this.campos.codpersona!="" || this.campos.codpersona!=2) {
				this.$http.get(url+"ventas/lineascredito/phuyu_lineascredito/"+this.campos.codpersona).then(function(data){
					$("#codlote").empty().html(data.body);
					this.campos.codlote = parseInt($("#codlote").val());
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
		phuyu_cuotas: function(){
			var importe = Number((this.totales.importe/this.campos.nrocuotas).toFixed(1));

			var interes = Number(( (importe*(this.campos.tasainteres/100)*(this.campos.nrodias/30)) ).toFixed(1));
			var total = Number((importe + interes).toFixed(1));
			var fechainicio = $("#fechainicio").val();
			fechainicio = String(fechainicio).split("-");
    		fecha = new Date(fechainicio[0]+"/"+fechainicio[1]+"/"+fechainicio[2]);
    		this.totales.interes = Number(( (this.totales.importe*(this.campos.tasainteres/100)*(this.campos.nrodias/30)) ).toFixed(1));
			this.campos.totalcredito = Number(( parseFloat(this.totales.importe) + parseFloat(this.totales.interes) ).toFixed(1));
    		
			this.cuotas = []; var suma_importe = 0; var suma_total = 0;this.importetotalcredito = 0;
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
					importe = Number(( this.totales.importe - parseFloat(suma_importe) ).toFixed(1));
					total = Number(( this.campos.totalcredito - parseFloat(suma_total) ).toFixed(1));
				}else{
					suma_importe = Number(( parseFloat(suma_importe) + parseFloat(importe) ).toFixed(1));
					suma_total = Number(( parseFloat(suma_total) + parseFloat(total) ).toFixed(1));
				}
				this.importetotalcredito = (this.importetotalcredito + total);
				this.cuotas.push({
					"nrocuota":i,"fechavence":fechavence,"nroletra":"","nrounicodepago":"","importe":importe,"interes":interes,"total":total
				});
			}
			this.importetotalcredito = parseFloat(this.importetotalcredito).toFixed(2)
		},

		calcular_credito: function(){
			var importe = Number((this.totales.importe/this.campos.nrocuotas).toFixed(1));

			var interes = Number(( (importe*(this.campos.tasainteres/100)*(this.campos.nrodias/30)) ).toFixed(1));
    		this.totales.interes = Number(( (this.totales.importe*(this.campos.tasainteres/100)*(this.campos.nrodias/30)) ).toFixed(1));
			this.campos.totalcredito = Number(( parseFloat(this.totales.importe) + parseFloat(this.totales.interes) ).toFixed(1));
			this.importetotalcredito = 0;
			var t = this;
			var l = this.cuotas.length; i = 1;
			var suma_importe = 0; var suma_total = 0;
			var cuotas = this.cuotas.filter(function(p){
				p.interes = interes;
				p.total = Number((parseFloat(p.importe) + parseFloat(p.interes)).toFixed(2));
				if(l==i){
					p.importe = Number(( t.totales.importe - parseFloat(suma_importe) ).toFixed(1));
					p.total = Number(( t.campos.totalcredito - parseFloat(suma_total) ).toFixed(1));
				}else{
					suma_importe = Number(( parseFloat(suma_importe) + parseFloat(p.importe) ).toFixed(1));
					suma_total = Number(( parseFloat(suma_total) + parseFloat(p.total) ).toFixed(1));
				}
				t.importetotalcredito = t.importetotalcredito + p.total;
				i++
			});

			this.importetotalcredito = parseFloat(this.importetotalcredito).toFixed(2);
		},

		// GUARDAR LA COMPRA //

		phuyu_guardar: function(){
			this.pagos.importe = this.totales.importe;
			
			if (this.detalle.length==0) {
				phuyu_sistema.phuyu_noti("REGISTRAR UN PRODUCTO EN EL DETALLE", "REGISTRAR ITEM PARA LA COMPRA","error"); return false;
			}

			if(this.campos.codpersona==2){
				swal({
					title: "ESTA GENERANDO UNA COMPRA CON PROVEEDORES VARIOS",   
					text: "ESTA SEGURO DE CONTINUAR CON LA COMPRA?", 
					icon: "warning",
					dangerMode: true,
					buttons: ["CANCELAR", "SI, CONTINUAR"],
				}).then((willDelete) => {
					if (willDelete){
						this.phuyu_lineascredito();
						$("#modal_finventa").modal({backdrop: 'static', keyboard: false});
					}
				});
			}else{
				this.phuyu_lineascredito();
				$("#modal_finventa").modal({backdrop: 'static', keyboard: false});
			}

			/*swal({
				title: "SEGURO REGISTRAR LA COMPRA ?",   
				text: "VERIFIQUE SUS CAMPOS QUE TODO ESTE CORRECTO", 
				icon: "warning",
				dangerMode: true,
				buttons: ["CANCELAR", "SI, REGISTRAR COMPRA"],
			}).then((willDelete) => {
				if (willDelete){
					
				}
			});*/
		},
		phuyu_pagar: function(){
			if (this.campos.condicionpago==1) {
				if (parseFloat(this.totales.importe)!=parseFloat(this.pagos.importe)) {
					phuyu_sistema.phuyu_noti("EL IMPORTE DEL PAGO DEBE SER IGUAL AL TOTAL DE LA COMPRA","ACTUAL S/. "+this.pagos.importe,"error"); 
					return false;
				}
			}else{
				this.campos.codlote = parseInt($("#codlote").val());
				console.log(this.campos.codlote+' - '+this.rubro)
				if (this.campos.codpersona==2) {
					phuyu_sistema.phuyu_noti("ATENCION USUARIO: EL SISTEMA NO PERMITE REGISTRAR UN CREDITO A PROVEEDORES VARIOS","","error");
					return false;
				}
				if(this.campos.codlote==0 && this.rubro==6){
					phuyu_sistema.phuyu_noti("ATENCION USUARIO: LA COMPRA NO SE PUEDE REALIZAR PORQUE EL PROVEEDOR SELECCIONADO NO CUENTA CON UNA LINEA DE CREDITO VÁLIDA","","error");
					return false;
				}
			}

			if(this.campos.totalcredito!=this.importetotalcredito){
				$("#totalimportecredito").addClass("rojo");
				phuyu_sistema.phuyu_noti("ATENCION USUARIO: LA COMPRA NO SE PUEDE REALIZAR LOS TOTALES DE LOS CREDITOS NO COINCIDEN","","error");
				return false;
			}
			
			this.campos.fechacomprobante = $("#fechacomprobante").val();
			this.campos.fechakardex = $("#fechakardex").val();
			this.pagos.fechadocbanco = $("#fechadocbanco").val();

			this.estado = 1; phuyu_sistema.phuyu_inicio_guardar("GUARDANDO COMPRA . . .");
			this.$http.post(url+phuyu_controller+"/guardar", {"campos":this.campos,"detalle":this.detalle,"cuotas":this.cuotas,"pagos":this.pagos,"totales":this.totales}).then(function(data){
				if (data.body=="e") {
					phuyu_sistema.phuyu_alerta("SESION DEL USUARIO TERMINADA","DEBE INICIAR SESION NUEVAMENTE","error");
				}else{
					if (data.body==1) {
						phuyu_sistema.phuyu_noti("COMPRA REGISTRADA CORRECTAMENTE","COMPRA REGISTRADA EN EL SISTEMA","success");
					}else{
						phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR COMPRA","ERROR DE RED","error");
					}
				}
				$("#modal_finventa").hide();
				if ($('.modal-backdrop').is(':visible')) {
				  $('body').removeClass('modal-open'); 
				  $('.modal-backdrop').remove(); 
				};
				phuyu_sistema.phuyu_fin(); this.phuyu_nueva_compra();
			}, function(){
				phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR COMPRA","ERROR DE RED","error");
				phuyu_sistema.phuyu_fin();
			});
		}
	},
	created: function(){
		this.phuyu_tipocambio(); phuyu_sistema.phuyu_fin();
	}
});

document.addEventListener("keyup", buscar_f11, false);
function buscar_f11(e){
    var keyCode = e.keyCode;
    if(keyCode==122){
    	phuyu_operacion.phuyu_item();
    }
}