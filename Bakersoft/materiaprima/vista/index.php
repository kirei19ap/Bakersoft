<?php
    require_once("head/head.php");
    require_once("../controlador/controladorMP.php");
    $obj = new controladorMP();
    $filas = $obj->mostrarTodos();
?>
<div class="titulo-contenido shadow-sm"><h1 class="display-5">Materia Prima</h1></div>
<div class="contenido-principal">
    
    <div class="encabezado-tabla">
        <div class="buscador">
            <input type="text" placeholder="Buscar...">
            <ion-icon name="search-outline"></ion-icon>
        </div>
        <div>
            <!-- <ion-icon name="add-outline"></ion-icon>
            <a href="nuevo_empleado.php">Registrar Materia Prima</a> -->
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#registrarMP">
                Registrar Materia Prima
            </button>
        </div>
    </div>

    <!-- Modal Registrar Materia Prima-->
    <div class="modal fade" id="registrarMP" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Registrar Materia Prima</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="crearMP.php" method="post">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre de Materia Prima</label>
                            <input type="text" required class="form-control" name="nombre" id="nombre">
                        </div>
                        <div class="mb-3">
                            <label for="stockminimo" class="form-label">Stock Mínimo</label>
                            <input type="number" class="form-control" name="stockminimo" id="stockminimo">
                        </div>
                        <div class="mb-3">
                            <label for="stockactual" class="form-label">Stock Actual</label>
                            <input type="number" class="form-control" name="stockactual" id="stockactual">
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
    <div class="modal fade" id="editarMP" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Editar Materia Prima</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="modificarMP.php" method="post">
                        <div class="mb-3">
                            <label for="editid" class="form-label">ID</label>
                            <input type="text" required readonly class="form-control" name="editid" id="editid">
                        </div>
                        <div class="mb-3">
                            <label for="editnombre" class="form-label">Nombre de Materia Prima</label>
                            <input type="text" required class="form-control" name="editnombre" id="editnombre">
                        </div>
                        <div class="mb-3">
                            <label for="editstockminimo" class="form-label">Stock Mínimo</label>
                            <input type="number" class="form-control" name="editstockminimo" id="editstockminimo">
                        </div>
                        <div class="mb-3">
                            <label for="editstockactual" class="form-label">Stock Actual</label>
                            <input type="number" class="form-control" name="editstockactual" id="editstockactual">
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
    <div class="modal" id="borrarMP" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Eliminar Materia Prima</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="borraMP.php" method="post">
                    <input type="text" hidden name="borrarID" id="borrarID">
                    <div class="modal-body">
                        <p>Esta seguro que desea eliminar el siguiente registro?</p>
                        <div class="table-responsive">
                            <table class="table table-primary">
                                <thead>
                                    <tr>
                                        <th scope="col">ID</th>
                                        <th scope="col">Nombre</th>
                                        <th scope="col">Stock Mínimo</th>
                                        <th scope="col">Stock Actual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="">
                                        <td name="deleteid" id="deleteid" scope="row"></td>
                                        <td name="deleteNombre" id="deleteNombre" scope="row"></td>
                                        <td name="deletestmin" id="deletestmin" scope="row"></td>
                                        <td name="deletestact" id="deletestact" scope="row"></td>
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
                        <th scope="col">Stock Mínimo</th>
                        <th scope="col">Stock Actual</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody id="empleados-lista">
                    <!-- Aquí se llena la tabla con las materias primas -->

                    <?php if($filas): ?>
                    <?php foreach ($filas as $regmp){?>
                    <tr>
                        <td><?php echo $regmp['id'];?></td>
                        <td><?php echo $regmp['nombre'];?></td>
                        <td><?php echo $regmp['stockminimo'];?></td>
                        <td><?php echo $regmp['stockactual'];?></td>

                        <td class="text-center">
                            <button class="btn btn-primary editbtn">
                                <ion-icon name="create-outline"></ion-icon>
                            </button>
                            <button class="btn btn-danger deletebtn">
                                <ion-icon name="trash-outline"></ion-icon>
                            </button>
                        </td>
                        <?php }  ?>
                        <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No existen registros</td>
                    </tr>
                    <?php endif; ?>

                </tbody>
            </table>
        </div>

    </div>


    <?php
    require_once("foot/foot.php")
?>
    <script>
    $(document).ready(function() {
        $('.editbtn').on('click', function() {
            $('#editarMP').modal('show');
            $tr = $(this).closest('tr');

            var datos = $tr.children("td").map(function() {
                return $(this).text();
            }).get();

            console.log(datos);

            $('#editid').val(datos[0]);
            $('#editnombre').val(datos[1]);
            $('#editstockminimo').val(datos[2]);
            $('#editstockactual').val(datos[3]);
        })
    });

    $(document).ready(function() {
        $('.deletebtn').on('click', function() {
            $('#borrarMP').modal('show');
            $tr = $(this).closest('tr');

            var datos = $tr.children("td").map(function() {
                return $(this).text();
            }).get();

            console.log(datos);
            $('#deleteid').text(datos[0]);
            $('#borrarID').val(datos[0]);
            $('#deleteNombre').text(datos[1]);
            $('#deletestmin').text(datos[2]);
            $('#deletestact').text(datos[3]);
        })
    });
    </script>