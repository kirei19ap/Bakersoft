<?php

class controladorProveedor{
    private $modelo;
    public function __construct(){
        require_once("../modelo/modeloProveedores.php");
        $this->modelo = new modeloProveedor();

    }
    
    public function guardar($nombre, $direccion, $email, $telefono){
        #validar que no exite una materia prima con el mismo nombre
        $consulta = $this->modelo->consultarProveedor($nombre);
        if($consulta != false){
            $_SESSION['error_valida_proveedor'] ="Ya existe un proveedor registrado con ese nombre, intente nuevamente o edite el registro correspondiente.";
            return header("Location:index.php");
        }else{
            $id = $this->modelo->insertar($nombre, $direccion, $email, $telefono);
            return ($id != false) ? header("Location:index.php") : header("Location:error.php");
        }

        
    }

    public function actualizar($id, $nombre, $direccion, $email, $telefono){
        return ($this->modelo->update($id, $nombre, $direccion, $email, $telefono) != false) ? header("Location:index.php") : header("Location:error.php");
    }

    public function borrar($id){
        return ($this->modelo->delete($id)) ? header("Location:index.php") : header("Location:error.php") ;
    }

    public function mostrarTodos(){
        return ($this->modelo->listarTodos() ? $this->modelo->listarTodos() : false);
    }
}

?>