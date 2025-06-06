<?php

class modeloUsuario{
    private $PDO;
    public function __construct(){
        require_once("../../config/bd.php");
        $con = new bd();
        $this->PDO = $con->conexion();
    }
    public function listarTodos(){
        $consulta = $this->PDO->prepare("SELECT * FROM usuarios");
        return ($consulta->execute()) ? $consulta->fetchAll() : false;
    }

    public function rolesTodos(){
        $consulta = $this->PDO->prepare("SELECT * FROM roles");
        return ($consulta->execute()) ? $consulta->fetchAll() : false;
    }

    public function crearUsuario($usuario, $nomyapellido, $rol, $contrasena) {
        $hash = md5($contrasena);     
        $sql = "INSERT INTO `usuarios` (`id`, `usuario`, `contrasena`, `nomyapellido`, `rol`) VALUES (NULL, :usuario, :contrasena, :nya, :rol)";
        $stmt = $this->PDO->prepare($sql);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->bindParam(':contrasena', $hash);
        $stmt->bindParam(':nya', $nomyapellido);
        $stmt->bindParam(':rol', $rol);
        return $stmt->execute();
    }

    public function deleteUSR($id_usuario){
        $consulta = $this->PDO->prepare("DELETE FROM usuarios WHERE id = :id");
        $consulta->bindParam(":id",$id_usuario);
        return ($consulta->execute()) ? true : false ;
    }

}