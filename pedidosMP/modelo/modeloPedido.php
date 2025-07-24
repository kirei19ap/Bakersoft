<?php

class modeloPedido {
    private $PDO;
    public function __construct(){
        require_once("../../config/bd.php");
        $con = new bd();
        $this->PDO = $con->conexion();
    }
    
    public function traerProveedores(){
        $consulta = $this->PDO->prepare("SELECT * FROM proveedor WHERE estado = :estado");
        $consulta->bindValue(":estado", "Activo");
        return ($consulta->execute()) ? $consulta->fetchAll() : false;
    }

    public function traerMPbyProveed($id_prove){
        $consulta = $this->PDO->prepare("SELECT * FROM materiaprima WHERE proveedor = :id_prove AND estado = 'activo'");
        $consulta->bindParam(":id_prove",$id_prove);
        return ($consulta->execute()) ? $consulta->fetchAll() : false;
    }

    public function traerNroUltimoPedido(){
        $consulta = $this->PDO->prepare("SELECT idPedido FROM pedidomp ORDER BY idPedido DESC LIMIT 1");
        $res = $consulta->execute();
        if ($res && $row = $consulta->fetch(PDO::FETCH_ASSOC)) {
            return $row; // Devuelve el array
        } else {
            return null;
        }
    }

    public function insertarPedido($idProveedor) {
        $stmt = $this->PDO->prepare("INSERT INTO `pedidomp` (`idPedido`, `idProveedor`, `fechaPedido`, `Estado`) VALUES (NULL, '$idProveedor', NOW(), '10')");
        if ($stmt->execute()) {
            return $this->PDO->lastInsertId();
        }
        return false;
    }
    
    public function insertarDetallePedido($idPedido, $idMateriaPrima, $cantidad) {
        $stmt = $this->PDO->prepare("INSERT INTO `detallepedido` (`idDetallePedido`, `idPedido`, `idMP`, `cantidad`) VALUES (NULL, $idPedido, $idMateriaPrima, $cantidad)");
        return $stmt->execute();
    }

    public function pedidosTodos(){
        $consulta = $this->PDO->prepare("SELECT p.idPedido, prov.nombre AS proveedor, p.fechaPedido, e.descEstado AS estado FROM pedidomp p JOIN proveedor prov ON p.idProveedor = prov.id_proveedor JOIN estadospedidos e ON p.Estado = e.codEstado;");
        return ($consulta->execute()) ? $consulta->fetchAll() : false;
    }

    public function detallePedido($idPedido){
        $consulta = $this->PDO->prepare("SELECT mp.nombre AS materiaprima, dp.cantidad, mp.unidad_medida, p.nombre AS proveedor FROM detallepedido dp JOIN materiaprima mp ON dp.idMP = mp.id JOIN pedidomp pe ON dp.idPedido = pe.idPedido JOIN proveedor p ON pe.idProveedor = p.id_proveedor WHERE pe.idPedido = ?;");
        $consulta->execute([$idPedido]);
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    public function cancelarPedido($idPedido){
        $consulta = "UPDATE pedidomp SET estado = 60 WHERE idPedido = :id";
        $stmt = $this->PDO->prepare($consulta);
        return $stmt->execute([':id' => $idPedido]);
    }

    public function traerProveedorporNombre($nombreProveedor){
        $consulta = $this->PDO->prepare("SELECT * FROM proveedor WHERE nombre = :nombre_prove");
        $consulta->bindParam(":nombre_prove",$nombreProveedor);
        return ($consulta->execute()) ? $consulta->fetch(PDO::FETCH_ASSOC) : false;
    }
}