<?php

class modeloProveedor {
    private $PDO;
    public function __construct(){
        require_once("../../config/bd.php");
        $con = new bd();
        $this->PDO = $con->conexion();
    }
    public function insertar($nombre, $direccion, $email, $telefono){

        $consulta = $this->PDO->prepare("INSERT INTO proveedor (`id_proveedor`, `nombre`, `direccion`, `email`, `telefono`) VALUES (NULL, '$nombre', '$direccion', '$email', '$telefono')");      

        return ($consulta->execute()) ? $this->PDO->lastInsertId() : false;

    }

    public function update($id, $nombre, $direccion, $email, $telefono){
        $consulta = $this->PDO->prepare("UPDATE proveedor SET nombre= :nombre, direccion = :direccion, email = :email, telefono = :telefono WHERE id_proveedor = :id");
        $consulta->bindParam(":nombre",$nombre);
        $consulta->bindParam(":direccion",$direccion);
        $consulta->bindParam(":email",$email);
        $consulta->bindParam(":telefono",$telefono);
        $consulta->bindParam(":id",$id);

        return ($consulta->execute()) ? $id : false;
        #return $consulta;
    }

    public function delete ($id){
        $consulta = $this->PDO->prepare("DELETE FROM proveedor WHERE id_proveedor = :id");
        $consulta->bindParam(":id",$id);
        return ($consulta->execute()) ? true : false ;
    }

    public function listarTodos(){
        $consulta = $this->PDO->prepare("SELECT * FROM proveedor");
        return ($consulta->execute()) ? $consulta->fetchAll() : false;
    }

    public function consultarProveedor($nombre){
        $consulta = $this->PDO->prepare("SELECT * FROM proveedor WHERE nombre = :nombre");
        $consulta->bindParam(":nombre",$nombre);
        $consulta->execute();
        if($consulta->rowCount() > 0){
            return true;
        }else{
            return false;
        }
    }
}




?>