<?php
class controladorReportes{
    private $modelo;
    public function __construct(){
        require_once("../modelo/modeloReportes.php");
        $this->modelo = new modeloReportes();

    }
    public function datosReportes(){
        return $this->modelo->datosReportes();
    }
    
    public function datosGraficos(){
        return $this->modelo->datosGraficos();
    }

    public function traerMPListado(){
        return $this->modelo->traerMPListado();
    }

    public function traerProveedoresListado(){
        return $this->modelo->traerProveedoresListado();
    }
}

?>