<?php
include_once("../../includes/head_app.php");
require_once("../controlador/controladorReportes.php");
$obj = new controladorReportes();
?>
<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Reportes y Estadísticas de Materia Prima y Proveedores</h1>
</div>
<div class="contenido-principal">
    <h3>Estadísticas</h3>
    <div class="contenido">
        <div class="row mb-4">

            <!-- Materias Primas Registradas -->
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm h-100 kpi-card kpi-blue">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted fw-bold">Materias Primas Registradas</small>
                            <ion-icon name="cube-outline" class="kpi-card-icon"></ion-icon>
                        </div>
                        <h3 class="mt-2 mb-0" id="mp_total">0</h3>
                    </div>
                </div>
            </div>

            <!-- Pedidos Realizados (Último Mes) -->
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm h-100 kpi-card kpi-green">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted fw-bold">Pedidos Realizados (Último Mes)</small>
                            <ion-icon name="calendar-outline" class="kpi-card-icon"></ion-icon>
                        </div>
                        <h3 class="mt-2 mb-0" id="pedidos_mes">0</h3>
                    </div>
                </div>
            </div>

            <!-- Materia Prima Sin Stock -->
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm h-100 kpi-card kpi-yellow">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted fw-bold">Materia Prima Sin Stock</small>
                            <ion-icon name="alert-circle-outline" class="kpi-card-icon"></ion-icon>
                        </div>
                        <h3 class="mt-2 mb-0" id="sin_stock">0</h3>
                    </div>
                </div>
            </div>

            <!-- Proveedores Registrados -->
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm h-100 kpi-card kpi-red">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted fw-bold">Proveedores Registrados</small>
                            <ion-icon name="people-outline" class="kpi-card-icon"></ion-icon>
                        </div>
                        <h3 class="mt-2 mb-0" id="proveedores">0</h3>
                    </div>
                </div>
            </div>

        </div>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm p-3">
                    <h5 class="text-center">Estado de Stock de Materias Primas</h5>
                    <canvas id="stockPie"></canvas>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card shadow-sm p-3">
                    <h5 class="text-center">Pedidos de Materia Prima (Último mes)</h5>
                    <canvas id="pedidosLine"></canvas>
                </div>
            </div>
        </div>
        <div class="row my-4">
            <div class="col-12">
                <h3 class="mb-4">📊 Reportes disponibles</h3>
            </div>

            <div class="col-md-6 mb-3 d-flex justify-content-center">
                <a class="btn btn-primary btn-lg px-5" href="reporte_materiasprimas.php" target="_blank">
                    📄 Listado de Materias Primas
                </a>
            </div>

            <div class="col-md-6 mb-3 d-flex justify-content-center">
                <a class="btn btn-success btn-lg px-5" href="reporte_proveedores.php" target="_blank">
                    📄 Listado integral de Proveedores
                </a>
            </div>
        </div>
    </div>
</div>


<?php
require_once("foot/foot.php")
?>