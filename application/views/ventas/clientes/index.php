<?php include("application/views/phuyu/phuyu_velzon_module.php");?>

<style>
    #phuyu_datos .card {
        border-radius: 16px;
    }

    #phuyu_datos .form-control,
    #phuyu_datos .form-select {
        border-radius: 10px;
    }

    #phuyu_datos .phuyu-search-input {
        min-height: 42px;
    }

    #phuyu_datos .phuyu-btn-new {
        border-radius: 10px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all .2s ease;
    }

    #phuyu_datos .phuyu-btn-new:hover {
        transform: translateY(-2px);
    }

    #phuyu_datos .phuyu-tabla-contenedor {
        border: 1px solid #eef1f4;
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
    }

    #phuyu_datos .phuyu-tabla-clientes thead th {
        background: #f8f9fb;
        color: #495057;
        font-size: 12px;
        font-weight: 800;
        text-transform: uppercase;
        white-space: nowrap;
    }

    #phuyu_datos .phuyu-tabla-clientes tbody td {
        font-size: 13px;
        vertical-align: middle;
    }

    #phuyu_datos .phuyu-tabla-clientes tbody tr:hover {
        background: #fafcff;
    }

    #phuyu_datos .phuyu-action-btn {
        width: 34px;
        height: 34px;
        border-radius: 9px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    #phuyu_datos .phuyu-action-btn:hover {
        transform: translateY(-1px);
    }

    #phuyu_datos .phuyu-loading-overlay {
        position: absolute;
        inset: 0;
        background: rgba(255, 255, 255, .75);
        backdrop-filter: blur(2px);
        z-index: 5;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #phuyu_datos .phuyu-custom-modal-body label {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
    }
</style>

