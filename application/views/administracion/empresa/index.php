<style>
.phuyu_datos-container {
    --phuyu-primary: #4361ee;
    --phuyu-primary-soft: #edf2ff;
    --phuyu-secondary: #0dcaf0;
    --phuyu-accent: #22c55e;
    --phuyu-bg: #f5f7ff;
    --phuyu-text-main: #1f2933;
    --phuyu-text-muted: #6b7280;

    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: var(--phuyu-text-main);
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px 12px 30px;
    background: radial-gradient(circle at top left, #eef2ff 0, transparent 55%),
                radial-gradient(circle at bottom right, #e0f2fe 0, transparent 55%);
}

/* CARD PRINCIPAL */
.phuyu-card {
    background: #ffffff;
    border-radius: 18px;
    box-shadow:
        0 20px 45px rgba(15, 23, 42, 0.10),
        0 0 0 1px rgba(148, 163, 184, 0.15);
    overflow: hidden;
    border: none;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    position: relative;
}

.phuyu-card::before {
    content: "";
    position: absolute;
    inset: 0;
    border-radius: 18px;
    border-top: 4px solid var(--phuyu-primary);
    pointer-events: none;
}

.phuyu-card:hover {
    transform: translateY(-4px);
    box-shadow:
        0 28px 60px rgba(15, 23, 42, 0.16),
        0 0 0 1px rgba(129, 140, 248, 0.25);
}

.phuyu-card-body {
    padding: 26px 28px 24px;
}

/* HEADER EMPRESA */
.phuyu-header {
    display: flex;
    align-items: flex-start;
    margin-bottom: 24px;
    padding-bottom: 18px;
    border-bottom: 1px dashed rgba(148, 163, 184, 0.45);
    gap: 18px;
}

.phuyu-logo-wrapper {
    position: relative;
}

.phuyu-logo {
    width: 120px;
    height: 120px;
    border-radius: 20px;
    object-fit: cover;
    box-shadow: 0 12px 30px rgba(15, 23, 42, 0.25);
    border: 3px solid #ffffff;
    background: #f9fafb;
}

.phuyu-logo-pill {
    position: absolute;
    bottom: -8px;
    left: 4px;
    padding: 4px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    background: rgba(67, 97, 238, 0.10);
    color: var(--phuyu-primary);
    backdrop-filter: blur(6px);
}

.phuyu-company-info {
    flex: 1;
}

.phuyu-company-name {
    font-size: 26px;
    font-weight: 800;
    color: #111827;
    margin-bottom: 6px;
    line-height: 1.2;
    display: flex;
    align-items: center;
    gap: 10px;
}

.phuyu-company-tag {
    font-size: 11px;
    padding: 3px 9px;
    border-radius: 999px;
    background: rgba(67, 97, 238, 0.08);
    color: var(--phuyu-primary);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-weight: 600;
}

.phuyu-ruc {
    display: inline-flex;
    align-items: center;
    background: rgba(148, 163, 184, 0.15);
    padding: 6px 14px;
    border-radius: 999px;
    font-weight: 600;
    color: #374151;
    font-size: 13px;
    margin-bottom: 10px;
    gap: 6px;
}

.phuyu-ruc i {
    font-size: 14px;
    color: var(--phuyu-primary);
}

.phuyu-address {
    font-size: 14px;
    color: var(--phuyu-text-muted);
    margin-bottom: 12px;
    line-height: 1.4;
}

.phuyu-contact-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px 18px;
}

.phuyu-contact-list li {
    display: flex;
    align-items: center;
    font-size: 13px;
    color: #4b5563;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.phuyu-contact-list i {
    width: 20px;
    text-align: center;
    margin-right: 8px;
    color: var(--phuyu-primary);
    font-size: 14px;
}

/* GRID CONTENIDO */
.phuyu-content-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.3fr) 1px minmax(0, 1fr);
    gap: 24px;
    margin-bottom: 18px;
    padding-top: 6px;
}

