<?php
    session_start();
    // Verificar si el usuario está logueado
if (!isset($_SESSION['user'])) {
    // Si no está logueado, redirigir al login
    header('Location: ../../index.php');
    exit();
}
    require_once("../controlador/controladorProveedores.php");
    $obj = new controladorProveedor();

    #var_dump($_POST);
   if(isset($_POST['nombre'])){
        $respuesta = $obj->guardar($_POST['nombre'],$_POST['direccion'],$_POST['email'],$_POST['telefono']);
    }
?>