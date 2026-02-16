<?php
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="ReporteVentas' . date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');
?>
<style type="text/css">
    .celdauno{
        font-weight: 700;
        text-align: center;
        border:1px solid #ccc;
    }
    .celdados{
        font-size: 11px !important;
        font-weight: 700;
        border:1px solid #ccc;
    }
    .celdatres{
        border:none;
    }
</style>
<table style="font-size: 12px">
    <tr>
        <th colspan="23"> 
            <b><?php echo utf8_decode($_SESSION["phuyu_empresa"]);?></b>
        </th>
    </tr>
    <tr>
        <th colspan="23">
            <?php echo 'RUC: '.$empresa[0]["documento"]; ?>
        </th>
    </tr>
    <tr>
        <th colspan="23"><?php echo 'DIRECCION: '.$empresa[0]["direccion"];?></th>
    </tr>
    <tr>
        <th colspan="23"><?php echo 'REGISTRO DE VENTAS E INGRESOS PERIODO: '.$periodo[1].'/'.$periodo[0];?></th>
    </tr>
    <tr><th colspan="23"></th></tr>
</table>
<table style="font-size: 10px">
    <?php 
        $valorventa_general = 0; $igv_general = 0; $icbper_general = 0; $total_general = 0;
         ?>  
        <tr>
            <td class="celdauno" rowspan="3" valign="middle">CUO</td>
            <td class="celdauno" rowspan="2" colspan="5" style="width: 100px">COMPROBANTE DE PAGO O DOCUMENTO</td>
            <td class="celdauno" colspan="3">INFORMACION DEL CLIENTE</td>
            <td class="celdauno" rowspan="3"><?php echo utf8_decode("VALOR FACTURADO DE LA EXPORTACIÓN")?></td>
            <td class="celdauno" rowspan="3"><?php echo utf8_decode("BASE IMPONIBLE DE LA OPERACIÓN GRAVADA")?></td>
            <td class="celdauno" rowspan="2" colspan="2"><?php echo utf8_decode("IMPORTE TOTAL DE LA OPERACIÓN EXONERADA O INAFECTA")?></td>
            <td class="celdauno" rowspan="3">ISC</td>
            <td class="celdauno" rowspan="3">IGV Y/O IPM</td>
            <td class="celdauno" rowspan="3">ICBPER</td>
            <td class="celdauno" rowspan="3">OTROS</td>
            <td class="celdauno" rowspan="3">IMPORTE TOTAL</td>
            <td class="celdauno" rowspan="3">TIPO DE CAMBIO</td>
            <td class="celdauno" rowspan="2" colspan="4">REF. DE COMPROBANTE DE PAGO O DOC. ORIGINAL QUE SE MODIFICO</td>
        </tr>
        <tr>
            <td class="celdauno" colspan="2">DOC. IDENTIDAD</td>
            <td class="celdauno" rowspan="2"><?php echo utf8_decode("APELLIDOS Y NOMBRES, DENOMINACIÓN O RAZÓN SOCIAL")?></td>
        </tr>
        <tr>
            <td class="celdauno" style="width: 20px !important"><?php echo utf8_decode("F.EMISIÓN")?></td>
            <td class="celdauno" style="width: 20px !important">F.VENCIMIENTO</td>
            <td class="celdauno" style="width: 20px !important">TIPO</td>
            <td class="celdauno" style="width: 20px !important">SERIE</td>
            <td class="celdauno" style="width: 20px !important">NUMERO</td>
            <td class="celdauno">TIPO</td>
            <td class="celdauno">NUMERO</td>
            <td class="celdauno">EXONERADA</td>
            <td class="celdauno">INAFECTA</td>
            <td class="celdauno">FECHA</td>
            <td class="celdauno">TIPO</td>
            <td class="celdauno">SERIE</td>
            <td class="celdauno">NUMERO</td>
        </tr>
            <?php 
                $valorventa = 0; $igv = 0; $icbper = 0; $total = 0; $item = 0;
                $style='mso-number-format:"@";';
                foreach ($lista as $val) { $item++;
                    $nrocorrelativo = str_pad($item, 8, "0", STR_PAD_LEFT);
                    $nrocorrelativo = 'M'.$nrocorrelativo; 
                    $oficial = $val['oficial'];
                    $color = "";
                    if ((int)$val["estado"]==0) {
                        $color = "color:red !important";
                    } ?>
                    
                    <tr>
                        <td class="celdatres"><?php echo $nrocorrelativo;?></td>
                        <td class="celdatres"><?php echo $val["fechacomprobante"];?></td>
                        <td class="celdatres"></td>
                        <td class="celdatres">&nbsp;<?php echo $oficial;?></td>
                        <td class="celdatres"><?php echo $val["seriecomprobante"];?></td>
                        <td class="celdatres"><?php echo $val["nrocomprobante"];?></td>
                        <?php 
                            if ($val["coddocumentotipo"]==1 || (int)$val["estado"]==0) {
                                echo '<td class="celdatres"> 0 </td><td class="celdatres"> 0 </td>';
                            }else{
                                echo '<td class="celdatres">'.$val["tipodocumento"].'</td><td class="celdatres">'.$val["documento"].'</td>';
                            }

                            if ((int)$val["estado"]==0) {
                                echo '<td class="celdatres">ANULADO</td>';
                                echo '<td class="celdatres"></td>';
                                echo '<td class="celdatres"></td>';
                                echo '<td class="celdatres">'.number_format(0.00,2).'</td>';
                                echo '<td class="celdatres"></td>';
                                echo '<td class="celdatres"></td>';
                                echo '<td class="celdatres">'.number_format(0.00,2).'</td>';
                                echo '<td class="celdatres">'.number_format(0.00,2).'</td>';
                                echo '<td class="celdatres"></td>';
                                echo '<td class="celdatres">'.number_format(0.00,2).'</td>';
                                echo '<td class="celdatres"></td>';
                            }else{
                                $valorventa = $valorventa + $val["valorventa"]; 
                                $igv = $igv + $val["igv"]; 
                                $icbper = $icbper + $val["icbper"]; 
                                $total = $total + $val["importe"];

                                echo '<td class="celdatres">'.utf8_decode($val["razonsocial"]).'</td>';
                                echo '<td class="celdatres"></td>';
                                echo '<td class="celdatres"></td>';
                                echo '<td class="celdatres">'.number_format($val["valorventa"],2, '.', '').'</td>';
                                echo '<td class="celdatres"></td>';
                                echo '<td class="celdatres"></td>';
                                echo '<td class="celdatres">'.number_format($val["igv"],2).'</td>';
                                echo '<td class="celdatres">'.number_format($val["icbper"],2).'</td>';
                                echo '<td class="celdatres"></td>';
                                echo '<td class="celdatres">'.number_format($val["importe"],2).'</td>';
                                echo '<td class="celdatres"></td>';
                            }
                        ?>

                        <td class="celdatres"><?php echo $val["fecharef"];?> </td>
                        <td class="celdatres"><?php echo $val["tiporef"];?> </td>
                        <td class="celdatres"><?php echo $val["serie_ref"];?> </td>
                        <td class="celdatres"><?php echo $val["numero_ref"];?> </td>
                    </tr>
                <?php 
            $valorventa_general = $valorventa_general + $valorventa; 
            $igv_general = $igv_general + $igv; 
            $icbper_general = $icbper_general + $icbper; 
            $total_general = $total_general + $total;
        }
            
    ?>
    <tr>
        <td class="celdatres" colspan="8"></td>
        <td class="celdados" style="text-align:right"> <b>TOTAL S/:</b> </td>
        <td class="celdados">0</td>
        <td class="celdados">0</td>
        <td class="celdados"><?php echo number_format($valorventa,2)?></td>
        <td class="celdados">0</td>
        <td class="celdados">0</td>
        <td class="celdados"><?php echo number_format($igv,2)?></td>
        <td class="celdados"><?php echo number_format($icbper,2)?></td>
        <td class="celdados">0</td>
        <td class="celdados"><?php echo number_format($total,2)?></td>
        <td class="celdatres"></td>
        <td class="celdatres" colspan="4"> </td>
    </tr>
            
</table>