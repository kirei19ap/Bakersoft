<?php

class controladorMP{
    private $modelo;
    public function __construct(){
        require_once("../modelo/modeloMP.php");
        $this->modelo = new modeloMP();

    }
    public function guardar($nombre, $stockminimo, $stockactual){
        $id = $this->modelo->insertar($nombre, $stockminimo, $stockactual);
        return ($id != false) ? header("Location:index.php") : header("Location:error.php");
    }
    public function actualizar($id, $nombre, $stockminimo, $stockactual){
        return ($this->modelo->update($id, $nombre, $stockminimo, $stockactual) != false) ? header("Location:index.php") : header("Location:error.php");
    }

    public function borrar($id){
        return ($this->modelo->delete($id)) ? header("Location:index.php") : header("Location:error.php") ;
    }

    public function mostrarTodos(){
        return ($this->modelo->listarTodos() ? $this->modelo->listarTodos() : false);
    }
}

?>