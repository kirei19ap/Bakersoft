<?php
require_once("head/head.php");
require_once("../controlador/controladorProductos.php");
$ctrl = new controladorProducto();
?>
<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Estadísticas de Productos</h1>
</div>

<div class="contenido-principal">
    <div class="contenido">
        <div class="container-fluid py-3">

            <!-- Filtros de fecha -->
            <div class="card mb-3">
                <div class="card-body">
                    <form id="formFiltroEstadisticas" class="row g-3 align-items-end">
                        <div class="col-sm-4 col-md-3">
                            <label for="est_fecha_desde" class="form-label">Fecha desde</label>
                            <input type="date" class="form-control" id="est_fecha_desde" name="fecha_desde" required>
                        </div>
                        <div class="col-sm-4 col-md-3">
                            <label for="est_fecha_hasta" class="form-label">Fecha hasta</label>
                            <input type="date" class="form-control" id="est_fecha_hasta" name="fecha_hasta" required>
                        </div>
                        <div class="col-sm-4 col-md-3">
                            <button type="button" class="btn btn-primary mt-4" id="btnAplicarEstadisticas">
                                Aplicar filtro
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Resumen -->
            <div class="row g-3 mb-3" id="resumenEstadisticas">
                <div class="col-md-4">
                    <div class="card text-bg-light">
                        <div class="card-body">
                            <h6 class="card-title mb-2">Total de productos</h6>
                            <p class="fs-3 mb-0" id="est_total">-</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-bg-light">
                        <div class="card-body">
                            <h6 class="card-title mb-2">Activos</h6>
                            <p class="fs-3 mb-0" id="est_activos">-</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-bg-light">
                        <div class="card-body">
                            <h6 class="card-title mb-2">Inactivos</h6>
                            <p class="fs-3 mb-0" id="est_inactivos">-</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gráficos -->
            <div class="row g-3">
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Productos por fecha de alta</h6>
                            <canvas id="chartProductosFecha" height="220"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Productos por categoría</h6>
                            <canvas id="chartProductosCategoria" height="220"></canvas>
                        </div>
                    </div>
                </div>
            </div>

        </div> <!-- /container -->
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ===== ESTADÍSTICAS =====
    if (document.getElementById('formFiltroEstadisticas')) {
        const btn = document.getElementById('btnAplicarEstadisticas');
        const fDesde = document.getElementById('est_fecha_desde');
        const fHasta = document.getElementById('est_fecha_hasta');

        // Opcional: valores por defecto (últimos 30 días)
        const hoy = new Date();
        const hace30 = new Date();
        hace30.setDate(hoy.getDate() - 30);
        fHasta.value = hoy.toISOString().substring(0, 10);
        fDesde.value = hace30.toISOString().substring(0, 10);

        let chartFecha = null;
        let chartCategoria = null;

        btn.addEventListener('click', function () {
            const fd = fDesde.value;
            const fh = fHasta.value;
            if (!fd || !fh || fd > fh) {
                alert('Seleccioná un rango de fechas válido.');
                return;
            }

            fetch('estadisticas_datos.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ fecha_desde: fd, fecha_hasta: fh })
            })
                .then(r => r.json())
                .then(json => {
                    console.log('JSON estadisticas:', json); // 👈 DEBUG

                    if (!json.ok) {
                        alert(json.error || 'Error al obtener estadísticas');
                        return;
                    }

                    const data = json.data || {};

                    // Resumen
                    const resumen = data.resumen || {};
                    document.getElementById('est_total').textContent = resumen.total ?? 0;
                    document.getElementById('est_activos').textContent = resumen.activos ?? 0;
                    document.getElementById('est_inactivos').textContent = resumen.inactivos ?? 0;

                    // Gráfico por fecha
                    const porFecha = Array.isArray(data.por_fecha) ? data.por_fecha : [];
                    const labelsFecha = porFecha.map(r => r.fecha);
                    const valoresFecha = porFecha.map(r => Number(r.cantidad || 0));

                    const ctxFecha = document.getElementById('chartProductosFecha').getContext('2d');
                    if (chartFecha) chartFecha.destroy();
                    chartFecha = new Chart(ctxFecha, {
                        type: 'bar',
                        data: {
                            labels: labelsFecha,
                            datasets: [{
                                label: 'Productos dados de alta',
                                data: valoresFecha
                            }]
                        }
                    });

                    // Gráfico por categoría
                    const porCat = Array.isArray(data.por_categoria) ? data.por_categoria : [];
                    const labelsCat = porCat.map(r => r.categoria);
                    const valoresCat = porCat.map(r => Number(r.cantidad || 0));

                    const ctxCat = document.getElementById('chartProductosCategoria').getContext('2d');
                    if (chartCategoria) chartCategoria.destroy();
                    chartCategoria = new Chart(ctxCat, {
                        type: 'doughnut',
                        data: {
                            labels: labelsCat,
                            datasets: [{
                                data: valoresCat
                            }]
                        }
                    });
                })
                .catch(err => {
                    console.error('Error fetch estadisticas:', err);
                    alert('Error al obtener estadísticas.');
                });
        });


        // Carga inicial
        btn.click();
    }
});
    </script>
<?php
require_once("foot/foot_stat.php");
?>
