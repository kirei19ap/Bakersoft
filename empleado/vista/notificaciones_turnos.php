<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . "/../../turnos/controlador/controladorTurnos.php";

$ctrl = new controladorTurnos();

$usuarioLogin = $_SESSION['user'] ?? null;

if (!$usuarioLogin) {
    echo json_encode([
        'ok'        => false,
        'mensaje'   => 'No se pudo identificar al usuario logueado.',
        'pendientes'=> 0
    ]);
    exit;
}

// Rango: hoy → hoy + 14 días
$hoy        = date('Y-m-d');
$en14Dias   = date('Y-m-d', strtotime('+14 days'));

$resp = $ctrl->contarPendientesPorUsuarioLogin($usuarioLogin, $hoy, $en14Dias);

// Siempre devolvemos la misma estructura
echo json_encode([
    'ok'        => $resp['ok'],
    'mensaje'   => $resp['mensaje'],
    'pendientes'=> $resp['pendientes'] ?? 0
]);
