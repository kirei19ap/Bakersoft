<?php
// empleado/vista/cargar_turnos_empleado_calendario.php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . "/../../turnos/controlador/controladorTurnos.php";

$ctrl = new controladorTurnos();

// usuario logueado (username)
$usuarioLogin = $_SESSION['user'] ?? null;
if (!$usuarioLogin) {
    echo json_encode([
        'ok'    => false,
        'mensaje' => 'No se pudo identificar al usuario logueado.',
        'data'  => []
    ]);
    exit;
}

// buscamos el empleado asociado
$empleado = $ctrl->obtenerEmpleadoPorUsuarioId($usuarioLogin);
if (!$empleado || empty($empleado['id_empleado'])) {
    echo json_encode([
        'ok'    => false,
        'mensaje' => 'No se encontró un empleado asociado al usuario logueado.',
        'data'  => []
    ]);
    exit;
}

$idEmpleado = (int)$empleado['id_empleado'];

// recibimos rango de fechas para el mes
$fechaDesde = $_GET['desde'] ?? '';
$fechaHasta = $_GET['hasta'] ?? '';

// si no vienen, tomamos mes actual
if (empty($fechaDesde) || empty($fechaHasta)) {
    $anio  = date('Y');
    $mes   = date('m');
    $desde = "$anio-$mes-01";
    $ultimoDia = date('t', strtotime($desde)); // cant días mes
    $hasta = "$anio-$mes-$ultimoDia";
} else {
    $desde = $fechaDesde;
    $hasta = $fechaHasta;
}

// Traemos TODOS los estados en el rango (estado = null)
$resp = $ctrl->listarTurnosEmpleado($idEmpleado, $desde, $hasta, null);

echo json_encode($resp);
