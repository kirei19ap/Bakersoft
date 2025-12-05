<?php
require_once("head/head.php");
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

// Si el estado no es Generado, no permitimos editar
if ((int)$pedido['estado'] !== 70) {
    header("Location: index.php?msg=" . urlencode('Solo se pueden editar pedidos en estado Generado.') . "&tipo=error");
    exit();
}

$productos = $ctrl->obtenerProductosVenta();
?>

<div class="titulo-contenido shadow-sm">
  <h1 class="display-5">Editar Pedido #<?php echo (int)$pedido['idPedidoVenta']; ?></h1>
</div>

<div class="contenido-principal">
  <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
    <p class="mb-0 text-muted">Modificar datos del cliente y el detalle del pedido.</p>
    <a href="index.php" class="btn btn-outline-secondary btn-sm">
      <ion-icon name="arrow-back-outline"></ion-icon>
      Volver al listado
    </a>
  </div>

  <div class="contenido">
    <div class="card mb-3">
      <div class="card-body">

        <form id="formPedido" action="../controlador/controladorPedidos.php" method="POST">
          <input type="hidden" name="accion" value="actualizarPedido">
          <input type="hidden" name="idPedidoVenta" value="<?php echo (int)$pedido['idPedidoVenta']; ?>">
          <input type="hidden" name="idCliente" value="<?php echo (int)$pedido['id_cliente']; ?>">

          <!-- DATOS DEL CLIENTE -->
          <div class="card mb-3">
            <div class="card-header">
              Datos del cliente
            </div>
            <div class="card-body">
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="clienteNombre" class="form-label">Nombre / Razón social *</label>
                  <input type="text" class="form-control" id="clienteNombre" name="clienteNombre"
                         value="<?php echo htmlspecialchars($pedido['nombre']); ?>" required>
                </div>
                <div class="col-md-3">
                  <label for="clienteTelefono" class="form-label">Teléfono</label>
                  <input type="text" class="form-control" id="clienteTelefono" name="clienteTelefono"
                         value="<?php echo htmlspecialchars($pedido['telefono']); ?>">
                </div>
                <div class="col-md-3">
                  <label for="clienteEmail" class="form-label">Email</label>
                  <input type="email" class="form-control" id="clienteEmail" name="clienteEmail"
                         value="<?php echo htmlspecialchars($pedido['email']); ?>">
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="clienteCalle" class="form-label">Calle</label>
                  <input type="text" class="form-control" id="clienteCalle" name="clienteCalle"
                         value="<?php echo htmlspecialchars($pedido['calle']); ?>">
                </div>
                <div class="col-md-2">
                  <label for="clienteAltura" class="form-label">Altura</label>
                  <input type="number" class="form-control" id="clienteAltura" name="clienteAltura" min="0"
                         value="<?php echo (int)$pedido['altura']; ?>">
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
                  <label for="fechaPedido" class="form-label">Fecha del pedido</label>
                  <?php
                    // fechaPedido viene como Y-m-d H:i:s
                    $fechaRaw = $pedido['fechaPedido'];
                    $dt = DateTime::createFromFormat('Y-m-d H:i:s', $fechaRaw);
                    $fechaValue = $dt ? $dt->format('Y-m-d') : date('Y-m-d');
                  ?>
                  <input type="date" class="form-control" id="fechaPedido" name="fechaPedido"
                         value="<?php echo $fechaValue; ?>">
                </div>
                <div class="col-md-9">
                  <label for="observaciones" class="form-label">Observaciones</label>
                  <textarea class="form-control" id="observaciones" name="observaciones" rows="2"><?php
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
                <table class="table table-sm align-middle" id="tablaDetallePedido">
                  <thead>
                    <tr>
                      <th style="width: 40%;">Producto</th>
                      <th style="width: 15%;">Cantidad</th>
                      <th style="width: 20%;">Precio unitario</th>
                      <th style="width: 20%;">Subtotal</th>
                      <th style="width: 5%;"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                      $esPrimera = true;
                      foreach ($detalle as $item):
                    ?>
                      <tr class="fila-detalle">
                        <td>
                          <select name="idProducto[]" class="form-select campo-producto">
                            <option value="">Seleccione un producto...</option>
                            <?php foreach ($productos as $prod): ?>
                              <option value="<?php echo $prod['idProducto']; ?>"
                                      data-precio="<?php echo number_format($prod['precio_venta'], 2, '.', ''); ?>"
                                      <?php echo ($prod['idProducto'] == $item['idProducto']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($prod['nombre']); ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </td>
                        <td>
                          <input type="number" name="cantidad[]" class="form-control campo-cantidad"
                                 min="0.01" step="0.01"
                                 value="<?php echo number_format($item['cantidad'], 2, '.', ''); ?>">
                        </td>
                        <td>
                          <input type="number" name="precioUnitario[]" class="form-control campo-precio"
                                 min="0" step="0.01"
                                 value="<?php echo number_format($item['precioUnitario'], 2, '.', ''); ?>">
                        </td>
                        <td>
                          <input type="number" name="subtotal[]" class="form-control campo-subtotal"
                                 readonly step="0.01"
                                 value="<?php echo number_format($item['subtotal'], 2, '.', ''); ?>">
                        </td>
                        <td class="text-center">
                          <button type="button" class="btn btn-sm btn-outline-danger btnEliminarFila"
                                  title="Eliminar línea">
                            <ion-icon name="trash-outline"></ion-icon>
                          </button>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

              <!-- NUEVA UBICACIÓN: botón Agregar línea debajo de la tabla -->
              <div class="d-flex justify-content-start mt-2 mb-2">
                <button type="button" class="btn btn-sm btn-success" id="btnAgregarLinea">
                  <ion-icon name="add-outline"></ion-icon>
                  Agregar producto
                </button>
              </div>

              <div class="d-flex justify-content-end mt-2">
                <div class="input-group" style="max-width: 250px;">
                  <span class="input-group-text">Total</span>
                  <input type="text" class="form-control text-end" id="totalPedido" name="totalPedido"
                         readonly
                         value="<?php echo number_format($pedido['total'], 2, '.', ''); ?>">
                </div>
              </div>
            </div>
          </div>

          <!-- BOTONES -->
          <div class="d-flex justify-content-end gap-2">
            <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary" id="btnGuardarPedido">
              <ion-icon name="save-outline"></ion-icon>
              Guardar cambios
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>

<?php
require_once("foot/foot.php");
?>
