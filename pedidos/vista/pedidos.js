// pedidos/vista/pedidos.js

function mostrarError(mensaje) {
  Swal.fire({
    icon: 'warning',
    title: 'Atención',
    text: mensaje,
    confirmButtonText: 'Entendido'
  });
}

function mostrarOk(mensaje) {
  Swal.fire({
    icon: 'success',
    title: 'Listo',
    text: mensaje,
    confirmButtonText: 'Continuar'
  });
}

/**
 * Helpers de validación visual (Bootstrap 5)
 * - is-invalid / is-valid
 * - agrega <div class="invalid-feedback">...</div> si no existe
 */
function ensureInvalidFeedback(el) {
  if (!el) return null;

  // Si el input está dentro de un .input-group, el feedback debe ir fuera del group
  const inputGroup = el.closest('.input-group');
  const container = inputGroup ? inputGroup.parentElement : el.parentElement;

  if (!container) return null;

  let fb = container.querySelector('.invalid-feedback[data-for="' + (el.id || '') + '"]');
  if (!fb) {
    fb = document.createElement('div');
    fb.className = 'invalid-feedback';
    if (el.id) fb.setAttribute('data-for', el.id);
    container.appendChild(fb);
  }
  return fb;
}

function setInvalid(el, msg) {
  if (!el) return;
  el.classList.remove('is-valid');
  el.classList.add('is-invalid');

  const fb = ensureInvalidFeedback(el);
  if (fb) fb.textContent = msg || 'Campo inválido';
}

function setValid(el) {
  if (!el) return;
  el.classList.remove('is-invalid');
  el.classList.add('is-valid');

  // No mostramos feedback de "ok" para no ensuciar el form
}

function clearValidation(el) {
  if (!el) return;
  el.classList.remove('is-invalid', 'is-valid');

  // No borramos el div feedback, solo el estado visual
}

