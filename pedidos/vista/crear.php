<?php
$currentPage = 'pedidos';
include_once("../../includes/head_app.php");
require_once("../controlador/controladorPedidos.php");

$ctrl = new controladorPedidos();
$productos = $ctrl->obtenerProductosVenta();
?>

<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Nuevo Pedido</h1>
</div>

<div class="contenido-principal">
    <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
        <p class="mb-0 text-muted">Registrar pedido de cliente con su detalle de productos.</p>
        <a href="index.php" class="btn btn-outline-secondary btn-sm">
            <ion-icon name="arrow-back-outline"></ion-icon>
            Volver al listado
        </a>
    </div>

    <div class="contenido">
        <div class="card mb-3">
            <div class="card-body">

                <form id="formPedido" action="../controlador/controladorPedidos.php" method="POST">
                    <input type="hidden" name="accion" value="crearPedido">

                    <!-- NUEVO: controles internos para el modo de cliente -->
                    <input type="hidden" id="modoCliente" name="modoCliente" value="nuevo">
                    <input type="hidden" id="idCliente" name="idCliente" value="">

                    <!-- DATOS DEL CLIENTE -->
                    <div class="card mb-3">
                        <div class="card-header">
                            Datos del cliente
                        </div>
                        <div class="card-body">

                            <!-- BUSCADOR DE CLIENTE -->
                            <div class="row mb-3 align-items-end">
                                <div class="col-md-6">
                                    <label for="busquedaCliente" class="form-label">
                                        Buscar cliente (nombre, email o teléfono)
                                    </label>
                                    <input type="text" class="form-control" id="busquedaCliente"
                                        placeholder="Ej: Juan, 351..., @mail.com">
                                </div>
                                <div class="col-md-3 d-flex gap-2">
                                    <button type="button" class="btn btn-outline-primary mt-auto" id="btnBuscarCliente">
                                        <ion-icon name="search-outline"></ion-icon>
                                        Buscar cliente
                                    </button>
                                </div>
                                <!-- La columna de "Registrar nuevo cliente" se movió más abajo, junto a Altura -->
                            </div>

                            <!-- RESULTADOS DE BÚSQUEDA -->
                            <div id="resultadosBusquedaCliente" class="mb-3" style="display:none;">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Teléfono</th>
                                                <th>Email</th>
                                                <th>Dirección</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tablaResultadosCliente">
                                            <!-- filas dinámicas via JS -->
                                        </tbody>
                                    </table>
                                </div>
                                <small class="text-muted">Haga clic en un cliente para seleccionarlo.</small>
                            </div>

                            <!-- INDICADOR DE CLIENTE SELECCIONADO -->
                            <div class="mb-2" id="bloqueClienteSeleccionado" style="display:none;">
                                <span class="badge bg-info text-dark">
                                    Cliente seleccionado:
                                    <span id="clienteSeleccionado"></span>
                                </span>
                            </div>

                            <!-- =========================
     DATOS DEL CLIENTE
