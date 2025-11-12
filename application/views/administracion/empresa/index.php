<style>
.phuyu_datos-container {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #333;
    max-width: 1200px;
    margin: 0 auto;
}

.phuyu-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    overflow: hidden;
    border: none;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.phuyu-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
}

.phuyu-card-body {
    padding: 30px;
}

.phuyu-header {
    display: flex;
    align-items: flex-start;
    margin-bottom: 30px;
    padding-bottom: 25px;
    border-bottom: 1px solid #f0f0f0;
}

.phuyu-logo {
    width: 140px;
    height: 140px;
    border-radius: 12px;
    object-fit: cover;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    margin-right: 25px;
    border: 3px solid #fff;
}

.phuyu-company-info {
    flex: 1;
}

.phuyu-company-name {
    font-size: 28px;
    font-weight: 700;
    color: #2c3e50;
    margin-bottom: 8px;
    line-height: 1.2;
}

.phuyu-ruc {
    display: inline-block;
    background: #f8f9fa;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    color: #495057;
    font-size: 14px;
    margin-bottom: 15px;
}

.phuyu-address {
    font-size: 16px;
    color: #6c757d;
    margin-bottom: 20px;
    line-height: 1.5;
}

.phuyu-contact-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.phuyu-contact-list li {
    display: flex;
    align-items: center;
    margin-bottom: 12px;
    font-size: 15px;
    color: #495057;
}

.phuyu-contact-list i {
    width: 24px;
    text-align: center;
    margin-right: 10px;
    color: #4361ee;
}

.phuyu-content-grid {
    display: grid;
    grid-template-columns: 1fr 1px 1fr;
    gap: 30px;
    margin-bottom: 30px;
}

