<?php
// empleado/vista/solicitar_cambio_turno.php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . "/../../turnos/controlador/controladorTurnos.php";

try {
    $ctrl = new controladorTurnos();

    // usuario logueado
    $usuarioLogin = $_SESSION['user'] ?? null;
    if (!$usuarioLogin) {
        echo json_encode([
            'ok'      => false,
            'mensaje' => 'No se pudo identificar al usuario logueado.'
        ]);
        exit;
    }

    // Obtenemos el empleado asociado (ya lo usaste en otros endpoints)
    $empleado = $ctrl->obtenerEmpleadoPorUsuarioId($usuarioLogin);
    if (!$empleado || empty($empleado['id_empleado'])) {
        echo json_encode([
            'ok'      => false,
            'mensaje' => 'No se encontró un empleado asociado al usuario logueado.'
        ]);
        exit;
    }

    $idEmpleado   = (int)$empleado['id_empleado'];
    $idAsignacion = $_POST['idAsignacion'] ?? 0;
    $tipo         = $_POST['tipo'] ?? 'Rechazo';
    $motivo       = $_POST['motivo'] ?? '';

    $resp = $ctrl->solicitarCambioTurnoEmpleado($idAsignacion, $idEmpleado, $tipo, $motivo);

    echo json_encode($resp);

} catch (Throwable $e) {
    // Si algo explota, al menos devolvemos un JSON controlado
    echo json_encode([
        'ok'      => false,
        'mensaje' => 'Error interno al procesar la solicitud.',
        'detalle' => $e->getMessage()
    ]);
}
