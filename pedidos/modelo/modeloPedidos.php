<?php

class modeloPedidos
{
    private $pdo;

    public function __construct()
    {
        require_once("../../config/bd.php");
        $con = new bd();
        $this->pdo = $con->conexion();
    }

    /**
     * Obtiene los productos activos para usar en el detalle del pedido.
     */
    public function obtenerProductosVenta()
    {
        $sql = "SELECT idProducto, nombre, unidad_medida, precio_venta
                FROM producto
                WHERE estado = 'Activo'
                ORDER BY nombre";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Busca clientes por nombre, email o teléfono (para el buscador del pedido).
     */
    public function buscarClientes(string $termino)
    {
        $sql = "SELECT 
                    id_cliente,
                    nombre,
                    email,
                    telefono,
                    calle,
                    altura,
                    provincia,
                    localidad
                FROM clientes
                WHERE nombre   LIKE :term
                   OR email    LIKE :term
                   OR telefono LIKE :term
                ORDER BY nombre
                LIMIT 20";
        $stmt = $this->pdo->prepare($sql);
        $like = '%' . $termino . '%';
        $stmt->execute([':term' => $like]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza datos básicos de un cliente existente (sin tocar provincia/localidad/estado).
     */
    public function actualizarClienteBasico(array $cliente)
    {
        $sql = "UPDATE clientes
                SET nombre   = :nombre,
                    email    = :email,
                    telefono = :telefono,
                    calle    = :calle,
                    altura   = :altura
                WHERE id_cliente = :idCliente";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':nombre'    => $cliente['nombre'],
            ':email'     => $cliente['email'],
            ':telefono'  => $cliente['telefono'],
            ':calle'     => $cliente['calle'],
            ':altura'    => $cliente['altura'],
            ':idCliente' => $cliente['idCliente'],
        ]);
    }

    /**
     * Crea un cliente básico (sin pedido) y devuelve el id insertado.
     */
    public function crearClienteBasico(array $cliente)
    {
        try {
            $sql = "INSERT INTO clientes
                    (nombre, email, telefono, calle, altura, provincia, localidad, estado)
                    VALUES
                    (:nombre, :email, :telefono, :calle, :altura, :provincia, :localidad, :estado)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':nombre'    => $cliente['nombre'],
                ':email'     => $cliente['email'],
                ':telefono'  => $cliente['telefono'],
                ':calle'     => $cliente['calle'],
                ':altura'    => $cliente['altura'],
                ':provincia' => $cliente['provincia'] ?? 1,
                ':localidad' => $cliente['localidad'] ?? 1,
                ':estado'    => $cliente['estado'] ?? 'Activo',
            ]);

            $idCliente = $this->pdo->lastInsertId();

            return [
                'ok'        => true,
                'idCliente' => (int)$idCliente
            ];
        } catch (Exception $e) {
            return [
                'ok'    => false,
                'error' => $e->getMessage()
            ];
        }
    }



    /**
     * Registra cliente + pedido + detalle en una sola transacción.
     * (Caso "cliente nuevo").
     */
    public function registrarPedidoCompleto(array $cliente, array $pedido, array $detalle)
    {
        try {
            $this->pdo->beginTransaction();

            // 1) Insertar cliente
            $sqlCliente = "INSERT INTO clientes
                           (nombre, email, telefono, calle, altura, provincia, localidad, estado)
                           VALUES
                           (:nombre, :email, :telefono, :calle, :altura, :provincia, :localidad, :estado)";
            $stmtCli = $this->pdo->prepare($sqlCliente);
            $stmtCli->execute([
                ':nombre'    => $cliente['nombre'],
                ':email'     => $cliente['email'],
                ':telefono'  => $cliente['telefono'],
                ':calle'     => $cliente['calle'],
                ':altura'    => $cliente['altura'],
                ':provincia' => $cliente['provincia'],
                ':localidad' => $cliente['localidad'],
                ':estado'    => $cliente['estado'] ?? 'Activo',
            ]);
            $idCliente = $this->pdo->lastInsertId();

            // 2) Insertar cabecera de pedido
            $sqlPedido = "INSERT INTO pedidoventa
                          (idCliente, fechaPedido, estado, observaciones, total)
                          VALUES
                          (:idCliente, :fechaPedido, :estado, :observaciones, :total)";
            $stmtPed = $this->pdo->prepare($sqlPedido);
            $stmtPed->execute([
                ':idCliente'     => $idCliente,
                ':fechaPedido'   => $pedido['fechaPedido'],
                ':estado'        => $pedido['estado'],
                ':observaciones' => $pedido['observaciones'],
                ':total'         => $pedido['total'],
            ]);
            $idPedidoVenta = $this->pdo->lastInsertId();

            // 3) Insertar detalle
            $sqlDetalle = "INSERT INTO detallepedidoventa
                           (idPedidoVenta, idProducto, cantidad, precioUnitario, subtotal)
                           VALUES
                           (:idPedidoVenta, :idProducto, :cantidad, :precioUnitario, :subtotal)";
            $stmtDet = $this->pdo->prepare($sqlDetalle);

            foreach ($detalle as $item) {
                $stmtDet->execute([
                    ':idPedidoVenta' => $idPedidoVenta,
                    ':idProducto'    => $item['idProducto'],
                    ':cantidad'      => $item['cantidad'],
                    ':precioUnitario' => $item['precioUnitario'],
                    ':subtotal'      => $item['subtotal'],
                ]);
            }

            $this->pdo->commit();

            return [
                'ok'            => true,
                'idPedidoVenta' => $idPedidoVenta
            ];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return [
                'ok'    => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Registra un pedido para un cliente existente (no inserta cliente nuevo).
     */
    public function registrarPedidoParaClienteExistente(int $idCliente, array $pedido, array $detalle)
    {
        try {
            $this->pdo->beginTransaction();

            // 1) Insertar cabecera de pedido
            $sqlPedido = "INSERT INTO pedidoventa
                          (idCliente, fechaPedido, estado, observaciones, total)
                          VALUES
                          (:idCliente, :fechaPedido, :estado, :observaciones, :total)";
            $stmtPed = $this->pdo->prepare($sqlPedido);
            $stmtPed->execute([
                ':idCliente'     => $idCliente,
                ':fechaPedido'   => $pedido['fechaPedido'],
                ':estado'        => $pedido['estado'],
                ':observaciones' => $pedido['observaciones'],
                ':total'         => $pedido['total'],
            ]);
            $idPedidoVenta = $this->pdo->lastInsertId();

            // 2) Insertar detalle
            $sqlDetalle = "INSERT INTO detallepedidoventa
                           (idPedidoVenta, idProducto, cantidad, precioUnitario, subtotal)
                           VALUES
                           (:idPedidoVenta, :idProducto, :cantidad, :precioUnitario, :subtotal)";
            $stmtDet = $this->pdo->prepare($sqlDetalle);

            foreach ($detalle as $item) {
                $stmtDet->execute([
                    ':idPedidoVenta' => $idPedidoVenta,
                    ':idProducto'    => $item['idProducto'],
                    ':cantidad'      => $item['cantidad'],
                    ':precioUnitario' => $item['precioUnitario'],
                    ':subtotal'      => $item['subtotal'],
                ]);
            }

            $this->pdo->commit();

            return [
                'ok'            => true,
                'idPedidoVenta' => $idPedidoVenta
            ];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return [
                'ok'    => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Lista los pedidos de venta con info de cliente y estado.
     */
    public function listarPedidosVenta()
    {
        $sql = "SELECT 
                    pv.idPedidoVenta,
                    pv.fechaPedido,
                    pv.total,
                    pv.estado,
                    c.nombre AS cliente,
                    e.descEstado
                FROM pedidoventa pv
                INNER JOIN clientes c ON pv.idCliente = c.id_cliente
                LEFT JOIN estadospedidos e ON pv.estado = e.codEstado
                ORDER BY pv.fechaPedido DESC, pv.idPedidoVenta DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Actualiza el estado de un pedido de venta.
     */
    public function actualizarEstadoPedido(int $idPedidoVenta, int $nuevoEstado)
    {
        $sql = "UPDATE pedidoventa
                SET estado = :estado
                WHERE idPedidoVenta = :idPedidoVenta";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':estado'        => $nuevoEstado,
            ':idPedidoVenta' => $idPedidoVenta,
        ]);
    }

    /**
     * Obtiene pedido + cliente + detalle para edición.
     */
    public function obtenerPedidoCompleto(int $idPedidoVenta)
    {
        // Cabecera + cliente
        $sql = "SELECT 
                    pv.idPedidoVenta,
                    pv.fechaPedido,
                    pv.observaciones,
                    pv.total,
                    pv.estado,
                    c.id_cliente,
                    c.nombre,
                    c.email,
                    c.telefono,
                    c.calle,
                    c.altura,
                    c.provincia,
                    c.localidad
                FROM pedidoventa pv
                INNER JOIN clientes c ON pv.idCliente = c.id_cliente
                WHERE pv.idPedidoVenta = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $idPedidoVenta]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pedido) {
            return null;
        }

        // Detalle
        $sqlDet = "SELECT 
                        dpv.idDetallePedidoVenta,
                        dpv.idProducto,
                        p.nombre AS nombreProducto,
                        dpv.cantidad,
                        dpv.precioUnitario,
                        dpv.subtotal
                   FROM detallepedidoventa dpv
                   INNER JOIN producto p ON dpv.idProducto = p.idProducto
                   WHERE dpv.idPedidoVenta = :id";
        $stmtDet = $this->pdo->prepare($sqlDet);
        $stmtDet->execute([':id' => $idPedidoVenta]);
        $detalle = $stmtDet->fetchAll(PDO::FETCH_ASSOC);

        return [
            'pedido'  => $pedido,
            'detalle' => $detalle
        ];
    }

    /**
     * Actualiza cliente + cabecera + detalle de un pedido existente.
     * (Se asume que el estado se mantiene, no se modifica acá).
     */
    public function actualizarPedidoCompleto(int $idPedidoVenta, array $cliente, array $pedido, array $detalle)
    {
        try {
            $this->pdo->beginTransaction();

            // 1) Actualizar cliente
            $sqlCliente = "UPDATE clientes
                           SET nombre   = :nombre,
                               email    = :email,
                               telefono = :telefono,
                               calle    = :calle,
                               altura   = :altura,
                               provincia= :provincia,
                               localidad= :localidad
                           WHERE id_cliente = :idCliente";
            $stmtCli = $this->pdo->prepare($sqlCliente);
            $stmtCli->execute([
                ':nombre'    => $cliente['nombre'],
                ':email'     => $cliente['email'],
                ':telefono'  => $cliente['telefono'],
                ':calle'     => $cliente['calle'],
                ':altura'    => $cliente['altura'],
                ':provincia' => $cliente['provincia'],
                ':localidad' => $cliente['localidad'],
                ':idCliente' => $cliente['idCliente'],
            ]);

            // 2) Actualizar cabecera de pedido (sin tocar estado)
            $sqlPedido = "UPDATE pedidoventa
                          SET fechaPedido   = :fechaPedido,
                              observaciones = :observaciones,
                              total         = :total
                          WHERE idPedidoVenta = :idPedidoVenta";
            $stmtPed = $this->pdo->prepare($sqlPedido);
            $stmtPed->execute([
                ':fechaPedido'   => $pedido['fechaPedido'],
                ':observaciones' => $pedido['observaciones'],
                ':total'         => $pedido['total'],
                ':idPedidoVenta' => $idPedidoVenta,
            ]);

            // 3) Borrar detalle anterior
            $sqlDel = "DELETE FROM detallepedidoventa WHERE idPedidoVenta = :idPedidoVenta";
            $stmtDel = $this->pdo->prepare($sqlDel);
            $stmtDel->execute([':idPedidoVenta' => $idPedidoVenta]);

            // 4) Insertar nuevo detalle
            $sqlDetalle = "INSERT INTO detallepedidoventa
                           (idPedidoVenta, idProducto, cantidad, precioUnitario, subtotal)
                           VALUES
                           (:idPedidoVenta, :idProducto, :cantidad, :precioUnitario, :subtotal)";
            $stmtDet = $this->pdo->prepare($sqlDetalle);

            foreach ($detalle as $item) {
                $stmtDet->execute([
                    ':idPedidoVenta' => $idPedidoVenta,
                    ':idProducto'    => $item['idProducto'],
                    ':cantidad'      => $item['cantidad'],
                    ':precioUnitario' => $item['precioUnitario'],
                    ':subtotal'      => $item['subtotal'],
                ]);
            }

            $this->pdo->commit();
            return ['ok' => true];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Resumen de pedidos por estado en un rango de fechas.
     * $desde y $hasta vienen en formato 'Y-m-d H:i:s'
     */
    public function resumenPedidosPorEstado(string $desde, string $hasta)
    {
        $sql = "SELECT 
                    pv.estado,
                    COALESCE(e.descEstado, 'Sin estado') AS descEstado,
                    COUNT(*) AS cantidad
                FROM pedidoventa pv
                LEFT JOIN estadospedidos e ON pv.estado = e.codEstado
                WHERE pv.fechaPedido BETWEEN :desde AND :hasta
                GROUP BY pv.estado, e.descEstado
                ORDER BY pv.estado";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':desde' => $desde,
            ':hasta' => $hasta,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Cantidad de pedidos por día en un rango de fechas.
     */
    public function resumenPedidosPorDia(string $desde, string $hasta)
    {
        $sql = "SELECT 
                    DATE(pv.fechaPedido) AS fecha,
                    COUNT(*) AS cantidad
                FROM pedidoventa pv
                WHERE pv.fechaPedido BETWEEN :desde AND :hasta
                GROUP BY DATE(pv.fechaPedido)
                ORDER BY fecha";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':desde' => $desde,
            ':hasta' => $hasta,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Facturación (suma de total) por día en un rango de fechas.
     */
    public function resumenFacturacionPorDia(string $desde, string $hasta)
    {
        $sql = "SELECT 
                    DATE(pv.fechaPedido) AS fecha,
                    SUM(pv.total) AS total
                FROM pedidoventa pv
                WHERE pv.fechaPedido BETWEEN :desde AND :hasta
                GROUP BY DATE(pv.fechaPedido)
                ORDER BY fecha";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':desde' => $desde,
            ':hasta' => $hasta,
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Lista pedidos filtrados por rango de fechas y estado (opcional),
     * para usar en reportes (tabla y PDF).
     *
     * $desde y $hasta vienen en formato 'Y-m-d H:i:s'
     * $estado puede ser null para "todos".
     */
    public function listarPedidosFiltrados(string $desde, string $hasta, ?int $estado = null)
    {
        $sql = "SELECT 
                    pv.idPedidoVenta,
                    pv.fechaPedido,
                    pv.total,
                    pv.estado,
                    c.nombre AS cliente,
                    e.descEstado
                FROM pedidoventa pv
                INNER JOIN clientes c ON pv.idCliente = c.id_cliente
                LEFT JOIN estadospedidos e ON pv.estado = e.codEstado
                WHERE pv.fechaPedido BETWEEN :desde AND :hasta";

        $params = [
            ':desde' => $desde,
            ':hasta' => $hasta,
        ];

        if (!is_null($estado)) {
            $sql .= " AND pv.estado = :estado";
            $params[':estado'] = $estado;
        }

        $sql .= " ORDER BY pv.fechaPedido DESC, pv.idPedidoVenta DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Evalúa si hay stock de materia prima suficiente para fabricar
     * TODO el pedido indicado.
     *
     * - Lee el detalle del pedido (detallepedidoventa).
     * - Usa la receta de cada producto (detalleproducto).
     * - Compara el total requerido por MP contra materiaprima.stockactual.
     *
     * Devuelve:
     * [
     *   'suficiente' => bool,
     *   'faltantes'  => [
     *      [
     *        'idMP'       => int,
     *        'nombreMP'   => string,
     *        'requerido'  => float,
     *        'disponible' => float,
     *        'faltante'   => float
     *      ],
     *      ...
     *   ],
     *   'consumos'   => [ idMP => requeridoTotal, ... ]
     * ]
     */
    public function evaluarStockPedido(int $idPedidoVenta): array
    {
        // 1) Detalle del pedido + receta (detalleproducto)
        $sql = "SELECT 
                    dpv.idProducto,
                    dpv.cantidad       AS cantidadPedido,
                    dp.idMP,
                    dp.cantidad        AS cantidadMPxUnidad
                FROM detallepedidoventa dpv
                INNER JOIN detalleproducto dp 
                        ON dpv.idProducto = dp.idProducto
                WHERE dpv.idPedidoVenta = :idPedidoVenta";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':idPedidoVenta' => $idPedidoVenta]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Si no hay detalle o no tiene receta cargada, asumimos que no consume MP.
        if (!$rows) {
            return [
                'suficiente' => true,
                'faltantes'  => [],
                'consumos'   => [],
            ];
        }

        // 2) Calcular consumo total por materia prima
        $consumos = []; // idMP => cantidad total requerida

        foreach ($rows as $r) {
            $idMP       = (int)$r['idMP'];
            $cantPedida = (float)$r['cantidadPedido'];
            $cantMPxUni = (float)$r['cantidadMPxUnidad'];

            $requerido = $cantPedida * $cantMPxUni; // NO incluimos merma por ahora

            if (!isset($consumos[$idMP])) {
                $consumos[$idMP] = 0.0;
            }
            $consumos[$idMP] += $requerido;
        }

        if (empty($consumos)) {
            return [
                'suficiente' => true,
                'faltantes'  => [],
                'consumos'   => [],
            ];
        }

        // 3) Traer stock actual de esas MP
        $idsMP = array_keys($consumos);
        $placeholders = implode(',', array_fill(0, count($idsMP), '?'));

        $sqlStock = "SELECT id, nombre, stockactual, unidad_medida
             FROM materiaprima
             WHERE id IN ($placeholders)";
        $stmtStock = $this->pdo->prepare($sqlStock);
        $stmtStock->execute($idsMP);
        $rowsStock = $stmtStock->fetchAll(PDO::FETCH_ASSOC);

        $stocks = []; // idMP => ['nombre' => ..., 'stockactual' => ..., 'unidad' => ...]
        foreach ($rowsStock as $s) {
            $stocks[(int)$s['id']] = [
                'nombre'      => $s['nombre'],
                'stockactual' => (float)$s['stockactual'],
                'unidad'      => $s['unidad_medida'],
            ];
        }


        // 4) Comparar requerido vs disponible
        $faltantes = [];

        foreach ($consumos as $idMP => $requerido) {
            $infoStock  = $stocks[$idMP] ?? null;
            $disponible = $infoStock ? $infoStock['stockactual'] : 0.0;
            $nombreMP   = $infoStock ? $infoStock['nombre'] : ('MP #' . $idMP);
            $unidad     = $infoStock['unidad'] ?? '';

            if ($disponible + 1e-6 < $requerido) {
                $faltantes[] = [
                    'idMP'       => $idMP,
                    'nombreMP'   => $nombreMP,
                    'unidad'     => $unidad,
                    'requerido'  => $requerido,
                    'disponible' => $disponible,
                    'faltante'   => $requerido - $disponible,
                ];
            }
        }

        $suficiente = empty($faltantes);

        return [
            'suficiente' => $suficiente,
            'faltantes'  => $faltantes,
            'consumos'   => $consumos,
        ];
    }

    /**
     * Pasa el pedido a estado PREPARADO (90) descontando las materias primas
     * utilizadas, todo dentro de una transacción.
     *
     * Devuelve true si todo salió bien, false si falla stock o alguna operación.
     */
    public function prepararPedidoConDescuentoStock(int $idPedidoVenta): bool
    {
        try {
            $this->pdo->beginTransaction();

            // 1) Re-evaluar stock de MP para este pedido
            $resumen = $this->evaluarStockPedido($idPedidoVenta);

            if (empty($resumen) || !($resumen['suficiente'] ?? false)) {
                // No hay stock suficiente: no descontamos nada ni cambiamos estado
                $this->pdo->rollBack();
                return false;
            }

            $consumos = $resumen['consumos'] ?? [];
            if (empty($consumos)) {
                // El pedido no tiene consumo de MP (sin recetas): solo cambio de estado
                $ok = $this->actualizarEstadoPedido($idPedidoVenta, 90);
                if (!$ok) {
                    $this->pdo->rollBack();
                    return false;
                }
                $this->pdo->commit();
                return true;
            }

            // 2) Bloquear filas de MP involucradas y verificar stock nuevamente
            $idsMP = array_keys($consumos);
            $placeholders = implode(',', array_fill(0, count($idsMP), '?'));

            $sqlSel = "SELECT id, stockactual
                       FROM materiaprima
                       WHERE id IN ($placeholders)
                       FOR UPDATE";
            $stmtSel = $this->pdo->prepare($sqlSel);
            $stmtSel->execute($idsMP);
            $rowsStock = $stmtSel->fetchAll(PDO::FETCH_ASSOC);

            $stocks = []; // idMP => stockactual
            foreach ($rowsStock as $row) {
                $stocks[(int)$row['id']] = (float)$row['stockactual'];
            }

            // Verificar que siga habiendo stock suficiente (por si hubo cambios entre listados)
            foreach ($consumos as $idMP => $requerido) {
                $disponible = $stocks[$idMP] ?? 0.0;
                if ($disponible + 1e-6 < $requerido) {
                    // Stock ya no alcanza
                    $this->pdo->rollBack();
                    return false;
                }
            }

            // 3) Descontar stock de cada MP
            $sqlUpd = "UPDATE materiaprima
                       SET stockactual = stockactual - :consumo
                       WHERE id = :idMP";
            $stmtUpd = $this->pdo->prepare($sqlUpd);

            foreach ($consumos as $idMP => $requerido) {
                $stmtUpd->execute([
                    ':consumo' => $requerido,
                    ':idMP'    => $idMP,
                ]);
            }

            // 4) Actualizar estado del pedido a PREPARADO (90)
            $okEstado = $this->actualizarEstadoPedido($idPedidoVenta, 90);
            if (!$okEstado) {
                $this->pdo->rollBack();
                return false;
            }

            // 5) Todo OK
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            // Ante cualquier error, revertimos
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            // Podrías loguear $e->getMessage() si lo necesitás
            return false;
        }
    }

    /**
     * Devuelve los pedidos de HOY en un estado específico.
     * Se apoya en listarPedidosFiltrados para mantener la lógica central en un solo lugar.
     *
     * @param int $estado Código del estado (ej: 80 = En Preparación)
     * @return array
     */
    public function obtenerPedidosHoyPorEstado(int $estado): array
    {
        // Rango de "hoy" según fechaPedido
        $desde = date('Y-m-d') . ' 00:00:00';
        $hasta = date('Y-m-d') . ' 23:59:59';

        // Reutilizamos la función de reportes
        return $this->listarPedidosFiltrados($desde, $hasta, $estado);
    }

    /**
     * Devuelve pedidos en estado específico sin limitar por fecha.
     * @param int $estado Código de estado (ej: 80 = En Preparación)
     * @param int $limite Cantidad máxima de pedidos
     * @return array
     */
    public function obtenerPedidosPorEstado(int $estado, int $limite = 50): array
    {
        $sql = "SELECT 
                pv.idPedidoVenta,
                pv.fechaPedido,
                pv.total,
                pv.estado,
                c.nombre AS cliente,
                e.descEstado
            FROM pedidoventa pv
            INNER JOIN clientes c ON pv.idCliente = c.id_cliente
            LEFT JOIN estadospedidos e ON pv.estado = e.codEstado
            WHERE pv.estado = :estado
            ORDER BY pv.fechaPedido DESC, pv.idPedidoVenta DESC
            LIMIT :limite";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':estado', $estado, PDO::PARAM_INT);
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Atajo específico para "En Preparación" sin filtro de fecha.
     */
    public function obtenerPedidosEnPreparacion(): array
    {
        return $this->obtenerPedidosPorEstado(80, 50);
    }

    public function obtenerPedidosPreparados(): array
    {
        return $this->obtenerPedidosPorEstado(90, 50);
    }

    public function obtenerPedidosPreparadosHoy(): array
    {
        $desde = date('Y-m-d') . ' 00:00:00';
        $hasta = date('Y-m-d') . ' 23:59:59';

        return $this->listarPedidosFiltrados($desde, $hasta, 90);
    }

    public function obtenerPedidosEntregados(): array
    {
        return $this->obtenerPedidosPorEstado(100, 50);
    }

    public function obtenerPedidosEntregadosHoy(): array
    {
        $desde = date('Y-m-d') . ' 00:00:00';
        $hasta = date('Y-m-d') . ' 23:59:59';

        return $this->listarPedidosFiltrados($desde, $hasta, 100);
    }

    public function topProductosVendidos(string $desde, string $hasta, int $limit = 10)
    {
        $limit = max(1, min($limit, 50)); // seguridad

        $sql = "SELECT
              p.idProducto,
              p.nombre,
              p.unidad_medida,
              SUM(dpv.cantidad) AS cantidad_total,
              SUM(dpv.subtotal) AS facturacion_total
            FROM detallepedidoventa dpv
            INNER JOIN pedidoventa pv ON pv.idPedidoVenta = dpv.idPedidoVenta
            INNER JOIN producto p     ON p.idProducto = dpv.idProducto
            WHERE pv.fechaPedido BETWEEN :desde AND :hasta
              AND pv.estado <> 60
            GROUP BY p.idProducto, p.nombre, p.unidad_medida
            ORDER BY cantidad_total DESC
            LIMIT $limit";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':desde' => $desde,
            ':hasta' => $hasta
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }




    /**
     * Atajo específico para "en preparación" (estado 80).
     *
     * @return array
     */
    public function obtenerPedidosEnPreparacionHoy(): array
    {
        // 80 = En Preparación según tabla estadospedidos
        return $this->obtenerPedidosHoyPorEstado(80);
    }
}
