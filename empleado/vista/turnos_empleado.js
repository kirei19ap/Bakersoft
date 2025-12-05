// empleado/vista/turnos_empleado.js

document.addEventListener('DOMContentLoaded', () => {
    const inputDesde = document.getElementById('fechaDesde');
    const inputHasta = document.getElementById('fechaHasta');
    const selectEstado = document.getElementById('estado');
    const btnBuscar = document.getElementById('btnBuscarTurnos');
    const btnAceptarSel = document.getElementById('btnAceptarSeleccionados');
    const btnFinalizarSel = document.getElementById('btnFinalizarSeleccionados');
    const tabla = document.getElementById('tablaMisTurnos');
    const tbody = tabla ? tabla.querySelector('tbody') : null;
    const chkTodos = document.getElementById('chkSeleccionarTodos');
    const spanTotalTurnos = document.getElementById('totalTurnosValor');
    const spanAsignados = document.getElementById('asignadosValor');
    const spanConfirmados = document.getElementById('confirmadosValor');
    const spanProximoTurno = document.getElementById('proximoTurnoValor');
    const modalSolicitud = document.getElementById('modalSolicitudCambioTurno');
    const spanInfoTurnoSel = document.getElementById('infoTurnoSeleccionado');
    const txtMotivo = document.getElementById('motivoSolicitud');
    const btnConfirmarSol = document.getElementById('btnConfirmarSolicitudCambio');

    let modalSolicitudInstance = null;
    let asignacionIdEnSolicitud = null;

    if (modalSolicitud) {
        modalSolicitudInstance = new bootstrap.Modal(modalSolicitud);
    }


    if (!tabla || !tbody) return;

    // Helper fecha
    function hoyYmd() {
        const h = new Date();
        const y = h.getFullYear();
        const m = (h.getMonth() + 1).toString().padStart(2, '0');
        const d = h.getDate().toString().padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function formatearDMY(yyyy_mm_dd) {
        if (!yyyy_mm_dd) return '';
        const [y, m, d] = yyyy_mm_dd.split('-');
        return `${d}-${m}-${y}`;
    }

    // Seteamos valores por defecto de fechas: hoy y hoy+14
    const hoy = hoyYmd();
    const en14 = (() => {
        const f = new Date();
        f.setDate(f.getDate() + 14);
        const y = f.getFullYear();
        const m = (f.getMonth() + 1).toString().padStart(2, '0');
        const d = f.getDate().toString().padStart(2, '0');
        return `${y}-${m}-${d}`;
    })();

    if (inputDesde) inputDesde.value = hoy;
    if (inputHasta) inputHasta.value = en14;

    // Buscar turnos
    if (btnBuscar) {
        btnBuscar.addEventListener('click', () => {
            cargarTurnos();
        });
    }

    // Seleccionar todos
    if (chkTodos) {
        chkTodos.addEventListener('change', () => {
            const checks = tbody.querySelectorAll('input.chk-turno');
            checks.forEach(chk => chk.checked = chkTodos.checked);
        });
    }

    // Aceptar seleccionados
    if (btnAceptarSel) {
        btnAceptarSel.addEventListener('click', async () => {
            await cambiarEstadoSeleccionados('Confirmado');
        });
    }

    // Finalizar seleccionados
    if (btnFinalizarSel) {
        btnFinalizarSel.addEventListener('click', async () => {
            await cambiarEstadoSeleccionados('Finalizado');
        });
    }

    // Delegación de eventos para botones por fila
    tbody.addEventListener('click', async (e) => {
        const btn = e.target.closest('button');
        if (!btn) return;

        const id = btn.dataset.id;
        const fecha = btn.dataset.fecha; // YYYY-MM-DD

        if (!id) return;

        if (btn.classList.contains('btn-aceptar')) {
            await confirmarCambioEstadoFila(id, 'Confirmado');
        } else if (btn.classList.contains('btn-finalizar')) {
            // validación simple lado cliente
            if (fecha > hoyYmd()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'No se puede finalizar un turno futuro.'
                });
                return;
            }
            await confirmarCambioEstadoFila(id, 'Finalizado');
        }
        if (btn.classList.contains('btn-solicitar-cambio')) {
            const idAsignacion = btn.getAttribute('data-id');
            const fecha = btn.getAttribute('data-fecha');
            const turno = btn.getAttribute('data-turno');
            const horario = btn.getAttribute('data-horario');

            abrirModalSolicitudCambio(idAsignacion, fecha, turno, horario);
            return;
        }
    });

    // Carga inicial
    cargarTurnos();

    async function cargarTurnos() {
        const fechaDesde = inputDesde.value;
        const fechaHasta = inputHasta.value;
        const estado = selectEstado.value;

        const params = new URLSearchParams();
        if (fechaDesde) params.append('fechaDesde', fechaDesde);
        if (fechaHasta) params.append('fechaHasta', fechaHasta);
        if (estado) params.append('estado', estado);

        try {
            const resp = await fetch('cargar_turnos_empleado.php?' + params.toString());
            const json = await resp.json();

            if (!json.ok) {
                tbody.innerHTML = `
                    <tr>
                      <td colspan="5" class="text-center text-muted">
                        ${json.mensaje || 'No se pudieron obtener los turnos.'}
                      </td>
                    </tr>
                `;
                return;
            }

            renderTabla(json.data || []);

        } catch (error) {
            console.error(error);
            tbody.innerHTML = `
                <tr>
                  <td colspan="5" class="text-center text-muted">
                    Error al comunicarse con el servidor.
                  </td>
                </tr>
            `;
        }
    }

    function renderTabla(turnos) {
        tbody.innerHTML = '';
        chkTodos.checked = false;

        // === MÉTRICAS ===
        actualizarMetricas(turnos);

        if (!turnos.length) {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td colspan="5" class="text-center text-muted">
                  No se encontraron turnos para el criterio seleccionado.
                </td>
            `;
            tbody.appendChild(tr);
            return;
        }

        const hoyStr = hoyYmd();

        turnos.forEach(t => {
            const tr = document.createElement('tr');

            // Checkbox selección
            const tdSel = document.createElement('td');
            const chk = document.createElement('input');
            chk.type = 'checkbox';
            chk.className = 'chk-turno';
            chk.dataset.id = t.idAsignacion;
            chk.dataset.estado = t.estadoAsignacion;
            chk.dataset.fecha = t.fecha;
            tdSel.appendChild(chk);
            tr.appendChild(tdSel);

            // Fecha
            const tdFecha = document.createElement('td');
            tdFecha.textContent = formatearDMY(t.fecha);
            tr.appendChild(tdFecha);

            // Turno
            const tdTurno = document.createElement('td');
            tdTurno.innerHTML = `
                ${t.nombreTurno}<br>
                <small class="text-muted">
                  ${t.horaDesde.substring(0, 5)} - ${t.horaHasta.substring(0, 5)}
                </small>
            `;
            tr.appendChild(tdTurno);

            // Estado
            const tdEstado = document.createElement('td');
            let badge = '';
            if (t.estadoAsignacion === 'Asignado') {
                badge = '<span class="badge bg-warning text-dark">Asignado</span>';
            } else if (t.estadoAsignacion === 'Confirmado') {
                badge = '<span class="badge bg-info text-dark">Confirmado</span>';
            } else if (t.estadoAsignacion === 'Finalizado') {
                badge = '<span class="badge bg-success">Finalizado</span>';
            } else {
                badge = '<span class="badge bg-secondary">N/A</span>';
            }
            tdEstado.innerHTML = badge;
            tr.appendChild(tdEstado);

            // Acciones
            const tdAcc = document.createElement('td');
            tdAcc.className = 'text-center';

            if (t.solicitudesPendientes && parseInt(t.solicitudesPendientes) > 0) {
                // Hay solicitud pendiente: informamos y no dejamos tocar
                tdAcc.innerHTML = `
        <span class="badge bg-warning text-dark mb-1 d-block">Solicitud en revisión</span>
        <small class="text-muted">Esperando respuesta de producción</small>
    `;
            } else if (t.estadoAsignacion === 'Asignado') {
                tdAcc.innerHTML = `
        <div class="d-flex flex-column flex-md-row justify-content-center gap-1">
          <button type="button" class="btn btn-sm btn-success btn-aceptar"
                  data-id="${t.idAsignacion}" data-fecha="${t.fecha}">
            <ion-icon name="checkmark-done-outline"></ion-icon>
            <span>Aceptar</span>
          </button>
          <button type="button" class="btn btn-sm btn-outline-secondary btn-solicitar-cambio"
                  data-id="${t.idAsignacion}"
                  data-fecha="${t.fecha}"
                  data-turno="${t.nombreTurno}"
                  data-horario="${t.horaDesde.substring(0, 5)} - ${t.horaHasta.substring(0, 5)}">
            <ion-icon name="swap-horizontal-outline"></ion-icon>
            <span>Solicitar cambio</span>
          </button>
        </div>
    `;
            } else if (t.estadoAsignacion === 'Confirmado') {
                // solo mostrar finalizar si fecha <= hoy
                if (t.fecha <= hoyStr) {
                    tdAcc.innerHTML = `
                        <button type="button" class="btn btn-sm btn-primary btn-finalizar"
                                data-id="${t.idAsignacion}" data-fecha="${t.fecha}">
                          <ion-icon name="checkmark-circle-outline"></ion-icon>
                          <span>Finalizar</span>
                        </button>
                    `;
                } else {
                    tdAcc.innerHTML = '<span class="text-muted">Pendiente</span>';
                }
            } else {
                tdAcc.innerHTML = '<span class="text-muted">Sin acciones</span>';
            }

            tr.appendChild(tdAcc);

            tbody.appendChild(tr);
        });
    }

    function actualizarMetricas(turnos) {
        if (!spanTotalTurnos || !spanAsignados || !spanConfirmados || !spanProximoTurno) {
            return; // por si algún día se usa este JS en otra página sin panel
        }

        const total = turnos.length;
        let asignados = 0;
        let confirmados = 0;

        const hoyStr = hoyYmd();

        // Para calcular el próximo turno (fecha >= hoy, estado Asignado o Confirmado)
        const candidatosProximo = [];

        turnos.forEach(t => {
            if (t.estadoAsignacion === 'Asignado') asignados++;
            if (t.estadoAsignacion === 'Confirmado') confirmados++;

            if ((t.estadoAsignacion === 'Asignado' || t.estadoAsignacion === 'Confirmado') &&
                t.fecha >= hoyStr) {
                candidatosProximo.push(t);
            }
        });

        spanTotalTurnos.textContent = total;
        spanAsignados.textContent = asignados;
        spanConfirmados.textContent = confirmados;

        if (!candidatosProximo.length) {
            spanProximoTurno.textContent = 'Sin turnos próximos';
            return;
        }

        // Ordenamos candidatos por fecha + horaDesde para encontrar el más próximo
        candidatosProximo.sort((a, b) => {
            if (a.fecha < b.fecha) return -1;
            if (a.fecha > b.fecha) return 1;
            if (a.horaDesde < b.horaDesde) return -1;
            if (a.horaDesde > b.horaDesde) return 1;
            return 0;
        });

        const prox = candidatosProximo[0];
        const fechaBonita = formatearDMY(prox.fecha);
        const horaDesde = prox.horaDesde.substring(0, 5);
        const horaHasta = prox.horaHasta.substring(0, 5);

        spanProximoTurno.textContent = `${fechaBonita} – ${prox.nombreTurno} (${horaDesde} a ${horaHasta})`;
    }


    async function confirmarCambioEstadoFila(idAsignacion, nuevoEstado) {
        const textoAccion = nuevoEstado === 'Confirmado' ? 'aceptar este turno' : 'marcar este turno como finalizado';

        const confirm = await Swal.fire({
            icon: 'question',
            title: 'Confirmar acción',
            text: `¿Deseás ${textoAccion}?`,
            showCancelButton: true,
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar'
        });

        if (!confirm.isConfirmed) return;

        const ok = await llamarActualizarEstado(idAsignacion, nuevoEstado);
        if (ok) {
            cargarTurnos();
        }
    }

    async function cambiarEstadoSeleccionados(nuevoEstado) {
        const checks = Array.from(tbody.querySelectorAll('input.chk-turno:checked'));
        if (!checks.length) {
            Swal.fire({
                icon: 'info',
                title: 'Atención',
                text: 'No hay turnos seleccionados.'
            });
            return;
        }

        const textoAccion = nuevoEstado === 'Confirmado' ? 'aceptar' : 'finalizar';

        const confirm = await Swal.fire({
            icon: 'question',
            title: 'Confirmar acción',
            text: `¿Deseás ${textoAccion} los turnos seleccionados?`,
            showCancelButton: true,
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar'
        });

        if (!confirm.isConfirmed) return;

        // Validación rápida cliente para finalizar futuros
        if (nuevoEstado === 'Finalizado') {
            const hoyStr = hoyYmd();
            const futuros = checks.filter(chk => chk.dataset.fecha > hoyStr);
            if (futuros.length) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'Hay turnos futuros seleccionados. No se pueden finalizar.'
                });
                return;
            }
        }

        for (const chk of checks) {
            const id = chk.dataset.id;
            const ok = await llamarActualizarEstado(id, nuevoEstado);
            if (!ok) {
                // ya mostramos mensaje en la función
                break;
            }
        }

        cargarTurnos();
    }

    async function llamarActualizarEstado(idAsignacion, nuevoEstado) {
        try {
            const resp = await fetch('actualizar_estado_turno.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    idAsignacion: idAsignacion,
                    nuevoEstado: nuevoEstado
                })
            });

            const json = await resp.json();

            if (!json.ok) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: json.mensaje || 'No se pudo actualizar el estado del turno.'
                });
                return false;
            }

            // Para uso masivo no mostramos un Swal por cada turno, se ve en el refresh
            return true;

        } catch (error) {
            console.error(error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al comunicarse con el servidor.'
            });
            return false;
        }
    }

    // === NOTIFICACIÓN CAMPANITA ===

    async function actualizarNotificacionPendientes() {
        try {
            const resp = await fetch('notificaciones_turnos.php');
            const json = await resp.json();

            const icono = document.getElementById('iconoCampana');
            const badge = document.getElementById('badgePendientes');
            const cont = document.getElementById('notificacionTurnosPendientes');

            if (!icono || !badge || !cont) return;

            const pendientes = json.pendientes ?? 0;

            if (pendientes > 0) {
                // Campana activa
                icono.style.color = "#dc3545";   // rojo suave
                icono.setAttribute("name", "notifications");

                // Badge visible
                badge.style.display = "inline-block";
                badge.textContent = pendientes;

                // Tooltip
                cont.setAttribute("title", `Tenés ${pendientes} turno${pendientes > 1 ? 's' : ''} pendiente${pendientes > 1 ? 's' : ''} de confirmar`);
            } else {
                // Campana gris
                icono.style.color = "#6c757d";
                icono.setAttribute("name", "notifications-outline");
                badge.style.display = "none";
                cont.setAttribute("title", "Sin turnos pendientes");
            }

        } catch (err) {
            console.error("Error al obtener notificaciones:", err);
        }
    }

    // Ejecutar al cargar la página
    actualizarNotificacionPendientes();

    function abrirModalSolicitudCambio(idAsignacion, fecha, turno, horario) {
        asignacionIdEnSolicitud = idAsignacion;

        if (spanInfoTurnoSel) {
            spanInfoTurnoSel.textContent = `${formatearDMY(fecha)} – ${turno} (${horario})`;
        }
        if (txtMotivo) {
            txtMotivo.value = '';
        }
        const radioRechazo = document.getElementById('tipoRechazo');
        if (radioRechazo) radioRechazo.checked = true;

        if (modalSolicitudInstance) {
            modalSolicitudInstance.show();
        }
    }

    if (btnConfirmarSol) {
        btnConfirmarSol.addEventListener('click', async () => {
            if (!asignacionIdEnSolicitud) return;

            const motivo = (txtMotivo && txtMotivo.value.trim()) || '';
            if (motivo === '') {
                Swal.fire('Atención', 'Por favor indicá un motivo para la solicitud.', 'warning');
                return;
            }

            const tipoInput = document.querySelector('input[name="tipoSolicitud"]:checked');
            const tipo = tipoInput ? tipoInput.value : 'Rechazo';

            const formData = new FormData();
            formData.append('idAsignacion', asignacionIdEnSolicitud);
            formData.append('tipo', tipo);
            formData.append('motivo', motivo);

            try {
                const resp = await fetch('solicitar_cambio_turno.php', {
                    method: 'POST',
                    body: formData
                });
                //const json = await resp.json();
                const texto = await resp.text();
                console.log('RESPUESTA CRUDA:', texto);
                const json = JSON.parse(texto);

                if (!json.ok) {
                    Swal.fire('Error', json.mensaje || 'No se pudo registrar la solicitud.', 'error');
                    return;
                }

                Swal.fire('Solicitud enviada', json.mensaje || 'La solicitud fue registrada.', 'success');
                if (modalSolicitudInstance) {
                    modalSolicitudInstance.hide();
                }
                asignacionIdEnSolicitud = null;
                // Recargamos la tabla para reflejar "Solicitud en revisión"
                cargarTurnos();

            } catch (error) {
                console.error(error);
                Swal.fire('Error', 'Ocurrió un error al enviar la solicitud.', 'error');
            }
        });
    }


});

