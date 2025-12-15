<?php
$currentPage = 'pedidos_mp';
include_once("../../includes/head_app.php");
require_once("../controlador/controladorPedido.php");
$obj = new controladorPedido();
$filas = $obj->traerPedidos();

function badgeEstado($estadoDesc) {
    $clase = 'bg-secondary';

    switch ($estadoDesc) {
        case 'Registrado':
            $clase = 'bg-secondary';
            break;
        case 'Recibida':
        case 'Entregado':
            $clase = 'bg-success';
            break;
        case 'Cancelado':
        case 'Rechazada':
            $clase = 'bg-danger';
            break;
        case 'En Evaluación':
        case 'Aprobada':
        case 'Generado':
        case 'Confirmado':
        case 'Preparado':
            $clase = 'bg-warning text-dark';
            break;
    }

    return '<span class="badge '.$clase.'">'.htmlspecialchars($estadoDesc).'</span>';
}
?>
<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Pedidos de Materia Prima</h1>
</div>
<div class="contenido-principal">

    <div class="encabezado-tabla">
        <div class="mb-3 buscador">
            <h3 class="mb-0 text-muted">Listado de pedidos de materia prima.</h3>
        </div>
        <div>
            <!-- <ion-icon name="add-outline"></ion-icon>
            <a href="nuevo_empleado.php">Registrar Materia Prima</a> -->
            <a href="nuevoPedido.php" class="btn btn-success">
                Nuevo Pedido
            </a>
        </div>
    </div>

    <div class="">
        <?php
        //var_dump($filas);
        ?>
    </div>


    <div class="contenido">
        <div class="card">
            <div class="card-body">
                <div class="tabla-empleados">
                    <table id="Pedidos" class="shadow-sm table table-striped table-hover table-bordered">
                        <thead class="thead-dark">
                            <tr class="text-center">
                                <th scope="col">N° de pedido</th>
                                <th scope="col">Fecha de Pedido</th>
                                <th scope="col">Proveedor</th>
                                <th scope="col">Estado</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Aquí se llena la tabla con las materias primas -->

                            <?php if ($filas): ?>
                                <?php foreach ($filas as $pedmp) { ?>
                                    <tr>
                                        <td><?php echo $pedmp['idPedido']; ?></td>
                                        <td><?php echo $pedmp['fechaPedido']; ?></td>
                                        <td><?php echo $pedmp['proveedor']; ?></td>
                                        <td class = "text-center"><?= badgeEstado($pedmp['estado']); ?></td>
                                        <td class="text-center">
                                            <a class="btn btn-success" title="Consultar Pedido" href="verPedido.php?id=<?php echo $pedmp['idPedido']; ?>">
                                                <ion-icon name="eye-outline"></ion-icon>
                                            </a>
                                        </td>
                                    </tr>
                                <?php }  ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No existen registros para mostrar</td>
                                </tr>
                            <?php endif; ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <?php
    require_once("foot/foot.php")
    ?>