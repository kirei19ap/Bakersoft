<?php

class modeloPedido
{
    private $PDO;

    // Estados para pedidos de materia prima
    const ESTADO_REGISTRADO = 10;   // Registrado / pendiente
    const ESTADO_RECIBIDO   = 50;   // Recibida
    const ESTADO_CANCELADO  = 60;   // Cancelado

    public function __construct()
    {
        require_once("../../config/bd.php");
        $con = new bd();
        $this->PDO = $con->conexion();
    }

    public function traerProveedores()
    {
        $consulta = $this->PDO->prepare("SELECT * FROM proveedor WHERE estado = :estado");
        $consulta->bindValue(":estado", "Activo");
        return ($consulta->execute()) ? $consulta->fetchAll() : false;
    }

    public function traerMPbyProveed($id_prove)
    {
        $consulta = $this->PDO->prepare("SELECT * FROM materiaprima WHERE proveedor = :id_prove AND estado = 'activo'");
        $consulta->bindParam(":id_prove", $id_prove, PDO::PARAM_INT);
        return ($consulta->execute()) ? $consulta->fetchAll() : false;
    }

    public function traerNroUltimoPedido()
    {
        $consulta = $this->PDO->prepare("SELECT idPedido FROM pedidomp ORDER BY idPedido DESC LIMIT 1");
        $res = $consulta->execute();
        if ($res && $row = $consulta->fetch(PDO::FETCH_ASSOC)) {
            return $row; // Devuelve el array
        } else {
            return null;
        }
    }

    public function insertarPedido($idProveedor)
    {
        $stmt = $this->PDO->prepare("
            INSERT INTO pedidomp (idPedido, idProveedor, fechaPedido, fechaRecepcion, usuarioRecepcion, Estado)
            VALUES (NULL, :idProveedor, NOW(), NULL, NULL, :estado)
        ");
        $stmt->bindValue(':idProveedor', $idProveedor, PDO::PARAM_INT);
        $stmt->bindValue(':estado', self::ESTADO_REGISTRADO, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $this->PDO->lastInsertId();
        }
        return false;
    }

    public function insertarDetallePedido($idPedido, $idMateriaPrima, $cantidad)
    {
        $stmt = $this->PDO->prepare("
            INSERT INTO detallepedido (idDetallePedido, idPedido, idMP, cantidad)
            VALUES (NULL, :idPedido, :idMP, :cantidad)
        ");
        $stmt->bindValue(':idPedido', $idPedido, PDO::PARAM_INT);
        $stmt->bindValue(':idMP', $idMateriaPrima, PDO::PARAM_INT);
        $stmt->bindValue(':cantidad', $cantidad, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function pedidosTodos()
    {
        $consulta = $this->PDO->prepare("
            SELECT 
                p.idPedido, 
                prov.nombre AS proveedor, 
                p.fechaPedido, 
                p.fechaRecepcion,
                e.descEstado AS estado
            FROM pedidomp p 
            JOIN proveedor prov ON p.idProveedor = prov.id_proveedor 
            JOIN estadospedidos e ON p.Estado = e.codEstado
        ");
        return ($consulta->execute()) ? $consulta->fetchAll() : false;
    }

    public function detallePedido($idPedido)
    {
        $consulta = $this->PDO->prepare("
        SELECT 
            mp.nombre AS materiaprima,
            dp.cantidad,
            mp.unidad_medida,
            p.nombre AS proveedor,
            pe.Estado AS codEstado,
            e.descEstado AS estadoPedido
        FROM detallepedido dp 
        JOIN materiaprima mp ON dp.idMP = mp.id 
        JOIN pedidomp pe ON dp.idPedido = pe.idPedido 
        JOIN proveedor p ON pe.idProveedor = p.id_proveedor 
        JOIN estadospedidos e ON pe.Estado = e.codEstado
        WHERE pe.idPedido = ?;
    ");
        $consulta->execute([$idPedido]);
        return $consulta->fetchAll(PDO::FETCH_ASSOC);
    }


    public function cancelarPedido($idPedido)
    {
        $consulta = "UPDATE pedidomp SET Estado = :estado WHERE idPedido = :id";
        $stmt = $this->PDO->prepare($consulta);
        return $stmt->execute([
            ':estado' => self::ESTADO_CANCELADO,
            ':id'     => $idPedido
        ]);
    }

    public function traerProveedorporNombre($nombreProveedor)
    {
        $consulta = $this->PDO->prepare("SELECT * FROM proveedor WHERE nombre = :nombre_prove");
        $consulta->bindParam(":nombre_prove", $nombreProveedor);
        return ($consulta->execute()) ? $consulta->fetch(PDO::FETCH_ASSOC) : false;
    }

    /**
     * Marcar un pedido de materia prima como recibido y actualizar stock.
     * $idUsuario: ID del usuario logueado que realiza la recepción.
     */
    public function recibirPedido($idPedido, $idUsuario)
    {
        try {
            // Arrancamos transacción
            $this->PDO->beginTransaction();

            // 1) Traer el pedido y validar estado
            $stmtPedido = $this->PDO->prepare("
                SELECT Estado 
                FROM pedidomp 
                WHERE idPedido = :id
                FOR UPDATE
            ");
            $stmtPedido->execute([':id' => $idPedido]);
            $pedido = $stmtPedido->fetch(PDO::FETCH_ASSOC);

            if (!$pedido) {
                // Pedido no existe
                $this->PDO->rollBack();
                return false;
            }

            // Solo permitimos recibir si está Registrado
            if ((int)$pedido['Estado'] !== self::ESTADO_REGISTRADO) {
                $this->PDO->rollBack();
                return false;
            }

            // 2) Traer detalle del pedido
            $stmtDet = $this->PDO->prepare("
                SELECT idMP, cantidad 
                FROM detallepedido 
                WHERE idPedido = :id
            ");
            $stmtDet->execute([':id' => $idPedido]);
            $detalles = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

            // Si no hay detalle, no tiene sentido recibir
            if (!$detalles) {
                $this->PDO->rollBack();
                return false;
            }

            // 3) Actualizar stock de cada materia prima
            $stmtUpdateMP = $this->PDO->prepare("
                UPDATE materiaprima 
                SET stockactual = stockactual + :cantidad
                WHERE id = :idMP
            ");

            foreach ($detalles as $item) {
                $stmtUpdateMP->execute([
                    ':cantidad' => (int)$item['cantidad'],
                    ':idMP'     => (int)$item['idMP']
                ]);
            }

            // 4) Actualizar estado del pedido y datos de recepción
            $stmtUpdPedido = $this->PDO->prepare("
                UPDATE pedidomp
                SET Estado = :estado,
                    fechaRecepcion = NOW(),
                    usuarioRecepcion = :usuario
                WHERE idPedido = :id
            ");
            $stmtUpdPedido->execute([
                ':estado'  => self::ESTADO_RECIBIDO,
                ':usuario' => $idUsuario,
                ':id'      => $idPedido
            ]);

            // 5) Confirmamos transacción
            $this->PDO->commit();
            return true;
        } catch (Exception $e) {
            // Ante cualquier error, volvemos atrás
            if ($this->PDO->inTransaction()) {
                $this->PDO->rollBack();
            }
            return false;
        }
    }
}
