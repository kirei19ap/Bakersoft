<?php

class modeloMP {
    private $PDO;
    public function __construct(){
        require_once("../../config/bd.php");
        $con = new bd();
        $this->PDO = $con->conexion();
    }
    public function insertar($nombre, $unidad_medida, $stockminimo, $stockactual, $proveedor){

        $consulta = $this->PDO->prepare("INSERT INTO materiaprima (`id`, `nombre`, `unidad_medida`, `stockminimo`, `stockactual`, `proveedor`) VALUES (NULL, '$nombre', '$unidad_medida', '$stockminimo', '$stockactual', '$proveedor')");      

        return ($consulta->execute()) ? $this->PDO->lastInsertId() : false;

    }

    public function update($id, $nombre, $stockminimo, $stockactual, $id_proveedor){
        $consulta = $this->PDO->prepare("UPDATE materiaprima SET nombre= :nombre, stockminimo = :stockminimo, stockactual = :stockactual, proveedor = :id_proveedor WHERE id = :id");
        $consulta->bindParam(":nombre",$nombre);
        $consulta->bindParam(":stockminimo",$stockminimo);
        $consulta->bindParam(":stockactual",$stockactual);
        $consulta->bindParam(":id",$id);
        $consulta->bindParam(":id_proveedor",$id_proveedor);

        return ($consulta->execute()) ? $id : false;
        #return $consulta;
    }

    public function delete ($id){
        $consulta = $this->PDO->prepare("DELETE FROM materiaprima WHERE id = :id");
        $consulta->bindParam(":id",$id);
        return ($consulta->execute()) ? true : false ;
    }

    public function listarTodos(){
        $consulta = $this->PDO->prepare("SELECT * FROM materiaprima");
        return ($consulta->execute()) ? $consulta->fetchAll() : false;
    }

    public function consultarMP($nombre){
        $consulta = $this->PDO->prepare("SELECT * FROM materiaprima WHERE nombre = :nombre");
        $consulta->bindParam(":nombre",$nombre);
        $consulta->execute();
        if($consulta->rowCount() > 0){
            return true;
        }else{
            return false;
        }
    }

    public function traerProveedores(){
        $consulta = $this->PDO->prepare("SELECT * FROM proveedor");
        return ($consulta->execute()) ? $consulta->fetchAll() : false;
    }

    public function traeProveedor($id){
        $consulta = $this->PDO->prepare("SELECT * FROM proveedor WHERE id_proveedor = :id");
        $consulta->bindParam(":id",$id);
        return ($consulta->execute()) ? $consulta->fetchAll() : false;
    }
}

?>