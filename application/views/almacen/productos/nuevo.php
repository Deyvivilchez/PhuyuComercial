<div id="phuyu_formulario" class="producto-modal-shell">
  <div id="phuyu_form" class="producto-modal-card">
    <!-- TÍTULO DINÁMICO -->
    <div class="row mb-3">
      <div class="col-12">
        <h4 class="fw-bold text-primary mb-2">
          <i class="bi bi-box-seam me-2"></i>
          <span v-if="campos.codregistro == '' || campos.codregistro == 0">REGISTRO DE PRODUCTO</span>
          <span v-else>EDITAR PRODUCTO</span>
        </h4>
        <hr class="my-2">
      </div>
    </div>

    <form id="formulario" class="form-horizontal" v-on:submit.prevent="phuyu_guardar()">
      <input type="hidden" name="codregistro" v-model="campos.codregistro">
      <input type="hidden" id="paraventa" v-model="campos.paraventa">
      <input type="hidden" v-model.trim="campos.codatencion">
      <input type="hidden" id="calcular" v-model="campos.calcular">
      <input type="hidden" id="afectoicbper" v-model="campos.afectoicbper">
      <input type="hidden" id="codafectoigv" value="<?php echo $_SESSION['phuyu_afectacionigv']; ?>">

      <div class="row g-3 mb-3">
        <div class="col-md-2 col-6">
          <label class="form-label">CÓDIGO</label>
          <input type="text" id="codigo" v-model.trim="campos.codigo" class="form-control" autocomplete="off" placeholder="Código ...">
        </div>
        <div class="col-md-2 col-6">
          <label class="form-label">TIPO PRODUCTO</label>
          <select id="tipo" v-model="campos.tipo" class="form-select">
            <option value="1">BIEN</option>
            <option value="2">SERVICIO</option>
          </select>
        </div>
        <div class="col-md-8 col-12">
          <label class="form-label">DESCRIPCIÓN PRODUCTO <span class="text-danger">*</span></label>
          <input type="text" id="descripcion" v-model="campos.descripcion" class="form-control" autocomplete="off" placeholder="Descripción ..." maxlength="100" required>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-4 col-12">
          <label class="form-label">FAMILIA PRODUCTO <span class="text-danger">*</span></label>
          <div class="input-group">
            <select class="form-select" id="codfamilia" v-model="campos.codfamilia" required>
              <option value="">SELECCIONE ...</option>
              <option v-for="dato in familias" :value="dato.codfamilia">{{ dato.descripcion }}</option>
            </select>
            <button type="button" class="btn btn-primary" v-on:click="phuyu_nuevo_extencion('almacen/familias')">
              <i class="bi bi-plus-lg"></i>
            </button>
          </div>
        </div>

        <div class="col-md-4 col-12">
          <label class="form-label">LÍNEA PRODUCTO <span class="text-danger">*</span></label>
          <div class="input-group">
            <select class="form-select" id="codlinea" v-model="campos.codlinea" required>
              <option value="">SELECCIONE ...</option>
              <option v-for="dato in lineas" :value="dato.codlinea">{{ dato.descripcion }}</option>
            </select>
            <button type="button" class="btn btn-primary" v-on:click="phuyu_nuevo_extencion('almacen/lineas')">
              <i class="bi bi-plus-lg"></i>
            </button>
          </div>
        </div>

        <div class="col-md-4 col-12">
          <label class="form-label">MARCA PRODUCTO <span class="text-danger">*</span></label>
          <div class="input-group">
            <select class="form-select" id="codmarca" v-model="campos.codmarca" required>
              <option value="">SELECCIONE ...</option>
              <option v-for="dato in marcas" :value="dato.codmarca">{{ dato.descripcion }}</option>
            </select>
            <button type="button" class="btn btn-primary" v-on:click="phuyu_nuevo_extencion('almacen/marcas')">
              <i class="bi bi-plus-lg"></i>
            </button>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-12">
          <label class="form-label">CARACTERÍSTICAS PRODUCTO</label>
          <input type="text" v-model="campos.caracteristicas" class="form-control" autocomplete="off" placeholder="Características ..." maxlength="255">
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-md-3">
          <label class="form-label">AFECTACIÓN IGV COMPRA</label>
          <select name="codafectacionigvcompra" v-model="campos.codafectacionigvcompra" class="form-select" required>
            <option value="">SELECCIONE</option>
            <?php foreach ($afectacionigv as $value) { ?>
              <option value="<?php echo $value['codafectacionigv']; ?>"><?php echo $value['descripcion']; ?></option>
            <?php } ?>
          </select>
        </div>

        <div class="col-md-3">
          <label class="form-label">AFECTACIÓN IGV VENTA</label>
          <select name="codafectacionigvventa" v-model="campos.codafectacionigvventa" class="form-select" required>
            <option value="">SELECCIONE</option>
            <?php foreach ($afectacionigv as $value) { ?>
              <option value="<?php echo $value['codafectacionigv']; ?>"><?php echo $value['descripcion']; ?></option>
            <?php } ?>
          </select>
        </div>

        <div class="col-md-3">
          <div class="form-check form-switch mb-2 mt-4">
            <input class="form-check-input" type="checkbox" id="afectoicbper_check" :checked="campos.afectoicbper != 0" v-on:click="phuyu_activaricbper()">
            <label class="form-check-label" for="afectoicbper_check">ICBPER</label>
          </div>
          <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" id="stock" :checked="campos.controlstock == 1" v-on:click="phuyu_activarstock()">
            <label class="form-check-label" for="stock">CONTROLA STOCK</label>
          </div>
          <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="controlarseries" v-model="campos.controlarseries" true-value="1" false-value="0">
            <label class="form-check-label" for="controlarseries">CONTROLAR SERIES</label>
          </div>
        </div>

        <div class="col-md-3 col-6">
          <label class="form-label">COMISIÓN (%)</label>
          <input type="hidden" name="codproducto" id="codproducto">
          <input type="number" class="form-control" v-model="campos.comisionvendedor" placeholder="0.00">
        </div>
      </div>

      <hr>

      <div class="row mb-3">
        <div class="col-md-5 col-12">
          <button type="button" class="btn btn-success w-100" v-on:click="phuyu_addunidad()">
            <i class="bi bi-plus-lg me-1"></i> Agregar Unidad de Medida
          </button>
        </div>
      </div>

      <div class="table-responsive producto-table-wrap">
        <table class="table table-bordered align-middle" style="min-width: 1000px;">
          <thead>
            <tr>
              <th>UNIDAD</th>
              <th>FACTOR</th>
              <th>P.COMPRA</th>
              <th>P.VENTA</th>
              <th>P.MÍNIMO</th>
              <th>P.CRÉDITO</th>
              <th>P.MAYOR</th>
              <th>P.OTROS</th>
              <th>C.BARRA</th>
              <th>ELIMINAR</th>
            </tr>
          </thead>
          <tbody style="font-size:13px;">
            <tr v-for="(uni,index) in unidades" :key="index">
              <td>
                <select class="form-select number" v-bind:disabled="editar==1 && uni.factor==1" v-model="uni.codunidad" required>
                  <?php foreach ($unidades as $value) { ?>
                    <option value="<?php echo $value['codunidad']; ?>"><?php echo $value['descripcion']; ?></option>
                  <?php } ?>
                </select>
              </td>
              <td><input type="number" step="0.1" class="form-control number" v-model.number="uni.factor" min="1" required></td>
              <td><input type="number" step="0.0001" class="form-control number" v-model.number="uni.preciocompra" min="0" required></td>
              <td><input type="number" step="0.0001" class="form-control number" v-model.number="uni.pventapublico" min="0.1" required></td>
              <td><input type="number" step="0.0001" class="form-control number" v-model.number="uni.pventamin" min="0" required></td>
              <td><input type="number" step="0.0001" class="form-control number" v-model.number="uni.pventacredito" min="0" required></td>
              <td><input type="number" step="0.0001" class="form-control number" v-model.number="uni.pventaxmayor" min="0" required></td>
              <td><input type="number" step="0.0001" class="form-control number" v-model.number="uni.pventaadicional" min="0" required></td>
              <td><input type="text" class="form-control number" v-model="uni.codigobarra"></td>
              <td>
                <button type="button" class="btn btn-danger btn-sm" v-on:click="phuyu_deleteunidad(index,uni)" v-bind:disabled="editar==1 && uni.factor==1">
                  <i class="bi bi-trash3"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="form-group text-center mt-4">
        <button type="submit" class="btn btn-success me-2" v-bind:disabled="estado==1">
          <i class="bi bi-save me-1"></i> GUARDAR
        </button>
        <button type="button" class="btn btn-danger" v-on:click="phuyu_cerrar()">
          <i class="bi bi-x-circle me-1"></i> CERRAR
        </button>
      </div>
    </form>

    <div id="modal_extencion" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-sm">
        <div class="modal-content" style="border: 2px solid #747474;">
          <div class="modal-body" id="extencion_modal"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div id="productoBackdrop" class="producto-backdrop"></div>

