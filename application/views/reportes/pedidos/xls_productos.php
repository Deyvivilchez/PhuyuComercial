<?php
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Pedidos-Productos-'.date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');
?>

<table border="1">
    <tr>
        <th colspan="8"> 
            <b><?php echo utf8_decode("REPORTE DE PEDIDOS PRODUCTOS");?></b>
        </th>
    </tr>
    <?php
    foreach ($sucursal as $key => $value) { ?>
    	<tr>
	        <th colspan="8"> 
	            <b><?php echo utf8_decode("SUCURSAL: ".$value["descripcion"]." DEL ".$fechadesde." AL ".$fechahasta);?></b>
	        </th>
	    </tr>
	    <tr>
			<th>CODIGO</th>
			<th>PRODUCTO</th>
			<th>UNIDAD</th>
			<th>CANTIDAD</th>
			<th>PRECIO</th>
			<th>TOTAL</th>
			<th>FECHA P.</th>
			<th>RAZON SOCIAL CLIENTE</th>
		</tr>
		<?php
			$cantidad = 0; $total = 0; $totalref = 0;
			foreach ($value["lista"] as $k => $v) {
				$cantidad = $cantidad + $v["cantidad"];
				$total = $total + $v["subtotal"];
				$totalref = $totalref + $v["subtotalref"]; ?>
				<tr>
					<td><?php echo $v["codigo"];?></td>
					<td><?php echo utf8_decode($v["producto"]);?></td>
					<td><?php echo $v["unidad"];?></td>
					<td><?php echo number_format($v["cantidad"],2); ?></td>
					<td><?php echo number_format($v["preciounitario"],2); ?></td>
					<td><b><?php echo number_format($v["subtotal"],2); ?></b></td>
					<td><?php echo utf8_decode($v["fechapedido"]);?></td>
					<td><?php echo utf8_decode($v["cliente"]);?></td>
				</tr>
			<?php }
		?>
		<tr>
			<th colspan="3">TOTAL</th>
			<th><?php echo number_format($cantidad,2); ?></th> <th></th>
			<th><?php echo number_format($total,2); ?></th> <th></th>
		</tr>
	<?php } ?>	
</table>