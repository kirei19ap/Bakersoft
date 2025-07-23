<?php
    require_once("head/head.php");
    require_once("../controlador/controladorProveedores.php");
    $obj = new controladorproveedor();
    $filas = $obj->mostrarTodos();
    $prov_all = $obj->mostrarProvincias();
?>
<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Registro de Proveedores</h1>
</div>
<div class="contenido-principal">

    <div class="encabezado-tabla">
        <div>
            <!-- <ion-icon name="add-outline"></ion-icon>
            <a href="nuevo_empleado.php">Registrar Materia Prima</a> -->
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#registrarProveedor">
                Registrar Proveedor
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
 
    
    <!-- Modal Registrar Proveedor-->
    <div class="modal fade" id="registrarProveedor" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Registrar Proveedor</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="crearProveedor.php" method="post">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre del proveedor</label>
                            <input type="text" required class="form-control" name="nombre" id="nombre">
                        </div>
                        <div class="mb-3 ">
                            <div class="row">
                                <div class="col">
                            <label for="Calle" class="form-label">Calle</label>
                            <input type="text" class="form-control" name="calle" id="calle">
                            </div>
                            <div class="col">
                            <label for="altura" class="form-label">Altura</label>
                            <input type="number" class="form-control" name="altura" id="altura">
                            </div>
                            </div>
                        </div>

                        <div class="mb-3">
                        <label for="provincia" class="form-label">Provincia</label>
                            <select class="form-select" name="provincia" id="provincia">
                                <option value="-1"></option>
                                <?php
                                    foreach ($prov_all as $provincia) {
                                ?>
                                <option value="<?php echo $provincia['id_provincia']; ?>">
                                    <?php echo $provincia['provincia']; ?></option>
                                <?php
                                }
                                ?>
                            </select>
                            
                        </div>
                        <div class="mb-3">
                            <label for="localidad" class="form-label">Localidad</label>
                            <select class="form-select" name="localidad" id="localidad"></select>
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

    <!-- Modal Editar Proveedor-->
    <div class="modal fade" id="editarProveedor" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Editar Proveedor</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="modificarProveedor.php" method="post">
                        <div class="mb-3 oculto">
                            <label for="editidProve" class="form-label">ID</label>
                            <input type="text" required readonly class="form-control" name="editidProve"
                                id="editidProve">
                        </div>
                        <div class="mb-3">
                            <label for="editnombreProve" class="form-label">Nombre del proveedor</label>
                            <input type="text" required class="form-control" name="editnombreProve"
                                id="editnombreProve">
                        </div>
                        <div class="mb-3 ">
                            <div class="row">
                                <div class="col">
                            <label for="editcalleprove" class="form-label">Calle</label>
                            <input type="text" class="form-control" name="editcalleprove" id="editcalleprove">
                            </div>
                            <div class="col">
                            <label for="editalturaprove" class="form-label">Altura</label>
                            <input type="number" class="form-control" name="editalturaprove" id="editalturaprove">
                            </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editprovProve" class="form-label">Provincia</label>
                            <select class="form-select" name="editprovProve" id="editprovProve">
                            <option value="-1"></option>
                                <?php
                                    foreach ($prov_all as $provincia) {
                                ?>
                                <option value="<?php echo $provincia['id_provincia']; ?>">
                                    <?php echo $provincia['provincia']; ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editlocprove" class="form-label">Localidad</label>
                            <select class="form-select" name="editlocprove" id="editlocprove"></select>
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


    <!-- Modal Consultar Proveedor-->
    <div class="modal fade" id="verProveedor" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Consultar Proveedor</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                        <div class="mb-3 oculto">
                            <label for="veridProve" class="form-label">ID</label>
                            <input type="text" required readonly class="form-control" name="veridProve"
                                id="veridProve">
                        </div>
                        <div class="mb-3">
                            <label for="vernombreProve" class="form-label">Nombre del proveedor</label>
                            <input type="text" required readonly class="form-control" name="vernombreProve"
                                id="vernombreProve">
                        </div>
                        <div class="mb-3 ">
                            <div class="row">
                                <div class="col">
                            <label for="vercalleprove" class="form-label">Calle</label>
                            <input type="text" readonly class="form-control" name="vercalleprove" id="vercalleprove">
                            </div>
                            <div class="col">
                            <label for="veralturaprove" class="form-label">Altura</label>
                            <input type="number" readonly class="form-control" name="veralturaprove" id="veralturaprove">
                            </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="verprovProve" class="form-label">Provincia</label>
                            <select class="form-select" name="verprovProve" id="verprovProve" disabled>
                            <option value="-1"></option>
                                <?php
                                    foreach ($prov_all as $provincia) {
                                ?>
                                <option value="<?php echo $provincia['id_provincia']; ?>">
                                    <?php echo $provincia['provincia']; ?></option>
                                <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="verlocprove" class="form-label">Localidad</label>
                            <select class="form-select" name="verlocprove" id="verlocprove" disabled></select>
                        </div>
                        <div class="mb-3">
                            <label for="veremailProve" class="form-label">Email</label>
                            <input type="text" readonly class="form-control" name="veremailProve" id="veremailProve">
                        </div>
                        <div class="mb-3">
                            <label for="vertelefonoProve" class="form-label">Teléfono</label>
                            <input type="text" readonly class="form-control" name="vertelefonoProve" id="vertelefonoProve">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        </div>
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
                                        <th hidden scope="col">ID</th>
                                        <th scope="col">Nombre</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Teléfono</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="">
                                        <td hidden name="deleteidProve" id="deleteidProve" scope="row"></td>
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
            <table id="Provedores-lista" class="shadow-sm table table-striped table-hover table-bordered">
                <thead class="thead-dark">
                    <tr class="text-center">
                        <th hidden scope="col">ID</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Dirección</th>
                        <th scope="col">Email</th>
                        <th scope="col">Teléfono</th>
                        <th scope="col">Acciones</th>
                        <th style="display:none;" scope="col">Provincia</th>
                        <th style="display:none;" scope="col">Localidad</th>
                    </tr>
                </thead>
                <tbody id="empleados-lista">
                    <!-- Aquí se llena la tabla con los proveedores -->

                    <?php if($filas): ?>
                    <?php foreach ($filas as $regprov){?>
                    <tr>
                        <td hidden><?php echo $regprov['id_proveedor'];?></td>
                        <td><?php echo $regprov['nombre'];?></td>
                        <td><?php echo $regprov['calle'].' '.$regprov['altura'];?></td>
                        <td><?php echo $regprov['email'];?></td>
                        <td><?php echo $regprov['telefono'];?></td>
                        <td style="display:none;"><?php echo $regprov['localidad'];?></td>
                        <td style="display:none;"><?php echo $regprov['provincia'];?></td>


                        <td class="text-center">
                            <button class="btn btn-success verbtnproveed" title="Consultar Proveedor">
                                <ion-icon name="eye-outline"></ion-icon>
                            </button>
                            <button class="btn btn-primary editbtnproveed" title="Editar Proveedor">
                                <ion-icon name="create-outline"></ion-icon>
                            </button>
                            <button class="btn btn-danger deletebtnProveed" title="Eliminar Proveedor">
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