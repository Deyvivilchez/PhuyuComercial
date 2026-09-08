<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_pedidos" class="phuyu-velzon-list phuyu-ventas-velzon">

    <div class="row g-3 align-items-start mb-3">
        <div class="col-12 col-md-6">
            <input type="hidden" id="almacen" value="<?php echo $almacen; ?>">
            <input type="hidden" id="formato" value="<?php echo $_SESSION['phuyu_formatopedido']; ?>">
            <input type="hidden" id="perfil" value="<?php echo $_SESSION["phuyu_codperfil"]; ?>">

            <div class="phuyu-page-title mb-0">
                <span class="phuyu-page-icon"><i class="bi bi-bag-check"></i></span>
                <div>
                    <div class="text-muted small text-uppercase fw-semibold">Ventas</div>
                    <h4 class="mb-1" id="title">Pedidos</h4>
                    <p class="text-muted mb-0">Registro, atención e impresión de pedidos comerciales.</p>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="row g-2 justify-content-md-end">

                <div class="col-12 col-sm-4">
                    <label class="form-label small fw-semibold mb-1">
                        <i class="bi bi-calendar-date me-1"></i> Desde
                    </label>
                    <input type="date" class="form-control form-control-sm" id="fecha_desde" value="" v-on:change="phuyu_buscar()" autocomplete="off">
                </div>

                <div class="col-12 col-sm-4">
                    <label class="form-label small fw-semibold mb-1">
                        <i class="bi bi-calendar-check me-1"></i> Hasta
                    </label>
                    <input type="date" class="form-control form-control-sm" id="fecha_hasta" value="<?php echo date('Y-m-d'); ?>" v-on:change="phuyu_buscar()" autocomplete="off">
                </div>

                <div class="col-12 col-sm-4">
                    <label class="form-label small fw-semibold mb-1">
                        <i class="bi bi-printer me-1"></i> Formato impresión
                    </label>
                    <select class="form-select form-select-sm" v-model="formato_impresion" v-on:change="phuyu_formato()">
                        <option value="a4">A4 Impresión</option>
                        <option value="a5">A5 Impresión</option>
                        <option value="ticket">Ticket Impresión</option>
                    </select>
                </div>

            </div>
        </div>
    </div>

    <div class="phuyu_body">
        <div class="card border-0 shadow-sm phuyu-card-pedidos">
            <div class="card-body">

                <div class="row g-3 align-items-center mb-3">
                    <div class="col-12 col-md-5 col-lg-4 col-xl-3">
                        <div class="position-relative phuyu-search-box">
                            <input
                                class="form-control ps-5"
                                v-model="buscar"
                                v-on:keyup="phuyu_buscar()"
                                placeholder="Buscar registro..."
                                autocomplete="off"
                            />
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        </div>
                    </div>

                    <div class="col-12 col-md-7 col-lg-8 col-xl-9">
                        <div class="d-flex flex-wrap gap-2 justify-content-md-end phuyu-btn-group">

                            <button type="button" class="btn btn-success btn-sm phuyu-btn" title="Nuevo pedido" data-bs-toggle="tooltip" v-on:click="phuyu_nuevo()">
                                <i class="bi bi-plus-circle"></i>
                                <span>Nuevo</span>
                            </button>

                            <button type="button" class="btn btn-warning btn-sm phuyu-btn editar" title="Editar registro" data-bs-toggle="tooltip" v-on:click="phuyu_editar()">
                                <i class="bi bi-pencil-square"></i>
                                <span>Editar</span>
                            </button>

                            <button type="button" class="btn btn-danger btn-sm phuyu-btn eliminar" title="Anular pedido" data-bs-toggle="tooltip" v-on:click="phuyu_eliminar()">
                                <i class="bi bi-trash"></i>
                                <span>Eliminar</span>
                            </button>

                            <button type="button" class="btn btn-info btn-sm text-white phuyu-btn" title="Ver registro" data-bs-toggle="tooltip" v-on:click="phuyu_ver()">
                                <i class="bi bi-eye"></i>
                                <span>Ver</span>
                            </button>

                            <button type="button" class="btn btn-primary btn-sm phuyu-btn" title="Imprimir pedido" data-bs-toggle="tooltip" v-on:click="phuyu_imprimir()">
                                <i class="bi bi-printer"></i>
                                <span>Imprimir</span>
                            </button>

                            <button type="button" class="btn btn-secondary btn-sm phuyu-btn" title="Clonar pedido" data-bs-toggle="tooltip" v-on:click="phuyu_clonar()">
                                <i class="bi bi-copy"></i>
                                <span>Clonar</span>
                            </button>

                        </div>
                    </div>
                </div>

                <div class="phuyu_cargando" v-if="cargando">
                    <div class="overlay-spinner"></div>
                </div>

                <div class="table-responsive phuyu-tabla-wrapper">
                    <table class="table table-hover align-middle mb-0 phuyu-tabla-pedidos">
                        <thead>
                            <tr>
                                <th style="width: 70px;">ID</th>
                                <th>Documento</th>
                                <th>Razón social</th>
                                <th style="width: 120px;">Fecha</th>
                                <th style="width: 100px;">Tipo</th>
                                <th>Comprobante</th>
                                <th style="width: 130px;" class="text-end">Importe</th>
                                <th style="width: 130px;">Pago</th>
                                <th style="width: 120px;">Estado</th>
                                <th style="width: 60px;" class="text-center">
                                    <i class="bi bi-check2-circle"></i>
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="dato in datos" v-bind:class="[dato.estado == 0 ? 'phuyu_anulado' : '']">
                                <td class="text-muted fw-semibold">{{ dato.codpedido }}</td>
                                <td>{{ dato.documento }}</td>
                                <td class="fw-semibold text-dark">{{ dato.cliente }}</td>
                                <td>{{ dato.fechapedido }}</td>
                                <td>{{ dato.tipo }}</td>
                                <td>{{ dato.seriecomprobante }} - {{ dato.nrocomprobante }}</td>

                                <td class="text-end">
                                    <span v-if="dato.estado != 0" class="fw-bold text-success fs-6">
                                        S/. {{ dato.importe }}
                                    </span>
                                    <span v-else class="fw-semibold text-muted">
                                        S/. {{ dato.importe }}
                                    </span>
                                </td>

                                <td>
                                    <span v-if="dato.condicionpago == 1" class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle">
                                        <i class="bi bi-cash me-1"></i> Al contado
                                    </span>
                                    <span v-else class="badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle">
                                        <i class="bi bi-credit-card me-1"></i> Al crédito
                                    </span>
                                </td>

                                <td>
                                    <span v-if="dato.estadoproceso == 0" class="badge rounded-pill bg-warning-subtle text-warning border border-warning-subtle">
                                        <i class="bi bi-hourglass-split me-1"></i> Pendiente
                                    </span>
                                    <span v-else class="badge rounded-pill bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-check-circle me-1"></i> Atendido
                                    </span>
                                </td>

                                <td class="text-center">
                                    <input
                                        type="radio"
                                        class="form-check-input"
                                        name="phuyu_seleccionar"
                                        v-on:click="phuyu_seleccionar(dato.codpedido, dato.estado)"
                                    >
                                </td>
                            </tr>

                            <tr v-if="!cargando && datos.length === 0">
                                <td colspan="10" class="text-center text-muted py-5">
                                    <i class="bi bi-inbox d-block mb-2" style="font-size: 32px;"></i>
                                    No se encontraron pedidos
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <?php include("application/views/phuyu/phuyu_paginacion.php"); ?>
                </div>

                <div id="modal_reportes" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog modal-fullscreen">
                        <div class="modal-content border-0 rounded-0">
                            <div class="modal-header">
                                <h4 class="modal-title mb-0 w-100 text-center">
                                    <i class="bi bi-file-earmark-pdf me-2"></i>
                                    <b style="letter-spacing: 3px;"><?php echo $_SESSION["phuyu_empresa"]; ?></b>
                                </h4>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                            </div>

                            <div class="modal-body p-0" id="reportes_modal" style="height:450px;">
                                <iframe id="phuyu_pdf" src="" style="width:100%; height:100%; border:none;"></iframe>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<style>
