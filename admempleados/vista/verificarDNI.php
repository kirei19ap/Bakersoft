<?php
header('Content-Type: application/json; charset=UTF-8');
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once(__DIR__."/../modelo/modeloadmempleado.php");

$dni = isset($_GET['dni']) ? trim($_GET['dni']) : '';
$excluir = isset($_GET['excluir']) ? (int)$_GET['excluir'] : null;

$out = ['existe' => false];

try {
    $m = new ModeloAdmEmpleado();
    if ($dni !== '') {
        $out['existe'] = $m->existeDni($dni, $excluir);
    }
} catch (Throwable $e) {
    // opcional: log
}

echo json_encode($out);
exit;
