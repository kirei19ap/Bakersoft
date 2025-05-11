<?php
    include_once("head/head.php");
    require_once("../controlador/controladorusuarios.php");
    $obj = new controladorUsuario();
    $usuarios = $obj->mostrarTodos();
?>

<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Registro de Usuarios</h1>
</div>
<div class="contenido-principal">

    <div class="encabezado-tabla">
        <div>
            <!-- <ion-icon name="add-outline"></ion-icon>
            <a href="nuevo_empleado.php">Registrar Materia Prima</a> -->
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#registrarProveedor">
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

<div class="contenido">
        <div class="tabla-empleados">
            <table id="Usuarios-lista" class="shadow-sm table table-striped table-hover table-bordered">
                <thead class="thead-dark">
                    <tr class="text-center">
                        <th scope="col">ID</th>
                        <th scope="col">Usuario</th>
                        <th scope="col">Nombre y Apellido</th>
                        <th scope="col">Fecha de Creación</th>
                        <th scope="col">Acciones</th>

                    </tr>
                </thead>
                <tbody id="empleados-lista">
                    <!-- Aquí se llena la tabla con los proveedores -->

                    <?php if($usuarios): ?>
                    <?php foreach ($usuarios as $usuario){?>
                    <tr>
                        <td><?php echo $usuario['id'];?></td>
                        <td><?php echo $usuario['usuario'];?></td>
                        <td><?php echo $usuario['nomyapellido'];?></td>
                        <td><?php echo $usuario['fecha_creacion'];?></td>
                                                
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
                        <td colspan="6" class="text-center">No existen usuarios para mostrar</td>
                    </tr>
                    <?php endif; ?>

                </tbody>
            </table>
        </div>

    </div>


<?php 
    include_once("foot/foot.php");

?>