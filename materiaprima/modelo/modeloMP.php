<?php

class modeloMP
{
    private $PDO;
    public function __construct()
    {
        require_once("../../config/bd.php");
        $con = new bd();
        $this->PDO = $con->conexion();
    }
    public function insertar($nombre, $unidad_medida, $stockminimo, $stockactual, $proveedor, $idCatMP, $es_perecedero, $fecha_vencimiento)
    {

        $consulta = $this->PDO->prepare("INSERT INTO materiaprima (`id`, `nombre`, `unidad_medida`, `stockminimo`, `stockactual`, `proveedor`, `estado`, `categoriaMP`, `es_perecedero`, `fecha_vencimiento`) VALUES (NULL, '$nombre', '$unidad_medida', '$stockminimo', '$stockactual', '$proveedor', 'activo', '$idCatMP', '$es_perecedero', '$fecha_vencimiento')");

        return ($consulta->execute()) ? $this->PDO->lastInsertId() : false;
    }

    // public function update($id, $nombre, $stockminimo, $stockactual, $id_proveedor){
    //     $consulta = $this->PDO->prepare("UPDATE materiaprima SET nombre= :nombre, stockminimo = :stockminimo, stockactual = :stockactual, proveedor = :id_proveedor WHERE id = :id");
    //     $consulta->bindParam(":nombre",$nombre);
    //     $consulta->bindParam(":stockminimo",$stockminimo);
    //     $consulta->bindParam(":stockactual",$stockactual);
    //     $consulta->bindParam(":id",$id);
    //     $consulta->bindParam(":id_proveedor",$id_proveedor);

    //     return ($consulta->execute()) ? $id : false;
    //     #return $consulta;
    // }

    public function update($id, $nombre, $stockminimo, $stockactual, $id_proveedor, $no_perecedero, $fecha_vencimiento)
    {
        $sql = "UPDATE materiaprima
            SET nombre = :nombre,
                stockminimo = :stockminimo,
                stockactual = :stockactual,
                proveedor = :proveedor,
                es_perecedero = :np,
                fecha_vencimiento = :fv
            WHERE id = :id";
        $consulta = $this->PDO->prepare($sql);
        $consulta->bindParam(':nombre', $nombre);
        $consulta->bindParam(':stockminimo', $stockminimo, PDO::PARAM_INT);
        $consulta->bindParam(':stockactual', $stockactual, PDO::PARAM_INT);

        if ($id_proveedor === null || $id_proveedor === '' || $id_proveedor == -1) {
            $consulta->bindValue(':proveedor', null, PDO::PARAM_NULL);
        } else {
            $consulta->bindValue(':proveedor', $id_proveedor, PDO::PARAM_INT);
        }

        $consulta->bindValue(':np', $no_perecedero, PDO::PARAM_INT);

        $consulta->bindValue(':fv', $fecha_vencimiento ?: null, PDO::PARAM_STR);

        // if ((int)$no_perecedero === 0) {
        //     $consulta->bindValue(':fv', null, PDO::PARAM_NULL);
        // } else {
        //     $consulta->bindValue(':fv', $fecha_vencimiento ?: null, PDO::PARAM_STR);
        // }

        $consulta->bindParam(':id', $id, PDO::PARAM_INT);
        return ($consulta->execute()) ? $id : false;
    }

    public function delete($id)
    {
        $consulta = $this->PDO->prepare("UPDATE materiaprima SET estado = :estado WHERE id = :id");
        $consulta->bindParam(":id", $id);
        $consulta->bindValue(":estado", "inactivo");
        return ($consulta->execute()) ? true : false;
    }

    public function listarTodos()
    {
        $consulta = $this->PDO->prepare("SELECT * FROM materiaprima mp WHERE estado = :estado");
        $consulta->bindValue(":estado", "activo");
        return ($consulta->execute()) ? $consulta->fetchAll() : false;
    }

    public function consultarMP($nombre)
    {
        $consulta = $this->PDO->prepare("SELECT * FROM materiaprima WHERE nombre = :nombre");
        $consulta->bindValue(":nombre", $nombre);
        $consulta->execute();
        if ($consulta->rowCount() > 0) {
            return $consulta->fetch();
        } else {
            return false;
        }
    }

    public function reactivarMP($idMP, $unidad_medida, $stockminimo, $stockactual, $proveedor, $es_perecedero, $fecha_vencimiento)
    {
        $consulta = $this->PDO->prepare("UPDATE materiaprima SET unidad_medida = :unidad, stockminimo = :stockmin, stockactual = :stockactual, proveedor = :proveedor, estado = 'activo', es_perecedero = :es_perecedero, fecha_vencimiento = :fecha_vencimiento WHERE id = :id");
        $consulta->bindValue(":unidad", $unidad_medida);
        $consulta->bindValue(":stockmin", $stockminimo);
        $consulta->bindValue(":stockactual", $stockactual);
        $consulta->bindValue(":proveedor", $proveedor);
        $consulta->bindValue(":id", $idMP);
        $consulta->bindValue(":es_perecedero", $es_perecedero);
        $consulta->bindValue(":fecha_vencimiento", $fecha_vencimiento);
        return $consulta->execute();
    }

    public function traerProveedores()
    {
        $consulta = $this->PDO->prepare("SELECT * FROM proveedor WHERE estado = :estado");
        $consulta->bindValue(":estado", "Activo");
        return ($consulta->execute()) ? $consulta->fetchAll() : false;
    }

    public function traeProveedor($id)
    {
        $consulta = $this->PDO->prepare("SELECT * FROM proveedor WHERE id_proveedor = :id");
        $consulta->bindParam(":id", $id);
        return ($consulta->execute()) ? $consulta->fetchAll() : false;
    }

    public function traerCategorias()
    {
        $consulta = $this->PDO->prepare("SELECT * from categoriaMP");
        return ($consulta->execute()) ? $consulta->fetchAll() : false;
    }

    public function obtenerMPBajoStock(): array
    {
        $sql = "SELECT id, nombre, lote, unidad_medida, stockactual, stockminimo
            FROM materiaprima
            WHERE estado = :estado
              AND stockactual < stockminimo
            ORDER BY stockactual ASC, nombre ASC";

        $consulta = $this->PDO->prepare($sql);
        $consulta->bindValue(":estado", "activo");

        return ($consulta->execute()) ? ($consulta->fetchAll() ?: []) : [];
    }

    public function obtenerMPEnMinimo(): array
    {
        $sql = "SELECT id, nombre, lote, unidad_medida, stockactual, stockminimo
            FROM materiaprima
            WHERE estado = :estado
              AND stockactual = stockminimo
            ORDER BY nombre ASC";

        $consulta = $this->PDO->prepare($sql);
        $consulta->bindValue(":estado", "activo");

        return ($consulta->execute()) ? ($consulta->fetchAll() ?: []) : [];
    }
}
