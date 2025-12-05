<?php
   session_start();
   if(!isset($_SESSION['user'])){
       header('Location: login.php');
   }
require_once("../controlador/controladorPedido.php");

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo "ID de pedido no proporcionado.";
    exit;
}

$idPedido = intval($_GET['id']);
$ctrl = new controladorPedido();

$resultado = $ctrl->cancelarPedido($idPedido);

if ($resultado) {
    echo "El pedido fue cancelado exitosamente.";
} else {
    http_response_code(500);
    echo "No se pudo cancelar el pedido.";
}