<?php
// turnos/vista/notificaciones_solicitudes_turno.php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . "/../controlador/controladorTurnos.php";

// Si querés, podés limitar por rol:
// if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Admin Produccion') {
//     echo json_encode(['ok' => false, 'pendientes' => 0, 'mensaje' => 'Acceso no autorizado']);
//     exit;
// }

$ctrl = new controladorTurnos();
$resp = $ctrl->obtenerCantidadSolicitudesPendientes();

echo json_encode($resp);
