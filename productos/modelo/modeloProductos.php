<?php

class modeloProducto
{
    private $pdo;
    public function __construct()
    {
        require_once("../../config/bd.php");
        $con = new bd();
        $this->pdo = $con->conexion();
    }

    public function obtenerCategoriasProd(): array
    {
        $sql = "SELECT idCatProd, nombre FROM categoriaprod ORDER BY nombre";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerMateriasPrimas(): array
    {
        $sql = "SELECT id, nombre, unidad_medida, stockactual
                FROM materiaprima
                WHERE LOWER(estado) = 'activo'
                ORDER BY nombre";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarProductos(): array
    {
        $sql = "SELECT 
                    p.idProducto, 
                    p.nombre, 
                    p.unidad_medida, 
                    p.estado,
                    c.nombre      AS categoria,
                    p.precio_venta,          -- <<< nuevo campo disponible para listas
                    p.fecha_alta             -- ya lo teníamos para stats/reportes
                FROM producto p
                INNER JOIN categoriaprod c ON c.idCatProd = p.categoriaProd
                ORDER BY p.nombre";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function crearProducto(array $producto, array $componentes): int
    {
        try {
            $this->pdo->beginTransaction();

            // AHORA incluimos precio_venta en el INSERT
            $sqlP = "INSERT INTO producto 
                        (nombre, categoriaProd, unidad_medida, descripcion, precio_venta, estado)
                     VALUES 
                        (:nombre, :categoriaProd, :unidad_medida, :descripcion, :precio_venta, 'Activo')";
            $stP = $this->pdo->prepare($sqlP);
            $stP->execute([
                ':nombre'        => $producto['nombre'],
                ':categoriaProd' => $producto['categoriaProd'],
                ':unidad_medida' => $producto['unidad_medida'],
                ':descripcion'   => $producto['descripcion'] ?? null,
                ':precio_venta'  => $producto['precio_venta'],   // <<< nuevo parámetro
            ]);

            $idProducto = (int)$this->pdo->lastInsertId();

            $sqlD = "INSERT INTO detalleproducto (idProducto, idMP, cantidad, merma)
                     VALUES (:idProducto, :idMP, :cantidad, :merma)";
            $stD = $this->pdo->prepare($sqlD);

            foreach ($componentes as $comp) {
                if (!isset($comp['idMP'], $comp['cantidad']) || $comp['cantidad'] <= 0) {
                    throw new Exception("Componente inválido");
                }
                $stD->execute([
                    ':idProducto' => $idProducto,
                    ':idMP'       => (int)$comp['idMP'],
                    ':cantidad'   => (float)$comp['cantidad'],
                    ':merma'      => isset($comp['merma']) && $comp['merma'] !== '' ? (float)$comp['merma'] : null,
                ]);
            }

            $this->pdo->commit();
            return $idProducto;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /* ====== LECTURAS ====== */
    public function obtener(int $idProducto): array
    {
        $sql = "SELECT 
                    p.idProducto,
                    p.nombre,
                    p.categoriaProd,              -- id de categoría
                    c.nombre        AS categoria, -- nombre legible
                    p.unidad_medida,
                    p.estado,
                    p.precio_venta,              -- <<< nuevo
                    p.fecha_alta                 -- ya estaba
                FROM producto p
                LEFT JOIN categoriaprod c ON c.idCatProd = p.categoriaProd
                WHERE p.idProducto = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$idProducto]);
        $prod = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$prod) {
            throw new Exception('Producto no encontrado');
        }

        // Composición
        $sqlC = "SELECT 
                    d.idMP,
                    m.nombre        AS mp,
                    m.unidad_medida AS unidad_medida,
                    d.cantidad
                FROM detalleproducto d
                JOIN materiaprima m ON m.id = d.idMP
                WHERE d.idProducto = ?
                ORDER BY m.nombre ASC";
        $stmtC = $this->pdo->prepare($sqlC);
        $stmtC->execute([$idProducto]);
        $prod['componentes'] = $stmtC->fetchAll(PDO::FETCH_ASSOC);

        return $prod;
    }

    /* ====== VALIDACIONES ====== */
    public function existeNombre(string $nombre, ?int $excluirId = null): bool
    {
        if ($excluirId) {
            $sql = "SELECT 1 FROM producto WHERE nombre = ? AND idProducto <> ? LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$nombre, $excluirId]);
        } else {
            $sql = "SELECT 1 FROM producto WHERE nombre = ? LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$nombre]);
        }
        return (bool)$stmt->fetchColumn();
    }

    /* ====== CAMBIOS ====== */
    public function editar(
        int $id,
        string $nombre,
        string $categoria,
        string $unidad,
        float $precio_venta,   // <<< nuevo parámetro
        array $componentes
    ): bool {
        if ($this->existeNombre($nombre, $id)) {
            throw new Exception("Ya existe un producto con ese nombre");
        }

        $this->pdo->beginTransaction();
        try {
            // Cabecera: ahora también actualizamos precio_venta
            $sql = "UPDATE producto
                    SET nombre = ?, 
                        categoriaProd = ?, 
                        unidad_medida = ?,
                        precio_venta = ?      -- <<< nuevo campo en UPDATE
                    WHERE idProducto = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $nombre,
                (int)$categoria,
                $unidad,
                $precio_venta,
                $id
            ]);

            // Reemplazo de composición
            $this->pdo->prepare("DELETE FROM detalleproducto WHERE idProducto = ?")->execute([$id]);

            $sqlIns = "INSERT INTO detalleproducto (idProducto, idMP, cantidad) VALUES (?,?,?)";
            $stmtIns = $this->pdo->prepare($sqlIns);
            foreach ($componentes as $c) {
                $idMP = (int)($c['idMP'] ?? 0);
                $cant = (float)($c['cantidad'] ?? 0);
                if ($idMP > 0 && $cant > 0) {
                    $stmtIns->execute([$id, $idMP, $cant]);
                }
            }

            $this->pdo->commit();
            return true;
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function desactivar(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE producto SET estado = 'Inactivo' WHERE idProducto = ?");
        return $stmt->execute([$id]);
    }

    public function activar($idProducto)
    {
        $sql = "UPDATE producto SET estado = 'Activo' WHERE idProducto = ?";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([(int)$idProducto]);
    }

    /* ====== ESTADÍSTICAS ====== */
    public function estadisticasProductos(string $desde, string $hasta): array
    {
        $sqlResumen = "SELECT
                          COUNT(*) AS total,
                          SUM(CASE WHEN estado = 'Activo' THEN 1 ELSE 0 END)  AS activos,
                          SUM(CASE WHEN estado <> 'Activo' THEN 1 ELSE 0 END) AS inactivos
                       FROM producto
                       WHERE DATE(fecha_alta) BETWEEN :desde AND :hasta";
        $stmt = $this->pdo->prepare($sqlResumen);
        $stmt->execute([
            ':desde' => $desde,
            ':hasta' => $hasta
        ]);
        $resumen = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total'     => 0,
            'activos'   => 0,
            'inactivos' => 0,
        ];

        $sqlPorFecha = "SELECT 
                            DATE(fecha_alta) AS fecha,
                            COUNT(*)         AS cantidad
                        FROM producto
                        WHERE DATE(fecha_alta) BETWEEN :desde AND :hasta
                        GROUP BY DATE(fecha_alta)
                        ORDER BY DATE(fecha_alta)";
        $stmt = $this->pdo->prepare($sqlPorFecha);
        $stmt->execute([
            ':desde' => $desde,
            ':hasta' => $hasta
        ]);
        $porFecha = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sqlPorCategoria = "SELECT 
                                c.nombre AS categoria,
                                COUNT(*) AS cantidad
                            FROM producto p
                            INNER JOIN categoriaprod c ON c.idCatProd = p.categoriaProd
                            WHERE DATE(p.fecha_alta) BETWEEN :desde AND :hasta
                            GROUP BY c.idCatProd, c.nombre
                            ORDER BY c.nombre";
        $stmt = $this->pdo->prepare($sqlPorCategoria);
        $stmt->execute([
            ':desde' => $desde,
            ':hasta' => $hasta
        ]);
        $porCategoria = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'resumen'       => $resumen,
            'por_fecha'     => $porFecha,
            'por_categoria' => $porCategoria,
        ];
    }

    /* ====== REPORTES ====== */
    public function productosReporte(?string $desde = null, ?string $hasta = null): array
    {
        $params = [];
        $where  = "";

        if ($desde !== null && $hasta !== null && $desde !== "" && $hasta !== "") {
            $where = "WHERE DATE(p.fecha_alta) BETWEEN :desde AND :hasta";
            $params[':desde'] = $desde;
            $params[':hasta'] = $hasta;
        }

        $sql = "SELECT 
                    p.idProducto,
                    p.nombre,
                    c.nombre       AS categoria,
                    p.unidad_medida,
                    p.estado,
                    p.fecha_alta,
                    p.precio_venta        -- <<< también disponible para futuro
                FROM producto p
                INNER JOIN categoriaprod c ON c.idCatProd = p.categoriaProd
                $where
                ORDER BY p.nombre";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
