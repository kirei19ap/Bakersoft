<?php
    include_once("../../includes/head_app.php");
    require_once("../controlador/controladorBuscador.php");
    $ctrl = new controladorBuscador();
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        echo "ID de pedido no válido.";
        exit;
    }
    $idPedido = (int) $_GET['id'];
    $pedido = $ctrl->traerDetallePedido($idPedido);
    $proveedorNombre = $pedido[0]['proveedor'];
?>
<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Detalle de Pedido de Materia Prima</h1>
</div>
<div class="contenido-principal">

    <div class="encabezado-tabla">
        <div>
            <!-- <ion-icon name="add-outline"></ion-icon>
            <a href="nuevo_empleado.php">Registrar Materia Prima</a> -->

        </div>
    </div>
    <div class="contenido">
        <div class="container py-4">
            <h2 class="mb-4">Detalle del Pedido Nº <?= htmlspecialchars($idPedido) ?></h2>

            <div class="card shadow-sm mb-4">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-semibold me-2">Proveedor:</span>
                        <span class="fw-bold fs-5 text-primary"><?= htmlspecialchars($proveedorNombre) ?></span>
                    </div>
                    <script>
                    const pedidoId = <?php echo $idPedido ?>
                    </script>
                    <button id="cancelarPedidoBtn" class="btn btn-danger">
                        <ion-icon name="close-circle-outline"></ion-icon> Cancelar Pedido
                    </button>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title">Materias Primas</h5>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Materia Prima</th>
                                    <th>Cantidad</th>
                                    <th>Unidad de Medida</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pedido as $item): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item['materiaprima']) ?></td>
                                    <td><?= htmlspecialchars($item['cantidad']) ?></td>
                                    <td><?= htmlspecialchars($item['unidad_medida']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <a href="index.php" class="btn btn-secondary">← Volver a la busqueda</a>
            <a href="generarPDF.php?id=<?= $idPedido ?>" target="_blank" class="btn btn-primary">
                <ion-icon name="print-outline"></ion-icon> Imprimir en PDF
            </a>
        </div>
    </div>
    <?php
    require_once("foot/foot.php")
?>