<?php
// turnos/vista/gestionar_solicitud_turno.php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . "/../controlador/controladorTurnos.php";

// if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'Admin Produccion') { ... }

$idSolicitud = $_POST['idSolicitud'] ?? 0;
$accion      = $_POST['accion']      ?? '';

$ctrl = new controladorTurnos();
$resp = $ctrl->gestionarSolicitudTurno($idSolicitud, $accion);

echo json_encode($resp);
