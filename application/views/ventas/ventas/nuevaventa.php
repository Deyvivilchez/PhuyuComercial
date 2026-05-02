<style type="text/css">
  /* Estilos adicionales para mejorar la vista */
  .card-modern {
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
    border-radius: 1rem;
    overflow: hidden;
  }
  .card-header-modern {
    background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
    border-bottom: 1px solid #e9ecef;
    padding: 1rem 1.25rem;
  }
  .form-control-modern, .form-select-modern {
    border-radius: 0.5rem;
    border: 1px solid #e2e8f0;
    transition: all 0.2s;
  }
  .form-control-modern:focus, .form-select-modern:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
  }
  .btn-modern {
    border-radius: 0.5rem;
    padding: 0.4rem 0.9rem;
    font-weight: 500;
  }
  .table-items th {
    background-color: #f8fafc;
    font-weight: 700;
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom-width: 1px;
  }
  .table-items td {
    vertical-align: middle;
  }
  .total-group {
    background-color: #f8fafc;
    border-radius: 0.75rem;
    padding: 0.5rem;
  }
  .total-label {
    font-weight: 700;
    font-size: 0.7rem;
    text-transform: uppercase;
    color: #4b5563;
  }
  .total-value {
    font-weight: 800;
    font-size: 0.9rem;
    color: #1e293b;
  }
  .total-grande {
    font-size: 1.1rem;
    color: #dc2626;
  }
  .phuyu-input {
    border-radius: 0.5rem;
    border: 1px solid #e2e8f0;
    padding: 0.3rem 0.5rem;
    width: 100%;
  }
  .scroll-phuyu-view {
    max-height: 400px;
    overflow-y: auto;
  }
  @media (max-width: 768px) {
    .btn-modern {
      padding: 0.3rem 0.6rem;
      font-size: 0.8rem;
    }
  }
</style>

