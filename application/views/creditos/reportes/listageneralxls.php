<?php
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="phuyu-Peru-ListaCreditos.xls"');
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
            <b><?php echo utf8_decode("LISTA GENERALES DE CREDITOS POR COBRAR - ".$tipo);?></b>
        </th>
    </tr>
    <tr><th></th></tr>
    <tr>
        <th>N°</th>
        <th>DOCUMENTO</th>
        <th>RAZON SOCIAL</th>
        <th>F. CREDITO</th>
        <th>F. VENCE</th>
        <th>COMPROBANTE</th>
        <th>IMPORTE</th>
        <th>INTERES</th>
        <th>TOTAL</th>
        <th>SALDO</th>
    </tr>
    <?php $item = 1;
        foreach ($info as $key => $value) { ?>
            <tr>
                <td><?php echo "0".$item;?></td>
                <td><?php echo $value["documento"];?></td>
                <td><?php echo $value["razonsocial"];?></td>
                <td><?php echo $value["fechacredito"];?></td>
                <td><?php echo $value["fechavencimiento"];?></td>
                <td><?php echo $value["comprobante"];?></td>
                <td><?php echo number_format($value["importe"],2);?></td>
                <td><?php echo number_format($value["interes"],2);?></td>
                <td><?php echo number_format($value["total"],2);?></td>
                <td><?php echo number_format($value["saldo"],2);?></td>
            </tr>
        <?php $item++; }
    ?>
            <tr>
                <td colspan="6" align="right">TOTALES</td>
                <td><?php echo $total["totalimporte"];?></td>
                <td><?php echo $total["totalinteres"];?></td>
                <td><?php echo $total["totaltotal"];?></td>
                <td><?php echo $total["totalsaldo"];?></td> 
            </tr>
</table>