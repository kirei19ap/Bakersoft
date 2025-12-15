<?php
// reparto/modelo/modeloReparto.php

class ModeloReparto
{
    private $pdo;

    public function __construct()
    {
        // Misma forma de conexión que usás en el resto del sistema
        require_once("../../config/bd.php");
        $con = new bd();
        $this->pdo = $con->conexion();
    }

    /**
     * Listar repartos con datos de vehículo y chofer
     * $filtros: [
     *   'fechaDesde' => 'YYYY-MM-DD',
     *   'fechaHasta' => 'YYYY-MM-DD',
     *   'estado'     => 'Planificado|En Curso|Finalizado|Cancelado',
     *   'idVehiculo' => int
     * ]
     */
    public function listarRepartos(array $filtros = [])
    {
        $sql = "SELECT 
                    r.*,
                    v.patente,
                    v.descripcion AS vehiculoDescripcion,
                    e.nombre,
                    e.apellido,
                    COUNT(dr.idDetalleReparto) AS cantidadPedidos
                FROM reparto r
                INNER JOIN vehiculo v ON r.idVehiculo = v.idVehiculo
                INNER JOIN empleados e ON v.idChofer = e.id_empleado
                LEFT JOIN detallereparto dr ON r.idReparto = dr.idReparto
                WHERE r.eliminado = 0";

        $params = [];

        if (!empty($filtros['fechaDesde'])) {
            $sql .= " AND r.fechaReparto >= :fechaDesde";
            $params[':fechaDesde'] = $filtros['fechaDesde'];
        }

        if (!empty($filtros['fechaHasta'])) {
            $sql .= " AND r.fechaReparto <= :fechaHasta";
            $params[':fechaHasta'] = $filtros['fechaHasta'];
        }

        if (!empty($filtros['estado'])) {
            $sql .= " AND r.estado = :estado";
            $params[':estado'] = $filtros['estado'];
        }

        if (!empty($filtros['idVehiculo'])) {
            $sql .= " AND r.idVehiculo = :idVehiculo";
            $params[':idVehiculo'] = (int)$filtros['idVehiculo'];
        }

        $sql .= " GROUP BY r.idReparto
                  ORDER BY r.fechaReparto DESC, r.idReparto DESC";

        $stmt = $this->pdo->prepare($sql);

        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener cabecera de un reparto por ID
     */
    public function obtenerRepartoPorId($idReparto)
    {
        $sql = "SELECT 
                    r.*,
                    v.patente,
                    v.descripcion AS vehiculoDescripcion,
                    e.nombre,
                    e.apellido
                FROM reparto r
                INNER JOIN vehiculo v ON r.idVehiculo = v.idVehiculo
                INNER JOIN empleados e ON v.idChofer = e.id_empleado
                WHERE r.idReparto = :id
                  AND r.eliminado = 0";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $idReparto, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener detalle de un reparto (pedidos incluidos)
     * Incluye info del pedido y descripción del estado del pedido
     */
    public function obtenerDetalleReparto($idReparto)
    {
        $sql = "SELECT 
                    dr.*,
                    pv.fechaPedido,
                    pv.total,
                    pv.estado AS codEstadoPedido,
                    ep.descEstado
                FROM detallereparto dr
                INNER JOIN pedidoventa pv ON dr.idPedidoVenta = pv.idPedidoVenta
                LEFT JOIN estadospedidos ep ON pv.estado = ep.codEstado
                WHERE dr.idReparto = :id
                ORDER BY dr.ordenEntrega IS NULL, dr.ordenEntrega, dr.idDetalleReparto";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $idReparto, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Listar pedidos en estado PREPARADO (90) que no estén ya en un reparto activo
     * Repartos activos: Planificado, En Curso
     */
    public function listarPedidosPreparadosDisponibles()
    {
        $sql = "SELECT 
                    pv.idPedidoVenta,
                    pv.fechaPedido,
                    pv.total,
                    pv.observaciones,
                    pv.estado
                FROM pedidoventa pv
                WHERE pv.estado = 90  -- Preparado
                  AND NOT EXISTS (
                        SELECT 1
                        FROM detallereparto dr
                        INNER JOIN reparto r ON dr.idReparto = r.idReparto
                        WHERE dr.idPedidoVenta = pv.idPedidoVenta
                          AND r.eliminado = 0
                          AND r.estado IN ('Planificado','En Curso')
                  )
                ORDER BY pv.fechaPedido DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Crear un reparto (cabecera + detalle)
     * $cabecera = [
     *   'fechaReparto' => 'YYYY-MM-DD',
     *   'horaSalida'   => 'HH:MM:SS' o null,
     *   'zona'         => string,
     *   'idVehiculo'   => int,
     *   'observaciones'=> string|null
     * ]
     * $detallePedidos = [
     *   ['idPedidoVenta' => 1, 'ordenEntrega' => 1],
     *   ['idPedidoVenta' => 5, 'ordenEntrega' => 2],
     *   ...
     * ]
     */
    public function crearReparto(array $cabecera, array $detallePedidos)
    {
        try {
            $this->pdo->beginTransaction();

            $sqlCab = "INSERT INTO reparto
                        (fechaReparto, horaSalida, zona, idVehiculo, estado, observaciones)
                       VALUES
                        (:fechaReparto, :horaSalida, :zona, :idVehiculo, 'Planificado', :observaciones)";

            $stmtCab = $this->pdo->prepare($sqlCab);
            $stmtCab->bindValue(':fechaReparto', $cabecera['fechaReparto']);
            $stmtCab->bindValue(':horaSalida', !empty($cabecera['horaSalida']) ? $cabecera['horaSalida'] : null);
            $stmtCab->bindValue(':zona', $cabecera['zona']);
            $stmtCab->bindValue(':idVehiculo', (int)$cabecera['idVehiculo'], PDO::PARAM_INT);
            $stmtCab->bindValue(':observaciones', !empty($cabecera['observaciones']) ? $cabecera['observaciones'] : null);

            $stmtCab->execute();
            $idReparto = (int)$this->pdo->lastInsertId();

            if (!empty($detallePedidos)) {
                $sqlDet = "INSERT INTO detallereparto
                              (idReparto, idPedidoVenta, ordenEntrega, estadoEntrega)
                           VALUES
                              (:idReparto, :idPedidoVenta, :ordenEntrega, 'Pendiente')";
                $stmtDet = $this->pdo->prepare($sqlDet);

                foreach ($detallePedidos as $item) {
                    $stmtDet->bindValue(':idReparto', $idReparto, PDO::PARAM_INT);
                    $stmtDet->bindValue(':idPedidoVenta', (int)$item['idPedidoVenta'], PDO::PARAM_INT);
                    $orden = isset($item['ordenEntrega']) ? (int)$item['ordenEntrega'] : null;
                    $stmtDet->bindValue(':ordenEntrega', $orden);
                    $stmtDet->execute();
                }
            }

            $this->pdo->commit();
            return $idReparto;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            // Podés loguear el error si tenés logger
            return false;
        }
    }

    /**
     * Actualizar un reparto (cabecera + recalcular detalle)
     */
    public function actualizarReparto($idReparto, array $cabecera, array $detallePedidos)
    {
        try {
            $this->pdo->beginTransaction();

            $sqlCab = "UPDATE reparto
                       SET fechaReparto = :fechaReparto,
                           horaSalida   = :horaSalida,
                           zona         = :zona,
                           idVehiculo   = :idVehiculo,
                           observaciones= :observaciones
                       WHERE idReparto = :id
                         AND eliminado = 0";

            $stmtCab = $this->pdo->prepare($sqlCab);
            $stmtCab->bindValue(':fechaReparto', $cabecera['fechaReparto']);
            $stmtCab->bindValue(':horaSalida', !empty($cabecera['horaSalida']) ? $cabecera['horaSalida'] : null);
            $stmtCab->bindValue(':zona', $cabecera['zona']);
            $stmtCab->bindValue(':idVehiculo', (int)$cabecera['idVehiculo'], PDO::PARAM_INT);
            $stmtCab->bindValue(':observaciones', !empty($cabecera['observaciones']) ? $cabecera['observaciones'] : null);
            $stmtCab->bindValue(':id', $idReparto, PDO::PARAM_INT);
            $stmtCab->execute();

            // Borrar detalle anterior y volver a insertar
            $sqlDelDet = "DELETE FROM detallereparto WHERE idReparto = :id";
            $stmtDelDet = $this->pdo->prepare($sqlDelDet);
            $stmtDelDet->bindValue(':id', $idReparto, PDO::PARAM_INT);
            $stmtDelDet->execute();

            if (!empty($detallePedidos)) {
                $sqlDet = "INSERT INTO detallereparto
                              (idReparto, idPedidoVenta, ordenEntrega, estadoEntrega)
                           VALUES
                              (:idReparto, :idPedidoVenta, :ordenEntrega, 'Pendiente')";
                $stmtDet = $this->pdo->prepare($sqlDet);

                foreach ($detallePedidos as $item) {
                    $stmtDet->bindValue(':idReparto', $idReparto, PDO::PARAM_INT);
                    $stmtDet->bindValue(':idPedidoVenta', (int)$item['idPedidoVenta'], PDO::PARAM_INT);
                    $orden = isset($item['ordenEntrega']) ? (int)$item['ordenEntrega'] : null;
                    $stmtDet->bindValue(':ordenEntrega', $orden);
                    $stmtDet->execute();
                }
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    /**
     * Cambiar estado del reparto
     */
    /**
     * Cambiar estado del reparto.
     *
     * Regla importante:
     * - Si el nuevo estado es 'Finalizado', se marcan TODOS los detalles como 'Entregado'
     *   y TODOS los pedidos asociados pasan a estado 100 (Entregado).
     */
    public function cambiarEstadoReparto($idReparto, $nuevoEstado)
{
    $estadosValidos = ['Planificado', 'En Curso', 'Finalizado', 'Cancelado'];
    if (!in_array($nuevoEstado, $estadosValidos, true)) {
        return false;
    }

    try {
        $this->pdo->beginTransaction();

        // Si el reparto pasa a "En Curso", registramos la hora real de inicio
        if ($nuevoEstado === 'En Curso') {
            $sqlHoraIni = "UPDATE reparto
                           SET horaInicioReal = IFNULL(horaInicioReal, CURTIME())
                           WHERE idReparto = :idIni
                             AND eliminado = 0";
            $stmtHoraIni = $this->pdo->prepare($sqlHoraIni);
            $stmtHoraIni->bindValue(':idIni', $idReparto, PDO::PARAM_INT);
            $stmtHoraIni->execute();
        }

        if ($nuevoEstado === 'Finalizado') {
            // 1) Marcar todos los detalles del reparto como Entregado
            $sqlDet = "UPDATE detallereparto
                       SET estadoEntrega = 'Entregado'
                       WHERE idReparto = :idRep";
            $stmtDet = $this->pdo->prepare($sqlDet);
            $stmtDet->bindValue(':idRep', $idReparto, PDO::PARAM_INT);
            $stmtDet->execute();

            // 2) Marcar todos los pedidos del reparto como Entregado (100)
            $sqlPed = "UPDATE pedidoventa
                       SET estado = 100  -- Entregado
                       WHERE idPedidoVenta IN (
                           SELECT idPedidoVenta
                           FROM detallereparto
                           WHERE idReparto = :idRep2
                       )";
            $stmtPed = $this->pdo->prepare($sqlPed);
            $stmtPed->bindValue(':idRep2', $idReparto, PDO::PARAM_INT);
            $stmtPed->execute();

            // 3) Registrar hora real de fin (si aún no fue informada)
            $sqlHoraFin = "UPDATE reparto
                           SET horaFinReal = IFNULL(horaFinReal, CURTIME())
                           WHERE idReparto = :idFin
                             AND eliminado = 0";
            $stmtHoraFin = $this->pdo->prepare($sqlHoraFin);
            $stmtHoraFin->bindValue(':idFin', $idReparto, PDO::PARAM_INT);
            $stmtHoraFin->execute();
        }

        // 4) Actualizar estado del reparto
        $sqlRep = "UPDATE reparto
                   SET estado = :estado
                   WHERE idReparto = :id
                     AND eliminado = 0";
        $stmtRep = $this->pdo->prepare($sqlRep);
        $stmtRep->bindValue(':estado', $nuevoEstado);
        $stmtRep->bindValue(':id', $idReparto, PDO::PARAM_INT);
        $stmtRep->execute();

        $this->pdo->commit();
        return true;
    } catch (Exception $e) {
        $this->pdo->rollBack();
        return false;
    }
}

    /**
     * Duración promedio del reparto (en minutos) agrupada por zona.
     */
    public function obtenerDuracionPromedioPorZona($fechaDesde, $fechaHasta, $vehiculo = null, $estado = null)
    {
        $filtros = [];
        $params  = [];

        // Fecha (usamos mismo criterio que en obtenerDetalleRepartos)
        $filtros[]   = "r.fechaReparto BETWEEN :fd AND :fh";
        $params[':fd'] = $fechaDesde . " 00:00:00";
        $params[':fh'] = $fechaHasta . " 23:59:59";

        // Vehículo opcional
        if (!empty($vehiculo)) {
            $filtros[]     = "r.idVehiculo = :veh";
            $params[':veh'] = $vehiculo;
        }

        // Estado opcional (si != 'Todos')
        if (!empty($estado) && $estado !== 'Todos') {
            $filtros[]     = "r.estado = :est";
            $params[':est'] = $estado;
        }

        // Sólo repartos que tengan inicio y fin real cargados
        $filtros[] = "r.horaInicioReal IS NOT NULL";
        $filtros[] = "r.horaFinReal IS NOT NULL";
        $filtros[] = "r.eliminado = 0";

        $where = implode(" AND ", $filtros);

        $sql = "
            SELECT
                COALESCE(r.zona, 'Sin zona') AS zona,
                AVG(
                    TIMESTAMPDIFF(
                        MINUTE,
                        CONCAT(r.fechaReparto, ' ', r.horaInicioReal),
                        CONCAT(r.fechaReparto, ' ', r.horaFinReal)
                    )
                ) AS duracionPromedio
            FROM reparto r
            WHERE {$where}
            GROUP BY zona
            ORDER BY zona
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Indica si un pedido está asociado a algún reparto ACTIVO
     * (Planificado o En Curso).
     */
    public function pedidoEnRepartoActivo($idPedidoVenta)
    {
        $sql = "SELECT COUNT(*) AS cant
                FROM detallereparto dr
                INNER JOIN reparto r ON dr.idReparto = r.idReparto
                WHERE dr.idPedidoVenta = :idPed
                  AND r.eliminado = 0
                  AND r.estado IN ('Planificado','En Curso')";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':idPed', $idPedidoVenta, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row && (int)$row['cant'] > 0;
    }



    /**
     * Actualizar estado de entrega de un pedido dentro de un reparto
     * y en caso de quedar todos entregados, marcar el reparto como Finalizado.
     * Si se pasa a 'Entregado', también se actualiza el estado del pedidoventa a 100 (Entregado).
     */
    public function actualizarEstadoEntrega($idDetalleReparto, $nuevoEstadoEntrega)
    {
        $estadosValidos = ['Pendiente', 'Entregado', 'No Entregado', 'Reprogramado'];
        if (!in_array($nuevoEstadoEntrega, $estadosValidos, true)) {
            return false;
        }

        try {
            $this->pdo->beginTransaction();

            // Obtener info básica del detalle para saber idReparto e idPedidoVenta
            $sqlSel = "SELECT idReparto, idPedidoVenta
                       FROM detallereparto
                       WHERE idDetalleReparto = :idDet";
            $stmtSel = $this->pdo->prepare($sqlSel);
            $stmtSel->bindValue(':idDet', $idDetalleReparto, PDO::PARAM_INT);
            $stmtSel->execute();
            $detalle = $stmtSel->fetch(PDO::FETCH_ASSOC);

            if (!$detalle) {
                $this->pdo->rollBack();
                return false;
            }

            $idReparto = (int)$detalle['idReparto'];
            $idPedidoVenta = (int)$detalle['idPedidoVenta'];

            // Actualizar estadoEntrega del detalle
            $sqlUpdDet = "UPDATE detallereparto
                          SET estadoEntrega = :estadoEntrega
                          WHERE idDetalleReparto = :idDet";
            $stmtUpdDet = $this->pdo->prepare($sqlUpdDet);
            $stmtUpdDet->bindValue(':estadoEntrega', $nuevoEstadoEntrega);
            $stmtUpdDet->bindValue(':idDet', $idDetalleReparto, PDO::PARAM_INT);
            $stmtUpdDet->execute();

            // Si se marcó como Entregado, actualizar el estado del pedido a 100
            if ($nuevoEstadoEntrega === 'Entregado') {
                $sqlUpdPed = "UPDATE pedidoventa
                              SET estado = 100  -- Entregado
                              WHERE idPedidoVenta = :idPed";
                $stmtUpdPed = $this->pdo->prepare($sqlUpdPed);
                $stmtUpdPed->bindValue(':idPed', $idPedidoVenta, PDO::PARAM_INT);
                $stmtUpdPed->execute();
            }

            // Verificar si todos los detalles del reparto están Entregados
            $sqlPend = "SELECT COUNT(*) AS pendientes
                        FROM detallereparto
                        WHERE idReparto = :idRep
                          AND estadoEntrega <> 'Entregado'";
            $stmtPend = $this->pdo->prepare($sqlPend);
            $stmtPend->bindValue(':idRep', $idReparto, PDO::PARAM_INT);
            $stmtPend->execute();
            $rowPend = $stmtPend->fetch(PDO::FETCH_ASSOC);

            if ($rowPend && (int)$rowPend['pendientes'] === 0) {
                // Todos entregados -> marcar reparto como Finalizado
                $sqlFin = "UPDATE reparto
                           SET estado = 'Finalizado'
                           WHERE idReparto = :idRep";
                $stmtFin = $this->pdo->prepare($sqlFin);
                $stmtFin->bindValue(':idRep', $idReparto, PDO::PARAM_INT);
                $stmtFin->execute();
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    /**
     * Listar pedidos preparados (90) para la edición de un reparto:
     *  - Incluye los pedidos YA asignados a ESTE reparto (aunque esté Planificado).
     *  - Incluye pedidos preparados que NO estén en otros repartos activos (Planificado / En Curso).
     */
    public function listarPedidosPreparadosParaEdicion($idReparto)
    {
        $sql = "SELECT 
                    pv.idPedidoVenta,
                    pv.fechaPedido,
                    pv.total,
                    pv.observaciones,
                    pv.estado,
                    CASE 
                      WHEN EXISTS (
                          SELECT 1
                          FROM detallereparto dr
                          INNER JOIN reparto r ON dr.idReparto = r.idReparto
                          WHERE dr.idPedidoVenta = pv.idPedidoVenta
                            AND r.idReparto = :idReparto
                            AND r.eliminado = 0
                      ) THEN 1
                      ELSE 0
                    END AS enEsteReparto
                FROM pedidoventa pv
                WHERE pv.estado = 90
                  AND (
                        -- Ya pertenece a este reparto
                        EXISTS (
                            SELECT 1
                            FROM detallereparto dr
                            INNER JOIN reparto r ON dr.idReparto = r.idReparto
                            WHERE dr.idPedidoVenta = pv.idPedidoVenta
                              AND r.idReparto = :idReparto
                              AND r.eliminado = 0
                        )
                        OR
                        -- O no está en ningún reparto activo
                        NOT EXISTS (
                            SELECT 1
                            FROM detallereparto dr2
                            INNER JOIN reparto r2 ON dr2.idReparto = r2.idReparto
                            WHERE dr2.idPedidoVenta = pv.idPedidoVenta
                              AND r2.eliminado = 0
                              AND r2.estado IN ('Planificado','En Curso')
                        )
                  )
                ORDER BY enEsteReparto DESC, pv.fechaPedido DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':idReparto', $idReparto, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener KPIs principales para reportes.
     */
    public function obtenerKPIsReparto($fechaDesde, $fechaHasta, $vehiculo = null, $estado = null)
    {
        $filtros = [];
        $params = [];

        // Fechas
        $filtros[] = "r.fechaReparto BETWEEN :fd AND :fh";
        $params[':fd'] = $fechaDesde . " 00:00:00";
        $params[':fh'] = $fechaHasta . " 23:59:59";

        // Vehículo opcional
        if (!empty($vehiculo)) {
            $filtros[] = "r.idVehiculo = :veh";
            $params[':veh'] = $vehiculo;
        }

        // Estado opcional
        if (!empty($estado) && $estado !== 'Todos') {
            $filtros[] = "r.estado = :est";
            $params[':est'] = $estado;
        }

        $where = implode(" AND ", $filtros);

        // Total de repartos
        $sql1 = "SELECT COUNT(*) AS total
                 FROM reparto r
                 WHERE {$where}";
        $stmt = $this->pdo->prepare($sql1);
        $stmt->execute($params);
        $totalRepartos = $stmt->fetchColumn();

        // Total de pedidos asignados
        $sql2 = "SELECT COUNT(*) AS total
                 FROM detallereparto dr
                 INNER JOIN reparto r ON r.idReparto = dr.idReparto
                 WHERE {$where}";
        $stmt = $this->pdo->prepare($sql2);
        $stmt->execute($params);
        $totalPedidos = $stmt->fetchColumn();

        // Pedidos entregados
        $sql3 = "SELECT COUNT(*) AS entregados
                 FROM detallereparto dr
                 INNER JOIN reparto r ON r.idReparto = dr.idReparto
                 WHERE {$where}
                   AND dr.estadoEntrega = 'Entregado'";
        $stmt = $this->pdo->prepare($sql3);
        $stmt->execute($params);
        $entregados = $stmt->fetchColumn();

        // Repartos cancelados
        $sql4 = "SELECT COUNT(*) AS cancelados
                 FROM reparto r
                 WHERE {$where}
                   AND r.estado = 'Cancelado'";
        $stmt = $this->pdo->prepare($sql4);
        $stmt->execute($params);
        $cancelados = $stmt->fetchColumn();

        return [
            'totalRepartos' => (int)$totalRepartos,
            'totalPedidos' => (int)$totalPedidos,
            'entregados' => (int)$entregados,
            'cancelados' => (int)$cancelados,
        ];
    }


    /**
     * Obtener detalle para la tabla del reporte.
     */
    public function obtenerDetalleRepartos($fechaDesde, $fechaHasta, $vehiculo = null, $estado = null)
    {
        $filtros = [];
        $params = [];

        // Rango de fechas
        $filtros[] = "r.fechaReparto BETWEEN :fd AND :fh";
        $params[':fd'] = $fechaDesde . " 00:00:00";
        $params[':fh'] = $fechaHasta . " 23:59:59";

        // Vehículo opcional
        if (!empty($vehiculo)) {
            $filtros[] = "r.idVehiculo = :veh";
            $params[':veh'] = $vehiculo;
        }

        // Estado opcional
        if (!empty($estado) && $estado !== 'Todos') {
            $filtros[] = "r.estado = :est";
            $params[':est'] = $estado;
        }

        $where = implode(" AND ", $filtros);

        $sql = "
        SELECT
            r.idReparto,
            r.fechaReparto,
            r.estado,
            v.patente,
            v.descripcion AS vehiculoDescripcion,
            CONCAT(e.apellido, ', ', e.nombre) AS chofer,
            COUNT(dr.idDetalleReparto) AS totalPedidos,
            SUM(CASE WHEN dr.estadoEntrega = 'Entregado' THEN 1 ELSE 0 END) AS entregados
        FROM reparto r
        INNER JOIN vehiculo v ON v.idVehiculo = r.idVehiculo
        INNER JOIN empleados e ON v.idChofer = e.id_empleado
        LEFT JOIN detallereparto dr ON dr.idReparto = r.idReparto
        WHERE {$where}
        GROUP BY
            r.idReparto,
            r.fechaReparto,
            r.estado,
            v.patente,
            v.descripcion,
            e.apellido,
            e.nombre
        ORDER BY r.fechaReparto DESC, r.idReparto DESC
    ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
