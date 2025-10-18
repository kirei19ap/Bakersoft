(function () {
    "use strict";

    // ====== Helpers ======
    window.formatCuil = function (v) {
        if (!v) return '';
        const d = String(v).replace(/\D+/g, '');
        if (d.length !== 11) return String(v);
        return `${d.slice(0, 2)}-${d.slice(2, 10)}-${d.slice(10)}`;
    };
    window._get = (obj, k) => (obj && typeof obj[k] !== 'undefined' ? obj[k] : '');
    window._trim = (v) => (v == null ? '' : String(v).trim());
    function qs(sel, ctx) { return (ctx || document).querySelector(sel); }
    function qsa(sel, ctx) { return Array.from((ctx || document).querySelectorAll(sel)); }

    async function fetchJson(url) {
        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return await res.json();
    }

    function poblarSelect(select, items, valueKey, textKey, selectedValue) {
        select.innerHTML = '<option value="">-- Seleccionar --</option>';
        items.forEach(it => {
            const opt = document.createElement('option');
            opt.value = String(it[valueKey]);
            opt.text = String(it[textKey]);
            if (selectedValue != null && String(selectedValue) === String(it[valueKey])) {
                opt.selected = true;
            }
            select.appendChild(opt);
        });
    }

    // ====== Localidades por Provincia ======
    function getLocalidadesUrl(idProvincia) {
        // Ajustá si tu ruta difiere:
        return '/Bakersoft/proveedores/controlador/traerLocalidades.php?id_provincia=' + encodeURIComponent(idProvincia);
        // Alternativa relativa si cambia la raíz:
        // return '../../proveedores/controlador/traerLocalidades.php?id_provincia=' + encodeURIComponent(idProvincia);
    }

    function poblarSelectLocalidades(select, items, selectedValue, selectedName) {
        select.innerHTML = '<option value="">-- Seleccionar --</option>';
        items.forEach(it => {
            const id = String(it.id_localidad ?? it.idLocalidad ?? it.id ?? '');
            const text = String(it.localidad ?? it.nombre ?? it.text ?? '');
            if (!id || !text) return;
            const opt = document.createElement('option');
            opt.value = id;
            opt.text = text;
            select.appendChild(opt);
        });

        // 1) intento por ID
        if (selectedValue) {
            select.value = String(selectedValue);
        }
        // 2) si no hubo match y tengo nombre, intento por texto (case-insensitive)
        if ((!select.value || select.value === '') && selectedName) {
            const m = Array.from(select.options).find(
                o => o.text.trim().toLowerCase() === String(selectedName).trim().toLowerCase()
            );
            if (m) select.value = m.value;
        }
    }

    function wireLocalidades(provSelId, locSelId) {
        const selProv = qs('#' + provSelId);
        const selLoc = qs('#' + locSelId);
        if (!selProv || !selLoc) return;

        selProv.addEventListener('change', async (e) => {
            const idProv = e.target.value;
            if (!idProv) { selLoc.innerHTML = '<option value="">-- Seleccionar --</option>'; return; }

            const selectedKeep = selLoc.dataset.selected || '';         // ID deseado
            const selectedName = selLoc.dataset.selectedName || '';     // (opcional) nombre deseado
            selLoc.innerHTML = '<option value="">Cargando localidades...</option>';

            try {
                const data = await fetchJson(getLocalidadesUrl(idProv));
                const arr = Array.isArray(data) ? data : (Array.isArray(data?.data) ? data.data : []);
                poblarSelectLocalidades(selLoc, arr, selectedKeep, selectedName);

                // limpieza para próximas aperturas
                if (selectedKeep) selLoc.removeAttribute('data-selected');
                if (selectedName) selLoc.removeAttribute('data-selectedName');
            } catch (err) {
                selLoc.innerHTML = '<option value="">Error cargando localidades</option>';
            }
        });

    }

    function triggerLocalidadesOnModalShow(modalId, provSelId) {
        const modal = document.getElementById(modalId);
        const prov = document.getElementById(provSelId);
        const loc = document.getElementById('edit_emp_localidad');
        if (!modal || !prov) return;

        modal.addEventListener('shown.bs.modal', () => {
            // solo si aún no hay opciones cargadas
            if (!loc || loc.options.length > 1) return;
            prov.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }


    // ====== Estado civil (carga AJAX + helper de seteo) ======
    const API_ESTADO_CIVIL = '../controlador/traerEstadosCiviles.php';

    function cargarEstadosCiviles(select) {
        if (!select) return Promise.resolve();
        return fetch(API_ESTADO_CIVIL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(json => {
                select.innerHTML = '<option value="">-- Seleccionar --</option>';
                if (json && json.ok && Array.isArray(json.data)) {
                    json.data.forEach(ec => {
                        const opt = document.createElement('option');
                        opt.value = String(ec.id);
                        opt.textContent = ec.descripcion;
                        select.appendChild(opt);
                    });
                }
            })
            .catch(() => { select.innerHTML = '<option value="">(error cargando)</option>'; });
    }

    // ====== Validación FRONT (live) ======
    function hoyYmd() {
        const d = new Date();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${d.getFullYear()}-${m}-${day}`;
    }

    async function checkDniUnico(inputEl, excluirId) {
        const dni = (inputEl.value || '').trim();
        if (!dni) return;
        try {
            const url = `../controlador/verificarDni.php?dni=${encodeURIComponent(dni)}${excluirId ? ('&excluir=' + encodeURIComponent(excluirId)) : ''}`;
            const data = await fetchJson(url); // {existe: true|false}
            if (data && data.existe === true) {
                inputEl.setCustomValidity('Ya existe un empleado con ese DNI.');
            } else {
                inputEl.setCustomValidity('');
            }
            inputEl.reportValidity();
        } catch (e) { console.warn('Error verificando DNI por AJAX', e); }
    }

    function wireValidationForForm(form) {
        if (!form) return;

        const dni = qs('[name="dni"]', form);
        const fecha = qs('[name="fecha_ingreso"]', form);
        const email = qs('[name="email"]', form);
        const cuil = qs('[name="cuil"]', form);
        const legajo = qs('[name="legajo"]', form);
        const idEmp = qs('[name="id_empleado"]', form); // solo Editar

        if (dni) {
            dni.addEventListener('input', () => {
                const v = (dni.value || '').trim();
                if (!v) {
                    dni.setCustomValidity('El DNI es obligatorio.');
                } else if (!/^\d{7,8}$/.test(v)) {
                    dni.setCustomValidity('El DNI debe tener 8 dígitos numéricos.');
                } else {
                    dni.setCustomValidity('');
                }
                dni.reportValidity();
            });
            dni.addEventListener('blur', () => {
                const v = (dni.value || '').trim();
                if (/^\d{7,8}$/.test(v)) {
                    const excluir = idEmp ? (idEmp.value || '') : '';
                    checkDniUnico(dni, excluir);
                }
            });
        }

        if (fecha) {
            fecha.addEventListener('input', () => {
                const v = fecha.value;
                if (!v) {
                    fecha.setCustomValidity('La fecha de ingreso es obligatoria.');
                } else if (v > hoyYmd()) {
                    fecha.setCustomValidity('La fecha de ingreso no puede ser futura.');
                } else {
                    fecha.setCustomValidity('');
                }
                fecha.reportValidity();
            });
        }

        if (email) {
            email.addEventListener('input', () => {
                if (email.validity.typeMismatch) {
                    email.setCustomValidity('Ingresá un correo válido (ej: nombre@dominio.com).');
                } else {
                    email.setCustomValidity('');
                }
            });
        }

        async function ajaxUnico(url) {
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return await res.json(); // {existe: bool}
        }

        if (cuil) {
            cuil.addEventListener('input', () => {
                const digits = (cuil.value || '').replace(/\D+/g, '');
                if (digits && !/^\d{11}$/.test(digits)) {
                    cuil.setCustomValidity('El CUIL debe tener 11 dígitos (sin guiones).');
                } else {
                    cuil.setCustomValidity('');
                }
                cuil.reportValidity();
            });
            cuil.addEventListener('blur', async () => {
                const digits = (cuil.value || '').replace(/\D+/g, '');
                if (!digits || !/^\d{11}$/.test(digits)) return;
                try {
                    const url = `../controlador/verificarCUIL.php?cuil=${encodeURIComponent(digits)}${idEmp ? ('&excluir=' + encodeURIComponent(idEmp.value || '')) : ''}`;
                    const data = await ajaxUnico(url);
                    if (data && data.existe) {
                        cuil.setCustomValidity('Ya existe un empleado con ese CUIL.');
                    } else {
                        cuil.setCustomValidity('');
                    }
                    cuil.reportValidity();
                } catch (e) { /* noop */ }
            });
        }

        function isCrear() { return !idEmp; }

        if (legajo) {
            legajo.addEventListener('input', () => {
                if (isCrear() && !legajo.value.trim()) {
                    legajo.setCustomValidity('El legajo es obligatorio.');
                } else {
                    legajo.setCustomValidity('');
                }
                legajo.reportValidity();
            });
            legajo.addEventListener('blur', async () => {
                if (!isCrear()) return;
                const v = (legajo.value || '').trim();
                if (!v) return;
                try {
                    const res = await fetch(`../controlador/verificarLegajo.php?legajo=${encodeURIComponent(v)}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        if (data && data.existe) {
                            legajo.setCustomValidity('Ya existe un empleado con ese Legajo.');
                        } else {
                            legajo.setCustomValidity('');
                        }
                        legajo.reportValidity();
                    }
                } catch (e) { /* noop */ }
            });
        }

        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    }

    function wireFormsValidation() {
        qsa('.needs-validation').forEach(wireValidationForForm);
    }

    // ====== Cambiar Estado (modal) ======
    function wireCambiarEstadoModal() {
        qsa('.btnToggleEstado').forEach(btn => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.id;
                const apy = btn.dataset.apynom;
                const est = btn.dataset.estado;

                const txtAccion = qs('#txt_accion_estado');
                const lblApynom = qs('#estado_apynom');
                const lblId = qs('#estado_id');
                const hidId = qs('#estado_id_input');

                const accion = (est === 'Activo') ? 'inactivar' : 'activar';
                if (txtAccion) txtAccion.textContent = accion;
                if (lblApynom) lblApynom.textContent = apy;
                if (lblId) lblId.textContent = id;
                if (hidId) hidId.value = id;
            });
        });
    }

    // ====== DataTable ======
    function initDataTable() {
        const tbl = qs('#Empleados-lista');
        if (!tbl) return;
        new DataTable('#Empleados-lista', {
            responsive: true,
            autoWidth: false,
            lengthChange: true,
            pageLength: 10,
            order: [[0, 'asc']],
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
                "emptyTable": "No hay registros para mostrar en la tabla",
            },
            columnDefs: [
                { targets: [7, 15], className: 'text-center' }
            ]
        });
    }

    // ====== Init ======
    document.addEventListener('DOMContentLoaded', async function () {
        initDataTable();
        wireFormsValidation();

        // Localidades Crear y Editar
        wireLocalidades('emp_provincia', 'emp_localidad');
        wireLocalidades('edit_emp_provincia', 'edit_emp_localidad');
        triggerLocalidadesOnModalShow('editarEmpleado', 'edit_emp_provincia');

        // Estado civil: precargar en ambos modales
        await Promise.all([
            cargarEstadosCiviles(document.getElementById('emp_estado_civil')),
            cargarEstadosCiviles(document.getElementById('edit_estado_civil'))
        ]);

        wireCambiarEstadoModal();
    });

})();

