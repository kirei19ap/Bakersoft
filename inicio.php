<?php
#session_start();

// Si no está logueado, volver a login
if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

// No marcamos ningún ítem como activo
$currentPage = '';

// Incluir head dinámico
include 'includes/head_app.php';
?>

<!-- ====================== CONTENIDO DE LA LANDING ====================== -->

<div class="d-flex flex-column justify-content-center align-items-center"
    style="height: 100%; padding: 40px; text-align:center;">

    <h1 class="mb-4" style="font-weight: 300; color:#333;">
        Bienvenido a <strong>BakerSoft</strong>
    </h1>
    <div style="
    width: 260px;
    height: 260px;
    border-radius: 50%;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
">
        <img src="rsc/img/Welcome.png"
            alt="Bienvenido a BakerSoft"
            style="
            width: 100%;
            height: 100%;
            object-fit: cover;
            opacity: 0.95;
         ">
    </div>

</div>

<!-- ========================== FIN CONTENIDO ============================= -->

</div> <!-- cierre .contenido -->
</div> <!-- cierre .contenedor-principal -->

<?php
include 'includes/foot_app.php';
?>
