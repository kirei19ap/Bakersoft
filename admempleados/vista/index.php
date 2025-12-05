<?php
include_once("../../includes/head_app.php");

require_once(__DIR__ . "/../controlador/controladoradmempleado.php");
$obj = new ControladorAdmEmpleado();

// Trae todo por Controlador (Modelo por debajo)
$filas      = $obj->listar();
#var_dump($filas);
$provincias = $obj->provincias();
$usuarios   = $obj->usuariosCombo();
$puestos    = $obj->traerPuesto();

// Mapas id=>nombre
$provMap = [];
foreach ($provincias as $p) {
    $provMap[(int)$p['id_provincia']] = $p['provincia'];
}

// Mapa id=>nombre para usuarios (elige la mejor etiqueta que tengas)
$usuariosMap = [];
foreach ($usuarios as $u) {
    $nomyap = trim($u['nomyapellido'] ?? '');
    $usuario = trim($u['usuario'] ?? '');
    // Si hay nombre/apellido, mostrar "Nombre Apellido (usuario)". Si NO, mostrar solo "usuario" (SIN paréntesis).
    $label = 'Usuario ' . $u['id'];
    $usuariosMap[(int)$u['id']] = $label;
}

$locMap = $obj->localidadesMap(); // id_localidad => nombre