.phuyu-card-pedidos {
    border-radius: 16px;
}

.phuyu-search-box .form-control {
    border-radius: 12px;
    min-height: 40px;
}

.phuyu-btn-group .phuyu-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    border-radius: 10px;
    font-weight: 700;
    padding: 7px 12px;
    transition: all .2s ease;
    box-shadow: 0 2px 6px rgba(15, 23, 42, .06);
}

.phuyu-btn-group .phuyu-btn i {
    font-size: 14px;
    transition: transform .2s ease;
}

.phuyu-btn-group .phuyu-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(15, 23, 42, .14);
}

.phuyu-btn-group .phuyu-btn:hover i {
    transform: scale(1.15);
}

.phuyu-btn-group .phuyu-btn:active {
    transform: scale(.96);
}

.phuyu-tabla-wrapper {
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid #eef1f4;
}

.phuyu-tabla-pedidos thead th {
    background: #f8f9fb;
    color: #495057;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    border-bottom: 1px solid #e9ecef;
    white-space: nowrap;
    vertical-align: middle;
}

.phuyu-tabla-pedidos tbody td {
    font-size: 13px;
    vertical-align: middle;
    border-color: #f1f3f5;
}

.phuyu-tabla-pedidos tbody tr {
    transition: background .15s ease, transform .15s ease;
}

.phuyu-tabla-pedidos tbody tr:hover {
    background: #fafcff;
}

.phuyu_anulado {
    opacity: .65;
    background: #fff5f5 !important;
}

.phuyu_anulado td {
    color: #8b8b8b !important;
}

.form-check-input {
    cursor: pointer;
}

@media (max-width: 768px) {
    #title {
        font-size: 1.6rem !important;
    }

    .phuyu-btn-group {
        justify-content: flex-start !important;
    }

    .phuyu-btn-group .phuyu-btn {
        flex: 1 1 auto;
    }

    .phuyu-tabla-pedidos tbody td,
    .phuyu-tabla-pedidos thead th {
        font-size: 12px;
    }
}
</style>

<script>
    var pantalla = jQuery(document).height();
    $("#reportes_modal").css({ height: pantalla - 65 });
</script>

<script src="<?php echo base_url(); ?>phuyu/phuyu_pedidos/index.js"></script>
