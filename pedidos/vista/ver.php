<?php
include_once("../../includes/head_app.php");
require_once("../controlador/controladorPedidos.php");

$ctrl = new controladorPedidos();
$idPedido = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$datos = $ctrl->obtenerPedidoCompleto($idPedido);

if (!$datos) {
    header("Location: index.php?msg=" . urlencode('Pedido no encontrado.') . "&tipo=error");
    exit();
}

$pedido  = $datos['pedido'];
$detalle = $datos['detalle'];

// Estado y badge
$estado = (int)$pedido['estado'];
$descEstado = '';
$badgeClass = 'bg-secondary';

switch ($estado) {
    case 70:
        $descEstado = 'Generado';
        $badgeClass = 'bg-warning text-dark';
        break;
    case 80:
        $descEstado = 'Confirmado';
        $badgeClass = 'bg-info text-dark';
        break;
    case 90:
        $descEstado = 'Preparado';
        $badgeClass = 'bg-primary';
        break;
    case 100:
        $descEstado = 'Entregado';
        $badgeClass = 'bg-success';
        break;
    case 60:
        $descEstado = 'Cancelado';
        $badgeClass = 'bg-danger';
        break;
    default:
        $descEstado = 'Desconocido';
        break;
}

// Fecha pedido en formato Y-m-d para input (solo lectura) y dd-mm-YYYY si lo querés en algún texto
$fechaRaw = $pedido['fechaPedido'] ?? null;
$fechaInput = date('Y-m-d');
$fechaTexto = '';

if ($fechaRaw) {
    $dt = DateTime::createFromFormat('Y-m-d H:i:s', $fechaRaw);
    if ($dt) {
        $fechaInput = $dt->format('Y-m-d');
        $fechaTexto = $dt->format('d-m-Y');
    } else {
        $fechaInput = date('Y-m-d', strtotime($fechaRaw));
        $fechaTexto = date('d-m-Y', strtotime($fechaRaw));
    }
}
?>

<div class="titulo-contenido shadow-sm d-flex justify-content-between align-items-center">
  <div>
    <h1 class="display-5 mb-0">Ver Pedido #<?php echo (int)$pedido['idPedidoVenta']; ?></h1>
    <small class="text-muted">Consulta en modo lectura</small>
  </div>
  <div>
    <span class="badge <?php echo $badgeClass; ?>" style="font-size:0.9rem;">
      Estado: <?php echo htmlspecialchars($descEstado); ?>
    </span>
  </div>
</div>

<div class="contenido-principal">
  <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
    <p class="mb-0 text-muted">
      Detalle del pedido, datos del cliente y productos asociados. No es posible modificar desde esta vista.
    </p>
    <a href="index.php" class="btn btn-outline-secondary btn-sm">
      <ion-icon name="arrow-back-outline"></ion-icon>
      Volver al listado
    </a>
  </div>

  <div class="contenido">
    <div class="card mb-3">
      <div class="card-body">

        <!-- No hay form, solo lectura -->

        <!-- DATOS DEL CLIENTE -->
        <div class="card mb-3">
          <div class="card-header d-flex justify-content-between align-items-center">
            <span>Datos del cliente</span>
            <span class="badge bg-light text-muted">Sólo lectura</span>
          </div>
          <div class="card-body">
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Nombre / Razón social</label>
                <input type="text" class="form-control" 
                       value="<?php echo htmlspecialchars($pedido['nombre']); ?>" readonly>
              </div>
              <div class="col-md-3">
                <label class="form-label">Teléfono</label>
                <input type="text" class="form-control"
                       value="<?php echo htmlspecialchars($pedido['telefono']); ?>" readonly>
              </div>
              <div class="col-md-3">
                <label class="form-label">Email</label>
                <input type="text" class="form-control"
                       value="<?php echo htmlspecialchars($pedido['email']); ?>" readonly>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Calle</label>
                <input type="text" class="form-control"
                       value="<?php echo htmlspecialchars($pedido['calle']); ?>" readonly>
              </div>
              <div class="col-md-2">
                <label class="form-label">Altura</label>
                <input type="text" class="form-control"
                       value="<?php echo (int)$pedido['altura']; ?>" readonly>
              </div>
            </div>
          </div>
        </div>

        <!-- DATOS DEL PEDIDO -->
        <div class="card mb-3">
          <div class="card-header">
            Datos del pedido
          </div>
          <div class="card-body">
            <div class="row mb-3">
              <div class="col-md-3">
                <label class="form-label">Fecha del pedido</label>
                <input type="date" class="form-control"
                       value="<?php echo $fechaInput; ?>" readonly>
              </div>
              <div class="col-md-9">
                <label class="form-label">Observaciones</label>
                <textarea class="form-control" rows="2" readonly><?php
                  echo htmlspecialchars($pedido['observaciones'] ?? '');
                ?></textarea>
              </div>
            </div>
          </div>
        </div>

        <!-- DETALLE DEL PEDIDO -->
        <div class="card mb-3">
          <div class="card-header">
            Detalle del pedido
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-sm align-middle">
                <thead>
                  <tr>
                    <th style="width: 40%;">Producto</th>
                    <th style="width: 15%;">Cantidad</th>
                    <th style="width: 20%;">Precio unitario</th>
                    <th style="width: 20%;">Subtotal</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($detalle)): ?>
                    <?php foreach ($detalle as $item): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($item['nombreProducto']); ?></td>
                        <td><?php echo number_format($item['cantidad'], 2, ',', '.'); ?></td>
                        <td>$ <?php echo number_format($item['precioUnitario'], 2, ',', '.'); ?></td>
                        <td>$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="4" class="text-center text-muted">
                        No se encontraron ítems en el detalle del pedido.
                      </td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>

            <div class="d-flex justify-content-end mt-2">
              <div class="input-group" style="max-width: 250px;">
                <span class="input-group-text">Total</span>
                <input type="text" class="form-control text-end" 
                       value="<?php echo '$ ' . number_format($pedido['total'], 2, ',', '.'); ?>" readonly>
              </div>
            </div>
          </div>
        </div>

        <!-- SIN BOTONES DE GUARDADO / ACCIONES -->
        <div class="d-flex justify-content-end mt-3">
          <a href="index.php" class="btn btn-outline-secondary">
            <ion-icon name="arrow-back-outline"></ion-icon>
            Volver al listado
          </a>
        </div>

      </div>
    </div>
  </div>
</div>

<?php
require_once("foot/foot.php");
?>
