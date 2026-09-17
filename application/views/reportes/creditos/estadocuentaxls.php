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
           <td class="celdauno">CARGO</td>
           <td class="celdauno">INTERES</td>
           <td class="celdauno">T CARGO</td>
           <td class="celdauno">ABONO</td>
           <td class="celdauno">SALDO</td> 
        </tr>
        <tr>
           <td colspan="4" align="right">SALDO ANTERIOR</td>
           <td><?php echo $value["anterior"]["totalimporte"]?></td>
           <td><?php echo $value["anterior"]["totalinteres"]?></td>
           <td><?php echo $value["anterior"]["totaltotal"]?></td>
           <td><?php echo $value["anterior"]["totalpagado"]?></td>
           <td><?php echo $value["anterior"]["saldo"]?></td>
        </tr>
        <?php
        $saldo = $value["anterior"]["saldo"]; $abono = 0; 
        $cargo = 0; $totalinterespdf=0;
        foreach ($value["movimientos"] as $k => $val) {
            $saldo = $saldo + $val["cargo"] - $val["abono"];
            $cargo = $cargo + $val["cargo"]; 
            $abono = $abono + $val["abono"];
            $totalinterespdf=$totalinterespdf+$val["interes"];
        ?>
        <tr>
            <td><?php echo $val["fecha"];?></td>
            <td><?php echo $val["linea"]?></td>
            <td><?php echo $val["comprobante"]?></td>
            <td><?php echo $val["referencia"]?></td>
            <td><?php echo number_format($val["cargo"],2)?></td>
            <td><?php echo number_format($val["interes"],2)?></td>
            <td><?php echo number_format($val["cargo"]+$val["interes"],2)?></td>
            <td><?php echo number_format($val["abono"],2)?></td>
            <td><?php echo number_format($saldo,2)?></td>
        </tr>
        <?php } ?>
        <tr>
           <td class="celdados" colspan="4" align="right">TOTALES</td>
           <td class="celdauno"><?php echo number_format($cargo+$value["anterior"]["totalimporte"],2);?></td>
           <td class="celdauno"><?php echo number_format($totalinterespdf+$value["anterior"]["totalinteres"],2);?></td>
           <td class="celdauno"><?php echo number_format($cargo+$totalinterespdf+$value["anterior"]["totaltotal"],2);?></td>
           <td class="celdauno"><?php echo number_format($abono+$value["anterior"]["totalpagado"],2);?></td>
           <td class="celdauno"><?php echo number_format($value["anterior"]["saldo"] + $cargo - $abono + $totalinterespdf,2);?></td> 
        </tr>
    </table>
    <table><tr><td colspan="9"></td></tr></table>
<?php
    }
?>