<?php
require_once("head/head.php");
require_once("../controlador/controladorProductos.php");
$ctrl = new controladorProducto();
?>
<style>
    /* Contenedor de la tabla de reportes: que nunca genere scroll horizontal */
    #wrapReporteProductos {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
        /* cortamos cualquier desborde interno */
    }

    /* Wrapper que genera DataTables para esta tabla */
    #tablaReporteProductos_wrapper {
        width: 100% !important;
        max-width: 100% !important;
        overflow-x: hidden !important;
    }

    /* La tabla en sí misma se adapta al ancho disponible */
    #tablaReporteProductos {
        width: 100% !important;
        table-layout: fixed;
    }

    #tablaReporteProductos th,
    #tablaReporteProductos td {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: middle;
        padding: 8px 10px;
    }

    /* Distribución de columnas (sumando 100%) */
    #tablaReporteProductos th:nth-child(1),
    #tablaReporteProductos td:nth-child(1) {
        width: 6%;
        text-align: center;
    }

    /* # */
    #tablaReporteProductos th:nth-child(2),
    #tablaReporteProductos td:nth-child(2) {
        width: 32%;
        text-align: left;
    }

    /* Nombre */
    #tablaReporteProductos th:nth-child(3),
    #tablaReporteProductos td:nth-child(3) {
        width: 22%;
        text-align: left;
    }

    /* Categoría */
    #tablaReporteProductos th:nth-child(4),
    #tablaReporteProductos td:nth-child(4) {
        width: 10%;
        text-align: center;
    }

    /* Unidad */
    #tablaReporteProductos th:nth-child(5),
    #tablaReporteProductos td:nth-child(5) {
        width: 10%;
        text-align: center;
    }

    /* Estado */
    #tablaReporteProductos th:nth-child(6),
    #tablaReporteProductos td:nth-child(6) {
        width: 20%;
        text-align: center;
    }

    /* Fecha alta */
</style>



<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Reportes de Productos</h1>
</div>
<div>
    <div class="contenido-principal">
        <div class="contenido">
            <div class="container-fluid py-3">

                <!-- Filtros -->
                <div class="card mb-3">
                    <div class="card-body">
                        <form id="formFiltroReportes" class="row g-3 align-items-end" method="post" action="reporte_pdf.php">

                            <div class="col-12 col-md-4">
                                <label class="form-label d-block">Alcance</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="rep_modo" id="rep_modo_todos" value="todos" checked>
                                    <label class="form-check-label" for="rep_modo_todos">Todos los productos</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="rep_modo" id="rep_modo_rango" value="rango">
                                    <label class="form-check-label" for="rep_modo_rango">Filtrar por fecha de alta</label>
                                </div>
                            </div>

                            <div class="col-6 col-md-3">
                                <label for="rep_fecha_desde" class="form-label">Fecha desde</label>
                                <input type="date" class="form-control" id="rep_fecha_desde" name="fecha_desde" disabled>
                            </div>
                            <div class="col-6 col-md-3">
                                <label for="rep_fecha_hasta" class="form-label">Fecha hasta</label>
                                <input type="date" class="form-control" id="rep_fecha_hasta" name="fecha_hasta" disabled>
                            </div>

                            <div class="col-12 col-md-2 d-flex flex-column gap-2">
                                <button type="button" class="btn btn-primary mt-md-4 w-100" id="btnAplicarReporte">
                                    Generar
                                </button>
                                <button type="submit" class="btn btn-outline-secondary w-100">
                                    Exportar PDF
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tabla de reporte -->
                <div class="">
                    <div class="card-body">
                        <div class="wrapReporteProductos table-responsive">
                            <table class="table table-striped table-sm table-hover align-middle" id="tablaReporteProductos">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Nombre</th>
                                        <th>Categoría</th>
                                        <th>Unidad</th>
                                        <th>Estado</th>
                                        <th>Fecha alta</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // ===== REPORTES =====
        if (document.getElementById('tablaReporteProductos')) {
            const modoTodos = document.getElementById('rep_modo_todos');
            const modoRango = document.getElementById('rep_modo_rango');
            const fDesde = document.getElementById('rep_fecha_desde');
            const fHasta = document.getElementById('rep_fecha_hasta');
            const btnRep = document.getElementById('btnAplicarReporte');

            function actualizarHabilitadoFechas() {
                const esRango = modoRango.checked;
                fDesde.disabled = !esRango;
                fHasta.disabled = !esRango;
                if (!esRango) {
                    fDesde.value = '';
                    fHasta.value = '';
                }
            }

            modoTodos.addEventListener('change', actualizarHabilitadoFechas);
            modoRango.addEventListener('change', actualizarHabilitadoFechas);
            actualizarHabilitadoFechas();

            const tablaRep = $('#tablaReporteProductos').DataTable({
                columnDefs: [{
                    targets: 0,
                    visible: false,
                    searchable: false,
                    className: 'd-none'
                }, ],
                language: {
                    decimal: ",",
                    thousands: ".",
                    info: "Mostrando _END_ registros de un total de _TOTAL_",
                    infoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
                    loadingRecords: "Cargando...",
                    lengthMenu: "Mostrar _MENU_ registros",
                    paginate: {
                        first: "<<",
                        last: ">>",
                        next: ">",
                        previous: "<"
                    },
                    search: "Buscar:",
                    searchPlaceholder: "Buscar...",
                    emptyTable: "No hay registros para mostrar en la tabla",
                    sInfoFiltered: "(filtrado de un total de _MAX_ registros)",
                },
                processing: true,
                serverSide: false,
                ajax: function(data, callback) {
                    const modo = modoRango.checked ? 'rango' : 'todos';
                    const fd = fDesde.value;
                    const fh = fHasta.value;

                    const params = new URLSearchParams({
                        modo
                    });
                    if (modo === 'rango') {
                        if (!fd || !fh || fd > fh) {
                            alert('Seleccioná un rango de fechas válido.');
                            callback({
                                data: []
                            });
                            return;
                        }
                        params.append('fecha_desde', fd);
                        params.append('fecha_hasta', fh);
                    }

                    fetch('reporte_datos.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: params.toString()
                        })
                        .then(r => r.json())
                        .then(json => {
                            if (!json.ok) {
                                alert(json.error || 'Error al obtener reporte');
                                callback({
                                    data: []
                                });
                                return;
                            }

                            const filas = json.data.map((row, idx) => [
                                idx + 1,
                                row.nombre,
                                row.categoria,
                                row.unidad_medida,
                                row.estado,
                                row.fecha_alta
                            ]);

                            callback({
                                data: filas
                            });
                        })
                        .catch(err => {
                            console.error(err);
                            alert('Error al obtener reporte.');
                            callback({
                                data: []
                            });
                        });
                }
            });

            btnRep.addEventListener('click', function() {
                tablaRep.ajax.reload();
            });

            // Carga inicial
            tablaRep.ajax.reload();
        }
    });
</script>

<?php
require_once("foot/foot_stat.php");
?>