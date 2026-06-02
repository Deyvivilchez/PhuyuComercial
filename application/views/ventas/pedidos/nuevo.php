<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<div id="phuyu_operacion" class="phuyu-velzon-form phuyu-ventas-velzon">
    <div class="phuyu-page-title">
        <span class="phuyu-page-icon"><i class="bi bi-bag-check"></i></span>
        <div>
            <div class="text-muted small text-uppercase fw-semibold">Ventas</div>
            <h4 class="mb-1">Nuevo pedido</h4>
            <p class="text-muted mb-0">Registro de pedido con cliente, productos y condición de pago.</p>
        </div>
    </div>

    <form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
        <input type="hidden" id="comprobante" value="<?php echo $comprobantes[0]['codcomprobantetipo']; ?>">
        <input type="hidden" id="serie" value="<?php echo $comprobantes[0]['seriecomprobante']; ?>">
        <input type="hidden" id="comprobantereferencia" value="<?php echo $sucursalreferencia[0]['codcomprobantetipo']; ?>">
        <input type="hidden" id="seriereferencia" value="<?php echo $sucursalreferencia[0]['seriecomprobante']; ?>">
        <input type="hidden" id="stockalmacen" value="<?php echo $_SESSION['phuyu_stockalmacen']; ?>">
        <input type="hidden" id="itemrepetir" value="<?php echo $_SESSION['phuyu_itemrepetir']; ?>">
        <input type="hidden" id="igvsunat" value="<?php echo $_SESSION['phuyu_igv']; ?>">
        <input type="hidden" id="icbpersunat" value="<?php echo $_SESSION['phuyu_icbper']; ?>">
        <input type="hidden" id="formato" value="<?php echo $_SESSION['phuyu_formatopedido']; ?>">
        <input type="hidden" id="seriecomprobantereferencia" v-model="campos.seriecomprobantereferencia">
        <input type="hidden" id="empleado" value="<?php echo $_SESSION['phuyu_codpersona']; ?>">

        <div class="phuyu_body">
            <div class="card border-0 shadow-sm rounded-4">
                <?php
                $disabled = '';
                if ($_SESSION['phuyu_codperfil'] > 3) {
                    $disabled = 'disabled';
                }
                ?>

                <div class="card-body p-4">

                    <div class="mb-4">
                        <h4 class="text-danger mb-0 fw-bold">
                            <i class="bi bi-receipt-cutoff me-1"></i>
                            PEDIDO N° {{ campos.nro }}
                        </h4>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-2">
                            <label class="form-label">Serie</label>
                            <select class="form-select" id="seriecomprobante" v-model="campos.seriecomprobante" v-on:change="phuyu_correlativo()" required>
                                <option value="">SERIE</option>
                                <option v-for="dato in series" v-bind:value="dato.seriecomprobante">
                                    {{ dato.seriecomprobante }}
                                </option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">T. comprobante referencia</label>
                            <select class="form-select" name="codcomprobantetiporeferencia" v-model="campos.codcomprobantetiporeferencia" required>
                                <option value="">SELECCIONE...</option>
                                <?php foreach ($comprobantesreferencia as $key => $value) { ?>
                                    <option value="<?php echo $value['codcomprobantetipo']; ?>">
                                        <?php echo $value['descripcion']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Condición pago</label>
                            <select class="form-select" name="condicionpago" v-model="campos.condicionpago" v-on:change="phuyu_condicionpago()">
                                <option value="1">CONTADO</option>
                                <option value="2">CREDITO</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Seleccionar vendedor</label>
                            <select class="form-select" id="codempleado" name="codempleado" <?php echo $disabled; ?> v-model="campos.codempleado" required>
                                <option value="0">SIN VENDEDOR</option>
                                <?php foreach ($vendedores as $key => $value) { ?>
                                    <option value="<?php echo $value['codpersona']; ?>">
                                        <?php echo $value['razonsocial']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Fecha pedido</label>
                            <input type="date" class="form-control" name="fechacomprobante" id="fechacomprobante" value="<?php echo date('Y-m-d'); ?>" autocomplete="off" required>
                            <input type="hidden" name="fechakardex" id="fechakardex" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>

                    <div class="phuyu-cliente-panel mb-3">
                        <div class="row g-3 align-items-end">

                            <div class="col-md-4">
                                <label class="phuyu-label-cliente">
                                    <i class="bi bi-person-check me-1"></i>
                                    Seleccionar cliente
                                </label>

                                <div class="phuyu-cliente-select-box">
                                    <select id="codpersona" name="codpersona" class="form-select">
                                        <option value="2">CLIENTES VARIOS</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-1">
                                <label class="d-block">&nbsp;</label>
                                <button type="button" class="btn btn-primary phuyu-btn-icon-only w-100" v-on:click="phuyu_addcliente()" title="Agregar cliente">
                                    <i class="bi bi-person-plus"></i>
                                </button>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Cliente del pedido</label>
                                <input type="text" class="form-control" id="cliente" v-model.trim="campos.cliente" autocomplete="off" maxlength="250" placeholder="Razón social del cliente..." required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Dirección cliente</label>
                                <input type="text" class="form-control" id="direccion" v-model.trim="campos.direccion" autocomplete="off" maxlength="250" placeholder="Dirección del cliente..." required>
                            </div>

                            <?php if ($_SESSION["phuyu_rubro"] == 1) { ?>
                                <div class="col-md-4 mt-2">
                                    <label class="form-label">Nro de placa</label>
                                    <input type="text" class="form-control" v-model="campos.nroplaca" autocomplete="off" maxlength="100" placeholder="Nro placa...">
                                </div>
                            <?php } else { ?>
                                <div class="col-md-5" style="display:none">
                                    <label class="form-label">Glosa del pedido</label>
                                    <input type="text" class="form-control" v-model="campos.descripcion" autocomplete="off" maxlength="250" placeholder="Referencia del pedido...">
                                </div>
                            <?php } ?>

                        </div>
                    </div>

                    <div class="row g-3 align-items-end mb-3">
                        <div class="col-md-9">
                            <label class="form-label">Glosa del pedido</label>
                            <input type="text" class="form-control" v-model="campos.descripcion">
                        </div>

                        <div class="col-md-3 text-md-end">
                            <button type="button" class="btn btn-success phuyu-btn-text-icon w-100" v-on:click="phuyu_item()">
                                <i class="bi bi-search"></i>
                                <span>Buscar productos</span>
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive phuyu-table-wrapper">
                        <table class="table table-striped table-hover align-middle phuyu-tabla-detalle mb-0">
                            <thead>
                                <tr>
                                    <th width="7%">Acción</th>
                                    <th width="30%">Producto</th>
                                    <th width="10%">Unidad</th>
                                    <th width="8%">Stock</th>
                                    <th width="8%">Cantidad</th>
                                    <th width="10%">Precio unit.</th>
                                    <th width="8%">I.G.V.</th>
                                    <th width="5%">ICBPER</th>
                                    <th width="10%">Subtotal</th>
                                    <th width="1%" class="text-center"><i class="bi bi-trash"></i></th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr v-for="(dato,index) in detalle">
                                    <td>
                                        <button type="button" data-bs-target="#modal_itemdetalle" class="btn btn-primary btn-sm phuyu-btn-mas" v-on:click="phuyu_itemdetalle(index,dato)">
                                            <i class="bi bi-plus-circle me-1"></i> Más
                                        </button>
                                    </td>

                                    <td style="font-size:10px;">{{ dato.producto }}</td>

                                    <td>
                                        <select class="form-select number" v-model="dato.codunidad" v-on:change="informacion_unidad(index,dato,this.value)" id="codunidad">
                                            <template v-for="(unidads, und) in dato.unidades">
                                                <option v-bind:value="unidads.codunidad" v-if="unidads.factor==1" selected>
                                                    {{ unidads.descripcion }}
                                                </option>
                                                <option v-bind:value="unidads.codunidad" v-if="unidads.factor!=1">
                                                    {{ unidads.descripcion }}
                                                </option>
                                            </template>
                                        </select>
                                    </td>

                                    <td class="text-danger fw-bold">{{ dato.stock }}</td>

                                    <td>
                                        <input type="number" step="0.0001" class="form-control number" v-if="dato.control==1" v-model.number="dato.cantidad" v-on:keyup="phuyu_calcular(dato)" min="0.0001" required>
                                        <input type="number" step="0.0001" class="form-control number" v-if="dato.control==0" v-model.number="dato.cantidad" v-on:keyup="phuyu_calcular(dato)" min="0.0001" required>
                                    </td>

                                    <td>
                                        <input type="number" step="0.0001" class="form-control number" v-if="dato.codafectacionigv==21" v-model.number="dato.precio" min="0" readonly>
                                        <input type="number" step="0.0001" class="form-control number" v-if="dato.codafectacionigv!=21" v-model.number="dato.precio" v-on:keyup="phuyu_calcular(dato)" min="0.001" required v-bind:disabled="dato.porcdescuento==100">
                                    </td>

                                    <td><input type="number" class="form-control number" v-model.number="dato.igv" min="0" readonly></td>
                                    <td><input type="number" class="form-control number" v-model.number="dato.icbper" min="0" readonly></td>

                                    <td v-if="dato.codafectacionigv==21">
                                        <input type="number" step="0.01" class="form-control number" v-model.number="dato.subtotal">
                                    </td>

                                    <td v-if="dato.codafectacionigv!=21">
                                        <input type="number" step="0.01" class="form-control number" v-if="dato.calcular==0" v-model.number="dato.subtotal" readonly>
                                        <input type="number" step="0.01" class="form-control number" v-if="dato.calcular!=0" v-model.number="dato.subtotal" v-on:keyup="phuyu_subtotal(dato)" required v-bind:disabled="dato.porcdescuento==100">
                                    </td>

                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm phuyu-btn-delete" v-on:click="phuyu_deleteitem(index,dato)">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td colspan="8" class="text-end fw-bold">Subtotal</td>
                                    <td class="text-center">{{ totales.valorventa }}</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="8" class="text-end fw-bold">I.G.V</td>
                                    <td class="text-center">{{ totales.igv }}</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="8" class="text-end fw-bold text-danger">Total</td>
                                    <td class="text-center fs-6">
                                        <b class="text-danger">{{ totales.importe }}</b>
                                    </td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="border-top pt-3 mt-3">
                        <div class="d-flex justify-content-end flex-wrap gap-2">
                            <button type="button" class="btn btn-warning phuyu-btn-text-icon" v-on:click="phuyu_venta()">
                                <i class="bi bi-plus-circle"></i>
                                <span>Nuevo pedido</span>
                            </button>

                            <button type="submit" class="btn btn-info phuyu-btn-text-icon text-white" v-bind:disabled="estado==1">
                                <i class="bi bi-save"></i>
                                <span>Guardar pedido</span>
                            </button>

                            <button type="button" class="btn btn-danger phuyu-btn-text-icon" v-on:click="phuyu_atras()">
                                <i class="bi bi-arrow-left-circle"></i>
                                <span>Atrás</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </form>

    <!-- Tus modales se mantienen igual -->
    <div id="modal_cuotas" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <!-- deja aquí tu modal_cuotas original -->
    </div>

    <div id="modal_masconfiguraciones" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <!-- deja aquí tu modal_masconfiguraciones original -->
    </div>

    <div id="modal_itemdetalle" data-bs-backdrop="static" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <!-- deja aquí tu modal_itemdetalle original -->
    </div>

    <div id="modal_reportes" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <!-- deja aquí tu modal_reportes original -->
    </div>
</div>

<style>
#phuyu_operacion label,
#phuyu_operacion .form-label {
    font-size: 11px;
    font-weight: 800;
    color: #343a40;
    margin-bottom: 6px;
    text-transform: uppercase;
}

#phuyu_operacion .form-control,
#phuyu_operacion .form-select {
    border-radius: 9px;
}

