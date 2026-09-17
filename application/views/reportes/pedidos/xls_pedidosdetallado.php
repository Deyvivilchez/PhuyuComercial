<?php
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Pedidos-general-detallado-'.date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');
?>

<table border="1">
    <tr>
        <th colspan="8"> 
            <b><?php echo utf8_decode("REPORTE DE PEDIDOS DETALLADOS");?></b>
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
			<th>ID</th>
			<th>COMPROBANTE</th>
			<th>FECHA</th>
			<th>CLIENTE</th>
			<th>VALOR VENTA</th>
			<th>IGV</th>
			<th>IMPORTE</th>
			<th>ESTADO</th>
		</tr>
		<?php
			$valorventa = 0; $total = 0; $igv = 0;
			foreach ($value["lista"] as $k => $v) {
				$valorventa = $valorventa + $v["valorventa"];
				$total = $total + $v["importe"];
				$igv = $igv + $v["igv"]; 
				$estado = ($v["estadoproceso"]==0) ? 'PENDIENTE' : 'CANJEADO';
				?>
				<tr>
					<td><?php echo $v["codpedido"];?></td>
					<td><?php echo utf8_decode($v["seriecomprobante"]."-".$v["nrocomprobante"]);?></td>
					<td><?php echo $v["fechapedido"];?></td>
					<td><?php echo utf8_decode($v["cliente"]); ?></td>
					<td><?php echo number_format($v["valorventa"],2); ?></td>
					<td><?php echo number_format($v["igv"],2); ?></td>
					<td><?php echo number_format($v["importe"],2);?></td>
					<td><?php echo utf8_decode($estado);?></td>
				</tr>
				<tr>
					<th>CANT.</th>
					<th>UNI. MED.</th>
					<th colspan="2">DESCRIPCION DETALLE VENTA</th>
					<th>P. UNITARIO</th>
					<th>IGV</th>
					<th>IMPORTE</th>
					<th></th>	
				</tr>
			<?php
				$detalle = $this->db->query("select pd.*,pro.descripcion as producto,uni.descripcion as unidad from kardex.pedidosdetalle pd inner join almacen.productos as pro on(pd.codproducto=pro.codproducto) inner join almacen.unidades as uni on(pd.codunidad=uni.codunidad) where pd.codpedido=".$v["codpedido"])->result_array();

				foreach ($detalle as $val) { ?>
					<tr>
						<td><?php echo number_format($val["cantidad"],2); ?></td>
						<td><?php echo utf8_decode($val["unidad"]); ?></td>
						<td colspan="2"><?php echo utf8_decode($val["producto"]); ?></td>
						<td><?php echo number_format($val["preciounitario"],2); ?></td>
						<td><?php echo number_format($val["igv"],2); ?></td>
						<td><?php echo number_format($val["subtotal"],2); ?></td>
						<td></td>
					</tr>
			<?php	}
			}
		?>
		<tr>
			<th colspan="4">TOTAL</th>
			<th><?php echo number_format($valorventa,2); ?></th> 
			<th><?php echo number_format($igv,2); ?></th>
			<th><?php echo number_format($total,2); ?></th> <th></th>
		</tr>
	<?php } ?>	
</table>