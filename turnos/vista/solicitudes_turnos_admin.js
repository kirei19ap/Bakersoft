// turnos/vista/solicitudes_turnos_admin.js

document.addEventListener('DOMContentLoaded', () => {
    const tabla = document.getElementById('tablaSolicitudesTurno');
    if (!tabla) return;

    const tbody = tabla.querySelector('tbody');

    function formatearDMY(ymd) {
        if (!ymd) return '';
        const [y,m,d] = ymd.split('-');
        return `${d}-${m}-${y}`;
    }

    function formatearFechaHora(fechaHora) {
        if (!fechaHora) return '';
        // asumiendo formato "YYYY-MM-DD HH:MM:SS"
        const [fecha, hora] = fechaHora.split(' ');
        const [y,m,d] = fecha.split('-');
        const hhmm = hora ? hora.substring(0,5) : '';
        return `${d}-${m}-${y} ${hhmm}`;
    }

    async function cargarSolicitudes() {
        try {
            const resp = await fetch('listar_solicitudes_turno.php');
            const json = await resp.json();

            if (!json.ok) {
                tbody.innerHTML = `
                    <tr>
                      <td colspan="7" class="text-center text-danger">
                        ${json.mensaje || 'No se pudieron obtener las solicitudes.'}
                      </td>
                    </tr>
                `;
                return;
            }

            const solicitudes = json.data || [];

            if (!solicitudes.length) {
                tbody.innerHTML = `
                    <tr>
                      <td colspan="7" class="text-center text-muted">
                        No hay solicitudes pendientes.
                      </td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = '';

            solicitudes.forEach(s => {
                const tr = document.createElement('tr');

                // Fecha turno
                const tdFechaTurno = document.createElement('td');
                tdFechaTurno.textContent = formatearDMY(s.fecha);
                tr.appendChild(tdFechaTurno);

                // Turno
                const tdTurno = document.createElement('td');
                tdTurno.innerHTML = `
                    <div class="fw-semibold">${s.nombreTurno}</div>
                    <small class="text-muted">
                      ${s.horaDesde.substring(0,5)} - ${s.horaHasta.substring(0,5)}
                    </small>
                `;
                tr.appendChild(tdTurno);

                // Empleado
                const tdEmpleado = document.createElement('td');
                tdEmpleado.innerHTML = `
                    <div>${s.apellidoEmpleado}, ${s.nombreEmpleado}</div>
                    <small class="text-muted">Legajo: ${s.legajo || '-'}</small>
                `;
                tr.appendChild(tdEmpleado);

                // Tipo
                const tdTipo = document.createElement('td');
                let badgeTipo = '';
                if (s.tipo === 'Cambio') {
                    badgeTipo = '<span class="badge bg-info text-dark">Cambio</span>';
                } else {
                    badgeTipo = '<span class="badge bg-warning text-dark">Rechazo</span>';
                }
                tdTipo.innerHTML = badgeTipo;
                tr.appendChild(tdTipo);

                // Motivo
                const tdMotivo = document.createElement('td');
                tdMotivo.textContent = s.motivo;
                tr.appendChild(tdMotivo);

                // Fecha solicitud
                const tdFechaSol = document.createElement('td');
                tdFechaSol.textContent = formatearFechaHora(s.fechaSolicitud);
                tr.appendChild(tdFechaSol);

                // Acciones
                const tdAcc = document.createElement('td');
                tdAcc.className = 'text-center';

                tdAcc.innerHTML = `
                    <div class="d-flex flex-column flex-md-row justify-content-center gap-1">
                      <button type="button" class="btn btn-sm btn-success btn-aprobar"
                              data-id="${s.idSolicitud}">
                        <ion-icon name="checkmark-circle-outline"></ion-icon>
                        <span>Aprobar</span>
                      </button>
                      <button type="button" class="btn btn-sm btn-danger btn-rechazar"
                              data-id="${s.idSolicitud}">
                        <ion-icon name="close-circle-outline"></ion-icon>
                        <span>Rechazar</span>
                      </button>
                    </div>
                `;

                tr.appendChild(tdAcc);

                tbody.appendChild(tr);
            });

        } catch (error) {
            console.error(error);
            tbody.innerHTML = `
                <tr>
                  <td colspan="7" class="text-center text-danger">
                    Error al comunicarse con el servidor.
                  </td>
                </tr>
            `;
        }
    }

    async function gestionarSolicitud(idSolicitud, accion) {
        const formData = new FormData();
        formData.append('idSolicitud', idSolicitud);
        formData.append('accion', accion);

        try {
            const resp = await fetch('gestionar_solicitud_turno.php', {
                method: 'POST',
                body: formData
            });
            const json = await resp.json();

            if (!json.ok) {
                Swal.fire('Error', json.mensaje || 'No se pudo actualizar la solicitud.', 'error');
                return;
            }

            Swal.fire('OK', json.mensaje || 'Acción realizada correctamente.', 'success');
            cargarSolicitudes();

        } catch (error) {
            console.error(error);
            Swal.fire('Error', 'Ocurrió un error al comunicarse con el servidor.', 'error');
        }
    }

    // Delegamos clicks de los botones de la tabla
    tbody.addEventListener('click', (e) => {
        const btn = e.target.closest('button');
        if (!btn) return;

        const id = btn.getAttribute('data-id');
        if (!id) return;

        if (btn.classList.contains('btn-aprobar')) {
            Swal.fire({
                title: '¿Aprobar solicitud?',
                text: 'El turno del empleado se marcará como cancelado.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, aprobar',
                cancelButtonText: 'Cancelar'
            }).then(res => {
                if (res.isConfirmed) {
                    gestionarSolicitud(id, 'aprobar');
                }
            });
            return;
        }

        if (btn.classList.contains('btn-rechazar')) {
            Swal.fire({
                title: '¿Rechazar solicitud?',
                text: 'El turno se mantendrá asignado al empleado.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, rechazar',
                cancelButtonText: 'Cancelar'
            }).then(res => {
                if (res.isConfirmed) {
                    gestionarSolicitud(id, 'rechazar');
                }
            });
            return;
        }
    });

    // Carga inicial
    cargarSolicitudes();
});
