(function () {
    const API = `${moduleRoot}/controlador/estadisticas_empleados.php`;

    // Helpers
    const $ = sel => document.querySelector(sel);
    function setText(id, val) { const el = document.getElementById(id); if (el) el.textContent = val; }
    function niceNumber(n) { return (n ?? 0).toLocaleString('es-AR'); }
    function nowStr() {
        const d = new Date();
        return d.toLocaleString('es-AR', { dateStyle: 'short', timeStyle: 'short' });
    }

    function toPercents(arr) {
        const total = arr.reduce((a, b) => a + (Number(b) || 0), 0) || 1;
        return arr.map(v => +((v * 100) / total).toFixed(1)); // 1 decimal
    }

    function makePiePercent(ctx, labels, counts, title) {
        const percents = toPercents(counts);

        return new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels,
                datasets: [{
                    label: title,
                    data: percents, // usamos % como datos
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        top: 10,
                        bottom: 25, // deja espacio para la leyenda
                        left: 10,
                        right: 10
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            // Muestra "Etiqueta (xx%)" en la leyenda
                            generateLabels(chart) {
                                const data = chart.data;
                                const ds = data.datasets[0] || { data: [] };
                                return data.labels.map((label, i) => {
                                    const value = ds.data[i] ?? 0;
                                    const base = Chart.overrides.doughnut.plugins.legend.labels.generateLabels(chart)[i];
                                    base.text = `${label} (${value}%)`;
                                    return base;
                                });
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            // Tooltip: "Etiqueta: xx%"
                            label(ctx) {
                                const lbl = ctx.label || '';
                                const val = (ctx.parsed !== undefined) ? ctx.parsed : ctx.raw;
                                return `${lbl}: ${val}%`;
                            }
                        }
                    }
                }
            }
        });
    }

    // Render KPIs
    function renderKPIs(k) {
        setText('kpiActivos', niceNumber(k.activos));
        setText('kpiInactivos', niceNumber(k.inactivos));
        setText('kpiAltas30', niceNumber(k.altas_30d));
        setText('kpiAntigProm', (k.antiguedad_prom ?? 0).toFixed(1));
        setText('lastUpdate', `Actualizado: ${nowStr()}`);
    }

    // Chart factory (usa colores por defecto de Chart.js)
    function makeBar(ctx, labels, data, title) {
        return new Chart(ctx, {
            type: 'bar',
            data: { labels, datasets: [{ label: title, data }] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        top: 10,
                        bottom: 25, // deja espacio para la leyenda
                        left: 10,
                        right: 10
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 14,
                            padding: 10,
                            font: {
                                size: 12
                            }
                        }
                    },
                    tooltip: { enabled: true }
                }
            }

        });
    }
    function makePie(ctx, labels, data, title) {
        return new Chart(ctx, {
            type: 'doughnut',
            data: { labels, datasets: [{ label: title, data }] },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });
    }

    // Fetch + render
    function loadStats() {
        fetch(API, { method: 'GET' })
            .then(r => r.json())
            .then(json => {
                if (!json || json.ok !== true) throw new Error(json && json.error || 'Error al cargar estadísticas');

                renderKPIs(json.kpis);

                // Altas por mes
                makeBar($('#chAltasMes'), json.altas_por_mes.labels, json.altas_por_mes.data, 'Altas');

                // Por puesto (si hay más de 10 en back ya vienen top 10)
                makePiePercent($('#chPorPuesto'), json.por_puesto.labels, json.por_puesto.data, 'Puestos');

                // Estado
                makeBar($('#chPorEstado'), json.por_estado.labels, json.por_estado.data, 'Estado');

                // Género
                makePiePercent($('#chPorGenero'), json.por_genero.labels, json.por_genero.data, 'Género');

            })
            .catch(err => {
                console.error(err);
                alert('No se pudieron cargar las estadísticas.');
            });
    }

    document.addEventListener('DOMContentLoaded', loadStats);
})();
