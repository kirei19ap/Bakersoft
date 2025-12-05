<?php
class controladorPedido{
    private $modelo;
    public function __construct(){
        require_once("../modelo/modeloPedido.php");
        $this->modelo = new modeloPedido();

    }

    public function proveedoresTodos(){
        return ($this->modelo->traerProveedores() ? $this->modelo->traerProveedores() : false);
    }

    public function mostrarMPporProveedor($id_mp){
        return ($this->modelo->traerMPbyProveed($id_mp) ? $this->modelo->traerMPbyProveed($id_mp) : false);
     }

     public function nroultimopedido(){
        $resultado = $this->modelo->traerNroUltimoPedido();
        return $resultado ? $resultado['idPedido'] : 0;
     }

     public function guardarPedido($idProveedor, $items) {   
        $idPedido = $this->modelo->insertarPedido($idProveedor);
        if ($idPedido) {
            foreach ($items as $item) {
                $this->modelo->insertarDetallePedido($idPedido, $item['id'], $item['cantidad']);
            }
            return true;
        }    
        return false;
    }

    public function traerPedidos(){
        return ($this->modelo->pedidosTodos() ? $this->modelo->pedidosTodos() : false);
    }

    public function traerDetallePedido($idPedido){
        return ($this->modelo->detallePedido($idPedido) ? $this->modelo->detallePedido($idPedido) : false);
    }

    public function cancelarPedido($idPedido){
        return ($this->modelo->cancelarPedido($idPedido) ? true : false);
    }

    public function traerProveedorporNombre($nombreProveedor){
        return ($this->modelo->traerProveedorporNombre($nombreProveedor) ? $this->modelo->traerProveedorporNombre($nombreProveedor) : false);
    }
}