<div id="phuyu_operacion">
<?php 
if ($_SESSION['phuyu_codcontroldiario'] > 0) { ?>
  <form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
    <!-- Campos ocultos (se mantienen igual) -->
    <input type="hidden" id="comprobante" value="<?php echo $sucursal[0]['codcomprobantetipo'];?>">
    <input type="hidden" id="codempleado" value="<?php echo $_SESSION["phuyu_codempleado"];?>">
    <input type="hidden" id="serie" value="<?php echo $sucursal[0]['seriecomprobante'];?>">
    <input type="hidden" id="stockalmacen" value="<?php echo $_SESSION["phuyu_stockalmacen"];?>">
    <input type="hidden" id="coddespachotipo" value="<?php echo $_SESSION["phuyu_tipodespacho"];?>">
    <input type="hidden" id="itemrepetir" value="<?php echo $_SESSION["phuyu_itemrepetir"];?>">
    <input type="hidden" id="igvsunat" value="<?php echo $_SESSION["phuyu_igv"];?>">
    <input type="hidden" id="icbpersunat" value="<?php echo $_SESSION["phuyu_icbper"];?>">
    <input type="hidden" id="formato" value="<?php echo $_SESSION['phuyu_formato'];?>">
    <input type="hidden" id="codpedido" name="codpedido" value="0">
    <input type="hidden" id="codproforma" name="codproforma" value="0">
    <input type="hidden" id="rubro" value="<?php echo $_SESSION["phuyu_rubro"];?>" name="">
    <input type="hidden" id="afectacionigv" value="<?php echo $_SESSION["phuyu_afectacionigv"];?>" name="">
    <input type="hidden" id="ventaconpedido" value="<?php echo $_SESSION["phuyu_ventaconpedido"];?>" name="">
    <input type="hidden" id="ventaconproforma" value="<?php echo $_SESSION["phuyu_ventaconproforma"];?>" name="">

    <div class="phuyu_body">
      <div class="card card-modern">
        <div class="card-header card-header-modern d-flex flex-wrap justify-content-between align-items-center">
          <div>
            <h6 class="mb-0 fw-semibold"><i class="fa fa-shopping-cart me-2"></i> REGISTRO NUEVA VENTA</h6>
          </div>
          <div class="mt-2 mt-sm-0">
            <button type="button" class="btn btn-success btn-sm btn-modern me-1" v-on:click="phuyu_searchpedido()">
              <i class="fa fa-search me-1"></i> BUSCAR PEDIDO
            </button>
            <button type="button" class="btn btn-primary btn-sm btn-modern" v-on:click="phuyu_searchproforma()">
              <i class="fa fa-search me-1"></i> BUSCAR PROFORMA
            </button>
          </div>
        </div>

        <?php 
        $disabled = '';
        if($_SESSION["phuyu_codperfil"]>3){
          $disabled = 'disabled';
        }
        ?>
        <div class="card-body">
          <!-- Fila 1: Tipo comprobante, serie, fechas -->
          <div class="row g-3 mb-3">
            <div class="col-md-2 col-6">
              <label class="form-label small fw-semibold">TIPO COMPROBANTE</label>
              <select class="form-select form-select-modern" name="codcomprobantetipo" v-model="campos.codcomprobantetipo" required v-on:change="phuyu_series()">
                <?php foreach ($comprobantes as $key => $value) { ?>
                  <option value="<?php echo $value["codcomprobantetipo"];?>"><?php echo $value["descripcion"];?></option>
                <?php } ?>
              </select>
            </div>
            <div class="col-md-1 col-6">
              <label class="form-label small fw-semibold">SERIE</label>
              <select class="form-select form-select-modern" id="seriecomprobante" v-model="campos.seriecomprobante" v-on:change="phuyu_correlativo()" required>
                <option value="">SERIE</option>
                <option v-for="dato in series" v-bind:value="dato.seriecomprobante">{{dato.seriecomprobante}}</option>
              </select>
            </div>
            <div class="col-md-2 col-6">
              <label class="form-label small fw-semibold">FECHA VENTA</label>
              <input type="date" class="form-control form-control-modern" name="fechacomprobante" id="fechacomprobante" value="<?php echo date('Y-m-d');?>" autocomplete="off" required>
            </div>
            <div class="col-md-2 col-6">
              <label class="form-label small fw-semibold">FECHA KARDEX</label>
              <input type="date" class="form-control form-control-modern" name="fechakardex" id="fechakardex" value="<?php echo date('Y-m-d');?>" autocomplete="off" required>
            </div>
            <?php if ($_SESSION["phuyu_rubro"]==1) { ?>
              <div class="col-md-3 col-12">
                <label class="form-label small fw-semibold">NRO DE PLACA</label>
                <input type="text" class="form-control form-control-modern" v-model="campos.nroplaca" autocomplete="off" maxlength="100" placeholder="Nro placa . . .">
              </div>
            <?php } else { ?>
              <div class="col-md-3 col-12" style="display: none">
                <label class="form-label small fw-semibold">DESCRIPCION DEL MOVIMIENTO</label>
                <input type="text" class="form-control form-control-modern" v-model="campos.descripcion" autocomplete="off" maxlength="250" placeholder="Referencia de la venta . . .">
              </div>
              <div class="col-md-2 col-6">
                <label class="form-label small fw-semibold">CONDICION PAGO</label>
                <select class="form-select form-select-modern" name="condicionpago" v-model="campos.condicionpago" v-on:change="phuyu_condicionpago()">
                  <option value="1">CONTADO</option>
                  <option value="2">CREDITO</option>
                </select>
              </div>
            <?php } ?>
            <div class="col-md-2 col-6">
              <label class="form-label small fw-semibold">MONEDA</label>
              <select class="form-select form-select-modern" name="codmoneda" v-model="campos.codmoneda" v-on:change="phuyu_tipocambio()" required>
                <?php foreach ($monedas as $key => $value) { ?>
                  <option value="<?php echo $value["codmoneda"];?>"><?php echo $value["simbolo"]." ".$value["descripcion"];?></option>
                <?php } ?>
              </select>
            </div>
            <div class="col-md-1 col-6">
              <label class="form-label small fw-semibold">CAMBIO</label>
              <input type="number" step="0.001" class="form-control form-control-modern number" name="tipocambio" v-model.number="campos.tipocambio" autocomplete="off" min="1" v-bind:disabled="campos.codmoneda==1" required>
            </div>
          </div>

          <!-- Fila 2: Cliente -->
          <div class="row g-3 mb-3 align-items-end">
            <div class="col-md-3 col-9">
              <label class="form-label small fw-semibold">SELECCIONAR CLIENTE</label>
              <select class="form-select form-select-modern selectpicker ajax" name="codpersona" v-model="campos.codpersona" id="codpersona" required data-live-search="true" v-on:change="phuyu_infocliente()">
                <option value="2">CLIENTES VARIOS</option>
              </select>
            </div>
            <div class="col-md-1 col-3">
              <label class="form-label invisible d-none d-md-block">&nbsp;</label>
              <button type="button" class="btn btn-success btn-modern w-100" v-on:click="phuyu_addcliente()" title="AGREGAR CLIENTE">
                <i class="fa fa-user-plus"></i>
              </button>
            </div>
            <div class="col-md-2 col-6">
              <label class="form-label small fw-semibold">DOCUMENTO CLIENTE</label>
              <input type="text" class="form-control form-control-modern" name="nrodocumento" id="nrodocumento" v-model.trim="campos.nrodocumento" autocomplete="off" readonly>
            </div>
            <div class="col-md-3 col-6">
              <label class="form-label small fw-semibold">CLIENTE DE LA VENTA</label>
              <input type="text" class="form-control form-control-modern" id="cliente" v-model.trim="campos.cliente" autocomplete="off" maxlength="250" placeholder="Razon social del cliente . . ." required>
            </div>
            <div class="col-md-3 col-12">
              <label class="form-label small fw-semibold">DIRECCION CLIENTE</label>
              <input type="text" class="form-control form-control-modern" id="direccion" v-model.trim="campos.direccion" autocomplete="off" maxlength="250" placeholder="Direccion del cliente . . ." required>
            </div>
          </div>

          <!-- Tabla de productos -->
          <div class="row mb-3">
            <div class="col-12">
              <div class="table-responsive scroll-phuyu-view">
                <table class="table table-bordered table-striped table-items">
                  <thead>
                    <tr>
                      <th width="7%"><button type="button" class="btn btn-success btn-sm btn-modern" v-on:click="phuyu_item()"><i class="fa fa-plus-square"></i> ITEM</button></th>
                      <th width="30%">PRODUCTO</th>
                      <th width="10%">UNIDAD</th>
                      <th width="7%">STOCK</th>
                      <th width="10%">CANTIDAD</th>
                      <th width="10%">PRECIO UNIT.</th>
                      <th width="10%">I.G.V.</th>
                      <th>ICBPER</th>
                      <th width="10%">SUBTOTAL</th>
                      <th width="1%"><i class="fa fa-trash-o"></i></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(dato,index) in detalle">
                      <td class="phuyu-item-mas text-center" v-on:click="phuyu_itemdetalle(index,dato)" style="cursor: pointer;">
                        <i class="fa fa-plus-circle text-success fa-lg"></i> MAS
                      </td>
                      <td style="font-size:11px;">{{dato.producto}}</td>
                      <td>
                        <select class="phuyu-input" v-model="dato.codunidad" v-on:change="informacion_unidad(index,dato,this.value)" id="codunidad">
                          <template v-for="(unidad, und) in dato.unidades">
                            <option v-bind:value="unidad.codunidad" v-if="unidad.factor==1" selected>{{unidad.descripcion}}</option>
                            <option v-bind:value="unidad.codunidad" v-if="unidad.factor!=1">{{unidad.descripcion}}</option>
                          </template>
                        </select>
                      </td>
                      <td class="text-danger fw-bold">{{dato.stock}}</td>
                      <td><input type="number" step="0.0001" class="phuyu-input number" v-model.number="dato.cantidad" v-on:keyup="phuyu_calcular(dato,3)" required></td>
                      <td>
                        <input type="number" step="0.0001" class="phuyu-input number" v-if="dato.codafectacionigv==21" v-model.number="dato.precio" min="0" readonly>
                        <input type="number" step="0.0001" class="phuyu-input number" v-if="dato.codafectacionigv!=21" v-model.number="dato.precio" v-on:keyup="phuyu_calcular(dato)" min="0.001" required v-bind:disabled="dato.porcdescuento==100">
                      </td>
                      <td><input type="number" class="phuyu-input number" v-model.number="dato.igv" min="0" readonly></td>
                      <td><input type="number" class="phuyu-input number" v-model.number="dato.icbper" min="0" readonly></td>
                      <td v-if="dato.codafectacionigv==21">
                        <input type="number" step="0.01" class="phuyu-input number" v-model.number="dato.subtotal">
                      </td>
                      <td v-if="dato.codafectacionigv!=21">
                        <input type="number" step="0.01" class="phuyu-input number" v-if="dato.calcular==0" v-model.number="dato.subtotal" readonly>
                        <input type="number" step="0.01" class="phuyu-input number" v-if="dato.calcular!=0" v-model.number="dato.subtotal" v-on:keyup="phuyu_subtotal(dato)" required v-bind:disabled="dato.porcdescuento==100">
                      </td>
                      <td class="text-center">
                        <button type="button" class="btn btn-danger btn-xs" style="padding: 0.2rem 0.5rem;" v-on:click="phuyu_deleteitem(index,dato)">
                          <i class="fa fa-trash-o"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Totales en grid responsivo -->
          <div class="total-group p-3 mb-3">
            <div class="row g-2 text-center text-md-start">
              <div class="col-6 col-md-2">
                <div class="total-label">IMPORTE S/.</div>
                <div class="total-value">{{totales.bruto}}</div>
              </div>
              <div class="col-6 col-md-2">
                <div class="total-label">DESC: S/.</div>
                <div class="total-value">{{totales.descuentos}}</div>
              </div>
              <div class="col-6 col-md-2">
                <div class="total-label">GRAV.: S/.</div>
                <div class="total-value">{{operaciones.gravadas}}</div>
              </div>
              <div class="col-6 col-md-2">
                <div class="total-label">EXON.: S/.</div>
                <div class="total-value">{{operaciones.exoneradas}}</div>
              </div>
              <div class="col-6 col-md-2">
                <div class="total-label">INAF.: S/.</div>
                <div class="total-value">{{operaciones.inafectas}}</div>
              </div>
              <div class="col-6 col-md-2">
                <div class="total-label">GRAT.: S/.</div>
                <div class="total-value">{{operaciones.gratuitas}}</div>
              </div>
              <div class="col-6 col-md-2">
                <div class="total-label">IGV: S/.</div>
                <div class="total-value">{{totales.igv}}</div>
              </div>
              <div class="col-6 col-md-2">
                <div class="total-label">ISC: S/.</div>
                <div class="total-value">{{totales.isc}}</div>
              </div>
              <div class="col-6 col-md-2">
                <div class="total-label">ICBPER: S/.</div>
                <div class="total-value">{{totales.icbper}}</div>
              </div>
              <div class="col-6 col-md-2">
                <div class="total-label">TOTAL S/.</div>
                <div class="total-value total-grande">{{totales.importe}}</div>
              </div>
            </div>
          </div>

          <!-- Botones de acción -->
          <div class="row">
            <div class="col-12 text-center">
              <button type="button" class="btn btn-warning btn-modern me-2" v-on:click="phuyu_venta()">
                <i class="fa fa-plus-square"></i> NUEVA VENTA
              </button>
              <button type="submit" class="btn btn-info btn-modern" v-bind:disabled="estado==1">
                <i class="fa fa-arrow-right"></i> CONTINUAR VENTA
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </form>

<?php } else { ?>
  <div class="phuyu_body">
    <div class="card card-modern text-center">
      <div class="card-body py-5">
        <i class="fa fa-money text-success" style="font-size: 80px;"></i>
        <h4 class="mt-3">CAJA CERRADA! PARA PODER REALIZAR UNA VENTA, LA CAJA DEBE ESTAR APERTURADA</h4>
        <button type="button" class="btn btn-success btn-modern mt-3" v-on:click="aperturar_caja()">IR A APERTURAR CAJA</button>
      </div>
    </div>
  </div>
<?php } ?>

