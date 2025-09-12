<?php
include_once("head/headreporte.php");
?>

<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Reportes de Usuarios</h1>
</div>

<div class="contenido-principal">
    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Altas y Bajas (últimos 12 meses)</h5>
                    <div class="chart-box">
                        <canvas id="chartAltasBajas"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Estado actual de los usuarios</h5>
                    <div class="chart-box">
                        <canvas id="chartEstados"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Cantidad de usuarios por rol</h5>
                    <div class="chart-box">
                        <canvas id="chartRoles"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js (desde CDN, sólo en esta vista) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="/Bakersoft/rsc/script/scriptUsuariosReportes.js"></script>


<?php include_once("foot/foot.php"); ?>