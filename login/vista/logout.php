<?php

    include_once("../controlador/usrControlador.php"); // Vinculamos el controlador
    $useSession = new usrControlador(); // Creamos una instancia del controlador
    $useSession->cerrarSesion(); // Llamamos al método para cerrar sesión

    header("Location: /bakersoft/index.php");
?>