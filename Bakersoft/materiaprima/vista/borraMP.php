<?php
    require_once("../controlador/controladorMP.php");
    $obj = new controladorMP();

    #var_dump($_POST);
    $obj->borrar($_POST['borrarID']);
    ?>