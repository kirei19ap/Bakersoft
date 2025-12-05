// turnos/vista/turnos_index.js

document.addEventListener('DOMContentLoaded', () => {
    const tabla = document.querySelector('table');
    if (!tabla) return;

    const modalVer   = document.getElementById('modalVerAsignacion');
    const modalReas  = document.getElementById('modalReasignarTurno');
    const detalleDL  = document.getElementById('detalleAsignacionContenido');
    const infoTurno  = document.getElementById('infoTurnoReasignacion');
    const selNuevoOp = document.getElementById('selectNuevoOperario');
    const btnConfirmReas = document.getElementById('btnConfirmarReasignacion');

    let idAsignacionReasignar = null;

    const bsModalVer  = modalVer ? new bootstrap.Modal(modalVer) : null;
    const bsModalReas = modalReas ? new bootstrap.Modal(modalReas) : null;

    // Delegación de eventos para las acciones
    tabla.addEventListener('click', async (e) => {
        const btn = e.target.closest('button');
        if (!btn) return;

        const idAsignacion = btn.dataset.id;
        if (!idAsignacion) return;

        if (btn.classList.contains('btn-ver')) {
            await verAsignacion(idAsignacion);
        } else if (btn.classList.contains('btn-eliminar')) {
            await eliminarAsignacion(idAsignacion, btn);
        } else if (btn.classList.contains('btn-reasignar')) {
            await abrirReasignar(idAsignacion);
        }
    });

    async function verAsignacion(id) {
        try {
            const resp = await fetch(`ver_asignacion.php?id=${encodeURIComponent(id)}`);
            const json = await resp.json();

            if (!json.ok) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: json.mensaje || 'No se pudo obtener el detalle de la asignación.'
                });
                return;
            }

            const d = json.data;
            if (!detalleDL) return;

            detalleDL.innerHTML = `
              <dt class="col-sm-4">Fecha</dt>
              <dd class="col-sm-8">${formatoFechaDMY(d.fecha)}</dd>

              <dt class="col-sm-4">Turno</dt>
              <dd class="col-sm-8">${d.nombreTurno} (${d.horaDesde.substring(0,5)} - ${d.horaHasta.substring(0,5)})</dd>

              <dt class="col-sm-4">Empleado</dt>
              <dd class="col-sm-8">${d.apellido}, ${d.nombre} (Legajo: ${d.legajo || ''})</dd>

              <dt class="col-sm-4">Puesto</dt>
              <dd class="col-sm-8">${d.descrPuesto || ''}</dd>

              <dt class="col-sm-4">Estado turno</dt>
              <dd class="col-sm-8">${d.estadoAsignacion}</dd>

              <dt class="col-sm-4">Estado empleado</dt>
              <dd class="col-sm-8">${d.estadoEmpleado}</dd>
            `;

            if (bsModalVer) {
                bsModalVer.show();
            }

        } catch (error) {
            console.error(error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al comunicarse con el servidor.'
            });
        }
    }

    async function eliminarAsignacion(id, btn) {
        const fila = btn.closest('tr');

        const confirm = await Swal.fire({
            icon: 'warning',
            title: 'Eliminar asignación',
            text: '¿Seguro que querés eliminar esta asignación de turno?',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        });

        if (!confirm.isConfirmed) return;

        try {
            const formData = new FormData();
            formData.append('id', id);

            const resp = await fetch('eliminar_asignacion.php', {
                method: 'POST',
                body: formData
            });

            const json = await resp.json();

            if (!json.ok) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: json.mensaje || 'No se pudo eliminar la asignación.'
                });
                return;
            }

            Swal.fire({
                icon: 'success',
                title: 'OK',
                text: json.mensaje || 'Asignación eliminada correctamente.'
            });

            if (fila) {
                fila.remove();
            }

        } catch (error) {
            console.error(error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al comunicarse con el servidor.'
            });
        }
    }

    async function abrirReasignar(id) {
        idAsignacionReasignar = id;

        try {
            const resp = await fetch(`obtener_operarios_reasignar.php?id=${encodeURIComponent(id)}`);
            const json = await resp.json();

            if (!json.ok) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: json.mensaje || 'No se pudo obtener los datos para reasignar.'
                });
                return;
            }

            const asig = json.asignacion;
            const ops  = json.operarios || [];

            if (infoTurno) {
                infoTurno.innerHTML = `
                  <p class="mb-1"><strong>Fecha:</strong> ${formatoFechaDMY(asig.fecha)}</p>
                  <p class="mb-1"><strong>Turno:</strong> ${asig.nombreTurno} (${asig.horaDesde.substring(0,5)} - ${asig.horaHasta.substring(0,5)})</p>
                  <p class="mb-1"><strong>Empleado actual:</strong> ${asig.apellido}, ${asig.nombre} (Legajo: ${asig.legajo || ''})</p>
                `;
            }

            if (selNuevoOp) {
                selNuevoOp.innerHTML = '<option value="">-- Seleccionar operario --</option>';

                ops.forEach(op => {
                    // No listamos al empleado actual como opción
                    if (op.id_empleado === asig.id_empleado) return;

                    const label = `${op.apellido}, ${op.nombre} (Legajo: ${op.legajo || ''})`;

                    const opt = document.createElement('option');
                    opt.value = op.id_empleado;
                    opt.textContent = label;

                    // Si ya está Asignado a ese turno en esa fecha, podés marcarlo o excluirlo
                    if (op.estadoAsignacion === 'Asignado' || op.estadoAsignacion === 'Confirmado') {
                        // Excluimos los que ya tienen este turno
                        return;
                    }

                    selNuevoOp.appendChild(opt);
                });
            }

            if (bsModalReas) {
                bsModalReas.show();
            }

        } catch (error) {
            console.error(error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al comunicarse con el servidor.'
            });
        }
    }

    if (btnConfirmReas && selNuevoOp) {
        btnConfirmReas.addEventListener('click', async () => {
            if (!idAsignacionReasignar) return;

            const idNuevo = selNuevoOp.value;
            if (!idNuevo) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'Debés seleccionar un operario para reasignar el turno.'
                });
                return;
            }

            const confirm = await Swal.fire({
                icon: 'question',
                title: 'Confirmar reasignación',
                text: '¿Confirmás la reasignación de este turno al operario seleccionado?',
                showCancelButton: true,
                confirmButtonText: 'Sí, reasignar',
                cancelButtonText: 'Cancelar'
            });

            if (!confirm.isConfirmed) return;

            try {
                const resp = await fetch('reasignar_asignacion.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        idAsignacion: idAsignacionReasignar,
                        idEmpleadoNuevo: idNuevo
                    })
                });

                const json = await resp.json();

                if (!json.ok) {
                    console.warn('Conflictos reasignación:', json.conflictos);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: json.mensaje || 'No se pudo reasignar el turno.'
                    });
                    return;
                }

                Swal.fire({
                    icon: 'success',
                    title: 'OK',
                    text: json.mensaje || 'Turno reasignado correctamente.'
                });

                if (bsModalReas) {
                    bsModalReas.hide();
                }

                // Para simplificar, recargamos la página para ver el cambio
                window.location.reload();

            } catch (error) {
                console.error(error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al comunicarse con el servidor.'
                });
            }
        });
    }
});

function formatoFechaDMY(fechaYMD) {
    if (!fechaYMD) return "";
    const [y, m, d] = fechaYMD.split("-");
    return `${d}-${m}-${y}`;
}

