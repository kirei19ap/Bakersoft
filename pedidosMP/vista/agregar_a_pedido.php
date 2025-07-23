<?php
session_start();

// Validar y sanitizar entrada
$id = $_POST['id'] ?? null;
$nombre = $_POST['nombre'] ?? '';
$cantidad = $_POST['cantidad'] ?? 0;
$idprove = $_POST['idprove'] ?? null;

// Validación básica
if (!$id || !$idprove || $cantidad <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
    exit;
}

// Inicializar arrays si no existen
if (!isset($_SESSION['pedido'])) {
    $_SESSION['pedido'] = [];
}

// Guardar el ID del proveedor (si no está guardado aún o si coincide con el anterior)
if (!isset($_SESSION['idprove'])) {
    $_SESSION['idprove'] = $idprove;
} elseif ($_SESSION['idprove'] != $idprove) {
    echo json_encode(['status' => 'error', 'message' => 'No se puede mezclar proveedores en el mismo pedido.']);
    exit;
}

// Agregar ítem al pedido
$_SESSION['pedido'][] = [
    'id' => $id,
    'nombre' => $nombre,
    'cantidad' => $cantidad
];

echo json_encode(['status' => 'ok']);