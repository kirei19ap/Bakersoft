<?php

class modeloMP {
    private $PDO;
    public function __construct(){
        require_once("../../config/bd.php");
        $con = new bd();
        $this->PDO = $con->conexion();
    }
    public function insertar($nombre, $unidad_medida, $stockminimo, $stockactual, $proveedor){

        $consulta = $this->PDO->prepare("INSERT INTO materiaprima (`id`, `nombre`, `unidad_medida`, `stockminimo`, `stockactual`, `proveedor`, `estado`) VALUES (NULL, '$nombre', '$unidad_medida', '$stockminimo', '$stockactual', '$proveedor', 'activo')");      

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
        $consulta = $this->PDO->prepare("UPDATE materiaprima SET estado = :estado WHERE id = :id");
        $consulta->bindParam(":id",$id);
        $consulta->bindValue(":estado", "inactivo");
        return ($consulta->execute()) ? true : false ;
    }

    public function listarTodos(){
        $consulta = $this->PDO->prepare("SELECT * FROM materiaprima WHERE estado = :estado");
        $consulta->bindValue(":estado", "activo");
        return ($consulta->execute()) ? $consulta->fetchAll() : false;
    }

    public function consultarMP($nombre){
        $consulta = $this->PDO->prepare("SELECT * FROM materiaprima WHERE nombre = :nombre");
        $consulta->bindValue(":nombre",$nombre);
        $consulta->execute();
        if($consulta->rowCount() > 0){
            return $consulta->fetch();
        }else{
            return false;
        }
    }

    public function reactivarMP($idMP, $unidad_medida, $stockminimo, $stockactual, $proveedor){
        $consulta = $this->PDO->prepare("UPDATE materiaprima SET unidad_medida = :unidad, stockminimo = :stockmin, stockactual = :stockactual, proveedor = :proveedor, estado = 'activo' WHERE id = :id");
        $consulta->bindValue(":unidad", $unidad_medida);
        $consulta->bindValue(":stockmin", $stockminimo);
        $consulta->bindValue(":stockactual", $stockactual);
        $consulta->bindValue(":proveedor", $proveedor);
        $consulta->bindValue(":id", $idMP);
        return $consulta->execute();
    }

    public function traerProveedores(){
        $consulta = $this->PDO->prepare("SELECT * FROM proveedor WHERE estado = :estado");
        $consulta->bindValue(":estado", "Activo");
        return ($consulta->execute()) ? $consulta->fetchAll() : false;
    }

    public function traeProveedor($id){
        $consulta = $this->PDO->prepare("SELECT * FROM proveedor WHERE id_proveedor = :id");
        $consulta->bindParam(":id",$id);
        return ($consulta->execute()) ? $consulta->fetchAll() : false;
    }
}

?>