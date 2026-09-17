var phuyu_operacion = new Vue({
	el: "#phuyu_operacion",
	data: {
		campos:{
			"codkardex_ref":0,"codmovimientotipo":"","codpersona":1,"cliente":$("#codpersona option:selected").text(),"codcomprobantetipo":"","seriecomprobante":"","codalmacen_ref":"",
			"codcomprobantetipo_ref":0,"seriecomprobante_ref":"","nrocomprobante_ref":"","descripcion":"","fechakardex":""
		},
		estado:0, igvsunat:$("#igvsunat").val(), stockalmacen: $("#stockalmacen").val(), detalle: [],detalle_prestamo: [],putunidades : [], totales: {"valorventa":0.00,"igv":0.00,"importe":0.00},
		producto_rapido_procesando: false,
		producto_rapido_feedback_timer: null,
	},
	methods: {
		phuyu_infocliente: function(){
			this.campos.codpersona = $("#codpersona").val()
			this.campos.cliente = $(".select2-selection__rendered").text();
			this.phuyu_prestamos();
        },
        phuyu_verprestamo: function(codkardex){
        	$(".compose").removeClass("col-md-4").addClass("col-md-7");
			$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario",180);
			this.$http.get(url+phuyu_controller+"/verprestamo/"+codkardex).then(function(data){
				$("#phuyu_formulario").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error"); 
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			});
        },
        phuyu_prestamos: function(){
        	if(this.campos.codmovimientotipo==29){
				if (this.campos.codpersona!="") {
					this.estado = 1;
					this.$http.get(url+phuyu_controller+"/prestamosotorgados/"+this.campos.codpersona).then(function(data){
						this.detalle_prestamo = data.body.prestamo; this.estado = 0;
					},function(){
						phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
					});
				}else{
					this.comprobantes = []; phuyu_sistema.phuyu_noti("SELECCIONAR A LA PERSONA Y EL TIPO MOVIMIENTO", "PARA FILTRAR LOS PRESTAMOS","error");
				}
			}else{

			}
		},
		phuyu_seleccionar: function(datos){
			$("#"+this.kardex_id).css({"background-color":"#fff","color":"#000"}); 
			this.kardex_id = datos.codkardex;
			$("#"+datos.codkardex).css({"background-color":"#13a89e","color":"#fff"});

			this.campos.codkardex_ref = datos.codkardex; 
			this.campos.codcomprobantetipo_ref = datos.codcomprobantetipo;
			this.campos.seriecomprobante_ref = datos.seriecomprobante; this.campos.nrocomprobante_ref = datos.nrocomprobante;
			this.campos.cliente = datos.cliente; this.campos.direccion = datos.direccion;

			this.$http.get(url+phuyu_controller+"/detalle/"+datos.codkardex).then(function(data){
				var productos = eval(data.body.detalle);
				var filas = [];
				$.each( productos, function( k, v ) {
				    var unidades = []; var factores = []; var logo = []; arreglo = [];
		    		unidades = (v.unidades).split(";"); var funidades = [];

			    	for (var i = 0; i < unidades.length; i++) {
	                    factores = (unidades[i]).split("|");
			    		logo = {descripcion:factores[1],codunidad:factores[0],factor:factores[8]};
			    		funidades.push(logo)
			    		if(factores[8]==1){
			    			v.codunidad = factores[0];
			    		}
			    	}
			    	this.putunidades = funidades;
			    	v.subtotal = parseFloat(v.cantidad)*parseFloat(v.precio);
			    	v.valorventa = parseFloat(v.cantidad)*parseFloat(v.preciosinigv);
					filas.push({
						"itemorigen":v.item,"codproducto":v.codproducto,"producto":v.producto,"codunidad":v.codunidad,"unidades": this.putunidades,
						"unidad":v.unidad,"cantidad":v.cantidad,"stock":v.stock,"control":v.controlstock,"precio":parseFloat(v.precio).toFixed(2),
						"preciorefunitario":v.precio,"subtotal":v.subtotal,"valorventa":v.valorventa,"codafectacionigv":v.codafectacionigv,"igv":v.igv,
						"preciosinigv" : v.preciosinigv
					});
					this.putunidades = [];
				});

				this.detalle = filas
				var datos = eval(data.body.totales);
				//this.totales.valorventa = datos[0]["valorventa"]; this.totales.igv = datos[0]["igv"]; this.totales.importe = datos[0]["importe"];
				this.phuyu_totales();
			},function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED","error");
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
		phuyu_escape_producto_rapido: function(texto) {
			return String(texto || "")
				.replace(/&/g, "&amp;")
				.replace(/</g, "&lt;")
				.replace(/>/g, "&gt;")
				.replace(/"/g, "&quot;")
				.replace(/'/g, "&#039;");
		},
		phuyu_producto_rapido_match_exacto: function(producto, texto) {
			var termino = String(texto || "").trim().toUpperCase();
			return termino != "" && String(producto.codigo || "").trim().toUpperCase() === termino;
		},
		phuyu_formato_producto_rapido: function(resultado) {
			if (resultado.loading) {
				return resultado.text;
			}

			var producto = resultado.producto || resultado;
			var serie = parseInt(producto.controlarseries) === 1 ? " · Serie" : "";
			return '' +
				'<div class="phuyu-product-result">' +
					'<div>' +
						'<div class="phuyu-product-result-name">' + this.phuyu_escape_producto_rapido(producto.descripcion) + '</div>' +
						'<div class="phuyu-product-result-meta">' + this.phuyu_escape_producto_rapido(producto.codigo) + ' · Stock ' + this.phuyu_escape_producto_rapido(producto.stock) + serie + '</div>' +
					'</div>' +
					'<div class="phuyu-product-result-price">S/. ' + this.phuyu_escape_producto_rapido(producto.precio) + '</div>' +
				'</div>';
		},
		phuyu_inicializar_producto_rapido_select: function() {
			var vm = this;
			var $select = $("#producto_rapido_select");
			if (!$select.length || typeof $select.select2 !== "function") {
				return;
			}

			if ($select.data("select2")) {
				$select.select2("destroy");
			}

			$select.select2({
				ajax: {
					url: url + "almacen/productos/buscar_salidas",
					type: "POST",
					dataType: "json",
					contentType: "application/json",
					processData: false,
					delay: 120,
					data: function(params) {
						return JSON.stringify({
							buscar: params.term || "",
							pagina: params.page || 1
						});
					},
					processResults: function(data, params) {
						params.page = params.page || 1;
						var termino = String(params.term || "").trim().toUpperCase();
						var lista = data.lista || [];

						lista.sort(function(a, b) {
							var aExacto = vm.phuyu_producto_rapido_match_exacto(a, termino) ? 0 : 1;
							var bExacto = vm.phuyu_producto_rapido_match_exacto(b, termino) ? 0 : 1;
							return aExacto - bExacto;
						});

						return {
							results: lista.map(function(producto) {
								return {
									id: producto.codproducto + "-" + producto.codunidad,
									text: producto.descripcion,
									producto: producto
								};
							}),
							pagination: {
								more: data.paginacion && params.page < data.paginacion.ultima
							}
						};
					},
					cache: false
				},
				placeholder: "Escribir producto o codigo...",
				minimumInputLength: 1,
				width: "100%",
				dropdownParent: $("#phuyu_operacion"),
				language: {
					inputTooShort: function() { return "Escriba al menos 1 caracter"; },
					searching: function() { return "Buscando productos..."; },
					noResults: function() { return "No se encontraron productos"; },
					loadingMore: function() { return "Cargando mas productos..."; }
				},
				escapeMarkup: function(markup) {
					return markup;
				},
				templateResult: function(resultado) {
					return vm.phuyu_formato_producto_rapido(resultado);
				},
				templateSelection: function(resultado) {
					return resultado.text || "Buscar producto";
				}
			});

			$select.next(".select2-container").addClass("phuyu-product-select2");
			$select.off("select2:open.phuyuRapido").on("select2:open.phuyuRapido", function() {
				window.setTimeout(function() {
					var $search = $(".select2-container--open .select2-search__field");
					$search.off("keydown.phuyuRapido").on("keydown.phuyuRapido", function(evento) {
						var tecla = evento.which || evento.keyCode;
						if (tecla != 13) {
							return;
						}

						var termino = String($(this).val() || "").trim();
						var hayResultados = $(".select2-container--open .select2-results__option[aria-selected]").length > 0;
						if (termino != "" && !hayResultados) {
							evento.preventDefault();
							evento.stopPropagation();
							vm.phuyu_buscar_agregar_producto_rapido(termino);
						}
					});
				}, 0);
			});
			$select.off("select2:select.phuyuRapido").on("select2:select.phuyuRapido", function(evento) {
				var producto = evento.params.data.producto;
				if (!producto) {
					return;
				}

				$select.val(null).trigger("change");
				if (parseInt(producto.controlarseries) === 1) {
					phuyu_sistema.phuyu_noti("SELECCIONAR SERIE", "Use el boton Productos para elegir la serie del producto", "info");
					return;
				}

				vm.phuyu_additem(producto, producto.precio);
			});
		},
		phuyu_enfocar_producto_rapido: function(texto) {
			this.$nextTick(function() {
				var $select = $("#producto_rapido_select");
				if (!$select.length || !$select.data("select2")) {
					return;
				}

				$select.val(null).trigger("change");
				$select.select2("open");
				window.setTimeout(function() {
					var $search = $(".select2-container--open .select2-search__field");
					if (texto) {
						$search.val(texto).trigger("input");
					}
					$search.focus();
				}, 30);
			});
		},
		phuyu_buscar_agregar_producto_rapido: function(texto) {
			var termino = String(texto || "").trim();
			if (termino == "" || this.producto_rapido_procesando) {
				return false;
			}

			this.producto_rapido_procesando = true;
			this.$http.get(url + "almacen/productos/buscar_codigobarra/" + encodeURIComponent(termino)).then(function(data) {
				if (data.body.cantidad == 1) {
					var producto = data.body.info[0];
					producto.precio = data.body.precio;
					$("#producto_rapido_select").select2("close");
					this.phuyu_agregar_producto_rapido(producto);
					this.producto_rapido_procesando = false;
					return;
				}

				this.phuyu_buscar_agregar_producto_rapido_lista(termino);
			}, function() {
				this.phuyu_buscar_agregar_producto_rapido_lista(termino);
			});
		},
		phuyu_buscar_agregar_producto_rapido_lista: function(termino) {
			this.$http.post(url + "almacen/productos/buscar_salidas", {
				buscar: termino,
				pagina: 1
			}).then(function(data) {
				var lista = data.body.lista || [];
				var exactos = lista.filter(function(producto) {
					return this.phuyu_producto_rapido_match_exacto(producto, termino);
				}, this);
				var producto = null;

				if (exactos.length == 1) {
					producto = exactos[0];
				} else if (lista.length == 1) {
					producto = lista[0];
				}

				if (producto) {
					$("#producto_rapido_select").select2("close");
					this.phuyu_agregar_producto_rapido(producto);
					this.producto_rapido_procesando = false;
					return;
				}

				if (lista.length == 0) {
					phuyu_sistema.phuyu_noti("PRODUCTO NO ENCONTRADO", "Revise el codigo o nombre ingresado", "warning");
				} else {
					phuyu_sistema.phuyu_noti("SE ENCONTRARON VARIOS PRODUCTOS", "Seleccione uno de la lista", "info");
				}
				this.phuyu_enfocar_producto_rapido(termino);
				this.producto_rapido_procesando = false;
			}, function() {
				phuyu_sistema.phuyu_error();
				this.phuyu_enfocar_producto_rapido(termino);
				this.producto_rapido_procesando = false;
			});
		},
		phuyu_agregar_producto_rapido: function(producto) {
			if (parseInt(producto.controlarseries) === 1) {
				phuyu_sistema.phuyu_noti("SELECCIONAR SERIE", "Use el boton Productos para elegir la serie del producto", "info");
				return false;
			}

			this.phuyu_additem(producto, producto.precio);
			$("#producto_rapido_select").val(null).trigger("change");
		},
		phuyu_controla_stock_producto: function(producto) {
			return parseInt(this.stockalmacen || 0) === 1 && parseInt(producto.controlstock || producto.control || 0) === 1;
		},
		phuyu_stock_disponible_producto: function(producto) {
			var stock = parseFloat(producto.stock);
			return isNaN(stock) ? 0 : stock;
		},
		phuyu_cantidad_actual_producto: function(producto) {
			var total = 0;
			(this.detalle || []).forEach(function(item) {
				if (item.codproducto == producto.codproducto && item.codunidad == producto.codunidad) {
					total += parseFloat(item.cantidad || 0);
				}
			});
			return total;
		},
		phuyu_validar_stock_para_agregar: function(producto, cantidadAgregar) {
			if (!this.phuyu_controla_stock_producto(producto)) {
				return true;
			}

			var stock = this.phuyu_stock_disponible_producto(producto);
			var cantidadActual = this.phuyu_cantidad_actual_producto(producto);
			var cantidadNueva = cantidadActual + parseFloat(cantidadAgregar || 1);

			if (stock <= 0 || cantidadNueva > stock) {
				phuyu_sistema.phuyu_noti(
					"STOCK INSUFICIENTE",
					producto.descripcion + " tiene stock " + stock + ". No se puede agregar a la salida.",
					"warning"
				);
				return false;
			}

			return true;
		},
		phuyu_preparar_unidad_base_producto: function(producto) {
			if (producto.codunidad || !producto.unidades) {
				return;
			}

			var unidades = (producto.unidades || "").split(";");
			for (var i = 0; i < unidades.length; i++) {
				var factores = (unidades[i] || "").split("|");
				if (factores[8] == 1) {
					producto.codunidad = factores[0];
					producto.unidad = factores[1];
					producto.afectacionigv = factores[14];
					producto.factor = factores[8];
					return;
				}
			}
		},
		phuyu_additem_originalsalidas: function(producto){

			var existeproducto = this.detalle.filter(function(p){
			    if(p.codproducto == producto.codproducto && p.codunidad == producto.codunidad ){
			    	p.cantidad = p.cantidad + 1; return p;
			    };
			});

		    if (existeproducto.length==0) {
		    	var unidades = []; var factores = []; var logo = []; arreglo = [];
		    	unidades = (producto.unidades).split(";");

		    	for (var i = 0; i < unidades.length; i++) {
                    factores = (unidades[i]).split("|");
		    		logo = {descripcion:factores[1],codunidad:factores[0],factor:factores[8]};
		    		this.putunidades.push(logo)
		    		if(factores[8]==1){
		    			producto.codunidad = factores[0];
		    			producto.unidad = factores[1];
		    			producto.afectacionigv = factores[14];
		    		}
		    	}

		    	producto.preciosinigv = producto.precio;
				producto.valorventa =producto.precio;
		    	producto.igv = 0; var porcentaje = 1;
				if (producto.afectacionigv==10) {
					var porcentaje = (1 + this.igvsunat) / 100;
					producto.preciosinigv = Number((producto.precio / porcentaje).toFixed(4));
					producto.valorventa = Number((producto.precio / porcentaje).toFixed(2));
					producto.igv = Number((producto.precio - producto.valorventa).toFixed(2));
				}

				this.detalle.push({
					"itemorigen":0,
					"codproducto":producto.codproducto,
					"producto":producto.descripcion,
					"codunidad":producto.codunidad,
					"unidades": this.putunidades,
					"cantidad":1,
					"stock":producto.stock,
					"control":producto.controlstock,
					"precio":parseFloat(producto.precio).toFixed(2),
					"preciorefunitario":producto.precio,
					"subtotal":parseFloat(producto.precio).toFixed(2),
					"unidad": producto.unidad,
					"igv":producto.igv
					,"valorventa":producto.valorventa,
					"codafectacionigv": producto.afectacionigv
				});

				this.phuyu_calcular(producto,1);
				this.putunidades = [];
		    }else{
		    	this.phuyu_calcular(existeproducto[0],3);
		    }
		},
		phuyu_additem: function(producto,precio){
			 console.log("producto desde salida almacen:", producto);	
			this.phuyu_preparar_unidad_base_producto(producto);

			if (!this.phuyu_validar_stock_para_agregar(producto, 1)) {
				return false;
			}

			var existe_item = [];
			if ($("#itemrepetir").val()==0) {
				var existe_item = this.detalle.filter(function(p){
				    if(p.codproducto == producto.codproducto && p.codunidad == producto.codunidad ){
				    	return p;
				    }
				});
			}

			if (existe_item.length > 0 && $("#itemrepetir").val()==0) {
				existe_item[0].cantidad = Number(existe_item[0].cantidad || 0) + 1;
				this.phuyu_feedback_producto_rapido("Cantidad actualizada: " + existe_item[0].producto);
				this.phuyu_calcular(existe_item[0]);
				return;
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
		    			producto.afectacionigv = factores[14];
		    			producto.factor = factores[8];
		    		}
		    	}

		    	producto.preciooriginal = precio;
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

				producto.control = 0;
				if (this.stockalmacen==1) {
					if (producto.controlstock==1) {
						producto.control = 1;
					}
				}

				if (producto.controlarseries == 1) {
				let serieYaExiste = this.detalle.some(detalle => 
					detalle.serie_seleccionada && 
					detalle.serie_seleccionada.id_serie == producto.serie_seleccionada.id_serie
				);
				
				if (serieYaExiste) {
					swal({
						title: "❌ ERROR", 
						text: "La serie " + producto.serie_seleccionada.serie_codigo + " ya está en la lista", 
						icon: "error",
						button: false,
						timer: 1500
					});
					return false;
				}
			}

				

				this.detalle.push({
					itemorigen:0,
					codproducto: producto.codproducto, 
					producto: producto.descripcion, 
					codunidad: producto.codunidad,
					unidades: this.putunidades,
					unidad: producto.unidad, 
					cantidad:1, 
					controlarseries: producto.controlarseries,
					stock:parseFloat(producto.stock).toFixed(2), 
					control:producto.control,
					factor: producto.factor,
					preciobruto: producto.preciosinigv,
					preciosinigv: producto.preciosinigv, 
					precio: producto.precio,
					preciorefunitario: producto.precio,
					preciocredito:producto.preciocredito,
					porcdescuento: 0, 
					descuento: 0,
					codafectacionigv: producto.afectacionigv,
					igv: producto.igv, 
					conicbper: producto.afectoicbper,
					icbper: producto.icbper,
					valorventa: producto.valorventa, 
					subtotal:parseFloat(producto.precio).toFixed(2),
					subtotal_tem:producto.subtotal, 
	
					calcular: producto.calcular,
					preciooriginal:producto.preciooriginal,
					precioventa:producto.precioventa,
					descripcion: producto.controlarseries == 1 ? 'SERIE/CODIGO : ' + producto.serie_seleccionada.serie_codigo : producto.descripcion,
    				serie_seleccionada: producto.controlarseries == 1 ? producto.serie_seleccionada : null,
				});
				console.log("detalle despues de agregar producto:", this.detalle);
				this.phuyu_feedback_producto_rapido("Agregado: " + producto.descripcion);
				
			//	this.phuyu_totales();
				this.phuyu_calcular(producto,1);
				this.putunidades = [];
		    }else{
		    	this.phuyu_calcular(existe_item[0]);
		    }
		},
		phuyu_feedback_producto_rapido: function(mensaje) {
			window.clearTimeout(this.producto_rapido_feedback_timer);
			var $feedback = $("#phuyu_producto_feedback");
			if ($feedback.length) {
				$feedback.find("span").text(mensaje);
				$feedback.stop(true, true).fadeIn(120);
			}
			this.producto_rapido_feedback_timer = window.setTimeout(function() {
				$("#phuyu_producto_feedback").fadeOut(180);
			}, 1400);
		},
		informacion_unidad: function(index,producto,val){
			var codunidad = this.detalle[index].codunidad;
			//console.log(codunidad)
            var codproducto = producto.codproducto;
            this.$http.post(url+"almacen/productos/informacion_item",{"codunidad": codunidad, "codproducto": codproducto, "salida": 1}).then(function(data){
				//console.log(data)
				producto.preciosinigv = data.body[0].precio; producto.precio = data.body[0].precio; 
		    	producto.valorventa = data.body[0].precio; producto.subtotal = data.body[0].precio;
		    	producto.subtotal_tem = data.body[0].precio;
		    	
		    	this.detalle[index].afectacionigv = 20; this.detalle[index].igv = 0; var porcentaje = 1;
				if (this.detalle[index].afectoigvventa==1) {
					var porcentaje = (1 + this.igvsunat) / 100;

					this.detalle[index].afectacionigv = 10;
					producto.preciosinigv = Number((producto.precio / porcentaje).toFixed(4));
					producto.valorventa = Number((producto.precio / porcentaje).toFixed(2));
					producto.igv = Number((producto.subtotal - producto.valorventa).toFixed(2));
				}
				this.detalle[index].codunidad = codunidad;
				this.detalle[index].stock = data.body[0].stock;
				this.phuyu_itemcalcular(producto,1)
			});
		},
		phuyu_deleteitem: function(index,producto){
			this.phuyu_calcular(producto,2); this.detalle.splice(index,1);
			this.putunidades = [];
		},
		phuyu_calcular: function(producto,tipo){
			if (tipo==1) {
				this.totales.valorventa = Number((this.totales.valorventa + parseFloat(producto.precio) ).toFixed(2));
			}else{
				if (tipo==2) {
					this.totales.valorventa = Number((this.totales.valorventa - producto.subtotal).toFixed(2));
				}else{
					this.totales.valorventa = Number((this.totales.valorventa - producto.subtotal).toFixed(2));
					producto.subtotal = Number((producto.cantidad * producto.precio).toFixed(2));
					this.totales.valorventa = Number((this.totales.valorventa + producto.subtotal).toFixed(2));
				}
			}
			this.totales.importe = Number((this.totales.valorventa + this.totales.igv).toFixed(2));
		},
		phuyu_itemcalcular: function (item,tipoprecio) {
			var porcentaje = 1;
			if (item.codafectacionigv==21) {
				item.preciobruto = 0; item.porcdescuento = 0; item.descuento = 0; item.preciosinigv = 0; item.precio = 0; 
				item.igv = 0; item.valorventa = 0; item.subtotal = 0; 
			}
			if (item.codafectacionigv==10) {
				var porcentaje = (1 + this.igvsunat) / 100;
			}

			if (tipoprecio==-1) {
				item.porcdescuento = Number((item.descuento / item.preciobruto * 100).toFixed(2));
				item.precio = Number((item.preciobruto - item.descuento).toFixed(4)); tipoprecio = 2;
			}
			if (tipoprecio==-2) {
				item.descuento = Number((item.preciobruto * item.porcdescuento / 100).toFixed(4));
				item.precio = Number((item.preciobruto - item.descuento).toFixed(4)); tipoprecio = 2;
			}
			if(tipoprecio==0){
				item.precio = Number((item.preciobruto - item.descuento).toFixed(4));
			}
			
			var descuento = item.descuento;
			if (item.descuento=="") {
				var descuento = 0;
			}
			
			if (tipoprecio==1) {
				item.precio = Number((item.preciosinigv * porcentaje).toFixed(4));
				item.preciobruto = Number((item.precio + descuento).toFixed(4));
			}
			if (tipoprecio==2) {
				item.preciosinigv = Number((item.precio / porcentaje).toFixed(4));
				item.preciobruto = Number((item.precio + descuento).toFixed(4));
			}

			item.icbper = 0;
			if (item.conicbper==1) {
				item.icbper = Number((item.cantidad * this.icbpersunat).toFixed(2));
			}

			item.valorventa = Number((item.cantidad * item.preciosinigv).toFixed(2));
			item.subtotal = Number((item.cantidad * item.precio).toFixed(2));
			item.igv = Number((item.subtotal - item.valorventa).toFixed(2));
			this.phuyu_totales();
		},
		phuyu_totales: function () {
			this.totales.valorventa = 0.00; this.totales.subtotal = 0.00; this.totales.importe = 0.00;
			t = this;
			var detalle = this.detalle.filter(function(p){
				console.log(p.valorventa)
				t.totales.valorventa = Number((t.totales.valorventa + parseFloat(p.subtotal) ).toFixed(2));
			});

			this.totales.importe = Number((this.totales.valorventa).toFixed(2));
		},
		phuyu_guardar: function(){
			if (this.detalle.length==0) {
				phuyu_sistema.phuyu_noti("REGISTRAR UN PRODUCTO EN EL DETALLE","REGISTRAR ITEM PARA LA SALIDA","error"); 
				return false;
			}

			this.campos.fechakardex = $("#fechakardex").val();
			this.estado = 1; phuyu_sistema.phuyu_inicio_guardar("GUARDANDO SALIDA DE ALMACEN . . .");
			
			this.$http.post(url+phuyu_controller+"/guardar", {"campos":this.campos,"detalle":this.detalle,"totales":this.totales}).then(function(data){
				if (data.body=="e") {
					phuyu_sistema.phuyu_alerta("SU SESION DE USUARIO A TERMINADO","DEBE INICIAR SESION NUEVAMENTE","error");
				}else{
					if (data.body.estado==1) {
						phuyu_sistema.phuyu_alerta("SALIDA DE ALMACEN REGISTRADO","SALIDA DE ALMACEN EN EL SISTEMA","success");
						phuyu_sistema.phuyu_modulo();
					}else if(data.body.estado==2){
						var informacion = data.body.informacion;
						var mensaje = '';
						$.each(informacion.producto,function(indice, elemento) {
						  mensaje += '* '+elemento+': '+informacion.stock[indice]+' '+informacion.unidad[indice]+'\n';
						});
                       phuyu_sistema.phuyu_alerta("PRODUCTOS SIN STOCK, STOCK ACTUAL DE LOS PRODUCTOS:",mensaje,"error");
                       this.estado = 0;
					}else{
						phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR SALIDA DE ALMACEN","ERROR DE RED","error");
						this.estado = 0;
					}
				}
				phuyu_sistema.phuyu_fin();
			}, function(){
				phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR SALIDA DE ALMACEN","ERROR DE RED","error");
			});
		},
		phuyu_cerrar: function(){
			phuyu_sistema.phuyu_modulo();
		},
		phuyu_clonar: function(){
			this.titulo = "NUEVA COMPRA";
			this.$http.post(url+phuyu_controller+"/clonar",{"codregistro":phuyu_salidas.registro}).then(function(data){
				var socio = eval(data.body.socio);
				$("#select2-codpersona-container").empty().append(socio[0]["razonsocial"]);
				this.campos.codpersona = socio[0]["codpersona"];

				$("#codpersona").val(socio[0]["codpersona"]);
				this.campos.nrodocumento = socio[0]["documento"];
				this.codtipodocumento = socio[0]["coddocumentotipo"];
				this.campos.cliente = socio[0]["razonsocial"];
				this.campos.direccion = data.body.campos[0].direccion;
				this.campos.condicionpago = data.body.campos[0].condicionpago;
				this.campos.codmovimientotipo = data.body.campos[0].codmovimientotipo;
				this.campos.codcomprobantetipo_ref = data.body.campos[0].codcomprobantetipo_ref;
				this.campos.seriecomprobante_ref = data.body.campos[0].seriecomprobante_ref;
				this.campos.nrocomprobante_ref = data.body.campos[0].nrocomprobante_ref;
				this.campos.codalmacen_ref = data.body.campos[0].codalmacen_ref;
				if(data.body.campos[0].condicionpago==2){
					this.campos.tasainteres = data.body.campos[0].tasainteres;
					this.campos.nrodias = data.body.campos[0].nrodias;
					this.campos.nrocuotas = data.body.campos[0].nrocuotas;
				}
				/* campos:{
					"codkardex":0,"codpersona":2,"retirar":true,"afectacaja":true,"codmovimientotipo":2,"fechacomprobante":"","fechakardex":"",
					"codmoneda":1,"tipocambio":0.00,"codcomprobantetipo":"","seriecomprobante":"","nrocomprobante":"","codconcepto":12,
					"condicionpago":1,"nrodias":30,"nrocuotas":1,"codcreditoconcepto":4,"tasainteres":0,"totalcredito":0,"descripcion":"REGISTRO POR COMPRA"
				}, */
				this.campos.retirar = data.body.campos[0].retirar;
				this.campos.afectacaja = data.body.campos[0].afectacaja;
				$("#fechakardex").val(data.body.campos[0].fechakardex);
				this.campos.codmoneda = data.body.campos[0].codmoneda;
				this.campos.tipocambio = data.body.campos[0].tipocambio;
				this.campos.codcomprobantetipo = data.body.campos[0].codcomprobantetipo;
				this.campos.descripcion = data.body.campos[0].descripcion;

				this.totales.flete = data.body.campos[0].flete;
				this.totales.gastos = data.body.campos[0].gastos;
				this.totales.subtotal = data.body.campos[0].importe;
				this.totales.valorventa = data.body.campos[0].valorventa;
				this.totales.bruto = data.body.campos[0].valorventa;
				this.totales.descuentoglobal = data.body.campos[0].descglobal;
				this.totales.igv = data.body.campos[0].igv;
				this.totales.importe = data.body.campos[0].importe;
				
				/* this.detalle.push({
					"stock":producto.stock,"control":producto.control, "descuentototal":0,"descuento":0,
					"calcular":producto.calcular
				}); */
				
				this.detalle = data.body.detalle;
				this.phuyu_totales()
				phuyu_sistema.phuyu_fin();
			});
		}
	},
	created: function(){
		if (parseInt(phuyu_salidas.registro)!=0) {
			this.phuyu_clonar();
		}else{
			phuyu_sistema.phuyu_fin(); 
		}
		this.$nextTick(function(){
			this.phuyu_inicializar_producto_rapido_select();
		});
	}
});
