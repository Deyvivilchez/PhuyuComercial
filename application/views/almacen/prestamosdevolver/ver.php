<style>
    #phuyu_form.phuyu-prestamo-detalle {
        color: #212529;
    }
    #phuyu_form.phuyu-prestamo-detalle .phuyu-detail-card {
        border: 1px solid #e9ebec;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(56, 65, 74, 0.08);
        overflow: hidden;
    }
    #phuyu_form.phuyu-prestamo-detalle .phuyu-detail-header {
        align-items: center;
        background: #f3f6f9;
        border: 1px solid #e9ebec;
        border-radius: 8px;
        display: flex;
        gap: 12px;
        margin-bottom: 16px;
        padding: 14px 16px;
    }
    #phuyu_form.phuyu-prestamo-detalle .phuyu-detail-icon {
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
    #phuyu_form.phuyu-prestamo-detalle .phuyu-info-grid {
        display: grid;
        gap: 10px;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        margin-bottom: 18px;
    }
    #phuyu_form.phuyu-prestamo-detalle .phuyu-info-item {
        border: 1px solid #e9ebec;
        border-radius: 8px;
        padding: 10px 12px;
    }
    #phuyu_form.phuyu-prestamo-detalle .phuyu-info-item span {
        color: #878a99;
        display: block;
        font-size: 11px;
        font-weight: 700;
        margin-bottom: 4px;
        text-transform: uppercase;
    }
    #phuyu_form.phuyu-prestamo-detalle .table thead th {
        background: #f3f6f9;
        color: #495057;
        font-size: 11px;
        text-transform: uppercase;
        white-space: nowrap;
    }
    @media (max-width: 767.98px) {
        #phuyu_form.phuyu-prestamo-detalle .phuyu-detail-header {
            align-items: flex-start;
            flex-direction: column;
        }
        #phuyu_form.phuyu-prestamo-detalle .phuyu-info-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div id="phuyu_form" class="phuyu-prestamo-detalle">
    <div class="card phuyu-detail-card">
        <div class="card-body">
            <div class="phuyu-detail-header">
                <div class="phuyu-detail-icon">
                    <i class="bi bi-arrow-left-right"></i>
                </div>
                <div>
                    <p class="text-muted text-uppercase mb-1">Detalle de la devolución</p>
                    <h5 class="mb-0">SALIDA: 000<?php echo $info[0]["codkardex"];?></h5>
                </div>
                <span class="badge bg-info ms-auto">Fecha: <?php echo $info[0]["fechakardex"];?></span>
            </div>

            <div class="phuyu-info-grid">
                <div class="phuyu-info-item">
                    <span>Comprobante</span>
                    <strong><?php echo 'SALIDA : '.$info[0]["seriecomprobante"].'-'.$info[0]["nrocomprobante"];?></strong>
                </div>
                <div class="phuyu-info-item">
                    <span>Documento</span>
                    <strong><?php echo $info[0]["documento"];?></strong>
                </div>
                <div class="phuyu-info-item">
                    <span>Socio</span>
                    <strong><?php echo $info[0]["cliente"];?></strong>
                </div>
            </div>

            <h5 class="text-center mb-3">Detalle de la devolución</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th width="5px">ID</th>
                            <th>CODIGO</th>
                            <th>PRODUCTO</th>
                            <th>UNIDAD</th>
                            <th>CANTIDAD DEVUELTA</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            foreach ($detalle as $key => $value) { ?>
                                <tr>
                                    <td><?php echo $value["codproducto"];?></td>
                                    <td><?php echo $value["codigo"];?></td>
                                    <td><?php echo $value["producto"];?></td>
                                    <td><?php echo $value["unidad"];?></td>
                                    <td><?php echo round($value["cantidad"],2);?></td>
                                </tr>
                            <?php }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
