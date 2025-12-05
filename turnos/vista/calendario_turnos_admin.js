// turnos/vista/calendario_turnos_admin.js

document.addEventListener('DOMContentLoaded', () => {
    const calendarioContainer = document.getElementById('calendarioProdContainer');
    const tituloMes = document.getElementById('tituloMesProd');
    const btnMesAnterior = document.getElementById('btnMesAnteriorProd');
    const btnMesSiguiente = document.getElementById('btnMesSiguienteProd');
    const btnHoy = document.getElementById('btnHoyProd');

    const modalDetalle = document.getElementById('modalDetalleDiaProd');
    const detalleFecha = document.getElementById('detalleFechaProd');
    const tablaDetalleBody = document.getElementById('tablaDetalleDiaProdBody');

    if (!calendarioContainer) return;

    const hoy = new Date();
    let currentYear = hoy.getFullYear();
    let currentMonth = hoy.getMonth() + 1; // 1-12

    let turnosActivos = [];   // lista de turnos activos
    let resumenPorFecha = {}; // fecha -> { idTurno: {cantidad,...} }

    // Navegación de mes
    if (btnMesAnterior) {
        btnMesAnterior.addEventListener('click', () => {
            currentMonth--;
            if (currentMonth < 1) {
                currentMonth = 12;
                currentYear--;
            }
            cargarCalendario();
        });
    }

    if (btnMesSiguiente) {
        btnMesSiguiente.addEventListener('click', () => {
            currentMonth++;
            if (currentMonth > 12) {
                currentMonth = 1;
                currentYear++;
            }
            cargarCalendario();
        });
    }

    if (btnHoy) {
        btnHoy.addEventListener('click', () => {
            const now = new Date();
            currentYear = now.getFullYear();
            currentMonth = now.getMonth() + 1;
            cargarCalendario();
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

    function formatearDMY(ymd) {
        if (!ymd) return '';
        const [y,m,d] = ymd.split('-');
        return `${d}-${m}-${y}`;
    }

    function hoyYmd() {
        const f = new Date();
        return `${f.getFullYear()}-${pad(f.getMonth()+1)}-${pad(f.getDate())}`;
    }

    async function cargarCalendario() {
        const desde = `${currentYear}-${pad(currentMonth)}-01`;
        const ultimoDia = new Date(currentYear, currentMonth, 0).getDate();
        const hasta = `${currentYear}-${pad(currentMonth)}-${pad(ultimoDia)}`;

        if (tituloMes) {
            tituloMes.textContent = formatearMesLargo(currentYear, currentMonth);
        }

        const params = new URLSearchParams();
        params.append('desde', desde);
        params.append('hasta', hasta);

        try {
            const resp = await fetch('cargar_calendario_turnos.php?' + params.toString());
            const json = await resp.json();

            if (!json.ok) {
                calendarioContainer.innerHTML = `
                    <div class="alert alert-danger mt-2" role="alert">
                        ${json.mensaje || 'No se pudieron obtener los datos del calendario.'}
                    </div>
                `;
                return;
            }

            turnosActivos = json.turnos || [];
            const resumen = json.data || [];

            // Construimos mapa fecha -> mapa de turnos
            resumenPorFecha = {};
            resumen.forEach(r => {
                const f = r.fecha;
                if (!resumenPorFecha[f]) resumenPorFecha[f] = {};
                resumenPorFecha[f][r.idTurno] = {
                    cantidad: r.cantidad,
                    nombreTurno: r.nombreTurno,
                    horaDesde: r.horaDesde,
                    horaHasta: r.horaHasta
                };
            });

            renderCalendario(currentYear, currentMonth);

        } catch (error) {
            console.error(error);
            calendarioContainer.innerHTML = `
                <div class="alert alert-danger mt-2" role="alert">
                    Error al comunicarse con el servidor.
                </div>
            `;
        }
    }

    function renderCalendario(year, month) {
        const primerDia = new Date(year, month - 1, 1);
        const diaSemana = primerDia.getDay(); // 0=Dom, 1=Lun,...
        const diasEnMes = new Date(year, month, 0).getDate();
        const hoyStr = hoyYmd();

        // Semana arrancando en lunes
        const offsetLunes = (diaSemana === 0) ? 6 : (diaSemana - 1);

        let html = `
          <table class="table table-bordered align-middle mb-0">
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

        while (dia <= diasEnMes) {
            html += '<tr>';
            for (let col = 0; col < 7; col++) {
                if (celda < offsetLunes || dia > diasEnMes) {
                    html += '<td class="bg-light-subtle"></td>';
                } else {
                    const fechaStr = `${year}-${pad(month)}-${pad(dia)}`;
                    const resumenDia = resumenPorFecha[fechaStr] || {};

                    const esHoy = (fechaStr === hoyStr);

                    // Detectamos si hay algún turno sin cobertura
                    let hayTurnos = false;
                    let hayFaltantes = false;

                    turnosActivos.forEach(t => {
                        const info = resumenDia[t.idTurno];
                        if (info && info.cantidad > 0) {
                            hayTurnos = true;
                        } else {
                            // turno activo sin registros: faltante
                            hayFaltantes = true;
                        }
                    });

                    // Bordes según estado general del día
                    let clasesExtra = '';
                    if (turnosActivos.length && hayFaltantes) {
                        clasesExtra = 'border border-danger';
                    } else if (turnosActivos.length && hayTurnos) {
                        clasesExtra = 'border border-success';
                    }

                    html += `
                      <td class="align-top calendar-cell-prod ${turnosActivos.length ? 'calendar-cell-clickable-prod' : ''} ${clasesExtra} ${esHoy ? 'border-2' : ''}"
                          data-fecha="${fechaStr}">
                        <div class="d-flex justify-content-between">
                          <span class="fw-semibold">${dia}</span>
                          ${esHoy ? '<span class="badge bg-primary">Hoy</span>' : ''}
                        </div>
                        <div class="mt-1 small">
                    `;

                    if (!turnosActivos.length) {
                        html += `<span class="text-muted">Sin turnos definidos</span>`;
                    } else {
                        if (!Object.keys(resumenDia).length) {
                            html += `<span class="text-muted">Sin asignaciones</span>`;
                        } else {
                            turnosActivos.forEach(t => {
                                const info = resumenDia[t.idTurno];
                                const cant = info ? info.cantidad : 0;
                                if (cant > 0) {
                                    html += `
                                      <div class="mb-1">
                                        <span class="badge bg-success">
                                          ${t.nombre}: ${cant}
                                        </span>
                                      </div>
                                    `;
                                } else {
                                    html += `
                                      <div class="mb-1">
                                        <span class="badge bg-danger">
                                          ${t.nombre}: 0
                                        </span>
                                      </div>
                                    `;
                                }
                            });
                        }
                    }

                    html += `
                        </div>
                      </td>
                    `;

                    dia++;
                }
                celda++;
            }
            html += '</tr>';
        }

        html += '</tbody></table>';

        calendarioContainer.innerHTML = html;

        // Eventos de click en celdas
        const celdas = calendarioContainer.querySelectorAll('.calendar-cell-clickable-prod');
        celdas.forEach(td => {
            td.addEventListener('click', () => {
                const fechaStr = td.getAttribute('data-fecha');
                mostrarDetalleDia(fechaStr);
            });
        });
    }

    function mostrarDetalleDia(fechaStr) {
        if (!turnosActivos.length) return;

        const resumenDia = resumenPorFecha[fechaStr] || {};

        if (detalleFecha) {
            detalleFecha.textContent = `Fecha: ${formatearDMY(fechaStr)}`;
        }

        if (tablaDetalleBody) {
            tablaDetalleBody.innerHTML = '';

            turnosActivos.forEach(t => {
                const info = resumenDia[t.idTurno];
                const cant = info ? info.cantidad : 0;

                const tr = document.createElement('tr');

                const tdTurno = document.createElement('td');
                tdTurno.textContent = t.nombre;
                tr.appendChild(tdTurno);

                const tdHora = document.createElement('td');
                tdHora.textContent = `${t.horaDesde.substring(0,5)} - ${t.horaHasta.substring(0,5)}`;
                tr.appendChild(tdHora);

                const tdCant = document.createElement('td');
                tdCant.textContent = cant;
                tr.appendChild(tdCant);

                const tdEstado = document.createElement('td');
                if (cant > 0) {
                    tdEstado.innerHTML = '<span class="badge bg-success">Turno cubierto</span>';
                } else {
                    tdEstado.innerHTML = '<span class="badge bg-danger">Sin cobertura</span>';
                }
                tr.appendChild(tdEstado);

                tablaDetalleBody.appendChild(tr);
            });
        }

        if (modalDetalle) {
            const modal = new bootstrap.Modal(modalDetalle);
            modal.show();
        }
    }

    // Carga inicial
    cargarCalendario();
});
