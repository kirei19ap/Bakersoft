<?php
session_start();
require_once(__DIR__ . "/../../config/bd.php");
$pdo = (new bd())->conexion();

$id = (int)($_POST['id_rol'] ?? 0);
if ($id <= 0) {
  $_SESSION['roles_err'] = "ID inválido.";
  header("Location: index.php"); exit;
}

// No borrar si hay usuarios con este rol
$chk = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE rol = ?");
$chk->execute([$id]);
if ((int)$chk->fetchColumn() > 0) {
  $_SESSION['roles_err'] = "No se puede eliminar: existen usuarios asignados a este rol.";
  header("Location: index.php"); exit;
}

$del = $pdo->prepare("DELETE FROM roles WHERE id_rol = ?");
$del->execute([$id]);

$_SESSION['roles_msg'] = "Rol eliminado correctamente.";
header("Location: index.php");
