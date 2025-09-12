// Delegación para que funcione al paginar/redibujar
$(document).on('click', '#Empleados-lista tbody .editEmpleado', function () {
    let $tr = $(this).closest('tr'); if ($tr.hasClass('child')) $tr = $tr.prev();
    const d = $tr.children('td').map(function () { return $(this).text().trim(); }).get();
    // d[0]=id (hidden), d[1]=Apellido, Nombre, d[2]=DNI, d[3]=Puesto, d[4]=Fecha, d[5]=Estado badge (texto),
    // d[6]=email(hidden), d[7]=tel(hidden), d[8]=dir(hidden), d[9]=estado(hidden), d[10]=usuario_id(hidden)

    $('#edit_id_empleado').val(d[0]);
    const partes = (d[1] || '').split(','); // "Apellido, Nombre"
    $('#edit_apellido').val((partes[0] || '').trim());
    $('#edit_nombre').val((partes[1] || '').trim());
    $('#edit_dni').val(d[2]); $('#edit_puesto').val(d[3]); $('#edit_fecha_ingreso').val(d[4]);
    $('#edit_estado').val(d[9] || ((d[5] || '').includes('Activo') ? 'Activo' : 'Inactivo'));
    $('#edit_email').val(d[6]); $('#edit_telefono').val(d[7]); $('#edit_direccion').val(d[8]); $('#edit_usuario_id').val(d[10]);
    // --- Provincia / Localidad (nuevos) ---
    const provId = d[11] || '';
    const locId = d[12] || '';

    // Seteo provincia, cargo localidades y preselecciono la del empleado
    $('#edit_emp_provincia').val(provId);

    // Cargar localidades para esa provincia y seleccionar la del empleado
    cargarLocalidadesEdit(provId, locId);

    // Re-bind del change para permitir cambiar provincia en el modal de edición
    $('#edit_emp_provincia').off('change').on('change', function (e) {
        const nuevaProv = e.target.value;
        cargarLocalidadesEdit(nuevaProv, null); // al cambiar, limpiar selección de localidad
    });
    const uid = (d[10] || '').trim();
    $('#edit_usuario_id').val(uid !== '0' ? uid : '');  // '' => “Sin usuario”

    $('#editarEmpleado').modal('show');
});


$(document).on('click', '#Empleados-lista tbody .verEmpleado', function () {
    let $tr = $(this).closest('tr'); if ($tr.hasClass('child')) $tr = $tr.prev();
    const d = $tr.children('td').map(function () { return $(this).text().trim(); }).get();
    // d[0]=id, d[1]=Apellido, Nombre, d[2]=DNI, d[3]=Puesto, d[4]=Fecha,
    // d[5]=Estado (badge), d[6]=email, d[7]=tel, d[8]=dir, d[9]=estado raw,
    // d[10]=usuario_id, d[11]=provincia_id, d[12]=localidad_id

    $('#ver_apynom').val(d[1]);
    $('#ver_dni').val(d[2]);
    $('#ver_puesto').val(d[3]);
    $('#ver_fecha').val(d[4]);

    const estadoTxt = d[9] || ((d[5] || '').includes('Activo') ? 'Activo' : 'Inactivo');
    $('#ver_estado').val(estadoTxt);

    $('#ver_email').val(d[6]);
    $('#ver_tel').val(d[7]);
    $('#ver_dir').val(d[8]);

    // ------- Usuario vinculado -------
    const uid = (d[10] || '').trim();
    const uidKey = String(uid);
    let usuarioLabel = '—';
    if (uidKey) {
        if (window.EMP_USUARIOS && EMP_USUARIOS[uidKey]) {
            usuarioLabel = EMP_USUARIOS[uidKey];
        } else {
            // Fallback: buscar en el <select> de usuarios (nuevo o editar)
            const selNew = document.getElementById('new_usuario_id');
            const selEdit = document.getElementById('edit_usuario_id');
            const findOpt = (sel, val) => {
                if (!sel) return null;
                for (const opt of sel.options) { if (String(opt.value) === String(val)) return opt.text; }
                return null;
            };
            usuarioLabel = findOpt(selNew, uidKey) || findOpt(selEdit, uidKey) || '—';
        }
    }
    $('#ver_usuario').val(usuarioLabel);

    // ------- Provincia -------
    const provId = (d[11] || '').trim();
    const provKey = String(provId);
    let provLabel = '—';
    if (provKey) {
        if (window.EMP_PROVINCIAS && EMP_PROVINCIAS[provKey]) {
            provLabel = EMP_PROVINCIAS[provKey];
        } else {
            // Fallback: buscar en los selects que ya tienen las provincias cargadas
            const selNewProv = document.getElementById('emp_provincia');
            const selEditProv = document.getElementById('edit_emp_provincia');
            const findProv = (sel, val) => {
                if (!sel) return null;
                for (const opt of sel.options) { if (String(opt.value) === String(val)) return opt.text; }
                return null;
            };
            provLabel = findProv(selNewProv, provKey) || findProv(selEditProv, provKey) || provKey;
        }
    }
    $('#ver_provincia').val(provLabel);

    // ------- Localidad (via endpoint existente) -------
    const locId = (d[12] || '').trim();
    if (provKey) {
        $('#ver_localidad').val('Cargando...');
        const xhr = new XMLHttpRequest();
        xhr.open('GET', '/Bakersoft/proveedores/controlador/traerLocalidades.php?id_provincia=' + encodeURIComponent(provKey), true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        const cities = JSON.parse(xhr.responseText);
                        const found = cities.find(c => String(c.id_localidad) === String(locId));
                        $('#ver_localidad').val(found ? found.localidad : (locId || '—'));
                    } catch (e) {
                        $('#ver_localidad').val(locId || '—');
                    }
                } else {
                    $('#ver_localidad').val(locId || '—');
                }
            }
        };
        xhr.send();
    } else {
        $('#ver_localidad').val('—');
    }

    $('#verEmpleado').modal('show');

});

