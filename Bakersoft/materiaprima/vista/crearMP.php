<?php
    require_once("../controlador/controladorMP.php");
    $obj = new controladorMP();

    #var_dump($_POST);
   if(isset($_POST['nombre'])){
        $obj->guardar($_POST['nombre'],$_POST['stockminimo'],$_POST['stockactual']);
    }
?>