<style>
  #productoBackdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.72);
    backdrop-filter: blur(2px);
    -webkit-backdrop-filter: blur(2px);
    z-index: 3000;
    display: none;
  }

  body.modal-producto-open #productoBackdrop {
    display: block !important;
  }

  #phuyu_formulario.producto-modal-shell {
    position: fixed !important;
    inset: 0 !important;
    z-index: 3001 !important;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 24px;
    overflow-y: auto;
  }

  body.modal-producto-open #phuyu_formulario.producto-modal-shell {
    display: flex !important;
  }

  #phuyu_formulario .producto-modal-card {
    width: min(1200px, 92vw);
    max-height: calc(100vh - 48px);
    overflow-y: auto;
    background: #fff;
    border-radius: 22px;
    padding: 24px 24px 20px;
    box-shadow: 0 24px 70px rgba(0, 0, 0, 0.25);
    position: relative;
  }

  .producto-table-wrap {
    overflow-x: auto;
  }

  @media (max-width: 767.98px) {
    #phuyu_formulario.producto-modal-shell {
      padding: 12px;
    }

    #phuyu_formulario .producto-modal-card {
      width: 100%;
      max-height: calc(100vh - 24px);
      padding: 18px 16px;
      border-radius: 18px;
    }
  }
</style>

