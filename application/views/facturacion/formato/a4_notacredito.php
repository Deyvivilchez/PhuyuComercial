<?php
// La nota comparte el diseño de la factura: cabecera, marca de agua y pie.
// Adaptar los datos del controlador sin duplicar la plantilla ni sus estilos.
$modo_pdf = true;
$empresa = $empresa[0] ?? [];
$sucursal = $sucursal[0] ?? [];
$principal = $principal[0] ?? [];
$parametros = $parametros[0] ?? [];
$venta = $venta[0] ?? [];
$venta['comprobante'] = $venta['comprobante'] ?? 'NOTA DE CRÉDITO ELECTRÓNICA';
$venta['oficial'] = '07';
$totales = $totales[0] ?? [];
$credito = $credito[0] ?? [];
$formato = $formatoNota ?? [];
$nombre_empresa = $nombre;
$empleado = [];
$cuentascorrientes = [];
$fechavencimiento = $credito['fechavencimiento'] ?? '';
$qr_src = $qrSrc;
$importe_en_letras = $textoNota;
$nota_credito = [
    'referencia' => ($venta['comprobante_ref'] ?? '') . ' ' . ($venta['seriecomprobante_ref'] ?? '') . '-' . ($venta['nrocomprobante_ref'] ?? ''),
    'motivo' => $venta['motivo'] ?? '',
    'descripcion' => $venta['descripcion'] ?? ''
];
$logo_src = '';
if (is_file($logoNota)) {
    $imagen = @getimagesize($logoNota);
    if ($imagen && in_array($imagen['mime'], ['image/jpeg','image/png','image/gif','image/webp'], true)) {
        $logo_src = 'data:' . $imagen['mime'] . ';base64,' . base64_encode(file_get_contents($logoNota));
    }
}
$slogan = !empty($formato['slogan']) ? $formato['slogan'] : ($parametros['slogan'] ?? '');
$publicidad = !empty($formato['publicidad']) ? $formato['publicidad'] : ($parametros['publicidad'] ?? '');
include __DIR__ . '/../../reportes/ventas/a4comprobante.php';
