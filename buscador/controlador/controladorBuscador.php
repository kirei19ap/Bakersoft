<?php

class controladorBuscador{
    private $modelo;
    public function __construct(){
        require_once("../modelo/modeloBuscador.php");
        $this->modelo = new modeloBuscador();

    }

    public function proveedoresTodos(){
        return ($this->modelo->traerProveedores() ? $this->modelo->traerProveedores() : false);
    }
    public function buscar($fecha, $idproveedor, $materiaId, $estado) {
        $pedidos = [];

        if ($fecha || $idproveedor || $materiaId || $estado) {
            $pedidos = $this->modelo->buscar($fecha, $idproveedor, $materiaId, $estado);
            if ($pedidos){
                return $pedidos;
            }
        }

    }

    public function mpTodas(){
        return ($this->modelo->mpTodas() ? $this->modelo->mpTodas() : false);
    }

    public function obtenerEstados(){
        return ($this->modelo->estadosTodos() ? $this->modelo->estadosTodos() : false);
    }

    function hayFiltroValido($fecha, $proveedorId, $materiaId, $estado) {
        // Para cada parámetro chequeamos que NO sea null y NO sea cadena vacía
        // Esto acepta "0" como válido (porque "0" != "")
        return (
            ($fecha !== null && $fecha !== '') ||
            ($proveedorId !== null && $proveedorId !== '') ||
            ($materiaId !== null && $materiaId !== '') ||
            ($estado !== null && $estado !== '')
        );
    }

    public function traerDetallePedido($idPedido){
        return ($this->modelo->detallePedido($idPedido) ? $this->modelo->detallePedido($idPedido) : false);
    }

    public function traerProveedorporNombre($nombreProveedor){
        return ($this->modelo->traerProveedorporNombre($nombreProveedor) ? $this->modelo->traerProveedorporNombre($nombreProveedor) : false);
    }
}

?>