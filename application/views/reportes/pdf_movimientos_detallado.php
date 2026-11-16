<?php defined('BASEPATH') OR exit('No direct script access allowed');
$esc = function($valor) { return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8'); };
$dinero = function($valor) { return 'S/ '.number_format((float)$valor, 2, '.', ','); };
$ingresos = 0; $egresos = 0; $recibos = []; $pagado = 0; $entregado = 0; $vuelto = 0; $total = 0;
foreach ($movimientos as $m) {
    if ((int)($m['estado'] ?? 1) === 0) { continue; }
    if ((int)$m['tipomovimiento'] === 1) { $ingresos += (float)$m['importe_pago']; }
    else { $egresos += (float)$m['importe_pago']; }
    $pagado += (float)$m['importe_pago']; $entregado += (float)$m['importe_entregado']; $vuelto += (float)$m['vuelto'];
    if (!isset($recibos[$m['codmovimiento']])) { $total += (float)$m['total_movimiento']; $recibos[$m['codmovimiento']] = true; }
}
?>
<table width="100%" cellpadding="6" cellspacing="0">
 <tr>
  <?php if (!empty($logo_pdf)): ?>
  <td width="20%" align="left" valign="middle"><img src="<?php echo $esc($logo_pdf); ?>" width="<?php echo (int)$logo_ancho; ?>" height="<?php echo (int)$logo_alto; ?>" /></td>
  <?php endif; ?>
  <td width="<?php echo !empty($logo_pdf) ? '48%' : '68%'; ?>" valign="middle"><b style="font-size:13pt;color:#241631;"><?php echo $esc($nombreEmpresa); ?></b><?php if (!empty($ruc_empresa)): ?><br><span style="font-size:9pt;color:#6f6682;">RUC: <?php echo $esc($ruc_empresa); ?></span><?php endif; ?><br><span style="font-size:8pt;color:#82788e;">Administración y control de caja</span></td>
  <td width="32%" align="right" valign="middle"><span style="font-size:8pt;color:#6f6682;">REPORTE DE TESORERÍA</span><br><b style="font-size:11pt;color:#581c87;"><?php echo $esc($caja_nombre ?? ''); ?></b><br><span style="font-size:8pt;color:#6f6682;">Moneda: soles (PEN)</span></td>
 </tr>
</table>
<table width="100%" cellpadding="0" cellspacing="0"><tr><td bgcolor="#581c87" height="2" style="font-size:2pt;line-height:2pt;">&nbsp;</td></tr></table>
<table width="100%" cellpadding="12" cellspacing="0">
 <tr>
  <td width="70%" bgcolor="#581c87" style="color:#ffffff;"><span style="font-size:7pt;color:#e9d8fa;">TESORERÍA / REPORTE DE CAJA</span><br><b style="font-size:19pt;color:#ffffff;">Movimientos de caja</b></td>
  <td width="30%" bgcolor="#581c87" align="right"><b style="font-size:11pt;color:#ffffff;">DETALLE DE OPERACIONES</b><br><span style="font-size:8pt;color:#e9d8fa;">Ingresos y egresos de caja</span></td>
 </tr>
</table>
<table width="100%" cellpadding="7" cellspacing="0">
 <tr>
  <td width="66%" style="border-bottom:1px solid #e4d4ff;"><b style="font-size:8pt;color:#581c87;">DATOS DE LA CONSULTA</b><br><span style="font-size:8pt;color:#71647e;">Todas las aperturas de <?php echo $esc($caja_nombre ?? 'esta caja'); ?><?php if (!empty($usuario_exporta)) { echo ' - Exportado por: '.$esc($usuario_exporta); } ?></span></td>
  <td width="34%" align="right" bgcolor="#faf7ff" style="border:1px solid #e4d4ff;"><span style="font-size:8pt;color:#71647e;">Período: <b><?php echo date('d/m/Y', strtotime($desde)); ?> al <?php echo date('d/m/Y', strtotime($hasta)); ?></b><br>Exportado: <?php echo $esc($exportado_en ?? date('d/m/Y H:i')); ?></span></td>
 </tr>
</table>
<?php if (!empty($buscar)): ?><p style="font-size:7pt;color:#82788e;">Búsqueda aplicada: <?php echo $esc($buscar); ?></p><?php endif; ?>
<table width="100%" cellpadding="9" cellspacing="0">
 <tr>
  <td width="20%" bgcolor="#f4edff"><span style="color:#581c87;">RECIBOS ACTIVOS</span><br><b style="font-size:13pt;"><?php echo count($recibos); ?></b></td>
  <td width="27%" bgcolor="#f0f7f3"><span style="color:#16734b;">INGRESOS</span><br><b style="font-size:13pt;color:#16734b;"><?php echo $dinero($ingresos); ?></b></td>
  <td width="26%" bgcolor="#fcf3f3"><span style="color:#b23838;">EGRESOS</span><br><b style="font-size:13pt;color:#b23838;"><?php echo $dinero($egresos); ?></b></td>
  <td width="27%" bgcolor="#f4edff"><span style="color:#581c87;">NETO DEL PERÍODO</span><br><b style="font-size:13pt;color:#581c87;"><?php echo $dinero($ingresos-$egresos); ?></b></td>
 </tr>
</table>
<p style="font-size:7pt;color:#82788e;">Una fila por forma de pago. Los ANULADOS se muestran como historial, conservan su fecha original y no suman a los totales. El total del recibo se muestra una sola vez. El neto no incluye el saldo inicial.</p>
<p style="font-size:8pt;color:#581c87;"><b>DETALLE DE MOVIMIENTOS</b></p>
<?php ob_start(); ?>
<table width="100%" cellpadding="5" cellspacing="0" style="font-size:8pt;">
 <thead><tr bgcolor="#4c1d95" style="color:#ffffff;font-size:7pt;font-weight:bold;text-align:center;">
  <th width="3%">#</th><th width="9%">Fecha / Hora</th><th width="11%">Recibo / Tipo</th><th width="14%">Comprobante relacionado</th><th width="14%">Socio / Razón social</th><th width="12%">Concepto</th><th width="10%">Forma pago</th><th width="7%">Pagado</th><th width="7%">Entregado</th><th width="6%">Vuelto</th><th width="7%">Total</th>
 </tr></thead>
 <tbody>
<?php $cabecera_detalle = ob_get_clean(); echo $cabecera_detalle; ?>
 <?php $vistos = []; foreach ($movimientos as $i=>$m): $anulado = (int)($m['estado'] ?? 1) === 0; $primero = !isset($vistos[$m['codmovimiento']]); $vistos[$m['codmovimiento']] = true; ?>
 <tr style="<?php echo $anulado ? 'color:#b71c1c;text-decoration:line-through;' : ''; ?>" bgcolor="<?php echo $anulado ? '#fff0f0' : ($i%2 ? '#f7f5fa' : '#ffffff'); ?>" nobr="true">
  <td width="3%" align="center"><?php echo $i+1; ?></td>
  <td width="9%" align="center"><?php echo $esc($m['fechamovimiento']); ?><br><span style="font-size:7pt;color:#82788e;"><?php echo $esc($m['horamovimiento'] ?: '--:--:--'); ?></span></td>
  <td width="11%"><b><?php echo $esc($m['seriecomprobante'].'-'.$m['nrocomprobante']); ?></b><br><span style="color:<?php echo (int)$m['tipomovimiento']===1 ? '#16734b' : '#b23838'; ?>;"><?php echo (int)$m['tipomovimiento']===1 ? 'Ingreso' : 'Egreso'; ?></span><?php if ($anulado) { echo '<br><b style="color:#b71c1c;">ANULADO</b>'; } ?></td>
  <td width="14%"><span style="font-size:7pt;color:#82788e;"><?php echo $esc($m['tipo_comprobante_referencia'] ?? ''); ?></span><br><b><?php echo $esc($m['comprobante_referencia'] ?: '-'); ?></b><?php if (!empty($m['fecha_comprobante_referencia'])) { echo '<br><span style="font-size:7pt;">'.$esc($m['fecha_comprobante_referencia']).'</span>'; } ?></td>
  <td width="14%"><?php echo $esc($m['razonsocial']); ?></td>
  <td width="12%"><?php echo $esc($m['concepto_caja']); ?></td>
  <td width="10%" align="center"><?php echo $esc($m['tipopago']); ?></td>
  <td width="7%" align="right"><?php echo $dinero($m['importe_pago']); ?></td>
  <td width="7%" align="right"><?php echo $dinero($m['importe_entregado']); ?></td>
  <td width="6%" align="right"><?php echo $dinero($m['vuelto']); ?></td>
  <td width="7%" align="right"><b><?php echo $primero ? $dinero($m['total_movimiento']) : ''; ?></b></td>
 </tr>
 <?php
 // Limitar el DOM de TCPDF: cada bloque contiene como máximo 40 filas.
 // Los acumuladores y recibos vistos se conservan entre bloques.
 if (($i + 1) % 40 === 0 && ($i + 1) < count($movimientos)) {
     echo '</tbody></table><!-- phuyu-pdf-block -->'.$cabecera_detalle;
 }
 endforeach; ?>
 <?php if (empty($movimientos)): ?><tr><td colspan="11" align="center">No se encontraron movimientos con los filtros seleccionados.</td></tr><?php endif; ?>
 <tr bgcolor="#f4edff" style="color:#581c87;font-size:7pt;font-weight:bold;" nobr="true"><td colspan="7" width="73%" align="right">TOTALES DE MOVIMIENTOS ACTIVOS (S/)</td><td width="7%" align="right"><?php echo number_format($pagado, 2, '.', ','); ?></td><td width="7%" align="right"><?php echo number_format($entregado, 2, '.', ','); ?></td><td width="6%" align="right"><?php echo number_format($vuelto, 2, '.', ','); ?></td><td width="7%" align="right"><?php echo number_format($total, 2, '.', ','); ?></td></tr>
 </tbody>
</table>
