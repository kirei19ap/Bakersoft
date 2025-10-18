<?php
header('Content-Type: application/json; charset=UTF-8');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once(__DIR__."/../modelo/modeloadmempleado.php");

$legajo = isset($_GET['legajo']) ? trim($_GET['legajo']) : '';
$excluir = isset($_GET['excluir']) ? (int)$_GET['excluir'] : null;

$out = ['existe' => false];
try {
    $m = new ModeloAdmEmpleado();
    if ($legajo !== '') $out['existe'] = $m->existeLegajo($legajo, $excluir);
} catch (Throwable $e) { }
echo json_encode($out);
?>