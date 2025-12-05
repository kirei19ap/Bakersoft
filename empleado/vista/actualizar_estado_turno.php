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
        'mensaje' => 'No se pudo identificar al usuario logueado.'
    ]);
    exit;
}

// Buscamos empleado asociado
$empleado = $ctrl->obtenerEmpleadoPorUsuarioId($usuarioIdSesion);

if (!$empleado || empty($empleado['id_empleado'])) {
    echo json_encode([
        'ok'      => false,
        'mensaje' => 'No se encontró un empleado asociado al usuario logueado.'
    ]);
    exit;
}

$idEmpleado = (int)$empleado['id_empleado'];

// Para trazas podés guardar el ID de usuario o el de empleado, lo que prefieras
$usuarioId = (int)$usuarioIdSesion;

$input = file_get_contents('php://input');
$data  = json_decode($input, true);

$idAsignacion = $data['idAsignacion'] ?? null;
$nuevoEstado  = $data['nuevoEstado']  ?? null;

$resp = $ctrl->actualizarEstadoTurnoEmpleado($idAsignacion, $idEmpleado, $nuevoEstado, $usuarioId);

echo json_encode($resp);
