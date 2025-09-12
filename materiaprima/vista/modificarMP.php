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

$no_p = isset($_POST['no_perecedero']) ? 1 : 0;
$fv   = $_POST['fecha_vencimiento'] ?? null;
#var_dump($_POST);
$res = $obj->actualizar($_POST['editid'], $_POST['editnombre'], $_POST['editstockminimo'], $_POST['editstockactual'], $_POST['editMPproveedor'], $no_p, $fv);