========================= -->
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h5 class="mb-0">Datos del cliente</h5>

                                <button class="btn btn-outline-secondary btn-sm"
                                    type="button"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#collapseDatosCliente"
                                    aria-expanded="true"
                                    aria-controls="collapseDatosCliente">
                                    Mostrar / Ocultar
                                </button>
                            </div>

                            <div class="collapse show" id="collapseDatosCliente">
                                <div class="card card-body">

                                    <input type="hidden" id="idCliente" name="idCliente" value="">
                                    <input type="hidden" id="modoCliente" name="modoCliente" value="nuevo">

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">Nombre *</label>
                                            <input type="text" id="clienteNombre" name="clienteNombre" class="form-control">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Teléfono *</label>
                                            <input type="text" id="clienteTelefono" name="clienteTelefono" class="form-control">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Email *</label>
                                            <input type="email" id="clienteEmail" name="clienteEmail" class="form-control">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label">Calle *</label>
                                            <input type="text" id="clienteCalle" name="clienteCalle" class="form-control">
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Altura *</label>
                                            <input type="number" id="clienteAltura" name="clienteAltura" class="form-control">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label">Provincia *</label>
                                            <select id="clienteProvincia" name="clienteProvincia" class="form-select">
                                                <option value="">Seleccione...</option>
                                            </select>
                                        </div>

                                        <div class="col-md-5">
                                            <label class="form-label">Localidad *</label>
                                            <select id="clienteLocalidad" name="clienteLocalidad" class="form-select" disabled>
                                                <option value="">Seleccione una provincia...</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end mt-3">
                                        <button type="button" id="btnNuevoCliente" class="btn btn-primary">
                                            Registrar nuevo cliente
                                        </button>
                                    </div>

                                </div>
                            </div>


                        </div>
                    </div>

                    <!-- DATOS DEL PEDIDO -->
                    <div class="card mb-3">
                        <div class="card-header">
                            Datos del pedido
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <label for="fechaPedido" class="form-label">Fecha del pedido</label>
                                    <input type="date" class="form-control" id="fechaPedido" name="fechaPedido"
                                        value="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div class="col-md-9">
                                    <label for="observaciones" class="form-label">Observaciones</label>
                                    <textarea class="form-control" id="observaciones" name="observaciones" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- DETALLE DEL PEDIDO -->
                    <div class="card mb-3">
                        <div class="card-header">
                            Detalle del pedido
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle" id="tablaDetallePedido">
                                    <thead>
                                        <tr>
                                            <th style="width: 40%;">Producto</th>
                                            <th style="width: 15%;">Cantidad</th>
                                            <th style="width: 20%;">Precio unitario</th>
                                            <th style="width: 20%;">Subtotal</th>
                                            <th style="width: 5%;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="fila-detalle">
                                            <td>
                                                <select name="idProducto[]" class="form-select campo-producto">
                                                    <option value="">Seleccione un producto...</option>
                                                    <?php foreach ($productos as $prod): ?>
                                                        <option value="<?php echo $prod['idProducto']; ?>"
                                                            data-precio="<?php echo number_format($prod['precio_venta'], 2, '.', ''); ?>">
                                                            <?php
                                                            echo htmlspecialchars($prod['nombre']) . ' (' . htmlspecialchars($prod['unidad_medida']) . ')';
                                                            ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>

                                            </td>
                                            <td>
                                                <input type="number" name="cantidad[]" class="form-control campo-cantidad"
                                                    min="1" step="1">
                                            </td>
                                            <td>
                                                <input type="number" name="precioUnitario[]" class="form-control campo-precio"
                                                    min="0" step="0.50">
                                            </td>
                                            <td>
                                                <input type="number" name="subtotal[]" class="form-control campo-subtotal"
                                                    readonly step="0.01">
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger btnEliminarFila"
                                                    title="Eliminar línea">
                                                    <ion-icon name="trash-outline"></ion-icon>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Botón Agregar producto debajo de la tabla -->
                            <div class="d-flex justify-content-start mt-2 mb-2">
                                <button type="button" class="btn btn-sm btn-success" id="btnAgregarLinea">
                                    <ion-icon name="add-outline"></ion-icon>
                                    Agregar producto
                                </button>
                            </div>

                            <div class="d-flex justify-content-end mt-2">
                                <div class="input-group" style="max-width: 250px;">
                                    <span class="input-group-text">Total</span>
                                    <input type="text" class="form-control text-end" id="totalPedido"
                                        name="totalPedido" readonly value="0.00">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- BOTONES -->
                    <div class="d-flex justify-content-end gap-2">
                        <button type="reset" class="btn btn-outline-secondary">Limpiar</button>
                        <button type="submit" class="btn btn-primary" id="btnGuardarPedido">
                            <ion-icon name="save-outline"></ion-icon>
                            Registrar pedido
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<?php
require_once("foot/foot.php");
?>