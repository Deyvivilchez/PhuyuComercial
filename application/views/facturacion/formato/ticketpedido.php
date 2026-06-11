<!DOCTYPE html>
<html>
	<!-- <link href="http://allfont.es/allfont.css?fonts=agency-fb" rel="stylesheet" type="text/css" /> -->
	<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>public/css/ticket/ticket.css">
	<script language="javascript">
	    function printThis() {
	        window.print(); return false;
	    }
	</script>

	<body onLoad="printThis();">
		<?php $linea = '--------------------------------------------------------------------'; ?>
		<?php
           if($empresa[0]["nombrecomercial"] == ''){
           	   $nombre = $empresa[0]['razonsocial'];
           }else{
           	   $nombre = $empresa[0]["nombrecomercial"];
           }
		?>
		<table  width="280" border="0" align="center">
			<tr>
	            <td colspan="3" align="center" class="Cabecera0"> <img src="<?php echo base_url();?>public/img/<?php echo $_SESSION['phuyu_logo'];?>" style="height:80px;"> </td>
	        </tr>
	        <tr>
	            <td colspan="3" align="center" class="Cabecera0"> <?php echo $nombre;?> </td>
	        </tr>
	        <tr>
	            <td colspan="3" align="center" class="Cabecera2"><?php echo utf8_decode($sucursal[0]['direccion']);?> </td>
	        </tr>
	        <tr>
	            <td colspan="3" align="center" class="Cabecera2-numeros1">RUC: <?php echo $empresa[0]['documento'];?></td>
	        </tr>
	        
	        <tr>
	            <td colspan="3" align="center" class="footer">VENDEDOR <?php echo $vendedor[0]["razonsocial"];?> </td>
	        </tr>
	        <tr>
	            <td colspan="3" align="center" class="footer">USUARIO: <?php echo $venta[0]["usuario"];?> </td>
	        </tr>
	        <tr>
	            <td colspan="3" align="center" class="footer">TELEFONO <?php echo $vendedor[0]["telefono"];?> </td>
	        </tr>
	        <tr class="Linea"><td colspan="3" align="center"><?php echo $linea ?> </td></tr>
	        <tr>
	            <td align="left" class="Cabecera2" colspan="2"> FECHA: <?php echo $venta[0]['fechapedido'];?></td>
	            <td align="right" class="Cabecera2"> <?php echo date("H:i:s");?></td>
	        </tr>

	        <tr>
                <td colspan="3" align="center" class="InfoVer"><b> <?php echo $venta[0]["comprobante"]." <br> ".$venta[0]["seriecomprobante"]."-".$venta[0]["nrocomprobante"];?> </b> </td>
            </tr>

            <tr>
                <td align="left" class="InfoVer" colspan="3"> CLIENTE: <?php echo $venta[0]["cliente"];?> </td>
            </tr>
            <tr>
                <td align="left" class="InfoVer" colspan="3"> DIRECCION: <?php echo $venta[0]["direccion"];?> </td>
            </tr>
            <tr>
                <td align="left" class="InfoVer" colspan="3">D.N.I / R.U.C: <?php echo $venta[0]["documento"];?></td>
            </tr>
            <?php 

            	if ($venta[0]["condicionpago"]==2) { ?>
            		<tr>
		                <td align="left" class="InfoVer" colspan="3">CONDICION DE PAGO: AL CREDITO</td>
		            </tr>
            	<?php }
            ?>
            <tr>
                <td colspan="3" valign="top">
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tr class="Linea"><td colspan="4" align="center"> <?php echo $linea ?> </td></tr>
                        <tr>
                            <td width="10" align="center" class="Detallecab">CANT</td>
                            <td width="240" align="center" class="Detallecab">PRODUCTO</td>
                            <td width="30" align="center" class="Detallecab">P.U.</td>
                            <td width="30" align="center" class="Detallecab">TOTAL</td>
                        </tr>
                        <tr class="Linea"><td colspan="4" align="center"> <?php echo $linea ?> </td></tr>
                        <?php 
							foreach ($detalle as $key => $value) { ?>
								<tr>
									<td align="left" class="Detallenumeritos1" style="padding-right:5px;">
										&nbsp;<span style="font-size:14px;"><?php echo round($value["cantidad"],2)?></span>&nbsp;<?php echo substr($value["unidad"],0,3);?>
									</td>
									<td align="left" class="Detalle"><?php echo $value["producto"].' '.$value["descripcion"];?></td>
									<td align="right" class="Detallenumeritos1" style="font-size:13px;"><?php echo number_format($value["preciounitario"],2);?>&nbsp;</td>
									<td align="right" class="Detallenumeritos1" style="font-size:13px;"><?php echo number_format($value["subtotal"],2);?></td>
								</tr>
							<?php }
						?>

						<tr class="Linea"><td colspan="4" align="center"> <?php echo $linea ?> </td></tr>

						<tr>
                            <td colspan="3" align="right" class="Total">SUB TOTAL S/:</td>
                            <td align="right" class="numeritos1"> <?php echo number_format($venta[0]["valorventa"],2);?> </td>
                		</tr>
                        
                        <tr>
                            <td colspan="3" align="right" class="Total">DESCUENTO S/:</td>
                            <td align="right" class="numeritos1"> <?php echo number_format($venta[0]["descglobal"],2);?> </td>
                        </tr>
                        <tr>
                            <td colspan="3" align="right" class="Total">IGV S/:</td>
                            <td align="right" class="numeritos1"> <?php echo number_format($venta[0]["igv"],2);?> </td>
                        </tr>
                        <tr>
                            <td colspan="3" align="right" class="Total">TOTAL S/:</td>
                            <td align="right" class="numeritos1"><?php echo number_format($venta[0]["importe"],2);?> </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="4" align="left" class="Total"> <?php echo $texto_importe; ?></td>
            </tr>

            <tr class="Linea"><td colspan="4" align="center"> <?php echo $linea ?> </td></tr>
	        <tr>
	            <td colspan="3" align="center" class="footer">CAJERO: <?php echo $_SESSION["phuyu_usuario"]." - ".$_SESSION["phuyu_caja"];?> </td>
	        </tr>
	        <!-- <tr align="center" class="footer">
	            <td colspan="3">NRO. AUTORIZACION: </td>
	        </tr> -->
	        <tr class="footer">
	            <td colspan="3" align="center"> <b>GRACIAS POR SU PEDIDO !</b> </td>
	        </tr>
	        <tr>
	            <td colspan="3">&nbsp;</td>
	        </tr>
	        <tr>
	            <td colspan="3">&nbsp;</td>
	        </tr>
	        <tr>
	            <td colspan="3">&nbsp;</td>
	        </tr>
	    </table>
	</body>
</html>