<?php
session_start();
// Verificar si el usuario está logueado
if (!isset($_SESSION['user'])) {
     //Si no está logueado, redirigir al login
    header('Location: ../../index.php');
    exit();
}

require_once(__DIR__."/../controlador/controladoradmempleado.php");
$obj = new ControladorAdmEmpleado();
$id = (int)($_POST['id_empleado'] ?? 0);
if ($id > 0) {
    $ok = $obj->toggle($id);
    if ($ok) {
        $_SESSION['flash_success'] = "Estado actualizado correctamente.";
    } else {
        $_SESSION['flash_error'] = "No se pudo actualizar el estado del empleado.";
    }
}
header("Location: index.php");
?>