<?php
// includes/head_app.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si no está logueado, volvemos al index (login)
if (!isset($_SESSION['user'])) {
    header('Location: ../index.php');
    exit();
}

// Rol logueado y página actual
$rolActual   = $_SESSION['rol'] ?? '';
$currentPage = $currentPage ?? ''; // la define cada página antes del include
$base = "/bakersoft";

// DEFINICIÓN DEL MENÚ POR ROL
$menuItems = [
    //   [
    //       'id'    => 'portal_empleado',
    //       'label' => 'Portal del Empleado',
    //       'icon'  => 'people-outline',
    //       'href'  => "$base/empleado/vista/index.php",
    //       'roles' => ['Admin Usuarios', 'Admin Produccion', 'Admin RRHH', 'Usuario', 'Encargado de atención cliente']
    //   ],
    [
        'id'    => 'empleados',
        'label' => 'Empleados',
        'icon'  => 'people-outline',
        'href'  => "$base/admempleados/vista/index.php",
        'roles' => ['Admin RRHH']
    ],
    [
        'id'    => 'licencias',
        'label' => 'Licencias',
        'icon'  => 'people-outline',
        'href'  => "$base/admempleados/vista/licenciasAdmin.php",
        'roles' => ['Admin RRHH']
    ],
    [
        'id'    => 'turnos',
        'label' => 'Turnos Laborales',
        'icon'  => 'git-pull-request-outline',
        'href'  => "$base/turnos/vista/index.php",
        'roles' => ['Admin Produccion']
    ],
    [
        'id'    => 'materiaprima',
        'label' => 'Materia Prima',
        'icon'  => 'people-outline',
        'href'  => "$base/materiaprima/vista/index.php",
        'roles' => ['Admin Produccion']
    ],
    [
        'id'    => 'proveedores',
        'label' => 'Proveedores',
        'icon'  => 'git-pull-request-outline',
        'href'  => "$base/proveedores/vista/index.php",
        'roles' => ['Admin Produccion']
    ],
    [
        'id'    => 'productos',
        'label' => 'Productos',
        'icon'  => 'pizza-outline',
        'href'  => "$base/productos/vista/index.php",
        'roles' => ['Admin Produccion']
    ],
    [
        'id'    => 'pedidos_mp',
        'label' => 'Pedidos de MP',
        'icon'  => 'cart',
        'href'  => "$base/pedidosMP/vista/index.php",
        'roles' => ['Admin Produccion']
    ],
    [
        'id'    => 'buscadorMP',
        'label' => 'Buscador',
        'icon'  => 'search',
        'href'  => "$base/buscador/vista/index.php",
        'roles' => ['Admin Produccion']
    ],
    [
        'id'    => 'pedidos',
        'label' => 'Pedidos',
        'icon'  => 'git-pull-request-outline',
        'href'  => "$base/pedidos/vista/index.php",
        'roles' => ['Admin Produccion', 'Encargado de atención cliente']
    ],
    [
        'id'    => 'buscadorEmpleados',
        'label' => 'Buscador',
        'icon'  => 'git-pull-request-outline',
        'href'  => "$base/admempleados/vista/buscador.php",
        'roles' => ['Admin RRHH']
    ],
    [
        'id'    => 'estadisticasEmpleados',
        'label' => 'Estadisticas',
        'icon'  => 'git-pull-request-outline',
        'href'  => "$base/admempleados/vista/estadisticas.php",
        'roles' => ['Admin RRHH']
    ],
    [
        'id'    => 'reportesEmpleados',
        'label' => 'Reportes',
        'icon'  => 'git-pull-request-outline',
        'href'  => "$base/admempleados/vista/reportes.php",
        'roles' => ['Admin RRHH']
    ],
    [
        'id'    => 'adminUsuarios',
        'label' => 'Usuarios',
        'icon'  => 'git-pull-request-outline',
        'href'  => "$base/usuarios/vista/index.php",
        'roles' => ['Admin Usuarios']
    ],
    [
        'id'    => 'adminRoles',
        'label' => 'Roles',
        'icon'  => 'git-pull-request-outline',
        'href'  => "$base/roles/vista/index.php",
        'roles' => ['Admin Usuarios']
    ],
    [
        'id'    => 'reportesEmpleados',
        'label' => 'Reportes',
        'icon'  => 'git-pull-request-outline',
        'href'  => "$base/usuarios/vista/reportesUsuario.php",
        'roles' => ['Admin Usuarios']
    ],
    [
        'id'    => 'reportespedidos',
        'label' => 'Estadísticas y Reportes',
        'icon'  => 'stats-chart-outline',
        'href'  => "$base/reportes/vista/reportes_produccion.php",
        'roles' => ['Admin Produccion']
    ],
];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bakersoft</title>
    <link rel="icon" href="/bakersoft/favicon.jpg" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous" />
    <link rel="stylesheet" href="/bakersoft/rsc/estilos/style.css">
    <link rel="stylesheet" href="/bakersoft/rsc/estilos/contenido.css">
    <link rel="stylecheet" href="https://cdn.datatables.net/2.3.0/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>
    <div class="barra-lateral">

        <div class="nombre-pagina">
            <img src="/bakersoft/rsc/img/Logo.jfif" alt="LOGO">
            <span>Bakersoft</span>
        </div>

        <br>
        <div class="linea"></div>
        <br>
        <div class="usuario">
            <img src="/bakersoft/rsc/img/user-icon.png" alt="">
            <div class="datos-usuario">
                <div class="nombre-email">
                    <span class="nombre"><?php echo $_SESSION['nomyapellido'];  ?></span>
                    <span class="rol"><?php echo $_SESSION['rol'];  ?></span>
                </div>
            </div>

        </div>
        <div class="cerrar_sesion">
            <a href="/bakersoft/login/vista/logout.php" class="d-flex align-items-center">
                <ion-icon name="lock-closed-outline" style="font-size:20px; margin-right:6px;"></ion-icon>
                <span>Cerrar Sesión</span>
            </a>
        </div>
        

        <nav class="navegacion">
            <ul>
                <hr>
                <li>
                    <a class="" href="/bakersoft/empleado/vista/index.php">
                        <ion-icon name="people-outline" role="img" class="md hydrated"></ion-icon>
                        <span>Portal del Empleado</span>
                    </a>
                </li>
                <hr>
                <?php foreach ($menuItems as $item): ?>
                    <?php if (!in_array($rolActual, $item['roles'])) continue; ?>

                    <?php $activeClass = ($currentPage === $item['id']) ? 'active' : ''; ?>
                    <li>
                        <a class="<?php echo $activeClass; ?>" href="<?php echo $item['href']; ?>">
                            <ion-icon name="<?php echo $item['icon']; ?>"></ion-icon>
                            <span><?php echo $item['label']; ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </div>