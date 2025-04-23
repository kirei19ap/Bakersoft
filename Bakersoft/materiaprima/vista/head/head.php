<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bakersoft</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
    <link rel="stylesheet" href="../../rsc/estilos/style.css">
    <link rel="stylesheet" href="../../rsc/estilos/empleados.css">

</head>

<body>

    <div class="barra-lateral">

        <div class="usuario">
            <img src="../../rsc/img/user-icon.png" alt="">
            <div class="datos-usuario">
                <div class="nombre-email">
                    <span class="nombre">Diego</span>
                    <span class="email">diego@gmail.com</span>
                </div>
                <ion-icon name="ellipsis-vertical-outline"></ion-icon>
            </div>

        </div>
        <br>
        <div class="linea"></div>
        <br>
        <div class="nombre-pagina">
            <ion-icon id="icono-menu" name="apps-outline"></ion-icon>
            <span>Bakersoft</span>
        </div>
        <nav class="navegacion">
            <ul>
                <li>
                    <a class="active" href="index.php">
                        <ion-icon name="people-outline"></ion-icon>
                        <span>Materia Prima</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <ion-icon name="git-pull-request-outline"></ion-icon>
                        <span>Proveedores</span>
                    </a>
                </li>
                <li>
                    <a href="#">
                        <ion-icon name="stats-chart-outline"></ion-icon>
                        <span>Reportes</span>
                    </a>
                </li>
                <li class="cerrar_sesion">
                    <a href="#">
                        <ion-icon name="exit-outline"></ion-icon>
                        <span>Cerrar Sesión</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>