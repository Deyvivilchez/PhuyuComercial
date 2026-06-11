var phuyu_migrarstock = new Vue({
	el: "#phuyu_form",
	data: {
		estado: 0,
		campos: campos,
		almacenes: typeof almacenesMigrarStock !== "undefined" ? almacenesMigrarStock : [],
		camposSistema: [
			{ key: "codigo", label: "Codigo / SKU", required: true },
			{ key: "cantidad", label: "Cantidad a sumar", required: true },
			{ key: "descripcion", label: "Descripcion / nombre", required: false },
			{ key: "unidad", label: "Unidad / codunidad", required: false },
			{ key: "codigo_barra", label: "Codigo de barra", required: false },
			{ key: "precio_compra", label: "Precio compra", required: false },
			{ key: "precio_venta", label: "Precio venta", required: false },
			{ key: "marca", label: "Marca", required: false },
			{ key: "linea", label: "Linea", required: false },
			{ key: "familia", label: "Familia", required: false }
		],
		alias: {
			codigo: ["codigo", "código", "cod", "sku", "cod_producto", "codigo producto", "codigo_producto", "codproducto", "codigo item", "codigo_barra", "codigo barra", "código barra", "barra", "barcode"],
			cantidad: ["cantidad", "cantidad sumar", "cantidad_sumar", "cantidad a sumar", "stock", "stock inicial", "stock_inicial", "stock sumar", "stock_sumar", "stock a sumar", "existencia", "inventario", "unidades", "qty"],
			descripcion: ["descripcion", "descripción", "producto", "nombre", "detalle", "articulo", "artículo"],
			unidad: ["unidad", "codunidad", "cod_unidad", "unidad medida", "unidad_medida"],
			codigo_barra: ["codigo barra", "código barra", "codigo_barra", "codigobarra", "barra", "barcode"],
			precio_compra: ["precio compra", "precio_compra", "preciocompra", "compra", "costo compra"],
			precio_venta: ["precio venta", "precio_venta", "precioventa", "venta", "pventa", "precio publico", "precio público"],
			marca: ["marca"],
			linea: ["linea", "línea", "rubro"],
			familia: ["familia", "categoria", "categoría"]
		}
	},
	methods: {
		np_formato_migrarstock: function(){
			window.open(url+"almacen/migrarstock/formato", "_blank");
		},
		np_leer_archivo_migrarstock: function(event){
			var file = event.target.files[0];
			if (!file) {
				return;
			}
			if (typeof XLSX === "undefined") {
				phuyu_sistema.phuyu_alerta("No se pudo cargar el lector Excel", "Revise su conexion e intente nuevamente", "error");
				return;
			}

			this.estado = 1;
			phuyu_sistema.phuyu_inicio_guardar("Leyendo Excel...");

			var self = this;
			var reader = new FileReader();
			reader.onload = function(e){
				try {
					var data = new Uint8Array(e.target.result);
					var workbook = XLSX.read(data, { type: "array" });
					var firstSheet = workbook.SheetNames[0];
					var worksheet = workbook.Sheets[firstSheet];
					var rows = XLSX.utils.sheet_to_json(worksheet, { defval: "", raw: false });

					self.campos.filas = rows;
					self.campos.columnas = rows.length ? Object.keys(rows[0]) : [];
					self.campos.preview = [];
					self.campos.resumen = null;
					self.campos.limite_excel = 80;
					self.campos.seleccionar_todo = true;
					self.np_autodetectar_migrarstock();

					if (!rows.length) {
						phuyu_sistema.phuyu_alerta("Excel sin filas", "No se encontraron datos para mostrar", "error");
					} else {
						phuyu_sistema.phuyu_alerta("Excel cargado", "Revise el mapeo y genere la previsualizacion", "success");
					}
				} catch (error) {
					self.np_limpiar_migrarstock(false);
					phuyu_sistema.phuyu_alerta("No se pudo leer el Excel", "Verifique que el archivo sea XLS, XLSX o CSV valido", "error");
				}
				self.estado = 0;
				phuyu_sistema.phuyu_fin();
			};
			reader.onerror = function(){
				self.estado = 0;
				phuyu_sistema.phuyu_fin();
				phuyu_sistema.phuyu_alerta("No se pudo leer el archivo", "Intente seleccionar el archivo nuevamente", "error");
			};
			reader.readAsArrayBuffer(file);
		},
		np_autodetectar_migrarstock: function(){
			var self = this;
			this.camposSistema.forEach(function(campo){
				var detectada = "";
				self.campos.columnas.forEach(function(col){
					if (detectada !== "") {
						return;
					}
					var normalizada = self.np_normalizar_columna_migrarstock(col);
					var opciones = self.alias[campo.key] || [];
					opciones.forEach(function(alias){
						if (detectada === "" && normalizada === self.np_normalizar_columna_migrarstock(alias)) {
							detectada = col;
						}
					});
				});
				if (detectada === "") {
					self.campos.columnas.forEach(function(col){
						if (detectada !== "") {
							return;
						}
						var normalizada = self.np_normalizar_columna_migrarstock(col);
						var opciones = self.alias[campo.key] || [];
						opciones.forEach(function(alias){
							var aliasNormalizado = self.np_normalizar_columna_migrarstock(alias);
							if (
								detectada === "" &&
								aliasNormalizado.length >= 4 &&
								(normalizada.indexOf(aliasNormalizado) !== -1 || aliasNormalizado.indexOf(normalizada) !== -1)
							) {
								detectada = col;
							}
						});
					});
				}
				self.campos.mapeo[campo.key] = detectada;
			});
		},
		np_normalizar_columna_migrarstock: function(texto){
			return String(texto || "")
				.toLowerCase()
				.normalize("NFD")
				.replace(/[\u0300-\u036f]/g, "")
				.replace(/[_-]+/g, " ")
				.replace(/\s+/g, " ")
				.trim();
		},
		np_clase_columna_excel_migrarstock: function(col){
			var normalizada = this.np_normalizar_columna_migrarstock(col);
			var clases = [];

			if (String(col) === String(this.campos.mapeo.codigo || "") || this.np_es_alias_migrarstock(normalizada, this.alias.codigo)) {
				clases.push("sticky-col", "sticky-code");
			}

			if (this.np_es_descripcion_migrarstock(normalizada)) {
				clases.push("sticky-col", "sticky-desc", "sticky-shadow");
			}

			return clases.join(" ");
		},
		np_es_alias_migrarstock: function(normalizada, aliases){
			for (var i = 0; i < aliases.length; i++) {
				if (normalizada === this.np_normalizar_columna_migrarstock(aliases[i])) {
					return true;
				}
			}
			return false;
		},
		np_es_descripcion_migrarstock: function(normalizada){
			var aliasesDescripcion = ["descripcion", "descripción", "producto", "nombre", "detalle", "articulo", "artículo"];
			for (var i = 0; i < aliasesDescripcion.length; i++) {
				var alias = this.np_normalizar_columna_migrarstock(aliasesDescripcion[i]);
				if (normalizada === alias || normalizada.indexOf(alias) !== -1) {
					return true;
				}
			}
			return false;
		},
		np_previsualizar_migrarstock: function(){
			if (!this.campos.codalmacen) {
				phuyu_sistema.phuyu_alerta("Seleccione almacen", "Debe elegir el almacen destino para validar el stock", "error");
				return;
			}
			if (!this.campos.mapeo.codigo || !this.campos.mapeo.cantidad) {
				phuyu_sistema.phuyu_alerta("Mapeo incompleto", "Seleccione en el mapeo las columnas Codigo / SKU y Cantidad a sumar", "error");
				return;
			}
			if (this.campos.crear_productos && !this.campos.mapeo.descripcion) {
				phuyu_sistema.phuyu_alerta("Mapeo incompleto", "Para crear productos faltantes debe asignar Descripcion / nombre", "error");
				return;
			}
			if (!this.campos.filas.length) {
				phuyu_sistema.phuyu_alerta("Seleccione un Excel", "No hay filas cargadas para previsualizar", "error");
				return;
			}

			this.estado = 1;
			phuyu_sistema.phuyu_inicio_guardar("Validando productos y stock...");

			var self = this;
			this.$http.post(url+"almacen/migrarstock/previsualizar_filas", {
				filas: this.campos.filas,
				mapeo: this.campos.mapeo,
				codalmacen: this.campos.codalmacen,
				ignorar_stock_cero: this.campos.ignorar_stock_cero,
				crear_productos: this.campos.crear_productos
			}).then(function(response){
				if (response.body.estado == 1) {
					self.campos.preview = response.body.preview || [];
					self.campos.resumen = response.body.resumen || null;
					self.campos.seleccionar_todo = true;
					phuyu_sistema.phuyu_alerta("Previsualizacion lista", response.body.mensaje || "Revise las filas antes de aplicar", "success");
				} else {
					self.campos.preview = [];
					self.campos.resumen = null;
					phuyu_sistema.phuyu_alerta("No se pudo previsualizar", response.body.mensaje || "Revise el mapeo e intente nuevamente", "error");
				}
				self.estado = 0;
				phuyu_sistema.phuyu_fin();
			}, function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS", "ERROR DE RED", "error");
				self.estado = 0;
				phuyu_sistema.phuyu_fin();
			});
		},
		np_limpiar_migrarstock: function(limpiarInput){
			if (limpiarInput === undefined) {
				limpiarInput = true;
			}
			this.campos.filas = [];
			this.campos.columnas = [];
			this.campos.preview = [];
			this.campos.resumen = null;
			this.campos.limite_excel = 80;
			this.campos.seleccionar_todo = true;
			this.campos.mapeo = {
				codigo: "",
				cantidad: "",
				descripcion: "",
				unidad: "",
				codigo_barra: "",
				precio_compra: "",
				precio_venta: "",
				marca: "",
				linea: "",
				familia: ""
			};
			if (limpiarInput) {
				$("#phuyu_form input[type=file]").val("");
			}
		},
		np_cambio_almacen_migrarstock: function(){
			this.campos.preview = [];
			this.campos.resumen = null;
			this.campos.seleccionar_todo = true;
		},
		np_cambio_crear_productos_migrarstock: function(){
			this.campos.preview = [];
			this.campos.resumen = null;
			this.campos.seleccionar_todo = true;
		},
		np_nombre_almacen_migrarstock: function(){
			var codalmacen = String(this.campos.codalmacen || "");
			for (var i = 0; i < this.almacenes.length; i++) {
				if (String(this.almacenes[i].codalmacen) === codalmacen) {
					return this.almacenes[i].descripcion;
				}
			}
			return "No seleccionado";
		},
		np_filas_excel_visibles_migrarstock: function(){
			return Math.min(this.campos.limite_excel || 0, this.campos.filas.length);
		},
		np_cargar_mas_excel_migrarstock: function(){
			if (!this.campos.filas || !this.campos.filas.length) {
				return;
			}
			this.campos.limite_excel = Math.min((this.campos.limite_excel || 80) + 80, this.campos.filas.length);
		},
		np_estado_mapeo_migrarstock: function(){
			if (!this.campos.mapeo.codigo && !this.campos.mapeo.cantidad) {
				return "Seleccione Codigo / SKU y Cantidad a sumar";
			}
			if (!this.campos.mapeo.codigo) {
				return "Falta asignar Codigo / SKU";
			}
			if (!this.campos.mapeo.cantidad) {
				return "Falta asignar Cantidad a sumar";
			}
			if (this.campos.crear_productos && !this.campos.mapeo.descripcion) {
				return "Para crear faltantes falta asignar Descripcion / nombre";
			}
			return "Mapeo listo para validar";
		},
		np_volver_migrarstock_productos: function(){
			phuyu_controller = "almacen/productos";
			if (window.history && window.history.pushState) {
				window.history.pushState({}, "", url+"phuyu/w/"+phuyu_controller);
			}
			phuyu_sistema.phuyu_modulo();
		},
		np_filas_migrarstock_seleccionadas: function(){
			if (!this.campos.preview) {
				return 0;
			}
			return this.campos.preview.filter(function(fila){
				return fila.valido && fila.seleccionado;
			}).length;
		},
		np_marcar_migrarstock_todos: function(){
			var marcar = this.campos.seleccionar_todo;
			if (!this.campos.preview) {
				return;
			}
			this.campos.preview.forEach(function(fila){
				if (fila.valido) {
					fila.seleccionado = marcar;
				}
			});
		},
		np_toggle_migrarstock_todos: function(){
			if (!this.campos.preview || !this.campos.preview.length) {
				return;
			}
			this.campos.seleccionar_todo = !this.campos.seleccionar_todo;
			this.np_marcar_migrarstock_todos();
		},
		np_procesar_migrarstock: function(){
			var filas = (this.campos.preview || []).filter(function(fila){
				return fila.valido && fila.seleccionado;
			});
			if (filas.length == 0) {
				phuyu_sistema.phuyu_alerta("Debe seleccionar filas", "No hay filas validas seleccionadas para aplicar", "error");
				return;
			}

			this.estado = 1;
			phuyu_sistema.phuyu_inicio_guardar("Aplicando stock seleccionado...");

			var self = this;
			this.$http.post(url+"almacen/migrarstock/procesar_previsualizacion", {
				filas: filas,
				codalmacen: this.campos.codalmacen,
				ignorar_stock_cero: this.campos.ignorar_stock_cero,
				crear_productos: this.campos.crear_productos
			}).then(function(response){
				if (response.body.estado == 1) {
					phuyu_sistema.phuyu_alerta("Stock actualizado correctamente !!!", response.body.mensaje || "Actualizacion realizada correctamente", "success");
					self.campos.preview = [];
					self.campos.resumen = null;
				} else {
					phuyu_sistema.phuyu_alerta("No se pudo actualizar stock !!!", response.body.mensaje || "Revise las filas e intente nuevamente", "error");
				}
				self.estado = 0;
				phuyu_sistema.phuyu_fin();
			}, function(){
				phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS", "ERROR DE RED", "error");
				self.estado = 0;
				phuyu_sistema.phuyu_fin();
			});
		},
		np_activar_scroll_tablas_migrarstock: function(){
			this.$nextTick(function(){
				$("#phuyu_form .table-box").each(function(){
					var tabla = this;
					if ($(tabla).data("drag-scroll")) {
						return;
					}
					$(tabla).data("drag-scroll", true);

					var activo = false;
					var inicioX = 0;
					var scrollInicial = 0;

					$(tabla).on("mousedown", function(e){
						if ($(e.target).is("input, select, button, a, textarea")) {
							return;
						}
						activo = true;
						inicioX = e.pageX - tabla.offsetLeft;
						scrollInicial = tabla.scrollLeft;
						$(tabla).addClass("is-dragging");
					});

					$(tabla).on("mouseleave mouseup", function(){
						activo = false;
						$(tabla).removeClass("is-dragging");
					});

					$(tabla).on("mousemove", function(e){
						if (!activo) {
							return;
						}
						e.preventDefault();
						var x = e.pageX - tabla.offsetLeft;
						tabla.scrollLeft = scrollInicial - ((x - inicioX) * 1.2);
					});

					$(tabla).on("scroll", function(){
						if (!$(tabla).hasClass("excel-preview")) {
							return;
						}
						if (tabla.scrollTop + tabla.clientHeight >= tabla.scrollHeight - 80) {
							phuyu_migrarstock.np_cargar_mas_excel_migrarstock();
						}
					});
				});
			});
		}
	},
	updated: function(){
		this.np_activar_scroll_tablas_migrarstock();
	},
	mounted: function(){
		this.np_activar_scroll_tablas_migrarstock();
	}
});