$(document).on('click', '#Empleados-lista tbody .deleteEmpleado', function () {
    let $tr = $(this).closest('tr'); if ($tr.hasClass('child')) $tr = $tr.prev();
    const d = $tr.children('td').map(function () { return $(this).text().trim(); }).get();
    $('#del_id').text(d[0]); $('#del_id_input').val(d[0]); $('#del_apynom').text(d[1]);
    $('#borrarEmpleado').modal('show');
});

// DataTable
var tablaEmp = new DataTable('#Empleados-lista', {
    language: {
        decimal: ",", thousands: ".",
        info: "Mostrando _END_ registros de un total de _TOTAL_",
        infoEmpty: "Mostrando registros del 0 al 0 de un total de 0 registros",
        infoFiltered: "(filtrado de un total de _MAX_ registros)",
        loadingRecords: "Cargando...",
        lengthMenu: "Mostrar _MENU_",
        paginate: { first: "<<", last: ">>", next: ">", previous: "<" },
        search: "Buscador:", searchPlaceholder: "Buscar...",
        emptyTable: "No hay registros para mostrar en la tabla",
    },
    lengthMenu: [5, 10, 25, 50], pageLength: 10,
    order: [[0, "asc"]], searching: true, paging: true, info: true, autoWidth: false, responsive: false
});

function cargarLocalidadesEdit(idProvincia, localidadSeleccionada) {
    const selLoc = document.getElementById('edit_emp_localidad');
    if (!selLoc) return;
    if (!idProvincia) {
        selLoc.innerHTML = '<option value="">-- Seleccioná provincia primero --</option>';
        return;
    }
    selLoc.innerHTML = '<option value="">Cargando localidades...</option>';

    const xhr = new XMLHttpRequest();
    xhr.open('GET', '/Bakersoft/proveedores/controlador/traerLocalidades.php?id_provincia=' + encodeURIComponent(idProvincia), true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                try {
                    const cities = JSON.parse(xhr.responseText);
                    selLoc.innerHTML = '<option value="">-- Seleccionar --</option>';
                    cities.forEach(function (c) {
                        const opt = document.createElement('option');
                        opt.value = c.id_localidad;
                        opt.text = c.localidad;
                        if (localidadSeleccionada && String(c.id_localidad) === String(localidadSeleccionada)) {
                            opt.selected = true;
                        }
                        selLoc.appendChild(opt);
                    });
                } catch (e) {
                    selLoc.innerHTML = '<option value="">Error cargando localidades</option>';
                }
            } else {
                selLoc.innerHTML = '<option value="">Error cargando localidades</option>';
            }
        }
    };
    xhr.send();
}
