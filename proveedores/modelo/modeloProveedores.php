<?php

class modeloProveedor {
    private $PDO;
    public function __construct(){
        require_once("../../config/bd.php");
        $con = new bd();
        $this->PDO = $con->conexion();
    }
    public function insertar($nombre, $calle, $altura, $provincia, $localidad, $email, $telefono){
        
        $consulta = $this->PDO->prepare("INSERT INTO proveedor (`id_proveedor`, `nombre`, `email`, `telefono`, `calle`, `altura`, `provincia`, `localidad`, `Estado`) VALUES (NULL, '$nombre', '$email', '$telefono', '$calle', '$altura', '$provincia', '$localidad', 'Activo')");      

        return ($consulta->execute()) ? $this->PDO->lastInsertId() : false;

    }


    public function update($id, $nombre, $calle, $altura, $provincia, $localidad, $email, $telefono){
        $consulta = $this->PDO->prepare("UPDATE proveedor SET nombre= :nombre, calle = :calle, altura = :altura, email = :email, telefono = :telefono, provincia = :provincia, localidad = :localidad WHERE id_proveedor = :id");
        $consulta->bindParam(":nombre",$nombre);
        $consulta->bindParam(":calle",$calle);
        $consulta->bindParam(":altura",$altura);
        $consulta->bindParam(":provincia",$provincia);
        $consulta->bindParam(":localidad",$localidad);
        $consulta->bindParam(":email",$email);
        $consulta->bindParam(":telefono",$telefono);
        $consulta->bindParam(":id",$id);

        return ($consulta->execute()) ? $id : false;
        #return $consulta;
    }

    public function delete ($id){
        try{
            #$consulta = $this->PDO->prepare("DELETE FROM proveedor WHERE id_proveedor = :id");
            $consulta = $this->PDO->prepare("UPDATE proveedor SET estado = :estado WHERE id_proveedor = :id");
            $consulta->bindValue(":estado", "Inactivo");
            $consulta->bindParam(":id",$id);
            $resultado = $consulta->execute();
            return $resultado;
        }catch (PDOException $e){
            if ($e->getCode() == "23000") {
                // Este código es típico cuando hay un conflicto por clave foránea
                return "ERROR_RELACION";
            } else {
                // Para otros errores de base de datos
                return "ERROR_GENERAL";
            }
        }
        return ($consulta->execute()) ? true : false ;
    }

    public function listarTodos(){
        $consulta = $this->PDO->prepare("SELECT * FROM proveedor WHERE estado = :estado");
        $consulta->bindValue(":estado", "Activo");
        return ($consulta->execute()) ? $consulta->fetchAll() : false;
    }

    public function consultarProveedor($nombre){
        $consulta = $this->PDO->prepare("SELECT * FROM proveedor WHERE nombre = :nombre AND estado = :estado");
        $consulta->bindValue(":estado", "Activo");
        $consulta->bindParam(":nombre",$nombre);
        $consulta->execute();
        if($consulta->rowCount() > 0){
            return true;
        }else{
            return false;
        }
    }

    public function listarProvincias(){
        $consulta = $this->PDO->prepare("SELECT * FROM provincias");
        return ($consulta->execute()) ? $consulta->fetchAll() : false;
    }

    public function listarLocalidades($id_provincia){
        $consulta = $this->PDO->prepare("SELECT * FROM localidades WHERE id_provincia = :id_provincia");
        $consulta->bindParam(":id_provincia",$id_provincia);
        return ($consulta->execute()) ? $consulta->fetchAll() : false;
    }
    
}
?>