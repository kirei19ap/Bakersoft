<?php
    require_once("../controlador/controladorProveedores.php");
    $obj = new controladorProveedor();

   #var_dump($_POST);
      $res = $obj->actualizar($_POST['editidProve'],$_POST['editnombreProve'],$_POST['editcalleprove'],$_POST['editalturaprove'],$_POST['editprovProve'],$_POST['editlocprove'],$_POST['editemailProve'],$_POST['edittelefonoProve']);

?>