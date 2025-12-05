<?php
// turnos/vista/index.php

$currentPage = 'turnos';

include_once("../../includes/head_app.php");
require_once("../controlador/controladorTurnos.php");

$ctrl = new controladorTurnos();

// Filtros desde GET
$fechaDesde = $_GET['fechaDesde'] ?? '';
$fechaHasta = $_GET['fechaHasta'] ?? '';
$idTurno    = $_GET['idTurno']    ?? '';
$estado     = $_GET['estado']     ?? '';

// Turnos activos para el combo
$turnosActivos = $ctrl->obtenerTurnosActivos();

// Datos de asignaciones
$asignaciones = $ctrl->listarAsignaciones(
  $fechaDesde ?: null,
  $fechaHasta ?: null,
  $idTurno    ?: null,
  $estado     ?: null
);
?>

<div class="titulo-contenido shadow-sm">
  <h1 class="display-5">Turnos laborales</h1>
</div>

<div class="contenido-principal">

  <div class="encabezado-tabla d-flex justify-content-between align-items-center mb-3 mt-3">
    <div>
      <h3 class="mb-0 text-muted">Asignaciones registradas.</h3>
      <small class="text-muted">
        La tabla muestra los turnos asignados para el rango seleccionado (por defecto 14 dias).
      </small>
    </div>

      <div class="d-flex flex-wrap align-items-center gap-3">

    <!-- CAMPANA SOLICITUDES PENDIENTES -->
    <a href="solicitudes.php"
       class="position-relative text-decoration-none"
       id="notificacionSolicitudesTurno"
       style="cursor:pointer;"
       title="Sin solicitudes pendientes">
        <ion-icon id="iconoCampanaSolicitudes"
                  name="notifications-outline"
                  style="font-size:26px; color:#6c757d;">
        </ion-icon>
        <span id="badgeSolicitudesPendientes"
              class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
              style="display:none;">
            0
        </span>
    </a>

    <!-- BOTONES ACCIÓN -->
    <div class="d-flex flex-wrap gap-2">
        <!-- Botón principal: asignar turnos -->
        <a href="planificacion.php"
           class="btn btn-primary d-flex align-items-center gap-1">
          <ion-icon name="people-outline" style="font-size:18px"></ion-icon>
          <span>Asignar turnos</span>
        </a>

        <!-- Botón: vista calendario -->
        <a href="calendario.php"
           class="btn btn-info d-flex align-items-center gap-1">
          <ion-icon name="calendar-outline" style="font-size:18px"></ion-icon>
          <span>Vista calendario</span>
        </a>

        <!-- Botón: solicitudes de cambio -->
        <a href="solicitudes.php"
           class="btn btn-warning d-flex align-items-center gap-1">
          <ion-icon name="swap-horizontal-outline" style="font-size:18px"></ion-icon>
          <span>Solicitudes de cambio</span>
        </a>
    </div>

  </div>

  </div>


  <div class="contenido">
    <div class="card">
      <div class="card-body">

        <!-- FILTROS -->
        <form method="get" class="row g-3 mb-3">

          <div class="col-md-3">
            <label for="fechaDesde" class="form-label">Fecha desde</label>
            <input type="date" id="fechaDesde" name="fechaDesde"
              class="form-control"
              value="<?php echo htmlspecialchars($fechaDesde); ?>">
          </div>

          <div class="col-md-3">
            <label for="fechaHasta" class="form-label">Fecha hasta</label>
            <input type="date" id="fechaHasta" name="fechaHasta"
              class="form-control"
              value="<?php echo htmlspecialchars($fechaHasta); ?>">
          </div>

          <div class="col-md-3">
            <label for="idTurno" class="form-label">Turno</label>
            <select id="idTurno" name="idTurno" class="form-select">
              <option value="">-- Todos --</option>
              <?php foreach ($turnosActivos as $t): ?>
                <option value="<?php echo $t['idTurno']; ?>"
                  <?php echo ($idTurno == $t['idTurno']) ? 'selected' : ''; ?>>
                  <?php
                  echo htmlspecialchars(
                    $t['nombre'] . " (" . substr($t['horaDesde'], 0, 5) . " - " . substr($t['horaHasta'], 0, 5) . ")"
                  );
                  ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-3">
            <label for="estado" class="form-label">Estado turno</label>
            <select id="estado" name="estado" class="form-select">
              <option value="">-- Todos --</option>
              <option value="Asignado" <?php echo ($estado === 'Asignado')   ? 'selected' : ''; ?>>Asignado</option>
              <option value="Confirmado" <?php echo ($estado === 'Confirmado') ? 'selected' : ''; ?>>Confirmado</option>
              <option value="Finalizado" <?php echo ($estado === 'Finalizado') ? 'selected' : ''; ?>>Finalizado</option>
              <option value="Cancelado" <?php echo ($estado === 'Cancelado') ? 'selected' : ''; ?>>A Reasignar</option>
            </select>
          </div>

          <div class="col-12 d-flex justify-content-end">
            <button type="submit" class="btn btn-secondary me-2">
              <ion-icon name="search-outline"></ion-icon>
              <span>Buscar</span>
            </button>
            <a href="index.php" class="btn btn-outline-secondary">
              <ion-icon name="refresh-outline"></ion-icon>
              <span>Limpiar</span>
            </a>
          </div>
        </form>

        <!-- TABLA -->
        <div class="table-responsive tabla-empleados mt-3">
          <table class="table table-striped table-bordered align-middle">
            <thead class="table-light">
              <tr>
                <th style="width: 12%;">Fecha</th>
                <th style="width: 15%;">Turno</th>
                <th style="width: 19%;">Empleado</th>
                <th style="width: 15%;">Puesto</th>
                <th style="width: 12%;">Estado turno</th>
                <th style="width: 12%;">Estado empleado</th>
                <th style="width: 15%;">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($asignaciones)): ?>
                <tr>
                  <td colspan="7" class="text-center text-muted">
                    No se encontraron asignaciones con los filtros seleccionados.
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($asignaciones as $row): ?>
                  <tr>
                    <td><?php echo date("d-m-Y", strtotime($row['fecha'])); ?></td>
                    <td>
                      <?php echo htmlspecialchars($row['nombreTurno']); ?><br>
                      <small class="text-muted">
                        <?php echo substr($row['horaDesde'], 0, 5) . " - " . substr($row['horaHasta'], 0, 5); ?>
                      </small>
                    </td>
                    <td>
                      <?php echo htmlspecialchars($row['apellido'] . ', ' . $row['nombre']); ?><br>
                      <small class="text-muted">Legajo: <?php echo htmlspecialchars($row['legajo']); ?></small>
                    </td>
                    <td><?php echo htmlspecialchars($row['descrPuesto']); ?></td>
                    <td class="text-center">
                      <?php if ($row['estadoAsignacion'] === 'Asignado'): ?>
                        <span class="badge bg-warning text-dark">Asignado</span>
                      <?php elseif ($row['estadoAsignacion'] === 'Confirmado'): ?>
                        <span class="badge bg-info text-dark">Confirmado</span>
                      <?php elseif ($row['estadoAsignacion'] === 'Finalizado'): ?>
                        <span class="badge bg-success">Finalizado</span>
                      <?php elseif ($row['estadoAsignacion'] === 'Cancelado'): ?>
                        <span class="badge bg-danger">A Reasignar</span>
                      <?php else: ?>
                        <span class="badge bg-secondary">N/A</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center">
                      <?php if ($row['estadoEmpleado'] === 'Activo'): ?>
                        <span class="badge bg-success">Activo</span>
                      <?php else: ?>
                        <span class="badge bg-secondary"><?php echo htmlspecialchars($row['estadoEmpleado']); ?></span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center">
                      <button type="button"
                        class="btn btn-sm btn-success btn-ver"
                        title="Ver Turno"
                        data-id="<?php echo $row['idAsignacion']; ?>">
                        <ion-icon name="eye-outline"></ion-icon>
                      </button>

                      <button type="button"
                        class="btn btn-sm btn-warning btn-reasignar"
                        title="Reasignar"
                        data-id="<?php echo $row['idAsignacion']; ?>">
                        <ion-icon name="swap-horizontal-outline"></ion-icon>
                      </button>

                      <button type="button"
                        class="btn btn-sm btn-danger btn-eliminar"
                        title="Eliminar"
                        data-id="<?php echo $row['idAsignacion']; ?>">
                        <ion-icon name="trash-outline"></ion-icon>
                      </button>
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