<!-- MODALES (se mantienen exactamente igual para no romper funcionalidad) -->
<div id="modal_masconfiguraciones" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" align="center">
      <div class="modal-header"> 
        <button type="button" class="close" data-dismiss="modal" style="font-size:30px;margin-bottom:0px;">
          <i class="fa fa-times-circle"></i> 
        </button>
        <h4 class="modal-title"> <b style="letter-spacing:1px;">DETALLE DEL ITEM DE LA VENTA</b> </h4> 
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
              <?php foreach ($centrocostos as $key => $value) { ?>
                <option value="<?php echo $value["codcentrocosto"];?>"><?php echo $value["descripcion"];?></option>
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

<div id="modal_itemdetalle" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header modal-phuyu-titulo">
        <h4 class="modal-title"> <b style="letter-spacing:0.5px;">DETALLE DEL ITEM DE LA VENTA</b> </h4> 
      </div>
      <div class="modal-body">
        <h5> <b>PRODUCTO: {{item.producto}} &nbsp; <span class="label label-warning">CANTIDAD: {{item.cantidad}} {{item.unidad}}</span></b> </h5> <hr>
        <div class="row form-group">
          <div class="col-md-4 col-xs-12">
            <label>PRECIO BRUTO</label>
            <input type="number" class="phuyu-input number" v-model.number="item.preciobruto" v-on:keyup="phuyu_itemcalcular(item,0)" v-bind:disabled="item.codafectacionigv==21">
          </div>
          <div class="col-md-4 col-xs-12">
            <label>DESCUENTO PRECIO (S/.)</label>
            <input type="number" class="phuyu-input number" v-model.number="item.descuento" v-on:keyup="phuyu_itemcalcular(item,-1)" v-bind:disabled="item.codafectacionigv==21">
          </div>
          <div class="col-md-4 col-xs-12">
            <label>DESCUENTO PRECIO (%)</label>
            <input type="number" class="phuyu-input number" v-model.number="item.porcdescuento" v-on:keyup="phuyu_itemcalcular(item,-2)" v-bind:disabled="item.codafectacionigv==21">
          </div>
        </div>
        <div class="row form-group">
          <div class="col-md-4 col-xs-12">
            <label>PRECIO SIN I.G.V.</label>
            <input type="number" class="phuyu-input number" v-model.number="item.preciosinigv" v-on:keyup="phuyu_itemcalcular(item,1)" v-bind:disabled="item.codafectacionigv==21">
          </div>
          <div class="col-md-3 col-xs-12">
            <label>PRECIO UNITARIO</label>
            <input type="number" class="phuyu-input number" v-model.number="item.precio" v-on:keyup="phuyu_itemcalcular(item,2)" v-bind:disabled="item.codafectacionigv==21">
          </div>
          <div class="col-md-3 col-xs-12">
            <label>TIPO AFECTACION</label>
            <select class="phuyu-input" v-model="item.codafectacionigv" v-on:change="phuyu_itemcalcular(item,2)">
              <?php foreach ($afectacionigv as $key => $value) { ?>
                <option value="<?php echo $value["oficial"];?>"><?php echo $value["descripcion"];?></option>
              <?php } ?>
            </select>
          </div>
          <div class="col-md-2 col-xs-12">
            <label>ICBPER</label>
            <select class="phuyu-input" v-model="item.conicbper" v-on:change="phuyu_itemcalcular(item,2)">
              <option value="1">SI</option>
              <option value="0">NO</option>
            </select>
          </div>
        </div>
        <div class="form-group text-center">
          <button type="button" class="btn btn-info btn-sm"> <b>VALOR VENTA: S/. {{item.valorventa}}</b> </button>
          <button type="button" class="btn btn-danger btn-sm"> <b>IGV: S/. {{item.igv}}</b></button>
          <button type="button" class="btn btn-danger btn-sm"> <b>ICBPER: S/. {{item.icbper}}</b> </button>
          <button type="button" class="btn btn-success btn-sm"> <b>SUBTOTAL: S/. {{item.subtotal}}</b> </button>
        </div>
        <div class="form-group">
          <label>DESCRIPCION DEL ITEM DE VENTA</label>
          <textarea class="form-control" v-model="item.descripcion" rows="3" maxlength="250"></textarea>
        </div>
        <div class="text-center">
          <button type="button" class="btn btn-success" v-on:click="phuyu_itemcalcular_cerrar(item)">
            <i class="fa fa-save"></i> GUARDAR CAMBIOS DEL ITEM Y <i class="fa fa-times-circle"></i> CERRAR
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
        <button type="button" class="close" data-dismiss="modal"> <i class="fa fa-times-circle"></i> </button>
        <h4 class="modal-title"></h4> 
      </div>
      <div class="modal-body"></div>
    </div>
  </div>
</div>

<div id="modal_finventa" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" style="margin-top:-3rem">
      <div class="modal-header">
        <h5 class="modal-title"><b style="font-size:25px;">TOTAL VENTA S/. {{totales.importe}}</b></h5>
      </div>
      <div class="modal-body" id="cuerpotermino">
        <div class="cargando" style="display: none"><center> <img src="<?php echo base_url(); ?>public/img/phuyu_loading.gif" style="padding-top:100px;"> </center></div>
        <form v-on:submit.prevent="phuyu_pagar()" class="formulario">
          <div class="row form-group">
            <div class="col-md-6 col-xs-12">
              <label>SELECCIONAR VENDEDOR</label>
              <select class="form-control" name="codempleado" <?php echo $disabled;?> v-model="campos.codempleado" required>
                <option value="0">SIN VENDEDOR</option>
                <?php foreach ($vendedores as $key => $value) { ?>
                  <option value="<?php echo $value["codpersona"];?>"><?php echo $value["razonsocial"];?></option>
                <?php } ?>
              </select>
            </div>
            <div class="col-md-6 col-xs-12">
              <label>CONDICION PAGO</label>
              <select class="form-control" name="condicionpago" v-model="campos.condicionpago" v-on:change="phuyu_condicionpago()">
                <option value="1">CONTADO</option>
                <option value="2">CREDITO</option>
              </select>
            </div>
          </div>
          <div class="row form-group" v-if="campos.condicionpago==2 && rubro==6">
            <div class="col-xs-7">
              <label>LINEA DE CREDITO DEL CLIENTE</label>
              <select class="form-control" name="codlote" v-model="campos.codlote" id="codlote"></select>
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
          <div v-if="campos.condicionpago==1">
            <h5 align="center"><b><i class="fa fa-money"></i> REGISTRAR PAGO DE LA VENTA</b></h5>
            <div class="phuyu-linea"></div>
            <div class="row form-group">
              <div class="col-md-4 col-xs-12" align="center">
                <label><i class="fa fa-money" style="font-size:35px;"></i> <br>PAGO CON EFECTIVO</label>
              </div>
              <div class="col-md-4 col-xs-12">
                <label>S/. MONTO RECIBIDO</label>
                <input type="number" step="0.01" class="form-control number phuyu-money-success" min="0" required v-model="pagos.monto_efectivo" placeholder="S/. 0.00" v-on:keyup="phuyu_vuelto()">
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
                <select class="form-control" v-model="pagos.codtipopago_tarjeta" v-on:change="phuyu_pagotarjeta()" required>
                  <option value="0">SIN TARJETA</option>
                  <?php foreach ($tipopagos as $key => $value) { 
                    if ($value["codtipopago"]!=1) { ?>
                      <option value="<?php echo $value["codtipopago"];?>"><?php echo $value["descripcion"];?></option>
                    <?php } 
                  } ?>
                </select>
              </div>
              <div class="col-md-4 col-xs-12">
                <label>S/. MONTO</label>
                <input type="number" step="0.01" class="form-control number phuyu-money-success" min="0.01" id="monto_tarjeta" v-model="pagos.monto_tarjeta" placeholder="S/. 0.00" readonly>
              </div>
              <div class="col-md-4 col-xs-12">
                <label>NRO VOUCHER</label>
                <input type="text" class="form-control phuyu-money-default" id="nrovoucher" v-model.trim="pagos.nrovoucher" autocomplete="off" readonly>
              </div>
            </div>
          </div>
          <div v-if="campos.condicionpago==2">
            <div class="table-responsive">
              <table class="table table-bordered">
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
                  <tr v-for="dato in cuotas">
                    <td><input type="date" class="form-control" v-model="dato.fechavence" name=""></td>
                    <td><input type="text" class="form-control" v-model="dato.nroletra" name="" maxlength="10"></td>
                    <td><input type="text" class="form-control" v-model="dato.nrounicodepago" name=""></td>
                    <td><input type="number" class="form-control" v-model="dato.importe" step="0.01" v-on:keyup="calcular_credito()"></td>
                    <td><input type="number" disabled="disabled" class="form-control" v-model="dato.interes"></td>
                    <td>{{dato.total}}</td>
                  </tr>
                  <tr>
                    <td colspan="5" align="right">TOTAL</td>
                    <td id="totalimportecredito">{{importetotalcredito}}</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div style="border-bottom:2px solid #13a89e;padding-bottom:10px;" align="center">
              <button type="button" class="btn btn-warning btn-sm"><b>INTERES: S/. {{totales.interes}}</b></button>
              <button type="button" class="btn btn-danger btn-sm"><b>TOTAL CREDITO: S/. {{campos.totalcredito}}</b></button>
            </div>
          </div><br>
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
          <div class="row form-group" align="center">
            <div class="col-md-12">
              <button type="submit" class="btn btn-success btn-lg" v-bind:disabled="estado==1"><b>GUARDAR VENTA</b></button>
              <button type="button" class="btn btn-danger btn-lg" data-dismiss="modal"><b>CANCELAR</b></button>
            </div>
          </div>
        </form>
        <div class="div_ventarealizada" style="display: none">
          <div class="row mb-2" style="border-bottom:2px solid #13a89e;padding-bottom:10px;">
            <div class="col-md-12">
              <div class="alert alert-success" style="padding: 8px !important;margin-bottom: 10px !important" role="alert">
                Venta Registrada Correctamente! Se Agregó un Nuevo Comprobante al Sistema
              </div>
            </div>
          </div>
          <div class="row mb-2">
            <div class="col-md-12" align="right">
              <button class="btn btn-default btn-sm botones a5" v-on:click="phuyu_imprimir2('a5')">A5</button>
              <button class="btn btn-default btn-sm botones a4" v-on:click="phuyu_imprimir2('a4')">A4</button>
              <button class="btn btn-default btn-sm botones ticket" v-on:click="phuyu_imprimir2('ticket')">TICKET</button>
            </div>
          </div>
          <div class="row mb-2" style="border-bottom:2px solid #13a89e;padding-bottom:10px;">
            <iframe id="phuyu_pdf" src="" style="width:100%; height:300px; border:none;"></iframe>
          </div>
          <div class="row form-group" align="center">
            <button type="button" class="btn btn-default btn-lg" v-on:click="phuyu_lista()">VER LISTA</button>
            <button type="button" class="btn btn-primary btn-lg" v-on:click="phuyu_nueva_venta()">CREAR NUEVO COMPROBANTE</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div id="modal_reportes" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog" style="width:100%;margin:0px;">
    <div class="modal-content" align="center" style="border-radius:0px">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" style="font-size:30px;margin-bottom:0px;">
          <i class="fa fa-times-circle"></i> 
        </button>
        <h4 class="modal-title">
          <b style="letter-spacing:4px;"><?php echo $_SESSION["phuyu_empresa"]." - ".$_SESSION["phuyu_sucursal"];?> </b>
        </h4>
      </div>
      <div class="modal-body" style="height:450px;padding:0px;">
        <iframe src="" style="width:100%; height:100%; border:none;"></iframe>
      </div>
    </div>
  </div>
</div>
</div>

<script src="<?php echo base_url();?>phuyu/phuyu_ventas/nuevaventa.js"></script>
<script src="<?php echo base_url();?>phuyu/phuyu_personas_2.js"></script>
<script>
  var pantalla = jQuery(document).height(); $("#reportes_modal").css({height: pantalla - 65});
  $(".datepicker").datetimepicker({format: 'YYYY-MM-DD', ignoreReadonly: true}).attr("readonly","true");
</script>