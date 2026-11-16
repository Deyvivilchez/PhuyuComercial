<?php defined('BASEPATH') OR exit('No direct script access allowed');

/** Exportacion de solo lectura, compatible con las columnas A:W de cargarproductos. */
class Producto_migracion_export {
    public function crear($registros, $almacen){
        $temporales = [];
        $hojas = [];
        $archivo = false;
        try {
            foreach (['Carga productos', 'Respaldo original', 'Ayuda'] as $indice => $nombre) {
                $ruta = tempnam(sys_get_temp_dir(), 'phuyu_hoja_');
                if ($ruta === false) { throw new RuntimeException('No se pudo crear la hoja temporal.'); }
                $temporales[] = $ruta;
                $xml = new XMLWriter();
                if (!$xml->openUri($ruta)) { throw new RuntimeException('No se pudo escribir la hoja.'); }
                $xml->startDocument('1.0', 'UTF-8');
                $xml->startElement('worksheet');
                $xml->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
                $xml->startElement('sheetViews');
                $xml->startElement('sheetView');
                $xml->writeAttribute('workbookViewId', '0');
                $xml->startElement('pane');
                foreach (['ySplit'=>'1','topLeftCell'=>'A2','activePane'=>'bottomLeft','state'=>'frozen'] as $k=>$v) { $xml->writeAttribute($k, $v); }
                $xml->endElement();
                $xml->endElement();
                $xml->endElement();
                $xml->startElement('cols');
                $xml->startElement('col');
                foreach (['min'=>'1','max'=>'16384','width'=>'22','customWidth'=>'1'] as $k=>$v) { $xml->writeAttribute($k, $v); }
                $xml->endElement();
                $xml->endElement();
                $xml->startElement('sheetData');
                $hojas[] = $xml;
            }
            $this->escribir_hojas($registros, $almacen, $hojas);
            foreach ($hojas as $xml) {
                $xml->endElement(); // sheetData
                $xml->endElement(); // worksheet
                $xml->endDocument();
                $xml->flush();
            }
            $archivo = tempnam(sys_get_temp_dir(), 'phuyu_productos_');
            if ($archivo === false) { throw new RuntimeException('No se pudo crear el archivo temporal.'); }
            $this->empaquetar($archivo, $temporales);
            return $archivo;
        } catch (Throwable $e) {
            if ($archivo !== false) { unlink($archivo); }
            throw $e;
        } finally {
            // XMLWriter escribe cada fila a disco; no conserva las celdas en memoria.
            $hojas = [];
            unset($xml);
            foreach ($temporales as $ruta) { if (is_file($ruta)) { unlink($ruta); } }
        }
    }

