<?php
session_start();
require_once("../controlador/controladorPedido.php");
$ctrl = new controladorPedido();

try {
    if (!isset($_SESSION['pedido']) || empty($_SESSION['pedido'])) {
        echo "No hay materias primas para guardar.";
        exit;
    }

    $idProveedor = $_SESSION['idprove'];
    $items = $_SESSION['pedido'];

    if ($ctrl->guardarPedido($idProveedor, $items)) {
        // Limpiar sesión después de guardar
        unset($_SESSION['pedido']);
        unset($_SESSION['idprove']);
        echo "El pedido fue generado exitosamente.";
    } else {
        echo "Hubo un error al generar el pedido.";
    }

} catch (Exception $e) {
    echo "Error al generar el pedido: " . $e->getMessage();
}