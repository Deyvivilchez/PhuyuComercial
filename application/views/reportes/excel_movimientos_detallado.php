<?php defined('BASEPATH') OR exit('No direct script access allowed');

// Generador del reporte descargable. Se usa la libreria Excel del sistema.
require_once APPPATH.'third_party/phuyu_excel/PHPExcel.php';
require_once APPPATH.'third_party/phuyu_excel/PHPExcel/IOFactory.php';

$libro = new PHPExcel();
$libro->getProperties()->setCreator('Phuyu System')->setTitle('Movimientos de caja')->setSubject($caja_nombre);
$hoja = $libro->getActiveSheet();
$hoja->setTitle('Movimientos')->setShowGridlines(false);
$hoja->getTabColor()->setRGB('581C87');
$libro->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
$libro->getDefaultStyle()->getFont()->getColor()->setRGB('243247');
$libro->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
$hoja->getDefaultRowDimension()->setRowHeight(25);
$moneda = '"S/ "#,##0.00;[Red]-"S/ "#,##0.00';
$texto = function($celda, $valor) use ($hoja) {
    // Los textos del usuario nunca se interpretan como formulas de Excel.
    $hoja->setCellValueExplicit($celda, (string)$valor, PHPExcel_Cell_DataType::TYPE_STRING);
};
$franja = function($rango, $color) use ($hoja) {
    $hoja->getStyle($rango)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($color);
};

$colCabecera = !empty($logo_pdf) ? 'D' : 'A';
foreach ([2, 3, 4, 5] as $filaCabecera) {
    $hoja->mergeCells($colCabecera.$filaCabecera.':J'.$filaCabecera);
    $hoja->mergeCells('M'.$filaCabecera.':R'.$filaCabecera);
}

if (!empty($logo_pdf)) {
    $imagen = new PHPExcel_Worksheet_Drawing();
    $imagen->setName('Logo de la empresa')->setDescription('Identidad de la empresa del reporte');
    $imagen->setPath($logo_pdf)->setCoordinates('A2')->setOffsetX(8)->setOffsetY(4);
    $imagen->setResizeProportional(false)->setWidth($logo_ancho)->setHeight($logo_alto);
    $imagen->setWorksheet($hoja);
}
$texto($colCabecera.'2', 'Movimientos de caja');
$hoja->getStyle($colCabecera.'2')->getFont()->setSize(16)->setBold(true)->getColor()->setRGB('581C87');
$hoja->getRowDimension(2)->setRowHeight(32);
$texto($colCabecera.'3', $empresa.' · '.$caja_nombre);
$hoja->getStyle($colCabecera.'3')->getFont()->setBold(true);
$texto($colCabecera.'4', 'Período: '.date('d/m/Y', strtotime($desde)).' al '.date('d/m/Y', strtotime($hasta)).(!empty($ruc_empresa) ? ' · RUC: '.$ruc_empresa : ''));
$texto('M2', 'PHUYU SYSTEM');
$hoja->getStyle('M2')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('581C87');
$texto('M3', 'Exportado desde Phuyu System');
$texto('M4', 'Exportado: '.($exportado_en ?? date('d/m/Y H:i')).' - Hora de Perú');
$texto('M5', 'Usuario: '.($usuario_exporta ?? ''));
$alcance = isset($filtros['vista']) && $filtros['vista'] === 'actual' ? 'Apertura actual' : 'Historial de esta caja';
$criterios = [$alcance];
if (!empty($filtros['buscar'])) { $criterios[] = 'Búsqueda: '.$filtros['buscar']; }
if (!empty($filtros['tipomovimiento'])) { $criterios[] = (int)$filtros['tipomovimiento'] === 1 ? 'Solo ingresos' : 'Solo egresos'; }
if (!empty($filtros['codtipopago'])) {
    foreach ($movimientos as $movimiento) {
        // El filtro se aplica a los recibos; se conservan todas sus formas de pago.
        if ((int)$movimiento['codtipopago'] === (int)$filtros['codtipopago']) {
            $criterios[] = 'Recibos con pago: '.$movimiento['tipopago']; break;
        }
    }
}
$texto($colCabecera.'5', implode(' · ', $criterios));
$hoja->getStyle('A4:R5')->getFont()->setItalic(true)->getColor()->setRGB('7B728A');
$hoja->getStyle('A5:R5')->getAlignment()->setWrapText(false);
$hoja->getRowDimension(5)->setRowHeight(30);

