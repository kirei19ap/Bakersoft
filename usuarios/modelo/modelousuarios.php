<?php

class modeloUsuario{
    private $PDO;
    public function __construct(){
        require_once("../../config/bd.php");
        $con = new bd();
        $this->PDO = $con->conexion();
    }
    public function listarTodos(){
        $consulta = $this->PDO->prepare("SELECT id, usuario, nomyapellido, rol, fecha_creacion, estado
FROM usuarios
WHERE eliminado = 0
ORDER BY id ASC");
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
        $consulta = $this->PDO->prepare("UPDATE usuarios SET eliminado=1, estado='Inactivo', fecha_baja = NOW() WHERE id=:id");
        $consulta->bindParam(":id",$id_usuario);
        return ($consulta->execute()) ? true : false ;
    }

}