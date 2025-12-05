<?php
include_once("head/head.php");
require_once("../controlador/controladorLicencias.php");
$ctrl = new ControladorLicencias();
$datos = $ctrl->datosIniciales();
function esc($v)
{
  return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
}
function fecha($v)
{
  return $v ? date('d/m/Y', strtotime($v)) : '';
}
?>

<style>
  /* Scoped al módulo Licencias */
  .licencias-modulo .card {
    width: 100%;
    max-width: 1100px;
    margin-left: auto;
    margin-right: auto;
    padding-bottom: 16px;
  }

  .licencias-modulo .table {
    width: 100%;
  }

  .licencias-modulo .resumen-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 10px;
  }

  .licencias-modulo .res-item {
    border: 1px solid #eee;
    border-radius: 8px;
    padding: 10px 12px;
    background: #fff;
    display: flex;
    flex-direction: column;
  }

  .licencias-modulo .res-item .lbl {
    font-weight: 600;
    color: #555;
  }

  .licencias-modulo .res-item .val {
    font-size: 1.1rem;
  }

  @media (max-width: 768px) {
    .licencias-modulo .resumen-grid {
      grid-template-columns: 1fr 1fr;
    }
  }

  /* Botón en esquina inferior derecha del card resumen */
  .licencias-modulo .resumen-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 10px;
  }

  /* Modal */
  #modalNuevaLicencia .form-label {
    font-weight: 600;
  }

  /* --- Modal Ver: más ancho y mejor lectura --- */
  #modalVer .modal-dialog {
    max-width: 920px;
  }

  #modalVer .card {
    border-radius: .75rem;
  }

  #modalVer .card h6 {
    letter-spacing: .04em;
    font-weight: 600;
  }

  #modalVer dl.row>dt {
    font-weight: 600;
  }

  #modalVer dl.row>dd {
    margin-bottom: .35rem;
  }

  /* En el modal, que el contenido largo envuelva en varias líneas (no se corte) */
  #modalVer dd,
  #modalVer .list-group-item {
    white-space: normal;
    word-break: break-word;
  }

  #tablaSolicitudes {
    width: 100% !important;
    table-layout: auto;
  }

  .dataTables_wrapper .dataTables_scrollHead table,
  .dataTables_wrapper .dataTables_scrollBody table {
    width: 100% !important;
  }

  .mini-card {
    border: 1px solid rgba(0, 0, 0, .06);
    border-radius: .75rem;
    background: var(--bs-body-bg);
  }

  .mini-card .title {
    font-size: 1.05rem;
    /* antes ~.85rem */
    font-weight: 600;
    color: var(--bs-secondary-color);
    text-align: center;
    line-height: 1;
  }

  .mini-card .value {
    font-weight: 500;
    font-size: 1.5rem;
    /* antes ~1.35rem */
    line-height: 1;
    text-align: center;
    margin-top: .45rem;
  }

  .mini-card .badge {
    font-size: .95rem;
    /* badge un toque más grande */
    padding: .35rem .65rem;
    vertical-align: middle;
  }

  /* Ajustes en pantallas medianas+ */
  @media (min-width:768px) {
    .mini-card {
      padding: 1rem;
    }

    .mini-card .title {
      font-size: 1.1rem;
    }

    .mini-card .value {
      font-size: 1.85rem;
    }

    .mini-card .badge {
      font-size: 1rem;
    }

    .dt-empty {
      text-align: center !important;
    }
  }
</style>
</style>

<div class="titulo-contenido shadow-sm">
  <h1 class="display-5">Licencias</h1>
</div>

