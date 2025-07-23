<?php
require_once('../config/bd.php');

$bd = new bd();
$pdo = $bd->conexion();

function randomFecha($mesesAtras = 6) {
    $dias = rand(0, $mesesAtras * 30);
    return date('Y-m-d', strtotime("-$dias days"));
}

// Obtener IDs existentes
$proveedores = $pdo->query("SELECT id_proveedor FROM proveedor")->fetchAll(PDO::FETCH_COLUMN);
$materiasPrimas = $pdo->query("SELECT id FROM materiaprima")->fetchAll(PDO::FETCH_COLUMN);
$estados = $pdo->query("SELECT codEstado FROM estadospedidos")->fetchAll(PDO::FETCH_COLUMN);

if (empty($proveedores) || empty($materiasPrimas) || empty($estados)) {
    die("Faltan datos en proveedores, materias primas o estados.");
}

$cantPedidos = 100;

for ($i = 0; $i < $cantPedidos; $i++) {
    $idProveedor = $proveedores[array_rand($proveedores)];
    $fecha = randomFecha(6);
    
    // Ahora seleccionamos estados válidos
    $estado = $estados[array_rand($estados)];

    $stmt = $pdo->prepare("INSERT INTO pedidomp (idProveedor, fechaPedido, Estado) VALUES (?, ?, ?)");
    $stmt->execute([$idProveedor, $fecha, $estado]);
    $idPedido = $pdo->lastInsertId();

    $cantidadItems = rand(1, 4);
    $materiasUsadas = array_rand($materiasPrimas, $cantidadItems);
    if (!is_array($materiasUsadas)) $materiasUsadas = [$materiasUsadas];

    foreach ($materiasUsadas as $idx) {
        $idMP = $materiasPrimas[$idx];
        $cantidad = rand(5, 50);
        $stmtDetalle = $pdo->prepare("INSERT INTO detallepedido (idPedido, idMP, cantidad) VALUES (?, ?, ?)");
        $stmtDetalle->execute([$idPedido, $idMP, $cantidad]);
    }
}

echo "Datos de prueba cargados correctamente.";
?>