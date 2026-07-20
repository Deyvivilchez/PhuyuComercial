<?php defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'third_party/phuyu_excel/PHPExcel.php';
require_once APPPATH . 'third_party/phuyu_excel/PHPExcel/IOFactory.php';

class Migrarstock extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        if ($this->input->is_ajax_request() and isset($_SESSION['phuyu_codusuario'])) {
            $almacenes = $this->almacenes_disponibles();
            $lineas = $this->lineas_disponibles();
            $codalmacenActual = isset($_SESSION['phuyu_codalmacen']) ? (int) $_SESSION['phuyu_codalmacen'] : 0;
            $this->load->view('almacen/productos/migrar_stock', compact('almacenes', 'lineas', 'codalmacenActual'));
        } else {
            $this->load->view('phuyu/404');
        }
    }

    public function formato()
    {
        if (!isset($_SESSION['phuyu_codusuario'])) {
            $this->load->view('phuyu/404');
            return;
        }

        $this->limpiar_salida_excel();

        $this->descargar_xls('formato-migrar-stock.xls', [
            [
                'nombre' => 'Actualizar stock',
                'filas' => [
                    ['codigo', 'cantidad', 'codunidad', 'observacion'],
                    ['PROD001', 8, '', 'codunidad vacio usa la unidad base/factor 1'],
                    ['PROD002', 3.5, 1, 'elige en pantalla si esta cantidad suma o reemplaza el stock']
                ]
            ],
            [
                'nombre' => 'Ayuda',
                'filas' => [
                    ['Campo', 'Descripcion'],
                    ['codigo', 'Obligatorio. Puede llamarse codigo, codigo de producto, SKU o similar.'],
                    ['cantidad', 'Obligatorio. Valor numerico. En modo sumar debe ser mayor a 0; en reemplazar puede ser 0.'],
                    ['codunidad', 'Opcional. Si esta vacio se usa la unidad base del producto en el almacen destino.'],
                    ['Importante', 'Elige en pantalla si la cantidad del Excel se suma o reemplaza el stock existente.']
                ]
            ]
        ]);
    }

    public function previsualizar()
    {
        if (!$this->input->is_ajax_request() || !isset($_SESSION['phuyu_codusuario'])) {
            $this->load->view('phuyu/404');
            return;
        }
        $this->output->set_content_type('application/json', 'utf-8');

        if (!isset($_FILES['archivo']) || $_FILES['archivo']['name'] == '') {
            echo json_encode(['estado' => 0, 'mensaje' => 'Debe seleccionar el archivo de stock.']);
            return;
        }

        $codalmacen = $this->codalmacen_migrar((int) $this->input->post('codalmacen'));
        if ($codalmacen <= 0) {
            echo json_encode(['estado' => 0, 'mensaje' => 'Debe seleccionar el almacen destino.']);
            return;
        }

        $ignorarStockCero = (int) $this->input->post('ignorar_stock_cero') == 1;
        $soloAplicar = (int) $this->input->post('solo_aplicar') == 1;
        $modoStock = $this->modo_stock_migrar($this->input->post('modo_stock'));

        try {
            $cabeceras = $this->leer_cabeceras_archivo($_FILES['archivo']);
            $mapaColumnas = $this->mapear_cabeceras_migrar_stock($cabeceras);
            $validacion = $this->validar_cabeceras_migrar_stock($mapaColumnas);

            if (!$validacion['estado']) {
                echo json_encode(['estado' => 0, 'mensaje' => $validacion['mensaje']]);
                return;
            }

            $filas = $this->leer_archivo($_FILES['archivo']);
            $preview = [];
            $validas = 0;
            $errores = 0;
            $omitidas = 0;

            foreach ($filas as $row => $fila) {
                $codigo = $this->normalizar_texto($this->valor_fila($fila, $mapaColumnas['codigo']));
                $cantidad = $this->numero_excel($this->valor_fila($fila, $mapaColumnas['cantidad']), null);
                $codunidad = $mapaColumnas['unidad'] !== '' ? $this->entero_excel($this->valor_fila($fila, $mapaColumnas['unidad']), 0) : 0;
                $aplicar = $mapaColumnas['aplicar'] !== '' ? $this->normalizar_texto($this->valor_fila($fila, $mapaColumnas['aplicar'])) : '';

                if ($codigo === '' && $cantidad === null && $codunidad == 0 && $aplicar === '') {
                    continue;
                }

                $resultado = $this->preparar_fila_preview($row, $codigo, $cantidad, $codunidad, $aplicar, $soloAplicar, $ignorarStockCero, $codalmacen, false, [], $modoStock);
                $preview[] = $resultado;

                if ($resultado['valido']) {
                    $validas++;
                } elseif (!empty($resultado['omitido'])) {
                    $omitidas++;
                } else {
                    $errores++;
                }
            }

            if (count($preview) == 0) {
                echo json_encode(['estado' => 0, 'mensaje' => 'No se encontraron filas para previsualizar.']);
                return;
            }

            echo json_encode([
                'estado' => 1,
                'mensaje' => 'Previsualizacion generada correctamente.',
                'preview' => $preview,
                'columnas' => $this->columnas_detectadas($cabeceras),
                'mapeo' => $this->mapeo_detectado($mapaColumnas, $cabeceras),
                'resumen' => [
                    'total' => count($preview),
                    'validas' => $validas,
                    'errores' => $errores,
                    'omitidas' => $omitidas
                ]
            ]);
        } catch (Exception $e) {
            echo json_encode(['estado' => 0, 'mensaje' => 'No se pudo leer el archivo. Verifique que sea un Excel o CSV valido.']);
        }
    }

    public function previsualizar_filas()
    {
        if (!$this->input->is_ajax_request() || !isset($_SESSION['phuyu_codusuario'])) {
            $this->load->view('phuyu/404');
            return;
        }
        $this->output->set_content_type('application/json', 'utf-8');

        $request = json_decode(file_get_contents('php://input'));
        $filas = isset($request->filas) && is_array($request->filas) ? $request->filas : [];
        $mapeo = isset($request->mapeo) && is_object($request->mapeo) ? $request->mapeo : new stdClass();
        $codalmacen = $this->codalmacen_migrar(isset($request->codalmacen) ? (int) $request->codalmacen : 0);
        $ignorarStockCero = isset($request->ignorar_stock_cero) ? (bool) $request->ignorar_stock_cero : false;
        $crearProductos = isset($request->crear_productos) ? (bool) $request->crear_productos : false;
        $reemplazarNombre = isset($request->reemplazar_nombre) ? (bool) $request->reemplazar_nombre : false;
        $soloAplicar = isset($request->solo_aplicar) ? (bool) $request->solo_aplicar : false;
        $modoStock = $this->modo_stock_migrar(isset($request->modo_stock) ? $request->modo_stock : 'sumar');

        $colCodigo = isset($mapeo->codigo) ? (string) $mapeo->codigo : '';
        $colCantidad = isset($mapeo->cantidad) ? (string) $mapeo->cantidad : '';
        $colDescripcion = isset($mapeo->descripcion) ? (string) $mapeo->descripcion : '';
        $colUnidad = isset($mapeo->unidad) ? (string) $mapeo->unidad : '';
        $colCodigoBarra = isset($mapeo->codigo_barra) ? (string) $mapeo->codigo_barra : '';
        $colPrecioCompra = isset($mapeo->precio_compra) ? (string) $mapeo->precio_compra : '';
        $colPrecioVenta = isset($mapeo->precio_venta) ? (string) $mapeo->precio_venta : '';
        $colMarca = isset($mapeo->marca) ? (string) $mapeo->marca : '';
        $colLinea = isset($mapeo->linea) ? (string) $mapeo->linea : '';
        $colFamilia = isset($mapeo->familia) ? (string) $mapeo->familia : '';
        $colAplicar = isset($mapeo->aplicar) ? (string) $mapeo->aplicar : '';

        if ($codalmacen <= 0) {
            echo json_encode(['estado' => 0, 'mensaje' => 'Debe seleccionar el almacen destino.']);
            return;
        }

        if ($colCodigo === '' || $colCantidad === '') {
            echo json_encode(['estado' => 0, 'mensaje' => 'Debe mapear las columnas obligatorias: codigo y cantidad.']);
            return;
        }

        if ($crearProductos && $colDescripcion === '') {
            echo json_encode(['estado' => 0, 'mensaje' => 'Para crear productos faltantes debe mapear la columna descripcion/nombre.']);
            return;
        }

        if (count($filas) == 0) {
            echo json_encode(['estado' => 0, 'mensaje' => 'No hay filas del Excel para previsualizar.']);
            return;
        }

        $preview = [];
        $validas = 0;
        $errores = 0;
        $omitidas = 0;

        foreach ($filas as $idx => $fila) {
            if (!is_object($fila) && !is_array($fila)) {
                continue;
            }

            $codigo = $this->normalizar_texto($this->valor_fila_objeto($fila, $colCodigo));
            $cantidad = $this->numero_excel($this->valor_fila_objeto($fila, $colCantidad), null);
            $unidadExcel = $colUnidad !== '' ? $this->normalizar_texto($this->valor_fila_objeto($fila, $colUnidad)) : '';
            $codunidad = $colUnidad !== '' ? $this->entero_excel($unidadExcel, 0) : 0;
            $datosNuevo = [
                'descripcion' => $colDescripcion !== '' ? $this->normalizar_texto($this->valor_fila_objeto($fila, $colDescripcion)) : '',
                'unidad_texto' => $unidadExcel,
                'codigo_barra' => $colCodigoBarra !== '' ? $this->normalizar_texto($this->valor_fila_objeto($fila, $colCodigoBarra)) : '',
                'precio_compra' => $colPrecioCompra !== '' ? $this->numero_excel($this->valor_fila_objeto($fila, $colPrecioCompra), 0) : 0,
                'precio_venta' => $colPrecioVenta !== '' ? $this->numero_excel($this->valor_fila_objeto($fila, $colPrecioVenta), 0) : 0,
                'marca' => $colMarca !== '' ? $this->normalizar_texto($this->valor_fila_objeto($fila, $colMarca)) : '',
                'linea' => $colLinea !== '' ? $this->normalizar_texto($this->valor_fila_objeto($fila, $colLinea)) : '',
                'familia' => $colFamilia !== '' ? $this->normalizar_texto($this->valor_fila_objeto($fila, $colFamilia)) : ''
            ];
            $aplicar = $colAplicar !== '' ? $this->normalizar_texto($this->valor_fila_objeto($fila, $colAplicar)) : '';

            if ($codigo === '' && $datosNuevo['descripcion'] === '' && $cantidad === null && $codunidad == 0 && $aplicar === '') {
                continue;
            }

            $resultado = $this->preparar_fila_preview($idx + 2, $codigo, $cantidad, $codunidad, $aplicar, $soloAplicar, $ignorarStockCero, $codalmacen, $crearProductos, $datosNuevo, $modoStock, $reemplazarNombre);
            $preview[] = $resultado;

            if ($resultado['valido']) {
                $validas++;
            } elseif (!empty($resultado['omitido'])) {
                $omitidas++;
            } else {
                $errores++;
            }
        }

        if (count($preview) == 0) {
            echo json_encode(['estado' => 0, 'mensaje' => 'No se encontraron filas para previsualizar.']);
            return;
        }

        echo json_encode([
            'estado' => 1,
            'mensaje' => 'Previsualizacion generada correctamente.',
            'preview' => $preview,
            'resumen' => [
                'total' => count($preview),
                'validas' => $validas,
                'errores' => $errores,
                'omitidas' => $omitidas
            ]
        ]);
    }

    public function procesar_previsualizacion()
    {
        if (!$this->input->is_ajax_request() || !isset($_SESSION['phuyu_codusuario'])) {
            $this->load->view('phuyu/404');
            return;
        }
        $this->output->set_content_type('application/json', 'utf-8');

        $request = json_decode(file_get_contents('php://input'));
        $filas = isset($request->filas) && is_array($request->filas) ? $request->filas : [];
        $codalmacen = $this->codalmacen_migrar(isset($request->codalmacen) ? (int) $request->codalmacen : 0);
        $ignorarStockCero = isset($request->ignorar_stock_cero) ? (bool) $request->ignorar_stock_cero : false;
        $crearProductos = isset($request->crear_productos) ? (bool) $request->crear_productos : false;
        $reemplazarNombre = isset($request->reemplazar_nombre) ? (bool) $request->reemplazar_nombre : false;
        $limpiezaAlmacen = $this->limpieza_almacen_migrar(isset($request->limpieza_almacen) ? $request->limpieza_almacen : 'conservar');
        $modoStock = $this->modo_stock_migrar(isset($request->modo_stock) ? $request->modo_stock : 'sumar');
        $procesados = 0;
        $ubicacionesProcesadas = [];
        $errores = [];
        $transaccion = false;

        if ($codalmacen <= 0) {
            echo json_encode(['estado' => 0, 'mensaje' => 'Debe seleccionar el almacen destino.']);
            return;
        }

        if (count($filas) == 0) {
            echo json_encode(['estado' => 0, 'mensaje' => 'Debe seleccionar al menos una fila valida.']);
            return;
        }

        try {
            $this->db->trans_begin();
            $transaccion = true;

            foreach ($filas as $fila) {
                if (empty($fila->seleccionado) || empty($fila->valido)) {
                    continue;
                }

                $codigo = $this->normalizar_texto(isset($fila->codigo) ? $fila->codigo : '');
                $cantidad = $this->numero_excel(isset($fila->cantidad) ? $fila->cantidad : null, null);
                $codunidad = isset($fila->codunidad) ? (int) $fila->codunidad : 0;
                $row = isset($fila->fila) ? (int) $fila->fila : 0;
                $datosNuevo = [
                    'descripcion' => isset($fila->descripcion_excel) ? $this->normalizar_texto($fila->descripcion_excel) : (isset($fila->producto) ? $this->normalizar_texto($fila->producto) : ''),
                    'unidad_texto' => isset($fila->unidad_texto) ? $this->normalizar_texto($fila->unidad_texto) : '',
                    'codigo_barra' => isset($fila->codigo_barra) ? $this->normalizar_texto($fila->codigo_barra) : '',
                    'precio_compra' => isset($fila->precio_compra) ? $this->numero_excel($fila->precio_compra, 0) : 0,
                    'precio_venta' => isset($fila->precio_venta) ? $this->numero_excel($fila->precio_venta, 0) : 0,
                    'marca' => isset($fila->marca) ? $this->normalizar_texto($fila->marca) : '',
                    'linea' => isset($fila->linea) ? $this->normalizar_texto($fila->linea) : '',
                    'familia' => isset($fila->familia) ? $this->normalizar_texto($fila->familia) : ''
                ];

                $resultado = $this->aplicar_stock_producto_excel($codigo, $cantidad, $codunidad, $ignorarStockCero, $row, $codalmacen, $crearProductos, $datosNuevo, $modoStock, $reemplazarNombre);
                if ($resultado['estado'] == 1) {
                    $procesados++;
                    if (!empty($resultado['codproducto']) && !empty($resultado['codunidad'])) {
                        $ubicacionesProcesadas[] = [
                            'codproducto' => (int) $resultado['codproducto'],
                            'codunidad' => (int) $resultado['codunidad']
                        ];
                    }
                } else {
                    $errores[] = $resultado['mensaje'];
                }
            }

            $limpiados = 0;
            if ($procesados > 0 && $limpiezaAlmacen !== 'conservar') {
                $limpiados = $this->limpiar_productos_no_incluidos_migrar($codalmacen, $ubicacionesProcesadas, $limpiezaAlmacen);
            }

            if ($procesados == 0 || $this->db->trans_status() === false) {
                $this->db->trans_rollback();
                $mensaje = 'No se actualizo ningun producto.';
                if (count($errores) > 0) {
                    $mensaje .= ' ' . implode(' ', array_slice($errores, 0, 5));
                }
                echo json_encode(['estado' => 0, 'mensaje' => $mensaje, 'procesados' => $procesados, 'errores' => $errores]);
                return;
            }

            $this->db->trans_commit();

            $mensaje = 'Stock actualizado: ' . $procesados . ' productos.';
            if ($limpiezaAlmacen !== 'conservar') {
                $mensaje .= ' Productos no seleccionados limpiados: ' . $limpiados . '.';
            }
            if (count($errores) > 0) {
                $mensaje .= ' Filas con error: ' . count($errores) . '. ' . implode(' ', array_slice($errores, 0, 3));
            }

            echo json_encode(['estado' => 1, 'mensaje' => $mensaje, 'procesados' => $procesados, 'errores' => $errores]);
        } catch (Exception $e) {
            if ($transaccion) {
                $this->db->trans_rollback();
            }
            echo json_encode(['estado' => 0, 'mensaje' => 'No se pudo aplicar la actualizacion de stock.']);
        }
    }

    public function preparar_catalogo()
    {
        if (!$this->input->is_ajax_request() || !isset($_SESSION['phuyu_codusuario'])) {
            $this->load->view('phuyu/404');
            return;
        }
        $this->output->set_content_type('application/json', 'utf-8');

        try {
            $this->db->trans_begin();

            $catalogos = $this->asegurar_catalogos_generales_migrar();
            $productosNormalizados = $this->normalizar_productos_generales_migrar($catalogos);
            $unificacion = $this->unificar_duplicados_barra_migrar();

            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                echo json_encode(['estado' => 0, 'mensaje' => 'No se pudo preparar el catalogo.']);
                return;
            }

            $this->db->trans_commit();

            echo json_encode([
                'estado' => 1,
                'mensaje' => 'Productos normalizados: ' . $productosNormalizados . '. Duplicados inactivados: ' . $unificacion['duplicados'] . '. Ubicaciones movidas o consolidadas: ' . $unificacion['ubicaciones'] . '.'
            ]);
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo json_encode(['estado' => 0, 'mensaje' => 'No se pudo preparar el catalogo.']);
        }
    }

    public function poner_almacen_cero()
    {
        if (!$this->input->is_ajax_request() || !isset($_SESSION['phuyu_codusuario'])) {
            $this->load->view('phuyu/404');
            return;
        }
        $this->output->set_content_type('application/json', 'utf-8');

        $request = json_decode(file_get_contents('php://input'));
        $codalmacen = $this->codalmacen_migrar(isset($request->codalmacen) ? (int) $request->codalmacen : 0);
        $alcanceLineas = isset($request->alcance_lineas) ? (string) $request->alcance_lineas : 'todos';
        $lineas = isset($request->lineas) && is_array($request->lineas) ? array_map('intval', $request->lineas) : [];

        if ($codalmacen <= 0) {
            echo json_encode(['estado' => 0, 'mensaje' => 'Debe seleccionar un almacen valido.']);
            return;
        }

        if (in_array($alcanceLineas, ['solo', 'excepto'], true) && count($lineas) == 0) {
            echo json_encode(['estado' => 0, 'mensaje' => 'Debe seleccionar al menos una linea.']);
            return;
        }

        try {
            $this->db->trans_begin();

            $this->db->set('stockactual', 0);
            $this->db->set('stockactualreal', 0);
            $this->db->set('stockactualconvertido', 0);
            $this->db->set('preciostockvalorizado', 0);
            $this->db->where('codalmacen', $codalmacen);

            if ($alcanceLineas === 'solo') {
                $this->db->where('codproducto in (select codproducto from almacen.productos where codlinea in (' . implode(',', $lineas) . '))', null, false);
            } elseif ($alcanceLineas === 'excepto') {
                $this->db->where('codproducto not in (select codproducto from almacen.productos where codlinea in (' . implode(',', $lineas) . '))', null, false);
            }

            $this->db->update('almacen.productoubicacion');

            $afectados = $this->db->affected_rows();

            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                echo json_encode(['estado' => 0, 'mensaje' => 'No se pudo poner el almacen en cero.']);
                return;
            }

            $this->db->trans_commit();
            echo json_encode(['estado' => 1, 'mensaje' => 'Stock en cero para ' . $afectados . ' ubicaciones del almacen seleccionado.']);
        } catch (Exception $e) {
            $this->db->trans_rollback();
            echo json_encode(['estado' => 0, 'mensaje' => 'No se pudo poner el almacen en cero.']);
        }
    }

    public function procesar()
    {
        if (!$this->input->is_ajax_request() || !isset($_SESSION['phuyu_codusuario'])) {
            $this->load->view('phuyu/404');
            return;
        }
        $this->output->set_content_type('application/json', 'utf-8');

        if (!isset($_FILES['archivo']) || $_FILES['archivo']['name'] == '') {
            echo json_encode(['estado' => 0, 'mensaje' => 'Debe seleccionar el archivo de stock.']);
            return;
        }

        $codalmacen = $this->codalmacen_migrar((int) $this->input->post('codalmacen'));
        if ($codalmacen <= 0) {
            echo json_encode(['estado' => 0, 'mensaje' => 'Debe seleccionar el almacen destino.']);
            return;
        }

        $ignorarStockCero = (int) $this->input->post('ignorar_stock_cero') == 1;
        $soloAplicar = (int) $this->input->post('solo_aplicar') == 1;
        $modoStock = $this->modo_stock_migrar($this->input->post('modo_stock'));
        $procesados = 0;
        $omitidos = 0;
        $errores = [];
        $transaccion = false;

        try {
            $cabeceras = $this->leer_cabeceras_archivo($_FILES['archivo']);
            $mapaColumnas = $this->mapear_cabeceras_migrar_stock($cabeceras);
            $validacion = $this->validar_cabeceras_migrar_stock($mapaColumnas);

            if (!$validacion['estado']) {
                echo json_encode(['estado' => 0, 'mensaje' => $validacion['mensaje']]);
                return;
            }

            $filas = $this->leer_archivo($_FILES['archivo']);
            $this->db->trans_begin();
            $transaccion = true;

            foreach ($filas as $row => $fila) {
                $codigo = $this->normalizar_texto($this->valor_fila($fila, $mapaColumnas['codigo']));
                $cantidad = $this->numero_excel($this->valor_fila($fila, $mapaColumnas['cantidad']), null);
                $codunidad = $mapaColumnas['unidad'] !== '' ? $this->entero_excel($this->valor_fila($fila, $mapaColumnas['unidad']), 0) : 0;
                $aplicar = $mapaColumnas['aplicar'] !== '' ? $this->normalizar_texto($this->valor_fila($fila, $mapaColumnas['aplicar'])) : '';

                if ($codigo === '' && $cantidad === null && $codunidad == 0 && $aplicar === '') {
                    continue;
                }

                if ($soloAplicar && $this->booleano_excel($aplicar, 0) != 1) {
                    $omitidos++;
                    continue;
                }

                if ($codigo === '') {
                    $errores[] = 'Fila ' . $row . ': falta codigo de producto.';
                    continue;
                }

                if (!$this->cantidad_valida_migrar($cantidad, $modoStock)) {
                    $errores[] = 'Fila ' . $row . ': cantidad/stock invalido para el modo seleccionado.';
                    continue;
                }

                $resultado = $this->aplicar_stock_producto_excel($codigo, $cantidad, $codunidad, $ignorarStockCero, $row, $codalmacen, false, [], $modoStock);

                if ($resultado['estado'] == 1) {
                    $procesados++;
                } elseif ($resultado['omitido'] == 1) {
                    $omitidos++;
                } else {
                    $errores[] = $resultado['mensaje'];
                }
            }

            if ($procesados == 0 || $this->db->trans_status() === false) {
                $this->db->trans_rollback();
                $mensaje = $procesados == 0 ? 'No se actualizo ningun producto.' : 'No se pudo completar la actualizacion de stock.';
                if (count($errores) > 0) {
                    $mensaje .= ' ' . implode(' ', array_slice($errores, 0, 5));
                }
                if ($omitidos > 0) {
                    $mensaje .= ' Filas omitidas: ' . $omitidos . '.';
                }
                echo json_encode(['estado' => 0, 'mensaje' => $mensaje, 'procesados' => $procesados, 'omitidos' => $omitidos, 'errores' => $errores]);
                return;
            }

            $this->db->trans_commit();

            $mensaje = 'Stock actualizado: ' . $procesados . ' productos.';
            if ($omitidos > 0) {
                $mensaje .= ' Filas omitidas: ' . $omitidos . '.';
            }
            if (count($errores) > 0) {
                $mensaje .= ' Filas con error: ' . count($errores) . '. ' . implode(' ', array_slice($errores, 0, 3));
            }

            echo json_encode([
                'estado' => 1,
                'mensaje' => $mensaje,
                'procesados' => $procesados,
                'omitidos' => $omitidos,
                'errores' => $errores
            ]);
        } catch (Exception $e) {
            if ($transaccion) {
                $this->db->trans_rollback();
            }
            echo json_encode(['estado' => 0, 'mensaje' => 'No se pudo leer el archivo. Verifique que sea un Excel o CSV valido.']);
        }
    }

    private function almacenes_disponibles()
    {
        return $this->db->query(
            'select a.codalmacen, a.descripcion, a.codsucursal, coalesce(s.descripcion, \'\') as sucursal
             from almacen.almacenes a
             left join public.sucursales s on (s.codsucursal=a.codsucursal)
             where a.estado=1
             order by a.codsucursal, a.descripcion'
        )->result_array();
    }

    private function lineas_disponibles()
    {
        return $this->db->query(
            'select codlinea, descripcion
             from almacen.lineas
             where estado=1
             order by descripcion'
        )->result_array();
    }

    private function codalmacen_migrar($codalmacen)
    {
        $codalmacen = (int) $codalmacen;
        if ($codalmacen <= 0) {
            $codalmacen = isset($_SESSION['phuyu_codalmacen']) ? (int) $_SESSION['phuyu_codalmacen'] : 0;
        }

        if ($codalmacen <= 0) {
            return 0;
        }

        $existe = $this->db->get_where('almacen.almacenes', [
            'codalmacen' => $codalmacen,
            'estado' => 1
        ])->row_array();

        return empty($existe) ? 0 : $codalmacen;
    }

    private function modo_stock_migrar($modo)
    {
        return $modo === 'reemplazar' ? 'reemplazar' : 'sumar';
    }

    private function cantidad_valida_migrar($cantidad, $modoStock)
    {
        if ($cantidad === null || !is_numeric($cantidad)) {
            return false;
        }

        if ($this->modo_stock_migrar($modoStock) === 'reemplazar') {
            return (float) $cantidad >= 0;
        }

        return (float) $cantidad > 0;
    }

    private function limpieza_almacen_migrar($limpieza)
    {
        return in_array($limpieza, ['cero', 'inactivar'], true) ? $limpieza : 'conservar';
    }

    private function preparar_fila_preview($row, $codigo, $cantidad, $codunidad, $aplicar, $soloAplicar, $ignorarStockCero, $codalmacen, $crearProductos = false, $datosNuevo = [], $modoStock = 'sumar', $reemplazarNombre = false)
    {
        $modoStock = $this->modo_stock_migrar($modoStock);
        $base = [
            'fila' => (int) $row,
            'codigo' => $codigo,
            'producto' => '',
            'descripcion_excel' => isset($datosNuevo['descripcion']) ? $datosNuevo['descripcion'] : '',
            'codproducto' => 0,
            'codunidad' => (int) $codunidad,
            'unidad' => '',
            'unidad_texto' => isset($datosNuevo['unidad_texto']) ? $datosNuevo['unidad_texto'] : '',
            'codigo_barra' => isset($datosNuevo['codigo_barra']) ? $datosNuevo['codigo_barra'] : '',
            'precio_compra' => isset($datosNuevo['precio_compra']) ? (float) $datosNuevo['precio_compra'] : 0,
            'precio_venta' => isset($datosNuevo['precio_venta']) ? (float) $datosNuevo['precio_venta'] : 0,
            'marca' => isset($datosNuevo['marca']) ? $datosNuevo['marca'] : '',
            'linea' => isset($datosNuevo['linea']) ? $datosNuevo['linea'] : '',
            'familia' => isset($datosNuevo['familia']) ? $datosNuevo['familia'] : '',
            'stock_actual' => '',
            'cantidad' => $cantidad,
            'modo_stock' => $modoStock,
            'stock_final' => '',
            'valido' => false,
            'omitido' => false,
            'nuevo' => false,
            'seleccionado' => false,
            'mensaje' => ''
        ];

        if ($soloAplicar && $this->booleano_excel($aplicar, 0) != 1) {
            $base['omitido'] = true;
            $base['mensaje'] = 'No marcada para aplicar.';
            return $base;
        }

        $descripcionNueva = isset($datosNuevo['descripcion']) ? $this->normalizar_texto($datosNuevo['descripcion']) : '';

        if ($codigo === '' && $descripcionNueva === '') {
            $base['mensaje'] = 'Falta codigo o descripcion.';
            return $base;
        }

        $producto = $this->buscar_producto_migrar($codigo, $descripcionNueva, $base['codigo_barra']);
        if (empty($producto) && !$crearProductos) {
            $base['mensaje'] = 'Producto no existe.';
            return $base;
        }

        if (empty($producto)) {
            $base['producto'] = $descripcionNueva;
            $base['nuevo'] = true;
        } else {
            $codproducto = (int) $producto['codproducto'];
            $base['producto'] = $reemplazarNombre && $descripcionNueva !== '' ? $descripcionNueva : $producto['descripcion'];
            $base['codproducto'] = $codproducto;
        }

        if (!$this->cantidad_valida_migrar($cantidad, $modoStock)) {
            $base['mensaje'] = $modoStock === 'reemplazar' ? 'Cantidad debe ser 0 o mayor.' : 'Cantidad debe ser mayor a 0.';
            return $base;
        }

        if ($base['nuevo']) {
            $unidadPreview = $this->buscar_unidad_migrar($base['unidad_texto']);
            $base['codunidad'] = $unidadPreview > 0 ? $unidadPreview : 0;
            $base['unidad'] = $base['unidad_texto'] !== '' ? $base['unidad_texto'] : 'UNIDAD';
            $base['stock_actual'] = 0;
            $base['stock_final'] = round((float) $cantidad, 3);
            $base['valido'] = true;
            $base['seleccionado'] = true;
            $base['mensaje'] = 'Producto nuevo.';
            return $base;
        }

        $codproducto = (int) $producto['codproducto'];
        if ($codunidad <= 0) {
            $unidadBase = $this->db->query(
                'select codunidad from almacen.productoubicacion where codalmacen=? and codproducto=? and estado=1 order by factor asc limit 1',
                [$codalmacen, $codproducto]
            )->row_array();

            if (empty($unidadBase)) {
                $unidadBase = $this->db->query(
                    'select codunidad from almacen.productounidades where codproducto=? and estado=1 order by factor asc limit 1',
                    [$codproducto]
                )->row_array();
            }

            $codunidad = !empty($unidadBase) ? (int) $unidadBase['codunidad'] : 0;
        }

        if ($codunidad <= 0) {
            $base['mensaje'] = 'No se encontro unidad.';
            return $base;
        }

        $ubicacion = $this->db->query(
            'select pu.*, u.descripcion as unidad from almacen.productoubicacion pu inner join almacen.unidades u on (u.codunidad=pu.codunidad) where pu.codalmacen=? and pu.codproducto=? and pu.codunidad=? limit 1',
            [$codalmacen, $codproducto, $codunidad]
        )->row_array();

        if (empty($ubicacion)) {
            $unidadInfo = $this->db->query(
                'select u.descripcion as unidad from almacen.productounidades pu inner join almacen.unidades u on (u.codunidad=pu.codunidad) where pu.codproducto=? and pu.codunidad=? and pu.estado=1 limit 1',
                [$codproducto, $codunidad]
            )->row_array();

            if (empty($unidadInfo)) {
                $base['codunidad'] = $codunidad;
                $base['mensaje'] = 'Sin unidad configurada.';
                return $base;
            }

            $base['codunidad'] = $codunidad;
            $base['unidad'] = $unidadInfo['unidad'];
            $base['stock_actual'] = 0;
            $base['stock_final'] = round((float) $cantidad, 3);
            $base['valido'] = true;
            $base['seleccionado'] = true;
            $base['mensaje'] = 'Se agregara a este almacen.';
            return $base;
        }

        $stockActual = (int) $ubicacion['estado'] === 1 ? (float) $ubicacion['stockactualconvertido'] : 0;

        $base['codunidad'] = $codunidad;
        $base['unidad'] = $ubicacion['unidad'];
        $base['stock_actual'] = round($stockActual, 3);
        $base['stock_final'] = $modoStock === 'reemplazar' ? round((float) $cantidad, 3) : round($stockActual + (float) $cantidad, 3);

        if ($ignorarStockCero && $stockActual == 0.0) {
            $base['omitido'] = true;
            $base['mensaje'] = 'Stock actual 0.';
            return $base;
        }

        $base['valido'] = true;
        $base['seleccionado'] = true;
        $base['mensaje'] = (int) $ubicacion['estado'] === 1 ? 'OK' : 'Se reactivara en este almacen.';

        return $base;
    }

    private function aplicar_stock_producto_excel($codigo, $cantidad, $codunidad, $ignorarStockCero, $row, $codalmacen, $crearProductos = false, $datosNuevo = [], $modoStock = 'sumar', $reemplazarNombre = false)
    {
        $modoStock = $this->modo_stock_migrar($modoStock);
        $descripcion = isset($datosNuevo['descripcion']) ? $this->normalizar_texto($datosNuevo['descripcion']) : '';
        $codigoBarra = isset($datosNuevo['codigo_barra']) ? $this->normalizar_texto($datosNuevo['codigo_barra']) : '';
        $producto = $this->buscar_producto_migrar($codigo, $descripcion, $codigoBarra);
        if (empty($producto)) {
            if (!$crearProductos) {
                return ['estado' => 0, 'omitido' => 0, 'mensaje' => 'Fila ' . $row . ': no existe producto con codigo ' . $codigo . '.'];
            }

            $creado = $this->crear_producto_migrar($codigo, $cantidad, $codalmacen, $datosNuevo);
            if ($creado['estado'] == 1) {
                return [
                    'estado' => 1,
                    'omitido' => 0,
                    'mensaje' => '',
                    'codproducto' => isset($creado['codproducto']) ? (int) $creado['codproducto'] : 0,
                    'codunidad' => isset($creado['codunidad']) ? (int) $creado['codunidad'] : 0
                ];
            }
            return ['estado' => 0, 'omitido' => 0, 'mensaje' => 'Fila ' . $row . ': ' . $creado['mensaje']];
        }

        $codproducto = (int) $producto['codproducto'];

        if ($reemplazarNombre && $descripcion !== '' && $descripcion !== $this->normalizar_texto($producto['descripcion'])) {
            $this->db->where('codproducto', $codproducto);
            $this->db->update('almacen.productos', ['descripcion' => $descripcion]);
        }

        if ($codunidad <= 0) {
            $unidadBase = $this->db->query(
                'select codunidad from almacen.productoubicacion where codalmacen=? and codproducto=? and estado=1 order by factor asc limit 1',
                [$codalmacen, $codproducto]
            )->row_array();

            if (empty($unidadBase)) {
                $unidadBase = $this->db->query(
                    'select codunidad from almacen.productounidades where codproducto=? and estado=1 order by factor asc limit 1',
                    [$codproducto]
                )->row_array();
            }

            $codunidad = !empty($unidadBase) ? (int) $unidadBase['codunidad'] : 0;
        }

        if ($codunidad <= 0) {
            return ['estado' => 0, 'omitido' => 0, 'mensaje' => 'Fila ' . $row . ': no se encontro unidad para el producto ' . $codigo . '.'];
        }

        if (!$this->cantidad_valida_migrar($cantidad, $modoStock)) {
            $mensaje = $modoStock === 'reemplazar' ? 'cantidad/stock debe ser 0 o mayor.' : 'cantidad/stock a sumar debe ser mayor a 0.';
            return ['estado' => 0, 'omitido' => 0, 'mensaje' => 'Fila ' . $row . ': ' . $mensaje];
        }

        $ubicacion = $this->db->query(
            'select * from almacen.productoubicacion where codalmacen=? and codproducto=? and codunidad=? limit 1',
            [$codalmacen, $codproducto, $codunidad]
        )->row_array();

        if (empty($ubicacion)) {
            $creada = $this->crear_ubicacion_producto_migrar($codproducto, $codunidad, $codalmacen, $cantidad, $modoStock);
            if ($creada['estado'] != 1) {
                return ['estado' => 0, 'omitido' => 0, 'mensaje' => 'Fila ' . $row . ': ' . $creada['mensaje']];
            }

            return ['estado' => 1, 'omitido' => 0, 'mensaje' => '', 'codproducto' => $codproducto, 'codunidad' => $codunidad];
        }

        if ($ignorarStockCero && (float) $ubicacion['stockactualconvertido'] == 0.0) {
            return ['estado' => 0, 'omitido' => 1, 'mensaje' => ''];
        }

        $unidadActual = $this->db->query(
            'select factor from almacen.productounidades where codproducto=? and codunidad=? and estado=1 limit 1',
            [$codproducto, $codunidad]
        )->row_array();

        $factorActual = !empty($unidadActual) && (float) $unidadActual['factor'] > 0 ? (float) $unidadActual['factor'] : (float) $ubicacion['factor'];
        if ($factorActual <= 0) {
            $factorActual = 1;
        }

        $stockNuevo = $modoStock === 'reemplazar'
            ? (float) $cantidad
            : (float) $ubicacion['stockactual'] + (float) $cantidad;
        $stockRealNuevo = $modoStock === 'reemplazar'
            ? (float) $cantidad
            : (float) $ubicacion['stockactualreal'] + (float) $cantidad;
        $stockConvertidoNuevo = $modoStock === 'reemplazar'
            ? (float) $cantidad
            : (float) $ubicacion['stockactualconvertido'] + (float) $cantidad;

        $this->db->where('codalmacen', $codalmacen);
        $this->db->where('codproducto', $codproducto);
        $this->db->where('codunidad', $codunidad);
        $this->db->update('almacen.productoubicacion', [
            'stockactual' => (float) round($stockNuevo, 3),
            'stockactualreal' => (float) round($stockRealNuevo, 3),
            'stockactualconvertido' => (float) round($stockConvertidoNuevo, 3),
            'estado' => 1
        ]);

        $ubicaciones = $this->db->query(
            'select * from almacen.productoubicacion where codalmacen=? and codproducto=?',
            [$codalmacen, $codproducto]
        )->result_array();

        foreach ($ubicaciones as $value) {
            if ((int) $value['codunidad'] === $codunidad) {
                continue;
            }

            $unidadDestino = $this->db->query(
                'select factor from almacen.productounidades where codproducto=? and codunidad=? and estado=1 limit 1',
                [$codproducto, (int) $value['codunidad']]
            )->row_array();

            $factorDestino = !empty($unidadDestino) && (float) $unidadDestino['factor'] > 0 ? (float) $unidadDestino['factor'] : (float) $value['factor'];
            if ($factorDestino <= 0) {
                $factorDestino = 1;
            }

            $cantidadConvertida = ((float) $cantidad * $factorActual) / $factorDestino;
            $stockConvertidoDestino = $modoStock === 'reemplazar'
                ? $cantidadConvertida
                : (float) $value['stockactualconvertido'] + (float) $cantidadConvertida;

            $this->db->where('codalmacen', $codalmacen);
            $this->db->where('codproducto', $codproducto);
            $this->db->where('codunidad', (int) $value['codunidad']);
            $this->db->update('almacen.productoubicacion', [
                'stockactualconvertido' => (float) round($stockConvertidoDestino, 3),
                'estado' => 1
            ]);
        }

        return ['estado' => 1, 'omitido' => 0, 'mensaje' => '', 'codproducto' => $codproducto, 'codunidad' => $codunidad];
    }

    private function limpiar_productos_no_incluidos_migrar($codalmacen, $ubicacionesProcesadas, $limpiezaAlmacen)
    {
        $limpiezaAlmacen = $this->limpieza_almacen_migrar($limpiezaAlmacen);
        if ($limpiezaAlmacen === 'conservar') {
            return 0;
        }

        $productosIncluidos = [];
        foreach ($ubicacionesProcesadas as $ubicacion) {
            if (empty($ubicacion['codproducto'])) {
                continue;
            }
            $productosIncluidos[(int) $ubicacion['codproducto']] = true;
        }

        $ubicaciones = $this->db->query(
            'select codproducto, codunidad from almacen.productoubicacion where codalmacen=?',
            [(int) $codalmacen]
        )->result_array();

        $limpiados = 0;
        foreach ($ubicaciones as $ubicacion) {
            if (isset($productosIncluidos[(int) $ubicacion['codproducto']])) {
                continue;
            }

            $data = [
                'stockactual' => 0,
                'stockactualreal' => 0,
                'stockactualconvertido' => 0,
                'preciostockvalorizado' => 0
            ];
            if ($limpiezaAlmacen === 'inactivar') {
                $data['estado'] = 0;
            }

            $this->db->where('codalmacen', (int) $codalmacen);
            $this->db->where('codproducto', (int) $ubicacion['codproducto']);
            $this->db->where('codunidad', (int) $ubicacion['codunidad']);
            $this->db->update('almacen.productoubicacion', $data);
            $limpiados++;
        }

        return $limpiados;
    }

    private function crear_ubicacion_producto_migrar($codproducto, $codunidad, $codalmacen, $cantidad, $modoStock)
    {
        $almacen = $this->db->get_where('almacen.almacenes', ['codalmacen' => (int) $codalmacen, 'estado' => 1])->row_array();
        if (empty($almacen)) {
            return ['estado' => 0, 'mensaje' => 'almacen destino invalido.'];
        }

        $unidad = $this->db->query(
            'select * from almacen.productounidades where codproducto=? and codunidad=? and estado=1 limit 1',
            [(int) $codproducto, (int) $codunidad]
        )->row_array();
        if (empty($unidad)) {
            return ['estado' => 0, 'mensaje' => 'producto sin unidad configurada para este stock.'];
        }

        $stock = (float) $cantidad;
        $dataUbicacion = [
            'codalmacen' => (int) $codalmacen,
            'codproducto' => (int) $codproducto,
            'codunidad' => (int) $codunidad,
            'codsucursal' => isset($almacen['codsucursal']) ? (int) $almacen['codsucursal'] : null,
            'stockactual' => $stock,
            'stockactualreal' => $stock,
            'stockactualconvertido' => $stock,
            'preciostockvalorizado' => $stock * (isset($unidad['preciocompra']) ? (float) $unidad['preciocompra'] : 0),
            'ventarecogo' => 0,
            'comprarecogo' => 0,
            'stockminimo' => 0,
            'stockmaximo' => 0,
            'factor' => isset($unidad['factor']) ? (float) $unidad['factor'] : 1,
            'preciocompra' => isset($unidad['preciocompra']) ? (float) $unidad['preciocompra'] : 0,
            'pventapublico' => isset($unidad['pventapublico']) ? (float) $unidad['pventapublico'] : 0,
            'pventamin' => isset($unidad['pventamin']) ? (float) $unidad['pventamin'] : 0,
            'pventacredito' => isset($unidad['pventacredito']) ? (float) $unidad['pventacredito'] : 0,
            'pventaxmayor' => isset($unidad['pventaxmayor']) ? (float) $unidad['pventaxmayor'] : 0,
            'pventaadicional' => isset($unidad['pventaadicional']) ? (float) $unidad['pventaadicional'] : 0,
            'preciocosto' => isset($unidad['preciocosto']) ? (float) $unidad['preciocosto'] : 0,
            'gastos' => isset($unidad['gastos']) ? (float) $unidad['gastos'] : 0,
            'codigobarra' => isset($unidad['codigobarra']) ? $unidad['codigobarra'] : '',
            'stockpedido' => 0,
            'stockproveedor' => 0,
            'codafectacionigvcompra' => isset($almacen['codafectacionigv']) && (int) $almacen['codafectacionigv'] > 0 ? (int) $almacen['codafectacionigv'] : 1,
            'codafectacionigvventa' => isset($almacen['codafectacionigv']) && (int) $almacen['codafectacionigv'] > 0 ? (int) $almacen['codafectacionigv'] : 1,
            'comisionvendedor' => 0,
            'estado' => 1
        ];

        $this->upsert_por_filtro_migrar('almacen.productoubicacion', $dataUbicacion, [
            'codalmacen' => (int) $codalmacen,
            'codproducto' => (int) $codproducto,
            'codunidad' => (int) $codunidad
        ]);

        return ['estado' => 1, 'mensaje' => ''];
    }

    private function asegurar_catalogos_generales_migrar()
    {
        $codlinea = $this->asegurar_linea_general_migrar();
        $codmarca = $this->asegurar_marca_generica_migrar();
        $codfamilia = $this->asegurar_familia_general_migrar($codlinea);

        $sucursales = $this->db->query('select codsucursal from public.sucursales where estado=1')->result_array();
        foreach ($sucursales as $sucursal) {
            $existe = $this->db->get_where('almacen.lineasxsucursales', [
                'codlinea' => $codlinea,
                'codsucursal' => (int) $sucursal['codsucursal']
            ])->row_array();

            if (empty($existe)) {
                $this->db->insert('almacen.lineasxsucursales', [
                    'codlinea' => $codlinea,
                    'codsucursal' => (int) $sucursal['codsucursal']
                ]);
            }
        }

        return [
            'codlinea' => $codlinea,
            'codfamilia' => $codfamilia,
            'codmarca' => $codmarca
        ];
    }

    private function asegurar_linea_general_migrar()
    {
        $registro = $this->db->query("select codlinea from almacen.lineas where upper(trim(descripcion))='GENERAL' limit 1")->row_array();
        if (!empty($registro)) {
            $this->db->where('codlinea', (int) $registro['codlinea']);
            $this->db->update('almacen.lineas', ['descripcion' => 'GENERAL', 'abreviatura' => 'GEN', 'estado' => 1]);
            return (int) $registro['codlinea'];
        }

        $this->db->insert('almacen.lineas', ['descripcion' => 'GENERAL', 'abreviatura' => 'GEN', 'estado' => 1]);
        return $this->ultimo_id_insertado_migrar('almacen.lineas', 'codlinea');
    }

    private function asegurar_marca_generica_migrar()
    {
        $registro = $this->db->query("select codmarca from almacen.marcas where upper(trim(descripcion))='GENERICO' limit 1")->row_array();
        if (!empty($registro)) {
            $this->db->where('codmarca', (int) $registro['codmarca']);
            $this->db->update('almacen.marcas', ['descripcion' => 'GENERICO', 'estado' => 1]);
            return (int) $registro['codmarca'];
        }

        $this->db->insert('almacen.marcas', ['descripcion' => 'GENERICO', 'estado' => 1]);
        return $this->ultimo_id_insertado_migrar('almacen.marcas', 'codmarca');
    }

    private function asegurar_familia_general_migrar($codlinea)
    {
        $registro = $this->db->query("select codfamilia from almacen.familias where upper(trim(descripcion))='GENERAL' limit 1")->row_array();
        if (!empty($registro)) {
            $this->db->where('codfamilia', (int) $registro['codfamilia']);
            $this->db->update('almacen.familias', [
                'descripcion' => 'GENERAL',
                'abreviatura' => 'GEN',
                'codlinea' => (int) $codlinea,
                'estado' => 1
            ]);
            return (int) $registro['codfamilia'];
        }

        $this->db->insert('almacen.familias', [
            'descripcion' => 'GENERAL',
            'abreviatura' => 'GEN',
            'codlinea' => (int) $codlinea,
            'estado' => 1
        ]);
        return $this->ultimo_id_insertado_migrar('almacen.familias', 'codfamilia');
    }

    private function normalizar_productos_generales_migrar($catalogos)
    {
        $this->db->update('almacen.productos', [
            'codfamilia' => (int) $catalogos['codfamilia'],
            'codlinea' => (int) $catalogos['codlinea'],
            'codmarca' => (int) $catalogos['codmarca']
        ]);

        return $this->db->affected_rows();
    }

    private function unificar_duplicados_barra_migrar()
    {
        $pares = $this->db->query(
            "select distinct d.codigobarra, d.codproducto_maestro, pu.codproducto as codproducto_duplicado
             from (
                select trim(pu.codigobarra) as codigobarra, min(p.codproducto) as codproducto_maestro
                from almacen.productounidades pu
                inner join almacen.productos p on (p.codproducto=pu.codproducto)
                where p.estado=1
                  and pu.estado=1
                  and coalesce(trim(pu.codigobarra),'') <> ''
                group by trim(pu.codigobarra)
                having count(distinct p.codproducto) > 1
             ) d
             inner join almacen.productounidades pu
                on trim(pu.codigobarra)=d.codigobarra
               and pu.estado=1
             inner join almacen.productos p
                on p.codproducto=pu.codproducto
               and p.estado=1
             where pu.codproducto <> d.codproducto_maestro
             order by d.codigobarra, pu.codproducto"
        )->result_array();

        $duplicados = [];
        $ubicaciones = 0;

        foreach ($pares as $par) {
            $codproductoMaestro = (int) $par['codproducto_maestro'];
            $codproductoDuplicado = (int) $par['codproducto_duplicado'];
            if ($codproductoMaestro <= 0 || $codproductoDuplicado <= 0 || isset($duplicados[$codproductoDuplicado])) {
                continue;
            }

            $ubicaciones += $this->mover_unidades_duplicado_migrar($codproductoMaestro, $codproductoDuplicado);
            $ubicaciones += $this->mover_ubicaciones_duplicado_migrar($codproductoMaestro, $codproductoDuplicado);

            $this->db->where('codproducto', $codproductoDuplicado);
            $this->db->update('almacen.productounidades', ['estado' => 0]);

            $this->db->where('codproducto', $codproductoDuplicado);
            $this->db->update('almacen.productoubicacion', [
                'stockactual' => 0,
                'stockactualreal' => 0,
                'stockactualconvertido' => 0,
                'preciostockvalorizado' => 0,
                'estado' => 0
            ]);

            $this->db->where('codproducto', $codproductoDuplicado);
            $this->db->update('almacen.productos', ['estado' => 0]);

            $duplicados[$codproductoDuplicado] = true;
        }

        return [
            'duplicados' => count($duplicados),
            'ubicaciones' => $ubicaciones
        ];
    }

    private function mover_unidades_duplicado_migrar($codproductoMaestro, $codproductoDuplicado)
    {
        $unidades = $this->db->get_where('almacen.productounidades', ['codproducto' => (int) $codproductoDuplicado, 'estado' => 1])->result_array();
        $movidas = 0;

        foreach ($unidades as $unidad) {
            $existe = $this->db->get_where('almacen.productounidades', [
                'codproducto' => (int) $codproductoMaestro,
                'codunidad' => (int) $unidad['codunidad']
            ])->row_array();

            if (empty($existe)) {
                $unidad['codproducto'] = (int) $codproductoMaestro;
                $this->db->insert('almacen.productounidades', $unidad);
                $movidas++;
                continue;
            }

            if (empty($existe['codigobarra']) && !empty($unidad['codigobarra'])) {
                $this->db->where('codproducto', (int) $codproductoMaestro);
                $this->db->where('codunidad', (int) $unidad['codunidad']);
                $this->db->update('almacen.productounidades', [
                    'codigobarra' => $unidad['codigobarra'],
                    'estado' => 1
                ]);
            }
        }

        return $movidas;
    }

    private function mover_ubicaciones_duplicado_migrar($codproductoMaestro, $codproductoDuplicado)
    {
        $ubicaciones = $this->db->get_where('almacen.productoubicacion', ['codproducto' => (int) $codproductoDuplicado])->result_array();
        $movidas = 0;

        foreach ($ubicaciones as $ubicacion) {
            $filtro = [
                'codalmacen' => (int) $ubicacion['codalmacen'],
                'codproducto' => (int) $codproductoMaestro,
                'codunidad' => (int) $ubicacion['codunidad']
            ];
            $maestro = $this->db->get_where('almacen.productoubicacion', $filtro)->row_array();

            if (empty($maestro)) {
                $ubicacion['codproducto'] = (int) $codproductoMaestro;
                $this->db->insert('almacen.productoubicacion', $ubicacion);
                $movidas++;
                continue;
            }

            foreach ($filtro as $campo => $valor) {
                $this->db->where($campo, $valor);
            }
            $this->db->update('almacen.productoubicacion', [
                'stockactual' => (float) $maestro['stockactual'] + (float) $ubicacion['stockactual'],
                'stockactualreal' => (float) $maestro['stockactualreal'] + (float) $ubicacion['stockactualreal'],
                'stockactualconvertido' => (float) $maestro['stockactualconvertido'] + (float) $ubicacion['stockactualconvertido'],
                'preciostockvalorizado' => (float) $maestro['preciostockvalorizado'] + (float) $ubicacion['preciostockvalorizado'],
                'estado' => ((int) $maestro['estado'] === 1 || (int) $ubicacion['estado'] === 1) ? 1 : 0
            ]);
            $movidas++;
        }

        return $movidas;
    }

    private function buscar_producto_migrar($codigo, $descripcion = '', $codigoBarra = '')
    {
        $codigo = $this->normalizar_texto($codigo);
        $descripcion = $this->normalizar_texto($descripcion);
        $codigoBarra = $this->normalizar_texto($codigoBarra);

        if ($codigo !== '') {
            $producto = $this->db->get_where('almacen.productos', ['codigo' => $codigo, 'estado' => 1])->row_array();
            if (!empty($producto)) {
                return $producto;
            }

            if (is_numeric($codigo)) {
                $producto = $this->db->get_where('almacen.productos', ['codproducto' => (int) $codigo, 'estado' => 1])->row_array();
                if (!empty($producto)) {
                    return $producto;
                }
            }

            $producto = $this->buscar_producto_por_barra_migrar($codigo);
            if (!empty($producto)) {
                return $producto;
            }
        }

        if ($codigoBarra !== '') {
            $producto = $this->buscar_producto_por_barra_migrar($codigoBarra);
            if (!empty($producto)) {
                return $producto;
            }
        }

        if ($descripcion !== '') {
            $producto = $this->db->query(
                'select * from almacen.productos where estado=1 and upper(trim(descripcion))=upper(trim(?)) limit 1',
                [$descripcion]
            )->row_array();
            if (!empty($producto)) {
                return $producto;
            }
        }

        return [];
    }

    private function buscar_producto_por_barra_migrar($codigoBarra)
    {
        $codigoBarra = $this->normalizar_texto($codigoBarra);
        if ($codigoBarra === '') {
            return [];
        }

        return $this->db->query(
            'select p.* from almacen.productos p inner join almacen.productounidades u on (p.codproducto=u.codproducto) where p.estado=1 and u.estado=1 and u.codigobarra=? limit 1',
            [$codigoBarra]
        )->row_array();
    }

    private function crear_producto_migrar($codigo, $cantidad, $codalmacen, $datos)
    {
        $descripcion = isset($datos['descripcion']) ? $this->normalizar_texto($datos['descripcion']) : '';
        if ($descripcion === '') {
            return ['estado' => 0, 'mensaje' => 'falta descripcion para crear producto.'];
        }

        $almacen = $this->db->get_where('almacen.almacenes', ['codalmacen' => $codalmacen, 'estado' => 1])->row_array();
        if (empty($almacen)) {
            return ['estado' => 0, 'mensaje' => 'almacen destino invalido.'];
        }

        $codfamilia = $this->resolver_catalogo_migrar('familias', 'codfamilia', isset($datos['familia']) ? $datos['familia'] : '', 'GENERAL');
        $codlinea = $this->resolver_catalogo_migrar('lineas', 'codlinea', isset($datos['linea']) ? $datos['linea'] : '', 'GENERAL');
        $codmarca = $this->resolver_catalogo_migrar('marcas', 'codmarca', isset($datos['marca']) ? $datos['marca'] : '', 'GENERICO');
        $codunidad = $this->resolver_unidad_migrar(isset($datos['unidad_texto']) ? $datos['unidad_texto'] : '');

        if ($codfamilia <= 0 || $codlinea <= 0 || $codmarca <= 0 || $codunidad <= 0) {
            return ['estado' => 0, 'mensaje' => 'no se pudo resolver familia, linea, marca o unidad.'];
        }

        $precioCompra = isset($datos['precio_compra']) ? (float) $datos['precio_compra'] : 0;
        $precioVenta = isset($datos['precio_venta']) ? (float) $datos['precio_venta'] : 0;
        $codigoBarra = isset($datos['codigo_barra']) ? $this->normalizar_texto($datos['codigo_barra']) : '';

        $this->db->insert('almacen.productos', [
            'codfamilia' => $codfamilia,
            'codlinea' => $codlinea,
            'codmarca' => $codmarca,
            'codempresa' => isset($_SESSION['phuyu_codempresa']) ? (int) $_SESSION['phuyu_codempresa'] : 1,
            'codigo' => $codigo !== '' ? $codigo : '',
            'descripcion' => $descripcion,
            'afectoicbper' => 0,
            'controlstock' => 1,
            'afectoigvcompra' => 0,
            'afectoigvventa' => 0,
            'calcular' => 0,
            'paraventa' => 1,
            'codatencion' => 0,
            'caracteristicas' => '',
            'tipo' => 1,
            'controlarseries' => 0,
            'estado' => 1
        ]);

        $codproducto = $this->ultimo_id_insertado_migrar('almacen.productos', 'codproducto');
        if ($codproducto <= 0) {
            return ['estado' => 0, 'mensaje' => 'no se pudo obtener codigo del producto creado.'];
        }

        if ($codigo === '') {
            $codigo = '000' . $codproducto;
            $this->db->where('codproducto', $codproducto);
            $this->db->update('almacen.productos', ['codigo' => $codigo]);
        }

        $dataUnidad = [
            'codproducto' => $codproducto,
            'codunidad' => $codunidad,
            'codsucursal' => isset($_SESSION['phuyu_codsucursal']) ? (int) $_SESSION['phuyu_codsucursal'] : null,
            'factor' => 1,
            'preciocompra' => $precioCompra,
            'preciocosto' => $precioCompra,
            'pventapublico' => $precioVenta,
            'pventamin' => $precioVenta,
            'pventacredito' => $precioVenta,
            'pventaxmayor' => $precioVenta,
            'pventaadicional' => $precioVenta,
            'codigobarra' => $codigoBarra,
            'estado' => 1
        ];
        $this->upsert_por_filtro_migrar('almacen.productounidades', $dataUnidad, ['codproducto' => $codproducto, 'codunidad' => $codunidad]);

        $dataUbicacion = $dataUnidad;
        unset($dataUbicacion['codsucursal']);
        $dataUbicacion['codalmacen'] = $codalmacen;
        $dataUbicacion['codsucursal'] = isset($almacen['codsucursal']) ? (int) $almacen['codsucursal'] : (isset($_SESSION['phuyu_codsucursal']) ? (int) $_SESSION['phuyu_codsucursal'] : null);
        $dataUbicacion['stockactual'] = (float) $cantidad;
        $dataUbicacion['stockactualreal'] = (float) $cantidad;
        $dataUbicacion['stockactualconvertido'] = (float) $cantidad;
        $dataUbicacion['preciostockvalorizado'] = (float) $cantidad * $precioCompra;
        $dataUbicacion['codafectacionigvcompra'] = isset($almacen['codafectacionigv']) && (int) $almacen['codafectacionigv'] > 0 ? (int) $almacen['codafectacionigv'] : (isset($_SESSION['phuyu_afectacionigv']) ? (int) $_SESSION['phuyu_afectacionigv'] : 1);
        $dataUbicacion['codafectacionigvventa'] = $dataUbicacion['codafectacionigvcompra'];
        $dataUbicacion['comisionvendedor'] = 0;

        $this->upsert_por_filtro_migrar('almacen.productoubicacion', $dataUbicacion, [
            'codalmacen' => $codalmacen,
            'codproducto' => $codproducto,
            'codunidad' => $codunidad
        ]);

        return ['estado' => 1, 'mensaje' => '', 'codproducto' => $codproducto, 'codunidad' => $codunidad];
    }

    private function resolver_catalogo_migrar($tabla, $pk, $valor, $defecto)
    {
        $texto = $this->normalizar_texto($valor);
        if ($texto === '') {
            $texto = $defecto;
        }

        if (is_numeric($texto)) {
            $registro = $this->db->get_where('almacen.' . $tabla, [$pk => (int) $texto, 'estado' => 1])->row_array();
            if (!empty($registro)) {
                return (int) $registro[$pk];
            }
        }

        $registro = $this->db->query('select ' . $pk . ' from almacen.' . $tabla . ' where estado=1 and upper(trim(descripcion))=? limit 1', [strtoupper($texto)])->row_array();
        if (!empty($registro)) {
            return (int) $registro[$pk];
        }

        $this->db->insert('almacen.' . $tabla, ['descripcion' => $texto, 'estado' => 1]);
        return $this->ultimo_id_insertado_migrar('almacen.' . $tabla, $pk);
    }

    private function resolver_unidad_migrar($valor)
    {
        $texto = $this->normalizar_texto($valor);
        if ($texto === '') {
            $texto = 'UNIDAD';
        }

        if (is_numeric($texto)) {
            $registro = $this->db->get_where('almacen.unidades', ['codunidad' => (int) $texto, 'estado' => 1])->row_array();
            if (!empty($registro)) {
                return (int) $registro['codunidad'];
            }
        }

        $registro = $this->db->query('select codunidad from almacen.unidades where estado=1 and (upper(trim(descripcion))=? or upper(trim(oficial))=?) limit 1', [strtoupper($texto), strtoupper($texto)])->row_array();
        if (!empty($registro)) {
            return (int) $registro['codunidad'];
        }

        $oficial = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $texto), 0, 3));
        if ($oficial === '') {
            $oficial = 'NIU';
        }
        $this->db->insert('almacen.unidades', ['descripcion' => $texto, 'oficial' => $oficial, 'estado' => 1]);
        return $this->ultimo_id_insertado_migrar('almacen.unidades', 'codunidad');
    }

    private function buscar_unidad_migrar($valor)
    {
        $texto = $this->normalizar_texto($valor);
        if ($texto === '') {
            $texto = 'UNIDAD';
        }

        if (is_numeric($texto)) {
            $registro = $this->db->get_where('almacen.unidades', ['codunidad' => (int) $texto, 'estado' => 1])->row_array();
            if (!empty($registro)) {
                return (int) $registro['codunidad'];
            }
        }

        $registro = $this->db->query('select codunidad from almacen.unidades where estado=1 and (upper(trim(descripcion))=? or upper(trim(oficial))=?) limit 1', [strtoupper($texto), strtoupper($texto)])->row_array();
        return !empty($registro) ? (int) $registro['codunidad'] : 0;
    }

    private function upsert_por_filtro_migrar($tabla, $data, $filtro)
    {
        $existe = $this->db->get_where($tabla, $filtro)->row_array();
        if (empty($existe)) {
            $this->db->insert($tabla, $data);
            return;
        }

        foreach ($filtro as $campo => $valor) {
            $this->db->where($campo, $valor);
        }
        $this->db->update($tabla, $data);
    }

    private function ultimo_id_insertado_migrar($tabla, $columna)
    {
        $id = (int) $this->db->insert_id();
        if ($id > 0) {
            return $id;
        }

        $registro = $this->db->query('select currval(pg_get_serial_sequence(?, ?)) as id', [$tabla, $columna])->row_array();
        return isset($registro['id']) ? (int) $registro['id'] : 0;
    }

    private function mapear_cabeceras_migrar_stock($cabeceras)
    {
        $mapa = ['codigo' => '', 'cantidad' => '', 'unidad' => '', 'aplicar' => ''];
        $aliases = [
            'codigo' => ['codigo', 'codigoproducto', 'codigo_producto', 'codigodeproducto', 'cod_producto', 'codproducto', 'sku', 'cod', 'codigoitem', 'codigo_item'],
            'cantidad' => ['cantidadsumar', 'cantidad_sumar', 'cantidadasumar', 'cantidad_a_sumar', 'cantidad', 'stocksumar', 'stock_sumar', 'stockasumar', 'stock_a_sumar', 'stock', 'stockinicial', 'stock_inicial', 'inventario', 'existencia', 'unidades', 'qty', 'cantidadstock', 'cantidad_stock'],
            'unidad' => ['codunidad', 'cod_unidad', 'unidad', 'unidadmedida', 'unidad_medida'],
            'aplicar' => ['aplicar', 'procesar', 'seleccionar', 'migrar', 'usar']
        ];

        foreach ($cabeceras as $columna => $cabecera) {
            $normalizada = $this->normalizar_cabecera_excel($cabecera);
            foreach ($aliases as $campo => $opciones) {
                if ($mapa[$campo] === '' && in_array($normalizada, $opciones, true)) {
                    $mapa[$campo] = $columna;
                }
            }
        }

        return $mapa;
    }

    private function validar_cabeceras_migrar_stock($mapa)
    {
        if ($mapa['codigo'] === '' || $mapa['cantidad'] === '') {
            return [
                'estado' => false,
                'mensaje' => 'No se pudieron detectar las columnas obligatorias. El Excel debe tener una cabecera para codigo/SKU y otra para cantidad/stock.'
            ];
        }

        return ['estado' => true, 'mensaje' => ''];
    }

    private function columnas_detectadas($cabeceras)
    {
        $columnas = [];
        foreach ($cabeceras as $columna => $cabecera) {
            $texto = $this->normalizar_texto($cabecera);
            if ($texto !== '') {
                $columnas[] = [
                    'columna' => $columna,
                    'cabecera' => $texto
                ];
            }
        }

        return $columnas;
    }

    private function mapeo_detectado($mapaColumnas, $cabeceras)
    {
        $labels = [
            'codigo' => 'Codigo / SKU',
            'cantidad' => 'Cantidad',
            'unidad' => 'Unidad',
            'aplicar' => 'Aplicar'
        ];
        $mapeo = [];

        foreach ($labels as $campo => $label) {
            $columna = isset($mapaColumnas[$campo]) ? $mapaColumnas[$campo] : '';
            $mapeo[] = [
                'campo' => $campo,
                'label' => $label,
                'columna' => $columna,
                'cabecera' => ($columna !== '' && isset($cabeceras[$columna])) ? $this->normalizar_texto($cabeceras[$columna]) : 'No detectado'
            ];
        }

        return $mapeo;
    }

    private function leer_archivo($archivo)
    {
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

        if ($extension === 'csv' || $extension === 'txt') {
            return $this->leer_csv($archivo['tmp_name'], false);
        }

        if ($extension === 'xlsx') {
            return $this->leer_xlsx($archivo['tmp_name'], false);
        }

        if ($extension === 'xls' || $extension === 'html' || $extension === 'htm') {
            $contenido = (string) file_get_contents($archivo['tmp_name'], false, null, 0, 512);
            if (stripos($contenido, '<html') !== false || stripos($contenido, '<table') !== false) {
                return $this->leer_html_xls($archivo['tmp_name'], false);
            }
            return $this->leer_phpexcel($archivo['tmp_name'], false);
        }

        throw new Exception('Formato no soportado');
    }

    private function leer_cabeceras_archivo($archivo)
    {
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

        if ($extension === 'csv' || $extension === 'txt') {
            return $this->leer_csv($archivo['tmp_name'], true);
        }

        if ($extension === 'xlsx') {
            return $this->leer_xlsx($archivo['tmp_name'], true);
        }

        if ($extension === 'xls' || $extension === 'html' || $extension === 'htm') {
            $contenido = (string) file_get_contents($archivo['tmp_name'], false, null, 0, 512);
            if (stripos($contenido, '<html') !== false || stripos($contenido, '<table') !== false) {
                return $this->leer_html_xls($archivo['tmp_name'], true);
            }
            return $this->leer_phpexcel($archivo['tmp_name'], true);
        }

        throw new Exception('Formato no soportado');
    }

    private function leer_csv($archivo, $soloCabeceras)
    {
        $lineaInicial = (string) file_get_contents($archivo, false, null, 0, 4096);
        $delimitador = substr_count($lineaInicial, ';') >= substr_count($lineaInicial, ',') ? ';' : ',';
        $handle = fopen($archivo, 'r');
        $filas = [];
        $numeroFila = 0;

        while (($data = fgetcsv($handle, 0, $delimitador)) !== false) {
            $numeroFila++;
            $fila = [];
            foreach ($data as $index => $valor) {
                $fila[$this->columna_excel_desde_indice($index)] = $valor;
            }

            if ($numeroFila == 1 && $soloCabeceras) {
                fclose($handle);
                return $fila;
            }

            if ($numeroFila == 1) {
                continue;
            }

            $filas[$numeroFila] = $fila;
        }

        fclose($handle);
        return $soloCabeceras ? [] : $filas;
    }

    private function leer_html_xls($archivo, $soloCabeceras)
    {
        $contenido = file_get_contents($archivo);
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $contenido);
        libxml_clear_errors();

        $tablas = $dom->getElementsByTagName('table');
        if ($tablas->length == 0) {
            return $this->leer_csv($archivo, $soloCabeceras);
        }

        $filas = [];
        $numeroFila = 0;
        $rows = $tablas->item(0)->getElementsByTagName('tr');

        foreach ($rows as $row) {
            $numeroFila++;
            $fila = [];
            $celdaIndex = 0;
            foreach ($row->childNodes as $cell) {
                if (!in_array(strtolower($cell->nodeName), ['td', 'th'], true)) {
                    continue;
                }
                $fila[$this->columna_excel_desde_indice($celdaIndex)] = trim($cell->textContent);
                $celdaIndex++;
            }

            if ($numeroFila == 1 && $soloCabeceras) {
                return $fila;
            }

            if ($numeroFila == 1) {
                continue;
            }

            $filas[$numeroFila] = $fila;
        }

        return $soloCabeceras ? [] : $filas;
    }

    private function leer_phpexcel($archivo, $soloCabeceras)
    {
        $anterior = error_reporting();
        $displayErrors = ini_get('display_errors');
        ini_set('display_errors', '0');
        error_reporting(0);

        try {
            $inputFileType = PHPExcel_IOFactory::identify($archivo);
            $objReader = PHPExcel_IOFactory::createReader($inputFileType);
            $objPHPExcel = $objReader->load($archivo);
        } finally {
            error_reporting($anterior);
            ini_set('display_errors', $displayErrors);
        }

        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow();
        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn());
        $filas = [];

        for ($row = 1; $row <= $highestRow; $row++) {
            $fila = [];
            for ($col = 0; $col < $highestColumnIndex; $col++) {
                $columna = $this->columna_excel_desde_indice($col);
                $valor = $sheet->getCell($columna . $row)->getCalculatedValue();
                if ($valor instanceof PHPExcel_RichText) {
                    $valor = $valor->getPlainText();
                }
                $fila[$columna] = $valor;
            }

            if ($row == 1 && $soloCabeceras) {
                return $fila;
            }

            if ($row == 1) {
                continue;
            }

            $filas[$row] = $fila;
        }

        return $soloCabeceras ? [] : $filas;
    }

    private function leer_xlsx($archivo, $soloCabeceras)
    {
        $zip = new ZipArchive();
        if ($zip->open($archivo) !== true) {
            throw new Exception('No se pudo abrir el xlsx');
        }

        $sharedStrings = $this->leer_xlsx_shared_strings($zip);
        $xmlHoja = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if ($xmlHoja === false) {
            throw new Exception('El xlsx no contiene hoja principal');
        }

        $xml = simplexml_load_string($xmlHoja);
        $filas = [];

        foreach ($xml->sheetData->row as $row) {
            $numeroFila = (int) $row['r'];
            $fila = [];
            foreach ($row->c as $cell) {
                $referencia = (string) $cell['r'];
                $columna = preg_replace('/[0-9]/', '', $referencia);
                $tipo = (string) $cell['t'];
                $valor = '';

                if ($tipo === 's') {
                    $indice = (int) $cell->v;
                    $valor = isset($sharedStrings[$indice]) ? $sharedStrings[$indice] : '';
                } elseif ($tipo === 'inlineStr') {
                    $valor = isset($cell->is->t) ? (string) $cell->is->t : '';
                } else {
                    $valor = isset($cell->v) ? (string) $cell->v : '';
                }

                $fila[$columna] = $valor;
            }

            if ($numeroFila == 1 && $soloCabeceras) {
                return $fila;
            }

            if ($numeroFila <= 1) {
                continue;
            }

            $filas[$numeroFila] = $fila;
        }

        return $soloCabeceras ? [] : $filas;
    }

    private function leer_xlsx_shared_strings($zip)
    {
        $xmlStrings = $zip->getFromName('xl/sharedStrings.xml');
        if ($xmlStrings === false) {
            return [];
        }

        $xml = simplexml_load_string($xmlStrings);
        $strings = [];

        foreach ($xml->si as $si) {
            if (isset($si->t)) {
                $strings[] = (string) $si->t;
                continue;
            }

            $texto = '';
            if (isset($si->r)) {
                foreach ($si->r as $run) {
                    $texto .= isset($run->t) ? (string) $run->t : '';
                }
            }
            $strings[] = $texto;
        }

        return $strings;
    }

    private function descargar_xls($archivo, $hojas)
    {
        $excel = new PHPExcel();
        $excel->getProperties()->setCreator('Phuyu Comercial')->setTitle(pathinfo($archivo, PATHINFO_FILENAME));

        foreach ($hojas as $index => $hoja) {
            $sheet = $index == 0 ? $excel->setActiveSheetIndex(0) : $excel->createSheet($index);
            $sheet->setTitle($this->nombre_hoja_excel(isset($hoja['nombre']) ? $hoja['nombre'] : 'Hoja ' . ($index + 1)));
            $sheet->fromArray($hoja['filas'], null, 'A1');
            $highestColumn = $sheet->getHighestColumn();
            $highestRow = $sheet->getHighestRow();
            $sheet->freezePane('A2');
            $sheet->setAutoFilter('A1:' . $highestColumn . $highestRow);
            $sheet->getStyle('A1:' . $highestColumn . '1')->getFont()->setBold(true);
            $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
            for ($col = 0; $col < $highestColumnIndex; $col++) {
                $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
            }
        }

        $excel->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $archivo . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $writer->save('php://output');
        exit;
    }

    private function nombre_hoja_excel($nombre)
    {
        $nombre = preg_replace('/[\\\\\\/\\?\\*\\[\\]\\:]/', ' ', $this->normalizar_texto($nombre));
        $nombre = trim(substr($nombre, 0, 31));
        return $nombre === '' ? 'Hoja' : $nombre;
    }

    private function columna_excel_desde_indice($indice)
    {
        $columna = '';
        $indice++;

        while ($indice > 0) {
            $modulo = ($indice - 1) % 26;
            $columna = chr(65 + $modulo) . $columna;
            $indice = (int) (($indice - $modulo) / 26);
        }

        return $columna;
    }

    private function valor_fila($fila, $columna)
    {
        return $columna !== '' && isset($fila[$columna]) ? $fila[$columna] : '';
    }

    private function valor_fila_objeto($fila, $columna)
    {
        if ($columna === '') {
            return '';
        }

        if (is_array($fila)) {
            return isset($fila[$columna]) ? $fila[$columna] : '';
        }

        if (is_object($fila)) {
            return isset($fila->{$columna}) ? $fila->{$columna} : '';
        }

        return '';
    }

    private function normalizar_texto($valor)
    {
        $valor = str_replace("\xEF\xBB\xBF", '', (string) $valor);
        return trim(preg_replace('/\s+/', ' ', $valor));
    }

    private function normalizar_cabecera_excel($valor)
    {
        $texto = strtolower($this->normalizar_texto($valor));
        $texto = strtr($texto, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n']);
        return preg_replace('/[^a-z0-9_]/', '', $texto);
    }

    private function numero_excel($valor, $defecto = 0)
    {
        $valor = $this->normalizar_texto($valor);
        if ($valor === '') {
            return $defecto;
        }

        $valor = str_ireplace(['S/', 'USD', '$', ' '], '', $valor);
        if (strpos($valor, ',') !== false && strpos($valor, '.') === false) {
            $valor = str_replace(',', '.', $valor);
        } else {
            $valor = str_replace(',', '', $valor);
        }

        return is_numeric($valor) ? (float) $valor : $defecto;
    }

    private function entero_excel($valor, $defecto = 0)
    {
        return (int) $this->numero_excel($valor, $defecto);
    }

    private function booleano_excel($valor, $defecto = 0)
    {
        $texto = strtoupper($this->normalizar_texto($valor));
        if ($texto === '') {
            return (int) $defecto;
        }

        if (in_array($texto, ['1', 'SI', 'S', 'YES', 'Y', 'TRUE', 'VERDADERO'], true)) {
            return 1;
        }

        return 0;
    }

    private function limpiar_salida_excel()
    {
        while (ob_get_level() > 0) {
            if (!@ob_end_clean()) {
                break;
            }
        }
    }
}
