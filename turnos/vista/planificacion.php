<?php
// Identificador para marcar activo el menú "Turnos Laborales"
$currentPage = 'turnos';

include_once("../../includes/head_app.php");

// Más adelante vamos a requerir un controlador del estilo:
// require_once("../controlador/controladorTurnosProduccion.php");
// $ctrl = new controladorTurnosProduccion();
// $turnos = $ctrl->obtenerTurnosActivos();
// $asignaciones = ...;

$mensaje    = $_GET['msg']  ?? '';
$tipoAlerta = $_GET['tipo'] ?? '';
?>

<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Turnos laborales</h1>
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
            <h3 class="mb-0 text-muted">Asignación de turnos a operarios.</h3>
            <small class="text-muted">
                Seleccioná una fecha y un turno para asignar operarios a la jornada.
            </small>
        </div>
    </div>

    <div class="contenido">
        <div class="card">
            <div class="card-body">

                <!-- FILTROS (Rango de fechas + Turno) -->
                <form id="formFiltrosTurnos" class="row g-3 mb-3">
                    <div class="col-md-3">
                        <label for="fechaDesde" class="form-label">Fecha desde</label>
                        <input type="date" id="fechaDesde" name="fechaDesde" class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label for="fechaHasta" class="form-label">Fecha hasta</label>
                        <input type="date" id="fechaHasta" name="fechaHasta" class="form-control">
                        <small class="text-muted d-block mt-1" id="rangoSemanaTexto">
                            Seleccioná un rango de hasta 7 días.
                        </small>
                    </div>

                    <div class="col-md-3">
                        <label for="turno" class="form-label">Turno</label>
                        <select id="turno" name="turno" class="form-select">
                            <option value="">-- Seleccionar --</option>
                            <!-- Idealmente llenar esto desde la base usando obtenerTurnosActivos() -->
                            <option value="1">Mañana (placeholder)</option>
                            <option value="2">Tarde (placeholder)</option>
                            <option value="3">Noche (placeholder)</option>
                        </select>
                    </div>

                    <div class="col-md-3 d-flex align-items-end justify-content-end">
                        <button type="button" id="btnCargarOperarios" class="btn btn-primary">
                            <ion-icon name="people-outline"></ion-icon>
                            <span>Cargar operarios</span>
                        </button>
                    </div>
                </form>

                <!-- TABLA DE OPERARIOS POR RANGO DE FECHAS -->
                <div class="mt-3">
                    <table id="tablaTurnosOperarios" class="table table-striped table-bordered align-middle">
                        <thead class="table-light">
                            <tr id="headerTurnos">
                                <!-- Encabezados se completan por JS -->
                                <th style="width: 8%;">Legajo</th>
                                <th style="width: 25%;">Empleado</th>
                                <th style="width: 15%;">Puesto</th>
                                <th style="width: 12%;">Estado empleado</th>
                                <!-- Columnas de días dinámicas -->
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="11" class="text-center text-muted">
                                    Seleccioná un rango de fechas y un turno, y luego presioná
                                    <strong>"Cargar operarios"</strong> para ver la planificación.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- BOTÓN GUARDAR ASIGNACIONES -->
                <div class="d-flex justify-content-end mt-3">
                    <button type="button"
                        id="btnGuardarAsignaciones"
                        class="btn btn-success"
                        disabled>
                        <ion-icon name="save-outline"></ion-icon>
                        <span>Guardar asignaciones</span>
                    </button>
                </div>

            </div>
        </div>
    </div>
</div>

<?php

require_once("foot/foot.php");
?>