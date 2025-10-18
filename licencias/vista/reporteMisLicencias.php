<?php
include_once("head/headReporte.php");
require_once("../controlador/controladorLicencias.php");
$ctrl = new ControladorLicencias();

?>

<div class="contenido">
    <div class="titulo-contenido shadow-sm">
        <h1 class="display-6">Mis licencias y vacaciones</h1>
    </div>

    <div class="contenido-principal">
        <div class="container-fluid">
            <!-- Parámetros -->
            <div class="card mb-3">
                <div class="card-header bg-light"><strong>Parámetros</strong></div>
                <div class="card-body">
                    <form id="frmParams" class="row g-3">
                        <div class="col-sm-4">
                            <label class="form-label">Desde *</label>
                            <input type="date" class="form-control" id="fdesde" required>
                        </div>
                        <div class="col-sm-4">
                            <label class="form-label">Hasta *</label>
                            <input type="date" class="form-control" id="fhasta" required>
                        </div>
                        <div class="col-sm-4 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">Generar informe</button>
                            <button type="button" id="btnPdf" class="btn btn-outline-danger" disabled>Exportar PDF</button>
                        </div>
                    </form>
                    <small class="text-muted d-block mt-2">
                        * Rango de fechas obligatorio. Se mostrarán sólo tus licencias en el período. Orden por fecha de inicio ascendente.
                    </small>
                </div>
            </div>

            <!-- Resumen simple -->
            <div id="resumen" class="alert alert-info d-none"></div>

            <!-- Resultado -->
            <div class="card">
                <div class="card-header bg-light"><strong>Resultado</strong></div>
                <div class="card-body">
                    <div id="alerta" class="alert alert-warning d-none mb-0"></div>
                    <div class="table-responsive">
                        <table id="tablaMisLicencias" class="table table-striped table-hover table-bordered align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>Tipo</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Desde</th>
                                    <th class="text-center">Hasta</th>
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

<?php include_once("foot/foot.php"); ?>

<script>
    (function($) {
        const $tabla = $('#tablaMisLicencias');
        const $alerta = $('#alerta');
        const $btnPdf = $('#btnPdf');
        const $resumen = $('#resumen');
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
                dom: "<'row align-items-center mb-2'<'col-md-6'l><'col-md-6'f>>" +
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
                    "search": "Buscador:",
                    "searchPlaceholder": "Buscar...",
                    "emptyTable": "No hay registros para mostrar en la tabla",
                },
                autoWidth: false,
                pageLength: 25,
                order: [
                    [2, 'asc']
                ], // 2 = Desde
                columns: [{
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

        function cargar(desde, hasta) {
            const fd = new FormData();
            fd.append('accion', 'emp_reporte_listar');
            fd.append('desde', desde);
            fd.append('hasta', hasta);

            return fetch('../controlador/controladorLicencias.php', {
                method: 'POST',
                body: fd
            }).then(r => r.json());
        }

        function pintarResumen(resp) {
            const r = resp?.resumen;
            if (!r) {
                $('#resumen').addClass('d-none').text('');
                return;
            }
            $('#resumen')
                .removeClass('d-none alert-warning')
                .addClass('alert-info')
                .html(
                    `<strong>Resumen ${r.anio}:</strong> ` +
                    `Dias de licencia disponibles: <strong>${r.dias_anuales}</strong> &nbsp;|&nbsp; ` +
                    `Tomados Año en curso: <strong>${r.tomados_ytd}</strong> &nbsp;|&nbsp; ` +
                    `Remanentes: <strong>${r.remanentes}</strong>`
                );
        }


        // Submit
        $('#frmParams').on('submit', function(e) {
            e.preventDefault();
            const desde = $('#fdesde').val();
            const hasta = $('#fhasta').val();

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

            cargar(desde, hasta).then(resp => {
                const tabla = initDT();

                if (!resp?.ok) {
                    $alerta.removeClass('d-none alert-success').addClass('alert-warning')
                        .text(resp?.msg || 'No autorizado');
                    $btnPdf.prop('disabled', true);
                    tabla.clear().draw();
                    $resumen.addClass('d-none').text('');
                    return;
                }

                const items = resp.items || [];
                if (items.length === 0) {
                    $alerta.removeClass('d-none alert-success').addClass('alert-warning')
                        .text('No se encontraron licencias en el período definido.');
                    $btnPdf.prop('disabled', true);
                    tabla.clear().draw();
                    $resumen.addClass('d-none').text('');
                    return;
                }

                ultimoRango = {
                    desde,
                    hasta
                };
                $btnPdf.prop('disabled', false);
                $alerta.addClass('d-none').text('');
                $resumen.addClass('d-none').text(''); // limpia
                tabla.clear().rows.add(items).draw();
                pintarResumen(resp);
            }).catch(() => {
                $alerta.removeClass('d-none alert-success').addClass('alert-warning')
                    .text('Error al obtener el informe.');
                $btnPdf.prop('disabled', true);
                if (dt) dt.clear().draw();
                $resumen.addClass('d-none').text('');
            });
        });

        // PDF
        $btnPdf.on('click', function() {
            if (!ultimoRango) return;
            const q = new URLSearchParams({
                accion: 'emp_reporte_pdf',
                desde: ultimoRango.desde,
                hasta: ultimoRango.hasta
            });
            window.open('../controlador/controladorLicencias.php?' + q.toString(), '_blank');
        });

    })(jQuery);
</script>