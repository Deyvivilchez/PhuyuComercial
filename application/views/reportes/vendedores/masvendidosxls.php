<?php
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="ReporteVentas' . date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');
?>

<table border="1">
    <tr>
        <th colspan="13"> 
            <b>REPORTE DE PRODUCTOS VENDIDOS <?php echo utf8_decode($_SESSION["phuyu_empresa"]);?></b>
        </th>
    </tr>
    <tr>
        <th colspan="13">
            <b style="font-size:9px"><?php echo $vendedor_texto;?></b>
        </th>
    </tr>

    <tr>
        <td>N°</td>
        <td>CODIGO PRODUCTO</td>
        <td colspan="7">DESCRIPCION PRODUCTO</td>
        <td>U.MEDIDA</td>
        <td>CANTIDAD</td>
        <td>U.MEDIDAD MIN</td>
        <td>CANTIDAD</td>
    </tr>
    <?php 
        $item = 0; $total = 0; $totalmin = 0;
        foreach ($lista as $key => $value){ 
            $unidades = $this->db->query("select u.descripcion as unidad,pu.codunidad, pu.factor from almacen.productounidades as pu inner join almacen.unidades as u on(pu.codunidad=u.codunidad) where pu.codproducto=".$value["codproducto"]." and pu.estado=1 order by factor asc")->result_array();
            if (count($unidades)==1) {
                $codunidadmin = $unidades[0]["codunidad"]; $unidadmin = $unidades[0]["unidad"]; $factormin = $unidades[0]["factor"];
                $codunidad= 0; $unidad = "-"; $factor = 1;
            }else{
                $codunidadmin = $unidades[0]["codunidad"]; $unidadmin = $unidades[0]["unidad"]; $factormin = $unidades[0]["factor"];
                $codunidad = $unidades[1]["codunidad"]; $unidad = $unidades[1]["unidad"]; $factor = $unidades[1]["factor"];
            }

            $ventas = $this->db->query("select kd.codproducto,kd.codunidad,kd.cantidad from kardex.kardex as k inner join kardex.kardexdetalle as kd on(k.codkardex=kd.codkardex) where k.codmovimientotipo=20 and kd.codproducto=".$value["codproducto"]." and k.fechacomprobante>='".$this->request->fechadesde."' and k.fechacomprobante<='".$this->request->fechahasta."' and k.estado=".$this->request->estado)->result_array();
            $cantidad = 0;
            foreach ($ventas as $v) {
                if ($v["codunidad"]==$codunidadmin) {
                    $cantidad = $cantidad + ($factormin * $v["cantidad"]);
                }else{
                    $cantidad = $cantidad + ($factor * $v["cantidad"]);
                }
            }

            if ($codunidad==0) {
                $cantidad_unidad = $cantidad; $cantidad_unidad_min = 0; $unidad = $unidadmin; $unidadmin = "-";
            }else{
                $cantidad_unidad = floor($cantidad / $factor);
                $cantidad_unidad_min = $cantidad - ($cantidad_unidad * $factor);
            }
            
            $total = $total + $cantidad_unidad; $totalmin = $totalmin + $cantidad_unidad_min;
            $item++; ?>                    
            <tr>
                <td><?php echo $item;?></td>
                <td><?php echo $value["codigo"];?></td>
                <td colspan="7"><?php echo utf8_decode($value["descripcion"]);?></td>
                <td><?php echo $unidad;?></td>
                <td><?php echo number_format($cantidad_unidad,2);?></td>
                <td><?php echo $unidadmin;?></td>
                <td><?php echo number_format($cantidad_unidad_min,2);?></td>
            </tr>
        <?php 
        }
    ?>
    <tr>
        <td style="color:#d9534f;text-align:right" colspan="9"> <b>TOTAL VENDIDOS:</td>
        <td></td>
        <td style="color:#d9534f"><?php echo number_format($total,2); ?></td>
        <td></td>
        <td style="color:#d9534f"><?php echo number_format($totalmin,2);?></td>
    </tr>
</table>