    private function escribir_hojas($registros, $almacen, $hojas){
        list($carga, $respaldo, $ayuda) = $hojas;
        $cabeceras = ['codigo','descripcion','tipo','familia','linea','marca','unidad','factor',
            'precio_compra','precio_venta','precio_minimo','precio_credito','precio_mayor',
            'precio_adicional','codigo_barra','stock_inicial','control_stock',
            'cod_afectacion_igv_compra','cod_afectacion_igv_venta','afecto_icbper',
            'comision_vendedor','caracteristicas','controlar_series'];
        $this->fila($carga, 1, $cabeceras);

        $columnas = [];
        $filaCarga = 2;
        $codigos = [];
        $barras = [];
        $series = 0;
        $sinCodigo = 0;
        foreach ($registros as $registro) {
            $p = json_decode($registro['producto'], true) ?: [];
            $pu = json_decode($registro['unidad_producto'], true) ?: [];
            $ub = json_decode($registro['ubicacion'], true) ?: [];
            foreach ($this->original($registro, $p, $pu, $ub) as $nombre => $valor) {
                $columnas[$nombre] = true;
            }

            if ((int)$p['estado'] !== 1 || (int)$ub['estado'] !== 1) { continue; }
            $codigo = (string)$p['codigo'];
            $barra = trim((string)($ub['codigobarra'] ?? ''));
            if ($barra === '') { $barra = (string)($pu['codigobarra'] ?? ''); }
            $codigos[$codigo][(int)$p['codproducto']] = true;
            if ($barra !== '') { $barras[$barra][(int)$p['codproducto']] = true; }
            if ($codigo === '') { $sinCodigo++; }
            if (!empty($p['controlarseries'])) { $series++; }
            // Se usan nombres de catalogos para no depender de sus IDs en el nuevo sistema.
            $valores = [$codigo, $p['descripcion'], $p['tipo'], $registro['familia'],
                $registro['linea'], $registro['marca'], $registro['unidad'], $ub['factor'],
                $ub['preciocompra'], $ub['pventapublico'], $ub['pventamin'], $ub['pventacredito'],
                $ub['pventaxmayor'], $ub['pventaadicional'], $barra, $ub['stockactualconvertido'],
                $p['controlstock'], $registro['afectacion_compra'], $registro['afectacion_venta'],
                $p['afectoicbper'], $ub['comisionvendedor'] ?? 0, $p['caracteristicas'], $p['controlarseries']];
            $this->fila($carga, $filaCarga++, $valores, [2,7,8,9,10,11,12,13,15,16,19,20,22]);
        }

        $columnas = array_keys($columnas);
        $this->fila($respaldo, 1, $columnas ?: ['Sin productos registrados en este almacen']);
        foreach ($registros as $indice => $registro) {
            $original = $this->original($registro,
                json_decode($registro['producto'], true) ?: [],
                json_decode($registro['unidad_producto'], true) ?: [],
                json_decode($registro['ubicacion'], true) ?: []);
            $valores = [];
            foreach ($columnas as $columna) { $valores[] = $original[$columna] ?? ''; }
            $this->fila($respaldo, $indice+2, $valores);
        }
        $duplicados = function($grupos){
            return count(array_filter($grupos, function($productos){ return count($productos)>1; }));
        };
        $notas = [
            ['Dato', 'Detalle'],
            ['Fecha de exportacion', date('Y-m-d H:i:s')],
            ['RUC de origen', (string)($_SESSION['phuyu_ruc'] ?? '')],
            ['Almacen de origen', $almacen['codalmacen'].' - '.$almacen['descripcion']],
            ['Sucursal de origen (ID)', $almacen['codsucursal']],
            ['Filas para cargar (producto/unidad)', $filaCarga-2],
            ['Filas de respaldo (incluye inactivos)', count($registros)],
            ['Alcance', 'Solo productos registrados en el almacen seleccionado. Repita la exportacion por cada almacen antes de reconfigurar. Incluye productos sin stock y servicios. No aplica filtros de linea, busqueda, fechas o control de stock.'],
            ['Carga productos', 'Primera hoja compatible con Almacen > Productos > Cargar productos. Mantenga las columnas A:W y una fila por producto/unidad. Solo contiene productos y ubicaciones activos.'],
            ['Stock inicial', 'Es stockactualconvertido actual de cada unidad. Importe una sola vez en un almacen destino nuevo o vacio: el importador suma el stock cuando ya existe. No es un saldo historico a la fecha del filtro.'],
            ['Precios y barras', 'La hoja de carga usa precios del almacen y su codigo de barras; si la barra esta vacia usa la de unidad. El respaldo conserva ambos valores originales y el costo separado del precio de compra.'],
            ['Respaldo original', 'Referencia completa de los campos de producto, unidad y ubicacion, incluidos IDs y estados. Esta hoja no se importa automaticamente. El importador actual usa precio_compra como costo y activa los productos cargados.'],
            ['Codigos y codigos de barras', 'Se guardan como texto para conservar ceros iniciales y codigos largos. No convertir estas columnas a numeros.'],
            ['Codigos internos compartidos por productos distintos', $duplicados($codigos)],
            ['Barras compartidas por productos distintos', $duplicados($barras)],
            ['Filas sin codigo interno', $sinCodigo],
            ['Revision de coincidencias', 'Revise codigos, barras o descripciones repetidas antes de importar: la carga actual puede unir productos por esas coincidencias. Revise tambien nombres de catalogos que sean solo numeros, pues el importador puede interpretarlos como IDs.'],
            ['Filas con control de series', $series],
            ['Series, lotes y fotos', 'No incluye el detalle de series/lotes ni los archivos de fotos. Si los usa, respalde y migre esos datos aparte. Las rutas de fotos quedan como referencia en Respaldo original.'],
            ['Datos adicionales', 'Estados, stock minimo/maximo, costos, otros campos e IDs quedan en el respaldo. La carga A:W solo restaura los campos de su plantilla; otros campos requieren una migracion adicional.'],
            ['Cambio de RUC', 'Este archivo exporta productos. No cambia RUC ni configuracion y no traslada comprobantes, ventas, compras o deudas. Conserve tambien una copia completa de la base antes de reconfigurar.'],
            ['Verificacion', 'Pruebe primero una copia en el destino y compare cantidad de productos/unidades, barras, precios y stock por almacen antes de la carga definitiva.']
        ];
        foreach ($notas as $indice => $nota) { $this->fila($ayuda, $indice+1, $nota); }
    }

