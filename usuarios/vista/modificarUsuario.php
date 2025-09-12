<?php
session_start();
require_once(__DIR__."/../../config/bd.php");
$pdo = (new bd())->conexion();

$id           = (int)($_POST['id'] ?? 0);
$usuario      = trim($_POST['usuario'] ?? '');
$nomyapellido = trim($_POST['nomyapellido'] ?? '');
$rol          = ($_POST['rol'] ?? '') === '' ? null : (int)$_POST['rol'];
$estado       = ($_POST['estado'] ?? 'Activo') === 'Inactivo' ? 'Inactivo' : 'Activo';
$pass         = (string)($_POST['contrasena'] ?? '');
$pass2        = (string)($_POST['contrasena2'] ?? '');

if ($id<=0 || $usuario==='') { header("Location: index.php"); exit; }

// único entre no eliminados (excluyéndome)
$st = $pdo->prepare("SELECT 1 FROM usuarios WHERE LOWER(usuario)=LOWER(?) AND id<>? AND eliminado=0");
$st->execute([$usuario, $id]);
if ($st->fetch()) { header("Location: index.php"); exit; }

if ($pass !== '') {
  if ($pass !== $pass2) { header("Location: index.php"); exit; }
  $hash = md5($pass);
  $sql = "UPDATE usuarios SET usuario=?, contrasena=?, nomyapellido=?, rol=?, estado=? WHERE id=?";
  $params = [$usuario, $hash, $nomyapellido, $rol, $estado, $id];
} else {
  $sql = "UPDATE usuarios SET usuario=?, nomyapellido=?, rol=?, estado=? WHERE id=?";
  $params = [$usuario, $nomyapellido, $rol, $estado, $id];
}
$up = $pdo->prepare($sql);
$up->execute($params);

header("Location: index.php");

