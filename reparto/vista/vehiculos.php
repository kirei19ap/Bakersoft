<?php
// reparto/vista/vehiculos.php
$currentPage = 'vehiculos';

include_once("../../includes/head_app.php");
require_once("../controlador/controladorVehiculo.php");

$ctrl = new ControladorVehiculo();
$mensaje = "";
$tipoMensaje = ""; // success / danger

// Manejo de acciones (crear/editar/desactivar)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    $data = [
        'patente'     => trim($_POST['patente'] ?? ''),
        'descripcion' => trim($_POST['descripcion'] ?? ''),
        'marca'       => trim($_POST['marca'] ?? ''),
        'modelo'      => trim($_POST['modelo'] ?? ''),
        'anio'        => $_POST['anio'] ?? null,
        'capacidadKg' => $_POST['capacidadKg'] ?? null,
        'idChofer'    => $_POST['idChofer'] ?? null,
        'estado'      => $_POST['estado'] ?? 'Activo'
    ];

    try {
        if ($accion === 'crear') {
            if (empty($data['patente']) || empty($data['idChofer'])) {
                throw new Exception("Patente y Chofer son obligatorios.");
            }
            $ok = $ctrl->crearVehiculo($data);
            if ($ok) {
                $mensaje = "Vehículo creado correctamente.";
                $tipoMensaje = "success";
            } else {
                throw new Exception("No se pudo crear el vehículo.");
            }
        } elseif ($accion === 'editar') {
            $idVehiculo = (int)($_POST['idVehiculo'] ?? 0);
            if ($idVehiculo <= 0) {
                throw new Exception("ID de vehículo inválido.");
            }
            if (empty($data['patente']) || empty($data['idChofer'])) {
                throw new Exception("Patente y Chofer son obligatorios.");
            }
            $ok = $ctrl->actualizarVehiculo($idVehiculo, $data);
            if ($ok) {
                $mensaje = "Vehículo actualizado correctamente.";
                $tipoMensaje = "success";
            } else {
                throw new Exception("No se pudo actualizar el vehículo.");
            }
        } elseif ($accion === 'desactivar') {
            $idVehiculo = (int)($_POST['idVehiculo'] ?? 0);
            if ($idVehiculo <= 0) {
                throw new Exception("ID de vehículo inválido.");
            }
            $ok = $ctrl->desactivarVehiculo($idVehiculo);
            if ($ok) {
                $mensaje = "Vehículo desactivado correctamente.";
                $tipoMensaje = "success";
            } else {
                throw new Exception("No se pudo desactivar el vehículo.");
            }
        }
    } catch (Exception $e) {
        $mensaje = $e->getMessage();
        $tipoMensaje = "danger";
    }
}

// Datos para la pantalla
$vehiculos = $ctrl->listarVehiculos(false);
$choferes  = $ctrl->listarChoferes();

// Helper para estados
function badgeEstadoVehiculo($estado)
{
    $class = ($estado === 'Activo') ? 'bg-success' : 'bg-secondary';
    return "<span class='badge $class'>{$estado}</span>";
}

?>
<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Gestión de Vehículos</h1>
</div>

