<?php
session_start();
require_once(__DIR__."/../../config/bd.php");
$pdo = (new bd())->conexion();

$usuario      = trim($_POST['usuario'] ?? '');
$nombre       = trim($_POST['nombre_usu'] ?? '');
$apellido     = trim($_POST['apellido_usu'] ?? '');
$nomyapellido = trim($nombre.' '.$apellido);
$rol          = ($_POST['rol'] ?? '') === '' ? null : (int)$_POST['rol'];
$estado       = ($_POST['estado'] ?? 'Activo') === 'Inactivo' ? 'Inactivo' : 'Activo';
$pass         = (string)($_POST['contrasena'] ?? '');
$pass2        = (string)($_POST['contrasena_conf'] ?? '');

if ($usuario==='' || $pass==='' || $pass!==$pass2) { /* manejar error/redirect */ }

// único entre no eliminados
$st = $pdo->prepare("SELECT 1 FROM usuarios WHERE LOWER(usuario)=LOWER(?) AND eliminado=0");
$st->execute([$usuario]);
if ($st->fetch()) { /* manejar error/redirect */ }

// Mantener compatibilidad hashing actual (md5); cambiaremos luego si querés
$hash = md5($pass);

$ins = $pdo->prepare("
  INSERT INTO usuarios (usuario, contrasena, nomyapellido, rol, estado)
  VALUES (?, ?, ?, ?, ?)
");
$ins->execute([$usuario, $hash, $nomyapellido, $rol, $estado]);

header("Location: index.php");
