<?php
// reparto/vista/repartos.php
$currentPage = 'repartos';

include_once("../../includes/head_app.php");
require_once("../controlador/controladorReparto.php");
require_once("../controlador/controladorVehiculo.php");

$ctrlReparto   = new ControladorReparto();
$ctrlVehiculo  = new ControladorVehiculo();

$mensaje = "";
$tipoMensaje = ""; // success | danger

// === Manejo de acciones (por ahora: crear reparto) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crearReparto') {
        try {
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

            // Cabecera para el modelo
            $cabecera = [
                'fechaReparto'  => $fechaReparto,
                'horaSalida'    => $horaSalida ?: null,
                'zona'          => $zona,
                'idVehiculo'    => $idVehiculo,
                'observaciones' => $observaciones ?: null
            ];

            // Detalle: pedidos seleccionados con un ordenEntrega simple (1,2,3,...)
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

            $idReparto = $ctrlReparto->crearReparto($cabecera, $detallePedidos);

            if ($idReparto === false) {
                throw new Exception("Ocurrió un error al registrar el reparto.");
            }

            $mensaje = "Reparto registrado correctamente (ID: {$idReparto}).";
            $tipoMensaje = "success";
        } catch (Exception $e) {
            $mensaje = $e->getMessage();
            $tipoMensaje = "danger";
        }
    } elseif ($accion === 'cambiarEstadoReparto') {
        try {
            $idReparto   = (int)($_POST['idReparto'] ?? 0);
            $nuevoEstado = $_POST['nuevoEstado'] ?? '';

            if ($idReparto <= 0) {
                throw new Exception("ID de reparto inválido.");
            }

            $ok = $ctrlReparto->cambiarEstadoReparto($idReparto, $nuevoEstado);
            if ($ok) {
                $mensaje = "Estado del reparto actualizado correctamente.";
                $tipoMensaje = "success";
            } else {
                throw new Exception("No se pudo actualizar el estado del reparto.");
            }
        } catch (Exception $e) {
            $mensaje = $e->getMessage();
            $tipoMensaje = "danger";
        }
    }
}

// === Filtros para listado de repartos (por ahora simples, se pueden mejorar luego) ===
$filtros = [
    'fechaDesde' => $_GET['fechaDesde'] ?? null,
    'fechaHasta' => $_GET['fechaHasta'] ?? null,
    'estado'     => $_GET['estado'] ?? null,
    'idVehiculo' => $_GET['idVehiculo'] ?? null,
];

$repartos            = $ctrlReparto->listarRepartos($filtros);
$vehiculosActivos    = $ctrlVehiculo->listarVehiculos(true); // Solo activos para combos
$pedidosDisponibles  = $ctrlReparto->listarPedidosPreparadosDisponibles();

// Helper para badge de estado
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
    <h1 class="display-5">Gestión de Repartos</h1>
</div>

