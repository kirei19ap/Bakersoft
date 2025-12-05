<?php
include_once("../../includes/head_app.php");
require_once(__DIR__ . "/../controlador/controladoradmempleado.php");
$obj = new ControladorAdmEmpleado();
// admempleados/vista/buscador.php
// Opcional: $roles puede venir del controlador si querés renderizar el combo de Rol
// $roles = $roles ?? [];
$puestos    = $obj->traerPuesto();
?>
<style>
    .chart-card {
        min-height: 340px;
        /* más petisos que antes */
        height: 340px;
        display: flex;
        flex-direction: column;
    }

    .chart-card .card-body {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Barras ocupan todo el alto */
    canvas.chart-bar {
        width: 100% !important;
        height: 100% !important;
    }

    /* Tortas, tamaño controlado */
    canvas.chart-pie {
        max-width: 300px !important;
        max-height: 300px !important;
        width: 100% !important;
        height: auto !important;
    }

    /* KPIs como en Materia Prima */
    .card h3 {
        font-size: 1.8rem;
        margin: 0;
    }

    .card h6 {
        font-size: 0.9rem;
        letter-spacing: .3px;
    }

    .contenido-principal {
        background: #fff;
        border-radius: .5rem;
        padding: 1rem 1.25rem;
        margin-top: 1rem;
    }

    .contenido {
        padding-top: .5rem;
    }

    /* Tarjetas KPI (las que tienen h6 + h3) */
  .chart-card {
    border: 1px solid rgba(0,0,0,.06);
    border-radius: 14px;
    background: var(--bs-body-bg);
  }
  .chart-card.p-3 { padding: .9rem !important; }          /* un poco más compacto */
  .chart-card .card-body { padding: .75rem .5rem !important; }

  /* Tipografías: título + valor KPI */
  .chart-card h6 {
    margin: 0 0 .25rem 0;
    font-size: .92rem;                 /* más nítido que el default */
    font-weight: 600;
    color: var(--bs-secondary-color);
  }
  .chart-card h3 {
    margin: 0;
    font-size: 1.9rem;                 /* valor visible pero sin romper el layout */
    font-weight: 800;
    letter-spacing: .2px;
    line-height: 1.1;
  }

  /* Asegurar alto homogéneo de KPIs (no “bailan”) */
  .row .chart-card.text-center {
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-height: 118px;                 /* igualá si querés más compacto */
  }

  /* Tarjetas de gráficos: coherencia visual */
  .chart-card h5 {
    margin: .25rem 0 .25rem 0;
    font-size: 1.05rem;
    font-weight: 700;
    color: var(--bs-body-color);
    text-align: center;
  }

  /* Lienzos de Chart.js: alto fijo y sin scrolls raros */
  .chart-card canvas {
    width: 100% !important;
    height: 280px !important;          /* 260–320px funciona bien; ajustá a gusto */
    display: block;
  }

  /* Grillas y separaciones generales */
  .row.g-3 > [class*="col-"] .chart-card { height: 100%; }
  .mb-4 .chart-card { margin-bottom: 0 !important; }

  /* Sombras suaves y hover discreto */
  .chart-card {
    box-shadow: 0 2px 10px rgba(0,0,0,.03);
    transition: box-shadow .18s ease, transform .18s ease;
  }
  .chart-card:hover {
    box-shadow: 0 6px 18px rgba(0,0,0,.06);
    transform: translateY(-1px);
  }

  /* Responsivo: en móviles valores un poco más chicos */
  @media (max-width: 576px) {
    .chart-card h3 { font-size: 1.6rem; }
    .chart-card canvas { height: 240px !important; }
    .row .chart-card.text-center { min-height: 110px; }
  }
</style>

<!-- Encabezado -->
<div class="titulo-contenido shadow-sm">
    <h1 class="display-5">Estadísticas</h1>
</div>

<div class="contenido-principal">
    <div class="col-12">
        <h3 class="mb-4">Estadísticas de empleados</h3>
    </div>

    <!-- KPIs (estilo Materia Prima) -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm p-3 text-center">
                <h6 class="mb-2 text-secondary">Empleados Activos</h6>
                <h3 class="fw-bold text-primary" id="kpiActivos">0</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm p-3 text-center">
                <h6 class="mb-2 text-secondary">Empleados Inactivos</h6>
                <h3 class="fw-bold text-danger" id="kpiInactivos">0</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm p-3 text-center">
                <h6 class="mb-2 text-secondary">Altas Últimos 30 Días</h6>
                <h3 class="fw-bold text-success" id="kpiAltas30">0</h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm p-3 text-center">
                <h6 class="mb-2 text-secondary">Antigüedad Promedio (Años)</h6>
                <h3 class="fw-bold text-warning" id="kpiAntigProm">0.0</h3>
            </div>
        </div>
    </div>

    <!-- Gráficos (más chicos) -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm p-3 chart-card">
                <h5 class="text-center mb-2">Altas por mes (últimos 12 meses)</h5>
                <div class="card-body">
                    <canvas id="chAltasMes" class="chart-bar"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm p-3 chart-card">
                <h5 class="text-center mb-2">Distribución por puesto (%)</h5>
                <div class="card-body">
                    <canvas id="chPorPuesto" class="chart-pie"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm p-3 chart-card">
                <h5 class="text-center mb-2">Activos vs. Inactivos</h5>
                <div class="card-body">
                    <canvas id="chPorEstado" class="chart-bar"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm p-3 chart-card">
                <h5 class="text-center mb-2">Distribución por género (%)</h5>
                <div class="card-body">
                    <canvas id="chPorGenero" class="chart-pie"></canvas>
                </div>
            </div>
        </div>
    </div>
    <!-- ===================== ESTADÍSTICAS DE LICENCIAS ===================== -->
    <div class="col-12 mt-2">
        <h3 class="mb-3">Estadísticas de licencias</h3>
    </div>

    <!-- KPIs Licencias (3 x 2) -->
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card shadow-sm p-3 text-center chart-card">
                <h6 class="mb-2 text-secondary">Pendientes de aprobación</h6>
                <h3 class="fw-bold text-warning" id="kpiLicPendAprob">0</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm p-3 text-center chart-card">
                <h6 class="mb-2 text-secondary">Aprobadas (año)</h6>
                <h3 class="fw-bold text-success" id="kpiLicAprobYTD">0</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm p-3 text-center chart-card">
                <h6 class="mb-2 text-secondary">Días de vacaciones tomados (año)</h6>
                <h3 class="fw-bold text-primary" id="kpiDiasVacYTD">0</h3>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm p-3 text-center chart-card">
                <h6 class="mb-2 text-secondary">Promedio días por licencia aprobada</h6>
                <h3 class="fw-bold text-info" id="kpiPromDiasAprob">0</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm p-3 text-center chart-card">
                <h6 class="mb-2 text-secondary">Vacaciones tomadas (licencias)</h6>
                <h3 class="fw-bold text-secondary" id="kpiCantVacLic">0</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm p-3 text-center chart-card">
                <h6 class="mb-2 text-secondary">Otras licencias aprobadas</h6>
                <h3 class="fw-bold text-secondary" id="kpiCantOtrasLic">0</h3>
            </div>
        </div>
    </div>

    <!-- Charts licencias -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm p-3 chart-card">
                <h5 class="text-center mb-2">Licencias por estado (año)</h5>
                <div class="card-body">
                    <canvas id="chLicPorEstado" class="chart-donut"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm p-3 chart-card">
                <h5 class="text-center mb-2">Top 5 tipos de licencia (aprobadas)</h5>
                <div class="card-body">
                    <canvas id="chLicPorTipo" class="chart-bar"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 mb-4">
            <div class="card shadow-sm p-3 chart-card">
                <h5 class="text-center mb-2">Aprobadas por mes (año)</h5>
                <div class="card-body">
                    <canvas id="chLicAprobMensual" class="chart-line"></canvas>
                </div>
            </div>
        </div>
    </div>
    <!-- =================== FIN ESTADÍSTICAS DE LICENCIAS ==================== -->

</div>


<script>
    // Detecta raíz del módulo (/bakersoft/admempleados)
    const moduleRoot = window.location.pathname.replace(/\/vista\/.*$/, '');
</script>
<script src="../../rsc/script//estadisticasEmpleados.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const API_PDF = `${moduleRoot}/controlador/nomina_empleados_pdf.php`;
        const a = document.getElementById('btnExportPdfStats');
        if (a) a.addEventListener('click', (e) => {
            e.preventDefault();
            window.open(API_PDF, '_blank'); // sin filtros => todo
        });
    });

  (function(){
    const API_LIC = `../../licencias/controlador/controladorLicencias.php`;

    const $ = (sel) => document.querySelector(sel);
    function setText(id, val){ const el = document.getElementById(id); if (el) el.textContent = val; }

    async function fetchStatsLicencias(){
      const fd = new FormData();
      fd.append('accion', 'stats_rrhh');
      const r = await fetch(API_LIC, { method:'POST', body: fd });
      const txt = await r.text();
      try { return JSON.parse(txt); } catch(e){ console.error('No JSON:', txt); throw e; }
    }

    // Helpers Chart.js
    function makeDonut(ctx, labels, data){
      return new Chart(ctx, {
        type:'doughnut',
        data:{ labels, datasets:[{ data }] },
        options:{ responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom' } } }
      });
    }
    function makeBar(ctx, labels, data){
      return new Chart(ctx, {
        type:'bar',
        data:{ labels, datasets:[{ data }] },
        options:{
          responsive:true, maintainAspectRatio:false,
          scales:{ y:{ beginAtZero:true, ticks:{ precision:0 } } },
          plugins:{ legend:{ display:false } }
        }
      });
    }
    function makeLine(ctx, labels, data){
      return new Chart(ctx, {
        type:'line',
        data:{ labels, datasets:[{ data, fill:false, tension:0.2 }] },
        options:{
          responsive:true, maintainAspectRatio:false,
          scales:{ y:{ beginAtZero:true, ticks:{ precision:0 } } },
          plugins:{ legend:{ display:false } }
        }
      });
    }

    document.addEventListener('DOMContentLoaded', async function(){
      try{
        const resp = await fetchStatsLicencias();
        if(!resp?.ok){ console.warn(resp?.msg || 'Sin stats'); return; }

        // KPIs
        const k = resp.kpis || {};
        setText('kpiLicPendAprob', k.pendientes_aprob ?? 0);
        setText('kpiLicAprobYTD',   k.aprobadas_ytd ?? 0);
        setText('kpiDiasVacYTD',    k.dias_vac_ytd ?? 0);
        setText('kpiPromDiasAprob', k.prom_dias_aprob ?? 0);
        setText('kpiCantVacLic',    k.cant_vac_lic ?? 0);
        setText('kpiCantOtrasLic',  k.cant_otras_lic ?? 0);

        // Donut por estado
        const est = resp.por_estado || [];
        const estLabels = est.map(x=>x.nombre);
        const estData   = est.map(x=>parseInt(x.total||0));
        makeDonut($('#chLicPorEstado').getContext('2d'), estLabels, estData);

        // Barras por tipo (Top 5)
        const tip = resp.por_tipo || [];
        const tipLabels = tip.map(x=>x.nombre);
        const tipData   = tip.map(x=>parseInt(x.total||0));
        makeBar($('#chLicPorTipo').getContext('2d'), tipLabels, tipData);

        // Línea mensual (enero..diciembre)
        const serie = resp.serie_mensual || [];
        const meses = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        makeLine($('#chLicAprobMensual').getContext('2d'), meses, serie);
      }catch(e){
        console.error(e);
      }
    });
  })();


</script>

<?php require_once("foot/foot.php"); ?>