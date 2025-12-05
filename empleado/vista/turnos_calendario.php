<?php
$currentPage = ''; // usamos el layout general

include_once("head/head_turnos.php");
?>

<div class="titulo-contenido shadow-sm">
  <h1 class="display-5">Calendario de mis turnos</h1>
</div>

<div class="contenido-principal">
  <div class="contenido">
    <div class="card">
      <div class="card-body">

        <!-- FILTROS / NAVEGACIÓN DE MES -->
        <div class="row align-items-center mb-3">
          <div class="col-12 col-md-4 mb-2 mb-md-0">
            <h5 id="tituloMes" class="mb-0 text-muted"></h5>
            <small class="text-muted">Visualizá tus turnos en el mes seleccionado.</small>
          </div>

          <div class="col-12 col-md-4 d-flex justify-content-md-center mb-2 mb-md-0">
            <div class="btn-group" role="group" aria-label="Navegación de mes">
              <button type="button" id="btnMesAnterior" class="btn btn-outline-secondary btn-sm">
                <ion-icon name="chevron-back-outline"></ion-icon>
              </button>
              <button type="button" id="btnHoy" class="btn btn-outline-secondary btn-sm">
                Hoy
              </button>
              <button type="button" id="btnMesSiguiente" class="btn btn-outline-secondary btn-sm">
                <ion-icon name="chevron-forward-outline"></ion-icon>
              </button>
            </div>
          </div>

          <div class="col-12 col-md-4 d-flex justify-content-md-end">
            <a href="mis_turnos.php" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
              <ion-icon name="list-outline" style="font-size:18px"></ion-icon>
              <span>Ver listado</span>
            </a>
          </div>
        </div>

        <!-- LEYENDA DE COLORES -->
        <div class="mb-2">
          <span class="badge bg-warning text-dark me-1">Asignado</span>
          <span class="badge bg-info text-dark me-1">Confirmado</span>
          <span class="badge bg-success me-1">Finalizado</span>
        </div>

        <!-- CALENDARIO -->
        <div id="calendarioContainer" class="table-responsive">
          <!-- acá se inyecta el calendario por JS -->
        </div>

      </div>
    </div>
  </div>
</div>

<!-- MODAL DETALLE DÍA -->
<div class="modal fade" id="modalDetalleDia" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Turnos del día</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p id="detalleFechaSeleccionada" class="fw-semibold mb-2"></p>
        <div class="table-responsive">
          <table class="table table-sm table-bordered align-middle">
            <thead class="table-light">
              <tr>
                <th style="width: 30%;">Turno</th>
                <th style="width: 20%;">Horario</th>
                <th style="width: 20%;">Estado</th>
              </tr>
            </thead>
            <tbody id="tablaDetalleDiaBody">
              <!-- filas por JS -->
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <small class="text-muted me-auto">
          Para aceptar o finalizar turnos utilizá la pantalla <strong>"Mis turnos"</strong>.
        </small>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<?php
echo '<script src="turnos_calendario.js"></script>';
require_once("foot/foot.php");
?>
