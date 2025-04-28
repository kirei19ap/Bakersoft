<?php
    require_once("head/head.php");
    require_once("../controlador/controladorProveedores.php");
    $obj = new controladorproveedor();
    $filas = $obj->mostrarTodos();
?>
<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Registro de Proveedores</h1>
</div>
<div class="contenido-principal">

    <div class="encabezado-tabla">
        <div class="buscador">
            <input type="text" placeholder="Buscar...">
            <ion-icon name="search-outline"></ion-icon>
        </div>
        <div>
            <!-- <ion-icon name="add-outline"></ion-icon>
            <a href="nuevo_empleado.php">Registrar Materia Prima</a> -->
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#registrarProveedor">
                Registrar Proveedor
            </button>
        </div>
    </div>
    <div class=""><?php
            if (isset($_SESSION['error_valida_proveedor'])): ?>
                <script>
                    alert('<?php echo $_SESSION['error_valida_proveedor']; ?>');
                </script>
                <?php
                unset($_SESSION['error_valida_proveedor']);
                endif;
                ?>
            </div>
    <!-- Modal Registrar Proveedor-->
    <div class="modal fade" id="registrarProveedor" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Registrar Proeveedor</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="crearProveedor.php" method="post">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre del proveedor</label>
                            <input type="text" required class="form-control" name="nombre" id="nombre">
                        </div>
                        <div class="mb-3">
                            <label for="direccion" class="form-label">Dirección</label>
                            <input type="text" class="form-control" name="direccion" id="direccion">
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="text" class="form-control" name="email" id="email">
                        </div>
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" name="telefono" id="telefono">
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

    <!-- Modal Editar Materia Prima-->
    <div class="modal fade" id="editarProveedor" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Editar Proveedor</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="modificarProveedor.php" method="post">
                        <div class="mb-3">
                            <label for="editidProve" class="form-label">ID</label>
                            <input type="text" required readonly class="form-control" name="editidProve" id="editidProve">
                        </div>
                        <div class="mb-3">
                            <label for="editnombreProve" class="form-label">Nombre del proveedor</label>
                            <input type="text" required class="form-control" name="editnombreProve" id="editnombreProve">
                        </div>
                        <div class="mb-3">
                            <label for="editdireccionProve" class="form-label">Dirección</label>
                            <input type="text" class="form-control" name="editdireccionProve" id="editdireccionProve">
                        </div>
                        <div class="mb-3">
                            <label for="editemailProve" class="form-label">Email</label>
                            <input type="text" class="form-control" name="editemailProve" id="editemailProve">
                        </div>
                        <div class="mb-3">
                            <label for="edittelefonoProve" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" name="edittelefonoProve" id="edittelefonoProve">
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
    <div class="modal" id="borrarProveedor" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar Proveedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="borraProveedor.php" method="post">
                    <input type="text" hidden name="borrarProveedorId" id="borrarProveedorId">
                    <div class="modal-body">
                        <p>Esta seguro que desea eliminar el siguiente registro?</p>
                        <div class="table-responsive">
                            <table class="table table-primary">
                                <thead>
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Nombre</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Teléfono</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="">
                                        <td name="deleteidProve" id="deleteidProve" scope="row"></td>
                                        <td name="deleteNombreProve" id="deleteNombreProve" scope="row"></td>
                                        <td name="deletestemailProve" id="deletestemailProve" scope="row"></td>
                                        <td name="deletetelefProve" id="deletetelefProve" scope="row"></td>
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
            <table class="shadow-sm table table-striped table-hover table-bordered">
                <thead class="thead-dark">
                    <tr class="text-center">
                        <th scope="col">ID</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Dirección</th>
                        <th scope="col">Email</th>
                        <th scope="col">Teléfono</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody id="empleados-lista">
                    <!-- Aquí se llena la tabla con las materias primas -->

                    <?php if($filas): ?>
                    <?php foreach ($filas as $regprov){?>
                    <tr>
                        <td><?php echo $regprov['id_proveedor'];?></td>
                        <td><?php echo $regprov['nombre'];?></td>
                        <td><?php echo $regprov['direccion'];?></td>
                        <td><?php echo $regprov['email'];?></td>
                        <td><?php echo $regprov['telefono'];?></td>

                        <td class="text-center">
                            <button class="btn btn-primary editbtnproveed">
                                <ion-icon name="create-outline"></ion-icon>
                            </button>
                            <button class="btn btn-danger deletebtnProveed">
                                <ion-icon name="trash-outline"></ion-icon>
                            </button>
                        </td>
                        <?php }  ?>
                        <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center">No existen registros para mostrar</td>
                    </tr>
                    <?php endif; ?>

                </tbody>
            </table>
        </div>

    </div>


    <?php
    require_once("foot/foot.php")
?>