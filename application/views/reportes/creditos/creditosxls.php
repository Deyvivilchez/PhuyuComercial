<?php
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="ESTADODECUENTAS' . date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');
?>
<style type="text/css">
    .celdauno{
        font-weight: 700;
        text-align: center;
        border:1px solid #ccc;
    }
    .celdados{
        font-size: 14px !important;
        font-weight: 700;
        border:1px solid #ccc;
    }
    .celdatres{
        border:none;
    }
</style>
<table style="font-size: 17px">
    <tr>
        <th colspan="9"> 
            <b><?php echo utf8_decode($_SESSION["phuyu_empresa"]);?></b>
        </th>
    </tr>
    <tr><th colspan="9"><?php echo 'ESTADO DE CUENTA - '.$tipo;?></th></tr>
    <tr><td colspan="9" align="center"><?php echo 'REPORTE DESDE '.$desde[2]."-".$desde[1]."-".$desde[0]." HASTA ".$hasta[2]."-".$hasta[1]."-".$hasta[0];?></td></tr>
</table>

<?php
    foreach ($socios as $key => $value) {
        
?>
    <table class="table">
        <tr>
            <td colspan="9"><b><?php echo $socio.": ".utf8_decode($value["razonsocial"])." | DIRECCION: ".utf8_decode($value["direccion"]); ?></b></td>
        </tr>
        <tr>
           <td class="celdauno">FECHA</td>
           <td class="celdauno">LINEA</td>
           <td class="celdauno">COMPROBANTE</td>
           <td class="celdauno">DESCRIPCION</td>
           <td class="celdauno">IMPORTE</td>
           <td class="celdauno">INTERES</td>
           <td class="celdauno">T IMPORTE</td>
           <td class="celdauno">ABONO</td>
           <td class="celdauno">SALDO</td> 
        </tr>
        <?php
        $importe = 0; $interes = 0; $total = 0; $cobranza = 0; $saldo = 0;
        foreach ($value["creditos"] as $k => $val) {
            $importe = $importe + $val["importe"]; 
            $interes = $interes + $val["interes"]; 
            $total = $total + $val["total"]; 
            $cobranza = $cobranza + $val["cobranza"]; 
            $saldo = $saldo + $val["saldo"];
        ?>
        <tr>
            <td><?php echo $val["fecha"];?></td>
            <td><?php echo $val["linea"]?></td>
            <td><?php echo $val["comprobante"]?></td>
            <td><?php echo $val["referencia"]?></td>
            <td><?php echo number_format($val["importe"],2)?></td>
            <td><?php echo number_format($val["interes"],2)?></td>
            <td><?php echo number_format($val["total"],2)?></td>
            <td><?php echo number_format($val["cobranza"],2)?></td>
            <td><?php echo number_format($val["saldo"],2)?></td>
        </tr>
        <?php } ?>
        <tr>
           <td class="celdados" colspan="4" align="right">TOTALES</td>
           <td class="celdauno"><?php echo number_format($importe,2);?></td>
           <td class="celdauno"><?php echo number_format($interes,2);?></td>
           <td class="celdauno"><?php echo number_format($total,2);?></td>
           <td class="celdauno"><?php echo number_format($cobranza,2);?></td>
           <td class="celdauno"><?php echo number_format($saldo,2);?></td> 
        </tr>
    </table>
    <table><tr><td colspan="9"></td></tr></table>
<?php
    }
?>