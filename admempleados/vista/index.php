<?php
include_once("head/head.php");

require_once(__DIR__ . "/../controlador/controladoradmempleado.php");
$obj = new ControladorAdmEmpleado();
$filas = $obj->listar();
require_once(__DIR__ . "/../../config/bd.php");
$pdo = (new bd())->conexion();
$provincias = $pdo->query("SELECT id_provincia, provincia FROM provincias ORDER BY provincia")->fetchAll(PDO::FETCH_ASSOC);
$usuarios = $pdo->query("SELECT id, usuario, COALESCE(nomyapellido,'') AS nomyapellido FROM usuarios ORDER BY nomyapellido, usuario")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Registro de empleados</h1>
</div>
<div class="contenido-principal">

    <div class="encabezado-tabla">
        <div class="mb-3">

        </div>
        <div>
            <!-- <ion-icon name="add-outline"></ion-icon>
            <a href="nuevo_empleado.php">Registrar Materia Prima</a> -->
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#nuevoEmpleado">
                Registrar Empleado
            </button>
        </div>
    </div>

    <div class=""><?php
                    if (!empty($_SESSION['error_valida_existe'])):
                        $mensaje = $_SESSION['error_valida_existe'];
                        unset($_SESSION['error_valida_existe']);
                    ?>
            <script>
                console.log("<?php echo $mensaje; ?>");
                Swal.fire({
                    icon: 'warning',
                    title: 'Error al crear',
                    text: '<?php echo $mensaje; ?>',
                    confirmButtonText: 'Aceptar'
                });
            </script>
        <?php endif; ?>
    </div>

    <div class="contenido container-fluid">
        <div class="tabla-empleados">

            <table id="Empleados-lista" class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th hidden>ID</th>
                        <th>Apellido, Nombre</th>
                        <th>DNI</th>
                        <th>Puesto</th>
                        <th>Fecha ingreso</th>
                        <th>Estado</th>
                        <th style="display:none">Email</th>
                        <th style="display:none">Teléfono</th>
                        <th style="display:none">Dirección</th>
                        <th style="display:none">EstadoRaw</th>
                        <th style="display:none">UsuarioID</th>
                        <th style="display:none">ProvinciaID</th>
                        <th style="display:none">LocalidadID</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filas as $r): ?>
                        <tr>
                            <td hidden><?= $r['id_empleado'] ?></td>
                            <td><?= htmlspecialchars($r['apellido'] . ", " . $r['nombre']) ?></td>
                            <td><?= htmlspecialchars($r['dni']) ?></td>
                            <td><?= htmlspecialchars($r['puesto']) ?></td>
                            <td><?= htmlspecialchars($r['fecha_ingreso']) ?></td>
                            <td>
                                <?php if ($r['estado'] === 'Activo'): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <!-- columnas ocultas auxiliares -->
                            <td hidden><?= htmlspecialchars($r['email']) ?></td>
                            <td hidden><?= htmlspecialchars($r['telefono']) ?></td>
                            <td hidden><?= htmlspecialchars($r['direccion']) ?></td>
                            <td hidden><?= htmlspecialchars($r['estado']) ?></td>
                            <td hidden><?= htmlspecialchars((string)($r['usuario_id'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                            <td hidden><?= (int)$r['provincia'] ?></td>
                            <td hidden><?= (int)$r['localidad'] ?></td>

                            <td class="text-center">
                                <button class="btn btn-success verEmpleado" title="Consultar Empleado"><ion-icon name="eye-outline"></ion-icon></button>
                                <button class="btn btn-primary editEmpleado" title="Consultar Empleado"><ion-icon name="create-outline"></ion-icon></button>
                                <button class="btn btn-danger deleteEmpleado" title="Consultar Empleado"><ion-icon name="trash-outline"></ion-icon></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal: Nuevo -->
    <div class="modal fade" id="nuevoEmpleado" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="crearEmpleado.php" method="POST" class="needs-validation" novalidate>
                    <div class="modal-header">
                        <h5 class="modal-title">Nuevo empleado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre</label>
                                <input name="nombre" class="form-control" required>
                                <div class="invalid-feedback">Obligatorio.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Apellido</label>
                                <input name="apellido" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">DNI</label>
                                <input name="dni" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha ingreso</label>
                                <input type="date" name="fecha_ingreso" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Puesto</label>
                                <input name="puesto" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Teléfono</label>
                                <input name="telefono" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Provincia</label>
                                <select id="emp_provincia" name="provincia" class="form-select" required>
                                    <option value="">-- Seleccionar --</option>
                                    <?php foreach ($provincias as $p): ?>
                                        <option value="<?= (int)$p['id_provincia'] ?>"><?= htmlspecialchars($p['provincia']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Seleccioná una provincia.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Localidad</label>
                                <select id="emp_localidad" name="localidad" class="form-select" required>
                                    <option value="">-- Seleccioná provincia primero --</option>
                                </select>
                                <div class="invalid-feedback">Seleccioná una localidad.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Estado</label>
                                <select name="estado" class="form-select">
                                    <option value="Activo">Activo</option>
                                    <option value="Inactivo">Inactivo</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Dirección</label>
                                <input name="direccion" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Usuario vinculado (opcional)</label>
                                <select name="usuario_id" id="new_usuario_id" class="form-select">
                                    <option value="">— Sin usuario —</option>
                                    <?php foreach ($usuarios as $u):
                                        $label = trim($u['nomyapellido']) !== ''
                                            ? htmlspecialchars($u['nomyapellido'] . ' (' . $u['usuario'] . ')', ENT_QUOTES, 'UTF-8')
                                            : htmlspecialchars($u['usuario'], ENT_QUOTES, 'UTF-8');
                                    ?>
                                        <option value="<?= (int)$u['id'] ?>"><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button class="btn btn-success" type="submit">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Editar -->
    <div class="modal fade" id="editarEmpleado" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="modificarEmpleado.php" method="POST" class="needs-validation" novalidate>
                    <div class="modal-header">
                        <h5 class="modal-title">Editar empleado</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_empleado" id="edit_id_empleado">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre</label>
                                <input name="nombre" id="edit_nombre" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Apellido</label>
                                <input name="apellido" id="edit_apellido" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">DNI</label>
                                <input name="dni" id="edit_dni" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha ingreso</label>
                                <input type="date" name="fecha_ingreso" id="edit_fecha_ingreso" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Puesto</label>
                                <input name="puesto" id="edit_puesto" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" id="edit_email" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Teléfono</label>
                                <input name="telefono" id="edit_telefono" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Provincia</label>
                                <select id="edit_emp_provincia" name="provincia" class="form-select" required>
                                    <option value="">-- Seleccionar --</option>
                                    <?php foreach ($provincias as $p): ?>
                                        <option value="<?= (int)$p['id_provincia'] ?>"><?= htmlspecialchars($p['provincia']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Seleccioná una provincia.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Localidad</label>
                                <select id="edit_emp_localidad" name="localidad" class="form-select" required>
                                    <option value="">-- Seleccioná provincia primero --</option>
                                </select>
                                <div class="invalid-feedback">Seleccioná una localidad.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Estado</label>
                                <select name="estado" id="edit_estado" class="form-select">
                                    <option value="Activo">Activo</option>
                                    <option value="Inactivo">Inactivo</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Dirección</label>
                                <input name="direccion" id="edit_direccion" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Usuario vinculado (opcional)</label>
                                <select name="usuario_id" id="edit_usuario_id" class="form-select">
                                    <option value="">— Sin usuario —</option>
                                    <?php foreach ($usuarios as $u):
                                        $label = trim($u['nomyapellido']) !== ''
                                            ? htmlspecialchars($u['nomyapellido'] . ' (' . $u['usuario'] . ')', ENT_QUOTES, 'UTF-8')
                                            : htmlspecialchars($u['usuario'], ENT_QUOTES, 'UTF-8');
                                    ?>
                                        <option value="<?= (int)$u['id'] ?>"><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button class="btn btn-primary" type="submit">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Ver -->
    <!-- Modal: Ver (REEMPLAZAR) -->
    <div class="modal fade" id="verEmpleado" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Empleado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Apellido, Nombre</label>
                            <input id="ver_apynom" class="form-control" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">DNI</label>
                            <input id="ver_dni" class="form-control" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fecha ingreso</label>
                            <input id="ver_fecha" class="form-control" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Puesto</label>
                            <input id="ver_puesto" class="form-control" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estado</label>
                            <input id="ver_estado" class="form-control" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Usuario vinculado</label>
                            <input id="ver_usuario" class="form-control" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input id="ver_email" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input id="ver_tel" class="form-control" readonly>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Dirección</label>
                            <input id="ver_dir" class="form-control" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Provincia</label>
                            <input id="ver_provincia" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Localidad</label>
                            <input id="ver_localidad" class="form-control" readonly>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal: Eliminar -->
    <div class="modal fade" id="borrarEmpleado" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="eliminarEmpleado.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Eliminar</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>¿Confirmás eliminar al empleado <b id="del_apynom"></b> (ID <span id="del_id"></span>)?</p>
                        <input type="hidden" name="id_empleado" id="del_id_input">
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button class="btn btn-danger" type="submit">Eliminar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
    require_once("foot/foot.php")
    ?>