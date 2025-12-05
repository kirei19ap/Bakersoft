<?php
include_once("head/head.php");

require_once("../controlador/controladorEmpleado.php");
$ctrl = new ControladorEmpleado();

// Detectamos el ID de usuario desde la sesión (probamos varias claves por compatibilidad)
$usuarioId = $_SESSION['id_usuario'] ?? $_SESSION['user_id'] ?? $_SESSION['id'] ?? null;
$usuarioLogin = $_SESSION['user'] ?? null;

if ($usuarioId) {
  $emp = $ctrl->perfilEmpleado((int)$usuarioId);
} elseif (!empty($usuarioLogin)) {
  $emp = $ctrl->perfilEmpleadoPorLogin($usuarioLogin);
} else {
  // Sin ID ni login: fallback inmediato
  $emp = $ctrl->perfilEmpleado(null);
}

#var_dump($emp); // DEBUG
function esc($v)
{
  return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
}
function fecha($v)
{
  return $v ? date('d/m/Y', strtotime($v)) : '';
}
?>
<div class="titulo-contenido shadow-sm">
  <h1 class="display-5">Empleados</h1>
</div>

<div class="contenido-principal empleado-modulo">
  <div class="contenido">
    <div class="tabla-empleados">
      <?php if (!$emp['asociado']): ?>
        <div class="card border-warning mb-4" style="max-width: 1000px;">
          <div class="card-body">
            <h5 class="card-title text-warning">Usuario sin empleado vinculado</h5>
            <p class="card-text">
              Tu usuario <strong><?= esc($emp['user_login'] ?? '') ?></strong> no está asociado a ningún empleado.
              Por favor, comunicate con el encargado de <strong>RRHH</strong> para completar la vinculación y habilitar la solicitud de licencias.
            </p>
          </div>
        </div>
      <?php else: ?>

        <!-- Tarjeta: Mis Datos -->
        <div class="card mb-4" style="max-width: 1000px;">
          <div class="card-header bg-light">
            <strong>Mis datos</strong>
          </div>
          <div class="datos-grid" id="misDatosGrid">
            <div class="dato">
              <span class="lbl">Nombre y apellido</span>
              <span class="val"><?= esc(($emp['emp_nombre'] ?? '') . ' ' . ($emp['emp_apellido'] ?? '')) ?></span>
            </div>
            <div class="dato">
              <span class="lbl">DNI / CUIL</span>
              <span class="val"><?= esc(($emp['emp_dni'] ?? '') . ' / ' . ($emp['emp_cuil'] ?? '')) ?></span>
            </div>
            <div class="dato">
              <span class="lbl">Fecha de nacimiento</span>
              <span class="val"><?= esc(fecha($emp['emp_fecha_nac'] ?? null)) ?></span>
            </div>
            <div class="dato">
              <span class="lbl">Dirección</span>
              <span class="val"><?= esc($emp['emp_direccion'] ?? '') ?></span>
            </div>
            <div class="dato">
              <span class="lbl">Provincia / Localidad</span>
              <span class="val"><?= esc(($emp['emp_provincia'] ?? '') . ' / ' . ($emp['emp_localidad'] ?? '')) ?></span>
            </div>
            <div class="dato">
              <span class="lbl">Fecha de ingreso</span>
              <span class="val"><?= esc(fecha($emp['emp_fecha_ingreso'] ?? null)) ?></span>
            </div>
            <div class="dato">
              <span class="lbl">Puesto</span>
              <span class="val"><?= esc($emp['emp_puesto'] ?? '') ?></span>
            </div>
            <div class="dato">
              <span class="lbl">Estado</span>
              <span class="val"><?= esc($emp['emp_estado'] ?? '') ?></span>
            </div>
            <div class="dato">
              <span class="lbl">Email</span>
              <span class="val"><?= esc($emp['emp_email'] ?? '') ?></span>
            </div>
            <div class="dato">
              <span class="lbl">Teléfono</span>
              <span class="val"><?= esc($emp['emp_telefono'] ?? '') ?></span>
            </div>

          </div>

        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php include_once("foot/foot.php"); ?>

<script>
  // Deshabilitar opción de menú "Licencias" si el usuario no está asociado a un empleado
  (function() {
    const asociado = <?= $emp['asociado'] ? 'true' : 'false' ?>;
    if (!asociado) {
      document.addEventListener('DOMContentLoaded', function() {
        // Ideal: dar un id="menuLicencias" al item de la barra lateral
        let item = document.querySelector('#menuLicencias');
        if (!item) {
          // Fallback: buscamos por href o texto
          item = document.querySelector('a[href*="licencia"], a[href*="licencias"]') || null;
          if (!item) {
            const links = Array.from(document.querySelectorAll('.barra-lateral a, nav a'));
            item = links.find(a => /licencia/i.test(a.textContent || '')) || null;
          }
        }
        if (item) {
          item.classList.add('disabled');
          item.setAttribute('aria-disabled', 'true');
          item.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            if (typeof Swal !== 'undefined') {
              Swal.fire({
                icon: 'warning',
                title: 'Acceso no disponible',
                text: 'Tu usuario aún no está asociado a un empleado. Contactá a RRHH para habilitar Licencias.',
                confirmButtonText: 'Entendido'
              });
            } else {
              alert('Tu usuario no está asociado a un empleado. Contactá a RRHH para habilitar Licencias.');
            }
          }, {
            capture: true
          });
        }
      });
    }
  })();
</script>