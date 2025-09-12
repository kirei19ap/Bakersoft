<?php
 session_start();
 // Verificar si el usuario está logueado
if (!isset($_SESSION['user'])) {
 // Si no está logueado, redirigir al login
 header('Location: ../../index.php');
 exit();
}
 require_once("../controlador/controladorusuarios.php");
 $obj = new controladorUsuario();

#var_dump($_POST);

 if(isset($_POST['usuario'])){
   $respuesta = $obj->altaUsuario($_POST['usuario'],$_POST['nombre_usu'],$_POST['apellido_usu'],$_POST['rol'],$_POST['contrasena']);
}
?>