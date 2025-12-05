const $ = window.jQuery;

// productos/vista/productos.js
document.addEventListener('DOMContentLoaded', () => {
    // --- GUARD: sólo correr en páginas que tengan la tabla o el modal ---
    const hasTabla = !!document.getElementById('tablaProductos');
    const hasModal = !!document.getElementById('modalCrearProducto');
    if (!hasTabla && !hasModal) return; // nada que hacer aquí

    // ====== DataTable de productos (si existe la tabla) ======
    if (hasTabla && window.jQuery) {
        const $tabla = $('#tablaProductos');
        if ($tabla.length) {
            const tabla = $('#tablaProductos').DataTable({
                ajax: { url: 'listar.php', dataSrc: '' },
                columns: [
                    { data: 'idProducto' },           // Oculta por columnDefs
                    { data: 'nombre' },
                    { data: 'categoria' },
                    { data: 'unidad_medida' },
                    { data: 'estado' },
                    { data: null }                    // Acciones
                ],
                columnDefs: [
                    { targets: 0, visible: false, searchable: false, className: 'd-none' },
                    {
                        targets: -1,
                        orderable: false,
                        searchable: false,
                        className: 'text-end text-nowrap',
                        render: function (_, __, row) {
                            const isActivo = (row.estado || '').toLowerCase() === 'activo';
                            const btnToggle = isActivo
                                ? `<button class="btn btn-sm btn-danger btnDesactivar" data-id="${row.idProducto}"><ion-icon name="trash-outline"></ion-icon></button>`
                                : `<button class="btn btn-sm btn-info btnActivar" data-id="${row.idProducto}"><ion-icon name="refresh-circle-outline"></ion-icon></button>`;

                            return `<div class="ion-fuente">
          <button class="btn btn-icon btn-success btnVer" title="Ver" data-id="${row.idProducto}">
            <ion-icon name="eye-outline"></ion-icon>
          </button>
          <button class="btn btn-icon btn-primary btnEditar" title="Editar" data-id="${row.idProducto}">
            <ion-icon name="create-outline"></ion-icon>
          </button>
          ${btnToggle}
        </div>`;
                        }
                    }
                ],
                order: [[1, 'asc']],
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                responsive: { details: false },
                autoWidth: false,
                scrollX: false,
                dom:
                    '<"row g-2 align-items-center mb-2"<"col-12 col-md-6"l><"col-12 col-md-6 d-flex justify-content-md-end"f>>' +
                    't' +
                    '<"row mt-2"<"col-sm-6"i><"col-sm-6"p>>',
                language: {
                    decimal: ",",
                    thousands: ".",
                    info: "Mostrando _END_ registros de un total de _TOTAL_",
                    infoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
                    loadingRecords: "Cargando...",
                    lengthMenu: "Mostrar _MENU_ ",
                    paginate: { first: "<<", last: ">>", next: ">", previous: "<" },
                    search: "Buscar:",
                    searchPlaceholder: "Buscar...",
                    emptyTable: "No hay registros para mostrar en la tabla",
                    sInfoFiltered: "(filtrado de un total de _MAX_ registros)",
                }
            });

            // “Bootstrapear” el input del filtro (tamaño y ancho) ya renderizado por DataTables
            const $wrap = $('#tablaProductos').closest('.dataTables_wrapper');
            $wrap.find('.dataTables_filter label')
                .css({ display: 'flex', alignItems: 'center', gap: '.5rem', marginBottom: 0 });
            $wrap.find('.dataTables_filter input')
                .addClass('form-control form-control-sm')
                .attr('placeholder', 'Buscar...')
                .css('max-width', '320px');
        }
    }

    // ====== Elementos del modal Crear (si existe el modal) ======
    if (!hasModal) return;

    const modalEl = document.getElementById('modalCrearProducto');
    const form = document.getElementById('formCrearProducto');
    const btnGuardar = document.getElementById('btnGuardarProducto');
    const nombre = document.getElementById('nombreProducto');
    const textoDup = document.getElementById('textoNombreDuplicado');
    const selCategoria = document.getElementById('categoriaProd');
    const selUnidad = document.getElementById('unidadMedida');
    const selMP = document.getElementById('mpSelect');
    const inpCant = document.getElementById('mpCantidad');
    const tablaComp = document.getElementById('tablaComposicion');
    const tblBody = tablaComp ? tablaComp.querySelector('tbody') : null;
    const hiddenComp = document.getElementById('componentesJson');
    const btnAgregar = document.getElementById('btnAgregarMP');

    // Helper seguro para parsear JSON del hidden
    const getComponentes = () => {
        try {
            if (!hiddenComp) return [];
            const val = hiddenComp.value?.trim();
            if (!val) return [];
            const js = JSON.parse(val);
            return Array.isArray(js) ? js : [];
        } catch {
            return [];
        }
    };

    const setComponentes = (rows) => {
        if (hiddenComp) hiddenComp.value = JSON.stringify(rows || []);
    };

    const renderJSON = () => {
        if (!tblBody) return;
        const rows = [...tblBody.querySelectorAll('tr')].map(tr => ({
            idMP: parseInt(tr.dataset.idmp || '0', 10),
            cantidad: parseFloat(tr.dataset.cantidad || '0'),
        }));
        setComponentes(rows);
    };

    const agregarFila = (idMP, texto, cantidad, merma) => {
        if (!tblBody) return;
        const tr = document.createElement('tr');
        tr.dataset.idmp = String(idMP);
        tr.dataset.cantidad = String(cantidad);
        tr.innerHTML = `
      <td>${texto}</td>
      <td class="text-center">${Number(cantidad)}</td>
      <td class="text-center">
        <button type="button" class="btn btn-danger btn-icon btnQuitar" title="Quitar">
            <ion-icon name="close-circle-outline"></ion-icon>
        </button>
      </td>
    `;
        tblBody.appendChild(tr);
        renderJSON();
    };

    // ====== Poblar combos al abrir el modal (AJAX) ======
    if (modalEl) {
        modalEl.addEventListener('show.bs.modal', async () => {
            // Categorías
            if (selCategoria && (!selCategoria.options || selCategoria.options.length <= 1)) {
                try {
                    const r = await fetch('categorias.php');
                    const cats = await r.json();
                    if (Array.isArray(cats)) {
                        cats.forEach(c => {
                            const op = document.createElement('option');
                            op.value = c.idCatProd;
                            op.textContent = c.nombre;
                            selCategoria.appendChild(op);
                        });
                    }
                } catch (e) {
                    console.warn('No se pudieron cargar categorías', e);
                }
            }
            // Materias primas
            if (selMP && (!selMP.options || selMP.options.length <= 1)) {
                try {
                    const r = await fetch('materias.php');
                    const mps = await r.json();
                    if (Array.isArray(mps)) {
                        mps.forEach(m => {
                            const op = document.createElement('option');
                            op.value = m.id;
                            op.textContent = `${m.nombre} (${m.unidad_medida}) — Stock: ${m.stockactual}`;
                            selMP.appendChild(op);
                        });
                    }
                } catch (e) {
                    console.warn('No se pudieron cargar materias primas', e);
                }
            }
        });

        // Limpiar validación y composición al cerrar el modal Crear
        modalEl.addEventListener('hidden.bs.modal', () => {
            if (!form) return;

            form.classList.remove('was-validated');
            form.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
                el.classList.remove('is-valid', 'is-invalid');
            });

            if (textoDup) textoDup.style.display = 'none';

            if (tblBody) tblBody.innerHTML = '';
            setComponentes([]);

            // Limpiar campos de texto/select
            if (nombre) nombre.value = '';
            if (selCategoria) selCategoria.value = '';
            if (selUnidad) selUnidad.value = '';
            if (selMP) selMP.value = '';
            if (inpCant) inpCant.value = '';
            const desc = document.getElementById('descripcion');
            if (desc) desc.value = '';
            const precio = document.getElementById('precioVenta');
            if (precio) precio.value = '';
        });
    }

    // ====== Validación de nombre único con debounce ======
    if (nombre && textoDup) {
        let timer;
        nombre.addEventListener('input', () => {
            clearTimeout(timer);
            textoDup.style.display = 'none';
            timer = setTimeout(async () => {
                const q = nombre.value?.trim();
                if (!q) { nombre.setCustomValidity(''); return; }
                try {
                    const r = await fetch(`validarNombre.php?nombre=${encodeURIComponent(q)}`);
                    const js = await r.json();
                    const existe = !!(js && js.existe);
                    textoDup.style.display = existe ? 'block' : 'none';
                    nombre.setCustomValidity(existe ? 'duplicado' : '');
                } catch {
                    nombre.setCustomValidity('');
                }
            }, 300);
        });
    }

    // ====== Agregar a composición ======
    if (btnAgregar) {
        btnAgregar.addEventListener('click', () => {
            if (!selMP || !inpCant) return;
            const idMP = parseInt(selMP.value || '0', 10);
            const texto = selMP.options && selMP.selectedIndex >= 0
                ? (selMP.options[selMP.selectedIndex].text || '')
                : '';
            const cantidad = parseFloat(inpCant.value || '0');

            if (!idMP) { selMP.focus(); return; }
            if (!(cantidad > 0)) { inpCant.focus(); return; }

            agregarFila(idMP, texto, cantidad);
            selMP.value = '';
            inpCant.value = '';
            selMP.focus();
        });
    }

    // Quitar filas de composición
    if (tblBody) {
        tblBody.addEventListener('click', (e) => {
            const btn = e.target.closest?.('.btnQuitar');
            if (btn) {
                const tr = btn.closest('tr');
                if (tr) tr.remove();
                renderJSON();
            }
        });
    }

    // ====== Envío del formulario Crear ======
    if (btnGuardar && form) {
        btnGuardar.addEventListener('click', () => {
            form.classList.add('was-validated');

            const comps = getComponentes(); // siempre array
            if (!Array.isArray(comps) || comps.length === 0) {
                alert('Debe agregar al menos una materia prima con cantidad.');
                return;
            }
            if (!form.checkValidity()) return;
            form.submit(); // POST a crear.php
        });
    }

});

