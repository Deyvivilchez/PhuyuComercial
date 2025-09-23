<?php
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="phuyu-Peru-ListaCobranza.xls"');
header('Cache-Control: max-age=0');
?>

<table border="1">
    <tr>
        <th colspan="10">
            <b><?php echo utf8_decode("SUCURSAL: ".$_SESSION["phuyu_sucursal"]);?></b>
        </th>
    </tr>
    <tr>
        <th colspan="10">
            <b><?php echo utf8_decode("LISTA GENERALES DE COBRANZA - ".$tipo." DEL ".$fechadesde.' AL '.$fechahasta);?></b>
        </th>
    </tr>
    <tr><th></th></tr>
    <tr>
        <th>N°</th>
        <th colspan="3">RAZON SOCIAL</th>
        <th>F. CREDITO</th>
        <th>F. VENCE</th>
        <th>COMPROBANTE</th>
        <th>TOTAL</th>
        <th>F. PAGO</th>
        <th>IMPORTE</th>
    </tr>
    <?php $item = 1;
        foreach ($info as $key => $value) { ?>
            <tr>
                <td><?php echo "0".$item;?></td>
                <td colspan="3"><?php echo $value["cliente"];?></td>
                <td><?php echo $value["fechacredito"];?></td>
                <td><?php echo $value["fechavencimientocredito"];?></td>
                <td><?php echo $value["seriecomprobante"].'-'.$value["nrocomprobante"];?></td>
                <td><?php echo number_format($value["totalcredito"],2);?></td>
                <td><?php echo $value["fechamovimiento"];?></td>
                <td><?php echo number_format($value["importe"],2);?></td>
            </tr>
        <?php $item++; }
    ?>
            <tr>
                <td colspan="7" align="right">TOTALES</td>
                <td><?php echo $total["totaltotal"];?></td>
                <td></td>
                <td><?php echo $total["totalpago"];?></td>
            </tr>
</table>