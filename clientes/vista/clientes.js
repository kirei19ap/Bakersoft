// clientes/vista/clientes.js

function mostrarError(mensaje) {
    Swal.fire({ icon: 'warning', title: 'Atención', text: mensaje, confirmButtonText: 'Entendido' });
}

function mostrarOk(mensaje) {
    Swal.fire({ icon: 'success', title: 'Listo', text: mensaje, confirmButtonText: 'Continuar' });
}

/** ==== helpers de validación visual (idénticos a pedidos.js) ==== */
function ensureInvalidFeedback(el) {
    if (!el) return null;
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
/** ============================================================ */

async function fetchJSON(url, opts) {
    const resp = await fetch(url, opts);
    const json = await resp.json();
    return json;
}

document.addEventListener('DOMContentLoaded', () => {
    const tabla = document.getElementById('tablaClientes');
    if (!tabla) return;

    const modalVer = new bootstrap.Modal(document.getElementById('modalVerCliente'));
    const modalEditarEl = document.getElementById('modalEditarCliente');
    const modalEditar = new bootstrap.Modal(modalEditarEl);

    const formEditar = document.getElementById('formEditarCliente');

    const editId = document.getElementById('editIdCliente');
    const editNombre = document.getElementById('editNombre');
    const editTelefono = document.getElementById('editTelefono');
    const editEmail = document.getElementById('editEmail');
    const editCalle = document.getElementById('editCalle');
    const editAltura = document.getElementById('editAltura');
    const editEstado = document.getElementById('editEstado');

    // NUEVO: selects
    const editProvincia = document.getElementById('editProvincia');
    const editLocalidad = document.getElementById('editLocalidad');

    // Limpieza de validaciones al cerrar modal
    modalEditarEl.addEventListener('hidden.bs.modal', () => {
        [editNombre, editTelefono, editEmail, editCalle, editAltura, editProvincia, editLocalidad].forEach(el => clearValidation(el));
        formEditar.reset();

        if (editLocalidad) {
            editLocalidad.innerHTML = `<option value="">Seleccione una provincia primero...</option>`;
            editLocalidad.disabled = true;
        }
    });

    // Limpieza al tipear/cambiar
    [editNombre, editTelefono, editEmail, editCalle, editAltura, editProvincia, editLocalidad].forEach(el => {
        if (!el) return;
        el.addEventListener('input', () => clearValidation(el));
        el.addEventListener('change', () => clearValidation(el));
    });

    // === DataTable
    const dt = new DataTable('#tablaClientes', {
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
        order: [[1, 'asc']]
    });

    function badgeEstado(estado) {
        switch (estado) {
            case 'Activo': return '<span class="badge bg-success">Activo</span>';
            case 'Eliminado': return '<span class="badge bg-danger">Eliminado</span>';
            case 'Deshabilitado': return '<span class="badge bg-secondary">Deshabilitado</span>';
            default: return `<span class="badge bg-light text-dark">${estado}</span>`;
        }
    }

    function renderAcciones(id, estado) {
        let acciones = `
      <button class="btn btn-sm btn-success btn-ver" data-id="${id}">
        <ion-icon name="eye-outline"></ion-icon>
      </button>
      <button class="btn btn-sm btn-primary btn-editar" data-id="${id}">
        <ion-icon name="create-outline"></ion-icon>
      </button>
    `;
        if (estado === 'Activo') {
            acciones += `
        <button class="btn btn-sm btn-danger btn-eliminar" data-id="${id}">
          <ion-icon name="trash-outline"></ion-icon>
        </button>
      `;
        } else {
            acciones += `
        <button class="btn btn-sm btn-success btn-reactivar" data-id="${id}">
          <ion-icon name="refresh-outline"></ion-icon>
        </button>
      `;
        }
        return `<div class="d-flex justify-content-center gap-2">${acciones}</div>`;
    }

    async function cargarTabla() {
        try {
            const json = await fetchJSON('../controlador/controladorClientes.php?accion=listar');
            if (!json.ok) {
                mostrarError(json.msg || 'No se pudo cargar la lista de clientes.');
                return;
            }

            dt.clear();
            json.data.forEach(c => {
                const direccion = `${c.calle || ''} ${c.altura || ''}`.trim();
                dt.row.add([
                    c.id_cliente,
                    c.nombre || '',
                    c.telefono || '',
                    c.email || '',
                    direccion,
                    badgeEstado(c.estado),
                    renderAcciones(c.id_cliente, c.estado)
                ]);
            });
            dt.draw();
        } catch (e) {
            console.error(e);
            mostrarError('Ocurrió un error al cargar clientes.');
        }
    }

    async function obtenerCliente(id) {
        const json = await fetchJSON(`../controlador/controladorClientes.php?accion=obtener&id=${encodeURIComponent(id)}`);
        if (!json.ok) throw new Error(json.msg || 'No se pudo obtener el cliente.');
        return json.data;
    }

    // ===== Provincias / Localidades =====
    async function cargarProvincias(select, selectedId = null) {
        if (!select) return;
        const json = await fetchJSON('../controlador/controladorClientes.php?accion=listarProvincias');
        if (!json.ok) throw new Error(json.msg || 'No se pudieron cargar provincias.');

        const opts = ['<option value="">Seleccione...</option>']
            .concat((json.data || []).map(p => `<option value="${p.id_provincia}">${p.provincia}</option>`));
        select.innerHTML = opts.join('');

        if (selectedId) select.value = String(selectedId);
    }

    async function cargarLocalidades(select, idProvincia, selectedId = null) {
        if (!select) return;

        if (!idProvincia) {
            select.innerHTML = `<option value="">Seleccione una provincia primero...</option>`;
            select.disabled = true;
            return;
        }

        const json = await fetchJSON(`../controlador/controladorClientes.php?accion=listarLocalidades&id_provincia=${encodeURIComponent(idProvincia)}`);
        if (!json.ok) throw new Error(json.msg || 'No se pudieron cargar localidades.');

        const opts = ['<option value="">Seleccione...</option>']
            .concat((json.data || []).map(l => `<option value="${l.id_localidad}">${l.localidad}</option>`));

        select.innerHTML = opts.join('');
        select.disabled = false;

        if (selectedId) select.value = String(selectedId);
    }

    if (editProvincia) {
        editProvincia.addEventListener('change', async () => {
            clearValidation(editProvincia);
            clearValidation(editLocalidad);
            const idProv = (editProvincia.value || '').trim();
            await cargarLocalidades(editLocalidad, idProv, null);
        });
    }

    // Delegación de eventos en la tabla
    tabla.addEventListener('click', async (e) => {
        const btnVer = e.target.closest('.btn-ver');
        const btnEditar = e.target.closest('.btn-editar');
        const btnEliminar = e.target.closest('.btn-eliminar');
        const btnReactivar = e.target.closest('.btn-reactivar');

        try {
            if (btnVer) {
                const id = btnVer.getAttribute('data-id');
                const c = await obtenerCliente(id);

                document.getElementById('verNombre').value = c.nombre || '';
                document.getElementById('verTelefono').value = c.telefono || '';
                document.getElementById('verEmail').value = c.email || '';
                document.getElementById('verDireccion').value = `${c.calle || ''} ${c.altura || ''}`.trim();
                document.getElementById('verProvincia').value = c.provincia_nombre || '';
                document.getElementById('verLocalidad').value = c.localidad_nombre || '';


                modalVer.show();
            }

            if (btnEditar) {
                const id = btnEditar.getAttribute('data-id');
                const c = await obtenerCliente(id);

                editId.value = c.id_cliente;
                editNombre.value = c.nombre || '';
                editTelefono.value = c.telefono || '';
                editEmail.value = c.email || '';
                editCalle.value = c.calle || '';
                editAltura.value = c.altura || '';
                editEstado.value = c.estado || '';

                [editNombre, editTelefono, editEmail, editCalle, editAltura, editProvincia, editLocalidad].forEach(el => clearValidation(el));

                // Cargar provincias y localidades y preseleccionar
                await cargarProvincias(editProvincia, c.provincia || null);
                await cargarLocalidades(editLocalidad, c.provincia || null, c.localidad || null);

                modalEditar.show();
            }

            if (btnEliminar) {
                const id = btnEliminar.getAttribute('data-id');

                const res = await Swal.fire({
                    title: '¿Eliminar cliente?',
                    text: 'Se aplicará baja lógica (estado = Eliminado). No aparecerá en el buscador de pedidos.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Continuar',
                    cancelButtonText: 'Cancelar'
                });
                if (!res.isConfirmed) return;

                const body = new URLSearchParams();
                body.append('accion', 'eliminar');
                body.append('idCliente', id);

                const json = await fetchJSON('../controlador/controladorClientes.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body
                });

                if (!json.ok) {
                    mostrarError(json.msg || 'No se pudo eliminar el cliente.');
                    return;
                }

                mostrarOk(json.msg || 'Cliente eliminado.');
                cargarTabla();
            }

            if (btnReactivar) {
                const id = btnReactivar.dataset.id;

                const res = await Swal.fire({
                    title: '¿Reactivar cliente?',
                    text: 'El cliente volverá a estar disponible para pedidos.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Reactivar',
                    cancelButtonText: 'Cancelar'
                });
                if (!res.isConfirmed) return;

                const body = new URLSearchParams();
                body.append('accion', 'reactivar');
                body.append('idCliente', id);

                const json = await fetchJSON('../controlador/controladorClientes.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body
                });

                if (!json.ok) {
                    mostrarError(json.msg);
                    return;
                }

                mostrarOk(json.msg);
                cargarTabla();
            }

        } catch (err) {
            console.error(err);
            mostrarError(err.message || 'Ocurrió un error.');
        }
    });

    // Guardar cambios
    formEditar.addEventListener('submit', async (e) => {
        e.preventDefault();

        [editNombre, editTelefono, editEmail, editCalle, editAltura, editProvincia, editLocalidad].forEach(el => clearValidation(el));

        const nombre = (editNombre.value || '').trim();
        const telefono = (editTelefono.value || '').trim();
        const email = (editEmail.value || '').trim();
        const calle = (editCalle.value || '').trim();
        const altura = (editAltura.value || '').trim();
        const provincia = (editProvincia?.value || '').trim();
        const localidad = (editLocalidad?.value || '').trim();

        let ok = true;

        if (!nombre) { setInvalid(editNombre, 'Nombre obligatorio.'); ok = false; } else setValid(editNombre);
        if (!telefono) { setInvalid(editTelefono, 'Teléfono obligatorio.'); ok = false; } else setValid(editTelefono);
        if (!email) { setInvalid(editEmail, 'Email obligatorio.'); ok = false; } else setValid(editEmail);
        if (!calle) { setInvalid(editCalle, 'Calle obligatoria.'); ok = false; } else setValid(editCalle);

        if (!altura) { setInvalid(editAltura, 'Altura obligatoria.'); ok = false; }
        else if (!/^\d+$/.test(altura) || parseInt(altura, 10) <= 0) { setInvalid(editAltura, 'Altura inválida.'); ok = false; }
        else setValid(editAltura);

        if (!provincia) { setInvalid(editProvincia, 'Seleccione una provincia.'); ok = false; } else setValid(editProvincia);
        if (!localidad) { setInvalid(editLocalidad, 'Seleccione una localidad.'); ok = false; } else setValid(editLocalidad);

        if (!ok) {
            mostrarError('Debe completar todos los datos del cliente, incluyendo provincia y localidad.');
            focusFirstInvalid(formEditar);
            return;
        }

        const confirm = await Swal.fire({
            title: '¿Guardar cambios?',
            text: 'Se actualizarán los datos del cliente.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar'
        });
        if (!confirm.isConfirmed) return;

        try {
            const body = new URLSearchParams();
            body.append('accion', 'actualizar');
            body.append('idCliente', editId.value);
            body.append('nombre', nombre);
            body.append('telefono', telefono);
            body.append('email', email);
            body.append('calle', calle);
            body.append('altura', altura);
            body.append('provincia', provincia);
            body.append('localidad', localidad);

            const json = await fetchJSON('../controlador/controladorClientes.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body
            });

            if (!json.ok) {
                mostrarError(json.msg || 'No se pudo actualizar el cliente.');
                return;
            }

            modalEditar.hide();
            mostrarOk(json.msg || 'Cliente actualizado.');
            cargarTabla();
        } catch (err) {
            console.error(err);
            mostrarError('Ocurrió un error al guardar.');
        }
    });

    // Inicial
    cargarTabla();
});