// Helper para abrir modales
const bsModal = (id) => new bootstrap.Modal(document.getElementById(id), { keyboard: false, backdrop: 'static' });

// ============ VER ============
$('#tablaProductos').on('click', '.btnVer', async function () {
    const id = this.dataset.id;
    try {
        const r = await fetch(`ver.php?id=${encodeURIComponent(id)}`);
        const data = await r.json(); // { idProducto, nombre, categoria, unidad_medida, precio_venta, estado, componentes:[{mp, cantidad}] }

        // Completar cabecera
        document.getElementById('ver_nombre').value = data.nombre || '';
        document.getElementById('ver_categoria').value = data.categoria || '';
        document.getElementById('ver_unidad').value = data.unidad_medida || '';

        // Precio (solo lectura)
        const inpPrecioVer = document.getElementById('ver_precio');
        if (inpPrecioVer) {
            const pv = parseFloat(data.precio_venta ?? data.precio ?? 0);
            inpPrecioVer.value = Number.isFinite(pv) ? pv.toFixed(2) : '';
        }

        // Badge estado (Ver)
        const badge = document.getElementById('badgeEstadoVer');
        badge.textContent = data.estado;

        if ((data.estado || '').toLowerCase() === 'activo') {
            badge.className = "badge bg-success";
        } else {
            badge.className = "badge bg-secondary";
        }

        // Composición
        const tbody = document.querySelector('#tablaComposicionVer tbody');
        tbody.innerHTML = '';

        const fmt = (v) => {
            const n = Number(v);
            if (!Number.isFinite(n)) return v ?? '';
            return n % 1 === 0 ? n.toString() : n.toFixed(3).replace(/\.?0+$/, '');
        };

        (data.componentes || []).forEach(c => {
            const cant = fmt(c.cantidad);
            const unidad = c.unidad_medida ? ` ${c.unidad_medida}` : '';
            const tr = document.createElement('tr');
            tr.innerHTML = `
    <td>${c.mp}</td>
    <td class="text-center">${cant}${unidad}</td>
  `;
            tbody.appendChild(tr);
        });

        if (!data.componentes || data.componentes.length === 0) {
            tbody.innerHTML = `<tr><td colspan="2" class="text-center text-muted">Sin componentes cargados</td></tr>`;
        }

        bsModal('modalVerProducto').show();
    } catch (e) {
        console.error(e);
        alert('No se pudo obtener el detalle del producto.');
    }
});