.phuyu-divider {
    background: linear-gradient(to bottom, transparent, #e9ecef, transparent);
    width: 1px;
}

.phuyu-section-title {
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #4361ee;
    display: inline-block;
}

.phuyu-facturacion-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.phuyu-facturacion-list li {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
    padding: 12px 15px;
    background: #f8f9fa;
    border-radius: 10px;
    transition: all 0.2s ease;
}

.phuyu-facturacion-list li:hover {
    background: #edf2ff;
    transform: translateX(5px);
}

.phuyu-facturacion-list i {
    width: 24px;
    text-align: center;
    margin-right: 12px;
    color: #4361ee;
}

.phuyu-download-link {
    display: flex;
    align-items: center;
    color: #4361ee !important;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.2s ease;
    padding: 12px 15px;
    background: #f8f9fa;
    border-radius: 10px;
}

.phuyu-download-link:hover {
    background: #4361ee;
    color: white !important;
    transform: translateX(5px);
}

.phuyu-download-link i {
    margin-right: 10px;
}

.phuyu-status-section {
    margin-top: 25px;
}

.phuyu-status-title {
    font-size: 16px;
    font-weight: 600;
    color: #e74c3c;
    margin-bottom: 15px;
}

.phuyu-status-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.phuyu-badge {
    padding: 8px 15px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    display: inline-block;
}

.phuyu-badge-success {
    background: #d4edda;
    color: #155724;
}

.phuyu-badge-primary {
    background: #d1ecf1;
    color: #0c5460;
}

.phuyu-actions {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 30px;
    padding-top: 30px;
    border-top: 1px solid #f0f0f0;
}

.phuyu-btn {
    display: inline-flex;
    align-items: center;
    padding: 14px 28px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.phuyu-btn i {
    margin-right: 10px;
    font-size: 18px;
}

.phuyu-btn-success {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
}

.phuyu-btn-success:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(40, 167, 69, 0.3);
}

.phuyu-btn-warning {
    background: linear-gradient(135deg, #ffc107, #fd7e14);
    color: white;
}

.phuyu-btn-warning:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(255, 193, 7, 0.3);
}

/* Responsive */
@media (max-width: 992px) {
    .phuyu-content-grid {
        grid-template-columns: 1fr;
    }
    
    .phuyu-divider {
        display: none;
    }
    
    .phuyu-header {
        flex-direction: column;
    }
    
    .phuyu-logo {
        margin-right: 0;
        margin-bottom: 20px;
        align-self: center;
    }
    
    .phuyu-actions {
        flex-direction: column;
        align-items: center;
    }
    
    .phuyu-btn {
        width: 100%;
        max-width: 300px;
        justify-content: center;
    }
}

@media (max-width: 576px) {
    .phuyu-card-body {
        padding: 20px;
    }
    
    .phuyu-company-name {
        font-size: 24px;
    }
    
    .phuyu-status-badges {
        flex-direction: column;
    }
}
</style>

<div id="phuyu_datos" class="phuyu_datos-container">
    <div class="phuyu-card">
        <div class="phuyu-card-body">
            <!-- Header con logo e información básica -->
            <div class="phuyu-header">
                <img class="phuyu-logo" src="<?php echo base_url();?>public/img/empresa/<?php echo $info[0]['foto']?>" alt="Logo Empresa">
                <div class="phuyu-company-info">
                    <input type="hidden" id="codempresa" value="<?php echo $_SESSION["phuyu_codempresa"];?>">
                    <h2 class="phuyu-company-name"><?php echo $info[0]["nombrecomercial"];?></h2>
                    <div class="phuyu-ruc">RUC: <?php echo $info[0]["documento"];?></div>
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
                    <h3 class="phuyu-section-title">DATOS DE FACTURACIÓN</h3>
                    <ul class="phuyu-facturacion-list">
                        <li><i class="fas fa-user"></i> USUARIO SOL: <?php echo $service[0]["usuariosol"];?></li>
                        <li><i class="fas fa-key"></i> CLAVE SOL: <?php echo $service[0]["clavesol"];?></li>
                        <li><i class="fas fa-paper-plane"></i> EMAIL ENVIO: <?php echo $service[0]["envioemail"];?></li>
                        <li><i class="fas fa-lock"></i> EMAIL CLAVE: <?php echo $service[0]["claveemail"];?></li>
                        <li><i class="fas fa-certificate"></i> CLAVE CERTIFICADO: <?php echo $service[0]["certificado_clave"];?></li>
                    </ul>
                    <a class="phuyu-download-link" download="<?php echo $service[0]['certificado_pfx'];?>" href="<?php echo base_url();?>sunat/certificado/<?php echo $service[0]['certificado_pfx'];?>">
                        <i class="fas fa-cloud-download-alt"></i> DESCARGAR CERTIFICADO
                    </a>
                </div>
                
                <!-- Divisor -->
                <div class="phuyu-divider"></div>
                
                <!-- Columna derecha - Estado y configuración -->
                <div>
                    <div class="phuyu-status-section">
                        <h4 class="phuyu-status-title">ARCHIVOS PEM: <?php echo $pen;?></h4>
                        <div class="phuyu-status-badges">
                            <?php if ($service[0]["sunatose"]==0) { ?>
                                <span class="phuyu-badge phuyu-badge-success">SERVICIO: SUNAT</span>
                            <?php } else { ?>
                                <span class="phuyu-badge phuyu-badge-success">SERVICIO: OSE</span>
                            <?php } ?>
                            
                            <?php if ($service[0]["serviceweb"]==0) { ?>
                                <span class="phuyu-badge phuyu-badge-primary">ESTADO: PRODUCCIÓN</span>
                            <?php } else { ?>
                                <span class="phuyu-badge phuyu-badge-primary">ESTADO: BETA HOMOLOGACIÓN</span>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Acciones -->
            <div class="phuyu-actions">
                <button type="button" class="phuyu-btn phuyu-btn-success" v-on:click="phuyu_editar()">
                    <i class="fas fa-edit"></i> CONFIGURAR FACTURACIÓN ELECTRÓNICA
                </button>
                <button type="button" class="phuyu-btn phuyu-btn-warning" v-on:click="phuyu_copia()">
                    <i class="fas fa-database"></i> GENERAR COPIA DE SEGURIDAD
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