?>
<script>
    window.EMP_USUARIOS = <?= json_encode($usuariosMap, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
</script>
<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Registro de empleados</h1>
</div>

<div class="contenido-principal">

    <!-- Botonera -->
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div></div>
        <div>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#nuevoEmpleado">
                Registrar Empleado
            </button>
        </div>
    </div>

    <!-- SweetAlerts de back -->
    <?php if (!empty($_SESSION['flash_error'])): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?= addslashes($_SESSION['flash_error']) ?>',
                confirmButtonText: 'Aceptar'
            });
        </script>
        <?php unset($_SESSION['flash_error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['flash_success'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'OK',
                text: '<?= addslashes($_SESSION['flash_success']) ?>',
                timer: 2000,
                showConfirmButton: false
            });
        </script>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <!-- Tabla -->
    <div class="contenido">
        <div class="tabla-empleados">
            <table id="Empleados-lista" class="shadow-sm table table-striped table-hover table-bordered">
                <thead>
                    <tr>
                        <th style="display:none">ID</th>
                        <th>Apellido, Nombre</th>
                        <th>DNI</th>
                        <th>Legajo</th>
                        <th>Genero</th>
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
                            <td style="display:none"><?= (int)$r['id_empleado'] ?></td>
                            <td><?= htmlspecialchars($r['apellido'] . ", " . $r['nombre']) ?></td>
                            <td><?= htmlspecialchars($r['dni']) ?></td>
                            <td><?= htmlspecialchars($r['legajo'] ?? '') ?></td>
                            <td><?= htmlspecialchars($r['sexo'] ?? '') ?></td>
                            <td><?= htmlspecialchars($r['descrPuesto']) ?></td>
                            <td><?= htmlspecialchars($r['fecha_ingreso']) ?></td>
                            <td>
                                <?php if ($r['estado'] === 'Activo'): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td style="display:none"><?= htmlspecialchars($r['email'] ?? '') ?></td>
                            <td style="display:none"><?= htmlspecialchars($r['telefono'] ?? '') ?></td>
                            <td style="display:none"><?= htmlspecialchars($r['direccion'] ?? '') ?></td>
                            <td style="display:none"><?= htmlspecialchars($r['estado']) ?></td>
                            <td style="display:none"><?= htmlspecialchars($r['usuario_id']) ?></td>
                            <td style="display:none"><?= htmlspecialchars($r['provincia']) ?></td>
                            <td style="display:none"><?= htmlspecialchars($locName) ?></td>
                            <td class="text-center">
                                <!-- Ver -->
                                <?php
                                $provNombre = $provMap[(int)$r['provincia']] ?? '';
                                //$locNombre  = $locMap[(int)$r['localidad']] ?? '';
                                $usuarioId    = (int)($r['usuario_id'] ?? 0);
                                $usuarioLabel = $r['usuario_label'] ?? '';
                                $puestoId     = (int)($r['puesto_id'] ?? $r['idPuesto'] ?? 0);
                                $puestoNombre = (string)($r['descrPuesto'] ?? '');
                                $estadoCivilId  = (int)($r['estado_civil_id'] ?? 0);
                                $estadoCivilTxt = (string)($r['estado_civil'] ?? '');
                                $provId   = (int)($r['provincia'] ?? $r['provincia_id'] ?? 0);
                                $locId    = (int)($r['localidad_id'] ?? $r['localidad'] ?? 0);               // ✅ ID
                                $locName  = (string)($r['localidad_nombre'] ?? ''); // ✅ Nombre (por si querés mostrar o usar de fallback)
                                ?>
                                <button type="button" class="btn btn-info btn-sm verEmpleado"
                                    data-bs-toggle="modal" data-bs-target="#verEmpleado"
                                    data-id="<?= (int)$r['id_empleado'] ?>"
                                    data-nombre="<?= htmlspecialchars($r['nombre']) ?>"
                                    data-apellido="<?= htmlspecialchars($r['apellido']) ?>"
                                    data-dni="<?= htmlspecialchars($r['dni']) ?>"
                                    data-sexo="<?= htmlspecialchars($r['sexo'] ?? '') ?>"
                                    data-fechanac="<?= htmlspecialchars($r['fecha_nac'] ?? '') ?>"
                                    data-cuil="<?= htmlspecialchars($r['cuil'] ?? '') ?>"
                                    data-legajo="<?= htmlspecialchars($r['legajo'] ?? '') ?>"
                                    data-fecha="<?= htmlspecialchars($r['fecha_ingreso']) ?>"
                                    data-estado="<?= htmlspecialchars($r['estado']) ?>"
                                    data-email="<?= htmlspecialchars($r['email'] ?? '') ?>"
                                    data-telefono="<?= htmlspecialchars($r['telefono'] ?? '') ?>"
                                    data-direccion="<?= htmlspecialchars($r['direccion'] ?? '') ?>"
                                    data-usuario="<?= $usuarioId ?>"
                                    data-usuario-nombre="<?= htmlspecialchars($usuarioLabel) ?>"
                                    data-provincia-nombre="<?= htmlspecialchars($provNombre) ?>"
                                    data-provincia="<?= $provId ?>"
                                    data-localidad="<?= $locId ?>"
                                    data-localidad-nombre="<?= htmlspecialchars($locName, ENT_QUOTES, 'UTF-8') ?>"
                                    data-puesto-id="<?= $puestoId ?>"
                                    data-puesto="<?= htmlspecialchars($puestoNombre) ?>"
                                    data-estado-civil-id="<?= $estadoCivilId ?: '' ?>"
                                    data-estado-civil="<?= htmlspecialchars($estadoCivilTxt, ENT_QUOTES, 'UTF-8') ?>"

                                    title="Ver">
                                    <ion-icon name="eye-outline"></ion-icon>

                                </button>



                                <!-- Editar -->
                                <button type="button" class="btn btn-primary btn-sm editEmpleado"
                                    data-bs-toggle="modal" data-bs-target="#editarEmpleado"
                                    data-id="<?= (int)$r['id_empleado'] ?>"
                                    data-nombre="<?= htmlspecialchars($r['nombre']) ?>"
                                    data-apellido="<?= htmlspecialchars($r['apellido']) ?>"
                                    data-dni="<?= htmlspecialchars($r['dni']) ?>"
                                    data-sexo="<?= htmlspecialchars($r['sexo'] ?? '') ?>"
                                    data-fechanac="<?= htmlspecialchars($r['fecha_nac'] ?? '') ?>"
                                    data-cuil="<?= htmlspecialchars($r['cuil'] ?? '') ?>"
                                    data-legajo="<?= htmlspecialchars($r['legajo'] ?? '') ?>"
                                    data-fecha="<?= htmlspecialchars($r['fecha_ingreso']) ?>"
                                    data-estado="<?= htmlspecialchars($r['estado']) ?>"
                                    data-email="<?= htmlspecialchars($r['email'] ?? '') ?>"
                                    data-telefono="<?= htmlspecialchars($r['telefono'] ?? '') ?>"
                                    data-direccion="<?= htmlspecialchars($r['direccion'] ?? '') ?>"
                                    data-usuario="<?= $usuarioId ?>"
                                    data-usuario-nombre="<?= htmlspecialchars($usuarioLabel) ?>"
                                    data-provincia="<?= (int)$r['provincia'] ?>"
                                    data-localidad="<?= $locId ?>"
                                    data-localidad-nombre="<?= htmlspecialchars($locName) ?>"
                                    data-puesto-id="<?= $puestoId ?>"
                                    data-puesto="<?= htmlspecialchars($puestoNombre) ?>"
                                    data-estado-civil-id="<?= $estadoCivilId ?: '' ?>"
                                    data-estado-civil="<?= htmlspecialchars($estadoCivilTxt, ENT_QUOTES, 'UTF-8') ?>"

                                    title="Editar">
                                    <ion-icon name="create-outline"></ion-icon>

                                </button>


                                <!-- Activar / Inactivar -->
                                <?php
                                $esActivo = ($r['estado'] === 'Activo');
                                $btnClase = $esActivo ? 'btn-danger' : 'btn-warning';
                                $icono    = $esActivo ? 'person-remove-outline' : 'person-add-outline';
                                $btnTexto = $esActivo ? 'Inactivar' : 'Activar'; // accesibilidad
                                ?>
                                <button class="btn <?= $btnClase ?> btn-sm btnToggleEstado"
                                    data-id="<?= (int)$r['id_empleado'] ?>"
                                    data-apynom="<?= htmlspecialchars($r['apellido'] . ', ' . $r['nombre']) ?>"
                                    data-estado="<?= htmlspecialchars($r['estado']) ?>"
                                    data-bs-toggle="modal" data-bs-target="#cambiarEstadoModal"
                                    title="<?= $btnTexto ?>">
                                    <ion-icon name="<?= $icono ?>" aria-hidden="true"></ion-icon>
                                    <span class="visually-hidden"><?= $btnTexto ?></span>
                                </button>
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
                        <h6 class="text-secondary mb-2">Datos personales</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre</label>
                                <input name="nombre" class="form-control" required>
                                <div class="invalid-feedback">El nombre es obligatorio.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Apellido</label>
                                <input name="apellido" class="form-control" required>
                                <div class="invalid-feedback">El apellido es obligatorio.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">DNI</label>
                                <input name="dni" class="form-control" required>
                                <div class="invalid-feedback">El DNI es obligatorio.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">CUIL</label>
                                <input name="cuil" class="form-control" inputmode="numeric" maxlength="13" placeholder="##-########-#">
                                <div class="form-text">Se aceptan con o sin guiones.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Género</label>
                                <select name="sexo" class="form-select">
                                    <option value="">-- Seleccioná --</option>
                                    <option>Masculino</option>
                                    <option>Femenino</option>
                                    <option>Otro</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Estado civil</label>
                                <select id="emp_estado_civil" name="id_estado_civil" class="form-select"><!-- crear -->
                                    <option value="">-- Seleccionar --</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha de nacimiento</label>
                                <input type="date" name="fecha_nac" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Provincia</label>
                                <select id="emp_provincia" name="provincia" class="form-select" required>
                                    <option value="">-- Seleccioná una provincia--</option>
                                    <?php foreach ($provincias as $p): ?>
                                        <option value="<?= (int)$p['id_provincia'] ?>"><?= htmlspecialchars($p['provincia']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Seleccioná una provincia.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Localidad</label>
                                <select id="emp_localidad" name="localidad" class="form-select" required>
                                    <option value="">-- Seleccioná provincia primero --</option>
                                </select>
                                <div class="invalid-feedback">Seleccioná una localidad.</div>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Dirección</label>
                                <input name="direccion" class="form-control">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control" placeholder="nombre@dominio.com">
                                <div class="invalid-feedback">Ingresá un correo válido.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teléfono Particular</label>
                                <input name="telefono" class="form-control">
                            </div>

                            <!-- DATOS LABORALES -->
                            <hr class="my-4">
                            <h6 class="text-secondary mb-2">Datos laborales</h6>
                            <div class="col-md-4">
                                <label class="form-label">Legajo</label>
                                <input name="legajo" class="form-control" maxlength="20" required>
                                <div class="invalid-feedback">El legajo es obligatorio.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha ingreso</label>
                                <input type="date" name="fecha_ingreso" class="form-control" required>
                                <div class="invalid-feedback">La fecha de ingreso es obligatoria.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Puesto</label>
                                <select name="puesto" id="emp_puesto" class="form-select" required>
                                    <option value="">-- Seleccioná un puesto--</option>
                                    <?php foreach ($puestos as $p):
                                        $id    = (int)($p['idPuesto']);
                                        $label = (string)($p['descrPuesto']);
                                    ?>
                                        <option value="<?= $id ?>"><?= htmlspecialchars($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Seleccioná un puesto.</div>
                            </div>


                            <div class="col-md-6">
                                <label class="form-label">Estado</label>
                                <select name="estado" class="form-select">
                                    <option value="Activo" selected>Activo</option>
                                    <option value="Inactivo">Inactivo</option>
                                </select>
                            </div>



                            <div class="col-6">
                                <label class="form-label">Usuario vinculado (opcional)</label>
                                <select name="usuario_id" class="form-select">
                                    <option value="">-- Sin usuario --</option>
                                    <?php foreach ($usuarios as $u): ?>
                                        <option value="<?= (int)$u['id'] ?>"><?= htmlspecialchars($u['usuario']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
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
                                <div class="invalid-feedback">El nombre es obligatorio.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Apellido</label>
                                <input name="apellido" id="edit_apellido" class="form-control" required>
                                <div class="invalid-feedback">El apellido es obligatorio.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">DNI</label>
                                <!-- readonly: se envía en el POST pero no es editable -->
                                <input name="dni" id="edit_dni" class="form-control" required readonly>
                                <div class="form-text">El DNI no puede modificarse.</div>
                                <div class="invalid-feedback">El DNI es obligatorio.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Género</label>
                                <select name="sexo" id="edit_sexo" class="form-select">
                                    <option value="">-- Seleccioná --</option>
                                    <option>Masculino</option>
                                    <option>Femenino</option>
                                    <option>Otro</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Estado civil</label>
                                <select id="edit_estado_civil" name="id_estado_civil" class="form-select">
                                    <option value="">-- Seleccionar --</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fecha de nacimiento</label>
                                <input type="date" name="fecha_nac" id="edit_fecha_nac" class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">CUIL</label>
                                <input name="cuil" id="edit_cuil" class="form-control" inputmode="numeric" maxlength="13" placeholder="##-########-#">
                                <div class="form-text">Se guardará con 11 dígitos.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Legajo</label>
                                <input name="legajo" id="edit_legajo" class="form-control" maxlength="20" readonly>
                                <div class="form-text">El lejago no puede modificarse.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Fecha ingreso</label>
                                <input type="date" name="fecha_ingreso" id="edit_fecha_ingreso" class="form-control" required>
                                <div class="invalid-feedback">La fecha de ingreso es obligatoria.</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Puesto</label>
                                <select name="puesto" id="edit_puesto" class="form-select" required>
                                    <option value="">-- Seleccioná --</option>
                                    <?php foreach ($puestos as $p):
                                        $id    = (int)($p['idPuesto']);
                                        $label = (string)($p['descrPuesto']);
                                    ?>
                                        <option value="<?= $id ?>"><?= htmlspecialchars($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Seleccioná un puesto.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Provincia</label>
                                <select id="edit_emp_provincia" name="provincia" class="form-select" required>
                                    <option value="">-- Seleccioná --</option>
                                    <?php foreach ($provincias as $p): ?>
                                        <option value="<?= (int)$p['id_provincia'] ?>"><?= htmlspecialchars($p['provincia']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Seleccioná una provincia.</div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Localidad</label>
                                <select id="edit_emp_localidad" name="localidad" class="form-select" required
                                    data-selected="">
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
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" id="edit_email" class="form-control" placeholder="nombre@dominio.com">
                                <div class="invalid-feedback">Ingresá un correo válido.</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Teléfono Particular</label>
                                <input name="telefono" id="edit_telefono" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Usuario vinculado (opcional)</label>
                                <select name="usuario_id" id="edit_usuario_id" class="form-select">
                                    <option value="">-- Sin usuario --</option>
                                    <?php foreach ($usuarios as $u): ?>
                                        <option value="<?= (int)$u['id'] ?>"><?= htmlspecialchars($u['usuario']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                        <button class="btn btn-primary" type="submit">Guardar cambios</button>
                    </div>
                </form>
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
                            <label class="form-label">Estado civil</label>
                            <input class="form-control" data-ver="estado_civil" readonly>
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
                            <!-- Si querés mantener el valor en texto plano además del badge, dejá esta línea: -->
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

                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal: Cambiar estado -->
    <div class="modal fade" id="cambiarEstadoModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="cambiarEstado.php" method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cambiar estado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Vas a <b id="txt_accion_estado"></b> al empleado <b id="estado_apynom"></b> (ID <span id="estado_id"></span>).</p>
                    <input type="hidden" name="id_empleado" id="estado_id_input">
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary" type="submit">Confirmar</button>
                </div>
            </form>
        </div>
    </div>

</div>

<?php require_once("foot/foot.php"); ?>