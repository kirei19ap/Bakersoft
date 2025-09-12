<?php
    session_start();
if (!isset($_SESSION['user'])) {
    header('Location: ../../index.php');
    exit();
}
require_once("../controlador/controladorReportes.php");
$obj = new controladorReportes();
$datosReporte = $obj->datosGraficos();
if ($datosReporte) {
    // Convertir a JSON
    header('Content-Type: application/json');
    echo json_encode($datosReporte);
} else {
    // Manejar error
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener los datos de reportes']);
}

?>