// ===================== MODAL EDITAR =====================

// Select seguro para Puesto (sin duplicar opciones)
function setPuestoEnEditar(modal, getVal) {
    const sel = modal.querySelector('#edit_puesto'); // <select name="puesto">
    if (!sel) return;

    const pid = String(getVal('puestoId') ?? '').trim();
    const ptext = String(getVal('puesto') ?? '').trim();

    if (sel.dataset.dynamicOptId && sel.dataset.dynamicOptId !== pid) {
        const oldDyn = sel.querySelector('option[data-dynamic="1"]');
        if (oldDyn) oldDyn.remove();
        delete sel.dataset.dynamicOptId;
    }

    if (pid && pid !== '0') {
        let opt = sel.querySelector(`option[value="${CSS.escape(pid)}"]`);
        if (!opt) {
            opt = document.createElement('option');
            opt.value = pid;
            opt.textContent = ptext || `Puesto ${pid}`;
            opt.setAttribute('data-dynamic', '1');
            sel.appendChild(opt);
            sel.dataset.dynamicOptId = pid;
        }
        sel.value = pid;
    } else if (ptext) {
        const opt = Array.from(sel.options).find(o => o.text.trim().toLowerCase() === ptext.toLowerCase());
        sel.value = opt ? opt.value : '';
    } else {
        sel.value = '';
    }

    if (window.jQuery && jQuery.fn && jQuery.fn.select2 && window.jQuery(sel).hasClass('select2-hidden-accessible')) {
        window.jQuery(sel).val(sel.value).trigger('change.select2');
    }
}