<!-- Modal VER ASIGNACIÓN -->
<div class="modal fade" id="modalVerAsignacion" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalle del turno asignado</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <dl class="row mb-0" id="detalleAsignacionContenido">
          <!-- Se completa por JS -->
        </dl>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal REASIGNAR TURNO -->
<div class="modal fade" id="modalReasignarTurno" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Reasignar turno</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <div id="infoTurnoReasignacion" class="mb-3">
          <!-- Se completa por JS: fecha, turno, empleado actual -->
        </div>
        <div class="mb-3">
          <label for="selectNuevoOperario" class="form-label">Nuevo operario</label>
          <select id="selectNuevoOperario" class="form-select">
            <!-- Opciones cargadas por JS -->
          </select>
          <small class="text-muted">
            Solo se listan operarios activos. El sistema verificará solapamientos antes de confirmar.
          </small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnConfirmarReasignacion">
          <ion-icon name="swap-horizontal-outline"></ion-icon>
          <span>Confirmar reasignación</span>
        </button>
      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const icono = document.getElementById('iconoCampanaSolicitudes');
    const badge = document.getElementById('badgeSolicitudesPendientes');
    const cont  = document.getElementById('notificacionSolicitudesTurno');

    if (!icono || !badge || !cont) return;

    async function actualizarCampanaSolicitudes() {
        try {
            const resp = await fetch('notificaciones_solicitudes_turno.php');
            const json = await resp.json();

            if (!json.ok) {
                // Si hay error, dejamos la campana gris y sin badge
                icono.style.color = "#6c757d";
                icono.setAttribute("name", "notifications-outline");
                badge.style.display = "none";
                cont.setAttribute("title", "Sin solicitudes pendientes");
                return;
            }

            const pendientes = json.pendientes ?? 0;

            if (pendientes > 0) {
                icono.style.color = "#dc3545";        // rojo
                icono.setAttribute("name", "notifications");
                badge.style.display = "inline-block";
                badge.textContent = pendientes;

                cont.setAttribute(
                    "title",
                    `Tenés ${pendientes} solicitud${pendientes>1?'es':''} de cambio pendientes`
                );
            } else {
                icono.style.color = "#6c757d";        // gris
                icono.setAttribute("name", "notifications-outline");
                badge.style.display = "none";
                cont.setAttribute("title", "Sin solicitudes pendientes");
            }

        } catch (error) {
            console.error('Error al cargar notificaciones de solicitudes:', error);
            icono.style.color = "#6c757d";
            icono.setAttribute("name", "notifications-outline");
            badge.style.display = "none";
            cont.setAttribute("title", "Sin solicitudes pendientes");
        }
    }

    // Carga inicial
    actualizarCampanaSolicitudes();

    // Si querés refrescar cada X segundos, podés descomentar esto:
    // setInterval(actualizarCampanaSolicitudes, 60000);
});
</script>
<?php
require_once("foot/foot.php");
?>