<div class="contenido-principal">

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($mensaje); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="card mb-3">
        <div class="card-body">
            <form class="row g-3" method="get">
                <div class="col-md-3">
                    <label for="fechaDesde" class="form-label">Fecha desde</label>
                    <input type="date" id="fechaDesde" name="fechaDesde"
                        class="form-control"
                        value="<?php echo htmlspecialchars($filtros['fechaDesde'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label for="fechaHasta" class="form-label">Fecha hasta</label>
                    <input type="date" id="fechaHasta" name="fechaHasta"
                        class="form-control"
                        value="<?php echo htmlspecialchars($filtros['fechaHasta'] ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label for="estado" class="form-label">Estado del reparto</label>
                    <select id="estado" name="estado" class="form-select">
                        <option value="">-- Todos --</option>
                        <?php
                        $estados = ['Planificado', 'En Curso', 'Finalizado', 'Cancelado'];
                        foreach ($estados as $est) {
                            $sel = ($filtros['estado'] ?? '') === $est ? 'selected' : '';
                            echo "<option value=\"{$est}\" {$sel}>{$est}</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filtroVehiculo" class="form-label">Vehículo</label>
                    <select id="filtroVehiculo" name="idVehiculo" class="form-select">
                        <option value="">-- Todos --</option>
                        <?php foreach ($vehiculosActivos as $v): ?>
                            <?php
                            $sel = ((string)($filtros['idVehiculo'] ?? '') === (string)$v['idVehiculo']) ? 'selected' : '';
                            $texto = $v['patente'] . ' - ' . ($v['descripcion'] ?? '');
                            ?>
                            <option value="<?php echo $v['idVehiculo']; ?>" <?php echo $sel; ?>>
                                <?php echo htmlspecialchars($texto); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <ion-icon name="search-outline"></ion-icon> Filtrar
                    </button>
                    <a href="repartos.php" class="btn btn-outline-secondary">
                        <ion-icon name="refresh-outline"></ion-icon> Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Encabezado y botón nuevo reparto -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Listado de repartos</h5>
        <button type="button" class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#modalNuevoReparto"
            onclick="abrirModalNuevoReparto()">
            <ion-icon name="add-outline"></ion-icon> Nuevo reparto
        </button>
    </div>

    <!-- Tabla de repartos -->
    <div class="contenido">
        <div class="tabla-empleados">
            <div class="card">
                <div class="card-body">
                    <table id="tablaRepartos" class="table table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha reparto</th>
                                <th>Vehículo</th>
                                <th>Chofer</th>
                                <th>Zona</th>
                                <th>Pedidos</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($repartos as $r): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($r['fechaReparto']); ?></td>
                                    <td>
                                        <?php
                                        $veh = $r['patente'] . ' - ' . ($r['vehiculoDescripcion'] ?? '');
                                        echo htmlspecialchars(trim($veh));
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($r['apellido'] . ', ' . $r['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($r['zona']); ?></td>
                                    <td><?php echo (int)$r['cantidadPedidos']; ?></td>
                                    <td><?php echo badgeEstadoReparto($r['estado']); ?></td>
                                    <td class="text-center">

                                        <!-- Ver -->
                                        <button type="button"
                                            class="btn btn-sm btn-secondary me-1 mb-1"
                                            title="Ver detalle del reparto"
                                            onclick="window.location.href='verReparto.php?idReparto=<?php echo $r['idReparto']; ?>'">
                                            <ion-icon name="eye-outline"></ion-icon>
                                        </button>

                                        <!-- Editar (solo si está Planificado) -->
                                        <?php if ($r['estado'] === 'Planificado'): ?>
                                            <button type="button"
                                                class="btn btn-sm btn-primary me-1 mb-1"
                                                title="Editar reparto"
                                                onclick="window.location.href='editarReparto.php?idReparto=<?php echo $r['idReparto']; ?>'">
                                                <ion-icon name="create-outline"></ion-icon>
                                            </button>
                                        <?php else: ?>
                                            <button type="button"
                                                class="btn btn-sm btn-primary me-1 mb-1"
                                                title="Editar reparto"
                                                disabled>
                                                <ion-icon name="create-outline"></ion-icon>
                                            </button>
                                        <?php endif; ?>


                                        <!-- Acciones según estado -->
                                        <?php if ($r['estado'] === 'Planificado'): ?>

                                            <!-- Pasar a En Curso -->
                                            <form method="post"
                                                class="d-inline form-estado-reparto"
                                                data-accion-reparto="cambiarEstado">
                                                <input type="hidden" name="accion" value="cambiarEstadoReparto">
                                                <input type="hidden" name="idReparto" value="<?php echo $r['idReparto']; ?>">
                                                <input type="hidden" name="nuevoEstado" value="En Curso">
                                                <button type="submit"
                                                    class="btn btn-sm btn-info me-1 mb-1"
                                                    title="Marcar reparto como En Curso">
                                                    <ion-icon name="play-outline"></ion-icon>
                                                </button>
                                            </form>

                                            <!-- Cancelar -->
                                            <form method="post"
                                                class="d-inline form-estado-reparto"
                                                data-accion-reparto="cambiarEstado">
                                                <input type="hidden" name="accion" value="cambiarEstadoReparto">
                                                <input type="hidden" name="idReparto" value="<?php echo $r['idReparto']; ?>">
                                                <input type="hidden" name="nuevoEstado" value="Cancelado">
                                                <button type="submit"
                                                    class="btn btn-sm btn-danger mb-1"
                                                    title="Cancelar reparto">
                                                    <ion-icon name="close-circle-outline"></ion-icon>
                                                </button>
                                            </form>

                                        <?php elseif ($r['estado'] === 'En Curso'): ?>

                                            <!-- Finalizar -->
                                            <form method="post"
                                                class="d-inline form-estado-reparto"
                                                data-accion-reparto="cambiarEstado">
                                                <input type="hidden" name="accion" value="cambiarEstadoReparto">
                                                <input type="hidden" name="idReparto" value="<?php echo $r['idReparto']; ?>">
                                                <input type="hidden" name="nuevoEstado" value="Finalizado">
                                                <button type="submit"
                                                    class="btn btn-sm btn-success me-1 mb-1"
                                                    title="Finalizar reparto">
                                                    <ion-icon name="checkmark-done-outline"></ion-icon>
                                                </button>
                                            </form>

                                            <!-- Cancelar -->
                                            <form method="post"
                                                class="d-inline form-estado-reparto"
                                                data-accion-reparto="cambiarEstado">
                                                <input type="hidden" name="accion" value="cambiarEstadoReparto">
                                                <input type="hidden" name="idReparto" value="<?php echo $r['idReparto']; ?>">
                                                <input type="hidden" name="nuevoEstado" value="Cancelado">
                                                <button type="submit"
                                                    class="btn btn-sm btn-danger mb-1"
                                                    title="Cancelar reparto">
                                                    <ion-icon name="close-circle-outline"></ion-icon>
                                                </button>
                                            </form>

                                        <?php endif; ?>


                                    </td>



                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Nuevo Reparto -->
<div class="modal fade" id="modalNuevoReparto" tabindex="-1" aria-labelledby="modalNuevoRepartoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <form method="post" id="formNuevoReparto">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalNuevoRepartoLabel">Nuevo reparto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="accion" value="crearReparto">

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="fechaReparto" class="form-label">Fecha reparto *</label>
                            <input type="date" id="fechaReparto" name="fechaReparto" class="form-control" required>
                        </div>
                        <div class="col-md-3">
                            <label for="horaSalida" class="form-label">Hora salida</label>
                            <input type="time" id="horaSalida" name="horaSalida" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label for="idVehiculo" class="form-label">Vehículo *</label>
                            <select id="idVehiculo" name="idVehiculo" class="form-select" required>
                                <option value="">-- Seleccione --</option>
                                <?php foreach ($vehiculosActivos as $v): ?>
                                    <?php $texto = $v['patente'] . ' - ' . ($v['descripcion'] ?? ''); ?>
                                    <option value="<?php echo $v['idVehiculo']; ?>">
                                        <?php echo htmlspecialchars(trim($texto)); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="zona" class="form-label">Zona</label>
                            <select class="form-select" id="zona" name="zona" required>
                                <option value="">-- Seleccionar --</option>
                                <option value="NORTE">NORTE</option>
                                <option value="SUR">SUR</option>
                                <option value="ESTE">ESTE</option>
                                <option value="OESTE">OESTE</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-12">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea id="observaciones" name="observaciones" class="form-control" rows="2"></textarea>
                        </div>
                    </div>

                    <hr>

                    <h6>Pedidos preparados disponibles para asignar</h6>
                    <p class="text-muted mb-2">
                        Se muestran únicamente los pedidos en estado <strong>Preparado</strong> que no están ya asociados a un reparto Planificado o En Curso.
                    </p>

                    <?php if (empty($pedidosDisponibles)): ?>
                        <div class="alert alert-info mb-0">
                            No hay pedidos preparados disponibles para asignar a un reparto.
                        </div>
                    <?php else: ?>
                        <div class="mt-2">
                            <table id="tablaPedidosDisponibles" class="table table-sm table-striped table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40px;" class="text-center">
                                            <!-- checkbox general para seleccionar/deseleccionar todos -->
                                            <input type="checkbox" id="chkTodosPedidos" onclick="toggleSeleccionTodos(this)">
                                        </th>
                                        <th>ID Pedido</th>
                                        <th>Fecha Pedido</th>
                                        <th>Total</th>
                                        <th>Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pedidosDisponibles as $p): ?>
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox"
                                                    name="pedidos[]"
                                                    value="<?php echo $p['idPedidoVenta']; ?>"
                                                    class="chk-pedido">
                                            </td>
                                            <td><?php echo (int)$p['idPedidoVenta']; ?></td>
                                            <td><?php echo htmlspecialchars($p['fechaPedido']); ?></td>
                                            <td><?php echo number_format((float)$p['total'], 2, ',', '.'); ?></td>
                                            <td><?php echo htmlspecialchars($p['observaciones']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <small class="text-muted d-block mt-2">Los campos marcados con * son obligatorios.</small>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"
                        onclick="return validarNuevoReparto();">
                        Guardar reparto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts DataTable + helpers -->
<!-- Scripts DataTable + helpers -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // DataTable para la tabla de repartos
        if (window.DataTable) {
            new DataTable('#tablaRepartos', {
                responsive: true,
                language: {
                    "decimal": ",",
                    "thousands": ".",
                    "info": "Mostrando _END_ registros de un total de _TOTAL_",
                    "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "infoFiltered": "(filtrado de un total de _MAX_ registros)",
                    "loadingRecords": "Cargando...",
                    "lengthMenu": "Mostrar _MENU_",
                    "paginate": {
                        "first": "<<",
                        "last": ">>",
                        "next": ">",
                        "previous": "<"
                    },
                    "search": "Buscador:",
                    "searchPlaceholder": "Buscar...",
                    "emptyTable": "No hay repartos registrados.",
                },
                searching: false,
                pageLength: 10,
                scrollX: false
            });

            // DataTable para pedidos disponibles (si existe)
            const tablaPedidos = document.getElementById('tablaPedidosDisponibles');
            if (tablaPedidos) {
                new DataTable('#tablaPedidosDisponibles', {
                    responsive: true,
                    language: {
                        "decimal": ",",
                        "thousands": ".",
                        "info": "Mostrando _END_ registros de un total de _TOTAL_",
                        "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                        "infoFiltered": "(filtrado de un total de _MAX_ registros)",
                        "loadingRecords": "Cargando...",
                        "lengthMenu": "Mostrar _MENU_",
                        "paginate": {
                            "first": "<<",
                            "last": ">>",
                            "next": ">",
                            "previous": "<"
                        },
                        "search": "Buscador:",
                        "searchPlaceholder": "Buscar...",
                        "emptyTable": "No hay pedidos preparados disponibles.",
                    },
                    pageLength: 10,
                    scrollX: false
                });
            }
        }

        // Confirmación con SweetAlert para TODOS los cambios de estado
        const formsEstado = document.querySelectorAll('form.form-estado-reparto');

        formsEstado.forEach(function(form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const nuevoEstadoInput = form.querySelector('input[name="nuevoEstado"]');
                const nuevoEstado = nuevoEstadoInput ? nuevoEstadoInput.value : '';
                let texto = '';

                switch (nuevoEstado) {
                    case 'En Curso':
                        texto = 'El reparto pasará a estado "En Curso".';
                        break;
                    case 'Finalizado':
                        texto = 'Se marcará el reparto como "Finalizado" y se considerará que los pedidos fueron entregados.';
                        break;
                    case 'Cancelado':
                        texto = 'El reparto se marcará como "Cancelado" y los pedidos quedarán disponibles para reasignar.';
                        break;
                    default:
                        texto = 'Se cambiará el estado del reparto.';
                        break;
                }

                Swal.fire({
                    icon: 'question',
                    title: 'Confirmar cambio de estado',
                    text: texto,
                    showCancelButton: true,
                    confirmButtonText: 'Sí, continuar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });

    function abrirModalNuevoReparto() {
        // Setear fecha por defecto (hoy) al abrir
        const inputFecha = document.getElementById('fechaReparto');
        if (inputFecha && !inputFecha.value) {
            const hoy = new Date().toISOString().slice(0, 10);
            inputFecha.value = hoy;
        }

        // Limpiar campos de cabecera
        document.getElementById('horaSalida').value = '';
        document.getElementById('idVehiculo').value = '';
        document.getElementById('zona').value = '';
        document.getElementById('observaciones').value = '';

        // Desmarcar todos los pedidos
        const checks = document.querySelectorAll('.chk-pedido');
        checks.forEach(chk => chk.checked = false);

        const chkTodos = document.getElementById('chkTodosPedidos');
        if (chkTodos) chkTodos.checked = false;
    }

    function toggleSeleccionTodos(chkMaster) {
        const checks = document.querySelectorAll('.chk-pedido');
        checks.forEach(chk => chk.checked = chkMaster.checked);
    }

    // Validación del alta de reparto con SweetAlert en lugar de alert()
    function validarNuevoReparto() {
        const fecha = document.getElementById('fechaReparto')?.value;
        const vehiculo = document.getElementById('idVehiculo')?.value;

        if (!fecha || !vehiculo) {
            Swal.fire({
                icon: 'warning',
                title: 'Datos incompletos',
                text: 'La fecha de reparto y el vehículo son obligatorios.'
            });
            return false;
        }

        const checks = document.querySelectorAll('.chk-pedido:checked');
        if (!checks.length) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin pedidos seleccionados',
                text: 'Debe seleccionar al menos un pedido para el reparto.'
            });
            return false;
        }

        return true;
    }
</script>
<?php
require_once("foot/foot.php")
?>