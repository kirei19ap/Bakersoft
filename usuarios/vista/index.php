<?php
$currentPage = 'adminUsuarios';
include_once("../../includes/head_app.php");
require_once("../controlador/controladorusuarios.php");
require_once(__DIR__ . "/../../config/bd.php");
$obj = new controladorUsuario();
$usuarios = $obj->mostrarTodos();
$roles = $obj->traerRoles();
$pdo = (new bd())->conexion();

$roles = $pdo->query("SELECT id_rol, nombre_rol FROM roles ORDER BY nombre_rol")->fetchAll(PDO::FETCH_ASSOC);
$rolesMap = [];
foreach ($roles as $r) {
    $rolesMap[(int)$r['id_rol']] = $r['nombre_rol'];
}

function e($v)
{
    return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
}
?>

<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Administración de Usuarios</h1>
</div>
<div class="contenido-principal">

    <div class="encabezado-tabla">
        <div class="">
            <h3 class="mb-0 text-muted">Listado de usuarios del sistema.</h3>
        </div>
        <div>
            <!-- <ion-icon name="add-outline"></ion-icon>
            <a href="nuevo_empleado.php">Registrar Materia Prima</a> -->
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#registrarUsuario">
                Registrar Usuario
            </button>
        </div>
    </div>
    <div class=""><?php
                    if (!empty($_SESSION['error_valida_proveedor'])):
                        $mensaje = $_SESSION['error_valida_proveedor'];
                        unset($_SESSION['error_valida_proveedor']);
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
    <div class=""><?php
                    if (!empty($_SESSION['error_borra_proveedor'])):
                        $mensaje = $_SESSION['error_borra_proveedor'];
                        unset($_SESSION['error_borra_proveedor']);
                    ?>
            <script>
                console.log("<?php echo $mensaje; ?>");
                Swal.fire({
                    icon: 'error',
                    title: 'Error al eliminar',
                    text: '<?php echo $mensaje; ?>',
                    confirmButtonText: 'Aceptar'
                });
            </script>
        <?php endif; ?>
    </div>

    <!-- Modal Registrar Usuario-->
    <div class="modal fade" id="registrarUsuario" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Registrar Usuario</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <form id="formUsu" action="crearUsuario.php" method="post" novalidate>
                        <!-- Datos del usuario -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="usuario" class="form-label">Usuario</label>
                                <input type="text" required class="form-control" name="usuario" id="usuario">
                                <div class="invalid-feedback">El usuario es obligatorio.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Rol</label>
                                <select name="rol" id="new_rol" class="form-select">
                                    <option value="">— Sin rol —</option>
                                    <?php foreach ($roles as $r): ?>
                                        <option value="<?= (int)$r['id_rol'] ?>"><?= e($r['nombre_rol']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Estado</label>
                                <select name="estado" id="new_estado" class="form-select">
                                    <option value="Activo" selected>Activo</option>
                                    <option value="Inactivo">Inactivo</option>
                                </select>
                            </div>


                            <div class="col-md-6">
                                <label for="nombre_usu" class="form-label">Nombre</label>
                                <input type="text" class="form-control" name="nombre_usu" id="nombre_usu">
                            </div>
                            <div class="col-md-6">
                                <label for="apellido_usu" class="form-label">Apellido</label>
                                <input type="text" class="form-control" name="apellido_usu" id="apellido_usu">
                            </div>
                        </div>

                        <!-- Separador visual -->
                        <hr class="my-3">

                        <!-- Credenciales -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="contrasena" class="form-label">Contraseña</label>
                                <input type="password" class="form-control" name="contrasena" id="contrasena"
                                    required minlength="6" autocomplete="new-password">
                                <div class="invalid-feedback" id="passLenFeedback">
                                    La contraseña es obligatoria y debe tener al menos 6 caracteres.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="contrasena_conf" class="form-label">Confirmar Contraseña</label>
                                <input type="password" class="form-control" name="contrasena_conf" id="contrasena_conf"
                                    required minlength="6" autocomplete="new-password">
                                <div class="invalid-feedback" id="passMismatchFeedback">
                                    Las contraseñas deben coincidir.
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer mt-3">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- Modal: Ver Usuario (NUEVO) -->
    <div class="modal fade" id="verUsuario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Usuario</label>
                            <input id="ver_usuario" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nombre y Apellido</label>
                            <input id="ver_nomyapellido" class="form-control" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Rol</label>
                            <input id="ver_rol" class="form-control" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estado</label>
                            <input id="ver_estado" class="form-control" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Fecha de Creación</label>
                            <input id="ver_fecha" class="form-control" readonly>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal: Editar Usuario -->
    <div class="modal fade" id="editarUsuario" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="modificarUsuario.php" method="POST" class="needs-validation" novalidate>
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Usuario</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="id" id="edit_id">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Usuario</label>
                                <input name="usuario" id="edit_usuario" class="form-control" required>
                                <div class="invalid-feedback">Obligatorio.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nombre y apellido</label>
                                <input name="nomyapellido" id="edit_nomyapellido" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Rol</label>
                                <select name="rol" id="edit_rol" class="form-select">
                                    <option value="">— Sin rol —</option>
                                    <?php foreach ($roles as $r): ?>
                                        <option value="<?= (int)$r['id_rol'] ?>"><?= e($r['nombre_rol']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Estado</label>
                                <select name="estado" id="edit_estado" class="form-select">
                                    <option value="Activo">Activo</option>
                                    <option value="Inactivo">Inactivo</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nueva contraseña (opcional)</label>
                                <input type="password" name="contrasena" id="edit_contrasena"
                                    class="form-control" minlength="6" autocomplete="new-password"
                                    placeholder="Dejar vacío para no cambiar">
                                <div class="invalid-feedback" id="passLenFeedbackEdit">
                                    Si vas a cambiarla, mínimo 6 caracteres.
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirmar contraseña</label>
                                <input type="password" name="contrasena2" id="edit_contrasena2"
                                    class="form-control" minlength="6" autocomplete="new-password"
                                    placeholder="Repetir si cambiás la contraseña">
                                <div class="invalid-feedback" id="passMismatchFeedbackEdit">
                                    Las contraseñas deben coincidir.
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                        <button class="btn btn-primary" type="submit">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Modal Warning previo a borrar -->
    <div class="modal" id="borrarUsuario" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="borraUsuario.php" method="post">
                    <input type="text" hidden name="borrarUsuarioId" id="borrarUsuarioId">
                    <div class="modal-body">
                        <p>Esta seguro que desea eliminar el siguiente usuario?</p>
                        <div class="table-responsive">
                            <table class="table table-primary">
                                <thead>
                                    <tr>
                                        <th scope="col">Usuario</th>
                                        <th scope="col">Nombre y Apellido</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="">
                                        <td name="deleteidUsu" id="deleteidUsu" scope="row"></td>
                                        <td name="deleteNombreUsu" id="deleteNombreUsu" scope="row"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Eliminar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="contenido">
        <div class="card">
            <div class="card-body">
                <div class="tabla-empleados">
                    <table id="Usuarios-lista" class="shadow-sm table table-striped table-hover table-bordered">
                        <thead class="thead-dark">
                            <tr class="text-center">
                                <th style="display:none" scope="col">ID</th>
                                <th scope="col">Usuario</th>
                                <th scope="col">Nombre y Apellido</th>
                                <th scope="col">Rol</th>
                                <th scope="col">Estado</th>
                                <th scope="col">Fecha de Creación</th>
                                <th style="display:none">rol_id</th>
                                <th class="text-center" scope="col">Acciones</th>

                            </tr>
                        </thead>
                        <tbody id="empleados-lista">
                            <!-- Aquí se llena la tabla con los proveedores -->

                            <?php if ($usuarios): ?>
                                <?php foreach ($usuarios as $u) { ?>
                                    <tr>
                                        <td style="display:none"><?= (int)$u['id'] ?></td>
                                        <td><?= e($u['usuario']) ?></td>
                                        <td><?= e($u['nomyapellido']) ?></td>
                                        <td><?= e($rolesMap[(int)($u['rol'] ?? 0)] ?? '—') ?></td>
                                        <td class="text-center">
                                            <?php if (($u['estado'] ?? 'Activo') === 'Activo'): ?>
                                                <span class="badge bg-success">Activo</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactivo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= e($u['fecha_creacion']) ?></td>
                                        <td style="display:none"><?= (int)($u['rol'] ?? 0) ?></td>

                                        <td class="text-center">
                                            <button class="btn btn-success verUsuario" title="Consultar Usuario">
                                                <ion-icon name="eye-outline"></ion-icon>
                                            </button>
                                            <button class="btn btn-primary editUsuario" title="Editar Usuario">
                                                <ion-icon name="create-outline"></ion-icon>
                                            </button>
                                            <button class="btn btn-danger deleteUsuario" title="Eliminar Usuario">
                                                <ion-icon name="trash-outline"></ion-icon>
                                            </button>
                                        </td>
                                    <?php }  ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No existen usuarios para mostrar</td>
                                    </tr>
                                <?php endif; ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>


    <?php
    include_once("foot/foot.php");

    ?>