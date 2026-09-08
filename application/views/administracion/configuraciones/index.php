<?php include("application/views/phuyu/phuyu_velzon_module.php"); ?>

<div id="phuyu_datos">
    <form id="formulario" v-on:submit.prevent="phuyu_guardar()" enctype="multipart/form-data">

        <input type="hidden" name="codpersona" v-model="campos.codpersona">
        <input type="hidden" name="codempresa" v-model="campos.codempresa">
        <input type="hidden" name="itemrepetircomprobante" v-model="campos.itemrepetircomprobante">

        <div class="container-fluid">

            <!-- TITULO -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3 phuyu-page-head">
                            <div>
                                <h4 class="mb-1 fw-bold">
                                    <i class="ri-settings-3-line me-1"></i>
                                    Configuración de Empresa
                                </h4>
                                <p class="text-muted mb-0">
                                    Administra datos principales, parámetros SUNAT, logos, mensajes y datos visibles para tus clientes.
                                </p>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-3-line me-1"></i>
                                Guardar configuración
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">

                <!-- COLUMNA IZQUIERDA -->
                <div class="col-xl-7">

                    <!-- DATOS EMPRESA -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ri-building-4-line me-1"></i>
                                Datos de la empresa
                            </h5>
                        </div>

                        <div class="card-body">

                            <div class="row g-3">
                                <div class="col-md-7">
                                    <label class="form-label">RUC empresa</label>
                                    <input type="text"
                                           class="form-control"
                                           name="documento"
                                           id="documento"
                                           v-model="campos.documento"
                                           placeholder="11 dígitos"
                                           minlength="11"
                                           maxlength="11"
                                           autocomplete="off"
                                           required>
                                    <small class="text-muted">Solo números.</small>
                                </div>

                                <div class="col-md-5 d-flex align-items-end">
                                    <button type="button"
                                            class="btn btn-info w-100"
                                            v-on:click="phuyu_consultar()">
                                        <i class="ri-search-line me-1"></i>
                                        Consultar SUNAT
                                    </button>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Razón social</label>
                                    <input type="text"
                                           class="form-control"
                                           name="razonsocial"
                                           v-model="campos.razonsocial"
                                           placeholder="Razón social"
                                           autocomplete="off"
                                           required>
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Nombre comercial</label>
                                    <input type="text"
                                           class="form-control"
                                           name="nombrecomercial"
                                           v-model="campos.nombrecomercial"
                                           placeholder="Nombre comercial"
                                           autocomplete="off">
                                </div>

                                <div class="col-md-8">
                                    <label class="form-label">Dirección</label>
                                    <input type="text"
                                           class="form-control"
                                           name="direccion"
                                           v-model="campos.direccion"
                                           placeholder="Av./Jr./Mz./Lt."
                                           autocomplete="off"
                                           required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Clave seguridad</label>
                                    <input type="password"
                                           class="form-control"
                                           name="claveseguridad"
                                           v-model="campos.claveseguridad"
                                           placeholder="••••••"
                                           autocomplete="off"
                                           maxlength="50">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Departamento</label>
                                    <select class="form-select"
                                            name="departamento"
                                            v-model="campos.departamento"
                                            v-on:change="phuyu_provincias()"
                                            required>
                                        <option value="">Seleccione</option>
                                        <?php foreach ($departamentos as $value) { ?>
                                            <option value="<?php echo $value['ubidepartamento']; ?>">
                                                <?php echo $value['departamento']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Provincia</label>
                                    <select class="form-select"
                                            name="provincia"
                                            id="provincia"
                                            v-model="campos.provincia"
                                            v-on:change="phuyu_distritos()"
                                            required>
                                        <option value="">Seleccione</option>
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Distrito</label>
                                    <select class="form-select"
                                            name="codubigeo"
                                            id="codubigeo"
                                            v-model="campos.codubigeo"
                                            required>
                                        <option value="">Seleccione</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Email empresa</label>
                                    <input type="email"
                                           class="form-control"
                                           name="email"
                                           v-model="campos.email"
                                           placeholder="correo@empresa.com"
                                           autocomplete="off">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label">Telf./Cel.</label>
                                    <input type="text"
                                           class="form-control"
                                           name="telefono"
                                           v-model="campos.telefono"
                                           placeholder="Ej. 942 000 000"
                                           autocomplete="off"
                                           maxlength="100">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label">Slogan empresa</label>
                                    <textarea class="form-control"
                                              name="slogan"
                                              v-model="campos.slogan"
                                              rows="2"
                                              placeholder="Una frase corta que identifique a la empresa"></textarea>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- LEYENDAS AMAZONIA -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ri-file-text-line me-1"></i>
                                Leyendas para comprobantes
                            </h5>
                        </div>

                        <div class="card-body">
                            <div class="row g-3">

                                <div class="col-md-4">
                                    <label class="form-label">Código bienes</label>
                                    <input type="text"
                                           class="form-control"
                                           name="codleyendapamazonia"
                                           v-model="campos.codleyendapamazonia"
                                           placeholder="Código">
                                </div>

                                <div class="col-md-8">
                                    <label class="form-label">Leyenda de bienes</label>
                                    <textarea class="form-control"
                                              name="leyendapamazonia"
                                              v-model="campos.leyendapamazonia"
                                              rows="2"
                                              placeholder="Leyenda de bienes"></textarea>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Código servicios</label>
                                    <input type="text"
                                           class="form-control"
                                           name="codleyendasamazonia"
                                           v-model="campos.codleyendasamazonia"
                                           placeholder="Código">
                                </div>

                                <div class="col-md-8">
                                    <label class="form-label">Leyenda de servicios</label>
                                    <textarea class="form-control"
                                              name="leyendasamazonia"
                                              v-model="campos.leyendasamazonia"
                                              rows="2"
                                              placeholder="Leyenda de servicios"></textarea>
                                </div>

                            </div>
                        </div>
                    </div>

                </div>

                <!-- COLUMNA DERECHA -->
                <div class="col-xl-5">

                    <!-- PARAMETROS SUNAT -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ri-percent-line me-1"></i>
                                Parámetros SUNAT
                            </h5>
                        </div>

                        <div class="card-body">
                            <div class="row g-3">

                                <div class="col-md-4">
                                    <label class="form-label">IGV (%)</label>
                                    <input type="number"
                                           step="0.01"
                                           class="form-control text-end"
                                           name="igvsunat"
                                           v-model.number="campos.igvsunat"
                                           required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">ICBPER</label>
                                    <input type="number"
                                           step="0.01"
                                           class="form-control text-end"
                                           name="icbpersunat"
                                           v-model.number="campos.icbpersunat"
                                           required>
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">ISC (%)</label>
                                    <input type="number"
                                           step="0.01"
                                           class="form-control text-end"
                                           name="iscsunat"
                                           v-model.number="campos.iscsunat"
                                           required>
                                </div>

                                <div class="col-12">
                                    <div class="form-check form-switch form-switch-md">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               id="itemrepetircomprobante"
                                               :checked="campos.itemrepetircomprobante == 1"
                                               @click="phuyu_itemrepetir()">
                                        <label class="form-check-label" for="itemrepetircomprobante">
                                            Repetir ítem en comprobante
                                        </label>
                                    </div>
                                    <small class="text-muted">
                                        Repite el último ítem al agregar productos o servicios.
                                    </small>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- LOGOS -->
                    <div class="card phuyu-logos-card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div>
                                <h5 class="card-title mb-0">
                                    <i class="ri-image-line me-1"></i>
                                    Logos
                                </h5>
                                <small class="text-muted">Estos logos se mostrarán en comprobantes, reportes y documentos.</small>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="row g-3">

                                <!-- LOGO EMPRESA -->
                                <div class="col-md-6">
                                    <div class="logo-upload-card">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <label class="form-label mb-0">Logo empresa</label>
                                            <span class="badge bg-primary-subtle text-primary">Actual</span>
                                        </div>

                                        <label class="logo-preview-box" for="logo_empresa_input">
                                            <?php if (!empty($info[0]["foto"])) { ?>
                                                <img id="logo_empresa_preview"
                                                     src="<?php echo base_url('public/img/empresa/' . $info[0]["foto"]); ?>"
                                                     alt="Logo empresa actual">
                                            <?php } else { ?>
                                                <div id="logo_empresa_empty" class="logo-empty">
                                                    <i class="ri-image-add-line"></i>
                                                    <span>Sin logo actual</span>
                                                    <small>Selecciona una imagen</small>
                                                </div>
                                                <img id="logo_empresa_preview" src="" alt="Logo empresa" style="display:none;">
                                            <?php } ?>
                                        </label>

                                        <input id="logo_empresa_input"
                                               type="file"
                                               class="form-control"
                                               name="logo"
                                               accept="image/*"
                                               onchange="previewLogo(this, 'logo_empresa_preview', 'logo_empresa_empty')">

                                        <small class="text-muted d-block mt-2">
                                            PNG/JPG recomendado. Si no seleccionas archivo, se mantiene el actual.
                                        </small>
                                    </div>
                                </div>

                                <!-- LOGO AUSPICIADOR -->
                                <div class="col-md-6">
                                    <div class="logo-upload-card">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <label class="form-label mb-0">Logo auspiciador</label>
                                            <span class="badge bg-secondary-subtle text-secondary">Opcional</span>
                                        </div>

                                        <label class="logo-preview-box" for="logo_auspiciador_input">
                                            <?php if (!empty($empresa[0]["logoauspiciador"])) { ?>
                                                <img id="logo_auspiciador_preview"
                                                     src="<?php echo base_url('public/img/empresa/' . $empresa[0]["logoauspiciador"]); ?>"
                                                     alt="Logo auspiciador actual">
                                            <?php } else { ?>
                                                <div id="logo_auspiciador_empty" class="logo-empty">
                                                    <i class="ri-image-add-line"></i>
                                                    <span>Sin logo actual</span>
                                                    <small>Selecciona una imagen</small>
                                                </div>
                                                <img id="logo_auspiciador_preview" src="" alt="Logo auspiciador" style="display:none;">
                                            <?php } ?>
                                        </label>

                                        <input id="logo_auspiciador_input"
                                               type="file"
                                               class="form-control"
                                               name="auspiciador"
                                               accept="image/*"
                                               onchange="previewLogo(this, 'logo_auspiciador_preview', 'logo_auspiciador_empty')">

                                        <small class="text-muted d-block mt-2">
                                            PNG/JPG recomendado. Si no seleccionas archivo, se mantiene el actual.
                                        </small>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- MENSAJES -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="ri-chat-quote-line me-1"></i>
                                Mensajes y enlaces
                            </h5>
                        </div>

                        <div class="card-body">
                            <div class="row g-3">

                                <div class="col-12">
                                    <label class="form-label">Publicidad</label>
                                    <textarea class="form-control"
                                              name="publicidad"
                                              v-model="campos.publicidad"
                                              rows="2"
                                              placeholder="Texto de publicidad"></textarea>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Agradecimiento</label>
                                    <textarea class="form-control"
                                              name="agradecimiento"
                                              v-model="campos.agradecimiento"
                                              rows="2"
                                              placeholder="Mensaje de agradecimiento"></textarea>
                                </div>

                                <div class="col-12">
                                    <label class="form-label">URL consulta comprobantes</label>
                                    <input type="url"
                                           class="form-control"
                                           name="urlconsultacomprobantes"
                                           v-model="campos.urlconsultacomprobantes"
                                           placeholder="https://tu-dominio.com/consultas">
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- GUARDAR -->
                    <div class="card">
                        <div class="card-body text-end">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="ri-save-3-line me-1"></i>
                                Guardar configuración
                            </button>
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </form>
</div>

<script>
    var campos = {
        codpersona: "<?php echo $info[0]["codpersona"]; ?>",
        codempresa: "<?php echo $empresa[0]["codempresa"]; ?>",
        documento: "<?php echo $info[0]["documento"]; ?>",
        razonsocial: "<?php echo $info[0]["razonsocial"]; ?>",
        nombrecomercial: "<?php echo $info[0]["nombrecomercial"]; ?>",
        direccion: "<?php echo $info[0]["direccion"]; ?>",
        claveseguridad: "<?php echo $empresa[0]["claveseguridad"]; ?>",
        email: "<?php echo $info[0]["email"]; ?>",
        telefono: "<?php echo $info[0]["telefono"]; ?>",
        slogan: "<?php echo $empresa[0]["slogan"]; ?>",
        igvsunat: "<?php echo $empresa[0]["igvsunat"]; ?>",
        icbpersunat: "<?php echo $empresa[0]["icbpersunat"]; ?>",
        iscsunat: "<?php echo $empresa[0]["iscsunat"]; ?>",
        itemrepetircomprobante: "<?php echo $empresa[0]["itemrepetircomprobante"]; ?>",
        publicidad: "<?php echo $empresa[0]["publicidad"]; ?>",
        agradecimiento: "<?php echo $empresa[0]["agradecimiento"]; ?>",
        departamento: "<?php echo $info[0]["departamento"]; ?>",
        provincia: "<?php echo $info[0]["provincia"]; ?>",
        codubigeo: "<?php echo $info[0]["distrito"]; ?>",
        provinciacod: "<?php echo $info[0]["provincia"]; ?>",
        codubigeocod: "<?php echo $info[0]["codubigeo"]; ?>",
        leyendapamazonia: "<?php echo $empresa[0]["leyendapamazonia"]; ?>",
        codleyendapamazonia: "<?php echo $empresa[0]["codleyendapamazonia"]; ?>",
        leyendasamazonia: "<?php echo $empresa[0]["leyendasamazonia"]; ?>",
        codleyendasamazonia: "<?php echo $empresa[0]["codleyendasamazonia"]; ?>",
        urlconsultacomprobantes: "<?php echo $empresa[0]["urlconsultacomprobantes"]; ?>"
    };
    function previewLogo(input, previewId, emptyId) {
        const preview = document.getElementById(previewId);
        const empty = document.getElementById(emptyId);
        const file = input.files && input.files[0];

        if (!file) {
            return;
        }

        preview.src = URL.createObjectURL(file);
        preview.style.display = 'block';

        if (empty) {
            empty.style.display = 'none';
        }
    }
</script>
<style>
    #phuyu_datos {
        padding-bottom: 2rem;
    }

    #phuyu_datos .card {
        margin-bottom: 1.5rem;
        border: 0;
        border-radius: 12px;
        box-shadow: 0 1px 2px rgba(56, 65, 74, .12), 0 8px 24px rgba(56, 65, 74, .06);
    }

    #phuyu_datos .card-header {
        background-color: #fff;
        border-bottom: 1px solid #e9ebec;
        padding: 1rem 1.25rem;
    }

    #phuyu_datos .card-body {
        padding: 1.25rem;
    }

    #phuyu_datos .card-title {
        font-weight: 700;
        color: #212529;
    }

    #phuyu_datos .form-label {
        font-weight: 600;
        color: #343a40;
    }

    #phuyu_datos textarea {
        resize: vertical;
    }

    #phuyu_datos .form-control,
    #phuyu_datos .form-select {
        border-radius: 8px;
    }

    #phuyu_datos .form-control:focus,
    #phuyu_datos .form-select:focus {
        border-color: #405189;
        box-shadow: 0 0 0 .15rem rgba(64, 81, 137, .15);
    }

    #phuyu_datos .phuyu-page-head h4 {
        color: #212529;
    }

    #phuyu_datos .phuyu-logos-card .card-header small {
        display: block;
        margin-top: .25rem;
    }

    #phuyu_datos .logo-upload-card {
        height: 100%;
        border: 1px solid #e9ebec;
        border-radius: 12px;
        padding: 1rem;
        background: linear-gradient(180deg, #ffffff, #f8f9fa);
    }

    #phuyu_datos .logo-preview-box {
        position: relative;
        width: 100%;
        height: 150px;
        border: 1px dashed #ced4da;
        border-radius: 12px;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        cursor: pointer;
        transition: all .2s ease-in-out;
        padding: 8px;
    }

    #phuyu_datos .logo-upload-card {
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    #phuyu_datos .logo-preview-box:hover {
        border-color: #405189;
        background: #f3f6f9;
    }

    #phuyu_datos .logo-preview-box img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: .5rem;
    }

    #phuyu_datos .logo-empty {
        color: #878a99;
        text-align: center;
        font-size: 13px;
        line-height: 1.4;
    }

    #phuyu_datos .logo-empty i {
        display: block;
        font-size: 34px;
        margin-bottom: .35rem;
        color: #405189;
    }

    #phuyu_datos .logo-empty span {
        display: block;
        font-weight: 600;
        color: #495057;
    }

    #phuyu_datos .logo-empty small {
        color: #878a99;
    }

    @media (max-width: 575.98px) {
        #phuyu_datos .phuyu-page-head {
            align-items: flex-start !important;
        }

        #phuyu_datos .phuyu-page-head .btn {
            width: 100%;
        }
    }
</style>
<script src="<?php echo base_url(); ?>phuyu/phuyu_empresa/configuraciones.js"></script>