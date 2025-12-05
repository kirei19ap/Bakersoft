// admempleados/js/buscadorEmpleados.js
(function () {
  let dt = null;          // instancia DataTable
  let initialized = false; // si la tabla fue creada
  const $ = (sel) => document.querySelector(sel);

  // URL del endpoint (definida en buscador.php como window.BASE_URL opcional)
  //const API_BUSCAR = (window.BASE_URL || '') + '/controlador/buscar_empleados.php';
  const moduleRoot = window.location.pathname.replace(/\/vista\/.*$/, '');
  const API_BUSCAR = `${moduleRoot}/controlador/buscar_empleados.php`;
  const API_VER = `${moduleRoot}/controlador/ver_empleado.php`;
  function validarRangoFechas() {
    const d = $('#f_desde').value;
    const h = $('#f_hasta').value;
    if (d && h && d > h) {
      Swal.fire({ icon: 'warning', title: 'Rango inválido', text: 'La fecha "Desde" no puede ser mayor que "Hasta".' });
      return false;
    }
    return true;
  }

  function fmtDateYMDToDMY(ymd) {
    if (!ymd) return '—';
    const s = String(ymd).substring(0, 10);
    const [y, m, d] = s.split('-');
    return (y && m && d) ? `${d}/${m}/${y}` : ymd;
  }

  function setText(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = (val ?? '—');
  }
  function fmtDateYMDToDMY(ymd) {
    if (!ymd) return '—';
    // espera 'YYYY-MM-DD' o 'YYYY-MM-DD HH:mm:ss'
    const parts = ymd.substring(0, 10).split('-');
    if (parts.length !== 3) return ymd;
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
  }

  function setVer(name, value) {
    const el = document.querySelector(`[data-ver="${name}"]`);
    if (el) el.value = (value ?? '—');
  }
  function setBadgeEstado(valor) {
    const badge = document.querySelector('[data-ver="estado-badge"]');
    if (!badge) return;
    const activo = (valor === 'Activo' || valor === 1 || valor === '1');
    badge.classList.remove('bg-success', 'bg-secondary');
    badge.classList.add(activo ? 'bg-success' : 'bg-secondary');
    badge.textContent = activo ? 'Activo' : 'Inactivo';
  }
  function fmtDMY(ymd) {
    if (!ymd) return '—';
    const s = String(ymd).slice(0, 10);
    const [y, m, d] = s.split('-');
    return (y && m && d) ? `${d}/${m}/${y}` : ymd;
  }

  function renderDetalleEmpleado(emp) {
    const estadoBadge = (emp.estado === 'Activo' || emp.estado === 1 || emp.estado === '1')
      ? '<span class="badge bg-success">Activo</span>'
      : '<span class="badge bg-secondary">Inactivo</span>';

    return `
    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label fw-semibold">Legajo</label>
        <div>${emp.legajo ?? '—'}</div>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Fecha de ingreso</label>
        <div>${fmtDateYMDToDMY(emp.fecha_alta)}</div>
      </div>

      <div class="col-md-6">
        <label class="form-label fw-semibold">Nombre</label>
        <div>${emp.nombre ?? '—'}</div>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Apellido</label>
        <div>${emp.apellido ?? '—'}</div>
      </div>

      <div class="col-md-6">
        <label class="form-label fw-semibold">DNI</label>
        <div>${emp.dni ?? '—'}</div>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Email</label>
        <div>${emp.email ?? '—'}</div>
      </div>

      <div class="col-md-6">
        <label class="form-label fw-semibold">Puesto</label>
        <div>${emp.puesto ?? 'Sin asignar'}</div>
      </div>
      <div class="col-md-6">
        <label class="form-label fw-semibold">Estado</label>
        <div>${estadoBadge}</div>
      </div>
    </div>
  `;
  }


  function construirAjaxData(d) {
    // Búsqueda global
    d.search = d.search || {};
    d.search.value = $('#qGlobal').value;

    // Filtros
    d.f_estado = $('#f_estado').value;
    d.f_puesto = $('#f_puesto') ? $('#f_puesto').value : ''; // por si tu select se llama distinto
    d.f_desde = $('#f_desde').value;
    d.f_hasta = $('#f_hasta').value;
  }

  function initDataTable() {
    dt = new DataTable('#tblBusquedaEmpleados', {
      processing: true,
      serverSide: true,
      searching: false,
      deferRender: true,
      pageLength: 10,
      ajax: {
        url: API_BUSCAR,
        type: 'POST',
        data: function (d) { construirAjaxData(d); },

        // 👇 MUY IMPORTANTE: corta el spinner y valida el JSON
        dataSrc: function (json) {
          try {
            if (json && typeof json.recordsTotal !== 'undefined' && Array.isArray(json.data)) {
              return json.data; // OK -> DataTables renderiza y oculta el spinner
            }
            console.error('Respuesta inválida del servidor:', json);
            Swal.fire({
              icon: 'error',
              title: 'Error de búsqueda',
              text: (json && json.error) ? json.error : 'La respuesta del servidor no es válida.'
            });
          } catch (e) {
            console.error('Parse error:', e);
            Swal.fire({ icon: 'error', title: 'Error de búsqueda', text: 'No se pudo interpretar la respuesta.' });
          }
          return []; // Devuelve vacío para que DataTables cierre “Processing…”
        },

        error: function (xhr) {
          console.error('AJAX error:', xhr?.status, xhr?.responseText);
          Swal.fire({
            icon: 'error',
            title: 'No se pudo buscar',
            text: 'Verificá la conexión o intentá nuevamente.'
          });
        }
      },

      // 👇 Español para todo el UI de la tabla
      language: {
        "decimal": ",",
        "thousands": ".",
        "info": "Mostrando _END_ registros de un total de _TOTAL_",
        "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
        "infoPostFix": "",
        "infoFiltered": "(filtrado de un total de _MAX_ registros)",
        "loadingRecords": "Cargando...",
        "lengthMenu": "Mostrar _MENU_",
        "zeroRecords": "No se encontraron registros para los datos ingesados.",
        "paginate": {
          "first": "<<",
          "last": ">>",
          "next": ">",
          "previous": "<"
        },
        "emptyTable": "No hay registros para mostrar en la tabla",
      },

      columns: [
        { data: 'legajo' },
        { data: 'nombre' },
        { data: 'apellido' },
        { data: 'dni' },
        { data: 'email' },
        { data: 'puesto' },
        {
          data: 'estado',
          render: function (val) {
            const isOn = (val === 'Activo' || val === 1 || val === '1');
            const badge = isOn ? 'bg-success' : 'bg-secondary';
            const txt = (val === 1 || val === '1') ? 'Activo' :
              (val === 0 || val === '0') ? 'Inactivo' : (val || '—');
            return `<span class="badge ${badge}">${txt}</span>`;
          }
        },
        { data: 'fecha_alta' },
        {
          data: null, orderable: false, searchable: false,
          className: 'text-center',
          render: function (row) {
            return `
            <div class="btn-group btn-group-sm" role="group">
              <button class="btn btn-info verBtn" data-id="${row.id}">Ver</button>
            </div>`;
          }
        }
      ],
      order: [[2, 'asc']]
    });


    // Delegación: única acción “Ver”
    document.querySelector('#tblBusquedaEmpleados').addEventListener('click', function (ev) {
      const btn = ev.target.closest('button');
      if (!btn) return;
      const id = btn.getAttribute('data-id');
      if (btn.classList.contains('verBtn')) {
        const id = btn.getAttribute('data-id');
        const modal = new bootstrap.Modal(document.getElementById('verEmpleado'));

        // placeholders
        ['apynom', 'dni', 'sexo', 'fechanac', 'cuil', 'legajo', 'estado', 'puesto', 'fecha', 'usuario', 'provincia', 'localidad', 'email', 'telefono', 'direccion']
          .forEach(k => setVer(k, '—'));
        setBadgeEstado('—');

        modal.show();

        fetch(API_VER, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
          body: new URLSearchParams({ id })
        })
          .then(r => r.json())
          .then(json => {
            if (!json || json.ok !== true || !json.data) {
              throw new Error(json && json.error ? json.error : 'Respuesta inválida');
            }
            const e = json.data;

            // Apellido, Nombre
            setVer('apynom', [e.apellido, e.nombre].filter(Boolean).join(', '));
            setVer('dni', e.dni);
            setVer('sexo', e.sexo);
            setVer('fechanac', fmtDMY(e.fecha_nacimiento));   // ajustar si tu campo es 'fecha_nac'
            setVer('cuil', e.cuil);
            setVer('legajo', e.legajo);
            setBadgeEstado(e.estado);
            setVer('estado', e.estado); // por si mostrás el texto también
            setVer('puesto', e.puesto || 'Sin asignar');
            setVer('fecha', fmtDMY(e.fecha_alta));            // el modelo expone fecha_ingreso como fecha_alta
            setVer('usuario', e.usuario);
            setVer('provincia', e.provincia);
            setVer('localidad', e.localidad);
            setVer('email', e.email);
            setVer('telefono', e.telefono);
            setVer('direccion', e.direccion);
          })
          .catch(err => {
            console.error(err);
            Swal.fire({ icon: 'error', title: 'Error', text: err.message || 'No se pudo cargar el detalle.' });
          });
      }


    });

    initialized = true;
  }

  function ejecutarBusqueda() {
    if (!validarRangoFechas()) return;
    const q = document.querySelector('#qGlobal').value.trim();
    const estado = document.querySelector('#f_estado').value.trim();
    const puesto = document.querySelector('#f_puesto') ? document.querySelector('#f_puesto').value.trim() : '';
    const desde = document.querySelector('#f_desde').value.trim();
    const hasta = document.querySelector('#f_hasta').value.trim();

    if (!q && !estado && !puesto && !desde && !hasta) {
      Swal.fire({
        icon: 'warning',
        title: 'Sin criterios de búsqueda',
        text: 'Debe completar al menos un campo o filtro antes de buscar.'
      });
      return;
    }
    if (!initialized) {
      initDataTable();     // crear tabla recién en el primer "Buscar"
    } else {
      dt.draw();           // siguientes búsquedas/redibujos
    }
  }

  function limpiarFormulario() {
    $('#qGlobal').value = '';
    $('#f_estado').value = '';
    if ($('#f_puesto')) $('#f_puesto').value = '';
    $('#f_desde').value = '';
    $('#f_hasta').value = '';
  }

  function resetearTabla() {
    // Destruir DataTable y dejar la tabla vacía (sin resultados)
    if (initialized && dt) {
      dt.destroy(); // destruye instancia y eventos internos
      dt = null;
      initialized = false;
    }
    // Vaciar tbody para que quede visualmente en blanco
    const tbody = document.querySelector('#tblBusquedaEmpleados tbody');
    if (tbody) tbody.innerHTML = '';
  }

  function bindUI() {
    // Buscar
    $('#btnBuscar').addEventListener('click', ejecutarBusqueda);

    // Enter en la caja de búsqueda
    $('#qGlobal').addEventListener('keydown', function (e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        ejecutarBusqueda();
      }
    });

    // Limpiar: vuelve a estado en blanco (sin resultados)
    $('#btnLimpiar').addEventListener('click', function () {
      limpiarFormulario();
      resetearTabla();
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    bindUI();
    // Importante: NO inicializamos la tabla aquí → no hay request ni resultados al abrir
  });
})();
