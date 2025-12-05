<?php
include_once("../../includes/head_app.php");
require_once("../controlador/controladorPedidos.php");

$ctrl = new controladorPedidos();

// Fechas y estado desde GET
$hoy    = date('Y-m-d');
$hace30 = date('Y-m-d', strtotime('-30 days'));

$fechaDesde = $_GET['desde'] ?? $hace30;
$fechaHasta = $_GET['hasta'] ?? $hoy;
$estadoSel  = isset($_GET['estado']) && $_GET['estado'] !== '' ? (int)$_GET['estado'] : null;

// Obtener pedidos filtrados
$pedidos = $ctrl->obtenerPedidosFiltrados($fechaDesde, $fechaHasta, $estadoSel);

// Helper para descripciones de estado (por si descEstado viene null)
function obtenerDescEstado(int $estado, ?string $descBD): string {
    if ($descBD) return $descBD;

    switch ($estado) {
        case 70: return 'Generado';
        case 80: return 'Confirmado';
        case 90: return 'Preparado';
        case 100: return 'Entregado';
        case 60: return 'Cancelado';
        default: return 'Desconocido';
    }
}

// Map para select
$estadosOpciones = [
    ''   => 'Todos',
    70   => 'Generado',
    80   => 'Confirmado',
    90   => 'Preparado',
    100  => 'Entregado',
    60   => 'Cancelado',
];

// Totales
$cantidadPedidos = count($pedidos);
$totalFacturacion = array_reduce($pedidos, function($acum, $p) {
    return $acum + (float)$p['total'];
}, 0.0);

?>

<div class="titulo-contenido shadow-sm">
  <h1 class="display-5">Reporte de pedidos</h1>
</div>

<div class="contenido-principal">
  <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
    <p class="mb-0 text-muted">
      Listado de pedidos filtrado por fechas y estado, con opción de exportar a PDF.
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
        <form class="row g-3 align-items-end" method="GET" action="reportes_listado.php">
          <div class="col-md-3">
            <label for="fechaDesde" class="form-label">Desde</label>
            <input type="date" id="fechaDesde" name="desde" class="form-control"
                   value="<?php echo htmlspecialchars($fechaDesde); ?>">
          </div>
          <div class="col-md-3">
            <label for="fechaHasta" class="form-label">Hasta</label>
            <input type="date" id="fechaHasta" name="hasta" class="form-control"
                   value="<?php echo htmlspecialchars($fechaHasta); ?>">
          </div>
          <div class="col-md-3">
            <label for="estado" class="form-label">Estado</label>
            <select id="estado" name="estado" class="form-select">
              <?php foreach ($estadosOpciones as $valor => $texto): ?>
                <option value="<?php echo $valor; ?>"
                  <?php echo ($valor !== '' && $estadoSel === (int)$valor) ? 'selected' : ''; ?>>
                  <?php echo $texto; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-3 d-flex gap-2">
            <!-- Botón FILTRAR (GET a esta misma página) -->
            <button type="submit" class="btn btn-primary mt-4">
              <ion-icon name="filter-outline"></ion-icon>
              Filtrar
            </button>

            <!-- Botón EXPORTAR PDF (POST a reporte_pedidos_pdf.php en nueva pestaña) -->
            <button type="submit"
                    class="btn btn-outline-danger mt-4"
                    formaction="reporte_pedidos_pdf.php"
                    formmethod="post"
                    formtarget="_blank">
              <ion-icon name="document-outline"></ion-icon>
              Exportar PDF
            </button>
          </div>
        </form>
      </div>
    </div>


    <!-- RESUMEN -->
    <div class="row mb-3">
      <div class="col-md-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <span class="text-muted">Pedidos en el período</span>
            <h4 class="mb-0"><?php echo $cantidadPedidos; ?></h4>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card border-0 shadow-sm">
          <div class="card-body">
            <span class="text-muted">Facturación total</span>
            <h4 class="mb-0">$ <?php echo number_format($totalFacturacion, 2, ',', '.'); ?></h4>
          </div>
        </div>
      </div>
    </div>

    <!-- TABLA DE PEDIDOS -->
    <div class="card">
      <div class="card-header">
        Resultados del filtro
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-sm align-middle" id="tablaReportePedidos">
            <thead>
              <tr>
                <th># Pedido</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Estado</th>
                <th class="text-end">Total</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($pedidos)): ?>
                <?php foreach ($pedidos as $p): ?>
                  <?php
                    $fechaRaw = $p['fechaPedido'];
                    $fechaTexto = '';
                    $dt = DateTime::createFromFormat('Y-m-d H:i:s', $fechaRaw);
                    if ($dt) {
                        $fechaTexto = $dt->format('d-m-Y');
                    } else {
                        $fechaTexto = date('d-m-Y', strtotime($fechaRaw));
                    }

                    $descEstado = obtenerDescEstado((int)$p['estado'], $p['descEstado'] ?? null);
                    $badgeClass = 'bg-secondary';
                    switch ((int)$p['estado']) {
                        case 70: $badgeClass = 'bg-warning text-dark'; break;
                        case 80: $badgeClass = 'bg-info text-dark'; break;
                        case 90: $badgeClass = 'bg-primary'; break;
                        case 100:$badgeClass = 'bg-success'; break;
                        case 60: $badgeClass = 'bg-danger'; break;
                    }
                  ?>
                  <tr>
                    <td><?php echo (int)$p['idPedidoVenta']; ?></td>
                    <td><?php echo $fechaTexto; ?></td>
                    <td><?php echo htmlspecialchars($p['cliente']); ?></td>
                    <td>
                      <span class="badge <?php echo $badgeClass; ?>">
                        <?php echo htmlspecialchars($descEstado); ?>
                      </span>
                    </td>
                    <td class="text-end">
                      $ <?php echo number_format($p['total'], 2, ',', '.'); ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5" class="text-center text-muted">
                    No se encontraron pedidos para el filtro seleccionado.
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>
</div>

<?php
require_once("foot/foot.php");
?>
