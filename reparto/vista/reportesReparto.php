<?php
$currentPage = 'reportesReparto';
include_once("../../includes/head_app.php");
?>

<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Reportes y Estadísticas de Reparto</h1>
</div>

<div class="contenido-principal">

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="card-title mb-3">Tablero de control del módulo de reparto</h4>
                    <p class="card-text">
                        Desde esta sección podés consultar las <strong>estadísticas operativas</strong> del reparto
                        y generar <strong>reportes detallados</strong> con los pedidos asignados a cada salida,
                        filtrando por fechas, vehículo y estado del reparto.
                    </p>

                    <div class="d-flex flex-wrap gap-2 mt-3">
                        <button type="button"
                                class="btn btn-primary"
                                onclick="window.location.href='estadisticasReparto.php'">
                            <ion-icon name="stats-chart-outline"></ion-icon>
                            <span class="ms-1">Ver estadísticas</span>
                        </button>

                        <button type="button"
                                class="btn btn-secondary"
                                onclick="window.location.href='reportesRepartoListado.php'">
                            <ion-icon name="document-text-outline"></ion-icon>
                            <span class="ms-1">Ver reportes</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php
require_once("foot/foot.php");
?>
