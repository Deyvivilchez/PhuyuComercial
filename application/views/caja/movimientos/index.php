<div id="phuyu_datos">
  <div class="row mb-3">
    <div class="col-12 col-md-6">
      <?php if ($_SESSION['phuyu_codcontroldiario'] == 0): ?>
        <span class="badge bg-danger">CAJA CERRADA</span>
      <?php endif; ?>
      <h1 class="display-6 fw-bold">Administración de Movimientos de Caja</h1>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb small">
          <li class="breadcrumb-item"><a href="javascript:;">Inicio</a></li>
          <li class="breadcrumb-item active" aria-current="page">Movimientos</li>
        </ol>
      </nav>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <input type="hidden" id="phuyu_opcion" value="1">
      <div class="row align-items-end g-2">
        <!-- BUSCADOR -->
        <div class="col-lg-3 col-md-4 col-sm-6">
          <label class="form-label">Buscar</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" v-model="buscar" @keyup="phuyu_buscar" placeholder="Buscar registro...">
          </div>
        </div>

        <!-- RANGO DE FECHAS -->
        <div class="col-sm-6 col-md-2">
          <label class="form-label">Desde</label>
          <input type="date" class="form-control" v-model="fecha_desde">
        </div>
        <div class="col-sm-6 col-md-2">
          <label class="form-label">Hasta</label>
          <input type="date" class="form-control" v-model="fecha_hasta">
        </div>

        <!-- BOTONES DE ACCIÓN -->
        <?php if ($_SESSION['phuyu_codcontroldiario'] != 0): ?>
        <div class="col-12 col-md-auto text-end mt-3">
          <div class="d-flex flex-wrap gap-2 justify-content-end">
            <div class="btn-group">
              <button class="btn btn-success" @click="phuyu_nuevo">
                <i class="bi bi-plus-circle"></i> Nuevo
              </button>
              <button class="btn btn-info" @click="phuyu_transferencias">
                <i class="bi bi-arrow-left-right"></i> Transferencias
                <span class="badge bg-danger ms-1"><?php echo $transferencias[0]['cantidad']; ?></span>
              </button>
              <button class="btn btn-warning" @click="phuyu_editar">
                <i class="bi bi-pencil-square"></i> Editar
              </button>
              <button class="btn btn-danger" @click="phuyu_eliminar">
                <i class="bi bi-trash"></i> Eliminar
              </button>
            </div>

            <div class="btn-group">
              <button class="btn btn-outline-success" @click="exportarExcelDetallado">
                <i class="bi bi-file-earmark-excel"></i> Excel
              </button>
              <button class="btn btn-outline-danger" @click="exportarPdfDetallado">
                <i class="bi bi-file-earmark-pdf"></i> PDF
              </button>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>

      <!-- LOADER -->
      <div class="phuyu_cargando my-4" v-if="cargando">
        <div class="overlay-spinner"></div>
      </div>

      <!-- TABLA -->
      <div v-if="!cargando" class="table-responsive mt-3">
        <table class="table table-bordered table-hover table-sm align-middle">
          <thead class="table-light">
            <tr class="text-center">
              <th>Fecha</th>
              <th>Nº Recibo</th>
              <th>Concepto</th>
              <th>Razón Social</th>
              <th>Referencia</th>
              <th>Tipo</th>
              <th>Importe (S/)</th>
              <th>Sel.</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(dato, index) in datos" :class="{ 'table-danger': dato.estado == 0 }">
              <td>{{ dato.fechamovimiento }}</td>
              <td>{{ dato.seriecomprobante + '-' + dato.nrocomprobante }}</td>
              <td>{{ dato.concepto }}</td>
              <td>{{ dato.razonsocial }}</td>
              <td>{{ dato.referencia }}</td>
              <td class="text-center">
                <span class="badge bg-danger" v-if="dato.tipomovimiento == 2">Egreso</span>
                <span class="badge bg-warning text-dark" v-if="dato.tipomovimiento == 1">Ingreso</span>
              </td>
              <td class="text-end">{{ dato.importe_r }}</td>
              <td class="text-center">
                <input type="radio" class="form-check-input" name="phuyu_seleccionar" @click="phuyu_seleccionar(dato.codmovimiento)">
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <?php include("application/views/phuyu/phuyu_paginacion.php"); ?>
    </div>
  </div>

  <!-- MODAL TRANSFERENCIAS -->
  <?php include("application/views/caja/modal_transferencias.php"); ?>
</div>

<script>
  if (typeof AcornIcons !== 'undefined') new AcornIcons().replace();
  if (typeof Icons !== 'undefined') new Icons();
</script>
<script src="<?php echo base_url(); ?>phuyu/phuyu_caja/movi_index.js"></script>
