// pedidos/vista/pedidos.js

/* =========================
   Helpers de mensajes (SweetAlert2)
========================= */
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

/* =========================
   Helpers validación visual (Bootstrap)
========================= */
function ensureInvalidFeedback(el) {
  if (!el) return null;
  const container = el.closest('.col, .col-md-1, .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-md-9, .col-md-10, .col-md-11, .col-md-12') || el.parentElement;
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
}

function clearValidation(el) {
  if (!el) return;
  el.classList.remove('is-invalid', 'is-valid');
}

function focusFirstInvalid(container) {
  if (!container) return;
  const first = container.querySelector('.is-invalid');
  if (first && typeof first.focus === 'function') first.focus();
}

/* =========================
   DOM Ready
========================= */
document.addEventListener('DOMContentLoaded', () => {
  // =========================
  // Endpoints (evita 404)
  // =========================
  // pedidos/vista/ -> clientes/controlador/
  const ENDPOINT_CLIENTES = '../../clientes/controlador/controladorClientes.php';
  // pedidos/vista/ -> pedidos/controlador/
  const ENDPOINT_PEDIDOS = '../controlador/controladorPedidos.php';

  // =========================
  // DataTable (listado pedidos) – si aplica
  // =========================
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

    // Confirmaciones de cambio de estado (si usás forms)
    document.querySelectorAll('form.form-accion-estado').forEach(form => {
      form.addEventListener('submit', (e) => {
        e.preventDefault();
        const boton = form.querySelector('button[type="submit"]');
        const accionTexto = boton ? (boton.getAttribute('data-accion') || boton.textContent).trim() : 'esta acción';

        Swal.fire({
          title: `¿Confirmar ${accionTexto.toLowerCase()}?`,
          text: 'Esta acción cambiará el estado del pedido.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Continuar',
          cancelButtonText: 'Cancelar'
        }).then(res => {
          if (res.isConfirmed) form.submit();
        });
      });
    });
  }

  // =========================
  // Form Crear/Editar pedido
  // =========================
  const form = document.getElementById('formPedido');
  if (!form) return; // si no estamos en crear/editar, no seguimos

  // Cliente (inputs)
  const inputIdCliente = document.getElementById('idCliente');
  const inputModoCliente = document.getElementById('modoCliente');
  const bloqueClienteSeleccionado = document.getElementById('bloqueClienteSeleccionado');
  const spanClienteSeleccionado = document.getElementById('clienteSeleccionado');

  const inputNombreCliente = document.getElementById('clienteNombre');
  const inputTelefonoCliente = document.getElementById('clienteTelefono');
  const inputEmailCliente = document.getElementById('clienteEmail');
  const inputCalleCliente = document.getElementById('clienteCalle');
  const inputAlturaCliente = document.getElementById('clienteAltura');

  const inputProvinciaCliente = document.getElementById('clienteProvincia');
  const inputLocalidadCliente = document.getElementById('clienteLocalidad');

  // Búsqueda cliente
  const inputBusquedaCliente = document.getElementById('busquedaCliente');
  const btnBuscarCliente = document.getElementById('btnBuscarCliente');
  const btnNuevoCliente = document.getElementById('btnNuevoCliente');
  const divResultadosCliente = document.getElementById('resultadosBusquedaCliente');
  const tbodyResultadosCliente = document.getElementById('tablaResultadosCliente');

  // Detalle pedido
  const tablaDetalle = document.getElementById('tablaDetallePedido');
  const tbodyDetalle = tablaDetalle ? tablaDetalle.querySelector('tbody') : null;
  const btnAgregarLinea = document.getElementById('btnAgregarLinea');
  const totalInput = document.getElementById('totalPedido');

  // =========================
  // Collapse Datos Cliente
  // =========================
  const collapseEl = document.getElementById('collapseDatosCliente');
  const collapseDatosCliente = collapseEl
    ? new bootstrap.Collapse(collapseEl, { toggle: false })
    : null;

  function mostrarDatosCliente() {
    if (collapseDatosCliente) collapseDatosCliente.show();
  }

  function ocultarDatosCliente() {
    if (collapseDatosCliente) collapseDatosCliente.hide();
  }

  // =========================
  // Limpieza de validación al editar
  // =========================
  [
    inputNombreCliente, inputTelefonoCliente, inputEmailCliente, inputCalleCliente, inputAlturaCliente,
    inputProvinciaCliente, inputLocalidadCliente, inputBusquedaCliente
  ].forEach(el => {
    if (!el) return;
    el.addEventListener('input', () => clearValidation(el));
    el.addEventListener('change', () => clearValidation(el));
  });

  // =========================
  // Utilidades cliente
  // =========================
  function limpiarSeleccionCliente() {
    if (inputIdCliente) inputIdCliente.value = '';
    if (inputModoCliente) inputModoCliente.value = 'nuevo';
    if (bloqueClienteSeleccionado) bloqueClienteSeleccionado.style.display = 'none';
    if (spanClienteSeleccionado) spanClienteSeleccionado.textContent = '';
  }

  function limpiarResultadosBusqueda() {
    if (tbodyResultadosCliente) tbodyResultadosCliente.innerHTML = '';
    if (divResultadosCliente) divResultadosCliente.style.display = 'none';
  }

  // =========================
  // Provincias / Localidades
  // =========================
  async function cargarProvinciasPedido(selectedId = null) {
    if (!inputProvinciaCliente) return;

    const resp = await fetch(`${ENDPOINT_CLIENTES}?accion=listarProvincias`);
    const json = await resp.json();
    if (!json.ok) {
      mostrarError(json.msg || 'No se pudieron cargar provincias.');
      return;
    }

    const opts = ['<option value="">Seleccione...</option>']
      .concat((json.data || []).map(p => `<option value="${p.id_provincia}">${p.provincia}</option>`));

    inputProvinciaCliente.innerHTML = opts.join('');

    if (selectedId) inputProvinciaCliente.value = String(selectedId);
  }

  async function cargarLocalidadesPedido(idProvincia, selectedId = null) {
    if (!inputLocalidadCliente) return;

    if (!idProvincia) {
      inputLocalidadCliente.innerHTML = `<option value="">Seleccione una provincia primero...</option>`;
      inputLocalidadCliente.disabled = true;
      return;
    }

    const resp = await fetch(`${ENDPOINT_CLIENTES}?accion=listarLocalidades&id_provincia=${encodeURIComponent(idProvincia)}`);
    const json = await resp.json();
    if (!json.ok) {
      mostrarError(json.msg || 'No se pudieron cargar localidades.');
      return;
    }

    const opts = ['<option value="">Seleccione...</option>']
      .concat((json.data || []).map(l => `<option value="${l.id_localidad}">${l.localidad}</option>`));

    inputLocalidadCliente.innerHTML = opts.join('');
    inputLocalidadCliente.disabled = false;

    if (selectedId) inputLocalidadCliente.value = String(selectedId);
  }

  if (inputProvinciaCliente) {
    inputProvinciaCliente.addEventListener('change', async () => {
      clearValidation(inputProvinciaCliente);
      clearValidation(inputLocalidadCliente);
      await cargarLocalidadesPedido((inputProvinciaCliente.value || '').trim(), null);
    });
  }

  // =========================
  // Cargar datos cliente al seleccionar
  // =========================
  async function cargarDatosClienteEnFormulario(cliente) {
    if (!cliente) return;

    if (inputIdCliente) inputIdCliente.value = cliente.id_cliente || '';
    if (inputModoCliente) inputModoCliente.value = 'existente';

    if (inputNombreCliente) inputNombreCliente.value = cliente.nombre || '';
    if (inputTelefonoCliente) inputTelefonoCliente.value = cliente.telefono || '';
    if (inputEmailCliente) inputEmailCliente.value = cliente.email || '';
    if (inputCalleCliente) inputCalleCliente.value = cliente.calle || '';
    if (inputAlturaCliente) inputAlturaCliente.value = cliente.altura || '';

    const prov = cliente.provincia || '';
    const loc = cliente.localidad || '';

    // Asegurar combos cargados y selección consistente
    await cargarProvinciasPedido(prov || null);
    if (inputProvinciaCliente) inputProvinciaCliente.value = prov;

    await cargarLocalidadesPedido(prov, loc);

    if (spanClienteSeleccionado) spanClienteSeleccionado.textContent = cliente.nombre || '';
    if (bloqueClienteSeleccionado) bloqueClienteSeleccionado.style.display = 'inline-block';

    ocultarDatosCliente(); // UX: si el cliente ya está seleccionado, oculto sección
  }

  // =========================
  // Buscar cliente
  // =========================
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

      fetch(`${ENDPOINT_PEDIDOS}?accion=buscarClientes&q=${encodeURIComponent(termino)}`)
        .then(resp => resp.json())
        .then(data => {
          limpiarResultadosBusqueda();

          if (!Array.isArray(data) || data.length === 0) {
            divResultadosCliente.style.display = 'block';
            tbodyResultadosCliente.innerHTML = `
              <tr>
                <td colspan="4" class="text-muted text-center">
                  No se encontraron clientes para el criterio ingresado.
                </td>
              </tr>`;
            mostrarDatosCliente(); // ← solo si no hay resultados
            return;
          }

          const filas = data.map(cli => {
            const direccion = `${cli.calle || ''} ${cli.altura || ''}`.trim();
            return `
              <tr class="fila-resultado-cliente"
                  data-id="${cli.id_cliente}"
                  data-nombre="${cli.nombre || ''}"
                  data-email="${cli.email || ''}"
                  data-telefono="${cli.telefono || ''}"
                  data-calle="${cli.calle || ''}"
                  data-altura="${cli.altura || ''}"
                  data-provincia="${cli.provincia || ''}"
                  data-localidad="${cli.localidad || ''}">
                <td>${cli.nombre || ''}</td>
                <td>${cli.telefono || ''}</td>
                <td>${cli.email || ''}</td>
                <td>${direccion}</td>
              </tr>
            `;
          }).join('');

          tbodyResultadosCliente.innerHTML = filas;
          divResultadosCliente.style.display = 'block';
          // No fuerzo mostrar/ocultar acá. El usuario selecciona.
        })
        .catch(err => {
          console.error('Error al buscar clientes:', err);
          mostrarError('Ocurrió un error al buscar clientes.');
        });
    });
  }

  // Click en resultado de búsqueda
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
        altura: fila.getAttribute('data-altura'),
        provincia: fila.getAttribute('data-provincia'),
        localidad: fila.getAttribute('data-localidad')
      };

      cargarDatosClienteEnFormulario(cliente);
    });
  }

  // =========================
  // Registrar cliente desde pedido
  // =========================
  if (btnNuevoCliente) {
    btnNuevoCliente.addEventListener('click', () => {
      mostrarDatosCliente(); // si estaba oculto, lo muestro para que el usuario vea qué falta

      // Limpio validaciones
      [
        inputNombreCliente, inputTelefonoCliente, inputEmailCliente, inputCalleCliente, inputAlturaCliente,
        inputProvinciaCliente, inputLocalidadCliente
      ].forEach(el => el && clearValidation(el));

      const nombre = (inputNombreCliente?.value || '').trim();
      const telefono = (inputTelefonoCliente?.value || '').trim();
      const email = (inputEmailCliente?.value || '').trim();
      const calle = (inputCalleCliente?.value || '').trim();
      const altura = (inputAlturaCliente?.value || '').trim();
      const provincia = (inputProvinciaCliente?.value || '').trim();
      const localidad = (inputLocalidadCliente?.value || '').trim();

      let ok = true;

      if (!nombre) { setInvalid(inputNombreCliente, 'Nombre obligatorio.'); ok = false; } else setValid(inputNombreCliente);
      if (!telefono) { setInvalid(inputTelefonoCliente, 'Teléfono obligatorio.'); ok = false; } else setValid(inputTelefonoCliente);
      if (!email) { setInvalid(inputEmailCliente, 'Email obligatorio.'); ok = false; } else setValid(inputEmailCliente);
      if (!calle) { setInvalid(inputCalleCliente, 'Calle obligatoria.'); ok = false; } else setValid(inputCalleCliente);
      if (!altura) { setInvalid(inputAlturaCliente, 'Altura obligatoria.'); ok = false; } else setValid(inputAlturaCliente);

      if (!provincia) { setInvalid(inputProvinciaCliente, 'Provincia obligatoria.'); ok = false; } else setValid(inputProvinciaCliente);
      if (!localidad) { setInvalid(inputLocalidadCliente, 'Localidad obligatoria.'); ok = false; } else setValid(inputLocalidadCliente);

      if (!ok) {
        mostrarError('Para registrar un nuevo cliente debe completar Nombre, Teléfono, Email, Calle, Altura, Provincia y Localidad.');
        focusFirstInvalid(form);
        return;
      }

      const body = new URLSearchParams();
      body.append('accion', 'registrarClienteDesdePedido');
      body.append('clienteNombre', nombre);
      body.append('clienteTelefono', telefono);
      body.append('clienteEmail', email);
      body.append('clienteCalle', calle);
      body.append('clienteAltura', altura);
      body.append('clienteProvincia', provincia);
      body.append('clienteLocalidad', localidad);

      fetch(ENDPOINT_PEDIDOS, {
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

          if (inputIdCliente) inputIdCliente.value = data.idCliente;
          if (inputModoCliente) inputModoCliente.value = 'existente';

          if (spanClienteSeleccionado) spanClienteSeleccionado.textContent = nombre;
          if (bloqueClienteSeleccionado) bloqueClienteSeleccionado.style.display = 'inline-block';

          [
            inputNombreCliente, inputTelefonoCliente, inputEmailCliente, inputCalleCliente, inputAlturaCliente,
            inputProvinciaCliente, inputLocalidadCliente
          ].forEach(el => el && setValid(el));

          ocultarDatosCliente();

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

  // =========================
  // Detalle pedido: filas + totales
  // =========================
  function recalcularTotales() {
    if (!tbodyDetalle || !totalInput) return;

    let total = 0;
    const filas = tbodyDetalle.querySelectorAll('tr');

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

  function agregarFilaDetalle() {
    if (!tbodyDetalle) return;

    const filaBase = tbodyDetalle.querySelector('tr');
    if (!filaBase) return;

    const nuevaFila = filaBase.cloneNode(true);
    nuevaFila.querySelectorAll('input, select').forEach(el => {
      clearValidation(el);
      if (el.tagName === 'SELECT') el.selectedIndex = 0;
      else el.value = '';
    });

    tbodyDetalle.appendChild(nuevaFila);
  }

  if (btnAgregarLinea) {
    btnAgregarLinea.addEventListener('click', () => agregarFilaDetalle());
  }

  if (tbodyDetalle) {
    tbodyDetalle.addEventListener('input', (e) => {
      if (e.target.matches('input, select')) clearValidation(e.target);
      if (e.target.classList.contains('campo-cantidad') || e.target.classList.contains('campo-precio')) {
        recalcularTotales();
      }
    });

    tbodyDetalle.addEventListener('change', (e) => {
      // Si el select producto tiene data-precio, autocompleto precio si está vacío
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

    tbodyDetalle.addEventListener('click', (e) => {
      const btn = e.target.closest('.btnEliminarFila');
      if (!btn) return;

      const fila = btn.closest('tr');
      const filas = tbodyDetalle.querySelectorAll('tr');

      if (filas.length > 1) {
        fila.remove();
      } else {
        // reset fila única
        fila.querySelectorAll('input, select').forEach(el => {
          clearValidation(el);
          if (el.tagName === 'SELECT') el.selectedIndex = 0;
          else el.value = '';
        });
      }
      recalcularTotales();
    });
  }

  // =========================
  // Validación submit pedido
  // =========================
  form.addEventListener('submit', (e) => {
    [
      inputNombreCliente, inputTelefonoCliente, inputEmailCliente,
      inputCalleCliente, inputAlturaCliente, inputProvinciaCliente, inputLocalidadCliente
    ].forEach(el => el && clearValidation(el));

    const camposCliente = [
      { el: inputNombreCliente, msg: 'El nombre es obligatorio.' },
      { el: inputTelefonoCliente, msg: 'El teléfono es obligatorio.' },
      { el: inputEmailCliente, msg: 'El email es obligatorio.' },
      { el: inputCalleCliente, msg: 'La calle es obligatoria.' },
      { el: inputAlturaCliente, msg: 'La altura es obligatoria.' },
      { el: inputProvinciaCliente, msg: 'La provincia es obligatoria.' },
      { el: inputLocalidadCliente, msg: 'La localidad es obligatoria.' }
    ];

    let clienteValido = true;
    camposCliente.forEach(campo => {
      const valor = (campo.el?.value || '').toString().trim();
      if (!valor) {
        setInvalid(campo.el, campo.msg);
        clienteValido = false;
      } else {
        setValid(campo.el);
      }
    });

    if (!clienteValido) {
      e.preventDefault();
      mostrarDatosCliente();
      mostrarError('Debe completar todos los datos del cliente (incluyendo provincia y localidad).');
      focusFirstInvalid(form);
      return;
    }

    const modoCliente = (inputModoCliente?.value || 'nuevo').toString();
    const idCliHidden = parseInt((inputIdCliente?.value || '0'), 10);

    if (modoCliente === 'existente' && (!idCliHidden || idCliHidden <= 0)) {
      e.preventDefault();
      mostrarError('Debe seleccionar un cliente válido desde la búsqueda o registrar uno nuevo.');
      return;
    }

    // Validar detalle (al menos un producto con cantidad)
    const filas = tbodyDetalle ? tbodyDetalle.querySelectorAll('tr') : [];
    let hayDetalleValido = false;

    filas.forEach(fila => {
      const selectProd = fila.querySelector('.campo-producto');
      const inputCant = fila.querySelector('.campo-cantidad');

      clearValidation(selectProd);
      clearValidation(inputCant);

      const idProd = (selectProd?.value || '').toString();
      const cant = parseFloat(((inputCant?.value || '').toString()).replace(',', '.')) || 0;

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
        setInvalid(primera.querySelector('.campo-producto'), 'Seleccione un producto.');
        setInvalid(primera.querySelector('.campo-cantidad'), 'Ingrese una cantidad.');
      }
      mostrarError('Debe agregar al menos un producto con cantidad mayor a cero.');
      focusFirstInvalid(form);
      return;
    }
  });

  // =========================
  // Inicial
  // =========================
  recalcularTotales();

  (async () => {
    const provSel = inputProvinciaCliente?.dataset?.selected || null;
    const locSel  = inputLocalidadCliente?.dataset?.selected || null;

    await cargarProvinciasPedido(provSel);

    if (provSel) {
        // Editar pedido con cliente existente
        await cargarLocalidadesPedido(provSel, locSel);
        ocultarDatosCliente();
    } else {
        // Crear pedido SIN cliente aún
        ocultarDatosCliente(); // ← arranca cerrada
        await cargarLocalidadesPedido('', null);
    }
})();
});
