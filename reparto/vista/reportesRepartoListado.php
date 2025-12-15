    <?php
    $currentPage = 'reportesReparto';

    include_once("../../includes/head_app.php");
    require_once("../controlador/controladorReparto.php");
    require_once("../controlador/controladorVehiculo.php");

    // Controladores
    $ctrlReparto  = new ControladorReparto();
    $ctrlVehiculo = new ControladorVehiculo();

    // Filtros
    $fechaDesde = $_GET['fd'] ?? date('Y-m-01');
    $fechaHasta = $_GET['fh'] ?? date('Y-m-d');
    $vehiculo   = $_GET['veh'] ?? '';
    $estado     = $_GET['est'] ?? 'Todos';

    // Datos
    $kpis    = $ctrlReparto->obtenerKPIsReparto($fechaDesde, $fechaHasta, $vehiculo ?: null, $estado);
    $detalle = $ctrlReparto->obtenerDetalleRepartos($fechaDesde, $fechaHasta, $vehiculo ?: null, $estado);

    // Vehículos
    $vehiculos = $ctrlVehiculo->listarVehiculos(true);

    // Helper badge estado
    function badgeEstadoReparto($estado)
    {
        switch ($estado) {
            case 'Planificado':
                $class = 'bg-secondary';
                break;
            case 'En Curso':
                $class = 'bg-info';
                break;
            case 'Finalizado':
                $class = 'bg-success';
                break;
            case 'Cancelado':
                $class = 'bg-danger';
                break;
            default:
                $class = 'bg-light text-dark';
                break;
        }
        return "<span class='badge {$class}'>" . htmlspecialchars($estado) . "</span>";
    }

    // KPIs derivados
    $totalRepartos = $kpis['totalRepartos'] ?? 0;
    $totalPedidos  = $kpis['totalPedidos'] ?? 0;
    $entregados    = $kpis['entregados'] ?? 0;
    $cancelados    = $kpis['cancelados'] ?? 0;

    $porcEntregados = ($totalPedidos > 0)
        ? round(($entregados / $totalPedidos) * 100, 1)
        : 0;

    $porcCancelados = ($totalRepartos > 0)
        ? round(($cancelados / $totalRepartos) * 100, 1)
        : 0;

    $promPedidosPorReparto = ($totalRepartos > 0)
        ? round(($totalPedidos / $totalRepartos), 1)
        : 0;
    ?>

    <div class="titulo-contenido shadow-sm">
        <h1 class="display-5">Reportes de Reparto</h1>
    </div>

    <div class="contenido-principal">

        <!-- FILTROS -->
        <form method="get" class="row mb-4">

            <div class="col-md-3">
                <label class="form-label">Fecha Desde</label>
                <input type="date" name="fd" class="form-control"
                    value="<?php echo htmlspecialchars($fechaDesde); ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label">Fecha Hasta</label>
                <input type="date" name="fh" class="form-control"
                    value="<?php echo htmlspecialchars($fechaHasta); ?>">
            </div>

            <div class="col-md-3">
                <label class="form-label">Vehículo</label>
                <select name="veh" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach ($vehiculos as $v): ?>
                        <?php
                        $sel = ((string)$vehiculo === (string)$v['idVehiculo']) ? 'selected' : '';
                        $textoVeh = $v['patente'] . ' - ' . ($v['descripcion'] ?? '');
                        ?>
                        <option value="<?php echo $v['idVehiculo']; ?>" <?php echo $sel; ?>>
                            <?php echo htmlspecialchars(trim($textoVeh)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <select name="est" class="form-select">
                    <?php
                    $estadosPosibles = ['Todos', 'Planificado', 'En Curso', 'Finalizado', 'Cancelado'];
                    foreach ($estadosPosibles as $est) {
                        $sel = ($estado === $est) ? 'selected' : '';
                        echo "<option value=\"{$est}\" {$sel}>{$est}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-12 mt-3 d-flex justify-content-end">
                <button class="btn btn-primary">
                    <ion-icon name="search-outline"></ion-icon> Buscar
                </button>

                <?php
                $queryPdf = http_build_query([
                    'fd'  => $fechaDesde,
                    'fh'  => $fechaHasta,
                    'veh' => $vehiculo,
                    'est' => $estado,
                ]);
                ?>
                <a href="reporteRepartosPDF.php?<?php echo $queryPdf; ?>"
                    class="btn btn-danger ms-2" target="_blank">
                    <ion-icon name="document-outline"></ion-icon> Exportar PDF
                </a>

            </div>
        </form>

        <!-- KPIs resumidos arriba del reporte -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted">Repartos en el período</small>
                        <h3 class="mb-0"><?php echo $totalRepartos; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted">Pedidos asignados</small>
                        <h3 class="mb-0"><?php echo $totalPedidos; ?></h3>
                        <small class="text-muted">
                            Promedio por reparto: <?php echo $promPedidosPorReparto; ?>
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted">Pedidos entregados</small>
                        <h3 class="mb-0"><?php echo $entregados; ?></h3>
                        <small class="text-muted">
                            <?php echo $porcEntregados; ?>% del total asignado
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card shadow-sm h-100">
                    <div class="card-body">
                        <small class="text-muted">Repartos cancelados</small>
                        <h3 class="mb-0"><?php echo $cancelados; ?></h3>
                        <small class="text-muted">
                            <?php echo $porcCancelados; ?>% de los repartos
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLA DETALLE -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Detalle de repartos</h5>

                <?php if (empty($detalle)): ?>
                    <div class="alert alert-info mb-0">
                        No se encontraron repartos para los filtros seleccionados.
                    </div>
                <?php else: ?>
                    <div class="">
                        <table id="tablaReporteReparto" class="table table-striped table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Vehículo</th>
                                    <th>Chofer</th>
                                    <th>Pedidos asignados</th>
                                    <th>Entregados</th>
                                    <th>% Cumplimiento</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($detalle as $d): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars(date("d/m/Y", strtotime($d['fechaReparto']))); ?></td>
                                        <td>
                                            <?php
                                            $vehTxt = $d['patente'] . ' - ' . ($d['vehiculoDescripcion'] ?? '');
                                            echo htmlspecialchars(trim($vehTxt));
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($d['chofer']); ?></td>
                                        <td><?php echo (int)$d['totalPedidos']; ?></td>
                                        <td><?php echo (int)$d['entregados']; ?></td>
                                        <td>
                                            <?php
                                            $porc = ($d['totalPedidos'] > 0)
                                                ? round(($d['entregados'] / $d['totalPedidos']) * 100, 1)
                                                : 0;
                                            echo $porc . '%';
                                            ?>
                                        </td>
                                        <td><?php echo badgeEstadoReparto($d['estado']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.DataTable) {
                new DataTable('#tablaReporteReparto', {
                    responsive: true,
                    language: {
                        "decimal": ",",
                        "thousands": ".",
                        "info": "Mostrando _END_ registros de un total de _TOTAL_",
                        "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                        "infoFiltered": "(filtrado de un total de _MAX_ registros)",
                        "loadingRecords": "Cargando...",
                        "lengthMenu": "Mostrar _MENU_",
                        "paginate": {
                            "first": "<<",
                            "last": ">>",
                            "next": ">",
                            "previous": "<"
                        },
                        "search": "Buscador:",
                        "searchPlaceholder": "Buscar...",
                        "emptyTable": "No hay repartos en el período seleccionado.",
                    },
                    pageLength: 10,
                    scrollX: false
                });
            }
        });
    </script>

    <?php
    require_once("foot/foot.php");
    ?>