var phuyu_operacion = new Vue({
	el: "#phuyu_operacion",
	data: {
		campos: {
			"codkardex_ref": 0, "codmovimientotipo": "", "codpersona": 1, "cliente": $("#codpersona option:selected").text(), "codcomprobantetipo": "", "seriecomprobante": "", "codalmacen_ref": "",
			"codcomprobantetipo_ref": 0, "seriecomprobante_ref": "", "nrocomprobante_ref": "", "descripcion": "", "fechakardex": ""
		},
		estado: 0, kardex_id: 0, igvsunat: $("#igvsunat").val(), detalle: [], detalle_prestamo: [], putunidades: [], totales: { "valorventa": 0.00, "igv": 0.00, "importe": 0.00 },
		productoSeleccionado: {},
		nuevaSerie: null,
		producto_rapido_procesando: false,
	},
	methods: {

			eliminarSerie(index) {
			const ps = this.productoSeleccionado;
			if (!ps || !Array.isArray(ps.series)) return;
			if (index < 0 || index >= ps.series.length) return;
			if (ps.index == null || !this.detalle[ps.index]) return;

			// 1) Crea copia y elimina en la copia (no sobre el array compartido)
			const nuevasSeries = ps.series.slice();
			nuevasSeries.splice(index, 1);

			// 2) Actualiza el modal con copia
			ps.series = nuevasSeries;

			// 3) Actualiza el detalle con copia y fuerza reactividad
			this.$set(this.detalle[ps.index], "series", nuevasSeries);
			

			this.detalle[ps.index].cantidad = nuevasSeries.length;
			this.detalle[ps.index].subtotal = parseFloat(this.detalle[ps.index].precio) * nuevasSeries.length;
			const item = this.detalle[ps.index];
			this.phuyu_calcular(item);

			phuyu_sistema.phuyu_noti(
				"Serie eliminada",
				"La serie se eliminó correctamente.",
				"warning"
			);

		},


		async agregarSerie_original() {
			if (!this.nuevaSerie) {
				phuyu_sistema.phuyu_noti("Serie requerida", "Ingrese el número de serie", "warning");
				return;
			}

			const serie = this.nuevaSerie.trim().toUpperCase();

			if (this.productoSeleccionado.series.length === 0) {
				this.agregarSerieLista(serie, "Primera serie agregada");
				return;
			}

			if (this.productoSeleccionado.series.some(s => s.serie_codigo === serie)) {
				phuyu_sistema.phuyu_noti("Serie duplicada", "Ya existe en la lista", "info");
				return;
			}

			this.agregarSerieLista(serie, "Serie agregada");
		},
		async agregarSerie() {

			if (!this.nuevaSerie) {
				phuyu_sistema.phuyu_noti("Serie requerida", "Ingrese el número de serie", "warning");
				return false;
			}
			const serie = this.nuevaSerie.trim().toUpperCase();

			resp = await this.$http.post(url + "almacen/productos/buscar_serie",{ serie : serie });
			if(resp.body.existe){
				phuyu_sistema.phuyu_noti("Serie duplicada", resp.body.mensaje , "info");
				return false;
			}
			if (this.productoSeleccionado.series.some(s => s.serie_codigo === serie)) {
				phuyu_sistema.phuyu_noti("Serie duplicada", "Ya existe en la lista", "info");
				return false;
			}

			this.agregarSerieLista(serie, "Serie agregada");

		},

		agregarSerieLista(serie, mensaje) {
			this.productoSeleccionado.series.push({ serie_codigo: serie });
			this.detalle[this.productoSeleccionado.index].series = this.productoSeleccionado.series;
			// RECALCULAO DEL SUB TOTAL DEL ITEM
			this.detalle[this.productoSeleccionado.index].cantidad = this.detalle[this.productoSeleccionado.index].series.length;
			this.phuyu_calcular(this.detalle[this.productoSeleccionado.index]);
			// FIN RECALCULAO DEL SUB TOTAL DEL ITEM
			this.nuevaSerie = '';
			phuyu_sistema.phuyu_noti("Éxito", mensaje, "success");
		},
		/* FUNCIONES MODAL SERIES */
		phuyu_ModalSeries(producto, index) {
			this.productoSeleccionado = producto;

			// ✅ CORRECTO: Verificar si existe el array de series
			if (this.detalle[index].series === undefined) {
				this.detalle[index].series = []; // Inicializar si no existe				
			}

			this.productoSeleccionado.series = this.detalle[index].series;
			this.productoSeleccionado.index = index;
			//
			$("#modalSeries").modal('show');
		},









		phuyu_infocliente: function () {
			this.campos.codpersona = $("#codpersona").val();
			this.campos.cliente = $(".select2-selection__rendered").text();
			this.phuyu_prestamos();
		},
		phuyu_verprestamo: function (codkardex) {
			$(".compose").removeClass("col-md-4").addClass("col-md-7");
			$(".compose").slideToggle(); phuyu_sistema.phuyu_loader("phuyu_formulario", 180);
			this.$http.get(url + phuyu_controller + "/verprestamo/" + codkardex).then(function (data) {
				$("#phuyu_formulario").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			}, function () {
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED", "error");
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			});
		},
		phuyu_prestamos: function () {
			if (this.campos.codmovimientotipo == 11) {
				if (this.campos.codpersona != "") {
					this.estado = 1;
					this.$http.get(url + phuyu_controller + "/prestamosotorgados/" + this.campos.codpersona).then(function (data) {
						this.detalle_prestamo = data.body.prestamo; this.estado = 0;
					}, function () {
						phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED", "error");
					});
				} else {
					this.comprobantes = []; phuyu_sistema.phuyu_noti("SELECCIONAR A LA PERSONA Y EL TIPO MOVIMIENTO", "PARA FILTRAR LOS PRESTAMOS", "error");
				}
			} else {

			}
		},
		phuyu_seleccionar: function (datos) {
			$("#" + this.kardex_id).css({ "background-color": "#fff", "color": "#000" });
			this.kardex_id = datos.codkardex;
			$("#" + datos.codkardex).css({ "background-color": "#13a89e", "color": "#fff" });

			this.campos.codkardex_ref = datos.codkardex;
			this.campos.codcomprobantetipo_ref = datos.codcomprobantetipo;
			this.campos.seriecomprobante_ref = datos.seriecomprobante; this.campos.nrocomprobante_ref = datos.nrocomprobante;
			this.campos.cliente = datos.cliente; this.campos.direccion = datos.direccion;

			this.$http.get(url + phuyu_controller + "/detalle/" + datos.codkardex).then(function (data) {
				var productos = eval(data.body.detalle);
				var filas = [];
				$.each(productos, function (k, v) {
					var unidades = []; var factores = []; var logo = []; arreglo = [];
					unidades = (v.unidades).split(";"); var funidades = [];

					for (var i = 0; i < unidades.length; i++) {
						factores = (unidades[i]).split("|");
						logo = { descripcion: factores[1], codunidad: factores[0], factor: factores[8] };
						funidades.push(logo)
						if (factores[8] == 1) {
							v.codunidad = factores[0];
						}
					}
					this.putunidades = funidades;
					v.subtotal = parseFloat(v.cantidad) * parseFloat(v.precio);
					v.valorventa = parseFloat(v.cantidad) * parseFloat(v.preciosinigv);
					filas.push({
						"itemorigen": v.item, "codproducto": v.codproducto, "producto": v.producto, "codunidad": v.codunidad, "unidades": this.putunidades,
						"unidad": v.unidad, "cantidad": v.cantidad, "stock": v.stock, "control": v.controlstock, "precio": parseFloat(v.precio).toFixed(2),
						"preciorefunitario": v.precio, "subtotal": v.subtotal, "valorventa": v.valorventa, "codafectacionigv": v.codafectacionigv, "igv": v.igv,
						"preciosinigv": v.preciosinigv
					});
					this.putunidades = [];
				});

				this.detalle = filas
				var datos = eval(data.body.totales);
				//this.totales.valorventa = datos[0]["valorventa"]; this.totales.igv = datos[0]["igv"]; this.totales.importe = datos[0]["importe"];
				this.phuyu_totales();
			}, function () {
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED", "error");
			});
		},
		phuyu_item: function () {
			$(".compose").removeClass("col-md-7").addClass("col-md-4");
			$(".compose").slideToggle(); $("#phuyu_tituloform").text("BUSCAR PRODUCTO");
			phuyu_sistema.phuyu_loader("phuyu_formulario", 180);

			this.$http.post(url + "almacen/productos/buscar/compras").then(function (data) {
				$("#phuyu_formulario").empty().html(data.body);
				phuyu_sistema.phuyu_finloader("phuyu_formulario");
			}, function () {
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED", "error");
				phuyu_sistema.phuyu_modulo();
			});
		},
		phuyu_escape_producto_rapido: function (texto) {
			return String(texto || "")
				.replace(/&/g, "&amp;")
				.replace(/</g, "&lt;")
				.replace(/>/g, "&gt;")
				.replace(/"/g, "&quot;")
				.replace(/'/g, "&#039;");
		},
		phuyu_producto_rapido_match_exacto: function (producto, texto) {
			var termino = String(texto || "").trim().toUpperCase();
			return termino != "" && String(producto.codigo || "").trim().toUpperCase() === termino;
		},
		phuyu_formato_producto_rapido: function (resultado) {
			if (resultado.loading) {
				return resultado.text;
			}

			var producto = resultado.producto || resultado;
			return '' +
				'<div class="phuyu-product-result">' +
					'<div>' +
						'<div class="phuyu-product-result-name">' + this.phuyu_escape_producto_rapido(producto.descripcion) + '</div>' +
						'<div class="phuyu-product-result-meta">' + this.phuyu_escape_producto_rapido(producto.codigo) + ' · Stock ' + this.phuyu_escape_producto_rapido(producto.stock) + '</div>' +
					'</div>' +
					'<div class="phuyu-product-result-price">S/. ' + this.phuyu_escape_producto_rapido(producto.precio) + '</div>' +
				'</div>';
		},
		phuyu_inicializar_producto_rapido_select: function () {
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
					url: url + "almacen/productos/buscar_ingresos",
					type: "POST",
					dataType: "json",
					contentType: "application/json",
					processData: false,
					delay: 120,
					data: function (params) {
						return JSON.stringify({
							buscar: params.term || "",
							pagina: params.page || 1
						});
					},
					processResults: function (data, params) {
						params.page = params.page || 1;
						var termino = String(params.term || "").trim().toUpperCase();
						var lista = data.lista || [];

						lista.sort(function (a, b) {
							var aExacto = vm.phuyu_producto_rapido_match_exacto(a, termino) ? 0 : 1;
							var bExacto = vm.phuyu_producto_rapido_match_exacto(b, termino) ? 0 : 1;
							return aExacto - bExacto;
						});

						return {
							results: lista.map(function (producto) {
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
					inputTooShort: function () { return "Escriba al menos 1 caracter"; },
					searching: function () { return "Buscando productos..."; },
					noResults: function () { return "No se encontraron productos"; },
					loadingMore: function () { return "Cargando mas productos..."; }
				},
				escapeMarkup: function (markup) {
					return markup;
				},
				templateResult: function (resultado) {
					return vm.phuyu_formato_producto_rapido(resultado);
				},
				templateSelection: function (resultado) {
					return resultado.text || "Buscar producto";
				}
			});

			$select.next(".select2-container").addClass("phuyu-product-select2");
			$select.off("select2:open.phuyuRapido").on("select2:open.phuyuRapido", function () {
				window.setTimeout(function () {
					var $search = $(".select2-container--open .select2-search__field");
					$search.off("keydown.phuyuRapido").on("keydown.phuyuRapido", function (evento) {
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
			$select.off("select2:select.phuyuRapido").on("select2:select.phuyuRapido", function (evento) {
				var producto = evento.params.data.producto;
				if (!producto) {
					return;
				}

				$select.val(null).trigger("change");
				vm.phuyu_additem(producto);
			});
		},
		phuyu_enfocar_producto_rapido: function (texto) {
			this.$nextTick(function () {
				var $select = $("#producto_rapido_select");
				if (!$select.length || !$select.data("select2")) {
					return;
				}

				$select.val(null).trigger("change");
				$select.select2("open");
				window.setTimeout(function () {
					var $search = $(".select2-container--open .select2-search__field");
					if (texto) {
						$search.val(texto).trigger("input");
					}
					$search.focus();
				}, 30);
			});
		},
		phuyu_buscar_agregar_producto_rapido: function (texto) {
			var termino = String(texto || "").trim();
			if (termino == "" || this.producto_rapido_procesando) {
				return false;
			}

			this.producto_rapido_procesando = true;
			this.$http.get(url + "almacen/productos/buscar_codigobarra/" + encodeURIComponent(termino)).then(function (data) {
				if (data.body.cantidad == 1) {
					var producto = data.body.info[0];
					producto.precio = data.body.precio;
					$("#producto_rapido_select").select2("close");
					this.phuyu_additem(producto);
					this.producto_rapido_procesando = false;
					return;
				}

				this.phuyu_buscar_agregar_producto_rapido_lista(termino);
			}, function () {
				this.phuyu_buscar_agregar_producto_rapido_lista(termino);
			});
		},
		phuyu_buscar_agregar_producto_rapido_lista: function (termino) {
			this.$http.post(url + "almacen/productos/buscar_ingresos", {
				buscar: termino,
				pagina: 1
			}).then(function (data) {
				var lista = data.body.lista || [];
				var exactos = lista.filter(function (producto) {
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
					this.phuyu_additem(producto);
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
			}, function () {
				phuyu_sistema.phuyu_error();
				this.phuyu_enfocar_producto_rapido(termino);
				this.producto_rapido_procesando = false;
			});
		},
		phuyu_additem: function (producto) {

			// limpia putunidades por si quedó de antes
			this.putunidades = [];
			const encontrado = this.detalle.find(p => p.codproducto == producto.codproducto && p.codunidad == producto.codunidad);

			// SI YA EXISTE: sube cantidad y recalcula
			if (encontrado) {	encontrado.cantidad = Number(encontrado.cantidad || 0) + 1;	this.phuyu_calcular(encontrado, 3); // recalcula con el item real
				return;
			}
			// --- SI NO EXISTE: arma unidades ---
			let unidades = (producto.unidades || "").split(";");

			for (let i = 0; i < unidades.length; i++) {
				const factores = (unidades[i] || "").split("|");
				const logo = { descripcion: factores[1], codunidad: factores[0], factor: factores[8] };
				this.putunidades.push(logo);

				if (Number(factores[8]) === 1) {
					producto.codunidad = factores[0];
					producto.afectacionigv = factores[14];
					producto.factor = factores[8];
				}
			}

			// --- Normaliza precio ---
			const precio = Number(producto.precio || 0);

			// --- Calcula base/igv según afectación ---
			producto.preciosinigv = precio;
			producto.valorventa = precio;
			producto.igv = 0;

			if (Number(producto.afectacionigv) === 10) {
				const factorIgv = 1 + (Number(this.igvsunat) / 100); // 1.18 si igvsunat=18
				producto.preciosinigv = +(precio / factorIgv).toFixed(4);
				producto.valorventa = +(precio / factorIgv).toFixed(2);
				producto.igv = +(precio - producto.valorventa).toFixed(2);
			}

			// --- Cantidad inicial por series ---
			const cantidad = (Number(producto.controlarseries) === 1) ? 0 : 1;

			// --- Item del detalle (ojo: precio mostrado string, cálculos con números) ---
			const item = {
				itemorigen: 0,
				codproducto: producto.codproducto,
				producto: producto.descripcion,
				codunidad: producto.codunidad,
				unidades: [...this.putunidades], // copia, no referencia mutable
				unidad: producto.unidad,

				cantidad,
				stock: producto.stock,
				control: producto.controlstock,

				precio: precio.toFixed(2),
				preciorefunitario: precio,

				preciosinigv: Number(producto.preciosinigv || 0),
				igv: Number(producto.igv || 0),

				// subtotal inicial (según tu lógica: precio con IGV * cantidad)
				subtotal: +(precio * cantidad).toFixed(2),

				valorventa: Number(producto.valorventa || 0),
				codafectacionigv: producto.afectacionigv,
				factor: producto.factor,
				controlarseries: producto.controlarseries
			};

			this.detalle.push(item);

			// recalcula con el item real del detalle
			this.phuyu_calcular(item, 1);

			// limpia putunidades para el siguiente add
			this.putunidades = [];
		},

		informacion_unidad: function (index, producto, val) {
			var codunidad = this.detalle[index].codunidad;
			//console.log(codunidad)
			var codproducto = producto.codproducto;
			this.$http.post(url + "almacen/productos/informacion_item", { "codunidad": codunidad, "codproducto": codproducto, "salida": 1 }).then(function (data) {
				//console.log(data)
				producto.preciosinigv = data.body[0].precio; producto.precio = data.body[0].precio;
				producto.valorventa = data.body[0].precio; producto.subtotal = data.body[0].precio;
				producto.subtotal_tem = data.body[0].precio;
				producto.factor = data.body[0].factor;
				this.detalle[index].afectacionigv = 20; this.detalle[index].igv = 0; var porcentaje = 1;
				if (this.detalle[index].afectoigvventa == 1) {
					var porcentaje = (1 + this.igvsunat) / 100;

					this.detalle[index].afectacionigv = 10;
					producto.preciosinigv = Number((producto.precio / porcentaje).toFixed(4));
					producto.valorventa = Number((producto.precio / porcentaje).toFixed(2));
					producto.igv = Number((producto.subtotal - producto.valorventa).toFixed(2));
				}
				this.detalle[index].codunidad = codunidad;
				this.detalle[index].stock = data.body[0].stock;
				this.phuyu_itemcalcular(producto, 1)
			});
		},
		phuyu_deleteitem: function (index, producto) {
			
			this.detalle.splice(index, 1);
			this.phuyu_calcular(producto, 2); 
			this.putunidades = [];
			this.phuyu_totales();
		},
		phuyu_itemcalcular: function (item, tipoprecio) {
			var porcentaje = 1;
			if (item.codafectacionigv == 21) {
				item.preciobruto = 0; item.porcdescuento = 0; item.descuento = 0; item.preciosinigv = 0; item.precio = 0;
				item.igv = 0; item.valorventa = 0; item.subtotal = 0;
			}
			if (item.codafectacionigv == 10) {
				var porcentaje = (1 + this.igvsunat) / 100;
			}

			if (tipoprecio == -1) {
				item.porcdescuento = Number((item.descuento / item.preciobruto * 100).toFixed(2));
				item.precio = Number((item.preciobruto - item.descuento).toFixed(4)); tipoprecio = 2;
			}
			if (tipoprecio == -2) {
				item.descuento = Number((item.preciobruto * item.porcdescuento / 100).toFixed(4));
				item.precio = Number((item.preciobruto - item.descuento).toFixed(4)); tipoprecio = 2;
			}
			if (tipoprecio == 0) {
				item.precio = Number((item.preciobruto - item.descuento).toFixed(4));
			}

			var descuento = item.descuento;
			if (item.descuento == "") {
				var descuento = 0;
			}

			if (tipoprecio == 1) {
				item.precio = Number((item.preciosinigv * porcentaje).toFixed(4));
				item.preciobruto = Number((item.precio + descuento).toFixed(4));
			}
			if (tipoprecio == 2) {
				item.preciosinigv = Number((item.precio / porcentaje).toFixed(4));
				item.preciobruto = Number((item.precio + descuento).toFixed(4));
			}

			item.icbper = 0;
			if (item.conicbper == 1) {
				item.icbper = Number((item.cantidad * this.icbpersunat).toFixed(2));
			}

			item.valorventa = Number((item.cantidad * item.preciosinigv).toFixed(2));
			item.subtotal = Number((item.cantidad * item.precio).toFixed(2));
			item.igv = Number((item.subtotal - item.valorventa).toFixed(2));
			this.phuyu_totales();
		},
		phuyu_calcular: function (producto) {
			producto.preciooriginal = producto.precio;
			var porcentaje = 1;
			if (producto.codafectacionigv == 10) {
				var porcentaje = (1 + this.igvsunat) / 100;
			}
			producto.preciosinigv = Number((parseFloat(producto.precio) / porcentaje).toFixed(4));
			//producto.preciobruto = Number((parseFloat(producto.precio)).toFixed(4));

			producto.valorventa = Number((parseFloat(producto.cantidad) * parseFloat(producto.preciosinigv)).toFixed(2));
			producto.subtotal = Number((parseFloat(producto.cantidad) * parseFloat(producto.precio)).toFixed(2));
			producto.igv = Number((parseFloat(producto.subtotal) - parseFloat(producto.valorventa)).toFixed(2));

			this.phuyu_totales();
		},
		phuyu_totales: function () {
			this.totales.valorventa = 0.00; 
			this.totales.igv = 0.00; 
			this.totales.subtotal = 0.00; 
			this.totales.importe = 0.00;
			t = this;
			var detalle = this.detalle.filter(function (p) {
				t.totales.igv = Number((t.totales.igv + parseFloat(p.igv)).toFixed(2));
				t.totales.valorventa = Number((t.totales.valorventa + parseFloat(p.valorventa)).toFixed(2));
			});

			this.totales.importe = Number((this.totales.valorventa + this.totales.igv).toFixed(2));
		},
		phuyu_guardar: function () {
			if (this.detalle.length == 0) {
				phuyu_sistema.phuyu_noti("REGISTRAR UN PRODUCTO EN EL DETALLE", "REGISTRAR ITEM PARA EL INGRESO", "error");
				return false;
			}

			this.campos.fechakardex = $("#fechakardex").val();
			this.estado = 1; phuyu_sistema.phuyu_inicio_guardar("GUARDANDO INGRESO DE ALMACEN . . .");

			this.$http.post(url + phuyu_controller + "/guardar", { "campos": this.campos, "detalle": this.detalle, "totales": this.totales }).then(function (data) {
				if (data.body == "e") {
					phuyu_sistema.phuyu_alerta("SU SESION DE USUARIO A TERMINADO", "DEBE INICIAR SESION NUEVAMENTE", "error");
				} else {
					if (data.body == 1) {
						phuyu_sistema.phuyu_alerta("INGRESO DE ALMACEN REGISTRADO", "INGRESO DE ALMACEN EN EL SISTEMA", "success");
					} else {
						phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR INGRESO DE ALMACEN", "ERROR DE RED", "error");
					}
				}
				phuyu_sistema.phuyu_fin(); phuyu_sistema.phuyu_modulo();
			}, function () {
				phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR INGRESO DE ALMACEN", "ERROR DE RED", "error");
			});
		},
		phuyu_cerrar: function () {
			phuyu_sistema.phuyu_modulo();
		},
		phuyu_clonar: function () {
			this.titulo = "NUEVA COMPRA";
			this.$http.post(url + phuyu_controller + "/clonar", { "codregistro": phuyu_ingresos.registro }).then(function (data) {
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
				if (data.body.campos[0].condicionpago == 2) {
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
	created: function () {
		if (parseInt(phuyu_ingresos.registro) != 0) {
			this.phuyu_clonar();
		} else {
			phuyu_sistema.phuyu_fin();
		}
		this.$nextTick(function () {
			this.phuyu_inicializar_producto_rapido_select();
		});
	}
});
