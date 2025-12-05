<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once("../controlador/controladorTurnos.php");
$ctrl = new controladorTurnos();

$input = file_get_contents('php://input');
$data  = json_decode($input, true);

$idAsignacion   = $data['idAsignacion']   ?? null;
$idEmpleadoNuevo= $data['idEmpleadoNuevo']?? null;

// Ajustá el nombre de la variable de sesión al que uses para el usuario logueado
$usuarioId = $_SESSION['id_usuario'] ?? $_SESSION['user_id'] ?? 0;

$resp = $ctrl->reasignarAsignacion($idAsignacion, $idEmpleadoNuevo, $usuarioId);

echo json_encode($resp);
