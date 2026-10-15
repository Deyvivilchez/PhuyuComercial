<style>
    #phuyu_historial.phuyu-historial-prestamos {
        color: #212529;
    }
    #phuyu_historial.phuyu-historial-prestamos .phuyu-list-card {
        border: 1px solid #e9ebec;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(56, 65, 74, 0.08);
    }
    #phuyu_historial.phuyu-historial-prestamos .phuyu-header {
        align-items: center;
        background: #f3f6f9;
        border: 1px solid #e9ebec;
        border-radius: 8px;
        display: flex;
        gap: 12px;
        margin-bottom: 16px;
        padding: 14px 16px;
    }
    #phuyu_historial.phuyu-historial-prestamos .phuyu-header-icon {
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
    #phuyu_historial.phuyu-historial-prestamos label {
        color: #495057;
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 6px;
        text-transform: uppercase;
    }
    #phuyu_historial.phuyu-historial-prestamos .form-control,
    #phuyu_historial.phuyu-historial-prestamos .form-select {
        border: 1px solid #d9e2ef;
        border-radius: 6px;
        box-shadow: none;
        min-height: 38px;
    }
    #phuyu_historial.phuyu-historial-prestamos .phuyu-toolbar {
        align-items: center;
        gap: 8px;
    }
    #phuyu_historial.phuyu-historial-prestamos .table-responsive {
        max-height: 340px;
        overflow: auto;
    }
    #phuyu_historial.phuyu-historial-prestamos .table {
        font-size: 12px;
    }
    #phuyu_historial.phuyu-historial-prestamos .table thead th {
        background: #f3f6f9;
        color: #495057;
        font-size: 11px;
        text-transform: uppercase;
        white-space: nowrap;
    }
    #phuyu_historial.phuyu-historial-prestamos .modal-content {
        border: 0;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.14);
    }
    #phuyu_historial.phuyu-historial-prestamos .modal-header {
        background: #f3f6f9;
        border-bottom: 1px solid #e9ebec;
    }
</style>

<div id="phuyu_historial" class="phuyu-historial-prestamos">
    <div class="phuyu_body">
        <div class="card phuyu-list-card">
            <div class="card-body">
                <input type="hidden" id="tipo" value="29">
                <div class="phuyu-header">
                    <div class="phuyu-header-icon">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <p class="text-muted text-uppercase mb-1">Historial de préstamos</p>
                        <h5 class="mb-0">Prestamos devueltos a: <?php echo $info[0]["cliente"];?></h5>
                    </div>
                </div>

                <div class="row g-2 align-items-end mb-3">
                    <div class="col-md-2">
                        <label>FECHA INICIAL</label>
                        <input type="date" class="form-control" id="fechadesde" value="<?php echo date('Y-m-01');?>" autocomplete="off">
                    </div>
                    <div class="col-md-2">
                        <label>FECHA FIN</label>
                        <input type="date" class="form-control" id="fechahasta" value="<?php echo date('Y-m-d');?>" autocomplete="off">
                    </div>
                    <div class="col-md-2">
                        <label>FILTRO?</label>
                        <select class="form-select" v-model="campos.filtro">
                            <option value="1">FECHAS FILTRO (SI)</option>
                            <option value="0">FECHAS FILTRO (NO)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex flex-wrap justify-content-end phuyu-toolbar">
                            <button type="button" class="btn btn-success" v-on:click="phuyu_prestamos()">
                                <i class="bi bi-search me-1"></i> Consulta
                            </button>
                            <button type="button" class="btn btn-light" v-on:click="phuyu_cerrar()">
                                <i class="bi bi-arrow-left me-1"></i> Cerrar
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th width="30px">ID</th>
                                <th>SALIDAS</th>
                                <th width="200px">FECHA DEVOL.</th>
                                <th width="200px">IMPORTE</th>
                                <th width="100px">VER</th>
                                <th width="100px">EDITAR</th>
                                <th width="100px">ANULAR</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="dato in prestamos" v-bind:class="[dato.estado==0 ? 'phuyu_anulado':'']">
                                <td>000{{dato.codkardex}}</td>
                                <td>{{dato.seriecomprobante}}-{{dato.nrocomprobante}}</td>
                                <td>{{dato.fechakardex}}</td>
                                <td>{{dato.importe}}</td>
                                <td>
                                    <button type="button" class="btn btn-info btn-sm" v-on:click="phuyu_ver(dato.codkardex)">
                                        <i class="bi bi-eye me-1"></i> Ver
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-warning btn-sm" v-on:click="phuyu_editar(dato.codkardex)">
                                        <i class="bi bi-pencil-square me-1"></i> Editar
                                    </button>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-sm" v-on:click="phuyu_eliminar(dato.codkardex,dato.codkardex_ref)">
                                        <i class="bi bi-trash me-1"></i> Anular
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="modal_editar" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-fullscreen-xxl-down">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Editar devolución de préstamo</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="cuerpo">
                        </div>
                    </div>
                </div>
            </div>

            <div id="modal_ver" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Ver devolución</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="modalver">
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
<script src="<?php echo base_url();?>phuyu/phuyu_prestamos/historial.js"> </script>
