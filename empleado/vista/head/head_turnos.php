<?php
session_start();
// Verificar si el usuario está logueado
if (!isset($_SESSION['user'])) {
     //Si no está logueado, redirigir al login
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
    <link rel="stylecheet" href="https://cdn.datatables.net/2.3.0/css/dataTables.bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
  /* Estilos locales al módulo Empleado (no tocan el resto) */
  /*.empleado-modulo {*/
    /* Este contenedor aplica sólo aquí */
  /*}*/
  .empleado-modulo .card {
    width: 100%;
    max-width: 900px;       /* Ajustá a 840–960px si querés afinar */
    margin-left: auto;
    margin-right: auto;     /* centra las tarjetas */
    padding-bottom: 20px;
  }
  .empleado-modulo .table {
    width: 100%;
  }
  /* Afinamos la columna de etiquetas para que no crezca de más */
  .empleado-modulo .tabla-empleados th {
    width: 240px;           /* podés mover a 220/260 según lo veas */
    white-space: nowrap;
  }
  /* En pantallas grandes, reducimos un pelín el max-width para evitar “franja vacía” a la derecha */
  @media (min-width: 1400px) {
    .empleado-modulo .card {
      max-width: 860px;
    }
  }
  /* En móviles que respire mejor */
  @media (max-width: 576px) {
    .empleado-modulo .tabla-empleados th {
      width: 42%;
    }
  }
   .empleado-modulo .datos-grid {
    display: grid;
    grid-template-columns: 1fr;           /* móvil: una columna */
    gap: 10px 16px;
    margin-top: 15px;            /* espacio entre encabezado y los primeros datos */
  margin-bottom: 10px; 
  }
  @media (min-width: 768px) {
    .empleado-modulo .datos-grid {
      grid-template-columns: 1fr 1fr;     /* desktop: dos columnas */
      gap: 12px 18px;
    }
  }
  .empleado-modulo .dato {
    display: grid;
    grid-template-columns: 220px 1fr;     /* etiqueta + valor */
    align-items: center;
    padding: 8px 12px;
    border: 1px solid #eee;
    border-radius: 8px;
    background: #fff;
     background: #fff;
  border: 1px solid #eee;
  border-radius: 8px;
  padding: 10px 12px;
  }
  .empleado-modulo .dato .lbl {
    font-weight: 600;
    color: #555;
    white-space: nowrap;
     font-weight: 600;
  color: #444;
  }
  .empleado-modulo .dato .val {
    color: #222;
    word-break: break-word;
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
                    <a id="menuLicencias" class="" href="#">
                        <ion-icon name="people-outline"></ion-icon>
                        <span>Inicio</span>
                    </a>
                </li>
                <li>
                    <a id="menuLicencias" class="" href="../../licencias/vista/index.php">
                        <ion-icon name="people-outline"></ion-icon>
                        <span>Licencias</span>
                    </a>
                </li>
                <li>
                    <a id="menuTurnos" class="active" href="../../empleado/vista/mis_turnos.php">
                        <ion-icon name="people-outline"></ion-icon>
                        <span>Turnos Laborales</span>
                    </a>
                </li>
                <li>
                  <a id="menuLicencias" class="" href="../../licencias/vista/reporteMisLicencias.php">
                        <ion-icon name="people-outline"></ion-icon>
                        <span>Reporte Mis Licencias</span>
                    </a>
                </li>
                <li>
                  <a id="menuLicencias" class="" href="/bakersoft/inicio.php">
                        <ion-icon name="people-outline"></ion-icon>
                        <span>Volver al menu principal</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
    
