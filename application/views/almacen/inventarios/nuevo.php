<style>
    #phuyu_form.phuyu-inventario-form {
        color: #212529;
    }
    #phuyu_form.phuyu-inventario-form .phuyu-form-card {
        border: 1px solid #e9ebec;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(56, 65, 74, 0.08);
        overflow: hidden;
    }
    #phuyu_form.phuyu-inventario-form .phuyu-form-header {
        align-items: center;
        background: #f3f6f9;
        border: 1px solid #e9ebec;
        border-radius: 8px;
        display: flex;
        gap: 12px;
        margin-bottom: 18px;
        padding: 14px 16px;
    }
    #phuyu_form.phuyu-inventario-form .phuyu-form-icon {
        align-items: center;
        background: rgba(64, 81, 137, 0.12);
        border-radius: 8px;
        color: #405189;
        display: inline-flex;
        font-size: 22px;
        height: 44px;
        justify-content: center;
        width: 44px;
    }
    #phuyu_form.phuyu-inventario-form label {
        color: #495057;
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 7px;
        text-transform: uppercase;
    }
    #phuyu_form.phuyu-inventario-form .form-control,
    #phuyu_form.phuyu-inventario-form .form-select {
        border: 1px solid #d9e2ef;
        border-radius: 6px;
        box-shadow: none;
        min-height: 38px;
    }
    #phuyu_form.phuyu-inventario-form .phuyu-form-actions {
        border-top: 1px solid #e9ebec;
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: center;
        margin-top: 16px;
        padding-top: 18px;
    }
</style>

<div id="phuyu_form" class="phuyu-inventario-form">
    <div class="card phuyu-form-card">
        <div class="card-body">
            <div class="phuyu-form-header">
                <div class="phuyu-form-icon">
                    <i class="bi bi-clipboard-data"></i>
                </div>
                <div>
                    <p class="text-muted text-uppercase mb-1">Inventario</p>
                    <h5 class="mb-0">Nuevo inventario</h5>
                </div>
            </div>

            <form id="formulario" v-on:submit.prevent="phuyu_guardar()">
                <input type="hidden" name="codregistro" v-model="campos.codregistro">

                <div class="row g-3">
                    <div class="col-md-12">
                        <label>SELECCIONAR SUCURSAL</label>
                        <select class="form-select" name="codsucursal" v-model="campos.codsucursal" required v-on:change="phuyu_almacenes()">
                            <?php
                                foreach ($sucursales as $key => $value) { ?>
                                    <option value="<?php echo $value["codsucursal"];?>"><?php echo $value["descripcion"];?></option>
                                <?php }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label>SELECCIONAR ALMACEN</label>
                        <select class="form-select" name="codalmacen" v-model="campos.codalmacen" required>
                            <option value="">SELECCIONE</option>
                            <option v-for="dato in almacenes" v-bind:value="dato.codalmacen"> {{dato.descripcion}} </option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label>TIPO INVENTARIO</label>
                        <select name="descripcion" class="form-select" v-model="campos.tipoinventario" required>
                            <option value="0">INVENTARIO INICIAL</option>
                            <option value="1">INVENTARIO SEMANAL</option>
                            <option value="2">INVENTARIO MENSUAL</option>
                            <option value="3">INVENTARIO TRIMESTRAL</option>
                            <option value="4">INVENTARIO ANUAL</option>
                        </select>
                    </div>
                </div>

                <div class="phuyu-form-actions">
                    <button type="submit" class="btn btn-success" v-bind:disabled="estado==1">
                        <i class="bi bi-save me-1"></i> Guardar
                    </button>
                    <button type="button" class="btn btn-light" v-on:click="phuyu_cerrar()">
                        <i class="bi bi-x-lg me-1"></i> Cerrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script> var campos = {codregistro:"",codsucursal:"<?php echo $_SESSION['phuyu_codsucursal'];?>",codalmacen:"",tipoinventario:"2",codmovimientotipo:"9"}; </script>
<script src="<?php echo base_url();?>phuyu/phuyu_inventarios/nuevo.js"></script>
