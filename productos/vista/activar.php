<?php
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../controlador/controladorProductos.php';
$ctrl = new controladorProducto();

$id = isset($_POST['idProducto']) ? (int)$_POST['idProducto'] : 0;

if ($id <= 0) {
    echo json_encode(['ok' => false, 'error' => 'ID inválido']);
    exit;
}

$res = $ctrl->activar($id);
echo json_encode($res);
