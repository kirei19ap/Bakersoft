<?php

class modeloRoles{
    private $PDO;
    public function __construct(){
        require_once("../../config/bd.php");
        $con = new bd();
        $this->PDO = $con->conexion();
    }
    public function listarTodos(){
        $consulta = $this->PDO->prepare("SELECT * FROM roles");
        return ($consulta->execute()) ? $consulta->fetchAll() : false;
    }
}