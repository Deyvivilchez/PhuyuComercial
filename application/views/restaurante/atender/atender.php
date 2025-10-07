<!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" /> -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet" />
<style>
    .total-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 1030;
        /* Por encima del contenido */
        backdrop-filter: blur(10px);
        /* Efecto glass opcional */
    }

    .chip.active {
        background-color: #007bff;
        color: white !important;
    }

    body {
        background: #f8fafc;
    }

    /* chips */
    .chips {
        overflow: auto;
        white-space: nowrap;
    }

    .chip {
        background: #f1f5f9;
        border: 0;
        margin-right: .5rem;
        border-radius: 999px;
        padding: .35rem .75rem;
    }

    .chip.active {
        background: #0d6efd;
        color: #fff;
    }

    /* mesas */
    /* mesas */
    .mesas-scroll {
        overflow: auto;
        max-height: 35vh;
        /* Computadora por defecto */
    }

    /* Tablet */
    @media (max-width: 1023px) {
        .mesas-scroll {
            max-height: 28vh;
        }
    }

    /* Móvil */
    @media (max-width: 767px) {
        .mesas-scroll {
            max-height: 18vh;
        }
    }

    .mesa-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 10px;
        text-align: center;
        font-weight: 600;
        background: #fff;
        transition: .15s ease;
        cursor: pointer;
    }

    .mesa-libre {
        background: #e8f7ee;
        border-color: #c8ecd4;
    }

    .mesa-ocupada {
        background: #fdecec;
        border-color: #f3c5c5;
    }

    /* ⭐ Mesa seleccionada (CSS) */
    .mesa-activa {
        border: 2px solid #0d6efd !important;
        box-shadow: 0 0 10px rgba(13, 110, 253, 0.45);
        transform: scale(1.03);
    }

    /* productos */
    .producto {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 10px;
        background: #fff;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .producto h6 {
        font-size: .95rem;
        margin: 0;
    }

    .producto .precio {
        font-weight: 700;
    }

    .agotado {
        opacity: .55;
        filter: grayscale(.2);
    }

    /* barra total inferior */
    .total-bar {
        position: sticky;
        bottom: 0;
        z-index: 10;
        box-shadow: 0 -6px 16px rgba(0, 0, 0, .06);
        background: #fff;
    }

    /* Botón circular de configuración */
    .total-bar .btn.rounded-circle {
        box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
    }

    .total-bar .btn.rounded-circle:hover {
        transform: scale(1.05);
    }

    /* scrollbars suaves (opcional) */
    ::-webkit-scrollbar {
        height: 8px;
        width: 8px;
    }

    ::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 999px;
    }
</style>

