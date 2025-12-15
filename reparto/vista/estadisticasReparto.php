<?php
$currentPage = 'reportesReparto';

include_once("../../includes/head_app.php");
require_once("../controlador/controladorReparto.php");
require_once("../controlador/controladorVehiculo.php");

// Controladores
$ctrlReparto  = new ControladorReparto();
$ctrlVehiculo = new ControladorVehiculo();

// Filtros
$fechaDesde = $_GET['fd'] ?? date('Y-m-01');   // primer día del mes
$fechaHasta = $_GET['fh'] ?? date('Y-m-d');    // hoy
$vehiculo   = $_GET['veh'] ?? '';
$estado     = $_GET['est'] ?? 'Todos';

// Datos para KPIs y detalle
$kpis    = $ctrlReparto->obtenerKPIsReparto($fechaDesde, $fechaHasta, $vehiculo ?: null, $estado);
$detalle = $ctrlReparto->obtenerDetalleRepartos($fechaDesde, $fechaHasta, $vehiculo ?: null, $estado);

// Lista de vehículos
$vehiculos = $ctrlVehiculo->listarVehiculos(true); // sólo activos si tu método lo soporta

// Helper para badge de estado (por si lo necesitás luego)
function badgeEstadoReparto($estado)
{
    switch ($estado) {
        case 'Planificado':
            $class = 'bg-secondary';
            break;
        case 'En Curso':
            $class = 'bg-info';
            break;
        case 'Finalizado':
            $class = 'bg-success';
            break;
        case 'Cancelado':
            $class = 'bg-danger';
            break;
        default:
            $class = 'bg-light text-dark';
            break;
    }
    return "<span class='badge {$class}'>" . htmlspecialchars($estado) . "</span>";
}

// ==== KPIs derivados ====
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


// ==== Datos agregados para gráficos ====

// Global entregados vs no entregados
$globalEntregados    = 0;
$globalNoEntregados  = 0;

// Repartos por estado
$repartosPorEstado   = [
    'Planificado' => 0,
    'En Curso'    => 0,
    'Finalizado'  => 0,
    'Cancelado'   => 0,
];

// Pedidos entregados por vehículo
$pedidosPorVehiculo = [];

foreach ($detalle as $d) {
    $totalPed = (int)$d['totalPedidos'];
    $entreg   = (int)$d['entregados'];
    $noEnt    = max(0, $totalPed - $entreg);

    $globalEntregados   += $entreg;
    $globalNoEntregados += $noEnt;

    // Estado reparto
    if (isset($repartosPorEstado[$d['estado']])) {
        $repartosPorEstado[$d['estado']]++;
    } else {
        $repartosPorEstado[$d['estado']] = 1;
    }

    // Vehículo
    $vehKey = trim($d['patente'] . ' - ' . ($d['vehiculoDescripcion'] ?? ''));
    if (!isset($pedidosPorVehiculo[$vehKey])) {
        $pedidosPorVehiculo[$vehKey] = [
            'total'      => 0,
            'entregados' => 0,
        ];
    }
    $pedidosPorVehiculo[$vehKey]['total']      += $totalPed;
    $pedidosPorVehiculo[$vehKey]['entregados'] += $entreg;
}

$totalGlobal = $globalEntregados + $globalNoEntregados;

$porcEntregados = ($totalGlobal > 0)
    ? round(($globalEntregados / $totalGlobal) * 100, 1)
    : 0;

$porcNoEntregados = ($totalGlobal > 0)
    ? round(($globalNoEntregados / $totalGlobal) * 100, 1)
    : 0;

// Arrays para Chart.js: estados
$chartEstadosLabels = array_keys($repartosPorEstado);
$chartEstadosData   = array_values($repartosPorEstado);

// Arrays para Chart.js: pedidos por vehículo (sólo entregados)
$chartVehLabels = array_keys($pedidosPorVehiculo);
$chartVehData   = [];
foreach ($pedidosPorVehiculo as $veh => $datos) {
    $chartVehData[] = (int)$datos['entregados'];
}

