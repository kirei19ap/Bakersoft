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
   if(isset($_POST['nombre'])){
            $obj->guardar($_POST['nombre'],$_POST['unidad_medida'],$_POST['stockminimo'],$_POST['stockactual'],$_POST['proveedor']);

    }

?>