$tarjetas = [
    ['A7:D7','A8:D8','B7','B8','# Recibos','F4EDFF','581C87'],
    ['E7:F7','E8:F8','E7','E8','↑ Ingresos','E8F6EF','16734B'],
    ['G7:H7','G8:H8','G7','G8','↓ Egresos','FCEDEC','B23838'],
    ['I7:J7','I8:J8','I7','I8','= Neto del período','F4EDFF','581C87']
];
foreach ($tarjetas as $tarjeta) {
    list($titulo, $valor, $t, $v, $etiqueta, $fondo, $color) = $tarjeta;
    $texto($t, $etiqueta); $franja($titulo, $fondo); $franja($valor, $fondo);
    $hoja->getStyle($titulo)->getFont()->setBold(true)->getColor()->setRGB($color);
    $hoja->getStyle($valor)->getFont()->setSize(16)->setBold(true)->getColor()->setRGB($color);
    $hoja->getStyle($titulo)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
}
$hoja->getRowDimension(8)->setRowHeight(34);
$texto('A10', 'Importes en soles. Una fila por forma de pago. Total recibo se muestra una sola vez. Neto = ingresos menos egresos del filtro; no incluye saldo inicial.');
$hoja->getStyle('A10')->getFont()->setItalic(true)->getColor()->setRGB('7B728A');

$encabezados = ['#','Fecha','Hora','Movimiento','Recibo','Comprobante ref.','Socio / Razón social','Concepto','Descripción / Referencia','Forma de pago','Fecha doc. banco','N.º doc. banco','Importe pagado','Entregado','Vuelto','Total recibo','Apertura','ID movimiento'];
$hoja->fromArray($encabezados, null, 'A11');
$franja('A11:R11', '581C87');
$hoja->getStyle('A11:R11')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
$hoja->getStyle('A11:R11')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)->setWrapText(true);
$hoja->getRowDimension(11)->setRowHeight(32);
$anchos = ['A'=>6,'B'=>13,'C'=>11,'D'=>15,'E'=>22,'F'=>24,'G'=>38,'H'=>40,'I'=>40,'J'=>24,'K'=>16,'L'=>23,'M'=>19,'N'=>19,'O'=>17,'P'=>19,'Q'=>12,'R'=>17];
foreach ($anchos as $columna=>$ancho) { $hoja->getColumnDimension($columna)->setWidth($ancho); }