// Duración promedio por zona (nuevo dataset)
$duracionZonas = $ctrlReparto->obtenerDuracionPromedioPorZona(
    $fechaDesde,
    $fechaHasta,
    $vehiculo ?: null,
    ($estado === 'Todos') ? null : $estado
);

$chartDuracionLabels = [];
$chartDuracionData   = [];

if (!empty($duracionZonas)) {
    foreach ($duracionZonas as $fila) {
        $chartDuracionLabels[] = $fila['zona'];
        // redondeamos a 1 decimal para mostrar
        $chartDuracionData[]   = round((float)$fila['duracionPromedio'], 1);
    }
}

?>

<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Estadísticas de Reparto</h1>
</div>

<div class="contenido-principal">

    <!-- FILTROS -->
    <form method="get" class="row mb-4">

        <div class="col-md-3">
            <label class="form-label">Fecha Desde</label>
            <input type="date" name="fd" class="form-control"
                value="<?php echo htmlspecialchars($fechaDesde); ?>">
        </div>

        <div class="col-md-3">
            <label class="form-label">Fecha Hasta</label>
            <input type="date" name="fh" class="form-control"
                value="<?php echo htmlspecialchars($fechaHasta); ?>">
        </div>

        <div class="col-md-3">
            <label class="form-label">Vehículo</label>
            <select name="veh" class="form-select">
                <option value="">Todos</option>
                <?php foreach ($vehiculos as $v): ?>
                    <?php
                    $sel = ((string)$vehiculo === (string)$v['idVehiculo']) ? 'selected' : '';
                    $textoVeh = $v['patente'] . ' - ' . ($v['descripcion'] ?? '');
                    ?>
                    <option value="<?php echo $v['idVehiculo']; ?>" <?php echo $sel; ?>>
                        <?php echo htmlspecialchars(trim($textoVeh)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-3">
            <label class="form-label">Estado</label>
            <select name="est" class="form-select">
                <?php
                $estadosPosibles = ['Todos', 'Planificado', 'En Curso', 'Finalizado', 'Cancelado'];
                foreach ($estadosPosibles as $est) {
                    $sel = ($estado === $est) ? 'selected' : '';
                    echo "<option value=\"{$est}\" {$sel}>{$est}</option>";
                }
                ?>
            </select>
        </div>

        <div class="col-12 mt-3 d-flex justify-content-end">
            <button class="btn btn-primary">
                <ion-icon name="search-outline"></ion-icon> Filtrar
            </button>
        </div>
    </form>


    <!-- KPIs -->
    <div class="row mb-4">

        <!-- Repartos -->
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm h-100 kpi-card kpi-blue">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted fw-bold">Repartos en el período</small>
                        <ion-icon name="calendar-outline" class="kpi-card-icon"></ion-icon>
                    </div>
                    <h3 class="mt-2 mb-0"><?php echo $totalRepartos; ?></h3>
                </div>
            </div>
        </div>

        <!-- Pedidos asignados -->
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm h-100 kpi-card kpi-green">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted fw-bold">Pedidos asignados</small>
                        <ion-icon name="cube-outline" class="kpi-card-icon"></ion-icon>
                    </div>
                    <h3 class="mt-2 mb-0"><?php echo $totalPedidos; ?></h3>
                    <small class="text-muted">Promedio: <?php echo $promPedidosPorReparto; ?></small>
                </div>
            </div>
        </div>

        <!-- Entregados -->
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm h-100 kpi-card kpi-teal">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted fw-bold">Pedidos entregados</small>
                        <ion-icon name="checkmark-done-circle-outline" class="kpi-card-icon"></ion-icon>
                    </div>
                    <h3 class="mt-2 mb-0"><?php echo $entregados; ?></h3>
                    <small class="text-muted"><?php echo $porcEntregados; ?>% del total</small>
                </div>
            </div>
        </div>

        <!-- Cancelados -->
        <div class="col-md-3 mb-3">
            <div class="card shadow-sm h-100 kpi-card kpi-red">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted fw-bold">Repartos cancelados</small>
                        <ion-icon name="close-circle-outline" class="kpi-card-icon"></ion-icon>
                    </div>
                    <h3 class="mt-2 mb-0"><?php echo $cancelados; ?></h3>
                    <small class="text-muted"><?php echo $porcCancelados; ?>% del total</small>
                </div>
            </div>
        </div>

    </div>



    <!-- GRÁFICOS -->
    <div class="row mb-4">

        <!-- Doughnut: entregados vs no entregados -->
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title">Pedidos entregados vs no entregados</h6>
                    <div style="height: 260px;">
                        <canvas id="chartEntregados"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barras: repartos por estado -->
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title">Repartos por estado</h6>
                    <div style="height: 260px;">
                        <canvas id="chartEstadosReparto"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="row mb-4">

        <!-- Barras: pedidos entregados por vehículo -->
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title">Pedidos entregados por vehículo</h6>
                    <div style="height: 260px;">
                        <canvas id="chartVehiculos"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Barras: duración promedio por zona -->
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title">Duración promedio por zona (min)</h6>
                    <div style="height: 260px;">
                        <canvas id="chartDuracionZona"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>


</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {

        const porcEntregados = <?php echo $porcEntregados; ?>;
        const porcNoEntregados = <?php echo $porcNoEntregados; ?>;


        const estadosLabels = <?php echo json_encode($chartEstadosLabels); ?>;
        const estadosData = <?php echo json_encode($chartEstadosData); ?>;

        const vehLabels = <?php echo json_encode($chartVehLabels); ?>;
        const vehData = <?php echo json_encode($chartVehData); ?>;

        const duracionLabels = <?php echo json_encode($chartDuracionLabels); ?>;
        const duracionData = <?php echo json_encode($chartDuracionData); ?>;

        // 1) Doughnut: Entregados vs No entregados
        const ctxEnt = document.getElementById('chartEntregados');
        if (ctxEnt) {
            new Chart(ctxEnt, {
                type: 'doughnut',
                data: {
                    labels: ['Entregados', 'No entregados'],
                    datasets: [{
                        data: [porcEntregados, porcNoEntregados],
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            position: 'bottom'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    const label = ctx.label || '';
                                    const value = ctx.parsed || 0;
                                    return `${label}: ${value}%`;
                                }
                            }
                        }
                    },
                    layout: {
                        padding: 10
                    }
                }
            });
        }

        // 2) Barras: Repartos por estado
        const ctxEst = document.getElementById('chartEstadosReparto');
        if (ctxEst) {
            new Chart(ctxEst, {
                type: 'bar',
                data: {
                    labels: estadosLabels,
                    datasets: [{
                        data: estadosData
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }

        // 3) Barras: Pedidos entregados por vehículo
        const ctxVeh = document.getElementById('chartVehiculos');
        if (ctxVeh) {
            new Chart(ctxVeh, {
                type: 'bar',
                data: {
                    labels: vehLabels,
                    datasets: [{
                        data: vehData
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            ticks: {
                                autoSkip: true,
                                maxRotation: 45,
                                minRotation: 0
                            }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }

        // 4) Barras: Duración promedio por zona
        const ctxDur = document.getElementById('chartDuracionZona');
        if (ctxDur && duracionLabels.length > 0) {
            new Chart(ctxDur, {
                type: 'bar',
                data: {
                    labels: duracionLabels,
                    datasets: [{
                        data: duracionData
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => ` ${ctx.parsed.y} min`
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            }
                        }
                    }
                }
            });
        }

    });
</script>


<?php
require_once("foot/foot.php");
?>