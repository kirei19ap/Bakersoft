<?php
session_start();

require_once("../controlador/controladorPedido.php");
$controlador = new controladorPedido();

// Validar que venga el id del pedido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo "ID de pedido inválido.";
    exit;
}

$idPedido = (int)$_GET['id'];

// 🔐 Ajustá este nombre al que uses realmente en el login
// Ejemplos habituales: idUsuario, id_usuario, usuario_id, etc.
if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo "Sesión no válida. Vuelva a iniciar sesión.";
    exit;
}

$idUsuario = (int)$_SESSION['user'];

// Llamamos al controlador
$resultado = $controlador->recibirPedido($idPedido, $idUsuario);

if ($resultado) {
    echo "Pedido de materia prima recibido correctamente. El stock fue actualizado.";
} else {
    // Puede ser porque el estado no sea Registrado, no tenga detalle, etc.
    http_response_code(400);
    echo "No se pudo recibir el pedido. Verifique que el pedido exista y esté en estado Registrado.";
}
