<?php
    if ($info[0]["codmovimientotipo"]==2) {
        $tipo = 3;
        $operacion = "COMPRA";
        $ope1 = "RECIBIDO";
        $ope2 = "RECIBIR";
        $accion = "RECIBIR COMPRA";
        $icono = "bi-box-arrow-in-down";
        $tono = "primary";
    } else {
        $tipo = 4;
        $operacion = "VENTA";
        $ope1 = "DESPACHADO";
        $ope2 = "DESPACHAR";
        $accion = "DESPACHAR VENTA";
        $icono = "bi-truck";
        $tono = "info";
    }
?>

<style>
    #phuyu_despacho.phuyu-despacho-form {
        color: #212529;
    }
    #phuyu_despacho.phuyu-despacho-form .phuyu-action-card {
        border: 1px solid #e9ebec;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(56, 65, 74, 0.08);
        overflow: hidden;
    }
    #phuyu_despacho.phuyu-despacho-form .phuyu-action-header {
        align-items: center;
        background: #f3f6f9;
        border: 1px solid #e9ebec;
        border-radius: 8px;
        display: flex;
        gap: 12px;
        margin-bottom: 18px;
        padding: 14px 16px;
    }
    #phuyu_despacho.phuyu-despacho-form .phuyu-action-icon {
        align-items: center;
        border-radius: 8px;
        display: inline-flex;
        font-size: 22px;
        height: 44px;
        justify-content: center;
        width: 44px;
    }
    #phuyu_despacho.phuyu-despacho-form .phuyu-action-icon.primary {
        background: rgba(64, 81, 137, 0.12);
        color: #405189;
    }
    #phuyu_despacho.phuyu-despacho-form .phuyu-action-icon.info {
        background: rgba(41, 156, 219, 0.12);
        color: #299cdb;
    }
    #phuyu_despacho.phuyu-despacho-form .phuyu-section-title {
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
    #phuyu_despacho.phuyu-despacho-form .phuyu-panel {
        border: 1px solid #e9ebec;
        border-radius: 8px;
        height: 100%;
        padding: 14px;
    }
    #phuyu_despacho.phuyu-despacho-form .detalle,
    #phuyu_despacho.phuyu-despacho-form .entregas {
        max-height: 340px;
        overflow: auto;
    }
    #phuyu_despacho.phuyu-despacho-form .entregas {
        max-height: 300px;
    }
    #phuyu_despacho.phuyu-despacho-form .table {
        font-size: 12px;
    }
    #phuyu_despacho.phuyu-despacho-form .table thead th {
        background: #f3f6f9;
        color: #495057;
        font-size: 11px;
        text-transform: uppercase;
        white-space: nowrap;
    }
    #phuyu_despacho.phuyu-despacho-form .form-control,
    #phuyu_despacho.phuyu-despacho-form .phuyu-input {
        border: 1px solid #d9e2ef;
        border-radius: 6px;
        box-shadow: none;
        min-height: 36px;
        width: 100%;
    }
    #phuyu_despacho.phuyu-despacho-form .phuyu-input:focus {
        border-color: #0ab39c;
        outline: 0;
    }
    #phuyu_despacho.phuyu-despacho-form .phuyu-pending {
        color: #f06548;
        font-weight: 700;
    }
    #phuyu_despacho.phuyu-despacho-form .phuyu-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: center;
        padding-top: 12px;
    }
    @media (max-width: 767.98px) {
        #phuyu_despacho.phuyu-despacho-form .phuyu-action-header {
            align-items: flex-start;
            flex-direction: column;
        }
        #phuyu_despacho.phuyu-despacho-form .phuyu-action-header .badge {
            margin-left: 0 !important;
        }
    }
</style>

<div id="phuyu_despacho" class="phuyu-despacho-form">
    <div class="phuyu_body">
        <div class="card phuyu-action-card">
            <div class="card-body">
                <div class="phuyu-action-header">
                    <div class="phuyu-action-icon <?php echo $tono;?>">
                        <i class="bi <?php echo $icono;?>"></i>
                    </div>
                    <div>
                        <p class="text-muted text-uppercase mb-1">Operación de almacén</p>
                        <h5 class="mb-0"><?php echo $accion;?></h5>
                    </div>
                    <span class="badge bg-light text-dark ms-auto">
                        Comprobante: <?php echo $info[0]["seriecomprobante"]." - ".$info[0]["nrocomprobante"]?>
                    </span>
                </div>

                <div class="row g-3">
                    <div class="col-md-7 col-xs-12">
                        <div class="phuyu-panel">
                            <form id="formulario" v-on:submit.prevent="phuyu_guardar()">
                                <h5 class="phuyu-section-title">Detalle de la <?php echo $operacion;?></h5>

                                <div class="detalle table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr align="center">
                                                <th width="20%">PRODUCTO</th>
                                                <th width="12%">UNIDAD</th>
                                                <th width="10%">CANTIDAD</th>
                                                <th width="10%"><?php echo $ope1;?></th>
                                                <th width="10%">PENDIENTE</th>
                                                <th width="10%"><?php echo $ope2;?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(dato,index) in detalle">
                                                <td>{{dato.producto}}</td>
                                                <td>{{dato.unidad}} </td>
                                                <td>{{dato.cantidad}} </td>
                                                <td>{{dato.recogido}} </td>
                                                <td class="phuyu-pending">{{dato.pendiente}}</td>
                                                <td>
                                                    <input type="number" step="0.0001" class="phuyu-input number" v-model.number="dato.recoger" min="0" v-bind:max="dato.pendiente" required>
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
                                        <i class="bi bi-save me-1"></i> Guardar <?php echo $ope1; ?>
                                    </button>
                                    <button type="button" class="btn btn-light" v-on:click="phuyu_cerrar()">
                                        <i class="bi bi-x-lg me-1"></i> Cancelar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-md-5 col-xs-12">
                        <div class="phuyu-panel">
                            <h5 class="phuyu-section-title">Entregas o despachos realizados</h5>
                            <div class="entregas table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr align="center">
                                            <th width="8%">ID</th>
                                            <th width="15%">FECHA</th>
                                            <th width="20%">PRODUCTO</th>
                                            <th width="15%">UNIDAD</th>
                                            <th width="10%">CANTIDAD</th>
                                            <th width="5%"> </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="dato in entregados">
                                            <td>{{dato.codkardexalmacen}}</td>
                                            <td>{{dato.fechakardex}}</td>
                                            <td>{{dato.producto}}</td>
                                            <td>{{dato.unidad}} </td>
                                            <td>{{dato.cantidad}} </td>
                                            <td>
                                                <button type="button" class="btn btn-warning btn-sm" v-on:click="phuyu_imprimir(dato.codkardexalmacen)" title="IMPRIMIR"><i class="bi bi-printer"></i></button>
                                                <button type="button" class="btn btn-danger btn-sm" v-on:click="phuyu_eliminar(dato)"><i class="bi bi-trash"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
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
<script> var campos = {"codkardex":"<?php echo $info[0]['codkardex'];?>","codmovimientotipo":"<?php echo $info[0]['codmovimientotipo'];?>","codcomprobantetipo":"<?php echo $tipo;?>","observacion":""};</script>
<script src="<?php echo base_url();?>phuyu/phuyu_despachos/nuevo.js"> </script>
<script>
    var div_altura = jQuery(document).height(); var detalle = div_altura - 320; var entregas = div_altura - 250;
</script>