$fila = 12; $recibos = [];
foreach ($movimientos as $indice=>$m) {
    $id = (int)$m['codmovimiento'];
    $ingreso = (int)$m['tipomovimiento'] === 1;
    $hoja->setCellValue('A'.$fila, $indice+1);
    $hoja->setCellValue('B'.$fila, PHPExcel_Shared_Date::PHPToExcel(strtotime($m['fechamovimiento'])));
    // Hora guardada como fraccion del dia, sin depender de la zona horaria de Excel.
    if (!empty($m['horamovimiento'])) {
        $hora = array_map('floatval', explode(':', $m['horamovimiento']));
        $hoja->setCellValue('C'.$fila, ($hora[0]*3600+$hora[1]*60+$hora[2])/86400);
    }
    $texto('D'.$fila, $ingreso ? '↑ Ingreso' : '↓ Egreso');
    foreach (['E'=>$m['seriecomprobante'].'-'.$m['nrocomprobante'], 'F'=>implode("\n", array_filter([$m['tipo_comprobante_referencia'] ?? '', $m['comprobante_referencia'], $m['fecha_comprobante_referencia'] ?? ''])), 'G'=>$m['razonsocial'], 'H'=>$m['concepto_caja'], 'I'=>$m['referencia'], 'J'=>$m['tipopago'], 'L'=>$m['nrodocbanco']] as $columna=>$valor) { $texto($columna.$fila, $valor); }
    if (!empty($m['fechadocbanco'])) { $hoja->setCellValue('K'.$fila, PHPExcel_Shared_Date::PHPToExcel(strtotime($m['fechadocbanco']))); }
    foreach (['M'=>'importe_pago','N'=>'importe_entregado','O'=>'vuelto'] as $columna=>$campo) { $hoja->setCellValue($columna.$fila, (float)$m[$campo]); }
    if (!isset($recibos[$id])) { $hoja->setCellValue('P'.$fila, (float)$m['total_movimiento']); $recibos[$id] = true; }
    $hoja->setCellValue('Q'.$fila, (int)$m['codcontroldiario']);
    $hoja->setCellValue('R'.$fila, $id);
    if ($indice % 2 === 1) { $franja('A'.$fila.':R'.$fila, 'FAF8FD'); }
    $franja('D'.$fila, $ingreso ? 'E8F6EF' : 'FCEDEC');
    $hoja->getStyle('D'.$fila)->getFont()->setBold(true)->getColor()->setRGB($ingreso ? '16734B' : 'B23838');
    $hoja->getStyle('F'.$fila)->getAlignment()->setWrapText(true);
    $hoja->getStyle('G'.$fila.':J'.$fila)->getAlignment()->setWrapText(true);
    $largo = max(mb_strlen((string)$m['razonsocial']), mb_strlen((string)$m['concepto_caja']), mb_strlen((string)$m['referencia']), mb_strlen((string)$m['tipopago']));
    $hoja->getRowDimension($fila)->setRowHeight(max(53, ceil($largo/30)*15+8));
    $fila++;
}
$ultima = $fila-1;
$hoja->setCellValue('B8', count($recibos));
if ($ultima >= 12) {
    $hoja->setCellValue('E8', '=SUMIFS(M12:M'.$ultima.',D12:D'.$ultima.',"↑ Ingreso")');
    $hoja->setCellValue('G8', '=SUMIFS(M12:M'.$ultima.',D12:D'.$ultima.',"↓ Egreso")');
    $hoja->setAutoFilter('A11:R'.$ultima);
    $hoja->getStyle('E12:F'.$ultima)->getNumberFormat()->setFormatCode('@');
    $hoja->getStyle('L12:L'.$ultima)->getNumberFormat()->setFormatCode('@');
    $hoja->getStyle('B12:B'.$ultima)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
    $hoja->getStyle('C12:C'.$ultima)->getNumberFormat()->setFormatCode('hh:mm:ss');
    $hoja->getStyle('K12:K'.$ultima)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
    $hoja->getStyle('M12:P'.$ultima)->getNumberFormat()->setFormatCode($moneda);
} else {
    $hoja->setCellValue('E8', 0)->setCellValue('G8', 0);
$texto('A12', 'No se encontraron movimientos con los filtros seleccionados.');
    $fila = 13;
}
$hoja->setCellValue('I8', '=E8-G8');
foreach (['E8','G8','I8'] as $celda) { $hoja->getStyle($celda)->getNumberFormat()->setFormatCode($moneda); }
$texto('B'.$fila, 'Σ TOTALES DEL DETALLE');
foreach (['M','N','O','P'] as $columna) {
    $hoja->setCellValue($columna.$fila, $ultima >= 12 ? '=SUM('.$columna.'12:'.$columna.$ultima.')' : 0);
}
$franja('A'.$fila.':R'.$fila, 'EEE5F8');
$hoja->getStyle('A'.$fila.':R'.$fila)->getFont()->setBold(true);
$hoja->getStyle('M'.$fila.':P'.$fila)->getNumberFormat()->setFormatCode($moneda);
$hoja->getRowDimension($fila)->setRowHeight(30);
$hoja->freezePane('F12');
$hoja->setSelectedCell('A1');
$hoja->getSheetView()->setZoomScale(85);
$hoja->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE)->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A3)->setScale(85)->setFitToPage(false)->setRowsToRepeatAtTopByStartAndEnd(11,11)->setPrintArea('A1:R'.$fila);
$filaMarca = $fila + 2;
$texto('B'.$filaMarca, 'Exportado desde Phuyu System');
$texto('G'.$filaMarca, 'Fecha: '.($exportado_en ?? date('d/m/Y H:i')).' - Hora de Perú');
$texto('M'.$filaMarca, 'Usuario: '.($usuario_exporta ?? ''));
$hoja->getStyle('B'.$filaMarca.':R'.$filaMarca)->getFont()->setSize(9)->setItalic(true)->getColor()->setRGB('7B728A');
$hoja->getPageSetup()->setPrintArea('A1:R'.$filaMarca);
$hoja->getHeaderFooter()->setOddFooter('&LExportado desde Phuyu System&RPágina &P de &N');

// Apache puede tener un TMPDIR heredado que pertenece al usuario de escritorio.
// Elegir un directorio existente y escribible antes de crear el ZIP del XLSX.
$temporal = false;
foreach (array_unique([ini_get('upload_tmp_dir'), sys_get_temp_dir(), '/tmp']) as $directorio) {
    if ($directorio && is_dir($directorio) && is_writable($directorio)) {
        $temporal = tempnam($directorio, 'phuyu_excel_');
        if ($temporal !== false) { break; }
    }
}
if ($temporal === false) {
    throw new RuntimeException('No hay un directorio temporal disponible para generar el Excel.');
}
try {
    $escritor = PHPExcel_IOFactory::createWriter($libro, 'Excel2007');
    $escritor->setPreCalculateFormulas(true);
    $escritor->save($temporal);
    // La vista se captura en el controlador; todavia no se envia al navegador.
    $contenido = file_get_contents($temporal);
    if ($contenido === false || substr($contenido, 0, 2) !== 'PK') {
        throw new RuntimeException('No se pudo generar el archivo Excel.');
    }
    echo $contenido;
} finally {
    if (is_file($temporal)) { unlink($temporal); }
}
