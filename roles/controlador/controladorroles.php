<?php
class controladorRoles{
    private $modelo;
    public function __construct(){
        require_once("../modelo/modeloroles.php");
        $this->modelo = new modeloRoles();

    }

    public function mostrarTodos(){
        return ($this->modelo->listarTodos() ? $this->modelo->listarTodos() : false);
    }
}