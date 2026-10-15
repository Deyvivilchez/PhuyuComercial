<?php
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="ReportePrestamos' . date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');
?>

<table border="1">
    <tr>
        <th colspan="10"> 
            <b>REPORTE DE VENTAS <?php echo utf8_decode($_SESSION["phuyu_empresa"]);?></b>
        </th>
    </tr>
    <tr>
        <th colspan="10">
            <b style="font-size:9px"><?php echo $titulo;?></b>
        </th>
    </tr>
    <tr>
        <th colspan="3">RUC: <?php echo $_SESSION["phuyu_ruc"];?></th>
    </tr>

    <tr>
        <th colspan="3">PERSONA</th>
        <th>FECHA PRESTAMO</th>
        <th>COMPROB. REF.</th>
        <th>IMPORTE</th>
        <th colspan="2">OBSERVACION</th>
        <th colspan="2">ESTADO</th> 
    </tr>

    <?php 
        foreach ($lista as $val) { 
            $color = "";
            if ((int)$val["procesoprestamo"]==0) {
                $color = "color:red !important";
            }else{
                $color = "color:green !important";
            } ?>
            
            <tr style="<?php echo $color;?>">
                <td colspan="3"><?php echo $val["persona"];?></td>
                <td><?php echo $val["fechakardex"];?></td>
                <td><?php echo $val["seriecomprobante"].'-'.$val["nrocomprobante"];?></td>
                <td><?php echo $val["importe"];?> </td>
                <td colspan="2"><?php echo $val["descripcion"];?> </td>
                <?php
                    if($val["procesoprestamo"]==0){
                        $estado = 'PENDIENTE';
                    }else{
                        $estado = 'DEVUELTO';
                    }
                ?>
                <td colspan="2"><?php echo $estado;?> </td>

                <?php
                    if($formato==2){
                        $detalle = $this->db->query("select kd.codproducto,kd.codunidad,round(kd.cantidad,2) as cantidad,round(kd.cantidaddevuelta,2) as cantidaddevuelta,p.codigo,COALESCE(round(kd.cantidad - kd.cantidaddevuelta,2),0) as cantidadxdevolver,round(kd.preciounitario,2) as precio,kd.preciosinigv,kd.preciorefunitario,kd.valorventa,round(kd.igv,2) as igv, round(kd.subtotal,2) as subtotal,kd.item, kd.codafectacionigv,p.descripcion as producto,u.descripcion as unidad, kd.recoger,kd.recogido,kd.descripcion,kd.codafectacionigv from kardex.kardexdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codkardex=".$val["codkardex"]." and kd.estado=1 order by kd.item")->result_array(); ?>
                    <tr>
                        <th>#</th>
                        <th colspan="2" class="detalle">PRODUCTO</th>
                        <th class="detalle">ID</th>
                        <th class="detalle">CODIGO</th>
                        <th class="detalle">UNIDAD</th>
                        <th class="detalle">CANT. PRESTADA</th>
                        <th class="detalle">CANT. DEVUELTA</th>
                    </tr>
                    <?php $i = 0;
                        foreach ($detalle as $v) { $i++; ?>
                           <tr>
                               <td><?php echo $i;?> </td>
                               <td colspan="2"><?php echo $v["producto"];?> </td>
                               <td><?php echo $v["codproducto"];?> </td>
                               <td><?php echo $v["codigo"];?> </td>
                               <td><?php echo $v["unidad"];?> </td>
                               <td><?php echo $v["cantidad"];?> </td>
                               <td><?php echo $v["cantidaddevuelta"];?> </td>
                           </tr>
                    <?php }
                    ?>
                <?php  }
                ?>
            </tr>
        <?php }
    ?>
</table>