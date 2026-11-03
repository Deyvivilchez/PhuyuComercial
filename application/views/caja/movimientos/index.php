<style>
  #phuyu_datos { min-width: 0; max-width: 100%; }
  #phuyu_datos .movimientos-scroll {
    width: 100%; max-width: 100%; max-height: 60vh;
    overflow: auto; overscroll-behavior: contain;
    border: 1px solid #e2e8f0; border-radius: 10px;
    scrollbar-width: auto; scrollbar-color: #aab6cb #f1f4f9;
  }
  #phuyu_datos .movimientos-scroll::-webkit-scrollbar { width: 12px; height: 12px; }
  #phuyu_datos .movimientos-scroll::-webkit-scrollbar-track { background: #f1f4f9; }
  #phuyu_datos .movimientos-scroll::-webkit-scrollbar-thumb { background: #aab6cb; border: 3px solid #f1f4f9; border-radius: 8px; }
  #phuyu_datos .movimientos-tabla { min-width: 1900px; margin: 0; border-collapse: separate; border-spacing: 0; }
  #phuyu_datos .movimientos-tabla thead th {
    position: sticky; top: 0; z-index: 2; background: #f3f5f9;
    color: #34415c; white-space: nowrap; vertical-align: middle;
    padding: 12px 10px; border-bottom: 1px solid #dce3ed;
  }
  #phuyu_datos .movimientos-tabla td { max-width: 280px; overflow-wrap: anywhere; padding: 10px; }
  #phuyu_datos .movimientos-tabla .movimiento-monto,
  #phuyu_datos .movimientos-tabla .movimiento-importe { min-width: 140px; white-space: nowrap; }
  #phuyu_datos .movimientos-tabla .movimiento-monto { font-weight: 700; color: #344777; background: #f6f8fc; }
  #phuyu_datos .movimientos-tabla .movimiento-seleccion {
    position: sticky; right: 0; z-index: 1;
    min-width: 56px; width: 56px; background: #fff;
    box-shadow: -2px 0 5px rgba(36,50,71,.06);
  }
  #phuyu_datos .movimientos-tabla thead .movimiento-seleccion { z-index: 3; background: #f3f5f9; }
  #phuyu_datos .movimientos-scroll:focus-visible { outline: 2px solid #405189; outline-offset: 2px; }
  #phuyu_datos .movimientos-resumen { margin: 16px 0; padding: 16px; border: 1px solid #e2e8f0; border-radius: 10px; background: #f8fafc; }
  #phuyu_datos .movimientos-resumen-titulo { margin: 0 0 12px; color: #34415c; font-size: 13px; font-weight: 700; }
  #phuyu_datos .movimientos-resumen-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
  #phuyu_datos .movimientos-resumen-item { min-width: 0; padding: 12px 16px; border: 1px solid #e4e9f1; border-radius: 8px; background: #fff; }
  #phuyu_datos .movimientos-resumen-item span { display: block; margin-bottom: 6px; color: #66758c; font-size: 12px; }
  #phuyu_datos .movimientos-resumen-item strong { display: block; color: #34415c; font-size: 18px; font-variant-numeric: tabular-nums; overflow-wrap: anywhere; }
  #phuyu_datos .movimientos-resumen-total { background: #405189; border-color: #405189; }
  #phuyu_datos .movimientos-resumen-total span { color: #e1e7fa; }
  #phuyu_datos .movimientos-resumen-total strong { color: #fff; }
  #phuyu_datos .movimientos-paginacion { margin-top: 16px; }
  #phuyu_datos > .row h1 { font-size: clamp(24px, 2.3vw, 34px); line-height: 1.2; letter-spacing: -.6px; margin-bottom: 10px; color: #283750; }
  #phuyu_datos .card { border: 1px solid #e7ecf3; border-radius: 16px; }
  #phuyu_datos .card-body { padding: 22px; }
  #phuyu_datos .nav-pills { width: fit-content; padding: 5px; background: #f1f4fa; border: 1px solid #e4e9f2; border-radius: 11px; }
  #phuyu_datos .nav-pills .nav-link { border: 0; border-radius: 7px; padding: 10px 16px; font-weight: 600; color: #66758c; background: transparent; }
  #phuyu_datos .nav-pills .nav-link.active { background: #405189; color: #fff; box-shadow: 0 3px 8px rgba(64,81,137,.16); }
  #phuyu_datos .movimientos-filtros { margin: 0; padding: 16px; border: 1px solid #e7ecf3; border-radius: 12px; background: #f9fbfd; }
  #phuyu_datos .movimientos-filtros .form-label { font-size: 12px; font-weight: 600; color: #53647b; margin-bottom: 6px; }
  #phuyu_datos .movimientos-filtros .form-control,
  #phuyu_datos .movimientos-filtros .form-select { min-height: 38px; border-color: #dce3ed; border-radius: 7px; background-color: #fff; }
  #phuyu_datos .movimientos-filtros .input-group .form-control { border-top-left-radius: 0; border-bottom-left-radius: 0; }
  #phuyu_datos .movimientos-filtros .btn { min-height: 38px; }
  #phuyu_datos .movimientos-resumen-item span i { margin-right: 6px; color: #405189; }
  #phuyu_datos .movimientos-resumen-total span i { color: #e1e7fa; }
  @media (max-width: 767px) {
    #phuyu_datos .movimientos-resumen-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
    #phuyu_datos .movimientos-resumen { padding: 12px; }
    #phuyu_datos .movimientos-resumen-item { padding: 10px; }
    #phuyu_datos .movimientos-resumen-item strong { font-size: 16px; }
  }