// ============ EDITAR ============
const renderEditRowsJSON = () => {
    const rows = [...document.querySelectorAll('#tablaComposicionEdit tbody tr')].map(tr => ({
        idMP: parseInt(tr.dataset.idmp || '0', 10),
        cantidad: parseFloat(tr.dataset.cantidad || '0')
    }));
    document.getElementById('componentesJsonEdit').value = JSON.stringify(rows);
};

$('#tablaProductos').on('click', '.btnEditar', async function () {
    const id = this.dataset.id;
    try {
        const r = await fetch(`ver.php?id=${encodeURIComponent(id)}`);
        const data = await r.json();

        // id hidden
        document.getElementById('edit_idProducto').value = data.idProducto;

        // combos (si no están cargados, traerlos)
        // categorías
        const selCat = document.getElementById('edit_categoria');
        if (selCat.options.length <= 1) {
            const rc = await fetch('categorias.php');
            const cats = await rc.json();
            cats.forEach(c => { const op = new Option(c.nombre, c.idCatProd); selCat.add(op); });
        }
        // materias primas selector
        const selMP = document.getElementById('edit_mpSelect');
        if (selMP.options.length <= 1) {
            const rm = await fetch('materias.php');
            const mps = await rm.json();
            mps.forEach(m => {
                const op = new Option(`${m.nombre} (${m.unidad_medida})`, m.id);
                op.setAttribute('data-unidad', m.unidad_medida || '');
                selMP.add(op);
            });
        }

        // set campos
        document.getElementById('edit_nombre').value = data.nombre || '';
        selCat.value = data.categoriaProd || '';
        document.getElementById('edit_unidad').value = data.unidad_medida || '';

        // Precio de venta
        const inpPrecioEdit = document.getElementById('edit_precio');
        if (inpPrecioEdit) {
            const pv = parseFloat(data.precio_venta ?? data.precio ?? 0);
            inpPrecioEdit.value = Number.isFinite(pv) ? pv.toFixed(2) : '';
        }

        // Badge estado (Editar)
        const badgeE = document.getElementById('badgeEstadoEdit');
        badgeE.textContent = data.estado;

        if ((data.estado || '').toLowerCase() === 'activo') {
            badgeE.className = "badge bg-success";
        } else {
            badgeE.className = "badge bg-secondary";
        }

        // composición
        const tbody = document.querySelector('#tablaComposicionEdit tbody');
        tbody.innerHTML = '';
        (data.componentes || []).forEach(c => {
            const tr = document.createElement('tr');
            tr.dataset.idmp = c.idMP;
            tr.dataset.cantidad = c.cantidad;
            const unidad = c.unidad_medida ? ` ${c.unidad_medida}` : '';
            tr.innerHTML = `
        <td>${c.mp}</td>
        <td class="text-end">${Number(c.cantidad).toFixed(0)}${unidad}</td>
        <td class="text-center">
          <button type="button" class="btn btn-outline-danger btn-icon btnQuitarEdit" title="Quitar">
            <ion-icon name="close-circle-outline"></ion-icon>
          </button>
        </td>
    `;
            tbody.appendChild(tr);
        });
        renderEditRowsJSON();

        // abrir modal
        bsModal('modalEditarProducto').show();

    } catch (e) {
        console.error(e);
        alert('No se pudo cargar el producto para edición.');
    }
});

