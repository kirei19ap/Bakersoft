<?php

class controladorMP{
    private $modelo;
    public function __construct(){
        require_once("../modelo/modeloMP.php");
        $this->modelo = new modeloMP();

    }
    
    public function guardar($nombre, $unidad_medida, $stockminimo, $stockactual, $proveedor, $idCatMP, $es_perecedero, $fecha_vencimiento){
        $registro = $this->modelo->consultarMP($nombre);
    
        if($registro != false){
            if ($registro['estado'] === "activo") {
                // Ya existe y está activa
                $_SESSION['error_valida_existe'] = "Ya existe una materia prima con ese nombre, intente nuevamente o edite el registro correspondiente.";
                return header("Location:index.php");
            } else {
                // Existe pero está inactiva: la reactivamos y actualizamos
                $this->modelo->reactivarMP($registro['id'], $unidad_medida, $stockminimo, $stockactual, $proveedor, $es_perecedero, $fecha_vencimiento);
                return header("Location:index.php");
            }
        } else {
            // No existe: insertar nuevo
            $id = $this->modelo->insertar($nombre, $unidad_medida, $stockminimo, $stockactual, $proveedor, $idCatMP, $es_perecedero, $fecha_vencimiento);
            return ($id != false) ? header("Location:index.php") : header("Location:error.php");
        }
    }




    public function actualizar($id, $nombre, $stockminimo, $stockactual, $id_proveedor, $no_perecedero, $fecha_vencimiento){
        return ($this->modelo->update($id, $nombre, $stockminimo, $stockactual, $id_proveedor, $no_perecedero, $fecha_vencimiento) != false) ? header("Location:index.php") : header("Location:error.php");
    }

    public function borrar($id){
        return ($this->modelo->delete($id)) ? header("Location:index.php") : header("Location:error.php") ;
    }

    public function mostrarTodos(){
        return ($this->modelo->listarTodos() ? $this->modelo->listarTodos() : false);
    }

    public function consultaMP ($nombre){
        $consulta = $this->modelo->consultarMP($nombre);
        return $consulta;
    }

    public function consultaProveedor($id_proveedor){
        $consulta = $this->modelo->traeProveedor($id_proveedor);
        return $consulta;
    }

    public function proveedoresTodos(){
        return ($this->modelo->traerProveedores() ? $this->modelo->traerProveedores() : false);
    }

    public function traerCategorias(){
        return ($this->modelo->traerCategorias() ? $this->modelo->traerCategorias() : false);

    }
}

?>