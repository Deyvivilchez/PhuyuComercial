<?php
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="ReporteVentas' . date('Y-m-d') . '.xls"');
header('Cache-Control: max-age=0');
?>

<table border="1">
    <tr>
        <th colspan="13"> 
            <b>REPORTE DE VENTAS <?php echo utf8_decode($_SESSION["phuyu_empresa"]);?></b>
        </th>
    </tr>
    <tr>
        <th colspan="13">
            <b style="font-size:9px"><?php echo $fecha;?></b>
        </th>
    </tr>
    <?php
        foreach ($socios as $key => $value){?> 
            <tr>
                <td colspan="13"><strong><?php echo "CLIENTE: ".utf8_decode($value["razonsocial"])." | DIRECCION: ".utf8_decode($value["direccion"]); ?></strong></td>
            </tr>

            <?php
                $lista = $this->db->query("select personas.documento,kardex.cliente,kardex.codkardex, kardex.codcomprobantetipo, kardex.seriecomprobante,kardex.condicionpago, kardex.nrocomprobante, kardex.fechacomprobante,kardex.valorventa,kardex.igv, kardex.descglobal, kardex.importe,kardex.condicionpago, comprobantes.descripcion as tipo from kardex.kardex as kardex inner join public.personas as personas on (kardex.codpersona=personas.codpersona) inner join caja.comprobantetipos as comprobantes on(kardex.codcomprobantetipo=comprobantes.codcomprobantetipo) where kardex.codpersona=".$value["codpersona"]." and kardex.fechacomprobante>='".$this->request->fechadesde."' and kardex.fechacomprobante<='".$this->request->fechahasta."' and kardex.codmovimientotipo=20 ".$almacen." and kardex.codsucursal=".$this->request->codsucursal." and kardex.estado=1 order by kardex.fechacomprobante, kardex.codcomprobantetipo, kardex.seriecomprobante, kardex.nrocomprobante")->result_array();
            ?>
            <tr>
                <td>#</td>
                <td>FECHA</td>
                <td colspan="2">DOCUMENTO</td>
                <td>SUBTOTAL</td>
                <td>DESCUENTO</td>
                <td>IGV</td>
                <td>TOTAL</td>
                <td colspan="5">CONDICION</td>
            </tr>
            <?php 
                $item = 0; $valorventa = 0; $descglobal = 0; $igv = 0; $importe = 0;
                    foreach($lista as $value){ $item++; 
                        $valorventa = $valorventa + $value["valorventa"]; $descglobal = $descglobal + $value["descglobal"];
                        $igv = $igv + $value["igv"]; $importe = $importe + $value["importe"];
                        if ($value["condicionpago"]==1) {
                            $condicion = "CONTADO";
                        }else{
                            $condicion = "CREDITO";
                        }
                    ?>
                        <td style="background: #ddd"><strong><?php echo $item;?></strong></td>
                        <td style="background: #ddd"><strong><?php echo $value["fechacomprobante"];?></strong></td>
                        <td style="background: #ddd" colspan="2"><strong><?php echo $value["seriecomprobante"]."-".$value["nrocomprobante"];?></strong></td>
                        <td style="background: #ddd"><strong><?php echo number_format($value["valorventa"],2);?></strong></td>
                        <td style="background: #ddd"><strong><?php echo number_format($value["descglobal"],2);?></strong></td>
                        <td style="background: #ddd"><strong><?php echo number_format($value["igv"],2);?></strong></td>
                        <td style="background: #ddd"><strong><?php echo number_format($value["importe"],2);?></strong></td>
                        <td style="background: #ddd" colspan="5"><strong><?php echo $condicion;?></strong></td>
                <?php    

                if (!empty($tipos)) { 
                    $detalle = $this->db->query("select kd.*,p.descripcion as producto,u.descripcion as unidad,p.codigo from kardex.kardexdetalle as kd inner join almacen.productos as p on(kd.codproducto=p.codproducto) inner join almacen.unidades as u on(kd.codunidad=u.codunidad) where kd.codkardex=".$value["codkardex"]." and kd.estado=1 order by kd.item")->result_array();


                ?>
               <tr>
                    <td style="font-size: 10;font-weight: 700">CANT</td>
                    <td colspan="8" style="font-size: 10;font-weight: 700">DESCRIPCION DETALLE VENTA</td>
                    <td style="font-size: 10;font-weight: 700">UNI.MED</td>
                    <td style="font-size: 10;font-weight: 700">P.UNITARIO</td>
                    <td style="font-size: 10;font-weight: 700">IGV</td>
                    <td style="font-size: 10;font-weight: 700">IMPORTE</td>
               </tr>
               <?php 
                    foreach ($detalle as $v) { ?>
                    <tr>
                        <td style="font-size: 10;font-weight: 700"><?php echo number_format($v["cantidad"],2);?></td>
                        <td style="font-size: 10;font-weight: 700" colspan="8"><?php echo utf8_decode($v["codigo"]." - ".$v["producto"].' '.$v["descripcion"]);?></td>
                        <td style="font-size: 10;font-weight: 700"><?php echo utf8_decode($v["unidad"]);?></td>
                        <td style="font-size: 10;font-weight: 700"><?php echo number_format($v["preciounitario"],2);?></td>
                        <td style="font-size: 10;font-weight: 700"><?php echo number_format($v["igv"],2);?></td>
                        <td style="font-size: 10;font-weight: 700"><?php echo number_format($v["subtotal"],2);?></td>
                    </tr> 
            <?php
                    }        
                }
            }  
            ?>
            <tr>
                <td style="color:#d9534f;text-align:right" colspan="4"> <b>TOTALES S/:</td>
                <td style="color:#d9534f"><?php echo number_format($valorventa,2)?></td>
                <td style="color:#d9534f"><?php echo number_format($descglobal,2)?></td>
                <td style="color:#d9534f"><?php echo number_format($igv,2)?></td>
                <td style="color:#d9534f"><?php echo number_format($importe,2)?></td>
            </tr>
        <?php 
        }
    ?>
</table>