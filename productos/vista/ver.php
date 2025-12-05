<?php
session_start();
require_once __DIR__ . '/../controlador/controladorProductos.php';
header('Content-Type: application/json; charset=utf-8');

try {
  $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
  if ($id <= 0) throw new Exception('ID inválido');

  $ctrl = new controladorProducto();
  $detalle = $ctrl->ver($id);

  echo json_encode($detalle, JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}

