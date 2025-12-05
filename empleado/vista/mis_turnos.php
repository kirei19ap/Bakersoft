<?php
// empleado/vista/mis_turnos.php

#session_start();
$currentPage = ''; // dentro del portal empleado, no usamos el menú lateral de roles aquí

// Ajustá el include según uses head_app o un head específico del portal
include_once("head/head_turnos.php");

?>

<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Turnos laborales</h1>
</div>

<div class="contenido-principal">
    <div class="contenido">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">

                    <h3 class="mb-0 text-muted">Mis turnos asignados</h3>

                    <div class="d-flex align-items-center gap-3">

                        <!-- CAMPANITA DE NOTIFICACIONES -->
                        <div class="position-relative"
                            id="notificacionTurnosPendientes"
                            style="cursor:pointer;"
                            title="Sin turnos pendientes">

                            <!-- Ícono campana -->
                            <ion-icon id="iconoCampana"
                                name="notifications-outline"
                                style="font-size:26px; color:#6c757d;">
                            </ion-icon>

                            <!-- Badge de cantidad (oculto por defecto) -->
                            <span id="badgePendientes"
                                class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                style="display:none;">
                                0
                            </span>
                        </div>

                        <!-- BOTÓN CALENDARIO -->
                        <a href="turnos_calendario.php"
                            class="btn btn-primary d-flex align-items-center gap-1">
                            <ion-icon name="calendar-outline" style="font-size:18px"></ion-icon>
                            <span>Vista calendario</span>
                        </a>

                    </div>

                </div>

            </div>
        </div>

        <div class="card">
            <div class="card-body">

                <!-- PANEL DE MÉTRICAS -->
                <!-- PANEL DE MÉTRICAS -->
                <div id="panelMetricasTurnos" class="row mb-3">

                    <!-- Turnos en el período -->
                    <div class="col-6 col-md-3 mb-2">
                        <div class="card text-center shadow-sm">
                            <div class="card-body py-2">
                                <h6 class="mb-1 text-muted">Turnos en el período</h6>
                                <span id="totalTurnosValor" class="fs-4 fw-bold">0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Asignados -->
                    <div class="col-6 col-md-3 mb-2">
                        <div class="card text-center shadow-sm">
                            <div class="card-body py-2">
                                <h6 class="mb-1 text-muted">Asignados</h6>
                                <span id="asignadosValor" class="badge bg-warning text-dark fs-6">0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Confirmados -->
                    <div class="col-6 col-md-3 mb-2">
                        <div class="card text-center shadow-sm">
                            <div class="card-body py-2">
                                <h6 class="mb-1 text-muted">Confirmados</h6>
                                <span id="confirmadosValor" class="badge bg-info text-dark fs-6">0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Próximo turno -->
                    <div class="col-6 col-md-3 mb-2">
                        <div class="card text-center shadow-sm">
                            <div class="card-body py-2">
                                <h6 class="mb-1 text-muted">Próximo turno</h6>
                                <span id="proximoTurnoValor" class="fw-semibold">Sin turnos próximos</span>
                            </div>
                        </div>
                    </div>

                </div>


                <!-- FILTROS -->
                <form id="formFiltrosTurnosEmpleado" class="row gy-2 gx-2 mb-3 align-items-end">

                    <!-- Fecha desde -->
                    <div class="col-12 col-md-2">
                        <label for="fechaDesde" class="form-label">Fecha desde</label>
                        <input type="date" id="fechaDesde" name="fechaDesde" class="form-control">
                    </div>

                    <!-- Fecha hasta -->
                    <div class="col-12 col-md-2">
                        <label for="fechaHasta" class="form-label">Fecha hasta</label>
                        <input type="date" id="fechaHasta" name="fechaHasta" class="form-control">
                    </div>

                    <!-- Estado turno -->
                    <div class="col-12 col-md-2">
                        <label for="estado" class="form-label">Estado turno</label>
                        <select id="estado" name="estado" class="form-select">
                            <option value="">-- Todos --</option>
                            <option value="Asignado">Asignado</option>
                            <option value="Confirmado">Confirmado</option>
                            <option value="Finalizado">Finalizado</option>
                        </select>
                    </div>

                    <!-- Botones -->
                    <div class="col-12 col-md-6">
                        <div class="d-flex flex-wrap justify-content-md-end gap-2 mt-1 mt-md-0">

                            <button type="button" id="btnBuscarTurnos"
                                class="btn btn-secondary d-flex align-items-center gap-1 px-3">
                                <ion-icon name="search-outline" style="font-size:18px"></ion-icon>
                                <span>Buscar</span>
                            </button>

                            <button type="button" id="btnAceptarSeleccionados"
                                class="btn btn-success d-flex align-items-center gap-1 px-3">
                                <ion-icon name="checkmark-done-outline" style="font-size:18px"></ion-icon>
                                <span>Aceptar seleccionados</span>
                            </button>

                            <button type="button" id="btnFinalizarSeleccionados"
                                class="btn btn-primary d-flex align-items-center gap-1 px-3">
                                <ion-icon name="checkmark-circle-outline" style="font-size:18px"></ion-icon>
                                <span>Finalizar seleccionados</span>
                            </button>

                        </div>
                    </div>

                </form>



                <!-- TABLA -->
                <div class="table-responsive mt-3">
                    <table id="tablaMisTurnos" class="table table-striped table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width:5%;"><input type="checkbox" id="chkSeleccionarTodos"></th>
                                <th style="width:15%;">Fecha</th>
                                <th style="width:30%;">Turno</th>
                                <th style="width:15%;">Estado</th>
                                <th style="width:20%;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    Seleccioná un rango de fechas y presioná "Buscar" para ver tus turnos.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- MODAL SOLICITAR CAMBIO DE TURNO -->
<div class="modal fade" id="modalSolicitudCambioTurno" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-md modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Solicitar cambio de turno</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p class="mb-2">
          <strong>Turno seleccionado:</strong><br>
          <span id="infoTurnoSeleccionado" class="text-muted"></span>
        </p>

        <div class="mb-3">
          <label class="form-label d-block">Tipo de solicitud</label>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="tipoSolicitud" id="tipoRechazo" value="Rechazo" checked>
            <label class="form-check-label" for="tipoRechazo">No puedo asistir</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" name="tipoSolicitud" id="tipoCambio" value="Cambio">
            <label class="form-check-label" for="tipoCambio">Solicitar cambio</label>
          </div>
        </div>

        <div class="mb-3">
          <label for="motivoSolicitud" class="form-label">Motivo</label>
          <textarea id="motivoSolicitud" class="form-control" rows="3"
                    placeholder="Indicá brevemente el motivo de la solicitud" maxlength="255"></textarea>
        </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnConfirmarSolicitudCambio">
          Enviar solicitud
        </button>
      </div>
    </div>
  </div>
</div>

<script src="turnos_notificaciones.js"></script>
<?php

require_once("foot/foot.php");
?>