<?php
session_start();
require_once(__DIR__ . "/../../config/bd.php");
$pdo = (new bd())->conexion();

$nombre = trim($_POST['nombre_rol'] ?? '');
if ($nombre === '') {
  $_SESSION['roles_err'] = "El nombre del rol es obligatorio.";
  header("Location: index.php"); exit;
}

// Unicidad (case-insensitive)
$st = $pdo->prepare("SELECT 1 FROM roles WHERE LOWER(nombre_rol) = LOWER(?)");
$st->execute([$nombre]);
if ($st->fetch()) {
  $_SESSION['roles_err'] = "Ya existe un rol con ese nombre.";
  header("Location: index.php"); exit;
}

$ins = $pdo->prepare("INSERT INTO roles (nombre_rol) VALUES (?)");
$ins->execute([$nombre]);

$_SESSION['roles_msg'] = "Rol creado correctamente.";
header("Location: index.php");
