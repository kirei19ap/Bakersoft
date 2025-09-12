let CH_ALTAS_BAJAS = null;
let CH_ESTADOS = null;
let CH_ROLES = null;

(function () {
    fetch('datosreportesUsuario.php', { cache: 'no-store' })
        .then(r => r.json())
        .then(drawCharts)
        .catch(err => console.error('Reportes Usuarios:', err));

    function drawCharts(data) {
        const meses = (data.series?.meses || []).map(m => m.replace('-', '/')); // "YYYY/MM"
        const altas = data.series?.altas || [];
        const bajas = data.series?.bajas || [];
        const tot = data.totales || { total: 0, activos: 0, inactivos: 0, eliminados: 0 };
        const roles = data.roles || [];

        // 1) Altas vs Bajas (barras apiladas)
        const ctx1 = document.getElementById('chartAltasBajas');
        if (CH_ALTAS_BAJAS) { CH_ALTAS_BAJAS.destroy(); }
        CH_ALTAS_BAJAS = new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: meses,
                datasets: [
                    { label: 'Altas', data: altas },
                    { label: 'Bajas', data: bajas }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                resizeDelay: 200,
                plugins: { legend: { position: 'top' } },
                scales: { x: { stacked: true }, y: { stacked: true, beginAtZero: true } }
            }
        });

        // 2) Estados actuales (doughnut) en %
        const ctx2 = document.getElementById('chartEstados');
        if (ctx2) {
            // destruir si ya existe
            if (CH_ESTADOS) { CH_ESTADOS.destroy(); }

            // Totales absolutos
            const act = +tot.activos || 0;
            const ina = +tot.inactivos || 0;
            const eli = +tot.eliminados || 0;
            const totalUsers = act + ina + eli;

            // Porcentajes (1 decimal)
            const toPct = v => totalUsers ? Math.round((v / totalUsers) * 1000) / 10 : 0;
            const estadosPct = [toPct(act), toPct(ina), toPct(eli)];
            const estadosAbs = [act, ina, eli]; // para tooltip

            CH_ESTADOS = new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: ['Activos', 'Inactivos', 'Eliminados'],
                    datasets: [{ data: estadosPct }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,  
                    resizeDelay: 200,            
                    cutout: '60%',
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => {
                                    const i = ctx.dataIndex;
                                    const pct = estadosPct[i].toLocaleString(undefined, { maximumFractionDigits: 1 });
                                    const abs = estadosAbs[i];
                                    return `${ctx.label}: ${pct}% (${abs})`;
                                }
                            }
                        }
                    }
                }
            });
        }


        // 3) Usuarios por Rol (barras)
        const ctx3 = document.getElementById('chartRoles');
        if (ctx3) {
            if (CH_ROLES) { CH_ROLES.destroy(); }  // evitar múltiples instancias

            CH_ROLES = new Chart(ctx3, {
                type: 'bar',
                data: {
                    labels: roles.map(r => r.rol),
                    datasets: [{ label: 'Usuarios', data: roles.map(r => r.cant) }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,  // respeta la altura fija del contenedor
                    resizeDelay: 200,            // frena bucles de resize
                    animation: { duration: 300 },// opcional: suaviza sin recalcular infinito
                    plugins: { legend: { display: false } },
                    layout: { padding: 8 },
                    scales: {
                        x: {
                            ticks: {
                                autoSkip: false,       
                                maxRotation: 45,
                                minRotation: 0
                            }
                        },
                        y: { beginAtZero: true, precision: 0 }
                    }
                }
            });
        }

    }
})();
