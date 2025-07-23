<?php
    include_once("head/head.php");
    require_once("../controlador/controladorroles.php");
    $obj = new controladorRoles();
    $roles = $obj->mostrarTodos();
?>

<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Administración de Roles</h1>
</div>
<div class="contenido-principal">

    <div class="encabezado-tabla">
        <div>
            <!-- <ion-icon name="add-outline"></ion-icon>
            <a href="nuevo_empleado.php">Registrar Materia Prima</a> -->
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#registrarUsuario">
                Agregar Rol
            </button>
        </div>
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

<!-- Modal Registrar Rol-->
<div class="modal fade" id="registrarUsuario" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Registrar Usuario</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formUsu" action="crearUsuario.php" method="post">
                        <div class="mb-3">
                            <label for="usuario" class="form-label">Usuario</label>
                            <input type="text" required class="form-control" name="usuario" id="usuario">
                        </div>
                        <div class="mb-3 ">
                            <div class="row">
                            <div class="col">
                                <label for="nombre_usu" class="form-label">Nombre</label>
                                <input type="text" class="form-control" name="nombre_usu" id="nombre_usu">
                            </div>
                            <div class="col">
                                <label for="apellido_usu" class="form-label">Apellido</label>
                                <input type="text" class="form-control" name="apellido_usu" id="apellido_usu">
                            </div>
                            </div>
                        </div>

                        <div class="mb-3">
                        <label for="rol" class="form-label">Rol</label>
                            <select class="form-select" name="rol" id="rol" required>
                                <option value=""></option>
                                <?php
                                    foreach ($roles as $rol) {
                                ?>
                                <option value="<?php echo $rol['id_rol']; ?>">
                                    <?php echo $rol['nombre_rol']; ?></option>
                                <?php
                                }
                                ?>
                            </select>
                            
                        </div>
                        <div class="mb-3">
                            <label for="contrasena" class="form-label">Contraseña</label>
                            <input type="password" class="form-control" name="contrasena" id="contrasena" required></select>
                            <div class="invalid-feedback">La contraseña es obligatoria.</div>
                        </div>
                        <div class="mb-3">
                            <label for="contrasena_conf" class="form-label">Confirmar Contraseña</label>
                            <input type="password" class="form-control" name="contrasena_conf" id="contrasena_conf" required>
                            <div class="invalid-feedback" id="passMismatchFeedback">Las contraseñas deben coincidir.</div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<!-- Modal Warning previo a borrar -->
<div class="modal" id="borrarUsuario" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar Proveedor</h5>
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
        <div class="tabla-empleados">
            <table id="Usuarios-lista" class="shadow-sm table table-striped table-hover table-bordered">
                <thead class="thead-dark">
                    <tr class="text-center">
                        <th scope="col">ID</th>
                        <th scope="col">Rol</th>
                        <th scope="col">Acciones</th>

                    </tr>
                </thead>
                <tbody id="empleados-lista">
                    <!-- Aquí se llena la tabla con los roles -->

                    <?php if($roles): ?>
                    <?php foreach ($roles as $rol){?>
                    <tr>
                        <td><?php echo $rol['id_rol'];?></td>
                        <td><?php echo $rol['nombre_rol'];?></td>
                                                
                        <td class="text-center">
                            <button class="btn btn-success verbtnproveed" title="Consultar Usuario">
                                <ion-icon name="eye-outline"></ion-icon>
                            </button>
                            <button class="btn btn-primary editbtnproveed" title="Editar Usuario">
                                <ion-icon name="create-outline"></ion-icon>
                            </button>
                            <button class="btn btn-danger deleteUsuario" title="Eliminar Usuario">
                                <ion-icon name="trash-outline"></ion-icon>
                            </button>
                        </td>
                        <?php }  ?>
                        <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No existen roles para mostrar</td>
                    </tr>
                    <?php endif; ?>

                </tbody>
            </table>
        </div>

    </div>


<?php 
    include_once("foot/foot.php");

?>