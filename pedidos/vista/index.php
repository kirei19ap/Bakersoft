<?php
$currentPage = 'pedidos';
include_once("../../includes/head_app.php");
require_once("../controlador/controladorPedidos.php");
require_once("../../reparto/controlador/controladorReparto.php");

$ctrl = new controladorPedidos();
$ctrlReparto = new ControladorReparto();
$pedidos = $ctrl->listarPedidos(); // NUEVO

$mensaje = $_GET['msg'] ?? '';
$tipoAlerta = $_GET['tipo'] ?? '';
?>
<style>
  table.dataTable td.dt-empty {
    text-align: center !important;
    color: #6c757d;
    /* gris bonito */
    font-style: italic;
    /* opcional */
  }
</style>
<div class="titulo-contenido shadow-sm">
  <h1 class="display-5">Pedidos</h1>
</div>

<div class="contenido-principal">

  <?php if ($mensaje): ?>
    <div class="alert alert-<?php echo $tipoAlerta === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show mt-2" role="alert">
      <?php echo htmlspecialchars($mensaje); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
  <?php endif; ?>

  <div class="encabezado-tabla d-flex justify-content-between align-items-center mb-3 mt-3">
    <div>
      <h3 class="mb-0 text-muted">Listado de pedidos de clientes.</h3>
    </div>
    <div>
      <a href="crear.php" class="btn btn-success">
        <ion-icon name="add-outline"></ion-icon>
        <span>Nuevo pedido</span>
      </a>
    </div>
  </div>

  <div class="contenido">
    <div class="tabla-empleados card">
      <div class="card-body">
        <div class="">
          <table id="tablaPedidos" class="table table-striped table-bordered align-middle">
            <thead class="table-light">
              <tr>
                <th style="width: 9%;" class="text-center">N° Pedido</th>
                <th style="width: 25%;">Cliente</th>
                <th style="width: 13%;">Fecha</th>
                <th style="width: 10%" class="text-center">Stock MP</th>
                <th style="width: 11%;">Estado Pedido</th>
                <th style="width: 12%;" class="text-center">Total</th>
                <th style="width: 20%;" class="text-center">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($pedidos)): ?>
                <?php foreach ($pedidos as $p): ?>
                  <?php
                  $estado = (int)$p['estado'];
                  $descEstado = $p['descEstado'] ?? 'N/D';
                  $idPedido = (int)$p['idPedidoVenta'];
                  $enRepartoActivo = $ctrlReparto->pedidoEnRepartoActivo($idPedido);

                  // Resumen de stock para este pedido
                  $resumenStock   = $ctrl->obtenerResumenStockPedido($idPedido);
                  $stockSuficiente = $resumenStock['suficiente'] ?? false;
                  // Construir texto del tooltip
                  $tooltipMP = '';
                  if (!$stockSuficiente && !empty($resumenStock['faltantes'])) {
                    $lineas = [];
                    foreach ($resumenStock['faltantes'] as $f) {
                      $nombre = $f['nombreMP'];
                      $faltante = (float)$f['faltante'];
                      $unidad = $f['unidad'] ?? '';

                      // Formateo lindo: 2 o 3 decimales, sin ceros de más
                      $faltanteFmt = rtrim(rtrim(number_format($faltante, 3, ',', '.'), '0'), ',');

                      $lineas[] = $nombre . ": falta " . $faltanteFmt . " " . $unidad;
                    }
                    $tooltipMP = implode("\n", $lineas);
                  } else {
                    $tooltipMP = "Stock suficiente de todas las materias primas.";
                  }



                  // Badge según estado
                  $badgeClass = 'bg-secondary';
                  switch ($estado) {
                    case 70:
                      $badgeClass = 'bg-warning text-dark';
                      break;    // Generado
                    case 80:
                      $badgeClass = 'bg-info text-dark';
                      break;       // Confirmado
                    case 90:
                      $badgeClass = 'bg-primary';
                      break;              // Preparado
                    case 100:
                      $badgeClass = 'bg-success';
                      break;             // Entregado
                    case 60:
                      $badgeClass = 'bg-danger';
                      break;               // Cancelado
                  }

                  // Definición de acciones según estado
                  $acciones = [];
                  $puedePreparar = true;

                  if ($estado === 70) { // Generado
                    $puedePreparar = $stockSuficiente ? true : false;
                    $acciones[] = ['dataAction' => 'Confirmar', 'nuevoEstado' => 80, 'class' => 'btn-info', 'label' => 'Confirmar'];
                    $acciones[] = ['dataAction' => 'Cancelar',  'nuevoEstado' => 60, 'class' => 'btn-danger',  'label' => 'Cancelar'];
                  } elseif ($estado === 80) { // Confirmado
                    $acciones[] = ['dataAction' => 'Preparar', 'nuevoEstado' => 90, 'class' => 'btn-info', 'label' => 'Preparado'];
                    $acciones[] = ['dataAction' => 'Cancelar',  'nuevoEstado' => 60, 'class' => 'btn-danger',  'label' => 'Cancelar'];
                  } elseif ($estado === 90) { // Preparado
                    if ($enRepartoActivo) {
                      // El pedido está siendo gestionado por un reparto activo:
                      // - NO permitir Entregar desde pedidos
                      // - NO permitir Cancelar desde pedidos
                      $acciones = []; // que la gestión venga 100% desde el módulo de repartos
                    } else {
                      // Pedido preparado que NO está en reparto:
                      // se puede entregar directo o cancelar.
                      $acciones[] = [
                        'dataAction'  => 'Entregar',
                        'nuevoEstado' => 100,
                        'class'       => 'btn-light',
                        'label'       => 'Entregar'
                      ];
                      $acciones[] = [
                        'dataAction'  => 'Cancelar',
                        'nuevoEstado' => 60,
                        'class'       => 'btn-danger',
                        'label'       => 'Cancelar'
                      ];
                    }
                  } else {
                    $acciones = [];
                  }


                  ?>
                  <tr>
                    <td class="text-center"><?php echo (int)$p['idPedidoVenta']; ?></td>
                    <td><?php echo htmlspecialchars($p['cliente']); ?></td>
                    <td><?php $fechaRaw = $p['fechaPedido'] ?? null;
                        $fechaFormateada = '';

                        if ($fechaRaw) {
                          $dt = DateTime::createFromFormat('Y-m-d H:i:s', $fechaRaw);
                          if ($dt) {
                            $fechaFormateada = $dt->format('d-m-Y');
                          } else {
                            // fallback por si viene en otro formato
                            $ts = strtotime($fechaRaw);
                            $fechaFormateada = $ts ? date('d-m-Y', $ts) : $fechaRaw;
                          }
                        }

                        echo htmlspecialchars($fechaFormateada);
                        ?>
                    </td>
                    <td class="text-center">
                      <?php if ($stockSuficiente): ?>
                        <ion-icon name="ellipse" class="text-success"
                          style="font-size:1.1rem;"
                          title="<?php echo htmlspecialchars($tooltipMP); ?>"></ion-icon>
                      <?php else: ?>
                        <ion-icon name="ellipse" class="text-danger"
                          style="font-size:1.1rem;"
                          title="<?php echo htmlspecialchars($tooltipMP); ?>"></ion-icon>
                      <?php endif; ?>

                    </td>

                    <td>
                      <span class="badge <?php echo $badgeClass; ?>">
                        <?php echo htmlspecialchars($descEstado); ?>
                      </span>
                    </td>
                    <td class="text-end">
                      $ <?php echo number_format($p['total'], 2, ',', '.'); ?>
                    </td>
                    <td class="text-center">
                      <div class="d-flex flex-wrap gap-1">

                        <!-- BOTÓN VER (solo lectura) -->
                        <a href="ver.php?id=<?php echo (int)$p['idPedidoVenta']; ?>"
                          class="btn btn-sm btn-success linkcomoBoton"
                          title="Ver pedido">
                          <ion-icon name="eye-outline" style="font-size:1.2rem; vertical-align:middle;"></ion-icon>
                        </a>

                        <!-- BOTÓN EDITAR: solo estado Generado (70) -->
                        <?php $puedeEditar = ($estado === 70); ?>
                        <?php if ($puedeEditar): ?>
                          <a href="editar.php?id=<?php echo (int)$p['idPedidoVenta']; ?>"
                            class="btn btn-sm btn-primary"
                            title="Editar pedido">
                            <ion-icon name="create-outline" style="font-size:1.2rem; vertical-align:middle;"></ion-icon>
                          </a>
                        <?php else: ?>
                          <button type="button"
                            class="btn btn-sm btn-secondary"
                            disabled
                            title="Solo se pueden editar pedidos en estado Generado">
                            <ion-icon name="create-outline"
                              style="font-size:1.2rem; vertical-align:middle; opacity:0.5;"></ion-icon>
                          </button>
                        <?php endif; ?>

                        <!-- ACCIONES DE ESTADO (Confirmar / Preparar / Entregar / Cancelar) -->
                        <?php foreach ($acciones as $acc): ?>

                          <?php
                          // Icono por etiqueta
                          $icono = '';
                          switch ($acc['label']) {
                            case 'Confirmar':
                              $icono = 'checkmark-outline';
                              break;
                            case 'Preparado':
                              $icono = 'hammer-outline';
                              break;
                            case 'Entregar':
                              $icono = 'car-outline';
                              break;
                            case 'Cancelar':
                              $icono = 'trash-outline';
                              break;
                          }
                          ?>

                          <?php if ($acc['label'] === 'Confirmar' && !$puedePreparar): ?>
                            <!-- CONFIRMAR DESHABILITADO POR FALTA DE STOCK -->
                            <button type="button"
                              class="btn btn-sm btn-info"
                              disabled
                              title="No hay materias primas suficientes para preparar este pedido">
                              <ion-icon name="<?php echo $icono; ?>"
                                style="font-size:1.2rem; vertical-align:middle; opacity:0.6;"></ion-icon>
                            </button>
                          <?php else: ?>
                            <!-- RESTO DE ACCIONES (y Preparar cuando SÍ hay stock) -->
                            <form action="../controlador/controladorPedidos.php"
                              method="POST"
                              class="d-inline form-accion-estado">

                              <input type="hidden" name="accion" value="cambiarEstado">
                              <input type="hidden" name="idPedidoVenta" value="<?php echo (int)$p['idPedidoVenta']; ?>">
                              <input type="hidden" name="estadoActual" value="<?php echo (int)$estado; ?>">
                              <input type="hidden" name="nuevoEstado" value="<?php echo (int)$acc['nuevoEstado']; ?>">

                              <button type="submit"
                                class="btn btn-sm <?php echo $acc['class']; ?>"
                                data-accion="<?php echo $acc['label']; ?>"
                                title="<?php echo $acc['label']; ?>">
                                <ion-icon name="<?php echo $icono; ?>"
                                  style="font-size:1.2rem; vertical-align:middle;"></ion-icon>
                              </button>
                            </form>
                          <?php endif; ?>


                        <?php endforeach; ?>

                      </div>
                    </td>


                  </tr>
                <?php endforeach; ?>
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