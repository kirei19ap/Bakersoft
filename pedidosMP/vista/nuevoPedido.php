<?php
    include_once("head/head.php");
    require_once("../controlador/controladorPedido.php");
    $ctrl = new controladorPedido();
    $proveedores = $ctrl->proveedoresTodos();
    $ultimoPedido = $ctrl->nroultimopedido();
    $proveedorSeleccionado = $_SESSION['idprove'] ?? -1;
?>
<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Pedidos de Materia Prima</h1>
</div>
<div class="contenido-principal">

    <div class="encabezado-tabla">
        <div>
            <!-- <ion-icon name="add-outline"></ion-icon>
            <a href="nuevo_empleado.php">Registrar Materia Prima</a> -->

        </div>
    </div>

    <div class="contenido">
        <div class="mb-4">
            <div class="alert alert-primary d-flex justify-content-between align-items-center" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-receipt-cutoff me-2"></i>
                    <div>
                        <strong>Pedido actual Nº:</strong> <?php echo $ultimoPedido + 1; ?>
                    </div>
                </div>
                <div>
                    <strong>Estado:</strong> Nuevo
                </div>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-body">
                <form class="row g-3">
                    <div class="col-12">
                        <div class="row align-items-end">
                            <div class="col-md-6">
                                <label for="proveedor" class="form-label">Proveedor</label>
                                <select class="form-select" name="proveedor" id="proveedor"
                                    <?php if (isset($_SESSION['idprove'])) echo 'disabled'; ?>>
                                    <option value="-1" disabled
                                        <?php if ($proveedorSeleccionado == -1) echo 'selected'; ?>>
                                        Seleccione un proveedor
                                    </option>
                                    <?php
                                        foreach ($proveedores as $proveedor) {
                                            $selected = ($proveedor['id_proveedor'] == $proveedorSeleccionado) ? 'selected' : '';
                                            echo "<option value=\"{$proveedor['id_proveedor']}\" $selected>{$proveedor['nombre']}</option>";
                                        }
                                        ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="materiaPrima" class="form-label">Materia Prima</label>
                                <select class="form-select" name="materiaPrima" id="materiaPrima"></select>
                            </div>
                        </div>
                        <div class="form-text text-muted mt-1">
                            Una vez seleccionado el proveedor, no se podrá modificar.
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label for="unidad" class="form-label">Unidad de medida:</label>
                        <input type="text" class="form-control" id="unidad" readonly>
                    </div>

                    <div class="col-md-3">
                        <label for="stockactual" class="form-label">Stock Actual:</label>
                        <input type="text" class="form-control" id="stockactual" readonly>
                    </div>

                    <div class="col-md-3">
                        <label for="cantidad" class="form-label">Cantidad a pedir</label>
                        <input type="number" min="1" class="form-control" id="cantidad" placeholder="Ingrese cantidad">
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button id="agregarBTN" class="btn btn-primary">Agregar al pedido</button>
                    </div>
                </form>
                <hr class="my-4">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive mb-3">
                            <table id="tablaPedido" class="table table-striped table-bordered align-middle text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th>Materia Prima</th>
                                        <th>Cantidad</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                        <div class="text-end">

                            <!-- Botón para generar el pedido final -->
                            <button id="generarPedidoBtn" class="btn btn-success">Generar pedido</button>
                            <button type="button" class="btn btn-danger" onclick="window.location.href='index.php'">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
    require_once("foot/foot.php")
?>