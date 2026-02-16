<!DOCTYPE html>
<html>
<!-- <link href="http://allfont.es/allfont.css?fonts=agency-fb" rel="stylesheet" type="text/css" /> -->
<link rel="stylesheet" type="text/css" href="<?php echo base_url(); ?>public/css/ticket/ticket.css">
<script language="javascript">
    function printThis() {
        window.print();
        return false;
    }
</script>


<body onLoad="printThis();">
    <?php $linea = '--------------------------------------------------------------------'; ?>
    <table width="280" border="0" align="center">
        <tr>
            <td colspan="3" align="center" class="Cabecera0"> <img src="<?php echo $direccionlogo; ?>" style="height:80px;">
            </td>
        </tr>


		<tr>
			<td colspan="3" style="text-align: center; padding: 4px 0 6px 0;">
				
				<!-- Logo empresa tipo ticket / sello -->
				<?php
				if ($logoEmpresa != '') {
					echo "<img src='" . base_url() . "public/img/" . $logoEmpresa . "'
							alt='Logo empresa'
							style='width:80px; height:80px; object-fit:contain; border-radius:5px; display:block; margin:0 auto;' />";
				} else {
					echo "<div style='width:30px; height:30px;
								background:#f3f3f3;
								border-radius:5px;
								display:flex;
								align-items:center;
								justify-content:center;
								color:#555;
								font-weight:bold;
								font-size:7px;
								margin:0 auto;
								text-align:center;'>
								LOGO<br>EMPRESA
						</div>";
				}
				?>
			</td>
		</tr>

        <tr>
            <td colspan="3" align="center" class="Cabecera0"> <?php echo $nombre; ?> </td>
        </tr>
        <tr>
            <td colspan="3" align="center" class="Cabecera2"><?php echo $sucursal[0]['direccion']; ?> </td>
        </tr>
        <tr>
            <td colspan="3" align="center" class="Cabecera2-numeros1">RUC: <?php echo $empresa[0]['documento']; ?></td>
        </tr>

        <tr>
            <td colspan="3" align="center" class="footer">VENDEDOR <?php echo $vendedor[0]['razonsocial']; ?> </td>
        </tr>

        <tr>
            <td colspan="3" align="center" class="footer">USUARIO: <?php echo $_SESSION['phuyu_usuario']; ?> </td>
        </tr>
        <tr>
            <td colspan="3" align="center" class="footer">TELEFONO <?php echo $vendedor[0]['telefono']; ?> </td>
        </tr>
        <tr class="Linea">
            <td colspan="3" align="center"><?php echo $linea; ?> </td>
        </tr>
        <tr>
            <td align="left" class="Cabecera2" colspan="2"> FECHA EMISION: <?php echo $fechavencimiento; ?></td>
            <td align="right" class="Cabecera2"> <?php echo date('H:i:s'); ?></td>
        </tr>
        <tr>
            <td align="left" class="Cabecera2" colspan="2"> FECHA VENCIMIENTO: <?php echo $fechavencimiento; ?></td>
        </tr>
        <tr>
            <td colspan="3" align="center" class="InfoVer"><b> <?php echo $venta[0]['comprobante'] . ' <br> ' . $venta[0]['seriecomprobante'] . '-' . $venta[0]['nrocomprobante']; ?> </b> </td>
        </tr>

        <tr>
            <td align="left" class="InfoVer" colspan="3"> CLIENTE: <?php echo $venta[0]['cliente']; ?> </td>
        </tr>
        <tr>
            <td align="left" class="InfoVer" colspan="3"> DIRECCION: <?php echo $venta[0]['direccion']; ?> </td>
        </tr>
        <tr>
            <td align="left" class="InfoVer" colspan="3">D.N.I / R.U.C: <?php echo $venta[0]['documento']; ?></td>
        </tr>
        <?php 
            	if ($_SESSION["phuyu_rubro"]==1) { ?>
        <tr>
            <td align="left" class="InfoVer" colspan="3">NRO PLACA: <?php echo $venta[0]['nroplaca']; ?></td>
        </tr>
        <?php }

            	if ($venta[0]["condicionpago"]==2) { ?>
        <tr>
            <td align="left" class="InfoVer" colspan="3">CONDICION DE PAGO: AL CREDITO</td>
        </tr>
        <?php }else{ ?>
        <tr>
            <td align="left" class="InfoVer" colspan="3">CONDICION DE PAGO: AL CONTADO</td>
        </tr>
        <?php } ?>
        <tr>
            <td colspan="3" valign="top">
                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                    <tr class="Linea">
                        <td colspan="4" align="center"> <?php echo $linea; ?> </td>
                    </tr>
                    <tr>
                        <td width="10" align="center" class="Detallecab">CANT</td>
                        <td width="240" align="center" class="Detallecab">PRODUCTO</td>
                        <td width="30" align="center" class="Detallecab">P.U.</td>
                        <td width="30" align="center" class="Detallecab">TOTAL</td>
                    </tr>
                    <tr class="Linea">
                        <td colspan="4" align="center"> <?php echo $linea; ?> </td>
                    </tr>
                    <?php 
							foreach ($detalle as $key => $value) { ?>
                    <tr>
                        <td align="left" class="Detallenumeritos1" style="padding-right:5px;">
                            &nbsp;<span style="font-size:14px;"><?php echo round($value['cantidad'], 2); ?></span>&nbsp;<?php echo substr($value['unidad'], 0, 3); ?>
                        </td>
                        <td align="left" class="Detalle"><?php echo $value['producto'] . ' ' . $value['descripcion']; ?></td>
                        <td align="right" class="Detallenumeritos1" style="font-size:13px;"><?php echo number_format($value['preciounitario'], 2); ?>&nbsp;
                        </td>
                        <td align="right" class="Detallenumeritos1" style="font-size:13px;"><?php echo number_format($value['subtotal'], 2); ?></td>
                    </tr>
                    <?php }
						?>

                    <tr class="Linea">
                        <td colspan="4" align="center"> <?php echo $linea; ?> </td>
                    </tr>

                    <?php 
							if ($venta[0]["codcomprobantetipo"]==10 || $venta[0]["codcomprobantetipo"]==12) { ?>
                    <tr>
                        <td align="right" class="Total"colspan="3">OP GRAVADAS S/:</td>
                        <td align="right" class="numeritos1"><?php echo number_format($totales[0]['gravado'] - $venta[0]['igv'], 2); ?></td>
                    </tr>
                    <tr>
                        <td colspan="3" align="right" class="Total">OP INAFECTAS S/:</td>
                        <td align="right" class="numeritos1"> <?php echo number_format($totales[0]['inafecto'], 2); ?></td>
                    </tr>
                    <tr>
                        <td colspan="3" align="right" class="Total">OP EXONERADAS S/:</td>
                        <td align="right" class="numeritos1"><?php echo number_format($totales[0]['exonerado'], 2); ?></td>
                    </tr>
                    <tr>
                        <td colspan="3" align="right" class="Total">OP GRATUITAS S/:</td>
                        <td align="right" class="numeritos1"> <?php echo number_format($totales[0]['gratuito'], 2); ?> </td>
                    </tr>
                    <?php }else{ ?>
                    <tr>
                        <td colspan="3" align="right" class="Total">SUB TOTAL S/:</td>
                        <td align="right" class="numeritos1"> <?php echo number_format($venta[0]['valorventa'], 2); ?> </td>
                    </tr>
                    <?php }
						?>

                    <tr>
                        <td colspan="3" align="right" class="Total">DESCUENTO S/:</td>
                        <td align="right" class="numeritos1"> <?php echo number_format($venta[0]['descglobal'], 2); ?> </td>
                    </tr>
                    <tr>
                        <td colspan="3" align="right" class="Total">IGV S/:</td>
                        <td align="right" class="numeritos1"> <?php echo number_format($venta[0]['igv'], 2); ?> </td>
                    </tr>
                    <tr>
                        <td colspan="3" align="right" class="Total">TOTAL S/:</td>
                        <td align="right" class="numeritos1"><?php echo number_format($venta[0]['importe'], 2); ?> </td>
                    </tr>
                    <?php if($efectivo==1){ ?>
                    <tr>
                        <td colspan="3" align="right" class="Total">EFECTIVO S/: </td>
                        <td align="right" class="numeritos1"><?php echo number_format($detallemovimiento[0]['importeentregado'], 2); ?></td>
                    </tr>
                    <tr>
                        <td colspan="3" align="right" class="Total">VUELTO S/: </td>
                        <td align="right" class="numeritos1"><?php echo number_format($detallemovimiento[0]['vuelto'], 2); ?></td>
                    </tr>
                    <?php } ?>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="4" align="left" class="Total"> <?php echo $texto_importe; ?></td>
        </tr>

        <tr class="Linea">
            <td colspan="4" align="center"> <?php echo $linea; ?> </td>
        </tr>

        <?php 
            	if ($venta[0]["codcomprobantetipo"]==10 || $venta[0]["codcomprobantetipo"]==12) { ?>
        <tr>
            <td colspan="3" align="center" class="footer"> <img
                    src="<?php echo base_url(); ?>sunat/webphuyu/qrcode.png" style="height:80px;"> </td>
        </tr>
        <tr align="center" class="footer">
            <td colspan="4">CONSULTA TU COMPROBANTE EN <?php echo $sucursal[0]['urlconsultacomprobantes']; ?></td>
        </tr>
        <?php }
            ?>
        <?php
        $html = '';
        if ($venta[0]['conleyendaamazonia'] == 1) {
            $html .= '<p align="center" style="font-size:8px">';
            if ($formato[0]['tipoconleyendaamazonia'] == 1) {
                $html .= $sucursal[0]['codleyendapamazonia'] . ' - ' . $sucursal[0]['leyendapamazonia'];
            } elseif ($formato[0]['tipoconleyendaamazonia'] == 2) {
                $html .= $sucursal[0]['codleyendasamazonia'] . ' - ' . $sucursal[0]['leyendasamazonia'];
            } else {
                $html .= $sucursal[0]['codleyendapamazonia'] . ' - ' . $sucursal[0]['leyendapamazonia'] . '<br>' . $sucursal[0]['codleyendasamazonia'] . ' - ' . $sucursal[0]['leyendasamazonia'];
            }
            $html .= '</p>';
        }
        
        ?>
        <tr>
            <td colspan="4" style="font-size: 10px"><?php echo $html; ?></td>
        </tr>
        <!-- <tr align="center" class="footer">
 <td colspan="3">NRO. AUTORIZACION: </td>
 </tr> -->
        <tr class="footer">
            <td colspan="3" align="center"> <b>GRACIAS POR SU COMPRA !</b><br> <b>NO SE ACEPTAN CAMBIOS NI
                    DEVOLUCIONES</b> </td>
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
