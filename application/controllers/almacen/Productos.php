<?php defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'third_party/phuyu_excel/PHPExcel.php';
require_once APPPATH . 'third_party/phuyu_excel/PHPExcel/IOFactory.php';

class Productos extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('phuyu_model');
    }

    public function index()
    {
        if ($this->input->is_ajax_request()) {
            if (isset($_SESSION['phuyu_usuario'])) {
                if ($_SESSION['phuyu_ruc'] == '20603454112') {
                    $this->load->view('almacen/productos/index_lista');
                } else {
                    $this->load->view('almacen/productos/index');
                }
                // $this->load->view("almacen/productos/index_lista");
            } else {
                $this->load->view('phuyu/505');
            }
        } else {
            $this->load->view('phuyu/404');
        }
    }

    public function lista()
    {
        if ($this->input->is_ajax_request()) {
            $this->request = json_decode(file_get_contents('php://input'));
            $limit = 12;
            $offset = $this->request->pagina * $limit - $limit;

            $lista = $this->db->query("select productos.*, marcas.descripcion as marca 
            from almacen.productos as productos 
            inner join almacen.marcas as marcas on(productos.codmarca=marcas.codmarca)
            where (UPPER(productos.descripcion)
             like UPPER('%" . $this->request->buscar . "%') or UPPER(productos.codigo) 
             like UPPER('%" . $this->request->buscar . "%') or UPPER(marcas.descripcion) 
             like UPPER('%" . $this->request->buscar . "%')
             OR EXISTS (
                SELECT 1 FROM almacen.productounidades pbu
                WHERE pbu.codproducto = productos.codproducto
                  AND pbu.estado = 1
                  AND UPPER(COALESCE(pbu.codigobarra, '')) LIKE UPPER('%" . $this->request->buscar . "%')
             )
             OR EXISTS (
                SELECT 1 FROM almacen.productoubicacion pbo
                WHERE pbo.codproducto = productos.codproducto
                  AND pbo.estado = 1
                  AND UPPER(COALESCE(pbo.codigobarra, '')) LIKE UPPER('%" . $this->request->buscar . "%')
             )
             ) and productos.estado=1 
             order by productos.descripcion, productos.codproducto asc offset " . $offset . ' limit ' . $limit)->result_array();

            foreach ($lista as $key => $value) {
                $precio = $this->db->query('select pventapublico,codunidad,preciocosto from almacen.productounidades where codproducto=' . $value['codproducto'] . ' order by factor')->result_array();

                if (count($precio) == 0) {
                    $lista[$key]['precio'] = 0.0;
                    $codunidad = 0;
                    $lista[$key]['costo'] = 0.0;
                } else {
                    $lista[$key]['precio'] = number_format(round($precio[0]['pventapublico'], 2), 2);
                    $codunidad = $precio[0]['codunidad'];
                    $lista[$key]['costo'] = number_format(round($precio[0]['preciocosto'], 2), 2);
                }

                $stock = $this->db->query('select pu.stockactualconvertido,u.descripcion as unidad from almacen.productoubicacion as pu inner join almacen.unidades as u on(pu.codunidad=u.codunidad) where pu.codproducto=' . $value['codproducto'] . ' and pu.codunidad=' . $codunidad . ' and pu.codalmacen=' . $_SESSION['phuyu_codalmacen'] . ' and pu.estado=1')->result_array();
                if (count($stock) == 0) {
                    $lista[$key]['stock'] = 0;
                    $lista[$key]['unidad'] = 'SIN UNIDAD';
                } else {
                    $lista[$key]['stock'] = round($stock[0]['stockactualconvertido'], 2);
                    $lista[$key]['unidad'] = $stock[0]['unidad'];
                }
            }

            $total = $this->db->query("select count(*) as total from almacen.productos as productos inner join almacen.marcas as marcas on(productos.codmarca=marcas.codmarca) where (UPPER(productos.descripcion) like UPPER('%" . $this->request->buscar . "%') or UPPER(productos.codigo) like UPPER('%" . $this->request->buscar . "%') or UPPER(marcas.descripcion) like UPPER('%" . $this->request->buscar . "%') OR EXISTS (SELECT 1 FROM almacen.productounidades pbu WHERE pbu.codproducto = productos.codproducto AND pbu.estado = 1 AND UPPER(COALESCE(pbu.codigobarra, '')) LIKE UPPER('%" . $this->request->buscar . "%')) OR EXISTS (SELECT 1 FROM almacen.productoubicacion pbo WHERE pbo.codproducto = productos.codproducto AND pbo.estado = 1 AND UPPER(COALESCE(pbo.codigobarra, '')) LIKE UPPER('%" . $this->request->buscar . "%')) ) and productos.estado=1")->result_array();

            $paginas = floor($total[0]['total'] / $limit);
            if ($total[0]['total'] % $limit != 0) {
                $paginas = $paginas + 1;
            }

            $paginacion = [];
            $paginacion['total'] = $total[0]['total'];
            $paginacion['actual'] = $this->request->pagina;
            $paginacion['ultima'] = $paginas;
            $paginacion['desde'] = $offset;
            $paginacion['hasta'] = $offset + $limit;

            echo json_encode(['lista' => $lista, 'paginacion' => $paginacion]);
        } else {
            $this->load->view('phuyu/404');
        }
    }

    function buscar_productos()
    {
        if ($this->input->is_ajax_request()) {
            if (isset($_POST['q'])) {
                $condicionBusqueda = $this->condicion_busqueda_productos($_POST['q'], 'producto', 'marca');
                $productos = $this->db
                    ->query(
                        "select producto.codproducto, producto.codigo,
                        producto.descripcion,
                        marca.descripcion as marca from almacen.productos as producto
                        inner join almacen.marcas as marca on (producto.codmarca=marca.codmarca)
                        where " . $condicionBusqueda . " and producto.estado=1 limit 10",
                    )
                    ->result_array();
            } else {
                $productos = $this->db->query('select producto.codproducto, producto.codigo, producto.descripcion, marca.descripcion as marca from almacen.productos as producto inner join almacen.marcas as marca on (producto.codmarca=marca.codmarca) where producto.estado=1 limit 10')->result_array();
            }
            echo json_encode($productos);
        }
    }

    private function condicion_busqueda_productos($buscar, $productoAlias = 'p', $marcaAlias = 'ma')
    {
        $like = $this->db->escape('%' . $this->db->escape_like_str((string) $buscar) . '%');

        return "(REPLACE(UPPER(" . $productoAlias . ".descripcion),' ','%') LIKE REPLACE(UPPER(" . $like . "),' ','%')
            OR UPPER(" . $productoAlias . ".codigo) LIKE UPPER(" . $like . ")
            OR UPPER(" . $marcaAlias . ".descripcion) LIKE UPPER(" . $like . ")
            OR EXISTS (
                SELECT 1
                FROM almacen.productounidades pbu
                WHERE pbu.codproducto = " . $productoAlias . ".codproducto
                    AND pbu.estado = 1
                    AND UPPER(COALESCE(pbu.codigobarra, '')) LIKE UPPER(" . $like . ")
            )
            OR EXISTS (
                SELECT 1
                FROM almacen.productoubicacion pbo
                WHERE pbo.codproducto = " . $productoAlias . ".codproducto
                    AND pbo.estado = 1
                    AND UPPER(COALESCE(pbo.codigobarra, '')) LIKE UPPER(" . $like . ")
            ))";
    }

    public function nuevo()
    {
        if ($this->input->is_ajax_request()) {
            if (isset($_SESSION['phuyu_usuario'])) {
                $unidades = $this->db->query('select *from almacen.unidades where estado=1 order by descripcion')->result_array();
                $atenciones = $this->db->query('select *from almacen.atenciones where estado=1 order by descripcion')->result_array();
                $afectacionigv = $this->db->query('select *from afectacionigv where estado = 1')->result_array();
                if ($_SESSION['phuyu_rubro'] == 4) {
                    $this->load->view('almacen/productos/nuevo_perfumeria', compact('unidades', 'atenciones'));
                } else {
                    $this->load->view('almacen/productos/nuevo', compact('unidades', 'atenciones', 'afectacionigv'));
                }
            } else {
                $this->load->view('phuyu/505');
            }
        } else {
            $this->load->view('phuyu/404');
        }
    }

    public function operacion()
    {
        if ($this->input->is_ajax_request() and isset($_SESSION['phuyu_codusuario'])) {
            $this->load->view('almacen/productos/operacion');
        } else {
            $this->load->view('phuyu/404');
        }
    }

    public function formato_cargarproductos()
    {
        if (!isset($_SESSION['phuyu_codusuario'])) {
            $this->load->view('phuyu/404');
            return;
        }

        $this->limpiar_salida_excel();

        $archivo = 'formato-carga-productos.xls';

        $this->descargar_xls($archivo, [
            [
                'nombre' => 'Productos',
                'filas' => [
                    $this->cabeceras_carga_productos(),
                    [
                        'PROD001',
                        'PRODUCTO DE EJEMPLO',
                        1,
                        'GENERAL',
                        'GENERAL',
                        'GENERICO',
                        'UNIDAD',
                        1,
                        10,
                        15,
                        14,
                        15,
                        13,
                        15,
                        '',
                        0,
                        1,
                        1,
                        isset($_SESSION['phuyu_afectacionigv']) ? (int) $_SESSION['phuyu_afectacionigv'] : 9,
                        0,
                        0,
                        '',
                        0
                    ],
                    [
                        'SERV001',
                        'SERVICIO DE EJEMPLO',
                        2,
                        'GENERAL',
                        'GENERAL',
                        'GENERICO',
                        'SERVICIO',
                        1,
                        0,
                        50,
                        50,
                        50,
                        50,
                        50,
                        '',
                        0,
                        0,
                        1,
                        isset($_SESSION['phuyu_afectacionigv']) ? (int) $_SESSION['phuyu_afectacionigv'] : 9,
                        0,
                        0,
                        '',
                        0
                    ]
                ]
            ],
            [
                'nombre' => 'Ayuda',
                'filas' => [
                    ['Campo', 'Descripcion'],
                    ['codigo', 'Opcional. Si existe, actualiza el producto con ese codigo; si esta vacio se genera automaticamente.'],
                    ['descripcion', 'Obligatorio. Nombre del producto o servicio.'],
                    ['tipo', '1 = bien/producto, 2 = servicio.'],
                    ['familia, linea, marca, unidad', 'Puede ingresar el codigo interno o el nombre. Si el nombre no existe, el sistema lo crea.'],
                    ['factor', 'Conversion de la unidad. Use 1 para la unidad base.'],
                    ['precios', 'Ingrese valores numericos sin simbolos o con S/. si lo necesita.'],
                    ['stock_inicial', 'Carga stock solo en el almacen actual y solo cuando la celda tiene valor.'],
                    ['control_stock, afecto_icbper, controlar_series', 'Use 1/SI para activar y 0/NO para desactivar.'],
                    ['cod_afectacion_igv_compra / venta', 'Puede ingresar codigo, descripcion u oficial de afectacion IGV.']
                ]
            ]
        ]);
    }

    public function formato_stockextra()
    {
        if (!isset($_SESSION['phuyu_codusuario'])) {
            $this->load->view('phuyu/404');
            return;
        }

        $this->limpiar_salida_excel();

        $archivo = 'formato-stock-extra.xls';

        $this->descargar_xls($archivo, [
            [
                'nombre' => 'Stock extra',
                'filas' => [
                    ['codigo', 'descripcion_referencia', 'unidad_referencia', 'nota', 'stock_extra'],
                    ['PROD001', 'PRODUCTO DE EJEMPLO', 'UNIDAD', '', 25],
                    ['PROD002', 'OTRO PRODUCTO', 'UNIDAD', '', 10]
                ]
            ],
            [
                'nombre' => 'Ayuda',
                'filas' => [
                    ['Campo', 'Descripcion'],
                    ['codigo', 'Obligatorio. Debe coincidir con almacen.productos.codigo.'],
                    ['stock_extra', 'Obligatorio. Es la cantidad que se mostrara como STOCK P en el buscador de productos de pedidos.'],
                    ['columnas B, C y D', 'Son solo referencia visual. El proceso actual solo lee la columna A y la columna E.'],
                    ['Importante', 'Antes de cargar el archivo, el sistema pone stockproveedor en 0 para todos los productos y luego aplica las cantidades del Excel.']
                ]
            ]
        ]);
    }

	    public function cargarproductos()
	    {
	        if (!$this->input->is_ajax_request() || !isset($_SESSION['phuyu_codusuario'])) {
	            $this->load->view('phuyu/404');
	            return;
	        }
	        $this->output->set_content_type('application/json', 'utf-8');

	        if (!isset($_FILES['archivo']) || $_FILES['archivo']['name'] == '') {
	            echo json_encode(['estado' => 0, 'mensaje' => 'Debe seleccionar el archivo de productos.']);
	            return;
	        }

	        $procesados = 0;
	        $errores = [];
	        $transaccion = false;

	        try {
	            $filas = $this->leer_archivo_carga_productos($_FILES['archivo']);

	            $this->db->trans_begin();
	            $transaccion = true;

            foreach ($filas as $row => $fila) {
                $resultado = $this->procesar_fila_carga_producto($fila, $row);

                if ($resultado['estado'] == 1) {
                    $procesados++;
                } elseif ($resultado['mensaje'] !== '') {
                    $errores[] = $resultado['mensaje'];
                }
            }

            if ($procesados == 0 || $this->db->trans_status() === false) {
                $this->db->trans_rollback();
                $mensaje = $procesados == 0 ? 'No se encontro ninguna fila valida para cargar.' : 'No se pudo guardar la carga de productos.';
                if (count($errores) > 0) {
                    $mensaje .= ' ' . implode(' ', array_slice($errores, 0, 5));
                }
                echo json_encode(['estado' => 0, 'mensaje' => $mensaje]);
                return;
            }

            $this->db->trans_commit();

            $mensaje = 'Productos procesados: ' . $procesados . '.';
            if (count($errores) > 0) {
                $mensaje .= ' Filas omitidas: ' . count($errores) . '. ' . implode(' ', array_slice($errores, 0, 3));
            }

            echo json_encode(['estado' => 1, 'mensaje' => $mensaje, 'procesados' => $procesados, 'errores' => $errores]);
	        } catch (Exception $e) {
	            if ($transaccion) {
	                $this->db->trans_rollback();
	            }
	            echo json_encode(['estado' => 0, 'mensaje' => 'No se pudo leer el archivo. Verifique que sea un Excel o CSV valido.']);
	        }
	    }

    function ver($codregistro)
    {
        if ($this->input->is_ajax_request()) {
            if (isset($_SESSION['phuyu_codusuario'])) {
                $info = $this->db->query("select almacen.productos.*, almacen.marcas.descripcion as marca, almacen.familias.descripcion as familia, almacen.lineas.descripcion as linea,(CASE WHEN tipo = 1 THEN 'BIEN' ELSE 'SERVICIO' END) AS tipo from almacen.productos inner join almacen.marcas on(almacen.productos.codmarca=almacen.marcas.codmarca) inner join almacen.familias on(almacen.productos.codfamilia=almacen.familias.codfamilia) inner join almacen.lineas on(almacen.productos.codlinea=almacen.lineas.codlinea) where codproducto=" . $codregistro)->result_array();

                foreach ($info as $key => $value) {
                    $afectacion = $this->db->query('select *from almacen.productoubicacion where codproducto=' . $value['codproducto'] . ' AND codalmacen=' . $_SESSION['phuyu_codalmacen'] . ' AND factor=1 AND estado = 1')->result_array();

                    $info[$key]['comisionvendedor'] = $afectacion[0]['comisionvendedor'];
                }

                $unidades = $this->db->query('select almacen.productounidades.*, almacen.unidades.descripcion as unidad from almacen.productounidades inner join almacen.unidades on(almacen.productounidades.codunidad=almacen.unidades.codunidad) where almacen.productounidades.codproducto=' . $codregistro . ' and almacen.productounidades.estado=1 order by almacen.productounidades.factor')->result_array();
                foreach ($unidades as $key => $value) {
                    $stock = $this->db->query('select stockactual from almacen.productoubicacion where codproducto=' . $value['codproducto'] . ' and codunidad=' . $value['codunidad'] . ' and codalmacen=' . $_SESSION['phuyu_codalmacen'] . ' and estado=1')->result_array();
                    $unidades[$key]['stock'] = 0;
                    if (count($stock) > 0) {
                        $unidades[$key]['stock'] = round($stock[0]['stockactual'], 2);
                    }
                }
                $this->load->view('almacen/productos/ver', compact('info', 'unidades'));
            } else {
                $this->load->view('inicio/505');
            }
        } else {
            $this->load->view('inicio/404');
        }
    }

    function stock_almacenes($codproducto)
    {
        if ($this->input->is_ajax_request()) {
            $info = $this->db
                ->query(
                    "SELECT pun.codalmacen, p.codproducto, p.codigo,p.codfamilia, p.codlinea, p.codmarca, ma.descripcion AS marca, p.descripcion, pun.unidades, p.afectoicbper, p.controlstock, p.afectoigvcompra, pun.almacen, p.foto, p.calcular, p.paraventa, p.codmodelo, p.codcolor, p.codtalla
    FROM almacen.productos p
  JOIN almacen.v_productounidades pun ON (p.codproducto = pun.codproducto AND p.estado = 1 )
  JOIN almacen.marcas ma ON (p.codmarca = ma.codmarca) WHERE pun.codalmacen <> " .
                        $_SESSION['phuyu_codalmacen'] .
                        ' AND p.codproducto=' .
                        $codproducto,
                )
                ->result_array();

            echo json_encode(['almacenes' => $info]);
        }
    }

    function guardar()
    {
        if ($this->input->is_ajax_request()) {
            $this->request = json_decode(file_get_contents('php://input'));

            $campos = ['codfamilia', 'codlinea', 'codmarca', 'codempresa', 'codigo', 'descripcion', 'afectoicbper', 'codatencion', 'paraventa', 'calcular', 'controlstock', 'caracteristicas', 'tipo', 'controlarseries'];
            $valores = [
                (int) $this->request->campos->codfamilia,
                (int) $this->request->campos->codlinea,
                (int) $this->request->campos->codmarca,
                (int) $_SESSION['phuyu_codempresa'],
                $this->request->campos->codigo,
                $this->request->campos->descripcion,
                (int) $this->request->campos->afectoicbper,
                (int) $this->request->campos->codatencion,
                (int) 1,
                (int) $this->request->campos->calcular,
                (int) $this->request->campos->controlstock,
                $this->request->campos->caracteristicas,
                (int) $this->request->campos->tipo,
                (int) $this->request->campos->controlarseries, //controlarseries
            ];

            $campos_1 = ['codproducto', 'codunidad', 'codsucursal', 'factor', 'preciocompra', 'preciocosto', 'pventapublico', 'pventamin', 'pventacredito', 'pventaxmayor', 'pventaadicional', 'codigobarra', 'estado'];

            $this->db->trans_begin();

            if ($this->request->campos->codregistro == '') {
                $codproducto = $this->phuyu_model->phuyu_guardar('almacen.productos', $campos, $valores, 'true');

                if ($this->request->campos->codigo == '') {
                    $data = ['codigo' => '000' . $codproducto];
                    $this->db->where('codproducto', $codproducto);
                    $estado = $this->db->update('almacen.productos', $data);
                }

                if (isset($this->request->unidades)) {
                    
                    foreach ($this->request->unidades as $key => $value) {
                        $valores_1 = [(int) $codproducto, (int) $this->request->unidades[$key]->codunidad, (int) $_SESSION['phuyu_codsucursal'], $this->request->unidades[$key]->factor, (float) $this->request->unidades[$key]->preciocompra, (float) $this->request->unidades[$key]->preciocompra, (float) $this->request->unidades[$key]->pventapublico, (float) $this->request->unidades[$key]->pventamin, (float) $this->request->unidades[$key]->pventacredito, (float) $this->request->unidades[$key]->pventaxmayor, (float) $this->request->unidades[$key]->pventaadicional, $this->request->unidades[$key]->codigobarra, 1];
                        $estado = $this->phuyu_model->phuyu_guardar('almacen.productounidades', $campos_1, $valores_1);

                        $almacenes = $this->db->query('select *from almacen.almacenes where estado = 1')->result_array();

                        foreach ($almacenes as $k => $val) {
                            if ($_SESSION['phuyu_codalmacen'] != (int) $val['codalmacen']) {
                                $this->request->campos->codafectacionigvcompra = $val['codafectacionigv'];
                                $this->request->campos->codafectacionigvventa = $val['codafectacionigv'];
                            }
                            $campos = ['codalmacen', 'codproducto', 'codunidad', 'codsucursal', 'factor', 'preciocompra', 'preciocosto', 'pventapublico', 'pventamin', 'pventacredito', 'pventaxmayor', 'pventaadicional', 'codigobarra', 'estado', 'codafectacionigvcompra', 'codafectacionigvventa', 'comisionvendedor'];
                            $valores = [
                                (int) $val['codalmacen'],
                                (int) $codproducto,
                                (int) $this->request->unidades[$key]->codunidad,
                                (int) $val['codsucursal'],
                                $this->request->unidades[$key]->factor,
                                (float) $this->request->unidades[$key]->preciocompra,
                                (float) $this->request->unidades[$key]->preciocompra,
                                (float) $this->request->unidades[$key]->pventapublico,
                                (float) $this->request->unidades[$key]->pventamin,
                                (float) $this->request->unidades[$key]->pventacredito,
                                (float) $this->request->unidades[$key]->pventaxmayor,
                                (float) $this->request->unidades[$key]->pventaadicional,
                                $this->request->unidades[$key]->codigobarra,
                                1,
                                (int) $this->request->campos->codafectacionigvcompra,
                                (int) $this->request->campos->codafectacionigvventa,
                                (int) $this->request->campos->comisionvendedor
                            ];
                            $estado = $this->phuyu_model->phuyu_guardar('almacen.productoubicacion', $campos, $valores);
                        }
                    }
                }
            } else {
                $codproducto = $this->request->campos->codregistro;
                $estado = $this->phuyu_model->phuyu_editar('almacen.productos', $campos, $valores, 'codproducto', $codproducto);

                $campos_2 = ['estado'];
                $valores_2 = [0];
                $f = ['codproducto'];
                $v = [$codproducto];
                $f2 = ['codproducto', 'codalmacen'];
                $v2 = [$codproducto, (int) $_SESSION['phuyu_codalmacen']];
                $estado = $this->phuyu_model->phuyu_editar_1('almacen.productounidades', $campos_2, $valores_2, $f, $v);
                $estado = $this->phuyu_model->phuyu_editar_1('almacen.productoubicacion', $campos_2, $valores_2, $f2, $v2);

                if (isset($this->request->unidades)) {
                    foreach ($this->request->unidades as $key => $value) {
                        $valores_1 = [(int) $codproducto, (int) $this->request->unidades[$key]->codunidad, (int) $_SESSION['phuyu_codsucursal'], $this->request->unidades[$key]->factor, (float) $this->request->unidades[$key]->preciocompra, (float) $this->request->unidades[$key]->preciocompra, (float) $this->request->unidades[$key]->pventapublico, (float) $this->request->unidades[$key]->pventamin, (float) $this->request->unidades[$key]->pventacredito, (float) $this->request->unidades[$key]->pventaxmayor, (float) $this->request->unidades[$key]->pventaadicional, $this->request->unidades[$key]->codigobarra, 1];

                        $existe = $this->db->query('select *from almacen.productounidades where codproducto=' . $codproducto . ' and codunidad=' . $this->request->unidades[$key]->codunidad)->result_array();

                        if (count($existe) == 0) {
                            $estado = $this->phuyu_model->phuyu_guardar('almacen.productounidades', $campos_1, $valores_1);
                        } else {
                            $f = ['codproducto', 'codunidad'];
                            $v = [(int) $codproducto, $this->request->unidades[$key]->codunidad];
                            $estado = $this->phuyu_model->phuyu_editar_1('almacen.productounidades', $campos_1, $valores_1, $f, $v);
                        }

                        $existe_ubi = $this->db->query(
                            'select *from almacen.productoubicacion 
                        where codproducto=' . $codproducto . ' and codunidad=' . $this->request->unidades[$key]->codunidad
                        )->result_array();

                        if (count($existe_ubi) == 0) {
                            $almacenes = $this->db->query('select * from almacen.almacenes where estado = 1')->result_array();

                            foreach ($almacenes as $k => $val) {
                                if ($_SESSION['phuyu_codalmacen'] != (int) $val['codalmacen']) {
                                    $this->request->campos->codafectacionigvcompra = $val['codafectacionigv'];
                                    $this->request->campos->codafectacionigvventa = $val['codafectacionigv'];
                                }
                                $campos = ['codalmacen', 'codproducto', 'codunidad', 'codsucursal', 'factor', 'preciocompra', 'preciocosto', 'pventapublico', 'pventamin', 'pventacredito', 'pventaxmayor', 'pventaadicional', 'codigobarra', 'estado', 'codafectacionigvcompra', 'codafectacionigvventa', 'comisionvendedor'];
                                $valores = [
                                    (int) $val['codalmacen'],
                                    (int) $codproducto,
                                    (int) $this->request->unidades[$key]->codunidad,
                                    (int) $val['codsucursal'],
                                    $this->request->unidades[$key]->factor,
                                    (float) $this->request->unidades[$key]->preciocompra,
                                    (float) $this->request->unidades[$key]->preciocompra,
                                    (float) $this->request->unidades[$key]->pventapublico,
                                    (float) $this->request->unidades[$key]->pventamin,
                                    (float) $this->request->unidades[$key]->pventacredito,
                                    (float) $this->request->unidades[$key]->pventaxmayor,
                                    (float) $this->request->unidades[$key]->pventaadicional,
                                    $this->request->unidades[$key]->codigobarra,
                                    1,
                                    (int) $this->request->campos->codafectacionigvcompra,
                                    (int) $this->request->campos->codafectacionigvventa,
                                    (int) $this->request->campos->comisionvendedor
                                ];
                                $estado = $this->phuyu_model->phuyu_guardar('almacen.productoubicacion', $campos, $valores);
                            }
                        } else {
                            $campos = ['estado', 'factor', 'preciocompra', 'preciocosto', 'pventapublico', 'pventamin', 'pventacredito', 'pventaxmayor', 'pventaadicional', 'codigobarra', 'codafectacionigvcompra', 'codafectacionigvventa', 'comisionvendedor'];
                            $valores = [1, $this->request->unidades[$key]->factor, (float) $this->request->unidades[$key]->preciocompra, (float) $this->request->unidades[$key]->preciocompra, (float) $this->request->unidades[$key]->pventapublico, (float) $this->request->unidades[$key]->pventamin, (float) $this->request->unidades[$key]->pventacredito, (float) $this->request->unidades[$key]->pventaxmayor, (float) $this->request->unidades[$key]->pventaadicional, $this->request->unidades[$key]->codigobarra, (int) $this->request->campos->codafectacionigvcompra, (int) $this->request->campos->codafectacionigvventa, (int) $this->request->campos->comisionvendedor];
                            $f = ['codproducto', 'codunidad', 'codalmacen'];
                            $v = [(int) $codproducto, $this->request->unidades[$key]->codunidad, (int) $_SESSION['phuyu_codalmacen']];
                            $estado = $this->phuyu_model->phuyu_editar_1('almacen.productoubicacion', $campos, $valores, $f, $v);
                        }

                        if ($this->request->unidades[$key]->factor > 1) {
                            $almacenes = $this->db->query('select *from almacen.almacenes where estado = 1')->result_array();

                            foreach ($almacenes as $k => $val) {
                                $stockconvertidobase = $this->db->query('select *from almacen.productoubicacion where codproducto=' . $codproducto . ' AND factor=1 AND codalmacen=' . $val['codalmacen'])->result_array();

                                $stockc = (float) $stockconvertidobase[0]['stockactualconvertido'] / (float) $this->request->unidades[$key]->factor;

                                $data = [
                                    'stockactualconvertido' => (float) round($stockc, 3),
                                ];

                                $this->db->where('codalmacen', $val['codalmacen']);
                                $this->db->where('codproducto', $codproducto);
                                $this->db->where('codunidad', (int) $this->request->unidades[$key]->codunidad);
                                $estado = $this->db->update('almacen.productoubicacion', $data);
                            }
                        }
                    }
                }
            }

            if ($this->db->trans_status() === false) {
                $this->db->trans_rollback();
                $estado = 0;
            } else {
                $this->db->trans_commit();
                $estado = $codproducto;
            }

            echo $estado;
        } else {
            $this->load->view('phuyu/404');
        }
    }

    function guardar_foto()
    {
        if ($this->input->is_ajax_request()) {
            $estado = 1;
            echo $this->input->post('foto');
            exit();
            if ($_FILES['foto']['name'] != '') {
                $file = $this->input->post('codproducto') . '_' . substr($_FILES['foto']['name'], -5);
                move_uploaded_file($_FILES['foto']['tmp_name'], './public/img/productos/' . $file);
                chmod('./public/img/productos/' . $file, 0777);

                $data = ['foto' => $file];
                $this->db->where('codproducto', $this->input->post('codproducto'));
                $estado = $this->db->update('almacen.productos', $data);
            }
            echo $estado;
        } else {
            $this->load->view('phuyu/404');
        }
    }

    function editar_ORIGINAL()
    {
        if ($this->input->is_ajax_request()) {
            $this->request = json_decode(file_get_contents('php://input'));
            $info = $this->db
                ->query(
                    "
   select codproducto as codregistro, *
   from almacen.productos
   where codproducto=" . $this->request->codregistro,
                )
                ->result_array();

            foreach ($info as $key => $value) {
                $afectacion = $this->db
                    ->query(
                        "
    select *from almacen.productoubicacion
    where codproducto=" .
                            $value['codproducto'] .
                            ' AND codalmacen=' .
                            $_SESSION['phuyu_codalmacen'] .
                            ' AND factor=1 AND estado = 1',
                    )
                    ->result_array();

                if ($afectacion[0]['codafectacionigvcompra'] == '' || $afectacion[0]['codafectacionigvcompra'] == null || $afectacion[0]['codafectacionigvcompra'] == 0) {
                    $afectacion[0]['codafectacionigvcompra'] = $_SESSION['phuyu_afectacionigv'];
                }
                if ($afectacion[0]['codafectacionigvventa'] == '' || $afectacion[0]['codafectacionigvventa'] == null || $afectacion[0]['codafectacionigvventa'] == 0) {
                    $afectacion[0]['codafectacionigvventa'] = $_SESSION['phuyu_afectacionigv'];
                }
                $info[$key]['codafectacionigvcompra'] = $afectacion[0]['codafectacionigvcompra'];
                $info[$key]['codafectacionigvventa'] = $afectacion[0]['codafectacionigvventa'];
                $info[$key]['comisionvendedor'] = $afectacion[0]['comisionvendedor'];
            }
            echo json_encode($info);
        } else {
            $this->load->view('phuyu/404');
        }
    }
    function editar()
    {
        if ($this->input->is_ajax_request()) {
            $this->request = json_decode(file_get_contents('php://input'));
            $info = $this->db
                ->query(
                    "
            SELECT codproducto as codregistro, *
            FROM almacen.productos
            WHERE codproducto = " . $this->request->codregistro,
                )
                ->result_array();

            foreach ($info as $key => $value) {
                $afectacion = $this->db
                    ->query(
                        "
                SELECT * FROM almacen.productoubicacion
                WHERE codproducto = " .
                            $value['codproducto'] .
                            "
                AND codalmacen = " .
                            $_SESSION['phuyu_codalmacen'] .
                            "
                AND factor = 1
                AND estado = 1",
                    )
                    ->result_array();

                // VALIDAR SI HAY REGISTROS EN AFECTACION
                if (!empty($afectacion)) {
                    $codAfectacionCompra = $afectacion[0]['codafectacionigvcompra'];
                    $codAfectacionVenta = $afectacion[0]['codafectacionigvventa'];
                    $comisionVendedor = $afectacion[0]['comisionvendedor'];
                } else {
                    // VALORES POR DEFECTO SI NO HAY REGISTROS
                    $codAfectacionCompra = $_SESSION['phuyu_afectacionigv'];
                    $codAfectacionVenta = $_SESSION['phuyu_afectacionigv'];
                    $comisionVendedor = 0; // O el valor por defecto que necesites
                }

                // ASIGNAR VALORES VALIDADOS
                $info[$key]['codafectacionigvcompra'] = !empty($codAfectacionCompra) && $codAfectacionCompra != 0 ? $codAfectacionCompra : $_SESSION['phuyu_afectacionigv'];

                $info[$key]['codafectacionigvventa'] = !empty($codAfectacionVenta) && $codAfectacionVenta != 0 ? $codAfectacionVenta : $_SESSION['phuyu_afectacionigv'];

                $info[$key]['comisionvendedor'] = $comisionVendedor;
            }
            echo json_encode($info);
        } else {
            $this->load->view('phuyu/404');
        }
    }

    function unidades()
    {
        if ($this->input->is_ajax_request()) {
            $this->request = json_decode(file_get_contents('php://input'));
            $unidades = $this->db->query('select pu.*,u.descripcion as unidad from almacen.productoubicacion as pu inner join almacen.unidades as u on(pu.codunidad=u.codunidad) where pu.codproducto=' . $this->request->codregistro . ' AND pu.codalmacen = ' . $_SESSION['phuyu_codalmacen'] . ' and pu.estado=1 order by pu.factor asc')->result_array();
            $campos = $this->db->query('select codfamilia,codlinea,codmarca from almacen.productos where codproducto=' . $this->request->codregistro)->result_array();

            $data['unidades'] = $unidades;
            $data['campos'] = $campos;
            echo json_encode($data);
        } else {
            $this->load->view('phuyu/404');
        }
    }

    function unidades_venta($codproducto, $factor)
    {
        if ($this->input->is_ajax_request()) {
            $unidades = $this->db->query('select u.codunidad,u.descripcion,pu.factor from almacen.productounidades as pu inner join almacen.unidades as u on(pu.codunidad=u.codunidad) where pu.codproducto=' . $codproducto . ' and pu.factor<>' . $factor . ' order by pu.factor asc')->result_array();
            echo json_encode($unidades);
        }
    }

    function eliminar()
    {
        if ($this->input->is_ajax_request()) {
            $this->request = json_decode(file_get_contents('php://input'));
            $estado = $this->phuyu_model->phuyu_eliminar('almacen.productos', 'codproducto', $this->request->codregistro);
            echo $estado;
        } else {
            $this->load->view('phuyu/404');
        }
    }

    // BUSCAR PRODUCTOS EN COMPRAS, EN VENTAS, EN INGRESOS Y EGRESOS ALMACEN //

    function buscar($operacion)
    {
        if ($this->input->is_ajax_request()) {
            if (isset($_SESSION['phuyu_codusuario'])) {
                $this->load->view('almacen/productos/buscar', compact('operacion'));
            } else {
                $this->load->view('phuyu/505');
            }
        } else {
            $this->load->view('phuyu/404');
        }
    }

    function buscar_codigobarra($codigobarra)
    {
        if ($this->input->is_ajax_request()) {
            $info = $this->db
                ->query(
                    "select p.codproducto,p.descripcion,p.caracteristicas, p.afectoicbper,p.controlstock, p.afectoigvcompra, p.afectoigvventa, p.codigo,p.calcular,p.foto,p.controlarseries,u.codunidad,u.descripcion as unidad,round(pu.stockactual,3) as stock, m.descripcion as marca, puv.factor, puv.factor as factormaximo, round(puv.pventapublico,2) as precio, round(puv.pventamin,2) as preciomin, round(puv.pventacredito,2) as preciocredito, round(puv.pventaxmayor,2) as preciomayor, round(puv.preciocosto,2) as preciocosto, round(puv.pventaadicional,2) as precioadicional,
                    COALESCE(
                        (SELECT vpun.unidades
                        FROM almacen.v_productounidades vpun
                        WHERE vpun.codproducto = p.codproducto
                        AND vpun.codalmacen = pu.codalmacen
                        LIMIT 1),
                        ''
                    ) AS unidades,
                    COALESCE(
                        (SELECT jsonb_agg(jsonb_build_object(
                            'serie_codigo', s.serie_codigo,
                            'estado', s.estado,
                            'fecha_ingreso', s.fecha_ingreso,
                            'id_serie', s.id_serie,
                            'codproducto', p.codproducto
                        ))
                        FROM almacen.series s
                        WHERE s.codproducto = p.codproducto
                        AND s.estado = 'EN_ALMACEN'
                        AND s.codalmacen = " . $_SESSION['phuyu_codalmacen'] . "),
                        '[]'::jsonb
                    ) AS series
                    from almacen.productos as p inner join almacen.productoubicacion as pu on(p.codproducto=pu.codproducto) inner join almacen.unidades as u on(u.codunidad=pu.codunidad) inner join almacen.marcas as m on(p.codmarca=m.codmarca) inner join almacen.productounidades as puv on(pu.codproducto=puv.codproducto and pu.codunidad=puv.codunidad) where puv.codigobarra='" .
                        $codigobarra .
                        "' and p.estado=1 and pu.estado=1 and pu.codalmacen=" .
                        $_SESSION['phuyu_codalmacen'],
                )
                ->result_array();
            foreach ($info as &$producto) {
                if (!empty($producto['series']) && is_string($producto['series'])) {
                    $producto['series'] = json_decode($producto['series'], true);
                } else {
                    $producto['series'] = [];
                }
            }
            $data = [];
            $precio = 0;
            if (count($info) > 0) {
                $precio = $info[0]['precio'];
            }
            $data['cantidad'] = count($info);
            $data['info'] = $info;
            $data['precio'] = (float) $precio;

            echo json_encode($data);
        }
    }

    function buscar_salidas()
    {
        if ($this->input->is_ajax_request()) {
            $this->request = json_decode(file_get_contents('php://input'));
            $limit = 10;
            $offset = $this->request->pagina * $limit - $limit;
            $condicionBusqueda = $this->condicion_busqueda_productos($this->request->buscar, 'p', 'ma');

            // $lista = $this->db->query(
            //         "SELECT pun.codalmacen, p.codproducto, p.codigo,p.codfamilia, p.codlinea, p.codmarca, ma.descripcion AS marca,
            // 		 p.descripcion, pun.unidades, p.afectoicbper, p.controlstock, p.afectoigvcompra, p.afectoigvventa, p.foto, p.calcular,
            // 		  p.paraventa, p.codmodelo, p.codcolor, p.codtalla, p.tipo,p.controlarseries
            // 		FROM almacen.productos p
            // 		JOIN almacen.v_productounidades pun ON (p.codproducto = pun.codproducto AND p.estado = 1 )
            // 		JOIN almacen.lineasxsucursales ls ON (pun.codsucursal = ls.codsucursal 
            // 		AND p.codlinea = ls.codlinea AND ls.codsucursal = " .	$_SESSION['phuyu_codsucursal'] . " )
            // 		JOIN almacen.marcas ma ON (p.codmarca = ma.codmarca)
            // 		where (REPLACE(UPPER(p.descripcion),' ','%') 
            // 		like REPLACE (UPPER('%" .$this->request->buscar ."%'),' ','%') or UPPER(p.codigo) 
            // 		like UPPER('%" .$this->request->buscar ."%') or UPPER(ma.descripcion) 
            // 		like UPPER('%" .$this->request->buscar ."%') ) 
            // 		and p.estado=1 and pun.codalmacen=" .$_SESSION['phuyu_codalmacen'] .' 
            // 		order by p.codproducto desc offset ' .$offset .' limit ' .$limit,)
            //     ->result_array();

            $lista = $this->db->query(
                "
                    SELECT 
                        pun.codalmacen, 
                        p.codproducto, 
                        p.codigo,
                        p.codfamilia, 
                        p.codlinea, 
                        p.codmarca, 
                        ma.descripcion AS marca,
                        p.descripcion, 
                        pun.unidades, 
                        p.afectoicbper, 
                        p.controlstock, 
                        p.afectoigvcompra, 
                        p.afectoigvventa, 
                        p.foto, 
                        p.calcular,
                        p.paraventa, 
                        p.codmodelo, 
                        p.codcolor, 
                        p.codtalla, 
                        p.tipo,
                        p.controlarseries,
                           -- Cambiar json_agg por jsonb_agg para que venga como objeto
                            COALESCE(
                                (SELECT jsonb_agg(jsonb_build_object(
                                    'serie_codigo', s.serie_codigo,
                                    'estado', s.estado,
                                    'fecha_ingreso', s.fecha_ingreso,
                                    'id_serie', s.id_serie,
                                    'codproducto ', p.codproducto 
                                ))
                                FROM almacen.series s
                                WHERE s.codproducto = p.codproducto 
                                AND s.estado = 'EN_ALMACEN'
                                AND s.codalmacen = " . $_SESSION['phuyu_codalmacen'] . "),
                                '[]'::jsonb
                            ) AS series
                    FROM almacen.productos p
                    JOIN almacen.v_productounidades pun ON (p.codproducto = pun.codproducto AND p.estado = 1)
                    JOIN almacen.lineasxsucursales ls ON (pun.codsucursal = ls.codsucursal 
                        AND p.codlinea = ls.codlinea AND ls.codsucursal = " . $_SESSION['phuyu_codsucursal'] . ")
                    JOIN almacen.marcas ma ON (p.codmarca = ma.codmarca)
                    WHERE " . $condicionBusqueda . "
                    AND p.estado = 1 
                    AND pun.codalmacen = " . $_SESSION['phuyu_codalmacen'] . "
                    ORDER BY p.codproducto DESC 
                    OFFSET " . $offset . " LIMIT " . $limit
            )->result_array();

            foreach ($lista as $key => $value) {
                $factormaximo = $this->db->query('
                select max(factor) as factor 
                from almacen.productounidades 
                where codproducto=' . $value['codproducto'] . ' and estado=1')->result_array();

                if ($value['unidades'] != '' || $value['unidades'] != null) {
                    $unidades = explode(';', $value['unidades']);
                    foreach ($unidades as $k => $v) {
                        $factores = explode('|', $v);
                        if ($factores[8] == 1) {
                            $lista[$key]['factormaximo'] = $factormaximo[0]['factor'];
                            $lista[$key]['factor'] = $factores[8];
                            $lista[$key]['precio'] = $factores[5];
                            $lista[$key]['precioventa'] = $factores[5];
                            $lista[$key]['preciomin'] = round((float) $factores[6], 2);
                            $lista[$key]['preciocredito'] = round((float) $factores[7], 2);
                            $lista[$key]['preciomayor'] = round((float) $factores[10], 2);
                            $lista[$key]['stock'] = $factores[3];
                            $lista[$key]['stockproveedor'] = $factores[4];
                            $lista[$key]['unidad'] = $factores[1];
                        }
                    }
                }
            }

            $total = $this->db
                ->query(
                    "select count(*) as total FROM almacen.productos p
                    JOIN almacen.v_productounidades pun ON (p.codproducto = pun.codproducto AND p.estado = 1 )
                    JOIN almacen.lineasxsucursales ls ON (pun.codsucursal = ls.codsucursal AND p.codlinea = ls.codlinea 
                    AND ls.codsucursal = " . $_SESSION['phuyu_codsucursal'] . " )
                    JOIN almacen.marcas ma ON (p.codmarca = ma.codmarca)
                    where " . $condicionBusqueda . " and p.estado=1 
                    and pun.codalmacen=" . $_SESSION['phuyu_codalmacen'],
                )
                ->result_array();

            $paginas = floor($total[0]['total'] / $limit);
            if ($total[0]['total'] % $limit != 0) {
                $paginas = $paginas + 1;
            }

            $paginacion = [];
            $paginacion['total'] = $total[0]['total'];
            $paginacion['actual'] = $this->request->pagina;
            $paginacion['ultima'] = $paginas;
            $paginacion['desde'] = $offset;
            $paginacion['hasta'] = $offset + $limit;
            foreach ($lista as &$producto) {
                if (!empty($producto['series']) && is_string($producto['series'])) {
                    $producto['series'] = json_decode($producto['series'], true);
                } else {
                    $producto['series'] = [];
                }
            }


            echo json_encode(['lista' => $lista, 'paginacion' => $paginacion]);
        } else {
            $this->load->view('phuyu/404');
        }
    }

    function informacion_item()
    {
        if ($this->input->is_ajax_request()) {
            $this->request = json_decode(file_get_contents('php://input'));

            $lista = $this->db
                ->query(
                    "select round(pu.stockactualconvertido,2) as stock,pu.codunidad
    from almacen.productoubicacion as pu WHERE codproducto=" .
                        $this->request->codproducto .
                        ' and codunidad=' .
                        $this->request->codunidad .
                        ' AND codalmacen=' .
                        $_SESSION['phuyu_codalmacen'],
                )
                ->result_array();

            foreach ($lista as $key => $value) {
                $factormaximo = $this->db->query('select max(factor) as factor from almacen.productounidades where codproducto=' . $this->request->codproducto . ' and estado=1')->result_array();

                $precio = $this->db->query('select factor,pventapublico,pventamin,pventacredito,pventaxmayor, preciocosto,pventaadicional from almacen.productounidades where codproducto=' . $this->request->codproducto . ' and codunidad=' . $this->request->codunidad . ' and estado=1')->result_array();
                if (count($precio) == 0) {
                    $lista[$key]['factor'] = 0;
                    $lista[$key]['factormaximo'] = 0;
                    $lista[$key]['precio'] = 0.0;
                    $lista[$key]['preciomin'] = 0.0;
                    $lista[$key]['preciocredito'] = 0.0;
                    $lista[$key]['preciomayor'] = 0.0;
                    $lista[$key]['preciocosto'] = 0.0;
                    $lista[$key]['precioadicional'] = 0.0;
                } else {
                    $lista[$key]['factor'] = $precio[0]['factor'];
                    $lista[$key]['factormaximo'] = $factormaximo[0]['factor'];
                    if (isset($this->request->salida)) {
                        $preciod = $precio[0]['preciocosto'];
                    } else {
                        $preciod = $precio[0]['pventapublico'];
                    }
                    $lista[$key]['precio'] = round($preciod, 2);
                    $lista[$key]['precioventa'] = round($preciod, 2);
                    $lista[$key]['preciomin'] = round($precio[0]['pventamin'], 2);
                    $lista[$key]['preciocredito'] = round($precio[0]['pventacredito'], 2);
                    $lista[$key]['preciomayor'] = round($precio[0]['pventaxmayor'], 2);
                    $lista[$key]['preciocosto'] = round($precio[0]['preciocosto'], 2);
                    $lista[$key]['precioadicional'] = round($precio[0]['pventaadicional'], 2);
                }
            }

            echo json_encode($lista);
        }
    }

    function buscar_ingresos()
    {
        if ($this->input->is_ajax_request()) {
            $this->request = json_decode(file_get_contents('php://input'));
            $limit = 10;
            $offset = $this->request->pagina * $limit - $limit;
            $condicionBusqueda = $this->condicion_busqueda_productos($this->request->buscar, 'p', 'ma');

            $lista = $this->db
                ->query(
                    "
   SELECT pun.codalmacen, p.codproducto, p.codigo,p.codfamilia, p.codlinea, p.codmarca, ma.descripcion AS marca,
   p.descripcion, pun.unidades, p.afectoicbper, p.controlstock, p.afectoigvcompra, p.afectoigvventa,
   p.foto, p.calcular, p.paraventa, p.codmodelo, p.codcolor, p.codtalla ,p.controlarseries
   FROM almacen.productos p
   JOIN almacen.v_productounidades pun ON (p.codproducto = pun.codproducto AND p.estado = 1 )
   JOIN almacen.lineasxsucursales ls ON (pun.codsucursal = ls.codsucursal AND p.codlinea = ls.codlinea AND ls.codsucursal = " .
                        $_SESSION['phuyu_codsucursal'] .
                        " )
   JOIN almacen.marcas ma ON (p.codmarca = ma.codmarca)
   where " . $condicionBusqueda . " and p.estado=1 and pun.codalmacen=" .
                        $_SESSION['phuyu_codalmacen'] .
                        ' order by p.codproducto desc offset ' .
                        $offset .
                        ' limit ' .
                        $limit,
                )
                ->result_array();

            foreach ($lista as $key => $value) {
                $factormaximo = $this->db->query('select max(factor) as factor from almacen.productounidades where codproducto=' . $value['codproducto'] . ' and estado=1')->result_array();

                if ($value['unidades'] != '') {
                    $unidades = explode(';', $value['unidades']);
                    foreach ($unidades as $k => $v) {
                        $factores = explode('|', $v);
                        if ($factores[8] == 1) {
                            $lista[$key]['factormaximo'] = $factormaximo[0]['factor'];
                            $lista[$key]['factor'] = $factores[8];
                            $lista[$key]['precio'] = $factores[9];
                            $lista[$key]['stock'] = $factores[3];
                            $lista[$key]['preciomayor'] = round((float) $factores[10], 2);
                        }
                    }
                }
            }

            $total = $this->db
                ->query(
                    "select count(*) as total FROM almacen.productos p
   JOIN almacen.v_productounidades pun ON (p.codproducto = pun.codproducto AND p.estado = 1 )
   JOIN almacen.lineasxsucursales ls ON (pun.codsucursal = ls.codsucursal AND p.codlinea = ls.codlinea AND ls.codsucursal = " .
                        $_SESSION['phuyu_codsucursal'] .
                        " )
   JOIN almacen.marcas ma ON (p.codmarca = ma.codmarca)
   where " . $condicionBusqueda . " and p.estado=1 and pun.codalmacen=" .
                        $_SESSION['phuyu_codalmacen'],
                )
                ->result_array();

            $paginas = floor($total[0]['total'] / $limit);
            if ($total[0]['total'] % $limit != 0) {
                $paginas = $paginas + 1;
            }

            $paginacion = [];
            $paginacion['total'] = $total[0]['total'];
            $paginacion['actual'] = $this->request->pagina;
            $paginacion['ultima'] = $paginas;
            $paginacion['desde'] = $offset;
            $paginacion['hasta'] = $offset + $limit;

            echo json_encode(['lista' => $lista, 'paginacion' => $paginacion]);
        } else {
            $this->load->view('phuyu/404');
        }
    }

    function restobar($codlinea)
    {
        if ($this->input->is_ajax_request()) {
            if (isset($_SESSION['phuyu_codusuario'])) {
                $this->load->view('almacen/productos/restaurant', compact('codlinea'));
            }
        }
    }

    function buscando_restobar_original()
    {
        if ($this->input->is_ajax_request()) {
            $this->request = json_decode(file_get_contents('php://input'));

            if ($this->request->codlinea == 0) {
                $linea = '';
            } else {
                $linea = 'p.codlinea=' . $this->request->codlinea . ' and ';
            }

            $lista = $this->db
                ->query(
                    "select p.codproducto,p.codigo,p.descripcion,p.controlstock,p.afectoigvcompra,p.afectoigvventa,
   p.codigo, p.calcular, p.foto,
   u.codunidad,u.descripcion as unidad,round(pu.stockactualconvertido,2) as stock,
    (select coalesce(sum(pd.cantidad),0)
    from kardex.pedidos as pedi inner join kardex.pedidosdetalle as pd on(pedi.codpedido=pd.codpedido)
    where pedi.estado=1 and pu.codproducto=pd.codproducto and pu.codunidad=pd.codunidad) as comprometido, m.descripcion as marca,l.background,l.color
    from almacen.productos as p inner join almacen.productoubicacion as pu on(p.codproducto=pu.codproducto)
    inner join almacen.unidades as u on(u.codunidad=pu.codunidad) inner join almacen.marcas as m on(p.codmarca=m.codmarca)
    inner join almacen.lineas as l on(p.codlinea=l.codlinea)
    where " .
                        $linea .
                        " (REPLACE(UPPER(p.descripcion),' ','%') like REPLACE (UPPER('%" .
                        $this->request->buscar .
                        "%'),' ','%') or UPPER(p.codigo) like UPPER('%" .
                        $this->request->buscar .
                        "%') or UPPER(m.descripcion) like UPPER('%" .
                        $this->request->buscar .
                        "%') ) and p.paraventa=1 and p.estado=1 and pu.estado=1 and pu.codalmacen=" .
                        $_SESSION['phuyu_codalmacen'] .
                        ' and pu.codunidad = (
                            select pun0.codunidad
                            from almacen.productounidades pun0
                            where pun0.codproducto = p.codproducto and pun0.estado = 1
                            order by pun0.factor asc, pun0.codunidad asc
                            limit 1
                        ) order by p.codproducto desc',
                )
                ->result_array();

            foreach ($lista as $key => $value) {
                $factormaximo = $this->db->query('select max(factor) as factor from almacen.productounidades where codproducto=' . $value['codproducto'] . ' and estado=1')->result_array();

                $precio = $this->db->query('select factor,pventapublico,pventamin,pventacredito,pventaxmayor, preciocosto,pventaadicional from almacen.productounidades where codproducto=' . $value['codproducto'] . ' and codunidad=' . $value['codunidad'] . ' and estado=1')->result_array();
                $lista[$key]['mostrarstock'] = 'STOCK: ' . round($value['stock'] - $value['comprometido'], 2);
                $lista[$key]['stockdisponible'] = round($value['stock'] - $value['comprometido'], 2);
                if (count($precio) == 0) {
                    $lista[$key]['factor'] = 0;
                    $lista[$key]['factormaximo'] = 0;
                    $lista[$key]['precio'] = 0.0;
                    $lista[$key]['preciomin'] = 0.0;
                    $lista[$key]['preciocredito'] = 0.0;
                    $lista[$key]['preciomayor'] = 0.0;
                    $lista[$key]['preciocosto'] = 0.0;
                    $lista[$key]['precioadicional'] = 0.0;
                } else {
                    $lista[$key]['factor'] = $precio[0]['factor'];
                    $lista[$key]['factormaximo'] = $factormaximo[0]['factor'];
                    $lista[$key]['precio'] = round($precio[0]['pventapublico'], 2);
                    $lista[$key]['preciomin'] = round($precio[0]['pventamin'], 2);
                    $lista[$key]['preciocredito'] = round($precio[0]['pventacredito'], 2);
                    $lista[$key]['preciomayor'] = round($precio[0]['pventaxmayor'], 2);
                    $lista[$key]['preciocosto'] = round($precio[0]['preciocosto'], 2);
                    $lista[$key]['precioadicional'] = round($precio[0]['pventaadicional'], 2);
                }
            }

            echo json_encode($lista);
        }
    }
    public function buscando_restobar()
    {
        if (!$this->input->is_ajax_request()) {
            show_error('Acceso no autorizado', 403);
            return;
        }

        $this->request = json_decode(file_get_contents('php://input'), true);

        // Parámetros del cliente
        $buscar = isset($this->request['buscar']) ? trim($this->request['buscar']) : '';
        $codlinea = isset($this->request['codlinea']) ? intval($this->request['codlinea']) : 0;
        $codalmacen = isset($_SESSION['phuyu_codalmacen']) ? intval($_SESSION['phuyu_codalmacen']) : 0;
        $limit = isset($this->request['limit']) ? intval($this->request['limit']) : 50;
        $offset = isset($this->request['offset']) ? intval($this->request['offset']) : 0;

        $like = $this->db->escape_like_str($buscar);

        $this->db->select(
            "
        p.codproducto, p.codigo, p.descripcion,
        p.controlstock, p.afectoigvcompra, p.afectoigvventa,
        p.calcular, p.foto,
        u.codunidad, u.descripcion AS unidad,
        ROUND(pu.stockactualconvertido,2) AS stock,
        m.descripcion AS marca, l.background, l.color,
        (
            SELECT COALESCE(SUM(pd.cantidad),0)
            FROM kardex.pedidos AS pedi
            INNER JOIN restaurante.mesaspedido AS mp ON (mp.codpedido = pedi.codpedido AND mp.estado = 1)
            INNER JOIN kardex.pedidosdetalle AS pd ON (pedi.codpedido = pd.codpedido)
            WHERE pedi.estado = 1
              AND pd.estado = 1
              AND pu.codproducto = pd.codproducto
              AND pu.codunidad = pd.codunidad
        ) AS comprometido,
        (
            SELECT COALESCE(MAX(factor),0)
            FROM almacen.productounidades AS pun2
            WHERE pun2.codproducto = p.codproducto AND pun2.estado = 1
        ) AS factormaximo,
        pun.factor, pun.pventapublico, pun.pventamin, pun.pventacredito, pun.pventaxmayor,
        pun.preciocosto, pun.pventaadicional
   ",
            false,
        );

        $this->db->from('almacen.productos AS p');
        $this->db->join('almacen.productoubicacion AS pu', 'p.codproducto = pu.codproducto', 'inner');
        $this->db->join('almacen.unidades AS u', 'u.codunidad = pu.codunidad', 'inner');
        $this->db->join('almacen.marcas AS m', 'p.codmarca = m.codmarca', 'inner');
        $this->db->join('almacen.lineas AS l', 'p.codlinea = l.codlinea', 'inner');
        $this->db->join('almacen.productounidades AS pun', 'p.codproducto = pun.codproducto AND pu.codunidad = pun.codunidad AND pun.estado = 1', 'left');

        if ($codlinea > 0) {
            $this->db->where('p.codlinea', $codlinea);
        }

        $this->db->group_start();
        $this->db->like("REPLACE(UPPER(p.descripcion),' ','')", str_replace(' ', '', strtoupper($like)));
        $this->db->or_like('UPPER(p.codigo)', strtoupper($like));
        $this->db->or_like('UPPER(m.descripcion)', strtoupper($like));
        $this->db->group_end();

        $this->db->where('p.paraventa', 1);
        $this->db->where('p.estado', 1);
        $this->db->where('pu.estado', 1);
        $this->db->where('pu.codalmacen', $codalmacen);
        $this->db->where(
            'pu.codunidad = (
                SELECT pun0.codunidad
                FROM almacen.productounidades AS pun0
                WHERE pun0.codproducto = p.codproducto
                  AND pun0.estado = 1
                ORDER BY pun0.factor ASC, pun0.codunidad ASC
                LIMIT 1
            )',
            null,
            false,
        );

        $this->db->order_by('p.codproducto', 'desc');

        // 👉 Aquí se aplica el límite
        $limit = 80; // cantidad de registros por página
        $page = 1;
        $this->db->limit($limit, $offset);

        $query = $this->db->get();
        $lista = $query->result_array();

        foreach ($lista as $k => $row) {
            $stock = (float) $row['stock'];
            $comprometido = (float) $row['comprometido'];
            $disponible = round($stock - $comprometido, 2);

            $lista[$k]['mostrarstock'] = 'STOCK: ' . $disponible;
            $lista[$k]['stockdisponible'] = $disponible;

            $lista[$k]['precio'] = round((float) ($row['pventapublico'] ?? 0), 2);
            $lista[$k]['preciomin'] = round((float) ($row['pventamin'] ?? 0), 2);
            $lista[$k]['preciocredito'] = round((float) ($row['pventacredito'] ?? 0), 2);
            $lista[$k]['preciomayor'] = round((float) ($row['pventaxmayor'] ?? 0), 2);
            $lista[$k]['preciocosto'] = round((float) ($row['preciocosto'] ?? 0), 2);
            $lista[$k]['precioadicional'] = round((float) ($row['pventaadicional'] ?? 0), 2);
        }

        $this->output->set_content_type('application/json')->set_output(json_encode($lista));
    }

    function producto_tipopedido($codproducto)
    {
        if ($this->input->is_ajax_request()) {
            $lista = $this->db->query('select p.codproducto,p.descripcion,u.codunidad,u.descripcion as unidad from almacen.productos as p inner join almacen.productoubicacion as pu on(p.codproducto=pu.codproducto) inner join almacen.unidades as u on(u.codunidad=pu.codunidad) where p.codproducto=' . $codproducto . ' and pu.estado=1')->result_array();
            foreach ($lista as $key => $value) {
                $lista[$key]['stock'] = 0;
                $lista[$key]['control'] = 0;
                $lista[$key]['calcular'] = 0;

                $precio = $this->db->query('select pventapublico from almacen.productounidades where codproducto=' . $value['codproducto'] . ' and codunidad=' . $value['codunidad'] . ' and estado=1')->result_array();
                if (count($precio) == 0) {
                    $lista[$key]['precio'] = 0.0;
                } else {
                    $lista[$key]['precio'] = round($precio[0]['pventapublico'], 2);
                }
            }
            echo json_encode($lista);
        }
    }

	    public function stockextra()
	    {
	        if (!$this->input->is_ajax_request() || !isset($_SESSION['phuyu_codusuario'])) {
	            $this->load->view('phuyu/404');
	            return;
	        }
	        $this->output->set_content_type('application/json', 'utf-8');

	        if (!isset($_FILES['archivo']) || $_FILES['archivo']['name'] == '') {
	            echo json_encode(['estado' => 0, 'mensaje' => 'Debe seleccionar el archivo de stock extra.']);
	            return;
	        }

	        $procesados = 0;
	        $errores = [];
	        $transaccion = false;

	        try {
	            $filas = $this->leer_archivo_stockextra($_FILES['archivo']);

	            $this->db->trans_begin();
	            $transaccion = true;
	            $this->db->update('almacen.productoubicacion', ['stockproveedor' => 0]);

            foreach ($filas as $row => $fila) {
                $codigo = $this->normalizar_texto($this->valor_fila($fila, 'A'));
                $stock = $this->numero_excel($this->valor_fila($fila, 'E'), null);

                if ($codigo === '' && $stock === null) {
                    continue;
                }

                if ($codigo === '' || $stock === null) {
                    $errores[] = 'Fila ' . $row . ': falta codigo o stock extra.';
                    continue;
                }

                $producto = $this->db->get_where('almacen.productos', ['codigo' => $codigo])->row_array();
                if (empty($producto)) {
                    $errores[] = 'Fila ' . $row . ': no existe producto con codigo ' . $codigo . '.';
                    continue;
                }

                $this->db->where('codproducto', (int) $producto['codproducto']);
                $this->db->where('factor', 1);
                $this->db->where('estado', 1);
                $this->db->update('almacen.productoubicacion', ['stockproveedor' => (float) $stock]);
                $procesados++;
            }

            if ($procesados == 0 || $this->db->trans_status() === false) {
                $this->db->trans_rollback();
                $mensaje = 'No se encontro ninguna fila valida para cargar stock extra.';
                if (count($errores) > 0) {
                    $mensaje .= ' ' . implode(' ', array_slice($errores, 0, 5));
                }
                echo json_encode(['estado' => 0, 'mensaje' => $mensaje]);
                return;
            }

            $this->db->trans_commit();

            $mensaje = 'Stock extra procesado: ' . $procesados . ' productos.';
            if (count($errores) > 0) {
                $mensaje .= ' Filas omitidas: ' . count($errores) . '. ' . implode(' ', array_slice($errores, 0, 3));
            }

            echo json_encode(['estado' => 1, 'mensaje' => $mensaje, 'procesados' => $procesados, 'errores' => $errores]);
	        } catch (Exception $e) {
	            if ($transaccion) {
	                $this->db->trans_rollback();
	            }
	            echo json_encode(['estado' => 0, 'mensaje' => 'No se pudo leer el archivo. Verifique que sea un Excel o CSV valido.']);
	        }
	    }

    function phuyu_masprecios()
    {
        if ($this->input->is_ajax_request()) {
            $this->request = json_decode(file_get_contents('php://input'));
            $almacenes = $this->db->query('select *from almacen.almacenes where estado = 1 AND codsucursal=' . $_SESSION['phuyu_codsucursal'])->result_array();
            $precio = (float) $this->request->producto->preciosinigv * (float) $this->request->tipocambio;
            $igv = (float) $this->request->producto->igv * (float) $this->request->tipocambio;
            $moneda = $this->request->moneda;
            $tipocambio = $this->request->tipocambio;
            $productosunidades = $this->db->query('select *from almacen.productoubicacion where codproducto=' . $this->request->producto->codproducto . ' AND codalmacen=' . $_SESSION['phuyu_codalmacen'] . ' AND codunidad=' . $this->request->producto->codunidad)->result_array();
            $this->load->view('almacen/productos/masprecios', compact('almacenes', 'productosunidades', 'precio', 'igv', 'moneda', 'tipocambio'));
        }
    }

    function modificarprecios()
    {
        if ($this->input->is_ajax_request()) {
            $this->request = json_decode(file_get_contents('php://input'));

            $campos = ['preciocompra', 'pventapublico', 'pventamin', 'pventacredito', 'pventaxmayor', 'preciocosto', 'igvcompra', 'fletecompra', 'utilidad', 'utilidadporc', 'igvventa', 'pigvventapublico', 'pventaminutilidad', 'pventaminutilidadporc', 'igvminimo', 'pventaminigv', 'pventaxmayorutilidad', 'pventaxmayorutilidadporc', 'igvxmayor', 'pventaxmayorigv', 'pventacreditoutilidad', 'pventacreditoutilidadporc', 'igvcredito', 'pventacreditoigv'];

            $valores = [
                $this->request->precios->preciocompra,
                $this->request->precios->pventapublico,
                $this->request->precios->pventamin,
                $this->request->precios->pventacredito,
                $this->request->precios->pventaxmayor,
                $this->request->precios->preciocosto,
                $this->request->precios->igvcompra,
                $this->request->precios->fletecompra,
                $this->request->precios->utilidad,
                $this->request->precios->utilidadporc,
                $this->request->precios->igvventa,
                $this->request->precios->pigvventapublico,
                $this->request->precios->pventaminutilidad,
                $this->request->precios->pventaminutilidadporc,
                $this->request->precios->igvminimo,
                $this->request->precios->pventaminigv,
                $this->request->precios->pventaxmayorutilidad,
                $this->request->precios->pventaxmayorutilidadporc,
                $this->request->precios->igvxmayor,
                $this->request->precios->pventaxmayorigv,
                $this->request->precios->pventacreditoutilidad,
                $this->request->precios->pventacreditoutilidadporc,
                $this->request->precios->igvcredito,
                $this->request->precios->pventacreditoigv,
            ];

            $f = ['codproducto', 'codunidad', 'codalmacen'];
            $v = [(int) $this->request->campos->codproducto, (int) $this->request->campos->codunidad, (int) $this->request->campos->codalmacen];
            $estado = $this->phuyu_model->phuyu_editar_1('almacen.productoubicacion', $campos, $valores, $f, $v);

            echo $estado;
        }
    }

    public function buscar_serie()
    {
        if ($this->input->is_ajax_request()) {

            $request = json_decode(file_get_contents('php://input'));
            $serie = trim($request->serie ?? '');

            if ($serie === '') {
                echo json_encode([
                    "estado" => false,
                    "existe" => false,
                    "mensaje" => "Serie vacía",
                    "data" => null
                ]);
                return;
            }

            $query = $this->db
                ->select('*')
                ->from('almacen.series')
                ->where('serie_codigo', $serie)
                ->limit(1)
                ->get();

            $existe = $query->num_rows() > 0;
            $data   = $existe ? $query->row_array() : null;

            echo json_encode([
                "estado"  => true,
                "existe"  => $existe,
                "mensaje" => $existe
                    ? "La serie ya está registrada en el sistema."
                    : "Serie disponible.",
                "data"    => $data
            ]);
        }
    }

    private function leer_archivo_carga_productos($archivo)
    {
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

        if ($extension === 'csv' || $extension === 'txt') {
            return $this->leer_csv_productos($archivo['tmp_name']);
        }

        if ($extension === 'xlsx') {
            return $this->leer_xlsx_productos($archivo['tmp_name']);
        }

        if ($extension === 'xls' || $extension === 'html' || $extension === 'htm') {
            return $this->leer_xls_productos($archivo['tmp_name']);
        }

        throw new Exception('Formato no soportado');
    }

    private function leer_archivo_stockextra($archivo)
    {
        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

        if ($extension === 'csv' || $extension === 'txt') {
            return $this->leer_csv_productos($archivo['tmp_name']);
        }

        if ($extension === 'xlsx') {
            return $this->leer_xlsx_productos($archivo['tmp_name']);
        }

        if ($extension === 'xls' || $extension === 'html' || $extension === 'htm') {
            return $this->leer_xls_productos($archivo['tmp_name']);
        }

        throw new Exception('Formato no soportado');
    }

    private function cabeceras_carga_productos()
    {
        return [
            'codigo',
            'descripcion',
            'tipo',
            'familia',
            'linea',
            'marca',
            'unidad',
            'factor',
            'precio_compra',
            'precio_venta',
            'precio_minimo',
            'precio_credito',
            'precio_mayor',
            'precio_adicional',
            'codigo_barra',
            'stock_inicial',
            'control_stock',
            'cod_afectacion_igv_compra',
            'cod_afectacion_igv_venta',
            'afecto_icbper',
            'comision_vendedor',
            'caracteristicas',
            'controlar_series'
        ];
    }

    private function leer_csv_productos($archivo)
    {
        $lineaInicial = (string) file_get_contents($archivo, false, null, 0, 4096);
        $delimitador = $this->detectar_delimitador_csv($lineaInicial);
        $handle = fopen($archivo, 'r');
        $filas = [];
        $numeroFila = 0;

        while (($data = fgetcsv($handle, 0, $delimitador)) !== false) {
            $numeroFila++;
            if ($numeroFila == 1) {
                continue;
            }

            $fila = [];
            foreach ($data as $index => $valor) {
                $fila[$this->columna_excel_desde_indice($index)] = $valor;
            }
            $filas[$numeroFila] = $fila;
        }

        fclose($handle);
        return $filas;
    }

    private function leer_html_xls_productos($archivo)
    {
        $contenido = file_get_contents($archivo);
        if ($contenido === false || trim($contenido) === '') {
            return [];
        }

        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        $dom->loadHTML('<?xml encoding="utf-8" ?>' . $contenido);
        libxml_clear_errors();

        $tablas = $dom->getElementsByTagName('table');
        if ($tablas->length == 0) {
            return $this->leer_csv_productos($archivo);
        }

        $filas = [];
        $numeroFila = 0;
        $rows = $tablas->item(0)->getElementsByTagName('tr');

        foreach ($rows as $row) {
            $numeroFila++;
            if ($numeroFila == 1) {
                continue;
            }

            $fila = [];
            $celdaIndex = 0;
            foreach ($row->childNodes as $cell) {
                if (!in_array(strtolower($cell->nodeName), ['td', 'th'], true)) {
                    continue;
                }

                $fila[$this->columna_excel_desde_indice($celdaIndex)] = trim($cell->textContent);
                $celdaIndex++;
            }

            $filas[$numeroFila] = $fila;
        }

        return $filas;
    }

    private function leer_xls_productos($archivo)
    {
        $contenido = (string) file_get_contents($archivo, false, null, 0, 512);
        if (stripos($contenido, '<html') !== false || stripos($contenido, '<table') !== false) {
            return $this->leer_html_xls_productos($archivo);
        }

        return $this->leer_phpexcel_productos($archivo);
    }

	    private function leer_phpexcel_productos($archivo)
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
        $highestColumn = $sheet->getHighestColumn();
        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
        $filas = [];

        for ($row = 2; $row <= $highestRow; $row++) {
            $fila = [];
            for ($col = 0; $col < $highestColumnIndex; $col++) {
                $fila[$this->columna_excel_desde_indice($col)] = $this->valor_celda($sheet, $this->columna_excel_desde_indice($col), $row);
            }
            $filas[$row] = $fila;
        }

        return $filas;
    }

    private function leer_xlsx_productos($archivo)
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
            if ($numeroFila <= 1) {
                continue;
            }

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

            $filas[$numeroFila] = $fila;
        }

        return $filas;
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
        $anterior = error_reporting();
        $displayErrors = ini_get('display_errors');
        ini_set('display_errors', '0');
        error_reporting(0);

        $excel = new PHPExcel();
        $excel->getProperties()
            ->setCreator('Phuyu Comercial')
            ->setTitle(pathinfo($archivo, PATHINFO_FILENAME));

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

        error_reporting($anterior);
        ini_set('display_errors', $displayErrors);
        exit;
    }

    private function nombre_hoja_excel($nombre)
    {
        $nombre = preg_replace('/[\\\\\\/\\?\\*\\[\\]\\:]/', ' ', $this->normalizar_texto($nombre));
        $nombre = trim(substr($nombre, 0, 31));
        return $nombre === '' ? 'Hoja' : $nombre;
    }

    private function detectar_delimitador_csv($linea)
    {
        $puntoComa = substr_count($linea, ';');
        $coma = substr_count($linea, ',');
        return $puntoComa >= $coma ? ';' : ',';
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

    private function procesar_fila_carga_producto($fila, $row)
    {
        $codigo = $this->normalizar_texto($this->valor_fila($fila, 'A'));
        $descripcion = $this->normalizar_texto($this->valor_fila($fila, 'B'));
        $familia = $this->valor_fila($fila, 'D');
        $linea = $this->valor_fila($fila, 'E');
        $marca = $this->valor_fila($fila, 'F');
        $unidad = $this->valor_fila($fila, 'G');

        if ($codigo === '' && $descripcion === '' && $this->normalizar_texto($familia) === '' && $this->normalizar_texto($linea) === '' && $this->normalizar_texto($marca) === '' && $this->normalizar_texto($unidad) === '') {
            return ['estado' => 0, 'mensaje' => ''];
        }

        if ($descripcion === '') {
            return ['estado' => 0, 'mensaje' => 'Fila ' . $row . ': falta descripcion.'];
        }

        $tipo = $this->entero_excel($this->valor_fila($fila, 'C'), 1);
        if ($tipo != 1 && $tipo != 2) {
            $tipo = 1;
        }

        $codfamilia = $this->resolver_catalogo('familias', 'codfamilia', $familia, 'GENERAL');
        $codlinea = $this->resolver_catalogo('lineas', 'codlinea', $linea, 'GENERAL');
        $codmarca = $this->resolver_catalogo('marcas', 'codmarca', $marca, 'GENERICO');
        $codunidad = $this->resolver_unidad($unidad);

        if ($codfamilia === null || $codlinea === null || $codmarca === null || $codunidad === null) {
            return ['estado' => 0, 'mensaje' => 'Fila ' . $row . ': no se pudo resolver familia, linea, marca o unidad.'];
        }

        $factor = $this->numero_excel($this->valor_fila($fila, 'H'), 1);
        if ($factor <= 0) {
            $factor = 1;
        }

        $precioCompra = $this->numero_excel($this->valor_fila($fila, 'I'), 0);
        $precioVenta = $this->numero_excel($this->valor_fila($fila, 'J'), 0);
        $precioMinimo = $this->numero_excel($this->valor_fila($fila, 'K'), $precioVenta);
        $precioCredito = $this->numero_excel($this->valor_fila($fila, 'L'), $precioVenta);
        $precioMayor = $this->numero_excel($this->valor_fila($fila, 'M'), $precioVenta);
        $precioAdicional = $this->numero_excel($this->valor_fila($fila, 'N'), $precioVenta);
        $codigoBarra = $this->normalizar_texto($this->valor_fila($fila, 'O'));
        $stockInicial = $this->numero_excel($this->valor_fila($fila, 'P'), null);
        $controlStock = $this->booleano_excel($this->valor_fila($fila, 'Q'), 1);
        $codAfectacionCompra = $this->resolver_afectacion($this->valor_fila($fila, 'R'), 1);
        $codAfectacionVenta = $this->resolver_afectacion($this->valor_fila($fila, 'S'), isset($_SESSION['phuyu_afectacionigv']) ? (int) $_SESSION['phuyu_afectacionigv'] : 9);
        $afectoIcbper = $this->booleano_excel($this->valor_fila($fila, 'T'), 0);
        $comisionVendedor = $this->numero_excel($this->valor_fila($fila, 'U'), 0);
        $caracteristicas = $this->normalizar_texto($this->valor_fila($fila, 'V'));
        $controlarSeries = $this->booleano_excel($this->valor_fila($fila, 'W'), 0);

        $producto = [];
        if ($codigo !== '') {
            $producto = $this->db->get_where('almacen.productos', ['codigo' => $codigo])->row_array();
        }

        if (empty($producto) && $codigoBarra !== '') {
            $producto = $this->db->query(
                "select p.* from almacen.productos p inner join almacen.productounidades u on (p.codproducto = u.codproducto) where p.estado = 1 and u.codigobarra = ? limit 1",
                [$codigoBarra]
            )->row_array();
        }

        if (empty($producto) && $descripcion !== '') {
            $producto = $this->db->query(
                "select * from almacen.productos where estado = 1 and upper(trim(descripcion)) = upper(trim(?)) limit 1",
                [$descripcion]
            )->row_array();
        }

        $dataProducto = [
            'codfamilia' => (int) $codfamilia,
            'codlinea' => (int) $codlinea,
            'codmarca' => (int) $codmarca,
            'codempresa' => isset($_SESSION['phuyu_codempresa']) ? (int) $_SESSION['phuyu_codempresa'] : 1,
            'descripcion' => $descripcion,
            'afectoicbper' => (int) $afectoIcbper,
            'controlstock' => (int) $controlStock,
            'afectoigvcompra' => $codAfectacionCompra == 1 ? 1 : 0,
            'afectoigvventa' => $codAfectacionVenta == 1 ? 1 : 0,
            'calcular' => 0,
            'paraventa' => 1,
            'codatencion' => 0,
            'caracteristicas' => $caracteristicas,
            'tipo' => (int) $tipo,
            'controlarseries' => (int) $controlarSeries,
            'estado' => 1
        ];

        if ($codigo !== '') {
            $dataProducto['codigo'] = $codigo;
        }

        if (!empty($producto)) {
            $codproducto = (int) $producto['codproducto'];
            $this->db->where('codproducto', $codproducto);
            $this->db->update('almacen.productos', $dataProducto);
        } else {
            $this->db->insert('almacen.productos', $dataProducto);
            $codproducto = $this->ultimo_id_insertado('almacen.productos', 'codproducto');

            if ($codigo === '') {
                $codigo = '000' . $codproducto;
                $this->db->where('codproducto', $codproducto);
                $this->db->update('almacen.productos', ['codigo' => $codigo]);
            }
        }

        $dataUnidad = [
            'codproducto' => (int) $codproducto,
            'codunidad' => (int) $codunidad,
            'codsucursal' => isset($_SESSION['phuyu_codsucursal']) ? (int) $_SESSION['phuyu_codsucursal'] : null,
            'factor' => (float) $factor,
            'preciocompra' => (float) $precioCompra,
            'preciocosto' => (float) $precioCompra,
            'pventapublico' => (float) $precioVenta,
            'pventamin' => (float) $precioMinimo,
            'pventacredito' => (float) $precioCredito,
            'pventaxmayor' => (float) $precioMayor,
            'pventaadicional' => (float) $precioAdicional,
            'codigobarra' => $codigoBarra,
            'estado' => 1
        ];

        $this->upsert_por_filtro('almacen.productounidades', $dataUnidad, [
            'codproducto' => (int) $codproducto,
            'codunidad' => (int) $codunidad
        ]);

        $almacenes = $this->db->query('select codalmacen, codsucursal, codafectacionigv from almacen.almacenes where estado=1 order by codalmacen')->result_array();
        foreach ($almacenes as $almacen) {
            $esAlmacenActual = isset($_SESSION['phuyu_codalmacen']) && (int) $_SESSION['phuyu_codalmacen'] == (int) $almacen['codalmacen'];
            $afectacionAlmacen = (int) $almacen['codafectacionigv'] > 0 ? (int) $almacen['codafectacionigv'] : $codAfectacionVenta;

            $dataUbicacion = [
                'codalmacen' => (int) $almacen['codalmacen'],
                'codproducto' => (int) $codproducto,
                'codunidad' => (int) $codunidad,
                'codsucursal' => (int) $almacen['codsucursal'],
                'factor' => (float) $factor,
                'preciocompra' => (float) $precioCompra,
                'preciocosto' => (float) $precioCompra,
                'pventapublico' => (float) $precioVenta,
                'pventamin' => (float) $precioMinimo,
                'pventacredito' => (float) $precioCredito,
                'pventaxmayor' => (float) $precioMayor,
                'pventaadicional' => (float) $precioAdicional,
                'codigobarra' => $codigoBarra,
                'estado' => 1,
                'codafectacionigvcompra' => $esAlmacenActual ? (int) $codAfectacionCompra : $afectacionAlmacen,
                'codafectacionigvventa' => $esAlmacenActual ? (int) $codAfectacionVenta : $afectacionAlmacen,
                'comisionvendedor' => (float) $comisionVendedor
            ];

            if ($stockInicial !== null && $esAlmacenActual) {
                $existenciaUbicacion = $this->db->get_where('almacen.productoubicacion', [
                    'codalmacen' => (int) $almacen['codalmacen'],
                    'codproducto' => (int) $codproducto,
                    'codunidad' => (int) $codunidad,
                    'estado' => 1
                ])->row_array();

                if (!empty($existenciaUbicacion)) {
                    $dataUbicacion['stockactual'] = (float) $existenciaUbicacion['stockactual'] + (float) $stockInicial;
                    $dataUbicacion['stockactualreal'] = (float) $existenciaUbicacion['stockactualreal'] + (float) $stockInicial;
                    $dataUbicacion['stockactualconvertido'] = (float) $existenciaUbicacion['stockactualconvertido'] + (float) $stockInicial;
                    $dataUbicacion['preciostockvalorizado'] = (float) $existenciaUbicacion['preciostockvalorizado'] + ((float) $stockInicial * (float) $precioCompra);
                } else {
                    $dataUbicacion['stockactual'] = (float) $stockInicial;
                    $dataUbicacion['stockactualreal'] = (float) $stockInicial;
                    $dataUbicacion['stockactualconvertido'] = (float) $stockInicial;
                    $dataUbicacion['preciostockvalorizado'] = (float) $stockInicial * (float) $precioCompra;
                }
            }

            $shouldUpsertUbicacion = true;
            $existenciaUbicacion = $this->db->get_where('almacen.productoubicacion', [
                'codalmacen' => (int) $almacen['codalmacen'],
                'codproducto' => (int) $codproducto,
                'codunidad' => (int) $codunidad,
                'estado' => 1
            ])->row_array();

            if ($stockInicial === null) {
                if (empty($existenciaUbicacion)) {
                    // No vamos a crear una ubicación de stock en 0 si el Excel no trae valor de stock.
                    $shouldUpsertUbicacion = false;
                } else {
                    // Si hay stock_inicial null pero el registro existe, solo actualizar precios sin tocar el stock
                    $dataUbicacion['stockactual'] = (float) $existenciaUbicacion['stockactual'];
                    $dataUbicacion['stockactualreal'] = (float) $existenciaUbicacion['stockactualreal'];
                    $dataUbicacion['stockactualconvertido'] = (float) $existenciaUbicacion['stockactualconvertido'];
                    $dataUbicacion['preciostockvalorizado'] = (float) $existenciaUbicacion['preciostockvalorizado'];
                }
            }

            if ($shouldUpsertUbicacion) {
                $this->upsert_por_filtro('almacen.productoubicacion', $dataUbicacion, [
                    'codalmacen' => (int) $almacen['codalmacen'],
                    'codproducto' => (int) $codproducto,
                    'codunidad' => (int) $codunidad
                ]);
            }
        }

        return ['estado' => 1, 'mensaje' => ''];
    }

    private function resolver_catalogo($tabla, $pk, $valor, $defecto)
    {
        $texto = $this->normalizar_texto($valor);

        if ($texto !== '' && is_numeric($texto)) {
            $registro = $this->db->get_where('almacen.' . $tabla, [$pk => (int) $texto, 'estado' => 1])->row_array();
            if (!empty($registro)) {
                if ($tabla === 'lineas') {
                    $this->asegurar_linea_sucursal((int) $registro[$pk]);
                }
                return (int) $registro[$pk];
            }
        }

        if ($texto === '') {
            $texto = $defecto;
        }

        $registro = $this->db->query('select ' . $pk . ' from almacen.' . $tabla . ' where estado=1 and upper(trim(descripcion))=? limit 1', [strtoupper($texto)])->row_array();
        if (!empty($registro)) {
            if ($tabla === 'lineas') {
                $this->asegurar_linea_sucursal((int) $registro[$pk]);
            }
            return (int) $registro[$pk];
        }

        $this->db->insert('almacen.' . $tabla, [
            'descripcion' => $texto,
            'estado' => 1
        ]);

        $codigo = $this->ultimo_id_insertado('almacen.' . $tabla, $pk);
        if ($tabla === 'lineas') {
            $this->asegurar_linea_sucursal($codigo);
        }

        return $codigo > 0 ? $codigo : null;
    }

    private function resolver_unidad($valor)
    {
        $texto = $this->normalizar_texto($valor);

        if ($texto !== '' && is_numeric($texto)) {
            $registro = $this->db->get_where('almacen.unidades', ['codunidad' => (int) $texto, 'estado' => 1])->row_array();
            if (!empty($registro)) {
                return (int) $registro['codunidad'];
            }
        }

        if ($texto === '') {
            $texto = 'UNIDAD';
        }

        $registro = $this->db->query("select codunidad from almacen.unidades where estado=1 and (upper(trim(descripcion))=? or upper(trim(oficial))=?) limit 1", [strtoupper($texto), strtoupper($texto)])->row_array();
        if (!empty($registro)) {
            return (int) $registro['codunidad'];
        }

        $oficial = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $texto), 0, 3));
        if ($oficial === '') {
            $oficial = 'NIU';
        }

        $this->db->insert('almacen.unidades', [
            'descripcion' => $texto,
            'oficial' => $oficial,
            'estado' => 1
        ]);

        $codigo = $this->ultimo_id_insertado('almacen.unidades', 'codunidad');
        return $codigo > 0 ? $codigo : null;
    }

    private function resolver_afectacion($valor, $defecto)
    {
        $texto = $this->normalizar_texto($valor);

        if ($texto !== '' && is_numeric($texto)) {
            $registro = $this->db->get_where('afectacionigv', ['codafectacionigv' => (int) $texto, 'estado' => 1])->row_array();
            if (!empty($registro)) {
                return (int) $registro['codafectacionigv'];
            }
        }

        if ($texto !== '') {
            $registro = $this->db->query("select codafectacionigv from afectacionigv where estado=1 and (upper(trim(descripcion))=? or upper(trim(oficial))=?) limit 1", [strtoupper($texto), strtoupper($texto)])->row_array();
            if (!empty($registro)) {
                return (int) $registro['codafectacionigv'];
            }
        }

        return (int) $defecto;
    }

    private function asegurar_linea_sucursal($codlinea)
    {
        if ($codlinea <= 0 || !isset($_SESSION['phuyu_codsucursal'])) {
            return;
        }

        $existe = $this->db->get_where('almacen.lineasxsucursales', [
            'codlinea' => (int) $codlinea,
            'codsucursal' => (int) $_SESSION['phuyu_codsucursal']
        ])->row_array();

        if (empty($existe)) {
            $this->db->insert('almacen.lineasxsucursales', [
                'codlinea' => (int) $codlinea,
                'codsucursal' => (int) $_SESSION['phuyu_codsucursal']
            ]);
        }
    }

    private function upsert_por_filtro($tabla, $data, $filtro)
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

    private function ultimo_id_insertado($tabla, $columna)
    {
        $id = (int) $this->db->insert_id();
        if ($id > 0) {
            return $id;
        }

        $registro = $this->db->query("select currval(pg_get_serial_sequence(?, ?)) as id", [$tabla, $columna])->row_array();
        return isset($registro['id']) ? (int) $registro['id'] : 0;
    }

    private function valor_celda($sheet, $columna, $fila)
    {
        $valor = $sheet->getCell($columna . $fila)->getCalculatedValue();
        if ($valor instanceof PHPExcel_RichText) {
            $valor = $valor->getPlainText();
        }
        return $valor;
    }

    private function valor_fila($fila, $columna)
    {
        return isset($fila[$columna]) ? $fila[$columna] : '';
    }

    private function normalizar_texto($valor)
    {
        $valor = str_replace("\xEF\xBB\xBF", '', (string) $valor);
        return trim(preg_replace('/\s+/', ' ', $valor));
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
