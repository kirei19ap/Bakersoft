<?php
// admempleados/controlador/estadisticas_empleados.php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors','0'); ini_set('html_errors','0'); error_reporting(E_ALL);
if (function_exists('ob_get_level')) { while (ob_get_level() > 0) { ob_end_clean(); } }

try {
    $ROOT   = dirname(__DIR__, 2); // .../Bakersoft
    $MODULE = dirname(__DIR__);    // .../Bakersoft/admempleados

    require_once $ROOT   . '/config/bd.php';
    require_once $MODULE . '/modelo/modeloadmempleado.php';

    $bd  = new bd();
    $pdo = $bd->conexion();
    if (!$pdo instanceof PDO) throw new RuntimeException('Sin PDO');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $pdo->exec("SET NAMES utf8mb4");

    $modelo = new ModeloAdmEmpleado($pdo);

    if (!method_exists($modelo, 'getEstadisticasEmpleados')) {
        throw new RuntimeException('Falta método getEstadisticasEmpleados en el modelo');
    }

    $stats = $modelo->getEstadisticasEmpleados();

    echo json_encode(['ok'=>true] + $stats);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'error'=>'Error al obtener estadísticas: '.$e->getMessage()]);
    exit;
}
