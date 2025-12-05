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
    <h1 class="display-5">Reportes RRHH</h1>
</div>

<div class="contenido-principal">
    <div class="col-12">
        <h3 class="mb-4">Reportes de licencias y empleados</h3>
    </div>


    <!-- Reportes disponibles -->
    <div class="row my-4">
        <div class="col-12">
            <h3 class="mb-4">📊 Reportes disponibles</h3>
        </div>

        <div class="col-md-6 mb-3 d-flex justify-content-center">
            <a id="btnExportPdfStats" class="btn btn-outline-danger btn-lg px-5" href="#" target="_blank">
                📄 Nómina de Empleados (PDF)
            </a>
        </div>

        <div class="col-md-6 mb-3 d-flex justify-content-center">
            <a href="reporteLicencias.php" class="btn btn-outline-warning btn-lg px-5">
                📄Listado integral de licencias
            </a>
        </div>


        <!-- Espacio para futuros reportes (ej. Excel, distribución por puesto) -->
        <div class="col-md-6 mb-3 d-flex justify-content-center">
            <!-- Placeholder de otro reporte -->
        </div>
    </div>


</div>


<script>
    // Detecta raíz del módulo (/bakersoft/admempleados)
    const moduleRoot = window.location.pathname.replace(/\/vista\/.*$/, '');
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const API_PDF = `${moduleRoot}/controlador/nomina_empleados_pdf.php`;
        const a = document.getElementById('btnExportPdfStats');
        if (a) a.addEventListener('click', (e) => {
            e.preventDefault();
            window.open(API_PDF, '_blank'); // sin filtros => todo
        });
    });

</script>

<?php require_once("foot/foot.php"); ?>