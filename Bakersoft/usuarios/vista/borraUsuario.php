<?php
    session_start();
    require_once("../controlador/controladorusuarios.php");
    $obj = new controladorUsuario();

    #var_dump($_POST);
    $obj->borrarUsuario($_POST['borrarUsuarioId']);
    ?>