<div class="contenido-principal licencias-modulo">

  <?php if (empty($datos['asociado'])): ?>
    <div class="card border-warning mb-4">
      <div class="card-body">
        <h5 class="card-title text-warning">Usuario sin empleado vinculado</h5>
        <p>Tu usuario no está asociado a un empleado. Contactá a RRHH.</p>
      </div>
    </div>
  <?php else: ?>
    <div class="contenido">
      <!-- Resumen -->
      <?php
      $r = $datos['resumen'] ?? [];
      $anio   = (int)($r['anio'] ?? date('Y'));
      $ant    = (int)($r['antiguedad_anios'] ?? 0);
      $corr   = (int)($r['dias_correspondientes'] ?? 0);

      $tomVac = (int)($r['tomados_vacaciones'] ?? ($r['dias_tomados'] ?? 0)); // compatibilidad
      $tot    = (int)($r['total_tomados'] ?? $tomVac);
      $otras  = (int)($r['tomados_otras'] ?? max(0, $tot - $tomVac));

      $pend   = (int)($r['dias_pendientes'] ?? 0);
      $restV  = (int)($r['dias_restantes_vac'] ?? max(0, $corr - $tomVac - $pend)); // restante real de vacaciones
      $restG  = (int)($r['dias_restantes'] ?? max(0, $corr - (int)($r['dias_tomados'] ?? 0) - $pend)); // compat
      ?>
      <div class="card mb-3">
        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
          <strong>Resumen de licencias (<?= $anio ?>)</strong>
          <span class="badge rounded-pill text-bg-info">Antigüedad: <?= $ant ?> año<?= $ant === 1 ? '' : 's' ?></span>
        </div>

        <div class="card-body py-3">
          <div class="row g-3">
            <!-- Fila 1 -->
            <div class="col-12 col-md-4">
              <div class="mini-card">
                <div class="title">Vacaciones correspondientes</div>
                <div class="value"><?= $corr ?> Dias</div>
              </div>
            </div>
            <div class="col-12 col-md-4">
              <div class="mini-card">
                <div class="title">Vacaciones disponibles</div>
                <div class="value"><span class="badge text-bg-success px-3 py-2"><?= $restV ?></span></div>
              </div>
            </div>
            <div class="col-12 col-md-4">
              <div class="mini-card">
                <div class="title">Pendientes (solicitados)</div>
                <div class="value"><?= $pend ?></div>
              </div>
            </div>


            <!-- Fila 2 -->
            <div class="col-12 col-md-4">
              <div class="mini-card">
                <div class="title">Otras licencias solicitadas</div>
                <div class="value"><span class="badge text-bg-info px-3 py-2"><?= $otras ?></span></div>
              </div>
            </div>
            <div class="col-12 col-md-4">
              <div class="mini-card">
                <div class="title">Vacaciones tomadas</div>
                <div class="value"><span class="badge text-bg-primary px-3 py-2"><?= $tomVac ?></span></div>
              </div>
            </div>

            <div class="col-12 col-md-4">
              <div class="mini-card">
                <div class="title">Aprobados (tomados)</div>
                <div class="value"><span class="badge text-bg-secondary px-3 py-2"><?= $tot ?></span></div>
              </div>
            </div>
          </div>

          <div class="mt-3 d-flex justify-content-end">
            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaLicencia">
              <ion-icon name="add-circle-outline"></ion-icon> Solicitar licencia
            </button>
          </div>
        </div>
      </div>
      <div class="tabla-empleados">

        <!-- Mis solicitudes -->
        <div class="card mb-4">
          <div class="card-header bg-light"><strong>Mis solicitudes</strong></div>
          <div class="card-body table-responsive">
            <table class="table table-striped table-sm align-middle" id="tablaSolicitudes">
              <thead class="table-light">
                <tr>
                  <th hidden>#</th>
                  <th>Fecha solicitud</th>
                  <th>Tipo</th>
                  <th>Estado</th>
                  <th>Inicio</th>
                  <th>Fin</th>
                  <th>Días</th>
                  <th>Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($datos['solicitudes'])): ?>
                  <?php foreach ($datos['solicitudes'] as $i => $s): ?>
                    <tr>
                      <td hidden><?= (int)$s['id_licencia'] ?></td>
                      <td><?= esc(fecha($s['fecha_solicitud'])) ?></td>
                      <td><?= esc($s['tipo']) ?></td>
                      <td><?= esc($s['estado']) ?></td>
                      <td><?= esc(fecha($s['fecha_inicio'])) ?></td>
                      <td><?= esc(fecha($s['fecha_fin'])) ?></td>
                      <td><?= (int)$s['cantidad_dias'] ?></td>
                      <td>
                        <div class="" role="group" aria-label="Acciones">
                          <!-- Ver: siempre habilitado -->
                          <button type="button" class="btn btn-success btnVer" data-id="<?= (int)$s['id_licencia'] ?>" title="Ver solicitud">
                            <ion-icon name="eye-outline"></ion-icon>
                          </button>

                          <?php
                          $estadoId   = isset($s['id_estado']) ? (int)$s['id_estado'] : 0;
                          $porId      = in_array($estadoId, [2, 3], true);
                          $estadoTxt  = strtolower(trim($s['estado'] ?? ''));
                          $porTexto   = in_array($estadoTxt, ['pendiente de envío', 'pendiente de aprobación'], true);
                          $habilitado = $porId || $porTexto; // 2 o 3
                          ?>

                          <!-- Editar: visible siempre; disabled si no corresponde -->
                          <button type="button"
                            class="btn btn-primary btnEditar"
                            data-id="<?= (int)$s['id_licencia'] ?>"
                            title="<?= $habilitado ? 'Editar solicitud' : 'Solo editable en pendiente de envío o aprobación' ?>"
                            <?= $habilitado ? '' : 'disabled aria-disabled="true"' ?>>
                            <ion-icon name="create-outline"></ion-icon>
                          </button>

                          <!-- Cancelar: visible siempre; disabled si no corresponde -->
                          <button type="button"
                            class="btn btn-danger btnCancelar"
                            data-id="<?= (int)$s['id_licencia'] ?>"
                            title="<?= $habilitado ? 'Cancelar solicitud' : 'Solo cancelable en pendiente de envío o aprobación' ?>"
                            <?= $habilitado ? '' : 'disabled aria-disabled="true"' ?>>
                            <ion-icon name="trash-outline"></ion-icon>
                          </button>
                        </div>
                      </td>


                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>
