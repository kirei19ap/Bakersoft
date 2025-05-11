<?php
class controladorUsuario{
    private $modelo;
    public function __construct(){
        require_once("../modelo/modeloUsuarios.php");
        $this->modelo = new modeloUsuario();

    }

    public function mostrarTodos(){
        return ($this->modelo->listarTodos() ? $this->modelo->listarTodos() : false);
    }

}