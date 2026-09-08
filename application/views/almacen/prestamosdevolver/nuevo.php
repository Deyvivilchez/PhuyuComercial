<style>
    #phuyu_despacho.phuyu-prestamo-form {
        color: #212529;
    }
    #phuyu_despacho.phuyu-prestamo-form .phuyu-action-card {
        border: 1px solid #e9ebec;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(56, 65, 74, 0.08);
        overflow: hidden;
    }
    #phuyu_despacho.phuyu-prestamo-form .phuyu-action-header {
        align-items: center;
        background: #f3f6f9;
        border: 1px solid #e9ebec;
        border-radius: 8px;
        display: flex;
        gap: 12px;
        margin-bottom: 18px;
        padding: 14px 16px;
    }
    #phuyu_despacho.phuyu-prestamo-form .phuyu-action-icon {
        align-items: center;
        background: rgba(41, 156, 219, 0.12);
        border-radius: 8px;
        color: #299cdb;
        display: inline-flex;
        font-size: 22px;
        height: 44px;
        justify-content: center;
        width: 44px;
    }
    #phuyu_despacho.phuyu-prestamo-form .phuyu-section-title {
        border-bottom: 1px solid #e9ebec;
        color: #343a40;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: .2px;
        margin-bottom: 12px;
        padding-bottom: 10px;
        text-align: center;
        text-transform: uppercase;
    }
    #phuyu_despacho.phuyu-prestamo-form .detalle {
        max-height: 360px;
        overflow: auto;
    }
    #phuyu_despacho.phuyu-prestamo-form .table {
        font-size: 12px;
    }
    #phuyu_despacho.phuyu-prestamo-form .table thead th {
        background: #f3f6f9;
        color: #495057;
        font-size: 11px;
        text-transform: uppercase;
        white-space: nowrap;
    }
    #phuyu_despacho.phuyu-prestamo-form .form-control,
    #phuyu_despacho.phuyu-prestamo-form .phuyu-input {
        border: 1px solid #d9e2ef;
        border-radius: 6px;
        box-shadow: none;
        min-height: 36px;
        width: 100%;
    }
    #phuyu_despacho.phuyu-prestamo-form .phuyu-input:focus {
        border-color: #0ab39c;
        outline: 0;
    }
    #phuyu_despacho.phuyu-prestamo-form .phuyu-pending {
        color: #f06548;
        font-weight: 700;
    }
    #phuyu_despacho.phuyu-prestamo-form .phuyu-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: center;
        padding-top: 12px;
    }
    @media (max-width: 767.98px) {
        #phuyu_despacho.phuyu-prestamo-form .phuyu-action-header {
            align-items: flex-start;
            flex-direction: column;
        }
        #phuyu_despacho.phuyu-prestamo-form .phuyu-action-header .badge {
            margin-left: 0 !important;
        }
    }
</style>

<div id="phuyu_despacho" class="phuyu-prestamo-form">
    <div class="phuyu_body">
        <div class="card phuyu-action-card">
            <div class="card-body">
                <div class="phuyu-action-header">
                    <div class="phuyu-action-icon">
                        <i class="bi bi-arrow-left-right"></i>
                    </div>
                    <div>
                        <p class="text-muted text-uppercase mb-1">Operación de préstamo</p>
                        <h5 class="mb-0">Devolver préstamo</h5>
                    </div>
                    <span class="badge bg-light text-dark ms-auto">
                        Comprobante: <?php echo $info[0]["seriecomprobante"]." - ".$info[0]["nrocomprobante"]?>
                    </span>
                </div>

                <form id="formulario" v-on:submit.prevent="phuyu_guardar()">
                    <h5 class="phuyu-section-title">Detalle del préstamo</h5>

                    <div class="detalle table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr align="center">
                                    <th width="20%">PRODUCTO</th>
                                    <th width="12%">UNIDAD</th>
                                    <th width="10%">CANTIDAD</th>
                                    <th width="10%">DEVUELTO</th>
                                    <th width="10%">PENDIENTE</th>
                                    <th width="10%">DEVOLVER</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(dato,index) in detalle">
                                    <td>{{dato.producto}}</td>
                                    <td>{{dato.unidad}} </td>
                                    <td>{{dato.cantidadprestada}} </td>
                                    <td>{{dato.cantidaddevuelta}} </td>
                                    <td class="phuyu-pending">{{dato.cantidad}}</td>
                                    <td>
                                        <input type="number" step="0.0001" class="phuyu-input number" v-model.number="dato.recoger" min="0" v-bind:max="dato.cantidad" required>
                                        <input type="hidden" v-model="dato.codunidad" name="">
                                        <input type="hidden" v-model="dato.preciobruto" name="">
                                        <input type="hidden" v-model="dato.preciosinigv" name="">
                                        <input type="hidden" v-model="dato.preciorefunitario" name="">
                                        <input type="hidden" v-model="dato.preciounitario" name="">
                                        <input type="hidden" v-model="dato.codafectacionigv" name="">
                                        <input type="hidden" v-model="dato.valorventa" name="">
                                        <input type="hidden" v-model="dato.igv" name="">
                                        <input type="hidden" v-model="dato.subtotal" name="">
                                        <input type="hidden" v-model="dato.item" name="">
                                        <input type="hidden" v-model="dato.factor" name="">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="row form-group mt-3">
                        <div class="col-md-12">
                            <textarea class="form-control" v-model="campos.observacion" placeholder="Escribir una observación(opcional)"></textarea>
                        </div>
                    </div>
                    <div class="phuyu-actions">
                        <button type="submit" class="btn btn-success" v-bind:disabled="estado==1">
                            <i class="bi bi-save me-1"></i> Guardar operación
                        </button>
                        <button type="button" class="btn btn-light" v-on:click="phuyu_cerrar()">
                            <i class="bi bi-x-lg me-1"></i> Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    if (typeof AcornIcons !== 'undefined') {
        new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
        const icons = new Icons();
    }
</script>
<script> var campos = {"codkardex_ref":"<?php echo $info[0]['codkardex'];?>","codcomprobantetipo":"<?php echo $info[0]['codcomprobantetipo'];?>","observacion":"","codpersona":"<?php echo $info[0]['codpersona'];?>","seriecomprobante_ref":"<?php echo $info[0]['seriecomprobante'];?>","nrocomprobante_ref": "<?php echo $info[0]['nrocomprobante'];?>"};</script>
<script src="<?php echo base_url();?>phuyu/phuyu_prestamos/nuevo.js"> </script>
<script>
    var div_altura = jQuery(document).height(); var detalle = div_altura - 320; var entregas = div_altura - 250;
</script>
