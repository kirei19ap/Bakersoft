<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== "Admin RRHH") { // Encargado RRHH
    header('Location: ../../index.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bakersoft</title>
    <link rel="icon" href="../../favicon.jpg" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
    <link rel="stylesheet" href="../../rsc/estilos/style.css">
    <link rel="stylesheet" href="../../rsc/estilos/contenido.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.0/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .chart-card {
            min-height: 420px;
            /* alto uniforme */
            height: 420px;
            display: flex;
            flex-direction: column;
        }

        .chart-card .card-body {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Barras: que ocupen todo el alto disponible */
        canvas.chart-bar {
            width: 100% !important;
            height: 100% !important;
        }

        /* Tortas: limitar tamaño máximo para que no queden gigantes */
        canvas.chart-pie {
            max-width: 340px !important;
            /* ajustá 320–380 según te guste */
            max-height: 340px !important;
            width: 100% !important;
            height: auto !important;
            /* mantiene proporción */
        }

        .card h3 {
            font-size: 1.8rem;
            margin: 0;
        }

        .card h6 {
            font-size: 0.9rem;
            letter-spacing: 0.3px;
        }
    </style>

</head>

<body>

    <div class="barra-lateral">

        <div class="nombre-pagina">
            <img src="../../rsc/img/Logo.jfif" alt="LOGO">
            <span>Bakersoft</span>
        </div>

        <br>
        <div class="linea"></div>
        <br>
        <div class="usuario">
            <img src="../../rsc/img/user-icon.png" alt="">
            <div class="datos-usuario">
                <div class="nombre-email">
                    <span class="nombre"><?php echo $_SESSION['nomyapellido'];  ?></span>
                    <span class="rol"><?php echo $_SESSION['rol'];  ?></span>
                </div>
            </div>

        </div>
        <div class="cerrar_sesion">
            <ion-icon name="lock-closed-outline">
                <span></ion-icon><a href="../../login/vista/logout.php">Cerrar Sesión</a></span>
        </div>

        <nav class="navegacion">
            <ul>
                <li>
                    <a class="" href="index.php">
                        <ion-icon name="people-outline"></ion-icon>
                        <span>Empleados</span>
                    </a>
                </li>
                <li>
                    <a class="" href="licenciasAdmin.php">
                        <ion-icon name="people-outline"></ion-icon>
                        <span>Licencias</span>
                    </a>
                </li>
                <li>
                    <a class="" href="buscador.php">
                        <ion-icon name="search"></ion-icon>
                        <span>Buscador</span>
                    </a>
                </li>
                <li>
                    <a class="" href="estadisticas.php">
                        <ion-icon name="stats-chart-outline"></ion-icon>
                        <span>Estadísticas</span>
                    </a>
                </li>
                <li>
                    <a class="active" href="reportes.php">
                        <ion-icon name="stats-chart-outline"></ion-icon>
                        <span>Reportes</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>