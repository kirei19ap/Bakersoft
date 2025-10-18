<?php
include_once("head/headLicencias.php");
?>
<div class="contenido">
    <div class="titulo-contenido shadow-sm">
        <h1 class="display-5">Gestión de licencias (RRHH)</h1>
    </div>
    <div class="contenido-principal">
        <div class="contenido">
            <div class="container-fluid">
                <div class="card tabla-empleados">
                    <div class="card-header bg-light"><strong>Pendientes de aprobación</strong></div>
                    <div class="card-body table-responsive">
                        <div class="table-responsive">
                            <table id="tablaRRHH" class="table table-striped table-hover table-bordered align-middle w-100">
                                <thead class="table-light">
                                    <tr>
                                        <!-- Ajustá los encabezados a tus columnas reales si difieren -->
                                        <th hidden>ID</th>
                                        <th>Empleado</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Desde</th>
                                        <th>Hasta</th>
                                        <th>Días</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>

                                <tbody></tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include_once("foot/foot.php"); ?>

    <!-- Modal Observación -->
    <div class="modal fade" id="modalObs" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Observación</h5>
                </div>
                <div class="modal-body">
                    <textarea id="txtObs" class="form-control" rows="4" placeholder="(opcional)"></textarea>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button class="btn btn-primary" id="btnConfirmarObs">Confirmar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ver (detalle) -->
<div class="modal fade" id="modalVerRRHH" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" style="max-width:1140px">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle de solicitud</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="verRRHHBody"></div>
            </div>
            <div class="modal-footer" id="verRRHHAcciones"></div>
        </div>
    </div>
</div>