.phuyu-divider {
    background: linear-gradient(to bottom, transparent, #d1d5db, transparent);
    width: 1px;
}

/* TITULOS */
.phuyu-section-title {
    font-size: 15px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 14px;
    padding-bottom: 6px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    position: relative;
}

.phuyu-section-title::after {
    content: "";
    height: 3px;
    width: 38px;
    border-radius: 999px;
    background: linear-gradient(90deg, var(--phuyu-primary), var(--phuyu-secondary));
    position: absolute;
    bottom: 0;
    left: 0;
}

/* LISTA FACTURACIÓN */
.phuyu-facturacion-list {
    list-style: none;
    padding: 0;
    margin: 0 0 10px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.phuyu-facturacion-list li {
    display: flex;
    align-items: center;
    padding: 10px 11px;
    background: #f9fafb;
    border-radius: 10px;
    border: 1px solid rgba(209, 213, 219, 0.6);
    transition: all 0.18s ease;
    font-size: 13px;
    color: #374151;
}

.phuyu-facturacion-list li:hover {
    background: var(--phuyu-primary-soft);
    border-color: rgba(67, 97, 238, 0.45);
    transform: translateX(3px);
    box-shadow: 0 6px 16px rgba(148, 163, 248, 0.35);
}

.phuyu-facturacion-list i {
    width: 22px;
    text-align: center;
    margin-right: 10px;
    color: var(--phuyu-primary);
    font-size: 15px;
}

.phuyu-facturacion-label {
    font-weight: 600;
    margin-right: 4px;
    color: #111827;
}

/* LINK DESCARGA */
.phuyu-download-link {
    display: inline-flex;
    align-items: center;
    color: var(--phuyu-primary) !important;
    text-decoration: none;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.22s ease;
    padding: 11px 13px;
    background: #f9fafb;
    border-radius: 999px;
    border: 1px solid rgba(209, 213, 219, 0.7);
    margin-top: 4px;
}

.phuyu-download-link:hover {
    background: linear-gradient(90deg, var(--phuyu-primary), var(--phuyu-secondary));
    color: #ffffff !important;
    transform: translateY(-1px);
    border-color: transparent;
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.5);
}

.phuyu-download-link i {
    margin-right: 8px;
    font-size: 16px;
}

/* ESTADO SUNAT / OSE */
.phuyu-status-section {
    margin-top: 4px;
}

.phuyu-status-title {
    font-size: 13px;
    font-weight: 700;
    color: #991b1b;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}

.phuyu-status-title i {
    font-size: 14px;
}

.phuyu-status-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.phuyu-badge {
    padding: 7px 12px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    border: 1px solid transparent;
}

.phuyu-badge-success {
    background: rgba(34, 197, 94, 0.14);
    color: #166534;
    border-color: rgba(34, 197, 94, 0.4);
}

.phuyu-badge-primary {
    background: rgba(67, 97, 238, 0.1);
    color: var(--phuyu-primary);
    border-color: rgba(67, 97, 238, 0.45);
}

/* ACCIONES */
.phuyu-actions {
    display: flex;
    justify-content: flex-end;
    gap: 14px;
    margin-top: 18px;
    padding-top: 14px;
    border-top: 1px dashed rgba(148, 163, 184, 0.6);
}

.phuyu-actions-left {
    flex: 1;
    font-size: 11px;
    color: var(--phuyu-text-muted);
    display: flex;
    align-items: center;
    gap: 6px;
}

/* BOTONES */
.phuyu-btn {
    display: inline-flex;
    align-items: center;
    padding: 11px 20px;
    border-radius: 999px;
    font-weight: 600;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.24s ease;
    border: none;
    outline: none;
    gap: 8px;
    box-shadow: 0 8px 18px rgba(15, 23, 42, 0.22);
    white-space: nowrap;
}

.phuyu-btn i {
    font-size: 15px;
}

.phuyu-btn-success {
    background: linear-gradient(120deg, var(--phuyu-primary), #6366f1);
    color: #ffffff;
}

.phuyu-btn-success:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(79, 70, 229, 0.60);
}

.phuyu-btn-warning {
    background: linear-gradient(120deg, #0ea5e9, #22c55e);
    color: #ffffff;
}

.phuyu-btn-warning:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 30px rgba(45, 212, 191, 0.6);
}

/* RESPONSIVE */
@media (max-width: 992px) {
    .phuyu-header {
        flex-direction: column;
    }

    .phuyu-logo {
        margin-bottom: 12px;
    }

    .phuyu-contact-list {
        grid-template-columns: 1fr;
    }

    .phuyu-content-grid {
        grid-template-columns: 1fr;
    }

    .phuyu-divider {
        display: none;
    }

    .phuyu-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .phuyu-btn {
        justify-content: center;
        width: 100%;
    }
}

@media (max-width: 576px) {
    .phuyu-card-body {
        padding: 18px 16px 16px;
    }

    .phuyu-company-name {
        font-size: 22px;
        flex-wrap: wrap;
    }

    .phuyu-ruc {
        font-size: 12px;
    }
}
</style>

<div id="phuyu_datos" class="phuyu_datos-container">
    <div class="phuyu-card">
        <div class="phuyu-card-body">
            <!-- Header con logo e información básica -->
            <div class="phuyu-header">
                <div class="phuyu-logo-wrapper">
                    <img class="phuyu-logo" src="<?php echo base_url();?>public/img/empresa/<?php echo $info[0]['foto']?>" alt="Logo Empresa">
                    <div class="phuyu-logo-pill">Phuyu System</div>
                </div>
                <div class="phuyu-company-info">
                    <input type="hidden" id="codempresa" value="<?php echo $_SESSION["phuyu_codempresa"];?>">
                    <h2 class="phuyu-company-name">
                        <?php echo $info[0]["nombrecomercial"];?>
                        <span class="phuyu-company-tag">Facturación electrónica</span>
                    </h2>
                    <div class="phuyu-ruc">
                        <i class="fas fa-id-card-alt"></i>
                        <span>RUC: <?php echo $info[0]["documento"];?></span>
                    </div>
                    <div class="phuyu-address"><?php echo $info[0]["direccion"];?></div>
                    <ul class="phuyu-contact-list">
                        <li><i class="fas fa-envelope"></i> EMAIL: <?php echo $info[0]["email"];?></li>
                        <li><i class="fas fa-phone"></i> TELF./CEL.: <?php echo $info[0]["telefono"];?></li>
                        <li><i class="fas fa-map-marker-alt"></i> UBIGEO: <?php echo $empresa[0]["departamento"]."-".$empresa[0]["provincia"]."-".$empresa[0]["distrito"]." (".$empresa[0]["ubigeo"].")";?></li>
                    </ul>
                </div>
            </div>
            
            <!-- Contenido principal en dos columnas -->
            <div class="phuyu-content-grid">
                <!-- Columna izquierda - Datos de facturación -->
                <div>
                    <h3 class="phuyu-section-title">
                        <i class="fas fa-file-invoice"></i> DATOS DE FACTURACIÓN
                    </h3>
                    <ul class="phuyu-facturacion-list">
                        <li>
                            <i class="fas fa-user"></i>
                            <span><span class="phuyu-facturacion-label">USUARIO SOL:</span> <?php echo $service[0]["usuariosol"];?></span>
                        </li>
                        <li>
                            <i class="fas fa-key"></i>
                            <span><span class="phuyu-facturacion-label">CLAVE SOL:</span> <?php echo $service[0]["clavesol"];?></span>
                        </li>
                        <li>
                            <i class="fas fa-paper-plane"></i>
                            <span><span class="phuyu-facturacion-label">EMAIL ENVÍO:</span> <?php echo $service[0]["envioemail"];?></span>
                        </li>
                        <li>
                            <i class="fas fa-lock"></i>
                            <span><span class="phuyu-facturacion-label">EMAIL CLAVE:</span> <?php echo $service[0]["claveemail"];?></span>
                        </li>
                        <li>
                            <i class="fas fa-certificate"></i>
                            <span><span class="phuyu-facturacion-label">CLAVE CERTIFICADO:</span> <?php echo $service[0]["certificado_clave"];?></span>
                        </li>
                    </ul>
                    <a class="phuyu-download-link" download="<?php echo $service[0]['certificado_pfx'];?>" href="<?php echo base_url();?>sunat/certificado/<?php echo $service[0]['certificado_pfx'];?>">
                        <i class="fas fa-cloud-download-alt"></i> DESCARGAR CERTIFICADO DIGITAL
                    </a>
                </div>
                
                <!-- Divisor -->
                <div class="phuyu-divider"></div>
                
                <!-- Columna derecha - Estado y configuración -->
                <div>
                    <div class="phuyu-status-section">
                        <h4 class="phuyu-status-title">
                            <i class="fas fa-shield-alt"></i>
                            ARCHIVOS PEM: <?php echo $pen;?>
                        </h4>
                        <div class="phuyu-status-badges">
                            <?php if ($service[0]["sunatose"]==0) { ?>
                                <span class="phuyu-badge phuyu-badge-success">
                                    <i class="fas fa-bolt"></i> SERVICIO: SUNAT
                                </span>
                            <?php } else { ?>
                                <span class="phuyu-badge phuyu-badge-success">
                                    <i class="fas fa-building"></i> SERVICIO: OSE
                                </span>
                            <?php } ?>
                            
                            <?php if ($service[0]["serviceweb"]==0) { ?>
                                <span class="phuyu-badge phuyu-badge-primary">
                                    <i class="fas fa-toggle-on"></i> ESTADO: PRODUCCIÓN
                                </span>
                            <?php } else { ?>
                                <span class="phuyu-badge phuyu-badge-primary">
                                    <i class="fas fa-vial"></i> ESTADO: BETA HOMOLOGACIÓN
                                </span>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Acciones -->
            <div class="phuyu-actions">
                <div class="phuyu-actions-left">
                    <i class="fas fa-info-circle"></i>
                    Configura tus credenciales para que Phuyu System pueda emitir y enviar tus comprobantes electrónicos sin errores.
                </div>
                <button type="button" class="phuyu-btn phuyu-btn-success" v-on:click="phuyu_editar()">
                    <i class="fas fa-edit"></i> CONFIGURAR FACTURACIÓN
                </button>
                <button type="button" class="phuyu-btn phuyu-btn-warning" v-on:click="phuyu_copia()">
                    <i class="fas fa-database"></i> COPIA DE SEGURIDAD
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    if (typeof AcornIcons !== 'undefined') {
        new AcornIcons().replace();
    }
    if (typeof Icons !== 'undefined') {
        const icons = new Icons();
    }
</script>
<script src="<?php echo base_url();?>phuyu/phuyu_empresa/index.js"></script>