<div class="contenido-principal">

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-<?php echo $tipoMensaje; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($mensaje); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Listado de vehículos</h5>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalVehiculo"
            onclick="abrirModalCrear()">
            <ion-icon name="add-outline"></ion-icon> Nuevo Vehículo
        </button>
    </div>
    <div class="contenido">
        <div class="tabla-empleados">
            <div class="card">
                <div class="card-body">
                    <table id="tablaVehiculos" class="table table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Patente</th>
                                <th>Descripción</th>
                                <th>Chofer</th>
                                <th>Marca/Modelo</th>
                                <th>Año</th>
                                <th>Capacidad (Kg)</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vehiculos as $v): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($v['patente']); ?></td>
                                    <td><?php echo htmlspecialchars($v['descripcion']); ?></td>
                                    <td><?php echo htmlspecialchars($v['apellido'] . ', ' . $v['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars(trim($v['marca'] . ' ' . $v['modelo'])); ?></td>
                                    <td><?php echo htmlspecialchars($v['anio']); ?></td>
                                    <td><?php echo htmlspecialchars($v['capacidadKg']); ?></td>
                                    <td><?php echo badgeEstadoVehiculo($v['estado']); ?></td>
                                    <td class="text-center">
                                        <button type="button"
                                            class="btn btn-sm btn-secondary me-1"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalVehiculo"
                                            onclick="abrirModalEditar(<?php echo htmlspecialchars(json_encode($v)); ?>)">
                                            <ion-icon name="create-outline"></ion-icon>
                                        </button>

                                        <form method="post" class="d-inline"
                                            onsubmit="return confirm('¿Seguro que desea desactivar este vehículo?');">
                                            <input type="hidden" name="accion" value="desactivar">
                                            <input type="hidden" name="idVehiculo" value="<?php echo $v['idVehiculo']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <ion-icon name="trash-outline"></ion-icon>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Crear/Editar Vehículo -->
    <div class="modal fade" id="modalVehiculo" tabindex="-1" aria-labelledby="modalVehiculoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <form method="post" id="formVehiculo">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalVehiculoLabel">Nuevo Vehículo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="accion" id="accion" value="crear">
                        <input type="hidden" name="idVehiculo" id="idVehiculo" value="">

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="patente" class="form-label">Patente *</label>
                                <input type="text" class="form-control" id="patente" name="patente" required>
                            </div>
                            <div class="col-md-8">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <input type="text" class="form-control" id="descripcion" name="descripcion">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="marca" class="form-label">Marca</label>
                                <input type="text" class="form-control" id="marca" name="marca">
                            </div>
                            <div class="col-md-4">
                                <label for="modelo" class="form-label">Modelo</label>
                                <input type="text" class="form-control" id="modelo" name="modelo">
                            </div>
                            <div class="col-md-4">
                                <label for="anio" class="form-label">Año</label>
                                <input type="number" class="form-control" id="anio" name="anio" min="1980" max="2100">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="capacidadKg" class="form-label">Capacidad (Kg)</label>
                                <input type="number" step="0.01" class="form-control" id="capacidadKg" name="capacidadKg">
                            </div>
                            <div class="col-md-5">
                                <label for="idChofer" class="form-label">Chofer *</label>
                                <select id="idChofer" name="idChofer" class="form-select" required>
                                    <option value="">-- Seleccione --</option>
                                    <?php foreach ($choferes as $c): ?>
                                        <option value="<?php echo $c['id_empleado']; ?>">
                                            <?php echo htmlspecialchars($c['apellido'] . ', ' . $c['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select id="estado" name="estado" class="form-select">
                                    <option value="Activo">Activo</option>
                                    <option value="Inactivo">Inactivo</option>
                                </select>
                            </div>
                        </div>

                        <small class="text-muted">Los campos marcados con * son obligatorios.</small>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script DataTable + helpers modal -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.DataTable) {
                new DataTable('#tablaVehiculos', {
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
                        "emptyTable": "No hay vehículos cargados.",
                    },
                    pageLength: 10,
                    scrollX: false
                });
            }
        });

        function abrirModalCrear() {
            document.getElementById('modalVehiculoLabel').innerText = 'Nuevo Vehículo';
            document.getElementById('accion').value = 'crear';
            document.getElementById('idVehiculo').value = '';

            document.getElementById('patente').value = '';
            document.getElementById('descripcion').value = '';
            document.getElementById('marca').value = '';
            document.getElementById('modelo').value = '';
            document.getElementById('anio').value = '';
            document.getElementById('capacidadKg').value = '';
            document.getElementById('idChofer').value = '';
            document.getElementById('estado').value = 'Activo';
        }

        function abrirModalEditar(vehiculo) {
            document.getElementById('modalVehiculoLabel').innerText = 'Editar Vehículo';
            document.getElementById('accion').value = 'editar';
            document.getElementById('idVehiculo').value = vehiculo.idVehiculo;

            document.getElementById('patente').value = vehiculo.patente ?? '';
            document.getElementById('descripcion').value = vehiculo.descripcion ?? '';
            document.getElementById('marca').value = vehiculo.marca ?? '';
            document.getElementById('modelo').value = vehiculo.modelo ?? '';
            document.getElementById('anio').value = vehiculo.anio ?? '';
            document.getElementById('capacidadKg').value = vehiculo.capacidadKg ?? '';
            document.getElementById('idChofer').value = vehiculo.idChofer ?? '';
            document.getElementById('estado').value = vehiculo.estado ?? 'Activo';
        }
    </script>
    <?php
    require_once("foot/foot.php")
    ?>