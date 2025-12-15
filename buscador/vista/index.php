<?php
$currentPage = 'buscadorMP';
include_once("../../includes/head_app.php");
require_once("../controlador/controladorBuscador.php");

$ctrl = new controladorBuscador();

// Tipo de pedido: 'mp' (materia prima) o 'clientes' (pedidos de clientes)
$tipoPedido = $_GET['tipoPedido'] ?? 'mp';

// Datos base
$proveedores    = $ctrl->proveedoresTodos();
$materiasPrimas = $ctrl->mpTodas();
$estados        = $ctrl->obtenerEstados();
$clientes       = $ctrl->clientesTodos();   // nuevo: lista de clientes
$estadosClientes = [];

if (!empty($estados)) {
    foreach ($estados as $est) {
        $cod = (int)$est['codEstado'];
        if ($cod >= 60) {
            // Por ejemplo: 60 Cancelado, 70 Generado, 80 Confirmado, 90 Preparado, 100 Entregado
            $estadosClientes[] = $est;
        } else {
            // Estados usados para pedidos de materia prima
            $estadosMP[] = $est;
        }
    }
}

// Filtros comunes
$fecha_desde = $_GET['fecha_desde'] ?? '';
$fecha_hasta = $_GET['fecha_hasta'] ?? '';
$estadoSel   = $_GET['estado'] ?? '';

$pedidos = [];
$seBusco = false;

if ($tipoPedido === 'mp') {
    // Filtros específicos de pedidos de materia prima
    $proveedorId = $_GET['proveedor'] ?? '';
    $materiaId   = $_GET['materia'] ?? '';

    if ($ctrl->hayFiltroValido($fecha_desde, $fecha_hasta, $proveedorId, $materiaId, $estadoSel)) {
        $pedidos = $ctrl->buscar($fecha_desde, $fecha_hasta, $proveedorId, $materiaId, $estadoSel);
        $seBusco = true;
    }
} else {
    // Pedidos de clientes
    $clienteId = $_GET['cliente'] ?? '';

    if ($ctrl->hayFiltroValidoClientes($fecha_desde, $fecha_hasta, $clienteId, $estadoSel)) {
        $pedidos = $ctrl->buscarPedidosClientes($fecha_desde, $fecha_hasta, $clienteId, $estadoSel);
        $seBusco = true;
    }
}
?>
<style>
    /* Ajuste suave sólo para el buscador */
    .form-busqueda .form-select,
    .form-busqueda .form-control {
        border-radius: 0.375rem;
        /* valor estándar Bootstrap */
    }

    /* Opcional: compactar un poquito la separación vertical */
    .form-busqueda .form-group {
        margin-bottom: 0.5rem;
    }
</style>
<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Buscador</h1>
</div>

