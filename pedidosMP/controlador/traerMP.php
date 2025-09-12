<?php

require_once("../controlador/controladorPedido.php");

$MPs = new controladorPedido();
$mpbyproveedor = $MPs -> mostrarMPporProveedor($_GET['id_proveedor']);
header('Content-Type: application/json');
echo json_encode($mpbyproveedor);