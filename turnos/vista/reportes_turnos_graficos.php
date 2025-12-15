<?php
// turnos/vista/reportes_turnos_graficos.php

$currentPage = 'reportespedidos';
include_once("../../includes/head_app.php");
require_once("../controlador/controladorTurnos.php");

$ctrlTurnos = new controladorTurnos();

// Filtros de fechas para el dashboard
$fechaDesde = $_GET['desde'] ?? date('Y-m-01');
$fechaHasta = $_GET['hasta'] ?? date('Y-m-t');

$dashboard = $ctrlTurnos->obtenerDashboardTurnos($fechaDesde, $fechaHasta);

$kpis             = $dashboard['kpis']             ?? [];
$totalesPorEstado = $dashboard['totalesPorEstado'] ?? [];
$totalTurnos      = $dashboard['totalTurnos']      ?? 0;
$tendenciaSemanal = $dashboard['tendenciaSemanal'] ?? [];

$porcentajesEstados = [];

foreach ($totalesPorEstado as $estado => $cantidad) {
  $porcentajesEstados[$estado] = ($totalTurnos > 0)
    ? round(($cantidad / $totalTurnos) * 100, 1)
    : 0;
}


function formatearFechaDMY($fechaYmd)
{
  if (!$fechaYmd) return '';
  $partes = explode('-', $fechaYmd);
  if (count($partes) !== 3) return $fechaYmd;
  return $partes[2] . '-' . $partes[1] . '-' . $partes[0];
}
?>
<style>
  .chart-container {
    position: relative;
    width: 100%;
    max-width: 100%;
    height: 260px;
    /* ajustable, pero así quedan prolijos los dos */
  }
</style>

<div class="titulo-contenido shadow-sm d-flex justify-content-between align-items-center">
  <div>
    <h1 class="display-5 mb-0">Estadísticas de turnos laborales</h1>
    <small class="text-muted">
      Panel de métricas y gráficos sobre la asignación y cumplimiento de turnos.
    </small>
  </div>
  <div>
    <a href="../../reportes/vista/reportes_produccion.php"
      class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
      <ion-icon name="arrow-back-outline" style="font-size:18px;"></ion-icon>
      <span>Volver al panel de reportes</span>
    </a>
  </div>
</div>

