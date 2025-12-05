<?php
// productos/vista/editar.php
session_start();

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'ok'  => false,
            'msg' => 'Método no permitido'
        ]);
        exit;
    }

    require_once("../controlador/controladorProductos.php");
    $ctrl = new controladorProducto();

    // Campos del formulario
    $idProducto = isset($_POST['idProducto']) ? (int)$_POST['idProducto'] : 0;
    $nombre     = trim($_POST['nombre'] ?? '');
    $categoria  = trim($_POST['categoria'] ?? '');
    $unidad     = trim($_POST['unidad'] ?? '');
    $precio     = isset($_POST['precio_venta']) ? (float)$_POST['precio_venta'] : 0.0;

    // Componentes (JSON enviado en el hidden componentesJsonEdit)
    $componentesJson = $_POST['componentes'] ?? '[]';
    $componentes = json_decode($componentesJson, true);
    if (!is_array($componentes)) {
        $componentes = [];
    }

    // Validaciones básicas
    if ($idProducto <= 0) {
        throw new Exception('ID de producto inválido.');
    }
    if ($nombre === '' || $categoria === '' || $unidad === '') {
        throw new Exception('Datos obligatorios incompletos.');
    }
    if ($precio < 0) {
        throw new Exception('El precio de venta no puede ser negativo.');
    }
    if (count($componentes) === 0) {
        throw new Exception('Debe agregar al menos una materia prima.');
    }

    // Llamada al controlador (firma nueva con precio_venta)
    $ok = $ctrl->editar(
        $idProducto,
        $nombre,
        $categoria,
        $unidad,
        $precio,
        $componentes
    );

    if ($ok) {
        echo json_encode([
            'ok'  => true,
            'msg' => 'Producto actualizado correctamente.'
        ]);
    } else {
        echo json_encode([
            'ok'  => false,
            'msg' => 'No se pudieron guardar los cambios.'
        ]);
    }

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'ok'  => false,
        'msg' => $e->getMessage()
    ]);
}
