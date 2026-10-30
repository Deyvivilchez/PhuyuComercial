<?php
if (!function_exists('phuyu_nombre_archivo_empresa')) {
    function phuyu_nombre_archivo_empresa($tipo, $empresaDatos = array())
    {
        $empresaInfo = isset($empresaDatos[0]) ? $empresaDatos[0] : array();
        $empresa = '';

        if (!empty($empresaInfo["razonsocial"])) {
            $empresa = $empresaInfo["razonsocial"];
        } elseif (!empty($empresaInfo["nombrecomercial"])) {
            $empresa = $empresaInfo["nombrecomercial"];
        } elseif (!empty($empresaInfo["documento"])) {
            $empresa = $empresaInfo["documento"];
        } elseif (!empty($_SESSION["phuyu_empresa"])) {
            $empresa = $_SESSION["phuyu_empresa"];
        } else {
            $empresa = "Negocio";
        }

        $empresaAscii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $empresa);
        $empresa = $empresaAscii !== false ? $empresaAscii : $empresa;
        $empresa = preg_replace('/[^A-Za-z0-9]+/', '_', $empresa);
        $empresa = trim($empresa, '_');

        return ($empresa !== '' ? $empresa : 'Negocio') . '_' . $tipo . '_' . date('Y-m-d') . '.xls';
    }
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . phuyu_nombre_archivo_empresa('VentasVendedor', isset($empresa) ? $empresa : array()) . '"');
header('Cache-Control: max-age=0');
?>

<table border="1">
    <tr>
        <th colspan="13"> 
            <b>REPORTE DE VENTAS <?php echo utf8_decode($_SESSION["phuyu_empresa"]);?></b>
        </th>
    </tr>
    <tr>
        <th colspan="13">
            <b style="font-size:9px">VENTAS DESDE <?php echo $vendedor_texto;?></b>
        </th>
    </tr>

    <tr>
        <td>N°</td>
        <td>FECHA</td>
        <td>DOCUMENTO</td>
        <td>DNI/RUC</td>
        <td colspan="6">RAZON SOCIAL</td>
        <td>SUBTOTAL</td>
        <td>IGV</td>
        <td>TOTAL</td>
    </tr>
    <?php 
        $item = 0;$valorventa_general = 0; $igv_general = 0; $icbper_general = 0; $total_general = 0;
        foreach ($lista as $key => $value){ $item++; ?>                    
            <tr>
                <td style="background: #ddd"><strong><?php echo $item;?></strong></td>
                <td style="background: #ddd"><strong><?php echo $value["fechacomprobante"];?><strong></td>
                <td style="background: #ddd"><strong><?php echo $value["seriecomprobante"].'-'.$value["nrocomprobante"];?></strong></td>
                <td style="background: #ddd"><strong><?php echo $value["documento"];?></strong></td>
                <td style="background: #ddd" colspan="6"><strong><?php echo utf8_decode($value["cliente"]);?></strong></td>
                <td style="background: #ddd"><strong><?php echo $value["valorventa"];?></strong></td>
                <td style="background: #ddd"><strong><?php echo $value["igv"];?></strong></td>
                <td style="background: #ddd"><strong><?php echo $value["importe"];?></strong></td>
            </tr>

            <?php 
                if (!empty($tipos)) { 
                    $detalle = $this->db->query("select kd.*,p.descripcion as producto,u.descripcion as unidad,p.codigo from kardex.kardexdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codkardex=".$value["codkardex"]." and kd.estado=1 order by kd.item")->result_array();


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
        }
    ?>
    <tr>
        <td style="color:#d9534f;text-align:right" colspan="12"> <b>TOTAL NETO GENERAL S/:</td>
        <td style="color:#d9534f"><?php echo number_format($total_general,2)?></td>
    </tr>
</table>
