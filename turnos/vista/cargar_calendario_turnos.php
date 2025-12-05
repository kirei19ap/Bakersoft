<?php
// turnos/vista/cargar_calendario_turnos.php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . "/../controlador/controladorTurnos.php";

// Opcional: validar que el rol sea Admin Produccion
// if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Admin Produccion') {
//     echo json_encode([
//         'ok'      => false,
//         'mensaje' => 'Acceso no autorizado',
//         'turnos'  => [],
//         'data'    => []
//     ]);
//     exit;
// }

$ctrl = new controladorTurnos();

$fechaDesde = $_GET['desde'] ?? '';
$fechaHasta = $_GET['hasta'] ?? '';

$resp = $ctrl->obtenerDatosCalendarioProduccion($fechaDesde, $fechaHasta);

echo json_encode($resp);
