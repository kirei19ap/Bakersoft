<?php

class modeloBuscador {
    private $PDO;
    public function __construct(){
        require_once("../../config/bd.php");
        $con = new bd();
        $this->PDO = $con->conexion();
    }
    
    public function traerProveedores(){
        $consulta = $this->PDO->prepare("SELECT * FROM proveedor");
        return ($consulta->execute()) ? $consulta->fetchAll() : false;
    }

    public function buscar($fecha_desde = null, $fecha_hasta = null, $proveedorId = null, $materiaId = null, $estado = null) {
        $sql = "SELECT DISTINCT 
                p.idPedido, 
                p.fechaPedido, 
                pr.nombre AS proveedor_nombre, 
                e.descEstado AS estado 
                FROM pedidomp p 
                JOIN proveedor pr ON p.idProveedor = pr.id_proveedor 
                JOIN estadospedidos e ON p.Estado = e.codEstado 
                JOIN detallepedido dp ON p.idPedido = dp.idPedido 
                WHERE 1 = 1";

        $params = [];
        $condiciones = [];

        if ($fecha_desde) {
            $sql .= " AND p.fechaPedido >= :fecha_desde";
            $params[':fecha_desde'] = $fecha_desde;
        }
        
        if ($fecha_hasta) {
            $sql .= " AND p.fechaPedido <= :fecha_hasta";
            $params[':fecha_hasta'] = $fecha_hasta;
        }

        if ($proveedorId) {
            $sql .= " AND p.idProveedor = :proveedorId ";
            $params[':proveedorId'] = $proveedorId;
        }

        if (!empty($materiaId)) {
            $sql .= " AND dp.idMP = :materia";
            $params[':materia'] = $materiaId;
        }

        if (!empty($estado)) {
            $sql .= " AND e.codEstado = :estado";
            $params[':estado'] = $estado;
        }

        $sql .= " ORDER BY p.fechaPedido DESC";
        #var_dump($sql);
        $stmt = $this->PDO->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function mpTodas(){
        $consulta = $this->PDO->prepare("SELECT * FROM materiaprima");
        return ($consulta->execute()) ? $consulta->fetchAll() : false;
    }

    public function estadosTodos(){
        $consulta = $this->PDO->prepare("SELECT * FROM estadospedidos");
        return ($consulta->execute()) ? $consulta->fetchAll() : false;
    }

    public function detallePedido($idPedido){
        $consulta = $this->PDO->prepare("SELECT mp.nombre AS materiaprima, dp.cantidad, mp.unidad_medida, p.nombre AS proveedor FROM detallepedido dp JOIN materiaprima mp ON dp.idMP = mp.id JOIN pedidomp pe ON dp.idPedido = pe.idPedido JOIN proveedor p ON pe.idProveedor = p.id_proveedor WHERE pe.idPedido = ?;");
        $consulta->execute([$idPedido]);
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }
    public function traerProveedorporNombre($nombreProveedor){
        $consulta = $this->PDO->prepare("SELECT * FROM proveedor WHERE nombre = :nombre_prove");
        $consulta->bindParam(":nombre_prove",$nombreProveedor);
        return ($consulta->execute()) ? $consulta->fetch(PDO::FETCH_ASSOC) : false;
    }
}

?>