<script>
(function () {
  const modal = document.getElementById('phuyu_formulario');

  function openProductoModal() {
    document.body.classList.add('modal-producto-open');
  }

  function closeProductoModal() {
    document.body.classList.remove('modal-producto-open');
  }

  document.addEventListener('click', function (e) {
    const openBtn = e.target.closest('[v-on\\:click="phuyu_nuevoproducto"]');
    const closeBtn = e.target.closest('[v-on\\:click="phuyu_cerrar()"], [v-on\\:click="phuyu_cerrar"], [data-bs-dismiss="modal"]');

    if (openBtn) {
      setTimeout(openProductoModal, 80);
    }

    if (closeBtn) {
      setTimeout(closeProductoModal, 80);
    }
  });

  const observer = new MutationObserver(function () {
    if (!modal) return;
    const visible = modal.offsetWidth > 0 && modal.offsetHeight > 0;
    if (visible) {
      document.body.classList.add('modal-producto-open');
    }
  });

  if (modal) {
    observer.observe(modal, { attributes: true, attributeFilter: ['style', 'class'] });
  }

  window.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      closeProductoModal();
    }
  });
})();
</script>

<script>
  var campos = {
    codregistro: "",
    descripcion: "",
    codfamilia: 0,
    codlinea: 0,
    codmarca: 0,
    codigo: "",
    codigobarra: "",
    calcular: "0",
    controlstock: 1,
    tipo: "1",
    codafectacionigvcompra: "1",
    codafectacionigvventa: "9",
    afectoicbper: "0",
    codatencion: "0",
    paraventa: "0",
    caracteristicas: "",
    comisionvendedor: 0,
    controlarseries: 0,
  };
  var campos_1 = {
    codunidad: "",
    unidad: "",
    factor: "1",
    preciocompra: "0.00",
    pventapublico: "0.00",
    pventamin: "0.00",
    pventacredito: "0.00",
    pventaxmayor: "0.00",
    pventaadicional: "0.00",
    codigobarra: ""
  };
</script>

<script src="<?php echo base_url(); ?>phuyu/phuyu_almacen/productos.js"></script>