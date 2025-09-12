<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== "Encargado RRHH") { // Encargado RRHH
    header('Location: ../../index.php'); exit;
}

require_once(__DIR__."/../controlador/controladoradmempleado.php");
$obj = new ControladorAdmEmpleado();
$id = (int)($_POST['id_empleado'] ?? 0);
if ($id>0) { $obj->borrar($id); }
header("Location: index.php");
