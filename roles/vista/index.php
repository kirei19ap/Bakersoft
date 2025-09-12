<?php
include_once("head/head.php");
require_once(__DIR__ . "/../../config/bd.php");
$pdo = (new bd())->conexion();

// Traer roles
$roles = $pdo->query("
  SELECT r.id_rol, r.nombre_rol, COUNT(u.id) AS cant_usuarios
  FROM roles r
  LEFT JOIN usuarios u ON u.rol = r.id_rol
  GROUP BY r.id_rol, r.nombre_rol
  ORDER BY r.nombre_rol
")->fetchAll(PDO::FETCH_ASSOC);


function e($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
?>

<div class="titulo-contenido shadow-sm">
  <h1 class="display-5">Administración de Roles</h1>
</div>

<div class="contenido-principal">

  <div class="encabezado-tabla d-flex justify-content-between align-items-center mb-2">
    <div></div>
    <div>
      <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#registrarRol">
        Registrar Rol
      </button>
    </div>
  </div>

  <!-- Flash (SweetAlert) -->
  <?php if (!empty($_SESSION['roles_msg'])): $msg = $_SESSION['roles_msg']; unset($_SESSION['roles_msg']); ?>
    <script>
      Swal.fire({ icon: 'success', title: 'OK', text: '<?= e($msg) ?>', confirmButtonText: 'Aceptar' });
    </script>
  <?php endif; ?>
  <?php if (!empty($_SESSION['roles_err'])): $msg = $_SESSION['roles_err']; unset($_SESSION['roles_err']); ?>
    <script>
      Swal.fire({ icon: 'error', title: 'Atención', text: '<?= e($msg) ?>', confirmButtonText: 'Aceptar' });
    </script>
  <?php endif; ?>

  <div class="contenido">
    <div class="tabla-empleados">
      <table id="Roles-lista" class="shadow-sm table table-striped table-hover table-bordered align-middle">
        <thead class="thead-dark">
          <tr class="text-center">
            <th style="display:none">ID</th>
            <th>Nombre del Rol</th>
            <th class="text-center">Usuarios</th>
            <th class="text-center">Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($roles): foreach ($roles as $r): ?>
            <tr>
              <td style="display:none"><?= (int)$r['id_rol'] ?></td>
              <td><?= e($r['nombre_rol']) ?></td>
              <td class="text-center"><?= (int)$r['cant_usuarios'] ?></td>
              <td class="text-center">
                <button class="btn btn-success verRol" title="Ver"><ion-icon name="eye-outline"></ion-icon></button>
                <button class="btn btn-primary editRol" title="Editar"><ion-icon name="create-outline"></ion-icon></button>
                <button class="btn btn-danger deleteRol" title="Eliminar"><ion-icon name="trash-outline"></ion-icon></button>
              </td>
            </tr>
          <?php endforeach; else: ?>
            <tr><td colspan="4" class="text-center">No existen roles para mostrar</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal: Registrar Rol -->
<div class="modal fade" id="registrarRol" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="crearRol.php" method="post" class="needs-validation" novalidate>
        <div class="modal-header">
          <h5 class="modal-title">Registrar Rol</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <label class="form-label">Nombre del rol</label>
          <input type="text" class="form-control" name="nombre_rol" id="new_nombre_rol" required>
          <div class="invalid-feedback">El nombre es obligatorio.</div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button class="btn btn-primary" type="submit">Guardar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Editar Rol -->
<div class="modal fade" id="editarRol" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="modificarRol.php" method="post" class="needs-validation" novalidate>
        <div class="modal-header">
          <h5 class="modal-title">Editar Rol</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_rol" id="edit_idrol">
          <label class="form-label">Nombre del rol</label>
          <input type="text" class="form-control" name="nombre_rol" id="edit_nombrerol" required>
          <div class="invalid-feedback">El nombre es obligatorio.</div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button class="btn btn-primary" type="submit">Actualizar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal: Ver Rol -->
<div class="modal fade" id="verRol" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Rol</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <label class="form-label">Nombre del rol</label>
        <input id="ver_nombrerol" class="form-control" readonly>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal: Borrar Rol -->
<div class="modal fade" id="borrarRol" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="borraRol.php" method="post">
        <div class="modal-header">
          <h5 class="modal-title">Eliminar Rol</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_rol" id="del_idrol">
          <p>¿Confirmás eliminar el rol <b id="del_nombrerol"></b>?</p>
          <small class="text-muted d-block">No se podrá eliminar si hay usuarios con este rol.</small>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button class="btn btn-danger" type="submit">Eliminar</button>
        </div>
      </form>
    </div>
  </div>
</div>



<?php include_once("foot/foot.php"); ?>
