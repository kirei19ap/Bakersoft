<?php
$currentPage = 'reportespedidos';
include_once("../../includes/head_app.php");
// Si manejás roles en sesión, podrías validar acá:
// if ($_SESSION['rol'] !== 'AdminProduccion') { header("Location: ../../index.php"); exit; }
?>

<div class="titulo-contenido shadow-sm d-flex justify-content-between align-items-center">
  <div>
    <h1 class="display-5 mb-0">Panel de reportes - Producción</h1>
  </div>
</div>

<div class="contenido-principal">
  <div class="contenido mt-3">

    <div class="row g-3">

      <!-- BLOQUE MATERIA PRIMA -->
      <div class="col-md-6">
        <div class="card h-100 shadow-sm">
          <div class="card-header d-flex align-items-center">
            <ion-icon name="cube-outline" style="font-size:1.4rem; margin-right:8px;"></ion-icon>
            <span>Reportes y estadísticas de Materia Prima</span>
          </div>
          <div class="card-body d-flex flex-column justify-content-between">
            <p class="text-muted mb-3">
              Consultá el estado de la materia prima, consumos, stock y reportes asociados
              para la planificación de la producción.
            </p>
            <div class="d-flex flex-wrap gap-2">
              <!-- Ajustá las rutas según tus archivos reales de estadística/reportes MP -->
              <a href="../../reportes/vista/index.php"
                class="btn btn-sm btn-primary">
                <ion-icon name="stats-chart-outline" style="vertical-align:middle;"></ion-icon>
                <span class="ms-1">Estadísticas de materia prima</span>
              </a>

              <!-- <a href="../../reportes/vista/index.php"
                 class="btn btn-sm btn-outline-primary">
                <ion-icon name="document-text-outline" style="vertical-align:middle;"></ion-icon>
                <span class="ms-1">Reportes / PDF</span>
              </a> -->
            </div>
          </div>
        </div>
      </div>

      <!-- BLOQUE PEDIDOS -->
      <div class="col-md-6">
        <div class="card h-100 shadow-sm">
          <div class="card-header d-flex align-items-center">
            <ion-icon name="receipt-outline" style="font-size:1.4rem; margin-right:8px;"></ion-icon>
            <span>Reportes y estadísticas de Pedidos</span>
          </div>
          <div class="card-body d-flex flex-column justify-content-between">
            <p class="text-muted mb-3">
              Visualizá la evolución de pedidos, su estado y facturación,
              y generá reportes detallados en PDF para análisis o gestión.
            </p>
            <div class="d-flex flex-wrap gap-2">
              <!-- Estos sí los tenemos definidos -->
              <a href="../../pedidos/vista/reportes_graficos.php"
                class="btn btn-sm btn-success">
                <ion-icon name="pie-chart-outline" style="vertical-align:middle;"></ion-icon>
                <span class="ms-1">Estadísticas de pedidos</span>
              </a>

              <a href="../../pedidos/vista/reportes_listado.php"
                class="btn btn-sm btn-outline-success">
                <ion-icon name="document-outline" style="vertical-align:middle;"></ion-icon>
                <span class="ms-1">Reportes / PDF</span>
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- BLOQUE TURNOS LABORALES -->
      <div class="col-md-6">
        <div class="card h-100 shadow-sm">
          <div class="card-header d-flex align-items-center">
            <ion-icon name="time-outline" style="font-size:1.4rem; margin-right:8px;"></ion-icon>
            <span>Reportes y estadísticas de Turnos laborales</span>
          </div>
          <div class="card-body d-flex flex-column justify-content-between">
            <p class="text-muted mb-3">
              Analizá la asignación, cumplimiento y reasignación de turnos laborales
              para optimizar la dotación de operarios.
            </p>
            <div class="d-flex flex-wrap gap-2">
              <a href="../../turnos/vista/reportes_turnos_graficos.php"
                class="btn btn-sm btn-info">
                <ion-icon name="stats-chart-outline" style="vertical-align:middle;"></ion-icon>
                <span class="ms-1">Estadísticas de turnos</span>
              </a>

              <a href="../../turnos/vista/reportes_turnos_listado.php"
                class="btn btn-sm btn-outline-info">
                <ion-icon name="document-outline" style="vertical-align:middle;"></ion-icon>
                <span class="ms-1">Reportes / PDF</span>
              </a>
            </div>
          </div>
        </div>
      </div>


    </div>

  </div>
</div>

<?php
require_once("foot/foot.php");
?>