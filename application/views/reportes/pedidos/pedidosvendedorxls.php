<?php
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="ReportePedidosVendedor' . date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');
?>

<table border="1">
    <tr>
        <th colspan="13"> 
            <b>REPORTE DE PEDIDOS X VENDEDOR <?php echo utf8_decode($_SESSION["phuyu_empresa"]);?></b>
        </th>
    </tr>
    <tr>
        <th colspan="13">
            <b style="font-size:9px">PEDIDOS DESDE <?php echo $vendedor_texto;?></b>
        </th>
    </tr>

    <tr>
        <th>N°</th>
        <th>FECHA</th>
        <th>DOCUMENTO</th>
        <th>DNI/RUC</th>
        <th colspan="6">RAZON SOCIAL</th>
        <th>SUBTOTAL</th>
        <th>IGV</th>
        <th>TOTAL</th>
    </tr>
    <?php 
        $item = 0;$valorventa_general = 0; $igv_general = 0; $icbper_general = 0; $total_general = 0;
        foreach ($lista as $key => $value){ $item++; ?>                    
            <tr>
                <td style="background: #ddd"><?php echo $item;?></td>
                <td style="background: #ddd"><?php echo $value["fechapedido"];?></td>
                <td style="background: #ddd"><?php echo $value["seriecomprobante"].'-'.$value["nrocomprobante"];?></td>
                <td style="background: #ddd"><?php echo $value["documento"];?></td>
                <td style="background: #ddd" colspan="6"><?php echo utf8_decode($value["cliente"]);?></td>
                <td style="background: #ddd"><?php echo $value["valorventa"];?></td>
                <td style="background: #ddd"><?php echo $value["igv"];?></td>
                <td style="background: #ddd"><?php echo $value["importe"];?></td>
            </tr>

            <?php 
                if (!empty($tipos)) { 
                    $detalle = $this->db->query("select kd.*,p.descripcion as producto,u.descripcion as unidad,p.codigo from kardex.pedidosdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codpedido=".$value["codpedido"]." and kd.estado=1 order by kd.item")->result_array();


                ?>
               <tr>
                    <td>CANT</td>
                    <td colspan="8">DESCRIPCION DETALLE VENTA</td>
                    <td>UNI.MED</td>
                    <td>P.UNITARIO</td>
                    <td>IGV</td>
                    <td>IMPORTE</td>
               </tr>
            <?php 
                    foreach ($detalle as $v) { ?>
                    <tr>
                        <td><?php echo number_format($v["cantidad"],2);?></td>
                        <td colspan="8"><?php echo utf8_decode($v["codigo"]." - ".$v["producto"].' '.$v["descripcion"]);?></td>
                        <td><?php echo utf8_decode($v["unidad"]);?></td>
                        <td><?php echo number_format($v["preciounitario"],2);?></td>
                        <td><?php echo number_format($v["igv"],2);?></td>
                        <td><?php echo number_format($v["subtotal"],2);?></td>
                    </tr>
            <?php
                    }        
                }
            ?>
        <?php 
            $total_general = $total_general + $value["importe"];
            $valorventa_general = $valorventa_general + $value["valorventa"];
            $igv_general = $igv_general + $value["igv"];
        }
    ?>
    <tr>
        <td style="color:#d9534f;text-align:right" colspan="10"> <b>TOTAL NETO GENERAL S/:</td>
        <td style="color:#d9534f"><?php echo number_format($valorventa_general,2)?></td>
        <td style="color:#d9534f"><?php echo number_format($igv_general,2)?></td>
        <td style="color:#d9534f"><?php echo number_format($total_general,2)?></td>
    </tr>
</table>