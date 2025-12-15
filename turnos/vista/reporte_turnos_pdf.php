<?php
// turnos/vista/reporte_turnos_pdf.php
session_start();

require_once("../controlador/controladorTurnos.php");

// AJUSTAR segun tu estructura de vendor / dompdf
require_once("../../vendor/autoload.php"); // mismo que uses en pedidos

use Dompdf\Dompdf;

$ctrlTurnos = new controladorTurnos();

$fechaDesdeTurnos = $_POST['desde_turnos']  ?? date('Y-m-01');
$fechaHastaTurnos = $_POST['hasta_turnos']  ?? date('Y-m-t');
$estadoTurnoRep   = $_POST['estado_turno']  ?? '';
$empleadoRep      = $_POST['empleado']      ?? '';

$listadoTurnos = $ctrlTurnos->obtenerReporteTurnos(
    $empleadoRep ?: null,
    $fechaDesdeTurnos,
    $fechaHastaTurnos,
    $estadoTurnoRep
);

function formatearFechaDMY($fechaYmd) {
    if (!$fechaYmd) return '';
    $partes = explode('-', $fechaYmd);
    if (count($partes) !== 3) return $fechaYmd;
    return $partes[2] . '-' . $partes[1] . '-' . $partes[0];
}

$estadoTexto = 'Todos';
if ($estadoTurnoRep === 'Asignado')   $estadoTexto = 'Asignado';
if ($estadoTurnoRep === 'Confirmado') $estadoTexto = 'Confirmado';
if ($estadoTurnoRep === 'Finalizado') $estadoTexto = 'Finalizado';
if ($estadoTurnoRep === 'Cancelado')  $estadoTexto = 'A reasignar';

$html = '
<html>
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; }
    h1 { font-size: 16pt; margin-bottom: 5px; }
    p  { margin: 4px 0; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { border: 1px solid #000; padding: 4px; }
    th { background-color: #f0f0f0; }
    .texto-small { font-size: 9pt; }
  </style>
</head>
<body>
  <h1>BakerSoft - Reporte de turnos laborales</h1>
  <p class="texto-small">
    Rango de fechas: <strong>' . formatearFechaDMY($fechaDesdeTurnos) . '</strong>
    al <strong>' . formatearFechaDMY($fechaHastaTurnos) . '</strong><br>
    Estado: <strong>' . $estadoTexto . '</strong>
  </p>
  <table>
    <thead>
      <tr>
        <th style="width: 12%;">Fecha</th>
        <th style="width: 16%;">Turno</th>
        <th style="width: 14%;">Horario</th>
        <th style="width: 34%;">Empleado</th>
        <th style="width: 12%;">Legajo</th>
        <th style="width: 12%;">Estado</th>
      </tr>
    </thead>
    <tbody>';

if (empty($listadoTurnos)) {
    $html .= '
      <tr>
        <td colspan="6" style="text-align:center;">No se encontraron turnos con los filtros seleccionados.</td>
      </tr>';
} else {
    foreach ($listadoTurnos as $t) {
        $estado = $t['estadoAsignacion'] ?? '';
        if ($estado === 'Asignado') {
            $estadoLabel = 'Asignado';
        } elseif ($estado === 'Confirmado') {
            $estadoLabel = 'Confirmado';
        } elseif ($estado === 'Finalizado') {
            $estadoLabel = 'Finalizado';
        } elseif ($estado === 'Cancelado') {
            $estadoLabel = 'A reasignar';
        } else {
            $estadoLabel = 'N/A';
        }

        $html .= '
      <tr>
        <td>' . formatearFechaDMY($t['fecha']) . '</td>
        <td>' . htmlspecialchars($t['nombreTurno']) . '</td>
        <td>' . substr($t['horaDesde'], 0, 5) . ' - ' . substr($t['horaHasta'], 0, 5) . '</td>
        <td>' . htmlspecialchars($t['apellidoEmpleado'] . ', ' . $t['nombreEmpleado']) . '</td>
        <td>' . htmlspecialchars($t['legajo'] ?? '-') . '</td>
        <td>' . $estadoLabel . '</td>
      </tr>';
    }
}

$html .= '
    </tbody>
  </table>
</body>
</html>
';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// descarga directa
$dompdf->stream('reporte_turnos_laborales.pdf', ['Attachment' => true]);
exit;
