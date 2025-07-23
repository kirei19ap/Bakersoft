<?php
    include_once("head/head.php");
    require_once("../controlador/controladorBuscador.php");
    $ctrl = new controladorBuscador();
    $proveedores = $ctrl->proveedoresTodos();
    $materiasPrimas = $ctrl->mpTodas();
    $estados = $ctrl->obtenerEstados();
    $pedidos = [];

    $fecha = $_GET['fecha'] ?? null;
    $proveedorId = $_GET['proveedor'] ?? null;
    $materiaId = $_GET['materia'] ?? null;
    $estado = $_GET['estado'] ?? null;

    if ($ctrl->hayFiltroValido($fecha, $proveedorId, $materiaId, $estado)) {
        $pedidos = $ctrl->buscar($fecha, $proveedorId, $materiaId, $estado);
    }
?>

<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Buscador</h1>
</div>
<div class="contenido-principal">

    <div class="encabezado-tabla">

    </div>

    <pre>
<?php

?>
</pre>

    <h1>Buscar Pedidos</h1>

    <form method="GET" action="" class="form-busqueda">
        <div class="form-group">
            <label for="fecha">Fecha del Pedido:</label>
            <input type="date" name="fecha" id="fecha" value="<?= htmlspecialchars($_GET['fecha'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="proveedor">Proveedor:</label>
            <select name="proveedor" id="proveedor">
                <option value="">-- Todos --</option>
                <?php foreach ($proveedores as $prov): ?>
                <option value="<?= $prov['id_proveedor'] ?>"
                    <?= (isset($_GET['proveedor']) && $_GET['proveedor'] == $prov['id_proveedor']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($prov['nombre']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="materia">Materia Prima:</label>
            <select name="materia" id="materia">
                <option value="">-- Todas --</option>
                <?php foreach ($materiasPrimas as $mp): ?>
                <option value="<?= $mp['id'] ?>"
                    <?= (isset($_GET['materia']) && $_GET['materia'] == $mp['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($mp['nombre']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="estado">Estado:</label>
            <select name="estado" id="estado">
                <option value="">-- Todos --</option>
                <?php foreach ($estados as $estado): ?>
                <option value="<?= $estado['codEstado'] ?>"
                    <?= (isset($_GET['estado']) && $_GET['estado'] == $estado['codEstado']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($estado['descEstado']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit">Buscar</button>
    </form>

    <?php if ($pedidos): ?>
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
                    <td><?= $pedido['idPedido'] ?></td>
                    <td><?= $pedido['fechaPedido'] ?></td>
                    <td><?= htmlspecialchars($pedido['proveedor_nombre']) ?></td>
                    <td><?= $pedido['estado'] ?></td>
                    <td class="text-center">
                        <a class="btn btn-success verMPbtn" title="Consultar Materia Prima"
                            href="verPedido.php?id=<?= $pedido['idPedido'] ?>">
                            <ion-icon name="eye-outline"></ion-icon>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php elseif ($fecha || $proveedorId): ?>
        <p>No se encontraron pedidos con esos criterios.</p>
        <?php endif; ?>


        <?php
    require_once("foot/foot.php")
?>