<?php
require_once("./login/controlador/usrControlador.php");

$userSesion = new usrControlador(); #Inicia la sesion


if (isset($SESSION['user'])) {
    #echo "hay sesion";
    #$userSesion->setCurrentUser($SESSION['user']);
    #include_once("menuprincipal/MenuPrincipal.php");

} elseif (isset($_POST['usuario']) && isset($_POST['contrasena'])) {
    #echo "validacion de login";
    $userLogin = $_POST['usuario'];
    $passLogin = $_POST['contrasena'];

    if ($userSesion->validarUser($userLogin, $passLogin)) {
        #echo "usuario validado";
        $datosUsuario = $userSesion->setCurrentUser($userLogin); /*Almacena el nombre de usuario en la sesion*/
        #var_dump($datosUsuario);
        $_SESSION['user'] = $datosUsuario[0]['usuario'];
        $_SESSION['rol'] = $datosUsuario[0]['nombre_rol'];
        $_SESSION['nomyapellido'] = $datosUsuario[0]['nomyapellido'];
        if ($datosUsuario[0]['nombre_rol'] == "Admin Usuarios") {
            include_once("menuprincipal/MenuPrincipalUSU.php");
        } else if ($datosUsuario[0]['nombre_rol'] == "Admin Produccion") {
            include_once("menuprincipal/MenuPrincipalMP.php");
        } else if ($datosUsuario[0]['nombre_rol'] == "Admin RRHH") {
            include_once("menuprincipal/MenuPrincipalEMP.php");
        } else if ($datosUsuario[0]['nombre_rol'] == "Usuario") {
            header("Location: empleado/vista/index.php");
            exit();
        }
        #echo $_SESSION['usuario'];
        #echo $_SESSION['nomyapellido'];
        #echo $_SESSION['rol'];
        #echo $_SESSION['id_rol'];

        #include_once("menuprincipal/MenuPrincipalMP.php"); /*Carga el menu principal*/
    } else {
        #echo "Nombre de usario y/o contraseña incorrecto.";
        $errorLogin = "Nombre de usuario y/o contraseña incorrecto. O su usuario se encuentra inactivo.";
        include_once("login/vista/login.php");
    }
} else {

    include_once("login/vista/login.php");
}
