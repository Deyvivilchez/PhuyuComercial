<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<style>
  #phuyu_ventas .btn-xs {
    font-size: 10px;
    line-height: 1.5;
  }

  #phuyu_ventas .phuyu-card-ventas {
    border-radius: 16px;
  }

  #phuyu_ventas .phuyu-search-box .form-control {
    border-radius: 12px;
    min-height: 40px;
  }

  #phuyu_ventas .phuyu-btn-group .phuyu-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    border-radius: 10px;
    font-weight: 700;
    padding: 7px 12px;
    transition: all .2s ease;
    box-shadow: 0 2px 6px rgba(15, 23, 42, .06);
    white-space: nowrap;
  }

  #phuyu_ventas .phuyu-btn-group .phuyu-btn i {
    font-size: 14px;
    transition: transform .2s ease;
  }

  #phuyu_ventas .phuyu-btn-group .phuyu-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(15, 23, 42, .14);
  }

  #phuyu_ventas .phuyu-btn-group .phuyu-btn:hover i {
    transform: scale(1.15);
  }

  #phuyu_ventas .phuyu-btn-group .phuyu-btn:active {
    transform: scale(.96);
  }

  #phuyu_ventas .phuyu-table-wrapper {
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid #eef1f4;
  }

  #phuyu_ventas .phuyu-table-ventas {
    font-size: 0.75rem;
  }

  #phuyu_ventas .phuyu-table-ventas thead th {
    background: #f8f9fb;
    color: #495057;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    border-bottom: 1px solid #e9ecef;
    white-space: nowrap;
    vertical-align: middle;
  }

  #phuyu_ventas .phuyu-table-ventas tbody td {
    font-size: 13px;
    vertical-align: middle;
    border-color: #f1f3f5;
  }

  #phuyu_ventas .phuyu-table-ventas tbody tr {
    transition: background .15s ease;
  }

  #phuyu_ventas .phuyu-table-ventas tbody tr:hover {
    background: #fafcff;
  }

  #phuyu_ventas .phuyu_anulado {
    opacity: .65;
    background: #fff5f5 !important;
    text-decoration: line-through;
  }

  #phuyu_ventas .phuyu_anulado td {
    color: #8b8b8b !important;
  }

  #phuyu_ventas .phuyu-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    border-radius: 999px;
    padding: 0.28rem 0.65rem;
    font-size: 0.7rem;
    font-weight: 700;
    white-space: nowrap;
  }

  #phuyu_ventas .phuyu-download-btn {
    border-radius: 999px;
    font-weight: 700;
    font-size: 11px;
  }

  #phuyu_ventas .form-check-input {
    cursor: pointer;
  }

  @media (max-width: 768px) {
    #phuyu_ventas #title {
      font-size: 1.6rem !important;
    }

    #phuyu_ventas .phuyu-btn-group {
      justify-content: flex-start !important;
    }

    #phuyu_ventas .phuyu-btn-group .phuyu-btn {
      flex: 1 1 auto;
    }

    #phuyu_ventas .phuyu-table-ventas tbody td,
    #phuyu_ventas .phuyu-table-ventas thead th {
      font-size: 12px;
    }
  }
</style>