<script>
    (function() {
        // ==== Refs MODAL OBSERVACIÓN (scope global del script) ====
        const modalObsEl = document.getElementById('modalObs');
        const modalObs = bootstrap.Modal.getOrCreateInstance(modalObsEl);
        const txtObs = document.getElementById('txtObs');
        const btnConfirmarObs = document.getElementById('btnConfirmarObs');

        const tbody = document.querySelector('#tablaRRHH tbody');
        let accionRRHH = null,
            licSeleccionada = null;

        function fetchListar() {
            const fd = new FormData();
            fd.append('accion', 'rrhh_listar');
            return fetch('../../licencias/controlador/controladorLicencias.php', {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json());
        }

        function resolver(accion, idLic, observacion) {
            const fd = new FormData();
            fd.append('accion', 'rrhh_resolver');
            fd.append('accion_rrhh', accion); // aprobar | rechazar
            fd.append('id_licencia', idLic);
            fd.append('observacion', observacion || '');
            return fetch('../../licencias/controlador/controladorLicencias.php', {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json());
        }



        function render(items) {
            const fmtFecha = (s) => {
                if (!s) return '';
                const [y, m, d] = String(s).split(' ')[0].split('-');
                if (!y || !m || !d) return s;
                return `${d}/${m}/${y}`;
            };

            const ESTADOS_BY_ID = {
                1: 'Nueva',
                2: 'Pendiente de envío',
                3: 'Pendiente de aprobación',
                4: 'Cancelada',
                5: 'Aprobada',
                6: 'Rechazada'
            };

            const clsBadge = (estadoTxt) => {
                const e = String(estadoTxt || '').toLowerCase();
                if (e.includes('aprob')) return 'success';
                if (e.includes('rechaz')) return 'danger';
                if (e.includes('pendiente de aprobación')) return 'warning';
                if (e.includes('pendiente de envío')) return 'secondary';
                if (e.includes('cancel')) return 'dark';
                if (e.includes('nueva')) return 'info';
                return 'secondary';
            };

            const normalizarEstado = (it) => {
                // intenta diferentes nombres de campo
                let txt = it.estado ?? it.estado_nombre ?? it.nombre_estado ?? '';
                // si vino numérico (string o number), mapealo
                if (!txt && (it.id_estado !== undefined && it.id_estado !== null)) {
                    const idNum = Number(it.id_estado);
                    if (!Number.isNaN(idNum)) txt = ESTADOS_BY_ID[idNum] || '';
                }
                return String(txt || '');
            };

            tbody.innerHTML = (items || []).map(it => {
                const estadoTxt = normalizarEstado(it);
                const badge = `<span class="badge text-bg-${clsBadge(estadoTxt)}">${estadoTxt || '—'}</span>`;
                return `
      <tr>
        <td hidden>${it.id_licencia ?? ''}</td>
        <td>${it.empleado ?? ''}</td>
        <td>${it.tipo ?? ''}</td>
        <td class="text-center">${badge}</td>
        <td class="text-center">${fmtFecha(it.fecha_inicio)}</td>
        <td class="text-center">${fmtFecha(it.fecha_fin)}</td>
        <td class="text-center">${it.cantidad_dias ?? ''}</td>
        <td>
          <button class="btn btn-success btn-sm btnVer" data-id="${it.id_licencia}">
            <ion-icon name="eye-outline"></ion-icon>
          </button>
        </td>
      </tr>
    `;
            }).join('');
        }


        // Cargar inicial
        fetchListar().then(resp => {
            if (!resp.ok) {
                Swal.fire('Error', resp.msg || 'No autorizado', 'error');
                return;
            }
            render(resp.items || []);
            (function() {
                // Evitar múltiples inits si redibujan la vista
                const $t = $('#tablaRRHH');
                if ($.fn.DataTable.isDataTable($t)) {
                    $t.DataTable().destroy();
                }

                const dt = $t.DataTable({
                    // ---- Layout consistente con el resto de la app ----
                    dom: "<'row align-items-center mb-2'<'col-md-6 d-flex gap-2'lB><'col-md-6'f>>" +
                        "rt" +
                        "<'row mt-2'<'col-md-5'i><'col-md-7'p>>",

                    buttons: [{
                            extend: 'copy',
                            text: 'Copiar',
                            className: 'btn btn-sm btn-outline-secondary'
                        },
                        {
                            extend: 'excel',
                            text: 'Excel',
                            className: 'btn btn-sm btn-outline-success',
                            title: 'Licencias RRHH'
                        },
                        {
                            extend: 'csv',
                            text: 'CSV',
                            className: 'btn btn-sm btn-outline-primary',
                            title: 'Licencias RRHH'
                        },
                        {
                            extend: 'print',
                            text: 'Imprimir',
                            className: 'btn btn-sm btn-outline-dark'
                        }
                        // Si en tu proyecto usan pdfmake, podés agregar PDF aquí
                    ],

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

                    // ---- Comportamiento y aspecto ----
                    responsive: true,
                    autoWidth: false,
                    pageLength: 10,
                    lengthMenu: [
                        [10, 25, 50, -1],
                        [10, 25, 50, "Todos"]
                    ],
                    order: [
                        [0, 'desc']
                    ], // orden por ID desc (ajustá si usás otra col)

                    // En tablas anchas:
                    // scrollX: true,

                    fixedHeader: {
                        header: true,
                        headerOffset: 56 // ajustá si tu navbar tiene otro alto
                    },

                    // ---- Columnas (ajustá índices si tu estructura difiere) ----
                    columnDefs: [
                        // ID oculta (sin ancho reservado)
                        {
                            targets: 0,
                            visible: false,
                            searchable: false
                        },

                        // Estado ya viene con badge en el HTML de la celda
                        {
                            targets: 3,
                            className: "text-center"
                        },

                        // Fechas centradas (Desde/Hasta)
                        {
                            targets: [4, 5],
                            className: "text-center",
                            width: "120px"
                        },

                        // Días
                        {
                            targets: 6,
                            className: "text-center",
                            width: "80px"
                        },

                        // Acciones
                        {
                            targets: -1,
                            orderable: false,
                            searchable: false,
                            className: "text-center",
                            width: "140px"
                        }
                    ]

                });
                // Ajuste inicial para evitar espacios/gaps tras ocultar columnas
                dt.columns.adjust();
                if (dt.responsive && typeof dt.responsive.recalc === 'function') {
                    dt.responsive.recalc();
                }
                if (dt.fixedHeader && typeof dt.fixedHeader.adjust === 'function') {
                    dt.fixedHeader.adjust();
                }

                // Si la tabla vive dentro de tabs/modales, recalcular al mostrarlos
                $(document).on('shown.bs.tab shown.bs.modal', function() {
                    dt.columns.adjust();
                    if (dt.responsive && typeof dt.responsive.recalc === 'function') {
                        dt.responsive.recalc();
                    }
                    if (dt.fixedHeader && typeof dt.fixedHeader.adjust === 'function') {
                        dt.fixedHeader.adjust();
                    }
                });

                // Recalcular en resize (por si cambia el ancho del contenedor)
                $(window).on('resize', function() {
                    dt.columns.adjust();
                    if (dt.responsive && typeof dt.responsive.recalc === 'function') {
                        dt.responsive.recalc();
                    }
                    if (dt.fixedHeader && typeof dt.fixedHeader.adjust === 'function') {
                        dt.fixedHeader.adjust();
                    }
                });



                // Opcional: si usás tooltips en botones dentro de la tabla
                $t.on('draw.dt', function() {
                    $('[data-bs-toggle="tooltip"]').tooltip({
                        container: 'body'
                    });
                });

            })();
        });


        // Helpers modal
        const modalVerRRHH = new bootstrap.Modal(document.getElementById('modalVerRRHH'));
        const verRRHHBody = document.getElementById('verRRHHBody');
        const verRRHHAcciones = document.getElementById('verRRHHAcciones');

        // 2.3.1 Llama al controlador para traer el detalle
        function detalle(idLic) {
            const fd = new FormData();
            fd.append('accion', 'rrhh_detalle');
            fd.append('id_licencia', idLic);
            return fetch('../../licencias/controlador/controladorLicencias.php', {
                method: 'POST',
                body: fd
            }).then(async r => {
                const txt = await r.text();
                try {
                    return JSON.parse(txt);
                } catch (e) {
                    console.error('Respuesta no JSON:', txt);
                    throw e;
                }
            });
        }

        // 2.3.2 Render del modal (incluye observaciones del empleado)
        function renderDetalleRRHH(d, acciones = []) {
            // ... (deja el resto igual)

            const ESTADOS_BY_ID = {
                1: 'Nueva',
                2: 'Pendiente de envío',
                3: 'Pendiente de aprobación',
                4: 'Cancelada',
                5: 'Aprobada',
                6: 'Rechazada'
            };

            const estadoTxt = (function() {
                let t = d.estado ?? d.estado_nombre ?? d.nombre_estado ?? '';
                if (!t && (d.id_estado !== undefined && d.id_estado !== null)) {
                    const idNum = Number(d.id_estado);
                    if (!Number.isNaN(idNum)) t = ESTADOS_BY_ID[idNum] || '';
                }
                return String(t || '');
            })();

            const badgeClass = (function(eTxt) {
                const e = eTxt.toLowerCase();
                if (e.includes('aprob')) return 'success';
                if (e.includes('rechaz')) return 'danger';
                if (e.includes('pendiente de aprobación')) return 'warning';
                if (e.includes('pendiente de envío')) return 'secondary';
                if (e.includes('cancel')) return 'dark';
                if (e.includes('nueva')) return 'info';
                return 'secondary';
            })(estadoTxt);

            // ----- CONTENIDO -----
            verRRHHBody.innerHTML = `
    <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
      <div class="d-flex align-items-center gap-2">
        <span class="badge rounded-pill text-bg-dark px-3 py-2">#${d.id_licencia ?? ''}</span>
        <span class="badge text-bg-${badgeClass} px-3 py-2">${estadoTxt || '—'}</span>
      </div>
      <small class="text-muted">${d.fecha_solicitud ? `Solicitada: ${d.fecha_solicitud}` : ''}</small>
    </div>

    <div class="row g-3">
      <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h6 class="text-uppercase text-muted mb-3">Solicitud</h6>
            <dl class="row mb-0">
              <dt class="col-5">Tipo</dt><dd class="col-7">${d.tipo ?? '—'}</dd>
              <dt class="col-5">Obs. empleado</dt><dd class="col-7">${d.observaciones ?? '—'}</dd>
            </dl>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h6 class="text-uppercase text-muted mb-3">Empleado</h6>
            <dl class="row mb-0">
              <dt class="col-5">Nombre</dt><dd class="col-7">${d.empleado ?? '—'}</dd>
              <dt class="col-5">Legajo</dt><dd class="col-7">${d.legajo ?? '—'}</dd>
            </dl>
          </div>
        </div>
      </div>

      <div class="col-12 col-md-6 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body">
            <h6 class="text-uppercase text-muted mb-3">Período</h6>
            <dl class="row mb-0">
              <dt class="col-5">Desde</dt><dd class="col-7">${d.fecha_inicio ?? '—'}</dd>
              <dt class="col-5">Hasta</dt><dd class="col-7">${d.fecha_fin ?? '—'}</dd>
              <dt class="col-5">Días</dt><dd class="col-7">${d.cantidad_dias ?? '—'}</dd>
            </dl>
          </div>
        </div>
      </div>
    </div>
  `;
            // ----- ACCIONES -----
            verRRHHAcciones.innerHTML = '';

            const mkBtn = (txt, cls, onClick) => {
                const b = document.createElement('button');
                b.type = 'button';
                b.className = `btn ${cls}`;
                b.textContent = txt;
                b.onclick = onClick;
                return b;
            };

            // APROBAR
            if (acciones.includes('aprobar')) {
                const btnA = mkBtn('Aprobar', 'btn-success', async () => {
                    btnA.disabled = true;
                    const r = await resolver('aprobar', d.id_licencia, '');
                    if (r?.ok) Swal.fire('OK', 'Solicitud aprobada.', 'success').then(() => location.reload());
                    else {
                        btnA.disabled = false;
                        Swal.fire('Error', r?.msg || 'No se pudo aprobar.', 'error');
                    }
                });
                verRRHHAcciones.appendChild(btnA);
            }

            // RECHAZAR (cierra Ver, abre Observación; si cancelan, reabre Ver)
            if (acciones.includes('rechazar')) {
                const btnR = mkBtn('Rechazar', 'btn-danger ms-2', () => {
                    let confirmed = false;

                    // preparar modal de observación
                    if (txtObs) txtObs.value = '';

                    // Al cerrar Observación SIN confirmar, reabrir "Ver"
                    const onHidden = () => {
                        if (!confirmed) modalVerRRHH.show();
                        modalObsEl.removeEventListener('hidden.bs.modal', onHidden);
                    };
                    modalObsEl.addEventListener('hidden.bs.modal', onHidden);

                    // Evitar handlers duplicados si abren/cancelan varias veces
                    btnConfirmarObs.onclick = null;

                    // Confirmar rechazo (motivo obligatorio)
                    btnConfirmarObs.onclick = async () => {
                        const obs = (txtObs?.value || '').trim();
                        if (!obs) {
                            Swal.fire('Atención', 'Ingresá un motivo de rechazo.', 'warning');
                            return;
                        }
                        confirmed = true; // para que no se reabra "Ver"
                        modalObs.hide(); // cerramos Observación

                        const r = await resolver('rechazar', d.id_licencia, obs);
                        if (r?.ok) {
                            Swal.fire('OK', 'Solicitud rechazada.', 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error', r?.msg || 'No se pudo rechazar.', 'error').then(() => {
                                modalVerRRHH.show(); // si falló, reabrimos "Ver"
                            });
                        }
                    };

                    // cerrar "Ver" y abrir Observación
                    modalVerRRHH.hide();
                    modalObs.show();
                });
                verRRHHAcciones.appendChild(btnR);
            }

        }

      

        // Click en botón "Ver" de la tabla
        tbody.addEventListener('click', async (e) => {
            const btn = e.target.closest('.btnVer');
            if (!btn) return;

            const id = btn.getAttribute('data-id');
            const r = await detalle(id);
            if (!r?.ok) {
                Swal.fire('Error', r?.msg || 'No se pudo obtener el detalle.', 'error');
                return;
            }

            const d = r.data,
                acciones = r.acciones || [];
            renderDetalleRRHH(d, acciones);

            const modalVerRRHH = bootstrap.Modal.getOrCreateInstance(document.getElementById('modalVerRRHH'));
            modalVerRRHH.show();
        });

    })();
</script>