<div class="contenido-principal">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h3 class="mb-0 text-muted">Buscador parametrizado para pedidos de materia prima y clientes.</h3>
            <small class="text-muted">
                Seleccione de los parametros disponibles para utilizarlo. Debe completar al menos un campo para realizar la búsqueda.
            </small>
        </div>
        <div>
        </div>
    </div>

    <form method="GET" action="" class="form-busqueda mb-4">

        <!-- FILA 1: Tipo de pedido + Fechas -->
        <div class="row">
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label for="tipoPedido" class="form-label">Tipo de pedido:</label>
                    <select name="tipoPedido" id="tipoPedido" class="form-select">
                        <option value="mp" <?= $tipoPedido === 'mp' ? 'selected' : '' ?>>
                            Materia prima
                        </option>
                        <option value="clientes" <?= $tipoPedido === 'clientes' ? 'selected' : '' ?>>
                            Pedidos de clientes
                        </option>
                    </select>
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label for="fecha_desde" class="form-label">Fecha desde:</label>
                    <input type="date"
                        name="fecha_desde"
                        id="fecha_desde"
                        class="form-control"
                        value="<?= htmlspecialchars($fecha_desde) ?>">
                </div>
            </div>

            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label for="fecha_hasta" class="form-label">Fecha hasta:</label>
                    <input type="date"
                        name="fecha_hasta"
                        id="fecha_hasta"
                        class="form-control"
                        value="<?= htmlspecialchars($fecha_hasta) ?>">
                </div>
            </div>
        </div>

        <!-- FILA 2A: Filtros específicos de MP -->
        <div id="filtrosMP" class="<?= $tipoPedido === 'mp' ? '' : 'd-none' ?>">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="proveedor" class="form-label">Proveedor:</label>
                        <select name="proveedor" id="proveedor" class="form-select">
                            <option value="">-- Todos --</option>
                            <?php if (!empty($proveedores)): ?>
                                <?php foreach ($proveedores as $prov): ?>
                                    <option value="<?= $prov['id_proveedor'] ?>"
                                        <?= (isset($_GET['proveedor']) && $_GET['proveedor'] == $prov['id_proveedor']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($prov['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="materia" class="form-label">Materia prima:</label>
                        <select name="materia" id="materia" class="form-select">
                            <option value="">-- Todas --</option>
                            <?php if (!empty($materiasPrimas)): ?>
                                <?php foreach ($materiasPrimas as $mp): ?>
                                    <option value="<?= $mp['id'] ?>"
                                        <?= (isset($_GET['materia']) && $_GET['materia'] == $mp['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($mp['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="estadoMP" class="form-label">Estado del pedido (MP):</label>
                        <select
                            id="estadoMP"
                            class="form-select"
                            <?= $tipoPedido === 'mp' ? 'name="estado"' : 'name="estadoMP" disabled' ?>>
                            <option value="">-- Todos --</option>
                            <?php if (!empty($estadosMP)): ?>
                                <?php foreach ($estadosMP as $est): ?>
                                    <option value="<?= $est['codEstado'] ?>"
                                        <?= ($estadoSel !== '' && $estadoSel == $est['codEstado'] && $tipoPedido === 'mp') ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($est['descEstado']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILA 2B: Filtros específicos de pedidos de clientes -->
        <div id="filtrosClientes" class="<?= $tipoPedido === 'clientes' ? '' : 'd-none' ?>">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label for="cliente" class="form-label">Cliente:</label>
                        <select name="cliente" id="cliente" class="form-select">
                            <option value="">-- Todos --</option>
                            <?php if (!empty($clientes)): ?>
                                <?php foreach ($clientes as $cli): ?>
                                    <option value="<?= $cli['id_cliente'] ?>"
                                        <?= (isset($_GET['cliente']) && $_GET['cliente'] == $cli['id_cliente']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cli['nombre']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label for="estadoClientes" class="form-label">Estado del pedido (clientes):</label>
                        <select
                            id="estadoClientes"
                            class="form-select"
                            <?= $tipoPedido === 'clientes' ? 'name="estado"' : 'name="estadoClientes" disabled' ?>>
                            <option value="">-- Todos --</option>
                            <?php if (!empty($estadosClientes)): ?>
                                <?php foreach ($estadosClientes as $est): ?>
                                    <option value="<?= $est['codEstado'] ?>"
                                        <?= ($estadoSel !== '' && $estadoSel == $est['codEstado'] && $tipoPedido === 'clientes') ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($est['descEstado']) ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- FILA 3: Botón a la derecha -->
        <div class="row">
            <div class="col-12 text-end">
                <button type="submit" class="btn btn-primary mt-2">
                    <ion-icon name="search-outline"></ion-icon>
                    <span>Buscar</span>
                </button>
            </div>
        </div>
    </form>




    <!-- RESULTADOS -->
    <?php if (!empty($pedidos)): ?>

        <?php if ($tipoPedido === 'mp'): ?>
            <!-- Tabla de pedidos de materia prima -->
            <div class="card">
                <div class="card-body">
                    <div class="tabla-empleados">
                        <table id="MP-lista" class="shadow-sm table table-striped table-hover table-bordered">
                            <thead class="thead-dark">
                                <tr class="text-center">
                                    <th>N° Pedido</th>
                                    <th>Fecha</th>
                                    <th>Proveedor</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pedidos as $pedido): ?>
                                    <tr>
                                        <td><?= (int)$pedido['idPedido'] ?></td>
                                        <td><?= htmlspecialchars($pedido['fechaPedido']) ?></td>
                                        <td><?= htmlspecialchars($pedido['proveedor_nombre']) ?></td>
                                        <td><?= htmlspecialchars($pedido['estado']) ?></td>
                                        <td class="text-center">
                                            <a class="btn btn-success verMPbtn"
                                                title="Consultar pedido de materia prima"
                                                href="verPedido.php?id=<?= (int)$pedido['idPedido'] ?>">
                                                <ion-icon name="eye-outline"></ion-icon>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Tabla de pedidos de clientes -->
            <div class="card">
                <div class="card-body">
                    <div class="tabla-empleados">
                        <table id="PedidosClientes-lista" class="shadow-sm table table-striped table-hover table-bordered">
                            <thead class="thead-dark">
                                <tr class="text-center">
                                    <th>N° Pedido</th>
                                    <th>Fecha</th>
                                    <th>Cliente</th>
                                    <th>Estado</th>
                                    <th>Total</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pedidos as $pedido): ?>
                                    <tr>
                                        <td class="text-center"><?= (int)$pedido['idPedidoVenta'] ?></td>
                                        <td class="text-center"><?php $fechaRaw = $pedido['fechaPedido'] ?? null;
                                            $fechaFormateada = '';

                                            if ($fechaRaw) {
                                                $dt = DateTime::createFromFormat('Y-m-d H:i:s', $fechaRaw);
                                                if ($dt) {
                                                    $fechaFormateada = $dt->format('d-m-Y');
                                                } else {
                                                    // fallback por si viene en otro formato
                                                    $ts = strtotime($fechaRaw);
                                                    $fechaFormateada = $ts ? date('d-m-Y', $ts) : $fechaRaw;
                                                }
                                            }

                                            echo htmlspecialchars($fechaFormateada);
                                            ?>


                                        </td>
                                        <td><?= htmlspecialchars($pedido['cliente'] ?? '') ?></td>
                                        <td class="text-center"><?= htmlspecialchars($pedido['descEstado'] ?? '') ?></td>
                                        <td class="text-end">
                                            $ <?= number_format($pedido['total'] ?? 0, 2, ',', '.') ?>
                                        </td>
                                        <td class="text-center">
                                            <a class="btn btn-info"
                                                title="Ver pedido de cliente"
                                                href="/bakersoft/pedidos/vista/ver.php?id=<?= (int)$pedido['idPedidoVenta'] ?>">
                                                <ion-icon name="eye-outline"></ion-icon>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    <?php elseif ($seBusco): ?>
        <p>No se encontraron pedidos con esos criterios.</p>
    <?php endif; ?>

</div>

<?php
require_once("foot/foot.php");
?>

<!-- Script chiquito para mostrar/ocultar filtros según el tipo de pedido -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectTipo = document.getElementById('tipoPedido');
        const filtrosMP = document.getElementById('filtrosMP');
        const filtrosClientes = document.getElementById('filtrosClientes');
        const estadoMP = document.getElementById('estadoMP');
        const estadoClientes = document.getElementById('estadoClientes');

        if (!selectTipo) return;

        function actualizarFiltros() {
            if (selectTipo.value === 'mp') {
                // Mostrar filtros de MP
                filtrosMP.classList.remove('d-none');
                filtrosClientes.classList.add('d-none');

                // Estado MP activo
                estadoMP.disabled = false;
                estadoMP.name = 'estado';

                // Estado clientes desactivado
                estadoClientes.disabled = true;
                estadoClientes.name = 'estadoClientes';
            } else {
                // Mostrar filtros de clientes
                filtrosClientes.classList.remove('d-none');
                filtrosMP.classList.add('d-none');

                // Estado clientes activo
                estadoClientes.disabled = false;
                estadoClientes.name = 'estado';

                // Estado MP desactivado
                estadoMP.disabled = true;
                estadoMP.name = 'estadoMP';
            }
        }

        // Inicializar según el valor cargado por PHP
        actualizarFiltros();

        // Cambiar al modificar el tipo de pedido
        selectTipo.addEventListener('change', actualizarFiltros);
    });

    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('.form-busqueda');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            const tipoPedido = document.getElementById('tipoPedido').value;

            const fechaDesde = document.getElementById('fecha_desde').value.trim();
            const fechaHasta = document.getElementById('fecha_hasta').value.trim();

            // MP
            const proveedor = document.getElementById('proveedor')?.value.trim();
            const materia = document.getElementById('materia')?.value.trim();
            const estadoMP = document.getElementById('estadoMP')?.value.trim();

            // CLIENTES
            const cliente = document.getElementById('cliente')?.value.trim();
            const estadoCli = document.getElementById('estadoClientes')?.value.trim();

            let hayFiltro = false;

            // Fechas aplican a ambos
            if (fechaDesde !== '' || fechaHasta !== '') {
                hayFiltro = true;
            }

            if (tipoPedido === 'mp') {
                if (proveedor || materia || estadoMP) {
                    hayFiltro = true;
                }
            }

            if (tipoPedido === 'clientes') {
                if (cliente || estadoCli) {
                    hayFiltro = true;
                }
            }

            if (!hayFiltro) {
                e.preventDefault();

                Swal.fire({
                    icon: 'info',
                    title: 'Sin filtros',
                    text: 'Debes completar al menos un criterio para realizar la búsqueda.',
                    confirmButtonText: 'Entendido'
                });

                return;
            }
        });
    });
</script>