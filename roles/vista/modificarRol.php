<?php
session_start();
require_once(__DIR__ . "/../../config/bd.php");
$pdo = (new bd())->conexion();

$id     = (int)($_POST['id_rol'] ?? 0);
$nombre = trim($_POST['nombre_rol'] ?? '');

if ($id <= 0 || $nombre === '') {
  $_SESSION['roles_err'] = "Datos inválidos para editar.";
  header("Location: index.php"); exit;
}

// Unicidad (excluyendo el propio)
$st = $pdo->prepare("SELECT 1 FROM roles WHERE LOWER(nombre_rol) = LOWER(?) AND id_rol <> ?");
$st->execute([$nombre, $id]);
if ($st->fetch()) {
  $_SESSION['roles_err'] = "Ya existe otro rol con ese nombre.";
  header("Location: index.php"); exit;
}

$up = $pdo->prepare("UPDATE roles SET nombre_rol = ? WHERE id_rol = ?");
$up->execute([$nombre, $id]);

$_SESSION['roles_msg'] = "Rol actualizado correctamente.";
header("Location: index.php");
