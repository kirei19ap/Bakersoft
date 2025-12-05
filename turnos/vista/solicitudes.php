<?php
// turnos/vista/solicitudes.php
session_start();

$currentPage = 'turnos'; // resalta "Turnos Laborales" en el menú

include_once("../../includes/head_app.php");
?>

<div class="titulo-contenido shadow-sm">
  <h1 class="display-5">Solicitudes de cambio de turno</h1>
</div>

<div class="contenido-principal">
  <div class="contenido">
    <div class="card">
      <div class="card-body">

        <div class="d-flex justify-content-between align-items-center mb-3">
          <div>
            <h5 class="mb-0 text-muted">Solicitudes pendientes</h5>
            <small class="text-muted">
              Administrá las solicitudes enviadas por los empleados para cambio o rechazo de turnos.
            </small>
          </div>

          <a href="index.php"
             class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1">
            <ion-icon name="arrow-back-outline" style="font-size:18px"></ion-icon>
            <span>Volver a turnos</span>
          </a>
        </div>

        <div class="table-responsive">
          <table id="tablaSolicitudesTurno" class="table table-striped table-bordered align-middle">
            <thead class="table-light">
              <tr>
                <th style="width: 8%;">Fecha turno</th>
                <th style="width: 18%;">Turno</th>
                <th style="width: 20%;">Empleado</th>
                <th style="width: 12%;">Tipo</th>
                <th style="width: 25%;">Motivo</th>
                <th style="width: 12%;">Fecha solicitud</th>
                <th style="width: 15%;" class="text-center">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td colspan="7" class="text-center text-muted">
                  Cargando solicitudes...
                </td>
              </tr>
            </tbody>
          </table>
        </div>

      </div>
    </div>
  </div>
</div>

<?php
echo '<script src="solicitudes_turnos_admin.js"></script>';
require_once("foot/foot.php");
?>
