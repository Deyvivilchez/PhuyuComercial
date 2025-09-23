<!DOCTYPE html>
<html>
	<link href="http://allfont.es/allfont.css?fonts=agency-fb" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" type="text/css" href="<?php echo base_url();?>public/css/ticket/ticket.css">
	<script language="javascript">
	    function printThis() {
	        window.print(); return false;
	    }
	</script>

	<body onLoad="printThis();">
		<?php $linea = '--------------------------------------------------------------------'; ?>
		<table  width="260" border="0" align="center">
			<tr>
	            <!-- <td colspan="3" align="center" class="Cabecera0"> <img src="<?php echo base_url();?>public/img/empresa/<?php echo $empresa[0]['foto'];?>" style="height:80px;"> </td> -->
	        </tr>
	        <tr>
	            <td colspan="3" align="center" class="Cabecera0"> <?php echo $empresa[0]['razonsocial'];?> </td>
	        </tr>
	        <tr>
	            <td colspan="3" align="center" class="Cabecera2"><?php echo utf8_decode($sucursal[0]['direccion']);?> </td>
	        </tr>
	        <tr>
	            <td colspan="3" align="center" class="Cabecera2-numeros">RUC: <?php echo $empresa[0]['documento'];?></td>
	        </tr>
	        <tr>
	            <td colspan="3" align="center" class="Cabecera2-numeros"><?php echo $sucursal[0]['telefonos'];?></td>
	        </tr>
	        <tr class="Linea"><td colspan="3" align="center"><?php echo $linea ?> </td></tr>
	        <tr>
	            <td align="left" class="Cabecera2" colspan="2"> FECHA: <?php echo $venta[0]['fechacomprobante'];?></td>
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
            	if ($_SESSION["netix_rubro"]==1) { ?>
            		<tr>
		                <td align="left" class="InfoVer" colspan="3">NRO PLACA: <?php echo $venta[0]["nroplaca"];?></td>
		            </tr>
            	<?php }

            	if ($venta[0]["condicionpago"]==2) { ?>
            		<tr>
		                <td align="left" class="InfoVer" colspan="3">CONDICION DE PAGO: AL CREDITO</td>
		            </tr>
		            <tr>
		                <td align="left" class="InfoVer" colspan="3">
		                    FECHA VENCIMIENTO: <?php echo $credito[0]["fechavencimiento"];?>
		                </td>
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
									<td align="left" class="Detallenumeritos">
										&nbsp;<span style="font-size:14px;"><?php echo round($value["cantidad"],2)?></span>&nbsp;<?php echo substr($value["unidad"],0,3);?>
									</td>
									<td align="left" class="Detalle"><?php echo $value["producto"];?></td>
									<td align="right" class="Detallenumeritos" style="font-size:13px;"><?php echo number_format($value["preciounitario"],2);?>&nbsp;</td>
									<td align="right" class="Detallenumeritos" style="font-size:13px;"><?php echo number_format($value["subtotal"],2);?></td>
								</tr>
							<?php }
						?>

						<tr class="Linea"><td colspan="4" align="center"> <?php echo $linea ?> </td></tr>

						<?php 
							if ($venta[0]["codcomprobantetipo"]==10 || $venta[0]["codcomprobantetipo"]==12) { ?>
								<tr>
		                            <td align="right"  class="Total"colspan="3">OP GRAVADAS S/:</td>
		                            <td align="right" class="numeritos"><?php echo number_format($totales[0]["gravado"],2);?></td>
		                        </tr>
		                        <tr>
		                            <td colspan="3" align="right" class="Total">OP INAFECTAS S/:</td>
		                            <td align="right" class="numeritos"> <?php echo number_format($totales[0]["inafecto"],2);?></td>
		                        </tr>
		                        <tr>
		                            <td colspan="3" align="right" class="Total">OP EXONERADAS S/:</td>
		                            <td align="right" class="numeritos"><?php echo number_format($totales[0]["exonerado"],2);?></td>
		                        </tr>
		                        <tr>
		                            <td colspan="3" align="right" class="Total">OP GRATUITAS S/:</td>
		                            <td align="right" class="numeritos"> <?php echo number_format($totales[0]["gratuito"],2);?> </td>
                        		</tr>
							<?php }else{ ?>
								<tr>
		                            <td colspan="3" align="right" class="Total">SUB TOTAL S/:</td>
		                            <td align="right" class="numeritos"> <?php echo number_format($venta[0]["valorventa"],2);?> </td>
                        		</tr>
							<?php }
						?>
                        
                        <tr>
                            <td colspan="3" align="right" class="Total">DESCUENTO S/:</td>
                            <td align="right" class="numeritos"> <?php echo number_format($venta[0]["descglobal"],2);?> </td>
                        </tr>
                        <tr>
                            <td colspan="3" align="right" class="Total">IGV S/:</td>
                            <td align="right" class="numeritos"> <?php echo number_format($venta[0]["igv"],2);?> </td>
                        </tr>
                        <tr>
                            <td colspan="3" align="right" class="Total">TOTAL S/:</td>
                            <td align="right" class="numeritos"><?php echo number_format($venta[0]["importe"],2);?> </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td colspan="4" align="left" class="Total"> <?php echo $texto_importe; ?></td>
            </tr>

            <tr class="Linea"><td colspan="4" align="center"> <?php echo $linea ?> </td></tr>

            <?php 
            	if ($venta[0]["codcomprobantetipo"]==10 || $venta[0]["codcomprobantetipo"]==12) { ?>
            		<tr>
			            <td colspan="3" align="center" class="footer"> <img src="<?php echo base_url();?>sunat/webnetix/qrcode.png" style="height:80px;"> </td>
			        </tr>
			        <tr align="center" class="footer">
			            <td colspan="3">CONSULTA TU COMPROBANTE EN http://netixperu.com/sunat</td>
			        </tr>
            	<?php }
            ?>
	        <tr>
	            <td colspan="3" align="center" class="footer">CAJERO: <?php echo $_SESSION["netix_usuario"]." - ".$_SESSION["netix_caja"];?> </td>
	        </tr>
	        <tr>
	            <td colspan="3" align="center" class="footer"> <?php echo $vendedor[0]["razonsocial"];?> </td>
	        </tr>
	        <tr align="center" class="footer">
	            <td colspan="3">NRO. AUTORIZACION: 0183845126059</td>
	        </tr>
	        <tr class="footer">
	            <td colspan="3" align="center"> <b>GRACIAS POR SU COMPRA !</b> </td>
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