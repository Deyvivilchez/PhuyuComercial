<?php
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=reporte_movimientos_detallado_{$desde}_al_{$hasta}.xls");
header("Cache-Control: max-age=0");
?>

<style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        font-size: 13px;
    }

    th, td {
        border: 1px solid #ccc;
        padding: 8px;
        text-align: center;
    }

    th {
        background-color: #003366;
        color: white;
        font-weight: bold;
    }

    .header-title {
        font-size: 16px;
        font-weight: bold;
        background-color: #007BFF;
        color: #fff;
        text-align: center;
        padding: 12px;
    }

    .subtitulo {
        background-color: #f5f5f5;
        text-align: center;
        font-size: 13px;
        font-weight: bold;
        padding: 8px;
    }

    .total-row {
        background-color: #e9ecef;
        font-weight: bold;
    }

    .right {
        text-align: right;
    }
</style>

<table>
    <tr>
        <td colspan="11" class="header-title">
            REPORTE DE MOVIMIENTOS DETALLADO
        </td>
    </tr>
    <tr>
        <td colspan="11" class="subtitulo">
            Desde: <?php echo $desde; ?> &nbsp;&nbsp;&nbsp;&nbsp; Hasta: <?php echo $hasta; ?>
        </td>
    </tr>
    <tr>
        <th>#</th>
        <th>Fecha</th>
        <th>Comprobante</th>
        <th>Comprobante REF</th>
        <th>Razón Social</th>
        <th>Concepto</th>
        <th>Tipo de Pago</th>
        <th>Importe Pagado</th>
        <th>Importe Entregado</th>
        <th>Vuelto</th>
        <th>Total Movimiento</th>
        
    </tr>
    <?php
    $i = 1;
    $suma_pagado = 0;
    $suma_entregado = 0;
    $suma_vuelto = 0;
    $suma_total = 0;

    foreach ($movimientos as $m) {
        $suma_pagado += $m['importe_pago'];
        $suma_entregado += $m['importe_entregado'];
        $suma_vuelto += $m['vuelto'];
        $suma_total += $m['importe_pago'];;

        echo "<tr>";
        echo "<td>{$i}</td>";
        echo "<td>{$m['fechamovimiento']}</td>";
        echo "<td>{$m['seriecomprobante']}-{$m['nrocomprobante']}</td>";
         echo "<td>{$m['comprobante_referencia']}</td>";
        echo "<td>".utf8_decode($m['razonsocial'])."</td>";
        echo "<td>".utf8_decode($m['concepto_caja'])."</td>";
        echo "<td>".utf8_decode($m['tipopago'])."</td>";
        echo "<td class='right'>".number_format($m['importe_pago'], 2)."</td>";
        echo "<td class='right'>".number_format($m['importe_entregado'], 2)."</td>";
        echo "<td class='right'>".number_format($m['vuelto'], 2)."</td>";
        echo "<td class='right'><b>".number_format($m['total_movimiento'], 2)."</b></td>";
        echo "</tr>";
        $i++;
    }
    ?>
    <tr class="total-row">
        <td colspan="7" class="right">TOTAL GENERAL</td>
        <td class="right"><?php echo number_format($suma_pagado, 2); ?></td>
        <td class="right"><?php echo number_format($suma_entregado, 2); ?></td>
        <td class="right"><?php echo number_format($suma_vuelto, 2); ?></td>
        <td class="right"><?php echo number_format($suma_total, 2); ?></td>
    </tr>
</table>
