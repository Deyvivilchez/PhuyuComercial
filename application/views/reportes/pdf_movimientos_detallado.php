<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Movimientos</title>
</head>

<body
    style="font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif; font-size: 8pt; margin: 0; padding: 15px; color: #2c3e50; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh;">
    <div style="width: 100%; max-width: 100%;">
        <!-- ENCABEZADO SUPER ELEGANTE CON LOGO REAL -->
        <div
            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px 30px; margin-bottom: 25px; border-radius: 15px; box-shadow: 0 10px 30px
             rgba(0,0,0,0.2); position: relative; overflow: hidden; border: 1px solid rgba(255,255,255,0.2);">
            <div
                style="position: absolute; top: -50%; right: -50%; width: 100%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 20px 20px; transform: rotate(25deg);">
            </div>

            <div
                style="display: flex; align-items: center; justify-content: space-between; position: relative; z-index: 2;">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <div
                        style="width: 80px; height: 80px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; border: 2px solid rgba(255,255,255,0.3); backdrop-filter: blur(10px); overflow: hidden; padding: 5px;">
                        <?php
                        
                        echo $logoEmpresa != '' ? "<img src='" . base_url() . 'public/img/' . $logoEmpresa . "' alt='Logo' style='max-width: 100%; max-height: 100%; object-fit: contain; border-radius: 8px;'>" : "<div style='width: 100%; height: 100%; background: rgba(255,255,255,0.3); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 9pt; text-align: center;'>LOGO<br>EMPRESA</div>";
                        ?>
                    </div>
                    <div style="color: white;">
                        <div
                            style="font-size: 20pt; font-weight: 800; margin-bottom: 5px; letter-spacing: 0.5px; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                            <?php echo $nombreEmpresa; ?>.</div>
                        <div style="font-size: 9pt; font-weight: 300; opacity: 0.9; font-style: italic;">Phuyu System
                        </div>
                    </div>
                </div>

                <div style="text-align: right; color: white;">
                    <div
                        style="font-size: 16pt; font-weight: 700; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px; text-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                        Reporte Financiero</div>
                    <div
                        style="font-size: 10pt; font-weight: 500; background: rgba(255,255,255,0.2); padding: 8px 15px; border-radius: 20px; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.3);">
                        📅 <?php echo $desde; ?> - <?php echo $hasta; ?></div>
                    <div style="font-size: 8pt; margin-top: 5px; opacity: 0.8;">Generado: <?php echo date('d/m/Y H:i:s'); ?></div>
                </div>
            </div>
        </div>

        <!-- TABLA ELEGANTE -->
        <div
            style="margin: 20px 0; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 25px rgba(0,0,0,0.15); background: white;">
            <table style="width: 100%; border-collapse: collapse; font-size: 7.5pt;">
                <thead style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);">
                    <tr>
                        <th
                            style="color: #ffffff; font-weight: 600; padding: 12px 6px; border: none; text-align: center; text-transform: uppercase; letter-spacing: 0.8px; font-size: 7pt; position: relative; width: 30px;">
                            #
                        </th>
                        <th
                            style="color: #ffffff; font-weight: 600; padding: 12px 6px; border: none; text-align: center; text-transform: uppercase; letter-spacing: 0.8px; font-size: 7pt; position: relative; width: 60px;">
                            Fecha
                        </th>
                        <th
                            style="color: #ffffff; font-weight: 600; padding: 12px 6px; border: none; text-align: center; text-transform: uppercase; letter-spacing: 0.8px; font-size: 7pt; position: relative; width: 80px;">
                            Comp movimiento
                        </th>
                         <th
                            style="color: #ffffff; font-weight: 600; padding: 12px 6px; border: none; text-align: center; text-transform: uppercase; letter-spacing: 0.8px; font-size: 7pt; position: relative; width: 80px;">
                            Comprobante ref
                        </th>
                        <th
                            style="color: #ffffff; font-weight: 600; padding: 12px 6px; border: none; text-align: left; text-transform: uppercase; letter-spacing: 0.8px; font-size: 7pt; position: relative; width: 170px;">
                            Cliente
                        </th>
                        <th
                            style="color: #ffffff; font-weight: 600; padding: 12px 6px; border: none; text-align: left; text-transform: uppercase; letter-spacing: 0.8px; font-size: 7pt; position: relative; width: 140px;">
                            Concepto
                        </th>
                        <th
                            style="color: #ffffff; font-weight: 600; padding: 12px 6px; border: none; text-align: center; text-transform: uppercase; letter-spacing: 0.8px; font-size: 7pt; position: relative; width: 70px;">
                            Forma Pago
                        </th>
                        <th
                            style="color: #ffffff; font-weight: 600; padding: 12px 6px; border: none; text-align: center; text-transform: uppercase; letter-spacing: 0.8px; font-size: 7pt; position: relative; width: 75px;">
                            Pagado
                        </th>
                        <th
                            style="color: #ffffff; font-weight: 600; padding: 12px 6px; border: none; text-align: center; text-transform: uppercase; letter-spacing: 0.8px; font-size: 7pt; position: relative; width: 75px;">
                            Entregado
                        </th>
                        <th
                            style="color: #ffffff; font-weight: 600; padding: 12px 6px; border: none; text-align: center; text-transform: uppercase; letter-spacing: 0.8px; font-size: 7pt; position: relative; width: 65px;">
                            Vuelto
                        </th>
                        <th
                            style="color: #ffffff; font-weight: 600; padding: 12px 6px; border: none; text-align: center; text-transform: uppercase; letter-spacing: 0.8px; font-size: 7pt; position: relative; width: 80px;">
                            Total
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    $totalPago = 0;
                    $totalEntregado = 0;
                    $totalVuelto = 0;
                    $totalMovimiento = 0;
                    
                    foreach ($movimientos as $m) {
                        $tipoPago = strtolower($m['tipopago']);
                        $badge_style = 'background: linear-gradient(135deg, #636e72 0%, #2d3436 100%); color: white;';
                        if (strpos($tipoPago, 'efectivo') !== false) {
                            $badge_style = 'background: linear-gradient(135deg, #00b894 0%, #00a085 100%); color: white;';
                        } elseif (strpos($tipoPago, 'tarjeta') !== false) {
                            $badge_style = 'background: linear-gradient(135deg, #0984e3 0%, #086cc3 100%); color: white;';
                        } elseif (strpos($tipoPago, 'transferencia') !== false) {
                            $badge_style = 'background: linear-gradient(135deg, #a29bfe 0%, #6c5ce7 100%); color: white;';
                        }
                    
                        echo "<tr style='transition: all 0.3s ease;'>";
                        echo "<td style='padding: 8px 6px; border-bottom: 1px solid #ecf0f1; vertical-align: middle; text-align: center;'><strong>{$i}</strong></td>";
                        echo "<td style='padding: 8px 6px; border-bottom: 1px solid #ecf0f1; vertical-align: middle; text-align: center;'>{$m['fechamovimiento']}</td>";
                        echo "<td style='padding: 8px 6px; border-bottom: 1px solid #ecf0f1; vertical-align: middle; text-align: center;'><span style='font-family: \"SF Mono\", monospace; font-weight: 700; color: #2c3e50; letter-spacing: 0.5px; background: #f8f9fa; padding: 3px 6px; border-radius: 4px; border: 1px solid #e9ecef;'>{$m['seriecomprobante']}-{$m['nrocomprobante']}</span></td>";
                        echo "<td style='padding: 8px 6px; border-bottom: 1px solid #ecf0f1; vertical-align: middle; text-align: left; font-weight: 600; color: #34495e;'>{$m['comprobante_referencia']}</td>";
                        echo "<td style='padding: 8px 6px; border-bottom: 1px solid #ecf0f1; vertical-align: middle; text-align: left; font-weight: 600; color: #34495e;'>{$m['razonsocial']}</td>";

                        echo "<td style='padding: 8px 6px; border-bottom: 1px solid #ecf0f1; vertical-align: middle; text-align: left; color: #5d6d7e; font-style: italic;'>{$m['concepto_caja']}</td>";
                        echo "<td style='padding: 8px 6px; border-bottom: 1px solid #ecf0f1; vertical-align: middle; text-align: center;'><span style='display: inline-block; padding: 4px 10px; border-radius: 15px; font-size: 6.5pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); {$badge_style}'>{$m['tipopago']}</span></td>";
                        echo "<td style='padding: 8px 6px; border-bottom: 1px solid #ecf0f1; vertical-align: middle; text-align: right; font-family: \"SF Mono\", \"Monaco\", \"Consolas\", monospace; font-weight: 700; color: #2c3e50;'><span style='font-size: 6pt; color: #7f8c8d; margin-right: 2px; font-weight: 600;'>S/</span>" . number_format($m['importe_pago'], 2) . '</td>';
                        echo "<td style='padding: 8px 6px; border-bottom: 1px solid #ecf0f1; vertical-align: middle; text-align: right; font-family: \"SF Mono\", \"Monaco\", \"Consolas\", monospace; font-weight: 500;'><span style='font-size: 6pt; color: #7f8c8d; margin-right: 2px; font-weight: 600;'>S/</span>" . number_format($m['importe_entregado'], 2) . '</td>';
                        echo "<td style='padding: 8px 6px; border-bottom: 1px solid #ecf0f1; vertical-align: middle; text-align: right; font-family: \"SF Mono\", \"Monaco\", \"Consolas\", monospace; font-weight: 500;'><span style='font-size: 6pt; color: #7f8c8d; margin-right: 2px; font-weight: 600;'>S/</span>" . number_format($m['vuelto'], 2) . '</td>';
                        echo "<td style='padding: 8px 6px; border-bottom: 1px solid #ecf0f1; vertical-align: middle; text-align: right; font-family: \"SF Mono\", \"Monaco\", \"Consolas\", monospace; font-weight: 700; color: #2c3e50;'><strong><span style='font-size: 6pt; color: #7f8c8d; margin-right: 2px; font-weight: 600;'>S/</span>" . number_format($m['importe_pago'], 2) . '</strong></td>';
                        echo '</tr>';
                    
                        $totalPago += $m['importe_pago'];
                        $totalEntregado += $m['importe_entregado'];
                        $totalVuelto += $m['vuelto'];
                        $totalMovimiento += $m['importe_pago'];
                        $i++;
                    }
                    ?>

                    <!-- FILA DE TOTALES ELEGANTE -->
                    <tr
                        style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%) !important; color: #ffffff; font-weight: 800;">
                        <td colspan="7"
                            style="padding: 12px 6px; border: none; font-size: 8pt; text-shadow: 0 1px 2px rgba(0,0,0,0.3); text-align: right; padding-right: 20px;">
                            <strong>TOTALES GENERALES</strong>
                        </td>
                        <td
                            style="padding: 12px 6px; border: none; font-size: 8pt; text-shadow: 0 1px 2px rgba(0,0,0,0.3); text-align: right; font-family: 'SF Mono', 'Monaco', 'Consolas', monospace;">
                            <strong>S/ <?php echo number_format($totalPago, 2); ?></strong></td>
                        <td
                            style="padding: 12px 6px; border: none; font-size: 8pt; text-shadow: 0 1px 2px rgba(0,0,0,0.3); text-align: right; font-family: 'SF Mono', 'Monaco', 'Consolas', monospace;">
                            <strong>S/ <?php echo number_format($totalEntregado, 2); ?></strong></td>
                        <td
                            style="padding: 12px 6px; border: none; font-size: 8pt; text-shadow: 0 1px 2px rgba(0,0,0,0.3); text-align: right; font-family: 'SF Mono', 'Monaco', 'Consolas', monospace;">
                            <strong>S/ <?php echo number_format($totalVuelto, 2); ?></strong></td>
                        <td
                            style="padding: 12px 6px; border: none; font-size: 8pt; text-shadow: 0 1px 2px rgba(0,0,0,0.3); text-align: right; font-family: 'SF Mono', 'Monaco', 'Consolas', monospace;">
                            <strong>S/ <?php echo number_format($totalMovimiento, 2); ?></strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- FOOTER CON ESTADÍSTICAS -->
      
    </div>
</body>

</html>
