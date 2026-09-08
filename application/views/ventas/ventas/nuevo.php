<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<style>
  #phuyu_operacion.phuyu-venta-form .phuyu-section {
    padding: 1rem;
    border: 1px solid rgba(64, 81, 137, .10);
    border-radius: .75rem;
    background: #f8fafc;
  }

  #phuyu_operacion.phuyu-venta-form .phuyu-section .form-label {
    font-size: .74rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .03em;
    color: #495057;
    margin-bottom: .4rem;
  }

  #phuyu_operacion.phuyu-venta-form .phuyu-select-wrap .select2-container {
    width: 100% !important;
  }

  #phuyu_operacion.phuyu-venta-form .phuyu-select-wrap .select2-selection--single {
    display: flex !important;
    align-items: center !important;
    border-color: rgba(64, 81, 137, .16) !important;
    border-radius: .375rem !important;
    height: 40px !important;
  }

  #phuyu_operacion.phuyu-venta-form .phuyu-select-wrap .select2-selection__rendered {
    line-height: 40px !important;
    padding-left: .75rem !important;
  }

  #phuyu_operacion.phuyu-venta-form .phuyu-select-wrap .form-select {
    min-height: 40px;
    border-color: rgba(64, 81, 137, .16);
  }

  #phuyu_operacion.phuyu-venta-form .phuyu-btn-icon-only {
    min-height: 40px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding-left: .75rem;
    padding-right: .75rem;
  }
</style>