</style>
<div id="phuyu_datos" data-inicio-mes="<?php echo date('Y-m-01'); ?>" data-hoy="<?php echo date('Y-m-d'); ?>" data-transferencias-pendientes="<?php echo (int)$transferencias[0]['cantidad']; ?>">
  <div class="row mb-3">
    <div class="col-12">
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
      <div class="nav nav-pills gap-2 mb-3" role="tablist" aria-label="Consulta de movimientos">
        <button type="button" class="nav-link" :class="{active: vista === 'actual'}" role="tab" :aria-selected="vista === 'actual'" @click="cambiar_vista('actual')">Apertura actual</button>
        <button type="button" class="nav-link" :class="{active: vista === 'historial'}" role="tab" :aria-selected="vista === 'historial'" @click="cambiar_vista('historial')">Historial de esta caja</button>
      </div>
      <p class="text-muted small" v-if="vista === 'actual'">Movimientos de la apertura activa. Para consultar otras aperturas, ingresa al historial.</p>
      <p class="text-muted small" v-else>Consulta de todas las aperturas de esta caja dentro del rango. Los resultados son de solo lectura.</p>
      <div class="row align-items-end g-2 movimientos-filtros">
        <!-- BUSCADOR -->
        <div class="col-lg-3 col-md-4 col-sm-6">
          <label class="form-label">Buscar</label>
          <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control" v-model="buscar" @keyup.enter="phuyu_buscar" placeholder="Recibo, boleta, factura, persona o referencia">
          </div>
        </div>

        <!-- RANGO DE FECHAS -->
        <div class="col-sm-6 col-md-2">
          <label class="form-label">Desde</label>
          <input type="date" class="form-control" v-model="fecha_desde" @change="phuyu_buscar">
        </div>
        <div class="col-sm-6 col-md-2">
          <label class="form-label">Hasta</label>
          <input type="date" class="form-control" v-model="fecha_hasta" @change="phuyu_buscar">
        </div>
        <div class="col-sm-6 col-md-2">
          <label class="form-label">Forma pago</label>
          <select class="form-select" v-model="codtipopago" @change="phuyu_buscar">
            <option value="0">Todos</option>
            <?php foreach ($tipopagos as $tipopago): ?>
              <option value="<?php echo $tipopago['codtipopago']; ?>"><?php echo $tipopago['descripcion']; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-sm-6 col-md-2">
          <label class="form-label">Movimiento</label>
          <select class="form-select" v-model="tipomovimiento" @change="phuyu_buscar">
            <option value="0">Todos</option>
            <option value="1">Ingresos</option>
            <option value="2">Egresos</option>
          </select>
        </div>

        <div class="col-auto">
          <button type="button" class="btn btn-primary" @click="phuyu_buscar" :disabled="cargando">Buscar</button>
        </div>
        <div class="col-12 text-muted small">Excel y PDF consultan todas las aperturas de esta caja en el rango de fechas, con los filtros de búsqueda seleccionados.</div>
        <!-- BOTONES DE ACCIÓN -->
        <div class="col-12 col-md-auto text-end mt-3">
          <div class="d-flex flex-wrap gap-2 justify-content-end">
            <div class="btn-group" v-if="vista === 'actual' && <?php echo (int)$_SESSION['phuyu_codcontroldiario']; ?> > 0">
              <button class="btn btn-success" @click="phuyu_nuevo">
                <i class="bi bi-plus-circle"></i> Nuevo
              </button>
              <button class="btn btn-info" @click="phuyu_transferencias">
                <i class="bi bi-arrow-left-right"></i> Transferencias
                <span class="badge bg-danger ms-1" v-if="transferencias_pendientes > 0">{{ transferencias_pendientes }}</span>
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
      </div>

      <!-- LOADER -->
      <div class="phuyu_cargando my-4" v-if="cargando">
        <div class="overlay-spinner"></div>
      </div>

      <!-- TABLA -->
      <p class="text-muted small mt-3 mb-2" v-if="!cargando">Desplázate horizontalmente para ver los importes y todos los detalles. El resumen de esta página aparece debajo de la tabla.</p>
      <div v-if="!cargando" class="movimientos-scroll" tabindex="0" role="region" aria-label="Movimientos de caja. Tabla con desplazamiento horizontal y vertical.">
        <table class="table table-bordered table-hover table-sm align-middle movimientos-tabla">
          <thead class="table-light">
            <tr class="text-center">
              <th>Fecha / Hora</th>
              <th>Recibo de caja</th>
              <th>Comprobante relacionado</th>
              <th>Concepto</th>
              <th>Razón Social</th>
              <th>Referencia</th>
              <th>Forma pago</th>
              <th>Tipo</th>
              <th class="movimiento-importe">Pagado (S/)</th>
              <th class="movimiento-importe">Entregado (S/)</th>
              <th class="movimiento-importe">Vuelto (S/)</th>
              <th class="movimiento-monto">Total (S/)</th>
              <th class="movimiento-seleccion" v-if="vista === 'actual'">Sel.</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="datos.length === 0"><td :colspan="vista === 'actual' ? 13 : 12" class="text-center text-muted py-4">No se encontraron movimientos con estos filtros.</td></tr>
            <tr v-for="(dato, index) in datos" :class="{ 'table-danger': dato.estado == 0 }">
              <td class="text-nowrap">{{ dato.fechamovimiento }}<br><small class="text-muted">{{ dato.hora_movimiento || '—' }}</small></td>
              <td class="text-nowrap">{{ dato.seriecomprobante + '-' + dato.nrocomprobante }}</td>
              <td>
                <small class="text-muted" v-if="dato.comprobante_referencia">{{ dato.tipo_comprobante_referencia || 'Documento de referencia' }}</small>
                <div>{{ dato.comprobante_referencia || '—' }}</div>
                <small class="text-muted" v-if="dato.fecha_comprobante_referencia">Fecha doc.: {{ dato.fecha_comprobante_referencia }}</small>
              </td>
              <td>{{ dato.concepto }}</td>
              <td>{{ dato.razonsocial }}</td>
              <td>{{ dato.referencia }}</td>
              <td class="text-center">{{ dato.tipopago }}</td>
              <td class="text-center">
                <span class="badge bg-danger" v-if="dato.tipomovimiento == 2">Egreso</span>
                <span class="badge bg-warning text-dark" v-if="dato.tipomovimiento == 1">Ingreso</span>
              </td>
              <td class="text-end movimiento-importe">{{ formato_importe(dato.importe_pagado) }}</td>
              <td class="text-end movimiento-importe">{{ formato_importe(dato.importe_entregado) }}</td>
              <td class="text-end movimiento-importe">{{ formato_importe(dato.vuelto) }}</td>
              <td class="text-end movimiento-monto">{{ formato_importe(dato.importe_r) }}</td>
              <td class="text-center movimiento-seleccion" v-if="vista === 'actual'">
                <input type="radio" class="form-check-input" name="phuyu_seleccionar" @click="phuyu_seleccionar(dato.codmovimiento)">
              </td>
            </tr>
          </tbody>

        </table>
      </div>

      <section class="movimientos-resumen" v-if="!cargando && datos.length > 0" aria-label="Totales de esta página">
        <h2 class="movimientos-resumen-titulo">Totales de esta página</h2>
        <div class="movimientos-resumen-grid">
          <div class="movimientos-resumen-item"><span><i class="bi bi-wallet2" aria-hidden="true"></i>Pagado</span><strong>{{ formato_importe(totales_pagina.pagado) }}</strong></div>
          <div class="movimientos-resumen-item"><span><i class="bi bi-cash-stack" aria-hidden="true"></i>Entregado</span><strong>{{ formato_importe(totales_pagina.entregado) }}</strong></div>
          <div class="movimientos-resumen-item"><span><i class="bi bi-arrow-return-left" aria-hidden="true"></i>Vuelto</span><strong>{{ formato_importe(totales_pagina.vuelto) }}</strong></div>
          <div class="movimientos-resumen-item movimientos-resumen-total"><span><i class="bi bi-calculator" aria-hidden="true"></i>Total de movimientos</span><strong>{{ formato_importe(totales_pagina.total) }}</strong></div>
        </div>
      </section>
      <div class="movimientos-paginacion">
        <?php include("application/views/phuyu/phuyu_paginacion.php"); ?>
      </div>
    </div>
  </div>

  <!-- MODAL TRANSFERENCIAS -->
  <?php include("application/views/caja/modal_transferencias.php"); ?>
</div>

<script>
  if (typeof AcornIcons !== 'undefined') new AcornIcons().replace();
  if (typeof Icons !== 'undefined') new Icons();
</script>
<script src="<?php echo base_url(); ?>phuyu/phuyu_caja/movi_index.js?v=<?php echo filemtime(FCPATH . 'phuyu/phuyu_caja/movi_index.js'); ?>"></script>
