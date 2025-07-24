<?php
class modeloReportes {
    private $PDO;

    public function __construct() {
        require_once("../../config/bd.php");
        $con = new bd();
        $this->PDO = $con->conexion();
    }

    public function datosReportes() {
        $resultados = [];

        // 1) Materias Primas Registradas
        $sql1 = "SELECT COUNT(*) as total_materias_primas FROM materiaprima WHERE estado = 'activo'";
        $res1 = $this->PDO->query($sql1);
        $row1 = $res1->fetch(PDO::FETCH_ASSOC);
        $resultados['materias_primas'] = $row1['total_materias_primas'];

        // 2) Pedidos del mes actual
        $sql2 = "SELECT COUNT(*) as total_pedidos_mes 
                 FROM pedidomp 
                 WHERE MONTH(fechaPedido) = MONTH(CURDATE()) 
                   AND YEAR(fechaPedido) = YEAR(CURDATE())";
        $res2 = $this->PDO->query($sql2);
        $row2 = $res2->fetch(PDO::FETCH_ASSOC);
        $resultados['pedidos_mes'] = $row2['total_pedidos_mes'];

        // 3) Proveedores registrados
        $sql3 = "SELECT COUNT(*) as total_proveedores FROM proveedor WHERE estado = 'Activo'";
        $res3 = $this->PDO->query($sql3);
        $row3 = $res3->fetch(PDO::FETCH_ASSOC);
        $resultados['proveedores'] = $row3['total_proveedores'];

        // 4) Ítems sin stock
        $sql4 = "SELECT COUNT(*) as sin_stock FROM materiaprima WHERE stockactual = 0";
        $res4 = $this->PDO->query($sql4);
        $row4 = $res4->fetch(PDO::FETCH_ASSOC);
        $resultados['sin_stock'] = $row4['sin_stock'];

        return $resultados;
    }

    public function datosGraficos(){
        $sql1 = "SELECT 
                    SUM(CASE WHEN stockactual > stockminimo THEN 1 ELSE 0 END) AS sobrestock,
                    SUM(CASE WHEN stockactual = stockminimo THEN 1 ELSE 0 END) AS en_stock_justo,
                    SUM(CASE WHEN stockactual < stockminimo THEN 1 ELSE 0 END) AS bajo_stock
                FROM materiaprima";
        $stmt1 = $this->PDO->query($sql1);
        $stockData = $stmt1->fetch(PDO::FETCH_ASSOC);
        $resultado['stock'] = $stockData;

        // 2️⃣ Pedidos por día (últimos 30 días)
        $sql2 = "
            SELECT fechaPedido AS fecha, COUNT(*) AS cantidad
            FROM pedidomp
            WHERE fechaPedido >= CURDATE() - INTERVAL 40 DAY
            GROUP BY fechaPedido
            ORDER BY fechaPedido";

        $stmt2 = $this->PDO->query($sql2);
        $pedidosData = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        $resultado['pedidos'] = $pedidosData;
        return $resultado;
    }

    public function traerMPListado() {
        $sql = "SELECT mp.nombre, mp.unidad_medida, mp.stockactual, mp.stockminimo, p.nombre as proveedor 
        FROM materiaprima mp
        LEFT JOIN proveedor p ON mp.proveedor = p.id_proveedor
        ORDER BY mp.nombre ASC";
        $stmt = $this->PDO->query($sql);
        $materias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $materias;
    }

    public function traerProveedoresListado(){
        $sql = "SELECT pr.nombre, pr.email, pr.telefono, pr.calle, pr.altura, l.localidad, prov.provincia, pr.estado
        FROM proveedor pr
        JOIN localidades l ON pr.localidad = l.id_localidad
        JOIN provincias prov ON pr.provincia = prov.id_provincia
        ORDER BY pr.nombre ASC";
        $stmt = $this->PDO->query($sql);
        $proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $proveedores;
    }

}


?>