<div id="phuyu_operacion" class="phuyu-venta-form phuyu-velzon-form phuyu-ventas-velzon">
  <div class="phuyu-page-title">
    <span class="phuyu-page-icon"><i class="bi bi-receipt"></i></span>
    <div>
      <div class="text-muted small text-uppercase fw-semibold">Ventas</div>
      <h4 class="mb-1">Nueva venta</h4>
      <p class="text-muted mb-0">Registro de comprobantes, detalle de productos y pagos.</p>
    </div>
  </div>

  <form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
    <!-- INPUTS OCULTOS (se mantienen igual) -->
    <input type="hidden" id="comprobante" value="<?php echo $sucursal[0]['codcomprobantetipo']; ?>">
    <input type="hidden" id="codempleado" value="<?php echo $_SESSION['phuyu_codempleado']; ?>">
    <input type="hidden" id="serie" value="<?php echo $sucursal[0]['seriecomprobante']; ?>">
    <input type="hidden" id="stockalmacen" value="<?php echo $_SESSION['phuyu_stockalmacen']; ?>">
    <input type="hidden" id="coddespachotipo" value="<?php echo $_SESSION['phuyu_tipodespacho']; ?>">
    <input type="hidden" id="itemrepetir" value="<?php echo $_SESSION['phuyu_itemrepetir']; ?>">
    <input type="hidden" id="igvsunat" value="<?php echo $_SESSION['phuyu_igv']; ?>">
    <input type="hidden" id="icbpersunat" value="<?php echo $_SESSION['phuyu_icbper']; ?>">
    <input type="hidden" id="formato" value="<?php echo $_SESSION['phuyu_formato']; ?>">
    <input type="hidden" id="codpedido" name="codpedido" value="0">
    <input type="hidden" id="codproforma" name="codproforma" value="0">
    <input type="hidden" id="rubro" value="<?php echo $_SESSION['phuyu_rubro']; ?>" name="">
    <input type="hidden" id="afectacionigv" value="<?php echo $_SESSION['phuyu_afectacionigv']; ?>" name="">
    <input type="hidden" id="ventaconpedido" value="<?php echo $_SESSION['phuyu_ventaconpedido']; ?>" name="">
    <input type="hidden" id="ventaconproforma" value="<?php echo $_SESSION['phuyu_ventaconproforma']; ?>" name="">

    <div class="phuyu_body">
      <div class="card border-0 shadow-sm rounded-4">
        <?php $disabled = ($_SESSION['phuyu_codperfil'] > 3) ? 'disabled' : ''; ?>
        <div class="card-body p-3 p-lg-4">
          <!-- Número de venta -->
          <div class="row mb-4">
            <div class="col-12">
              <h4 class="text-danger fw-bold mb-0">VENTA N° {{ campos.nro }}</h4>
            </div>
          </div>

          <!-- Fila 1: tipo comprobante, serie, fechas -->
          <div class="row g-3 mb-3">
            <div class="col-md-3 col-6">
              <label class="form-label fw-semibold small">TIPO COMPROBANTE</label>
              <select class="form-select" name="codcomprobantetipo" v-model="campos.codcomprobantetipo" required v-on:change="phuyu_series()">
                <?php foreach ($comprobantes as $value) { ?>
                  <option value="<?php echo $value['codcomprobantetipo']; ?>"><?php echo $value['descripcion']; ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="col-md-2 col-6">
              <label class="form-label fw-semibold small">SERIE</label>
              <select class="form-select" id="seriecomprobante" v-model="campos.seriecomprobante" v-on:change="phuyu_correlativo()" required>
                <option value="">SERIE</option>
                <option v-for="dato in series" v-bind:value="dato.seriecomprobante">{{ dato.seriedescripcion }}</option>
              </select>
            </div>
            <div class="col-md-2 col-6">
              <label class="form-label fw-semibold small">FECHA VENTA</label>
              <input type="date" class="form-control" name="fechacomprobante" id="fechacomprobante" value="<?php echo date('Y-m-d'); ?>" autocomplete="off" required>
            </div>
            <div class="col-md-2 col-6">
              <label class="form-label fw-semibold small">FECHA KARDEX</label>
              <input type="date" class="form-control" name="fechakardex" id="fechakardex" value="<?php echo date('Y-m-d'); ?>" autocomplete="off" required>
            </div>
            <?php if ($_SESSION['phuyu_rubro'] == 1) { ?>
              <div class="col-md-3 col-12">
                <label class="form-label fw-semibold small">NRO DE PLACA</label>
                <input type="text" class="form-control" v-model="campos.nroplaca" autocomplete="off" maxlength="100" placeholder="Nro placa . . .">
              </div>
            <?php } else { ?>
              <div class="col-md-3 col-12" style="display: none">
                <label class="form-label fw-semibold small">DESCRIPCION DEL MOVIMIENTO</label>
                <input type="text" class="form-control" v-model="campos.descripcion" autocomplete="off" maxlength="250" placeholder="Referencia de la venta . . .">
              </div>
              <div class="col-md-3 col-12">
                <label class="form-label fw-semibold small">CONDICION PAGO</label>
                <select class="form-select" name="condicionpago" v-model="campos.condicionpago" v-on:change="phuyu_condicionpago()">
                  <option value="1">CONTADO</option>
                  <option value="2">CREDITO</option>
                </select>
              </div>
            <?php } ?>
          </div>

          <!-- Fila 2: Cliente -->
          <div class="phuyu-section mb-3">
            <div class="row g-3 align-items-end">
              <div class="col-12 col-lg-4">
                <label class="form-label">Seleccionar cliente</label>
                <div class="phuyu-select-wrap">
                  <select id="codpersona" name="codpersona" class="form-select ajax" v-model="campos.codpersona" required data-live-search="true" v-on:change="phuyu_infocliente()">
                    <option value="2">CLIENTES VARIOS</option>
                  </select>
                </div>
              </div>

              <div class="col-12 col-sm-2 col-lg-1">
                <label class="form-label d-none d-lg-block">&nbsp;</label>
                <button type="button" class="btn btn-primary phuyu-btn-icon-only w-100" v-on:click="phuyu_addcliente()" title="Agregar cliente">
                  <i class="bi bi-person-plus"></i>
                </button>
              </div>

              <div class="col-12 col-md-4 col-lg-2">
                <label class="form-label">Documento cliente</label>
                <input type="text" class="form-control" name="nrodocumento" id="nrodocumento" v-model.trim="campos.nrodocumento" autocomplete="off" readonly>
              </div>

              <div class="col-12 col-md-8 col-lg-5">
                <label class="form-label">Cliente de la venta</label>
                <input type="text" class="form-control" id="cliente" v-model.trim="campos.cliente" autocomplete="off" maxlength="250" placeholder="Razon social del cliente" required>
              </div>

              <div class="col-12">
                <label class="form-label">Direccion cliente</label>
                <input type="text" class="form-control" id="direccion" v-model.trim="campos.direccion" autocomplete="off" maxlength="250" placeholder="Direccion del cliente" required>
              </div>
            </div>
          </div>

          <!-- Fila 3: Moneda, cambio, botón buscar productos -->
          <div class="row g-3 mb-4">
            <div class="col-md-2 col-6">
              <label class="form-label fw-semibold small">MONEDA</label>
              <select class="form-select" name="codmoneda" v-model="campos.codmoneda" v-on:change="phuyu_tipocambio()" required>
                <?php foreach ($monedas as $value) { ?>
                  <option value="<?php echo $value['codmoneda']; ?>"><?php echo $value['simbolo'] . ' ' . $value['descripcion']; ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="col-md-1 col-6">
              <label class="form-label fw-semibold small">CAMBIO</label>
              <input type="number" step="0.001" class="form-control number" name="tipocambio" v-model.number="campos.tipocambio" autocomplete="off" min="1" v-bind:disabled="campos.codmoneda==1" required>
            </div>
            <div class="col-md-3 col-12 d-flex align-items-end justify-content-end">
              <button type="button" class="btn btn-success w-100" v-on:click="phuyu_item()">
                <i class="bi bi-search me-1"></i> Buscar Productos
              </button>
            </div>
          </div>

          <!-- Tabla de productos (detalle) -->
          <div class="row mb-4">
            <div class="col-12">
              <div class="table-responsive">
                <table class="table table-hover table-sm align-middle" style="font-size: 0.75rem;">
                  <thead class="table-light">
                    <tr>
                      <th width="7%"></th>
                      <th width="30%">PRODUCTO</th>
                      <th width="10%">UNIDAD</th>
                      <th width="7%">STOCK</th>
                      <th width="10%">CANTIDAD</th>
                      <th width="10%">PRECIO UNIT.</th>
                      <th width="10%">I.G.V.</th>
                      <th>ICBPER</th>
                      <th width="10%">SUBTOTAL</th>
                      <th width="1%"><i class="bi bi-trash3"></i></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(dato,index) in detalle" :key="index">
                      <td>
                        <button type="button" class="btn btn-primary btn-xs w-100" v-on:click="phuyu_itemdetalle(index,dato)">
                          <b>+ MAS</b>
                        </button>
                      </td>
                      <td v-if="dato.controlarseries == 1">{{ dato.producto }} - <strong class="text-primary">{{ dato.descripcion }}</strong></td>
                      <td v-else>{{ dato.producto }}</td>
                      <td>
                        <select class="form-select form-select-sm" v-model="dato.codunidad" v-on:change="informacion_unidad(index,dato,this.value)">
                          <template v-for="(unidad, und) in dato.unidades">
                            <option v-bind:value="unidad.codunidad" v-if="unidad.factor==1" selected>{{ unidad.descripcion }}</option>
                            <option v-bind:value="unidad.codunidad" v-if="unidad.factor!=1">{{ unidad.descripcion }}</option>
                          </template>
                        </select>
                      </td>
                      <td class="text-danger fw-bold">{{ dato.stock }}</td>
                      <td>
                        <input type="number" step="0.0001" class="form-control form-control-sm number" v-model.number="dato.cantidad" v-on:keyup="phuyu_calcular(dato,3)" required>
                      </td>
                      <td>
                        <input type="number" step="0.0001" class="form-control form-control-sm number" v-if="dato.codafectacionigv==21" v-model.number="dato.precio" min="0" readonly>
                        <input type="number" step="0.0001" class="form-control form-control-sm number" v-if="dato.codafectacionigv!=21" v-model.number="dato.precio" v-on:keyup="phuyu_calcular(dato)" min="0.001" required v-bind:disabled="dato.porcdescuento==100">
                      </td>
                      <td>
                        <input type="number" class="form-control form-control-sm number" v-model.number="dato.igv" min="0" readonly>
                      </td>
                      <td>
                        <input type="number" class="form-control form-control-sm number" v-model.number="dato.icbper" min="0" readonly>
                      </td>
                      <td v-if="dato.codafectacionigv==21">
                        <input type="number" step="0.01" class="form-control form-control-sm number" v-model.number="dato.subtotal">
                      </td>
                      <td v-if="dato.codafectacionigv!=21">
                        <input type="number" step="0.01" class="form-control form-control-sm number" v-if="dato.calcular==0" v-model.number="dato.subtotal" readonly>
                        <input type="number" step="0.01" class="form-control form-control-sm number" v-if="dato.calcular!=0" v-model.number="dato.subtotal" v-on:keyup="phuyu_subtotal(dato)" required v-bind:disabled="dato.porcdescuento==100">
                      </td>
                      <td>
                        <button type="button" class="btn btn-danger btn-xs w-100" v-on:click="phuyu_deleteitem(index,dato)">
                          <b>X</b>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                  <tfoot class="table-group-divider">
                    <tr>
                      <td colspan="8" class="text-end fw-bold">Subtotal</td>
                      <td class="text-center">{{ totales.valorventa }}</td>
                    </tr>
                    <tr>
                      <td colspan="8" class="text-end fw-bold">I.G.V</td>
                      <td class="text-center">{{ totales.igv }}</td>
                    </tr>
                    <tr class="fw-bold">
                      <td colspan="8" class="text-end fs-6">Total</td>
                      <td class="text-center text-danger fs-6">{{ totales.importe }}</td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>
          </div>

          <!-- Botones de acción -->
          <div class="row g-2">
            <div class="col-md-5 col-12">
              <div class="d-grid gap-2 d-md-block">
                <button type="button" class="btn btn-success mb-2 mb-md-0 me-md-2" v-on:click="phuyu_searchpedido()">
                  <i class="bi bi-search me-1"></i> BUSCAR PEDIDO
                </button>
                <button type="button" class="btn btn-primary" v-on:click="phuyu_searchproforma()">
                  <i class="bi bi-search me-1"></i> BUSCAR PROFORMA
                </button>
              </div>
            </div>
            <div class="col-md-7 col-12 text-md-end">
              <div class="d-grid gap-2 d-md-block">
                <button type="button" class="btn btn-warning me-md-2 mb-2 mb-md-0" v-on:click="phuyu_venta()">
                  <i class="bi bi-plus-lg me-1"></i> NUEVA VENTA
                </button>
                <button type="submit" class="btn btn-info me-md-2 mb-2 mb-md-0" v-bind:disabled="estado==1">
                  <i class="bi bi-arrow-right me-1"></i> CONTINUAR VENTA
                </button>
                <button type="button" class="btn btn-danger" v-on:click="phuyu_atras()">
                  <i class="bi bi-arrow-left me-1"></i> ATRAS
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>

  <!-- MODALES (se mantienen igual, solo se ajustan algunos iconos y botones de cierre) -->
  <div id="modal_masconfiguraciones" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" style="font-size:30px;margin-bottom:0px;">
            <i class="bi bi-x-circle-fill"></i>
          </button>
          <h4 class="modal-title"><b style="letter-spacing:1px;">DETALLE DEL ITEM DE LA VENTA</b></h4>
        </div>
        <div class="modal-body" style="height: 380px;">
          <div class="row form-group">
            <div class="col-md-4 col-xs-12" align="center">
              <label>RECOGIDO</label>
              <input type="checkbox" style="height:20px;width:20px;" disabled="true">
            </div>
          </div>
          <div class="row form-group">
            <div class="col-md-4 col-xs-12">
              <label>CENTRO COSTO</label>
              <select class="form-control" v-model="campos.codcentrocosto">
                <option value="0">SIN CENTRO COSTO</option>
                <?php foreach ($centrocostos as $value) { ?>
                  <option value="<?php echo $value['codcentrocosto']; ?>"><?php echo $value['descripcion']; ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="col-md-2 col-xs-12" v-if="rubro==1">
              <label>NRO PLACA</label>
              <input type="text" class="form-control" v-model="campos.nroplaca" autocomplete="off" maxlength="50" placeholder="Nro placa . . .">
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="modal_itemdetalle" data-bs-backdrop="static" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header modal-phuyu-titulo">
          <h4 class="modal-title"><b style="letter-spacing:0.5px;">DETALLE DEL ITEM DE LA VENTA</b></h4>
        </div>
        <div class="modal-body">
          <h6><b>PRODUCTO: {{ item.producto }} &nbsp; <span class="label label-warning">CANTIDAD: {{ item.cantidad }} {{ item.unidad }}</span></b></h6>
          <hr>
          <div class="row form-group">
            <div class="col-md-4 col-xs-12">
              <label>PRECIO BRUTO</label>
              <input type="number" class="form-control number" v-model.number="item.preciobruto" v-on:keyup="phuyu_itemcalcular(item,0)" v-bind:disabled="item.codafectacionigv==21">
            </div>
            <div class="col-md-4 col-xs-12">
              <label>DESCUENTO (S/.)</label>
              <input type="number" class="form-control number" v-model.number="item.descuento" v-on:keyup="phuyu_itemcalcular(item,-1)" v-bind:disabled="item.codafectacionigv==21">
            </div>
            <div class="col-md-4 col-xs-12">
              <label>DESCUENTO (%)</label>
              <input type="number" class="form-control number" v-model.number="item.porcdescuento" v-on:keyup="phuyu_itemcalcular(item,-2)" v-bind:disabled="item.codafectacionigv==21">
            </div>
          </div>
          <div class="row form-group">
            <div class="col-md-4">
              <label>PRECIO SIN I.G.V.</label>
              <input type="number" class="form-control number" v-model.number="item.preciosinigv" v-on:keyup="phuyu_itemcalcular(item,1)" v-bind:disabled="item.codafectacionigv==21">
            </div>
            <div class="col-md-3">
              <label>PRECIO UNITARIO</label>
              <input type="number" class="form-control number" v-model.number="item.precio" v-on:keyup="phuyu_itemcalcular(item,2)" v-bind:disabled="item.codafectacionigv==21">
            </div>
            <div class="col-md-3">
              <label>TIPO AFECTACION</label>
              <select class="form-select" v-model="item.codafectacionigv" v-on:change="phuyu_itemcalcular(item,2)">
                <?php foreach ($afectacionigv as $value) { ?>
                  <option value="<?php echo $value['oficial']; ?>"><?php echo $value['descripcion']; ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="col-md-2">
              <label>ICBPER</label>
              <select class="form-select" v-model="item.conicbper" v-on:change="phuyu_itemcalcular(item,2)">
                <option value="1">SI</option>
                <option value="0">NO</option>
              </select>
            </div>
          </div>
          <div class="form-group text-center">
            <button type="button" class="btn btn-info btn-sm"><b>VALOR VENTA: S/. {{ item.valorventa }}</b></button>
            <button type="button" class="btn btn-danger btn-sm"><b>IGV: S/. {{ item.igv }}</b></button>
            <button type="button" class="btn btn-danger btn-sm"><b>ICBPER: S/. {{ item.icbper }}</b></button>
            <button type="button" class="btn btn-success btn-sm"><b>SUBTOTAL: S/. {{ item.subtotal }}</b></button>
          </div>
          <div class="form-group">
            <label>DESCRIPCION DEL ITEM DE VENTA</label>
            <textarea class="form-control" v-model="item.descripcion" rows="3" maxlength="250"></textarea>
          </div>
          <div class="text-center">
            <button type="button" class="btn btn-success" v-on:click="phuyu_itemcalcular_cerrar(item)">
              <i class="bi bi-save me-1"></i> GUARDAR CAMBIOS DEL ITEM Y <i class="bi bi-x-circle-fill ms-1"></i> CERRAR
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div id="modal_pago" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header modal-phuyu-titulo text-center">
          <button type="button" class="close" data-dismiss="modal"><i class="bi bi-x-circle-fill"></i></button>
          <h4 class="modal-title"></h4>
        </div>
        <div class="modal-body"></div>
      </div>
    </div>
  </div>

  <!-- MODAL FINVENTA: se mantiene igual (solo se cambia el botón de cierre) -->
  <div id="modal_finventa" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header modal-phuyu-titulo">
          <h5 class="modal-title"><b style="font-size:25px;">TOTAL VENTA S/. {{ totales.importe }}</b></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="cuerpotermino">
          <form v-on:submit.prevent="phuyu_pagar()" class="formulario">
            <div class="row form-group">
              <div class="col-md-12 col-xs-12">
                <label>SELECCIONAR VENDEDOR</label>
                <select class="form-select" name="codempleado" <?php echo $disabled; ?> v-model="campos.codempleado" required>
                  <option value="0">SIN VENDEDOR</option>
                  <?php foreach ($vendedores as $value) { ?>
                    <option value="<?php echo $value['codpersona']; ?>"><?php echo $value['razonsocial']; ?></option>
                  <?php } ?>
                </select>
              </div>
            </div>
            <div class="row form-group" v-show="campos.condicionpago==2 && rubro==6">
              <div class="col-xs-7">
                <label>LINEA DE CREDITO DEL CLIENTE</label>
                <select class="form-select" name="codlote" v-model="campos.codlote" id="codlote"></select>
              </div>
              <div class="col-xs-5">
                <label></label>
                <button type="button" class="btn btn-success btn-block" v-on:click="phuyu_lineascreditodirecto()"><i class="fa fa-plus"></i> NUEVA LINEA DE CREDITO</button>
              </div>
            </div>
            <div class="row form-group" v-if="campos.condicionpago==2">
              <div class="col-md-5 col-xs-12">
                <label>NRO DIAS</label>
                <input class="form-control" name="nrodias" v-model="campos.nrodias" v-on:keyup="phuyu_cuotas()" required>
              </div>
              <div class="col-md-3 col-xs-12">
                <label>CUOTAS</label>
                <input class="form-control" name="nrocuotas" v-model="campos.nrocuotas" v-on:keyup="phuyu_cuotas()" required>
              </div>
              <div class="col-md-4 col-xs-12">
                <label>TASA INTERES (%)</label>
                <input class="form-control" name="tasainteres" v-model="campos.tasainteres" v-on:keyup="calcular_credito()" required>
              </div>
            </div>
            <div class="row" v-if="campos.condicionpago==2">
              <div class="col-6">
                <button type="button" class="btn" :class="inicialActivado ? 'btn-danger' : 'btn-success'" @click="toggleInicial">
                  {{ inicialActivado ? 'Quitar inicial' : 'Dar inicial' }}
                </button>
              </div>
              <div class="col-6" v-if="inicialActivado">
                <label>Inicial</label>
                <input type="number" step="0.01" class="form-control number" min="0" v-model.number="campos.inicial" @input="calcular_credito()" placeholder="S/. 0.00">
              </div>
            </div>

            <div v-if="campos.condicionpago==1">
              <h5 align="center"><b><i class="fa fa-money"></i> REGISTRAR PAGO DE LA VENTA</b></h5>
              <div class="phuyu-linea"></div>
              <div class="row form-group">
                <div class="col-md-4 col-xs-12" align="center">
                  <label><i class="fa fa-money" style="font-size:35px;"></i> <br>PAGO CON EFECTIVO</label>
                </div>
                <div class="col-md-4 col-xs-12">
                  <label>S/. MONTO RECIBIDO</label>
                  <input type="number" step="0.01" class="form-control number phuyu-money-success" min="0" required v-model="pagos.monto_efectivo" placeholder="S/. 0.00" v-on:keyup="phuyu_vuelto(0)">
                </div>
                <div class="col-md-4 col-xs-12">
                  <label>VUELTO</label>
                  <input type="number" step="0.01" class="form-control phuyu-money-error" readonly v-model="pagos.vuelto_efectivo">
                </div>
              </div>

              <div class="phuyu-linea"></div>
              <div class="row form-group">
                <div class="col-md-4 col-xs-12">
                  <label><i class="fa fa-money"></i> TARJETA O CHEQUE</label>
                  <select class="form-select" v-model="pagos.codtipopago_tarjeta" v-on:change="phuyu_pagotarjeta()" required>
                    <option value="0">SIN TARJETA</option>
                    <?php foreach ($tipopagos as $value) { if ($value["codtipopago"] != 1) { ?>
                      <option value="<?php echo $value['codtipopago']; ?>"><?php echo $value['descripcion']; ?></option>
                    <?php } } ?>
                  </select>
                </div>
                <div class="col-md-4 col-xs-12">
                  <label>S/. MONTO</label>
                  <input type="number" step="0.01" class="form-control number phuyu-money-success" min="0.01" id="monto_tarjeta" v-model="pagos.monto_tarjeta" v-on:keyup="phuyu_vuelto(1)" placeholder="S/. 0.00" readonly>
                </div>
                <div class="col-md-4 col-xs-12">
                  <label>NRO VOUCHER</label>
                  <input type="text" class="form-control phuyu-money-default" id="nrovoucher" v-model.trim="pagos.nrovoucher" autocomplete="off" readonly>
                </div>
              </div>
            </div>

            <div v-if="campos.condicionpago==2">
              <div class="data-table-responsive-wrapper">
                <table class="table table-striped" style="font-size: 11px">
                  <thead>
                    <tr>
                      <th>FECHA VENCE</th>
                      <th>N° LETRA</th>
                      <th>COD. UNICO</th>
                      <th>IMPORTE</th>
                      <th>INTERES</th>
                      <th>TOTAL</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(dato,index) in cuotas" :key="index">
                      <td><input type="date" class="form-control" v-model="dato.fechavence" @change="recalcular_fechas_desde(index)"></td>
                      <td><input type="text" class="form-control" v-model="dato.nroletra" maxlength="10"></td>
                      <td><input type="text" class="form-control" v-model="dato.nrounicodepago"></td>
                      <td><input type="number" class="form-control" v-model="dato.importe" step="0.001" v-on:keyup="calcular_credito()"></td>
                      <td><input type="number" disabled="disabled" class="form-control" v-model="dato.interes"></td>
                      <td>{{ dato.total }}</td>
                    </tr>
                    <tr>
                      <td colspan="5" align="right">TOTAL</td>
                      <td id="totalimportecredito">{{ importetotalcredito }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div style="border-bottom:2px solid #13a89e;padding-bottom:10px;" align="center">
                <button type="button" class="btn btn-warning btn-sm"><b>INTERES: S/. {{ totales.interes }}</b></button>
                <button type="button" class="btn btn-danger btn-sm"><b>TOTAL CREDITO: S/. {{ campos.totalcredito }}</b></button>
              </div>
            </div>

            <br>
            <div class="row mb-2" align="center">
              <div class="col-md-4 terminarpedido">
                <label style="font-size:12px;">TERMINAR PEDIDO</label><br>
                <input type="checkbox" style="height:20px;width:20px;" v-model="campos.terminarpedido">
              </div>
              <div class="col-md-4">
                <label style="font-size:12px;">DESPACHO DIRECTO</label><br>
                <input type="checkbox" style="height:20px;width:20px;" id="retirar" v-bind:checked="coddespachotipo==1">
              </div>
              <div class="col-md-4">
                <label style="font-size:12px;">LEYENDA AMZ</label><br>
                <input type="checkbox" style="height:20px;width:20px;" id="conleyendaamazonia" checked>
              </div>
            </div>
            <hr>
            <div class="row form-group" align="center"><br>
              <div class="col-md-12">
                <button type="submit" class="btn btn-success btn-lg" v-bind:disabled="estado==1"><b>GUARDAR VENTA</b></button>
                <button type="button" class="btn btn-danger btn-lg" data-bs-dismiss="modal"><b>CANCELAR</b></button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div id="modal_reportes" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" style="width:100%;margin:0px;">
      <div class="modal-content" align="center" style="border-radius:0px">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" style="font-size:30px;margin-bottom:0px;">
            <i class="bi bi-x-circle-fill"></i>
          </button>
          <h4 class="modal-title">
            <b style="letter-spacing:4px;"><?php echo $_SESSION['phuyu_empresa'] . ' - ' . $_SESSION['phuyu_sucursal']; ?></b>
          </h4>
        </div>
        <div class="modal-body" style="height:450px;padding:0px;">
          <iframe src="" style="width:100%; height:100%; border:none;"></iframe>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Scripts: se mantienen exactamente igual -->
<script src="<?php echo base_url(); ?>phuyu/phuyu_ventas/nuevo.js"></script>
<script src="<?php echo base_url(); ?>phuyu/phuyu_personas_2.js"></script>
<script>
  var pantalla = jQuery(document).height();
  $("#reportes_modal").css({ height: pantalla - 65 });

  // Inicializaciones de la plantilla (se mantienen)
//   if (typeof AcornIcons !== 'undefined') {
//     new AcornIcons().replace();
//   }
//   if (typeof Icons !== 'undefined') {
//     const icons = new Icons();
//   }
  if (typeof Select2Controls !== 'undefined') {
    let select2Controls = new Select2Controls();
  }
</script>
