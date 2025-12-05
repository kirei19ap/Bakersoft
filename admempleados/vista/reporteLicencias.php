<?php
include_once("../../includes/head_app.php");
require_once(__DIR__ . "/../controlador/controladoradmempleado.php");
$obj = new ControladorAdmEmpleado();
// admempleados/vista/buscador.php
// Opcional: $roles puede venir del controlador si querés renderizar el combo de Rol
// $roles = $roles ?? [];
?>


<!-- Encabezado -->
<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Reportes</h1>
</div>


<div class="contenido-principal">
    <div class="container-fluid">
        <!-- Parámetros -->
        <div class="card mb-3">
            <div class="card-header bg-light"><strong>Listado integral de licencias por rango de fechas y tipo</strong></div>
            <div class="card-body">
                <form id="frmParams" class="row g-3">
                    <div class="col-sm-3">
                        <label class="form-label">Desde *</label>
                        <input type="date" class="form-control" id="fdesde" required>
                    </div>
                    <div class="col-sm-3">
                        <label class="form-label">Hasta *</label>
                        <input type="date" class="form-control" id="fhasta" required>
                    </div>
                    <div class="col-sm-3">
                        <label for="tipoLicencia" class="form-label">Tipo de licencia</label>
                        <select id="tipoLicencia" class="form-select">
                            <option value="">Todas</option>
                        </select>
                    </div>
                    <div class="col-sm-3">
                        <label for="estadoLicencia" class="form-label">Estado</label>
                        <select id="estadoLicencia" class="form-select">
                            <option value="">Todos</option>
                        </select>
                    </div>
<small class="text-muted d-block mt-2">* Rango de fechas obligatorio. Orden: fecha de inicio ascendente.</small>
                    <div class="col-12 d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-primary">
                            Generar listado
                        </button>
                        <button type="button" id="btnPdf" class="btn btn-outline-danger" disabled>
                            Exportar PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Resultado -->
        <div class="card">
            <div class="card-header bg-light"><strong>Resultado</strong></div>
            <div class="card-body">
                <div id="alerta" class="alert alert-warning d-none mb-0"></div>
                <div class="table-responsive">
                    <table id="tablaReporte" class="table table-striped table-hover table-bordered align-middle w-100">
                        <thead class="table-light">
                            <tr>
                                <th>Empleado</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Desde</th>
                                <th>Hasta</th>
                                <th class="text-center">Días</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
</div>

<!-- === Librerías en el orden correcto === -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

<!-- jQuery SIEMPRE antes que DataTables -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>

<!-- DataTables núcleo + Bootstrap -->
<script src="https://cdn.datatables.net/2.3.0/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.0/js/dataTables.bootstrap5.min.js"></script>

<!-- (Opcional) Si luego usás Buttons/Responsive/FixedHeader, cargalos aquí -->

