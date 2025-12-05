<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . "/../../turnos/controlador/controladorTurnos.php";
$ctrl = new controladorTurnos();

// ID de usuario desde sesión (usuarios.id)
$usuarioIdSesion = $_SESSION['user'] ?? null;

if (!$usuarioIdSesion) {
    echo json_encode([
        'ok'      => false,
        'mensaje' => 'No se pudo identificar al usuario logueado.',
        'data'    => []
    ]);
    exit;
}

// Buscamos el empleado asociado a ese usuario
$empleado = $ctrl->obtenerEmpleadoPorUsuarioId($usuarioIdSesion);

if (!$empleado || empty($empleado['id_empleado'])) {
    echo json_encode([
        'ok'      => false,
        'mensaje' => 'No se encontró un empleado asociado al usuario logueado.',
        'data'    => []
    ]);
    exit;
}

$idEmpleado = (int)$empleado['id_empleado'];

$fechaDesde = $_GET['fechaDesde'] ?? '';
$fechaHasta = $_GET['fechaHasta'] ?? '';
$estado     = $_GET['estado']     ?? '';

$resp = $ctrl->listarTurnosEmpleado($idEmpleado, $fechaDesde, $fechaHasta, $estado ?: null);

echo json_encode($resp);
