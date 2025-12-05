<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once("../controlador/controladorTurnos.php");
$ctrl = new controladorTurnos();

$idAsignacion = $_POST['id'] ?? '';

$resp = $ctrl->eliminarAsignacion($idAsignacion);

echo json_encode($resp);
