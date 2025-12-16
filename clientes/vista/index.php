<?php
$currentPage = 'clientes';
include_once("../../includes/head_app.php");
?>

<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Administración de Clientes</h1>
</div>

<div class="contenido-principal">
    <div class="encabezado-tabla">
        <div class="mb-3 buscador">
            <h3 class="mb-0 text-muted">Listado de clientes.</h3>
        </div>
        <div>
            <!-- <ion-icon name="add-outline"></ion-icon>
            <a href="nuevo_empleado.php">Registrar Materia Prima</a> -->
        </div>
    </div>

    <!--FILTROS
    <div class="row mb-3">
        <div class="col-md-3">
            <label class="form-label">Estado</label>
            <select id="filtroEstado" class="form-select">
                <option value="">Todos</option>
                <option value="Activo">Activo</option>
                <option value="Eliminado">Eliminado</option>
                <option value="Deshabilitado">Deshabilitado</option>
            </select>
        </div>
    </div>-->
    <div class="contenido">
        <div class="tabla-empleados shadow-sm">
            <div class="card">
                <div class="card-body">
                    <table id="tablaClientes" class="table table-striped table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Dirección</th>
                                <th>Estado</th>
                                <th class="text-center" style="width:140px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Se carga por JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal VER -->
<div class="modal fade" id="modalVerCliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre</label>
                        <input type="text" id="verNombre" class="form-control" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Teléfono</label>
                        <input type="text" id="verTelefono" class="form-control" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input type="text" id="verEmail" class="form-control" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Dirección</label>
                        <input type="text" id="verDireccion" class="form-control" readonly>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal EDITAR -->
<div class="modal fade" id="modalEditarCliente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form id="formEditarCliente" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="editIdCliente" name="idCliente">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="editNombre" class="form-label">Nombre</label>
                        <input type="text" id="editNombre" name="nombre" class="form-control" maxlength="100">
                    </div>

                    <div class="col-md-6">
                        <label for="editTelefono" class="form-label">Teléfono</label>
                        <input type="text" id="editTelefono" name="telefono" class="form-control" maxlength="20">
                    </div>

                    <div class="col-md-6">
                        <label for="editEmail" class="form-label">Email</label>
                        <input type="email" id="editEmail" name="email" class="form-control" maxlength="50">
                    </div>

                    <div class="col-md-6">
                        <label for="editCalle" class="form-label">Calle</label>
                        <input type="text" id="editCalle" name="calle" class="form-control" maxlength="100">
                    </div>

                    <div class="col-md-3">
                        <label for="editAltura" class="form-label">Altura</label>
                        <input type="number" id="editAltura" name="altura" class="form-control" min="1" step="1">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Estado</label>
                        <input type="text" id="editEstado" class="form-control" readonly>
                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
            </div>
        </form>
    </div>
</div>

<?php
require_once("foot/foot.php");
?>