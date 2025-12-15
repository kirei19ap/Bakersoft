<?php
// reparto/vista/verReparto.php

include_once("../../includes/head_app.php");
require_once("../controlador/controladorReparto.php");

$ctrlReparto = new ControladorReparto();

$mensaje = "";
$tipoMensaje = "";

// Validar ID de reparto
if (!isset($_GET['idReparto']) || !is_numeric($_GET['idReparto'])) {
    echo "<div class='alert alert-danger m-3'>ID de reparto no válido.</div>";
    exit;
}
$idReparto = (int)$_GET['idReparto'];

// Manejo de acciones POST (cambiar estado de reparto / actualizar entrega)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    try {
        if ($accion === 'cambiarEstadoReparto') {
            $nuevoEstado = $_POST['nuevoEstado'] ?? '';
            $ok = $ctrlReparto->cambiarEstadoReparto($idReparto, $nuevoEstado);
            if ($ok) {
                $mensaje = "Estado del reparto actualizado correctamente.";
                $tipoMensaje = "success";
            } else {
                throw new Exception("No se pudo actualizar el estado del reparto.");
            }
        } elseif ($accion === 'actualizarEntrega') {
            $idDetalleReparto = (int)($_POST['idDetalleReparto'] ?? 0);
            $nuevoEstadoEntrega = $_POST['nuevoEstadoEntrega'] ?? '';
            if ($idDetalleReparto <= 0) {
                throw new Exception("ID de detalle de reparto inválido.");
            }
            $ok = $ctrlReparto->actualizarEstadoEntrega($idDetalleReparto, $nuevoEstadoEntrega);
            if ($ok) {
                $mensaje = "Estado de entrega del pedido actualizado correctamente.";
                $tipoMensaje = "success";
            } else {
                throw new Exception("No se pudo actualizar el estado de entrega del pedido.");
            }
        }
    } catch (Exception $e) {
        $mensaje = $e->getMessage();
        $tipoMensaje = "danger";
    }
}

// Volver a cargar cabecera y detalle luego de cualquier acción
$reparto  = $ctrlReparto->obtenerRepartoPorId($idReparto);
$detalle  = $ctrlReparto->obtenerDetalleReparto($idReparto);

if (!$reparto) {
    echo "<div class='alert alert-danger m-3'>No se encontró el reparto indicado.</div>";
    exit;
}

// Helpers
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
    return "<span class='badge {$class}'>{$estado}</span>";
}

function badgeEstadoEntrega($estadoEntrega)
{
    switch ($estadoEntrega) {
        case 'Pendiente':
            $class = 'bg-secondary';
            break;
        case 'Entregado':
            $class = 'bg-success';
            break;
        case 'No Entregado':
            $class = 'bg-danger';
            break;
        case 'Reprogramado':
            $class = 'bg-warning text-dark';
            break;
        default:
            $class = 'bg-light text-dark';
            break;
    }
    return "<span class='badge {$class}'>{$estadoEntrega}</span>";
}

?>
<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Detalle de Reparto</h1>
</div>

