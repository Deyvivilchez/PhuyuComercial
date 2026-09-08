<?php
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="REPORTE-'.$tipo.'-'.date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');
?>

<table border="1">
    <tr>
        <th colspan="8"> 
            <b><?php echo utf8_decode("REPORTE DE ".$tipo);?></b>
        </th>
    </tr>
    <tr>
		<th>ID</th>
		<th>DOCUMENTO</th>
		<th colspan="2">RAZON SOCIAL</th>
		<th>DIRECCION</th>
		<th>CONTACTO</th>
		<th>EMAIL</th>
		<th>UBIGEO</th>
	</tr>
	<?php
		foreach ($lista as $k => $v) { ?>
			<tr>
				<td><?php echo $v["codpersona"];?></td>
				<td><?php echo utf8_decode($v["documento"]);?></td>
				<td colspan="2"><?php echo utf8_decode($v["razonsocial"]);?></td>
				<td><?php echo utf8_decode($v["direccion"]); ?></td>
				<td><?php echo utf8_decode($v["telefono"]); ?></td>
				<td><?php echo utf8_decode($v["email"]); ?></td>
				<td><?php echo utf8_decode($v["provincia"].' - '.$v["distrito"]);?></td>
			</tr>
		<?php }
	?>
</table>