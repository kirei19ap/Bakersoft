<?php
$currentPage = 'pedidos_mp';
include_once("../../includes/head_app.php");
require_once("../controlador/controladorPedido.php");
$obj = new controladorPedido();
$filas = $obj->traerPedidos();

?>
<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Pedidos de Materia Prima</h1>
</div>
<div class="contenido-principal">

    <div class="encabezado-tabla">
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
                                        <td><?php echo $pedmp['estado']; ?></td>
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