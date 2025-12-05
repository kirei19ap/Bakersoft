// pedidos/vista/pedidos.js

document.addEventListener('DOMContentLoaded', () => {
    // ====== DataTable en pantalla de listado de pedidos ======
    const tablaPedidos = document.getElementById('tablaPedidos');
    if (tablaPedidos && window.DataTable) {
        new DataTable('#tablaPedidos', {
            responsive: true,
            language: {
                "decimal": ",",
                "thousands": ".",
                "info": "Mostrando _END_ registros de un total de _TOTAL_",
                "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                "infoFiltered": "(filtrado de un total de _MAX_ registros)",
                "loadingRecords": "Cargando...",
                "lengthMenu": "Mostrar _MENU_",
                "paginate": {
                    "first": "<<",
                    "last": ">>",
                    "next": ">",
                    "previous": "<"
                },
                "search": "Buscador:",
                "searchPlaceholder": "Buscar...",
                "emptyTable": "No hay registros para mostrar en la tabla"
            },
            pageLength: 10,
            scrollX: false,
            order: [[0, 'desc']]
        });

        // === Confirmación con SweetAlert para cambio de estado ===
        const formsEstado = document.querySelectorAll('form.form-accion-estado');

        formsEstado.forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const boton = form.querySelector('button[type="submit"]');
                const accionTexto = boton
                    ? (boton.getAttribute('data-accion') || boton.textContent).trim()
                    : 'esta acción';

                Swal.fire({
                    title: `¿Esta seguro que desea ${accionTexto.toLowerCase()} el pedido?`,
                    text: 'Esta acción cambiará el estado del pedido.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Continuar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    }


    // ====== Lógica del formulario de nuevo pedido (crear.php) ======
    const form = document.getElementById('formPedido');
    if (!form) {
        // No estamos en la pantalla de creación de pedido
        return;
    }

    const tablaDetalle = document.getElementById('tablaDetallePedido');
    const tbody = tablaDetalle.querySelector('tbody');
    const btnAgregarLinea = document.getElementById('btnAgregarLinea');
    const totalInput = document.getElementById('totalPedido');
    // ====== LÓGICA DE CLIENTE: buscador y selección ======
    const inputBusquedaCliente = document.getElementById('busquedaCliente');
    const btnBuscarCliente = document.getElementById('btnBuscarCliente');
    const btnNuevoCliente = document.getElementById('btnNuevoCliente');
    const divResultadosCliente = document.getElementById('resultadosBusquedaCliente');
    const tbodyResultadosCliente = document.getElementById('tablaResultadosCliente');
    const inputIdCliente = document.getElementById('idCliente');
    const inputModoCliente = document.getElementById('modoCliente');
    const bloqueClienteSeleccionado = document.getElementById('bloqueClienteSeleccionado');
    const spanClienteSeleccionado = document.getElementById('clienteSeleccionado');

    const inputNombreCliente = document.getElementById('clienteNombre');
    const inputTelefonoCliente = document.getElementById('clienteTelefono');
    const inputEmailCliente = document.getElementById('clienteEmail');
    const inputCalleCliente = document.getElementById('clienteCalle');
    const inputAlturaCliente = document.getElementById('clienteAltura');

    function limpiarSeleccionCliente() {
        if (inputIdCliente) inputIdCliente.value = '';
        if (inputModoCliente) inputModoCliente.value = 'nuevo';
        if (bloqueClienteSeleccionado) bloqueClienteSeleccionado.style.display = 'none';
        if (spanClienteSeleccionado) spanClienteSeleccionado.textContent = '';
    }

    function limpiarResultadosBusqueda() {
        if (tbodyResultadosCliente) {
            tbodyResultadosCliente.innerHTML = '';
        }
        if (divResultadosCliente) {
            divResultadosCliente.style.display = 'none';
        }
    }

    function cargarDatosClienteEnFormulario(cliente) {
        if (!cliente) return;

        if (inputIdCliente) inputIdCliente.value = cliente.id_cliente;
        if (inputModoCliente) inputModoCliente.value = 'existente';

        if (inputNombreCliente) inputNombreCliente.value = cliente.nombre || '';
        if (inputTelefonoCliente) inputTelefonoCliente.value = cliente.telefono || '';
        if (inputEmailCliente) inputEmailCliente.value = cliente.email || '';
        if (inputCalleCliente) inputCalleCliente.value = cliente.calle || '';
        if (inputAlturaCliente) inputAlturaCliente.value = cliente.altura || '';

        if (spanClienteSeleccionado) {
            spanClienteSeleccionado.textContent = cliente.nombre || '';
        }
        if (bloqueClienteSeleccionado) {
            bloqueClienteSeleccionado.style.display = 'inline-block';
        }
    }

    if (btnBuscarCliente && inputBusquedaCliente && tbodyResultadosCliente && divResultadosCliente) {
        btnBuscarCliente.addEventListener('click', () => {
            const termino = inputBusquedaCliente.value.trim();

            if (termino.length < 2) {
                alert('Ingrese al menos 2 caracteres para buscar un cliente.');
                return;
            }

            limpiarResultadosBusqueda();
            limpiarSeleccionCliente();

            fetch(`../controlador/controladorPedidos.php?accion=buscarClientes&q=${encodeURIComponent(termino)}`)
                .then(resp => resp.json())
                .then(data => {
                    if (!Array.isArray(data) || data.length === 0) {
                        divResultadosCliente.style.display = 'block';
                        tbodyResultadosCliente.innerHTML = `
                            <tr>
                                <td colspan="4" class="text-muted text-center">
                                    No se encontraron clientes para el criterio ingresado.
                                </td>
                            </tr>`;
                        return;
                    }

                    const filas = data.map(cli => {
                        const direccion = `${cli.calle || ''} ${cli.altura || ''}`.trim();
                        return `
                            <tr class="fila-resultado-cliente" data-id="${cli.id_cliente}"
                                data-nombre="${cli.nombre || ''}"
                                data-email="${cli.email || ''}"
                                data-telefono="${cli.telefono || ''}"
                                data-calle="${cli.calle || ''}"
                                data-altura="${cli.altura || ''}">
                                <td>${cli.nombre || ''}</td>
                                <td>${cli.telefono || ''}</td>
                                <td>${cli.email || ''}</td>
                                <td>${direccion}</td>
                            </tr>
                        `;
                    }).join('');

                    tbodyResultadosCliente.innerHTML = filas;
                    divResultadosCliente.style.display = 'block';
                })
                .catch(err => {
                    console.error('Error al buscar clientes:', err);
                    alert('Ocurrió un error al buscar clientes.');
                });
        });
    }

    if (tbodyResultadosCliente) {
        tbodyResultadosCliente.addEventListener('click', (e) => {
            const fila = e.target.closest('.fila-resultado-cliente');
            if (!fila) return;

            const cliente = {
                id_cliente: fila.getAttribute('data-id'),
                nombre: fila.getAttribute('data-nombre'),
                email: fila.getAttribute('data-email'),
                telefono: fila.getAttribute('data-telefono'),
                calle: fila.getAttribute('data-calle'),
                altura: fila.getAttribute('data-altura')
            };

            cargarDatosClienteEnFormulario(cliente);
        });
    }

    if (btnNuevoCliente) {
        btnNuevoCliente.addEventListener('click', () => {
            limpiarResultadosBusqueda();
            limpiarSeleccionCliente();

            if (inputNombreCliente) inputNombreCliente.value = '';
            if (inputTelefonoCliente) inputTelefonoCliente.value = '';
            if (inputEmailCliente) inputEmailCliente.value = '';
            if (inputCalleCliente) inputCalleCliente.value = '';
            if (inputAlturaCliente) inputAlturaCliente.value = '';

            if (inputNombreCliente) {
                inputNombreCliente.focus();
            }
        });
    }

    // Clona la primera fila como plantilla
    function agregarFilaDetalle() {
        const filaBase = tbody.querySelector('tr.fila-detalle');
        if (!filaBase) return;

        const nuevaFila = filaBase.cloneNode(true);

        // Limpiar valores de la nueva fila
        nuevaFila.querySelectorAll('input, select').forEach(el => {
            if (el.tagName === 'SELECT') {
                el.selectedIndex = 0;
            } else {
                el.value = '';
            }
        });

        tbody.appendChild(nuevaFila);
    }

    function recalcularTotales() {
        let total = 0;
        const filas = tbody.querySelectorAll('tr');

        filas.forEach(fila => {
            const inputCantidad = fila.querySelector('.campo-cantidad');
            const inputPrecio = fila.querySelector('.campo-precio');
            const inputSubtotal = fila.querySelector('.campo-subtotal');

            const cantidad = parseFloat(inputCantidad.value) || 0;
            const precio = parseFloat(inputPrecio.value) || 0;
            const subtotal = cantidad * precio;

            inputSubtotal.value = subtotal > 0 ? subtotal.toFixed(2) : '';
            total += subtotal;
        });

        totalInput.value = total.toFixed(2);
    }

    // Evento: agregar nueva línea
    btnAgregarLinea.addEventListener('click', () => {
        agregarFilaDetalle();
    });

    // Delegación de eventos en el tbody
    tbody.addEventListener('change', (e) => {
        // Si cambia el producto, podemos sugerir precio
        if (e.target.classList.contains('campo-producto')) {
            const select = e.target;
            const fila = select.closest('tr');
            const inputPrecio = fila.querySelector('.campo-precio');

            const opcion = select.selectedOptions[0];
            if (opcion) {
                const precioSugerido = opcion.getAttribute('data-precio');
                if (precioSugerido && !inputPrecio.value) {
                    inputPrecio.value = parseFloat(precioSugerido).toFixed(2);
                }
            }
            recalcularTotales();
        }
    });

    tbody.addEventListener('input', (e) => {
        if (e.target.classList.contains('campo-cantidad') ||
            e.target.classList.contains('campo-precio')) {
            recalcularTotales();
        }
    });

    tbody.addEventListener('click', (e) => {
        const btn = e.target.closest('.btnEliminarFila');
        if (!btn) return;

        const fila = btn.closest('tr');
        const filas = tbody.querySelectorAll('tr');

        if (filas.length > 1) {
            fila.remove();
        } else {
            // Si es la única fila, sólo la limpiamos
            fila.querySelectorAll('input, select').forEach(el => {
                if (el.tagName === 'SELECT') {
                    el.selectedIndex = 0;
                } else {
                    el.value = '';
                }
            });
        }
        recalcularTotales();
    });

    // Validación antes de enviar
    // Validación antes de enviar
    form.addEventListener('submit', (e) => {
        const nombreCliente = form.clienteNombre.value.trim();
        const modoCliente = inputModoCliente ? inputModoCliente.value : 'nuevo';
        const idCliHidden = inputIdCliente ? parseInt(inputIdCliente.value || '0', 10) : 0;

        if (modoCliente === 'existente') {
            if (!idCliHidden || idCliHidden <= 0) {
                e.preventDefault();
                alert('Debe seleccionar un cliente válido de la búsqueda o registrar uno nuevo.');
                return;
            }
        } else {
            // Cliente nuevo
            if (!nombreCliente) {
                e.preventDefault();
                alert('Debe indicar al menos el nombre del cliente.');
                return;
            }
        }

        const filas = tbody.querySelectorAll('tr');
        let hayDetalleValido = false;

        filas.forEach(fila => {
            const selectProd = fila.querySelector('.campo-producto');
            const inputCant = fila.querySelector('.campo-cantidad');

            const idProd = selectProd ? selectProd.value : '';
            const cant = inputCant ? (parseFloat(inputCant.value) || 0) : 0;

            if (idProd && cant > 0) {
                hayDetalleValido = true;
            }
        });

        if (!hayDetalleValido) {
            e.preventDefault();
            alert('Debe agregar al menos un producto con cantidad mayor a cero.');
            return;
        }
    });


    // Recalcular totales por si hay valores iniciales
    recalcularTotales();
});
