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
header('Content-Disposition: attachment;filename="' . phuyu_nombre_archivo_empresa('ProductosVendidos', isset($empresa) ? $empresa : array()) . '"');
header('Cache-Control: max-age=0');
?>

<style type="text/css">
    .titulo{
        color:#ffffff;
        background:#17365d;
        font-size:16px;
        font-weight:700;
        text-align:center;
        border:1px solid #17365d;
    }
    .subtitulo{
        color:#1f4e78;
        background:#d9eaf7;
        font-size:12px;
        font-weight:700;
        text-align:center;
        border:1px solid #9dc3e6;
    }
    .etiqueta{
        color:#ffffff;
        background:#4472c4;
        font-size:11px;
        font-weight:700;
        border:1px solid #4472c4;
    }
    .dato{
        color:#1f1f1f;
        background:#f7fbff;
        font-size:11px;
        font-weight:700;
        border:1px solid #d6e3f3;
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
        <th class="titulo" colspan="13">
            <?php echo utf8_decode($_SESSION["phuyu_empresa"]);?>
        </th>
    </tr>
    <tr>
        <th class="subtitulo" colspan="13"><?php echo utf8_decode('REPORTE DE VENTAS POR PRODUCTOS'); ?></th>
    </tr>
    <tr>
        <td class="etiqueta" colspan="2">SUCURSAL</td>
        <td class="dato" colspan="11"><?php echo utf8_decode(isset($sucursal_texto) ? $sucursal_texto : 'TODAS LAS SUCURSALES ACTIVAS');?></td>
    </tr>
    <tr>
        <td class="etiqueta" colspan="2">RANGO</td>
        <td class="dato" colspan="4"><?php echo (isset($fechadesde) ? $fechadesde : '').' AL '.(isset($fechahasta) ? $fechahasta : '');?></td>
        <td class="etiqueta" colspan="2">FILTRO</td>
        <td class="dato" colspan="5"><?php echo utf8_decode($vendedor_texto);?></td>
    </tr>
    <tr><td colspan="13"></td></tr>

    <tr>
        <td class="cabecera">N°</td>
        <td class="cabecera">CODIGO PRODUCTO</td>
        <td class="cabecera" colspan="7">DESCRIPCION PRODUCTO</td>
        <td class="cabecera">U.MEDIDA</td>
        <td class="cabecera">CANTIDAD</td>
        <td class="cabecera">U.MEDIDA MIN</td>
        <td class="cabecera">CANTIDAD</td>
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

            $ventas = $this->db->query("select kd.codproducto,kd.codunidad,kd.cantidad from kardex.kardex as k inner join kardex.kardexdetalle as kd on(k.codkardex=kd.codkardex) where k.codmovimientotipo=20 and kd.codproducto=".$value["codproducto"]." and k.fechacomprobante>='".$this->request->fechadesde."' and k.fechacomprobante<='".$this->request->fechahasta."' and k.estado=".$this->request->estado." ".(isset($filtro) ? $filtro : ""))->result_array();
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
                <td class="detalle"><?php echo $item;?></td>
                <td class="detalle"><?php echo $value["codigo"];?></td>
                <td class="detalle" colspan="7"><?php echo utf8_decode($value["descripcion"]);?></td>
                <td class="detalle"><?php echo $unidad;?></td>
                <td class="detalle"><?php echo number_format($cantidad_unidad,2);?></td>
                <td class="detalle"><?php echo $unidadmin;?></td>
                <td class="detalle"><?php echo number_format($cantidad_unidad_min,2);?></td>
            </tr>
        <?php 
        }
    ?>
    <tr>
        <td class="total" style="text-align:right" colspan="9">TOTAL VENDIDOS:</td>
        <td class="total"></td>
        <td class="total"><?php echo number_format($total,2); ?></td>
        <td class="total"></td>
        <td class="total"><?php echo number_format($totalmin,2);?></td>
    </tr>
</table>
