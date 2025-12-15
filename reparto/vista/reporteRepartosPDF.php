<?php
// reporteRepartosPDF.php

// Controladores (mismas rutas que en reportesRepartoListado.php)
require_once("../controlador/controladorReparto.php");
require_once("../controlador/controladorVehiculo.php");

// AJUSTAR ESTA LÍNEA SEGÚN TU PROYECTO
// - Si usás Composer:
//   require_once("../../vendor/autoload.php");
// - Si tenés dompdf en otra carpeta, apuntá ahí:
require_once("../../vendor/autoload.php");

use Dompdf\Dompdf;
use Dompdf\Options;

// ====== Filtros (IGUAL que en reportesRepartoListado.php) ======
$fechaDesde = $_GET['fd'] ?? date('Y-m-01');
$fechaHasta = $_GET['fh'] ?? date('Y-m-d');
$vehiculo   = $_GET['veh'] ?? '';
$estado     = $_GET['est'] ?? 'Todos';

// Controladores (usar el mismo nombre de clase que estés usando en las vistas)
$ctrlReparto  = new controladorReparto();
$ctrlVehiculo = new controladorVehiculo();

// ====== Datos (MISMA LÓGICA QUE EN LA PÁGINA DE LISTADO) ======
$kpis    = $ctrlReparto->obtenerKPIsReparto($fechaDesde, $fechaHasta, $vehiculo ?: null, $estado);
$detalle = $ctrlReparto->obtenerDetalleRepartos($fechaDesde, $fechaHasta, $vehiculo ?: null, $estado);

// Vehículo seleccionado (texto para filtros)
$vehiculoTexto = 'Todos';
if (!empty($vehiculo)) {
    $vehiculos = $ctrlVehiculo->listarVehiculos(true);
    foreach ($vehiculos as $v) {
        if ((string)$v['idVehiculo'] === (string)$vehiculo) {
            $vehiculoTexto = trim($v['patente'] . ' - ' . ($v['descripcion'] ?? ''));
            break;
        }
    }
}

// KPIs derivados (MISMO CÁLCULO QUE EN reportesRepartoListado.php)
$totalRepartos = $kpis['totalRepartos'] ?? 0;
$totalPedidos  = $kpis['totalPedidos'] ?? 0;
$entregados    = $kpis['entregados'] ?? 0;
$cancelados    = $kpis['cancelados'] ?? 0;

$porcEntregados = ($totalPedidos > 0)
    ? round(($entregados / $totalPedidos) * 100, 1)
    : 0;

$porcCancelados = ($totalRepartos > 0)
    ? round(($cancelados / $totalRepartos) * 100, 1)
    : 0;

$promPedidosPorReparto = ($totalRepartos > 0)
    ? round(($totalPedidos / $totalRepartos), 1)
    : 0;

// Helper estado
function badgeEstadoRepartoPDF($estado)
{
    $class = '';
    switch ($estado) {
        case 'Planificado': $class = 'estado-planificado'; break;
        case 'En Curso':    $class = 'estado-encurso';     break;
        case 'Finalizado':  $class = 'estado-finalizado';  break;
        case 'Cancelado':   $class = 'estado-cancelado';   break;
        default:            $class = 'estado-default';     break;
    }
    $estadoEsc = htmlspecialchars($estado, ENT_QUOTES, 'UTF-8');
    return "<span class=\"badge-estado {$class}\">{$estadoEsc}</span>";
}

// Fechas formato dd/mm/yyyy
function fmtFechaCorta($fecha)
{
    if (empty($fecha)) return '';
    return date("d/m/Y", strtotime($fecha));
}

$fechaDesdeTxt = fmtFechaCorta($fechaDesde);
$fechaHastaTxt = fmtFechaCorta($fechaHasta);

