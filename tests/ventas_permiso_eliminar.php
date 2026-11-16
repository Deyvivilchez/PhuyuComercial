<?php
// Ejecutar: php tests/ventas_permiso_eliminar.php
// Comprueba sesiones abiertas sin modificar ventas ni conectarse a la base de datos.
define('BASEPATH', __DIR__);
class CI_Controller {
    public $db;
    public $load;
    public function __construct() {
        $this->db = $GLOBALS['db_prueba'];
        $this->load = new class { public function model($nombre) {} };
    }
}
require __DIR__.'/../application/controllers/ventas/Ventas.php';
class PermisosDbPrueba {
    public $usuario = [];
    public $consultas = 0;
    public function query($sql, $params) {
        if (strpos($sql, 'eliminar_venta') === false || strpos($sql, 'estado=1') === false || $params !== [1]) {
            throw new RuntimeException('Consulta de permisos incorrecta');
        }
        $this->consultas++;
        return new class($this->usuario) {
            private $usuario;
            public function __construct($usuario) { $this->usuario = $usuario; }
            public function row_array() { return $this->usuario; }
        };
    }
}
function comprobar($condicion, $mensaje) {
    if (!$condicion) { throw new RuntimeException($mensaje); }
}
$GLOBALS['db_prueba'] = new PermisosDbPrueba();
$permiso = new ReflectionMethod(Ventas::class, 'phuyu_usuario_puede_eliminar_venta');
$permiso->setAccessible(true);
foreach ([1, 0] as $valor) {
    $_SESSION = ['phuyu_codusuario' => 1, 'phuyu_eliminar_venta' => 1 - $valor];
    $GLOBALS['db_prueba']->usuario = ['editar_pventa' => 0, 'eliminar_venta' => (string)$valor];
    $ventas = new Ventas();
    comprobar($permiso->invoke($ventas) === ($valor === 1), 'No actualiza el permiso de la sesion abierta');
}
$_SESSION = ['phuyu_codusuario' => 1, 'phuyu_eliminar_venta' => 1];
$GLOBALS['db_prueba']->usuario = [];
comprobar($permiso->invoke(new Ventas()) === false, 'Usuario inactivo conserva permiso');
$_SESSION = [];
$consultas = $GLOBALS['db_prueba']->consultas;
comprobar($permiso->invoke(new Ventas()) === false, 'Sesion anonima autorizada');
comprobar($GLOBALS['db_prueba']->consultas === $consultas, 'Sesion anonima consulta permisos');
echo "OK: permiso concedido y revocado en sesiones abiertas; usuario inactivo y sesion anonima rechazados.\n";
