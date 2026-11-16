<?php
// Prueba CLI: todas las escrituras se limitan a tablas TEMPORARY de esta conexión.
if (PHP_SAPI !== 'cli') { exit(1); }
$source = $argv[1] ?? dirname(__DIR__);
$config = $argv[2] ?? '/var/www/boss.phuyusystem.com';
define('BASEPATH', $config); define('ENVIRONMENT', 'production');
ini_set('session.save_path', '/tmp');
require $config.'/application/config/database.php';
session_write_close();
$c = $db[$active_group];
$pdo = new PDO('pgsql:host='.$c['hostname'].';port='.($c['port'] ?: '5432').';dbname='.$c['database'], $c['username'], $c['password'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
class CI_Model { public $db; public $load; public $Stock_reservas_model; public function __construct() {} }
class TestResult {
    private $rows;
    public function __construct($rows) { $this->rows=$rows; }
    public function result_array() { return $this->rows; }
    public function row_array() { return $this->rows[0] ?? []; }
}
class TestDB {
    private $pdo;
    public $depth = 1;
    public function trans_begin() { $this->depth++; }
    public function trans_complete() { $this->depth--; }
    public function trans_status() { return true; }
    public function __construct($pdo) { $this->pdo=$pdo; }
    public function query($sql,$params=[]) {
        if (!preg_match('/^\s*SELECT/i', $sql)) { throw new Exception('El modelo intentó escribir'); }
        $sql=strtr($sql, [
            'almacen.productoubicacion'=>'pg_temp.stock_pu', 'almacen.productounidades'=>'pg_temp.stock_pun',
            'almacen.productos'=>'pg_temp.stock_p', 'almacen.unidades'=>'pg_temp.stock_u',
            'kardex.pedidosdetalle'=>'pg_temp.stock_pd', 'kardex.pedidos'=>'pg_temp.stock_pedi',
            'restaurante.mesaspedido'=>'pg_temp.stock_mp'
        ]);
        if (preg_match('/\b(almacen|kardex|restaurante)\./', $sql)) { throw new Exception('Referencia fuera de las tablas temporales'); }
        $stmt=$this->pdo->prepare($sql);$stmt->execute($params);
        return new TestResult($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
require $source.'/application/models/Stock_reservas_model.php';
function check($ok,$msg) { if (!$ok) { throw new Exception($msg); } echo 'OK '.$msg.PHP_EOL; }
$pdo->beginTransaction();
try {
    foreach ([
        'stock_p (codproducto int, descripcion text, controlstock int)',
        'stock_u (codunidad int, descripcion text)',
        'stock_pun (codproducto int, codunidad int, factor numeric)',
        'stock_pu (codproducto int,codunidad int,codalmacen int,factor numeric,stockactualconvertido numeric,estado int)',
        'stock_pedi (codpedido int, codalmacen int,codsucursal int,estado int)',
        'stock_pd (codpedido int,codproducto int,codunidad int,cantidad numeric,cantidadcomprobante numeric,estado int)',
        'stock_mp (codpedido int, estado int)'
    ] as $ddl) { $pdo->exec('CREATE TEMP TABLE '.$ddl.' ON COMMIT DROP'); }
    $pdo->exec("INSERT INTO pg_temp.stock_p VALUES (31,'COCA COLA',1),(99,'SERVICIO',0);
        INSERT INTO pg_temp.stock_u VALUES (138,'UNIDAD'),(43,'PAQUETE');
        INSERT INTO pg_temp.stock_pun VALUES (31,138,1),(31,43,12);
        INSERT INTO pg_temp.stock_pu VALUES (31,138,1,1,1,1),(31,43,1,12,1.0/12,1);
        INSERT INTO pg_temp.stock_pedi VALUES (2007,1,1,1);
        INSERT INTO pg_temp.stock_pd VALUES (2007,31,138,1,0,1);
        INSERT INTO pg_temp.stock_mp VALUES (2007,1)");
    $_SESSION=['phuyu_codalmacen'=>1,'phuyu_codsucursal'=>1,'phuyu_stockalmacen'=>1];
    $model=new Stock_reservas_model();$model->db=new TestDB($pdo);
    $item=(object)['codproducto'=>31,'codunidad'=>138,'cantidad'=>1];
    $p=$model->enriquecer(['codproducto'=>31,'codunidad'=>138,'stock'=>1,'unidades'=>'138|UNIDAD|x|1|0|4|4|4|1']);
    check($p['stock']==1 && $p['reservado']==1 && $p['stockdisponible']==0,'Existencia 1, reservado 1, disponible 0');
    check(!$model->validar([$item])['success'],'Bloquea venta normal de una unidad reservada');
    check($model->validar([$item],2007)['success'],'Permite cobrar la reserva del propio pedido');
    require $source.'/application/models/Kardex_model.php';
    $kardex = new Kardex_model();
    $kardex->db = $model->db;
    $kardex->Stock_reservas_model = $model;
    $kardex->load = new class { public function model($name) {} };
    check(!$kardex->phuyu_kardexdetalle(0,0,[$item],1)['success'], 'El guardado central rechaza lo reservado antes de escribir');
    $empty = $kardex->phuyu_kardexdetalle(0,0,[],1);
    check($empty['success'] && $model->db->depth === 1, 'El detalle no confirma la transacción del controlador');

    $pdo->exec('INSERT INTO pg_temp.stock_mp VALUES (2007,1)');
    check($model->enriquecer(['codproducto'=>31,'codunidad'=>138,'stock'=>1])['reservado']==1,'Una mesa duplicada no duplica la reserva');
    $pdo->exec('INSERT INTO pg_temp.stock_pedi VALUES (2,2,1,1),(3,1,2,1),(4,1,1,0),(5,1,1,1);
        INSERT INTO pg_temp.stock_pd VALUES (2,31,138,50,0,1),(3,31,138,50,0,1),(4,31,138,50,0,1),(5,31,138,50,0,0);
        INSERT INTO pg_temp.stock_mp VALUES (2,1),(3,1),(4,1),(5,1)');
    check($model->enriquecer(['codproducto'=>31,'codunidad'=>138,'stock'=>1])['reservado']==1,'Ignora otros almacenes, sucursales y pedidos/detalles anulados');
    $pdo->exec('UPDATE pg_temp.stock_pd SET codunidad=43 WHERE codpedido=2007; UPDATE pg_temp.stock_pu SET stockactualconvertido=24/factor');
    $p=$model->enriquecer(['codproducto'=>31,'codunidad'=>138,'stock'=>24]);
    check($p['reservado']==12 && $p['stockdisponible']==12,'Reserva un paquete como doce unidades');
    $item->cantidad=7;
    check(!$model->validar([$item,$item])['success'],'Suma filas repetidas antes de validar');
    $item->cantidad=1;
    $pack=(object)['codproducto'=>31,'codunidad'=>43,'cantidad'=>1];
    check(!$model->validar([$item,$pack])['success'],'Suma presentaciones distintas del mismo producto');
    $pdo->exec('UPDATE pg_temp.stock_pd SET cantidad=2,cantidadcomprobante=1 WHERE codpedido=2007');
    check($model->enriquecer(['codproducto'=>31,'codunidad'=>138,'stock'=>24])['reservado']==12,'Reserva solo la cantidad pendiente de comprobante');
    $pdo->exec('UPDATE pg_temp.stock_pedi SET estado=0 WHERE codpedido=2007');
    check($model->enriquecer(['codproducto'=>31,'codunidad'=>138,'stock'=>24])['reservado']==0,'Cerrar o anular el pedido libera la reserva');
    $pdo->exec('UPDATE pg_temp.stock_pu SET stockactualconvertido=0');
    check(!$model->validar([$item])['success'],'Después de descontar la existencia no permite otra venta');
    check($model->validar([(object)['codproducto'=>99,'codunidad'=>138,'cantidad'=>1]])['success'],'Respeta servicios sin control de inventario');
    $_SESSION['phuyu_stockalmacen']=0;
    check($model->validar([$item])['success'],'Respeta almacenes sin control de stock');
} finally { $pdo->rollBack(); }
echo "Pruebas completadas; tablas temporales descartadas.\n";