// agregar item en edición
document.getElementById('btnAgregarMPEdit')?.addEventListener('click', () => {
    const sel = document.getElementById('edit_mpSelect');
    const cant = document.getElementById('edit_mpCantidad');
    const idMP = parseInt(sel.value || '0', 10);
    const opt = sel.options[sel.selectedIndex];
    const txt = opt?.text || '';
    const unidad = opt?.getAttribute('data-unidad') || '';
    const qty = parseInt(cant.value || '0', 10);
    if (!idMP || !(qty > 0)) return;

    const tbody = document.querySelector('#tablaComposicionEdit tbody');
    const tr = document.createElement('tr');
    tr.dataset.idmp = idMP;
    tr.dataset.cantidad = qty;
    tr.innerHTML = `
    <td>${txt}</td>
    <td class="text-end">${Number(qty).toFixed(0)}${unidad ? ' ' + unidad : ''}</td>
    <td class="text-center">
      <button type="button" class="btn btn-outline-danger btn-icon btnQuitarEdit" title="Quitar">
        <ion-icon name="close-circle-outline"></ion-icon>
      </button>
    </td>
  `;
    tbody.appendChild(tr);
    sel.value = '';
    cant.value = '';
    renderEditRowsJSON();
});

// quitar item en edición
document.querySelector('#tablaComposicionEdit tbody')?.addEventListener('click', (e) => {
    const btn = e.target.closest?.('.btnQuitarEdit');
    if (!btn) return;
    const tr = btn.closest('tr');
    tr?.remove();
    renderEditRowsJSON();
});

