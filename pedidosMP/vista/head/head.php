<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user'])) {
    // Si no está logueado, redirigir al login
    header('Location: ../../index.php');
    exit();
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

    <script>const proveedorEnSesion = <?php echo isset($_SESSION['idprove']) ? intval($_SESSION['idprove']) : 'null'; ?>;</script>
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
                    <a  href="../../materiaprima/vista/index.php">
                        <ion-icon name="people-outline"></ion-icon>
                        <span>Materia Prima</span>
                    </a>
                </li>
                <li>
                    <a href="../../proveedores/vista/index.php">
                        <ion-icon name="git-pull-request-outline"></ion-icon>
                        <span>Proveedores</span>
                    </a>
                </li>
                <li>
                    <a class="active" href="index.php">
                        <ion-icon name="stats-chart-outline"></ion-icon>
                        <span>Pedidos de MP</span>
                    </a>
                </li>
                <li>
                    <a class="" href="../../buscador/vista/index.php">
                        <ion-icon name="stats-chart-outline"></ion-icon>
                        <span>Buscador</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>