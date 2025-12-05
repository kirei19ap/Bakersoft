<?php
$currentPage = 'materiaprima';
include_once("../../includes/head_app.php");
require_once("../controlador/controladorMP.php");
$obj = new controladorMP();
$filas = $obj->mostrarTodos();
$cat = $obj->traerCategorias();
#var_dump($filas);
#var_dump($cat);
$proveedores = $obj->proveedoresTodos();


?>
<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Registro de Materia Prima</h1>
</div>
<div class="contenido-principal">

    <div class="encabezado-tabla">
        <div class="mb-3">
            <div id="contenedorCategoria" class="text-start">
                <label for="filtroCategoria" class="form-label mb-1">Filtrar por categoría:</label>
                <select id="filtroCategoria" class="form-select form-select-sm">
                    <option value="">-- Todas --</option>
                    <?php
                    foreach ($cat as $categoria) {
                    ?>
                        <option value="<?php echo $categoria['idCatMP']; ?>">
                            <?php echo $categoria['nombreCatMP']; ?></option>
                    <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        <div>
            <!-- <ion-icon name="add-outline"></ion-icon>
            <a href="nuevo_empleado.php">Registrar Materia Prima</a> -->
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#registrarMP">
                Registrar Materia Prima
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
                            <label for="nombre" required class="form-label">Nombre de Materia Prima</label>
                            <input type="text" required class="form-control" name="nombre" id="nombre">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Unidad de Medida</label><br>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="unidad_medida" id="unidad1"
                                    value="kg" required>
                                <label class="form-check-label" for="unidad1">Kilogramo (kg)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="unidad_medida" id="unidad2" value="l"
                                    required>
                                <label class="form-check-label" for="unidad2">Litro (L)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="unidad_medida" id="unidad3"
                                    value="un" required>
                                <label class="form-check-label" for="unidad3">Unidades (un)</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Catogoría</label><br>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="categoriaMP" id="cat1"
                                    value="1" required>
                                <label class="form-check-label" for="unidad1">Seco</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="categoriaMP" id="cat2" value="2"
                                    required>
                                <label class="form-check-label" for="unidad2">Humedo</label>
                            </div>
                        </div>
                        <div class="mb-3 form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="es_perecedero" name="es_perecedero" value="1">
                            <label class="form-check-label" for="es_perecedero">¿Es perecedero?</label>
                        </div>

                        <div class="mb-3">
                            <label for="fecha_vencimiento" class="form-label">Fecha de vencimiento</label>
                            <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" min="<?php echo date('Y-m-d'); ?>" disabled>
                            <div class="form-text">Obligatoria sólo si es perecedero.</div>
                        </div>

                        <script>
                            const chk = document.getElementById('es_perecedero');
                            const vto = document.getElementById('fecha_vencimiento');

                            function toggleVto() {
                                if (chk.checked) {
                                    vto.disabled = false;
                                    vto.required = true;
                                } else {
                                    vto.value = '';
                                    vto.required = false;
                                    vto.disabled = true;
                                }
                            }
                            chk.addEventListener('change', toggleVto);
                            // Si editás un registro ya perecedero:
                            document.addEventListener('DOMContentLoaded', toggleVto);
                        </script>
                        <div class="mb-3">
                            <label for="stockminimo" class="form-label">Stock Mínimo</label>
                            <input type="number" class="form-control" name="stockminimo" id="stockminimo">
                        </div>
                        <div class="mb-3">
                            <label for="stockactual" class="form-label">Stock Actual</label>
                            <input type="number" class="form-control" name="stockactual" id="stockactual">
                        </div>
                        <div class="mb-3">
                            <label for="proveedor" class="form-label">Proveedor</label>
                            <select class="form-select" name="proveedor" id="proveedor">
                                <option value="-1"></option>
                                <?php
                                foreach ($proveedores as $proveedor) {
                                ?>
                                    <option value="<?php echo $proveedor['id_proveedor']; ?>">
                                        <?php echo $proveedor['nombre']; ?></option>
                                <?php
                                }
                                ?>
                            </select>

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
                        <div class="mb-3 oculto">
                            <label for="editid" class="form-label">ID</label>
                            <input type="text" required readonly class="form-control" name="editid" id="editid">
                        </div>
                        <div class="mb-3">
                            <label for="editnombre" class="form-label">Nombre de Materia Prima</label>
                            <input type="text" required class="form-control" name="editnombre" id="editnombre">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Unidad de Medida</label><br>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="unidad_medida" id="unidad1"
                                    value="kg" required>
                                <label class="form-check-label" for="unidad1">Kilogramo (kg)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="unidad_medida" id="unidad2" value="l"
                                    required>
                                <label class="form-check-label" for="unidad2">Litro (L)</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="unidad_medida" id="unidad3"
                                    value="un" required>
                                <label class="form-check-label" for="unidad3">Unidades (un)</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editcategoria" class="form-label">Categoria</label>
                            <input type="text" required class="form-control" name="editcategoria" id="editcategoria" readonly>
                        </div>
                        <!-- <div class="mb-3 form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="edit_es_perecedero" name="edit_es_perecedero" value="1">
                            <label class="form-check-label" for="edit_es_perecedero">¿Es perecedero?</label>
                        </div> -->

                        <!-- Campo oculto que sigue enviando lo que espera el backend: no_perecedero (1 = NO perecedero, 0 = perecedero) -->
                        <input type="hidden" name="no_perecedero" id="edit_no_perecedero_hidden" value="1">

                        <div class="mb-3">
                            <div class="col-md-4">
                                <!-- Switch visible para el usuario: ON = Perecedero -->
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="edit_perecedero">
                                    <label class="form-check-label" for="edit_perecedero">Perecedero</label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="edit_fecha_vencimiento" class="form-label">Fecha de vencimiento</label>
                                <input type="date" class="form-control" id="edit_fecha_vencimiento" name="fecha_vencimiento" min="<?php echo date('Y-m-d'); ?>">
                                <div class="invalid-feedback">Obligatoria si es perecedero.</div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="editstockminimo" class="form-label">Stock Mínimo</label>
                            <input type="number" class="form-control" name="editstockminimo" id="editstockminimo">
                        </div>
                        <div class="mb-3">
                            <label for="editstockactual" class="form-label">Stock Actual</label>
                            <input type="number" class="form-control" name="editstockactual" id="editstockactual">
                        </div>
                        <div class="mb-3">
                            <label for="editMPproveedor" class="form-label">Proveedor</label>
                            <select class="form-select" name="editMPproveedor" id="editMPproveedor">
                                <option value="-1"></option>
                                <?php
                                foreach ($proveedores as $proveedor) {
                                ?>
                                    <option value="<?php echo $proveedor['id_proveedor']; ?>">
                                        <?php echo $proveedor['nombre']; ?></option>
                                <?php
                                }
                                ?>
                            </select>

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

    <!-- Modal Consultar Materia Prima-->
    <div class="modal fade" id="verMP" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Consultar Materia Prima</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 oculto">
                        <label for="verid" class="form-label">ID</label>
                        <input type="text" required readonly class="form-control" name="verid" id="verid">
                    </div>
                    <div class="mb-3">
                        <label for="vernombre" class="form-label">Nombre de Materia Prima</label>
                        <input type="text" required class="form-control" name="vernombre" id="vernombre" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="vercategoria" class="form-label">Categoria</label>
                        <input type="text" required class="form-control" name="vercategoria" id="vercategoria" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Condición</label>
                        <input type="text" class="form-control" id="ver_condicion" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha de vencimiento</label>
                        <input type="text" class="form-control" id="ver_fecha_venc" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="verstockminimo" class="form-label">Stock Mínimo</label>
                        <input type="text" class="form-control" name="verstockminimo" id="verstockminimo" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="verstockactual" class="form-label">Stock Actual</label>
                        <input type="text" class="form-control" name="verstockactual" id="verstockactual" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="verMPproveedor" class="form-label">Proveedor</label>
                        <select class="form-select" name="verMPproveedor" id="verMPproveedor" disabled>
                            <option value="-1"></option>
                            <?php
                            foreach ($proveedores as $proveedor) {
                            ?>
                                <option value="<?php echo $proveedor['id_proveedor']; ?>">
                                    <?php echo $proveedor['nombre']; ?></option>
                            <?php
                            }
                            ?>
                        </select>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    </div>
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
                                        <th hidden scope="col">ID</th>
                                        <th scope="col">Nombre</th>
                                        <th scope="col">Stock Mínimo</th>
                                        <th scope="col">Stock Actual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="">
                                        <td hidden name="deleteid" id="deleteid" scope="row"></td>
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
        <div class="card">
            <div class="card-body">
                <div class="tabla-empleados">
                    <table id="MP-lista" class="shadow-sm table table-striped table-hover table-bordered">
                        <thead class="thead-dark">
                            <tr class="text-center">
                                <th hidden scope="col">ID</th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Stock Mínimo</th>
                                <th scope="col">Stock Actual</th>
                                <th scope="col">Proveedor</th>
                                <th style="display:none" scope="col">ID Proveedor</th>
                                <th style="display:none" scope="col">Categoria</th>
                                <th style="display:none" scope="col">No Perecedero</th>
                                <th style="display:none" scope="col">Fecha Vencimiento</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Aquí se llena la tabla con las materias primas -->

                            <?php if ($filas): ?>
                                <?php foreach ($filas as $regmp) { ?>
                                    <tr>
                                        <td hidden><?php echo $regmp['id']; ?></td>
                                        <td><?php echo $regmp['nombre']; ?></td>
                                        <td><?php echo $regmp['stockminimo'] . ' ' . $regmp['unidad_medida']; ?></td>
                                        <td style="background-color: <?php if ($regmp['stockactual'] < $regmp['stockminimo']) {
                                                                            echo "#f55f5f";
                                                                        } else if ($regmp['stockactual'] == $regmp['stockminimo']) {
                                                                            echo "#ffff80";
                                                                        } else {
                                                                            echo "#a5d46a";
                                                                        }
                                                                        ?>"><?php echo $regmp['stockactual'] . ' ' . $regmp['unidad_medida']; ?></td>
                                        <td><?php $prove = $obj->consultaProveedor($regmp['proveedor']);
                                            echo $prove[0]['nombre'];
                                            ?></td>
                                        <td style="display:none"><?php echo $prove[0]['id_proveedor'] ?></td>
                                        <td style="display:none"><?php echo $regmp['categoriaMP']; ?>
                                        <td style="display:none"><?php echo isset($regmp['es_perecedero']) ? $regmp['es_perecedero'] : 1; ?></td>
                                        <td style="display:none"><?php echo !empty($regmp['fecha_vencimiento']) ? $regmp['fecha_vencimiento'] : ''; ?></td>
                                        <td class="text-center">
                                            <button class="btn btn-success verMPbtn" title="Consultar Materia Prima">
                                                <ion-icon name="eye-outline"></ion-icon>
                                            </button>
                                            <button class="btn btn-primary editbtn" title="Editar Materia Prima">
                                                <ion-icon name="create-outline"></ion-icon>
                                            </button>
                                            <button class="btn btn-danger deletebtn" title="Eliminar Materia Prima">
                                                <ion-icon name="trash-outline"></ion-icon>
                                            </button>
                                        </td>
                                    <?php }  ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">No existen registros para mostrar</td>
                                    </tr>
                                <?php endif; ?>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    <?php
    require_once("foot/foot.php")
    ?>