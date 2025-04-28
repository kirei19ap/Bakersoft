<?php
require_once("./login/controlador/usrControlador.php");

$userSesion = new usrControlador(); #Inicia la sesion


if(isset($SESSION['user'])){
    echo "hay sesion";
    #$userSesion->setCurrentUser($SESSION['user']);
    #include_once("menuprincipal/MenuPrincipal.php");

}elseif(isset($_POST['usuario']) && isset($_POST['contrasena'])){
    #echo "validacion de login";
    $userLogin = $_POST['usuario'];
    $passLogin = $_POST['contrasena'];

    if ($userSesion->validarUser($userLogin, $passLogin)){
        #echo "usuario validado";
        $userSesion->setCurrentUser($userLogin); /*Almacena el nombre de usuario en la sesion*/
        #echo $_SESSION['user']; /*Devuelve el nombre de usuario almacenado en la sesion*/
       #echo $_SESSION['nomyapellido']; /*Devuelve el nombre y apellido almacenado en la sesion*/
        include_once("menuprincipal/MenuPrincipal.php"); /*Carga el menu principal*/
    }else{
        #echo "Nombre de usario y/o contraseña incorrecto.";
        $errorLogin = "Nombre de usuario y/o contraseña incorrecto.";
        include_once("login/vista/login.php");
    }
}else{

    include_once("login/vista/login.php");
}


?>
