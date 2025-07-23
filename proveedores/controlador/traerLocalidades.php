<?php

class traerLocalidades{
    private $modelo;
    public function __construct(){
        require_once("../modelo/modeloProveedores.php");
        $this->modelo = new modeloProveedor();

    }

    public function mostrarLocalidades($id_provincia){
       return ($this->modelo->listarLocalidades($id_provincia) ? $this->modelo->listarLocalidades($id_provincia) : false);
    }



}

$Localidades = new traerLocalidades();
$traerLocalidades = $Localidades -> mostrarLocalidades($_GET['id_provincia']);
header('Content-Type: application/json');
echo json_encode($traerLocalidades);