    private function original($registro, $p, $pu, $ub){
        $original = [];
        foreach (['producto'=>$p, 'unidad'=>$pu, 'almacen'=>$ub] as $prefijo=>$datos) {
            foreach ($datos as $campo=>$valor) { $original[$prefijo.'.'.$campo] = $valor; }
        }
        foreach (['familia','linea','marca','unidad','afectacion_compra','afectacion_venta'] as $nombre) {
            $original['referencia.'.$nombre] = $registro[$nombre];
        }
        return $original;
    }

    private function fila($xml, $fila, $valores, $numericas = []){
        $xml->startElement('row');
        $xml->writeAttribute('r', (string)$fila);
        foreach (array_values($valores) as $indice=>$valor) {
            $columna = '';
            for ($n=$indice+1; $n>0; $n=(int)(($n-1)/26)) { $columna = chr(65+(($n-1)%26)).$columna; }
            $xml->startElement('c');
            $xml->writeAttribute('r', $columna.$fila);
            if (in_array($indice, $numericas, true) && is_numeric($valor)) {
                $xml->writeElement('v', (string)$valor);
            } else {
                // Texto explicito conserva barras largas, ceros y formulas como texto.
                if (is_array($valor)) { $valor = json_encode($valor, JSON_UNESCAPED_UNICODE); }
                $valor = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)$valor);
                $xml->writeAttribute('t', 'inlineStr');
                $xml->startElement('is');
                $xml->startElement('t');
                $xml->writeAttribute('xml:space', 'preserve');
                $xml->text($valor);
                $xml->endElement();
                $xml->endElement();
            }
            $xml->endElement();
        }
        $xml->endElement();
        $xml->flush();
    }

    private function empaquetar($archivo, $hojas){
        $zip = new ZipArchive();
        if ($zip->open($archivo, ZipArchive::OVERWRITE) !== true) { throw new RuntimeException('No se pudo crear el Excel.'); }
        try {
            $tipos = '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';
            $libro = '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>';
            $relaciones = '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
            foreach (['Carga productos','Respaldo original','Ayuda'] as $indice=>$nombre) {
                $n = $indice+1;
                $tipos .= '<Override PartName="/xl/worksheets/sheet'.$n.'.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
                $libro .= '<sheet name="'.$nombre.'" sheetId="'.$n.'" r:id="rId'.$n.'"/>';
                $relaciones .= '<Relationship Id="rId'.$n.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet'.$n.'.xml"/>';
                if (!$zip->addFile($hojas[$indice], 'xl/worksheets/sheet'.$n.'.xml')) { throw new RuntimeException('No se pudo incluir la hoja.'); }
            }
            $archivos = [
                '[Content_Types].xml'=>$tipos.'</Types>',
                '_rels/.rels'=>'<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
                'xl/workbook.xml'=>$libro.'</sheets></workbook>',
                'xl/_rels/workbook.xml.rels'=>$relaciones.'</Relationships>'
            ];
            foreach ($archivos as $nombre=>$contenido) {
                if (!$zip->addFromString($nombre, $contenido)) { throw new RuntimeException('No se pudo completar el Excel.'); }
            }
        } finally {
            if (!$zip->close()) { throw new RuntimeException('No se pudo guardar el Excel.'); }
        }
    }
}
