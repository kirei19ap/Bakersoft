document.addEventListener('DOMContentLoaded', () => {
    fetch('obtenerDatos.php')
        .then(response => response.json())
        .then(data => {
            document.getElementById('mp_total').innerText = data.materias_primas;
            document.getElementById('pedidos_mes').innerText = data.pedidos_mes;
            document.getElementById('proveedores').innerText = data.proveedores;
            document.getElementById('sin_stock').innerText = data.sin_stock;
        })
        .catch(error => console.error('Error al cargar los KPIs:', error));
        
});


document.addEventListener('DOMContentLoaded', () => {
    fetch('datosGraficos.php')
        .then(response => response.json())
        .then(data => {
            // === GRAFICO 1: Materia prima por stock ===
            const ctxPie = document.getElementById('stockPie').getContext('2d');
            new Chart(ctxPie, {
                type: 'pie',
                data: {
                    labels: ['Sobrestock', 'Stock Justo', 'Bajo Stock'],
                    datasets: [{
                        data: [
                            data.stock.sobrestock,
                            data.stock.en_stock_justo,
                            data.stock.bajo_stock
                        ],
                        backgroundColor: [
                            '#4caf50', // verde - sobrestock
                            '#ffeb3b', // amarillo - justo
                            '#f44336'  // rojo - bajo stock
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    aspectRatio: 2,
                    plugins: {
                        title: {
                            display: false,
                            text: 'Estado de Stock de Materias Primas'
                        }
                    }
                }
            });

            // === GRAFICO 2: Pedidos de los últimos 30 días ===
            const fechas = data.pedidos.map(item => item.fecha);
            const cantidades = data.pedidos.map(item => item.cantidad);

            const ctxLine = document.getElementById('pedidosLine').getContext('2d');
            new Chart(ctxLine, {
                type: 'line',
                data: {
                    labels: fechas,
                    datasets: [{
                        label: 'Pedidos por Día',
                        data: cantidades,
                        fill: true,
                        borderColor: '#42a5f5',
                        backgroundColor: 'rgba(66, 165, 245, 0.2)',
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: false,
                            text: 'Pedidos de Materia Prima (Últimos 30 días)'
                        }
                    }
                }
            });
        })
        .catch(err => console.error('Error cargando los gráficos:', err));
});