// Select seguro para Estado civil (espera carga y selecciona por ID)
async function setEstadoCivilEnEditar(modal, getVal) {
    const sel = modal.querySelector('#edit_estado_civil'); // <select name="id_estado_civil">
    if (!sel) return;

    const idEC = String(getVal('estadoCivilId') ?? getVal('id_estado_civil') ?? '').trim();
    const txtEC = String(getVal('estadoCivil') ?? getVal('estado_civil') ?? '').trim();

    // Si el combo aún no está cargado (sólo placeholder), esperá un tick
    if (sel.options.length <= 1) {
        await new Promise(res => setTimeout(res, 120));
    }

    if (idEC) {
        let opt = Array.from(sel.options).find(o => o.value === idEC);
        if (!opt && txtEC) {
            const tmp = document.createElement('option');
            tmp.value = idEC;
            tmp.textContent = txtEC;
            tmp.dataset.dynamic = '1';
            sel.appendChild(tmp);
        }
        sel.value = idEC;
    } else if (txtEC) {
        const optTxt = Array.from(sel.options).find(o => o.text.trim().toLowerCase() === txtEC.toLowerCase());
        sel.value = optTxt ? optTxt.value : '';
    } else {
        sel.value = '';
    }

    if (window.jQuery && jQuery.fn && jQuery.fn.select2 && window.jQuery(sel).hasClass('select2-hidden-accessible')) {
        window.jQuery(sel).val(sel.value).trigger('change.select2');
    }
}

