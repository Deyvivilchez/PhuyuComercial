<?php
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Stock Valorizado Productos-'.date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');
?>

<table border="1">
    <tr>
        <th colspan="7"> 
            <b><?php echo utf8_decode($titulo);?></b>
        </th>
    </tr>

    <tr>
        <th>N°</th>
        <th>CODIGO</th>
        <th>DESCRIPCION PRODUCTO</th>
        <th>U.MEDIDA</th>
        <th>STOCK</th>
        <th>PRECIO UNITARIO</th>
        <th>TOTAL</th>
    </tr>
    <?php $item = 0;
        foreach ($lineas as $key => $value) { 
            if(count($value["lista"])>0){ ?>
                <tr>
                    <th colspan="7"> <b>LINEA DE PRODUCTO: <?php echo utf8_decode($value["descripcion"]);?></b> </th>
                </tr>
            <?php } ?>
                
            <?php
                foreach ($value["lista"] as $val) { $item = $item + 1; ?>
                    <tr>
                        <td><?php echo $val["codproducto"];?></td>
                        <td><?php echo $val["codigo"];?></td>
                        <td><?php echo $val["descripcion"];?></td>
                        <td><?php echo $val["unidad"];?></td>
                        <td><?php echo number_format($val["cantidad_a"],4);?></td>
                        <td><?php echo number_format($val["precio_a"],4);?></td>
                        <td><?php echo number_format($val["total_a"],4);?></td>
                    </tr>
                <?php }
            ?>
        <?php }
    ?>
</table>