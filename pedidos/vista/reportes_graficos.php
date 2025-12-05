<?php
include_once("../../includes/head_app.php");
require_once("../controlador/controladorPedidos.php");

// Fechas por defecto: últimos 30 días
$hoy   = date('Y-m-d');
$hace30 = date('Y-m-d', strtotime('-30 days'));
$fechaDesde = $_GET['desde'] ?? $hace30;
$fechaHasta = $_GET['hasta'] ?? $hoy;
?>

<div class="titulo-contenido shadow-sm">
  <h1 class="display-5">Reportes gráficos de pedidos</h1>
</div>

<div class="contenido-principal">
  <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
    <p class="mb-0 text-muted">
      Visualización de pedidos por estado, por día y facturación en un rango de fechas.
    </p>
    <a href="index.php" class="btn btn-outline-secondary btn-sm">
      <ion-icon name="arrow-back-outline"></ion-icon>
      Volver a pedidos
    </a>
  </div>

    <div class="contenido">

    <!-- FILTROS -->
    <div class="card mb-3">
      <div class="card-body">
        <div class="row g-3 align-items-end">
          <div class="col-md-3">
            <label for="fechaDesde" class="form-label">Desde</label>
            <input type="date" id="fechaDesde" class="form-control"
                   value="<?php echo htmlspecialchars($fechaDesde); ?>">
          </div>
          <div class="col-md-3">
            <label for="fechaHasta" class="form-label">Hasta</label>
            <input type="date" id="fechaHasta" class="form-control"
                   value="<?php echo htmlspecialchars($fechaHasta); ?>">
          </div>
          <div class="col-md-3">
            <button type="button" class="btn btn-primary mt-4" id="btnAplicarFiltrosPedidos">
              <ion-icon name="refresh-outline"></ion-icon>
              Aplicar filtros
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- FILA 1: ESTADOS + PEDIDOS POR DÍA -->
    <div class="row g-3">
      <div class="col-lg-6">
        <div class="card h-100">
          <div class="card-header">
            Pedidos por estado
          </div>
          <div class="card-body" style="height: 280px;">
            <canvas id="chartEstadosPedidos"></canvas>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card h-100">
          <div class="card-header">
            Cantidad de pedidos por día
          </div>
          <div class="card-body" style="height: 280px;">
            <canvas id="chartPedidosPorDia"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- FILA 2: FACTURACIÓN -->
    <div class="row g-3 mt-1">
      <div class="col-12">
        <div class="card">
          <div class="card-header">
            Facturación por día
          </div>
          <div class="card-body" style="height: 280px;">
            <canvas id="chartFacturacionPorDia"></canvas>
          </div>
        </div>
      </div>
    </div>

  </div>

</div>

<?php
require_once("foot/foot.php");
?>
