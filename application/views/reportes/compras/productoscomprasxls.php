<?php
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="ProductosComprados' . date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');
?>

<style type="text/css">
    .titulo{
        color:#ffffff;
        background:#17365d;
        font-size:15px;
        font-weight:700;
        text-align:center;
        border:1px solid #17365d;
    }
    .subtitulo{
        color:#1f4e78;
        background:#d9eaf7;
        font-size:11px;
        font-weight:700;
        text-align:center;
        border:1px solid #9dc3e6;
    }
    .cabecera{
        color:#ffffff;
        background:#1f4e78;
        font-weight:700;
        text-align:center;
        border:1px solid #8ea9db;
    }
    .detalle{
        border:1px solid #d9e2f3;
    }
    .total{
        color:#c00000;
        background:#fff2cc;
        font-weight:700;
        border:1px solid #d6b656;
    }
</style>

<table border="1" style="font-size:11px">
    <tr>
        <th class="titulo" colspan="6"><?php echo utf8_decode($_SESSION["phuyu_empresa"]);?></th>
    </tr>
    <tr>
        <th class="subtitulo" colspan="6">
            LISTADO DE PRODUCTOS COMPRADOS POR <?php echo isset($titulo_fecha) ? $titulo_fecha : 'FECHA COMPROBANTE';?>
            DESDE <?php echo $fechadesde.' A '.$fechahasta;?>
        </th>
    </tr>
    <tr><td colspan="6"></td></tr>
    <tr>
        <td class="cabecera">N°</td>
        <td class="cabecera">CODIGO PRODUCTO</td>
        <td class="cabecera">PRODUCTO</td>
        <td class="cabecera">UNIDAD</td>
        <td class="cabecera">CANTIDAD</td>
        <td class="cabecera">IMPORTE</td>
    </tr>
    <?php
        $item = 0;
        $total_importe = 0;
        foreach ($lista as $value) {
            $item++;
            $total_importe += (double)$value["importe"];
    ?>
        <tr>
            <td class="detalle"><?php echo $item;?></td>
            <td class="detalle"><?php echo $value["codigo"];?></td>
            <td class="detalle"><?php echo utf8_decode($value["producto"]);?></td>
            <td class="detalle"><?php echo utf8_decode($value["unidad"]);?></td>
            <td class="detalle"><?php echo number_format($value["cantidad"],2);?></td>
            <td class="detalle"><?php echo number_format($value["importe"],2);?></td>
        </tr>
    <?php } ?>
    <tr>
        <td class="total" colspan="5" style="text-align:right">TOTAL IMPORTE</td>
        <td class="total"><?php echo number_format($total_importe,2);?></td>
    </tr>
</table>
