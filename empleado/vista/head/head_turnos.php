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
     <script src="<?php echo $base; ?>/rsc/script/mignon.js"></script>
    <script> const BAKERSOFT_BASE = "<?php echo $base; ?>";</script>
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

 <!-- ========================================= -->
    <!-- MIGNON: BOTÓN FLOTANTE Y PANEL DE CHAT   -->
    <!-- ========================================= -->
<style>
/* ============================= */
/* MIGNON - BOTÓN Y PANEL        */
/* ============================= */

/* BOTÓN FLOTANTE */
.mignon-launcher {
  position: fixed;
  bottom: 24px;
  right: 24px;
  width: 74px;              /* MÁS GRANDE */
  height: 74px;             /* MÁS GRANDE */
  cursor: pointer;
  z-index: 9999;
  border-radius: 50%;
  background-color: #FBF4E5; /* Crema BakerSoft */
  box-shadow: 0 4px 14px rgba(0,0,0,0.28);
  display: flex;
  align-items: center;
  justify-content: center;
  transition: transform 0.18s ease, box-shadow 0.18s ease;
  border: 2px solid #697565; /* Verde BakerSoft */
}

/* Hover sutil */
.mignon-launcher:hover {
  transform: scale(1.06);
  box-shadow: 0 6px 18px rgba(0,0,0,0.32);
}

.mignon-launcher svg {
  width: 86%;
  height: 86%;
  display: block;
}


/* PANEL DE CHAT */
.mignon-panel {
  position: fixed;
  bottom: 110px;
  right: 24px;
  width: 320px;
  max-height: 480px;
  background: #F2F2F2; /* Fondo suave */
  border-radius: 16px;
  box-shadow: 0 10px 28px rgba(0,0,0,0.25);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  z-index: 10000;
  transform: translateY(20px);
  opacity: 0;
  pointer-events: none;
  transition: all 0.25s ease-out;
  border: 1px solid #DDDDDD;
}

.mignon-panel.mignon-open {
  transform: translateY(0);
  opacity: 1;
  pointer-events: auto;
}


/* HEADER DEL PANEL */
.mignon-header {
  display: flex;
  align-items: center;
  padding: 10px 12px;
  background: #697565;              /* Verde BakerSoft */
  border-bottom: 1px solid #545F55; /* Un tono más oscuro */
  color: #FFFFFF;
}

.mignon-header-icon svg {
  width: 34px;
  height: 34px;
  filter: drop-shadow(0 1px 1px rgba(0,0,0,0.15));
}

.mignon-header-text {
  flex: 1;
  margin-left: 8px;
}

.mignon-name {
  font-weight: 600;
  font-size: 0.95rem;
  color: #FFFFFF;
}

.mignon-subtitle {
  font-size: 0.78rem;
  color: #E8EDE8;
}

.mignon-close {
  border: none;
  background: transparent;
  font-size: 1.3rem;
  line-height: 1;
  cursor: pointer;
  color: #FFFFFF;
}


/* MENSAJES */
.mignon-messages {
  flex: 1;
  padding: 10px;
  overflow-y: auto;
  background: #F7F7F7;
}

.mignon-message {
  display: flex;
  margin-bottom: 8px;
}

.mignon-from-bot {
  justify-content: flex-start;
}

.mignon-from-user {
  justify-content: flex-end;
}

.mignon-bubble {
  max-width: 80%;
  padding: 8px 10px;
  border-radius: 12px;
  font-size: 0.85rem;
  line-height: 1.4;
  color: #333333;
}

/* Burbuja de Mignon con acento verde */
.mignon-from-bot .mignon-bubble {
  background: #E3E5E1;          /* gris verdoso suave */
  border-left: 4px solid #697565;
  border-bottom-left-radius: 4px;
}

/* Burbuja del usuario */
.mignon-from-user .mignon-bubble {
  background: #FFFFFF;
  border: 1px solid #DDDDDD;
  border-bottom-right-radius: 4px;
}


/* INPUT Y BOTÓN */
.mignon-input-area {
  display: flex;
  border-top: 1px solid #DDDDDD;
  padding: 6px;
  background: #FFFFFF;
}

.mignon-input {
  flex: 1;
  border: 1px solid #CCCCCC;
  border-radius: 999px;
  padding: 6px 10px;
  font-size: 0.85rem;
  outline: none;
  color: #333333;
  background-color: #FAFAFA;
}

.mignon-input:focus {
  border-color: #697565;
  background-color: #FFFFFF;
}

/* Botón enviar */
.mignon-send-btn {
  margin-left: 6px;
  padding: 6px 12px;
  border: none;
  border-radius: 999px;
  font-size: 0.85rem;
  background: #697565;   /* Verde BakerSoft */
  color: #FFFFFF;
  cursor: pointer;
  transition: background 0.2s ease;
}

.mignon-send-btn:hover {
  background: #545F55;
}


/* SCROLLBAR */
.mignon-messages::-webkit-scrollbar {
  width: 6px;
}
.mignon-messages::-webkit-scrollbar-thumb {
  background: #D0C0A8;
  border-radius: 3px;
}