function focusFirstInvalid(container) {
  if (!container) return;
  const first = container.querySelector('.is-invalid');
  if (first && typeof first.focus === 'function') first.focus();
}

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
        "paginate": { "first": "<<", "last": ">>", "next": ">", "previous": "<" },
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

  // ====== Lógica del formulario de nuevo pedido (crear.php / editar.php) ======
  const form = document.getElementById('formPedido');
  if (!form) return;

  const tablaDetalle = document.getElementById('tablaDetallePedido');
  const tbody = tablaDetalle ? tablaDetalle.querySelector('tbody') : null;
  const btnAgregarLinea = document.getElementById('btnAgregarLinea');
  const totalInput = document.getElementById('totalPedido');

  // ====== Cliente: buscador y selección ======
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

  // === Limpieza visual al tipear/cambiar (cliente) ===
  [inputNombreCliente, inputTelefonoCliente, inputEmailCliente, inputCalleCliente, inputAlturaCliente, inputBusquedaCliente].forEach(el => {
    if (!el) return;
    el.addEventListener('input', () => clearValidation(el));
    el.addEventListener('change', () => clearValidation(el));
  });

  function limpiarSeleccionCliente() {
    if (inputIdCliente) inputIdCliente.value = '';
    if (inputModoCliente) inputModoCliente.value = 'nuevo';
    if (bloqueClienteSeleccionado) bloqueClienteSeleccionado.style.display = 'none';
    if (spanClienteSeleccionado) spanClienteSeleccionado.textContent = '';

    // Cuando vuelvo a "nuevo", limpio validaciones de id/mode si existieran
    if (inputNombreCliente) clearValidation(inputNombreCliente);
  }

  function limpiarResultadosBusqueda() {
    if (tbodyResultadosCliente) tbodyResultadosCliente.innerHTML = '';
    if (divResultadosCliente) divResultadosCliente.style.display = 'none';
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

    if (spanClienteSeleccionado) spanClienteSeleccionado.textContent = cliente.nombre || '';
    if (bloqueClienteSeleccionado) bloqueClienteSeleccionado.style.display = 'inline-block';

    // Visualmente “ok”
    if (inputNombreCliente) setValid(inputNombreCliente);
  }

  // ===== Buscar cliente =====
  if (btnBuscarCliente && inputBusquedaCliente && tbodyResultadosCliente && divResultadosCliente) {
    btnBuscarCliente.addEventListener('click', () => {
      const termino = inputBusquedaCliente.value.trim();

      clearValidation(inputBusquedaCliente);

      if (termino.length < 2) {
        setInvalid(inputBusquedaCliente, 'Ingrese al menos 2 caracteres.');
        mostrarError('Ingrese al menos 2 caracteres para buscar un cliente.');
        inputBusquedaCliente.focus();
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
          mostrarError('Ocurrió un error al buscar clientes.');
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

  // ===== Registrar cliente desde pedido =====
  if (btnNuevoCliente) {
    btnNuevoCliente.addEventListener('click', () => {
      // Limpio estados previos
      [inputNombreCliente, inputTelefonoCliente, inputCalleCliente, inputAlturaCliente].forEach(el => clearValidation(el));

      const nombre = (inputNombreCliente?.value || '').trim();
      const telefono = (inputTelefonoCliente?.value || '').trim();
      const email = (inputEmailCliente?.value || '').trim();
      const calle = (inputCalleCliente?.value || '').trim();
      const altura = (inputAlturaCliente?.value || '').trim();

      // Reglas mínimas: nombre + teléfono + calle + altura
      let ok = true;

      if (!nombre) { setInvalid(inputNombreCliente, 'Nombre obligatorio.'); ok = false; }
      else setValid(inputNombreCliente);

      if (!telefono) { setInvalid(inputTelefonoCliente, 'Teléfono obligatorio.'); ok = false; }
      else setValid(inputTelefonoCliente);

      if (!calle) { setInvalid(inputCalleCliente, 'Calle obligatoria.'); ok = false; }
      else setValid(inputCalleCliente);

      if (!altura) { setInvalid(inputAlturaCliente, 'Altura obligatoria.'); ok = false; }
      else setValid(inputAlturaCliente);

      if (!ok) {
        mostrarError('Para registrar un nuevo cliente debe completar Nombre, Teléfono, Calle y Altura.');
        focusFirstInvalid(form);
        return;
      }

      // Limpiamos resultados de búsqueda / selección previa
      limpiarResultadosBusqueda();
      limpiarSeleccionCliente(); // pone modoCliente='nuevo' e idCliente=''

      const body = new URLSearchParams();
      body.append('accion', 'registrarClienteDesdePedido');
      body.append('clienteNombre', nombre);
      body.append('clienteTelefono', telefono);
      body.append('clienteEmail', email);
      body.append('clienteCalle', calle);
      body.append('clienteAltura', altura);

      fetch('../controlador/controladorPedidos.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body
      })
        .then(resp => resp.json())
        .then(data => {
          if (!data.ok) {
            Swal.fire({
              icon: 'error',
              title: 'Error al registrar el cliente',
              text: data.error || 'No se pudo registrar el cliente.',
              confirmButtonText: 'Aceptar'
            });
            return;
          }

          // Cliente creado OK: lo tratamos como "existente"
          if (inputIdCliente) inputIdCliente.value = data.idCliente;
          if (inputModoCliente) inputModoCliente.value = 'existente';

          if (spanClienteSeleccionado) spanClienteSeleccionado.textContent = nombre;
          if (bloqueClienteSeleccionado) bloqueClienteSeleccionado.style.display = 'inline-block';

          // Visualmente OK
          [inputNombreCliente, inputTelefonoCliente, inputCalleCliente, inputAlturaCliente].forEach(el => el && setValid(el));

          Swal.fire({
            icon: 'success',
            title: 'Cliente registrado',
            text: data.mensaje || 'El cliente se registró correctamente. Ahora puede completar el pedido.',
            confirmButtonText: 'Continuar'
          });
        })
        .catch(err => {
          console.error('Error al registrar cliente:', err);
          Swal.fire({
            icon: 'error',
            title: 'Error inesperado',
            text: 'Ocurrió un error al registrar el cliente.',
            confirmButtonText: 'Aceptar'
          });
        });
    });
  }

  // ====== Detalle del pedido ======
  function agregarFilaDetalle() {
    if (!tbody) return;

    const filaBase = tbody.querySelector('tr.fila-detalle');
    if (!filaBase) return;

    const nuevaFila = filaBase.cloneNode(true);

    // Limpiar valores
    nuevaFila.querySelectorAll('input, select').forEach(el => {
      clearValidation(el);
      if (el.tagName === 'SELECT') el.selectedIndex = 0;
      else el.value = '';
    });

    tbody.appendChild(nuevaFila);
  }

  function recalcularTotales() {
    if (!tbody || !totalInput) return;

    let total = 0;
    const filas = tbody.querySelectorAll('tr');

    filas.forEach(fila => {
      const inputCantidad = fila.querySelector('.campo-cantidad');
      const inputPrecio = fila.querySelector('.campo-precio');
      const inputSubtotal = fila.querySelector('.campo-subtotal');

      const cantidad = parseFloat((inputCantidad?.value || '').toString().replace(',', '.')) || 0;
      const precio = parseFloat((inputPrecio?.value || '').toString().replace(',', '.')) || 0;
      const subtotal = cantidad * precio;

      if (inputSubtotal) inputSubtotal.value = subtotal > 0 ? subtotal.toFixed(2) : '';
      total += subtotal;
    });

    totalInput.value = total.toFixed(2);
  }

  if (btnAgregarLinea) {
    btnAgregarLinea.addEventListener('click', () => agregarFilaDetalle());
  }

  if (tbody) {
    // Limpieza visual al editar detalle
    tbody.addEventListener('input', (e) => {
      if (e.target.matches('input, select')) clearValidation(e.target);

      if (e.target.classList.contains('campo-cantidad') || e.target.classList.contains('campo-precio')) {
        recalcularTotales();
      }
    });

    tbody.addEventListener('change', (e) => {
      if (e.target.classList.contains('campo-producto')) {
        const select = e.target;
        const fila = select.closest('tr');
        const inputPrecio = fila ? fila.querySelector('.campo-precio') : null;

        const opcion = select.selectedOptions[0];
        if (opcion) {
          const precioSugerido = opcion.getAttribute('data-precio');
          if (precioSugerido && inputPrecio && !inputPrecio.value) {
            inputPrecio.value = parseFloat(precioSugerido).toFixed(2);
          }
        }
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
        fila.querySelectorAll('input, select').forEach(el => {
          clearValidation(el);
          if (el.tagName === 'SELECT') el.selectedIndex = 0;
          else el.value = '';
        });
      }
      recalcularTotales();
    });
  }

  // ====== Validación final antes de enviar ======
  form.addEventListener('submit', (e) => {

  // ===== LIMPIEZA PREVIA =====
  [
    inputNombreCliente,
    inputTelefonoCliente,
    inputEmailCliente,
    inputCalleCliente,
    inputAlturaCliente
  ].forEach(el => el && clearValidation(el));

  // ===== VALIDACIÓN DATOS DEL CLIENTE =====
  const camposCliente = [
    { el: inputNombreCliente, msg: 'El nombre es obligatorio.' },
    { el: inputTelefonoCliente, msg: 'El teléfono es obligatorio.' },
    { el: inputEmailCliente, msg: 'El email es obligatorio.' },
    { el: inputCalleCliente, msg: 'La calle es obligatoria.' },
    { el: inputAlturaCliente, msg: 'La altura es obligatoria.' }
  ];

  let clienteValido = true;

  camposCliente.forEach(campo => {
    const valor = campo.el?.value.trim();
    if (!valor) {
      setInvalid(campo.el, campo.msg);
      clienteValido = false;
    } else {
      setValid(campo.el);
    }
  });

  if (!clienteValido) {
    e.preventDefault();
    mostrarError(
      'Debe completar todos los datos del cliente (nombre, teléfono, email, calle y altura).'
    );
    focusFirstInvalid(form);
    return;
  }

  // ===== VALIDACIÓN CLIENTE EXISTENTE =====
  const modoCliente = inputModoCliente ? inputModoCliente.value : 'nuevo';
  const idCliHidden = inputIdCliente ? parseInt(inputIdCliente.value || '0', 10) : 0;

  if (modoCliente === 'existente' && (!idCliHidden || idCliHidden <= 0)) {
    e.preventDefault();
    mostrarError(
      'Debe seleccionar un cliente válido desde la búsqueda o registrar uno nuevo.'
    );
    return;
  }

  // ===== VALIDACIÓN DETALLE DEL PEDIDO =====
  const filas = tbody.querySelectorAll('tr');
  let hayDetalleValido = false;

  filas.forEach(fila => {
    const selectProd = fila.querySelector('.campo-producto');
    const inputCant = fila.querySelector('.campo-cantidad');

    clearValidation(selectProd);
    clearValidation(inputCant);

    const idProd = selectProd?.value || '';
    const cant = parseFloat((inputCant?.value || '').replace(',', '.')) || 0;

    if (idProd && cant > 0) {
      hayDetalleValido = true;
      setValid(selectProd);
      setValid(inputCant);
    }
  });

  if (!hayDetalleValido) {
    e.preventDefault();

    const primera = filas[0];
    if (primera) {
      setInvalid(
        primera.querySelector('.campo-producto'),
        'Seleccione un producto.'
      );
      setInvalid(
        primera.querySelector('.campo-cantidad'),
        'Ingrese una cantidad.'
      );
    }

    mostrarError(
      'Debe agregar al menos un producto con cantidad mayor a cero.'
    );
    focusFirstInvalid(form);
    return;
  }

});


  // Inicial
  recalcularTotales();
});
