<?php
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="REPORTE-'.$tipo.'-'.date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');
?>

<table border="1">
    <tr>
        <th colspan="14"> 
            <b><?php echo utf8_decode($tipo);?></b>
        </th>
    </tr>
    <tr>
		<th >N° CRED.</th>
		<th>DOCUMENTO</th>
		<th >RAZON SOCIAL</th>
		<th>COMPROBANTE</th>
		<th>MONEDA</th>
		<th>FECHA CRED.</th>
		<th>FECHA VENC.</th>
		<th>DIAS VENC.</th>
		<th>N° CUOTA</th>
		<th>LETRA</th>
		<th>N° PAGO UNI.</th>
		<th>IMPORTE</th>
		<th>INTERES</th>
		<th>SALDO</th>
	</tr>
	<?php
		$totalimporte = 0; $totalinteres = 0; $totalsaldo = 0;
		foreach ($lista as $k => $v) { 
			$totalimporte = $totalimporte + $v["importecuota"];
			$totalinteres = $totalinteres + $v["interescuota"];
			$totalsaldo = $totalsaldo + $v["saldocuota"];
	?>
			<tr>
				<td><?php echo $v["codcredito"];?></td>
				<td><?php echo utf8_decode($v["tipoynrodocumento"]);?></td>
				<td ><?php echo utf8_decode($v["razonsocial"]);?></td>
				<td><?php echo utf8_decode($v["comprobantereferencia"]); ?></td>
				<td><?php echo utf8_decode($v["monedasimbolo"]); ?></td>
				<td><?php echo utf8_decode($v["fechainiciocredito"]); ?></td>
				<td><?php echo utf8_decode($v["fechavencecuota"]);?></td>
				<td><?php echo utf8_decode($v["diasvencidos"]);?></td>
				<td ><?php echo utf8_decode($v["nrocuota"]);?></td>
				<td><?php echo utf8_decode($v["nroletra"]); ?></td>
				<td><?php echo utf8_decode($v["nrounicodepago"]); ?></td>
				<td><?php echo number_format($v["importecuota"],2,".",""); ?></td>
				<td><?php echo number_format($v["interescuota"],2,".","");?></td>
				<td><?php echo number_format($v["saldocuota"],2,".","");?></td>
			</tr>
		<?php }
	?>
		<tr>
			<td colspan="11" style="text-align: center">TOTALES</td>
			<td>
				<?php echo number_format($totalimporte,2,".",""); ?>
			</td>
			<td>
				<?php echo number_format($totalinteres,2,".",""); ?>
			</td>
			<td>
				<?php echo number_format($totalsaldo,2,".",""); ?>
			</td>
		</tr>
</table>