<div class="card container p-1">
    <div id="phuyu_operacion" class="min-vh-100 d-flex flex-column ">

        <input type="hidden" id="comprobante" value="<?php echo $sucursal[0]['codcomprobantetipo']; ?>">
        <input type="hidden" id="serie" value="<?php echo $sucursal[0]['seriecomprobante']; ?>">
        <input type="hidden" id="stockalmacen" value="<?php echo $_SESSION['phuyu_stockalmacen']; ?>">
        <input type="hidden" id="itemrepetir" value="<?php echo $_SESSION['phuyu_itemrepetir']; ?>">
        <input type="hidden" id="igvsunat" value="<?php echo $_SESSION['phuyu_igv']; ?>">
        <input type="hidden" id="icbpersunat" value="<?php echo $_SESSION['phuyu_icbper']; ?>">
        <input type="hidden" id="fechapedido" value="<?php echo date('Y-m-d'); ?>">
        <input type="hidden" id="sessioncaja" value="<?php echo $_SESSION["phuyu_codcontroldiario"]; ?>">


        <!-- BUSCADOR + FILTROS -->
        <section class="container py-3">

            <div class="input-group mb-2">
                <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                <input v-model="buscar" type="search" class="form-control"
                    placeholder="Buscar producto, plato o bebida…" />
            </div>
            <div class="d-flex gap-2 align-items-center mb-2">
                <select class="form-select " v-model="campos.codambiente" v-on:change="phuyu_mesas()" id="codambiente">

                    <?php foreach ($ambientes as $key => $value) { ?>
                        <option value="<?php echo $value['codambiente']; ?>"><?php echo $value['descripcion']; ?></option>
                    <?php } ?>
                </select>
                <div class="chips ms-auto w-100">
                    <!-- Botón "Todo" -->
                    <button class="chip btn btn-sm text-dark" :class="{ 'active': lineaActiva === 0 }"
                        v-on:click="phuyu_producto(0)">
                        <span class="me-1">🍮</span> Todo
                    </button>

                    <!-- Botones de líneas -->
                    <?php foreach ($lineas as $key => $value) { ?>
                        <button class="chip btn btn-sm text-dark"
                            :class="{ 'active': lineaActiva === <?php echo $value['codlinea']; ?> }"
                            v-on:click="phuyu_producto(<?php echo $value['codlinea']; ?>)">
                            <span class="me-1">🍮</span> <?php echo $value['descripcion']; ?>
                        </button>
                    <?php } ?>
                </div>
            </div>
        </section>

        <!-- MESAS (scroll + selección + infinite) -->
        <section class="container">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold mb-0">Mesas</h6>
                <span class="small text-muted">{{ mesas . length }} mesas</span>
            </div>
            <div ref="mesasScroll" class="mesas-scroll p-1">
                <div class="row g-2">
                    <div v-for="mesa in mesas" :key="mesa.codmesa" class="col-6 col-sm-4">
                        <div class="mesa-card"
                            :class="[mesa.texto === 'LIBRE' ? 'mesa-libre' : 'mesa-ocupada', mesaSeleccionada === mesa.codmesa ? 'mesa-activa' : ''  ]"
                            @click="selectMesa(mesa)">
                            {{ mesa . nromesa }}<br /><small>{{ mesa . texto }}</small>
                        </div>
                    </div>
                </div>
                <div v-if="hasMoreMesas" ref="sentinel" class="text-center py-3 text-muted small">Cargando más…
                </div>
            </div>
        </section>

        <!-- PRODUCTOS -->
        <section class="container mt-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="fw-bold mb-0">Productos</h6>
                <div class="dropdown">
                    <button class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown"><i
                            class="bi bi-sort-down"></i> Ordenar</button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" @click.prevent="orden = 'name'">Nombre</a>
                        </li>
                        <li><a class="dropdown-item" href="#" @click.prevent="orden = 'price'">Precio</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="row g-2">
                <div v-if="productos.length === 0" class="text-center">
                    <div class="spinner-border"></div>
                    <p>Cargando productos...</p>
                </div>
                <div class="contenedor-scroll" style="height: 500px; overflow-y: auto;">
                    <div class="row">
                        <div v-for="p in productosVisibles" :key="p.codproducto" class="col-6 col-md-4 mb-3">
                            <div class="producto" :style="p.stockdisponible <= 0 ? { opacity: '0.7', border: '1px solid red' } : {}">
                                <div>
                                    <h6 class="mb-0">{{ p . descripcion || 'SIN NOMBRE' }}</h6>
                                    <div class="text-muted small">{{ p . marca || 'GENÉRICO' }}</div>
                                    <div class="text-sm"
                                        :style="{ color: p.stockdisponible <= 0 ? 'red' : '#6c757d', fontWeight: p
                                                .stockdisponible <= 0 ? 'bold' : 'normal' }">
                                        {{ p . mostrarstock || 'STOCK: 0' }}
                                    </div>
                                    <div class="text-sm">
                                        <small :class="p.controlstock == 1 ? 'text-success' : 'text-muted'">
                                            {{ p . controlstock == 1 ? '🟢 Stock controlado' : '⚪ Stock libre' }}
                                        </small>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center justify-content-between mt-2">
                                    <span class="precio">S/. {{ (p . precio || 0) . toFixed(2) }}</span>
                                    <button class="btn btn-sm" :class="(p.controlstock == 1 && p.stockdisponible <= 0) ? 'btn-secondary' :
                                        'btn-primary'" :disabled="p.controlstock == 1 && p.stockdisponible <= 0"
                                        @click="agregar(p,p.precio)">
                                        <i :class="(p.controlstock == 1 && p.stockdisponible <= 0) ? 'bi bi-dash-circle' :
                                            'bi bi-plus-circle'"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Spinner de carga automático -->
                    <div v-if="cargando" class="text-center p-3">
                        <div class="spinner-border spinner-border-sm text-primary"></div>
                        <span class="ms-2">Cargando más productos...</span>
                    </div>

                    <!-- Mensaje cuando no hay más productos -->
                    <div v-if="limite >= productos.length && productos.length > 0" class="text-center p-3 text-muted">
                        🎉 Todos los productos cargados
                    </div>
                </div>
            </div>
        </section>

        <!-- BARRA TOTAL -->
        <div class="total-bar fixed-bottom bg-white border-top shadow-sm">
            <div class="container py-2">
                <div class="row align-items-center">
                    <!-- Total -->
                    <div class="col-12 col-md-4 text-center text-md-start mb-2 mb-md-0">
                        <div class="small text-muted">Total del pedido</div>
                        <div class="fs-5 fw-bold">S/. {{ total . toFixed(2) }}</div>
                    </div>

                    <!-- Botones -->
                    <div class="col-12 col-md-8">
                        <div class="d-flex justify-content-center justify-content-md-end gap-2 flex-wrap">
                            <button class="btn btn-outline-secondary btn-sm" @click="verPedido">
                                <i class="bi bi-list-check me-1"></i>
                                Ver pedido
                            </button>
                            <button class="btn btn-success btn-sm" @click="phuyu_cobrar_pedido">
                                <i class="bi bi-cash me-1"></i>
                                Cobrar
                            </button>
                            <div class="dropup">
                                <button
                                    class="btn btn-primary btn-sm rounded-circle d-flex align-items-center justify-content-center"
                                    style="width:40px; height:40px;" data-bs-toggle="dropdown">
                                    <i class="bi bi-gear"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                    <li class="dropdown-header">Operación</li>
                                    <!-- <li><a class="dropdown-item" href="#" @click.prevent="anfitrion">🧑‍💼 Anfitrión</a></li>
                                        <li><a class="dropdown-item" href="#" @click.prevent="ingresoCaja">💵 Ingre. caja</a></li>
                                        <li><a class="dropdown-item" href="#" @click.prevent="egresoCaja">💸 Egre. caja</a></li>
                                        <li><a class="dropdown-item" href="#" @click.prevent="ventaDiaria">📊 Venta diaria</a></li>
                                        <li><hr class="dropdown-divider"></li> -->
                                    <li class="dropdown-header">Pedido</li>
                                    <li><a class="dropdown-item" @click.prevent="guardar">💾 Guardar pedido</a></li>
                                    <li><a class="dropdown-item" @click.prevent="phuyu_atender_pedido">👨‍🍳 Atender pedido</a></li>
                                    <li><a class="dropdown-item" v-on:click="phuyu_avance_pedido()">🧾 Imprimir pre-cuenta</a></li>
                                    <li><a class="dropdown-item" v-on:click="phuyu_comanda()">🖨️ Imprimir comanda</a></li>
                                    <li><a class="dropdown-item text-danger" v-on:click="phuyu_anular_pedido()">❌ Anular pedido</a></li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <!-- <li class="dropdown-header">Caja</li>
                                        <li><a class="dropdown-item" href="#" @click.prevent="balanceCaja">📦 Balance caja</a></li>
                                        <li><a class="dropdown-item fw-semibold" href="#" @click.prevent="precuenta"><i class="bi bi-receipt me-1"></i> Pre-cuenta</a></li> -->
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- OFFCANVAS Pedido -->
        <div class="offcanvas offcanvas-bottom rounded-top" tabindex="-1" id="pedidoCanvas" style="height:70vh">
            <div class="offcanvas-header">
                <h6 class="offcanvas-title">Pedido actual - Mesa {{ DatosMesaSelect . nromesa }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body d-flex flex-column">
                <ul class="list-group list-group-flush flex-grow-1">
                    <li v-if="!items.length" class="list-group-item text-center text-muted">
                        Tu pedido está vacío.
                    </li>
                    <li v-for="(producto, index) in items" :key="producto.codproducto" class="list-group-item">

                        <!-- Fila 1: Nombre y precio -->
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ producto.producto || 'SIN NOMBRE' }}</div>
                                <div class="text-muted small">
                                    S/. {{ Number(producto.precio).toFixed(2) }} c/u
                                    <span v-if="producto.marca && producto.marca !== 'SIN MARCA'"> •
                                        {{ producto . marca }}</span>
                                </div>
                            </div>
                            <div class="fw-bold text-nowrap ms-2">
                                S/. {{ (producto . cantidad * Number(producto.precio).toFixed(2)).toFixed(2) }}
                            </div>
                        </div>

                        <!-- Fila 2: Estado y controles -->
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <!-- Estado -->
                                <button type="button" class="btn btn-xs py-0"
                                    :class="producto.atendido == 1 ? 'btn-danger' : 'btn-success'">
                                    <small>
                                        <i class="fa fa-flag-o"></i>
                                        {{ producto . atendido == 1 ? 'ATENDIDO' : 'PENDIENTE' }}
                                    </small>
                                </button>

                                <!-- Stock info -->
                                <span v-if="producto.controlstock == 1" class="text-muted small">
                                    Stock: {{ producto . stockdisponible }}
                                </span>
                            </div>

                            <!-- Controles de cantidad -->
                            <div class="d-flex align-items-center gap-2">
                                <div class="cantidad d-inline-flex align-items-center gap-1">
                                    <button class="btn btn-outline-secondary btn-sm" @click="dec(index)">
                                        <i class="bi bi-dash"></i>
                                    </button>
                                    <input type="number" v-model.number="producto.cantidad"
                                        @change="validarCantidad(producto, index)"
                                        class="form-control form-control-sm" style="width: 60px; text-align: center"
                                        min="1"
                                        :max="producto.controlstock == 1 ? producto.stockdisponible : null">
                                    <button class="btn btn-outline-secondary btn-sm" @click="inc(index)"
                                        :disabled="producto.controlstock == 1 && producto.cantidad >= producto.stockdisponible">
                                        <i class="bi bi-plus"></i>
                                    </button>
                                </div>
                                <button class="btn btn-outline-danger btn-sm" @click="delItem(index)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </li>
                </ul>
                <div class="pt-2 border-top mt-2">
                    <!-- Fila 1: Total -->
                    <div class="row mb-2">
                        <div class="col-12">
                            <div class="fw-bold text-center text-md-start">Total: <span class="fs-5">S/.
                                    {{ total . toFixed(2) }}</span></div>
                        </div>
                    </div>

                    <!-- Fila 2: Botones -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex gap-1 justify-content-center justify-content-md-start">
                                <button class="btn btn-outline-secondary btn-sm flex-fill"
                                    data-bs-dismiss="offcanvas">
                                    <i class="bi bi-arrow-left me-1"></i>
                                    Más
                                </button>
                                <button class="btn btn-success btn-sm flex-fill" @click="guardar"
                                    :disabled="!items.length">
                                    <i class="bi bi-check-lg me-1"></i>
                                    Guardar
                                </button>
                                <button class="btn btn-primary btn-sm flex-fill" @click="precuenta"
                                    :disabled="!items.length">
                                    <i class="bi bi-printer me-1"></i>
                                    Imprimir
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>




        <div id="modal_atender" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header" style="padding:10px 15px 5px">
                        <h4 class="modal-title">
                            <b style="letter-spacing:3px;">PEDIDO N°: 0000{{ DatosMesaSelect.codpedido }} | MESA {{ DatosMesaSelect.nromesa }}</b>
                        </h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            style="font-size:30px;margin-bottom:0px;">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="x_panel scroll-phuyu"
                            style="height:220px;overflow:auto;overflow-x:hidden;padding:0px;">
                            <table class="table table-bordered" style="font-size: 11px">
                                <thead>
                                    <tr>
                                        <th>DESCRIPCION</th>
                                        <th width="10px">UNIDAD</th>
                                        <th width="10px">CANTIDAD</th>
                                        <th width="10px">ATENDIDO</th>
                                        <th width="10px">ATENDER</th>
                                        <th width="10px" colspan="2">AGREGAR</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(dato,index) in atender">
                                        <td>{{ dato . producto }} - {{ dato . descripcion }}</td>
                                        <td>{{ dato . unidad }}</td>
                                        <td>{{ dato . cantidad }}</td>
                                        <td>{{ dato . atendido }}</td>
                                        <td>
                                            <input type="number" step="0.1" class="form-control number line-success"
                                                v-model.number="dato.atender" min="0" max="dato.cantidad"
                                                readonly>
                                        </td>
                                        <td v-if="dato.cantidad!=dato.atendido">
                                            <button class="btn btn-info btn-xs btn-block" style="margin-bottom:-1px;"
                                                v-on:click="phuyu_mas_menos(dato,1)">
                                                +
                                            </button>
                                        </td>
                                        <td v-if="dato.cantidad!=dato.atendido">
                                            <button class="btn btn-warning btn-xs btn-block" style="margin-bottom:-1px;"
                                                v-on:click="phuyu_mas_menos(dato,2)">
                                                -
                                            </button>
                                        </td>
                                        <td v-if="dato.cantidad==dato.atendido" colspan="2">
                                            <button type="button" class="btn btn-danger btn-xs btn-block" style="margin-bottom:-1px;">ATENDIDO</button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr v-for="dato in totales">
                                        <td colspan="2" align="right"><b>TOTALES</b></td>
                                        <td><b>{{ dato . cantidad }}</b></td>
                                        <td><b>{{ dato . atendido }}</b></td>
                                        <td colspan="3">
                                            <button type="button" class="btn btn-success btn-block btn-sm"
                                                style="margin-bottom:-1px;" v-on:click="phuyu_atender()"
                                                v-bind:disabled="estado==1">GUARDAR ATENCION</button>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <h5 class="text-center"> <b>DETALLE DE LAS ATENCIONES DEL PEDIDO</b> </h5>

                        <div class="x_panel scroll-phuyu"
                            style="height:200px;overflow:auto;overflow-x:hidden;padding:0px;">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>DESCRIPCION</th>
                                        <th width="10px">UNIDAD</th>
                                        <th width="10px">CANTIDAD</th>
                                        <th width="140px">FECHA Y HORA</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="dato in atendidos">
                                        <td><b>{{ dato . producto }} - {{ dato . descripcion }}</b></td>
                                        <td>{{ dato . unidad }}</td>
                                        <td>{{ dato . cantidad }}</td>
                                        <td><b style="color:#d43f3a">{{ dato . fecha }} {{ dato . hora }}</b></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div id="modal_pago" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-body">
                        <form class="x_panel" v-on:submit.prevent="phuyu_pagar()">
                            <div class="d-grid"><a class="btn btn-success btn-block"> <b style="font-size:25px;">
                                        TOTAL VENTA S/. {{ totales . importe }}</b>
                                </a></div> <br>

                            <div class="row form-group">
                                <div class="col-md-12 col-xs-12">
                                    <label>CLIENTE DE LA VENTA</label>
                                    <select class="form-control" name="codpersona" v-model="campos.codpersona"
                                        id="codpersona" required>
                                        <option value="2">CLIENTES VARIOS</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row form-group">
                                <div class="col-md-5 col-xs-12">
                                    <label>TIPO COMPROBANTE</label>
                                    <select class="form-select" name="codcomprobantetipo"
                                        v-model="campos.codcomprobantetipo" required v-on:change="phuyu_series()">
                                        <?php
                                        foreach ($comprobantes as $key => $value) { ?>
                                            <option value="<?php echo $value['codcomprobantetipo']; ?>">
                                                <?php echo $value['descripcion']; ?>
                                            </option>
                                        <?php }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-3 col-xs-12">
                                    <label>SERIE</label>
                                    <select class="form-control" id="seriecomprobante" v-model="campos.seriecomprobante"
                                        v-on:change="phuyu_correlativo()" required>
                                        <option value="">SERIE</option>
                                        <option v-for="dato in series" v-bind:value="dato.seriecomprobante">
                                            {{ dato . seriecomprobante }}
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-4 col-xs-12">
                                    <label>CONDICION PAGO</label>
                                    <select class="form-select" name="condicionpago" v-model="campos.condicionpago" v-on:change="phuyu_condicionpago()" disabled>
                                        <option value="1">CONTADO</option>
                                        <option value="2">CREDITO</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row form-group">
                                <div class="col-md-12 col-xs-12">
                                    <label>
                                        SELECCIONAR VENDEDOR
                                        <b style="color:#d9534f;padding-left:100px">(COMPROBANTE:
                                            {{ campos . seriecomprobante }} - {{ campos . nro }})</b>
                                    </label>
                                    <select class="form-select" name="codempleado" v-model="campos.codempleado" required>
                                        <option value="0">SIN VENDEDOR</option>
                                        <?php
                                        foreach ($vendedores as $key => $value) { ?>
                                            <option value="<?php echo $value['codpersona']; ?>"> <?php echo $value['razonsocial']; ?> </option>
                                        <?php }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row form-group" v-if="campos.condicionpago==2">
                                <div class="col-md-5 col-xs-12">
                                    <label>NRO DIAS</label>
                                    <input class="form-control" name="nrodias" v-model="campos.nrodias"
                                        v-on:keyup="phuyu_cuotas()" required>
                                </div>
                                <div class="col-md-3 col-xs-12">
                                    <label>CUOTAS</label>
                                    <input class="form-control" name="nrocuotas" v-model="campos.nrocuotas"
                                        v-on:keyup="phuyu_cuotas()" required>
                                </div>
                                <div class="col-md-4 col-xs-12">
                                    <label>INTERES (%)</label>
                                    <input class="form-control" name="tasainteres" v-model="campos.tasainteres"
                                        v-on:keyup="phuyu_cuotas()" required>
                                </div>
                            </div>

                            <div v-if="campos.condicionpago==1">
                                <h5 align="center"> <b> <i class="fa fa-money"></i> REGISTRAR PAGO DE LA VENTA</b> </h5>
                                <div class="phuyu-linea"></div>
                                <div class="row form-group">
                                    <div class="col-md-4 col-xs-12" align="center">
                                        <label><i class="fa fa-money" style="font-size:35px;"></i> <br>PAGO CON
                                            EFECTIVO</label>
                                    </div>
                                    <div class="col-md-4 col-xs-12">
                                        <label>S/. MONTO RECIBIDO</label>
                                        <input type="number" step="0.01"
                                            class="form-control number phuyu-money-success" min="0" required
                                            v-model="pagos.monto_efectivo" placeholder="S/. 0.00"
                                            v-on:keyup="phuyu_vuelto()">
                                    </div>
                                    <div class="col-md-4 col-xs-12">
                                        <label>VUELTO</label>
                                        <input type="number" step="0.01" class="form-control phuyu-money-error"
                                            readonly v-model="pagos.vuelto_efectivo">
                                    </div>
                                </div>

                                <div class="phuyu-linea"></div>
                                <div class="row form-group">
                                    <div class="col-md-4 col-xs-12">
                                        <label> <i class="fa fa-money"></i> TARJETA O CHEQUE</label>
                                        <select class="form-select" v-model="pagos.codtipopago_tarjeta"
                                            v-on:change="phuyu_pagotarjeta()" required>
                                            <option value="0">SIN TARJETA</option>
                                            <?php
                                            foreach ($tipopagos as $key => $value) {
                                                if ($value["codtipopago"] != 1) { ?>
                                                    <option value="<?php echo $value['codtipopago']; ?>">
                                                        <?php echo $value['descripcion']; ?>
                                                    </option>
                                            <?php }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4 col-xs-12">
                                        <label>S/. MONTO</label>
                                        <input type="number" step="0.01"
                                            class="form-control number phuyu-money-success" min="0.01"
                                            id="monto_tarjeta" v-model="pagos.monto_tarjeta" placeholder="S/. 0.00"
                                            readonly>
                                    </div>
                                    <div class="col-md-4 col-xs-12">
                                        <label>NRO VOUCHER</label>
                                        <input type="text" class="form-control phuyu-money-default" id="nrovoucher"
                                            v-model.trim="pagos.nrovoucher" autocomplete="off" readonly>
                                    </div>
                                </div>
                            </div>

                            <div v-if="campos.condicionpago==2">
                                <div class="table-responsive" style="height:90px;">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>FECHA VENCE</th>
                                                <th>IMPORTE</th>
                                                <th>INTERES</th>
                                                <th>TOTAL</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="dato in cuotas">
                                                <td>{{ dato . fechavence }}</td>
                                                <td>{{ dato . importe }}</td>
                                                <td>{{ dato . interes }}</td>
                                                <td>{{ dato . total }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div style="border-bottom:2px solid #13a89e;padding-bottom:10px;" align="center">
                                    <button type="button" class="btn btn-warning btn-sm"> <b>INTERES: S/.
                                            {{ totales . interes }}</b></button>
                                    <button type="button" class="btn btn-danger btn-sm"> <b>TOTAL CREDITO: S/.
                                            {{ campos . totalcredito }}</b> </button>
                                </div>
                            </div>

                            <div class="row form-group" align="center"> <br>
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-success btn-lg"
                                        v-bind:disabled="estado==1">
                                        <b>GUARDAR VENTA</b>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-lg" data-dismiss="modal">
                                        <b>CANCELAR</b> </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>



    </div>
</div>

<div class="ticket" style="display:none">
    <div id="imprimir_pedido"> </div>
</div>


<script>
    // let ambientes = <?php echo json_encode($ambientes ?? []); ?>;
    let lineas = <?php echo json_encode($lineas ?? []); ?>;
    let comprobantes = <?php echo json_encode($comprobantes ?? []); ?>;
    let conceptos = <?php echo json_encode($conceptos ?? []); ?>;
    let tipopagos = <?php echo json_encode($tipopagos ?? []); ?>;
    let vendedores = <?php echo json_encode($vendedores ?? []); ?>;
    let sucursal = <?php echo json_encode($sucursal[0] ?? []); ?>;
</script>
<script>
    (function() {
        window.phuyu_operacion = new Vue({
            el: '#phuyu_operacion',
            data: {
                //||datos para sistema||
                estado: 0,
                stockalmacen: $("#stockalmacen").val(),
                igvsunat: $("#igvsunat").val(),
                icbpersunat: $("#icbpersunat").val(),
                rubro: 0,
                series: [],
                cuotas: [],
                mesas: [],
                detalle: [],
                atender: [],
                atendidos: [],
                campos: {
                    codambiente: $("#codambiente").val(),
                    conleyendaamazonia: 1,
                    creditoprogramado: 1,
                    codlote: 2,
                    codmesa: "0",
                    mesa: "...",
                    codpedido: 0,
                    pedidonuevo: 1,
                    tipopedido: 0,
                    codcomprobante: $("#comprobante").val(),
                    fechapedido: $("#fechapedido").val(),
                    codkardex: 0,
                    codpersona: 2,
                    codmovimientotipo: 20,
                    codcomprobantetipo: $("#comprobante").val(),
                    seriecomprobante: $("#serie").val(),
                    nro: "",
                    fechacomprobante: $("#fechapedido").val(),
                    fechakardex: $("#fechapedido").val(),
                    codconcepto: 13,
                    descripcion: "REGISTRO POR VENTA",
                    cliente: "CLIENTES VARIOS",
                    direccion: "-",
                    codempleado: 0,
                    codmoneda: 1,
                    tipocambio: 0.00,
                    codcentrocosto: 0,
                    nroplaca: "",
                    retirar: true,
                    afectacaja: true,
                    condicionpago: 1,
                    nrodias: 30,
                    nrocuotas: 1,
                    codcreditoconcepto: 3,
                    tasainteres: 0,
                    interes: 0,
                    totalcredito: 0,
                    porcdescuento: 0.00
                },
                pagos: {
                    codtipopago_efectivo: 1,
                    monto_efectivo: 0,
                    vuelto_efectivo: 0,
                    codtipopago_tarjeta: 0,
                    monto_tarjeta: 0,
                    nrovoucher: ""
                },
                operaciones: {
                    gravadas: 0.00,
                    exoneradas: 0.00,
                    inafectas: 0.00,
                    gratuitas: 0.00
                },
                item: {
                    producto: "",
                    unidad: "",
                    cantidad: 0,
                    preciobruto: 0,
                    descuento: 0,
                    porcdescuento: 0,
                    preciosinigv: 0,
                    precio: 0,
                    codafectacionigv: "",
                    igv: 0,
                    valorventa: 0,
                    conicbper: 0,
                    icbper: 0,
                    subtotal: 0,
                    descripcion: ""
                },
                totales: {
                    flete: 0.00,
                    gastos: 0.00,
                    bruto: 0.00,
                    descuentos: 0.00,
                    descglobal: 0.00,
                    valorventa: 0.00,
                    igv: 0.00,
                    isc: 0.00,
                    icbper: 0.00,
                    subtotal: 0.00,
                    importe: 0.00
                },
                buscando: "buscando_restobar",
                //||fin datos sistema||

                // UI
                isDark: false,
                q: '',
                filtro: 'all',
                orden: 'name',
                salon: 'Salón Principal',

                // Datos base
                // salones: ['Salón Principal', 'Terraza', 'Patio'],
                // mesas: Array.from({   length: 80   }).map((_, i) => ({  id: i + 1,  codigo: `M${String(i + 1).padStart(3, '0')}`,   
                //                 estado: Math.random() > 0.5 ? 'LIBRE' : 'OCUPADA',
                //                  salon: ['Salón Principal', 'Terraza', 'Patio'][i % 3]})),
                productos: [],

                // Pedido / carrito
                items: [],
                buscar: '',

                // Mesas y scroll
                PAGE_SIZE: 12,
                page: 1,
                mesaSeleccionada: null,
                observer: null,
                limite: 12,
                cargando: false,
                lineaActiva: 0,
                DatosMesaSelect: {},
                estado: 0,
            },

            computed: {

                productosVisibles() {
                    if (!this.productos || this.productos.length === 0) return [];

                    var list = this.productos.filter(p => p.descripcion && p.precio !== undefined)

                    if (this.orden === 'name')
                        list.sort((a, b) => a.descripcion.localeCompare(b.descripcion))

                    if (this.orden === 'price')
                        list.sort((a, b) => a.precio - b.precio)

                    return list.slice(0, this.limite); // ← IMPORTANTE
                },
                mesasFiltradas() {
                    return this.mesas.filter(m => m.salon === this.salon)
                },
                mesasPaginadas() {
                    return this.mesasFiltradas.slice(0, this.page * this.PAGE_SIZE)
                },
                hasMoreMesas() {
                    return this.mesasPaginadas.length < this.mesasFiltradas.length
                },
                total() {
                    return this.items.reduce((s, i) => s + i.precio * i.cantidad, 0)
                },
                totalcantidad() {
                    return this.items.reduce((s, i) => s + i.producto.cantidad, 0)
                }
            },

            methods: {
                phuyu_cobrar_pedido: function() {
                    if (this.campos.pedidonuevo == 1) {
                        phuyu_sistema.phuyu_noti("DEBE SELECCIONAR UNA MESA CON PEDIDO", "PARA COBRAR EL PEDIDO", "error");
                        return false;
                    }

                    if ($("#sessioncaja").val() == 0) {
                        phuyu_sistema.phuyu_noti("ESTIMADO USUARIO SU CAJA NO ESTA APERTURADA", "NO PUEDE COBRAR PEDIDO", "error");
                    } else {
                        this.campos.codcomprobantetipo = this.campos.codcomprobante;
                        this.phuyu_series();
                        this.pagos.monto_efectivo = this.totales.importe;
                        $("#modal_pago").modal('show');
                        this.inicializarSelect2Cliente();

                    }

                },
                // Función separada para inicializar Select2
                inicializarSelect2Cliente: function() {
                    setTimeout(() => {
                        var tipo = 1;
                        if ($('#codpersona').hasClass('select2-hidden-accessible')) {
                            $('#codpersona').select2('destroy');
                        }

                        $('#codpersona').select2({
                            ajax: {
                                url: url + 'ventas/clientes/buscar',
                                dataType: 'json',
                                delay: 250,
                                data: function(params) {
                                    return {
                                        search: {
                                            value: params.term,
                                            tipo: tipo
                                        },
                                        page: params.page
                                    };
                                },
                                processResults: function(data, page) {
                                    // Transformar los datos al formato que Select2 espera
                                    const resultados = data.data.map(cliente => ({
                                        id: cliente.codpersona,
                                        text: `${cliente.razonsocial} - ${cliente.documento}`
                                    }));

                                    return {
                                        results: resultados
                                    };
                                },
                            },
                            placeholder: 'Buscar cliente...',
                            minimumInputLength: 2,
                            width: '100%',
                            dropdownParent: $('#modal_pago')
                        });

                        // Sincronizar con v-model
                        $('#codpersona').on('select2:select', (e) => {
                            this.campos.codpersona = e.params.data.id;
                        });
                    }, 300);
                },
                phuyu_series: function() {
                    if (this.campos.codcomprobantetipo != undefined) {
                        this.estado = 1;
                        this.$http.get(url + "caja/controlcajas/phuyu_seriescaja/" + this.campos.codcomprobantetipo).then(function(data) {
                            this.series = data.body.series;
                            this.estado = 0;
                            // this.campos.seriecomprobante = $("#serie").val(); this.phuyu_correlativo();
                            this.campos.seriecomprobante = data.body.serie;
                            this.phuyu_correlativo();
                        });

                        if (this.campos.codcomprobantetipo == 10) {
                            this.$http.get(url + "ventas/clientes/infocliente/" + this.campos.codpersona).then(function(data) {
                                this.codtipodocumento = data.body[0].coddocumentotipo;
                            });
                        }
                    }
                },
                phuyu_correlativo: function() {
                    if (this.campos.codcomprobantetipo != undefined) {
                        if (this.campos.seriecomprobante != "") {
                            this.$http.get(url + "caja/controlcajas/phuyu_correlativo/" + this.campos.codcomprobantetipo + "/" + this.campos.seriecomprobante).then(function(data) {
                                this.campos.nro = data.body;
                            });
                        }
                    }
                },

                phuyu_pagotarjeta: function() {
                    if (this.pagos.codtipopago_tarjeta == 0) {
                        this.pagos.monto_tarjeta = 0;
                        this.pagos.nrovoucher = "";
                        $("#monto_tarjeta").attr("readonly", "true");
                        $("#monto_tarjeta").removeAttr("required");
                        $("#nrovoucher").attr("readonly", "true");
                        $("#nrovoucher").removeAttr("required");
                    } else {
                        $("#monto_tarjeta").removeAttr("readonly");
                        $("#monto_tarjeta").attr("required", "true");
                        $("#nrovoucher").removeAttr("readonly");
                        $("#nrovoucher").attr("required", "true");
                    }
                },
                phuyu_vuelto: function() {
                    this.pagos.vuelto_efectivo = Number((
                        this.pagos.monto_efectivo - this.totales.importe).toFixed(2));
                    if (this.pagos.vuelto_efectivo <= 0) {
                        this.pagos.vuelto_efectivo = 0;
                    }
                },
                phuyu_atender_pedido() {
                    if (this.campos.pedidonuevo == 1) {
                        phuyu_sistema.phuyu_noti("DEBE SELECCIONAR UNA MESA CON PEDIDO", "PARA ATENDER UN PEDIDO", "error");
                        return false;
                    } else {

                        this.$http.post(url + "ventas/pedidos/phuyu_atenciones", {
                            "codpedido": this.campos.codpedido
                        }).then(function(data) {
                            this.atender = data.body.detalle;
                            this.atendidos = data.body.atendidos;
                            this.totales = data.body.totales;
                            $("#modal_atender").modal("show");

                            console.log(this.atender);
                        }, function() {
                            phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED", "error");
                        });
                    }
                },
                phuyu_atender: function() {
                    var atender = 0;
                    for (var i = 0; i < this.atender.length; i++) {
                        if (this.atender[i]["atender"] != "") {
                            atender = atender + parseFloat(this.atender[i]["atender"]);
                        }
                    }
                    if (atender == 0) {
                        phuyu_sistema.phuyu_noti("NO HAY PEDIDOS PARA ATENDER", "MINIMO DEBE HABER UNA CANTIDAD ATENDIDA", "error");
                    } else {
                        this.estado = 1;
                        $("#modal_atender").modal("hide");
                        phuyu_sistema.phuyu_inicio_guardar("GUARDANDO ATENCION . . .");
                        this.$http.post(url + "ventas/pedidos/guardar_atencion", {
                            "atender": this.atender
                        }).then(function(data) {
                            if (data.body == 1) {
                                phuyu_sistema.phuyu_noti("ATENCION REGISTRADA CORRECTAMENTE", "PEDIDO 000" + this.campos.codpedido + " ATENDIDO", "success");
                            } else {
                                phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR ATENCION", "ERROR DE RED", "error");
                            }
                            phuyu_sistema.phuyu_fin();
                            phuyu_sistema.phuyu_modulo();
                        }, function() {
                            phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR ATENCION", "ERROR DE RED", "error");
                            phuyu_sistema.phuyu_fin();
                            phuyu_sistema.phuyu_modulo();
                        });
                    }
                },
                phuyu_mas_menos: function(pedido, tipo) {
                    if (tipo == 1) {
                        if (pedido.falta != pedido.atender) {
                            pedido.atender = pedido.atender + 1;
                        }
                    } else {
                        if (pedido.atender > 0) {
                            pedido.atender = pedido.atender - 1;
                        }
                    }
                },

                phuyu_comanda: function() {
                    if (this.DatosMesaSelect.pedidonuevo == 1) {
                        phuyu_sistema.phuyu_noti("DEBE SELECCIONAR UNA MESA CON PEDIDO", "PARA IMPRIMIR LA COMANDA", "error");
                        return false;
                    } else {
                        this.$http.get(url + "restaurante/caja/comanda/" + this.campos.codpedido).then(function(data) {
                            $("#imprimir_pedido").empty().html(data.body);
                            var id = "imprimir_pedido";
                            var data = document.getElementById(id).innerHTML;
                            var myWindow = window.open('', 'IMPRIMIENDO', 'height=500,width=1000');
                            myWindow.document.write('<html><head><title>TICKET</title>');
                            // myWindow.document.write('<link rel="stylesheet" href="main.css" type="text/css" />');
                            myWindow.document.write('</head><body >');
                            myWindow.document.write(data);
                            myWindow.document.write('</body></html>');
                            myWindow.document.close();

                            myWindow.onload = function() {
                                myWindow.focus();
                                myWindow.print();
                                myWindow.close();
                            };
                        });
                        // $("#phuyu_pdf").attr("src",url+"restaurante/caja/avance_pedido/"+this.campos.codpedido);
                    }
                },
                phuyu_avance_pedido: function() {
                    if (this.DatosMesaSelect.pedidonuevo == 1) {
                        phuyu_sistema.phuyu_noti("DEBE SELECCIONAR UNA MESA CON PEDIDO", "PARA IMPRIMIR EL AVANCE DE CUENTE", "error");
                        return false;
                    } else {
                        this.$http.get(url + "restaurante/caja/avance_pedido/" + this.campos.codpedido).then(function(data) {
                            $("#imprimir_pedido").empty().html(data.body);
                            var id = "imprimir_pedido";
                            var data = document.getElementById(id).innerHTML;
                            var myWindow = window.open('', 'IMPRIMIENDO', 'height=400,width=800');
                            myWindow.document.write('<html><head><title>TICKET</title>');
                            // myWindow.document.write('<link rel="stylesheet" href="main.css" type="text/css" />');
                            myWindow.document.write('</head><body >');
                            myWindow.document.write(data);
                            myWindow.document.write('</body></html>');
                            myWindow.document.close();

                            myWindow.onload = function() {
                                myWindow.focus();
                                myWindow.print();
                                myWindow.close();
                            };
                        });
                        // $("#phuyu_pdf").attr("src",url+"restaurante/caja/avance_pedido/"+this.campos.codpedido);
                    }
                },
                phuyu_anular_pedido: function() {

                    console.log(this.campos);
                    if (this.campos.pedidonuevo == 1) {
                        phuyu_sistema.phuyu_noti("DEBE SELECCIONAR UNA MESA CON PEDIDO", "PARA ANULAR EL PEDIDO", "error");
                        return false;
                    }

                    swal({
                        title: "SEGURO ANULAR PEDIDO ?",
                        text: "USTED ESTA POR ANULAR EL PEDIDO 00" + this.campos.codpedido,
                        icon: "warning",
                        dangerMode: true,
                        buttons: ["CANCELAR", "SI, ANULAR"],
                    }).then((willDelete) => {
                        if (willDelete) {
                            this.$http.post(url + "ventas/pedidos/anular_pedido", {
                                "codregistro": this.campos.codpedido,
                                "codmesa": this.DatosMesaSelect.codmesa
                            }).then(function(data) {
                                if (data.body == 1) {
                                    phuyu_sistema.phuyu_alerta("ANULADO CORRECTAMENTE", "UN PEDIDO ANULADO EN EL SISTEMA", "success");
                                } else {
                                    phuyu_sistema.phuyu_alerta("OCURRIO UN ERROR !!!", "SE PERDIÓ LA CONEXION !!! LO SENTIMOS", "error");
                                }
                                phuyu_sistema.phuyu_modulo();
                            }, function() {
                                phuyu_sistema.phuyu_alerta("ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS", "ERROR DE RED", "error");
                            });
                        }
                    });
                },
                verPedido() {

                    if (!this.mesaSeleccionada) {
                        alert('Por favor, selecciona una mesa primero');
                        return;
                    }

                    var myOffcanvas = new bootstrap.Offcanvas(document.getElementById('pedidoCanvas'));
                    myOffcanvas.show()
                    // data-bs-target="#pedidoCanvas"
                },
                validarCantidad(producto, index) {
                    let nuevaCantidad = parseInt(producto.cantidad) || 1;

                    if (producto.controlstock == 1 && nuevaCantidad > producto.stockdisponible) {
                        nuevaCantidad = producto.stockdisponible;
                        alert(`Stock máximo disponible: ${producto.stockdisponible}`);
                    }

                    if (nuevaCantidad < 1) {
                        nuevaCantidad = 1;
                    }

                    producto.cantidad = nuevaCantidad;

                    this.actualizarStockVisual(producto);
                },


                configurarScroll() {
                    // Esperar a que Vue renderice el DOM
                    this.$nextTick(() => {
                        const contenedor = this.$el.querySelector('.contenedor-scroll');
                        if (contenedor) {
                            contenedor.addEventListener('scroll', this.manejarScroll);
                        }
                    });
                },

                manejarScroll(event) {
                    const elemento = event.target;
                    const scrollTop = elemento.scrollTop;
                    const scrollHeight = elemento.scrollHeight;
                    const clientHeight = elemento.clientHeight;

                    // Si está a 100px del final y no está cargando y hay más productos
                    if (scrollTop + clientHeight >= scrollHeight - 100 &&
                        !this.cargando &&
                        this.limite < this.productos.length) {

                        this.cargarMas();
                    }
                },

                cargarMas() {
                    this.cargando = true;

                    // Simular carga de 1 segundo
                    setTimeout(() => {
                        this.limite += 12;
                        this.cargando = false;
                    }, 1000);
                },
                async phuyu_producto(codlinea) {

                    if (!codlinea) codlinea = 0; // si no se envía o viene null/undefined, usar 0
                    this.lineaActiva = codlinea;
                    //this.cargando = true;
                    let getProd = await this.$http.post(url + "almacen/productos/" + this.buscando, {
                        "buscar": this.buscar,
                        "codlinea": codlinea
                    });
                    // console.log(getProd.data);
                    this.productos = getProd.data;
                    //this.cargando = false;
                },


                async phuyu_mesas() {
                    try {
                        // Vue Resource no devuelve una Promise nativa, por eso no funciona con await
                        let response = await this.$http.post(url + "restaurante/mesas/mesas_ambiente/" +
                            this.campos.codambiente);
                        //  console.log(response.body);
                        this.mesas = response.body;
                        return false;

                    } catch (error) {
                        console.error('Error:', error);
                    }
                },
                // Scroll infinito
                initInfiniteScroll() {
                    var sentinel = this.$refs && this.$refs.sentinel
                    var container = this.$refs && this.$refs.mesasScroll
                    if (!sentinel || !container) return

                    var self = this
                    this.observer = new IntersectionObserver(function(entries) {
                        var entry = entries[0]
                        if (entry.isIntersecting && self.hasMoreMesas) self.page++
                    }, {
                        root: container,
                        threshold: 1
                    })

                    this.observer.observe(sentinel)
                },

                // Seleccionar mesa
                async selectMesa(mesa) {
                    this.mesaSeleccionada = mesa.codmesa;
                    this.campos.codmesa = mesa.codmesa;
                    this.campos.mesa = mesa.nromesa;
                    this.DatosMesaSelect = mesa;
                    console.log(mesa);

                    if (mesa.texto == "LIBRE") {
                        this.DetalleMesa = [];
                        this.items = [];
                    }

                    let getPedido = await this.$http.post(url + "ventas/pedidos/phuyu_pedido", {
                        "codmesa": this.mesaSeleccionada
                    });
                    // data.body.pedidonuevo=1 		SIN DETALLE - PEDIDO NUEVO //
                    this.campos.codpedido = getPedido.body.codpedido;
                    this.campos.pedidonuevo = getPedido.body.pedidonuevo;
                    this.DatosMesaSelect.pedidonuevo = getPedido.body.pedidonuevo;

                    if (getPedido.body.pedidonuevo == 0) {
                        let data = getPedido;
                        this.totales.valorventa = Number((parseFloat(data.body.pedido[0].valorventa)).toFixed(2));
                        this.totales.descglobal = Number((parseFloat(data.body.pedido[0].descglobal)).toFixed(2));
                        this.totales.igv = Number((parseFloat(data.body.pedido[0].igv)).toFixed(2));
                        this.totales.importe = Number((parseFloat(data.body.pedido[0].importe)).toFixed(2));
                        this.campos.codcomprobante = parseInt(data.body.pedido[0].codcomprobantetipo);
                        this.campos.codempleado = parseInt(data.body.pedido[0].codempleado);
                    }
                    this.DetalleMesa = getPedido.body.detalle;
                    this.items = getPedido.body.detalle;

                },
                phuyu_pedido: function(mesa) {
                    //  $("#"+this.campos.codmesa).removeClass("mesa-activa");
                    this.campos.codmesa = mesa.codmesa;
                    this.campos.mesa = mesa.nromesa;
                    //  $("#" + this.campos.codmesa).addClass("mesa-activa");

                    this.$http.post(url + "ventas/pedidos/phuyu_pedido", {
                        "codmesa": this.campos.codmesa
                    }).then(function(data) {

                        this.campos.codpedido = data.body.codpedido;
                        this.campos.pedidonuevo = data.body.pedidonuevo;
                        // data.body.pedidonuevo=1 		SIN DETALLE - PEDIDO NUEVO //
                        if (data.body.pedidonuevo == 0) {
                            this.totales.valorventa = Number((parseFloat(data.body.pedido[0]
                                .valorventa)).toFixed(2));
                            this.totales.descglobal = Number((parseFloat(data.body.pedido[0]
                                .descglobal)).toFixed(2));
                            this.totales.igv = Number((parseFloat(data.body.pedido[0].igv))
                                .toFixed(2));
                            this.totales.importe = Number((parseFloat(data.body.pedido[0]
                                .importe)).toFixed(2));

                            this.campos.codcomprobante = parseInt(data.body.pedido[0]
                                .codcomprobantetipo);
                            this.campos.codempleado = parseInt(data.body.pedido[0].codempleado);
                        }
                        this.detalle = data.body.detalle;
                        this.items = this.detalle;
                    }, function() {
                        phuyu_sistema.phuyu_alerta("SIN CONEXION DE INTERNET", "ERROR DE RED",
                            "error");
                    });
                },

                // Carrito / pedido
                agregar(producto, precio) {
                    //let p = producto;

                    if (!this.mesaSeleccionada) {
                        //alert('Por favor, selecciona una mesa primero');
                        phuyu_sistema.phuyu_alerta("Debe selecionar una mesa", "", "error");
                        return;
                    }

                    // Validar stock si controla stock
                    if (producto.controlstock == 1 && (!producto.stockdisponible || producto
                            .stockdisponible <= 0)) {
                        //  alert('No hay stock disponible');
                        phuyu_sistema.phuyu_alerta("No hay stock disponibl", "", "error");
                        return;
                    }
                    var existe_item = [];
                    if ($("#itemrepetir").val() == 0) {
                        var existe_item = this.items.filter(function(p) {
                            if (p.codproducto == producto.codproducto && p.codunidad == producto
                                .codunidad) {
                                p.cantidad = parseFloat(p.cantidad) + 1;
                                return p;
                            };
                        });
                        // Actualizar stock localmente si controla stock
                        if (producto.controlstock == 1 && producto.stockdisponible > 0) {
                            producto.stockdisponible--;
                            producto.mostrarstock = "STOCK: " + producto.stockdisponible;
                        }

                    }
                    if (existe_item.length == 0 || $("#itemrepetir").val() == 1) {
                        producto.preciosinigv = producto.precio;
                        producto.precio = precio;
                        producto.valorventa = Number(producto.precio);
                        producto.subtotal = producto.precio;

                        producto.afectacionigv = 20;
                        producto.igv = 0;
                        var porcentaje = 1;
                        if (producto.afectoigvventa == 1) {
                            var porcentaje = (1 + this.igvsunat) / 100;

                            producto.afectacionigv = 10;
                            producto.preciosinigv = Number((producto.precio / porcentaje).toFixed(4));
                            producto.valorventa = Number((producto.precio / porcentaje).toFixed(2));
                            producto.igv = Number((producto.subtotal - producto.valorventa).toFixed(2));
                        }

                        producto.icbper = 0;
                        producto.isc = 0;
                        if (producto.afectoicbper == 1) {
                            producto.icbper = Number((1 * this.icbpersunat).toFixed(2));;
                        }

                        producto.control = 0;
                        if (this.stockalmacen == 1) {
                            if (producto.controlstock == 1) {
                                producto.control = 1;
                            }
                        }

                        this.items.push({

                            codproducto: producto.codproducto,
                            producto: producto.descripcion,
                            codunidad: producto.codunidad,
                            unidad: producto.unidad,
                            cantidad: 1,
                            stock: producto.stock,
                            control: producto.control,
                            preciobruto: producto.preciosinigv,
                            preciosinigv: producto.preciosinigv,
                            precio: producto.precio,
                            preciorefunitario: producto.precio,
                            porcdescuento: 0,
                            descuento: 0,
                            codafectacionigv: producto.afectacionigv,
                            igv: producto.igv,
                            conicbper: producto.afectoicbper,
                            icbper: producto.icbper,
                            valorventa: producto.valorventa,
                            subtotal: producto.subtotal,
                            subtotal_tem: producto.subtotal,
                            descripcion: "",
                            calcular: producto.calcular,
                            atendido: 0,
                            item: 0,

                        });
                        // Actualizar stock localmente si controla stock
                        if (producto.controlstock == 1 && producto.stockdisponible > 0) {
                            producto.stockdisponible--;
                            producto.mostrarstock = "STOCK: " + producto.stockdisponible;
                        }
                        //this.phuyu_calcular(producto,1);
                    } else {
                        //this.phuyu_calcular(existe_item[0],3);
                    }
                },
                phuyu_calcular: function(producto, tipo) {
                    if (tipo == 1) {
                        this.totales.valorventa = Number((this.totales.valorventa + parseFloat(producto
                            .precio)).toFixed(2));
                    } else {
                        if (tipo == 2) {
                            this.totales.valorventa = Number((this.totales.valorventa - producto
                                .subtotal).toFixed(2));
                        } else {
                            this.totales.valorventa = Number((this.totales.valorventa - producto
                                .subtotal).toFixed(2));

                            if (producto.cantidad == "") {
                                producto.subtotal = 0;
                            } else {
                                producto.subtotal = Number((producto.cantidad * producto.precio)
                                    .toFixed(2));
                            }
                            this.totales.valorventa = Number((this.totales.valorventa + producto
                                .subtotal).toFixed(2));
                        }
                    }
                    this.totales.importe = Number((this.totales.valorventa + this.totales.igv).toFixed(
                        2));
                },
                agregar_original(p) {
                    // Validar que haya una mesa seleccionada
                    if (!this.mesaSeleccionada) {
                        alert('Por favor, selecciona una mesa primero');
                        return;
                    }

                    // Validar stock si controla stock
                    if (p.controlstock == 1 && (!p.stockdisponible || p.stockdisponible <= 0)) {
                        alert('No hay stock disponible');
                        return;
                    }

                    // Buscar si ya existe en el carrito para esta mesa
                    // Asumiendo que this.items es un array de productos para la mesa actual
                    var found = this.items.find(i => i.codproducto === p.codproducto)

                    if (found) {
                        found.cantidad++;
                    } else {
                        this.items.push({
                            ...p, // Todos los campos del producto
                            cantidad: 1, // Cantidad inicial
                            atendido: 0,
                        });
                    }

                    // Actualizar stock localmente si controla stock
                    if (p.controlstock == 1 && p.stockdisponible > 0) {
                        p.stockdisponible--;
                        p.mostrarstock = "STOCK: " + p.stockdisponible;
                    }
                },
                phuyu_guardar_pedido: function() {
                    if ($("#sessioncaja").val() == 0) {
                        phuyu_sistema.phuyu_noti("ESTIMADO USUARIO SU CAJA NO ESTA APERTURADA", "NO PUEDE REALIZAR VENTAS", "error");
                        return false;
                    }

                    if (this.campos.codmesa == "") {
                        phuyu_sistema.phuyu_noti("DEBE SELECCIONAR LA MESA DEL PEDIDO PARA PODER REGISTRAR", "", "error");
                        return false;
                    }

                    // if (this.detalle.length == 0) {
                    //     phuyu_sistema.phuyu_noti("REGISTRAR UN PRODUCTO EN EL DETALLE", "REGISTRAR ITEM PARA EL PEDIDO", "error");
                    //     return false;
                    // }
                    if (this.items.length == 0) {
                        phuyu_sistema.phuyu_noti("REGISTRAR UN PRODUCTO EN EL DETALLE", "REGISTRAR ITEM PARA EL PEDIDO", "error");
                        return false;
                    }

                    this.estado = 1;

                    phuyu_sistema.phuyu_inicio_guardar("GUARDANDO PEDIDO . . .");
                    this.$http.post(url + "ventas/pedidos/guardar_pedido", {
                        "campos": this.campos,
                        "detalle": this.items,
                        "totales": this.totales
                    }).then(function(data) {
                        if (data.body == "e") {
                            phuyu_sistema.phuyu_alerta("SESION DEL USUARIO TERMINADA", "DEBE INICIAR SESION NUEVAMENTE", "error");
                        } else {
                            if (data.body.estado == 1) {
                                phuyu_sistema.phuyu_noti("PEDIDO REGISTRADO CORRECTAMENTE", "PEDIDO REGISTRADO EN EL SISTEMA", "success");
                                // this.$http.get(url + "restaurante/caja/comanda/" + data.body.codpedido).then(function(data) {
                                //     $("#imprimir_pedido").empty().html(data.body);
                                //     var id = "imprimir_pedido";
                                //     var data = document.getElementById(id).innerHTML;
                                //     var myWindow = window.open('', 'IMPRIMIENDO', 'height=500,width=1000');
                                //     myWindow.document.write('<html><head><title>TICKET</title>');
                                //     // myWindow.document.write('<link rel="stylesheet" href="main.css" type="text/css" />');
                                //     myWindow.document.write('</head><body >');
                                //     myWindow.document.write(data);
                                //     myWindow.document.write('</body></html>');
                                //     myWindow.document.close();

                                //     myWindow.onload = function() {
                                //         myWindow.focus();
                                //         myWindow.print();
                                //         myWindow.close();
                                //     };
                                // });
                            } else {
                                phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR PEDIDO", "ERROR DE RED", "error");
                            }
                        }
                        phuyu_sistema.phuyu_fin();
                        phuyu_sistema.phuyu_modulo();
                    }, function() {
                        phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR PEDIDO", "ERROR DE RED", "error");
                        phuyu_sistema.phuyu_fin();
                        phuyu_sistema.phuyu_modulo();
                    });
                },


                phuyu_pagar: function() {
                    if ((this.campos.codcomprobantetipo == 10 || this.campos.codcomprobantetipo == 25) && this.codtipodocumento != 4) {
                        phuyu_sistema.phuyu_noti("PARA EMITIR UNA FACTURA", "DEBE SELECCIONAR UN CLIENTE CON RUC", "error");
                        return false;
                    }

                    if (parseFloat(this.totales.importe) >= 700) {
                        if ((this.campos.codcomprobantetipo == 12 || this.campos.codcomprobantetipo == 26) && this.codtipodocumento == 0) {
                            phuyu_sistema.phuyu_noti("PARA EMITIR UNA BOLETA CON MONTO MAYOR A 700.00 SOLES", "DEBE SELECCIONAR UN CLIENTE CON DNI o RUC", "error");
                            return false;
                        }
                    }

                    if (this.campos.condicionpago == 1) {
                        if (this.pagos.codtipopago_tarjeta == 0) {
                            if (parseFloat(this.pagos.monto_efectivo) < parseFloat(this.totales.importe)) {
                                phuyu_sistema.phuyu_noti("EL IMPORTE DEBE SER MAYOR O IGUAL AL TOTAL DE LA VENTA", "FALTAN S/. " +
                                    Number((parseFloat(this.totales.importe - this.pagos.monto_efectivo)).toFixed(2)), "error");
                                return false;
                            }
                        } else {
                            var suma_importe = parseFloat(this.pagos.monto_efectivo) + parseFloat(this.pagos.monto_tarjeta);
                            if (parseFloat(suma_importe) != parseFloat(this.totales.importe)) {
                                phuyu_sistema.phuyu_noti("LA SUMA DE LOS IMPORTES DEBE SER IGUAL AL TOTAL DE LA VENTA", "DIFERENCIA S/. " +
                                    Number((parseFloat(this.totales.importe - suma_importe)).toFixed(2)), "error");
                                return false;
                            }
                        }
                    } else {
                        if (this.campos.codpersona == 2) {
                            phuyu_sistema.phuyu_noti("ATENCION USUARIO: EL SISTEMA NO PERMITE REGISTRAR UN CREDITO A CLIENTES VARIOS", "", "error");
                            return false;
                        }
                    }

                    this.estado = 1;
                    $("#modal_pago").modal("hide");
                    phuyu_sistema.phuyu_inicio_guardar("GUARDANDO VENTA . . .");

                    this.$http.post(url + "ventas/pedidos/cobrar_pedido", {
                        "campos": this.campos,
                       // "detalle": this.detalle,
                         "detalle":  this.items,
                        "cuotas": this.cuotas,
                        "pagos": this.pagos,
                        "totales": this.totales
                    }).then(function(data) {
                        if (data.body == "e") {
                            phuyu_sistema.phuyu_alerta("SESION DEL USUARIO TERMINADA", "DEBE INICIAR SESION NUEVAMENTE", "error");
                        } else {
                            if (data.body.estado == 1) {
                                swal({
                                    title: "DESEA IMPRIMIR LA VENTA ?",
                                    text: "DESEA IMPRIMIR EL COMPROBANTE REGISTRADO",
                                    icon: "warning",
                                    dangerMode: true,
                                    buttons: ["CANCELAR", "SI, IMPRIMIR"],
                                }).then((willDelete) => {
                                    if (willDelete) {
                                        window.open(url + "facturacion/formato/ticket/" + data.body.codkardex, "_blank");

                                        // $("#phuyu_pdf").attr("src",url+"restaurante/caja/cobrar_pedido/"+data.body.codkardex);
                                    }
                                });
                                phuyu_sistema.phuyu_noti("VENTA REGISTRADA CORRECTAMENTE", "VENTA REGISTRADA EN EL SISTEMA", "success");
                            } else {
                                phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR VENTA", "ERROR DE RED", "error");
                            }
                        }
                        phuyu_sistema.phuyu_fin();
                        phuyu_sistema.phuyu_modulo();
                    }, function() {
                        phuyu_sistema.phuyu_alerta("ERROR AL REGISTRAR VENTA", "ERROR DE RED", "error");
                        phuyu_sistema.phuyu_fin();
                        phuyu_sistema.phuyu_modulo();
                    });
                },
                inc(i) {
                    this.items[i].cantidad++
                },
                dec(i) {
                    this.items[i].cantidad = Math.max(1, this.items[i].cantidad - 1)
                },
                delItem(i) {
                    this.items.splice(i, 1)
                },

                // UI
                toggleDark() {
                    this.isDark = !this.isDark
                    document.body.classList.toggle('bg-dark', this.isDark)
                    document.body.classList.toggle('text-white', this.isDark)
                },
                openMenu() {
                    /* placeholder opcional */
                },
                cobrar() {
                    alert('Cobrar pedido (demo)')
                },
                precuenta() {
                    alert('Imprimir pre-cuenta (demo)')
                },

                // Acciones de configuración
                anfitrion() {
                    alert('Abrir módulo Anfitrión (demo)')
                },
                ingresoCaja() {
                    alert('Registrar ingreso de caja (demo)')
                },
                egresoCaja() {
                    alert('Registrar egreso de caja (demo)')
                },
                ventaDiaria() {
                    alert('Ver venta diaria (demo)')
                },
                guardar() {
                    this.phuyu_guardar_pedido();
                    //    // alert('Guardar pedido (demo)')

                    //     let temp = $("#sessioncaja").val();

                    //     console.log(temp);
                },
                atender() {
                    alert('Enviar a cocina / Atender (demo)')
                },
                imprimirPrecuenta() {
                    alert('Imprimir pre-cuenta (demo)')
                },
                imprimirComanda() {
                    alert('Imprimir comanda (demo)')
                },
                anular() {
                    if (confirm('¿Seguro de anular el pedido?')) alert('Pedido anulado (demo)')
                },
                balanceCaja() {
                    alert('Balance de caja (demo)')
                }
            },
            created() {

            },


            mounted() {
                // Espera al DOM virtual de Vue
                this.$nextTick(this.initInfiniteScroll);
                // (Opcional) atajo de teclado si lo usabas:
                var self = this
                document.addEventListener('keyup', function(e) {
                    if (e.keyCode === 122) { // F11
                        if (typeof self.phuyu_producto === 'function') self.phuyu_producto()
                    }
                }, false)

                // Stub opcional para evitar errores si alguien llama a phuyu_producto()
                if (typeof this.phuyu_producto !== 'function') {
                    this.phuyu_producto = function() {
                        /* noop */
                    }
                }
                this.phuyu_producto();
                this.phuyu_mesas();

                // Agregar el event listener al contenedor
                this.$nextTick(() => {
                    const contenedor = this.$el.querySelector('.contenedor-productos');
                    if (contenedor) {
                        contenedor.addEventListener('scroll', this.manejarScroll);
                    }
                });

                this.configurarScroll();



            },

            // Vue 2
            beforeDestroy() {
                if (this.observer) this.observer.disconnect()

                // Remover el event listener
                const contenedor = this.$el.querySelector('.contenedor-productos');
                if (contenedor) {
                    contenedor.removeEventListener('scroll', this.manejarScroll);
                }
            }
        })
    })();
</script>
<script>
    phuyu_sistema.phuyu_fin();
</script>