.phuyu-cliente-panel {
    border: 1px solid rgba(64,81,137,.12);
    border-radius: 16px;
    padding: 16px;
    background: #fff;
}

.phuyu-label-cliente {
    color: #405189;
    font-weight: 900;
    letter-spacing: .35px;
}

.phuyu-cliente-select-box {
    position: relative;
    padding: 4px;
    border-radius: 13px;
    background: linear-gradient(135deg, rgba(64,81,137,.13), rgba(10,179,156,.12));
    border: 1px solid rgba(64,81,137,.15);
}

.phuyu-cliente-select-box .form-select,
.phuyu-cliente-select-box select {
    min-height: 40px;
    border-radius: 9px;
    border: 1px solid #d6dce5;
    background-color: #fff;
    font-weight: 700;
    font-size: 12px;
    color: #343a40;
}

.phuyu-cliente-select-box .select2-container {
    width: 100% !important;
}

.phuyu-cliente-select-box .select2-selection--single {
    height: 40px !important;
    border-radius: 9px !important;
    border: 1px solid #d6dce5 !important;
    display: flex !important;
    align-items: center !important;
}

.phuyu-cliente-select-box .select2-selection__rendered {
    font-weight: 700 !important;
    font-size: 12px !important;
    color: #343a40 !important;
    line-height: 40px !important;
}

.phuyu-btn-text-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: .45rem;
    font-weight: 700;
    border-radius: 10px;
}

.phuyu-btn-icon-only {
    width: 40px;
    height: 40px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
}

.phuyu-btn-icon-only i {
    font-size: 18px;
}

.phuyu-btn-mas {
    min-width: 74px;
    border-radius: 8px;
}

.phuyu-btn-delete {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.phuyu-table-wrapper {
    border: 1px solid #eef1f4;
    border-radius: 14px;
    overflow: auto;
}

.phuyu-tabla-detalle {
    font-size: 11px;
    min-width: 1120px;
}

.phuyu-tabla-detalle thead th {
    background: #f3f6f9;
    font-weight: 800;
    text-transform: uppercase;
    white-space: nowrap;
}

.phuyu-tabla-detalle td {
    vertical-align: middle;
}

.phuyu-tabla-detalle .form-control,
.phuyu-tabla-detalle .form-select {
    min-height: 34px;
    font-size: 12px;
}
</style>

<script src="<?php echo base_url(); ?>phuyu/phuyu_pedidos/nuevo.js"></script>
<script src="<?php echo base_url(); ?>phuyu/phuyu_personas_2.js"></script>
