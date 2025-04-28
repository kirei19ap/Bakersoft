<?php
    require_once("../controlador/controladorProveedores.php");
    $obj = new controladorProveedor();

    #var_dump($_POST);
    $obj->borrar($_POST['borrarProveedorId']);
    ?>