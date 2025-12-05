<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once("../controlador/controladorTurnos.php");
$ctrl = new controladorTurnos();

$idAsignacion = $_GET['id'] ?? $_POST['id'] ?? '';

$resp = $ctrl->verAsignacion($idAsignacion);

echo json_encode($resp);
