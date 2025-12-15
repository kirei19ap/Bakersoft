<?php
// reparto/vista/editarReparto.php

include_once("../../includes/head_app.php");
require_once("../controlador/controladorReparto.php");
require_once("../controlador/controladorVehiculo.php");

$ctrlReparto  = new ControladorReparto();
$ctrlVehiculo = new ControladorVehiculo();

$mensaje = "";
$tipoMensaje = "";

// Validar ID de reparto
if (!isset($_GET['idReparto']) || !is_numeric($_GET['idReparto'])) {
    echo "<div class='alert alert-danger m-3'>ID de reparto no válido.</div>";
    exit;
}
$idReparto = (int)$_GET['idReparto'];

// Cargar cabecera antes de procesar POST para saber estado
$reparto = $ctrlReparto->obtenerRepartoPorId($idReparto);
if (!$reparto) {
    echo "<div class='alert alert-danger m-3'>No se encontró el reparto indicado.</div>";
    exit;
}
$editable = ($reparto['estado'] === 'Planificado');

// Manejo de acción editar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'editarReparto') {
    try {
        if (!$editable) {
            throw new Exception("Sólo se pueden editar repartos en estado Planificado.");
        }

        $fechaReparto = $_POST['fechaReparto'] ?? '';
        $horaSalida   = $_POST['horaSalida'] ?? null;
        $zona         = trim($_POST['zona'] ?? '');
        $idVehiculo   = (int)($_POST['idVehiculo'] ?? 0);
        $observaciones = trim($_POST['observaciones'] ?? '');
        $pedidosSel   = $_POST['pedidos'] ?? [];

        if (empty($fechaReparto) || $idVehiculo <= 0) {
            throw new Exception("La fecha de reparto y el vehículo son obligatorios.");
        }

        if (empty($pedidosSel) || !is_array($pedidosSel)) {
            throw new Exception("Debe seleccionar al menos un pedido para el reparto.");
        }

        $cabecera = [
            'fechaReparto'  => $fechaReparto,
            'horaSalida'    => $horaSalida ?: null,
            'zona'          => $zona,
            'idVehiculo'    => $idVehiculo,
            'observaciones' => $observaciones ?: null
        ];

        $detallePedidos = [];
        $orden = 1;
        foreach ($pedidosSel as $idPed) {
            $idPed = (int)$idPed;
            if ($idPed > 0) {
                $detallePedidos[] = [
                    'idPedidoVenta' => $idPed,
                    'ordenEntrega'  => $orden++
                ];
            }
        }

        if (empty($detallePedidos)) {
            throw new Exception("No se detectaron pedidos válidos para el reparto.");
        }

        $ok = $ctrlReparto->actualizarReparto($idReparto, $cabecera, $detallePedidos);
        if ($ok) {
            $mensaje = "Reparto actualizado correctamente.";
            $tipoMensaje = "success";
            // Refrescar cabecera y editable
            $reparto = $ctrlReparto->obtenerRepartoPorId($idReparto);
            $editable = ($reparto['estado'] === 'Planificado');
        } else {
            throw new Exception("Ocurrió un error al actualizar el reparto.");
        }
    } catch (Exception $e) {
        $mensaje = $e->getMessage();
        $tipoMensaje = "danger";
    }
}

// Cargar datos para el formulario
$vehiculosActivos   = $ctrlVehiculo->listarVehiculos(true);
$detalleActual      = $ctrlReparto->obtenerDetalleReparto($idReparto);
$pedidosParaEdicion = $ctrlReparto->listarPedidosPreparadosParaEdicion($idReparto);