</style>
<!-- Botón flotante -->
    <div class="mignon-launcher" id="mignonLauncher" title="Mignon, tu amigo panadero">
        <!-- SVG de Mignon -->
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 72 72">
            <circle cx="36" cy="36" r="34" fill="#FBE9E0" />
            <circle cx="36" cy="36" r="29" fill="#F5D1A1" />
            <ellipse cx="36" cy="50" rx="16" ry="5" fill="#E0B27A" opacity="0.45" />
            <path
                d="M28 18
         C26 21, 25 26, 25 34
         C25 42, 26 47, 28 50
         C31 53, 41 53, 44 50
         C46 47, 47 42, 47 34
         C47 26, 46 21, 44 18
         C41 15, 31 15, 28 18Z"
                fill="#F2B36A"
                stroke="#C47C3A"
                stroke-width="2"
                stroke-linejoin="round" />
            <path
                d="M30 20
         C28.5 24, 28 28, 28 34
         C28 40, 28.5 44, 30 48"
                fill="none"
                stroke="#FFDDB2"
                stroke-width="1.6"
                stroke-linecap="round"
                opacity="0.85" />
            <path
                d="M30 23
         C33 22, 37 22, 40 23"
                fill="none"
                stroke="#F9E3C5"
                stroke-width="2"
                stroke-linecap="round" />
            <path
                d="M31 26
         C34 25.5, 36.5 25.5, 39 26"
                fill="none"
                stroke="#F9E3C5"
                stroke-width="2"
                stroke-linecap="round"
                opacity="0.95" />
            <ellipse cx="32" cy="31" rx="3" ry="4" fill="#FFFFFF" />
            <ellipse cx="32.3" cy="31.6" rx="1.4" ry="2" fill="#4E342E" />
            <circle cx="31.6" cy="30.7" r="0.7" fill="#FFFFFF" opacity="0.9" />
            <ellipse cx="40" cy="31" rx="3" ry="4" fill="#FFFFFF" />
            <ellipse cx="40.3" cy="31.6" rx="1.4" ry="2" fill="#4E342E" />
            <circle cx="39.6" cy="30.7" r="0.7" fill="#FFFFFF" opacity="0.9" />
            <circle cx="29" cy="36" r="1.7" fill="#F9C6A5" opacity="0.85" />
            <circle cx="43" cy="36" r="1.7" fill="#F9C6A5" opacity="0.85" />
            <path
                d="M31.5 41
         C34 43.5, 38 43.5, 40.5 41"
                fill="none"
                stroke="#4E342E"
                stroke-width="1.7"
                stroke-linecap="round" />
        </svg>
    </div>

    <!-- Panel de chat de Mignon -->
    <div class="mignon-panel" id="mignonPanel">
        <div class="mignon-header">
            <div class="mignon-header-icon">
                <!-- Podés reutilizar un Mignon mini o un icono simple -->
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 72 72">
                    <circle cx="36" cy="36" r="29" fill="#F5D1A1" />
                    <path
                        d="M28 18
             C26 21, 25 26, 25 34
             C25 42, 26 47, 28 50
             C31 53, 41 53, 44 50
             C46 47, 47 42, 47 34
             C47 26, 46 21, 44 18
             C41 15, 31 15, 28 18Z"
                        fill="#F2B36A"
                        stroke="#C47C3A"
                        stroke-width="2"
                        stroke-linejoin="round" />
                    <ellipse cx="32" cy="31" rx="3" ry="4" fill="#FFFFFF" />
                    <ellipse cx="32.3" cy="31.6" rx="1.4" ry="2" fill="#4E342E" />
                    <ellipse cx="40" cy="31" rx="3" ry="4" fill="#FFFFFF" />
                    <ellipse cx="40.3" cy="31.6" rx="1.4" ry="2" fill="#4E342E" />
                    <path
                        d="M31.5 41
             C34 43.5, 38 43.5, 40.5 41"
                        fill="none"
                        stroke="#4E342E"
                        stroke-width="1.7"
                        stroke-linecap="round" />
                </svg>
            </div>
            <div class="mignon-header-text">
                <div class="mignon-name">Mignon</div>
                <div class="mignon-subtitle">Tu amigo panadero</div>
            </div>
            <button class="mignon-close" id="mignonCloseBtn">&times;</button>
        </div>

        <div class="mignon-messages" id="mignonMessages">
            <!-- Mensaje de bienvenida -->
            <div class="mignon-message mignon-from-bot">
                <div class="mignon-bubble">
                    Hola, soy <strong>Mignon</strong>, tu amigo panadero. ¿En qué puedo ayudarte hoy?
                </div>
            </div>
        </div>

        <div class="mignon-input-area">
            <input
                type="text"
                id="mignonInput"
                class="mignon-input"
                placeholder="Escribí en lenguaje normal, yo me encargo del resto..."
                autocomplete="off" />
            <button id="mignonSendBtn" class="mignon-send-btn">Enviar</button>
        </div>
    </div>
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
    
