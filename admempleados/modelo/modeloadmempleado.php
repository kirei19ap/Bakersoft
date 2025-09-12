<?php
class ModeloAdmEmpleado {
    private $PDO;

    public function __construct(){
        require_once(__DIR__ . "/../../config/bd.php");
        $con = new bd();
        $this->PDO = $con->conexion();
    }

    public function listarTodos(){
        $sql = "SELECT * FROM empleados ORDER BY id_empleado ASC";
        return $this->PDO->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtener($id){
        $sql = "SELECT * FROM empleados WHERE id_empleado = :id";
        $st  = $this->PDO->prepare($sql);
        $st->bindParam(":id", $id, PDO::PARAM_INT);
        $st->execute();
        return $st->fetch(PDO::FETCH_ASSOC);
    }

    public function insertar($nombre,$apellido,$dni,$email,$telefono,$direccion,$provincia,$localidad,$fecha_ingreso,$puesto,$estado,$usuario_id){
        $sql = "INSERT INTO empleados
                (nombre,apellido,dni,email,telefono,direccion,provincia,localidad,fecha_ingreso,puesto,estado,usuario_id)
                VALUES
                (:nombre,:apellido,:dni,:email,:telefono,:direccion,:provincia,:localidad,:fecha_ingreso,:puesto,:estado,:usuario_id)";
        $st = $this->PDO->prepare($sql);
        $st->bindValue(":nombre", $nombre);
        $st->bindValue(":apellido", $apellido);
        $st->bindValue(":dni", $dni);
        $st->bindValue(":email", $email ?: null, PDO::PARAM_NULL|PDO::PARAM_STR);
        $st->bindValue(":telefono", $telefono ?: null, PDO::PARAM_NULL|PDO::PARAM_STR);
        $st->bindValue(":direccion", $direccion ?: null, PDO::PARAM_NULL|PDO::PARAM_STR);
        $st->bindValue(":provincia", (int)$provincia, PDO::PARAM_INT);
        $st->bindValue(":localidad", (int)$localidad, PDO::PARAM_INT);
        $st->bindValue(":fecha_ingreso", $fecha_ingreso);
        $st->bindValue(":puesto", $puesto);
        $st->bindValue(":estado", $estado);
        $st->bindValue(":usuario_id", $usuario_id ?: null, PDO::PARAM_NULL|PDO::PARAM_INT);
        return $st->execute() ? $this->PDO->lastInsertId() : false;
    }
    

    public function update($id,$nombre,$apellido,$dni,$email,$telefono,$direccion,$provincia,$localidad,$fecha_ingreso,$puesto,$estado,$usuario_id){
        $sql = "UPDATE empleados
                   SET nombre=:nombre, apellido=:apellido, dni=:dni, email=:email, telefono=:telefono,
                       direccion=:direccion, provincia=:provincia, localidad=:localidad,
                       fecha_ingreso=:fecha_ingreso, puesto=:puesto, estado=:estado, usuario_id=:usuario_id
                 WHERE id_empleado=:id";
        $st = $this->PDO->prepare($sql);
        $st->bindValue(":nombre", $nombre);
        $st->bindValue(":apellido", $apellido);
        $st->bindValue(":dni", $dni);
        $st->bindValue(":email", $email ?: null, PDO::PARAM_NULL|PDO::PARAM_STR);
        $st->bindValue(":telefono", $telefono ?: null, PDO::PARAM_NULL|PDO::PARAM_STR);
        $st->bindValue(":direccion", $direccion ?: null, PDO::PARAM_NULL|PDO::PARAM_STR);
        $st->bindValue(":provincia", (int)$provincia, PDO::PARAM_INT);
        $st->bindValue(":localidad", (int)$localidad, PDO::PARAM_INT);
        $st->bindValue(":fecha_ingreso", $fecha_ingreso);
        $st->bindValue(":puesto", $puesto);
        $st->bindValue(":estado", $estado);
        $st->bindValue(":usuario_id", $usuario_id ?: null, PDO::PARAM_NULL|PDO::PARAM_INT);
        $st->bindValue(":id", $id, PDO::PARAM_INT);
        return $st->execute() ? $id : false;
    }
    

    public function eliminar($id){
        $sql = "DELETE FROM empleados WHERE id_empleado = :id";
        $st  = $this->PDO->prepare($sql);
        $st->bindParam(":id", $id, PDO::PARAM_INT);
        return $st->execute();
    }
}
