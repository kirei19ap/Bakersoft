<?php
// reparto/controlador/controladorReparto.php

require_once(__DIR__ . "/../modelo/modeloReparto.php");

class ControladorReparto
{
    private $modelo;

    public function __construct()
    {
        $this->modelo = new ModeloReparto();
    }

    public function listarRepartos(array $filtros = [])
    {
        return $this->modelo->listarRepartos($filtros);
    }

    public function obtenerRepartoPorId($idReparto)
    {
        return $this->modelo->obtenerRepartoPorId($idReparto);
    }

    public function obtenerDetalleReparto($idReparto)
    {
        return $this->modelo->obtenerDetalleReparto($idReparto);
    }

    public function listarPedidosPreparadosDisponibles()
    {
        return $this->modelo->listarPedidosPreparadosDisponibles();
    }

    /**
     * Crear reparto
     * $cabecera y $detallePedidos con el formato esperado por el modelo
     */
    public function crearReparto(array $cabecera, array $detallePedidos)
    {
        return $this->modelo->crearReparto($cabecera, $detallePedidos);
    }

    /**
     * Actualizar reparto
     */
    public function actualizarReparto($idReparto, array $cabecera, array $detallePedidos)
    {
        return $this->modelo->actualizarReparto($idReparto, $cabecera, $detallePedidos);
    }

    /**
     * Cambiar estado del reparto
     */
    public function cambiarEstadoReparto($idReparto, $nuevoEstado)
    {
        return $this->modelo->cambiarEstadoReparto($idReparto, $nuevoEstado);
    }

    /**
     * Actualizar estado de entrega de un pedido dentro del reparto
     */
    public function actualizarEstadoEntrega($idDetalleReparto, $nuevoEstadoEntrega)
    {
        return $this->modelo->actualizarEstadoEntrega($idDetalleReparto, $nuevoEstadoEntrega);
    }

    public function listarPedidosPreparadosParaEdicion($idReparto)
    {
        return $this->modelo->listarPedidosPreparadosParaEdicion($idReparto);
    }
    public function pedidoEnRepartoActivo($idPedidoVenta)
    {
        return $this->modelo->pedidoEnRepartoActivo($idPedidoVenta);
    }
    public function obtenerKPIsReparto($fd, $fh, $veh = null, $est = null)
    {
        return $this->modelo->obtenerKPIsReparto($fd, $fh, $veh, $est);
    }

    public function obtenerDetalleRepartos($fd, $fh, $veh = null, $est = null)
    {
        return $this->modelo->obtenerDetalleRepartos($fd, $fh, $veh, $est);
    }
        public function obtenerDuracionPromedioPorZona($fd, $fh, $veh = null, $est = null)
    {
        return $this->modelo->obtenerDuracionPromedioPorZona($fd, $fh, $veh, $est);
    }

}
