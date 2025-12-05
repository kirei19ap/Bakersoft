<?php
session_start();
require_once __DIR__ . '/../controlador/controladorProductos.php';
header('Content-Type: application/json; charset=utf-8');

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Método inválido');

  $id = (int)($_POST['idProducto'] ?? 0);
  if ($id <= 0) throw new Exception('ID inválido');

  $ctrl = new controladorProducto();
  $ok = $ctrl->desactivar($id);

  echo json_encode(['ok' => (bool)$ok], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