<div id="phuyu_datos" class="phuyu-velzon-list phuyu-ventas-velzon">
    <div class="phuyu-page-title">
        <span class="phuyu-page-icon"><i class="bi bi-people"></i></span>
        <div>
            <div class="text-muted small text-uppercase fw-semibold">Ventas</div>
            <h4 class="mb-1" id="title">Clientes</h4>
            <p class="text-muted mb-0">Administración de clientes para comprobantes, pedidos y proformas.</p>
        </div>
    </div>

    <div id="app" class="phuyu_body">
        <div class="card border-0 shadow-sm">
            <input type="hidden" id="phuyu_opcion" value="1">

            <div class="card-body">

                <div class="row align-items-center mb-4 g-3">
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="position-relative">
                            <input type="text"
                                class="form-control ps-5 phuyu-search-input"
                                v-model="buscar"
                                @keyup="phuyu_buscar"
                                placeholder="Buscar por razón social, documento..."
                                autocomplete="off">

                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 text-md-end">
                        <button type="button"
                            class="btn btn-success px-4 phuyu-btn-new"
                            @click="phuyu_nuevo"
                            data-bs-toggle="tooltip"
                            title="Nuevo cliente">
                            <i class="bi bi-person-plus"></i>
                            Nuevo
                        </button>
                    </div>
                </div>

                <div class="position-relative phuyu-tabla-contenedor">

                    <div v-if="cargando" class="phuyu-loading-overlay">
                        <div class="text-center">
                            <div class="spinner-border text-primary mb-2" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <div class="small text-muted">Cargando registros...</div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0 phuyu-tabla-clientes">
                            <thead>
                                <tr>
                                    <th style="width: 70px;">ID</th>
                                    <th style="width: 130px;">Documento</th>
                                    <th>Razón Social</th>
                                    <th>Dirección</th>
                                    <th style="width: 120px;">Teléfono</th>
                                    <th style="width: 180px;">Email</th>
                                    <th class="text-center" style="width: 120px;">Acciones</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr v-for="dato in datos" :key="dato.codpersona">
                                    <td class="text-muted fw-medium">{{ dato.codpersona }}</td>
                                    <td>{{ dato.documento || '-' }}</td>
                                    <td class="fw-semibold text-dark">{{ dato.razonsocial || '-' }}</td>
                                    <td>{{ dato.direccion || '-' }}</td>
                                    <td>{{ dato.telefono || '-' }}</td>
                                    <td>{{ dato.email || '-' }}</td>

                                    <td class="text-center">
                                        <div class="d-inline-flex gap-1">
                                            <button type="button"
                                                class="btn btn-sm btn-warning text-white phuyu-action-btn"
                                                @click="phuyu_editar(dato)"
                                                data-bs-toggle="tooltip"
                                                title="Editar">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>

                                            <button type="button"
                                                class="btn btn-sm btn-danger phuyu-action-btn"
                                                @click="phuyu_eliminar(dato)"
                                                data-bs-toggle="tooltip"
                                                title="Eliminar">
                                                <i class="bi bi-trash3"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <tr v-if="!cargando && datos.length === 0">
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                        No se encontraron registros
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mt-4 px-2 pb-3">
                        <div class="text-muted small">
                            Mostrando {{ paginacion.desde }} a {{ paginacion.hasta }} de {{ paginacion.total }} registros
                        </div>

                        <nav v-if="paginacion.ultima > 1">
                            <ul class="pagination pagination-sm mb-0">
                                <li class="page-item" :class="{ disabled: paginacion.actual === 1 }">
                                    <a class="page-link" href="javascript:;" @click="phuyu_paginacion(paginacion.actual - 1)">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>

                                <li v-for="pagina in phuyu_paginas"
                                    :key="pagina"
                                    class="page-item"
                                    :class="{ active: pagina === paginacion.actual }">
                                    <a class="page-link" href="javascript:;" @click="phuyu_paginacion(pagina)">
                                        {{ pagina }}
                                    </a>
                                </li>

                                <li class="page-item" :class="{ disabled: paginacion.actual === paginacion.ultima }">
                                    <a class="page-link" href="javascript:;" @click="phuyu_paginacion(paginacion.actual + 1)">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>

            </div>
        </div>

        <div class="phuyu-custom-overlay" v-show="modalAbierto">
            <div class="phuyu-custom-modal">
                <div class="phuyu-custom-modal-header">
                    <div>
                        <div class="text-uppercase small fw-semibold text-secondary">Clientes</div>
                        <h4 class="modal-title mb-0 fw-bold">
                            <i class="bi bi-person-badge me-2"></i>
                            <span v-if="!campos.codregistro">Registro de cliente</span>
                            <span v-else>Editar cliente</span>
                        </h4>
                    </div>

                    <button type="button"
                        class="btn-close"
                        @click="phuyu_cerrar_modal"
                        aria-label="Cerrar"></button>
                </div>

                <div class="phuyu-custom-modal-body">
                    <form @submit.prevent="phuyu_guardar">
                        <input type="hidden" v-model="campos.codregistro">
                        <input type="hidden" v-model="campos.codsociotipo">

                        <div class="row g-3 mb-3">
                            <div class="col-md-5">
                                <label class="form-label">Tipo de documento</label>
                                <select class="form-select"
                                    v-model="campos.coddocumentotipo"
                                    required
                                    @change="phuyu_tipodocumento"
                                    ref="coddocumentotipo">
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($tipodocumentos as $value): ?>
                                        <option value="<?= $value['coddocumentotipo'] ?>">
                                            <?= $value['descripcion'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-5">
                                <label class="form-label">Documento</label>
                                <div class="input-group">
                                    <input type="text"
                                        class="form-control"
                                        v-model="campos.documento"
                                        id="documento"
                                        placeholder="Ingrese número"
                                        required
                                        autocomplete="off"
                                        minlength="8"
                                        maxlength="15"
                                        ref="documento">

                                    <button type="button"
                                        class="btn btn-outline-secondary btn-consultar"
                                        @click="phuyu_consultar"
                                        title="Consultar">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button"
                                    class="btn btn-light w-100"
                                    @click="phuyu_cerrar_modal">
                                    <i class="bi bi-x-circle me-1"></i>
                                    Cerrar
                                </button>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12">
                                <label class="form-label">Razón social</label>
                                <input type="text"
                                    class="form-control"
                                    v-model="campos.razonsocial"
                                    placeholder="Razón social"
                                    required
                                    autocomplete="off">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Nombre comercial</label>
                                <input type="text"
                                    class="form-control"
                                    v-model="campos.nombrecomercial"
                                    placeholder="Nombre comercial"
                                    autocomplete="off">
                            </div>
                             <div class="col-12">
                                <label class="form-label">Departamento</label>
                                <select class="form-select"
                                    v-model="campos.departamento"
                                    required
                                    @change="phuyu_provincias">
                                    <option value="">Seleccione departamento</option>
                                    <?php foreach ($departamentos as $value): ?>
                                        <option value="<?= $value['ubidepartamento'] ?>">
                                            <?= $value['departamento'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Provincia</label>
                                <select class="form-select"
                                    v-model="campos.provincia"
                                    id="provincia"
                                    required
                                    @change="phuyu_distritos">
                                    <option value="">Seleccione provincia</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Distrito</label>
                                <select class="form-select"
                                    v-model="campos.codubigeo"
                                    id="codubigeo"
                                    required>
                                    <option value="">Seleccione distrito</option>
                                </select>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Dirección</label>
                                <input type="text"
                                    class="form-control"
                                    v-model="campos.direccion"
                                    placeholder="Dirección"
                                    required
                                    autocomplete="off">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email"
                                    class="form-control"
                                    v-model="campos.email"
                                    placeholder="Email"
                                    autocomplete="off">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Teléfono / Celular</label>
                                <input type="tel"
                                    class="form-control"
                                    v-model="campos.telefono"
                                    placeholder="Telf./Cel."
                                    autocomplete="off">
                            </div>



                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end gap-2">
                            <button type="button"
                                class="btn btn-light"
                                @click="phuyu_cerrar_modal">
                                Cancelar
                            </button>

                            <button type="submit"
                                class="btn btn-success"
                                :disabled="guardando">
                                <span v-if="guardando" class="spinner-border spinner-border-sm me-2"></span>
                                <i v-else class="bi bi-save me-1"></i>
                                Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    (function() {
        'use strict';

        // ========== CONFIGURACIÓN GLOBAL ESPERADA ==========
        // Variables que deben estar definidas en el entorno PHP/global:
        // - url (string): base URL para peticiones
        // - phuyu_controller (string): controlador actual, ej: "ventas/clientes"
        // - phuyu_sistema (object): métodos phuyu_alerta, phuyu_noti, phuyu_loader, phuyu_finloader, phuyu_fin
        // - swal (function): librería SweetAlert
        // - phuyu_creditos (object, opcional): usado en cuentas corrientes


        // Inicializar tooltips de Bootstrap
        function initTooltips() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        new Vue({
            el: '#app',
            data: {
                // Listado
                cargando: true,
                buscar: '',
                datos: [],
                paginacion: {
                    total: 0,
                    actual: 1,
                    ultima: 0,
                    desde: 0,
                    hasta: 0
                },
                offset: 3,

                // Modal
                modalAbierto: false,
                guardando: false,
                campos: {
                    codregistro: '',
                    codsociotipo: '1',
                    coddocumentotipo: '',
                    documento: '',
                    razonsocial: '',
                    nombrecomercial: '',
                    direccion: '',
                    email: '',
                    telefono: '',
                    departamento: '',
                    provincia: '',
                    codubigeo: '',
                    codpatrocinador: 0,
                    codzona: ''
                }
            },
            computed: {
                phuyu_paginas: function() {
                    if (!this.paginacion.hasta) return [];
                    var desde = this.paginacion.actual - this.offset;
                    if (desde < 1) desde = 1;
                    var hasta = desde + (this.offset * 2);
                    if (hasta >= this.paginacion.ultima) hasta = this.paginacion.ultima;
                    var paginas = [];
                    for (var i = desde; i <= hasta; i++) paginas.push(i);
                    return paginas;
                }
            },
            methods: {
                // ========== LISTADO ==========
                phuyu_opcion: function() {
                    if (document.getElementById('phuyu_opcion').value == '1') {
                        this.phuyu_datos_1();
                    } else {
                        this.phuyu_datos_2();
                    }
                },
                phuyu_datos_1: function() {
                    var vm = this;
                    vm.cargando = true;
                    var sucursal = document.getElementById('sucursal_search') ? document.getElementById(
                        'sucursal_search').value : '';
                    this.$http.post(url + phuyu_controller + '/lista', {
                        buscar: vm.buscar,
                        pagina: vm.paginacion.actual,
                        sucursal: sucursal
                    }).then(function(response) {
                        vm.datos = response.body.lista;
                        vm.paginacion = response.body.paginacion;
                        vm.cargando = false;
                        if (typeof phuyu_sistema !== 'undefined') phuyu_sistema.phuyu_fin();
                    }).catch(function() {
                        if (typeof phuyu_sistema !== 'undefined') {
                            phuyu_sistema.phuyu_alerta('ESTAMOS TENIENDO PROBLEMAS',
                                'ERROR DE RED', 'error');
                        }
                        vm.cargando = false;
                    });
                },
                phuyu_datos_2: function() {
                    if (typeof phuyu_sistema !== 'undefined') phuyu_sistema.phuyu_fin();
                },
                phuyu_buscar: function() {
                    this.paginacion.actual = 1;
                    this.phuyu_opcion();
                },
                phuyu_paginacion: function(pagina) {
                    if (pagina < 1 || pagina > this.paginacion.ultima) return;
                    this.paginacion.actual = pagina;
                    this.phuyu_opcion();
                },

                // ========== ACCIONES ==========
                phuyu_nuevo: function() {
                    this.resetFormulario();
                    this.modalAbierto = true;
                },
                phuyu_editar: function(cliente) {
                    var vm = this;
                    // Intentar usar datos locales primero
                    if (cliente.departamento !== undefined) {
                        // El objeto de la lista ya contiene los campos de ubicación
                        this.cargarFormulario(cliente);
                    } else {
                        // Hacer petición para obtener datos completos
                        vm.modalAbierto = true; // Abrir modal mientras carga
                        this.$http.post(url + phuyu_controller + '/editar', {
                                codregistro: cliente.codpersona
                            })
                            .then(function(response) {
                                var datos = eval(response.body);
                                if (datos.length) {
                                    vm.cargarFormulario(datos[0]);
                                } else {
                                    phuyu_sistema.phuyu_alerta(
                                        'NO SE PUDO OBTENER INFORMACIÓN COMPLETA',
                                        'INTENTE NUEVAMENTE', 'warning');
                                    vm.modalAbierto = false;
                                }
                            })
                            .catch(function() {
                                phuyu_sistema.phuyu_alerta('ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS',
                                    'ERROR DE RED', 'error');
                                vm.modalAbierto = false;
                            });
                    }
                },
                cargarFormulario: function(datosCliente) {
                    this.campos = {
                        codregistro: datosCliente.codpersona || '',
                        codsociotipo: datosCliente.codsociotipo || '1',
                        coddocumentotipo: datosCliente.coddocumentotipo || '',
                        documento: datosCliente.documento || '',
                        razonsocial: datosCliente.razonsocial || '',
                        nombrecomercial: datosCliente.nombrecomercial || '',
                        direccion: datosCliente.direccion || '',
                        email: datosCliente.email || '',
                        telefono: datosCliente.telefono || '',
                        departamento: datosCliente.departamento || '',
                        provincia: datosCliente.provincia || '',
                        codubigeo: datosCliente.codubigeo || '',
                        codpatrocinador: datosCliente.codpatrocinador || 0,
                        codzona: datosCliente.codzona || ''
                    };
                    this.modalAbierto = true;
                    this.$nextTick(function() {
                        this.phuyu_tipodocumento();
                        if (this.campos.departamento) {
                            this.phuyu_provincias();
                            setTimeout(() => {
                                if (this.campos.provincia) {
                                    this.phuyu_distritos();
                                }
                            }, 300);
                        }
                    });
                },
                phuyu_eliminar: function(cliente) {
                    var vm = this;
                    swal({
                        title: '¿SEGURO ELIMINAR REGISTRO?',
                        text: 'USTED ESTÁ POR ELIMINAR A ' + cliente.razonsocial,
                        icon: 'warning',
                        dangerMode: true,
                        buttons: ['CANCELAR', 'SI, ELIMINAR'],
                    }).then(function(willDelete) {
                        if (willDelete) {
                            vm.$http.post(url + phuyu_controller + '/eliminar', {
                                    codregistro: cliente.codpersona
                                })
                                .then(function(response) {
                                    if (response.body == 1) {
                                        phuyu_sistema.phuyu_alerta(
                                            'ELIMINADO CORRECTAMENTE',
                                            'UN REGISTRO ELIMINADO EN EL SISTEMA',
                                            'success');
                                    } else {
                                        phuyu_sistema.phuyu_alerta('OCURRIO UN ERROR !!!',
                                            'SE PERDIÓ LA CONEXIÓN !!! LO SENTIMOS',
                                            'error');
                                    }
                                    vm.phuyu_opcion();
                                })
                                .catch(function() {
                                    phuyu_sistema.phuyu_alerta(
                                        'ESTAMOS TENIENDO PROBLEMAS LO SENTIMOS',
                                        'ERROR DE RED', 'error');
                                });
                        }
                    });
                },

                // ========== MODAL ==========
                resetFormulario: function() {
                    this.campos = {
                        codregistro: '',
                        codsociotipo: '1',
                        coddocumentotipo: '',
                        documento: '',
                        razonsocial: '',
                        nombrecomercial: '',
                        direccion: '',
                        email: '',
                        telefono: '',
                        departamento: '',
                        provincia: '',
                        codubigeo: '',
                        codpatrocinador: 0,
                        codzona: ''
                    };
                    this.guardando = false;
                    this.$nextTick(function() {
                        var provinciaSelect = document.getElementById('provincia');
                        var distritoSelect = document.getElementById('codubigeo');
                        if (provinciaSelect) provinciaSelect.innerHTML =
                            '<option value="">Seleccione provincia</option>';
                        if (distritoSelect) distritoSelect.innerHTML =
                            '<option value="">Seleccione distrito</option>';
                    });
                },
                phuyu_cerrar_modal: function() {
                    this.modalAbierto = false;
                    this.guardando = false;
                },

                // ========== GUARDAR ==========
                phuyu_guardar: function() {
                    var vm = this;
                    vm.guardando = true;
                    var ruta = phuyu_controller;
                    if (phuyu_controller == 'creditos/cuentaspagar' || phuyu_controller ==
                        'creditos/cuentascobrar') {
                        ruta = 'caja/ctasctes';
                    }
                    this.$http.post(url + ruta + '/guardar', vm.campos).then(function(response) {
                        if (response.body == 'e') {
                            phuyu_sistema.phuyu_noti('ESTE NRO DE DOCUMENTO YA EXISTE',
                                'CAMBIAR DE NRO DOCUMENTO', 'danger');
                            vm.guardando = false;
                        } else if (response.body == 1) {
                            var mensaje = vm.campos.codregistro ? 'EDITADO CORRECTAMENTE' :
                                'GUARDADO CORRECTAMENTE';
                            var tipo = vm.campos.codregistro ? 'info' : 'success';
                            phuyu_sistema.phuyu_alerta(mensaje, 'UN REGISTRO ' + (vm.campos
                                    .codregistro ? 'EDITADO' : 'NUEVO') + ' EN EL SISTEMA',
                                tipo);
                            if (phuyu_controller != 'creditos/cuentaspagar' &&
                                phuyu_controller != 'creditos/cuentascobrar') {
                                vm.phuyu_opcion();
                                vm.phuyu_cerrar_modal();
                            } else {
                                document.getElementById('phuyu_tituloform').innerText =
                                    'BUSCAR CUENTAS CORRIENTES DEL SOCIO';
                                phuyu_sistema.phuyu_loader('cuerpo', 180);
                                vm.$http.post(url + 'caja/ctasctes/buscar', {
                                    codregistro: phuyu_creditos.registro
                                }).then(function(res) {
                                    document.getElementById('cuerpo').innerHTML = res
                                        .body;
                                    phuyu_sistema.phuyu_finloader('cuerpo');
                                });
                            }
                        } else {
                            phuyu_sistema.phuyu_alerta('OCURRIO UN ERROR AL REGISTRAR',
                                'NO SE PUEDE REGISTRAR', 'error');
                            vm.guardando = false;
                        }
                    }).catch(function() {
                        phuyu_sistema.phuyu_alerta('ESTAMOS TENIENDO PROBLEMAS', 'ERROR DE RED',
                            'error');
                        vm.guardando = false;
                    });
                },

                // ========== LÓGICA DEL FORMULARIO ==========
                phuyu_tipodocumento: function() {
                    var docInput = document.getElementById('documento');
                    if (!docInput) return;
                    if (this.campos.coddocumentotipo == 2) {
                        docInput.setAttribute('minlength', '8');
                        docInput.setAttribute('maxlength', '8');
                    } else if (this.campos.coddocumentotipo == 4) {
                        docInput.setAttribute('minlength', '11');
                        docInput.setAttribute('maxlength', '11');
                    } else {
                        docInput.setAttribute('minlength', '8');
                        docInput.setAttribute('maxlength', '15');
                    }
                    if (this.campos.coddocumentotipo == 1) {
                        docInput.removeAttribute('required');
                        docInput.setAttribute('readonly', 'readonly');
                        document.querySelector('.btn-consultar')?.setAttribute('disabled', 'disabled');
                    } else {
                        docInput.setAttribute('required', 'required');
                        docInput.removeAttribute('readonly');
                        document.querySelector('.btn-consultar')?.removeAttribute('disabled');
                    }
                },
                phuyu_consultar: function() {
                    var vm = this;
                    if (!vm.campos.coddocumentotipo) {
                        phuyu_sistema.phuyu_noti('SELECCIONE TIPO DE DOCUMENTO',
                            'DEBE SELECCIONAR . . .', 'danger');
                        vm.$refs.coddocumentotipo.focus();
                        return;
                    }
                    if (vm.campos.coddocumentotipo == 2 && vm.campos.documento.length != 8) {
                        vm.$refs.documento.focus();
                        return;
                    }
                    if (vm.campos.coddocumentotipo == 4 && vm.campos.documento.length != 11) {
                        vm.$refs.documento.focus();
                        return;
                    }
                    var btn = document.querySelector('.btn-consultar');
                    if (btn) btn.setAttribute('disabled', 'disabled');
                    vm.$http.get(url + 'web/phuyu_buscarsocio/' + vm.campos.documento).then(function(
                        response) {
                        if (response.body != '') {
                            var datos = eval(response.body);
                            if (datos[0].estado !== '0') {
                                vm.campos.razonsocial = datos[0].razonsocial;
                                vm.campos.nombrecomercial = datos[0].nombrecomercial;
                                vm.campos.direccion = datos[0].direccion;
                                vm.campos.email = datos[0].email;
                                vm.campos.telefono = datos[0].telefono;
                                phuyu_sistema.phuyu_noti('DOCUMENTO EXISTE EN EL SISTEMA',
                                    'DOCUMENTO YA REGISTRADO', 'warning');
                                if (btn) btn.removeAttribute('disabled');
                            } else {
                                swal({
                                    title: 'EL REGISTRO ESTÁ ELIMINADO O ANULADO',
                                    text: '¿USTED DESEA ACTIVARLO DE NUEVO AL SISTEMA?',
                                    icon: 'warning',
                                    dangerMode: true,
                                    buttons: ['CANCELAR', 'SI, ACTIVAR'],
                                }).then(function(willActivate) {
                                    if (willActivate) {
                                        vm.$http.post(url + phuyu_controller +
                                            '/activar/' + datos[0].codpersona).then(
                                            function(res) {
                                                if (res.body == 1) {
                                                    phuyu_sistema.phuyu_alerta(
                                                        'ACTIVADO CORRECTAMENTE',
                                                        'UN REGISTRO ACTIVADO EN EL SISTEMA',
                                                        'success');
                                                    vm.phuyu_opcion();
                                                } else {
                                                    phuyu_sistema.phuyu_alerta(
                                                        'OCURRIO UN ERROR !!!',
                                                        'SE PERDIÓ LA CONEXIÓN !!! LO SENTIMOS',
                                                        'error');
                                                }
                                                vm.phuyu_cerrar_modal();
                                            });
                                    }
                                });
                                vm.phuyu_cerrar_modal();
                            }
                        } else {
                            if (vm.campos.coddocumentotipo == 2) {
                                vm.$http.get(url + 'web/phuyu_dni/' + vm.campos.documento).then(
                                    function(res) {
                                        if (res.body.success) {
                                            var r = res.body.result;
                                            vm.campos.razonsocial = r.apellidoPaterno +
                                                ' ' + r.apellidoMaterno + ' ' + r.nombres;
                                            vm.campos.direccion = '-';
                                        } else {
                                            phuyu_sistema.phuyu_noti(
                                                'NO SE ENCONTRARON DATOS',
                                                'DOCUMENTO NO EXISTE', 'danger');
                                        }
                                        if (btn) btn.removeAttribute('disabled');
                                    });
                            } else if (vm.campos.coddocumentotipo == 4) {
                                vm.$http.post(url + 'web/phuyu_ruc/' + vm.campos.documento)
                                    .then(function(res) {
                                        if (res.body.persona) {
                                            vm.campos.razonsocial = res.body.persona
                                                .razonSocial;
                                            vm.campos.direccion = res.body.persona
                                            .direccion;
                                            vm.campos.nombrecomercial = res.body.persona
                                                .razonSocial;
                                        } else {
                                            phuyu_sistema.phuyu_noti(
                                                'NO SE ENCONTRARON DATOS',
                                                'DOCUMENTO NO EXISTE', 'danger');
                                        }
                                        if (btn) btn.removeAttribute('disabled');
                                    });
                            } else {
                                phuyu_sistema.phuyu_noti('NO SE ENCONTRARON DATOS',
                                    'DOCUMENTO NO EXISTE', 'danger');
                                if (btn) btn.removeAttribute('disabled');
                            }
                        }
                    });
                },
                phuyu_provincias: function() {
                    var vm = this;
                    if (vm.campos.departamento) {
                        vm.$http.get(url + 'ventas/clientes/provincias/' + vm.campos.departamento).then(
                            function(response) {
                                document.getElementById('provincia').innerHTML = response.body;
                                document.getElementById('codubigeo').innerHTML =
                                    '<option value="">SELECCIONE</option>';
                                // Si estamos editando y ya había provincia seleccionada, se debe restaurar después de cargar opciones
                                if (vm.campos.provincia) {
                                    setTimeout(() => {
                                        document.getElementById('provincia').value = vm
                                            .campos.provincia;
                                        vm
                                    .phuyu_distritos(); // Cargar distritos correspondientes
                                    }, 100);
                                }
                            });
                    }
                },
                phuyu_distritos: function() {
                    var vm = this;
                    if (vm.campos.provincia) {
                        vm.$http.get(url + 'ventas/clientes/distritos/' + vm.campos.departamento + '/' +
                            vm.campos.provincia).then(function(response) {
                            document.getElementById('codubigeo').innerHTML = response.body;
                            if (vm.campos.codubigeo) {
                                setTimeout(() => {
                                    document.getElementById('codubigeo').value = vm
                                        .campos.codubigeo;
                                }, 50);
                            }
                        });
                    }
                }
            },
            mounted: function() {
                initTooltips();
                this.phuyu_opcion();
                if (typeof phuyu_sistema !== 'undefined') phuyu_sistema.phuyu_fin();
            }
        });
    })();
</script>
