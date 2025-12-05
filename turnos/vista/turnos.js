// turnos/vista/turnos.js

document.addEventListener('DOMContentLoaded', () => {
    const inputFechaDesde = document.getElementById('fechaDesde');
    const inputFechaHasta = document.getElementById('fechaHasta');
    const selectTurno = document.getElementById('turno');
    const btnCargar = document.getElementById('btnCargarOperarios');
    const btnGuardar = document.getElementById('btnGuardarAsignaciones');
    const tabla = document.getElementById('tablaTurnosOperarios');
    const tbody = tabla ? tabla.querySelector('tbody') : null;
    const headerRow = document.getElementById('headerTurnos');
    const rangoSemanaTexto = document.getElementById('rangoSemanaTexto');

    if (!tabla || !tbody || !headerRow) return;

    let rangoActual = null;      // { inicio, fin, dias[] }
    let empleadosRango = [];     // data retornada por el backend

    // Utilities
    function formatearFechaDMY(yyyy_mm_dd) {
        if (!yyyy_mm_dd) return '';
        const [y, m, d] = yyyy_mm_dd.split('-');
        return `${d}-${m}-${y}`;
    }

    if (inputFechaDesde || inputFechaHasta) {
        const actualizarLeyenda = () => {
            if (inputFechaDesde.value && inputFechaHasta.value) {
                rangoSemanaTexto.textContent =
                    `El rango seleccionado es del ${formatearFechaDMY(inputFechaDesde.value)} al ${formatearFechaDMY(inputFechaHasta.value)} (máx. 7 días).`;
            } else {
                rangoSemanaTexto.textContent = 'Seleccioná un rango de hasta 7 días.';
            }
        };
        if (inputFechaDesde) inputFechaDesde.addEventListener('change', actualizarLeyenda);
        if (inputFechaHasta) inputFechaHasta.addEventListener('change', actualizarLeyenda);
    }

    // Click en "Cargar operarios"
    if (btnCargar) {
        btnCargar.addEventListener('click', async () => {
            const fechaDesde = inputFechaDesde.value;
            const fechaHasta = inputFechaHasta.value;
            const idTurno = selectTurno.value;

            if (!fechaDesde || !fechaHasta || !idTurno) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'Debés seleccionar fecha desde, fecha hasta y un turno.',
                });
                return;
            }

            try {
                const formData = new FormData();
                formData.append('fechaDesde', fechaDesde);
                formData.append('fechaHasta', fechaHasta);
                formData.append('idTurno', idTurno);

                const resp = await fetch('cargar_operarios.php', {
                    method: 'POST',
                    body: formData
                });

                const json = await resp.json();

                if (!json.ok) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: json.mensaje || 'No se pudieron cargar los operarios.',
                    });
                    return;
                }

                rangoActual = json.semana;   // mantiene el nombre "semana" en el backend
                empleadosRango = json.data || [];

                actualizarHeaderRango(rangoActual);
                renderTabla(empleadosRango, rangoActual);

                btnGuardar.disabled = empleadosRango.length === 0;

            } catch (error) {
                console.error('Error al cargar operarios:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al comunicarse con el servidor.',
                });
            }
        });
    }

    // Click en "Guardar asignaciones"
    if (btnGuardar) {
        btnGuardar.addEventListener('click', async () => {
            if (!rangoActual || !empleadosRango.length) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'Primero debés cargar los operarios del rango.',
                });
                return;
            }

            const fechaDesde = inputFechaDesde.value;
            const fechaHasta = inputFechaHasta.value;
            const idTurno = selectTurno.value;

            if (!fechaDesde || !fechaHasta || !idTurno) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'Debés seleccionar un rango de fechas y un turno válidos.',
                });
                return;
            }

            // Armamos el payload en base a los checkboxes
            const payloadAsignaciones = construirPayloadAsignaciones(rangoActual);

            try {
                const resp = await fetch('guardar_asignaciones.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        fechaBase: fechaDesde, // se usa sólo para validaciones simples en el backend
                        idTurno: idTurno,
                        asignaciones: payloadAsignaciones
                    })
                });

                const json = await resp.json();

                if (!json.ok) {
                    console.warn('Conflictos:', json.conflictos, 'Errores:', json.errores);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atención',
                        text: json.mensaje || 'Se detectaron problemas al guardar. Revisá la planificación.',
                    });
                    return;
                }

                Swal.fire({
                    icon: 'success',
                    title: 'OK',
                    text: json.mensaje || 'Asignaciones guardadas correctamente.',
                });

            } catch (error) {
                console.error('Error al guardar asignaciones:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al guardar las asignaciones.',
                });
            }
        });
    }

    /**
     * Actualiza el header de la tabla con columnas dinámicas según el rango:
     * Legajo, Empleado, Puesto, Estado empleado, [Fecha1], [Fecha2], ...
     */
    function actualizarHeaderRango(rango) {
        if (!rango || !rango.dias) return;

        headerRow.innerHTML = '';

        // Columnas fijas
        const colsFijas = [
            { text: 'Legajo', style: 'width:8%;' },
            { text: 'Empleado', style: 'width:25%;' },
            { text: 'Puesto', style: 'width:15%;' },
            { text: 'Estado empleado', style: 'width:12%;' },
        ];

        colsFijas.forEach(col => {
            const th = document.createElement('th');
            th.style = col.style;
            th.textContent = col.text;
            headerRow.appendChild(th);
        });

        // Columnas dinámicas por día
        rango.dias.forEach(dia => {
            const th = document.createElement('th');
            th.className = 'text-center';

            // dia.fecha viene como "YYYY-MM-DD"
            const [y, m, d] = dia.fecha.split('-');
            const dd = d.padStart(2, '0');
            const mm = m.padStart(2, '0');

            th.innerHTML = `${dia.etiqueta}<br><small>${dd}/${mm}</small>`;
            headerRow.appendChild(th);
        });


        // Texto de ayuda
        if (rangoSemanaTexto) {
            rangoSemanaTexto.textContent =
                `Rango del ${formatearFechaDMY(rango.inicio)} al ${formatearFechaDMY(rango.fin)}.`;
        }
    }

    /**
     * Renderiza el cuerpo de la tabla: filas = empleados, columnas = días del rango.
     */
    function renderTabla(empleados, rango) {
        tbody.innerHTML = '';

        if (!empleados.length) {
            const tr = document.createElement('tr');
            const td = document.createElement('td');
            td.colSpan = 4 + (rango.dias ? rango.dias.length : 0);
            td.className = 'text-center text-muted';
            td.textContent = 'No hay operarios para mostrar en el rango seleccionado.';
            tr.appendChild(td);
            tbody.appendChild(tr);
            return;
        }

        const fechasRango = rango.dias.map(d => d.fecha);

        empleados.forEach(emp => {
            const tr = document.createElement('tr');
            tr.dataset.idEmpleado = emp.id_empleado;

            // Legajo
            let td = document.createElement('td');
            td.textContent = emp.legajo || '';
            tr.appendChild(td);

            // Empleado
            td = document.createElement('td');
            td.textContent = emp.nombreCompleto || (emp.apellido + ', ' + emp.nombre);
            tr.appendChild(td);

            // Puesto
            td = document.createElement('td');
            td.textContent = emp.descrPuesto || '';
            tr.appendChild(td);

            // Estado empleado
            td = document.createElement('td');
            if (emp.estadoEmpleado === 'Activo') {
                td.innerHTML = '<span class="badge bg-success">Activo</span>';
            } else {
                td.innerHTML = '<span class="badge bg-secondary">' + (emp.estadoEmpleado || '') + '</span>';
            }
            tr.appendChild(td);

            // Columnas de días
            fechasRango.forEach(fecha => {
                const estadoDia = (emp.asignaciones && emp.asignaciones[fecha]) || null;

                const tdDia = document.createElement('td');
                tdDia.className = 'text-center';

                const chk = document.createElement('input');
                chk.type = 'checkbox';
                chk.className = 'chk-asignado';
                chk.dataset.fecha = fecha;
                chk.dataset.idEmpleado = emp.id_empleado;

                if (estadoDia === 'Asignado') {
                    chk.checked = true;
                }

                tdDia.appendChild(chk);
                tr.appendChild(tdDia);
            });

            tbody.appendChild(tr);
        });
    }

    /**
     * Arma el payload de asignaciones:
     * [
     *   {
     *     idEmpleado: 1,
     *     porDia: {
     *       'YYYY-MM-DD': 'Asignado' | 'SinTurno',
     *       ...
     *     }
     *   },
     *   ...
     * ]
     */
    function construirPayloadAsignaciones(rango) {
        const filas = tbody.querySelectorAll('tr[data-id-empleado]');
        const fechas = rango.dias.map(d => d.fecha);
        const resultado = [];

        filas.forEach(tr => {
            const idEmpleado = parseInt(tr.dataset.idEmpleado, 10);
            if (!idEmpleado) return;

            const porDia = {};

            fechas.forEach(fecha => {
                const chk = tr.querySelector(`input.chk-asignado[data-fecha="${fecha}"]`);
                if (!chk) return;

                porDia[fecha] = chk.checked ? 'Asignado' : 'SinTurno';
            });

            resultado.push({
                idEmpleado: idEmpleado,
                porDia: porDia
            });
        });

        return resultado;
    }
});
