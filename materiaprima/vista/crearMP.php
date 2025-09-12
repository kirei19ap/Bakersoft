<?php
session_start();
// Verificar si el usuario está logueado
if (!isset($_SESSION['user'])) {
    // Si no está logueado, redirigir al login
    header('Location: ../../index.php');
    exit();
}
require_once("../controlador/controladorMP.php");
$obj = new controladorMP();

#var_dump($_POST);
if (isset($_POST['nombre'])) {
    $es_perecedero = isset($_POST['es_perecedero']) ? 1 : 0;
    $fecha_vencimiento = null;

    if ($es_perecedero) {
        // Validación de formato y que no sea pasado (opcional)
        $fecha_vencimiento = $_POST['fecha_vencimiento'] ?? null;
        if (!$fecha_vencimiento) {
            // devolver error amigable
        }
    }
    $obj->guardar($_POST['nombre'], $_POST['unidad_medida'], $_POST['stockminimo'], $_POST['stockactual'], $_POST['proveedor'], $_POST['categoriaMP'], $es_perecedero, $fecha_vencimiento);
}
