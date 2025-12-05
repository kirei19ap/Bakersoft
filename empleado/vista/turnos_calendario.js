// empleado/vista/turnos_calendario.js

document.addEventListener('DOMContentLoaded', () => {
    const calendarioContainer = document.getElementById('calendarioContainer');
    const tituloMes = document.getElementById('tituloMes');
    const btnMesAnterior = document.getElementById('btnMesAnterior');
    const btnMesSiguiente = document.getElementById('btnMesSiguiente');
    const btnHoy = document.getElementById('btnHoy');

    const modalDetalle = document.getElementById('modalDetalleDia');
    const detalleFechaSeleccionada = document.getElementById('detalleFechaSeleccionada');
    const tablaDetalleDiaBody = document.getElementById('tablaDetalleDiaBody');

    if (!calendarioContainer) return;

    // Estado del mes actual en vista
    const hoy = new Date();
    let currentYear = hoy.getFullYear();
    let currentMonth = hoy.getMonth() + 1; // 1-12

    // cache de turnos por fecha para el mes
    let turnosPorFecha = {};

    // Navegación
    if (btnMesAnterior) {
        btnMesAnterior.addEventListener('click', () => {
            currentMonth--;
            if (currentMonth < 1) {
                currentMonth = 12;
                currentYear--;
            }
            cargarTurnosMes();
        });
    }

    if (btnMesSiguiente) {
        btnMesSiguiente.addEventListener('click', () => {
            currentMonth++;
            if (currentMonth > 12) {
                currentMonth = 1;
                currentYear++;
            }
            cargarTurnosMes();
        });
    }

    if (btnHoy) {
        btnHoy.addEventListener('click', () => {
            const ahora = new Date();
            currentYear = ahora.getFullYear();
            currentMonth = ahora.getMonth() + 1;
            cargarTurnosMes();
        });
    }

    // Helpers
    function pad(num) {
        return num.toString().padStart(2, '0');
    }

    function formatearMesLargo(year, month) {
        const nombresMes = [
            'Enero','Febrero','Marzo','Abril','Mayo','Junio',
            'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'
        ];
        return `${nombresMes[month - 1]} ${year}`;
    }

    function formatearDMY(yyyy_mm_dd) {
        if (!yyyy_mm_dd) return '';
        const [y,m,d] = yyyy_mm_dd.split('-');
        return `${d}-${m}-${y}`;
    }

    function hoyYmd() {
        const n = new Date();
        const y = n.getFullYear();
        const m = pad(n.getMonth() + 1);
        const d = pad(n.getDate());
        return `${y}-${m}-${d}`;
    }

    // Carga turnos del mes actual
    async function cargarTurnosMes() {
        const desde = `${currentYear}-${pad(currentMonth)}-01`;
        const ultimoDia = new Date(currentYear, currentMonth, 0).getDate(); // mes base 1
        const hasta = `${currentYear}-${pad(currentMonth)}-${pad(ultimoDia)}`;

        if (tituloMes) {
            tituloMes.textContent = formatearMesLargo(currentYear, currentMonth);
        }

        const params = new URLSearchParams();
        params.append('desde', desde);
        params.append('hasta', hasta);

        try {
            const resp = await fetch('cargar_turnos_empleado_calendario.php?' + params.toString());
            const json = await resp.json();

            if (!json.ok) {
                calendarioContainer.innerHTML = `
                    <div class="alert alert-danger mt-2" role="alert">
                        ${json.mensaje || 'No se pudieron obtener los turnos.'}
                    </div>
                `;
                return;
            }

            const turnos = json.data || [];
            // Agrupamos por fecha
            turnosPorFecha = {};
            turnos.forEach(t => {
                const f = t.fecha;
                if (!turnosPorFecha[f]) {
                    turnosPorFecha[f] = [];
                }
                turnosPorFecha[f].push(t);
            });

            renderCalendario(currentYear, currentMonth, turnosPorFecha);

        } catch (error) {
            console.error(error);
            calendarioContainer.innerHTML = `
                <div class="alert alert-danger mt-2" role="alert">
                    Error al comunicarse con el servidor.
                </div>
            `;
        }
    }

    function renderCalendario(year, month, datosPorFecha) {
        // Creamos una tabla tipo calendario (lunes a domingo)
        const primerDia = new Date(year, month - 1, 1);
        const diaSemana = primerDia.getDay(); // 0=Dom, 1=Lun, ...
        const diasEnMes = new Date(year, month, 0).getDate();

        const hoyStr = hoyYmd();

        // Queremos que la semana arranque en lunes
        const offsetLunes = diaSemana === 0 ? 6 : diaSemana - 1; // cuántos días vacíos antes del 1

        let html = `
          <table class="table table-bordered calendar-table align-middle mb-0">
            <thead class="table-light">
              <tr class="text-center">
                <th style="width:14.28%;">Lun</th>
                <th style="width:14.28%;">Mar</th>
                <th style="width:14.28%;">Mié</th>
                <th style="width:14.28%;">Jue</th>
                <th style="width:14.28%;">Vie</th>
                <th style="width:14.28%;">Sáb</th>
                <th style="width:14.28%;">Dom</th>
              </tr>
            </thead>
            <tbody>
        `;

        let dia = 1;
        let celda = 0;

        // Mientras no renderizamos todos los días del mes
        while (dia <= diasEnMes) {
            html += '<tr>';

            for (let col = 0; col < 7; col++) {
                if (celda < offsetLunes || dia > diasEnMes) {
                    html += '<td class="bg-light-subtle"></td>';
                } else {
                    const fechaStr = `${year}-${pad(month)}-${pad(dia)}`;
                    const turnosDia = datosPorFecha[fechaStr] || [];

                    // contamos por estado
                    let asignados = 0, confirmados = 0, finalizados = 0;
                    turnosDia.forEach(t => {
                        if (t.estadoAsignacion === 'Asignado') asignados++;
                        if (t.estadoAsignacion === 'Confirmado') confirmados++;
                        if (t.estadoAsignacion === 'Finalizado') finalizados++;
                    });

                    const esHoy = (fechaStr === hoyStr);

                    html += `
                      <td 
                        class="calendar-cell align-top ${turnosDia.length ? 'calendar-cell-clickable' : ''} ${esHoy ? 'border border-primary' : ''}"
                        data-fecha="${fechaStr}"
                      >
                        <div class="d-flex justify-content-between">
                          <span class="fw-semibold">${dia}</span>
                          ${esHoy ? '<span class="badge bg-primary">Hoy</span>' : ''}
                        </div>
                    `;

                    if (turnosDia.length) {
                        html += `<div class="mt-1">`;
                        if (asignados > 0) {
                            html += `<span class="badge bg-warning text-dark me-1 mb-1">A: ${asignados}</span>`;
                        }
                        if (confirmados > 0) {
                            html += `<span class="badge bg-info text-dark me-1 mb-1">C: ${confirmados}</span>`;
                        }
                        if (finalizados > 0) {
                            html += `<span class="badge bg-success me-1 mb-1">F: ${finalizados}</span>`;
                        }
                        html += `</div>`;
                    } else {
                        html += `<div class="mt-1 text-muted small">Sin turnos</div>`;
                    }

                    html += `</td>`;
                    dia++;
                }
                celda++;
            }

            html += '</tr>';
        }

        html += '</tbody></table>';

        calendarioContainer.innerHTML = html;

        // Asignamos eventos de click para las celdas con turnos
        const celdas = calendarioContainer.querySelectorAll('.calendar-cell-clickable');
        celdas.forEach(td => {
            td.addEventListener('click', () => {
                const fechaStr = td.getAttribute('data-fecha');
                mostrarDetalleDia(fechaStr);
            });
        });
    }

    function mostrarDetalleDia(fechaStr) {
        if (!turnosPorFecha[fechaStr] || !turnosPorFecha[fechaStr].length) {
            return;
        }

        const turnosDia = turnosPorFecha[fechaStr];

        if (detalleFechaSeleccionada) {
            detalleFechaSeleccionada.textContent = `Fecha: ${formatearDMY(fechaStr)}`;
        }

        if (tablaDetalleDiaBody) {
            tablaDetalleDiaBody.innerHTML = '';

            turnosDia.forEach(t => {
                const tr = document.createElement('tr');

                const tdTurno = document.createElement('td');
                tdTurno.textContent = t.nombreTurno;
                tr.appendChild(tdTurno);

                const tdHora = document.createElement('td');
                tdHora.textContent = `${t.horaDesde.substring(0,5)} - ${t.horaHasta.substring(0,5)}`;
                tr.appendChild(tdHora);

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

                tablaDetalleDiaBody.appendChild(tr);
            });
        }

        if (modalDetalle) {
            const modal = new bootstrap.Modal(modalDetalle);
            modal.show();
        }
    }

    // Carga inicial
    cargarTurnosMes();
});
