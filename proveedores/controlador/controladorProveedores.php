<?php
class controladorProveedor{
    private $modelo;
    public function __construct(){
        require_once("../modelo/modeloProveedores.php");
        $this->modelo = new modeloProveedor();

    }
    
    public function guardar($nombre, $calle, $altura, $provincia, $localidad, $email, $telefono){
        #validar que no exite una materia prima con el mismo nombre
        $consulta = $this->modelo->consultarProveedor($nombre);
        if($consulta != false){
            $_SESSION['error_valida_proveedor'] ="Ya existe un proveedor activo registrado con ese nombre, intente nuevamente con otro nombre o edite el registro correspondiente.";
            header("Location:index.php"); 
            exit;
        }else{
            $id = $this->modelo->insertar($nombre, $calle, $altura, $provincia, $localidad, $email, $telefono);
            return ($id != false) ? header("Location:index.php") : header("Location:error.php");
        }

        
    }

    public function actualizar($id, $nombre, $calle, $altura, $provincia, $localidad, $email, $telefono){
        return ($this->modelo->update($id, $nombre, $calle, $altura, $provincia, $localidad, $email, $telefono) != false) ? header("Location:index.php") : header("Location:error.php");
    }

    public function borrar($id){

        $resultado = $this->modelo->delete($id);
        #var_dump($resultado);
        if($resultado == "1"){
            header("Location:index.php");
            exit;
        }else if($resultado == "ERROR_RELACION"){
            $_SESSION['error_borra_proveedor'] = "No es posible eliminar el proveedor ya que tiene Materias Primas asociadas, efectúe los cambios necesarios y vuelva a intentarlo.";
            header("Location:index.php"); 
            exit; 
        }else{
            $_SESSION['error_borra_proveedor'] = "No es posible eliminar el proveedor. Error desconocido.";
            header("Location:index.php"); 
            exit;
        }
    }

    public function mostrarTodos(){
        return ($this->modelo->listarTodos() ? $this->modelo->listarTodos() : false);
    }

    public function mostrarProvincias(){
        return ($this->modelo->listarProvincias() ? $this->modelo->listarProvincias() : false);
    }
    
}

?>