<?php
    require_once("head/head.php");
    require_once("../controlador/controladorReportes.php");
    $obj = new controladorReportes();
    
?>
<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Reportes y Estadísticas</h1>
</div>
<div class="contenido-principal">
    <h3>Estadísticas</h3>
    <div class="contenido">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm p-3 text-center">
                    <h6 class="mb-2 text-secondary">Materias Primas Registradas</h6>
                    <h3 class="fw-bold text-primary" id="mp_total">0</h3>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm p-3 text-center">
                    <h6 class="mb-2 text-secondary">Pedidos Realizados (Último Mes)</h6>
                    <h3 class="fw-bold text-success" id="pedidos_mes">0</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm p-3 text-center">
                    <h6 class="mb-2 text-secondary">Ítems Sin Stock</h6>
                    <h3 class="fw-bold text-warning" id="sin_stock">0</h3>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card shadow-sm p-3 text-center">
                    <h6 class="mb-2 text-secondary">Proveedores Registrados</h6>
                    <h3 class="fw-bold text-danger" id="proveedores">0</h3>
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