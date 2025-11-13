<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket de Venta</title>
    <style>
        /* RESET Y ESTILOS BASE */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.1;
        }
        
        /* CENTRADO PERFECTO */
        html, body {
            width: 100%;
            min-height: 100vh;
        }
        
        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            background: #f0f0f0;
            padding: 20px 0;
        }
        
        /* CONTENEDOR DEL TICKET */
        .ticket-container {
            width: 80mm;
            max-width: 80mm;
            background: white;
            padding: 3mm;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: 1px solid #ddd;
        }
        
        /* ALINEACIONES */
        .center { text-align: center; }
        .left { text-align: left; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        
        /* TAMAÑOS DE TEXTO */
        .text-sm { font-size: 10px; }
        .text-xs { font-size: 9px; }
        .text-xxs { font-size: 8px; }
        .text-micro { font-size: 7px; }
        
        /* MÁRGENES */
        .my-1 { margin: 1mm 0; }
        .my-2 { margin: 2mm 0; }
        .mt-1 { margin-top: 1mm; }
        .mt-2 { margin-top: 2mm; }
        .mb-1 { margin-bottom: 1mm; }
        .mb-2 { margin-bottom: 2mm; }
        
        /* LÍNEAS SEPARADORAS */
        .separator {
            border-top: 1px dashed #000;
            margin: 2mm 0;
            text-align: center;
        }
        
        .separator-thick {
            border-top: 2px solid #000;
            margin: 2mm 0;
        }
        
        /* TABLAS */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .products-table td {
            padding: 1px 0;
            vertical-align: top;
        }
        
        .totals-table td {
            padding: 1px 0;
        }
        
        /* COLUMNAS PRODUCTOS */
        .col-qty { width: 18%; }
        .col-product { width: 42%; }
        .col-price { width: 20%; text-align: right; }
        .col-total { width: 20%; text-align: right; }
        
        /* LOGOS */
        .logo-main {
            height: 70px;
            max-width: 100%;
            display: block;
            margin: 0 auto;
        }
        
        .logo-empresa {
            width: 45px;
            height: 45px;
            object-fit: contain;
            display: block;
            margin: 0 auto;
        }
        
        .logo-placeholder {
            width: 45px;
            height: 45px;
            background: #f8f8f8;
            border: 1px dashed #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            font-size: 7px;
            text-align: center;
            line-height: 1.1;
        }
        
        /* FOOTER PHUYUSYSTEM */
        .phuyu-footer {
            background: #f8f9fa;
            border-top: 2px solid #007bff;
            padding: 3mm 2mm;
            margin-top: 4mm;
            text-align: center;
            border-radius: 3px;
        }
        
        .phuyu-logo {
            font-weight: bold;
            color: #007bff;
            font-size: 11px;
            letter-spacing: 0.5px;
        }
        
        .phuyu-url {
            color: #666;
            font-size: 8px;
            margin-top: 1mm;
        }
        
        .phuyu-version {
            color: #888;
            font-size: 7px;
            margin-top: 1mm;
            font-style: italic;
        }
        
        /* BOTÓN IMPRIMIR */
        .print-btn {
            padding: 12px 24px;
            font-size: 16px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 15px 0;
            transition: background 0.3s;
        }
        
        .print-btn:hover {
            background: #0056b3;
        }
        
        /* ESTILOS DE IMPRESIÓN */
        @media print {
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
                display: block !important;
            }
            
            .ticket-container {
                box-shadow: none !important;
                border: none !important;
                padding: 2mm !important;
                margin: 0 auto !important;
            }
            
            .no-print {
                display: none !important;
            }
            
            .phuyu-footer {
                background: #fff !important;
                border: 1px solid #ddd !important;
            }
            
            @page {
                margin: 0;
                size: 80mm auto;
            }
        }
        
        /* MEJORAS VISUALES */
        .document-number {
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        
        .client-info div {
            margin: 1px 0;
        }
        
        .grand-total {
            border-top: 1px dashed #000;
            padding-top: 2px;
        }
        
        .cut-line {
            border-top: 1px dashed #666;
            margin: 3mm 0 1mm;
            text-align: center;
            position: relative;
        }
        
        .cut-line::after {
            content: "✄";
            position: absolute;
            top: -7px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            padding: 0 5px;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <!-- CONTENEDOR PRINCIPAL CENTRADO -->
    <div class="ticket-container">
        <!-- LOGO PRINCIPAL -->
        <div class="center">
            <img src="<?php echo $direccionlogo; ?>" class="logo-main">
        </div>
        
        <!-- LOGO EMPRESA -->
        <div class="center my-0">
            <?php if ($logoEmpresa != ''): ?>
                <img src="<?php echo base_url() . 'public/img/' . $logoEmpresa; ?>" class="logo-empresa">
            <?php else: ?>
                <div class="logo-placeholder">
                    LOGO<br>EMPRESA
                </div>
            <?php endif; ?>
        </div>
        
        <!-- INFORMACIÓN EMPRESA -->
        <div class="center bold my-1"><?php echo $nombre; ?></div>
        <div class="center text-sm"><?php echo $sucursal[0]['direccion']; ?></div>
        <div class="center text-sm">RUC: <?php echo $empresa[0]['documento']; ?></div>
        
        <!-- INFORMACIÓN VENDEDOR -->
        <div class="center text-xs my-1">VENDEDOR: <?php echo $vendedor[0]['razonsocial']; ?></div>
        <div class="center text-xs">USUARIO: <?php echo $_SESSION['phuyu_usuario']; ?></div>
        <div class="center text-xs">TEL: <?php echo $vendedor[0]['telefono']; ?></div>
        
        <div class="separator"></div>
        
        <!-- FECHAS Y HORA -->
        <div class="text-sm">
            <div style="float: left;">EMISIÓN: <?php echo $fechavencimiento; ?></div>
            <div style="float: right;"><?php echo date('H:i:s'); ?></div>
            <div style="clear: both;"></div>
        </div>
        <div class="text-sm">VENCIMIENTO: <?php echo $fechavencimiento; ?></div>
        
        <!-- DOCUMENTO -->
        <div class="center document-number my-1">
            <?php echo $venta[0]['comprobante']; ?><br>
            <?php echo $venta[0]['seriecomprobante'] . '-' . $venta[0]['nrocomprobante']; ?>
        </div>
        
        <!-- INFORMACIÓN CLIENTE -->
        <div class="client-info text-sm my-1">
            <div><span class="bold">CLIENTE:</span> <?php echo $venta[0]['cliente']; ?></div>
            <div><span class="bold">DIRECCIÓN:</span> <?php echo $venta[0]['direccion']; ?></div>
            <div><span class="bold">DOCUMENTO:</span> <?php echo $venta[0]['documento']; ?></div>
            
            <?php if ($_SESSION["phuyu_rubro"] == 1): ?>
                <div><span class="bold">NRO PLACA:</span> <?php echo $venta[0]['nroplaca']; ?></div>
            <?php endif; ?>
            
            <div><span class="bold">CONDICIÓN:</span> 
                <?php echo ($venta[0]["condicionpago"] == 2) ? 'AL CRÉDITO' : 'AL CONTADO'; ?>
            </div>
        </div>
        
        <div class="separator"></div>
        
        <!-- DETALLE DE PRODUCTOS -->
        <table class="products-table">
            <thead>
                <tr>
                    <th class="col-qty left text-xs">CANT</th>
                    <th class="col-product left text-xs">PRODUCTO</th>
                    <th class="col-price text-xs">P.U.</th>
                    <th class="col-total text-xs">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalle as $key => $value): ?>
                <tr>
                    <td class="col-qty left text-xs">
                        <?php echo round($value['cantidad'], 2); ?> <?php echo substr($value['unidad'], 0, 3); ?>
                    </td>
                    <td class="col-product left text-xs"><?php echo $value['producto'] . ' ' . $value['descripcion']; ?></td>
                    <td class="col-price text-xs"><?php echo number_format($value['preciounitario'], 2); ?></td>
                    <td class="col-total text-xs"><?php echo number_format($value['subtotal'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div class="separator"></div>
        
        <!-- TOTALES -->
        <table class="totals-table">
            <?php if ($venta[0]["codcomprobantetipo"] == 10 || $venta[0]["codcomprobantetipo"] == 12): ?>
                <tr>
                    <td class="right text-sm">OP GRAVADAS S/:</td>
                    <td class="right text-sm" style="width: 35%;"><?php echo number_format($totales[0]['gravado'] - $venta[0]['igv'], 2); ?></td>
                </tr>
                <tr>
                    <td class="right text-sm">OP INAFECTAS S/:</td>
                    <td class="right text-sm"><?php echo number_format($totales[0]['inafecto'], 2); ?></td>
                </tr>
                <tr>
                    <td class="right text-sm">OP EXONERADAS S/:</td>
                    <td class="right text-sm"><?php echo number_format($totales[0]['exonerado'], 2); ?></td>
                </tr>
                <tr>
                    <td class="right text-sm">OP GRATUITAS S/:</td>
                    <td class="right text-sm"><?php echo number_format($totales[0]['gratuito'], 2); ?></td>
                </tr>
            <?php else: ?>
                <tr>
                    <td class="right text-sm">SUB TOTAL S/:</td>
                    <td class="right text-sm" style="width: 35%;"><?php echo number_format($venta[0]['valorventa'], 2); ?></td>
                </tr>
            <?php endif; ?>
            
            <tr>
                <td class="right text-sm">DESCUENTO S/:</td>
                    <td class="right text-sm"><?php echo number_format($venta[0]['descglobal'], 2); ?></td>
                </tr>
                <tr>
                    <td class="right text-sm">IGV S/:</td>
                    <td class="right text-sm"><?php echo number_format($venta[0]['igv'], 2); ?></td>
                </tr>
                <tr class="grand-total">
                    <td class="right bold">TOTAL S/:</td>
                    <td class="right bold"><?php echo number_format($venta[0]['importe'], 2); ?></td>
                </tr>
                
                <?php if($efectivo == 1): ?>
                    <tr>
                        <td class="right text-sm">EFECTIVO S/:</td>
                        <td class="right text-sm"><?php echo number_format($detallemovimiento[0]['importeentregado'], 2); ?></td>
                    </tr>
                    <tr>
                        <td class="right text-sm">VUELTO S/:</td>
                        <td class="right text-sm"><?php echo number_format($detallemovimiento[0]['vuelto'], 2); ?></td>
                    </tr>
                <?php endif; ?>
            </table>
            
            <!-- IMPORTE EN LETRAS -->
            <div class="text-xs my-1"><?php echo $texto_importe; ?></div>
            
            <div class="separator"></div>
            
            <!-- CÓDIGO QR -->
            <?php if ($venta[0]["codcomprobantetipo"] == 10 || $venta[0]["codcomprobantetipo"] == 12): ?>
                <div class="center">
                    <img src="<?php echo base_url(); ?>sunat/webphuyu/qrcode.png" style="height:60px; max-width: 100%;">
                </div>
                <div class="center text-xs my-1">
                    CONSULTA TU COMPROBANTE EN<br>
                    <?php echo $sucursal[0]['urlconsultacomprobantes']; ?>
                </div>
            <?php endif; ?>
            
            <!-- LEYENDA AMAZONÍA -->
            <?php
            if ($venta[0]['conleyendaamazonia'] == 1) {
                echo '<div class="center text-xxs my-1">';
                if ($formato[0]['tipoconleyendaamazonia'] == 1) {
                    echo $sucursal[0]['codleyendapamazonia'] . ' - ' . $sucursal[0]['leyendapamazonia'];
                } elseif ($formato[0]['tipoconleyendaamazonia'] == 2) {
                    echo $sucursal[0]['codleyendasamazonia'] . ' - ' . $sucursal[0]['leysendasamazonia'];
                } else {
                    echo $sucursal[0]['codleyendapamazonia'] . ' - ' . $sucursal[0]['leyendapamazonia'] . '<br>' . 
                         $sucursal[0]['codleyendasamazonia'] . ' - ' . $sucursal[0]['leyendasamazonia'];
                }
                echo '</div>';
            }
            ?>
            
            <!-- MENSAJES FINALES -->
            <div class="center bold my-2">¡GRACIAS POR SU COMPRA!</div>
            <div class="center text-xxs">NO SE ACEPTAN CAMBIOS NI DEVOLUCIONES</div>
            
            <!-- FOOTER PHUYUSYSTEM -->
            <div class="phuyu-footer">
                <div class="phuyu-logo">SISTEMA FACTURADOR PHUYUSYSTEM</div>
                <div class="phuyu-url">www.phuyusystem.com</div>
                <div class="phuyu-version">Software de gestión empresarial</div>
            </div>
            
            <!-- LÍNEA DE CORTE -->
            <div class="cut-line"></div>
            <!-- <div class="center text-xxs">CORTAR POR LA LÍNEA</div> -->
        </div>
        
        <!-- BOTÓN IMPRIMIR -->
        <div class="no-print">
            <button class="print-btn" onclick="imprimirTicket()">
                🖨️ IMPRIMIR TICKET
            </button>
            <div class="center text-sm" style="color: #666;">
                Ticket optimizado para impresora térmica de 80mm
            </div>
        </div>
        
        <script>
            function imprimirTicket() {
                window.print();
            }
            
            // Auto-impresión al cargar
            window.addEventListener('load', function() {
                setTimeout(function() {
                    window.print();
                }, 1000);
            });
            
            // Mejorar estilos de impresión
            const printStyles = `
                @media print {
                    body * {
                        visibility: hidden;
                    }
                    .ticket-container, .ticket-container * {
                        visibility: visible;
                    }
                    .ticket-container {
                        position: absolute;
                        left: 50%;
                        transform: translateX(-50%);
                        top: 0;
                    }
                }
            `;
            
            const styleSheet = document.createElement("style");
            styleSheet.type = "text/css";
            styleSheet.innerText = printStyles;
            document.head.appendChild(styleSheet);
        </script>
    </body>
    </html>