// Limpiar validación del modal Editar al cerrarse
const modalEditEl = document.getElementById('modalEditarProducto');
if (modalEditEl) {
    modalEditEl.addEventListener('hidden.bs.modal', () => {
        const formEdit = document.getElementById('formEditarProducto');
        if (!formEdit) return;

        formEdit.classList.remove('was-validated');
        formEdit.querySelectorAll('.is-valid, .is-invalid').forEach(el => {
            el.classList.remove('is-valid', 'is-invalid');
        });

        const hiddenEdit = document.getElementById('componentesJsonEdit');
        if (hiddenEdit) hiddenEdit.value = '[]';

        const tbodyEdit = document.querySelector('#tablaComposicionEdit tbody');
        if (tbodyEdit) tbodyEdit.innerHTML = '';
    });
}

// guardar edición
document.getElementById('btnGuardarEdit')?.addEventListener('click', async () => {
    const form = document.getElementById('formEditarProducto');
    form.classList.add('was-validated');

    const comps = document.getElementById('componentesJsonEdit').value;
    if (!comps || JSON.parse(comps).length === 0) {
        alert('Debe agregar al menos una materia prima.');
        return;
    }
    if (!form.checkValidity()) return;

    try {
        const fd = new FormData(form);
        const r = await fetch('editar.php', { method: 'POST', body: fd });
        const js = await r.json();
        if (js?.ok) {
            bootstrap.Modal.getInstance(document.getElementById('modalEditarProducto'))?.hide();
            if ($.fn.dataTable && $.fn.dataTable.isDataTable('#tablaProductos')) {
                $('#tablaProductos').DataTable().ajax.reload(null, false);
            } else {
                location.reload();
            }
        } else {
            alert(js?.msg || 'No se pudieron guardar los cambios.');
        }
    } catch (e) {
        console.error(e);
        alert('Error de comunicación al guardar.');
    }
});

// Utilidad para abrir el modal de confirmación
function abrirModalConfirmacion({ id, accion }) {
    const isActivar = accion === 'activar';
    const titulo = isActivar ? 'Confirmar activación' : 'Confirmar desactivación';
    const mensaje = isActivar
        ? '¿Deseás activar nuevamente este producto?'
        : '¿Deseás desactivar este producto?';

    document.getElementById('confirmTitulo').textContent = titulo;
    document.getElementById('confirmMensaje').textContent = mensaje;
    document.getElementById('confirmIdProducto').value = id;
    document.getElementById('confirmAccion').value = accion;

    const modal = new bootstrap.Modal(document.getElementById('modalConfirmar'));
    modal.show();
}

// Click en botón ACTIVAR
$(document).on('click', '.btnActivar', function () {
    const id = $(this).data('id');
    abrirModalConfirmacion({ id, accion: 'activar' });
});

// Click en botón DESACTIVAR
$(document).on('click', '.btnDesactivar', function () {
    const id = $(this).data('id');
    abrirModalConfirmacion({ id, accion: 'desactivar' });
});

document.getElementById('btnConfirmarAccion').addEventListener('click', function () {
    const id = parseInt(document.getElementById('confirmIdProducto').value, 10);
    const accion = document.getElementById('confirmAccion').value; // 'activar' | 'desactivar'
    const endpoint = accion === 'activar' ? '../vista/activar.php' : '../vista/desactivar.php';

    const fd = new FormData();
    fd.append('idProducto', id);

    fetch(endpoint, { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.ok) {
                const modalEl = document.getElementById('modalConfirmar');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();

                if ($.fn.dataTable && $.fn.dataTable.isDataTable('#tablaProductos')) {
                    $('#tablaProductos').DataTable().ajax.reload(null, false);
                } else {
                    location.reload();
                }
            } else {
                alert('No se pudo completar la acción: ' + (res.error || 'Error desconocido'));
            }
        })
        .catch(() => alert('Error de comunicación.'));
});