<!-- Modal Nueva Licencia -->
<div class="modal fade" id="modalNuevaLicencia" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Nueva licencia</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <form id="formLicencia">
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label">Fecha de inicio</label>
              <input type="date" name="fecha_inicio" class="form-control" required>
              <div class="form-text">No puede ser más de 7 días en el pasado.</div>
            </div>
            <div class="col-md-4">
              <label class="form-label">Tipo de licencia</label>
              <select name="id_tipo" class="form-select" required>
                <option value="">Seleccione...</option>
                <?php foreach (($datos['tipos'] ?? []) as $t): ?>
                  <option value="<?= (int)$t['id_tipo'] ?>">
                    <?= esc($t['descripcion']) ?><?= !empty($t['impacta_banco_vacaciones']) && (int)$t['impacta_banco_vacaciones'] === 0 ? ' (no descuenta)' : '' ?>
                  </option>
                <?php endforeach; ?>

              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label">Cantidad de días</label>
              <input type="number" name="cantidad_dias" class="form-control" min="1" value="1" required>
            </div>
            <div class="col-12">
              <label class="form-label">Observaciones (máx. 200)</label>
              <textarea name="observaciones" class="form-control" maxlength="200" rows="3" placeholder="Texto libre (opcional)"></textarea>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-warning" id="btnGuardarBorrador">Guardar como borrador</button>
        <button type="button" class="btn btn-primary" id="btnEnviar">Enviar a aprobación</button>
      </div>
    </div>
  </div>
</div>

<!-- Modal Ver Detalle -->
<div class="modal fade" id="modalVer" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Detalle de solicitud</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="verBody"></div>
      </div>
      <div class="modal-footer" id="verActions"></div>
    </div>
  </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formEditar" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Editar solicitud</h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_licencia" id="ed_id_licencia">
        <div class="mb-3">
          <label class="form-label">Tipo de licencia</label>
          <select class="form-select" name="id_tipo" id="ed_id_tipo" required>
            <!-- rellenar con tus tipos_licencia (podés usar los mismos que cargas en el form de alta) -->
          </select>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Desde</label>
            <input type="date" class="form-control" name="fecha_inicio" id="ed_fecha_inicio" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Hasta</label>
            <input type="date" class="form-control" name="fecha_fin" id="ed_fecha_fin" required>
          </div>
        </div>
        <div class="mt-3">
          <label class="form-label">Días</label>
          <input type="number" class="form-control" name="cantidad_dias" id="ed_cantidad_dias" min="1" readonly>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-primary">Guardar cambios</button>
      </div>
    </form>
  </div>
</div>


<?php include_once("foot/foot.php"); ?>