<div id="phuyu_ventas" class="phuyu-velzon-list phuyu-ventas-velzon">

  <div class="row g-3 align-items-start mb-4">
    <div class="col-12 col-md-6">
      <div class="phuyu-page-title mb-0">
        <span class="phuyu-page-icon"><i class="bi bi-receipt"></i></span>
        <div>
          <div class="text-muted small text-uppercase fw-semibold">Ventas</div>
          <h4 class="mb-1" id="title">Ventas</h4>
          <p class="text-muted mb-0">Administración de comprobantes, pagos y documentos emitidos.</p>
        </div>
      </div>
    </div>

    <div class="col-12 col-md-6 mt-3 mt-md-0">
      <div class="row g-2">

        <div class="col-md-4 col-6">
          <label class="form-label small fw-semibold">
            <i class="bi bi-calendar-date me-1"></i> DESDE
          </label>
          <input type="date" class="form-control form-control-sm" id="fecha_desde" v-on:change="phuyu_buscar()" autocomplete="off">
        </div>

        <div class="col-md-4 col-6">
          <label class="form-label small fw-semibold">
            <i class="bi bi-calendar-check me-1"></i> HASTA
          </label>
          <input type="date" class="form-control form-control-sm" id="fecha_hasta" value="<?php echo date('Y-m-d');?>" v-on:change="phuyu_buscar()" autocomplete="off">
        </div>

        <div class="col-md-4 col-12">
          <label class="form-label small fw-semibold">
            <i class="bi bi-printer me-1"></i> FORMATO IMPRESIÓN
          </label>
          <select class="form-select form-select-sm" v-model="formato_impresion" v-on:change="phuyu_formato()">
            <option value="a4">A4 IMPRESIÓN</option>
            <option value="a5">A5 IMPRESIÓN</option>
            <option value="ticket">TICKET IMPRESIÓN</option>
          </select>
        </div>

      </div>
    </div>
  </div>

  <div class="phuyu_body">
    <div class="card border-0 shadow-sm phuyu-card-ventas">
      <div class="card-body p-3 p-lg-4">

        <input type="hidden" id="caja" value="<?php echo $caja;?>">
        <input type="hidden" id="almacen" value="<?php echo $almacen;?>">
        <input type="hidden" id="formato" value="<?php echo $_SESSION['phuyu_formato'];?>">

        <div class="row g-3 mb-4 align-items-center">
          <div class="col-sm-12 col-md-5 col-lg-4 col-xxl-3">
            <div class="position-relative phuyu-search-box">
              <input
                class="form-control form-control-sm ps-5"
                v-model="buscar"
                v-on:keyup="phuyu_buscar()"
                placeholder="Buscar registro..."
                autocomplete="off"
              >
              <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
            </div>
          </div>

          <div class="col-sm-12 col-md-7 col-lg-8 col-xxl-9">
            <div class="d-flex flex-wrap gap-2 justify-content-md-end phuyu-btn-group">

              <button type="button" class="btn btn-success btn-sm phuyu-btn" title="Nueva venta" data-bs-toggle="tooltip" v-on:click="phuyu_nuevo()">
                <i class="bi bi-plus-circle"></i>
                <span>Nuevo</span>
              </button>

              <button type="button" class="btn btn-danger btn-sm phuyu-btn" title="Anular venta" data-bs-toggle="tooltip" v-on:click="phuyu_eliminar()">
                <i class="bi bi-trash"></i>
                <span>Anular</span>
              </button>

              <button type="button" class="btn btn-info btn-sm text-white phuyu-btn" title="Ver detalle" data-bs-toggle="tooltip" v-on:click="phuyu_ver()">
                <i class="bi bi-eye"></i>
                <span>Ver</span>
              </button>

              <button type="button" class="btn btn-primary btn-sm phuyu-btn" title="Imprimir comprobante" data-bs-toggle="tooltip" v-on:click="phuyu_imprimir()">
                <i class="bi bi-printer"></i>
                <span>Imprimir</span>
              </button>

              <button type="button" class="btn btn-warning btn-sm phuyu-btn" title="Restaurar venta anulada" data-bs-toggle="tooltip" v-on:click="restaurar_venta()">
                <i class="bi bi-arrow-counterclockwise"></i>
                <span>Restaurar</span>
              </button>

              <button type="button" class="btn btn-secondary btn-sm phuyu-btn" title="Clonar venta" data-bs-toggle="tooltip" v-on:click="phuyu_clonar()">
                <i class="bi bi-copy"></i>
                <span>Clonar</span>
              </button>

            </div>
          </div>
        </div>

        <div class="phuyu_cargando text-center py-3" v-if="cargando">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando...</span>
          </div>
        </div>

        <div class="table-responsive phuyu-table-wrapper">
          <table class="table table-hover table-sm align-middle mb-0 phuyu-table-ventas">
            <thead>
              <tr>
                <th>ID</th>
                <th>Comprobante</th>
                <th>Documento</th>
                <th>Razón social</th>
                <th>Fecha</th>
                <th width="110px" class="text-end">Importe</th>
                <th>Pago</th>
                <th>E. Sunat</th>
                <th>Descargar</th>
                <th width="30px" class="text-center">
                  <i class="bi bi-check2-circle"></i>
                </th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="dato in datos" :class="[dato.estado==0 ? 'phuyu_anulado' : '']">
                <td class="fw-bold text-muted">{{dato.codkardex}}</td>

                <td>
                  <span class="fw-semibold">
                    {{dato.abreviatura}}: ({{dato.seriecomprobante}} - {{dato.nrocomprobante}})
                  </span>
                  <span v-if="dato.referencia" class="badge bg-secondary-subtle text-secondary border border-secondary-subtle mt-1 d-inline-flex align-items-center gap-1">
                    <i class="bi bi-receipt"></i>
                    Pedido: {{dato.referencia}}
                  </span>
                </td>

                <td>{{dato.documento}}</td>
                <td class="fw-semibold text-dark">{{dato.cliente}}</td>

                <td>
                  {{dato.fechacomprobante}}<br>
                  <small class="text-muted">
                    <i class="bi bi-clock me-1"></i>{{dato.hora}}
                  </small>
                </td>

                <td class="text-end">
                  <b :class="dato.estado!=0 ? 'text-success fs-6' : 'text-muted'">
                    S/. {{dato.importe}}
                  </b>
                </td>

                <td>
                  <span v-if="dato.condicionpago==1" class="phuyu-badge bg-primary-subtle text-primary border border-primary-subtle">
                    <i class="bi bi-cash"></i> CONTADO
                  </span>
                  <span v-else class="phuyu-badge bg-warning-subtle text-warning border border-warning-subtle">
                    <i class="bi bi-credit-card"></i> CRÉDITO
                  </span>
                </td>

                <td class="text-center">
                  <span v-if="dato.estadosunat==0" class="phuyu-badge bg-danger-subtle text-danger border border-danger-subtle">
                    <i class="bi bi-hourglass-split"></i> PENDIENTE
                  </span>
                  <span v-else class="phuyu-badge bg-success-subtle text-success border border-success-subtle">
                    <i class="bi bi-check-circle"></i> ENVIADO
                  </span>
                </td>

                <td>
                  <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-secondary dropdown-toggle phuyu-download-btn" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="bi bi-download me-1"></i>
                      Formatos
                    </button>

                    <ul class="dropdown-menu">
                      <li>
                        <a class="dropdown-item" href="javascript:void(0)" v-on:click="phuyu_docu('pdf',dato.codkardex)">
                          <i class="bi bi-file-earmark-pdf me-2 text-danger"></i>PDF
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="javascript:void(0)" v-on:click="phuyu_docu('xml',dato.codkardex)">
                          <i class="bi bi-filetype-xml me-2 text-primary"></i>XML
                        </a>
                      </li>
                      <li v-if="dato.estadosunat==1">
                        <a class="dropdown-item" href="javascript:void(0)" v-on:click="phuyu_docu('cdr',dato.codkardex)">
                          <i class="bi bi-file-earmark-check me-2 text-success"></i>CDR
                        </a>
                      </li>
                    </ul>
                  </div>
                </td>

                <td class="text-center">
                  <input
                    type="radio"
                    class="form-check-input"
                    name="phuyu_seleccionar"
                    v-on:click="phuyu_seleccionar(dato.codkardex, dato.estado)"
                  >
                </td>
              </tr>

              <tr v-if="datos.length === 0 && !cargando">
                <td colspan="10" class="text-center text-muted py-5">
                  <i class="bi bi-inbox d-block mb-2" style="font-size: 32px;"></i>
                  No se encontraron ventas
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <?php include("application/views/phuyu/phuyu_paginacion.php");?>

        <div id="modal_reportes" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-fullscreen">
            <div class="modal-content border-0 rounded-0">
              <div class="modal-header">
                <h4 class="modal-title mb-0 w-100 text-center">
                  <i class="bi bi-file-earmark-pdf me-2"></i>
                  <b style="letter-spacing:4px;"><?php echo $_SESSION["phuyu_empresa"];?> </b>
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

<script>
  var pantalla = jQuery(document).height();
  $("#reportes_modal").css({height: pantalla - 65});

  if (typeof bootstrap !== 'undefined') {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
      new bootstrap.Tooltip(el);
    });
  }
</script>

<script src="<?php echo base_url();?>phuyu/phuyu_ventas/index.js"></script>
