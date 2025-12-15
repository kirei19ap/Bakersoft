<?php
// turnos/vista/reportes_turnos_listado.php

$currentPage = 'reportespedidos'; // resalta "Estadísticas y Reportes" en el menú
include_once("../../includes/head_app.php");
require_once("../controlador/controladorTurnos.php");

$ctrlTurnos = new controladorTurnos();

// Filtros
$fechaDesdeTurnos = $_GET['desde_turnos']  ?? date('Y-m-01');
$fechaHastaTurnos = $_GET['hasta_turnos']  ?? date('Y-m-t');
$estadoTurnoRep   = $_GET['estado_turno']  ?? '';
$empleadoRep      = $_GET['empleado']      ?? '';

// Operarios para el combo
$operarios = $ctrlTurnos->obtenerOperariosActivos();

// Listado sólo si se presiona "Aplicar filtros"
$listadoTurnos = [];
if (isset($_GET['filtrar_turnos'])) {
    $listadoTurnos = $ctrlTurnos->obtenerReporteTurnos(
        $empleadoRep ?: null,
        $fechaDesdeTurnos,
        $fechaHastaTurnos,
        $estadoTurnoRep
    );
}

function formatearFechaDMY($fechaYmd) {
    if (!$fechaYmd) return '';
    $partes = explode('-', $fechaYmd);
    if (count($partes) !== 3) return $fechaYmd;
    return $partes[2] . '-' . $partes[1] . '-' . $partes[0];
}
?>

<div class="titulo-contenido shadow-sm d-flex justify-content-between align-items-center">
  <div>
    <h1 class="display-5 mb-0">Reporte de turnos laborales</h1>
    <small class="text-muted">
      Listado de turnos por rango de fechas, estado y empleado, con opción de exportar a PDF.
    </small>
  </div>
  <div>
    <a href="../../reportes/vista/reportes_produccion.php"
       class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
      <ion-icon name="arrow-back-outline" style="font-size:18px;"></ion-icon>
      <span>Volver al panel de reportes</span>
    </a>
  </div>
</div>

<div class="contenido-principal">
  <div class="contenido mt-3">

    <div class="card shadow-sm">
      <div class="card-body">

        <!-- FORMULARIO DE FILTROS -->
        <form class="row g-3 mb-3" method="GET">
          <div class="col-md-3">
            <label for="desde_turnos" class="form-label">Fecha desde</label>
            <input type="date"
                   id="desde_turnos"
                   name="desde_turnos"
                   class="form-control"
                   value="<?php echo htmlspecialchars($fechaDesdeTurnos); ?>">
          </div>

          <div class="col-md-3">
            <label for="hasta_turnos" class="form-label">Fecha hasta</label>
            <input type="date"
                   id="hasta_turnos"
                   name="hasta_turnos"
                   class="form-control"
                   value="<?php echo htmlspecialchars($fechaHastaTurnos); ?>">
          </div>

          <div class="col-md-3">
            <label for="estado_turno" class="form-label">Estado turno</label>
            <select id="estado_turno" name="estado_turno" class="form-select">
              <option value="">-- Todos --</option>
              <option value="Asignado"   <?php echo ($estadoTurnoRep === 'Asignado')   ? 'selected' : ''; ?>>Asignado</option>
              <option value="Confirmado" <?php echo ($estadoTurnoRep === 'Confirmado') ? 'selected' : ''; ?>>Confirmado</option>
              <option value="Finalizado" <?php echo ($estadoTurnoRep === 'Finalizado') ? 'selected' : ''; ?>>Finalizado</option>
              <option value="Cancelado"  <?php echo ($estadoTurnoRep === 'Cancelado')  ? 'selected' : ''; ?>>A reasignar</option>
            </select>
          </div>

          <div class="col-md-3">
            <label for="empleado" class="form-label">Empleado</label>
            <select id="empleado" name="empleado" class="form-select">
              <option value="">-- Todos --</option>
              <?php foreach ($operarios as $op): ?>
                <?php
                  $selected = ($empleadoRep !== '' && (int)$empleadoRep === (int)$op['id_empleado']) ? 'selected' : '';
                  $labelEmp = $op['apellido'] . ', ' . $op['nombre'];
                  if (!empty($op['legajo'])) {
                      $labelEmp .= " (Legajo: {$op['legajo']})";
                  }
                ?>
                <option value="<?php echo $op['id_empleado']; ?>" <?php echo $selected; ?>>
                  <?php echo htmlspecialchars($labelEmp); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-12 d-flex justify-content-end gap-2 mt-2">
            <!-- Botón filtrar (GET) -->
            <button type="submit"
                    name="filtrar_turnos"
                    value="1"
                    class="btn btn-primary d-flex align-items-center gap-1">
              <ion-icon name="funnel-outline" style="font-size:18px"></ion-icon>
              <span>Aplicar filtros</span>
            </button>

            <!-- Botón exportar PDF (POST hacia el script de PDF) -->
            <button type="submit"
                    formaction="reporte_turnos_pdf.php"
                    formmethod="POST"
                    class="btn btn-outline-danger d-flex align-items-center gap-1">
              <ion-icon name="document-outline" style="font-size:18px"></ion-icon>
              <span>Exportar PDF</span>
            </button>
          </div>
        </form>

        <!-- TABLA DE RESULTADOS -->
        <div class="table-responsive">
          <table class="table table-striped table-bordered align-middle">
            <thead class="table-light">
              <tr>
                <th style="width: 10%;">Fecha</th>
                <th style="width: 15%;">Turno</th>
                <th style="width: 12%;">Horario</th>
                <th style="width: 25%;">Empleado</th>
                <th style="width: 10%;">Legajo</th>
                <th style="width: 10%;">Estado</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($listadoTurnos)): ?>
                <tr>
                  <td colspan="6" class="text-center text-muted">
                    <?php if (isset($_GET['filtrar_turnos'])): ?>
                      No se encontraron turnos con los filtros seleccionados.
                    <?php else: ?>
                      Aplicá filtros y presioná "Aplicar filtros" para ver el listado.
                    <?php endif; ?>
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($listadoTurnos as $t): ?>
                  <tr>
                    <td><?php echo formatearFechaDMY($t['fecha']); ?></td>
                    <td><?php echo htmlspecialchars($t['nombreTurno']); ?></td>
                    <td><?php echo substr($t['horaDesde'], 0, 5) . ' - ' . substr($t['horaHasta'], 0, 5); ?></td>
                    <td><?php echo htmlspecialchars($t['apellidoEmpleado'] . ', ' . $t['nombreEmpleado']); ?></td>
                    <td><?php echo htmlspecialchars($t['legajo'] ?? '-'); ?></td>
                    <td>
                      <?php
                        $estado = $t['estadoAsignacion'] ?? '';
                        if ($estado === 'Asignado') {
                          echo '<span class="badge bg-warning text-dark">Asignado</span>';
                        } elseif ($estado === 'Confirmado') {
                          echo '<span class="badge bg-info text-dark">Confirmado</span>';
                        } elseif ($estado === 'Finalizado') {
                          echo '<span class="badge bg-success">Finalizado</span>';
                        } elseif ($estado === 'Cancelado') {
                          echo '<span class="badge bg-danger">A reasignar</span>';
                        } else {
                          echo '<span class="badge bg-secondary">N/A</span>';
                        }
                      ?>
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
