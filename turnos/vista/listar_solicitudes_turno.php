<?php
// turnos/vista/listar_solicitudes_turno.php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . "/../controlador/controladorTurnos.php";

// Podés validar rol si querés
// if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Admin Produccion') { ... }

$ctrl = new controladorTurnos();

$resp = $ctrl->obtenerSolicitudesPendientes();

echo json_encode($resp);
