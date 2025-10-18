<?php
// admempleados/controlador/ver_empleado.php
declare(strict_types=1);

// Responder siempre JSON
header('Content-Type: application/json; charset=utf-8');

// Silenciar HTML de errores y capturarlos como excepciones
ini_set('display_errors', '0');
ini_set('html_errors', '0');
error_reporting(E_ALL);

if (function_exists('ob_get_level')) {
    while (ob_get_level() > 0) { ob_end_clean(); }
}

set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    // === RUTAS ROBUSTAS (sin barras “a mano”) ===
    // Desde /admempleados/controlador → subir dos niveles a la raíz del proyecto
    $PROJECT_ROOT = dirname(__DIR__, 2);                // .../Bakersoft
    $MODULE_DIR   = dirname(__DIR__);                   // .../Bakersoft/admempleados

    // OJO: el archivo de conexión es bd.php (no db.php)
    $pathBd      = $PROJECT_ROOT . DIRECTORY_SEPARATOR . 'config'  . DIRECTORY_SEPARATOR . 'bd.php';
    $pathModelo  = $MODULE_DIR   . DIRECTORY_SEPARATOR . 'modelo'  . DIRECTORY_SEPARATOR . 'modeloadmempleado.php';

    if (!is_file($pathBd))     { throw new RuntimeException("No se encontró config/bd.php en: {$pathBd}"); }
    if (!is_file($pathModelo)) { throw new RuntimeException("No se encontró modelo/modeloadmempleado.php en: {$pathModelo}"); }

    require_once $pathBd;      // define class bd
    require_once $pathModelo;  // define class ModeloAdmEmpleado

    if (!class_exists('bd')) {
        throw new RuntimeException('No se encontró la clase bd en config/bd.php');
    }

    // Conexión mediante tu clase bd
    $bd  = new bd();
    $pdo = $bd->conexion();
    if (!$pdo instanceof PDO) {
        throw new RuntimeException('No se pudo obtener conexión PDO desde la clase bd.');
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $pdo->exec("SET NAMES utf8mb4");

    if (!class_exists('ModeloAdmEmpleado')) {
        throw new RuntimeException('No existe la clase ModeloAdmEmpleado (revisar include del modelo).');
    }

    // Leer y validar ID
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'ID inválido']);
        exit;
    }

    // Obtener detalle
    $modelo = new ModeloAdmEmpleado($pdo);
    if (!method_exists($modelo, 'obtenerEmpleadoPorId')) {
        throw new RuntimeException('Falta el método obtenerEmpleadoPorId en el modelo.');
    }

    $emp = $modelo->obtenerEmpleadoPorId($id);
    if (!$emp) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Empleado no encontrado']);
        exit;
    }

    echo json_encode(['ok' => true, 'data' => $emp]);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok'    => false,
        'error' => 'Error al obtener empleado: ' . $e->getMessage()
    ]);
    exit;
}