<div class="contenido-principal">

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($mensaje); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <!-- Cabecera del reparto -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h5 class="card-title mb-1">
                        Reparto #<?php echo $reparto['idReparto']; ?>
                    </h5>
                    <p class="mb-1">
                        <strong>Fecha:</strong> <?php echo htmlspecialchars($reparto['fechaReparto']); ?><br>
                        <?php if (!empty($reparto['horaSalida'])): ?>
                            <strong>Hora salida:</strong> <?php echo htmlspecialchars(substr($reparto['horaSalida'], 0, 5)); ?><br>
                        <?php endif; ?>
                        <strong>Zona:</strong> <?php echo htmlspecialchars($reparto['zona']); ?><br>
                        <strong>Vehículo:</strong>
                        <?php
                        $veh = $reparto['patente'] . ' - ' . ($reparto['vehiculoDescripcion'] ?? '');
                        echo htmlspecialchars(trim($veh));
                        ?><br>
                        <strong>Chofer:</strong>
                        <?php echo htmlspecialchars($reparto['apellido'] . ', ' . $reparto['nombre']); ?>
                    </p>
                    <?php if (!empty($reparto['observaciones'])): ?>
                        <p class="mb-0">
                            <strong>Observaciones:</strong>
                            <?php echo htmlspecialchars($reparto['observaciones']); ?>
                        </p>
                    <?php endif; ?>
                </div>
                <div class="text-end">
                    <p class="mb-2">
                        <strong>Estado del reparto:</strong><br>
                        <?php echo badgeEstadoReparto($reparto['estado']); ?>
                    </p>

                    <a href="repartos.php" class="btn btn-link btn-sm mt-2">
                        <ion-icon name="arrow-back-outline"></ion-icon> Volver a repartos
                    </a>
                </div>

            </div>
        </div>
    </div>

    <!-- Detalle de pedidos del reparto -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Pedidos incluidos en el reparto</h6>
            <small class="text-muted">
                Al marcar un pedido como <strong>Entregado</strong>, se actualiza también el estado del pedido de venta a <strong>Entregado (100)</strong>.
            </small>
        </div>
        <div class="card-body">
            <?php if (empty($detalle)): ?>
                <div class="alert alert-info mb-0">
                    No hay pedidos asociados a este reparto.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th hidden>#</th>
                                <th class = "text-center">ID Pedido</th>
                                <th>Fecha Pedido</th>
                                <th>Total</th>
                                <th>Estado Pedido</th>
                                <th>Orden entrega</th>
                                <th>Estado entrega</th>
                                <!-- <th class="text-center">Actualizar entrega</th> -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            foreach ($detalle as $d):
                            ?>
                                <tr>
                                    <td hidden><?php echo $i++; ?></td>
                                    <td class = "text-center"><?php echo (int)$d['idPedidoVenta']; ?></td>
                                    <td><?php echo htmlspecialchars($d['fechaPedido']); ?></td>
                                    <td><?php echo number_format((float)$d['total'], 2, ',', '.'); ?></td>
                                    <td>
                                        <?php
                                        $txtEstadoPed = $d['descEstado'] ?? ('Cod: ' . $d['codEstadoPedido']);
                                        echo htmlspecialchars($txtEstadoPed);
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($d['ordenEntrega']); ?></td>
                                    <td><?php echo badgeEstadoEntrega($d['estadoEntrega']); ?></td>
                                    <!-- <td class="text-center">
                                        <?php #if (!in_array($reparto['estado'], ['Finalizado', 'Cancelado'])): ?>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="accion" value="actualizarEntrega">
                                                <input type="hidden" name="idDetalleReparto" value="<?php #echo $d['idDetalleReparto']; ?>">
                                                <div class="input-group input-group-sm">
                                                    <select name="nuevoEstadoEntrega" class="form-select form-select-sm">
                                                        <?php 
                                                        #$estadosEnt = ['Pendiente', 'Entregado', 'No Entregado', 'Reprogramado'];
                                                        #foreach ($estadosEnt as $estE) {
                                                            #$sel = ($d['estadoEntrega'] === $estE) ? 'selected' : '';
                                                            #echo "<option value=\"{$estE}\" {$sel}>{$estE}</option>";
                                                        #}
                                                        ?>
                                                    </select>
                                                    <button type="submit" class="btn btn-outline-primary btn-sm">
                                                        <ion-icon name="checkmark-outline"></ion-icon>
                                                    </button>
                                                </div>
                                            </form>
                                        <?php #else: ?>
                                            <small class="text-muted">Reparto <?php #echo htmlspecialchars($reparto['estado']); ?></small>
                                        <?php #endif; ?>
                                    </td> -->
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php
require_once("foot/foot.php")
?>