document.addEventListener('DOMContentLoaded', function () {
    const canvasEstados = document.getElementById('chartEstadosPedidos');
    const canvasPorDia = document.getElementById('chartPedidosPorDia');
    const canvasFacturacion = document.getElementById('chartFacturacionPorDia');

    // Si no estamos en la pantalla de reportes, no hacemos nada
    if (!canvasEstados || !canvasPorDia || !canvasFacturacion) {
        return;
    }

    const inputDesde = document.getElementById('fechaDesde');
    const inputHasta = document.getElementById('fechaHasta');
    const btnFiltros = document.getElementById('btnAplicarFiltrosPedidos');

    let chartEstados = null;
    let chartPorDia = null;
    let chartFacturacion = null;

    function cargarReportes() {
        const desde = inputDesde ? inputDesde.value : '';
        const hasta = inputHasta ? inputHasta.value : '';

        const params = new URLSearchParams();
        if (desde) params.append('desde', desde);
        if (hasta) params.append('hasta', hasta);

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
                                legend: {
                                    position: 'bottom'
                                },
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
                            layout: {
                                padding: 10
                            }
                        }
                    });
                }
            })

            .catch(err => {
                console.error('Error resumenEstados:', err);
            });

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
                                y: {
                                    beginAtZero: true,
                                    precision: 0
                                }
                            },
                            layout: {
                                padding: 10
                            }
                        }
                    });

                }
            })
            .catch(err => {
                console.error('Error resumenPorDia:', err);
            });

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
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            },
                            layout: {
                                padding: 10
                            }
                        }
                    });

                }
            })
            .catch(err => {
                console.error('Error resumenFacturacion:', err);
            });
    }

    // Cargar al entrar
    cargarReportes();

    // Recalcular al aplicar filtros
    if (btnFiltros) {
        btnFiltros.addEventListener('click', function () {
            cargarReportes();
        });
    }
});
