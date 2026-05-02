<div id="phuyu_buscar">
  <!-- Encabezado con búsqueda y botón nuevo producto -->
  <div class="row g-3 mb-4 align-items-center">
    <div class="col-md-10 col-9">
      <div class="position-relative">
        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
        <input type="text" class="form-control ps-5" v-model="buscar" v-on:keyup="phuyu_buscar()" placeholder="BUSCAR PRODUCTO ..." autofocus>
      </div>
    </div>
    <div class="col-md-2 col-3 text-end">
      <button type="button" class="btn btn-warning w-100" v-on:click="phuyu_nuevoproducto()" title="Nuevo Producto">
        <i class="bi bi-plus-circle me-1"></i> <i class="bi bi-box-seam"></i>
      </button>
    </div>
  </div>

  <!-- Tabla de productos (se mantiene la misma estructura) -->
  <div class="table-responsive">
    <table class="table table-hover align-middle table-sm projects" style="font-size: 11px;">
      <tbody>
        <tr v-for="(dato, index) in productos" :key="dato.codproducto">
          <td style="width:100%; cursor:pointer; padding: 12px 10px;" v-on:click="phuyu_seleccionado(index, dato)">
            <div class="row g-2">
              <div class="col-md-9">
                <div class="fw-bold">{{ dato.descripcion }}</div>
                <div>
                  <strong class="text-success fs-6" v-if="rubro==4">S/. {{ dato.preciocosto }}</strong>
                  <strong class="text-success fs-6" v-else>S/. {{ dato.precio }}</strong>
                </div>
                <div>
                  <span :class="dato.stock > 0 ? 'text-success' : 'text-danger'">
                    <i class="bi bi-box-seam me-1"></i> STOCK {{ dato.stock }} {{ dato.unidad }}
                  </span>
                  <span class="text-muted ms-2" v-if="dato.stockproveedor">
                    <i class="bi bi-truck"></i> STOCK P: {{ dato.stockproveedor }}
                  </span>
                </div>
                <small class="text-muted">MARCA: {{ dato.marca }} CARACT. {{ dato.caracteristicas }}</small>
                <div v-if="dato.controlarseries == 1" class="mt-1">
                  <span class="badge bg-info text-white">
                    <i class="bi bi-upc-scan"></i> CONTROLA-SERIES
                  </span>
                </div>
              </div>
              <div class="col-md-3">
                <div class="d-flex flex-wrap gap-2">
                  <button v-if="verprecios==1" type="button" class="btn btn-outline-secondary btn-sm" v-on:click.stop="phuyu_masprecios(dato, index+1)">
                    <i class="bi bi-currency-dollar"></i> MAS PRECIOS
                  </button>
                  <button type="button" class="btn btn-outline-info btn-sm" v-on:click.stop="phuyu_masstock(dato)">
                    <i class="bi bi-boxes"></i> STOCKS
                  </button>
                </div>
              </div>
            </div>

            <!-- Tabla de precios adicionales (se muestra al hacer clic en MAS PRECIOS) -->
            <div v-if="mostrarprecio == index+1" class="mt-3">
              <table class="table table-bordered table-sm bg-light rounded-3" style="font-size: 11px;">
                <thead class="table-light">
                  <tr>
                    <th>PRECIO PUBLICO</th>
                    <th>PRECIO MINIMO</th>
                    <th>PRECIO X MAYOR</th>
                    <th>PRECIO CREDITO</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>S/ {{ dato.precio }}</td>
                    <td>S/ {{ masprecios.preciomin }}</td>
                    <td>S/ {{ masprecios.preciomayor }}</td>
                    <td>S/ {{ masprecios.preciocredito }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </td>
        </tr>
        <tr v-if="productos.length === 0 && !cargando">
          <td class="text-center py-5">
            <i class="bi bi-box-seam fs-1 text-muted"></i>
            <p class="mt-2">No se encontraron productos</p>
          </td>
        </tr>
        <tr v-if="cargando">
          <td class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Cargando...</span>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- Paginación -->
  <div class="row justify-content-center mt-4">
    <div class="col-auto">
      <ul class="pagination mb-0">
        <li class="page-item" :class="{ disabled: paginacion.actual <= 1 }">
          <a class="page-link" href="#" v-if="paginacion.actual > 1" v-on:click.prevent="phuyu_paginacion(paginacion.actual - 1)">
            <i class="bi bi-chevron-left"></i>
          </a>
          <span class="page-link" v-else><i class="bi bi-chevron-left"></i></span>
        </li>

        <li class="page-item" v-for="pag in phuyu_paginas" :class="{ active: pag == phuyu_actual }">
          <a class="page-link" href="#" v-on:click.prevent="phuyu_paginacion(pag)">{{ pag }}</a>
        </li>

        <li class="page-item" :class="{ disabled: paginacion.actual >= paginacion.ultima }">
          <a class="page-link" href="#" v-if="paginacion.actual < paginacion.ultima" v-on:click.prevent="phuyu_paginacion(paginacion.actual + 1)">
            <i class="bi bi-chevron-right"></i>
          </a>
          <span class="page-link" v-else><i class="bi bi-chevron-right"></i></span>
        </li>
      </ul>
    </div>
  </div>

  <!-- ========== MODALES (se mantienen igual, solo se ajustan iconos) ========== -->

  <!-- Modal de precios -->
  <div id="modal_precios" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4">
        <div class="modal-header bg-light">
          <h5 class="modal-title fw-bold"><i class="bi bi-tags me-2"></i> MÁS PRECIOS DEL PRODUCTO</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center p-4">
          <h5 class="mb-3"><b>{{ masprecios.producto }}</b> <span class="badge bg-secondary ms-2">{{ masprecios.unidad }}</span></h5>
          <div class="row g-3">
            <div class="col-md-4 col-6">
              <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                  <h6 class="card-title">Precio Público</h6>
                  <button type="button" class="btn btn-outline-success w-100" v-on:click="phuyu_seleccionado_1(masprecios.precio)">
                    S/ {{ masprecios.precio }}
                  </button>
                </div>
              </div>
            </div>
            <div class="col-md-4 col-6">
              <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                  <h6 class="card-title">Precio Mínimo</h6>
                  <button type="button" class="btn btn-outline-success w-100" v-on:click="phuyu_seleccionado_1(masprecios.preciomin)">
                    S/ {{ masprecios.preciomin }}
                  </button>
                </div>
              </div>
            </div>
            <div class="col-md-4 col-6">
              <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                  <h6 class="card-title">Precio Crédito</h6>
                  <button type="button" class="btn btn-outline-success w-100" v-on:click="phuyu_seleccionado_1(masprecios.preciocredito)">
                    S/ {{ masprecios.preciocredito }}
                  </button>
                </div>
              </div>
            </div>
            <div class="col-md-4 col-6">
              <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                  <h6 class="card-title">Precio x Mayor</h6>
                  <button type="button" class="btn btn-outline-success w-100" v-on:click="phuyu_seleccionado_1(masprecios.preciomayor)">
                    S/ {{ masprecios.preciomayor }}
                  </button>
                </div>
              </div>
            </div>
            <div class="col-md-4 col-6">
              <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                  <h6 class="card-title">Precio Costo</h6>
                  <button type="button" class="btn btn-outline-success w-100" v-on:click="phuyu_seleccionado_1(masprecios.preciocosto)">
                    S/ {{ masprecios.preciocosto }}
                  </button>
                </div>
              </div>
            </div>
            <div class="col-md-4 col-6">
              <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                  <h6 class="card-title">Precio Adicional</h6>
                  <button type="button" class="btn btn-outline-success w-100" v-on:click="phuyu_seleccionado_1(masprecios.precioadicional)">
                    S/ {{ masprecios.precioadicional }}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de salidas de stock -->
  <div id="modal_salidas" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4">
        <div class="modal-header bg-light">
          <h5 class="modal-title fw-bold"><i class="bi bi-arrow-right-circle me-2"></i> SALIDA DE STOCK</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <h4 class="text-center">{{ salida.producto }}</h4>
          <div class="alert alert-warning text-center py-2">STOCK: {{ salida.stock }} {{ salida.unidad }}</div>
          <hr>
          <div class="mb-3">
            <label class="form-label fw-semibold">FECHA KARDEX Y COMPROBANTE</label>
            <input type="text" class="form-control datepicker" id="fechakardex_salida" value="<?php echo date('Y-m-d'); ?>">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">CANTIDAD SALIDA ({{ salida.unidad }})</label>
            <input type="number" class="form-control number" min="0" step="0.01" v-model="salida.cantidad" v-on:keyup="phuyu_unidadingreso()">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">UNIDAD A CONVERTIR</label>
            <select class="form-select" id="codunidad_ingreso" v-model="salida.codunidad_ingreso" v-on:change="phuyu_unidadingreso()">
              <option value="0">SELECCIONE</option>
              <option v-for="dato in unidades" :value="dato.codunidad">{{ dato.descripcion }}</option>
            </select>
          </div>
          <div class="alert alert-info text-center">TOTAL INGRESO: {{ salida.cantidadingreso }}</div>
          <button type="button" class="btn btn-success w-100 btn-salida" v-on:click="phuyu_guardarsalida()">GUARDAR OPERACIÓN DE STOCK</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de stock por almacenes -->
  <div id="modal_stock" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4">
        <div class="modal-header bg-light">
          <h5 class="modal-title fw-bold"><i class="bi bi-building me-2"></i> STOCK EN ALMACENES</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <h4 class="text-center">{{ stock.producto }}</h4>
          <div class="alert alert-warning text-center py-2">STOCK TOTAL: {{ stock.stock }} {{ stock.unidad }}</div>
          <hr>
          <div class="table-responsive">
            <table class="table table-bordered align-middle">
              <thead class="table-light">
                <tr><th>ALMACÉN</th><th>STOCK POR UNIDAD</th></tr>
              </thead>
              <tbody>
                <tr v-for="dato in almacenes">
                  <td class="fw-semibold">{{ dato.almacen }}</td>
                  <td>
                    <span v-for="(unidads, und) in dato.unidades" class="d-block">
                      <strong>{{ unidads.descripcion }}:</strong> {{ unidads.stock }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal para selección de series (se mantiene igual, solo se cambian iconos) -->
  <div class="modal fade" id="listadoSeries" tabindex="-1" aria-labelledby="listadoSeries" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" style="z-index: 1090;">
    <div class="modal-dialog modal-lg">
      <div class="modal-content rounded-4">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title"><i class="bi bi-upc-scan me-2"></i> <strong>Seleccionar Serie</strong></h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <input type="text" class="form-control form-control-sm" placeholder="Buscar serie..." v-model="buscarSerie">
          </div>
          <div class="row g-3">
            <div class="col-md-4 col-sm-6" v-for="serie in listadoSeriesFiltrado" :key="serie.id_serie" v-on:click="SerieSeleccionada(serie)">
              <div class="card text-center series-card shadow-sm border-0 rounded-3" style="cursor: pointer;">
                <div class="card-body p-3">
                  <h6 class="card-title fw-bold mb-1">{{ serie.serie_codigo }}</h6>
                  <p class="card-text mb-1"><small class="text-success">DISPONIBLE</small></p>
                  <i class="bi bi-check-circle-fill text-success fs-4"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- Los scripts de Vue y los métodos se mantienen exactamente igual al final del archivo -->
<!-- Scripts: se mantienen exactamente igual (ya están al final) -->
<script>
    var phuyu_buscar = new Vue({
        el: "#phuyu_buscar",
        data: {
            cargando: true,
            buscar: "",
            rubro: "<?php echo $_SESSION['phuyu_rubro']; ?>",
            almacenControlStock: <?php echo isset($_SESSION['phuyu_stockalmacen']) ? (int)$_SESSION['phuyu_stockalmacen'] : 1; ?>,
            verprecios: 1,
            putunidades: [],
            mostrarprecio: 0,
            productos: [],
            unidades: [],
            productoprecio: {},
            almacenes: [],
            masprecios: {
                producto: "",
                unidad: "",
                precio: 0,
                preciomin: 0,
                preciocredito: 0,
                preciomayor: 0,
                preciocosto: 0,
                precioadicional: 0
            },
            stock: {
                producto: "",
                unidad: "",
                stock: 0
            },
            salida: {
                producto: "",
                unidad: "",
                codproducto: 0,
                codunidad: 0,
                factor: 0,
                preciocosto: 0,
                stock: 0,
                cantidad: 1,
                fechakardex: "",
                codunidad_ingreso: 0,
                factor_ingreso: 0,
                cantidadingreso: 0
            },
            paginacion: {
                "total": 0,
                "actual": 1,
                "ultima": 0,
                "desde": 0,
                "hasta": 0
            },
            offset: 3,
            // Para selección de series
            listadoSeries: [],
            // listadoSeriesFiltrado: [],
            buscarSerie: '',
            // fin selección de series
            ProductoSelecionado: {},

        },
        computed: {
            phuyu_actual: function() {
                return this.paginacion.actual;
            },
            phuyu_paginas: function() {
                if (!this.paginacion.hasta) {
                    return [];
                }
                var desde = this.paginacion.actual - this.offset;
                if (desde < 1) {
                    desde = 1;
                }
                var hasta = desde + (this.offset * 2);
                if (hasta >= this.paginacion.ultima) {
                    hasta = this.paginacion.ultima;
                }

                var paginas = [];
                while (desde <= hasta) {
                    paginas.push(desde);
                    desde++;
                }
                return paginas;
            },
            // AGREGAR ESTE COMPUTED PARA FILTRAR SERIES
            listadoSeriesFiltrado: function() {

                console.log("Filtro de series activado:", this.buscarSerie);
                // Paso 1: Si no hay texto de búsqueda, devuelve todas las series
                if (!this.buscarSerie) return this.listadoSeries;

                // Paso 2: Convierte el texto de búsqueda a minúsculas
                const termino = this.buscarSerie.toLowerCase();

                // Paso 3: Filtra las series que incluyen el término de búsqueda
                return this.listadoSeries.filter(serie =>
                    serie.serie_codigo.toLowerCase().includes(termino)
                );
            }

        },
        methods: {

            phuyu_nuevoproducto: function() {
                $(".compose").removeClass("col-md-4").addClass("col-md-9");
                phuyu_sistema.phuyu_loader("phuyu_formulario", 180);
                this.$http.post(url + "almacen/productos/nuevo").then(function(data) {
                    $("#phuyu_formulario").empty().html(data.body);
                    phuyu_sistema.phuyu_finloader("phuyu_formulario");
                }, function() {
                    phuyu_sistema.phuyu_error();
                    phuyu_sistema.phuyu_finloader("phuyu_formulario");
                });
            },
            phuyu_productos: function() {


                var buscar = "buscar_salidas";
                if (phuyu_controller == "almacen/ingresos" || phuyu_controller == "compras/compras" || phuyu_controller == "compras/pedidos" || phuyu_controller == "compras/proformas") {
                    var buscar = "buscar_ingresos";
                    this.verprecios = 0;
                }


                this.cargando = true;
                this.$http.post(url + "almacen/productos/" + buscar, {
                    "buscar": this.buscar,
                    "pagina": this.paginacion.actual
                }).then(function(data) {
                    this.productos = data.body.lista;
                    this.paginacion = data.body.paginacion;
                    this.cargando = false;
                }, function() {
                    phuyu_sistema.phuyu_error();
                    this.cargando = false;
                });
            },
            phuyu_buscar: function() {
                this.paginacion.actual = 1;
                this.phuyu_productos();
            },
            phuyu_paginacion: function(pagina) {
                this.paginacion.actual = pagina;
                this.phuyu_productos();
            },
            phuyu_seleccionado_07022026: async function(index, producto) {

                // inf para identificar si es egreso o ventas asi debe selecionar la serie de producto 
                if ((phuyu_controller == 'ventas/ventas' || phuyu_controller == 'almacen/salidas') && producto.controlarseries == 1) {
                    // console.log("ENTRO A VENTAS O EGRESOS", phuyu_controller);
                    let detalleActual = phuyu_operacion.detalle || [];
                    // Lista completa de series del producto
                    let listaSeriesSinFiltro = producto.series;
                    // Productos del mismo tipo que ya están en el detalle
                    let FiltroProductos = detalleActual.filter(dp => dp.codproducto == producto.codproducto);
                    // Filtrar: quitar las series que ya están en FiltroProductos
                    this.listadoSeries = listaSeriesSinFiltro.filter(serie => {
                        // Verificar si esta serie ya existe en los productos filtrados
                        let serieYaExiste = FiltroProductos.some(productoDetalle =>
                            productoDetalle.serie_seleccionada &&
                            productoDetalle.serie_seleccionada.id_serie == serie.id_serie
                        );
                        // Mantener solo las series que NO existen
                        return !serieYaExiste;
                    });
                    console.log("Series originales:", listaSeriesSinFiltro);
                    console.log("Productos en detalle:", FiltroProductos);
                    console.log("Series disponibles:", this.listadoSeries);
                    // this.listadoSeries = producto.series;
                    this.listadoSeriesFiltrado = this.listadoSeries;
                    this.ProductoSelecionado = producto;

                    $('#listadoSeries').modal('show');
                    return false;
                }
                //console.log(producto);
                index = index;
                $('.projects tr:eq(' + index + ') td').addClass("columna");
                phuyu_operacion.phuyu_additem(producto, producto.precio);
                timeout = setTimeout(removerColumna, 100, index);
            },
            phuyu_seleccionado: async function(index, producto) {

                const esSalidaOVenta =
                    (phuyu_controller == 'ventas/ventas' || phuyu_controller == 'almacen/salidas');

                const validaStock =
                    esSalidaOVenta &&
                    parseInt(this.almacenControlStock) === 1 &&
                    parseInt(producto.controlstock) === 1;

                if (validaStock && parseFloat(producto.stock) <= 0) {
                    phuyu_sistema.phuyu_alerta(
                        "NO HAY STOCK DISPONIBLE PARA ESTE PRODUCTO",
                        producto.descripcion + " · STOCK: " + producto.stock + " " + producto.unidad,
                        "error"
                    );
                    return false;
                }

                if (esSalidaOVenta && producto.controlarseries == 1) {
                    let detalleActual = phuyu_operacion.detalle || [];
                    let listaSeriesSinFiltro = producto.series || [];
                    let FiltroProductos = detalleActual.filter(dp => dp.codproducto == producto.codproducto);

                    this.listadoSeries = listaSeriesSinFiltro.filter(serie => {
                        let serieYaExiste = FiltroProductos.some(productoDetalle =>
                            productoDetalle.serie_seleccionada &&
                            productoDetalle.serie_seleccionada.id_serie == serie.id_serie
                        );
                        return !serieYaExiste;
                    });

                    this.ProductoSelecionado = producto;
                    $('#listadoSeries').modal('show');
                    return false;
                }

                $('.projects tr:eq(' + index + ') td').addClass("columna");
                phuyu_operacion.phuyu_additem(producto, producto.precio);
                timeout = setTimeout(removerColumna, 100, index);
            },
            SerieSeleccionada_07032026: function(serie) {
                console.log("Serie seleccionada:", serie);
                this.ProductoSelecionado.serie_seleccionada = serie;
                phuyu_operacion.phuyu_additem(this.ProductoSelecionado, this.ProductoSelecionado.precio);
                $('#listadoSeries').modal('hide');
            },
            SerieSeleccionada: function(serie) {
                const esSalidaOVenta =
                    (phuyu_controller == 'ventas/ventas' || phuyu_controller == 'almacen/salidas');

                const validaStock =
                    esSalidaOVenta &&
                    parseInt(this.almacenControlStock) === 1 &&
                    parseInt(this.ProductoSelecionado.controlstock) === 1;

                if (validaStock && parseFloat(this.ProductoSelecionado.stock) <= 0) {
                    phuyu_sistema.phuyu_alerta(
                        "NO HAY STOCK DISPONIBLE PARA ESTE PRODUCTO",
                        this.ProductoSelecionado.descripcion + " · STOCK: " + this.ProductoSelecionado.stock + " " + this.ProductoSelecionado.unidad,
                        "error"
                    );
                    $('#listadoSeries').modal('hide');
                    return false;
                }

                this.ProductoSelecionado.serie_seleccionada = serie;
                phuyu_operacion.phuyu_additem(this.ProductoSelecionado, this.ProductoSelecionado.precio);
                $('#listadoSeries').modal('hide');
            },
            phuyu_masprecios: function(producto, index) {
                this.masprecios.producto = producto.descripcion;
                this.masprecios.unidad = producto.unidad;
                this.masprecios.precio = producto.precio;
                this.masprecios.preciomin = producto.preciomin;
                this.masprecios.preciocredito = producto.preciocredito;
                this.masprecios.preciomayor = producto.preciomayor;
                this.masprecios.preciocosto = producto.precio;
                this.masprecios.preciomayorcre = producto.preciomayorcre;

                this.productoprecio = producto;
                if (this.mostrarprecio == index) {
                    this.mostrarprecio = 0;
                } else {
                    this.mostrarprecio = index;
                }
            },
            phuyu_masstock: function(producto) {
                this.stock.producto = producto.descripcion;
                this.stock.stock = producto.stock;
                this.stock.unidad = producto.unidad;
                $("#modal_stock").modal("show");
                this.$http.get(url + "almacen/productos/stock_almacenes/" + producto.codproducto).then(
                    function(data) {
                        var datos = data.body
                        var filas = [];
                        $.each(datos.almacenes, function(k, v) {
                            var unidades = [];
                            var factores = [];
                            var logo = [];
                            arreglo = [];
                            unidades = (v.unidades).split(";");
                            var funidades = [];

                            for (var i = 0; i < unidades.length; i++) {
                                factores = (unidades[i]).split("|");
                                logo = {
                                    descripcion: factores[1],
                                    codunidad: factores[0],
                                    factor: factores[8],
                                    stock: factores[3]
                                };
                                funidades.push(logo)
                            }
                            this.putunidades = funidades;
                            filas.push({
                                almacen: v.almacen,
                                unidades: this.putunidades
                            });
                            this.putunidades = [];
                        });



                        this.almacenes = filas;
                        $("#modal_stock").modal({
                            backdrop: 'static',
                            keyboard: false
                        });
                    });
            },
            phuyu_seleccionado_1: function(precio) {
                phuyu_operacion.phuyu_additem(this.productoprecio, precio);
                $("#modal_precios").modal("hide");
            },
            phuyu_salida: function(producto) {
                this.salida.producto = producto.descripcion;
                this.salida.unidad = producto.unidad;
                this.salida.codproducto = producto.codproducto;
                this.salida.codunidad = producto.codunidad;
                this.salida.factor = producto.factor;
                this.salida.preciocosto = producto.precio;
                this.salida.stock = producto.stock;
                this.salida.cantidad = 1;
                this.salida.codunidad_ingreso = 0;
                this.salida.factor_ingreso = 0;
                this.salida.cantidadingreso = 0;

                this.$http.get(url + "almacen/productos/unidades_venta/" + producto.codproducto + "/" +
                    producto.factor).then(function(data) {
                    this.unidades = data.body;
                    $(".btn-salida").html("GUARDAR OPERACION DE STOCK").removeAttr("disabled");
                    $("#modal_salidas").modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                });
            },
            phuyu_unidadingreso: function() {
                that = this;
                var existe_factor = this.unidades.filter(function(u) {
                    if (u.codunidad == that.salida.codunidad_ingreso) {
                        that.salida.factor_ingreso = u.factor;
                        return u;
                    };
                });
                this.salida.cantidadingreso = 0;
                if (this.salida.factor_ingreso > 0) {
                    this.salida.cantidadingreso = this.salida.cantidad * this.salida.factor / this.salida
                        .factor_ingreso;
                }
            },
            phuyu_guardarsalida: function() {
                if ($("#codunidad_ingreso").val() == 0 || $("#codunidad_ingreso").val() == "") {
                    phuyu_sistema.phuyu_alerta("SELECCIONE UNIDAD MEDIDA A CONVERTIR", "", "error");
                    return false;
                }
                if (this.salida.cantidad == "") {
                    phuyu_sistema.phuyu_alerta("INGRESAR LA CANTIDAD A DAR SALIDA", "", "error");
                    return false;
                }
                if (parseFloat(this.salida.stock) < parseFloat(this.salida.cantidad)) {
                    phuyu_sistema.phuyu_alerta("LA CANTIDAD EN STOCK SOLO ES " + this.salida.stock + " " +
                        this.salida.unidad, "", "error");
                } else {
                    this.salida.fechakardex = $("#fechakardex_salida").val();
                    $(".btn-salida").html("<i class='fa fa-spinner fa-spin'></i> GUARDANDO OPERACION").attr(
                        "disabled", "true");
                    this.$http.post(url + "almacen/salidas/guardar_operacionstock", this.salida).then(
                        function(data) {
                            if (data.body == 1) {
                                phuyu_sistema.phuyu_alerta("OPERACION GUARDADA CORRECTAMENTE", "",
                                    "success");
                                this.phuyu_productos();
                            } else {
                                phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS", "ERROR DE RED",
                                    "error");
                            }
                            $("#modal_salidas").modal("hide");
                        },
                        function() {
                            phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS", "ERROR DE RED",
                                "error");
                            $("#modal_salidas").modal("hide");
                        });
                }
            },
            phuyu_cerrar: function() {
                $(".compose").slideToggle();
            },

        },
        created: function() {
            this.phuyu_productos();
        }
    });
</script>

<script>
    if (typeof AcornIcons !== 'undefined') {
        new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
        const icons = new Icons();
    }

    function removerColumna(index) {
        $('.projects tr:eq(' + index + ') td').removeClass("columna");
    }
</script>
