<?php
require_once("login/modelo/modelologin.php");
require_once("login/modelo/usr_sesion.php");

$userSesion = new UserSesion();

$user = new User();

if(isset($SESSION['user'])){
    #echo "hay sesion";
    $user -> setUser($userSesion->getCurrentUser());
    include_once("menuprincipal/MenuPrincipal.php");

}elseif(isset($_POST['usuario']) && isset($_POST['contrasena'])){
    #echo "validacion de login";
    $userLogin = $_POST['usuario'];
    $passLogin = $_POST['contrasena'];

    if ($user->existeUser($userLogin, $passLogin)){
        #echo "usuario validado";
        $userSesion->setCurrentUser($userLogin); /*Almacena el nombre de usuario en la sesion*/
        $user->setUser($userLogin); #Almacena en nombre y apellido del usuario en la sesion
        include_once("menuprincipal/MenuPrincipal.php");
    }else{
        #echo "Nombre de usario y/o contraseña incorrecto.";
        $errorLogin = "Nombre de usuario y/o contraseña incorrecto.";
        include_once("login/login.php");
    }
}else{
    #echo "ir a login para iniciar sesion";
    include_once("login/login.php");
}


?>
