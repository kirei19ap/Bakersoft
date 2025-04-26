<?php
    require_once("../controlador/controladorMP.php");
    $obj = new controladorMP();

   #var_dump($_POST);
       $res = $obj->actualizar($_POST['editid'],$_POST['editnombre'],$_POST['editstockminimo'],$_POST['editstockactual']);

?>