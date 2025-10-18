<?php
include_once("head/headbuscador.php");
require_once(__DIR__ . "/../controlador/controladoradmempleado.php");
$obj = new ControladorAdmEmpleado();
// admempleados/vista/buscador.php
// Opcional: $roles puede venir del controlador si querés renderizar el combo de Rol
// $roles = $roles ?? [];
$puestos    = $obj->traerPuesto();
?>
<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Buscador de empleados</h1>
</div>
<div class="contenido-principal">

    <!-- Filtros -->
    <div class="card mb-3">
        <div class="card-body">
            <form id="formFiltros" class="row g-2 align-items-end">
                <div class="col-12 col-md-4">
                    <label for="qGlobal" class="form-label">Búsqueda</label>
                    <input id="qGlobal" type="text" class="form-control"
                        placeholder="Nombre, apellido, legajo, DNI, email...">
                    <!--<div class="form-text">Búsqueda global por múltiples campos.</div>-->
                </div>

                <div class="col-6 col-md-2">
                    <label for="f_estado" class="form-label">Estado</label>
                    <select id="f_estado" class="form-select">
                        <option value="">Todos</option>
                        <option value="Activo">Activo</option>
                        <option value="Inactivo">Inactivo</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label for="f_puesto" class="form-label">Puesto</label>
                    <select id="f_puesto" class="form-select">
                        <option value="">Todos</option>
                        <?php if (!empty($puestos)): ?>
                            <?php foreach ($puestos as $p): ?>
                                <option value="<?= htmlspecialchars($p['idPuesto']) ?>">
                                    <?= htmlspecialchars($p['descrPuesto']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label for="f_desde" class="form-label">Fecha de Alta desde</label>
                    <input id="f_desde" type="date" class="form-control">
                </div>

                <div class="col-6 col-md-2">
                    <label for="f_hasta" class="form-label">Fecha de alta hasta</label>
                    <input id="f_hasta" type="date" class="form-control">
                </div>

                <div class="col-12 d-flex gap-2 mt-2">
                    <button id="btnBuscar" type="button" class="btn btn-primary">
                        Buscar
                    </button>
                    <button id="btnLimpiar" type="button" class="btn btn-outline-secondary">
                        Limpiar
                    </button>
                    
                </div>
            </form>
        </div>
    </div>

    <!-- Resultados -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tblBusquedaEmpleados" class="table table-striped table-hover w-100">
                    <thead>
                        <tr>
                            <th>Legajo</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>DNI</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Fecha Alta</th>
                            <th>Ver Registro</th>
                        </tr>
                    </thead>
                    <tbody><!-- Se llena por JS --></tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modal: Ver (solo lectura) -->
<div class="modal fade" id="verEmpleado" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle del empleado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Apellido, Nombre</label>
                        <input class="form-control" data-ver="apynom" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">DNI</label>
                        <input class="form-control" data-ver="dni" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Género</label>
                        <input class="form-control" data-ver="sexo" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha de nacimiento</label>
                        <input class="form-control" data-ver="fechanac" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">CUIL</label>
                        <input class="form-control" data-ver="cuil" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Legajo</label>
                        <input class="form-control" data-ver="legajo" readonly>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label d-block">Estado</label>
                        <span class="badge rounded-pill px-3 py-2" data-ver="estado-badge">—</span>
                        <input class="form-control mt-2" data-ver="estado" readonly style="display:none">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Puesto</label>
                        <input class="form-control" data-ver="puesto" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha ingreso</label>
                        <input class="form-control" data-ver="fecha" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Usuario vinculado</label>
                        <input class="form-control" data-ver="usuario" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Provincia</label>
                        <input class="form-control" data-ver="provincia" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Localidad</label>
                        <input class="form-control" data-ver="localidad" readonly>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email</label>
                        <input class="form-control" data-ver="email" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Teléfono Particular</label>
                        <input class="form-control" data-ver="telefono" readonly>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Dirección</label>
                        <input class="form-control" data-ver="direccion" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JS específico del buscador -->
<script src="../../rsc/script/buscadorEmpleados.js"></script>
<?php require_once("foot/foot.php"); ?>