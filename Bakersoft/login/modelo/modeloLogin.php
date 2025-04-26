<?php
// vinculamos con la conexion a la BD
require_once("D:/wamp/www/Bakersoft/config/bd.php"); // "Conexión a la base de datos"

class User extends bd{
    private $nombre;
    private $apellido;
    private $usuario;

    public function existeUser ($user, $password){
        $md5pass = md5($password);
        $query = $this->conexion()->prepare("SELECT * from usuarios WHERE usuario = :user AND contrasena = :pass");
        $query -> execute(['user'=>$user, 'pass'=>$md5pass]);

        if($query->rowcount()){
            return true;
        }else{
            return false;
        }
    }

    public function setUser($user){
        $query = $this->conexion()->prepare("SELECT * FROM usuarios WHERE usuario = :user");
        $query->execute(['user' => $user]);
        foreach ($query as $usrActual){
            $this->nombre = $usrActual['usr_nombre'];
            $this->apellido = $usrActual['usr_apellido'];
            $this->usuario = $usrActual['usuario'];
        }
    }

    public function getNombre(){
        return $this->nombre;
    }
}

?>






