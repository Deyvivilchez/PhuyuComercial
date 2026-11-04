<style>
  #phuyu_datos { --m-primary: var(--vz-primary, #405189); --m-purple: #73508f; --m-ink: var(--vz-heading-color, #495057); --m-muted: var(--vz-secondary-color, #878a99); --m-border: var(--vz-border-color, #e9ebec); min-width: 0; max-width: 100%; color: var(--m-ink); font-family: var(--vz-font-family-secondary, Arial), sans-serif; }
  #phuyu_datos .movimientos-cabecera { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
  #phuyu_datos .movimientos-marca { display: grid; place-items: center; flex: 0 0 44px; height: 44px; border: 1px solid #e7d8f3; border-radius: 12px; background: #f2eafa; color: var(--m-purple); font-size: 24px; }
  #phuyu_datos .movimientos-eyebrow { font-size: 10px; letter-spacing: 1.2px; font-weight: 700; color: var(--m-muted); margin-bottom: 4px; }
  #phuyu_datos .movimientos-cabecera h1 { margin: 0; font-size: clamp(19px, 1.7vw, 24px); line-height: 1.25; font-weight: 700; letter-spacing: -.4px; color: var(--m-ink); }
  #phuyu_datos .movimientos-cabecera .breadcrumb { margin: 4px 0 0; font-size: 10px; }
  #phuyu_datos .movimientos-cabecera .breadcrumb a { color: var(--m-muted); }
  #phuyu_datos .movimientos-apertura { margin-left: auto; padding: 7px 12px; border-radius: 30px; font-size: 11px; font-weight: 600; white-space: nowrap; color: #197552; background: #edf8f1; border: 1px solid #d8eee1; }
  #phuyu_datos .movimientos-apertura.cerrada { color: #b64747; background: #fdf0f0; border-color: #f3dada; }
  #phuyu_datos > .card { border: 1px solid var(--m-border); border-radius: 18px; box-shadow: 0 8px 28px rgba(51,30,71,.04) !important; }
  #phuyu_datos .card-body { padding: 22px; }
  #phuyu_datos .nav-pills { width: fit-content; display: flex; padding: 4px; gap: 4px !important; background: #f6f3f9; border: 1px solid #eee7f4; border-radius: 11px; }
  #phuyu_datos .nav-pills .nav-link { padding: 10px 16px; border: 0; border-radius: 8px; font-size: 12px; font-weight: 650; color: #82718f; background: transparent; }
  #phuyu_datos .nav-pills .nav-link.active { color: #fff; background: var(--m-primary); box-shadow: 0 3px 9px rgba(64,81,137,.14); }
  #phuyu_datos .movimientos-filtros { display: grid; grid-template-columns: minmax(220px, 1.8fr) repeat(4, minmax(120px, 1fr)) auto; gap: 12px; margin: 16px 0 0; padding: 16px; border: 1px solid var(--m-border); border-radius: 12px; background: #f8f9fb; }
  #phuyu_datos .movimientos-filtros > div { width: auto; max-width: none; margin: 0; padding: 0; min-width: 0; }
  #phuyu_datos .movimientos-filtros > .col-12 { grid-column: 1 / -1; }
  #phuyu_datos .movimientos-filtros .form-label { display: block; color: #73617f; font-size: 12px; font-weight: 600; margin-bottom: 7px; }
  #phuyu_datos .movimientos-filtros .form-control, #phuyu_datos .movimientos-filtros .form-select { height: 40px; min-width: 0; border: 1px solid #ced4da; border-radius: 8px; background-color: #fff; color: #495057; font-size: 13px; }
  #phuyu_datos .movimientos-filtros .form-control:focus, #phuyu_datos .movimientos-filtros .form-select:focus { border-color: #b68bcc; box-shadow: 0 0 0 3px #f0e6f7; }
  #phuyu_datos .movimientos-filtros .input-group-text { background: white; border-color: #ded4e8; color: #957ca7; border-radius: 8px 0 0 8px; }
  #phuyu_datos .movimientos-filtros .input-group .form-control { border-left: 0; border-radius: 0 8px 8px 0; padding-left: 0; }
  #phuyu_datos .btn { border-radius: 8px; font-size: 12px; font-weight: 600; padding: 9px 13px; }
  #phuyu_datos .btn-primary { color: #fff; background: var(--m-primary); border-color: var(--m-primary); }
  #phuyu_datos .movimientos-acciones .btn-success { color: #fff; background: var(--vz-success, #0ab39c); border-color: var(--vz-success, #0ab39c); }
  #phuyu_datos .movimientos-toolbar-wrap { padding-top: 14px !important; border-top: 1px solid var(--m-border); }
  #phuyu_datos .movimientos-toolbar { align-items: center; justify-content: space-between !important; gap: 10px !important; }
  #phuyu_datos .movimientos-toolbar .btn-group { display: flex; flex-wrap: wrap; gap: 7px; }
  #phuyu_datos .movimientos-toolbar .btn-group > .btn { margin: 0; border-radius: 8px !important; }
  #phuyu_datos .movimientos-acciones .btn-info { color: #684087; background: #efe7f7; border-color: #e6d9f0; }
  #phuyu_datos .movimientos-acciones .btn-warning { color: #95691c; background: #fff8e9; border-color: #f2e4bd; }
  #phuyu_datos .movimientos-acciones .btn-danger { color: #b94b53; background: #fff2f3; border-color: #f2d8dc; }
  #phuyu_datos .movimientos-exportaciones { margin-left: auto; }
  #phuyu_datos .movimientos-exportaciones .btn { background: #fff; }
  #phuyu_datos .movimientos-ayuda { display: flex; align-items: center; gap: 7px; font-size: 11px; color: var(--m-muted); margin: 16px 0 9px; }
  #phuyu_datos .movimientos-scroll { width: 100%; max-width: 100%; max-height: 58vh; overflow: auto; overscroll-behavior: contain; border: 1px solid var(--m-border); border-radius: 12px; scrollbar-color: #baa8ca #f7f3fa; }
  #phuyu_datos .movimientos-scroll::-webkit-scrollbar { width: 11px; height: 11px; }
  #phuyu_datos .movimientos-scroll::-webkit-scrollbar-track { background: #f7f3fa; }
  #phuyu_datos .movimientos-scroll::-webkit-scrollbar-thumb { background: #baa8ca; border: 3px solid #f7f3fa; border-radius: 10px; }
  #phuyu_datos .movimientos-tabla { min-width: 1900px; margin: 0; border-collapse: separate; border-spacing: 0; font-size: 13px; }
  #phuyu_datos .movimientos-tabla thead th { position: sticky; top: 0; z-index: 2; color: #495057; background: #f3f6f9; border: 0; border-bottom: 1px solid #e9ebec; white-space: nowrap; vertical-align: middle; font-weight: 650; padding: 13px 12px; }
  #phuyu_datos .movimientos-tabla td { padding: 12px; border: 0; border-bottom: 1px solid #eee9f3; max-width: 280px; overflow-wrap: anywhere; vertical-align: middle; }
  #phuyu_datos .movimientos-tabla tbody tr:nth-child(even) { background: #fafbfc; }
  #phuyu_datos .movimientos-tabla tbody tr:hover { background: #f3f5f9; }
  #phuyu_datos .movimientos-tabla .movimiento-monto, #phuyu_datos .movimientos-tabla .movimiento-importe { min-width: 140px; white-space: nowrap; font-variant-numeric: tabular-nums; }
  #phuyu_datos .movimientos-tabla .movimiento-monto { color: var(--m-purple); background: #faf7fd; font-weight: 700; }
  #phuyu_datos .movimientos-tabla .movimiento-seleccion { min-width: 56px; background: #fff; }
  #phuyu_datos .movimientos-tabla thead .movimiento-seleccion { background: #f5f0f9; }
  #phuyu_datos .movimientos-tabla .movimiento-estado { min-width: 120px; }
  #phuyu_datos .movimientos-tabla .movimiento-pago { min-width: 150px; white-space: nowrap; overflow-wrap: normal; }
  #phuyu_datos .movimientos-tabla .movimiento-recibo { font-weight: 600; color: var(--m-primary); }
  #phuyu_datos .badge { border-radius: 6px; padding: 5px 7px; font-size: 10px; font-weight: 600; }
  #phuyu_datos .movimiento-anulado td, #phuyu_datos .movimiento-anulado td * { color: #b71c1c !important; text-decoration: line-through; }
  #phuyu_datos .movimiento-anulado td { background: #fff2f2 !important; }
  #phuyu_datos .movimiento-anulado .badge { background: transparent !important; }
  #phuyu_datos .movimiento-anulado .btn-restaurar { display: inline-block; margin-top: 6px; padding: 5px 9px; color: #581c87 !important; text-decoration: none !important; background: #fff !important; border: 1px solid #d8c7eb; border-radius: 6px; font-weight: 600; }
  #phuyu_datos .movimientos-resumen { margin-top: 18px; padding: 0; }
  #phuyu_datos .movimientos-resumen-titulo { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 12px; font-weight: 650; color: #766282; }
  #phuyu_datos .movimientos-resumen-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
  #phuyu_datos .movimientos-resumen-item { min-width: 0; padding: 16px 18px; border: 1px solid var(--m-border); border-radius: 12px; background: #fff; }
  #phuyu_datos .movimientos-resumen-item span { display: block; margin-bottom: 9px; font-size: 11px; color: var(--m-muted); }
  #phuyu_datos .movimientos-resumen-item span i { margin-right: 6px; color: #8c63a5; }
  #phuyu_datos .movimientos-resumen-item strong { display: block; color: #46324f; font-size: 22px; font-weight: 700; letter-spacing: -.5px; font-variant-numeric: tabular-nums; overflow-wrap: anywhere; }
  #phuyu_datos .movimientos-resumen-total { background: var(--m-primary); border-color: var(--m-primary); }
  #phuyu_datos .movimientos-resumen-total span, #phuyu_datos .movimientos-resumen-total span i { color: #e4d1f2; }
  #phuyu_datos .movimientos-resumen-total strong { color: white; }
  #phuyu_datos .movimientos-paginacion { margin-top: 18px; }
  #phuyu_datos .movimientos-scroll:focus-visible, #phuyu_datos .btn:focus-visible { outline: 2px solid #9363b5; outline-offset: 3px; }
  @media(max-width: 1199px) { #phuyu_datos .movimientos-filtros { grid-template-columns: repeat(3, minmax(0, 1fr)); } #phuyu_datos .movimientos-filtros > div:first-child { grid-column: span 2; } }
  @media(max-width: 767px) { #phuyu_datos .card-body { padding: 14px; } #phuyu_datos .movimientos-cabecera { gap: 10px; flex-wrap: wrap; } #phuyu_datos .movimientos-marca { flex-basis: 42px; height: 42px; border-radius: 12px; font-size: 20px; } #phuyu_datos .movimientos-apertura { margin-left: 52px; } #phuyu_datos .movimientos-filtros { padding: 12px; grid-template-columns: repeat(2,minmax(0,1fr)); } #phuyu_datos .movimientos-filtros > div:first-child { grid-column: 1 / -1; } #phuyu_datos .movimientos-filtros > .col-auto { grid-column: 1 / -1; } #phuyu_datos .movimientos-filtros > .col-auto .btn { width: 100%; } #phuyu_datos .movimientos-resumen-grid { grid-template-columns: repeat(2,minmax(0,1fr)); gap: 8px; } #phuyu_datos .movimientos-resumen-item { padding: 12px; } #phuyu_datos .movimientos-resumen-item strong { font-size: 18px; } #phuyu_datos .nav-pills { width: 100%; } #phuyu_datos .nav-pills .nav-link { flex: 1; padding: 9px 6px; font-size: 11px; } }
</style>
<div id="phuyu_datos" data-inicio-mes="<?php echo date('Y-m-01'); ?>" data-hoy="<?php echo date('Y-m-d'); ?>" data-transferencias-pendientes="<?php echo (int)$transferencias[0]['cantidad']; ?>">
  <header class="movimientos-cabecera">
    <div class="movimientos-marca" aria-hidden="true"><i class="bi bi-wallet2"></i></div>
    <div>
      <div class="movimientos-eyebrow">TESORERÍA</div>
      <h1>Movimientos de caja</h1>
      <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item">Inicio</li><li class="breadcrumb-item active" aria-current="page">Movimientos</li></ol></nav>
    </div>
    <span class="movimientos-apertura <?php echo (int)$_SESSION['phuyu_codcontroldiario'] === 0 ? 'cerrada' : ''; ?>"><i class="bi bi-circle-fill" aria-hidden="true"></i> <?php echo (int)$_SESSION['phuyu_codcontroldiario'] === 0 ? 'Caja cerrada' : 'Caja abierta'; ?></span>
  </header>

  <div class="card shadow-sm">
    <div class="card-body">
      <input type="hidden" id="phuyu_opcion" value="1">
      <div class="nav nav-pills gap-2 mb-3" role="tablist" aria-label="Consulta de movimientos">
        <button type="button" class="nav-link" :class="{active: vista === 'actual'}" role="tab" :aria-selected="vista === 'actual'" @click="cambiar_vista('actual')">Apertura actual</button>
        <button type="button" class="nav-link" :class="{active: vista === 'historial'}" role="tab" :aria-selected="vista === 'historial'" @click="cambiar_vista('historial')">Historial de esta caja</button>
      </div>
      <p class="text-muted small" v-if="vista === 'actual'">Movimientos de la apertura activa. Para consultar otras aperturas, ingresa al historial.</p>
      <p class="text-muted small" v-else>Consulta todas las aperturas de esta caja dentro del rango seleccionado.</p>
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
          <button type="button" class="btn btn-primary" @click="phuyu_buscar" :disabled="cargando"><i class="bi bi-search" aria-hidden="true"></i> Buscar</button>
        </div>
        <div class="col-12 text-muted small">Excel y PDF consultan todas las aperturas de esta caja en el rango de fechas, con los filtros de búsqueda seleccionados.</div>
        <!-- BOTONES DE ACCIÓN -->
        <div class="col-12 movimientos-toolbar-wrap">
          <div class="d-flex flex-wrap movimientos-toolbar">
            <div class="btn-group movimientos-acciones" v-if="vista === 'actual' && <?php echo (int)$_SESSION['phuyu_codcontroldiario']; ?> > 0">
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

            <div class="btn-group movimientos-exportaciones">
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
      <p class="movimientos-ayuda" v-if="!cargando"><i class="bi bi-arrow-left-right" aria-hidden="true"></i> Desliza para ver todos los detalles. Los anulados se muestran en rojo y no suman a los totales.</p>
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
              <th>Estado</th>
              <th class="movimiento-importe">Pagado (S/)</th>
              <th class="movimiento-importe">Entregado (S/)</th>
              <th class="movimiento-importe">Vuelto (S/)</th>
              <th class="movimiento-monto">Total (S/)</th>
              <th class="movimiento-seleccion" v-if="vista === 'actual'">Sel.</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="datos.length === 0"><td :colspan="vista === 'actual' ? 14 : 13" class="text-center text-muted py-4">No se encontraron movimientos con estos filtros.</td></tr>
            <tr v-for="(dato, index) in datos" :class="{ 'table-danger': dato.estado == 0, 'movimiento-anulado': dato.estado == 0 }">
              <td class="text-nowrap">{{ dato.fechamovimiento }}<br><small class="text-muted">{{ dato.hora_movimiento || '—' }}</small></td>
              <td class="text-nowrap movimiento-recibo">{{ dato.seriecomprobante + '-' + dato.nrocomprobante }}</td>
              <td>
                <small class="text-muted" v-if="dato.comprobante_referencia">{{ dato.tipo_comprobante_referencia || 'Documento de referencia' }}</small>
                <div>{{ dato.comprobante_referencia || '—' }}</div>
                <small class="text-muted" v-if="dato.fecha_comprobante_referencia">Fecha doc.: {{ dato.fecha_comprobante_referencia }}</small>
              </td>
              <td>{{ dato.concepto }}</td>
              <td>{{ dato.razonsocial }}</td>
              <td>{{ dato.referencia }}</td>
              <td class="text-center movimiento-pago">{{ dato.tipopago }}</td>
              <td class="text-center">
                <span class="badge bg-danger" v-if="dato.tipomovimiento == 2">Egreso</span>
                <span class="badge bg-warning text-dark" v-if="dato.tipomovimiento == 1">Ingreso</span>
              </td>
              <td class="text-center movimiento-estado"><span class="badge bg-danger" v-if="dato.estado == 0">ANULADO</span><span class="badge bg-success" v-else>ACTIVO</span>
                <?php if (!empty($puede_restaurar)): ?>
                <button type="button" class="btn btn-sm btn-restaurar" v-if="dato.estado == 0" :disabled="restaurando !== 0" @click.stop="phuyu_restaurar(dato)">{{ restaurando == dato.codmovimiento ? 'Restaurando…' : 'Restaurar' }}</button>
                <?php endif; ?>
              </td>
              <td class="text-end movimiento-importe">{{ formato_importe(dato.importe_pagado) }}</td>
              <td class="text-end movimiento-importe">{{ formato_importe(dato.importe_entregado) }}</td>
              <td class="text-end movimiento-importe">{{ formato_importe(dato.vuelto) }}</td>
              <td class="text-end movimiento-monto">{{ formato_importe(dato.importe_r) }}</td>
              <td class="text-center movimiento-seleccion" v-if="vista === 'actual'">
                <input type="radio" class="form-check-input" name="phuyu_seleccionar" :disabled="dato.estado == 0" @click="phuyu_seleccionar(dato.codmovimiento)">
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