<script>
  (function() {
    const form = document.getElementById('formLicencia');
    const btnBorrador = document.getElementById('btnGuardarBorrador');
    const btnEnviar = document.getElementById('btnEnviar');
    const tabla = document.getElementById('tablaSolicitudes');
    const modalEditar = new bootstrap.Modal(document.getElementById('modalEditar'));
    const formEditar = document.getElementById('formEditar');
    const edId = document.getElementById('ed_id_licencia');
    const edTipo = document.getElementById('ed_id_tipo');
    const edIni = document.getElementById('ed_fecha_inicio');
    const edFin = document.getElementById('ed_fecha_fin');
    const edDias = document.getElementById('ed_cantidad_dias');

    // --- NUEVO: refs del modal Ver ---
    const modalVer = new bootstrap.Modal(document.getElementById('modalVer'));
    const verBody = document.getElementById('verBody');
    const verActions = document.getElementById('verActions');

    function makeBtn(label, cls, onClick) {
      const b = document.createElement('button');
      b.type = 'button';
      b.className = 'btn ' + cls;
      b.textContent = label;
      b.onclick = onClick;
      return b;
    }

    function postAccion(accion, payload) {
      const fd = new FormData();
      fd.append('accion', accion);
      for (const k in payload) fd.append(k, payload[k]);

      // calcula ruta de forma robusta desde esta vista
      const URL_CTRL = "<?php echo rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/../controlador/controladorLicencias.php'; ?>";

      return fetch('../controlador/controladorLicencias.php', {
          method: 'POST',
          body: fd
        })
        .then(async (r) => {
          const txt = await r.text();
          try {
            return JSON.parse(txt);
          } catch (e) {
            console.error('Respuesta no JSON:', txt);
            Swal.fire('Error', 'Respuesta no válida del servidor.\n' + txt.slice(0, 300), 'error');
            return {
              ok: false,
              msg: 'Respuesta no JSON',
              raw: txt
            };
          }
        });
    }

    // Cache en memoria para no pedirlos cada vez
    let TIPOS_LICENCIAS = null;
    async function cargarTiposLicencias() {
      if (Array.isArray(TIPOS_LICENCIAS) && TIPOS_LICENCIAS.length) return TIPOS_LICENCIAS;
      const r = await postAccion('datos', {});
      const tipos = r?.tipos || r?.data?.tipos || r?.combos?.tipos || r?.combos?.tipos_licencia || [];
      if (!tipos.length) throw new Error('No se pudieron cargar los tipos');
      TIPOS_LICENCIAS = tipos;
      return TIPOS_LICENCIAS;
    }

    function renderTiposEnSelect(selectEl, tipos, selectedId) {
      selectEl.innerHTML = tipos.map(t => `
    <option value="${t.id_tipo}" ${String(t.id_tipo)===String(selectedId)?'selected':''}>
      ${t.descripcion}
    </option>`).join('');
    }


    // 1) Click en Editar: cargar detalle y abrir modal
    // Abrir modal Editar
    // Abrir modal Editar (solo si el botón no está disabled)
    document.getElementById('tablaSolicitudes')?.addEventListener('click', async (e) => {
      const btn = e.target.closest('.btnEditar');
      if (!btn) return;
      if (btn.hasAttribute('disabled')) return;

      const id = btn.getAttribute('data-id');
      const r = await postAccion('detalle', {
        id_licencia: id
      });
      if (!r?.ok) {
        Swal.fire('Error', r?.msg || 'No se pudo obtener el detalle.', 'error');
        return;
      }

      const d = r.data; // trae id_tipo e id_estado desde el backend
      edId.value = d.id_licencia;
      edIni.value = d.fecha_inicio || '';
      edFin.value = d.fecha_fin || '';
      edDias.value = d.cantidad_dias || '';

      try {
        const tipos = await cargarTiposLicencias();
        renderTiposEnSelect(edTipo, tipos, d.id_tipo);
      } catch (err) {
        console.error(err);
        Swal.fire('Error', 'No se pudieron cargar los tipos de licencia.', 'error');
        return;
      }

      modalEditar.show();
    });



    // 2) Submit del modal: guardar
    formEditar?.addEventListener('submit', async (ev) => {
      ev.preventDefault();
      const fd = new FormData(formEditar);
      fd.append('accion', 'editar');

      const resp = await fetch('../controlador/controladorLicencias.php', {
        method: 'POST',
        body: fd
      }).then(r => r.json()).catch(() => ({
        ok: false,
        msg: 'Error de red'
      }));

      if (resp.ok) {
        Swal.fire('OK', 'Cambios guardados.', 'success').then(() => location.reload());
      } else {
        Swal.fire('Error', resp.msg || 'No se pudo guardar.', 'error');
      }
    });


    // (Opcional) Autocalcular días al cambiar fechas
    function diffDias(inicio, fin) {
      const a = new Date(inicio),
        b = new Date(fin);
      if (isNaN(a) || isNaN(b)) return null;
      const ms = b - a;
      return ms >= 0 ? Math.floor(ms / 86400000) + 1 : null;
    }
    [edIni, edFin].forEach(inp => inp?.addEventListener('change', () => {
      const d = diffDias(edIni.value, edFin.value);
      if (d !== null) edDias.value = d;
    }));

    function crearSolicitud(submit_action) {
      const data = Object.fromEntries(new FormData(form).entries());
      data.submit_action = submit_action; // 'borrador' | 'enviar'
      return postAccion('crear', data);
    }

    function renderDetalleV2(d) {
      const colorEstado = (estado) => {
        const e = (estado || '').toLowerCase();
        if (e.includes('aprob')) return 'success';
        if (e.includes('rechaz')) return 'danger';
        if (e.includes('pendiente')) return 'warning';
        if (e.includes('anul')) return 'secondary';
        return 'secondary';
      };
      const fmt = (x) => (x ?? '') || '—';

      verBody.innerHTML = `
    <!-- Header: Nº + Estado -->
    <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
      <div class="d-flex align-items-center gap-2">
        <span class="badge rounded-pill text-bg-dark px-3 py-2">#${fmt(d.id_licencia)}</span>
        <span class="badge text-bg-${colorEstado(d.estado)} px-3 py-2">${fmt(d.estado)}</span>
      </div>
      <small class="text-muted">
        ${d.fecha_solicitud ? `Solicitada: ${d.fecha_solicitud}` : ''}
      </small>
    </div>

    <div class="row g-3">
      <!-- Solicitud -->
      <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h6 class="text-uppercase text-muted mb-3">Solicitud</h6>
            <dl class="row mb-0">
              <dt class="col-5">Tipo</dt><dd class="col-7">${fmt(d.tipo)}</dd>
              <dt class="col-5">Observaciones</dt><dd class="col-7">${fmt(d.observaciones)}</dd>
            </dl>
          </div>
        </div>
      </div>
      <!-- Empleado -->
      <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h6 class="text-uppercase text-muted mb-3">Empleado</h6>
            <dl class="row mb-0">
              <dt class="col-5">Nombre</dt><dd class="col-7">${fmt(d.empleado)}</dd>
              <dt class="col-5">Legajo</dt><dd class="col-7">${fmt(d.legajo)}</dd>
            </dl>
          </div>
        </div>
      </div>
      <!-- Período -->
      <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h6 class="text-uppercase text-muted mb-3">Período</h6>
            <dl class="row mb-0">
              <dt class="col-5">Desde</dt><dd class="col-7">${fmt(d.fecha_inicio)}</dd>
              <dt class="col-5">Hasta</dt><dd class="col-7">${fmt(d.fecha_fin)}</dd>
            </dl>
          </div>
        </div>
      </div>
      <!-- Cómputo -->
      <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h6 class="text-uppercase text-muted mb-3">Cómputo</h6>
            <dl class="row mb-0">
              <dt class="col-5">Días</dt><dd class="col-7">${fmt(d.cantidad_dias)}</dd>
            </dl>
          </div>
        </div>
      </div>
    </div>
  `;
    }


    btnBorrador.addEventListener('click', async () => {
      const resp = await crearSolicitud('borrador');
      if (resp.ok) {
        Swal.fire('OK', 'Borrador guardado correctamente.', 'success').then(() => location.reload());
      } else {
        Swal.fire('Error', resp.msg || 'No se pudo guardar.', 'error');
      }
    });

    btnEnviar.addEventListener('click', async () => {
      const resp = await crearSolicitud('enviar');
      if (resp.ok) {
        Swal.fire('OK', 'Solicitud enviada a aprobación.', 'success').then(() => location.reload());
      } else {
        Swal.fire('Error', resp.msg || 'No se pudo enviar.', 'error');
      }
    });

    // Cancelar solicitud
    tabla?.addEventListener('click', async (e) => {
      const btn = e.target.closest('.btnCancelar');
      if (!btn) return;
      const id = btn.getAttribute('data-id');
      const conf = await Swal.fire({
        icon: 'warning',
        title: 'Cancelar solicitud',
        text: '¿Seguro que querés cancelar esta solicitud?',
        showCancelButton: true,
        confirmButtonText: 'Sí, cancelar',
        cancelButtonText: 'No'
      });
      if (!conf.isConfirmed) return;

      const resp = await postAccion('cancelar', {
        id_licencia: id
      });
      if (resp.ok) {
        Swal.fire('OK', 'Solicitud cancelada.', 'success').then(() => location.reload());
      } else {
        Swal.fire('Error', resp.msg || 'No se pudo cancelar.', 'error');
      }
    });

    // Ver (modal con detalle + acciones)
    tabla?.addEventListener('click', async (e) => {
      const btn = e.target.closest('.btnVer');
      if (!btn) return;
      const id = btn.getAttribute('data-id');

      const resp = await postAccion('detalle', {
        id_licencia: id
      });
      if (!resp?.ok) {
        Swal.fire('Error', resp?.msg || 'No se pudo obtener el detalle.', 'error');
        return;
      }

      const d = resp.data,
        acciones = resp.acciones || [];
      renderDetalleV2(d);

      verActions.innerHTML = '';
      if (acciones.includes('enviar')) {
        verActions.appendChild(makeBtn('Enviar', 'btn-primary', async () => {
          const r = await postAccion('enviar', {
            id_licencia: id
          });
          if (r?.ok) Swal.fire('OK', 'Enviada a aprobación.', 'success').then(() => location.reload());
          else Swal.fire('Error', r.msg || 'No se pudo enviar.', 'error');
        }));
      }
      if (acciones.includes('cancelar')) {
        verActions.appendChild(makeBtn('Cancelar', 'btn-danger', async () => {
          const conf = await Swal.fire({
            icon: 'warning',
            title: 'Cancelar',
            text: '¿Seguro que querés cancelar esta solicitud?',
            showCancelButton: true,
            confirmButtonText: 'Sí'
          });
          if (!conf.isConfirmed) return;
          const r = await postAccion('cancelar', {
            id_licencia: id
          });
          if (r?.ok) Swal.fire('OK', 'Solicitud cancelada.', 'success').then(() => location.reload());
          else Swal.fire('Error', r.msg || 'No se pudo cancelar.', 'error');
        }));
      }

      modalVer.show();
    });


    // Opcional: DataTables (si querés)
    if (window.DataTable) {
      new DataTable('#tablaSolicitudes', {
        responsive: true,
        language: {
          "decimal": ",",
          "thousands": ".",
          "info": "Mostrando _END_ registros de un total de _TOTAL_",
          "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
          "infoFiltered": "(filtrado de un total de _MAX_ registros)",
          "loadingRecords": "Cargando...",
          "lengthMenu": "Mostrar _MENU_",
          "paginate": {
            "first": "<<",
            "last": ">>",
            "next": ">",
            "previous": "<"
          },
          "search": "Buscador:",
          "searchPlaceholder": "Buscar...",
          "emptyTable": "No hay registros para mostrar en la tabla",
        },
        pageLength: 10,
        scrollX: false,
        autoWidth: false,
        columnDefs: [
          // 1: Fecha Solicitud -> centrada
          {
            targets: 1,
            className: 'text-center text-nowrap'
          },

          // 2: Tipo -> sin width fijo (que respire)
          {
            targets: 2,
            className: 'text-wrap'
          },

          // 3: Estado -> sin width fijo (que respire)
          {
            targets: 3,
            className: 'text-wrap'
          },

          // 4: Inicio -> centrada y no wrap
          {
            targets: 4,
            className: 'text-center text-nowrap'
          },

          // 5: Fin -> centrada y no wrap
          {
            targets: 5,
            className: 'text-center text-nowrap'
          },

          // 6: Días -> centrada
          {
            targets: 6,
            className: 'text-center'
          },

          // 7: Acciones (dejalo como venga)
          {
            targets: 7,
            className: ''
          },
        ]
      });
      //dt.columns.adjust();
    }
  })();
</script>