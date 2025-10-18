<?php
session_start();
require_once("../controlador/controladorMP.php");
$ctrl = new controladorMP();

$nombre = $_GET['nombre'] ?? '';
$lote   = $_GET['lote'] ?? '';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

$existe = false;
if ($nombre !== '' && $lote !== '') {
    $existe = $ctrl->existeNombreLote($nombre, $lote, $id) ? true : false;
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['exists' => $existe]);
?>