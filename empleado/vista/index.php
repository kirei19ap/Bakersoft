<?php
session_start();
require_once '../db.php'; // tu PDO

$usuarioId = $_SESSION['usuario_id'] ?? null;
if (!$usuarioId || ($_SESSION['rol'] ?? null) === null) { header('Location: /login.php'); exit; }

// Traer empleado por usuario_id
$stmt = $pdo->prepare("
  SELECT e.*, p.provincia AS nombre_prov, l.localidad AS nombre_loc
  FROM empleados e
  LEFT JOIN provincias p ON p.id_provincia = e.provincia
  LEFT JOIN localidades l ON l.id_localidad = e.localidad
  WHERE e.usuario_id = :uid
  LIMIT 1
");
$stmt->execute([':uid' => $usuarioId]);
$empleado = $stmt->fetch(PDO::FETCH_ASSOC);
?>