<?php
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="ReporteMovimientoAlmacen' . date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');
?>

<table border="1">
    <tr>
        <th colspan="14"> 
            <b><?php echo utf8_decode($_SESSION["phuyu_empresa"]);?></b>
        </th>
    </tr>
    <tr>
        <th colspan="14">
            <b style="font-size:9px"><?php echo $cabecera_texto;?></b>
        </th>
    </tr>
    <tr>
        <th colspan="3">RUC: <?php echo $empresa[0]["documento"];?></th>
        <th colspan="7"></th>
        <th colspan="3"> MONEDA: SOLES </th>
    </tr>

    <tr>
        <td rowspan="2">ID</td>
        <td rowspan="2">FECHA</td>
        <td rowspan="2">T.DOC</td>
        <td rowspan="2"><?php echo utf8_decode("N°DOC")?></td>
        <td rowspan="2">DOC. IDEN</td>
        <td rowspan="2">RAZON SOCIAL</td>
        <td rowspan="2">VALOR VENTA</td>
        <td rowspan="2">IGV</td>
        <td rowspan="2">ICBPER</td>
        <td rowspan="2">TOTAL</td>
        <td rowspan="2">IMPORTE N.C</td>
        <td colspan="3">DOCUMENTO ORIGINAL QUE SE MODIFICA</td>
    </tr>
    <tr>
        <td>FECHA</td>
        <td>T.DOC</td>
        <td><?php echo utf8_decode("N°DOC")?></td>
    </tr>

    <?php 
        $valorventa = 0; $igv = 0; $icbper = 0; $total = 0;
        foreach ($lista as $val) { 
            $color = "";
            if ((int)$val["estado"]==0) {
                $color = "color:red !important";
            } ?>
            
            <tr style="<?php echo $color;?>">
                <td><?php echo $val["codkardex"];?></td>
                <td><?php echo $val["fechacomprobante"];?></td>
                <td><?php echo $val["seriecomprobante"].'-'.$val["nrocomprobante"];?></td>
                <td><?php echo $val["movimiento"];?></td>
                <?php

                    if ((int)$val["estado"]==0) {
                        echo '<td>ANULADO</td>';

                        echo '<td>'.number_format(0.00,2).'</td>';
                        echo '<td>'.number_format(0.00,2).'</td>';
                        echo '<td>'.number_format(0.00,2).'</td>';
                        echo '<td>'.number_format(0.00,2).'</td>';
                    }else{
                        $valorventa = $valorventa + $val["valorventa"]; 
                        $igv = $igv + $val["igv"]; 
                        $icbper = $icbper + $val["icbper"]; 
                        $total = $total + $val["importe"];

                        echo '<td>'.$val["documento"].' - '.$val["razonsocial"].'</td>';

                        echo '<td>'.number_format($val["valorventa"],2).'</td>';
                        echo '<td>'.number_format($val["igv"],2).'</td>';
                        echo '<td>'.number_format($val["icbper"],2).'</td>';
                        echo '<td>'.number_format($val["importe"],2).'</td>';
                        echo '<td>'.number_format(0.00,2).'</td>';
                    }
                ?>

                <td><?php echo $val["fecharef"];?> </td>
                <td><?php echo $val["tiporef"];?> </td>
                <td><?php echo $val["documentoref"];?> </td>
            </tr>
        <?php }
    ?>

    <tr>
        <td style="color:#d9534f;text-align:right" colspan="5"> <b><?php echo utf8_decode($empresa[0]["direccion"]);?></b> </td>
        <td style="color:#d9534f"><?php echo number_format($valorventa,2)?></td>
        <td style="color:#d9534f"><?php echo number_format($igv,2)?></td>
        <td style="color:#d9534f"><?php echo number_format($icbper,2)?></td>
        <td style="color:#d9534f"><?php echo number_format($total,2)?></td>
        <td style="color:#d9534f"><?php echo number_format(0.00,2)?></td>
        <td colspan="3"> </td>
    </tr>
    <tr>
        <td style="color:#d9534f;text-align:right" colspan="5"> <b>TOTAL GENERAL S/:</b> </td>
        <td style="color:#d9534f"><?php echo number_format($valorventa,2)?></td>
        <td style="color:#d9534f"><?php echo number_format($igv,2)?></td>
        <td style="color:#d9534f"><?php echo number_format($icbper,2)?></td>
        <td style="color:#d9534f"><?php echo number_format($total,2)?></td>
        <td style="color:#d9534f"><?php echo number_format(0.00,2)?></td>
        <td colspan="3"> </td>
    </tr>
</table>