<div class="contenido-principal">
  <div class="contenido mt-3">

    <!-- FILTROS DE FECHA PARA EL DASHBOARD -->
    <div class="card mb-3 shadow-sm">
      <div class="card-body">
        <form class="row g-3" method="GET">
          <div class="col-md-3">
            <label for="desde" class="form-label">Fecha desde</label>
            <input type="date"
              id="desde"
              name="desde"
              class="form-control"
              value="<?php echo htmlspecialchars($fechaDesde); ?>">
          </div>
          <div class="col-md-3">
            <label for="hasta" class="form-label">Fecha hasta</label>
            <input type="date"
              id="hasta"
              name="hasta"
              class="form-control"
              value="<?php echo htmlspecialchars($fechaHasta); ?>">
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <button type="submit"
              class="btn btn-primary d-flex align-items-center gap-1">
              <ion-icon name="funnel-outline" style="font-size:18px;"></ion-icon>
              <span>Actualizar</span>
            </button>
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <p class="text-muted mb-0">
              Rango actual:<br>
              <strong><?php echo formatearFechaDMY($dashboard['fechas']['desde']); ?></strong>
              al
              <strong><?php echo formatearFechaDMY($dashboard['fechas']['hasta']); ?></strong>
            </p>
          </div>
        </form>
      </div>
    </div>

    <!-- KPIs Turnos -->
    <div class="row g-3 mb-3">

      <!-- Cumplimiento de turnos -->
      <div class="col-md-3">
        <div class="card shadow-sm h-100 kpi-card kpi-green">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <small class="text-muted fw-bold">Cumplimiento de turnos</small>
              <ion-icon name="checkmark-done-circle-outline" class="kpi-card-icon"></ion-icon>
            </div>
            <h3 class="mt-2 mb-0">
              <?php echo ($totalTurnos > 0) ? ($kpis['cumplimiento'] . '%') : '--'; ?>
            </h3>
            <small class="text-muted">Finalizados vs asignados/confirmados</small>
          </div>
        </div>
      </div>

      <!-- Ausentismo -->
      <div class="col-md-3">
        <div class="card shadow-sm h-100 kpi-card kpi-red">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <small class="text-muted fw-bold">Ausentismo</small>
              <ion-icon name="alert-circle-outline" class="kpi-card-icon"></ion-icon>
            </div>
            <h3 class="mt-2 mb-0">
              <?php echo ($totalTurnos > 0) ? ($kpis['ausentismo'] . '%') : '--'; ?>
            </h3>
            <small class="text-muted">Turnos cancelados sobre el total</small>
          </div>
        </div>
      </div>

      <!-- Turnos a reasignar -->
      <div class="col-md-3">
        <div class="card shadow-sm h-100 kpi-card kpi-yellow">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <small class="text-muted fw-bold">Turnos a reasignar</small>
              <ion-icon name="swap-horizontal-outline" class="kpi-card-icon"></ion-icon>
            </div>
            <h3 class="mt-2 mb-0">
              <?php echo (int)($kpis['turnosReasignar'] ?? 0); ?>
            </h3>
            <small class="text-muted">Turnos cancelados en el período</small>
          </div>
        </div>
      </div>

      <!-- Solicitudes pendientes -->
      <div class="col-md-3">
        <div class="card shadow-sm h-100 kpi-card kpi-blue">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <small class="text-muted fw-bold">Solicitudes pendientes</small>
              <ion-icon name="time-outline" class="kpi-card-icon"></ion-icon>
            </div>
            <h3 class="mt-2 mb-0">
              <?php echo (int)($kpis['solicitudesPendientes'] ?? 0); ?>
            </h3>
            <small class="text-muted">Solicitudes de cambio sin gestionar</small>
          </div>
        </div>
      </div>

    </div>


    <!-- GRÁFICOS -->
    <div class="row g-3">
      <div class="col-md-6">
        <div class="card shadow-sm h-100">
          <div class="card-header">
            <span>Distribución de estados de turnos</span>
          </div>
          <div class="card-body">
            <?php if ($totalTurnos === 0): ?>
              <p class="text-muted mb-0">No hay turnos en el rango seleccionado.</p>
            <?php else: ?>
              <div class="chart-container">
                <canvas id="chartEstadosTurnos"></canvas>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card shadow-sm h-100">
          <div class="card-header">
            <span>Tendencia semanal de cumplimiento</span>
          </div>
          <div class="card-body">
            <?php if (empty($tendenciaSemanal['labels'])): ?>
              <p class="text-muted mb-0">No hay datos suficientes para mostrar la tendencia.</p>
            <?php else: ?>
              <div class="chart-container">
                <canvas id="chartTendenciaCumplimiento"></canvas>
              </div>

            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Chart.js desde CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // ====== CHART ESTADOS ======
    <?php if ($totalTurnos > 0): ?>
      const ctxEstados = document.getElementById('chartEstadosTurnos').getContext('2d');

      const dataEstados = {
        labels: ['Asignado', 'Confirmado', 'Finalizado', 'A reasignar', 'Otros'],
        datasets: [{
          data: [
            <?php echo $porcentajesEstados['Asignado']; ?>,
            <?php echo $porcentajesEstados['Confirmado']; ?>,
            <?php echo $porcentajesEstados['Finalizado']; ?>,
            <?php echo $porcentajesEstados['Cancelado']; ?>,
            <?php echo $porcentajesEstados['Otros']; ?>
          ]
        }]
      };


      new Chart(ctxEstados, {
        type: 'doughnut',
        data: dataEstados,
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'bottom'
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  const label = context.label || '';
                  const porcentaje = context.parsed || 0;

                  // Recuperar cantidades reales desde PHP
                  const cantidades = {
                    Asignado: <?php echo (int)$totalesPorEstado['Asignado']; ?>,
                    Confirmado: <?php echo (int)$totalesPorEstado['Confirmado']; ?>,
                    Finalizado: <?php echo (int)$totalesPorEstado['Finalizado']; ?>,
                    'A reasignar': <?php echo (int)$totalesPorEstado['Cancelado']; ?>,
                    Otros: <?php echo (int)$totalesPorEstado['Otros']; ?>
                  };

                  const cantidad = cantidades[label] ?? 0;

                  return `${label}: ${cantidad} (${porcentaje}%)`;
                }
              }
            }

          }
        }
      });
    <?php endif; ?>

    // ====== CHART TENDENCIA CUMPLIMIENTO ======
    <?php if (!empty($tendenciaSemanal['labels'])): ?>
      const ctxTendencia = document.getElementById('chartTendenciaCumplimiento').getContext('2d');

      const dataTendencia = {
        labels: <?php echo json_encode($tendenciaSemanal['labels']); ?>,
        datasets: [{
          label: 'Cumplimiento (%)',
          data: <?php echo json_encode($tendenciaSemanal['datos']); ?>,
          fill: false,
          tension: 0.2
        }]
      };

      new Chart(ctxTendencia, {
        type: 'line',
        data: dataTendencia,
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              max: 100
            }
          }
        }
      });
    <?php endif; ?>
  });
</script>

<?php
require_once("foot/foot.php");
?>