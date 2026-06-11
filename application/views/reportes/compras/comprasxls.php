<?php
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="ReporteVentas' . date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');
?>

<table border="1">
    <tr>
        <th colspan="15"> 
            <b>REPORTE DE COMPRAS <?php echo utf8_decode($_SESSION["phuyu_empresa"]);?></b>
        </th>
    </tr>
    <tr>
        <th colspan="15">
            <b style="font-size:9px">COMPRAS DESDE <?php echo $fechadesde.' A '.$fechahasta;?></b>
        </th>
    </tr>
    <?php 
        
        foreach ($sucursales as $key => $value){ ?>  
            <tr>
                <th colspan="14"> <b>SUCURSAL: <?php echo utf8_decode($value["descripcion"]);?></b> </th>
            </tr>                  
            <tr>
                <td style="font-weight: 700">N°</td>
                <td style="font-weight: 700">FECHA</td>
                <td style="font-weight: 700">DOCUMENTO</td>
                <td style="font-weight: 700" colspan="7">RAZON SOCIAL</td>
                <td style="font-weight: 700">TIPO</td>
                <td style="font-weight: 700">COMPROBANTE</td>
                <td style="font-weight: 700">VALOR VENTA</td>
                <td style="font-weight: 700">IGV</td>
                <td style="font-weight: 700">IMPORTE</td>
            </tr>

            <?php 
                $item = 0;$importe = 0;
                foreach ($value["lista"] as $val) { $item++;
                    $color = ""; $relleno = "";
                    if ((int)$val["estado"]==0) {
                        $color = "color:red !important";
                    }

                    if (!empty($tipos)) {
                        $relleno = "background-color: #ddd;font-weight:700";
                    } 

                    ?>

                    <tr style="<?php echo $color;?>">
                        <td style="<?php echo $relleno; ?>"><?php echo $item; ?></td>
                        <td style="<?php echo $relleno; ?>"><?php echo $val["fechacomprobante"];?></td>
                        <td style="<?php echo $relleno; ?>"><?php echo $val["documento"];?></td>
                        <td style="<?php echo $relleno; ?>" colspan="7"><?php echo $val["razonsocial"];?></td>
                        <td style="<?php echo $relleno; ?>"><?php echo $val["tipo"];?></td>
                        <td style="<?php echo $relleno; ?>"><?php echo $val["seriecomprobante"].'-'.$val["nrocomprobante"];?></td>
                        <td style="<?php echo $relleno; ?>"><?php echo $val["valorventa"];?></td>
                        <td style="<?php echo $relleno; ?>"><?php echo $val["igv"];?></td>
                        <td style="<?php echo $relleno; ?>"><?php echo $val["importe"];?></td>
                    </tr>
                <?php 

                    if (!empty($tipos)) { 
                        $detalle = $this->db->query("select kd.*,p.descripcion as producto,u.descripcion as unidad,p.codigo from kardex.kardexdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codkardex=".$val["codkardex"]." and kd.estado=1 order by kd.item")->result_array();


                    ?>
                   <tr>
                        <td>CANT</td>
                        <td colspan="10">DESCRIPCION DETALLE VENTA</td>
                        <td>UNI.MED</td>
                        <td>P.UNITARIO</td>
                        <td>IGV</td>
                        <td>IMPORTE</td>
                   </tr>
                   <?php
                       foreach ($detalle as $v) { ?>
                        <tr>
                            <td><?php echo number_format($v["cantidad"],2);?></td>
                            <td colspan="10"><?php echo utf8_decode($v["codigo"]." - ".$v["producto"].' '.$v["descripcion"]);?></td>
                            <td><?php echo utf8_decode($v["unidad"]);?></td>
                            <td><?php echo number_format($v["preciounitario"],2);?></td>
                            <td><?php echo number_format($v["igv"],2);?></td>
                            <td><?php echo number_format($v["subtotal"],2);?></td>
                        </tr>
                    <?php
                        }
                    }
                    $importe = $importe + $val["importe"];
                } ?>

            <tr>
                <td style="color:#d9534f;text-align:right" colspan="14"> <b>TOTAL NETO GENERAL S/:</td>
                <td style="color:#d9534f"><?php echo number_format($importe,2)?></td>
            </tr>
            <tr></tr>
   <?php 
        }
    ?>
</table>