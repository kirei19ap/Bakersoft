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
    public function buscar($fecha_desde, $fecha_hasta, $idproveedor, $materiaId, $estado) {
        $pedidos = [];

        if ($fecha_desde || $fecha_hasta || $idproveedor || $materiaId || $estado) {
            $pedidos = $this->modelo->buscar($fecha_desde, $fecha_hasta, $idproveedor, $materiaId, $estado);
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

    function hayFiltroValido($fecha_desde, $fecha_hasta, $proveedorId, $materiaId, $estado) {
        // Para cada parámetro chequeamos que NO sea null y NO sea cadena vacía
        return (
            ($fecha_desde !== null && $fecha_desde !== '') ||
            ($fecha_hasta !== null && $fecha_hasta !== '') ||
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

        /**
     * NUEVO: lista de clientes para el combo cuando busquemos pedidos de clientes.
     */
    public function clientesTodos() {
        return ($this->modelo->clientesTodos() ? $this->modelo->clientesTodos() : false);
    }

    /**
     * NUEVO: validación de filtros para pedidos de clientes.
     * Al menos uno de estos debe venir con valor:
     * - fecha_desde
     * - fecha_hasta
     * - clienteId
     * - estado
     */
    public function hayFiltroValidoClientes($fecha_desde, $fecha_hasta, $clienteId, $estado) {
        return (
            ($fecha_desde !== null && $fecha_desde !== '') ||
            ($fecha_hasta !== null && $fecha_hasta !== '') ||
            ($clienteId !== null && $clienteId !== '') ||
            ($estado !== null && $estado !== '')
        );
    }

    /**
     * NUEVO: búsqueda de pedidos de clientes (pedidoventa),
     * envolviendo al método del modelo.
     */
    public function buscarPedidosClientes($fecha_desde, $fecha_hasta, $clienteId, $estado) {
        $pedidos = [];

        if ($this->hayFiltroValidoClientes($fecha_desde, $fecha_hasta, $clienteId, $estado)) {
            $pedidos = $this->modelo->buscarPedidosClientes($fecha_desde, $fecha_hasta, $clienteId, $estado);
            if ($pedidos) {
                return $pedidos;
            }
        }

        return [];
    }

}

?>