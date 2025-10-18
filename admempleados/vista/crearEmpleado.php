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
var_dump($POST);
$res = $obj->guardar($_POST);
if ($res === false && !empty($_SESSION['flash_error'])) {
    header("Location: index.php");
    exit;
}
$_SESSION['flash_success'] = "Empleado creado correctamente.";
header("Location: index.php");
