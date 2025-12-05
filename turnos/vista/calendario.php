<?php
// turnos/vista/calendario.php
session_start();

$currentPage = 'turnos'; // dejamos activo el menú Turnos Laborales

include_once("../../includes/head_app.php");
?>

<div class="titulo-contenido shadow-sm">
  <h1 class="display-5">Calendario de turnos de producción</h1>
</div>

<div class="contenido-principal">
  <div class="contenido">
    <div class="card">
      <div class="card-body">

        <!-- CABECERA: TÍTULO MES + BOTONES + LINK A PLANIFICACIÓN -->
        <div class="row align-items-center mb-3">
          <div class="col-12 col-md-4 mb-2 mb-md-0">
            <h5 id="tituloMesProd" class="mb-0 text-muted"></h5>
            <small class="text-muted">
              Visualizá la cobertura de turnos por día.
            </small>
          </div>

          <div class="col-12 col-md-4 d-flex justify-content-md-center mb-2 mb-md-0">
            <div class="btn-group" role="group" aria-label="Navegación de mes">
              <button type="button" id="btnMesAnteriorProd" class="btn btn-outline-secondary btn-sm">
                <ion-icon name="chevron-back-outline"></ion-icon>
              </button>
              <button type="button" id="btnHoyProd" class="btn btn-outline-secondary btn-sm">
                Hoy
              </button>
              <button type="button" id="btnMesSiguienteProd" class="btn btn-outline-secondary btn-sm">
                <ion-icon name="chevron-forward-outline"></ion-icon>
              </button>
            </div>
          </div>

          <div class="col-12 col-md-4 d-flex justify-content-md-end">
            <a href="index.php" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
              <ion-icon name="construct-outline" style="font-size:18px"></ion-icon>
              <span>Ir a asignaciones</span>
            </a>
          </div>
        </div>

        <!-- LEYENDA -->
        <div class="mb-2">
          <span class="badge bg-success me-1">Turno cubierto</span>
          <span class="badge bg-danger me-1">Turno sin cobertura</span>
        </div>

        <!-- CALENDARIO -->
        <div id="calendarioProdContainer" class="table-responsive">
          <!-- Se inyecta por JS -->
        </div>

      </div>
    </div>
  </div>
</div>

<!-- MODAL DETALLE DIA -->
<div class="modal fade" id="modalDetalleDiaProd" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalle de cobertura</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p id="detalleFechaProd" class="fw-semibold mb-2"></p>
        <div class="table-responsive">
          <table class="table table-sm table-bordered align-middle">
            <thead class="table-light">
              <tr>
                <th style="width: 30%;">Turno</th>
                <th style="width: 20%;">Horario</th>
                <th style="width: 20%;">Cantidad asignada</th>
                <th style="width: 30%;">Estado de cobertura</th>
              </tr>
            </thead>
            <tbody id="tablaDetalleDiaProdBody">
              <!-- filas por JS -->
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <small class="text-muted me-auto">
          Para asignar o reasignar turnos usá la pantalla <strong>"Turnos laborales"</strong>.
        </small>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<?php
echo '<script src="calendario_turnos_admin.js"></script>';
require_once("foot/foot.php");
?>