// Armar un set de IDs de pedidos que ya están en el reparto para marcar check
$idsPedidosActuales = [];
foreach ($detalleActual as $d) {
    $idsPedidosActuales[(int)$d['idPedidoVenta']] = true;
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

?>
<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Editar Reparto</h1>
</div>

<div class="contenido-principal">

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($mensaje); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <!-- Info del reparto -->
    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-start">
            <div>
                <h5 class="card-title mb-1">
                    Reparto #<?php echo $reparto['idReparto']; ?>
                </h5>
                <p class="mb-0">
                    <strong>Estado:</strong> <?php echo badgeEstadoReparto($reparto['estado']); ?>
                </p>
                <?php if (!$editable): ?>
                    <p class="text-danger mb-0">
                        Este reparto ya no se puede editar porque no está en estado Planificado.
                    </p>
                <?php endif; ?>
            </div>
            <div class="text-end">
                <a href="repartos.php" class="btn btn-link btn-sm">
                    <ion-icon name="arrow-back-outline"></ion-icon> Volver a repartos
                </a>
                <a href="verReparto.php?idReparto=<?php echo $reparto['idReparto']; ?>"
                    class="btn btn-link btn-sm">
                    <ion-icon name="eye-outline"></ion-icon> Ver detalle
                </a>
            </div>
        </div>
    </div>

    <!-- Formulario de edición -->
    <div class="card">
        <div class="card-body">
            <form method="post" id="formEditarReparto">
                <input type="hidden" name="accion" value="editarReparto">

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="fechaReparto" class="form-label">Fecha reparto *</label>
                        <input type="date"
                            id="fechaReparto"
                            name="fechaReparto"
                            class="form-control"
                            value="<?php echo htmlspecialchars($reparto['fechaReparto']); ?>"
                            <?php echo !$editable ? 'disabled' : ''; ?>>
                    </div>
                    <div class="col-md-3">
                        <label for="horaSalida" class="form-label">Hora salida</label>
                        <input type="time"
                            id="horaSalida"
                            name="horaSalida"
                            class="form-control"
                            value="<?php echo htmlspecialchars(substr($reparto['horaSalida'] ?? '', 0, 5)); ?>"
                            <?php echo !$editable ? 'disabled' : ''; ?>>
                    </div>
                    <div class="col-md-3">
                        <label for="idVehiculo" class="form-label">Vehículo *</label>
                        <select id="idVehiculo"
                            name="idVehiculo"
                            class="form-select"
                            <?php echo !$editable ? 'disabled' : ''; ?>>
                            <option value="">-- Seleccione --</option>
                            <?php foreach ($vehiculosActivos as $v): ?>
                                <?php
                                $texto = $v['patente'] . ' - ' . ($v['descripcion'] ?? '');
                                $sel = ($reparto['idVehiculo'] == $v['idVehiculo']) ? 'selected' : '';
                                ?>
                                <option value="<?php echo $v['idVehiculo']; ?>" <?php echo $sel; ?>>
                                    <?php echo htmlspecialchars(trim($texto)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="zona" class="form-label">Zona</label>
                        <select id="zona"
                            name="zona"
                            class="form-select"
                            <?php echo !$editable ? 'disabled' : ''; ?>>
                            <option value="">-- Seleccionar --</option>

                            <option value="NORTE"
                                <?php echo ($reparto['zona'] === 'NORTE') ? 'selected' : ''; ?>>
                                NORTE
                            </option>

                            <option value="SUR"
                                <?php echo ($reparto['zona'] === 'SUR') ? 'selected' : ''; ?>>
                                SUR
                            </option>

                            <option value="ESTE"
                                <?php echo ($reparto['zona'] === 'ESTE') ? 'selected' : ''; ?>>
                                ESTE
                            </option>

                            <option value="OESTE"
                                <?php echo ($reparto['zona'] === 'OESTE') ? 'selected' : ''; ?>>
                                OESTE
                            </option>
                        </select>
                    </div>

                </div>

                <div class="row mb-3">
                    <div class="col-12">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea id="observaciones"
                            name="observaciones"
                            class="form-control"
                            rows="2"
                            <?php echo !$editable ? 'disabled' : ''; ?>><?php
                                                                        echo htmlspecialchars($reparto['observaciones'] ?? '');
                                                                        ?></textarea>
                    </div>
                </div>

                <hr>

                <h6>Pedidos preparados disponibles para este reparto</h6>
                <p class="text-muted mb-2">
                    Se muestran los pedidos en estado <strong>Preparado (90)</strong> que:
                    <br>- ya pertenecen a este reparto, o<br>
                    - no están asignados a otros repartos activos.
                </p>

                <?php if (empty($pedidosParaEdicion)): ?>
                    <div class="alert alert-info mb-0">
                        No hay pedidos preparados disponibles.
                    </div>
                <?php else: ?>
                    <div class="table-responsive mt-2">
                        <table class="table table-sm table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;" class="text-center">
                                        <input type="checkbox"
                                            id="chkTodosPedidos"
                                            onclick="toggleSeleccionTodos(this)"
                                            <?php echo !$editable ? 'disabled' : ''; ?>>
                                    </th>
                                    <th>ID Pedido</th>
                                    <th>Fecha Pedido</th>
                                    <th>Total</th>
                                    <th>Observaciones</th>
                                    <th>En este reparto</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pedidosParaEdicion as $p): ?>
                                    <?php
                                    $idPed = (int)$p['idPedidoVenta'];
                                    $checked = isset($idsPedidosActuales[$idPed]) ? 'checked' : '';
                                    ?>
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox"
                                                name="pedidos[]"
                                                value="<?php echo $idPed; ?>"
                                                class="chk-pedido"
                                                <?php echo $checked; ?>
                                                <?php echo !$editable ? 'disabled' : ''; ?>>
                                        </td>
                                        <td><?php echo $idPed; ?></td>
                                        <td><?php echo htmlspecialchars($p['fechaPedido']); ?></td>
                                        <td><?php echo number_format((float)$p['total'], 2, ',', '.'); ?></td>
                                        <td><?php echo htmlspecialchars($p['observaciones']); ?></td>
                                        <td>
                                            <?php if (!empty($checked)): ?>
                                                <span class="badge bg-primary">Sí</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">No</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>

                <small class="text-muted d-block mt-2">Los campos marcados con * son obligatorios.</small>

                <div class="mt-3 d-flex justify-content-end">
                    <a href="repartos.php" class="btn btn-secondary me-2">
                        Volver
                    </a>
                    <?php if ($editable): ?>
                        <button type="submit"
                            class="btn btn-primary"
                            onclick="return validarEditarReparto();">
                            Guardar cambios
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn btn-primary" disabled>
                            Guardar cambios
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleSeleccionTodos(chkMaster) {
        const checks = document.querySelectorAll('.chk-pedido');
        checks.forEach(chk => {
            if (!chk.disabled) chk.checked = chkMaster.checked;
        });
    }

    function validarEditarReparto() {
        const fecha = document.getElementById('fechaReparto')?.value;
        const vehiculo = document.getElementById('idVehiculo')?.value;

        if (!fecha || !vehiculo) {
            alert('La fecha de reparto y el vehículo son obligatorios.');
            return false;
        }

        const checks = document.querySelectorAll('.chk-pedido:checked');
        if (!checks.length) {
            alert('Debe seleccionar al menos un pedido para el reparto.');
            return false;
        }

        return true;
    }
</script>