// Abrir modal de Edición y mapear TODOS los campos
document.addEventListener('click', async function (e) {
    const btn = e.target.closest('.editEmpleado');
    if (!btn) return;

    const modal = document.getElementById('editarEmpleado');
    if (!modal) return;

    const ds = btn.dataset;
    const get = (k, ...alts) => {
        const keys = [k, ...alts];
        for (const kk of keys) {
            if (ds[kk] != null && String(ds[kk]).trim() !== '') return String(ds[kk]).trim();
        }
        return '';
    };

    const setVal = (selector, value, { triggerChange = false } = {}) => {
        const el = modal.querySelector(selector);
        if (!el) return;
        el.value = value ?? '';
        if (triggerChange) {
            if (window.jQuery && jQuery.fn && jQuery.fn.select2 && window.jQuery(el).hasClass('select2-hidden-accessible')) {
                window.jQuery(el).val(el.value).trigger('change.select2');
            } else {
                el.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
    };

    const toDateInput = (v) => {
        if (!v) return '';
        const s = String(v).slice(0, 10);
        return /^\d{4}-\d{2}-\d{2}$/.test(s) ? s : '';
    };

    // Básicos
    setVal('#edit_id_empleado', get('id', 'idempleado', 'idEmpleado'));
    setVal('#edit_nombre', get('nombre'));
    setVal('#edit_apellido', get('apellido'));
    setVal('#edit_dni', get('dni'));
    setVal('#edit_sexo', get('sexo', 'genero'));

    // Fechas
    setVal('#edit_fecha_nac', toDateInput(get('fechanac', 'fecha_nacimiento')));
    setVal('#edit_fecha_ingreso', toDateInput(get('fecha', 'fecha_ingreso', 'fechaalta', 'fecha_alta')));

    // Contacto/otros
    setVal('#edit_cuil', get('cuil'));
    setVal('#edit_legajo', get('legajo'));
    setVal('#edit_email', get('email'));
    setVal('#edit_telefono', get('telefono', 'telefono_particular'));
    setVal('#edit_direccion', get('direccion'));
    setVal('#edit_estado', get('estado'), { triggerChange: true });

    // Usuario (si existe)
    const selUsuario = modal.querySelector('#edit_usuario_id');
    if (selUsuario) {
        const uid = get('usuarioId', 'usuario_id', 'usuario');
        const uname = get('usuarioNombre', 'usuario_nombre', 'usuarioLabel', 'usuario_label');
        if (uid) {
            selUsuario.value = uid;
            if (selUsuario.value !== uid) {
                const opt = document.createElement('option');
                opt.value = uid;
                opt.textContent = uname || `Usuario ${uid}`;
                opt.dataset.dynamic = '1';
                selUsuario.appendChild(opt);
                selUsuario.value = uid;
            }
            if (window.jQuery && jQuery.fn && jQuery.fn.select2 && window.jQuery(selUsuario).hasClass('select2-hidden-accessible')) {
                window.jQuery(selUsuario).val(uid).trigger('change.select2');
            }
        } else {
            selUsuario.value = '';
        }
    }

    // Puesto (ID seguro)
    setPuestoEnEditar(modal, (k) => get(k));

    // Estado civil (espera carga y elige)
    await setEstadoCivilEnEditar(modal, (k) => get(k));

    // Provincia / Localidad
    // === Provincia / Localidad en EDITAR (ID + fallback nombre) ===
    const selProv = modal.querySelector('#edit_emp_provincia');
    const selLoc = modal.querySelector('#edit_emp_localidad');
    if (selProv) {
        const provId = get('provincia', 'provincia_id', 'id_provincia');
        const locId = get('localidad', 'localidad_id', 'id_localidad');       // ✅ esperamos ID acá
        const locName = get('localidadNombre', 'localidad_nombre');             // opcional, texto

        selProv.value = provId || '';
        if (selLoc) {
            if (locId) selLoc.dataset.selected = String(locId);
            if (locName) selLoc.dataset.selectedName = String(locName);
        }

        // disparar después de setear dataset (evita carreras)
        setTimeout(() => {
            selProv.dispatchEvent(new Event('change', { bubbles: true }));
        }, 80);
    }


    if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
        const bsModal = bootstrap.Modal.getOrCreateInstance(modal);
        bsModal.show();
    }
});

// Limpieza de opciones dinámicas al cerrar
document.addEventListener('hidden.bs.modal', function (e) {
    const modal = e.target;
    if (!modal || modal.id !== 'editarEmpleado') return;

    const selP = modal.querySelector('#edit_puesto');
    if (selP) {
        const dyn = selP.querySelector('option[data-dynamic="1"]');
        if (dyn) dyn.remove();
        delete selP.dataset.dynamicOptId;
    }
    const selU = modal.querySelector('#edit_usuario_id');
    if (selU) {
        const dynU = selU.querySelector('option[data-dynamic="1"]');
        if (dynU) dynU.remove();
    }
    const selEC = modal.querySelector('#edit_estado_civil');
    if (selEC) {
        const dynEC = selEC.querySelector('option[data-dynamic="1"]');
        if (dynEC) dynEC.remove();
    }
});

// ===================== MODAL VER =====================
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.verEmpleado');
    if (!btn) return;

    const modal = document.getElementById('verEmpleado');
    if (!modal) return;

    const ds = btn.dataset;
    const v = (k) => _trim(ds[k]);

    // Usuario texto (si tenés ese campo)
    const uInput = modal.querySelector('[data-ver="usuario"]');
    if (uInput) uInput.value = (v('usuarioNombre') || v('usuario') || '');

    const getEl = (sel) => modal.querySelector(sel);
    const apynom = `${v('apellido')}, ${v('nombre')}`.replace(/^, /, '').trim();

    function setVer(sel, value) {
        const el = getEl(sel);
        if (el && 'value' in el) el.value = value ?? '';
    }

    setVer('[data-ver="apynom"]', apynom);
    setVer('[data-ver="dni"]', v('dni'));
    setVer('[data-ver="sexo"]', v('sexo'));
    setVer('[data-ver="fechanac"]', v('fechanac'));
    setVer('[data-ver="cuil"]', window.formatCuil(v('cuil')));
    setVer('[data-ver="legajo"]', v('legajo'));
    setVer('[data-ver="puesto"]', v('puesto'));
    setVer('[data-ver="fecha"]', v('fecha'));

    // Estado + badge
    setVer('[data-ver="estado"]', v('estado'));
    const badge = modal.querySelector('[data-ver="estado-badge"]');
    if (badge) {
        badge.classList.remove('bg-success', 'bg-secondary', 'bg-danger', 'bg-warning', 'text-dark');
        const estado = (v('estado') || '').toLowerCase();
        badge.textContent = v('estado') || '—';
        if (estado === 'activo') badge.classList.add('bg-success');
        else if (estado === 'inactivo') badge.classList.add('bg-secondary');
        else badge.classList.add('bg-warning', 'text-dark');
    }

    setVer('[data-ver="email"]', v('email'));
    setVer('[data-ver="telefono"]', v('telefono'));
    setVer('[data-ver="direccion"]', v('direccion'));

    // Provincia / Localidad (nombres si los tenés en data-*)
    setVer('[data-ver="provincia"]', v('provinciaNombre') || v('provincia'));
    setVer('[data-ver="localidad"]', v('localidadNombre') || v('localidad'));

    // ✅ Estado civil en modal VER
    setVer('[data-ver="estado_civil"]', v('estadoCivil') || v('estado_civil') || '');
});

// Normalizar CUIL al enviar (quitar guiones/puntos)
document.addEventListener('submit', function (e) {
    const form = e.target.closest('form');
    if (!form) return;
    const c = form.querySelector('[name="cuil"]');
    if (c && c.value) c.value = c.value.replace(/\D+/g, '');
}, true);
