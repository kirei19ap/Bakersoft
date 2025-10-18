<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

try {
    $ROOT   = dirname(__DIR__, 2);
    $MODULE = dirname(__DIR__);

    require_once ('../../config/bd.php');
    require_once ('../modelo/modeloadmempleado.php');

    $bd  = new bd();
    $pdo = $bd->conexion();
    if (!$pdo instanceof PDO) { throw new RuntimeException('Sin PDO'); }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4");

    $modelo = new ModeloAdmEmpleado($pdo);
    $rows   = $modelo->getEstadosCiviles();

    echo json_encode(['ok'=>true,'data'=>$rows], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false, 'error'=>$e->getMessage()]);
}
