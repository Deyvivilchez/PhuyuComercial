<?php
require_once __DIR__ . '/../../../helpers/descripcion_item_helper.php';
$e = static function ($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); };
$fecha = static function ($value) {
    $ts = $value ? strtotime($value) : false;
    return $ts ? date('d/m/Y', $ts) : '—';
};
$numero = static function ($value) { return number_format((float)$value, 2); };
$serie = ($guia['seriecomprobante'] ?? '') . ' - ' . ($guia['nrocomprobante'] ?? '');
$ubigeoPartida = implode(' - ', array_filter([$guia['distpartida'] ?? '', $guia['provpartida'] ?? '', $guia['deppartida'] ?? '']));
$ubigeoLlegada = implode(' - ', array_filter([$guia['distdestino'] ?? '', $guia['provdestino'] ?? '', $guia['depdestino'] ?? '']));
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Guía <?= $e($serie) ?></title>
<style>
    @page { margin: 10mm 9mm 20mm; }
    body { margin: 0; font-family: DejaVu Sans, sans-serif; font-size: 8pt; color: #241f31; }
    table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    td { vertical-align: top; }
    .watermark { position: fixed; top: 68mm; left: 16mm; width: 160mm; text-align: center; z-index: -1; }
    .watermark img { max-width: 100%; max-height: 150mm; opacity: .06; }
    .header { margin-bottom: 4px; }
    .brand-col { width: 62%; padding-right: 10px; }
    .doc-col { width: 38%; }
    .brand { border: 1px solid #e4d4ff; border-radius: 12px; padding: 9px 11px; }
    .logo { max-width: 150px; max-height: 48px; margin-bottom: 4px; }
    .company { font-size: 12px; font-weight: bold; color: #1d1430; margin-bottom: 4px; }
    .meta { color: #6f6682; font-size: 8px; line-height: 1.55; overflow-wrap: break-word; }
    .ribbon { background: #581c87; color: white; border-radius: 0 18px 0 18px; padding: 9px 11px; margin-bottom: 5px; }
    .doc-title { font-size: 14px; line-height: 1.15; font-weight: bold; text-transform: uppercase; }
    .doc-number { font-size: 12px; font-weight: bold; margin-top: 5px; }
    .doc-box { background: #faf7ff; border: 1px solid #e4d4ff; border-radius: 10px; padding: 6px 10px; line-height: 1.5; font-size: 8px; }
    .ruc { color: #581c87; font-size: 11px; font-weight: bold; margin-bottom: 3px; }
    .section-title { color: #581c87; font-size: 10px; font-weight: bold; border-bottom: 2px solid #dcc3ff; padding-bottom: 3px; margin: 7px 0 4px; }
    .panel { border: 1px solid #e4d4ff; border-radius: 9px; padding: 4px 7px; }
    .info td { padding: 2px 4px; font-size: 9px; line-height: 1.4; overflow-wrap: break-word; }
    .label { color: #7b728a; font-weight: bold; }
    .route td { width: 50%; padding: 6px 8px; border: 1px solid #e4d4ff; line-height: 1.5; font-size: 9px; }
    .route-label { color: #581c87; font-size: 8px; font-weight: bold; margin-bottom: 5px; }
    .address { font-weight: bold; margin-bottom: 3px; overflow-wrap: break-word; }
    .items { margin-top: 6px; }
    .items thead { display: table-header-group; }
    .items th { background: #4c1d95; color: white; border: 1px solid #d9c2ff; font-size: 8px; padding: 6px 5px; text-align: left; }
    .items td { border: 1px solid #eadcff; padding: 5px 5px; font-size: 8.5px; line-height: 1.3; overflow-wrap: break-word; }
    .items tr { page-break-inside: avoid; }
    .items tr:nth-child(even) td { background: #fcf9ff; }
    .items .right { text-align: right; }
    .items .center { text-align: center; }
    .item-note { color: #6f6682; font-size: 8px; margin-top: 3px; line-height: 1.3; white-space: normal; }
    .summary { margin-top: 6px; padding: 7px 10px; background: #faf7ff; border: 1px solid #e4d4ff; color: #581c87; font-size: 9px; text-align: right; }
    .keep { page-break-inside: avoid; }
    .observations { border: 1px solid #e4d4ff; border-left: 3px solid #6d28d9; border-radius: 8px; padding: 8px 10px; line-height: 1.5; overflow-wrap: break-word; }
    .advert { text-align: center; color: #7b728a; font-size: 8px; margin-top: 12px; }
</style>
</head>
<body>
<?php if ($logo_src !== ''): ?><div class="watermark"><img src="<?= $e($logo_src) ?>"></div><?php endif; ?>
<table class="header"><tr>
    <td class="brand-col"><div class="brand">
        <?php if ($logo_src !== ''): ?><img class="logo" src="<?= $e($logo_src) ?>" alt="Logo"><br><?php endif; ?>
        <div class="company"><?= $e($nombre_empresa) ?></div>
        <div class="meta">
            <?php if (!empty($principal) && ($sucursal['codsucursal'] ?? '') != ($principal['codsucursal'] ?? '')): ?>
                <b>Principal:</b> <?= $e($principal['direccion'] ?? '') ?><br>
                <b>Sucursal:</b>
            <?php endif; ?>
            <?= $e($sucursal['direccion'] ?? '') ?><br>
            <?php if (!empty($sucursal['telefonos'])): ?>Tel.: <?= $e($sucursal['telefonos']) ?><br><?php endif; ?>
            <?php if (!empty($empresa['email'])): ?><?= $e($empresa['email']) ?><br><?php endif; ?>
            <?php if ($slogan !== ''): ?><i><?= $e($slogan) ?></i><?php endif; ?>
        </div>
    </div></td>
    <td class="doc-col">
        <div class="ribbon"><div class="doc-title"><?= $e($guia['comprobante'] ?? 'GUÍA DE REMISIÓN') ?></div><div class="doc-number"><?= $e($serie) ?></div></div>
        <div class="doc-box"><div class="ruc">RUC: <?= $e($empresa['documento'] ?? '') ?></div>
            <b>Fecha de emisión:</b> <?= $e($fecha($guia['fechaguia'] ?? '')) ?><br>
            <b>Inicio del traslado:</b> <?= $e($fecha($guia['fechatraslado'] ?? '')) ?>
        </div>
    </td>
</tr></table>
<div class="keep">
    <div class="section-title">DATOS DEL DESTINATARIO</div>
    <div class="panel"><table class="info">
        <tr><td class="label" style="width:18%">Razón social</td><td style="width:82%" colspan="3"><?= $e($guia['cliente'] ?? '') ?></td></tr>
        <tr><td class="label">DNI / RUC</td><td colspan="3"><?= $e($guia['documento'] ?? '') ?></td></tr>
    </table></div>
</div>
<div class="keep">
    <div class="section-title">DATOS DEL TRASLADO</div>
    <div class="panel"><table class="info">
        <tr><td class="label" style="width:18%">Motivo</td><td style="width:32%"><?= $e($guia['motivo'] ?? '') ?></td><td class="label" style="width:22%">Modalidad</td><td style="width:28%"><?= $e($guia['modalidad'] ?? '') ?></td></tr>
        <tr><td class="label">Peso total bruto</td><td><?= $e($numero($guia['peso'] ?? 0)) ?></td><td class="label">N.º total de bultos</td><td><?= $e($numero($guia['nropaquetes'] ?? 0)) ?></td></tr>
    </table></div>
    <table class="route" style="margin-top:6px"><tr>
        <td><div class="route-label">01 &nbsp; PUNTO DE PARTIDA</div><div class="address"><?= $e($guia['direccionpartida'] ?? '') ?></div><span class="meta"><?= $e($ubigeoPartida) ?></span></td>
        <td><div class="route-label">02 &nbsp; PUNTO DE LLEGADA</div><div class="address"><?= $e($guia['direccionllegada'] ?? '') ?></div><span class="meta"><?= $e($ubigeoLlegada) ?></span></td>
    </tr></table>
</div>
<div class="keep">
    <div class="section-title">TRANSPORTISTA, CONDUCTOR Y VEHÍCULO</div>
    <div class="panel"><table class="info">
        <tr><td class="label" style="width:18%">Transportista</td><td style="width:44%"><?= $e($guia['razonsocialtransportista'] ?? '') ?></td><td class="label" style="width:16%">DNI / RUC</td><td style="width:22%"><?= $e($guia['documentotransportista'] ?? '') ?></td></tr>
        <tr><td class="label">Conductor</td><td><?= $e($guia['razonsocialconductor'] ?? '') ?></td><td class="label">DNI / RUC</td><td><?= $e($guia['documentoconductor'] ?? '') ?></td></tr>
        <tr><td class="label">Placa</td><td><?= $e($guia['nroplaca'] ?? '') ?></td><td class="label">Licencia</td><td><?= $e($guia['licenciaconductor'] ?? '') ?></td></tr>
        <tr><td class="label">Constancia MTC</td><td colspan="3"><?= $e($guia['constancia'] ?? '') ?></td></tr>
    </table></div>
</div>
<div class="section-title">BIENES A TRANSPORTAR</div>
<table class="items">
    <thead><tr><th style="width:6%" class="center">ÍTEM</th><th style="width:54%">DESCRIPCIÓN</th><th style="width:14%">UNIDAD</th><th style="width:13%" class="right">CANTIDAD</th><th style="width:13%" class="right">PESO</th></tr></thead>
    <tbody>
    <?php foreach ($detalle as $item):
        $bloques = phuyu_descripcion_bloques($item['producto'] ?? '');
        $notas = phuyu_descripcion_bloques($item['observacion_item'] ?? '');
        $filas = max(count($bloques), count($notas), 1);
        for ($i = 0; $i < $filas; $i++): ?>
        <tr>
            <td class="center"><?= $i === 0 ? $e(str_pad((string)$item['item'], 2, '0', STR_PAD_LEFT)) : '' ?></td>
            <td><?php if (isset($bloques[$i])): ?><?= nl2br($e($bloques[$i])) ?><?php endif; ?>
                <?php if (isset($notas[$i])): ?><div class="item-note"><b>Observación<?= $i > 0 ? ' (cont.)' : '' ?>:</b> <?= nl2br($e($notas[$i])) ?></div><?php endif; ?></td>
            <td><?= $i === 0 ? $e($item['unidad'] ?? '') : '' ?></td>
            <td class="right"><?= $i === 0 ? $e($numero($item['cantidad'] ?? 0)) : '' ?></td>
            <td class="right"><?= $i === 0 ? $e($numero($item['peso'] ?? 0)) : '' ?></td>
        </tr>
        <?php endfor; endforeach; ?>
    </tbody>
</table>
<div class="summary"><b><?= count($detalle) ?></b> ítem(s) &nbsp; | &nbsp; Peso total bruto: <b><?= $e($numero($guia['peso'] ?? 0)) ?></b> &nbsp; | &nbsp; Bultos: <b><?= $e($numero($guia['nropaquetes'] ?? 0)) ?></b></div>
<?php if (trim((string)($guia['observaciones'] ?? '')) !== ''): ?>
    <div class="section-title">OBSERVACIONES GENERALES</div>
    <?php foreach (phuyu_descripcion_bloques($guia['observaciones']) as $bloque): ?><div class="observations"><?= nl2br($e($bloque)) ?></div><?php endforeach; ?>
<?php endif; ?>
<?php if ($publicidad !== ''): ?><div class="advert"><?= nl2br($e($publicidad)) ?></div><?php endif; ?>
</body>
</html>
