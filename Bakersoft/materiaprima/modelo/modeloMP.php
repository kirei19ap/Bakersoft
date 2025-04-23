<?php

class modeloMP {
    private $PDO;
    public function __construct(){
        require_once("../../config/bd.php");
        $con = new bd();
        $this->PDO = $con->conexion();
    }
    public function insertar($nombre, $stockminimo, $stockactual){

        $consulta = $this->PDO->prepare("INSERT INTO materiaprima (`id`, `nombre`, `stockminimo`, `stockactual`) VALUES (NULL, '$nombre', '$stockminimo', '$stockactual')");      

        return ($consulta->execute()) ? $this->PDO->lastInsertId() : false;

    }

    public function update($id, $nombre, $stockminimo, $stockactual){
        $consulta = $this->PDO->prepare("UPDATE materiaprima SET nombre= :nombre, stockminimo = :stockminimo, stockactual = :stockactual WHERE id = :id");
        $consulta->bindParam(":nombre",$nombre);
        $consulta->bindParam(":stockminimo",$stockminimo);
        $consulta->bindParam(":stockactual",$stockactual);
        $consulta->bindParam(":id",$id);

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
}




?>