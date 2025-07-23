<?php
require("D:/wamp/www/bakersoft/login/modelo/usrModelo.php"); // Vinculamos el modelo

class usrControlador{
    
    public function __construct(){
        session_start(); // Iniciar la sesión
    }

    public function validarUser($user, $contrasena){
        $modelo = new usrModelo(); // Crear una instancia del modelo
        if($modelo->existeUsuario($user, $contrasena)){
            #$modelo->setUser($user); // Establecer el usuario en el modelo
            return true; // El usuario es válido
        }else{
            return false; // El usuario no es válido
        }
    }

    public function setCurrentUser($user){
        $modelo = new usrModelo(); // Crear una instancia del modelo
        #$_SESSION['user'] = $modelo->setUser($user); // Almacena el nombre de usuario en la sesión
        $reguser = $modelo->setUser($user);
        return $reguser;
        #$_SESSION['user'] = $reguser[0]['usuario'];
        #$_SESSION['nomyapellido'] = $reguser[0]['nomyapellido'];
        #$_SESSION['rol'] = $reguser['nombre_rol'];
        #$_SESSION['id_rol'] = $reguser['rol'];
        #$_SESSION['nomyapellido'] = $modelo->getNombre(); // Almacena el nombre y apellido en la sesión
    }

    public function cerrarSesion(){
        session_unset();
        session_destroy(); // Destruir la sesión actual
    }   
}

?>