<!-- === Tu script: envuelto para garantizar que $ exista === -->
<script>
    (function($) {
        const $tabla = $('#tablaReporte');
        const $alerta = $('#alerta');
        const $btnPdf = $('#btnPdf');
        const $tipo = $('#tipoLicencia');
        const $estado = $('#estadoLicencia');
        let dt = null;
        let ultimoRango = null;


        function fmtFechaDMY(s) {
            if (!s) return '';
            const [y, m, d] = String(s).split(' ')[0].split('-');
            if (!y || !m || !d) return s;
            return `${d}/${m}/${y}`;
        }

        function normalizarEstado(row) {
            const byId = {
                1: 'Nueva',
                2: 'Pendiente de envío',
                3: 'Pendiente de aprobación',
                4: 'Cancelada',
                5: 'Aprobada',
                6: 'Rechazada'
            };
            let txt = row.estado ?? row.estado_nombre ?? row.nombre_estado ?? '';
            if (!txt && row.id_estado != null) {
                const n = Number(row.id_estado);
                if (!Number.isNaN(n)) txt = byId[n] || '';
            }
            return txt || '—';
        }

        function initDT() {
            if (dt) return dt;
            dt = $tabla.DataTable({
                dom: "<'row align-items-center mb-2'<'col-md-6'l>>" +
                    "rt" +
                    "<'row mt-2'<'col-md-5'i><'col-md-7'p>>",
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
                    "emptyTable": "No hay registros para mostrar en la tabla",
                },
                autoWidth: false,
                pageLength: 25,
                order: [
                    [3, 'asc']
                ], // 3 = Desde
                columns: [{
                        data: 'empleado'
                    },
                    {
                        data: 'tipo'
                    },
                    {
                        data: null,
                        className: 'text-center',
                        render: (row, type) => {
                            const txt = normalizarEstado(row);
                            if (type !== 'display') return txt;
                            const e = txt.toLowerCase();
                            const cls = e.includes('aprob') ? 'success' :
                                e.includes('rechaz') ? 'danger' :
                                e.includes('pendiente de aprobación') ? 'warning' :
                                e.includes('pendiente de envío') ? 'secondary' :
                                e.includes('cancel') ? 'dark' :
                                e.includes('nueva') ? 'info' :
                                'secondary';
                            return `<span class="badge text-bg-${cls}">${txt}</span>`;
                        }
                    },
                    {
                        data: 'fecha_inicio',
                        className: 'text-center',
                        render: (s) => fmtFechaDMY(s),
                        width: '120px'
                    },
                    {
                        data: 'fecha_fin',
                        className: 'text-center',
                        render: (s) => fmtFechaDMY(s),
                        width: '120px'
                    },
                    {
                        data: 'cantidad_dias',
                        className: 'text-center',
                        width: '80px'
                    }
                ]
            });
            return dt;
        }

        function cargarEstados() {
            const fd = new FormData();
            fd.append('accion', 'rrhh_listar_estados');

            fetch('../../licencias/controlador/controladorLicencias.php', {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(resp => {
                    if (!resp?.ok || !Array.isArray(resp.items)) return;

                    // Limpiamos todo menos "Todos"
                    $estado.find('option:not(:first)').remove();

                    resp.items.forEach(e => {
                        // Según tu tabla:
                        // id_estado, nombre
                        const id = e.id_estado ?? e.id;
                        const desc = e.nombre ?? '';

                        if (!id || !desc) return;

                        const opt = document.createElement('option');
                        opt.value = id;
                        opt.textContent = desc;
                        $estado.append(opt);
                    });
                })
                .catch(() => {
                    // Si falla, dejamos solo "Todos"
                });
        }


        function cargarTipos() {
            const fd = new FormData();
            fd.append('accion', 'rrhh_listar_tipos');

            fetch('../../licencias/controlador/controladorLicencias.php', {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(resp => {
                    if (!resp?.ok || !Array.isArray(resp.items)) return;

                    // Limpiamos todo menos la opción "Todas"
                    $tipo.find('option:not(:first)').remove();

                    resp.items.forEach(t => {
                        // 👇 Ajustá los nombres según lo que devuelva listarTipos()
                        const id = t.id_tipo_licencia ?? t.id_tipo ?? t.id ?? null;
                        const desc = t.nombre ?? t.descripcion ?? t.tipo ?? null;

                        if (id == null || !desc) return;

                        const opt = document.createElement('option');
                        opt.value = id;
                        opt.textContent = desc;
                        $tipo.append(opt);
                    });
                })
                .catch(() => {
                    // Si falla, dejamos solo "Todas"
                });
        }


        function cargar(desde, hasta, tipo, estado) {
            const fd = new FormData();
            fd.append('accion', 'rrhh_reporte_listar');
            fd.append('desde', desde);
            fd.append('hasta', hasta);
            fd.append('tipo', tipo || '');
            fd.append('estado', estado || '');

            return fetch('../../licencias/controlador/controladorLicencias.php', {
                method: 'POST',
                body: fd
            }).then(r => r.json());
        }




        // Submit de parámetros
        $('#frmParams').on('submit', function(e) {
            e.preventDefault();
            const desde = $('#fdesde').val();
            const hasta = $('#fhasta').val();
            const tipo = $tipo.val();
            const estado = $estado.val();

            if (!desde || !hasta) {
                $alerta.removeClass('d-none alert-success').addClass('alert-warning')
                    .text('Seleccioná un rango de fechas.');
                $btnPdf.prop('disabled', true);
                return;
            }
            if (desde > hasta) {
                $alerta.removeClass('d-none alert-success').addClass('alert-warning')
                    .text('La fecha Desde no puede ser mayor que Hasta.');
                $btnPdf.prop('disabled', true);
                return;
            }

            $alerta.addClass('d-none').text('');

            cargar(desde, hasta, tipo, estado).then(resp => {
                const tabla = initDT();

                if (!resp?.ok) {
                    $alerta.removeClass('d-none alert-success').addClass('alert-warning')
                        .text(resp?.msg || 'No autorizado');
                    $btnPdf.prop('disabled', true);
                    tabla.clear().draw();
                    return;
                }

                const items = resp.items || [];
                if (items.length === 0) {
                    $alerta.removeClass('d-none alert-success').addClass('alert-warning')
                        .text('No se encontraron licencias en el período definido.');
                    $btnPdf.prop('disabled', true);
                    tabla.clear().draw();
                    return;
                }

                ultimoRango = {
                    desde,
                    hasta,
                    tipo,
                    estado
                };
                $btnPdf.prop('disabled', false);
                $alerta.addClass('d-none').text('');
                tabla.clear().rows.add(items).draw();
            }).catch(() => {
                $alerta.removeClass('d-none alert-success').addClass('alert-warning')
                    .text('Error al obtener el reporte.');
                $btnPdf.prop('disabled', true);
                if (dt) dt.clear().draw();
            });
        });



        // Botón PDF
        $btnPdf.on('click', function() {
            if (!ultimoRango) return;

            const params = {
                accion: 'rrhh_reporte_pdf',
                desde: ultimoRango.desde,
                hasta: ultimoRango.hasta
            };

            if (ultimoRango.tipo) {
                params.tipo = ultimoRango.tipo;
            }
            if (ultimoRango.estado) {
                params.estado = ultimoRango.estado;
            }

            const q = new URLSearchParams(params);
            window.open('../../licencias/controlador/controladorLicencias.php?' + q.toString(), '_blank');
        });

        cargarTipos();
         cargarEstados();

    })(jQuery);
</script>


<?php require_once("foot/foot.php"); ?>