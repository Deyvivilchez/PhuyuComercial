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
            $codalmacenActual = isset($_SESSION['phuyu_codalmacen']) ? (int) $_SESSION['phuyu_codalmacen'] : 0;
            $this->load->view('almacen/productos/migrar_stock', compact('almacenes', 'codalmacenActual'));
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
                    ['codigo', 'cantidad_sumar', 'codunidad', 'observacion'],
                    ['PROD001', 8, '', 'codunidad vacio usa la unidad base/factor 1'],
                    ['PROD002', 3.5, 1, 'cantidad_sumar siempre suma al stock actual']
                ]
            ],
            [
                'nombre' => 'Ayuda',
                'filas' => [
                    ['Campo', 'Descripcion'],
                    ['codigo', 'Obligatorio. Puede llamarse codigo, codigo de producto, SKU o similar.'],
                    ['cantidad_sumar', 'Obligatorio. Valor numerico mayor a 0. No se aceptan cantidades negativas.'],
                    ['codunidad', 'Opcional. Si esta vacio se usa la unidad base del producto en el almacen destino.'],
                    ['Importante', 'El proceso suma al stock existente y actualiza las presentaciones convertidas segun factor.']
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

                $resultado = $this->preparar_fila_preview($row, $codigo, $cantidad, $codunidad, $aplicar, $soloAplicar, $ignorarStockCero, $codalmacen);
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
        $soloAplicar = isset($request->solo_aplicar) ? (bool) $request->solo_aplicar : false;

        $colCodigo = isset($mapeo->codigo) ? (string) $mapeo->codigo : '';
        $colCantidad = isset($mapeo->cantidad) ? (string) $mapeo->cantidad : '';
        $colUnidad = isset($mapeo->unidad) ? (string) $mapeo->unidad : '';
        $colAplicar = isset($mapeo->aplicar) ? (string) $mapeo->aplicar : '';

        if ($codalmacen <= 0) {
            echo json_encode(['estado' => 0, 'mensaje' => 'Debe seleccionar el almacen destino.']);
            return;
        }

        if ($colCodigo === '' || $colCantidad === '') {
            echo json_encode(['estado' => 0, 'mensaje' => 'Debe mapear las columnas obligatorias: codigo y cantidad a sumar.']);
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
            $codunidad = $colUnidad !== '' ? $this->entero_excel($this->valor_fila_objeto($fila, $colUnidad), 0) : 0;
            $aplicar = $colAplicar !== '' ? $this->normalizar_texto($this->valor_fila_objeto($fila, $colAplicar)) : '';

            if ($codigo === '' && $cantidad === null && $codunidad == 0 && $aplicar === '') {
                continue;
            }

            $resultado = $this->preparar_fila_preview($idx + 2, $codigo, $cantidad, $codunidad, $aplicar, $soloAplicar, $ignorarStockCero, $codalmacen);
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
        $procesados = 0;
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

                $resultado = $this->sumar_stock_producto_excel($codigo, $cantidad, $codunidad, $ignorarStockCero, $row, $codalmacen);
                if ($resultado['estado'] == 1) {
                    $procesados++;
                } else {
                    $errores[] = $resultado['mensaje'];
                }
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

                if ($cantidad === null || $cantidad <= 0) {
                    $errores[] = 'Fila ' . $row . ': cantidad/stock a sumar debe ser mayor a 0.';
                    continue;
                }

                $resultado = $this->sumar_stock_producto_excel($codigo, $cantidad, $codunidad, $ignorarStockCero, $row, $codalmacen);

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

    private function preparar_fila_preview($row, $codigo, $cantidad, $codunidad, $aplicar, $soloAplicar, $ignorarStockCero, $codalmacen)
    {
        $base = [
            'fila' => (int) $row,
            'codigo' => $codigo,
            'producto' => '',
            'codproducto' => 0,
            'codunidad' => (int) $codunidad,
            'unidad' => '',
            'stock_actual' => '',
            'cantidad' => $cantidad,
            'stock_final' => '',
            'valido' => false,
            'omitido' => false,
            'seleccionado' => false,
            'mensaje' => ''
        ];

        if ($soloAplicar && $this->booleano_excel($aplicar, 0) != 1) {
            $base['omitido'] = true;
            $base['mensaje'] = 'No marcada para aplicar.';
            return $base;
        }

        if ($codigo === '') {
            $base['mensaje'] = 'Falta codigo.';
            return $base;
        }

        $producto = $this->db->get_where('almacen.productos', ['codigo' => $codigo, 'estado' => 1])->row_array();
        if (empty($producto)) {
            $base['mensaje'] = 'Producto no existe.';
            return $base;
        }

        $codproducto = (int) $producto['codproducto'];
        $base['producto'] = $producto['descripcion'];
        $base['codproducto'] = $codproducto;

        if ($cantidad === null || $cantidad <= 0) {
            $base['mensaje'] = 'Cantidad debe ser mayor a 0.';
            return $base;
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
            $base['mensaje'] = 'No se encontro unidad.';
            return $base;
        }

        $ubicacion = $this->db->query(
            'select pu.*, u.descripcion as unidad from almacen.productoubicacion pu inner join almacen.unidades u on (u.codunidad=pu.codunidad) where pu.codalmacen=? and pu.codproducto=? and pu.codunidad=? and pu.estado=1 limit 1',
            [$codalmacen, $codproducto, $codunidad]
        )->row_array();

        if (empty($ubicacion)) {
            $base['codunidad'] = $codunidad;
            $base['mensaje'] = 'Sin ubicacion activa.';
            return $base;
        }

        $stockActual = (float) $ubicacion['stockactualconvertido'];

        $base['codunidad'] = $codunidad;
        $base['unidad'] = $ubicacion['unidad'];
        $base['stock_actual'] = round($stockActual, 3);
        $base['stock_final'] = round($stockActual + (float) $cantidad, 3);

        if ($ignorarStockCero && $stockActual == 0.0) {
            $base['omitido'] = true;
            $base['mensaje'] = 'Stock actual 0.';
            return $base;
        }

        $base['valido'] = true;
        $base['seleccionado'] = true;
        $base['mensaje'] = 'OK';

        return $base;
    }

    private function sumar_stock_producto_excel($codigo, $cantidad, $codunidad, $ignorarStockCero, $row, $codalmacen)
    {
        $producto = $this->db->get_where('almacen.productos', ['codigo' => $codigo, 'estado' => 1])->row_array();
        if (empty($producto)) {
            return ['estado' => 0, 'omitido' => 0, 'mensaje' => 'Fila ' . $row . ': no existe producto con codigo ' . $codigo . '.'];
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
            return ['estado' => 0, 'omitido' => 0, 'mensaje' => 'Fila ' . $row . ': no se encontro unidad para el producto ' . $codigo . '.'];
        }

        $ubicacion = $this->db->query(
            'select * from almacen.productoubicacion where codalmacen=? and codproducto=? and codunidad=? and estado=1 limit 1',
            [$codalmacen, $codproducto, $codunidad]
        )->row_array();

        if (empty($ubicacion)) {
            return ['estado' => 0, 'omitido' => 0, 'mensaje' => 'Fila ' . $row . ': el producto ' . $codigo . ' no tiene ubicacion activa en este almacen/unidad.'];
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

        $this->db->where('codalmacen', $codalmacen);
        $this->db->where('codproducto', $codproducto);
        $this->db->where('codunidad', $codunidad);
        $this->db->update('almacen.productoubicacion', [
            'stockactual' => (float) round((float) $ubicacion['stockactual'] + (float) $cantidad, 3),
            'stockactualreal' => (float) round((float) $ubicacion['stockactualreal'] + (float) $cantidad, 3),
            'stockactualconvertido' => (float) round((float) $ubicacion['stockactualconvertido'] + (float) $cantidad, 3)
        ]);

        $ubicaciones = $this->db->query(
            'select * from almacen.productoubicacion where codalmacen=? and codproducto=? and estado=1',
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

            $this->db->where('codalmacen', $codalmacen);
            $this->db->where('codproducto', $codproducto);
            $this->db->where('codunidad', (int) $value['codunidad']);
            $this->db->update('almacen.productoubicacion', [
                'stockactualconvertido' => (float) round((float) $value['stockactualconvertido'] + (float) $cantidadConvertida, 3)
            ]);
        }

        return ['estado' => 1, 'omitido' => 0, 'mensaje' => ''];
    }

    private function mapear_cabeceras_migrar_stock($cabeceras)
    {
        $mapa = ['codigo' => '', 'cantidad' => '', 'unidad' => '', 'aplicar' => ''];
        $aliases = [
            'codigo' => ['codigo', 'codigoproducto', 'codigo_producto', 'codigodeproducto', 'cod_producto', 'codproducto', 'sku', 'cod', 'codigoitem', 'codigo_item'],
            'cantidad' => ['cantidadsumar', 'cantidad_sumar', 'cantidadasumar', 'cantidad_a_sumar', 'cantidad', 'stocksumar', 'stock_sumar', 'stockasumar', 'stock_a_sumar', 'stock', 'unidades', 'qty', 'cantidadstock', 'cantidad_stock'],
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
                'mensaje' => 'No se pudieron detectar las columnas obligatorias. El Excel debe tener una cabecera para codigo/SKU y otra para cantidad/stock a sumar.'
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
            'cantidad' => 'Cantidad a sumar',
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
