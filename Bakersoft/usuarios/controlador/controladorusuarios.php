<?php
class controladorUsuario{
    private $modelo;
    public function __construct(){
        require_once("../modelo/modeloUsuarios.php");
        $this->modelo = new modeloUsuario();

    }

    public function mostrarTodos(){
        return ($this->modelo->listarTodos() ? $this->modelo->listarTodos() : false);
    }

    public function traerRoles(){
        return ($this->modelo->rolesTodos() ? $this->modelo->rolesTodos() : false);
    }
    
    public function altaUsuario ($usuario, $nombre, $apellido, $rol, $contrasena){
        $nomyapellido = $nombre.' '.$apellido;
        echo $nomyapellido;
        $resultado = $this->modelo->crearUsuario($usuario, $nomyapellido, $rol, $contrasena);
        header("Location:index.php");
        exit;
    }

    public function borrarUsuario($id_usuario){
        $resultado = $this->modelo->deleteUSR($id_usuario);
        if($resultado){
            header("Location:index.php");
        }else {
            header("Location:error.php");
        }
        
    }
}