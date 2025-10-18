<?php
header('Content-Type: application/json; charset=UTF-8');
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once(__DIR__."/../modelo/modeloadmempleado.php");

$cuil = isset($_GET['cuil']) ? preg_replace('/\D+/', '', $_GET['cuil']) : '';
$excluir = isset($_GET['excluir']) ? (int)$_GET['excluir'] : null;

$out = ['existe' => false];
try {
    $m = new ModeloAdmEmpleado();
    if ($cuil !== '') $out['existe'] = $m->existeCuil($cuil, $excluir);
} catch (Throwable $e) { }
echo json_encode($out);
?>