<?php
// vinculamos con la conexion a la BD
include_once("D:/wamp/www/bakersoft/config/bd.php"); // "Conexión a la base de datos"

class usrModelo extends bd{
    private $contrasena;
    private $id_usuario;
    private $nomyapellido;

    public function __construct($nombre = "", $contrasena = ""){
        $this->id_usuario = $nombre;
        $this->contrasena = $contrasena;
    }

    public function existeUsuario($user, $contrasena){
        $db = new bd(); // Crear una instancia de la clase bd
        $md5pass = md5($contrasena);
        $sql = $db->conexion()->prepare("SELECT * FROM usuarios WHERE usuario = :id_usuario AND contrasena = :contrasena");
        $sql->bindParam(":id_usuario", $user, PDO::PARAM_STR);
        $sql->bindParam(":contrasena", $md5pass, PDO::PARAM_STR);
        $sql->execute();
        if($sql->rowCount() > 0){
            return true; // El usuario existe
        }else{
            return false; // El usuario no existe
        }
    }

    public function setUser($user){
        $db = new bd(); // Crear una instancia de la clase bd
       $sql = $db->conexion()->prepare("SELECT * FROM usuarios WHERE usuario = :id_usuario");
       $sql->bindParam(":id_usuario", $user, PDO::PARAM_STR);
       $sql->execute();
       foreach($sql as $row){
            $this->id_usuario = $row['usuario'];
           $this->nomyapellido = $row['nomyapellido'];
       }
       return $this->id_usuario; // Devuelve el id del usuario

    }

    public function getNombre(){
        return $this->nomyapellido;
    }


}


?>