// ==================== ARMAR HTML ====================
ob_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Repartos</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #333;
        }
        h1, h2, h3, h4 {
            margin: 0;
            padding: 0;
        }
        .titulo {
            text-align: center;
            margin-bottom: 10px;
        }
        .subtitulo {
            text-align: center;
            font-size: 11px;
            color: #555;
            margin-bottom: 15px;
        }
        .filtros {
            font-size: 10px;
            margin-bottom: 15px;
        }
        .filtros table {
            width: 100%;
        }
        .filtros th {
            text-align: left;
            width: 120px;
        }

        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .kpi-table th, .kpi-table td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
        }
        .kpi-table th {
            background-color: #f3f4f6;
            font-weight: bold;
            font-size: 10px;
        }

        .detalle-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        .detalle-table th, .detalle-table td {
            border: 1px solid #ccc;
            padding: 4px 5px;
            font-size: 9.5px;
        }
        .detalle-table th {
            background-color: #e5e7eb;
            font-weight: bold;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        .badge-estado {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }
        .estado-planificado { background-color: #e5e7eb; color: #374151; }
        .estado-encurso    { background-color: #bfdbfe; color: #1d4ed8; }
        .estado-finalizado { background-color: #bbf7d0; color: #15803d; }
        .estado-cancelado  { background-color: #fecaca; color: #b91c1c; }
        .estado-default    { background-color: #f3f4f6; color: #374151; }

        .small { font-size: 9px; }

        .mt-5 { margin-top: 5px; }
        .mt-10 { margin-top: 10px; }
    </style>
</head>
<body>

    <div class="titulo">
        <h2>BakerSoft - Reporte de Repartos</h2>
    </div>
    <div class="subtitulo">
        Período: <?php echo $fechaDesdeTxt; ?> al <?php echo $fechaHastaTxt; ?>
    </div>

    <div class="filtros">
        <table>
            <tr>
                <th>Vehículo:</th>
                <td><?php echo htmlspecialchars($vehiculoTexto, ENT_QUOTES, 'UTF-8'); ?></td>
                <th>Estado reparto:</th>
                <td><?php echo htmlspecialchars($estado, ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
            <tr>
                <th>Fecha de generación:</th>
                <td colspan="3"><?php echo date("d/m/Y H:i"); ?></td>
            </tr>
        </table>
    </div>

    <h3>Resumen general</h3>
    <table class="kpi-table">
        <tr>
            <th>Repartos en el período</th>
            <th>Pedidos asignados</th>
            <th>Pedidos entregados</th>
            <th>Repartos cancelados</th>
        </tr>
        <tr>
            <td><?php echo $totalRepartos; ?></td>
            <td>
                <?php echo $totalPedidos; ?>
                <span class="small">(Prom. por reparto: <?php echo $promPedidosPorReparto; ?>)</span>
            </td>
            <td>
                <?php echo $entregados; ?>
                <span class="small">(<?php echo $porcEntregados; ?>% del total asignado)</span>
            </td>
            <td>
                <?php echo $cancelados; ?>
                <span class="small">(<?php echo $porcCancelados; ?>% de los repartos)</span>
            </td>
        </tr>
    </table>

    <h3>Detalle de repartos</h3>

    <?php if (empty($detalle)): ?>
        <p class="small mt-10">No se encontraron repartos para los filtros seleccionados.</p>
    <?php else: ?>
        <table class="detalle-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Vehículo</th>
                    <th>Chofer</th>
                    <th class="text-right">Pedidos asignados</th>
                    <th class="text-right">Entregados</th>
                    <th class="text-right">% Cumplimiento</th>
                    <th class="text-center">Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalle as $d): ?>
                    <?php
                        $fechaRep = fmtFechaCorta($d['fechaReparto']);
                        $vehTxt   = trim($d['patente'] . ' - ' . ($d['vehiculoDescripcion'] ?? ''));
                        $chofer   = $d['chofer'] ?? '';
                        $totPed   = (int)$d['totalPedidos'];
                        $entreg   = (int)$d['entregados'];
                        $cumpl    = ($totPed > 0)
                            ? round(($entreg / $totPed) * 100, 1)
                            : 0;
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($fechaRep, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($vehTxt, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($chofer, ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="text-right"><?php echo $totPed; ?></td>
                        <td class="text-right"><?php echo $entreg; ?></td>
                        <td class="text-right"><?php echo $cumpl; ?>%</td>
                        <td class="text-center">
                            <?php echo badgeEstadoRepartoPDF($d['estado']); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</body>
</html>
<?php
$html = ob_get_clean();

// ============ GENERAR PDF ===============
$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html, 'UTF-8');
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$filename = 'reporte_repartos_' . date('Ymd_His') . '.pdf';
$dompdf->stream($filename, ['Attachment' => false]);
exit;
