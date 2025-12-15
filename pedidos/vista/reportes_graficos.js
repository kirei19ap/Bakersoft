// reportes_graficos.js

document.addEventListener('DOMContentLoaded', function () {
    const canvasEstados = document.getElementById('chartEstadosPedidos');
    const canvasPorDia = document.getElementById('chartPedidosPorDia');
    const canvasFacturacion = document.getElementById('chartFacturacionPorDia');

    // Canvas opcional (si lo agregaste en el front)
    const canvasTopProductos = document.getElementById('chartProductosMasVendidos');

    // Si no estamos en la pantalla de reportes principal, no hacemos nada
    if (!canvasEstados || !canvasPorDia || !canvasFacturacion) {
        return;
    }

    const inputDesde = document.getElementById('fechaDesde');
    const inputHasta = document.getElementById('fechaHasta');
    const btnFiltros = document.getElementById('btnAplicarFiltrosPedidos');

    let chartEstados = null;
    let chartPorDia = null;
    let chartFacturacion = null;

    function getRangoFechas() {
        const desde = inputDesde ? (inputDesde.value || '') : '';
        const hasta = inputHasta ? (inputHasta.value || '') : '';
        return { desde, hasta };
    }

    function buildParams(desde, hasta) {
        const params = new URLSearchParams();
        if (desde) params.append('desde', desde);
        if (hasta) params.append('hasta', hasta);
        return params;
    }

    function cargarReportes() {
        const { desde, hasta } = getRangoFechas();
        const params = buildParams(desde, hasta);

        // --- 1) Pedidos por estado ---
        fetch(`../controlador/controladorPedidos.php?accion=resumenEstados&${params.toString()}`)
            .then(r => r.json())
            .then(data => {
                const labels = data.map(item => item.descEstado || 'Sin estado');
                const valores = data.map(item => parseInt(item.cantidad || 0, 10));

                // Total de pedidos del rango
                const total = valores.reduce((acum, val) => acum + val, 0);

                // Convertimos a porcentajes
                const porcentajes = valores.map(v => total > 0 ? ((v * 100) / total).toFixed(1) : 0);

                if (chartEstados) {
                    chartEstados.data.labels = labels;
                    chartEstados.data.datasets[0].data = porcentajes;
                    chartEstados.update();
                } else {
                    chartEstados = new Chart(canvasEstados, {
                        type: 'doughnut',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: '% de pedidos',
                                data: porcentajes
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom' },
                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            const label = context.label || '';
                                            const value = context.raw || 0;
                                            return `${label}: ${value}%`;
                                        }
                                    }
                                }
                            },
                            layout: { padding: 10 }
                        }
                    });
                }
            })
            .catch(err => console.error('Error resumenEstados:', err));

        // --- 2) Pedidos por día ---
        fetch(`../controlador/controladorPedidos.php?accion=resumenPorDia&${params.toString()}`)
            .then(r => r.json())
            .then(data => {
                const labels = data.map(item => item.fecha);
                const valores = data.map(item => parseInt(item.cantidad || 0, 10));

                if (chartPorDia) {
                    chartPorDia.data.labels = labels;
                    chartPorDia.data.datasets[0].data = valores;
                    chartPorDia.update();
                } else {
                    chartPorDia = new Chart(canvasPorDia, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Pedidos por día',
                                data: valores,
                                tension: 0.2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: { beginAtZero: true, precision: 0 }
                            },
                            layout: { padding: 10 }
                        }
                    });
                }
            })
            .catch(err => console.error('Error resumenPorDia:', err));

        // --- 3) Facturación por día ---
        fetch(`../controlador/controladorPedidos.php?accion=resumenFacturacion&${params.toString()}`)
            .then(r => r.json())
            .then(data => {
                const labels = data.map(item => item.fecha);
                const valores = data.map(item => parseFloat(item.total || 0));

                if (chartFacturacion) {
                    chartFacturacion.data.labels = labels;
                    chartFacturacion.data.datasets[0].data = valores;
                    chartFacturacion.update();
                } else {
                    chartFacturacion = new Chart(canvasFacturacion, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Facturación por día',
                                data: valores
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: { y: { beginAtZero: true } },
                            layout: { padding: 10 }
                        }
                    });
                }
            })
            .catch(err => console.error('Error resumenFacturacion:', err));
    }

    async function cargarTopProductos(desde, hasta) {
        // Si no existe el canvas, igual puede existir la tabla; no rompemos nada.
        const url = `../controlador/controladorPedidos.php?accion=resumenProductos&desde=${encodeURIComponent(desde)}&hasta=${encodeURIComponent(hasta)}&limit=10`;
        const resp = await fetch(url);
        const data = await resp.json();

        const labels = data.map(x => x.nombre);
        const cantidades = data.map(x => parseFloat(x.cantidad_total || 0));
        const facturacion = data.map(x => parseFloat(x.facturacion_total || 0));

        // Tabla (si existe)
        const tbody = document.querySelector('#tablaTopProductos tbody');
        if (tbody) {
            if (!data.length) {
                tbody.innerHTML = `<tr><td colspan="3" class="text-muted text-center">Sin resultados para el rango seleccionado.</td></tr>`;
            } else {
                tbody.innerHTML = data.map(x => {
                    const cant = (x.cantidad_total ?? '0').toString();
                    const fac = Number(x.facturacion_total || 0).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    return `
                        <tr>
                            <td>${x.nombre}</td>
                            <td class="text-end">${cant} ${x.unidad_medida || ''}</td>
                            <td class="text-end">$ ${fac}</td>
                        </tr>
                    `;
                }).join('');
            }
        }

        // Chart (si existe el canvas)
        if (!canvasTopProductos) return;

        if (window._chartTopProductos) {
            window._chartTopProductos.destroy();
        }

        window._chartTopProductos = new Chart(canvasTopProductos, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Cantidad vendida',
                    data: cantidades
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            afterLabel: (context) => {
                                const idx = context.dataIndex;
                                const fac = Number(facturacion[idx] || 0).toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                return `Facturación: $ ${fac}`;
                            }
                        }
                    }
                },
                scales: { y: { beginAtZero: true } }
            }
        });
    }

    function cargarTodo() {
        const { desde, hasta } = getRangoFechas();
        cargarReportes();
        // Top productos sólo si agregaste el bloque (tabla o canvas)
        const existeTop = document.getElementById('tablaTopProductos') || document.getElementById('chartProductosMasVendidos');
        if (existeTop) {
            cargarTopProductos(desde, hasta);
        }
    }

    // Cargar al entrar
    cargarTodo();

    // Recalcular al aplicar filtros
    if (btnFiltros) {
        btnFiltros.addEventListener('click', function () {
            cargarTodo();
        });
    }
});
